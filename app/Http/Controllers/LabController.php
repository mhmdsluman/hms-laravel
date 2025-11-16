<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Support\Facades\Schema;
use App\Models\Patient;
use App\Models\LabTest;
use App\Models\LabOrder;
use App\Models\LabOrderResult;
use App\Models\LabSample;
use App\Models\QcResult;
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
        $stats = [
            'samples_collected_today' => LabSample::whereDate('collected_at', today())->count(),
            'tests_pending_validation' => LabOrderResult::where('status', 'Preliminary')->count(),
            'qc_failures' => QcResult::where('in_range', false)->whereDate('created_at', today())->count(),
        ];

        // Build per-status lists for the laboratory dashboard
        // Avoid referencing columns that may not exist on all deployments.
        $labCategoryFilter = function ($query) {
            $query->where(function ($q) {
                $added = false;
                if (Schema::hasColumn('services', 'category')) {
                    $q->where('category', 'Laboratory')->orWhere('category', 'lab');
                    $added = true;
                }

                if (Schema::hasColumn('services', 'department')) {
                    // if we already added a condition, chain with orWhere, otherwise where
                    if ($added) {
                        $q->orWhere('department', 'Laboratory');
                    } else {
                        $q->where('department', 'Laboratory');
                        $added = true;
                    }
                }

                // Fallback: if neither column exists, match by name heuristics (best-effort)
                if (! $added) {
                    $q->where('name', 'like', '%cbc%')
                      ->orWhere('name', 'like', '%urinalysis%')
                      ->orWhere('name', 'like', '%stool%')
                      ->orWhere('name', 'like', '%urine%');
                }
            });
        };

        $pendingLabOrders = \App\Models\OrderItem::whereHas('service', $labCategoryFilter)
            ->where('status', 'Pending')
            ->with('order.patient', 'service', 'labResult')
            ->latest()
            ->get();

        $collectedLabOrders = \App\Models\OrderItem::whereHas('service', $labCategoryFilter)
            ->where('status', 'Sample Collected')
            ->with('order.patient', 'service', 'labResult')
            ->latest()
            ->get();

        $resultsReadyOrders = \App\Models\OrderItem::whereHas('service', $labCategoryFilter)
            ->where('status', 'Result Ready')
            ->with('order.patient', 'service', 'labResult')
            ->latest()
            ->get();

        $completedOrders = \App\Models\OrderItem::whereHas('service', $labCategoryFilter)
            ->whereHas('labResult', function ($q) {
                $q->where('status', 'Validated');
            })
            ->with('order.patient', 'service', 'labResult')
            ->latest()
            ->get();

        $recent_activity = LabOrder::with('patient')->latest()->limit(10)->get();

        return Inertia::render('Lab/Dashboard', [
            'stats' => $stats,
            'recent_activity' => $recent_activity,
            'pendingLabOrders' => $pendingLabOrders,
            'collectedLabOrders' => $collectedLabOrders,
            'resultsReadyOrders' => $resultsReadyOrders,
            'completedOrders' => $completedOrders,
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
                'status' => 'Preliminary',
            ]);

            $this->labInventoryController->decrementStock($labTest);
        }

        $orderItem->update(['status' => 'Result Ready']);

        return redirect()->route('lab.index')->with('success', 'Lab results stored successfully.');
    }

    public function verifyResult(Request $request, LabOrderResult $labResult)
    {
        $labResult->update(['status' => 'Validated']);
        return redirect()->back()->with('success', 'Lab result validated successfully.');
    }

    public function rejectResult(Request $request, LabOrderResult $labResult)
    {
        $labResult->update(['status' => 'Requires Correction']);
        return redirect()->back()->with('success', 'Lab result sent back for correction.');
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

    public function cumulativeReport(Patient $patient, LabTest $test): Response
    {
        $history = LabOrderResult::whereHas('labOrder', function ($query) use ($patient) {
            $query->where('patient_id', $patient->id);
        })
        ->where('lab_test_id', $test->id)
        ->orderBy('created_at', 'asc')
        ->get();

        return Inertia::render('Lab/CumulativeReport', [
            'patient' => $patient,
            'test' => $test,
            'history' => $history,
        ]);
    }
}
