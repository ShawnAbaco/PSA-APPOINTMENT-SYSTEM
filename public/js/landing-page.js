// public/js/landing-page.js

const psaLat = 8.482432;
const psaLng = 124.655153;

let map;
let routingControl = null;
let userMarker = null;
let psaMarker = null;
let userLatLng = null;

// Variable to track if Ctrl key is pressed
let ctrlPressed = false;

const psaIconCustom = L.divIcon({
    html: '<div class="custom-psa-marker"><img src="/images/psa.png" alt="PSA Logo"></div>',
    iconSize: [55, 55],
    iconAnchor: [27, 55],
    popupAnchor: [0, -45],
    className: 'psa-marker-container'
});

function initMap() {
    map = L.map('liveMap').setView([psaLat, psaLng], 16);

    const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        maxZoom: 20,
    });

    const googleSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        maxZoom: 20,
    });

    const googleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        maxZoom: 20,
    });

    const esriSatellite = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
        });

    const osmStreets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    });

    googleHybrid.addTo(map);

    const baseMaps = {
        "Hybrid View": googleHybrid,
        "Satellite View": googleSatellite,
        "Street Map": googleStreets,
        "Esri Satellite": esriSatellite,
        "OpenStreetMap": osmStreets
    };

    L.control.layers(baseMaps).addTo(map);

    L.control.scale({
        metric: true,
        imperial: false,
        position: 'bottomleft'
    }).addTo(map);

    const popupContent = `
        <div style="min-width: 260px; font-family: 'Inter', sans-serif;">
            <div style="background: linear-gradient(135deg, #0f3b6f, #0a2c52); color: white; padding: 12px 16px; border-radius: 12px 12px 0 0; margin: -12px -12px 0 -12px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <img src="/images/psa.png" style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid #c49a2c; background: white; padding: 2px;">
                    <div>
                        <strong style="font-size: 1rem;">PSA - Misamis Oriental</strong><br>
                        <small style="font-size: 0.7rem; opacity:   0.9;">Fixed Registration Center</small>
                    </div>
                </div>
            </div>
            <div style="padding: 12px;">
                <div style="background: #f0f4fa; padding: 10px; border-radius: 10px; margin-bottom: 10px; border-left: 4px solid #c49a2c;">
                    <strong>OFFICIAL ADDRESS</strong><br>
                    <span style="font-size: 0.9rem;">Capt. Vicente Roa Street,<br>Brgy. 31, Cagayan de Oro City,<br>9000 Misamis Oriental, Philippines</span>
                </div>
                <div style="display: flex; gap: 12px; font-size: 0.8rem; color: #475569;">
                    <span>📞 0956 576 6106</span>
                    <span>🕒 Mon-Fri 8AM-5PM</span>
                </div>
                <div style="margin-top: 8px; font-size: 0.75rem; color: #0f3b6f; background: #e8f0fe; padding: 6px; border-radius: 8px; text-align: center;">
                    NID Registration | Updating | Status Inquiry
                </div>
            </div>
        </div>
    `;

    psaMarker = L.marker([psaLat, psaLng], {
        icon: psaIconCustom
    }).addTo(map);
    psaMarker.bindPopup(popupContent).openPopup();

    L.circle([psaLat, psaLng], {
        color: '#c49a2c',
        fillColor: '#f59e0b',
        fillOpacity: 0.15,
        radius: 120,
        weight: 3
    }).addTo(map);

    L.circle([psaLat, psaLng], {
        color: '#0f3b6f',
        fillColor: '#0f3b6f',
        fillOpacity: 0.08,
        radius: 200,
        weight: 1.5,
        dashArray: '8, 6'
    }).addTo(map);

    // ============================================
    // CTRL + SCROLL ZOOM IMPLEMENTATION
    // ============================================
    
    // Disable default scroll wheel zoom initially
    map.scrollWheelZoom.disable();
    
    // Listen for keydown events on the window
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Control' || e.keyCode === 17) {
            ctrlPressed = true;
            // Enable zoom when Ctrl is pressed
            if (!map.scrollWheelZoom.enabled()) {
                map.scrollWheelZoom.enable();
            }
        }
    });
    
    // Listen for keyup events on the window
    window.addEventListener('keyup', function(e) {
        if (e.key === 'Control' || e.keyCode === 17) {
            ctrlPressed = false;
            // Disable zoom when Ctrl is released
            if (map.scrollWheelZoom.enabled()) {
                map.scrollWheelZoom.disable();
            }
        }
    });
    
    // Handle window blur (when user clicks outside, release Ctrl state)
    window.addEventListener('blur', function() {
        ctrlPressed = false;
        if (map.scrollWheelZoom.enabled()) {
            map.scrollWheelZoom.disable();
        }
    });
    
    // Prevent default scroll behavior on map container to avoid page scrolling
    const mapContainer = document.getElementById('liveMap');
    if (mapContainer) {
        mapContainer.addEventListener('wheel', function(e) {
            if (!e.ctrlKey) {
                e.preventDefault();
                return false;
            }
        }, { passive: false });
    }
    
    // Add a custom control to show Ctrl+Zoom hint
    const CtrlZoomControl = L.Control.extend({
        options: {
            position: 'bottomright'
        },
        
        onAdd: function(map) {
            const container = L.DomUtil.create('div', 'ctrl-zoom-control');
            container.innerHTML = `
                <div style="background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); color: white; padding: 8px 14px; border-radius: 30px; font-size: 12px; font-family: monospace; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); pointer-events: none;">
                    <kbd style="background: #333; border-radius: 4px; padding: 2px 8px; font-weight: bold; color: #ffd700; font-size: 11px;">Ctrl</kbd>
                    <span style="font-size: 12px;">+</span>
                    <i class="fas fa-mouse-pointer" style="font-size: 12px;"></i>
                    <span>Scroll to zoom map</span>
                </div>
            `;
            return container;
        }
    });
    
    map.addControl(new CtrlZoomControl());
    
    console.log('Map initialized with Ctrl+Scroll zoom - Zoom only works when holding Ctrl key');
}

