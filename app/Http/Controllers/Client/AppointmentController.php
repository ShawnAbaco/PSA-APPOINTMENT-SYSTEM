<?php
// app/Http/Controllers/Client/AppointmentController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\AppointmentSlot;
use App\Models\Service;
use App\Models\Setting;
use App\Models\WorkingDaysDefault;
use App\Models\WorkingDaysOverride;
use App\Models\ServiceSlotsConfig;
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
            
            // Get service capacities from config table
            $serviceConfigs = [];
            try {
                $serviceConfigs = ServiceSlotsConfig::all()->pluck('default_capacity', 'service_code')->toArray();
            } catch (\Exception $e) {
                Log::warning('ServiceSlotsConfig table not ready: ' . $e->getMessage());
            }
            
            return view('client.appointment', compact('services', 'serviceConfigs'));
        } catch (\Exception $e) {
            Log::error('Error loading appointment page: ' . $e->getMessage());
            $services = collect();
            $serviceConfigs = [];
            return view('client.appointment', compact('services', 'serviceConfigs'));
        }
    }
    
    public function getAvailableDates(Request $request)
    {
        try {
            $month = (int)$request->get('month', date('n'));
            $year = (int)$request->get('year', date('Y'));
            $clientCount = (int)$request->get('client_count', 1);
            $selectedService = $request->get('service');
            $servicesParam = $request->get('services');
            
            // Get list of services to check
            $servicesToCheck = [];
            if ($servicesParam) {
                $servicesToCheck = explode(',', $servicesParam);
            } elseif ($selectedService) {
                $servicesToCheck = [$selectedService];
            } else {
                // UPDATED: Use reg, updating, inquiry
                $servicesToCheck = ['reg', 'updating', 'inquiry'];
            }
            
            $advanceDays = 30;
            $advanceSetting = Setting::where('key', 'appointment.advance_booking_days')->first();
            if ($advanceSetting) {
                $advanceDays = (int)$advanceSetting->value;
            }
            
            // Get working days - handle if table doesn't exist
            $workingDays = [1,2,3,4,5]; // Default Mon-Fri
            try {
                $workingDays = WorkingDaysDefault::where('is_working', true)->pluck('day_of_week')->toArray();
                if (empty($workingDays)) {
                    $workingDays = [1,2,3,4,5];
                }
            } catch (\Exception $e) {
                Log::warning('WorkingDaysDefault table not ready: ' . $e->getMessage());
            }
            
            $maxDate = Carbon::now()->addDays($advanceDays);
            $dates = [];
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day);
                $dateKey = $date->format('Y-m-d');
                $dayOfWeek = $date->dayOfWeek;
                
                // Skip past dates
                if ($date->lt(Carbon::now()->startOfDay())) {
                    continue;
                }
                
                // Skip dates beyond max booking
                if ($date->gt($maxDate)) {
                    continue;
                }
                
                // Check working day override
                $isWorkingDay = in_array($dayOfWeek, $workingDays);
                try {
                    $override = WorkingDaysOverride::where('date', $dateKey)->first();
                    if ($override) {
                        $isWorkingDay = $override->is_working;
                    }
                } catch (\Exception $e) {
                    // Override table might not exist yet
                }
                
                if (!$isWorkingDay) {
                    continue;
                }
                
                // Check appointment_slots table
                $slot = AppointmentSlot::where('date', $dateKey)->first();
                
                // Skip if holiday
                if ($slot && $slot->day_type === 'holiday') {
                    continue;
                }
                
                $serviceAvailability = [];
                $allAvailable = true;
                
                foreach ($servicesToCheck as $service) {
                    // Get default capacity
                    $defaultCapacity = 10;
                    try {
                        $serviceConfig = ServiceSlotsConfig::where('service_code', $service)->first();
                        if ($serviceConfig) {
                            $defaultCapacity = $serviceConfig->default_capacity;
                        }
                    } catch (\Exception $e) {
                        // Use default
                    }
                    
                    $serviceCapacity = $defaultCapacity;
                    $bookedCount = 0;
                    
                    if ($slot) {
                        switch ($service) {
                            case 'reg':
                                $serviceCapacity = $slot->reg_capacity ?? $defaultCapacity;
                                $bookedCount = $slot->reg_booked ?? 0;
                                break;
                            case 'updating':
                                $serviceCapacity = $slot->updating_capacity ?? $defaultCapacity;
                                $bookedCount = $slot->updating_booked ?? 0;
                                break;
                            case 'inquiry':
                                $serviceCapacity = $slot->inquiry_capacity ?? $defaultCapacity;
                                $bookedCount = $slot->inquiry_booked ?? 0;
                                break;
                            default:
                                $serviceCapacity = $defaultCapacity;
                        }
                        
                        // Apply half day logic
                        if ($slot->day_type === 'half_day') {
                            $serviceCapacity = (int)ceil($serviceCapacity / 2);
                        }
                    }
                    
                    $availableSlots = max(0, $serviceCapacity - $bookedCount);
                    $serviceAvailability[$service] = $availableSlots;
                    
                    if ($availableSlots < $clientCount) {
                        $allAvailable = false;
                    }
                }
                
                if ($allAvailable) {
                    $dates[] = [
                        'date' => $dateKey,
                        'available' => true,
                        'available_slots' => min($serviceAvailability),
                        'service_availability' => $serviceAvailability,
                        'day' => $date->format('l'),
                        'display_date' => $date->format('F d, Y'),
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
    
    public function store(Request $request)
    {
        try {
            \Log::info('Store method called', $request->all());
            
            $validator = Validator::make($request->all(), [
                'appointment_type' => 'required|in:single,multiple',
                'appointment_date' => 'required|date|after_or_equal:today',
                'contact_name' => 'required|string|max:255',
                'contact_mobile' => 'required|string|max:20',
                'contact_email' => 'nullable|email|max:255',
                'clients' => 'required|array|min:1|max:10',
                'clients.*.first_name' => 'required|string|max:255',
                'clients.*.last_name' => 'required|string|max:255',
                'clients.*.sex' => 'required|in:Male,Female',
                'clients.*.birthdate' => 'required|date|before:today',
                'clients.*.service' => 'required|in:reg,updating,inquiry',
                'clients.*.has_trn' => 'nullable|boolean',
                'clients.*.trn_number' => 'nullable|string|size:29|regex:/^\d+$/',
                // ========== LOCATION VALIDATION ==========
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
                // Get or create slot for this date
                $slot = AppointmentSlot::firstOrNew(['date' => $request->appointment_date]);
                
                if (!$slot->exists) {
                    // Initialize slot with default capacities
                    $serviceConfigs = \App\Models\ServiceSlotsConfig::all()->pluck('default_capacity', 'service_code')->toArray();
                    $slot->date = $request->appointment_date;
                    $slot->day_type = 'working';
                    $slot->reg_capacity = $serviceConfigs['reg'] ?? 10;
                    $slot->updating_capacity = $serviceConfigs['updating'] ?? 5;
                    $slot->inquiry_capacity = $serviceConfigs['inquiry'] ?? 8;
                    $slot->reg_booked = 0;
                    $slot->updating_booked = 0;
                    $slot->inquiry_booked = 0;
                    $slot->save();
                }
                
                // Check availability for each service
                foreach ($clientsByService as $service => $count) {
                    $capacity = 0;
                    $booked = 0;
                    
                    switch ($service) {
                        case 'reg':
                            $capacity = $slot->reg_capacity;
                            $booked = $slot->reg_booked;
                            break;
                        case 'updating':
                            $capacity = $slot->updating_capacity;
                            $booked = $slot->updating_booked;
                            break;
                        case 'inquiry':
                            $capacity = $slot->inquiry_capacity;
                            $booked = $slot->inquiry_booked;
                            break;
                    }
                    
                    // Apply half day logic
                    if ($slot->day_type === 'half_day') {
                        $capacity = ceil($capacity / 2);
                    }
                    
                    if (($booked + $count) > $capacity) {
                        DB::rollback();
                        $serviceNames = [
                            'reg' => 'Registration',
                            'updating' => 'Correction/Updating',
                            'inquiry' => 'STATUS INQUIRY / RETRIEVAL OF TRN / OTHER CONCERN'
                        ];
                        return response()->json([
                            'success' => false,
                            'message' => "Not enough slots for {$serviceNames[$service]}. Only {$capacity} slots available."
                        ], 422);
                    }
                }
                
                // Generate appointment number
                $date = Carbon::now()->format('Ymd');
                $last = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
                $appointmentNumber = 'PSA-' . $date . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
                $referenceCode = 'REF-' . strtoupper(uniqid());
                
                // Create appointment with location data
                $appointment = new Appointment();
                $appointment->appointment_number = $appointmentNumber;
                $appointment->type = $request->appointment_type;
                $appointment->appointment_date = $request->appointment_date;
                $appointment->contact_name = $request->contact_name;
                $appointment->contact_email = $request->contact_email;
                $appointment->contact_mobile = $request->contact_mobile;
                $appointment->reference_code = $referenceCode;
                $appointment->status = 'pending';
                $appointment->metadata = json_encode([
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip()
                ]);
                
                // ========== SAVE LOCATION DATA ==========
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
                
                if ($request->filled('user_city')) {
                    \Log::info('Location saved for appointment', [
                        'appointment_number' => $appointmentNumber,
                        'city' => $request->user_city,
                        'lat' => $request->user_lat,
                        'lng' => $request->user_lng
                    ]);
                }
                // ========== END LOCATION DATA ==========
                
                $appointment->save();
                
                // Store client data for email
                $clientsData = [];
                
                // Create clients and update slot counts
                foreach ($request->clients as $clientData) {
                    $client = new AppointmentClient();
                    $client->client_number = AppointmentClient::generateClientNumber();
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
                    
                    // Save TRN data for inquiry service
                    if ($clientData['service'] === 'inquiry') {
                        $client->has_trn = $clientData['has_trn'] ?? null;
                        $client->trn_number = ($clientData['has_trn'] ?? false) ? ($clientData['trn_number'] ?? null) : null;
                    }
                    
                    $client->save();
                    
                    $clientsData[] = $clientData;
                    
                    // Update slot booked count
                    switch ($clientData['service']) {
                        case 'reg':
                            $slot->reg_booked += 1;
                            break;
                        case 'updating':
                            $slot->updating_booked += 1;
                            break;
                        case 'inquiry':
                            $slot->inquiry_booked += 1;
                            break;
                    }
                }
                
                // Recalculate available counts
                $slot->reg_available = $slot->reg_capacity - $slot->reg_booked;
                $slot->updating_available = $slot->updating_capacity - $slot->updating_booked;
                $slot->inquiry_available = $slot->inquiry_capacity - $slot->inquiry_booked;
                
                // Apply half day logic if needed
                if ($slot->day_type === 'half_day') {
                    $slot->reg_available = ceil($slot->reg_capacity / 2) - $slot->reg_booked;
                    $slot->updating_available = ceil($slot->updating_capacity / 2) - $slot->updating_booked;
                    $slot->inquiry_available = ceil($slot->inquiry_capacity / 2) - $slot->inquiry_booked;
                }
                
                // Ensure no negative values
                $slot->reg_available = max(0, $slot->reg_available);
                $slot->updating_available = max(0, $slot->updating_available);
                $slot->inquiry_available = max(0, $slot->inquiry_available);
                
                $slot->save();
                
                DB::commit();
                
                // Send email confirmation
                $emailSent = false;
                if ($appointment->contact_email) {
                    try {
                        $emailSent = $this->mailService->sendAppointmentConfirmation($appointment, $clientsData);
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
                        'clients_count' => count($request->clients),
                        'location_city' => $appointment->user_city ?? null
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Transaction failed: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ], 500);
            }
            
        } catch (\Exception $e) {
            \Log::error('Store method error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function checkAvailability(Request $request)
    {
        try {
            $date = $request->get('date');
            $clientCount = $request->get('client_count', 1);
            $service = $request->get('service', 'reg');
            
            $slot = AppointmentSlot::where('date', $date)->first();
            
            // Get service capacity
            $serviceConfig = \App\Models\ServiceSlotsConfig::where('service_code', $service)->first();
            $defaultCapacity = $serviceConfig ? $serviceConfig->default_capacity : 10;
            
            $capacity = $defaultCapacity;
            $bookedCount = 0;
            
            if ($slot) {
                switch ($service) {
                    case 'reg':
                        $capacity = $slot->reg_capacity;
                        $bookedCount = $slot->reg_booked;
                        break;
                    case 'updating':
                        $capacity = $slot->updating_capacity;
                        $bookedCount = $slot->updating_booked;
                        break;
                    case 'inquiry':
                        $capacity = $slot->inquiry_capacity;
                        $bookedCount = $slot->inquiry_booked;
                        break;
                }
                
                if ($slot->day_type === 'half_day') {
                    $capacity = ceil($capacity / 2);
                }
            }
            
            $availableSlots = $capacity - $bookedCount;
            
            return response()->json([
                'success' => true,
                'available' => $availableSlots >= $clientCount,
                'slots_left' => max(0, $availableSlots),
                'capacity' => $capacity,
                'booked' => $bookedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking availability: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability'
            ], 500);
        }
    }
    
    // ========== API endpoint to get location statistics ==========
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
    
    // ========== Get PSA coordinates for map ==========
    public function getPsaCoordinates()
    {
        return response()->json([
            'success' => true,
            'lat' => self::PSA_LAT,
            'lng' => self::PSA_LNG,
            'address' => 'Capt. Vicente Roa Street, Brgy. 31, Cagayan de Oro City, 9000 Misamis Oriental, Philippines'
        ]);
    }
    
    private function generateAppointmentNumber()
    {
        $date = Carbon::now()->format('Ymd');
        $last = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
        return 'PSA-' . $date . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
    
    private function generateReferenceCode()
    {
        return 'REF-' . strtoupper(uniqid());
    }
}