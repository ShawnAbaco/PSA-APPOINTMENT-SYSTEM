<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Appointments Report Export</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 24px;
        }
        .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .subtitle {
            font-size: 12px;
            color: #4b5563;
            margin-bottom: 12px;
        }
        .summary {
            font-size: 12px;
            color: #374151;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f9fafb;
            color: #111827;
            font-weight: 700;
        }
        td {
            color: #111827;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        .status-confirmed {
            background-color: #dcfce7;
            color: #15803d;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
        }
        .footer {
            margin-top: 18px;
            font-size: 11px;
            color: #525252;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Appointments Report</div>
        <div class="subtitle">Generated on {{ now()->format('F j, Y H:i') }}</div>
        <div class="summary">Total records: {{ count($appointments ?? []) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Appointment #</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Contact Person</th>
                <th>Contact Mobile</th>
                <th>Status</th>
                <th># of Applicants</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments ?? [] as $appointment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $appointment->appointment_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</td>
                    <td>
                        @if ($appointment->timeSlot)
                            {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('g:i A') }}
                            -
                            {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('g:i A') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $appointment->contact_name }}</td>
                    <td>{{ $appointment->contact_mobile }}</td>
                    <td>
                        <span class="status-{{ strtolower($appointment->status) }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </td>
                    <td>{{ $appointment->clients->count() }}</td>
                    <td>{{ $appointment->user_city ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No appointments found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">PSA Appointment System — Appointments report generated automatically.</div>
</body>
</html>
