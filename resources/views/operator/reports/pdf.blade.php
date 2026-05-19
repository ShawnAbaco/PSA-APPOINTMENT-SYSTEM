{{-- resources/views/operator/reports/pdf.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Appointments Report - Philippine Statistics Authority</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #0f3b6f;
            padding-bottom: 15px;
        }

        .header-logo-container {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .header-logo-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: 33%;
        }

        .header-logo-cell.logo-left {
            text-align: left;
            width: 80px;
        }

        .header-logo-cell.logo-center {
            text-align: center;
        }

        .header-logo-cell.logo-right {
            text-align: right;
            width: 80px;
        }

        .header-logo {
            width: 70px;
            height: 70px;
        }

        .header-agency-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f3b6f;
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-subtitle {
            font-size: 13px;
            color: #c49a2c;
            margin: 0 0 5px 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h1 {
            color: #0f3b6f;
            font-size: 20px;
            margin: 10px 0 5px 0;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .header p {
            color: #666;
            margin: 5px 0;
            font-size: 13px;
        }

        .report-period {
            background: #f8f9fa;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .report-period .filter-badge {
            background: #0f3b6f;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 8px;
        }

        .summary-section {
            margin-bottom: 30px;
        }

        .summary-section h2 {
            color: #0f3b6f;
            font-size: 18px;
            border-bottom: 2px solid #c49a2c;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 10px;
        }

        .summary-card {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
        }

        .summary-card .label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .summary-card .value {
            font-size: 28px;
            font-weight: bold;
        }

        .summary-card .trend {
            font-size: 10px;
            color: #888;
            margin-top: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th {
            background: #0f3b6f;
            color: white;
            padding: 8px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            font-size: 10px;
        }

        .table tr:nth-child(even) {
            background: #f9fafb;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 10px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: var(--warning);
        }

        .status-completed {
            background: #3b82f6;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #999;
        }

        .total-row {
            background: #f0f4ff !important;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #0f3b6f;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-logo-container">
            <div class="header-logo-cell logo-left">
                <img src="{{ public_path('images/psa.png') }}" alt="PSA Logo" class="header-logo">
            </div>
            <div class="header-logo-cell logo-center">
                <div class="header-agency-name">Republic of the Philippines</div>
                <div class="header-subtitle">Philippine Statistics Authority</div>
            </div>
            <div class="header-logo-cell logo-right">
                <img src="{{ public_path('images/logo1.png') }}" alt="PSA Logo" class="header-logo">
            </div>
        </div>
        <h1>APPOINTMENTS REPORT</h1>
        <p>Processed Appointments Status Report</p>
    </div>

    <!-- Report Period -->
    <div class="report-period">
        Report Period: <strong>{{ $startDate }} to {{ $endDate }}</strong>
        @if ($statusFilter)
            <span class="filter-badge">Status: {{ ucfirst($statusFilter) }}</span>
        @endif
    </div>

    <!-- Summary Statistics -->
    <div class="summary-section">
        <h2>Summary Statistics</h2>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="label">Pending</div>
                <div class="value" style="color: #F59E0B;">{{ $summary['pending'] ?? 0 }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Completed</div>
                <div class="value" style="color: #3b82f6;">{{ $summary['completed'] ?? 0 }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Total</div>
                <div class="value" style="color: #0f3b6f;">
                    {{ ($summary['pending'] ?? 0) + ($summary['completed'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Bookings Table -->
    <div class="summary-section">
        <h2>Appointment Details</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Appointment #</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Contact Person</th>
                    <th>Contact Mobile</th>
                    <th>Location</th>
                    <th># of Applicants</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td><strong>{{ $appointment->appointment_number }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</td>
                        <td>
                            @php
                                $timeSlotDisplay = 'N/A';
                                if ($appointment->timeSlot) {
                                    $ts = $appointment->timeSlot;
                                    if ($ts->slot_label) {
                                        $timeSlotDisplay = $ts->slot_label;
                                    } elseif ($ts->start_time && $ts->end_time) {
                                        $timeSlotDisplay =
                                            \Carbon\Carbon::parse($ts->start_time)->format('g:i A') .
                                            ' - ' .
                                            \Carbon\Carbon::parse($ts->end_time)->format('g:i A');
                                    } else {
                                        $timeSlotDisplay = $ts->label ?? 'ID: ' . $ts->id;
                                    }
                                }
                            @endphp
                            {{ $timeSlotDisplay }}
                        </td>
                        <td>{{ $appointment->contact_name }}</td>
                        <td>{{ $appointment->contact_mobile }}</td>
                        <td>{{ $appointment->user_city ?? 'N/A' }}</td>
                        <td style="text-align: center;">{{ $appointment->clients->count() }}</td>
                        <td>
                            <span class="status-badge status-{{ $appointment->status }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: #999;">
                            No appointments found for this period.
                        </td>
                    </tr>
                @endforelse
                @if ($appointments->isNotEmpty())
                    <tr class="total-row">
                        <td colspan="7" style="text-align: right;"><strong>TOTAL APPOINTMENTS:</strong></td>
                        <td style="text-align: center;"><strong>{{ $appointments->count() }}</strong></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>Philippine Statistics Authority</strong><br>
            Appointment Management System<br>
            Generated on {{ now()->format('F j, Y \a\t g:i A') }}
        </p>
        <p style="margin-top: 10px; font-style: italic;">
            This report is system-generated and is valid without signature.
        </p>
    </div>
</body>

</html>
