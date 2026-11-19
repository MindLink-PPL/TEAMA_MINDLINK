<?php

namespace Tests\Browser\Sprint1\UserLogin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthenticationDuskTest extends DuskTestCase
{
    /**
     * Clear rate limiter keys for a given email across local loopback addresses and cache stores.
     */
    protected function clearLoginRateLimiter(string $email): void
    {
        $normalized = strtolower($email);
        RateLimiter::clear("{$normalized}|127.0.0.1");
        RateLimiter::clear("{$normalized}|::1");

        try {
            Cache::store('file')->flush();
        } catch (\Throwable) {
        }

        try {
            if (Schema::hasTable('cache')) {
                DB::table('cache')->truncate();
            }
        } catch (\Throwable) {
        }
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
                ->visit('/login')
                ->assertPathIs('/login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->type('email', 'admin@gmail.com')
                ->type('password', '12345')
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
                ->visit('/login')
                ->assertPathIs('/login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->type('email', 'wrong@mindlink.com')
                ->type('password', 'wrongpass')
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
                ->visit('/login')
                ->assertPathIs('/login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->type('email', 'student@gmail.com')
                ->type('password', '12345')
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
                ->visit('/login')
                ->assertPathIs('/login')
                ->type('email', 'notanemail')
                ->type('password', '123')
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
                ->visit('/login')
                ->assertPathIs('/login')
                ->type('email', 'unknown.user@gmail.com')
                ->type('password', 'wrong12345')
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
                ->visit('/login')
                ->assertPathIs('/login')
                ->type('email', '')
                ->type('password', '')
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

                for ($attempt = 1; $attempt <= 4; $attempt++) {
                    $browser->visit('/login')
                        ->assertPathIs('/login')
                        ->type('email', 'admin@gmail.com')
                        ->type('password', "wrongpass{$attempt}")
                        ->press('Log in')
                        ->assertPathIs('/login')
                        ->assertSee('These credentials do not match our records.');
                }

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

                for ($attempt = 1; $attempt <= 6; $attempt++) {
                    $browser->visit('/login')
                        ->type('email', 'admin@gmail.com')
                        ->type('password', 'invalidpassword')
                        ->press('Log in');
                }

                $browser->assertPathIs('/login')
                    ->assertSee('Too many login attempts')
                    ->assertGuest();
            });
        } finally {
            $this->clearLoginRateLimiter('admin@gmail.com');
        }
    }
}