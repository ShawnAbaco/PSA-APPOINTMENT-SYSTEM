{{-- resources/views/client/appointment.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PhilSys Appointment System</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/appointment/appointment.css') }}">
    <link rel="stylesheet" href="{{ asset('css/appointment/responsive.css') }}">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Pass service options from controller to JavaScript -->
    <script>
        window.serviceOptions = {
            'reg': 'National ID Registration',
            'updating': 'Correction/Updating',
            'inquiry': 'Status Inquiry / Retrieval Of TRN / Other Concern'
        };
        window.identityReminders = {
            'reg': 'The person named below must be the one registering for the National ID.',
            'updating': 'The person named below must be one of the parents or the authorized representative requesting the correction/update.',
            'inquiry': 'The person named below must be the one requesting status inquiry, TRN retrieval, or other concern.'
        };
    </script>
</head>

<body>
    <!-- PRIVACY NOTICE MODAL -->
    <div class="privacy-overlay" id="privacyModal">
        <div class="privacy-modal">
            <h2>Privacy Notice</h2>
            <p>This system collects and processes limited personal information solely for the purpose of scheduling,
                managing, and confirming PhilSys Appointment Management System, in accordance with the <span
                    class="legal-ref">Data Privacy Act of 2012 (RA 10173)</span> and applicable Philippine Statistics
                Authority (PSA) policies.</p>
            <p>The personal data collected include your full name, email address or mobile number, selected service, and
                preferred appointment schedule.</p>
            <p>Your personal data shall be used exclusively for appointment management and communication purposes. These
                data are stored securely and protected by appropriate administrative, technical, and physical
                safeguards.</p>
            <p>Your personal data will not be shared or disclosed to unauthorized parties, except to authorized PSA
                personnel or when required by law.</p>
            <p>As a data subject, you have the right to access, correct, or request deletion of your personal data,
                subject to applicable laws and regulations.</p>
            <p>For data privacy concerns, you may contact the PSA Data Protection Officer through the official PSA
                communication channels.</p>
            <p>By proceeding, you confirm that you have read, understood, and voluntarily consent to the collection and
                processing of your personal data for the stated purpose.</p>
            <button class="btn-agree" id="agreePrivacyBtn">I AGREE</button>
        </div>
    </div>

    <!-- REQUIREMENTS MODAL -->
    <div class="modal-overlay" id="reqModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalServiceTitle">Requirements</h3>
                <span class="close-modal" id="closeReqModal">&times;</span>
            </div>
            <div id="modalBodyContent" class="req-list"></div>
            <button class="btn-understand" id="understandBtn">I Understand</button>
        </div>
    </div>

<!-- SUCCESS MODAL -->
<div class="modal-overlay" id="successModal" style="display: none;">
    <div class="modal-content" style="max-width: 600px; text-align: center;">
        <div class="modal-header" style="border-bottom: none; padding-bottom: 0; display: flex; justify-content: flex-end;">
            <span class="close-modal" id="closeSuccessModal">&times;</span>
        </div>
        <div id="successBody" style="padding: 20px; padding-top: 0;">
            <div id="successDetails"></div>
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                <button class="btn-next" id="downloadPngBtn" style="background: #28a745;">
                    <i class="fas fa-image"></i> Download PNG
                </button>
                <button class="btn-next" id="downloadPdfBtn" style="background: #dc3545;">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- LOADING OVERLAY -->
    <div id="loadingOverlay"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 20px; border-radius: 10px; text-align: center;">
            <div class="spinner"></div>
            <p>Processing your appointment...</p>
        </div>
    </div>

    <!-- MAIN APPOINTMENT CARD -->
    <div class="appointment-card">
        <div class="card-header">
            <div class="logos">
                <img src="{{ asset('images/psa-logo.png') }}" alt="PSA Logo" style="height: 60px; width: auto;">
            </div>
            <!-- <div class="header-title">
                <img src="{{ asset('images/logo.png') }}" alt="National ID System" style="height: 50px; width: auto;">
            </div> -->
        </div>

        <div class="stepper">
            <div class="step active" id="step1"><span class="step-num">0</span> Guide</div>
            <div class="step" id="step2"><span class="step-num">1</span> Applicants</div>
            <div class="step" id="step3"><span class="step-num">2</span> Schedule</div>
            <div class="step" id="step4"><span class="step-num">3</span> Contact</div>
            <div class="step" id="step5"><span class="step-num">4</span> Review</div>
            <div class="step" id="step6"><span class="step-num">5</span> Confirm</div>
            <div class="step" id="step7"
                style="margin-left: auto; background: var(--primary); color: white; border: 1px solid var(--primary); padding: 8px 16px; border-radius: 40px; font-weight: 700;">
                <span>Book your appointment now</span>
            </div>
        </div>

        <div class="content-body">
            <!-- STEP 1: GUIDE -->
            <div id="sectionGuide">
                <div class="guide-step">
                    <h3><span class="step-number"></span> How to Book an Appointment</h3>
                    <p>Follow these simple steps to schedule your National ID appointment</p>
                </div>

                <div class="howto-grid">
                    <div class="howto-card">
                        <span class="howto-step">STEP 1</span>
                        <h3>Start Booking</h3>
                        <p>Click <strong>"Start Booking"</strong> button scroll below to begin your appointment process.</p>
                        <small>Initial step to access the booking system</small>
                    </div>
                    <div class="howto-card">
                        <span class="howto-step">STEP 2</span>
                        <h3>Add Applicants</h3>
                        <p>Add up to <strong>4 persons</strong> per booking. Enter name, sex, birthdate, and select service type.</p>
                        <small>Each person selects their own service</small>
                    </div>
                    <div class="howto-card">
                        <span class="howto-step">STEP 3</span>
                        <h3>Pick Date & Time</h3>
                        <p>Select a <strong>green highlighted date</strong> on the calendar, then choose an available time slot for each applicant.</p>
                        <small>Gray dates = unavailable</small>
                    </div>
                    <div class="howto-card">
                        <span class="howto-step">STEP 4</span>
                        <h3>Contact Details</h3>
                        <p>Provide contact person name, email (optional), and <strong>10-digit mobile number starting with 9</strong>.</p>
                        <small>For appointment confirmation & reminders</small>
                    </div>
                </div>

                <div class="howto-grid second-row">
                    <div class="howto-card">
                        <span class="howto-step">STEP 5</span>
                        <h3>Review Information</h3>
                        <p>Double-check all applicant names, services, date, time slots, and contact info for accuracy before proceeding.</p>
                        <small>Verify everything is correct</small>
                    </div>
                    <div class="howto-card">
                        <span class="howto-step">STEP 6</span>
                        <h3>Confirm & Submit</h3>
                        <p>Check the confirmation box and click <strong>"Confirm & Submit"</strong>. Wait for processing to complete.</p>
                        <small>Final submission step</small>
                    </div>
                    <div class="howto-card">
                        <span class="howto-step">STEP 7</span>
                        <h3>Save Confirmation</h3>
                        <p>Save your <strong>Appointment Slip.</strong> Download your slip as PNG or PDF. A PDF copy will be sent to your email if provided. <strong>If no email was provided, please save your slip now as you cannot retrieve it again.</strong></p>
                        <small>Don't lose your Slip!</small>
                    </div>
                </div>
                <div style="background: #e3f2fd; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <i class="fas fa-info-circle" style="color: #2c5f8a;"></i>
                    <strong>Important Reminders:</strong>
                    <ul style="margin-top: 10px; margin-bottom: 0; margin-left: 20px">
                        <li>Maximum of 4 persons per appointment</li>
                        <li>Each person can select their own service type</li>
                        <li>Bring valid IDs and required documents</li>
                        <li>Arrive 30 minutes before your scheduled time</li>
                        <li>Click the button below (Start Booking) to set your Appointment.</li>
                    </ul>
                </div>

                <button class="btn-next" id="startBookingBtn">Start Booking <i
                        class="fas fa-arrow-right"></i></button>
            </div>

            <!-- STEP 2: CLIENTS -->
            <div id="sectionClients" class="hidden">
                <div class="section-title">Applicant Information</div>
                <p style="color: var(--gray-500); margin-bottom: 20px;">
                    <i class="fas fa-users"></i> Add the persons who will attend (Maximum 4 persons)
                </p>



                <div class="clients-list" id="clientsList"></div>

                <button class="btn-add-client" id="addClientBtn">
                    <i class="fas fa-plus-circle"></i> Add Another Person (Max 4)
                </button>

                <div id="clientCountContainer"
                    style="margin-top: 20px; padding: 14px; background: var(--gray-50); border-radius: var(--radius); border: 1px solid var(--gray-200);">
                    <p style="display: flex; justify-content: space-between;">
                        <span><i class="fas fa-users" style="color: var(--primary);"></i> <strong>Total
                                persons:</strong></span>
                        <span style="font-weight: 700; color: var(--primary);"><span id="clientCount">1</span> /
                            4</span>
                    </p>
                </div>

                <button class="btn-next" id="nextToSchedule">Next: Select Schedule <i
                        class="fas fa-arrow-right"></i></button>
            </div>

            <!-- STEP 3: SCHEDULE -->
            <div id="sectionSchedule" class="hidden">
                <div class="section-title">Select Appointment Date & Time</div>

                <div class="slot-info" id="slotInfo">
                    <i class="fas fa-calendar-check" style="color: var(--primary);"></i>
                    <span id="slotInfoText">Select an available date first</span>
                </div>

                <div class="calendar-container">
                    <div class="calendar-header">
                        <span class="calendar-month-year" id="calendarMonthYear">Loading...</span>
                        <div class="calendar-nav">
                            <button class="calendar-nav-btn" id="prevMonthBtn"><i
                                    class="fas fa-chevron-left"></i></button>
                            <button class="calendar-nav-btn" id="nextMonthBtn"><i
                                    class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="calendar-weekdays">
                        <span class="weekday">Mo</span><span class="weekday">Tu</span><span class="weekday">We</span>
                        <span class="weekday">Th</span><span class="weekday">Fr</span><span
                            class="weekday">Sa</span><span class="weekday">Su</span>
                    </div>
                    <div class="calendar-days" id="calendarDays">
                        <div class="loading-spinner">Loading calendar...</div>
                    </div>
                </div>

                <div class="selected-date-display">
                    <i class="far fa-check-circle" style="color: var(--success);"></i>
                    <span>Selected Date:</span>
                    <span id="selectedDateText" style="font-weight: 600; color: var(--gray-900);">No date
                        selected</span>
                </div>

                <div id="perApplicantTimeSlotsContainer" style="margin-top: 30px;"></div>

                <div class="btn-group">
                    <button class="btn-next" id="nextToContact">Next: Contact Info <i
                            class="fas fa-arrow-right"></i></button>
                    <button class="btn-next back-btn" id="backToClients"><i class="fas fa-arrow-left"></i>
                        Back</button>
                </div>
            </div>

            <!-- STEP 4: CONTACT -->
            <div id="sectionContact" class="hidden">
                <div class="section-title">Primary Contact Information</div>
                <p style="color: var(--gray-500); margin-bottom: 24px;">
                    <i class="fas fa-phone-alt"></i> Who should we contact?
                </p>

                <div style="margin-bottom: 20px;">
                    <label>Contact Person Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="contactName" placeholder="e.g., Maria Dela Cruz">
                </div>

                <div style="margin-bottom: 20px;">
                    <label>Email Address</label>
                    <input type="email" id="contactEmail" placeholder="maria@example.com">
                </div>

                <div style="margin-bottom: 20px;">
                    <label>Mobile Number</label>
                    <div
                        style="display: flex; align-items: center; border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: hidden;">
                        <span
                            style="background: var(--gray-100); padding: 10px 12px; font-weight: 500; color: var(--gray-700); border-right: 1px solid var(--gray-200);">+63</span>
                        <input type="tel" id="contactMobile" name="contact_mobile_suffix"
                            placeholder="9XXXXXXXXX" maxlength="10" pattern="[0-9]{10}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); if(this.value.length > 0 && !this.value.startsWith('9')) this.value = '';"
                            style="flex: 1; padding: 10px 12px; border: none; outline: none; font-size: 14px;">
                    </div>
                    <input type="hidden" name="contact_mobile" id="contactMobileFull">
                    <small style="color: #666; display: block; margin-top: 5px;">Format: +63 9XXXXXXXXX (10 digits
                        starting with 9, e.g., 9123456789)</small>
                </div>

                <button class="btn-next" id="nextToReview">Review Appointment <i
                        class="fas fa-arrow-right"></i></button>
                <button class="btn-next back-btn" id="backToScheduleFromContact"><i class="fas fa-arrow-left"></i>
                    Back</button>
            </div>

            <!-- STEP 5: REVIEW -->
            <div id="sectionReview" class="hidden">
                <div class="section-title">Review Your Appointment</div>

                <div class="review-container">
                    <div class="review-section">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span class="review-section-title"><i class="fas fa-users"
                                    style="color: var(--primary);"></i> Applicants (<span
                                    id="reviewClientCount">1</span>)</span>
                            <button class="edit-link" data-edit="clients" style="color: var(--secondary);"><i
                                    class="fas fa-pen"></i> Edit</button>
                        </div>
                        <div id="reviewClientsList"></div>
                    </div>

                    <div class="review-section">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span class="review-section-title"><i class="far fa-calendar-alt"
                                    style="color: var(--primary);"></i> Schedule</span>
                            <button class="edit-link" data-edit="schedule" style="color: var(--secondary);"><i
                                    class="fas fa-pen"></i> Edit</button>
                        </div>
                        <div><span id="reviewDateTime" style="font-weight: 600;">-</span></div>
                    </div>

                    <div class="review-section">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span class="review-section-title"><i class="fas fa-address-book"
                                    style="color: var(--primary);"></i> Contact</span>
                            <button class="edit-link" data-edit="contact" style="color: var(--secondary);"><i
                                    class="fas fa-pen"></i> Edit</button>
                        </div>
                        <div>
                            <span id="reviewContactName" style="font-weight: 600;">-</span><br>
                            <span id="reviewContactEmail" style="color: var(--gray-500);">-</span><br>
                            <span id="reviewContactMobile" style="font-weight: 500;">-</span>
                        </div>
                    </div>
                </div>

                <button class="btn-next" id="nextToConfirm">Proceed to Confirm <i
                        class="fas fa-arrow-right"></i></button>
                <button class="btn-next back-btn" id="backToContact"><i class="fas fa-arrow-left"></i> Back</button>
            </div>

            <!-- STEP 6: CONFIRM -->
<div id="sectionConfirm" class="hidden">
    <div class="section-title">Confirm Appointment</div>

    <div class="summary-box" style="background: #f8fafc; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <h4 style="margin: 0 0 15px 0; color: var(--primary); font-size: 1.1rem;">
            <i class="fas fa-clipboard-list"></i> Appointment Summary
        </h4>

        <!-- Applicants Section -->
        <div style="margin-bottom: 20px;">
            <div style="background: var(--primary); color: rgb(0, 0, 0); padding: 8px 12px; border-radius: 8px; margin-bottom: 10px;">
                <i class="fas fa-users"></i> Applicants (<span id="sumClients">0</span>)
            </div>
            <div id="sumApplicantsList" style="padding: 10px; background: white; border-radius: 8px; border: 1px solid var(--gray-200);">
                <!-- Dynamic content will be inserted here -->
            </div>
        </div>

        <!-- Schedule Section -->
        <div style="margin-bottom: 20px;">
            <div style="background: var(--primary); color: rgb(0, 0, 0); padding: 8px 12px; border-radius: 8px; margin-bottom: 10px;">
                <i class="far fa-calendar-alt"></i> Schedule
            </div>
            <div id="sumDateTime" style="padding: 12px; background: white; border-radius: 8px; border: 1px solid var(--gray-200);">
                <!-- Dynamic content -->
            </div>
        </div>

        <!-- Contact Section -->
        <div style="margin-bottom: 20px;">
            <div style="background: var(--primary); color: rgb(0, 0, 0); padding: 8px 12px; border-radius: 8px; margin-bottom: 10px;">
                <i class="fas fa-address-book"></i> Contact Information
            </div>
            <div id="sumContact" style="padding: 12px; background: white; border-radius: 8px; border: 1px solid var(--gray-200);">
                <!-- Dynamic content -->
            </div>
        </div>


    </div>

    <div class="confirmation-checkbox" style="margin: 20px 0;">
        <input type="checkbox" id="confirmCheckbox">
        <label for="confirmCheckbox">
            <strong>I confirm that all information is accurate and complete.</strong><br>
            <span style="font-size: 0.85rem; color: var(--gray-500);">I have read all service requirements and will bring necessary documents.</span>
        </label>
    </div>

    <button class="btn-next" id="submitRequestBtn" disabled>
        <i class="fas fa-check-circle"></i> Confirm & Submit
    </button>

    <div class="reminder" style="margin-top: 20px; padding: 12px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
        <i class="fas fa-save" style="color: #856404;"></i>
        <strong style="color: #856404;">Reminder:</strong>
        <span style="color: #856404;">Please save your appointment reference number and reference code for verification.</span>
    </div>

    <button class="btn-next back-btn" id="backToReview" style="margin-top: 15px;">
        <i class="fas fa-arrow-left"></i> Back
    </button>
</div>
        </div>

        <div class="footer-note">
    <div class="copyright-text">
        <i class="fas fa-copyright"></i>
        2026 PSA - Misamis Oriental | PhilSys Appointment Management System. All rights reserved. | RA 11055 (PhilSys Act)
    </div>
    <div class="copyright-devs">
        <i class="fas fa-code"></i>
        Developed by Shawn Laurence M. Abaco | Kent Zyrone L. Flores
    </div>
    <div class="copyright-version">
        <i class="fas fa-code-branch"></i>
        Version v1.0.0
    </div>
</div>
    </div>

    <!-- Hidden fields for location data -->
    <input type="hidden" id="userLat" value="">
    <input type="hidden" id="userLng" value="">
    <input type="hidden" id="userCity" value="">
    <input type="hidden" id="userAddress" value="">
    <input type="hidden" id="userZipcode" value="">

<!-- QR Code Generator -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            'use strict';

            let selectedDate = null;
            let availableDatesData = [];
            let clientTimeSlots = {};
            let availableTimeSlotsCache = [];
            let clientTrnData = {};
            let userLocation = {
                lat: null,
                lng: null,
                city: null,
                address: null,
                zipcode: null
            };
            let html5QrCode = null;
            let currentQrClientId = null;
            let currentRequirementsClientId = null;

            let serviceOptions = window.serviceOptions || {};
            let identityReminders = window.identityReminders || {};

            const TRN_LENGTH = 29;
            const MAX_CLIENTS = 4;

            function isValidTrn(trnValue) {
                if (!trnValue) return false;
                const cleanTrn = trnValue.replace(/\s/g, '');
                return /^\d{29}$/.test(cleanTrn);
            }

            function getServiceName(code) {
                return serviceOptions[code] || code;
            }

            function getFullName(c) {
                const parts = [c.firstName, c.middleName, c.lastName].filter(p => p && p.trim());
                let fullName = parts.join(' ') || '(No name)';
                if (c.suffix) fullName += ' ' + c.suffix;
                return fullName;
            }

            async function loadAvailableDates() {
                const services = [...new Set(clients.map(c => c.service))];
                let url =
                    `{{ route('client.appointment.available-dates') }}?month=${currentMonth + 1}&year=${currentYear}&client_count=${clients.length}&services=${services.join(',')}`;
                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    if (data.success) {
                        availableDatesData = data.dates;
                        renderCalendar();
                    } else {
                        document.getElementById('calendarDays').innerHTML = '<div class="error">' + (data.message ||
                            'Failed to load available dates') + '</div>';
                    }
                } catch (error) {
                    console.error('Error loading dates:', error);
                    document.getElementById('calendarDays').innerHTML =
                        '<div class="error">Failed to load available dates.</div>';
                }
            }

            async function loadAvailableTimeSlotsForDate(date) {
                try {
                    const selectedServices = [...new Set(clients.map(c => c.service))];
                    const url =
                        `{{ route('client.appointment.available-time-slots') }}?date=${date}&services=${selectedServices.join(',')}&client_count=${clients.length}`;
                    const response = await fetch(url);
                    const data = await response.json();
                    if (data.success && data.time_slots && data.time_slots.length > 0) {
                        availableTimeSlotsCache = data.time_slots;
                        return availableTimeSlotsCache;
                    } else {
                        availableTimeSlotsCache = [];
                        return [];
                    }
                } catch (error) {
                    console.error('Error loading time slots:', error);
                    availableTimeSlotsCache = [];
                    return [];
                }
            }

            function renderPerApplicantTimeSlots() {
                const container = document.getElementById('perApplicantTimeSlotsContainer');

                if (!selectedDate) {
                    container.innerHTML =
                        '<div class="slot-info"><i class="fas fa-info-circle"></i> Please select a date first</div>';
                    return;
                }

                if (availableTimeSlotsCache.length === 0) {
                    container.innerHTML =
                        '<div class="slot-info"><i class="fas fa-exclamation-triangle"></i> No available time slots for this date. Please select another date.</div>';
                    return;
                }

                let html = '';
                clients.forEach((client) => {
                    const currentSelectedSlot = clientTimeSlots[client.id];
                    const hasSelectedTime = currentSelectedSlot && currentSelectedSlot.slotId;

                    html += `
                <div class="applicant-time-slot-card">
                    <div class="applicant-time-slot-header">
                        <i class="fas fa-user-circle"></i>
                        <span class="applicant-name">${escapeHtml(getFullName(client))}</span>
                        <span class="applicant-service">${getServiceName(client.service)}</span>
                    </div>
            `;

                    if (hasSelectedTime) {
                        html += `
                    <div class="applicant-time-selected">
                        <div class="selected-time-display">
                            <i class="fas fa-check-circle"></i>
                            <span>Selected Time:</span>
                            <span class="time-value">${escapeHtml(currentSelectedSlot.slotLabel)}</span>
                        </div>
                        <button type="button" class="btn-change-time" data-client-id="${client.id}">
                            <i class="fas fa-pen"></i> Change Time
                        </button>
                    </div>
                    <div class="applicant-time-options" id="timeOptions_${client.id}" style="display: none;">
                        <div class="time-slots-compact" id="timeSlotsCompact_${client.id}">
                            ${generateTimeSlotOptions(client.id)}
                        </div>
                    </div>
                `;
                    } else {
                        html += `
                    <div class="applicant-time-options visible" id="timeOptions_${client.id}">
                        <div style="margin-bottom: 10px; color: var(--gray-600);">
                            <i class="fas fa-clock"></i> Select preferred time slot:
                        </div>
                        <div class="time-slots-compact" id="timeSlotsCompact_${client.id}">
                            ${generateTimeSlotOptions(client.id)}
                        </div>
                    </div>
                `;
                    }
                    html += `</div>`;
                });

                container.innerHTML = html;

                document.querySelectorAll('.btn-change-time').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const clientId = parseInt(btn.dataset.clientId);
                        const optionsDiv = document.getElementById(`timeOptions_${clientId}`);
                        const selectedDiv = btn.closest('.applicant-time-selected');
                        if (optionsDiv) {
                            optionsDiv.style.display = 'block';
                            if (selectedDiv) selectedDiv.style.display = 'none';
                        }
                    });
                });

                clients.forEach(client => attachTimeSlotClickHandlers(client.id));
            }

            function generateTimeSlotOptions(clientId) {
                const client = clients.find(c => c.id === clientId);
                if (!client) return '<div>Error: Client not found</div>';

                const currentSelectedSlot = clientTimeSlots[clientId];

                let html = '';
                availableTimeSlotsCache.forEach(slot => {
                    const availableForService = slot.service_availability && slot.service_availability[client
                        .service] > 0;
                    const isSelected = currentSelectedSlot && currentSelectedSlot.slotId === slot.id;
                    const disabledClass = !availableForService ? 'disabled' : '';
                    const selectedClass = isSelected ? 'selected' : '';

                    html += `
                <div class="time-slot-option ${disabledClass} ${selectedClass}"
                     data-client-id="${clientId}"
                     data-slot-id="${slot.id}"
                     data-slot-label="${escapeHtml(slot.slot_label)}"
                     data-available="${availableForService}">
                    ${escapeHtml(slot.slot_label)}
                </div>
            `;
                });

                if (html === '') {
                    html = '<div class="slot-info">No available time slots for this service on this date.</div>';
                }
                return html;
            }

            function attachTimeSlotClickHandlers(clientId) {
                const options = document.querySelectorAll(`.time-slot-option[data-client-id="${clientId}"]`);
                options.forEach(option => {
                    option.removeEventListener('click', handleTimeSlotClick);
                    option.addEventListener('click', handleTimeSlotClick);
                });
            }

            function handleTimeSlotClick(e) {
                const option = e.currentTarget;
                if (option.dataset.available === 'false') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Slot Unavailable',
                        text: 'This time slot is fully booked.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                const clientId = parseInt(option.dataset.clientId);
                const slotId = parseInt(option.dataset.slotId);
                const slotLabel = option.dataset.slotLabel;

                clientTimeSlots[clientId] = {
                    slotId: slotId,
                    slotLabel: slotLabel
                };
                renderPerApplicantTimeSlots();
            }

            function renderCalendar() {
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
                    'September', 'October', 'November', 'December'
                ];
                document.getElementById('calendarMonthYear').textContent = `${monthNames[currentMonth]} ${currentYear}`;
                const firstDay = new Date(currentYear, currentMonth, 1);
                const startDayOfWeek = firstDay.getDay() || 7;
                const startOffset = startDayOfWeek === 7 ? 0 : startDayOfWeek - 1;
                const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
                let html = '';
                for (let i = 0; i < startOffset; i++) html += '<div class="calendar-day empty"></div>';
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                for (let d = 1; d <= daysInMonth; d++) {
                    const date = new Date(currentYear, currentMonth, d);
                    const dateKey =
                        `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    const dateData = availableDatesData ? availableDatesData.find(item => item.date === dateKey) : null;
                    const isPast = date < today;
                    const isSelected = selectedDate === dateKey;
                    let cls = 'calendar-day';
                    if (isPast) cls += ' disabled';
                    else if (dateData && dateData.available) cls += ' available';
                    else if (!dateData) cls += ' disabled';
                    if (isSelected) cls += ' selected';
                    html +=
                        `<div class="${cls}" data-date="${dateKey}" onclick="window.selectDate('${dateKey}')"><div class="day-number">${d}</div></div>`;
                }
                document.getElementById('calendarDays').innerHTML = html;
            }

            window.selectDate = async function(dateKey) {
                selectedDate = dateKey;
                clientTimeSlots = {};
                document.getElementById('selectedDateText').textContent = formatDisplayDate(dateKey);
                document.getElementById('slotInfoText').innerHTML =
                    `✓ Date selected. Please choose time slots for each applicant.`;
                renderCalendar();
                await loadAvailableTimeSlotsForDate(dateKey);
                renderPerApplicantTimeSlots();
            };

            // TRN Functions
            function validateAndStyleTrnInput(clientId) {
                const trnInput = document.getElementById(`trnNumber_${clientId}`);
                const charCounter = document.getElementById(`trnCharCounter_${clientId}`);
                if (!trnInput) return false;
                const trnValue = trnInput.value;
                const cleanValue = trnValue.replace(/\s/g, '');
                const isValid = isValidTrn(cleanValue);
                const currentLength = cleanValue.length;
                if (isValid) {
                    trnInput.classList.remove('trn-invalid');
                    trnInput.classList.add('trn-valid');
                    if (charCounter) {
                        charCounter.classList.remove('invalid');
                        charCounter.classList.add('valid');
                        charCounter.innerHTML =
                            `<i class="fas fa-check-circle"></i> ${currentLength}/${TRN_LENGTH} digits - Valid TRN`;
                    }
                    clientTrnData[clientId].trnNumber = cleanValue;
                    clientTrnData[clientId].isValid = true;
                    return true;
                } else {
                    trnInput.classList.remove('trn-valid');
                    trnInput.classList.add('trn-invalid');
                    if (charCounter) {
                        charCounter.classList.remove('valid');
                        charCounter.classList.add('invalid');
                        if (currentLength > 0 && currentLength < TRN_LENGTH) {
                            charCounter.innerHTML =
                                `<i class="fas fa-exclamation-triangle"></i> ${currentLength}/${TRN_LENGTH} digits - TRN must be exactly 29 digits`;
                        } else if (currentLength > TRN_LENGTH) {
                            charCounter.innerHTML =
                                `<i class="fas fa-exclamation-triangle"></i> ${currentLength}/${TRN_LENGTH} digits - Too many digits (max ${TRN_LENGTH})`;
                        } else {
                            charCounter.innerHTML =
                                `<i class="fas fa-info-circle"></i> TRN must be exactly 29 digits (0-9 only)`;
                        }
                    }
                    clientTrnData[clientId].trnNumber = trnValue;
                    clientTrnData[clientId].isValid = false;
                    return false;
                }
            }

            function createTrnHtml(clientId, hasTrnValue, trnNumberValue) {
                const isValid = trnNumberValue && isValidTrn(trnNumberValue);
                const validClass = isValid ? 'trn-valid' : '';
                const currentLength = trnNumberValue ? trnNumberValue.replace(/\s/g, '').length : 0;
                return `
            <div class="trn-field-group" data-client-id="${clientId}">
                <div class="trn-question"><i class="fas fa-question-circle"></i> DO YOU HAVE A TRN (TRANSACTION REFERENCE NUMBER)?</div>
                <div class="trn-checkbox-group">
                    <label><input type="radio" name="hasTrn_${clientId}" value="yes" ${hasTrnValue === true ? 'checked' : ''}> YES</label>
                    <label><input type="radio" name="hasTrn_${clientId}" value="no" ${hasTrnValue === false ? 'checked' : ''}> NO</label>
                </div>
                <div id="trnInputArea_${clientId}" style="display: ${hasTrnValue === true ? 'block' : 'none'};">
                    <div class="trn-input-group">
                        <label>TRN NUMBER (29 DIGITS) <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="trnNumber_${clientId}" placeholder="Enter 29-digit TRN number" class="trn-input ${validClass}" value="${trnNumberValue || ''}" maxlength="29" inputmode="numeric">
                        <span id="trnCharCounter_${clientId}" class="trn-char-counter ${isValid ? 'valid' : (currentLength > 0 ? 'invalid' : '')}">
                            ${currentLength > 0 ? (isValid ? `<i class="fas fa-check-circle"></i> ${currentLength}/29 digits - Valid TRN` : `<i class="fas fa-exclamation-triangle"></i> ${currentLength}/29 digits - TRN must be exactly 29 digits`) : `<i class="fas fa-info-circle"></i> TRN must be exactly 29 digits (0-9 only)`}
                        </span>
                    </div>
                    <div class="qr-scan-area">
                        <p><i class="fas fa-qrcode"></i> OR SCAN QR CODE</p>
                        <button type="button" class="qr-scan-btn" data-client-id="${clientId}"><i class="fas fa-camera"></i> SCAN QR CODE</button>
                        <div id="qr-reader_${clientId}" class="qr-reader-container" style="display: none;"></div>
                    </div>
                </div>
            </div>`;
            }

            function attachTrnEvents(clientId) {
                const radioYes = document.querySelector(`input[name="hasTrn_${clientId}"][value="yes"]`);
                const radioNo = document.querySelector(`input[name="hasTrn_${clientId}"][value="no"]`);
                const trnInputArea = document.getElementById(`trnInputArea_${clientId}`);
                const trnNumberInput = document.getElementById(`trnNumber_${clientId}`);
                const scanBtn = document.querySelector(`.qr-scan-btn[data-client-id="${clientId}"]`);
                if (!clientTrnData[clientId]) clientTrnData[clientId] = {
                    hasTrn: null,
                    trnNumber: '',
                    isValid: false
                };
                if (radioYes) radioYes.addEventListener('change', function() {
                    if (this.checked) {
                        clientTrnData[clientId].hasTrn = true;
                        if (trnInputArea) trnInputArea.style.display = 'block';
                        setTimeout(() => validateAndStyleTrnInput(clientId), 10);
                    }
                });
                if (radioNo) radioNo.addEventListener('change', function() {
                    if (this.checked) {
                        clientTrnData[clientId].hasTrn = false;
                        clientTrnData[clientId].trnNumber = '';
                        clientTrnData[clientId].isValid = false;
                        if (trnInputArea) trnInputArea.style.display = 'none';
                        if (trnNumberInput) trnNumberInput.value = '';
                    }
                });
                if (trnNumberInput) trnNumberInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length > TRN_LENGTH) this.value = this.value.slice(0, TRN_LENGTH);
                    clientTrnData[clientId].trnNumber = this.value;
                    validateAndStyleTrnInput(clientId);
                });
                if (scanBtn) scanBtn.addEventListener('click', () => startQrScanner(clientId));
            }

            async function startQrScanner(clientId) {
                const qrReaderDiv = document.getElementById(`qr-reader_${clientId}`);
                if (!qrReaderDiv) return;
                if (html5QrCode && html5QrCode.isScanning) await html5QrCode.stop();
                qrReaderDiv.style.display = 'block';
                currentQrClientId = clientId;
                html5QrCode = new Html5Qrcode(`qr-reader_${clientId}`);
                try {
                    await html5QrCode.start({
                            facingMode: "environment"
                        }, {
                            fps: 10,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        (decodedText) => {
                            const trnInput = document.getElementById(`trnNumber_${clientId}`);
                            if (trnInput) {
                                const digitsOnly = decodedText.replace(/[^0-9]/g, '');
                                const finalTrn = digitsOnly.slice(0, TRN_LENGTH);
                                trnInput.value = finalTrn;
                                clientTrnData[clientId].trnNumber = finalTrn;
                                validateAndStyleTrnInput(clientId);
                            }
                            if (html5QrCode && html5QrCode.isScanning) html5QrCode.stop();
                            qrReaderDiv.style.display = 'none';
                            alert(clientTrnData[clientId].isValid ? 'QR Code scanned successfully!' :
                                'QR Code scanned. Please ensure TRN is exactly 29 digits.');
                        },
                        (errorMessage) => console.log(`QR Scan error: ${errorMessage}`)
                    );
                } catch (err) {
                    console.error(`Failed to start QR scanner: ${err}`);
                    alert('Could not access camera. Please grant camera permissions.');
                    qrReaderDiv.style.display = 'none';
                }
            }

            function shouldShowTrnForClient(client) {
                return client.service === 'inquiry';
            }

            function validateTrnForClient(client) {
                if (!shouldShowTrnForClient(client)) return true;
                const trnData = clientTrnData[client.id];
                if (!trnData || trnData.hasTrn === null) return false;
                if (trnData.hasTrn === true && (!trnData.trnNumber || !trnData.isValid)) return false;
                return true;
            }

            let clients = [{
                id: 1,
                firstName: '',
                middleName: '',
                lastName: '',
                suffix: '',
                sex: 'Male',
                birthdate: '',
                service: '',
                reqAcknowledged: false,
                requirementsRead: false
            }];
            let nextClientId = 2;
            let currentMonth = new Date().getMonth();
            let currentYear = new Date().getFullYear();

            const privacyModal = document.getElementById('privacyModal');
            const reqModal = document.getElementById('reqModal');
            const successModal = document.getElementById('successModal');
            const modalTitle = document.getElementById('modalServiceTitle');
            const modalBody = document.getElementById('modalBodyContent');

            const sections = {
                guide: document.getElementById('sectionGuide'),
                clients: document.getElementById('sectionClients'),
                schedule: document.getElementById('sectionSchedule'),
                contact: document.getElementById('sectionContact'),
                review: document.getElementById('sectionReview'),
                confirm: document.getElementById('sectionConfirm')
            };

            function loadLocationFromLandingPage() {
                const stored = localStorage.getItem('userLocation');
                if (stored) {
                    try {
                        const locationData = JSON.parse(stored);
                        if (locationData.detected === true && locationData.lat && locationData.lng) {
                            userLocation.lat = locationData.lat;
                            userLocation.lng = locationData.lng;
                            userLocation.city = locationData.city || '';
                            userLocation.address = locationData.address || '';
                            userLocation.zipcode = locationData.zipcode || '';
                            document.getElementById('userLat').value = userLocation.lat;
                            document.getElementById('userLng').value = userLocation.lng;
                            document.getElementById('userCity').value = userLocation.city;
                            document.getElementById('userAddress').value = userLocation.address;
                            document.getElementById('userZipcode').value = userLocation.zipcode;
                            return true;
                        }
                    } catch (e) {
                        console.error('Error parsing location data:', e);
                    }
                }
                return false;
            }

            function showLoading() {
                document.getElementById('loadingOverlay').style.display = 'flex';
            }

            function hideLoading() {
                document.getElementById('loadingOverlay').style.display = 'none';
            }

            function formatDisplayDate(d) {
                if (!d) return 'No date selected';
                return new Date(d).toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            function isClientFieldsComplete(client) {
                return client.firstName && client.firstName.trim() !== '' &&
                    client.lastName && client.lastName.trim() !== '' &&
                    client.birthdate && client.birthdate !== '' &&
                    client.sex && client.sex !== '' &&
                    client.service && client.service !== '';
            }

            function updateViewRequirementsButton(clientId) {
                const client = clients.find(c => c.id == clientId);
                if (!client) return;
                const button = document.querySelector(`.btn-view-req[data-id="${clientId}"]`);
                if (button) {
                    const isComplete = isClientFieldsComplete(client);
                    if (isComplete) {
                        button.disabled = false;
                        button.style.opacity = '1';
                    } else {
                        button.disabled = true;
                        button.style.opacity = '0.6';
                    }
                    if (client.requirementsRead) button.classList.add('requirements-read');
                    else button.classList.remove('requirements-read');
                }
                updateAcknowledgmentCheckbox(clientId);
            }

            function updateAcknowledgmentCheckbox(clientId) {
                const client = clients.find(c => c.id == clientId);
                if (!client) return;
                const checkbox = document.querySelector(`.req-ack[data-id="${clientId}"]`);
                const ackRow = document.querySelector(`.req-ack-row[data-client-id="${clientId}"]`);
                if (checkbox && ackRow) {
                    const canCheck = isClientFieldsComplete(client) && client.requirementsRead === true;
                    if (canCheck) {
                        checkbox.disabled = false;
                        ackRow.classList.remove('disabled-checkbox');
                        const existingHelper = ackRow.querySelector('.helper-text');
                        if (existingHelper) existingHelper.remove();
                    } else {
                        checkbox.disabled = true;
                        ackRow.classList.add('disabled-checkbox');
                        if (!ackRow.querySelector('.helper-text')) {
                            const helperText = document.createElement('small');
                            helperText.className = 'helper-text';
                            if (!isClientFieldsComplete(client)) helperText.innerHTML =
                                '<i class="fas fa-info-circle"></i> Please fill in all required fields first.';
                            else if (!client.requirementsRead) helperText.innerHTML =
                                '<i class="fas fa-info-circle"></i> Please click "View Requirements" and read the requirements first.';
                            ackRow.appendChild(helperText);
                        }
                    }
                }
            }

            async function fetchRequirementsFromBackend(serviceCode, birthdate) {
                try {
                    let url = `{{ route('client.appointment.get-requirements') }}?service=${serviceCode}`;
                    if (birthdate) url += `&birthdate=${birthdate}`;
                    const response = await fetch(url, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    return await response.json();
                } catch (error) {
                    return {
                        success: false,
                        message: 'Failed to load requirements'
                    };
                }
            }

            async function showRequirementsModal(clientId) {
                const client = clients.find(c => c.id == clientId);
                if (!client || !isClientFieldsComplete(client)) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Information',
                        text: 'Please fill in all required fields first.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                modalTitle.textContent = 'Loading Requirements...';
                modalBody.innerHTML =
                    '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading requirements...</div>';
                reqModal.style.display = 'flex';
                currentRequirementsClientId = clientId;
                const result = await fetchRequirementsFromBackend(client.service, client.birthdate);
                if (result.success && result.html) {
                    modalTitle.textContent = `${getServiceName(client.service)} Requirements`;
                    modalBody.innerHTML = result.html;
                } else {
                    modalBody.innerHTML =
                        `<div class="error-message">${result.message || 'Failed to load requirements.'}</div>`;
                }
            }

            function renderClients() {
                const container = document.getElementById('clientsList');
                container.innerHTML = '';
                clients.forEach((c, i) => {
                    const showTrn = shouldShowTrnForClient(c);
                    const trnData = clientTrnData[c.id] || {
                        hasTrn: null,
                        trnNumber: '',
                        isValid: false
                    };
                    const isComplete = isClientFieldsComplete(c);
                    const card = document.createElement('div');
                    card.className = 'client-card';
                    card.innerHTML = `
                <div class="client-header">
                    <span class="client-title"><i class="fas fa-user-circle"></i> Person ${i + 1}</span>
                    <div style="display: flex; gap: 8px;">${clients.length > 1 ? `<button class="btn-remove-client" data-id="${c.id}"><i class="fas fa-trash-alt"></i></button>` : ''}</div>
                </div>
                <div class="identity-reminder"><i class="fas fa-id-card"></i> <strong>Important:</strong> ${identityReminders[c.service] || 'Please ensure the information is accurate.'}</div>
                <div class="form-row">
                    <div class="form-col"><label>First Name *</label><input class="client-firstname" data-id="${c.id}" value="${escapeHtml(c.firstName)}" placeholder="First Name"></div>
                    <div class="form-col"><label>Middle Name</label><input class="client-middlename" data-id="${c.id}" value="${escapeHtml(c.middleName)}" placeholder="Middle Name"></div>
                </div>
                <div class="form-row">
                    <div class="form-col"><label>Last Name *</label><input class="client-lastname" data-id="${c.id}" value="${escapeHtml(c.lastName)}" placeholder="Last Name"></div>
                    <div class="form-col"><label>Suffix</label><select class="client-suffix" data-id="${c.id}"><option value="">None</option><option value="Jr." ${c.suffix === 'Jr.' ? 'selected' : ''}>Jr.</option><option value="Sr." ${c.suffix === 'Sr.' ? 'selected' : ''}>Sr.</option><option value="I" ${c.suffix === 'I' ? 'selected' : ''}>I</option><option value="II" ${c.suffix === 'II' ? 'selected' : ''}>II</option><option value="III" ${c.suffix === 'III' ? 'selected' : ''}>III</option></select></div>
                </div>
                <div class="form-row">
                    <div class="form-col"><label>Sex *</label><select class="client-sex" data-id="${c.id}"><option value="Male" ${c.sex === 'Male' ? 'selected' : ''}>Male</option><option value="Female" ${c.sex === 'Female' ? 'selected' : ''}>Female</option></select></div>
                    <div class="form-col"><label>Birthdate *</label><input type="date" class="client-birthdate" data-id="${c.id}" value="${c.birthdate}"></div>
                </div>
                <div class="form-col"><label>Service *</label>
                    <select class="client-service" data-id="${c.id}">
                        <option value="" disabled ${c.service === '' ? 'selected' : ''}>Select Service</option>
                        <option value="reg" ${c.service === 'reg' ? 'selected' : ''}>National ID Registration</option>
                        <option value="updating" ${c.service === 'updating' ? 'selected' : ''}>Correction/Updating</option>
                        <option value="inquiry" ${c.service === 'inquiry' ? 'selected' : ''}>Status Inquiry / Retrieval Of TRN / Other Concern</option>
                    </select>
                </div>
                ${showTrn ? createTrnHtml(c.id, trnData.hasTrn, trnData.trnNumber) : ''}
                <button class="btn-view-req ${c.requirementsRead ? 'requirements-read' : ''}" data-id="${c.id}" ${!isComplete ? 'disabled' : ''}><i class="fas fa-book"></i> View Requirements</button>
                <div class="req-ack-row" data-client-id="${c.id}">
                    <input type="checkbox" class="req-ack" data-id="${c.id}" ${c.reqAcknowledged ? 'checked' : ''} ${!c.requirementsRead ? 'disabled' : ''}>
                    <label>I have read and understood the requirements for <strong>${getServiceName(c.service)}</strong>. I will bring all necessary documents.</label>
                </div>
            `;
                    container.appendChild(card);
                });
                attachClientEvents();
                document.getElementById('clientCount').textContent = clients.length;
            }

            function escapeHtml(str) {
                if (!str) return '';
                return str.replace(/[&<>]/g, function(m) {
                    if (m === '&') return '&amp;';
                    if (m === '<') return '&lt;';
                    if (m === '>') return '&gt;';
                    return m;
                });
            }

            function attachClientEvents() {
                document.querySelectorAll('.client-firstname, .client-lastname, .client-sex, .client-birthdate')
                    .forEach(e => e.addEventListener('change', (ev) => {
                        const c = clients.find(x => x.id == ev.target.dataset.id);
                        if (c) {
                            if (ev.target.classList.contains('client-firstname')) c.firstName = ev.target.value;
                            if (ev.target.classList.contains('client-lastname')) c.lastName = ev.target.value;
                            if (ev.target.classList.contains('client-sex')) c.sex = ev.target.value;
                            if (ev.target.classList.contains('client-birthdate')) c.birthdate = ev.target.value;
                            updateViewRequirementsButton(c.id);
                        }
                    }));
                document.querySelectorAll('.client-middlename').forEach(e => e.addEventListener('change', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) c.middleName = ev.target.value;
                }));
                document.querySelectorAll('.client-suffix').forEach(e => e.addEventListener('change', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) c.suffix = ev.target.value;
                }));
                document.querySelectorAll('.client-service').forEach(e => e.addEventListener('change', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) {
                        c.service = ev.target.value;
                        c.reqAcknowledged = false;
                        c.requirementsRead = false;
                        if (c.service !== 'inquiry') delete clientTrnData[c.id];
                        else if (!clientTrnData[c.id]) clientTrnData[c.id] = {
                            hasTrn: null,
                            trnNumber: '',
                            isValid: false
                        };
                        renderClients();
                    }
                }));
                document.querySelectorAll('.req-ack').forEach(e => e.addEventListener('change', async (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c && ev.target.checked && (!isClientFieldsComplete(c) || !c.requirementsRead)) {
                        ev.target.checked = false;
                        if (!isClientFieldsComplete(c)) {
                            await Swal.fire({
                                icon: 'warning',
                                title: 'Cannot Acknowledge',
                                text: 'Please fill in all required fields first.',
                                confirmButtonColor: '#dc3545'
                            });
                        } else if (!c.requirementsRead) {
                            await Swal.fire({
                                icon: 'info',
                                title: 'Requirements Not Read',
                                text: 'Please click "View Requirements" first.',
                                confirmButtonColor: '#28a745',
                                confirmButtonText: 'View Requirements'
                            }).then((result) => {
                                if (result.isConfirmed) document.querySelector(
                                    `.btn-view-req[data-id="${c.id}"]`).click();
                            });
                        }
                        return;
                    }
                    if (c) c.reqAcknowledged = ev.target.checked;
                }));
                document.querySelectorAll('.btn-view-req').forEach(b => b.addEventListener('click', async (ev) => {
                    const id = ev.target.closest('.btn-view-req').dataset.id;
                    await showRequirementsModal(parseInt(id));
                }));
                document.querySelectorAll('.btn-remove-client').forEach(b => b.addEventListener('click', (ev) => {
                    const id = parseInt(ev.target.closest('.btn-remove-client').dataset.id);
                    clients = clients.filter(c => c.id !== id);
                    delete clientTrnData[id];
                    delete clientTimeSlots[id];
                    renderClients();
                }));
                clients.forEach(client => {
                    if (shouldShowTrnForClient(client)) attachTrnEvents(client.id);
                });
            }

            function setActiveStep(s) {
                document.querySelectorAll('.step').forEach((e, i) => e.classList.toggle('active', i + 1 === s));
            }

            function showSection(s) {
                Object.values(sections).forEach(x => x.classList.add('hidden'));
                s.classList.remove('hidden');
            }

            // EVENT LISTENERS
            document.getElementById('agreePrivacyBtn').onclick = () => privacyModal.style.display = 'none';
            document.getElementById('startBookingBtn').onclick = () => {
                showSection(sections.clients);
                setActiveStep(2);
            };
            document.getElementById('addClientBtn').onclick = () => {
                if (clients.length >= MAX_CLIENTS) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Maximum Reached',
                        text: `Maximum ${MAX_CLIENTS} persons only.`,
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                clients.push({
                    id: nextClientId++,
                    firstName: '',
                    middleName: '',
                    lastName: '',
                    suffix: '',
                    sex: 'Male',
                    birthdate: '',
                    service: '',
                    reqAcknowledged: false,
                    requirementsRead: false
                });
                renderClients();
            };

            document.getElementById('nextToSchedule').onclick = () => {
                for (let c of clients) {
                    if (!c.firstName?.trim() || !c.lastName?.trim()) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing Information',
                            text: 'First Name and Last Name are required.',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }
                    if (!c.sex || !c.birthdate || !c.service) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing Information',
                            text: 'Please fill in all fields.',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }
                    if (!validateTrnForClient(c)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing TRN',
                            text: `${getFullName(c)}: Please indicate TRN status.`,
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }
                    if (!c.reqAcknowledged) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Requirements Not Acknowledged',
                            text: `Please acknowledge requirements for ${getFullName(c)}.`,
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }
                }
                selectedDate = null;
                clientTimeSlots = {};
                availableTimeSlotsCache = [];
                loadAvailableDates();
                showSection(sections.schedule);
                setActiveStep(3);
                document.getElementById('perApplicantTimeSlotsContainer').innerHTML =
                    '<div class="slot-info"><i class="fas fa-calendar-alt"></i> Please select a date first</div>';
            };

            document.getElementById('backToClients').onclick = () => {
                showSection(sections.clients);
                setActiveStep(2);
            };
            document.getElementById('prevMonthBtn').onclick = () => {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                loadAvailableDates();
            };
            document.getElementById('nextMonthBtn').onclick = () => {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                loadAvailableDates();
            };

            document.getElementById('nextToContact').onclick = () => {
                if (!selectedDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Date Selected',
                        text: 'Please select a date.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                const missing = clients.filter(c => !clientTimeSlots[c.id]?.slotId);
                if (missing.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Time Slots',
                        text: `Please select time slots for: ${missing.map(c => getFullName(c)).join(', ')}`,
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                showSection(sections.contact);
                setActiveStep(4);
            };

            document.getElementById('backToScheduleFromContact').onclick = () => {
                showSection(sections.schedule);
                setActiveStep(3);
                renderPerApplicantTimeSlots();
            };

            document.getElementById('nextToReview').onclick = () => {
                const name = document.getElementById('contactName').value;
                const mobile = document.getElementById('contactMobile').value;
                if (!name) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Contact',
                        text: 'Please enter a contact name.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                if (mobile && (mobile.length !== 10 || !mobile.startsWith('9'))) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Contact',
                        text: 'Please enter a valid mobile number or leave it blank.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }
                document.getElementById('reviewClientCount').textContent = clients.length;
                let clientsHtml = '';
                clients.forEach((c, i) => {
                    const slot = clientTimeSlots[c.id];
                    clientsHtml +=
                        `<div><strong>${i+1}. ${escapeHtml(getFullName(c))}</strong> - ${getServiceName(c.service)}<br><small>Time: ${slot?.slotLabel || 'Not selected'}</small></div>`;
                });
                document.getElementById('reviewClientsList').innerHTML = clientsHtml;
                document.getElementById('reviewDateTime').innerHTML = formatDisplayDate(selectedDate);
                document.getElementById('reviewContactName').textContent = escapeHtml(name);
                document.getElementById('reviewContactEmail').textContent = document.getElementById('contactEmail')
                    .value || 'Not provided';
                document.getElementById('reviewContactMobile').textContent = mobile ? '+63' + mobile : 'Not provided';
                showSection(sections.review);
                setActiveStep(5);
            };

            document.getElementById('backToContact').onclick = () => {
                showSection(sections.contact);
                setActiveStep(4);
            };
            document.getElementById('nextToConfirm').onclick = () => {
    const mobile = document.getElementById('contactMobile').value;
    const name = document.getElementById('contactName').value;
    const email = document.getElementById('contactEmail').value;

    // Update applicants list
    document.getElementById('sumClients').textContent = clients.length + ' applicant(s)';
    let applicantsHtml = '';
    clients.forEach((c, index) => {
        const slot = clientTimeSlots[c.id];
        applicantsHtml += `
            <div style="padding: 12px; border-bottom: 1px solid var(--gray-200); ${index === clients.length - 1 ? 'border-bottom: none;' : ''}">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <strong style="font-size: 1rem;">${escapeHtml(getFullName(c))}</strong>
                        <span style="display: inline-block; background: var(--primary); color: black; padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; margin-left: 8px;">${getServiceName(c.service)}</span>
                    </div>
                    <div style="color: var(--success);">
                        <i class="fas fa-clock"></i> ${escapeHtml(slot?.slotLabel || 'Not selected')}
                    </div>
                </div>
                <div style="font-size: 0.8rem; color: var(--gray-500); margin-top: 5px;">
                    <i class="fas fa-birthday-cake"></i> Birthdate: ${c.birthdate || 'Not provided'} |
                    <i class="fas fa-venus-mars"></i> Sex: ${c.sex || 'Not selected'}
                </div>
            </div>
        `;
    });
    document.getElementById('sumApplicantsList').innerHTML = applicantsHtml || '<div>No applicants added</div>';

    // Update schedule
    let timeSummary = `<div style="margin-bottom: 5px;"><strong>Date:</strong> ${formatDisplayDate(selectedDate)}</div>`;
    timeSummary += `<div><strong>Individual Time Slots:</strong></div>`;
    clients.forEach(c => {
        const slot = clientTimeSlots[c.id];
        timeSummary += `<div style="margin-left: 15px; margin-top: 5px;">• ${escapeHtml(getFullName(c))}: <strong>${escapeHtml(slot?.slotLabel || 'Not selected')}</strong></div>`;
    });
    document.getElementById('sumDateTime').innerHTML = timeSummary;

    // Update contact
    let contactHtml = `
        <div><strong>Name:</strong> ${escapeHtml(name)}</div>
        <div style="margin-top: 8px;"><strong>Mobile:</strong> ${mobile ? '+63' + mobile : 'Not provided'}</div>
        ${email ? `<div style="margin-top: 8px;"><strong>Email:</strong> ${escapeHtml(email)}</div>` : ''}
    `;
    document.getElementById('sumContact').innerHTML = contactHtml;

    // Update location if available
    // const userCity = document.getElementById('userCity').value;
    // const userAddress = document.getElementById('userAddress').value;
    // if (userCity || userAddress) {
    //     let locationHtml = '';
    //     if (userAddress) locationHtml += `<div><strong>Address:</strong> ${escapeHtml(userAddress)}</div>`;
    //     if (userCity) locationHtml += `<div style="margin-top: 8px;"><strong>City:</strong> ${escapeHtml(userCity)}</div>`;
    //     document.getElementById('sumLocation').innerHTML = locationHtml;
    //     document.getElementById('sumLocationContainer').style.display = 'block';
    // } else {
    //     document.getElementById('sumLocationContainer').style.display = 'none';
    // }

    showSection(sections.confirm);
    setActiveStep(6);
};

            document.getElementById('backToReview').onclick = () => {
                showSection(sections.review);
                setActiveStep(5);
            };

            document.getElementById('confirmCheckbox').addEventListener('change', function(e) {
                document.getElementById('submitRequestBtn').disabled = !e.target.checked;
            });

            document.getElementById('closeReqModal').onclick = () => {
                reqModal.style.display = 'none';
                currentRequirementsClientId = null;
            };
            document.getElementById('understandBtn').onclick = () => {
                if (currentRequirementsClientId) {
                    const client = clients.find(c => c.id === currentRequirementsClientId);
                    if (client) {
                        client.requirementsRead = true;
                        renderClients();
                        Swal.fire({
                            icon: 'success',
                            title: 'Requirements Reviewed',
                            text: 'You may now check the acknowledgment box.',
                            confirmButtonColor: '#28a745',
                            timer: 3000
                        });
                    }
                    currentRequirementsClientId = null;
                }
                reqModal.style.display = 'none';
            };

            document.getElementById('closeSuccessModal').onclick = () => {
    successModal.style.display = 'none';
    // Close the appointment modal if it exists in parent window
    if (window.parent && window.parent.closeAppointmentModal) {
        window.parent.closeAppointmentModal();
    } else if (window.parent) {
        // Try to close via parent window's modal
        const parentModal = window.parent.document.getElementById('appointmentModal');
        if (parentModal) {
            parentModal.classList.remove('active');
            window.parent.document.body.classList.remove('modal-open');
        }
    }
    // Or just reset the iframe
    const iframe = document.getElementById('appointmentIframe');
    if (iframe && iframe.parentElement) {
        // If in iframe, let parent handle closing
    }
};

            document.querySelectorAll('[data-edit]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const target = e.target.closest('[data-edit]').dataset.edit;
                    if (target === 'clients') {
                        showSection(sections.clients);
                        setActiveStep(2);
                    } else if (target === 'schedule') {
                        showSection(sections.schedule);
                        setActiveStep(3);
                        renderPerApplicantTimeSlots();
                    } else if (target === 'contact') {
                        showSection(sections.contact);
                        setActiveStep(4);
                    }
                });
            });

            renderClients();
            loadLocationFromLandingPage();

            document.getElementById('submitRequestBtn').onclick = async () => {
                const mobile = document.getElementById('contactMobile').value;
                if (mobile && (mobile.length !== 10 || !mobile.startsWith('9'))) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Mobile',
                        text: 'Please enter a valid mobile number or leave it blank.',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                const clientsData = clients.map(c => {
                    const timeSlot = clientTimeSlots[c.id];
                    return {
                        first_name: c.firstName,
                        middle_name: c.middleName || null,
                        last_name: c.lastName,
                        suffix: c.suffix || null,
                        sex: c.sex,
                        birthdate: c.birthdate,
                        service: c.service,
                        time_slot_id: timeSlot ? timeSlot.slotId : null,
                        has_trn: clientTrnData[c.id]?.hasTrn || null,
                        trn_number: clientTrnData[c.id]?.hasTrn && clientTrnData[c.id]?.isValid ?
                            clientTrnData[c.id]?.trnNumber : null
                    };
                });

                // Debug: Log location values before sending
                console.log('📍 Location values being submitted:', {
                    user_lat: document.getElementById('userLat').value,
                    user_lng: document.getElementById('userLng').value,
                    user_city: document.getElementById('userCity').value,
                    user_address: document.getElementById('userAddress').value,
                    user_zipcode: document.getElementById('userZipcode').value
                });

                showLoading();
                try {
                    const response = await fetch('{{ route('client.appointment.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            appointment_date: selectedDate,
                            contact_name: document.getElementById('contactName').value,
                            contact_email: document.getElementById('contactEmail').value ||
                                null,
                            contact_mobile: '+63' + mobile,
                            clients: clientsData,
                            user_lat: document.getElementById('userLat').value || null,
                            user_lng: document.getElementById('userLng').value || null,
                            user_city: document.getElementById('userCity').value || null,
                            user_address: document.getElementById('userAddress').value || null,
                            user_zipcode: document.getElementById('userZipcode').value || null
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        // Store appointment data globally for download functions
                        window.globalAppointmentData = {
                            appointment_number: result.appointment.number,
                            reference_code: result.appointment.reference_code,
                            date: result.appointment.date,
                            contact_name: result.appointment.contact_name,
                            contact_mobile: result.appointment.contact_mobile,
                            contact_email: result.appointment.contact_email,
                            clients_list: result.appointment.clients_list || []
                        };

                        // Build clients HTML for display
                        let clientsHtml = '';
                        if (window.globalAppointmentData.clients_list && window.globalAppointmentData
                            .clients_list.length > 0) {
                            clientsHtml =
                                '<div style="text-align: left; margin-top: 10px;"><strong>Applicant Details:</strong><ul>';
                            window.globalAppointmentData.clients_list.forEach(client => {
                                clientsHtml +=
                                    `<li><strong>${escapeHtml(client.name)}</strong><br><small>Service: ${client.service_name}</small><br><small>Time: ${client.time_slot}</small></li>`;
                            });
                            clientsHtml += '</ul></div>';
                        }

document.getElementById('successDetails').innerHTML = `
    <div style="text-align: left;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid var(--success);">
            <i class="fas fa-check-circle" style="font-size: 48px; color: var(--success); margin-bottom: 10px;"></i>
            <h3 style="color: var(--success); margin: 0;">Appointment Successfully Booked!</h3>
            <p style="color: var(--gray-500); margin: 5px 0 0 0;">Please save the information below for your records</p>
        </div>

        <!-- Appointment Reference -->
<div style="background: #f0fdf4; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
    <div style="margin-bottom: 12px;">
        <div style="font-size: 0.85rem; color: var(--gray-600); margin-bottom: 4px;"><strong>Appointment Number</strong></div>
        <div style="font-size: 1.2rem; font-weight: 700; color: var(--gray-800); word-break: break-all;">${result.appointment.number}</div>
    </div>
    <div>
        <div style="font-size: 0.85rem; color: var(--gray-600); margin-bottom: 4px;"><strong>Reference Code</strong></div>
        <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary); word-break: break-all;">${result.appointment.reference_code}</div>
    </div>
