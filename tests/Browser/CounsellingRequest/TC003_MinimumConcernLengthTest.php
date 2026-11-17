<?php

namespace Tests\Browser\CounsellingRequest;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC003_MinimumConcernLengthTest extends DuskTestCase
{
    public function test_minimum_concern_length_10_characters(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/counselling/request')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', 'Need help!') // Exact 10 chars
                ->press('Continue to Consent →')
                ->pause(300)
                ->assertSee('Step 2: Informed Consent Agreement');
        });
    }
}