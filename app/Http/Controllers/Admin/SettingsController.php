<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AppointmentSlot;
use App\Models\SlotCapacityRule;
use App\Models\SlotCapacityOverride;
use App\Models\WorkingDaysDefault;
use App\Models\WorkingDaysOverride;
use App\Models\TimeSlot;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        // Get all settings from database
        $allSettings = Setting::all();
        
        // Get service configurations (for default capacities)
        $serviceConfigs = collect();
        try {
            $serviceConfigs = DB::table('slot_capacity_rules')
                ->select('service_code', DB::raw('AVG(reg_capacity) as default_capacity'))
                ->where('day_type', 'weekday')
                ->groupBy('service_code')
                ->get();
            
            // If no rules exist, create default structure
            if ($serviceConfigs->isEmpty()) {
                $serviceConfigs = collect([
                    (object)['service_code' => 'reg', 'default_capacity' => 10],
                    (object)['service_code' => 'updating', 'default_capacity' => 5],
                    (object)['service_code' => 'inquiry', 'default_capacity' => 8],
                ]);
            }
        } catch (\Exception $e) {
            $serviceConfigs = collect([
                (object)['service_code' => 'reg', 'default_capacity' => 10],
                (object)['service_code' => 'updating', 'default_capacity' => 5],
                (object)['service_code' => 'inquiry', 'default_capacity' => 8],
            ]);
        }
        
        // Get working days from working_days_defaults table
        $workingDaysDefaults = WorkingDaysDefault::all()->pluck('is_working', 'day_of_week')->toArray();
        $workingDaysList = [];
        foreach ($workingDaysDefaults as $day => $isWorking) {
            if ($isWorking) {
                $workingDaysList[] = $day;
            }
        }
        $workingDaysValue = implode(',', $workingDaysList);
        
        // Initialize settings array with defaults
        $settings = [
            'advance_booking_days' => 30,
            'cancellation_hours' => 24,
            'enable_email' => true,
            'working_days' => $workingDaysValue ?: '1,2,3,4,5',
            'email_host' => 'smtp.gmail.com',
            'email_port' => 587,
            'email_encryption' => 'tls',
            'email_username' => '',
            'email_password' => '',
            'email_from_address' => 'noreply@psa.gov.ph',
            'email_from_name' => 'PSA Appointment System',
            'enable_per_service_limits' => true,
            'enable_time_slots' => true,
            'time_slots_default_capacity' => 4,
        ];
        
        // Override with database values
        foreach ($allSettings as $setting) {
            $key = $setting->key;
            $value = $setting->value;
            
            // Map database keys to simple keys for view
            $simpleKey = str_replace('appointment.', '', $key);
            $simpleKey = str_replace('notification.', '', $simpleKey);
            $simpleKey = str_replace('time_slots.', '', $simpleKey);
            
            // Handle different types
            if ($setting->type === 'password') {
                $settings[$simpleKey] = !empty($value) ? '********' : '';
            } elseif ($setting->type === 'boolean') {
                $settings[$simpleKey] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } elseif ($setting->type === 'number') {
                $settings[$simpleKey] = (int)$value;
            } else {
                $settings[$simpleKey] = $value;
            }
        }
        
        // Get time slots for the view
        $timeSlots = TimeSlot::orderBy('display_order')->get();
            $capacityRules = SlotCapacityRule::all()->groupBy('time_slot_id');

        
        return view('admin.settings.index', compact('settings', 'serviceConfigs', 'timeSlots', 'capacityRules'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'advance_booking_days' => 'nullable|integer|min:1|max:365',
            'cancellation_hours' => 'nullable|integer|min:1|max:168',
            'enable_email' => 'nullable|in:true,false',
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|between:1,7',
            'email_host' => 'nullable|string',
            'email_port' => 'nullable|integer',
            'email_encryption' => 'nullable|in:tls,ssl',
            'email_username' => 'nullable|string',
            'email_password' => 'nullable|string',
            'email_from_address' => 'nullable|email',
            'email_from_name' => 'nullable|string',
            'enable_per_service_limits' => 'nullable|in:true,false',
            'enable_time_slots' => 'nullable|in:true,false',
            'time_slots_default_capacity' => 'nullable|integer|min:1|max:50',
            // Service capacities for slot_capacity_rules
            'reg_capacity' => 'nullable|integer|min:0|max:100',
            'updating_capacity' => 'nullable|integer|min:0|max:100',
            'inquiry_capacity' => 'nullable|integer|min:0|max:100',
        ]);
        
        // Map form field names to database keys and types
        $settingsMap = [
            'advance_booking_days' => ['key' => 'appointment.advance_booking_days', 'type' => 'number', 'group' => 'appointment'],
            'cancellation_hours' => ['key' => 'appointment.cancellation_hours', 'type' => 'number', 'group' => 'appointment'],
            'enable_email' => ['key' => 'notification.enable_email', 'type' => 'boolean', 'group' => 'notification'],
            'enable_per_service_limits' => ['key' => 'enable_per_service_limits', 'type' => 'boolean', 'group' => 'appointment'],
            'enable_time_slots' => ['key' => 'time_slots.enabled', 'type' => 'boolean', 'group' => 'appointment'],
            'time_slots_default_capacity' => ['key' => 'time_slots.default_capacity', 'type' => 'number', 'group' => 'appointment'],
            'email_host' => ['key' => 'email_host', 'type' => 'text', 'group' => 'email'],
            'email_port' => ['key' => 'email_port', 'type' => 'number', 'group' => 'email'],
            'email_encryption' => ['key' => 'email_encryption', 'type' => 'text', 'group' => 'email'],
            'email_username' => ['key' => 'email_username', 'type' => 'text', 'group' => 'email'],
            'email_password' => ['key' => 'email_password', 'type' => 'password', 'group' => 'email'],
            'email_from_address' => ['key' => 'email_from_address', 'type' => 'text', 'group' => 'email'],
            'email_from_name' => ['key' => 'email_from_name', 'type' => 'text', 'group' => 'email'],
        ];
        
        foreach ($validated as $field => $value) {
            if (isset($settingsMap[$field])) {
                $config = $settingsMap[$field];
                
                // Skip password update if it's the placeholder value
                if ($field === 'email_password' && $value === '********') {
                    continue;
                }
                
                // Handle boolean values
                if ($config['type'] === 'boolean') {
                    $value = $value === 'true' ? 'true' : 'false';
                }
                
                // Encrypt password type
                if ($config['type'] === 'password' && !empty($value)) {
                    $value = Crypt::encryptString($value);
                }
                
                // Update or create setting
                Setting::updateOrCreate(
                    ['key' => $config['key']],
                    [
                        'value' => $value,
                        'group' => $config['group'],
                        'type' => $config['type'],
                    ]
                );
            }
        }
        
        // ========== UPDATE WORKING DAYS IN working_days_defaults TABLE ==========
        if ($request->has('working_days')) {
            $workingDaysArray = $request->working_days;
            
            // Update each day's is_working status
            for ($day = 1; $day <= 7; $day++) {
                $isWorking = in_array($day, $workingDaysArray) ? 1 : 0;
                
                WorkingDaysDefault::updateOrCreate(
                    ['day_of_week' => $day],
                    [
                        'is_working' => $isWorking,
                        'updated_at' => now(),
                    ]
                );
            }
            
            // Also save to settings table for backward compatibility
            $workingDaysValue = implode(',', $workingDaysArray);
            Setting::updateOrCreate(
                ['key' => 'working_days'],
                [
                    'value' => $workingDaysValue,
                    'group' => 'appointment',
                    'type' => 'text',
                    'description' => 'Working days (1=Mon,2=Tue,3=Wed,4=Thu,5=Fri,6=Sat,7=Sun)'
                ]
            );
        }
        
        // ========== UPDATE SERVICE CAPACITIES IN slot_capacity_rules TABLE ==========
        // This updates the DEFAULT capacity rules for all time slots
        $dayTypes = ['weekday', 'saturday', 'sunday', 'holiday'];
        $timeSlots = TimeSlot::where('is_active', true)->get();
        
        $serviceCapacities = [
            'reg' => $request->reg_capacity ?? 10,
            'updating' => $request->updating_capacity ?? 5,
            'inquiry' => $request->inquiry_capacity ?? 8,
        ];
        
        foreach ($timeSlots as $timeSlot) {
            foreach ($dayTypes as $dayType) {
                // Determine capacity based on day type
                switch ($dayType) {
                    case 'weekday':
                        $regCapacity = $serviceCapacities['reg'];
                        $updatingCapacity = $serviceCapacities['updating'];
                        $inquiryCapacity = $serviceCapacities['inquiry'];
                        break;
                    case 'saturday':
                        $regCapacity = max(1, floor($serviceCapacities['reg'] / 2));
                        $updatingCapacity = max(1, floor($serviceCapacities['updating'] / 2));
                        $inquiryCapacity = max(1, floor($serviceCapacities['inquiry'] / 2));
                        break;
                    case 'sunday':
                    case 'holiday':
                        $regCapacity = 0;
                        $updatingCapacity = 0;
                        $inquiryCapacity = 0;
                        break;
                    default:
                        $regCapacity = $serviceCapacities['reg'];
                        $updatingCapacity = $serviceCapacities['updating'];
                        $inquiryCapacity = $serviceCapacities['inquiry'];
                }
                
                SlotCapacityRule::updateOrCreate(
                    [
                        'time_slot_id' => $timeSlot->id,
                        'day_type' => $dayType,
                    ],
                    [
                        'reg_capacity' => $regCapacity,
                        'updating_capacity' => $updatingCapacity,
                        'inquiry_capacity' => $inquiryCapacity,
                    ]
                );
            }
        }
        
        // Clear cache
        Cache::forget('app_settings');
        
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
    
    /**
     * Sync all working slots with current service capacities
     * This updates slot_capacity_overrides for existing slots that are NOT manually overridden
     */
    public function syncAllSlots(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Get current service capacities from rules (weekday defaults)
            $defaultRules = SlotCapacityRule::where('day_type', 'weekday')
                ->select('time_slot_id', 'reg_capacity', 'updating_capacity', 'inquiry_capacity')
                ->get()
                ->keyBy('time_slot_id');
            
            // Get all working slots (not holiday, not special, not manually overridden)
            $slotsToSync = AppointmentSlot::where('day_type', 'working')
                ->whereDoesntHave('override') // Only sync slots without manual overrides
                ->get();
            
            $updatedCount = 0;
            
            foreach ($slotsToSync as $slot) {
                $defaultRule = $defaultRules->get($slot->time_slot_id);
                if ($defaultRule) {
                    SlotCapacityOverride::updateOrCreate(
                        [
                            'date' => $slot->date,
                            'time_slot_id' => $slot->time_slot_id,
                        ],
                        [
                            'reg_capacity' => $defaultRule->reg_capacity,
                            'updating_capacity' => $defaultRule->updating_capacity,
                            'inquiry_capacity' => $defaultRule->inquiry_capacity,
                            'reason' => 'Auto-synced from default rules',
                        ]
                    );
                    $updatedCount++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$updatedCount} working slots with current service capacities."
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Sync slots error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing slots: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        try {
            $mailService = new MailService();
            
            $testAppointment = new \stdClass();
            $testAppointment->appointment_number = 'TEST-001';
            $testAppointment->reference_code = 'TEST-REF-' . date('Ymd');
            $testAppointment->appointment_date = now();
            $testAppointment->type = 'single';
            $testAppointment->contact_name = 'Test User';
            $testAppointment->contact_mobile = '09123456789';
            $testAppointment->contact_email = $request->email;
            
            $testClients = [
                [
                    'first_name' => 'Test',
                    'middle_name' => '',
                    'last_name' => 'User',
                    'suffix' => '',
                    'service' => 'reg'
                ]
            ];
            
            $result = $mailService->sendAppointmentConfirmation($testAppointment, $testClients);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test email sent successfully to ' . $request->email
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test email. Check your SMTP settings and logs.'
                ], 500);
            }
            
        } catch (\Exception $e) {
            \Log::error('Test email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeTimeSlot(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_label' => 'nullable|string|max:255',
            'capacity_per_slot' => 'required|integer|min:1|max:50',
        ]);
        
        $displayOrder = TimeSlot::max('display_order') + 1;
        
        $timeSlot = TimeSlot::create([
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
            'label' => $validated['slot_label'] ?? date('g:i A', strtotime($validated['start_time'])) . ' - ' . date('g:i A', strtotime($validated['end_time'])),
            'display_order' => $displayOrder,
            'is_active' => true,
        ]);
        
        // Create default capacity rules for this new time slot
        $dayTypes = ['weekday', 'saturday', 'sunday', 'holiday'];
        foreach ($dayTypes as $dayType) {
            switch ($dayType) {
                case 'weekday':
                    $regCapacity = 10;
                    $updatingCapacity = 5;
                    $inquiryCapacity = 8;
                    break;
                case 'saturday':
                    $regCapacity = 5;
                    $updatingCapacity = 3;
                    $inquiryCapacity = 4;
                    break;
                default:
                    $regCapacity = 0;
                    $updatingCapacity = 0;
                    $inquiryCapacity = 0;
            }
            
            SlotCapacityRule::create([
                'time_slot_id' => $timeSlot->id,
                'day_type' => $dayType,
                'reg_capacity' => $regCapacity,
                'updating_capacity' => $updatingCapacity,
                'inquiry_capacity' => $inquiryCapacity,
            ]);
        }
        
        return response()->json(['success' => true, 'time_slot' => $timeSlot]);
    }

    public function updateTimeSlot(Request $request, $id)
    {
        $timeSlot = TimeSlot::findOrFail($id);
        
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'slot_label' => 'nullable|string|max:255',
            'capacity_per_slot' => 'required|integer|min:1|max:50',
            'is_active' => 'required|boolean',
        ]);
        
        $timeSlot->update([
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'label' => $validated['slot_label'],
            'is_active' => $validated['is_active'],
        ]);
        
        return response()->json(['success' => true]);
    }

    public function destroyTimeSlot($id)
    {
        $timeSlot = TimeSlot::findOrFail($id);
        
        // Check if there are appointments using this time slot
        $hasAppointments = \App\Models\Appointment::where('time_slot_id', $id)->exists();
        
        if ($hasAppointments) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete time slot with existing appointments.'
            ], 400);
        }
        
        // Delete associated capacity rules
        SlotCapacityRule::where('time_slot_id', $id)->delete();
        SlotCapacityOverride::where('time_slot_id', $id)->delete();
        
        // Delete the time slot
        $timeSlot->delete();
        
        return response()->json(['success' => true]);
    }
    /**
 * Clear application cache
 */
public function clearCache(Request $request)
{
    try {
        Cache::flush();
        return response()->json(['success' => true, 'message' => 'Cache cleared successfully.']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
/**
 * Save default capacity rules for time slots
 */
public function saveCapacityRules(Request $request)
{
    try {
        DB::beginTransaction();
        
        $capacities = $request->input('capacities', []);
        
        foreach ($capacities as $timeSlotId => $dayTypes) {
            foreach ($dayTypes as $dayType => $services) {
                SlotCapacityRule::updateOrCreate(
                    [
                        'time_slot_id' => $timeSlotId,
                        'day_type' => $dayType,
                    ],
                    [
                        'reg_capacity' => $services['reg'] ?? 0,
                        'updating_capacity' => $services['updating'] ?? 0,
                        'inquiry_capacity' => $services['inquiry'] ?? 0,
                    ]
                );
            }
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Capacity rules saved successfully!'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Save capacity rules error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Error saving capacity rules: ' . $e->getMessage()
        ], 500);
    }
}

}