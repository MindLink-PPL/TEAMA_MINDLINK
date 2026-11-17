<?php

namespace Tests\Browser\CounsellorDashboard;

use App\Models\CounsellingRequest;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC002_DeclineRequestWithNotesTest extends DuskTestCase
{
    public function test_counsellor_can_decline_request_with_mandatory_notes(): void
    {
        $request = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'Unique decline test concern: requesting urgent psychiatric medication evaluation.',
            'goals' => 'Get prescription support.',
            'appointment_date' => now()->addDays(4)->format('Y-m-d'),
            'appointment_time' => '11:00',
            'appointment_mode' => 'In Person',
            'status' => 'Under Review',
        ]);

        try {
            $this->browse(function (Browser $browser) {
                $browser->visit('/counsellor/dashboard')
                    ->waitForText('Student Counselling Requests')
                    ->clickAtXPath("//tr[contains(., 'Unique decline test concern')]//button[contains(., 'Review & Assess')]")
                    ->pause(300)
                    ->type('textarea[placeholder*="Write session preparation notes"]', 'Referred to Telkom Medika Psychiatric Clinic for medical evaluation.')
                    ->press('Decline / Refer')
                    ->waitForText('Declined')
                    ->assertSee('Declined');
            });

            $this->assertDatabaseHas('counselling_requests', [
                'request_id' => $request->request_id,
                'status' => 'Declined',
                'counsellor_notes' => 'Referred to Telkom Medika Psychiatric Clinic for medical evaluation.',
            ]);
        } finally {
            $request->delete();
        }
    }
}