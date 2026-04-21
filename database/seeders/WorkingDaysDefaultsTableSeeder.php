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
            ['day_of_week' => 1, 'day_name' => 'Monday', 'is_working' => true],
            ['day_of_week' => 2, 'day_name' => 'Tuesday', 'is_working' => true],
            ['day_of_week' => 3, 'day_name' => 'Wednesday', 'is_working' => true],
            ['day_of_week' => 4, 'day_name' => 'Thursday', 'is_working' => true],
            ['day_of_week' => 5, 'day_name' => 'Friday', 'is_working' => true],
            ['day_of_week' => 6, 'day_name' => 'Saturday', 'is_working' => false],
            ['day_of_week' => 7, 'day_name' => 'Sunday', 'is_working' => false],
        ]);
    }
}