<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $inventoryItems = InventoryItem::with('service')
            ->latest()
            ->paginate(10);

        return Inertia::render('Pharmacy/Inventory/Index', [
            'inventoryItems' => $inventoryItems,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $medications = Service::where('department', 'Pharmacy')->orderBy('name')->get();

        return Inertia::render('Pharmacy/Inventory/Create', [
            'medications' => $medications,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity_in_stock' => 'required|integer|min:0',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
        ]);

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory item added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryItem $inventoryItem): Response
    {
        $medications = Service::where('department', 'Pharmacy')->orderBy('name')->get();

        return Inertia::render('Pharmacy/Inventory/Edit', [
            'inventoryItem' => $inventoryItem,
            'medications' => $medications,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity_in_stock' => 'required|integer|min:0',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
        ]);

        $inventoryItem->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Inventory item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();

        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted successfully.');
    }
}
