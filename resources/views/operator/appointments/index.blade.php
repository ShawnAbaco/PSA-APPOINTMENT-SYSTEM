{{-- resources/views/operator/appointments/index.blade.php --}}
@extends('layouts.operator')

@section('content')
    <div class="appt-container">
        <!-- Header Section -->
        <div class="appt-welcome-section">
            <div>
                <h1 class="appt-title">Appointments</h1>
                <p class="appt-subtitle">Manage and monitor all client appointments</p>
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
                <i class="fas fa-filter"></i>
                <select id="statusFilter" class="appt-filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
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
                    <button class="appt-btn-bulk" id="bulkConfirmBtn">
                        <i class="fas fa-check-circle"></i>Confirm
                    </button>
                    <button class="appt-btn-bulk" id="bulkCancelBtn">
                        <i class="fas fa-times-circle"></i>Cancel
                    </button>
                    <button class="appt-btn-bulk appt-btn-bulk-danger" id="bulkDeleteBtn">
                        <i class="fas fa-trash-alt"></i>Delete
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
                    <h5 class="appt-card-title"><i class="fas fa-calendar-alt"></i> All Appointments</h5>
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
                                <th>Status</th>
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
                                                class="appt-time">{{ date('h:i A', strtotime($appointment->appointment_time ?? '09:00')) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="appt-contact-info">
                                            <div class="appt-contact-name">{{ $appointment->contact_name }}</div>
                                            <div class="appt-contact-phone">{{ $appointment->contact_phone ?? '—' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="appt-client-count">
                                            <i class="fas fa-user-friends"></i>
                                            <span>{{ $appointment->clients->count() }} person(s)</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="appt-status {{ $appointment->status }}">
                                            <i
                                                class="fas {{ $appointment->status == 'confirmed' ? 'fa-check-circle' : ($appointment->status == 'pending' ? 'fa-clock' : ($appointment->status == 'completed' ? 'fa-check-double' : 'fa-times-circle')) }}"></i>
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="appt-action-buttons">
                                            <button class="appt-btn-action appt-btn-view"
                                                onclick="openViewModal({{ $appointment->id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <div class="appt-dropdown">
                                                <button class="appt-btn-action appt-dropdown-toggle"
                                                    onclick="toggleDropdown(this)">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="appt-dropdown-menu">
                                                    <button class="appt-dropdown-item appt-dropdown-edit"
                                                        onclick="openEditModal({{ $appointment->id }})">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    @if (in_array($appointment->status, ['pending', 'confirmed']))
                                                        <button class="appt-dropdown-item appt-dropdown-cancel"
                                                            onclick="openCancelModal({{ $appointment->id }})">
                                                            <i class="fas fa-times-circle"></i> Cancel
                                                        </button>
                                                    @endif
                                                    <button class="appt-dropdown-item appt-dropdown-delete"
                                                        onclick="openDeleteModal({{ $appointment->id }})">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="appt-empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h4>No appointments found</h4>
                                        <p>No appointments match your current filters</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="appt-pagination-wrapper">
                    <div class="appt-pagination-info">
                        Showing {{ $appointments->firstItem() ?? 0 }} to {{ $appointments->lastItem() ?? 0 }} of
                        {{ $appointments->total() }} appointments
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

    <!-- Quick Add Modal -->
    <div id="quickAddModal" class="appt-modal">
        <div class="appt-modal-overlay" onclick="closeModal('quickAddModal')"></div>
        <div class="appt-modal-container appt-modal-md">
            <div class="appt-modal-header">
                <h3>Quick Add Appointment</h3>
                <button class="appt-modal-close" onclick="closeModal('quickAddModal')">&times;</button>
            </div>
            <div class="appt-modal-body">
                <form id="quickAddForm">
                    @csrf
                    <div class="appt-form-group">
                        <label>Contact Name *</label>
                        <input type="text" name="contact_name" class="appt-form-control" required>
                    </div>
                    <div class="appt-form-group">
                        <label>Contact Mobile *</label>
                        <input type="text" name="contact_mobile" class="appt-form-control" required>
                    </div>
                    <div class="appt-form-group">
                        <label>Appointment Date *</label>
                        <input type="date" name="appointment_date" class="appt-form-control" required>
                    </div>
                    <div class="appt-form-group">
                        <label>Appointment Time</label>
                        <input type="time" name="appointment_time" class="appt-form-control">
                    </div>
                    <div class="appt-modal-actions">
                        <button type="button" class="appt-btn-secondary"
                            onclick="closeModal('quickAddModal')">Cancel</button>
                        <button type="submit" class="appt-btn-primary">Create Appointment</button>
                    </div>
                </form>
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
                <input type="hidden" id="cancelAppointmentId">
                <div class="appt-modal-actions">
                    <button class="appt-btn-secondary" onclick="closeModal('cancelModal')">No, Go Back</button>
                    <button class="appt-btn-danger" onclick="confirmCancel()">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="appt-modal">
        <div class="appt-modal-overlay" onclick="closeModal('deleteModal')"></div>
        <div class="appt-modal-container appt-modal-sm">
            <div class="appt-modal-header appt-modal-header-danger">
                <h3>Delete Appointment</h3>
                <button class="appt-modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="appt-modal-body">
                <p>Are you sure you want to delete this appointment?</p>
                <p class="appt-text-muted">This action is permanent and cannot be undone.</p>
                <input type="hidden" id="deleteAppointmentId">
                <div class="appt-modal-actions">
                    <button class="appt-btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                    <button class="appt-btn-danger" onclick="confirmDelete()">Delete Permanently</button>
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
                const statusFilter = document.getElementById('statusFilter')?.value || '';
                const dateFilter = document.getElementById('dateFilter')?.value || '';
                const weekFilter = document.getElementById('weekFilter')?.value || '';

                const rows = document.querySelectorAll('.appt-table-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const appointmentNumber = row.querySelector('.appt-number-badge')?.textContent
                        .toLowerCase() || '';
                    const contactName = row.querySelector('.appt-contact-name')?.textContent
                    .toLowerCase() || '';
                    const rowStatus = row.getAttribute('data-status') || '';
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
                    const matchesStatus = !statusFilter || rowStatus === statusFilter;
                    const matchesDate = !dateFilter || rowDate === dateFilter;

                    const shouldShow = matchesSearch && matchesStatus && matchesDate && matchesWeek;
                    row.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                });

                const recordCountSpan = document.getElementById('recordCount');
                if (recordCountSpan) {
                    recordCountSpan.textContent = visibleCount + ' record' + (visibleCount !== 1 ? 's' : '');
                }

                updateStats();
                showNotification(`Showing ${visibleCount} appointments`);
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
        async function bulkConfirm() {
            if (selectedAppointments.size === 0) return;
            if (confirm(`Confirm ${selectedAppointments.size} appointment(s)?`)) {
                showNotification(`Processing ${selectedAppointments.size} appointment(s)...`);
                const promises = Array.from(selectedAppointments).map(id =>
                    fetch(`/operator/appointments/${id}/confirm`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({})
                    })
                );
                await Promise.all(promises);
                showNotification(`${selectedAppointments.size} appointment(s) confirmed!`);
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

        async function bulkDelete() {
            if (selectedAppointments.size === 0) return;
            if (confirm(
                `⚠️ WARNING: Delete ${selectedAppointments.size} appointment(s)? This action cannot be undone!`)) {
                showNotification(`Deleting ${selectedAppointments.size} appointment(s)...`);
                const promises = Array.from(selectedAppointments).map(id =>
                    fetch(`/operator/appointments/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                );
                await Promise.all(promises);
                showNotification(`${selectedAppointments.size} appointment(s) deleted!`);
                setTimeout(() => location.reload(), 1500);
            }
        }

        // Export to CSV
        function exportToCSV() {
            const rows = document.querySelectorAll('.appt-table-row');
            const csvData = [
                ['Appointment #', 'Date', 'Time', 'Contact Name', 'Contact Phone', 'Status', 'Clients']
            ];

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const appointmentNumber = row.querySelector('.appt-number-badge')?.textContent || '';
                    const date = row.querySelector('.appt-date')?.textContent || '';
                    const time = row.querySelector('.appt-time')?.textContent || '';
                    const contactName = row.querySelector('.appt-contact-name')?.textContent || '';
                    const contactPhone = row.querySelector('.appt-contact-phone')?.textContent || '';
                    const status = row.querySelector('.appt-status')?.textContent.trim() || '';
                    const clients = row.querySelector('.appt-client-count span')?.textContent || '';

                    csvData.push([appointmentNumber, date, time, contactName, contactPhone, status, clients]);
                }
            });

            const csvContent = csvData.map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], {
                type: 'text/csv'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `appointments_${new Date().toISOString().split('T')[0]}.csv`;
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
            <head><title>Appointments Report</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background: #f5f5f5; }
            </style>
            </head>
            <body>
                <h2>Appointments Report</h2>
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

        // Quick add form
        document.getElementById('quickAddForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', csrfToken);

            try {
                const response = await fetch('/operator/appointments', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    closeModal('quickAddModal');
                    showNotification('Appointment created successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('Failed to create appointment', 'error');
                }
            } catch (error) {
                showNotification('An error occurred', 'error');
            }
        });

        // Event listeners
        document.getElementById('searchAppointment')?.addEventListener('keyup', () => filterTable());
        document.getElementById('statusFilter')?.addEventListener('change', () => filterTable());
        document.getElementById('dateFilter')?.addEventListener('change', () => filterTable());
        document.getElementById('weekFilter')?.addEventListener('change', () => filterTable());

        document.getElementById('resetFilters')?.addEventListener('click', () => {
            document.getElementById('searchAppointment').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFilter').value = '';
            document.getElementById('weekFilter').value = '';
            filterTable();
        });

        document.getElementById('refreshBtn')?.addEventListener('click', () => location.reload());
        document.getElementById('exportBtn')?.addEventListener('click', () => exportToCSV());
        document.getElementById('printBtn')?.addEventListener('click', () => printTable());

        document.getElementById('bulkConfirmBtn')?.addEventListener('click', () => bulkConfirm());
        document.getElementById('bulkCancelBtn')?.addEventListener('click', () => bulkCancel());
        document.getElementById('bulkDeleteBtn')?.addEventListener('click', () => bulkDelete());
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

        // Dropdown functionality
        function toggleDropdown(btn) {
            event.stopPropagation();
            const dropdown = btn.closest('.appt-dropdown');
            const menu = dropdown.querySelector('.appt-dropdown-menu');

            document.querySelectorAll('.appt-dropdown-menu.show').forEach(menu => {
                if (menu !== dropdown.querySelector('.appt-dropdown-menu')) {
                    menu.classList.remove('show');
                }
            });

            menu.classList.toggle('show');
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.appt-dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
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

        // Edit Modal
        async function openEditModal(id) {
            openModal('editModal');
            const modalBody = document.getElementById('editModalBody');
            modalBody.innerHTML = '<div class="appt-loading-spinner">Loading edit form...</div>';

            try {
                const response = await fetch(`/operator/appointments/${id}/edit`);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const formContent = doc.querySelector('.container-fluid') || doc.body;
                modalBody.innerHTML = `
                <div class="appt-appointment-edit-form">
                    ${formContent.innerHTML}
                    <div class="appt-modal-actions">
                        <button class="appt-btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                        <button class="appt-btn-primary" onclick="saveEdit(${id})">Save Changes</button>
                    </div>
                </div>
            `;
            } catch (error) {
                modalBody.innerHTML =
                    '<div class="appt-error-message">Failed to load edit form. Please try again.</div>';
            }
        }

        async function saveEdit(id) {
            const form = document.querySelector('#editModalBody form');
            if (!form) return;

            const formData = new FormData(form);
            formData.append('_token', csrfToken);
            formData.append('_method', 'PUT');

            try {
                const response = await fetch(`/operator/appointments/${id}`, {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    closeModal('editModal');
                    showNotification('Changes saved successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Failed to save changes', 'error');
                }
            } catch (error) {
                showNotification('An error occurred', 'error');
            }
        }

        // Cancel Modal
        function openCancelModal(id) {
            document.getElementById('cancelAppointmentId').value = id;
            openModal('cancelModal');
        }

        async function confirmCancel() {
            const id = document.getElementById('cancelAppointmentId').value;

            try {
                const response = await fetch(`/operator/appointments/${id}/cancel`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
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

        // Delete Modal
        function openDeleteModal(id) {
            document.getElementById('deleteAppointmentId').value = id;
            openModal('deleteModal');
        }

        async function confirmDelete() {
            const id = document.getElementById('deleteAppointmentId').value;

            if (confirm('Are you absolutely sure? This action cannot be undone.')) {
                try {
                    const response = await fetch(`/operator/appointments/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (response.ok) {
                        closeModal('deleteModal');
                        showNotification('Appointment deleted successfully!');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification('Failed to delete appointment', 'error');
                    }
                } catch (error) {
                    showNotification('An error occurred', 'error');
                }
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
