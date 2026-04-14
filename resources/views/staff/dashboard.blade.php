{{-- resources/views/staff/appointments/index.blade.php --}}
@extends('layouts.staff')

@section('content')
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div>
                <h1 class="dashboard-title">Dashboard</h1>
                <p class="welcome-text">Welcome back, Sarah Johnson!</p>
            </div>
            <div class="date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Stats Grid with Sample Data -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Today's Appointments</h6>
                        <h2 class="stat-value">8</h2>
                        <p class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i> +12% from yesterday
                        </p>
                    </div>
                    <div class="stat-icon-circle">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Pending Approval</h6>
                        <h2 class="stat-value text-warning">5</h2>
                        <p class="stat-trend trend-neutral">
                            <i class="fas fa-minus"></i> Awaiting action
                        </p>
                    </div>
                    <div class="stat-icon-circle warning-bg">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Confirmed</h6>
                        <h2 class="stat-value text-success">24</h2>
                        <p class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i> +5% this week
                        </p>
                    </div>
                    <div class="stat-icon-circle success-bg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Completed</h6>
                        <h2 class="stat-value text-info">156</h2>
                        <p class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i> +23% total
                        </p>
                    </div>
                    <div class="stat-icon-circle info-bg">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Analytics Section -->
        <div class="charts-row">
            <!-- Appointment Trends Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title">
                        <i class="fas fa-chart-line"></i>
                        Appointment Trends
                    </h5>
                    <select class="chart-filter" id="trendFilter">
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

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Upcoming Schedule Card -->
            <div class="schedule-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-week"></i>
                        Upcoming Schedule
                    </h5>
                    <a href="#" class="view-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="schedule-list">
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-clock"></i>
                            10:00 AM
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-001</div>
                            <div class="schedule-subtitle">John Smith - 2 clients</div>
                        </div>
                        <span class="status-badge status-confirmed">Confirmed</span>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-clock"></i>
                            11:30 AM
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-002</div>
                            <div class="schedule-subtitle">Emma Wilson - 1 client</div>
                        </div>
                        <span class="status-badge status-pending">Pending</span>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-clock"></i>
                            02:00 PM
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-003</div>
                            <div class="schedule-subtitle">Michael Brown - 3 clients</div>
                        </div>
                        <span class="status-badge status-confirmed">Confirmed</span>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <i class="far fa-clock"></i>
                            03:30 PM
                        </div>
                        <div class="schedule-info">
                            <div class="schedule-title">APT-2024-004</div>
                            <div class="schedule-subtitle">Lisa Davis - 1 client</div>
                        </div>
                        <span class="status-badge status-completed">Completed</span>
                    </div>
                </div>
            </div>

            <!-- Performance Summary Card -->
            <div class="performance-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-trophy"></i>
                        Performance Summary
                    </h5>
                </div>
                <div class="performance-stats">
                    <div class="performance-item">
                        <div class="performance-label">Completion Rate</div>
                        <div class="performance-value">94%</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 94%"></div>
                        </div>
                    </div>
                    <div class="performance-item">
                        <div class="performance-label">On-Time Rate</div>
                        <div class="performance-value">88%</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 88%"></div>
                        </div>
                    </div>
                    <div class="performance-item">
                        <div class="performance-label">Client Satisfaction</div>
                        <div class="performance-value">4.8/5</div>
                        <div class="rating-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Appointment Trends Chart
        const trendsCtx = document.getElementById('appointmentTrendsChart').getContext('2d');
        let trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Appointments',
                    data: [12, 19, 15, 17, 14, 10, 8],
                    borderColor: '#0F3B6F',
                    backgroundColor: 'rgba(15, 59, 111, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#F05A28',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#1f2937',
                        bodyColor: '#6b7280',
                        borderColor: '#e5e7eb',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            stepSize: 5
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [5, 24, 156, 3],
                    backgroundColor: ['#F59E0B', '#10B981', '#3B82F6', '#EF4444'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // Chart filter functionality (demo)
        document.getElementById('trendFilter').addEventListener('change', function(e) {
            const value = e.target.value;
            let newData = [];

            if (value === 'weekly') {
                newData = [12, 19, 15, 17, 14, 10, 8];
                trendsChart.data.labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            } else if (value === 'monthly') {
                newData = [45, 52, 48, 60, 55, 58, 62, 70, 65, 68, 72, 75, 80, 78, 82, 85, 88, 90, 92, 85, 88, 84,
                    82, 79, 75, 72, 68, 65, 62, 58
                ];
                trendsChart.data.labels = Array.from({
                    length: 30
                }, (_, i) => `Day ${i+1}`);
            } else {
                newData = [120, 135, 148, 160, 175, 190, 210, 225, 240, 260, 280, 310];
                trendsChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
                    'Nov', 'Dec'
                ];
            }

            trendsChart.data.datasets[0].data = newData;
            trendsChart.update();
        });
    </script>
@endpush
