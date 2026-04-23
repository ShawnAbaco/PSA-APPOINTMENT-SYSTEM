@extends('layouts.admin')

@section('content')
    <div class="calendar-container">
        <!-- Calendar Header -->
        <div class="calendar-welcome-section">
            <div>
                <h1 class="calendar-title">Appointment Calendar</h1>
                <p class="calendar-subtitle">View and manage all appointments in calendar format</p>
            </div>
            <div class="calendar-date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Calendar Card -->
        <div class="calendar-card">
            <div class="calendar-card-header">
                <h5 class="calendar-card-title"><i class="fas fa-calendar-alt"></i> Calendar Overview</h5>
                <div class="calendar-view-options">
                    <button class="calendar-view-btn" onclick="switchCalendarView('dayGridMonth')">Month</button>
                    <button class="calendar-view-btn" onclick="switchCalendarView('timeGridWeek')">Week</button>
                    <button class="calendar-view-btn" onclick="switchCalendarView('timeGridDay')">Day</button>
                </div>
            </div>
            <div class="calendar-card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Appointment Details Modal -->
    <div id="appointmentModal" class="calendar-modal" style="display: none;">
        <div class="calendar-modal-content">
            <div class="calendar-modal-header">
                <h3 class="calendar-modal-title">
                    <i class="fas fa-calendar-check"></i>
                    Appointment Details
                </h3>
                <span class="calendar-modal-close" onclick="closeModal()">&times;</span>
            </div>
            <div class="calendar-modal-body" id="modalBody">
                <!-- Content will be populated dynamically -->
            </div>
        </div>
    </div>
