<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;

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
        
        // Recent appointments
        $recentAppointments = Appointment::with('clients')->latest()->take(5)->get();
        
        return view('admin.dashboard', compact(
            'totalUsers', 
            'totalAdmins', 
            'totalStaff', 
            'activeStaff',
            'totalAppointments', 
            'pendingAppointments',
            'confirmedAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'todayAppointments',
            'recentAppointments'
        ));
    }
}