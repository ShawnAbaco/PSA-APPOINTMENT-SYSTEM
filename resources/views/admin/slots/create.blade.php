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
                
                $workingDaysCollection = WorkingDaysDefault::where('is_working', true)
                    ->orderBy('day_of_week')
                    ->get();
                
                $workingDaysArray = $workingDaysCollection->pluck('day_of_week')->toArray();
                $workingDaysList = $workingDaysCollection->pluck('day_name')->toArray();
                $workingDaysText = implode(', ', $workingDaysList);
                
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
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ request()->get('date') }}" required>
                        <small class="text-muted">Select the date for this slot</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
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
                    <div class="col-md-3 mb-3">
                        <label class="form-label">REG Capacity *</label>
                        <input type="number" name="reg_capacity" class="form-control capacity-input" value="10" min="0" max="100" required>
                        <small class="text-muted">National ID Registration</small>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">COR Capacity *</label>
                        <input type="number" name="correction_capacity" class="form-control capacity-input" value="5" min="0" max="100" required>
                        <small class="text-muted">Correction/Updating</small>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">EPH Capacity *</label>
                        <input type="number" name="ephilid_capacity" class="form-control capacity-input" value="3" min="0" max="100" required>
                        <small class="text-muted">ePhilID Issuance</small>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">TRN Capacity *</label>
                        <input type="number" name="trn_capacity" class="form-control capacity-input" value="2" min="0" max="100" required>
                        <small class="text-muted">TRN Retrieval</small>
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
    const capacityInputs = document.querySelectorAll('.capacity-input');
    
    // Working days from database (Sunday = 7, not included if is_working = 0)
    const workingDays = @json($workingDaysArray);
    
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
                // Keep original values but show a warning
                input.style.backgroundColor = '#fff3e0';
            });
        } else {
            // Enable all capacity inputs
            capacityInputs.forEach(input => {
                input.disabled = false;
                input.style.backgroundColor = '';
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
    });
    
    // Initialize capacity fields based on default day type
    updateCapacityFields();
    
    // Additional validation for Sunday selection
    dateInput?.addEventListener('input', function() {
        const selectedDate = new Date(this.value);
        if (selectedDate.getDay() === 0) { // Sunday
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            console.log(`Selected date is ${dayNames[selectedDate.getDay()]}`);
        }
    });
</script>
@endpush
@endsection