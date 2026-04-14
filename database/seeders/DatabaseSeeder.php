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
        // Call the UserSeeder to create admin, staff, and regular users
        $this->call(UserSeeder::class);
        
        // Call the AppointmentSlotSeeder to generate appointment slots for future dates
        $this->call(AppointmentSlotSeeder::class);
        
        // Optional: Keep the factory for testing (commented out)
        // User::factory(10)->create();
        
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}