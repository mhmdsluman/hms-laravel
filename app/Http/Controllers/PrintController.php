<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\LabOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    /**
     * Generate a printable PDF for a specific lab result.
     */
    public function labResult(Request $request, LabOrder $labOrder)
    {
        $labOrder->load('patient', 'results.test');

        if ($request->has('print_all')) {
            $resultsToPrint = $labOrder->results;
        } else {
            $resultsToPrint = $labOrder->results->whereIn('id', array_keys($request->get('print_selection', [])));
        }

        $pdf = Pdf::loadView('print.lab_result', [
            'labOrder' => $labOrder,
            'resultsToPrint' => $resultsToPrint,
        ]);

        return $pdf->stream('lab-result-' . $labOrder->order_id . '.pdf');
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
