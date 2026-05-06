@extends('layouts.operator')

@section('content')
    <div class="clients-container">
        <!-- Header Section -->
        <div class="clients-header">
            <div>
                <h1 class="page-title">Clients Directory</h1>
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
            <button class="btn-reset" id="resetFilters">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title-section">
                    <h3>All Clients</h3>
                    <span class="record-count" id="recordCount">{{ $clients->total() }} records</span>
                </div>
                <div class="table-actions">
                    <a href="{{ route('operator.clients.export') }}" class="btn-icon" title="Export to CSV">
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
                            <th>Appointment</th>
                        </tr>
                    </thead>
                    <tbody id="clientsTableBody">
                        @forelse($clients as $client)
                            <tr class="client-row" data-id="{{ $client->id }}" data-client-id="{{ $client->id }}"
                                data-verified="{{ $client->is_verified ? 'verified' : 'pending' }}"
                                data-sex="{{ $client->sex }}" data-service="{{ $client->service }}"
                                data-age="{{ \Carbon\Carbon::parse($client->birthdate)->age }}">
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
                                    <span
                                        class="service-badge">{{ $services[$client->service] ?? $client->service }}</span>
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
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h4>No clients found</h4>
                                    <p>No clients match your current filters</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing {{ $clients->firstItem() ?? 0 }} to {{ $clients->lastItem() ?? 0 }} of
                    {{ $clients->total() }} clients
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
                    Client Details
                </h3>
                <button class="modal-close" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body" id="clientModalBody">
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Loading client details...</p>
                </div>
            </div>
            <div class="modal-footer"
                style="padding: 16px 24px; background: #f8f9fa; border-top: 1px solid #e9ecef; display: flex; justify-content: flex-end; gap: 12px;">
                <button class="modern-btn" id="closeModalFooterBtn" style="background: #6c757d;">Close</button>
                <button class="modern-btn" id="verifyClientModalBtn"
                    style="display: none; background: linear-gradient(135deg, #28a745, #20c997);">
                    <i class="fas fa-check-circle"></i> Verify Client
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

            // Show modal with loading state
            modal.classList.add('active');
            modalBody.innerHTML = `
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Loading client details...</p>
                </div>
            `;
            verifyBtn.style.display = 'none';

            // Fetch client details
            fetch(`/operator/clients/${clientId}/modal`, {
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

                    // Check if client is verified to show/hide verify button
                    const isVerified = modalBody.innerHTML.includes('status-badge-modal verified') ||
                        !modalBody.innerHTML.includes('Pending Verification');
                    if (!isVerified && modalBody.innerHTML.includes('Pending Verification')) {
                        verifyBtn.style.display = 'inline-flex';
                    } else {
                        verifyBtn.style.display = 'none';
                    }

                    // Attach event listeners to dynamic content
                    attachModalEventListeners();
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 12px; display: block;"></i>
                        <strong>Failed to load client details.</strong><br>
                        Please try again.
                    </div>
                `;
                });
        }

        // Close modal function
        function closeModal() {
            modal.classList.remove('active');
            currentClientId = null;
        }

        // Attach event listeners for modal content
        function attachModalEventListeners() {
            // Update reference number button
            const updateRefBtn = document.querySelector('.update-reference-btn');
            if (updateRefBtn) {
                // Remove old listener to avoid duplicates
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

        // Verify client from modal
        function verifyClientFromModal(clientId) {
            fetch(`/operator/clients/${clientId}/verify`, {
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
                        showNotification('Client verified successfully!');
                        // Reload modal content
                        if (currentClientId) {
                            openClientModal(currentClientId);
                        }
                        // Reload the page to reflect changes in the table
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showNotification(data.message || 'Failed to verify client', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while verifying client', 'error');
                });
        }

        // Update reference number
        function updateReferenceNumber(clientId, refNumber) {
            fetch(`/operator/clients/${clientId}/reference`, {
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
                        // Reload modal content
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

        // Filter table function
        function filterTable() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                const searchTerm = document.getElementById('searchClient')?.value.toLowerCase() || '';
                const serviceFilter = document.getElementById('serviceFilter')?.value || '';

                const rows = document.querySelectorAll('.client-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const clientId = row.querySelector('.client-id')?.textContent || '';
                    const clientName = row.querySelector('.client-name strong')?.textContent
                        .toLowerCase() || '';
                    const service = row.getAttribute('data-service') || '';

                    const matchesSearch = !searchTerm || clientId.includes(searchTerm) || clientName
                        .includes(searchTerm);
                    const matchesService = !serviceFilter || service === serviceFilter;

                    const shouldShow = matchesSearch && matchesService;
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
            }, 300);
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            const icon = toast.querySelector('i');

            toastMessage.textContent = message;
            icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            toast.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
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

            // Escape key to close modal
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });

            // Filter events
            const searchInput = document.getElementById('searchClient');
            const serviceFilter = document.getElementById('serviceFilter');
            const resetBtn = document.getElementById('resetFilters');
            const refreshBtn = document.getElementById('refreshBtn');

            if (searchInput) searchInput.addEventListener('keyup', filterTable);
            if (serviceFilter) serviceFilter.addEventListener('change', filterTable);

            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    if (searchInput) searchInput.value = '';
                    if (serviceFilter) serviceFilter.value = '';
                    filterTable();
                });
            }

            if (refreshBtn) {
                refreshBtn.addEventListener('click', () => location.reload());
            }

            // Verify button in modal
            if (verifyBtn) {
                verifyBtn.addEventListener('click', function() {
                    if (currentClientId) {
                        verifyClientFromModal(currentClientId);
                    }
                });
            }
        });

        // Event delegation for client modal links (works with filtered/paginated content)
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
