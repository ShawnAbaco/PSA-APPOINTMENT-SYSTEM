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
            $table->date('date')->unique();
            $table->enum('day_type', ['working', 'half_day', 'holiday', 'special'])->default('working');
            $table->integer('total_capacity')->default(0);
            
            // Per-service capacities
            $table->integer('reg_capacity')->default(0);
            $table->integer('updating_capacity')->default(0);
            $table->integer('inquiry_capacity')->default(0);
            
            // Per-service booked counts
            $table->integer('reg_booked')->default(0);
            $table->integer('updating_booked')->default(0);
            $table->integer('inquiry_booked')->default(0);
            
            // Per-service available counts
            $table->integer('reg_available')->default(0);
            $table->integer('updating_available')->default(0);
            $table->integer('inquiry_available')->default(0);
            
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['date', 'day_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_slots');
    }
};