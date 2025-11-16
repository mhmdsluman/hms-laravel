<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\LabOrder;
use App\Models\LabOrderResult;
use App\Models\LabSample;
use App\Models\QcResult;
use App\Http\Controllers\LabInventoryController;
use App\Services\CbcCalculationService;
use App\Services\LabResultFlaggingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LabController extends Controller
{
    // ... constructor and other methods

    public function storeResult(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.service_id' => 'required|exists:services,id',
            'results.*.result' => 'required|string',
            'results.*.comment' => 'nullable|string',
        ]);

        if ($orderItem->service->name === 'Complete Blood Count (CBC)') {
            $calculatedResults = CbcCalculationService::calculate($validated['results']);
            foreach ($calculatedResults as $name => $result) {
                $service = Service::where('name', $name)->first();
                if ($service) {
                    $validated['results'][] = ['service_id' => $service->id, 'result' => $result, 'comment' => null];
                }
            }
        }

        $labOrder = LabOrder::firstOrCreate(
            ['order_id' => $orderItem->order->id],
            ['patient_id' => $orderItem->order->patient_id]
        );

        $patient = $orderItem->order->patient;
        $ageInDays = $patient->date_of_birth->diffInDays(now());

        foreach ($validated['results'] as $resultData) {
            $service = Service::find($resultData['service_id']);
            $flag = LabResultFlaggingService::getFlag($service, $resultData['result'], $ageInDays, $patient->gender);

            LabOrderResult::updateOrCreate(
                [
                    'lab_order_id' => $labOrder->id,
                    'order_item_id' => $orderItem->id,
                    // Assuming one result per service per order item. A better key might be needed for re-tests.
                    'service_id' => $service->id,
                ],
                [
                    'result' => $resultData['result'],
                    'is_abnormal' => in_array($flag, ['Low', 'High', 'Abnormal', 'Present']),
                    'comment' => $resultData['comment'] ?? null,
                    'status' => 'Preliminary',
                ]
            );

            $this->labInventoryController->decrementStock($service);
        }

        $orderItem->update(['status' => 'Result Ready']);

        return redirect()->route('lab.index')->with('success', 'Lab results stored successfully.');
    }

    // ... other methods
}
