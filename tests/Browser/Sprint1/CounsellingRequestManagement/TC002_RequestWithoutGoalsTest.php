<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\Counsellor;
use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC002_RequestWithoutGoalsTest extends DuskTestCase
{
    public function test_counselling_request_without_optional_goals(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();
        $counsellor = Counsellor::firstOrCreate(
            ['name' => 'Alisa'],
            ['specialization' => 'Student wellbeing']
        );

        $tomorrow = now()->addDays(2)->format('Y-m-d');

        CounsellingRequest::where('counsellor_name', 'Alisa')
            ->where('appointment_date', $tomorrow)
            ->where('appointment_time', '09:00')
            ->delete();

        $this->browse(function (Browser $browser) use ($student, $counsellor, $tomorrow) {
            $browser->loginAs($student)
                ->visit('/counselling/request')
                ->waitForText('Step 1: Describe your concern')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', 'I need guidance on balancing coursework and organization duties.')
                ->press('Continue to Consent →')
                ->waitForText('Step 2: Informed Consent Agreement')
                ->check('input[type="checkbox"]')
                ->press('Continue to Schedule →')
                ->waitForText('Choose an available time & format')
                ->press('💻 Online Meeting')
                ->clickAtXPath("//span[contains(text(), '{$counsellor->name}')]")
                ->script("let d = document.querySelector('input[type=\"date\"]'); d.value = '{$tomorrow}'; d.dispatchEvent(new Event('input')); d.dispatchEvent(new Event('change'));");

            $browser->pause(300)
                ->press('09:00')
                ->press('Review Request →')
                ->waitForText('Step 4: Final Review')
                ->press('Confirm & Submit Request')
                ->waitForText('Request Submitted for Review')
                ->press('Understand & Proceed')
                ->waitForText('Counselling request successfully sent!')
                ->assertSee('Counselling request successfully sent!');
        });
    }
}