@extends('layouts.admin')

@section('content')
<meta name="working-days" content="{{ $workingDays ?? '1,2,3,4,5' }}">
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
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2 align-items-center">
                    <button id="prevMonth" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 34px; height: 34px;">
                        <i class="fas fa-chevron-left fa-sm"></i>
                    </button>
                    <h4 id="calendarMonthYear" class="mb-0 fw-semibold" style="min-width: 180px; text-align: center;"></h4>
                    <button id="nextMonth" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 34px; height: 34px;">
                        <i class="fas fa-chevron-right fa-sm"></i>
                    </button>
                </div>
                <button id="todayBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-calendar-day me-1"></i>Today
                </button>
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



<style>
    
.calendar-day.non-working {
    background: #f5f5f5;
    opacity: 0.6;
    cursor: default;
}
.calendar-day.non-working:hover {
    transform: none;
    box-shadow: none;
    background: #f5f5f5;
}
.non-working-label {
    font-size: 0.6rem;
    color: #999;
    text-align: center;
    margin-top: 20px;
}
.service-badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 4px;
    margin-top: 4px;
}
.service-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 2px 5px;
    border-radius: 4px;
    min-width: 32px;
    text-align: center;
}
.service-badge.available {
    background: #c8e6c9;
    color: #2e7d32;
}
.service-badge.full {
    background: #ffcdd2;
    color: #c62828;
}
.not-configured {
    font-size: 0.6rem;
    color: #6c757d;
    text-align: center;
    margin-top: 8px;
}
a.text-decoration-none {
    text-decoration: none !important;
}
.calendar-day .day-number {
    text-decoration: none;
    border-bottom: none !important;
}
.calendar-day a,
.calendar-day a:hover,
.calendar-day a:focus,
.calendar-day a:active {
    text-decoration: none !important;
    border-bottom: none !important;
}
.calendar-wrapper {
    background: white;
    border-radius: 12px;
    overflow: hidden;
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
.calendar-day {
    min-height: 110px;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 8px;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    cursor: pointer;
}
.calendar-day:not(.past):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: #ffffff;
    border-color: #dee2e6;
}
.calendar-day.empty {
    background: transparent;
    border-color: transparent;
    cursor: default;
    box-shadow: none;
}
.calendar-day.empty:hover {
    transform: none;
    background: transparent;
}
.calendar-day.past {
    opacity: 0.5;
    background: #e9ecef;
    cursor: not-allowed;
}
.calendar-day.past:hover {
    transform: none;
    box-shadow: none;
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
.day-number {
    font-size: 0.9rem;
    font-weight: 700;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin-bottom: 8px;
    background: white;
    color: #2c3e50;
}
.badge-icon {
    font-size: 0.6rem;
    padding: 3px 6px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 6px;
    font-weight: 500;
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
    height: 3px;
    background: #e5e7eb;
    border-radius: 3px;
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
    .calendar-day {
        min-height: 85px;
        padding: 5px;
    }
    .day-number {
        width: 22px;
        height: 22px;
        font-size: 0.75rem;
        margin-bottom: 4px;
    }
    .badge-icon {
        font-size: 0.55rem;
        padding: 2px 4px;
        margin-bottom: 4px;
    }
    .calendar-weekdays div {
        font-size: 0.65rem;
        padding: 5px;
    }
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
    
    const serviceShort = { 'reg': 'R', 'correction': 'C', 'ephilid': 'E', 'trn': 'T' };
    
    // Get working days from meta tag (should be: 1,2,3,4,5,6 for Mon-Sat, Sunday=7 is excluded)
    let workingDays = [1, 2, 3, 4, 5]; // Default: Mon-Fri
    const workingDaysMeta = document.querySelector('meta[name="working-days"]');
    if (workingDaysMeta) {
        workingDays = workingDaysMeta.getAttribute('content').split(',').map(Number);
        console.log('Working days:', workingDays); // Debug: should show [1,2,3,4,5,6] if Saturday is working
    }
    
    let html = '';
    
    // Empty cells for days before month starts
    for (let i = 0; i < startOffset; i++) {
        html += '<div class="calendar-day empty"></div>';
    }
    
    // Render each day of the month
    for (let d = 1; d <= daysInMonth; d++) {
        const date = new Date(year, month, d);
        const dateKey = formatDate(date);
        const slot = slotsData[dateKey];
        const isPast = date < today;
        const isToday = formatDate(date) === formatDate(new Date());
        
        // Convert JavaScript day (0=Sunday, 1=Monday) to your database format (1=Monday, 7=Sunday)
        let dayOfWeek = date.getDay(); // 0-6
        if (dayOfWeek === 0) {
            dayOfWeek = 7; // Sunday becomes 7
        }
        // Saturday is 6, stays as 6
        
        const isWorkingDay = workingDays.includes(dayOfWeek);
        
        let dayClass = 'calendar-day';
        if (isPast) dayClass += ' past';
        if (isToday) dayClass += ' today';
        
        // Special styling for non-working days (like Sunday when is_working=0)
        if (!isWorkingDay) {
            dayClass += ' non-working';
        }
        
        let content = '';
        
        // Handle non-working days (Sunday)
        if (!isWorkingDay) {
            content = '<div class="non-working-label">🔒 Non-working day</div>';
        } 
        // Handle days with slots configured
        else if (slot) {
            if (slot.day_type === 'holiday') {
                dayClass += ' holiday';
                content = '<div class="badge-icon holiday">🎄 Holiday</div>';
            } else if (slot.day_type === 'half_day') {
                dayClass += ' half-day';
                content = '<div class="badge-icon half-day">🌙 Half Day</div>';
            } else if (slot.day_type === 'special') {
                dayClass += ' special';
                content = '<div class="badge-icon special">⭐ Special</div>';
            }
            
            let badgesHtml = '<div class="service-badges">';
            const services = ['reg', 'correction', 'ephilid', 'trn'];
            for (const svc of services) {
                const available = slot[`${svc}_available`] || 0;
                const capacity = slot[`${svc}_capacity`] || 0;
                if (capacity > 0) {
                    const statusClass = available > 0 ? 'available' : 'full';
                    badgesHtml += `<span class="service-badge ${statusClass}">${serviceShort[svc]}${available}</span>`;
                }
            }
            badgesHtml += '</div>';
            content += badgesHtml;
            
            if (slot.day_type === 'working') {
                const totalCap = (slot.reg_capacity||0) + (slot.correction_capacity||0) + (slot.ephilid_capacity||0) + (slot.trn_capacity||0);
                const totalBooked = (slot.reg_booked||0) + (slot.correction_booked||0) + (slot.ephilid_booked||0) + (slot.trn_booked||0);
                const percent = totalCap > 0 ? (totalBooked / totalCap) * 100 : 0;
                const barColor = percent >= 80 ? 'danger' : (percent >= 50 ? 'warning' : 'success');
                content += `<div class="progress"><div class="progress-bar bg-${barColor}" style="width: ${percent}%"></div></div>`;
            }
            
            if (slot.notes) {
                content += `<div class="slot-notes" title="${slot.notes}">📝 ${slot.notes.substring(0, 20)}${slot.notes.length > 20 ? '...' : ''}</div>`;
            }
        } 
        // Handle working days without slots configured
        else if (isWorkingDay) {
            content = '<div class="not-configured">⚙️ Not configured</div>';
        }
        
        // Render the day (always as div, no clickable links)
        html += `<div class="${dayClass}"><div class="day-number">${d}</div>${content}</div>`;
    }
    
    document.getElementById('calendarDays').innerHTML = html;
}

// Day selection helpers
document.getElementById('selectAllDays')?.addEventListener('click', () => {
    document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => cb.checked = true);
});
document.getElementById('selectWeekdays')?.addEventListener('click', () => {
    document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => {
        cb.checked = [1,2,3,4,5].includes(parseInt(cb.value));
    });
});
document.getElementById('selectWeekends')?.addEventListener('click', () => {
    document.querySelectorAll('#bulkGenerateForm input[name="days[]"]').forEach(cb => {
        cb.checked = [6,7].includes(parseInt(cb.value));
    });
});

// Validate bulk generate dates
document.getElementById('bulkGenerateForm')?.addEventListener('submit', function(e) {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (startDate < today) {
        e.preventDefault();
        alert('Start date cannot be in the past.');
        return false;
    }
    if (endDate < startDate) {
        e.preventDefault();
        alert('End date must be after start date.');
        return false;
    }
});

// Calendar navigation
document.getElementById('prevMonth')?.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    loadSlots();
});
document.getElementById('nextMonth')?.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    loadSlots();
});
document.getElementById('todayBtn')?.addEventListener('click', () => {
    currentDate = new Date();
    loadSlots();
});

loadSlots();
</script>
@endpush
@endsection