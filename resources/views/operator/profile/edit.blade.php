@extends('layouts.operator')

@section('content')

    <body>
        <div class="admin-container">

            <div class="admin-main">

                <div class="admin-content">
                    <div class="profile-container">
                        <div class="profile-card">
                            <div class="card-header">
                                <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
                                <p>Update your personal information and profile picture</p>
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

                                <form id="profileUpdateForm" method="POST" action="{{ route('operator.profile.update') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="avatar-section">
                                        @php
                                            $avatarPath = Auth::user()->profile_photo ?? null;
                                            $userInitial = strtoupper(substr(Auth::user()->first_name, 0, 1));
                                        @endphp
                                        @if ($avatarPath && file_exists(public_path('storage/' . $avatarPath)))
                                            <img src="{{ asset('storage/' . $avatarPath) }}" alt="Profile"
                                                class="avatar-preview" id="avatarPreview">
                                        @else
                                            <div class="avatar-placeholder" id="avatarPlaceholder">{{ $userInitial }}
                                            </div>
                                            <img style="display:none" class="avatar-preview" id="avatarPreview">
                                        @endif
                                        <div>
                                            <label class="upload-btn">
                                                <i class="fas fa-camera"></i> Choose New Photo
                                                <input type="file" name="profile_photo" id="profilePhotoInput"
                                                    accept="image/*" style="display: none;">
                                            </label>
                                            <small style="display: block; margin-top: 8px; color: #6b7280;">Max 2MB.
                                                JPG,
                                                PNG, GIF only</small>
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
                                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label>Contact Number</label>
                                        <input type="text" name="contact_number"
                                            value="{{ old('contact_number', Auth::user()->contact_number) }}"
                                            placeholder="e.g., 09123456789">
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn-save" id="saveBtn">
                                            <i class="fas fa-save"></i> Save Changes
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

            // Form submission
            const form = document.getElementById('profileUpdateForm');
            const saveBtn = document.getElementById('saveBtn');

            if (form) {
                form.addEventListener('submit', function() {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
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
