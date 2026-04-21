<?php
// database/migrations/2026_04_21_000002_update_appointment_slots_with_time_slots.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, drop existing triggers
        DB::statement("DROP TRIGGER IF EXISTS update_total_capacity_before_insert");
        DB::statement("DROP TRIGGER IF EXISTS update_total_capacity_before_update");
        
        // Drop the old appointment_slots table
        Schema::dropIfExists('appointment_slots');
        
        // Create new appointment_slots table with time slot support
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('time_slot_id')->constrained('time_slots')->onDelete('cascade');
            $table->enum('day_type', ['working', 'half_day', 'holiday', 'special'])->default('working');
            
            // Total capacity for this time slot
            $table->integer('total_capacity')->default(0);
            
            // Per-service capacities for this time slot
            $table->integer('reg_capacity')->default(0);
            $table->integer('updating_capacity')->default(0);
            $table->integer('inquiry_capacity')->default(0);
            
            // Per-service booked counts for this time slot
            $table->integer('reg_booked')->default(0);
            $table->integer('updating_booked')->default(0);
            $table->integer('inquiry_booked')->default(0);
            
            // Per-service available counts for this time slot
            $table->integer('reg_available')->default(0);
            $table->integer('updating_available')->default(0);
            $table->integer('inquiry_available')->default(0);
            
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Unique constraint: one time slot per date
            $table->unique(['date', 'time_slot_id']);
            $table->index(['date', 'time_slot_id']);
            $table->index('time_slot_id');
        });
        
        // Create triggers for appointment_slots
        DB::statement("
            CREATE TRIGGER update_slot_capacity_before_insert 
            BEFORE INSERT ON appointment_slots
            FOR EACH ROW
            BEGIN
                SET NEW.total_capacity = NEW.reg_capacity + NEW.updating_capacity + NEW.inquiry_capacity;
                SET NEW.reg_available = NEW.reg_capacity - NEW.reg_booked;
                SET NEW.updating_available = NEW.updating_capacity - NEW.updating_booked;
                SET NEW.inquiry_available = NEW.inquiry_capacity - NEW.inquiry_booked;
            END
        ");
        
        DB::statement("
            CREATE TRIGGER update_slot_capacity_before_update 
            BEFORE UPDATE ON appointment_slots
            FOR EACH ROW
            BEGIN
                SET NEW.total_capacity = NEW.reg_capacity + NEW.updating_capacity + NEW.inquiry_capacity;
                SET NEW.reg_available = NEW.reg_capacity - NEW.reg_booked;
                SET NEW.updating_available = NEW.updating_capacity - NEW.updating_booked;
                SET NEW.inquiry_available = NEW.inquiry_capacity - NEW.inquiry_booked;
            END
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS update_slot_capacity_before_insert");
        DB::statement("DROP TRIGGER IF EXISTS update_slot_capacity_before_update");
        
        Schema::dropIfExists('appointment_slots');
        
        // Recreate the old structure if needed, or just leave it
    }
};