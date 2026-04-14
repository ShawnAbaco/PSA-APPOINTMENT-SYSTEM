<?php
// database/migrations/2026_04_14_000003_create_appointment_clients_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_number')->nullable()->unique();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            
            // Client personal information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->enum('sex', ['Male', 'Female']);
            $table->date('birthdate');
            
            // Service selected
            $table->enum('service', ['reg', 'correction', 'ephilid', 'trn']);
            
            // Acknowledgment
            $table->boolean('requirements_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            
            // For future PhilSys integration
            $table->string('psa_reference_number')->nullable(); // TRN or other reference
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('client_number');
            $table->index(['last_name', 'first_name']);
            $table->index('service');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_clients');
    }
};