<?php
// database/seeders/WorkingDaysOverridesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkingDaysOverride;
use Carbon\Carbon;

class WorkingDaysOverridesSeeder extends Seeder
{
    public function run()
    {
        // Example: Add Philippine public holidays for current year
        $year = Carbon::now()->year;
        
        $holidays = [
            // Regular holidays (day_type = 'holiday')
            ['date' => Carbon::create($year, 1, 1), 'day_type' => 'holiday', 'reason' => 'New Year\'s Day'],
            ['date' => Carbon::create($year, 4, 9), 'day_type' => 'holiday', 'reason' => 'Araw ng Kagitingan'],
            ['date' => Carbon::create($year, 4, 17), 'day_type' => 'holiday', 'reason' => 'Maundy Thursday'],
            ['date' => Carbon::create($year, 4, 18), 'day_type' => 'holiday', 'reason' => 'Good Friday'],
            ['date' => Carbon::create($year, 5, 1), 'day_type' => 'holiday', 'reason' => 'Labor Day'],
            ['date' => Carbon::create($year, 6, 12), 'day_type' => 'holiday', 'reason' => 'Independence Day'],
            ['date' => Carbon::create($year, 8, 21), 'day_type' => 'holiday', 'reason' => 'Ninoy Aquino Day'],
            ['date' => Carbon::create($year, 8, 26), 'day_type' => 'holiday', 'reason' => 'National Heroes Day'],
            ['date' => Carbon::create($year, 11, 30), 'day_type' => 'holiday', 'reason' => 'Bonifacio Day'],
            ['date' => Carbon::create($year, 12, 25), 'day_type' => 'holiday', 'reason' => 'Christmas Day'],
            ['date' => Carbon::create($year, 12, 30), 'day_type' => 'holiday', 'reason' => 'Rizal Day'],
            
            // Special non-working holidays (also 'holiday' type)
            ['date' => Carbon::create($year, 2, 25), 'day_type' => 'holiday', 'reason' => 'EDSA People Power Revolution Anniversary'],
            ['date' => Carbon::create($year, 11, 1), 'day_type' => 'holiday', 'reason' => 'All Saints\' Day'],
            ['date' => Carbon::create($year, 11, 2), 'day_type' => 'holiday', 'reason' => 'All Souls\' Day'],
            
            // Example of a date that is normally working (Tuesday) but becomes non_working due to special event
            ['date' => Carbon::create($year, 5, 20), 'day_type' => 'non_working', 'reason' => 'Special Non-Working Day (Local Election)'],
        ];
        
        foreach ($holidays as $holiday) {
            WorkingDaysOverride::updateOrCreate(
                ['date' => $holiday['date']->format('Y-m-d')],
                [
                    'day_type' => $holiday['day_type'],
                    'reason' => $holiday['reason'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            $icon = $holiday['day_type'] === 'holiday' ? '🎉' : '⚠️';
            $typeText = $holiday['day_type'] === 'holiday' ? 'HOLIDAY' : 'NON-WORKING';
            $this->command->info("{$icon} {$holiday['reason']} on {$holiday['date']->format('Y-m-d')} ({$typeText})");
        }
        
        $this->command->info('=============================================');
        $this->command->info('Working days overrides seeded successfully!');
        $this->command->info('Total overrides: ' . count($holidays));
    }
}