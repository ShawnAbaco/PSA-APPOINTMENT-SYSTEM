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
            ->where('day_type', 'working')
            ->groupBy('service_code')
            ->get();
        
        if ($serviceConfigs->isEmpty()) {
            $serviceConfigs = collect([
                (object)['service_code' => 'reg', 'default_capacity' => 4],
                (object)['service_code' => 'updating', 'default_capacity' => 4],
                (object)['service_code' => 'inquiry', 'default_capacity' => 4],
            ]);
        }
    } catch (\Exception $e) {
        $serviceConfigs = collect([
            (object)['service_code' => 'reg', 'default_capacity' => 4],
            (object)['service_code' => 'updating', 'default_capacity' => 4],
            (object)['service_code' => 'inquiry', 'default_capacity' => 4],
        ]);
    }
    
    // Load working days from database correctly
    $workingDaysList = [];
    try {
        // Get all working days from the working_days_defaults table
        $workingDaysDefaults = WorkingDaysDefault::where('day_type', 'working')->get();
        
        foreach ($workingDaysDefaults as $day) {
            $dayNumber = $this->getDayNumber($day->day_name);
            if ($dayNumber) {
                $workingDaysList[] = (string)$dayNumber;
            }
        }
        
        // If no working days found in database, set defaults (Monday to Friday = 1,2,3,4,5)
        if (empty($workingDaysList)) {
            $workingDaysList = ['1', '2', '3', '4', '5']; // Monday to Friday
        }
    } catch (\Exception $e) {
        \Log::error('Error loading working days: ' . $e->getMessage());
        $workingDaysList = ['1', '2', '3', '4', '5']; // Fallback to Monday-Friday
    }
    
    $workingDaysValue = implode(',', $workingDaysList);
    
    // Initialize settings array with defaults
    $settings = [
        'advance_booking_days' => 30,
        'cancellation_hours' => 24,
        'enable_email' => true,
        'working_days' => $workingDaysValue,
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
        
        $simpleKey = str_replace('appointment.', '', $key);
        $simpleKey = str_replace('notification.', '', $simpleKey);
        $simpleKey = str_replace('time_slots.', '', $simpleKey);
        
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
    
    /**
     * Convert day name to day number (1=Monday, 7=Sunday)
     */
    private function getDayNumber($dayName)
    {
        $days = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];
        return $days[strtolower($dayName)] ?? null;
    }
    
    /**
     * Convert day number to day name
     */
    private function getDayName($dayNumber)
    {
        $days = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];
        return $days[$dayNumber] ?? null;
    }
    
    /**
     * Sync all working slots with current service capacities
     * This updates slot_capacity_overrides for existing slots that are NOT manually overridden
     */
    public function syncAllSlots(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Get current service capacities from rules (working day defaults)
            $defaultRules = SlotCapacityRule::where('day_type', 'working')
                ->select('time_slot_id', 'reg_capacity', 'updating_capacity', 'inquiry_capacity')
                ->get()
                ->keyBy('time_slot_id');
            
            // Get all working slots (not manually overridden)
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
    
    /**
     * Test email configuration using .env settings
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        try {
            $mailService = new \App\Services\MailService();
            $result = $mailService->sendTestEmail($request->email);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test email sent successfully to ' . $request->email
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test email. Check your .env SMTP settings and logs.'
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
        // Working day rule (4 slots per service)
        SlotCapacityRule::create([
            'time_slot_id' => $timeSlot->id,
            'day_type' => 'working',
            'reason' => null,
            'reg_capacity' => 4,
            'updating_capacity' => 4,
            'inquiry_capacity' => 4,
        ]);
        
        // Non-working day rule (0 slots)
        SlotCapacityRule::create([
            'time_slot_id' => $timeSlot->id,
            'day_type' => 'non_working',
            'reason' => 'Regular non-working day',
            'reg_capacity' => 0,
            'updating_capacity' => 0,
            'inquiry_capacity' => 0,
        ]);
        
        return response()->json(['success' => true, 'time_slot' => $timeSlot]);
    }

    /**
     * Update Time Slot - Removed capacity_per_slot and is_active
     */
    /**
 * Update Time Slot - Fixed time format validation
 */
public function updateTimeSlot(Request $request, $id)
{
    $timeSlot = TimeSlot::findOrFail($id);
    
    // Convert time values from H:i:s to H:i format if needed
    $startTime = $request->start_time;
    $endTime = $request->end_time;
    
    // Remove seconds if present (convert from H:i:s to H:i)
    if (strlen($startTime) > 5) {
        $startTime = substr($startTime, 0, 5);
    }
    if (strlen($endTime) > 5) {
        $endTime = substr($endTime, 0, 5);
    }
    
    $validated = $request->validate([
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'slot_label' => 'nullable|string|max:255',
    ]);
    
    $timeSlot->update([
        'start_time' => $validated['start_time'] . ':00',
        'end_time' => $validated['end_time'] . ':00',
        'label' => $validated['slot_label'] ?? date('g:i A', strtotime($validated['start_time'])) . ' - ' . date('g:i A', strtotime($validated['end_time'])),
    ]);
    
    return response()->json(['success' => true, 'message' => 'Time slot updated successfully']);
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
            
            $capacities = $request->input('slot_capacities', []);
            
            foreach ($capacities as $timeSlotId => $services) {
                SlotCapacityRule::updateOrCreate(
                    [
                        'time_slot_id' => $timeSlotId,
                        'day_type' => 'working',
                    ],
                    [
                        'reg_capacity' => $services['reg'] ?? 0,
                        'updating_capacity' => $services['updating'] ?? 0,
                        'inquiry_capacity' => $services['inquiry'] ?? 0,
                    ]
                );
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

    /**
 * Update working days independently via AJAX
 */
public function updateWorkingDays(Request $request)
{
    try {
        \Log::info('=== WORKING DAYS AJAX UPDATE ===');
        \Log::info('Working days received: ', $request->all());
        
        $validated = $request->validate([
            'working_days' => 'nullable|array',
            'working_days.*' => 'integer|between:1,7'
        ]);
        
        $workingDaysArray = $request->working_days ?? [];
        
        // Convert day numbers to day names
        $dayMapping = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];
        
        $updatedCount = 0;
        
        // Update each day's working status
        for ($day = 1; $day <= 7; $day++) {
            $isWorking = in_array($day, $workingDaysArray);
            $dayName = $dayMapping[$day];
            $dayType = $isWorking ? 'working' : 'non_working';
            
            \Log::info("Saving day: {$dayName}, isWorking: " . ($isWorking ? 'true' : 'false') . ", day_type: {$dayType}");
            
            // First, try to find existing record
            $existingRecord = WorkingDaysDefault::where('day_name', $dayName)->first();
            
            if ($existingRecord) {
                // Update existing record
                $existingRecord->day_type = $dayType;
                $existingRecord->updated_at = now();
                $existingRecord->save();
                $updatedCount++;
                \Log::info("Updated existing record for {$dayName}");
            } else {
                // Create new record if doesn't exist
                WorkingDaysDefault::create([
                    'day_name' => $dayName,
                    'day_type' => $dayType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $updatedCount++;
                \Log::info("Created new record for {$dayName}");
            }
        }
        
        // Also update the settings table for backward compatibility
        $workingDaysValue = implode(',', $workingDaysArray);
        Setting::updateOrCreate(
            ['key' => 'working_days'],
            [
                'value' => $workingDaysValue,
                'group' => 'appointment',
                'type' => 'text',
                'description' => 'Working days (1=Mon,2=Tue,3=Wed,4=Thu,5=Fri,6=Sat,7=Sun)',
                'updated_at' => now(),
            ]
        );
        
        // Clear cache
        Cache::forget('app_settings');
        Cache::forget('working_days');
        
        \Log::info("Successfully updated {$updatedCount} working days records");
        
        return response()->json([
            'success' => true,
            'message' => 'Working days updated successfully! ' . $updatedCount . ' days updated.',
            'working_days' => $workingDaysArray
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error saving working days: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Error saving working days: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * Update appointment settings independently via AJAX
 */
public function updateAppointmentSettings(Request $request)
{
    try {
        \Log::info('=== APPOINTMENT SETTINGS AJAX UPDATE ===');
        \Log::info('Appointment settings received: ', $request->all());
        
        $validated = $request->validate([
            'advance_booking_days' => 'required|integer|min:1|max:365',
            'cancellation_hours' => 'required|integer|min:1|max:168',
            'enable_email' => 'required|in:true,false',
            'enable_per_service_limits' => 'required|in:true,false',
        ]);
        
        // Map form field names to database keys and types
        $settingsMap = [
            'advance_booking_days' => ['key' => 'appointment.advance_booking_days', 'type' => 'number', 'group' => 'appointment'],
            'cancellation_hours' => ['key' => 'appointment.cancellation_hours', 'type' => 'number', 'group' => 'appointment'],
            'enable_email' => ['key' => 'notification.enable_email', 'type' => 'boolean', 'group' => 'notification'],
            'enable_per_service_limits' => ['key' => 'enable_per_service_limits', 'type' => 'boolean', 'group' => 'appointment'],
        ];
        
        $updatedCount = 0;
        
        foreach ($validated as $field => $value) {
            if (isset($settingsMap[$field])) {
                $config = $settingsMap[$field];
                
                // Handle boolean values
                if ($config['type'] === 'boolean') {
                    $value = $value === 'true' ? 'true' : 'false';
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
                $updatedCount++;
                
                \Log::info("Updated setting: {$config['key']} = {$value}");
            }
        }
        
        // Clear cache
        Cache::forget('app_settings');
        
        \Log::info("Successfully updated {$updatedCount} appointment settings");
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment settings saved successfully!',
            'settings' => $validated
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error saving appointment settings: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Error saving appointment settings: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Reset appointment settings to defaults
 */
public function resetAppointmentSettings(Request $request)
{
    try {
        $defaults = [
            'advance_booking_days' => 30,
            'cancellation_hours' => 24,
            'enable_email' => 'true',
            'enable_per_service_limits' => 'true',
        ];
        
        $settingsMap = [
            'advance_booking_days' => ['key' => 'appointment.advance_booking_days', 'type' => 'number', 'group' => 'appointment'],
            'cancellation_hours' => ['key' => 'appointment.cancellation_hours', 'type' => 'number', 'group' => 'appointment'],
            'enable_email' => ['key' => 'notification.enable_email', 'type' => 'boolean', 'group' => 'notification'],
            'enable_per_service_limits' => ['key' => 'enable_per_service_limits', 'type' => 'boolean', 'group' => 'appointment'],
        ];
        
        foreach ($defaults as $field => $defaultValue) {
            $config = $settingsMap[$field];
            Setting::updateOrCreate(
                ['key' => $config['key']],
                [
                    'value' => $defaultValue,
                    'group' => $config['group'],
                    'type' => $config['type'],
                ]
            );
        }
        
        Cache::forget('app_settings');
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment settings reset to defaults!',
            'defaults' => $defaults
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error resetting appointment settings: ' . $e->getMessage()
        ], 500);
    }
}
}