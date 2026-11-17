<?php

namespace Tests\Browser\CounsellingRequest;

use App\Models\Counsellor;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC007_ScheduleValidationNegativeTest extends DuskTestCase
{
    public function test_schedule_blocks_missing_fields_and_past_date(): void
    {
        $counsellor = Counsellor::firstOrCreate(
            ['name' => 'Alisa'],
            ['specialization' => 'Student wellbeing']
        );

        $pastDate = now()->subDay()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        $this->browse(function (Browser $browser) use ($counsellor, $pastDate, $tomorrow) {
            $browser->visit('/counselling/request')
                ->waitForText('Step 1: Describe your concern')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', 'Valid concern description for schedule validation.')
                ->press('Continue to Consent →')
                ->waitForText('Step 2: Informed Consent Agreement')
                ->check('input[type="checkbox"]')
                ->press('Continue to Schedule →')
                ->waitForText('Choose an available time & format')
                
                ->press('Review Request →')
                ->waitForText('Please select a counsellor before continuing.')
                ->assertSee('Please select a counsellor before continuing.')

                ->clickAtXPath("//span[contains(text(), '{$counsellor->name}')]")
                ->script("let d = document.querySelector('input[type=\"date\"]'); d.value = '{$pastDate}'; d.dispatchEvent(new Event('input')); d.dispatchEvent(new Event('change'));");

            $browser->pause(300)
                ->press('13:30')
                ->press('Review Request →')
                ->waitForText('Appointment date cannot be in the past.')
                ->assertSee('Appointment date cannot be in the past.')

                ->script("let d = document.querySelector('input[type=\"date\"]'); d.value = '{$tomorrow}'; d.dispatchEvent(new Event('input')); d.dispatchEvent(new Event('change'));");

            $browser->pause(300)
                ->press('13:30')
                ->press('Review Request →')
                ->waitForText('Please select an available time slot.')
                ->assertSee('Please select an available time slot.');
        });
    }
}