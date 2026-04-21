<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentSlot;
use App\Models\TimeSlot;
use App\Models\Setting;
use App\Models\ServiceSlotsConfig;
use Carbon\Carbon;

class AppointmentSlotSeeder extends Seeder
{
    public function run()
    {
        // Get all active time slots
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('display_order')->get();
        
        if ($timeSlots->isEmpty()) {
            $this->command->error('No time slots found. Please run TimeSlotSeeder first.');
            return;
        }
        
        // Get default capacities per service
        $serviceCapacities = [
            'reg' => 10,
            'updating' => 5,
            'inquiry' => 8
        ];
        
        // Get default capacity per time slot
        $defaultTimeSlotCapacity = 4;
        $capacitySetting = Setting::where('key', 'time_slots.default_capacity')->first();
        if ($capacitySetting && $capacitySetting->value) {
            $defaultTimeSlotCapacity = (int)$capacitySetting->value;
        }
        
        // Try to get service capacities from settings if available
        $serviceCapacitySetting = Setting::where('key', 'service_capacities')->first();
        if ($serviceCapacitySetting && $serviceCapacitySetting->value) {
            // Check if value is already an array or needs to be decoded
            if (is_array($serviceCapacitySetting->value)) {
                $savedCapacities = $serviceCapacitySetting->value;
            } else {
                $savedCapacities = json_decode($serviceCapacitySetting->value, true);
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
        
        // Get per time slot capacities from settings (if configured differently per time slot)
        $timeSlotCapacities = [];
        try {
            $timeSlotSettings = \DB::table('time_slot_capacity_settings')->get();
            foreach ($timeSlotSettings as $setting) {
                $timeSlotCapacities[$setting->time_slot_id][$setting->day_type] = $setting->capacity;
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }
        
        $generatedCount = 0;
        $skippedCount = 0;
        
        // Generate slots for next 90 days
        for ($i = 0; $i < 90; $i++) {
            $date = Carbon::today()->addDays($i);
            $dateString = $date->format('Y-m-d');
            
            // Determine day type
            $dayOfWeek = $date->dayOfWeek;
            $dayType = 'weekday';
            if ($dayOfWeek == 6) {
                $dayType = 'saturday';
            } elseif ($dayOfWeek == 7) {
                $dayType = 'sunday';
            }
            
            foreach ($timeSlots as $timeSlot) {
                // Get capacity for this time slot based on day type
                $slotCapacity = $defaultTimeSlotCapacity;
                
                // Check if there's a specific setting for this time slot and day type
                if (isset($timeSlotCapacities[$timeSlot->id][$dayType])) {
                    $slotCapacity = $timeSlotCapacities[$timeSlot->id][$dayType];
                } elseif (isset($timeSlotCapacities[$timeSlot->id]['weekday'])) {
                    $slotCapacity = $timeSlotCapacities[$timeSlot->id]['weekday'];
                }
                
                // Skip if capacity is 0 (no appointments for this time slot on this day type)
                if ($slotCapacity <= 0) {
                    $skippedCount++;
                    continue;
                }
                
                // Calculate per-service capacities based on total slot capacity
                // Distribute proportionally based on service capacities
                $totalServiceCapacity = array_sum($serviceCapacities);
                $regCapacity = (int)round(($serviceCapacities['reg'] / $totalServiceCapacity) * $slotCapacity);
                $updatingCapacity = (int)round(($serviceCapacities['updating'] / $totalServiceCapacity) * $slotCapacity);
                $inquiryCapacity = (int)round(($serviceCapacities['inquiry'] / $totalServiceCapacity) * $slotCapacity);
                
                // Ensure at least 1 capacity for each service if total capacity allows
                if ($slotCapacity >= 3) {
                    $regCapacity = max(1, $regCapacity);
                    $updatingCapacity = max(1, $updatingCapacity);
                    $inquiryCapacity = max(1, $inquiryCapacity);
                }
                
                // Adjust to match total capacity
                $total = $regCapacity + $updatingCapacity + $inquiryCapacity;
                if ($total !== $slotCapacity && $slotCapacity > $total) {
                    $regCapacity += ($slotCapacity - $total);
                }
                
                AppointmentSlot::firstOrCreate(
                    [
                        'date' => $dateString,
                        'time_slot_id' => $timeSlot->id
                    ],
                    [
                        'day_type' => 'working',
                        'total_capacity' => $slotCapacity,
                        'reg_capacity' => $regCapacity,
                        'updating_capacity' => $updatingCapacity,
                        'inquiry_capacity' => $inquiryCapacity,
                        'reg_booked' => 0,
                        'updating_booked' => 0,
                        'inquiry_booked' => 0,
                        'reg_available' => $regCapacity,
                        'updating_available' => $updatingCapacity,
                        'inquiry_available' => $inquiryCapacity,
                        'notes' => null,
                    ]
                );
                $generatedCount++;
            }
        }
        
        $this->command->info("Appointment slots generated for the next 90 days.");
        $this->command->info("Generated: {$generatedCount} slots, Skipped: {$skippedCount} slots");
        $this->command->info("Time slots per day: " . $timeSlots->count());
        $this->command->info("Default capacity per time slot: {$defaultTimeSlotCapacity}");
        $this->command->info("Service capacities - Registration: {$serviceCapacities['reg']}, Updating: {$serviceCapacities['updating']}, Inquiry: {$serviceCapacities['inquiry']}");
    }
}