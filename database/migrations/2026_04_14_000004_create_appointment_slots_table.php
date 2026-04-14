<?php
// database/migrations/2026_04_14_000004_create_appointment_slots_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('total_capacity')->default(20);
            $table->integer('booked_count')->default(0);
            $table->integer('available_count')->default(20);
            
            // Time slots (optional for future enhancement)
            $table->json('time_slots')->nullable(); // Store time slot configurations
            
            // Holiday or special day settings
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_special_non_working')->default(false);
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->unique('date');
            $table->index(['date', 'available_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_slots');
    }
};