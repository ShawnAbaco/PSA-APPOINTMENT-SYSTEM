<div class="modal-content-wrapper">
    <div class="row g-4 justify-content-center" style="display: flex; gap: 24px; flex-wrap: wrap;">
        <div class="col-lg-5" style="flex: 1; min-width: 300px;">
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-user-circle"></i>
                    <span>Personal Information</span>
                </div>
                <div class="detail-card-body">
                    <table class="detail-table">
                        <tr>
                            <th class="detail-th">Full Name</th>
                            <td class="detail-td highlight">{{ $client->full_name }}</td>
                        </tr>
                        <tr>
                            <th class="detail-th">First Name</th>
                            <td class="detail-td">{{ $client->first_name }}</td>
                        </tr>
                        <tr>
                            <th class="detail-th">Middle Name</th>
                            <td class="detail-td">{{ $client->middle_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="detail-th">Last Name</th>
                            <td class="detail-td">{{ $client->last_name }}</td>
                        </tr>
                        @if ($client->suffix)
                            <tr>
                                <th class="detail-th">Suffix</th>
                                <td class="detail-td">{{ $client->suffix }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="detail-th">Sex</th>
                            <td class="detail-td">
                                <span class="sex-badge-modal {{ $client->sex == 'Male' ? 'male' : 'female' }}">
                                    <i class="fas {{ $client->sex == 'Male' ? 'fa-mars' : 'fa-venus' }}"></i>
                                    {{ $client->sex }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="detail-th">Birthdate</th>
                            <td class="detail-td">{{ date('F d, Y', strtotime($client->birthdate)) }}</td>
                        </tr>
                        <tr>
                            <th class="detail-th">Age</th>
                            <td class="detail-td">{{ \Carbon\Carbon::parse($client->birthdate)->age }} years old</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5" style="flex: 1; min-width: 300px;">
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointment & Service Details</span>
                </div>
                <div class="detail-card-body">
                    <table class="detail-table">
                        <tr>
                            <th class="detail-th">Service</th>
                            <td class="detail-td">
                                <span
                                    class="service-badge-modal">{{ $services[$client->service] ?? $client->service }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="detail-th">Appointment</th>
                            <td class="detail-td">
                                @if ($client->appointment)
                                    <a href="{{ route('admin.appointments.index', $client->appointment->id) }}"
                                        class="appointment-link-modal">
                                        <i class="fas fa-link"></i> {{ $client->appointment->appointment_number }}
                                    </a>
                                    <br>
                                    <small
                                        class="date-text">{{ date('F d, Y', strtotime($client->appointment->appointment_date)) }}</small>
                                @else
                                    <span class="text-muted-modal">No appointment found</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="detail-th">Verification Status</th>
                            <td class="detail-td">
                                @if ($client->is_verified)
                                    <span class="status-badge-modal verified">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </span>
                                    <br>
                                    <small
                                        class="date-text">{{ date('F d, Y h:i A', strtotime($client->verified_at)) }}</small>
                                @else
                                    <span class="status-badge-modal pending">
                                        <i class="fas fa-clock"></i> Pending Verification
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="detail-th">PSA Reference Number (TRN)</th>
                            <td class="detail-td">
                                @if ($client->psa_reference_number)
                                    <code class="trn-code">{{ $client->psa_reference_number }}</code>
                                @else
                                    <span class="text-muted-modal">Not yet assigned</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if (!$client->psa_reference_number)
                        <div class="assign-trn-section">
                            <label class="section-label">Assign TRN/Reference Number</label>
                            <div class="input-group-modern">
                                <input type="text" id="modalReferenceNumber" class="modern-input"
                                    placeholder="Enter TRN...">
                                <button class="modern-btn update-reference-btn" data-id="{{ $client->id }}">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
