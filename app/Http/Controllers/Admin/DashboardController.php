<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\TimeSlot;
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
        $upcomingAppointments = Appointment::with('clients')
            ->where('appointment_date', '>=', Carbon::today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->take(10)
            ->get();
        
        // Chart Data
        // Today's hourly data
        $todayLabels = [];
        $todayData = [];
        for ($hour = 8; $hour <= 20; $hour++) {
            $todayLabels[] = date('gA', mktime($hour, 0, 0));
            $count = Appointment::whereDate('appointment_date', Carbon::today())
                ->whereRaw('HOUR(appointment_time) = ?', [$hour])
                ->count();
            $todayData[] = $count;
        }
        
        // Yesterday's hourly data
        $yesterdayLabels = [];
        $yesterdayData = [];
        for ($hour = 8; $hour <= 20; $hour++) {
            $yesterdayLabels[] = date('gA', mktime($hour, 0, 0));
            $count = Appointment::whereDate('appointment_date', Carbon::yesterday())
                ->whereRaw('HOUR(appointment_time) = ?', [$hour])
                ->count();
            $yesterdayData[] = $count;
        }
        
        // Weekly data
        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyLabels[] = $date->format('D');
            $count = Appointment::whereDate('appointment_date', $date)->count();
            $weeklyData[] = $count;
        }
        
        // Monthly data
        $monthlyLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $monthlyData = [];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        for ($week = 1; $week <= 4; $week++) {
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
        
        // Status distribution
        $statusData = [
            'pending' => $pendingAppointments,
            'confirmed' => $confirmedAppointments,
            'completed' => $completedAppointments,
            'cancelled' => $cancelledAppointments,
        ];
        
        // Appointment Places (based on user_city from appointments table)
        $appointmentPlaces = $this->getAppointmentPlaces();
        
        // Calendar Slot Data
        $calendarSlotData = $this->getCalendarSlotData();
        
        // Summary Data for chart
        $summaryData = [
            ['label' => 'Total', 'value' => $totalAppointments],
            ['label' => 'Pending', 'value' => $pendingAppointments],
            ['label' => 'Confirmed', 'value' => $confirmedAppointments],
            ['label' => 'Completed', 'value' => $completedAppointments],
            ['label' => 'Cancelled', 'value' => $cancelledAppointments],
            ['label' => 'Total Users', 'value' => $totalUsers],
            ['label' => 'Staff Users', 'value' => $totalStaff],
        ];

        $summaryStatsData = [
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

        
        // Location Map Data (for map visualization)
        $locationMapData = $this->getLocationMapData();
        
        return view('admin.dashboard', compact(
            'totalUsers', 'totalAdmins', 'totalStaff', 'activeStaff',
            'totalAppointments', 'pendingAppointments', 'confirmedAppointments',
            'completedAppointments', 'cancelledAppointments', 'todayAppointments',
            'upcomingAppointments', 'todayLabels', 'todayData', 'yesterdayLabels',
            'yesterdayData', 'weeklyLabels', 'weeklyData', 'monthlyLabels',
            'monthlyData', 'yearlyLabels', 'yearlyData', 'statusData',
            'appointmentPlaces', 'calendarSlotData', 'summaryData', 'locationMapData',
            'summaryStatsData'
        ));
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
                'date' => $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y') : null
            ];
        });
    }
    
    /**
     * Get calendar slot data for the current month
     */
    private function getCalendarSlotData()
    {
        $slotData = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Get appointments grouped by date
        $appointments = Appointment::whereBetween('appointment_date', [$startOfMonth, $endOfMonth])
            ->select('appointment_date', DB::raw('count(*) as total'))
            ->groupBy('appointment_date')
            ->get();
        
        // Default slots per day (can be configured)
        $defaultSlotsPerDay = 20;
        
        foreach ($appointments as $appointment) {
            $dateKey = $appointment->appointment_date->format('Y-m-d');
            $slotData[$dateKey] = [
                'total' => $defaultSlotsPerDay,
                'booked' => $appointment->total
            ];
        }
        
        // Fill in dates with no appointments
        $currentDate = clone $startOfMonth;
        while ($currentDate <= $endOfMonth) {
            $dateKey = $currentDate->format('Y-m-d');
            if (!isset($slotData[$dateKey])) {
                $slotData[$dateKey] = [
                    'total' => $defaultSlotsPerDay,
                    'booked' => 0
                ];
            }
            $currentDate->addDay();
        }
        
        return $slotData;
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
                'date' => $location->appointment_date ? $location->appointment_date->format('M d, Y') : null
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