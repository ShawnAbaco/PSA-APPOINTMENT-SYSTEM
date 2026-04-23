@extends('layouts.admin')

<style>
    /* ============================================
       SETTINGS PAGE PURE CSS STYLES
       ============================================ */
    
    /* Container */
    .settings-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    /* Header Section */
    .settings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .header-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .header-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .header-icon i {
        font-size: 24px;
        color: white;
    }
    
    .header-text h1 {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 5px 0;
        color: #1f2937;
    }
    
    .header-text p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }
    
    .btn-clear-cache {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        color: white;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-clear-cache:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(245, 158, 11, 0.3);
    }
    
    /* Cards */
    .settings-card {
        background: white;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .settings-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        padding: 20px 25px;
        border-bottom: 2px solid #f3f4f6;
        background: white;
    }
    
    .card-header h5 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .card-header h5 i {
        margin-right: 10px;
        color: #667eea;
    }
    
    .card-body {
        padding: 25px;
    }
    
    /* Alert Styles */
    .alert-modern {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-info {
        background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
        color: #1e40af;
        border-left: 4px solid #3b82f6;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fed7aa 0%, #ffedd5 100%);
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    
    /* Form Groups */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }
    
    .input-group {
        display: flex;
        align-items: stretch;
    }
    
    .input-group-text {
        display: flex;
        align-items: center;
        padding: 0 15px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: #6b7280;
    }
    
    .form-control, .form-select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif;
    }
    
    .input-group .form-control {
        border-radius: 0 10px 10px 0;
    }
    
    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-text {
        display: block;
        margin-top: 5px;
        font-size: 12px;
        color: #6b7280;
    }
    
    /* Grid System */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: -12px;
    }
    
    .col-md-6 {
        flex: 0 0 50%;
        padding: 12px;
    }
    
    .col-md-12 {
        flex: 0 0 100%;
        padding: 12px;
    }
    
    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 20px;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th, .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .table th {
        background: #f9fafb;
        font-weight: 600;
        color: #374151;
    }
    
    .table tbody tr:hover {
        background: #f9fafb;
    }
    
    .table-bordered {
        border: 1px solid #e5e7eb;
    }
    
    .table-bordered th, .table-bordered td {
        border: 1px solid #e5e7eb;
    }
    
    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-success {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        color: white;
    }
    
    .badge-danger {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        color: white;
    }
    
    .badge-info {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }
    
    .badge-primary {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .badge-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    
    /* Buttons */
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Inter', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        color: white;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
    }
    
    .btn-outline-primary {
        background: transparent;
        border: 2px solid #667eea;
        color: #667eea;
    }
    
    .btn-outline-primary:hover {
        background: #667eea;
        color: white;
    }
    
    .btn-outline-secondary {
        background: transparent;
        border: 2px solid #6b7280;
        color: #6b7280;
    }
    
    .btn-outline-secondary:hover {
        background: #6b7280;
        color: white;
    }
    
    .btn-sm {
        padding: 5px 12px;
        font-size: 12px;
    }
    
    .btn-lg {
        padding: 12px 30px;
        font-size: 16px;
    }
    
    /* Working Days - Checkbox Buttons */
    .days-container {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .day-checkbox {
        display: none;
    }
    
    .day-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .day-checkbox:checked + .day-label {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }
    
    .day-label:hover {
        border-color: #667eea;
        transform: translateY(-2px);
    }
    
    /* Capacity Inputs */
    .capacity-input {
        width: 90px;
        text-align: center;
        padding: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .capacity-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* Time Slots Container */
    .time-slots-container {
        background: #f9fafb;
        padding: 20px;
        border-radius: 12px;
        margin-top: 20px;
    }
    
    /* Button Group */
    .btn-group {
        display: flex;
        gap: 8px;
    }
    
    /* Action Buttons Container */
    .action-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e5e7eb;
    }
    
    /* Divider */
    hr {
        margin: 20px 0;
        border: none;
        border-top: 2px solid #e5e7eb;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .col-md-6 {
            flex: 0 0 100%;
        }
        
        .settings-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
        
        .header-title {
            flex-direction: column;
            text-align: center;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
        
        .table-responsive {
            font-size: 12px;
        }
        
        .capacity-input {
            width: 70px;
        }
    }
    
    /* Loading Animation */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .btn:active {
        animation: pulse 0.3s ease;
    }
    
    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }
    
    /* Hover Effects */
    .btn, .day-label, .form-control, .form-select {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Focus Visible for Accessibility */
    .btn:focus-visible, .form-control:focus-visible, .day-label:focus-visible {
        outline: 2px solid #667eea;
        outline-offset: 2px;
    }
</style>


@section('content')
<div class="settings-container">
    <!-- Modern Header -->
    <div class="settings-header">
        <div class="header-title">
            <div class="header-icon">
                <i class="fas fa-sliders-h"></i>
            </div>
            <div class="header-text">
                <h1>System Settings</h1>
                <p>Configure appointment rules, time slots, and email settings</p>
            </div>
        </div>
        <div>
            <button type="button" class="btn-clear-cache" id="refreshCacheBtn">
                <i class="fas fa-sync-alt"></i> Clear Cache
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" id="settingsForm">
        @csrf
        
        <!-- Appointment Settings -->
        <div class="settings-card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-alt"></i> Appointment Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Advance Booking Days</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-week"></i></span>
                                <input type="number" name="advance_booking_days" class="form-control" value="{{ $settings['advance_booking_days'] ?? 30 }}" min="1" max="365">
                            </div>
                            <small class="form-text">How many days in advance users can book appointments</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Cancellation Hours</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                <input type="number" name="cancellation_hours" class="form-control" value="{{ $settings['cancellation_hours'] ?? 24 }}" min="1" max="168">
                            </div>
                            <small class="form-text">Hours before appointment to allow cancellation</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Enable Email Notifications</label>
                            <select name="enable_email" class="form-select">
                                <option value="true" {{ ($settings['enable_email'] ?? true) == true ? 'selected' : '' }}>Yes</option>
                                <option value="false" {{ ($settings['enable_email'] ?? true) == false ? 'selected' : '' }}>No</option>
                            </select>
                            <small class="form-text">Send email notifications for appointment confirmations</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Enable Per-Service Limits</label>
                            <select name="enable_per_service_limits" class="form-select">
                                <option value="true" {{ ($settings['enable_per_service_limits'] ?? true) == true ? 'selected' : '' }}>Yes</option>
                                <option value="false" {{ ($settings['enable_per_service_limits'] ?? true) == false ? 'selected' : '' }}>No</option>
                            </select>
                            <small class="form-text">Enable separate slot limits for each service type</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Time Slots Configuration -->
        <div class="settings-card">
            <div class="card-header">
                <h5><i class="fas fa-clock"></i> Time Slots Configuration</h5>
            </div>
            <div class="card-body">
                <div class="alert-modern alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Time slots define when appointments can be scheduled. Each time slot has default capacity rules that can be overridden per date.</span>
                </div>
                
                <div id="timeSlotsContainer">
                    @php
                        $timeSlots = App\Models\TimeSlot::orderBy('display_order')->get();
                    @endphp
                    
                    @if($timeSlots->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
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
                                    @foreach($timeSlots as $slot)
                                        <tr>
                                            <td><strong>{{ date('g:i A', strtotime($slot->start_time)) }}</strong></td>
                                            <td><strong>{{ date('g:i A', strtotime($slot->end_time)) }}</strong></td>
                                            <td><span class="badge badge-primary">{{ $slot->label }}</span></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <i class="fas fa-users"></i> {{ $slot->capacity_per_slot ?? 4 }} per slot
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $slot->is_active ? 'badge-success' : 'badge-danger' }}">
                                                    <i class="fas {{ $slot->is_active ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                    {{ $slot->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-primary btn-sm edit-time-slot" 
                                                            data-id="{{ $slot->id }}"
                                                            data-start_time="{{ $slot->start_time }}"
                                                            data-end_time="{{ $slot->end_time }}"
                                                            data-slot_label="{{ $slot->label }}"
                                                            data-capacity="{{ $slot->capacity_per_slot ?? 4 }}"
                                                            data-active="{{ $slot->is_active }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-time-slot" 
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
                        <div class="alert-modern alert-warning">No time slots configured. Add time slots below.</div>
                    @endif
                    
                    <hr>
                    
                    <div class="time-slots-container">
                        <h6 style="margin-bottom: 15px; font-weight: 600;">
                            <i class="fas fa-plus-circle" style="color: #10b981;"></i> Add New Time Slot
                        </h6>
                        <div class="row">
                            <div class="col-md-2">
                                <label class="form-label">Start Time</label>
                                <input type="time" id="new_start_time" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">End Time</label>
                                <input type="time" id="new_end_time" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Label (optional)</label>
                                <input type="text" id="new_slot_label" class="form-control" placeholder="e.g., 9:00 AM - 10:00 AM">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Capacity</label>
                                <input type="number" id="new_capacity" class="form-control" value="4" min="1" max="50">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" id="addTimeSlotBtn" class="btn btn-success" style="width: 100%;">
                                    <i class="fas fa-plus"></i> Add Time Slot
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Default Capacity Rules - SIMPLIFIED to Working/Non-Working -->
        <div class="settings-card">
            <div class="card-header">
                <h5><i class="fas fa-sliders-h"></i> Default Capacity Rules</h5>
            </div>
            <div class="card-body">
                <div class="alert-modern alert-warning">
                    <i class="fas fa-info-circle"></i>
                    <span>These are the default capacity rules for working and non-working days. When you create a new slot without an override, these values will be used. <strong>Changes here will NOT affect existing slots that have manual overrides.</strong></span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead style="background: #1f2937; color: white;">
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
                            @foreach($timeSlots as $timeSlot)
                                @php
                                    $dayTypes = ['working', 'non_working'];
                                    $dayTypeNames = ['working' => 'Working Day', 'non_working' => 'Non-Working Day'];
                                    $dayTypeColors = ['working' => 'success', 'non_working' => 'danger'];
                                @endphp
                                @foreach($dayTypes as $dayType)
                                    @php
                                        $rule = $capacityRules[$timeSlot->id]->firstWhere('day_type', $dayType) ?? null;
                                        $defaultValues = [
                                            'working' => ['reg' => 4, 'updating' => 4, 'inquiry' => 4],
                                            'non_working' => ['reg' => 0, 'updating' => 0, 'inquiry' => 0],
                                        ];
                                    @endphp
                                    <tr>
                                        @if($loop->first)
                                            <td rowspan="{{ count($dayTypes) }}" style="vertical-align: middle; background: #f9fafb;">
                                                <strong>{{ $timeSlot->label }}</strong><br>
                                                <small style="color: #6b7280;">{{ date('g:i A', strtotime($timeSlot->start_time)) }} - {{ date('g:i A', strtotime($timeSlot->end_time)) }}</small>
                                            </td>
                                        @endif
                                        <td>
                                            <span class="badge badge-{{ $dayTypeColors[$dayType] }}">
                                                {{ $dayTypeNames[$dayType] }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $rule->reason ?? ($dayType === 'working' ? 'Regular working day' : 'Non-working day') }}</small>
                                        </td>
                                        <td>
                                            <input type="number" name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][reg]" 
                                                   class="capacity-input" 
                                                   value="{{ $rule->reg_capacity ?? $defaultValues[$dayType]['reg'] }}" 
                                                   min="0" max="100">
                                        </td>
                                        <td>
                                            <input type="number" name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][updating]" 
                                                   class="capacity-input" 
                                                   value="{{ $rule->updating_capacity ?? $defaultValues[$dayType]['updating'] }}" 
                                                   min="0" max="100">
                                        </td>
                                        <td>
                                            <input type="number" name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][inquiry]" 
                                                   class="capacity-input" 
                                                   value="{{ $rule->inquiry_capacity ?? $defaultValues[$dayType]['inquiry'] }}" 
                                                   min="0" max="100">
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="alert-modern alert-info" style="margin-top: 15px;">
                    <i class="fas fa-info-circle"></i>
                    <span><strong>Note:</strong> Working days are Tuesday to Friday. Non-working days include Monday, Saturday, and Sunday (or any day marked as non-working in Working Days Configuration).</span>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" id="saveCapacityRulesBtn" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Default Capacity Rules
                    </button>
                    <button type="button" id="resetCapacityRulesBtn" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i> Reset to Defaults
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Working Days Configuration -->
        <div class="settings-card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-week"></i> Working Days Configuration</h5>
            </div>
            <div class="card-body">
                @php
                    $workingDays = isset($settings['working_days']) ? explode(',', $settings['working_days']) : ['2','3','4','5'];
                    $dayNames = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday'];
                @endphp
                
                <div class="days-container">
                    @foreach($dayNames as $value => $name)
                        <div>
                            <input type="checkbox" name="working_days[]" value="{{ $value }}" id="day{{ $value }}" class="day-checkbox" 
                                   {{ in_array($value, $workingDays) ? 'checked' : '' }}>
                            <label class="day-label" for="day{{ $value }}">
                                <i class="fas fa-calendar-day"></i> {{ $name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                
                <small class="form-text">Select which days appointments can be booked. Non-working days will not appear in the client calendar.</small>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" id="selectWeekdays" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-briefcase"></i> Weekdays (Mon-Fri)
                    </button>
                    <button type="button" id="selectWeekends" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-umbrella-beach"></i> Weekends (Sat-Sun)
                    </button>
                    <button type="button" id="selectAllDays" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-calendar-week"></i> All Days
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Email Settings -->
        <div class="settings-card">
            <div class="card-header">
                <h5><i class="fas fa-envelope"></i> Email (SMTP) Settings</h5>
            </div>
            <div class="card-body">
                <div class="alert-modern alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Configure your SMTP settings to enable email notifications. For Gmail, you need to use an <strong>App Password</strong> (enable 2FA first).</span>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">SMTP Host</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-server"></i></span>
                                <input type="text" name="email_host" class="form-control" value="{{ $settings['email_host'] ?? 'smtp.gmail.com' }}" placeholder="smtp.gmail.com">
                            </div>
                            <small class="form-text">e.g., smtp.gmail.com, smtp.office365.com, mail.yourdomain.com</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">SMTP Port</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plug"></i></span>
                                <input type="number" name="email_port" class="form-control" value="{{ $settings['email_port'] ?? 587 }}" placeholder="587">
                            </div>
                            <small class="form-text">587 for TLS, 465 for SSL</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Encryption</label>
                            <select name="email_encryption" class="form-select">
                                <option value="tls" {{ ($settings['email_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                                <option value="ssl" {{ ($settings['email_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                            <small class="form-text">Security protocol for email sending</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">SMTP Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="email_username" class="form-control" value="{{ $settings['email_username'] ?? '' }}" placeholder="your-email@gmail.com">
                            </div>
                            <small class="form-text">Your email address (e.g., your-email@gmail.com)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">SMTP Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="email_password" id="email_password" class="form-control" 
                                       value="{{ $settings['email_password'] ?? '' }}" 
                                       placeholder="Enter new password to change" autocomplete="off">
                                <button type="button" id="togglePasswordBtn" class="btn btn-outline-secondary" style="border-radius: 0 10px 10px 0;">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <small class="form-text" id="passwordStatus">
                                @if(!empty($settings['email_password']) && $settings['email_password'] === '********')
                                    Password is set. Enter new password to change it.
                                @elseif(!empty($settings['email_password']))
                                    Password is configured.
                                @else
                                    No password set. Enter your SMTP password.
                                @endif
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">From Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email_from_address" class="form-control" value="{{ $settings['email_from_address'] ?? 'noreply@psa.gov.ph' }}" placeholder="noreply@psa.gov.ph">
                            </div>
                            <small class="form-text">Sender email address (usually same as SMTP username)</small>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">From Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" name="email_from_name" class="form-control" value="{{ $settings['email_from_name'] ?? 'PSA Appointment System' }}" placeholder="PSA Appointment System">
                            </div>
                            <small class="form-text">Sender name that recipients will see</small>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="button" class="btn btn-outline-primary" id="testEmailBtn">
                        <i class="fas fa-paper-plane"></i> Send Test Email
                    </button>
                    <small class="form-text" style="display: inline-block; margin-left: 10px;">Test your email configuration by sending a test message</small>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Save All Settings
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-lg">
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
        $('.day-checkbox').each(function() {
            let val = parseInt($(this).val());
            $(this).prop('checked', val >= 1 && val <= 5);
        });
    });
    
    $('#selectWeekends').click(function() {
        $('.day-checkbox').each(function() {
            let val = parseInt($(this).val());
            $(this).prop('checked', val === 6 || val === 7);
        });
    });
    
    $('#selectAllDays').click(function() {
        $('.day-checkbox').prop('checked', true);
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
                passwordStatus.innerHTML = '<i class="fas fa-edit"></i> New password will be saved when you submit the form.';
                passwordStatus.style.color = '#f59e0b';
            } else if (this.value.length === 0 && passwordStatus) {
                passwordStatus.innerHTML = '<i class="fas fa-lock"></i> Leave empty to keep current password.';
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
                    showToast('Error', response.message || 'Error adding time slot', 'error');
                }
            },
            error: function(xhr) {
                showToast('Error', xhr.responseJSON?.message || 'Failed to add time slot', 'error');
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
                    showToast('Error', response.message || 'Error updating time slot', 'error');
                }
            },
            error: function(xhr) {
                showToast('Error', xhr.responseJSON?.message || 'Failed to update time slot', 'error');
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
        
        $('.capacity-input').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            formData.append(name, value);
        });
        
        showToast('Saving...', 'Please wait while we save your settings', 'info');
        
        $.ajax({
            url: '{{ route("admin.slots.capacity-rules") }}',
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
    
    // Reset Capacity Rules - SIMPLIFIED for working/non_working
    $('#resetCapacityRulesBtn').click(function() {
        if (confirm('Reset all capacity rules to default values? This cannot be undone.')) {
            $('.capacity-input').each(function() {
                const name = $(this).attr('name');
                if (name.includes('working')) {
                    // Working days: 4 for all services
                    if (name.includes('reg')) $(this).val(4);
                    else if (name.includes('updating')) $(this).val(4);
                    else if (name.includes('inquiry')) $(this).val(4);
                } else if (name.includes('non_working')) {
                    // Non-working days: 0 for all services
                    $(this).val(0);
                }
            });
            showToast('Reset Complete', 'Values reset to defaults (Working days: 4 each, Non-working days: 0). Click "Save Default Capacity Rules" to apply.', 'success');
        }
    });
    
    // Test email functionality
    $('#testEmailBtn').click(function() {
        const testEmail = prompt('Enter email address to send test email:', '{{ Auth::user()->email }}');
        if (!testEmail) return;
        
        const originalText = $(this).html();
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.settings.test-email") }}',
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
                url: '{{ route("admin.settings.clear-cache") }}',
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
    // Simple alert fallback
    alert(title + ': ' + message);
}
</script>
@endpush
@endsection