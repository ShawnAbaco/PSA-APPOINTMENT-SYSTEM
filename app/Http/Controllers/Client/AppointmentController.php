<?php
// app/Http/Controllers/Client/AppointmentController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\DocumentRequirement;
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
    
    const PSA_LAT = 8.4815315;
    const PSA_LNG = 124.6549067;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    // In your AppointmentController.php, update the index method:

 public function index()
    {
        // Fetch all active requirements from database
        $requirements = DocumentRequirement::where('is_active', true)
            ->orderBy('service')
            ->orderBy('age_group')
            ->get()
            ->groupBy(['service', 'age_group']);
        
        return view('client.appointment', compact('requirements'));
    }
    
    /**
     * Get day type for a specific date
     * Priority: WorkingDaysOverride (for holidays) > WorkingDaysDefault
     * Returns: 'working' or 'non_working'
     */
    private function getDayType($date)
    {
        try {
            $dateString = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
            
            // STEP 1: Check override FIRST (for holidays/special non-working days)
            $override = WorkingDaysOverride::where('date', $dateString)->first();
            if ($override) {
                // Holiday overrides make the day non_working
                return 'non_working';
            }
            
            // STEP 2: Get day name from Carbon (monday, tuesday, etc.)
            $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
            $dayName = strtolower($carbonDate->format('l'));
            
            // STEP 3: Get from working_days_defaults table
            $default = WorkingDaysDefault::where('day_name', $dayName)->first();
            
            if (!$default) {
                Log::warning("No working day configuration found for: {$dayName}");
                return 'non_working';
            }
            
            return $default->day_type; // 'working' or 'non_working'
            
        } catch (\Exception $e) {
            Log::error('Error in getDayType: ' . $e->getMessage());
            return 'non_working';
        }
    }

    /**
     * Get capacity for a specific date, time slot, and service
     * 
     * PRIORITY ORDER (CORRECT):
     * 1. SlotCapacityOverride (admin can set custom capacity for ANY date - HIGHEST PRIORITY)
     * 2. SlotCapacityRule based on day_type from working_days
     * 3. Default fallback (4 for working, 0 for non_working)
     */
    private function getCapacity($date, $timeSlotId, $service)
    {
        try {
            $dateString = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
            
            // ========== STEP 1: Check for SlotCapacityOverride (HIGHEST PRIORITY) ==========
            // This allows admin to override capacity for specific dates (e.g., increase to 10 for a special event)
            $override = SlotCapacityOverride::where('date', $dateString)
                ->where('time_slot_id', $timeSlotId)
                ->first();
            
            if ($override) {
                Log::info("Using OVERRIDE for {$dateString} - Slot {$timeSlotId}: R={$override->reg_capacity}, U={$override->updating_capacity}, S={$override->inquiry_capacity}");
                
                switch ($service) {
                    case 'reg': return $override->reg_capacity ?? 0;
                    case 'updating': return $override->updating_capacity ?? 0;
                    case 'inquiry': return $override->inquiry_capacity ?? 0;
                    default: return 0;
                }
            }
            
            // ========== STEP 2: Get day type from working days configuration ==========
            $dayType = $this->getDayType($dateString);
            
            // ========== STEP 3: Get capacity rule based on day_type ==========
            $rule = SlotCapacityRule::where('time_slot_id', $timeSlotId)
                ->where('day_type', $dayType)
                ->first();
            
            if ($rule) {
                Log::info("Using RULE for {$dateString} (day_type: {$dayType}) - Slot {$timeSlotId}: R={$rule->reg_capacity}, U={$rule->updating_capacity}, S={$rule->inquiry_capacity}");
                
                switch ($service) {
                    case 'reg': return $rule->reg_capacity;
                    case 'updating': return $rule->updating_capacity;
                    case 'inquiry': return $rule->inquiry_capacity;
                    default: return 0;
                }
            }
            
            // ========== STEP 4: Default fallback values ==========
            if ($dayType === 'working') {
                return 4; // Default working day capacity
            }
            
            return 0; // For non_working days, default to 0 (closed)
            
        } catch (\Exception $e) {
            Log::warning('Error getting capacity: ' . $e->getMessage());
            return 4;
        }
    }
    
    /**
     * Get booked count for a specific date, time slot, and service
     * Counts ONLY confirmed/pending appointments (not cancelled)
     */
    /**
 * Get booked count for a specific date, time slot, and service
 * Counts clients with their individual time slots
 */
