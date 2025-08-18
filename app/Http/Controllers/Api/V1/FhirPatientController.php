<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Transformers\FHIR\PatientTransformer;
use Illuminate\Http\Request;

class FhirPatientController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Patient $patient, PatientTransformer $transformer)
    {
        return response()->json($transformer->transform($patient));
    }
}
