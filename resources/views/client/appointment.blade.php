{{-- resources/views/client/appointment.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>National ID Appointment System</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/appointment.css') }}">

    <style>
        /* Age-specific requirements styling */
        .age-requirements-badge {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 8px;
            font-size: 13px;
        }
        
        .age-requirements-badge i {
            color: #ffc107;
            margin-right: 8px;
        }
        
        .child-requirements-toggle {
            background: #e8f4f8;
            border: 1px solid #b8d4e8;
            border-radius: 8px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .child-requirements-header {
            background: #d1e7f0;
            padding: 10px 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: #0f3b6f;
        }
        
        .child-requirements-header:hover {
            background: #c5dfea;
        }
        
        .child-requirements-content {
            padding: 15px;
            display: none;
            background: white;
        }
        
        .child-requirements-content.show {
            display: block;
        }
        
        .req-docs-list {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .req-docs-list li {
            margin: 8px 0;
            line-height: 1.4;
        }
        
        .req-section-title {
            font-weight: 700;
            color: #2c5f8a;
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 14px;
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
            <div class="modal-header">
                <h3 style="color: var(--success);"><i class="fas fa-check-circle"></i> Appointment Confirmed!</h3>
                <span class="close-modal" id="closeSuccessModal">&times;</span>
            </div>
            <div id="successBody" style="padding: 20px;">
                <p>Your appointment has been successfully booked.</p>
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
            <div class="header-title">
                <img src="{{ asset('images/logo.png') }}" alt="National ID System" style="height: 50px; width: auto;">
            </div>
        </div>

        <div class="stepper">
            <div class="step active" id="step1"><span class="step-num">1</span> Guide</div>
            <div class="step" id="step2"><span class="step-num">2</span> Clients</div>
            <div class="step" id="step3"><span class="step-num">3</span> Schedule</div>
            <div class="step" id="step4"><span class="step-num">4</span> Contact</div>
            <div class="step" id="step5"><span class="step-num">5</span> Review</div>
            <div class="step" id="step6"><span class="step-num">6</span> Confirm</div>
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
                        <div class="howto-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <span class="howto-step">STEP 1</span>
                        <h3>Add Clients</h3>
                        <p>Add the person/s who will attend the appointment. Maximum of <strong>4 persons</strong> per
                            booking.</p>
                        <small>Each person can select their own service type</small>
                    </div>
                    <div class="howto-card">
                        <div class="howto-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <span class="howto-step">STEP 2</span>
                        <h3>Select Schedule</h3>
                        <p>Choose your preferred appointment date and available time slot based on your selected
                            services.</p>
                        <small>Real-time slot availability</small>
                    </div>
                    <div class="howto-card">
                        <div class="howto-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <span class="howto-step">STEP 3</span>
                        <h3>Contact Info</h3>
                        <p>Provide your contact details for appointment confirmation and reminders.</p>
                        <small>Email & mobile number</small>
                    </div>
                    <div class="howto-card">
                        <div class="howto-icon">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <span class="howto-step">STEP 4</span>
                        <h3>Review & Confirm</h3>
                        <p>Double-check all information and confirm your appointment.</p>
                        <small>Download your confirmation as PNG or PDF</small>
                    </div>
                </div>

                <div style="background: #e3f2fd; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <i class="fas fa-info-circle" style="color: #2c5f8a;"></i>
                    <strong>Important Reminders:</strong>
                    <ul style="margin-top: 10px; margin-bottom: 0;">
                        <li>Maximum of 4 persons per appointment</li>
                        <li>Each person can select their own service type</li>
                        <li>Bring valid IDs and required documents</li>
                        <li>Arrive 15 minutes before your scheduled time</li>
                        <li>For children aged 1-4 years old, additional requirements apply</li>
                    </ul>
                </div>

                <button class="btn-next" id="startBookingBtn">Start Booking <i
                        class="fas fa-arrow-right"></i></button>
            </div>

            <!-- STEP 2: CLIENTS -->
            <div id="sectionClients" class="hidden">
                <div class="section-title">Client Information</div>
                <p style="color: var(--gray-500); margin-bottom: 20px;">
                    <i class="fas fa-users"></i> Add the persons who will attend (Maximum 4 persons)
                </p>

                <div class="req-summary-banner" id="reqSummaryBanner">
                    <strong><i class="fas fa-clipboard-list"></i> Requirements Status</strong>
                    <div id="reqSummaryList"></div>
                </div>

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
                <div class="btn-group">
                <button class="btn-next" id="nextToContact">Next: Contact Info <i
                        class="fas fa-arrow-right"></i></button>
                <button class="btn-next back-btn" id="backToClients"><i class="fas fa-arrow-left"></i> Back</button>
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
                    <label>Mobile Number <span style="color: var(--danger);">*</span></label>
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
                    <div class="summary-row"><span class="summary-label">Clients</span><span class="summary-value"
                            id="sumClients">-</span></div>
                    <div class="summary-row"><span class="summary-label">Date & Time</span><span
                            class="summary-value" id="sumDateTime">-</span></div>
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
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    // Global validation function that can be called from HTML attributes
    function validateBirthdate(input) {
        if (input.value) {
            const year = input.value.split('-')[0];
            if (year && year.length !== 4) {
                input.setCustomValidity('Year must be 4 digits (e.g., 1990)');
                input.reportValidity();
                input.value = '';
            } else {
                input.setCustomValidity('');
            }
        }
    }

    (function() {
        'use strict';

        let selectedDate = null;
        let selectedTimeSlot = null;
        let selectedTimeSlotLabel = null;
        let availableDatesData = [];
        let availableTimeSlots = [];
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

        const TRN_LENGTH = 29;
        const MAX_CLIENTS = 4;

        function isValidTrn(trnValue) {
            if (!trnValue) return false;
            const cleanTrn = trnValue.replace(/\s/g, '');
            return /^\d{29}$/.test(cleanTrn);
        }

        // Calculate age from birthdate
        function calculateAge(birthdate) {
            if (!birthdate) return null;
            const today = new Date();
            const birthDate = new Date(birthdate);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }

        // Get requirements for child (1-4 years old) - REPLACES standard requirements
        function getChildRequirements() {
            return `
                <div class="req-card">
                    <h4><i class="fas fa-child"></i> Requirements for Children (1-4 years old)</h4>
                    
                    <div class="req-section-title">Primary Documents - Any of the following:</div>
                    <ul class="req-docs-list">
                        <li>Certificate of Live Birth issued by the PSA or Local Civil Registry Office (LCRO)</li>
                        <li>Report of Birth issued by the PSA or Philippine Foreign Service Post (PFSP)</li>
                        <li>Certificate of Foundling issued by the PSA</li>
                        <li>Certificate of Foundling or Certificate of Live Birth of Person with No Known Parent/s issued by the PSA</li>
                        <li>Municipal Form No. 102</li>
                        <li>Philippine Passport or ePassport issued by the Department of Foreign Affairs (DFA)</li>
                        <li>PSA-issued Certificate of Live Birth/National Statistics Office (NSO)-issued Certificate of Live Birth with Birth Reference Number (BreN)</li>
                        <li>Local Civil Registry Office (LCRO)-issued Certificate of Live Birth</li>
                        <li>National ID of the Parent (either Mother or Father) or Guardian of the Child</li>
                    </ul>
                    
                    <div class="req-section-title">Alternative/Additional Documents:</div>
                    <ul class="req-docs-list">
                        <li>Any document showing the:
                            <ul style="margin-top: 5px; margin-left: 20px;">
                                <li>Full name of the child</li>
                                <li>Date and place of birth</li>
                                <li>Full name of the mother</li>
                                <li>Full name of the father (if acknowledged)</li>
                            </ul>
                        </li>
                    </ul>
                    
                    <div class="warning-note" style="margin-top: 15px;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Important:</strong> For children aged 1-4 years old, please bring the parent or guardian's valid ID along with the child's documents.
                    </div>
                </div>
            `;
        }

        const serviceOptions = {
            'reg': 'National ID Registration',
            'updating': 'Correction/Updating',
            'inquiry': 'Status Inquiry / Retrieval Of TRN / Other Concern'
        };

        const requirementsContent = {
            'reg': `<div class="requirements-grid">
                <div class="req-card">
                    <ul class="req-docs">
                        <li><strong>PRIMARY DOCUMENTS:</strong>
                            <ol style="margin-top: 5px; margin-left: 20px; list-style: decimal;">
                                <li>PSA-issued Certificate of Live Birth and one (1) government-issued identification document which bears the full name, front-facing photograph, and signature or thumbmark;</li>
                                <li>Philippine Passport or ePassport issued by the Department of Foreign Affairs (DFA);</li>
                                <li>Unified Multi-purpose Identification (UMID) Card issued by the Government Service Insurance System (GSIS) or Social Security System (SSS); or</li>
                                <li>Student's License Permit or Non-Professional/Professional Driver's License issued by the Land Transportation Office (LTO).</li>
                            </ol>
                        </li>
                        <li><strong>SECONDARY DOCUMENTS:</strong>
                            <ol style="margin-top: 5px; margin-left: 20px; list-style: decimal; max-height: 200px; overflow-y: auto;">
                                <li>PSA-issued Certificate of Live Birth/National Statistics Office (NSO)-issued Certificate of Live Birth with Birth Reference Number (BreN);</li>
                                <li>Barangay Certificate - Must contain full name, birthdate, father's full name, mother's maiden name, address, front facing photograph and applicant signature/thumbmark.</li>
                                <li>Local Civil Registry Office (LCRO)-issued Certificate of Live Birth;</li>
                                <li>PSA-issued Report of Birth;</li>
                                <li>PSA-issued Certificate of Foundling;</li>
                                <li>Integrated Bar of the Philippines (IBP) Identification Card;</li>
                                <li>Professional Regulatory Commission (PRC) ID;</li>
                                <li>Seaman's Book (Seafarer's Record Book);</li>
                                <li>Overseas Workers Welfare Administration (OWWA) ID;</li>
                                <li>Senior Citizen's ID;</li>
                                <li>SSS ID;</li>
                                <li>Pantawid Pamilyang Pilipino Program (4Ps) ID;</li>
                                <li>License to Own or Possess Firearms (LTOPF);</li>
                                <li>NBI Clearance;</li>
                                <li>Police Clearance/ID;</li>
                                <li>Solo Parent's ID;</li>
                                <li>Person with Disability (PWD) ID;</li>
                                <li>Voter's ID;</li>
                                <li>Postal ID;</li>
                                <li>Taxpayer Identification Number (TIN);</li>
                                <li>PhilHealth ID;</li>
                                <li>Special Resident Retiree's Visa (SRRV);</li>
                                <li>National ID from other countries;</li>
                                <li>Residence ID from other countries;</li>
                                <li>Professional Identification Card;</li>
                                <li>Eligibility Card; and</li>
                                <li>Dependent's ID.</li>
                            </ol>
                        </li>
                    </ul>
                    <div class="warning-note">Bring <strong>original documents</strong>. No photocopies accepted for primary validation.</div>
                </div>
            </div>`,
            'updating': `<h4><i class="fas fa-pen"></i> Correction/Updating</h4><p><strong>Required Documents by Field:</strong></p><ul><li><strong>First/Last Name:</strong> Birth Certificate, Marriage Certificate</li><li><strong>Sex/DOB:</strong> Birth Certificate</li><li><strong>Address:</strong> Barangay Certificate + Billing</li></ul><p><em>⚠️ Bring ORIGINAL copies.</em></p>`,
            'inquiry': `<h4><i class="fas fa-question-circle"></i> Status Inquiry / Retrieval Of TRN / Other Concern</h4><p><strong>REQUIREMENTS:</strong></p><ul><li>Valid Government-issued ID</li><li>Any previous transaction slip (if available)</li><li>For TRN Retrieval: Provide full name, date of birth, and sex for verification</li><li><strong>TRN must be exactly 29 digits</strong></li></ul><p><em>⚠️ Please bring any reference documents you may have for faster processing.</em></p>`
        };

        const identityReminders = {
            'reg': 'The person named below must be the one registering for the National ID.',
            'updating': 'The person named below must be the one requesting the correction/update.',
            'inquiry': 'The person named below must be the one requesting status inquiry, TRN retrieval, or other concern.'
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

        function formatDisplayDateTime(date, timeSlotLabel) {
            if (!date) return 'No date selected';
            if (!timeSlotLabel) return formatDisplayDate(date);
            return `${formatDisplayDate(date)} at ${timeSlotLabel}`;
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

        function allRequirementsAcknowledged() {
            return clients.every(c => c.reqAcknowledged);
        }

        function updateReqSummary() {
            const list = document.getElementById('reqSummaryList');
            const banner = document.getElementById('reqSummaryBanner');
            let html = '';
            let allAcked = true;
            clients.forEach((c, i) => {
                const svc = c.service;
                const acked = c.reqAcknowledged;
                if (!acked) allAcked = false;
                const displayName = getFullName(c);
                const age = calculateAge(c.birthdate);
                const ageIndicator = (age !== null && age >= 1 && age <= 4) ? ' (Child 1-4 yrs old)' : '';
                html +=
                    `<div class="req-item"><i class="fas ${acked ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="color:${acked ? 'var(--success)' : 'var(--warning)'};"></i> ${displayName || 'Person ' + (i + 1)} - ${getServiceName(svc)}${ageIndicator} ${acked ? '✓' : '(Pending)'}</div>`;
            });
            list.innerHTML = html;
            banner.classList.toggle('complete', allAcked);
            const nextBtn = document.getElementById('nextToSchedule');
            if (nextBtn) nextBtn.disabled = !allAcked;
        }

        async function loadAvailableDates() {
            const clientCount = clients.length;
            const services = [...new Set(clients.map(c => c.service))];
            let url =
                `{{ route('client.appointment.available-dates') }}?month=${currentMonth + 1}&year=${currentYear}&client_count=${clientCount}&services=${services.join(',')}`;
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

        function renderTimeSlots(timeSlots) {
            const timeSlotsGrid = document.getElementById('timeSlotsGrid');

            if (!timeSlots || timeSlots.length === 0) {
                timeSlotsGrid.innerHTML =
                    '<div class="time-slots-loading"><i class="fas fa-info-circle"></i> No available time slots for your selected services on this date.</div>';
                return;
            }

            const selectedServices = [...new Set(clients.map(c => c.service))];

            let html = '';
            timeSlots.forEach(slot => {
                const isSelected = selectedTimeSlot === slot.id;
                let allServicesAvailable = true;

                if (slot.service_availability) {
                    for (const service of selectedServices) {
                        const availableSlots = slot.service_availability[service] || 0;
                        if (availableSlots <= 0) {
                            allServicesAvailable = false;
                            break;
                        }
                    }
                }

                if (allServicesAvailable) {
                    html += `
                        <div class="time-slot-card ${isSelected ? 'selected' : ''}" 
                             data-slot-id="${slot.id}" data-slot-label="${slot.slot_label}" data-available="true">
                            <div class="time-slot-time">${slot.slot_label}</div>
                        </div>`;
                }
            });

            if (html === '') {
                timeSlotsGrid.innerHTML =
                    '<div class="time-slots-loading"><i class="fas fa-info-circle"></i> No available time slots for your selected services on this date.</div>';
            } else {
                timeSlotsGrid.innerHTML = html;

                document.querySelectorAll('.time-slot-card').forEach(card => {
                    card.addEventListener('click', () => {
                        if (card.dataset.available !== 'true') return;
                        document.querySelectorAll('.time-slot-card').forEach(c => c.classList
                            .remove('selected'));
                        card.classList.add('selected');
                        selectedTimeSlot = parseInt(card.dataset.slotId);
                        selectedTimeSlotLabel = card.dataset.slotLabel;
                    });
                });
            }
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
                html += `<div class="${cls}" data-date="${dateKey}" onclick="window.selectDate('${dateKey}')">`;
                html += `<div class="day-number">${d}</div></div>`;
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

        async function loadTimeSlots(date) {
            const timeSlotsGrid = document.getElementById('timeSlotsGrid');
            const timeSlotsContainer = document.getElementById('timeSlotsContainer');
            if (!date) {
                timeSlotsContainer.style.display = 'none';
                return;
            }
            timeSlotsContainer.style.display = 'block';
            timeSlotsGrid.innerHTML =
                '<div class="time-slots-loading"><i class="fas fa-spinner fa-spin"></i> Loading available time slots...</div>';
            try {
                const selectedServices = [...new Set(clients.map(c => c.service))];
                const url =
                    `{{ route('client.appointment.available-time-slots') }}?date=${date}&services=${selectedServices.join(',')}&client_count=${clients.length}`;
                const response = await fetch(url);
                const data = await response.json();
                if (data.success && data.time_slots && data.time_slots.length > 0) {
                    availableTimeSlots = data.time_slots;
                    renderTimeSlots(availableTimeSlots);
                } else {
                    timeSlotsGrid.innerHTML =
                        '<div class="time-slots-loading"><i class="fas fa-info-circle"></i> No available time slots for this date.</div>';
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
                timeSlotsGrid.innerHTML =
                    '<div class="time-slots-loading"><i class="fas fa-exclamation-triangle"></i> Failed to load time slots.</div>';
            }
        }

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

        // Helper function to check if birthdate is within 1-4 years old
        function isChildAgeRange(birthdate) {
            if (!birthdate) return false;
            const age = calculateAge(birthdate);
            return age !== null && age >= 1 && age <= 4;
        }

        function renderClients() {
            const container = document.getElementById('clientsList');
            container.innerHTML = '';
            clients.forEach((c, i) => {
                const svc = c.service;
                const reminder = identityReminders[svc] || 'Please ensure the information is accurate.';
                const showTrn = shouldShowTrnForClient(c);
                const trnData = clientTrnData[c.id] || {
                    hasTrn: null,
                    trnNumber: '',
                    isValid: false
                };
                const isChild = isChildAgeRange(c.birthdate);
                const ageBadge = isChild ? '<span class="age-requirements-badge" style="display: inline-block; margin-left: 10px; background-color: #ffc107; color: #000; padding: 2px 8px; border-radius: 20px; font-size: 11px;"><i class="fas fa-child"></i> Child (1-4 yrs) - Additional Requirements</span>' : '';
                
                const card = document.createElement('div');
                card.className = 'client-card';
                card.innerHTML = `
                    <div class="client-header">
                        <span class="client-title"><i class="fas fa-user-circle"></i> Person ${i + 1} ${ageBadge}</span>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-view-req" data-id="${c.id}"><i class="fas fa-book"></i> View Requirements</button>
                            ${clients.length > 1 ? `<button class="btn-remove-client" data-id="${c.id}"><i class="fas fa-trash-alt"></i></button>` : ''}
                        </div>
                    </div>
                    <div class="identity-reminder"><i class="fas fa-id-card"></i> <strong>Important:</strong> ${reminder}</div>
                    ${isChild ? `<div class="age-requirements-badge"><i class="fas fa-child"></i> This person is ${calculateAge(c.birthdate)} year(s) old. Please review the additional requirements for children aged 1-4 years.</div>` : ''}
                    <div class="form-row">
                        <div class="form-col"><label>First Name <span style="color: var(--danger);">*</span></label><input class="client-firstname" data-id="${c.id}" value="${escapeHtml(c.firstName) || ''}" placeholder="First Name"></div>
                        <div class="form-col"><label>Middle Name</label><input class="client-middlename" data-id="${c.id}" value="${escapeHtml(c.middleName) || ''}" placeholder="Middle Name"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-col"><label>Last Name <span style="color: var(--danger);">*</span></label><input class="client-lastname" data-id="${c.id}" value="${escapeHtml(c.lastName) || ''}" placeholder="Last Name"></div>
                        <div class="form-col"><label>Suffix</label><select class="client-suffix" data-id="${c.id}"><option value=""> None </option><option value="Jr." ${c.suffix === 'Jr.' ? 'selected' : ''}>Jr.</option><option value="Sr." ${c.suffix === 'Sr.' ? 'selected' : ''}>Sr.</option><option value="I" ${c.suffix === 'I' ? 'selected' : ''}>I</option><option value="II" ${c.suffix === 'II' ? 'selected' : ''}>II</option><option value="III" ${c.suffix === 'III' ? 'selected' : ''}>III</option><option value="IV" ${c.suffix === 'IV' ? 'selected' : ''}>IV</option><option value="V" ${c.suffix === 'V' ? 'selected' : ''}>V</option></select></div>
                    </div>
                    <div class="form-row">
                        <div class="form-col"><label>Sex <span style="color: var(--danger);">*</span></label><select class="client-sex" data-id="${c.id}"><option value="Male" ${c.sex === 'Male' ? 'selected' : ''}>Male</option><option value="Female" ${c.sex === 'Female' ? 'selected' : ''}>Female</option></select></div>
                        <div class="form-col"><label>Birthdate <span style="color: var(--danger);">*</span></label>
                        <input type="date" class="client-birthdate" data-id="${c.id}" value="${c.birthdate || ''}" 
                            oninput="validateBirthdate(this)" 
                            onchange="validateBirthdate(this)">
                        </div>
                    </div>
                    <div class="form-col" style="margin-top: 12px;">
                        <label>Service <span style="color: var(--danger);">*</span></label>
                        <select class="client-service" data-id="${c.id}">
                            <option value="reg" ${c.service === 'reg' ? 'selected' : ''}>National ID Registration</option>
                            <option value="updating" ${c.service === 'updating' ? 'selected' : ''}>Correction/Updating</option>
                            <option value="inquiry" ${c.service === 'inquiry' ? 'selected' : ''}>Status Inquiry / Retrieval Of TRN / Other Concern</option>
                        </select>
                    </div>
                    ${showTrn ? createTrnHtml(c.id, trnData.hasTrn, trnData.trnNumber) : ''}
                    <div class="req-ack-row ${c.reqAcknowledged ? 'acknowledged' : ''}">
                        <input type="checkbox" class="req-ack" data-id="${c.id}" ${c.reqAcknowledged ? 'checked' : ''}>
                        <label>I have read and understood the requirements for <strong>${getServiceName(svc)}</strong>${isChild ? ' (Children 1-4 years old)' : ''}. I will bring all necessary documents.</label>
                    </div>
                `;
                container.appendChild(card);
            });
            attachClientEvents();
            document.getElementById('clientCount').textContent = clients.length;
            updateReqSummary();
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
                if (c) {
                    if (ev.target.value) {
                        const year = ev.target.value.split('-')[0];
                        if (year && year.length !== 4) {
                            alert('Please enter a valid year (YYYY format, e.g., 1990)');
                            ev.target.value = '';
                            return;
                        }
                    }
                    c.birthdate = ev.target.value;
                    renderClients();
                }
            }));
            document.querySelectorAll('.client-service').forEach(e => e.addEventListener('change', (ev) => {
                const c = clients.find(x => x.id == ev.target.dataset.id);
                if (c) {
                    c.service = ev.target.value;
                    c.reqAcknowledged = false;
                    if (c.service !== 'inquiry') delete clientTrnData[c.id];
                    else if (!clientTrnData[c.id]) clientTrnData[c.id] = {
                        hasTrn: null,
                        trnNumber: '',
                        isValid: false
                    };
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
                const svc = client.service;
                const isChild = isChildAgeRange(client.birthdate);
                
                let content = '';
                
                // For children 1-4 years old with Registration service, show ONLY child requirements (REPLACE, not append)
                if (isChild && svc === 'reg') {
                    content = getChildRequirements();
                    modalTitle.textContent = `${getServiceName(svc)} Requirements for Children (1-4 years old)`;
                } else {
                    // For everyone else, show the standard requirements
                    content = requirementsContent[svc] || '<p>Requirements not available.</p>';
                    modalTitle.textContent = `${getServiceName(svc)} Requirements`;
                }
                
                modalBody.innerHTML = content;
                reqModal.style.display = 'flex';
            }));
            document.querySelectorAll('.btn-remove-client').forEach(b => b.addEventListener('click', (ev) => {
                const id = ev.target.closest('.btn-remove-client').dataset.id;
                clients = clients.filter(c => c.id != id);
                delete clientTrnData[id];
                renderClients();
                updateReqSummary();
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

        // ==================== EVENT LISTENERS ====================

        document.getElementById('agreePrivacyBtn').onclick = () => privacyModal.style.display = 'none';
        document.getElementById('startBookingBtn').onclick = () => {
            showSection(sections.clients);
            setActiveStep(2);
        };
        document.getElementById('addClientBtn').onclick = () => {
            if (clients.length >= MAX_CLIENTS) {
                alert(`Maximum ${MAX_CLIENTS} persons only.`);
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
                if (!c.service) {
                    alert('Please select service for all clients');
                    return;
                }
                if (!validateTrnForClient(c)) {
                    const clientName = getFullName(c) || `Person ${clients.indexOf(c) + 1}`;
                    alert(
                        `${clientName}: Please indicate whether you have a TRN. If YES, please enter the exact 29-digit TRN number or scan the QR code.`
                    );
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
            const mobileSuffix = document.getElementById('contactMobile').value;

            if (!name || !mobileSuffix) {
                alert('Contact name and mobile number are required.');
                return;
            }

            if (mobileSuffix.length !== 10 || !mobileSuffix.startsWith('9')) {
                alert(
                    'Please enter a valid mobile number. Format: 9XXXXXXXXX (10 digits starting with 9, e.g., 9123456789)'
                );
                return;
            }

            const fullMobile = '+63' + mobileSuffix;

            document.getElementById('reviewClientCount').textContent = clients.length;
            let reviewClientsHtml = '';
            clients.forEach((c, i) => {
                const isChild = isChildAgeRange(c.birthdate);
                const childBadge = isChild ? ' <span style="background: #ffc107; color: #000; padding: 2px 6px; border-radius: 12px; font-size: 10px; margin-left: 5px;"><i class="fas fa-child"></i> Child (1-4 yrs)</span>' : '';
                reviewClientsHtml += `<div class="client-summary-item"><strong>${i + 1}. ${escapeHtml(getFullName(c))}</strong> - ${getServiceName(c.service)}${childBadge}</div>`;
            });
            document.getElementById('reviewClientsList').innerHTML = reviewClientsHtml;
            document.getElementById('reviewDateTime').textContent = formatDisplayDateTime(selectedDate,
                selectedTimeSlotLabel);
            document.getElementById('reviewContactName').textContent = escapeHtml(name);
            document.getElementById('reviewContactEmail').textContent = document.getElementById('contactEmail')
                .value || 'Not provided';
            document.getElementById('reviewContactMobile').textContent = fullMobile;
            showSection(sections.review);
            setActiveStep(5);
        };
        document.getElementById('backToContact').onclick = () => {
            showSection(sections.contact);
            setActiveStep(4);
        };
        document.getElementById('nextToConfirm').onclick = () => {
            const mobileSuffix = document.getElementById('contactMobile').value;
            const fullMobile = mobileSuffix ? '+63' + mobileSuffix : '';

            document.getElementById('sumClients').textContent = clients.length + ' person(s)';
            document.getElementById('sumDateTime').textContent = formatDisplayDateTime(selectedDate,
                selectedTimeSlotLabel);
            document.getElementById('sumContact').textContent = document.getElementById('contactName').value +
                ' / ' + fullMobile;
            showSection(sections.confirm);
            setActiveStep(6);
        };
        document.getElementById('backToReview').onclick = () => {
            showSection(sections.review);
            setActiveStep(5);
        };

        const confirmCheckbox = document.getElementById('confirmCheckbox');
        const submitBtn = document.getElementById('submitRequestBtn');

        if (confirmCheckbox && submitBtn) {
            confirmCheckbox.addEventListener('change', function(e) {
                submitBtn.disabled = !e.target.checked;
            });
        }

        document.getElementById('closeReqModal').onclick = () => reqModal.style.display = 'none';
        document.getElementById('understandBtn').onclick = () => reqModal.style.display = 'none';
        
        document.getElementById('closeSuccessModal').onclick = () => {
            successModal.style.display = 'none';
            if (window.parent && window.parent.location) {
                window.parent.location.href = '/';
            } else {
                window.location.href = '/';
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
                } else if (target === 'contact') {
                    showSection(sections.contact);
                    setActiveStep(4);
                }
            });
        });

        renderClients();
        
        window.addEventListener('click', (e) => {
            if (e.target === reqModal) reqModal.style.display = 'none';
            if (e.target === successModal) {
                successModal.style.display = 'none';
                if (window.parent && window.parent.location) {
                    window.parent.location.href = '/';
                } else {
                    window.location.href = '/';
                }
            }
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (reqModal.style.display !== 'none') reqModal.style.display = 'none';
                if (successModal.style.display !== 'none') {
                    successModal.style.display = 'none';
                    if (window.parent && window.parent.location) {
                        window.parent.location.href = '/';
                    } else {
                        window.location.href = '/';
                    }
                }
            }
        });
        
        loadLocationFromLandingPage();

        submitBtn.onclick = async () => {
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
                    service: c.service
                };
                if (shouldShowTrnForClient(c)) {
                    const trnData = clientTrnData[c.id];
                    if (trnData) {
                        clientInfo.has_trn = trnData.hasTrn;
                        clientInfo.trn_number = trnData.hasTrn && trnData.isValid ? trnData
                            .trnNumber : null;
                    }
                }
                return clientInfo;
            });

            const mobileSuffix = document.getElementById('contactMobile').value;
            let fullMobileNumber = '';

            if (!mobileSuffix || mobileSuffix.length !== 10 || !mobileSuffix.startsWith('9')) {
                alert(
                    'Please enter a valid mobile number. Format: 9XXXXXXXXX (10 digits starting with 9, e.g., 9123456789)'
                );
                return;
            }

            fullMobileNumber = '+63' + mobileSuffix;

            const formData = {
                appointment_type: 'multiple',
                appointment_date: selectedDate,
                appointment_time_slot_id: selectedTimeSlot,
                contact_name: document.getElementById('contactName').value,
                contact_email: document.getElementById('contactEmail').value || null,
                contact_mobile: fullMobileNumber,
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
                    let clientsHtml = '';
                    if (result.appointment.clients_list && result.appointment.clients_list.length > 0) {
                        clientsHtml =
                            '<div style="text-align: left; margin-top: 10px;"><strong>Client Details:</strong><ul style="margin-top: 5px;">';
                        result.appointment.clients_list.forEach(client => {
                            clientsHtml += `<li><strong>${escapeHtml(client.name)}</strong><br>
                                    <small>Client Number: ${client.client_number}</small><br>
                                    <small>Service: ${client.service_name}</small>
                                   </li>`;
                        });
                        clientsHtml += '</ul></div>';
                    }   

                    document.getElementById('successDetails').innerHTML = `
                        <div style="text-align: left;">
                            <p><strong>Appointment Number:</strong> ${result.appointment.number}</p>
                            <p><strong>Reference Code:</strong> ${result.appointment.reference_code}</p>
                            <p><strong>Date & Time:</strong> ${result.appointment.date} at ${result.appointment.time || 'Selected time slot'}</p>
                            <p><strong>Contact Person:</strong> ${escapeHtml(result.appointment.contact_name)}</p>
                            <p><strong>Contact Number:</strong> ${result.appointment.contact_mobile}</p>
                            ${result.appointment.contact_email ? `<p><strong>Email:</strong> ${result.appointment.contact_email}</p>` : ''}
                            <p><strong>Total Clients:</strong> ${result.appointment.clients_count} person(s)</p>
                            ${clientsHtml}
                            <hr>
                            <p><small>A confirmation ${result.email_sent ? 'email has been sent' : 'SMS will be sent'} to your registered contact.</small></p>
                            <p><small style="color: #dc3545;">⚠️ Please save your Reference Code for verification.</small></p>
                        </div>
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
    })();

    // ==================== DOWNLOAD FUNCTIONS ====================
    async function captureSuccessModal() {
        const successDetails = document.getElementById('successDetails');
        if (!successDetails) return null;

        const receiptContainer = document.createElement('div');
        receiptContainer.style.cssText = `
            background: white;
            padding: 30px;
            border-radius: 12px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
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
                <img src="{{ asset('images/psa-logo.png') }}" alt="PSA Logo" style="height: 60px; width: auto; margin-bottom: 10px;">
                <h2 style="color: #2c5f8a; margin: 0;">Philippine Statistics Authority</h2>
                <h3 style="color: #4a5568; margin: 5px 0;">National ID System (PhilSys)</h3>
                <p style="color: #718096; margin: 5px 0;">Appointment Confirmation</p>
            </div>
            <div style="border-top: 2px solid #2c5f8a; margin: 10px 0;"></div>
            <div style="padding: 10px 0;">
                ${successDetails.innerHTML}
            </div>
            <div style="border-top: 1px solid #e2e8f0; margin: 10px 0;"></div>
            <div style="text-align: center; padding-top: 10px;">
                <p style="color: #718096; font-size: 12px; margin: 5px 0;">This is a system-generated confirmation.</p>
                <p style="color: #718096; font-size: 12px; margin: 5px 0;">Generated on: ${formattedDateTime}</p>
                <p style="color: #718096; font-size: 12px; margin: 5px 0;">© ${new Date().getFullYear()} Philippine Statistics Authority</p>
            </div>
        `;

        return receiptContainer;
    }

    document.getElementById('downloadPngBtn').onclick = async () => {
        try {
            const btn = document.getElementById('downloadPngBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;

            if (typeof html2canvas === 'undefined') {
                await new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src =
                        'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            const receipt = await captureSuccessModal();
            if (!receipt) {
                alert('Unable to generate receipt');
                return;
            }

            document.body.appendChild(receipt);
            const canvas = await html2canvas(receipt, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true
            });
            document.body.removeChild(receipt);

            const link = document.createElement('a');
            link.download = `appointment-confirmation-${Date.now()}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();

            btn.innerHTML = originalText;
            btn.disabled = false;
        } catch (error) {
            console.error('PNG download error:', error);
            alert('Failed to generate PNG. Please try again.');
            const btn = document.getElementById('downloadPngBtn');
            btn.innerHTML = '<i class="fas fa-image"></i> Download PNG';
            btn.disabled = false;
        }
    };

    document.getElementById('downloadPdfBtn').onclick = async () => {
        try {
            const btn = document.getElementById('downloadPdfBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;

            const receipt = await captureSuccessModal();
            if (!receipt) {
                alert('Unable to generate receipt');
                return;
            }

            document.body.appendChild(receipt);

            const opt = {
                margin: [0.5, 0.5, 0.5, 0.5],
                filename: `appointment-confirmation-${Date.now()}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            await html2pdf().set(opt).from(receipt).save();
            document.body.removeChild(receipt);

            btn.innerHTML = originalText;
            btn.disabled = false;
        } catch (error) {
            console.error('PDF download error:', error);
            alert('Failed to generate PDF. Please try again.');
            const btn = document.getElementById('downloadPdfBtn');
            btn.innerHTML = '<i class="fas fa-file-pdf"></i> Download PDF';
            btn.disabled = false;
        }
    };
</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</body>

</html>