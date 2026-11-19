<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC004_EmptyAndShortConcernNegativeTest extends DuskTestCase
{
    public function test_empty_and_below_minimum_concern_blocks_progression(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/counselling/request')
                ->waitForText('Step 1: Describe your concern')
                ->press('Continue to Consent →')
                ->pause(200)
                ->assertSee('Please describe your concern before continuing.')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', '123456789')
                ->press('Continue to Consent →')
                ->pause(200)
                ->assertSee('Your concern description must be at least 10 characters.');
        });
    }
}