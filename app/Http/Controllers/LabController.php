<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
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
use Illuminate\Support\Facades\Schema;

class LabController extends Controller
{
    protected $labInventoryController;

    public function __construct(LabInventoryController $labInventoryController)
    {
        $this->labInventoryController = $labInventoryController;
    }

    public function index(Request $request): Response
    {
        // Basic stats
        $stats = [
            'samples_collected_today' => LabSample::whereDate('collected_at', today())->count(),
            'tests_pending_validation' => LabOrderResult::where('status', 'Preliminary')->count(),
            'qc_failures' => QcResult::where('in_range', false)->whereDate('created_at', today())->count(),
        ];

        $recent_activity = LabOrder::with('patient')->latest()->limit(10)->get();

        // Build lists of order items by status for the lab dashboard. Prefer filtering to laboratory services when possible.
        $labFilter = function ($query) {
            if (Schema::hasColumn('services', 'department')) {
                $query->where('department', 'Laboratory');
            } elseif (Schema::hasColumn('services', 'category')) {
                $query->where('category', 'Laboratory');
            }
        };

        $pending = OrderItem::with(['order.patient', 'service'])
            ->where('status', 'Pending')
            ->whereHas('service', $labFilter)
            ->latest()->limit(50)->get();

        $collected = OrderItem::with(['order.patient', 'service'])
            ->where('status', 'Sample Collected')
            ->whereHas('service', $labFilter)
            ->latest()->limit(50)->get();

        $ready = OrderItem::with(['order.patient', 'service'])
            ->where('status', 'Result Ready')
            ->whereHas('service', $labFilter)
            ->latest()->limit(50)->get();

        $completed = OrderItem::with(['order.patient', 'service'])
            ->where('status', 'Completed')
            ->whereHas('service', $labFilter)
            ->latest()->limit(50)->get();

        return Inertia::render('Lab/Dashboard', [
            'stats' => $stats,
            'recent_activity' => $recent_activity,
            'pending' => $pending,
            'collected' => $collected,
            'ready' => $ready,
            'completed' => $completed,
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

        // Use age in days for reference range matching (consistent with front-end)
        $patientAgeDays = $patient->age_in_days ?? null;

        foreach ($validated['results'] as $testId => $result) {
            $labTest = LabTest::find($testId);
            $isAbnormal = false;

            // Attempt to parse structured reference ranges from the lab_test's JSON field
            $ranges = [];
            if ($labTest && $labTest->reference_ranges) {
                $decoded = json_decode($labTest->reference_ranges, true);
                if (is_array($decoded)) {
                    // If decoded is an indexed array of ranges
                    $hasNumeric = array_keys($decoded) === range(0, count($decoded) - 1);
                    if ($hasNumeric) {
                        $ranges = $decoded;
                    } else {
                        // associative: try to flatten common shapes
                        foreach ($decoded as $entry) {
                            if (is_array($entry)) {
                                // if entry itself is an indexed list, merge
                                $subHasNumeric = is_array($entry) && array_keys($entry) === range(0, count($entry) - 1);
                                if ($subHasNumeric) {
                                    $ranges = array_merge($ranges, $entry);
                                } else {
                                    $ranges[] = $entry;
                                }
                            }
                        }
                    }
                }
            }

            // Fallback: try service-level reference ranges (if this order item references a service)
            if (empty($ranges) && $orderItem->service) {
                $svcRanges = $orderItem->service->referenceRanges()->get()->toArray();
                foreach ($svcRanges as $r) {
                    $ranges[] = [
                        'age_min' => $r['age_min'] ?? ($r['ageMin'] ?? 0),
                        'age_max' => $r['age_max'] ?? ($r['ageMax'] ?? 0),
                        'range_low' => $r['range_low'] ?? ($r['rangeLow'] ?? null),
                        'range_high' => $r['range_high'] ?? ($r['rangeHigh'] ?? null),
                        'gender' => $r['gender'] ?? 'All',
                    ];
                }
            }

            // Evaluate abnormality using normalized ranges (if present)
            if (!empty($ranges) && $patientAgeDays !== null) {
                foreach ($ranges as $range) {
                    $minAge = $range['age_min'] ?? ($range['min_age'] ?? null);
                    $maxAge = $range['age_max'] ?? ($range['max_age'] ?? null);
                    $low = $range['range_low'] ?? ($range['min_range'] ?? null);
                    $high = $range['range_high'] ?? ($range['max_range'] ?? null);
                    $gender = $range['gender'] ?? ($range['sex'] ?? 'All');

                    // Skip ranges that don't contain min/max age
                    if ($minAge === null || $maxAge === null) {
                        continue;
                    }

                    // Compare using days (assume stored values are in days for both sources)
                    if ($patientAgeDays >= $minAge && $patientAgeDays <= $maxAge) {
                        // If numeric bounds provided, check abnormality
                        if ($low !== null && $high !== null) {
                            $numeric = is_array($result) ? null : floatval($result);
                            if ($numeric !== null && ($numeric < $low || $numeric > $high)) {
                                // gender check
                                if ($gender === 'All' || $gender === $patient->gender) {
                                    $isAbnormal = true;
                                }
                            }
                        }
                        break;
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

            // decrement inventory if labTest available
            if ($labTest) {
                $this->labInventoryController->decrementStock($labTest);
            }
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
