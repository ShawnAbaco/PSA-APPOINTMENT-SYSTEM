<?php
// database/migrations/2026_04_14_000009_create_triggers.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing triggers if they exist
        DB::statement("DROP TRIGGER IF EXISTS update_total_capacity_before_insert");
        DB::statement("DROP TRIGGER IF EXISTS update_total_capacity_before_update");
        
        // Create triggers for appointment_slots
        DB::statement("
            CREATE TRIGGER update_total_capacity_before_insert 
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
            CREATE TRIGGER update_total_capacity_before_update 
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
        DB::statement("DROP TRIGGER IF EXISTS update_total_capacity_before_insert");
        DB::statement("DROP TRIGGER IF EXISTS update_total_capacity_before_update");
    }
};