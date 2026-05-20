{{-- resources/views/client/pdf.blade.php --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Confirmation</title>
    <style>
        /* Minimal, formal layout with reduced top spacing */
        body{font-family: Arial, Helvetica, sans-serif; color:#111; margin:0; padding:0}
        .container{padding:12px 18px; box-sizing:border-box}
        .header{display:flex; align-items:center; gap:12px; margin-bottom:8px}
        .logo{height:44px}
        h1{font-size:16px; margin:0; font-weight:700}
        .meta{margin-top:4px; color:#444; font-size:12px}
        .applicant{margin-top:8px}
        .label{color:#555; font-size:12px; margin-bottom:4px}
        .value{font-weight:700; font-size:14px; color:#111}
        .section{margin-top:8px}
        .page{page-break-after:always}
        @media print{ .page{page-break-after:always} }
    </style>
</head>
<body>
@php
    $appointmentRef = $appointment['reference'] ?? ($appointment_reference ?? '');
    $scheduleDisplay = $appointment['schedule_display'] ?? ($schedule_display ?? '');
    $contactName = $appointment['contact_name'] ?? ($contact_name ?? '');
    $clients = $clients ?? ($clients_list ?? []);
@endphp

@foreach($clients as $client)
    @php
        $name = trim($client['name'] ?? (($client['first_name'] ?? '') . ' ' . ($client['middle_name'] ?? '') . ' ' . ($client['last_name'] ?? '')));
        $serviceLabel = $client['service_name'] ?? $client['service_label'] ?? $client['service'] ?? '';
        $appointmentNumber = $appointment['number'] ?? $appointment['appointment_number'] ?? ($appointment['appointment_number'] ?? '');
        $referenceCode = $appointment['reference_code'] ?? $appointmentRef;
        $dateDisplay = $appointment['date'] ?? $scheduleDisplay;
        $timeSlot = $client['time_slot'] ?? $client['time_slot_label'] ?? ($client['time_slot'] ?? '');
        $contactMobile = $appointment['contact_mobile'] ?? $appointment['contact_phone'] ?? '';
        $contactEmail = $appointment['contact_email'] ?? '';
    @endphp

    <div class="page">
        <div class="container">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="vertical-align:middle;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <img src="{{ public_path('images/psa-logo.png') }}" alt="PSA" style="height:50px; object-fit:contain;" />
                            <div>
                                <div style="font-size:16px; font-weight:700; color:#0b3d91;">Philippine Statistics Authority</div>
                                <div style="font-size:12px; color:#444;">PhilSys Appointment Management System</div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:right; vertical-align:top;">
                        <div style="font-size:12px; color:#444;">Generated: {{ now()->format('F d, Y') }}</div>
                        <div style="margin-top:8px; font-size:12px;">Appointment #: <strong>{{ $appointmentNumber }}</strong></div>
                        <div style="margin-top:6px; font-size:12px;">Reference: <strong>{{ $referenceCode }}</strong></div>
                    </td>
                </tr>
            </table>

            <hr style="border:none; border-top:1px solid #e6e6e6; margin:12px 0 18px 0;">

            <table style="width:100%; font-size:13px;">
                <tr>
                    <td style="width:65%; vertical-align:top; padding-right:12px;">
                        <div style="margin-bottom:10px;"><span style="color:#555;">Applicant Name</span><div style="font-weight:700; font-size:15px; margin-top:6px;">{{ $name }}</div></div>

                        <div style="margin-bottom:10px;"><span style="color:#555;">Service</span><div style="font-weight:700; margin-top:6px;">{{ $serviceLabel }}</div></div>

                        <div style="margin-bottom:10px;"><span style="color:#555;">Schedule</span>
                        <div style="font-weight:700; margin-top:6px;">{{ $dateDisplay }} {{ $timeSlot ? '• ' . $timeSlot : '' }}</div></div>

                        <div style="margin-bottom:10px;"><span style="color:#555;">Contact Person</span>
                        <div style="font-weight:700; margin-top:6px;">{{ $appointment['contact_name'] ?? '' }}</div></div>

                        <div style="margin-bottom:10px; display:flex; gap:12px;">
                            <div style="flex:1;"><span style="color:#555;">Contact Number</span><div style="font-weight:700; margin-top:6px;">{{ $contactMobile }}</div></div>
                            <div style="flex:1;"><span style="color:#555;">Email</span><div style="font-weight:700; margin-top:6px;">{{ $contactEmail }}</div></div>
                        </div>
                    </td>
                    <td style="width:35%; vertical-align:top; text-align:center;">
                        <div style="border:1px solid #e6e6e6; padding:10px; border-radius:6px; background:#fafafa;">
                            <div style="font-size:12px; color:#666;">Reference Code</div>
                            <div style="font-weight:700; font-size:18px; margin-top:8px;">{{ $referenceCode }}</div>
                        </div>

                        <div style="margin-top:14px; font-size:12px; color:#666;">Applicant No.</div>
                        <div style="font-weight:700; font-size:14px; margin-top:6px;">{{ $client['client_number'] ?? '' }}</div>
                    </td>
                </tr>
            </table>

            <div style="border-top:1px solid #e6e6e6; margin:18px 0 12px 0;"></div>

            <div style="font-size:11px; color:#666;">Please arrive at least <strong>30 minutes</strong> before your scheduled time and bring all required documents for verification. This confirmation slip is per applicant; keep it for verification at the PSA office.</div>

            <div style="margin-top:18px; font-size:11px; color:#777; text-align:left;">
                <strong>Office:</strong> Capt. Vicente Roa Street, Brgy. 31, Cagayan de Oro City, 9000 Misamis Oriental<br>
                <strong>Note:</strong> This is a system-generated document. For inquiries contact PSA.
            </div>
        </div>
    </div>
@endforeach

</body>
</html>
