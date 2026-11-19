<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\Counsellor;
use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC002_SlotReavailabilityAfterDeclineTest extends DuskTestCase
{
    public function test_declined_request_slot_becomes_available_again(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();
        $counsellor = Counsellor::firstOrCreate(
            ['name' => 'Alisa'],
            ['specialization' => 'Student wellbeing']
        );

        $targetDate = now()->addDays(11)->format('Y-m-d');

        CounsellingRequest::where('counsellor_name', 'Alisa')
            ->where('appointment_date', $targetDate)
            ->where('appointment_time', '10:00')
            ->delete();

        $declined = CounsellingRequest::create([
            'student_id' => $student->id,
            'counsellor_name' => 'Alisa',
            'concern' => 'DeclinedSlotTest: previously declined session.',
            'appointment_date' => $targetDate,
            'appointment_time' => '10:00',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Declined',
            'counsellor_notes' => 'Referred externally.',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($student, $counsellor, $targetDate) {
                $browser->loginAs($student)
                    ->visit('/counselling/request')
                    ->waitForText('Step 1: Describe your concern')
                    ->type('textarea[placeholder*="Describe what you are currently going through"]', 'Testing that a declined slot is open again.')
                    ->press('Continue to Consent →')
                    ->waitForText('Step 2: Informed Consent Agreement')
                    ->check('input[type="checkbox"]')
                    ->press('Continue to Schedule →')
                    ->waitForText('Choose an available time & format')
                    ->clickAtXPath("//span[contains(text(), '{$counsellor->name}')]")
                    ->script("let d = document.querySelector('input[type=\"date\"]'); d.value = '{$targetDate}'; d.dispatchEvent(new Event('input')); d.dispatchEvent(new Event('change'));");

                $browser->pause(300)
                    ->press('10:00')
                    ->press('Review Request →')
                    ->waitForText('Step 4: Final Review')
                    ->assertSee('Step 4: Final Review');
            });
        } finally {
            $declined->delete();
        }
    }
}