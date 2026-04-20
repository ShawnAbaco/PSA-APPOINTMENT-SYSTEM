@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Slot Management</h1>
            <p class="text-muted mb-0">Manage daily appointment capacity, holidays, and special dates</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkGenerateModal">
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
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1 small">Half Days</p>
                    <h3 class="mb-0 fw-bold text-warning">{{ $totalHalfDays ?? 0 }}</h3>
                </div>
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="fas fa-sun text-warning fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Holidays</p>
                            <h3 class="mb-0 fw-bold text-danger">{{ $totalHolidays }}</h3>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                            <i class="fas fa-calendar-times text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Special Days</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $totalSpecialDays }}</h3>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="fas fa-star text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Booked</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $totalBooked }}</h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-users text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2 align-items-center">
                    <button id="prevMonth" class="btn btn-outline-secondary rounded-circle" style="width: 40px; height: 40px;">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h3 id="calendarMonthYear" class="mb-0 fw-bold" style="min-width: 200px; text-align: center;"></h3>
                    <button id="nextMonth" class="btn btn-outline-secondary rounded-circle" style="width: 40px; height: 40px;">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <button id="todayBtn" class="btn btn-outline-primary rounded-pill">
                    <i class="fas fa-calendar-day me-2"></i>Today
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="calendar-grid">
                <div class="calendar-weekdays">
                    <div class="weekday">Mon</div>
                    <div class="weekday">Tue</div>
                    <div class="weekday">Wed</div>
                    <div class="weekday">Thu</div>
                    <div class="weekday">Fri</div>
                    <div class="weekday">Sat</div>
                    <div class="weekday">Sun</div>
                </div>
                <div class="calendar-days" id="calendarDays">
                    <div class="loading-spinner text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading calendar...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Generate Modal -->
<div class="modal fade" id="bulkGenerateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Bulk Generate Slots</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.slots.bulk-generate') }}" id="bulkGenerateForm">
                @csrf
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" name="start_date" id="startDate" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date" name="end_date" id="endDate" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Total Capacity</label>
                        <input type="number" name="total_capacity" class="form-control rounded-3" value="20" min="1" max="100" required>
                        <small class="text-muted">Default capacity for all generated slots</small>
                    </div>
                    
                    <!-- Day Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Days to Include</label>
                        <div class="row g-2">
                            <div class="col-4 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="days[]" value="1" id="dayMon" class="form-check-input">
                                    <label class="form-check-label" for="dayMon">Monday</label>
                                </div>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="days[]" value="2" id="dayTue" class="form-check-input">
                                    <label class="form-check-label" for="dayTue">Tuesday</label>
                                </div>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="days[]" value="3" id="dayWed" class="form-check-input">
                                    <label class="form-check-label" for="dayWed">Wednesday</label>
                                </div>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="days[]" value="4" id="dayThu" class="form-check-input">
                                    <label class="form-check-label" for="dayThu">Thursday</label>
                                </div>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="days[]" value="5" id="dayFri" class="form-check-input">
                                    <label class="form-check-label" for="dayFri">Friday</label>
                                </div>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="days[]" value="6" id="daySat" class="form-check-input">
                                    <label class="form-check-label" for="daySat">Saturday</label>
                                </div>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="days[]" value="7" id="daySun" class="form-check-input">
                                    <label class="form-check-label" for="daySun">Sunday</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" id="selectAllDays" class="btn btn-sm btn-link p-0 me-2">Select All</button>
                            <button type="button" id="selectWeekdays" class="btn btn-sm btn-link p-0 me-2">Weekdays (Mon-Fri)</button>
                            <button type="button" id="selectWeekends" class="btn btn-sm btn-link p-0">Weekends (Sat-Sun)</button>
                        </div>
                        <small class="text-muted">Leave unchecked to include all days</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Generate Slots</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.calendar-grid {
    background: white;
    border-radius: 16px;
}
.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}
.weekday {
    text-align: center;
    padding: 12px;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}
