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

class DashboardController extends Controller
{
    public function index()
    {
        // User stats
        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
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
        
        // Calendar Slot Data - Get daily capacity from rules
        $calendarSlotData = $this->getCalendarSlotData();
        
        // Summary Data for chart
        $summaryStatsData = $this->getSummaryStatsData();
        
        // Location Map Data (for map visualization)
        $locationMapData = $this->getLocationMapData();
        
        return view('admin.dashboard', compact(
            'totalUsers', 'totalAdmins', 'totalStaff', 'activeStaff',
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
        return [
            'today' => [
                'total' => Appointment::whereDate('appointment_date', Carbon::today())->count(),
                'pending' => Appointment::whereDate('appointment_date', Carbon::today())->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereDate('appointment_date', Carbon::today())->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereDate('appointment_date', Carbon::today())->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereDate('appointment_date', Carbon::today())->where('status', 'cancelled')->count(),
            ],
            'yesterday' => [
                'total' => Appointment::whereDate('appointment_date', Carbon::yesterday())->count(),
                'pending' => Appointment::whereDate('appointment_date', Carbon::yesterday())->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereDate('appointment_date', Carbon::yesterday())->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereDate('appointment_date', Carbon::yesterday())->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereDate('appointment_date', Carbon::yesterday())->where('status', 'cancelled')->count(),
            ],
            'weekly' => [
                'total' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
                'pending' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('status', 'cancelled')->count(),
            ],
            'monthly' => [
                'total' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->count(),
                'pending' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereMonth('appointment_date', Carbon::now()->month)->where('status', 'cancelled')->count(),
            ],
            'yearly' => [
                'total' => Appointment::whereYear('appointment_date', Carbon::now()->year)->count(),
                'pending' => Appointment::whereYear('appointment_date', Carbon::now()->year)->where('status', 'pending')->count(),
                'confirmed' => Appointment::whereYear('appointment_date', Carbon::now()->year)->where('status', 'confirmed')->count(),
                'completed' => Appointment::whereYear('appointment_date', Carbon::now()->year)->where('status', 'completed')->count(),
                'cancelled' => Appointment::whereYear('appointment_date', Carbon::now()->year)->where('status', 'cancelled')->count(),
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
     * Get calendar slot data - Calculate daily capacity from rules
     */
    private function getCalendarSlotData()
    {
        $slotData = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Get all time slots
        $timeSlots = TimeSlot::where('is_active', true)->get();
        
        // Get appointments grouped by date
        $appointmentsByDate = Appointment::whereBetween('appointment_date', [$startOfMonth, $endOfMonth])
            ->select('appointment_date', DB::raw('count(*) as total'))
            ->groupBy('appointment_date')
            ->get()
            ->keyBy('appointment_date');
        
        // Calculate capacity for each date based on rules
        $currentDate = clone $startOfMonth;
        while ($currentDate <= $endOfMonth) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayType = $this->getDayTypeForDate($currentDate);
            
            $totalCapacity = 0;
            
            // Sum capacity from all time slots for this day type
            foreach ($timeSlots as $timeSlot) {
                $rule = SlotCapacityRule::where('time_slot_id', $timeSlot->id)
                    ->where('day_type', $dayType)
                    ->first();
                
                if ($rule) {
                    // Sum all service capacities for total daily capacity
                    $totalCapacity += $rule->reg_capacity + $rule->updating_capacity + $rule->inquiry_capacity;
                }
            }
            
            $booked = isset($appointmentsByDate[$dateKey]) ? $appointmentsByDate[$dateKey]->total : 0;
            
            $slotData[$dateKey] = [
                'total' => $totalCapacity,
                'booked' => $booked
            ];
            
            $currentDate->addDay();
        }
        
        return $slotData;
    }
    
    /**
     * Get day type for a specific date
     */
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
            'total' => $totalAppointments,
            'pending' => $pendingAppointments,
            'confirmed' => $confirmedAppointments,
            'completed' => $completedAppointments,
            'cancelled' => $cancelledAppointments,
            'by_city' => $appointmentsByCity
        ]);
    }
}