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
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())->count();
        $weeklyAppointments = Appointment::whereBetween('appointment_date', [
            Carbon::now()->startOfWeek(), 
            Carbon::now()->endOfWeek()
        ])->count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $confirmedAppointments = Appointment::where('status', 'confirmed')->count();
        $completedAppointments = Appointment::where('status', 'completed')->count();
        $totalAppointments = Appointment::count();
        $recentAppointments = Appointment::with('clients')->latest()->take(10)->get();
        
        return view('staff.dashboard', compact(
            'todayAppointments', 
            'weeklyAppointments', 
            'pendingAppointments',
            'confirmedAppointments',
            'completedAppointments',
            'totalAppointments',
            'recentAppointments'
        ));
    }
}