<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC006_ConsentRequiredNegativeTest extends DuskTestCase
{
    public function test_cannot_proceed_without_accepting_informed_consent(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/counselling/request')
                ->waitForText('Step 1: Describe your concern')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', 'Valid concern description with more than ten characters.')
                ->press('Continue to Consent →')
                ->pause(300)
                ->assertSee('Step 2: Informed Consent Agreement')
                ->press('Continue to Schedule →')
                ->pause(200)
                ->assertSee('You must accept the Informed Consent Agreement (UU PDP) to proceed.');
        });
    }
}