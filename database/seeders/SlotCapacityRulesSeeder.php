<?php
// database/seeders/SlotCapacityRulesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;
use App\Models\SlotCapacityRule;
use Carbon\Carbon;

class SlotCapacityRulesSeeder extends Seeder
{
    public function run()
    {
        $timeSlots = TimeSlot::where('is_active', true)->get();
        
        $dayTypes = ['weekday', 'saturday', 'sunday', 'holiday'];
        
        foreach ($timeSlots as $timeSlot) {
            foreach ($dayTypes as $dayType) {
                // Set different capacities based on day type
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
                    case 'sunday':
                        $regCapacity = 0;
                        $updatingCapacity = 0;
                        $inquiryCapacity = 0;
                        break;
                    case 'holiday':
                        $regCapacity = 0;
                        $updatingCapacity = 0;
                        $inquiryCapacity = 0;
                        break;
                    default:
                        $regCapacity = 4;
                        $updatingCapacity = 2;
                        $inquiryCapacity = 3;
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
        
        $this->command->info('Slot capacity rules seeded successfully!');
    }
}