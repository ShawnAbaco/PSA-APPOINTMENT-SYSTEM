<?php
// app/Http/Controllers/Operator/OReportsController.php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OReportsController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to current month
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Get status filter (only confirmed or completed allowed for operator)
        $statusFilter = $request->get('status', '');
        
        // Convert to Carbon instances for query
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        // Build appointment query with filters 
        $appointmentQuery = Appointment::with(['clients', 'timeSlot'])
            ->whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['confirmed', 'completed']); 
        
        if ($statusFilter && in_array($statusFilter, ['confirmed', 'completed'])) {
            $appointmentQuery->where('status', $statusFilter);
        }
        
        // Get appointments with pagination
        $perPage = $request->get('per_page', 15);
        $appointments = $appointmentQuery->orderBy('appointment_date', 'asc')
            ->orderBy('time_slot_id', 'asc')
            ->paginate($perPage);
        
        // Get all appointments for statistics (within date range, only confirmed/completed)
        $allAppointments = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['confirmed', 'completed'])
            ->get();
        
        // Summary statistics - ONLY CONFIRMED AND COMPLETED
        $confirmedAppointments = $allAppointments->where('status', 'confirmed')->count();
        $completedAppointments = $allAppointments->where('status', 'completed')->count();
        
        // City Summary with status breakdown (only confirmed and completed)
        $citySummary = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereNotNull('user_city')
            ->select(
                'user_city',
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            )
            ->groupBy('user_city')
            ->get();
        
        // Sort the collection in PHP instead of SQL (to avoid the alias reference error)
        $citySummary = $citySummary->sortByDesc(function($item) {
            return $item->confirmed + $item->completed;
        })->values();
        
        // Also get total count for the selected period
        $totalBookings = $allAppointments->count();
        
        return view('operator.reports.index', compact(
            'appointments',
            'startDate',
            'endDate',
            'confirmedAppointments',
            'completedAppointments',
            'citySummary',
            'totalBookings',
            'statusFilter'
        ));
    }
    
    /**
     * Export report to CSV
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $statusFilter = $request->get('status', '');
        
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        $query = Appointment::with(['clients', 'timeSlot'])
            ->whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['confirmed', 'completed']);
        
        if ($statusFilter && in_array($statusFilter, ['confirmed', 'completed'])) {
            $query->where('status', $statusFilter);
        }
        
        $appointments = $query->orderBy('appointment_date', 'asc')->get();
        
        $filename = 'confirmed_completed_report_' . date('Y-m-d_His') . '.csv';
        
        return response()->stream(
            function() use ($appointments) {
                $handle = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for Excel compatibility
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($handle, [
                    'Appointment #',
                    'Date',
                    'Time Slot',
                    'Contact Person',
                    'Contact Mobile',
                    'Contact Email',
                    'Status',
                    'Number of Clients',
                    'Clients Names',
                    'Services',
                    'Location (City)',
                    'Created At'
                ]);
                
                foreach ($appointments as $appointment) {
                    $clientNames = $appointment->clients->map(function($client) {
                        return $client->first_name . ' ' . $client->last_name;
                    })->implode(', ');
                    
                    $services = $appointment->clients->map(function($client) {
                        $serviceNames = [
                            'reg' => 'Registration',
                            'updating' => 'Correction/Updating',
                            'inquiry' => 'Status Inquiry'
                        ];
                        return $serviceNames[$client->service] ?? $client->service;
                    })->unique()->implode(', ');
                    
                    // Fixed time slot label with proper null check
                    $timeSlotLabel = $appointment->timeSlot ? $appointment->timeSlot->slot_label : 'N/A';
                    
                    fputcsv($handle, [
                        $appointment->appointment_number,
                        $appointment->appointment_date,
                        $timeSlotLabel,
                        $appointment->contact_name,
                        $appointment->contact_mobile,
                        $appointment->contact_email,
                        ucfirst($appointment->status),
                        $appointment->clients->count(),
                        $clientNames,
                        $services,
                        $appointment->user_city ?? 'N/A',
                        $appointment->created_at,
                    ]);
                }
                
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
    
    /**
     * Export city summary to CSV
     */
    public function exportCitySummary(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        $citySummary = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereNotNull('user_city')
            ->select(
                'user_city',
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            )
            ->groupBy('user_city')
            ->get()
            ->sortByDesc(function($item) {
                return $item->confirmed + $item->completed;
            });
        
        $filename = 'city_summary_' . date('Y-m-d_His') . '.csv';
        
        return response()->stream(
            function() use ($citySummary) {
                $handle = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for Excel compatibility
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($handle, [
                    'City',
                    'Confirmed Appointments',
                    'Completed Appointments',
                    'Total'
                ]);
                
                foreach ($citySummary as $city) {
                    fputcsv($handle, [
                        $city->user_city,
                        $city->confirmed,
                        $city->completed,
                        $city->confirmed + $city->completed
                    ]);
                }
                
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
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
            
            $appointmentQuery = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
                ->whereIn('status', ['confirmed', 'completed']);
            
            if ($statusFilter && in_array($statusFilter, ['confirmed', 'completed'])) {
                $appointmentQuery->where('status', $statusFilter);
            }
            
            // Eager load both clients and timeSlot relationships
            $appointments = $appointmentQuery
                ->with(['clients', 'timeSlot'])
                ->orderBy('appointment_date', 'desc')
                ->get();
            
            $summary = [
                'total' => $appointments->count(),
                'confirmed' => $appointments->where('status', 'confirmed')->count(),
                'completed' => $appointments->where('status', 'completed')->count(),
            ];
            
            // City summary (only confirmed and completed)
            $citySummary = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereNotNull('user_city')
                ->select(
                    'user_city',
                    DB::raw('COUNT(*) as total_bookings'),
                    DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                    DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
                )
                ->groupBy('user_city')
                ->orderBy('total_bookings', 'desc')
                ->get();
            
            $uniqueLocations = $citySummary->count();
            $topCity = $citySummary->isNotEmpty() ? $citySummary->first()->user_city : 'N/A';
            $topCityCount = $citySummary->isNotEmpty() ? $citySummary->first()->total_bookings : 0;
            $completionRate = $summary['total'] > 0 ? round(($summary['completed'] / $summary['total']) * 100, 1) : 0;
            
            $data = compact(
                'appointments', 'summary', 'startDate', 'endDate', 'statusFilter',
                'completionRate', 'uniqueLocations', 'topCity', 'topCityCount',
                'citySummary'
            );
            
            // Check if DOMPDF is installed
            if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('operator.reports.pdf', $data);
                $pdf->setPaper('A4', 'landscape');
                
                $filename = 'Confirmed_Completed_Report_' . $startDate . '_to_' . $endDate . '.pdf';
                
                return $pdf->download($filename);
            } else {
                // Fallback: Force download as HTML file
                $html = view('operator.reports.pdf', $data)->render();
                
                $filename = 'Confirmed_Completed_Report_' . $startDate . '_to_' . $endDate . '.html';
                
                return response($html)
                    ->header('Content-Type', 'text/html')
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
            
            $appointmentQuery = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
                ->whereIn('status', ['confirmed', 'completed']);
            
            if ($statusFilter && in_array($statusFilter, ['confirmed', 'completed'])) {
                $appointmentQuery->where('status', $statusFilter);
            }
            
            // Eager load with timeSlot relationship
            $appointments = $appointmentQuery->with(['clients', 'timeSlot'])->orderBy('appointment_date', 'desc')->get();
            
            $summary = [
                'total' => $appointments->count(),
                'confirmed' => $appointments->where('status', 'confirmed')->count(),
                'completed' => $appointments->where('status', 'completed')->count(),
            ];
            
            $completionRate = $summary['total'] > 0 ? round(($summary['completed'] / $summary['total']) * 100, 1) : 0;
            
            $citySummary = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereNotNull('user_city')
                ->select(
                    'user_city',
                    DB::raw('COUNT(*) as total_bookings'),
                    DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed"),
                    DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
                )
                ->groupBy('user_city')
                ->orderBy('total_bookings', 'desc')
                ->get();
            
            $uniqueLocations = $citySummary->count();
            $topCity = $citySummary->isNotEmpty() ? $citySummary->first()->user_city : 'N/A';
            $topCityCount = $citySummary->isNotEmpty() ? $citySummary->first()->total_bookings : 0;
            
            // Set headers for Excel download
            $filename = 'Confirmed_Completed_Report_' . $startDate . '_to_' . $endDate . '.xls';
            
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Output Excel-compatible HTML
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta charset="UTF-8">';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
            echo '<x:Name>Confirmed Completed Report</x:Name>';
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
            echo '.status-confirmed { background-color: #10B981; color: white; padding: 2px 8px; }';
            echo '.status-completed { background-color: #3B82F6; color: white; padding: 2px 8px; }';
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
            echo '<tr><td class="summary-label">Confirmed</td><td>' . $summary['confirmed'] . '</td></tr>';
            echo '<tr><td class="summary-label">Completed</td><td>' . $summary['completed'] . '</td></tr>';
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
            
            // City Summary Table
            if ($citySummary->isNotEmpty()) {
                echo '<h3 style="color:#0F3B6F;">CITY/MUNICIPALITY SUMMARY</h3>';
                echo '<table style="width:100%;">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>City/Municipality</th>';
                echo '<th>Total Bookings</th>';
                echo '<th>Confirmed</th>';
                echo '<th>Completed</th>';
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
                    echo '<td style="text-align:center;">' . $city->confirmed . '</td>';
                    echo '<td style="text-align:center;">' . $city->completed . '</td>';
                    echo '<td style="text-align:center;">' . $cityCompletionRate . '%</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                echo '<br><br>';
            }
            
            // Footer
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