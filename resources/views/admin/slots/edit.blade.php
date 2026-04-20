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
                <strong>Date:</strong> {{ date('F d, Y (l)', strtotime($slot->date)) }}<br>
                <strong>Currently Booked:</strong> {{ $slot->booked_count }} clients<br>
                <strong>Working Days:</strong> {{ $workingDaysText ?: 'Monday to Friday' }}<br>
                <small>Only these days are available for booking. You can change this in <a href="{{ route('admin.settings.index') }}">System Settings</a>.</small>
            </div>
            
            <form method="POST" action="{{ route('admin.slots.update', $slot->id) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Capacity *</label>
                        <input type="number" name="total_capacity" class="form-control" value="{{ $slot->total_capacity }}" min="0" max="100" required>
                        <small class="text-muted">
                            Current capacity: {{ $slot->total_capacity }} | Booked: {{ $slot->booked_count }} | 
                            Available: {{ $slot->total_capacity - $slot->booked_count }}
                        </small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Day Type *</label>
                        <select name="day_type" class="form-control" required>
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
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this slot">{{ $slot->notes }}</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Slot</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const dayOfWeek = new Date('{{ $slot->date }}').getDay();
    const dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
    const workingDays = @json($workingDaysArray);
    
    if (!workingDays.includes(dayNumber.toString())) {
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        setTimeout(() => {
            alert(`Note: ${dayNames[dayOfWeek]} is currently set as a non-working day in system settings. Clients will not see this date in the calendar unless you change the working days configuration.`);
        }, 500);
    }
</script>
@endpush
@endsection