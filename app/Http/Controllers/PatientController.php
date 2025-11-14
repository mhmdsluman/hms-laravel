<?php

namespace App\Http\Controllers;

use App\Models\Claim; // <-- Keep if used elsewhere
use App\Models\InsuranceProvider;
use App\Models\Patient;
use App\Models\LabOrderResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            'photo' => 'nullable|image|max:2048',
        ]);

        $countryCode = Str::startsWith($validatedData['primary_phone_country_code'], '+')
            ? $validatedData['primary_phone_country_code']
            : '+' . $validatedData['primary_phone_country_code'];

        // Use ltrim to remove leading zeros from the local phone number
        $localPhone = ltrim($validatedData['primary_phone'], '0');
        $fullPhoneNumber = $countryCode . $localPhone;

        // Use the local phone number (without country code) for the password
        $defaultPassword = Hash::make($localPhone);

        $addresses = $validatedData['addresses'];
        foreach ($addresses as &$address) {
            if (empty($address['postal_code'])) {
                $address['postal_code'] = 'N/A';
            }
        }

        // Handle File Upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('patient_photos', 'public');
        }

        $patient = Patient::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'date_of_birth' => $validatedData['date_of_birth'],
            'gender' => $validatedData['gender'],
            'primary_phone_country_code' => $countryCode,
            'primary_phone' => $fullPhoneNumber,
            'email' => $validatedData['email'] ?? null,
            'addresses' => $addresses,
            'patient_portal_password_hash' => $defaultPassword,
            'photo_capture_path' => $photoPath,
            'created_by_user_id' => Auth::id(),
            'updated_by_user_id' => Auth::id(),
            'uhid' => 'TEMP-' . time(),
        ]);

        $patient->uhid = 'HMS-A' . str_pad($patient->id, 7, '0', STR_PAD_LEFT);
        $patient->save();

        if (!empty($validatedData['insurance_provider_id']) && !empty($validatedData['policy_number'])) {
            $patient->insurancePolicies()->create([
                'insurance_provider_id' => $validatedData['insurance_provider_id'],
                'policy_number' => $validatedData['policy_number'],
                'start_date' => $validatedData['start_date'] ?? null,
                'end_date' => $validatedData['end_date'] ?? null,
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
            'labOrders.results.test',
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
            'email' => $validatedData['email'] ?? null,
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

        // Only allow certain fields for safety
        if (!in_array($field, ['email', 'primary_phone', 'uhid'])) {
            return response()->json(['is_duplicate' => false]);
        }

        $query = Patient::where($field, $value)->whereNull('deleted_at');

        if ($patientId) {
            $query->where('id', '!=', $patientId);
        }

        return response()->json(['is_duplicate' => $query->exists()]);
    }

    /**
     * Simple patient search endpoint used by the frontend autocomplete.
     * GET /patients/search?query=...
     */
    public function getAbnormalSummary(Patient $patient)
    {
        $abnormalResults = LabOrderResult::where('is_abnormal', true)
            ->whereHas('labOrder', function ($query) use ($patient) {
                $query->where('patient_id', $patient->id);
            })
            ->with('test')
            ->orderBy('created_at')
            ->get();

        $summary = [];
        $groupedResults = $abnormalResults->groupBy('lab_test_id');

        foreach ($groupedResults as $testId => $results) {
            if (count($results) > 2) { // Only summarize if there are more than 2 abnormal results
                $firstAbnormal = $results->first();
                $lastAbnormal = $results->last();

                // Simple direction check, can be improved
                $direction = $lastAbnormal->result > $firstAbnormal->result ? 'increased' : 'decreased';

                $summary[] = [
                    'test' => $firstAbnormal->test,
                    'direction' => $direction,
                    'first_abnormal_date' => $firstAbnormal->created_at,
                ];
            }
        }

        return response()->json($summary);
    }

    public function search(Request $request)
    {
        // validate to avoid unexpected types / large payloads
        $data = $request->validate([
            'query' => 'nullable|string|max:100',
        ]);

        $raw = $data['query'] ?? '';
        // remove control chars, collapse whitespace, trim
        $query = trim(preg_replace('/\s+/', ' ', preg_replace('/[\x00-\x1F\x7F]+/', ' ', $raw)));

        try {
            if ($query === '') {
                return response()->json([], 200);
            }

            // Escape backslash, percent and underscore for safe LIKE queries
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
            $searchTerms = array_filter(explode(' ', $escaped), fn($t) => $t !== '');

            $patientsQuery = Patient::query()
                ->where(function ($q) use ($escaped, $searchTerms) {
                    // Full string searches against UHID and phone using ESCAPE '\' (works in MySQL)
                    $q->whereRaw("uhid LIKE ? ESCAPE '\\\\'", ["%{$escaped}%"])
                      ->orWhereRaw("primary_phone LIKE ? ESCAPE '\\\\'", ["%{$escaped}%"]);

                    // For names: require each term (AND) to match either first_name or last_name
                    if (count($searchTerms) > 0) {
                        $q->orWhere(function ($nameQuery) use ($searchTerms) {
                            foreach ($searchTerms as $term) {
                                $nameQuery->where(function ($sub) use ($term) {
                                    $sub->whereRaw("first_name LIKE ? ESCAPE '\\\\'", ["%{$term}%"])
                                        ->orWhereRaw("last_name LIKE ? ESCAPE '\\\\'", ["%{$term}%"]);
                                });
                            }
                        });
                    }
                });

            $patients = $patientsQuery
                ->limit(30)
                ->get(['id', 'first_name', 'last_name', 'uhid', 'primary_phone']);

            return response()->json($patients, 200);
        } catch (\Throwable $e) {
            // log full exception so you can inspect stacktrace in storage/logs/laravel.log
            Log::error('Patient search failed', [
                'query_raw' => $raw ?? null,
                'query_sanitized' => $query ?? null,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Server error while searching patients.'], 500);
        }
    }
}
