<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentSlot;
use App\Models\Setting;
use App\Models\ServiceSlotsConfig;
use Carbon\Carbon;

class AppointmentSlotSeeder extends Seeder
{
    public function run()
    {
        // Get default capacities per service
        $serviceCapacities = [
            'reg' => 10,
            'updating' => 5,
            'inquiry' => 8
        ];
        
        // Try to get from settings if available
        $capacitySetting = Setting::where('key', 'service_capacities')->first();
        if ($capacitySetting && $capacitySetting->value) {
            // Check if value is already an array or needs to be decoded
            if (is_array($capacitySetting->value)) {
                $savedCapacities = $capacitySetting->value;
            } else {
                $savedCapacities = json_decode($capacitySetting->value, true);
            }
            
            if (is_array($savedCapacities)) {
                $serviceCapacities = array_merge($serviceCapacities, $savedCapacities);
            }
        }
        
        // Try to get from service slots config table
        try {
            $configs = ServiceSlotsConfig::all();
            foreach ($configs as $config) {
                if (isset($serviceCapacities[$config->service_code])) {
                    $serviceCapacities[$config->service_code] = $config->default_capacity;
                }
            }
        } catch (\Exception $e) {
            // Table might not exist yet, use defaults
        }
        
        // Generate slots for next 90 days
        for ($i = 0; $i < 90; $i++) {
            $date = Carbon::today()->addDays($i);
            $dateString = $date->format('Y-m-d');
            
            // Calculate total capacity
            $totalCapacity = $serviceCapacities['reg'] + $serviceCapacities['updating'] + $serviceCapacities['inquiry'];
            
            AppointmentSlot::firstOrCreate(
                ['date' => $dateString],
                [
                    'day_type' => 'working',
                    'total_capacity' => $totalCapacity,
                    'reg_capacity' => $serviceCapacities['reg'],
                    'updating_capacity' => $serviceCapacities['updating'],
                    'inquiry_capacity' => $serviceCapacities['inquiry'],
                    'reg_booked' => 0,
                    'updating_booked' => 0,
                    'inquiry_booked' => 0,
                    'reg_available' => $serviceCapacities['reg'],
                    'updating_available' => $serviceCapacities['updating'],
                    'inquiry_available' => $serviceCapacities['inquiry'],
                    'notes' => null,
                ]
            );
        }
        
        $this->command->info('Appointment slots generated for the next 90 days.');
        $this->command->info('Capacities - Registration: ' . $serviceCapacities['reg'] . 
                             ', Updating: ' . $serviceCapacities['updating'] . 
                             ', Inquiry: ' . $serviceCapacities['inquiry']);
    }
}