<?php
// database/migrations/2026_04_21_000001_create_time_slots_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('slot_label')->nullable(); // e.g., "9:00 AM - 10:00 AM"
            $table->integer('capacity_per_slot')->default(4); // Default 4 persons per time slot
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        
        
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};