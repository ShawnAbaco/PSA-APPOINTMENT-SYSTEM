@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Appointment Calendar</h1>
    
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: [
            @foreach($appointments as $appointment)
            {
                title: '{{ $appointment->appointment_number }} - {{ $appointment->clients->count() }} client(s)',
                start: '{{ $appointment->appointment_date }}',
                color: '{{ $appointment->status === "confirmed" ? "#28a745" : "#ffc107" }}',
                url: '{{ route("admin.appointments.show", $appointment->id) }}'
            },
            @endforeach
        ]
    });
    calendar.render();
});
</script>
@endpush
@endsection