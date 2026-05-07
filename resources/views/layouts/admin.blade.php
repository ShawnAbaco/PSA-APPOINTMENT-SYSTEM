<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Admin Panel - PSA Appointment System</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">
    <!-- Fonts and Icons - Only essential external resources -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/admin-stbar.css') }}">

    {{-- appointment css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/appointments/appointments.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/clients/client.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/clients/show.css') }}">

    <link rel="stylesheet" href="{{ asset('css/admin/appointments/calendar.css') }}">

    {{-- users css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/users/user.css') }}">

    {{-- slots css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/slots/slot.css') }}">

    {{-- reports css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/reports/report.css') }}">

    {{-- settings css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/settings/setting.css') }}">

    {{-- profile css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/profile/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/profile/change-password.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/profile/edit.css') }}">


    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Footer Styles */
        .admin-footer {
            background: white;
            border-top: 1px solid var(--gray-200);
            padding: 20px 30px;
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-copyright {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray-600);
            font-size: 13px;
        }

        .footer-copyright i {
            color: #CE1126;
            font-size: 16px;
        }

        .footer-links {
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--gray-600);
            text-decoration: none;
            font-size: 13px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-datetime {
            display: flex;
            align-items: center;
            gap: 16px;
            color: var(--gray-600);
            font-size: 13px;
        }

        .footer-datetime div {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-datetime i {
            color: var(--primary);
            font-size: 12px;
        }

        .footer-version {
            background: var(--gray-100);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: var(--gray-600);
        }

        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-links {
                justify-content: center;
            }

            .footer-datetime {
                justify-content: center;
            }

            .admin-footer {
                padding: 16px 20px;
            }
        }

        /* Ensure admin-main takes full height to push footer down */
        .admin-main {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .admin-content {
            flex: 1;
        }
    </style>

</head>

<body>
    <div class="admin-container">
        <!-- Sidebar with Logo and Logout at Bottom -->
        <div class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="sidebar-logo">
                    <h4>Philippine<br>Statistics Authority</h4>
                </div>
            </div>
            <div class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.appointments.index') }}"
                        class="nav-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.clients.index') }}"
                        class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clients</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.calendar') }}"
                        class="nav-link {{ request()->routeIs('admin.calendar') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Calendar View</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.users.index') }}"
                        class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Users Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.slots.index') }}"
                        class="nav-link {{ request()->routeIs('admin.slots.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-week"></i>
                        <span>Slot Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.reports.index') }}"
                        class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.settings.index') }}"
                        class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
                <div class="sidebar-divider"></div>
                <div style="flex: 1;"></div>
            </div>
            <!-- Logout at bottom with loading state -->
            <div class="logout-wrapper">
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="nav-link logout-btn" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                        <div class="btn-loader"></div>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-btn" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="welcome-text">
                        <h5>Welcome, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h5>
                    </div>
                </div>
                <div class="topbar-right">
                    <!-- Notification Bell -->
                    <div class="notification-container">
                        <button class="notification-bell" id="notificationBell">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationCount">3</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h6>Notifications</h6>
                                <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                            </div>
                            <div class="notification-list" id="notificationList"></div>
                            <div class="notification-footer" style="padding: 10px; text-align:center;">
                                <a href="#" style="color:#CE1126; font-size:0.75rem;">View all</a>
                            </div>
                        </div>
                    </div>
                    <span class="role-badge" id="roleBadge"
                        style="cursor: pointer;">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
            </div>

            <!-- Content Area -->
            <div class="admin-content">
                <!-- Alert Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="alert-close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('error') }}
                        <button type="button" class="alert-close" data-dismiss="alert">&times;</button>
                    </div>
                @endif
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="admin-footer">
                <div class="footer-content">
                    <div class="footer-copyright">
                        <i class="fas fa-copyright"></i>
                        <span>{{ date('Y') }} Philippine Statistics Authority. All rights reserved.</span>
                    </div>
                    <div class="footer-links">
                        <a href="#" id="aboutLink">
                            <i class="fas fa-info-circle"></i>
                            About
                        </a>
                        <a href="#" id="helpLink">
                            <i class="fas fa-question-circle"></i>
                            Help
                        </a>
                        <a href="#" id="privacyLink">
                            <i class="fas fa-shield-alt"></i>
                            Privacy Policy
                        </a>
                        <a href="#" id="contactLink">
                            <i class="fas fa-envelope"></i>
                            Contact
                        </a>
                    </div>
                    <div class="footer-datetime">
                        <div class="footer-date">
                            <i class="fas fa-calendar-day"></i>
                            <span id="currentDate"></span>
                        </div>
                        <div class="footer-time">
                            <i class="fas fa-clock"></i>
                            <span id="currentTime"></span>
                        </div>
                        <div class="footer-version">
                            <i class="fas fa-code-branch"></i>
                            v1.0.0
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="profile-modal" id="profileModal">
        <div class="profile-modal-content">
            <div class="profile-modal-header">
                <h3><i class="fas fa-user-circle"></i> My Profile</h3>
                <button class="profile-modal-close" id="closeProfileModal">&times;</button>
            </div>
            <div class="profile-modal-body">
                <!-- Tabs -->
                <div class="profile-tabs">
                    <button class="profile-tab active" data-tab="view-tab">Profile</button>
                    <button class="profile-tab" data-tab="edit-tab">Edit Profile</button>
                    <button class="profile-tab" data-tab="password-tab">Change Password</button>
                </div>

                <!-- View Profile Tab -->
                <div class="tab-pane active" id="view-tab">
                    <div class="profile-view-header">
                        @php
                            $avatarPath = Auth::user()->profile_photo ?? null;
                            $userInitial = strtoupper(substr(Auth::user()->first_name, 0, 1));
                        @endphp
                        @if ($avatarPath && file_exists(public_path('storage/' . $avatarPath)))
                            <img src="{{ asset('storage/' . $avatarPath) }}" alt="Profile"
                                class="profile-view-avatar" id="modalAvatar">
                        @else
                            <div class="profile-view-avatar-placeholder" id="modalAvatarPlaceholder">
                                {{ $userInitial }}</div>
                            <img style="display:none" class="profile-view-avatar" id="modalAvatar">
                        @endif
                        <h4 class="profile-view-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                        </h4>
                        <span class="profile-view-role">{{ ucfirst(Auth::user()->role) }}</span>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-envelope"></i></div>
                            <div class="info-content">
                                <div class="info-label">Email Address</div>
                                <div class="info-value" id="viewEmail">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-phone"></i></div>
                            <div class="info-content">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value" id="viewContact">
                                    {{ Auth::user()->contact_number ?? 'Not set' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div class="info-content">
                                <div class="info-label">Member Since</div>
                                <div class="info-value">{{ Auth::user()->created_at->format('F d, Y') }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-id-badge"></i></div>
                            <div class="info-content">
                                <div class="info-label">User ID</div>
                                <div class="info-value">#{{ Auth::user()->id }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Tab -->
                <div class="tab-pane" id="edit-tab">
                    <form id="profileUpdateForm" method="POST" action="{{ route('admin.profile.update') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="avatar-section">
                            @php
                                $currentAvatar = Auth::user()->profile_photo ?? null;
                                $avatarUrl =
                                    $currentAvatar && file_exists(public_path('storage/' . $currentAvatar))
                                        ? asset('storage/' . $currentAvatar)
                                        : null;
                            @endphp
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="Profile" class="avatar-preview"
                                    id="editAvatarPreview">
                            @else
                                <div class="avatar-placeholder" id="editAvatarPlaceholder">{{ $userInitial }}</div>
                                <img style="display:none" class="avatar-preview" id="editAvatarPreview">
                            @endif
                            <div>
                                <label class="upload-btn">
                                    <i class="fas fa-camera"></i> Change Photo
                                    <input type="file" name="profile_photo" id="profilePhotoInput"
                                        accept="image/*" style="display: none;">
                                </label>
                                <small style="display: block; margin-top: 5px; color: #6b7280; font-size: 11px;">Max
                                    2MB. JPG, PNG only</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" id="editFirstName"
                                    value="{{ old('first_name', Auth::user()->first_name) }}" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name" id="editLastName"
                                    value="{{ old('last_name', Auth::user()->last_name) }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="email" id="editEmail"
                                value="{{ old('email', Auth::user()->email) }}" required>
                        </div>

                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" id="editContact"
                                value="{{ old('contact_number', Auth::user()->contact_number) }}"
                                placeholder="e.g., 09123456789">
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-save" id="saveProfileBtn">Save Changes</button>
                            <button type="button" class="btn-cancel cancel-edit">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane" id="password-tab">
                    <form id="passwordUpdateForm" method="POST"
                        action="{{ route('admin.profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Current Password <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="current_password" id="currentPassword" required>
                                <i class="fas fa-eye-slash toggle-password" data-target="currentPassword"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>New Password <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="new_password" id="newPassword" required>
                                <i class="fas fa-eye-slash toggle-password" data-target="newPassword"></i>
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar" id="strengthBar"></div>
                                <div class="strength-text" id="strengthText"></div>
                            </div>
                        </div>

                        <div class="password-requirements">
                            <p>Password Requirements:</p>
                            <div class="requirement" id="reqLength"><i class="fas fa-circle"></i> At least 8
                                characters</div>
                            <div class="requirement" id="reqUppercase"><i class="fas fa-circle"></i> At least 1
                                uppercase letter</div>
                            <div class="requirement" id="reqLowercase"><i class="fas fa-circle"></i> At least 1
                                lowercase letter</div>
                            <div class="requirement" id="reqNumber"><i class="fas fa-circle"></i> At least 1 number
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="new_password_confirmation" id="confirmPassword"
                                    required>
                                <i class="fas fa-eye-slash toggle-password" data-target="confirmPassword"></i>
                            </div>
                            <div id="matchMessage" style="font-size: 11px; margin-top: 5px;"></div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-save" id="savePasswordBtn">Update Password</button>
                            <button type="button" class="btn-cancel cancel-password">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- PSA LOADER MODAL -->
    <div class="psa-loader-modal" id="psaLoaderModal">
        <div class="psa-loader-container">
            <img src="{{ asset('images/psa.png') }}" alt="PSA Loading" class="psa-loader-logo">
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // ============================================================
        //  PSA LOADER CONTROLS 
        // ============================================================
        function showPSALoader(customText = null) {
            const loader = document.getElementById('psaLoaderModal');
            const loaderText = document.querySelector('.psa-loader-text');
            if (loaderText && customText) {
                loaderText.textContent = customText;
            } else if (loaderText) {
                loaderText.textContent = 'Loading...';
            }
            if (loader) loader.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hidePSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.remove('show');
            document.body.style.overflow = '';
        }

        // ============================================================
        //  DATE & TIME UPDATE
        // ============================================================
        function updateDateTime() {
            const now = new Date();

            // Format date: Thursday, May 5, 2026
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const dateElement = document.getElementById('currentDate');
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', dateOptions);
            }

            // Format time: 2:30:45 PM
            const timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', timeOptions);
            }
        }

        // Update immediately and then every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // ============================================================
        //  TOAST NOTIFICATION 
        // ============================================================
        window.showToast = function(title, message, type = 'info') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = 'toast';
            let icon = 'fa-info-circle';
            if (type === 'success') icon = 'fa-check-circle';
            if (type === 'warning') icon = 'fa-exclamation-triangle';
            toast.innerHTML =
                `<i class="fas ${icon}" style="color:#FCD116;"></i><div><strong>${escapeHtml(title)}</strong><br/><small>${escapeHtml(message)}</small></div>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        };

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ============================================================
        //  NOTIFICATION MANAGER
        // ============================================================
        class NotificationManager {
            constructor() {
                this.notifications = [{
                        id: 1,
                        title: 'New Appointment',
                        message: 'John Doe booked for tomorrow at 10:00 AM',
                        time: '5 min ago',
                        type: 'success',
                        read: false
                    },
                    {
                        id: 2,
                        title: 'Appointment Reminder',
                        message: 'You have 3 appointments today',
                        time: '1 hour ago',
                        type: 'info',
                        read: false
                    },
                    {
                        id: 3,
                        title: 'Schedule Change',
                        message: 'Your Friday schedule updated',
                        time: '2 hours ago',
                        type: 'warning',
                        read: false
                    },
                    {
                        id: 4,
                        title: 'Client Feedback',
                        message: 'New feedback from Maria Santos',
                        time: '3 hours ago',
                        type: 'info',
                        read: true
                    },
                    {
                        id: 5,
                        title: 'System Update',
                        message: 'Maintenance tonight at 11 PM',
                        time: 'Yesterday',
                        type: 'warning',
                        read: true
                    }
                ];
                this.unreadCount = this.notifications.filter(n => !n.read).length;
                this.init();
            }
            init() {
                this.render();
                this.updateBadge();
                this.attach();
                setInterval(() => this.demoNew(), 35000);
            }
            render() {
                const container = document.getElementById('notificationList');
                if (!container) return;
                if (this.notifications.length === 0) {
                    container.innerHTML = `<div style="padding:30px;text-align:center;">No notifications</div>`;
                    return;
                }
                container.innerHTML = this.notifications.map(n => `
                    <div class="notification-item ${!n.read ? 'unread' : ''}" data-id="${n.id}">
                        <div class="notification-icon ${n.type}"><i class="fas ${this.getIcon(n.type)}"></i></div>
                        <div class="notification-content">
                            <div class="notification-title">${escapeHtml(n.title)}</div>
                            <div class="notification-message">${escapeHtml(n.message)}</div>
                            <div class="notification-time">${escapeHtml(n.time)}</div>
                        </div>
                    </div>
                `).join('');
                document.querySelectorAll('.notification-item').forEach(el => {
                    el.addEventListener('click', () => this.markRead(parseInt(el.dataset.id)));
                });
            }
            getIcon(type) {
                if (type === 'success') return 'fa-check-circle';
                if (type === 'warning') return 'fa-exclamation-triangle';
                return 'fa-info-circle';
            }
            updateBadge() {
                const badge = document.getElementById('notificationCount');
                if (badge) {
                    badge.textContent = this.unreadCount;
                    badge.style.display = this.unreadCount > 0 ? 'flex' : 'none';
                }
            }
            markRead(id) {
                const n = this.notifications.find(x => x.id === id);
                if (n && !n.read) {
                    n.read = true;
                    this.unreadCount--;
                    this.render();
                    this.updateBadge();
                    showToast('Notification', 'Marked as read', 'info');
                }
            }
            markAllRead() {
                this.notifications.forEach(n => n.read = true);
                this.unreadCount = 0;
                this.render();
                this.updateBadge();
                showToast('Notifications', 'All marked as read', 'success');
            }
            demoNew() {
                const msgs = [{
                        title: 'New Booking',
                        message: 'Client requested appointment',
                        type: 'success'
                    },
                    {
                        title: 'Reminder',
                        message: 'Appointment in 20 min',
                        type: 'warning'
                    },
                    {
                        title: 'Info',
                        message: 'System health check passed',
                        type: 'info'
                    }
                ];
                const r = msgs[Math.floor(Math.random() * msgs.length)];
                this.add(r.title, r.message, r.type);
            }
            add(title, message, type) {
                const newN = {
                    id: Date.now(),
                    title,
                    message,
                    time: 'Just now',
                    type,
                    read: false
                };
                this.notifications.unshift(newN);
                this.unreadCount++;
                this.render();
                this.updateBadge();
                showToast(title, message, type);
            }
            attach() {
                const bell = document.getElementById('notificationBell');
                const dropdown = document.getElementById('notificationDropdown');
                const markBtn = document.getElementById('markAllRead');
                if (bell) bell.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });
                if (markBtn) markBtn.addEventListener('click', () => this.markAllRead());
                document.addEventListener('click', (e) => {
                    if (!bell?.contains(e.target) && !dropdown?.contains(e.target)) dropdown?.classList.remove(
                        'show');
                });
            }
        }

        // ============================================================
        //  FOOTER LINK HANDLERS
        // ============================================================
        function initFooterLinks() {
            const aboutLink = document.getElementById('aboutLink');
            const helpLink = document.getElementById('helpLink');
            const privacyLink = document.getElementById('privacyLink');
            const contactLink = document.getElementById('contactLink');
            const roleBadge = document.getElementById('roleBadge');

            if (roleBadge) {
                roleBadge.addEventListener('click', function(e) {
                    e.preventDefault();
                    const modal = document.getElementById('profileModal');
                    if (modal) {
                        modal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                });
            }

            if (aboutLink) {
                aboutLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    showToast('About',
                        'PSA Appointment System v1.0.0<br>Developed for Philippine Statistics Authority', 'info'
                    );
                });
            }

            if (helpLink) {
                helpLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    showToast('Help',
                        'For assistance, contact your system administrator or refer to the user manual.', 'info'
                    );
                });
            }

            if (privacyLink) {
                privacyLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    showToast('Privacy Policy',
                        'All data is handled in accordance with Data Privacy Act of 2012 (RA 10173).', 'info');
                });
            }

            if (contactLink) {
                contactLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    showToast('Contact', 'Email: support@psa.gov.ph<br>Phone: (088) 856-1234', 'info');
                });
            }
        }

        // ============================================================
        //  MOBILE SIDEBAR TOGGLE 
        // ============================================================
        function initMobileSidebar() {
            const toggleBtn = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('adminSidebar');
            if (!toggleBtn || !sidebar) return;
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('sidebar-open');
            });
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                        sidebar.classList.remove('sidebar-open');
                    }
                }
            });
        }

        // ============================================================
        //  LOGOUT HANDLER WITH LOADING STATE
        // ============================================================
        function initLogoutHandler() {
            const logoutForm = document.getElementById('logoutForm');
            const logoutBtn = document.getElementById('logoutBtn');

            if (logoutForm && logoutBtn) {
                logoutForm.addEventListener('submit', function(e) {
                    logoutBtn.classList.add('loading');
                    showPSALoader('Logging out...');
                    logoutBtn.disabled = true;
                });
            }
        }

        // ============================================================
        //  FORM SUBMIT LOADER
        // ============================================================
        function bindFormLoaders() {
            const forms = document.querySelectorAll('form:not(#logoutForm)');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    showPSALoader('Submitting form...');
                });
            });
        }

        // ============================================================
        //  NAVIGATION LINK LOADER
        // ============================================================
        function bindNavigationLoaders() {
            const navLinks = document.querySelectorAll('.nav-link:not(.logout-btn)');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('javascript')) {
                        showPSALoader('Loading page...');
                    }
                });
            });
        }

        // ============================================================
        //  AJAX GLOBAL LOADER
        // ============================================================
        if (typeof $ !== 'undefined') {
            $(document).ajaxStart(function() {
                showPSALoader('Processing request...');
            });
            $(document).ajaxStop(function() {
                hidePSALoader();
            });
            $(document).ajaxError(function() {
                hidePSALoader();
            });
        }

        // ============================================================
        //  PAGE LOAD COMPLETE
        // ============================================================
        window.addEventListener('load', function() {
            setTimeout(() => hidePSALoader(), 500);
        });

        window.addEventListener('pageshow', function() {
            hidePSALoader();
        });

        // ============================================================
        //  DOM CONTENT LOADED - Initialize all components
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            window.notificationManager = new NotificationManager();
            initMobileSidebar();
            bindFormLoaders();
            bindNavigationLoaders();
            initLogoutHandler();
            initFooterLinks();

            // Profile Modal Functionality
            const profileModal = document.getElementById('profileModal');
            const closeModalBtn = document.getElementById('closeProfileModal');

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', function() {
                    profileModal.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            if (profileModal) {
                profileModal.addEventListener('click', function(e) {
                    if (e.target === profileModal) {
                        profileModal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }

            // Tab switching
            const tabs = document.querySelectorAll('.profile-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove(
                        'active'));
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Cancel buttons
            document.querySelectorAll('.cancel-edit, .cancel-password').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelector('.profile-tab[data-tab="view-tab"]').click();
                });
            });

            // Avatar preview
            const photoInput = document.getElementById('profilePhotoInput');
            const editAvatarPreview = document.getElementById('editAvatarPreview');
            const editAvatarPlaceholder = document.getElementById('editAvatarPlaceholder');

            if (photoInput) {
                photoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File size must be less than 2MB');
                            this.value = '';
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            if (editAvatarPreview) {
                                editAvatarPreview.src = event.target.result;
                                editAvatarPreview.style.display = 'block';
                            }
                            if (editAvatarPlaceholder) editAvatarPlaceholder.style.display = 'none';
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const input = document.getElementById(targetId);
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    } else {
                        input.type = 'password';
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    }
                });
            });

            // Password strength checker
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const matchMessage = document.getElementById('matchMessage');

            function checkPasswordStrength(password) {
                let score = 0;
                if (password.length >= 8) score++;
                if (password.match(/[A-Z]/)) score++;
                if (password.match(/[a-z]/)) score++;
                if (password.match(/[0-9]/)) score++;
                return score;
            }

            function updateStrengthDisplay() {
                const password = newPassword ? newPassword.value : '';
                const score = checkPasswordStrength(password);

                let barClass = '';
                let textClass = '';
                let text = '';

                if (password.length === 0) {
                    barClass = '';
                    text = '';
                } else if (score === 1) {
                    barClass = 'weak';
                    textClass = 'weak';
                    text = 'Weak';
                } else if (score === 2) {
                    barClass = 'medium';
                    textClass = 'medium';
                    text = 'Medium';
                } else if (score === 3) {
                    barClass = 'strong';
                    textClass = 'strong';
                    text = 'Strong';
                } else if (score === 4) {
                    barClass = 'very-strong';
                    textClass = 'very-strong';
                    text = 'Very Strong';
                }

                if (strengthBar) strengthBar.className = 'strength-bar ' + barClass;
                if (strengthText) {
                    strengthText.className = 'strength-text ' + textClass;
                    strengthText.textContent = text;
                }

                const reqLength = document.getElementById('reqLength');
                const reqUppercase = document.getElementById('reqUppercase');
                const reqLowercase = document.getElementById('reqLowercase');
                const reqNumber = document.getElementById('reqNumber');

                if (reqLength) {
                    reqLength.className = password.length >= 8 ? 'requirement valid' : 'requirement invalid';
                    reqLength.innerHTML = (password.length >= 8 ? '<i class="fas fa-check-circle"></i>' :
                        '<i class="fas fa-circle"></i>') + ' At least 8 characters';
                }
                if (reqUppercase) {
                    reqUppercase.className = password.match(/[A-Z]/) ? 'requirement valid' : 'requirement invalid';
                    reqUppercase.innerHTML = (password.match(/[A-Z]/) ? '<i class="fas fa-check-circle"></i>' :
                        '<i class="fas fa-circle"></i>') + ' At least 1 uppercase letter';
                }
                if (reqLowercase) {
                    reqLowercase.className = password.match(/[a-z]/) ? 'requirement valid' : 'requirement invalid';
                    reqLowercase.innerHTML = (password.match(/[a-z]/) ? '<i class="fas fa-check-circle"></i>' :
                        '<i class="fas fa-circle"></i>') + ' At least 1 lowercase letter';
                }
                if (reqNumber) {
                    reqNumber.className = password.match(/[0-9]/) ? 'requirement valid' : 'requirement invalid';
                    reqNumber.innerHTML = (password.match(/[0-9]/) ? '<i class="fas fa-check-circle"></i>' :
                        '<i class="fas fa-circle"></i>') + ' At least 1 number';
                }
            }

            function checkPasswordMatch() {
                if (!newPassword || !confirmPassword || !matchMessage) return;
                const password = newPassword.value;
                const confirm = confirmPassword.value;

                if (confirm.length === 0) {
                    matchMessage.innerHTML = '';
                    return;
                }

                if (password === confirm) {
                    matchMessage.innerHTML =
                        '<i class="fas fa-check-circle" style="color: #10b981;"></i> Passwords match';
                    matchMessage.style.color = '#10b981';
                } else {
                    matchMessage.innerHTML =
                        '<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i> Passwords do not match';
                    matchMessage.style.color = '#ef4444';
                }
            }

            if (newPassword) {
                newPassword.addEventListener('input', updateStrengthDisplay);
                newPassword.addEventListener('input', checkPasswordMatch);
            }
            if (confirmPassword) {
                confirmPassword.addEventListener('input', checkPasswordMatch);
            }

            // Profile Update Form Submission (AJAX)
            const profileForm = document.getElementById('profileUpdateForm');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const saveBtn = document.getElementById('saveProfileBtn');

                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                    // Use POST with _method override so multipart FormData is parsed by Laravel
                    formData.append('_method', 'PUT');
                    fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update view tab with new data
                                document.querySelector('.profile-view-name').textContent = document
                                    .getElementById('editFirstName').value + ' ' + document
                                    .getElementById('editLastName').value;
                                document.getElementById('viewEmail').textContent = document
                                    .getElementById('editEmail').value;
                                document.getElementById('viewContact').textContent = document
                                    .getElementById('editContact').value || 'Not set';

                                // Update avatar in view tab if changed
                                const editPreview = document.getElementById('editAvatarPreview');
                                if (editPreview && editPreview.style.display !== 'none') {
                                    const viewAvatar = document.getElementById('modalAvatar');
                                    const viewPlaceholder = document.getElementById(
                                        'modalAvatarPlaceholder');
                                    if (viewAvatar) {
                                        viewAvatar.src = editPreview.src;
                                        viewAvatar.style.display = 'block';
                                    }
                                    if (viewPlaceholder) viewPlaceholder.style.display = 'none';
                                }

                                // Show success message
                                const editTab = document.getElementById('edit-tab');
                                const successAlert = document.createElement('div');
                                successAlert.className = 'alert alert-success';
                                successAlert.innerHTML = data.message +
                                    '<button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>';
                                editTab.insertBefore(successAlert, editTab.firstChild);

                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                                hidePSALoader();

                                // Switch to view tab after 1.5 seconds
                                setTimeout(() => {
                                    document.querySelector('.profile-tab[data-tab="view-tab"]')
                                        .click();
                                }, 1500);
                            } else {
                                alert(data.message || 'Something went wrong');
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                                hidePSALoader();
                            }
                        })
                        .catch(error => {
                            alert('Network error occurred');
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                            hidePSALoader();
                        });
                });
            }

            // Password Update Form Submission (AJAX)
            const passwordForm = document.getElementById('passwordUpdateForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const password = newPassword.value;
                    const confirm = confirmPassword.value;

                    if (password !== confirm) {
                        alert('Passwords do not match!');
                        return;
                    }

                    if (password.length < 8) {
                        alert('Password must be at least 8 characters!');
                        return;
                    }

                    const saveBtn = document.getElementById('savePasswordBtn');
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

                    fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                current_password: document.getElementById('currentPassword')
                                    .value,
                                new_password: password,
                                new_password_confirmation: confirm
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const passwordTab = document.getElementById('password-tab');
                                const successAlert = document.createElement('div');
                                successAlert.className = 'alert alert-success';
                                successAlert.innerHTML = data.message +
                                    '<button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>';
                                passwordTab.insertBefore(successAlert, passwordTab.firstChild);

                                passwordForm.reset();
                                if (strengthBar) strengthBar.className = 'strength-bar';
                                if (strengthText) strengthText.textContent = '';
                                if (matchMessage) matchMessage.innerHTML = '';

                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                                hidePSALoader();

                                setTimeout(() => {
                                    document.querySelector('.profile-tab[data-tab="view-tab"]')
                                        .click();
                                }, 2000);
                            } else {
                                alert(data.message || 'Current password is incorrect');
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                                hidePSALoader();
                            }
                        })
                        .catch(error => {
                            alert('Network error occurred');
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                            hidePSALoader();
                        });
                });
            }

            window.showPSALoader = showPSALoader;
            window.hidePSALoader = hidePSALoader;

            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 4000);

            document.querySelectorAll('.alert-close').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.parentElement.remove();
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
