@extends('layouts.operator')

@section('content')
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div>
                <h1 class="dashboard-title">Dashboard</h1>
            </div>
            <div class="date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Stats Grid with Real Data -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Today</h6>
                        <h2 class="stat-value">{{ $todayAppointments }}</h2>
                        <p class="stat-trend trend-neutral">
                            <i class="fas fa-calendar-day"></i> Scheduled for today
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
                        <h6 class="stat-label">Pending</h6>
                        <h2 class="stat-value text-warning">{{ $pendingAppointments }}</h2>
                        <p class="stat-trend trend-neutral">
                            <i class="fas fa-clock"></i> Awaiting action
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
                        <h2 class="stat-value text-success">{{ $confirmedAppointments }}</h2>
                        <p class="stat-trend trend-neutral">
                            <i class="fas fa-check-circle"></i> Ready for service
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
                        <h2 class="stat-value text-info">{{ $completedAppointments }}</h2>
                        <p class="stat-trend trend-neutral">
                            <i class="fas fa-check-double"></i> Successfully completed
                        </p>
                    </div>
                    <div class="stat-icon-circle info-bg">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Total Appointments</h6>
                        <h2 class="stat-value">{{ $totalAppointments }}</h2>
                        <p class="stat-trend trend-neutral">
                            <i class="fas fa-chart-line"></i> All time total
                        </p>
                    </div>
                    <div class="stat-icon-circle">
                        <i class="fas fa-chart-line"></i>
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
                        <option value="daily">Daily</option>
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
            <!-- Recent Appointments Card -->
            <div class="recent-appointments-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Recent Appointments
                    </h5>
                    <a href="{{ route('operator.appointments.index') }}" class="view-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="appointments-list">
                    @forelse($recentAppointments as $appointment)
                        <div class="appointment-item"
                            onclick="window.location='{{ route('operator.appointments.show', $appointment->id) }}'">
                            <div class="appointment-time">
                                <i class="far fa-calendar-alt"></i>
                                {{ date('M d, Y', strtotime($appointment->appointment_date)) }}
                                <span
                                    class="appointment-time-small">{{ date('h:i A', strtotime($appointment->appointment_time ?? '09:00')) }}</span>
                            </div>
                            <div class="appointment-info">
                                <div class="appointment-title">{{ $appointment->appointment_number }}</div>
                                <div class="appointment-subtitle">{{ $appointment->contact_name }} -
                                    {{ $appointment->clients->count() }} client(s)</div>
                            </div>
                            <span class="status-badge status-{{ $appointment->status }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="appointments-empty">
                            <i class="fas fa-calendar-times"></i>
                            <p>No appointments found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Real data from PHP
        const dailyData = @json($dailyChartData ?? []);
        const dailyLabels = @json($dailyChartLabels ?? []);
        const weeklyData = @json($weeklyChartData ?? []);
        const weeklyLabels = @json($weeklyChartLabels ?? []);
        const monthlyData = @json($monthlyChartData ?? []);
        const monthlyLabels = @json($monthlyChartLabels ?? []);
        const yearlyData = @json($yearlyChartData ?? []);
        const yearlyLabels = @json($yearlyChartLabels ?? []);

        // Status counts for pie chart
        const statusCounts = {
            pending: {{ $pendingAppointments }},
            confirmed: {{ $confirmedAppointments }},
            completed: {{ $completedAppointments }},
            cancelled: {{ $cancelledAppointments ?? 0 }}
        };

        // Appointment Trends Chart
        const trendsCtx = document.getElementById('appointmentTrendsChart').getContext('2d');
        let trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: weeklyLabels.length ? weeklyLabels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Appointments',
                    data: weeklyData.length ? weeklyData : [0, 0, 0, 0, 0, 0, 0],
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
                            stepSize: 1,
                            precision: 0
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
                    data: [
                        statusCounts.pending,
                        statusCounts.confirmed,
                        statusCounts.completed,
                        statusCounts.cancelled
                    ],
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = statusCounts.pending + statusCounts.confirmed + statusCounts
                                    .completed + statusCounts.cancelled;
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });

        // Chart filter functionality
        document.getElementById('trendFilter').addEventListener('change', function(e) {
            const value = e.target.value;
            let newData = [];
            let newLabels = [];

            if (value === 'daily') {
                newData = dailyData.length ? dailyData : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                newLabels = dailyLabels.length ? dailyLabels : ['9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM',
                    '4PM', '5PM', '6PM', '7PM', '8PM'
                ];
            } else if (value === 'weekly') {
                newData = weeklyData.length ? weeklyData : [0, 0, 0, 0, 0, 0, 0];
                newLabels = weeklyLabels.length ? weeklyLabels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            } else if (value === 'monthly') {
                newData = monthlyData.length ? monthlyData : Array(30).fill(0);
                newLabels = monthlyLabels.length ? monthlyLabels : Array.from({
                    length: 30
                }, (_, i) => i + 1);
            } else if (value === 'yearly') {
                newData = yearlyData.length ? yearlyData : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                newLabels = yearlyLabels.length ? yearlyLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul',
                    'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ];
            }

            trendsChart.data.datasets[0].data = newData;
            trendsChart.data.labels = newLabels;
            trendsChart.update();
        });
    </script>
@endpush
