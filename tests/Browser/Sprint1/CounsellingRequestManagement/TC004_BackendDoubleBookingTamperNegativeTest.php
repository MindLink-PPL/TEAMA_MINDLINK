<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\Counsellor;
use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC004_BackendDoubleBookingTamperNegativeTest extends DuskTestCase
{
    public function test_backend_blocks_tampered_double_booking_submission(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();
        $counsellor = Counsellor::firstOrCreate(
            ['name' => 'Alisa'],
            ['specialization' => 'Student wellbeing']
        );

        $targetDate = now()->addDays(13)->format('Y-m-d');

        CounsellingRequest::where('counsellor_name', 'Alisa')
            ->where('appointment_date', $targetDate)
            ->delete();

        $existing = CounsellingRequest::create([
            'student_id' => $student->id,
            'counsellor_name' => 'Alisa',
            'concern' => 'DoubleBookingBackendTest: existing active booking.',
            'appointment_date' => $targetDate,
            'appointment_time' => '15:00',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Approved',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($student, $counsellor, $targetDate) {
                $browser->loginAs($student)
                    ->visit('/counselling/request')
                    ->waitForText('Step 1: Describe your concern')
                    ->type('textarea[placeholder*="Describe what you are currently going through"]', 'Tamper attempt to double-book 15:00 slot.')
                    ->press('Continue to Consent →')
                    ->waitForText('Step 2: Informed Consent Agreement')
                    ->check('input[type="checkbox"]')
                    ->press('Continue to Schedule →')
                    ->waitForText('Choose an available time & format')
                    ->clickAtXPath("//span[contains(text(), '{$counsellor->name}')]")
                    ->script("let d = document.querySelector('input[type=\"date\"]'); d.value = '{$targetDate}'; d.dispatchEvent(new Event('input')); d.dispatchEvent(new Event('change'));");

                $browser->pause(300)
                    ->press('13:30')
                    ->press('Review Request →')
                    ->waitForText('Step 4: Final Review')
                    ->script("document.querySelector('input[name=\"appointment_time\"]').value = '15:00';");

                $browser->press('Confirm & Submit Request')
                    ->waitForText('Request Submitted for Review')
                    ->press('Understand & Proceed')
                    ->waitForText('already booked')
                    ->assertSee('already booked');
            });
        } finally {
            $existing->delete();
        }
    }
}