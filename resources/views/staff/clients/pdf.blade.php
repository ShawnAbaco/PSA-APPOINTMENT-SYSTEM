<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Applicant Export</title>
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
        .footer {
            margin-top: 18px;
            font-size: 11px;
            color: #525252;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Applicants List</div>
        <div class="subtitle">Generated on {{ now()->format('F j, Y H:i') }}</div>
        <div class="summary">Total records: {{ $clients->count() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Applicant</th>
                <th>Birthdate</th>
                <th>Service</th>
                <th>Reference No.</th>
                <th>Appointment #</th>
                <th>Appointment Date</th>
                <th>Appointment Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name . ' ' . $client->suffix) }}</td>
                    <td>{{ $client->birthdate ? date('F j, Y', strtotime($client->birthdate)) : '-' }}</td>
                    <td>{{ $serviceNames[$client->service] ?? $client->service }}</td>
                    <td>{{ $client->psa_reference_number ?: ($client->appointment?->reference_code ?? '-') }}</td>
                    <td>{{ $client->appointment?->appointment_number ?? '-' }}</td>
                    <td>{{ $client->appointment?->appointment_date ? date('F j, Y', strtotime($client->appointment->appointment_date)) : '-' }}</td>
                    <td>{{ $client->appointment?->timeSlot?->label ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">PSA Appointment System — Applicant export generated automatically.</div>
</body>
</html>
