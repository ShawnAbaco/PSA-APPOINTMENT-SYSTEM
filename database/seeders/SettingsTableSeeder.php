<?php
// database/seeders/SettingsTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->insert([
            // Appointment settings
            [
                'key' => 'appointment.daily_capacity',
                'value' => '20',
                'group' => 'appointment',
                'type' => 'number',
                'description' => 'Maximum number of appointments per day',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'appointment.advance_booking_days',
                'value' => '30',
                'group' => 'appointment',
                'type' => 'number',
                'description' => 'How many days in advance can users book',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'appointment.cancellation_hours',
                'value' => '24',
                'group' => 'appointment',
                'type' => 'number',
                'description' => 'Hours before appointment to allow cancellation',
                'created_at' => now(),
                'updated_at' => now(),
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_per_service_limits',
                'value' => 'true',
                'group' => 'appointment',
                'type' => 'boolean',
                'description' => 'Enable per-service slot limits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Notification settings
            [
                'key' => 'notification.enable_email',
                'value' => 'true',
                'group' => 'notification',
                'type' => 'boolean',
                'description' => 'Enable email notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'notification.enable_sms',
                'value' => 'false',
                'group' => 'notification',
                'type' => 'boolean',
                'description' => 'Enable SMS notifications',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Email settings
            [
                'key' => 'email_host',
                'value' => 'smtp.gmail.com',
                'group' => 'email',
                'type' => 'text',
                'description' => 'SMTP server hostname',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_port',
                'value' => '587',
                'group' => 'email',
                'type' => 'number',
                'description' => 'SMTP server port',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_encryption',
                'value' => 'tls',
                'group' => 'email',
                'type' => 'text',
                'description' => 'Encryption type (tls or ssl)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_username',
                'value' => '',
                'group' => 'email',
                'type' => 'text',
                'description' => 'SMTP username',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_password',
                'value' => '',
                'group' => 'email',
                'type' => 'password',
                'description' => 'SMTP password',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_from_address',
                'value' => 'noreply@psa.gov.ph',
                'group' => 'email',
                'type' => 'text',
                'description' => 'From email address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_from_name',
                'value' => 'PSA Appointment System',
                'group' => 'email',
                'type' => 'text',
                'description' => 'From name for outgoing emails',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}