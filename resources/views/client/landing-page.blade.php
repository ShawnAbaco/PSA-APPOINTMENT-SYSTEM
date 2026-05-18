{{-- resources/views/landing-page.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>PSA | National ID Appointment Management System</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/landing-page.css') }}">
</head>

<body>

    <header class="site-header">
        <div class="container">
            <div class="logo-area">
                <div class="logo-icon">
                    <img src="{{ asset('images/psa.png') }}" style="width:48px; height:48px; object-fit: cover;"
                        alt="PSA Logo">
                </div>
                <div class="logo-text">
                    <span class="psa-title">Philippine Statistics Authority - Misamis Oriental</span>
                    <span class="psa-sub">National ID Appointment Management System</span>
                </div>
            </div>
            <nav class="main-nav">
                <a href="#home" class="nav-link">Home</a>
                <a href="#howto" class="nav-link">How to Book</a>
                <a href="#map-live" class="nav-link">Map & Directions</a>
                <a href="#requirements" class="nav-link">Requirements</a>
                <a href="#info" class="nav-link">Guidelines</a>
                <a href="#book-appointment" class="button button-primary" id="bookAppointmentNavBtn">Book
                    Appointment</a>

            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section id="home" class="hero-section">
            <div class="container hero-grid">
                <div class="hero-content">
                    <div class="badge">PhilSys - National ID Appointment Management System</div>
                    <h1>Step into the future with <span class="highlight">National ID</span> registration</h1>
                    <p>Schedule your PhilSys registration, ePhilID printing, or data correction appointment. Secure your
                        Philippine Identification System credentials with ease.</p>
                    <div class="hero-buttons">
                        {{-- <a class="button button-primary" href="javascript:void(0)" id="heroBookBtn">Book an Appointment
                            →</a> --}}
                        <a class="button-view" href="#requirements">View Requirements</a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="floating-card">
                        <img src="{{ asset('images/ePhilID.png') }}" alt="ePhilID Sample"
                            style="width:100%; border-radius: 24px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </section>

        <!-- STEP BY STEP SECTION - HOW TO BOOK -->
        <section id="howto" class="howto-section">
            <div class="container">
                <div class="section-header">
                    <h2>How to Book a National ID Appointment</h2>
                    <p>Follow these simple steps to schedule your appointment in minutes</p>
                </div>
                <div class="howto-grid">
                    <div class="howto-card">
                        <div class="howto-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <span class="howto-step">STEP 1</span>
                        <h3>Add Applicants</h3>
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
                <div class="howto-cta">
                    <a href="javascript:void(0)" class="button button-primary" id="howtoBookBtn">
                        <i class="fas fa-calendar-check"></i> Book Your Appointment Now →
                    </a>
                </div>
            </div>
        </section>

        <!-- Live Map Section -->
        <section id="map-live" class="map-section">
            <div class="container">
                <div class="section-header">
                    <h2>Philippine Statistics Authority Office - Misamis Oriental</h2>
                    <p>Auto-detecting your location and showing route to PSA center</p>
                </div>
                <div class="map-card">
                    <div id="liveMap" class="leaflet-map"></div>
                    <button class="custom-toggle-btn" id="toggleRoutingBtn" aria-label="Toggle directions">✕</button>
                </div>
            </div>
        </section>

        <!-- Requirements Section - IMPROVED UI WITH SCROLLABLE LISTS -->
        <section id="requirements" class="requirements-section">
            <div class="container">
                <div class="section-header">
                    <h2>Appointment Requirements & Services</h2>
                    <p>Prepare the necessary documents before your scheduled visit</p>
                </div>
                <div class="requirements-grid">

                    {{-- NATIONAL ID REGISTRATION CARD --}}
                    @php
                        $regRequirements = $requirements['reg'] ?? collect();
                        $regStandardRequirements = $regRequirements->where('age_group', '4_above');
                        $regChildRequirements = $regRequirements->where('age_group', 'child');
                    @endphp
                    <div class="req-card">
                        <div class="req-header">
                            <div class="req-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h3>National ID Registration</h3>
                        </div>

                        <div class="req-tabs">
                            <button class="req-tab active" data-tab="standard-reg">5+ Years Old</button>
                            <button class="req-tab" data-tab="child-reg">0-4 Years Old</button>
                        </div>

                        <div class="req-content active" id="standard-reg">
                            <div class="req-list-container">
                                <ul class="req-docs">
                                    @foreach ($regStandardRequirements as $req)
                                        <li><span>{{ $req->requirement }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="req-content" id="child-reg">
                            <div class="req-list-container">
                                <ul class="req-docs">
                                    @foreach ($regChildRequirements as $req)
                                        <li><i class="fas fa-child"></i> <span>{{ $req->requirement }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="warning-note">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Bring <strong>original documents</strong>. No photocopies accepted for primary
                                validation.</span>
                        </div>
                    </div>

                    {{-- STATUS INQUIRY / ePhilID / TRN RETRIEVAL CARD --}}
                    @php
                        $inquiryRequirements = $requirements['inquiry'] ?? collect();
                        $inquiryStandardRequirements = $inquiryRequirements->where('age_group', '4_above');
                        $inquiryChildRequirements = $inquiryRequirements->where('age_group', 'child');
                    @endphp
                    <div class="req-card">
                        <div class="req-header">
                            <div class="req-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <h3>Status Inquiry & ePhilID</h3>
                        </div>

                        <div class="req-tabs">
                            <button class="req-tab active" data-tab="standard-inq">5+ Years Old</button>
                            <button class="req-tab" data-tab="child-inq">0-4 Years Old</button>
                        </div>

                        <div class="req-content active" id="standard-inq">
                            <div class="req-list-container">
                                <ul class="req-docs">
                                    @foreach ($inquiryStandardRequirements as $req)
                                        <li> <span>{{ $req->requirement }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="req-content" id="child-inq">
                            <div class="req-list-container">
                                <ul class="req-docs">
                                    @foreach ($inquiryChildRequirements as $req)
                                        <li><i class="fas fa-child"></i> <span>{{ $req->requirement }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="warning-note">
                            <i class="fas fa-info-circle"></i>
                            <span>ePhilID is a valid proof of identity while waiting for the physical
                                card.<br>Confidential per RA 10173 (Data Privacy Act).</span>
                        </div>
                    </div>

                    {{-- CORRECTION / UPDATING CARD --}}
                    @php
                        $updatingRequirements = $requirements['updating'] ?? collect();
                        $updatingStandardRequirements = $updatingRequirements->where('age_group', '4_above');
                        $updatingChildRequirements = $updatingRequirements->where('age_group', 'child');
                    @endphp
                    <div class="req-card">
                        <div class="req-header">
                            <div class="req-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h3>Correction / Updating</h3>
                        </div>

                        <div class="req-tabs">
                            <button class="req-tab active" data-tab="standard-upd">5+ Years Old</button>
                            <button class="req-tab" data-tab="child-upd">0-4 Years Old</button>
                        </div>

                        <div class="req-content active" id="standard-upd">
                            <div class="req-list-container">
                                <ul class="req-docs">
                                    @foreach ($updatingStandardRequirements as $req)
                                        <li> <span>{{ $req->requirement }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="req-content" id="child-upd">
                            <div class="req-list-container">
                                <ul class="req-docs">
                                    @foreach ($updatingChildRequirements as $req)
                                        <li><i class="fas fa-child"></i> <span>{{ $req->requirement }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="warning-note">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Bring <strong>ORIGINAL copies</strong> of supporting documents for any demographic
                                change.</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Guidelines Section -->
        <section id="info" class="info-section">
            <div class="container">
                <div class="info-grid">
                    <div class="info-text">
                        <h2>National ID Appointment Guidelines</h2>
                        <ul>
                            <li>Strictly by appointment – walk-ins accommodated only during off-peak hours.</li>
                            <li>Arrive 15 minutes before schedule with printed appointment slip or reference number.
                            </li>
                            <li>Wear a face mask (optional but encouraged) and practice physical distancing.</li>
                            <li>Minors must be accompanied by a parent/legal guardian with valid ID.</li>
                            <li>ePhilID printing and TRN retrieval are free of charge.</li>
                            <li>For lost transaction slip, present any valid government ID for verification.</li>
                        </ul>
                        <a href="javascript:void(0)" class="button button-primary" id="guidelinesBookBtn"
                            style="margin-top: 16px;">Schedule Your Appointment →</a>
                    </div>
                    <div class="info-contact">
                        <h3>PSA Misamis Oriental Support</h3>
                        <p><strong>Hotline:</strong> 0956 576 6106</p>
                        <p><strong>Email:</strong> psamisamisoriental@yahoo.com.ph</p>
                        <p><strong>Facebook:</strong> <a href="https://www.facebook.com/PSAMISOR" target="_blank">PSA
                                Misamis Oriental</a></p>
                        <div class="warning-note" style="margin-top: 20px;">
                            <span>For urgent concerns regarding National ID, call the hotline from Mon-Fri
                                8AM-5PM.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="site-footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-logo">
                        <span style="font-weight:700;">Philippine Statistics Authority - Misamis Oriental</span>
                        <small style="display:block;">PhilSys – Philippine Identification System</small>
                    </div>
                    <div class="footer-links">
                        <a href="#" id="privacyPolicyLink">Privacy Policy</a>
                        <a href="#" id="dataPrivacyActLink">Data Privacy Act</a>
                        <a href="javascript:void(0)" id="footerBookBtn">Book Appointment</a>
                    </div>
                </div>
                <div class="copyright">
                    &copy; 2025 PSA - Misamis Oriental. All rights reserved. | RA 11055 (PhilSys Act)
                </div>
            </div>
        </footer>
    </main>

    <!-- Privacy Policy Modal -->
    <div id="privacyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Privacy Policy</h2>
                <button class="close-modal" onclick="closeModal('privacyModal')">&times;</button>
            </div>
            <div class="modal-body">
                <h3>Data Privacy Statement</h3>
                <p>The Philippine Statistics Authority (PSA) is committed to protecting your personal data in compliance
                    with Republic Act No. 10173 or the Data Privacy Act of 2012.</p>

                <h3>Collection of Information</h3>
                <p>We collect personal information necessary for the processing of National ID registration, including
                    but not limited to:</p>
                <ul>
                    <li>Full name (first, middle, last)</li>
                    <li>Date and place of birth</li>
                    <li>Sex and civil status</li>
                    <li>Address and contact information</li>
                    <li>Biometric information (fingerprints, iris scan, photo)</li>
                </ul>

                <h3>Use of Information</h3>
                <p>Your personal data will be used exclusively for the PhilSys (Philippine Identification System)
                    registration, verification, and issuance of the National ID and ePhilID.</p>

                <h3>Data Security</h3>
                <p>PSA implements strict security measures to protect your data from unauthorized access, disclosure, or
                    alteration. Your information is stored in secure government databases.</p>

                <h3>Data Sharing</h3>
                <p>Your data may be shared with authorized government agencies strictly for identity verification
                    purposes, subject to your consent and existing laws.</p>

                <h3>Your Rights</h3>
                <p>You have the right to access, correct, and dispute your personal information under the Data Privacy
                    Act. For concerns, contact our Data Protection Officer at dpo@psa.gov.ph.</p>
            </div>
        </div>
    </div>

    <!-- Data Privacy Act Modal -->
    <div id="dataPrivacyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Data Privacy Act of 2012 (RA 10173)</h2>
                <button class="close-modal" onclick="closeModal('dataPrivacyModal')">&times;</button>
            </div>
            <div class="modal-body">
                <h3>Republic Act No. 10173</h3>
                <p>An act protecting individual personal information in information and communications systems in the
                    government and the private sector.</p>

                <h3>Key Provisions</h3>
                <ul>
                    <li><strong>Section 11:</strong> Principles of transparency, legitimate purpose, and proportionality
                        in data processing.</li>
                    <li><strong>Section 12:</strong> Criteria for lawful processing of personal information.</li>
                    <li><strong>Section 16:</strong> Rights of the data subject including access, correction, and
                        objection.</li>
                    <li><strong>Section 20-22:</strong> Security measures for personal data protection.</li>
                    <li><strong>Section 25-28:</strong> Penalties for unauthorized processing, access, and breach of
                        confidentiality.</li>
                </ul>

                <h3>Your Rights as a Data Subject</h3>
                <ul>
                    <li>Right to be informed</li>
                    <li>Right to access</li>
                    <li>Right to object</li>
                    <li>Right to erasure or blocking</li>
                    <li>Right to damages</li>
                    <li>Right to file a complaint</li>
                    <li>Right to data portability</li>
                </ul>

                <h3>PSA Compliance</h3>
                <p>The PSA fully complies with RA 10173 in all PhilSys transactions. Your data is handled with utmost
                    confidentiality and security. For data privacy concerns, email: dataprivacy@psa.gov.ph</p>
            </div>
        </div>
    </div>

    <!-- APPOINTMENT MODAL -->
    <div id="appointmentModal" class="appointment-modal-overlay">
        <div class="appointment-modal-container">
            <div class="appointment-modal-header">
                <h3><img src="{{ asset('images/psa.png') }}" alt="PSA" style="height: 40px;">National ID
                    Appointment Management System</h3>
                <button class="close-appointment-modal" id="closeAppointmentModalBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="appointment-modal-body">
                <div class="modal-loading" id="modalLoading">
                    <div class="spinner"></div>
                    <p>Loading appointment system...</p>
                </div>
                <iframe id="appointmentIframe" class="appointment-iframe" src=""
                    title="Book Appointment"></iframe>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="{{ asset('js/landing-page.js') }}"></script>

</body>

</html>
