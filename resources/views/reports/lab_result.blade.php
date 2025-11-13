<x-layouts.print :title="$title" :patient="$patient">
    <style>
        .result-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .result-table th, .result-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .result-table th { background-color: #f2f2f2; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #777; }
    </style>
    <table class="result-table">
        <thead>
            <tr>
                <th>Test Name</th>
                <th>Result</th>
                <th>Units</th>
                <th>Reference Range</th>
                <th>Flag</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $result->orderItem->service->name }}</td>
                <td><strong>{{ $result->result_value }}</strong></td>
                <td>{{ $result->units }}</td>
                <td>{{ $result->reference_range }}</td>
                <td>{{ $result->flag }}</td>
            </tr>
        </tbody>
    </table>
    <div class="footer">
        <p>Report verified by: {{ $result->verifier->name ?? 'N/A' }}</p>
        <p>This is a computer-generated report and does not require a signature.</p>
    </div>
</x-layouts.print>
