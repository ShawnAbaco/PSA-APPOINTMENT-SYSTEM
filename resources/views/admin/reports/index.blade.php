{{-- resources/views/admin/reports/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <div class="reports-container">
        <!-- Header Section -->
        <div class="reports-welcome-section">
            <div>
                <h1 class="reports-title">Reports & Analytics</h1>
                <p class="reports-subtitle">Comprehensive analytics and insights for your appointment system</p>
            </div>
            <div class="reports-date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="reports-card reports-mb-4">
            <div class="reports-card-header">
                <h5 class="reports-card-title"><i class="fas fa-filter"></i> Filter Reports</h5>
            </div>
            <div class="reports-card-body">
                <form method="GET" action="{{ route('admin.reports.index') }}">
                    <div class="reports-form-row">
                        <div class="reports-form-group">
                            <label class="reports-form-label">Start Date</label>
                            <input type="date" name="start_date" class="reports-form-control"
                                value="{{ $startDate ?? date('Y-m-d') }}">
                        </div>
                        <div class="reports-form-group">
                            <label class="reports-form-label">End Date</label>
                            <input type="date" name="end_date" class="reports-form-control"
                                value="{{ $endDate ?? date('Y-m-d') }}">
                        </div>
                        <div class="reports-form-group">
                            <label class="reports-form-label">Status</label>
                            <select name="status" class="reports-form-control">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>
                        <div class="reports-form-group reports-form-group-btn">
                            <button type="submit" class="reports-btn reports-btn-primary reports-btn-block">Generate
                                Report</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="reports-stats-grid reports-mb-4">
            <div class="reports-stat-card">
                <div class="reports-stat-card-content">
                    <div class="reports-stat-info">
                        <h6 class="reports-stat-label">Total Bookings</h6>
                        <h2 class="reports-stat-value">{{ $totalBookings ?? 0 }}</h2>
                        <p class="reports-stat-trend"><i class="fas fa-calendar-check"></i> All time bookings</p>
                    </div>
                    <div class="reports-stat-icon-circle"><i class="fas fa-calendar-check"></i></div>
                </div>
            </div>

            <div class="reports-stat-card">
                <div class="reports-stat-card-content">
                    <div class="reports-stat-info">
                        <h6 class="reports-stat-label">Unique Locations</h6>
                        <h2 class="reports-stat-value reports-text-success">{{ $uniqueLocations ?? 0 }}</h2>
                        <p class="reports-stat-trend"><i class="fas fa-map-marker-alt"></i> Cities with bookings</p>
                    </div>
                    <div class="reports-stat-icon-circle success-bg"><i class="fas fa-map-marker-alt"></i></div>
                </div>
            </div>

            <div class="reports-stat-card">
                <div class="reports-stat-card-content">
                    <div class="reports-stat-info">
                        <h6 class="reports-stat-label">Most Active City</h6>
                        <h2 class="reports-stat-value reports-text-warning">{{ $topCity ?? 'N/A' }}</h2>
                        <p class="reports-stat-trend"><i class="fas fa-trophy"></i> {{ $topCityCount ?? 0 }} bookings</p>
                    </div>
                    <div class="reports-stat-icon-circle warning-bg"><i class="fas fa-trophy"></i></div>
                </div>
            </div>

            <div class="reports-stat-card">
                <div class="reports-stat-card-content">
                    <div class="reports-stat-info">
                        <h6 class="reports-stat-label">Completion Rate</h6>
                        <h2 class="reports-stat-value reports-text-info">{{ $completionRate ?? 0 }}%</h2>
                        <p class="reports-stat-trend"><i class="fas fa-chart-line"></i> Successfully completed</p>
                    </div>
                    <div class="reports-stat-icon-circle info-bg"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="reports-row-2">
            <!-- Appointment Status Chart -->
            <div class="reports-chart-card">
                <div class="reports-chart-header">
                    <h5 class="reports-chart-title"><i class="fas fa-chart-bar"></i> Appointment Status Summary</h5>
                </div>
                <div class="reports-chart-body">
                    <canvas id="appointmentChart"></canvas>
                </div>
            </div>

            <!-- Services Distribution Chart -->
            <div class="reports-chart-card">
                <div class="reports-chart-header">
                    <h5 class="reports-chart-title"><i class="fas fa-chart-pie"></i> Services Distribution</h5>
                </div>
                <div class="reports-chart-body">
                    <canvas id="servicesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Booking Location Summary -->
        <div class="reports-card reports-mb-4">
            <div class="reports-card-header">
                <h5 class="reports-card-title"><i class="fas fa-map-marker-alt"></i> Booking Summary by City / Location</h5>
                <button onclick="exportToCSV()" class="reports-view-link"><i class="fas fa-download"></i> Export
                    CSV</button>
            </div>
            <div class="reports-card-body reports-table-responsive">
                <table class="reports-table" id="citySummaryTable">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>City / Place</th>
                            <th>Total Bookings</th>
                            <th>Percentage</th>
                            <th>Status Breakdown</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = $citySummary->sum('total_bookings'); @endphp
                        @forelse($citySummary as $index => $summary)
                            <tr>
                                <td><strong>{{ $index + 1 }}</strong></td>
                                <td>
                                    {{ $summary->user_city }}
                                    @if ($index == 0)
                                        <span class="reports-badge reports-badge-warning reports-badge-sm">🏆 Top
                                            Location</span>
                                    @endif
                                </td>
                                <td><span class="reports-stat-number">{{ $summary->total_bookings }}</span></td>
                                <td>
                                    <div class="reports-progress-wrapper">
                                        <div class="reports-progress">
                                            <div class="reports-progress-bar reports-progress-bar-info"
                                                style="width: {{ ($summary->total_bookings / $grandTotal) * 100 }}%">
                                            </div>
                                        </div>
                                        <span
                                            class="reports-progress-percent">{{ round(($summary->total_bookings / $grandTotal) * 100, 1) }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="reports-status-badges">
                                        <span class="reports-badge reports-badge-warning">P:
                                            {{ $summary->pending ?? 0 }}</span>
                                        <span class="reports-badge reports-badge-success">C:
                                            {{ $summary->confirmed ?? 0 }}</span>
                                        <span class="reports-badge reports-badge-info">COM:
                                            {{ $summary->completed ?? 0 }}</span>
                                        <span class="reports-badge reports-badge-danger">CAN:
                                            {{ $summary->cancelled ?? 0 }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($summary->trend > 0)
                                        <span class="reports-trend-up">↑ +{{ $summary->trend }}%</span>
                                    @elseif($summary->trend < 0)
                                        <span class="reports-trend-down">↓ {{ $summary->trend }}%</span>
                                    @else
                                        <span class="reports-trend-neutral">→ 0%</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="reports-empty-state">No booking data available for the selected
                                    filters</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Grand Total</th>
                            <th class="reports-stat-number">{{ $grandTotal }}</th>
                            <th>100%</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Map View -->
        <div class="reports-card reports-mb-4">
            <div class="reports-card-header">
                <h5 class="reports-card-title"><i class="fas fa-map"></i> Geographic Distribution of Bookings</h5>
                <small class="reports-text-muted">📍 Each marker represents bookings from that location. Click marker for
                    details.</small>
            </div>
            <div class="reports-card-body reports-map-container">
                <div id="bookingsMap" class="reports-map"></div>
            </div>
        </div>

        <!-- City Performance Chart -->
        <div class="reports-chart-card reports-mb-4">
            <div class="reports-chart-header">
                <h5 class="reports-chart-title"><i class="fas fa-chart-line"></i> Top 5 Cities - Booking Trends</h5>
            </div>
            <div class="reports-chart-body reports-trend-chart">
                <canvas id="cityTrendChart"></canvas>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="reports-card">
            <div class="reports-card-body reports-action-buttons">
                <button onclick="exportToCSV()" class="reports-btn reports-btn-success"><i class="fas fa-file-csv"></i>
                    Export to CSV</button>
                <button onclick="window.print()" class="reports-btn reports-btn-secondary"><i class="fas fa-print"></i>
                    Print Report</button>
                <button onclick="copyTableData()" class="reports-btn reports-btn-info"><i class="fas fa-copy"></i> Copy
                    Summary</button>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        // Appointment Status Chart
        const ctx1 = document.getElementById('appointmentChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
                datasets: [{
                    label: 'Number of Appointments',
                    data: [{{ $pendingAppointments ?? 0 }}, {{ $confirmedAppointments ?? 0 }},
                        {{ $completedAppointments ?? 0 }}, {{ $cancelledAppointments ?? 0 }}
                    ],
                    backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
                    borderRadius: 8,
                    barPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
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

        // Services Distribution Chart
        const ctx2 = document.getElementById('servicesChart').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: {!! json_encode($serviceLabels ?? []) !!},
                datasets: [{
                    data: {!! json_encode($serviceData ?? []) !!},
                    backgroundColor: ['#0f3b6f', '#c49a2c', '#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                    borderWidth: 0
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
                            boxWidth: 10
                        }
                    },
                    tooltip: {
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
                }
            }
        });

        // City Trend Chart
        const cityTrendData = @json($cityTrendData ?? []);
        const cityLabels = @json($cityTrendLabels ?? []);
        if (cityTrendData && cityLabels.length > 0) {
            new Chart(document.getElementById('cityTrendChart'), {
                type: 'line',
                data: {
                    labels: cityLabels,
                    datasets: cityTrendData
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true
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
                                text: 'Number of Bookings'
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

        // Initialize Leaflet Map
        let map, markersLayer;
        const bookingsLocations = @json($bookingLocations ?? []);
        const psaLat = @json($psaLat ?? 8.4815315);
        const psaLng = @json($psaLng ?? 124.6549067);
        const psaCenter = [psaLat, psaLng];

        function initMap() {
            map = L.map('bookingsMap').setView(psaCenter, 14);

            // Google Hybrid layer
            const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                maxZoom: 20,
            });

            const googleSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                maxZoom: 20,
            });

            const googleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                maxZoom: 20,
            });

            googleHybrid.addTo(map);

            const baseMaps = {
                "Hybrid View": googleHybrid,
                "Satellite View": googleSatellite,
                "Street Map": googleStreets
            };

            L.control.layers(baseMaps).addTo(map);

            // PSA Marker
            const psaIconCustom = L.divIcon({
                html: '<div style="background: linear-gradient(135deg, #0f3b6f, #0a2c52); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #c49a2c; box-shadow: 0 4px 12px rgba(0,0,0,0.3);"><img src="/images/psa.png" style="width: 32px; height: 32px; border-radius: 50%;"></div>',
                iconSize: [44, 44],
                popupAnchor: [0, -22],
                className: 'psa-marker-container'
            });

            const psaMarker = L.marker(psaCenter, {
                icon: psaIconCustom
            }).addTo(map);
            psaMarker.bindPopup(`
            <div style="min-width: 240px;">
                <div style="background: linear-gradient(135deg, #0f3b6f, #0a2c52); color: white; padding: 10px 12px; border-radius: 10px 10px 0 0; margin: -12px -12px 0 -12px;">
                    <strong>PSA Misamis Oriental</strong><br>
                    <small>National ID Registration Center</small>
                </div>
                <div style="padding: 12px;">
                    <div style="background: #f0f4fa; padding: 8px; border-radius: 8px; margin-bottom: 8px;">
                        <strong>📍 Address</strong><br>
                        Capt. Vicente Roa Street, Brgy. 31,<br>Cagayan de Oro City, 9000 Misamis Oriental
                    </div>
                    <div style="display: flex; gap: 10px; font-size: 0.75rem; color: #475569;">
                        <span>📞 0956 576 6106</span>
                        <span>🕒 Mon-Fri 8AM-5PM</span>
                    </div>
                </div>
            </div>
        `, {
                maxWidth: 280
            });

            // Add circles around PSA
            L.circle(psaCenter, {
                color: '#c49a2c',
                fillColor: '#f59e0b',
                fillOpacity: 0.15,
                radius: 120,
                weight: 3
            }).addTo(map);

            L.circle(psaCenter, {
                color: '#0f3b6f',
                fillColor: '#0f3b6f',
                fillOpacity: 0.08,
                radius: 200,
                weight: 1.5,
                dashArray: '8, 6'
            }).addTo(map);

            // Scale control
            L.control.scale({
                metric: true,
                imperial: false,
                position: 'bottomleft'
            }).addTo(map);

            // Marker Cluster Group
            markersLayer = L.markerClusterGroup({
                chunkedLoading: true,
                maxClusterRadius: 60
            });

            bookingsLocations.forEach(location => {
                if (location.lat && location.lng) {
                    let markerColor = '#28a745';
                    if (location.count >= 30) markerColor = '#dc3545';
                    else if (location.count >= 10) markerColor = '#ffc107';

                    const icon = L.divIcon({
                        html: `<div style="background:${markerColor};width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid white;font-weight:bold;color:white;font-size:12px;box-shadow:0 2px 6px rgba(0,0,0,0.2);">${location.count}</div>`,
                        iconSize: [30, 30],
                        popupAnchor: [0, -15]
                    });

                    const marker = L.marker([parseFloat(location.lat), parseFloat(location.lng)], {
                        icon
                    });
                    marker.bindPopup(`
                    <div>
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 12px; border-radius: 8px 8px 0 0; margin: -12px -12px 0 -12px;">
                            <strong>📍 ${escapeHtml(location.city)}</strong>
                        </div>
                        <div style="padding: 10px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>Total Bookings:</span><strong>${location.count}</strong></div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>Pending:</span><strong style="color:#f59e0b;">${location.pending || 0}</strong></div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>Confirmed:</span><strong style="color:#10b981;">${location.confirmed || 0}</strong></div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>Completed:</span><strong style="color:#3b82f6;">${location.completed || 0}</strong></div>
                            <div style="display: flex; justify-content: space-between;"><span>Cancelled:</span><strong style="color:#ef4444;">${location.cancelled || 0}</strong></div>
                        </div>
                        <div style="padding: 8px 12px; background: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center;">
                            <button class="reports-btn reports-btn-sm reports-btn-primary" onclick="window.location.href='/admin/appointments?city=${encodeURIComponent(location.city)}'">View Bookings →</button>
                        </div>
                    </div>
                `, {
                        maxWidth: 300
                    });
                    markersLayer.addLayer(marker);
                }
            });
            map.addLayer(markersLayer);

            // Legend
            const legend = L.control({
                position: 'bottomright'
            });
            legend.onAdd = function() {
                const div = L.DomUtil.create('div', 'map-legend');
                div.innerHTML = `
                <h6 style="margin:0 0 8px 0;font-size:12px;">📊 Booking Density</h6>
                <div style="display:flex;align-items:center;margin-bottom:5px;"><div style="width:16px;height:16px;border-radius:50%;background:#28a745;margin-right:8px;"></div><span>Low (1-9)</span></div>
                <div style="display:flex;align-items:center;margin-bottom:5px;"><div style="width:16px;height:16px;border-radius:50%;background:#ffc107;margin-right:8px;"></div><span>Medium (10-29)</span></div>
                <div style="display:flex;align-items:center;margin-bottom:5px;"><div style="width:16px;height:16px;border-radius:50%;background:#dc3545;margin-right:8px;"></div><span>High (30+)</span></div>
                <div style="display:flex;align-items:center;margin-top:8px;padding-top:5px;border-top:1px solid #ddd;"><div style="width:16px;height:16px;border-radius:50%;background:#0f3b6f;margin-right:8px;border:2px solid #c49a2c;"></div><span>PSA Center</span></div>
            `;
                return div;
            };
            legend.addTo(map);
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        function exportToCSV() {
            const table = document.getElementById('citySummaryTable');
            let csv = [];
            table.querySelectorAll('tr').forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const rowData = Array.from(cells).map(cell =>
                    `"${cell.innerText.trim().replace(/[^\w\s,.-]/g, '')}"`);
                csv.push(rowData.join(','));
            });
            const blob = new Blob([csv.join('\n')], {
                type: 'text/csv'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `booking_report_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            URL.revokeObjectURL(url);
            alert('✅ Report exported to CSV successfully!');
        }

        function copyTableData() {
            const table = document.getElementById('citySummaryTable');
            const range = document.createRange();
            range.selectNode(table);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            alert('✅ Summary table copied to clipboard!');
            window.getSelection().removeAllRanges();
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('bookingsMap')) setTimeout(initMap, 100);
        });
        window.addEventListener('resize', () => {
            if (map) setTimeout(() => map.invalidateSize(), 200);
        });
    </script>
@endsection
