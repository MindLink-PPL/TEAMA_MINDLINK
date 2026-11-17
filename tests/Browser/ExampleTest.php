<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * Test that MindLink UI loads correctly on the home page.
     */
    public function test_mindlink_portal_loads_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->script('localStorage.clear(); window.location.reload();');
            $browser->waitForText('MindLink')
                ->assertSee('MindLink')
                ->assertSee('Support platform')
                ->assertSee('Student portal')
                ->assertSee('Good morning, Ava');
        });
    }

    /**
     * Test role switching between Student, Counsellor, and Admin.
     */
    public function test_role_switching(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->script('localStorage.clear(); window.location.reload();');
            $browser->waitFor('#role-switch')
                ->click('#role-switch')
                ->waitForText('Counsellor portal')
                ->assertSee('Counsellor portal')
                ->click('#role-switch')
                ->waitForText('Admin portal')
                ->assertSee('Admin portal');
        });
    }

    /**
     * Test navigation to counselling section.
     */
    public function test_counselling_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->script('localStorage.clear(); window.location.reload();');
            $browser->waitForText('Counselling')
                ->click('.nav-item[data-page="Counselling"]')
                ->waitForText('Your counselling requests')
                ->assertSee('Your counselling requests')
                ->assertSee('Request private support');
        });
    }
}
