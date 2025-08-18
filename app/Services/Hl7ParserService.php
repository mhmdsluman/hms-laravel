<?php

namespace App\Services;

class Hl7ParserService
{
    /**
     * Parse an HL7 message string into a structured array.
     */
    public function parse(string $message): array
    {
        $segments = explode("\n", trim($message));
        $data = [];

        foreach ($segments as $segment) {
            $fields = explode('|', trim($segment));
            $segmentName = array_shift($fields);

            switch ($segmentName) {
                case 'MSH':
                    $messageType = explode('^', $fields[7])[0] ?? null;
                    $data['message_type'] = $messageType;
                    break;
                case 'PID':
                    $data['patient_uhid'] = explode('^', $fields[2])[0] ?? null;
                    $name = explode('^', $fields[4]);
                    $data['patient_last_name'] = $name[0] ?? null;
                    $data['patient_first_name'] = $name[1] ?? null;
                    $data['patient_dob'] = isset($fields[6]) ? \Carbon\Carbon::createFromFormat('Ymd', $fields[6])->format('Y-m-d') : null;
                    $data['patient_gender'] = $fields[7] ?? null;
                    break;
                case 'PV1':
                    $data['patient_class'] = $fields[1] ?? null; // e.g., 'I' for Inpatient
                    $location = explode('^', $fields[2]);
                    $data['assigned_ward'] = $location[0] ?? null;
                    $data['assigned_room'] = $location[1] ?? null;
                    $data['assigned_bed'] = $location[2] ?? null;
                    break;
                case 'OBR':
                    $data['placer_order_number'] = $fields[1] ?? null;
                    break;
                case 'OBX':
                    $data['results'][] = [
                        'test_name' => explode('^', $fields[2])[1] ?? null,
                        'value' => $fields[4] ?? null,
                        'units' => $fields[5] ?? null,
                        'reference_range' => $fields[6] ?? null,
                        'flag' => $fields[7] ?? null,
                    ];
                    break;
            }
        }

        return $data;
    }
}