</div>

        <!-- Date & Time -->
        <div style="background: #f8fafc; padding: 12px; border-radius: 10px; margin-bottom: 15px;">
            <p style="margin: 0;"><strong><i class="far fa-calendar-alt"></i> Date:</strong> ${result.appointment.date}</p>
        </div>

        <!-- Applicants List -->
<div style="margin-bottom: 15px;">
    <p style="margin: 0 0 10px 0; font-weight: 600;"><i class="fas fa-users"></i> Applicants (${result.appointment.clients_count})</p>
    <div style="max-height: 300px; overflow-y: auto;">
        ${result.appointment.clients_list.map(client => `
            <div style="background: #f8fafc; padding: 12px; border-radius: 8px; margin-bottom: 10px; border-left: 3px solid var(--primary);">
                <div style="font-weight: 600; margin-bottom: 8px;">${escapeHtml(client.name)}</div>
                <div style="margin-bottom: 6px;">
                    <span style="background: var(--primary); color: black; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; display: inline-block;">${client.service_name}</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--gray-700); margin-bottom: 4px;">
                    <i class="fas fa-id-card" style="width: 20px;"></i> Client #: ${client.client_number}
                </div>
                <div style="font-size: 0.8rem; color: var(--gray-700);">
                    <i class="fas fa-clock" style="width: 20px;"></i> Time: ${client.time_slot}
                </div>
            </div>
        `).join('')}
    </div>
