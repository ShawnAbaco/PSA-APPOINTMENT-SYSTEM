@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">User Management</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add New User</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->employee_id ?? 'N/A' }}</td>
                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'staff' ? 'info' : 'secondary') }}">{{ ucfirst($user->role) }}</span></td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $user->last_login_at ? date('M d, Y', strtotime($user->last_login_at)) : 'Never' }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <button class="btn btn-sm btn-danger delete-user" data-id="{{ $user->id }}">Delete</button>
                                <button class="btn btn-sm btn-info toggle-status" data-id="{{ $user->id }}">Toggle Status</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('.delete-user').click(function() {
        if(confirm('Are you sure you want to delete this user?')) {
            let id = $(this).data('id');
            $.ajax({
                url: `/admin/users/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() { location.reload(); }
            });
        }
    });
    
    $('.toggle-status').click(function() {
        let id = $(this).data('id');
        $.ajax({
            url: `/admin/users/${id}/toggle-status`,
            method: 'PUT',
            data: { _token: '{{ csrf_token() }}' },
            success: function() { location.reload(); }
        });
    });
});
</script>
@endpush
@endsection