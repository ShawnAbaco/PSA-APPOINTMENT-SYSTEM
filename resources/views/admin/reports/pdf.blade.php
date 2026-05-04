{{-- resources/views/admin/reports/pdf.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Appointment Report</title>
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

        .header-title-section {
            text-align: center;
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
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
        }

        .summary-card.primary {
            border-left: 4px solid #4b4b4b;
        }

        .summary-card.success {
            border-left: 4px solid #4b4b4b;
        }

        .summary-card.warning {
            border-left: 4px solid #4b4b4b;
        }

        .summary-card.info {
            border-left: 4px solid #4b4b4b;
        }

        .summary-card.danger {
            border-left: 4px solid #4b4b4b;
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
            color: #0f3b6f;
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

        .table tr:hover {
            background: #f0f4ff;
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
            background: #f59e0b;
        }

        .status-confirmed {
            background: #3b82f6;
        }

        .status-completed {
            background: #10b981;
        }

        .status-cancelled {
            background: #ef4444;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #999;
        }

        .footer .footer-logos {
            margin-bottom: 10px;
        }

        .footer .footer-logos img {
            width: 40px;
            height: 40px;
            margin: 0 5px;
            vertical-align: middle;
        }

        .page-break {
            page-break-after: always;
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
                <img src="{{ public_path('images/psa.png') }}" alt="PSA Logo" class="header-logo">
            </div>
        </div>
        <h1>APPOINTMENT SYSTEM REPORT</h1>
        <p>Comprehensive Analytics and Insights</p>
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
            <div class="summary-card primary">
                <div class="label">Total Bookings</div>
                <div class="value">{{ $summary['total'] }}</div>
            </div>
            <div class="summary-card success">
                <div class="label">Unique Locations</div>
                <div class="value" style="color: #000000;">{{ $uniqueLocations }}</div>
            </div>
            <div class="summary-card warning">
                <div class="label">Most Booked City</div>
                <div class="value" style="color: #000000; font-size: 20px;">{{ $topCity }}</div>
                <div style="font-size: 11px; color: #000000;">{{ $topCityCount }} bookings</div>
            </div>
            <div class="summary-card info">
                <div class="label">Completion Rate</div>
                <div class="value" style="color: #000000;">{{ $completionRate }}%</div>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-card warning">
                <div class="label">Pending</div>
                <div class="value" style="color: #000000;">{{ $summary['pending'] }}</div>
            </div>
            <div class="summary-card info">
                <div class="label">Confirmed</div>
                <div class="value" style="color: #000000;">{{ $summary['confirmed'] }}</div>
            </div>
            <div class="summary-card success">
                <div class="label">Completed</div>
                <div class="value" style="color: #000000;">{{ $summary['completed'] }}</div>
            </div>
            <div class="summary-card danger">
                <div class="label">Cancelled</div>
                <div class="value" style="color: #000000;">{{ $summary['cancelled'] }}</div>
            </div>
        </div>
    </div>

    <!-- Detailed Bookings Table -->
    <div class="summary-section">
        <h2>Detailed Bookings</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Appointment #</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Client Name</th>
                    <th>Contact</th>
                    <th>City/Municipality</th>
                    <th>Services</th>
                    <th>Status</th>
                    <th>Clients</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td><strong>{{ $appointment->appointment_number }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</td>
                        <td>
                            @if ($appointment->timeSlot)
                                {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('g:i A') }} -
                                {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('g:i A') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $appointment->contact_name }}</td>
                        <td>{{ $appointment->contact_mobile }}</td>
                        <td>{{ $appointment->user_city ?? 'N/A' }}</td>
                        <td>
                            @php
                                $services = $appointment->clients
                                    ->pluck('service')
                                    ->unique()
                                    ->map(function ($s) {
                                        $names = [
                                            'reg' => 'Registration',
                                            'updating' => 'Updating',
                                            'inquiry' => 'Inquiry',
                                        ];
                                        return $names[$s] ?? $s;
                                    })
                                    ->implode(', ');
                            @endphp
                            {{ $services }}
                        </td>
                        <td>
                            <span class="status-badge status-{{ $appointment->status }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </td>
                        <td style="text-align: center;">{{ $appointment->clients->count() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                            No appointments found for this period.
                        </td>
                    </tr>
                @endforelse
                @if ($appointments->isNotEmpty())
                    <tr class="total-row">
                        <td colspan="8" style="text-align: right;"><strong>TOTAL APPOINTMENTS:</strong></td>
                        <td style="text-align: center;"><strong>{{ $appointments->count() }}</strong></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- City Summary -->
    @if ($citySummary->isNotEmpty())
        <div class="page-break"></div>
        <div class="summary-section">
            <h2>City/Municipality Summary</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>City/Municipality</th>
                        <th>Total Bookings</th>
                        <th>Pending</th>
                        <th>Confirmed</th>
                        <th>Completed</th>
                        <th>Cancelled</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($citySummary as $city)
                        <tr>
                            <td><strong>{{ $city->user_city }}</strong></td>
                            <td style="text-align: center;">{{ $city->total_bookings }}</td>
                            <td style="text-align: center;">{{ $city->pending }}</td>
                            <td style="text-align: center;">{{ $city->confirmed }}</td>
                            <td style="text-align: center;">{{ $city->completed }}</td>
                            <td style="text-align: center;">{{ $city->cancelled }}</td>
                            <td style="text-align: center;">
                                @php
                                    $cityCompletionRate =
                                        $city->total_bookings > 0
                                            ? round(($city->completed / $city->total_bookings) * 100, 1)
                                            : 0;
                                @endphp
                                {{ $cityCompletionRate }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div class="footer-logos">
            <img src="{{ public_path('images/psa.png') }}" alt="PSA Logo">
        </div>
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
