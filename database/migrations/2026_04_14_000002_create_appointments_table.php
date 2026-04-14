<?php
// database/migrations/2026_04_14_000002_create_appointments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique(); // Format: PSA-YYYYMMDD-XXXXX
            $table->enum('type', ['single', 'multiple'])->default('single');
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();
            $table->enum('status', [
                'pending', 
                'confirmed', 
                'cancelled', 
                'completed', 
                'no_show'
            ])->default('pending');
            
            // Contact Information
            $table->string('contact_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_mobile');
            
            // Tracking
            $table->string('reference_code')->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Foreign Keys
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For additional data
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['appointment_date', 'status']);
            $table->index('appointment_number');
            $table->index('reference_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};