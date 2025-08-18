<?php

namespace App\Http\Controllers;

use App\Models\LabResult;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LabResultController extends Controller
{
    public function create(OrderItem $orderItem): Response
    {
        $orderItem->load(['order.patient', 'service.referenceRanges']);

        return Inertia::render('Laboratory/Results/Create', [
            'orderItem' => $orderItem,
        ]);
    }

    public function store(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'result_value' => 'required|string|max:255',
            'result_numeric' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $orderItem->load(['order.patient', 'service.referenceRanges']);
        $patient = $orderItem->order->patient;
        $service = $orderItem->service;
        $flag = null;
        $referenceRangeText = null;

        if (isset($validated['result_numeric'])) {
            $resultValue = (float) $validated['result_numeric'];
            $matchingRange = $service->referenceRanges->first(function ($range) use ($patient) {
                return $patient->age >= $range->age_min
                    && $patient->age <= $range->age_max
                    && ($range->gender === 'All' || $range->gender === $patient->gender);
            });

            if ($matchingRange) {
                $referenceRangeText = "{$matchingRange->range_low} - {$matchingRange->range_high}";
                if ($matchingRange->critical_low !== null && $resultValue < $matchingRange->critical_low) {
                    $flag = 'Critical Low';
                } elseif ($matchingRange->critical_high !== null && $resultValue > $matchingRange->critical_high) {
                    $flag = 'Critical High';
                } elseif ($resultValue < $matchingRange->range_low) {
                    $flag = 'Low';
                } elseif ($resultValue > $matchingRange->range_high) {
                    $flag = 'High';
                } else {
                    $flag = 'Normal';
                }
            }
        }

        LabResult::create([
            'order_item_id' => $orderItem->id,
            'result_value' => $validated['result_value'],
            'result_numeric' => $validated['result_numeric'] ?? null,
            'units' => $service->units,
            'reference_range' => $referenceRangeText,
            'flag' => $flag,
            'notes' => $validated['notes'],
            'entered_by_user_id' => Auth::id(),
        ]);

        $orderItem->update(['status' => 'Result Ready']);

        return redirect()->route('lab.index')->with('success', 'Result entered successfully.');
    }

    /**
     * Verify a lab result.
     */
    public function verify(Request $request, LabResult $labResult)
    {
        DB::transaction(function () use ($labResult) {
            $labResult->update([
                'verified_by_user_id' => Auth::id(),
                'verified_at' => now(),
            ]);

            $labResult->orderItem->update(['status' => 'Completed']);
        });

        return redirect()->route('lab.index')->with('success', 'Result verified successfully.');
    }
}
