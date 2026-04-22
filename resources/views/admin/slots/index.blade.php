@extends('layouts.admin')


<style>
.calendar-day {
    min-height: 130px;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 8px;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    position: relative;
}
.calendar-day:hover:not(.past):not(.empty) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: #ffffff;
    border-color: #dee2e6;
}
.calendar-day.past {
    opacity: 0.5;
    background: #e9ecef;
    cursor: not-allowed;
}
.calendar-day.today {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}
.calendar-day.today .day-number {
    background: rgba(255,255,255,0.2);
    color: white;
}
.calendar-day.holiday {
    background: #fee2e2;
    border-color: #fecaca;
}
.calendar-day.half-day {
    background: #fff3e0;
    border-color: #ffe0b2;
}
.calendar-day.special {
    background: #e8eaf6;
    border-color: #c5cae9;
}
.calendar-day.non-working {
    background: #f5f5f5;
    opacity: 0.6;
    cursor: default;
}
.day-number {
    font-size: 1rem;
    font-weight: 700;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin-bottom: 8px;
    background: white;
    color: #2c3e50;
}
.service-badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
    margin: 8px 0;
}
.service-badge {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 20px;
    min-width: 45px;
    text-align: center;
}
.service-badge.available {
    background: #c8e6c9;
    color: #2e7d32;
}
.service-badge.limited {
    background: #ffe0b2;
    color: #e65100;
}
.service-badge.full {
    background: #ffcdd2;
    color: #c62828;
}
.badge-icon {
    font-size: 0.65rem;
    padding: 3px 8px;
    border-radius: 20px;
    text-align: center;
    margin-bottom: 6px;
    font-weight: 500;
    display: inline-block;
}
.badge-icon.holiday {
    background: #dc2626;
    color: white;
}
.badge-icon.half-day {
    background: #f59e0b;
    color: white;
}
.badge-icon.special {
    background: #5e35b1;
    color: white;
}
.progress {
    height: 4px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 6px;
}
.progress-bar {
    transition: width 0.3s ease;
}
.slot-notes {
    font-size: 0.6rem;
    color: #6c757d;
    margin-top: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.overridden-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 0.65rem;
    background: #ff9800;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
}
.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
    margin-bottom: 8px;
}
.calendar-weekdays div {
    text-align: center;
    padding: 8px 4px;
    font-weight: 600;
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}
@media (max-width: 768px) {
    .calendar-day {
        min-height: 100px;
        padding: 5px;
    }
    .day-number {
        width: 26px;
        height: 26px;
        font-size: 0.8rem;
    }
    .service-badge {
        font-size: 0.6rem;
        padding: 2px 5px;
        min-width: 35px;
    }
}
</style>

