<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff - PSA Appointment System</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/staff/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/staff-stbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/appointments/appointments.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/appointments/show.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/clients/client.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/clients/show.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/reports/reports.css') }}">

    {{-- profile css link --}}
    <link rel="stylesheet" href="{{ asset('css/staff/profile/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/profile/change-password.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff/profile/edit.css') }}">

    <style>
        /* Footer Styles */
        .staff-footer {
            background: white;
            border-top: 1px solid #e4e7eb;
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
            color: #6c757d;
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
            color: #6c757d;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .footer-links a:hover {
            color: #0f3b6f;
        }

        .footer-datetime {
            display: flex;
            align-items: center;
            gap: 16px;
            color: #6c757d;
            font-size: 13px;
        }

        .footer-datetime div {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-datetime i {
            color: #0f3b6f;
            font-size: 12px;
        }

        .footer-version {
            background: #f5f6f8;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: #6c757d;
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

            .staff-footer {
                padding: 16px 20px;
            }
        }

        /* Ensure staff-main takes full height to push footer down */
        .staff-main {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .staff-content {
            flex: 1;
        }
    </style>
</head>

<body>
    <div class="staff-container">
        <!-- Sidebar -->
        <div class="staff-sidebar" id="staffSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="sidebar-logo">
                    <h4>Philippine<br>Statistics Authority</h4>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('staff.dashboard') }}"
                        class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('staff.appointments.index') }}"
                        class="nav-link {{ request()->routeIs('staff.appointments.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('staff.clients.index') }}"
                        class="nav-link {{ request()->routeIs('staff.clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clients</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('staff.reports.index') }}"
                        class="nav-link {{ request()->routeIs('staff.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </div>
                <div class="sidebar-divider"></div>
                <div style="flex: 1;"></div>
            </nav>
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
        <div class="staff-main">
            <div class="staff-topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-btn" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="welcome-text">
                        <h5>Welcome, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h5>
                    </div>
                </div>
                <div class="topbar-right">
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

            <div class="staff-content">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info">
                        {{ session('info') }}
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="staff-footer">
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

    <!-- Profile Modal (available globally for staff) -->
    <div class="profile-modal" id="profileModal">
        <div class="profile-modal-content">
            <div class="profile-modal-header">
                <h3><i class="fas fa-user-circle"></i> My Profile</h3>
                <button class="profile-modal-close" id="closeProfileModal">&times;</button>
            </div>
            <div class="profile-modal-body">
                <div class="profile-tabs">
                    <button class="profile-tab active" data-tab="view-tab">Profile</button>
                    <button class="profile-tab" data-tab="edit-tab">Edit Profile</button>
                    <button class="profile-tab" data-tab="password-tab">Change Password</button>
                </div>

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
                    <form id="profileUpdateForm" method="POST" action="{{ route('staff.profile.update') }}"
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
                                    id="avatarPreview">
                            @else
                                <div class="avatar-placeholder" id="avatarPlaceholder">{{ $userInitial }}</div>
                                <img style="display:none" class="avatar-preview" id="avatarPreview">
                            @endif
                            <div>
                                <label class="upload-btn">
                                    <i class="fas fa-camera"></i> Change Photo
                                    <input type="file" name="profile_photo" id="profilePhotoInput"
                                        accept="image/*" style="display: none;">
                                </label>
                                <small style="display: block; margin-top: 5px; color: #6b7280; font-size: 11px;">Max
                                    2MB.
                                    JPG, PNG only</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span class="required">*</span></label>
                                <input type="text" name="first_name"
                                    value="{{ old('first_name', Auth::user()->first_name) }}" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name"
                                    value="{{ old('last_name', Auth::user()->last_name) }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number"
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
                        action="{{ route('staff.profile.password.update') }}">
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
                                characters
                            </div>
                            <div class="requirement" id="reqUppercase"><i class="fas fa-circle"></i> At least 1
                                uppercase
                                letter</div>
                            <div class="requirement" id="reqLowercase"><i class="fas fa-circle"></i> At least 1
                                lowercase
                                letter</div>
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

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
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
        //  PSA LOADER CONTROLS (from operator)
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
        //  TOAST NOTIFICATION (from operator)
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
        //  FOOTER LINK HANDLERS
        // ============================================================
        function initFooterLinks() {
            const aboutLink = document.getElementById('aboutLink');
            const helpLink = document.getElementById('helpLink');
            const privacyLink = document.getElementById('privacyLink');
            const contactLink = document.getElementById('contactLink');

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
        //  NOTIFICATION MANAGER (from operator)
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
        //  MOBILE SIDEBAR TOGGLE (from operator)
        // ============================================================
        function initMobileSidebar() {
            const toggleBtn = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('staffSidebar');
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

            // Profile Modal Functionality (make role badge open modal)
            const profileModal = document.getElementById('profileModal');
            const closeModalBtn = document.getElementById('closeProfileModal');
            const roleBadgeEl = document.getElementById('roleBadge') || document.querySelector('.role-badge');

            if (roleBadgeEl) {
                roleBadgeEl.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (profileModal) {
                        profileModal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                });
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', function() {
                    if (profileModal) {
                        profileModal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
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

            // Tab switching inside profile modal
            const profileTabs = document.querySelectorAll('.profile-tab');
            if (profileTabs.length) {
                profileTabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const tabId = this.dataset.tab;
                        profileTabs.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove(
                            'active'));
                        const pane = document.getElementById(tabId);
                        if (pane) pane.classList.add('active');
                    });
                });
            }

            // Profile modal: password strength, match check, and AJAX submit handlers
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const matchMessage = document.getElementById('matchMessage');

            function updateStrengthDisplay() {
                if (!newPassword) return;
                const val = newPassword.value;
                let score = 0;
                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[a-z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;

                if (strengthBar) {
                    strengthBar.className = 'strength-bar';
                    if (score <= 1) strengthBar.classList.add('weak');
                    else if (score === 2) strengthBar.classList.add('moderate');
                    else strengthBar.classList.add('strong');
                }
                if (strengthText) {
                    strengthText.textContent = score <= 1 ? 'Weak' : score === 2 ? 'Moderate' : 'Strong';
                }
                if (document.getElementById('reqLength')) document.getElementById('reqLength').style.color = val
                    .length >= 8 ? '#10b981' : '';
                if (document.getElementById('reqUppercase')) document.getElementById('reqUppercase').style.color =
                    /[A-Z]/.test(val) ? '#10b981' : '';
                if (document.getElementById('reqLowercase')) document.getElementById('reqLowercase').style.color =
                    /[a-z]/.test(val) ? '#10b981' : '';
                if (document.getElementById('reqNumber')) document.getElementById('reqNumber').style.color = /[0-9]/
                    .test(val) ? '#10b981' : '';
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

            // Profile Update Form Submission (AJAX) - keep modal open
            const profileForm = document.getElementById('profileUpdateForm');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const saveBtn = document.getElementById('saveProfileBtn');

                    if (saveBtn) {
                        saveBtn.disabled = true;
                        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    }

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
                                // update view fields
                                const viewName = document.querySelector('.profile-view-name');
                                if (viewName) viewName.textContent = document.querySelector(
                                        'input[name="first_name"]').value + ' ' + document
                                    .querySelector('input[name="last_name"]').value;
                                const viewEmail = document.getElementById('viewEmail');
                                if (viewEmail) viewEmail.textContent = document.querySelector(
                                    'input[name="email"]').value;
                                const viewContact = document.getElementById('viewContact');
                                if (viewContact) viewContact.textContent = document.querySelector(
                                    'input[name="contact_number"]').value || 'Not set';

                                // update avatar preview if changed
                                const editPreview = document.getElementById('avatarPreview');
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

                                // show success alert in edit tab
                                const editTab = document.getElementById('edit-tab');
                                const successAlert = document.createElement('div');
                                successAlert.className = 'alert alert-success';
                                successAlert.innerHTML = data.message +
                                    '<button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>';
                                if (editTab) editTab.insertBefore(successAlert, editTab.firstChild);

                                if (saveBtn) {
                                    saveBtn.disabled = false;
                                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                                }
                                hidePSALoader();
                                // keep modal open and switch to view tab after short delay
                                setTimeout(() => {
                                    document.querySelector('.profile-tab[data-tab="view-tab"]')
                                        .click();
                                }, 1200);
                            } else {
                                alert(data.message || 'Something went wrong');
                                if (saveBtn) {
                                    saveBtn.disabled = false;
                                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                                }
                                hidePSALoader();
                            }
                        })
                        .catch(error => {
                            alert('Network error occurred');
                            if (saveBtn) {
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                            }
                            hidePSALoader();
                        });
                });
            }

            // Password Update Form Submission (AJAX) - keep modal open
            const passwordForm = document.getElementById('passwordUpdateForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (!newPassword || !confirmPassword) return;
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
                    if (saveBtn) {
                        saveBtn.disabled = true;
                        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                    }

                    fetch(this.action, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                current_password: document.querySelector(
                                    'input[name="current_password"]').value,
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
                                if (passwordTab) passwordTab.insertBefore(successAlert, passwordTab
                                    .firstChild);

                                passwordForm.reset();
                                if (strengthBar) strengthBar.className = 'strength-bar';
                                if (strengthText) strengthText.textContent = '';
                                if (matchMessage) matchMessage.innerHTML = '';

                                if (saveBtn) {
                                    saveBtn.disabled = false;
                                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                                }
                                hidePSALoader();
                                // stay in modal and switch to view tab after short delay
                                setTimeout(() => {
                                    document.querySelector('.profile-tab[data-tab="view-tab"]')
                                        .click();
                                }, 1500);
                            } else {
                                alert(data.message || 'Current password is incorrect');
                                if (saveBtn) {
                                    saveBtn.disabled = false;
                                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                                }
                                hidePSALoader();
                            }
                        })
                        .catch(error => {
                            alert('Network error occurred');
                            if (saveBtn) {
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                            }
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
        });
    </script>

    @stack('scripts')
</body>

</html>
