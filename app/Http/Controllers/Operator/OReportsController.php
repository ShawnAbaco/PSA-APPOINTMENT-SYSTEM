<?php
// app/Http/Controllers/Operator/ReportsController.php

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
        
        // Build appointment query with filters - ONLY CONFIRMED AND COMPLETED
        $appointmentQuery = Appointment::with(['clients', 'timeSlot'])
            ->whereBetween('appointment_date', [$startDateCarbon, $endDateCarbon])
            ->whereIn('status', ['confirmed', 'completed']); // 🔥 Only confirmed and completed
        
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
        
        $query = Appointment::with('clients', 'timeSlot')
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
}