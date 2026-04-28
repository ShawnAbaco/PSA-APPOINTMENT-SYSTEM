<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\TimeSlot;
use App\Models\SlotCapacityRule;
use App\Models\SlotCapacityOverride;
use App\Models\WorkingDaysDefault;
use App\Models\WorkingDaysOverride;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // User stats
        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalOperator = User::where('role', 'operator')->count();
        $totalStaff = User::where('role', 'staff')->count();
        $activeStaff = User::where('role', 'staff')->where('is_active', true)->count();
        
        // Appointment stats
        $totalAppointments = Appointment::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $confirmedAppointments = Appointment::where('status', 'confirmed')->count();
        $completedAppointments = Appointment::where('status', 'completed')->count();
        $cancelledAppointments = Appointment::where('status', 'cancelled')->count();
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())->count();
        
        // Upcoming appointments (next 10)
        $upcomingAppointments = Appointment::with('clients', 'timeSlot')
            ->where('appointment_date', '>=', Carbon::today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('time_slot_id', 'asc')
            ->take(10)
            ->get();
        
        // Chart Data
        // Today's hourly data (based on time_slots)
        $todayLabels = [];
        $todayData = [];
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        
        foreach ($timeSlots as $slot) {
            $todayLabels[] = $slot->label ?? date('g:i A', strtotime($slot->start_time));
            $count = Appointment::whereDate('appointment_date', Carbon::today())
                ->where('time_slot_id', $slot->id)
                ->count();
            $todayData[] = $count;
        }
        
        // Yesterday's data
        $yesterdayLabels = [];
        $yesterdayData = [];
        foreach ($timeSlots as $slot) {
            $yesterdayLabels[] = $slot->label ?? date('g:i A', strtotime($slot->start_time));
            $count = Appointment::whereDate('appointment_date', Carbon::yesterday())
                ->where('time_slot_id', $slot->id)
                ->count();
            $yesterdayData[] = $count;
        }
        
        // Weekly data (by day of week)
        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyLabels[] = $date->format('D, M j');
            $count = Appointment::whereDate('appointment_date', $date)->count();
            $weeklyData[] = $count;
        }
        
        // Monthly data (by week)
        $monthlyLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];
        $monthlyData = [];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        for ($week = 1; $week <= 5; $week++) {
            $start = Carbon::create($currentYear, $currentMonth, 1)->addWeeks($week - 1);
            $end = (clone $start)->endOfWeek();
            $count = Appointment::whereBetween('appointment_date', [$start, $end])
                ->whereMonth('appointment_date', $currentMonth)
                ->count();
            $monthlyData[] = $count;
        }
        
        // Yearly data
        $yearlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $yearlyData = [];
        foreach ($yearlyLabels as $index => $month) {
            $count = Appointment::whereMonth('appointment_date', $index + 1)
                ->whereYear('appointment_date', Carbon::now()->year)
                ->count();
            $yearlyData[] = $count;
        }
        
        // Get status distribution over time for bump chart
        $statusTimeData = $this->getStatusTimeData();
        
        // Appointment Places (based on user_city from appointments table)
        $appointmentPlaces = $this->getAppointmentPlaces();
        
        // Calendar Slot Data - Get daily capacity from rules with per-service breakdown
        $calendarSlotData = $this->getCalendarSlotData();
        
        // Summary Data for chart
        $summaryStatsData = $this->getSummaryStatsData();
        
        // Location Map Data (for map visualization)
        $locationMapData = $this->getLocationMapData();
        
        return view('admin.dashboard', compact(
            'totalUsers', 'totalAdmins', 'totalOperator', 'totalStaff', 'activeStaff',
            'totalAppointments', 'pendingAppointments', 'confirmedAppointments',
            'completedAppointments', 'cancelledAppointments', 'todayAppointments',
            'upcomingAppointments', 'todayLabels', 'todayData', 'yesterdayLabels',
            'yesterdayData', 'weeklyLabels', 'weeklyData', 'monthlyLabels',
            'monthlyData', 'yearlyLabels', 'yearlyData', 'statusTimeData',
            'appointmentPlaces', 'calendarSlotData', 'locationMapData',
            'summaryStatsData'
        ));
    }
    
    /**
     * Get status distribution over time for bump chart
     */
    private function getStatusTimeData()
    {
        $statusData = [];
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $statusData[$dateKey] = [
                'pending' => Appointment::whereDate('appointment_date', $dateKey)->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereDate('appointment_date', $dateKey)->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereDate('appointment_date', $dateKey)->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereDate('appointment_date', $dateKey)->where('status', 'cancelled')->count(),
            ];
        }
        
        return $statusData;
    }
    
    /**
     * Get summary statistics for all periods
     */
    private function getSummaryStatsData()
    {
        // Get total slots count (sum of capacities across all time slots for working days)
        $timeSlots = TimeSlot::where('is_active', true)->get();
        $totalSlots = 0;
        foreach ($timeSlots as $timeSlot) {
            $rule = SlotCapacityRule::where('time_slot_id', $timeSlot->id)
                ->where('day_type', 'working')
                ->first();
            if ($rule) {
                $totalSlots += $rule->reg_capacity + $rule->updating_capacity + $rule->inquiry_capacity;
            } else {
                $totalSlots += 12; // Default total capacity per time slot (4 per service type)
            }
        }
        
        // Get total distinct locations with appointments
        $totalLocations = Appointment::whereNotNull('user_city')
            ->distinct('user_city')
            ->count('user_city');
        
        return [
            'today' => [
                'total' => Appointment::whereDate('appointment_date', Carbon::today())->count(),
                'pending' => Appointment::whereDate('appointment_date', Carbon::today())->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereDate('appointment_date', Carbon::today())->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereDate('appointment_date', Carbon::today())->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereDate('appointment_date', Carbon::today())->where('status', 'cancelled')->count(),
                'slots' => $totalSlots,
                'by_location' => Appointment::whereDate('appointment_date', Carbon::today())->whereNotNull('user_city')->distinct('user_city')->count('user_city')
            ],
            'yesterday' => [
                'total' => Appointment::whereDate('appointment_date', Carbon::yesterday())->count(),
                'pending' => Appointment::whereDate('appointment_date', Carbon::yesterday())->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereDate('appointment_date', Carbon::yesterday())->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereDate('appointment_date', Carbon::yesterday())->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereDate('appointment_date', Carbon::yesterday())->where('status', 'cancelled')->count(),
                'slots' => $totalSlots,
                'by_location' => Appointment::whereDate('appointment_date', Carbon::yesterday())->whereNotNull('user_city')->distinct('user_city')->count('user_city')
            ],
            'weekly' => [
                'total' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
                'pending' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'cancelled')->count(),
                'slots' => $totalSlots * 7, // Weekly slot capacity
                'by_location' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->whereNotNull('user_city')->distinct('user_city')->count('user_city')
            ],
            'monthly' => [
                'total' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->count(),
                'pending' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->where('status', 'cancelled')->count(),
                'slots' => $totalSlots * 30,
                'by_location' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->whereNotNull('user_city')->distinct('user_city')->count('user_city')
            ],
            'yearly' => [
                'total' => Appointment::whereYear('appointment_date', Carbon::now()->year)->count(),
                'pending' => Appointment::whereYear('appointment_date', Carbon::now()->year)->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereYear('appointment_date', Carbon::now()->year)->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereYear('appointment_date', Carbon::now()->year)->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereYear('appointment_date', Carbon::now()->year)->where('status', 'cancelled')->count(),
                'slots' => $totalSlots * 365,
                'by_location' => $totalLocations
            ],
        ];
    }
    
    /**
     * Get appointment places statistics from user_city field
     */
    private function getAppointmentPlaces($filter = 'all')
    {
        $query = Appointment::whereNotNull('user_city');
        
        switch ($filter) {
            case 'today':
                $query->whereDate('appointment_date', Carbon::today());
                break;
            case 'weekly':
                $query->whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereMonth('appointment_date', Carbon::now()->month);
                break;
        }
        
        $appointments = $query->get();
        $places = [];
        $colors = ['#0f3b6f', '#c49a2c', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec489a', '#06b6d4'];
        $total = $appointments->count();
        
        // Group by city
        $grouped = $appointments->groupBy('user_city');
        
        foreach ($grouped as $city => $items) {
            if (empty($city)) continue;
            $count = $items->count();
            $percentage = $total > 0 ? round(($count / $total) * 100) : 0;
            $places[] = [
                'name' => $city,
                'count' => $count,
                'percentage' => $percentage,
                'color' => $colors[count($places) % count($colors)]
            ];
        }
        
        // Sort by count descending
        usort($places, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        return $places;
    }
    
    /**
     * Get capacity for a specific date, time slot, and service
     * This returns the MAXIMUM number of clients that can be served for this service
     */
    private function getCapacity($date, $timeSlotId, $service)
    {
        try {
            // Check for override first
            $override = SlotCapacityOverride::where('date', $date)
                ->where('time_slot_id', $timeSlotId)
                ->first();
            
            if ($override) {
                switch ($service) {
                    case 'reg': return $override->reg_capacity ?? 0;
                    case 'updating': return $override->updating_capacity ?? 0;
                    case 'inquiry': return $override->inquiry_capacity ?? 0;
                    default: return 0;
                }
            }
            
            // Get day type
            $dayType = $this->getDayTypeForDate(Carbon::parse($date));
            
            // Get capacity rule
            $rule = SlotCapacityRule::where('time_slot_id', $timeSlotId)
                ->where('day_type', $dayType)
                ->first();
            
            if ($rule) {
                switch ($service) {
                    case 'reg': return $rule->reg_capacity;
                    case 'updating': return $rule->updating_capacity;
                    case 'inquiry': return $rule->inquiry_capacity;
                    default: return 0;
                }
            }
            
            // Default fallback - 4 per service per time slot for working days
            if ($dayType === 'working') {
                return 4;
            }
            return 0;
            
        } catch (\Exception $e) {
            Log::error('Error getting capacity: ' . $e->getMessage());
            return 4;
        }
    }
    
    /**
     * Get booked count for a specific date, time slot, and service
     * This counts the number of CLIENTS (from appointment_clients) booked for this service
     */
    private function getBookedCount($date, $timeSlotId, $service)
    {
        try {
            // Count clients directly from appointment_clients table
            // This ensures we count each person individually, not each appointment
            return AppointmentClient::where('service', $service)
                ->whereHas('appointment', function($query) use ($date, $timeSlotId) {
                    $query->where('appointment_date', $date)
                        ->where('time_slot_id', $timeSlotId)
                        ->where('status', '!=', 'cancelled');
                })
                ->count();
        } catch (\Exception $e) {
            Log::error('Error getting booked count: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get available slots for a specific date, time slot, and service
     */
    private function getAvailableSlots($date, $timeSlotId, $service)
    {
        $capacity = $this->getCapacity($date, $timeSlotId, $service);
        $booked = $this->getBookedCount($date, $timeSlotId, $service);
        return max(0, $capacity - $booked);
    }
    
    /**
     * Get calendar slot data with per-service breakdown
     */
    private function getCalendarSlotData()
    {
        $slotData = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Get all active time slots
        $timeSlots = TimeSlot::where('is_active', true)->get();
        
        // Services to track
        $services = ['reg', 'updating', 'inquiry'];
        
        // Calculate capacity and booked for each date
        $currentDate = clone $startOfMonth;
        while ($currentDate <= $endOfMonth) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayType = $this->getDayTypeForDate($currentDate);
            
            $totalCapacity = 0;
            $totalBooked = 0;
            $serviceBreakdown = [];
            
            // Initialize service breakdown
            foreach ($services as $service) {
                $serviceBreakdown[$service] = ['capacity' => 0, 'booked' => 0];
            }
            
            // Sum capacity and booked appointments from all time slots for this day
            foreach ($timeSlots as $timeSlot) {
                // Calculate capacity for each service
                foreach ($services as $service) {
                    $capacity = $this->getCapacity($dateKey, $timeSlot->id, $service);
                    $serviceBreakdown[$service]['capacity'] += $capacity;
                    $totalCapacity += $capacity;
                }
                
                // Count booked clients for this time slot on this date
                foreach ($services as $service) {
                    $booked = $this->getBookedCount($dateKey, $timeSlot->id, $service);
                    $serviceBreakdown[$service]['booked'] += $booked;
                    $totalBooked += $booked;
                }
            }
            
            $remaining = max(0, $totalCapacity - $totalBooked);
            
            $slotData[$dateKey] = [
                'total' => $totalCapacity,
                'booked' => $totalBooked,
                'remaining' => $remaining,
                'service_breakdown' => $serviceBreakdown
            ];
            
            $currentDate->addDay();
        }
        
        return $slotData;
    }
    
    /**
     * Get day type for a specific date
     * Returns: 'working', 'non_working', or 'holiday'
     */
    private function getDayTypeForDate($date)
    {
        // Step 1: Check override first (for holidays and special non-working days)
        $override = WorkingDaysOverride::where('date', $date->format('Y-m-d'))->first();
        if ($override) {
            return $override->day_type; // 'non_working' or 'holiday'
        }
        
        // Step 2: Get day name (monday, tuesday, etc.)
        $dayName = strtolower($date->format('l'));
        
        // Step 3: Check default working days configuration
        $default = WorkingDaysDefault::where('day_name', $dayName)->first();
        
        if (!$default) {
            return 'working';
        }
        
        return $default->day_type; // 'working' or 'non_working'
    }
    
    /**
     * Get location map data with coordinates
     */
    private function getLocationMapData()
    {
        $appointments = Appointment::whereNotNull('user_lat')
            ->whereNotNull('user_lng')
            ->select('user_lat', 'user_lng', 'user_city', 'appointment_number', 'status', 'appointment_date')
            ->get();
        
        return $appointments->map(function($appointment) {
            return [
                'lat' => (float)$appointment->user_lat,
                'lng' => (float)$appointment->user_lng,
                'city' => $appointment->user_city,
                'appointment_number' => $appointment->appointment_number,
                'status' => $appointment->status,
                'date' => $appointment->appointment_date ? Carbon::parse($appointment->appointment_date)->format('M d, Y') : null
            ];
        });
    }
    
    /**
     * Get appointment places via AJAX with filter
     */
    public function getAppointmentPlacesAjax(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $places = $this->getAppointmentPlaces($filter);
        return response()->json($places);
    }
    
    /**
     * Get location map data via AJAX
     */
    public function getLocationMapDataAjax(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $query = Appointment::whereNotNull('user_lat')->whereNotNull('user_lng');
        
        switch ($filter) {
            case 'today':
                $query->whereDate('appointment_date', Carbon::today());
                break;
            case 'weekly':
                $query->whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereMonth('appointment_date', Carbon::now()->month);
                break;
        }
        
        $locations = $query->select('user_lat', 'user_lng', 'user_city', 'appointment_number', 'status', 'appointment_date')->get();
        
        return response()->json($locations->map(function($location) {
            return [
                'lat' => (float)$location->user_lat,
                'lng' => (float)$location->user_lng,
                'city' => $location->user_city,
                'appointment_number' => $location->appointment_number,
                'status' => $location->status,
                'date' => $location->appointment_date ? Carbon::parse($location->appointment_date)->format('M d, Y') : null
            ];
        }));
    }
    
    /**
     * Get summary statistics for dashboard
     */
    public function getSummaryStats()
    {
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())->count();
        
        $totalAppointments = Appointment::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $confirmedAppointments = Appointment::where('status', 'confirmed')->count();
        $completedAppointments = Appointment::where('status', 'completed')->count();
        $cancelledAppointments = Appointment::where('status', 'cancelled')->count();
        
        // Get appointments by city
        $appointmentsByCity = Appointment::whereNotNull('user_city')
            ->select('user_city', DB::raw('count(*) as total'))
            ->groupBy('user_city')
            ->orderBy('total', 'desc')
            ->get();
        
        return response()->json([
            'today' => $todayAppointments,
            'total' => $totalAppointments,
            'pending' => $pendingAppointments,
            'confirmed' => $confirmedAppointments,
            'completed' => $completedAppointments,
            'cancelled' => $cancelledAppointments,
            'by_city' => $appointmentsByCity
        ]);
    }
    
    /**
     * Get refreshed calendar data via AJAX
     */
    public function getCalendarData(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $timeSlots = TimeSlot::where('is_active', true)->get();
        $services = ['reg', 'updating', 'inquiry'];
        
        $slotData = [];
        $currentDate = clone $startDate;
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayType = $this->getDayTypeForDate($currentDate);
            
            $totalCapacity = 0;
            $totalBooked = 0;
            $serviceBreakdown = [];
            
            foreach ($services as $service) {
                $serviceBreakdown[$service] = ['capacity' => 0, 'booked' => 0];
            }
            
            foreach ($timeSlots as $timeSlot) {
                foreach ($services as $service) {
                    $capacity = $this->getCapacity($dateKey, $timeSlot->id, $service);
                    $serviceBreakdown[$service]['capacity'] += $capacity;
                    $totalCapacity += $capacity;
                    
                    $booked = $this->getBookedCount($dateKey, $timeSlot->id, $service);
                    $serviceBreakdown[$service]['booked'] += $booked;
                    $totalBooked += $booked;
                }
            }
            
            $remaining = max(0, $totalCapacity - $totalBooked);
            
            $slotData[$dateKey] = [
                'total' => $totalCapacity,
                'booked' => $totalBooked,
                'remaining' => $remaining,
                'service_breakdown' => $serviceBreakdown
            ];
            
            $currentDate->addDay();
        }
        
        return response()->json([
            'success' => true,
            'slotData' => $slotData
        ]);
    }
}