<?php

use App\Http\Controllers\Api\V1\FhirDiagnosticReportController;
use App\Http\Controllers\Api\V1\FhirPatientController;
use App\Http\Controllers\Api\V1\Hl7AdtController;
use App\Http\Controllers\Api\V1\Hl7IngestController;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Group API routes for version 1
Route::prefix('v1')->group(function () {
    // ... other routes

    // Generic Lab Test Ranges
    Route::get('/lab-test-ranges/{serviceId}', function (Request $request, $serviceId) {
        $ageInDays = $request->query('age_days');
        $gender = $request->query('gender');

        $service = Service::with(['referenceRanges' => function ($query) use ($ageInDays, $gender) {
            $query->where(function ($q) use ($gender) {
                $q->where('gender', $gender)->orWhere('gender', 'any');
            })
            ->where('age_min', '<=', $ageInDays)
            ->where(function ($q) use ($ageInDays) {
                $q->whereNull('age_max')->orWhere('age_max', '>=', $ageInDays);
            });
        }])->findOrFail($serviceId);

        return $service->referenceRanges->first();
    })->middleware('auth:sanctum');
});
