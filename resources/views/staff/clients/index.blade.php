@extends('layouts.staff')

@section('content')
    <div class="clients-container">
        <!-- Header Section -->
        <div class="clients-header">
            <div>
                <h1 class="page-title">Applicants</h1>
                <p class="page-subtitle">Manage and monitor all applicant appointments</p>
            </div>
            <div class="page-date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="clients-filters-bar">
            <div class="filter-group">
                <i class="fas fa-search"></i>
                <input type="text" id="searchClient" placeholder="Search by name, ID, or TRN..." class="filter-input">
            </div>
            <div class="filter-group">
                <i class="fas fa-tag"></i>
                <select id="serviceFilter" class="filter-select">
                    <option value="">All Services</option>
                    <option value="reg">National ID Registration</option>
                    <option value="updating">Correction/Updating</option>
                    <option value="inquiry">Status Inquiry / TRN Retrieval</option>
                </select>
            </div>
            <div class="filter-group">
                <i class="fas fa-calendar-alt"></i>
                <span style="font-size: 13px; color: #6c757d;">From:</span>
                <input type="date" id="dateFromFilter" class="filter-input" style="width: 140px;">
                <span style="font-size: 13px; color: #6c757d;">To:</span>
                <input type="date" id="dateToFilter" class="filter-input" style="width: 140px;">
            </div>
            <div class="filter-group">
                <i class="fas fa-clock"></i>
                <select id="timeSlotFilter" class="filter-select">
                    <option value="">All Time Slots</option>
                    @php
                        $timeSlots = \App\Models\TimeSlot::where('is_active', true)->orderBy('display_order')->get();
                    @endphp
                    @foreach($timeSlots as $slot)
                        <option value="{{ $slot->id }}">{{ $slot->label }} ({{ $slot->start_time }} - {{ $slot->end_time }})</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-reset" id="resetFilters">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
        </div>

        <!-- Today/Week Quick Filters -->
        <div class="quick-filters-bar">
            <button class="quick-filter-btn" data-quick="today">Today</button>
            <button class="quick-filter-btn" data-quick="tomorrow">Tomorrow</button>
            <button class="quick-filter-btn" data-quick="this_week">This Week</button>
            <button class="quick-filter-btn" data-quick="next_week">Next Week</button>
            <button class="quick-filter-btn" data-quick="this_month">This Month</button>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title-section">
                    <h3>All Applicants</h3>
                    <span class="record-count" id="recordCount">{{ $clients->total() }} records</span>
                </div>
                <div class="table-actions">
                    <a href="{{ route('staff.applicants.export') }}" class="btn-icon" title="Export to CSV">
                        <i class="fas fa-download"></i>
                    </a>
                    <button class="btn-icon" id="refreshBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="clients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Sex</th>
                            <th>Birthdate</th>
                            <th>Age</th>
                            <th>Service</th>
                            <th>Appointment Date</th>
                            <th>Appointment Time</th>
                            <th>Appointment #</th>
                        </tr>
                    </thead>
                    <tbody id="clientsTableBody">
                        @forelse($clients as $client)
                            <tr class="client-row" data-id="{{ $client->id }}" data-client-id="{{ $client->id }}"
                                data-verified="{{ $client->is_verified ? 'verified' : 'pending' }}"
                                data-sex="{{ $client->sex }}" data-service="{{ $client->service }}"
                                data-age="{{ \Carbon\Carbon::parse($client->birthdate)->age }}"
                                data-appointment-date="{{ $client->appointment?->appointment_date ? \Carbon\Carbon::parse($client->appointment->appointment_date)->format('Y-m-d') : '' }}"
                                data-time-slot-id="{{ $client->appointment?->time_slot_id ?? '' }}">
                                <td class="client-id">{{ $client->id }}</td>
                                <td class="client-name">
                                    <div class="client-name-info">
                                        <strong>{{ $client->first_name }} {{ $client->last_name }}</strong>
                                        @if ($client->middle_name)
                                            <small class="text-muted">{{ $client->middle_name }}</small>
                                        @endif
                                        @if ($client->suffix)
                                            <small class="text-muted">({{ $client->suffix }})</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="sex-badge {{ $client->sex == 'Male' ? 'male' : 'female' }}">
                                        <i class="fas {{ $client->sex == 'Male' ? 'fa-mars' : 'fa-venus' }}"></i>
                                        {{ $client->sex }}
                                    </span>
                                </td>
                                <td>{{ date('M d, Y', strtotime($client->birthdate)) }}</td>
                                <td>{{ \Carbon\Carbon::parse($client->birthdate)->age }} years</td>
                                <td>
                                    <span class="service-badge">{{ $services[$client->service] ?? $client->service }}</span>
                                </td>
                                <td class="appointment-date">
                                    @if ($client->appointment && $client->appointment->appointment_date)
                                        {{ \Carbon\Carbon::parse($client->appointment->appointment_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="appointment-time">
                                    @if ($client->appointment && $client->appointment->timeSlot)
                                        <span class="time-badge">{{ $client->appointment->timeSlot->label }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($client->appointment)
                                        <button class="appointment-link show-client-modal"
                                            data-client-id="{{ $client->id }}"
                                            style="background: none; border: none; cursor: pointer;">
                                            {{ $client->appointment->appointment_number }}
                                        </button>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h4>No applicants found</h4>
                                    <p>No applicants match your current filters</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing {{ $clients->firstItem() ?? 0 }} to {{ $clients->lastItem() ?? 0 }} of
                    {{ $clients->total() }} applicants
                </div>
                <div class="simple-pagination">
                    @if ($clients->onFirstPage())
                        <button class="pagination-btn disabled" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                    @else
                        <a href="{{ $clients->previousPageUrl() }}" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    @endif

                    <span class="pagination-current">
                        Page {{ $clients->currentPage() }} of {{ $clients->lastPage() }}
                    </span>

                    @if ($clients->hasMorePages())
                        <a href="{{ $clients->nextPageUrl() }}" class="pagination-btn">
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

    <!-- Custom Modal -->
    <div id="clientDetailModal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-container modal-xl">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-user-circle"></i>
                    Applicant Details
                </h3>
                <button class="modal-close" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body" id="clientModalBody">
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Loading applicant details...</p>
                </div>
            </div>
            <div class="modal-footer"
                style="padding: 16px 24px; background: #f8f9fa; border-top: 1px solid #e9ecef; display: flex; justify-content: flex-end; gap: 12px;">
                <button class="modern-btn" id="closeModalFooterBtn" style="background: #6c757d;">Close</button>
                <button class="modern-btn" id="verifyClientModalBtn"
                    style="display: none; background: linear-gradient(135deg, #28a745, #20c997);">
                    <i class="fas fa-check-circle"></i> Verify Applicant
                </button>
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

    <style>
        /* Additional styles */
        .time-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .quick-filters-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            flex-wrap: wrap;
        }
        
        .quick-filter-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .quick-filter-btn:hover {
            background: #e9ecef;
        }
        
        .quick-filter-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .appointment-date, .appointment-time {
            font-size: 13px;
        }
        
        .active-filter {
            background: #007bff;
            color: white;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 11px;
            margin-left: 8px;
        }
        
        @media (max-width: 1200px) {
            .clients-filters-bar {
                flex-wrap: wrap;
            }
            .filter-group {
                flex-wrap: wrap;
            }
        }
    </style>

    <script>
        // ============================================
        // CLIENT MODAL FUNCTIONALITY
        // ============================================

        const csrfToken = '{{ csrf_token() }}';
        let currentClientId = null;

        // DOM Elements
        const modal = document.getElementById('clientDetailModal');
        const modalBody = document.getElementById('clientModalBody');
        const verifyBtn = document.getElementById('verifyClientModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const closeModalFooterBtn = document.getElementById('closeModalFooterBtn');
        const modalOverlay = document.querySelector('.modal-overlay');

        // Open modal function
        function openClientModal(clientId) {
            currentClientId = clientId;

            modal.classList.add('active');
            modalBody.innerHTML = `
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Loading applicant details...</p>
                </div>
            `;
            verifyBtn.style.display = 'none';

            fetch(`/staff/applicants/${clientId}/modal`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                    const isVerified = modalBody.innerHTML.includes('status-badge-modal verified') ||
                        !modalBody.innerHTML.includes('Pending Verification');
                    if (!isVerified && modalBody.innerHTML.includes('Pending Verification')) {
                        verifyBtn.style.display = 'inline-flex';
                    } else {
                        verifyBtn.style.display = 'none';
                    }
                    attachModalEventListeners();
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 12px; display: block;"></i>
                        <strong>Failed to load applicant details.</strong><br>
                        Please try again.
                    </div>
                `;
                });
        }

        function closeModal() {
            modal.classList.remove('active');
            currentClientId = null;
        }

        function attachModalEventListeners() {
            const updateRefBtn = document.querySelector('.update-reference-btn');
            if (updateRefBtn) {
                const newBtn = updateRefBtn.cloneNode(true);
                updateRefBtn.parentNode.replaceChild(newBtn, updateRefBtn);
                newBtn.addEventListener('click', function() {
                    const clientId = this.getAttribute('data-id');
                    const refNumber = document.getElementById('modalReferenceNumber')?.value;
                    if (!refNumber) {
                        showNotification('Please enter a reference number', 'error');
                        return;
                    }
                    updateReferenceNumber(clientId, refNumber);
                });
            }
        }

        function verifyClientFromModal(clientId) {
            fetch(`/staff/applicants/${clientId}/verify`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Applicant verified successfully!');
                        if (currentClientId) {
                            openClientModal(currentClientId);
                        }
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showNotification(data.message || 'Failed to verify applicant', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while verifying applicant', 'error');
                });
        }

        function updateReferenceNumber(clientId, refNumber) {
            fetch(`/staff/applicants/${clientId}/reference`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        psa_reference_number: refNumber
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Reference number updated successfully!');
                        if (currentClientId) {
                            openClientModal(currentClientId);
                        }
                    } else {
                        showNotification(data.message || 'Failed to update reference number', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while updating reference number', 'error');
                });
        }

        // ============================================
        // FILTERING & TABLE FUNCTIONS
        // ============================================

        let filterTimeout;
        let currentQuickFilter = '';

        // Helper function to format date as YYYY-MM-DD
        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        // Filter table function with date range
        function filterTable() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                const searchTerm = document.getElementById('searchClient')?.value.toLowerCase() || '';
                const serviceFilter = document.getElementById('serviceFilter')?.value || '';
                const dateFrom = document.getElementById('dateFromFilter')?.value || '';
                const dateTo = document.getElementById('dateToFilter')?.value || '';
                const timeSlotFilter = document.getElementById('timeSlotFilter')?.value || '';

                const rows = document.querySelectorAll('.client-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const clientId = row.querySelector('.client-id')?.textContent || '';
                    const clientName = row.querySelector('.client-name strong')?.textContent.toLowerCase() || '';
                    const service = row.getAttribute('data-service') || '';
                    const appointmentDate = row.getAttribute('data-appointment-date') || '';
                    const timeSlotId = row.getAttribute('data-time-slot-id') || '';

                    const matchesSearch = !searchTerm || clientId.includes(searchTerm) || clientName.includes(searchTerm);
                    const matchesService = !serviceFilter || service === serviceFilter;
                    
                    // Date range filter
                    let matchesDateRange = true;
                    if (dateFrom && appointmentDate) {
                        matchesDateRange = appointmentDate >= dateFrom;
                    }
                    if (dateTo && appointmentDate && matchesDateRange) {
                        matchesDateRange = appointmentDate <= dateTo;
                    }
                    if ((dateFrom || dateTo) && !appointmentDate) {
                        matchesDateRange = false;
                    }
                    
                    const matchesTimeSlot = !timeSlotFilter || timeSlotId === timeSlotFilter;

                    const shouldShow = matchesSearch && matchesService && matchesDateRange && matchesTimeSlot;
                    row.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                });

                const recordCountSpan = document.getElementById('recordCount');
                if (recordCountSpan) {
                    const totalRows = document.querySelectorAll('.client-row').length;
                    if (visibleCount !== totalRows) {
                        recordCountSpan.textContent = visibleCount + ' records (filtered)';
                    } else {
                        recordCountSpan.textContent = '{{ $clients->total() }} records';
                    }
                }
                
                showActiveFilters();
            }, 300);
        }

        // Quick filter function (now sets both from and to dates)
        function setQuickFilter(type) {
            const dateFromInput = document.getElementById('dateFromFilter');
            const dateToInput = document.getElementById('dateToFilter');
            const today = new Date();
            
            // Clear any existing active classes
            document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            switch(type) {
                case 'today':
                    dateFromInput.value = formatDate(today);
                    dateToInput.value = formatDate(today);
                    break;
                case 'tomorrow':
                    const tomorrow = new Date(today);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    dateFromInput.value = formatDate(tomorrow);
                    dateToInput.value = formatDate(tomorrow);
                    break;
                case 'this_week':
                    // Get Monday (start of week)
                    const monday = new Date(today);
                    const day = today.getDay();
                    const diffToMonday = today.getDate() - day + (day === 0 ? -6 : 1);
                    monday.setDate(diffToMonday);
                    // Get Sunday (end of week)
                    const sunday = new Date(monday);
                    sunday.setDate(monday.getDate() + 6);
                    dateFromInput.value = formatDate(monday);
                    dateToInput.value = formatDate(sunday);
                    showNotification(`Showing appointments from ${formatDate(monday)} to ${formatDate(sunday)}`, 'info');
                    break;
                case 'next_week':
                    const nextMonday = new Date(today);
                    const nextDay = today.getDay();
                    const nextDiffToMonday = today.getDate() - nextDay + (nextDay === 0 ? -6 : 1) + 7;
                    nextMonday.setDate(nextDiffToMonday);
                    const nextSunday = new Date(nextMonday);
                    nextSunday.setDate(nextMonday.getDate() + 6);
                    dateFromInput.value = formatDate(nextMonday);
                    dateToInput.value = formatDate(nextSunday);
                    showNotification(`Showing appointments from ${formatDate(nextMonday)} to ${formatDate(nextSunday)}`, 'info');
                    break;
                case 'this_month':
                    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    dateFromInput.value = formatDate(firstDay);
                    dateToInput.value = formatDate(lastDay);
                    showNotification(`Showing appointments from ${formatDate(firstDay)} to ${formatDate(lastDay)}`, 'info');
                    break;
                default:
                    dateFromInput.value = '';
                    dateToInput.value = '';
            }
            
            // Add active class to clicked button if it set a value
            if (dateFromInput.value) {
                const clickedBtn = document.querySelector(`.quick-filter-btn[data-quick="${type}"]`);
                if (clickedBtn) clickedBtn.classList.add('active');
            }
            
            filterTable();
        }

        function showActiveFilters() {
            const activeFilters = [];
            if (document.getElementById('searchClient')?.value) activeFilters.push('Search');
            if (document.getElementById('serviceFilter')?.value) activeFilters.push('Service');
            if (document.getElementById('dateFromFilter')?.value || document.getElementById('dateToFilter')?.value) activeFilters.push('Date Range');
            if (document.getElementById('timeSlotFilter')?.value) activeFilters.push('Time Slot');
            
            const resetBtn = document.getElementById('resetFilters');
            const existingIndicator = resetBtn.querySelector('.active-filter');
            
            if (activeFilters.length > 0) {
                if (!existingIndicator) {
                    const indicator = document.createElement('span');
                    indicator.className = 'active-filter';
                    indicator.textContent = activeFilters.length + ' filter(s) active';
                    resetBtn.appendChild(indicator);
                } else {
                    existingIndicator.textContent = activeFilters.length + ' filter(s) active';
                }
            } else if (existingIndicator) {
                existingIndicator.remove();
            }
        }

        function resetAllFilters() {
            document.getElementById('searchClient').value = '';
            document.getElementById('serviceFilter').value = '';
            document.getElementById('dateFromFilter').value = '';
            document.getElementById('dateToFilter').value = '';
            document.getElementById('timeSlotFilter').value = '';
            
            document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            filterTable();
        }

        function showNotification(message, type = 'success') {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            const icon = toast.querySelector('i');

            toastMessage.textContent = message;
            icon.className = type === 'success' ? 'fas fa-check-circle' : type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-info-circle';
            toast.style.backgroundColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';
            toast.style.display = 'block';
            toast.style.opacity = '1';

            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 300);
            }, 3000);
        }

        // ============================================
        // EVENT LISTENERS
        // ============================================

        document.addEventListener('DOMContentLoaded', function() {
            // Modal close events
            if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
            if (closeModalFooterBtn) closeModalFooterBtn.addEventListener('click', closeModal);
            if (modalOverlay) modalOverlay.addEventListener('click', closeModal);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });

            // Filter events
            document.getElementById('searchClient')?.addEventListener('keyup', filterTable);
            document.getElementById('serviceFilter')?.addEventListener('change', filterTable);
            document.getElementById('dateFromFilter')?.addEventListener('change', filterTable);
            document.getElementById('dateToFilter')?.addEventListener('change', filterTable);
            document.getElementById('timeSlotFilter')?.addEventListener('change', filterTable);
            document.getElementById('resetFilters')?.addEventListener('click', resetAllFilters);
            document.getElementById('refreshBtn')?.addEventListener('click', () => location.reload());

            // Quick filter buttons
            document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const type = btn.getAttribute('data-quick');
                    setQuickFilter(type);
                });
            });

            // Verify button in modal
            if (verifyBtn) {
                verifyBtn.addEventListener('click', function() {
                    if (currentClientId) {
                        verifyClientFromModal(currentClientId);
                    }
                });
            }
        });

        // Event delegation for client modal links
        document.addEventListener('click', function(e) {
            const modalButton = e.target.closest('.show-client-modal');
            if (modalButton) {
                e.preventDefault();
                const clientId = modalButton.getAttribute('data-client-id');
                if (clientId) {
                    openClientModal(clientId);
                }
            }
        });
    </script>
@endsection