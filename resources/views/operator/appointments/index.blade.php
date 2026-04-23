{{-- resources/views/operator/appointments/index.blade.php --}}
@extends('layouts.operator')

@section('content')
    <div class="appointments-container">
        <!-- Header Section with Soft Gradient -->
        <div class="appointments-header">
            <div>
                <h1 class="page-title">Appointments</h1>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="filters-bar">
            <div class="filter-group">
                <i class="fas fa-search"></i>
                <input type="text" id="searchAppointment" placeholder="Search by number or client..." class="filter-input">
            </div>
            <div class="filter-group">
                <i class="fas fa-filter"></i>
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="filter-group">
                <i class="fas fa-calendar"></i>
                <input type="date" id="dateFilter" class="filter-input">
            </div>
            <div class="filter-group">
                <i class="fas fa-calendar-week"></i>
                <select id="weekFilter" class="filter-select">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="tomorrow">Tomorrow</option>
                    <option value="this_week">This Week</option>
                    <option value="next_week">Next Week</option>
                    <option value="this_month">This Month</option>
                </select>
            </div>
            <button class="btn-reset" id="resetFilters">
                <i class="fas fa-undo-alt"></i>
            </button>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="bulk-actions-bar" id="bulkActionsBar" style="display: none;">
            <div class="bulk-actions-content">
                <div class="bulk-buttons">
                    <button class="btn-bulk" id="bulkConfirmBtn">
                        <i class="fas fa-check-circle"></i>Confirm
                    </button>
                    <button class="btn-bulk" id="bulkCancelBtn">
                        <i class="fas fa-times-circle"></i>Cancel
                    </button>
                    <button class="btn-bulk btn-bulk-danger" id="bulkDeleteBtn">
                        <i class="fas fa-trash-alt"></i>Delete
                    </button>
                </div>
                <span class="bulk-count" id="bulkCount">0 items selected</span>

                <span class="bulk-actions">
                    <button class="btn-bulk" id="clearSelectionBtn">
                        <i class="fas fa-times"></i>
                    </button>
                </span>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title-section">
                    <div class="select-all-wrapper">
                        <input type="checkbox" id="selectAllCheckbox" class="select-all-checkbox">
                        <label for="selectAllCheckbox" class="select-all-label">Select All</label>
                    </div>
                    <h3>All Appointments</h3>
                    <span class="record-count" id="recordCount">{{ $appointments->total() }} records</span>
                </div>
                <div class="table-actions">
                    <button class="btn-icon" id="exportBtn" title="Export to CSV">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn-icon" id="printBtn" title="Print">
                        <i class="fas fa-print"></i>
                    </button>
                    <button class="btn-icon" id="refreshBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAllCheckboxHeader" class="select-all-checkbox">
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
                            <tr class="table-row" data-status="{{ $appointment->status }}"
                                data-date="{{ $appointment->appointment_date }}" data-id="{{ $appointment->id }}">
                                <td>
                                    <input type="checkbox" class="appointment-checkbox" value="{{ $appointment->id }}">
                                </td>
                                <td class="appointment-number">
                                    <span class="number-badge">{{ $appointment->appointment_number }}</span>
                                </td>
                                <td>
                                    <div class="date-time">
                                        <span
                                            class="date">{{ date('M d, Y', strtotime($appointment->appointment_date)) }}</span>
                                        <span
                                            class="time">{{ date('h:i A', strtotime($appointment->appointment_time ?? '09:00')) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <div class="contact-name">{{ $appointment->contact_name }}</div>
                                        <div class="contact-phone">{{ $appointment->contact_phone ?? '—' }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="client-count">
                                        <i class="fas fa-user-friends"></i>
                                        <span>{{ $appointment->clients->count() }} person(s)</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status {{ $appointment->status }}">
                                        <i
                                            class="fas {{ $appointment->status == 'confirmed' ? 'fa-check-circle' : ($appointment->status == 'pending' ? 'fa-clock' : ($appointment->status == 'completed' ? 'fa-check-double' : 'fa-times-circle')) }}"></i>
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action view" onclick="openViewModal({{ $appointment->id }})"
                                            title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn-action dropdown-toggle" onclick="toggleDropdown(this)">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <button class="dropdown-item edit"
                                                    onclick="openEditModal({{ $appointment->id }})">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                @if (in_array($appointment->status, ['pending', 'confirmed']))
                                                    <button class="dropdown-item cancel"
                                                        onclick="openCancelModal({{ $appointment->id }})">
                                                        <i class="fas fa-times-circle"></i> Cancel
                                                    </button>
                                                @endif
                                                <button class="dropdown-item delete"
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
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <h4>No appointments found</h4>
                                    <p>No appointments match your current filters</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing {{ $appointments->firstItem() ?? 0 }} to {{ $appointments->lastItem() ?? 0 }} of
                    {{ $appointments->total() }} appointments
                </div>
                <div class="simple-pagination">
                    @if ($appointments->onFirstPage())
                        <button class="pagination-btn disabled" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                    @else
                        <a href="{{ $appointments->previousPageUrl() }}" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    @endif

                    <span class="pagination-current">
                        Page {{ $appointments->currentPage() }} of {{ $appointments->lastPage() }}
                    </span>

                    @if ($appointments->hasMorePages())
                        <a href="{{ $appointments->nextPageUrl() }}" class="pagination-btn">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="pagination-btn disabled" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Appointment Modal -->
    <div id="quickAddModal" class="modal">
        <div class="modal-overlay" onclick="closeModal('quickAddModal')"></div>
        <div class="modal-container modal-md">
            <div class="modal-header">
                <h3>Quick Add Appointment</h3>
                <button class="modal-close" onclick="closeModal('quickAddModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="quickAddForm">
                    @csrf
                    <div class="form-group">
                        <label>Contact Name *</label>
                        <input type="text" name="contact_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Mobile *</label>
                        <input type="text" name="contact_mobile" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Appointment Date *</label>
                        <input type="date" name="appointment_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Appointment Time</label>
                        <input type="time" name="appointment_time" class="form-control">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary"
                            onclick="closeModal('quickAddModal')">Cancel</button>
                        <button type="submit" class="btn-primary">Create Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-overlay" onclick="closeModal('viewModal')"></div>
        <div class="modal-container modal-lg">
            <div class="modal-header">
                <h3>Appointment Details</h3>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="loading-spinner">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-overlay" onclick="closeModal('editModal')"></div>
        <div class="modal-container modal-md">
            <div class="modal-header">
                <h3>Edit Appointment</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="modal-body" id="editModalBody">
                <div class="loading-spinner">Loading...</div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-overlay" onclick="closeModal('cancelModal')"></div>
        <div class="modal-container modal-sm">
            <div class="modal-header">
                <h3>Cancel Appointment</h3>
                <button class="modal-close" onclick="closeModal('cancelModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this appointment?</p>
                <p class="text-muted">This action cannot be undone.</p>
                <input type="hidden" id="cancelAppointmentId">
                <div class="modal-actions">
                    <button class="btn-danger" onclick="confirmCancel()">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-overlay" onclick="closeModal('deleteModal')"></div>
        <div class="modal-container modal-sm">
            <div class="modal-header">
                <h3>Delete Appointment</h3>
                <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this appointment?</p>
                <p class="text-muted">This action is permanent and cannot be undone.</p>
                <input type="hidden" id="deleteAppointmentId">
                <div class="modal-actions">
                    <button class="btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                    <button class="btn-danger" onclick="confirmDelete()">Delete Permanently</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notificationToast" class="notification-toast" style="display: none;">
        <div class="toast-content">
            <i class="fas fa-check-circle"></i>
            <span id="toastMessage">Action completed successfully!</span>
        </div>
    </div>

    <script>
        // CSRF Token setup
        const csrfToken = '{{ csrf_token() }}';
        let selectedAppointments = new Set();

        // Filter functionality with pagination support
        let filterTimeout;

        function filterTable() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                const searchTerm = document.getElementById('searchAppointment')?.value.toLowerCase() || '';
                const statusFilter = document.getElementById('statusFilter')?.value || '';
                const dateFilter = document.getElementById('dateFilter')?.value || '';
                const weekFilter = document.getElementById('weekFilter')?.value || '';

                const rows = document.querySelectorAll('.table-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const appointmentNumber = row.querySelector('.number-badge')?.textContent
                        .toLowerCase() || '';
                    const contactName = row.querySelector('.contact-name')?.textContent.toLowerCase() || '';
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

                // Update record count
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

            // Update select all checkbox
            const checkboxes = document.querySelectorAll('.appointment-checkbox');
            const visibleCheckboxes = Array.from(checkboxes).filter(cb => cb.closest('.table-row').style.display !==
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
            const checkboxes = document.querySelectorAll('.appointment-checkbox');
            const visibleCheckboxes = Array.from(checkboxes).filter(cb => cb.closest('.table-row').style.display !==
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
            const rows = document.querySelectorAll('.table-row');
            const csvData = [
                ['Appointment #', 'Date', 'Time', 'Contact Name', 'Contact Phone', 'Status', 'Clients']
            ];

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const appointmentNumber = row.querySelector('.number-badge')?.textContent || '';
                    const date = row.querySelector('.date')?.textContent || '';
                    const time = row.querySelector('.time')?.textContent || '';
                    const contactName = row.querySelector('.contact-name')?.textContent || '';
                    const contactPhone = row.querySelector('.contact-phone')?.textContent || '';
                    const status = row.querySelector('.status')?.textContent.trim() || '';
                    const clients = row.querySelector('.client-count span')?.textContent || '';

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
            const tableContent = document.querySelector('.table-responsive').cloneNode(true);
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

        // Per page selector
        document.getElementById('perPageSelector')?.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', this.value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });

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
            document.querySelectorAll('.appointment-checkbox').forEach(cb => cb.checked = false);
            updateBulkActionsBar();
        });

        document.querySelectorAll('#selectAllCheckbox, #selectAllCheckboxHeader').forEach(checkbox => {
            checkbox.addEventListener('change', () => selectAllAppointments());
        });

        // Initialize checkboxes
        document.querySelectorAll('.appointment-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleAppointmentSelection(this, this.value);
            });
        });

        // Dropdown functionality
        function toggleDropdown(btn) {
            event.stopPropagation();
            const dropdown = btn.closest('.dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');

            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== dropdown.querySelector('.dropdown-menu')) {
                    menu.classList.remove('show');
                }
            });

            menu.classList.toggle('show');
        }

        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
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
            modalBody.innerHTML = '<div class="loading-spinner">Loading appointment details...</div>';

            try {
                const response = await fetch(`/operator/appointments/${id}`);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const content = doc.querySelector('.container-fluid') || doc.body;
                modalBody.innerHTML = `<div class="appointment-details-view">${content.innerHTML}</div>`;
            } catch (error) {
                modalBody.innerHTML =
                    '<div class="error-message">Failed to load appointment details. Please try again.</div>';
            }
        }

        // Edit Modal
        async function openEditModal(id) {
            openModal('editModal');
            const modalBody = document.getElementById('editModalBody');
            modalBody.innerHTML = '<div class="loading-spinner">Loading edit form...</div>';

            try {
                const response = await fetch(`/operator/appointments/${id}/edit`);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const formContent = doc.querySelector('.container-fluid') || doc.body;
                modalBody.innerHTML = `
                    <div class="appointment-edit-form">
                        ${formContent.innerHTML}
                        <div class="modal-actions">
                            <button class="btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                            <button class="btn-primary" onclick="saveEdit(${id})">Save Changes</button>
                        </div>
                    </div>
                `;
            } catch (error) {
                modalBody.innerHTML = '<div class="error-message">Failed to load edit form. Please try again.</div>';
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
                document.querySelectorAll('.modal.active').forEach(modal => {
                    modal.classList.remove('active');
                });
                document.body.style.overflow = '';
            }
        });

        // Initialize stats on load
        updateStats();
    </script>
@endsection
