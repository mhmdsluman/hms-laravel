<div class="print-header">
    <div class="header-content">
        <div class="hospital-info">
            @if ($hospitalLogo)
                <img src="{{ $hospitalLogo }}" alt="Hospital Logo" class="logo">
            @endif
            <h1>{{ $hospitalName }}</h1>
        </div>
        <div class="contact-info">
            <p>{{ $hospitalAddress }}</p>
            <p>Phone: {{ $hospitalPhone }}</p>
            <p>Email: {{ $hospitalEmail }}</p>
        </div>
    </div>
    <h2 class="document-title">{{ $title }}</h2>
    @if ($patient)
        <div class="patient-info">
            <p><strong>Patient:</strong> {{ $patient->first_name }} {{ $patient->last_name }}</p>
            <p><strong>Patient ID:</strong> {{ $patient->patient_no }}</p>
        </div>
    @endif
</div>

<style>
    .print-header {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 2px solid #eee;
    }
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .hospital-info {
        display: flex;
        align-items: center;
    }
    .logo {
        max-height: 80px;
        margin-right: 20px;
    }
    .hospital-info h1 {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    .contact-info {
        text-align: right;
        font-size: 14px;
        color: #666;
    }
    .document-title {
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        margin-top: 20px;
        color: #333;
    }
    .patient-info {
        margin-top: 20px;
        font-size: 14px;
        color: #666;
    }
</style>
