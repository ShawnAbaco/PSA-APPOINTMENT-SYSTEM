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
            <button type="button" class="slots-btn slots-btn-outline-primary" data-bs-toggle="modal"
                data-bs-target="#slotsCapacityRulesModal">
                <i class="fas fa-sliders-h"></i> Default Capacity Rules
            </button>
            <button type="button" class="slots-btn slots-btn-outline-success" data-bs-toggle="modal"
                data-bs-target="#slotsBulkGenerateModal">
                <i class="fas fa-calendar-plus"></i> Bulk Generate
            </button>
            <button type="button" class="slots-btn slots-btn-primary" id="slotsAddSingleSlotBtn">
                <i class="fas fa-plus-circle"></i> Add Single Slot
            </button>
        </div>

        <!-- Create Single Slot Modal -->
        <div class="slots-modal" id="slotsCreateSlotModal" tabindex="-1">
            <div class="slots-modal-dialog slots-modal-lg">
                <div class="slots-modal-content">
                    <div class="slots-modal-header slots-modal-header-primary">
                        <h5 class="slots-modal-title"><i class="fas fa-plus-circle"></i> Create Appointment Slot</h5>
                        <span class="slots-modal-close" data-bs-dismiss="modal">&times;</span>
                    </div>
                    <div class="slots-modal-body" id="slotsCreateSlotBody">
                        <div class="slots-loading-state">
                            <i class="fas fa-spinner fa-pulse"></i>
                            <p>Loading form...</p>
                        </div>
                    </div>
                </div>
            </div>
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
                    <div class="slots-dropdown">
                        <button class="slots-btn slots-btn-sm slots-btn-outline-secondary slots-dropdown-toggle"
                            type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter"></i> Legend
                        </button>
                        <div class="slots-dropdown-menu">
                            <div class="slots-legend-item"><span class="slots-legend-color available"></span>Available Slots
                            </div>
                            <div class="slots-legend-item"><span class="slots-legend-color partial"></span>Limited Slots
                                (&lt;50%)</div>
                            <div class="slots-legend-item"><span class="slots-legend-color full"></span>Fully Booked</div>
                            <div class="slots-legend-item"><span class="slots-legend-color holiday"></span>Holiday/Closed
                            </div>
                            <div class="slots-legend-item"><span class="slots-legend-color half-day"></span>Half Day</div>
                        </div>
                    </div>
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

    <!-- Capacity Rules Modal -->
    <div class="slots-modal" id="slotsCapacityRulesModal" tabindex="-1">
        <div class="slots-modal-dialog slots-modal-xl">
            <div class="slots-modal-content">
                <div class="slots-modal-header">
                    <h5 class="slots-modal-title"><i class="fas fa-sliders-h"></i> Default Capacity Rules by Day Type</h5>
                    <span class="slots-modal-close" data-bs-dismiss="modal">&times;</span>
                </div>
                <div class="slots-modal-body">
                    <div class="slots-alert slots-alert-info">
                        <i class="fas fa-info-circle"></i>
                        These are the default capacity rules for each day type. When you create a new slot without an
                        override, these values will be used.
                        Changes here will NOT affect existing slots that have manual overrides.
                    </div>

                    <form id="slotsCapacityRulesForm">
                        @csrf
                        <div class="slots-table-responsive">
                            <table class="slots-table">
                                <thead>
                                    <tr>
                                        <th>Time Slot</th>
                                        <th>Day Type</th>
                                        <th>Registration (R)</th>
                                        <th>Updating (U)</th>
                                        <th>Inquiry (S)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($timeSlots as $timeSlot)
                                        @php
                                            $dayTypes = ['weekday', 'saturday', 'sunday', 'holiday'];
                                            $dayTypeNames = [
                                                'weekday' => 'Weekday',
                                                'saturday' => 'Saturday',
                                                'sunday' => 'Sunday',
                                                'holiday' => 'Holiday',
                                            ];
                                        @endphp
                                        @foreach ($dayTypes as $dayType)
                                            @php $rule = $capacityRules[$timeSlot->id][$dayType] ?? null; @endphp
                                            <tr>
                                                @if ($loop->first)
                                                    <td rowspan="4" class="slots-time-slot-cell">
                                                        <strong>{{ $timeSlot->label }}</strong><br>
                                                        <small>{{ date('g:i A', strtotime($timeSlot->start_time)) }}
                                                            - {{ date('g:i A', strtotime($timeSlot->end_time)) }}</small>
                                                    </td>
                                                @endif
                                                <td>
                                                    <span class="slots-day-type-badge {{ $dayType }}">
                                                        {{ $dayTypeNames[$dayType] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][reg]"
                                                        class="slots-form-control slots-form-control-sm"
                                                        value="{{ $rule->reg_capacity ?? ($dayType == 'weekday' ? 10 : ($dayType == 'saturday' ? 5 : 0)) }}"
                                                        min="0" max="100">
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][updating]"
                                                        class="slots-form-control slots-form-control-sm"
                                                        value="{{ $rule->updating_capacity ?? ($dayType == 'weekday' ? 5 : ($dayType == 'saturday' ? 3 : 0)) }}"
                                                        min="0" max="100">
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][inquiry]"
                                                        class="slots-form-control slots-form-control-sm"
                                                        value="{{ $rule->inquiry_capacity ?? ($dayType == 'weekday' ? 8 : ($dayType == 'saturday' ? 4 : 0)) }}"
                                                        min="0" max="100">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="slots-modal-footer">
                            <button type="button" class="slots-btn slots-btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="slots-btn slots-btn-primary">Save Default Rules</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Generate Modal -->
    <div class="slots-modal" id="slotsBulkGenerateModal" tabindex="-1">
        <div class="slots-modal-dialog slots-modal-lg">
            <div class="slots-modal-content">
                <form method="POST" action="{{ route('admin.slots.bulk-generate') }}" id="slotsBulkGenerateForm">
                    @csrf
                    <div class="slots-modal-header slots-modal-header-success">
                        <h5 class="slots-modal-title"><i class="fas fa-calendar-plus"></i> Bulk Generate Slots</h5>
                        <span class="slots-modal-close" data-bs-dismiss="modal">&times;</span>
                    </div>
                    <div class="slots-modal-body">
                        <div class="slots-alert slots-alert-info">
                            <i class="fas fa-info-circle"></i>
                            Generate appointment slots for multiple dates at once. Each generated slot will have its own
                            capacity override.
                        </div>

                        <div class="slots-form-row">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Start Date *</label>
                                <input type="date" name="start_date" id="slotsStartDate" class="slots-form-control"
                                    required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">End Date *</label>
                                <input type="date" name="end_date" id="slotsEndDate" class="slots-form-control"
                                    required>
                            </div>
                        </div>

                        <div class="slots-form-group">
                            <label class="slots-form-label">Time Slot *</label>
                            <select name="time_slot_id" class="slots-form-control" required>
                                <option value="">Select Time Slot</option>
                                @foreach ($timeSlots ?? [] as $timeSlot)
                                    <option value="{{ $timeSlot->id }}">{{ $timeSlot->label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="slots-form-row">
                            <div class="slots-form-group">
                                <label class="slots-form-label">Registration (R) Capacity</label>
                                <input type="number" name="reg_capacity" class="slots-form-control" value="10"
                                    min="0" max="100" required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Updating (U) Capacity</label>
                                <input type="number" name="updating_capacity" class="slots-form-control" value="5"
                                    min="0" max="100" required>
                            </div>
                            <div class="slots-form-group">
                                <label class="slots-form-label">Inquiry (S) Capacity</label>
                                <input type="number" name="inquiry_capacity" class="slots-form-control" value="8"
                                    min="0" max="100" required>
                            </div>
                        </div>

                        <div class="slots-form-group">
                            <label class="slots-form-label">Days to Include</label>
                            <div class="slots-checkbox-group">
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="1"
                                        checked> Monday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="2"
                                        checked> Tuesday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="3"
                                        checked> Wednesday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="4"
                                        checked> Thursday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="5"
                                        checked> Friday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="6">
                                    Saturday</label>
                                <label class="slots-checkbox"><input type="checkbox" name="days[]" value="7">
                                    Sunday</label>
                            </div>
                        </div>

                        <div class="slots-button-group">
                            <button type="button" id="slotsSelectAllDays"
                                class="slots-btn slots-btn-sm slots-btn-outline-secondary">Select All</button>
                            <button type="button" id="slotsSelectWeekdays"
                                class="slots-btn slots-btn-sm slots-btn-outline-secondary">Weekdays</button>
                            <button type="button" id="slotsSelectWeekends"
                                class="slots-btn slots-btn-sm slots-btn-outline-secondary">Weekends</button>
                        </div>
                    </div>
                    <div class="slots-modal-footer">
                        <button type="button" class="slots-btn slots-btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="slots-btn slots-btn-success">Generate Slots</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Slot Detail Modal -->
    <div class="slots-modal" id="slotsSlotDetailModal" tabindex="-1">
        <div class="slots-modal-dialog slots-modal-lg">
            <div class="slots-modal-content">
                <div class="slots-modal-header">
                    <h5 class="slots-modal-title"><i class="fas fa-info-circle"></i> Slot Details</h5>
                    <span class="slots-modal-close" data-bs-dismiss="modal">&times;</span>
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
        // Load Create Slot Form in Modal
        document.getElementById('slotsAddSingleSlotBtn')?.addEventListener('click', async function() {
            const modal = document.getElementById('slotsCreateSlotModal');
            const modalBody = document.getElementById('slotsCreateSlotBody');

            // Show modal
            modal.classList.add('show');
            modalBody.innerHTML =
                '<div class="slots-loading-state"><i class="fas fa-spinner fa-pulse"></i><p>Loading form...</p></div>';

            try {
                // Fetch the create form HTML
                const response = await fetch('{{ route('admin.slots.create') }}');
                const html = await response.text();

                // Extract just the form content (the card body content)
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Get the form and its content
                const formContent = doc.querySelector('.card-body');

                if (formContent) {
                    modalBody.innerHTML = formContent.innerHTML;

                    // Re-initialize any JavaScript for the loaded form
                    initializeCreateFormScripts();
                } else {
                    modalBody.innerHTML =
                        '<div class="slots-alert slots-alert-danger">Error loading form. Please try again.</div>';
                }
            } catch (error) {
                console.error('Error loading create form:', error);
                modalBody.innerHTML =
                    '<div class="slots-alert slots-alert-danger">Error loading form. Please refresh the page and try again.</div>';
            }
        });

        // Function to initialize scripts for the dynamically loaded create form
        function initializeCreateFormScripts() {
            // Get form elements
            const dateInput = document.getElementById('date');
            const dayTypeSelect = document.getElementById('day_type');
            const timeSlotSelect = document.getElementById('time_slot_id');
            const capacityInputs = document.querySelectorAll('.capacity-input');
            const regCapacity = document.getElementById('reg_capacity');
            const updatingCapacity = document.getElementById('updating_capacity');
            const inquiryCapacity = document.getElementById('inquiry_capacity');
            const createForm = document.querySelector('#slotsCreateSlotBody form');

            // Working days from meta tag or data attribute
            let workingDays = [1, 2, 3, 4, 5];
            const workingDaysMeta = document.querySelector('meta[name="slots-working-days"]');
            if (workingDaysMeta) {
                workingDays = workingDaysMeta.getAttribute('content').split(',').map(Number);
            }

            // Default capacities
            const defaultCapacities = {
                reg: 10,
                updating: 5,
                inquiry: 8
            };

            // Function to update capacity fields based on day type
            function updateCapacityFields() {
                if (!dayTypeSelect) return;
                const dayType = dayTypeSelect.value;

                if (dayType === 'holiday') {
                    capacityInputs.forEach(input => {
                        input.disabled = true;
                        input.value = 0;
                    });
                } else if (dayType === 'half_day') {
                    capacityInputs.forEach(input => {
                        input.disabled = false;
                        input.style.backgroundColor = '#fff3e0';
                    });
                } else {
                    capacityInputs.forEach(input => {
                        input.disabled = false;
                        input.style.backgroundColor = '';
                        if (!input.value || input.value == 0) {
                            if (input.id === 'reg_capacity') input.value = defaultCapacities.reg;
                            if (input.id === 'updating_capacity') input.value = defaultCapacities.updating;
                            if (input.id === 'inquiry_capacity') input.value = defaultCapacities.inquiry;
                        }
                    });
                }
            }

            // Function to check if selected date is a working day
            function checkWorkingDay() {
                if (!dateInput || !dateInput.value) return true;

                const selectedDate = new Date(dateInput.value);
                let dayOfWeek = selectedDate.getDay();
                let dayNumber = dayOfWeek === 0 ? 7 : dayOfWeek;

                const isWorking = workingDays.includes(dayNumber);

                if (!isWorking) {
                    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const warningMessage =
                        `⚠️ Warning: ${dayNames[dayOfWeek]} is currently set as a NON-WORKING DAY.\n\nWould you like to continue creating this slot?`;

                    if (!confirm(warningMessage)) {
                        dateInput.value = '';
                        return false;
                    }
                }
                return true;
            }

            // Function to check if time slot already exists
            async function checkExistingTimeSlot() {
                if (!dateInput || !dateInput.value || !timeSlotSelect || !timeSlotSelect.value) return true;

                const date = dateInput.value;
                const timeSlotId = timeSlotSelect.value;

                try {
                    const response = await fetch(`/admin/slots/check-existing?date=${date}&time_slot_id=${timeSlotId}`);
                    const data = await response.json();

                    if (data.exists) {
                        alert(
                            `⚠️ A slot already exists for ${date} at ${data.time_slot_label}. Please select a different time slot or date.`
                            );
                        timeSlotSelect.value = '';
                        return false;
                    }
                } catch (error) {
                    console.error('Error checking existing slot:', error);
                }
                return true;
            }

            // Add event listeners
            if (dayTypeSelect) {
                dayTypeSelect.addEventListener('change', function() {
                    if (this.value === 'half_day') {
                        alert(
                            'Note: Half day means only 50% of the configured capacity will be available for booking.'
                            );
                    }
                    updateCapacityFields();
                });
            }

            if (dateInput) {
                dateInput.addEventListener('change', function() {
                    checkWorkingDay();
                    checkExistingTimeSlot();
                });
            }

            if (timeSlotSelect) {
                timeSlotSelect.addEventListener('change', function() {
                    checkExistingTimeSlot();
                });
            }

            // Handle form submission via AJAX
            if (createForm) {
                createForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    // Validate
                    if (!dateInput || !dateInput.value) {
                        alert('Please select a date.');
                        return false;
                    }

                    if (!timeSlotSelect || !timeSlotSelect.value) {
                        alert('Please select a time slot.');
                        return false;
                    }

                    // Check if time slot exists
                    const exists = await checkExistingTimeSlot();
                    if (!exists) return false;

                    // Validate capacities
                    const dayType = dayTypeSelect ? dayTypeSelect.value : 'working';
                    if (dayType !== 'holiday') {
                        const reg = parseInt(regCapacity?.value) || 0;
                        const updating = parseInt(updatingCapacity?.value) || 0;
                        const inquiry = parseInt(inquiryCapacity?.value) || 0;

                        if (reg === 0 && updating === 0 && inquiry === 0) {
                            const confirmSubmit = confirm(
                                'All capacities are set to 0. No appointments can be booked for this slot. Continue?'
                            );
                            if (!confirmSubmit) return false;
                        }
                    }

                    // Submit via AJAX
                    const formData = new FormData(createForm);
                    formData.append('_token', document.querySelector('meta[name="slots-csrf-token"]').content);

                    try {
                        const response = await fetch(createForm.action, {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert('Slot created successfully!');
                            // Close modal
                            document.getElementById('slotsCreateSlotModal').classList.remove('show');
                            // Refresh calendar
                            slotsLoadSlots();
                        } else {
                            alert(data.message || 'Error creating slot. Please try again.');
                        }
                    } catch (error) {
                        console.error('Error submitting form:', error);
                        alert('Error creating slot. Please try again.');
                    }
                });
            }

            // Initialize
            updateCapacityFields();
        }

        // Make sure modal close functionality works with the new modal
        document.querySelectorAll('.slots-modal-close, [data-bs-dismiss="modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.slots-modal.show').forEach(modal => {
                    modal.classList.remove('show');
                });
            });
        });

        let slotsCurrentDate = new Date();
        let slotsData = {};

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
                const response = await fetch(`/admin/slots?month=${month}&year=${year}&ajax=1`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                slotsData = data.slots || {};
                slotsRenderCalendar();
            } catch (error) {
                console.error('Error loading slots:', error);
                document.getElementById('slotsCalendarDays').innerHTML =
                    '<div class="slots-loading-state"><i class="fas fa-exclamation-triangle"></i><p>Failed to load slots. Please refresh the page.</p></div>';
            }
        }

        function slotsRenderCalendar() {
            const year = slotsCurrentDate.getFullYear();
            const month = slotsCurrentDate.getMonth();
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
                'October', 'November', 'December'
            ];
            document.getElementById('slotsCalendarMonthYear').textContent = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1);
            const startOffset = (firstDay.getDay() || 7) - 1;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let workingDays = [1, 2, 3, 4, 5];
            const workingDaysMeta = document.querySelector('meta[name="slots-working-days"]');
            if (workingDaysMeta) {
                workingDays = workingDaysMeta.getAttribute('content').split(',').map(Number);
            }

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
                if (dayOfWeek === 0) dayOfWeek = 7;
                const isWorkingDay = workingDays.includes(dayOfWeek);

                let dayClass = 'slots-calendar-day';
                if (isPast) dayClass += ' slots-past';
                if (isToday) dayClass += ' slots-today';
                if (!isWorkingDay) dayClass += ' slots-non-working';

                let content = `<div class="slots-day-number">${d}</div>`;

                if (!isWorkingDay) {
                    content +=
                        '<div class="slots-badge-icon" style="background:#6c757d; color:white;">🔒 Non-working</div>';
                } else if (slot && Object.keys(slot).length > 0) {
                    const firstSlot = Object.values(slot)[0];

                    if (firstSlot.day_type === 'holiday') {
                        dayClass += ' slots-holiday';
                        content =
                            `<div class="slots-day-number">${d}</div><div class="slots-badge-icon" style="background:#dc3545; color:white;">🎄 Holiday</div>`;
                    } else if (firstSlot.day_type === 'half_day') {
                        dayClass += ' slots-half-day';
                        content =
                            `<div class="slots-day-number">${d}</div><div class="slots-badge-icon" style="background:#ffc107;">🌙 Half Day</div>`;
                    }

                    let totalRegAvailable = 0,
                        totalUpdatingAvailable = 0,
                        totalInquiryAvailable = 0;
                    let totalRegCapacity = 0,
                        totalUpdatingCapacity = 0,
                        totalInquiryCapacity = 0;

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
                        let percent = (totalRegAvailable / totalRegCapacity) * 100;
                        let statusClass = totalRegAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' :
                            'slots-available');
                        badgesHtml += `<span class="slots-service-badge ${statusClass}">R${totalRegAvailable}</span>`;
                    }
                    if (totalUpdatingCapacity > 0) {
                        let percent = (totalUpdatingAvailable / totalUpdatingCapacity) * 100;
                        let statusClass = totalUpdatingAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' :
                            'slots-available');
                        badgesHtml += `<span class="slots-service-badge ${statusClass}">U${totalUpdatingAvailable}</span>`;
                    }
                    if (totalInquiryCapacity > 0) {
                        let percent = (totalInquiryAvailable / totalInquiryCapacity) * 100;
                        let statusClass = totalInquiryAvailable === 0 ? 'slots-full' : (percent < 30 ? 'slots-limited' :
                            'slots-available');
                        badgesHtml += `<span class="slots-service-badge ${statusClass}">S${totalInquiryAvailable}</span>`;
                    }
                    badgesHtml += '</div>';
                    content += badgesHtml;

                    if (firstSlot.notes) {
                        content += `<div class="slots-slot-notes">📝 ${firstSlot.notes.substring(0, 20)}</div>`;
                    }
                    if (firstSlot.is_overridden) {
                        content += `<div class="slots-overridden-badge">✏️ Override</div>`;
                    }
                } else if (isWorkingDay && !isPast) {
                    content += '<div class="slots-badge-icon" style="background:#6c757d; color:white;">⚙️ Not set</div>';
                }

                html += `<div class="${dayClass}" onclick="slotsShowSlotDetails('${dateKey}')">${content}</div>`;
            }

            document.getElementById('slotsCalendarDays').innerHTML = html;
        }

        async function slotsShowSlotDetails(dateKey) {
            const modal = document.getElementById('slotsSlotDetailModal');
            const modalBody = document.getElementById('slotsSlotDetailBody');
            modal.classList.add('show');
            modalBody.innerHTML =
                '<div class="slots-loading-state"><i class="fas fa-spinner fa-pulse"></i><p>Loading...</p></div>';

            try {
                const response = await fetch(`/admin/slots/details/${dateKey}`);
                const data = await response.json();

                if (data.success && data.slots.length > 0) {
                    let html =
                        '<div class="slots-table-responsive"><table class="slots-table"><thead><tr><th>Time Slot</th><th>Day Type</th><th>R</th><th>U</th><th>S</th><th>Booked</th><th>Available</th><th>Actions</th></tr></thead><tbody>';
                    for (const slot of data.slots) {
                        const total = (slot.reg_capacity || 0) + (slot.updating_capacity || 0) + (slot
                            .inquiry_capacity || 0);
                        const booked = (slot.reg_booked || 0) + (slot.updating_booked || 0) + (slot.inquiry_booked ||
                            0);
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
                    modalBody.innerHTML =
                        '<div class="slots-alert slots-alert-info">No slots configured for this date.</div>';
                }
            } catch (error) {
                modalBody.innerHTML =
                    '<div class="slots-alert" style="background:#fee2e2; color:#991b1b;">Error loading details.</div>';
            }
        }

        // Close modal when clicking outside or on close button
        document.querySelectorAll('.slots-modal-close, [data-bs-dismiss="modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.slots-modal.show').forEach(modal => {
                    modal.classList.remove('show');
                });
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('slots-modal')) {
                event.target.classList.remove('show');
            }
        });

        // Event Listeners
        document.getElementById('slotsPrevMonth')?.addEventListener('click', () => {
            slotsCurrentDate.setMonth(slotsCurrentDate.getMonth() - 1);
            slotsLoadSlots();
        });
        document.getElementById('slotsNextMonth')?.addEventListener('click', () => {
            slotsCurrentDate.setMonth(slotsCurrentDate.getMonth() + 1);
            slotsLoadSlots();
        });
        document.getElementById('slotsTodayBtn')?.addEventListener('click', () => {
            slotsCurrentDate = new Date();
            slotsLoadSlots();
        });
        document.getElementById('slotsSelectAllDays')?.addEventListener('click', () => {
            document.querySelectorAll('#slotsBulkGenerateForm input[name="days[]"]').forEach(cb => cb.checked =
                true);
        });
        document.getElementById('slotsSelectWeekdays')?.addEventListener('click', () => {
            document.querySelectorAll('#slotsBulkGenerateForm input[name="days[]"]').forEach(cb => {
                cb.checked = [1, 2, 3, 4, 5].includes(parseInt(cb.value));
            });
        });
        document.getElementById('slotsSelectWeekends')?.addEventListener('click', () => {
            document.querySelectorAll('#slotsBulkGenerateForm input[name="days[]"]').forEach(cb => {
                cb.checked = [6, 7].includes(parseInt(cb.value));
            });
        });

        document.getElementById('slotsCapacityRulesForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('_token', document.querySelector('meta[name="slots-csrf-token"]').content);
            const response = await fetch('/admin/slots/capacity-rules', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            alert(data.success ? 'Default rules saved successfully!' : 'Error saving rules');
            if (data.success) location.reload();
        });

        slotsLoadSlots();
    </script>
@endsection
