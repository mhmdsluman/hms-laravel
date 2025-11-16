<?php

use App\Http\Controllers\Api\V1\FhirDiagnosticReportController;
use App\Http\Controllers\Api\V1\FhirPatientController;
use App\Http\Controllers\Api\V1\Hl7AdtController;
use App\Http\Controllers\Api\V1\Hl7IngestController;
use App\Models\CbcParameterRange;
use App\Models\UrineParameterRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Group API routes for version 1
Route::prefix('v1')->group(function () {
    // HL7 Ingestion Endpoints
    Route::post('/hl7/ingest', Hl7IngestController::class);
    Route::post('/hl7/adt', Hl7AdtController::class);

    // FHIR Read Endpoints (require Sanctum auth)
    Route::middleware('auth:sanctum')->group(function() {
        Route::get('/fhir/Patient/{patient}', [FhirPatientController::class, 'show'])->name('fhir.patient.show');
        Route::get('/fhir/DiagnosticReport/{orderItem}', [FhirDiagnosticReportController::class, 'show'])->name('fhir.diagnosticreport.show');
    });

    // CBC Ranges
    Route::get('/cbc-ranges', function (Request $request) {
        $ageInDays = $request->query('age_days');
        $gender = $request->query('gender');

        return CbcParameterRange::where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                      ->orWhere('gender', 'any');
            })
            ->where('min_age_days', '<=', $ageInDays)
            ->where(function ($query) use ($ageInDays) {
                $query->whereNull('max_age_days')
                      ->orWhere('max_age_days', '>=', $ageInDays);
            })
            ->orderByRaw("CASE WHEN gender = 'any' THEN 1 ELSE 0 END")
            ->get()
            ->keyBy('parameter');
    })->middleware('auth:sanctum');

    // Urine Ranges
    Route::get('/urine-ranges', function (Request $request) {
        $ageInDays = $request->query('age_days');
        $gender = $request->query('gender');

        return UrineParameterRange::where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                      ->orWhere('gender', 'any');
            })
            ->where('min_age_days', '<=', $ageInDays)
            ->where(function ($query) use ($ageInDays) {
                $query->whereNull('max_age_days')
                      ->orWhere('max_age_days', '>=', $ageInDays);
            })
            ->orderByRaw("CASE WHEN gender = 'any' THEN 1 ELSE 0 END")
            ->get()
            ->keyBy('parameter');
    })->middleware('auth:sanctum');
});