private function getBookedCount($date, $timeSlotId, $service)
{
    try {
        $dateString = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
        
        return AppointmentClient::whereHas('appointment', function($query) use ($dateString) {
            $query->where('appointment_date', $dateString)
                ->whereIn('status', ['pending', 'confirmed']);
        })->where('time_slot_id', $timeSlotId)
          ->where('service', $service)
          ->count();
          
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
        $available = max(0, $capacity - $booked);
        
        Log::info("Availability: Date={$date}, Slot={$timeSlotId}, Service={$service}, Capacity={$capacity}, Booked={$booked}, Available={$available}");
        
        return $available;
    }

    public function getAvailableDates(Request $request)
    {
        try {
            $month = (int)$request->get('month', date('n'));
            $year = (int)$request->get('year', date('Y'));
            $clientCount = (int)$request->get('client_count', 1);
            $servicesParam = $request->get('services', '');
            
            $servicesToCheck = !empty($servicesParam) ? explode(',', $servicesParam) : ['reg', 'updating', 'inquiry'];
            
            $advanceDays = 30;
            $advanceSetting = Setting::where('key', 'appointment.advance_booking_days')->first();
            if ($advanceSetting) {
                $advanceDays = (int)$advanceSetting->value;
            }
            
            $maxDate = Carbon::now()->addDays($advanceDays);
            $dates = [];
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            
            $timeSlots = TimeSlot::where('is_active', true)->get();
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day);
                $dateKey = $date->format('Y-m-d');
                
                if ($date->lt(Carbon::now()->startOfDay())) continue;
                if ($date->gt($maxDate)) continue;
                
                // Check if date is a working day
                $dayType = $this->getDayType($date);
                if ($dayType !== 'working') continue;
                
                if ($timeSlots->isEmpty()) continue;
                
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
                        foreach ($servicesToCheck as $service) {
                            $available = $this->getAvailableSlots($dateKey, $timeSlot->id, $service);
                            $serviceAvailability[$service] += $available;
                        }
                    }
                }
                
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
        
        // First check if the date is a working day
        $dayType = $this->getDayType($date);
        if ($dayType !== 'working') {
            return response()->json([
                'success' => true,
                'time_slots' => [],
                'date' => $date,
                'message' => 'This date is not a working day.'
            ]);
        }
        
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        $availableTimeSlots = [];
        
        foreach ($timeSlots as $timeSlot) {
            $availableForServices = [];
            $hasAnyAvailability = false;
            $minAvailable = PHP_INT_MAX;
            
            foreach ($servicesToCheck as $service) {
                $available = $this->getAvailableSlots($date, $timeSlot->id, $service);
                $availableForServices[$service] = $available;
                $minAvailable = min($minAvailable, $available);
                
                if ($available > 0) {
                    $hasAnyAvailability = true;
                }
            }
            
            // Show time slot if ANY service has available slots (for per-client selection)
            // Also include availability data for each service so frontend can filter
            if ($hasAnyAvailability) {
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
        Log::info('Store method called', $request->all());
        
        $clientCount = count($request->clients);
        $detectedType = $clientCount === 1 ? 'single' : 'multiple';
        $request->merge(['appointment_type' => $detectedType]);
        
        $validator = Validator::make($request->all(), [
            'appointment_type' => 'required|in:single,multiple',
            'appointment_date' => 'required|date|after_or_equal:today',
            'contact_name' => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'clients' => 'required|array|min:1|max:4',
            'clients.*.first_name' => 'required|string|max:255',
            'clients.*.last_name' => 'required|string|max:255',
            'clients.*.sex' => 'required|in:Male,Female',
            'clients.*.birthdate' => 'required|date|before:today',
            'clients.*.service' => 'required|in:reg,updating,inquiry',
            'clients.*.time_slot_id' => 'required|exists:time_slots,id',
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
        
        $dateToCheck = Carbon::parse($request->appointment_date);
        $dayType = $this->getDayType($dateToCheck);
        if ($dayType !== 'working') {
            return response()->json([
                'success' => false,
                'message' => 'The selected date is not available for appointments. Please choose a working day.'
            ], 422);
        }
        
        // Group clients by service AND time slot for availability checking
        $clientsByServiceAndSlot = [];
        foreach ($request->clients as $client) {
            $key = $client['service'] . '_' . $client['time_slot_id'];
            if (!isset($clientsByServiceAndSlot[$key])) {
                $clientsByServiceAndSlot[$key] = [
                    'service' => $client['service'],
                    'time_slot_id' => $client['time_slot_id'],
                    'count' => 0
                ];
            }
            $clientsByServiceAndSlot[$key]['count']++;
        }
        
        DB::beginTransaction();
        
        try {
            // Check availability for each service and time slot combination
            foreach ($clientsByServiceAndSlot as $item) {
                $available = $this->getAvailableSlots($request->appointment_date, $item['time_slot_id'], $item['service']);
                
                if ($available < $item['count']) {
                    DB::rollback();
                    $serviceNames = ['reg' => 'Registration', 'updating' => 'Correction/Updating', 'inquiry' => 'Status Inquiry'];
                    $timeSlot = TimeSlot::find($item['time_slot_id']);
                    $slotLabel = $timeSlot ? ($timeSlot->label ?? $this->formatTimeRange($timeSlot->start_time, $timeSlot->end_time)) : 'Selected time slot';
                    
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough slots for {$serviceNames[$item['service']]} at {$slotLabel}. Only {$available} slots available. You need {$item['count']} slots."
                    ], 422);
                }
            }
            
            // Generate appointment number and reference code
            $date = Carbon::now()->format('Ymd');
            $last = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
            $appointmentNumber = 'PSA-' . $date . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
            $referenceCode = 'REF-' . strtoupper(uniqid());
            
            // Create appointment (time_slot_id is null since each client has their own)
            // Create appointment (use first client's time slot as the main slot)
            $appointment = new Appointment();
            $appointment->appointment_number = $appointmentNumber;
            $appointment->type = $request->appointment_type;
            $appointment->appointment_date = $request->appointment_date;
            // Use the first client's time slot ID for the main appointment (since time_slot_id cannot be null)
            $appointment->time_slot_id = $request->clients[0]['time_slot_id'];
            $appointment->contact_name = $request->contact_name;
            $appointment->contact_email = $request->contact_email;
            $appointment->contact_mobile = $request->contact_mobile;
            $appointment->reference_code = $referenceCode;
            $appointment->status = 'pending';
            $appointment->metadata = json_encode([
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'per_client_time_slots' => true
            ]);
            
            if ($request->filled('user_lat')) $appointment->user_lat = $request->user_lat;
            if ($request->filled('user_lng')) $appointment->user_lng = $request->user_lng;
            if ($request->filled('user_city')) $appointment->user_city = $request->user_city;
            if ($request->filled('user_address')) $appointment->user_address = $request->user_address;
            if ($request->filled('user_zipcode')) $appointment->user_zipcode = $request->user_zipcode;
            
            $appointment->save();
            
            // Store clients with their individual time slots
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
                $client->time_slot_id = $clientData['time_slot_id'];
                $client->requirements_acknowledged = true;
                $client->acknowledged_at = now();
                
                if ($clientData['service'] === 'inquiry') {
                    $client->has_trn = $clientData['has_trn'] ?? null;
                    $client->trn_number = ($clientData['has_trn'] ?? false) ? ($clientData['trn_number'] ?? null) : null;
                }
                
                $client->save();
                
                $fullName = trim($clientData['first_name'] . ' ' . ($clientData['middle_name'] ? $clientData['middle_name'] . ' ' : '') . $clientData['last_name']);
                if (!empty($clientData['suffix'])) $fullName .= ' ' . $clientData['suffix'];
                
                $timeSlot = TimeSlot::find($clientData['time_slot_id']);
                $timeSlotLabel = $timeSlot ? ($timeSlot->label ?? $this->formatTimeRange($timeSlot->start_time, $timeSlot->end_time)) : 'Time slot selected';
                
                $clientsList[] = [
                    'client_number' => $clientNumber,
                    'name' => $fullName,
                    'service' => $clientData['service'],
                    'service_name' => $this->getServiceName($clientData['service']),
                    'time_slot' => $timeSlotLabel
                ];
                
                $clientsData[] = $clientData;
            }
            
            DB::commit();
            
            $timeSlotLabel = 'Multiple time slots - see applicant details';
            $emailSent = false;
            
            if ($appointment->contact_email) {
                try {
                    $emailSent = $this->mailService->sendAppointmentConfirmation($appointment, $clientsData, $timeSlotLabel);
                } catch (\Exception $e) {
                    Log::warning('Email sending failed: ' . $e->getMessage());
                }
            }
            
            $successMessage = 'Appointment created successfully!';
            if ($emailSent) $successMessage .= ' A confirmation email has been sent to your email address.';
            elseif ($appointment->contact_email) $successMessage .= ' We could not send a confirmation email. Please save your reference code.';
            
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'email_sent' => $emailSent,
                'appointment' => [
                    'number' => $appointment->appointment_number,
                    'reference_code' => $appointment->reference_code,
                    'date' => Carbon::parse($appointment->appointment_date)->format('F d, Y'),
                    'clients_count' => count($request->clients),
                    'type' => $appointment->type,
                    'contact_name' => $appointment->contact_name,
                    'contact_mobile' => $appointment->contact_mobile,
                    'contact_email' => $appointment->contact_email,
                    'clients_list' => $clientsList
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
        
    } catch (\Exception $e) {
        Log::error('Store method error: ' . $e->getMessage());
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

   /**
 * Get requirements for a specific service and birthdate
 * This ensures the logic is handled on the backend
 */
public function getRequirements(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'service' => 'required|in:reg,updating,inquiry',
            'birthdate' => 'nullable|date'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $service = $request->service;
        $birthdate = $request->birthdate;
        
        // Determine age group - FIXED LOGIC
        $ageGroup = 'standard';
        if ($birthdate) {
            $birthDate = Carbon::parse($birthdate);
            $today = Carbon::now();
            
            // Calculate exact age in years
            $age = $birthDate->diffInYears($today);
            
            // If birthdate is in the future or invalid, treat as child (1-4 years old)
            if ($birthDate->greaterThan($today)) {
                $age = 0;
            }
            
            // Child: 1-4 years old (including infants)
            // standard: 5 years old and above
            if ($age >= 0 && $age <= 4) {
                $ageGroup = 'child';
            } else {
                $ageGroup = 'standard';
            }
            
            Log::info("Age calculation: Birthdate={$birthdate}, Age={$age}, Group={$ageGroup}");
        }
        
        // Fetch requirements from database
        $requirements = DocumentRequirement::where('service', $service)
            ->where('age_group', $ageGroup)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
        
        // Build HTML response
        $html = $this->buildRequirementsHtml($service, $ageGroup, $requirements);
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'service' => $service,
            'age_group' => $ageGroup,
            'age' => $age ?? null,
            'requirements_count' => $requirements->count()
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching requirements: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to load requirements. Please try again.'
        ], 500);
    }
}

