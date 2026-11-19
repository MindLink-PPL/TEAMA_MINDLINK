<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\Counsellor;
use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC003_FrontendDoubleBookingBlockedNegativeTest extends DuskTestCase
{
    public function test_frontend_disables_already_booked_time_slot(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();
        $counsellor = Counsellor::firstOrCreate(
            ['name' => 'Alisa'],
            ['specialization' => 'Student wellbeing']
        );

        $targetDate = now()->addDays(12)->format('Y-m-d');

        $existing = CounsellingRequest::create([
            'student_id' => $student->id,
            'counsellor_name' => 'Alisa',
            'concern' => 'DoubleBookingFrontendTest: existing active booking.',
            'appointment_date' => $targetDate,
            'appointment_time' => '13:30',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Under Review',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($student, $counsellor, $targetDate) {
                $browser->loginAs($student)
                    ->visit('/counselling/request')
                    ->waitForText('Step 1: Describe your concern')
                    ->type('textarea[placeholder*="Describe what you are currently going through"]', 'Attempting to book an already occupied slot.')
                    ->press('Continue to Consent →')
                    ->waitForText('Step 2: Informed Consent Agreement')
                    ->check('input[type="checkbox"]')
                    ->press('Continue to Schedule →')
                    ->waitForText('Choose an available time & format')
                    ->clickAtXPath("//span[contains(text(), '{$counsellor->name}')]")
                    ->script("let d = document.querySelector('input[type=\"date\"]'); d.value = '{$targetDate}'; d.dispatchEvent(new Event('input')); d.dispatchEvent(new Event('change'));");

                $browser->pause(300)
                    ->assertSee('BOOKED')
                    ->assertDisabled('button:disabled');
            });
        } finally {
            $existing->delete();
        }
    }
}