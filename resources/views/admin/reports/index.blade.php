{{-- resources/views/admin/reports/index.blade.php --}}
@extends('layouts.admin')


@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4">Reports & Analytics</h1>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filter Reports</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.index') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ $startDate ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control"
                                value="{{ $endDate ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label>Status</label>
                            <select name="status" class="form-control">
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
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">Generate Report</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Bookings</h5>
                        <h2 class="mb-0">{{ $totalBookings ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Unique Locations</h5>
                        <h2 class="mb-0">{{ $uniqueLocations ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Most Active City</h5>
                        <h2 class="mb-0">{{ $topCity ?? 'N/A' }}</h2>
                        <small>{{ $topCityCount ?? 0 }} bookings</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Completion Rate</h5>
                        <h2 class="mb-0">{{ $completionRate ?? 0 }}%</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Appointment Status Chart -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Appointment Status Summary</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="appointmentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Services Distribution Chart -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Services Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="servicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Location Summary by City -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>📍 Booking Summary by City / Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="citySummaryTable">
                                <thead class="table-dark">
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
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $summary->user_city }}</strong>
                                                @if ($index == 0)
                                                    <span class="badge bg-warning ms-2">🏆 Top Location</span>
                                                @endif
                                            </td>
                                            <td><span class="fw-bold">{{ $summary->total_bookings }}</span></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1" style="height: 8px;">
                                                        <div class="progress-bar bg-info"
                                                            style="width: {{ ($summary->total_bookings / $grandTotal) * 100 }}%">
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="ms-2">{{ round(($summary->total_bookings / $grandTotal) * 100, 1) }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">P: {{ $summary->pending ?? 0 }}</span>
                                                <span class="badge bg-success">C: {{ $summary->confirmed ?? 0 }}</span>
                                                <span class="badge bg-info">COM: {{ $summary->completed ?? 0 }}</span>
                                                <span class="badge bg-danger">CAN: {{ $summary->cancelled ?? 0 }}</span>
                                            </td>
                                            <td>
                                                @if ($summary->trend > 0)
                                                    <span class="text-success">↑ +{{ $summary->trend }}%</span>
                                                @elseif($summary->trend < 0)
                                                    <span class="text-danger">↓ {{ $summary->trend }}%</span>
                                                @else
                                                    <span class="text-muted">→ 0%</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No booking data available for
                                                the selected filters</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="2">Grand Total</th>
                                        <th>{{ $grandTotal }}</th>
                                        <th>100%</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map View of Booking Locations -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>🗺️ Geographic Distribution of Bookings</h5>
                    </div>
                    <div class="card-body">
                        <div id="bookingsMap" style="height: 500px; width: 100%; border-radius: 8px; z-index: 1;"></div>
                        <small class="text-muted mt-2 d-block">📍 Each marker represents bookings from that location. Click
                            marker for details.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- City Performance Over Time -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>📈 Top 5 Cities - Booking Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="cityTrendChart" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button onclick="exportToCSV()" class="btn btn-success">📊 Export to CSV</button>
                        <button onclick="window.print()" class="btn btn-secondary">🖨️ Print Report</button>
                        <button onclick="copyTableData()" class="btn btn-info">📋 Copy Summary</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
        <style>
            .leaflet-bottom {
                bottom: -18px !important;
            }

            #bookingsMap {
                background-color: #f8f9fa;
                border-radius: 8px;
                overflow: hidden;
            }

            .custom-popup .leaflet-popup-content-wrapper {
                border-radius: 12px;
                padding: 0;
                overflow: hidden;
            }

            .popup-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 12px 16px;
            }

            .popup-body {
                padding: 16px;
            }

            .popup-stat {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                padding: 4px 0;
                border-bottom: 1px solid #eee;
            }

            .popup-footer {
                padding: 12px 16px;
                background: #f8f9fa;
                text-align: center;
            }

            .map-legend {
                background: rgba(255, 255, 255, 0.95);
                padding: 12px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            }

            .legend-color {
                width: 18px;
                height: 18px;
                border-radius: 50%;
                display: inline-block;
                margin-right: 8px;
            }

            .legend-color.low {
                background: #28a745;
            }

            .legend-color.medium {
                background: #ffc107;
            }

            .legend-color.high {
                background: #dc3545;
            }

            .legend-color.psa {
                background: #dc3545;
            }

            @media print {

                .btn,
                .leaflet-control {
                    display: none !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
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
                        backgroundColor: ['#ffc107', '#28a745', '#17a2b8', '#dc3545'],
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
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
                        backgroundColor: ['#667eea', '#764ba2', '#28a745', '#17a2b8', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
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
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Bookings'
                                }
                            }
                        }
                    }
                });
            }

            // Initialize Leaflet Map with correct PSA coordinates
            let map, markersLayer;
            const bookingsLocations = @json($bookingLocations ?? []);
            const psaLat = @json($psaLat ?? 8.4815315);
            const psaLng = @json($psaLng ?? 124.6549067);
            const psaCenter = [psaLat, psaLng];

            function initMap() {
                map = L.map('bookingsMap').setView(psaCenter, 14);

                // Google Hybrid layer (matching landing page)
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

                // PSA Marker with custom icon (matching landing page)
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

                // Add circles around PSA (matching landing page)
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
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>Pending:</span><strong style="color:#ffc107;">${location.pending || 0}</strong></div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>Confirmed:</span><strong style="color:#28a745;">${location.confirmed || 0}</strong></div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>Completed:</span><strong style="color:#17a2b8;">${location.completed || 0}</strong></div>
                        <div style="display: flex; justify-content: space-between;"><span>Cancelled:</span><strong style="color:#dc3545;">${location.cancelled || 0}</strong></div>
                    </div>
                    <div style="padding: 8px 12px; background: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center;">
                        <button class="btn btn-sm btn-primary" onclick="window.location.href='/admin/appointments?city=${encodeURIComponent(location.city)}'">View Bookings →</button>
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
    @endpush
@endsection
