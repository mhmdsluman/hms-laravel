<!DOCTYPE html>
<html>
<head>
    <title>Stool Analysis Report</title>
    <style>
        body { font-family: sans-serif; }
        .report-container { width: 80%; margin: auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 1.2em; margin-top: 20px; border-bottom: 1px solid #000; }
        .result-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px; }
        .result-item { display: contents; }
        .result-item > div { padding: 5px; }
        .result-label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="header">
            <h1>Stool Analysis Report</h1>
            <p>Patient: {{ $order->patient->name }}</p>
            <p>Order ID: {{ $order->id }}</p>
        </div>

        <div class="section-title">Macroscopic Examination</div>
        <div class="result-grid">
            @foreach($results->where('category', 'macroscopic') as $result)
                <div class="result-item">
                    <div class="result-label">{{ $result->parameter->name }}:</div>
                    <div>{{ $result->value }}</div>
                    <div>{{ $result->flag }}</div>
                </div>
            @endforeach
        </div>

        <div class="section-title">Microscopic Examination</div>
        <div class="result-grid">
            @foreach($results->where('category', 'microscopic') as $result)
                <div class="result-item">
                    <div class="result-label">{{ $result->parameter->name }}:</div>
                    <div>{{ $result->value }}</div>
                    <div>{{ $result->flag }}</div>
                </div>
            @endforeach
        </div>

        <div class="section-title">Chemical Examination</div>
        <div class="result-grid">
            @foreach($results->where('category', 'chemical') as $result)
                <div class="result-item">
                    <div class="result-label">{{ $result->parameter->name }}:</div>
                    <div>{{ $result->value }}</div>
                    <div>{{ $result->flag }}</div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
