<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC003_MinimumConcernLengthTest extends DuskTestCase
{
    public function test_minimum_concern_length_10_characters(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/counselling/request')
                ->waitForText('Step 1: Describe your concern')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', 'Need help!')
                ->press('Continue to Consent →')
                ->pause(300)
                ->assertSee('Step 2: Informed Consent Agreement');
        });
    }
}