<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       
        /*
        |--------------------------------------------------------------------------
        | SERVICES
        |--------------------------------------------------------------------------
        */
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // reg, updating, inquiry
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('estimated_duration_minutes')->default(15);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | TIME SLOTS (TEMPLATE)
        |--------------------------------------------------------------------------
        */
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('label')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | DEFAULT CAPACITY RULES (BY DAY TYPE)
        |--------------------------------------------------------------------------
        | Simplified to just 'working' and 'non_working'
        | Use 'reason' field to specify why it's non-working (e.g., 'Holiday', 'Sunday', 'Saturday')
        */
        Schema::create('slot_capacity_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            $table->enum('day_type', ['working', 'non_working', 'holiday'])->default('working');
            
            // Reason for non_working (e.g., 'Holiday', 'Sunday', 'Saturday', 'Special Event')
            $table->string('reason')->nullable();

            $table->integer('reg_capacity')->default(4);        // Registration: 4 slots default
            $table->integer('updating_capacity')->default(4);   // Updating: 4 slots default
            $table->integer('inquiry_capacity')->default(4);    // Inquiry: 4 slots default

            $table->timestamps();

            $table->unique(['time_slot_id', 'day_type']);
        });

        /*
        |--------------------------------------------------------------------------
        | PER-DAY OVERRIDES (MANUAL CONTROL)
        |--------------------------------------------------------------------------
        | This allows you to override capacity for specific dates
        | Example: Increase capacity to 8 for a special Saturday event
        | Or reduce to 2 for a holiday with limited staff
        */
        Schema::create('slot_capacity_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            $table->enum('day_type', ['working', 'non_working', 'holiday'])->default('working');

            // Reason for non_working (e.g., 'Holiday', 'Sunday', 'Saturday', 'Special Event')
            $table->string('reason')->nullable();

            $table->integer('reg_capacity')->nullable();
            $table->integer('updating_capacity')->nullable();
            $table->integer('inquiry_capacity')->nullable();

            $table->timestamps();

            $table->unique(['date', 'time_slot_id']);
        });

        /*
        |--------------------------------------------------------------------------
        | WORKING DAYS
        |--------------------------------------------------------------------------
        | Using ENUM for better readability and type safety
        | day_name: monday, tuesday, wednesday, thursday, friday, saturday, sunday
        */
        Schema::create('working_days_defaults', function (Blueprint $table) {
            $table->id();
            $table->enum('day_name', [
                'monday', 'tuesday', 'wednesday', 'thursday', 
                'friday', 'saturday', 'sunday'
            ])->unique();
            $table->enum('day_type', ['working', 'non_working',])->default('working');
            $table->timestamps();
        });

        Schema::create('working_days_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->enum('day_type', ['non_working','holiday']);
            $table->string('reason')->nullable(); // e.g., 'Holiday', 'Special Event', 'Half Day'
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | APPOINTMENT SLOTS (DATE + TIME SLOT INSTANCE)
        |--------------------------------------------------------------------------
        */
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            $table->enum('day_type', ['working', 'non_working'])->default('working');

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['date', 'time_slot_id']);
        });

        /*
        |--------------------------------------------------------------------------
        | APPOINTMENTS
        |--------------------------------------------------------------------------
        */
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();
            $table->enum('type', ['single', 'multiple'])->default('single');
            $table->date('appointment_date');
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');

            $table->string('contact_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_mobile');

            $table->string('reference_code')->unique();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Location
            $table->decimal('user_lat', 10, 8)->nullable();
            $table->decimal('user_lng', 11, 8)->nullable();
            $table->string('user_city')->nullable();
            $table->text('user_address')->nullable();
            $table->string('user_zipcode')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('processed_by')->nullable()->constrained('users');

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['appointment_date', 'time_slot_id', 'status']);
        });

        /*
        |--------------------------------------------------------------------------
        | APPOINTMENT CLIENTS (SERVICE HERE)
        |--------------------------------------------------------------------------
        */
        Schema::create('appointment_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_number')->nullable()->unique();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();

            $table->enum('sex', ['Male', 'Female']);
            $table->date('birthdate');

            $table->enum('service', ['reg', 'updating', 'inquiry']);

            $table->boolean('has_trn')->nullable();
            $table->string('trn_number', 29)->nullable();

            $table->boolean('requirements_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();

            $table->string('psa_reference_number')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->index(['service']);
        });

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOGS
        |--------------------------------------------------------------------------
        */
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('module');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number');
            $table->string('recipient_type');
            $table->string('recipient');
            $table->string('type');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | SETTINGS
        |--------------------------------------------------------------------------
        */
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->enum('service', ['reg', 'updating', 'inquiry']);
            $table->enum('age_group', ['standard', 'child'])->default('standard');
            $table->text('requirement');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['service', 'age_group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('appointment_clients');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('appointment_slots');
        Schema::dropIfExists('working_days_overrides');
        Schema::dropIfExists('working_days_defaults');
        Schema::dropIfExists('slot_capacity_overrides');
        Schema::dropIfExists('slot_capacity_rules');
        Schema::dropIfExists('time_slots');
        Schema::dropIfExists('services');
    }
};