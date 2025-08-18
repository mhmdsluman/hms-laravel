<?php

namespace App\Transformers\FHIR;

use App\Models\LabResult;

class ObservationTransformer
{
    /**
     * Transform a LabResult model into a FHIR R4 Observation resource array.
     */
    public function transform(LabResult $labResult): array
    {
        $orderItem = $labResult->orderItem;
        $service = $orderItem->service;
        $patient = $orderItem->order->patient;

        return [
            'resourceType' => 'Observation',
            'id' => (string) $labResult->id,
            'status' => 'final',
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                            'code' => 'laboratory',
                            'display' => 'Laboratory',
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
            'effectiveDateTime' => $labResult->created_at->toIso8601String(),
            'valueString' => $labResult->result_value, // Using valueString for simplicity
            'interpretation' => [
                [
                    'text' => $labResult->flag,
                ],
            ],
            'referenceRange' => [
                [
                    'text' => $labResult->reference_range . ' ' . $labResult->units,
                ],
            ],
        ];
    }
}
