<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * PBI #1 — TC.Login.001: User login menggunakan email dan password yang sudah ter-registrasi (Admin).
     */
    public function test_tc_login_001_registered_user_can_log_in_with_valid_email_and_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Email')
            ->assertSee('Password');

        $response = $this->post('/login', [
            'email' => 'admin@gmail.com',
            'password' => '12345',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * PBI #2 — TC.Login.002: User login menggunakan email dan password yang salah.
     */
    public function test_tc_login_002_user_cannot_log_in_with_unregistered_email_and_wrong_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@mindlink.com',
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * PBI #2 — TC.Login.003: User attempts logs in with a registered account (Student).
     */
    public function test_tc_login_003_registered_student_account_can_log_in(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'email' => 'student@gmail.com',
            'password' => '12345',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * PBI #2 — TC.Login.004: User attempts logs in with a wrong email format and password.
     */
    public function test_tc_login_004_login_fails_when_email_format_is_invalid(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'notanemail',
            'password' => '123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * PBI #2 — TC.Login.005: User attempts to log in with wrong email and password but valid format.
     */
    public function test_tc_login_005_login_fails_with_valid_email_format_but_wrong_credentials(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->from('/login')->post('/login', [
            'email' => 'unknown.user@gmail.com',
            'password' => 'wrong12345',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * PBI #2 — TC.Login.006: User attempts to log in using empty field inputs.
     */
    public function test_tc_login_006_login_fails_when_email_and_password_fields_are_empty(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * PBI #2 — TC.Login.007: Brute Force Login attempt (4 consecutive failed login attempts).
     */
    public function test_tc_login_007_brute_force_login_attempts_are_rejected_and_keep_user_guest(): void
    {
        $this->seed(DatabaseSeeder::class);

        for ($i = 1; $i <= 4; $i++) {
            $response = $this->from('/login')->post('/login', [
                'email' => 'admin@gmail.com',
                'password' => "wrongpass{$i}",
            ]);

            $response->assertRedirect('/login');
            $response->assertSessionHasErrors('email');
        }

        $this->assertGuest();
    }

    /**
     * PBI #2 — TC.Login.008: Lockout after too many attempts (> 5 failed login attempts).
     */
    public function test_tc_login_008_user_is_locked_out_after_exceeding_five_failed_login_attempts(): void
    {
        $this->seed(DatabaseSeeder::class);

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', [
                'email' => 'admin@gmail.com',
                'password' => 'invalidpassword',
            ]);
        }

        $lockoutResponse = $this->from('/login')->post('/login', [
            'email' => 'admin@gmail.com',
            'password' => 'invalidpassword',
        ]);

        $lockoutResponse->assertRedirect('/login');
        $lockoutResponse->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }
}
