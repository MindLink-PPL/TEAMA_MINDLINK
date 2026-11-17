<?php

namespace Tests\Browser\CounsellingRequest;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC005_MaxCharacterLimitNegativeTest extends DuskTestCase
{
    public function test_concern_maxlength_enforces_1000_characters(): void
    {
        $longText = str_repeat('a', 1005);

        $this->browse(function (Browser $browser) use ($longText) {
            $browser->visit('/counselling/request')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', $longText)
                ->pause(200)
                ->assertSee('1000 / 1000 max');
        });
    }
}