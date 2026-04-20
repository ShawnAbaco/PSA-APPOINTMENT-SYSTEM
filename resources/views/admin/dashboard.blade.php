{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')
<style>
    /* public/css/admin/dashboard.css */

    :root {
        --psa-primary: #0F3B6F;
        --psa-accent: #1D4ED8;
        --psa-gray: #E5E7EB;
        --psa-gray-light: #F9FAFB;
        --text-muted: #6B7280;
        --success: #10B981;
        --warning: #F59E0B;
        --info: #3B82F6;
        --danger: #EF4444;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .dashboard-container {
        width: 100%;
        padding: 0;
    }

    /* Welcome Section */
    .welcome-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .dashboard-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 8px 0;
    }

    .welcome-text {
        color: var(--text-muted);
        margin: 0;
        font-size: 0.95rem;
    }

    .date-display {
        background: var(--psa-gray-light);
        padding: 10px 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--psa-primary);
        font-weight: 500;
        font-size: 0.9rem;
        border: 1px solid var(--psa-gray);
    }

    .date-display i {
        font-size: 1rem;
    }

    /* Stats Grid Layout */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    /* Stat Card */
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        border: 1px solid var(--psa-gray);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .stat-card-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .stat-info {
        flex: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-muted);
        margin: 0 0 8px 0;
        letter-spacing: 0.3px;
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 800;
        margin: 0 0 8px 0;
        color: var(--text-dark);
    }

    .stat-trend {
        font-size: 0.75rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .trend-up {
        color: var(--success);
    }

    .trend-down {
        color: var(--danger);
    }

    .trend-neutral {
        color: var(--warning);
    }

    .stat-value.text-warning {
        color: var(--warning);
    }

    .stat-value.text-success {
        color: var(--success);
    }

    .stat-value.text-info {
        color: var(--info);
    }

    /* Stat Icon Circles */
    .stat-icon-circle {
        width: 54px;
        height: 54px;
        background: rgba(15, 59, 111, 0.1);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .stat-icon-circle i {
        font-size: 1.6rem;
        color: var(--psa-primary);
    }

    .stat-icon-circle.warning-bg {
        background: rgba(245, 158, 11, 0.1);
    }

    .stat-icon-circle.warning-bg i {
        color: var(--warning);
    }

    .stat-icon-circle.success-bg {
        background: rgba(16, 185, 129, 0.1);
    }

    .stat-icon-circle.success-bg i {
        color: var(--success);
    }

    .stat-icon-circle.info-bg {
        background: rgba(59, 130, 246, 0.1);
    }

    .stat-icon-circle.info-bg i {
        color: var(--info);
    }

    .stat-card:hover .stat-icon-circle {
        transform: scale(1.05);
    }

    /* Stats Grid for Total Users Card (complex stats) */
    .stats-complex {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--psa-gray);
    }

    .stat-row:last-child {
        border-bottom: none;
    }

    .stat-row-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .stat-row-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .stat-row-value.text-primary {
        color: var(--psa-primary);
    }

    .stat-row-value.text-success {
        color: var(--success);
    }

    /* Charts Row */
    .charts-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .chart-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--psa-gray);
        overflow: hidden;
    }

    .chart-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--psa-gray);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .chart-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chart-title i {
        color: var(--psa-accent);
        font-size: 1.1rem;
    }

    .chart-subtitle {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .chart-filter {
        padding: 6px 12px;
        border: 1px solid var(--psa-gray);
        border-radius: 8px;
        background: white;
        color: var(--text-dark);
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .chart-filter:hover {
        border-color: var(--psa-accent);
        background: var(--psa-gray-light);
    }

    .chart-body {
        padding: 24px;
        height: 300px;
        position: relative;
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 24px;
    }

    /* Schedule Card */
    .schedule-card {
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--psa-gray);
        overflow: hidden;
    }

    .card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--psa-gray);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-title i {
        color: var(--psa-accent);
    }

    .view-link {
        color: var(--psa-accent);
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .view-link:hover {
        gap: 10px;
        color: var(--psa-primary);
    }

    /* Schedule List */
    .schedule-list {
        padding: 8px 0;
        max-height: 400px;
        overflow-y: auto;
    }

    .schedule-list::-webkit-scrollbar {
        width: 6px;
    }

    .schedule-list::-webkit-scrollbar-track {
        background: var(--psa-gray);
        border-radius: 10px;
    }

    .schedule-list::-webkit-scrollbar-thumb {
        background: var(--psa-accent);
        border-radius: 10px;
    }

    .schedule-item {
        display: flex;
        align-items: center;
        padding: 16px 24px;
        border-bottom: 1px solid var(--psa-gray);
        transition: background 0.2s ease;
    }

    .schedule-item:hover {
        background: var(--psa-gray-light);
    }

    .schedule-time {
        min-width: 100px;
        font-weight: 600;
        color: var(--psa-primary);
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .schedule-time i {
        font-size: 0.8rem;
    }

    .schedule-info {
        flex: 1;
        padding: 0 16px;
    }

    .schedule-title {
        font-weight: 500;
        color: var(--text-dark);
        margin-bottom: 4px;
        font-size: 0.9rem;
    }

    .schedule-subtitle {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .empty-schedule {
        text-align: center;
        padding: 48px 24px;
        color: var(--text-muted);
    }

    .empty-schedule i {
        font-size: 3rem;
        margin-bottom: 12px;
        display: block;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: capitalize;
        white-space: nowrap;
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

    /* Loading States */
    .loading-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        color: var(--text-muted);
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 3px solid var(--psa-gray);
        border-top-color: var(--psa-accent);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 12px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card,
    .chart-card,
    .schedule-card {
        animation: fadeInUp 0.5s ease-out;
    }

    .stat-card:nth-child(1) {
        animation-delay: 0.05s;
    }

    .stat-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .stat-card:nth-child(3) {
        animation-delay: 0.15s;
    }

    .stat-card:nth-child(4) {
        animation-delay: 0.2s;
    }

    .stat-card:nth-child(5) {
        animation-delay: 0.25s;
    }

    /* Responsive Design */
    @media (max-width: 1280px) {
        .stats-grid {
            gap: 20px;
        }
    }

    @media (max-width: 1024px) {
        .charts-row {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0;
        }

        .welcome-section {
            padding: 20px;
            margin-bottom: 24px;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-value {
            font-size: 1.6rem;
        }

        .stat-icon-circle {
            width: 44px;
            height: 44px;
        }

        .stat-icon-circle i {
            font-size: 1.3rem;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .chart-body {
            height: 250px;
            padding: 16px;
        }

        .chart-header {
            padding: 16px 20px;
        }

        .card-header {
            padding: 14px 20px;
        }

        .schedule-time {
            min-width: 80px;
            font-size: 0.75rem;
        }

        .schedule-item {
            padding: 12px 20px;
        }
    }

    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .dashboard-title {
            font-size: 1.3rem;
        }

        .welcome-section {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .date-display {
            width: 100%;
            justify-content: center;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .schedule-item {
            flex-wrap: wrap;
            gap: 10px;
        }

        .schedule-time {
            width: 100%;
            min-width: auto;
        }

        .schedule-info {
            padding-left: 0;
            width: 100%;
        }

        .status-badge {
            margin-left: auto;
        }

        .chart-filter {
            width: 100%;
        }

        .chart-title {
            font-size: 0.95rem;
        }
    }

    @media (max-width: 480px) {
        .welcome-section {
            padding: 16px;
        }

        .stat-card {
            padding: 16px;
        }

        .stat-value {
            font-size: 1.4rem;
        }

        .chart-body {
            height: 200px;
            padding: 12px;
        }

        .schedule-item {
            padding: 12px 16px;
        }

        .schedule-title {
            font-size: 0.85rem;
        }
    }

    /* Print Styles */
    @media print {
        .dashboard-container {
            background: white;
            padding: 20px;
        }

        .stat-card,
        .chart-card,
        .schedule-card {
            break-inside: avoid;
            box-shadow: none;
            border: 1px solid #ddd;
        }

        .chart-filter,
        .view-link {
            display: none;
        }

        .stat-card:hover {
            transform: none;
        }
    }
</style>
@section('content')
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div>
                <h1 class="dashboard-title">Admin Dashboard</h1>
            </div>
            <div class="date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- All Stats in ONE Row - 8 Cards -->
        <div class="stats-grid">
            <!-- Total Appointments -->
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Total Appointments</h6>
                        <h2 class="stat-value">{{ $totalAppointments ?? 156 }}</h2>
                        <p class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i> +12%
                        </p>
                    </div>
                    <div class="stat-icon-circle">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Approval -->
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Pending Approval</h6>
                        <h2 class="stat-value text-warning">{{ $pendingAppointments ?? 8 }}</h2>
                        <p class="stat-trend trend-neutral">
                            <i class="fas fa-clock"></i> Awaiting
                        </p>
                    </div>
                    <div class="stat-icon-circle warning-bg">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <!-- Confirmed -->
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Confirmed</h6>
                        <h2 class="stat-value text-success">{{ $confirmedAppointments ?? 42 }}</h2>
                        <p class="stat-trend trend-up">
                            <i class="fas fa-check"></i> +5%
                        </p>
                    </div>
                    <div class="stat-icon-circle success-bg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Completed</h6>
                        <h2 class="stat-value text-info">{{ $completedAppointments ?? 98 }}</h2>
                        <p class="stat-trend trend-up">
                            <i class="fas fa-chart-line"></i> +23%
                        </p>
                    </div>
                    <div class="stat-icon-circle info-bg">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Total Users {{ $totalUsers ?? '' }}</h6>
                        <h6 class="stat-label">Staff Users {{ $staffUsers ?? '0' }}</h6>
                        <h6 class="stat-label">Active Staff{{ $activeStaff ?? '' }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <!-- Appointment Trends Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="fas fa-chart-line"></i>
                        Appointment Trends
                    </h5>
                    <select class="chart-filter" id="trendFilter">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="weekly">This Week</option>
                        <option value="monthly">This Month</option>
                        <option value="yearly">This Year</option>
                    </select>
                </div>
                <div class="chart-body">
                    <canvas id="appointmentTrendsChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="fas fa-chart-pie"></i>
                        Status Distribution
                    </h5>
                    <span class="chart-subtitle">Current appointment statuses</span>
                </div>
                <div class="chart-body">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid - Only Recent Appointments and Staff Directory -->
        <div class="dashboard-grid">
            <!-- Recent Appointments Card -->
            <div class="schedule-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-list-alt"></i>
                        Recent Appointments
                    </h5>
                    <a href="#" class="view-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="schedule-list">
                    <!-- Sample Recent Appointments Data -->
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-calendar-alt"></i>
                            Dec 15, 2024
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-0156</div>
                            <div class="schedule-subtitle">John Smith - 2 clients</div>
                        </div>
                        <span class="status-badge status-confirmed">Confirmed</span>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-calendar-alt"></i>
                            Dec 14, 2024
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-0155</div>
                            <div class="schedule-subtitle">Emma Wilson - 1 client</div>
                        </div>
                        <span class="status-badge status-completed">Completed</span>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-calendar-alt"></i>
                            Dec 14, 2024
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-0154</div>
                            <div class="schedule-subtitle">Michael Brown - 3 clients</div>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-calendar-alt"></i>
                            Dec 13, 2024
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-0153</div>
                            <div class="schedule-subtitle">Lisa Davis - 1 client</div>
                        </div>
                        <span class="status-badge status-confirmed">Confirmed</span>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-calendar-alt"></i>
                            Dec 13, 2024
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-0152</div>
                            <div class="schedule-subtitle">David Lee - 2 clients</div>
                        </div>
                        <span class="status-badge status-completed">Completed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let appointmentTrendsChart;
            let statusDistributionChart;

            // Sample data for different time periods including Today and Yesterday
            const chartData = {
                today: {
                    labels: ['12 AM', '2 AM', '4 AM', '6 AM', '8 AM', '10 AM', '12 PM', '2 PM', '4 PM', '6 PM',
                        '8 PM', '10 PM'
                    ],
                    data: [0, 0, 0, 1, 3, 8, 12, 15, 10, 6, 2, 1]
                },
                yesterday: {
                    labels: ['12 AM', '2 AM', '4 AM', '6 AM', '8 AM', '10 AM', '12 PM', '2 PM', '4 PM', '6 PM',
                        '8 PM', '10 PM'
                    ],
                    data: [0, 0, 1, 2, 4, 7, 11, 14, 12, 8, 3, 1]
                },
                weekly: {
                    labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    data: [12, 19, 15, 17, 14, 10, 8]
                },
                monthly: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    data: [45, 62, 58, 71]
                },
                yearly: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                        'Dec'
                    ],
                    data: [65, 72, 85, 78, 92, 88, 95, 102, 98, 105, 112, 118]
                }
            };

            // Initialize Appointment Trends Chart
            function initAppointmentTrendsChart(period = 'monthly') {
                const ctx = document.getElementById('appointmentTrendsChart').getContext('2d');

                if (appointmentTrendsChart) {
                    appointmentTrendsChart.destroy();
                }

                // Get the appropriate label and data based on period
                let labels = chartData[period].labels;
                let data = chartData[period].data;
                let labelText = 'Appointments';

                // Customize label based on period
                if (period === 'today' || period === 'yesterday') {
                    labelText = period === 'today' ? 'Today\'s Appointments' : 'Yesterday\'s Appointments';
                } else if (period === 'weekly') {
                    labelText = 'Weekly Appointments';
                } else if (period === 'monthly') {
                    labelText = 'Monthly Appointments';
                } else if (period === 'yearly') {
                    labelText = 'Yearly Appointments';
                }

                appointmentTrendsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: labelText,
                            data: data,
                            borderColor: '#1D4ED8',
                            backgroundColor: 'rgba(29, 78, 216, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#1D4ED8',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#0F3B6F'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 10,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: '#1D4ED8',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.raw}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    stepSize: period === 'today' || period === 'yesterday' ? 5 : 20,
                                    font: {
                                        size: 11
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Appointments',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    maxRotation: period === 'today' || period === 'yesterday' ? 45 : 0,
                                    minRotation: period === 'today' || period === 'yesterday' ? 45 : 0
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }

            // Initialize Status Distribution Chart
            function initStatusDistributionChart() {
                const ctx = document.getElementById('statusDistributionChart').getContext('2d');

                statusDistributionChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending Approval', 'Confirmed', 'Completed', 'Cancelled'],
                        datasets: [{
                            data: [8, 42, 98, 12],
                            backgroundColor: [
                                '#F59E0B',
                                '#10B981',
                                '#3B82F6',
                                '#EF4444'
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 10,
                            offset: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: {
                                        size: 12
                                    },
                                    generateLabels: function(chart) {
                                        const data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            return data.labels.map((label, i) => {
                                                const dataset = data.datasets[0];
                                                const value = dataset.data[i];
                                                const total = dataset.data.reduce((a, b) => a +
                                                    b, 0);
                                                const percentage = ((value / total) * 100)
                                                    .toFixed(1);

                                                return {
                                                    text: `${label} (${percentage}%)`,
                                                    fillStyle: dataset.backgroundColor[i],
                                                    strokeStyle: dataset.borderColor,
                                                    lineWidth: dataset.borderWidth,
                                                    hidden: false,
                                                    index: i
                                                };
                                            });
                                        }
                                        return [];
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '60%',
                        radius: '70%'
                    }
                });
            }

            // Initialize charts
            initAppointmentTrendsChart('monthly');
            initStatusDistributionChart();

            // Handle filter change for appointment trends
            const trendFilter = document.getElementById('trendFilter');
            if (trendFilter) {
                trendFilter.addEventListener('change', function(e) {
                    initAppointmentTrendsChart(e.target.value);
                });
            }

            // Animation on scroll for charts
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const chartObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        chartObserver.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.chart-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease-out';
                chartObserver.observe(card);
            });
        });
    </script>
@endpush
