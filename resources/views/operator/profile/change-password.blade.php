@extends('layouts.operator')

@section('content')

    <body>
        <div class="admin-container">

            <div class="admin-main">

                <div class="admin-content">
                    <div class="profile-container">
                        <div class="profile-card">
                            <div class="card-header">
                                <h2><i class="fas fa-key"></i> Change Password</h2>
                                <p>Update your account password</p>
                            </div>

                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                        <button type="button" class="alert-close"
                                            onclick="this.parentElement.remove()">&times;</button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                        <button type="button" class="alert-close"
                                            onclick="this.parentElement.remove()">&times;</button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul style="margin: 0; padding-left: 20px;">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form id="passwordUpdateForm" method="POST"
                                    action="{{ route('operator.profile.password.update') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label>Current Password <span class="required">*</span></label>
                                        <div class="password-input-wrapper">
                                            <input type="password" name="current_password" id="currentPassword" required
                                                autocomplete="current-password">
                                            <i class="fas fa-eye-slash toggle-password" data-target="currentPassword"></i>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>New Password <span class="required">*</span></label>
                                        <div class="password-input-wrapper">
                                            <input type="password" name="new_password" id="newPassword" required>
                                            <i class="fas fa-eye-slash toggle-password" data-target="newPassword"></i>
                                        </div>
                                        <div class="password-strength">
                                            <div class="strength-bar" id="strengthBar"></div>
                                            <div class="strength-text" id="strengthText"></div>
                                        </div>
                                    </div>

                                    <div class="password-requirements">
                                        <p>Password Requirements:</p>
                                        <div class="requirement" id="reqLength">
                                            <i class="fas fa-circle"></i> At least 8 characters
                                        </div>
                                        <div class="requirement" id="reqUppercase">
                                            <i class="fas fa-circle"></i> At least 1 uppercase letter
                                        </div>
                                        <div class="requirement" id="reqLowercase">
                                            <i class="fas fa-circle"></i> At least 1 lowercase letter
                                        </div>
                                        <div class="requirement" id="reqNumber">
                                            <i class="fas fa-circle"></i> At least 1 number
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Confirm New Password <span class="required">*</span></label>
                                        <div class="password-input-wrapper">
                                            <input type="password" name="new_password_confirmation" id="confirmPassword"
                                                required>
                                            <i class="fas fa-eye-slash toggle-password" data-target="confirmPassword"></i>
                                        </div>
                                        <div id="matchMessage" style="font-size: 11px; margin-top: 5px;"></div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn-save" id="saveBtn">
                                            <i class="fas fa-save"></i> Update Password
                                        </button>
                                        <a href="{{ route('operator.profile.index') }}" class="btn-cancel">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            function showPSALoader() {
                const loader = document.getElementById('psaLoaderModal');
                if (loader) loader.classList.add('show');
            }

            function hidePSALoader() {
                const loader = document.getElementById('psaLoaderModal');
                if (loader) loader.classList.remove('show');
            }

            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const input = document.getElementById(targetId);
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    } else {
                        input.type = 'password';
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    }
                });
            });

            // Password strength checker
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const matchMessage = document.getElementById('matchMessage');

            function checkPasswordStrength(password) {
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[a-z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;

                return {
                    strength,
                    score: strength
                };
            }

            function updateStrengthDisplay() {
                const password = newPassword.value;
                const {
                    strength,
                    score
                } = checkPasswordStrength(password);

                let barClass = '';
                let textClass = '';
                let text = '';

                if (password.length === 0) {
                    barClass = '';
                    text = '';
                } else if (score === 1) {
                    barClass = 'weak';
                    textClass = 'weak';
                    text = 'Weak';
                } else if (score === 2) {
                    barClass = 'medium';
                    textClass = 'medium';
                    text = 'Medium';
                } else if (score === 3) {
                    barClass = 'strong';
                    textClass = 'strong';
                    text = 'Strong';
                } else if (score === 4) {
                    barClass = 'very-strong';
                    textClass = 'very-strong';
                    text = 'Very Strong';
                }

                strengthBar.className = 'strength-bar ' + barClass;
                strengthText.className = 'strength-text ' + textClass;
                strengthText.textContent = text;

                // Update requirements
                document.getElementById('reqLength').className = password.length >= 8 ? 'requirement valid' :
                    'requirement invalid';
                document.getElementById('reqLength').innerHTML = (password.length >= 8 ? '<i class="fas fa-check-circle"></i>' :
                    '<i class="fas fa-circle"></i>') + ' At least 8 characters';

                document.getElementById('reqUppercase').className = password.match(/[A-Z]/) ? 'requirement valid' :
                    'requirement invalid';
                document.getElementById('reqUppercase').innerHTML = (password.match(/[A-Z]/) ?
                        '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-circle"></i>') +
                    ' At least 1 uppercase letter';

                document.getElementById('reqLowercase').className = password.match(/[a-z]/) ? 'requirement valid' :
                    'requirement invalid';
                document.getElementById('reqLowercase').innerHTML = (password.match(/[a-z]/) ?
                        '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-circle"></i>') +
                    ' At least 1 lowercase letter';

                document.getElementById('reqNumber').className = password.match(/[0-9]/) ? 'requirement valid' :
                    'requirement invalid';
                document.getElementById('reqNumber').innerHTML = (password.match(/[0-9]/) ?
                    '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-circle"></i>') + ' At least 1 number';
            }

            function checkPasswordMatch() {
                const password = newPassword.value;
                const confirm = confirmPassword.value;

                if (confirm.length === 0) {
                    matchMessage.innerHTML = '';
                    return;
                }

                if (password === confirm) {
                    matchMessage.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i> Passwords match';
                    matchMessage.style.color = '#10b981';
                } else {
                    matchMessage.innerHTML =
                        '<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i> Passwords do not match';
                    matchMessage.style.color = '#ef4444';
                }
            }

            newPassword.addEventListener('input', updateStrengthDisplay);
            newPassword.addEventListener('input', checkPasswordMatch);
            confirmPassword.addEventListener('input', checkPasswordMatch);

            // Form submission
            const form = document.getElementById('passwordUpdateForm');
            const saveBtn = document.getElementById('saveBtn');

            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = newPassword.value;
                    const confirm = confirmPassword.value;

                    if (password !== confirm) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        return;
                    }

                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters!');
                        return;
                    }

                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                    showPSALoader();
                });
            }

            window.addEventListener('load', function() {
                setTimeout(() => hidePSALoader(), 500);
            });
        </script>
    </body>

    </html>

@endsection
