<?php
// database/migrations/2026_04_20_000013_restructure_appointment_slots_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old appointment_slots table if exists
        Schema::dropIfExists('appointment_slots');
        
        // Create new appointment_slots table with service breakdown
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            
            // Day type configuration
            $table->enum('day_type', ['working', 'half_day', 'holiday', 'special'])->default('working');
            
            // Total capacity for the day (sum of all service capacities)
            $table->integer('total_capacity')->default(0);
            
            // Per-service capacities
            $table->integer('reg_capacity')->default(0);
            $table->integer('correction_capacity')->default(0);
            $table->integer('ephilid_capacity')->default(0);
            $table->integer('trn_capacity')->default(0);
            
            // Per-service booked counts
            $table->integer('reg_booked')->default(0);
            $table->integer('correction_booked')->default(0);
            $table->integer('ephilid_booked')->default(0);
            $table->integer('trn_booked')->default(0);
            
            // Per-service available counts
            $table->integer('reg_available')->default(0);
            $table->integer('correction_available')->default(0);
            $table->integer('ephilid_available')->default(0);
            $table->integer('trn_available')->default(0);
            
            // Notes
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Indexes
            $table->unique('date');
            $table->index(['date', 'day_type']);
        });
        
        // Create a stored procedure to auto-calculate total capacity
        DB::statement("
            CREATE TRIGGER update_total_capacity_before_insert 
            BEFORE INSERT ON appointment_slots
            FOR EACH ROW
            BEGIN
                SET NEW.total_capacity = NEW.reg_capacity + NEW.correction_capacity + NEW.ephilid_capacity + NEW.trn_capacity;
                SET NEW.reg_available = NEW.reg_capacity - NEW.reg_booked;
                SET NEW.correction_available = NEW.correction_capacity - NEW.correction_booked;
                SET NEW.ephilid_available = NEW.ephilid_capacity - NEW.ephilid_booked;
                SET NEW.trn_available = NEW.trn_capacity - NEW.trn_booked;
            END
        ");
        
        DB::statement("
            CREATE TRIGGER update_total_capacity_before_update 
            BEFORE UPDATE ON appointment_slots
            FOR EACH ROW
            BEGIN
                SET NEW.total_capacity = NEW.reg_capacity + NEW.correction_capacity + NEW.ephilid_capacity + NEW.trn_capacity;
                SET NEW.reg_available = NEW.reg_capacity - NEW.reg_booked;
                SET NEW.correction_available = NEW.correction_capacity - NEW.correction_booked;
                SET NEW.ephilid_available = NEW.ephilid_capacity - NEW.ephilid_booked;
                SET NEW.trn_available = NEW.trn_capacity - NEW.trn_booked;
            END
        ");
    }

    public function down(): void
    {
        // Drop triggers first
        DB::statement("DROP TRIGGER IF EXISTS update_total_capacity_before_insert");
        DB::statement("DROP TRIGGER IF EXISTS update_total_capacity_before_update");
        
        Schema::dropIfExists('appointment_slots');
    }
};