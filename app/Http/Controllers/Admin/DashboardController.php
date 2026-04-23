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
        // ========== REAL DATA FROM DATABASE ==========
        
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
        $todayAppointments = Appointment::today()->count();
        
        // Upcoming appointments (next 10)
        $upcomingAppointments = Appointment::with('clients', 'timeSlot')
            ->where('appointment_date', '>=', Carbon::today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('time_slot_id', 'asc')
            ->take(10)
            ->get();
        
        // Get time slots
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('start_time')
            ->get();
        
        // Today's hourly data
        $todayLabels = [];
        $todayData = [];
        
        foreach ($timeSlots as $slot) {
            $todayLabels[] = $slot->label ?? Carbon::parse($slot->start_time)->format('g:i A');
            $count = Appointment::whereDate('appointment_date', Carbon::today())
                ->where('time_slot_id', $slot->id)
                ->whereIn('status', ['pending', 'confirmed', 'completed'])
                ->count();
            $todayData[] = $count;
        }
        
        // Yesterday's data
        $yesterdayLabels = [];
        $yesterdayData = [];
        foreach ($timeSlots as $slot) {
            $yesterdayLabels[] = $slot->label ?? Carbon::parse($slot->start_time)->format('g:i A');
            $count = Appointment::whereDate('appointment_date', Carbon::yesterday())
                ->where('time_slot_id', $slot->id)
                ->whereIn('status', ['pending', 'confirmed', 'completed'])
                ->count();
            $yesterdayData[] = $count;
        }
        
        // Weekly data (last 7 days)
        $weeklyLabels = [];
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyLabels[] = $date->format('D, M j');
            $count = Appointment::whereDate('appointment_date', $date)
                ->whereIn('status', ['pending', 'confirmed', 'completed'])
                ->count();
            $weeklyData[] = $count;
        }
        
        // Monthly data (by week)
        $monthlyLabels = [];
        $monthlyData = [];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $firstDayOfMonth = Carbon::create($currentYear, $currentMonth, 1);
        $lastDayOfMonth = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
        $weeks = ceil(($firstDayOfMonth->dayOfWeek + $lastDayOfMonth->day) / 7);
        
        for ($week = 1; $week <= $weeks; $week++) {
            $monthlyLabels[] = "Week $week";
            $start = Carbon::create($currentYear, $currentMonth, 1)->addWeeks($week - 1);
            $end = (clone $start)->endOfWeek();
            $count = Appointment::whereBetween('appointment_date', [$start, $end])
                ->whereMonth('appointment_date', $currentMonth)
                ->whereYear('appointment_date', $currentYear)
                ->whereIn('status', ['pending', 'confirmed', 'completed'])
                ->count();
            $monthlyData[] = $count;
        }
        
        // Yearly data
        $yearlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $yearlyData = [];
        foreach ($yearlyLabels as $index => $month) {
            $count = Appointment::whereMonth('appointment_date', $index + 1)
                ->whereYear('appointment_date', Carbon::now()->year)
                ->whereIn('status', ['pending', 'confirmed', 'completed'])
                ->count();
            $yearlyData[] = $count;
        }
        
        // Status distribution over time (last 30 days)
        $statusTimeData = $this->getStatusTimeData();
        
        // Appointment places data
        $appointmentPlaces = $this->getAppointmentPlaces('all');
        
        // Calendar slot data with capacity (REAL-TIME with service breakdown)
        $calendarSlotData = $this->getCalendarSlotData();
        
        // Summary statistics
        $summaryStatsData = $this->getSummaryStatsData();
        
        // Location map data
        $locationMapData = $this->getLocationMapData();
        
        return view('admin.dashboard', compact(
            'totalUsers', 'totalAdmins', 'totalStaff', 'activeStaff',
            'totalAppointments', 'pendingAppointments', 'confirmedAppointments',
            'completedAppointments', 'cancelledAppointments', 'todayAppointments',
            'upcomingAppointments', 'todayLabels', 'todayData', 'yesterdayLabels',
            'yesterdayData', 'weeklyLabels', 'weeklyData', 'monthlyLabels',
            'monthlyData', 'yearlyLabels', 'yearlyData', 'statusTimeData',
            'appointmentPlaces', 'calendarSlotData', 'locationMapData',
            'summaryStatsData', 'timeSlots'
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
        
        // Get all appointments in date range
        $appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->select('appointment_date', 'status', DB::raw('count(*) as total'))
            ->groupBy('appointment_date', 'status')
            ->get();
        
        // Initialize all dates
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $statusData[$dateKey] = [
                'pending' => 0,
                'confirmed' => 0,
                'completed' => 0,
                'cancelled' => 0,
            ];
        }
        
        // Fill with actual data
        foreach ($appointments as $appointment) {
            $dateKey = $appointment->appointment_date instanceof Carbon 
                ? $appointment->appointment_date->format('Y-m-d')
                : $appointment->appointment_date;
            
            if (isset($statusData[$dateKey])) {
                $statusData[$dateKey][$appointment->status] = $appointment->total;
            }
        }
        
        return $statusData;
    }
    
    /**
     * Get summary statistics for all periods
     */
    private function getSummaryStatsData()
    {
        // Get total unique locations for each period
        $todayLocations = Appointment::today()
            ->whereNotNull('user_city')
            ->where('user_city', '!=', '')
            ->distinct('user_city')
            ->count('user_city');
        
        $yesterdayLocations = Appointment::whereDate('appointment_date', Carbon::yesterday())
            ->whereNotNull('user_city')
            ->where('user_city', '!=', '')
            ->distinct('user_city')
            ->count('user_city');
        
        $weeklyLocations = Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->whereNotNull('user_city')
            ->where('user_city', '!=', '')
            ->distinct('user_city')
            ->count('user_city');
        
        $monthlyLocations = Appointment::whereMonth('appointment_date', Carbon::now()->month)
            ->whereYear('appointment_date', Carbon::now()->year)
            ->whereNotNull('user_city')
            ->where('user_city', '!=', '')
            ->distinct('user_city')
            ->count('user_city');
        
        $yearlyLocations = Appointment::whereYear('appointment_date', Carbon::now()->year)
            ->whereNotNull('user_city')
            ->where('user_city', '!=', '')
            ->distinct('user_city')
            ->count('user_city');
        
        return [
            'today' => [
                'total' => Appointment::today()->count(),
                'slots' => $this->getTotalSlotsForPeriod(Carbon::today(), Carbon::today()),
                'by_location' => $todayLocations,
            ],
            'yesterday' => [
                'total' => Appointment::whereDate('appointment_date', Carbon::yesterday())->count(),
                'slots' => $this->getTotalSlotsForPeriod(Carbon::yesterday(), Carbon::yesterday()),
                'by_location' => $yesterdayLocations,
            ],
            'weekly' => [
                'total' => Appointment::whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
                'slots' => $this->getTotalSlotsForPeriod(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()),
                'by_location' => $weeklyLocations,
            ],
            'monthly' => [
                'total' => Appointment::whereMonth('appointment_date', Carbon::now()->month)
                    ->whereYear('appointment_date', Carbon::now()->year)
                    ->count(),
                'slots' => $this->getTotalSlotsForPeriod(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()),
                'by_location' => $monthlyLocations,
            ],
            'yearly' => [
                'total' => Appointment::whereYear('appointment_date', Carbon::now()->year)->count(),
                'slots' => $this->getTotalSlotsForPeriod(Carbon::now()->startOfYear(), Carbon::now()->endOfYear()),
                'by_location' => $yearlyLocations,
            ],
        ];
    }
    
    /**
     * Get appointment places statistics
     */
    private function getAppointmentPlaces($filter = 'all')
    {
        $query = Appointment::whereNotNull('user_city')->where('user_city', '!=', '');
        
        switch ($filter) {
            case 'today':
                $query->today();
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
        
        if ($total == 0) {
            return [];
        }
        
        // Group by city
        $grouped = $appointments->groupBy('user_city');
        
        foreach ($grouped as $city => $items) {
            if (empty($city)) continue;
            $count = $items->count();
            $percentage = round(($count / $total) * 100);
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
        
        return array_slice($places, 0, 8);
    }
    
    /**
     * Get calendar slot data with capacity breakdown by service type
     */
    private function getCalendarSlotData()
    {
        $slotData = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Get all active time slots
        $timeSlots = TimeSlot::where('is_active', true)->get();
        
        // Get appointments grouped by date and service
        $appointmentsByDateAndService = AppointmentClient::whereHas('appointment', function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('appointment_date', [$startOfMonth, $endOfMonth])
                      ->whereIn('status', ['pending', 'confirmed', 'completed']);
            })
            ->select('appointment_date', 'service', DB::raw('count(*) as total'))
            ->join('appointments', 'appointment_clients.appointment_id', '=', 'appointments.id')
            ->groupBy('appointment_date', 'service')
            ->get()
            ->groupBy(function($item) {
                return $item->appointment_date instanceof Carbon 
                    ? $item->appointment_date->format('Y-m-d')
                    : $item->appointment_date;
            });
        
        // Calculate capacity for each date
        $currentDate = clone $startOfMonth;
        while ($currentDate <= $endOfMonth) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayType = $this->getDayTypeForDate($currentDate);
            
            $capacities = [
                'reg' => ['total' => 0, 'booked' => 0],
                'updating' => ['total' => 0, 'booked' => 0],
                'inquiry' => ['total' => 0, 'booked' => 0],
            ];
            
            // Calculate capacity for each time slot and service
            foreach ($timeSlots as $timeSlot) {
                // Check for override
                $override = SlotCapacityOverride::where('date', $dateKey)
                    ->where('time_slot_id', $timeSlot->id)
                    ->first();
                
                if ($override) {
                    $capacities['reg']['total'] += $override->reg_capacity ?? 0;
                    $capacities['updating']['total'] += $override->updating_capacity ?? 0;
                    $capacities['inquiry']['total'] += $override->inquiry_capacity ?? 0;
                } else {
                    $rule = SlotCapacityRule::where('time_slot_id', $timeSlot->id)
                        ->where('day_type', $dayType)
                        ->first();
                    
                    if ($rule) {
                        $capacities['reg']['total'] += $rule->reg_capacity;
                        $capacities['updating']['total'] += $rule->updating_capacity;
                        $capacities['inquiry']['total'] += $rule->inquiry_capacity;
                    } else {
                        // Default capacity if no rule exists
                        $capacities['reg']['total'] += 4;
                        $capacities['updating']['total'] += 2;
                        $capacities['inquiry']['total'] += 2;
                    }
                }
            }
            
            // Get booked counts for this date by service
            if (isset($appointmentsByDateAndService[$dateKey])) {
                foreach ($appointmentsByDateAndService[$dateKey] as $booking) {
                    $service = $booking->service;
                    if (isset($capacities[$service])) {
                        $capacities[$service]['booked'] = $booking->total;
                    }
                }
            }
            
            // Calculate remaining slots
            $slotData[$dateKey] = [
                'total' => array_sum(array_column($capacities, 'total')),
                'booked' => array_sum(array_column($capacities, 'booked')),
                'remaining' => array_sum(array_column($capacities, 'total')) - array_sum(array_column($capacities, 'booked')),
                'services' => [
                    'reg' => [
                        'total' => $capacities['reg']['total'],
                        'booked' => $capacities['reg']['booked'],
                        'remaining' => $capacities['reg']['total'] - $capacities['reg']['booked'],
                        'name' => 'Registration'
                    ],
                    'updating' => [
                        'total' => $capacities['updating']['total'],
                        'booked' => $capacities['updating']['booked'],
                        'remaining' => $capacities['updating']['total'] - $capacities['updating']['booked'],
                        'name' => 'Correction/Updating'
                    ],
                    'inquiry' => [
                        'total' => $capacities['inquiry']['total'],
                        'booked' => $capacities['inquiry']['booked'],
                        'remaining' => $capacities['inquiry']['total'] - $capacities['inquiry']['booked'],
                        'name' => 'Status Inquiry'
                    ],
                ]
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
        
        // Check default working days
        $dayOfWeek = $date->dayOfWeek;
        $default = WorkingDaysDefault::where('day_of_week', $dayOfWeek)->first();
        
        if (!$default || !$default->is_working) {
            if ($dayOfWeek == 6) return 'saturday';
            if ($dayOfWeek == 0) return 'sunday';
            return 'holiday';
        }
        
        return 'weekday';
    }
    
    /**
     * Get location map data
     */
    private function getLocationMapData()
    {
        $appointments = Appointment::whereNotNull('user_lat')
            ->whereNotNull('user_lng')
            ->where('user_lat', '!=', 0)
            ->where('user_lng', '!=', 0)
            ->select('user_lat', 'user_lng', 'user_city', 'appointment_number', 'status', 'appointment_date')
            ->limit(100)
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
     * Get total slots for a date range
     */
    private function getTotalSlotsForPeriod($startDate, $endDate)
    {
        $totalSlots = 0;
        $currentDate = clone $startDate;
        $timeSlots = TimeSlot::where('is_active', true)->get();
        
        while ($currentDate <= $endDate) {
            $dayType = $this->getDayTypeForDate($currentDate);
            $dateKey = $currentDate->format('Y-m-d');
            
            foreach ($timeSlots as $timeSlot) {
                // Check for override
                $override = SlotCapacityOverride::where('date', $dateKey)
                    ->where('time_slot_id', $timeSlot->id)
                    ->first();
                
                if ($override) {
                    $totalSlots += ($override->reg_capacity ?? 0) + 
                                   ($override->updating_capacity ?? 0) + 
                                   ($override->inquiry_capacity ?? 0);
                } else {
                    $rule = SlotCapacityRule::where('time_slot_id', $timeSlot->id)
                        ->where('day_type', $dayType)
                        ->first();
                    
                    if ($rule) {
                        $totalSlots += $rule->reg_capacity + $rule->updating_capacity + $rule->inquiry_capacity;
                    } else {
                        // Default capacity if no rule exists
                        $totalSlots += 8; // 4+2+2 default
                    }
                }
            }
            
            $currentDate->addDay();
        }
        
        return $totalSlots > 0 ? $totalSlots : 20;
    }
    
    // ========== AJAX METHODS FOR REAL-TIME UPDATES ==========
    
    /**
     * Get appointment places via AJAX
     */
    public function getAppointmentPlacesAjax(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $places = $this->getAppointmentPlaces($filter);
        return response()->json($places);
    }
    
    /**
     * Get real-time calendar slot data via AJAX
     */
    public function getCalendarSlotDataAjax(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Get all active time slots
        $timeSlots = TimeSlot::where('is_active', true)->get();
        
        // Get appointments grouped by date and service for the requested month
        $appointmentsByDateAndService = AppointmentClient::whereHas('appointment', function($query) use ($startDate, $endDate) {
                $query->whereBetween('appointment_date', [$startDate, $endDate])
                      ->whereIn('status', ['pending', 'confirmed', 'completed']);
            })
            ->select('appointment_date', 'service', DB::raw('count(*) as total'))
            ->join('appointments', 'appointment_clients.appointment_id', '=', 'appointments.id')
            ->groupBy('appointment_date', 'service')
            ->get()
            ->groupBy(function($item) {
                return $item->appointment_date instanceof Carbon 
                    ? $item->appointment_date->format('Y-m-d')
                    : $item->appointment_date;
            });
        
        $slotData = [];
        $currentDate = clone $startDate;
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayType = $this->getDayTypeForDate($currentDate);
            $isPastDate = $currentDate < Carbon::now()->startOfDay();
            $isWeekend = in_array($currentDate->dayOfWeek, [0, 6]);
            
            $capacities = [
                'reg' => ['total' => 0, 'booked' => 0, 'name' => 'Registration'],
                'updating' => ['total' => 0, 'booked' => 0, 'name' => 'Correction/Updating'],
                'inquiry' => ['total' => 0, 'booked' => 0, 'name' => 'Status Inquiry'],
            ];
            
            // Only calculate capacity for non-past dates
            if (!$isPastDate) {
                foreach ($timeSlots as $timeSlot) {
                    // Check for override
                    $override = SlotCapacityOverride::where('date', $dateKey)
                        ->where('time_slot_id', $timeSlot->id)
                        ->first();
                    
                    if ($override) {
                        $capacities['reg']['total'] += $override->reg_capacity ?? 0;
                        $capacities['updating']['total'] += $override->updating_capacity ?? 0;
                        $capacities['inquiry']['total'] += $override->inquiry_capacity ?? 0;
                    } else {
                        $rule = SlotCapacityRule::where('time_slot_id', $timeSlot->id)
                            ->where('day_type', $dayType)
                            ->first();
                        
                        if ($rule) {
                            $capacities['reg']['total'] += $rule->reg_capacity;
                            $capacities['updating']['total'] += $rule->updating_capacity;
                            $capacities['inquiry']['total'] += $rule->inquiry_capacity;
                        }
                    }
                }
            }
            
            // Get booked counts for this date by service
            if (isset($appointmentsByDateAndService[$dateKey])) {
                foreach ($appointmentsByDateAndService[$dateKey] as $booking) {
                    $service = $booking->service;
                    if (isset($capacities[$service])) {
                        $capacities[$service]['booked'] = $booking->total;
                    }
                }
            }
            
            // Calculate status based on overall availability
            $totalRemaining = 0;
            $serviceStatuses = [];
            
            foreach ($capacities as $key => $service) {
                $remaining = $service['total'] - $service['booked'];
                $totalRemaining += $remaining;
                $serviceStatuses[$key] = [
                    'remaining' => max(0, $remaining),
                    'total' => $service['total'],
                    'booked' => $service['booked'],
                    'name' => $service['name']
                ];
            }
            
            // Determine overall status
            if ($isPastDate) {
                $status = 'past';
            } elseif ($isWeekend) {
                $status = 'weekend';
            } elseif ($totalRemaining <= 0) {
                $status = 'full';
            } elseif ($totalRemaining < 10) {
                $status = 'partial';
            } else {
                $status = 'available';
            }
            
            $slotData[$dateKey] = [
                'status' => $status,
                'total_remaining' => max(0, $totalRemaining),
                'services' => $serviceStatuses,
                'is_past' => $isPastDate,
                'is_weekend' => $isWeekend,
                'day' => $currentDate->format('l')
            ];
            
            $currentDate->addDay();
        }
        
        return response()->json([
            'success' => true,
            'slot_data' => $slotData,
            'year' => $year,
            'month' => $month
        ]);
    }
    
    /**
     * Get location map data via AJAX
     */
    public function getLocationMapDataAjax(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $query = Appointment::whereNotNull('user_lat')
            ->whereNotNull('user_lng')
            ->where('user_lat', '!=', 0)
            ->where('user_lng', '!=', 0);
        
        switch ($filter) {
            case 'today':
                $query->today();
                break;
            case 'weekly':
                $query->whereBetween('appointment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereMonth('appointment_date', Carbon::now()->month);
                break;
        }
        
        $locations = $query->select('user_lat', 'user_lng', 'user_city', 'appointment_number', 'status', 'appointment_date')
            ->limit(100)
            ->get();
        
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
     * Get summary statistics via AJAX
     */
    public function getSummaryStatsAjax(Request $request)
    {
        $todayAppointments = Appointment::today()->count();
        
        return response()->json([
            'total' => Appointment::count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
            'today' => $todayAppointments,
            'by_city' => Appointment::whereNotNull('user_city')
                ->select('user_city', DB::raw('count(*) as total'))
                ->groupBy('user_city')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
        ]);
    }
}