</div>

        <!-- Contact Information -->
        <div style="background: #f8fafc; padding: 12px; border-radius: 10px; margin-bottom: 15px;">
            <p style="margin: 0 0 8px 0; font-weight: 600;"><i class="fas fa-address-book"></i> Contact Information</p>
            <p style="margin: 0;"><strong>Name:</strong> ${escapeHtml(result.appointment.contact_name)}</p>
            <p style="margin: 5px 0 0 0;"><strong>Mobile:</strong> ${result.appointment.contact_mobile}</p>
            ${result.appointment.contact_email ? `<p style="margin: 5px 0 0 0;"><strong>Email:</strong> ${escapeHtml(result.appointment.contact_email)}</p>` : ''}
        </div>

        <!-- Office Address -->
        <div style="background: #e3f2fd; padding: 12px; border-radius: 10px; margin-bottom: 15px; text-align: center;">
            <p style="margin: 0; font-size: 0.85rem;">
                <strong><i class="fas fa-map-marker-alt"></i> PSA Misamis Oriental Office</strong><br>
                Capt. Vicente Roa Street, Brgy. 31,<br>
                Cagayan de Oro City, 9000 Misamis Oriental
            </p>
        </div>

        <!-- Reminder -->
        <div style="background: #fef3c7; padding: 12px; border-radius: 10px; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; font-size: 0.85rem; color: #92400e;">
                <strong><i class="fas fa-clock"></i> Important Reminder:</strong><br>
                Please arrive at least <strong>30 minutes before</strong> your scheduled appointment time.<br>
                Bring all required documents for verification.
            </p>
        </div>

        <hr style="margin: 15px 0;">
        <p style="text-align: center; color: #dc3545; font-size: 0.85rem; margin: 0;">
            ⚠️ Please save your Reference Code for verification.
        </p>
    </div>
