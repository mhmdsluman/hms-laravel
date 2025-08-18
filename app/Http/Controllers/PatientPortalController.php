<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PatientPortalController extends Controller
{
    // ... dashboard, appointments, and bills methods remain the same ...
    public function dashboard(): Response
    {
        $patient = Patient::where('email', Auth::user()->email)->firstOrFail();
        $patient->load([
            'appointments' => function ($query) {
                $query->with('clinician')->where('appointment_time', '>=', now())->orderBy('appointment_time')->limit(5);
            },
            'orders.items.labResult',
            'orders.items.service'
        ]);
        return Inertia::render('Portal/Dashboard', [ 'patient' => $patient ]);
    }

    public function appointments(): Response
    {
        $patient = Patient::where('email', Auth::user()->email)->firstOrFail();
        return Inertia::render('Portal/MyAppointments', [
            'appointments' => $patient->appointments()->with('clinician')->latest('appointment_time')->paginate(10),
        ]);
    }

    public function bills(): Response
    {
        $patient = Patient::where('email', Auth::user()->email)->firstOrFail();
        return Inertia::render('Portal/MyBills', [
            'bills' => $patient->bills()->latest()->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new appointment request.
     */
    public function createAppointment(): Response
    {
        return Inertia::render('Portal/RequestAppointment', [
            'clinicians' => User::where('role', 'clinician')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
