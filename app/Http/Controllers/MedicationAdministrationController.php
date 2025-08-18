<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\MedicationAdministration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MedicationAdministrationController extends Controller
{
    /**
     * Display the Medication Administration Record for a specific admission.
     */
    public function show(Admission $admission): Response
    {
        // Eager load all necessary data for the MAR screen
        $admission->load([
            'patient',
            'bed',
            'medicationAdministrations.orderItem.service',
            'medicationAdministrations.administrator'
        ]);

        return Inertia::render('IPD/Mar', [
            'admission' => $admission,
        ]);
    }

    /**
     * Update the specified resource in storage (record an administration).
     */
    public function update(Request $request, MedicationAdministration $medicationAdministration)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $medicationAdministration->update([
            'status' => 'Administered',
            'administered_time' => now(),
            'administered_by_user_id' => Auth::id(),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->back()->with('success', 'Medication administration recorded.');
    }
}
