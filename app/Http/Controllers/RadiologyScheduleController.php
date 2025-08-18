<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\RadiologySchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RadiologyScheduleController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(OrderItem $orderItem): Response
    {
        $orderItem->load(['order.patient', 'service']);

        return Inertia::render('Radiology/Schedule', [
            'orderItem' => $orderItem,
            'technologists' => User::where('role', 'radiology')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'scheduled_time' => 'required|date|after:now',
            'room' => 'nullable|string|max:255',
            'machine' => 'nullable|string|max:255',
            'technologist_id' => 'nullable|exists:users,id',
            'preparation_instructions' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $orderItem) {
            RadiologySchedule::create([
                'order_item_id' => $orderItem->id,
                ...$validated,
            ]);

            // Update the order item status to reflect it has been scheduled
            $orderItem->update(['status' => 'Scheduled']);
        });

        return redirect()->route('radiology.index')->with('success', 'Imaging study scheduled successfully.');
    }
}
