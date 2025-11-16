<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoolTestRequest;
use App\Models\LabResult;
use App\Models\OrderItem;
use App\Models\Patient;
use App\Services\StoolRangeInterpreter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoolTestController extends Controller
{
    public function store(StoreStoolTestRequest $request)
    {
        $validated = $request->validated();
        $patient = Patient::findOrFail($validated['patient_id']);
        $ageInDays = $patient->age_in_days; // Assuming an accessor on the Patient model

        DB::transaction(function () use ($validated, $patient, $ageInDays) {
            foreach ($validated['results'] as $result) {
                $orderItem = OrderItem::findOrFail($result['order_item_id']);

                LabResult::create([
                    'order_item_id' => $orderItem->id,
                    'result_value' => $result['value'],
                    'flag' => StoolRangeInterpreter::getStatus(
                        $orderItem->service_id,
                        $result['value'],
                        $ageInDays,
                        $patient->gender
                    ),
                    'entered_by_user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Stool Analysis results saved successfully.');
    }

    public function update(StoreStoolTestRequest $request, $orderId)
    {
        $validated = $request->validated();
        $patient = OrderItem::where('order_id', $orderId)->firstOrFail()->order->patient;
        $ageInDays = $patient->age_in_days;

        DB::transaction(function () use ($validated, $patient, $ageInDays) {
            foreach ($validated['results'] as $result) {
                $labResult = LabResult::findOrFail($result['id']);
                $orderItem = $labResult->orderItem;

                $labResult->update([
                    'result_value' => $result['value'],
                    'flag' => StoolRangeInterpreter::getStatus(
                        $orderItem->service_id,
                        $result['value'],
                        $ageInDays,
                        $patient->gender
                    ),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Stool Analysis results updated successfully.');
    }
}
