<?php
// database/migrations/2026_04_20_000015_add_service_capacity_settings.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add service capacity settings
        DB::table('settings')->insert([
            [
                'key' => 'service_capacities',
                'value' => json_encode([
                    'reg' => 10,
                    'correction' => 5,
                    'ephilid' => 3,
                    'trn' => 2
                ]),
                'group' => 'appointment',
                'type' => 'json',
                'description' => 'Default capacity per service type',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_per_service_limits',
                'value' => 'true',
                'group' => 'appointment',
                'type' => 'boolean',
                'description' => 'Enable per-service slot limits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['service_capacities', 'enable_per_service_limits'])->delete();
    }
};