@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">System Settings</h1>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Daily Appointment Capacity</label>
                        <input type="number" name="daily_capacity" class="form-control" value="{{ $settings['daily_capacity'] ?? 20 }}">
                        <small class="text-muted">Maximum appointments per day</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>Advance Booking Days</label>
                        <input type="number" name="advance_booking_days" class="form-control" value="{{ $settings['advance_booking_days'] ?? 30 }}">
                        <small class="text-muted">How many days in advance can users book</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Cancellation Hours</label>
                        <input type="number" name="cancellation_hours" class="form-control" value="{{ $settings['cancellation_hours'] ?? 24 }}">
                        <small class="text-muted">Hours before appointment to allow cancellation</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label>Enable Email Notifications</label>
                        <select name="enable_email" class="form-control">
                            <option value="true" {{ ($settings['enable_email'] ?? 'true') == 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ ($settings['enable_email'] ?? 'true') == 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection