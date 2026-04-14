@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">Appointment Calendar</h1>
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                Calendar view coming soon. Total appointments: {{ $appointments->count() }}
            </div>
        </div>
    </div>
</div>
@endsection