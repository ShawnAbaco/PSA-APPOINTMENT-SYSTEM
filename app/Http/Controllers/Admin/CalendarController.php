<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\TimeSlot;
use App\Models\SlotCapacityOverride;
use App\Models\SlotCapacityRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    // Main calendar view
    public function index()
    {
        $appointments = Appointment::with('clients', 'timeSlot')
            ->orderBy('appointment_date')
            ->get();
            
        return view('admin.calendar.index', compact('appointments'));
    }
    
    // Get appointment JSON for modal
    public function getJson($id)
    {
        try {
            $appointment = Appointment::with(['timeSlot', 'clients'])->find($id);
            
            if (!$appointment) {
                return response()->json(['success' => false, 'message' => 'Appointment not found']);
            }
            
            return response()->json([
                'success' => true,
                'appointment' => $appointment
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getJson: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
    
    // Get aggregated slot data for calendar display
    public function getSlotData(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            
            // Parse dates properly
            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->endOfDay();
            
            $dates = $this->getDateRange($startDate, $endDate);
            $slotData = [];
            
            // Get all active time slots
            $timeSlots = TimeSlot::where('is_active', true)
                ->orderBy('display_order')
                ->get();
            
            foreach ($dates as $date) {
                $totalDailyCapacity = 0;
                $totalBooked = 0;
                
                // Calculate capacity for each time slot on this date
                foreach ($timeSlots as $slot) {
                    // Get the capacity for this time slot on this specific date
                    $slotCapacities = $this->getSlotCapacitiesForDate($date, $slot->id);
                    
                    // Add to total daily capacity (sum of all services)
                    $totalDailyCapacity += $slotCapacities['reg'] + $slotCapacities['updating'] + $slotCapacities['inquiry'];
                    
                    // Get booked appointments for this time slot on this date (all services)
                    $bookedCount = AppointmentClient::whereHas('appointment', function($query) use ($date, $slot) {
                        $query->where('appointment_date', $date)
                            ->where('time_slot_id', $slot->id);
                    })->count();
                    
                    $totalBooked += $bookedCount;
                }
                
                $remaining = max(0, $totalDailyCapacity - $totalBooked);
                
                $slotData[$date] = [
                    'remaining' => $remaining,
                    'total' => $totalDailyCapacity,
                    'booked' => $totalBooked,
                    'percentage' => $totalDailyCapacity > 0 ? round(($totalBooked / $totalDailyCapacity) * 100) : 0
                ];
            }
            
            return response()->json([
                'success' => true,
                'slotData' => $slotData
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getSlotData: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error loading slot data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Get time slots by date and service for the day modal
public function getTimeSlotsByDate(Request $request)
{
    try {
        $date = $request->get('date');
        $service = $request->get('service');
        
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        $allSlots = [];
        foreach ($timeSlots as $slot) {
            // Get total capacity for this specific service on this date/time slot
            $capacities = $this->getSlotCapacitiesForDate($date, $slot->id);
            
            $totalCapacity = 0;
            switch ($service) {
                case 'reg':
                    $totalCapacity = $capacities['reg'];
                    break;
                case 'updating':
                    $totalCapacity = $capacities['updating'];
                    break;
                case 'inquiry':
                    $totalCapacity = $capacities['inquiry'];
                    break;
            }
            
            // Get booked count for this service
            $bookedCount = AppointmentClient::whereHas('appointment', function($query) use ($date, $slot) {
                $query->where('appointment_date', $date)
                    ->where('time_slot_id', $slot->id);
            })->where('service', $service)->count();
            
            $availableSlots = max(0, $totalCapacity - $bookedCount);
            
            // Calculate percentage for color coding
            $percentageAvailable = $totalCapacity > 0 ? ($availableSlots / $totalCapacity) * 100 : 0;
            
            // Determine status
            if ($totalCapacity == 0) {
                $status = 'unavailable';
            } elseif ($availableSlots == 0) {
                $status = 'full';
            } elseif ($percentageAvailable <= 33) {
                $status = 'limited'; // Orange - almost full
            } elseif ($percentageAvailable <= 66) {
                $status = 'available'; // Yellow - moderate availability
            } else {
                $status = 'plenty'; // Green - plenty available
            }
            
            $allSlots[] = [
                'id' => $slot->id,
                'slot_label' => $slot->label ?? $this->formatTimeRange($slot->start_time, $slot->end_time),
                'available_slots' => $availableSlots,
                'total_capacity' => $totalCapacity,
                'booked_count' => $bookedCount,
                'status' => $status,
                'percentage_available' => round($percentageAvailable)
            ];
        }
        
        return response()->json([
            'success' => true,
            'time_slots' => $allSlots
        ]);
    } catch (\Exception $e) {
        Log::error('Error in getTimeSlotsByDate: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error loading time slots'
        ], 500);
    }
}
    
    // Get appointments by time slot for the day modal
    public function getAppointmentsByTimeSlot(Request $request)
    {
        try {
            $date = $request->get('date');
            $timeSlotId = $request->get('time_slot_id');
            $service = $request->get('service');
            
            $appointments = Appointment::with(['clients', 'timeSlot'])
                ->where('appointment_date', $date)
                ->where('time_slot_id', $timeSlotId)
                ->whereHas('clients', function($query) use ($service) {
                    $query->where('service', $service);
                })
                ->get();
            
            return response()->json([
                'success' => true,
                'appointments' => $appointments
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAppointmentsByTimeSlot: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading appointments'
            ], 500);
        }
    }
    
    // Get full appointment details with clients for the detailed modal
    public function getFullDetails($id)
    {
        try {
            $appointment = Appointment::with(['timeSlot', 'clients'])->find($id);
            
            if (!$appointment) {
                return response()->json(['success' => false, 'message' => 'Appointment not found']);
            }
            
            $clients = $appointment->clients->map(function($client) {
                return $client;
            });
            
            return response()->json([
                'success' => true,
                'appointment' => $appointment,
                'clients' => $clients
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getFullDetails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading appointment details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getStats()
    {
        try {
            $total = Appointment::count();
            $pending = Appointment::where('status', 'pending')->count();
            $confirmed = Appointment::where('status', 'confirmed')->count();
            $completed = Appointment::where('status', 'completed')->count();
            
            $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;
            
            return response()->json([
                'success' => true,
                'total' => $total,
                'pending' => $pending,
                'confirmed' => $confirmed,
                'completionRate' => $completionRate . '%'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getStats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading stats'
            ], 500);
        }
    }
    
    // Helper method to get capacities for a specific time slot on a specific date
    // Helper method to get capacities for a specific time slot on a specific date
private function getSlotCapacitiesForDate($date, $timeSlotId)
{
    // First, check if there's a specific override for this date and time slot
    $override = SlotCapacityOverride::where('date', $date)
        ->where('time_slot_id', $timeSlotId)
        ->first();
    
    if ($override) {
        // Use override capacities from database
        return [
            'reg' => $override->reg_capacity ?? 0,  // Null means 0 capacity for this service
            'updating' => $override->updating_capacity ?? 0,
            'inquiry' => $override->inquiry_capacity ?? 0
        ];
    }
    
    // If no override, determine the day type
    $carbonDate = Carbon::parse($date);
    $dayOfWeek = $carbonDate->dayOfWeek;
    $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 0);
    $dayType = $isWeekend ? 'non_working' : 'working';
    
    // Get the rule from database for this time slot and day type
    $rule = SlotCapacityRule::where('time_slot_id', $timeSlotId)
        ->where('day_type', $dayType)
        ->first();
    
    if ($rule) {
        // Use rule capacities from database (these have defaults of 4 in migration)
        return [
            'reg' => $rule->reg_capacity,
            'updating' => $rule->updating_capacity,
            'inquiry' => $rule->inquiry_capacity
        ];
    }
    
    // Only as LAST RESORT - if no rule exists in database, use fallback
    // But ideally you should have rules for all time slots in your database
    return [
        'reg' => 4,
        'updating' => 4,
        'inquiry' => 4
    ];
}
    
    // Helper method to get available slots for a specific service on a specific date and time slot
    private function getAvailableSlotsForService($date, $timeSlotId, $service)
    {
        // Get the capacity for this service on this date and time slot
        $capacities = $this->getSlotCapacitiesForDate($date, $timeSlotId);
        
        $capacity = 0;
        switch ($service) {
            case 'reg':
                $capacity = $capacities['reg'];
                break;
            case 'updating':
                $capacity = $capacities['updating'];
                break;
            case 'inquiry':
                $capacity = $capacities['inquiry'];
                break;
            default:
                $capacity = 0;
        }
        
        // If capacity is 0, no slots available
        if ($capacity <= 0) {
            return 0;
        }
        
        // Get booked count for this specific service on this date and time slot
        $booked = AppointmentClient::whereHas('appointment', function($query) use ($date, $timeSlotId) {
            $query->where('appointment_date', $date)
                ->where('time_slot_id', $timeSlotId);
        })->where('service', $service)->count();
        
        return max(0, $capacity - $booked);
    }
    
    private function formatTimeRange($startTime, $endTime)
    {
        try {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            return $start->format('g:i A') . ' - ' . $end->format('g:i A');
        } catch (\Exception $e) {
            return $startTime . ' - ' . $endTime;
        }
    }
    
    private function getDateRange($start, $end)
    {
        $dates = [];
        $current = clone $start;
        $endDate = clone $end;
        
        while ($current <= $endDate) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }
        
        return $dates;
    }
}