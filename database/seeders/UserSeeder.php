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
        // Admin User
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
            'position' => 'IT Administrator',
            'department' => 'Information Technology',
            'profile_photo' => null,
            'permissions' => json_encode(['*']),
            'email_verified_at' => now(),
            'last_login_at' => null,
            'last_login_ip' => null,
            'created_by' => null,
            'updated_by' => null,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Staff User 1 - Registration Officer
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
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Staff User 2 - Encoder
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
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Staff User 3 - Inquiry Officer (NEW - for handling STATUS INQUIRY / RETRIEVAL OF TRN)
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
            'is_active' => true,
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
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Regular User
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
            'role' => 'user',
            'is_active' => true,
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
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('Users seeded successfully!');
        $this->command->warn('Default passwords: admin123 for admin, staff123 for staff, user123 for regular users');
    }
}