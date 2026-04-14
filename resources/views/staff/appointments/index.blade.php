@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h1 class="h3">Appointments List</h1>
        <a href="{{ route('staff.appointments.create') }}" class="btn btn-primary">New Appointment</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Appointment #</th><th>Date</th><th>Contact Person</th><th>Clients</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->appointment_number }}</td>
                            <td>{{ date('M d, Y', strtotime($appointment->appointment_date)) }}</td>
                            <td>{{ $appointment->contact_name }}</td>
                            <td>{{ $appointment->clients->count() }}</td>
                            <td><span class="status-badge status-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                            <td><a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">No appointments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $appointments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection