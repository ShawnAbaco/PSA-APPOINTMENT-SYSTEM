<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PSA Appointment System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Your provided CSS goes here */
        :root {
            --psa-primary: #0f3b6f;
            --psa-primary-dark: #0a2c52;
            --psa-accent: #c49a2c;
            --psa-accent-light: #e0b54b;
            --psa-gray-light: #f1f5f9;
            --psa-gray: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #475569;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.08), 0 4px 6px -4px rgb(0 0 0 / 0.08);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: var(--text-dark);
            line-height: 1.5;
        }

        /* Container Layout */
        .staff-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .staff-sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--psa-primary) 0%, var(--psa-primary-dark) 100%);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 100;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h4 {
            color: white;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header h4 i {
            color: var(--psa-accent);
        }

        .sidebar-nav {
            padding: 20px 16px;
        }

        .nav-item {
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 500;
            width: 100%;
            background: none;
            border: none;
            cursor: pointer;
        }

        .nav-link i {
            width: 22px;
            font-size: 1.1rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 3px solid var(--psa-accent);
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 16px 0;
        }

        .logout-btn {
            color: #ffc107;
        }

        .logout-btn:hover {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        /* Main Content */
        .staff-main {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            background: #f8fafc;
        }

        /* Top Bar with Notifications */
        .staff-topbar {
            background: white;
            padding: 16px 24px;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 99;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .welcome-text h5 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* Notification Bell */
        .notification-container {
            position: relative;
        }

        .notification-bell {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: var(--text-muted);
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s;
            position: relative;
        }

        .notification-bell:hover {
            background: var(--psa-gray-light);
            color: var(--psa-primary);
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 20px;
            min-width: 18px;
            text-align: center;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 45px;
            right: 0;
            width: 380px;
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--psa-gray);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--psa-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h6 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }

        .mark-all-read {
            background: none;
            border: none;
            color: var(--psa-primary);
            font-size: 0.75rem;
            cursor: pointer;
            font-weight: 500;
        }

        .mark-all-read:hover {
            text-decoration: underline;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 14px 20px;
            border-bottom: 1px solid var(--psa-gray);
            display: flex;
            gap: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: var(--psa-gray-light);
        }

        .notification-item.unread {
            background: rgba(59, 130, 246, 0.05);
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .notification-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .notification-icon.info {
            background: rgba(59, 130, 242, 0.1);
            color: var(--info);
        }

        .notification-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .notification-message {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .notification-time {
            font-size: 0.65rem;
            color: var(--text-muted);
        }

        .notification-footer {
            padding: 12px 20px;
            border-top: 1px solid var(--psa-gray);
            text-align: center;
        }

        .view-all-link {
            color: var(--psa-primary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .role-badge {
            background: var(--psa-primary);
            color: white;
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Content Area */
        .staff-content {
            padding: 24px;
        }

        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: #ecfdf5;
            border-left: 4px solid var(--success);
            color: #065f46;
        }

        .alert-danger {
            background: #fef2f2;
            border-left: 4px solid var(--danger);
            color: #991b1b;
        }

        .alert-info {
            background: #eff6ff;
            border-left: 4px solid var(--info);
            color: #1e40af;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.6;
            padding: 4px 8px;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* Stat Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid var(--psa-gray);
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: rgba(15, 59, 111, 0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: var(--psa-primary);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 20px;
            overflow-x: auto;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--psa-gray);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .data-table th {
            background: var(--psa-gray-light);
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.875rem;
        }

        .data-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--psa-gray);
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .data-table tr:hover {
            background: var(--psa-gray-light);
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-completed {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.75rem;
        }

        .btn-primary {
            background: var(--psa-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--psa-primary-dark);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--psa-gray);
            color: var(--text-dark);
        }

        .btn-outline:hover {
            background: var(--psa-gray-light);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--psa-gray);
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.2s;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--psa-primary);
            box-shadow: 0 0 0 3px rgba(15, 59, 111, 0.1);
        }

        select.form-control {
            cursor: pointer;
        }

        textarea.form-control {
            resize: vertical;
        }

        /* Map Container */
        .map-container {
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 24px;
            border: 1px solid var(--psa-gray);
        }

        .leaflet-map {
            height: 100%;
            width: 100%;
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
        }

        .toast-notification {
            background: white;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 10px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
            border-left: 4px solid;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-success {
            border-left-color: var(--success);
        }

        .toast-error {
            border-left-color: var(--danger);
        }

        .toast-warning {
            border-left-color: var(--warning);
        }

        .toast-info {
            border-left-color: var(--info);
        }

        .toast-icon {
            font-size: 1.25rem;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 2px;
        }

        .toast-message {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .toast-close {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
        }

        /* Row/Column Grid System */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -12px;
        }

        .col {
            flex: 1;
            padding: 12px;
        }

        .col-2 {
            flex: 0 0 16.666%;
        }

        .col-3 {
            flex: 0 0 25%;
        }

        .col-4 {
            flex: 0 0 33.333%;
        }

        .col-6 {
            flex: 0 0 50%;
        }

        .col-8 {
            flex: 0 0 66.666%;
        }

        .col-10 {
            flex: 0 0 83.333%;
        }

        .col-12 {
            flex: 0 0 100%;
        }

        /* Utilities */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-3 {
            margin-top: 16px;
        }

        .mt-4 {
            margin-top: 24px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 16px;
        }

        .mb-4 {
            margin-bottom: 24px;
        }

        .p-3 {
            padding: 16px;
        }

        .px-4 {
            padding-left: 24px;
            padding-right: 24px;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .gap-2 {
            gap: 8px;
        }

        .gap-3 {
            gap: 16px;
        }

        .w-100 {
            width: 100%;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--psa-gray);
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--psa-gray);
            background: white;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .staff-sidebar {
                width: 70px;
            }

            .staff-sidebar .sidebar-header h4 span,
            .staff-sidebar .nav-link span {
                display: none;
            }

            .staff-sidebar .sidebar-header h4 i {
                font-size: 1.5rem;
            }

            .staff-sidebar .nav-link {
                justify-content: center;
            }

            .staff-sidebar .nav-link i {
                margin: 0;
            }

            .staff-main {
                margin-left: 70px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .notification-dropdown {
                width: 320px;
                right: -60px;
            }

            .col-2,
            .col-3,
            .col-4,
            .col-6,
            .col-8,
            .col-10 {
                flex: 0 0 100%;
            }
        }

        @media (max-width: 480px) {
            .staff-topbar {
                flex-direction: column;
                text-align: center;
            }

            .staff-content {
                padding: 16px;
            }

            .stat-card {
                padding: 16px;
            }

            .notification-dropdown {
                width: 280px;
                right: -80px;
            }
        }
    </style>
</head>

<body>
    <div class="staff-container">
        <!-- Sidebar -->
        <div class="staff-sidebar">
            <div class="sidebar-header">
                <h4>
                    <i class="fas fa-id-card"></i>
                    <span>PSA Admin</span>
                </h4>
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
        <div class="staff-main">
            <!-- Top Bar -->
            <div class="staff-topbar">
                <div class="topbar-left">
                    <div class="welcome-text">
                        <h5>Welcome, {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h5>
                    </div>
                </div>
                <div class="topbar-right">
                    <span class="role-badge">{{ ucfirst(Auth::user()->role) }}</span>

                    <!-- Notification Bell (optional - can be implemented later) -->
                    <div class="notification-container">
                        <button class="notification-bell" id="notificationBell">
                            <i class="far fa-bell"></i>
                            <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h6>Notifications</h6>
                                <button class="mark-all-read">Mark all as read</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <!-- Notifications will be loaded here -->
                                <div class="notification-item">
                                    <div class="notification-content">
                                        <div class="notification-title">No new notifications</div>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-footer">
                                <a href="#" class="view-all-link">View all notifications</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="staff-content">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Alert auto-dismiss
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Notification dropdown toggle
        const bell = document.getElementById('notificationBell');
        const dropdown = document.getElementById('notificationDropdown');

        if (bell) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('show');
            });

            document.addEventListener('click', () => {
                dropdown.classList.remove('show');
            });

            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        // Toast notification function
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
                <div class="toast-icon">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Make showToast available globally
        window.showToast = showToast;
    </script>
    @stack('scripts')
</body>

</html>
