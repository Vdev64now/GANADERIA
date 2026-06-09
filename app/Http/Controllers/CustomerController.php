<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withCount('sales')->orderBy('first_name')->get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'has_butcher_shop' => 'nullable|boolean',
            'butcher_shop_name' => 'required_if:has_butcher_shop,1|nullable|string|max:255',
        ]);

        $validated['has_butcher_shop'] = $request->has('has_butcher_shop');

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente registrado con éxito.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'has_butcher_shop' => 'nullable|boolean',
            'butcher_shop_name' => 'required_if:has_butcher_shop,1|nullable|string|max:255',
        ]);

        $validated['has_butcher_shop'] = $request->has('has_butcher_shop');

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente actualizado con éxito.');
    }

    public function destroy(Customer $customer)
    {
        // Prevent deletion if it has sales history
        if ($customer->sales()->count() > 0) {
            return redirect()->route('customers.index')->with('error', 'No se puede eliminar este cliente porque tiene historial de compras.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Cliente eliminado con éxito.');
    }
}
