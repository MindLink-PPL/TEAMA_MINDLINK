<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudentRoleAccessDuskTest extends DuskTestCase
{
    /**
     * PBI #4 — TC.StudentRole.001 (Positive):
     * Student logs in and accesses Student Dashboard (PG-01), verifying student portal & role permissions.
     */
    public function test_tc_student_role_001_student_accesses_dashboard_pg01_and_verifies_role_permissions(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            $browser->loginAs($student)
                // Step 1: Open Student Dashboard (PG-01) with "Student portal" header pill
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('Student Dashboard')
                ->assertSee('Student portal')
                // Step 2: Verify student navigation links and role access cards
                ->assertSee('Dashboard')
                ->assertSee('Learn')
                ->assertSee('Wellbeing')
                ->assertSee('Counselling')
                ->assertSee('Check in')
                ->assertSee('Request counselling')
                ->assertSee('Learn about mental health')
                ->assertSee('Your Student Access & Privacy')
                ->assertSee('Student Role')
                ->assertSee('Upcoming appointment');
        });
    }

    /**
     * PBI #4 — TC.StudentRole.002 (Negative):
     * Student role is forbidden (403) from accessing Administrator and Counsellor routes.
     */
    public function test_tc_student_role_002_student_is_forbidden_from_admin_and_counsellor_routes(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            $browser->loginAs($student)
                ->visit('/admin/users')
                ->assertSee('403')
                ->visit('/counsellor/requests')
                ->assertSee('403')
                ->visit('/counsellor/records')
                ->assertSee('403');
        });
    }

    /**
     * PBI #4 — TC.StudentRole.003 (Negative):
     * Non-student roles (Admin & Counsellor) attempt to access student-only routes and receive 403.
     */
    public function test_tc_student_role_003_non_student_roles_are_forbidden_from_student_only_routes(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
            $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

            // Step 1: Log in as Administrator and visit /student/wellbeing -> 403 Forbidden
            $browser->loginAs($admin)
                ->visit('/student/wellbeing')
                ->assertSee('403');

            // Step 2: Log in as Counsellor and visit /student/counselling -> 403 Forbidden
            $browser->loginAs($counsellor)
                ->visit('/student/counselling')
                ->assertSee('403');
        });
    }

    /**
     * PBI #4 — TC.StudentRole.004 (Positive):
     * Student can cancel an unconfirmed counselling request from the Student Dashboard or Counselling page.
     */
    public function test_tc_student_role_004_student_can_cancel_unconfirmed_counselling_request(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            \App\Models\InformedConsent::firstOrCreate(
                ['user_id' => $student->id, 'policy_version' => 'v1.0'],
                ['agreed_at' => now()]
            );

            $pendingRequest = \App\Models\CounsellingRequest::create([
                'user_id' => $student->id,
                'student_id' => $student->id,
                'counsellor_name' => 'Alisa',
                'concern' => 'Exam stress support request to cancel.',
                'appointment_date' => now()->addDays(2)->format('Y-m-d'),
                'appointment_time' => '10:00',
                'appointment_mode' => 'In person',
                'status' => 'Under Review',
            ]);

            $browser->loginAs($student)
                ->visit('/counselling')
                ->assertSee('Your counselling requests & scheduled appointments')
                ->assertSee('Exam stress support request to cancel.')
                ->assertSee('Cancel Request')
                ->click("@cancel-request-{$pendingRequest->id}")
                ->pause(300)
                ->assertSee('Counselling request has been cancelled.')
                ->assertSee('Cancelled');
        });
    }
}
