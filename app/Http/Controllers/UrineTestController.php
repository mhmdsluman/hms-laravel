<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUrineTestRequest;
use App\Models\UrineTest;
use App\Services\UrineCalculator;
use App\Services\UrineRangeMatcher;
use App\Models\Patient;
use Illuminate\Http\Request;

class UrineTestController extends Controller
{
    public function store(StoreUrineTestRequest $request)
    {
        $validated = $request->validated();
        $patient = Patient::findOrFail($validated['patient_id']);
        $calculationResult = UrineCalculator::calculate($validated['values']);

        $flags = UrineRangeMatcher::computeFlagsForTest(
            $patient,
            $validated['values'],
            $calculationResult['calculated']
        );

        $urineTest = new UrineTest();
        $urineTest->patient_id = $validated['patient_id'];
        $urineTest->values = $validated['values'];
        $urineTest->calculated = $calculationResult['calculated'];
        $urineTest->flags = $flags;
        $urineTest->save();

        return redirect()->back()->with('success', 'Urine Test saved successfully.');
    }

    public function update(StoreUrineTestRequest $request, UrineTest $urineTest)
    {
        $validated = $request->validated();
        $patient = Patient::findOrFail($urineTest->patient_id);
        $calculationResult = UrineCalculator::calculate($validated['values']);

        $flags = UrineRangeMatcher::computeFlagsForTest(
            $patient,
            $validated['values'],
            $calculationResult['calculated']
        );

        $urineTest->values = $validated['values'];
        $urineTest->calculated = $calculationResult['calculated'];
        $urineTest->flags = $flags;
        $urineTest->save();

        return redirect()->back()->with('success', 'Urine Test updated successfully.');
    }
}
