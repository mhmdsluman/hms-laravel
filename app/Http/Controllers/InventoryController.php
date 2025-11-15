<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(): Response
    {
        // Frontend pages live under Pages/Pharmacy/Inventory — render those so Inertia can resolve the component
        return Inertia::render('Pharmacy/Inventory/Index', [
            'items' => Inventory::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Pharmacy/Inventory/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory,name',
            'description' => 'nullable|string',
            'category' => 'required|string|in:Pharmacy,Lab',
            'location' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        Inventory::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory item created.');
    }

    public function edit(Inventory $inventory): Response
    {
        return Inertia::render('Pharmacy/Inventory/Edit', [
            'item' => $inventory,
        ]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory,name,' . $inventory->id,
            'description' => 'nullable|string',
            'category' => 'required|string|in:Pharmacy,Lab',
            'location' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory item updated.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted.');
    }
}
