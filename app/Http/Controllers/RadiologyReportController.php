<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\RadiologyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RadiologyReportController extends Controller
{
    public function create(OrderItem $orderItem): Response
    {
        $orderItem->load(['order.patient', 'service']);

        return Inertia::render('Radiology/Reports/Create', [
            'orderItem' => $orderItem,
        ]);
    }

    public function store(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'report_text' => 'required|string',
            'study_instance_uid' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $orderItem) {
            RadiologyReport::create([
                'order_item_id' => $orderItem->id,
                'report_text' => $validated['report_text'],
                'study_instance_uid' => $validated['study_instance_uid'],
                'reported_by_user_id' => Auth::id(),
            ]);

            $orderItem->update(['status' => 'Completed']);
        });

        return redirect()->route('radiology.index')->with('success', 'Radiology report saved successfully.');
    }
}
