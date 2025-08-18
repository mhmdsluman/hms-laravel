<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LabController extends Controller
{
    public function index(): Response
    {
        $baseQuery = OrderItem::whereHas('service', function ($query) {
            $query->where('department', 'Laboratory');
        })->with(['order.patient', 'service']);

        $pendingLabOrders = (clone $baseQuery)->where('status', 'Pending')->latest()->get();
        $collectedLabOrders = (clone $baseQuery)->where('status', 'Sample Collected')->latest()->get();
        $resultsReadyOrders = (clone $baseQuery)->where('status', 'Result Ready')->with('labResult')->latest()->get();
        $completedOrders = (clone $baseQuery)->where('status', 'Completed')->with('labResult.verifier')->latest()->limit(10)->get();

        return Inertia::render('Laboratory/Index', [
            'pendingLabOrders' => $pendingLabOrders,
            'collectedLabOrders' => $collectedLabOrders,
            'resultsReadyOrders' => $resultsReadyOrders,
            'completedOrders' => $completedOrders,
        ]);
    }

    public function updateStatus(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Sample Collected',
        ]);

        $orderItem->update(['status' => $validated['status']]);

        return redirect()->route('lab.index')->with('success', 'Order status updated successfully.');
    }
}
