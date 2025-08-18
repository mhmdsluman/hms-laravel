<?php

use App\Http\Controllers\Api\V1\FhirDiagnosticReportController;
use App\Http\Controllers\Api\V1\FhirPatientController;
use App\Http\Controllers\Api\V1\Hl7AdtController;
use App\Http\Controllers\Api\V1\Hl7IngestController;
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
});
