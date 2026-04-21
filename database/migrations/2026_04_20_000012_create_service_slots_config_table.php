<?php
// database/migrations/2026_04_20_000012_create_service_slots_config_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_slots_config', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique(); // reg, updating, inquiry
            $table->string('service_name');
            $table->integer('default_capacity')->default(10);
            $table->integer('max_capacity')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Insert default service slot configs - UPDATED: removed ephilid/trn, added updating/inquiry
        DB::table('service_slots_config')->insert([
            ['service_code' => 'reg', 'service_name' => 'National ID Registration', 'default_capacity' => 10, 'max_capacity' => 20],
            ['service_code' => 'updating', 'service_name' => 'Correction/Updating', 'default_capacity' => 5, 'max_capacity' => 10],
            ['service_code' => 'inquiry', 'service_name' => 'STATUS INQUIRY / RETRIEVAL OF TRN / OTHER CONCERN', 'default_capacity' => 8, 'max_capacity' => 15],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('service_slots_config');
    }
};