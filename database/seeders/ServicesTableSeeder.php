<?php
// database/seeders/ServicesTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('services')->insert([
            [
                'code' => 'reg',
                'name' => 'National ID Registration',
                'description' => 'First-time registration for Philippine National ID',
                'requirements' => 'PSA Birth Certificate + 1 government-issued ID',
                'estimated_duration_minutes' => 20,
                'display_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'updating',
                'name' => 'Updating/Correction',
                'description' => 'Update or correct personal information in PhilSys',
                'requirements' => 'Supporting documents depending on the field to update',
                'estimated_duration_minutes' => 15,
                'display_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'inquiry',
                'name' => 'Status Inquiry / Retrieval Of TRN / Other Concern',
                'description' => 'Status inquiry, TRN retrieval, or other concerns',
                'requirements' => 'Valid Government-issued ID',
                'estimated_duration_minutes' => 10,
                'display_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}