{{-- resources/views/operator/reports/index.blade.php --}}
@extends('layouts.operator')

@section('content')
    <div class="reports-container">
        <!-- Header Section -->
        <div class="reports-welcome-section">
            <div>
                <h1 class="reports-title">Reports & Analytics</h1>
                <p class="reports-subtitle">Confirmed and Completed Appointments Report</p>
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
                <div class="reports-action-buttons">
                    <button onclick="exportToPDF()" class="reports-btn reports-btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button onclick="exportToExcel()" class="reports-btn reports-btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="reports-card-body">
                <form method="GET" action="{{ route('operator.reports.index') }}" id="filterForm">
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
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                            </select>
                        </div>
                        <div class="reports-form-group reports-form-group-btn">
                            <button type="submit" class="reports-btn reports-btn-primary reports-btn-block">
                                <i class="fas fa-chart-line"></i> Generate Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="reports-stats-grid reports-mb-4">
            <div class="reports-stat-card confirmed-card">
                <div class="reports-stat-card-content">
                    <div class="reports-stat-info">
                        <h6 class="reports-stat-label">Confirmed Appointments</h6>
                        <h2 class="reports-stat-value reports-text-success">{{ $confirmedAppointments ?? 0 }}</h2>
                        <p class="reports-stat-trend"><i class="fas fa-check-circle"></i> Ready for service</p>
                    </div>
                    <div class="reports-stat-icon-circle success-bg"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>

            <div class="reports-stat-card completed-card">
                <div class="reports-stat-card-content">
                    <div class="reports-stat-info">
                        <h6 class="reports-stat-label">Completed Appointments</h6>
                        <h2 class="reports-stat-value reports-text-info">{{ $completedAppointments ?? 0 }}</h2>
                        <p class="reports-stat-trend"><i class="fas fa-clipboard-check"></i> Successfully processed</p>
                    </div>
                    <div class="reports-stat-icon-circle info-bg"><i class="fas fa-clipboard-check"></i></div>
                </div>
            </div>

            <div class="reports-stat-card total-card">
                <div class="reports-stat-card-content">
                    <div class="reports-stat-info">
                        <h6 class="reports-stat-label">Total</h6>
                        <h2 class="reports-stat-value reports-text-primary">
                            {{ ($confirmedAppointments ?? 0) + ($completedAppointments ?? 0) }}
                        </h2>
                        <p class="reports-stat-trend"><i class="fas fa-calendar-alt"></i> Processed appointments</p>
                    </div>
                    <div class="reports-stat-icon-circle primary-bg"><i class="fas fa-calendar-alt"></i></div>
                </div>
            </div>
        </div>

        <!-- Status Chart - Confirmed and Completed -->
        <div class="reports-chart-card reports-mb-4">
            <div class="reports-chart-header">
                <h5 class="reports-chart-title"><i class="fas fa-chart-bar"></i> Confirmed vs Completed Appointments</h5>
            </div>
            <div class="reports-chart-body">
                <canvas id="appointmentChart"></canvas>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="reports-card">
            <div class="reports-card-header">
                <h5 class="reports-card-title"><i class="fas fa-list-alt"></i> Appointment Details</h5>
            </div>
            <div class="reports-card-body">
                <div class="table-responsive">
                    <table class="reports-table" id="appointmentsTable">
                        <thead>
                            <tr>
                                <th>Appointment #</th>
                                <th>Date</th>
                                <th>Time Slot</th>
                                <th>Contact Person</th>
                                <th>Contact Mobile</th>
                                <th>Status</th>
                                <th># of Clients</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments ?? [] as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</td>
                                    <td>
                                        @if ($appointment->timeSlot)
                                            {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('g:i A') }}
                                            -
                                            {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('g:i A') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $appointment->contact_name }}</td>
                                    <td>{{ $appointment->contact_mobile }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $appointment->status }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $appointment->clients->count() }}</td>
                                    <td>{{ $appointment->user_city ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No appointments found for the selected period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if (isset($appointments) && method_exists($appointments, 'links') && $appointments->total() > 0)
                    <div class="reports-pagination-wrapper">
                        <div class="reports-pagination-info">
                            Showing {{ $appointments->firstItem() ?? 0 }} to {{ $appointments->lastItem() ?? 0 }} of
                            {{ $appointments->total() }} appointments
                        </div>
                        <div class="reports-simple-pagination">
                            @if ($appointments->onFirstPage())
                                <button class="reports-pagination-btn reports-pagination-disabled" disabled>
                                    <i class="fas fa-chevron-left"></i> Previous
                                </button>
                            @else
                                <a href="{{ $appointments->previousPageUrl() . '&' . http_build_query(request()->except('page')) }}"
                                    class="reports-pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            @endif

                            <span class="reports-pagination-current">
                                Page {{ $appointments->currentPage() }} of {{ $appointments->lastPage() }}
                            </span>

                            @if ($appointments->hasMorePages())
                                <a href="{{ $appointments->nextPageUrl() . '&' . http_build_query(request()->except('page')) }}"
                                    class="reports-pagination-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <button class="reports-pagination-btn reports-pagination-disabled" disabled>
                                    Next <i class="fas fa-chevron-right"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- City Summary Table -->
        <div class="reports-card reports-mt-4">
            <div class="reports-card-header">
                <h5 class="reports-card-title"><i class="fas fa-chart-simple"></i> Summary by City</h5>
            </div>
            <div class="reports-card-body">
                <div class="table-responsive">
                    <table class="reports-table" id="citySummaryTable">
                        <thead>
                            <tr>
                                <th>City</th>
                                <th>Confirmed</th>
                                <th>Completed</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($citySummary ?? [] as $city)
                                <tr>
                                    <td>{{ $city->user_city }}</td>
                                    <td>{{ $city->confirmed ?? 0 }}</td>
                                    <td>{{ $city->completed ?? 0 }}</td>
                                    <td><strong>{{ ($city->confirmed ?? 0) + ($city->completed ?? 0) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No city data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>

    <script>
        // Get current filter values for export
        function getCurrentFilters() {
            const startDate = document.querySelector('input[name="start_date"]')?.value || '';
            const endDate = document.querySelector('input[name="end_date"]')?.value || '';
            const status = document.querySelector('select[name="status"]')?.value || '';
            return {
                startDate,
                endDate,
                status
            };
        }

        // Convert date to readable format
        function formatDate(dateString) {
            const date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
        }

        // Export to PDF with PSA style
        async function exportToPDF() {
            const table = document.getElementById('appointmentsTable');
            const cityTable = document.getElementById('citySummaryTable');

            if (!table || table.querySelectorAll('tbody tr').length === 0) {
                alert('No data to export');
                return;
            }

            const filters = getCurrentFilters();
            const confirmedCount = {{ $confirmedAppointments ?? 0 }};
            const completedCount = {{ $completedAppointments ?? 0 }};
            const totalCount = confirmedCount + completedCount;

            // Create report wrapper
            const pdfContent = document.createElement('div');
            pdfContent.style.cssText = `
                margin: 0;
                padding: 0;
                background: white;
                font-family: 'Arial', 'Helvetica', sans-serif;
                line-height: 1.3;
            `;

            // Inner container with margins
            const inner = document.createElement('div');
            inner.style.cssText = `
                margin: 0 auto;
                padding: 50px 60px 50px 60px;
                max-width: 1200px;
                box-sizing: border-box;
            `;
            pdfContent.appendChild(inner);

            // === HEADER: Philippine Statistics Authority ===
            const header = document.createElement('div');
            header.style.cssText = `
                text-align: center;
                margin-bottom: 30px;
            `;
            header.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 10px;">
                    <div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <img src="/images/psa.png" alt="PSA Logo" style="max-width: 100%; max-height: 100%;" onerror="this.style.display='none'">
                    </div>
                    <div>
                        <div style="font-size: 22px; font-weight: bold; letter-spacing: 1px;">PHILIPPINE STATISTICS AUTHORITY</div>
                        <div style="font-size: 11px; margin-top: 5px;">Civil Registration</div>
                    </div>
                    <div style="width: 60px;"></div>
                </div>
                <div style="border-bottom: 2px solid #000; margin-top: 10px;"></div>
            `;
            inner.appendChild(header);

            // Report Title with Date Range
            const title = document.createElement('div');
            title.style.cssText = `
                text-align: center;
                margin: 25px 0 20px 0;
            `;
            title.innerHTML = `
                <h2 style="margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase;">APPOINTMENTS REPORT</h2>
                <p style="margin: 5px 0 0 0; font-size: 12px;">Period: ${filters.startDate || 'N/A'} — ${filters.endDate || 'N/A'}</p>
            `;
            inner.appendChild(title);

            // === SUMMARY BY CITY / DISTRICT and SUMMARY STATISTICS side by side ===
            const summaryFlex = document.createElement('div');
            summaryFlex.style.cssText = `
                display: flex;
                gap: 40px;
                margin: 25px 0 30px 0;
            `;

            // Left side: Summary by City / District Table
            const cityDiv = document.createElement('div');
            cityDiv.style.cssText = `
                flex: 1.5;
            `;

            if (cityTable && cityTable.querySelectorAll('tbody tr').length > 0) {
                const cityTitle = document.createElement('div');
                cityTitle.style.cssText = `
                    font-size: 13px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    text-transform: uppercase;
                `;
                cityTitle.innerHTML = `SUMMARY BY CITY / DISTRICT`;
                cityDiv.appendChild(cityTitle);

                const cloneCityTable = cityTable.cloneNode(true);
                cloneCityTable.style.cssText = `
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 11px;
                `;

                const cityThCells = cloneCityTable.querySelectorAll('th');
                cityThCells.forEach(th => {
                    th.style.cssText = `
                        background: #f2f2f2;
                        padding: 8px 10px;
                        text-align: left;
                        font-weight: bold;
                        border: 1px solid #000;
                    `;
                });

                const cityTdCells = cloneCityTable.querySelectorAll('td');
                cityTdCells.forEach(td => {
                    td.style.cssText = `
                        padding: 6px 10px;
                        border: 1px solid #000;
                    `;
                });

                cityDiv.appendChild(cloneCityTable);
            } else {
                cityDiv.innerHTML = `<p style="font-size: 11px; color: #999;">No city data available.</p>`;
            }
            summaryFlex.appendChild(cityDiv);

            // Right side: Summary Statistics
            const statsDiv = document.createElement('div');
            statsDiv.style.cssText = `
                flex: 1;
            `;

            const statsTitle = document.createElement('div');
            statsTitle.style.cssText = `
                font-size: 13px;
                font-weight: bold;
                margin-bottom: 10px;
                text-transform: uppercase;
            `;
            statsTitle.innerHTML = `SUMMARY STATISTICS`;
            statsDiv.appendChild(statsTitle);

            const statsTable = document.createElement('table');
            statsTable.style.cssText = `
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
            `;
            statsTable.innerHTML = `
                <tr>
                    <td style="border: 1px solid #000; padding: 8px 10px; font-weight: bold;">Confirmed:</td>
                    <td style="border: 1px solid #000; padding: 8px 10px; text-align: right;">${confirmedCount}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px 10px; font-weight: bold;">Completed:</td>
                    <td style="border: 1px solid #000; padding: 8px 10px; text-align: right;">${completedCount}</td>
                </tr>
                <tr style="background: #f9f9f9;">
                    <td style="border: 1px solid #000; padding: 8px 10px; font-weight: bold;">TOTAL:</td>
                    <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; font-weight: bold;">${totalCount}</td>
                </tr>
            `;
            statsDiv.appendChild(statsTable);
            summaryFlex.appendChild(statsDiv);

            inner.appendChild(summaryFlex);

            // === APPOINTMENT DETAILS TABLE ===
            const tableTitle = document.createElement('div');
            tableTitle.style.cssText = `
                font-size: 13px;
                font-weight: bold;
                margin: 20px 0 10px 0;
                text-transform: uppercase;
            `;
            tableTitle.innerHTML = `APPOINTMENT DETAILS`;
            inner.appendChild(tableTitle);

            // Clone and style table
            const cloneTable = table.cloneNode(true);
            cloneTable.style.cssText = `
                width: 100%;
                border-collapse: collapse;
                font-size: 10px;
                margin-bottom: 20px;
            `;

            // Style headers
            const thCells = cloneTable.querySelectorAll('th');
            thCells.forEach(th => {
                th.style.cssText = `
                    background: #f2f2f2;
                    padding: 8px 6px;
                    text-align: left;
                    font-weight: bold;
                    border: 1px solid #000;
                    font-size: 10px;
                `;
            });

            // Style cells
            const tdCells = cloneTable.querySelectorAll('td');
            tdCells.forEach(td => {
                td.style.cssText = `
                    padding: 6px;
                    border: 1px solid #000;
                    color: #000;
                    font-size: 9px;
                `;
            });

            // Process status badges
            cloneTable.querySelectorAll('.status-badge').forEach(badge => {
                const statusText = badge.innerText;
                const parent = badge.parentNode;
                parent.textContent = statusText;
            });

            inner.appendChild(cloneTable);

            // Footer with generation info
            const footer = document.createElement('div');
            footer.style.cssText = `
                margin-top: 25px;
                padding-top: 10px;
                border-top: 1px solid #ccc;
                font-size: 8px;
                color: #666;
                text-align: center;
            `;
            footer.innerHTML = `
                <p style="margin: 2px 0;">Philippine Statistics Authority | Civil Registration</p>
                <p style="margin: 2px 0;">Generated: ${new Date().toLocaleString()} | Page 1 of 1</p>
            `;
            inner.appendChild(footer);

            // PDF configuration
            const opt = {
                margin: [0.5, 0.5, 0.5, 0.5],
                filename: `appointment_report_${filters.startDate || 'all'}_to_${filters.endDate || 'all'}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    letterRendering: true,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(opt).from(pdfContent).save();
        }

        // Export to Excel with PSA style
        function exportToExcel() {
            const table = document.getElementById('appointmentsTable');
            const cityTable = document.getElementById('citySummaryTable');

            if (!table || table.querySelectorAll('tbody tr').length === 0) {
                alert('No data to export');
                return;
            }

            const filters = getCurrentFilters();
            const confirmedCount = {{ $confirmedAppointments ?? 0 }};
            const completedCount = {{ $completedAppointments ?? 0 }};
            const totalCount = confirmedCount + completedCount;

            // Get table data
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(th.innerText.trim());
            });

            const rows = [];
            table.querySelectorAll('tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => {
                    const badge = cell.querySelector('.status-badge');
                    let text = badge ? badge.innerText.trim() : cell.innerText.trim();
                    rowData.push(text);
                });
                rows.push(rowData);
            });

            // Get city data
            const cityHeaders = [];
            const cityRows = [];
            if (cityTable && cityTable.querySelectorAll('tbody tr').length > 0) {
                cityTable.querySelectorAll('thead th').forEach(th => {
                    cityHeaders.push(th.innerText.trim());
                });
                cityTable.querySelectorAll('tbody tr').forEach(row => {
                    const rowData = [];
                    row.querySelectorAll('td').forEach(cell => {
                        rowData.push(cell.innerText.trim());
                    });
                    cityRows.push(rowData);
                });
            }

            // Build Excel data
            const wsData = [];

            // Header
            wsData.push(['PHILIPPINE STATISTICS AUTHORITY']);
            wsData.push(['Civil Registration and Vital Statistics Division']);
            wsData.push([]);
            wsData.push(['CONFIRMED AND COMPLETED APPOINTMENTS REPORT']);
            wsData.push([`Period: ${filters.startDate || 'N/A'} — ${filters.endDate || 'N/A'}`]);
            wsData.push([]);
            wsData.push([]);

            // Summary by City and Summary Statistics side by side
            // Row with two sections
            if (cityRows.length > 0) {
                wsData.push(['SUMMARY BY CITY / DISTRICT', '', '', '', 'SUMMARY STATISTICS', '', '', '']);
                wsData.push([...cityHeaders, '', '', 'Metric', 'Count', '', '']);
                for (let i = 0; i < Math.max(cityRows.length, 3); i++) {
                    const cityRow = i < cityRows.length ? cityRows[i] : ['', '', '', ''];
                    if (i === 0) {
                        wsData.push([...cityRow, '', '', 'Confirmed Appointments:', confirmedCount, '', '']);
                    } else if (i === 1) {
                        wsData.push([...cityRow, '', '', 'Completed Appointments:', completedCount, '', '']);
                    } else if (i === 2) {
                        wsData.push([...cityRow, '', '', 'TOTAL:', totalCount, '', '']);
                    } else {
                        wsData.push([...cityRow, '', '', '', '', '', '']);
                    }
                }
            } else {
                wsData.push(['SUMMARY STATISTICS']);
                wsData.push(['Confirmed Appointments:', confirmedCount]);
                wsData.push(['Completed Appointments:', completedCount]);
                wsData.push(['TOTAL:', totalCount]);
            }
            wsData.push([]);
            wsData.push([]);

            // Appointment Details
            wsData.push(['APPOINTMENT DETAILS']);
            wsData.push(headers);
            rows.forEach(row => wsData.push(row));
            wsData.push([]);
            wsData.push([]);

            // Footer
            wsData.push(['Philippine Statistics Authority | Civil Registration and Vital Statistics Division']);
            wsData.push([`Generated: ${new Date().toLocaleString()}`]);

            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(wsData);

            // Set column widths
            ws['!cols'] = [{
                    wch: 20
                }, // Column A
                {
                    wch: 14
                }, // Column B
                {
                    wch: 18
                }, // Column C
                {
                    wch: 20
                }, // Column D
                {
                    wch: 18
                }, // Column E
                {
                    wch: 12
                }, // Column F
                {
                    wch: 12
                }, // Column G
                {
                    wch: 18
                } // Column H
            ];

            // Create workbook
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Appointments Report');

            const fileName = `appointment_report_${filters.startDate || 'start'}_to_${filters.endDate || 'end'}.xlsx`;
            XLSX.writeFile(wb, fileName);
        }

        // Appointment Status Chart
        const ctx1 = document.getElementById('appointmentChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Confirmed', 'Completed'],
                datasets: [{
                    label: 'Number of Appointments',
                    data: [{{ $confirmedAppointments ?? 0 }}, {{ $completedAppointments ?? 0 }}],
                    backgroundColor: ['#10b981', '#3b82f6'],
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
                    },
                    tooltip: {
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
                            color: 'rgba(0,0,0,0.05)'
                        },
                        title: {
                            display: true,
                            text: 'Number of Appointments'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Status'
                        }
                    }
                }
            }
        });
    </script>
@endsection
