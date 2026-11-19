<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC001_ApproveRequestTest extends DuskTestCase
{
    public function test_counsellor_can_inspect_and_approve_request(): void
    {
        $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();
        $request = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'Unique approve test concern: struggling with exam anxiety and sleep schedule.',
            'goals' => 'Develop healthier study and rest habits.',
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'appointment_time' => '10:00',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Under Review',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($counsellor) {
                $browser->loginAs($counsellor)
                    ->visit('/counsellor/dashboard')
                    ->waitForText('Student Counselling Requests')
                    ->clickAtXPath("//tr[contains(., 'Unique approve test concern')]//button[contains(., 'Review & Assess')]")
                    ->pause(300)
                    ->assertSee('Unique approve test concern: struggling with exam anxiety and sleep schedule.')
                    ->type('textarea[placeholder*="Write session preparation notes"]', 'Looking forward to our session. Please prepare a list of your daily routines.')
                    ->press('Approve & Confirm Session')
                    ->waitForText('Approved')
                    ->assertSee('Approved');
            });

            $this->assertDatabaseHas('counselling_requests', [
                'request_id' => $request->request_id,
                'status' => 'Approved',
                'counsellor_notes' => 'Looking forward to our session. Please prepare a list of your daily routines.',
            ]);
        } finally {
            $request->delete();
        }
    }
}