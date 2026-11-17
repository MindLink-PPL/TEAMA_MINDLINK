<?php

namespace Tests\Browser\CounsellingRequest;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC004_EmptyAndShortConcernNegativeTest extends DuskTestCase
{
    public function test_empty_and_below_minimum_concern_blocks_progression(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/counselling/request')
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