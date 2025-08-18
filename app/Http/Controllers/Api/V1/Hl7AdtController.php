<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\Hl7ParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Hl7AdtController extends Controller
{
    protected $parser;

    public function __construct(Hl7ParserService $parser)
    {
        $this->parser = $parser;
    }

    public function __invoke(Request $request)
    {
        $hl7Message = $request->getContent();
        $parsedData = $this->parser->parse($hl7Message);

        try {
            if ($parsedData['message_type'] === 'A04') { // Register Patient
                Patient::updateOrCreate(
                    ['uhid' => $parsedData['patient_uhid']],
                    [
                        'first_name' => $parsedData['patient_first_name'],
                        'last_name' => $parsedData['patient_last_name'],
                        'date_of_birth' => $parsedData['patient_dob'],
                        'gender' => $this->mapGender($parsedData['patient_gender']),
                        'primary_phone' => '+0000000000', // Placeholder
                        'primary_phone_country_code' => '+0', // Placeholder
                        'created_by_user_id' => 1, // System User
                    ]
                );

                Log::info('Successfully processed HL7 ADT A04 message for patient: ' . $parsedData['patient_uhid']);
                return response()->json(['status' => 'success', 'message' => 'Patient registered successfully.']);
            }

            // Placeholder for ADT A01 (Admit Patient) logic
            if ($parsedData['message_type'] === 'A01') {
                // Find patient, find available bed in ward, create admission record...
                Log::info('Received HL7 ADT A01 message for patient: ' . $parsedData['patient_uhid']);
                return response()->json(['status' => 'success', 'message' => 'Admission message received (logic not implemented yet).']);
            }

            return response()->json(['status' => 'error', 'message' => 'Unsupported ADT message type.'], 400);

        } catch (\Exception $e) {
            Log::error('Failed to process HL7 ADT message: ' . $e->getMessage(), ['hl7' => $hl7Message]);
            return response()->json(['status' => 'error', 'message' => 'Failed to process HL7 ADT message.'], 500);
        }
    }

    private function mapGender($hl7Gender)
    {
        return match ($hl7Gender) {
            'M' => 'Male',
            'F' => 'Female',
            'O' => 'Other',
            default => 'Undisclosed',
        };
    }
}
