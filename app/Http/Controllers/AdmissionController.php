<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdmissionController extends Controller
{
    /**
     * Show the form for creating a new admission.
     */
    public function create(Request $request): Response
    {
        $request->validate(['bed_id' => 'required|exists:beds,id']);

        return Inertia::render('IPD/Admit', [
            'bed' => Bed::findOrFail($request->input('bed_id')),
            // **MODIFIED**: Added 'date_of_birth' to the query
            'patients' => Patient::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'date_of_birth']),
            'clinicians' => User::where('role', 'clinician')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created admission in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'patient_id' => 'required|exists:patients,id',
            'admitting_doctor_id' => 'required|exists:users,id',
            'reason_for_admission' => 'required|string',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);

        if ($bed->status !== 'Available') {
            return back()->withErrors(['bed_id' => 'This bed is not available.'])->withInput();
        }

        DB::transaction(function () use ($validated, $bed) {
            Admission::create([
                'patient_id' => $validated['patient_id'],
                'bed_id' => $validated['bed_id'],
                'admitting_doctor_id' => $validated['admitting_doctor_id'],
                'reason_for_admission' => $validated['reason_for_admission'],
                'admission_time' => now(),
                'status' => 'Admitted',
            ]);
            $bed->update(['status' => 'Occupied']);
        });

        return redirect()->route('ipd.index')->with('success', 'Patient admitted successfully.');
    }
}
