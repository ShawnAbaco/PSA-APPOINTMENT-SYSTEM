@extends('layouts.staff')

@section('content')
    <!-- Profile Modal -->
    <div class="profile-modal" id="profileModal">
        <div class="profile-modal-content">
            <div class="profile-modal-header">
                <h3><i class="fas fa-user-circle"></i> My Profile</h3>
                <button class="profile-modal-close" id="closeProfileModal">&times;</button>
            </div>
            <div class="profile-modal-body">
                <!-- Tabs -->
                <div class="profile-tabs">
                    <button class="profile-tab active" data-tab="view-tab">Profile</button>
                    <button class="profile-tab" data-tab="edit-tab">Edit Profile</button>
                    <button class="profile-tab" data-tab="password-tab">Change Password</button>
                </div>

                <!-- View Profile Tab -->
                <div class="tab-pane active" id="view-tab">
                    <div class="profile-view-header">
                        @php
                            $avatarPath = Auth::user()->profile_photo ?? null;
                            $userInitial = strtoupper(substr(Auth::user()->first_name, 0, 1));
                        @endphp
                        @if ($avatarPath && file_exists(public_path('storage/' . $avatarPath)))
                            <img src="{{ asset('storage/' . $avatarPath) }}" alt="Profile" class="profile-view-avatar">
                        @else
                            <div class="profile-view-avatar-placeholder">{{ $userInitial }}</div>
                        @endif
                        <h4 class="profile-view-name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h4>
                        <span class="profile-view-role">{{ ucfirst(Auth::user()->role) }}</span>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-envelope"></i></div>
                            <div class="info-content">
                                <div class="info-label">Email Address</div>
                                <div class="info-value">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-phone"></i></div>
                            <div class="info-content">
                                <div class="info-label">Contact Number</div>
                                <div class="info-value">{{ Auth::user()->contact_number ?? 'Not set' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div class="info-content">
                                <div class="info-label">Member Since</div>
                                <div class="info-value">{{ Auth::user()->created_at->format('F d, Y') }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-id-badge"></i></div>
                            <div class="info-content">
                                <div class="info-label">User ID</div>
                                <div class="info-value">#{{ Auth::user()->id }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Tab -->
                <div class="tab-pane" id="edit-tab">
                    <form id="profileUpdateForm" method="POST" action="{{ route('staff.profile.update') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="avatar-section">
                            @php
                                $currentAvatar = Auth::user()->profile_photo ?? null;
                                $avatarUrl =
                                    $currentAvatar && file_exists(public_path('storage/' . $currentAvatar))
                                        ? asset('storage/' . $currentAvatar)
                                        : null;
                            @endphp
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="Profile" class="avatar-preview" id="avatarPreview">
                            @else
                                <div class="avatar-placeholder" id="avatarPlaceholder">{{ $userInitial }}</div>
                                <img style="display:none" class="avatar-preview" id="avatarPreview">
                            @endif
                            <div>
                                <label class="upload-btn">
                                    <i class="fas fa-camera"></i> Change Photo
                                    <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/*"
                                        style="display: none;">
                                </label>
                                <small style="display: block; margin-top: 5px; color: #6b7280; font-size: 11px;">Max 2MB.
                                    JPG, PNG only</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span class="required">*</span></label>
                                <input type="text" name="first_name"
                                    value="{{ old('first_name', Auth::user()->first_name) }}" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name"
                                    value="{{ old('last_name', Auth::user()->last_name) }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                        </div>

                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number"
                                value="{{ old('contact_number', Auth::user()->contact_number) }}"
                                placeholder="e.g., 09123456789">
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-save" id="saveProfileBtn">Save Changes</button>
                            <button type="button" class="btn-cancel cancel-edit">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane" id="password-tab">
                    <form id="passwordUpdateForm" method="POST" action="{{ route('staff.profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Current Password <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="current_password" id="currentPassword" required>
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
                            <div class="requirement" id="reqLength"><i class="fas fa-circle"></i> At least 8 characters
                            </div>
                            <div class="requirement" id="reqUppercase"><i class="fas fa-circle"></i> At least 1 uppercase
                                letter</div>
                            <div class="requirement" id="reqLowercase"><i class="fas fa-circle"></i> At least 1 lowercase
                                letter</div>
                            <div class="requirement" id="reqNumber"><i class="fas fa-circle"></i> At least 1 number</div>
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="new_password_confirmation" id="confirmPassword" required>
                                <i class="fas fa-eye-slash toggle-password" data-target="confirmPassword"></i>
                            </div>
                            <div id="matchMessage" style="font-size: 11px; margin-top: 5px;"></div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-save" id="savePasswordBtn">Update Password</button>
                            <button type="button" class="btn-cancel cancel-password">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Modal Elements
        const profileModal = document.getElementById('profileModal');
        const roleBadge = document.querySelector('.role-badge');
        const closeModalBtn = document.getElementById('closeProfileModal');

        // Open modal when role badge is clicked
        if (roleBadge) {
            roleBadge.style.cursor = 'pointer';
            roleBadge.addEventListener('click', function(e) {
                e.preventDefault();
                profileModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        // Close modal function
        function closeModal() {
            profileModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeModal);
        }

        // Close modal when clicking outside
        profileModal.addEventListener('click', function(e) {
            if (e.target === profileModal) {
                closeModal();
            }
        });

        // Tab switching
        const tabs = document.querySelectorAll('.profile-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Cancel buttons
        document.querySelectorAll('.cancel-edit, .cancel-password').forEach(btn => {
            btn.addEventListener('click', function() {
                // Switch back to view tab
                document.querySelector('.profile-tab[data-tab="view-tab"]').click();
            });
        });

        // Avatar preview
        const photoInput = document.getElementById('profilePhotoInput');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarPlaceholder = document.getElementById('avatarPlaceholder');

        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB');
                        this.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        if (avatarPreview) {
                            avatarPreview.src = event.target.result;
                            avatarPreview.style.display = 'block';
                        }
                        if (avatarPlaceholder) avatarPlaceholder.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
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
            let score = 0;
            if (password.length >= 8) score++;
            if (password.match(/[A-Z]/)) score++;
            if (password.match(/[a-z]/)) score++;
            if (password.match(/[0-9]/)) score++;
            return score;
        }

        function updateStrengthDisplay() {
            const password = newPassword.value;
            const score = checkPasswordStrength(password);

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

        if (newPassword) {
            newPassword.addEventListener('input', updateStrengthDisplay);
            newPassword.addEventListener('input', checkPasswordMatch);
        }
        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkPasswordMatch);
        }

        // Profile Update Form Submission (AJAX)
        const profileForm = document.getElementById('profileUpdateForm');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const saveBtn = document.getElementById('saveProfileBtn');

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                fetch(this.action, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            const editTab = document.getElementById('edit-tab');
                            const successAlert = document.createElement('div');
                            successAlert.className = 'alert alert-success';
                            successAlert.innerHTML = data.message +
                                '<button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>';
                            editTab.insertBefore(successAlert, editTab.firstChild);

                            // Reload page after 1.5 seconds to refresh user data
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            alert(data.message || 'Something went wrong');
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                        }
                    })
                    .catch(error => {
                        alert('Network error occurred');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                    });
            });
        }

        // Password Update Form Submission (AJAX)
        const passwordForm = document.getElementById('passwordUpdateForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const password = newPassword.value;
                const confirm = confirmPassword.value;

                if (password !== confirm) {
                    alert('Passwords do not match!');
                    return;
                }

                if (password.length < 8) {
                    alert('Password must be at least 8 characters!');
                    return;
                }

                const saveBtn = document.getElementById('savePasswordBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

                fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            current_password: document.querySelector('input[name="current_password"]')
                                .value,
                            new_password: password,
                            new_password_confirmation: confirm
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            const passwordTab = document.getElementById('password-tab');
                            const successAlert = document.createElement('div');
                            successAlert.className = 'alert alert-success';
                            successAlert.innerHTML = data.message +
                                '<button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>';
                            passwordTab.insertBefore(successAlert, passwordTab.firstChild);

                            // Clear form
                            passwordForm.reset();

                            // Reset strength display
                            if (strengthBar) strengthBar.className = 'strength-bar';
                            if (strengthText) strengthText.textContent = '';
                            if (matchMessage) matchMessage.innerHTML = '';

                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';

                            // Switch to view tab after 2 seconds
                            setTimeout(() => {
                                document.querySelector('.profile-tab[data-tab="view-tab"]').click();
                            }, 2000);
                        } else {
                            alert(data.message || 'Current password is incorrect');
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                        }
                    })
                    .catch(error => {
                        alert('Network error occurred');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
                    });
            });
        }

        // Show/hide loader functions
        function showPSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.add('show');
        }

        function hidePSALoader() {
            const loader = document.getElementById('psaLoaderModal');
            if (loader) loader.classList.remove('show');
        }

        window.addEventListener('load', function() {
            setTimeout(() => hidePSALoader(), 500);
        });
    </script>
@endsection