@section('content')
<meta name="working-days" content="{{ $workingDays ?? '1,2,3,4,5' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Slot Management</h1>
            <p class="text-muted mb-0">Manage daily appointment capacity, time slots, and special dates</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#capacityRulesModal">
                <i class="fas fa-sliders-h me-2"></i>Default Capacity Rules
            </button>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#bulkGenerateModal">
                <i class="fas fa-calendar-plus me-2"></i>Bulk Generate
            </button>
            <a href="{{ route('admin.slots.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add Single Slot
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Slots</p>
                            <h3 class="mb-0 fw-bold">{{ $totalSlots }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                            <i class="fas fa-calendar-week text-primary fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Half Days</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $totalHalfDays ?? 0 }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                            <i class="fas fa-sun text-warning fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Holidays</p>
                            <h3 class="mb-0 fw-bold text-danger">{{ $totalHolidays }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-2 rounded-circle">
                            <i class="fas fa-calendar-times text-danger fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Booked</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $totalBooked }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                            <i class="fas fa-users text-success fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Calendar -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 pt-3 px-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex gap-2 align-items-center">
                    <button id="prevMonth" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 34px; height: 34px;">
                        <i class="fas fa-chevron-left fa-sm"></i>
                    </button>
                    <h4 id="calendarMonthYear" class="mb-0 fw-semibold" style="min-width: 180px; text-align: center;"></h4>
                    <button id="nextMonth" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 34px; height: 34px;">
                        <i class="fas fa-chevron-right fa-sm"></i>
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button id="todayBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fas fa-calendar-day me-1"></i>Today
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i>Legend
                        </button>
                        <div class="dropdown-menu p-3" style="min-width: 200px;">
                            <div class="d-flex align-items-center mb-2"><span class="badge bg-success me-2">&nbsp;&nbsp;</span> Available Slots</div>
                            <div class="d-flex align-items-center mb-2"><span class="badge bg-warning me-2">&nbsp;&nbsp;</span> Limited Slots (&lt;50%)</div>
                            <div class="d-flex align-items-center mb-2"><span class="badge bg-danger me-2">&nbsp;&nbsp;</span> Fully Booked</div>
                            <div class="d-flex align-items-center mb-2"><span class="badge bg-secondary me-2">&nbsp;&nbsp;</span> Holiday/Closed</div>
                            <div class="d-flex align-items-center"><span class="badge bg-info me-2">&nbsp;&nbsp;</span> Half Day</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="calendar-wrapper">
                <div class="calendar-weekdays">
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                    <div>Sun</div>
                </div>
                <div class="calendar-days" id="calendarDays">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                        <p class="mt-2 text-muted small mb-0">Loading calendar...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Capacity Rules Modal -->
<div class="modal fade" id="capacityRulesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-sliders-h me-2"></i>Default Capacity Rules by Day Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    These are the default capacity rules for each day type. When you create a new slot without an override, these values will be used.
                    Changes here will NOT affect existing slots that have manual overrides.
                </div>
                
                <form id="capacityRulesForm">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr><th>Time Slot</th><th>Day Type</th><th>Registration (R)</th><th>Updating (U)</th><th>Inquiry (S)</th></tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $timeSlot)
                                    @php
                                        $dayTypes = ['weekday', 'saturday', 'sunday', 'holiday'];
                                        $dayTypeNames = ['weekday' => 'Weekday', 'saturday' => 'Saturday', 'sunday' => 'Sunday', 'holiday' => 'Holiday'];
                                    @endphp
                                    @foreach($dayTypes as $dayType)
                                        @php $rule = $capacityRules[$timeSlot->id][$dayType] ?? null; @endphp
                                        <tr>
                                            @if($loop->first)
                                                <td rowspan="4" style="vertical-align: middle; background:#f8f9fa;">
                                                    <strong>{{ $timeSlot->label }}</strong><br>
                                                    <small class="text-muted">{{ date('g:i A', strtotime($timeSlot->start_time)) }} - {{ date('g:i A', strtotime($timeSlot->end_time)) }}</small>
                                                </td>
                                            @endif
                                            <td><span class="badge {{ $dayType == 'weekday' ? 'bg-primary' : ($dayType == 'saturday' ? 'bg-warning' : 'bg-secondary') }}">{{ $dayTypeNames[$dayType] }}</span></td>
                                            <td><input type="number" name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][reg]" class="form-control form-control-sm" value="{{ $rule->reg_capacity ?? ($dayType == 'weekday' ? 10 : ($dayType == 'saturday' ? 5 : 0)) }}" min="0" max="100" style="width:80px"></td>
                                            <td><input type="number" name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][updating]" class="form-control form-control-sm" value="{{ $rule->updating_capacity ?? ($dayType == 'weekday' ? 5 : ($dayType == 'saturday' ? 3 : 0)) }}" min="0" max="100" style="width:80px"></td>
                                            <td><input type="number" name="capacities[{{ $timeSlot->id }}][{{ $dayType }}][inquiry]" class="form-control form-control-sm" value="{{ $rule->inquiry_capacity ?? ($dayType == 'weekday' ? 8 : ($dayType == 'saturday' ? 4 : 0)) }}" min="0" max="100" style="width:80px"></td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save Default Rules</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Generate Modal -->