function autoDetectAndRoute() {
    if (!navigator.geolocation) {
        console.warn("Geolocation not supported");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            userLatLng = L.latLng(lat, lng);

            const userIcon = L.divIcon({
                html: '<div style="background: linear-gradient(135deg, #dc2626, #ef4444); width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.3);"><span style="color:white; font-size:18px;">📍</span></div>',
                iconSize: [34, 34],
                popupAnchor: [0, -12],
                className: 'user-marker-animated'
            });

            if (userMarker) {
                userMarker.setLatLng([lat, lng]);
            } else {
                userMarker = L.marker([lat, lng], {
                    icon: userIcon
                }).addTo(map);
                userMarker.bindPopup("<center><b>Your Current Location</b><br/>You are here</center>")
                    .openPopup();
            }

            const bounds = L.latLngBounds(userLatLng, L.latLng(psaLat, psaLng));
            map.fitBounds(bounds.pad(0.12));

            if (routingControl) {
                map.removeControl(routingControl);
                routingControl = null;
            }

            routingControl = L.Routing.control({
                waypoints: [userLatLng, L.latLng(psaLat, psaLng)],
                routeWhileDragging: false,
                showAlternatives: true,
                fitSelectedRoutes: false,
                lineOptions: {
                    styles: [{
                        color: '#2563eb',
                        weight: 6,
                        opacity: 0.9,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }],
                    addWaypoints: false,
                    extendToWaypoints: true,
                    missingRouteTolerance: 0
                },
                createMarker: function() {
                    return null;
                },
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1',
                    profile: 'driving'
                }),
                summaryTemplate: '<div style="background:linear-gradient(135deg, #1e3a5f, #0f2b4a); color:white; padding:10px 20px; border-radius:40px; font-weight:600; box-shadow:0 4px 15px rgba(0,0,0,0.2);"><b>Destination route to PSA</b> &nbsp;|&nbsp; {distance} &nbsp;|&nbsp; {time}</div>',
                show: true,
                addWaypoints: false,
                draggableWaypoints: false
            }).addTo(map);

            routingControl.on('routesfound', function(e) {
                const route = e.routes[0];
                const distance = (route.summary.totalDistance / 1000).toFixed(1);
                const duration = Math.round(route.summary.totalTime / 60);
                console.log(`Route found: ${distance} km, ${duration} minutes`);
            });

            routingControl.on('routingerror', function(e) {
                console.error('Routing error:', e);
            });
        },
        (error) => {
            console.error("Geolocation error:", error);
            map.setView([psaLat, psaLng], 16);
            if (psaMarker) psaMarker.openPopup();
        }, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

let routingVisible = true;

function toggleRoutingSummary() {
    const routingContainer = document.querySelector('.leaflet-routing-container');
    const toggleBtn = document.getElementById('toggleRoutingBtn');

    if (routingContainer) {
        if (routingVisible) {
            routingContainer.classList.add('hide-routing');
            toggleBtn.innerHTML = '☰';
            toggleBtn.title = 'Show Directions';
            routingVisible = false;
        } else {
            routingContainer.classList.remove('hide-routing');
            toggleBtn.innerHTML = '✕';
            toggleBtn.title = 'Hide Directions';
            routingVisible = true;
        }
    }
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Active navigation link highlighting
function setActiveNavLink() {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.nav-link');
    
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollY >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
}

// Smooth scroll for anchor links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initMap();
    setTimeout(() => {
        autoDetectAndRoute();
    }, 800);

    const toggleBtn = document.getElementById('toggleRoutingBtn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleRoutingSummary);
    }

    const privacyLink = document.getElementById('privacyPolicyLink');
    const dataPrivacyLink = document.getElementById('dataPrivacyActLink');

    if (privacyLink) {
        privacyLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal('privacyModal');
        });
    }

    if (dataPrivacyLink) {
        dataPrivacyLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal('dataPrivacyModal');
        });
    }
    
    window.onclick = function(event) {
        const privacyModal = document.getElementById('privacyModal');
        const dataPrivacyModal = document.getElementById('dataPrivacyModal');
        if (event.target === privacyModal) {
            closeModal('privacyModal');
        }
        if (event.target === dataPrivacyModal) {
            closeModal('dataPrivacyModal');
        }
    }
    
    setActiveNavLink();
    initSmoothScroll();
});

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

        // Requirements tabs functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.req-tab');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const card = this.closest('.req-card');
                    const tabId = this.getAttribute('data-tab');

                    // Remove active class from all tabs in this card
                    card.querySelectorAll('.req-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Hide all content in this card
                    card.querySelectorAll('.req-content').forEach(content => content.classList
                        .remove('active'));

                    // Show selected content
                    const activeContent = card.querySelector(`#${tabId}`);
                    if (activeContent) activeContent.classList.add('active');
                });
            });
        });
        