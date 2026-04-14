@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Client Details</h1>
        <div>
            <a href="{{ route('staff.clients.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Clients
            </a>
            @if(!$client->is_verified)
                <button class="btn btn-success verify-client" data-id="{{ $client->id }}">
                    <i class="fas fa-check-circle"></i> Verify Client
                </button>
            @endif
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Personal Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 40%">Full Name:</th>
                            <td><strong>{{ $client->full_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>First Name:</th>
                            <td>{{ $client->first_name }}</td>
                        </tr>
                        <tr>
                            <th>Middle Name:</th>
                            <td>{{ $client->middle_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Last Name:</th>
                            <td>{{ $client->last_name }}</td>
                        </tr>
                        @if($client->suffix)
                        <tr>
                            <th>Suffix:</th>
                            <td>{{ $client->suffix }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Sex:</th>
                            <td>{{ $client->sex }}</td>
                        </tr>
                        <tr>
                            <th>Birthdate:</th>
                            <td>{{ date('F d, Y', strtotime($client->birthdate)) }}</td>
                        </tr>
                        <tr>
                            <th>Age:</th>
                            <td>{{ \Carbon\Carbon::parse($client->birthdate)->age }} years old</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Appointment & Service Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Service:</th>
                            <td>
                                <span class="badge bg-primary">{{ $services[$client->service] ?? $client->service }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Appointment:</th>
                            <td>
                                @if($client->appointment)
                                    <a href="{{ route('staff.appointments.show', $client->appointment->id) }}">
                                        {{ $client->appointment->appointment_number }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ date('F d, Y', strtotime($client->appointment->appointment_date)) }}</small>
                                @else
                                    <span class="text-muted">No appointment found</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Verification Status:</th>
                            <td>
                                @if($client->is_verified)
                                    <span class="badge bg-success">Verified</span>
                                    <br>
                                    <small>Verified on: {{ date('F d, Y h:i A', strtotime($client->verified_at)) }}</small>
                                @else
                                    <span class="badge bg-warning">Pending Verification</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>PSA Reference Number (TRN):</th>
                            <td>
                                @if($client->psa_reference_number)
                                    <code>{{ $client->psa_reference_number }}</code>
                                @else
                                    <span class="text-muted">Not yet assigned</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    
                    @if(!$client->psa_reference_number)
                    <div class="mt-3">
                        <label class="form-label">Assign TRN/Reference Number</label>
                        <div class="input-group">
                            <input type="text" id="referenceNumber" class="form-control" placeholder="Enter TRN...">
                            <button class="btn btn-primary" id="updateReferenceBtn" data-id="{{ $client->id }}">
                                Update
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if($clientHistory->count() > 1)
    <div class="card">
        <div class="card-header">
            <h5>Appointment History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Appointment #</th>
                            <th>Date</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientHistory as $history)
                        <tr>
                            <td>{{ $history->appointment?->appointment_number ?? 'N/A' }}</td>
                            <td>{{ $history->appointment?->appointment_date ? date('M d, Y', strtotime($history->appointment->appointment_date)) : 'N/A' }}</td>
                            <td>{{ $services[$history->service] ?? $history->service }}</td>
                            <td>
                                <span class="badge bg-{{ $history->appointment?->status === 'confirmed' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($history->appointment?->status ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                @if($history->appointment)
                                    <a href="{{ route('staff.appointments.show', $history->appointment->id) }}" class="btn btn-sm btn-info">
                                        View Appointment
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.verify-client').click(function() {
        if(confirm('Verify this client? This will confirm their appointment as well.')) {
            let id = $(this).data('id');
            $.ajax({
                url: `/staff/clients/${id}/verify`,
                method: 'PUT',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    location.reload();
                }
            });
        }
    });
    
    $('#updateReferenceBtn').click(function() {
        let id = $(this).data('id');
        let reference = $('#referenceNumber').val();
        
        if(!reference) {
            alert('Please enter a reference number.');
            return;
        }
        
        $.ajax({
            url: `/staff/clients/${id}/reference`,
            method: 'PUT',
            data: { 
                psa_reference_number: reference,
                _token: '{{ csrf_token() }}' 
            },
            success: function(response) {
                if(response.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>
@endpush
@endsection