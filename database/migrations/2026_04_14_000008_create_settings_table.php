<?php
// database/migrations/2026_04_14_000008_create_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('type')->default('text'); // text, json, boolean, number
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('settings')->insert([
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
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};