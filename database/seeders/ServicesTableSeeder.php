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
        'description' => 'First-time registration for PhilSys',
        'estimated_duration_minutes' => 20,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'code' => 'updating',
        'name' => 'Updating/Correction',
        'description' => 'Update personal info',
        'estimated_duration_minutes' => 15,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'code' => 'inquiry',
        'name' => 'Status Inquiry / TRN Retrieval',
        'description' => 'Check status or retrieve TRN',
        'estimated_duration_minutes' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);
    }
}