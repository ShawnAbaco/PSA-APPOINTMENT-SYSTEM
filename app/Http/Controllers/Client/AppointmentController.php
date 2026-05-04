<?php
// app/Http/Controllers/Client/AppointmentController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TimeSlot;
use App\Models\SlotCapacityRule;
use App\Models\SlotCapacityOverride;
use App\Models\WorkingDaysDefault;
use App\Models\WorkingDaysOverride;
use Carbon\Carbon;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    protected $mailService;
    
    // PSA Misamis Oriental Coordinates
    const PSA_LAT = 8.4815315;
    const PSA_LNG = 124.6549067;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function index()
    {
        try {
            $services = Service::where('is_active', true)->orderBy('display_order')->get();
            
            return view('client.appointment', compact('services'));
        } catch (\Exception $e) {
            Log::error('Error loading appointment page: ' . $e->getMessage());
            $services = collect();
            return view('client.appointment', compact('services'));
        }
    }
    
    /**
     * Get day type for a specific date
     * Returns: 'working', 'non_working', or 'holiday'
     */
    private function getDayType($date)
    {
        try {
            // Check override FIRST (for holidays and special non-working days)
            $override = WorkingDaysOverride::where('date', $date->format('Y-m-d'))->first();
            if ($override) {
                return $override->day_type;
            }
            
            // Get day name from Carbon
            $dayName = strtolower($date->format('l')); // monday, tuesday, etc.
            
            // Get from database using correct column name 'day_name'
            $default = WorkingDaysDefault::where('day_name', $dayName)->first();
            
            if (!$default) {
                \Log::warning("No working day configuration found for: {$dayName}");
                return 'non_working';
            }
            
            return $default->day_type;
            
        } catch (\Exception $e) {
            \Log::error('Error in getDayType: ' . $e->getMessage());
            return 'non_working';
        }
    }

    /**
     * Get capacity for a specific date, time slot, and service
     * 
     * Priority order:
     * 1. SlotCapacityOverride (admin can set custom capacity for any date)
     * 2. SlotCapacityRule based on day_type from working_days (working, non_working, holiday)
     * 3. Default fallback (4 for working, 0 for others)
     */
    private function getCapacity($date, $timeSlotId, $service)
    {
        try {
            // STEP 1: Check for SlotCapacityOverride FIRST (highest priority)
            $override = SlotCapacityOverride::where('date', $date)
                ->where('time_slot_id', $timeSlotId)
                ->first();
            
            if ($override) {
                switch ($service) {
                    case 'reg': return $override->reg_capacity ?? 0;
                    case 'updating': return $override->updating_capacity ?? 0;
                    case 'inquiry': return $override->inquiry_capacity ?? 0;
                    default: return 0;
                }
            }
            
            // STEP 2: Get day type from working days configuration
            $dayType = $this->getDayType(Carbon::parse($date));
            
            // STEP 3: Get capacity rule based on day_type
            $rule = SlotCapacityRule::where('time_slot_id', $timeSlotId)
                ->where('day_type', $dayType)
                ->first();
            
            if ($rule) {
                switch ($service) {
                    case 'reg': return $rule->reg_capacity;
                    case 'updating': return $rule->updating_capacity;
                    case 'inquiry': return $rule->inquiry_capacity;
                    default: return 0;
                }
            }
            
            // STEP 4: Default fallback values
            if ($dayType === 'working') {
                return 4; // Default working day capacity
            }
            
            return 0; // For non_working and holiday, default to 0 (closed)
            
        } catch (\Exception $e) {
            Log::warning('Error getting capacity: ' . $e->getMessage());
            return 4;
        }
    }
    
    /**
     * Get booked count for a specific date, time slot, and service
     */
    private function getBookedCount($date, $timeSlotId, $service)
    {
        try {
            return AppointmentClient::whereHas('appointment', function($query) use ($date, $timeSlotId) {
                $query->where('appointment_date', $date)
                    ->where('time_slot_id', $timeSlotId)
                    ->where('status', '!=', 'cancelled');
            })->where('service', $service)->count();
        } catch (\Exception $e) {
            Log::warning('Error getting booked count: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get available slots for a specific date, time slot, and service
     */
    private function getAvailableSlots($date, $timeSlotId, $service)
    {
        $capacity = $this->getCapacity($date, $timeSlotId, $service);
        $booked = $this->getBookedCount($date, $timeSlotId, $service);
        return max(0, $capacity - $booked);
    }

    public function getAvailableDates(Request $request)
    {
        try {
            $month = (int)$request->get('month', date('n'));
            $year = (int)$request->get('year', date('Y'));
            $clientCount = (int)$request->get('client_count', 1);
            $servicesParam = $request->get('services', '');
            
            // Get list of services to check
            $servicesToCheck = !empty($servicesParam) ? explode(',', $servicesParam) : ['reg', 'updating', 'inquiry'];
            
            $advanceDays = 30;
            $advanceSetting = Setting::where('key', 'appointment.advance_booking_days')->first();
            if ($advanceSetting) {
                $advanceDays = (int)$advanceSetting->value;
            }
            
            $maxDate = Carbon::now()->addDays($advanceDays);
            $dates = [];
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            
            // Get all active time slots
            $timeSlots = TimeSlot::where('is_active', true)->get();
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day);
                $dateKey = $date->format('Y-m-d');
                
                // Skip past dates
                if ($date->lt(Carbon::now()->startOfDay())) {
                    continue;
                }
                
                // Skip dates beyond max booking
                if ($date->gt($maxDate)) {
                    continue;
                }
                
                // Check if date is a working day
                $dayType = $this->getDayType($date);
                if ($dayType !== 'working') {
                    continue;
                }
                
                if ($timeSlots->isEmpty()) {
                    continue;
                }
                
                // Check if there's ANY time slot that can accommodate ALL selected services
                $hasAnyAvailableTimeSlot = false;
                $serviceAvailability = [];
                
                foreach ($servicesToCheck as $service) {
                    $serviceAvailability[$service] = 0;
                }
                
                foreach ($timeSlots as $timeSlot) {
                    $allServicesHaveAvailability = true;
                    
                    foreach ($servicesToCheck as $service) {
                        $available = $this->getAvailableSlots($dateKey, $timeSlot->id, $service);
                        if ($available <= 0) {
                            $allServicesHaveAvailability = false;
                            break;
                        }
                    }
                    
                    if ($allServicesHaveAvailability) {
                        $hasAnyAvailableTimeSlot = true;
                        // Add to service availability totals
                        foreach ($servicesToCheck as $service) {
                            $available = $this->getAvailableSlots($dateKey, $timeSlot->id, $service);
                            $serviceAvailability[$service] += $available;
                        }
                    }
                }
                
                // Only include dates that have at least one time slot where ALL services have availability
                if ($hasAnyAvailableTimeSlot) {
                    $totalAvailableForClientCount = min($serviceAvailability);
                    
                    $dates[] = [
                        'date' => $dateKey,
                        'available' => true,
                        'available_slots' => $totalAvailableForClientCount,
                        'service_availability' => $serviceAvailability,
                        'day' => $date->format('l'),
                        'display_date' => $date->format('F d, Y'),
                        'day_type' => $dayType,
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'dates' => $dates,
                'month' => Carbon::create($year, $month)->format('F Y'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('getAvailableDates error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'dates' => []
            ], 500);
        }
    }
    
    public function getAvailableTimeSlots(Request $request)
    {
        try {
            $date = $request->get('date');
            $servicesParam = $request->get('services', '');
            $clientCount = (int)$request->get('client_count', 1);
            
            $servicesToCheck = !empty($servicesParam) ? explode(',', $servicesParam) : ['reg', 'updating', 'inquiry'];
            
            // Get all active time slots
            $timeSlots = TimeSlot::where('is_active', true)
                ->orderBy('display_order')
                ->get();
            
            $availableTimeSlots = [];
            
            foreach ($timeSlots as $timeSlot) {
                $availableForServices = [];
                $allServicesHaveAvailability = true;
                $minAvailable = PHP_INT_MAX;
                
                foreach ($servicesToCheck as $service) {
                    $available = $this->getAvailableSlots($date, $timeSlot->id, $service);
                    $availableForServices[$service] = $available;
                    $minAvailable = min($minAvailable, $available);
                    
                    // If ANY service has 0 available slots, this time slot is NOT available for this client group
                    if ($available <= 0) {
                        $allServicesHaveAvailability = false;
                    }
                }
                
                // Show time slot ONLY if ALL selected services have at least 1 available slot
                if ($allServicesHaveAvailability) {
                    $availableTimeSlots[] = [
                        'id' => $timeSlot->id,
                        'time_slot_id' => $timeSlot->id,
                        'slot_label' => $timeSlot->label ?? $this->formatTimeRange($timeSlot->start_time, $timeSlot->end_time),
                        'start_time' => $timeSlot->start_time,
                        'end_time' => $timeSlot->end_time,
                        'is_available' => true,
                        'available_slots' => $minAvailable,
                        'service_availability' => $availableForServices,
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'time_slots' => $availableTimeSlots,
                'date' => $date
            ]);
            
        } catch (\Exception $e) {
            Log::error('getAvailableTimeSlots error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'time_slots' => []
            ], 500);
        }
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
    
    public function store(Request $request)
    {
        try {
            \Log::info('Store method called', $request->all());
            
            // AUTO-DETECT appointment type based on number of clients
            $clientCount = count($request->clients);
            $detectedType = $clientCount === 1 ? 'single' : 'multiple';
            
            $request->merge(['appointment_type' => $detectedType]);
            
            \Log::info('Auto-detected appointment type: ' . $detectedType . ' (Clients: ' . $clientCount . ')');
            
            $validator = Validator::make($request->all(), [
                'appointment_type' => 'required|in:single,multiple',
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
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Check if the selected date is working
            $dateToCheck = Carbon::parse($request->appointment_date);
            $dayType = $this->getDayType($dateToCheck);
            if ($dayType !== 'working') {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected date is not available for appointments. Please choose a working day.'
                ], 422);
            }
            
            // Group clients by service type
            $clientsByService = [];
            foreach ($request->clients as $client) {
                $service = $client['service'];
                if (!isset($clientsByService[$service])) {
                    $clientsByService[$service] = 0;
                }
                $clientsByService[$service]++;
            }
            
            DB::beginTransaction();
            
            try {
                $timeSlot = TimeSlot::find($request->appointment_time_slot_id);
                if (!$timeSlot) {
                    throw new \Exception('Invalid time slot selected');
                }
                
                // Check availability for each service BEFORE booking
                foreach ($clientsByService as $service => $count) {
                    $available = $this->getAvailableSlots($request->appointment_date, $request->appointment_time_slot_id, $service);
                    
                    if ($available < $count) {
                        DB::rollback();
                        $serviceNames = [
                            'reg' => 'Registration',
                            'updating' => 'Correction/Updating',
                            'inquiry' => 'Status Inquiry'
                        ];
                        return response()->json([
                            'success' => false,
                            'message' => "Not enough slots for {$serviceNames[$service]}. Only {$available} slots available. You need {$count} slots."
                        ], 422);
                    }
                }
                
                // Generate appointment number and reference code
                $date = Carbon::now()->format('Ymd');
                $last = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
                $appointmentNumber = 'PSA-' . $date . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
                $referenceCode = 'REF-' . strtoupper(uniqid());
                
                // Create appointment
                $appointment = new Appointment();
                $appointment->appointment_number = $appointmentNumber;
                $appointment->type = $request->appointment_type;
                $appointment->appointment_date = $request->appointment_date;
                $appointment->time_slot_id = $request->appointment_time_slot_id;
                $appointment->contact_name = $request->contact_name;
                $appointment->contact_email = $request->contact_email;
                $appointment->contact_mobile = $request->contact_mobile;
                $appointment->reference_code = $referenceCode;
                $appointment->status = 'pending';
                $appointment->metadata = json_encode([
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'auto_detected_type' => true,
                    'original_client_count' => $clientCount
                ]);
                
                // Save location data
                if ($request->filled('user_lat')) {
                    $appointment->user_lat = $request->user_lat;
                }
                if ($request->filled('user_lng')) {
                    $appointment->user_lng = $request->user_lng;
                }
                if ($request->filled('user_city')) {
                    $appointment->user_city = $request->user_city;
                }
                if ($request->filled('user_address')) {
                    $appointment->user_address = $request->user_address;
                }
                if ($request->filled('user_zipcode')) {
                    $appointment->user_zipcode = $request->user_zipcode;
                }
                
                $appointment->save();
                
                // Store clients
                $clientsData = [];
                $clientsList = [];
                
                foreach ($request->clients as $index => $clientData) {
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
                        $client->has_trn = $clientData['has_trn'] ?? null;
                        $client->trn_number = ($clientData['has_trn'] ?? false) ? ($clientData['trn_number'] ?? null) : null;
                    }
                    
                    $client->save();
                    
                    $fullName = trim($clientData['first_name'] . ' ' . ($clientData['middle_name'] ? $clientData['middle_name'] . ' ' : '') . $clientData['last_name']);
                    if (!empty($clientData['suffix'])) {
                        $fullName .= ' ' . $clientData['suffix'];
                    }
                    
                    $clientsList[] = [
                        'client_number' => $clientNumber,
                        'name' => $fullName,
                        'service' => $clientData['service'],
                        'service_name' => $this->getServiceName($clientData['service'])
                    ];
                    
                    $clientsData[] = $clientData;
                }
                
                DB::commit();
                
                // Prepare time slot label for email
                $timeSlotLabel = $timeSlot->label ?? $this->formatTimeRange($timeSlot->start_time, $timeSlot->end_time);
                
                // Send email confirmation with time slot
                $emailSent = false;
                if ($appointment->contact_email) {
                    try {
                        $emailSent = $this->mailService->sendAppointmentConfirmation(
                            $appointment, 
                            $clientsData,
                            $timeSlotLabel  // Pass the time slot label here
                        );
                    } catch (\Exception $e) {
                        \Log::warning('Email sending failed but appointment was saved: ' . $e->getMessage());
                    }
                }
                
                $successMessage = 'Appointment created successfully!';
                if ($emailSent) {
                    $successMessage .= ' A confirmation email has been sent to your email address.';
                } elseif ($appointment->contact_email) {
                    $successMessage .= ' We could not send a confirmation email. Please save your reference code.';
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'email_sent' => $emailSent,
                    'appointment' => [
                        'number' => $appointment->appointment_number,
                        'reference_code' => $appointment->reference_code,
                        'date' => Carbon::parse($appointment->appointment_date)->format('F d, Y'),
                        'time' => $timeSlotLabel,
                        'clients_count' => count($request->clients),
                        'type' => $appointment->type,
                        'location_city' => $appointment->user_city ?? null,
                        'contact_name' => $appointment->contact_name,
                        'contact_mobile' => $appointment->contact_mobile,
                        'contact_email' => $appointment->contact_email,
                        'clients_list' => $clientsList
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Transaction failed: ' . $e->getMessage());
                \Log::error($e->getTraceAsString());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ], 500);
            }
            
        } catch (\Exception $e) {
            \Log::error('Store method error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getServiceName($code)
    {
        $services = [
            'reg' => 'National ID Registration',
            'updating' => 'Correction/Updating',
            'inquiry' => 'Status Inquiry / Retrieval Of TRN / Other Concern'
        ];
        return $services[$code] ?? $code;
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
    
    public function getLocationStats(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
            
            $stats = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
                ->whereNotNull('user_city')
                ->selectRaw('user_city, COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending')
                ->selectRaw('SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed')
                ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                ->selectRaw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled')
                ->groupBy('user_city')
                ->orderBy('total', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting location stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get location statistics'
            ], 500);
        }
    }
    
    public function getPsaCoordinates()
    {
        return response()->json([
            'success' => true,
            'lat' => self::PSA_LAT,
            'lng' => self::PSA_LNG,
            'address' => 'Capt. Vicente Roa Street, Brgy. 31, Cagayan de Oro City, 9000 Misamis Oriental, Philippines'
        ]);
    }
}