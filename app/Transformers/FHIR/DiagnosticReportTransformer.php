<?php

namespace App\Transformers\FHIR;

use App\Models\OrderItem;

class DiagnosticReportTransformer
{
    protected $observationTransformer;

    public function __construct(ObservationTransformer $observationTransformer)
    {
        $this->observationTransformer = $observationTransformer;
    }

    /**
     * Transform an OrderItem model into a FHIR R4 DiagnosticReport resource array.
     */
    public function transform(OrderItem $orderItem): array
    {
        $service = $orderItem->service;
        $patient = $orderItem->order->patient;
        $labResult = $orderItem->labResult;

        $resultReferences = [];
        if ($labResult) {
            $resultReferences[] = ['reference' => "Observation/{$labResult->id}"];
        }

        return [
            'resourceType' => 'DiagnosticReport',
            'id' => (string) $orderItem->id,
            'status' => 'final',
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://loinc.org',
                            'code' => 'LP29684-5', // Example code for Laboratory Report
                            'display' => 'Laboratory Report',
                        ],
                    ],
                ],
            ],
            'code' => [
                'text' => $service->name,
            ],
            'subject' => [
                'reference' => "Patient/{$patient->id}",
            ],
            'effectiveDateTime' => $labResult ? $labResult->created_at->toIso8601String() : $orderItem->updated_at->toIso8601String(),
            'issued' => $labResult ? $labResult->verified_at?->toIso8601String() : null,
            'result' => $resultReferences,
            // The 'contained' element allows us to bundle the Observation within the report
            'contained' => $labResult ? [$this->observationTransformer->transform($labResult)] : [],
        ];
    }
}
