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
        
        foreach ($timeSlots as $timeSlot) {
            // Create WORKING day rule (Tuesday to Friday)
            SlotCapacityRule::updateOrCreate(
                [
                    'time_slot_id' => $timeSlot->id,
                    'day_type' => 'working',
                ],
                [
                    'reason' => null, // No reason needed for working days
                    'reg_capacity' => 4,
                    'updating_capacity' => 4,
                    'inquiry_capacity' => 4,
                ]
            );
            
            // Create NON_WORKING day rule (Monday, Saturday, Sunday, Holidays)
            SlotCapacityRule::updateOrCreate(
                [
                    'time_slot_id' => $timeSlot->id,
                    'day_type' => 'non_working',
                ],
                [
                    'reason' => 'Regular non-working day', // Default reason
                    'reg_capacity' => 0,
                    'updating_capacity' => 0,
                    'inquiry_capacity' => 0,
                ]
            );
        }
        
        $this->command->info('Slot capacity rules seeded successfully!');
        $this->command->info('Working days: 4 slots per service');
        $this->command->info('Non-working days: 0 slots per service');
    }
}