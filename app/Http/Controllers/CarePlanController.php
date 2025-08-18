<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\CarePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CarePlanController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Admission $admission): Response
    {
        // Eager load the patient and the existing care plan with its items
        $admission->load(['patient', 'carePlan.items']);

        return Inertia::render('IPD/CarePlan/Builder', [
            'admission' => $admission,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Admission $admission)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.nursing_diagnosis' => 'required|string',
            'items.*.goals' => 'required|string',
            'items.*.interventions' => 'required|string',
        ]);

        DB::transaction(function () use ($validated, $admission) {
            // Create or find the main care plan for this admission
            $carePlan = CarePlan::firstOrCreate(
                ['admission_id' => $admission->id],
                ['created_by_user_id' => Auth::id()]
            );

            // Delete old items and create the new ones to sync the plan
            $carePlan->items()->delete();
            $carePlan->items()->createMany($validated['items']);
        });

        return redirect()->route('nursing.index')->with('success', 'Care plan saved successfully.');
    }
}
