<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC004_DeclineWithoutNotesNegativeTest extends DuskTestCase
{
    public function test_decline_is_blocked_when_assessment_notes_are_empty(): void
    {
        $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();
        $request = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'NegativeDeclineTest: empty notes validation check.',
            'appointment_date' => now()->addDays(6)->format('Y-m-d'),
            'appointment_time' => '13:30',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Under Review',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($counsellor) {
                $browser->loginAs($counsellor)
                    ->visit('/counsellor/dashboard')
                    ->waitForText('Student Counselling Requests')
                    ->clickAtXPath("//tr[contains(., 'NegativeDeclineTest: empty notes validation check')]//button[contains(., 'Review & Assess')]")
                    ->pause(300)
                    ->press('Decline / Refer')
                    ->waitForText('Please provide assessment or referral notes before declining a request.')
                    ->assertSee('Please provide assessment or referral notes before declining a request.');
            });

            $this->assertDatabaseHas('counselling_requests', [
                'request_id' => $request->request_id,
                'status' => 'Under Review',
            ]);
        } finally {
            $request->delete();
        }
    }
}