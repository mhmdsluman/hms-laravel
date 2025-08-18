<?php

namespace App\Http\Controllers;

use App\Models\OperativeNote;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OperativeNoteController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(OrderItem $orderItem): Response
    {
        $orderItem->load(['order.patient', 'service', 'otSchedule']);

        return Inertia::render('OT/Notes/Create', [
            'orderItem' => $orderItem,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'preoperative_diagnosis' => 'required|string',
            'postoperative_diagnosis' => 'required|string',
            'procedure_description' => 'required|string',
            'findings' => 'required|string',
            'procedure_start_time' => 'required|date',
            'procedure_end_time' => 'required|date|after:procedure_start_time',
        ]);

        DB::transaction(function () use ($validated, $orderItem) {
            OperativeNote::create([
                'order_item_id' => $orderItem->id,
                'surgeon_id' => $orderItem->otSchedule->surgeon_id, // Get surgeon from the schedule
                ...$validated,
            ]);

            // Mark the order as completed
            $orderItem->update(['status' => 'Completed']);
        });

        return redirect()->route('ot.index')->with('success', 'Operative note saved successfully.');
    }
}
