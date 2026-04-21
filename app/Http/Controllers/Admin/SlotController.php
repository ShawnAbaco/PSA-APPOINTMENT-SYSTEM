<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\WorkingDaysDefault;
use App\Models\WorkingDaysOverride;
use App\Models\ServiceSlotsConfig;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlotController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->has('ajax')) {
            return $this->getSlotsJson($request);
        }
        
        $query = AppointmentSlot::orderBy('date', 'desc');
        
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }
        
        $slots = $query->paginate(30);
        
        $totalSlots = AppointmentSlot::count();
        $totalHolidays = AppointmentSlot::where('day_type', 'holiday')->count();
        $totalSpecialDays = AppointmentSlot::where('day_type', 'special')->count();
        $totalHalfDays = AppointmentSlot::where('day_type', 'half_day')->count();
        $totalBooked = AppointmentSlot::sum(DB::raw('reg_booked + updating_booked + inquiry_booked'));
        
        $currentMonth = $request->get('month', date('m'));
        $currentYear = $request->get('year', date('Y'));
        
        // Get working days for meta tag
        $workingDaysArray = [1,2,3,4,5];
        try {
            $workingDaysArray = WorkingDaysDefault::where('is_working', true)->pluck('day_of_week')->toArray();
            if (empty($workingDaysArray)) {
                $workingDaysArray = [1,2,3,4,5];
            }
        } catch (\Exception $e) {
            Log::warning('WorkingDaysDefault table not ready: ' . $e->getMessage());
        }
        $workingDays = implode(',', $workingDaysArray);
        
        return view('admin.slots.index', compact('slots', 'totalSlots', 'totalHolidays', 'totalSpecialDays', 'totalHalfDays', 'totalBooked', 'currentMonth', 'currentYear', 'workingDays'));
    }
    
    public function create()
    {
        $serviceConfigs = ServiceSlotsConfig::all();
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        return view('admin.slots.create', compact('serviceConfigs', 'timeSlots'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'day_type' => 'required|in:working,half_day,holiday,special',
            'reg_capacity' => 'required|integer|min:0|max:100',
            'updating_capacity' => 'required|integer|min:0|max:100',
            'inquiry_capacity' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);
        
        // Check for existing slot on same date and time slot
        $existing = AppointmentSlot::where('date', $request->date)
            ->where('time_slot_id', $request->time_slot_id)
            ->first();
        
        if ($existing) {
            return redirect()->back()
                ->with('error', 'A slot already exists for this date and time slot.')
                ->withInput();
        }
        
        // Check if the date is a working day
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek == 0 ? 7 : $date->dayOfWeek;
        
        $isWorkingDay = WorkingDaysDefault::where('day_of_week', $dayOfWeek)
            ->where('is_working', true)
            ->exists();
        
        // Special validation: If it's a holiday, skip working day check
        if ($request->day_type !== 'holiday' && $request->day_type !== 'special') {
            if (!$isWorkingDay) {
                return redirect()->back()
                    ->with('error', 'Cannot create regular slots on non-working days. Only "Holiday" or "Special" day types can be used for non-working days.')
                    ->withInput();
            }
        }
        
        $validated['created_by'] = auth()->id();
        $validated['reg_booked'] = 0;
        $validated['updating_booked'] = 0;
        $validated['inquiry_booked'] = 0;
        
        // Handle different day types
        if ($request->day_type === 'holiday') {
            $validated['reg_capacity'] = 0;
            $validated['updating_capacity'] = 0;
            $validated['inquiry_capacity'] = 0;
            $validated['reg_available'] = 0;
            $validated['updating_available'] = 0;
            $validated['inquiry_available'] = 0;
            $validated['total_capacity'] = 0;
            
            if (empty($validated['notes'])) {
                $validated['notes'] = 'Public Holiday - No appointments available';
            }
        } 
        elseif ($request->day_type === 'half_day') {
            $validated['reg_available'] = ceil($validated['reg_capacity'] / 2);
            $validated['updating_available'] = ceil($validated['updating_capacity'] / 2);
            $validated['inquiry_available'] = ceil($validated['inquiry_capacity'] / 2);
            $validated['total_capacity'] = array_sum([
                $validated['reg_available'],
                $validated['updating_available'],
                $validated['inquiry_available']
            ]);
            
            if (empty($validated['notes'])) {
                $validated['notes'] = 'Half day - Limited appointments available';
            }
        }
        else {
            $validated['reg_available'] = $validated['reg_capacity'];
            $validated['updating_available'] = $validated['updating_capacity'];
            $validated['inquiry_available'] = $validated['inquiry_capacity'];
            $validated['total_capacity'] = array_sum([
                $validated['reg_capacity'],
                $validated['updating_capacity'],
                $validated['inquiry_capacity']
            ]);
        }
        
        $slot = AppointmentSlot::create($validated);
        
        // Also create an entry in working_days_overrides for holidays
        if ($request->day_type === 'holiday') {
            WorkingDaysOverride::updateOrCreate(
                ['date' => $request->date],
                [
                    'is_working' => false,
                    'reason' => $validated['notes'] ?? 'Holiday',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        
        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot created successfully as ' . ucfirst(str_replace('_', ' ', $request->day_type)) . '.');
    }
    
    public function edit($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        $serviceConfigs = ServiceSlotsConfig::all();
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        return view('admin.slots.edit', compact('slot', 'serviceConfigs', 'timeSlots'));
    }
    
    public function update(Request $request, $id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        
        $validated = $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'day_type' => 'required|in:working,half_day,holiday,special',
            'reg_capacity' => 'required|integer|min:0|max:100',
            'updating_capacity' => 'required|integer|min:0|max:100',
            'inquiry_capacity' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);
        
        // Get actual booked counts from appointment_clients for this date and time slot
        $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($slot) {
            $query->whereDate('appointment_date', $slot->date)
                  ->where('time_slot_id', $slot->time_slot_id)
                  ->whereIn('status', ['pending', 'confirmed']);
        })
        ->selectRaw('service, COUNT(*) as count')
        ->groupBy('service')
        ->pluck('count', 'service')
        ->toArray();
        
        $slot->time_slot_id = $validated['time_slot_id'];
        $slot->day_type = $validated['day_type'];
        $slot->reg_capacity = $validated['reg_capacity'];
        $slot->updating_capacity = $validated['updating_capacity'];
        $slot->inquiry_capacity = $validated['inquiry_capacity'];
        $slot->notes = $validated['notes'] ?? null;
        
        // Set booked counts from actual data
        $slot->reg_booked = $bookedCounts['reg'] ?? 0;
        $slot->updating_booked = $bookedCounts['updating'] ?? 0;
        $slot->inquiry_booked = $bookedCounts['inquiry'] ?? 0;
        
        // Calculate available based on day type
        if ($slot->day_type === 'holiday') {
            $slot->reg_available = 0;
            $slot->updating_available = 0;
            $slot->inquiry_available = 0;
        } elseif ($slot->day_type === 'half_day') {
            $slot->reg_available = max(0, ceil($slot->reg_capacity / 2) - $slot->reg_booked);
            $slot->updating_available = max(0, ceil($slot->updating_capacity / 2) - $slot->updating_booked);
            $slot->inquiry_available = max(0, ceil($slot->inquiry_capacity / 2) - $slot->inquiry_booked);
        } else {
            $slot->reg_available = max(0, $slot->reg_capacity - $slot->reg_booked);
            $slot->updating_available = max(0, $slot->updating_capacity - $slot->updating_booked);
            $slot->inquiry_available = max(0, $slot->inquiry_capacity - $slot->inquiry_booked);
        }
        
        $slot->total_capacity = $slot->reg_available + $slot->updating_available + $slot->inquiry_available;
        
        $slot->save();
        
        return redirect()->route('admin.slots.index')->with('success', 'Slot updated successfully.');
    }
    
    public function destroy($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        
        $hasAppointments = Appointment::whereDate('appointment_date', $slot->date)
            ->where('time_slot_id', $slot->time_slot_id)
            ->exists();
        
        if ($hasAppointments) {
            return response()->json(['success' => false, 'message' => 'Cannot delete slot with existing appointments.'], 400);
        }
        
        $slot->delete();
        
        return response()->json(['success' => true, 'message' => 'Slot deleted successfully.']);
    }
    
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'reg_capacity' => 'required|integer|min:0|max:100',
            'updating_capacity' => 'required|integer|min:0|max:100',
            'inquiry_capacity' => 'required|integer|min:0|max:100',
            'days' => 'nullable|array',
            'days.*' => 'integer|between:1,7',
        ]);
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $selectedDays = $request->days;
        $created = 0;
        $skipped = 0;
        
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;
            
            if (!empty($selectedDays) && !in_array($dayOfWeek, $selectedDays)) {
                $skipped++;
                continue;
            }
            
            $existing = AppointmentSlot::where('date', $date->format('Y-m-d'))
                ->where('time_slot_id', $request->time_slot_id)
                ->first();
            
            if ($existing) {
                $skipped++;
                continue;
            }
            
            AppointmentSlot::create([
                'date' => $date->format('Y-m-d'),
                'time_slot_id' => $request->time_slot_id,
                'day_type' => 'working',
                'reg_capacity' => $request->reg_capacity,
                'updating_capacity' => $request->updating_capacity,
                'inquiry_capacity' => $request->inquiry_capacity,
                'reg_booked' => 0,
                'updating_booked' => 0,
                'inquiry_booked' => 0,
                'reg_available' => $request->reg_capacity,
                'updating_available' => $request->updating_capacity,
                'inquiry_available' => $request->inquiry_capacity,
                'total_capacity' => $request->reg_capacity + $request->updating_capacity + $request->inquiry_capacity,
                'created_by' => auth()->id(),
            ]);
            $created++;
        }
        
        $message = "Generated {$created} slots.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} slots (existing or excluded by day filter).";
        }
        
        return redirect()->route('admin.slots.index')->with('success', $message);
    }
    
   public function getSlotsJson(Request $request)
{
    try {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        
        // Get ALL slots for the month (with time_slot_id)
        $slots = AppointmentSlot::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('timeSlot')
            ->get()
            ->groupBy('date');
        
        $result = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m');
            
            if ($slots->has($date)) {
                // Aggregate all time slots for this date
                $daySlots = $slots->get($date);
                $aggregated = [];
                
                foreach ($daySlots as $slot) {
                    $aggregated[$slot->time_slot_id] = [
                        'time_slot_label' => $slot->timeSlot->slot_label,
                        'reg_available' => $slot->reg_available,
                        'updating_available' => $slot->updating_available,
                        'inquiry_available' => $slot->inquiry_available,
                        'reg_capacity' => $slot->reg_capacity,
                        'updating_capacity' => $slot->updating_capacity,
                        'inquiry_capacity' => $slot->inquiry_capacity,
                        'day_type' => $slot->day_type,
                        'notes' => $slot->notes,
                    ];
                }
                
                $result[$date] = $aggregated;
            } else {
                // No slots configured for this date
                $result[$date] = [];
            }
        }
        
        return response()->json(['slots' => $result]);
        
    } catch (\Exception $e) {
        Log::error('Error in getSlotsJson: ' . $e->getMessage());
        return response()->json(['slots' => [], 'error' => $e->getMessage()], 500);
    }
}
    
    public function toggleHoliday($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        
        if ($slot->day_type === 'holiday') {
            $slot->day_type = 'working';
            $slot->notes = null;
            $message = 'Slot converted back to working day.';
        } else {
            $slot->day_type = 'holiday';
            $slot->reg_capacity = 0;
            $slot->updating_capacity = 0;
            $slot->inquiry_capacity = 0;
            $slot->reg_available = 0;
            $slot->updating_available = 0;
            $slot->inquiry_available = 0;
            $slot->total_capacity = 0;
            $slot->notes = 'Marked as holiday';
            $message = 'Slot marked as holiday.';
        }
        
        $slot->save();
        
        return response()->json(['success' => true, 'message' => $message]);
    }
    
    public function getSlotDetails($date)
    {
        $slots = AppointmentSlot::where('date', $date)->with('timeSlot')->get();
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'slots' => $slots
        ]);
    }
}