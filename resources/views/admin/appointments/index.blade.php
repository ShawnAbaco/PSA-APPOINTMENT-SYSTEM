@extends('layouts.admin')

@section('content')
    <div class="appt-container">
        <!-- Header Section -->
        <div class="appt-header">
            <div class="appt-header-left">
                <h1 class="appt-page-title">Appointments Management</h1>
                <p class="appt-page-subtitle">Manage and track all client appointments</p>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="appt-filters-bar">
            <form id="filterForm" method="GET" action="{{ route('admin.appointments.index') }}"
                style="display: contents; width: 100%;">
                <div class="appt-filter-group">
                    <i class="fas fa-tag"></i>
                    <select name="status" id="status" class="appt-filter-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                        </option>
                        <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>

                <div class="appt-filter-group">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" name="date_from" id="date_from" class="appt-filter-input"
                        value="{{ request('date_from') }}" placeholder="Date From">
                </div>

                <div class="appt-filter-group">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" name="date_to" id="date_to" class="appt-filter-input"
                        value="{{ request('date_to') }}" placeholder="Date To">
                </div>

                <div class="appt-filter-group">
                    <i class="fas fa-clock"></i>
                    <select name="time_slot" id="function" class="appt-filter-select">
                        <option value="">All Time Slots</option>
                        @foreach ($timeSlots ?? [] as $slot)
                            <option value="{{ $slot->id }}" {{ request('time_slot') == $slot->id ? 'selected' : '' }}>
                                {{ date('h:i A', strtotime($slot->start_time)) }} -
                                {{ date('h:i A', strtotime($slot->end_time)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="appt-filter-group">
                    <i class="fas fa-city"></i>
                    <select name="city" id="city" class="appt-filter-select">
                        <option value="">All Cities</option>
                        @foreach ($cities ?? [] as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="appt-btn appt-btn-outline">
                    <i class="fas fa-search"></i>
                    Apply Filters
                </button>

                <a href="{{ route('admin.appointments.index') }}" class="appt-btn appt-btn-outline">
                    <i class="fas fa-redo"></i>
                    Reset
                </a>
            </form>
        </div>

        <!-- Appointments Card -->
        <div class="appt-data-card">
            <div class="appt-data-card-header">
                <div class="appt-card-header-left">
                    <h5 class="appt-data-card-title">
                        <i class="fas fa-calendar-check"></i>
                        All Appointments
                    </h5>
                    <span class="appt-appointment-count">{{ $appointments->total() }} total</span>
                </div>
                <div class="appt-card-header-right">
                    <button class="appt-icon-btn" id="refreshBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="appt-data-card-body">
                <div class="appt-table-wrapper">
                    <table class="appt-table">
                        <thead>
                            <tr>
                                <th>ID</th>
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
                                <tr class="appt-row" data-status="{{ $appointment->status }}"
                                    data-date="{{ $appointment->appointment_date }}">
                                    <td class="appt-id">
                                        <i class="fas fa-hashtag"></i>
                                        {{ $appointment->id }}
                                    </td>
                                    <td class="appt-number">
                                        <i class="fas fa-ticket-alt"></i>
                                        {{ $appointment->appointment_number }}
                                    </td>
                                    <td class="appt-date">
                                        <i class="fas fa-calendar-day"></i>
                                        {{ date('M d, Y', strtotime($appointment->appointment_date)) }}
                                        <small class="appt-time-badge">
                                            <i class="fas fa-clock"></i>
                                            @if ($appointment->timeSlot)
                                                {{ date('h:i A', strtotime($appointment->timeSlot->start_time)) }}
                                            @else
                                                {{ date('h:i A', strtotime($appointment->appointment_time ?? '09:00')) }}
                                            @endif
                                        </small>
                                    </td>
                                    <td class="appt-contact-info">
                                        <div class="appt-contact-details">
                                            <strong>{{ $appointment->contact_name }}</strong>
                                            <small><i class="fas fa-phone"></i>
                                                {{ $appointment->contact_mobile ?? ($appointment->contact_phone ?? 'No phone') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="appt-clients-count">
                                        <span class="appt-client-badge">
                                            <i class="fas fa-users"></i>
                                            {{ $appointment->clients->count() }} person(s)
                                        </span>
                                    </td>
                                    <td>
                                        <select
                                            class="appt-status-select appt-status-badge appt-status-{{ $appointment->status }}"
                                            data-id="{{ $appointment->id }}" data-status="{{ $appointment->status }}">
                                            <option value="pending"
                                                {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed"
                                                {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Confirmed
                                            </option>
                                            <option value="completed"
                                                {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed
                                            </option>
                                            <option value="cancelled"
                                                {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled
                                            </option>
                                            <option value="no_show"
                                                {{ $appointment->status == 'no_show' ? 'selected' : '' }}>No Show</option>
                                        </select>
                                    </td>
                                    <td class="appt-actions-cell">
                                        <button class="appt-action-btn appt-view-btn" data-id="{{ $appointment->id }}"
                                            data-appointment-number="{{ $appointment->appointment_number }}"
                                            title="View Details">
                                            <i class="fas fa-eye"></i>
                                            <span>View</span>
                                        </button>
                                        <button class="appt-action-btn appt-delete-btn" data-id="{{ $appointment->id }}"
                                            title="Delete Appointment">
                                            <i class="fas fa-trash-alt"></i>
                                            <span>Delete</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="appt-empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h4>No Appointments Found</h4>
                                        <p>No appointments match your criteria</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Custom Pagination -->
                <div class="pagination-wrapper" id="paginationContainer">
                    @if ($appointments->lastPage() > 1)
                        <div class="pagination-info">
                            Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} of
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
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- View Appointment Modal -->
    <div class="appt-modal" id="viewAppointmentModal">
        <div class="appt-modal-dialog appt-modal-lg">
            <div class="appt-modal-content">
                <div class="appt-modal-header appt-modal-header-primary">
                    <h5 class="appt-modal-title">
                        <i class="fas fa-calendar-alt"></i>
                        Appointment Details
                        <span id="modalAppointmentNumber" class="appt-modal-subtitle"></span>
                    </h5>
                    <button type="button" class="appt-modal-close" data-modal="close">&times;</button>
                </div>
                <div class="appt-modal-body" id="modalBody">
                    <div class="appt-loading" id="modalLoading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading appointment details...</p>
                    </div>
                    <div id="modalContent" style="display: none;"></div>
                </div>
                <div class="appt-modal-footer">
                    <button type="button" class="appt-btn appt-btn-outline" data-modal="close">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Pagination Styles */
        .pagination-wrapper {
            padding: 20px 28px;
            border-top: 1px solid var(--gray-200);
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .pagination-info {
            font-size: 13px;
            color: var(--gray-600);
        }

        .simple-pagination {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pagination-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius-sm);
            color: var(--gray-700);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .pagination-btn:hover:not(.disabled) {
            background: var(--primary-gradient);
            border-color: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: var(--gray-100);
        }

        .pagination-current {
            font-size: 13px;
            color: var(--gray-600);
            padding: 0 8px;
        }

        @media (max-width: 768px) {
            .pagination-wrapper {
                flex-direction: column;
                text-align: center;
                padding: 16px 20px;
            }

            .simple-pagination {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Handle pagination click with AJAX
        function setupPaginationLinks() {
            document.querySelectorAll('#paginationContainer .pagination-btn').forEach(link => {
                if (link.classList.contains('disabled')) return;

                link.removeEventListener('click', link._paginationListener);
                link._paginationListener = function(e) {
                    e.preventDefault();
                    const url = this.href;
                    if (!url) return;

                    showLoading(true);

                    fetch(url, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('appointmentsTableBody').innerHTML = data.html;
                                document.getElementById('paginationContainer').innerHTML = data.pagination;
                                const totalSpan = document.querySelector('.appt-appointment-count');
                                if (totalSpan) totalSpan.textContent = data.total + ' total';
                                attachEventListeners();
                                setupPaginationLinks();
                            }
                            showLoading(false);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Error loading page', 'error');
                            showLoading(false);
                        });
                };
                link.addEventListener('click', link._paginationListener);
            });
        }

        // Show/hide loading overlay
        function showLoading(show) {
            let overlay = document.getElementById('loadingOverlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'loadingOverlay';
                overlay.style.cssText =
                    'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: none; align-items: center; justify-content: center;';
                overlay.innerHTML =
                    '<div style="background: white; padding: 20px; border-radius: 12px; text-align: center;"><i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #0f3b6f;"></i><p style="margin-top: 12px;">Loading...</p></div>';
                document.body.appendChild(overlay);
            }
            overlay.style.display = show ? 'flex' : 'none';
        }

        // Attach all event listeners
        function attachEventListeners() {
            // View buttons
            document.querySelectorAll('.appt-view-btn').forEach(btn => {
                btn.removeEventListener('click', btn._listener);
                btn._listener = function() {
                    const appointmentId = this.dataset.id;
                    const appointmentNumber = this.dataset.appointmentNumber;
                    openModal(appointmentId, appointmentNumber);
                };
                btn.addEventListener('click', btn._listener);
            });

            // Status change
            document.querySelectorAll('.appt-status-select').forEach(select => {
                select.removeEventListener('change', select._listener);
                select._listener = function() {
                    const id = this.dataset.id;
                    const status = this.value;
                    const originalStatus = this.getAttribute('data-status');

                    if (confirm(`Change appointment status to ${status.toUpperCase()}?`)) {
                        showLoading(true);
                        fetch(`/admin/appointments/${id}/status`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    status: status
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.className =
                                        `appt-status-select appt-status-badge appt-status-${status}`;
                                    this.setAttribute('data-status', status);
                                    this.closest('tr').setAttribute('data-status', status);
                                    showToast('Status updated successfully', 'success');
                                    setTimeout(() => location.reload(), 1500);
                                } else {
                                    this.value = originalStatus;
                                    showToast(data.message || 'Error updating status', 'error');
                                    showLoading(false);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.value = originalStatus;
                                showToast('Error updating status', 'error');
                                showLoading(false);
                            });
                    } else {
                        this.value = originalStatus;
                    }
                };
                select.addEventListener('change', select._listener);
            });

            // Delete buttons
            document.querySelectorAll('.appt-delete-btn').forEach(btn => {
                btn.removeEventListener('click', btn._listener);
                btn._listener = function() {
                    const id = this.dataset.id;

                    if (confirm(
                            'Are you sure you want to delete this appointment? This action cannot be undone.'
                            )) {
                        showLoading(true);
                        fetch(`/admin/appointments/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showToast('Appointment deleted successfully', 'success');
                                    location.reload();
                                } else {
                                    showToast(data.message || 'Error deleting appointment', 'error');
                                    showLoading(false);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showToast('Error deleting appointment', 'error');
                                showLoading(false);
                            });
                    }
                };
                btn.addEventListener('click', btn._listener);
            });
        }

        // Modal functions
        const modal = document.getElementById('viewAppointmentModal');
        const modalLoading = document.getElementById('modalLoading');
        const modalContent = document.getElementById('modalContent');
        const modalAppointmentNumber = document.getElementById('modalAppointmentNumber');

        function openModal(appointmentId, appointmentNumber) {
            modal.classList.add('show');
            modalAppointmentNumber.textContent = `#${appointmentNumber}`;
            modalLoading.style.display = 'block';
            modalContent.style.display = 'none';
            modalContent.innerHTML = '';

            fetch(`/admin/appointments/${appointmentId}/modal`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'text/html'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    modalLoading.style.display = 'none';
                    modalContent.innerHTML = html;
                    modalContent.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalLoading.innerHTML = `
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ef4444;"></i>
                        <p>Error loading appointment details.</p>
                        <button class="appt-btn appt-btn-outline" onclick="location.reload()">Reload</button>
                    `;
                });
        }

        function closeModal() {
            modal.classList.remove('show');
            modalContent.innerHTML = '';
            modalContent.style.display = 'none';
            modalLoading.style.display = 'block';
            modalLoading.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading appointment details...</p>
            `;
        }

        // Refresh button
        document.getElementById('refreshBtn')?.addEventListener('click', function() {
            location.reload();
        });

        // Close modal buttons
        document.querySelectorAll('[data-modal="close"]').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('show')) closeModal();
        });

        // Initialize
        attachEventListeners();
        setupPaginationLinks();
    </script>
@endsection
