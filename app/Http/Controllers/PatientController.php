<?php

namespace App\Http\Controllers;

use App\Models\Claim; // <-- Add this import
use App\Models\InsuranceProvider;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Patient::query();

        $query->when($request->input('search'), function ($query, $search) {
            $query->where('uhid', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
        });

        $patients = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Patients/Create', [
            'providers' => InsuranceProvider::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|string',
            'primary_phone_country_code' => 'required|string|max:5',
            'primary_phone' => 'required|string|max:20',
            'email' => ['nullable', 'email', Rule::unique('patients')->whereNull('deleted_at')],
            'addresses' => 'present|array',
            'addresses.*.type' => 'required|string',
            'addresses.*.street' => 'required|string|max:255',
            'addresses.*.city' => 'required|string|max:255',
            'addresses.*.state' => 'nullable|string|max:255',
            'addresses.*.postal_code' => 'nullable|string|max:255',
            'addresses.*.country' => 'required|string|max:255',
            'insurance_provider_id' => 'nullable|exists:insurance_providers,id',
            'policy_number' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $countryCode = Str::startsWith($validatedData['primary_phone_country_code'], '+')
            ? $validatedData['primary_phone_country_code']
            : '+' . $validatedData['primary_phone_country_code'];
        $localPhone = ltrim($validatedData['primary_phone'], '0');
        $fullPhoneNumber = $countryCode . $localPhone;
        $defaultPassword = Hash::make($localPhone);

        $addresses = $validatedData['addresses'];
        foreach ($addresses as &$address) {
            if (empty($address['postal_code'])) {
                $address['postal_code'] = 'N/A';
            }
        }

        $patient = Patient::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'date_of_birth' => $validatedData['date_of_birth'],
            'gender' => $validatedData['gender'],
            'primary_phone_country_code' => $countryCode,
            'primary_phone' => $fullPhoneNumber,
            'email' => $validatedData['email'],
            'addresses' => $addresses,
            'patient_portal_password_hash' => $defaultPassword,
            'created_by_user_id' => Auth::id(),
            'updated_by_user_id' => Auth::id(),
            'uhid' => 'TEMP-' . time(),
        ]);

        $patient->uhid = 'HMS-A' . str_pad($patient->id, 7, '0', STR_PAD_LEFT);
        $patient->save();

        if ($validatedData['insurance_provider_id'] && $validatedData['policy_number']) {
            $patient->insurancePolicies()->create([
                'insurance_provider_id' => $validatedData['insurance_provider_id'],
                'policy_number' => $validatedData['policy_number'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
            ]);
        }

        return redirect()->route('patients.create')->with('success', 'Patient registered successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient): Response
    {
        $patient->load([
            'vitals.recorder',
            'orders.items.labResult',
            'orders.items.radiologyReport.reporter',
            'orders.items.operativeNote.surgeon',
            'orders.items.service',
            'admissions.nursingNotes.nurse',
            'admissions.shiftHandovers.outgoingNurse',
            'insurancePolicies.provider',
        ]);

        return Inertia::render('Patients/Show', [
            'patient' => $patient,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient): Response
    {
        $patient->load('insurancePolicies.provider');
        return Inertia::render('Patients/Edit', [
            'patient' => $patient,
            'providers' => InsuranceProvider::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|string',
            'primary_phone_country_code' => 'required|string|max:5',
            'primary_phone' => 'required|string|max:20',
            'email' => ['nullable', 'email', Rule::unique('patients')->ignore($patient->id)->whereNull('deleted_at')],
            'addresses' => 'present|array',
            'addresses.*.street' => 'required|string|max:255',
            // ... other address fields
            'insurance_provider_id' => 'nullable|exists:insurance_providers,id',
            'policy_number' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $countryCode = Str::startsWith($validatedData['primary_phone_country_code'], '+')
            ? $validatedData['primary_phone_country_code']
            : '+' . $validatedData['primary_phone_country_code'];
        $localPhone = ltrim($validatedData['primary_phone'], '0');
        $fullPhoneNumber = $countryCode . $localPhone;

        $addresses = $validatedData['addresses'];
        foreach ($addresses as &$address) {
            if (empty($address['postal_code'])) {
                $address['postal_code'] = 'N/A';
            }
        }

        $patient->update([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'date_of_birth' => $validatedData['date_of_birth'],
            'gender' => $validatedData['gender'],
            'primary_phone_country_code' => $countryCode,
            'primary_phone' => $fullPhoneNumber,
            'email' => $validatedData['email'],
            'addresses' => $addresses,
            'updated_by_user_id' => Auth::id(),
        ]);

        if ($request->input('insurance_provider_id') && $request->input('policy_number')) {
            $patient->insurancePolicies()->updateOrCreate(
                ['is_primary' => true],
                [
                    'insurance_provider_id' => $request->input('insurance_provider_id'),
                    'policy_number' => $request->input('policy_number'),
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date'),
                ]
            );
        }

        return redirect()->route('patients.show', $patient->id)->with('success', 'Patient information updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();
        return redirect()->route('patients.index')->with('success', 'Patient record deleted successfully.');
    }

    /**
     * Check for duplicate email or phone number in real-time.
     */
    public function checkDuplicate(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        $patientId = $request->input('patient_id', null);

        if (!$field || !$value) {
            return response()->json(['is_duplicate' => false]);
        }

        $query = Patient::where($field, $value)->whereNull('deleted_at');

        if ($patientId) {
            $query->where('id', '!=', $patientId);
        }

        return response()->json(['is_duplicate' => $query->exists()]);
    }
}
