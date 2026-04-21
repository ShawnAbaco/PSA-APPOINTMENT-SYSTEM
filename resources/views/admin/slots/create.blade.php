@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create Appointment Slot</h1>
        <a href="{{ route('admin.slots.index') }}" class="btn btn-secondary">Back</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            @php
                // Use the new working_days_defaults table instead of settings
                use App\Models\WorkingDaysDefault;
                use App\Models\TimeSlot;
                
                $workingDaysCollection = WorkingDaysDefault::where('is_working', true)
                    ->orderBy('day_of_week')
                    ->get();
                
                $workingDaysArray = $workingDaysCollection->pluck('day_of_week')->toArray();
                $workingDaysList = $workingDaysCollection->pluck('day_name')->toArray();
                $workingDaysText = implode(', ', $workingDaysList);
                
                $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
                
                $dayNames = [
                    1 => 'Monday', 
                    2 => 'Tuesday', 
                    3 => 'Wednesday', 
                    4 => 'Thursday', 
                    5 => 'Friday', 
                    6 => 'Saturday', 
                    7 => 'Sunday'
                ];
            @endphp
            
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i> 
                <strong>Working Days:</strong> {{ $workingDaysText ?: 'Monday to Friday' }}<br>
                <small>Only these days are available for booking. Non-working days will not appear in the client calendar. You can change this in <a href="{{ route('admin.settings.index') }}">System Settings</a>.</small>
            </div>
            
            <form method="POST" action="{{ route('admin.slots.store') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ request()->get('date') }}" required>
                        <small class="text-muted">Select the date for this slot</small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Time Slot *</label>
                        <select name="time_slot_id" id="time_slot_id" class="form-control" required>
                            <option value="">Select Time Slot</option>
                            @foreach($timeSlots as $timeSlot)
                                <option value="{{ $timeSlot->id }}">{{ $timeSlot->slot_label }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the time slot for this appointment</small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Day Type *</label>
                        <select name="day_type" id="day_type" class="form-control" required>
                            <option value="working">Working Day (Full Capacity)</option>
                            <option value="half_day">Half Day (50% Capacity)</option>
                            <option value="holiday">Holiday (No Appointments)</option>
                            <option value="special">Special Day (Custom)</option>
                        </select>
                        <small class="text-muted">
                            <strong>Working:</strong> Full capacity available<br>
                            <strong>Half Day:</strong> Only 50% of capacity available<br>
                            <strong>Holiday:</strong> No appointments allowed<br>
                            <strong>Special:</strong> Custom configuration with notes
                        </small>
                    </div>
                </div>
                
                <div class="row" id="capacityFields">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Registration (R) Capacity *</label>
                        <input type="number" name="reg_capacity" id="reg_capacity" class="form-control capacity-input" value="10" min="0" max="100" required>
                        <small class="text-muted">National ID Registration slots</small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Updating (U) Capacity *</label>
                        <input type="number" name="updating_capacity" id="updating_capacity" class="form-control capacity-input" value="5" min="0" max="100" required>
                        <small class="text-muted">Correction/Updating slots</small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Inquiry (S) Capacity *</label>
                        <input type="number" name="inquiry_capacity" id="inquiry_capacity" class="form-control capacity-input" value="8" min="0" max="100" required>
                        <small class="text-muted">Status Inquiry / TRN Retrieval slots</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this slot (e.g., holiday name, special event, half day reason)"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Create Slot</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dateInput = document.getElementById('date');
    const dayTypeSelect = document.getElementById('day_type');
    const timeSlotSelect = document.getElementById('time_slot_id');
    const capacityInputs = document.querySelectorAll('.capacity-input');
    const regCapacity = document.getElementById('reg_capacity');
    const updatingCapacity = document.getElementById('updating_capacity');
    const inquiryCapacity = document.getElementById('inquiry_capacity');
    
    // Working days from database
    const workingDays = @json($workingDaysArray);
    
    // Default capacities from service config (can be fetched via AJAX if needed)
    const defaultCapacities = {
        reg: 10,
        updating: 5,
        inquiry: 8
    };
    
    // Function to update capacity fields based on day type
    function updateCapacityFields() {
        const dayType = dayTypeSelect.value;
        
        if (dayType === 'holiday') {
            // Disable all capacity inputs and set to 0
            capacityInputs.forEach(input => {
                input.disabled = true;
                input.value = 0;
            });
        } else if (dayType === 'half_day') {
            // Enable inputs but show that it's half capacity (will be calculated in backend)
            capacityInputs.forEach(input => {
                input.disabled = false;
                input.style.backgroundColor = '#fff3e0';
            });
        } else {
            // Enable all capacity inputs
            capacityInputs.forEach(input => {
                input.disabled = false;
                input.style.backgroundColor = '';
                // Restore default values if empty
                if (!input.value || input.value == 0) {
                    if (input.id === 'reg_capacity') input.value = defaultCapacities.reg;
                    if (input.id === 'updating_capacity') input.value = defaultCapacities.updating;
                    if (input.id === 'inquiry_capacity') input.value = defaultCapacities.inquiry;
                }
            });
        }
    }
    
    // Function to check if selected date is a working day
    function checkWorkingDay() {
        if (!dateInput.value) return true;
        
        const selectedDate = new Date(dateInput.value);
        const dayOfWeek = selectedDate.getDay(); // 0=Sun, 1=Mon, 6=Sat
        let dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
        
        const isWorking = workingDays.includes(dayNumber);
        
        if (!isWorking) {
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const warningMessage = `⚠️ Warning: ${dayNames[dayOfWeek]} is currently set as a NON-WORKING DAY in system settings.\n\n`;
            const continueMessage = `Would you like to continue creating this slot?`;
            
            if (!confirm(warningMessage + continueMessage)) {
                dateInput.value = '';
                return false;
            }
        }
        return true;
    }
    
    // Function to check if time slot already exists for this date
    async function checkExistingTimeSlot() {
        if (!dateInput.value || !timeSlotSelect.value) return true;
        
        const date = dateInput.value;
        const timeSlotId = timeSlotSelect.value;
        
        try {
            const response = await fetch(`/admin/slots/check-existing?date=${date}&time_slot_id=${timeSlotId}`);
            const data = await response.json();
            
            if (data.exists) {
                alert(`⚠️ A slot already exists for ${date} at ${data.time_slot_label}. Please select a different time slot or date.`);
                timeSlotSelect.value = '';
                return false;
            }
        } catch (error) {
            console.error('Error checking existing slot:', error);
        }
        return true;
    }
    
    // Add warning for half-day selection
    dayTypeSelect?.addEventListener('change', function() {
        if (this.value === 'half_day') {
            alert('Note: Half day means only 50% of the configured capacity will be available for booking.');
        }
        updateCapacityFields();
    });
    
    // Check working day when date changes
    dateInput?.addEventListener('change', function() {
        checkWorkingDay();
        checkExistingTimeSlot();
    });
    
    // Check existing time slot when time slot changes
    timeSlotSelect?.addEventListener('change', function() {
        checkExistingTimeSlot();
    });
    
    // Initialize capacity fields based on default day type
    updateCapacityFields();
    
    // Validate form before submit
    document.querySelector('form').addEventListener('submit', async function(e) {
        if (!dateInput.value) {
            e.preventDefault();
            alert('Please select a date.');
            return false;
        }
        
        if (!timeSlotSelect.value) {
            e.preventDefault();
            alert('Please select a time slot.');
            return false;
        }
        
        // Check if time slot exists
        const exists = await checkExistingTimeSlot();
        if (!exists) {
            e.preventDefault();
            return false;
        }
        
        // Validate capacities for non-holiday days
        const dayType = dayTypeSelect.value;
        if (dayType !== 'holiday') {
            const reg = parseInt(regCapacity.value) || 0;
            const updating = parseInt(updatingCapacity.value) || 0;
            const inquiry = parseInt(inquiryCapacity.value) || 0;
            
            if (reg === 0 && updating === 0 && inquiry === 0) {
                const confirmSubmit = confirm('All capacities are set to 0. No appointments can be booked for this slot. Continue?');
                if (!confirmSubmit) {
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
</script>
@endpush
@endsection