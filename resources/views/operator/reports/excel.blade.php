{{-- resources/views/operator/reports/excel.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments Report - Philippine Statistics Authority</title>
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
            background: var(--warning);
        }

        .status-completed {
            background: #3b82f6;
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
        <a href="{{ route('operator.reports.index', request()->query()) }}" class="btn-back">← Back to Reports</a>
    </div>

    <div id="export-content">
        <div class="header">
            <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="header-logo">
            <div>
                <p class="header-agency">Republic of the Philippines</p>
                <p class="header-title">Philippine Statistics Authority</p>
            </div>
            <img src="{{ asset('images/logo1.png') }}" alt="PSA Logo" class="header-logo">
        </div>
        <div class="report-title">CONFIRMED & COMPLETED APPOINTMENTS REPORT</div>
        <div class="report-subtitle">Processed Appointments Status Report</div>

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
                        <td><strong>Pending Appointments</strong></td>
                        <td style="color: var(--warning); font-weight: bold;">{{ $summary['pending'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td><strong>Completed Appointments</strong></td>
                        <td style="color: #3b82f6; font-weight: bold;">{{ $summary['completed'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Processed Appointments</strong></td>
                        <td style="color: #0f3b6f; font-weight: bold;">
                            {{ ($summary['pending'] ?? 0) + ($summary['completed'] ?? 0) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Appointment Details</h2>
            <table id="bookings-table">
                <thead>
                    <tr>
                        <th>Appointment #</th>
                        <th>Date</th>
                        <th>Time Slot</th>
                        <th>Contact Person</th>
                        <th>Contact Number</th>
                        <th>Location</th>
                        <th>No. of Applicants</th>
                        <th>Status</th>
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
                                    // Method 1: Check if timeSlot relationship is loaded
                                    if ($appointment->relationLoaded('timeSlot') && $appointment->timeSlot) {
                                        $ts = $appointment->timeSlot;
                                        // Try slot_label first
                                        if ($ts->slot_label) {
                                            $timeSlotDisplay = $ts->slot_label;
                                        }
                                        // Try label field
                                        elseif ($ts->label) {
                                            $timeSlotDisplay = $ts->label;
                                        }
                                        // Try time_slot field (if exists)
                                        elseif (isset($ts->time_slot)) {
                                            $timeSlotDisplay = $ts->time_slot;
                                        }
                                        // Fallback to start_time and end_time
                                        elseif ($ts->start_time && $ts->end_time) {
                                            $timeSlotDisplay =
                                                \Carbon\Carbon::parse($ts->start_time)->format('g:i A') .
                                                ' - ' .
                                                \Carbon\Carbon::parse($ts->end_time)->format('g:i A');
                                        } else {
                                            $timeSlotDisplay = 'ID: ' . $ts->id;
                                        }
                                    }
                                    // Method 2: Fallback to querying TimeSlot model directly
                                    elseif ($appointment->time_slot_id) {
                                        $ts = \App\Models\TimeSlot::find($appointment->time_slot_id);
                                        if ($ts) {
                                            if ($ts->slot_label) {
                                                $timeSlotDisplay = $ts->slot_label;
                                            } elseif ($ts->label) {
                                                $timeSlotDisplay = $ts->label;
                                            } elseif (isset($ts->time_slot)) {
                                                $timeSlotDisplay = $ts->time_slot;
                                            } elseif ($ts->start_time && $ts->end_time) {
                                                $timeSlotDisplay =
                                                    \Carbon\Carbon::parse($ts->start_time)->format('g:i A') .
                                                    ' - ' .
                                                    \Carbon\Carbon::parse($ts->end_time)->format('g:i A');
                                            } else {
                                                $timeSlotDisplay = 'ID: ' . $ts->id;
                                            }
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
    </div>

    <script>
        function exportToExcel() {
            const content = document.getElementById('export-content');
            const htmlContent = `
                <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                <head>
                    <meta charset="UTF-8">
                    <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
                    <x:Name>Pending Completed Report</x:Name>
                    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                    </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
                    <style>
                        table { border-collapse: collapse; }
                        th, td { padding: 8px; border: 1px solid #000; }
                        th { background-color: #0F3B6F; color: white; }
                        .status-pending { background: #10b981; color: white; padding: 3px 10px; border-radius: 12px; display: inline-block; }
                        .status-completed { background: #3b82f6; color: white; padding: 3px 10px; border-radius: 12px; display: inline-block; }
                        .total-row { font-weight: bold; background-color: #f0f4ff; }
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
            a.download = 'pending_completed_report_{{ $startDate }}_to_{{ $endDate }}.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>

</html>