@endsection

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
    let calendar;

    function switchCalendarView(view) {
        if (calendar) {
            calendar.changeView(view);
        }
    }

    function openModal(appointmentId) {
        fetch(`/admin/appointments/${appointmentId}/json`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayModalContent(data.appointment);
                }
            })
            .catch(error => {
                console.error('Error fetching appointment:', error);
                showErrorModal();
            });
    }

    function displayModalContent(appointment) {
        const modalBody = document.getElementById('modalBody');

        // Determine status class
        let statusClass = '';
        switch (appointment.status) {
            case 'confirmed':
                statusClass = 'status-confirmed';
                break;
            case 'pending':
                statusClass = 'status-pending';
                break;
            case 'completed':
                statusClass = 'status-completed';
                break;
            case 'cancelled':
                statusClass = 'status-cancelled';
                break;
            case 'no_show':
                statusClass = 'status-no_show';
                break;
            default:
                statusClass = 'status-pending';
        }

        let clientsHtml = '';
        if (appointment.clients && appointment.clients.length > 0) {
            appointment.clients.forEach(client => {
                let serviceClass = '';
                switch (client.service) {
                    case 'reg':
                        serviceClass = 'service-reg';
                        break;
                    case 'updating':
                        serviceClass = 'service-updating';
                        break;
                    case 'inquiry':
                        serviceClass = 'service-inquiry';
                        break;
                }

                let serviceName = client.service === 'reg' ? 'Registration' :
                    client.service === 'updating' ? 'Updating' : 'Inquiry';

                clientsHtml += `
                    <div class="client-card">
                        <div class="client-header">
                            <span class="client-name">${escapeHtml(client.first_name)} ${escapeHtml(client.middle_name || '')} ${escapeHtml(client.last_name)} ${escapeHtml(client.suffix || '')}</span>
                            <span class="service-badge ${serviceClass}">${serviceName}</span>
                        </div>
                        <div class="client-details">
                            <div class="client-detail-item">
                                <i class="fas fa-venus-mars"></i>
                                <span>${escapeHtml(client.sex)}</span>
                            </div>
                            <div class="client-detail-item">
                                <i class="fas fa-birthday-cake"></i>
                                <span>${formatDate(client.birthdate)}</span>
                            </div>
                            ${client.trn_number ? `
                            <div class="client-detail-item">
                                <i class="fas fa-id-card"></i>
                                <span>TRN: ${escapeHtml(client.trn_number)}</span>
                            </div>
                            ` : ''}
                            ${client.psa_reference_number ? `
                            <div class="client-detail-item">
                                <i class="fas fa-file-alt"></i>
                                <span>PSA: ${escapeHtml(client.psa_reference_number)}</span>
                            </div>
                            ` : ''}
                            <div class="client-detail-item">
                                <i class="fas ${client.is_verified ? 'fa-check-circle' : 'fa-clock'}"></i>
                                <span>${client.is_verified ? 'Verified' : 'Pending Verification'}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            clientsHtml =
                '<p style="color: var(--gray-500); text-align: center;">No clients found for this appointment.</p>';
        }

        // Format time slot for display (convert to 12-hour format)
        let timeSlotDisplay = 'N/A';
        if (appointment.time_slot) {
            timeSlotDisplay = formatTimeRange12Hour(appointment.time_slot.start_time, appointment.time_slot.end_time);
        }

        modalBody.innerHTML = `
            <div class="appointment-info-card">
                <div class="appointment-info-header">
                    <span class="appointment-number">
                        <i class="fas fa-ticket-alt"></i> ${escapeHtml(appointment.appointment_number)}
                    </span>
                    <span class="status-badge ${statusClass}">${escapeHtml(appointment.status.toUpperCase())}</span>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-day"></i> Appointment Date</span>
                        <span class="info-value">${formatDate(appointment.appointment_date)}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-clock"></i> Time Slot</span>
                        <span class="info-value">${timeSlotDisplay}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user"></i> Contact Person</span>
                        <span class="info-value">${escapeHtml(appointment.contact_name)}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-phone"></i> Contact Number</span>
                        <span class="info-value">${escapeHtml(appointment.contact_mobile)}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                        <span class="info-value">${escapeHtml(appointment.contact_email || 'N/A')}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-hashtag"></i> Reference Code</span>
                        <span class="info-value">${escapeHtml(appointment.reference_code)}</span>
                    </div>
                    ${appointment.user_city ? `
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-map-marker-alt"></i> Location</span>
                        <span class="info-value">${escapeHtml(appointment.user_city)}${appointment.user_address ? ', ' + escapeHtml(appointment.user_address) : ''}</span>
                    </div>
                    ` : ''}
                    ${appointment.notes ? `
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-sticky-note"></i> Notes</span>
                        <span class="info-value">${escapeHtml(appointment.notes)}</span>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            <div class="clients-section">
                <div class="clients-title">
                    <i class="fas fa-users"></i>
                    Clients (${appointment.clients ? appointment.clients.length : 0})
                </div>
                <div class="clients-grid">
                    ${clientsHtml}
                </div>
            </div>
        
        `;

        document.getElementById('appointmentModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function showErrorModal() {
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--danger); margin-bottom: 16px;"></i>
                <h3 style="color: var(--gray-800); margin-bottom: 8px;">Error Loading Appointment</h3>
                <p style="color: var(--gray-500);">Unable to load appointment details. Please try again.</p>
                <button onclick="closeModal()" class="btn-primary" style="margin-top: 20px;">Close</button>
            </div>
        `;
        document.getElementById('appointmentModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('appointmentModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function formatTimeRange12Hour(startTime, endTime) {
        if (!startTime || !endTime) return 'N/A';

        const formatTime = (timeStr) => {
            const [hours, minutes] = timeStr.split(':');
            let hour = parseInt(hours);
            const minute = minutes;
            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12;
            hour = hour === 0 ? 12 : hour;
            return `${hour}:${minute} ${ampm}`;
        };

        return `${formatTime(startTime)} - ${formatTime(endTime)}`;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('appointmentModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            events: [
                @foreach ($appointments as $appointment)
                    {
                        id: '{{ $appointment->id }}',
                        title: '{{ $appointment->timeSlot ? \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('g:i') . ' - ' . \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('g:i A') : 'No Time Slot' }}',
                        start: '{{ $appointment->appointment_date }}',
                        clientCount: {{ $appointment->clients->count() }},
                        color: '{{ $appointment->status === 'confirmed' ? '#10b981' : ($appointment->status === 'completed' ? '#3b82f6' : ($appointment->status === 'cancelled' ? '#ef4444' : '#f59e0b')) }}',
                        backgroundColor: '{{ $appointment->status === 'confirmed' ? '#10b981' : ($appointment->status === 'completed' ? '#3b82f6' : ($appointment->status === 'cancelled' ? '#ef4444' : '#f59e0b')) }}',
                        borderColor: 'transparent',
                        textColor: '#ffffff',
                        extendedProps: {
                            appointmentId: {{ $appointment->id }}
                        }
                    },
                @endforeach
            ],
            eventDidMount: function(info) {
                // Add tooltip with client count
                const clientCount = info.event.extendedProps.clientCount || info.event
                    .clientCount || 0;
                info.el.setAttribute('title', `${info.event.title} - ${clientCount} client(s)`);
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                const appointmentId = info.event.extendedProps.appointmentId;
                if (appointmentId) {
                    openModal(appointmentId);
                }
            }
        });
        calendar.render();
    });
</script>
