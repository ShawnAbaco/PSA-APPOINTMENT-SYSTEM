@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Appointment Slot</h1>
        <a href="{{ route('admin.slots.index') }}" class="btn btn-secondary">Back</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            @php
                use App\Models\WorkingDaysDefault;
                use App\Models\TimeSlot;
                use App\Models\SlotCapacityOverride;
                
                // Get the override for this slot
                $override = $override ?? null;
                
                // Get working days from working_days_defaults table
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
                
                // Calculate booked counts
                use App\Models\AppointmentClient;
                $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($slot) {
                    $query->whereDate('appointment_date', $slot->date)
                          ->where('time_slot_id', $slot->time_slot_id)
                          ->where('status', 'confirmed');
                })
                ->selectRaw('service, COUNT(*) as count')
                ->groupBy('service')
                ->pluck('count', 'service')
                ->toArray();
                
                $totalBooked = ($bookedCounts['reg'] ?? 0) + ($bookedCounts['updating'] ?? 0) + ($bookedCounts['inquiry'] ?? 0);
                $regCapacity = $override->reg_capacity ?? 0;
                $updatingCapacity = $override->updating_capacity ?? 0;
                $inquiryCapacity = $override->inquiry_capacity ?? 0;
            @endphp
            
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i> 
                <strong>Date:</strong> {{ date('F d, Y (l)', strtotime($slot->date)) }}<br>
                <strong>Currently Booked:</strong> {{ $totalBooked }} clients<br>
                <strong>Working Days:</strong> {{ $workingDaysText }}<br>
                <small>Only these days are available for booking. You can change this in <a href="{{ route('admin.settings.index') }}">System Settings</a>.</small>
            </div>
            
            <form id="editSlotForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Time Slot *</label>
                        <select name="time_slot_id" id="time_slot_id" class="form-control" required>
                            <option value="">Select Time Slot</option>
                            @foreach($timeSlots as $timeSlot)
                                <option value="{{ $timeSlot->id }}" {{ $slot->time_slot_id == $timeSlot->id ? 'selected' : '' }}>
                                    {{ $timeSlot->label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the time slot for this appointment</small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Day Type *</label>
                        <select name="day_type" id="day_type" class="form-control" required>
                            <option value="working" {{ $slot->day_type == 'working' ? 'selected' : '' }}>Working Day (Full Capacity)</option>
                            <option value="half_day" {{ $slot->day_type == 'half_day' ? 'selected' : '' }}>Half Day (50% Capacity)</option>
                            <option value="holiday" {{ $slot->day_type == 'holiday' ? 'selected' : '' }}>Holiday (No Appointments)</option>
                            <option value="special" {{ $slot->day_type == 'special' ? 'selected' : '' }}>Special Day (Custom)</option>
                        </select>
                        <small class="text-muted">
                            <strong>Working:</strong> Full capacity available<br>
                            <strong>Half Day:</strong> Only 50% of capacity available<br>
                            <strong>Holiday:</strong> No appointments allowed<br>
                            <strong>Special:</strong> Custom configuration with notes
                        </small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date</label>
                        <input type="text" class="form-control" value="{{ date('F d, Y', strtotime($slot->date)) }}" disabled>
                        <small class="text-muted">Date cannot be changed</small>
                    </div>
                </div>
                
                <div class="row" id="capacityFields">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Registration (R) Capacity *</label>
                        <input type="number" name="reg_capacity" id="reg_capacity" class="form-control capacity-input" value="{{ $regCapacity }}" min="0" max="100" required>
                        <small class="text-muted">
                            Booked: {{ $bookedCounts['reg'] ?? 0 }} | Available: {{ max(0, $regCapacity - ($bookedCounts['reg'] ?? 0)) }}
                        </small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Updating (U) Capacity *</label>
                        <input type="number" name="updating_capacity" id="updating_capacity" class="form-control capacity-input" value="{{ $updatingCapacity }}" min="0" max="100" required>
                        <small class="text-muted">
                            Booked: {{ $bookedCounts['updating'] ?? 0 }} | Available: {{ max(0, $updatingCapacity - ($bookedCounts['updating'] ?? 0)) }}
                        </small>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Inquiry (S) Capacity *</label>
                        <input type="number" name="inquiry_capacity" id="inquiry_capacity" class="form-control capacity-input" value="{{ $inquiryCapacity }}" min="0" max="100" required>
                        <small class="text-muted">
                            Booked: {{ $bookedCounts['inquiry'] ?? 0 }} | Available: {{ max(0, $inquiryCapacity - ($bookedCounts['inquiry'] ?? 0)) }}
                        </small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this slot">{{ $slot->notes }}</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Slot</button>
                <button type="button" class="btn btn-danger" id="deleteSlotBtn" data-id="{{ $slot->id }}">Delete Slot</button>
                <span id="formStatus" style="margin-left: 10px; display: none;"></span>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dayTypeSelect = document.getElementById('day_type');
    const capacityInputs = document.querySelectorAll('.capacity-input');
    const regCapacity = document.getElementById('reg_capacity');
    const updatingCapacity = document.getElementById('updating_capacity');
    const inquiryCapacity = document.getElementById('inquiry_capacity');
    const form = document.getElementById('editSlotForm');
    const formStatus = document.getElementById('formStatus');
    const deleteBtn = document.getElementById('deleteSlotBtn');
    const slotId = deleteBtn?.dataset.id;
    
    const workingDays = @json($workingDaysArray);
    const currentBooked = {
        reg: {{ $bookedCounts['reg'] ?? 0 }},
        updating: {{ $bookedCounts['updating'] ?? 0 }},
        inquiry: {{ $bookedCounts['inquiry'] ?? 0 }}
    };
    
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
            });
        } else {
            capacityInputs.forEach(input => {
                input.readOnly = false;
                input.style.backgroundColor = '';
            });
        }
    }
    
    // Check if capacity can be reduced below booked count
    function validateCapacityReduction() {
        const newReg = parseInt(regCapacity.value) || 0;
        const newUpdating = parseInt(updatingCapacity.value) || 0;
        const newInquiry = parseInt(inquiryCapacity.value) || 0;
        
        if (newReg < currentBooked.reg) {
            alert(`Cannot reduce Registration capacity below ${currentBooked.reg} (already booked).`);
            regCapacity.value = currentBooked.reg;
            return false;
        }
        if (newUpdating < currentBooked.updating) {
            alert(`Cannot reduce Updating capacity below ${currentBooked.updating} (already booked).`);
            updatingCapacity.value = currentBooked.updating;
            return false;
        }
        if (newInquiry < currentBooked.inquiry) {
            alert(`Cannot reduce Inquiry capacity below ${currentBooked.inquiry} (already booked).`);
            inquiryCapacity.value = currentBooked.inquiry;
            return false;
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
    
    // Capacity change validation
    regCapacity?.addEventListener('change', validateCapacityReduction);
    updatingCapacity?.addEventListener('change', validateCapacityReduction);
    inquiryCapacity?.addEventListener('change', validateCapacityReduction);
    
    // Form submission via AJAX
    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateCapacityReduction()) return false;
        
        const formData = new FormData(form);
        
        // Explicitly add capacity values to ensure they're included
        formData.set('reg_capacity', regCapacity.value || 0);
        formData.set('updating_capacity', updatingCapacity.value || 0);
        formData.set('inquiry_capacity', inquiryCapacity.value || 0);
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        submitBtn.disabled = true;
        formStatus.style.display = 'inline';
        formStatus.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Updating slot...';
        formStatus.style.color = '#0d6efd';
        
        try {
            const response = await fetch('{{ route("admin.slots.update", $slot->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success || data.message?.includes('success')) {
                formStatus.innerHTML = '<i class="fas fa-check-circle"></i> Slot updated successfully!';
                formStatus.style.color = '#198754';
                setTimeout(() => {
                    window.location.href = '{{ route("admin.slots.index") }}';
                }, 1500);
            } else {
                formStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Error updating slot');
                formStatus.style.color = '#dc3545';
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                setTimeout(() => formStatus.style.display = 'none', 3000);
            }
        } catch (error) {
            formStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error updating slot';
            formStatus.style.color = '#dc3545';
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            setTimeout(() => formStatus.style.display = 'none', 3000);
        }
    });
    
    // Delete slot
    deleteBtn?.addEventListener('click', async function() {
        if (!confirm('Are you sure you want to delete this slot? This action cannot be undone.')) return;
        
        if (currentBooked.reg > 0 || currentBooked.updating > 0 || currentBooked.inquiry > 0) {
            alert('Cannot delete slot with existing appointments.');
            return;
        }
        
        const deleteBtn = this;
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        deleteBtn.disabled = true;
        
        try {
            const response = await fetch(`/admin/slots/${slotId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Slot deleted successfully!');
                window.location.href = '{{ route("admin.slots.index") }}';
            } else {
                alert(data.message || 'Error deleting slot');
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            }
        } catch (error) {
            alert('Error deleting slot');
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    });
    
    // Check if date is working day
    const slotDate = new Date('{{ $slot->date }}');
    const dayOfWeek = slotDate.getDay();
    const dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
    
    if (!workingDays.includes(dayNumber)) {
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        setTimeout(() => {
            alert(`Note: ${dayNames[dayOfWeek]} is currently set as a non-working day in system settings. Clients will not see this date in the calendar unless you change the working days configuration.`);
        }, 500);
    }
    
    // Initialize
    updateCapacityFields();
</script>
@endpush
@endsection