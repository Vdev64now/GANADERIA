<?php

namespace App\Http\Controllers;

use App\Models\CutType;
use App\Models\Setting;
use Illuminate\Http\Request;

class CutTypeController extends Controller
{
    public function index()
    {
        $cutTypes = CutType::orderBy('category')->orderBy('name')->get();
        
        // Settings
        $minYieldThreshold = Setting::getValue('min_yield_percentage', '53');
        $maxWasteThreshold = Setting::getValue('max_waste_percentage', '8');

        return view('cuts.index', compact('cutTypes', 'minYieldThreshold', 'maxWasteThreshold'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cut_types,name',
            'category' => 'required|in:Primera,Segunda,Tercera/Desecho',
            'description' => 'nullable|string',
        ]);

        CutType::create($validated);

        return redirect()->route('cuts.index')->with('success', 'Tipo de corte registrado con éxito.');
    }

    public function update(Request $request, CutType $cut)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cut_types,name,' . $cut->id,
            'category' => 'required|in:Primera,Segunda,Tercera/Desecho',
            'description' => 'nullable|string',
        ]);

        $cut->update($validated);

        return redirect()->route('cuts.index')->with('success', 'Tipo de corte actualizado con éxito.');
    }

    public function destroy(CutType $cut)
    {
        // Check if there are deboning items referencing this cut type
        if ($cut->deboningItems()->count() > 0) {
            return redirect()->route('cuts.index')->with('error', 'No se puede eliminar este corte porque hay registros de inventario vinculados a él.');
        }

        $cut->delete();
        return redirect()->route('cuts.index')->with('success', 'Tipo de corte eliminado con éxito.');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'min_yield_percentage' => 'required|numeric|min:0|max:100',
            'max_waste_percentage' => 'required|numeric|min:0|max:100',
        ]);

        Setting::setValue('min_yield_percentage', $validated['min_yield_percentage']);
        Setting::setValue('max_waste_percentage', $validated['max_waste_percentage']);

        return redirect()->route('cuts.index')->with('success', 'Configuración de alertas actualizada con éxito.');
    }
}
