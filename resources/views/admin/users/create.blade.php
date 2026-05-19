@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Create New User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="user">User (No Employee ID)</option>
                            <option value="operator">Operator (Auto-generates PSA-OPERATOR-XXX)</option>
                            <option value="staff">Staff (Auto-generates PSA-STAFF-XXX)</option>
                            <option value="admin">Admin (Auto-generates PSA-ADMIN-XXX)</option>
                        </select>
                        <small class="text-muted">Employee ID will be automatically generated based on the selected role</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="d-block">
                        <input type="checkbox" name="is_active" value="1" checked> Active Account
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Create User</button>
            </form>
        </div>
    </div>
</div>
@endsection