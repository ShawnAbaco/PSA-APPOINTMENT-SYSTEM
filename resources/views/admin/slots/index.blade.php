@extends('layouts.admin')

@section('content')
    <meta name="slots-working-days" content="{{ $workingDays ?? '1,2,3,4,5' }}">
    <meta name="slots-csrf-token" content="{{ csrf_token() }}">

    <div class="slots-container">
        <!-- Header Section -->
        <div class="slots-welcome-section">
            <div>
                <h1 class="slots-title">Slot Management</h1>
                <p class="slots-subtitle">Manage daily appointment capacity, time slots, and special dates</p>
            </div>
            <div class="slots-date-display">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="slots-action-buttons">
            <button type="button" class="slots-btn slots-btn-outline-primary" id="slotsCapacityRulesBtn">
                <i class="fas fa-sliders-h"></i> Default Capacity Rules
            </button>
            <button type="button" class="slots-btn slots-btn-outline-success" id="slotsBulkGenerateBtn">
                <i class="fas fa-calendar-plus"></i> Bulk Generate
            </button>
            <button type="button" class="slots-btn slots-btn-primary" id="slotsAddSingleSlotBtn">
                <i class="fas fa-plus-circle"></i> Add Single Slot
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="slots-stats-grid">
            <div class="slots-stat-card">
                <div class="slots-stat-card-content">
                    <div class="slots-stat-info">
                        <h6 class="slots-stat-label">Total Slots</h6>
                        <h2 class="slots-stat-value">{{ $totalSlots }}</h2>
                        <p class="slots-stat-trend"><i class="fas fa-calendar-week"></i> All time slots</p>
                    </div>
                    <div class="slots-stat-icon-circle"><i class="fas fa-calendar-week"></i></div>
                </div>
            </div>

            <div class="slots-stat-card">
                <div class="slots-stat-card-content">
                    <div class="slots-stat-info">
                        <h6 class="slots-stat-label">Half Days</h6>
                        <h2 class="slots-stat-value slots-text-warning">{{ $totalHalfDays ?? 0 }}</h2>
                        <p class="slots-stat-trend"><i class="fas fa-sun"></i> Half day schedules</p>
                    </div>
                    <div class="slots-stat-icon-circle warning-bg"><i class="fas fa-sun"></i></div>
                </div>
            </div>

            <div class="slots-stat-card">
                <div class="slots-stat-card-content">
                    <div class="slots-stat-info">
                        <h6 class="slots-stat-label">Holidays</h6>
                        <h2 class="slots-stat-value slots-text-danger">{{ $totalHolidays }}</h2>
                        <p class="slots-stat-trend"><i class="fas fa-calendar-times"></i> Non-working days</p>
                    </div>
                    <div class="slots-stat-icon-circle danger-bg"><i class="fas fa-calendar-times"></i></div>
                </div>
            </div>

            <div class="slots-stat-card">
                <div class="slots-stat-card-content">
                    <div class="slots-stat-info">
                        <h6 class="slots-stat-label">Total Booked</h6>
                        <h2 class="slots-stat-value slots-text-success">{{ $totalBooked }}</h2>
                        <p class="slots-stat-trend"><i class="fas fa-users"></i> Appointments booked</p>
                    </div>
                    <div class="slots-stat-icon-circle success-bg"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>

        <!-- Calendar Card -->
        <div class="slots-card">
            <div class="slots-card-header">
                <h5 class="slots-card-title"><i class="fas fa-calendar-alt"></i> Slot Calendar</h5>
                <div class="slots-calendar-controls">
                    <button id="slotsPrevMonth" class="slots-calendar-nav-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h4 id="slotsCalendarMonthYear" class="slots-calendar-month-title"></h4>
                    <button id="slotsNextMonth" class="slots-calendar-nav-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button id="slotsTodayBtn" class="slots-btn slots-btn-sm slots-btn-outline-primary">
                        <i class="fas fa-calendar-day"></i> Today
                    </button>
                </div>
            </div>
            <div class="slots-card-body">
                <div class="slots-calendar-wrapper">
                    <div class="slots-calendar-weekdays">
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                        <div>Sun</div>
                    </div>
                    <div class="slots-calendar-days" id="slotsCalendarDays">
                        <div class="slots-loading-state">
                            <i class="fas fa-spinner fa-pulse"></i>
                            <p>Loading calendar...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Slot Modal -->
    <div class="slots-modal" id="slotsCreateSlotModal">
        <div class="slots-modal-dialog slots-modal-lg">
            <div class="slots-modal-content">
                <div class="slots-modal-header slots-modal-header-primary">
                    <h5 class="slots-modal-title"><i class="fas fa-plus-circle"></i> Create Appointment Slot</h5>
                    <span class="slots-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body">
                    <form id="slotsCreateForm">
                        @csrf
                        <div class="slots-alert slots-alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Working Days:</strong> <span id="workingDaysDisplay"></span><br>
                            <small>Only these days are available for booking.</small>
                        </div>

                        <div class="slots-form-row">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Date *</label>
                                <input type="date" name="date" id="create_date" class="slots-form-control" required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Time Slot *</label>
                                <select name="time_slot_id" id="create_time_slot_id" class="slots-form-control" required>
                                    <option value="">Select Time Slot</option>
                                    @foreach ($timeSlots as $timeSlot)
                                        <option value="{{ $timeSlot->id }}">{{ $timeSlot->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="slots-form-group">
                            <label class="slots-form-label">Day Type *</label>
                            <select name="day_type" id="create_day_type" class="slots-form-control" required>
                                <option value="working">Working Day</option>
                                <option value="half_day">Half Day (50% Capacity)</option>
                                <option value="holiday">Holiday (No Appointments)</option>
                                <option value="special">Special Day</option>
                            </select>
                        </div>

                        <div class="slots-form-row" id="createCapacityFields">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Registration (R) Capacity</label>
                                <input type="number" name="reg_capacity" id="create_reg_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Updating (U) Capacity</label>
                                <input type="number" name="updating_capacity" id="create_updating_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Inquiry (S) Capacity</label>
                                <input type="number" name="inquiry_capacity" id="create_inquiry_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                        </div>

                        <div class="slots-form-group">
                            <label class="slots-form-label">Notes</label>
                            <textarea name="notes" class="slots-form-control" rows="2" placeholder="Optional notes"></textarea>
                        </div>

                        <div class="slots-modal-footer">
                            <button type="button" class="slots-btn slots-btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="slots-btn slots-btn-primary">Create Slot</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Capacity Rules Modal -->
    <div class="slots-modal" id="slotsCapacityRulesModal">
        <div class="slots-modal-dialog slots-modal-xl">
            <div class="slots-modal-content">
                <div class="slots-modal-header">
                    <h5 class="slots-modal-title"><i class="fas fa-sliders-h"></i> Default Capacity Rules</h5>
                    <span class="slots-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body">
                    <div class="slots-alert slots-alert-info">
                        <i class="fas fa-info-circle"></i>
                        Default capacities for working days (Monday-Friday). Non-working days have 0 capacity by default.
                    </div>
                    <form id="slotsCapacityRulesForm">
                        @csrf
                        <div class="slots-table-responsive">
                            <table class="slots-table">
                                <thead>
                                    <tr>
                                        <th>Time Slot</th>
                                        <th>Registration (R)</th>
                                        <th>Updating (U)</th>
                                        <th>Inquiry (S)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($timeSlots as $timeSlot)
                                        @php
                                            $rule = $capacityRules[$timeSlot->id]->firstWhere('day_type', 'working') ?? null;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $timeSlot->label }}</strong><br>
                                                <small>{{ date('g:i A', strtotime($timeSlot->start_time)) }} - {{ date('g:i A', strtotime($timeSlot->end_time)) }}</small>
                                            </td>
                                            <td>
                                                <input type="number" name="capacities[{{ $timeSlot->id }}][reg]"
                                                    class="slots-form-control slots-form-control-sm"
                                                    value="{{ $rule->reg_capacity ?? 4 }}" min="0" max="100">
                                            </td>
                                            <td>
                                                <input type="number" name="capacities[{{ $timeSlot->id }}][updating]"
                                                    class="slots-form-control slots-form-control-sm"
                                                    value="{{ $rule->updating_capacity ?? 4 }}" min="0" max="100">
                                            </td>
                                            <td>
                                                <input type="number" name="capacities[{{ $timeSlot->id }}][inquiry]"
                                                    class="slots-form-control slots-form-control-sm"
                                                    value="{{ $rule->inquiry_capacity ?? 4 }}" min="0" max="100">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="slots-modal-footer">
                            <button type="button" class="slots-btn slots-btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="slots-btn slots-btn-primary">Save Rules</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Generate Modal -->
    <div class="slots-modal" id="slotsBulkGenerateModal">
        <div class="slots-modal-dialog slots-modal-lg">
            <div class="slots-modal-content">
                <div class="slots-modal-header slots-modal-header-success">
                    <h5 class="slots-modal-title"><i class="fas fa-calendar-plus"></i> Bulk Generate Slots</h5>
                    <span class="slots-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body">
                    <form id="slotsBulkGenerateForm">
                        @csrf
                        <div class="slots-form-row">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Start Date *</label>
                                <input type="date" name="start_date" id="bulk_start_date" class="slots-form-control" required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">End Date *</label>
                                <input type="date" name="end_date" id="bulk_end_date" class="slots-form-control" required>
                            </div>
                        </div>

                        <div class="slots-form-group">
                            <label class="slots-form-label">Time Slot *</label>
                            <select name="time_slot_id" id="bulk_time_slot_id" class="slots-form-control" required>
                                <option value="">Select Time Slot</option>
                                @foreach ($timeSlots as $timeSlot)
                                    <option value="{{ $timeSlot->id }}">{{ $timeSlot->label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="slots-form-row">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Registration (R) Capacity</label>
                                <input type="number" name="reg_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Updating (U) Capacity</label>
                                <input type="number" name="updating_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Inquiry (S) Capacity</label>
                                <input type="number" name="inquiry_capacity" class="slots-form-control" value="4" min="0" max="100">
                            </div>
                        </div>

                        <div class="slots-form-group">
                            <label class="slots-form-label">Days to Include</label>
                            <div class="slots-checkbox-group">
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="1" checked> Monday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="2" checked> Tuesday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="3" checked> Wednesday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="4" checked> Thursday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="5" checked> Friday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="6"> Saturday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="7"> Sunday</label>
                            </div>
                        </div>

                        <div class="slots-modal-footer">
                            <button type="button" class="slots-btn slots-btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="slots-btn slots-btn-success">Generate Slots</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Slot Detail Modal -->
    <div class="slots-modal" id="slotsSlotDetailModal">
        <div class="slots-modal-dialog slots-modal-lg">
            <div class="slots-modal-content">
                <div class="slots-modal-header">
                    <h5 class="slots-modal-title"><i class="fas fa-info-circle"></i> Slot Details</h5>
                    <span class="slots-modal-close" data-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body" id="slotsSlotDetailBody">
                    <div class="slots-loading-state">
                        <i class="fas fa-spinner fa-pulse"></i>
                        <p>Loading slot details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        let slotsCurrentDate = new Date();
        let slotsData = {};

        // Display working days
        const workingDaysMeta = document.querySelector('meta[name="slots-working-days"]');
        const workingDays = workingDaysMeta ? workingDaysMeta.getAttribute('content').split(',').map(Number) : [1,2,3,4,5];
        const dayNames = {1:'Monday',2:'Tuesday',3:'Wednesday',4:'Thursday',5:'Friday',6:'Saturday',7:'Sunday'};
        const workingDaysText = workingDays.map(d => dayNames[d]).join(', ');
        document.getElementById('workingDaysDisplay') && (document.getElementById('workingDaysDisplay').textContent = workingDaysText);

        function slotsFormatDate(date) {
            let d = new Date(date);
            let month = String(d.getMonth() + 1).padStart(2, '0');
            let day = String(d.getDate()).padStart(2, '0');
            return `${d.getFullYear()}-${month}-${day}`;
        }

        async function slotsLoadSlots() {
            const year = slotsCurrentDate.getFullYear();
            const month = slotsCurrentDate.getMonth() + 1;

            try {
                const response = await fetch(`/admin/slots/json?month=${month}&year=${year}`);
                const data = await response.json();
                slotsData = data.slots || {};
                slotsRenderCalendar();
            } catch (error) {
                console.error('Error loading slots:', error);
                document.getElementById('slotsCalendarDays').innerHTML = '<div class="slots-loading-state"><i class="fas fa-exclamation-triangle"></i><p>Failed to load slots.</p></div>';
            }
        }

        function slotsRenderCalendar() {
            const year = slotsCurrentDate.getFullYear();
            const month = slotsCurrentDate.getMonth();
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('slotsCalendarMonthYear').textContent = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1);
            const startOffset = (firstDay.getDay() || 7) - 1;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let html = '';

            for (let i = 0; i < startOffset; i++) {
                html += '<div class="slots-calendar-day slots-empty"></div>';
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const date = new Date(year, month, d);
                const dateKey = slotsFormatDate(date);
                const slot = slotsData[dateKey];
                const isPast = date < today;
                const isToday = slotsFormatDate(date) === slotsFormatDate(new Date());

                let dayOfWeek = date.getDay();
                let dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
                const isWorkingDay = workingDays.includes(dayNumber);

                let dayClass = 'slots-calendar-day';
                if (isPast) dayClass += ' slots-past';
                if (isToday) dayClass += ' slots-today';
                if (!isWorkingDay) dayClass += ' slots-non-working';

                let content = `<div class="slots-day-number">${d}</div>`;

                if (!isWorkingDay && !slot) {
                    content += '<div class="slots-badge-icon" style="background:#6c757d; color:white;">🔒 Non-working</div>';
                } else if (slot && Object.keys(slot).length > 0) {
                    const firstSlot = Object.values(slot)[0];
                    
                    if (firstSlot.day_type === 'holiday') {
                        dayClass += ' slots-holiday';
                        content = `<div class="slots-day-number">${d}</div><div class="slots-badge-icon" style="background:#dc3545; color:white;">🎄 Holiday</div>`;
                    } else if (firstSlot.day_type === 'half_day') {
                        dayClass += ' slots-half-day';
                        content = `<div class="slots-day-number">${d}</div><div class="slots-badge-icon" style="background:#ffc107;">🌙 Half Day</div>`;
                    }

                    let totalRegAvailable = 0, totalUpdatingAvailable = 0, totalInquiryAvailable = 0;
                    let totalRegCapacity = 0, totalUpdatingCapacity = 0, totalInquiryCapacity = 0;

                    for (const timeSlotData of Object.values(slot)) {
                        totalRegAvailable += timeSlotData.reg_available || 0;
                        totalUpdatingAvailable += timeSlotData.updating_available || 0;
                        totalInquiryAvailable += timeSlotData.inquiry_available || 0;
                        totalRegCapacity += timeSlotData.reg_capacity || 0;
                        totalUpdatingCapacity += timeSlotData.updating_capacity || 0;
                        totalInquiryCapacity += timeSlotData.inquiry_capacity || 0;
                    }

                    let badgesHtml = '<div class="slots-service-badges">';
                    if (totalRegCapacity > 0) {
                        let percent = totalRegCapacity > 0 ? (totalRegAvailable / totalRegCapacity) * 100 : 0;
                        let statusClass = totalRegAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' : 'slots-available');
                        badgesHtml += `<span class="slots-service-badge ${statusClass}">R${totalRegAvailable}</span>`;
                    }
                    if (totalUpdatingCapacity > 0) {
                        let percent = totalUpdatingCapacity > 0 ? (totalUpdatingAvailable / totalUpdatingCapacity) * 100 : 0;
                        let statusClass = totalUpdatingAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' : 'slots-available');
                        badgesHtml += `<span class="slots-service-badge ${statusClass}">U${totalUpdatingAvailable}</span>`;
                    }
                    if (totalInquiryCapacity > 0) {
                        let percent = totalInquiryCapacity > 0 ? (totalInquiryAvailable / totalInquiryCapacity) * 100 : 0;
                        let statusClass = totalInquiryAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' : 'slots-available');
                        badgesHtml += `<span class="slots-service-badge ${statusClass}">S${totalInquiryAvailable}</span>`;
                    }
                    badgesHtml += '</div>';
                    content += badgesHtml;
                } else if (isWorkingDay && !isPast) {
                    content += '<div class="slots-badge-icon" style="background:#6c757d; color:white;">⚙️ Not set</div>';
                }

                html += `<div class="${dayClass}" onclick="slotsShowSlotDetails('${dateKey}')">${content}</div>`;
            }

            document.getElementById('slotsCalendarDays').innerHTML = html;
        }

        window.slotsShowSlotDetails = async function(dateKey) {
            const modal = document.getElementById('slotsSlotDetailModal');
            const modalBody = document.getElementById('slotsSlotDetailBody');
            modal.classList.add('show');
            modalBody.innerHTML = '<div class="slots-loading-state"><i class="fas fa-spinner fa-pulse"></i><p>Loading...</p></div>';

            try {
                const response = await fetch(`/admin/slots/details/${dateKey}`);
                const data = await response.json();

                if (data.success && data.slots.length > 0) {
                    let html = '<div class="slots-table-responsive"><table class="slots-table"><thead><tr><th>Time Slot</th><th>Day Type</th><th>R</th><th>U</th><th>S</th><th>Booked</th><th>Available</th><th>Actions</th></tr></thead><tbody>';
                    for (const slot of data.slots) {
                        const total = (slot.reg_capacity || 0) + (slot.updating_capacity || 0) + (slot.inquiry_capacity || 0);
                        const booked = (slot.reg_booked || 0) + (slot.updating_booked || 0) + (slot.inquiry_booked || 0);
                        html += `<tr>
                            <td><strong>${slot.time_slot_label}</strong></td>
                            <td><span class="slots-day-type-badge ${slot.day_type}">${slot.day_type}</span></td>
                            <td>${slot.reg_capacity || 0}</td>
                            <td>${slot.updating_capacity || 0}</td>
                            <td>${slot.inquiry_capacity || 0}</td>
                            <td>${booked}</td>
                            <td>${total - booked}</td>
                            <td><a href="/admin/slots/${slot.id}/edit" class="slots-btn slots-btn-sm slots-btn-outline-primary"><i class="fas fa-edit"></i></a></td>
                        </tr>`;
                    }
                    html += '</tbody></table></div>';
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = '<div class="slots-alert slots-alert-info">No slots configured for this date.</div>';
                }
            } catch (error) {
                modalBody.innerHTML = '<div class="slots-alert" style="background:#fee2e2; color:#991b1b;">Error loading details.</div>';
            }
        };

        // Modal functions
        function openModal(modalId) { document.getElementById(modalId).classList.add('show'); }
        function closeModal(modalId) { document.getElementById(modalId).classList.remove('show'); }

        document.querySelectorAll('.slots-modal-close, [data-dismiss="modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.slots-modal.show').forEach(modal => modal.classList.remove('show'));
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('slots-modal')) event.target.classList.remove('show');
        });

        // Create Slot Form
        document.getElementById('slotsAddSingleSlotBtn')?.addEventListener('click', () => openModal('slotsCreateSlotModal'));
        
        document.getElementById('slotsCreateForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', document.querySelector('meta[name="slots-csrf-token"]').content);

            try {
                const response = await fetch('{{ route("admin.slots.store") }}', { method: 'POST', body: formData });
                const data = await response.json();
                alert(data.message || (data.success ? 'Slot created successfully!' : 'Error creating slot'));
                if (data.success) {
                    closeModal('slotsCreateSlotModal');
                    slotsLoadSlots();
                    this.reset();
                }
            } catch (error) {
                alert('Error creating slot');
            }
        });

        // Day type change handler for create form
        document.getElementById('create_day_type')?.addEventListener('change', function() {
            const capacityFields = document.getElementById('createCapacityFields');
            if (this.value === 'holiday') {
                capacityFields.style.display = 'none';
                document.getElementById('create_reg_capacity').value = 0;
                document.getElementById('create_updating_capacity').value = 0;
                document.getElementById('create_inquiry_capacity').value = 0;
            } else {
                capacityFields.style.display = 'flex';
                if (this.value === 'half_day') {
                    document.getElementById('create_reg_capacity').value = 2;
                    document.getElementById('create_updating_capacity').value = 2;
                    document.getElementById('create_inquiry_capacity').value = 2;
                } else {
                    document.getElementById('create_reg_capacity').value = 4;
                    document.getElementById('create_updating_capacity').value = 4;
                    document.getElementById('create_inquiry_capacity').value = 4;
                }
            }
        });

        // Check if date is working day
        document.getElementById('create_date')?.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const dayOfWeek = selectedDate.getDay();
            let dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;
            
            if (!workingDays.includes(dayNumber)) {
                const dayNamesFull = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                if (!confirm(`Warning: ${dayNamesFull[dayOfWeek]} is set as a NON-WORKING day. Continue?`)) {
                    this.value = '';
                }
            }
        });

        // Capacity Rules Modal
        document.getElementById('slotsCapacityRulesBtn')?.addEventListener('click', () => openModal('slotsCapacityRulesModal'));
        
        document.getElementById('slotsCapacityRulesForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', document.querySelector('meta[name="slots-csrf-token"]').content);

            try {
                const response = await fetch('{{ route("admin.slots.capacity-rules") }}', { method: 'POST', body: formData });
                const data = await response.json();
                alert(data.message || (data.success ? 'Rules saved!' : 'Error saving rules'));
                if (data.success) closeModal('slotsCapacityRulesModal');
            } catch (error) {
                alert('Error saving rules');
            }
        });

        // Bulk Generate Modal
        document.getElementById('slotsBulkGenerateBtn')?.addEventListener('click', () => openModal('slotsBulkGenerateModal'));
        
        document.getElementById('slotsBulkGenerateForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', document.querySelector('meta[name="slots-csrf-token"]').content);

            try {
                const response = await fetch('{{ route("admin.slots.bulk-generate") }}', { method: 'POST', body: formData });
                const data = await response.json();
                alert(data.message || (data.success ? 'Slots generated!' : 'Error generating slots'));
                if (data.success) {
                    closeModal('slotsBulkGenerateModal');
                    slotsLoadSlots();
                }
            } catch (error) {
                alert('Error generating slots');
            }
        });

        // Calendar navigation
        document.getElementById('slotsPrevMonth')?.addEventListener('click', () => { slotsCurrentDate.setMonth(slotsCurrentDate.getMonth() - 1); slotsLoadSlots(); });
        document.getElementById('slotsNextMonth')?.addEventListener('click', () => { slotsCurrentDate.setMonth(slotsCurrentDate.getMonth() + 1); slotsLoadSlots(); });
        document.getElementById('slotsTodayBtn')?.addEventListener('click', () => { slotsCurrentDate = new Date(); slotsLoadSlots(); });

        // Initialize
        slotsLoadSlots();
    </script>

    <style>
        .slots-title { font-size: 28px; font-weight: 600; margin: 0 0 5px 0; }
        .slots-subtitle { font-size: 14px; opacity: 0.9; margin: 0; }
        .slots-date-display { background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 10px; font-size: 16px; }
        .slots-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .slots-stat-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 20px; }
        .slots-stat-card-content { display: flex; justify-content: space-between; align-items: center; }
        .slots-stat-label { font-size: 13px; color: #6c757d; margin: 0 0 5px 0; }
        .slots-stat-value { font-size: 32px; font-weight: 700; margin: 0; }
        .slots-stat-trend { font-size: 12px; color: #6c757d; margin: 5px 0 0 0; }
        .slots-stat-icon-circle { width: 50px; height: 50px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #667eea; }
        .slots-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; overflow: hidden; }
        .slots-card-header { padding: 20px 25px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .slots-card-title { font-size: 18px; font-weight: 600; margin: 0; }
        .slots-card-body { padding: 20px 25px; }
        .slots-action-buttons { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .slots-btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; border: none; transition: all 0.3s; }
        .slots-btn-primary { background: #4361ee; color: white; }
        .slots-btn-primary:hover { background: #3a56d4; }
        .slots-btn-outline-primary { background: transparent; border: 1px solid #4361ee; color: #4361ee; }
        .slots-btn-outline-primary:hover { background: #4361ee; color: white; }
        .slots-btn-outline-success { background: transparent; border: 1px solid #28a745; color: #28a745; }
        .slots-btn-outline-success:hover { background: #28a745; color: white; }
        .slots-btn-outline-secondary { background: transparent; border: 1px solid #6c757d; color: #6c757d; }
        .slots-btn-outline-secondary:hover { background: #6c757d; color: white; }
        .slots-btn-sm { padding: 5px 12px; font-size: 12px; }
        .slots-calendar-wrapper { overflow-x: auto; }
        .slots-calendar-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; }
        .slots-calendar-weekdays div { padding: 12px; text-align: center; font-weight: 600; color: #495057; }
        .slots-calendar-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
        .slots-calendar-day { background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px; min-height: 100px; cursor: pointer; transition: all 0.2s; }
        .slots-calendar-day:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .slots-calendar-day.slots-past { background: #f8f9fa; opacity: 0.7; }
        .slots-calendar-day.slots-today { border: 2px solid #4361ee; background: #f0f4ff; }
        .slots-calendar-day.slots-non-working { background: #f8f9fa; border-color: #dee2e6; }
        .slots-calendar-day.slots-holiday { background: #fee2e2; border-color: #fecaca; }
        .slots-calendar-day.slots-half-day { background: #fff3e0; border-color: #ffe0b2; }
        .slots-day-number { font-weight: 600; font-size: 14px; margin-bottom: 8px; }
        .slots-service-badges { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; }
        .slots-service-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .slots-service-badge.slots-available { background: #d4edda; color: #155724; }
        .slots-service-badge.slots-limited { background: #fff3cd; color: #856404; }
        .slots-service-badge.slots-full { background: #f8d7da; color: #721c24; }
        .slots-badge-icon { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; margin-top: 5px; }
        .slots-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .slots-modal.show { display: flex; }
        .slots-modal-dialog { max-width: 600px; width: 90%; }
        .slots-modal-dialog.slots-modal-lg { max-width: 900px; }
        .slots-modal-dialog.slots-modal-xl { max-width: 1100px; }
        .slots-modal-content { background: white; border-radius: 12px; overflow: hidden; }
        .slots-modal-header { padding: 15px 20px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .slots-modal-header-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .slots-modal-header-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
        .slots-modal-title { margin: 0; font-size: 18px; }
        .slots-modal-close { font-size: 24px; cursor: pointer; opacity: 0.7; }
        .slots-modal-close:hover { opacity: 1; }
        .slots-modal-body { padding: 20px; max-height: 70vh; overflow-y: auto; }
        .slots-modal-footer { padding: 15px 20px; border-top: 1px solid #e9ecef; display: flex; justify-content: flex-end; gap: 10px; }
        .slots-form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px; }
        .slots-form-group { margin-bottom: 15px; }
        .slots-form-label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        .slots-form-control { width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        .slots-form-control:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
        .slots-table { width: 100%; border-collapse: collapse; }
        .slots-table th, .slots-table td { padding: 12px; border: 1px solid #e9ecef; text-align: left; }
        .slots-table th { background: #f8f9fa; font-weight: 600; }
        .slots-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .slots-alert-info { background: #e7f3ff; color: #004085; border: 1px solid #b8daff; }
        .slots-checkbox-group { display: flex; flex-wrap: wrap; gap: 15px; }
        .slots-checkbox { display: flex; align-items: center; gap: 5px; cursor: pointer; }
        .slots-day-type-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .slots-day-type-badge.working { background: #d4edda; color: #155724; }
        .slots-day-type-badge.half_day { background: #fff3cd; color: #856404; }
        .slots-day-type-badge.holiday { background: #f8d7da; color: #721c24; }
        .slots-day-type-badge.special { background: #d1ecf1; color: #0c5460; }
        .slots-loading-state { text-align: center; padding: 40px; color: #6c757d; }
        .slots-text-warning { color: #ffc107; }
        .slots-text-danger { color: #dc3545; }
        .slots-text-success { color: #28a745; }
        .warning-bg { background: #fff3cd; color: #856404; }
        .danger-bg { background: #f8d7da; color: #dc3545; }
        .success-bg { background: #d4edda; color: #28a745; }
    </style>
@endsection