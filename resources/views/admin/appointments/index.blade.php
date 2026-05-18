@extends('layouts.admin')

@section('content')
    <div class="appt-container">
        <!-- Header Section -->
        <div class="appt-header">
            <div class="appt-header-left">
                <h1 class="appt-page-title">Appointments Management</h1>
                <p class="appt-page-subtitle">Manage and track all client appointments</p>
            </div>
            <div class="appt-header-right">

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
                    <button class="appt-btn appt-btn-primary" id="createAppointmentBtn">
                        <i class="fas fa-plus"></i>
                        Create Appointment
                    </button>
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

    <!-- Create Appointment Modal -->
    <div class="appt-modal" id="createAppointmentModal">
        <div class="appt-modal-dialog appt-modal-lg">
            <div class="appt-modal-content">
                <div class="appt-modal-header appt-modal-header-success">
                    <h5 class="appt-modal-title">
                        <i class="fas fa-plus-circle"></i>
                        Create New Appointment
                    </h5>
                    <button type="button" class="appt-modal-close" data-modal="close">&times;</button>
                </div>
                <div class="appt-modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form id="createAppointmentForm">
                        @csrf

                        <!-- Contact Information Section -->
                        <div class="appt-form-section">
                            <h6 class="appt-section-title">
                                <i class="fas fa-user"></i>
                                Contact Information
                            </h6>
                            <div class="appt-form-row">
                                <div class="appt-form-group">
                                    <label class="appt-form-label">Contact Name *</label>
                                    <input type="text" name="contact_name" class="appt-form-input" required>
                                </div>
                                <div class="appt-form-group">
                                    <label class="appt-form-label">Contact Mobile *</label>
                                    <input type="text" name="contact_mobile" class="appt-form-input" required>
                                </div>
                            </div>
                            <div class="appt-form-row">
                                <div class="appt-form-group">
                                    <label class="appt-form-label">Contact Email</label>
                                    <input type="email" name="contact_email" class="appt-form-input">
                                </div>
                            </div>
                        </div>

                        <!-- Appointment Details Section -->
                        <div class="appt-form-section">
                            <h6 class="appt-section-title">
                                <i class="fas fa-calendar-alt"></i>
                                Appointment Details
                            </h6>
                            <div class="appt-form-row">
                                <div class="appt-form-group">
                                    <label class="appt-form-label">Appointment Date *</label>
                                    <input type="date" name="appointment_date" class="appt-form-input" required>
                                </div>
                                <div class="appt-form-group">
                                    <label class="appt-form-label">Time Slot *</label>
                                    <select name="appointment_time_slot_id" class="appt-form-input" required>
                                        <option value="">Select Time Slot</option>
                                        @foreach ($timeSlots ?? [] as $slot)
                                            <option value="{{ $slot->id }}">
                                                {{ date('h:i A', strtotime($slot->start_time)) }} -
                                                {{ date('h:i A', strtotime($slot->end_time)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Clients Section -->
                        <div class="appt-form-section">
                            <h6 class="appt-section-title">
                                <i class="fas fa-users"></i>
                                Clients
                                <button type="button" class="appt-add-client-btn" id="addClientBtn">
                                    <i class="fas fa-plus"></i> Add Client
                                </button>
                            </h6>
                            <div id="clientsContainer">
                                <div class="client-card" data-client-index="0">
                                    <div class="client-card-header">
                                        <span class="client-number">Client #1</span>
                                        <button type="button" class="remove-client-btn" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="appt-form-row">
                                        <div class="appt-form-group">
                                            <label class="appt-form-label">First Name *</label>
                                            <input type="text" name="clients[0][first_name]" class="appt-form-input"
                                                required>
                                        </div>
                                        <div class="appt-form-group">
                                            <label class="appt-form-label">Middle Name</label>
                                            <input type="text" name="clients[0][middle_name]" class="appt-form-input">
                                        </div>
                                    </div>
                                    <div class="appt-form-row">
                                        <div class="appt-form-group">
                                            <label class="appt-form-label">Last Name *</label>
                                            <input type="text" name="clients[0][last_name]" class="appt-form-input"
                                                required>
                                        </div>
                                        <div class="appt-form-group">
                                            <label class="appt-form-label">Suffix</label>
                                            <input type="text" name="clients[0][suffix]" class="appt-form-input"
                                                placeholder="Jr., Sr., III">
                                        </div>
                                    </div>
                                    <div class="appt-form-row">
                                        <div class="appt-form-group">
                                            <label class="appt-form-label">Sex *</label>
                                            <select name="clients[0][sex]" class="appt-form-input" required>
                                                <option value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                        <div class="appt-form-group">
                                            <label class="appt-form-label">Birthdate *</label>
                                            <input type="date" name="clients[0][birthdate]" class="appt-form-input"
                                                required>
                                        </div>
                                    </div>
                                    <div class="appt-form-group">
                                        <label class="appt-form-label">Service Type *</label>
                                        <select name="clients[0][service]" class="appt-form-input service-select"
                                            required>
                                            <option value="">Select Service</option>
                                            <option value="reg">Registration</option>
                                            <option value="updating">Correction/Updating</option>
                                            <option value="inquiry">Status Inquiry</option>
                                        </select>
                                    </div>
                                    <div class="inquiry-fields-0" style="display: none;">
                                        <div class="appt-form-group">
                                            <label class="appt-form-label">
                                                <input type="checkbox" name="clients[0][has_trn]" value="1">
                                                Has TRN Number?
                                            </label>
                                        </div>
                                        <div class="appt-form-group">
                                            <label class="appt-form-label">TRN Number</label>
                                            <input type="text" name="clients[0][trn_number]" class="appt-form-input"
                                                placeholder="29-digit TRN number">
                                            <small class="appt-form-hint">Required if has TRN is checked</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="appt-form-hint" style="margin-top: 12px;">
                                <i class="fas fa-info-circle"></i> Maximum of 4 clients per appointment
                            </div>
                        </div>
                    </form>
                </div>
                <div class="appt-modal-footer">
                    <button type="button" class="appt-btn appt-btn-outline" data-modal="close">Cancel</button>
                    <button type="button" class="appt-btn appt-btn-primary" id="submitAppointmentBtn">
                        <i class="fas fa-save"></i>
                        Create Appointment
                    </button>
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

        /* Create Appointment Modal Styles */
        .appt-modal-header-success {
            background: var(--title);
        }

        .appt-form-section {
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-200);
        }

        .appt-form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .appt-section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .appt-section-title i {
            color: var(--primary);
        }

        .appt-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .appt-form-group {
            margin-bottom: 16px;
        }

        .appt-form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 6px;
        }

        .appt-form-input,
        .appt-form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius-sm);
            font-size: 14px;
            transition: var(--transition);
        }

        .appt-form-input:focus,
        .appt-form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 59, 111, 0.1);
        }

        .appt-form-textarea {
            resize: vertical;
            font-family: inherit;
        }

        .appt-form-hint {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 4px;
        }

        .appt-add-client-btn {
            margin-left: auto;
            background: var(--primary);
            color: white;
            border: none;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: var(--transition);
        }

        .appt-add-client-btn:hover {
            background: var(--primary-dark);
        }

        .client-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            padding: 16px;
            margin-bottom: 16px;
        }

        .client-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--gray-200);
        }

        .client-number {
            font-weight: 600;
            color: var(--primary);
        }

        .remove-client-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
        }

        .remove-client-btn:hover {
            color: #dc2626;
            transform: scale(1.1);
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

            .appt-form-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }
    </style>

    <script>
        let clientCounter = 1;

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

        // Add client to form
        function addClient() {
            if (clientCounter >= 4) {
                showToast('Maximum of 4 clients per appointment', 'error');
                return;
            }

            const clientsContainer = document.getElementById('clientsContainer');
            const newClient = document.createElement('div');
            newClient.className = 'client-card';
            newClient.setAttribute('data-client-index', clientCounter);
            newClient.innerHTML = `
                <div class="client-card-header">
                    <span class="client-number">Client #${clientCounter + 1}</span>
                    <button type="button" class="remove-client-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="appt-form-row">
                    <div class="appt-form-group">
                        <label class="appt-form-label">First Name *</label>
                        <input type="text" name="clients[${clientCounter}][first_name]" class="appt-form-input" required>
                    </div>
                    <div class="appt-form-group">
                        <label class="appt-form-label">Middle Name</label>
                        <input type="text" name="clients[${clientCounter}][middle_name]" class="appt-form-input">
                    </div>
                </div>
                <div class="appt-form-row">
                    <div class="appt-form-group">
                        <label class="appt-form-label">Last Name *</label>
                        <input type="text" name="clients[${clientCounter}][last_name]" class="appt-form-input" required>
                    </div>
                    <div class="appt-form-group">
                        <label class="appt-form-label">Suffix</label>
                        <input type="text" name="clients[${clientCounter}][suffix]" class="appt-form-input" placeholder="Jr., Sr., III">
                    </div>
                </div>
                <div class="appt-form-row">
                    <div class="appt-form-group">
                        <label class="appt-form-label">Sex *</label>
                        <select name="clients[${clientCounter}][sex]" class="appt-form-input" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="appt-form-group">
                        <label class="appt-form-label">Birthdate *</label>
                        <input type="date" name="clients[${clientCounter}][birthdate]" class="appt-form-input" required>
                    </div>
                </div>
                <div class="appt-form-group">
                    <label class="appt-form-label">Service Type *</label>
                    <select name="clients[${clientCounter}][service]" class="appt-form-input service-select" required>
                        <option value="">Select Service</option>
                        <option value="reg">Registration</option>
                        <option value="updating">Correction/Updating</option>
                        <option value="inquiry">Status Inquiry</option>
                    </select>
                </div>
                <div class="inquiry-fields-${clientCounter}" style="display: none;">
                    <div class="appt-form-group">
                        <label class="appt-form-label">
                            <input type="checkbox" name="clients[${clientCounter}][has_trn]" value="1">
                            Has TRN Number?
                        </label>
                    </div>
                    <div class="appt-form-group">
                        <label class="appt-form-label">TRN Number</label>
                        <input type="text" name="clients[${clientCounter}][trn_number]" class="appt-form-input" placeholder="29-digit TRN number">
                        <small class="appt-form-hint">Required if has TRN is checked</small>
                    </div>
                </div>
            `;

            clientsContainer.appendChild(newClient);
            clientCounter++;

            // Attach remove client event
            newClient.querySelector('.remove-client-btn').addEventListener('click', function() {
                newClient.remove();
                updateClientNumbers();
            });

            // Attach service select change event for new client
            const serviceSelect = newClient.querySelector('.service-select');
            const inquiryFields = newClient.querySelector(`[class^="inquiry-fields-"]`);
            serviceSelect.addEventListener('change', function() {
                if (this.value === 'inquiry') {
                    inquiryFields.style.display = 'block';
                } else {
                    inquiryFields.style.display = 'none';
                }
            });
        }

        // Update client numbers after removal
        function updateClientNumbers() {
            const clients = document.querySelectorAll('.client-card');
            clients.forEach((client, index) => {
                const clientNumberSpan = client.querySelector('.client-number');
                clientNumberSpan.textContent = `Client #${index + 1}`;

                // Update all input names
                const inputs = client.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        const newName = name.replace(/clients\[\d+\]/, `clients[${index}]`);
                        input.setAttribute('name', newName);
                    }
                });

                client.setAttribute('data-client-index', index);

                // Show remove button for all except first client
                const removeBtn = client.querySelector('.remove-client-btn');
                if (removeBtn) {
                    removeBtn.style.display = index === 0 ? 'none' : 'block';
                }
            });
            clientCounter = clients.length;
        }

        // Submit appointment
        function submitAppointment() {
            const form = document.getElementById('createAppointmentForm');
            const formData = new FormData(form);

            // Validate at least one client
            const clients = document.querySelectorAll('.client-card');
            if (clients.length === 0) {
                showToast('Please add at least one client', 'error');
                return;
            }

            showLoading(true);

            fetch('{{ route('admin.appointments.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showLoading(false);
                    if (data.success) {
                        showToast(data.message, 'success');
                        closeCreateModal();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(data.message || 'Error creating appointment', 'error');
                    }
                })
                .catch(error => {
                    showLoading(false);
                    console.error('Error:', error);
                    showToast('Error creating appointment', 'error');
                });
        }

        // Create modal functions
        const createModal = document.getElementById('createAppointmentModal');

        function openCreateModal() {
            createModal.classList.add('show');
        }

        function closeCreateModal() {
            createModal.classList.remove('show');
            document.getElementById('createAppointmentForm').reset();
            // Reset clients to just one
            const clientsContainer = document.getElementById('clientsContainer');
            clientsContainer.innerHTML = `
                <div class="client-card" data-client-index="0">
                    <div class="client-card-header">
                        <span class="client-number">Client #1</span>
                        <button type="button" class="remove-client-btn" style="display: none;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="appt-form-row">
                        <div class="appt-form-group">
                            <label class="appt-form-label">First Name *</label>
                            <input type="text" name="clients[0][first_name]" class="appt-form-input" required>
                        </div>
                        <div class="appt-form-group">
                            <label class="appt-form-label">Middle Name</label>
                            <input type="text" name="clients[0][middle_name]" class="appt-form-input">
                        </div>
                    </div>
                    <div class="appt-form-row">
                        <div class="appt-form-group">
                            <label class="appt-form-label">Last Name *</label>
                            <input type="text" name="clients[0][last_name]" class="appt-form-input" required>
                        </div>
                        <div class="appt-form-group">
                            <label class="appt-form-label">Suffix</label>
                            <input type="text" name="clients[0][suffix]" class="appt-form-input" placeholder="Jr., Sr., III">
                        </div>
                    </div>
                    <div class="appt-form-row">
                        <div class="appt-form-group">
                            <label class="appt-form-label">Sex *</label>
                            <select name="clients[0][sex]" class="appt-form-input" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="appt-form-group">
                            <label class="appt-form-label">Birthdate *</label>
                            <input type="date" name="clients[0][birthdate]" class="appt-form-input" required>
                        </div>
                    </div>
                    <div class="appt-form-group">
                        <label class="appt-form-label">Service Type *</label>
                        <select name="clients[0][service]" class="appt-form-input service-select" required>
                            <option value="">Select Service</option>
                            <option value="reg">Registration</option>
                            <option value="updating">Correction/Updating</option>
                            <option value="inquiry">Status Inquiry</option>
                        </select>
                    </div>
                    <div class="inquiry-fields-0" style="display: none;">
                        <div class="appt-form-group">
                            <label class="appt-form-label">
                                <input type="checkbox" name="clients[0][has_trn]" value="1">
                                Has TRN Number?
                            </label>
                        </div>
                        <div class="appt-form-group">
                            <label class="appt-form-label">TRN Number</label>
                            <input type="text" name="clients[0][trn_number]" class="appt-form-input" placeholder="29-digit TRN number">
                            <small class="appt-form-hint">Required if has TRN is checked</small>
                        </div>
                    </div>
                </div>
            `;
            clientCounter = 1;

            // Re-attach service select event for the reset client
            const firstServiceSelect = document.querySelector('.service-select');
            const firstInquiryFields = document.querySelector('[class^="inquiry-fields-"]');
            if (firstServiceSelect) {
                firstServiceSelect.addEventListener('change', function() {
                    if (this.value === 'inquiry') {
                        firstInquiryFields.style.display = 'block';
                    } else {
                        firstInquiryFields.style.display = 'none';
                    }
                });
            }
        }

        // View modal functions
        const viewModal = document.getElementById('viewAppointmentModal');
        const modalLoading = document.getElementById('modalLoading');
        const modalContent = document.getElementById('modalContent');
        const modalAppointmentNumber = document.getElementById('modalAppointmentNumber');

        function openViewModal(appointmentId, appointmentNumber) {
            viewModal.classList.add('show');
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

        function closeModal(modalElement) {
            modalElement.classList.remove('show');
            if (modalElement.id === 'viewAppointmentModal') {
                modalContent.innerHTML = '';
                modalContent.style.display = 'none';
                modalLoading.style.display = 'block';
                modalLoading.innerHTML = `
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading appointment details...</p>
                `;
            }
        }

        // Attach all event listeners
        function attachEventListeners() {
            // View buttons
            document.querySelectorAll('.appt-view-btn').forEach(btn => {
                btn.removeEventListener('click', btn._listener);
                btn._listener = function() {
                    const appointmentId = this.dataset.id;
                    const appointmentNumber = this.dataset.appointmentNumber;
                    openViewModal(appointmentId, appointmentNumber);
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

        // Create Appointment Button
        document.getElementById('createAppointmentBtn')?.addEventListener('click', openCreateModal);

        // Submit Appointment Button
        document.getElementById('submitAppointmentBtn')?.addEventListener('click', submitAppointment);

        // Add Client Button
        document.getElementById('addClientBtn')?.addEventListener('click', addClient);

        // Close modal buttons
        document.querySelectorAll('[data-modal="close"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.appt-modal');
                if (modal) closeModal(modal);
            });
        });

        // Close modal on outside click
        document.querySelectorAll('.appt-modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal(modal);
            });
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.appt-modal.show').forEach(modal => {
                    closeModal(modal);
                });
            }
        });

        // Initialize service select listeners for existing clients
        document.querySelectorAll('.service-select').forEach((select, index) => {
            const inquiryFields = document.querySelector(`.inquiry-fields-${index}`);
            if (inquiryFields) {
                select.addEventListener('change', function() {
                    if (this.value === 'inquiry') {
                        inquiryFields.style.display = 'block';
                    } else {
                        inquiryFields.style.display = 'none';
                    }
                });
            }
        });

        // Refresh button
        document.getElementById('refreshBtn')?.addEventListener('click', function() {
            location.reload();
        });

        // Initialize
        attachEventListeners();
        setupPaginationLinks();
    </script>
@endsection
