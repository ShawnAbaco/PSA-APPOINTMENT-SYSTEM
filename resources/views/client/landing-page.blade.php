{{-- resources/views/landing-page.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>PSA | National ID Appointment System</title>
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
                    <span class="psa-title">Philippine Statistics Authority</span>
                    <span class="psa-sub">National ID Appointment System</span>
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
                    <div class="badge">PhilSys - National ID Appointment System</div>
                    <h1>Step into the future with <span class="highlight">National ID</span> registration</h1>
                    <p>Schedule your PhilSys registration, ePhilID printing, or data correction appointment. Secure your
                        Philippine Identification System credentials with ease.</p>
                    <div class="hero-buttons">
                        {{-- <a class="button button-primary" href="javascript:void(0)" id="heroBookBtn">Book an Appointment
                            →</a> --}}
                        <a class="button button-outline" href="#requirements">View Requirements</a>
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
                    <h2>Philippine Statistics Authority Office (Misamis Oriental)</h2>
                    <p>Auto-detecting your location and showing route to PSA center</p>
                </div>
                <div class="map-card">
                    <div id="liveMap" class="leaflet-map"></div>
                    <button class="custom-toggle-btn" id="toggleRoutingBtn" aria-label="Toggle directions">✕</button>
                </div>
            </div>
        </section>

        <!-- Requirements Section - NOW WITH 3 CARDS -->
        <section id="requirements" class="requirements-section">
            <div class="container">
                <div class="section-header">
                    <h2>Appointment Requirements & Services</h2>
                    <p>Prepare the necessary documents before your scheduled visit</p>
                </div>
                <div class="requirements-grid">
                    {{-- CARD 1: National ID Registration (Primary & Secondary) --}}
                    <div class="req-card">
                        <div class="req-header">
                            <h3>National ID Registration</h3>
                        </div>
                        <ul class="req-docs">
                            <li><strong>PRIMARY DOCUMENTS:</strong>
                                <ol style="margin-top: 5px; margin-left: 20px; list-style: decimal;">
                                    <li>PSA-issued Certificate of Live Birth and one (1) government-issued
                                        identification document which bears the full name, front-facing photograph, and
                                        signature or thumbmark;</li>
                                    <li>Philippine Passport or ePassport issued by the Department of Foreign Affairs
                                        (DFA);</li>
                                    <li>Unified Multi-purpose Identification (UMID) Card issued by the Government
                                        Service Insurance System (GSIS) or Social Security System (SSS); or</li>
                                    <li>Student's License Permit or Non-Professional/Professional Driver's License
                                        issued by the Land Transportation Office (LTO).</li>
                                </ol>
                            </li>
                            <li><strong>SECONDARY DOCUMENTS:</strong>
                                <ol
                                    style="margin-top: 5px; margin-left: 20px; list-style: decimal; max-height: 200px; overflow-y: auto;">
                                    <li>PSA-issued Certificate of Live Birth/National Statistics Office (NSO)-issued
                                        Certificate of Live Birth with Birth Reference Number (BreN);</li>
                                    <li>Local Civil Registry Office (LCRO)-issued Certificate of Live Birth;</li>
                                    <li>PSA-issued Report of Birth:</li>
                                    <li>PSA-issued Certificate of Foundling:</li>
                                    <li>Integrated Bar of the Philippines (IBP) Identification Card;</li>
                                    <li>Professional Regulatory Commission (PRC) ID;</li>
                                    <li>Seaman's Book (Seafarer's Record Book);</li>
                                    <li>Overseas Workers Welfare Administration (OWWA) ID:</li>
                                    <li>Senior Citizen's ID;</li>
                                    <li>SSS ID;</li>
                                    <li>Pantawid Pamilyang Pilipino Program (4Ps) ID;</li>
                                    <li>License to Own or Possess Firearms (LTOPF);</li>
                                    <li>NBI Clearance;</li>
                                    <li>Police Clearance/ID:</li>
                                    <li>Solo Parent's ID;</li>
                                    <li>Person with Disability (PWD) ID;</li>
                                    <li>Voter's ID;</li>
                                    <li>Postal ID;</li>
                                    <li>Taxpayer Identification Number (TIN):</li>
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
                        <div class="warning-note">Bring <strong>original documents</strong>. No photocopies accepted
                            for primary validation.</div>
                    </div>

                    {{-- CARD 2: Status Inquiry (ePhilID Issuance + TRN Retrieval) --}}
                    <div class="req-card">
                        <div class="req-header">
                            <h3>Status Inquiry</h3>
                        </div>
                        <ul class="req-docs">
                            <li><strong>ePhilID Issuance (Printing)</strong>
                                <ul style="margin-top: 5px; margin-left: 20px; list-style: disc;">
                                    <li>Transaction slip or reference number from Step 1 registration</li>
                                    <li><strong>For Representative:</strong> Authorization letter + valid ID of both
                                        parties</li>
                                    <li><strong>For Minor:</strong> Birth Certificate + Guardian's valid ID</li>
                                </ul>
                            </li>
                            <li><strong>TRN Retrieval</strong>
                                <ul style="margin-top: 5px; margin-left: 20px; list-style: disc;">
                                    <li>Provide: First, Middle, Last Name</li>
                                    <li>Date of Birth (exact as registered)</li>
                                    <li>Sex / Gender information</li>
                                </ul>
                            </li>
                        </ul>
                        <div class="warning-note">ePhilID is a valid proof of identity while waiting for the physical
                            card.<br>
                            Confidential per RA 10173 (Data Privacy Act). TRN will only be released to the data subject.
                        </div>
                    </div>


                    {{-- CARD 3: Correction / Updating --}}
                    <div class="req-card">
                        <div class="req-header">
                            <h3>Correction / Updating</h3>
                        </div>
                        <ul class="req-docs">
                            <li><strong>First/Last Name:</strong> Birth Certificate, Marriage Certificate (if
                                applicable)</li>
                            <li><strong>Sex/DOB:</strong> PSA Birth Certificate (original)</li>
                            <li><strong>Address:</strong> Barangay Certificate + Proof of Billing (utility bill)</li>
                        </ul>
                        <div class="warning-note">Bring <strong>ORIGINAL copies</strong> of supporting documents for
                            any demographic change.</div>
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
                        <span style="font-weight:700;">Philippine Statistics Authority</span>
                        <small style="display:block;">PhilSys – Philippine Identification System</small>
                    </div>
                    <div class="footer-links">
                        <a href="#" id="privacyPolicyLink">Privacy Policy</a>
                        <a href="#" id="dataPrivacyActLink">Data Privacy Act</a>
                        <a href="javascript:void(0)" id="footerBookBtn">Book Appointment</a>
                    </div>
                </div>
                <div class="copyright">
                    &copy; 2025 PSA - Philippine Statistics Authority. All rights reserved. | RA 11055 (PhilSys Act)
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
                <h3><img src="{{ asset('images/psa.png') }}" alt="PSA" style="height: 40px;"> National ID
                    Appointment System</h3>
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

    <script>
        (function() {
            'use strict';

            // Smooth scroll and active link highlighting
            const navLinks = document.querySelectorAll('.nav-link');
            const sections = document.querySelectorAll('section[id]');

            // Function to update active nav link based on scroll position
            function updateActiveNavLink() {
                let currentSection = '';
                const scrollPosition = window.scrollY + 100; // Offset for header

                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.offsetHeight;
                    if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                        currentSection = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('active');
                    const href = link.getAttribute('href');
                    if (href === `#${currentSection}`) {
                        link.classList.add('active');
                    }
                });
            }

            // Function to handle smooth scroll when clicking nav links
            function handleNavClick(e) {
                const targetId = this.getAttribute('href');
                if (targetId && targetId !== '#book-appointment' && targetId !== 'javascript:void(0)') {
                    e.preventDefault();
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        const headerOffset = 80;
                        const elementPosition = targetElement.offsetTop;
                        const offsetPosition = elementPosition - headerOffset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });

                        // Update URL hash without jumping
                        history.pushState(null, null, targetId);
                    }
                }
            }

            // Add click handlers to nav links
            navLinks.forEach(link => {
                link.addEventListener('click', handleNavClick);
            });

            // Listen for scroll events to update active link
            window.addEventListener('scroll', updateActiveNavLink);
            window.addEventListener('load', updateActiveNavLink);

            // Section fade-in animation on scroll
            const fadeSections = document.querySelectorAll(
                '.hero-section, .howto-section, .map-section, .requirements-section, .info-section');

            function checkFadeIn() {
                fadeSections.forEach(section => {
                    const sectionTop = section.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    if (sectionTop < windowHeight - 100) {
                        section.classList.add('visible');
                    }
                });
            }

            window.addEventListener('scroll', checkFadeIn);
            window.addEventListener('load', checkFadeIn);

            // Get all book appointment buttons/links
            const bookButtons = [
                document.getElementById('heroBookBtn'),
                document.getElementById('bookAppointmentNavBtn'),
                document.getElementById('guidelinesBookBtn'),
                document.getElementById('footerBookBtn'),
                document.getElementById('howtoBookBtn')
            ].filter(btn => btn !== null);

            const modal = document.getElementById('appointmentModal');
            const iframe = document.getElementById('appointmentIframe');
            const loadingOverlay = document.getElementById('modalLoading');
            const closeModalBtn = document.getElementById('closeAppointmentModalBtn');

            const appointmentUrl = "{{ url('/appointment') }}";

            function openAppointmentModal() {
                modal.classList.add('active');
                document.body.classList.add('modal-open');
                loadingOverlay.style.display = 'flex';
                iframe.style.opacity = '0';
                iframe.src = 'about:blank';
                setTimeout(() => {
                    iframe.src = appointmentUrl;
                }, 50);
            }

            function closeAppointmentModal() {
                modal.classList.remove('active');
                document.body.classList.remove('modal-open');
                setTimeout(() => {
                    iframe.src = 'about:blank';
                }, 300);
            }

            iframe.addEventListener('load', function() {
                loadingOverlay.style.display = 'none';
                iframe.style.opacity = '1';
            });

            setTimeout(() => {
                if (loadingOverlay.style.display !== 'none') {
                    loadingOverlay.innerHTML =
                        '<div class="spinner"></div><p>Taking longer than usual... <br> <small>Check your connection</small></p><button onclick="location.reload()" style="margin-top:15px; padding:8px 16px; background:#2c5f8a; color:white; border:none; border-radius:8px; cursor:pointer;">Retry</button>';
                }
            }, 8000);

            bookButtons.forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        openAppointmentModal();
                    });
                }
            });

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeAppointmentModal);
            }

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeAppointmentModal();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeAppointmentModal();
                }
            });

            console.log('Appointment modal initialized. Buttons found:', bookButtons.length);
        })();

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
        }

        document.getElementById('privacyPolicyLink')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('privacyModal').style.display = 'flex';
        });
        document.getElementById('dataPrivacyActLink')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('dataPrivacyModal').style.display = 'flex';
        });

        window.onclick = function(event) {
            const privacyModal = document.getElementById('privacyModal');
            const dataPrivacyModal = document.getElementById('dataPrivacyModal');
            if (event.target === privacyModal) privacyModal.style.display = 'none';
            if (event.target === dataPrivacyModal) dataPrivacyModal.style.display = 'none';
        }
    </script>

    <script>
        async function detectAndStoreLocation() {
            if (!navigator.geolocation) {
                console.warn("Geolocation not supported");
                return false;
            }

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                });

                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                let city = '',
                    address = '',
                    zipcode = '';
                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`
                    );
                    const data = await response.json();
                    if (data && data.address) {
                        city = data.address.city || data.address.town || data.address.municipality || '';
                        address = data.display_name || '';
                        zipcode = data.address.postcode || '';
                    }
                } catch (error) {
                    console.error('Reverse geocoding error:', error);
                }

                const locationData = {
                    lat: lat,
                    lng: lng,
                    city: city,
                    address: address,
                    zipcode: zipcode,
                    detected: true,
                    timestamp: Date.now()
                };

                localStorage.setItem('userLocation', JSON.stringify(locationData));
                console.log('Location stored for appointment:', locationData);
                return true;

            } catch (error) {
                console.error('Location detection error:', error);
                localStorage.setItem('userLocation', JSON.stringify({
                    detected: false,
                    error: error.message
                }));
                return false;
            }
        }

        setTimeout(() => {
            detectAndStoreLocation();
        }, 1000);

        const bookButtons = document.querySelectorAll(
            '#heroBookBtn, #bookAppointmentNavBtn, #guidelinesBookBtn, #footerBookBtn, #howtoBookBtn');
        bookButtons.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', function(e) {
                    const stored = localStorage.getItem('userLocation');
                    if (!stored || JSON.parse(stored).detected === false) {
                        e.preventDefault();
                        detectAndStoreLocation().then(() => {
                            document.getElementById('appointmentModal').classList.add('active');
                            document.body.classList.add('modal-open');
                            const iframe = document.getElementById('appointmentIframe');
                            const loadingOverlay = document.getElementById('modalLoading');
                            loadingOverlay.style.display = 'flex';
                            iframe.style.opacity = '0';
                            setTimeout(() => {
                                iframe.src = "{{ url('/appointment') }}";
                            }, 50);
                        });
                    }
                });
            }
        });
    </script>
</body>

</html>
