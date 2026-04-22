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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        /* CANVAS BACKGROUND */
        #canvas-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            display: block;
        }

        .gradient-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 40% 50%, rgb(255 255 255 / 30%) 0%, rgb(0 0 0 / 20%) 100%);
            z-index: 1;
            pointer-events: none;
        }

        .vignette {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at center, transparent 50%, rgba(0, 0, 0, 0.25) 100%);
            z-index: 1;
            pointer-events: none;
        }

        /* REGISTER CARD */
        .register-wrapper {
            position: relative;
            z-index: 20;
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            animation: fadeSlideUp 0.7s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }

        @keyframes fadeSlideUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(12px);
            border-radius: 48px;
            padding: 38px 36px;
            box-shadow: 0 35px 70px -20px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(206, 17, 38, 0.2);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }

        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 45px 80px -20px rgba(0, 0, 0, 0.5);
        }

        .register-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }

        .psa-logo-img {
            width: 520px;
            height: 100px;
            background: linear-gradient(0deg, #615c5c, #ffffff00);
            object-fit: contain;
            filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.15));
            transition: transform 0.25s ease;
        }

        .brand-title {
            font-size: 30px;
            font-weight: 800;
            background: linear-gradient(135deg, #0B1E4E, #CE1126);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            color: #5a6e8a;
            font-size: 14px;
            font-weight: 500;
        }

        /* Alert styles */
        .alert-message {
            background: #FEF2F2;
            border-left: 5px solid #CE1126;
            border-radius: 28px;
            padding: 12px 18px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #991B1B;
            animation: gentleShake 0.4s ease;
        }

        .alert-success {
            background: #E6F7EC;
            border-left-color: #2E7D32;
            color: #1B5E20;
        }

        @keyframes gentleShake {

            0%,
            100% {
                transform: translateX(0);
            }

            30% {
                transform: translateX(-4px);
            }

            60% {
                transform: translateX(4px);
            }
        }

        /* Form inputs */
        .input-group {
            position: relative;
            margin-bottom: 24px;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #8A99B4;
            font-size: 16px;
            pointer-events: none;
            z-index: 2;
            transition: color 0.2s;
        }

        .form-control {
            width: 100%;
            padding: 15px 18px 15px 48px;
            font-size: 14px;
            font-weight: 500;
            border: 2px solid #E9EDF2;
            border-radius: 40px;
            background: #FFFFFF;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
            color: #1E293B;
        }

        .form-control:focus {
            outline: none;
            border-color: #CE1126;
            box-shadow: 0 0 0 4px rgba(206, 17, 38, 0.1);
        }

        .floating-label {
            position: absolute;
            left: 42px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            padding: 0 6px;
            color: #8A99B4;
            font-size: 14px;
            font-weight: 500;
            pointer-events: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .form-control:focus+.floating-label,
        .form-control:not(:placeholder-shown)+.floating-label {
            transform: translateY(-30px) translateX(-10px) scale(0.82);
            background: white;
            color: #CE1126;
            border-radius: 12px;
            font-weight: 600;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            color: #8A99B4;
            font-size: 18px;
            padding: 0;
            z-index: 3;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #CE1126;
        }

        /* Staff badge */
        .staff-badge {
            background: linear-gradient(135deg, #CE1126, #A31F34);
            color: white;
            padding: 8px 20px;
            border-radius: 40px;
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .staff-badge i {
            margin-right: 8px;
        }

        /* Required field indicator */
        .required-field::after {
            content: '*';
            color: #CE1126;
            margin-left: 4px;
        }

        .btn-register {
            width: 100%;
            background: linear-gradient(105deg, #CE1126, #A31F34);
            border: none;
            padding: 15px 22px;
            border-radius: 50px;
            color: white;
            font-weight: 800;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 12px 24px -14px rgba(206, 17, 38, 0.45);
            margin-bottom: 24px;
            letter-spacing: 0.8px;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            background: linear-gradient(105deg, #E31B23, #CE1126);
            box-shadow: 0 18px 30px -12px #CE1126;
        }

        .btn-register:active {
            transform: translateY(1px);
        }

        .btn-register i {
            transition: transform 0.2s;
        }

        .btn-register:hover i {
            transform: translateX(5px);
        }

        .login-link {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            font-weight: 600;
        }

        .login-link a {
            color: #0038A8;
            text-decoration: none;
            font-weight: 800;
            transition: color 0.2s;
        }

        .login-link a:hover {
            color: #CE1126;
            text-decoration: underline;
        }

        .info-note {
            background: #F0F4FA;
            border-radius: 24px;
            padding: 12px 18px;
            margin-top: 20px;
            font-size: 11px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #E2E8F0;
        }

        .info-note i {
            color: #CE1126;
            font-size: 14px;
        }

        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 11px;
            color: #7C8BA0;
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        /* Password strength indicator */
        .password-strength {
            margin-top: 8px;
            margin-left: 12px;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .strength-bar {
            width: 60px;
            height: 4px;
            background: #E2E8F0;
            border-radius: 4px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.2s, background 0.2s;
        }

        @media (max-width: 550px) {
            .register-card {
                padding: 28px 22px;
                border-radius: 36px;
            }

            .brand-title {
                font-size: 26px;
            }

            .form-control {
                padding: 14px 15px 14px 48px;
                font-size: 14px;
            }

            .floating-label {
                left: 46px;
                font-size: 13px;
            }

            .btn-register {
                padding: 13px 18px;
                font-size: 14px;
            }

            .psa-logo-img {
                width: 490px;
                height: 100px;
            }
        }
    </style>
</head>

<body>
    <canvas id="canvas-bg"></canvas>
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

                <!-- Position / Job Title -->
                <div class="input-group">
                    <i class="fas fa-briefcase input-icon"></i>
                    <input type="text" name="position" id="position" class="form-control" placeholder=" "
                        value="{{ old('position') }}" required>
                    <label for="position" class="floating-label required-field">Position / Job Title</label>
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
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                        placeholder=" " required>
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
                    Already have an account? <a href="{{ route('login') }}">Sign in here</a>
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

        // Password strength checker
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            const config = {
                0: {
                    width: '0%',
                    text: 'Very Weak',
                    color: '#CE1126'
                },
                1: {
                    width: '20%',
                    text: 'Weak',
                    color: '#E67E22'
                },
                2: {
                    width: '40%',
                    text: 'Fair',
                    color: '#F1C40F'
                },
                3: {
                    width: '60%',
                    text: 'Good',
                    color: '#3498DB'
                },
                4: {
                    width: '80%',
                    text: 'Strong',
                    color: '#2ECC71'
                },
                5: {
                    width: '100%',
                    text: 'Very Strong',
                    color: '#27AE60'
                }
            };
            const level = Math.min(strength, 5);
            const result = config[level];
            if (strengthFill) {
                strengthFill.style.width = result.width;
                strengthFill.style.backgroundColor = result.color;
            }
            if (strengthText) strengthText.textContent = result.text;
            return level >= 3;
        }

        if (passwordField) {
            passwordField.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
            checkPasswordStrength(passwordField.value);
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
    </script>
</body>

</html>
