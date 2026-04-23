@extends('layouts.admin')

@section('content')
    <div class="settings-container">
        <!-- Modern Header -->
        <div class="settings-welcome-section">
            <div>
                <h1 class="settings-title">System Settings</h1>
                <p class="settings-subtitle">Configure appointment rules, time slots, and email settings</p>
            </div>
            <div class="settings-date-display">
                <button type="button" class="settings-clear-cache-btn" id="refreshCacheBtn">
                    <i class="fas fa-sync-alt"></i> Clear Cache
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" id="settingsForm">
            @csrf

            <!-- Appointment Settings -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h5 class="settings-card-title"><i class="fas fa-calendar-alt"></i> Appointment Settings</h5>
                </div>
                <div class="settings-card-body">
                    <div class="settings-form-row">
                        <div class="settings-form-group">
                            <label class="settings-form-label">Advance Booking Days</label>
                            <div class="settings-input-group">
                                <span class="settings-input-group-icon"><i class="fas fa-calendar-week"></i></span>
                                <input type="number" name="advance_booking_days" class="settings-form-control"
                                    value="{{ $settings['advance_booking_days'] ?? 30 }}" min="1" max="365">
                            </div>
                            <small class="settings-form-text">How many days in advance users can book appointments</small>
                        </div>

                        <div class="settings-form-group">
                            <label class="settings-form-label">Cancellation Hours</label>
                            <div class="settings-input-group">
                                <span class="settings-input-group-icon"><i class="fas fa-clock"></i></span>
                                <input type="number" name="cancellation_hours" class="settings-form-control"
                                    value="{{ $settings['cancellation_hours'] ?? 24 }}" min="1" max="168">
                            </div>
                            <small class="settings-form-text">Hours before appointment to allow cancellation</small>
                        </div>

                        <div class="settings-form-group">
                            <label class="settings-form-label">Enable Email Notifications</label>
                            <select name="enable_email" class="settings-form-select">
                                <option value="true" {{ ($settings['enable_email'] ?? true) == true ? 'selected' : '' }}>
                                    Yes</option>
                                <option value="false" {{ ($settings['enable_email'] ?? true) == false ? 'selected' : '' }}>
                                    No</option>
                            </select>
                            <small class="settings-form-text">Send email notifications for appointment confirmations</small>
                        </div>

                        <div class="settings-form-group">
                            <label class="settings-form-label">Enable Per-Service Limits</label>
                            <select name="enable_per_service_limits" class="settings-form-select">
                                <option value="true"
                                    {{ ($settings['enable_per_service_limits'] ?? true) == true ? 'selected' : '' }}>Yes
                                </option>
                                <option value="false"
                                    {{ ($settings['enable_per_service_limits'] ?? true) == false ? 'selected' : '' }}>No
                                </option>
                            </select>
                            <small class="settings-form-text">Enable separate slot limits for each service type</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time Slots Configuration -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h5 class="settings-card-title"><i class="fas fa-clock"></i> Time Slots Configuration</h5>
                </div>
                <div class="settings-card-body">
                    <div class="settings-alert settings-alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Time slots define when appointments can be scheduled. Each time slot has default capacity
                            rules that can be overridden per date.</span>
                    </div>

                    <div id="timeSlotsContainer">
                        @php
                            $timeSlots = App\Models\TimeSlot::orderBy('display_order')->get();
                        @endphp

                        @if ($timeSlots->count() > 0)
                            <div class="settings-table-responsive">
                                <table class="settings-table">
                                    <thead>
                                        <tr>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Label</th>
                                            <th>Default Capacity</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($timeSlots as $slot)
                                            <tr>
                                                <td><strong>{{ date('g:i A', strtotime($slot->start_time)) }}</strong></td>
                                                <td><strong>{{ date('g:i A', strtotime($slot->end_time)) }}</strong></td>
                                                <td><span
                                                        class="settings-badge settings-badge-primary">{{ $slot->label }}</span>
                                                </td>
                                                <td>
                                                    <span class="settings-badge settings-badge-info">
                                                        <i class="fas fa-users"></i> {{ $slot->capacity_per_slot ?? 4 }}
                                                        per slot
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="settings-badge {{ $slot->is_active ? 'settings-badge-success' : 'settings-badge-danger' }}">
                                                        <i
                                                            class="fas {{ $slot->is_active ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                        {{ $slot->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="settings-btn-group">
                                                        <button type="button"
                                                            class="settings-btn settings-btn-outline-primary settings-btn-sm edit-time-slot"
                                                            data-id="{{ $slot->id }}"
                                                            data-start_time="{{ $slot->start_time }}"
                                                            data-end_time="{{ $slot->end_time }}"
                                                            data-slot_label="{{ $slot->label }}"
                                                            data-capacity="{{ $slot->capacity_per_slot ?? 4 }}"
                                                            data-active="{{ $slot->is_active }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="settings-btn settings-btn-outline-danger settings-btn-sm delete-time-slot"
                                                            data-id="{{ $slot->id }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="settings-alert settings-alert-warning">No time slots configured. Add time slots
                                below.</div>
                        @endif

                        <hr class="settings-divider">

                        <div class="settings-time-slots-container">
                            <h6 class="settings-section-title">
                                <i class="fas fa-plus-circle"></i> Add New Time Slot
                            </h6>
                            <div class="settings-form-row">
                                <div class="settings-form-group">
                                    <label class="settings-form-label">Start Time</label>
                                    <input type="time" id="new_start_time" class="settings-form-control">
                                </div>
                                <div class="settings-form-group">
                                    <label class="settings-form-label">End Time</label>
                                    <input type="time" id="new_end_time" class="settings-form-control">
                                </div>
                                <div class="settings-form-group">
                                    <label class="settings-form-label">Label (optional)</label>
                                    <input type="text" id="new_slot_label" class="settings-form-control"
                                        placeholder="e.g., 9:00 AM - 10:00 AM">
                                </div>
                                <div class="settings-form-group">
                                    <label class="settings-form-label">Capacity</label>
                                    <input type="number" id="new_capacity" class="settings-form-control" value="4"
                                        min="1" max="50">
                                </div>
                                <div class="settings-form-group settings-form-group-btn">
                                    <button type="button" id="addTimeSlotBtn" class="settings-btn settings-btn-success">
                                        <i class="fas fa-plus"></i> Add Time Slot
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Default Capacity Rules -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h5 class="settings-card-title"><i class="fas fa-sliders-h"></i> Default Capacity Rules</h5>
                </div>
                <div class="settings-card-body">
                    <div class="settings-alert settings-alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <span>These are the default capacity rules for working and non-working days. When you create a new
                            slot without an override, these values will be used. <strong>Changes here will NOT affect
                                existing slots that have manual overrides.</strong></span>
                    </div>

                    <div class="settings-table-responsive">
                        <table class="settings-table settings-table-bordered">
                            <thead class="settings-table-dark">
                                <tr>
                                    <th style="width: 200px">Time Slot</th>
                                    <th style="width: 120px">Day Type</th>
                                    <th>Reason</th>
                                    <th>Registration (R)</th>
                                    <th>Updating (U)</th>
                                    <th>Inquiry (S)</th>
                                </tr>
                            </thead>
                            <tbody id="capacityRulesTableBody">
                                @foreach ($timeSlots as $timeSlot)
                                    @php
                                        $dayTypes = ['working', 'non_working'];
                                        $dayTypeNames = [
                                            'working' => 'Working Day',
                                            'non_working' => 'Non-Working Day',
                                        ];
                                        $dayTypeColors = ['working' => 'success', 'non_working' => 'danger'];
                                    @endphp
                                    @foreach ($dayTypes as $dayType)
                                        @php
                                            $rule =
                                                $capacityRules[$timeSlot->id]->firstWhere('day_type', $dayType) ?? null;
                                            $defaultValues = [
                                                'working' => ['reg' => 4, 'updating' => 4, 'inquiry' => 4],
                                                'non_working' => ['reg' => 0, 'updating' => 0, 'inquiry' => 0],
                                            ];
                                        @endphp
                                        <tr>
                                            @if ($loop->first)
                                                <td rowspan="{{ count($dayTypes) }}" class="settings-time-slot-cell">
                                                    <strong>{{ $timeSlot->label }}</strong><br>
                                                    <small
                                                        class="settings-text-muted">{{ date('g:i A', strtotime($timeSlot->start_time)) }}
                                                        - {{ date('g:i A', strtotime($timeSlot->end_time)) }}</small>
                                                </td>
                                            @endif
                                            <td>
                                                <span
                                                    class="settings-badge settings-badge-{{ $dayTypeColors[$dayType] }}">
                                                    {{ $dayTypeNames[$dayType] }}
                                                </span>
                                            </td>
                                            <td>
                                                <small
                                                    class="settings-text-muted">{{ $rule->reason ?? ($dayType === 'working' ? 'Regular working day' : 'Non-working day') }}</small>
                                            </td>
                                            <td>
                                                <input type="number"
                                                    name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][reg]"
                                                    class="settings-capacity-input"
                                                    value="{{ $rule->reg_capacity ?? $defaultValues[$dayType]['reg'] }}"
                                                    min="0" max="100">
                                            </td>
                                            <td>
                                                <input type="number"
                                                    name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][updating]"
                                                    class="settings-capacity-input"
                                                    value="{{ $rule->updating_capacity ?? $defaultValues[$dayType]['updating'] }}"
                                                    min="0" max="100">
                                            </td>
                                            <td>
                                                <input type="number"
                                                    name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][inquiry]"
                                                    class="settings-capacity-input"
                                                    value="{{ $rule->inquiry_capacity ?? $defaultValues[$dayType]['inquiry'] }}"
                                                    min="0" max="100">
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="settings-alert settings-alert-info settings-mt-3">
                        <i class="fas fa-info-circle"></i>
                        <span><strong>Note:</strong> Working days are Tuesday to Friday. Non-working days include Monday,
                            Saturday, and Sunday (or any day marked as non-working in Working Days Configuration).</span>
                    </div>

                    <div class="settings-action-group">
                        <button type="button" id="saveCapacityRulesBtn" class="settings-btn settings-btn-primary">
                            <i class="fas fa-save"></i> Save Default Capacity Rules
                        </button>
                        <button type="button" id="resetCapacityRulesBtn"
                            class="settings-btn settings-btn-outline-secondary">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>

            <!-- Working Days Configuration -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h5 class="settings-card-title"><i class="fas fa-calendar-week"></i> Working Days Configuration</h5>
                </div>
                <div class="settings-card-body">
                    @php
                        $workingDays = isset($settings['working_days'])
                            ? explode(',', $settings['working_days'])
                            : ['2', '3', '4', '5'];
                        $dayNames = [
                            '1' => 'Monday',
                            '2' => 'Tuesday',
                            '3' => 'Wednesday',
                            '4' => 'Thursday',
                            '5' => 'Friday',
                            '6' => 'Saturday',
                            '7' => 'Sunday',
                        ];
                    @endphp

                    <div class="settings-days-container">
                        @foreach ($dayNames as $value => $name)
                            <div>
                                <input type="checkbox" name="working_days[]" value="{{ $value }}"
                                    id="day{{ $value }}" class="settings-day-checkbox"
                                    {{ in_array($value, $workingDays) ? 'checked' : '' }}>
                                <label class="settings-day-label" for="day{{ $value }}">
                                    <i class="fas fa-calendar-day"></i> {{ $name }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <small class="settings-form-text">Select which days appointments can be booked. Non-working days will
                        not appear in the client calendar.</small>

                    <div class="settings-button-group settings-mt-3">
                        <button type="button" id="selectWeekdays"
                            class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                            <i class="fas fa-briefcase"></i> Weekdays (Mon-Fri)
                        </button>
                        <button type="button" id="selectWeekends"
                            class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                            <i class="fas fa-umbrella-beach"></i> Weekends (Sat-Sun)
                        </button>
                        <button type="button" id="selectAllDays"
                            class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                            <i class="fas fa-calendar-week"></i> All Days
                        </button>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <h5 class="settings-card-title"><i class="fas fa-envelope"></i> Email (SMTP) Settings</h5>
                </div>
                <div class="settings-card-body">
                    <div class="settings-alert settings-alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Configure your SMTP settings to enable email notifications. For Gmail, you need to use an
                            <strong>App Password</strong> (enable 2FA first).</span>
                    </div>

                    <div class="settings-form-row">
                        <div class="settings-form-group">
                            <label class="settings-form-label">SMTP Host</label>
                            <div class="settings-input-group">
                                <span class="settings-input-group-icon"><i class="fas fa-server"></i></span>
                                <input type="text" name="email_host" class="settings-form-control"
                                    value="{{ $settings['email_host'] ?? 'smtp.gmail.com' }}"
                                    placeholder="smtp.gmail.com">
                            </div>
                            <small class="settings-form-text">e.g., smtp.gmail.com, smtp.office365.com,
                                mail.yourdomain.com</small>
                        </div>

                        <div class="settings-form-group">
                            <label class="settings-form-label">SMTP Port</label>
                            <div class="settings-input-group">
                                <span class="settings-input-group-icon"><i class="fas fa-plug"></i></span>
                                <input type="number" name="email_port" class="settings-form-control"
                                    value="{{ $settings['email_port'] ?? 587 }}" placeholder="587">
                            </div>
                            <small class="settings-form-text">587 for TLS, 465 for SSL</small>
                        </div>

                        <div class="settings-form-group">
                            <label class="settings-form-label">Encryption</label>
                            <select name="email_encryption" class="settings-form-select">
                                <option value="tls"
                                    {{ ($settings['email_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS
                                    (Recommended)</option>
                                <option value="ssl"
                                    {{ ($settings['email_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                            <small class="settings-form-text">Security protocol for email sending</small>
                        </div>

                        <div class="settings-form-group">
                            <label class="settings-form-label">SMTP Username</label>
                            <div class="settings-input-group">
                                <span class="settings-input-group-icon"><i class="fas fa-user"></i></span>
                                <input type="text" name="email_username" class="settings-form-control"
                                    value="{{ $settings['email_username'] ?? '' }}" placeholder="your-email@gmail.com">
                            </div>
                            <small class="settings-form-text">Your email address (e.g., your-email@gmail.com)</small>
                        </div>

                        <div class="settings-form-group">
                            <label class="settings-form-label">SMTP Password</label>
                            <div class="settings-input-group">
                                <span class="settings-input-group-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" name="email_password" id="email_password"
                                    class="settings-form-control" value="{{ $settings['email_password'] ?? '' }}"
                                    placeholder="Enter new password to change" autocomplete="off">
                                <button type="button" id="togglePasswordBtn"
                                    class="settings-btn settings-btn-outline-secondary settings-btn-icon">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <small class="settings-form-text" id="passwordStatus">
                                @if (!empty($settings['email_password']) && $settings['email_password'] === '********')
                                    Password is set. Enter new password to change it.
                                @elseif(!empty($settings['email_password']))
                                    Password is configured.
                                @else
                                    No password set. Enter your SMTP password.
                                @endif
                            </small>
                        </div>

                        <div class="settings-form-group">
                            <label class="settings-form-label">From Email Address</label>
                            <div class="settings-input-group">
                                <span class="settings-input-group-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email_from_address" class="settings-form-control"
                                    value="{{ $settings['email_from_address'] ?? 'noreply@psa.gov.ph' }}"
                                    placeholder="noreply@psa.gov.ph">
                            </div>
                            <small class="settings-form-text">Sender email address (usually same as SMTP username)</small>
                        </div>

                        <div class="settings-form-group settings-form-group-full">
                            <label class="settings-form-label">From Name</label>
                            <div class="settings-input-group">
                                <span class="settings-input-group-icon"><i class="fas fa-tag"></i></span>
                                <input type="text" name="email_from_name" class="settings-form-control"
                                    value="{{ $settings['email_from_name'] ?? 'PSA Appointment System' }}"
                                    placeholder="PSA Appointment System">
                            </div>
                            <small class="settings-form-text">Sender name that recipients will see</small>
                        </div>
                    </div>

                    <div class="settings-test-email">
                        <button type="button" class="settings-btn settings-btn-outline-primary" id="testEmailBtn">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                        <small class="settings-form-text">Test your email configuration by sending a test message</small>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="settings-action-buttons">
                <button type="submit" class="settings-btn settings-btn-primary settings-btn-lg">
                    <i class="fas fa-save"></i> Save All Settings
                </button>
                <a href="{{ route('admin.dashboard') }}"
                    class="settings-btn settings-btn-outline-secondary settings-btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Day selection helpers
                $('#selectWeekdays').click(function() {
                    $('.settings-day-checkbox').each(function() {
                        let val = parseInt($(this).val());
                        $(this).prop('checked', val >= 1 && val <= 5);
                    });
                });

                $('#selectWeekends').click(function() {
                    $('.settings-day-checkbox').each(function() {
                        let val = parseInt($(this).val());
                        $(this).prop('checked', val === 6 || val === 7);
                    });
                });

                $('#selectAllDays').click(function() {
                    $('.settings-day-checkbox').prop('checked', true);
                });

                // Password toggle functionality
                const passwordField = document.getElementById('email_password');
                const toggleBtn = document.getElementById('togglePasswordBtn');
                const toggleIcon = document.getElementById('togglePasswordIcon');
                const passwordStatus = document.getElementById('passwordStatus');

                if (toggleBtn && passwordField) {
                    toggleBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordField.setAttribute('type', type);
                        if (type === 'text') {
                            toggleIcon.classList.remove('fa-eye');
                            toggleIcon.classList.add('fa-eye-slash');
                        } else {
                            toggleIcon.classList.remove('fa-eye-slash');
                            toggleIcon.classList.add('fa-eye');
                        }
                    });

                    passwordField.addEventListener('input', function() {
                        if (this.value.length > 0 && passwordStatus) {
                            passwordStatus.innerHTML =
                                '<i class="fas fa-edit"></i> New password will be saved when you submit the form.';
                            passwordStatus.style.color = '#f59e0b';
                        } else if (this.value.length === 0 && passwordStatus) {
                            passwordStatus.innerHTML =
                                '<i class="fas fa-lock"></i> Leave empty to keep current password.';
                            passwordStatus.style.color = '#6b7280';
                        }
                    });
                }

                // Add Time Slot
                $('#addTimeSlotBtn').click(function() {
                    const startTime = $('#new_start_time').val();
                    const endTime = $('#new_end_time').val();
                    const slotLabel = $('#new_slot_label').val();
                    const capacity = $('#new_capacity').val();

                    if (!startTime || !endTime) {
                        showToast('Missing Information', 'Please enter start time and end time', 'warning');
                        return;
                    }

                    $.ajax({
                        url: '/admin/time-slots/store',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            start_time: startTime,
                            end_time: endTime,
                            slot_label: slotLabel,
                            capacity_per_slot: capacity
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('Success!', 'Time slot added successfully', 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showToast('Error', response.message || 'Error adding time slot',
                                    'error');
                            }
                        },
                        error: function(xhr) {
                            showToast('Error', xhr.responseJSON?.message ||
                                'Failed to add time slot', 'error');
                        }
                    });
                });

                // Edit Time Slot
                $('.edit-time-slot').click(function() {
                    const id = $(this).data('id');
                    const startTime = $(this).data('start_time');
                    const endTime = $(this).data('end_time');
                    const slotLabel = $(this).data('slot_label');
                    const capacity = $(this).data('capacity');
                    const isActive = $(this).data('active');

                    const newStartTime = prompt('Enter start time (HH:MM:SS):', startTime);
                    if (!newStartTime) return;
                    const newEndTime = prompt('Enter end time (HH:MM:SS):', endTime);
                    if (!newEndTime) return;
                    const newSlotLabel = prompt('Enter slot label (optional):', slotLabel);
                    const newCapacity = prompt('Enter capacity per slot:', capacity);
                    if (!newCapacity) return;
                    const newIsActive = confirm('Is this time slot active? Click OK for Yes, Cancel for No');

                    $.ajax({
                        url: '/admin/time-slots/' + id,
                        method: 'PUT',
                        data: {
                            _token: '{{ csrf_token() }}',
                            start_time: newStartTime,
                            end_time: newEndTime,
                            slot_label: newSlotLabel,
                            capacity_per_slot: newCapacity,
                            is_active: newIsActive ? 1 : 0
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('Success!', 'Time slot updated successfully', 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showToast('Error', response.message || 'Error updating time slot',
                                    'error');
                            }
                        },
                        error: function(xhr) {
                            showToast('Error', xhr.responseJSON?.message ||
                                'Failed to update time slot', 'error');
                        }
                    });
                });

                // Delete Time Slot
                $('.delete-time-slot').click(function() {
                    const id = $(this).data('id');
                    if (confirm(
                            'Are you sure you want to delete this time slot? This may affect existing appointments.'
                            )) {
                        $.ajax({
                            url: '/admin/time-slots/' + id,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('Deleted!', 'Time slot has been deleted', 'success');
                                    setTimeout(() => location.reload(), 1500);
                                } else {
                                    showToast('Error', response.message ||
                                        'Error deleting time slot', 'error');
                                }
                            },
                            error: function(xhr) {
                                showToast('Error', xhr.responseJSON?.message ||
                                    'Failed to delete time slot', 'error');
                            }
                        });
                    }
                });

                // Save Capacity Rules
                $('#saveCapacityRulesBtn').click(function() {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');

                    $('.settings-capacity-input').each(function() {
                        const name = $(this).attr('name');
                        const value = $(this).val();
                        formData.append(name, value);
                    });

                    showToast('Saving...', 'Please wait while we save your settings', 'info');

                    $.ajax({
                        url: '{{ route('admin.slots.capacity-rules') }}',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                showToast('Success!', 'Capacity rules saved successfully!',
                                    'success');
                            } else {
                                showToast('Error', response.message ||
                                    'Error saving capacity rules', 'error');
                            }
                        },
                        error: function(xhr) {
                            showToast('Error', xhr.responseJSON?.message ||
                                'Failed to save capacity rules', 'error');
                        }
                    });
                });

                // Reset Capacity Rules
                $('#resetCapacityRulesBtn').click(function() {
                    if (confirm('Reset all capacity rules to default values? This cannot be undone.')) {
                        $('.settings-capacity-input').each(function() {
                            const name = $(this).attr('name');
                            if (name.includes('working')) {
                                if (name.includes('reg')) $(this).val(4);
                                else if (name.includes('updating')) $(this).val(4);
                                else if (name.includes('inquiry')) $(this).val(4);
                            } else if (name.includes('non_working')) {
                                $(this).val(0);
                            }
                        });
                        showToast('Reset Complete',
                            'Values reset to defaults (Working days: 4 each, Non-working days: 0). Click "Save Default Capacity Rules" to apply.',
                            'success');
                    }
                });

                // Test email functionality
                $('#testEmailBtn').click(function() {
                    const testEmail = prompt('Enter email address to send test email:',
                        '{{ Auth::user()->email }}');
                    if (!testEmail) return;

                    const originalText = $(this).html();
                    $(this).html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);

                    $.ajax({
                        url: '{{ route('admin.settings.test-email') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            email: testEmail
                        },
                        success: function(response) {
                            if (response.success) {
                                showToast('Success!', response.message, 'success');
                            } else {
                                showToast('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to send test email. Check your SMTP settings.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showToast('Error', errorMsg, 'error');
                        },
                        complete: function() {
                            $('#testEmailBtn').html(originalText).prop('disabled', false);
                        }
                    });
                });

                // Clear cache
                $('#refreshCacheBtn').click(function() {
                    if (confirm('Clear system cache? This may temporarily slow down the system.')) {
                        $.ajax({
                            url: '{{ route('admin.settings.clear-cache') }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('Success!', 'Cache cleared successfully!', 'success');
                                } else {
                                    showToast('Error', 'Error clearing cache', 'error');
                                }
                            },
                            error: function() {
                                showToast('Error', 'Error clearing cache', 'error');
                            }
                        });
                    }
                });

                // Form submit with loading state
                $('#settingsForm').on('submit', function() {
                    showToast('Saving Settings...', 'Please wait while we save your configuration', 'info');
                });
            });

            // Toast notification function
            function showToast(title, message, type) {
                alert(title + ': ' + message);
            }
        </script>
    @endpush
@endsection
