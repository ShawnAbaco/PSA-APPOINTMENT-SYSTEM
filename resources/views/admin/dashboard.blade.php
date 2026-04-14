@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Admin Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Total Appointments</h6>
                        <h2 class="mb-0">{{ $totalAppointments ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-calendar-check fa-2x text-primary"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Pending</h6>
                        <h2 class="mb-0 text-warning">{{ $pendingAppointments ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-clock fa-2x text-warning"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Confirmed</h6>
                        <h2 class="mb-0 text-success">{{ $confirmedAppointments ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Completed</h6>
                        <h2 class="mb-0 text-info">{{ $completedAppointments ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-check-double fa-2x text-info"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Appointments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Appointment #</th><th>Date</th><th>Clients</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                @forelse($recentAppointments ?? [] as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_number ?? 'N/A' }}</td>
                                    <td>{{ $appointment->appointment_date ? date('M d, Y', strtotime($appointment->appointment_date)) : 'N/A' }}</td>
                                    <td>{{ $appointment->clients->count() ?? 0 }} person(s)</td>
                                    <td><span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : ($appointment->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($appointment->status ?? 'N/A') }}</span></td>
                                    <td><a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">View</a></td>
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
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>System Stats</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            Total Users <span class="badge bg-primary">{{ $totalUsers ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Admin Users <span class="badge bg-danger">{{ $totalAdmins ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Staff Users <span class="badge bg-info">{{ $totalStaff ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Active Staff <span class="badge bg-success">{{ $activeStaff ?? 0 }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection