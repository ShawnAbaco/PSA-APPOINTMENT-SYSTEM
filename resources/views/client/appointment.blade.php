<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>PhilSys · National ID Appointment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* ========== DESIGN SYSTEM ========== */
        :root {
            --primary: #0b2f5c;
            --primary-light: #1a4a7a;
            --primary-dark: #071d38;
            --secondary: #1a73e8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #e9eef4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 16px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ========== PRIVACY MODAL ========== */
        .privacy-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }
        
        .privacy-modal {
            background: white;
            max-width: 560px;
            width: 100%;
            border-radius: var(--radius-xl);
            padding: 32px 28px;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s ease;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .privacy-modal h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            letter-spacing: -0.02em;
            border-left: 4px solid var(--secondary);
            padding-left: 20px;
        }
        
        .privacy-modal p {
            color: var(--gray-700);
            line-height: 1.7;
            margin-bottom: 16px;
            font-size: 0.95rem;
        }
        
        .privacy-modal .legal-ref {
            font-weight: 600;
            color: var(--primary);
        }
        
        .btn-agree {
            background: var(--primary);
            color: white;
            border: none;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 16px 24px;
            width: 100%;
            border-radius: 60px;
            margin-top: 24px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            letter-spacing: 0.3px;
        }
        
        .btn-agree:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* ========== MAIN CARD ========== */
        .appointment-card {
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .card-header {
            padding: 20px 24px 16px;
            background: linear-gradient(to bottom, white, var(--gray-50));
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .logos {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-placeholder {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            color: var(--primary);
            font-size: 1rem;
        }
        
        .logo-placeholder i {
            font-size: 28px;
            color: var(--primary);
        }
        
        .logo-sep {
            font-size: 22px;
            color: var(--gray-400);
            font-weight: 300;
        }
        
        .header-title {
            margin-left: auto;
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .center-name {
            background: var(--primary);
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            width: 100%;
            margin-top: 8px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }

        /* Stepper */
        .stepper {
            display: flex;
            padding: 20px 20px 12px;
            gap: 6px;
            background: white;
            border-bottom: 1px solid var(--gray-200);
            overflow-x: auto;
            scrollbar-width: none;
        }
        
        .stepper::-webkit-scrollbar { display: none; }
        
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray-500);
            font-weight: 500;
            font-size: 0.8rem;
            flex-shrink: 0;
            padding: 4px 8px 4px 4px;
            border-radius: 40px;
        }
        
        .step .step-num {
            width: 28px;
            height: 28px;
            background: var(--gray-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--gray-600);
            font-size: 0.8rem;
        }
        
        .step.active { color: var(--primary); background: var(--gray-50); }
        .step.active .step-num { background: var(--primary); color: white; box-shadow: 0 2px 8px rgba(11, 47, 92, 0.2); }
        .step.completed .step-num { background: var(--success); color: white; }

        /* Content Body */
        .content-body {
            padding: 28px 24px 32px;
            background: white;
        }

        .booking-note {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 14px 20px;
            border-radius: var(--radius-lg);
            margin-bottom: 28px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: var(--shadow);
        }
        
        .booking-note strong {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 0.9rem;
        }

        .section-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 24px;
            color: var(--gray-900);
        }

        /* Form Elements */
        label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--gray-700);
            margin-bottom: 6px;
        }

        select, input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
            background: white;
            transition: var(--transition);
            color: var(--gray-900);
        }
        
        select:focus, input:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
        }

        /* Appointment Type Selector */
        .appointment-type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 28px;
        }
        
        .type-option {
            padding: 24px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-lg);
            text-align: center;
            cursor: pointer;
            background: white;
        }
        
        .type-option:hover {
            border-color: var(--secondary);
            background: var(--gray-50);
        }
        
        .type-option i {
            font-size: 36px;
            color: var(--primary);
            margin-bottom: 12px;
        }
        
        .type-option span {
            display: block;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--gray-800);
        }
        
        .type-option small {
            font-size: 0.8rem;
            color: var(--gray-500);
        }
        
        .type-option.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, #f0f7ff 0%, #e8f4fd 100%);
            box-shadow: var(--shadow);
        }

        /* Buttons */
        .btn-next {
            background: var(--primary);
            color: white;
            font-weight: 600;
            border: none;
            padding: 16px 24px;
            border-radius: 60px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 16px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-next:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }
        
        .btn-next:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-add-client {
            background: white;
            border: 2px dashed var(--primary);
            color: var(--primary);
            padding: 14px 20px;
            border-radius: 60px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-requirements, .btn-view-req {
            background: white;
            border: 1.5px solid var(--primary);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 40px;
            font-weight: 500;
            font-size: 0.85rem;
            cursor: pointer;
        }
        
        .btn-requirements:hover, .btn-view-req:hover {
            background: var(--primary);
            color: white;
        }
        /* Service description and requirements button container */
#singleServiceSection {
    text-align: center;
}

#singleServiceDesc {
    text-align: center;
}

#singleServiceDesc p {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

#showReqBtnSingle {
    margin-left: auto !important;
    margin-right: auto !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

        .back-btn {
            background: transparent !important;
            color: var(--gray-600) !important;
            margin-top: 12px !important;
            border: 1.5px solid var(--gray-300) !important;
            box-shadow: none !important;
        }

        /* Client Cards */
        .clients-list { margin-bottom: 24px; }
        
        .client-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 16px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }
        
        .client-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .client-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .client-service-badge {
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .btn-remove-client {
            background: none;
            border: none;
            color: var(--danger);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: var(--radius-sm);
        }
        
        .btn-remove-client:hover { background: #fee2e2; }

        .req-ack-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-top: 16px;
            padding: 14px;
            background: #fef3c7;
            border-radius: var(--radius);
            border-left: 4px solid var(--warning);
        }
        
        .req-ack-row input {
            width: 20px;
            height: 20px;
            min-height: 20px;
            accent-color: var(--primary);
        }
        
        .req-ack-row.acknowledged {
            background: #d1fae5;
            border-left-color: var(--success);
        }

        .identity-reminder {
            background: #e0f2fe;
            border-radius: var(--radius);
            padding: 14px 16px;
            margin-bottom: 16px;
            border-left: 4px solid var(--secondary);
        }
        
        .identity-reminder i {
            color: var(--secondary);
            margin-right: 8px;
        }
        
        .identity-reminder strong { color: var(--gray-800); }
        .identity-reminder p { color: var(--gray-700); font-size: 0.9rem; margin-top: 6px; margin-left: 26px; }

        /* Requirements Summary Banner */
        .req-summary-banner {
            background: #fef3c7;
            border-radius: var(--radius-lg);
            padding: 16px 18px;
            margin-bottom: 24px;
            border-left: 5px solid var(--warning);
        }
        
        .req-summary-banner.complete {
            background: #d1fae5;
            border-left-color: var(--success);
        }
        
        .req-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
            font-size: 0.9rem;
        }

        /* Form Grid */
        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            margin-top: 12px;
        }
        
        .form-row-three {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 12px;
        }
        
        @media (min-width: 500px) {
            .form-row { grid-template-columns: 1fr 1fr; }
            .form-row-three { grid-template-columns: 1fr 1fr 1fr; }
        }

        /* Calendar */
        .calendar-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }
        
        .calendar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .calendar-month-year {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--gray-900);
        }
        
        .calendar-nav-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--gray-200);
            background: white;
            color: var(--gray-700);
            cursor: pointer;
        }
        
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            margin-bottom: 10px;
        }
        
        .weekday {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gray-500);
            text-transform: uppercase;
            padding: 10px 0;
        }
        
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }
        
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: var(--radius);
            cursor: pointer;
            background: white;
            border: 1px solid var(--gray-200);
        }
        
        .calendar-day .slots-left {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--success);
        }
        
        .calendar-day.available { background: #e8f4fd; border-color: var(--secondary); }
        .calendar-day.selected { background: var(--primary); color: white; border-color: var(--primary); }
        .calendar-day.disabled { opacity: 0.5; cursor: not-allowed; background: var(--gray-100); }
        .calendar-day.full { background: #fee2e2; border-color: #fecaca; cursor: not-allowed; }

        .selected-date-display {
            background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
            padding: 14px 18px;
            border-radius: var(--radius);
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--gray-200);
            font-weight: 600;
        }

        .slot-info {
            background: var(--gray-50);
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            border: 1px solid var(--gray-200);
        }

        /* Review Section */
        .review-container {
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--gray-200);
        }
        
        .review-section {
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .review-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .review-section-title {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 1.1rem;
            margin-bottom: 12px;
        }
        
        .client-summary-item {
            padding: 12px 0;
            border-bottom: 1px dashed var(--gray-300);
        }

        /* Confirmation */
        .summary-box {
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            padding: 24px;
            margin: 24px 0;
            border: 1px solid var(--gray-200);
        }
        
        .summary-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .summary-row:last-child { border-bottom: none; }
        
        .summary-label {
            width: 100px;
            font-weight: 600;
            color: var(--gray-600);
        }
        
        .summary-value { flex: 1; font-weight: 500; color: var(--gray-900); }

        .confirmation-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin: 24px 0;
            padding: 18px;
            background: #e8f4fd;
            border-radius: var(--radius);
            border: 1px solid var(--secondary);
        }
        
        .confirmation-checkbox input {
            width: 22px;
            height: 22px;
            min-height: 22px;
            accent-color: var(--primary);
        }

        /* Footer */
        .footer-note {
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
            padding: 18px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: var(--gray-500);
            font-size: 0.8rem;
        }
        
        @media (min-width: 600px) {
            .footer-note { flex-direction: row; justify-content: space-between; }
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            max-width: 700px;
            width: 100%;
            max-height: 75vh;
            overflow-y: auto;
            border-radius: var(--radius-xl);
            padding: 28px;
            box-shadow: var(--shadow-lg);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            color: var(--gray-900);
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .close-modal {
            font-size: 28px;
            cursor: pointer;
            color: var(--gray-500);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .close-modal:hover { background: var(--gray-100); }
        
        .btn-understand {
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 60px;
            width: 100%;
            font-weight: 600;
            margin-top: 24px;
            cursor: pointer;
        }

        .req-list h4 { color: var(--gray-900); margin: 20px 0 12px; }
        .req-list ul { padding-left: 24px; margin-bottom: 16px; }
        .req-list li { margin-bottom: 8px; color: var(--gray-700); }

        .hidden { display: none !important; }

        .service-option-tag {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 3px 10px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 8px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            body { padding: 8px; }
            .content-body { padding: 20px 16px; }
            .appointment-type-selector { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- PRIVACY NOTICE MODAL -->
<div class="privacy-overlay" id="privacyModal">
    <div class="privacy-modal">
        <h2>Privacy Notice</h2>
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
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalServiceTitle">Requirements</h3>
            <span class="close-modal" id="closeReqModal">&times;</span>
        </div>
        <div id="modalBodyContent" class="req-list"></div>
        <button class="btn-understand" id="understandBtn">I Understand</button>
    </div>
</div>

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
            <span><strong>Booking until 31 May 2026</strong></span>
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
                    <option value="reg">National ID Registration</option>
                    <option value="correction">Correction/Updating of Demographic Information</option>
                    <option value="ephilid">Issuance of National ID Paper Form (ePhilID)</option>
                    <option value="trn">Retrieval of TRN / Other Concern</option>
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
            <p style="color: var(--gray-500); margin-bottom: 20px;"><span id="clientsSubtitle">Add all persons who will attend.</span></p>
            
            <div class="req-summary-banner" id="reqSummaryBanner">
                <strong><i class="fas fa-clipboard-list"></i> Requirements Status</strong>
                <div id="reqSummaryList"></div>
            </div>
            
            <div class="clients-list" id="clientsList"></div>
            
            <button class="btn-add-client" id="addClientBtn">
                <i class="fas fa-plus-circle"></i> Add Another Person
            </button>
            
            <div id="clientCountContainer" style="margin-top: 20px; padding: 14px; background: var(--gray-50); border-radius: var(--radius); border: 1px solid var(--gray-200);">
                <p style="display: flex; justify-content: space-between;">
                    <span><i class="fas fa-users" style="color: var(--primary);"></i> <strong>Total persons:</strong></span>
                    <span style="font-weight: 700; color: var(--primary);"><span id="clientCount">1</span> / 10</span>
                </p>
            </div>
            
            <button class="btn-next" id="nextToSchedule">Next: Select Date <i class="fas fa-arrow-right"></i></button>
            <button class="btn-next back-btn" id="backToTypeFromClients"><i class="fas fa-arrow-left"></i> Back</button>
        </div>

        <!-- STEP 3: SCHEDULE -->
        <div id="sectionSchedule" class="hidden">
            <div class="section-title">Select Appointment Date</div>
            
            <div class="slot-info" id="slotInfo">
                <i class="fas fa-calendar-check" style="color: var(--primary);"></i>
                <span id="slotInfoText">Select an available date</span>
            </div>
            
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
            
            <div class="selected-date-display">
                <i class="far fa-check-circle" style="color: var(--success);"></i>
                <span>Selected Date:</span>
                <span id="selectedDateText" style="font-weight: 600; color: var(--gray-900);">Tuesday, April 14, 2026</span>
            </div>
            
            <button class="btn-next" id="nextToContact">Next: Contact Info <i class="fas fa-arrow-right"></i></button>
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
                <input type="text" id="contactName" placeholder="e.g., Maria Dela Cruz" value="Maria Dela Cruz">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label>Email Address</label>
                <input type="email" id="contactEmail" placeholder="maria@example.com" value="maria.delacruz@email.com">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label>Mobile Number <span style="color: var(--danger);">*</span></label>
                <input type="tel" id="contactMobile" placeholder="09XXXXXXXXX" value="09171234567">
            </div>
            
            <button class="btn-next" id="nextToReview">Review Appointment <i class="fas fa-arrow-right"></i></button>
            <button class="btn-next back-btn" id="backToScheduleFromContact"><i class="fas fa-arrow-left"></i> Back</button>
        </div>

        <!-- STEP 5: REVIEW -->
        <div id="sectionReview" class="hidden">
            <div class="section-title">Review Your Appointment</div>
            
            <div class="review-container">
                <div class="review-section">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span class="review-section-title"><i class="fas fa-tag" style="color: var(--primary);"></i> Type</span>
                        <button class="edit-link" data-edit="type" style="color: var(--secondary);"><i class="fas fa-pen"></i> Edit</button>
                    </div>
                    <div><span id="reviewType" style="font-weight: 600;">-</span></div>
                </div>
                
                <div class="review-section">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span class="review-section-title"><i class="fas fa-users" style="color: var(--primary);"></i> Clients (<span id="reviewClientCount">1</span>)</span>
                        <button class="edit-link" data-edit="clients" style="color: var(--secondary);"><i class="fas fa-pen"></i> Edit</button>
                    </div>
                    <div id="reviewClientsList"></div>
                </div>
                
                <div class="review-section">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span class="review-section-title"><i class="far fa-calendar-alt" style="color: var(--primary);"></i> Schedule</span>
                        <button class="edit-link" data-edit="schedule" style="color: var(--secondary);"><i class="fas fa-pen"></i> Edit</button>
                    </div>
                    <div><span id="reviewDate" style="font-weight: 600;">-</span></div>
                </div>
                
                <div class="review-section">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span class="review-section-title"><i class="fas fa-address-book" style="color: var(--primary);"></i> Contact</span>
                        <button class="edit-link" data-edit="contact" style="color: var(--secondary);"><i class="fas fa-pen"></i> Edit</button>
                    </div>
                    <div>
                        <span id="reviewContactName" style="font-weight: 600;">-</span><br>
                        <span id="reviewContactEmail" style="color: var(--gray-500);">-</span><br>
                        <span id="reviewContactMobile" style="font-weight: 500;">-</span>
                    </div>
                </div>
            </div>
            
            <button class="btn-next" id="nextToConfirm">Proceed to Confirm <i class="fas fa-arrow-right"></i></button>
            <button class="btn-next back-btn" id="backToContact"><i class="fas fa-arrow-left"></i> Back</button>
        </div>

        <!-- STEP 6: CONFIRM -->
        <div id="sectionConfirm" class="hidden">
            <div class="section-title">Confirm Appointment</div>
            
            <div class="summary-box">
                <div class="summary-row"><span class="summary-label">Type</span><span class="summary-value" id="sumType">-</span></div>
                <div class="summary-row"><span class="summary-label">Clients</span><span class="summary-value" id="sumClients">-</span></div>
                <div class="summary-row"><span class="summary-label">Date</span><span class="summary-value" id="sumDate">-</span></div>
                <div class="summary-row"><span class="summary-label">Contact</span><span class="summary-value" id="sumContact">-</span></div>
            </div>
            
            <div class="confirmation-checkbox">
                <input type="checkbox" id="confirmCheckbox">
                <label for="confirmCheckbox">
                    <strong>I confirm that all information is accurate and complete.</strong><br>
                    <span style="font-size: 0.85rem; color: var(--gray-500);">I have read all service requirements and will bring necessary documents.</span>
                </label>
            </div>
            
            <button class="btn-next" id="submitRequestBtn" disabled>
                <i class="fas fa-check-circle"></i> Confirm & Submit
            </button>
            
            <div class="reminder" style="margin-top: 20px;">
                <i class="far fa-bell" style="color: var(--warning);"></i> 
                Reminder: If your Appointment Slip is generated, your booking was recorded.
            </div>
            
            <button class="btn-next back-btn" id="backToReview"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
    </div>
    
    <div class="footer-note">
        <span><i class="far fa-copyright"></i> Philippine Statistics Authority</span>
        <span>National ID System (PhilSys) · Official Portal</span>
        <span>© 2026 All Rights Reserved</span>
    </div>
</div>

<script>
    (function(){
        'use strict';

        let appointmentType = 'single';
        let singleService = 'reg';
        let singleServiceText = 'National ID Registration';
        const serviceOptions = {
            'reg': 'National ID Registration',
            'correction': 'Correction/Updating',
            'ephilid': 'ePhilID Issuance',
            'trn': 'TRN Retrieval'
        };
        
        const requirementsContent = {
            'reg': `<h4><i class="fas fa-id-card"></i> National ID Registration</h4><p><strong>PRIMARY DOCUMENTS:</strong></p><ul><li>PSA Birth Certificate + 1 government-issued ID (Passport, UMID, Driver's License)</li></ul><p><strong>SECONDARY DOCUMENTS:</strong></p><ul><li>PSA/LCRO Birth Certificate</li><li>Voter's ID, Postal ID, PhilHealth ID</li><li>Employee ID, School ID, Barangay Certificate</li></ul><p><em>⚠️ Bring original documents.</em></p>`,
            'correction': `<h4><i class="fas fa-pen"></i> Correction/Updating</h4><p><strong>Required Documents by Field:</strong></p><ul><li><strong>First/Last Name:</strong> Birth Certificate, Marriage Certificate</li><li><strong>Sex/DOB:</strong> Birth Certificate</li><li><strong>Address:</strong> Barangay Certificate + Billing</li></ul><p><em>⚠️ Bring ORIGINAL copies.</em></p>`,
            'ephilid': `<h4><i class="fas fa-print"></i> ePhilID Printing</h4><p><strong>Requirements:</strong></p><ul><li>Transaction slip or reference number</li></ul><p><strong>Representative:</strong> Authorization letter + IDs</p><p><strong>Minor:</strong> Birth Certificate + Guardian ID</p>`,
            'trn': `<h4><i class="fas fa-search"></i> TRN Retrieval</h4><p><strong>Provide:</strong></p><ul><li>First, Middle, Last Name</li><li>Date of Birth</li><li>Sex</li></ul><p><em>🔒 Confidential per RA 10173.</em></p>`
        };
        
        const identityReminders = {
            'reg': 'The person named below must be the one registering for the National ID.',
            'correction': 'The person named below must be the one requesting the correction/update.',
            'ephilid': 'The person named below must be the one claiming the ePhilID.',
            'trn': 'The person named below must be the one requesting TRN retrieval.'
        };
        
        // Client structure with separate name fields
        let clients = [{ 
            id: 1, 
            firstName: 'Juan', 
            middleName: 'Santos', 
            lastName: 'Dela Cruz',
            suffix: '',
            sex: 'Male', 
            birthdate: '1990-01-15', 
            service: 'reg', 
            reqAcknowledged: false 
        }];
        let nextClientId = 2;
        
        let currentDate = new Date(2026, 3, 1);
        let selectedDate = new Date(2026, 3, 14);
        const dailyCapacity = 20;
        const getBookedSlots = (k) => { const d = parseInt(k.split('-')[2]); return d===14?8:d===15?18:d===16?20:Math.floor(Math.random()*12); };
        const availableDates = new Set(['2026-04-01','2026-04-02','2026-04-03','2026-04-06','2026-04-07','2026-04-08','2026-04-09','2026-04-10','2026-04-13','2026-04-14','2026-04-15','2026-04-16','2026-04-17','2026-04-20','2026-04-21','2026-04-22','2026-04-23','2026-04-24','2026-04-27','2026-04-28','2026-04-29','2026-04-30']);

        const privacyModal = document.getElementById('privacyModal');
        const reqModal = document.getElementById('reqModal');
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
        
        function formatDateKey(d) { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
        function formatDisplayDate(d) { return d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }); }
        function formatBirthdate(d) { return d?new Date(d).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}):''; }
        function getServiceName(c) { return serviceOptions[c]||c; }
        function canAccommodate(k, n) { return (getBookedSlots(k)+n) <= dailyCapacity; }
        function getSlotsLeft(k) { return Math.max(0, dailyCapacity - getBookedSlots(k)); }
        function allRequirementsAcknowledged() { return clients.every(c => c.reqAcknowledged); }
        
        function getFullName(c) {
            const parts = [c.firstName, c.middleName, c.lastName].filter(p => p && p.trim());
            let fullName = parts.join(' ') || '(No name)';
            if (c.suffix) fullName += ' ' + c.suffix;
            return fullName;
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
                html += `<div class="req-item"><i class="fas ${acked ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="color:${acked?'var(--success)':'var(--warning)'};"></i> ${displayName || 'Person '+(i+1)} - ${getServiceName(svc)} ${acked ? '✓' : '(Pending)'}</div>`;
            });
            list.innerHTML = html;
            banner.classList.toggle('complete', allAcked);
            document.getElementById('nextToSchedule').disabled = !allAcked;
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
                document.getElementById('clientsSubtitle').textContent = 'Enter the details of the person for this appointment.';
            } else {
                addBtn.classList.remove('hidden');
                countContainer.classList.remove('hidden');
                document.getElementById('clientsTitle').textContent = 'Family / Group Members';
                document.getElementById('clientsSubtitle').textContent = 'Add all persons who will attend. Each can select their own service.';
            }
            
            container.innerHTML = '';
            clients.forEach((c, i) => {
                const svc = isMultiple ? c.service : singleService;
                const reminder = identityReminders[svc] || 'Please ensure the information is accurate.';
                
                const card = document.createElement('div'); card.className = 'client-card';
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
                    
                    ${isMultiple ? `<div class="form-col" style="margin-top: 12px;"><label>Service <span style="color: var(--danger);">*</span></label><select class="client-service" data-id="${c.id}"><option value="reg" ${c.service==='reg'?'selected':''}>National ID Registration</option><option value="correction" ${c.service==='correction'?'selected':''}>Correction/Updating</option><option value="ephilid" ${c.service==='ephilid'?'selected':''}>ePhilID Issuance</option><option value="trn" ${c.service==='trn'?'selected':''}>TRN Retrieval / Other Concerb</option></select></div>` : ''}
                    
                    <div class="req-ack-row ${c.reqAcknowledged ? 'acknowledged' : ''}">
                        <input type="checkbox" class="req-ack" data-id="${c.id}" ${c.reqAcknowledged ? 'checked' : ''}>
                        <label>I have read and understood the requirements for <strong>${getServiceName(svc)}</strong>. I will bring all necessary documents.</label>
                    </div>
                `;
                container.appendChild(card);
            });
            
            document.querySelectorAll('.client-firstname').forEach(e=>e.addEventListener('input', (ev)=>{ const c=clients.find(x=>x.id==ev.target.dataset.id); if(c)c.firstName=ev.target.value; }));
            document.querySelectorAll('.client-middlename').forEach(e=>e.addEventListener('input', (ev)=>{ const c=clients.find(x=>x.id==ev.target.dataset.id); if(c)c.middleName=ev.target.value; }));
            document.querySelectorAll('.client-lastname').forEach(e=>e.addEventListener('input', (ev)=>{ const c=clients.find(x=>x.id==ev.target.dataset.id); if(c)c.lastName=ev.target.value; }));
            document.querySelectorAll('.client-suffix').forEach(e=>e.addEventListener('change', (ev)=>{ const c=clients.find(x=>x.id==ev.target.dataset.id); if(c)c.suffix=ev.target.value; }));
            document.querySelectorAll('.client-sex').forEach(e=>e.addEventListener('change', (ev)=>{ const c=clients.find(x=>x.id==ev.target.dataset.id); if(c)c.sex=ev.target.value; }));
            document.querySelectorAll('.client-birthdate').forEach(e=>e.addEventListener('input', (ev)=>{ const c=clients.find(x=>x.id==ev.target.dataset.id); if(c)c.birthdate=ev.target.value; }));
            document.querySelectorAll('.client-service').forEach(e=>e.addEventListener('change', (ev)=>{ 
                const c=clients.find(x=>x.id==ev.target.dataset.id); 
                if(c){c.service=ev.target.value; c.reqAcknowledged=false; renderClients(); updateReqSummary(); }
            }));
            document.querySelectorAll('.req-ack').forEach(e=>e.addEventListener('change', (ev)=>{ const c=clients.find(x=>x.id==ev.target.dataset.id); if(c){c.reqAcknowledged=ev.target.checked; renderClients(); updateReqSummary(); }}));
            document.querySelectorAll('.btn-view-req').forEach(b=>b.addEventListener('click', (ev)=>{
                const id = ev.target.closest('.btn-view-req').dataset.id;
                const client = clients.find(c=>c.id==id);
                const svc = appointmentType==='single'?singleService:client.service;
                modalTitle.textContent = `${getServiceName(svc)} Requirements`;
                modalBody.innerHTML = requirementsContent[svc] || '<p>Requirements not available.</p>';
                reqModal.style.display = 'flex';
            }));
            document.querySelectorAll('.btn-remove-client').forEach(b=>b.addEventListener('click', (ev)=>{
                const id = ev.target.closest('.btn-remove-client').dataset.id;
                clients = clients.filter(c=>c.id!=id);
                renderClients();
                updateReqSummary();
                document.getElementById('clientCount').textContent = clients.length;
            }));
            
            document.getElementById('clientCount').textContent = clients.length;
            updateReqSummary();
        }

        function renderCalendar() {
            const y = currentDate.getFullYear(), m = currentDate.getMonth();
            document.getElementById('calendarMonthYear').textContent = new Date(y,m,1).toLocaleDateString('en-US',{month:'long',year:'numeric'});
            const first = new Date(y,m,1); let start = first.getDay()||7; start = start===7?0:start-1;
            const days = new Date(y,m+1,0).getDate();
            let html = ''; for(let i=0;i<start;i++) html+='<div class="calendar-day empty"></div>';
            const today = new Date(); today.setHours(0,0,0,0);
            for(let d=1;d<=days;d++){
                const date = new Date(y,m,d), key = formatDateKey(date);
                const avail = availableDates.has(key), past = date<today, canFit = canAccommodate(key, clients.length);
                let cls = 'calendar-day';
                if(avail&&!past&&canFit) cls+=' available';
                if(date.getTime()===selectedDate.getTime()) cls+=' selected';
                if(past) cls+=' disabled';
                if(avail&&!past&&!canFit) cls+=' full';
                html+=`<div class="${cls}" data-date="${key}">${d}${avail&&!past?`<span class="slots-left">${getSlotsLeft(key)}</span>`:''}</div>`;
            }
            document.getElementById('calendarDays').innerHTML = html;
            document.querySelectorAll('.calendar-day[data-date]').forEach(el=>el.addEventListener('click', ()=>{
                const k = el.dataset.date; if(!availableDates.has(k)) return;
                if(!canAccommodate(k, clients.length)){ alert(`Only ${getSlotsLeft(k)} slots available.`); return; }
                const [y,m,d] = k.split('-').map(Number); selectedDate = new Date(y,m-1,d);
                renderCalendar(); document.getElementById('selectedDateText').textContent = formatDisplayDate(selectedDate);
                document.getElementById('slotInfoText').innerHTML = `✓ ${getSlotsLeft(k)} slots available.`;
            }));
        }

        function setActiveStep(s){ document.querySelectorAll('.step').forEach((e,i)=>e.classList.toggle('active', i+1===s)); }
        function showSection(s){ Object.values(sections).forEach(x=>x.classList.add('hidden')); s.classList.remove('hidden'); }
        
        document.getElementById('agreePrivacyBtn').onclick = ()=>privacyModal.style.display='none';
        
        document.getElementById('typeSingle').onclick = ()=>{
            appointmentType='single'; 
            document.getElementById('typeSingle').classList.add('selected'); 
            document.getElementById('typeMultiple').classList.remove('selected');
            document.getElementById('singleServiceSection').style.display='block';
            if (clients.length > 1) clients = [clients[0]];
            clients.forEach(c=>c.service=singleService);
            renderClients();
        };
        
        document.getElementById('typeMultiple').onclick = ()=>{
            appointmentType='multiple'; 
            document.getElementById('typeMultiple').classList.add('selected'); 
            document.getElementById('typeSingle').classList.remove('selected');
            document.getElementById('singleServiceSection').style.display='none';
            renderClients();
        };
        
        document.getElementById('singleServiceSelect').onchange = function(){
            singleService = this.value; 
            singleServiceText = serviceOptions[this.value]||'';
document.getElementById('singleServiceDesc').innerHTML = this.value ? 
    `<p style="color: var(--gray-700);"><i class="fas fa-check-circle" style="color: var(--success);"></i> ${singleServiceText} selected. Click 'View Requirements' for details.</p>` : 
    '<p style="color: var(--gray-500);"><i class="fas fa-info-circle"></i> Select a service.</p>';
        document.getElementById('showReqBtnSingle').style.display = this.value?'block':'none';
            if(appointmentType==='single'){ clients.forEach(c=>c.service=singleService); renderClients(); }
        };
        
        document.getElementById('showReqBtnSingle').onclick = ()=>{
            modalTitle.textContent = singleServiceText + ' Requirements';
            modalBody.innerHTML = requirementsContent[singleService] || '';
            reqModal.style.display='flex';
        };
        
        document.getElementById('addClientBtn').onclick = ()=>{
            if (appointmentType === 'single') return;
            if(clients.length>=10){ alert('Maximum 5 persons.'); return; }
            clients.push({ id: nextClientId++, firstName:'', middleName:'', lastName:'',suffix:'', sex:'Male', birthdate:'', service: 'reg', reqAcknowledged:false });
            renderClients();
        };
        
        document.getElementById('nextToClients').onclick = ()=>{
            if(appointmentType==='single' && !singleService){ alert('Please select a service'); return; }
            renderClients(); showSection(sections.clients); setActiveStep(2);
        };
        
        document.getElementById('backToTypeFromClients').onclick = ()=>{ showSection(sections.type); setActiveStep(1); };
        
        document.getElementById('nextToSchedule').onclick = ()=>{
            for (let c of clients) {
                if (!c.firstName?.trim() || !c.lastName?.trim()) { alert('First Name and Last Name are required for all clients'); return; }
                if (!c.sex) { alert('Please select sex for all clients'); return; }
                if (!c.birthdate) { alert('Please enter birthdate for all clients'); return; }
                if (appointmentType === 'multiple' && !c.service) { alert('Please select service for all clients'); return; }
            }
            if(!allRequirementsAcknowledged()){ alert('Please acknowledge all requirements.'); return; }
            renderCalendar(); showSection(sections.schedule); setActiveStep(3);
        };
        
        document.getElementById('backToClients').onclick = ()=>{ showSection(sections.clients); setActiveStep(2); };
        document.getElementById('prevMonthBtn').onclick = ()=>{ currentDate.setMonth(currentDate.getMonth()-1); renderCalendar(); };
        document.getElementById('nextMonthBtn').onclick = ()=>{ currentDate.setMonth(currentDate.getMonth()+1); renderCalendar(); };
        
        document.getElementById('nextToContact').onclick = ()=>{
            if(!canAccommodate(formatDateKey(selectedDate), clients.length)){ alert('Not enough slots.'); return; }
            showSection(sections.contact); setActiveStep(4);
        };
        
        document.getElementById('backToScheduleFromContact').onclick = ()=>{ showSection(sections.schedule); setActiveStep(3); };
        
        document.getElementById('nextToReview').onclick = ()=>{
            const name=document.getElementById('contactName').value, mob=document.getElementById('contactMobile').value;
            if(!name||!mob){ alert('Contact name and mobile required.'); return; }
            document.getElementById('reviewType').textContent = appointmentType==='single'?'Single':'Family / Group';
            document.getElementById('reviewClientCount').textContent = clients.length;
            document.getElementById('reviewClientsList').innerHTML = clients.map((c,i)=>`<div class="client-summary-item"><strong>${i+1}. ${getFullName(c)}</strong> - ${getServiceName(appointmentType==='single'?singleService:c.service)}</div>`).join('');
            document.getElementById('reviewDate').textContent = formatDisplayDate(selectedDate);
            document.getElementById('reviewContactName').textContent = name;
            document.getElementById('reviewContactEmail').textContent = document.getElementById('contactEmail').value || 'Not provided';
            document.getElementById('reviewContactMobile').textContent = mob;
            showSection(sections.review); setActiveStep(5);
        };
        
        document.getElementById('backToContact').onclick = ()=>{ showSection(sections.contact); setActiveStep(4); };
        
        document.getElementById('nextToConfirm').onclick = ()=>{
            document.getElementById('sumType').textContent = appointmentType==='single'?'Single':'Multiple';
            document.getElementById('sumClients').textContent = clients.length+' person(s)';
            document.getElementById('sumDate').textContent = formatDisplayDate(selectedDate);
            document.getElementById('sumContact').textContent = document.getElementById('contactName').value + ' / ' + document.getElementById('contactMobile').value;
            showSection(sections.confirm); setActiveStep(6);
        };
        
        document.getElementById('backToReview').onclick = ()=>{ showSection(sections.review); setActiveStep(5); };
        document.getElementById('confirmCheckbox').onchange = (e)=>document.getElementById('submitRequestBtn').disabled = !e.target.checked;
        
        document.getElementById('submitRequestBtn').onclick = ()=>{
            const serviceSummary = clients.map(c => `${getFullName(c)} (${getServiceName(appointmentType==='single'?singleService:c.service)})`).join(', ');
            alert(`✅ Appointment confirmed for ${clients.length} person(s)!\n\n${serviceSummary}\nDate: ${formatDisplayDate(selectedDate)}`);
        };
        
        document.getElementById('closeReqModal').onclick = ()=>reqModal.style.display='none';
        document.getElementById('understandBtn').onclick = ()=>reqModal.style.display='none';
        
        document.querySelectorAll('[data-edit]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const target = e.target.closest('[data-edit]').dataset.edit;
                if (target === 'type') { showSection(sections.type); setActiveStep(1); }
                else if (target === 'clients') { showSection(sections.clients); setActiveStep(2); }
                else if (target === 'schedule') { showSection(sections.schedule); setActiveStep(3); }
                else if (target === 'contact') { showSection(sections.contact); setActiveStep(4); }
            });
        });
        
        singleServiceSelect.value='reg'; singleServiceSelect.dispatchEvent(new Event('change'));
        renderClients();
        window.addEventListener('click', (e) => { if(e.target === reqModal) reqModal.style.display = 'none'; });
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') reqModal.style.display = 'none'; });
    })();
</script>
</body>
</html>