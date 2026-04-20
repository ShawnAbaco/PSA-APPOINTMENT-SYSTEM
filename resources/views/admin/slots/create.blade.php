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
                $workingDays = \App\Models\Setting::where('key', 'working_days')->first();
                $workingDaysArray = $workingDays ? explode(',', $workingDays->value) : ['1','2','3','4','5'];
                $dayNames = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday'];
                $workingDaysList = [];
                foreach ($workingDaysArray as $day) {
                    $workingDaysList[] = $dayNames[$day];
                }
                $workingDaysText = implode(', ', $workingDaysList);
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
                        <label class="form-label">Total Capacity *</label>
                        <input type="number" name="total_capacity" class="form-control" value="20" min="0" max="100" required>
                        <small class="text-muted">Maximum number of clients for this day</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Day Type *</label>
                        <select name="day_type" class="form-control" required>
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
    // Show warning when selecting a non-working day
    const dateInput = document.getElementById('date');
    const workingDays = @json($workingDaysArray);
    
    dateInput?.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const dayOfWeek = selectedDate.getDay(); // 0=Sun, 1=Mon, 6=Sat
        let dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
        
        if (!workingDays.includes(dayNumber.toString())) {
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            alert(`Warning: ${dayNames[dayOfWeek]} is currently set as a non-working day in system settings. Clients will not see this date in the calendar unless you change the working days configuration.`);
        }
    });
</script>
@endpush
@endsection