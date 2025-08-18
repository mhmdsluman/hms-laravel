<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
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

            // Billing logic: find or create a bill for this appointment
            $bill = Bill::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_id'   => $appointment->patient_id,
                    'total_amount' => 0,
                    'status'       => 'Unpaid',
                ]
            );

            // Add newly ordered services to the bill.
            // Prefer high-level addService() if model provides it, otherwise create bill items directly.
            foreach ($services as $service) {
                if (method_exists($bill, 'addService') && is_callable([$bill, 'addService'])) {
                    // If Bill::addService handles quantity/pricing/relationships internally
                    $bill->addService($service);
                } elseif (method_exists($bill, 'items')) {
                    // Fallback: create bill item record assuming bill->items() relation exists
                    $unitPrice = $service->price ?? 0;
                    $bill->items()->create([
                        'service_id'  => $service->id,
                        'quantity'    => 1,
                        'unit_price'  => $unitPrice,
                        'total_price' => $unitPrice * 1,
                    ]);
                } else {
                    // As a last resort, accumulate into total_amount (will be persisted below)
                    $bill->total_amount += ($service->price ?? 0);
                }
            }

            // Recalculate totals: prefer model's recalculateTotals() if available
            if (method_exists($bill, 'recalculateTotals') && is_callable([$bill, 'recalculateTotals'])) {
                $bill->recalculateTotals();
            } else {
                // Compute sum from related bill items if relation exists
                if (method_exists($bill, 'items')) {
                    $sum = (float) $bill->items()->sum('total_price');
                    $bill->total_amount = $sum;
                    $bill->save();
                } else {
                    // If no items relation, ensure we persist whatever total_amount we have
                    $bill->save();
                }
            }

            // If the bill was previously marked Paid or Void, reset to Unpaid because new items were added
            if (in_array($bill->status, ['Paid', 'Void'])) {
                $bill->update(['status' => 'Unpaid']);
            }
        });

        return redirect()
            ->back()
            ->with('success', 'Order placed and bill updated successfully.');
    }
}
