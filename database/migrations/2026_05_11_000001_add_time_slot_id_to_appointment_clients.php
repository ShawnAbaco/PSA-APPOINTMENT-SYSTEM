<?php
// database/migrations/2025_05_11_000001_add_time_slot_id_to_appointment_clients.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_clients', function (Blueprint $table) {
            $table->foreignId('time_slot_id')->nullable()->after('service')->constrained('time_slots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointment_clients', function (Blueprint $table) {
            $table->dropForeign(['time_slot_id']);
            $table->dropColumn('time_slot_id');
        });
    }
};