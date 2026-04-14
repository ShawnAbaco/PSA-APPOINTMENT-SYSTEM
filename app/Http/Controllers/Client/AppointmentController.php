<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentClient;
use App\Models\AppointmentSlot;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function index()
    {
        try {
            $services = Service::where('is_active', true)->orderBy('display_order')->get();
            
            // Get daily capacity manually
            $dailyCapacity = 20;
            $capacitySetting = Setting::where('key', 'appointment.daily_capacity')->first();
            if ($capacitySetting) {
                $dailyCapacity = (int)$capacitySetting->value;
            }
            
            return view('client.appointment', compact('services', 'dailyCapacity'));
        } catch (\Exception $e) {
            Log::error('Error loading appointment page: ' . $e->getMessage());
            // Fallback with empty services if database not ready
            $services = collect();
            $dailyCapacity = 20;
            return view('client.appointment', compact('services', 'dailyCapacity'));
        }
    }
    
   public function getAvailableDates(Request $request)
{
    try {
        $month = (int)$request->get('month', date('n'));
        $year = (int)$request->get('year', date('Y'));
        $clientCount = (int)$request->get('client_count', 1);
        
        // Get default capacity from settings
        $defaultCapacity = 20;
        $capacitySetting = Setting::where('key', 'appointment.daily_capacity')->first();
        if ($capacitySetting) {
            $defaultCapacity = (int)$capacitySetting->value;
        }
        
        $advanceDays = 30;
        $advanceSetting = Setting::where('key', 'appointment.advance_booking_days')->first();
        if ($advanceSetting) {
            $advanceDays = (int)$advanceSetting->value;
        }
        
        $maxDate = Carbon::now()->addDays($advanceDays);
        $dates = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        
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
            
            // Check appointment_slots table first
            $slot = AppointmentSlot::where('date', $dateKey)->first();
            
            // Skip if holiday
            if ($slot && $slot->is_holiday) {
                continue;
            }
            
            // Get capacity for this specific day (from slot or default)
            $dailyCapacity = $slot ? $slot->total_capacity : $defaultCapacity;
            
            // FIX: Count total clients, not appointments
            $totalBookedClients = AppointmentClient::whereHas('appointment', function($query) use ($dateKey) {
                $query->whereDate('appointment_date', $dateKey)
                      ->whereIn('status', ['pending', 'confirmed']);
            })->count();
            
            $availableSlots = $dailyCapacity - $totalBookedClients;
            
            if ($availableSlots >= $clientCount) {
                $dates[] = [
                    'date' => $dateKey,
                    'available_slots' => $availableSlots,
                    'day' => $date->format('l'),
                    'display_date' => $date->format('F d, Y'),
                    'capacity' => $dailyCapacity,
                    'is_holiday' => $slot ? $slot->is_holiday : false,
                    'booked_clients' => $totalBookedClients
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'dates' => $dates,
            'month' => Carbon::create($year, $month)->format('F Y'),
            'default_capacity' => $defaultCapacity
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
            'clients.*.service' => 'required|in:reg,correction,ephilid,trn',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Get daily capacity
        $dailyCapacity = 20;
        $capacitySetting = Setting::where('key', 'appointment.daily_capacity')->first();
        if ($capacitySetting) {
            $dailyCapacity = (int)$capacitySetting->value;
        }
        
        // ========== FIX 1: CHECK SLOT AVAILABILITY BY COUNTING CLIENTS ==========
        // Check slot availability - Count total clients, not appointments
        $totalBookedClients = AppointmentClient::whereHas('appointment', function($query) use ($request) {
            $query->whereDate('appointment_date', $request->appointment_date)
                  ->whereIn('status', ['pending', 'confirmed']);
        })->count();
        
        $totalNeeded = count($request->clients);
        
        if (($totalBookedClients + $totalNeeded) > $dailyCapacity) {
            return response()->json([
                'success' => false,
                'message' => 'No available slots for selected date. Only ' . ($dailyCapacity - $totalBookedClients) . ' slots left.'
            ], 422);
        }
        // ========== END OF FIX 1 ==========
        
        DB::beginTransaction();
        
        try {
            // Generate appointment number
            $date = Carbon::now()->format('Ymd');
            $last = Appointment::whereDate('created_at', Carbon::today())->count() + 1;
            $appointmentNumber = 'PSA-' . $date . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
            $referenceCode = 'REF-' . strtoupper(uniqid());
            
            // Create appointment
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
            $appointment->save();
            
            // Create clients
            foreach ($request->clients as $clientData) {
                $client = new AppointmentClient();
                $client->client_number = AppointmentClient::generateClientNumber(); // Add this line
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
                $client->save();
            }
            
            // ========== FIX 2: UPDATE APPOINTMENT SLOT WITH CLIENT COUNT ==========
            // Update or create slot record for this date
            try {
                $slot = AppointmentSlot::firstOrCreate(
                    ['date' => $request->appointment_date],
                    [
                        'total_capacity' => $dailyCapacity,
                        'booked_count' => 0,
                        'available_count' => $dailyCapacity,
                    ]
                );
                
                // Add the total number of clients, not just 1
                $slot->booked_count = ($slot->booked_count ?? 0) + $totalNeeded;
                $slot->available_count = $slot->total_capacity - $slot->booked_count;
                $slot->save();
                
                \Log::info('Appointment slot updated', [
                    'date' => $request->appointment_date,
                    'booked_count' => $slot->booked_count,
                    'available_count' => $slot->available_count,
                    'clients_added' => $totalNeeded
                ]);
                
            } catch (\Exception $e) {
                \Log::warning('Could not update appointment slot: ' . $e->getMessage());
                // Don't fail the transaction if slot update fails
            }
            // ========== END OF FIX 2 ==========
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment created successfully!',
                'appointment' => [
                    'number' => $appointment->appointment_number,
                    'reference_code' => $appointment->reference_code,
                    'date' => Carbon::parse($appointment->appointment_date)->format('F d, Y'),
                    'clients_count' => count($request->clients)
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
        
        // Get daily capacity manually
        $dailyCapacity = 20;
        $capacitySetting = Setting::where('key', 'appointment.daily_capacity')->first();
        if ($capacitySetting) {
            $dailyCapacity = (int)$capacitySetting->value;
        }
        
        // FIX: Count total clients, not appointments
        $totalBookedClients = AppointmentClient::whereHas('appointment', function($query) use ($date) {
            $query->whereDate('appointment_date', $date)
                  ->whereIn('status', ['pending', 'confirmed']);
        })->count();
        
        $availableSlots = $dailyCapacity - $totalBookedClients;
        
        return response()->json([
            'success' => true,
            'available' => $availableSlots >= $clientCount,
            'slots_left' => $availableSlots,
            'daily_capacity' => $dailyCapacity,
            'booked_clients' => $totalBookedClients
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error checking availability: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to check availability'
        ], 500);
    }
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