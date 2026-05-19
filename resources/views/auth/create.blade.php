{{-- resources/views/auth/create.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PSA Appointment System | Create Staff Account</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/create.css') }}">
</head>

<body>
    <!-- Canvas Background -->
    <canvas id="canvas-bg"></canvas>

    <!-- PSA Overlay Animations -->
    <div class="overlay"></div>
    <div class="gradient-overlay"></div>
    <div class="vignette"></div>

    <div class="register-wrapper">
        <div class="register-card">
            <div class="register-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa-logo.png') }}" alt="PSA Logo" class="psa-logo-img"
                        onerror="this.src='https://via.placeholder.com/90?text=PSA'">
                </div>
                <h1 class="brand-title">Staff Registration</h1>
                <p class="subtitle">Create Employee Account for PSA Appointment System</p>
            </div>

            <!-- Display validation errors -->
            @if ($errors->any())
                <div class="alert-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-message">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div class="alert-message alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Registration Form - Staff Only -->
            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf
                <!-- Hidden role input - always staff -->
                <input type="hidden" name="role" value="staff">

                <!-- First Name -->
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="first_name" id="first_name" class="form-control" placeholder=" "
                        value="{{ old('first_name') }}" required>
                    <label for="first_name" class="floating-label required-field">First Name</label>
                </div>

                <!-- Last Name -->
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="last_name" id="last_name" class="form-control" placeholder=" "
                        value="{{ old('last_name') }}" required>
                    <label for="last_name" class="floating-label required-field">Last Name</label>
                </div>

                <!-- Email -->
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" id="email" class="form-control" placeholder=" "
                        value="{{ old('email') }}" required>
                    <label for="email" class="floating-label required-field">Email Address</label>
                </div>

                <!-- Username -->
                <div class="input-group">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" name="username" id="username" class="form-control" placeholder=" "
                        value="{{ old('username') }}" required>
                    <label for="username" class="floating-label required-field">Username</label>
                </div>

                <!-- Password -->
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder=" " required>
                    <label for="password" class="floating-label required-field">Password</label>
                    <button type="button" class="password-toggle" id="togglePasswordBtn" tabindex="-1">
                        <i class="far fa-eye-slash"></i>
                    </button>
                </div>

                <!-- Confirm Password -->
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control" placeholder=" " required>
                    <label for="password_confirmation" class="floating-label required-field">Confirm Password</label>
                    <button type="button" class="password-toggle" id="toggleConfirmBtn" tabindex="-1">
                        <i class="far fa-eye-slash"></i>
                    </button>
                </div>

                <button type="submit" class="btn-register">
                    <span>CREATE ACCOUNT</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <div class="login-link">
                    Already have an account?
                    <a href="{{ route('login') }}" id="signInLink" style="cursor: pointer;">
                        Sign in here
                    </a>
                </div>
            </form>

            <div class="footer-note">
                <span>© 2025 PSA Appointment System</span>
                <span>•</span>
                <span>Secure SSL Registration</span>
                <span>•</span>
                <span><i class="fas fa-shield-alt"></i> Encrypted</span>
            </div>
        </div>
    </div>

    <!-- PSA LOADING MODAL -->
    <div class="psa-loader-modal" id="psaLoaderModal">
        <div class="psa-loader-container">
            <img src="{{ asset('images/psa.png') }}" alt="PSA Loading" class="psa-loader-logo">
        </div>
    </div>

    <script>
        // ============================================================
        //  MODERN PARTICLE NETWORK BACKGROUND (Canvas Animation)
        // ============================================================
        const canvas = document.getElementById('canvas-bg');
        let ctx = canvas.getContext('2d');
        let particles = [];
        let particleCount = 90;
        let connectionDistance = 150;
        let mouseX = null,
            mouseY = null;
        let mouseRadius = 200;

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.vx = (Math.random() - 0.5) * 0.5;
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 3 + 1.5;
                this.baseColor = `hsl(${Math.random() * 20 + 340}, 70%, 60%)`;
                if (Math.random() > 0.6) this.baseColor = `hsl(210, 70%, 55%)`;
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                if (this.x < 0) this.x = canvas.width;
                if (this.x > canvas.width) this.x = 0;
                if (this.y < 0) this.y = canvas.height;
                if (this.y > canvas.height) this.y = 0;

                if (mouseX !== null && mouseY !== null) {
                    let dx = this.x - mouseX;
                    let dy = this.y - mouseY;
                    let dist = Math.hypot(dx, dy);
                    if (dist < mouseRadius) {
                        let angle = Math.atan2(dy, dx);
                        let force = (mouseRadius - dist) / mouseRadius;
                        let moveX = Math.cos(angle) * force * 1.2;
                        let moveY = Math.sin(angle) * force * 1.2;
                        this.x += moveX;
                        this.y += moveY;
                    }
                }
            }

            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.baseColor;
                ctx.shadowBlur = 8;
                ctx.shadowColor = "rgba(206,17,38,0.5)";
                ctx.fill();
                ctx.shadowBlur = 0;
            }
        }

        function initParticles() {
            particles = [];
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }
        }

        function drawConnections() {
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    let dx = particles[i].x - particles[j].x;
                    let dy = particles[i].y - particles[j].y;
                    let distance = Math.hypot(dx, dy);
                    if (distance < connectionDistance) {
                        let opacity = 1 - (distance / connectionDistance);
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = `rgba(206, 17, 38, ${opacity * 0.35})`;
                        ctx.lineWidth = 1.2;
                        ctx.stroke();
                    }
                }
            }
        }

        function animateBackground() {
            if (!ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            let grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
            grad.addColorStop(0, '#0a1a3a');
            grad.addColorStop(0.5, '#142c52');
            grad.addColorStop(1, '#0f2b5e');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            for (let p of particles) {
                p.update();
                p.draw();
            }
            drawConnections();

            ctx.fillStyle = "rgba(255,240,200,0.3)";
            for (let i = 0; i < 30; i++) {
                if (Math.random() > 0.97) {
                    ctx.beginPath();
                    ctx.arc(Math.random() * canvas.width, Math.random() * canvas.height, 1.2, 0, Math.PI * 2);
                    ctx.fill();
                }
            }
            requestAnimationFrame(animateBackground);
        }

        window.addEventListener('resize', () => {
            resizeCanvas();
            initParticles();
        });

        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            mouseX = e.clientX - rect.left;
            mouseY = e.clientY - rect.top;
        });
        canvas.addEventListener('mouseleave', () => {
            mouseX = null;
            mouseY = null;
        });

        resizeCanvas();
        initParticles();
        animateBackground();

        // ============================================================
        //  PSA LOADER CONTROLS
        // ============================================================
        function showPSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.add('show');
        }

        function hidePSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.remove('show');
        }

        // ============================================================
        //  UI INTERACTIONS
        // ============================================================

        // Password visibility toggles
        const togglePassword = document.getElementById('togglePasswordBtn');
        const toggleConfirm = document.getElementById('toggleConfirmBtn');
        const passwordField = document.getElementById('password');
        const confirmField = document.getElementById('password_confirmation');

        function setupToggle(btn, field) {
            if (btn && field) {
                btn.addEventListener('click', () => {
                    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
                    field.setAttribute('type', type);
                    const icon = btn.querySelector('i');
                    if (type === 'text') {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                });
            }
        }

        setupToggle(togglePassword, passwordField);
        setupToggle(toggleConfirm, confirmField);

        // Handle form submission - show loading state
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                showPSALoader();
            });
        }

        const signInLink = document.getElementById('signInLink');
        if (signInLink) {
            signInLink.addEventListener('click', function(e) {
                e.preventDefault();
                showPSALoader();
                // Navigate after a short delay to show loader
                setTimeout(() => {
                    window.location.href = this.getAttribute('href');
                }, 300);
            });
        }

        // Handle create account button click (if any other navigation)
        const createAccountBtn = document.querySelector('.btn-create-account');
        if (createAccountBtn) {
            createAccountBtn.addEventListener('click', function(e) {
                showPSALoader();
            });
        }

        // Floating label fix for autofilled fields
        document.querySelectorAll('.form-control').forEach(input => {
            if (input.value.trim() !== '') {
                input.dispatchEvent(new Event('input'));
            }
        });

        // Auto-dismiss alerts after 6 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-message');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) alert.remove();
                }, 500);
            });
        }, 6000);

        // Hide loader when page fully loads
        window.addEventListener('pageshow', function() {
            hidePSALoader();
        });

        // Safety timeout to hide loader if something goes wrong
        setTimeout(() => {
            hidePSALoader();
        }, 3000);
    </script>
</body>

</html>
