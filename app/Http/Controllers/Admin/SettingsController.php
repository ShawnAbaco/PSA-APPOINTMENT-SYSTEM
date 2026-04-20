<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AppointmentSlot;
use App\Models\ServiceSlotsConfig;
use App\Models\WorkingDaysDefault;
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
        
        // Get service configurations
        $serviceConfigs = ServiceSlotsConfig::all();
        
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
        ];
        
        // Override with database values
        foreach ($allSettings as $setting) {
            $key = $setting->key;
            $value = $setting->value;
            
            // Map database keys to simple keys for view
            $simpleKey = str_replace('appointment.', '', $key);
            $simpleKey = str_replace('notification.', '', $simpleKey);
            
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
        
        return view('admin.settings.index', compact('settings', 'serviceConfigs'));
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
            // Service capacities
            'reg_capacity' => 'nullable|integer|min:0|max:100',
            'correction_capacity' => 'nullable|integer|min:0|max:100',
            'ephilid_capacity' => 'nullable|integer|min:0|max:100',
            'trn_capacity' => 'nullable|integer|min:0|max:100',
        ]);
        
        // Map form field names to database keys and types
        $settingsMap = [
            'advance_booking_days' => ['key' => 'appointment.advance_booking_days', 'type' => 'number', 'group' => 'appointment'],
            'cancellation_hours' => ['key' => 'appointment.cancellation_hours', 'type' => 'number', 'group' => 'appointment'],
            'enable_email' => ['key' => 'notification.enable_email', 'type' => 'boolean', 'group' => 'notification'],
            'enable_per_service_limits' => ['key' => 'enable_per_service_limits', 'type' => 'boolean', 'group' => 'appointment'],
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
        // ========== END WORKING DAYS UPDATE ==========
        
        // Update service capacities in service_slots_config table
        $serviceCapacities = [
            'reg' => $request->reg_capacity,
            'correction' => $request->correction_capacity,
            'ephilid' => $request->ephilid_capacity,
            'trn' => $request->trn_capacity,
        ];
        
        foreach ($serviceCapacities as $code => $capacity) {
            if ($capacity !== null) {
                ServiceSlotsConfig::where('service_code', $code)->update([
                    'default_capacity' => $capacity
                ]);
            }
        }
        
        // Clear cache
        Cache::forget('app_settings');
        
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
    
    /**
     * Sync all working slots with current service capacities
     */
    public function syncAllSlots(Request $request)
    {
        try {
            // Get current service capacities
            $serviceConfigs = ServiceSlotsConfig::all()->pluck('default_capacity', 'service_code')->toArray();
            
            $updatedCount = AppointmentSlot::where('day_type', 'working')
                ->update([
                    'reg_capacity' => $serviceConfigs['reg'] ?? 10,
                    'correction_capacity' => $serviceConfigs['correction'] ?? 5,
                    'ephilid_capacity' => $serviceConfigs['ephilid'] ?? 3,
                    'trn_capacity' => $serviceConfigs['trn'] ?? 2,
                ]);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$updatedCount} working slots with current service capacities."
            ]);
        } catch (\Exception $e) {
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
}