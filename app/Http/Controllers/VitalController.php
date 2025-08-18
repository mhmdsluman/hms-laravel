<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Vital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class VitalController extends Controller
{
    /**
     * Show the form for creating a new vitals record for an appointment.
     */
    public function create(Appointment $appointment): Response
    {
        // Load the patient relationship to display their name on the form
        $appointment->load('patient');

        return Inertia::render('Vitals/Create', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * Store a newly created vitals record in storage.
     */
    public function store(Request $request, Appointment $appointment)
    {
        $validatedData = $request->validate([
            'bp_systolic' => 'nullable|integer|min:0',
            'bp_diastolic' => 'nullable|integer|min:0',
            'heart_rate' => 'nullable|integer|min:0',
            'respiratory_rate' => 'nullable|integer|min:0',
            'temperature_celsius' => 'nullable|numeric|min:0',
            'oxygen_saturation' => 'nullable|integer|min:0|max:100',
            'weight_kg' => 'nullable|numeric|min:0',
            'height_cm' => 'nullable|integer|min:0',
            'pain_score' => 'nullable|integer|min:0|max:10',
        ]);

        // Create the vitals record
        Vital::create([
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'recorded_by_user_id' => Auth::id(),
            ...$validatedData, // Spread the validated data into the create method
        ]);

        // Redirect back to the appointments calendar with a success message
        return redirect()->route('appointments.index')->with('success', 'Vitals recorded successfully.');
    }
}
