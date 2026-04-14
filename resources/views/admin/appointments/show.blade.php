@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Appointment Details</h1>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">Back</a>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Appointment Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr><th>Appointment #:</th><td>{{ $appointment->appointment_number }}</td></tr>
                        <tr><th>Date:</th><td>{{ date('F d, Y', strtotime($appointment->appointment_date)) }}</td></tr>
                        <tr><th>Type:</th><td>{{ ucfirst($appointment->type) }}</td></tr>
                        <tr><th>Status:</th><td><span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : 'warning' }}">{{ ucfirst($appointment->status) }}</span></td></tr>
                        <tr><th>Reference Code:</th><td><code>{{ $appointment->reference_code }}</code></td></tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Contact Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr><th>Name:</th><td>{{ $appointment->contact_name }}</td></tr>
                        <tr><th>Email:</th><td>{{ $appointment->contact_email ?? 'N/A' }}</td></tr>
                        <tr><th>Mobile:</th><td>{{ $appointment->contact_mobile }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Clients Information</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>#</th><th>Full Name</th><th>Sex</th><th>Birthdate</th><th>Service</th></tr>
                    </thead>
                    <tbody>
                        @foreach($appointment->clients as $index => $client)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }} {{ $client->suffix }}</td>
                            <td>{{ $client->sex }}</td>
                            <td>{{ date('M d, Y', strtotime($client->birthdate)) }}</td>
                            <td>{{ $client->service_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection