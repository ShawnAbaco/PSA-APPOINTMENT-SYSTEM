@extends('layouts.operator')

@section('content')
    <div class="appointment-show container-fluid">
        <div class="appointment-show__header d-flex justify-content-between mb-4">
            <h1 class="appointment-show__title h3">Appointment Details</h1>
        </div>

        <div class="appointment-show__grid row">
            <div class="appointment-show__section col-md-6">
                <div class="info-card card mb-4">
                    <div class="info-card__header card-header">
                        <h5>Appointment Info</h5>
                    </div>
                    <div class="info-card__body card-body">
                        <p><strong>Appointment #:</strong> {{ $appointment->appointment_number }}</p>
                        <p><strong>Date:</strong> {{ date('F d, Y', strtotime($appointment->appointment_date)) }}</p>
                        <p><strong>Time Slot:</strong> {{ $appointment->timeSlot->label ?? 'N/A' }}</p>
                        <p><strong>Type:</strong> {{ ucfirst($appointment->type) }}</p>
                        <p><strong>Status:</strong> <span
                                class="status-badge status-badge--{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                        </p>
                        <p><strong>Reference Code:</strong> <code>{{ $appointment->reference_code }}</code></p>
                        @if ($appointment->confirmed_at)
                            <p><strong>Confirmed At:</strong>
                                {{ date('F d, Y h:i A', strtotime($appointment->confirmed_at)) }}</p>
                        @endif
                        @if ($appointment->completed_at)
                            <p><strong>Completed At:</strong>
                                {{ date('F d, Y h:i A', strtotime($appointment->completed_at)) }}</p>
                        @endif
                        @if ($appointment->cancelled_at)
                            <p><strong>Cancelled At:</strong>
                                {{ date('F d, Y h:i A', strtotime($appointment->cancelled_at)) }}</p>
                            <p><strong>Cancellation Reason:</strong> {{ $appointment->cancellation_reason ?? 'N/A' }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="appointment-show__section col-md-6">
                <div class="contact-card card mb-4">
                    <div class="contact-card__header card-header">
                        <h5>Contact Info</h5>
                    </div>
                    <div class="contact-card__body card-body">
                        <p><strong>Name:</strong> {{ $appointment->contact_name }}</p>
                        <p><strong>Email:</strong> {{ $appointment->contact_email ?? 'N/A' }}</p>
                        <p><strong>Mobile:</strong> {{ $appointment->contact_mobile }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="clients-card card mb-4">
            <div class="clients-card__header card-header">
                <h5>Applicants</h5>
            </div>
            <div class="clients-card__body card-body">
                <div class="table-responsive">
                    <table class="clients-table table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Applicant Number</th>
                                <th>Full Name</th>
                                <th>Birthdate</th>
                                <th>Service</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appointment->clients as $index => $client)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $client->client_number ?? 'N/A' }}</td>
                                    <td>{{ $client->first_name }} {{ $client->middle_name }} {{ $client->last_name }}
                                        {{ $client->suffix }}</td>
                                    <td>{{ date('M d, Y', strtotime($client->birthdate)) }}</td>
                                    <td>
                                        @php
                                            $serviceNames = [
                                                'reg' => 'Registration',
                                                'updating' => 'Correction/Updating',
                                                'inquiry' => 'Status Inquiry',
                                            ];
                                        @endphp
                                        {{ $serviceNames[$client->service] ?? $client->service }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- <div class="action-buttons-group">
            @if ($appointment->status == 'pending')
                <button class="btn btn--success btn-action confirm-btn"
                    onclick="confirmAppointment({{ $appointment->id }})" title="Confirm">
                    <i class="fas fa-check-circle"></i> Confirm
                </button>
            @endif
        </div> --}}

    </div>

    <!-- Cancel Reason Modal -->
    <div class="modal modal--cancel-reason" id="cancelReasonModal">
        <div class="modal__overlay" onclick="closeCancelModal()"></div>
        <div class="modal__dialog">
            <div class="modal__content">
                <div class="modal__header">
                    <h5>Cancellation Reason</h5>
                    <button class="modal__close" onclick="closeCancelModal()">&times;</button>
                </div>
                <div class="modal__body">
                    <p>Please provide a reason for cancelling this appointment:</p>
                    <textarea id="cancellationReason" class="form-control" rows="3" placeholder="Enter cancellation reason..."></textarea>
                </div>
                <div class="modal__footer">
                    <button class="btn btn--secondary" onclick="closeCancelModal()">Close</button>
                    <button class="btn btn--warning" id="confirmCancelBtn">Confirm Cancellation</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal modal--delete-confirm" id="deleteConfirmModal">
        <div class="modal__overlay" onclick="closeDeleteModal()"></div>
        <div class="modal__dialog">
            <div class="modal__content">
                <div class="modal__header">
                    <h5>Delete Appointment</h5>
                    <button class="modal__close" onclick="closeDeleteModal()">&times;</button>
                </div>
                <div class="modal__body">
                    <div class="alert alert--danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to permanently delete this appointment?</p>
                    <p class="text-muted">Appointment #: <strong>{{ $appointment->appointment_number }}</strong></p>
                    <p class="text-muted">Contact: <strong>{{ $appointment->contact_name }}</strong></p>
                </div>
                <div class="modal__footer">
                    <button class="btn btn--secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button class="btn btn--danger" id="confirmDeleteBtn">Yes, Delete Permanently</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // CSRF Token setup
        const csrfToken = '{{ csrf_token() }}';

        // Confirm Appointment with SweetAlert
        async function confirmAppointment(id) {
            const result = await Swal.fire({
                title: 'Confirm Appointment?',
                text: "This will change the status from Pending to Confirmed.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, confirm it!',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(`/operator/appointments/${id}/confirm`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({})
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Confirmed!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Refresh the page after SweetAlert closes
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to confirm appointment.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while confirming the appointment.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        }

        // Cancel Appointment - Show Modal
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('cancellationReason').value = '';
                document.getElementById('cancelReasonModal').classList.add('show');
            });
        });

        // Confirm Cancel with reason
        document.getElementById('confirmCancelBtn').addEventListener('click', async function() {
            const reason = document.getElementById('cancellationReason').value;
            if (!reason.trim()) {
                await Swal.fire({
                    title: 'Reason Required',
                    text: 'Please provide a cancellation reason.',
                    icon: 'warning',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            Swal.fire({
                title: 'Processing...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(`/operator/appointments/{{ $appointment->id }}/cancel`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        reason: reason
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({
                        title: 'Cancelled!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Refresh the page after SweetAlert closes
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to cancel appointment.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while cancelling the appointment.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
            closeCancelModal();
        });

        // Delete Appointment - Show Modal
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('deleteConfirmModal').classList.add('show');
            });
        });

        // Confirm Delete
        document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(`/operator/appointments/{{ $appointment->id }}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect to index page after delete
                        window.location.href = '/operator/appointments';
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to delete appointment.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while deleting the appointment.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
            closeDeleteModal();
        });

        // Close modals
        function closeCancelModal() {
            document.getElementById('cancelReasonModal').classList.remove('show');
        }

        function closeDeleteModal() {
            document.getElementById('deleteConfirmModal').classList.remove('show');
        }

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
    </script>

    <style>
        /* Button Styles */
        .btn-action {
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .action-buttons-group {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* Status Badge Styles */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-badge--pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-badge--confirmed {
            background: #d1fae5;
            color: #059669;
        }

        .status-badge--cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-badge--completed {
            background: #dbeafe;
            color: #2563eb;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge--success {
            background: #d1fae5;
            color: #059669;
        }

        .badge--secondary {
            background: #e5e7eb;
            color: #4b5563;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
        }

        .modal.show {
            display: block;
        }

        .modal__overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal__dialog {
            position: relative;
            top: 50%;
            transform: translateY(-50%);
            max-width: 500px;
            margin: 0 auto;
            z-index: 1001;
        }

        .modal__content {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal__header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal__header h5 {
            margin: 0;
            font-size: 1.1rem;
        }

        .modal__close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }

        .modal__close:hover {
            color: #343a40;
        }

        .modal__body {
            padding: 20px;
        }

        .modal__footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Alert Styles */
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert--danger {
            background: #fee;
            border-left: 3px solid #dc2626;
            color: #991b1b;
        }

        /* Form Control */
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .text-muted {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
@endsection
