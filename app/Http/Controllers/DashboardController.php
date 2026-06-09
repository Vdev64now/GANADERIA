<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farm;
use App\Models\Cattle;
use App\Models\Slaughter;
use App\Models\Deboning;
use App\Models\DeboningItem;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $farmId = session('global_farm_id', 0);

        // Sub-query base filters
        $cattleQuery = Cattle::query();
        $slaughterQuery = Slaughter::query()->join('cattles', 'slaughters.cattle_id', '=', 'cattles.id');
        $deboningQuery = Deboning::query()
            ->join('slaughters', 'debonings.slaughter_id', '=', 'slaughters.id')
            ->join('cattles', 'slaughters.cattle_id', '=', 'cattles.id');
        
        $deboningItemQuery = DeboningItem::query()
            ->join('debonings', 'deboning_items.deboning_id', '=', 'debonings.id')
            ->join('slaughters', 'debonings.slaughter_id', '=', 'slaughters.id')
            ->join('cattles', 'slaughters.cattle_id', '=', 'cattles.id');

        if ($farmId > 0) {
            $cattleQuery->where('farm_id', $farmId);
            $slaughterQuery->where('cattles.farm_id', $farmId);
            $deboningQuery->where('cattles.farm_id', $farmId);
            $deboningItemQuery->where('cattles.farm_id', $farmId);
        }

        // KPIs
        // 1. Cattle in Pie
        $cattleInPieCount = (clone $cattleQuery)->where('cattles.status', 'en_pie')->count();
        $cattleInPieWeight = (clone $cattleQuery)->where('cattles.status', 'en_pie')->sum('live_weight');

        // 2. Available Halves (Medias Canales Disponibles)
        $slaughters = (clone $slaughterQuery)->select('slaughters.*')->get();
        $availableLeftCarcasses = $slaughters->where('left_carcass_status', 'disponible')->count();
        $availableRightCarcasses = $slaughters->where('right_carcass_status', 'disponible')->count();
        $totalAvailableHalfCarcasses = $availableLeftCarcasses + $availableRightCarcasses;

        // 3. Stock of Cuts (kg)
        $totalCutsStockWeight = (clone $deboningItemQuery)->sum('deboning_items.current_weight');

        // 4. Financials
        // We get total sales for cattle belonging to this farm
        // Let's grab sales items
        $saleItemsQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('slaughters', 'sale_items.slaughter_id', '=', 'slaughters.id')
            ->leftJoin('deboning_items', 'sale_items.deboning_item_id', '=', 'deboning_items.id')
            ->leftJoin('debonings', 'deboning_items.deboning_id', '=', 'debonings.id')
            ->leftJoin('slaughters as slaughter_deboned', 'debonings.slaughter_id', '=', 'slaughter_deboned.id')
            // Determine cattle relation:
            ->leftJoin('cattles as cattle_slaughter', 'slaughters.cattle_id', '=', 'cattle_slaughter.id')
            ->leftJoin('cattles as cattle_deboned', 'slaughter_deboned.cattle_id', '=', 'cattle_deboned.id');

        if ($farmId > 0) {
            $saleItemsQuery->where(function($q) use ($farmId) {
                $q->where('cattle_slaughter.farm_id', $farmId)
                  ->orWhere('cattle_deboned.farm_id', $farmId);
            });
        }

        $totalRevenue = $saleItemsQuery->sum('sale_items.subtotal');

        // Cost of processed/sold cattle in this farm
        $processedCattle = (clone $cattleQuery)->whereIn('cattles.status', [
            'beneficiado_parcial', 'beneficiado_completo', 'despostado_completo', 'vendido'
        ])->get();

        $totalCattleCost = $processedCattle->sum('purchase_price_total');
        
        // Slaughter cost for processed cattle
        $processedSlaughterCost = (clone $slaughterQuery)->sum('slaughters.slaughter_cost');
        
        $totalCosts = $totalCattleCost + $processedSlaughterCost;
        $profitMargin = $totalRevenue - $totalCosts;

        // Threshold Alert configurations
        $minYieldThreshold = (float) Setting::getValue('min_yield_percentage', 53);
        $maxWasteThreshold = (float) Setting::getValue('max_waste_percentage', 8);

        // Check Alerts
        $alerts = [];

        // Carcass yield warnings
        $slaughtersWithCattle = (clone $slaughterQuery)->with('cattle')->select('slaughters.*')->get();
        foreach ($slaughtersWithCattle as $sl) {
            $yield = $sl->yield_percentage;
            if ($yield < $minYieldThreshold) {
                $alerts[] = [
                    'type' => 'yield',
                    'severity' => 'warning',
                    'message' => "La res arete #{$sl->cattle->ear_tag} ({$sl->cattle->breed}) tiene un Rendimiento de Canal de {$yield}%, que es menor al límite establecido de {$minYieldThreshold}%.",
                ];
            }
        }

        // Deboning waste warnings
        $deboningsWithCattle = (clone $deboningQuery)->with('slaughter.cattle')->select('debonings.*')->get();
        foreach ($deboningsWithCattle as $deb) {
            $wastePct = 0;
            if ($deb->input_weight > 0) {
                $wastePct = round(($deb->waste_weight / $deb->input_weight) * 100, 2);
            }
            if ($wastePct > $maxWasteThreshold) {
                $earTag = $deb->slaughter->cattle->ear_tag ?? 'N/A';
                $alerts[] = [
                    'type' => 'deboning_waste',
                    'severity' => 'danger',
                    'message' => "El desposte (Lado " . ucfirst($deb->side) . ") de la res #{$earTag} tiene una Merma de Desposte del {$wastePct}% ({$deb->waste_weight} kg), superando el límite establecido de {$maxWasteThreshold}%.",
                ];
            }
        }

        // Chart Data: Cut distribution by category (Primera, Segunda, Tercera/Desecho)
        $cutsByCategory = (clone $deboningItemQuery)
            ->join('cut_types', 'deboning_items.cut_type_id', '=', 'cut_types.id')
            ->select('cut_types.category', DB::raw('SUM(deboning_items.current_weight) as total_weight'))
            ->groupBy('cut_types.category')
            ->get();

        $chartCategories = ['Primera' => 0, 'Segunda' => 0, 'Tercera/Desecho' => 0];
        foreach ($cutsByCategory as $row) {
            if (isset($chartCategories[$row->category])) {
                $chartCategories[$row->category] = (float) $row->total_weight;
            }
        }

        return view('dashboard', compact(
            'cattleInPieCount',
            'cattleInPieWeight',
            'totalAvailableHalfCarcasses',
            'totalCutsStockWeight',
            'totalRevenue',
            'totalCosts',
            'profitMargin',
            'alerts',
            'chartCategories'
        ));
    }

    public function setFarm($id = 0)
    {
        session(['global_farm_id' => (int) $id]);
        return redirect()->back();
    }
}
