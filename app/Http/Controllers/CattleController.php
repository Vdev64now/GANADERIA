<?php

namespace App\Http\Controllers;

use App\Models\Cattle;
use App\Models\Farm;
use Illuminate\Http\Request;

class CattleController extends Controller
{
    public function index()
    {
        $farmId = session('global_farm_id', 0);
        
        $query = Cattle::with('farm')->orderBy('ear_tag');
        
        if ($farmId > 0) {
            $query->where('farm_id', $farmId);
        }
        
        $cattles = $query->get();
        return view('cattle.index', compact('cattles'));
    }

    public function create()
    {
        $farms = Farm::orderBy('name')->get();
        return view('cattle.create', compact('farms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'ear_tag' => 'required|string|max:50',
            'breed' => 'nullable|string|max:100',
            'provider' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'live_weight' => 'required|numeric|min:0.1',
            'purchase_price_total' => 'required|numeric|min:0',
        ]);

        $validated['status'] = 'en_pie';

        Cattle::create($validated);

        return redirect()->route('cattle.index')->with('success', 'Ganado registrado con éxito.');
    }

    public function edit(Cattle $cattle)
    {
        $farms = Farm::orderBy('name')->get();
        return view('cattle.edit', compact('cattle', 'farms'));
    }

    public function update(Request $request, Cattle $cattle)
    {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'ear_tag' => 'required|string|max:50',
            'breed' => 'nullable|string|max:100',
            'provider' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'live_weight' => 'required|numeric|min:0.1',
            'purchase_price_total' => 'required|numeric|min:0',
            'status' => 'required|in:en_pie,beneficiado_parcial,beneficiado_completo,despostado_completo,vendido',
        ]);

        $cattle->update($validated);

        return redirect()->route('cattle.index')->with('success', 'Ganado actualizado con éxito.');
    }

    public function destroy(Cattle $cattle)
    {
        $cattle->delete();
        return redirect()->route('cattle.index')->with('success', 'Cattle eliminado con éxito.');
    }
}
