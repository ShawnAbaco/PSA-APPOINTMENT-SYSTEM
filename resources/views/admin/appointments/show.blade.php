<!-- resources/views/admin/appointments/show.blade.php -->
<div class="appt-modal-details">
    <div class="appt-details-section">
        <div class="appt-details-section-title">
            <i class="fas fa-info-circle"></i>
            Appointment Information
        </div>
        <div class="appt-details-grid">
            <div class="appt-details-item">
                <label>Appointment #:</label>
                <span class="appt-details-value">{{ $appointment->appointment_number }}</span>
            </div>
            <div class="appt-details-item">
                <label>Date:</label>
                <span class="appt-details-value">{{ date('F d, Y', strtotime($appointment->appointment_date)) }}</span>
            </div>
            <div class="appt-details-item">
                <label>Time:</label>
                <span
                    class="appt-details-value">{{ date('h:i A', strtotime($appointment->appointment_time ?? '09:00')) }}</span>
            </div>
            <div class="appt-details-item">
                <label>Type:</label>
                <span class="appt-details-value">{{ ucfirst($appointment->type) }}</span>
            </div>
            <div class="appt-details-item">
                <label>Status:</label>
                <span class="appt-status-badge appt-status-{{ $appointment->status }}">
                    {{ ucfirst($appointment->status) }}
                </span>
            </div>
            <div class="appt-details-item">
                <label>Reference Code:</label>
                <code class="appt-reference-code">{{ $appointment->reference_code ?? 'N/A' }}</code>
            </div>
        </div>
    </div>

    <div class="appt-details-section">
        <div class="appt-details-section-title">
            <i class="fas fa-user"></i>
            Contact Information
        </div>
        <div class="appt-details-grid">
            <div class="appt-details-item">
                <label>Name:</label>
                <span class="appt-details-value"><strong>{{ $appointment->contact_name }}</strong></span>
            </div>
            <div class="appt-details-item">
                <label>Email:</label>
                <span class="appt-details-value">{{ $appointment->contact_email ?? 'N/A' }}</span>
            </div>
            <div class="appt-details-item">
                <label>Mobile:</label>
                <span
                    class="appt-details-value">{{ $appointment->contact_mobile ?? ($appointment->contact_phone ?? 'N/A') }}</span>
            </div>
        </div>
    </div>

    <div class="appt-details-section">
        <div class="appt-details-section-title">
            <i class="fas fa-users"></i>
            Applicants Information
            <span class="appt-client-count-badge">{{ $appointment->clients->count() }} applicant(s)</span>
        </div>
        <div class="appt-table-wrapper">
            <table class="appt-table appt-table-mini">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Sex</th>
                        <th>Birthdate</th>
                        <th>Service</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointment->clients as $index => $client)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}
                                    {{ $client->suffix }}</strong>
                            </td>
                            <td>{{ $client->sex ?? 'N/A' }}</td>
                            <td>{{ $client->birthdate ? date('M d, Y', strtotime($client->birthdate)) : 'N/A' }}</td>
                            <td>{{ $client->service_name ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
