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
      /* Footer Styles - Fully Centered */
.admin-footer {
    background: white;
    border-top: 1px solid var(--gray-200);
    padding: 20px 30px;
    margin-top: auto;
}

.footer-content {
    display: flex;
    justify-content: center;
    align-items: center;
}

.footer-centered {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 8px;
}

.footer-copyright {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: var(--gray-600);
    font-size: 13px;
    flex-wrap: wrap;
}

.footer-copyright i {
    color: #CE1126;
    font-size: 14px;
}

.footer-devs {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 12px;
    color: var(--gray-600);
    opacity: 0.85;
    flex-wrap: wrap;
}

.footer-devs i {
    color: var(--primary);
    font-size: 12px;
}

.footer-links {
    display: flex;
    align-items: center;
    justify-content: center;
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

.footer-version {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 500;
    color: var(--gray-500);
    background: transparent;
    padding: 4px 8px;
    margin-top: 4px;
}

.footer-version i {
    font-size: 10px;
    color: var(--primary);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .admin-footer {
        padding: 16px 20px;
    }

    .footer-links {
        gap: 16px;
    }

    .footer-copyright {
        font-size: 11px;
    }
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
                    <a href="{{ route('admin.applicants.index') }}"
                        class="nav-link {{ request()->routeIs('admin.applicants.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>Applicants</span>
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
                    <div class="welcome-text" style="text-transform: uppercase;">
                        <h5>Welcome, {{ Auth::user()->role }}</h5>
                    </div>
                </div>
                <div class="topbar-right">
                    <!-- User Full Name -->
                    <span class="user-fullname" id="userFullName"
                        style="cursor: pointer; font-weight: 500; color: var(--gray-700);">
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
                                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary);">
                        @else
                            <div class="topbar-avatar-placeholder"
                                style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, rgb(78, 0, 9), rgb(0, 31, 94)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; border: 2px solid var(--primary);">
                                {{ $userInitial }}
                            </div>
                        @endif
                    </div>
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

<!-- FOOTER - Fully Centered -->
<footer class="admin-footer">
    <div class="footer-content">
        <div class="footer-centered">
            <div class="footer-copyright">
                <i class="fas fa-copyright"></i>
                <span>
                    2026 PSA Misamis Oriental | NationalID Appointment System. All rights reserved.
                </span>
            </div>

            <div class="footer-devs">
                <i class="fas fa-code"></i>
                <span>
                    Developed by Shawn Laurence M. Abaco and Kent Zyrone L. Flores
                </span>
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
                    <button class="profile-tab" data-tab="twofa-tab">2FA Security</button>
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
                    <form id="profileUpdateForm" method="POST" action="{{ route('admin.update') }}"
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
                    <form id="passwordUpdateForm" method="POST" action="{{ route('admin.password.update') }}">
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
                                    <i class="fas fa-key"></i> Show QR-Code Key
                                </button>
                            </div>

                            <div id="qrPreview" style="display: none;" class="qr-preview">
                                <center><img id="qrImage" class="qr-image" src="" alt="QR Code"></center>
                                <div id="qrSecret" class="qr-secret"></div>
                            </div>
                        </div>
                    </div>
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
            if (loader) loader.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hidePSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.remove('show');
            document.body.style.overflow = '';
        }

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
            if (type === 'error') icon = 'fa-times-circle';
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
        //  FOOTER LINK HANDLERS (date/time removed)
        // ============================================================
        function initFooterLinks() {
            const aboutLink = document.getElementById('aboutLink');
            const helpLink = document.getElementById('helpLink');
            const privacyLink = document.getElementById('privacyLink');
            const contactLink = document.getElementById('contactLink');

            const userFullName = document.getElementById('userFullName');
            const profilePicContainer = document.getElementById('profilePicContainer');

            function openProfileModal(e) {
                if (e) e.preventDefault();
                const modal = document.getElementById('profileModal');
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            }

            if (userFullName) userFullName.addEventListener('click', openProfileModal);
            if (profilePicContainer) profilePicContainer.addEventListener('click', openProfileModal);

            if (aboutLink) {
                aboutLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    showToast('About', 'PSA Appointment System v1.0.0<br>Developed for Philippine Statistics Authority', 'info');
                });
            }
            if (helpLink) {
                helpLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    showToast('Help', 'For assistance, contact your system administrator or refer to the user manual.', 'info');
                });
            }
            if (privacyLink) {
                privacyLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    showToast('Privacy Policy', 'All data is handled in accordance with Data Privacy Act of 2012 (RA 10173).', 'info');
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
        //  DOM CONTENT LOADED - Initialize components
        // ============================================================
        document.addEventListener('DOMContentLoaded', function() {
            initMobileSidebar();
            initLogoutHandler();
            initFooterLinks();

            // Profile Modal close logic
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

            // Tab switching for profile
            const tabs = document.querySelectorAll('.profile-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Cancel buttons
            document.querySelectorAll('.cancel-edit, .cancel-password').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelector('.profile-tab[data-tab="view-tab"]').click();
                });
            });

            // Avatar preview logic
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

            window.showPSALoader = showPSALoader;
            window.hidePSALoader = hidePSALoader;

            // auto-dismiss alerts
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 4000);
            document.querySelectorAll('.alert-close').forEach(btn => {
                btn.addEventListener('click', function() { this.parentElement.remove(); });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
