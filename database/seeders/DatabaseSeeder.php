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
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full administrative access. Manages users, content, schedules, and role-based privacy boundaries.',
            ]
        );

        $counsellorRole = Role::updateOrCreate(
            ['slug' => 'counsellor'],
            [
                'name' => 'Counsellor',
                'description' => 'As a counsellor, I want to access counselling functions and authorised student information so that I can provide appropriate support.',
            ]
        );

        $studentRole = Role::updateOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'Student',
                'description' => 'As a student, I want to access student-specific features so that I can manage my wellbeing and counselling activities securely.',
            ]
        );

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
                'name' => 'Manage Counselling Requests',
                'slug' => 'counselling.requests.manage',
                'module' => 'Counselling',
                'description' => 'Can view, prioritise, and schedule incoming student counselling requests.',
            ],
            [
                'name' => 'View Authorised Student Information',
                'slug' => 'counselling.students.view',
                'module' => 'Counselling',
                'description' => 'Can access authorised student wellbeing check-ins, consent status, and support history.',
            ],
            [
                'name' => 'Document Confidential Session Notes',
                'slug' => 'counselling.notes.manage',
                'module' => 'Counselling',
                'description' => 'Can record private session observations, save drafts, complete sessions, and notify students.',
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
            $createdPermissions[$p['slug']] = Permission::updateOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }

        // 3. Assign Permissions to Roles
        $adminRole->permissions()->sync([
            $createdPermissions['roles.manage']->id,
            $createdPermissions['users.manage']->id,
            $createdPermissions['users.view']->id,
            $createdPermissions['settings.manage']->id,
            $createdPermissions['reports.view']->id,
            $createdPermissions['dashboard.view']->id,
            $createdPermissions['profile.manage']->id,
        ]);

        $counsellorRole->permissions()->sync([
            $createdPermissions['counsellor.workspace']->id,
            $createdPermissions['counselling.requests.manage']->id,
            $createdPermissions['counselling.students.view']->id,
            $createdPermissions['counselling.notes.manage']->id,
            $createdPermissions['users.view']->id,
            $createdPermissions['reports.view']->id,
            $createdPermissions['dashboard.view']->id,
            $createdPermissions['profile.manage']->id,
        ]);

        $studentRole->permissions()->sync([
            $createdPermissions['dashboard.view']->id,
            $createdPermissions['wellbeing.checkin']->id,
            $createdPermissions['literacy.view']->id,
            $createdPermissions['counselling.request']->id,
            $createdPermissions['profile.manage']->id,
        ]);

        // 4. Create Institutional Accounts (Create testStudent first so user_id = 1 is a Student)
        $testStudent = User::updateOrCreate(
            ['email' => 'student@gmail.com'],
            [
                'name' => 'Student',
                'password' => Hash::make('12345'),
                'role' => 'student',
                'role_id' => $studentRole->id,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345'),
                'role' => 'admin',
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );

        $testCounsellor = User::updateOrCreate(
            ['email' => 'counsellor@gmail.com'],
            [
                'name' => 'Counsellor',
                'password' => Hash::make('12345'),
                'role' => 'counsellor',
                'role_id' => $counsellorRole->id,
                'verification_status' => 'verified',
                'email_verified_at' => now(),
            ]
        );

        $alisaCounsellor = User::updateOrCreate(
            ['email' => 'counsellor@mindlink.com'],
            [
                'name' => 'Alisa Vance (Counsellor)',
                'password' => Hash::make('password'),
                'role' => 'counsellor',
                'role_id' => $counsellorRole->id,
                'verification_status' => 'verified',
                'email_verified_at' => now(),
            ]
        );

        $sarahCounsellor = User::updateOrCreate(
            ['email' => 'sarah@mindlink.com'],
            [
                'name' => 'Sarah Connor (Counsellor)',
                'password' => Hash::make('password'),
                'role' => 'counsellor',
                'role_id' => $counsellorRole->id,
                'verification_status' => 'verified',
                'email_verified_at' => now(),
            ]
        );

        $student1 = User::updateOrCreate(
            ['email' => 'student@mindlink.com'],
            [
                'name' => 'Alex Rivera (Student)',
                'password' => Hash::make('password'),
                'role' => 'student',
                'role_id' => $studentRole->id,
                'email_verified_at' => now(),
            ]
        );

        $student2 = User::updateOrCreate(
            ['email' => 'john@mindlink.com'],
            [
                'name' => 'John Doe (Student)',
                'password' => Hash::make('password'),
                'role' => 'student',
                'role_id' => $studentRole->id,
                'email_verified_at' => now(),
            ]
        );

        // 5. Run Module Seeders (Quizzes, Counsellors, Articles)
        $this->call([
            QuizSeeder::class,
            CounsellorSeeder::class,
            ArticleSeeder::class,
        ]);

        // 6. Seed Counselling Requests matching PG-01 & PG-05
        $req187 = CounsellingRequest::updateOrCreate(
            ['student_code' => 'ST-187'],
            [
                'user_id' => $testStudent->id,
                'student_id' => $testStudent->id,
                'counsellor_name' => 'Alisa • Student wellbeing',
                'counsellor_id' => $alisaCounsellor->id,
                'concern' => 'Study pressure',
                'notes' => 'Experiencing difficulty concentrating ahead of mid-semester deadlines.',
                'session_note' => 'Discussed structured study blocks and grounding exercises before exams.',
                'note_status' => 'draft',
                'support_action' => 'Structured Study & Grounding Plan',
                'follow_up_due' => false,
                'consent_accepted_at' => now()->subDays(8),
                'scheduled_date' => 'Wednesday, 21 October 2026',
                'scheduled_time' => '10:00',
                'session_mode' => 'In person',
                'appointment_date' => '2026-10-21',
                'appointment_time' => '10:00',
                'appointment_mode' => 'In person',
                'status' => 'Confirmed',
                'priority' => 'Scheduled',
                'submitted_label' => 'Study pressure • appointment 10:00',
            ]
        );

        $req204 = CounsellingRequest::updateOrCreate(
            ['student_code' => 'ST-204'],
            [
                'user_id' => $student1->id,
                'student_id' => $student1->id,
                'counsellor_name' => 'Alisa • Student wellbeing',
                'counsellor_id' => $alisaCounsellor->id,
                'concern' => 'Persistent anxiety',
                'notes' => 'Persistent anxious thoughts affecting sleep and lecture attendance over the past two weeks.',
                'session_note' => 'High-priority intake review required. Student scored 21/30 on wellbeing reflection.',
                'note_status' => 'draft',
                'support_action' => 'Priority Clinical Intake & Anxiety Support',
                'follow_up_due' => true,
                'consent_accepted_at' => now()->subDays(2),
                'scheduled_date' => 'Wednesday, 21 October 2026',
                'scheduled_time' => '10:00',
                'session_mode' => 'In person',
                'appointment_date' => '2026-10-21',
                'appointment_time' => '10:00',
                'appointment_mode' => 'In person',
                'status' => 'Confirmed',
                'priority' => 'High priority',
                'submitted_label' => 'Persistent anxiety • submitted 09:12',
            ]
        );

        CounsellingRequest::updateOrCreate(
            ['student_code' => 'ST-221'],
            [
                'user_id' => $student2->id,
                'student_id' => $student2->id,
                'counsellor_name' => 'Alisa • Student wellbeing',
                'counsellor_id' => $alisaCounsellor->id,
                'concern' => 'Sleep difficulty',
                'notes' => 'Difficulty falling asleep before 2am on weeknights.',
                'session_note' => null,
                'note_status' => 'none',
                'support_action' => 'Sleep Hygiene & Routine Consultation',
                'follow_up_due' => false,
                'consent_accepted_at' => now()->subDay(),
                'scheduled_date' => 'Thursday, 22 October 2026',
                'scheduled_time' => '13:30',
                'session_mode' => 'Online',
                'appointment_date' => '2026-10-22',
                'appointment_time' => '13:30',
                'appointment_mode' => 'Online',
                'status' => 'Pending review',
                'priority' => 'New',
                'submitted_label' => 'Sleep difficulty • submitted yesterday',
            ]
        );

        CounsellingRequest::updateOrCreate(
            ['student_code' => 'ST-163'],
            [
                'user_id' => $student1->id,
                'student_id' => $student1->id,
                'counsellor_name' => 'Sarah Connor • Academic & personal support',
                'counsellor_id' => $sarahCounsellor->id,
                'concern' => 'Follow-up requested by student',
                'notes' => 'Checking in on progress with the weekly workload boundary plan.',
                'session_note' => 'Student requested follow-up session to review workload pacing.',
                'note_status' => 'draft',
                'support_action' => 'Follow-Up Wellbeing Review',
                'follow_up_due' => true,
                'consent_accepted_at' => now()->subDays(10),
                'scheduled_date' => 'Wednesday, 21 October 2026',
                'scheduled_time' => '15:00',
                'session_mode' => 'In person',
                'appointment_date' => '2026-10-21',
                'appointment_time' => '15:00',
                'appointment_mode' => 'In person',
                'status' => 'Confirmed',
                'priority' => 'Follow-up',
                'submitted_label' => 'Follow-up requested by student',
            ]
        );
    }
}