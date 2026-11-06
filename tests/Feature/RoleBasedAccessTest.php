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

        $this->adminUser = User::where('email', 'admin@gmail.com')->first();
        $this->counsellorUser = User::where('email', 'counsellor@mindlink.com')->first();
        $this->standardUser = User::where('email', 'student@mindlink.com')->first();
    }

    public function test_default_admin_account_is_seeded_with_admin_role(): void
    {
        $this->assertNotNull($this->adminUser);
        $this->assertEquals('Admin', $this->adminUser->name);
        $this->assertEquals('admin@gmail.com', $this->adminUser->email);
        $this->assertTrue($this->adminUser->isAdmin());
    }

    public function test_default_admin_can_log_in_with_email_and_password_12345(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@gmail.com',
            'password' => '12345',
        ]);

        $this->assertAuthenticatedAs($this->adminUser);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_default_admin_cannot_log_in_with_wrong_password(): void
    {
        $this->post('/login', [
            'email' => 'admin@gmail.com',
            'password' => 'wrong',
        ]);

        $this->assertGuest();
    }

    public function test_default_student_account_is_seeded_and_can_log_in(): void
    {
        $testStudent = User::where('email', 'student@gmail.com')->first();

        $this->assertNotNull($testStudent);
        $this->assertEquals('Student', $testStudent->name);
        $this->assertTrue($testStudent->isStudent());

        $response = $this->post('/login', [
            'email' => 'student@gmail.com',
            'password' => '12345',
        ]);

        $this->assertAuthenticatedAs($testStudent);
        $response->assertRedirect(route('dashboard', absolute: false));
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
        $response->assertSee('admin@gmail.com');
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
        $response->assertSee('Start check-in');
        $response->assertSee('Request counselling');
        $response->assertSee('Request support');
        $response->assertSee('Learn about mental health');
        $response->assertSee('Browse articles');
        $response->assertSee('How are you feeling today?');
        $response->assertSee('Upcoming appointment');
        $response->assertSee('Need urgent help?');
        $response->assertSee('Student portal');
    }

    public function test_student_role_has_jira_description_and_student_permissions(): void
    {
        $this->assertEquals('Student', $this->studentRole->name);
        $this->assertEquals(
            'As a student, I want to access student-specific features so that I can manage my wellbeing and counselling activities securely.',
            $this->studentRole->description
        );
        $this->assertTrue($this->standardUser->isStudent());
        $this->assertTrue($this->standardUser->hasRole('student'));
        $this->assertTrue($this->standardUser->hasPermission('dashboard.view'));
        $this->assertTrue($this->standardUser->hasPermission('wellbeing.checkin'));
        $this->assertTrue($this->standardUser->hasPermission('literacy.view'));
        $this->assertTrue($this->standardUser->hasPermission('counselling.request'));
    }

    public function test_student_can_access_wellbeing_checkin_quiz_page_and_submit_reflection(): void
    {
        $response = $this->actingAs($this->standardUser)->get(route('student.wellbeing'));

        $response->assertStatus(200);
        $response->assertSee('Wellbeing Check In');
        $response->assertSee('A short reflection');
        $response->assertSee('6 of 10');
        $response->assertSee('How often have you found it difficult to concentrate on study or daily activities?');
        $response->assertSee('Several days');

        $submitResponse = $this->actingAs($this->standardUser)->post(route('student.wellbeing.store'), [
            'answers' => [1 => 1, 2 => 1, 3 => 2, 4 => 1, 5 => 1, 6 => 1, 7 => 0, 8 => 1, 9 => 1, 10 => 1],
        ]);

        $submitResponse->assertRedirect(route('student.wellbeing'));
        $submitResponse->assertSessionHas('success');
        $this->assertDatabaseHas('wellbeing_checkins', [
            'user_id' => $this->standardUser->id,
            'total_score' => 10,
        ]);
    }

    public function test_student_can_save_daily_mood_from_student_dashboard(): void
    {
        $response = $this->actingAs($this->standardUser)->post(route('student.mood.store'), [
            'mood' => 'Great',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('wellbeing_checkins', [
            'user_id' => $this->standardUser->id,
            'mood' => 'Great',
        ]);
    }

    public function test_student_can_access_mental_health_literacy_page_and_filter_articles(): void
    {
        $response = $this->actingAs($this->standardUser)->get(route('student.literacy'));

        $response->assertStatus(200);
        $response->assertSee('Mental Health Literacy');
        $response->assertSee('Find clear, practical information');
        $response->assertSee('Understanding anxious thoughts');
        $response->assertSee('Planning during a difficult week');
        $response->assertSee('A practical sleep routine');

        $filtered = $this->actingAs($this->standardUser)->get(route('student.literacy', ['topic' => 'Sleep']));
        $filtered->assertStatus(200);
        $filtered->assertSee('A practical sleep routine');
    }

    public function test_student_can_access_counselling_scheduling_page_and_make_an_appointment(): void
    {
        $response = $this->actingAs($this->standardUser)->get(route('student.counselling'));

        $response->assertStatus(200);
        $response->assertSee('Counselling Request');
        $response->assertSee('Request private support');
        $response->assertSee('Choose an available time');
        $response->assertSee('Alisa • Student wellbeing');
        $response->assertSee('Your request');
        $response->assertSee('Academic pressure and anxiety');
        $response->assertSee('Review the request before submission.');

        $scheduleAlias = $this->actingAs($this->standardUser)->get(route('student.schedule'));
        $scheduleAlias->assertStatus(200);

        $bookResponse = $this->actingAs($this->standardUser)->post(route('student.counselling.store'), [
            'counsellor_name' => 'Alisa • Student wellbeing',
            'concern' => 'Academic pressure and anxiety',
            'scheduled_date' => 'Wednesday, 21 October 2026',
            'scheduled_time' => '13:30',
            'session_mode' => 'In person',
            'consent' => 1,
        ]);

        $bookResponse->assertRedirect(route('student.counselling'));
        $bookResponse->assertSessionHas('success');
        $this->assertDatabaseHas('counselling_requests', [
            'user_id' => $this->standardUser->id,
            'scheduled_time' => '13:30',
            'status' => 'Confirmed',
        ]);
    }

    public function test_non_student_roles_cannot_access_student_specific_routes(): void
    {
        $this->actingAs($this->adminUser)->get(route('student.wellbeing'))->assertStatus(403);
        $this->actingAs($this->counsellorUser)->get(route('student.counselling'))->assertStatus(403);
    }
}
