<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentSlot;
use App\Models\Setting;
use Carbon\Carbon;

class AppointmentSlotSeeder extends Seeder
{
    public function run()
    {
        $defaultCapacity = 20;
        $capacitySetting = Setting::where('key', 'appointment.daily_capacity')->first();
        if ($capacitySetting) {
            $defaultCapacity = (int)$capacitySetting->value;
        }
        
        // Generate slots for next 90 days
        for ($i = 0; $i < 90; $i++) {
            $date = Carbon::today()->addDays($i);
            
            AppointmentSlot::firstOrCreate(
                ['date' => $date->format('Y-m-d')],
                [
                    'total_capacity' => $defaultCapacity,
                    'booked_count' => 0,
                    'available_count' => $defaultCapacity,
                    'is_holiday' => false,
                ]
            );
        }
    }
}