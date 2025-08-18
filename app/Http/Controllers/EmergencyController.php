<?php

namespace App\Http\Controllers;

use App\Models\EmergencyVisit;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EmergencyController extends Controller
{
    /**
     * Display the Emergency Department dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Emergency/Index', [
            'visits' => EmergencyVisit::with('patient')
                ->whereIn('status', ['Waiting', 'In-Progress'])
                ->orderBy('arrival_time')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Emergency/Register');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'gender' => 'required|string',
            'primary_phone' => 'required|string|unique:patients,primary_phone',
            'chief_complaint' => 'required|string|max:255',
        ]);

        // For a true ED workflow, we create a patient record on the fly.
        // A more advanced version would search for an existing patient first.
        $patient = Patient::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'primary_phone' => $validated['primary_phone'],
            'primary_phone_country_code' => '+249', // Default
            'uhid' => 'HMS-ER-' . Str::upper(Str::random(6)),
            'created_by_user_id' => Auth::id(),
        ]);

        EmergencyVisit::create([
            'patient_id' => $patient->id,
            'arrival_time' => now(),
            'chief_complaint' => $validated['chief_complaint'],
            'status' => 'Waiting',
            'registered_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('emergency.index')->with('success', 'Patient registered in Emergency successfully.');
    }
}
