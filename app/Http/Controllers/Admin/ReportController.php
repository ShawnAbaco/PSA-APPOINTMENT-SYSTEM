<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        
        $appointments = Appointment::whereBetween('appointment_date', [$startDate, $endDate])->get();
        
        $summary = [
            'total' => $appointments->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
        ];
        
        $byService = AppointmentClient::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('service, COUNT(*) as count')
            ->groupBy('service')
            ->get();
            
        $byDay = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->selectRaw('DAYNAME(appointment_date) as day, COUNT(*) as count')
            ->groupBy('day')
            ->get();
            
        return view('admin.reports.index', compact('summary', 'byService', 'byDay', 'startDate', 'endDate'));
    }
}