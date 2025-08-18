<?php

namespace App\Transformers\FHIR;

use App\Models\Patient;

class PatientTransformer
{
    /**
     * Transform a Patient model into a FHIR R4 Patient resource array.
     */
    public function transform(Patient $patient): array
    {
        return [
            'resourceType' => 'Patient',
            'id' => (string) $patient->id,
            'identifier' => [
                [
                    'system' => url('/'), // Represents the system that assigned the ID
                    'value' => $patient->uhid,
                    'type' => [
                        'text' => 'Unique Hospital ID',
                    ],
                ],
            ],
            'name' => [
                [
                    'use' => 'official',
                    'family' => $patient->last_name,
                    'given' => [$patient->first_name, $patient->middle_name],
                ],
            ],
            'telecom' => [
                [
                    'system' => 'phone',
                    'value' => $patient->primary_phone,
                    'use' => 'mobile',
                ],
                [
                    'system' => 'email',
                    'value' => $patient->email,
                ],
            ],
            'gender' => strtolower($patient->gender), // FHIR requires lowercase
            'birthDate' => $patient->date_of_birth->format('Y-m-d'),
        ];
    }
}
