<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LabResult;
use App\Models\OrderItem;
use App\Models\Patient;
use App\Services\Hl7ParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Hl7IngestController extends Controller
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
            $patient = Patient::where('uhid', $parsedData['patient_uhid'])->firstOrFail();
            $orderItem = OrderItem::where('placer_order_number', $parsedData['placer_order_number'])->firstOrFail();

            // For now, we'll just process the first result in the message
            $resultData = $parsedData['results'][0] ?? null;

            if ($resultData) {
                LabResult::create([
                    'order_item_id' => $orderItem->id,
                    'result_value' => $resultData['value'],
                    'units' => $resultData['units'],
                    'reference_range' => $resultData['reference_range'],
                    'flag' => $resultData['flag'],
                    'entered_by_user_id' => 1, // Assume a default system user for now
                ]);

                $orderItem->update(['status' => 'Result Ready']);
            }

            Log::info('Successfully processed HL7 message for order: ' . $orderItem->placer_order_number);
            return response()->json(['status' => 'success', 'message' => 'HL7 message processed.']);

        } catch (\Exception $e) {
            Log::error('Failed to process HL7 message: ' . $e->getMessage(), ['hl7' => $hl7Message]);
            return response()->json(['status' => 'error', 'message' => 'Failed to process HL7 message.'], 400);
        }
    }
}
