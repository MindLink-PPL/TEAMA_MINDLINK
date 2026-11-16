<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudentRoleAccessDuskTest extends DuskTestCase
{
    /**
     * PBI #4 — TC.StudentRole.001 (Positive):
     * Student accesses Student Dashboard (PG-01) and saves a daily mood response.
     */
    public function test_tc_student_role_001_student_accesses_dashboard_pg01_and_saves_daily_mood(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            $browser->loginAs($student)
                // Step 1: Open Student Dashboard (PG-01) with "Student portal" header pill
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('Student Dashboard')
                ->assertSee('Student portal')
                // Step 2: Verify action cards and Upcoming Appointment card
                ->assertSee('Check in')
                ->assertSee('Request counselling')
                ->assertSee('Learn about mental health')
                ->assertSee('How are you feeling today?')
                ->assertSee('Upcoming appointment')
                // Step 3: Select mood circle ("Great") under "How are you feeling today?"
                ->click('@mood-great')
                // Step 4: Click "Save response" -> saved in wellbeing_checkins and confirmation banner shown
                ->press('Save response')
                ->assertPathIs('/dashboard')
                ->assertSee('Your mood response (Great) has been saved privately.');
        });
    }

    /**
     * PBI #4 — TC.StudentRole.002 (Positive):
     * Student searches, filters, and reads mental health literacy articles (PG-02).
     */
    public function test_tc_student_role_002_student_searches_filters_and_reads_mental_health_literacy_pg02(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            $browser->loginAs($student)
                // Step 1: Visit /student/literacy (Mental Health Literacy PG-02)
                ->visit('/student/literacy')
                ->assertPathIs('/student/literacy')
                ->assertSee('Mental Health Literacy')
                ->assertSee('Find clear, practical information')
                ->assertSee('Understanding anxious thoughts')
                // Step 2: Click topic filter pill "Sleep"
                ->clickLink('Sleep')
                ->assertSee('A practical sleep routine')
                // Step 3: Enter keyword in search box and click Search
                ->type('search', 'sleep')
                ->press('Search')
                ->assertSee('A practical sleep routine')
                // Step 4: Click "Read article >" on an article card to open interactive reader modal
                ->click('@read-article-3')
                ->waitForText('Consistent wake times')
                ->assertSee('Consistent wake times');
        });
    }

    /**
     * PBI #4 — TC.StudentRole.003 (Positive):
     * Student completes the 10-question Wellbeing Check-In reflection quiz (PG-03).
     */
    public function test_tc_student_role_003_student_completes_wellbeing_checkin_reflection_quiz_pg03(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            $browser->loginAs($student)
                // Step 1: Open /student/wellbeing showing progress bar (6 of 10)
                ->visit('/student/wellbeing')
                ->assertPathIs('/student/wellbeing')
                ->assertSee('Wellbeing Check In')
                ->assertSee('A short reflection')
                ->assertSee('6 of 10')
                // Step 2: Verify and select answer options
                ->assertSee('Not at all')
                ->assertSee('Several days')
                ->assertSee('More than half the days')
                ->assertSee('Nearly every day')
                ->press('More than half the days')
                // Step 3: Navigate to question 10 using "Continue" button
                ->press('Continue')
                ->press('Continue')
                ->press('Continue')
                ->press('Continue')
                ->assertSee('10 of 10')
                // Step 4: Click "Submit check-in" -> score and guidance displayed in Recent private reflections
                ->press('Submit check-in')
                ->assertPathIs('/student/wellbeing')
                ->assertSee('Wellbeing check-in saved privately')
                ->assertSee('Recent private reflections')
                ->assertSee('Score:');
        });
    }

    /**
     * PBI #4 — TC.StudentRole.004 (Positive):
     * Student schedules and submits a private counselling appointment request (PG-04).
     */
    public function test_tc_student_role_004_student_schedules_and_submits_counselling_appointment_request_pg04(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            $browser->loginAs($student)
                // Step 1: Open /student/counselling with 4-step stepper (Concern, Consent, Schedule, Review)
                ->visit('/student/counselling')
                ->assertPathIs('/student/counselling')
                ->assertSee('Counselling Request')
                ->assertSee('Request private support')
                ->assertSee('Concern')
                ->assertSee('Consent')
                ->assertSee('Schedule')
                ->assertSee('Review')
                ->assertSee('Choose an available time')
                ->assertSee('Your request')
                // Step 2: Select Counsellor ("Alisa • Student wellbeing") and time slot ("13:30")
                ->select('#counsellor_select', 'Alisa • Student wellbeing')
                ->press('13:30')
                ->assertSee('21 October 2026, 13:30')
                // Step 3: Choose session mode ("In person" or "Online")
                ->press('In person')
                // Step 4: Click "Continue" to submit the request
                ->press('Continue')
                ->assertPathIs('/student/counselling')
                ->assertSee('scheduled and confirmed')
                ->assertSee('Your scheduled appointments')
                ->assertSee('Confirmed');
        });
    }

    /**
     * PBI #4 — TC.StudentRole.005 (Positive):
     * Student views private appointment confirmations and counsellor support updates.
     */
    public function test_tc_student_role_005_student_views_private_appointment_notifications_and_support_updates(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            $browser->loginAs($student)
                // Step 1: Visit /student/notifications
                ->visit('/student/notifications')
                ->assertPathIs('/student/notifications')
                ->assertSee('Notifications')
                ->assertSee('Private updates')
                // Step 2: Inspect appointment status cards
                ->assertSee('Session with')
                ->assertSee('Alisa');
        });
    }

    /**
     * PBI #4 — TC.StudentRole.006 (Negative):
     * Non-student roles (Admin & Counsellor) attempt to access student-only routes and receive 403.
     */
    public function test_tc_student_role_006_non_student_roles_are_forbidden_from_student_only_routes(): void
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
}
