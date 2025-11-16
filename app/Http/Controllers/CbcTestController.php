<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCbcTestRequest;
use App\Models\CbcTest;
use App\Models\Patient;
use App\Services\CbcCalculator;
use App\Services\CbcRangeMatcher;
use Illuminate\Http\Request;

class CbcTestController extends Controller
{
    public function store(StoreCbcTestRequest $request)
    {
        $validated = $request->validated();
        $patient = Patient::findOrFail($validated['patient_id']);
        $calculationResult = CbcCalculator::calculate($validated['values']);

        $flags = CbcRangeMatcher::computeFlagsForTest(
            $patient,
            $validated['values'],
            $calculationResult['calculated']
        );

        $cbcTest = new CbcTest();
        $cbcTest->patient_id = $validated['patient_id'];
        $cbcTest->values = $validated['values'];
        $cbcTest->calculated = $calculationResult['calculated'];
        $cbcTest->flags = $flags;
        $cbcTest->save();

        return redirect()->back()->with('success', 'CBC Test saved successfully.');
    }

    public function update(StoreCbcTestRequest $request, CbcTest $cbcTest)
    {
        $validated = $request->validated();
        $patient = Patient::findOrFail($cbcTest->patient_id);
        $calculationResult = CbcCalculator::calculate($validated['values']);

        $flags = CbcRangeMatcher::computeFlagsForTest(
            $patient,
            $validated['values'],
            $calculationResult['calculated']
        );

        $cbcTest->values = $validated['values'];
        $cbcTest->calculated = $calculationResult['calculated'];
        $cbcTest->flags = $flags;
        $cbcTest->save();

        return redirect()->back()->with('success', 'CBC Test updated successfully.');
    }
}
