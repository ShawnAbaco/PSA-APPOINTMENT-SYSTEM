{{-- resources/views/operator/appointments/index.blade.php --}}
@extends('layouts.operator')

@section('content')
    <div class="appt-container">
        <!-- Header Section -->
        <div class="appt-welcome-section">
            <div>
                <h1 class="appt-title">Confirmed Appointments</h1>
                <p class="appt-subtitle">View and manage all confirmed client appointments</p>
            </div>
            <div class="appt-date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="appt-filters-bar">
            <div class="appt-filter-group">
                <i class="fas fa-search"></i>
                <input type="text" id="searchAppointment" placeholder="Search by number or client..."
                    class="appt-filter-input">
            </div>
            <div class="appt-filter-group">
                <i class="fas fa-calendar"></i>
                <input type="date" id="dateFilter" class="appt-filter-input">
            </div>
            <div class="appt-filter-group">
                <i class="fas fa-calendar-week"></i>
                <select id="weekFilter" class="appt-filter-select">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="tomorrow">Tomorrow</option>
                    <option value="this_week">This Week</option>
                    <option value="next_week">Next Week</option>
                    <option value="this_month">This Month</option>
                </select>
            </div>
            <button class="appt-btn-reset" id="resetFilters">
                <i class="fas fa-undo-alt"></i>
            </button>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="appt-bulk-actions-bar" id="bulkActionsBar" style="display: none;">
            <div class="appt-bulk-actions-content">
                <div class="appt-bulk-buttons">
                    <button class="appt-btn-bulk" id="bulkCompleteBtn">
                        <i class="fas fa-check-double"></i>Mark Complete
                    </button>
                    <button class="appt-btn-bulk" id="bulkCancelBtn">
                        <i class="fas fa-times-circle"></i>Cancel
                    </button>
                </div>
                <span class="appt-bulk-count" id="bulkCount">0 items selected</span>
                <button class="appt-btn-bulk" id="clearSelectionBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="appt-card">
            <div class="appt-card-header">
                <div class="appt-table-title-section">
                    <div class="appt-select-all-wrapper">
                        <input type="checkbox" id="selectAllCheckbox" class="appt-select-all-checkbox">
                        <label for="selectAllCheckbox" class="appt-select-all-label">Select All</label>
                    </div>
                    <h5 class="appt-card-title"><i class="fas fa-check-circle"></i> Confirmed Appointments</h5>
                    <span class="appt-record-count" id="recordCount">{{ $appointments->total() }} records</span>
                </div>
                <div class="appt-table-actions">
                    <button class="appt-btn-icon" id="exportBtn" title="Export to CSV">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="appt-btn-icon" id="printBtn" title="Print">
                        <i class="fas fa-print"></i>
                    </button>
                    <button class="appt-btn-icon" id="refreshBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <div class="appt-card-body">
                <div class="appt-table-responsive">
                    <table class="appt-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAllCheckboxHeader" class="appt-select-all-checkbox">
                                </th>
                                <th>Appointment #</th>
                                <th>Date & Time</th>
                                <th>Contact Person</th>
                                <th>Clients</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="appointmentsTableBody">
                            @forelse($appointments as $appointment)
                                <tr class="appt-table-row" data-status="{{ $appointment->status }}"
                                    data-date="{{ $appointment->appointment_date }}" data-id="{{ $appointment->id }}">
                                    <td>
                                        <input type="checkbox" class="appt-checkbox" value="{{ $appointment->id }}">
                                    </td>
                                    <td class="appt-appointment-number">
                                        <span class="appt-number-badge">{{ $appointment->appointment_number }}</span>
                                    </td>
                                    <td>
                                        <div class="appt-date-time">
                                            <span
                                                class="appt-date">{{ date('M d, Y', strtotime($appointment->appointment_date)) }}</span>
                                            <span
                                                class="appt-time">{{ $appointment->timeSlot->label ?? date('h:i A', strtotime($appointment->appointment_time ?? '09:00')) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="appt-contact-info">
                                            <div class="appt-contact-name">{{ $appointment->contact_name }}</div>
                                            <div class="appt-contact-phone">{{ $appointment->contact_mobile ?? '—' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="appt-client-count">
                                            <i class="fas fa-user-friends"></i>
                                            <span>{{ $appointment->clients->count() }} person(s)</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="appt-action-buttons">
                                            <button class="appt-btn-action appt-btn-view"
                                                onclick="openViewModal({{ $appointment->id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="appt-btn-action appt-btn-complete"
                                                onclick="openCompleteModal({{ $appointment->id }})" title="Mark Complete">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                            <button class="appt-btn-action appt-btn-cancel"
                                                onclick="openCancelModal({{ $appointment->id }})" title="Cancel">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="appt-empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h4>No confirmed appointments found</h4>
                                        <p>No confirmed appointments match your current filters</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="appt-pagination-wrapper">
                    <div class="appt-pagination-info">
                        Showing {{ $appointments->firstItem() ?? 0 }} to {{ $appointments->lastItem() ?? 0 }} of
                        {{ $appointments->total() }} confirmed appointments
                    </div>
                    <div class="appt-simple-pagination">
                        @if ($appointments->onFirstPage())
                            <button class="appt-pagination-btn appt-pagination-disabled" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        @else
                            <a href="{{ $appointments->previousPageUrl() }}" class="appt-pagination-btn">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        @endif

                        <span class="appt-pagination-current">
                            Page {{ $appointments->currentPage() }} of {{ $appointments->lastPage() }}
                        </span>

                        @if ($appointments->hasMorePages())
                            <a href="{{ $appointments->nextPageUrl() }}" class="appt-pagination-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="appt-pagination-btn appt-pagination-disabled" disabled>
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="appt-modal">
        <div class="appt-modal-overlay" onclick="closeModal('viewModal')"></div>
        <div class="appt-modal-container appt-modal-lg">
            <div class="appt-modal-header">
                <h3>Appointment Details</h3>
                <button class="appt-modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="appt-modal-body" id="viewModalBody">
                <div class="appt-loading-spinner">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="appt-modal">
        <div class="appt-modal-overlay" onclick="closeModal('editModal')"></div>
        <div class="appt-modal-container appt-modal-md">
            <div class="appt-modal-header">
                <h3>Edit Appointment</h3>
                <button class="appt-modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="appt-modal-body" id="editModalBody">
                <div class="appt-loading-spinner">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Complete Modal -->
    <div id="completeModal" class="appt-modal">
        <div class="appt-modal-overlay" onclick="closeModal('completeModal')"></div>
        <div class="appt-modal-container appt-modal-sm">
            <div class="appt-modal-header appt-modal-header-success">
                <h3>Complete Appointment</h3>
                <button class="appt-modal-close" onclick="closeModal('completeModal')">&times;</button>
            </div>
            <div class="appt-modal-body">
                <p>Mark this appointment as completed?</p>
                <p class="appt-text-muted">This will change the appointment status to "Completed".</p>
                <input type="hidden" id="completeAppointmentId">
                <div class="appt-modal-actions">
                    <button class="appt-btn-secondary" onclick="closeModal('completeModal')">Cancel</button>
                    <button class="appt-btn-success" onclick="confirmComplete()">Yes, Mark Complete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="appt-modal">
        <div class="appt-modal-overlay" onclick="closeModal('cancelModal')"></div>
        <div class="appt-modal-container appt-modal-sm">
            <div class="appt-modal-header appt-modal-header-warning">
                <h3>Cancel Appointment</h3>
                <button class="appt-modal-close" onclick="closeModal('cancelModal')">&times;</button>
            </div>
            <div class="appt-modal-body">
                <p>Are you sure you want to cancel this appointment?</p>
                <p class="appt-text-muted">This action cannot be undone.</p>
                <div class="appt-form-group">
                    <label>Cancellation Reason (Optional)</label>
                    <textarea id="cancellationReason" class="appt-form-control" rows="3" placeholder="Enter reason for cancellation..."></textarea>
                </div>
                <input type="hidden" id="cancelAppointmentId">
                <div class="appt-modal-actions">
                    <button class="appt-btn-secondary" onclick="closeModal('cancelModal')">No, Go Back</button>
                    <button class="appt-btn-warning" onclick="confirmCancel()">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notificationToast" class="appt-notification-toast" style="display: none;">
        <div class="appt-toast-content">
            <i class="fas fa-check-circle"></i>
            <span id="toastMessage">Action completed successfully!</span>
        </div>
    </div>

    <script>
        // CSRF Token setup
        const csrfToken = '{{ csrf_token() }}';
        let selectedAppointments = new Set();

        // Filter functionality
        let filterTimeout;

        function filterTable() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                const searchTerm = document.getElementById('searchAppointment')?.value.toLowerCase() || '';
                const dateFilter = document.getElementById('dateFilter')?.value || '';
                const weekFilter = document.getElementById('weekFilter')?.value || '';

                const rows = document.querySelectorAll('.appt-table-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const appointmentNumber = row.querySelector('.appt-number-badge')?.textContent
                        .toLowerCase() || '';
                    const contactName = row.querySelector('.appt-contact-name')?.textContent
                    .toLowerCase() || '';
                    const rowDate = row.getAttribute('data-date') || '';

                    let matchesWeek = true;
                    if (weekFilter) {
                        const date = new Date(rowDate);
                        const today = new Date();
                        const weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - today.getDay());
                        const weekEnd = new Date(weekStart);
                        weekEnd.setDate(weekStart.getDate() + 6);

                        switch (weekFilter) {
                            case 'today':
                                matchesWeek = date.toDateString() === today.toDateString();
                                break;
                            case 'tomorrow':
                                const tomorrow = new Date(today);
                                tomorrow.setDate(today.getDate() + 1);
                                matchesWeek = date.toDateString() === tomorrow.toDateString();
                                break;
                            case 'this_week':
                                matchesWeek = date >= weekStart && date <= weekEnd;
                                break;
                            case 'next_week':
                                const nextWeekStart = new Date(weekEnd);
                                nextWeekStart.setDate(weekEnd.getDate() + 1);
                                const nextWeekEnd = new Date(nextWeekStart);
                                nextWeekEnd.setDate(nextWeekStart.getDate() + 6);
                                matchesWeek = date >= nextWeekStart && date <= nextWeekEnd;
                                break;
                            case 'this_month':
                                matchesWeek = date.getMonth() === today.getMonth() && date.getFullYear() ===
                                    today.getFullYear();
                                break;
                        }
                    }

                    const matchesSearch = !searchTerm || appointmentNumber.includes(searchTerm) ||
                        contactName.includes(searchTerm);
                    const matchesDate = !dateFilter || rowDate === dateFilter;

                    const shouldShow = matchesSearch && matchesDate && matchesWeek;
                    row.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                });

                const recordCountSpan = document.getElementById('recordCount');
                if (recordCountSpan) {
                    recordCountSpan.textContent = visibleCount + ' record' + (visibleCount !== 1 ? 's' : '');
                }

                showNotification(`Showing ${visibleCount} confirmed appointments`);
            }, 300);
        }

        // Bulk selection handlers
        function updateBulkActionsBar() {
            const bulkBar = document.getElementById('bulkActionsBar');
            const bulkCount = document.getElementById('bulkCount');

            if (selectedAppointments.size > 0) {
                bulkBar.style.display = 'block';
                bulkCount.textContent =
                    `${selectedAppointments.size} item${selectedAppointments.size !== 1 ? 's' : ''} selected`;
            } else {
                bulkBar.style.display = 'none';
            }

            const checkboxes = document.querySelectorAll('.appt-checkbox');
            const visibleCheckboxes = Array.from(checkboxes).filter(cb => cb.closest('.appt-table-row').style.display !==
                'none');
            const selectAllCheckboxes = document.querySelectorAll('#selectAllCheckbox, #selectAllCheckboxHeader');

            selectAllCheckboxes.forEach(selectAll => {
                if (visibleCheckboxes.length > 0 && selectedAppointments.size === visibleCheckboxes.length) {
                    selectAll.checked = true;
                    selectAll.indeterminate = false;
                } else if (selectedAppointments.size > 0) {
                    selectAll.checked = false;
                    selectAll.indeterminate = true;
                } else {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                }
            });
        }

        function toggleAppointmentSelection(checkbox, id) {
            if (checkbox.checked) {
                selectedAppointments.add(id.toString());
            } else {
                selectedAppointments.delete(id.toString());
            }
            updateBulkActionsBar();
        }

        function selectAllAppointments() {
            const checkboxes = document.querySelectorAll('.appt-checkbox');
            const visibleCheckboxes = Array.from(checkboxes).filter(cb => cb.closest('.appt-table-row').style.display !==
                'none');
            const allSelected = visibleCheckboxes.length === selectedAppointments.size;

            visibleCheckboxes.forEach(checkbox => {
                if (!allSelected && !checkbox.checked) {
                    checkbox.checked = true;
                    selectedAppointments.add(checkbox.value);
                } else if (allSelected && checkbox.checked) {
                    checkbox.checked = false;
                    selectedAppointments.delete(checkbox.value);
                }
            });

            updateBulkActionsBar();
        }

        // Bulk actions
        async function bulkComplete() {
            if (selectedAppointments.size === 0) return;
            if (confirm(`Mark ${selectedAppointments.size} appointment(s) as completed?`)) {
                showNotification(`Processing ${selectedAppointments.size} appointment(s)...`);
                const promises = Array.from(selectedAppointments).map(id =>
                    fetch(`/operator/appointments/${id}/complete`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({})
                    })
                );
                await Promise.all(promises);
                showNotification(`${selectedAppointments.size} appointment(s) marked as completed!`);
                setTimeout(() => location.reload(), 1500);
            }
        }

        async function bulkCancel() {
            if (selectedAppointments.size === 0) return;
            if (confirm(`Cancel ${selectedAppointments.size} appointment(s)?`)) {
                showNotification(`Processing ${selectedAppointments.size} appointment(s)...`);
                const promises = Array.from(selectedAppointments).map(id =>
                    fetch(`/operator/appointments/${id}/cancel`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({})
                    })
                );
                await Promise.all(promises);
                showNotification(`${selectedAppointments.size} appointment(s) cancelled!`);
                setTimeout(() => location.reload(), 1500);
            }
        }

        // Export to CSV
        function exportToCSV() {
            const rows = document.querySelectorAll('.appt-table-row');
            const csvData = [
                ['Appointment #', 'Date', 'Time', 'Contact Name', 'Contact Phone', 'Clients']
            ];

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const appointmentNumber = row.querySelector('.appt-number-badge')?.textContent || '';
                    const date = row.querySelector('.appt-date')?.textContent || '';
                    const time = row.querySelector('.appt-time')?.textContent || '';
                    const contactName = row.querySelector('.appt-contact-name')?.textContent || '';
                    const contactPhone = row.querySelector('.appt-contact-phone')?.textContent || '';
                    const clients = row.querySelector('.appt-client-count span')?.textContent || '';

                    csvData.push([appointmentNumber, date, time, contactName, contactPhone, clients]);
                }
            });

            const csvContent = csvData.map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], {
                type: 'text/csv'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `confirmed_appointments_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            URL.revokeObjectURL(url);
            showNotification('Exported to CSV successfully!');
        }

        // Print functionality
        function printTable() {
            const printWindow = window.open('', '_blank');
            const tableContent = document.querySelector('.appt-table-responsive').cloneNode(true);
            printWindow.document.write(`
            <html>
            <head><title>Confirmed Appointments Report</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background: #f5f5f5; }
            </style>
            </head>
            <body>
                <h2>Confirmed Appointments Report</h2>
                <p>Generated: ${new Date().toLocaleString()}</p>
                ${tableContent.outerHTML}
            </body>
            </html>
        `);
            printWindow.document.close();
            printWindow.print();
            showNotification('Print preview opened!');
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            const icon = toast.querySelector('i');

            toastMessage.textContent = message;
            icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            toast.style.display = 'block';

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.style.display = 'none';
                    toast.style.opacity = '1';
                }, 300);
            }, 3000);
        }

        // Event listeners
        document.getElementById('searchAppointment')?.addEventListener('keyup', () => filterTable());
        document.getElementById('dateFilter')?.addEventListener('change', () => filterTable());
        document.getElementById('weekFilter')?.addEventListener('change', () => filterTable());

        document.getElementById('resetFilters')?.addEventListener('click', () => {
            document.getElementById('searchAppointment').value = '';
            document.getElementById('dateFilter').value = '';
            document.getElementById('weekFilter').value = '';
            filterTable();
        });

        document.getElementById('refreshBtn')?.addEventListener('click', () => location.reload());
        document.getElementById('exportBtn')?.addEventListener('click', () => exportToCSV());
        document.getElementById('printBtn')?.addEventListener('click', () => printTable());

        document.getElementById('bulkCompleteBtn')?.addEventListener('click', () => bulkComplete());
        document.getElementById('bulkCancelBtn')?.addEventListener('click', () => bulkCancel());
        document.getElementById('clearSelectionBtn')?.addEventListener('click', () => {
            selectedAppointments.clear();
            document.querySelectorAll('.appt-checkbox').forEach(cb => cb.checked = false);
            updateBulkActionsBar();
        });

        document.querySelectorAll('#selectAllCheckbox, #selectAllCheckboxHeader').forEach(checkbox => {
            checkbox.addEventListener('change', () => selectAllAppointments());
        });

        // Initialize checkboxes
        document.querySelectorAll('.appt-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleAppointmentSelection(this, this.value);
            });
        });

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }

        // View Modal
        async function openViewModal(id) {
            openModal('viewModal');
            const modalBody = document.getElementById('viewModalBody');
            modalBody.innerHTML = '<div class="appt-loading-spinner">Loading appointment details...</div>';

            try {
                const response = await fetch(`/operator/appointments/${id}`);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const content = doc.querySelector('.container-fluid') || doc.body;
                modalBody.innerHTML = `<div class="appt-appointment-details-view">${content.innerHTML}</div>`;
            } catch (error) {
                modalBody.innerHTML =
                    '<div class="appt-error-message">Failed to load appointment details. Please try again.</div>';
            }
        }

        // Complete Modal
        function openCompleteModal(id) {
            document.getElementById('completeAppointmentId').value = id;
            openModal('completeModal');
        }

        async function confirmComplete() {
            const id = document.getElementById('completeAppointmentId').value;

            try {
                const response = await fetch(`/operator/appointments/${id}/complete`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
                });

                if (response.ok) {
                    closeModal('completeModal');
                    showNotification('Appointment marked as completed successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Failed to complete appointment', 'error');
                }
            } catch (error) {
                showNotification('An error occurred', 'error');
            }
        }

        // Cancel Modal
        function openCancelModal(id) {
            document.getElementById('cancelAppointmentId').value = id;
            document.getElementById('cancellationReason').value = '';
            openModal('cancelModal');
        }

        async function confirmCancel() {
            const id = document.getElementById('cancelAppointmentId').value;
            const reason = document.getElementById('cancellationReason').value;

            try {
                const response = await fetch(`/operator/appointments/${id}/cancel`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ reason: reason || 'Cancelled by operator' })
                });

                if (response.ok) {
                    closeModal('cancelModal');
                    showNotification('Appointment cancelled successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Failed to cancel appointment', 'error');
                }
            } catch (error) {
                showNotification('An error occurred', 'error');
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.appt-modal.active').forEach(modal => {
                    modal.classList.remove('active');
                });
                document.body.style.overflow = '';
            }
        });

        function updateStats() {}
    </script>
@endsection