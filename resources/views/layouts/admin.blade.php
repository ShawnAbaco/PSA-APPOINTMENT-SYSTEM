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
    <link rel="stylesheet" href="{{ asset('css/admin/appointments/calendar.css') }}">

    {{-- users css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/users/user.css') }}">

    {{-- slots css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/slots/slot.css') }}">

    {{-- reports css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/reports/report.css') }}">

    {{-- settings css link --}}
    <link rel="stylesheet" href="{{ asset('css/admin/settings/setting.css') }}">

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
                    <span class="role-badge">{{ ucfirst(Auth::user()->role) }}</span>
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
