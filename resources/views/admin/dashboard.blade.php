@extends('layouts.admin')

@section('content')
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div>
                <h1 class="dashboard-title">Admin Dashboard</h1>
                <p class="dashboard-subtitle">Real-time overview of your appointment system</p>
            </div>
            <div class="date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Row 1: Stats Grid - 4 Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Total Appointments</h6>
                        <h2 class="stat-value">{{ $totalAppointments }}</h2>
                        <p class="stat-trend trend-up"><i class="fas fa-chart-line"></i> All time total</p>
                    </div>
                    <div class="stat-icon-circle"><i class="fas fa-calendar-check"></i></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Today's Appointments</h6>
                        <h2 class="stat-value" id="todayAppointments">{{ $todayAppointments }}</h2>
                        <p class="stat-trend trend-neutral"><i class="fas fa-calendar-day"></i> Scheduled today</p>
                    </div>
                    <div class="stat-icon-circle"><i class="fas fa-calendar-day"></i></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">Completed</h6>
                        <h2 class="stat-value text-success">{{ $completedAppointments }}</h2>
                        <p class="stat-trend trend-up"><i class="fas fa-check-double"></i> Successfully done</p>
                    </div>
                    <div class="stat-icon-circle success-bg"><i class="fas fa-check-double"></i></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h6 class="stat-label">System Users</h6>
                        <div class="user-stats">
                            <div class="user-stat-item">
                                <span class="user-stat-label">Admins:</span>
                                <span class="user-stat-value">{{ $totalAdmins }}</span>
                            </div>
                            <div class="user-stat-item">
                                <span class="user-stat-label">Staff:</span>
                                <span class="user-stat-value">{{ $totalStaff }}</span>
                            </div>
                            <div class="user-stat-item">
                                <span class="user-stat-label">Active Staff:</span>
                                <span class="user-stat-value">{{ $activeStaff }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Total Appointment Places (Left) | Calendar (Right) -->
        <div class="row-2-layout">
            <!-- Total Appointment Places Card -->
            <div class="places-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Total Appointments By Location</h5>
                    <div class="places-filters">
                        <select id="placesFilter" class="places-filter">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="weekly">This Week</option>
                            <option value="monthly">This Month</option>
                        </select>
                    </div>
                </div>
                <div class="places-list" id="placesList">
                    @forelse($appointmentPlaces as $place)
                        <div class="place-item">
                            <div class="place-info">
                                <i class="fas fa-location-dot"></i>
                                <span class="place-name">{{ $place['name'] }}</span>
                            </div>
                            <div class="place-count">
                                <span class="count-number">{{ $place['count'] }}</span>
                                <span class="count-label">Appointments</span>
                            </div>
                            <div class="place-progress">
                                <div class="progress-bar"
                                    style="width: {{ $place['percentage'] }}%; background: {{ $place['color'] }}"></div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-map-marker-alt"></i>
                            <h4>No location data available</h4>
                            <p>Appointments with location will appear here</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Calendar Card -->
            <div class="calendar-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-calendar-alt"></i> Calendar Overview</h5>
                    <div class="calendar-filters">
                        <select id="calendarMonthFilter" class="calendar-filter">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select id="calendarYearFilter" class="calendar-filter">
                            @for ($y = now()->year; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="calendar-body">
                    <div class="calendar-weekdays">
                        <div class="weekday">Sun</div>
                        <div class="weekday">Mon</div>
                        <div class="weekday">Tue</div>
                        <div class="weekday">Wed</div>
                        <div class="weekday">Thu</div>
                        <div class="weekday">Fri</div>
                        <div class="weekday">Sat</div>
                    </div>
                    <div class="calendar-days" id="calendarDays"></div>
                </div>
                {{-- <div class="calendar-legend">
                    <div class="legend-item"><span class="legend-color available"></span><span>Available (>50% slots)</span>
                    </div>
                    <div class="legend-item"><span class="legend-color partial"></span><span>Partial (<50% slots)</span>
                    </div>
                    <div class="legend-item"><span class="legend-color full"></span><span>Full (0 slots)</span></div>
                </div> --}}
            </div>
        </div>

        <!-- Row 3: Three Charts -->
        <div class="row-3-layout">
            <!-- Summary Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-chart-bar"></i> Summary Overview</h5>
                    <select id="summaryFilter" class="chart-filter">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="weekly">This Week</option>
                        <option value="monthly">This Month</option>
                        <option value="yearly">This Year</option>
                    </select>
                </div>
                <div class="chart-body">
                    <canvas id="summaryChart"></canvas>
                </div>
            </div>

            <!-- Status Distribution Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-chart-line"></i> Status Distribution</h5>
                    <select id="statusPeriodFilter" class="chart-filter">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="weekly">This Week</option>
                        <option value="monthly">This Month</option>
                        <option value="yearly">This Year</option>
                    </select>
                </div>
                <div class="chart-body">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
            </div>

            <!-- Appointment Trends Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-chart-line"></i> Appointment Trends</h5>
                    <select id="trendFilter" class="chart-filter">
                        <option value="today">Today (by hour)</option>
                        <option value="yesterday">Yesterday (by hour)</option>
                        <option value="weekly">Last 7 Days</option>
                        <option value="monthly">This Month (by week)</option>
                        <option value="yearly">This Year (by month)</option>
                    </select>
                </div>
                <div class="chart-body">
                    <canvas id="appointmentTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 4: Upcoming Appointments -->
        <div class="upcoming-section">
            <div class="schedule-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-list-alt"></i> Upcoming Appointments</h5>
                    <a href="{{ route('admin.appointments.index') }}" class="view-link">View All <i
                            class="fas fa-arrow-right"></i></a>
                </div>
                <div class="schedule-list">
                    @forelse($upcomingAppointments as $appointment)
                        <div class="schedule-item"
                            onclick="window.location='{{ route('admin.appointments.show', $appointment->id) }}'">
                            <div class="schedule-time">
                                <i class="far fa-calendar-alt"></i>
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                                <span class="schedule-time-small">
                                    {{ $appointment->timeSlot ? \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('h:i A') : 'Time TBD' }}
                                </span>
                            </div>
                            <div class="schedule-info">
                                <div class="schedule-title">{{ $appointment->appointment_number }}</div>
                                <div class="schedule-subtitle">
                                    {{ $appointment->contact_name }} -
                                    {{ $appointment->clients->count() }} client(s)
                                </div>
                            </div>
                            <span class="status-badge status-{{ $appointment->status }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h4>No upcoming appointments</h4>
                            <p>New appointments will appear here</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Dashboard Styles */
        .dashboard-container {
            padding: 20px;
            background: #f5f7fb;
            min-height: 100vh;
        }

        .welcome-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .dashboard-subtitle {
            color: #6b7280;
            margin: 5px 0 0;
        }

        .date-display {
            background: white;
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-label {
            color: #6b7280;
            font-size: 14px;
            margin: 0 0 10px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 5px;
        }

        .text-success {
            color: #10b981;
        }

        .stat-trend {
            font-size: 12px;
            margin: 0;
        }

        .trend-up {
            color: #10b981;
        }

        .trend-neutral {
            color: #6b7280;
        }

        .stat-icon-circle {
            width: 50px;
            height: 50px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #0f3b6f;
        }

        .success-bg {
            background: #d1fae5;
            color: #10b981;
        }

        .user-stats {
            margin-top: 10px;
        }

        .user-stat-item {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 5px;
        }

        /* Row 2 Layout */
        .row-2-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Places Card */
        .places-card,
        .calendar-card,
        .chart-card,
        .schedule-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .places-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .place-item {
            margin-bottom: 20px;
        }

        .place-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .place-name {
            font-weight: 500;
            color: #1f2937;
        }

        .place-count {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .count-number {
            font-weight: 700;
            font-size: 18px;
            color: #0f3b6f;
        }

        .count-label {
            color: #6b7280;
        }

        .place-progress {
            background: #f3f4f6;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s;
        }

        /* Calendar Styles */
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            margin-bottom: 10px;
            font-weight: 600;
            color: #6b7280;
        }

        .weekday {
            padding: 10px;
            font-size: 14px;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day {
            min-height: 80px;
            padding: 8px;
            border-radius: 8px;
            background: #f9fafb;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }

        .calendar-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .calendar-day.available {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
        }

        .calendar-day.partial {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
        }

        .calendar-day.full {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            color: white;
        }

        .calendar-day.weekend,
        .calendar-day.past {
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .calendar-day.empty {
            background: transparent;
            cursor: default;
        }

        .day-number {
            font-size: 18px;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        .slot-info {
            font-size: 11px;
            display: block;
            margin-top: 5px;
        }

        .calendar-legend {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
        }

        .legend-color.available {
            background: #10b981;
        }

        .legend-color.partial {
            background: #f59e0b;
        }

        .legend-color.full {
            background: #ef4444;
        }

        .legend-color.weekend {
            background: #e5e7eb;
        }

        /* Row 3 Layout */
        .row-3-layout {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .chart-filter,
        .places-filter,
        .calendar-filter {
            padding: 5px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: white;
            font-size: 12px;
            cursor: pointer;
        }

        .chart-body {
            height: 300px;
            position: relative;
        }

        /* Upcoming Section */
        .schedule-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background 0.2s;
        }

        .schedule-item:hover {
            background: #f9fafb;
        }

        .schedule-time {
            min-width: 180px;
        }

        .schedule-time-small {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
        }

        .schedule-info {
            flex: 1;
            margin-left: 20px;
        }

        .schedule-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .schedule-subtitle {
            font-size: 14px;
            color: #6b7280;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #059669;
        }

        .status-completed {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .empty-state h4 {
            margin: 10px 0;
            color: #6b7280;
        }

        .view-link {
            color: #0f3b6f;
            text-decoration: none;
            font-size: 14px;
        }

        .view-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .row-2-layout {
                grid-template-columns: 1fr;
            }

            .row-3-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Store chart instances
        let appointmentTrendsChart, statusBumpChart, summaryChart;

        // Chart data from server
        const chartData = {
            today: {
                labels: @json($todayLabels),
                data: @json($todayData)
            },
            yesterday: {
                labels: @json($yesterdayLabels),
                data: @json($yesterdayData)
            },
            weekly: {
                labels: @json($weeklyLabels),
                data: @json($weeklyData)
            },
            monthly: {
                labels: @json($monthlyLabels),
                data: @json($monthlyData)
            },
            yearly: {
                labels: @json($yearlyLabels),
                data: @json($yearlyData)
            }
        };

        const summaryStatsData = @json($summaryStatsData);
        const slotData = @json($calendarSlotData);
        const statusTimeData = @json($statusTimeData);

        // ============================================
        // SUMMARY CHART
        // ============================================
        function getSummaryDataForPeriod(period) {
            const stats = summaryStatsData[period] || summaryStatsData.today;

            return {
                labels: ['Total Appointment', 'Total Slots', 'Total by Location'],
                data: [
                    stats.total || 0,
                    stats.slots || 0,
                    stats.by_location || 0
                ]
            };
        }

        function initSummaryChart(period = 'today') {
            const ctx = document.getElementById('summaryChart');
            if (!ctx) return;

            if (summaryChart) summaryChart.destroy();

            const {
                labels,
                data
            } = getSummaryDataForPeriod(period);

            summaryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Count',
                        data: data,
                        backgroundColor: [
                            '#0f3b6f',
                            '#c49a2c',
                            '#10b981'
                        ],
                        borderRadius: 8,
                        barPercentage: 0.7
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
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw;
                                    let periodText = '';

                                    switch (period) {
                                        case 'today':
                                            periodText = 'today';
                                            break;
                                        case 'yesterday':
                                            periodText = 'yesterday';
                                            break;
                                        case 'weekly':
                                            periodText = 'this week';
                                            break;
                                        case 'monthly':
                                            periodText = 'this month';
                                            break;
                                        case 'yearly':
                                            periodText = 'this year';
                                            break;
                                    }

                                    if (label === 'Total Slots') {
                                        return `${label} ${periodText}: ${value.toLocaleString()}`;
                                    } else if (label === 'Total by Location') {
                                        return `${label} ${periodText}: ${value.toLocaleString()}`;
                                    } else {
                                        return `${label} ${periodText}: ${value.toLocaleString()}`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            title: {
                                display: true,
                                text: 'Count',
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                },
                                stepSize: function() {
                                    const max = Math.max(...data);
                                    if (max <= 10) return 1;
                                    if (max <= 50) return 5;
                                    if (max <= 100) return 10;
                                    return 20;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });
        }

        // ============================================
        // STATUS DISTRIBUTION CHART - Labels Hidden
        // ============================================
        function getStatusCountsForPeriod(period) {
            const today = new Date();
            const labels = [];
            const pendingData = [];
            const confirmedData = [];
            const completedData = [];
            const cancelledData = [];

            if (period === 'today') {
                const dateKey = today.toISOString().split('T')[0];
                labels.push(''); // Empty label
                pendingData.push(statusTimeData[dateKey]?.pending || 0);
                confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
                completedData.push(statusTimeData[dateKey]?.completed || 0);
                cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
            } else if (period === 'yesterday') {
                const yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);
                const dateKey = yesterday.toISOString().split('T')[0];
                labels.push(''); // Empty label
                pendingData.push(statusTimeData[dateKey]?.pending || 0);
                confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
                completedData.push(statusTimeData[dateKey]?.completed || 0);
                cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
            } else if (period === 'weekly') {
                for (let i = 6; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(today.getDate() - i);
                    const dateKey = date.toISOString().split('T')[0];
                    labels.push(''); // Empty labels for weekly
                    pendingData.push(statusTimeData[dateKey]?.pending || 0);
                    confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
                    completedData.push(statusTimeData[dateKey]?.completed || 0);
                    cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
                }
            } else if (period === 'monthly') {
                const year = today.getFullYear();
                const month = today.getMonth();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                for (let i = 1; i <= daysInMonth; i++) {
                    labels.push(''); // Empty labels for monthly
                    pendingData.push(0);
                    confirmedData.push(0);
                    completedData.push(0);
                    cancelledData.push(0);
                }
                // Fill with actual data
                for (let i = 1; i <= daysInMonth; i++) {
                    const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                    if (statusTimeData[dateKey]) {
                        pendingData[i - 1] = statusTimeData[dateKey]?.pending || 0;
                        confirmedData[i - 1] = statusTimeData[dateKey]?.confirmed || 0;
                        completedData[i - 1] = statusTimeData[dateKey]?.completed || 0;
                        cancelledData[i - 1] = statusTimeData[dateKey]?.cancelled || 0;
                    }
                }
            } else if (period === 'yearly') {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const year = today.getFullYear();
                for (let i = 0; i < 12; i++) {
                    labels.push(''); // Empty labels for yearly
                    let pendingSum = 0,
                        confirmedSum = 0,
                        completedSum = 0,
                        cancelledSum = 0;
                    const daysInMonth = new Date(year, i + 1, 0).getDate();
                    for (let d = 1; d <= daysInMonth; d++) {
                        const dateKey = `${year}-${String(i + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        pendingSum += statusTimeData[dateKey]?.pending || 0;
                        confirmedSum += statusTimeData[dateKey]?.confirmed || 0;
                        completedSum += statusTimeData[dateKey]?.completed || 0;
                        cancelledSum += statusTimeData[dateKey]?.cancelled || 0;
                    }
                    pendingData.push(pendingSum);
                    confirmedData.push(confirmedSum);
                    completedData.push(completedSum);
                    cancelledData.push(cancelledSum);
                }
            }

            return {
                labels,
                pendingData,
                confirmedData,
                completedData,
                cancelledData
            };
        }

        function initStatusBumpChart(period = 'weekly') {
            const ctx = document.getElementById('statusDistributionChart');
            if (!ctx) return;

            if (statusBumpChart) statusBumpChart.destroy();

            const {
                labels,
                pendingData,
                confirmedData,
                completedData,
                cancelledData
            } = getStatusCountsForPeriod(period);

            statusBumpChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Pending',
                            data: pendingData,
                            borderColor: '#f59e0b',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#f59e0b',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        },
                        {
                            label: 'Confirmed',
                            data: confirmedData,
                            borderColor: '#10b981',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        },
                        {
                            label: 'Completed',
                            data: completedData,
                            borderColor: '#3b82f6',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        },
                        {
                            label: 'Cancelled',
                            data: cancelledData,
                            borderColor: '#ef4444',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.3,
                            pointBackgroundColor: '#ef4444',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw} appointments`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            title: {
                                display: true,
                                text: 'Number of Appointments'
                            },
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        },
                        x: {
                            display: false,
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // ============================================
        // APPOINTMENT TRENDS CHART - Labels Hidden
        // ============================================
        function initAppointmentTrendsChart(period = 'today') {
            const ctx = document.getElementById('appointmentTrendsChart');
            if (!ctx) return;

            if (appointmentTrendsChart) appointmentTrendsChart.destroy();

            const data = chartData[period];
            if (!data) return;

            // Create empty labels array of same length
            const emptyLabels = new Array(data.labels.length).fill('');

            appointmentTrendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: emptyLabels,
                    datasets: [{
                        label: 'Appointments',
                        data: data.data,
                        borderColor: '#0f3b6f',
                        backgroundColor: 'rgba(15, 59, 111, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#c49a2c',
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
                            position: 'top'
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            callbacks: {
                                // Show the actual label in tooltip
                                title: function(tooltipItems) {
                                    const index = tooltipItems[0].dataIndex;
                                    return data.labels[index];
                                },
                                label: function(context) {
                                    return `Appointments: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            title: {
                                display: true,
                                text: 'Number of Appointments'
                            }
                        },
                        x: {
                            display: false,
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // ============================================
        // CALENDAR FUNCTIONS
        // ============================================
        function generateCalendar(year, month) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const firstDay = new Date(year, month - 1, 1);
            const lastDay = new Date(year, month, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();

            const calendarDays = document.getElementById('calendarDays');
            if (!calendarDays) return;

            calendarDays.innerHTML = '';

            // Add empty cells for days before month starts
            for (let i = 0; i < startingDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day empty';
                calendarDays.appendChild(emptyDay);
            }

            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const currentDate = new Date(year, month - 1, day);
                currentDate.setHours(0, 0, 0, 0);
                const dayOfWeek = currentDate.getDay();
                const dateKey = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isPastDate = currentDate < today;
                const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                const slots = slotData[dateKey] || {
                    total: 20,
                    booked: 0,
                    remaining: 20
                };
                const remaining = slots.remaining;
                const total = slots.total;

                let statusClass = '';

                if (isPastDate) {
                    statusClass = 'past';
                } else if (isWeekend) {
                    statusClass = 'weekend';
                } else if (remaining <= 0) {
                    statusClass = 'full';
                } else if (remaining < total / 2) {
                    statusClass = 'partial';
                } else {
                    statusClass = 'available';
                }

                const dayElement = document.createElement('div');
                dayElement.className = `calendar-day ${statusClass}`;

                let slotInfo = '';
                if (!isPastDate && !isWeekend) {
                    if (remaining <= 0) {
                        slotInfo = '<span class="slot-info full-label">Full</span>';
                    } else {
                        slotInfo = `<span class="slot-info">${remaining} slots left</span>`;
                    }
                } else if (isPastDate) {
                    slotInfo = '<span class="slot-info past-label">Closed</span>';
                } else if (isWeekend) {
                    slotInfo = '<span class="slot-info weekend-label">Weekend</span>';
                }

                dayElement.innerHTML = `<span class="day-number">${day}</span>${slotInfo}`;
                calendarDays.appendChild(dayElement);
            }
        }

        // ============================================
        // APPOINTMENT PLACES FUNCTIONS
        // ============================================
        function loadAppointmentPlaces(filter = 'all') {
            fetch(`/admin/appointment-places?filter=${filter}`)
                .then(response => response.json())
                .then(data => {
                    const placesList = document.getElementById('placesList');
                    if (!placesList) return;

                    placesList.innerHTML = '';
                    if (data.length === 0) {
                        placesList.innerHTML =
                            '<div class="empty-state"><i class="fas fa-map-marker-alt"></i><h4>No location data available</h4></div>';
                        return;
                    }

                    data.forEach(place => {
                        placesList.innerHTML += `
                    <div class="place-item">
                        <div class="place-info">
                            <i class="fas fa-location-dot"></i>
                            <span class="place-name">${escapeHtml(place.name)}</span>
                        </div>
                        <div class="place-count">
                            <span class="count-number">${place.count}</span>
                            <span class="count-label">appointments</span>
                        </div>
                        <div class="place-progress">
                            <div class="progress-bar" style="width: ${place.percentage}%; background: ${place.color}"></div>
                        </div>
                    </div>
                `;
                    });
                })
                .catch(error => console.error('Error loading places:', error));
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ============================================
        // REAL-TIME AUTO REFRESH
        // ============================================
        let refreshInterval;

        function startAutoRefresh() {
            refreshInterval = setInterval(function() {
                refreshDashboardData();
            }, 30000);
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }

        function refreshDashboardData() {
            const placesFilter = document.getElementById('placesFilter')?.value || 'all';
            loadAppointmentPlaces(placesFilter);

            fetch('/admin/summary-stats')
                .then(response => response.json())
                .then(data => {
                    const todayAppointmentsSpan = document.getElementById('todayAppointments');
                    if (todayAppointmentsSpan) {
                        todayAppointmentsSpan.textContent = data.today;
                    }
                })
                .catch(error => console.error('Error refreshing stats:', error));

            const currentPeriod = document.getElementById('trendFilter')?.value || 'today';
            initAppointmentTrendsChart(currentPeriod);

            const statusPeriod = document.getElementById('statusPeriodFilter')?.value || 'weekly';
            initStatusBumpChart(statusPeriod);

            const summaryPeriod = document.getElementById('summaryFilter')?.value || 'today';
            initSummaryChart(summaryPeriod);
        }

        // ============================================
        // INITIALIZATION
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            initAppointmentTrendsChart('today');
            initStatusBumpChart('today');
            initSummaryChart('today');

            const currentDate = new Date();
            generateCalendar(currentDate.getFullYear(), currentDate.getMonth() + 1);

            const summaryFilter = document.getElementById('summaryFilter');
            if (summaryFilter) {
                summaryFilter.addEventListener('change', (e) => initSummaryChart(e.target.value));
            }

            const statusFilter = document.getElementById('statusPeriodFilter');
            if (statusFilter) {
                statusFilter.addEventListener('change', (e) => initStatusBumpChart(e.target.value));
            }

            const trendFilter = document.getElementById('trendFilter');
            if (trendFilter) {
                trendFilter.addEventListener('change', (e) => initAppointmentTrendsChart(e.target.value));
            }

            const placesFilter = document.getElementById('placesFilter');
            if (placesFilter) {
                placesFilter.addEventListener('change', (e) => loadAppointmentPlaces(e.target.value));
            }

            const monthFilter = document.getElementById('calendarMonthFilter');
            const yearFilter = document.getElementById('calendarYearFilter');

            if (monthFilter && yearFilter) {
                const updateCalendar = () => {
                    generateCalendar(parseInt(yearFilter.value), parseInt(monthFilter.value));
                };

                monthFilter.addEventListener('change', updateCalendar);
                yearFilter.addEventListener('change', updateCalendar);
            }

            startAutoRefresh();
        });

        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });
    </script>
@endpush
