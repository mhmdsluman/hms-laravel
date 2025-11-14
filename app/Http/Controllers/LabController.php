<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Patient;
use App\Models\LabTest;
use App\Models\LabOrder;
use App\Models\LabOrderResult;
use App\Http\Controllers\LabInventoryController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LabController extends Controller
{
    protected $labInventoryController;

    public function __construct(LabInventoryController $labInventoryController)
    {
        $this->labInventoryController = $labInventoryController;
    }

    public function index(Request $request): Response
    {
        $query = LabOrder::with('patient', 'results.test')->where('status', '!=', 'completed');

        $query->when($request->input('search'), function ($query, $search) {
            $query->where('order_id', 'like', "%{$search}%");
        });

        $activeOrders = $query->get();

        foreach ($activeOrders as $order) {
            if (count($order->tests) > 0) {
                $order->progress = (count($order->results) / count($order->tests)) * 100;
            } else {
                $order->progress = 0;
            }

            $maxTime = 0;
            foreach ($order->tests as $test) {
                if ($test->estimated_time > $maxTime) {
                    $maxTime = $test->estimated_time;
                }
            }
            $buffer = count($order->tests) > 5 ? 30 : 10;
            $order->estimated_time = $maxTime + $buffer . ' minutes';
        }

        return Inertia::render('Lab/LabDashboard', [
            'activeOrders' => $activeOrders,
            'filters' => $request->only(['search']),
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

    public function createResult(OrderItem $orderItem): Response
    {
        return Inertia::render('Lab/ResultEntry', [
            'orderItem' => $orderItem->load('order.patient', 'service'),
            'labTests' => \App\Models\LabTest::all(),
        ]);
    }

    public function storeResult(Request $request, OrderItem $orderItem)
    {
        $validated = $request->validate([
            'results' => 'required|array',
            'comments' => 'nullable|array',
        ]);

        $labOrder = LabOrder::create([
            'patient_id' => $orderItem->order->patient_id,
        ]);
        $labOrder->order_id = 'T' . str_pad($labOrder->id, 4, '0', STR_PAD_LEFT);
        $labOrder->save();

        $patient = $orderItem->order->patient;

        foreach ($validated['results'] as $testId => $result) {
            $labTest = LabTest::find($testId);
            $isAbnormal = false;

            if ($labTest->reference_ranges) {
                $ranges = json_decode($labTest->reference_ranges, true);
                $age = $patient->age;
                $gender = $patient->gender;

                foreach ($ranges as $range) {
                    if ($age >= $range['min_age'] && $age <= $range['max_age']) {
                        if ($range['gender'] === $gender || $range['gender'] === 'any') {
                            if ($result < $range['min_range'] || $result > $range['max_range']) {
                                $isAbnormal = true;
                            }
                            break;
                        }
                    }
                }
            }

            LabOrderResult::create([
                'lab_order_id' => $labOrder->id,
                'lab_test_id' => $testId,
                'result' => is_array($result) ? json_encode($result) : $result,
                'is_abnormal' => $isAbnormal,
                'comment' => $validated['comments'][$testId] ?? null,
            ]);

            $this->labInventoryController->decrementStock($labTest);
        }

        $orderItem->update(['status' => 'Result Ready']);

        return redirect()->route('lab.index')->with('success', 'Lab results stored successfully.');
    }

    public function verifyResult(Request $request, $labResult)
    {
        // Placeholder for verifying lab results
        return redirect()->route('lab.index')->with('success', 'Lab results verified successfully.');
    }

    public function getTestHistory(Patient $patient, LabTest $test)
    {
        $history = LabOrderResult::whereHas('labOrder', function ($query) use ($patient) {
            $query->where('patient_id', $patient->id);
        })
        ->where('lab_test_id', $test->id)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($history);
    }
}
