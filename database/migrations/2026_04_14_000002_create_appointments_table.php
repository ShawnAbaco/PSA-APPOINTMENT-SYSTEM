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
            $table->string('appointment_number')->unique();
            $table->enum('type', ['single', 'multiple'])->default('single');
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->string('contact_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_mobile');
            $table->string('reference_code')->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Location data
            $table->decimal('user_lat', 10, 8)->nullable();
            $table->decimal('user_lng', 11, 8)->nullable();
            $table->string('user_city')->nullable();
            $table->text('user_address')->nullable();
            $table->string('user_zipcode', 20)->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['appointment_date', 'status']);
            $table->index('appointment_number');
            $table->index('reference_code');
            $table->index('user_city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};