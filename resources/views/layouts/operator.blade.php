<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Operator - PSA Appointment System</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Your existing Laravel CSS files (preserved) -->
    <link rel="stylesheet" href="{{ asset('css/operator/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/operator-stbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/appointments/appointments.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/appointments/show.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/clients/client.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/clients/show.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/reports/reports.css') }}">

    {{-- profile css link --}}
    <link rel="stylesheet" href="{{ asset('css/operator/profile/index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/profile/change-password.css') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/profile/edit.css') }}">

    <style>
        /* Footer Styles */
        .operator-footer {
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

            .operator-footer {
                padding: 16px 20px;
            }
        }

        /* Ensure operator-main takes full height to push footer down */
        .operator-main {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .operator-content {
            flex: 1;
        }
    </style>
</head>

<body>
    <div class="operator-container">
        <!-- Sidebar -->
        <div class="operator-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="sidebar-logo">
                    <h4>Philippine<br>Statistics Authority</h4>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('operator.dashboard') }}"
                        class="nav-link {{ request()->routeIs('operator.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('operator.appointments.index') }}"
                        class="nav-link {{ request()->routeIs('operator.appointments.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i>
                        <span>Appointments</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('operator.clients.index') }}"
                        class="nav-link {{ request()->routeIs('operator.clients.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Clients</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('operator.reports.index') }}"
                        class="nav-link {{ request()->routeIs('operator.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </div>
                <div class="sidebar-divider"></div>
                <div style="flex: 1;"></div>
            </nav>
            <div class="logout-wrapper">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="operator-main">
            <div class="operator-topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-btn" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="welcome-text" style="text-transform: uppercase;">
                        <h5>Welcome, {{ Auth::user()->role }}</h5>
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

                    {{-- <!-- Role Badge -->
                    <span class="role-badge" id="roleBadge"
                        style="cursor: pointer;">{{ ucfirst(Auth::user()->role) }}</span> --}}

                    <!-- User Full Name -->
                    <span class="user-fullname" id="userFullName"
                        style="cursor: pointer; font-weight: 500; color: var(--gray-700, #374151);">
                        {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                    </span>

                    <!-- Profile Picture -->
                    <div class="profile-pic-container" id="profilePicContainer" style="cursor: pointer;">
                        @php
                            $avatarPath = Auth::user()->profile_photo ?? null;
                            $userInitial = strtoupper(substr(Auth::user()->first_name, 0, 1));
                        @endphp
                        @if ($avatarPath && file_exists(public_path('storage/' . $avatarPath)))
                            <img src="{{ asset('storage/' . $avatarPath) }}" alt="Profile" class="topbar-avatar"
                                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #0f3b6f;">
                        @else
                            <div class="topbar-avatar-placeholder"
                                style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0f3b6f, #CE1126); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; border: 2px solid #0f3b6f;">
                                {{ $userInitial }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="operator-content">
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
            <footer class="operator-footer">
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
                <div class="profile-tabs">
                    <button class="profile-tab active" data-tab="view-tab">Profile</button>
                    <button class="profile-tab" data-tab="edit-tab">Edit Profile</button>
                    <button class="profile-tab" data-tab="password-tab">Change Password</button>
                    <button class="profile-tab" data-tab="twofa-tab">2FA Security</button>
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

                <div class="tab-pane" id="edit-tab">
                    <form id="profileUpdateForm" method="POST" action="{{ route('operator.update') }}"
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
                <div class="tab-pane" id="password-tab">
                    <form id="passwordUpdateForm" method="POST" action="{{ route('operator.password.update') }}">
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
                <!-- Two-Factor Authentication Tab -->
                <div class="tab-pane" id="twofa-tab">
                    <div class="twofa-container">
                        <div class="twofa-header">
                            <div>
                                <h4><i class="fas fa-shield-alt"></i> Two-Factor Authentication</h4>
                            </div>
                            <div>
                                <span id="twoFactorStatus"
                                    class="twofa-status {{ Auth::user()->two_factor_enabled ? 'enabled' : 'disabled' }}">
                                    {{ Auth::user()->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                                <button id="manage2faBtn" class="btn-manage" style="margin-left: 10px;">
                                    <i class="fas fa-cog"></i> Manage
                                </button>
                            </div>
                        </div>
                        <p class="twofa-description">
                            Two-factor authentication adds an extra layer of security to your account.
                            Once enabled, you'll need to provide a verification code from your authenticator app when
                            logging in.
                        </p>

                        <div id="twoFactorPanel" style="display: none;">
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="2fa_action" value="enable"
                                        {{ !Auth::user()->two_factor_enabled ? 'checked' : '' }}>
                                    <i class="fas fa-check-circle"></i> Enable 2FA
                                </label>
                                <label>
                                    <input type="radio" name="2fa_action" value="disable"
                                        {{ Auth::user()->two_factor_enabled ? 'checked' : '' }}>
                                    <i class="fas fa-ban"></i> Disable 2FA
                                </label>
                            </div>

                            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                                <button id="apply2faBtn" class="btn-save">
                                    <i class="fas fa-check"></i> Apply Changes
                                </button>
                                <button id="showQrBtn" class="btn-manage"
                                    style="display: {{ Auth::user()->two_factor_enabled ? 'inline-block' : 'none' }};">
                                    <i class="fas fa-qrcode"></i> Show QR-Code Key
                                </button>
                            </div>

                            <div id="qrPreview" style="display: none;" class="qr-preview">
                                <img id="qrImage" class="qr-image" src="" alt="QR Code">
                                <div id="qrSecret" class="qr-secret"></div>
                                {{-- <div id="recoveryCodes" class="recovery-codes" style="display: none;">
                                    <strong><i class="fas fa-key"></i> Recovery Codes (store securely)</strong>
                                    <ul id="recoveryList"></ul>
                                    <small>⚠️ These codes can be used to log in if you lose access to your authenticator
                                        app.</small>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="psa-loader-modal" id="psaLoaderModal">
        <div class="psa-loader-container">
            <img src="{{ asset('images/psa.png') }}" alt="PSA Loading" class="psa-loader-logo">
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // ========================
        // DATE & TIME UPDATE
        // ========================
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

        // ========================
        // NOTIFICATION MANAGER
        // ========================
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
                            <div class="notification-title">${n.title}</div>
                            <div class="notification-message">${n.message}</div>
                            <div class="notification-time">${n.time}</div>
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

        // ========================
        // FOOTER LINK 
        // ========================
        function initFooterLinks() {
            const aboutLink = document.getElementById('aboutLink');
            const helpLink = document.getElementById('helpLink');
            const privacyLink = document.getElementById('privacyLink');
            const contactLink = document.getElementById('contactLink');

            // Profile modal triggers - all open the profile modal
            const roleBadgeEl = document.getElementById('roleBadge');
            const userFullName = document.getElementById('userFullName');
            const profilePicContainer = document.getElementById('profilePicContainer');
            const profileModal = document.getElementById('profileModal');

            function openProfileModal(e) {
                if (e) e.preventDefault();
                if (profileModal) {
                    profileModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            }

            // Attach click events to open profile modal
            if (roleBadgeEl) {
                roleBadgeEl.addEventListener('click', openProfileModal);
            }
            if (userFullName) {
                userFullName.addEventListener('click', openProfileModal);
            }
            if (profilePicContainer) {
                profilePicContainer.addEventListener('click', openProfileModal);
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

        // Toast
        window.showToast = function(title, message, type = 'info') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = 'toast';
            let icon = 'fa-info-circle';
            if (type === 'success') icon = 'fa-check-circle';
            if (type === 'warning') icon = 'fa-exclamation-triangle';
            toast.innerHTML =
                `<i class="fas ${icon}" style="color:#FCD116;"></i><div><strong>${title}</strong><br/><small>${message}</small></div>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        };

        // PSA Loader Controls
        function showPSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.add('show');
        }

        function hidePSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.remove('show');
        }

        // Mobile Sidebar Toggle
        function initMobileSidebar() {
            const toggleBtn = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
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

        // Auto-dismiss alerts & bind form loaders with PSA loading state
        document.addEventListener('DOMContentLoaded', function() {
            window.notificationManager = new NotificationManager();
            initMobileSidebar();
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

            // Password strength, match check, and AJAX submit handlers for modal
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
                if (strengthText) strengthText.textContent = score <= 1 ? 'Weak' : score === 2 ? 'Moderate' :
                    'Strong';
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
            if (confirmPassword) confirmPassword.addEventListener('input', checkPasswordMatch);

            // Profile form AJAX - keep modal open on success
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
                        .then(r => r.json()).then(data => {
                            if (data.success) {
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
                        }).catch(() => {
                            alert('Network error occurred');
                            if (saveBtn) {
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                            }
                            hidePSALoader();
                        });
                });
            }

            // Password form AJAX
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
                        .then(r => r.json()).then(data => {
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
                        }).catch(() => {
                            alert('Network error occurred');
                            if (saveBtn) {
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                            }
                            hidePSALoader();
                        });
                });
            }

            // Two-Factor Authentication handlers (operator)
            const manage2faBtn = document.getElementById('manage2faBtn');
            const twoFactorPanel = document.getElementById('twoFactorPanel');
            const apply2faBtn = document.getElementById('apply2faBtn');
            const showQrBtn = document.getElementById('showQrBtn');
            const qrPreview = document.getElementById('qrPreview');
            const qrImage = document.getElementById('qrImage');
            const qrSecret = document.getElementById('qrSecret');
            const twoFactorStatus = document.getElementById('twoFactorStatus');
            const recoveryCodes = document.getElementById('recoveryCodes');
            const recoveryList = document.getElementById('recoveryList');

            if (manage2faBtn) {
                manage2faBtn.addEventListener('click', () => {
                    if (twoFactorPanel) {
                        twoFactorPanel.style.display = twoFactorPanel.style.display === 'none' ? 'block' :
                            'none';
                    }
                });
            }

            if (apply2faBtn) {
                apply2faBtn.addEventListener('click', async () => {
                    const action = document.querySelector('input[name="2fa_action"]:checked');
                    if (!action) {
                        alert('Select enable or disable first');
                        return;
                    }
                    if (action.value === 'disable') {
                        if (!confirm('Are you sure you want to disable two-factor authentication?'))
                            return;
                    }

                    const btnText = apply2faBtn.innerHTML;
                    apply2faBtn.disabled = true;
                    apply2faBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

                    try {
                        const resp = await fetch('{{ route('operator.2fa.toggle') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                action: action.value
                            })
                        });
                        const data = await resp.json();

                        if (data.success) {
                            if (twoFactorStatus) {
                                twoFactorStatus.textContent = data.enabled ? 'Enabled' : 'Disabled';
                                twoFactorStatus.className =
                                    `twofa-status ${data.enabled ? 'enabled' : 'disabled'}`;
                            }
                            if (showQrBtn) showQrBtn.style.display = data.enabled ? 'inline-block' :
                                'none';

                            if (data.enabled && qrPreview) {
                                if (qrImage) qrImage.src = data.qr_image || data.qr;
                                if (qrSecret) qrSecret.textContent = data.secret;
                                qrPreview.style.display = 'block';
                                if (data.recovery_codes && recoveryList) {
                                    recoveryList.innerHTML = '';
                                    data.recovery_codes.forEach(c => {
                                        const li = document.createElement('li');
                                        li.textContent = c;
                                        recoveryList.appendChild(li);
                                    });
                                    if (recoveryCodes) recoveryCodes.style.display = 'block';
                                }
                            } else if (qrPreview) {
                                qrPreview.style.display = 'none';
                                if (qrImage) qrImage.src = '';
                                if (qrSecret) qrSecret.textContent = '';
                                if (recoveryCodes) recoveryCodes.style.display = 'none';
                            }
                            if (typeof showToast !== 'undefined') showToast('Two-Factor',
                                'Two-factor updated successfully', 'success');
                        } else {
                            if (typeof showToast !== 'undefined') showToast('Two-Factor', data
                                .message || 'Unable to update 2FA', 'warning');
                        }
                    } catch (error) {
                        if (typeof showToast !== 'undefined') showToast('Two-Factor',
                            'Network error occurred', 'error');
                    } finally {
                        apply2faBtn.disabled = false;
                        apply2faBtn.innerHTML = btnText;
                    }
                });
            }

            if (showQrBtn) {
                showQrBtn.addEventListener('click', async () => {
                    const btnText = showQrBtn.innerHTML;
                    showQrBtn.disabled = true;
                    showQrBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                    try {
                        const resp = await fetch('{{ route('operator.2fa.qr') }}');
                        const data = await resp.json();
                        if (data.success && qrPreview) {
                            if (qrImage) qrImage.src = data.qr_image || data.qr;
                            if (qrSecret) qrSecret.textContent = data.secret;
                            if (data.recovery_codes && recoveryList) {
                                recoveryList.innerHTML = '';
                                data.recovery_codes.forEach(c => {
                                    const li = document.createElement('li');
                                    li.textContent = c;
                                    recoveryList.appendChild(li);
                                });
                                if (recoveryCodes) recoveryCodes.style.display = 'block';
                            }
                            qrPreview.style.display = 'block';
                        } else {
                            if (typeof showToast !== 'undefined') showToast('Two-Factor',
                                '2FA not enabled', 'warning');
                        }
                    } catch (error) {
                        if (typeof showToast !== 'undefined') showToast('Two-Factor',
                            'Failed to load QR code', 'error');
                    } finally {
                        showQrBtn.disabled = false;
                        showQrBtn.innerHTML = btnText;
                    }
                });
            }

            // Auto dismiss alerts after 4 sec
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 4000);

            // Hook into all forms to show PSA loader on submit
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    showPSALoader();
                });
            });

            // Also attach to all navigation links that are actual route changes
            const navLinks = document.querySelectorAll('.nav-link:not(.logout-btn)');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('javascript')) {
                        showPSALoader();
                    }
                });
            });

            // Hide loader when page finishes loading (backup)
            window.addEventListener('load', function() {
                setTimeout(() => hidePSALoader(), 500);
            });

            // Expose globally
            window.showPSALoader = showPSALoader;
            window.hidePSALoader = hidePSALoader;
        });
    </script>

    @stack('scripts')
</body>

</html>
