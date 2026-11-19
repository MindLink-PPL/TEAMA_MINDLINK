<?php

namespace Tests\Browser\Sprint1\RoleBasedAccessControl;

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
                ->visit('/login')
                ->type('email', 'admin@gmail.com')
                ->type('password', '12345')
                ->press('Log in')
                ->assertPathIs('/dashboard')
                ->assertSee('Administrative Dashboard')
                ->assertSee('Admin portal')
                ->assertSee('Service overview')
                ->assertSee('Registered users')
                ->assertSee('Active Roles')
                ->assertSee('Permissions active')
                ->assertSee('Weekly request activity')
                ->assertSee('Privacy Boundary')
                ->assertSee('Administration sees service data, not counselling notes.')
                ->clickLink('Content')
                ->assertPathIs('/admin/articles')
                ->assertSee('Manage Mental Health Articles')
                ->assertSee('Admin portal')
                ->assertSee('Overview')
                ->assertSee('Users & Roles')
                ->clickLink('Schedules & Verification')
                ->assertPathIs('/admin/dashboard')
                ->assertSee('Counsellor Verification Dashboard')
                ->assertSee('Admin portal')
                ->assertSee('Overview')
                ->assertSee('Users & Roles');
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
                ->visit('/admin/users')
                ->assertPathIs('/admin/users')
                ->assertSee('User & Role Management')
                ->assertSee('Role & Access Control')
                ->assertSee('Total accounts')
                ->assertSee('Administrators')
                ->assertSee('Counsellors / Staff')
                ->assertSee('Students')
                ->assertSee('Alex Rivera (Student)')
                ->assertSee('Alisa Vance (Counsellor)')
                ->assertSee('Granted Permissions')
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

        $alex->update(['role_id' => $studentRole->id]);

        try {
            $this->browse(function (Browser $browser) use ($admin, $alex, $counsellorRole) {
                $browser->loginAs($admin)
                    ->visit('/admin/users')
                    ->assertPathIs('/admin/users')
                    ->assertSee('Alex Rivera (Student)')
                    ->select("@role-select-{$alex->id}", (string) $counsellorRole->id)
                    ->click("@update-role-{$alex->id}")
                    ->assertPathIs('/admin/users')
                    ->assertSee('Updated role for Alex Rivera (Student)')
                    ->assertSee('Counsellor Workspace');
            });

            $alex->refresh();
            $this->assertTrue($alex->hasPermission('counsellor.workspace'));
        } finally {
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
                ->visit('/admin/users')
                ->assertPathIs('/admin/users')
                ->assertSee('admin@gmail.com')
                ->assertSee('You')
                ->assertSee('Locked (Self)');

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

            $browser->assertPathIs('/admin/users')
                ->assertSee('Security protection: You cannot alter your own Administrator role to prevent lockout.');
        });

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

            $browser->loginAs($student)
                ->visit('/admin/users')
                ->assertSee('403');

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
                    ->visit('/register')
                    ->assertPathIs('/register')
                    ->type('name', 'Dusk Registered Student')
                    ->type('email', $uniqueEmail)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Register')
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