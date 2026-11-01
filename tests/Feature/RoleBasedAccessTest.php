<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminRole;
    protected Role $counsellorRole;
    protected Role $studentRole;
    protected User $adminUser;
    protected User $standardUser;
    protected User $counsellorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles, permissions, and users
        $this->seed(DatabaseSeeder::class);

        $this->adminRole = Role::where('slug', 'admin')->first();
        $this->counsellorRole = Role::where('slug', 'counsellor')->first();
        $this->studentRole = Role::where('slug', 'user')->first();

        $this->adminUser = User::where('email', 'admin@mindlink.com')->first();
        $this->counsellorUser = User::where('email', 'counsellor@mindlink.com')->first();
        $this->standardUser = User::where('email', 'student@mindlink.com')->first();
    }

    public function test_guest_is_redirected_to_login_when_accessing_role_management(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    public function test_standard_user_is_forbidden_from_accessing_role_management(): void
    {
        $response = $this->actingAs($this->standardUser)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_counsellor_is_forbidden_from_accessing_role_management(): void
    {
        $response = $this->actingAs($this->counsellorUser)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_administrator_can_view_role_management_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('User &amp; Role Management', false);
        $response->assertSee('System Administrator');
        $response->assertSee('Alisa Vance (Counsellor)');
        $response->assertSee('Alex Rivera (Student)');
        $response->assertSee('System Role &amp; Permission Matrix', false);
    }

    public function test_administrator_can_change_user_role_and_it_reflects_permissions(): void
    {
        // Initial state: standardUser has 'user' (student) role
        $this->assertEquals('user', $this->standardUser->role->slug);
        $this->assertFalse($this->standardUser->hasPermission('counsellor.workspace'));

        // Admin elevates standardUser to Counsellor
        $response = $this->actingAs($this->adminUser)->patch(
            route('admin.users.update-role', $this->standardUser),
            ['role_id' => $this->counsellorRole->id]
        );

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->standardUser->refresh();
        $this->assertEquals('counsellor', $this->standardUser->role->slug);
        // Permissions reflect immediately
        $this->assertTrue($this->standardUser->hasPermission('counsellor.workspace'));
        $this->assertTrue($this->standardUser->hasPermission('users.view'));
    }

    public function test_administrator_cannot_demote_themselves(): void
    {
        $response = $this->actingAs($this->adminUser)->patch(
            route('admin.users.update-role', $this->adminUser),
            ['role_id' => $this->studentRole->id]
        );

        $response->assertSessionHas('error');
        $this->adminUser->refresh();
        $this->assertEquals('admin', $this->adminUser->role->slug);
    }

    public function test_registration_assigns_default_user_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Newly Registered Student',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertNotNull($newUser->role);
        $this->assertEquals('user', $newUser->role->slug);
        $this->assertFalse($newUser->isAdmin());
    }

    public function test_admin_dashboard_shows_figma_pg06_design_elements(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Administrative Dashboard');
        $response->assertSee('Service overview');
        $response->assertSee('Weekly request activity');
        $response->assertSee('Content status');
        $response->assertSee('Privacy Boundary');
        $response->assertSee('Admin portal');
    }

    public function test_counsellor_dashboard_shows_figma_pg05_design_elements(): void
    {
        $response = $this->actingAs($this->counsellorUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Counsellor Workspace');
        $response->assertSee("Today's workload", false);
        $response->assertSee('Request queue');
        $response->assertSee('Counsellor portal');
    }

    public function test_student_dashboard_shows_figma_pg01_design_elements(): void
    {
        $response = $this->actingAs($this->standardUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Student Dashboard');
        $response->assertSee('Check in');
        $response->assertSee('How are you feeling today?');
        $response->assertSee('Student portal');
    }
}
