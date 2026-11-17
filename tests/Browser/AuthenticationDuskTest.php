<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\RateLimiter;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthenticationDuskTest extends DuskTestCase
{
    /**
     * Clear rate limiter keys for a given email across local loopback addresses.
     */
    protected function clearLoginRateLimiter(string $email): void
    {
        $normalized = strtolower($email);
        RateLimiter::clear("{$normalized}|127.0.0.1");
        RateLimiter::clear("{$normalized}|::1");
    }

    /**
     * PBI #1 — TC.Login.001 (Positive):
     * User login menggunakan email dan password yang sudah ter-registrasi.
     */
    public function test_tc_login_001_user_logs_in_with_registered_email_and_password(): void
    {
        $this->clearLoginRateLimiter('admin@gmail.com');

        $this->browse(function (Browser $browser) {
            $browser->logout()
                // Step 1: Launch the app and open /login
                ->visit('/login')
                ->assertPathIs('/login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                // Step 2: Input a registered email (admin@gmail.com)
                ->type('email', 'admin@gmail.com')
                // Step 3: Input registered password (12345)
                ->type('password', '12345')
                // Step 4: Tap Log in button -> authenticated & redirected to Dashboard
                ->press('Log in')
                ->assertPathIs('/dashboard')
                ->assertSee('Administrative Dashboard')
                ->assertAuthenticated();
        });
    }

    /**
     * PBI #2 — TC.Login.002 (Negative):
     * User login menggunakan email dan password yang salah.
     */
    public function test_tc_login_002_user_cannot_log_in_with_wrong_email_and_password(): void
    {
        $this->clearLoginRateLimiter('wrong@mindlink.com');

        $this->browse(function (Browser $browser) {
            $browser->logout()
                // Step 1: Launch the app and navigate to /login
                ->visit('/login')
                ->assertPathIs('/login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                // Step 2: Input an unregistered email (wrong@mindlink.com)
                ->type('email', 'wrong@mindlink.com')
                // Step 3: Input an incorrect password (wrongpass)
                ->type('password', 'wrongpass')
                // Step 4: Tap Log in button -> fails and shows credentials error
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('These credentials do not match our records.')
                ->assertGuest();
        });
    }

    /**
     * PBI #2 — TC.Login.003 (Positive):
     * User attempts logs in with a registered account.
     */
    public function test_tc_login_003_user_attempts_login_with_registered_account(): void
    {
        $this->clearLoginRateLimiter('student@gmail.com');

        $this->browse(function (Browser $browser) {
            $browser->logout()
                // Step 1: Launch the app and navigate to /login
                ->visit('/login')
                ->assertPathIs('/login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                // Step 2: Input a registered email (student@gmail.com)
                ->type('email', 'student@gmail.com')
                // Step 3: Input registered password (12345)
                ->type('password', '12345')
                // Step 4: Tap Log in button -> redirected to Dashboard screen
                ->press('Log in')
                ->assertPathIs('/dashboard')
                ->assertSee('Student Dashboard')
                ->assertAuthenticated();
        });
    }

    /**
     * PBI #2 — TC.Login.004 (Negative):
     * User attempts logs in with a wrong email and password and wrong format.
     */
    public function test_tc_login_004_user_attempts_login_with_wrong_email_format_and_password(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                // Step 1: Launch the app and open /login
                ->visit('/login')
                ->assertPathIs('/login')
                // Step 2: Input invalid email format without "@" (e.g. notanemail)
                ->type('email', 'notanemail')
                // Step 3: Input a random password (123)
                ->type('password', '123')
                // Step 4: Tap Log in button -> form submission blocked, remains guest on /login
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertGuest();
        });
    }

    /**
     * PBI #2 — TC.Login.005 (Negative):
     * User attempts to logs in with a wrong email and password but right format.
     */
    public function test_tc_login_005_user_attempts_login_with_wrong_credentials_and_valid_email_format(): void
    {
        $this->clearLoginRateLimiter('unknown.user@gmail.com');

        $this->browse(function (Browser $browser) {
            $browser->logout()
                // Step 1: Launch the app and open /login
                ->visit('/login')
                ->assertPathIs('/login')
                // Step 2: Input a valid-format email (unknown.user@gmail.com)
                ->type('email', 'unknown.user@gmail.com')
                // Step 3: Input wrong password (wrong12345)
                ->type('password', 'wrong12345')
                // Step 4: Tap Log in button -> fails with validation message
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('These credentials do not match our records.')
                ->assertGuest();
        });
    }

    /**
     * PBI #2 — TC.Login.006 (Negative):
     * User attempts to log in using empty field inputs.
     */
    public function test_tc_login_006_user_attempts_login_using_empty_field_inputs(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                // Step 1: Launch the app and open /login
                ->visit('/login')
                ->assertPathIs('/login')
                // Step 2: Leave the Email field blank
                ->type('email', '')
                // Step 3: Leave the Password field blank
                ->type('password', '')
                // Step 4: Tap Log in button -> submission blocked, remains guest on /login
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertGuest();
        });
    }

    /**
     * PBI #2 — TC.Login.007 (Negative):
     * Brute Force Login attempt (4 consecutive failed login attempts).
     */
    public function test_tc_login_007_brute_force_login_attempt_is_rejected(): void
    {
        $this->clearLoginRateLimiter('admin@gmail.com');

        try {
            $this->browse(function (Browser $browser) {
                $browser->logout();

                // Steps 1–3: Open /login, input registered email (admin@gmail.com), submit 4 wrong password attempts
                for ($attempt = 1; $attempt <= 4; $attempt++) {
                    $browser->visit('/login')
                        ->assertPathIs('/login')
                        ->type('email', 'admin@gmail.com')
                        ->type('password', "wrongpass{$attempt}")
                        ->press('Log in')
                        ->assertPathIs('/login')
                        ->assertSee('These credentials do not match our records.');
                }

                // Step 4: Verify user remains unauthenticated (guest)
                $browser->assertGuest();
            });
        } finally {
            $this->clearLoginRateLimiter('admin@gmail.com');
        }
    }

    /**
     * PBI #2 — TC.Login.008 (Negative):
     * Lockout after too many attempts (> 5 failed attempts triggers RateLimiter throttle).
     */
    public function test_tc_login_008_lockout_after_too_many_failed_login_attempts(): void
    {
        $this->clearLoginRateLimiter('admin@gmail.com');

        try {
            $this->browse(function (Browser $browser) {
                $browser->logout();

                // Steps 1–3: Submit 5 failed attempts followed by the 6th attempt with admin@gmail.com
                for ($attempt = 1; $attempt <= 6; $attempt++) {
                    $browser->visit('/login')
                        ->type('email', 'admin@gmail.com')
                        ->type('password', 'invalidpassword')
                        ->press('Log in');
                }

                // Step 4: Observe lockout error message ("Too many login attempts. Please try again in X seconds.")
                $browser->assertPathIs('/login')
                    ->assertSee('Too many login attempts')
                    ->assertGuest();
            });
        } finally {
            $this->clearLoginRateLimiter('admin@gmail.com');
        }
    }
}
