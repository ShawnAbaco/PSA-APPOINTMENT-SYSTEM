<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\User;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{

    const PSA_LAT = 8.482432;
    const PSA_LNG = 124.655153;

    public function index(Request $request)
    {
        // Get date range from request or default to current month
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Get status filter
        $statusFilter = $request->get('status', '');
        
        // Convert to Carbon instances for query
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        // Build appointment query with filters
        $appointmentQuery = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon]);
        
        if ($statusFilter) {
            $appointmentQuery->where('status', $statusFilter);
        }
        
        // Get appointments within date range
        $appointments = $appointmentQuery->with('clients')->orderBy('appointment_date', 'desc')->get();
        
        // Summary statistics
        $summary = [
            'total' => $appointments->count(),
            'pending' => $appointments->where('status', 'pending')->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
        ];
        
        // Get appointments by service (from appointment_clients)
        $clientQuery = AppointmentClient::whereBetween('created_at', [$startDateCarbon, $endDateCarbon]);
        
        $byService = $clientQuery->select('service', DB::raw('COUNT(*) as count'))
            ->groupBy('service')
            ->get();
        
        // Get appointments by day of week
        $dayQuery = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon]);
        if ($statusFilter) {
            $dayQuery->where('status', $statusFilter);
        }
        
        $byDay = $dayQuery->select(DB::raw('DAYNAME(appointment_date) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->orderBy(DB::raw('FIELD(day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")'))
            ->get();
        
        // Get appointments by time slot (using time_slot_id)
        $timeSlotQuery = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon]);
        if ($statusFilter) {
            $timeSlotQuery->where('status', $statusFilter);
        }
        
        $byTimeSlot = $timeSlotQuery->with('timeSlot')
            ->get()
            ->groupBy('timeSlot.slot_label')
            ->map(function($items) {
                return $items->count();
            });
        
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
        
        // ========== LOCATION-BASED BOOKINGS DETAILS ==========
        
        // Get detailed bookings grouped by city with client counts
        $bookingsQuery = Appointment::with(['clients', 'timeSlot'])
            ->whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereNotNull('user_city');
        
        if ($statusFilter) {
            $bookingsQuery->where('status', $statusFilter);
        }
        
        $allBookings = $bookingsQuery->orderBy('appointment_date', 'desc')->get();
        
        // Group by city and add clients count
        $bookingsByLocation = [];
        foreach ($allBookings as $booking) {
            $city = $booking->user_city;
            if (!isset($bookingsByLocation[$city])) {
                $bookingsByLocation[$city] = [];
            }
            $booking->clients_count = $booking->clients->count();
            $bookingsByLocation[$city][] = $booking;
        }
        
        // ========== INDIVIDUAL BOOKINGS FOR MAP (IMPORTANT!) ==========
        // Get INDIVIDUAL bookings (not grouped by city) for map markers
        $individualBookings = Appointment::with(['clients'])
            ->whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereNotNull('user_lat')
            ->whereNotNull('user_lng')
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'appointment_number' => $booking->appointment_number,
                    'contact_name' => $booking->contact_name,
                    'contact_mobile' => $booking->contact_mobile,
                    'status' => $booking->status,
                    'appointment_date' => $booking->appointment_date,
                    'user_city' => $booking->user_city,
                    'lat' => $booking->user_lat,
                    'lng' => $booking->user_lng,
                    'clients_count' => $booking->clients->count(),
                    'clients' => $booking->clients->map(function($client) {
                        return [
                            'first_name' => $client->first_name,
                            'last_name' => $client->last_name,
                            'middle_name' => $client->middle_name,
                            'suffix' => $client->suffix,
                            'service' => $client->service,
                            'sex' => $client->sex,
                            'birthdate' => $client->birthdate,
                            'trn_number' => $client->trn_number,
                        ];
                    })
                ];
            });
        
        // City Summary with status breakdown (for statistics)
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
        $topCity = $citySummary->isNotEmpty() ? $citySummary->first()->user_city : 'N/A';
        $topCityCount = $citySummary->isNotEmpty() ? $citySummary->first()->total_bookings : 0;
        $completionRate = $summary['total'] > 0 ? round(($summary['completed'] / $summary['total']) * 100, 1) : 0;
        
        // 3. Location data for map (aggregated - used for legend/stats only)
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
        $serviceNames = [
            'reg' => 'National ID Registration',
            'updating' => 'Correction/Updating',
            'inquiry' => 'Status Inquiry'
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
            'byTimeSlot',
            'topClients',
            'startDate',
            'endDate',
            'serviceLabels',
            'serviceData',
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
            'psaLng',
            'bookingsByLocation',
            'statusFilter',
            'individualBookings',
            'appointments'
        ));
    }

/**
 * Export report as PDF
 */
