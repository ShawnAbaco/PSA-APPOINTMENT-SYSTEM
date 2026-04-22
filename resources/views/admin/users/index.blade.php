@extends('layouts.admin')

@section('content')
    <div class="user-management-wrapper">
        <!-- Header Section -->
        <div class="user-welcome-box">
            <div>
                <h1 class="user-main-title">User Management</h1>
                <p class="user-subtitle">Manage system users, roles, and account approvals</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="user-primary-btn">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
        </div>

        <!-- Pending Accounts Alert -->
        @php
            $pendingCount = App\Models\User::where('account_status', 'pending')->count();
        @endphp

        @if ($pendingCount > 0)
            <div class="user-notice user-notice-warning">
                <div class="user-notice-content">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>{{ $pendingCount }} pending account(s)</strong> waiting for approval.
                        <a href="#pending-accounts" class="user-notice-link">Click here to review them</a>
                    </div>
                    <button type="button" class="user-notice-close" data-bs-dismiss="alert">×</button>
                </div>
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="user-tab-navigation">
            <button class="user-tab-btn active" data-tab="all-users">
                <i class="fas fa-users"></i> All Users
            </button>
            <button class="user-tab-btn" data-tab="pending">
                <i class="fas fa-clock"></i> Pending Approvals
                @if ($pendingCount > 0)
                    <span class="user-tab-badge">{{ $pendingCount }}</span>
                @endif
            </button>
        </div>

        <!-- All Users Tab Content -->
        <div class="user-tab-panel active" id="all-users-panel">
            <div class="user-data-card">
                <div class="user-card-content">
                    <div class="user-table-wrapper">
                        <table class="user-data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Account Status</th>
                                    <th>Active Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr class="user-table-row">
                                        <td data-label="ID">{{ $user->id }}</td>
                                        <td data-label="Employee ID">{{ $user->employee_id ?? 'N/A' }}</td>
                                        <td data-label="Name">{{ $user->first_name }} {{ $user->last_name }}</td>
                                        <td data-label="Username">{{ $user->username }}</td>
                                        <td data-label="Email">{{ $user->email }}</td>
                                        <td data-label="Role">
                                            <span class="user-role-badge user-role-{{ $user->role }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td data-label="Account Status">
                                            @if ($user->account_status == 'approved')
                                                <span class="user-status-badge user-status-approved">Approved</span>
                                            @elseif($user->account_status == 'pending')
                                                <span class="user-status-badge user-status-pending">Pending</span>
                                            @elseif($user->account_status == 'rejected')
                                                <span class="user-status-badge user-status-rejected">Rejected</span>
                                            @endif
                                        </td>
                                        <td data-label="Active Status">
                                            @if ($user->is_active)
                                                <span class="user-active-badge user-active-yes">Active</span>
                                            @else
                                                <span class="user-active-badge user-active-no">Inactive</span>
                                            @endif
                                        </td>
                                        <td data-label="Last Login">
                                            {{ $user->last_login_at ? date('M d, Y', strtotime($user->last_login_at)) : 'Never' }}
                                        </td>
                                        <td data-label="Actions">
                                            <div class="user-action-group">
                                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                                    class="user-action-btn user-action-edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="user-action-btn user-action-delete delete-user"
                                                    data-id="{{ $user->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                <button class="user-action-btn user-action-toggle toggle-status"
                                                    data-id="{{ $user->id }}">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="user-empty-state">
                                            <i class="fas fa-user-slash"></i>
                                            <p>No users found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="user-pagination-wrapper">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Accounts Tab Content -->
        <div class="user-tab-panel" id="pending-panel">
            <div class="user-data-card">
                <div class="user-card-header">
                    <h5 class="user-header-title">
                        <i class="fas fa-clock"></i> Accounts Waiting for Approval
                    </h5>
                </div>
                <div class="user-card-content">
                    <div class="user-table-wrapper">
                        <table class="user-data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Position</th>
                                    <th>Employee ID</th>
                                    <th>Registered Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $pendingUsers = App\Models\User::where('account_status', 'pending')
                                        ->orderBy('created_at', 'asc')
                                        ->get();
                                @endphp
                                @forelse($pendingUsers as $pendingUser)
                                    <tr class="user-table-row">
                                        <td data-label="ID">{{ $pendingUser->id }}</td>
                                        <td data-label="Name">{{ $pendingUser->first_name }} {{ $pendingUser->last_name }}
                                        </td>
                                        <td data-label="Username">{{ $pendingUser->username }}</td>
                                        <td data-label="Position">{{ $pendingUser->position ?? 'N/A' }}</td>
                                        <td data-label="Employee ID">{{ $pendingUser->employee_id ?? 'N/A' }}</td>
                                        <td data-label="Registered Date">
                                            {{ $pendingUser->created_at->format('M d, Y h:i A') }}</td>
                                        <td data-label="Actions">
                                            <div class="user-action-group">
                                                <button type="button"
                                                    class="user-action-btn user-action-approve approve-btn"
                                                    data-id="{{ $pendingUser->id }}"
                                                    data-name="{{ $pendingUser->first_name }} {{ $pendingUser->last_name }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="user-action-btn user-action-reject reject-btn"
                                                    data-id="{{ $pendingUser->id }}"
                                                    data-name="{{ $pendingUser->first_name }} {{ $pendingUser->last_name }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="user-empty-state">
                                            <i class="fas fa-check-circle"></i>
                                            <p>No pending accounts found. All accounts have been reviewed.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="user-modal-overlay" id="rejectModal">
        <div class="user-modal-container">
            <div class="user-modal-window">
                <div class="user-modal-header user-modal-header-danger">
                    <h5 class="user-modal-title">
                        <i class="fas fa-times-circle"></i> Reject Account
                    </h5>
                    <button type="button" class="user-modal-close" data-modal-close>×</button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="user-modal-body">
                        <p>Are you sure you want to reject <strong id="rejectUserName"></strong>'s account?</p>
                        <div class="user-form-group">
                            <label for="rejection_reason" class="user-form-label">
                                Rejection Reason <span class="user-required">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" class="user-form-textarea" rows="3"
                                placeholder="Please provide a reason for rejection..." required></textarea>
                        </div>
                    </div>
                    <div class="user-modal-footer">
                        <button type="button" class="user-secondary-btn" data-modal-close>Cancel</button>
                        <button type="submit" class="user-danger-btn">Reject Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Tab switching functionality
            document.querySelectorAll('.user-tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.dataset.tab;

                    // Update active tab button
                    document.querySelectorAll('.user-tab-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    // Update active panel
                    document.querySelectorAll('.user-tab-panel').forEach(panel => panel.classList.remove(
                        'active'));
                    document.getElementById(`${tabId}-panel`).classList.add('active');
                });
            });

            // Modal functionality
            const modal = document.getElementById('rejectModal');
            const modalCloseBtns = document.querySelectorAll('[data-modal-close]');

            function closeModal() {
                modal.classList.remove('active');
            }

            function openModal() {
                modal.classList.add('active');
            }

            modalCloseBtns.forEach(btn => {
                btn.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });

            // Delete user
            document.querySelectorAll('.delete-user').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this user?')) {
                        let id = this.dataset.id;
                        fetch(`/admin/users/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(() => location.reload());
                    }
                });
            });

            // Toggle status
            document.querySelectorAll('.toggle-status').forEach(btn => {
                btn.addEventListener('click', function() {
                    let id = this.dataset.id;
                    fetch(`/admin/users/${id}/toggle-status`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => location.reload());
                });
            });

            // Approve account
            document.querySelectorAll('.approve-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    let userId = this.dataset.id;
                    let userName = this.dataset.name;

                    if (confirm(`Are you sure you want to approve ${userName}'s account?`)) {
                        fetch(`/admin/users/${userId}/approve`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            }).then(response => response.json())
                            .then(() => location.reload())
                            .catch(() => alert('Error approving account. Please try again.'));
                    }
                });
            });

            // Reject account - show modal
            document.querySelectorAll('.reject-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    let userId = this.dataset.id;
                    let userName = this.dataset.name;

                    document.getElementById('rejectUserName').textContent = userName;
                    document.getElementById('rejectForm').action = `/admin/users/${userId}/reject`;
                    openModal();
                });
            });
        </script>
    @endpush
@endsection
