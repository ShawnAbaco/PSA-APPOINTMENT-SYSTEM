<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
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
        $recentAppointments = Appointment::with('clients')->latest()->take(10)->get();

        // Daily Chart Data (hourly breakdown for today)
        $dailyChartLabels = [];
        $dailyChartData = [];
        for ($hour = 8; $hour <= 20; $hour++) {
            $dailyChartLabels[] = date('gA', mktime($hour, 0, 0));
            $count = Appointment::whereDate('appointment_date', Carbon::today())
                ->whereRaw('HOUR(appointment_time) = ?', [$hour])
                ->count();
            $dailyChartData[] = $count;
        }

        // Weekly Chart Data (last 7 days)
        $weeklyChartLabels = [];
        $weeklyChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $weeklyChartLabels[] = $date->format('D');
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

        return view('staff.dashboard', compact(
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
            'yearlyChartData'
        ));
    }
}