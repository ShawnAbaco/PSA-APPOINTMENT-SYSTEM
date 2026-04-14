@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Staff Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div><h6 class="text-muted">Today's Appointments</h6><h2>{{ $todayAppointments ?? 0 }}</h2></div>
                    <i class="fas fa-calendar-day fa-2x text-primary"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div><h6 class="text-muted">Pending</h6><h2 class="text-warning">{{ $pendingAppointments ?? 0 }}</h2></div>
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div><h6 class="text-muted">Confirmed</h6><h2 class="text-success">{{ $confirmedAppointments ?? 0 }}</h2></div>
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between">
                    <div><h6 class="text-muted">Completed</h6><h2 class="text-info">{{ $completedAppointments ?? 0 }}</h2></div>
                    <i class="fas fa-check-double fa-2x text-info"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Recent Appointments</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Appointment #</th><th>Date</th><th>Client(s)</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($recentAppointments ?? [] as $appointment)
                        <tr>
                            <td>{{ $appointment->appointment_number }}</td>
                            <td>{{ date('M d, Y', strtotime($appointment->appointment_date)) }}</td>
                            <td>{{ $appointment->clients->count() }} person(s)</td>
                            <td><span class="status-badge status-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                            <td><a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">No appointments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection