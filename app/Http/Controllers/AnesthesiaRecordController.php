<?php

namespace App\Http\Controllers;

use App\Models\AnesthesiaRecord;
use App\Models\OperativeNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AnesthesiaRecordController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(OperativeNote $operativeNote): Response
    {
        $operativeNote->load(['orderItem.order.patient', 'orderItem.otSchedule']);

        return Inertia::render('OT/Notes/CreateAnesthesiaRecord', [
            'operativeNote' => $operativeNote,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, OperativeNote $operativeNote)
    {
        $validated = $request->validate([
            'anesthesia_type' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        AnesthesiaRecord::create([
            'operative_note_id' => $operativeNote->id,
            'anesthetist_id' => $operativeNote->orderItem->otSchedule->anesthetist_id ?? Auth::id(),
            'anesthesia_type' => $validated['anesthesia_type'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('operative-notes.create', ['orderItem' => $operativeNote->order_item_id])
            ->with('success', 'Anesthesia record saved successfully.');
    }
}