`;
                        successModal.style.display = 'flex';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Submission failed: ' + (error.message || 'Unknown error'),
                        confirmButtonColor: '#dc3545'
                    });
                } finally {
                    hideLoading();
                }
            };
        })();

        // DOWNLOAD FUNCTIONS
        // ==================== DOWNLOAD FUNCTIONS - MULTI-RECEIPT ====================

        // Local escape function for downloads (self-contained)
        function downloadEscapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        async function captureClientReceipt(client, appointmentData) {
            const receiptContainer = document.createElement('div');
            receiptContainer.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 12px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 500px;
        margin: 0 auto;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        page-break-after: always;
        break-inside: avoid;
    `;

            const now = new Date();
            const formattedDateTime = now.toLocaleString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            receiptContainer.innerHTML = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #2c5f8a; margin: 0; font-size: 1.3rem;">Philippine Statistics Authority</h2>
            <h3 style="color: #4a5568; margin: 5px 0; font-size: 1rem;">PhilSys Appointment Management System</h3>
            <p style="color: #718096; margin: 5px 0; font-size: 0.85rem;">Applicant Confirmation Slip</p>
        </div>

        <div style="border-top: 2px solid #2c5f8a; margin: 10px 0;"></div>

        <div style="padding: 10px 0;">
            <p style="margin: 8px 0;"><strong>Applicant Name:</strong> ${downloadEscapeHtml(client.name)}</p>
            <p style="margin: 8px 0;"><strong>Service:</strong> ${downloadEscapeHtml(client.service_name || client.service)}</p>
            <p style="margin: 8px 0;"><strong>Appointment Number:</strong> ${downloadEscapeHtml(appointmentData.appointment_number)}</p>
            <p style="margin: 8px 0;"><strong>Reference Code:</strong> ${downloadEscapeHtml(appointmentData.reference_code)}</p>
            <p style="margin: 8px 0;"><strong>Date:</strong> ${downloadEscapeHtml(appointmentData.date)}</p>
            <p style="margin: 8px 0;"><strong>Time Slot:</strong> ${downloadEscapeHtml(client.time_slot || 'Time slot selected')}</p>
            <p style="margin: 8px 0;"><strong>Contact Person:</strong> ${downloadEscapeHtml(appointmentData.contact_name)}</p>
            <p style="margin: 8px 0;"><strong>Contact Number:</strong> ${downloadEscapeHtml(appointmentData.contact_mobile)}</p>
            ${appointmentData.contact_email ? `<p style="margin: 8px 0;"><strong>Email:</strong> ${downloadEscapeHtml(appointmentData.contact_email)}</p>` : ''}
        </div>

        <div style="border-top: 1px solid #e2e8f0; margin: 10px 0;"></div>

        <div style="text-align: center; margin: 15px 0;">
            <div style="display: inline-block; padding: 10px; background: white; border: 1px solid #e2e8f0; border-radius: 8px;">
                <svg width="100" height="100" viewBox="0 0 100 100" style="margin: 0 auto;">
                    <rect width="100" height="100" fill="white"/>
                    <text x="50" y="50" text-anchor="middle" dominant-baseline="middle" font-size="8" fill="black">${downloadEscapeHtml(appointmentData.reference_code)}</text>
                </svg>
            </div>
        </div>

        <div style="margin-top: 15px; padding: 12px; background: #f8fafc; border-radius: 8px; text-align: center;">
            <p style="margin: 0; font-size: 0.8rem; color: #475569;">
                <strong><i class="fas fa-map-marker-alt"></i> PSA Misamis Oriental Office</strong><br>
                Capt. Vicente Roa Street, Brgy. 31, Cagayan de Oro City,<br>
                9000 Misamis Oriental, Philippines
            </p>
        </div>

        <div style="margin-top: 15px; padding: 12px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
            <p style="margin: 0; font-size: 0.8rem; color: #92400e;">
                <strong><i class="fas fa-clock"></i> Important Reminder:</strong><br>
                Please arrive at least <strong>30 minutes before</strong> your scheduled appointment time.
            </p>
        </div>

        <div style="border-top: 1px solid #e2e8f0; margin: 15px 0 10px 0;"></div>

        <div style="text-align: center; padding-top: 10px;">
            <p style="color: #718096; font-size: 10px; margin: 5px 0;">This is a system-generated confirmation for individual applicant.</p>
            <p style="color: #718096; font-size: 10px; margin: 5px 0;">Generated on: ${formattedDateTime}</p>
            <p style="color: #718096; font-size: 10px; margin: 5px 0;">© ${new Date().getFullYear()} Philippine Statistics Authority</p>
        </div>
    `;

            return receiptContainer;
        }

        async function generateAllClientReceipts() {
            const appointmentData = window.globalAppointmentData;
            if (!appointmentData || !appointmentData.clients_list || appointmentData.clients_list.length === 0) {
                return [];
            }

            const receipts = [];
            for (let i = 0; i < appointmentData.clients_list.length; i++) {
                const client = appointmentData.clients_list[i];
                const receipt = await captureClientReceipt(client, appointmentData);
                receipts.push(receipt);
            }
            return receipts;
        }


        // PNG Download - Multiple images
        document.getElementById('downloadPngBtn').onclick = async () => {
            try {
                const btn = document.getElementById('downloadPngBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PNGs...';
                btn.disabled = true;

                if (typeof html2canvas === 'undefined') {
                    await new Promise((resolve) => {
                        const script = document.createElement('script');
                        script.src =
                            'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                        script.onload = resolve;
                        document.head.appendChild(script);
                    });
                }

                const receipts = await generateAllClientReceipts();

                if (receipts.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: 'No appointment data found. Please submit an appointment first.',
                        confirmButtonColor: '#dc3545'
                    });
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }

                for (let i = 0; i < receipts.length; i++) {
                    const receipt = receipts[i];
                    document.body.appendChild(receipt);

                    const canvas = await html2canvas(receipt, {
                        scale: 2,
                        backgroundColor: '#ffffff',
                        logging: false,
                        useCORS: true
                    });

                    document.body.removeChild(receipt);

                    const link = document.createElement('a');
                    const clientName = (window.globalAppointmentData.clients_list[i].name || 'applicant').replace(
                        /\s/g, '_');
                    link.download = `appointment-${clientName}-${Date.now()}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();

                    await new Promise(resolve => setTimeout(resolve, 500));
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Download Complete',
                    text: `${receipts.length} receipt(s) downloaded successfully.`,
                    confirmButtonColor: '#28a745',
                    timer: 2000
                });

                btn.innerHTML = originalText;
                btn.disabled = false;
            } catch (error) {
                console.error('PNG download error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Download Failed',
                    text: 'Failed to generate PNG receipts. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
                const btn = document.getElementById('downloadPngBtn');
                btn.innerHTML = '<i class="fas fa-image"></i> Download PNG';
                btn.disabled = false;
            }
        };



        // PDF Download - request server to render the authoritative Blade PDF and download
        document.getElementById('downloadPdfBtn').onclick = async () => {
            try {
                const btn = document.getElementById('downloadPdfBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                btn.disabled = true;

                const appointment = window.globalAppointmentData || {};
                const clients = appointment.clients_list || [];

                if (!appointment || clients.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: 'No appointment data found. Please submit an appointment first.',
                        confirmButtonColor: '#dc3545'
                    });
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }

                const url = "{{ route('client.appointment.pdf') }}";

                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ appointment: appointment, clients: clients })
                });

                if (!resp.ok) throw new Error('PDF generation failed');

                const blob = await resp.blob();
                let filename = 'appointment.pdf';
                const cd = resp.headers.get('content-disposition');
                if (cd && cd.indexOf('filename=') !== -1) {
                    filename = cd.split('filename=')[1].replace(/['"]/g, '').trim();
                }

                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(link.href);

                Swal.fire({
                    icon: 'success',
                    title: 'Download Complete',
                    text: `PDF downloaded successfully.`,
                    confirmButtonColor: '#28a745',
                    timer: 2000
                });

                btn.innerHTML = originalText;
                btn.disabled = false;
            } catch (error) {
                console.error('PDF download error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Download Failed',
                    text: 'Failed to generate PDF. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
                const btn = document.getElementById('downloadPdfBtn');
                btn.innerHTML = '<i class="fas fa-file-pdf"></i> Download PDF';
                btn.disabled = false;
            }
        };
    </script>
</body>

</html>