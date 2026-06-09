<?php

namespace App\Http\Controllers;

use App\Models\Deboning;
use App\Models\DeboningItem;
use App\Models\Slaughter;
use App\Models\CutType;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeboningController extends Controller
{
    public function index()
    {
        $farmId = session('global_farm_id', 0);
        
        $query = Deboning::with('slaughter.cattle.farm')->orderBy('deboning_date', 'desc');
        
        if ($farmId > 0) {
            $query->whereHas('slaughter.cattle', function($q) use ($farmId) {
                $q->where('farm_id', $farmId);
            });
        }
        
        $debonings = $query->get();
        return view('debonings.index', compact('debonings'));
    }

    public function create()
    {
        $farmId = session('global_farm_id', 0);

        // Get slaughters that have at least one side available for deboning
        $slaughterQuery = Slaughter::with('cattle')
            ->where(function($q) {
                $q->where('left_carcass_status', 'disponible')
                  ->orWhere('right_carcass_status', 'disponible');
            });

        if ($farmId > 0) {
            $slaughterQuery->whereHas('cattle', function($q) use ($farmId) {
                $q->where('farm_id', $farmId);
            });
        }

        $slaughters = $slaughterQuery->get();
        $cutTypes = CutType::orderBy('name')->get();

        return view('debonings.create', compact('slaughters', 'cutTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slaughter_id' => 'required|exists:slaughters,id',
            'side' => 'required|in:izquierdo,derecho,ambos',
            'deboning_date' => 'required|date',
            'cuts' => 'required|array', // key is cut_type_id, value is weight
            'cuts.*' => 'nullable|numeric|min:0',
        ]);

        $slaughter = Slaughter::findOrFail($validated['slaughter_id']);
        $side = $validated['side'];

        // Determine input weight based on selected side and verify availability
        $inputWeight = 0;
        if ($side === 'izquierdo') {
            if ($slaughter->left_carcass_status !== 'disponible') {
                return redirect()->back()->withInput()->with('error', 'La media canal izquierda no está disponible.');
            }
            $inputWeight = (float) $slaughter->left_carcass_weight;
        } elseif ($side === 'derecho') {
            if ($slaughter->right_carcass_status !== 'disponible') {
                return redirect()->back()->withInput()->with('error', 'La media canal derecha no está disponible.');
            }
            $inputWeight = (float) $slaughter->right_carcass_weight;
        } elseif ($side === 'ambos') {
            if ($slaughter->left_carcass_status !== 'disponible' || $slaughter->right_carcass_status !== 'disponible') {
                return redirect()->back()->withInput()->with('error', 'Una o ambas medias canales no están disponibles.');
            }
            $inputWeight = (float) ($slaughter->left_carcass_weight + $slaughter->right_carcass_weight);
        }

        // Filter and sum cut weights
        $cutsWeightSum = 0;
        $filteredCuts = [];
        foreach ($validated['cuts'] as $cutTypeId => $weight) {
            $weightVal = (float) $weight;
            if ($weightVal > 0) {
                $filteredCuts[$cutTypeId] = $weightVal;
                $cutsWeightSum += $weightVal;
            }
        }

        if ($cutsWeightSum > $inputWeight) {
            return redirect()->back()->withInput()->with('error', 'El peso de los cortes obtenidos (' . $cutsWeightSum . ' kg) supera el peso inicial despostado (' . $inputWeight . ' kg).');
        }

        // Calculations
        $wasteWeight = $inputWeight - $cutsWeightSum;
        $yieldPercentage = $inputWeight > 0 ? ($cutsWeightSum / $inputWeight) * 100 : 0;
        $wastePercentage = $inputWeight > 0 ? ($wasteWeight / $inputWeight) * 100 : 0;

        DB::beginTransaction();
        try {
            // Save Deboning session
            $deboning = Deboning::create([
                'slaughter_id' => $slaughter->id,
                'side' => $side,
                'deboning_date' => $validated['deboning_date'],
                'input_weight' => $inputWeight,
                'total_cuts_weight' => $cutsWeightSum,
                'waste_weight' => $wasteWeight,
                'yield_percentage' => round($yieldPercentage, 2),
            ]);

            // Save Deboned Items
            foreach ($filteredCuts as $cutTypeId => $weightVal) {
                DeboningItem::create([
                    'deboning_id' => $deboning->id,
                    'cut_type_id' => $cutTypeId,
                    'weight' => $weightVal,
                    'current_weight' => $weightVal,
                ]);
            }

            // Update slaughter sides status
            if ($side === 'izquierdo') {
                $slaughter->update(['left_carcass_status' => 'despostado']);
            } elseif ($side === 'derecho') {
                $slaughter->update(['right_carcass_status' => 'despostado']);
            } elseif ($side === 'ambos') {
                $slaughter->update([
                    'left_carcass_status' => 'despostado',
                    'right_carcass_status' => 'despostado'
                ]);
            }

            // Refresh slaughter to check updated side statuses
            $slaughter->refresh();

            // Update cattle status
            $cattle = $slaughter->cattle;
            if ($slaughter->left_carcass_status === 'despostado' && $slaughter->right_carcass_status === 'despostado') {
                $cattle->update(['status' => 'despostado_completo']);
            } else {
                $cattle->update(['status' => 'beneficiado_parcial']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al procesar el desposte: ' . $e->getMessage());
        }

        // Check alerts
        $maxWasteThreshold = (float) Setting::getValue('max_waste_percentage', 8);
        if ($wastePercentage > $maxWasteThreshold) {
            return redirect()->route('debonings.index')->with('success', 'Despostaje registrado con éxito.')
                ->with('error', '¡Alerta de Merma! La merma de desposte es del ' . round($wastePercentage, 2) . '%, superior al umbral configurado de ' . $maxWasteThreshold . '%.');
        }

        return redirect()->route('debonings.index')->with('success', 'Despostaje registrado con éxito.');
    }

    public function destroy(Deboning $deboning)
    {
        DB::beginTransaction();
        try {
            $slaughter = $deboning->slaughter;
            $side = $deboning->side;

            // Revert slaughter sides status
            if ($side === 'izquierdo') {
                $slaughter->update(['left_carcass_status' => 'disponible']);
            } elseif ($side === 'derecho') {
                $slaughter->update(['right_carcass_status' => 'disponible']);
            } elseif ($side === 'ambos') {
                $slaughter->update([
                    'left_carcass_status' => 'disponible',
                    'right_carcass_status' => 'disponible'
                ]);
            }

            // Refresh slaughter and update cattle status
            $slaughter->refresh();
            $cattle = $slaughter->cattle;
            
            if ($slaughter->left_carcass_status === 'disponible' && $slaughter->right_carcass_status === 'disponible') {
                $cattle->update(['status' => 'beneficiado_completo']);
            } else {
                $cattle->update(['status' => 'beneficiado_parcial']);
            }

            // Delete deboning items & deboning session
            $deboning->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al revertir el desposte: ' . $e->getMessage());
        }

        return redirect()->route('debonings.index')->with('success', 'Despostaje revertido con éxito y stock restablecido.');
    }
}
