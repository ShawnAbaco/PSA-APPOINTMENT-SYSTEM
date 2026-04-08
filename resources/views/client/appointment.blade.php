<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>National ID System · Appointment</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }

        body {
            background: #f2f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 16px 12px;
        }

        /* privacy overlay / modal */
        .privacy-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
        }
        .privacy-modal {
            background: white;
            max-width: 580px;
            width: 100%;
            border-radius: 24px;
            padding: 24px 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: fadeIn 0.2s;
            max-height: 85vh;
            overflow-y: auto;
        }
        .privacy-modal h2 {
            font-size: clamp(1.3rem, 5vw, 1.7rem);
            color: #0b2f5c;
            margin-bottom: 16px;
            border-left: 5px solid #1a73e8;
            padding-left: 16px;
        }
        .privacy-modal p {
            color: #2c3e50;
            line-height: 1.5;
            margin-bottom: 14px;
            font-size: clamp(0.9rem, 3.5vw, 0.95rem);
        }
        .privacy-modal .legal-ref {
            font-weight: 600;
            color: #0b2f5c;
        }
        .btn-agree {
            background: #0b2f5c;
            color: white;
            border: none;
            font-weight: 600;
            font-size: clamp(1rem, 4vw, 1.2rem);
            padding: 16px 20px;
            width: 100%;
            border-radius: 50px;
            margin-top: 20px;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid #1e4a7a;
            -webkit-tap-highlight-color: transparent;
        }
        .btn-agree:active {
            background: #1a3f6a;
            transform: scale(0.98);
        }

        /* main appointment card */
        .appointment-card {
            max-width: 880px;
            width: 100%;
            background: white;
            border-radius: 24px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #e9ecf2;
        }

        /* header with logos - mobile optimized */
        .card-header {
            padding: 16px 16px 12px;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .logos {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .logo-placeholder {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            color: #0b2f5c;
            font-size: clamp(0.8rem, 3.5vw, 0.95rem);
        }
        .logo-placeholder i {
            font-size: clamp(22px, 6vw, 28px);
            color: #1e4a7a;
        }
        .logo-sep {
            font-size: 20px;
            color: #a0b8cc;
            font-weight: 300;
        }
        .header-title {
            margin-left: auto;
            font-weight: 600;
            color: #1e3a5f;
            font-size: clamp(0.9rem, 4vw, 1rem);
        }
        .center-name {
            background: #e9f0fa;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: clamp(0.75rem, 3vw, 0.9rem);
            color: #0b2f5c;
            width: 100%;
            margin-top: 8px;
            text-align: center;
        }

        /* stepper - mobile scrollable - now 5 steps */
        .stepper {
            display: flex;
            padding: 16px 8px 8px;
            gap: 6px;
            border-bottom: 1px solid #e6ecf3;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .stepper::-webkit-scrollbar {
            display: none;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #6c7a8a;
            font-weight: 500;
            font-size: clamp(0.7rem, 3vw, 0.85rem);
            flex-shrink: 0;
        }
        .step .step-num {
            width: 24px;
            height: 24px;
            background: #eef2f7;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #2c3e50;
            font-size: 0.8rem;
        }
        .step.active .step-num {
            background: #0b2f5c;
            color: white;
        }
        .step.active {
            color: #0b2f5c;
        }
        .step.completed .step-num {
            background: #1e6f3f;
            color: white;
        }
        .step.completed .step-num::after {
            content: '✓';
        }
        .step.completed .step-num span {
            display: none;
        }

        /* content body */
        .content-body {
            padding: 20px 16px 24px;
        }

        .booking-note {
            background: #f8fafd;
            padding: 12px 14px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-size: clamp(0.8rem, 3.5vw, 0.9rem);
            color: #1e3a5f;
            border: 1px solid #dde5ef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* form sections */
        .section-title {
            font-weight: 700;
            font-size: clamp(1.1rem, 5vw, 1.25rem);
            margin-bottom: 16px;
            color: #0b2f5c;
        }

        .service-selector {
            margin-bottom: 24px;
        }

        select, input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #cfdde9;
            border-radius: 16px;
            font-size: 16px;
            background: white;
            transition: 0.15s;
            -webkit-appearance: none;
            appearance: none;
        }
        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230b2f5c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            padding-right: 40px;
        }
        select:focus, input:focus {
            border-color: #1a73e8;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
        }

        .service-desc {
            background: #f3f9ff;
            padding: 14px 16px;
            border-radius: 16px;
            margin: 16px 0 12px;
            border-left: 4px solid #1a73e8;
            font-size: clamp(0.85rem, 3.5vw, 0.95rem);
        }
        .service-desc p {
            margin-bottom: 6px;
        }
        .badge-hours {
            font-size: clamp(0.75rem, 3vw, 0.85rem);
            color: #445e77;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .btn-requirements {
            background: none;
            border: 1.5px solid #0b2f5c;
            color: #0b2f5c;
            padding: 12px 20px;
            border-radius: 50px;
            font-weight: 600;
            margin: 12px 0 10px;
            cursor: pointer;
            font-size: clamp(0.9rem, 4vw, 1rem);
            transition: 0.1s;
            width: 100%;
            -webkit-tap-highlight-color: transparent;
        }
        .btn-requirements:active {
            background: #0b2f5c;
            color: white;
        }

        .btn-next {
            background: #0b2f5c;
            color: white;
            font-weight: 600;
            border: none;
            padding: 16px 20px;
            border-radius: 50px;
            font-size: clamp(1rem, 4vw, 1.1rem);
            cursor: pointer;
            width: 100%;
            margin-top: 12px;
            -webkit-tap-highlight-color: transparent;
        }
        .btn-next:active {
            background: #1a3f6a;
            transform: scale(0.98);
        }

        /* MODERN CALENDAR STYLES */
        .calendar-container {
            background: #fafcff;
            border-radius: 20px;
            padding: 16px;
            margin-bottom: 20px;
            border: 1px solid #e0e8f0;
        }
        
        .calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        
        .calendar-month-year {
            font-weight: 700;
            font-size: 1.1rem;
            color: #0b2f5c;
        }
        
        .calendar-nav {
            display: flex;
            gap: 8px;
        }
        
        .calendar-nav-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid #cfdde9;
            background: white;
            color: #0b2f5c;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.15s;
            -webkit-tap-highlight-color: transparent;
        }
        
        .calendar-nav-btn:active {
            background: #e9f0fa;
        }
        
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            margin-bottom: 8px;
        }
        
        .weekday {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c7a8a;
            padding: 8px 0;
        }
        
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }
        
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 500;
            color: #2c3e50;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.15s;
            background: white;
            border: 1px solid transparent;
            -webkit-tap-highlight-color: transparent;
        }
        
        .calendar-day.empty {
            cursor: default;
        }
        
        .calendar-day.available {
            background: #e8f4fd;
            border-color: #b8d4f0;
            color: #0b2f5c;
        }
        
        .calendar-day.available:hover {
            background: #d0e8ff;
        }
        
        .calendar-day.selected {
            background: #0b2f5c;
            color: white;
            border-color: #0b2f5c;
        }
        
        .calendar-day.disabled {
            color: #b0c0d0;
            cursor: not-allowed;
            background: #f5f8fc;
        }
        
        .calendar-day.unavailable {
            color: #b0c0d0;
            cursor: not-allowed;
            background: #f5f8fc;
            text-decoration: line-through;
        }
        
        /* Time slots - chip style */
        .time-slots-section {
            margin-top: 20px;
        }
        
        .time-slots-label {
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }
        
        .time-slot-chip {
            background: white;
            border: 1.5px solid #cfdde9;
            border-radius: 40px;
            padding: 12px 8px;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
            color: #2c3e50;
            cursor: pointer;
            transition: 0.15s;
            -webkit-tap-highlight-color: transparent;
        }
        
        .time-slot-chip.available {
            border-color: #0b2f5c;
            color: #0b2f5c;
        }
        
        .time-slot-chip.available:hover {
            background: #f0f7ff;
        }
        
        .time-slot-chip.selected {
            background: #0b2f5c;
            color: white;
            border-color: #0b2f5c;
        }
        
        .time-slot-chip.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f0f4fa;
        }
        
        .selected-date-display {
            background: #f3f9ff;
            padding: 12px 16px;
            border-radius: 16px;
            margin: 16px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .selected-date-label {
            font-weight: 600;
            color: #0b2f5c;
        }
        
        .selected-date-value {
            color: #1e3a5f;
        }
        
        .selected-time-value {
            background: #0b2f5c;
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: auto;
        }

        /* Review section styles */
        .review-container {
            background: #f8fafd;
            border-radius: 20px;
            padding: 20px 16px;
            margin-bottom: 20px;
        }
        
        .review-section {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e0e8f0;
        }
        
        .review-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .review-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .review-section-title {
            font-weight: 700;
            color: #0b2f5c;
            font-size: 1rem;
        }
        
        .edit-link {
            color: #1a73e8;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            background: none;
            border: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        .edit-link i {
            margin-right: 4px;
        }
        
        .review-detail-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .review-detail-label {
            width: 100px;
            color: #5e7182;
            font-weight: 500;
        }
        
        .review-detail-value {
            flex: 1;
            color: #1e3a5f;
            font-weight: 500;
        }
        
        .confirmation-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin: 20px 0;
            padding: 16px;
            background: #f3f9ff;
            border-radius: 16px;
        }
        
        .confirmation-checkbox input {
            width: 20px;
            height: 20px;
            min-height: 20px;
            margin-top: 2px;
            accent-color: #0b2f5c;
        }
        
        .confirmation-checkbox label {
            font-size: 0.9rem;
            color: #1e3a5f;
            line-height: 1.5;
        }

        .summary-box {
            background: #f8fafd;
            border-radius: 20px;
            padding: 18px 16px;
            margin: 24px 0 20px;
        }
        .summary-row {
            display: flex;
            margin-bottom: 12px;
            font-size: clamp(0.9rem, 3.5vw, 1rem);
            flex-wrap: wrap;
        }
        .summary-label {
            width: 85px;
            font-weight: 600;
            color: #2e475c;
        }
        .summary-value {
            flex: 1;
            word-break: break-word;
        }

        .ready-badge {
            display: inline-block;
            background: #e2f0e6;
            color: #1e6f3f;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: clamp(0.7rem, 3vw, 0.8rem);
            font-weight: 600;
            margin-right: 8px;
            margin-bottom: 6px;
        }

        .reminder {
            font-size: clamp(0.75rem, 3vw, 0.85rem);
            color: #6e7e8e;
            margin-top: 16px;
            text-align: center;
            line-height: 1.5;
        }

        .footer-note {
            border-top: 1px solid #e0e8f0;
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
            color: #3a5a78;
            font-size: clamp(0.7rem, 3vw, 0.8rem);
        }
        @media (min-width: 600px) {
            .footer-note {
                flex-direction: row;
                justify-content: space-between;
                text-align: left;
                padding: 16px 24px;
            }
        }

        /* requirement modal - mobile friendly */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 16px;
        }
        .modal-content {
            background: white;
            max-width: 700px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 24px;
            padding: 22px 18px;
            -webkit-overflow-scrolling: touch;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .modal-header h3 {
            color: #0b2f5c;
            font-size: clamp(1.1rem, 5vw, 1.3rem);
        }
        .close-modal {
            font-size: 28px;
            cursor: pointer;
            color: #5f7d9c;
            padding: 4px;
            -webkit-tap-highlight-color: transparent;
        }
        .req-list {
            margin: 16px 0;
            font-size: clamp(0.85rem, 3.5vw, 0.95rem);
        }
        .req-list h4 {
            color: #0b2f5c;
            margin: 16px 0 8px;
            font-size: 1rem;
        }
        .req-list ul {
            padding-left: 20px;
            margin-bottom: 12px;
        }
        .req-list li {
            margin-bottom: 4px;
        }
        .btn-understand {
            background: #0b2f5c;
            color: white;
            border: none;
            padding: 16px 20px;
            border-radius: 50px;
            width: 100%;
            font-weight: 600;
            margin-top: 20px;
            cursor: pointer;
            font-size: clamp(0.95rem, 4vw, 1.05rem);
            -webkit-tap-highlight-color: transparent;
        }
        .btn-understand:active {
            background: #1a3f6a;
        }

        .hidden {
            display: none !important;
        }

        /* back buttons styling */
        .back-btn {
            background: transparent !important;
            color: #0b2f5c !important;
            margin-top: 12px !important;
            border: 1.5px solid #cfdde9 !important;
        }
        .back-btn:active {
            background: #f0f4fa !important;
        }

        /* contact note */
        .contact-note {
            font-size: clamp(0.7rem, 3vw, 0.8rem);
            color: #5d7386;
            margin-top: 6px;
        }

        /* improved tap targets */
        button, select, input, .close-modal, .calendar-day, .time-slot-chip {
            min-height: 44px;
        }
        select, input {
            min-height: 52px;
        }

        /* Submit button disabled state */
        .btn-next:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); } 
            to { opacity: 1; transform: translateY(0); }
        }

        /* small screen adjustments */
        @media (max-width: 480px) {
            body {
                padding: 8px 6px;
            }
            .appointment-card {
                border-radius: 20px;
            }
            .card-header {
                padding: 12px 12px 8px;
            }
            .content-body {
                padding: 16px 12px 20px;
            }
            .btn-next, .btn-requirements, .btn-agree {
                padding: 14px 16px;
            }
            .time-slots-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* landscape orientation fix */
        @media (max-height: 500px) and (orientation: landscape) {
            .privacy-modal {
                max-height: 90vh;
            }
        }
    </style>
</head>
<body>

<!-- PRIVACY NOTICE MODAL -->
<div class="privacy-overlay" id="privacyModal">
    <div class="privacy-modal">
        <h2>Privacy Notice – National ID Appointment System</h2>
        <p>This system collects and processes limited personal information solely for the purpose of scheduling, managing, and confirming National ID System appointments, in accordance with the <span class="legal-ref">Data Privacy Act of 2012 (RA 10173)</span> and applicable Philippine Statistics Authority (PSA) policies.</p>
        <p>The personal data collected include your full name, email address or mobile number, selected service, and preferred appointment schedule.</p>
        <p>Your personal data shall be used exclusively for appointment management and communication purposes. These data are stored securely and protected by appropriate administrative, technical, and physical safeguards.</p>
        <p>Your personal data will not be shared or disclosed to unauthorized parties, except to authorized PSA personnel or when required by law.</p>
        <p>As a data subject, you have the right to access, correct, or request deletion of your personal data, subject to applicable laws and regulations.</p>
        <p>For data privacy concerns, you may contact the PSA Data Protection Officer through the official PSA communication channels.</p>
        <p>By proceeding, you confirm that you have read, understood, and voluntarily consent to the collection and processing of your personal data for the stated purpose.</p>
        <button class="btn-agree" id="agreePrivacyBtn">I AGREE</button>
    </div>
</div>

<!-- REQUIREMENTS MODAL -->
<div class="modal-overlay" id="reqModal">
    <div class="modal-content" id="reqModalContent">
        <div class="modal-header">
            <h3 id="modalServiceTitle">Requirements</h3>
            <span class="close-modal" id="closeReqModal">&times;</span>
        </div>
        <div id="modalBodyContent" class="req-list">
            <!-- filled via js -->
        </div>
        <button class="btn-understand" id="understandBtn">I Understand</button>
    </div>
</div>

<!-- MAIN APPOINTMENT CARD -->
<div class="appointment-card" id="mainAppCard">
    <div class="card-header">
        <div class="logos">
            <div class="logo-placeholder"><i class="fas fa-shield-alt"></i> PSA Logo</div>
            <span class="logo-sep">|</span>
            <div class="logo-placeholder"><i class="fas fa-id-card"></i> National ID Logo</div>
        </div>
        <div class="header-title">National ID System</div>
        <div class="center-name"><i class="fas fa-map-pin"></i> PSA CDO - Fixed Registration Center</div>
    </div>

    <!-- stepper - 5 steps -->
    <div class="stepper">
        <div class="step active" id="step1"><span class="step-num">1</span> Service</div>
        <div class="step" id="step2"><span class="step-num">2</span> Schedule</div>
        <div class="step" id="step3"><span class="step-num">3</span> Details</div>
        <div class="step" id="step4"><span class="step-num">4</span> Review</div>
        <div class="step" id="step5"><span class="step-num">5</span> Confirm</div>
    </div>

    <div class="content-body">
        <div class="booking-note">
            <span><i class="far fa-calendar-alt"></i> Appointment Schedule</span>
            <span><strong>Booking until 31 May 2026</strong></span>
        </div>

        <!-- ========= SERVICE SECTION ========= -->
        <div id="sectionService">
            <div class="section-title">Select Service</div>
            <div class="service-selector">
                <label>Select Service *</label>
                <select id="serviceSelect">
                    <option value="">-- Select Service --</option>
                    <option value="reg">National ID Registration</option>
                    <option value="correction">Correction/Updating of Demographic Information</option>
                    <option value="ephilid">Issuance of National ID Paper Form (ePhilID)</option>
                    <option value="trn">Retrieval of TRN / Other Concern</option>
                </select>
                <div class="badge-hours"><i class="far fa-clock"></i> Saturday: CLOSED • Sunday: CLOSED • Booking window: current + next month</div>
            </div>

            <div id="serviceDescBox" class="service-desc">
                <p><i class="fas fa-info-circle"></i> Select a service to see details.</p>
            </div>
            <button class="btn-requirements" id="showReqBtn" style="display:none;">📋 Requirements</button>
            <button class="btn-next" id="nextToSchedule">Next: Schedule →</button>
        </div>

        <!-- ========= SCHEDULE SECTION ========= -->
        <div id="sectionSchedule" class="hidden">
            <div class="section-title">Select Schedule</div>
            
            <div class="calendar-container">
                <div class="calendar-header">
                    <span class="calendar-month-year" id="calendarMonthYear">April 2026</span>
                    <div class="calendar-nav">
                        <button class="calendar-nav-btn" id="prevMonthBtn"><i class="fas fa-chevron-left"></i></button>
                        <button class="calendar-nav-btn" id="nextMonthBtn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="calendar-weekdays">
                    <span class="weekday">Mo</span><span class="weekday">Tu</span><span class="weekday">We</span>
                    <span class="weekday">Th</span><span class="weekday">Fr</span><span class="weekday">Sa</span><span class="weekday">Su</span>
                </div>
                <div class="calendar-days" id="calendarDays"></div>
            </div>
            
            <div class="selected-date-display" id="selectedDateDisplay">
                <span class="selected-date-label"><i class="far fa-calendar-check"></i> Selected:</span>
                <span class="selected-date-value" id="selectedDateText">April 14, 2026 (Monday)</span>
                <span class="selected-time-value" id="selectedTimeChip">10:00 AM</span>
            </div>
            
            <div class="time-slots-section">
                <div class="time-slots-label"><i class="far fa-clock"></i> Available Time Slots</div>
                <div class="time-slots-grid" id="timeSlotsGrid"></div>
            </div>
            
            <button class="btn-next" id="nextToDetails">Next: Applicant Details →</button>
            <button class="btn-requirements back-btn" id="backToServiceBtn">← Back to Service</button>
        </div>

        <!-- ========= DETAILS SECTION ========= -->
        <div id="sectionDetails" class="hidden">
            <div class="section-title">Applicant Details</div>
            <div>
                <label>Full Name *</label>
                <input type="text" id="fullName" placeholder="Enter full name" value="Juan Dela Cruz">
            </div>
            <div style="margin-top: 16px;">
                <label>Email Address</label>
                <input type="email" id="email" placeholder="example@gmail.com" value="juan.delacruz@email.com">
            </div>
            <div style="margin-top: 16px;">
                <label>Mobile Number</label>
                <input type="tel" id="mobile" placeholder="09XXXXXXXXX" value="09171234567">
                <div class="contact-note">Enter at least one contact detail.</div>
            </div>
            <button class="btn-next" id="nextToReview">Review Appointment →</button>
            <button class="btn-requirements back-btn" id="backToScheduleBtn">← Back to Schedule</button>
        </div>

        <!-- ========= REVIEW SECTION (Step 4) ========= -->
        <div id="sectionReview" class="hidden">
            <div class="section-title">Review Your Appointment</div>
            <p style="color: #5e7182; margin-bottom: 16px; font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i> Please review all details below. Click "Edit" to make changes.
            </p>
            
            <div class="review-container">
                <!-- Service Review -->
                <div class="review-section">
                    <div class="review-section-header">
                        <span class="review-section-title"><i class="fas fa-tag"></i> Selected Service</span>
                        <button class="edit-link" data-edit="service"><i class="fas fa-pen"></i> Edit</button>
                    </div>
                    <div class="review-detail-row">
                        <span class="review-detail-label">Service:</span>
                        <span class="review-detail-value" id="reviewService">National ID Registration</span>
                    </div>
                </div>
                
                <!-- Schedule Review -->
                <div class="review-section">
                    <div class="review-section-header">
                        <span class="review-section-title"><i class="far fa-calendar-alt"></i> Schedule</span>
                        <button class="edit-link" data-edit="schedule"><i class="fas fa-pen"></i> Edit</button>
                    </div>
                    <div class="review-detail-row">
                        <span class="review-detail-label">Date:</span>
                        <span class="review-detail-value" id="reviewDate">Tuesday, April 14, 2026</span>
                    </div>
                    <div class="review-detail-row">
                        <span class="review-detail-label">Time:</span>
                        <span class="review-detail-value" id="reviewTime">10:00 AM</span>
                    </div>
                </div>
                
                <!-- Applicant Details Review -->
                <div class="review-section">
                    <div class="review-section-header">
                        <span class="review-section-title"><i class="fas fa-user"></i> Applicant Details</span>
                        <button class="edit-link" data-edit="details"><i class="fas fa-pen"></i> Edit</button>
                    </div>
                    <div class="review-detail-row">
                        <span class="review-detail-label">Full Name:</span>
                        <span class="review-detail-value" id="reviewFullName">Juan Dela Cruz</span>
                    </div>
                    <div class="review-detail-row">
                        <span class="review-detail-label">Email:</span>
                        <span class="review-detail-value" id="reviewEmail">juan.delacruz@email.com</span>
                    </div>
                    <div class="review-detail-row">
                        <span class="review-detail-label">Mobile:</span>
                        <span class="review-detail-value" id="reviewMobile">09171234567</span>
                    </div>
                </div>
            </div>
            
            <button class="btn-next" id="nextToConfirm">Proceed to Confirm →</button>
            <button class="btn-requirements back-btn" id="backToDetailsFromReview">← Back to Details</button>
        </div>

        <!-- ========= CONFIRM SECTION (Step 5) ========= -->
        <div id="sectionConfirm" class="hidden">
            <div class="section-title">Confirm Appointment</div>
            
            <div class="summary-box">
                <div class="summary-row"><span class="summary-label">Service</span> <span class="summary-value" id="sumService">National ID Registration</span></div>
                <div class="summary-row"><span class="summary-label">Date</span> <span class="summary-value" id="sumDate">Tuesday, April 14, 2026</span></div>
                <div class="summary-row"><span class="summary-label">Time</span> <span class="summary-value" id="sumTime">10:00 AM</span></div>
                <div class="summary-row"><span class="summary-label">Name</span> <span class="summary-value" id="sumName">Juan Dela Cruz</span></div>
                <div class="summary-row"><span class="summary-label">Contact</span> <span class="summary-value" id="sumContact">juan.delacruz@email.com / 09171234567</span></div>
            </div>
            
            <div class="confirmation-checkbox">
                <input type="checkbox" id="confirmCheckbox">
                <label for="confirmCheckbox">
                    I confirm that all information provided is accurate and complete. I understand that providing false information may result in cancellation of my appointment.
                </label>
            </div>
            
            <button class="btn-next" id="submitRequestBtn" disabled>
                <i class="fas fa-check-circle"></i> Confirm & Submit Appointment
            </button>
            <p class="reminder">Please review the details before submitting.</p>
            <div class="reminder" style="margin-top: 8px;">
                <i class="far fa-bell"></i> Reminder: Not all clients may receive an email notification. If your Appointment Slip is generated, your booking was recorded successfully.
            </div>
            <button class="btn-requirements back-btn" id="backToReviewBtn">← Back to Review</button>
        </div>
    </div>

    <div class="footer-note">
        <span>Philippine Statistics Authority</span>
        <span>National ID System (PhilSys) · Official Appointment Booking Portal</span>
        <span>© 2026 All Rights Reserved</span>
    </div>
</div>

<script>
    (function(){
        'use strict';

        // ========== STATE ==========
        let currentService = '';
        let serviceText = '';
        
        let currentDate = new Date(2026, 3, 1);
        let selectedDate = new Date(2026, 3, 14);
        let selectedTime = '10:00 AM';
        
        const availableDates = new Set([
            '2026-04-01', '2026-04-02', '2026-04-03',
            '2026-04-06', '2026-04-07', '2026-04-08', '2026-04-09', '2026-04-10',
            '2026-04-13', '2026-04-14', '2026-04-15', '2026-04-16', '2026-04-17',
            '2026-04-20', '2026-04-21', '2026-04-22', '2026-04-23', '2026-04-24',
            '2026-04-27', '2026-04-28', '2026-04-29', '2026-04-30'
        ]);
        
        const timeSlots = ['9:00 AM', '10:00 AM', '11:00 AM', '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM'];

        // ========== DOM ELEMENTS ==========
        const privacyModal = document.getElementById('privacyModal');
        const agreeBtn = document.getElementById('agreePrivacyBtn');
        const serviceSelect = document.getElementById('serviceSelect');
        const serviceDescBox = document.getElementById('serviceDescBox');
        const showReqBtn = document.getElementById('showReqBtn');
        const reqModal = document.getElementById('reqModal');
        const closeReqModal = document.getElementById('closeReqModal');
        const understandBtn = document.getElementById('understandBtn');
        const modalServiceTitle = document.getElementById('modalServiceTitle');
        const modalBodyContent = document.getElementById('modalBodyContent');

        const sectionService = document.getElementById('sectionService');
        const sectionSchedule = document.getElementById('sectionSchedule');
        const sectionDetails = document.getElementById('sectionDetails');
        const sectionReview = document.getElementById('sectionReview');
        const sectionConfirm = document.getElementById('sectionConfirm');

        const nextToSchedule = document.getElementById('nextToSchedule');
        const backToServiceBtn = document.getElementById('backToServiceBtn');
        const nextToDetails = document.getElementById('nextToDetails');
        const backToScheduleBtn = document.getElementById('backToScheduleBtn');
        const nextToReview = document.getElementById('nextToReview');
        const backToDetailsFromReview = document.getElementById('backToDetailsFromReview');
        const nextToConfirm = document.getElementById('nextToConfirm');
        const backToReviewBtn = document.getElementById('backToReviewBtn');
        const submitBtn = document.getElementById('submitRequestBtn');

        const sumService = document.getElementById('sumService');
        const sumDate = document.getElementById('sumDate');
        const sumTime = document.getElementById('sumTime');
        const sumName = document.getElementById('sumName');
        const sumContact = document.getElementById('sumContact');
        const fullNameInput = document.getElementById('fullName');
        const emailInput = document.getElementById('email');
        const mobileInput = document.getElementById('mobile');

        const reviewService = document.getElementById('reviewService');
        const reviewDate = document.getElementById('reviewDate');
        const reviewTime = document.getElementById('reviewTime');
        const reviewFullName = document.getElementById('reviewFullName');
        const reviewEmail = document.getElementById('reviewEmail');
        const reviewMobile = document.getElementById('reviewMobile');

        const calendarMonthYear = document.getElementById('calendarMonthYear');
        const calendarDays = document.getElementById('calendarDays');
        const prevMonthBtn = document.getElementById('prevMonthBtn');
        const nextMonthBtn = document.getElementById('nextMonthBtn');
        const selectedDateText = document.getElementById('selectedDateText');
        const selectedTimeChip = document.getElementById('selectedTimeChip');
        const timeSlotsGrid = document.getElementById('timeSlotsGrid');
        const confirmCheckbox = document.getElementById('confirmCheckbox');

        // ========== PRIVACY ==========
        agreeBtn.addEventListener('click', () => privacyModal.style.display = 'none');

        // ========== SERVICE ==========
        serviceSelect.addEventListener('change', function() {
            const val = serviceSelect.value;
            currentService = val;
            updateServiceDescription(val);
            showReqBtn.style.display = val ? 'block' : 'none';
            if(val) {
                serviceText = serviceSelect.options[serviceSelect.selectedIndex].text;
                updateAllSummaries();
            } else {
                serviceDescBox.innerHTML = `<p><i class="fas fa-info-circle"></i> Select a service to see details.</p>`;
            }
        });

        function updateServiceDescription(serviceKey) {
            let desc = '';
            if(serviceKey === 'reg') desc = `<strong>National ID Registration</strong><p>Bring original supporting documents for first-time registration.</p>`;
            else if(serviceKey === 'correction') desc = `<strong>Correction/Updating of Demographic Information</strong><p>Prepare documents that support the exact field to be corrected.</p>`;
            else if(serviceKey === 'ephilid') desc = `<strong>Issuance of National ID Paper Form (ePhilID)</strong><p>This service is for clients requesting their National ID in paper format.</p>`;
            else if(serviceKey === 'trn') desc = `<strong>Retrieval of TRN / Other Concern</strong><p>Prepare details that can help personnel verify your concern quickly.</p>`;
            if(desc) serviceDescBox.innerHTML = desc + `<div class="badge-hours"><i class="far fa-clock"></i> Saturday: CLOSED • Sunday: CLOSED</div>`;
        }

        function updateAllSummaries() {
            const serviceDisplay = serviceText || 'Not selected';
            sumService.textContent = serviceDisplay;
            reviewService.textContent = serviceDisplay;
            
            const dateDisplay = formatDisplayDate(selectedDate);
            sumDate.textContent = dateDisplay;
            reviewDate.textContent = dateDisplay;
            
            sumTime.textContent = selectedTime || 'Not selected';
            reviewTime.textContent = selectedTime || 'Not selected';
            
            const fullName = fullNameInput.value || 'Not provided';
            sumName.textContent = fullName;
            reviewFullName.textContent = fullName;
            
            const email = emailInput.value || 'Not provided';
            const mobile = mobileInput.value || 'Not provided';
            reviewEmail.textContent = email;
            reviewMobile.textContent = mobile;
            sumContact.textContent = `${email} / ${mobile}`;
        }

        // ========== REQUIREMENTS MODAL ==========
        function getRequirementsContent(service) {
            if(service === 'reg') return `<h4>Supporting Documents – Filipino Citizens</h4><p><strong>PRIMARY:</strong> PSA Birth Certificate + 1 gov ID; Passport, UMID, Driver's License.</p>`;
            if(service === 'correction') return `<h4>Updating of Demographic Information</h4><p>First Name: Birth certificate. Last Name: Marriage cert.</p>`;
            if(service === 'ephilid') return `<h4>ePhilID Printing</h4><p>Present transaction slip. Representative: authorization letter & IDs.</p>`;
            if(service === 'trn') return `<h4>TRN Retrieval</h4><p>Provide: First, Middle, Last name, DOB, Sex.</p>`;
            return 'Select a service first.';
        }

        showReqBtn.addEventListener('click', () => {
            if(!currentService) return;
            modalServiceTitle.textContent = serviceSelect.options[serviceSelect.selectedIndex].text + ' Requirements';
            modalBodyContent.innerHTML = getRequirementsContent(currentService);
            reqModal.style.display = 'flex';
        });
        closeReqModal.addEventListener('click', () => reqModal.style.display = 'none');
        understandBtn.addEventListener('click', () => reqModal.style.display = 'none');
        window.addEventListener('click', (e) => { if(e.target === reqModal) reqModal.style.display = 'none'; });

        // ========== CALENDAR ==========
        function formatDateKey(date) {
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
        }
        function formatDisplayDate(date) {
            return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        function isDateAvailable(date) { return availableDates.has(formatDateKey(date)); }
        function isSameDate(d1, d2) { return d1.getFullYear() === d2.getFullYear() && d1.getMonth() === d2.getMonth() && d1.getDate() === d2.getDate(); }
        
        function renderCalendar() {
            const year = currentDate.getFullYear(), month = currentDate.getMonth();
            calendarMonthYear.textContent = new Date(year, month, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            const firstDay = new Date(year, month, 1);
            let startDay = firstDay.getDay() || 7;
            startDay = startDay === 7 ? 0 : startDay - 1;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            let html = '';
            for (let i = 0; i < startDay; i++) html += '<div class="calendar-day empty"></div>';
            const today = new Date(); today.setHours(0, 0, 0, 0);
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateKey = formatDateKey(date);
                const isAvailable = availableDates.has(dateKey);
                const isSelected = isSameDate(date, selectedDate);
                const isPast = date < today && !isSameDate(date, today);
                let classes = 'calendar-day';
                if (isAvailable && !isPast) classes += ' available';
                if (isSelected) classes += ' selected';
                if (isPast) classes += ' disabled';
                if (!isAvailable && !isPast) classes += ' unavailable';
                html += `<div class="${classes}" data-date="${dateKey}">${day}</div>`;
            }
            calendarDays.innerHTML = html;
            document.querySelectorAll('.calendar-day[data-date]').forEach(el => {
                el.addEventListener('click', () => {
                    const dateKey = el.dataset.date;
                    if (availableDates.has(dateKey)) {
                        const [y, m, d] = dateKey.split('-').map(Number);
                        selectedDate = new Date(y, m - 1, d);
                        renderCalendar();
                        updateSelectedDisplay();
                        renderTimeSlots();
                        updateAllSummaries();
                    }
                });
            });
        }
        
        function renderTimeSlots() {
            const isAvailable = isDateAvailable(selectedDate);
            const today = new Date(); today.setHours(0, 0, 0, 0);
            const isPast = selectedDate < today;
            let html = '';
            timeSlots.forEach(slot => {
                const isSelected = slot === selectedTime;
                const disabled = !isAvailable || isPast;
                let classes = 'time-slot-chip';
                if (!disabled) classes += ' available';
                if (isSelected && !disabled) classes += ' selected';
                if (disabled) classes += ' disabled';
                html += `<div class="${classes}" data-time="${slot}">${slot}</div>`;
            });
            timeSlotsGrid.innerHTML = html;
            document.querySelectorAll('.time-slot-chip[data-time]').forEach(el => {
                el.addEventListener('click', () => {
                    if (!el.classList.contains('disabled')) {
                        selectedTime = el.dataset.time;
                        renderTimeSlots();
                        updateSelectedDisplay();
                        updateAllSummaries();
                    }
                });
            });
        }
        
        function updateSelectedDisplay() {
            selectedDateText.textContent = formatDisplayDate(selectedDate);
            selectedTimeChip.textContent = selectedTime || 'Select time';
        }

        prevMonthBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
        nextMonthBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });

        // ========== NAVIGATION ==========
        function setActiveStep(stepNumber) {
            document.querySelectorAll('.step').forEach((el, idx) => {
                el.classList.remove('active', 'completed');
                if (idx + 1 < stepNumber) el.classList.add('completed');
                else if (idx + 1 === stepNumber) el.classList.add('active');
            });
        }
        
        function showSection(section) {
            [sectionService, sectionSchedule, sectionDetails, sectionReview, sectionConfirm].forEach(s => s.classList.add('hidden'));
            section.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Edit links
        document.querySelectorAll('[data-edit]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.target.closest('[data-edit]').dataset.edit;
                if (target === 'service') { showSection(sectionService); setActiveStep(1); }
                else if (target === 'schedule') { showSection(sectionSchedule); setActiveStep(2); }
                else if (target === 'details') { showSection(sectionDetails); setActiveStep(3); }
            });
        });

        nextToSchedule.addEventListener('click', () => {
            if(!currentService) { alert('Please select a service'); return; }
            showSection(sectionSchedule); setActiveStep(2);
            renderCalendar(); renderTimeSlots(); updateSelectedDisplay();
        });
        backToServiceBtn.addEventListener('click', () => { showSection(sectionService); setActiveStep(1); });
        
        nextToDetails.addEventListener('click', () => {
            if(!selectedTime) { alert('Please select a time slot'); return; }
            showSection(sectionDetails); setActiveStep(3);
        });
        backToScheduleBtn.addEventListener('click', () => { showSection(sectionSchedule); setActiveStep(2); });
        
        nextToReview.addEventListener('click', () => {
            const name = fullNameInput.value.trim();
            if(!name) { alert('Full name required'); return; }
            updateAllSummaries();
            showSection(sectionReview); setActiveStep(4);
        });
        backToDetailsFromReview.addEventListener('click', () => { showSection(sectionDetails); setActiveStep(3); });
        
        nextToConfirm.addEventListener('click', () => {
            updateAllSummaries();
            showSection(sectionConfirm); setActiveStep(5);
        });
        backToReviewBtn.addEventListener('click', () => { showSection(sectionReview); setActiveStep(4); });
        
        // Enable/disable submit based on checkbox
        confirmCheckbox.addEventListener('change', () => {
            submitBtn.disabled = !confirmCheckbox.checked;
        });
        
        submitBtn.addEventListener('click', () => {
            if (!confirmCheckbox.checked) { alert('Please confirm the information is accurate'); return; }
            alert('✅ Appointment confirmed! (Frontend demo)\nYour appointment has been successfully booked.');
        });

        // ========== INITIALIZE ==========
        serviceSelect.value = 'ephilid';
        currentService = 'ephilid';
        serviceText = 'Issuance of National ID Paper Form (ePhilID)';
        updateServiceDescription('ephilid');
        showReqBtn.style.display = 'block';
        
        selectedDate = new Date(2026, 3, 14);
        selectedTime = '11:00 AM';
        currentDate = new Date(2026, 3, 1);
        renderCalendar();
        renderTimeSlots();
        updateSelectedDisplay();
        updateAllSummaries();
        
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') reqModal.style.display = 'none'; });
    })();
</script>
</body>
</html>