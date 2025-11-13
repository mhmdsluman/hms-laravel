<?php

namespace App\Http\Controllers;

use App\Models\BillableEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillableEventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/BillableEvents/Index', [
            'billableEvents' => BillableEvent::where('status', 'Pending')->with(['patient', 'service'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'service_id' => 'required|integer|exists:services,id',
        ]);

        BillableEvent::create([
            'patient_id' => $validated['patient_id'],
            'service_id' => $validated['service_id'],
            'status' => 'Pending',
        ]);

        return redirect()->back()->with('success', 'Billable event created successfully.');
    }

    public function generateBill(Request $request)
    {
        $validated = $request->validate([
            'event_ids' => 'required|array|min:1',
            'event_ids.*' => 'required|integer|exists:billable_events,id',
        ]);

        $events = BillableEvent::find($validated['event_ids']);
        $patientId = $events->first()->patient_id;

        // Ensure all events are for the same patient
        foreach ($events as $event) {
            if ($event->patient_id !== $patientId) {
                return redirect()->back()->with('error', 'All events must be for the same patient.');
            }
        }

        $bill = Bill::create([
            'patient_id' => $patientId,
            'total_amount' => 0,
            'status' => 'Unpaid',
        ]);

        foreach ($events as $event) {
            $bill->items()->create([
                'service_id' => $event->service_id,
                'quantity' => 1,
                'unit_price' => $event->service->price,
                'total_price' => $event->service->price,
            ]);
            $event->update(['status' => 'Billed', 'bill_id' => $bill->id]);
        }

        $bill->update(['total_amount' => $bill->items->sum('total_price')]);

        return redirect()->back()->with('success', 'Bill generated successfully.');
    }
}
