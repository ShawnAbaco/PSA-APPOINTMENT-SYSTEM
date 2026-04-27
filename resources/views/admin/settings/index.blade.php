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
                        <span><strong>Note:</strong> Working days are Tuesday to Friday. Each working day has the capacity shown above. Non-working days (Monday, Saturday, Sunday, and holidays) have 0 capacity by default.</span>
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

                    <small class="settings-form-text">Select which days appointments can be booked. Non-working days will not appear in the client calendar.</small>

                    <div class="settings-button-group settings-mt-3">
                        <button type="button" id="selectWeekdays" class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                            <i class="fas fa-briefcase"></i> Weekdays (Mon-Fri)
                        </button>
                        <button type="button" id="selectWeekends" class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                            <i class="fas fa-umbrella-beach"></i> Weekends (Sat-Sun)
                        </button>
                        <button type="button" id="selectAllDays" class="settings-btn settings-btn-outline-secondary settings-btn-sm">
                            <i class="fas fa-calendar-week"></i> All Days
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="settings-action-buttons">
                <button type="submit" class="settings-btn settings-btn-primary settings-btn-lg">
                    <i class="fas fa-save"></i> Save All Settings
                </button>
                
            </div>
        </form>
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

            // Open Modal when clicking Add Time Slot button
            $('#addTimeSlotBtn').click(function() {
                $('#modal_new_start_time').val('');
                $('#modal_new_end_time').val('');
                $('#modal_new_slot_label').val('');
                $('#addTimeSlotModal').fadeIn(300);
            });

            // Close Modal functions
            function closeModal() {
                $('#addTimeSlotModal').fadeOut(300);
            }

            $('#closeModalBtn, #cancelModalBtn').click(function() {
                closeModal();
            });

            // Close modal when clicking outside
            $(window).click(function(e) {
                if ($(e.target).is('#addTimeSlotModal')) {
                    closeModal();
                }
            });

            // Add Time Slot from Modal
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
                        if (xhr.responseJSON?.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showToast('Error', errorMsg, 'error');
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Close modal with Escape key
            $(document).keydown(function(e) {
                if (e.key === "Escape" && $('#addTimeSlotModal').is(':visible')) {
                    closeModal();
                }
            });

           // Edit Time Slot - Open Modal
// Edit Time Slot - Open Modal
$('.edit-time-slot').click(function() {
    const id = $(this).data('id');
    let startTime = $(this).data('start_time');
    let endTime = $(this).data('end_time');
    const slotLabel = $(this).data('slot_label');
    
    // Convert time from H:i:s to H:i format for the time input
    if (startTime && startTime.length > 5) {
        startTime = startTime.substring(0, 5);
    }
    if (endTime && endTime.length > 5) {
        endTime = endTime.substring(0, 5);
    }
    
    $('#edit_slot_id').val(id);
    $('#edit_slot_label').val(slotLabel);
    $('#edit_start_time').val(startTime);
    $('#edit_end_time').val(endTime);
    
    $('#editTimeSlotModal').fadeIn(300);
});

// Close Edit Modal
function closeEditModal() {
    $('#editTimeSlotModal').fadeOut(300);
}

$('#closeEditModalBtn, #cancelEditModalBtn').click(function() {
    closeEditModal();
});

// Close edit modal when clicking outside
$(window).click(function(e) {
    if ($(e.target).is('#editTimeSlotModal')) {
        closeEditModal();
    }
});

// Update Time Slot
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
            if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showToast('Error', errorMsg, 'error');
            $btn.html(originalText).prop('disabled', false);
        }
    });
});

// Close edit modal with Escape key


            // Delete Time Slot
            $('.delete-time-slot').click(function() {
                const id = $(this).data('id');
                if (confirm('Are you sure you want to delete this time slot? This may affect existing appointments.')) {
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