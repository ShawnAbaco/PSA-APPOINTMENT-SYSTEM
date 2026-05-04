<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\TimeSlot;
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
            
            // Get all time slots to calculate total capacity
            $timeSlots = TimeSlot::where('is_active', true)->get();
            $slotsPerDay = $timeSlots->count();
            $capacityPerSlot = 4; // max appointments per time slot
            $totalDailyCapacity = $slotsPerDay * $capacityPerSlot;
            
            foreach ($dates as $date) {
                $totalBooked = AppointmentClient::whereHas('appointment', function($query) use ($date) {
                    $query->where('appointment_date', $date);
                })->count();
                
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
            
            $availableSlots = [];
            foreach ($timeSlots as $slot) {
                $available = $this->getAvailableSlots($date, $slot->id, $service);
                if ($available > 0) {
                    $availableSlots[] = [
                        'id' => $slot->id,
                        'slot_label' => $slot->label ?? $this->formatTimeRange($slot->start_time, $slot->end_time),
                        'available_slots' => $available
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'time_slots' => $availableSlots
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
            
            // Get all clients with their service-specific details
            $clients = $appointment->clients->map(function($client) {
                // Add service-specific fields directly from the client record
                // since they're likely stored in the appointment_clients table
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
    
    // Helper methods
    private function getAvailableSlots($date, $timeSlotId, $service)
    {
        $booked = AppointmentClient::whereHas('appointment', function($query) use ($date, $timeSlotId) {
            $query->where('appointment_date', $date)
                ->where('time_slot_id', $timeSlotId);
        })->where('service', $service)->count();
        
        $capacity = 4; // Default capacity per slot
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