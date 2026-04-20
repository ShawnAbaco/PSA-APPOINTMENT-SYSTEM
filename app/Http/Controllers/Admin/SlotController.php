<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSlot;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

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
        
        return view('admin.slots.index', compact('slots', 'totalSlots', 'totalHolidays', 'totalSpecialDays', 'totalHalfDays', 'totalBooked', 'currentMonth', 'currentYear'));
    }
    
    public function create()
    {
        $serviceConfigs = \App\Models\ServiceSlotsConfig::all();
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
        
        $validated['created_by'] = auth()->id();
        
        AppointmentSlot::create($validated);
        
        return redirect()->route('admin.slots.index')->with('success', 'Slot created successfully.');
    }
    
    public function edit($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        $serviceConfigs = \App\Models\ServiceSlotsConfig::all();
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
        
        // If holiday, set all available to 0
        if ($slot->day_type === 'holiday') {
            $slot->reg_available = 0;
            $slot->correction_available = 0;
            $slot->ephilid_available = 0;
            $slot->trn_available = 0;
        } elseif ($slot->day_type === 'half_day') {
            $slot->reg_available = ceil($slot->reg_capacity / 2) - $slot->reg_booked;
            $slot->correction_available = ceil($slot->correction_capacity / 2) - $slot->correction_booked;
            $slot->ephilid_available = ceil($slot->ephilid_capacity / 2) - $slot->ephilid_booked;
            $slot->trn_available = ceil($slot->trn_capacity / 2) - $slot->trn_booked;
        } else {
            $slot->reg_available = $slot->reg_capacity - $slot->reg_booked;
            $slot->correction_available = $slot->correction_capacity - $slot->correction_booked;
            $slot->ephilid_available = $slot->ephilid_capacity - $slot->ephilid_booked;
            $slot->trn_available = $slot->trn_capacity - $slot->trn_booked;
        }
        
        // Ensure no negative values
        $slot->reg_available = max(0, $slot->reg_available);
        $slot->correction_available = max(0, $slot->correction_available);
        $slot->ephilid_available = max(0, $slot->ephilid_available);
        $slot->trn_available = max(0, $slot->trn_available);
        
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
            
            $slots = AppointmentSlot::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->keyBy('date');
            
            $result = [];
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                
                if ($slots->has($date)) {
                    $slot = $slots->get($date);
                    $result[$date] = [
                        'id' => $slot->id,
                        'day_type' => $slot->day_type,
                        'total_capacity' => $slot->total_capacity,
                        'reg_capacity' => $slot->reg_capacity,
                        'reg_booked' => $slot->reg_booked,
                        'reg_available' => $slot->reg_available,
                        'correction_capacity' => $slot->correction_capacity,
                        'correction_booked' => $slot->correction_booked,
                        'correction_available' => $slot->correction_available,
                        'ephilid_capacity' => $slot->ephilid_capacity,
                        'ephilid_booked' => $slot->ephilid_booked,
                        'ephilid_available' => $slot->ephilid_available,
                        'trn_capacity' => $slot->trn_capacity,
                        'trn_booked' => $slot->trn_booked,
                        'trn_available' => $slot->trn_available,
                        'notes' => $slot->notes,
                    ];
                } else {
                    $result[$date] = [
                        'exists' => false,
                        'day_type' => 'working',
                    ];
                }
            }
            
            return response()->json(['slots' => $result]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getSlotsJson: ' . $e->getMessage());
            return response()->json(['slots' => [], 'error' => $e->getMessage()], 500);
        }
    }
}