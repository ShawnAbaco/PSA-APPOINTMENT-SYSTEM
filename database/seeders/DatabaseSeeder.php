<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
         $this->call([
            UserSeeder::class,
            TimeSlotSeeder::class,           // Add this first
            AppointmentSlotSeeder::class,
            ServicesTableSeeder::class,
            SettingsTableSeeder::class,
            WorkingDaysDefaultsTableSeeder::class,
            WorkingDaysOverridesSeeder::class,
            SlotCapacityRulesSeeder::class,
            DocumentRequirementsSeeder::class,
        ]);
}
}