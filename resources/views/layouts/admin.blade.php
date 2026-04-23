<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <!-- Sidebar with Logo -->
        <div class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="sidebar-logo">
                    <h4>Philippine Statistics Authority</h4>
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
                <div class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="w-100">
                        @csrf
                        <button type="submit" class="nav-link logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-topbar">
                <div class="topbar-left">
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
                            <div class="notification-footer">
                                <a href="#" class="view-all-link">View all notifications</a>
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

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showToast(title, message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            let icon = '';
            switch (type) {
                case 'success':
                    icon = 'fa-check-circle';
                    break;
                case 'error':
                    icon = 'fa-exclamation-circle';
                    break;
                case 'warning':
                    icon = 'fa-exclamation-triangle';
                    break;
                default:
                    icon = 'fa-info-circle';
            }
            toast.innerHTML = `
                <div class="toast-icon"><i class="fas ${icon}"></i></div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            `;
            toastContainer.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        class NotificationManager {
            constructor() {
                this.notifications = [];
                this.unreadCount = 0;
                this.init();
            }
            init() {
                this.loadNotifications();
                this.attachEventListeners();
                this.startAutoRefresh();
            }
            loadNotifications() {
                this.notifications = [{
                        id: 1,
                        title: 'New Appointment',
                        message: 'John Doe booked an appointment for tomorrow at 10:00 AM',
                        time: '5 minutes ago',
                        type: 'success',
                        read: false
                    },
                    {
                        id: 2,
                        title: 'Appointment Reminder',
                        message: 'You have 3 appointments scheduled for today',
                        time: '1 hour ago',
                        type: 'info',
                        read: false
                    },
                    {
                        id: 3,
                        title: 'Schedule Change',
                        message: 'Your schedule for Friday has been updated',
                        time: '2 hours ago',
                        type: 'warning',
                        read: false
                    }
                ];
                this.unreadCount = this.notifications.filter(n => !n.read).length;
                this.renderNotifications();
                this.updateBadge();
            }
            renderNotifications() {
                const container = document.getElementById('notificationList');
                if (!container) return;
                if (this.notifications.length === 0) {
                    container.innerHTML =
                        `<div class="notification-item"><div class="notification-content"><div class="notification-title">No new notifications</div></div></div>`;
                    return;
                }
                container.innerHTML = this.notifications.map(notification => `
                    <div class="notification-item ${!notification.read ? 'unread' : ''}" data-id="${notification.id}">
                        <div class="notification-icon ${notification.type}"><i class="fas ${this.getIconForType(notification.type)}"></i></div>
                        <div class="notification-content">
                            <div class="notification-title">${notification.title}</div>
                            <div class="notification-message">${notification.message}</div>
                            <div class="notification-time">${notification.time}</div>
                        </div>
                    </div>
                `).join('');
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const id = parseInt(item.dataset.id);
                        this.markAsRead(id);
                    });
                });
            }
            getIconForType(type) {
                switch (type) {
                    case 'success':
                        return 'fa-check-circle';
                    case 'warning':
                        return 'fa-exclamation-triangle';
                    case 'danger':
                        return 'fa-times-circle';
                    default:
                        return 'fa-info-circle';
                }
            }
            updateBadge() {
                const badge = document.getElementById('notificationCount');
                if (badge) {
                    if (this.unreadCount > 0) {
                        badge.textContent = this.unreadCount;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }
            markAsRead(id) {
                const notification = this.notifications.find(n => n.id === id);
                if (notification && !notification.read) {
                    notification.read = true;
                    this.unreadCount--;
                    this.renderNotifications();
                    this.updateBadge();
                    showToast('Notification', 'Marked as read', 'info');
                }
            }
            markAllAsRead() {
                this.notifications.forEach(n => n.read = true);
                this.unreadCount = 0;
                this.renderNotifications();
                this.updateBadge();
                showToast('Notifications', 'All notifications marked as read', 'success');
            }
            attachEventListeners() {
                const bell = document.getElementById('notificationBell');
                const dropdown = document.getElementById('notificationDropdown');
                const markAllBtn = document.getElementById('markAllRead');
                if (bell) {
                    bell.addEventListener('click', (e) => {
                        e.stopPropagation();
                        dropdown.classList.toggle('show');
                    });
                }
                if (markAllBtn) {
                    markAllBtn.addEventListener('click', () => {
                        this.markAllAsRead();
                    });
                }
                document.addEventListener('click', () => {
                    if (dropdown) dropdown.classList.remove('show');
                });
                if (dropdown) {
                    dropdown.addEventListener('click', (e) => {
                        e.stopPropagation();
                    });
                }
            }
            startAutoRefresh() {
                setInterval(() => {}, 30000);
            }
        }

        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        document.addEventListener('DOMContentLoaded', function() {
            window.notificationManager = new NotificationManager();
        });
        window.showToast = showToast;
    </script>
    @stack('scripts')
</body>

</html>