public function exportPdf(Request $request)
{
    try {
        // Get date range from request or default to current month
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $statusFilter = $request->get('status', '');
        
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        $appointmentQuery = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon]);
        
        if ($statusFilter) {
            $appointmentQuery->where('status', $statusFilter);
        }
        
        // Eager load both clients and timeSlot relationships
        $appointments = $appointmentQuery
            ->with(['clients', 'timeSlot'])
            ->orderBy('appointment_date', 'desc')
            ->get();
        
        $summary = [
            'total' => $appointments->count(),
            'pending' => $appointments->where('status', 'pending')->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
        ];
        
        $completionRate = $summary['total'] > 0 ? round(($summary['completed'] / $summary['total']) * 100, 1) : 0;
        
        // City summary
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
        
        $uniqueLocations = $citySummary->count();
        $topCity = $citySummary->isNotEmpty() ? $citySummary->first()->user_city : 'N/A';
        $topCityCount = $citySummary->isNotEmpty() ? $citySummary->first()->total_bookings : 0;
        
        $data = compact(
            'appointments', 'summary', 'startDate', 'endDate', 'statusFilter',
            'completionRate', 'uniqueLocations', 'topCity', 'topCityCount',
            'citySummary'
        );
        
        // Check if DOMPDF is installed
        if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf', $data);
            $pdf->setPaper('A4', 'landscape');
            
            $filename = 'PSA_Appointment_Report_' . $startDate . '_to_' . $endDate . '.pdf';
            
            return $pdf->download($filename);
        } else {
            // Fallback: Force download as HTML file
            $html = view('admin.reports.pdf', $data)->render();
            
            $filename = 'PSA_Appointment_Report_' . $startDate . '_to_' . $endDate . '.xls';
            
            return response($html)
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }
        
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to generate PDF report: ' . $e->getMessage());
    }
}

/**
 * Export report as Excel
 */
