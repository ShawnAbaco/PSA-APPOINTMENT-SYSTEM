<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;

class TimeSlotSeeder extends Seeder
{
    public function run()
    {
       $slots = [
    ['start_time' => '08:00:00', 'end_time' => '09:00:00', 'label' => '8:00 AM - 9:00 AM', 'display_order' => 1],
    ['start_time' => '09:00:00', 'end_time' => '10:00:00', 'label' => '9:00 AM - 10:00 AM', 'display_order' => 2],
    ['start_time' => '10:00:00', 'end_time' => '11:00:00', 'label' => '10:00 AM - 11:00 AM', 'display_order' => 3],
    ['start_time' => '11:00:00', 'end_time' => '12:00:00', 'label' => '11:00 AM - 12:00 PM', 'display_order' => 4],
    ['start_time' => '12:00:00', 'end_time' => '13:00:00', 'label' => '12:00 PM - 1:00 PM', 'display_order' => 5],
    ['start_time' => '13:00:00', 'end_time' => '14:00:00', 'label' => '1:00 PM - 2:00 PM', 'display_order' => 6],
    ['start_time' => '14:00:00', 'end_time' => '15:00:00', 'label' => '2:00 PM - 3:00 PM', 'display_order' => 7],
    ['start_time' => '15:00:00', 'end_time' => '16:00:00', 'label' => '3:00 PM - 4:00 PM', 'display_order' => 8],

];

foreach ($slots as $slot) {
    TimeSlot::firstOrCreate(
        ['start_time' => $slot['start_time']],
        $slot + [
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}

        $this->command->info('Time slots seeded successfully!');
        $this->command->info('Total time slots: ' . TimeSlot::count());
    }
}