<div class="modal fade" id="bulkGenerateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.slots.bulk-generate') }}" id="bulkGenerateForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Bulk Generate Slots</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Generate appointment slots for multiple dates at once. Each generated slot will have its own capacity override.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" id="startDate" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" id="endDate" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Time Slot *</label>
                        <select name="time_slot_id" class="form-control" required>
                            <option value="">Select Time Slot</option>
                            @foreach($timeSlots ?? [] as $timeSlot)
                                <option value="{{ $timeSlot->id }}">{{ $timeSlot->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Registration (R) Capacity</label>
                            <input type="number" name="reg_capacity" class="form-control" value="10" min="0" max="100" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Updating (U) Capacity</label>
                            <input type="number" name="updating_capacity" class="form-control" value="5" min="0" max="100" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Inquiry (S) Capacity</label>
                            <input type="number" name="inquiry_capacity" class="form-control" value="8" min="0" max="100" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Days to Include</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check"><input type="checkbox" name="days[]" value="1" id="dayMon" class="form-check-input" checked><label class="form-check-label" for="dayMon">Monday</label></div>
                            <div class="form-check"><input type="checkbox" name="days[]" value="2" id="dayTue" class="form-check-input" checked><label class="form-check-label" for="dayTue">Tuesday</label></div>
                            <div class="form-check"><input type="checkbox" name="days[]" value="3" id="dayWed" class="form-check-input" checked><label class="form-check-label" for="dayWed">Wednesday</label></div>
                            <div class="form-check"><input type="checkbox" name="days[]" value="4" id="dayThu" class="form-check-input" checked><label class="form-check-label" for="dayThu">Thursday</label></div>
                            <div class="form-check"><input type="checkbox" name="days[]" value="5" id="dayFri" class="form-check-input" checked><label class="form-check-label" for="dayFri">Friday</label></div>
                            <div class="form-check"><input type="checkbox" name="days[]" value="6" id="daySat" class="form-check-input"><label class="form-check-label" for="daySat">Saturday</label></div>
                            <div class="form-check"><input type="checkbox" name="days[]" value="7" id="daySun" class="form-check-input"><label class="form-check-label" for="daySun">Sunday</label></div>
                        </div>
                    </div>
                    
                    <div class="mt-2">
                        <button type="button" id="selectAllDays" class="btn btn-sm btn-outline-secondary me-2">Select All</button>
                        <button type="button" id="selectWeekdays" class="btn btn-sm btn-outline-secondary me-2">Weekdays</button>
                        <button type="button" id="selectWeekends" class="btn btn-sm btn-outline-secondary">Weekends</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Generate Slots</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Slot Detail Modal -->
<div class="modal fade" id="slotDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Slot Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="slotDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading slot details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
let currentDate = new Date();
let slotsData = {};

function formatDate(date) {
    let d = new Date(date);
    let month = String(d.getMonth() + 1).padStart(2, '0');
    let day = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${month}-${day}`;
}

async function loadSlots() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth() + 1;
    
    try {
        const response = await fetch(`/admin/slots?month=${month}&year=${year}&ajax=1`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        slotsData = data.slots || {};
        renderCalendar();
    } catch (error) {
        console.error('Error loading slots:', error);
        document.getElementById('calendarDays').innerHTML = '<div class="text-center text-danger small py-4">Failed to load slots. Please refresh the page.</div>';
    }
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('calendarMonthYear').textContent = `${monthNames[month]} ${year}`;
    
    const firstDay = new Date(year, month, 1);
    const startOffset = (firstDay.getDay() || 7) - 1;
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    let workingDays = [1, 2, 3, 4, 5];
    const workingDaysMeta = document.querySelector('meta[name="working-days"]');
    if (workingDaysMeta) {
        workingDays = workingDaysMeta.getAttribute('content').split(',').map(Number);
    }
    
    let html = '';
    
    for (let i = 0; i < startOffset; i++) {
        html += '<div class="calendar-day empty"></div>';
    }
    
    for (let d = 1; d <= daysInMonth; d++) {
        const date = new Date(year, month, d);
        const dateKey = formatDate(date);
        const slot = slotsData[dateKey];
        const isPast = date < today;
        const isToday = formatDate(date) === formatDate(new Date());
        
        let dayOfWeek = date.getDay();
        if (dayOfWeek === 0) dayOfWeek = 7;
        const isWorkingDay = workingDays.includes(dayOfWeek);
        
        let dayClass = 'calendar-day';
        if (isPast) dayClass += ' past';
        if (isToday) dayClass += ' today';
        if (!isWorkingDay) dayClass += ' non-working';
        
        let content = `<div class="day-number">${d}</div>`;
        
        if (!isWorkingDay) {
            content += '<div class="badge-icon" style="background:#6c757d;">🔒 Non-working</div>';
        } 
        else if (slot && Object.keys(slot).length > 0) {
            const firstSlot = Object.values(slot)[0];
            
            if (firstSlot.day_type === 'holiday') {
                dayClass += ' holiday';
                content = `<div class="day-number">${d}</div><div class="badge-icon holiday">🎄 Holiday</div>`;
            } else if (firstSlot.day_type === 'half_day') {
                dayClass += ' half-day';
                content = `<div class="day-number">${d}</div><div class="badge-icon half-day">🌙 Half Day</div>`;
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
            
            let badgesHtml = '<div class="service-badges">';
            if (totalRegCapacity > 0) {
                let percent = (totalRegAvailable / totalRegCapacity) * 100;
                let statusClass = totalRegAvailable === 0 ? 'full' : (percent < 30 ? 'limited' : 'available');
                badgesHtml += `<span class="service-badge ${statusClass}">R${totalRegAvailable}</span>`;
            }
            if (totalUpdatingCapacity > 0) {
                let percent = (totalUpdatingAvailable / totalUpdatingCapacity) * 100;
                let statusClass = totalUpdatingAvailable === 0 ? 'full' : (percent < 30 ? 'limited' : 'available');
                badgesHtml += `<span class="service-badge ${statusClass}">U${totalUpdatingAvailable}</span>`;
            }
            if (totalInquiryCapacity > 0) {
                let percent = (totalInquiryAvailable / totalInquiryCapacity) * 100;
                let statusClass = totalInquiryAvailable === 0 ? 'full' : (percent < 30 ? 'limited' : 'available');
                badgesHtml += `<span class="service-badge ${statusClass}">S${totalInquiryAvailable}</span>`;
            }
            badgesHtml += '</div>';
            content += badgesHtml;
            
            if (firstSlot.notes) {
                content += `<div class="slot-notes">📝 ${firstSlot.notes.substring(0, 20)}</div>`;
            }
            if (firstSlot.is_overridden) {
                content += `<div class="overridden-badge">✏️ Override</div>`;
            }
        } 
        else if (isWorkingDay && !isPast) {
            content += '<div class="badge-icon" style="background:#6c757d; color:white;">⚙️ Not set</div>';
        }
        
        html += `<div class="${dayClass}" onclick="showSlotDetails('${dateKey}')">${content}</div>`;
    }
    
    document.getElementById('calendarDays').innerHTML = html;
}

async function showSlotDetails(dateKey) {
    const modal = new bootstrap.Modal(document.getElementById('slotDetailModal'));
    const modalBody = document.getElementById('slotDetailBody');
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Loading...</p></div>';
    modal.show();
    
    try {
        const response = await fetch(`/admin/slots/details/${dateKey}`);
        const data = await response.json();
        
        if (data.success && data.slots.length > 0) {
            let html = '<div class="table-responsive"><table class="table table-bordered"><thead class="table-light"><tr><th>Time Slot</th><th>Day Type</th><th>R</th><th>U</th><th>S</th><th>Booked</th><th>Available</th><th>Actions</th></tr></thead><tbody>';
            for (const slot of data.slots) {
                const total = (slot.reg_capacity||0)+(slot.updating_capacity||0)+(slot.inquiry_capacity||0);
                const booked = (slot.reg_booked||0)+(slot.updating_booked||0)+(slot.inquiry_booked||0);
                html += `<tr>
                    <td><strong>${slot.time_slot_label}</strong></td>
                    <td><span class="badge bg-secondary">${slot.day_type}</span></td>
                    <td>${slot.reg_capacity||0}</td>
                    <td>${slot.updating_capacity||0}</td>
                    <td>${slot.inquiry_capacity||0}</td>
                    <td>${booked}</td>
                    <td>${total-booked}</td>
                    <td><a href="/admin/slots/${slot.id}/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td>
                </tr>`;
            }
            html += '</tbody></table></div>';
            modalBody.innerHTML = html;
        } else {
            modalBody.innerHTML = '<div class="alert alert-info">No slots configured for this date.</div>';
        }
    } catch (error) {
        modalBody.innerHTML = '<div class="alert alert-danger">Error loading details.</div>';
    }
}

// Event Listeners
document.getElementById('prevMonth')?.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); loadSlots(); });
document.getElementById('nextMonth')?.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); loadSlots(); });
document.getElementById('todayBtn')?.addEventListener('click', () => { currentDate = new Date(); loadSlots(); });
document.getElementById('selectAllDays')?.addEventListener('click', () => document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => cb.checked = true));
document.getElementById('selectWeekdays')?.addEventListener('click', () => document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => cb.checked = [1,2,3,4,5].includes(parseInt(cb.value))));
document.getElementById('selectWeekends')?.addEventListener('click', () => document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => cb.checked = [6,7].includes(parseInt(cb.value))));

document.getElementById('capacityRulesForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    const response = await fetch('/admin/slots/capacity-rules', { method: 'POST', body: formData });
    const data = await response.json();
    alert(data.success ? 'Saved!' : 'Error');
    if(data.success) location.reload();
});

loadSlots();
</script>