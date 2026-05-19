@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control" value="{{ $user->first_name }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-muted">Only fill if you want to change the password</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User (No Employee ID)</option>
                            <option value="operator" {{ $user->role == 'operator' ? 'selected' : '' }}>Operator (Auto-generates PSA-OPERATOR-XXX)</option>
                            <option value="staff" {{ $user->role == 'staff' ? 'selected' : '' }}>Staff (Auto-generates PSA-STAFF-XXX)</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin (Auto-generates PSA-ADMIN-XXX)</option>
                        </select>
                        <small class="text-muted">
                            @if($user->employee_id)
                                Current Employee ID: <strong>{{ $user->employee_id }}</strong>
                                @if($user->role != 'user')
                                    (Will be regenerated if role changes)
                                @endif
                            @else
                                No Employee ID assigned (User role)
                            @endif
                        </small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ $user->contact_number }}">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="d-block">
                        <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}> Active Account
                    </label>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> Employee ID is automatically generated based on role and cannot be manually edited.
                    It will follow the format: <strong>PSA-{ROLE}-XXX</strong> (e.g., PSA-ADMIN-001, PSA-STAFF-002, PSA-OPERATOR-003)
                </div>
                
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        </div>
    </div>
</div>
@endsection