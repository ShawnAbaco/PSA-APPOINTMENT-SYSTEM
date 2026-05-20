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
        $totalBooked = Appointment::count();
        
        $currentMonth = $request->get('month', date('m'));
        $currentYear = $request->get('year', date('Y'));
        
        $workingDaysArray = [1, 2, 3, 4, 5];
        try {
            $workingDaysRecords = WorkingDaysDefault::where('day_type', 'working')->get();
            $workingDaysArray = [];
            foreach ($workingDaysRecords as $record) {
                $dayNumber = $this->getDayNumber($record->day_name);
                if ($dayNumber) {
                    $workingDaysArray[] = $dayNumber;
                }
            }
            if (empty($workingDaysArray)) {
                $workingDaysArray = [1, 2, 3, 4, 5];
            }
        } catch (\Exception $e) {
            Log::warning('WorkingDaysDefault table not ready: ' . $e->getMessage());
        }
        $workingDays = implode(',', $workingDaysArray);
        
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        $capacityRules = SlotCapacityRule::all()->groupBy('time_slot_id');
        
        return view('admin.slots.index', compact('slots', 'totalSlots', 'totalHolidays', 'totalSpecialDays', 'totalHalfDays', 'totalBooked', 'currentMonth', 'currentYear', 'workingDays', 'timeSlots', 'capacityRules'));
    }
    
    private function getDayNumber($dayName)
    {
        $days = [
            'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4,
            'friday' => 5, 'saturday' => 6, 'sunday' => 7,
        ];
        return $days[strtolower($dayName)] ?? null;
    }
    
    private function getDayName($dayNumber)
    {
        $days = [
            1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday',
            5 => 'friday', 6 => 'saturday', 7 => 'sunday',
        ];
        return $days[$dayNumber] ?? null;
    }
    
    public function create()
    {
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        return view('admin.slots.create', compact('timeSlots'));
    }
    
    /**
     * Store or UPDATE a slot (UPSERT logic)
     * If slot exists, update it. If not, create new.
     * When admin creates/updates, we create an override.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'time_slot_id' => 'required|exists:time_slots,id',
                'day_type' => 'required|in:working,non_working,holiday',
                'reg_capacity' => 'required|integer|min:0|max:100',
                'updating_capacity' => 'required|integer|min:0|max:100',
                'inquiry_capacity' => 'required|integer|min:0|max:100',
                'notes' => 'nullable|string',
            ]);
            
            DB::beginTransaction();
            
            // Check if slot already exists
            $existingSlot = AppointmentSlot::where('date', $request->date)
                ->where('time_slot_id', $request->time_slot_id)
                ->first();
            
            $isUpdate = false;
            $slot = null;
            
            if ($existingSlot) {
                // UPDATE existing slot
                $isUpdate = true;
                $existingSlot->day_type = $request->day_type;
                $existingSlot->notes = $validated['notes'] ?? null;
                $existingSlot->updated_at = now();
                $existingSlot->save();
                $slot = $existingSlot;
                
                // Create or update capacity override (only when admin manually edits)
                SlotCapacityOverride::updateOrCreate(
                    [
                        'date' => $request->date,
                        'time_slot_id' => $request->time_slot_id,
                    ],
                    [
                        'day_type' => $request->day_type,
                        'reason' => $validated['notes'] ?? ($request->day_type === 'holiday' ? 'Holiday' : 'Manual override'),
                        'reg_capacity' => $request->reg_capacity,
                        'updating_capacity' => $request->updating_capacity,
                        'inquiry_capacity' => $request->inquiry_capacity,
                        'updated_at' => now(),
                    ]
                );
            } else {
                // CREATE new slot
                $slot = AppointmentSlot::create([
                    'date' => $request->date,
                    'time_slot_id' => $request->time_slot_id,
                    'day_type' => $request->day_type,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Create capacity override (only when admin manually creates)
                SlotCapacityOverride::create([
                    'date' => $request->date,
                    'time_slot_id' => $request->time_slot_id,
                    'day_type' => $request->day_type,
                    'reason' => $validated['notes'] ?? ($request->day_type === 'holiday' ? 'Holiday' : 'Manual override'),
                    'reg_capacity' => $request->reg_capacity,
                    'updating_capacity' => $request->updating_capacity,
                    'inquiry_capacity' => $request->inquiry_capacity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Handle working days override for holidays/non_working
            if ($request->day_type === 'holiday' || $request->day_type === 'non_working') {
                WorkingDaysOverride::updateOrCreate(
                    ['date' => $request->date],
                    [
                        'day_type' => $request->day_type,
                        'reason' => $validated['notes'] ?? ($request->day_type === 'holiday' ? 'Holiday' : 'Non-working day'),
                        'updated_at' => now(),
                    ]
                );
            } else {
                // If working day, remove any existing override for this date
                WorkingDaysOverride::where('date', $request->date)->delete();
            }
            
            DB::commit();
            
            $message = $isUpdate ? 'Slot updated successfully!' : 'Slot created successfully!';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'slot' => $slot,
                'is_update' => $isUpdate
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating/updating slot: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $slot = AppointmentSlot::findOrFail($id);
        $override = SlotCapacityOverride::where('date', $slot->date)
            ->where('time_slot_id', $slot->time_slot_id)
            ->first();
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        
        return view('admin.slots.edit', compact('slot', 'override', 'timeSlots'));
    }
    
    public function update(Request $request, $id)
    {
        try {
            $slot = AppointmentSlot::findOrFail($id);
            
            $validated = $request->validate([
                'time_slot_id' => 'required|exists:time_slots,id',
                'day_type' => 'required|in:working,non_working,holiday',
                'reg_capacity' => 'required|integer|min:0|max:100',
                'updating_capacity' => 'required|integer|min:0|max:100',
                'inquiry_capacity' => 'required|integer|min:0|max:100',
                'notes' => 'nullable|string',
            ]);
            
            DB::beginTransaction();
            
            // Check booked counts
            $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($slot) {
                $query->whereDate('appointment_date', $slot->date)
                      ->where('time_slot_id', $slot->time_slot_id);
            })
            ->selectRaw('service, COUNT(*) as count')
            ->groupBy('service')
            ->pluck('count', 'service')
            ->toArray();
            
            $regBooked = $bookedCounts['reg'] ?? 0;
            $updatingBooked = $bookedCounts['updating'] ?? 0;
            $inquiryBooked = $bookedCounts['inquiry'] ?? 0;
            
            // Validate that capacity is not less than booked counts
            if ($request->reg_capacity < $regBooked) {
                throw new \Exception("Registration capacity cannot be less than currently booked ({$regBooked})");
            }
            if ($request->updating_capacity < $updatingBooked) {
                throw new \Exception("Updating capacity cannot be less than currently booked ({$updatingBooked})");
            }
            if ($request->inquiry_capacity < $inquiryBooked) {
                throw new \Exception("Inquiry capacity cannot be less than currently booked ({$inquiryBooked})");
            }
            
            $slot->time_slot_id = $validated['time_slot_id'];
            $slot->day_type = $validated['day_type'];
            $slot->notes = $validated['notes'] ?? null;
            $slot->updated_at = now();
            $slot->save();
            
            // Create or update override (admin manually edited)
            SlotCapacityOverride::updateOrCreate(
                [
                    'date' => $slot->date,
                    'time_slot_id' => $slot->time_slot_id,
                ],
                [
                    'day_type' => $request->day_type,
                    'reason' => $validated['notes'] ?? ($slot->day_type === 'holiday' ? 'Holiday' : ($slot->day_type === 'non_working' ? 'Non-working day' : 'Manual override')),
                    'reg_capacity' => $request->reg_capacity,
                    'updating_capacity' => $request->updating_capacity,
                    'inquiry_capacity' => $request->inquiry_capacity,
                    'updated_at' => now(),
                ]
            );
            
            if ($slot->day_type === 'holiday' || $slot->day_type === 'non_working') {
                WorkingDaysOverride::updateOrCreate(
                    ['date' => $slot->date],
                    [
                        'day_type' => $slot->day_type,
                        'reason' => $validated['notes'] ?? ($slot->day_type === 'holiday' ? 'Holiday' : 'Non-working day'),
                        'updated_at' => now(),
                    ]
                );
            } else {
                WorkingDaysOverride::where('date', $slot->date)->delete();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Slot updated successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating slot: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating slot: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $slot = AppointmentSlot::findOrFail($id);
            
            $hasAppointments = Appointment::whereDate('appointment_date', $slot->date)
                ->where('time_slot_id', $slot->time_slot_id)
                ->exists();
            
            if ($hasAppointments) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot delete slot with existing appointments.'
                ], 400);
            }
            
            DB::beginTransaction();
            
            $slot->delete();
            
            SlotCapacityOverride::where('date', $slot->date)
                ->where('time_slot_id', $slot->time_slot_id)
                ->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true, 
                'message' => 'Slot deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting slot: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error deleting slot: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function bulkGenerate(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'time_slot_ids' => 'required|array|min:1',
                'time_slot_ids.*' => 'required|exists:time_slots,id',
                'reg_capacity' => 'required|integer|min:0|max:100',
                'updating_capacity' => 'required|integer|min:0|max:100',
                'inquiry_capacity' => 'required|integer|min:0|max:100',
                'days' => 'nullable|array',
                'days.*' => 'integer|between:1,7',
            ]);
            
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $selectedDays = $request->days ?? [1, 2, 3, 4, 5];
            $selectedTimeSlotIds = collect($request->input('time_slot_ids', []))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $created = 0;
            $updated = 0;
            $skipped = 0;
            
            DB::beginTransaction();
            
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $dayOfWeek = $date->dayOfWeek;
                $dbDayOfWeek = $dayOfWeek == 0 ? 7 : $dayOfWeek;
                
                if (!in_array($dbDayOfWeek, $selectedDays)) {
                    $skipped++;
                    continue;
                }
                
                foreach ($selectedTimeSlotIds as $timeSlotId) {
                    $existing = AppointmentSlot::where('date', $date->format('Y-m-d'))
                        ->where('time_slot_id', $timeSlotId)
                        ->first();
                    
                    if ($existing) {
                        // Update existing slot - but don't create override unless values differ from rules
                        $existing->day_type = 'working';
                        $existing->notes = null;
                        $existing->updated_at = now();
                        $existing->save();
                        
                        // Only create override if values are different from default rules
                        $defaultRule = SlotCapacityRule::where('time_slot_id', $timeSlotId)
                            ->where('day_type', 'working')
                            ->first();
                        
                        $shouldCreateOverride = false;
                        if ($defaultRule) {
                            if ($defaultRule->reg_capacity != $request->reg_capacity ||
                                $defaultRule->updating_capacity != $request->updating_capacity ||
                                $defaultRule->inquiry_capacity != $request->inquiry_capacity) {
                                $shouldCreateOverride = true;
                            }
                        } else {
                            $shouldCreateOverride = true;
                        }
                        
                        if ($shouldCreateOverride) {
                            SlotCapacityOverride::updateOrCreate(
                                [
                                    'date' => $date->format('Y-m-d'),
                                    'time_slot_id' => $timeSlotId,
                                ],
                                [
                                    'day_type' => 'working',
                                    'reason' => 'Bulk generated',
                                    'reg_capacity' => $request->reg_capacity,
                                    'updating_capacity' => $request->updating_capacity,
                                    'inquiry_capacity' => $request->inquiry_capacity,
                                    'updated_at' => now(),
                                ]
                            );
                        }
                        $updated++;
                    } else {
                        // Create new slot - but don't create override, just use rules
                        AppointmentSlot::create([
                            'date' => $date->format('Y-m-d'),
                            'time_slot_id' => $timeSlotId,
                            'day_type' => 'working',
                            'notes' => null,
                            'created_by' => auth()->id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        $created++;
                    }
                }
            }
            
            DB::commit();
            
            $message = "Generated {$created} new slots, updated {$updated} existing slots.";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} slots.";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk generate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating slots: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getSlotsJson(Request $request)
    {
        try {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
            
            $workingDaysList = [];
            $workingDaysRecords = WorkingDaysDefault::where('day_type', 'working')->get();
            foreach ($workingDaysRecords as $record) {
                $dayNumber = $this->getDayNumber($record->day_name);
                if ($dayNumber) {
                    $workingDaysList[] = $dayNumber;
                }
            }
            if (empty($workingDaysList)) {
                $workingDaysList = [1, 2, 3, 4, 5];
            }
            
            $slots = AppointmentSlot::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->with('timeSlot')
                ->get()
                ->groupBy('date');
            
            $dates = AppointmentSlot::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->pluck('date')
                ->unique();
            
            $overrides = SlotCapacityOverride::whereIn('date', $dates)
                ->get()
                ->keyBy(function($item) {
                    return $item->date . '_' . $item->time_slot_id;
                });
            
            // Get all default rules for fallback
            $defaultRules = SlotCapacityRule::where('day_type', 'working')
                ->get()
                ->keyBy('time_slot_id');
            
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
                        $defaultRule = $defaultRules->get($slot->time_slot_id);
                        
                        // Determine which capacities to use
                        if ($override) {
                            // Use override values (admin manually set this)
                            $regCapacity = $override->reg_capacity ?? 0;
                            $updatingCapacity = $override->updating_capacity ?? 0;
                            $inquiryCapacity = $override->inquiry_capacity ?? 0;
                            $dayType = $override->day_type ?? $slot->day_type;
                        } else {
                            // No override - use default rules
                            $regCapacity = $defaultRule->reg_capacity ?? 4;
                            $updatingCapacity = $defaultRule->updating_capacity ?? 4;
                            $inquiryCapacity = $defaultRule->inquiry_capacity ?? 4;
                            $dayType = $slot->day_type;
                            
                            // If it's a holiday or non_working from slot, set capacities to 0
                            if ($dayType === 'holiday' || $dayType === 'non_working') {
                                $regCapacity = 0;
                                $updatingCapacity = 0;
                                $inquiryCapacity = 0;
                            }
                        }
                        
                        $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($slot) {
                            $query->whereDate('appointment_date', $slot->date)
                                  ->where('time_slot_id', $slot->time_slot_id);
                        })
                        ->selectRaw('service, COUNT(*) as count')
                        ->groupBy('service')
                        ->pluck('count', 'service')
                        ->toArray();
                        
                        $regBooked = $bookedCounts['reg'] ?? 0;
                        $updatingBooked = $bookedCounts['updating'] ?? 0;
                        $inquiryBooked = $bookedCounts['inquiry'] ?? 0;
                        
                        $aggregated[$slot->time_slot_id] = [
                            'id' => $slot->id,
                            'time_slot_label' => $slot->timeSlot->label,
                            'reg_available' => max(0, $regCapacity - $regBooked),
                            'updating_available' => max(0, $updatingCapacity - $updatingBooked),
                            'inquiry_available' => max(0, $inquiryCapacity - $inquiryBooked),
                            'reg_capacity' => $regCapacity,
                            'updating_capacity' => $updatingCapacity,
                            'inquiry_capacity' => $inquiryCapacity,
                            'reg_booked' => $regBooked,
                            'updating_booked' => $updatingBooked,
                            'inquiry_booked' => $inquiryBooked,
                            'day_type' => $dayType,
                            'has_override' => !is_null($override),
                            'notes' => $slot->notes,
                        ];
                    }
                    
                    $result[$date] = $aggregated;
                } else {
                    $result[$date] = [];
                }
            }
            
            return response()->json([
                'slots' => $result,
                'working_days' => $workingDaysList,
                'default_rules' => $defaultRules
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getSlotsJson: ' . $e->getMessage());
            return response()->json([
                'slots' => [], 
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function toggleHoliday($id)
    {
        try {
            $slot = AppointmentSlot::findOrFail($id);
            
            DB::beginTransaction();
            
            if ($slot->day_type === 'holiday') {
                $slot->day_type = 'working';
                $slot->notes = null;
                $slot->updated_at = now();
                $slot->save();
                
                WorkingDaysOverride::where('date', $slot->date)->delete();
                
                // Remove override if exists
                SlotCapacityOverride::where('date', $slot->date)
                    ->where('time_slot_id', $slot->time_slot_id)
                    ->delete();
                
                $message = 'Slot converted back to working day.';
            } else {
                $slot->day_type = 'holiday';
                $slot->notes = 'Marked as holiday';
                $slot->updated_at = now();
                $slot->save();
                
                WorkingDaysOverride::updateOrCreate(
                    ['date' => $slot->date],
                    [
                        'day_type' => 'holiday',
                        'reason' => 'Holiday',
                        'updated_at' => now(),
                    ]
                );
                
                SlotCapacityOverride::updateOrCreate(
                    [
                        'date' => $slot->date,
                        'time_slot_id' => $slot->time_slot_id,
                    ],
                    [
                        'day_type' => 'holiday',
                        'reason' => 'Holiday - No appointments',
                        'reg_capacity' => 0,
                        'updating_capacity' => 0,
                        'inquiry_capacity' => 0,
                        'updated_at' => now(),
                    ]
                );
                
                $message = 'Slot marked as holiday.';
            }
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => $message]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling holiday: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getSlotDetails($date)
    {
        try {
            $slots = AppointmentSlot::where('date', $date)->with('timeSlot')->get();
            
            $defaultRules = SlotCapacityRule::where('day_type', 'working')
                ->get()
                ->keyBy('time_slot_id');
            
            $overrides = SlotCapacityOverride::where('date', $date)
                ->get()
                ->keyBy('time_slot_id');
            
            $slotDetails = [];
            foreach ($slots as $slot) {
                $override = $overrides->get($slot->time_slot_id);
                $defaultRule = $defaultRules->get($slot->time_slot_id);
                
                if ($override) {
                    $regCapacity = $override->reg_capacity ?? 0;
                    $updatingCapacity = $override->updating_capacity ?? 0;
                    $inquiryCapacity = $override->inquiry_capacity ?? 0;
                    $dayType = $override->day_type ?? $slot->day_type;
                } else {
                    $regCapacity = $defaultRule->reg_capacity ?? 4;
                    $updatingCapacity = $defaultRule->updating_capacity ?? 4;
                    $inquiryCapacity = $defaultRule->inquiry_capacity ?? 4;
                    $dayType = $slot->day_type;
                    
                    if ($dayType === 'holiday' || $dayType === 'non_working') {
                        $regCapacity = 0;
                        $updatingCapacity = 0;
                        $inquiryCapacity = 0;
                    }
                }
                
                $bookedCounts = AppointmentClient::whereHas('appointment', function($query) use ($slot) {
                    $query->whereDate('appointment_date', $slot->date)
                          ->where('time_slot_id', $slot->time_slot_id);
                })
                ->selectRaw('service, COUNT(*) as count')
                ->groupBy('service')
                ->pluck('count', 'service')
                ->toArray();
                
                $slotDetails[] = [
                    'id' => $slot->id,
                    'time_slot_id' => $slot->time_slot_id,
                    'time_slot_label' => $slot->timeSlot->label,
                    'day_type' => $dayType,
                    'reg_capacity' => $regCapacity,
                    'updating_capacity' => $updatingCapacity,
                    'inquiry_capacity' => $inquiryCapacity,
                    'reg_booked' => $bookedCounts['reg'] ?? 0,
                    'updating_booked' => $bookedCounts['updating'] ?? 0,
                    'inquiry_booked' => $bookedCounts['inquiry'] ?? 0,
                    'has_override' => !is_null($override),
                    'notes' => $slot->notes,
                ];
            }
            
            return response()->json([
                'success' => true,
                'date' => $date,
                'slots' => $slotDetails,
                'default_rules' => $defaultRules
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting slot details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading slot details'
            ], 500);
        }
    }
    
    public function saveCapacityRules(Request $request)
    {
        try {
            $capacities = $request->input('capacities', []);
            
            DB::beginTransaction();
            
            foreach ($capacities as $timeSlotId => $services) {
                SlotCapacityRule::updateOrCreate(
                    [
                        'time_slot_id' => $timeSlotId,
                        'day_type' => 'working',
                    ],
                    [
                        'reg_capacity' => $services['reg'] ?? 4,
                        'updating_capacity' => $services['updating'] ?? 4,
                        'inquiry_capacity' => $services['inquiry'] ?? 4,
                        'reason' => 'Default working day capacity',
                        'updated_at' => now(),
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Capacity rules saved successfully.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving capacity rules: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving capacity rules: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function checkExisting(Request $request)
    {
        try {
            $slot = AppointmentSlot::where('date', $request->date)
                ->where('time_slot_id', $request->time_slot_id)
                ->first();
            
            $exists = !is_null($slot);
            $hasOverride = false;
            $timeSlot = null;
            
            if ($exists) {
                $timeSlot = TimeSlot::find($slot->time_slot_id);
                $override = SlotCapacityOverride::where('date', $slot->date)
                    ->where('time_slot_id', $slot->time_slot_id)
                    ->first();
                $hasOverride = !is_null($override);
            }
            
            return response()->json([
                'exists' => $exists,
                'has_override' => $hasOverride,
                'time_slot_label' => $timeSlot ? $timeSlot->label : null,
                'message' => $exists ? 'A slot already exists. It will be updated with the new settings.' : null
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['exists' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    public function getDefaultCapacities(Request $request)
    {
        try {
            $timeSlotId = $request->time_slot_id;
            
            $rule = SlotCapacityRule::where('time_slot_id', $timeSlotId)
                ->where('day_type', 'working')
                ->first();
            
            return response()->json([
                'success' => true,
                'reg_capacity' => $rule->reg_capacity ?? 4,
                'updating_capacity' => $rule->updating_capacity ?? 4,
                'inquiry_capacity' => $rule->inquiry_capacity ?? 4,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
