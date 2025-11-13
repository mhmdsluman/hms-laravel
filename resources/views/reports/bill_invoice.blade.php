<x-layouts.print :title="$title" :patient="$patient">
    <style>
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
</x-layouts.print>