public function exportExcel(Request $request)
{
    try {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $statusFilter = $request->get('status', '');
        
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        $appointmentQuery = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon]);
        
        if ($statusFilter) {
            $appointmentQuery->where('status', $statusFilter);
        }
        
        // Eager load with timeSlot relationship
        $appointments = $appointmentQuery->with(['clients', 'timeSlot'])->orderBy('appointment_date', 'desc')->get();
        
        $summary = [
            'total' => $appointments->count(),
            'pending' => $appointments->where('status', 'pending')->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
        ];
        
        $completionRate = $summary['total'] > 0 ? round(($summary['completed'] / $summary['total']) * 100, 1) : 0;
        
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
        
        $uniqueLocations = $citySummary->count();
        $topCity = $citySummary->isNotEmpty() ? $citySummary->first()->user_city : 'N/A';
        $topCityCount = $citySummary->isNotEmpty() ? $citySummary->first()->total_bookings : 0;
        
        // Set headers for Excel download
        $filename = 'PSA_Appointment_Report_' . $startDate . '_to_' . $endDate . '.xls';
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Output Excel-compatible HTML
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        echo '<x:Name>Detailed Bookings</x:Name>';
        echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        echo '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        echo '<style>';
        echo 'table { border-collapse: collapse; }';
        echo 'th { background-color: #0F3B6F; color: white; font-weight: bold; padding: 8px; border: 1px solid #000; }';
        echo 'td { padding: 6px 8px; border: 1px solid #000; }';
        echo 'tr:nth-child(even) { background-color: #F8F9FA; }';
        echo '.header-title { font-size: 16px; font-weight: bold; color: #0F3B6F; text-align: center; }';
        echo '.header-subtitle { font-size: 14px; color: #C49A2C; text-align: center; }';
        echo '.total-row { font-weight: bold; background-color: #C49A2C !important; }';
        echo '.status-pending { background-color: #F59E0B; color: white; padding: 2px 8px; }';
        echo '.status-confirmed { background-color: #3B82F6; color: white; padding: 2px 8px; }';
        echo '.status-completed { background-color: #10B981; color: white; padding: 2px 8px; }';
        echo '.status-cancelled { background-color: #EF4444; color: white; padding: 2px 8px; }';
        echo '.summary-table td { padding: 8px 15px; }';
        echo '.summary-label { font-weight: bold; background-color: #F0F4FF; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        
        // Title
        echo '<table style="width:100%; border: none;">';
        echo '<tr><td style="text-align:center; border:none;" class="header-title">Republic of the Philippines</td></tr>';
        echo '<tr><td style="text-align:center; border:none;" class="header-subtitle">PHILIPPINE STATISTICS AUTHORITY</td></tr>';
        echo '<tr><td style="text-align:center; border:none; font-size:18px; font-weight:bold; color:#0F3B6F; padding:10px;">APPOINTMENT SYSTEM REPORT</td></tr>';
        echo '<tr><td style="text-align:center; border:none; font-weight:bold; background-color:#F8F9FA;">Period: ' . $startDate . ' to ' . $endDate;
        if ($statusFilter) {
            echo ' | Status: ' . ucfirst($statusFilter);
        }
        echo '</td></tr>';
        echo '</table>';
        echo '<br>';
        
        // Summary Statistics
        echo '<h3 style="color:#0F3B6F;">SUMMARY STATISTICS</h3>';
        echo '<table class="summary-table" style="width:50%;">';
        echo '<tr><td class="summary-label">Total Bookings</td><td>' . $summary['total'] . '</td></tr>';
        echo '<tr><td class="summary-label">Pending</td><td>' . $summary['pending'] . '</td></tr>';
        echo '<tr><td class="summary-label">Confirmed</td><td>' . $summary['confirmed'] . '</td></tr>';
        echo '<tr><td class="summary-label">Completed</td><td>' . $summary['completed'] . '</td></tr>';
        echo '<tr><td class="summary-label">Cancelled</td><td>' . $summary['cancelled'] . '</td></tr>';
        echo '<tr><td class="summary-label">Unique Locations</td><td>' . $uniqueLocations . '</td></tr>';
        echo '<tr><td class="summary-label">Most Booked City</td><td>' . htmlspecialchars($topCity) . ' (' . $topCityCount . ' bookings)</td></tr>';
        echo '<tr><td class="summary-label">Completion Rate</td><td>' . $completionRate . '%</td></tr>';
        echo '</table>';
        echo '<br><br>';
        
        // Detailed Bookings
        echo '<h3 style="color:#0F3B6F;">DETAILED BOOKINGS</h3>';
        echo '<table style="width:100%;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Appointment #</th>';
        echo '<th>Date</th>';
        echo '<th>Time Slot</th>';
        echo '<th>Client Name</th>';
        echo '<th>Contact Number</th>';
        echo '<th>City/Municipality</th>';
        echo '<th>Services</th>';
        echo '<th>Status</th>';
        echo '<th>No. of Clients</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        $serviceNames = [
            'reg' => 'Registration',
            'updating' => 'Updating',
            'inquiry' => 'Status Inquiry'
        ];
        
        foreach ($appointments as $appointment) {
            $services = $appointment->clients->pluck('service')->unique()->map(function($s) use ($serviceNames) {
                return $serviceNames[$s] ?? $s;
            })->implode(', ');
            
            $statusClass = 'status-' . $appointment->status;
            
            // Get time slot label
            $timeSlotLabel = 'N/A';
            if ($appointment->timeSlot) {
                $timeSlotLabel = $appointment->timeSlot->slot_label;
            } elseif ($appointment->time_slot_id) {
                $timeSlot = \App\Models\TimeSlot::find($appointment->time_slot_id);
                $timeSlotLabel = $timeSlot ? $timeSlot->slot_label : 'N/A';
            }
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($appointment->appointment_number) . '</td>';
            echo '<td>' . Carbon::parse($appointment->appointment_date)->format('M d, Y') . '</td>';
            echo '<td>' . htmlspecialchars($timeSlotLabel) . '</td>';
            echo '<td>' . htmlspecialchars($appointment->contact_name) . '</td>';
            echo '<td>' . htmlspecialchars($appointment->contact_mobile) . '</td>';
            echo '<td>' . htmlspecialchars($appointment->user_city ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($services) . '</td>';
            echo '<td><span class="' . $statusClass . '">' . ucfirst($appointment->status) . '</span></td>';
            echo '<td style="text-align:center;">' . $appointment->clients->count() . '</td>';
            echo '</tr>';
        }
        
        if ($appointments->isEmpty()) {
            echo '<tr><td colspan="9" style="text-align:center;">No appointments found for this period.</td></tr>';
        } else {
            echo '<tr class="total-row">';
            echo '<td colspan="8" style="text-align:right;"><strong>TOTAL APPOINTMENTS:</strong></td>';
            echo '<td style="text-align:center;"><strong>' . $appointments->count() . '</strong></td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '<br><br>';
        
        // City Summary
        if ($citySummary->isNotEmpty()) {
            echo '<h3 style="color:#0F3B6F;">CITY/MUNICIPALITY SUMMARY</h3>';
            echo '<table style="width:100%;">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>City/Municipality</th>';
            echo '<th>Total Bookings</th>';
            echo '<th>Pending</th>';
            echo '<th>Confirmed</th>';
            echo '<th>Completed</th>';
            echo '<th>Cancelled</th>';
            echo '<th>Completion Rate</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($citySummary as $city) {
                $cityCompletionRate = $city->total_bookings > 0 
                    ? round(($city->completed / $city->total_bookings) * 100, 1) 
                    : 0;
                
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($city->user_city) . '</strong></td>';
                echo '<td style="text-align:center;">' . $city->total_bookings . '</td>';
                echo '<td style="text-align:center;">' . $city->pending . '</td>';
                echo '<td style="text-align:center;">' . $city->confirmed . '</td>';
                echo '<td style="text-align:center;">' . $city->completed . '</td>';
                echo '<td style="text-align:center;">' . $city->cancelled . '</td>';
                echo '<td style="text-align:center;">' . $cityCompletionRate . '%</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        echo '<br>';
        echo '<p style="text-align:center; color:#666; font-style:italic;">';
        echo 'Philippine Statistics Authority | Appointment Management System<br>';
        echo 'Generated on ' . now()->format('F j, Y \a\t g:i A') . '<br>';
        echo 'This report is system-generated and is valid without signature.';
        echo '</p>';
        
        echo '</body>';
        echo '</html>';
        
        exit;
        
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to export Excel: ' . $e->getMessage());
    }
}
}