/**
 * Build HTML for requirements display - simple list without checkboxes
 */
private function buildRequirementsHtml($service, $ageGroup, $requirements)
{
    $serviceNames = [
        'reg' => 'National ID Registration',
        'updating' => 'Correction/Updating',
        'inquiry' => 'Status Inquiry / Retrieval Of TRN / Other Concern'
    ];
    
    $serviceName = $serviceNames[$service] ?? $service;
    
    // Update the age group label to be more clear
    if ($ageGroup === 'child') {
        $ageGroupLabel = 'Child (1-4 years old)';
    } else {
        $ageGroupLabel = 'Standard (5 years old and above)';
    }
    
    $serviceIcon = $this->getServiceIcon($service);
    
    $html = '<div class="requirements-container">';
    $html .= '<div class="requirements-header">';
    $html .= '<h4><i class="fas ' . $serviceIcon . '"></i> ' . e($serviceName) . ' - ' . e($ageGroupLabel) . '</h4>';
    $html .= '</div>';
    
    $html .= '<div class="requirements-simple-list">';
    $html .= '<ul>';
    
    if ($requirements->count() > 0) {
        foreach ($requirements as $req) {
            $html .= '<li>' . e($req->requirement) . '</li>';
        }
    } else {
        $html .= '<li>No specific requirements found. Please contact PSA for more information.</li>';
    }
    
    $html .= '</ul>';
    $html .= '</div>';
    
    // Add warning note
    $html .= '<div class="warning-note">';
    $html .= '<i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> Bring <strong>original documents</strong>. No photocopies accepted for primary validation.';
    $html .= '</div>';
    
    // Special note for child registration
    if ($service === 'reg' && $ageGroup === 'child') {
        $html .= '<div class="info-note">';
        $html .= '<i class="fas fa-child"></i> <strong>Note for Children (1-4 years old):</strong> Parent or legal guardian must accompany the child during the appointment. The guardian must bring a valid ID.';
        $html .= '</div>';
    }
    
    // Special note for infants
    if ($service === 'reg' && $ageGroup === 'child') {
        $html .= '<div class="info-note">';
        $html .= '<i class="fas fa-baby"></i> <strong>For Infants:</strong> If the child is below 1 year old, please bring the child\'s Birth Certificate and the parent\'s valid ID.';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}
private function getServiceIcon($service)
{
    $icons = [
        'reg' => 'fa-id-card',
        'updating' => 'fa-pen',
        'inquiry' => 'fa-question-circle'
    ];
    return $icons[$service] ?? 'fa-file-alt';
}
}