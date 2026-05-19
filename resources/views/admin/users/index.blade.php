@extends('layouts.admin')

@section('content')
    <div class="user-management-wrapper">
        <!-- Header Section -->
        <div class="user-welcome-box">
            <div>
                <h1 class="user-main-title">User Management</h1>
                <p class="user-subtitle">Manage system users, roles, and account approvals</p>
            </div>

            <div class="date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
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
                    </div>
                    <button type="button" class="user-notice-close" data-bs-dismiss="alert">×</button>
                </div>
            </div>
        @endif

        <!-- Tabs Navigation with Add Button on the Right -->
        <div class="user-tab-navigation">
            <div class="user-tabs-left">
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
            <div class="user-tabs-right">
                <button type="button" class="user-primary-btn" id="createUserBtn">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>
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
                                                <button type="button" class="user-action-btn user-action-edit edit-user"
                                                    data-id="{{ $user->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="user-action-btn user-action-delete delete-user"
                                                    data-id="{{ $user->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                {{-- <button class="user-action-btn user-action-toggle toggle-status"
                                                    data-id="{{ $user->id }}">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button> --}}
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

    <!-- Create/Edit User Modal with Unique Class Names -->
    <div class="psa-user-modal-overlay" id="userFormModal">
        <div class="psa-user-modal-container psa-user-modal-large">
            <div class="psa-user-modal-window">
                <div class="psa-user-modal-header">
                    <h5 class="psa-user-modal-title" id="modalTitle">
                        <i class="fas fa-user-plus"></i> Create New User
                    </h5>
                    <button type="button" class="psa-user-modal-close" data-modal-close>×</button>
                </div>
                <form id="userForm" method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="user_id" id="userId">
                    <div class="psa-user-modal-body">
                        <div class="psa-user-form-row">
                            <div class="psa-user-form-group">
                                <label class="psa-user-form-label">First Name <span
                                        class="psa-user-required">*</span></label>
                                <input type="text" name="first_name" id="first_name" class="psa-user-form-input"
                                    required>
                            </div>
                            <div class="psa-user-form-group">
                                <label class="psa-user-form-label">Last Name <span
                                        class="psa-user-required">*</span></label>
                                <input type="text" name="last_name" id="last_name" class="psa-user-form-input"
                                    required>
                            </div>
                        </div>

                        <div class="psa-user-form-row">
                            <div class="psa-user-form-group">
                                <label class="psa-user-form-label">Username <span
                                        class="psa-user-required">*</span></label>
                                <input type="text" name="username" id="username" class="psa-user-form-input"
                                    required>
                            </div>
                            <div class="psa-user-form-group">
                                <label class="psa-user-form-label">Email <span class="psa-user-required">*</span></label>
                                <input type="email" name="email" id="email" class="psa-user-form-input"
                                    required>
                            </div>
                        </div>

                        <div class="psa-user-form-row">
                            <div class="psa-user-form-group">
                                <label class="psa-user-form-label" id="passwordLabel">Password <span
                                        class="psa-user-required" id="passwordRequired">*</span></label>
                                <input type="password" name="password" id="password" class="psa-user-form-input">
                                <small class="psa-user-form-help" id="passwordHelp">Leave blank to keep current
                                    password</small>
                            </div>
                            <div class="psa-user-form-group">
                                <label class="psa-user-form-label">Role <span class="psa-user-required">*</span></label>
                                <select name="role" id="role" class="psa-user-form-select" required>
                                    <option value="user">User</option>
                                    <option value="operator">Operator</option>
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>

                        <div class="psa-user-form-row">
                            <div class="psa-user-form-group">
                                <label class="psa-user-form-label">Contact Number</label>
                                <input type="text" name="contact_number" id="contact_number"
                                    class="psa-user-form-input">
                            </div>
                            <div class="psa-user-form-group" id="employeeIdDisplay" style="display: none;">
                                <label class="psa-user-form-label">Employee ID</label>
                                <input type="text" id="display_employee_id" class="psa-user-form-input" readonly
                                    disabled>
                            </div>
                        </div>

                        <div class="psa-user-form-row">
                            <div class="psa-user-form-group">
                                <label class="psa-user-checkbox-label">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                                    <span>Active Account</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="psa-user-modal-footer">
                        <button type="button" class="psa-user-secondary-btn" data-modal-close>Cancel</button>
                        <button type="submit" class="psa-user-primary-btn" id="submitBtn">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal with Unique Class Names -->
    <div class="psa-user-modal-overlay" id="rejectModal">
        <div class="psa-user-modal-container">
            <div class="psa-user-modal-window">
                <div class="psa-user-modal-header psa-user-modal-header-danger">
                    <h5 class="psa-user-modal-title">
                        <i class="fas fa-times-circle"></i> Reject Account
                    </h5>
                    <button type="button" class="psa-user-modal-close" data-modal-close>×</button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="psa-user-modal-body">
                        <p>Are you sure you want to reject <strong id="rejectUserName"></strong>'s account?</p>
                        <div class="psa-user-form-group">
                            <label for="rejection_reason" class="psa-user-form-label">
                                Rejection Reason <span class="psa-user-required">*</span>
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" class="psa-user-form-textarea" rows="3"
                                placeholder="Please provide a reason for rejection..." required></textarea>
                        </div>
                    </div>
                    <div class="psa-user-modal-footer">
                        <button type="button" class="psa-user-secondary-btn" data-modal-close>Cancel</button>
                        <button type="submit" class="psa-user-danger-btn">Reject Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Make sure DOM is fully loaded
            document.addEventListener('DOMContentLoaded', function() {
                // Tab switching functionality
                document.querySelectorAll('.user-tab-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const tabId = this.dataset.tab;
                        document.querySelectorAll('.user-tab-btn').forEach(b => b.classList.remove(
                            'active'));
                        this.classList.add('active');
                        document.querySelectorAll('.user-tab-panel').forEach(panel => panel.classList
                            .remove('active'));
                        document.getElementById(`${tabId}-panel`).classList.add('active');
                    });
                });

                // Modal functionality
                const userFormModal = document.getElementById('userFormModal');
                const rejectModal = document.getElementById('rejectModal');
                const modalCloseBtns = document.querySelectorAll('[data-modal-close]');

                function closeModal(modal) {
                    if (modal) modal.classList.remove('active');
                }

                function openModal(modal) {
                    if (modal) modal.classList.add('active');
                }

                modalCloseBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        closeModal(userFormModal);
                        closeModal(rejectModal);
                    });
                });

                if (userFormModal) {
                    userFormModal.addEventListener('click', function(e) {
                        if (e.target === userFormModal) closeModal(userFormModal);
                    });
                }

                if (rejectModal) {
                    rejectModal.addEventListener('click', function(e) {
                        if (e.target === rejectModal) closeModal(rejectModal);
                    });
                }

                // Create User - Open Modal
                const createBtn = document.getElementById('createUserBtn');
                if (createBtn) {
                    createBtn.addEventListener('click', function() {
                        const form = document.getElementById('userForm');
                        if (form) form.reset();

                        const userIdField = document.getElementById('userId');
                        if (userIdField) userIdField.value = '';

                        const formMethod = document.getElementById('formMethod');
                        if (formMethod) formMethod.value = 'POST';

                        const modalTitle = document.getElementById('modalTitle');
                        if (modalTitle) modalTitle.innerHTML =
                            '<i class="fas fa-user-plus"></i> Create New User';

                        const submitBtn = document.getElementById('submitBtn');
                        if (submitBtn) submitBtn.innerHTML = 'Create User';

                        const passwordLabel = document.getElementById('passwordLabel');
                        if (passwordLabel) passwordLabel.innerHTML =
                            'Password <span class="psa-user-required">*</span>';

                        const passwordRequired = document.getElementById('passwordRequired');
                        if (passwordRequired) passwordRequired.style.display = 'inline';

                        const passwordHelp = document.getElementById('passwordHelp');
                        if (passwordHelp) passwordHelp.style.display = 'block';

                        const passwordInput = document.getElementById('password');
                        if (passwordInput) {
                            passwordInput.required = true;
                            passwordInput.value = '';
                        }

                        // Hide employee ID display for create mode
                        const employeeIdDisplay = document.getElementById('employeeIdDisplay');
                        if (employeeIdDisplay) employeeIdDisplay.style.display = 'none';

                        const userForm = document.getElementById('userForm');
                        if (userForm) userForm.action = "{{ route('admin.users.store') }}";

                        openModal(userFormModal);
                    });
                }

                // Edit User - Open Modal with Data
                document.querySelectorAll('.edit-user').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const userId = this.dataset.id;

                        try {
                            const response = await fetch(`/admin/users/${userId}/edit-data`);
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            const user = await response.json();

                            // Set form values
                            document.getElementById('userId').value = user.id;
                            document.getElementById('first_name').value = user.first_name || '';
                            document.getElementById('last_name').value = user.last_name || '';
                            document.getElementById('username').value = user.username || '';
                            document.getElementById('email').value = user.email || '';
                            document.getElementById('role').value = user.role || 'user';
                            document.getElementById('contact_number').value = user.contact_number ||
                                '';
                            document.getElementById('is_active').checked = user.is_active == 1;
                            document.getElementById('password').value = '';
                            document.getElementById('password').required = false;

                            // Show employee ID if exists
                            const employeeIdDisplay = document.getElementById('employeeIdDisplay');
                            const displayEmployeeId = document.getElementById(
                                'display_employee_id');

                            if (user.employee_id) {
                                employeeIdDisplay.style.display = 'block';
                                displayEmployeeId.value = user.employee_id;
                            } else {
                                employeeIdDisplay.style.display = 'none';
                                displayEmployeeId.value = '';
                            }

                            // Update form for edit mode
                            document.getElementById('formMethod').value = 'PUT';
                            document.getElementById('modalTitle').innerHTML =
                                '<i class="fas fa-edit"></i> Edit User';
                            document.getElementById('submitBtn').innerHTML = 'Update User';
                            document.getElementById('passwordLabel').innerHTML = 'Password';
                            const passwordRequired = document.getElementById('passwordRequired');
                            if (passwordRequired) passwordRequired.style.display = 'none';
                            document.getElementById('passwordHelp').style.display = 'block';
                            document.getElementById('userForm').action = `/admin/users/${userId}`;

                            openModal(userFormModal);
                        } catch (error) {
                            console.error('Error fetching user data:', error);
                            alert(
                                'Error loading user data. Please check if the route is properly defined.'
                            );
                        }
                    });
                });

                // Form Submission
                const userForm = document.getElementById('userForm');
                if (userForm) {
                    userForm.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const formData = new FormData(this);
                        const method = document.getElementById('formMethod').value;
                        let url = this.action;

                        const fetchOptions = {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.getAttribute('content') || '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        };

                        if (method === 'PUT') {
                            formData.append('_method', 'PUT');
                        }

                        try {
                            const response = await fetch(url, fetchOptions);
                            const result = await response.json();

                            if (response.ok) {
                                closeModal(userFormModal);
                                location.reload();
                            } else {
                                let errorMessage = 'Error saving user. ';
                                if (result.errors) {
                                    errorMessage += Object.values(result.errors).flat().join(', ');
                                } else if (result.message) {
                                    errorMessage += result.message;
                                }
                                alert(errorMessage);
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
                        }
                    });
                }

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

                document.querySelectorAll('.approve-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        let userId = this.dataset.id;
                        let userName = this.dataset.name;

                        if (confirm(`Are you sure you want to approve ${userName}'s account?`)) {
                            // Show loading state on button
                            const originalText = btn.innerHTML;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Approving...';
                            btn.disabled = true;

                            fetch(`/admin/users/${userId}/approve`, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        return response.json().then(err => {
                                            throw err;
                                        });
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.success) {
                                        alert('Account approved successfully!');
                                        location.reload();
                                    } else {
                                        throw new Error(data.message ||
                                        'Failed to approve account');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error approving account: ' + (error.message ||
                                        'Please try again.'));
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                });
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
                        openModal(rejectModal);
                    });
                });
            });
        </script>
    @endpush
@endsection
