{{-- resources/views/operator/reports/index.blade.php --}}
@extends('layouts.operator')

@section('content')
    <div class="reports-container">
        <!-- Header Section -->
        <div class="reports-welcome-section">
            <div>
                <h1 class="reports-title">Reports & Analytics</h1>
                <p class="reports-subtitle">Pending and Completed Appointments Report</p>
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
                    <a href="{{ route('operator.reports.export.pdf', request()->query()) }}"
                        class="reports-btn reports-btn-pdf">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    <a href="{{ route('operator.reports.export.excel', request()->query()) }}"
                        class="reports-btn reports-btn-excel">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </a>
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
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
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
                        <h6 class="reports-stat-label">Pending Appointments</h6>
                        <h2 class="stat-value text-warning">{{ $pendingAppointments ?? 0 }}</h2>
                        <p class="reports-stat-trend"><i class="fas fa-clock"></i> Awaiting pending</p>
                    </div>
                    <div class="stat-icon-circle warning-bg">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="reports-stat-card completed-card">
                <div class="reports-stat-card-content">
                    <div class="reports-stat-info">
                        <h6 class="reports-stat-label">Completed Appointments</h6>
                        <h2 class="stat-value text-info">{{ $completedAppointments ?? 0 }}</h2>
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
                            {{ ($pendingAppointments ?? 0) + ($completedAppointments ?? 0) }}
                        </h2>
                        <p class="reports-stat-trend"><i class="fas fa-calendar-alt"></i> Processed appointments</p>
                    </div>
                    <div class="reports-stat-icon-circle primary-bg"><i class="fas fa-calendar-alt"></i></div>
                </div>
            </div>
        </div>

        <!-- Status Chart - Pending and Completed -->
        <div class="reports-chart-card reports-mb-4">
            <div class="reports-chart-header">
                <h5 class="reports-chart-title"><i class="fas fa-chart-bar"></i> Pending & Completed Appointments</h5>
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
                                <th># of Applicants</th>
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
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Appointment Status Chart - Pending and Completed
        const ctx1 = document.getElementById('appointmentChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Completed'],
                datasets: [{
                    label: 'Number of Appointments',
                    data: [{{ $pendingAppointments ?? 0 }}, {{ $completedAppointments ?? 0 }}],
                    backgroundColor: ['#F59E0B', '#3b82f6'],
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

        // Export to CSV
        function exportToCSV() {
            const table = document.getElementById('appointmentsTable');
            if (!table) {
                alert('No data to export');
                return;
            }

            let csv = [];
            // Get headers
            const headers = [];
            table.querySelectorAll('thead th').forEach(th => {
                headers.push(`"${th.innerText.trim()}"`);
            });
            csv.push(headers.join(','));

            // Get rows
            table.querySelectorAll('tbody tr').forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowData = Array.from(cells).map(cell => {
                    const badge = cell.querySelector('.status-badge');
                    let text = badge ? badge.innerText.trim() : cell.innerText.trim();
                    return `"${text.replace(/[^\w\s,.-]/g, '')}"`;
                });
                csv.push(rowData.join(','));
            });

            const blob = new Blob([csv.join('\n')], {
                type: 'text/csv'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `pending_completed_report_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            URL.revokeObjectURL(url);
            alert('✅ Report exported to CSV successfully!');
        }

        // Copy Table Data
        function copyTableData() {
            const table = document.getElementById('appointmentsTable');
            if (!table) {
                alert('No table to copy');
                return;
            }

            const range = document.createRange();
            range.selectNode(table);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);

            try {
                document.execCommand('copy');
                alert('✅ Appointment table copied to clipboard!');
            } catch (err) {
                alert('Failed to copy table data');
            }

            window.getSelection().removeAllRanges();
        }
    </script>
@endsection
