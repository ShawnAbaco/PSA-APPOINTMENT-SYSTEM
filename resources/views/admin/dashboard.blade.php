@extends('layouts.admin')
@section('content')
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div>
                <h1 class="dashboard-title">Admin Dashboard</h1>
                <p class="dashboard-subtitle">Overview of your appointment system</p>
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
                        <h2 class="stat-value">{{ $todayAppointments }}</h2>
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
                            <div class="user-stat-item"><span class="user-stat-label">Admins:</span><span
                                    class="user-stat-value">{{ $totalAdmins }}</span></div>
                            <div class="user-stat-item"><span class="user-stat-label">Staff:</span><span
                                    class="user-stat-value">{{ $totalStaff }}</span></div>
                            <div class="user-stat-item"><span class="user-stat-label">Active Staff:</span><span
                                    class="user-stat-value">{{ $activeStaff }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Total Appointment Places (Full Width Left) | Calendar (Right) -->
        <div class="row-2-layout">
            <!-- Total Appointment Places Card -->
            <div class="places-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Total Appointment By Places</h5>
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
                    @foreach ($appointmentPlaces as $place)
                        <div class="place-item">
                            <div class="place-info"><i class="fas fa-location-dot"></i><span
                                    class="place-name">{{ $place['name'] }}</span></div>
                            <div class="place-count"><span class="count-number">{{ $place['count'] }}</span><span
                                    class="count-label">Appointments</span></div>
                            <div class="place-progress">
                                <div class="progress-bar"
                                    style="width: {{ $place['percentage'] }}%; background: {{ $place['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
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
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                        <select id="calendarYearFilter" class="calendar-filter">
                            @for ($y = now()->year; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>
                                    {{ $y }}</option>
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
                <div class="calendar-legend">
                    <div class="legend-item"><span class="legend-color available"></span><span>Available (>0 slots)</span>
                    </div>
                    <div class="legend-item"><span class="legend-color full"></span><span>Full (0 slots)</span></div>
                    <div class="legend-item"><span class="legend-color partial"></span><span>Partial (<50%)< /span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Summary Overview | Status Distribution | Appointment Trends -->
        <div class="row-3-layout">
            <!-- Summary Chart - Spline/Line Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-chart-line"></i> Summary Overview</h5>
                    <select id="summaryFilter" class="chart-filter">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="weekly">This Week</option>
                        <option value="monthly">This Month</option>
                        <option value="yearly">This Year</option>
                    </select>
                </div>
                <div class="chart-body"><canvas id="summaryChart"></canvas></div>
            </div>

            <!-- Status Distribution - Bump Chart -->
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
                <div class="chart-body"><canvas id="statusDistributionChart"></canvas></div>
            </div>

            <!-- Appointment Trends Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-chart-line"></i> Appointment Trends</h5>
                    <select class="chart-filter" id="trendFilter">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="weekly">This Week</option>
                        <option value="monthly">This Month</option>
                        <option value="yearly">This Year</option>
                    </select>
                </div>
                <div class="chart-body"><canvas id="appointmentTrendsChart"></canvas></div>
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
                                {{ date('M d, Y', strtotime($appointment->appointment_date)) }}
                                <span
                                    class="schedule-time-small">{{ date('h:i A', strtotime($appointment->appointment_time ?? '09:00')) }}</span>
                            </div>
                            <div class="schedule-info">
                                <div class="schedule-title">{{ $appointment->appointment_number }}</div>
                                <div class="schedule-subtitle">{{ $appointment->contact_name }} -
                                    {{ $appointment->clients->count() }} client(s)</div>
                            </div>
                            <span
                                class="status-badge status-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                        </div>
                    @empty
                        <div class="empty-state"><i class="fas fa-calendar-times"></i>
                            <h4>No upcoming appointments</h4>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Data from PHP
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
    const statusTimeData = @json($statusTimeData ?? []);

    let appointmentTrendsChart, statusBumpChart, summaryChart;

    // ============================================
    // SUMMARY CHART - With Filter and Summary
    // ============================================

    function getSummaryDataForPeriod(period) {
        const stats = summaryStatsData[period] || summaryStatsData.today;
        return {
            labels: ['Total', 'Pending', 'Confirmed', 'Completed', 'Cancelled'],
            data: [stats.total, stats.pending, stats.confirmed, stats.completed, stats.cancelled]
        };
    }

    function updateSummaryChartSummary(data) {
        const container = document.getElementById('summaryChartSummary');
        if (!container) return;

        const total = data.data[0];
        const pending = data.data[1];
        const confirmed = data.data[2];
        const completed = data.data[3];
        const cancelled = data.data[4];

        container.innerHTML = `
            <div class="chart-summary-grid">
                <div class="summary-item total"><span class="summary-label">Total:</span><span class="summary-value">${total}</span></div>
                <div class="summary-item pending"><span class="summary-label">Pending:</span><span class="summary-value">${pending}</span></div>
                <div class="summary-item confirmed"><span class="summary-label">Confirmed:</span><span class="summary-value">${confirmed}</span></div>
                <div class="summary-item completed"><span class="summary-label">Completed:</span><span class="summary-value">${completed}</span></div>
                <div class="summary-item cancelled"><span class="summary-label">Cancelled:</span><span class="summary-value">${cancelled}</span></div>
            </div>
        `;
    }

    function initSummaryChart(period = 'monthly') {
        const ctx = document.getElementById('summaryChart').getContext('2d');
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
                    backgroundColor: ['#0f3b6f', '#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
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
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}`;
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
                            text: 'Count'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                        }
                    }
                }
            }
        });

        updateSummaryChartSummary({
            labels,
            data
        });
    }

    // ============================================
    // STATUS DISTRIBUTION - BUMP CHART
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
            labels.push('Today');
            pendingData.push(statusTimeData[dateKey]?.pending || 0);
            confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
            completedData.push(statusTimeData[dateKey]?.completed || 0);
            cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
        } else if (period === 'yesterday') {
            const yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);
            const dateKey = yesterday.toISOString().split('T')[0];
            labels.push('Yesterday');
            pendingData.push(statusTimeData[dateKey]?.pending || 0);
            confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
            completedData.push(statusTimeData[dateKey]?.completed || 0);
            cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
        } else if (period === 'weekly') {
            for (let i = 6; i >= 0; i--) {
                const date = new Date();
                date.setDate(today.getDate() - i);
                const dateKey = date.toISOString().split('T')[0];
                labels.push(date.toLocaleDateString('en-US', {
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric'
                }));
                pendingData.push(statusTimeData[dateKey]?.pending || 0);
                confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
                completedData.push(statusTimeData[dateKey]?.completed || 0);
                cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
            }
        } else if (period === 'monthly') {
            const daysInMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
            for (let i = 1; i <= daysInMonth; i++) {
                const dateKey =
                    `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                labels.push(`Day ${i}`);
                pendingData.push(statusTimeData[dateKey]?.pending || 0);
                confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
                completedData.push(statusTimeData[dateKey]?.completed || 0);
                cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
            }
        } else if (period === 'yearly') {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for (let i = 0; i < 12; i++) {
                labels.push(months[i]);
                let pendingSum = 0,
                    confirmedSum = 0,
                    completedSum = 0,
                    cancelledSum = 0;
                const daysInMonth = new Date(today.getFullYear(), i + 1, 0).getDate();
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateKey =
                        `${today.getFullYear()}-${String(i + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
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

    function updateStatusSummary(pendingData, confirmedData, completedData, cancelledData) {
        const summaryContainer = document.getElementById('statusSummary');
        if (!summaryContainer) return;

        const totalPending = pendingData.reduce((a, b) => a + b, 0);
        const totalConfirmed = confirmedData.reduce((a, b) => a + b, 0);
        const totalCompleted = completedData.reduce((a, b) => a + b, 0);
        const totalCancelled = cancelledData.reduce((a, b) => a + b, 0);
        const total = totalPending + totalConfirmed + totalCompleted + totalCancelled;

        summaryContainer.innerHTML = `
            <div class="status-summary-grid">
                <div class="status-summary-item pending"><span class="status-dot"></span><span class="status-label">Pending</span><span class="status-total">${totalPending}</span><span class="status-percentage">(${total > 0 ? ((totalPending / total) * 100).toFixed(1) : 0}%)</span></div>
                <div class="status-summary-item confirmed"><span class="status-dot"></span><span class="status-label">Confirmed</span><span class="status-total">${totalConfirmed}</span><span class="status-percentage">(${total > 0 ? ((totalConfirmed / total) * 100).toFixed(1) : 0}%)</span></div>
                <div class="status-summary-item completed"><span class="status-dot"></span><span class="status-label">Completed</span><span class="status-total">${totalCompleted}</span><span class="status-percentage">(${total > 0 ? ((totalCompleted / total) * 100).toFixed(1) : 0}%)</span></div>
                <div class="status-summary-item cancelled"><span class="status-dot"></span><span class="status-label">Cancelled</span><span class="status-total">${totalCancelled}</span><span class="status-percentage">(${total > 0 ? ((totalCancelled / total) * 100).toFixed(1) : 0}%)</span></div>
            </div>
        `;
    }

    function initStatusBumpChart(period = 'weekly') {
        const ctx = document.getElementById('statusDistributionChart').getContext('2d');
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
                        fill: false,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointStyle: 'circle'
                    },
                    {
                        label: 'Confirmed',
                        data: confirmedData,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointStyle: 'circle'
                    },
                    {
                        label: 'Completed',
                        data: completedData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointStyle: 'circle'
                    },
                    {
                        label: 'Cancelled',
                        data: cancelledData,
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointStyle: 'circle'
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
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
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
                            text: 'Number of Appointments',
                            font: {
                                size: 11
                            }
                        },
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: period === 'weekly' ? 'Day' : period === 'monthly' ? 'Day of Month' : 'Month',
                            font: {
                                size: 11
                            }
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 10
                        }
                    }
                }
            }
        });

        updateStatusSummary(pendingData, confirmedData, completedData, cancelledData);
    }

    // ============================================
    // APPOINTMENT TRENDS CHART - With Summary
    // ============================================

    function updateTrendChartSummary(data) {
        const container = document.getElementById('trendChartSummary');
        if (!container) return;

        const total = data.reduce((a, b) => a + b, 0);
        const average = (total / data.length).toFixed(1);
        const max = Math.max(...data);
        const min = Math.min(...data);

        container.innerHTML = `
            <div class="chart-summary-grid">
                <div class="summary-item"><span class="summary-label">Total:</span><span class="summary-value">${total}</span></div>
                <div class="summary-item"><span class="summary-label">Average:</span><span class="summary-value">${average}</span></div>
                <div class="summary-item"><span class="summary-label">Highest:</span><span class="summary-value">${max}</span></div>
                <div class="summary-item"><span class="summary-label">Lowest:</span><span class="summary-value">${min}</span></div>
            </div>
        `;
    }

    function initAppointmentTrendsChart(period = 'monthly') {
        const ctx = document.getElementById('appointmentTrendsChart').getContext('2d');
        if (appointmentTrendsChart) appointmentTrendsChart.destroy();
        const data = chartData[period];
        let labelText = period === 'today' ? 'Today\'s Appointments' : period === 'yesterday' ?
            'Yesterday\'s Appointments' : period === 'weekly' ? 'Weekly Appointments' : period === 'monthly' ?
            'Monthly Appointments' : 'Yearly Appointments';

        appointmentTrendsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: labelText,
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
                        position: 'top',
                        labels: {
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
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

        updateTrendChartSummary(data.data);
    }

    // ============================================
    // CALENDAR FUNCTIONS
    // ============================================

    function getAvailableYears() {
        const currentYear = new Date().getFullYear();
        return [currentYear, currentYear + 1];
    }

    function updateYearFilter() {
        const yearFilter = document.getElementById('calendarYearFilter');
        if (!yearFilter) return;
        const availableYears = getAvailableYears();
        const currentYear = new Date().getFullYear();
        yearFilter.innerHTML = '';
        availableYears.forEach(year => {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) option.selected = true;
            yearFilter.appendChild(option);
        });
    }

    function generateCalendar(year, month) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();
        const calendarDays = document.getElementById('calendarDays');
        calendarDays.innerHTML = '';

        for (let i = 0; i < startingDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarDays.appendChild(emptyDay);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const currentDate = new Date(year, month - 1, day);
            currentDate.setHours(0, 0, 0, 0);
            const dayOfWeek = currentDate.getDay();
            const dateKey = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isPastDate = currentDate < today;
            const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
            const slots = slotData[dateKey] || {
                total: 20,
                booked: 0
            };
            const remaining = slots.total - slots.booked;

            let statusClass = '';
            let isDisabled = false;

            if (isPastDate) {
                isDisabled = true;
                statusClass = 'disabled past';
            } else if (isWeekend) {
                isDisabled = true;
                statusClass = 'disabled weekend';
            } else if (remaining <= 0) {
                isDisabled = true;
                statusClass = 'full disabled';
            } else if (remaining < slots.total / 2) {
                statusClass = 'partial';
            } else {
                statusClass = 'available';
            }

            const dayElement = document.createElement('div');
            dayElement.className = `calendar-day ${statusClass}`;
            dayElement.innerHTML =
                `<span class="day-number">${day}</span>${!isDisabled && !isWeekend && !isPastDate ? `<span class="slot-info">${remaining} Slots</span>` : ''}${isPastDate ? `<span class="slot-info past-label">Closed</span>` : ''}${remaining <= 0 && !isPastDate && !isWeekend ? `<span class="slot-info full-label">Full</span>` : ''}`;
            calendarDays.appendChild(dayElement);
        }
    }

    function initCalendar() {
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1;
        const currentYear = currentDate.getFullYear();
        const monthFilter = document.getElementById('calendarMonthFilter');
        if (monthFilter) {
            for (let i = 0; i < monthFilter.options.length; i++) {
                const option = monthFilter.options[i];
                const monthValue = parseInt(option.value);
                if (currentYear === currentDate.getFullYear() && monthValue < currentMonth) {
                    option.disabled = true;
                    option.style.color = '#ccc';
                }
            }
        }
        generateCalendar(currentYear, currentMonth);
    }

    // ============================================
    // APPOINTMENT PLACES FUNCTIONS
    // ============================================

    function loadAppointmentPlaces(filter = 'all') {
        fetch(`/admin/appointment-places?filter=${filter}`)
            .then(response => response.json())
            .then(data => {
                const placesList = document.getElementById('placesList');
                placesList.innerHTML = '';
                if (data.length === 0) {
                    placesList.innerHTML =
                        '<div class="empty-state"><i class="fas fa-map-marker-alt"></i><h4>No location data</h4></div>';
                    return;
                }
                data.forEach(place => {
                    placesList.innerHTML +=
                        `<div class="place-item"><div class="place-info"><i class="fas fa-location-dot"></i><span class="place-name">${place.name}</span></div><div class="place-count"><span class="count-number">${place.count}</span><span class="count-label">appointments</span></div><div class="place-progress"><div class="progress-bar" style="width: ${place.percentage}%; background: ${place.color}"></div></div></div>`;
                });
            })
            .catch(error => console.error('Error loading places:', error));
    }

    // ============================================
    // EVENT LISTENERS & INITIALIZATION
    // ============================================

    document.addEventListener('DOMContentLoaded', function() {
        initAppointmentTrendsChart('today');
        initStatusBumpChart('today');
        initSummaryChart('today');
        updateYearFilter();
        initCalendar();

        const monthFilter = document.getElementById('calendarMonthFilter');
        const yearFilter = document.getElementById('calendarYearFilter');
        const currentYear = new Date().getFullYear();
        const currentMonth = new Date().getMonth() + 1;

        if (monthFilter) {
            monthFilter.addEventListener('change', function() {
                const selectedYear = parseInt(yearFilter.value);
                const selectedMonth = parseInt(this.value);
                if (selectedYear === currentYear && selectedMonth < currentMonth) {
                    alert('Cannot select past months');
                    this.value = currentMonth;
                    return;
                }
                generateCalendar(selectedYear, selectedMonth);
            });
        }

        if (yearFilter) {
            yearFilter.addEventListener('change', function() {
                const selectedYear = parseInt(this.value);
                const selectedMonth = parseInt(monthFilter.value);
                if (selectedYear < currentYear) {
                    alert('Cannot select past years');
                    this.value = currentYear;
                    return;
                }
                if (selectedYear === currentYear && selectedMonth < currentMonth) {
                    monthFilter.value = currentMonth;
                }
                generateCalendar(selectedYear, selectedMonth);
            });
        }

        document.getElementById('summaryFilter')?.addEventListener('change', function(e) {
            initSummaryChart(e.target.value);
        });

        document.getElementById('statusPeriodFilter')?.addEventListener('change', function(e) {
            initStatusBumpChart(e.target.value);
        });

        document.getElementById('trendFilter')?.addEventListener('change', function(e) {
            initAppointmentTrendsChart(e.target.value);
        });

        document.getElementById('placesFilter')?.addEventListener('change', function(e) {
            loadAppointmentPlaces(e.target.value);
        });
    });
</script>
