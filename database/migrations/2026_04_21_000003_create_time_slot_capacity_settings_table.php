<?php
// database/migrations/2026_04_21_000003_create_time_slot_capacity_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slot_capacity_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_slot_id')->constrained('time_slots')->onDelete('cascade');
            $table->enum('day_type', ['weekday', 'saturday', 'sunday', 'holiday'])->default('weekday');
            $table->integer('capacity')->default(4); // Default 4 persons per slot
            $table->timestamps();
            
            $table->unique(['time_slot_id', 'day_type']);
        });
        
        // Insert default capacity settings for each time slot
        $timeSlots = DB::table('time_slots')->get();
        $dayTypes = ['weekday', 'saturday', 'sunday', 'holiday'];
        
        foreach ($timeSlots as $slot) {
            foreach ($dayTypes as $dayType) {
                $capacity = ($dayType === 'weekday') ? 4 : (($dayType === 'saturday') ? 2 : 0);
                DB::table('time_slot_capacity_settings')->insert([
                    'time_slot_id' => $slot->id,
                    'day_type' => $dayType,
                    'capacity' => $capacity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slot_capacity_settings');
    }
};