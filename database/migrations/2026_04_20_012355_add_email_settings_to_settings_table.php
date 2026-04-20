<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add email settings to the existing settings table
        DB::table('settings')->insert([
            [
                'key' => 'email_host',
                'value' => 'smtp.gmail.com',
                'group' => 'email',
                'type' => 'text',
                'description' => 'SMTP server hostname (e.g., smtp.gmail.com, smtp.office365.com)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_port',
                'value' => '587',
                'group' => 'email',
                'type' => 'number',
                'description' => 'SMTP server port (587 for TLS, 465 for SSL)',
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
                'description' => 'SMTP username (usually your email address)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_password',
                'value' => '',
                'group' => 'email',
                'type' => 'password',
                'description' => 'SMTP password or app-specific password for Gmail',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_from_address',
                'value' => 'noreply@psa.gov.ph',
                'group' => 'email',
                'type' => 'text',
                'description' => 'From email address for outgoing emails',
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

    public function down(): void
    {
        // Remove the email settings
        DB::table('settings')->whereIn('key', [
            'email_host',
            'email_port',
            'email_encryption',
            'email_username',
            'email_password',
            'email_from_address',
            'email_from_name',
        ])->delete();
    }
};