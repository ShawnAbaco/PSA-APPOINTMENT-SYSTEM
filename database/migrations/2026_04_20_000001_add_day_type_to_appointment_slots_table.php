<?php
// database/migrations/2026_04_20_000001_add_day_type_to_appointment_slots_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_slots', function (Blueprint $table) {
            // Add the new enum column
            $table->enum('day_type', ['working', 'half_day', 'holiday', 'special'])->default('working')->after('is_special_non_working');
        });
        
        // Migrate existing data from old boolean columns to new enum
        DB::table('appointment_slots')->update([
            'day_type' => DB::raw("
                CASE 
                    WHEN is_holiday = 1 THEN 'holiday'
                    WHEN is_special_non_working = 1 THEN 'special'
                    ELSE 'working'
                END
            ")
        ]);
        
        // Optional: Drop the old boolean columns after migration
        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->dropColumn(['is_holiday', 'is_special_non_working']);
        });
    }

    public function down(): void
    {
        Schema::table('appointment_slots', function (Blueprint $table) {
            // Re-add old columns
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_special_non_working')->default(false);
        });
        
        // Restore old data from enum
        DB::table('appointment_slots')->update([
            'is_holiday' => DB::raw("CASE WHEN day_type = 'holiday' THEN 1 ELSE 0 END"),
            'is_special_non_working' => DB::raw("CASE WHEN day_type = 'special' THEN 1 ELSE 0 END"),
        ]);
        
        $table->dropColumn('day_type');
    }
};