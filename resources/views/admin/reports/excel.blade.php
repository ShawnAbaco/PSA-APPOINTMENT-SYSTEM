{{-- resources/views/admin/reports/excel.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Report - Philippine Statistics Authority</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            color: #333;
            background: #fff;
        }

        .no-print {
            margin-bottom: 20px;
        }

        .btn-export {
            background: #217346;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-export:hover {
            background: #185a32;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #0f3b6f;
            padding-bottom: 20px;
            text-align: center;
        }

        .header-logo {
            width: 70px;
            height: 70px;
            margin: 0 20px;
        }

        .header-agency {
            font-size: 14px;
            font-weight: 400;
            color: #333;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f3b6f;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .report-title {
            text-align: center;
            font-size: 22px;
            color: #0f3b6f;
            margin: 15px 0 5px 0;
            font-weight: bold;
        }

        .report-subtitle {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .report-period {
            background: #f0f4ff;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid #0f3b6f;
        }

        .filter-badge {
            background: #0f3b6f;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            margin-left: 10px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            color: #0f3b6f;
            font-size: 18px;
            border-bottom: 2px solid #c49a2c;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 20px;
        }

        table thead th {
            background: #0f3b6f;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #0a2647;
        }

        table tbody td {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
        }

        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .total-row {
            background: #f0f4ff !important;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #0f3b6f;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            color: white;
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
            border-top: 2px solid #0f3b6f;
            font-size: 11px;
            color: #666;
        }

        .footer img {
            width: 40px;
            height: 40px;
            margin-bottom: 8px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 10px;
            }

            @page {
                margin: 1cm;
                size: A4 landscape;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="exportToExcel()" class="btn-export">📥 Download as Excel (.xls)</button>
        <a href="{{ route('reports.index', request()->query()) }}" class="btn-back">← Back to Reports</a>
    </div>

    <div id="export-content">
        <div class="header">
            <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="header-logo">
            <div>
                <p class="header-agency">Republic of the Philippines</p>
                <p class="header-title">Philippine Statistics Authority</p>
            </div>
            <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="header-logo">
        </div>
        <div class="report-title">APPOINTMENT SYSTEM REPORT</div>
        <div class="report-subtitle">Comprehensive Analytics and Insights</div>

        <div class="report-period">
            📅 Report Period: <strong>{{ $startDate }} to {{ $endDate }}</strong>
            @if ($statusFilter)
                <span class="filter-badge">Status: {{ ucfirst($statusFilter) }}</span>
            @endif
        </div>

        <div class="section">
            <h2>Summary Statistics</h2>
            <table>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total Bookings</strong></td>
                        <td>{{ $summary['total'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pending</strong></td>
                        <td>{{ $summary['pending'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Confirmed</strong></td>
                        <td>{{ $summary['confirmed'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Completed</strong></td>
                        <td>{{ $summary['completed'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cancelled</strong></td>
                        <td>{{ $summary['cancelled'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Unique Locations</strong></td>
                        <td>{{ $uniqueLocations }}</td>
                    </tr>
                    <tr>
                        <td><strong>Most Booked City</strong></td>
                        <td>{{ $topCity }} ({{ $topCityCount }} bookings)</td>
                    </tr>
                    <tr>
                        <td><strong>Completion Rate</strong></td>
                        <td>{{ $completionRate }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Detailed Bookings</h2>
            <table id="bookings-table">
                <thead>
                    <tr>
                        <th>Appointment #</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Client Name</th>
                        <th>Contact Number</th>
                        <th>City/Municipality</th>
                        <th>Services</th>
                        <th>Status</th>
                        <th>No. of Applicants</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->appointment_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</td>
                            <td>
                                @php
                                    $timeSlotDisplay = 'N/A';
                                    if ($appointment->relationLoaded('timeSlot') && $appointment->timeSlot) {
                                        $ts = $appointment->timeSlot;
                                        // Try all possible column names for time slot label
                                        $timeSlotDisplay =
                                            $ts->slot_label ??
                                            ($ts->label ??
                                                ($ts->time_slot ??
                                                    ($ts->time ??
                                                        ($ts->start_time && $ts->end_time
                                                            ? \Carbon\Carbon::parse($ts->start_time)->format('g:i A') .
                                                                ' - ' .
                                                                \Carbon\Carbon::parse($ts->end_time)->format('g:i A')
                                                            : 'ID: ' . $ts->id))));
                                    } elseif ($appointment->time_slot_id) {
                                        $ts = \App\Models\TimeSlot::find($appointment->time_slot_id);
                                        if ($ts) {
                                            $timeSlotDisplay =
                                                $ts->slot_label ??
                                                ($ts->label ??
                                                    ($ts->time_slot ??
                                                        ($ts->time ??
                                                            ($ts->start_time && $ts->end_time
                                                                ? \Carbon\Carbon::parse($ts->start_time)->format(
                                                                        'g:i A',
                                                                    ) .
                                                                    ' - ' .
                                                                    \Carbon\Carbon::parse($ts->end_time)->format(
                                                                        'g:i A',
                                                                    )
                                                                : 'ID: ' . $ts->id))));
                                        }
                                    }
                                @endphp
                                {{ $timeSlotDisplay }}
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

        @if ($citySummary->isNotEmpty())
            <div class="section">
                <h2>City/Municipality Summary</h2>
                <table>
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

        <div class="footer">
            <img src="{{ asset('images/psa.png') }}" alt="PSA Logo">
            <p>
                <strong>Philippine Statistics Authority</strong><br>
                Appointment Management System<br>
                Generated on {{ now()->format('F j, Y \a\t g:i A') }}
            </p>
            <p style="margin-top: 10px; font-style: italic;">
                This report is system-generated and is valid without signature.
            </p>
        </div>
    </div>

    <script>
        function exportToExcel() {
            const content = document.getElementById('export-content');
            const htmlContent = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                    <meta charset="UTF-8">
                    <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
                    <x:Name>Appointment Report</x:Name>
                    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                    </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
                    <style>
                        table { border-collapse: collapse; }
                        th, td { padding: 8px; border: 1px solid #000; }
                        th { background-color: #0F3B6F; color: white; }
                        .total-row { font-weight: bold; }
                    </style>
                </head>
                <body>${content.innerHTML}</body>
                </html>
            `;
            const blob = new Blob([htmlContent], {
                type: 'application/vnd.ms-excel'
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'appointment_report_{{ $startDate }}_to_{{ $endDate }}.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>

</html>
