<?php

namespace Database\Seeders;

use App\Models\CounsellingRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full administrative access. Manages users, content, schedules, and role-based privacy boundaries.',
        ]);

        $counsellorRole = Role::create([
            'name' => 'Counsellor',
            'slug' => 'counsellor',
            'description' => 'Clinical & counselling access. Prioritises student requests, manages appointments, and documents notes.',
        ]);

        $studentRole = Role::create([
            'name' => 'Student',
            'slug' => 'user',
            'description' => 'As a student, I want to access student-specific features so that I can manage my wellbeing and counselling activities securely.',
        ]);

        // 2. Create Permissions
        $permissions = [
            [
                'name' => 'Manage Roles & Access',
                'slug' => 'roles.manage',
                'module' => 'Access Control',
                'description' => 'Can view and change roles and security permissions for any user in the system.',
            ],
            [
                'name' => 'Manage Users',
                'slug' => 'users.manage',
                'module' => 'Users',
                'description' => 'Can create, edit, or remove user accounts across the institution.',
            ],
            [
                'name' => 'View User Directory',
                'slug' => 'users.view',
                'module' => 'Users',
                'description' => 'Can browse user accounts and verify their assigned security roles.',
            ],
            [
                'name' => 'Manage System Settings',
                'slug' => 'settings.manage',
                'module' => 'System',
                'description' => 'Can configure global system parameters and security controls.',
            ],
            [
                'name' => 'View Service Analytics',
                'slug' => 'reports.view',
                'module' => 'Analytics',
                'description' => 'Can view aggregate request metrics and attendance without viewing confidential notes.',
            ],
            [
                'name' => 'Counsellor Workspace',
                'slug' => 'counsellor.workspace',
                'module' => 'Counselling',
                'description' => 'Access to clinical appointment queue and private student counselling documentation.',
            ],
            [
                'name' => 'Student Wellbeing Portal',
                'slug' => 'dashboard.view',
                'module' => 'Student Portal',
                'description' => 'Can access the student dashboard, daily mood reflection, and upcoming appointments.',
            ],
            [
                'name' => 'Wellbeing Check-In',
                'slug' => 'wellbeing.checkin',
                'module' => 'Student Portal',
                'description' => 'Can complete the quiz-like wellbeing reflection check-in and receive non-diagnostic guidance.',
            ],
            [
                'name' => 'Mental Health Literacy',
                'slug' => 'literacy.view',
                'module' => 'Student Portal',
                'description' => 'Can browse and search educational mental health literacy articles and topic guides.',
            ],
            [
                'name' => 'Counselling & Scheduling',
                'slug' => 'counselling.request',
                'module' => 'Student Portal',
                'description' => 'Can schedule available time slots and submit private counselling appointment requests.',
            ],
            [
                'name' => 'Manage Personal Profile',
                'slug' => 'profile.manage',
                'module' => 'Account',
                'description' => 'Can view and update personal profile details, email, and password.',
            ],
        ];

        $createdPermissions = [];
        foreach ($permissions as $p) {
            $createdPermissions[$p['slug']] = Permission::create($p);
        }

        // 3. Assign Permissions to Roles
        $adminRole->permissions()->attach([
            $createdPermissions['roles.manage']->id,
            $createdPermissions['users.manage']->id,
            $createdPermissions['users.view']->id,
            $createdPermissions['settings.manage']->id,
            $createdPermissions['reports.view']->id,
            $createdPermissions['dashboard.view']->id,
            $createdPermissions['profile.manage']->id,
        ]);

        $counsellorRole->permissions()->attach([
            $createdPermissions['users.view']->id,
            $createdPermissions['reports.view']->id,
            $createdPermissions['counsellor.workspace']->id,
            $createdPermissions['dashboard.view']->id,
            $createdPermissions['profile.manage']->id,
        ]);

        $studentRole->permissions()->attach([
            $createdPermissions['dashboard.view']->id,
            $createdPermissions['wellbeing.checkin']->id,
            $createdPermissions['literacy.view']->id,
            $createdPermissions['counselling.request']->id,
            $createdPermissions['profile.manage']->id,
        ]);

        // 4. Create Institutional Accounts
        // Default test administrator — log in with Email: admin@gmail.com / Password: 12345
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );

        User::create([
            'name' => 'Alisa Vance (Counsellor)',
            'email' => 'counsellor@mindlink.com',
            'password' => Hash::make('password'),
            'role_id' => $counsellorRole->id,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Sarah Connor (Counsellor)',
            'email' => 'sarah@mindlink.com',
            'password' => Hash::make('password'),
            'role_id' => $counsellorRole->id,
            'email_verified_at' => now(),
        ]);

        // Default test student — log in with Email: student@gmail.com / Password: 12345
        $testStudent = User::updateOrCreate(
            ['email' => 'student@gmail.com'],
            [
                'name' => 'Student',
                'password' => Hash::make('12345'),
                'role_id' => $studentRole->id,
                'email_verified_at' => now(),
            ]
        );

        $student1 = User::updateOrCreate(
            ['email' => 'student@mindlink.com'],
            [
                'name' => 'Alex Rivera (Student)',
                'password' => Hash::make('password'),
                'role_id' => $studentRole->id,
                'email_verified_at' => now(),
            ]
        );

        $student2 = User::updateOrCreate(
            ['email' => 'john@mindlink.com'],
            [
                'name' => 'John Doe (Student)',
                'password' => Hash::make('password'),
                'role_id' => $studentRole->id,
                'email_verified_at' => now(),
            ]
        );

        // Seed initial confirmed appointments for demo student accounts matching PG-01
        foreach ([$testStudent, $student1] as $studentAccount) {
            CounsellingRequest::firstOrCreate(
                [
                    'user_id' => $studentAccount->id,
                    'scheduled_date' => 'Wednesday, 21 October 2026',
                    'scheduled_time' => '10:00',
                ],
                [
                    'counsellor_name' => 'Alisa • Student wellbeing',
                    'concern' => 'Academic pressure and anxiety',
                    'consent_accepted_at' => now()->subDays(8),
                    'session_mode' => 'In person',
                    'status' => 'Confirmed',
                ]
            );
        }
    }
}
