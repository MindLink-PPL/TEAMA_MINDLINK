<?php

namespace Tests\Browser\Sprint1\RoleBasedAccessControl;

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
                ->visit('/login')
                ->type('email', 'counsellor@gmail.com')
                ->type('password', '12345')
                ->press('Log in')
                ->assertPathIs('/dashboard')
                ->assertSee('Counsellor Workspace')
                ->assertSee('Counsellor portal')
                ->assertSee("Today's workload")
                ->assertSee('Pending requests')
                ->assertSee("Today's sessions")
                ->assertSee('Follow-ups due')
                ->assertSee('Request queue')
                ->assertSee('ST-204')
                ->assertSee('ST-187')
                ->click('@queue-item-ST-204')
                ->assertSee('ST-204 • Appointment 10:00')
                ->assertSee('Alex Rivera (Student)')
                ->assertSee('Workspace')
                ->assertSee('Requests')
                ->assertSee('Schedule')
                ->assertSee('Records');
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
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->click('@queue-item-ST-187')
                ->assertSee('ST-187 • Appointment 10:00')
                ->assertSee('Authorised counsellor only')
                ->type('session_note', 'Dusk clinical note: student agreed on structured study breaks and sleep routine.')
                ->press('Save draft')
                ->assertSee('Confidential session note draft saved')
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
     * Counsellor filters request queue and declines a student concern request with assessment notes.
     */
    public function test_tc_counsellor_role_003_counsellor_filters_request_queue_and_updates_student_support_plan(): void
    {
        $declineReq = CounsellingRequest::create([
            'student_id' => 1,
            'student_code' => 'ST-299',
            'counsellor_name' => 'Alisa',
            'concern' => 'Dusk decline test: scheduling conflict with laboratory exam.',
            'appointment_date' => now()->addDays(6)->format('Y-m-d'),
            'appointment_time' => '13:30',
            'appointment_mode' => 'In person',
            'priority' => 'High priority',
            'status' => 'Under Review',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($declineReq) {
                $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

                $browser->loginAs($counsellor)
                    ->visit('/counsellor/requests')
                    ->assertPathIs('/counsellor/requests')
                    ->assertSee('Counselling Requests')
                    ->assertSee('Request queue & triage')
                    ->assertSee('Authorised Student Info')
                    ->assertSee('ST-204')
                    ->clickLink('High priority')
                    ->assertSee('ST-204')
                    ->assertSee('ST-299')
                    ->click("@review-request-{$declineReq->request_id}")
                    ->waitForText('Student Concern Assessment')
                    ->type('textarea[placeholder*="Write session preparation notes"]', 'Please reschedule after your laboratory exam concludes.')
                    ->press('Decline / Refer')
                    ->assertSee("Request #{$declineReq->request_id} updated to Declined.")
                    ->assertSee('Declined');
            });

            $this->assertDatabaseHas('counselling_requests', [
                'id' => $declineReq->id,
                'status' => 'Declined',
            ]);
        } finally {
            $declineReq->delete();
        }
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
                ->visit('/counsellor/records')
                ->assertPathIs('/counsellor/records')
                ->assertSee('Authorised Student Records')
                ->assertSee('Authorised counsellor only')
                ->assertSee('Authorised student record')
                ->assertSee('Assigned Role:')
                ->assertSee('Support Requests:')
                ->assertSee('Authorised Support & Session History')
                ->assertSee('Alex Rivera (Student)')
                ->type('search', 'ST-204')
                ->press('Search Records')
                ->assertSee('ST-204')
                ->assertSee('Alex Rivera (Student)');
        });
    }

    /**
     * PBI #5 — TC.CounsellorRole.005 (Positive):
     * Counsellor manages time availability slots and approves/declines student concern requests.
     */
    public function test_tc_counsellor_role_005_counsellor_views_schedule_and_navigates_to_request_queue(): void
    {
        $futureDate = now()->addDays(7)->format('Y-m-d');
        $testReq = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'Dusk assessment test: exam anxiety and study pacing support.',
            'goals' => 'Build structured revision schedule.',
            'appointment_date' => $futureDate,
            'appointment_time' => '10:00',
            'appointment_mode' => 'In person',
            'status' => 'Under Review',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($futureDate, $testReq) {
                $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

                $browser->loginAs($counsellor)
                    ->visit('/counsellor/schedule')
                    ->assertPathIs('/counsellor/schedule')
                    ->assertSee('Counsellor Schedule')
                    ->assertSee('My Availability Slots')
                    ->assertSee('Add New Availability')
                    ->assertSee('09:00')
                    ->assertSee('10:00')
                    ->assertSee('13:30')
                    ->assertSee('15:00')
                    ->script("
                        document.querySelector('input[name=\"date\"]').value = '{$futureDate}';
                        document.querySelector('input[name=\"start_time\"]').value = '09:00';
                        document.querySelector('input[name=\"end_time\"]').value = '10:00';
                    ");

                $browser->press('+ Add Availability Slot')
                    ->assertSee('availability slot(s) added successfully!')
                    ->visit('/dashboard')
                    ->assertSee('Student Counselling Requests')
                    ->click("@review-request-{$testReq->request_id}")
                    ->waitForText('Student Concern Assessment')
                    ->assertSee('Dusk assessment test: exam anxiety and study pacing support.')
                    ->type('textarea[placeholder*="Write session preparation notes"]', 'Session approved; we will review your study schedule together.')
                    ->press('Approve & Confirm Session')
                    ->assertSee("Request #{$testReq->request_id} updated to Approved.");
            });

            $this->assertDatabaseHas('counselling_requests', [
                'id' => $testReq->id,
                'status' => 'Approved',
            ]);
        } finally {
            $testReq->delete();
            \App\Models\Availability::where('date', $futureDate)->delete();
        }
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

            $browser->loginAs($admin)
                ->visit('/counsellor/records')
                ->assertSee('403')
                ->visit('/counsellor/requests')
                ->assertSee('403');

            $browser->loginAs($student)
                ->visit('/counsellor/records')
                ->assertSee('403');
        });
    }
}