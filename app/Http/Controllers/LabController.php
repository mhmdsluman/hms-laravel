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
use Illuminate\Support\Facades\Schema;
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
        $labCategoryFilter = function ($query) {
            $query->where(function ($q) {
                $added = false;
                if (Schema::hasColumn('services', 'category')) {
                    $q->where('category', 'Laboratory')->orWhere('category', 'lab');
                    $added = true;
                }

                if (Schema::hasColumn('services', 'department')) {
                    if ($added) {
                        $q->orWhere('department', 'Laboratory');
                    } else {
                        $q->where('department', 'Laboratory');
                        $added = true;
                    }
                }

                if (! $added) {
                    $q->where('name', 'like', '%cbc%')
                      ->orWhere('name', 'like', '%urinalysis%')
                      ->orWhere('name', 'like', '%stool%')
                      ->orWhere('name', 'like', '%urine%');
                }
            });
        };

        $pendingLabOrders = OrderItem::whereHas('service', $labCategoryFilter)
            ->where('status', 'Pending')
            ->with('order.patient', 'service', 'labResult')
            ->latest()
            ->get();

        $collectedLabOrders = OrderItem::whereHas('service', $labCategoryFilter)
            ->where('status', 'Sample Collected')
            ->with('order.patient', 'service', 'labResult')
            ->latest()
            ->get();

        $resultsReadyOrders = OrderItem::whereHas('service', $labCategoryFilter)
            ->where('status', 'Result Ready')
            ->with('order.patient', 'service', 'labResult')
            ->latest()
            ->get();

        $completedOrders = OrderItem::whereHas('service', $labCategoryFilter)
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
