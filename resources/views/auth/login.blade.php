<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PSA Appointment System | Official Login Portal</title>

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
            background: radial-gradient(circle at 40% 50%, rgba(15, 43, 94, 0.3) 0%, rgba(0, 0, 0, 0.2) 100%);
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

        /* LOGIN CARD */
        .login-wrapper {
            position: relative;
            z-index: 20;
            width: 100%;
            max-width: 500px;
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

        .login-card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(12px);
            border-radius: 48px;
            padding: 42px 38px;
            box-shadow: 0 35px 70px -20px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(206, 17, 38, 0.2);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 45px 80px -20px rgba(0, 0, 0, 0.5);
        }

        .login-header {
            text-align: center;
            margin-bottom: 36px;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .psa-logo-img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.15));
            transition: transform 0.25s ease;
        }

        .psa-logo-img:hover {
            transform: scale(1.03);
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
            padding: 14px 20px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            font-weight: 600;
            color: #991B1B;
            animation: gentleShake 0.4s ease;
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

        .alert-success {
            background: #E6F7EC;
            border-left-color: #2E7D32;
            color: #1B5E20;
        }

        /* Form inputs */
        .input-group {
            margin-bottom: 28px;
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #8A99B4;
            font-size: 18px;
            pointer-events: none;
            z-index: 2;
            transition: color 0.2s;
        }

        .form-control {
            width: 100%;
            padding: 17px 20px 17px 52px;
            font-size: 15px;
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
            box-shadow: 0 0 0 5px rgba(206, 17, 38, 0.12);
        }

        .floating-label {
            position: absolute;
            left: 40px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            padding: 0 6px;
            color: #8A99B4;
            font-size: 18px;
            font-weight: 500;
            pointer-events: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .form-control:focus+.floating-label,
        .form-control:not(:placeholder-shown)+.floating-label {
            transform: translateY(-32px) translateX(-12px) scale(0.85);
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            font-size: 13px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #334155;
            font-weight: 600;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            accent-color: #CE1126;
            cursor: pointer;
        }

        .forgot-link {
            color: #0038A8;
            text-decoration: none;
            font-weight: 700;
            transition: 0.2s;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }

        .forgot-link:hover {
            color: #CE1126;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(105deg, #CE1126, #A31F34);
            border: none;
            padding: 16px 22px;
            border-radius: 50px;
            color: white;
            font-weight: 800;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 12px 24px -14px rgba(206, 17, 38, 0.45);
            margin-bottom: 30px;
            letter-spacing: 0.8px;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            background: linear-gradient(105deg, #E31B23, #CE1126);
            box-shadow: 0 18px 30px -12px #CE1126;
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .btn-login i {
            transition: transform 0.2s;
        }

        .btn-login:hover i {
            transform: translateX(5px);
        }

        .demo-card {
            background: #F8FAFE;
            border-radius: 32px;
            padding: 18px 24px;
            border: 1px solid rgba(206, 17, 38, 0.15);
            transition: all 0.2s;
        }

        .demo-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1E293B;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            letter-spacing: 1px;
        }

        .demo-title i {
            color: #CE1126;
            font-size: 14px;
        }

        .demo-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #E9EEF3;
            font-size: 13px;
        }

        .demo-row:last-child {
            border-bottom: none;
        }

        .demo-role {
            font-weight: 700;
            color: #0038A8;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .demo-role i {
            width: 22px;
            color: #CE1126;
        }

        .demo-creds {
            background: white;
            padding: 5px 14px;
            border-radius: 40px;
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 12px;
            color: #B11226;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .demo-note {
            margin-top: 14px;
            font-size: 11px;
            background: rgba(206, 17, 38, 0.06);
            border-radius: 30px;
            padding: 9px 14px;
            display: flex;
            gap: 8px;
            color: #475569;
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

        @media (max-width: 550px) {
            .login-card {
                padding: 32px 24px;
                border-radius: 36px;
            }

            .brand-title {
                font-size: 26px;
            }

            .form-control {
                padding: 15px 15px 15px 50px;
                font-size: 14px;
            }

            .floating-label {
                left: 50px;
                font-size: 14px;
            }

            .form-control:focus+.floating-label,
            .form-control:not(:placeholder-shown)+.floating-label {
                transform: translateY(-30px) translateX(-10px) scale(0.82);
            }

            .demo-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .btn-login {
                padding: 14px 18px;
                font-size: 15px;
            }

            .psa-logo-img {
                width: 75px;
                height: 75px;
            }
        }
    </style>
</head>

<body>

    <!-- MODERN CANVAS BACKGROUND -->
    <canvas id="canvas-bg"></canvas>
    <div class="gradient-overlay"></div>
    <div class="vignette"></div>

    <!-- LOGIN CARD -->
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <img src="{{ asset('images/psa.png') }}" alt="PSA Logo" class="psa-logo-img"
                        onerror="this.src='https://via.placeholder.com/90?text=PSA'">
                </div>
                <h1 class="brand-title">PSA Appointment System</h1>
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
                    <button type="button" class="forgot-link"
                        onclick="alert('📧 Password reset link would be sent to your registered email. Please contact PSA IT Support.')">
                        Forgot password?
                    </button>
                </div>

                <button type="submit" class="btn-login">
                    <span>LOGIN TO DASHBOARD</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Demo Credentials -->
            <div class="demo-card">
                <div class="demo-title">
                    <i class="fas fa-flask"></i> DEMO ACCESS
                </div>
                <div class="demo-row">
                    <span class="demo-role"><i class="fas fa-user-shield"></i> Administrator</span>
                    <span class="demo-creds">admin / admin123</span>
                </div>
                <div class="demo-row">
                    <span class="demo-role"><i class="fas fa-user-tie"></i> Staff Officer</span>
                    <span class="demo-creds">juan.delacruz / staff123</span>
                </div>
                <div class="demo-note">
                    <i class="fas fa-info-circle"></i> Use demo credentials to explore the appointment system.
                </div>
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

        // Remember Me functionality with localStorage (for UI convenience)
        if (localStorage.getItem('psa_remember_username')) {
            const savedUsername = localStorage.getItem('psa_remember_username');
            document.getElementById('username').value = savedUsername;
            document.getElementById('rememberCheck').checked = true;
        }

        document.getElementById('rememberCheck')?.addEventListener('change', function(e) {
            const username = document.getElementById('username').value;
            if (e.target.checked && username) {
                localStorage.setItem('psa_remember_username', username);
            } else if (!e.target.checked) {
                localStorage.removeItem('psa_remember_username');
            }
        });

        // Save username when typing if remember me is checked
        document.getElementById('username')?.addEventListener('input', function() {
            const rememberCheck = document.getElementById('rememberCheck');
            if (rememberCheck && rememberCheck.checked && this.value) {
                localStorage.setItem('psa_remember_username', this.value);
            }
        });
    </script>
</body>

</html>
