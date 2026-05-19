<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;

class ODashboardController extends Controller
{
    public function index()
    {
        // Basic statistics
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())->count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $confirmedAppointments = Appointment::where('status', 'confirmed')->count();
        $completedAppointments = Appointment::where('status', 'completed')->count();
        $cancelledAppointments = Appointment::where('status', 'cancelled')->count();
        $totalAppointments = Appointment::count();
        $recentAppointments = Appointment::with('clients', 'timeSlot')
            ->latest()
            ->take(10)
            ->get();

        // Daily Chart Data (by time slot for today)
        $dailyChartLabels = [];
        $dailyChartData = [];
        
        // Get all active time slots ordered by display_order
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        foreach ($timeSlots as $slot) {
            $dailyChartLabels[] = $slot->label ?? date('g:i A', strtotime($slot->start_time));
            $count = Appointment::whereDate('appointment_date', Carbon::today())
                ->where('time_slot_id', $slot->id)
                ->count();
            $dailyChartData[] = $count;
        }
        
        // If no time slots configured, use default hours
        if ($timeSlots->isEmpty()) {
            $dailyChartLabels = ['9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM'];
            $dailyChartData = array_fill(0, count($dailyChartLabels), 0);
        }

        // Weekly Chart Data (last 7 days)
        $weeklyChartLabels = [];
        $weeklyChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyChartLabels[] = $date->format('D, M j');
            $count = Appointment::whereDate('appointment_date', $date)->count();
            $weeklyChartData[] = $count;
        }

        // Monthly Chart Data (current month days)
        $monthlyChartLabels = [];
        $monthlyChartData = [];
        $daysInMonth = Carbon::now()->daysInMonth;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $monthlyChartLabels[] = $i;
            $date = Carbon::now()->startOfMonth()->addDays($i - 1);
            $count = Appointment::whereDate('appointment_date', $date)->count();
            $monthlyChartData[] = $count;
        }

        // Yearly Chart Data (months)
        $yearlyChartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $yearlyChartData = [];
        foreach ($yearlyChartLabels as $index => $month) {
            $count = Appointment::whereMonth('appointment_date', $index + 1)
                ->whereYear('appointment_date', Carbon::now()->year)
                ->count();
            $yearlyChartData[] = $count;
        }

        // Service distribution data (for additional chart if needed)
        $serviceDistribution = AppointmentClient::selectRaw('service, COUNT(*) as count')
            ->groupBy('service')
            ->get()
            ->mapWithKeys(function($item) {
                $serviceNames = [
                    'reg' => 'Registration',
                    'updating' => 'Correction/Updating',
                    'inquiry' => 'Status Inquiry'
                ];
                return [$serviceNames[$item->service] ?? $item->service => $item->count];
            });

        return view('operator.dashboard', compact(
            'todayAppointments',
            'pendingAppointments',
            'confirmedAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'totalAppointments',
            'recentAppointments',
            'dailyChartLabels',
            'dailyChartData',
            'weeklyChartLabels',
            'weeklyChartData',
            'monthlyChartLabels',
            'monthlyChartData',
            'yearlyChartLabels',
            'yearlyChartData',
            'serviceDistribution'
        ));
    }
}