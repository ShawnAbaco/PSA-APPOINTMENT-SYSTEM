{{-- resources/views/client/appointment.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PhilSys · National ID Appointment</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/appointment.css') }}">
    <style>
        /* Location banner styles */
        .location-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }

        .location-banner i {
            font-size: 20px;
            margin-right: 10px;
        }

        .location-banner .location-info {
            flex: 1;
        }

        .location-banner .location-city {
            font-weight: bold;
            font-size: 14px;
        }

        .location-banner .location-address {
            font-size: 11px;
            opacity: 0.9;
        }

        .btn-primary {
            background: #2c5f8a;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .hidden {
            display: none !important;
        }

        /* TRN Field Styles */
        .trn-field-group {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #2c5f8a;
        }

        .trn-question {
            font-weight: 600;
            margin-bottom: 16px;
            color: #2c5f8a;
            font-size: 16px;
        }

        .trn-checkbox-group {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }

        .trn-checkbox-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 15px;
        }

        .trn-input-group {
            margin-top: 16px;
        }

        .trn-input-group label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .trn-input-group input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: monospace;
            letter-spacing: 1px;
        }

        .trn-input-group input:focus {
            outline: none;
            border-color: #2c5f8a;
        }

        .trn-input-group input.trn-valid {
            border-color: #28a745;
            background-color: #f0fff4;
        }

        .trn-input-group input.trn-invalid {
            border-color: #dc3545;
            background-color: #fff5f5;
        }

        .trn-char-counter {
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .trn-char-counter.valid {
            color: #28a745;
        }

        .trn-char-counter.invalid {
            color: #dc3545;
        }

        .qr-scan-area {
            margin-top: 16px;
            padding: 16px;
            background: white;
            border-radius: 8px;
            border: 1px dashed #2c5f8a;
            text-align: center;
        }

        .qr-scan-btn {
            background: #2c5f8a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }

        .qr-reader-container {
            margin-top: 15px;
            position: relative;
        }

        #qr-reader {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }

        /* Calendar service badge styles */
        .service-badge {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: bold;
            padding: 2px 5px;
            margin: 1px;
            border-radius: 4px;
        }

        .service-badge.available {
            background: #28a745;
            color: white;
        }

        .service-badge.full {
            background: #dc3545;
            color: white;
        }

        .slot-indicators {
            display: flex;
            justify-content: center;
            gap: 5px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        /* Time Slot Styles */
        .time-slots-container {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
        }

        .time-slots-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c5f8a;
            font-size: 16px;
        }

        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }

        .time-slot-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .time-slot-card:hover:not(.disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #2c5f8a;
        }

        .time-slot-card.selected {
            border-color: #28a745;
            background: #e8f5e9;
        }

        .time-slot-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f5f5f5;
        }

        .time-slot-time {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .time-slot-availability {
            font-size: 12px;
            color: #666;
        }

        .time-slot-availability.available {
            color: #28a745;
        }

        .time-slot-availability.limited {
            color: #ff9800;
        }

        .time-slot-availability.full {
            color: #dc3545;
        }

        .time-slots-loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>

<body>
    <!-- PRIVACY NOTICE MODAL -->
    <div class="privacy-overlay" id="privacyModal">
        <div class="privacy-modal">
            <h2>Privacy Notice</h2>
            <p>This system collects and processes limited personal information solely for the purpose of scheduling,
                managing, and confirming National ID System appointments, in accordance with the <span
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
    <div class="modal-overlay" id="reqModal">
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
    <div class="modal-overlay" id="successModal">
        <div class="modal-content" style="max-width: 600px; text-align: center;">
            <div class="modal-header">
                <h3 style="color: var(--success);"><i class="fas fa-check-circle"></i> Appointment Confirmed!</h3>
                <span class="close-modal" id="closeSuccessModal">&times;</span>
            </div>
            <div id="successBody" style="padding: 20px;">
                <p>Your appointment has been successfully booked.</p>
                <div id="successDetails"></div>
                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                    <button class="btn-next" id="printSummaryBtn" style="background: #6c757d;">
                        <i class="fas fa-print"></i> Print
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
            <div class="spinner"
                style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #2c5f8a; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 15px;">
            </div>
            <p>Processing your appointment...</p>
        </div>
    </div>

    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2c5f8a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Calendar styles - Clean & Simple */
        .calendar-container {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.85rem;
            color: #666;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }

        .calendar-day {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px 6px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            border: 1px solid transparent;
            position: relative;
        }

        .calendar-day.available {
            background: #e8f5e9;
        }

        .calendar-day.available:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-color: #4caf50;
        }

        .calendar-day.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: #f5f5f5;
        }

        .calendar-day.selected {
            border: 2px solid #2c5f8a;
            background: #e3f2fd;
        }

        .day-number {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .slot-count {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 12px;
            display: inline-block;
        }

        .slot-count.available {
            background: #4caf50;
            color: white;
        }

        .slot-count.full {
            background: #f44336;
            color: white;
        }

        @media (max-width: 768px) {
            .calendar-day {
                padding: 6px 3px;
            }

            .day-number {
                font-size: 0.9rem;
            }

            .service-badge {
                font-size: 0.55rem;
                padding: 1px 3px;
            }

            .slot-count {
                font-size: 0.6rem;
            }

            .time-slots-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            }
        }
    </style>

    <!-- MAIN APPOINTMENT CARD -->
    <div class="appointment-card">
        <div class="card-header">
            <div class="logos">
                <div class="logo-placeholder"><i class="fas fa-shield-alt"></i> PSA</div>
                <span class="logo-sep">|</span>
                <div class="logo-placeholder"><i class="fas fa-id-card"></i> PhilSys</div>
            </div>
            <div class="header-title">National ID System</div>
            <div class="center-name"><i class="fas fa-map-pin"></i> PSA CDO - Fixed Registration Center</div>
        </div>

        <div class="stepper">
            <div class="step active" id="step1"><span class="step-num">1</span> Type</div>
            <div class="step" id="step2"><span class="step-num">2</span> Clients</div>
            <div class="step" id="step3"><span class="step-num">3</span> Schedule</div>
            <div class="step" id="step4"><span class="step-num">4</span> Contact</div>
            <div class="step" id="step5"><span class="step-num">5</span> Review</div>
            <div class="step" id="step6"><span class="step-num">6</span> Confirm</div>
        </div>

        <div class="content-body">
            <div class="booking-note">
                <span><i class="far fa-calendar-alt"></i> Appointment Scheduling</span>
                <span><strong>Book your appointment now</strong></span>
            </div>

            <!-- STEP 1: TYPE -->
            <div id="sectionType">
                <div class="section-title">Select Appointment Type</div>
                <div class="appointment-type-selector">
                    <div class="type-option selected" id="typeSingle">
                        <i class="fas fa-user"></i>
                        <span>Single Appointment</span>
                        <small>One person, one service</small>
                    </div>
                    <div class="type-option" id="typeMultiple">
                        <i class="fas fa-users"></i>
                        <span>Family / Group</span>
                        <small>2-10 persons, any services</small>
                    </div>
                </div>
                <div id="singleServiceSection">
                    <label>Select Service <span style="color: var(--danger);">*</span></label>
                    <select id="singleServiceSelect">
                        <option value="">-- Select Service --</option>
                        @foreach ($services as $service)
                            @if($service->code !== 'ephilid' && $service->code !== 'trn')
                                <option value="{{ $service->code }}">{{ $service->name }}</option>
                            @endif
                        @endforeach
                        <option value="status_inquiry">Status Inquiry / Retrieval Of TRN / Other Concern</option>
                    </select>
                    <div id="singleServiceDesc" style="margin-top: 16px; color: var(--gray-500);">
                        <p><i class="fas fa-info-circle"></i> Select a service to view details.</p>
                    </div>
                    <button class="btn-requirements" id="showReqBtnSingle" style="display:none; margin-top: 8px;">
                        <i class="fas fa-book"></i> View Requirements
                    </button>
                </div>
                <button class="btn-next" id="nextToClients">Next Step <i class="fas fa-arrow-right"></i></button>
            </div>

            <!-- STEP 2: CLIENTS -->
            <div id="sectionClients" class="hidden">
                <div class="section-title"><span id="clientsTitle">Family / Group Members</span></div>
                <p style="color: var(--gray-500); margin-bottom: 20px;"><span id="clientsSubtitle">Add all persons who
                        will attend.</span></p>

                <div class="req-summary-banner" id="reqSummaryBanner">
                    <strong><i class="fas fa-clipboard-list"></i> Requirements Status</strong>
                    <div id="reqSummaryList"></div>
                </div>

                <div class="clients-list" id="clientsList"></div>

                <button class="btn-add-client" id="addClientBtn">
                    <i class="fas fa-plus-circle"></i> Add Another Person
                </button>

                <div id="clientCountContainer"
                    style="margin-top: 20px; padding: 14px; background: var(--gray-50); border-radius: var(--radius); border: 1px solid var(--gray-200);">
                    <p style="display: flex; justify-content: space-between;">
                        <span><i class="fas fa-users" style="color: var(--primary);"></i> <strong>Total
                                persons:</strong></span>
                        <span style="font-weight: 700; color: var(--primary);"><span id="clientCount">1</span> /
                            10</span>
                    </p>
                </div>

                <button class="btn-next" id="nextToSchedule">Next: Select Date <i
                        class="fas fa-arrow-right"></i></button>
                <button class="btn-next back-btn" id="backToTypeFromClients"><i class="fas fa-arrow-left"></i>
                    Back</button>
            </div>

            <!-- STEP 3: SCHEDULE -->
            <div id="sectionSchedule" class="hidden">
                <div class="section-title">Select Appointment Date & Time</div>

                <div class="slot-info" id="slotInfo">
                    <i class="fas fa-calendar-check" style="color: var(--primary);"></i>
                    <span id="slotInfoText">Select an available date</span>
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

                <!-- Time Slots Container -->
                <div id="timeSlotsContainer" class="time-slots-container" style="display: none;">
                    <div class="time-slots-title">
                        <i class="fas fa-clock"></i> Select Preferred Time Slot
                    </div>
                    <div id="timeSlotsGrid" class="time-slots-grid">
                        <div class="time-slots-loading">Please select a date first</div>
                    </div>
                </div>

                <button class="btn-next" id="nextToContact">Next: Contact Info <i
                        class="fas fa-arrow-right"></i></button>
                <button class="btn-next back-btn" id="backToClients"><i class="fas fa-arrow-left"></i> Back</button>
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
                    <label>Mobile Number <span style="color: var(--danger);">*</span></label>
                    <input type="tel" id="contactMobile" placeholder="09XXXXXXXXX">
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
                            <span class="review-section-title"><i class="fas fa-tag"
                                    style="color: var(--primary);"></i> Type</span>
                            <button class="edit-link" data-edit="type" style="color: var(--secondary);"><i
                                    class="fas fa-pen"></i> Edit</button>
                        </div>
                        <div><span id="reviewType" style="font-weight: 600;">-</span></div>
                    </div>

                    <div class="review-section">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span class="review-section-title"><i class="fas fa-users"
                                    style="color: var(--primary);"></i> Clients (<span
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

                <div class="summary-box">
                    <div class="summary-row"><span class="summary-label">Type</span><span class="summary-value"
                            id="sumType">-</span></div>
                    <div class="summary-row"><span class="summary-label">Clients</span><span class="summary-value"
                            id="sumClients">-</span></div>
                    <div class="summary-row"><span class="summary-label">Date & Time</span><span class="summary-value"
                            id="sumDateTime">-</span></div>
                    <div class="summary-row"><span class="summary-label">Contact</span><span class="summary-value"
                            id="sumContact">-</span></div>
                </div>

                <div class="confirmation-checkbox">
                    <input type="checkbox" id="confirmCheckbox">
                    <label for="confirmCheckbox">
                        <strong>I confirm that all information is accurate and complete.</strong><br>
                        <span style="font-size: 0.85rem; color: var(--gray-500);">I have read all service requirements
                            and will bring necessary documents.</span>
                    </label>
                </div>

                <button class="btn-next" id="submitRequestBtn" disabled>
                    <i class="fas fa-check-circle"></i> Confirm & Submit
                </button>

                <div class="reminder" style="margin-top: 20px;">
                    <i class="far fa-bell" style="color: var(--warning);"></i>
                    Reminder: Please save your appointment reference number for verification.
                </div>

                <button class="btn-next back-btn" id="backToReview"><i class="fas fa-arrow-left"></i> Back</button>
            </div>
        </div>

        <div class="footer-note">
            <span><i class="far fa-copyright"></i> Philippine Statistics Authority</span>
            <span>National ID System (PhilSys) · Official Portal</span>
            <span>© {{ date('Y') }} All Rights Reserved</span>
        </div>
    </div>

    <!-- Hidden fields for location data -->
    <input type="hidden" id="userLat" value="">
    <input type="hidden" id="userLng" value="">
    <input type="hidden" id="userCity" value="">
    <input type="hidden" id="userAddress" value="">
    <input type="hidden" id="userZipcode" value="">

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (function() {
            'use strict';

            let appointmentType = 'single';
            let singleService = 'reg';
            let singleServiceText = 'National ID Registration';
            let selectedDate = null;
            let selectedTimeSlot = null;
            let selectedTimeSlotLabel = null;
            let availableDatesData = [];
            let availableTimeSlots = [];

            // TRN related variables (stored per client for multiple appointments)
            let clientTrnData = {}; // { clientId: { hasTrn: bool, trnNumber: string, isValid: bool } }

            // Location variables - will be populated from localStorage
            let userLocation = {
                lat: null,
                lng: null,
                city: null,
                address: null,
                zipcode: null
            };

            // QR Scanner instance
            let html5QrCode = null;
            let currentQrClientId = null;

            // TRN validation - exactly 29 digits
            const TRN_LENGTH = 29;

            function isValidTrn(trnValue) {
                if (!trnValue) return false;
                const cleanTrn = trnValue.replace(/\s/g, '');
                return /^\d{29}$/.test(cleanTrn);
            }

            const serviceOptions = {
                'reg': 'National ID Registration',
                'correction': 'Correction/Updating',
                'status_inquiry': 'Status Inquiry / Retrieval Of TRN / Other Concern'
            };

            const requirementsContent = {
                'reg': `<h4><i class="fas fa-id-card"></i> National ID Registration</h4><p><strong>PRIMARY DOCUMENTS:</strong></p><ul><li>PSA Birth Certificate + 1 government-issued ID (Passport, UMID, Driver's License)</li></ul><p><strong>SECONDARY DOCUMENTS:</strong></p><ul><li>PSA/LCRO Birth Certificate</li><li>Voter's ID, Postal ID, PhilHealth ID</li><li>Employee ID, School ID, Barangay Certificate</li></ul><p><em>⚠️ Bring original documents.</em></p>`,
                'correction': `<h4><i class="fas fa-pen"></i> Correction/Updating</h4><p><strong>Required Documents by Field:</strong></p><ul><li><strong>First/Last Name:</strong> Birth Certificate, Marriage Certificate</li><li><strong>Sex/DOB:</strong> Birth Certificate</li><li><strong>Address:</strong> Barangay Certificate + Billing</li></ul><p><em>⚠️ Bring ORIGINAL copies.</em></p>`,
                'status_inquiry': `<h4><i class="fas fa-question-circle"></i> Status Inquiry / Retrieval Of TRN / Other Concern</h4><p><strong>REQUIREMENTS:</strong></p><ul><li>Valid Government-issued ID</li><li>Any previous transaction slip (if available)</li><li>For TRN Retrieval: Provide full name, date of birth, and sex for verification</li><li><strong>TRN must be exactly 29 digits</strong></li></ul><p><em>⚠️ Please bring any reference documents you may have for faster processing.</em></p>`
            };

            const identityReminders = {
                'reg': 'The person named below must be the one registering for the National ID.',
                'correction': 'The person named below must be the one requesting the correction/update.',
                'status_inquiry': 'The person named below must be the one requesting status inquiry, TRN retrieval, or other concerns.'
            };

            let clients = [{
                id: 1,
                firstName: '',
                middleName: '',
                lastName: '',
                suffix: '',
                sex: 'Male',
                birthdate: '',
                service: 'reg',
                reqAcknowledged: false
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
                type: document.getElementById('sectionType'),
                clients: document.getElementById('sectionClients'),
                schedule: document.getElementById('sectionSchedule'),
                contact: document.getElementById('sectionContact'),
                review: document.getElementById('sectionReview'),
                confirm: document.getElementById('sectionConfirm')
            };

            // ==================== LOAD LOCATION FROM LANDING PAGE ====================

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

                            console.log('Location loaded from landing page:', userLocation);
                            return true;
                        }
                    } catch (e) {
                        console.error('Error parsing location data:', e);
                    }
                }
                return false;
            }

            function debugLocationData() {
                console.log('=== LOCATION DATA DEBUG ===');
                console.log('userLat:', document.getElementById('userLat').value);
                console.log('userLng:', document.getElementById('userLng').value);
                console.log('userCity:', document.getElementById('userCity').value);
                console.log('userAddress:', document.getElementById('userAddress').value);
                console.log('userZipcode:', document.getElementById('userZipcode').value);
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

            function formatDisplayDateTime(date, timeSlotLabel) {
                if (!date) return 'No date selected';
                if (!timeSlotLabel) return formatDisplayDate(date);
                return `${formatDisplayDate(date)} at ${timeSlotLabel}`;
            }

            function getServiceName(c) {
                return serviceOptions[c] || c;
            }

            function getFullName(c) {
                const parts = [c.firstName, c.middleName, c.lastName].filter(p => p && p.trim());
                let fullName = parts.join(' ') || '(No name)';
                if (c.suffix) fullName += ' ' + c.suffix;
                return fullName;
            }

            function allRequirementsAcknowledged() {
                return clients.every(c => c.reqAcknowledged);
            }

            function updateReqSummary() {
                const list = document.getElementById('reqSummaryList');
                const banner = document.getElementById('reqSummaryBanner');
                let html = '';
                let allAcked = true;
                clients.forEach((c, i) => {
                    const svc = appointmentType === 'single' ? singleService : c.service;
                    const acked = c.reqAcknowledged;
                    if (!acked) allAcked = false;
                    const displayName = getFullName(c);
                    html +=
                        `<div class="req-item"><i class="fas ${acked ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="color:${acked?'var(--success)':'var(--warning)'};"></i> ${displayName || 'Person '+(i+1)} - ${getServiceName(svc)} ${acked ? '✓' : '(Pending)'}</div>`;
                });
                list.innerHTML = html;
                banner.classList.toggle('complete', allAcked);
                const nextBtn = document.getElementById('nextToSchedule');
                if (nextBtn) nextBtn.disabled = !allAcked;
            }

            async function loadAvailableDates() {
                const clientCount = appointmentType === 'multiple' ? clients.length : 1;
                const selectedService = appointmentType === 'single' ? singleService : null;

                let url =
                    `{{ route('client.appointment.available-dates') }}?month=${currentMonth + 1}&year=${currentYear}&client_count=${clientCount}`;

                if (appointmentType === 'multiple') {
                    const services = [...new Set(clients.map(c => c.service))];
                    url += `&services=${services.join(',')}`;
                } else if (selectedService) {
                    url += `&service=${selectedService}`;
                }

                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
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
                        '<div class="error">Failed to load available dates. Please try again later.</div>';
                }
            }

            async function loadTimeSlots(date) {
                const timeSlotsGrid = document.getElementById('timeSlotsGrid');
                const timeSlotsContainer = document.getElementById('timeSlotsContainer');
                
                if (!date) {
                    timeSlotsContainer.style.display = 'none';
                    return;
                }
                
                timeSlotsContainer.style.display = 'block';
                timeSlotsGrid.innerHTML = '<div class="time-slots-loading"><i class="fas fa-spinner fa-spin"></i> Loading available time slots...</div>';
                
                try {
                    const selectedServices = appointmentType === 'multiple' 
                        ? [...new Set(clients.map(c => c.service))]
                        : [singleService];
                    
                    const url = `{{ route('client.appointment.available-time-slots') }}?date=${date}&services=${selectedServices.join(',')}&client_count=${appointmentType === 'multiple' ? clients.length : 1}`;
                    
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    if (data.success && data.time_slots && data.time_slots.length > 0) {
                        availableTimeSlots = data.time_slots;
                        renderTimeSlots(availableTimeSlots);
                    } else {
                        timeSlotsGrid.innerHTML = '<div class="time-slots-loading"><i class="fas fa-info-circle"></i> No available time slots for this date.</div>';
                    }
                } catch (error) {
                    console.error('Error loading time slots:', error);
                    timeSlotsGrid.innerHTML = '<div class="time-slots-loading"><i class="fas fa-exclamation-triangle"></i> Failed to load time slots.</div>';
                }
            }
            
            function renderTimeSlots(timeSlots) {
                const timeSlotsGrid = document.getElementById('timeSlotsGrid');
                
                if (!timeSlots || timeSlots.length === 0) {
                    timeSlotsGrid.innerHTML = '<div class="time-slots-loading"><i class="fas fa-info-circle"></i> No available time slots for this date.</div>';
                    return;
                }
                
                let html = '';
                timeSlots.forEach(slot => {
                    const isAvailable = slot.is_available;
                    const isSelected = selectedTimeSlot === slot.id;
                    const availableText = slot.available_slots > 0 ? `${slot.available_slots} slots available` : 'Fully booked';
                    const availabilityClass = slot.available_slots > 3 ? 'available' : (slot.available_slots > 0 ? 'limited' : 'full');
                    
                    html += `
                        <div class="time-slot-card ${isAvailable ? '' : 'disabled'} ${isSelected ? 'selected' : ''}" 
                             data-slot-id="${slot.id}" 
                             data-slot-label="${slot.slot_label}"
                             data-available="${isAvailable}">
                            <div class="time-slot-time">${slot.slot_label}</div>
                            <div class="time-slot-availability ${availabilityClass}">
                                ${isAvailable ? availableText : 'Not Available'}
                            </div>
                        </div>
                    `;
                });
                
                timeSlotsGrid.innerHTML = html;
                
                // Add click event listeners to time slot cards
                document.querySelectorAll('.time-slot-card').forEach(card => {
                    card.addEventListener('click', () => {
                        const isAvailable = card.dataset.available === 'true';
                        if (!isAvailable) return;
                        
                        const slotId = parseInt(card.dataset.slotId);
                        const slotLabel = card.dataset.slotLabel;
                        
                        // Remove selected class from all
                        document.querySelectorAll('.time-slot-card').forEach(c => c.classList.remove('selected'));
                        // Add selected class to clicked card
                        card.classList.add('selected');
                        
                        selectedTimeSlot = slotId;
                        selectedTimeSlotLabel = slotLabel;
                        
                        console.log('Time slot selected:', { id: selectedTimeSlot, label: selectedTimeSlotLabel });
                    });
                });
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
                for (let i = 0; i < startOffset; i++) {
                    html += '<div class="calendar-day empty"></div>';
                }

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const selectedServices = [...new Set(clients.map(c => c.service))];

                const serviceShort = {
                    'reg': 'R',
                    'correction': 'U',
                    'status_inquiry': 'S'
                };

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

                    let indicatorsHtml = '';

                    if (dateData && !isPast && dateData.available) {
                        if (appointmentType === 'multiple' && dateData.service_availability) {
                            indicatorsHtml = '<div class="slot-indicators">';
                            for (const service of selectedServices) {
                                const slots = dateData.service_availability[service] || 0;
                                const badgeClass = slots > 0 ? 'available' : 'full';
                                const shortCode = serviceShort[service] || service.charAt(0).toUpperCase();
                                indicatorsHtml +=
                                    `<span class="service-badge ${badgeClass}">${shortCode}${slots}</span>`;
                            }
                            indicatorsHtml += '</div>';
                        } else {
                            const countClass = dateData.available_slots > 0 ? 'available' : 'full';
                            indicatorsHtml = `<div class="slot-count ${countClass}">${dateData.available_slots}</div>`;
                        }
                    }

                    html +=
                        `<div class="${cls}" data-date="${dateKey}" onclick="window.selectDate('${dateKey}')">`;
                    html += `<div class="day-number">${d}</div>`;
                    html += indicatorsHtml;
                    html += `</div>`;
                }

                document.getElementById('calendarDays').innerHTML = html;
            }

            window.selectDate = function(dateKey) {
                selectedDate = dateKey;
                selectedTimeSlot = null;
                selectedTimeSlotLabel = null;
                
                document.getElementById('selectedDateText').textContent = formatDisplayDate(dateKey);
                document.getElementById('slotInfoText').innerHTML = `✓ Date selected. Please choose a time slot.`;
                
                renderCalendar();
                loadTimeSlots(dateKey);
            };

            // ==================== TRN FUNCTIONS ====================
            
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
                        charCounter.innerHTML = `<i class="fas fa-check-circle"></i> ${currentLength}/${TRN_LENGTH} digits - Valid TRN`;
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
                            charCounter.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${currentLength}/${TRN_LENGTH} digits - TRN must be exactly 29 digits`;
                        } else if (currentLength > TRN_LENGTH) {
                            charCounter.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${currentLength}/${TRN_LENGTH} digits - Too many digits (max ${TRN_LENGTH})`;
                        } else {
                            charCounter.innerHTML = `<i class="fas fa-info-circle"></i> TRN must be exactly 29 digits (0-9 only)`;
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
                        <div class="trn-question">
                            <i class="fas fa-question-circle"></i> DO YOU HAVE A TRN (TRANSACTION REFERENCE NUMBER)?
                        </div>
                        <div class="trn-checkbox-group">
                            <label>
                                <input type="radio" name="hasTrn_${clientId}" value="yes" ${hasTrnValue === true ? 'checked' : ''}> YES
                            </label>
                            <label>
                                <input type="radio" name="hasTrn_${clientId}" value="no" ${hasTrnValue === false ? 'checked' : ''}> NO
                            </label>
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
                                <button type="button" class="qr-scan-btn" data-client-id="${clientId}">
                                    <i class="fas fa-camera"></i> SCAN QR CODE
                                </button>
                                <div id="qr-reader_${clientId}" class="qr-reader-container" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            function attachTrnEvents(clientId) {
                const radioYes = document.querySelector(`input[name="hasTrn_${clientId}"][value="yes"]`);
                const radioNo = document.querySelector(`input[name="hasTrn_${clientId}"][value="no"]`);
                const trnInputArea = document.getElementById(`trnInputArea_${clientId}`);
                const trnNumberInput = document.getElementById(`trnNumber_${clientId}`);
                const scanBtn = document.querySelector(`.qr-scan-btn[data-client-id="${clientId}"]`);
                
                if (!clientTrnData[clientId]) {
                    clientTrnData[clientId] = { hasTrn: null, trnNumber: '', isValid: false };
                }
                
                if (radioYes) {
                    radioYes.addEventListener('change', function() {
                        if (this.checked) {
                            clientTrnData[clientId].hasTrn = true;
                            if (trnInputArea) trnInputArea.style.display = 'block';
                            setTimeout(() => validateAndStyleTrnInput(clientId), 10);
                        }
                    });
                }
                
                if (radioNo) {
                    radioNo.addEventListener('change', function() {
                        if (this.checked) {
                            clientTrnData[clientId].hasTrn = false;
                            clientTrnData[clientId].trnNumber = '';
                            clientTrnData[clientId].isValid = false;
                            if (trnInputArea) trnInputArea.style.display = 'none';
                            if (trnNumberInput) trnNumberInput.value = '';
                            stopQrScanner(clientId);
                        }
                    });
                }
                
                if (trnNumberInput) {
                    trnNumberInput.addEventListener('input', function(e) {
                        this.value = this.value.replace(/[^0-9]/g, '');
                        if (this.value.length > TRN_LENGTH) {
                            this.value = this.value.slice(0, TRN_LENGTH);
                        }
                        clientTrnData[clientId].trnNumber = this.value;
                        validateAndStyleTrnInput(clientId);
                    });
                }
                
                if (scanBtn) {
                    scanBtn.addEventListener('click', () => startQrScanner(clientId));
                }
            }
            
            async function startQrScanner(clientId) {
                const qrReaderDiv = document.getElementById(`qr-reader_${clientId}`);
                if (!qrReaderDiv) return;
                
                await stopQrScanner(clientId);
                
                qrReaderDiv.style.display = 'block';
                currentQrClientId = clientId;
                
                html5QrCode = new Html5Qrcode(`qr-reader_${clientId}`);
                
                try {
                    await html5QrCode.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            const trnInput = document.getElementById(`trnNumber_${clientId}`);
                            if (trnInput) {
                                const digitsOnly = decodedText.replace(/[^0-9]/g, '');
                                const finalTrn = digitsOnly.slice(0, TRN_LENGTH);
                                trnInput.value = finalTrn;
                                clientTrnData[clientId].trnNumber = finalTrn;
                                validateAndStyleTrnInput(clientId);
                            }
                            stopQrScanner(clientId);
                            qrReaderDiv.style.display = 'none';
                            alert(clientTrnData[clientId].isValid ? 'QR Code scanned successfully!' : 'QR Code scanned. Please ensure TRN is exactly 29 digits.');
                        },
                        (errorMessage) => {
                            console.log(`QR Scan error: ${errorMessage}`);
                        }
                    );
                } catch (err) {
                    console.error(`Failed to start QR scanner: ${err}`);
                    alert('Could not access camera. Please grant camera permissions.');
                    qrReaderDiv.style.display = 'none';
                }
            }
            
            async function stopQrScanner(clientId) {
                if (html5QrCode && html5QrCode.isScanning) {
                    try {
                        await html5QrCode.stop();
                        html5QrCode.clear();
                    } catch (err) {
                        console.error(`Failed to stop QR scanner: ${err}`);
                    }
                }
                if (currentQrClientId === clientId) {
                    html5QrCode = null;
                    currentQrClientId = null;
                }
            }
            
            function shouldShowTrnForClient(client) {
                const service = appointmentType === 'single' ? singleService : client.service;
                return service === 'status_inquiry';
            }
            
            function validateTrnForClient(client) {
                if (!shouldShowTrnForClient(client)) return true;
                
                const trnData = clientTrnData[client.id];
                if (!trnData || trnData.hasTrn === null) {
                    return false;
                }
                if (trnData.hasTrn === true) {
                    if (!trnData.trnNumber || !trnData.isValid) {
                        return false;
                    }
                }
                return true;
            }

            function renderClients() {
                const container = document.getElementById('clientsList');
                const isMultiple = appointmentType === 'multiple';
                const addBtn = document.getElementById('addClientBtn');
                const countContainer = document.getElementById('clientCountContainer');

                if (appointmentType === 'single') {
                    addBtn.classList.add('hidden');
                    countContainer.classList.add('hidden');
                    document.getElementById('clientsTitle').textContent = 'Client Information';
                    document.getElementById('clientsSubtitle').textContent =
                        'Enter the details of the person for this appointment.';
                } else {
                    addBtn.classList.remove('hidden');
                    countContainer.classList.remove('hidden');
                    document.getElementById('clientsTitle').textContent = 'Family / Group Members';
                    document.getElementById('clientsSubtitle').textContent =
                        'Add all persons who will attend. Each can select their own service.';
                }

                container.innerHTML = '';
                clients.forEach((c, i) => {
                    const svc = isMultiple ? c.service : singleService;
                    const reminder = identityReminders[svc] || 'Please ensure the information is accurate.';
                    const showTrn = shouldShowTrnForClient(c);
                    const trnData = clientTrnData[c.id] || { hasTrn: null, trnNumber: '', isValid: false };

                    const card = document.createElement('div');
                    card.className = 'client-card';
                    card.innerHTML = `
                    <div class="client-header">
                        <span class="client-title"><i class="fas fa-user-circle"></i> ${appointmentType === 'single' ? 'Client Information' : 'Person '+(i+1)} ${isMultiple?`<span class="client-service-badge">${getServiceName(svc)}</span>`:''}</span>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-view-req" data-id="${c.id}"><i class="fas fa-book"></i> View Requirements</button>
                            ${(appointmentType === 'multiple' && clients.length > 1) ? `<button class="btn-remove-client" data-id="${c.id}"><i class="fas fa-trash-alt"></i></button>` : ''}
                        </div>
                    </div>
                    <div class="identity-reminder">
                        <i class="fas fa-id-card"></i>
                        <strong>Important:</strong> ${reminder}
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <label>First Name <span style="color: var(--danger);">*</span></label>
                            <input class="client-firstname" data-id="${c.id}" value="${c.firstName || ''}" placeholder="First Name">
                        </div>
                        <div class="form-col">
                            <label>Middle Name</label>
                            <input class="client-middlename" data-id="${c.id}" value="${c.middleName || ''}" placeholder="Middle Name">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Last Name <span style="color: var(--danger);">*</span></label>
                            <input class="client-lastname" data-id="${c.id}" value="${c.lastName || ''}" placeholder="Last Name">
                        </div>
                        <div class="form-col">
                            <label>Suffix</label>
                            <select class="client-suffix" data-id="${c.id}">
                                <option value="" ${!c.suffix ? 'selected' : ''}>-- None --</option>
                                <option value="Jr." ${c.suffix === 'Jr.' ? 'selected' : ''}>Jr.</option>
                                <option value="Sr." ${c.suffix === 'Sr.' ? 'selected' : ''}>Sr.</option>
                                <option value="I" ${c.suffix === 'I' ? 'selected' : ''}>I</option>
                                <option value="II" ${c.suffix === 'II' ? 'selected' : ''}>II</option>
                                <option value="III" ${c.suffix === 'III' ? 'selected' : ''}>III</option>
                                <option value="IV" ${c.suffix === 'IV' ? 'selected' : ''}>IV</option>
                                <option value="V" ${c.suffix === 'V' ? 'selected' : ''}>V</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <label>Sex <span style="color: var(--danger);">*</span></label>
                            <select class="client-sex" data-id="${c.id}">
                                <option value="Male" ${c.sex==='Male'?'selected':''}>Male</option>
                                <option value="Female" ${c.sex==='Female'?'selected':''}>Female</option>
                            </select>
                        </div>
                        <div class="form-col">
                            <label>Birthdate <span style="color: var(--danger);">*</span></label>
                            <input type="date" class="client-birthdate" data-id="${c.id}" value="${c.birthdate}">
                        </div>
                    </div>
                    ${isMultiple ? `<div class="form-col" style="margin-top: 12px;"><label>Service <span style="color: var(--danger);">*</span></label><select class="client-service" data-id="${c.id}"><option value="reg" ${c.service==='reg'?'selected':''}>National ID Registration</option><option value="correction" ${c.service==='correction'?'selected':''}>Updating/Correction</option><option value="status_inquiry" ${c.service==='status_inquiry'?'selected':''}>Status Inquiry / Retrieval Of TRN / Other Concern</option></select></div>` : ''}
                    ${showTrn ? createTrnHtml(c.id, trnData.hasTrn, trnData.trnNumber) : ''}
                    <div class="req-ack-row ${c.reqAcknowledged ? 'acknowledged' : ''}">
                        <input type="checkbox" class="req-ack" data-id="${c.id}" ${c.reqAcknowledged ? 'checked' : ''}>
                        <label>I have read and understood the requirements for <strong>${getServiceName(svc)}</strong>. I will bring all necessary documents.</label>
                    </div>
                `;
                    container.appendChild(card);
                });

                attachClientEvents();
                document.getElementById('clientCount').textContent = clients.length;
                updateReqSummary();
            }

            function attachClientEvents() {
                document.querySelectorAll('.client-firstname').forEach(e => e.addEventListener('input', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) c.firstName = ev.target.value;
                }));
                document.querySelectorAll('.client-middlename').forEach(e => e.addEventListener('input', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) c.middleName = ev.target.value;
                }));
                document.querySelectorAll('.client-lastname').forEach(e => e.addEventListener('input', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) c.lastName = ev.target.value;
                }));
                document.querySelectorAll('.client-suffix').forEach(e => e.addEventListener('change', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) c.suffix = ev.target.value;
                }));
                document.querySelectorAll('.client-sex').forEach(e => e.addEventListener('change', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) c.sex = ev.target.value;
                }));
                document.querySelectorAll('.client-birthdate').forEach(e => e.addEventListener('input', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) c.birthdate = ev.target.value;
                }));
                document.querySelectorAll('.client-service').forEach(e => e.addEventListener('change', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) {
                        c.service = ev.target.value;
                        c.reqAcknowledged = false;
                        if (c.service !== 'status_inquiry') {
                            delete clientTrnData[c.id];
                        } else {
                            if (!clientTrnData[c.id]) {
                                clientTrnData[c.id] = { hasTrn: null, trnNumber: '', isValid: false };
                            }
                        }
                        renderClients();
                        updateReqSummary();
                    }
                }));
                document.querySelectorAll('.req-ack').forEach(e => e.addEventListener('change', (ev) => {
                    const c = clients.find(x => x.id == ev.target.dataset.id);
                    if (c) {
                        c.reqAcknowledged = ev.target.checked;
                        renderClients();
                        updateReqSummary();
                    }
                }));
                document.querySelectorAll('.btn-view-req').forEach(b => b.addEventListener('click', (ev) => {
                    const id = ev.target.closest('.btn-view-req').dataset.id;
                    const client = clients.find(c => c.id == id);
                    const svc = appointmentType === 'single' ? singleService : client.service;
                    modalTitle.textContent = `${getServiceName(svc)} Requirements`;
                    modalBody.innerHTML = requirementsContent[svc] || '<p>Requirements not available.</p>';
                    reqModal.style.display = 'flex';
                }));
                document.querySelectorAll('.btn-remove-client').forEach(b => b.addEventListener('click', (ev) => {
                    const id = ev.target.closest('.btn-remove-client').dataset.id;
                    clients = clients.filter(c => c.id != id);
                    delete clientTrnData[id];
                    renderClients();
                    updateReqSummary();
                    document.getElementById('clientCount').textContent = clients.length;
                }));
                
                clients.forEach(client => {
                    if (shouldShowTrnForClient(client)) {
                        attachTrnEvents(client.id);
                    }
                });
            }

            function setActiveStep(s) {
                document.querySelectorAll('.step').forEach((e, i) => e.classList.toggle('active', i + 1 === s));
            }

            function showSection(s) {
                Object.values(sections).forEach(x => x.classList.add('hidden'));
                s.classList.remove('hidden');
            }

            // ==================== SUBMIT HANDLER ====================

            document.getElementById('submitRequestBtn').onclick = async () => {
                const locationData = {
                    user_lat: document.getElementById('userLat').value || null,
                    user_lng: document.getElementById('userLng').value || null,
                    user_city: document.getElementById('userCity').value || null,
                    user_address: document.getElementById('userAddress').value || null,
                    user_zipcode: document.getElementById('userZipcode').value || null
                };

                const clientsData = clients.map(c => {
                    const clientInfo = {
                        first_name: c.firstName,
                        middle_name: c.middleName || null,
                        last_name: c.lastName,
                        suffix: c.suffix || null,
                        sex: c.sex,
                        birthdate: c.birthdate,
                        service: appointmentType === 'single' ? singleService : c.service
                    };
                    
                    if (shouldShowTrnForClient(c)) {
                        const trnData = clientTrnData[c.id];
                        if (trnData) {
                            clientInfo.has_trn = trnData.hasTrn;
                            clientInfo.trn_number = trnData.hasTrn && trnData.isValid ? trnData.trnNumber : null;
                        }
                    }
                    
                    return clientInfo;
                });

                const formData = {
                    appointment_type: appointmentType,
                    appointment_date: selectedDate,
                    appointment_time_slot_id: selectedTimeSlot,
                    contact_name: document.getElementById('contactName').value,
                    contact_email: document.getElementById('contactEmail').value || null,
                    contact_mobile: document.getElementById('contactMobile').value,
                    user_lat: locationData.user_lat,
                    user_lng: locationData.user_lng,
                    user_city: locationData.user_city,
                    user_address: locationData.user_address,
                    user_zipcode: locationData.user_zipcode,
                    clients: clientsData
                };

                if (!formData.contact_name) {
                    alert('Please enter contact name');
                    return;
                }
                if (!formData.contact_mobile) {
                    alert('Please enter mobile number');
                    return;
                }
                if (!formData.appointment_date) {
                    alert('Please select an appointment date');
                    return;
                }
                if (!formData.appointment_time_slot_id) {
                    alert('Please select a preferred time slot');
                    return;
                }

                showLoading();

                try {
                    const response = await fetch('{{ route('client.appointment.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        let locationMessage = '';
                        if (locationData.user_city) {
                            locationMessage = `<p><strong>Location:</strong> ${locationData.user_city}</p>`;
                        }

                        document.getElementById('successDetails').innerHTML = `
                        <p><strong>Appointment Number:</strong> ${result.appointment.number}</p>
                        <p><strong>Reference Code:</strong> ${result.appointment.reference_code}</p>
                        <p><strong>Date & Time:</strong> ${result.appointment.date} at ${result.appointment.time || 'Selected time slot'}</p>
                        <p><strong>Clients:</strong> ${result.appointment.clients_count} person(s)</p>
                        ${locationMessage}
                        <hr>
                        <p><small>A confirmation ${result.email_sent ? 'email has been sent' : 'SMS will be sent'} to your registered contact.</small></p>
                    `;

                        successModal.style.display = 'flex';
                    } else {
                        alert('Error: ' + (result.message || 'Unknown error occurred'));
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    alert('Failed to submit appointment. Please check your connection and try again.');
                } finally {
                    hideLoading();
                }
            };

            // ==================== EVENT LISTENERS ====================

            document.getElementById('agreePrivacyBtn').onclick = () => privacyModal.style.display = 'none';

            document.getElementById('typeSingle').onclick = () => {
                appointmentType = 'single';
                document.getElementById('typeSingle').classList.add('selected');
                document.getElementById('typeMultiple').classList.remove('selected');
                document.getElementById('singleServiceSection').style.display = 'block';
                if (clients.length > 1) clients = [clients[0]];
                clients.forEach(c => c.service = singleService);
                clientTrnData = {};
                renderClients();
            };

            document.getElementById('typeMultiple').onclick = () => {
                appointmentType = 'multiple';
                document.getElementById('typeMultiple').classList.add('selected');
                document.getElementById('typeSingle').classList.remove('selected');
                document.getElementById('singleServiceSection').style.display = 'none';
                renderClients();
            };

            document.getElementById('singleServiceSelect').onchange = function() {
                singleService = this.value;
                singleServiceText = serviceOptions[this.value] || '';
                const descDiv = document.getElementById('singleServiceDesc');
                const reqBtn = document.getElementById('showReqBtnSingle');
                
                if (this.value) {
                    descDiv.innerHTML = `<p style="color: var(--gray-700);"><i class="fas fa-check-circle" style="color: var(--success);"></i> ${singleServiceText} selected. Click 'View Requirements' for details.</p>`;
                    reqBtn.style.display = 'block';
                } else {
                    descDiv.innerHTML = '<p style="color: var(--gray-500);"><i class="fas fa-info-circle"></i> Select a service.</p>';
                    reqBtn.style.display = 'none';
                }
                
                if (appointmentType === 'single') {
                    clients.forEach(c => c.service = singleService);
                    clientTrnData = {};
                    renderClients();
                }
            };

            document.getElementById('showReqBtnSingle').onclick = () => {
                modalTitle.textContent = singleServiceText + ' Requirements';
                modalBody.innerHTML = requirementsContent[singleService] || '';
                reqModal.style.display = 'flex';
            };

            document.getElementById('addClientBtn').onclick = () => {
                if (appointmentType === 'single') return;
                if (clients.length >= 10) {
                    alert('Maximum 10 persons.');
                    return;
                }
                const newId = nextClientId++;
                clients.push({
                    id: newId,
                    firstName: '',
                    middleName: '',
                    lastName: '',
                    suffix: '',
                    sex: 'Male',
                    birthdate: '',
                    service: 'reg',
                    reqAcknowledged: false
                });
                renderClients();
            };

            document.getElementById('nextToClients').onclick = () => {
                if (appointmentType === 'single' && !singleService) {
                    alert('Please select a service');
                    return;
                }
                renderClients();
                showSection(sections.clients);
                setActiveStep(2);
            };

            document.getElementById('backToTypeFromClients').onclick = () => {
                showSection(sections.type);
                setActiveStep(1);
            };

            document.getElementById('nextToSchedule').onclick = () => {
                for (let c of clients) {
                    if (!c.firstName?.trim() || !c.lastName?.trim()) {
                        alert('First Name and Last Name are required for all clients');
                        return;
                    }
                    if (!c.sex) {
                        alert('Please select sex for all clients');
                        return;
                    }
                    if (!c.birthdate) {
                        alert('Please enter birthdate for all clients');
                        return;
                    }
                    if (appointmentType === 'multiple' && !c.service) {
                        alert('Please select service for all clients');
                        return;
                    }
                    if (!validateTrnForClient(c)) {
                        const clientName = getFullName(c) || `Person ${clients.indexOf(c) + 1}`;
                        alert(`${clientName}: Please indicate whether you have a TRN. If YES, please enter the exact 29-digit TRN number or scan the QR code.`);
                        return;
                    }
                }
                if (!allRequirementsAcknowledged()) {
                    alert('Please acknowledge all requirements.');
                    return;
                }
                loadAvailableDates();
                showSection(sections.schedule);
                setActiveStep(3);
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
                    alert('Please select an appointment date.');
                    return;
                }
                if (!selectedTimeSlot) {
                    alert('Please select a preferred time slot.');
                    return;
                }
                showSection(sections.contact);
                setActiveStep(4);
            };

            document.getElementById('backToScheduleFromContact').onclick = () => {
                showSection(sections.schedule);
                setActiveStep(3);
            };

            document.getElementById('nextToReview').onclick = () => {
                const name = document.getElementById('contactName').value;
                const mob = document.getElementById('contactMobile').value;
                if (!name || !mob) {
                    alert('Contact name and mobile number are required.');
                    return;
                }
                document.getElementById('reviewType').textContent = appointmentType === 'single' ? 'Single' : 'Family / Group';
                document.getElementById('reviewClientCount').textContent = clients.length;
                document.getElementById('reviewClientsList').innerHTML = clients.map((c, i) =>
                    `<div class="client-summary-item"><strong>${i+1}. ${getFullName(c)}</strong> - ${getServiceName(appointmentType==='single'?singleService:c.service)}</div>`
                ).join('');
                document.getElementById('reviewDateTime').textContent = formatDisplayDateTime(selectedDate, selectedTimeSlotLabel);
                document.getElementById('reviewContactName').textContent = name;
                document.getElementById('reviewContactEmail').textContent = document.getElementById('contactEmail').value || 'Not provided';
                document.getElementById('reviewContactMobile').textContent = mob;
                showSection(sections.review);
                setActiveStep(5);
            };

            document.getElementById('backToContact').onclick = () => {
                showSection(sections.contact);
                setActiveStep(4);
            };

            document.getElementById('nextToConfirm').onclick = () => {
                document.getElementById('sumType').textContent = appointmentType === 'single' ? 'Single' : 'Multiple';
                document.getElementById('sumClients').textContent = clients.length + ' person(s)';
                document.getElementById('sumDateTime').textContent = formatDisplayDateTime(selectedDate, selectedTimeSlotLabel);
                document.getElementById('sumContact').textContent = document.getElementById('contactName').value + ' / ' + document.getElementById('contactMobile').value;
                showSection(sections.confirm);
                setActiveStep(6);
            };

            document.getElementById('backToReview').onclick = () => {
                showSection(sections.review);
                setActiveStep(5);
            };

            document.getElementById('confirmCheckbox').onchange = (e) => {
                document.getElementById('submitRequestBtn').disabled = !e.target.checked;
            };

            document.getElementById('closeReqModal').onclick = () => reqModal.style.display = 'none';
            document.getElementById('understandBtn').onclick = () => reqModal.style.display = 'none';
            document.getElementById('closeSuccessModal').onclick = () => successModal.style.display = 'none';

            document.querySelectorAll('[data-edit]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const target = e.target.closest('[data-edit]').dataset.edit;
                    if (target === 'type') {
                        showSection(sections.type);
                        setActiveStep(1);
                    } else if (target === 'clients') {
                        showSection(sections.clients);
                        setActiveStep(2);
                    } else if (target === 'schedule') {
                        showSection(sections.schedule);
                        setActiveStep(3);
                    } else if (target === 'contact') {
                        showSection(sections.contact);
                        setActiveStep(4);
                    }
                });
            });

            document.getElementById('singleServiceSelect').value = 'reg';
            document.getElementById('singleServiceSelect').dispatchEvent(new Event('change'));
            renderClients();

            window.addEventListener('click', (e) => {
                if (e.target === reqModal) reqModal.style.display = 'none';
                if (e.target === successModal) successModal.style.display = 'none';
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    reqModal.style.display = 'none';
                    successModal.style.display = 'none';
                }
            });

            loadLocationFromLandingPage();
            window.debugLocation = debugLocationData;
        })();
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>