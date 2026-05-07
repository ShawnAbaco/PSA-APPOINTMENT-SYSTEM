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
                    
                </div>
            </div>
            <div class="calendar-card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Appointment Details Modal (Main) -->
    <div id="appointmentModal" class="calendar-modal" style="display: none;">
        <div class="calendar-modal-content">
            <div class="calendar-modal-header">
                <h3 class="calendar-modal-title">
                    <i class="fas fa-calendar-check"></i>
                    Appointment Details
                </h3>
                <span class="calendar-modal-close" onclick="closeModal()">&times;</span>
            </div>
            <div class="calendar-modal-body" id="modalBody"></div>
        </div>
    </div>

    <!-- Day Details Modal -->
    <div id="dayDetailsModal" class="calendar-modal" style="display: none;">
        <div class="calendar-modal-content" style="max-width: 700px;">
            <div class="calendar-modal-header">
                <h3 class="calendar-modal-title">
                    <i class="fas fa-calendar-day"></i>
                    <span id="dayModalTitle">Appointments for</span>
                </h3>
                <span class="calendar-modal-close" onclick="closeDayModal()">&times;</span>
            </div>
            <div class="calendar-modal-body">
                <div class="service-tabs">
                    <button class="service-tab active" data-service="reg">Registration</button>
                    <button class="service-tab" data-service="updating">Correction/Updating</button>
                    <button class="service-tab" data-service="inquiry">Status Inquiry</button>
                </div>
                
                <div id="dayTimeSlotsContainer">
                    <div class="loading-spinner">Loading time slots...</div>
                </div>
                
                <div id="dayAppointmentsContainer" style="display: none;">
                    <h4>Appointments for <span id="selectedSlotLabel"></span></h4>
                    <div id="dayAppointmentsList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Appointment Details Modal (New) -->
    <div id="fullAppointmentModal" class="calendar-modal" style="display: none;">
        <div class="calendar-modal-content" style="max-width: 800px;">
            <div class="calendar-modal-header">
                <h3 class="calendar-modal-title">
                    <i class="fas fa-info-circle"></i>
                    Complete Appointment Information
                </h3>
                <span class="calendar-modal-close" onclick="closeFullAppointmentModal()">&times;</span>
            </div>
            <div class="calendar-modal-body" id="fullAppointmentModalBody"></div>
        </div>
    </div>
@endsection

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>



