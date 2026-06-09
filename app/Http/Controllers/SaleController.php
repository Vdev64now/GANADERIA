<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Slaughter;
use App\Models\DeboningItem;
use App\Models\Cattle;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $farmId = session('global_farm_id', 0);
        
        $query = Sale::with(['customer', 'saleItems.slaughter.cattle.farm', 'saleItems.deboningItem.deboning.slaughter.cattle.farm'])
            ->orderBy('sale_date', 'desc');
            
        // If farmId is selected, we filter sales that contain items belonging to that farm
        if ($farmId > 0) {
            $query->whereHas('saleItems', function($q) use ($farmId) {
                $q->whereHas('slaughter.cattle', function($sq) use ($farmId) {
                    $sq->where('farm_id', $farmId);
                })->orWhereHas('deboningItem.deboning.slaughter.cattle', function($dq) use ($farmId) {
                    $dq->where('farm_id', $farmId);
                });
            });
        }

        $sales = $query->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $farmId = session('global_farm_id', 0);

        // 1. Available media canals (halves)
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
        $availableSlaughters = $slaughterQuery->get();

        // 2. Available cuts stock
        $cutsQuery = DeboningItem::with(['deboning.slaughter.cattle', 'cutType'])
            ->where('current_weight', '>', 0);

        if ($farmId > 0) {
            $cutsQuery->whereHas('deboning.slaughter.cattle', function($q) use ($farmId) {
                $q->where('farm_id', $farmId);
            });
        }
        $availableCuts = $cutsQuery->get();
        $customers = Customer::orderBy('first_name')->get();

        return view('sales.create', compact('availableSlaughters', 'availableCuts', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'sale_type' => 'required|in:media_canal_izquierda,media_canal_derecha,corte',
            // media canal validation
            'slaughter_id' => 'required_if:sale_type,media_canal_izquierda,media_canal_derecha|exists:slaughters,id|nullable',
            // cuts validation
            'deboning_item_id' => 'required_if:sale_type,corte|exists:deboning_items,id|nullable',
            'weight' => 'required_if:sale_type,corte|numeric|min:0.01|nullable',
            'price_per_kg' => 'required|numeric|min:0.01',
        ]);

        $saleType = $validated['sale_type'];
        $pricePerKg = (float) $validated['price_per_kg'];
        $weight = 0;
        $subtotal = 0;
        
        DB::beginTransaction();
        try {
            // Create Sale record
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'sale_date' => $validated['sale_date'],
                'total_amount' => 0, // updated after items added
            ]);

            if ($saleType === 'media_canal_izquierda' || $saleType === 'media_canal_derecha') {
                $slaughter = Slaughter::findOrFail($validated['slaughter_id']);
                
                if ($saleType === 'media_canal_izquierda') {
                    if ($slaughter->left_carcass_status !== 'disponible') {
                        return redirect()->back()->withInput()->with('error', 'La media canal izquierda no está disponible para la venta.');
                    }
                    $weight = (float) $slaughter->left_carcass_weight;
                    $slaughter->update(['left_carcass_status' => 'vendido']);
                } else {
                    if ($slaughter->right_carcass_status !== 'disponible') {
                        return redirect()->back()->withInput()->with('error', 'La media canal derecha no está disponible para la venta.');
                    }
                    $weight = (float) $slaughter->right_carcass_weight;
                    $slaughter->update(['right_carcass_status' => 'vendido']);
                }

                $subtotal = $weight * $pricePerKg;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'type' => $saleType,
                    'slaughter_id' => $slaughter->id,
                    'weight' => $weight,
                    'price_per_kg' => $pricePerKg,
                    'subtotal' => $subtotal,
                ]);

                // Update cattle status if both sides are processed/sold
                $slaughter->refresh();
                $cattle = $slaughter->cattle;
                
                $leftStatus = $slaughter->left_carcass_status;
                $rightStatus = $slaughter->right_carcass_status;

                if ($leftStatus !== 'disponible' && $rightStatus !== 'disponible') {
                    if ($leftStatus === 'vendido' && $rightStatus === 'vendido') {
                        $cattle->update(['status' => 'vendido']);
                    } else {
                        $cattle->update(['status' => 'despostado_completo']);
                    }
                } else {
                    $cattle->update(['status' => 'beneficiado_parcial']);
                }

            } elseif ($saleType === 'corte') {
                $deboningItem = DeboningItem::findOrFail($validated['deboning_item_id']);
                $weight = (float) $validated['weight'];

                if ($weight > (float) $deboningItem->current_weight) {
                    return redirect()->back()->withInput()->with('error', 'El peso solicitado (' . $weight . ' kg) supera la cantidad disponible (' . $deboningItem->current_weight . ' kg).');
                }

                $subtotal = $weight * $pricePerKg;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'type' => 'corte',
                    'deboning_item_id' => $deboningItem->id,
                    'weight' => $weight,
                    'price_per_kg' => $pricePerKg,
                    'subtotal' => $subtotal,
                ]);

                // Decrement inventory
                $deboningItem->decrement('current_weight', $weight);

                // Check if all cuts of this cattle are fully sold
                $cattle = $deboningItem->deboning->slaughter->cattle;
                
                // We want to check if the cattle status should change to "vendido"
                // This happens if both Left and Right carcasses are despostados or sold, 
                // AND there are no cuts left with current_weight > 0 for this cattle.
                $slaughter = $deboningItem->deboning->slaughter;
                if ($slaughter->left_carcass_status !== 'disponible' && $slaughter->right_carcass_status !== 'disponible') {
                    // Let's sum remaining weights of all deboned items for this slaughter
                    $remainingCutsWeight = DB::table('deboning_items')
                        ->join('debonings', 'deboning_items.deboning_id', '=', 'debonings.id')
                        ->where('debonings.slaughter_id', $slaughter->id)
                        ->sum('deboning_items.current_weight');

                    if ($remainingCutsWeight == 0) {
                        $cattle->update(['status' => 'vendido']);
                    }
                }
            }

            // Update total amount on Sale
            $sale->update(['total_amount' => $subtotal]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar la venta: ' . $e->getMessage());
        }

        return redirect()->route('sales.index')->with('success', 'Venta registrada con éxito.');
    }

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        try {
            foreach ($sale->saleItems as $item) {
                if ($item->type === 'media_canal_izquierda' || $item->type === 'media_canal_derecha') {
                    $slaughter = $item->slaughter;
                    if ($item->type === 'media_canal_izquierda') {
                        $slaughter->update(['left_carcass_status' => 'disponible']);
                    } else {
                        $slaughter->update(['right_carcass_status' => 'disponible']);
                    }

                    // Update cattle status
                    $slaughter->refresh();
                    $cattle = $slaughter->cattle;
                    if ($slaughter->left_carcass_status === 'disponible' && $slaughter->right_carcass_status === 'disponible') {
                        $cattle->update(['status' => 'beneficiado_completo']);
                    } else {
                        $cattle->update(['status' => 'beneficiado_parcial']);
                    }

                } elseif ($item->type === 'corte') {
                    // Return weight to inventory
                    $deboningItem = $item->deboningItem;
                    if ($deboningItem) {
                        $deboningItem->increment('current_weight', $item->weight);
                    }

                    // Revert cattle status from vendido if it was marked as such
                    $cattle = $deboningItem->deboning->slaughter->cattle;
                    if ($cattle->status === 'vendido') {
                        $cattle->update(['status' => 'despostado_completo']);
                    }
                }
            }

            $sale->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al anular la venta: ' . $e->getMessage());
        }

        return redirect()->route('sales.index')->with('success', 'Venta anulada con éxito y stock restablecido.');
    }
}
