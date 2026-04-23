@extends('layouts.staff')


@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-4">
            <h1 class="h3">Appointment Details</h1>
           
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Appointment Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Appointment #:</strong> {{ $appointment->appointment_number }}</p>
                        <p><strong>Date:</strong> {{ date('F d, Y', strtotime($appointment->appointment_date)) }}</p>
                        <p><strong>Time Slot:</strong> {{ $appointment->timeSlot->label ?? 'N/A' }}</p>
                        <p><strong>Type:</strong> {{ ucfirst($appointment->type) }}</p>
                        <p><strong>Status:</strong> <span
                                class="status-badge status-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                        </p>
                        <p><strong>Reference Code:</strong> <code>{{ $appointment->reference_code }}</code></p>
                        @if($appointment->confirmed_at)
                            <p><strong>Confirmed At:</strong> {{ date('F d, Y h:i A', strtotime($appointment->confirmed_at)) }}</p>
                        @endif
                        @if($appointment->completed_at)
                            <p><strong>Completed At:</strong> {{ date('F d, Y h:i A', strtotime($appointment->completed_at)) }}</p>
                        @endif
                        @if($appointment->cancelled_at)
                            <p><strong>Cancelled At:</strong> {{ date('F d, Y h:i A', strtotime($appointment->cancelled_at)) }}</p>
                            <p><strong>Cancellation Reason:</strong> {{ $appointment->cancellation_reason ?? 'N/A' }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Contact Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $appointment->contact_name }}</p>
                        <p><strong>Email:</strong> {{ $appointment->contact_email ?? 'N/A' }}</p>
                        <p><strong>Mobile:</strong> {{ $appointment->contact_mobile }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Clients</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Client Number</th>
                                <th>Full Name</th>
                                <th>Sex</th>
                                <th>Birthdate</th>
                                <th>Service</th>
                                <th>TRN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointment->clients as $index => $client)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $client->client_number ?? 'N/A' }}</td>
                                    <td>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}
                                        {{ $client->suffix }}</td>
                                    <td>{{ $client->sex }}</td>
                                    <td>{{ date('M d, Y', strtotime($client->birthdate)) }}</td>
                                    <td>
                                        @php
                                            $serviceNames = [
                                                'reg' => 'Registration',
                                                'updating' => 'Correction/Updating',
                                                'inquiry' => 'Status Inquiry'
                                            ];
                                        @endphp
                                        {{ $serviceNames[$client->service] ?? $client->service }}
                                    </td>
                                    <td>
                                        @if($client->service === 'inquiry')
                                            @if($client->has_trn)
                                                <span class="badge badge-success">Has TRN</span>
                                                <small class="d-block text-muted">{{ substr($client->trn_number, 0, 10) }}...</small>
                                            @else
                                                <span class="badge badge-secondary">No TRN</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
            <div class="action-buttons-group">
                @if ($appointment->status == 'pending')
                    <button class="btn btn-success btn-action confirm-btn" data-id="{{ $appointment->id }}">
                        <i class="fas fa-check-circle"></i> Confirm Appointment
                    </button>
                @endif
                
                @if ($appointment->status == 'confirmed')
                    <button class="btn btn-info btn-action complete-btn" data-id="{{ $appointment->id }}">
                        <i class="fas fa-check-double"></i> Mark Complete
                    </button>
                @endif
                
                @if (in_array($appointment->status, ['pending', 'confirmed']))
                    <button class="btn btn-warning btn-action cancel-btn" data-id="{{ $appointment->id }}">
                        <i class="fas fa-times-circle"></i> Cancel Appointment
                    </button>
                @endif
                
                @if (in_array($appointment->status, ['cancelled', 'completed']))
                    <button class="btn btn-danger btn-action delete-btn" data-id="{{ $appointment->id }}">
                        <i class="fas fa-trash-alt"></i> Delete Appointment
                    </button>
                @endif
           
        </div>
    </div>

    <!-- Cancel Reason Modal -->
    <div class="modal" id="cancelReasonModal">
        <div class="modal-overlay" onclick="closeCancelModal()"></div>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Cancellation Reason</h5>
                    <button class="modal-close" onclick="closeCancelModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for cancelling this appointment:</p>
                    <textarea id="cancellationReason" class="form-control" rows="3" placeholder="Enter cancellation reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeCancelModal()">Close</button>
                    <button class="btn btn-warning" id="confirmCancelBtn">Confirm Cancellation</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteConfirmModal">
        <div class="modal-overlay" onclick="closeDeleteModal()"></div>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Delete Appointment</h5>
                    <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to permanently delete this appointment?</p>
                    <p class="text-muted">Appointment #: <strong>{{ $appointment->appointment_number }}</strong></p>
                    <p class="text-muted">Client: <strong>{{ $appointment->contact_name }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete Permanently</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notificationToast" class="toast" style="display: none;">
        <div class="toast-header">
            <i class="fas fa-info-circle"></i>
            <strong>Notification</strong>
            <button class="btn-close" onclick="hideToast()">&times;</button>
        </div>
        <div class="toast-body" id="toastMessage">
            Action completed successfully!
        </div>
    </div>

    @push('scripts')
    <script>
        // Store the current appointment ID
        let currentAppointmentId = {{ $appointment->id }};
        let pendingAction = null;

        // Confirm Appointment
        document.querySelectorAll('.confirm-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Confirm this appointment?')) {
                    performAction('confirm');
                }
            });
        });

        // Complete Appointment
        document.querySelectorAll('.complete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Mark this appointment as completed?')) {
                    performAction('complete');
                }
            });
        });

        // Cancel Appointment - Show Modal
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('cancellationReason').value = '';
                document.getElementById('cancelReasonModal').classList.add('show');
            });
        });

        // Confirm Cancel with reason
        document.getElementById('confirmCancelBtn').addEventListener('click', function() {
            const reason = document.getElementById('cancellationReason').value;
            performAction('cancel', reason);
            closeCancelModal();
        });

        // Delete Appointment - Show Modal
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('deleteConfirmModal').classList.add('show');
            });
        });

        // Confirm Delete
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            performAction('delete');
            closeDeleteModal();
        });

        // Print button
        document.querySelector('.print-btn')?.addEventListener('click', function() {
            window.print();
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const cancelModal = document.getElementById('cancelReasonModal');
            const deleteModal = document.getElementById('deleteConfirmModal');
            if (event.target === cancelModal) {
                closeCancelModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        };

        function closeCancelModal() {
            document.getElementById('cancelReasonModal').classList.remove('show');
        }

        function closeDeleteModal() {
            document.getElementById('deleteConfirmModal').classList.remove('show');
        }

        function hideToast() {
            const toast = document.getElementById('notificationToast');
            toast.style.display = 'none';
        }

        function showNotification(message, isError = false) {
            const toast = document.getElementById('notificationToast');
            const toastMessage = document.getElementById('toastMessage');
            const icon = toast.querySelector('.toast-header i');
            
            toastMessage.textContent = message;
            
            if (isError) {
                icon.className = 'fas fa-exclamation-circle';
                icon.style.color = '#dc2626';
                toast.style.borderLeft = '3px solid #dc2626';
            } else {
                icon.className = 'fas fa-check-circle';
                icon.style.color = '#10b981';
                toast.style.borderLeft = '3px solid #10b981';
            }
            
            toast.style.display = 'block';
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.style.display = 'none';
                    toast.style.opacity = '1';
                }, 300);
            }, 3000);
        }

        async function performAction(action, reason = null) {
            // Disable buttons to prevent double submission
            const buttons = document.querySelectorAll('.btn-action');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.6';
            });
            
            let url = '';
            let method = 'PUT';
            let body = { _token: '{{ csrf_token() }}' };
            
            switch(action) {
                case 'confirm':
                    url = `/staff/appointments/${currentAppointmentId}/confirm`;
                    break;
                case 'complete':
                    url = `/staff/appointments/${currentAppointmentId}/complete`;
                    break;
                case 'cancel':
                    url = `/staff/appointments/${currentAppointmentId}/cancel`;
                    body.reason = reason || 'Cancelled by staff';
                    break;
                case 'delete':
                    url = `/staff/appointments/${currentAppointmentId}`;
                    method = 'DELETE';
                    break;
            }
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(body)
                });
                
                const data = await response.json();
                
                if (response.ok && (data.success || response.status === 200)) {
                    showNotification(getSuccessMessage(action));
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || getErrorMessage(action), true);
                    // Re-enable buttons on error
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', true);
                // Re-enable buttons on error
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                });
            }
        }

        function getSuccessMessage(action) {
            const messages = {
                'confirm': 'Appointment confirmed successfully!',
                'complete': 'Appointment marked as completed!',
                'cancel': 'Appointment cancelled successfully!',
                'delete': 'Appointment deleted successfully!'
            };
            return messages[action] || 'Action completed successfully!';
        }

        function getErrorMessage(action) {
            const messages = {
                'confirm': 'Failed to confirm appointment.',
                'complete': 'Failed to mark appointment as completed.',
                'cancel': 'Failed to cancel appointment.',
                'delete': 'Failed to delete appointment.'
            };
            return messages[action] || 'Action failed. Please try again.';
        }
    </script>
    @endpush
@endsection