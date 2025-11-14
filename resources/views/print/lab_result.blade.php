<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Result</title>
    <style>
        body { font-family: sans-serif; }
        .abnormal { font-weight: bold; color: red; }
        .abnormal-print { font-weight: bold; }
        .abnormal-print::after { content: " *"; }
    </style>
</head>
<body>
    <h1>Lab Result</h1>
    <p><strong>Patient:</strong> {{ $labOrder->patient->name }}</p>
    <p><strong>Order ID:</strong> {{ $labOrder->order_id }}</p>

    <form action="{{ route('print.labResult', $labOrder) }}" method="get">
        <label>
            <input type="checkbox" name="print_all" value="1" onchange="this.form.submit()" {{ request('print_all') ? 'checked' : '' }}>
            Print All
        </label>
    </form>

    <table>
        <thead>
            <tr>
                <th>Test</th>
                <th>Result</th>
                <th>Units</th>
                <th>Reference Range</th>
                <th>Comment</th>
                <th>Print</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($labOrder->results as $result)
                @if (request('print_all') || request('print_selection.' . $result->id))
                    <tr class="{{ $result->is_abnormal ? 'abnormal' : '' }}">
                        <td>{{ $result->test->name }}</td>
                        <td>{{ $result->result }}</td>
                        <td>{{ $result->test->units }}</td>
                        <td>{{ $result->test->reference_ranges }}</td>
                        <td>{{ $result->comment }}</td>
                        <td>
                            <input type="checkbox" name="print_selection[{{ $result->id }}]" value="1" onchange="document.querySelector('form').submit()" {{ request('print_selection.' . $result->id) ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <script>
        // Add a print-friendly class for black and white printing
        if (window.matchMedia('print').matches) {
            document.querySelectorAll('.abnormal').forEach(el => {
                el.classList.add('abnormal-print');
            });
        }
    </script>
</body>
</html>
