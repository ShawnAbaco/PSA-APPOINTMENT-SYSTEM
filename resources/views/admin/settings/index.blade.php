@extends('layouts.admin')

@section('content')
    <div class="settings-container">
        <!-- Modern Header -->
        <div class="settings-welcome-section">
            <div>
                <h1 class="settings-title">System Settings</h1>
                <p class="settings-subtitle">Configure appointment rules, time slots, and working days</p>
            </div>
            <div class="settings-date-display">
                <button type="button" class="settings-clear-cache-btn" id="refreshCacheBtn">
                    <i class="fas fa-sync-alt"></i> Clear Cache
                </button>
            </div>
        </div>

        @csrf

        <!-- Appointment Settings -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h5 class="settings-card-title"><i class="fas fa-calendar-alt"></i> Appointment Settings</h5>
            </div>
            <div class="settings-card-body">
                <div class="settings-alert settings-alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Configure general appointment rules and notification preferences.</span>
                </div>
                
                <div class="settings-form-row">
                    <div class="settings-form-group">
                        <label class="settings-form-label">Advance Booking Days</label>
                        <div class="settings-input-group">
                            <span class="settings-input-group-icon"><i class="fas fa-calendar-week"></i></span>
                            <input type="number" name="advance_booking_days" id="advance_booking_days" class="settings-form-control"
                                value="{{ $settings['advance_booking_days'] ?? 30 }}" min="1" max="365">
                        </div>
                        <small class="settings-form-text">How many days in advance users can book appointments</small>
                    </div>

                    <div class="settings-form-group">
                        <label class="settings-form-label">Cancellation Hours</label>
                        <div class="settings-input-group">
                            <span class="settings-input-group-icon"><i class="fas fa-clock"></i></span>
                            <input type="number" name="cancellation_hours" id="cancellation_hours" class="settings-form-control"
                                value="{{ $settings['cancellation_hours'] ?? 24 }}" min="1" max="168">
                        </div>
                        <small class="settings-form-text">Hours before appointment to allow cancellation</small>
                    </div>

                    <div class="settings-form-group">
                        <label class="settings-form-label">Enable Email Notifications</label>
                        <select name="enable_email" id="enable_email" class="settings-form-select">
                            <option value="true" {{ ($settings['enable_email'] ?? true) == true ? 'selected' : '' }}>
                                Yes</option>
                            <option value="false" {{ ($settings['enable_email'] ?? true) == false ? 'selected' : '' }}>
                                No</option>
                        </select>
                        <small class="settings-form-text">Send email notifications for appointment confirmations</small>
                    </div>

                    <div class="settings-form-group">
                        <label class="settings-form-label">Enable Per-Service Limits</label>
                        <select name="enable_per_service_limits" id="enable_per_service_limits" class="settings-form-select">
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

                <!-- Appointment Settings Action Buttons -->
                <div class="settings-action-group">
                    <button type="button" id="saveAppointmentSettingsBtn" class="settings-btn settings-btn-primary">
                        <i class="fas fa-save"></i> Save Appointment Settings
                    </button>
                    <button type="button" id="resetAppointmentSettingsBtn" class="settings-btn settings-btn-outline-secondary">
                        <i class="fas fa-undo"></i> Reset to Defaults
                    </button>
                </div>
                <span id="appointmentSettingsStatus" style="display: none; margin-left: 10px;"></span>
            </div>
        </div>

        <!-- Time Slots Configuration & Capacity Rules Combined -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h5 class="settings-card-title"><i class="fas fa-clock"></i> Time Slots & Capacity Rules</h5>
            </div>
            
            <div class="settings-card-body">
                <div class="settings-alert settings-alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Manage your time slots and their capacities for working days.</span>
                </div>

                <!-- Time Slots Table -->
                <div class="settings-table-responsive">
                    <div class="settings-table-header">
                        <h6 class="settings-table-title">
                            <i class="fas fa-list"></i> Time Slots Configuration
                        </h6>
                        <button type="button" id="addTimeSlotBtn" class="settings-btn settings-btn-success">
                            <i class="fas fa-plus"></i> Add Time Slot
                        </button>
                    </div>
                    
                    <table class="settings-table settings-table-bordered">
                        <thead class="settings-table-dark">
                            <tr>
                                <th>Time Slot</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Registration (R)</th>
                                <th>Updating (U)</th>
                                <th>Inquiry (S)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($timeSlots as $slot)
                                @php
                                    $rule = $capacityRules[$slot->id]->firstWhere('day_type', 'working') ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $slot->label }}</strong>
                                    </td>
                                    <td>{{ date('g:i A', strtotime($slot->start_time)) }}</td>
                                    <td>{{ date('g:i A', strtotime($slot->end_time)) }}</td>
                                    <td>
                                        <input type="number" 
                                               name="slot_capacities[{{ $slot->id }}][reg]"
                                               class="settings-capacity-input"
                                               value="{{ $rule->reg_capacity ?? 4 }}"
                                               min="0" max="100" step="1">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="slot_capacities[{{ $slot->id }}][updating]"
                                               class="settings-capacity-input"
                                               value="{{ $rule->updating_capacity ?? 4 }}"
                                               min="0" max="100" step="1">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="slot_capacities[{{ $slot->id }}][inquiry]"
                                               class="settings-capacity-input"
                                               value="{{ $rule->inquiry_capacity ?? 4 }}"
                                               min="0" max="100" step="1">
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

                <div class="settings-alert settings-alert-info settings-mt-3">
                    <i class="fas fa-info-circle"></i>
                    <span><strong>Note:</strong> Working days are Monday to Friday. Each working day has the capacity shown above. Non-working days (Saturday, Sunday, and holidays) have 0 capacity by default.</span>
                </div>

                <div class="settings-action-group">
                    <button type="button" id="saveCapacityRulesBtn" class="settings-btn settings-btn-primary">
                        <i class="fas fa-save"></i> Save Capacity Rules
                    </button>
                    <button type="button" id="resetCapacityRulesBtn" class="settings-btn settings-btn-outline-secondary">
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
                    // Load working days directly from database for display
                    $workingDaysFromDB = [];
                    try {
                        $dbWorkingDays = App\Models\WorkingDaysDefault::where('day_type', 'working')->get();
                        foreach ($dbWorkingDays as $day) {
                            $dayNumber = null;
                            switch($day->day_name) {
                                case 'monday': $dayNumber = 1; break;
                                case 'tuesday': $dayNumber = 2; break;
                                case 'wednesday': $dayNumber = 3; break;
                                case 'thursday': $dayNumber = 4; break;
                                case 'friday': $dayNumber = 5; break;
                                case 'saturday': $dayNumber = 6; break;
                                case 'sunday': $dayNumber = 7; break;
                            }
                            if ($dayNumber) $workingDaysFromDB[] = (string)$dayNumber;
                        }
                    } catch (\Exception $e) {
                        $workingDaysFromDB = ['1', '2', '3', '4', '5']; // Default Monday-Friday
                    }
                    
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

                <div class="settings-days-container" id="workingDaysContainer">
                    @foreach ($dayNames as $value => $name)
                        <div>
                            <input type="checkbox" name="working_days_checkbox[]" value="{{ $value }}"
                                id="day{{ $value }}" class="settings-day-checkbox working-day-checkbox"
                                {{ in_array($value, $workingDaysFromDB) ? 'checked' : '' }}>
                            <label class="settings-day-label" for="day{{ $value }}">
                                <i class="fas fa-calendar-day"></i> {{ $name }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <small class="settings-form-text">Select which days appointments can be booked. Non-working days will not appear in the client calendar.</small>

                <div class="settings-button-group settings-mt-3">
                    <button type="button" id="selectWeekdaysBtn" class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                        <i class="fas fa-briefcase"></i> Weekdays (Mon-Fri)
                    </button>
                    <button type="button" id="selectWeekendsBtn" class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                        <i class="fas fa-umbrella-beach"></i> Weekends (Sat-Sun)
                    </button>
                    <button type="button" id="selectAllDaysBtn" class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                        <i class="fas fa-calendar-week"></i> All Days
                    </button>
                </div>
                
                <!-- Working Days Action Buttons -->
                <div class="settings-action-group">
                    <button type="button" id="saveWorkingDaysBtn" class="settings-btn settings-btn-primary">
                        <i class="fas fa-save"></i> Save Working Days
                    </button>
                    <button type="button" id="resetWorkingDaysBtn" class="settings-btn settings-btn-outline-secondary">
                        <i class="fas fa-undo"></i> Reset to Defaults
                    </button>
                </div>
                <span id="workingDaysStatus" style="display: none; margin-left: 10px;"></span>
            </div>
        </div>
    </div>

    <!-- Add Time Slot Modal -->
    <div id="addTimeSlotModal" class="settings-modal">
        <div class="settings-modal-content">
            <div class="settings-modal-header">
                <h5 class="settings-modal-title">
                    <i class="fas fa-plus-circle"></i> Add New Time Slot
                </h5>
                <button type="button" class="settings-modal-close" id="closeModalBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="settings-modal-body">
                <div class="settings-form-group">
                    <label class="settings-form-label">Slot Label</label>
                    <input type="text" id="modal_new_slot_label" class="settings-form-control" placeholder="e.g., Morning Slot, Afternoon Slot">
                </div>
                <div class="settings-form-group">
                    <label class="settings-form-label">Start Time</label>
                    <input type="time" id="modal_new_start_time" class="settings-form-control">
                </div>
                <div class="settings-form-group">
                    <label class="settings-form-label">End Time</label>
                    <input type="time" id="modal_new_end_time" class="settings-form-control">
                </div>
            </div>
            <div class="settings-modal-footer">
                <button type="button" class="settings-btn settings-btn-outline-secondary" id="cancelModalBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" id="modalAddTimeSlotBtnSubmit" class="settings-btn settings-btn-success">
                    <i class="fas fa-plus"></i> Add Time Slot
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Time Slot Modal -->
    <div id="editTimeSlotModal" class="settings-modal">
        <div class="settings-modal-content">
            <div class="settings-modal-header">
                <h5 class="settings-modal-title">
                    <i class="fas fa-edit"></i> Edit Time Slot
                </h5>
                <button type="button" class="settings-modal-close" id="closeEditModalBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="settings-modal-body">
                <input type="hidden" id="edit_slot_id">
                <div class="settings-form-group">
                    <label class="settings-form-label">Slot Label</label>
                    <input type="text" id="edit_slot_label" class="settings-form-control" placeholder="e.g., Morning Slot, Afternoon Slot">
                </div>
                <div class="settings-form-group">
                    <label class="settings-form-label">Start Time</label>
                    <input type="time" id="edit_start_time" class="settings-form-control">
                </div>
                <div class="settings-form-group">
                    <label class="settings-form-label">End Time</label>
                    <input type="time" id="edit_end_time" class="settings-form-control">
                </div>
            </div>
            <div class="settings-modal-footer">
                <button type="button" class="settings-btn settings-btn-outline-secondary" id="cancelEditModalBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" id="updateTimeSlotBtn" class="settings-btn settings-btn-primary">
                    <i class="fas fa-save"></i> Update Time Slot
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Save Appointment Settings
            $('#saveAppointmentSettingsBtn').click(function() {
                const advanceBookingDays = $('#advance_booking_days').val();
                const cancellationHours = $('#cancellation_hours').val();
                const enableEmail = $('#enable_email').val();
                const enablePerServiceLimits = $('#enable_per_service_limits').val();
                
                const $btn = $(this);
                const originalText = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
                
                $('#appointmentSettingsStatus').show().html('<i class="fas fa-sync-alt fa-spin"></i> Saving appointment settings...').css('color', '#0d6efd');
                
                $.ajax({
                    url: '{{ route("admin.settings.appointment") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        advance_booking_days: advanceBookingDays,
                        cancellation_hours: cancellationHours,
                        enable_email: enableEmail,
                        enable_per_service_limits: enablePerServiceLimits
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#appointmentSettingsStatus').html('<i class="fas fa-check-circle"></i> ' + response.message).css('color', '#198754');
                            setTimeout(() => $('#appointmentSettingsStatus').fadeOut(), 3000);
                            showToast('Success!', 'Appointment settings saved successfully!', 'success');
                        } else {
                            $('#appointmentSettingsStatus').html('<i class="fas fa-exclamation-circle"></i> ' + response.message).css('color', '#dc3545');
                            setTimeout(() => $('#appointmentSettingsStatus').fadeOut(), 3000);
                            showToast('Error', response.message, 'error');
                        }
                        $btn.html(originalText).prop('disabled', false);
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to save appointment settings';
                        if (xhr.responseJSON?.message) errorMsg = xhr.responseJSON.message;
                        $('#appointmentSettingsStatus').html('<i class="fas fa-exclamation-circle"></i> ' + errorMsg).css('color', '#dc3545');
                        setTimeout(() => $('#appointmentSettingsStatus').fadeOut(), 3000);
                        showToast('Error', errorMsg, 'error');
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Reset Appointment Settings to Defaults
            $('#resetAppointmentSettingsBtn').click(function() {
                if (confirm('Reset appointment settings to default values? This cannot be undone.')) {
                    $('#advance_booking_days').val(30);
                    $('#cancellation_hours').val(24);
                    $('#enable_email').val('true');
                    $('#enable_per_service_limits').val('true');
                    
                    $('#appointmentSettingsStatus').show().html('<i class="fas fa-info-circle"></i> Defaults loaded (30 days, 24 hours, email enabled, per-service limits enabled). Click "Save Appointment Settings" to apply.').css('color', '#0d6efd');
                    setTimeout(() => $('#appointmentSettingsStatus').fadeOut(), 4000);
                    showToast('Defaults Loaded', 'Default values have been loaded. Click Save to apply changes.', 'info');
                }
            });

            // Reset Working Days to Default (Monday to Friday = 1,2,3,4,5)
            $('#resetWorkingDaysBtn').click(function() {
                if (confirm('Reset working days to default (Monday to Friday only)? This cannot be undone.')) {
                    $('.working-day-checkbox').each(function() {
                        let val = parseInt($(this).val());
                        $(this).prop('checked', val >= 1 && val <= 5);
                    });
                    
                    const selectedDays = [];
                    $('.working-day-checkbox:checked').each(function() {
                        selectedDays.push($(this).val());
                    });
                    selectedDays.sort((a, b) => a - b);
                    
                    const $btn = $(this);
                    const originalText = $btn.html();
                    $btn.html('<i class="fas fa-spinner fa-spin"></i> Resetting...').prop('disabled', true);
                    
                    $('#workingDaysStatus').show().html('<i class="fas fa-sync-alt fa-spin"></i> Resetting to defaults...').css('color', '#0d6efd');
                    
                    $.ajax({
                        url: '{{ route("admin.settings.working-days") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            working_days: selectedDays
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#workingDaysStatus').html('<i class="fas fa-check-circle"></i> Working days reset to defaults (Monday-Friday)!').css('color', '#198754');
                                setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
                                showToast('Success!', 'Working days reset to defaults (Monday-Friday)', 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                $('#workingDaysStatus').html('<i class="fas fa-exclamation-circle"></i> ' + response.message).css('color', '#dc3545');
                                setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
                                showToast('Error', response.message, 'error');
                                $btn.html(originalText).prop('disabled', false);
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to reset working days';
                            if (xhr.responseJSON?.message) errorMsg = xhr.responseJSON.message;
                            $('#workingDaysStatus').html('<i class="fas fa-exclamation-circle"></i> ' + errorMsg).css('color', '#dc3545');
                            setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
                            showToast('Error', errorMsg, 'error');
                            $btn.html(originalText).prop('disabled', false);
                        }
                    });
                }
            });

            // Save Working Days
            $('#saveWorkingDaysBtn').click(function() {
                const selectedDays = [];
                $('.working-day-checkbox:checked').each(function() {
                    selectedDays.push($(this).val());
                });
                selectedDays.sort((a, b) => a - b);
                
                const $btn = $(this);
                const originalText = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
                
                $('#workingDaysStatus').show().html('<i class="fas fa-sync-alt fa-spin"></i> Saving working days...').css('color', '#0d6efd');
                
                $.ajax({
                    url: '{{ route("admin.settings.working-days") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        working_days: selectedDays
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#workingDaysStatus').html('<i class="fas fa-check-circle"></i> ' + response.message).css('color', '#198754');
                            setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
                            showToast('Success!', 'Working days saved successfully!', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            $('#workingDaysStatus').html('<i class="fas fa-exclamation-circle"></i> ' + response.message).css('color', '#dc3545');
                            setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
                            showToast('Error', response.message, 'error');
                            $btn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to save working days';
                        if (xhr.responseJSON?.message) errorMsg = xhr.responseJSON.message;
                        $('#workingDaysStatus').html('<i class="fas fa-exclamation-circle"></i> ' + errorMsg).css('color', '#dc3545');
                        setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
                        showToast('Error', errorMsg, 'error');
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Day selection buttons
            $('#selectWeekdaysBtn').click(function() {
                $('.working-day-checkbox').each(function() {
                    let val = parseInt($(this).val());
                    $(this).prop('checked', val >= 1 && val <= 5);
                });
                $('#workingDaysStatus').show().html('<i class="fas fa-info-circle"></i> Weekdays selected (Mon-Fri). Click "Save Working Days" to apply.').css('color', '#0d6efd');
                setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
            });

            $('#selectWeekendsBtn').click(function() {
                $('.working-day-checkbox').each(function() {
                    let val = parseInt($(this).val());
                    $(this).prop('checked', val === 6 || val === 7);
                });
                $('#workingDaysStatus').show().html('<i class="fas fa-info-circle"></i> Weekends selected (Sat-Sun). Click "Save Working Days" to apply.').css('color', '#0d6efd');
                setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
            });

            $('#selectAllDaysBtn').click(function() {
                $('.working-day-checkbox').prop('checked', true);
                $('#workingDaysStatus').show().html('<i class="fas fa-info-circle"></i> All days selected. Click "Save Working Days" to apply.').css('color', '#0d6efd');
                setTimeout(() => $('#workingDaysStatus').fadeOut(), 3000);
            });

            // Add Time Slot Modal
            $('#addTimeSlotBtn').click(function() {
                $('#modal_new_start_time').val('');
                $('#modal_new_end_time').val('');
                $('#modal_new_slot_label').val('');
                $('#addTimeSlotModal').fadeIn(300);
            });

            function closeModal() {
                $('#addTimeSlotModal').fadeOut(300);
            }

            $('#closeModalBtn, #cancelModalBtn').click(function() {
                closeModal();
            });

            $(window).click(function(e) {
                if ($(e.target).is('#addTimeSlotModal')) {
                    closeModal();
                }
            });

            $('#modalAddTimeSlotBtnSubmit').click(function() {
                const startTime = $('#modal_new_start_time').val();
                const endTime = $('#modal_new_end_time').val();
                const slotLabel = $('#modal_new_slot_label').val();

                if (!startTime || !endTime) {
                    showToast('Missing Information', 'Please enter start time and end time', 'warning');
                    return;
                }

                const $btn = $(this);
                const originalText = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Adding...').prop('disabled', true);

                $.ajax({
                    url: '/admin/time-slots/store',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        start_time: startTime,
                        end_time: endTime,
                        slot_label: slotLabel,
                        capacity_per_slot: 4
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('Success!', 'Time slot added successfully', 'success');
                            closeModal();
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('Error', response.message || 'Error adding time slot', 'error');
                            $btn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to add time slot';
                        if (xhr.responseJSON?.message) errorMsg = xhr.responseJSON.message;
                        showToast('Error', errorMsg, 'error');
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            $(document).keydown(function(e) {
                if (e.key === "Escape" && $('#addTimeSlotModal').is(':visible')) {
                    closeModal();
                }
            });

            // Edit Time Slot
            $('.edit-time-slot').click(function() {
                const id = $(this).data('id');
                let startTime = $(this).data('start_time');
                let endTime = $(this).data('end_time');
                const slotLabel = $(this).data('slot_label');
                
                if (startTime && startTime.length > 5) startTime = startTime.substring(0, 5);
                if (endTime && endTime.length > 5) endTime = endTime.substring(0, 5);
                
                $('#edit_slot_id').val(id);
                $('#edit_slot_label').val(slotLabel);
                $('#edit_start_time').val(startTime);
                $('#edit_end_time').val(endTime);
                $('#editTimeSlotModal').fadeIn(300);
            });

            function closeEditModal() {
                $('#editTimeSlotModal').fadeOut(300);
            }

            $('#closeEditModalBtn, #cancelEditModalBtn').click(function() {
                closeEditModal();
            });

            $(window).click(function(e) {
                if ($(e.target).is('#editTimeSlotModal')) {
                    closeEditModal();
                }
            });

            $('#updateTimeSlotBtn').click(function() {
                const id = $('#edit_slot_id').val();
                const startTime = $('#edit_start_time').val();
                const endTime = $('#edit_end_time').val();
                const slotLabel = $('#edit_slot_label').val();

                if (!startTime || !endTime) {
                    showToast('Missing Information', 'Please enter start time and end time', 'warning');
                    return;
                }

                const $btn = $(this);
                const originalText = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);

                $.ajax({
                    url: '/admin/time-slots/' + id,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        start_time: startTime,
                        end_time: endTime,
                        slot_label: slotLabel
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('Success!', 'Time slot updated successfully', 'success');
                            closeEditModal();
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('Error', response.message || 'Error updating time slot', 'error');
                            $btn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to update time slot';
                        if (xhr.responseJSON?.message) errorMsg = xhr.responseJSON.message;
                        showToast('Error', errorMsg, 'error');
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Delete Time Slot
            $('.delete-time-slot').click(function() {
                const id = $(this).data('id');
                if (confirm('Are you sure you want to delete this time slot? This may affect existing appointments.')) {
                    $.ajax({
                        url: '/admin/time-slots/' + id,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                showToast('Deleted!', 'Time slot has been deleted', 'success');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showToast('Error', response.message || 'Error deleting time slot', 'error');
                            }
                        },
                        error: function(xhr) {
                            showToast('Error', xhr.responseJSON?.message || 'Failed to delete time slot', 'error');
                        }
                    });
                }
            });

            // Save Capacity Rules
            $('#saveCapacityRulesBtn').click(function() {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                $('input[name^="slot_capacities"]').each(function() {
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
                            showToast('Success!', 'Capacity rules saved successfully!', 'success');
                        } else {
                            showToast('Error', response.message || 'Error saving capacity rules', 'error');
                        }
                    },
                    error: function(xhr) {
                        showToast('Error', xhr.responseJSON?.message || 'Failed to save capacity rules', 'error');
                    }
                });
            });

            // Reset Capacity Rules
            $('#resetCapacityRulesBtn').click(function() {
                if (confirm('Reset all capacity rules to default values? This cannot be undone.')) {
                    $('input[name^="slot_capacities"]').each(function() {
                        $(this).val(4);
                    });
                    showToast('Reset Complete', 'Values reset to defaults (4 each). Click "Save Capacity Rules" to apply.', 'success');
                }
            });

            // Clear cache
            $('#refreshCacheBtn').click(function() {
                if (confirm('Clear system cache? This may temporarily slow down the system.')) {
                    $.ajax({
                        url: '{{ route('admin.settings.clear-cache') }}',
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
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
        });

        function showToast(title, message, type) {
            alert(title + ': ' + message);
        }
    </script>
    @endpush
@endsection