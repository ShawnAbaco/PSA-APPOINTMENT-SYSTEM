@extends('layouts.operator')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-4">
            <h1 class="h3">Appointment Details</h1>
            <div>
                @if ($appointment->status == 'pending')
                    <button class="btn btn-success confirm-btn" data-id="{{ $appointment->id }}">Confirm</button>
                @endif
                @if ($appointment->status == 'confirmed')
                    <button class="btn btn-info complete-btn" data-id="{{ $appointment->id }}">Mark Complete</button>
                @endif
                @if (in_array($appointment->status, ['pending', 'confirmed']))
                    <button class="btn btn-danger cancel-btn" data-id="{{ $appointment->id }}">Cancel</button>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Appointment Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Appointment #:</strong> {{ $appointment->appointment_number }}</p>
                        <p><strong>Date:</strong> {{ date('F d, Y', strtotime($appointment->appointment_date)) }}</p>
                        <p><strong>Type:</strong> {{ ucfirst($appointment->type) }}</p>
                        <p><strong>Status:</strong> <span
                                class="status-badge status-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                        </p>
                        <p><strong>Reference Code:</strong> <code>{{ $appointment->reference_code }}</code></p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Contact Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $appointment->contact_name }}</p>
                        <p><strong>Email:</strong> {{ $appointment->contact_email ?? 'N/A' }}</p>
                        <p><strong>Mobile:</strong> {{ $appointment->contact_mobile }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Clients</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Sex</th>
                            <th>Birthdate</th>
                            <th>Service</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appointment->clients as $index => $client)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}
                                    {{ $client->suffix }}</td>
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

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('.confirm-btn').click(function() {
                    let id = $(this).data('id');
                    $.ajax({
                        url: `/operator/appointments/${id}/confirm`,
                        method: 'PUT',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            location.reload();
                        }
                    });
                });

                $('.complete-btn').click(function() {
                    let id = $(this).data('id');
                    $.ajax({
                        url: `/operator/appointments/${id}/complete`,
                        method: 'PUT',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            location.reload();
                        }
                    });
                });

                $('.cancel-btn').click(function() {
                    if (confirm('Cancel this appointment?')) {
                        let id = $(this).data('id');
                        $.ajax({
                            url: `/operator/appointments/${id}/cancel`,
                            method: 'PUT',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                location.reload();
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
@endsection
