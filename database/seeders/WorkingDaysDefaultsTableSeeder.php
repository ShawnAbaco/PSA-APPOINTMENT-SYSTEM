<?php
// database/seeders/WorkingDaysDefaultsTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkingDaysDefault;

class WorkingDaysDefaultsTableSeeder extends Seeder
{
    public function run()
    {
        // Define regular weekly schedule (Monday to Friday working, Saturday & Sunday non-working)
        $daysConfig = [
            'monday'    => ['day_type' => 'working'],
            'tuesday'   => ['day_type' => 'working'],
            'wednesday' => ['day_type' => 'working'],
            'thursday'  => ['day_type' => 'working'],
            'friday'    => ['day_type' => 'working'],
            'saturday'  => ['day_type' => 'non_working'],
            'sunday'    => ['day_type' => 'non_working'],
        ];
        
        foreach ($daysConfig as $dayName => $config) {
            WorkingDaysDefault::updateOrCreate(
                ['day_name' => $dayName],
                [
                    'day_type' => $config['day_type'],
                    'updated_at' => now(),
                ]
            );
            
            $status = $config['day_type'] === 'working' ? '✅ WORKING' : '❌ NON-WORKING';
            $this->command->info(ucfirst($dayName) . ": {$status}");
        }
        
        $this->command->info('=============================================');
        $this->command->info('Working days defaults seeded successfully!');
        $this->command->info('Working days: Monday, Tuesday, Wednesday, Thursday, Friday');
        $this->command->info('Non-working days (regular): Saturday, Sunday');
        $this->command->info('');
        $this->command->info('📌 NOTE: Use working_days_overrides table for HOLIDAYS and SPECIAL days only!');
        $this->command->info('   - For regular non-working days (weekends): day_type = "non_working"');
        $this->command->info('   - For holidays: day_type = "holiday" with reason (e.g., "Rizal Day")');
    }
}