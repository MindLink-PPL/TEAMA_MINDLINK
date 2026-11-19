<?php

namespace Tests\Browser\Sprint1\InformedConsentManagement;

use App\Models\InformedConsent;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InformedConsentTest extends DuskTestCase
{
    /**
     * 1. PBI #6 - TC.InformedConsent.001 (Positive):
     * Student reviews and accepts the informed consent step in the unified wizard.
     */
    public function test_tc_informed_consent_001_submit_valid_agreement(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/counselling/request?step=2')
                ->waitForText('Step 2: Informed Consent Agreement')
                ->assertSee('Informed Consent & Data Usage Policy')
                ->check('#is_agreed')
                ->press('Continue to Schedule →')
                ->waitForText('Choose an available time & format')
                ->assertSee('Choose an available time & format');
        });
    }

    /**
     * 2. PBI #6 - TC.InformedConsent.002 (Negative):
     * Student attempts to proceed from Step 2 without checking the agreement box.
     */
    public function test_tc_informed_consent_002_submit_blocked_when_unchecked(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/counselling/request?step=2')
                ->waitForText('Step 2: Informed Consent Agreement')
                ->assertNotChecked('#is_agreed')
                ->press('Continue to Schedule →')
                ->waitForText('You must accept the Informed Consent Agreement (UU PDP) to proceed.')
                ->assertSee('You must accept the Informed Consent Agreement (UU PDP) to proceed.');
        });
    }

    /**
     * 3. PBI #7 - TC.AdminConsent.001 (Positive):
     * Administrator views submitted student consent records in the admin table.
     */
    public function test_tc_admin_consent_001_view_submitted_records(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        InformedConsent::updateOrCreate(
            [
                'user_id' => $student->id,
                'policy_version' => InformedConsent::CURRENT_VERSION,
            ],
            [
                'agreed_at' => now(),
            ]
        );

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/consents')
                ->assertSee('Student Consent Records')
                ->assertSee('Student ID')
                ->assertSee('Consent Status')
                ->assertSee('Agreed At')
                ->assertSee('Agreed');
        });
    }

    /**
     * 4. PBI #7 - TC.AdminConsent.002 (Negative):
     * Administrator accesses consent records cleanly without SQL/server errors.
     */
    public function test_tc_admin_consent_002_page_loads_gracefully(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/consents')
                ->assertSee('Student Consent Records')
                ->assertDontSee('SQLSTATE')
                ->assertDontSee('Whoops');
        });
    }

    /**
     * 5. PBI #8 - TC.DataPolicy.001 (Positive):
     * Student reviews all four sections of the data usage policy before consenting.
     */
    public function test_tc_data_policy_001_review_four_policy_sections(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/counselling/request?step=2')
                ->waitForText('Step 2: Informed Consent Agreement')
                ->assertSee('1. Purpose of Data Collection')
                ->assertSee('2. Confidentiality & Privacy Boundaries')
                ->assertSee('3. Data Sharing & Legal Exceptions')
                ->assertSee('4. Non-Emergency & Non-Diagnostic Scope')
                ->check('#is_agreed')
                ->press('Continue to Schedule →')
                ->waitForText('Choose an available time & format')
                ->assertSee('Choose an available time & format');
        });
    }

    /**
     * 6. PBI #8 - TC.DataPolicy.002 (Negative):
     * Student declines the informed consent and data usage terms.
     */
    public function test_tc_data_policy_002_decline_consent_flow(): void
    {
        $student = User::where('email', 'student@gmail.com')->firstOrFail();

        $this->browse(function (Browser $browser) use ($student) {
            $browser->loginAs($student)
                ->visit('/counselling/request?step=2')
                ->waitForText('Step 2: Informed Consent Agreement')
                ->press('Decline')
                ->waitFor('#declineAlert')
                ->assertVisible('#declineAlert')
                ->assertSee('Counselling services cannot proceed without your informed consent.')
                ->assertNotChecked('#is_agreed');
        });
    }
}