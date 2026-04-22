<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentSlot;
use App\Models\TimeSlot;
use Carbon\Carbon;

class AppointmentSlotSeeder extends Seeder
{
    public function run()
    {
        $timeSlots = TimeSlot::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        if ($timeSlots->isEmpty()) {
            $this->command->error('No time slots found. Run TimeSlotSeeder first.');
            return;
        }

        $generated = 0;

        for ($i = 0; $i < 90; $i++) {
            $date = Carbon::today()->addDays($i);

            foreach ($timeSlots as $slot) {
                AppointmentSlot::firstOrCreate(
                    [
                        'date' => $date->format('Y-m-d'),
                        'time_slot_id' => $slot->id,
                    ],
                    [
                        'day_type' => 'working',
                        'notes' => null,
                    ]
                );

                $generated++;
            }
        }

        $this->command->info("Generated {$generated} appointment slots (90 days).");
    }
}