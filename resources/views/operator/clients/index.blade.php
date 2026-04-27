@extends('layouts.operator')

@section('content')
    <div class="clients-container">
        <!-- Welcome Section -->
        <div class="clients-welcome-section">
            <div>
                <h1 class="clients-title">Clients Directory</h1>
                <p class="clients-subtitle">Manage and monitor all registered clients</p>
            </div>
            <div class="clients-date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="clients-filters-bar">
            <div class="clients-filter-group">
                <i class="fas fa-search"></i>
                <input type="text" id="searchClient" placeholder="Search by name, ID, or TRN..."
                    class="clients-filter-input">
            </div>
            <div class="clients-filter-group">
                <i class="fas fa-venus-mars"></i>
                <select id="sexFilter" class="clients-filter-select">
                    <option value="">All Genders</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="clients-filter-group">
                <i class="fas fa-check-circle"></i>
                <select id="verificationFilter" class="clients-filter-select">
                    <option value="">All Status</option>
                    <option value="verified">Verified</option>
                    <option value="pending">Pending Verification</option>
                </select>
            </div>
            <div class="clients-filter-group">
                <i class="fas fa-tag"></i>
                <select id="serviceFilter" class="clients-filter-select">
                    <option value="">All Services</option>
                    <option value="reg">National ID Registration</option>
                    <option value="updating">Correction/Updating</option>
                    <option value="inquiry">TRN Retrieval</option>
                </select>
            </div>
            <button class="clients-btn-reset" id="resetFilters">
                <i class="fas fa-undo-alt"></i>
            </button>
        </div>

        <!-- Table Container -->
        <div class="clients-card">
            <div class="clients-card-header">
                <div class="clients-table-title-section">
                    <h5 class="clients-card-title"> All Clients</h5>
                    <span class="clients-record-count" id="recordCount">{{ $clients->total() }} records</span>
                </div>
                <div class="clients-table-actions">
                    <a href="{{ route('operator.clients.export') }}" class="clients-btn-icon" title="Export to CSV">
                        <i class="fas fa-download"></i>
                    </a>
                    <button class="clients-btn-icon" id="refreshBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <div class="clients-card-body">
                <div class="clients-table-responsive">
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
                                <tr class="clients-table-row" data-id="{{ $client->id }}"
                                    data-verified="{{ $client->is_verified ? 'verified' : 'pending' }}"
                                    data-sex="{{ $client->sex }}" data-service="{{ $client->service }}"
                                    data-age="{{ \Carbon\Carbon::parse($client->birthdate)->age }}"
                                    onclick="openClientModal({{ $client->id }})">
                                    <td class="clients-id">{{ $client->id }}</td>
                                    <td class="clients-name">
                                        <div class="clients-name-info">
                                            <strong>{{ $client->first_name }} {{ $client->last_name }}</strong>
                                            @if ($client->middle_name)
                                                <small class="clients-text-muted">{{ $client->middle_name }}</small>
                                            @endif
                                            @if ($client->suffix)
                                                <small class="clients-text-muted">({{ $client->suffix }})</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="clients-sex-badge {{ $client->sex == 'Male' ? 'clients-male' : 'clients-female' }}">
                                            <i class="fas {{ $client->sex == 'Male' ? 'fa-mars' : 'fa-venus' }}"></i>
                                            {{ $client->sex }}
                                        </span>
                                    </td>
                                    <td>{{ date('M d, Y', strtotime($client->birthdate)) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($client->birthdate)->age }} years</td>
                                    <td>
                                        <span
                                            class="clients-service-badge">{{ $services[$client->service] ?? $client->service }}</span>
                                    </td>
                                    <td>
                                        @if ($client->appointment)
                                            <a href="{{ route('operator.appointments.show', $client->appointment->id) }}"
                                                class="clients-appointment-link" onclick="event.stopPropagation()">
                                                {{ $client->appointment->appointment_number }}
                                            </a>
                                        @else
                                            <span class="clients-text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="clients-empty-state">
                                        <i class="fas fa-users"></i>
                                        <h4>No clients found</h4>
                                        <p>No clients match your current filters</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="clients-pagination-wrapper">
                    <div class="clients-pagination-info">
                        Showing {{ $clients->firstItem() ?? 0 }} to {{ $clients->lastItem() ?? 0 }} of
                        {{ $clients->total() }} clients
                    </div>
                    <div class="clients-simple-pagination">
                        @if ($clients->onFirstPage())
                            <button class="clients-pagination-btn clients-pagination-disabled" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        @else
                            <a href="{{ $clients->previousPageUrl() }}" class="clients-pagination-btn">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        @endif

                        <span class="clients-pagination-current">
                            Page {{ $clients->currentPage() }} of {{ $clients->lastPage() }}
                        </span>

                        @if ($clients->hasMorePages())
                            <a href="{{ $clients->nextPageUrl() }}" class="clients-pagination-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="clients-pagination-btn clients-pagination-disabled" disabled>
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Details Modal -->
    <div id="clientModal" class="clients-modal">
        <div class="clients-modal-overlay" onclick="closeClientModal()"></div>
        <div class="clients-modal-container clients-modal-lg">
            <div class="clients-modal-header">
                <h3>Client Details</h3>
                <button class="clients-modal-close" onclick="closeClientModal()">&times;</button>
            </div>
            <div class="clients-modal-body" id="clientModalBody">
                <div class="clients-loading-spinner">Loading client details...</div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notificationToast" class="clients-notification-toast" style="display: none;">
        <div class="clients-toast-content">
            <i class="fas fa-check-circle"></i>
            <span id="toastMessage">Action completed successfully!</span>
        </div>
    </div>

    @push('scripts')
        <script>
            const csrfToken = '{{ csrf_token() }}';
            let filterTimeout;

            function filterTable() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    const searchTerm = document.getElementById('searchClient')?.value.toLowerCase() || '';
                    const sexFilter = document.getElementById('sexFilter')?.value || '';
                    const verificationFilter = document.getElementById('verificationFilter')?.value || '';
                    const serviceFilter = document.getElementById('serviceFilter')?.value || '';

                    const rows = document.querySelectorAll('.clients-table-row');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const clientId = row.querySelector('.clients-id')?.textContent || '';
                        const clientName = row.querySelector('.clients-name strong')?.textContent
                            .toLowerCase() || '';
                        const sex = row.getAttribute('data-sex') || '';
                        const verified = row.getAttribute('data-verified') || '';
                        const service = row.getAttribute('data-service') || '';

                        const matchesSearch = !searchTerm || clientId.includes(searchTerm) || clientName
                            .includes(searchTerm);
                        const matchesSex = !sexFilter || sex === sexFilter;
                        const matchesVerification = !verificationFilter || verified === verificationFilter;
                        const matchesService = !serviceFilter || service === serviceFilter;

                        const shouldShow = matchesSearch && matchesSex && matchesVerification && matchesService;
                        row.style.display = shouldShow ? '' : 'none';
                        if (shouldShow) visibleCount++;
                    });

                    const recordCountSpan = document.getElementById('recordCount');
                    if (recordCountSpan) {
                        if (visibleCount !== {{ $clients->total() }}) {
                            recordCountSpan.textContent = visibleCount + ' records (filtered)';
                        } else {
                            recordCountSpan.textContent = '{{ $clients->total() }} records';
                        }
                    }
                }, 300);
            }

            async function openClientModal(id) {
                openModal('clientModal');
                const modalBody = document.getElementById('clientModalBody');
                modalBody.innerHTML = '<div class="clients-loading-spinner">Loading client details...</div>';

                try {
                    const response = await fetch(`/operator/clients/${id}`);
                    const html = await response.text();

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const content = doc.querySelector('.container-fluid') || doc.body;

                    modalBody.innerHTML = `
                <div class="clients-details-content">
                    ${content.innerHTML}
                </div>
            `;

                    const scripts = modalBody.querySelectorAll('script');
                    scripts.forEach(script => {
                        const newScript = document.createElement('script');
                        newScript.textContent = script.textContent;
                        document.body.appendChild(newScript);
                    });
                } catch (error) {
                    modalBody.innerHTML =
                        '<div class="clients-error-message">Failed to load client details. Please try again.</div>';
                }
            }

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

            function openModal(modalId) {
                document.getElementById(modalId).classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeModal(modalId) {
                document.getElementById(modalId).classList.remove('active');
                document.body.style.overflow = '';
            }

            function closeClientModal() {
                closeModal('clientModal');
            }

            document.getElementById('searchClient')?.addEventListener('keyup', () => filterTable());
            document.getElementById('sexFilter')?.addEventListener('change', () => filterTable());
            document.getElementById('verificationFilter')?.addEventListener('change', () => filterTable());
            document.getElementById('serviceFilter')?.addEventListener('change', () => filterTable());

            document.getElementById('resetFilters')?.addEventListener('click', () => {
                document.getElementById('searchClient').value = '';
                document.getElementById('sexFilter').value = '';
                document.getElementById('verificationFilter').value = '';
                document.getElementById('serviceFilter').value = '';
                filterTable();
            });

            document.getElementById('refreshBtn')?.addEventListener('click', () => location.reload());

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.clients-modal.active').forEach(modal => {
                        modal.classList.remove('active');
                    });
                    document.body.style.overflow = '';
                }
            });
        </script>
    @endpush
@endsection
