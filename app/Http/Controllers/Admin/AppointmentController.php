<?php
// app/Http/Controllers/Admin/AppointmentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\User;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    const PSA_LAT = 8.4815315;
    const PSA_LNG = 124.6549067;

    public function index(Request $request)
    {
        $query = Appointment::with('clients', 'processedBy', 'timeSlot');
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        
        // Date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        
        // Time slot filter
        if ($request->filled('time_slot')) {
            $query->where('time_slot_id', $request->time_slot);
        }
        
        // City filter
        if ($request->filled('city')) {
            $query->where('user_city', 'like', '%' . $request->city . '%');
        }
        
        $appointments = $query->latest()->paginate(50);
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];
        
        // Get unique cities for filter dropdown
        $cities = Appointment::whereNotNull('user_city')
            ->select('user_city')
            ->distinct()
            ->pluck('user_city');
        
        // Get time slots for the filter dropdown
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        return view('admin.appointments.index', compact('appointments', 'statuses', 'cities', 'timeSlots'));
    }
    
    public function show($id)
    {
        $appointment = Appointment::with('clients', 'processedBy', 'createdBy')->findOrFail($id);
        return view('admin.appointments.show', compact('appointment'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->update([
                'status' => $request->status,
                'processed_by' => auth()->id(),
                $request->status . '_at' => now()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Status updated successfully']);
            }
            
            return redirect()->back()->with('success', 'Appointment status updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error updating status']);
            }
            return redirect()->back()->with('error', 'Error updating status');
        }
    }
    
    public function calendar()
    {
        // Get ALL appointments with their clients (not just confirmed/pending)
        $appointments = Appointment::with('clients', 'timeSlot')
            ->orderBy('appointment_date')
            ->get();
            
        return view('admin.appointments.calendar', compact('appointments'));
    }
    
    public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Appointment deleted successfully']);
            }
            
            return redirect()->route('admin.appointments.index')->with('success', 'Appointment deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error deleting appointment']);
            }
            return redirect()->route('admin.appointments.index')->with('error', 'Error deleting appointment');
        }
    }
    
    // ========== LOCATION METHODS ==========
    
    public function getByLocation(Request $request)
    {
        $query = Appointment::with('clients');
        
        if ($request->filled('start_date')) {
            $query->whereDate('appointment_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('appointment_date', '<=', $request->end_date);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $appointments = $query->whereNotNull('user_lat')
            ->whereNotNull('user_lng')
            ->get(['id', 'user_lat', 'user_lng', 'user_city', 'user_address', 'status', 'appointment_date']);
        
        return response()->json($appointments);
    }
    
    public function cityStatistics(Request $request)
    {
        $query = Appointment::query();
        
        if ($request->filled('start_date')) {
            $query->whereDate('appointment_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('appointment_date', '<=', $request->end_date);
        }
        
        $stats = $query->whereNotNull('user_city')
            ->selectRaw('user_city, COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending')
            ->selectRaw('SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->selectRaw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled')
            ->groupBy('user_city')
            ->orderBy('total', 'desc')
            ->get();
        
        return response()->json($stats);
    }
    
    public function getPsaCoordinates()
    {
        return response()->json([
            'lat' => self::PSA_LAT,
            'lng' => self::PSA_LNG,
            'address' => 'Capt. Vicente Roa Street, Brgy. 31, Cagayan de Oro City, 9000 Misamis Oriental, Philippines'
        ]);
    }
    
    public function showModal($id)
    {
        $appointment = Appointment::with('clients')->findOrFail($id);
        
        // Return the show.blade.php view without layout for modal
        return view('admin.appointments.show', compact('appointment'));
    }
    
    public function filter(Request $request)
    {
        $query = Appointment::with(['clients', 'timeSlot']);
        
        // Search filter
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('appointment_number', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_mobile', 'like', '%' . $request->search . '%');
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Date from filter
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        
        // Date to filter
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        
        // Time slot filter
        if ($request->filled('time_slot')) {
            $query->where('time_slot_id', $request->time_slot);
        }
        
        // City filter
        if ($request->filled('city')) {
            $query->where('user_city', 'like', '%' . $request->city . '%');
        }
        
        // Use 10 per page for AJAX as well
        $appointments = $query->orderBy('appointment_date', 'desc')
                             ->orderBy('id', 'desc')
                             ->paginate(10);
        
        if ($request->ajax()) {
            // Build table rows HTML
            $html = '';
            foreach($appointments as $appointment) {
                $html .= '
                    <tr class="appt-row" data-status="' . $appointment->status . '" data-date="' . $appointment->appointment_date . '">
                        <td class="appt-id"><i class="fas fa-hashtag"></i> ' . $appointment->id . '</td>
                        <td class="appt-number"><i class="fas fa-ticket-alt"></i> ' . $appointment->appointment_number . '</td>
                        <td class="appt-date">
                            <i class="fas fa-calendar-day"></i> ' . date('M d, Y', strtotime($appointment->appointment_date)) . '
                            <small class="appt-time-badge"><i class="fas fa-clock"></i> ' . ($appointment->timeSlot ? date('h:i A', strtotime($appointment->timeSlot->start_time)) : date('h:i A', strtotime($appointment->appointment_time ?? '09:00'))) . '</small>
                        </td>
                        <td class="appt-contact-info">
                            <div class="appt-contact-details">
                                <strong>' . e($appointment->contact_name) . '</strong>
                                <small><i class="fas fa-phone"></i> ' . e($appointment->contact_mobile ?? $appointment->contact_phone ?? 'No phone') . '</small>
                            </div>
                        </td>
                        <td class="appt-clients-count">
                            <span class="appt-client-badge"><i class="fas fa-users"></i> ' . $appointment->clients->count() . ' person(s)</span>
                        </td>
                        <td>
                            <select class="appt-status-select appt-status-badge appt-status-' . $appointment->status . '" data-id="' . $appointment->id . '" data-status="' . $appointment->status . '">
                                <option value="pending" ' . ($appointment->status == 'pending' ? 'selected' : '') . '>Pending</option>
                                <option value="confirmed" ' . ($appointment->status == 'confirmed' ? 'selected' : '') . '>Confirmed</option>
                                <option value="completed" ' . ($appointment->status == 'completed' ? 'selected' : '') . '>Completed</option>
                                <option value="cancelled" ' . ($appointment->status == 'cancelled' ? 'selected' : '') . '>Cancelled</option>
                                <option value="no_show" ' . ($appointment->status == 'no_show' ? 'selected' : '') . '>No Show</option>
                            </select>
                        </td>
                        <td class="appt-actions-cell">
                            <button class="appt-action-btn appt-view-btn" data-id="' . $appointment->id . '" data-appointment-number="' . $appointment->appointment_number . '" title="View Details">
                                <i class="fas fa-eye"></i> <span>View</span>
                            </button>
                            <button class="appt-action-btn appt-delete-btn" data-id="' . $appointment->id . '" title="Delete Appointment">
                                <i class="fas fa-trash-alt"></i> <span>Delete</span>
                            </button>
                        </td>
                    </tr>
                ';
            }
            
            if($appointments->isEmpty()) {
                $html = '<tr><td colspan="7" class="appt-empty-state"><i class="fas fa-calendar-times"></i><h4>No Appointments Found</h4><p>No appointments match your criteria</p></td></tr>';
            }
            
            // Build custom pagination HTML
            $paginationHtml = '';
            if ($appointments->lastPage() > 1) {
                $paginationHtml = '
                    <div class="pagination-wrapper">
                        <div class="pagination-info">Showing ' . $appointments->firstItem() . ' to ' . $appointments->lastItem() . ' of ' . $appointments->total() . ' results</div>
                        <div class="pagination-controls">';
                
                if ($appointments->onFirstPage()) {
                    $paginationHtml .= '<button class="pagination-btn disabled" disabled><i class="fas fa-chevron-left"></i> Previous</button>';
                } else {
                    $paginationHtml .= '<a href="' . $appointments->previousPageUrl() . '" class="pagination-btn" data-page="' . ($appointments->currentPage() - 1) . '"><i class="fas fa-chevron-left"></i> Previous</a>';
                }
                
                $paginationHtml .= '<span class="pagination-pages">Page <span class="current-page">' . $appointments->currentPage() . '</span> of <span class="total-pages">' . $appointments->lastPage() . '</span></span>';
                
                if ($appointments->hasMorePages()) {
                    $paginationHtml .= '<a href="' . $appointments->nextPageUrl() . '" class="pagination-btn" data-page="' . ($appointments->currentPage() + 1) . '">Next <i class="fas fa-chevron-right"></i></a>';
                } else {
                    $paginationHtml .= '<button class="pagination-btn disabled" disabled>Next <i class="fas fa-chevron-right"></i></button>';
                }
                
                $paginationHtml .= '</div></div>';
            }
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $paginationHtml,
                'total' => $appointments->total()
            ]);
        }
        
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        $cities = Appointment::whereNotNull('user_city')
            ->select('user_city')
            ->distinct()
            ->pluck('user_city');
        
        return view('admin.appointments.index', compact('appointments', 'timeSlots', 'cities'));
    }
    
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time_slot_id' => 'required|exists:time_slots,id',
                'contact_name' => 'required|string|max:255',
                'contact_mobile' => 'required|string|max:20',
                'contact_email' => 'nullable|email|max:255',
                'clients' => 'required|array|min:1|max:4',
                'clients.*.first_name' => 'required|string|max:255',
                'clients.*.last_name' => 'required|string|max:255',
                'clients.*.sex' => 'required|in:Male,Female',
                'clients.*.birthdate' => 'required|date|before:today',
                'clients.*.service' => 'required|in:reg,updating,inquiry',
                'clients.*.has_trn' => 'nullable|boolean',
                'clients.*.trn_number' => 'nullable|string|size:29|regex:/^\d+$/',
                'user_lat' => 'nullable|numeric|between:-90,90',
                'user_lng' => 'nullable|numeric|between:-180,180',
                'user_city' => 'nullable|string|max:100',
                'user_address' => 'nullable|string',
                'user_zipcode' => 'nullable|string|max:20',
                'notes' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $clientCount = count($request->clients);
            $detectedType = $clientCount === 1 ? 'single' : 'multiple';
            
            DB::beginTransaction();
            
            try {
                // Generate appointment number and reference code
                $date = Carbon::now()->format('Ymd');
                $last = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
                $appointmentNumber = 'PSA-' . $date . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
                $referenceCode = 'REF-' . strtoupper(uniqid());
                
                // Create appointment (NO SLOT VALIDATION FOR ADMIN)
                $appointment = new Appointment();
                $appointment->appointment_number = $appointmentNumber;
                $appointment->type = $detectedType;
                $appointment->appointment_date = $request->appointment_date;
                $appointment->time_slot_id = $request->appointment_time_slot_id;
                $appointment->contact_name = $request->contact_name;
                $appointment->contact_email = $request->contact_email;
                $appointment->contact_mobile = $request->contact_mobile;
                $appointment->reference_code = $referenceCode;
                $appointment->status = 'pending';
                $appointment->notes = $request->notes;
                $appointment->metadata = json_encode([
                    'created_by_admin' => true,
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'auto_detected_type' => true,
                    'original_client_count' => $clientCount,
                    'admin_id' => auth()->id()
                ]);
                
                if ($request->filled('user_lat')) $appointment->user_lat = $request->user_lat;
                if ($request->filled('user_lng')) $appointment->user_lng = $request->user_lng;
                if ($request->filled('user_city')) $appointment->user_city = $request->user_city;
                if ($request->filled('user_address')) $appointment->user_address = $request->user_address;
                if ($request->filled('user_zipcode')) $appointment->user_zipcode = $request->user_zipcode;
                
                $appointment->created_by = auth()->id();
                $appointment->save();
                
                // Store clients
                foreach ($request->clients as $clientData) {
                    $client = new AppointmentClient();
                    $clientNumber = $this->generateClientNumber();
                    $client->client_number = $clientNumber;
                    $client->appointment_id = $appointment->id;
                    $client->first_name = $clientData['first_name'];
                    $client->middle_name = $clientData['middle_name'] ?? null;
                    $client->last_name = $clientData['last_name'];
                    $client->suffix = $clientData['suffix'] ?? null;
                    $client->sex = $clientData['sex'];
                    $client->birthdate = $clientData['birthdate'];
                    $client->service = $clientData['service'];
                    $client->requirements_acknowledged = true;
                    $client->acknowledged_at = now();
                    
                    if ($clientData['service'] === 'inquiry') {
                        $client->has_trn = isset($clientData['has_trn']) ? (bool)$clientData['has_trn'] : false;
                        $client->trn_number = (isset($clientData['has_trn']) && $clientData['has_trn']) ? ($clientData['trn_number'] ?? null) : null;
                    }
                    
                    $client->save();
                }
                
                DB::commit();
                
                // Get time slot label
                $timeSlot = TimeSlot::find($request->appointment_time_slot_id);
                $timeSlotLabel = $timeSlot->label ?? $this->formatTimeRange($timeSlot->start_time, $timeSlot->end_time);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Appointment created successfully!',
                    'appointment' => [
                        'number' => $appointment->appointment_number,
                        'reference_code' => $appointment->reference_code,
                        'date' => Carbon::parse($appointment->appointment_date)->format('F d, Y'),
                        'time' => $timeSlotLabel,
                        'clients_count' => count($request->clients),
                        'type' => $appointment->type
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Admin appointment creation failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create appointment: ' . $e->getMessage()
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Admin appointment store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function generateClientNumber()
    {
        $year = date('Y');
        $month = date('m');
        $last = AppointmentClient::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        
        return 'CLN-' . $year . $month . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
    
    private function formatTimeRange($startTime, $endTime)
    {
        try {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            return $start->format('g:i A') . ' - ' . $end->format('g:i A');
        } catch (\Exception $e) {
            return $startTime . ' - ' . $endTime;
        }
    }
}