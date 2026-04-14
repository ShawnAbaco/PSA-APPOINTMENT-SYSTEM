@extends('layouts.staff')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Clients Directory</h1>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Sex</th>
                            <th>Birthdate</th>
                            <th>Service</th>
                            <th>Appointment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                        <tr>
                            <td>{{ $client->id }}</td>
                            <td>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }} {{ $client->suffix }}</td>
                            <td>{{ $client->sex }}</td>
                            <td>{{ date('M d, Y', strtotime($client->birthdate)) }}</td>
                            <td>{{ $client->service_name }}</td>
                            <td>
                                @if($client->appointment)
                                    <a href="{{ route('staff.appointments.show', $client->appointment->id) }}" class="btn btn-sm btn-info">
                                        {{ $client->appointment->appointment_number }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">No clients found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection