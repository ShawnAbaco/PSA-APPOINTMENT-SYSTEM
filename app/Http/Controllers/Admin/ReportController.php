<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to current month
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Convert to Carbon instances for query
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        // Get appointments within date range
        $appointments = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])->get();
        
        // Summary statistics
        $summary = [
            'total' => $appointments->count(),
            'pending' => $appointments->where('status', 'pending')->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
        ];
        
        // Get appointments by service
        $byService = AppointmentClient::whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->select('service', DB::raw('COUNT(*) as count'))
            ->groupBy('service')
            ->get();
        
        // Get appointments by day of week
        $byDay = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->select(DB::raw('DAYNAME(appointment_date) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->orderBy(DB::raw('FIELD(day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")'))
            ->get();
        
        // Get appointments by hour (for time analysis)
        $byHour = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->select(DB::raw('HOUR(appointment_time) as hour'), DB::raw('COUNT(*) as count'))
            ->whereNotNull('appointment_time')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        // Get top clients (most appointments)
        $topClients = AppointmentClient::select(
                'first_name', 
                'last_name', 
                DB::raw('COUNT(*) as appointment_count')
            )
            ->whereBetween('created_at', [$startDateCarbon, $endDateCarbon])
            ->groupBy('first_name', 'last_name')
            ->orderBy('appointment_count', 'desc')
            ->limit(10)
            ->get();
        
        // Get service labels and data for chart
        $serviceLabels = [];
        $serviceData = [];
        $serviceColors = [
            'reg' => '#28a745',
            'correction' => '#ffc107',
            'ephilid' => '#17a2b8',
            'trn' => '#6c757d'
        ];
        $serviceNames = [
            'reg' => 'Registration',
            'correction' => 'Correction',
            'ephilid' => 'ePhilID',
            'trn' => 'TRN Retrieval'
        ];
        
        foreach ($byService as $service) {
            $serviceLabels[] = $serviceNames[$service->service] ?? $service->service;
            $serviceData[] = $service->count;
        }
        
        // Prepare day labels and data
        $dayLabels = [];
        $dayData = [];
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($dayOrder as $day) {
            $found = $byDay->firstWhere('day', $day);
            $dayLabels[] = $day;
            $dayData[] = $found ? $found->count : 0;
        }
        
        // Get monthly trends for the last 6 months
        $monthlyTrends = Appointment::select(
                DB::raw('DATE_FORMAT(appointment_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled')
            )
            ->where('appointment_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Get staff performance
        $staffPerformance = User::where('role', 'staff')
            ->leftJoin('appointments', 'users.id', '=', 'appointments.processed_by')
            ->whereBetween('appointments.created_at', [$startDateCarbon, $endDateCarbon])
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                DB::raw('COUNT(appointments.id) as appointments_processed'),
                DB::raw('SUM(CASE WHEN appointments.status = "completed" THEN 1 ELSE 0 END) as completed_count')
            )
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->get();
        
        return view('admin.reports.index', compact(
            'summary',
            'byService',
            'byDay',
            'byHour',
            'topClients',
            'startDate',
            'endDate',
            'serviceLabels',
            'serviceData',
            'serviceColors',
            'serviceNames',
            'dayLabels',
            'dayData',
            'monthlyTrends',
            'staffPerformance'
        ));
    }
    
    /**
     * Export report to CSV
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        $appointments = Appointment::with('clients')
            ->whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->get();
        
        $filename = 'appointment_report_' . date('Y-m-d_His') . '.csv';
        
        return response()->stream(
            function() use ($appointments) {
                $handle = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($handle, [
                    'Appointment #',
                    'Date',
                    'Contact Person',
                    'Contact Mobile',
                    'Contact Email',
                    'Type',
                    'Status',
                    'Number of Clients',
                    'Clients Names',
                    'Services',
                    'Created At'
                ]);
                
                // Add data rows
                foreach ($appointments as $appointment) {
                    $clientNames = $appointment->clients->map(function($client) {
                        return $client->full_name;
                    })->implode(', ');
                    
                    $services = $appointment->clients->map(function($client) {
                        return $client->service_name;
                    })->unique()->implode(', ');
                    
                    fputcsv($handle, [
                        $appointment->appointment_number,
                        $appointment->appointment_date,
                        $appointment->contact_name,
                        $appointment->contact_mobile,
                        $appointment->contact_email,
                        $appointment->type,
                        $appointment->status,
                        $appointment->clients->count(),
                        $clientNames,
                        $services,
                        $appointment->created_at,
                    ]);
                }
                
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}