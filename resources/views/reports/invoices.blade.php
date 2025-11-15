<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 16px; line-height: 24px; color: #555; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(2) { text-align: right; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.details td { padding-bottom: 20px; }
        .invoice-box table tr.item td{ border-bottom: 1px solid #eee; }
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
    </style>
</head>
<body>
    @foreach($bills as $bill)
        <div class="invoice-box">
            <table>
                <tr class="top">
                    <td colspan="2">
                        <table>
                            <tr>
                                <td class="title">
                                    <h2>Invoice</h2>
                                </td>
                                <td>
                                    Invoice #: {{ $bill->id }}<br>
                                    Created: {{ $bill->created_at->format('F d, Y') }}<br>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="information">
                    <td colspan="2">
                        <table>
                            <tr>
                                <td>
                                    Patient: {{ $bill->patient->first_name }} {{ $bill->patient->last_name }}<br>
                                    UHID: {{ $bill->patient->uhid }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="heading">
                    <td>Item</td>
                    <td>Price</td>
                </tr>
                @foreach($bill->items as $item)
                    <tr class="item">
                        <td>{{ $item->service->name }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total">
                    <td></td>
                    <td>Total: ${{ number_format($bill->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>
        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>
