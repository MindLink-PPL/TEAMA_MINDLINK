<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminRoleAccessDuskTest extends DuskTestCase
{
    /**
     * PBI #3 — TC.RoleAccess.001 (Positive):
     * Administrator accesses the Administrative Dashboard (PG-06) and Privacy Boundary notice.
     */
    public function test_tc_role_access_001_admin_accesses_administrative_dashboard_pg06_and_privacy_boundary(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                // Step 1: Log in with admin@gmail.com / 12345 -> Redirected to /dashboard
                ->visit('/login')
                ->type('email', 'admin@gmail.com')
                ->type('password', '12345')
                ->press('Log in')
                ->assertPathIs('/dashboard')
                // Step 2: Verify top header and portal pill
                ->assertSee('Administrative Dashboard')
                ->assertSee('Admin portal')
                // Step 3: Inspect Service Overview cards and Weekly Request Activity chart
                ->assertSee('Service overview')
                ->assertSee('Registered users')
                ->assertSee('Active Roles')
                ->assertSee('Permissions active')
                ->assertSee('Weekly request activity')
                // Step 4: Inspect Privacy Boundary card
                ->assertSee('Privacy Boundary')
                ->assertSee('Administration sees service data, not counselling notes.');
        });
    }

    /**
     * PBI #3 — TC.RoleAccess.002 (Positive):
     * Administrator views User & Role Management directory and Permission Matrix.
     */
    public function test_tc_role_access_002_admin_views_user_and_role_management_directory_and_permission_matrix(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

            $browser->loginAs($admin)
                // Step 1: Visit /admin/users
                ->visit('/admin/users')
                ->assertPathIs('/admin/users')
                ->assertSee('User & Role Management')
                ->assertSee('Role & Access Control')
                // Step 2: Inspect top summary cards
                ->assertSee('Total accounts')
                ->assertSee('Administrators')
                ->assertSee('Counsellors / Staff')
                ->assertSee('Students')
                // Step 3: Inspect user directory table
                ->assertSee('Alex Rivera (Student)')
                ->assertSee('Alisa Vance (Counsellor)')
                ->assertSee('Granted Permissions')
                // Step 4: Scroll to System Role & Permission Matrix
                ->assertSee('System Role & Permission Matrix')
                ->assertSee('Full Administrative Clearance (All)');
        });
    }

    /**
     * PBI #3 — TC.RoleAccess.003 (Positive):
     * Administrator updates another user's role and permissions reflect immediately.
     */
    public function test_tc_role_access_003_admin_updates_another_user_role_and_permissions_reflect_immediately(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $alex = User::where('email', 'student@mindlink.com')->firstOrFail();
        $counsellorRole = Role::where('slug', 'counsellor')->firstOrFail();
        $studentRole = Role::where('slug', 'user')->firstOrFail();

        // Ensure Alex starts with the Student role
        $alex->update(['role_id' => $studentRole->id]);

        try {
            $this->browse(function (Browser $browser) use ($admin, $alex, $counsellorRole) {
                $browser->loginAs($admin)
                    // Step 1: Locate "Alex Rivera (Student)" in the User & Role Management table
                    ->visit('/admin/users')
                    ->assertPathIs('/admin/users')
                    ->assertSee('Alex Rivera (Student)')
                    // Step 2: Select "Counsellor" from the Assign Role dropdown for that user
                    ->select("@role-select-{$alex->id}", (string) $counsellorRole->id)
                    // Step 3: Click the "Update" button
                    ->click("@update-role-{$alex->id}")
                    // Step 4: Verify flash message and updated permissions
                    ->assertPathIs('/admin/users')
                    ->assertSee('Updated role for Alex Rivera (Student)')
                    ->assertSee('Counsellor Workspace');
            });

            $alex->refresh();
            $this->assertTrue($alex->hasPermission('counsellor.workspace'));
        } finally {
            // Restore Alex Rivera back to Student role for subsequent tests
            $alex->update(['role_id' => $studentRole->id]);
        }
    }

    /**
     * PBI #3 — TC.RoleAccess.004 (Negative):
     * Administrator attempts to demote their own Administrator account.
     */
    public function test_tc_role_access_004_admin_cannot_demote_their_own_administrator_account(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $studentRole = Role::where('slug', 'user')->firstOrFail();

        $this->browse(function (Browser $browser) use ($admin, $studentRole) {
            $browser->loginAs($admin)
                // Step 1: Navigate to /admin/users and verify Admin row shows "You" and "Locked (Self)"
                ->visit('/admin/users')
                ->assertPathIs('/admin/users')
                ->assertSee('admin@gmail.com')
                ->assertSee('You')
                ->assertSee('Locked (Self)');

            // Step 2: Attempt to send PATCH /admin/users/{admin_id}/role with Student role_id
            $actionUrl = route('admin.users.update-role', $admin, false);
            $roleId = (int) $studentRole->id;
            $browser->script("
                const token = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{$actionUrl}';
                form.innerHTML = '<input type=\"hidden\" name=\"_token\" value=\"' + token + '\">'
                    + '<input type=\"hidden\" name=\"_method\" value=\"PATCH\">'
                    + '<input type=\"hidden\" name=\"role_id\" value=\"{$roleId}\">';
                document.body.appendChild(form);
                form.submit();
            ");

            // Step 3: Observe safeguard error message
            $browser->assertPathIs('/admin/users')
                ->assertSee('Security protection: You cannot alter your own Administrator role to prevent lockout.');
        });

        // Step 4: Verify Admin role in database remains Administrator
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    /**
     * PBI #3 — TC.RoleAccess.005 (Negative):
     * Non-admin users (Student & Counsellor) attempt to access /admin/users and receive 403 Forbidden.
     */
    public function test_tc_role_access_005_non_admin_users_are_forbidden_from_accessing_admin_users(): void
    {
        $this->browse(function (Browser $browser) {
            $student = User::where('email', 'student@gmail.com')->firstOrFail();
            $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

            // Steps 1 & 2: Log in as Student and attempt to navigate directly to /admin/users
            $browser->loginAs($student)
                ->visit('/admin/users')
                ->assertSee('403');

            // Steps 3 & 4: Log in as Counsellor and attempt to navigate directly to /admin/users
            $browser->loginAs($counsellor)
                ->visit('/admin/users')
                ->assertSee('403');
        });
    }

    /**
     * PBI #3 — TC.RoleAccess.008 (Positive):
     * Newly registered user is automatically assigned the default Student role.
     */
    public function test_tc_role_access_008_newly_registered_user_is_assigned_default_student_role(): void
    {
        $uniqueEmail = 'dusk.student.' . time() . '@mindlink.com';

        try {
            $this->browse(function (Browser $browser) use ($uniqueEmail) {
                $browser->logout()
                    // Step 1: Open /register page
                    ->visit('/register')
                    ->assertPathIs('/register')
                    // Step 2: Input Name, Email, Password, and Confirm Password
                    ->type('name', 'Dusk Registered Student')
                    ->type('email', $uniqueEmail)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    // Step 3: Click Register button
                    ->press('Register')
                    // Step 4: Verify assigned role and redirect to Student Dashboard (PG-01)
                    ->assertPathIs('/dashboard')
                    ->assertSee('Student Dashboard')
                    ->assertSee('Student portal');
            });

            $newUser = User::where('email', $uniqueEmail)->firstOrFail();
            $this->assertTrue($newUser->isStudent());
        } finally {
            User::where('email', $uniqueEmail)->delete();
        }
    }
}