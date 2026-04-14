@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Appointments Management</h1>
        <div>
            <input type="text" id="searchAppointment" class="form-control" placeholder="Search appointments...">
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Appointment #</th>
                            <th>Date</th>
                            <th>Contact Person</th>
                            <th>Clients</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->id }}</td>
                            <td>{{ $appointment->appointment_number }}</td>
                            <td>{{ date('M d, Y', strtotime($appointment->appointment_date)) }}</td>
                            <td>{{ $appointment->contact_name }}</td>
                            <td>{{ $appointment->clients->count() }}</td>
                            <td>
                                <select class="form-select form-select-sm status-select" data-id="{{ $appointment->id }}" style="width: 120px;">
                                    <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-info">View</a>
                                <button class="btn btn-sm btn-danger delete-appointment" data-id="{{ $appointment->id }}">Delete</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center">No appointments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $appointments->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.status-select').change(function() {
        let id = $(this).data('id');
        let status = $(this).val();
        $.ajax({
            url: `/admin/appointments/${id}/status`,
            method: 'PUT',
            data: { status: status, _token: '{{ csrf_token() }}' },
            success: function(response) {
                location.reload();
            }
        });
    });
    
    $('.delete-appointment').click(function() {
        if(confirm('Are you sure?')) {
            let id = $(this).data('id');
            $.ajax({
                url: `/admin/appointments/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    location.reload();
                }
            });
        }
    });
});
</script>
@endpush
@endsection