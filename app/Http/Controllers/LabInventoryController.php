<?php

namespace App\Http\Controllers;

use App\Models\LabInventoryItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LabInventoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Laboratory/Inventory/Index', [
            'items' => LabInventoryItem::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Laboratory/Inventory/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:lab_inventory_items,name',
            'item_type' => 'required|string|in:Reagent,Consumable',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'supplier' => 'nullable|string|max:255',
        ]);

        LabInventoryItem::create($validated);

        return redirect()->route('lab-inventory.index')->with('success', 'Lab inventory item created.');
    }

    public function edit(LabInventoryItem $labInventoryItem): Response
    {
        return Inertia::render('Laboratory/Inventory/Edit', [
            'item' => $labInventoryItem,
        ]);
    }

    public function update(Request $request, LabInventoryItem $labInventoryItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:lab_inventory_items,name,' . $labInventoryItem->id,
            'item_type' => 'required|string|in:Reagent,Consumable',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'supplier' => 'nullable|string|max:255',
        ]);

        $labInventoryItem->update($validated);

        return redirect()->route('lab-inventory.index')->with('success', 'Lab inventory item updated.');
    }

    public function destroy(LabInventoryItem $labInventoryItem)
    {
        $labInventoryItem->delete();
        return redirect()->route('lab-inventory.index')->with('success', 'Lab inventory item deleted.');
    }
}
