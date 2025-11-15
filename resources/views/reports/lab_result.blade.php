<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Result Report</title>
    <style>
        body { font-family: sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 0; color: #555; }
        .patient-info { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .patient-info table { width: 100%; }
        .patient-info td { padding: 5px; }
        .result-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .result-table th, .result-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .result-table th { background-color: #f2f2f2; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #777; }
    </style>
</head>
<body>

    <div class="header">
        <h1>HMS Laboratory Report</h1>
        <p>Official Health Report</p>
    </div>

    <div class="patient-info">
        <table>
            <tr>
                <td><strong>Patient Name:</strong></td>
                <td>{{ $result->orderItem->order->patient->first_name }} {{ $result->orderItem->order->patient->last_name }}</td>
                <td><strong>UHID:</strong></td>
                <td>{{ $result->orderItem->order->patient->uhid }}</td>
            </tr>
            <tr>
                <td><strong>Age:</strong></td>
                <td>{{ $result->orderItem->order->patient->age }} years</td>
                <td><strong>Gender:</strong></td>
                <td>{{ $result->orderItem->order->patient->gender }}</td>
            </tr>
            <tr>
                <td><strong>Collected:</strong></td>
                <td>{{ $result->created_at->format('Y-m-d H:i') }}</td>
                <td><strong>Verified:</strong></td>
                <td>{{ $result->verified_at ? $result->verified_at->format('Y-m-d H:i') : 'N/A' }}</td>
            </tr>
        </table>
    </div>

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

</body>
</html>
