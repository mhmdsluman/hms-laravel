<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\LabResult;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    /**
     * Generate a printable PDF for a specific lab result.
     */
    public function labResult(LabResult $labResult)
    {
        $labResult->load([
            'orderItem.service',
            'orderItem.order.patient',
            'verifier'
        ]);

        $pdf = Pdf::loadView('reports.lab_result', [
            'result' => $labResult,
            'title' => 'Lab Result',
            'patient' => $labResult->orderItem->order->patient,
        ]);

        return $pdf->stream('lab-result-' . $labResult->id . '.pdf');
    }

    /**
     * Generate a printable PDF for a specific bill.
     */
    public function billInvoice(Bill $bill)
    {
        // Eager load all the necessary data for the invoice
        $bill->load([
            'patient',
            'items.service',
        ]);

        $pdf = Pdf::loadView('reports.bill_invoice', [
            'bill' => $bill,
            'title' => 'Bill Invoice',
            'patient' => $bill->patient,
        ]);

        return $pdf->stream('invoice-' . $bill->id . '.pdf');
    }
}
