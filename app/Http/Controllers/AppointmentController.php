<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    /**
     * Display the appointment scheduling calendar.
     */
    public function index(Request $request): Response
    {
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);
        $date = Carbon::createFromDate($year, $month, 1);

        $appointments = Appointment::with(['patient', 'clinician'])
            ->whereYear('appointment_time', $year)
            ->whereMonth('appointment_time', $month)
            ->orderBy('appointment_time')
            ->get();

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'clinicians' => User::where('role', 'clinician')->orderBy('name')->get(['id', 'name']),
            'currentDate' => [
                'month' => $date->month,
                'year' => $date->year,
                'monthName' => $date->format('F'),
            ],
        ]);
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'clinician_id' => 'required|exists:users,id',
            'appointment_time' => 'required|date',
            'reason_for_visit' => 'nullable|string|max:1000',
        ]);

        // Input from datetime-local is like '2025-11-01T13:00'
        // Use createFromFormat with timezone; fallback to parse if format mismatch
        try {
            $appointmentTime = Carbon::createFromFormat('Y-m-d\TH:i', $validatedData['appointment_time'], config('app.timezone'));
        } catch (\Throwable $e) {
            // fallback to Carbon parse
            $appointmentTime = Carbon::parse($validatedData['appointment_time'], config('app.timezone'));
        }

        DB::transaction(function () use ($validatedData, $appointmentTime) {
            $appointment = Appointment::create([
                'patient_id' => $validatedData['patient_id'],
                'clinician_id' => $validatedData['clinician_id'],
                'appointment_time' => $appointmentTime,
                'reason_for_visit' => $validatedData['reason_for_visit'] ?? null,
                'status' => 'Scheduled',
                'created_by_user_id' => Auth::id(),
            ]);

            // Create a new bill for the appointment
            $bill = Bill::create([
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'total_amount' => 0,
                'status' => 'Draft', // Starts as Draft until a service is added
            ]);

            // Add the base consultation fee if available
            $consultationService = Service::where('name', 'Consultation')->first();
            if ($consultationService) {
                // assuming addService is defined on Bill model
                $bill->addService($consultationService);
                if (method_exists($bill, 'recalculateTotals')) {
                    $bill->recalculateTotals();
                }
                $bill->update(['status' => 'Unpaid']);
            }
        });

        return redirect()->route('appointments.index')->with('success', 'Appointment booked successfully.');
    }

    /**
     * Update the status of an appointment.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:Completed,Cancelled',
        ]);

        $appointment->update(['status' => $validated['status']]);

        if ($validated['status'] === 'Cancelled' && $appointment->bill) {
            $appointment->bill->update(['status' => 'Void']);
        }

        if ($validated['status'] === 'Completed') {
            return redirect()->back()->with('success', 'Appointment for ' . $appointment->patient->first_name . ' ' . $appointment->patient->last_name . ' is completed.');
        }

        return redirect()->back()->with('success', 'Appointment status updated.');
    }

    /**
     * Store a new appointment request from the patient portal.
     */
    public function storeRequest(Request $request)
    {
        $validatedData = $request->validate([
            'clinician_id' => 'required|exists:users,id',
            'appointment_time' => 'required|date|after:now',
            'reason_for_visit' => 'nullable|string|max:1000',
        ]);

        try {
            $appointmentTime = Carbon::createFromFormat('Y-m-d\TH:i', $validatedData['appointment_time'], config('app.timezone'));
        } catch (\Throwable $e) {
            $appointmentTime = Carbon::parse($validatedData['appointment_time'], config('app.timezone'));
        }

        $patient = Patient::where('email', Auth::user()->email)->firstOrFail();

        Appointment::create([
            'patient_id' => $patient->id,
            'clinician_id' => $validatedData['clinician_id'],
            'appointment_time' => $appointmentTime,
            'reason_for_visit' => $validatedData['reason_for_visit'],
            'status' => 'Requested',
            'created_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('portal.appointments')->with('success', 'Appointment requested successfully. You will be notified once it is confirmed.');
    }
}
