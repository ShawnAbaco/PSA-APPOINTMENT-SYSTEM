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
                    {{-- REFRESH BUTTON REMOVED --}}
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
                                @if (in_array($appointment->status, ['pending', 'confirmed', 'completed']))
                                    <tr class="appt-table-row" data-status="{{ $appointment->status }}"
                                        data-date="{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d') }}"
                                        data-id="{{ $appointment->id }}">
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
                                                <div class="appt-contact-phone">{{ $appointment->contact_mobile ?? '—' }}
                                                </div>
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
                                                    class="fas {{ $appointment->status == 'completed' ? 'fa-check-double' : ($appointment->status == 'confirmed' ? 'fa-check-circle' : 'fa-clock') }}"></i>
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="appt-action-buttons">
                                                <button class="appt-btn-action appt-btn-view"
                                                    onclick="openViewModal({{ $appointment->id }})" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @if ($appointment->status == 'pending')
                                                    <button class="appt-btn-action appt-btn-complete"
                                                        onclick="completeAppointment({{ $appointment->id }})"
                                                        title="Complete">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                @endif

                                                @if ($appointment->status == 'confirmed')
                                                    <button class="appt-btn-action appt-btn-cancel"
                                                        onclick="cancelAppointment({{ $appointment->id }})"
                                                        title="Cancel">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                @endif

                                                @if ($appointment->status == 'completed')
                                                    <span class="appt-status-badge completed">
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
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

    <!-- PSA Loading Modal -->
    <div class="psa-loader-modal" id="psaLoaderModal">
        <div class="psa-loader-container">
            <img src="{{ asset('images/psa.png') }}" alt="PSA Loading" class="psa-loader-logo">
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notificationToast" class="appt-notification-toast" style="display: none;">
        <div class="appt-toast-content">
            <i class="fas fa-check-circle"></i>
            <span id="toastMessage">Action completed successfully!</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // CSRF Token setup
        const csrfToken = '{{ csrf_token() }}';
        let selectedAppointments = new Set();

        // ============================================================
        //  PSA LOADER CONTROLS
        // ============================================================
        function showPSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.add('show');
        }

        function hidePSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.remove('show');
        }

        // ============================================================
        // FILTER FUNCTIONALITY WITH PAGE RESET
        // ============================================================

        // Function to apply filters and reset to page 1
        function applyFiltersAndReset() {
            // Get filter values
            const searchTerm = document.getElementById('searchAppointment')?.value || '';
            const statusFilter = document.getElementById('statusFilter')?.value || '';
            const dateFilter = document.getElementById('dateFilter')?.value || '';
            const weekFilter = document.getElementById('weekFilter')?.value || '';

            // Build URL with filters and page=1
            const params = new URLSearchParams();
            if (searchTerm) params.set('search', searchTerm);
            if (statusFilter) params.set('status', statusFilter);
            if (dateFilter) params.set('date', dateFilter);
            if (weekFilter) params.set('week_filter', weekFilter);
            params.set('page', '1'); // Reset to page 1

            // Reload page with filters
            window.location.href = window.location.pathname + '?' + params.toString();
        }

        // Load filters from URL on page load
        function loadFiltersFromURL() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('search')) document.getElementById('searchAppointment').value = params.get('search');
            if (params.get('status')) document.getElementById('statusFilter').value = params.get('status');
            if (params.get('date')) document.getElementById('dateFilter').value = params.get('date');
            if (params.get('week_filter')) document.getElementById('weekFilter').value = params.get('week_filter');
        }

        // Update event listeners to use the new function
        document.getElementById('searchAppointment')?.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                applyFiltersAndReset();
            }
        });

        document.getElementById('statusFilter')?.addEventListener('change', () => applyFiltersAndReset());
        document.getElementById('dateFilter')?.addEventListener('change', () => applyFiltersAndReset());
        document.getElementById('weekFilter')?.addEventListener('change', () => applyFiltersAndReset());

        document.getElementById('resetFilters')?.addEventListener('click', () => {
            window.location.href = window.location.pathname;
        });

        // Initialize filters from URL on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFiltersFromURL();
        });

        // Helper function to get current page
        function getCurrentPage() {
            const pageText = document.querySelector('.appt-pagination-current')?.textContent || '';
            const match = pageText.match(/Page (\d+) of/);
            return match ? parseInt(match[1]) : 1;
        }

        // Helper function to refresh table via AJAX (with PSA Loader)
        async function refreshTable() {
            showPSALoader();

            const currentPage = getCurrentPage();
            const perPage = 15;
            const searchTerm = document.getElementById('searchAppointment')?.value || '';
            const statusFilter = document.getElementById('statusFilter')?.value || '';
            const dateFilter = document.getElementById('dateFilter')?.value || '';
            const weekFilter = document.getElementById('weekFilter')?.value || '';

            const params = new URLSearchParams({
                page: currentPage,
                per_page: perPage,
                search: searchTerm,
                status: statusFilter,
                date: dateFilter,
                week_filter: weekFilter
            });

            try {
                const response = await fetch(`/operator/appointments?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.html) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;

                    const newTbody = tempDiv.querySelector('tbody');
                    if (newTbody) {
                        document.getElementById('appointmentsTableBody').innerHTML = newTbody.innerHTML;
                    }

                    const paginationInfo = document.querySelector('.appt-pagination-info');
                    if (paginationInfo) {
                        const newPaginationInfo = tempDiv.querySelector('.appt-pagination-info');
                        if (newPaginationInfo) {
                            paginationInfo.innerHTML = newPaginationInfo.innerHTML;
                        }
                    }

                    const pagination = document.querySelector('.appt-simple-pagination');
                    if (pagination) {
                        const newPagination = tempDiv.querySelector('.appt-simple-pagination');
                        if (newPagination) {
                            pagination.innerHTML = newPagination.innerHTML;
                        }
                    }

                    const recordCount = document.getElementById('recordCount');
                    if (recordCount && data.total) {
                        recordCount.textContent = data.total + ' records';
                    }

                    reinitializeComponents();
                }
            } catch (error) {
                console.error('Error refreshing table:', error);
            } finally {
                hidePSALoader();
            }
        }

        // Reinitialize all components after AJAX refresh
        function reinitializeComponents() {
            selectedAppointments.clear();

            document.querySelectorAll('.appt-checkbox').forEach(checkbox => {
                checkbox.removeEventListener('change', handleCheckboxChange);
                checkbox.addEventListener('change', handleCheckboxChange);
            });

            document.querySelectorAll('#selectAllCheckbox, #selectAllCheckboxHeader').forEach(checkbox => {
                checkbox.removeEventListener('change', selectAllAppointments);
                checkbox.addEventListener('change', () => selectAllAppointments());
            });

            updateBulkActionsBar();
            filterTable();
        }

        function handleCheckboxChange() {
            toggleAppointmentSelection(this, this.value);
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


        // Complete Appointment
        // Complete Appointment - Using PUT method (same as confirm)
        async function completeAppointment(id) {
            const result = await Swal.fire({
                title: 'Complete Appointment?',
                text: "This will change the status from Confirmed to Completed.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                showPSALoader();

                try {
                    const response = await fetch(`/operator/appointments/${id}/complete`, {
                        method: 'PUT', // Using PUT like your confirm function
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        hidePSALoader();

                        Swal.fire({
                            title: 'Completed!',
                            text: 'Appointment has been completed successfully.',
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        hidePSALoader();
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to complete appointment.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                } catch (error) {
                    console.error('Complete appointment error:', error);
                    hidePSALoader();
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while completing the appointment.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        }

        // Cancel Appointment
        async function cancelAppointment(id) {
            const result = await Swal.fire({
                title: 'Cancel Appointment?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            });

            if (result.isConfirmed) {
                showPSALoader();

                try {
                    const response = await fetch(`/operator/appointments/${id}/cancel`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });

                    // Parse the JSON response
                    const data = await response.json();

                    // Check if the request was successful AND the success flag is true
                    if (response.ok && data.success) {
                        hidePSALoader();

                        Swal.fire({
                            title: 'Cancelled!',
                            text: data.message || 'Appointment has been cancelled successfully.',
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        hidePSALoader();
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to cancel appointment.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                } catch (error) {
                    console.error('Cancel error:', error);
                    hidePSALoader();
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'An error occurred while cancelling the appointment.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        }

        // Delete Appointment
        async function deleteAppointment(id) {
            const result = await Swal.fire({
                title: 'Delete Appointment?',
                text: "This action is permanent and cannot be undone!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                showPSALoader();

                try {
                    const response = await fetch(`/operator/appointments/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (response.ok) {
                        await refreshTable();
                        hidePSALoader();

                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Appointment has been deleted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        hidePSALoader();
                        const data = await response.json();
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to delete appointment.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                } catch (error) {
                    hidePSALoader();
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while deleting the appointment.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        }

        // Bulk actions
        async function bulkConfirm() {
            if (selectedAppointments.size === 0) {
                Swal.fire({
                    title: 'No Selection',
                    text: 'Please select at least one appointment to confirm.',
                    icon: 'warning',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Confirm Appointments?',
                text: `Are you sure you want to confirm ${selectedAppointments.size} appointment(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, confirm all!',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                showPSALoader();

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
                await refreshTable();
                hidePSALoader();

                Swal.fire({
                    title: 'Confirmed!',
                    text: `${selectedAppointments.size} appointment(s) confirmed successfully.`,
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }
        }

        async function bulkCancel() {
            if (selectedAppointments.size === 0) {
                Swal.fire({
                    title: 'No Selection',
                    text: 'Please select at least one appointment to cancel.',
                    icon: 'warning',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Cancel Appointments?',
                text: `Are you sure you want to cancel ${selectedAppointments.size} appointment(s)? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            });

            if (result.isConfirmed) {
                showPSALoader();

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
                await refreshTable();
                hidePSALoader();

                Swal.fire({
                    title: 'Cancelled!',
                    text: `${selectedAppointments.size} appointment(s) cancelled successfully.`,
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }
        }

        async function bulkDelete() {
            if (selectedAppointments.size === 0) {
                Swal.fire({
                    title: 'No Selection',
                    text: 'Please select at least one appointment to delete.',
                    icon: 'warning',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Delete Appointments?',
                text: `⚠️ WARNING: You are about to delete ${selectedAppointments.size} appointment(s). This action is permanent and cannot be undone!`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                showPSALoader();

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
                await refreshTable();
                hidePSALoader();

                Swal.fire({
                    title: 'Deleted!',
                    text: `${selectedAppointments.size} appointment(s) deleted successfully.`,
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
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

            Swal.fire({
                title: 'Exported!',
                text: 'CSV file has been downloaded successfully.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
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
        }

        // Quick add form submission
        document.getElementById('quickAddForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', csrfToken);

            showPSALoader();

            try {
                const response = await fetch('/operator/appointments', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    closeModal('quickAddModal');
                    await refreshTable();
                    hidePSALoader();

                    Swal.fire({
                        title: 'Created!',
                        text: 'Appointment created successfully!',
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                    hidePSALoader();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to create appointment.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                hidePSALoader();
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
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
            const modal = document.getElementById('editModal');
            const modalBody = document.getElementById('editModalBody');

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            modalBody.innerHTML = '<div class="edit-loading">Loading appointment data...</div>';

            try {
                const response = await fetch(`/operator/appointments/${id}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success && data.data) {
                    const apt = data.data.appointment;
                    const timeSlots = data.data.timeSlots || [];

                    let timeSlotOptions = '<option value="">Select Time Slot</option>';
                    timeSlots.forEach(slot => {
                        const selected = apt.time_slot_id === slot.id ? 'selected' : '';
                        timeSlotOptions +=
                            `<option value="${slot.id}" ${selected}>${escapeHtml(slot.start_time)} - ${escapeHtml(slot.end_time)}</option>`;
                    });

                    let clientsHtml = '';
                    if (apt.clients && apt.clients.length > 0) {
                        clientsHtml = '<div class="edit-clients-list">';
                        clientsHtml +=
                            '<div class="edit-clients-title"><i class="fas fa-users"></i> Assigned Applicants</div>';
                        clientsHtml += '<div class="edit-clients-items">';
                        apt.clients.forEach(client => {
                            clientsHtml += `
                            <div class="edit-client-item">
                                <span class="edit-client-name">${escapeHtml(client.first_name)} ${escapeHtml(client.last_name)}</span>
                                <span class="edit-client-service">${escapeHtml(client.service)}</span>
                            </div>
                        `;
                        });
                        clientsHtml += '</div></div>';
                    } else {
                        clientsHtml =
                            '<div class="edit-empty-clients"><i class="fas fa-user-slash"></i> No applicants assigned</div>';
                    }

                    modalBody.innerHTML = `
                    <form id="editAppointmentForm" class="edit-form">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="edit-form-group">
                            <label class="edit-label">Appointment Number</label>
                            <input type="text" class="edit-input edit-input-readonly" value="${escapeHtml(apt.appointment_number)}" disabled>
                        </div>
                        
                        <div class="edit-form-group">
                            <label class="edit-label edit-label-required">Contact Name</label>
                            <input type="text" name="contact_name" class="edit-input" value="${escapeHtml(apt.contact_name)}" required>
                        </div>
                        
                        <div class="edit-form-group">
                            <label class="edit-label edit-label-required">Contact Mobile</label>
                            <input type="text" name="contact_mobile" class="edit-input" value="${escapeHtml(apt.contact_mobile || '')}">
                        </div>
                        
                        <div class="edit-form-group">
                            <label class="edit-label">Contact Email</label>
                            <input type="email" name="contact_email" class="edit-input" value="${escapeHtml(apt.contact_email || '')}">
                        </div>
                        
                        <div class="edit-form-group">
                            <label class="edit-label edit-label-required">Appointment Date</label>
                            <input type="date" name="appointment_date" class="edit-input" value="${apt.appointment_date}" required>
                        </div>
                        
                        <div class="edit-form-group">
                            <label class="edit-label edit-label-required">Time Slot</label>
                            <select name="time_slot_id" class="edit-select" required>
                                ${timeSlotOptions}
                            </select>
                        </div>
                        
                        <div class="edit-form-group">
                            <label class="edit-label">Status</label>
                            <select name="status" class="edit-select">
                                <option value="pending" ${apt.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="confirmed" ${apt.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                <option value="cancelled" ${apt.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                <option value="completed" ${apt.status === 'completed' ? 'selected' : ''}>Completed</option>
                            </select>
                        </div>
                        
                        <div class="edit-form-group">
                            <label class="edit-label">Clients (${apt.clients ? apt.clients.length : 0})</label>
                            ${clientsHtml}
                        </div>
                        
                        <div class="edit-modal-actions">
                            <button type="button" class="edit-btn-cancel" onclick="closeEditModal()">Cancel</button>
                            <button type="submit" class="edit-btn-save">Save Changes</button>
                        </div>
                    </form>
                `;

                    const form = document.getElementById('editAppointmentForm');
                    // Remove any existing listener to avoid duplicates
                    form.removeEventListener('submit', handleEditSubmit);
                    // Add fresh listener
                    form.addEventListener('submit', handleEditSubmit);

                    // Store the appointment id for the submit handler
                    form.dataset.appointmentId = id;

                } else {
                    modalBody.innerHTML =
                        '<div class="edit-error"><i class="fas fa-exclamation-circle"></i> Failed to load edit form. Please try again.</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                modalBody.innerHTML =
                    '<div class="edit-error"><i class="fas fa-exclamation-circle"></i> Network error. Please check your connection and try again.</div>';
            }
        }

        // Separate submit handler for edit form
        async function handleEditSubmit(e) {
            e.preventDefault();

            const form = e.target;
            const id = form.dataset.appointmentId;

            if (!id) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Appointment ID not found.',
                    icon: 'error'
                });
                return;
            }

            const saveButton = form.querySelector('.edit-btn-save');
            const originalText = saveButton.textContent;

            saveButton.textContent = 'Saving...';
            saveButton.disabled = true;
            showPSALoader();

            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            try {
                const response = await fetch(`/operator/appointments/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    closeEditModal();
                    await refreshTable();
                    hidePSALoader();

                    Swal.fire({
                        title: 'Saved!',
                        text: data.message || 'Changes saved successfully!',
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                    hidePSALoader();
                    let errorMessage = data.message || 'Failed to save changes.';
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).flat().join('\n');
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                hidePSALoader();
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while saving. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                saveButton.textContent = originalText;
                saveButton.disabled = false;
            }
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

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
