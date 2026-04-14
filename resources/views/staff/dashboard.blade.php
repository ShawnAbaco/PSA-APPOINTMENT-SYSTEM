@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Staff Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Appointments</h5>
                    <h2 class="mb-0">{{ $totalAppointments ?? 0 }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Today's Appointments</h5>
                    <h2 class="mb-0">{{ $todayAppointments ?? 0 }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2 class="mb-0">{{ $pendingAppointments ?? 0 }}</h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Confirmed</h5>
                    <h2 class="mb-0">{{ $confirmedAppointments ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Appointments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Appointment #</th>
                                    <th>Date</th>
                                    <th>Client(s)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAppointments ?? [] as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_number ?? 'N/A' }}</td>
                                    <td>{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $appointment->clients->count() ?? 0 }} person(s)</td>
                                    <td>
                                        <span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : ($appointment->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($appointment->status ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No appointments found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection