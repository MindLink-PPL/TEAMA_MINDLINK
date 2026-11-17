<?php

namespace Tests\Browser\CounsellorDashboard;

use App\Models\CounsellingRequest;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC006_TamperedStatusNegativeTest extends DuskTestCase
{
    public function test_backend_rejects_tampered_invalid_status_value(): void
    {
        $request = CounsellingRequest::create([
            'student_id' => 1,
            'counsellor_name' => 'Alisa',
            'concern' => 'TamperedStatusTest: invalid status injection.',
            'appointment_date' => now()->addDays(8)->format('Y-m-d'),
            'appointment_time' => '16:30',
            'appointment_mode' => 'Online Meeting',
            'status' => 'Under Review',
        ]);

        try {
            $this->browse(function (Browser $browser) {
                $browser->visit('/counsellor/dashboard')
                    ->waitForText('Student Counselling Requests')
                    ->clickAtXPath("//tr[contains(., 'TamperedStatusTest: invalid status injection')]//button[contains(., 'Review & Assess')]")
                    ->pause(300)
                    ->script("
                        let form = document.querySelector('form[action*=\"/status\"]');
                        let statusInput = form.querySelector('input[name=\"status\"]');
                        if (statusInput) {
                            statusInput.value = 'HackedStatus';
                            form.submit();
                        }
                    ");

                $browser->waitForText('Invalid status action selected.')
                    ->assertSee('Invalid status action selected.');
            });

            $this->assertDatabaseHas('counselling_requests', [
                'request_id' => $request->request_id,
                'status' => 'Under Review',
            ]);
        } finally {
            $request->delete();
        }
    }
}