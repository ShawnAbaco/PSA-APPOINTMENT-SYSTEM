<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\WorkingDaysDefault;
use App\Models\WorkingDaysOverride;
use App\Models\SlotCapacityRule;
use App\Models\SlotCapacityOverride;
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
        
        // Get all appointment slots (date + time_slot instances)
        $query = AppointmentSlot::orderBy('date', 'desc');
        
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }
        
        $slots = $query->paginate(30);
        
        // Calculate stats based on appointment_slots (existence only)
        $totalSlots = AppointmentSlot::count();
        
        // For day types, we need to check working_days_overrides and appointment_slots day_type
        $totalHolidays = AppointmentSlot::where('day_type', 'holiday')->count();
        $totalSpecialDays = AppointmentSlot::where('day_type', 'special')->count();
        $totalHalfDays = AppointmentSlot::where('day_type', 'half_day')->count();
        
        // Total booked from actual appointments
        $totalBooked = Appointment::count();
        
        $currentMonth = $request->get('month', date('m'));
        $currentYear = $request->get('year', date('Y'));
        
        // Get working days for meta tag (0=Sunday, 1=Monday, etc.)
        $workingDaysArray = [1, 2, 3, 4, 5]; // Monday to Friday
        try {
            $workingDaysArray = WorkingDaysDefault::where('is_working', true)->pluck('day_of_week')->toArray();
            if (empty($workingDaysArray)) {
                $workingDaysArray = [1, 2, 3, 4, 5];
            }
        } catch (\Exception $e) {
            Log::warning('WorkingDaysDefault table not ready: ' . $e->getMessage());
        }
        $workingDays = implode(',', $workingDaysArray);
        
        // Get time slots for bulk generate modal
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        
        return view('admin.slots.index', compact('slots', 'totalSlots', 'totalHolidays', 'totalSpecialDays', 'totalHalfDays', 'totalBooked', 'currentMonth', 'currentYear', 'workingDays', 'timeSlots'));
    }
    
    public function create()
    {
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        return view('admin.slots.create', compact('timeSlots'));
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
        
        $date = Carbon::parse($request->date);
        
        // Check if slot already exists (date + time_slot_id)
        $existingSlot = AppointmentSlot::where('date', $request->date)
            ->where('time_slot_id', $request->time_slot_id)
            ->first();
        
        if ($existingSlot) {
            return redirect()->back()
                ->with('error', 'A slot already exists for this date and time slot.')
                ->withInput();
        }
        
        // Create the appointment slot (just the existence record)
        $slot = AppointmentSlot::create([
            'date' => $request->date,
            'time_slot_id' => $request->time_slot_id,
            'day_type' => $request->day_type,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
        
        // Create capacity rule for this specific date and time slot (override)
        SlotCapacityOverride::updateOrCreate(
            [
                'date' => $request->date,
                'time_slot_id' => $request->time_slot_id,
            ],
            [
                'reg_capacity' => $request->reg_capacity,
                'updating_capacity' => $request->updating_capacity,
                'inquiry_capacity' => $request->inquiry_capacity,
                'reason' => $validated['notes'] ?? ($request->day_type === 'holiday' ? 'Holiday' : 'Manual override'),
            ]
        );
        
        // Also create an entry in working_days_overrides for holidays
        if ($request->day_type === 'holiday') {
            WorkingDaysOverride::updateOrCreate(
                ['date' => $request->date],
                [
                    'is_working' => false,
                    'reason' => $validated['notes'] ?? 'Holiday',
                ]
            );
        }
        
        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot created successfully with capacity settings.');
    }
    
    public function edit($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        
        // Get the override for this slot if exists
        $override = SlotCapacityOverride::where('date', $slot->date)
            ->where('time_slot_id', $slot->time_slot_id)
            ->first();
        
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        
        return view('admin.slots.edit', compact('slot', 'override', 'timeSlots'));
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
        
        // Update the slot existence record
        $slot->time_slot_id = $validated['time_slot_id'];
        $slot->day_type = $validated['day_type'];
        $slot->notes = $validated['notes'] ?? null;
        $slot->save();
        
        // Update or create capacity override
        SlotCapacityOverride::updateOrCreate(
            [
                'date' => $slot->date,
                'time_slot_id' => $slot->time_slot_id,
            ],
            [
                'reg_capacity' => $request->reg_capacity,
                'updating_capacity' => $request->updating_capacity,
                'inquiry_capacity' => $request->inquiry_capacity,
                'reason' => $validated['notes'] ?? ($slot->day_type === 'holiday' ? 'Holiday' : 'Manual override'),
            ]
        );
        
        // Update working days override for holidays
        if ($slot->day_type === 'holiday') {
            WorkingDaysOverride::updateOrCreate(
                ['date' => $slot->date],
                [
                    'is_working' => false,
                    'reason' => $validated['notes'] ?? 'Holiday',
                ]
            );
        } else {
            // If it's not a holiday, remove from working days overrides if exists
            WorkingDaysOverride::where('date', $slot->date)->delete();
        }
        
        return redirect()->route('admin.slots.index')->with('success', 'Slot updated successfully.');
    }
    
    public function destroy($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        
        // Check if there are appointments for this slot
        $hasAppointments = Appointment::whereDate('appointment_date', $slot->date)
            ->where('time_slot_id', $slot->time_slot_id)
            ->exists();
        
        if ($hasAppointments) {
            return response()->json(['success' => false, 'message' => 'Cannot delete slot with existing appointments.'], 400);
        }
        
        // Delete the slot existence record
        $slot->delete();
        
        // Delete associated capacity override
        SlotCapacityOverride::where('date', $slot->date)
            ->where('time_slot_id', $slot->time_slot_id)
            ->delete();
        
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
            // Convert to database format (1=Monday, 7=Sunday)
            $dbDayOfWeek = $dayOfWeek == 0 ? 7 : $dayOfWeek;
            
            if (!empty($selectedDays) && !in_array($dbDayOfWeek, $selectedDays)) {
                $skipped++;
                continue;
            }
            
            // Check if slot already exists
            $existing = AppointmentSlot::where('date', $date->format('Y-m-d'))
                ->where('time_slot_id', $request->time_slot_id)
                ->first();
            
            if ($existing) {
                $skipped++;
                continue;
            }
            
            // Create the appointment slot
            AppointmentSlot::create([
                'date' => $date->format('Y-m-d'),
                'time_slot_id' => $request->time_slot_id,
                'day_type' => 'working',
                'notes' => null,
                'created_by' => auth()->id(),
            ]);
            
            // Create capacity override for this slot
            SlotCapacityOverride::create([
                'date' => $date->format('Y-m-d'),
                'time_slot_id' => $request->time_slot_id,
                'reg_capacity' => $request->reg_capacity,
                'updating_capacity' => $request->updating_capacity,
                'inquiry_capacity' => $request->inquiry_capacity,
                'reason' => 'Bulk generated',
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
            
            // Get all appointment slots for the month
            $slots = AppointmentSlot::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->with('timeSlot')
                ->get()
                ->groupBy('date');
            
            // Get all overrides for these dates
            $dates = AppointmentSlot::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->pluck('date')
                ->unique();
            
            $overrides = SlotCapacityOverride::whereIn('date', $dates)
                ->get()
                ->groupBy(function($item) {
                    return $item->date . '_' . $item->time_slot_id;
                });
            
            $result = [];
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                $dateSlots = $slots->get($date) ?? collect();
                
                if ($dateSlots->isNotEmpty()) {
                    $aggregated = [];
                    
                    foreach ($dateSlots as $slot) {
                        $overrideKey = $slot->date . '_' . $slot->time_slot_id;
                        $override = $overrides->get($overrideKey);
                        $capacity = $override && $override->isNotEmpty() ? $override->first() : null;
                        
                        // Get capacity from override or default from rules
                        if ($capacity) {
                            $regCapacity = $capacity->reg_capacity ?? 0;
                            $updatingCapacity = $capacity->updating_capacity ?? 0;
                            $inquiryCapacity = $capacity->inquiry_capacity ?? 0;
                        } else {
                            // Get default capacity from rules based on day type
                            $dayType = $this->getDayTypeForDate(Carbon::parse($date));
                            $rule = SlotCapacityRule::where('time_slot_id', $slot->time_slot_id)
                                ->where('day_type', $dayType)
                                ->first();
                            
                            $regCapacity = $rule->reg_capacity ?? 0;
                            $updatingCapacity = $rule->updating_capacity ?? 0;
                            $inquiryCapacity = $rule->inquiry_capacity ?? 0;
                        }
                        
                        // Get actual booked counts
                        $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($slot) {
                            $query->whereDate('appointment_date', $slot->date)
                                  ->where('time_slot_id', $slot->time_slot_id)
                                  ->whereIn('status', ['pending', 'confirmed']);
                        })
                        ->selectRaw('service, COUNT(*) as count')
                        ->groupBy('service')
                        ->pluck('count', 'service')
                        ->toArray();
                        
                        $regBooked = $bookedCounts['reg'] ?? 0;
                        $updatingBooked = $bookedCounts['updating'] ?? 0;
                        $inquiryBooked = $bookedCounts['inquiry'] ?? 0;
                        
                        $aggregated[$slot->time_slot_id] = [
                            'time_slot_label' => $slot->timeSlot->slot_label,
                            'reg_available' => max(0, $regCapacity - $regBooked),
                            'updating_available' => max(0, $updatingCapacity - $updatingBooked),
                            'inquiry_available' => max(0, $inquiryCapacity - $inquiryBooked),
                            'reg_capacity' => $regCapacity,
                            'updating_capacity' => $updatingCapacity,
                            'inquiry_capacity' => $inquiryCapacity,
                            'day_type' => $slot->day_type,
                            'notes' => $slot->notes,
                        ];
                    }
                    
                    $result[$date] = $aggregated;
                } else {
                    $result[$date] = [];
                }
            }
            
            return response()->json(['slots' => $result]);
            
        } catch (\Exception $e) {
            Log::error('Error in getSlotsJson: ' . $e->getMessage());
            return response()->json(['slots' => [], 'error' => $e->getMessage()], 500);
        }
    }
    
    private function getDayTypeForDate($date)
    {
        // Check override first
        $override = WorkingDaysOverride::where('date', $date->format('Y-m-d'))->first();
        if ($override) {
            return $override->is_working ? 'weekday' : 'holiday';
        }
        
        // Check default
        $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 1=Monday, etc.
        $default = WorkingDaysDefault::where('day_of_week', $dayOfWeek)->first();
        
        if (!$default || !$default->is_working) {
            if ($dayOfWeek == 6) return 'saturday';
            if ($dayOfWeek == 0) return 'sunday';
            return 'holiday';
        }
        
        return 'weekday';
    }
    
    public function toggleHoliday($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        
        if ($slot->day_type === 'holiday') {
            // Remove holiday status
            $slot->day_type = 'working';
            $slot->notes = null;
            $slot->save();
            
            // Remove from working days overrides
            WorkingDaysOverride::where('date', $slot->date)->delete();
            
            // Delete capacity override or set to default
            SlotCapacityOverride::where('date', $slot->date)
                ->where('time_slot_id', $slot->time_slot_id)
                ->delete();
            
            $message = 'Slot converted back to working day.';
        } else {
            // Mark as holiday
            $slot->day_type = 'holiday';
            $slot->notes = 'Marked as holiday';
            $slot->save();
            
            // Add to working days overrides
            WorkingDaysOverride::updateOrCreate(
                ['date' => $slot->date],
                ['is_working' => false, 'reason' => 'Holiday']
            );
            
            // Set capacity override to zero
            SlotCapacityOverride::updateOrCreate(
                [
                    'date' => $slot->date,
                    'time_slot_id' => $slot->time_slot_id,
                ],
                [
                    'reg_capacity' => 0,
                    'updating_capacity' => 0,
                    'inquiry_capacity' => 0,
                    'reason' => 'Holiday - No appointments',
                ]
            );
            
            $message = 'Slot marked as holiday.';
        }
        
        return response()->json(['success' => true, 'message' => $message]);
    }
    
    public function getSlotDetails($date)
    {
        $slots = AppointmentSlot::where('date', $date)->with('timeSlot')->get();
        
        $slotDetails = [];
        foreach ($slots as $slot) {
            $override = SlotCapacityOverride::where('date', $slot->date)
                ->where('time_slot_id', $slot->time_slot_id)
                ->first();
            
            // Get actual booked counts
            $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($slot) {
                $query->whereDate('appointment_date', $slot->date)
                      ->where('time_slot_id', $slot->time_slot_id)
                      ->whereIn('status', ['pending', 'confirmed']);
            })
            ->selectRaw('service, COUNT(*) as count')
            ->groupBy('service')
            ->pluck('count', 'service')
            ->toArray();
            
            $slotDetails[] = [
                'id' => $slot->id,
                'time_slot_id' => $slot->time_slot_id,
                'time_slot_label' => $slot->timeSlot->slot_label,
                'day_type' => $slot->day_type,
                'reg_capacity' => $override->reg_capacity ?? 0,
                'updating_capacity' => $override->updating_capacity ?? 0,
                'inquiry_capacity' => $override->inquiry_capacity ?? 0,
                'reg_booked' => $bookedCounts['reg'] ?? 0,
                'updating_booked' => $bookedCounts['updating'] ?? 0,
                'inquiry_booked' => $bookedCounts['inquiry'] ?? 0,
                'notes' => $slot->notes,
            ];
        }
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'slots' => $slotDetails
        ]);
    }
}