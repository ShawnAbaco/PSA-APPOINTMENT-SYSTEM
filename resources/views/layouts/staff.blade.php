<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    @stack('styles')
</head>

<body>
    <div class="staff-container">
        <!-- Sidebar -->
        <div class="staff-sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="sidebar-logo">
                    <h4>Philippine Statistics Authority</h4>
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
                <div class="sidebar-divider"></div>
                <div class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="staff-main">
            <div class="staff-topbar">
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
                            <div class="notification-list" id="notificationList">
                                <!-- Notifications will be loaded dynamically -->
                            </div>
                            <div class="notification-footer">
                                <a href="#" class="view-all-link">View all notifications</a>
                            </div>
                        </div>
                    </div>
                    <span class="role-badge">{{ ucfirst(Auth::user()->role) }}</span>
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
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        class NotificationManager {
            constructor() {
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
                    },
                    {
                        id: 4,
                        title: 'Client Feedback',
                        message: 'New feedback received from Maria Santos',
                        time: '3 hours ago',
                        type: 'info',
                        read: true
                    },
                    {
                        id: 5,
                        title: 'System Update',
                        message: 'System maintenance scheduled for tonight at 11 PM',
                        time: 'Yesterday',
                        type: 'warning',
                        read: true
                    }
                ];
                this.unreadCount = this.notifications.filter(n => !n.read).length;
                this.init();
            }

            init() {
                this.renderNotifications();
                this.updateBadge();
                this.attachEventListeners();
                this.startAutoRefresh();
            }

            renderNotifications() {
                const container = document.getElementById('notificationList');
                if (!container) return;

                if (this.notifications.length === 0) {
                    container.innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-bell-slash" style="font-size: 2rem; color: var(--psa-gray);"></i>
                            <p style="margin-top: 10px; color: var(--text-muted);">No notifications</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = this.notifications.map(notification => `
                    <div class="notification-item ${!notification.read ? 'unread' : ''}" data-id="${notification.id}">
                        <div class="notification-icon ${notification.type}">
                            <i class="fas ${this.getIconForType(notification.type)}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${notification.title}</div>
                            <div class="notification-message">${notification.message}</div>
                            <div class="notification-time">${notification.time}</div>
                        </div>
                    </div>
                `).join('');

                // Add click handlers
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
                this.unreadCount = this.notifications.filter(n => !n.read).length;
                const badge = document.getElementById('notificationCount');
                if (badge) {
                    badge.textContent = this.unreadCount;
                    badge.style.display = this.unreadCount > 0 ? 'flex' : 'none';
                }
            }

            markAsRead(id) {
                const notification = this.notifications.find(n => n.id === id);
                if (notification && !notification.read) {
                    notification.read = true;
                    this.renderNotifications();
                    this.updateBadge();
                    showToast('Notification', 'Marked as read', 'info');
                }
            }

            markAllAsRead() {
                this.notifications.forEach(n => n.read = true);
                this.renderNotifications();
                this.updateBadge();
                showToast('Notifications', 'All notifications marked as read', 'success');
            }

            addNotification(title, message, type = 'info') {
                const newNotification = {
                    id: Date.now(),
                    title,
                    message,
                    time: 'Just now',
                    type,
                    read: false
                };
                this.notifications.unshift(newNotification);
                this.renderNotifications();
                this.updateBadge();
                showToast(title, message, type);
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

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!bell?.contains(e.target) && !dropdown?.contains(e.target)) {
                        dropdown?.classList.remove('show');
                    }
                });
            }

            startAutoRefresh() {
                // Simulate new notifications every 30 seconds
                setInterval(() => {
                    const demoMessages = [{
                            title: 'New Appointment',
                            message: 'A new appointment has been scheduled',
                            type: 'success'
                        },
                        {
                            title: 'Reminder',
                            message: 'Upcoming appointment in 30 minutes',
                            type: 'warning'
                        },
                        {
                            title: 'Update',
                            message: 'Client information has been updated',
                            type: 'info'
                        }
                    ];
                    const random = demoMessages[Math.floor(Math.random() * demoMessages.length)];
                    this.addNotification(random.title, random.message, random.type);
                }, 30000);
            }
        }

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize notification manager
            window.notificationManager = new NotificationManager();

            // Auto-dismiss alerts
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(function(alert) {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s';
                    setTimeout(function() {
                        if (alert.parentElement) alert.remove();
                    }, 300);
                });
            }, 5000);
        });

        // Expose showToast globally
        window.showToast = showToast;
    </script>

    @stack('scripts')
</body>

</html>
