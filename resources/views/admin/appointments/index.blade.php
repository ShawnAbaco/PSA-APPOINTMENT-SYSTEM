@extends('layouts.admin')

@section('content')
    <div class="appointments-container">
        <!-- Header Section -->
        <div class="appointments-header">
            <div class="header-left">
                <h1 class="page-title">Appointments Management</h1>
                <p class="page-subtitle">Manage and track all client appointments</p>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="filters-bar">
            <div class="filter-group">
                <i class="fas fa-search"></i>
                <input type="text" id="searchAppointment" placeholder="Search by appointment # or client..." class="filter-input">
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
                <i class="fas fa-calendar-alt"></i>
                <input type="date" id="dateFilter" class="filter-input">
            </div>
            <button class="btn btn-outline" id="resetFilters">
                <i class="fas fa-undo-alt"></i> Reset
            </button>
        </div>

        <!-- Appointments Card -->
        <div class="data-card">
            <div class="data-card-header">
                <div class="card-header-left">
                    <h5 class="data-card-title">All Appointments</h5>
                    <span class="appointment-count">{{ $appointments->total() ?? $appointments->count() }} total</span>
                </div>
                <div class="card-header-right">
                    <button class="icon-btn" id="refreshBtn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="data-card-body">
                <div class="table-wrapper">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Appointment #</th>
                                <th>Date</th>
                                <th>Contact Person</th>
                                <th>Clients</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                <tr class="appointment-row" data-status="{{ $appointment->status }}"
                                    data-date="{{ $appointment->appointment_date }}">
                                    <td class="appointment-id">
                                        <i class="fas fa-hashtag"></i>
                                        {{ $appointment->id }}
                                    </td>
                                    <td class="appointment-number">
                                        <i class="fas fa-ticket-alt"></i>
                                        {{ $appointment->appointment_number }}
                                    </td>
                                    <td class="appointment-date">
                                        <i class="fas fa-calendar-day"></i>
                                        {{ date('M d, Y', strtotime($appointment->appointment_date)) }}
                                        <small class="time-badge">{{ date('h:i A', strtotime($appointment->appointment_time ?? '09:00')) }}</small>
                                    </td>
                                    <td class="contact-info">
                                        <div class="contact-details">
                                            <strong>{{ $appointment->contact_name }}</strong>
                                            <small>{{ $appointment->contact_phone ?? 'No phone' }}</small>
                                        </div>
                                    </td>
                                    <td class="clients-count">
                                        <span class="client-badge">
                                            <i class="fas fa-users"></i>
                                            {{ $appointment->clients->count() }} person(s)
                                        </span>
                                    </td>
                                    <td>
                                        <select class="status-select status-badge status-{{ $appointment->status }}" 
                                            data-id="{{ $appointment->id }}" 
                                            data-status="{{ $appointment->status }}">
                                            <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="{{ route('admin.appointments.show', $appointment->id) }}" 
                                            class="action-btn view-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                            <span>View</span>
                                        </a>
                                        <button class="action-btn delete-btn" 
                                            data-id="{{ $appointment->id }}" 
                                            title="Delete Appointment">
                                            <i class="fas fa-trash-alt"></i>
                                            <span>Delete</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <h4>No Appointments Found</h4>
                                        <p>No appointments match your criteria</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($appointments->hasPages())
                    <div class="pagination-container">
                        {{ $appointments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Search functionality
        document.getElementById('searchAppointment')?.addEventListener('keyup', function() {
            filterTable();
        });

        // Status filter
        document.getElementById('statusFilter')?.addEventListener('change', function() {
            filterTable();
        });

        // Date filter
        document.getElementById('dateFilter')?.addEventListener('change', function() {
            filterTable();
        });

        // Reset filters
        document.getElementById('resetFilters')?.addEventListener('click', function() {
            document.getElementById('searchAppointment').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFilter').value = '';
            filterTable();
        });

        // Refresh button
        document.getElementById('refreshBtn')?.addEventListener('click', function() {
            location.reload();
        });

        // Status change via AJAX
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                let id = this.dataset.id;
                let status = this.value;
                
                if (confirm(`Change appointment status to ${status}?`)) {
                    fetch(`/admin/appointments/${id}/status`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ status: status })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update status badge class
                            this.className = `status-select status-badge status-${status}`;
                            // Update row data-status
                            this.closest('tr').setAttribute('data-status', status);
                            // Show success message (optional)
                            showToast('Status updated successfully', 'success');
                        } else {
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        location.reload();
                    });
                } else {
                    // Reset select to original value
                    const originalStatus = this.getAttribute('data-status');
                    this.value = originalStatus;
                }
            });
        });

        // Delete appointment
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                
                if (confirm('Are you sure you want to delete this appointment? This action cannot be undone.')) {
                    fetch(`/admin/appointments/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove row with animation
                            const row = this.closest('tr');
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                showToast('Appointment deleted successfully', 'success');
                                // Update count
                                updateAppointmentCount();
                            }, 300);
                        } else {
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        location.reload();
                    });
                }
            });
        });

        function filterTable() {
            const searchTerm = document.getElementById('searchAppointment')?.value.toLowerCase() || '';
            const statusFilter = document.getElementById('statusFilter')?.value || '';
            const dateFilter = document.getElementById('dateFilter')?.value || '';

            const rows = document.querySelectorAll('.appointment-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const appointmentNumber = row.querySelector('.appointment-number')?.textContent.toLowerCase() || '';
                const contactName = row.querySelector('.contact-details strong')?.textContent.toLowerCase() || '';
                const rowStatus = row.getAttribute('data-status') || '';
                const rowDate = row.getAttribute('data-date') || '';

                let matchesSearch = searchTerm === '' || appointmentNumber.includes(searchTerm) || contactName.includes(searchTerm);
                let matchesStatus = statusFilter === '' || rowStatus === statusFilter;
                let matchesDate = dateFilter === '' || rowDate === dateFilter;

                if (matchesSearch && matchesStatus && matchesDate) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide empty message
            const tbody = document.querySelector('.appointments-table tbody');
            const existingEmptyRow = tbody?.querySelector('.empty-state-row');

            if (visibleCount === 0 && !document.querySelector('.empty-state')) {
                if (!existingEmptyRow && tbody) {
                    const tr = document.createElement('tr');
                    tr.className = 'empty-state-row';
                    tr.innerHTML = `
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-filter"></i>
                            <h4>No matching appointments</h4>
                            <p>Try adjusting your filters</p>
                        </td>
                    `;
                    tbody.appendChild(tr);
                }
            } else if (existingEmptyRow) {
                existingEmptyRow.remove();
            }
        }

        function updateAppointmentCount() {
            const visibleRows = document.querySelectorAll('.appointment-row:not([style*="display: none"])').length;
            const countSpan = document.querySelector('.appointment-count');
            if (countSpan) {
                countSpan.textContent = `${visibleRows} total`;
            }
        }

        function showToast(message, type = 'success') {
            // Simple toast implementation
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;
            toast.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : '#3b82f6'};
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    @endpush
@endsection