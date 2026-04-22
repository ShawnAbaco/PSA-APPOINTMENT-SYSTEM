<?php
// database/seeders/WorkingDaysDefaultsTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkingDaysDefaultsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('working_days_defaults')->insert([
    ['day_of_week' => 1, 'is_working' => true],
    ['day_of_week' => 2, 'is_working' => true],
    ['day_of_week' => 3, 'is_working' => true],
    ['day_of_week' => 4, 'is_working' => true],
    ['day_of_week' => 5, 'is_working' => true],
    ['day_of_week' => 6, 'is_working' => false],
    ['day_of_week' => 0, 'is_working' => false], // Sunday = 0 (Laravel/Carbon)
]);
    }
}