<?php
// app/Http/Controllers/Admin/ReportController.php

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

    const PSA_LAT = 8.4815315;
    const PSA_LNG = 124.6549067;

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
        
        // ========== NEW: LOCATION-BASED ANALYTICS ==========
        
        // 1. City Summary with status breakdown
        $citySummary = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereNotNull('user_city')
            ->select(
                'user_city',
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show")
            )
            ->groupBy('user_city')
            ->orderBy('total_bookings', 'desc')
            ->get();
        
        // Calculate trend for each city
        $previousStart = Carbon::parse($startDate)->subDays(30)->startOfDay();
        $previousEnd = Carbon::parse($endDate)->subDays(30)->endOfDay();
        
        foreach ($citySummary as $cityItem) {
            $previousCount = Appointment::where('user_city', $cityItem->user_city)
                ->whereBetween('appointment_date', [$previousStart, $previousEnd])
                ->count();
            
            if ($previousCount > 0) {
                $cityItem->trend = round((($cityItem->total_bookings - $previousCount) / $previousCount) * 100, 1);
            } else {
                $cityItem->trend = $cityItem->total_bookings > 0 ? 100 : 0;
            }
        }
        
        // 2. Statistics cards for locations
        $uniqueLocations = $citySummary->count();
        $topCity = $citySummary->first()->user_city ?? 'N/A';
        $topCityCount = $citySummary->first()->total_bookings ?? 0;
        $completionRate = $summary['total'] > 0 ? round(($summary['completed'] / $summary['total']) * 100, 1) : 0;
        
        // 3. Location data for map
        $bookingLocations = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereNotNull('user_lat')
            ->whereNotNull('user_lng')
            ->whereNotNull('user_city')
            ->select(
                'user_city as city',
                'user_lat as lat',
                'user_lng as lng',
                DB::raw('COUNT(*) as count'),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->groupBy('user_city', 'user_lat', 'user_lng')
            ->get();
        
        // 4. City trend data for last 6 months
        $topCities = $citySummary->take(5)->pluck('user_city')->toArray();
        $cityTrendData = [];
        $cityTrendLabels = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $cityTrendLabels[] = $month->format('M Y');
        }
        
        $colors = ['#667eea', '#764ba2', '#28a745', '#17a2b8', '#ffc107'];
        
        foreach ($topCities as $index => $city) {
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $count = Appointment::where('user_city', $city)
                    ->whereYear('appointment_date', $month->year)
                    ->whereMonth('appointment_date', $month->month)
                    ->count();
                $monthlyData[] = $count;
            }
            
            $cityTrendData[] = [
                'label' => $city,
                'data' => $monthlyData,
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => $colors[$index % count($colors)] . '20',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ];
        }
        
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
        
        // Pass data for appointment status chart
        $pendingAppointments = $summary['pending'];
        $confirmedAppointments = $summary['confirmed'];
        $completedAppointments = $summary['completed'];
        $cancelledAppointments = $summary['cancelled'];
        $totalBookings = $summary['total'];
        
        $psaLat = self::PSA_LAT;
        $psaLng = self::PSA_LNG;
        
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
            'staffPerformance',
            'citySummary',
            'uniqueLocations',
            'topCity',
            'topCityCount',
            'completionRate',
            'bookingLocations',
            'cityTrendData',
            'cityTrendLabels',
            'pendingAppointments',
            'confirmedAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'totalBookings',
            'psaLat',
            'psaLng'
        ));
    }
    
    /**
     * Export report to CSV (Enhanced with location data)
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
                
                // Add CSV headers with location fields
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
                    'User Location (City)',
                    'User Address',
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
                        $appointment->user_city ?? 'N/A',
                        $appointment->user_address ?? 'N/A',
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
    
    /**
     * NEW: Export location summary to CSV
     */
    public function exportLocationSummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        $citySummary = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereNotNull('user_city')
            ->select(
                'user_city',
                DB::raw('COUNT(*) as total_bookings'),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            )
            ->groupBy('user_city')
            ->orderBy('total_bookings', 'desc')
            ->get();
        
        $filename = 'location_summary_' . date('Y-m-d_His') . '.csv';
        
        return response()->stream(
            function() use ($citySummary) {
                $handle = fopen('php://output', 'w');
                
                fputcsv($handle, [
                    'City',
                    'Total Bookings',
                    'Pending',
                    'Confirmed',
                    'Completed',
                    'Cancelled'
                ]);
                
                foreach ($citySummary as $city) {
                    fputcsv($handle, [
                        $city->user_city,
                        $city->total_bookings,
                        $city->pending ?? 0,
                        $city->confirmed ?? 0,
                        $city->completed ?? 0,
                        $city->cancelled ?? 0
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