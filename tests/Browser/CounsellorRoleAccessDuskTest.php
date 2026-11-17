<?php

namespace Tests\Browser;

use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CounsellorRoleAccessDuskTest extends DuskTestCase
{
    /**
     * PBI #5 — TC.CounsellorRole.001 (Positive):
     * Counsellor logs in and accesses PG-05 Counsellor Workspace, workload metrics, and interactive request queue.
     */
    public function test_tc_counsellor_role_001_counsellor_accesses_workspace_pg05_and_interactive_request_queue(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                // Step 1: Log in with counsellor@gmail.com / 12345 -> Counsellor Workspace (PG-05) opens
                ->visit('/login')
                ->type('email', 'counsellor@gmail.com')
                ->type('password', '12345')
                ->press('Log in')
                ->assertPathIs('/dashboard')
                ->assertSee('Counsellor Workspace')
                ->assertSee('Counsellor portal')
                // Step 2: Inspect workload metric cards
                ->assertSee("Today's workload")
                ->assertSee('Pending requests')
                ->assertSee("Today's sessions")
                ->assertSee('Follow-ups due')
                // Step 3: Click ST-204 in the Request queue and verify right panel loads ST-204 Authorised Student Info
                ->assertSee('Request queue')
                ->assertSee('ST-204')
                ->assertSee('ST-187')
                ->click('@queue-item-ST-204')
                ->assertSee('ST-204 • Appointment 10:00')
                ->assertSee('Consent verified')
                ->assertSee('21/30')
                ->assertSee('Low')
                // Step 4: Verify sidebar navigation links
                ->assertSee('Workspace')
                ->assertSee('Requests')
                ->assertSee('Schedule')
                ->assertSee('Records')
                ->assertSee('Referrals');
        });
    }

    /**
     * PBI #5 — TC.CounsellorRole.002 (Positive):
     * Counsellor saves a session note draft and completes a session to notify the student.
     */
    public function test_tc_counsellor_role_002_counsellor_saves_draft_and_completes_session_note_to_notify_student(): void
    {
        $this->browse(function (Browser $browser) {
            $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

            $browser->loginAs($counsellor)
                // Step 1: Select ST-187 in the Counsellor Workspace
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->click('@queue-item-ST-187')
                ->assertSee('ST-187 • Appointment 10:00')
                ->assertSee('Authorised counsellor only')
                // Step 2: Enter observations in "Session note" and click "Save draft"
                ->type('session_note', 'Dusk clinical note: student agreed on structured study breaks and sleep routine.')
                ->press('Save draft')
                ->assertSee('Confidential session note draft saved')
                // Step 3: Update "Support plan" and click "Complete and notify"
                ->type('support_action', 'Structured Study & Grounding Plan')
                ->type('session_note', 'Dusk completed session note: follow-up scheduled and student notified.')
                ->press('Complete and notify')
                ->assertSee('completed and the student has been notified');
        });

        $req187 = CounsellingRequest::where('student_code', 'ST-187')->firstOrFail();
        $this->assertEquals('completed', $req187->note_status);
        $this->assertNotNull($req187->student_notified_at);
    }

    /**
     * PBI #5 — TC.CounsellorRole.003 (Positive):
     * Counsellor filters request queue and updates a student's priority and support action.
     */
    public function test_tc_counsellor_role_003_counsellor_filters_request_queue_and_updates_student_support_plan(): void
    {
        $this->browse(function (Browser $browser) {
            $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

            $browser->loginAs($counsellor)
                // Step 1: Visit /counsellor/requests
                ->visit('/counsellor/requests')
                ->assertPathIs('/counsellor/requests')
                ->assertSee('Counselling Requests')
                ->assertSee('Request queue & triage')
                ->assertSee('Authorised Student Info')
                ->assertSee('ST-204')
                // Step 2: Filter requests by clicking "High priority" pill
                ->clickLink('High priority')
                ->assertSee('ST-204')
                // Step 3: Click "Update Support" and verify flash message
                ->press('Update Support')
                ->assertSee('Updated counselling request');
        });
    }

    /**
     * PBI #5 — TC.CounsellorRole.004 (Positive):
     * Counsellor accesses consented student wellbeing check-ins, mood logs, and confidential records.
     */
    public function test_tc_counsellor_role_004_counsellor_accesses_authorised_student_information_and_records(): void
    {
        $this->browse(function (Browser $browser) {
            $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

            $browser->loginAs($counsellor)
                // Step 1: Visit /counsellor/records
                ->visit('/counsellor/records')
                ->assertPathIs('/counsellor/records')
                ->assertSee('Authorised Student Records')
                ->assertSee('Authorised counsellor only')
                // Step 2: Inspect student cards (Informed consent verified, Latest Mood, Wellbeing Score)
                ->assertSee('Informed consent verified')
                ->assertSee('Latest Mood:')
                ->assertSee('Wellbeing Score:')
                ->assertSee('10-Question Wellbeing Reflection Summary')
                ->assertSee('Alex Rivera (Student)')
                // Step 3: Search by student code "ST-204"
                ->type('search', 'ST-204')
                ->press('Search Records')
                ->assertSee('ST-204')
                ->assertSee('Alex Rivera (Student)')
                ->assertSee('21/30');
        });
    }

    /**
     * PBI #5 — TC.CounsellorRole.005 (Positive):
     * Counsellor views session timetable and creates a structured Student Support Referral.
     */
    public function test_tc_counsellor_role_005_counsellor_views_schedule_and_creates_student_support_referral(): void
    {
        $this->browse(function (Browser $browser) {
            $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

            $browser->loginAs($counsellor)
                // Step 1: Visit /counsellor/schedule and inspect time slots
                ->visit('/counsellor/schedule')
                ->assertPathIs('/counsellor/schedule')
                ->assertSee('Counsellor Schedule')
                ->assertSee("Today's sessions & timetable")
                ->assertSee('09:00')
                ->assertSee('10:00')
                ->assertSee('13:30')
                ->assertSee('15:00')
                // Step 2: Visit /counsellor/referrals
                ->visit('/counsellor/referrals')
                ->assertPathIs('/counsellor/referrals')
                ->assertSee('Referrals & Follow-Ups')
                ->assertSee('Student support referrals & follow-up plans')
                // Step 3: Fill out referral form and click "Save Support Referral"
                ->type('reason', 'Elevated academic stress and sleep disruption reported in wellbeing reflection.')
                ->type('support_plan', 'Coordinate academic deadline flexibility and weekly wellbeing follow-up.')
                ->press('Save Support Referral')
                ->assertPathIs('/counsellor/referrals')
                ->assertSee('Created support referral')
                ->assertSee('Coordinate academic deadline flexibility and weekly wellbeing follow-up.');
        });
    }

    /**
     * PBI #5 — TC.CounsellorRole.006 (Negative):
     * Non-counsellor roles (Administrator & Student) attempt to access confidential counsellor routes and receive 403.
     */
    public function test_tc_counsellor_role_006_non_counsellor_roles_are_forbidden_from_counsellor_routes(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
            $student = User::where('email', 'student@gmail.com')->firstOrFail();

            // Step 1 & 2: Administrator attempts to visit /counsellor/records and /counsellor/requests -> 403
            $browser->loginAs($admin)
                ->visit('/counsellor/records')
                ->assertSee('403')
                ->visit('/counsellor/requests')
                ->assertSee('403');

            // Step 3: Student attempts to visit /counsellor/records -> 403
            $browser->loginAs($student)
                ->visit('/counsellor/records')
                ->assertSee('403');
        });
    }
}
