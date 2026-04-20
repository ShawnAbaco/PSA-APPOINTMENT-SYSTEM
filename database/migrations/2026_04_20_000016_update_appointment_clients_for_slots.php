<?php
// database/migrations/2026_04_20_000016_update_appointment_clients_for_slots.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if index exists before adding
        Schema::table('appointment_clients', function (Blueprint $table) {
            // Remove existing index if it exists to avoid duplication
            try {
                $table->dropIndex('appointment_clients_service_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
            
            // Add the index
            $table->index('service');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_clients', function (Blueprint $table) {
            $table->dropIndex(['service']);
        });
    }
};