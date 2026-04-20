<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\WorkingDaysDefault;
use App\Models\WorkingDaysOverride;
use App\Models\ServiceSlotsConfig;
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
        $totalBooked = AppointmentSlot::sum(DB::raw('reg_booked + correction_booked + ephilid_booked + trn_booked'));
        
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
        return view('admin.slots.create', compact('serviceConfigs'));
    }
    
   public function store(Request $request)
{
    $validated = $request->validate([
        'date' => 'required|date|unique:appointment_slots,date',
        'day_type' => 'required|in:working,half_day,holiday,special',
        'reg_capacity' => 'required|integer|min:0|max:100',
        'correction_capacity' => 'required|integer|min:0|max:100',
        'ephilid_capacity' => 'required|integer|min:0|max:100',
        'trn_capacity' => 'required|integer|min:0|max:100',
        'notes' => 'nullable|string',
    ]);
    
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
    $validated['correction_booked'] = 0;
    $validated['ephilid_booked'] = 0;
    $validated['trn_booked'] = 0;
    
    // Handle different day types
    if ($request->day_type === 'holiday') {
        // For holidays: Set all capacities to 0
        $validated['reg_capacity'] = 0;
        $validated['correction_capacity'] = 0;
        $validated['ephilid_capacity'] = 0;
        $validated['trn_capacity'] = 0;
        $validated['reg_available'] = 0;
        $validated['correction_available'] = 0;
        $validated['ephilid_available'] = 0;
        $validated['trn_available'] = 0;
        $validated['total_capacity'] = 0;
        
        // Add holiday note if not provided
        if (empty($validated['notes'])) {
            $validated['notes'] = 'Public Holiday - No appointments available';
        }
    } 
    elseif ($request->day_type === 'half_day') {
        // For half day: Calculate 50% capacity
        $validated['reg_available'] = ceil($validated['reg_capacity'] / 2);
        $validated['correction_available'] = ceil($validated['correction_capacity'] / 2);
        $validated['ephilid_available'] = ceil($validated['ephilid_capacity'] / 2);
        $validated['trn_available'] = ceil($validated['trn_capacity'] / 2);
        $validated['total_capacity'] = array_sum([
            $validated['reg_available'],
            $validated['correction_available'],
            $validated['ephilid_available'],
            $validated['trn_available']
        ]);
        
        if (empty($validated['notes'])) {
            $validated['notes'] = 'Half day - Limited appointments available';
        }
    }
    else {
        // For working or special days: Full capacity
        $validated['reg_available'] = $validated['reg_capacity'];
        $validated['correction_available'] = $validated['correction_capacity'];
        $validated['ephilid_available'] = $validated['ephilid_capacity'];
        $validated['trn_available'] = $validated['trn_capacity'];
        $validated['total_capacity'] = array_sum([
            $validated['reg_capacity'],
            $validated['correction_capacity'],
            $validated['ephilid_capacity'],
            $validated['trn_capacity']
        ]);
    }
    
    // Create the slot
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
        return view('admin.slots.edit', compact('slot', 'serviceConfigs'));
    }
    
    public function update(Request $request, $id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        
        $validated = $request->validate([
            'day_type' => 'required|in:working,half_day,holiday,special',
            'reg_capacity' => 'required|integer|min:0|max:100',
            'correction_capacity' => 'required|integer|min:0|max:100',
            'ephilid_capacity' => 'required|integer|min:0|max:100',
            'trn_capacity' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);
        
        // Get actual booked counts from appointment_clients for this date
        $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($slot) {
            $query->whereDate('appointment_date', $slot->date)
                  ->whereIn('status', ['pending', 'confirmed']);
        })
        ->selectRaw('service, COUNT(*) as count')
        ->groupBy('service')
        ->pluck('count', 'service')
        ->toArray();
        
        $slot->day_type = $validated['day_type'];
        $slot->reg_capacity = $validated['reg_capacity'];
        $slot->correction_capacity = $validated['correction_capacity'];
        $slot->ephilid_capacity = $validated['ephilid_capacity'];
        $slot->trn_capacity = $validated['trn_capacity'];
        $slot->notes = $validated['notes'] ?? null;
        
        // Set booked counts from actual data
        $slot->reg_booked = $bookedCounts['reg'] ?? 0;
        $slot->correction_booked = $bookedCounts['correction'] ?? 0;
        $slot->ephilid_booked = $bookedCounts['ephilid'] ?? 0;
        $slot->trn_booked = $bookedCounts['trn'] ?? 0;
        
        // Calculate available based on day type
        if ($slot->day_type === 'holiday') {
            $slot->reg_available = 0;
            $slot->correction_available = 0;
            $slot->ephilid_available = 0;
            $slot->trn_available = 0;
        } elseif ($slot->day_type === 'half_day') {
            $slot->reg_available = max(0, ceil($slot->reg_capacity / 2) - $slot->reg_booked);
            $slot->correction_available = max(0, ceil($slot->correction_capacity / 2) - $slot->correction_booked);
            $slot->ephilid_available = max(0, ceil($slot->ephilid_capacity / 2) - $slot->ephilid_booked);
            $slot->trn_available = max(0, ceil($slot->trn_capacity / 2) - $slot->trn_booked);
        } else {
            $slot->reg_available = max(0, $slot->reg_capacity - $slot->reg_booked);
            $slot->correction_available = max(0, $slot->correction_capacity - $slot->correction_booked);
            $slot->ephilid_available = max(0, $slot->ephilid_capacity - $slot->ephilid_booked);
            $slot->trn_available = max(0, $slot->trn_capacity - $slot->trn_booked);
        }
        
        $slot->save();
        
        return redirect()->route('admin.slots.index')->with('success', 'Slot updated successfully.');
    }
    
    public function destroy($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        
        $hasAppointments = Appointment::whereDate('appointment_date', $slot->date)->exists();
        
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
            'reg_capacity' => 'required|integer|min:0|max:100',
            'correction_capacity' => 'required|integer|min:0|max:100',
            'ephilid_capacity' => 'required|integer|min:0|max:100',
            'trn_capacity' => 'required|integer|min:0|max:100',
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
            
            $existing = AppointmentSlot::where('date', $date->format('Y-m-d'))->first();
            
            if ($existing) {
                $skipped++;
                continue;
            }
            
            AppointmentSlot::create([
                'date' => $date->format('Y-m-d'),
                'day_type' => 'working',
                'reg_capacity' => $request->reg_capacity,
                'correction_capacity' => $request->correction_capacity,
                'ephilid_capacity' => $request->ephilid_capacity,
                'trn_capacity' => $request->trn_capacity,
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
            
            // Get default capacities from service_slots_config
            $defaultCapacities = ServiceSlotsConfig::all()->pluck('default_capacity', 'service_code')->toArray();
            
            $defaultRegCapacity = $defaultCapacities['reg'] ?? 10;
            $defaultCorrectionCapacity = $defaultCapacities['correction'] ?? 5;
            $defaultEphilidCapacity = $defaultCapacities['ephilid'] ?? 3;
            $defaultTrnCapacity = $defaultCapacities['trn'] ?? 2;
            
            $slots = AppointmentSlot::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->keyBy('date');
            
            // Get actual booked counts from appointment_clients for accuracy
            $clientBookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($month, $year) {
                $query->whereMonth('appointment_date', $month)
                      ->whereYear('appointment_date', $year)
                      ->whereIn('status', ['pending', 'confirmed']);
            })
            ->selectRaw('DATE(appointments.appointment_date) as date, service, COUNT(*) as count')
            ->join('appointments', 'appointment_clients.appointment_id', '=', 'appointments.id')
            ->groupBy('date', 'service')
            ->get()
            ->groupBy('date');
            
            $result = [];
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                
                // Get actual booked counts from clients
                $bookedData = $clientBookedCounts->get($date);
                $regBooked = 0;
                $correctionBooked = 0;
                $ephilidBooked = 0;
                $trnBooked = 0;
                
                if ($bookedData) {
                    foreach ($bookedData as $item) {
                        switch ($item->service) {
                            case 'reg': $regBooked = $item->count; break;
                            case 'correction': $correctionBooked = $item->count; break;
                            case 'ephilid': $ephilidBooked = $item->count; break;
                            case 'trn': $trnBooked = $item->count; break;
                        }
                    }
                }
                
                if ($slots->has($date)) {
                    $slot = $slots->get($date);
                    
                    // Use slot values if they exist
                    $regCapacity = $slot->reg_capacity;
                    $correctionCapacity = $slot->correction_capacity;
                    $ephilidCapacity = $slot->ephilid_capacity;
                    $trnCapacity = $slot->trn_capacity;
                    
                    // Calculate available based on day type and slot values
                    if ($slot->day_type === 'holiday') {
                        $regAvailable = 0;
                        $correctionAvailable = 0;
                        $ephilidAvailable = 0;
                        $trnAvailable = 0;
                    } elseif ($slot->day_type === 'half_day') {
                        $regAvailable = max(0, ceil($regCapacity / 2) - $regBooked);
                        $correctionAvailable = max(0, ceil($correctionCapacity / 2) - $correctionBooked);
                        $ephilidAvailable = max(0, ceil($ephilidCapacity / 2) - $ephilidBooked);
                        $trnAvailable = max(0, ceil($trnCapacity / 2) - $trnBooked);
                    } else {
                        $regAvailable = max(0, $regCapacity - $regBooked);
                        $correctionAvailable = max(0, $correctionCapacity - $correctionBooked);
                        $ephilidAvailable = max(0, $ephilidCapacity - $ephilidBooked);
                        $trnAvailable = max(0, $trnCapacity - $trnBooked);
                    }
                    
                    $result[$date] = [
                        'id' => $slot->id,
                        'day_type' => $slot->day_type,
                        'reg_capacity' => (int)$regCapacity,
                        'reg_booked' => (int)$regBooked,
                        'reg_available' => (int)$regAvailable,
                        'correction_capacity' => (int)$correctionCapacity,
                        'correction_booked' => (int)$correctionBooked,
                        'correction_available' => (int)$correctionAvailable,
                        'ephilid_capacity' => (int)$ephilidCapacity,
                        'ephilid_booked' => (int)$ephilidBooked,
                        'ephilid_available' => (int)$ephilidAvailable,
                        'trn_capacity' => (int)$trnCapacity,
                        'trn_booked' => (int)$trnBooked,
                        'trn_available' => (int)$trnAvailable,
                        'notes' => $slot->notes,
                    ];
                } else {
                    // No slot exists - use default capacities from service_slots_config
                    $result[$date] = [
                        'exists' => false,
                        'day_type' => 'working',
                        'reg_capacity' => (int)$defaultRegCapacity,
                        'reg_booked' => (int)$regBooked,
                        'reg_available' => max(0, $defaultRegCapacity - $regBooked),
                        'correction_capacity' => (int)$defaultCorrectionCapacity,
                        'correction_booked' => (int)$correctionBooked,
                        'correction_available' => max(0, $defaultCorrectionCapacity - $correctionBooked),
                        'ephilid_capacity' => (int)$defaultEphilidCapacity,
                        'ephilid_booked' => (int)$ephilidBooked,
                        'ephilid_available' => max(0, $defaultEphilidCapacity - $ephilidBooked),
                        'trn_capacity' => (int)$defaultTrnCapacity,
                        'trn_booked' => (int)$trnBooked,
                        'trn_available' => max(0, $defaultTrnCapacity - $trnBooked),
                        'notes' => null,
                    ];
                }
            }
            
            return response()->json(['slots' => $result]);
            
        } catch (\Exception $e) {
            Log::error('Error in getSlotsJson: ' . $e->getMessage());
            return response()->json(['slots' => [], 'error' => $e->getMessage()], 500);
        }
    }
}