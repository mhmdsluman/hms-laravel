<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\InsuranceContract;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BillController extends Controller
{
    public function index(): Response
    {
        $unbilledAppointments = Appointment::where('status', 'Completed')
            ->whereDoesntHave('bill')
            ->with('patient', 'clinician')
            ->latest('appointment_time')
            ->get();

        $bills = Bill::with(['patient'])->latest()->paginate(10);

        return Inertia::render('Billing/Index', [
            'unbilledAppointments' => $unbilledAppointments,
            'bills' => $bills,
        ]);
    }

    public function show(Bill $bill): Response
    {
        $bill->load(['patient', 'items.service']);
        return Inertia::render('Billing/Show', ['bill' => $bill]);
    }

    public function store(Request $request, Appointment $appointment)
    {
        $appointment->load(['patient.insurancePolicies.provider', 'orders.items.service']);

        if ($appointment->bill) {
            return redirect()->back()->with('error', 'A bill already exists for this appointment.');
        }

        $primaryPolicy = $appointment->patient->insurancePolicies->firstWhere('is_primary', true);
        $totalAmount = 0;
        $totalInsuranceAmount = 0;
        $totalPatientCoPay = 0;
        $billItemsData = [];

        $servicesToBill = $appointment->orders->flatMap(fn ($order) => $order->items->pluck('service'));
        $consultationService = Service::where('name', 'Consultation')->first();
        if ($consultationService) {
            $servicesToBill->push($consultationService);
        }

        foreach ($servicesToBill as $service) {
            $itemPrice = $service->price;
            $itemInsuranceAmount = 0;
            $itemPatientCoPay = $itemPrice;

            if ($primaryPolicy) {
                $contract = InsuranceContract::where('insurance_provider_id', $primaryPolicy->insurance_provider_id)
                    ->where('service_id', $service->id)
                    ->first();

                if ($contract) {
                    $itemInsuranceAmount = $itemPrice * ($contract->coverage_percentage / 100);
                    $itemPatientCoPay = $itemPrice - $itemInsuranceAmount;
                }
            }

            $totalAmount += $itemPrice;
            $totalInsuranceAmount += $itemInsuranceAmount;
            $totalPatientCoPay += $itemPatientCoPay;

            $billItemsData[] = [
                'service_id' => $service->id,
                'quantity' => 1,
                'unit_price' => $itemPrice,
                'total_price' => $itemPrice,
                'insurance_amount' => $itemInsuranceAmount,
                'patient_co_pay' => $itemPatientCoPay,
            ];
        }

        DB::transaction(function () use ($appointment, $totalAmount, $totalInsuranceAmount, $totalPatientCoPay, $billItemsData) {
            $bill = Bill::create([
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'total_amount' => $totalAmount,
                'insurance_amount' => $totalInsuranceAmount,
                'patient_co_pay' => $totalPatientCoPay,
                'status' => 'Unpaid',
            ]);

            if (!empty($billItemsData)) {
                $bill->items()->createMany($billItemsData);
            }
        });

        return redirect()->route('billing.index')->with('success', 'Bill generated successfully.');
    }

    public function recordPayment(Request $request, Bill $bill)
{
    // Re-evaluate balance in case something changed on the client
    $balanceDue = $bill->balance_due;

    $validated = $request->validate([
        'paid_amount' => 'required|numeric|min:0.01|max:' . $balanceDue,
    ]);

    try {
        $bill->paid_amount = (float) $bill->paid_amount + (float) $validated['paid_amount'];

        // Update status using balance_due accessor
        if ($bill->balance_due <= 0) {
            $bill->status = 'Paid';
        } else {
            $bill->status = 'Unpaid';
        }

        $bill->save();
    } catch (\Throwable $e) {
        // For AJAX return JSON, else redirect back with error
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Failed to record payment.', 'error' => $e->getMessage()], 500);
        }

        return redirect()->back()->with('error', 'Failed to record payment: ' . $e->getMessage());
    }

    // Return JSON for AJAX, or redirect for normal form
    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully.',
            'balance_due' => $bill->balance_due,
            'status' => $bill->status,
            'redirect' => route('billing.show', $bill->id),
        ]);
    }

    return redirect()->route('billing.show', $bill->id)->with('success', 'Payment recorded successfully.');
}

    public function applyDiscount(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'discount_amount' => 'required|numeric|min:0|max:' . $bill->balance_due,
            'discount_reason' => 'required|string|max:255',
        ]);

        $bill->update($validated);

        // Update status using balance_due accessor
        if ($bill->balance_due <= 0) {
            $bill->status = 'Paid';
        } else {
            $bill->status = 'Unpaid';
        }

        $bill->save();

return redirect()->route('billing.show', $bill->id)
    ->with('success', 'Discount applied successfully.');
    }

    public function destroy(Bill $bill)
    {
        if ($bill->status === 'Paid') {
            return redirect()->back()->with('error', 'Cannot void a bill that has been paid.');
        }

        $bill->delete();

        return redirect()->route('billing.index')->with('success', 'Bill has been voided successfully.');
    }
}
