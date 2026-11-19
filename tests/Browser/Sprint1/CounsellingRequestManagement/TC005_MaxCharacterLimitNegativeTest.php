<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC005_MaxCharacterLimitNegativeTest extends DuskTestCase
{
    public function test_concern_maxlength_enforces_1000_characters(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();
        $longText = str_repeat('a', 1005);

        $this->browse(function (Browser $browser) use ($student, $longText) {
            $browser->loginAs($student)
                ->visit('/counselling/request')
                ->waitForText('Step 1: Describe your concern')
                ->type('textarea[placeholder*="Describe what you are currently going through"]', $longText)
                ->pause(200)
                ->assertSee('1000 / 1000 max');
        });
    }
}