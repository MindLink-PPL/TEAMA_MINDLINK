<?php

namespace Tests\Browser\CounsellingRequest;

use App\Models\Counsellor;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC008_TamperedCounsellorIdNegativeTest extends DuskTestCase
{
    public function test_backend_rejects_tampered_counsellor_id(): void
    {
        $counsellor = Counsellor::firstOrCreate(
            ['name' => 'Alisa'],
            ['specialization' => 'Student wellbeing']
        );

        $tomorrow = now()->addDay()->format('Y-m-d');

        $this->browse(function (Browser $browser) use ($counsellor, $tomorrow) {
            $browser->visit('/counselling/request')
                ->waitForText('Step 1: Describe your concern')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', 'Valid concern description for tamper test.')
                ->press('Continue to Consent →')
                ->waitForText('Step 2: Informed Consent Agreement')
                ->check('input[type="checkbox"]')
                ->press('Continue to Schedule →')
                ->waitForText('Choose an available time & format')
                ->clickAtXPath("//span[contains(text(), '{$counsellor->name}')]")
                ->type('input[type="date"]', $tomorrow)
                ->press('15:00')
                ->press('Review Request →')
                ->waitForText('Step 4: Final Review')
                ->script("document.querySelector('input[name=\"counsellor_id\"]').value = '99999';");

            $browser->press('Confirm & Submit Request')
                ->waitForText('Request Submitted for Review')
                ->press('Understand & Proceed')
                ->waitForText('The selected counsellor does not exist.')
                ->assertSee('The selected counsellor does not exist.');
        });
    }
}