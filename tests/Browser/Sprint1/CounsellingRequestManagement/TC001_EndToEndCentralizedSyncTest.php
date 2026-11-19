<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\Counsellor;
use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC001_EndToEndCentralizedSyncTest extends DuskTestCase
{
    public function test_student_booking_syncs_immediately_to_counsellor_dashboard(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();
        $counsellorUser = User::where('email', 'counsellor@gmail.com')->firstOrFail();
        $counsellor = Counsellor::firstOrCreate(
            ['name' => 'Alisa'],
            ['specialization' => 'Student wellbeing']
        );

        $targetDate = now()->addDays(10)->format('Y-m-d');
        $uniqueConcern = 'CentralizedSyncTest: self-service request visible across portals.';

        CounsellingRequest::where('concern', $uniqueConcern)
            ->orWhere(function ($q) use ($targetDate) {
                $q->where('counsellor_name', 'Alisa')
                  ->where('appointment_date', $targetDate)
                  ->where('appointment_time', '10:00');
            })
            ->delete();

        try {
            $this->browse(function (Browser $browser) use ($student, $counsellorUser, $counsellor, $targetDate, $uniqueConcern) {
                $browser->loginAs($student)
                    ->visit('/counselling/request')
                    ->waitForText('Step 1: Describe your concern')
                    ->type('textarea[placeholder*="Describe what you are currently going through"]', $uniqueConcern)
                    ->press('Continue to Consent →')
                    ->waitForText('Step 2: Informed Consent Agreement')
                    ->check('input[type="checkbox"]')
                    ->press('Continue to Schedule →')
                    ->waitForText('Choose an available time & format')
                    ->press('💻 Online Meeting')
                    ->clickAtXPath("//span[contains(text(), '{$counsellor->name}')]")
                    ->script("let d = document.querySelector('input[type=\"date\"]'); d.value = '{$targetDate}'; d.dispatchEvent(new Event('input')); d.dispatchEvent(new Event('change'));");

                $browser->pause(300)
                    ->press('10:00')
                    ->press('Review Request →')
                    ->waitForText('Step 4: Final Review')
                    ->press('Confirm & Submit Request')
                    ->waitForText('Request Submitted for Review')
                    ->press('Understand & Proceed')
                    ->waitForText('Counselling request successfully sent!')
                    ->loginAs($counsellorUser)
                    ->visit('/counsellor/dashboard')
                    ->waitForText('Student Counselling Requests')
                    ->assertSee('CentralizedSyncTest: self-service request');
            });
        } finally {
            CounsellingRequest::where('concern', $uniqueConcern)->delete();
        }
    }
}