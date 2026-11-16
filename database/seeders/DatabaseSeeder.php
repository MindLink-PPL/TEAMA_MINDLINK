<?php

namespace Database\Seeders;

use App\Models\CounsellingRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StudentReferral;
use App\Models\User;
use App\Models\WellbeingCheckin;
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
                'name' => 'Manage Support Referrals & Follow-ups',
                'slug' => 'counselling.referrals.manage',
                'module' => 'Counselling',
                'description' => 'Can create appropriate support plans, follow-up tasks, and specialist referrals for students.',
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
            $createdPermissions['counselling.referrals.manage']->id,
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

        // Default test counsellor — log in with Email: counsellor@gmail.com / Password: 12345
        $testCounsellor = User::updateOrCreate(
            ['email' => 'counsellor@gmail.com'],
            [
                'name' => 'Counsellor',
                'password' => Hash::make('12345'),
                'role_id' => $counsellorRole->id,
                'email_verified_at' => now(),
            ]
        );

        $alisaCounsellor = User::updateOrCreate(
            ['email' => 'counsellor@mindlink.com'],
            [
                'name' => 'Alisa Vance (Counsellor)',
                'password' => Hash::make('password'),
                'role_id' => $counsellorRole->id,
                'email_verified_at' => now(),
            ]
        );

        $sarahCounsellor = User::updateOrCreate(
            ['email' => 'sarah@mindlink.com'],
            [
                'name' => 'Sarah Connor (Counsellor)',
                'password' => Hash::make('password'),
                'role_id' => $counsellorRole->id,
                'email_verified_at' => now(),
            ]
        );

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

        // 5. Seed Authorised Student Wellbeing Check-ins
        WellbeingCheckin::firstOrCreate(
            [
                'user_id' => $testStudent->id,
                'total_score' => 14,
            ],
            [
                'mood' => 'Okay',
                'answers' => [1 => 1, 2 => 2, 3 => 1, 4 => 1, 5 => 2, 6 => 2, 7 => 1, 8 => 2, 9 => 1, 10 => 1],
                'guidance' => 'You have experienced moderate study or emotional pressure over the past two weeks. Exploring our Mental Health Literacy articles or scheduling a check-in with a counsellor may be helpful.',
            ]
        );

        WellbeingCheckin::firstOrCreate(
            [
                'user_id' => $student1->id,
                'total_score' => 21,
            ],
            [
                'mood' => 'Low',
                'answers' => [1 => 2, 2 => 3, 3 => 2, 4 => 2, 5 => 3, 6 => 2, 7 => 2, 8 => 2, 9 => 1, 10 => 2],
                'guidance' => 'It looks like things have felt quite heavy recently. We encourage you to submit a private Counselling Request so an authorised counsellor can support you.',
            ]
        );

        WellbeingCheckin::firstOrCreate(
            [
                'user_id' => $student2->id,
                'total_score' => 11,
            ],
            [
                'mood' => 'Okay',
                'answers' => [1 => 1, 2 => 1, 3 => 3, 4 => 2, 5 => 1, 6 => 1, 7 => 0, 8 => 1, 9 => 0, 10 => 1],
                'guidance' => 'You have experienced moderate study or emotional pressure over the past two weeks. Exploring our Mental Health Literacy articles or scheduling a check-in with a counsellor may be helpful.',
            ]
        );

        // 6. Seed Counselling Requests matching PG-01 (Student Upcoming Appointment) & PG-05 (Counsellor Request Queue)
        // Request 1: ST-187 (Scheduled at 10:00 — shown on both PG-01 and PG-05)
        $req187 = CounsellingRequest::updateOrCreate(
            [
                'student_code' => 'ST-187',
            ],
            [
                'user_id' => $testStudent->id,
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
                'status' => 'Confirmed',
                'priority' => 'Scheduled',
                'submitted_label' => 'Study pressure • appointment 10:00',
            ]
        );

        // Request 2: ST-204 (High priority — Persistent anxiety • submitted 09:12)
        $req204 = CounsellingRequest::updateOrCreate(
            [
                'student_code' => 'ST-204',
            ],
            [
                'user_id' => $student1->id,
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
                'status' => 'Confirmed',
                'priority' => 'High priority',
                'submitted_label' => 'Persistent anxiety • submitted 09:12',
            ]
        );

        // Request 3: ST-221 (New — Sleep difficulty • submitted yesterday)
        CounsellingRequest::updateOrCreate(
            [
                'student_code' => 'ST-221',
            ],
            [
                'user_id' => $student2->id,
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
                'status' => 'Pending review',
                'priority' => 'New',
                'submitted_label' => 'Sleep difficulty • submitted yesterday',
            ]
        );

        // Request 4: ST-163 (Follow-up — Follow-up requested by student)
        CounsellingRequest::updateOrCreate(
            [
                'student_code' => 'ST-163',
            ],
            [
                'user_id' => $student1->id,
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
                'status' => 'Confirmed',
                'priority' => 'Follow-up',
                'submitted_label' => 'Follow-up requested by student',
            ]
        );

        // 7. Seed Initial Support Referrals for Counsellor Referrals Workspace
        StudentReferral::firstOrCreate(
            [
                'student_code' => 'ST-204',
                'referral_type' => 'Academic Support & Assessment Consideration',
            ],
            [
                'user_id' => $student1->id,
                'counsellor_id' => $alisaCounsellor->id,
                'counselling_request_id' => $req204->id,
                'urgency' => 'High priority',
                'reason' => 'Persistent anxiety impacting examination preparation and sleep continuity.',
                'support_plan' => 'Coordinate 7-day coursework extension with Faculty Academic Advisor and schedule weekly counselling check-in.',
                'status' => 'Active',
            ]
        );

        StudentReferral::firstOrCreate(
            [
                'student_code' => 'ST-187',
                'referral_type' => 'Study Skills & Stress Management Workshop',
            ],
            [
                'user_id' => $testStudent->id,
                'counsellor_id' => $alisaCounsellor->id,
                'counselling_request_id' => $req187->id,
                'urgency' => 'Standard',
                'reason' => 'Study pressure and concentration fatigue during mid-semester assessments.',
                'support_plan' => 'Provide structured study pacing toolkit and review progress at next 10:00 appointment.',
                'status' => 'Follow-up due',
            ]
        );
    }
}
