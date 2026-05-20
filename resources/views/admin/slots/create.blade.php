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
                use App\Models\WorkingDaysDefault;
                use App\Models\TimeSlot;
                use App\Models\SlotCapacityRule;
                
                // Get working days from working_days_defaults table using day_type
                $workingDaysCollection = WorkingDaysDefault::where('day_type', 'working')->get();
                $workingDaysArray = [];
                $workingDaysList = [];
                $dayNumberMap = [
                    'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4,
                    'friday' => 5, 'saturday' => 6, 'sunday' => 7
                ];
                foreach ($workingDaysCollection as $day) {
                    $dayNumber = $dayNumberMap[$day->day_name] ?? null;
                    if ($dayNumber) {
                        $workingDaysArray[] = $dayNumber;
                        $workingDaysList[] = ucfirst($day->day_name);
                    }
                }
                if (empty($workingDaysArray)) {
                    $workingDaysArray = [1, 2, 3, 4, 5];
                    $workingDaysList = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                }
                $workingDaysText = implode(', ', $workingDaysList);
                
                $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
                
                // Get default capacities from slot_capacity_rules for working days
                $defaultCapacities = ['reg' => 4, 'updating' => 4, 'inquiry' => 4];
                if ($timeSlots->isNotEmpty()) {
                    $firstTimeSlot = $timeSlots->first();
                    $defaultRule = SlotCapacityRule::where('time_slot_id', $firstTimeSlot->id)
                        ->where('day_type', 'working')
                        ->first();
                    if ($defaultRule) {
                        $defaultCapacities = [
                            'reg' => $defaultRule->reg_capacity ?? 4,
                            'updating' => $defaultRule->updating_capacity ?? 4,
                            'inquiry' => $defaultRule->inquiry_capacity ?? 4,
                        ];
                    }
                }
            @endphp
            
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i> 
                <strong>Working Days:</strong> {{ $workingDaysText }}<br>
                <small>Only these days are available for booking. Non-working days will not appear in the client calendar. You can change this in <a href="{{ route('admin.settings.index') }}">System Settings</a>.</small>
            </div>
            
            <form id="createSlotForm">
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
                                <option value="{{ $timeSlot->id }}">{{ $timeSlot->label }}</option>
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
                        <input type="number" name="reg_capacity" id="reg_capacity" class="form-control capacity-input" value="{{ $defaultCapacities['reg'] }}" min="0" max="100" required>
                        <small class="text-muted">National ID Registration slots</small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Updating (U) Capacity *</label>
                        <input type="number" name="updating_capacity" id="updating_capacity" class="form-control capacity-input" value="{{ $defaultCapacities['updating'] }}" min="0" max="100" required>
                        <small class="text-muted">Correction/Updating slots</small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Inquiry (S) Capacity *</label>
                        <input type="number" name="inquiry_capacity" id="inquiry_capacity" class="form-control capacity-input" value="{{ $defaultCapacities['inquiry'] }}" min="0" max="100" required>
                        <small class="text-muted">Status Inquiry / TRN Retrieval slots</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this slot (e.g., holiday name, special event, half day reason)"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Create Slot</button>
                <span id="formStatus" style="margin-left: 10px; display: none;"></span>
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
    const form = document.getElementById('createSlotForm');
    const formStatus = document.getElementById('formStatus');
    
    // Working days from PHP (already defined)
    const workingDays = @json($workingDaysArray);
    const defaultCapacities = @json($defaultCapacities);
    
    // Function to update capacity fields based on day type
    function updateCapacityFields() {
        const dayType = dayTypeSelect.value;
        
        if (dayType === 'holiday') {
            capacityInputs.forEach(input => {
                input.readOnly = true;
                input.value = 0;
                input.style.backgroundColor = '#f0f0f0';
            });
        } else if (dayType === 'half_day') {
            capacityInputs.forEach(input => {
                input.readOnly = false;
                input.style.backgroundColor = '#fff3e0';
                // Set to half of default
                if (input.id === 'reg_capacity') input.value = Math.floor(defaultCapacities.reg / 2);
                if (input.id === 'updating_capacity') input.value = Math.floor(defaultCapacities.updating / 2);
                if (input.id === 'inquiry_capacity') input.value = Math.floor(defaultCapacities.inquiry / 2);
            });
        } else {
            capacityInputs.forEach(input => {
                input.readOnly = false;
                input.style.backgroundColor = '';
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
        const dayOfWeek = selectedDate.getDay();
        let dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
        
        const isWorking = workingDays.includes(dayNumber);
        
        if (!isWorking) {
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const warningMessage = `⚠️ Warning: ${dayNames[dayOfWeek]} is currently set as a NON-WORKING DAY in system settings.\n\nWould you like to continue creating this slot?`;
            
            if (!confirm(warningMessage)) {
                dateInput.value = '';
                return false;
            }
        }
        return true;
    }
    
    // Function to check if time slot already exists
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
    
    // Day type change handler
    dayTypeSelect?.addEventListener('change', function() {
        if (this.value === 'half_day') {
            alert('Note: Half day means only 50% of the configured capacity will be available for booking.');
        }
        updateCapacityFields();
    });
    
    // Date change handler
    dateInput?.addEventListener('change', function() {
        checkWorkingDay();
        checkExistingTimeSlot();
    });
    
    // Time slot change handler
    timeSlotSelect?.addEventListener('change', function() {
        checkExistingTimeSlot();
        
        // Load default capacities for this time slot
        if (this.value) {
            fetch(`/admin/slots/get-default-capacities?time_slot_id=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && dayTypeSelect.value === 'working') {
                        regCapacity.value = data.reg_capacity || defaultCapacities.reg;
                        updatingCapacity.value = data.updating_capacity || defaultCapacities.updating;
                        inquiryCapacity.value = data.inquiry_capacity || defaultCapacities.inquiry;
                    }
                })
                .catch(error => console.error('Error loading capacities:', error));
        }
    });
    
    // Form submission via AJAX
    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate
        if (!dateInput.value) {
            alert('Please select a date.');
            return false;
        }
        
        if (!timeSlotSelect.value) {
            alert('Please select a time slot.');
            return false;
        }
        
        // Check if time slot exists
        const exists = await checkExistingTimeSlot();
        if (!exists) return false;
        
        // Validate capacities
        const dayType = dayTypeSelect.value;
        if (dayType !== 'holiday') {
            const reg = parseInt(regCapacity.value) || 0;
            const updating = parseInt(updatingCapacity.value) || 0;
            const inquiry = parseInt(inquiryCapacity.value) || 0;
            
            if (reg === 0 && updating === 0 && inquiry === 0) {
                const confirmSubmit = confirm('All capacities are set to 0. No appointments can be booked for this slot. Continue?');
                if (!confirmSubmit) return false;
            }
        }
        
        // Submit via AJAX
        const formData = new FormData(form);
        
        // Explicitly add capacity values to ensure they're included
        formData.set('reg_capacity', regCapacity.value || 0);
        formData.set('updating_capacity', updatingCapacity.value || 0);
        formData.set('inquiry_capacity', inquiryCapacity.value || 0);
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        submitBtn.disabled = true;
        formStatus.style.display = 'inline';
        formStatus.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Creating slot...';
        formStatus.style.color = '#0d6efd';
        
        try {
            const response = await fetch('{{ route("admin.slots.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                formStatus.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                formStatus.style.color = '#198754';
                setTimeout(() => {
                    window.location.href = '{{ route("admin.slots.index") }}';
                }, 1500);
            } else {
                formStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                formStatus.style.color = '#dc3545';
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                setTimeout(() => formStatus.style.display = 'none', 3000);
            }
        } catch (error) {
            formStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error creating slot';
            formStatus.style.color = '#dc3545';
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            setTimeout(() => formStatus.style.display = 'none', 3000);
        }
    });
    
    // Initialize
    updateCapacityFields();
</script>
@endpush
@endsection