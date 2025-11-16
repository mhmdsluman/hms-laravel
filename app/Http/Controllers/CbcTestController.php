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

        $cbcTest = new CbcTest();
        $cbcTest->patient_id = $validated['patient_id'];
        $cbcTest->values = $validated['values'];
        $cbcTest->calculated = CbcCalculator::calculate($validated['values'])['calculated'];
        $cbcTest->flags = CbcRangeMatcher::computeFlagsForTest($patient, $validated['values'], $cbcTest->calculated);
        $cbcTest->save();

        return redirect()->back()->with('success', 'CBC Test saved successfully.');
    }

    public function update(StoreCbcTestRequest $request, CbcTest $cbcTest)
    {
        $validated = $request->validated();
        $patient = Patient::findOrFail($cbcTest->patient_id);

        $cbcTest->values = $validated['values'];
        $cbcTest->calculated = CbcCalculator::calculate($validated['values'])['calculated'];
        $cbcTest->flags = CbcRangeMatcher::computeFlagsForTest($patient, $validated['values'], $cbcTest->calculated);
        $cbcTest->save();

        return redirect()->back()->with('success', 'CBC Test updated successfully.');
    }
}
