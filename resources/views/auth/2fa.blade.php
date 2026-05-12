<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/psa.png') }}">
    <title>Two-Factor Verification</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Canvas Background -->
    <canvas id="canvas-bg"></canvas>

    <!-- PSA Overlay Animation (Your requested overlay) -->
    <div class="overlay"></div>
    <div class="gradient-overlay"></div>
    <div class="vignette"></div>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa-logo.png') }}" alt="PSA Logo" class="psa-logo-img">
                </div>
                <h1 class="brand-title">Two-Factor Authentication</h1>
                <p class="subtitle">Enter the 6-digit code from your authenticator app</p>
            </div>

            @if ($errors->any())
                <div class="alert-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form id="twofaForm" method="POST" action="{{ route('auth.2fa.verify.post') }}">
                @csrf
                <div class="input-group">
                    <i class="fas fa-key input-icon"></i>
                    <input type="text" inputmode="numeric" pattern="\d{6}" maxlength="6" name="code"
                        value="{{ old('code') }}" class="form-control" placeholder=" " required autofocus>
                    <label class="floating-label">6-digit code</label>
                </div>

                <div class="form-options">
                    <a href="{{ route('login') }}" class="forgot-link">Back to login</a>
                </div>

                <button type="submit" class="btn-login">
                    <span>VERIFY</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
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

        // Hide loader when page fully loads
        window.addEventListener('pageshow', function() {
            hidePSALoader();
        });

        setTimeout(() => {
            hidePSALoader();
        }, 3000);

        // Show PSA loader when submitting the 2FA form
        const twofaForm = document.getElementById('twofaForm');
        if (twofaForm) {
            twofaForm.addEventListener('submit', function() {
                showPSALoader();
            });
        }
    </script>
</body>

</html>
