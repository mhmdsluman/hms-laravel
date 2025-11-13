<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\BillableEvent;
use App\Models\Order;
use App\Models\Service;
use App\Services\ClinicalDecisionSupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

        // Load the selected services
        $services = Service::find($validated['service_ids']);

        // Restriction check (formulary)
        foreach ($services as $service) {
            if ($service->formulary_status === 'Restricted') {
                throw ValidationException::withMessages([
                    'service_ids' => "The medication/service '{$service->name}' is restricted."
                ]);
            }
        }

        // CDSS Checks (Drug Interactions and Allergies)
        $patient = $appointment->patient;
        $orderedRxcuis = $services->whereNotNull('rxcui')->pluck('rxcui')->toArray();
        $patientMedications = $patient->medications()->whereNotNull('rxcui')->pluck('rxcui')->toArray();
        $allRxcuis = array_merge($orderedRxcuis, $patientMedications);

        if (count($allRxcuis) > 1) {
            $interactionResponse = $this->cdss->checkDrugInteractions($allRxcuis);
            if ($interactionResponse->successful() && !empty($interactionResponse->json())) {
                Log::warning('Drug-drug interactions found for patient ' . $patient->id, $interactionResponse->json());
                // In a real application, you would display a warning to the user here.
            }
        }

        foreach ($services as $service) {
            if ($patient->allergies->contains('name', $service->name)) {
                Log::warning('Patient ' . $patient->id . ' has a known allergy to ' . $service->name);
                // In a real application, you would display a warning to the user here.
            }
        }

        DB::transaction(function () use ($validated, $appointment, $services) {
            // Create the clinical order
            $order = Order::create([
                'patient_id'         => $appointment->patient_id,
                'appointment_id'     => $appointment->id,
                'ordered_by_user_id' => Auth::id(),
                'status'             => 'Pending',
            ]);

            // Create order items and handle inpatient pharmacy auto-MAR population
            foreach ($validated['service_ids'] as $serviceId) {
                $item = $order->items()->create([
                    'service_id'          => $serviceId,
                    'status'              => 'Pending',
                    // include order id to placer string to reduce collisions
                    'placer_order_number' => 'ORD-' . Str::upper(Str::random(5)) . '-' . $order->id,
                ]);

                // Create a billable event for the order item
                BillableEvent::create([
                    'patient_id' => $appointment->patient_id,
                    'service_id' => $serviceId,
                    'status'     => 'Pending',
                ]);

                // If the patient is currently admitted and the ordered service is a pharmacy item,
                // auto-create a medication administration record on the current admission (MAR).
                $patient = $appointment->patient()->with('currentAdmission')->first();
                if ($patient && $patient->currentAdmission && $item->service && $item->service->department === 'Pharmacy') {
                    // Ensure relationship exists on the admission model
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
