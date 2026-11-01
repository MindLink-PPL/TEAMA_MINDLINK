<?php

namespace Database\Seeders;

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
            'description' => 'Student user access. Conducts wellbeing check-ins, accesses literacy resources, and requests private support.',
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
                'module' => 'General',
                'description' => 'Can access wellbeing check-ins, mental health literacy articles, and appointments.',
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
            $createdPermissions['profile.manage']->id,
        ]);

        // 4. Create Institutional Accounts
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@mindlink.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

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

        User::create([
            'name' => 'Alex Rivera (Student)',
            'email' => 'student@mindlink.com',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'John Doe (Student)',
            'email' => 'john@mindlink.com',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'email_verified_at' => now(),
        ]);
    }
}
