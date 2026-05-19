<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\TotpService;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User - Always approved, no need for approval
        DB::table('users')->insert([
            'employee_id' => 'PSA-ADMIN-001',
            'username' => 'admin',
            'first_name' => 'System',
            'middle_name' => 'Super',
            'last_name' => 'Administrator',
            'suffix' => null,
            'email' => 'admin@psa.gov.ph',
            'password' => Hash::make('admin123'),
            'contact_number' => '09171234567',
            'alternate_contact' => null,
            'role' => 'admin',
            'is_active' => true,
            'account_status' => 'approved',
            'rejection_reason' => null,
            'position' => 'IT Administrator',
            'department' => 'Information Technology',
            'profile_photo' => null,
            'permissions' => json_encode(['*']),
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null,
            'created_by' => null,
            'updated_by' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);



        $this->command->info('Users seeded successfully!');
        $this->command->warn('=============================================');
        $this->command->warn('Default Login Credentials:');
        $this->command->warn('Admin:    admin / admin123 (APPROVED - Can login immediately)');
        $this->command->warn('=============================================');
        $this->command->warn('To approve pending accounts:');
        $this->command->warn('1. Login as admin');
        $this->command->warn('2. Go to User Management > Pending Approvals tab');
        $this->command->warn('3. Click Approve for carlos.reyes account');
    }
}
