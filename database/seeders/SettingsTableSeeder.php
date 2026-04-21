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
            // Appointment settings
            [
                'key' => 'appointment.daily_capacity',
                'value' => '20',
                'group' => 'appointment',
                'type' => 'number',
                'description' => 'Maximum number of appointments per day',
            ],
            [
                'key' => 'appointment.advance_booking_days',
                'value' => '30',
                'group' => 'appointment',
                'type' => 'number',
                'description' => 'How many days in advance can users book',
            ],
            [
                'key' => 'appointment.cancellation_hours',
                'value' => '24',
                'group' => 'appointment',
                'type' => 'number',
                'description' => 'Hours before appointment to allow cancellation',
            ],
            
            // Service capacity settings
            [
                'key' => 'service_capacities',
                'value' => json_encode([
                    'reg' => 10,
                    'updating' => 5,
                    'inquiry' => 8
                ]),
                'group' => 'appointment',
                'type' => 'json',
                'description' => 'Default capacity per service type',
            ],
            [
                'key' => 'enable_per_service_limits',
                'value' => 'true',
                'group' => 'appointment',
                'type' => 'boolean',
                'description' => 'Enable per-service slot limits',
            ],
            
            // Notification settings
            [
                'key' => 'notification.enable_email',
                'value' => 'true',
                'group' => 'notification',
                'type' => 'boolean',
                'description' => 'Enable email notifications',
            ],
            [
                'key' => 'notification.enable_sms',
                'value' => 'false',
                'group' => 'notification',
                'type' => 'boolean',
                'description' => 'Enable SMS notifications',
            ],
            
            // Email settings
            [
                'key' => 'email_host',
                'value' => 'smtp.gmail.com',
                'group' => 'email',
                'type' => 'text',
                'description' => 'SMTP server hostname',
            ],
            [
                'key' => 'email_port',
                'value' => '587',
                'group' => 'email',
                'type' => 'number',
                'description' => 'SMTP server port',
            ],
            [
                'key' => 'email_encryption',
                'value' => 'tls',
                'group' => 'email',
                'type' => 'text',
                'description' => 'Encryption type (tls or ssl)',
            ],
            [
                'key' => 'email_username',
                'value' => '',
                'group' => 'email',
                'type' => 'text',
                'description' => 'SMTP username',
            ],
            [
                'key' => 'email_password',
                'value' => '',
                'group' => 'email',
                'type' => 'password',
                'description' => 'SMTP password',
            ],
            [
                'key' => 'email_from_address',
                'value' => 'noreply@psa.gov.ph',
                'group' => 'email',
                'type' => 'text',
                'description' => 'From email address',
            ],
            [
                'key' => 'email_from_name',
                'value' => 'PSA Appointment System',
                'group' => 'email',
                'type' => 'text',
                'description' => 'From name for outgoing emails',
            ],
            
            // Time slot settings
            [
                'key' => 'time_slots.enabled',
                'value' => 'true',
                'group' => 'appointment',
                'type' => 'boolean',
                'description' => 'Enable time slot selection for appointments',
            ],
            [
                'key' => 'time_slots.duration_minutes',
                'value' => '60',
                'group' => 'appointment',
                'type' => 'number',
                'description' => 'Duration of each time slot in minutes',
            ],
            [
                'key' => 'time_slots.default_capacity',
                'value' => '4',
                'group' => 'appointment',
                'type' => 'number',
                'description' => 'Default capacity per time slot',
            ],
        ];
        
        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                    'description' => $setting['description'],
                    'updated_at' => now(),
                    'created_at' => DB::table('settings')->where('key', $setting['key'])->value('created_at') ?? now(),
                ]
            );
        }
        
        $this->command->info('Settings seeded successfully!');
    }
}