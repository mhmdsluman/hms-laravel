<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $bill->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; margin: 0; color: #333; }
        .container { padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .header .hospital-info h1 { margin: 0; font-size: 28px; color: #0056b3; }
        .header .hospital-info p { margin: 0; }
        .header .invoice-info { text-align: right; }
        .header .invoice-info h2 { margin: 0; font-size: 24px; }
        .header .invoice-info p { margin: 0; }
        .patient-info { margin-bottom: 30px; border-top: 2px solid #0056b3; border-bottom: 2px solid #0056b3; padding: 15px 0; }
        .patient-info table { width: 100%; }
        .patient-info td { padding: 4px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th, .items-table td { border-bottom: 1px solid #ddd; padding: 10px; text-align: left; }
        .items-table th { background-color: #f2f2f2; font-weight: bold; }
        .items-table .text-right { text-align: right; }
        .totals { margin-top: 30px; width: 50%; margin-left: 50%; }
        .totals table { width: 100%; }
        .totals td { padding: 8px; }
        .totals .grand-total { font-weight: bold; font-size: 1.2em; border-top: 2px solid #333; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="hospital-info">
                <h1>HMS Health Services</h1>
                <p>123 Health St, Mogadishu, Somalia</p>
                <p>contact@hms.so</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>Bill #:</strong> {{ $bill->id }}</p>
                <p><strong>Date:</strong> {{ $bill->created_at->format('Y-m-d') }}</p>
                <p><strong>Status:</strong> {{ $bill->status }}</p>
            </div>
        </div>

        <div class="patient-info">
            <table>
                <tr>
                    <td><strong>Patient Name:</strong></td>
                    <td>{{ $bill->patient->first_name }} {{ $bill->patient->last_name }}</td>
                    <td><strong>UHID:</strong></td>
                    <td>{{ $bill->patient->uhid }}</td>
                </tr>
                <tr>
                    <td><strong>Age/Gender:</strong></td>
                    <td>{{ $bill->patient->age }} years / {{ $bill->patient->gender }}</td>
                    <td><strong>Appointment:</strong></td>
                    <td>{{ $bill->appointment->appointment_time->format('Y-m-d') }}</td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Service Description</th>
                    <th class="text-right">Total Cost</th>
                    <th class="text-right">Insurance Coverage</th>
                    <th class="text-right">Patient Co-Pay</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->items as $item)
                <tr>
                    <td>{{ $item->service->name }}</td>
                    <td class="text-right">${{ number_format($item->total_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->insurance_amount, 2) }}</td>
                    <td class="text-right">${{ number_format($item->patient_co_pay, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">${{ number_format($bill->patient_co_pay, 2) }}</td>
                </tr>
                @if($bill->discount_amount > 0)
                <tr>
                    <td>Discount ({{ $bill->discount_reason }}):</td>
                    <td class="text-right">-${{ number_format($bill->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>Amount Paid:</td>
                    <td class="text-right">-${{ number_format($bill->paid_amount, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td>Balance Due:</td>
                    <td class="text-right">${{ number_format($bill->balance_due, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for choosing HMS Health Services.</p>
        </div>
    </div>
</body>
</html>
