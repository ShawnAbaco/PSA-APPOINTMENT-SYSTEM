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
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                            <option value="staff" {{ $user->role == 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Employee ID</label>
                        <input type="text" name="employee_id" class="form-control" value="{{ $user->employee_id }}">
                    </div>
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
                
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        </div>
    </div>
</div>
@endsection