.calendar-day {
    min-height: 120px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 10px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid transparent;
}
.calendar-day:not(.past):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: #fff;
    border-color: #e0e0e0;
}
.calendar-day.empty {
    background: transparent;
    cursor: default;
}
.calendar-day.empty:hover {
    transform: none;
    box-shadow: none;
}
.calendar-day.past {
    opacity: 0.5;
    cursor: not-allowed;
    background: #e9ecef;
}
.calendar-day.past:hover {
    transform: none;
    box-shadow: none;
}
.calendar-day.today {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.calendar-day.today .day-number {
    background: rgba(255,255,255,0.3);
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
.calendar-day.full {
    background: #e0e7ff;
}
.day-number {
    font-size: 1rem;
    font-weight: 600;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin-bottom: 8px;
}
.calendar-day:not(.today):not(.past) .day-number {
    background: white;
    color: #2c3e50;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.slot-info {
    font-size: 0.7rem;
    margin-top: 8px;
}
.slot-capacity {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 500;
}
.slot-capacity.available {
    background: #d1fae5;
    color: #065f46;
}
.slot-capacity.full {
    background: #fee2e2;
    color: #991b1b;
}
.slot-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 0.65rem;
    margin-top: 4px;
}
.slot-badge.holiday {
    background: #dc2626;
    color: white;
}
.slot-badge.half-day {
    background: #f59e0b;
    color: white;
}
.slot-badge.special {
    background: #5e35b1;
    color: white;
}
.slot-stats {
    font-size: 0.65rem;
    color: #6c757d;
}
.progress {
    background-color: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}
.progress-bar {
    transition: width 0.3s ease;
}
.loading-spinner {
    grid-column: 1 / -1;
    text-align: center;
    padding: 40px;
}
a.text-decoration-none {
    display: block;
    color: inherit;
}
a.text-decoration-none:hover {
    color: inherit;
}
.calendar-day.past a {
    cursor: not-allowed;
    pointer-events: none;
}
@media (max-width: 768px) {
    .calendar-day { min-height: 90px; padding: 6px; }
    .day-number { width: 24px; height: 24px; font-size: 0.75rem; }
    .slot-info { font-size: 0.6rem; }
    .slot-capacity { font-size: 0.6rem; padding: 1px 4px; }
    .weekday { font-size: 0.7rem; padding: 8px; }
}
</style>

@push('scripts')
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
        document.getElementById('calendarDays').innerHTML = '<div class="loading-spinner text-danger">Failed to load slots. Please refresh the page.</div>';
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
    
    let html = '';
    for (let i = 0; i < startOffset; i++) html += '<div class="calendar-day empty"></div>';
    
    for (let d = 1; d <= daysInMonth; d++) {
        const date = new Date(year, month, d);
        const dateKey = formatDate(date);
        const slot = slotsData[dateKey];
        const isPast = date < today;
        const isToday = formatDate(date) === formatDate(new Date());
        
        let capacityHtml = '', badgeHtml = '', statusClass = '';
        
        if (slot) {
            switch(slot.day_type) {
                case 'holiday':
                    statusClass = 'holiday';
                    badgeHtml = '<div class="slot-badge holiday"><i class="fas fa-gift me-1"></i>Holiday</div>';
                    capacityHtml = '<div class="slot-capacity full">No appointments</div>';
                    if (slot.notes) capacityHtml += `<div class="small text-muted mt-1">📝 ${slot.notes.substring(0, 30)}</div>`;
                    break;
                case 'half_day':
                    statusClass = 'half-day';
                    badgeHtml = '<div class="slot-badge half-day"><i class="fas fa-sun me-1"></i>Half Day</div>';
                    capacityHtml = `<div class="slot-capacity ${slot.available_count > 0 ? 'available' : 'full'}">
                        ${slot.available_count > 0 ? `✅ ${slot.available_count} slots left` : '❌ Full'}
                    </div>
                    <div class="slot-stats mt-1">📊 ${slot.booked_count}/${slot.total_capacity} booked (50% capacity)</div>`;
                    break;
                case 'special':
                    statusClass = 'special';
                    badgeHtml = '<div class="slot-badge special"><i class="fas fa-star me-1"></i>Special Day</div>';
                    capacityHtml = `<div class="slot-capacity ${slot.available_count > 0 ? 'available' : 'full'}">
                        ${slot.available_count > 0 ? `✅ ${slot.available_count} slots left` : '❌ Full'}
                    </div>
                    <div class="slot-stats mt-1">📊 ${slot.booked_count}/${slot.total_capacity} booked</div>`;
                    if (slot.notes) capacityHtml += `<div class="small text-muted mt-1">📝 ${slot.notes.substring(0, 30)}</div>`;
                    break;
                default: // working
                    const percentage = (slot.booked_count / slot.total_capacity) * 100;
                    capacityHtml = `<div class="slot-capacity ${slot.available_count > 0 ? 'available' : 'full'}">
                        ${slot.available_count > 0 ? `✅ ${slot.available_count} slots left` : '❌ Full'}
                    </div>
                    <div class="progress mt-1" style="height: 4px;">
                        <div class="progress-bar bg-${percentage >= 80 ? 'danger' : (percentage >= 50 ? 'warning' : 'success')}" style="width: ${percentage}%;"></div>
                    </div>`;
                    if (slot.available_count === 0) statusClass = 'full';
            }
        } else if (!isPast) {
            capacityHtml = '<div class="slot-capacity" style="background:#e5e7eb;color:#6b7280;">Not configured</div>';
            capacityHtml += '<div class="small text-muted mt-1">Click to configure</div>';
        }
        
        let dayClass = `calendar-day ${isPast ? 'past' : ''} ${isToday ? 'today' : ''} ${statusClass}`;
        const editUrl = (!isPast && slot?.id) ? `/admin/slots/${slot.id}/edit` : (!isPast ? `/admin/slots/create?date=${dateKey}` : '#');
        
        if (isPast) {
            html += `<div class="${dayClass}"><div class="day-number">${d}</div><div class="slot-info">${capacityHtml}${badgeHtml}</div></div>`;
        } else {
            html += `<a href="${editUrl}" class="text-decoration-none" style="display:block;color:inherit;"><div class="${dayClass}"><div class="day-number">${d}</div><div class="slot-info">${capacityHtml}${badgeHtml}</div></div></a>`;
        }
    }
    document.getElementById('calendarDays').innerHTML = html;
}

// Day selection helpers
document.getElementById('selectAllDays')?.addEventListener('click', () => document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => cb.checked = true));
document.getElementById('selectWeekdays')?.addEventListener('click', () => document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => cb.checked = [1,2,3,4,5].includes(parseInt(cb.value))));
document.getElementById('selectWeekends')?.addEventListener('click', () => document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => cb.checked = [6,7].includes(parseInt(cb.value))));

// Validate bulk generate dates
document.getElementById('bulkGenerateForm')?.addEventListener('submit', function(e) {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    const today = new Date();
    today.setHours(0,0,0,0);
    if (startDate < today) { e.preventDefault(); alert('Start date cannot be in the past.'); return false; }
    if (endDate < startDate) { e.preventDefault(); alert('End date must be after start date.'); return false; }
});

// Calendar navigation
document.getElementById('prevMonth')?.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); loadSlots(); });
document.getElementById('nextMonth')?.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); loadSlots(); });
document.getElementById('todayBtn')?.addEventListener('click', () => { currentDate = new Date(); loadSlots(); });

loadSlots();
</script>
@endpush
@endsection