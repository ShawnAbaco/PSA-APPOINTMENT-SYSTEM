@extends('layouts.admin')

@section('content')
    <meta name="slots-working-days" content="{{ $workingDays ?? '1,2,3,4,5' }}">
    <meta name="slots-csrf-token" content="{{ csrf_token() }}">

    <div class="slots-container">
        <!-- Header Section -->
        <div class="slots-welcome-section">
            <div>
                <h1 class="slots-title">Slot Management</h1>
                <p class="slots-subtitle">Manage daily appointment capacity, time slots, and special dates</p>
            </div>
            <div class="slots-date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="slots-action-buttons">
            <button type="button" class="slots-btn slots-btn-outline-primary" id="slotsCapacityRulesBtn">
                <i class="fas fa-sliders-h"></i> Default Capacity Rules
            </button>
            <button type="button" class="slots-btn slots-btn-outline-success" id="slotsBulkGenerateBtn">
                <i class="fas fa-calendar-plus"></i> Bulk Generate
            </button>
            <button type="button" class="slots-btn slots-btn-primary" id="slotsAddSingleSlotBtn">
                <i class="fas fa-plus-circle"></i> Add Single Slot
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="slots-stats-grid">
            <div class="slots-stat-card">
                <div class="slots-stat-card-content">
                    <div class="slots-stat-info">
                        <h6 class="slots-stat-label">Total Slots</h6>
                        <h2 class="slots-stat-value">{{ $totalSlots }}</h2>
                        <p class="slots-stat-trend"><i class="fas fa-calendar-week"></i> All time slots</p>
                    </div>
                    <div class="slots-stat-icon-circle"><i class="fas fa-calendar-week"></i></div>
                </div>
            </div>

            <div class="slots-stat-card">
                <div class="slots-stat-card-content">
                    <div class="slots-stat-info">
                        <h6 class="slots-stat-label">Non-Working Days</h6>
                        <h2 class="slots-stat-value slots-text-warning">{{ $totalNonWorking ?? 0 }}</h2>
                        <p class="slots-stat-trend"><i class="fas fa-sun"></i> Non-working schedules</p>
                    </div>
                    <div class="slots-stat-icon-circle warning-bg"><i class="fas fa-sun"></i></div>
                </div>
            </div>

            <div class="slots-stat-card">
                <div class="slots-stat-card-content">
                    <div class="slots-stat-info">
                        <h6 class="slots-stat-label">Holidays</h6>
                        <h2 class="slots-stat-value slots-text-danger">{{ $totalHolidays }}</h2>
                        <p class="slots-stat-trend"><i class="fas fa-calendar-times"></i> Non-working days</p>
                    </div>
                    <div class="slots-stat-icon-circle danger-bg"><i class="fas fa-calendar-times"></i></div>
                </div>
            </div>

            <div class="slots-stat-card">
                <div class="slots-stat-card-content">
                    <div class="slots-stat-info">
                        <h6 class="slots-stat-label">Total Booked</h6>
                        <h2 class="slots-stat-value slots-text-success">{{ $totalBooked }}</h2>
                        <p class="slots-stat-trend"><i class="fas fa-users"></i> Appointments booked</p>
                    </div>
                    <div class="slots-stat-icon-circle success-bg"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>

        <!-- Calendar Card -->
        <div class="slots-card">
            <div class="slots-card-header">
                <h5 class="slots-card-title"><i class="fas fa-calendar-alt"></i> Slot Calendar</h5>
                <div class="slots-calendar-controls">
                    <button id="slotsPrevMonth" class="slots-calendar-nav-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h4 id="slotsCalendarMonthYear" class="slots-calendar-month-title"></h4>
                    <button id="slotsNextMonth" class="slots-calendar-nav-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button id="slotsTodayBtn" class="slots-btn slots-btn-sm slots-btn-outline-primary">
                        <i class="fas fa-calendar-day"></i> Today
                    </button>
                </div>
            </div>
            <div class="slots-card-body">
                <div class="slots-calendar-wrapper">
                    <div class="slots-calendar-weekdays">
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                        <div>Sun</div>
                    </div>
                    <div class="slots-calendar-days" id="slotsCalendarDays">
                        <div class="slots-loading-state">
                            <i class="fas fa-spinner fa-pulse"></i>
                            <p>Loading calendar...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Slot Modal -->
    <div class="slots-modal" id="slotsCreateSlotModal">
        <div class="slots-modal-dialog slots-modal-lg">
            <div class="slots-modal-content">
                <div class="slots-modal-header slots-modal-header-primary">
                    <h5 class="slots-modal-title"><i class="fas fa-plus-circle"></i> Create Appointment Slots</h5>
                    <span class="slots-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body">
                    <form id="slotsCreateForm">
                        @csrf
                        <div class="slots-alert slots-alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Working Days:</strong> <span id="workingDaysDisplay"></span><br>
                            <small>Only these days are available for booking.</small>
                        </div>

                        <div class="slots-form-row">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Date *</label>
                                <input type="date" name="date" id="create_date" class="slots-form-control" required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Day Type *</label>
                                <select name="day_type" id="create_day_type" class="slots-form-control" required>
                                    <option value="working">Working Day</option>
                                    <option value="non_working">Non-Working Day</option>
                                </select>
                            </div>
                        </div>

                        <div class="slots-form-group" id="timeSlotsSelectorGroup">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <label class="slots-form-label" style="margin-bottom: 0;">Time Slots *</label>
                                <button type="button" class="slots-btn slots-btn-sm slots-btn-outline-primary" id="selectAllTimeSlots" style="font-size: 12px; padding: 4px 8px;">Select All</button>
                            </div>
                            <div class="slots-checkbox-group" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; padding: 10px; background: #f8f9fa; border-radius: 6px;">
                                @foreach ($timeSlots as $timeSlot)
                                    <label class="slots-checkbox">
                                        <input type="checkbox" name="time_slot_ids" value="{{ $timeSlot->id }}" class="time-slot-checkbox">
                                        <span>{{ $timeSlot->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="slots-form-row" id="capacityFields">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Registration (R) Capacity *</label>
                                <input type="number" name="reg_capacity" id="reg_capacity" class="slots-form-control" value="4" min="0" max="100" required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Updating (U) Capacity *</label>
                                <input type="number" name="updating_capacity" id="updating_capacity" class="slots-form-control" value="4" min="0" max="100" required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Inquiry (S) Capacity *</label>
                                <input type="number" name="inquiry_capacity" id="inquiry_capacity" class="slots-form-control" value="4" min="0" max="100" required>
                            </div>
                        </div>

                        <div class="slots-form-group" id="notesGroup" style="display: none;">
                            <label class="slots-form-label">Notes <span style="font-weight: normal; color: #6c757d;">(optional)</span></label>
                            <textarea name="notes" class="slots-form-control" rows="2" placeholder="e.g. holiday, special event, etc."></textarea>
                        </div>

                        <div class="slots-modal-footer">
                            <button type="button" class="slots-btn slots-btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="slots-btn slots-btn-primary">Create Slots</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Capacity Rules Modal -->
    <div class="slots-modal" id="slotsCapacityRulesModal">
        <div class="slots-modal-dialog slots-modal-xl">
            <div class="slots-modal-content">
                <div class="slots-modal-header">
                    <h5 class="slots-modal-title"><i class="fas fa-sliders-h"></i> Default Capacity Rules</h5>
                    <span class="slots-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body">
                    <div class="slots-alert slots-alert-info">
                        <i class="fas fa-info-circle"></i>
                        Default capacities for working days (Monday-Friday). These values are used when no manual override exists.
                    </div>
                    <form id="slotsCapacityRulesForm">
                        @csrf
                        <div class="slots-table-responsive">
                            <table class="slots-table">
                                <thead>
                                    <tr>
                                        <th>Time Slot</th>
                                        <th>Registration (R)</th>
                                        <th>Updating (U)</th>
                                        <th>Inquiry (S)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($timeSlots as $timeSlot)
                                        @php
                                            $rule = $capacityRules[$timeSlot->id]->firstWhere('day_type', 'working') ?? null;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $timeSlot->label }}</strong><br>
                                                <small>{{ date('g:i A', strtotime($timeSlot->start_time)) }} - {{ date('g:i A', strtotime($timeSlot->end_time)) }}</small>
                                            </td>
                                            <td>
                                                <input type="number" name="capacities[{{ $timeSlot->id }}][reg]"
                                                    class="slots-form-control slots-form-control-sm"
                                                    value="{{ $rule->reg_capacity ?? 4 }}" min="0" max="100">
                                            </td>
                                            <td>
                                                <input type="number" name="capacities[{{ $timeSlot->id }}][updating]"
                                                    class="slots-form-control slots-form-control-sm"
                                                    value="{{ $rule->updating_capacity ?? 4 }}" min="0" max="100">
                                            </td>
                                            <td>
                                                <input type="number" name="capacities[{{ $timeSlot->id }}][inquiry]"
                                                    class="slots-form-control slots-form-control-sm"
                                                    value="{{ $rule->inquiry_capacity ?? 4 }}" min="0" max="100">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="slots-modal-footer">
                            <button type="button" class="slots-btn slots-btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="slots-btn slots-btn-primary">Save Rules</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Generate Modal -->
    <div class="slots-modal" id="slotsBulkGenerateModal">
        <div class="slots-modal-dialog slots-modal-lg">
            <div class="slots-modal-content">
                <div class="slots-modal-header slots-modal-header-success">
                    <h5 class="slots-modal-title"><i class="fas fa-calendar-plus"></i> Bulk Generate Slots</h5>
                    <span class="slots-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body">
                    <form id="slotsBulkGenerateForm">
                        @csrf
                        <div class="slots-form-row">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Start Date *</label>
                                <input type="date" name="start_date" id="bulk_start_date" class="slots-form-control" required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">End Date *</label>
                                <input type="date" name="end_date" id="bulk_end_date" class="slots-form-control" required>
                            </div>
                        </div>

                        <div class="slots-form-group" id="bulkTimeSlotsSelectorGroup">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <label class="slots-form-label" style="margin-bottom: 0;">Time Slots *</label>
                                <button type="button" class="slots-btn slots-btn-sm slots-btn-outline-primary" id="bulkSelectAllTimeSlots" style="font-size: 12px; padding: 4px 8px;">Select All</button>
                            </div>
                            <div class="slots-checkbox-group" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; padding: 10px; background: #f8f9fa; border-radius: 6px;">
                                @foreach ($timeSlots as $timeSlot)
                                    <label class="slots-checkbox">
                                        <input type="checkbox" name="time_slot_ids[]" value="{{ $timeSlot->id }}" class="bulk-time-slot-checkbox">
                                        <span>{{ $timeSlot->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="slots-form-row">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Registration (R) Capacity</label>
                                <input type="number" name="reg_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Updating (U) Capacity</label>
                                <input type="number" name="updating_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Inquiry (S) Capacity</label>
                                <input type="number" name="inquiry_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                        </div>

                        <div class="slots-form-group">
                            <label class="slots-form-label">Days to Include</label>
                            <div class="slots-checkbox-group">
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="1" checked> Monday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="2" checked> Tuesday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="3" checked> Wednesday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="4" checked> Thursday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="5" checked> Friday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="6"> Saturday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="7"> Sunday</label>
                            </div>
                        </div>

                        <div class="slots-modal-footer">
                            <button type="button" class="slots-btn slots-btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="slots-btn slots-btn-success">Generate Slots</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Slot Detail Modal -->
    <div class="slots-modal" id="slotsSlotDetailModal">
        <div class="slots-modal-dialog slots-modal-lg">
            <div class="slots-modal-content">
                <div class="slots-modal-header">
                    <h5 class="slots-modal-title"><i class="fas fa-info-circle"></i> Slot Details</h5>
                    <span class="slots-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body" id="slotsSlotDetailBody">
                    <div class="slots-loading-state">
                        <i class="fas fa-spinner fa-pulse"></i>
                        <p>Loading slot details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Slot Modal -->
    <div class="edit-slot-modal" id="editSlotModal">
        <div class="edit-slot-modal-dialog edit-slot-modal-lg">
            <div class="edit-slot-modal-content">
                <div class="edit-slot-modal-header edit-slot-modal-header-primary">
                    <h5 class="edit-slot-modal-title"><i class="fas fa-edit"></i> Edit Appointment Slot</h5>
                    <span class="edit-slot-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="edit-slot-modal-body">
                    <form id="editSlotForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="slot_id" id="edit_slot_id">
                        
                        <div class="edit-slot-alert edit-slot-alert-info" id="editSlotInfo">
                            <i class="fas fa-info-circle"></i>
                            <strong>Date:</strong> <span id="edit_slot_date"></span><br>
                            <strong>Currently Booked:</strong> <span id="edit_booked_count">0</span> clients<br>
                            <small>Note: Capacity cannot be reduced below currently booked numbers.</small>
                        </div>

                        <div class="edit-slot-form-row">
                            <div class="edit-slot-form-group">
                                <label class="edit-slot-form-label">Time Slot *</label>
                                <select name="time_slot_id" id="edit_time_slot_id" class="edit-slot-form-control" required>
                                    <option value="">Select Time Slot</option>
                                    @foreach ($timeSlots as $timeSlot)
                                        <option value="{{ $timeSlot->id }}">{{ $timeSlot->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="edit-slot-form-group">
                                <label class="edit-slot-form-label">Day Type *</label>
                                <select name="day_type" id="edit_day_type" class="edit-slot-form-control" required>
                                    <option value="working">Working Day</option>
                                    <option value="non_working">Non-Working Day</option>
                                    <option value="holiday">Holiday (No Appointments)</option>
                                </select>
                            </div>
                        </div>

                        <div class="edit-slot-form-row" id="editCapacityFields">
                            <div class="edit-slot-form-group">
                                <label class="edit-slot-form-label">Registration (R) Capacity *</label>
                                <input type="number" name="reg_capacity" id="edit_reg_capacity" class="edit-slot-form-control" min="0" max="100" required>
                                <small class="edit-slot-form-help">Available: <span id="edit_reg_available">0</span> | Min: <span id="edit_reg_min">0</span></small>
                            </div>
                            <div class="edit-slot-form-group">
                                <label class="edit-slot-form-label">Updating (U) Capacity *</label>
                                <input type="number" name="updating_capacity" id="edit_updating_capacity" class="edit-slot-form-control" min="0" max="100" required>
                                <small class="edit-slot-form-help">Available: <span id="edit_updating_available">0</span> | Min: <span id="edit_updating_min">0</span></small>
                            </div>
                            <div class="edit-slot-form-group">
                                <label class="edit-slot-form-label">Inquiry (S) Capacity *</label>
                                <input type="number" name="inquiry_capacity" id="edit_inquiry_capacity" class="edit-slot-form-control" min="0" max="100" required>
                                <small class="edit-slot-form-help">Available: <span id="edit_inquiry_available">0</span> | Min: <span id="edit_inquiry_min">0</span></small>
                            </div>
                        </div>

                        <div class="edit-slot-form-group">
                            <label class="edit-slot-form-label">Notes</label>
                            <textarea name="notes" id="edit_notes" class="edit-slot-form-control" rows="3" placeholder="Optional notes about this slot"></textarea>
                        </div>

                        <div class="edit-slot-modal-footer">
                            <button type="button" class="edit-slot-btn edit-slot-btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="edit-slot-btn edit-slot-btn-primary">Update Slot</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Original Slots Modal CSS */
        .slots-title { font-size: 28px; font-weight: 600; margin: 0 0 5px 0; }
        .slots-subtitle { font-size: 14px; opacity: 0.9; margin: 0; }
        .slots-date-display { background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 10px; font-size: 16px; }
        .slots-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .slots-stat-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 20px; }
        .slots-stat-card-content { display: flex; justify-content: space-between; align-items: center; }
        .slots-stat-label { font-size: 13px; color: #6c757d; margin: 0 0 5px 0; }
        .slots-stat-value { font-size: 32px; font-weight: 700; margin: 0; }
        .slots-stat-trend { font-size: 12px; color: #6c757d; margin: 5px 0 0 0; }
        .slots-stat-icon-circle { width: 50px; height: 50px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #667eea; }
        .slots-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
        .slots-card-header { padding: 20px 25px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .slots-card-title { font-size: 18px; font-weight: 600; margin: 0; }
        .slots-card-body { padding: 20px 25px; }
        .slots-action-buttons { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .slots-btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; border: none; transition: all 0.3s; }
        .slots-btn-primary { background: #4361ee; color: white; }
        .slots-btn-primary:hover { background: #3a56d4; }
        .slots-btn-primary:disabled { background: #a6aef8; cursor: not-allowed; opacity: 0.6; }
        .slots-btn-outline-primary { background: transparent; border: 1px solid #4361ee; color: #4361ee; }
        .slots-btn-outline-primary:hover { background: #4361ee; color: white; }
        .slots-btn-outline-primary:disabled { border-color: #a6aef8; color: #a6aef8; cursor: not-allowed; opacity: 0.6; }
        .slots-btn-outline-success { background: transparent; border: 1px solid #28a745; color: #28a745; }
        .slots-btn-outline-success:hover { background: #28a745; color: white; }
        .slots-btn-outline-success:disabled { border-color: #a6d6a6; color: #a6d6a6; cursor: not-allowed; opacity: 0.6; }
        .slots-btn-outline-secondary { background: transparent; border: 1px solid #6c757d; color: #6c757d; }
        .slots-btn-outline-secondary:hover { background: #6c757d; color: white; }
        .slots-btn-outline-secondary:disabled { border-color: #c3c5c7; color: #c3c5c7; cursor: not-allowed; opacity: 0.6; }
        .slots-btn-secondary { background: #6c757d; color: white; }
        .slots-btn-secondary:hover { background: #5a6268; }
        .slots-btn-secondary:disabled { background: #a6aeb3; cursor: not-allowed; opacity: 0.6; }
        .slots-btn-success { background: #28a745; color: white; }
        .slots-btn-success:hover { background: #218838; }
        .slots-btn-success:disabled { background: #9dd19e; cursor: not-allowed; opacity: 0.6; }
        .slots-btn-sm { padding: 5px 12px; font-size: 12px; }
        .slots-calendar-wrapper { overflow-x: auto; }
        .slots-calendar-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; }
        .slots-calendar-weekdays div { padding: 12px; text-align: center; font-weight: 600; color: #495057; }
        .slots-calendar-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
        .slots-calendar-day { background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; min-height: 100px; cursor: pointer; transition: all 0.2s; }
        .slots-calendar-day:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .slots-calendar-day.slots-past { background: #f8f9fa; opacity: 0.7; }
        .slots-calendar-day.slots-today { border: 2px solid #4361ee; background: #f0f4ff; }
        .slots-calendar-day.slots-non-working { background: #f8f9fa; border-color: #dee2e6; }
        .slots-calendar-day.slots-holiday { background: #fee2e2; border-color: #fecaca; }
        .slots-day-number { font-weight: 600; font-size: 14px; margin-bottom: 8px; }
        .slots-service-badges { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; }
        .slots-service-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .slots-service-badge.slots-available { background: #d4edda; color: #155724; }
        .slots-service-badge.slots-limited { background: #fff3cd; color: #856404; }
        .slots-service-badge.slots-full { background: #f8d7da; color: #721c24; }
        .slots-badge-icon { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; margin-top: 5px; }
        .slots-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .slots-modal.show { display: flex; }
        .slots-modal-dialog { max-width: 600px; width: 90%; }
        .slots-modal-dialog.slots-modal-lg { max-width: 900px; }
        .slots-modal-dialog.slots-modal-xl { max-width: 1100px; }
        .slots-modal-content { background: white; border-radius: 12px; overflow: hidden; }
        .slots-modal-header { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: white; padding: 15px 20px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .slots-modal-header-primary { background: linear-gradient(135deg, #4361ee 0%, #667eea 100%); color: white; }
        .slots-modal-header-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
        .slots-modal-title { margin: 0; font-size: 18px; }
        .slots-modal-close { font-size: 24px; cursor: pointer; opacity: 0.7; }
        .slots-modal-close:hover { opacity: 1; }
        .slots-modal-body { padding: 20px; max-height: 70vh; overflow-y: auto; }
        .slots-modal-footer { padding: 15px 20px; border-top: 1px solid #e9ecef; display: flex; justify-content: flex-end; gap: 10px; }
        .slots-form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px; }
        .slots-form-group { margin-bottom: 15px; }
        .slots-form-label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        .slots-form-control { width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        .slots-form-control:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
        .slots-table { width: 100%; border-collapse: collapse; }
        .slots-table th, .slots-table td { padding: 12px; border: 1px solid #e9ecef; text-align: left; }
        .slots-table th { background: #f8f9fa; font-weight: 600; }
        .slots-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .slots-alert-info { background: #e7f3ff; color: #004085; border: 1px solid #b8daff; }
        .slots-checkbox-group { display: flex; flex-wrap: wrap; gap: 15px; }
        .slots-checkbox { display: flex; align-items: center; gap: 5px; cursor: pointer; }
        .slots-checkbox input[type="checkbox"] { cursor: pointer; width: 18px; height: 18px; accent-color: #4361ee; }
        .slots-checkbox span { user-select: none; }
        .slots-day-type-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .slots-day-type-badge.working { background: #d4edda; color: #155724; }
        .slots-day-type-badge.non_working { background: #fff3cd; color: #856404; }
        .slots-day-type-badge.holiday { background: #f8d7da; color: #721c24; }
        .slots-loading-state { text-align: center; padding: 40px; color: #6c757d; }
        .slots-text-warning { color: #ffc107; }
        .slots-text-danger { color: #dc3545; }
        .slots-text-success { color: #28a745; }
        .warning-bg { background: #fff3cd; color: #856404; }
        .danger-bg { background: #f8d7da; color: #dc3545; }
        .success-bg { background: #d4edda; color: #28a745; }

        /* Edit Modal CSS */
        .edit-slot-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1001;
            align-items: center;
            justify-content: center;
        }
        .edit-slot-modal.show { display: flex; }
        .edit-slot-modal-dialog { max-width: 600px; width: 90%; animation: editSlotSlideIn 0.3s ease; }
        .edit-slot-modal-lg { max-width: 800px; }
        @keyframes editSlotSlideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .edit-slot-modal-content { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2); }
        .edit-slot-modal-header { padding: 20px 24px; background: linear-gradient(135deg, #4361ee 0%, #667eea 100%); color: white; display: flex; justify-content: space-between; align-items: center; border-bottom: none; }
        .edit-slot-modal-header-primary { background: linear-gradient(135deg, #4361ee 0%, #667eea 100%); }
        .edit-slot-modal-title { margin: 0; font-size: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .edit-slot-modal-close { font-size: 28px; cursor: pointer; opacity: 0.8; transition: all 0.3s; line-height: 1; }
        .edit-slot-modal-close:hover { opacity: 1; transform: rotate(90deg); }
        .edit-slot-modal-body { padding: 24px; max-height: 70vh; overflow-y: auto; }
        .edit-slot-modal-footer { padding: 16px 24px; border-top: 1px solid #e9ecef; display: flex; justify-content: flex-end; gap: 12px; background: #f8f9fa; }
        .edit-slot-form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
        .edit-slot-form-group { margin-bottom: 20px; }
        .edit-slot-form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: #495057; text-transform: uppercase; letter-spacing: 0.5px; }
        .edit-slot-form-control { width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
        .edit-slot-form-control:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
        .edit-slot-form-control:disabled { background: #f1f5f9; cursor: not-allowed; color: #64748b; }
        .edit-slot-form-help { font-size: 12px; color: #6c757d; margin-top: 6px; display: block; }
        .edit-slot-alert { padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
        .edit-slot-alert-info { background: #e7f3ff; color: #004085; border: 1px solid #b8daff; }
        .edit-slot-alert-info i { font-size: 20px; }
        .edit-slot-btn { padding: 10px 24px; border-radius: 50px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .edit-slot-btn-primary { background: linear-gradient(135deg, #4361ee 0%, #667eea 100%); color: white; }
        .edit-slot-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3); }
        .edit-slot-btn-secondary { background: #6c757d; color: white; }
        .edit-slot-btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        @media (max-width: 768px) {
            .edit-slot-form-row { grid-template-columns: 1fr; gap: 15px; }
            .edit-slot-modal-footer { flex-direction: column; }
            .edit-slot-modal-footer .edit-slot-btn { width: 100%; justify-content: center; }
        }
    </style>

    <script>
    let slotsCurrentDate = new Date();
    let slotsData = {};
    let defaultRules = {};
    let slotsIsLoading = false;

    // Display working days
    const workingDaysMeta = document.querySelector('meta[name="slots-working-days"]');
    const workingDays = workingDaysMeta ? workingDaysMeta.getAttribute('content').split(',').map(Number) : [1,2,3,4,5];
    const dayNames = {1:'Monday',2:'Tuesday',3:'Wednesday',4:'Thursday',5:'Friday',6:'Saturday',7:'Sunday'};
    const workingDaysText = workingDays.map(d => dayNames[d]).join(', ');
    if(document.getElementById('workingDaysDisplay')) {
        document.getElementById('workingDaysDisplay').textContent = workingDaysText;
    }

    function slotsFormatDate(date) {
        let d = new Date(date);
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let day = String(d.getDate()).padStart(2, '0');
        return `${d.getFullYear()}-${month}-${day}`;
    }

    function formatDateDisplay(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' });
    }

    function slotsLoadSlots() {
        if (slotsIsLoading) return;
        
        slotsIsLoading = true;
        const year = slotsCurrentDate.getFullYear();
        const month = slotsCurrentDate.getMonth() + 1;
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const monthNameElem = document.getElementById('slotsCalendarMonthYear');
        if(monthNameElem) monthNameElem.textContent = `${monthNames[slotsCurrentDate.getMonth()]} ${year}`;

        fetch(`/admin/slots/json?month=${month}&year=${year}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                slotsData = data.slots || {};
                defaultRules = data.default_rules || {};
                slotsRenderCalendar();
            })
            .catch(error => {
                console.error('Error loading slots:', error);
                const calendarDays = document.getElementById('slotsCalendarDays');
                if(calendarDays) {
                    calendarDays.innerHTML = '<div class="slots-loading-state"><i class="fas fa-exclamation-triangle"></i><p>Failed to load slots. Please refresh the page.</p></div>';
                }
            })
            .finally(() => {
                slotsIsLoading = false;
            });
    }

    function slotsRenderCalendar() {
        const year = slotsCurrentDate.getFullYear();
        const month = slotsCurrentDate.getMonth();
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const monthNameElem = document.getElementById('slotsCalendarMonthYear');
        if(monthNameElem) monthNameElem.textContent = `${monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1);
        const startOffset = (firstDay.getDay() || 7) - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        let html = '';

        for (let i = 0; i < startOffset; i++) {
            html += '<div class="slots-calendar-day slots-empty"></div>';
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const date = new Date(year, month, d);
            const dateKey = slotsFormatDate(date);
            const slot = slotsData[dateKey];
            const isPast = date < today;
            const isToday = slotsFormatDate(date) === slotsFormatDate(new Date());

            let dayOfWeek = date.getDay();
            let dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
            const isWorkingDay = workingDays.includes(dayNumber);

            let dayClass = 'slots-calendar-day';
            if (isPast) dayClass += ' slots-past';
            if (isToday) dayClass += ' slots-today';
            if (!isWorkingDay && (!slot || Object.keys(slot).length === 0)) dayClass += ' slots-non-working';

            let content = `<div class="slots-day-number">${d}</div>`;

            if (!isWorkingDay && (!slot || Object.keys(slot).length === 0)) {
                content += '<div class="slots-badge-icon" style="background:#6c757d; color:white;">🔒 Non-working</div>';
            } else if (slot && Object.keys(slot).length > 0) {
                const firstSlot = Object.values(slot)[0];
                
                if (firstSlot.day_type === 'holiday') {
                    dayClass += ' slots-holiday';
                    content = `<div class="slots-day-number">${d}</div><div class="slots-badge-icon" style="background:#dc3545; color:white;">🎄 Holiday</div>`;
                } else if (firstSlot.day_type === 'non_working') {
                    dayClass += ' slots-non-working';
                    content = `<div class="slots-day-number">${d}</div><div class="slots-badge-icon" style="background:#ffc107; color:#856404;">⚠️ Non-working</div>`;
                }

                let totalRegAvailable = 0, totalUpdatingAvailable = 0, totalInquiryAvailable = 0;
                let totalRegCapacity = 0, totalUpdatingCapacity = 0, totalInquiryCapacity = 0;

                for (const timeSlotData of Object.values(slot)) {
                    totalRegAvailable += timeSlotData.reg_available || 0;
                    totalUpdatingAvailable += timeSlotData.updating_available || 0;
                    totalInquiryAvailable += timeSlotData.inquiry_available || 0;
                    totalRegCapacity += timeSlotData.reg_capacity || 0;
                    totalUpdatingCapacity += timeSlotData.updating_capacity || 0;
                    totalInquiryCapacity += timeSlotData.inquiry_capacity || 0;
                }

                let badgesHtml = '<div class="slots-service-badges">';
                if (totalRegCapacity > 0) {
                    let percent = totalRegCapacity > 0 ? (totalRegAvailable / totalRegCapacity) * 100 : 0;
                    let statusClass = totalRegAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' : 'slots-available');
                    badgesHtml += `<span class="slots-service-badge ${statusClass}">R${totalRegAvailable}</span>`;
                }
                if (totalUpdatingCapacity > 0) {
                    let percent = totalUpdatingCapacity > 0 ? (totalUpdatingAvailable / totalUpdatingCapacity) * 100 : 0;
                    let statusClass = totalUpdatingAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' : 'slots-available');
                    badgesHtml += `<span class="slots-service-badge ${statusClass}">U${totalUpdatingAvailable}</span>`;
                }
                if (totalInquiryCapacity > 0) {
                    let percent = totalInquiryCapacity > 0 ? (totalInquiryAvailable / totalInquiryCapacity) * 100 : 0;
                    let statusClass = totalInquiryAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' : 'slots-available');
                    badgesHtml += `<span class="slots-service-badge ${statusClass}">S${totalInquiryAvailable}</span>`;
                }
                badgesHtml += '</div>';
                content += badgesHtml;
            } else if (isWorkingDay && !isPast) {
    // Calculate total default capacities across all time slots for this day
    let totalRegCapacity = 0;
    let totalUpdatingCapacity = 0;
    let totalInquiryCapacity = 0;
    
    // Sum up capacities from all time slots using default rules
    for (const timeSlotId in defaultRules) {
        const rule = defaultRules[timeSlotId];
        if (rule) {
            totalRegCapacity += (rule.reg_capacity || 0);
            totalUpdatingCapacity += (rule.updating_capacity || 0);
            totalInquiryCapacity += (rule.inquiry_capacity || 0);
        }
    }
    
    // If no default rules found, use fallback values
    if (totalRegCapacity === 0 && totalUpdatingCapacity === 0 && totalInquiryCapacity === 0) {
        totalRegCapacity = 4;
        totalUpdatingCapacity = 4;
        totalInquiryCapacity = 4;
    }
    
    content += `<div class="slots-badge-icon" style="background:#28a745; color:white;">Default: R${totalRegCapacity} U${totalUpdatingCapacity} S${totalInquiryCapacity}</div>`;
}

            html += `<div class="${dayClass}" onclick="slotsShowSlotDetails('${dateKey}')">${content}</div>`;
        }

        const calendarDays = document.getElementById('slotsCalendarDays');
        if(calendarDays) calendarDays.innerHTML = html;
    }

    async function getDefaultCapacity(timeSlotId) {
        try {
            const response = await fetch(`/admin/slots/default-capacities?time_slot_id=${timeSlotId}`);
            const data = await response.json();
            if (data.success) {
                return {
                    reg: data.reg_capacity,
                    updating: data.updating_capacity,
                    inquiry: data.inquiry_capacity
                };
            }
        } catch (error) {
            console.error('Error fetching default capacities:', error);
        }
        return { reg: 4, updating: 4, inquiry: 4 };
    }

    window.openEditSlotModal = async function(slotId, dateKey) {
        const editModal = document.getElementById('editSlotModal');
        if(!editModal) return;
        
        const editForm = document.getElementById('editSlotForm');
        if(editForm) editForm.reset();
        
        try {
            const response = await fetch(`/admin/slots/details/${dateKey}`);
            const data = await response.json();
            
            if (data.success && data.slots.length > 0) {
                const slot = data.slots.find(s => s.id == slotId);
                if (slot) {
                    const defaultCap = await getDefaultCapacity(slot.time_slot_id);
                    
                    document.getElementById('edit_slot_id').value = slot.id;
                    document.getElementById('edit_time_slot_id').value = slot.time_slot_id;
                    document.getElementById('edit_day_type').value = slot.day_type;
                    document.getElementById('edit_reg_capacity').value = slot.reg_capacity;
                    document.getElementById('edit_updating_capacity').value = slot.updating_capacity;
                    document.getElementById('edit_inquiry_capacity').value = slot.inquiry_capacity;
                    document.getElementById('edit_notes').value = slot.notes || '';
                    document.getElementById('edit_slot_date').innerHTML = formatDateDisplay(dateKey);
                    
                    const totalBooked = (slot.reg_booked || 0) + (slot.updating_booked || 0) + (slot.inquiry_booked || 0);
                    document.getElementById('edit_booked_count').innerHTML = totalBooked;
                    
                    document.getElementById('edit_reg_available').innerHTML = (slot.reg_capacity - (slot.reg_booked || 0));
                    document.getElementById('edit_updating_available').innerHTML = (slot.updating_capacity - (slot.updating_booked || 0));
                    document.getElementById('edit_inquiry_available').innerHTML = (slot.inquiry_capacity - (slot.inquiry_booked || 0));
                    
                    const minReg = slot.reg_booked || 0;
                    const minUpdating = slot.updating_booked || 0;
                    const minInquiry = slot.inquiry_booked || 0;
                    
                    document.getElementById('edit_reg_min').innerHTML = minReg;
                    document.getElementById('edit_updating_min').innerHTML = minUpdating;
                    document.getElementById('edit_inquiry_min').innerHTML = minInquiry;
                    
                    window.minCapacities = {
                        reg: minReg,
                        updating: minUpdating,
                        inquiry: minInquiry
                    };
                    
                    window.currentBooked = {
                        reg: slot.reg_booked || 0,
                        updating: slot.updating_booked || 0,
                        inquiry: slot.inquiry_booked || 0,
                        total: totalBooked
                    };
                    
                    const regInput = document.getElementById('edit_reg_capacity');
                    const updatingInput = document.getElementById('edit_updating_capacity');
                    const inquiryInput = document.getElementById('edit_inquiry_capacity');
                    
                    if (regInput) regInput.min = minReg;
                    if (updatingInput) updatingInput.min = minUpdating;
                    if (inquiryInput) inquiryInput.min = minInquiry;
                    
                    updateEditCapacityFields();
                    editModal.classList.add('show');
                } else {
                    alert('Slot not found');
                }
            } else {
                alert('Error loading slot data');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading slot data');
        }
    };

    function updateEditCapacityFields() {
        const dayTypeElem = document.getElementById('edit_day_type');
        if(!dayTypeElem) return;
        
        const dayType = dayTypeElem.value;
        const regInput = document.getElementById('edit_reg_capacity');
        const updatingInput = document.getElementById('edit_updating_capacity');
        const inquiryInput = document.getElementById('edit_inquiry_capacity');
        
        if (!regInput) return;
        
        if (dayType === 'holiday' || dayType === 'non_working') {
            regInput.disabled = true;
            updatingInput.disabled = true;
            inquiryInput.disabled = true;
            regInput.value = 0;
            updatingInput.value = 0;
            inquiryInput.value = 0;
        } else {
            regInput.disabled = false;
            updatingInput.disabled = false;
            inquiryInput.disabled = false;
            
            if (window.minCapacities) {
                if (parseInt(regInput.value) < window.minCapacities.reg) regInput.value = window.minCapacities.reg;
                if (parseInt(updatingInput.value) < window.minCapacities.updating) updatingInput.value = window.minCapacities.updating;
                if (parseInt(inquiryInput.value) < window.minCapacities.inquiry) inquiryInput.value = window.minCapacities.inquiry;
            }
        }
    }

    window.slotsShowSlotDetails = async function(dateKey) {
        const modal = document.getElementById('slotsSlotDetailModal');
        const modalBody = document.getElementById('slotsSlotDetailBody');
        if(!modal || !modalBody) return;
        
        modal.classList.add('show');
        modalBody.innerHTML = '<div class="slots-loading-state"><i class="fas fa-spinner fa-pulse"></i><p>Loading...</p></div>';

        try {
            const response = await fetch(`/admin/slots/details/${dateKey}`);
            const data = await response.json();

            if (data.success && data.slots.length > 0) {
                let html = '<div class="slots-table-responsive"><table class="slots-table"><thead><tr><th>Time Slot</th><th>Day Type</th><th>R</th><th>U</th><th>S</th><th>Booked</th><th>Available</th><th>Override</th><th>Actions</th></tr></thead><tbody>';
                for (const slot of data.slots) {
                    const total = (slot.reg_capacity || 0) + (slot.updating_capacity || 0) + (slot.inquiry_capacity || 0);
                    const booked = (slot.reg_booked || 0) + (slot.updating_booked || 0) + (slot.inquiry_booked || 0);
                    const overrideBadge = slot.has_override ? '<span class="slots-badge-icon" style="background:#ffc107; color:#856404;">Manual</span>' : '<span class="slots-badge-icon" style="background:#28a745; color:white;">Default</span>';
                    html += `<tr>
                                <td><strong>${escapeHtml(slot.time_slot_label)}</strong></td>
                                <td><span class="slots-day-type-badge ${slot.day_type}">${slot.day_type}</span></td>
                                <td>${slot.reg_capacity || 0}</td>
                                <td>${slot.updating_capacity || 0}</td>
                                <td>${slot.inquiry_capacity || 0}</td>
                                <td>${booked}</td>
                                <td>${total - booked}</td>
                                <td>${overrideBadge}</td>
                                <td><button type="button" class="slots-btn slots-btn-sm slots-btn-outline-primary" onclick="openEditSlotModal(${slot.id}, '${dateKey}')"><i class="fas fa-edit"></i> Edit</button></td>
                            </tr>`;
                }
                html += '</tbody></table></div>';
                modalBody.innerHTML = html;
            } else {
                modalBody.innerHTML = '<div class="slots-alert slots-alert-info">No slots configured for this date.</div>';
            }
        } catch (error) {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="slots-alert" style="background:#fee2e2; color:#991b1b;">Error loading details.</div>';
        }
    };
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close handlers
    const createSlotModal = document.getElementById('slotsCreateSlotModal');
    if (createSlotModal) {
        const createCloseBtn = createSlotModal.querySelector('.slots-modal-close');
        if (createCloseBtn) createCloseBtn.addEventListener('click', () => createSlotModal.classList.remove('show'));
        const createCancelBtn = createSlotModal.querySelector('.slots-btn-secondary');
        if (createCancelBtn) createCancelBtn.addEventListener('click', () => createSlotModal.classList.remove('show'));
    }

    const capacityRulesModal = document.getElementById('slotsCapacityRulesModal');
    if (capacityRulesModal) {
        const capacityCloseBtn = capacityRulesModal.querySelector('.slots-modal-close');
        if (capacityCloseBtn) capacityCloseBtn.addEventListener('click', () => capacityRulesModal.classList.remove('show'));
        const capacityCancelBtn = capacityRulesModal.querySelector('.slots-btn-secondary');
        if (capacityCancelBtn) capacityCancelBtn.addEventListener('click', () => capacityRulesModal.classList.remove('show'));
    }

    const bulkModal = document.getElementById('slotsBulkGenerateModal');
    if (bulkModal) {
        const bulkCloseBtn = bulkModal.querySelector('.slots-modal-close');
        if (bulkCloseBtn) bulkCloseBtn.addEventListener('click', () => bulkModal.classList.remove('show'));
        const bulkCancelBtn = bulkModal.querySelector('.slots-btn-secondary');
        if (bulkCancelBtn) bulkCancelBtn.addEventListener('click', () => bulkModal.classList.remove('show'));
    }

    const slotDetailModal = document.getElementById('slotsSlotDetailModal');
    if (slotDetailModal) {
        const detailCloseBtn = slotDetailModal.querySelector('.slots-modal-close');
        if (detailCloseBtn) detailCloseBtn.addEventListener('click', () => slotDetailModal.classList.remove('show'));
    }

    const editModal = document.getElementById('editSlotModal');
    if (editModal) {
        const editCloseBtn = editModal.querySelector('.edit-slot-modal-close');
        if (editCloseBtn) editCloseBtn.addEventListener('click', () => editModal.classList.remove('show'));
        const editCancelBtn = editModal.querySelector('.edit-slot-btn-secondary');
        if (editCancelBtn) editCancelBtn.addEventListener('click', (e) => {
            e.preventDefault();
            editModal.classList.remove('show');
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('slots-modal')) event.target.classList.remove('show');
        if (event.target.classList.contains('edit-slot-modal')) event.target.classList.remove('show');
    });

    function openModal(modalId) { 
        const modal = document.getElementById(modalId);
        if(modal) modal.classList.add('show'); 
    }
    
    function closeModal(modalId) { 
        const modal = document.getElementById(modalId);
        if(modal) modal.classList.remove('show'); 
    }

    // Create Slot Form
    const addSlotBtn = document.getElementById('slotsAddSingleSlotBtn');
    if(addSlotBtn) addSlotBtn.addEventListener('click', () => openModal('slotsCreateSlotModal'));
    
    // Select All button for time slots
    const selectAllBtn = document.getElementById('selectAllTimeSlots');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const checkboxes = document.querySelectorAll('input[name="time_slot_ids"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => cb.checked = !allChecked);
            selectAllBtn.textContent = allChecked ? 'Select All' : 'Deselect All';
        });
    }
    
    const createForm = document.getElementById('slotsCreateForm');
    if(createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const date = document.getElementById('create_date')?.value;
            const dayType = document.getElementById('create_day_type')?.value;
            const regCapacity = document.getElementById('reg_capacity')?.value || 4;
            const updatingCapacity = document.getElementById('updating_capacity')?.value || 4;
            const inquiryCapacity = document.getElementById('inquiry_capacity')?.value || 4;
            const notes = document.querySelector('textarea[name="notes"]')?.value || '';
            
            // Get all checked time slot IDs
            const checkedCheckboxes = document.querySelectorAll('input[name="time_slot_ids"]:checked');
            const selectedTimeSlotIds = Array.from(checkedCheckboxes).map(cb => cb.value);
            
            if (!date) {
                alert('Please select a date');
                return;
            }
            
            if (selectedTimeSlotIds.length === 0) {
                alert('Please select at least one time slot');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            submitBtn.disabled = true;

            try {
                let successCount = 0;
                let failureCount = 0;
                
                // Create a slot for each selected time slot
                for (const timeSlotId of selectedTimeSlotIds) {
                    const slotData = {
                        date: date,
                        time_slot_id: timeSlotId,
                        day_type: dayType,
                        reg_capacity: parseInt(regCapacity) || 0,
                        updating_capacity: parseInt(updatingCapacity) || 0,
                        inquiry_capacity: parseInt(inquiryCapacity) || 0,
                        notes: notes
                    };
                    
                    try {
                        const response = await fetch('{{ route("admin.slots.store") }}', { 
                            method: 'POST', 
                            body: JSON.stringify(slotData),
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="slots-csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest' 
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            successCount++;
                        } else {
                            failureCount++;
                        }
                    } catch (error) {
                        console.error('Error creating slot:', error);
                        failureCount++;
                    }
                }
                
                if (failureCount === 0) {
                    alert(`All ${successCount} slot(s) created successfully!`);
                    createForm.reset();
                    // Uncheck all time slot checkboxes
                    document.querySelectorAll('input[name="time_slot_ids"]').forEach(cb => cb.checked = false);
                    closeModal('slotsCreateSlotModal');
                    location.reload();
                } else {
                    alert(`${successCount} created, ${failureCount} failed`);
                    location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating slots');
            } finally {
                submitBtn.innerHTML = originalHtml;
                submitBtn.disabled = false;
            }
        });
    }

    // Edit Slot Form Submission
    const editFormSubmit = document.getElementById('editSlotForm');
    if(editFormSubmit) {
        editFormSubmit.addEventListener('submit', async function(e) {
            e.preventDefault();
            const slotId = document.getElementById('edit_slot_id').value;
            const formData = new FormData(this);
            
            // Explicitly add capacity values to ensure they're included
            formData.set('reg_capacity', document.getElementById('edit_reg_capacity')?.value || 0);
            formData.set('updating_capacity', document.getElementById('edit_updating_capacity')?.value || 0);
            formData.set('inquiry_capacity', document.getElementById('edit_inquiry_capacity')?.value || 0);
            
            formData.append('_token', document.querySelector('meta[name="slots-csrf-token"]').content);
            formData.append('_method', 'PUT');

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;

            try {
                const response = await fetch(`/admin/slots/${slotId}`, { 
                    method: 'POST', 
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (data.success) {
                    alert('Slot updated successfully!');
                    if(editModal) editModal.classList.remove('show');
                    location.reload();
                } else {
                    alert(data.message || 'Error updating slot');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error connecting to server');
            } finally {
                submitBtn.innerHTML = originalHtml;
                submitBtn.disabled = false;
            }
        });
    }

    // Capacity Rules Form
    const capacityRulesBtn = document.getElementById('slotsCapacityRulesBtn');
    if(capacityRulesBtn) capacityRulesBtn.addEventListener('click', () => openModal('slotsCapacityRulesModal'));
    
    const capacityRulesForm = document.getElementById('slotsCapacityRulesForm');
    if(capacityRulesForm) {
        capacityRulesForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', document.querySelector('meta[name="slots-csrf-token"]').content);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('{{ route("admin.slots.capacity-rules") }}', { 
                    method: 'POST', 
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (data.success) {
                    alert('Capacity rules saved successfully!');
                    closeModal('slotsCapacityRulesModal');
                    location.reload();
                } else {
                    alert(data.message || 'Error saving rules');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error connecting to server');
            } finally {
                submitBtn.innerHTML = originalHtml;
                submitBtn.disabled = false;
            }
        });
    }

    // Bulk Generate Form
    const bulkGenerateBtn = document.getElementById('slotsBulkGenerateBtn');
    if (bulkGenerateBtn) bulkGenerateBtn.addEventListener('click', () => openModal('slotsBulkGenerateModal'));
    
    const bulkSelectAllBtn = document.getElementById('bulkSelectAllTimeSlots');
    if (bulkSelectAllBtn) {
        bulkSelectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const checkboxes = document.querySelectorAll('input[name="time_slot_ids[]"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => cb.checked = !allChecked);
            bulkSelectAllBtn.textContent = allChecked ? 'Select All' : 'Deselect All';
        });
    }
    
    const bulkTimeSlotCheckboxes = document.querySelectorAll('input[name="time_slot_ids[]"]');
    bulkTimeSlotCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (bulkSelectAllBtn) {
                const allChecked = Array.from(bulkTimeSlotCheckboxes).every(cb => cb.checked);
                bulkSelectAllBtn.textContent = allChecked ? 'Deselect All' : 'Select All';
            }
            
            if (this.checked) {
                updateBulkCapacityFields(this.value);
            }
        });
    });
    
    async function updateBulkCapacityFields(timeSlotId) {
        if (!timeSlotId) return;
        
        try {
            const response = await fetch(`/admin/slots/default-capacities?time_slot_id=${timeSlotId}`);
            const data = await response.json();
            
            if (data.success) {
                const regInput = document.querySelector('#slotsBulkGenerateForm input[name="reg_capacity"]');
                const updatingInput = document.querySelector('#slotsBulkGenerateForm input[name="updating_capacity"]');
                const inquiryInput = document.querySelector('#slotsBulkGenerateForm input[name="inquiry_capacity"]');
                
                if (regInput) regInput.value = data.reg_capacity ?? 4;
                if (updatingInput) updatingInput.value = data.updating_capacity ?? 4;
                if (inquiryInput) inquiryInput.value = data.inquiry_capacity ?? 4;
            }
        } catch (error) {
            console.error('Error loading bulk slot capacities:', error);
        }
    }
    
    const bulkGenerateForm = document.getElementById('slotsBulkGenerateForm');
    if (bulkGenerateForm) {
        bulkGenerateForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const selectedTimeSlots = document.querySelectorAll('input[name="time_slot_ids[]"]:checked');
            if (selectedTimeSlots.length === 0) {
                alert('Please select at least one time slot');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('_token', document.querySelector('meta[name="slots-csrf-token"]').content);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('{{ route("admin.slots.bulk-generate") }}', { 
                    method: 'POST', 
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (data.success) {
                    alert(data.message || 'Slots generated successfully!');
                    closeModal('slotsBulkGenerateModal');
                    location.reload();
                } else {
                    alert(data.message || 'Error generating slots');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error connecting to server');
            } finally {
                submitBtn.innerHTML = originalHtml;
                submitBtn.disabled = false;
            }
        });
    }

    // Day type change handlers
    const editDayType = document.getElementById('edit_day_type');
    if(editDayType) {
        editDayType.addEventListener('change', updateEditCapacityFields);
    }

    const createDayType = document.getElementById('create_day_type');
    if(createDayType) {
        createDayType.addEventListener('change', function() {
            const capacityFields = document.getElementById('capacityFields');
            const timeSlotsGroup = document.getElementById('timeSlotsSelectorGroup');
            const notesGroup = document.getElementById('notesGroup');
            
            if (this.value === 'working') {
                // Working day - show time slots and capacity fields, hide notes
                if (timeSlotsGroup) timeSlotsGroup.style.display = 'block';
                if (capacityFields) capacityFields.style.display = 'flex';
                if (notesGroup) notesGroup.style.display = 'none';
                
                // Uncheck all time slot checkboxes for working day
                document.querySelectorAll('input[name="time_slot_ids"]').forEach(cb => cb.checked = false);
                
                // Set default capacity values
                document.getElementById('reg_capacity').value = 4;
                document.getElementById('updating_capacity').value = 4;
                document.getElementById('inquiry_capacity').value = 4;
                
                // Reset button text
                const selectAllBtn = document.getElementById('selectAllTimeSlots');
                if (selectAllBtn) selectAllBtn.textContent = 'Select All';
            } else {
                // Non-working day - hide time slots and capacity fields, show notes
                if (timeSlotsGroup) timeSlotsGroup.style.display = 'none';
                if (capacityFields) capacityFields.style.display = 'none';
                if (notesGroup) notesGroup.style.display = 'block';
                
                // Auto-select all time slot checkboxes (hidden, but values set)
                document.querySelectorAll('input[name="time_slot_ids"]').forEach(cb => cb.checked = true);
                
                // Set capacity values to 0 for non-working day
                document.getElementById('reg_capacity').value = 0;
                document.getElementById('updating_capacity').value = 0;
                document.getElementById('inquiry_capacity').value = 0;
            }
        });
    }

    // Check if date is working day
    const createDate = document.getElementById('create_date');
    if(createDate) {
        createDate.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const dayOfWeek = selectedDate.getDay();
            let dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
            
            if (!workingDays.includes(dayNumber)) {
                const dayNamesFull = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                if (!confirm(`Warning: ${dayNamesFull[dayOfWeek]} is set as a NON-WORKING day. Continue?`)) {
                    this.value = '';
                }
            }
        });
    }

    // Calendar navigation
    const prevMonthBtn = document.getElementById('slotsPrevMonth');
    const nextMonthBtn = document.getElementById('slotsNextMonth');
    const todayBtn = document.getElementById('slotsTodayBtn');
    
    if(prevMonthBtn) {
        prevMonthBtn.addEventListener('click', () => { 
            if (!slotsIsLoading) {
                prevMonthBtn.disabled = true;
                slotsCurrentDate.setMonth(slotsCurrentDate.getMonth() - 1); 
                slotsLoadSlots();
                setTimeout(() => prevMonthBtn.disabled = false, 500);
            }
        });
    }
    if(nextMonthBtn) {
        nextMonthBtn.addEventListener('click', () => { 
            if (!slotsIsLoading) {
                nextMonthBtn.disabled = true;
                slotsCurrentDate.setMonth(slotsCurrentDate.getMonth() + 1); 
                slotsLoadSlots();
                setTimeout(() => nextMonthBtn.disabled = false, 500);
            }
        });
    }
    if(todayBtn) {
        todayBtn.addEventListener('click', () => { 
            if (!slotsIsLoading) {
                todayBtn.disabled = true;
                slotsCurrentDate = new Date(); 
                slotsLoadSlots();
                setTimeout(() => todayBtn.disabled = false, 500);
            }
        });
    }

    // Load default capacities when editing time slot changes
    const editTimeSlot = document.getElementById('edit_time_slot_id');
    if(editTimeSlot) {
        editTimeSlot.addEventListener('change', async function() {
            const timeSlotId = this.value;
            const dayType = document.getElementById('edit_day_type').value;
            if (timeSlotId && dayType === 'working') {
                try {
                    const response = await fetch(`/admin/slots/default-capacities?time_slot_id=${timeSlotId}`);
                    const data = await response.json();
                    if (data.success && window.minCapacities) {
                        const newMinReg = window.currentBooked?.reg || 0;
                        const newMinUpdating = window.currentBooked?.updating || 0;
                        const newMinInquiry = window.currentBooked?.inquiry || 0;
                        
                        window.minCapacities = {
                            reg: newMinReg,
                            updating: newMinUpdating,
                            inquiry: newMinInquiry
                        };
                        
                        document.getElementById('edit_reg_min').innerHTML = newMinReg;
                        document.getElementById('edit_updating_min').innerHTML = newMinUpdating;
                        document.getElementById('edit_inquiry_min').innerHTML = newMinInquiry;
                        
                        const regInput = document.getElementById('edit_reg_capacity');
                        const updatingInput = document.getElementById('edit_updating_capacity');
                        const inquiryInput = document.getElementById('edit_inquiry_capacity');
                        
                        if (regInput) regInput.min = newMinReg;
                        if (updatingInput) updatingInput.min = newMinUpdating;
                        if (inquiryInput) inquiryInput.min = newMinInquiry;
                        
                        if (parseInt(regInput.value) < newMinReg) regInput.value = newMinReg;
                        if (parseInt(updatingInput.value) < newMinUpdating) updatingInput.value = newMinUpdating;
                        if (parseInt(inquiryInput.value) < newMinInquiry) inquiryInput.value = newMinInquiry;
                    }
                } catch (error) {
                    console.error('Error loading default capacities:', error);
                }
            }
        });
    }

    // Initialize
    slotsLoadSlots();
</script>
@endsection
