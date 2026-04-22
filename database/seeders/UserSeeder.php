<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'account_status' => 'approved', // Admin is approved by default
            'rejection_reason' => null,
            'position' => 'IT Administrator',
            'department' => 'Information Technology',
            'profile_photo' => null,
            'permissions' => json_encode(['*']),
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null,
            'created_by' => null,
            'updated_by' => null,
            'approved_by' => null, // No need for admin approval
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        // Staff User 1 - Registration Officer (Pre-approved for demo)
        DB::table('users')->insert([
            'employee_id' => 'PSA-STAFF-001',
            'username' => 'juan.delacruz',
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'suffix' => null,
            'email' => 'juan.delacruz@psa.gov.ph',
            'password' => Hash::make('staff123'),
            'contact_number' => '09171234568',
            'alternate_contact' => '09221234567',
            'role' => 'staff',
            'is_active' => true,
            'account_status' => 'approved', // Pre-approved for demo
            'rejection_reason' => null,
            'position' => 'Registration Officer',
            'department' => 'PhilSys Registry Office',
            'profile_photo' => null,
            'permissions' => json_encode([
                'view_appointments',
                'create_appointments',
                'update_appointments',
                'cancel_appointments',
                'confirm_appointments',
                'view_reports'
            ]),
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null,
            'created_by' => 1,
            'updated_by' => 1,
            'approved_by' => 1, // Approved by admin
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        // Staff User 2 - Encoder (Pre-approved for demo)
        DB::table('users')->insert([
            'employee_id' => 'PSA-STAFF-002',
            'username' => 'maria.santos',
            'first_name' => 'Maria',
            'middle_name' => 'Reyes',
            'last_name' => 'Santos',
            'suffix' => null,
            'email' => 'maria.santos@psa.gov.ph',
            'password' => Hash::make('staff123'),
            'contact_number' => '09171234569',
            'alternate_contact' => null,
            'role' => 'staff',
            'is_active' => true,
            'account_status' => 'approved', // Pre-approved for demo
            'rejection_reason' => null,
            'position' => 'Data Encoder',
            'department' => 'PhilSys Registry Office',
            'profile_photo' => null,
            'permissions' => json_encode([
                'view_appointments',
                'create_appointments',
                'update_appointments',
            ]),
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null,
            'created_by' => 1,
            'updated_by' => 1,
            'approved_by' => 1, // Approved by admin
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        // Staff User 3 - Inquiry Officer (PENDING - for testing approval workflow)
        DB::table('users')->insert([
            'employee_id' => 'PSA-STAFF-003',
            'username' => 'carlos.reyes',
            'first_name' => 'Carlos',
            'middle_name' => 'Mendoza',
            'last_name' => 'Reyes',
            'suffix' => null,
            'email' => 'carlos.reyes@psa.gov.ph',
            'password' => Hash::make('staff123'),
            'contact_number' => '09171234571',
            'alternate_contact' => null,
            'role' => 'staff',
            'is_active' => false, // Not active until approved
            'account_status' => 'pending', // PENDING - needs admin approval
            'rejection_reason' => null,
            'position' => 'Inquiry Officer',
            'department' => 'PhilSys Registry Office',
            'profile_photo' => null,
            'permissions' => json_encode([
                'view_appointments',
                'create_appointments',
                'update_appointments',
                'verify_trn',
                'process_inquiries'
            ]),
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null,
            'created_by' => 1,
            'updated_by' => 1,
            'approved_by' => null, // Not approved yet
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        // Regular User - Not needed for your system since you removed client accounts
        // This is kept for reference but won't be used in your staff-only system
        DB::table('users')->insert([
            'employee_id' => null,
            'username' => 'john_doe',
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Doe',
            'suffix' => 'Jr.',
            'email' => 'john.doe@example.com',
            'password' => Hash::make('user123'),
            'contact_number' => '09171234570',
            'alternate_contact' => null,
            'role' => 'user', // This role is not used in your staff-only system
            'is_active' => true,
            'account_status' => 'approved', // Auto-approved but won't be used
            'rejection_reason' => null,
            'position' => null,
            'department' => null,
            'profile_photo' => null,
            'permissions' => json_encode([
                'view_own_appointments',
                'create_appointments',
                'cancel_own_appointments',
            ]),
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null,
            'created_by' => 1,
            'updated_by' => 1,
            'approved_by' => 1,
            'approved_at' => now(),
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
        $this->command->warn('Staff 1:  juan.delacruz / staff123 (APPROVED - Can login immediately)');
        $this->command->warn('Staff 2:  maria.santos / staff123 (APPROVED - Can login immediately)');
        $this->command->warn('Staff 3:  carlos.reyes / staff123 (PENDING - Needs admin approval)');
        $this->command->warn('=============================================');
        $this->command->warn('To approve pending accounts:');
        $this->command->warn('1. Login as admin');
        $this->command->warn('2. Go to User Management > Pending Approvals tab');
        $this->command->warn('3. Click Approve for carlos.reyes account');
    }
}