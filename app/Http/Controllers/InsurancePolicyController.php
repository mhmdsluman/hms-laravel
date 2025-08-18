<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\InsuranceProvider;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsurancePolicyController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $request->validate(['patient_id' => 'required|exists:patients,id']);

        return Inertia::render('Patients/Insurance/Create', [
            'patient' => Patient::findOrFail($request->input('patient_id')),
            'providers' => InsuranceProvider::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'insurance_provider_id' => 'required|exists:insurance_providers,id',
            'policy_number' => 'required|string|max:255',
            'group_number' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        InsurancePolicy::create($validated);

        return redirect()->route('patients.show', $validated['patient_id'])->with('success', 'Insurance policy added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InsurancePolicy $insurancePolicy): Response
    {
        $insurancePolicy->load('patient');
        return Inertia::render('Patients/Insurance/Edit', [
            'policy' => $insurancePolicy,
            'providers' => InsuranceProvider::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InsurancePolicy $insurancePolicy)
    {
        $validated = $request->validate([
            'insurance_provider_id' => 'required|exists:insurance_providers,id',
            'policy_number' => 'required|string|max:255',
            'group_number' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $insurancePolicy->update($validated);

        return redirect()->route('patients.show', $insurancePolicy->patient_id)->with('success', 'Insurance policy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InsurancePolicy $insurancePolicy)
    {
        $patientId = $insurancePolicy->patient_id;
        $insurancePolicy->delete();

        return redirect()->route('patients.show', $patientId)->with('success', 'Insurance policy deleted successfully.');
    }
}
