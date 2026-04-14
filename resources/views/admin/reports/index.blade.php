@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Reports</h1>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Appointment Summary</h5>
                </div>
                <div class="card-body">
                    <canvas id="appointmentChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Services Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="servicesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Filter Reports</h5>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate ?? date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate ?? date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx1 = document.getElementById('appointmentChart').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
        datasets: [{
            label: 'Appointments',
            data: [{{ $pendingAppointments ?? 0 }}, {{ $confirmedAppointments ?? 0 }}, {{ $completedAppointments ?? 0 }}, {{ $cancelledAppointments ?? 0 }}],
            backgroundColor: ['#ffc107', '#28a745', '#17a2b8', '#dc3545']
        }]
    }
});

const ctx2 = document.getElementById('servicesChart').getContext('2d');
new Chart(ctx2, {
    type: 'pie',
    data: {
        labels: {!! json_encode($serviceLabels ?? []) !!},
        datasets: [{
            data: {!! json_encode($serviceData ?? []) !!},
            backgroundColor: ['#667eea', '#764ba2', '#28a745', '#17a2b8']
        }]
    }
});
</script>
@endpush
@endsection