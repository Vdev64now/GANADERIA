<?php

namespace App\Http\Controllers;

use App\Models\Slaughter;
use App\Models\Cattle;
use App\Models\Setting;
use App\Models\Slaughterhouse;
use Illuminate\Http\Request;

class SlaughterController extends Controller
{
    public function index()
    {
        $farmId = session('global_farm_id', 0);
        
        $query = Slaughter::with(['cattle.farm', 'slaughterhouse'])->orderBy('slaughter_date', 'desc');
        
        if ($farmId > 0) {
            $query->whereHas('cattle', function($q) use ($farmId) {
                $q->where('farm_id', $farmId);
            });
        }
        
        $slaughters = $query->get();
        return view('slaughters.index', compact('slaughters'));
    }

    public function create()
    {
        // Only select cattle that are still "en_pie" (live)
        $farmId = session('global_farm_id', 0);
        $cattleQuery = Cattle::where('status', 'en_pie');
        
        if ($farmId > 0) {
            $cattleQuery->where('farm_id', $farmId);
        }
        
        $cattles = $cattleQuery->orderBy('ear_tag')->get();
        $slaughterhouses = Slaughterhouse::orderBy('name')->get();

        return view('slaughters.create', compact('cattles', 'slaughterhouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cattle_id' => 'required|exists:cattles,id',
            'slaughterhouse_id' => 'required|exists:slaughterhouses,id',
            'slaughter_date' => 'required|date',
            'left_carcass_weight' => 'required|numeric|min:0.1',
            'right_carcass_weight' => 'required|numeric|min:0.1',
            'slaughter_cost' => 'required|numeric|min:0',
        ]);

        $cattle = Cattle::findOrFail($validated['cattle_id']);
        
        if ($cattle->status !== 'en_pie') {
            return redirect()->back()->withInput()->with('error', 'El ganado seleccionado ya ha sido beneficiado o vendido.');
        }

        // Create slaughter record
        $slaughter = Slaughter::create([
            'cattle_id' => $validated['cattle_id'],
            'slaughterhouse_id' => $validated['slaughterhouse_id'],
            'slaughter_date' => $validated['slaughter_date'],
            'left_carcass_weight' => $validated['left_carcass_weight'],
            'right_carcass_weight' => $validated['right_carcass_weight'],
            'slaughter_cost' => $validated['slaughter_cost'],
            'left_carcass_status' => 'disponible',
            'right_carcass_status' => 'disponible',
        ]);

        // Update cattle status
        $cattle->update(['status' => 'beneficiado_completo']);

        // Check if carcass yield triggers alert
        $yield = $slaughter->yield_percentage;
        $minYieldThreshold = (float) Setting::getValue('min_yield_percentage', 53);

        if ($yield < $minYieldThreshold) {
            return redirect()->route('slaughters.index')->with('success', "Beneficio registrado con éxito. ")
                ->with('error', "¡Alerta de Rendimiento! El rendimiento obtenido fue de {$yield}%, inferior al umbral de {$minYieldThreshold}%.");
        }

        return redirect()->route('slaughters.index')->with('success', 'Beneficio registrado con éxito.');
    }

    public function destroy(Slaughter $slaughter)
    {
        // Revert cattle status to en_pie before deleting
        if ($slaughter->cattle) {
            $slaughter->cattle->update(['status' => 'en_pie']);
        }
        $slaughter->delete();
        return redirect()->route('slaughters.index')->with('success', 'Registro de beneficio eliminado con éxito y estado del ganado restablecido.');
    }
}
