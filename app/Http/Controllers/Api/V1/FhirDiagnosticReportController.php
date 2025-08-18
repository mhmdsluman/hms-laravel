<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Transformers\FHIR\DiagnosticReportTransformer;
use Illuminate\Http\Request;

class FhirDiagnosticReportController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(OrderItem $orderItem, DiagnosticReportTransformer $transformer)
    {
        // Ensure we only return reports for completed lab orders
        if ($orderItem->service->department !== 'Laboratory' || $orderItem->status !== 'Completed') {
            abort(404, 'Diagnostic report not found or not yet completed.');
        }

        $orderItem->load(['order.patient', 'service', 'labResult']);

        return response()->json($transformer->transform($orderItem));
    }
}
