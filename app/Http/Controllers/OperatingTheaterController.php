<?php

namespace App\Http\Controllers;

use App\Models\OperatingTheaterSchedule;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OperatingTheaterController extends Controller
{
    /**
     * Display the Operating Theater dashboard.
     */
    public function index(): Response
    {
        $baseQuery = OrderItem::whereHas('service', function ($query) {
            $query->where('department', 'Procedure'); // Assuming surgeries are 'Procedure' type
        })->with(['order.patient', 'service']);

        return Inertia::render('OT/Index', [
            'pendingOrders' => (clone $baseQuery)->where('status', 'Pending')->latest()->get(),
            'scheduledOrders' => (clone $baseQuery)->where('status', 'Scheduled')->with('otSchedule.surgeon')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(OrderItem $orderItem): Response
    {
        $orderItem->load(['order.patient', 'service']);

        return Inertia::render('OT/Schedule', [
            'orderItem' => $orderItem,
            'surgeons' => User::where('role', 'clinician')->orderBy('name')->get(['id', 'name']),
            'nurses' => User::where('role', 'nurse')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'scheduled_start_time' => 'required|date|after:now',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time',
            'theater_room' => 'required|string|max:255',
            'surgeon_id' => 'required|exists:users,id',
            'anesthetist_id' => 'nullable|exists:users,id',
            'scrub_nurse_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $orderItem) {
            OperatingTheaterSchedule::create([
                'order_item_id' => $orderItem->id,
                ...$validated,
            ]);

            $orderItem->update(['status' => 'Scheduled']);
        });

        return redirect()->route('ot.index')->with('success', 'Surgical procedure scheduled successfully.');
    }
}
