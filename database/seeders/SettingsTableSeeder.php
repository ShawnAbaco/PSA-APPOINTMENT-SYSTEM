<?php
// database/seeders/SettingsTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
    ['key' => 'appointment.daily_capacity', 'value' => '20', 'group' => 'appointment'],
    ['key' => 'appointment.advance_booking_days', 'value' => '30', 'group' => 'appointment'],
    ['key' => 'appointment.cancellation_hours', 'value' => '24', 'group' => 'appointment'],

    ['key' => 'service_capacities', 'value' => json_encode([
        'reg' => 10,
        'updating' => 5,
        'inquiry' => 8
    ]), 'group' => 'appointment'],

    ['key' => 'notification.enable_email', 'value' => 'true', 'group' => 'notification'],
    ['key' => 'notification.enable_sms', 'value' => 'false', 'group' => 'notification'],

    ['key' => 'time_slots.default_capacity', 'value' => '4', 'group' => 'appointment'],
];

foreach ($settings as $setting) {
    DB::table('settings')->updateOrInsert(
        ['key' => $setting['key']],
        [
            'value' => $setting['value'],
            'group' => $setting['group'],
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}
        
       
        
        $this->command->info('Settings seeded successfully!');
    }
}