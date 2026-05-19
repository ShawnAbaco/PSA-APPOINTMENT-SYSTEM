<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PSA Appointment System | Official Login Portal</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>

    <!-- Canvas Background -->
    <canvas id="canvas-bg"></canvas>

    <!-- PSA Overlay Animation (Your requested overlay) -->
    <div class="overlay"></div>
    <div class="gradient-overlay"></div>
    <div class="vignette"></div>

    <!-- LOGIN CARD -->
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa-logo.png') }}" alt="PSA Logo" class="psa-logo-img"
                        onerror="this.src='https://via.placeholder.com/90?text=PSA'">
                </div>
                <h1 class="brand-title">Appointment System</h1>
                <p class="subtitle">Admin and Staff Login Portal</p>
            </div>

            <!-- Display Laravel validation errors -->
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

            <!-- REAL LARAVEL LOGIN FORM -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <!-- Username Field -->
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="username" id="username" class="form-control" placeholder=" "
                        value="{{ old('username') }}" required autofocus>
                    <label for="username" class="floating-label">Username</label>
                </div>

                <!-- Password Field -->
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder=" " required>
                    <label for="password" class="floating-label">Password</label>
                    <button type="button" class="password-toggle" id="togglePasswordBtn" tabindex="-1">
                        <i class="far fa-eye-slash"></i>
                    </button>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="rememberCheck">
                        <span>Remember me</span>
                    </label>
                    <button type="button" class="forgot-link">
                        Forgot password?
                    </button>
                </div>

                <button type="submit" class="btn-login">
                    <span>LOGIN</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- CREATE ACCOUNT BUTTON -->
            <div class="create-account-section">
                <div class="divider">
                    <span>Don't have an account?</span>
                </div>
                <a href="{{ route('register') }}" class="btn-create-account">
                    <i class="fas fa-user-plus"></i>
                    <span>CREATE NEW ACCOUNT</span>
                </a>
            </div>

            <div class="footer-note">
                <span>© 2025 PSA Appointment System</span>
                <span>•</span>
                <span>Secure SSL Login</span>
                <span>•</span>
                <span><i class="fas fa-shield-alt"></i> Encrypted</span>
            </div>
        </div>
    </div>

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
        //  UI INTERACTIONS & FORM SUBMIT HANDLER
        // ============================================================
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordField = document.getElementById('password');
        if (toggleBtn && passwordField) {
            toggleBtn.addEventListener('click', () => {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                const icon = toggleBtn.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        }

        // Handle form submission - show loading state
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                showPSALoader();
            });
        }

        // Show loader on create account button click
        const createAccountBtn = document.querySelector('.btn-create-account');
        if (createAccountBtn) {
            createAccountBtn.addEventListener('click', function(e) {
                showPSALoader();
            });
        }

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

        // Floating label fix for autofilled fields
        document.querySelectorAll('.form-control').forEach(input => {
            if (input.value.trim() !== '') {
                input.dispatchEvent(new Event('input'));
            }
        });

        // Remember Me functionality
        if (localStorage.getItem('psa_remember_username')) {
            const savedUsername = localStorage.getItem('psa_remember_username');
            const usernameField = document.getElementById('username');
            if (usernameField) usernameField.value = savedUsername;
            const rememberCheck = document.getElementById('rememberCheck');
            if (rememberCheck) rememberCheck.checked = true;
        }

        const rememberCheck = document.getElementById('rememberCheck');
        const usernameInput = document.getElementById('username');

        if (rememberCheck && usernameInput) {
            rememberCheck.addEventListener('change', function(e) {
                const username = usernameInput.value;
                if (e.target.checked && username) {
                    localStorage.setItem('psa_remember_username', username);
                } else if (!e.target.checked) {
                    localStorage.removeItem('psa_remember_username');
                }
            });

            usernameInput.addEventListener('input', function() {
                if (rememberCheck.checked && this.value) {
                    localStorage.setItem('psa_remember_username', this.value);
                }
            });
        }

        // Hide loader when page fully loads
        window.addEventListener('pageshow', function() {
            hidePSALoader();
        });

        setTimeout(() => {
            hidePSALoader();
        }, 3000);
    </script>
</body>

</html>
