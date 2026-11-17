<?php

namespace Tests\Browser\CounsellorDashboard;

use App\Models\CounsellingRequest;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC005_FinalizedRequestLockNegativeTest extends DuskTestCase
{
    public function test_finalized_request_is_locked_from_further_modification(): void
    {
        $request = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'FinalizedLockTest: already approved session.',
            'appointment_date' => now()->addDays(7)->format('Y-m-d'),
            'appointment_time' => '15:00',
            'appointment_mode' => 'In Person',
            'status' => 'Approved',
            'counsellor_notes' => 'Session confirmed.',
        ]);

        try {
            $this->browse(function (Browser $browser) {
                $browser->visit('/counsellor/dashboard')
                    ->waitForText('Student Counselling Requests')
                    ->clickAtXPath("//tr[contains(., 'FinalizedLockTest: already approved session')]//button[contains(., 'View Details')]")
                    ->pause(300)
                    ->assertDontSee('Approve & Confirm Session')
                    ->assertDontSee('Decline / Refer');
            });
        } finally {
            $request->delete();
        }
    }
}