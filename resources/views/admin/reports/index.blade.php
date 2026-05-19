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
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <div class="reports-date-display">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ now()->format('l, F j, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="reports-card reports-mb-4">
            <div class="reports-card-header">
                <h5 class="reports-card-title"><i class="fas fa-filter"></i> Filter Reports</h5>
                <!-- Export Buttons -->
                <div class="export-buttons">
                    <a href="{{ route('admin.reports.export.pdf', request()->query()) }}"
                        class="reports-btn reports-btn-pdf">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    <a href="{{ route('admin.reports.export.excel', request()->query()) }}"
                        class="reports-btn reports-btn-excel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
                </div>
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
                        <h6 class="reports-stat-label">Most Book Place</h6>
                        <h2 class="reports-stat-value reports-text-warning">{{ $topCity ?? 'N/A' }}</h2>
                        <p class="reports-stat-trend"> {{ $topCityCount ?? 0 }} bookings</p>
                    </div>
                    <div class="custom-marker-icon"
                        style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b, #d97706); "><i
                            class="fas fa-calendar-check" style="font-size:30px;"></i></div>
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
            <div class="reports-chart-card">
                <div class="reports-chart-header">
                    <h5 class="reports-chart-title"><i class="fas fa-chart-bar"></i> Appointment Status Summary</h5>
                </div>
                <div class="reports-chart-body">
                    <canvas id="appointmentChart"></canvas>
                </div>
            </div>

            <div class="reports-chart-card">
                <div class="reports-chart-header">
                    <h5 class="reports-chart-title"><i class="fas fa-chart-pie"></i> Services Distribution</h5>
                </div>
                <div class="reports-chart-body">
                    <canvas id="servicesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Map View -->
        <div class="reports-card reports-mb-4">
            <div class="reports-card-header">
                <h5 class="reports-card-title"><i class="fas fa-map"></i> Geographic Distribution of Bookings</h5>
                <small>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="custom-marker-icon"
                            style="width:24px; height:24px; background: linear-gradient(135deg, #f59e0b, #d97706); "><i
                                class="fas fa-calendar-check" style="font-size:10px;"></i></div>
                        <span>Click on markers to view appointment details</span>
                    </div>
                </small>
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
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <style>
        .leaflet-bottom {
            bottom: -30px;
        }

        .leaflet-right {
            right: -10px;
        }

    </style>

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
                        position: 'top'
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
        const cityTrendLabels = @json($cityTrendLabels ?? []);
        if (cityTrendData && cityTrendLabels.length > 0) {
            new Chart(document.getElementById('cityTrendChart'), {
                type: 'line',
                data: {
                    labels: cityTrendLabels,
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

        // Map initialization
        let map, markersLayer;
        let ctrlPressed = false;
        const individualBookings = @json($individualBookings ?? []);
        const psaLat = @json($psaLat ?? 8.4815315);
        const psaLng = @json($psaLng ?? 124.6549067);
        const psaCenter = [psaLat, psaLng];

        function getMarkerColor(status) {
            switch (status) {
                case 'pending':
                    return 'pending';
                case 'confirmed':
                    return 'confirmed';
                case 'completed':
                    return 'completed';
                case 'cancelled':
                    return 'cancelled';
                default:
                    return '';
            }
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

        function initMap() {
            map = L.map('bookingsMap').setView(psaCenter, 13);

            const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                maxZoom: 20
            });
            const googleSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                maxZoom: 20
            });
            const googleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                maxZoom: 20
            });
            googleHybrid.addTo(map);
            L.control.layers({
                "Hybrid View": googleHybrid,
                "Satellite View": googleSatellite,
                "Street Map": googleStreets
            }).addTo(map);

            const psaIconCustom = L.divIcon({
                html: '<img src="{{ asset('images/psa.png') }}" class="psa-icon-img" alt="PSA" style="width:44px;height:44px;border-radius:50%;border:2px solid #c49a2c;">',
                iconSize: [44, 44],
                popupAnchor: [0, -22],
                className: 'psa-marker-container'
            });
            L.marker(psaCenter, {
                icon: psaIconCustom
            }).addTo(map);
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
            L.control.scale({
                metric: true,
                imperial: false,
                position: 'bottomleft'
            }).addTo(map);

            markersLayer = L.markerClusterGroup({
                chunkedLoading: true,
                maxClusterRadius: 60,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
                spiderfyOnMaxZoom: true,
                removeOutsideVisibleBounds: true
            });

            individualBookings.forEach(booking => {
                if (booking.lat && booking.lng) {
                    const icon = L.divIcon({
                        html: `<div class="custom-marker-icon" style="width:32px;height:32px;background: linear-gradient(135deg, #f59e0b, #d97706); border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="fas fa-calendar-check" style="font-size:16px; color:white;"></i></div>`,
                        iconSize: [32, 32],
                        popupAnchor: [0, -16],
                        className: 'marker-container'
                    });
                    const marker = L.marker([parseFloat(booking.lat), parseFloat(booking.lng)], {
                        icon
                    });
                    const clients = booking.clients || [];
                    let clientsHtml = '';
                    if (clients.length > 0) {
                        clientsHtml = '<div class="popup-clients-list">';
                        clients.forEach(client => {
                            let serviceName = client.service === 'reg' ? 'National ID Registration' : (
                                client.service === 'updating' ? 'Updating' : 'Status Inquiry');
                            clientsHtml +=
                                `<div class="popup-client-item"><div><div class="popup-client-name">${escapeHtml(client.first_name)} ${escapeHtml(client.last_name)}</div><div class="popup-client-service">${serviceName}</div></div><span class="popup-client-badge">${client.sex || 'N/A'}</span></div>`;
                        });
                        clientsHtml += '</div>';
                    } else {
                        clientsHtml =
                            '<div class="popup-client-item" style="text-align:center; color:#999;">No applicants</div>';
                    }
                    const statusText = booking.status.charAt(0).toUpperCase() + booking.status.slice(1);
                    const appointmentDate = new Date(booking.appointment_date).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                    const popupContent =
                        `<div style="min-width:260px;"><div class="popup-header" style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:8px;"><span class="popup-appointment"><i class="fas fa-hashtag"></i> ${escapeHtml(booking.appointment_number)}</span><span style="padding:2px 8px; border-radius:12px; font-size:11px; background:${booking.status === 'completed' ? '#10b981' : booking.status === 'confirmed' ? '#3b82f6' : booking.status === 'pending' ? '#f59e0b' : '#ef4444'}; color:white;">${statusText}</span></div><div class="popup-body"><div class="popup-location" style="margin-bottom:5px;"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(booking.user_city || 'Unknown Location')}</div><div class="popup-info-row" style="margin-bottom:5px;"><i class="fas fa-calendar-alt"></i> <strong>${appointmentDate}</strong></div><div class="popup-info-row" style="margin-bottom:5px;"><i class="fas fa-user-circle"></i> ${escapeHtml(booking.contact_name)}</div><div class="popup-info-row" style="margin-bottom:10px;"><i class="fas fa-phone-alt"></i> ${escapeHtml(booking.contact_mobile)}</div><div class="popup-clients"><div class="popup-clients-header" style="font-weight:bold; margin-bottom:5px;"><i class="fas fa-users"></i> Applicants (${clients.length})</div>${clientsHtml}</div></div></div>`;
                    marker.bindPopup(popupContent, {
                        maxWidth: 320,
                        minWidth: 280,
                        className: 'custom-popup'
                    });
                    markersLayer.addLayer(marker);
                }
            });
            map.addLayer(markersLayer);

            map.scrollWheelZoom.disable();
            const CtrlZoomControl = L.Control.extend({
                options: {
                    position: 'bottomright'
                },
                onAdd: function() {
                    const container = L.DomUtil.create('div', 'ctrl-zoom-hint');
                    container.innerHTML =
                        `<kbd>Ctrl</kbd><span>+</span><i class="fas fa-mouse-pointer"></i><span>Scroll to zoom map</span>`;
                    return container;
                }
            });
            map.addControl(new CtrlZoomControl());
            const activeIndicator = document.createElement('div');
            activeIndicator.className = 'ctrl-zoom-active';
            activeIndicator.innerHTML = '<i class="fas fa-search"></i> Ctrl + Scroll active';
            document.body.appendChild(activeIndicator);

            function handleKeyDown(e) {
                if (e.key === 'Control' || e.key === 'Meta' || e.keyCode === 17 || e.keyCode === 91) {
                    ctrlPressed = true;
                    if (map && !map.scrollWheelZoom.enabled()) {
                        map.scrollWheelZoom.enable();
                        activeIndicator.style.display = 'flex';
                        setTimeout(() => {
                            if (activeIndicator) activeIndicator.style.opacity = '1';
                        }, 10);
                    }
                }
            }

            function handleKeyUp(e) {
                if (e.key === 'Control' || e.key === 'Meta' || e.keyCode === 17 || e.keyCode === 91) {
                    ctrlPressed = false;
                    if (map && map.scrollWheelZoom.enabled()) {
                        map.scrollWheelZoom.disable();
                        activeIndicator.style.opacity = '0';
                        setTimeout(() => {
                            if (activeIndicator) activeIndicator.style.display = 'none';
                        }, 300);
                    }
                }
            }
            document.addEventListener('keydown', handleKeyDown);
            document.addEventListener('keyup', handleKeyUp);
            window.addEventListener('blur', function() {
                ctrlPressed = false;
                if (map && map.scrollWheelZoom.enabled()) {
                    map.scrollWheelZoom.disable();
                    activeIndicator.style.display = 'none';
                }
            });
            const mapContainer = document.getElementById('bookingsMap');
            if (mapContainer) {
                mapContainer.addEventListener('wheel', function(e) {
                    if (!ctrlPressed) {
                        e.preventDefault();
                        return false;
                    }
                }, {
                    passive: false
                });
            }

            const legend = L.control({
                position: 'bottomleft'
            });
            legend.onAdd = function() {
                const div = L.DomUtil.create('div', 'map-legend');
                div.innerHTML =
                    `<div style="display:flex; align-items:center; gap:8px; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 8px;"><div class="custom-marker-icon" style="width:24px; height:24px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius:50%; display:flex; align-items:center; justify-content:center;"><i class="fas fa-calendar-check" style="font-size:12px; color:white;"></i></div><span style="color:white;">Appointment</span></div><div style="display:flex; align-items:center; gap:8px; margin-left:12px; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 8px;"><img src="{{ asset('images/psa.png') }}" style="width:24px;height:24px;border-radius:50%;border:2px solid #c49a2c;"><span style="color:white;">PSA Center</span></div>`;
                return div;
            };
            legend.addTo(map);
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('bookingsMap')) {
                setTimeout(initMap, 100);
            }
        });
        window.addEventListener('resize', () => {
            if (map) setTimeout(() => map.invalidateSize(), 200);
        });
    </script>
@endsection
