<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\BillableEvent;
use App\Models\Order;
use App\Models\Service;
use App\Models\LabTest;
use App\Services\ClinicalDecisionSupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\OrderItem;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $cdss;

    public function __construct(ClinicalDecisionSupportService $cdss)
    {
        $this->cdss = $cdss;
    }
    /**
     * Store a new order for an appointment and update/create the associated bill.
     *
     * This method merges two flows:
     *  - create the clinical order and its items
     *  - create / update the bill for the appointment, adding the ordered services
     *
     * Safety: performs a restriction check for formulary items and runs all DB writes inside a transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment   $appointment
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'required|exists:services,id',
        ]);

        // Load the initial services from the request
        $initialServices = Service::find($validated['service_ids']);

        // --- Panel Expansion Logic ---
        $finalServiceIds = [];
        $panelChildTestNames = [];

        // First pass: identify panels and collect child test names
        foreach ($initialServices as $service) {
            $labTest = LabTest::where('name', $service->name)->first();

            if ($labTest && $labTest->is_panel) {
                $labTest->load('tests');
                foreach ($labTest->tests as $childTest) {
                    $panelChildTestNames[] = $childTest->name;
                }
            } else {
                $finalServiceIds[] = $service->id;
            }
        }

        if (!empty($panelChildTestNames)) {
            $childServices = Service::whereIn('name', array_unique($panelChildTestNames))->pluck('id')->toArray();
            $finalServiceIds = array_merge($finalServiceIds, $childServices);
        }

        $finalServiceIds = array_unique($finalServiceIds);
        $servicesToProcess = Service::find($finalServiceIds);
        // --- End Panel Expansion Logic ---

        // Restriction check (formulary) on the final, expanded list of services
        foreach ($servicesToProcess as $service) {
            if ($service->formulary_status === 'Restricted') {
                throw ValidationException::withMessages([
                    'service_ids' => "The medication/service '{$service->name}' is restricted."
                ]);
            }
        }

        // CDSS Checks (Drug Interactions and Allergies) on the final, expanded list
        $patient = $appointment->patient;
        $orderedRxcuis = $servicesToProcess->whereNotNull('rxcui')->pluck('rxcui')->toArray();

        $patientMedications = OrderItem::whereHas('order', function ($q) use ($patient) {
            $q->where('patient_id', $patient->id);
        })->whereHas('service', function ($q) {
            $q->whereNotNull('rxcui');
        })->with('service')->get()->pluck('service.rxcui')->filter()->unique()->toArray();
        $allRxcuis = array_merge($orderedRxcuis, $patientMedications);

        if (count($allRxcuis) > 1) {
            $interactionResponse = $this->cdss->checkDrugInteractions($allRxcuis);
            if ($interactionResponse->successful() && !empty($interactionResponse->json())) {
                Log::warning('Drug-drug interactions found for patient ' . $patient->id, $interactionResponse->json());
            }
        }

        $patientAllergies = collect($patient->allergies ?? []);
        foreach ($servicesToProcess as $service) {
            if ($patientAllergies->contains('name', $service->name)) {
                Log::warning('Patient ' . $patient->id . ' has a known allergy to ' . $service->name);
            }
        }

        // --- Database Transaction ---
        DB::transaction(function () use ($appointment, $finalServiceIds) {
            $order = Order::create([
                'patient_id'         => $appointment->patient_id,
                'appointment_id'     => $appointment->id,
                'ordered_by_user_id' => Auth::id(),
                'status'             => 'Pending',
            ]);

            // CORRECTED LOOP: Iterate over the final, expanded list of service IDs
            foreach ($finalServiceIds as $serviceId) {
                $item = $order->items()->create([
                    'service_id'          => $serviceId,
                    'status'              => 'Pending',
                    'placer_order_number' => 'ORD-' . Str::upper(Str::random(5)) . '-' . $order->id,
                ]);

                BillableEvent::create([
                    'patient_id' => $appointment->patient_id,
                    'service_id' => $serviceId,
                    'status'     => 'Pending',
                ]);

                // Auto-create MAR entry if patient is admitted and service is a pharmacy item
                $patient = $appointment->patient()->with('currentAdmission')->first();
                if ($patient && $patient->currentAdmission && $item->service && $item->service->department === 'Pharmacy') {
                    if (method_exists($patient->currentAdmission, 'medicationAdministrations')) {
                        $patient->currentAdmission->medicationAdministrations()->create([
                            'order_item_id'  => $item->id,
                            'scheduled_time' => now(),
                            'status'         => 'Due',
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->back()
            ->with('success', 'Order placed and bill updated successfully.');
    }
}