<script>
    let calendar;
    let currentSelectedDate = null;
    let currentSelectedService = 'reg';
    let currentSelectedSlotId = null;
    let slotData = {};

    // Get total capacity - you can adjust this based on your business logic

    function switchCalendarView(view) {
        if (calendar) calendar.changeView(view);
    }

    // New function to open full appointment details modal
    function openFullAppointmentModal(appointmentId) {
        fetch(`/admin/calendar/${appointmentId}/full-details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayFullAppointmentModalContent(data.appointment, data.clients);
                } else {
                    showErrorModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal();
            });
    }

    function displayFullAppointmentModalContent(appointment, clients) {
        const modalBody = document.getElementById('fullAppointmentModalBody');
        
        let statusClass = '';
        switch (appointment.status) {
            case 'confirmed': statusClass = 'status-confirmed'; break;
            case 'pending': statusClass = 'status-pending'; break;
            case 'completed': statusClass = 'status-completed'; break;
            case 'cancelled': statusClass = 'status-cancelled'; break;
            default: statusClass = 'status-pending';
        }

        // Build clients HTML
        let clientsHtml = '';
        if (clients && clients.length > 0) {
            clients.forEach((client, index) => {
                let serviceName = client.service === 'reg' ? 'Registration' : 
                                 (client.service === 'updating' ? 'Correction/Updating' : 'Status Inquiry');
                clientsHtml += `
                    <div class="client-card">
                        <div class="client-header">
                            <span>Client #${index + 1}</span>
                            <span class="status-badge" style="background: #e0e7ff; color: #3730a3;">${serviceName}</span>
                        </div>
                        <div class="client-detail-row">
                            <div class="client-detail-label">Full Name:</div>
                            <div class="client-detail-value"><strong>${escapeHtml(client.first_name)} ${escapeHtml(client.last_name)}</strong></div>
                        </div>
                        <div class="client-detail-row">
                            <div class="client-detail-label">Service Type:</div>
                            <div class="client-detail-value">${serviceName}</div>
                        </div>
                `;
                
                // Add client-specific fields based on service type
                if (client.service === 'reg') {
                    if (client.birth_cert_number || client.birth_cert_number !== 'N/A') {
                        clientsHtml += `
                            <div class="client-detail-row">
                                <div class="client-detail-label">Birth Cert #:</div>
                                <div class="client-detail-value">${escapeHtml(client.birth_cert_number || 'N/A')}</div>
                            </div>
                        `;
                    }
                    if (client.mother_name) {
                        clientsHtml += `
                            <div class="client-detail-row">
                                <div class="client-detail-label">Mother's Name:</div>
                                <div class="client-detail-value">${escapeHtml(client.mother_name)}</div>
                            </div>
                        `;
                    }
                } else if (client.service === 'updating') {
                    if (client.reference_number || client.reference_number !== 'N/A') {
                        clientsHtml += `
                            <div class="client-detail-row">
                                <div class="client-detail-label">Reference #:</div>
                                <div class="client-detail-value">${escapeHtml(client.reference_number || 'N/A')}</div>
                            </div>
                        `;
                    }
                    if (client.correction_type) {
                        clientsHtml += `
                            <div class="client-detail-row">
                                <div class="client-detail-label">Correction Type:</div>
                                <div class="client-detail-value">${escapeHtml(client.correction_type)}</div>
                            </div>
                        `;
                    }
                } else if (client.service === 'inquiry') {
                    if (client.psa_number || client.psa_number !== 'N/A') {
                        clientsHtml += `
                            <div class="client-detail-row">
                                <div class="client-detail-label">PSA Number:</div>
                                <div class="client-detail-value">${escapeHtml(client.psa_number || 'N/A')}</div>
                            </div>
                        `;
                    }
                    if (client.inquiry_type) {
                        clientsHtml += `
                            <div class="client-detail-row">
                                <div class="client-detail-label">Inquiry Type:</div>
                                <div class="client-detail-value">${escapeHtml(client.inquiry_type)}</div>
                            </div>
                        `;
                    }
                }
                
                clientsHtml += `</div>`;
            });
        } else {
            clientsHtml = '<p><em>No client details available</em></p>';
        }

        modalBody.innerHTML = `
            <div class="appointment-header">
                <h4 style="margin:0 0 10px 0;">${escapeHtml(appointment.appointment_number)}</h4>
                <p style="margin:0; opacity:0.9;"><i class="fas fa-calendar-alt"></i> ${formatDate(appointment.appointment_date)} | <i class="fas fa-clock"></i> ${appointment.time_slot ? appointment.time_slot.label : 'N/A'}</p>
            </div>
            
            <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h5 style="margin: 0 0 10px 0; color: #0f3b6f;"><i class="fas fa-user"></i> Contact Person Details</h5>
                <div class="client-detail-row">
                    <div class="client-detail-label">Name:</div>
                    <div class="client-detail-value"><strong>${escapeHtml(appointment.contact_name)}</strong></div>
                </div>
                <div class="client-detail-row">
                    <div class="client-detail-label">Mobile Number:</div>
                    <div class="client-detail-value">${escapeHtml(appointment.contact_mobile)}</div>
                </div>
                ${appointment.contact_email ? `
                <div class="client-detail-row">
                    <div class="client-detail-label">Email:</div>
                    <div class="client-detail-value">${escapeHtml(appointment.contact_email)}</div>
                </div>
                ` : ''}
                <div class="client-detail-row">
                    <div class="client-detail-label">Status:</div>
                    <div class="client-detail-value"><span class="status-badge ${statusClass}">${appointment.status.toUpperCase()}</span></div>
                </div>
            </div>
            
            <h5 style="margin: 0 0 15px 0; color: #0f3b6f;"><i class="fas fa-users"></i> Client Details (${clients ? clients.length : 0})</h5>
            ${clientsHtml}
        `;
        
        document.getElementById('fullAppointmentModal').style.display = 'block';
    }

    function showErrorModal() {
        const modalBody = document.getElementById('fullAppointmentModalBody');
        modalBody.innerHTML = `<div style="text-align:center;padding:20px;">Error loading appointment details.</div>`;
        document.getElementById('fullAppointmentModal').style.display = 'block';
    }

    function closeFullAppointmentModal() {
        document.getElementById('fullAppointmentModal').style.display = 'none';
    }

    // Keep original modal for backward compatibility
    function openModal(appointmentId) {
        fetch(`/admin/calendar/${appointmentId}/json`)
            .then(response => response.json())
            .then(data => {
                if (data.success) displayModalContent(data.appointment);
                else showErrorModal();
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal();
            });
    }

    function displayModalContent(appointment) {
        const modalBody = document.getElementById('modalBody');
        let statusClass = '';
        switch (appointment.status) {
            case 'confirmed': statusClass = 'status-confirmed'; break;
            case 'pending': statusClass = 'status-pending'; break;
            case 'completed': statusClass = 'status-completed'; break;
            case 'cancelled': statusClass = 'status-cancelled'; break;
            default: statusClass = 'status-pending';
        }

        let clientsHtml = '';
        if (appointment.clients && appointment.clients.length > 0) {
            appointment.clients.forEach(client => {
                let serviceName = client.service === 'reg' ? 'Registration' : 
                                 (client.service === 'updating' ? 'Updating' : 'Inquiry');
                clientsHtml += `<div><strong>${escapeHtml(client.first_name)} ${escapeHtml(client.last_name)}</strong> - ${serviceName}</div>`;
            });
        }

        modalBody.innerHTML = `
            <div style="padding: 10px;">
                <p><strong>Appointment #:</strong> ${escapeHtml(appointment.appointment_number)}</p>
                <p><strong>Status:</strong> <span class="status-badge ${statusClass}">${appointment.status.toUpperCase()}</span></p>
                <p><strong>Date:</strong> ${formatDate(appointment.appointment_date)}</p>
                <p><strong>Time:</strong> ${appointment.time_slot ? appointment.time_slot.label : 'N/A'}</p>
                <p><strong>Contact:</strong> ${escapeHtml(appointment.contact_name)}</p>
                <p><strong>Phone:</strong> ${escapeHtml(appointment.contact_mobile)}</p>
                <p><strong>Clients (${appointment.clients ? appointment.clients.length : 0}):</strong></p>
                <div>${clientsHtml || '<em>No clients found</em>'}</div>
            </div>
        `;
        document.getElementById('appointmentModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('appointmentModal').style.display = 'none';
    }

    function openDayModal(date) {
        currentSelectedDate = date;
        currentSelectedService = 'reg';
        currentSelectedSlotId = null;
        
        const dateObj = new Date(date);
        document.getElementById('dayModalTitle').innerHTML = `Appointments for ${dateObj.toLocaleDateString()}`;
        document.getElementById('dayDetailsModal').style.display = 'block';
        
        document.querySelectorAll('.service-tab').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.service === 'reg') tab.classList.add('active');
        });
        
        loadTimeSlots(date, 'reg');
    }
    
    function closeDayModal() {
        document.getElementById('dayDetailsModal').style.display = 'none';
        document.getElementById('dayAppointmentsContainer').style.display = 'none';
        currentSelectedSlotId = null;
    }
    
    function loadTimeSlots(date, service) {
    const container = document.getElementById('dayTimeSlotsContainer');
    container.innerHTML = '<div class="loading-spinner">Loading time slots...</div>';
    
    fetch(`/admin/calendar/time-slots?date=${date}&service=${service}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.time_slots.length > 0) {
                let html = '<div class="time-slots-list">';
                data.time_slots.forEach(slot => {
                    // Determine color class based on status
                    let statusClass = '';
                    let statusText = '';
                    let borderStyle = '';
                    
                    switch(slot.status) {
                        case 'full':
                            statusClass = 'time-slot-full';
                            statusText = '🔴 FULL';
                            borderStyle = 'border: 2px solid #dc2626;';
                            break;
                        case 'limited':
                            statusClass = 'time-slot-limited';
                            statusText = '🟠 AVAILABLE';
                            borderStyle = 'border: 2px solid #f97316;';
                            break;
                        case 'available':
                            statusClass = 'time-slot-available';
                            statusText = '🟡 AVAILABLE';
                            borderStyle = 'border: 2px solid #eab308;';
                            break;
                        case 'plenty':
                            statusClass = 'time-slot-plenty';
                            statusText = '🟢 AVAILABLE';
                            borderStyle = 'border: 2px solid #22c55e;';
                            break;
                        case 'unavailable':
                            statusClass = 'time-slot-unavailable';
                            statusText = '⚫ UNAVAILABLE';
                            borderStyle = 'border: 2px solid #6b7280; background: #f3f4f6;';
                            break;
                        default:
                            statusClass = 'time-slot-default';
                            borderStyle = 'border: 2px solid #cbd5e1;';
                    }
                    
                    // Don't hide any time slots - show all with appropriate styling
                    html += `
                        <div class="time-slot-item ${statusClass}" 
                             data-slot-id="${slot.id}" 
                             data-slot-label="${slot.slot_label}"
                             style="${borderStyle} cursor: pointer; margin-bottom: 10px; padding: 12px; border-radius: 8px; transition: all 0.3s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong>${slot.slot_label}</strong>
                                    <div style="font-size: 12px; margin-top: 5px;">
                                        ${slot.available_slots} / ${slot.total_capacity} slots available
                                    </div>
                                </div>
                                <div>
                                    <span class="status-badge" style="${slot.status === 'full' ? 'background: #fee2e2; color: #dc2626;' : 
                                                                       (slot.status === 'limited' ? 'background: #fff3e0; color: #f97316;' :
                                                                       (slot.status === 'available' ? 'background: #fef9c3; color: #854d0e;' :
                                                                       (slot.status === 'plenty' ? 'background: #dcfce7; color: #16a34a;' :
                                                                       'background: #e5e7eb; color: #4b5563;')))}">
                                        ${statusText}
                                    </span>
                                </div>
                            </div>
                            ${slot.available_slots > 0 ? `<small style="color: #64748b;">Click to view appointments</small>` : 
                                                          `<small style="color: #dc2626;">No available slots</small>`}
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
                
                // Add click handlers to all time slots (even full/unavailable ones)
                document.querySelectorAll('.time-slot-item').forEach(el => {
                    el.addEventListener('click', () => {
                        // Only load appointments if there are appointments to show
                        const slotId = el.dataset.slotId;
                        const slotLabel = el.dataset.slotLabel;
                        const status = el.classList.contains('time-slot-full') ? 'full' : 
                                     (el.classList.contains('time-slot-unavailable') ? 'unavailable' : 'available');
                        
                        // Remove selected class from all
                        document.querySelectorAll('.time-slot-item').forEach(s => s.classList.remove('selected'));
                        el.classList.add('selected');
                        
                        // Load appointments even if full (to show existing appointments)
                        loadAppointments(date, slotId, service, slotLabel);
                    });
                });
            } else {
                container.innerHTML = '<div class="loading-spinner">No time slots configured for this date.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="loading-spinner">Error loading time slots.</div>';
        });
}
    
    function loadAppointments(date, timeSlotId, service, slotLabel) {
        const container = document.getElementById('dayAppointmentsContainer');
        const listEl = document.getElementById('dayAppointmentsList');
        const labelEl = document.getElementById('selectedSlotLabel');
        
        labelEl.textContent = slotLabel;
        listEl.innerHTML = '<div class="loading-spinner">Loading appointments...</div>';
        container.style.display = 'block';
        
        fetch(`/admin/calendar/by-time-slot?date=${date}&time_slot_id=${timeSlotId}&service=${service}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.appointments.length > 0) {
                    let html = '';
                    data.appointments.forEach(appointment => {
                        // Display only appointment number
                        html += `
                            <div class="appointment-item" onclick="openFullAppointmentModal('${appointment.id}')">
                                <div class="appointment-number">
                                    <i class="fas fa-ticket-alt"></i> ${escapeHtml(appointment.appointment_number)}
                                </div>
                                <div style="font-size: 12px; color: #64748b;">
                                    Click to view full details
                                </div>
                            </div>
                        `;
                    });
                    listEl.innerHTML = html;
                } else {
                    listEl.innerHTML = '<div class="loading-spinner">No appointments for this time slot.</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                listEl.innerHTML = '<div class="loading-spinner">Error loading appointments.</div>';
            });
    }
    
    async function fetchAndDisplaySlotData(startDate, endDate) {
        try {
            const response = await fetch(`/admin/calendar/slot-data?start=${startDate}&end=${endDate}`);
            const data = await response.json();
            if (data.success) {
                slotData = data.slotData;
                updateDayCellsWithSlotInfo();
            }
        } catch (error) {
            console.error('Error fetching slot data:', error);
        }
    }
    
function updateDayCellsWithSlotInfo() {
    // Get all day cells
    const dayCells = document.querySelectorAll('.fc-daygrid-day');
    
    dayCells.forEach(cell => {
        // Find the date attribute
        const dateAttr = cell.getAttribute('data-date');
        if (!dateAttr) return;
        
        // Remove existing slot info if any
        const existingInfo = cell.querySelector('.day-slot-info');
        if (existingInfo) existingInfo.remove();
        
        // Get slot data for this date from the server
        const data = slotData[dateAttr];
        if (!data) return;
        
        // Use the actual values from the database (sent by the server)
        const remaining = data.remaining || 0;
        const total = data.total || 0;  // This now comes from actual database calculations
        
        // Skip if no capacity data
        if (total === 0) return;
        
        // Determine status class and icon based on remaining percentage
        const percentageRemaining = (remaining / total) * 100;
        let statusClass = '';
        let statusIcon = '';
        
        if (remaining === 0) {
            statusClass = 'available-low';
            statusIcon = '🔴';
        } else if (percentageRemaining <= 33) {
            statusClass = 'available-low';
            statusIcon = '🟠';
        } else if (percentageRemaining <= 66) {
            statusClass = 'available-medium';
            statusIcon = '🟡';
        } else {
            statusClass = 'available-high';
            statusIcon = '🟢';
        }
        
        // Create slot info element
        const slotInfo = document.createElement('div');
        slotInfo.className = `day-slot-info ${statusClass}`;
        slotInfo.innerHTML = `
            <span class="slot-count">${statusIcon} ${remaining} / ${total}</span>
            <span class="slot-label">Available Slots (All Services)</span>
        `;
        
        // Find the day frame and append the slot info
        const frame = cell.querySelector('.fc-daygrid-day-frame');
        if (frame) {
            frame.appendChild(slotInfo);
        }
    });
}
    
    function observeCalendarRendering() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.target.classList && 
                    mutation.target.classList.contains('fc-daygrid-body')) {
                    setTimeout(() => updateDayCellsWithSlotInfo(), 100);
                }
            });
        });
        
        const calendarBody = document.querySelector('.fc-daygrid-body');
        if (calendarBody) {
            observer.observe(calendarBody, { childList: true, subtree: true });
        }
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

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function setupTabListeners() {
        document.querySelectorAll('.service-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const service = this.dataset.service;
                currentSelectedService = service;
                currentSelectedSlotId = null;
                
                document.querySelectorAll('.service-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                document.getElementById('dayAppointmentsContainer').style.display = 'none';
                if (currentSelectedDate) {
                    loadTimeSlots(currentSelectedDate, service);
                }
            });
        });
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('appointmentModal');
        const dayModal = document.getElementById('dayDetailsModal');
        const fullModal = document.getElementById('fullAppointmentModal');
        if (event.target === modal) closeModal();
        if (event.target === dayModal) closeDayModal();
        if (event.target === fullModal) closeFullAppointmentModal();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            events: [], // No events to display, we're using day cells instead
            dateClick: function(info) {
                openDayModal(info.dateStr);
            },
            datesSet: function(info) {
                // Fetch slot data for the visible date range and update day cells
                fetchAndDisplaySlotData(info.startStr, info.endStr);
            },
            viewDidMount: function() {
                setTimeout(() => {
                    const start = calendar.view.activeStart;
                    const end = calendar.view.activeEnd;
                    if (start && end) {
                        fetchAndDisplaySlotData(
                            start.toISOString().split('T')[0],
                            end.toISOString().split('T')[0]
                        );
                    }
                }, 100);
            }
        });
        calendar.render();
        
        setupTabListeners();
    });
</script>