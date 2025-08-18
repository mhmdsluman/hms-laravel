<?php

namespace App\Http\Controllers;

use App\Models\OrderSet;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderSetController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/OrderSets/Index', [
            'orderSets' => OrderSet::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/OrderSets/Builder', [
            'services' => Service::orderBy('department')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:order_sets,name',
            'description' => 'nullable|string',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
        ]);

        DB::transaction(function () use ($validated) {
            $orderSet = OrderSet::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);

            foreach ($validated['service_ids'] as $serviceId) {
                $orderSet->items()->create(['service_id' => $serviceId]);
            }
        });

        return redirect()->route('order-sets.index')->with('success', 'Order Set created successfully.');
    }

    public function edit(OrderSet $orderSet): Response
    {
        $orderSet->load('items');

        return Inertia::render('Admin/OrderSets/Builder', [
            'orderSet' => $orderSet,
            'services' => Service::orderBy('department')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, OrderSet $orderSet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:order_sets,name,' . $orderSet->id,
            'description' => 'nullable|string',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
        ]);

        DB::transaction(function () use ($validated, $orderSet) {
            $orderSet->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);

            $orderSet->items()->delete();
            foreach ($validated['service_ids'] as $serviceId) {
                $orderSet->items()->create(['service_id' => $serviceId]);
            }
        });

        return redirect()->route('order-sets.index')->with('success', 'Order Set updated successfully.');
    }

    public function destroy(OrderSet $orderSet)
    {
        $orderSet->delete();
        return redirect()->route('order-sets.index')->with('success', 'Order Set deleted successfully.');
    }
}
