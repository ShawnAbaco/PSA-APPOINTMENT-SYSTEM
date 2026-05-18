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
                                <span class="user-stat-label">Admin:</span>
                                <span class="user-stat-value">{{ $totalAdmins }}</span>
                            </div>
                            <div class="user-stat-item">
                                <span class="user-stat-label">Operator:</span>
                                <span class="user-stat-value">{{ $totalOperator }}</span>
                            </div>
                            <div class="user-stat-item">
                                <span class="user-stat-label">Staff:</span>
                                <span class="user-stat-value">{{ $totalStaff }}</span>
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
                                    {{ $appointment->clients->count() }} applicant(s)
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

    <!-- Tooltip for slot details - FIXED STRUCTURE -->
    <div id="slotTooltip" class="slot-tooltip" style="display: none;">
        <div class="tooltip-header">
            <i class="fas fa-calendar-day"></i> <span id="tooltipDate"></span>
        </div>
        <div class="tooltip-body" id="tooltipBody"></div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Store chart instances
        let appointmentTrendsChart, statusBumpChart, summaryChart;
        let tooltipTimeout = null;
        let isHoveringTooltip = false;

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
        // TOOLTIP FUNCTIONS - FIXED
        // ============================================
        function showTooltip(event, date, slots) {
            const tooltip = document.getElementById('slotTooltip');
            const tooltipDate = document.getElementById('tooltipDate');
            const tooltipBody = document.getElementById('tooltipBody');

            if (!tooltip || !tooltipDate || !tooltipBody) {
                console.error('Tooltip elements not found');
                return;
            }

            // Clear any pending hide timeout
            if (tooltipTimeout) {
                clearTimeout(tooltipTimeout);
                tooltipTimeout = null;
            }

            const dateObj = new Date(date);
            const formattedDate = dateObj.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            tooltipDate.textContent = formattedDate;

            let tooltipHtml = '';

            if (slots && slots.service_breakdown) {
                const services = [{
                        key: 'reg',
                        name: 'National ID Registration'
                    },
                    {
                        key: 'updating',
                        name: 'Correction/Updating'
                    },
                    {
                        key: 'inquiry',
                        name: 'Status Inquiry'
                    }
                ];

                let totalRemaining = 0;
                let totalCapacity = 0;

                services.forEach(service => {
                    const capacity = slots.service_breakdown[service.key]?.capacity || 0;
                    const booked = slots.service_breakdown[service.key]?.booked || 0;
                    const remaining = capacity - booked;
                    totalRemaining += remaining;
                    totalCapacity += capacity;

                    let statusClass = 'green';
                    let statusText = `${remaining} left`;

                    if (remaining <= 0) {
                        statusClass = 'full';
                        statusText = 'FULL';
                    } else if (remaining < capacity / 2) {
                        statusClass = 'yellow';
                    }

                    tooltipHtml += `
                        <div class="tooltip-service-row">
                            <span class="service-name">${service.name}</span>
                            <span class="service-remaining ${statusClass}">${statusText}</span>
                        </div>
                    `;
                });

                let totalStatusClass = 'green';
                let totalStatusText = `${totalRemaining} slots left`;
                if (totalRemaining <= 0) {
                    totalStatusClass = 'full';
                    totalStatusText = 'FULLY BOOKED';
                } else if (totalRemaining < totalCapacity / 2) {
                    totalStatusClass = 'yellow';
                }

                tooltipHtml += `
                    <div class="tooltip-service-row" style="margin-top: 8px; padding-top: 8px; border-top: 2px solid #e0e0e0; font-weight: 600;">
                        <span class="service-name">TOTAL AVAILABLE</span>
                        <span class="service-remaining ${totalStatusClass}">${totalStatusText}</span>
                    </div>
                `;
            } else if (slots) {
                const remaining = slots.remaining || 0;
                const total = slots.total || 84;

                let statusClass = 'green';
                let statusText = `${remaining} slots left`;

                if (remaining <= 0) {
                    statusClass = 'full';
                    statusText = 'FULLY BOOKED';
                } else if (remaining < total / 2) {
                    statusClass = 'yellow';
                }

                tooltipHtml = `
                    <div class="tooltip-service-row">
                        <span class="service-name">Total Available Slots</span>
                        <span class="service-remaining ${statusClass}">${statusText}</span>
                    </div>
                `;
            } else {
                tooltipHtml = `
                    <div class="tooltip-service-row">
                        <span class="service-name">No slot information available</span>
                    </div>
                `;
            }

            tooltipBody.innerHTML = tooltipHtml;
            tooltip.style.display = 'block';

            // Position tooltip
            const rect = event.target.getBoundingClientRect();
            let left = rect.right + 10;
            let top = rect.top;

            const tooltipRect = tooltip.getBoundingClientRect();

            // Check if tooltip goes off screen to the right
            if (left + tooltipRect.width > window.innerWidth) {
                left = rect.left - tooltipRect.width - 10;
            }

            // Check if tooltip goes off screen at the bottom
            if (top + tooltipRect.height > window.innerHeight) {
                top = window.innerHeight - tooltipRect.height - 10;
            }

            // Check if tooltip goes off screen at the top
            if (top < 0) {
                top = 10;
            }

            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }

        function hideTooltip() {
            // Don't hide if mouse is over tooltip
            if (isHoveringTooltip) return;

            tooltipTimeout = setTimeout(() => {
                const tooltip = document.getElementById('slotTooltip');
                if (tooltip) {
                    tooltip.style.display = 'none';
                }
                tooltipTimeout = null;
            }, 300);
        }

        function cancelHideTooltip() {
            if (tooltipTimeout) {
                clearTimeout(tooltipTimeout);
                tooltipTimeout = null;
            }
        }

        // ============================================
        // SUMMARY CHART
        // ============================================
        function getSummaryDataForPeriod(period) {
            const stats = summaryStatsData[period] || summaryStatsData.today;
            return {
                labels: ['Total Appointment', 'Total Slots', 'Total by Location'],
                data: [stats.total || 0, stats.slots || 0, stats.by_location || 0]
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
                        backgroundColor: ['#0f3b6f', '#c49a2c', '#10b981'],
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
                            }
                        }
                    }
                }
            });
        }

        // ============================================
        // STATUS DISTRIBUTION CHART
        // ============================================
        function getStatusCountsForPeriod(period) {
            const today = new Date();
            const labels = [];
            const pendingData = [],
                confirmedData = [],
                completedData = [],
                cancelledData = [];

            if (period === 'today') {
                const dateKey = today.toISOString().split('T')[0];
                labels.push('');
                pendingData.push(statusTimeData[dateKey]?.pending || 0);
                confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
                completedData.push(statusTimeData[dateKey]?.completed || 0);
                cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
            } else if (period === 'yesterday') {
                const yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);
                const dateKey = yesterday.toISOString().split('T')[0];
                labels.push('');
                pendingData.push(statusTimeData[dateKey]?.pending || 0);
                confirmedData.push(statusTimeData[dateKey]?.confirmed || 0);
                completedData.push(statusTimeData[dateKey]?.completed || 0);
                cancelledData.push(statusTimeData[dateKey]?.cancelled || 0);
            } else if (period === 'weekly') {
                for (let i = 6; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(today.getDate() - i);
                    const dateKey = date.toISOString().split('T')[0];
                    labels.push('');
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
                    labels.push('');
                    pendingData.push(0);
                    confirmedData.push(0);
                    completedData.push(0);
                    cancelledData.push(0);
                }
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
                const year = today.getFullYear();
                for (let i = 0; i < 12; i++) {
                    labels.push('');
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
                            tension: 0.3,
                            pointRadius: 4
                        },
                        {
                            label: 'Confirmed',
                            data: confirmedData,
                            borderColor: '#10b981',
                            tension: 0.3,
                            pointRadius: 4
                        },
                        {
                            label: 'Completed',
                            data: completedData,
                            borderColor: '#3b82f6',
                            tension: 0.3,
                            pointRadius: 4
                        },
                        {
                            label: 'Cancelled',
                            data: cancelledData,
                            borderColor: '#ef4444',
                            tension: 0.3,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            display: false
                        }
                    }
                }
            });
        }

        // ============================================
        // APPOINTMENT TRENDS CHART
        // ============================================
        function initAppointmentTrendsChart(period = 'today') {
            const ctx = document.getElementById('appointmentTrendsChart');
            if (!ctx) return;
            if (appointmentTrendsChart) appointmentTrendsChart.destroy();
            const data = chartData[period];
            if (!data) return;
            appointmentTrendsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: new Array(data.labels.length).fill(''),
                    datasets: [{
                        label: 'Appointments',
                        data: data.data,
                        borderColor: '#0f3b6f',
                        backgroundColor: 'rgba(15, 59, 111, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#c49a2c',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: (items) => data.labels[items[0].dataIndex],
                                label: (ctx) => `Appointments: ${ctx.raw}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        },
                        x: {
                            display: false
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
                    total: 84,
                    booked: 0,
                    remaining: 84,
                    service_breakdown: {
                        reg: {
                            capacity: 28,
                            booked: 0
                        },
                        updating: {
                            capacity: 28,
                            booked: 0
                        },
                        inquiry: {
                            capacity: 28,
                            booked: 0
                        }
                    }
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
                dayElement.setAttribute('data-date', dateKey);
                dayElement.setAttribute('data-slots', JSON.stringify(slots));

                let slotInfo = '';
                if (!isPastDate && !isWeekend) {
                    if (remaining <= 0) {
                        slotInfo = '<span class="slot-info full-label">FULL</span>';
                    } else {
                        slotInfo = `<span class="slot-info">${remaining} slots left</span>`;
                    }
                } else if (isPastDate) {
                    slotInfo = '<span class="slot-info past-label">Closed</span>';
                } else if (isWeekend) {
                    slotInfo = '<span class="slot-info weekend-label">Weekend</span>';
                }

                dayElement.innerHTML = `<span class="day-number">${day}</span>${slotInfo}`;

                // Add hover events for available days
                if (!isPastDate && !isWeekend) {
                    dayElement.addEventListener('mouseenter', (e) => {
                        const slotsData = JSON.parse(dayElement.getAttribute('data-slots'));
                        showTooltip(e, dateKey, slotsData);
                    });
                    dayElement.addEventListener('mouseleave', () => {
                        hideTooltip();
                    });
                }

                calendarDays.appendChild(dayElement);
            }
        }

        // Setup tooltip hover handling
        function setupTooltipHandlers() {
            const tooltip = document.getElementById('slotTooltip');
            if (!tooltip) return;

            tooltip.addEventListener('mouseenter', () => {
                isHoveringTooltip = true;
                cancelHideTooltip();
            });

            tooltip.addEventListener('mouseleave', () => {
                isHoveringTooltip = false;
                hideTooltip();
            });
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
            refreshInterval = setInterval(refreshDashboardData, 30000);
        }

        function stopAutoRefresh() {
            if (refreshInterval) clearInterval(refreshInterval);
        }

        function refreshDashboardData() {
            const placesFilter = document.getElementById('placesFilter')?.value || 'all';
            loadAppointmentPlaces(placesFilter);

            fetch('/admin/summary-stats')
                .then(response => response.json())
                .then(data => {
                    const todaySpan = document.getElementById('todayAppointments');
                    if (todaySpan) todaySpan.textContent = data.today;
                })
                .catch(error => console.error('Error refreshing stats:', error));

            const currentPeriod = document.getElementById('trendFilter')?.value || 'today';
            initAppointmentTrendsChart(currentPeriod);

            const statusPeriod = document.getElementById('statusPeriodFilter')?.value || 'weekly';
            initStatusBumpChart(statusPeriod);

            const summaryPeriod = document.getElementById('summaryFilter')?.value || 'today';
            initSummaryChart(summaryPeriod);

            const monthFilter = document.getElementById('calendarMonthFilter');
            const yearFilter = document.getElementById('calendarYearFilter');
            if (monthFilter && yearFilter) {
                refreshCalendarData(parseInt(yearFilter.value), parseInt(monthFilter.value));
            }
        }

        function refreshCalendarData(year, month) {
            fetch(`/admin/calendar-data?year=${year}&month=${month}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Object.assign(slotData, data.slotData);
                        generateCalendar(year, month);
                    }
                })
                .catch(error => console.error('Error refreshing calendar:', error));
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

            setupTooltipHandlers();

            const summaryFilter = document.getElementById('summaryFilter');
            if (summaryFilter) summaryFilter.addEventListener('change', (e) => initSummaryChart(e.target.value));

            const statusFilter = document.getElementById('statusPeriodFilter');
            if (statusFilter) statusFilter.addEventListener('change', (e) => initStatusBumpChart(e.target.value));

            const trendFilter = document.getElementById('trendFilter');
            if (trendFilter) trendFilter.addEventListener('change', (e) => initAppointmentTrendsChart(e.target
                .value));

            const placesFilter = document.getElementById('placesFilter');
            if (placesFilter) placesFilter.addEventListener('change', (e) => loadAppointmentPlaces(e.target.value));

            const monthFilter = document.getElementById('calendarMonthFilter');
            const yearFilter = document.getElementById('calendarYearFilter');

            if (monthFilter && yearFilter) {
                const updateCalendar = () => generateCalendar(parseInt(yearFilter.value), parseInt(monthFilter
                    .value));
                monthFilter.addEventListener('change', updateCalendar);
                yearFilter.addEventListener('change', updateCalendar);
            }

            startAutoRefresh();
        });

        window.addEventListener('beforeunload', stopAutoRefresh);
    </script>
@endpush
