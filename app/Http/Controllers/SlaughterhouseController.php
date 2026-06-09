<?php

namespace App\Http\Controllers;

use App\Models\Slaughterhouse;
use Illuminate\Http\Request;

class SlaughterhouseController extends Controller
{
    public function index()
    {
        $slaughterhouses = Slaughterhouse::withCount('slaughters')->orderBy('name')->get();
        return view('slaughterhouses.index', compact('slaughterhouses'));
    }

    public function create()
    {
        return view('slaughterhouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:slaughterhouses,name',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        Slaughterhouse::create($validated);

        return redirect()->route('slaughterhouses.index')->with('success', 'Matadero registrado con éxito.');
    }

    public function edit(Slaughterhouse $slaughterhouse)
    {
        return view('slaughterhouses.edit', compact('slaughterhouse'));
    }

    public function update(Request $request, Slaughterhouse $slaughterhouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:slaughterhouses,name,' . $slaughterhouse->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $slaughterhouse->update($validated);

        return redirect()->route('slaughterhouses.index')->with('success', 'Matadero actualizado con éxito.');
    }

    public function destroy(Slaughterhouse $slaughterhouse)
    {
        // Prevent deleting if it has slaughter records
        if ($slaughterhouse->slaughters()->count() > 0) {
            return redirect()->route('slaughterhouses.index')->with('error', 'No se puede eliminar este matadero porque tiene registros de beneficios vinculados.');
        }

        $slaughterhouse->delete();
        return redirect()->route('slaughterhouses.index')->with('success', 'Matadero eliminado con éxito.');
    }
}
