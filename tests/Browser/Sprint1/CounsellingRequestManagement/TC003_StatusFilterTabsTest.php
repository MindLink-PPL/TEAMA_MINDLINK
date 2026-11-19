<?php

namespace Tests\Browser\Sprint1\CounsellingRequestManagement;

use App\Models\CounsellingRequest;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC003_StatusFilterTabsTest extends DuskTestCase
{
    public function test_status_filter_tabs_filter_requests_correctly(): void
    {
        $counsellor = User::where('email', 'counsellor@gmail.com')->firstOrFail();

        $pending = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'FilterTest_Pending_Concern_Text',
            'appointment_date' => now()->addDays(5)->format('Y-m-d'),
            'appointment_time' => '09:00',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Under Review',
        ]);

        $approved = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'FilterTest_Approved_Concern_Text',
            'appointment_date' => now()->addDays(5)->format('Y-m-d'),
            'appointment_time' => '10:00',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Approved',
        ]);

        $declined = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'FilterTest_Declined_Concern_Text',
            'appointment_date' => now()->addDays(5)->format('Y-m-d'),
            'appointment_time' => '11:00',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Declined',
            'counsellor_notes' => 'Referred externally.',
        ]);

        try {
            $this->browse(function (Browser $browser) use ($counsellor) {
                $browser->loginAs($counsellor)
                    ->visit('/counsellor/dashboard')
                    ->waitForText('Student Counselling Requests')
                    ->press('Approved')
                    ->pause(200)
                    ->assertSee('FilterTest_Approved_Concern_Text')
                    ->assertDontSee('FilterTest_Pending_Concern_Text')
                    ->assertDontSee('FilterTest_Declined_Concern_Text')
                    ->press('Declined')
                    ->pause(200)
                    ->assertSee('FilterTest_Declined_Concern_Text')
                    ->assertDontSee('FilterTest_Approved_Concern_Text')
                    ->press('Under Review')
                    ->pause(200)
                    ->assertSee('FilterTest_Pending_Concern_Text')
                    ->assertDontSee('FilterTest_Approved_Concern_Text')
                    ->press('All')
                    ->pause(200)
                    ->assertSee('FilterTest_Pending_Concern_Text')
                    ->assertSee('FilterTest_Approved_Concern_Text')
                    ->assertSee('FilterTest_Declined_Concern_Text');
            });
        } finally {
            $pending->delete();
            $approved->delete();
            $declined->delete();
        }
    }
}