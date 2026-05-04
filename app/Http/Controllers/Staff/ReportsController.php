<?php
// app/Http/Controllers/Staff/ReportsController.php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to current month
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Get status filter (only pending or confirmed allowed for staff)
        $statusFilter = $request->get('status', '');
        
        // Convert to Carbon instances for query
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        
        // Build appointment query with filters - ONLY PENDING AND CONFIRMED
        $appointmentQuery = Appointment::with(['clients', 'timeSlot'])
            ->whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['pending', 'confirmed']); // 🔥 Only pending and confirmed
        
        if ($statusFilter && in_array($statusFilter, ['pending', 'confirmed'])) {
            $appointmentQuery->where('status', $statusFilter);
        }
        
        // Get appointments with pagination
        $perPage = $request->get('per_page', 15);
        $appointments = $appointmentQuery->orderBy('appointment_date', 'asc')
            ->orderBy('time_slot_id', 'asc')
            ->paginate($perPage);
        
        // Get all appointments for statistics (within date range, only pending/confirmed)
        $allAppointments = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();
        
        // Summary statistics - ONLY PENDING AND CONFIRMED
        $pendingAppointments = $allAppointments->where('status', 'pending')->count();
        $confirmedAppointments = $allAppointments->where('status', 'confirmed')->count();
        
        // City Summary with status breakdown (only pending and confirmed)
        $citySummary = Appointment::whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNotNull('user_city')
            ->select(
                'user_city',
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed")
            )
            ->groupBy('user_city')
            ->get();
        
        // Sort the collection in PHP instead of SQL (to avoid the alias reference error)
        $citySummary = $citySummary->sortByDesc(function($item) {
            return $item->pending + $item->confirmed;
        })->values();
        
        // Also get total count for the selected period
        $totalBookings = $allAppointments->count();
        
        return view('staff.reports.index', compact(
            'appointments',
            'startDate',
            'endDate',
            'pendingAppointments',
            'confirmedAppointments',
            'citySummary',
            'totalBookings'
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
            ->whereIn('status', ['pending', 'confirmed']);
        
        if ($statusFilter && in_array($statusFilter, ['pending', 'confirmed'])) {
            $query->where('status', $statusFilter);
        }
        
        $appointments = $query->orderBy('appointment_date', 'asc')->get();
        
        $filename = 'pending_confirmed_report_' . date('Y-m-d_His') . '.csv';
        
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
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNotNull('user_city')
            ->select(
                'user_city',
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed")
            )
            ->groupBy('user_city')
            ->get()
            ->sortByDesc(function($item) {
                return $item->pending + $item->confirmed;
            });
        
        $filename = 'city_summary_' . date('Y-m-d_His') . '.csv';
        
        return response()->stream(
            function() use ($citySummary) {
                $handle = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for Excel compatibility
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($handle, [
                    'City',
                    'Pending Appointments',
                    'Confirmed Appointments',
                    'Total'
                ]);
                
                foreach ($citySummary as $city) {
                    fputcsv($handle, [
                        $city->user_city,
                        $city->pending,
                        $city->confirmed,
                        $city->pending + $city->confirmed
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
}