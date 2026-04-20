@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">System Settings</h1>
    
    <form method="POST" action="{{ route('admin.settings.update') }}" id="settingsForm">
        @csrf
        
        <!-- Appointment Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Appointment Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Advance Booking Days</label>
                        <input type="number" name="advance_booking_days" class="form-control" value="{{ $settings['advance_booking_days'] ?? 30 }}" min="1" max="365">
                        <small class="text-muted">How many days in advance users can book</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cancellation Hours</label>
                        <input type="number" name="cancellation_hours" class="form-control" value="{{ $settings['cancellation_hours'] ?? 24 }}" min="1" max="168">
                        <small class="text-muted">Hours before appointment to allow cancellation</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Enable Email Notifications</label>
                        <select name="enable_email" class="form-control">
                            <option value="true" {{ ($settings['enable_email'] ?? true) == true ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ ($settings['enable_email'] ?? true) == false ? 'selected' : '' }}>No</option>
                        </select>
                        <small class="text-muted">Send email notifications for appointment confirmations</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Enable Per-Service Limits</label>
                        <select name="enable_per_service_limits" class="form-control">
                            <option value="true" {{ ($settings['enable_per_service_limits'] ?? true) == true ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ ($settings['enable_per_service_limits'] ?? true) == false ? 'selected' : '' }}>No</option>
                        </select>
                        <small class="text-muted">Enable separate slot limits for each service type</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Service Capacity Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Service Slot Capacities</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> Set default capacity limits for each service type. These will apply to all working days.
                </div>
                
                <div class="row">
                    @php
                        $regCapacity = $serviceConfigs->where('service_code', 'reg')->first()->default_capacity ?? 10;
                        $correctionCapacity = $serviceConfigs->where('service_code', 'correction')->first()->default_capacity ?? 5;
                        $ephilidCapacity = $serviceConfigs->where('service_code', 'ephilid')->first()->default_capacity ?? 3;
                        $trnCapacity = $serviceConfigs->where('service_code', 'trn')->first()->default_capacity ?? 2;
                    @endphp
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">National ID Registration</label>
                        <input type="number" name="reg_capacity" class="form-control" value="{{ $regCapacity }}" min="0" max="100">
                        <small class="text-muted">Slots per day for Registration</small>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Correction/Updating</label>
                        <input type="number" name="correction_capacity" class="form-control" value="{{ $correctionCapacity }}" min="0" max="100">
                        <small class="text-muted">Slots per day for Correction</small>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">ePhilID Issuance</label>
                        <input type="number" name="ephilid_capacity" class="form-control" value="{{ $ephilidCapacity }}" min="0" max="100">
                        <small class="text-muted">Slots per day for ePhilID</small>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">TRN Retrieval</label>
                        <input type="number" name="trn_capacity" class="form-control" value="{{ $trnCapacity }}" min="0" max="100">
                        <small class="text-muted">Slots per day for TRN Retrieval</small>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="button" id="syncSlotsBtn" class="btn btn-warning" onclick="confirmSync()">
                            <i class="fas fa-sync-alt"></i> Sync All Working Slots with Current Capacities
                        </button>
                        <small class="text-muted d-block mt-1">
                            This will update ALL working slots to match the service capacities above.
                            Slots with custom configurations (Half Day, Holiday, Special) will not be affected.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Working Days Configuration -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Working Days Configuration</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Select Working Days</label>
                        <div class="d-flex flex-wrap gap-3">
                            @php
                                $workingDays = isset($settings['working_days']) ? explode(',', $settings['working_days']) : ['1','2','3','4','5'];
                            @endphp
                            <div class="form-check">
                                <input type="checkbox" name="working_days[]" value="1" id="dayMon" class="form-check-input" {{ in_array('1', $workingDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dayMon">Monday</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="working_days[]" value="2" id="dayTue" class="form-check-input" {{ in_array('2', $workingDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dayTue">Tuesday</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="working_days[]" value="3" id="dayWed" class="form-check-input" {{ in_array('3', $workingDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dayWed">Wednesday</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="working_days[]" value="4" id="dayThu" class="form-check-input" {{ in_array('4', $workingDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dayThu">Thursday</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="working_days[]" value="5" id="dayFri" class="form-check-input" {{ in_array('5', $workingDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dayFri">Friday</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="working_days[]" value="6" id="daySat" class="form-check-input" {{ in_array('6', $workingDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="daySat">Saturday</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="working_days[]" value="7" id="daySun" class="form-check-input" {{ in_array('7', $workingDays) ? 'checked' : '' }}>
                                <label class="form-check-label" for="daySun">Sunday</label>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">Select which days appointments can be booked. Non-working days will not appear in the client calendar.</small>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" id="selectWeekdays" class="btn btn-sm btn-outline-secondary me-2">Weekdays (Mon-Fri)</button>
                    <button type="button" id="selectWeekends" class="btn btn-sm btn-outline-secondary me-2">Weekends (Sat-Sun)</button>
                    <button type="button" id="selectAllDays" class="btn btn-sm btn-outline-secondary">All Days</button>
                </div>
            </div>
        </div>
        
        <!-- Email (SMTP) Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Email (SMTP) Settings</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> Configure your SMTP settings to enable email notifications. 
                    For Gmail, you need to use an <strong>App Password</strong> (enable 2FA first).
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="email_host" class="form-control" value="{{ $settings['email_host'] ?? 'smtp.gmail.com' }}" placeholder="smtp.gmail.com">
                        <small class="text-muted">e.g., smtp.gmail.com, smtp.office365.com, mail.yourdomain.com</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="email_port" class="form-control" value="{{ $settings['email_port'] ?? 587 }}" placeholder="587">
                        <small class="text-muted">587 for TLS, 465 for SSL</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Encryption</label>
                        <select name="email_encryption" class="form-control">
                            <option value="tls" {{ ($settings['email_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                            <option value="ssl" {{ ($settings['email_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        </select>
                        <small class="text-muted">Security protocol for email sending</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="email_username" class="form-control" value="{{ $settings['email_username'] ?? '' }}" placeholder="your-email@gmail.com">
                        <small class="text-muted">Your email address (e.g., your-email@gmail.com)</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SMTP Password</label>
                        <div class="position-relative">
                            <input type="password" name="email_password" id="email_password" class="form-control" 
                                   value="{{ $settings['email_password'] ?? '' }}" 
                                   placeholder="Enter new password to change" autocomplete="off"
                                   style="padding-right: 40px;">
                            <button type="button" id="togglePasswordBtn" 
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); 
                                           background: none; border: none; cursor: pointer; color: #6c757d; display: none;">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-lock"></i> 
                            <span id="passwordStatus">
                                @if(!empty($settings['email_password']) && $settings['email_password'] === '********')
                                    Password is set. Enter new password to change it.
                                @elseif(!empty($settings['email_password']))
                                    Password is configured.
                                @else
                                    No password set. Enter your SMTP password.
                                @endif
                            </span>
                            For Gmail: Use an <a href="https://support.google.com/accounts/answer/185833" target="_blank">App Password</a> (enable 2FA first)
                        </small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">From Email Address</label>
                        <input type="email" name="email_from_address" class="form-control" value="{{ $settings['email_from_address'] ?? 'noreply@psa.gov.ph' }}" placeholder="noreply@psa.gov.ph">
                        <small class="text-muted">Sender email address (usually same as SMTP username)</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" name="email_from_name" class="form-control" value="{{ $settings['email_from_name'] ?? 'PSA Appointment System' }}" placeholder="PSA Appointment System">
                        <small class="text-muted">Sender name that recipients will see</small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="button" class="btn btn-outline" id="testEmailBtn">
                        <i class="fas fa-paper-plane"></i> Send Test Email
                    </button>
                    <small class="text-muted ms-2">Test your email configuration by sending a test message</small>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Day selection helpers
    $('#selectWeekdays').click(function() {
        $('input[name="working_days[]"]').each(function() {
            let val = parseInt($(this).val());
            $(this).prop('checked', val >= 1 && val <= 5);
        });
    });
    
    $('#selectWeekends').click(function() {
        $('input[name="working_days[]"]').each(function() {
            let val = parseInt($(this).val());
            $(this).prop('checked', val === 6 || val === 7);
        });
    });
    
    $('#selectAllDays').click(function() {
        $('input[name="working_days[]"]').prop('checked', true);
    });
    
    // Password toggle functionality
    const passwordField = document.getElementById('email_password');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    const passwordStatus = document.getElementById('passwordStatus');
    
    function updateToggleVisibility() {
        if (document.activeElement === passwordField || (passwordField && passwordField.value && passwordField.value.length > 0)) {
            if (toggleBtn) toggleBtn.style.display = 'block';
        } else {
            if (toggleBtn) toggleBtn.style.display = 'none';
            if (passwordField && passwordField.getAttribute('type') === 'text') {
                passwordField.setAttribute('type', 'password');
                if (toggleIcon) {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            }
        }
    }
    
    if (toggleBtn && passwordField) {
        passwordField.addEventListener('focus', function() { toggleBtn.style.display = 'block'; });
        
        passwordField.addEventListener('blur', function() {
            setTimeout(() => {
                if (passwordField.value.length === 0) {
                    toggleBtn.style.display = 'none';
                    if (passwordField.getAttribute('type') === 'text') {
                        passwordField.setAttribute('type', 'password');
                        if (toggleIcon) {
                            toggleIcon.classList.remove('fa-eye-slash');
                            toggleIcon.classList.add('fa-eye');
                        }
                    }
                } else {
                    toggleBtn.style.display = 'block';
                }
            }, 200);
        });
        
        passwordField.addEventListener('input', function() {
            toggleBtn.style.display = 'block';
            if (this.value.length > 0 && passwordStatus) {
                passwordStatus.innerHTML = 'New password will be saved when you submit the form.';
                passwordStatus.style.color = '#f59e0b';
            } else if (this.value.length === 0 && passwordStatus) {
                passwordStatus.innerHTML = 'Leave empty to keep current password.';
                passwordStatus.style.color = '#6c757d';
            }
        });
        
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            if (type === 'text') {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
            passwordField.focus();
        });
        
        updateToggleVisibility();
    }
    
    // Test email functionality
    $('#testEmailBtn').click(function() {
        const testEmail = prompt('Enter email address to send test email:', '{{ Auth::user()->email }}');
        if (!testEmail) return;
        
        const originalText = $(this).html();
        $(this).html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.settings.test-email") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                email: testEmail
            },
            success: function(response) {
                if (response.success) {
                    showToast('Success', response.message, 'success');
                } else {
                    showToast('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to send test email. Check your SMTP settings.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showToast('Error', errorMsg, 'error');
            },
            complete: function() {
                $('#testEmailBtn').html(originalText).prop('disabled', false);
            }
        });
    });
});

// Sync slots function
function confirmSync() {
    if(confirm('This will update ALL working slots to match the service capacities above. Existing custom capacities will be overwritten. Continue?')) {
        showLoading();
        fetch('{{ route("admin.settings.sync-slots") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            showToast(data.success ? 'Success' : 'Error', data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(error => {
            hideLoading();
            showToast('Error', 'Failed to sync slots', 'error');
        });
    }
}

function showLoading() {
    $('#loadingOverlay').show();
}

function hideLoading() {
    $('#loadingOverlay').hide();
}

function showToast(title, message, type) {
    alert(message);
}
</script>

<style>
    .position-relative { position: relative; }
    #togglePasswordBtn {
        background: none;
        border: none;
        cursor: pointer;
        color: #6c757d;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    #togglePasswordBtn:hover { color: #2c5f8a; }
    #togglePasswordBtn:focus { outline: none; }
    #loadingOverlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        text-align: center;
        padding-top: 20%;
    }
</style>

<div id="loadingOverlay">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="text-white mt-2">Syncing slots...</p>
</div>
@endpush
@endsection