<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InformedConsentTest extends DuskTestCase
{
    /**
     * 1. PBI #6 - TC.InformedConsent.001 (Positive):
     * Student reviews and submits informed consent form with valid agreement
     */
    public function test_tc_informed_consent_001_submit_valid_agreement(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/test-consent')
                    ->assertSee('Informed Consent & Data Usage Policy')
                    ->check('#is_agreed')
                    ->assertEnabled('#submitBtn')
                    ->press('Agree & Continue')
                    ->waitForText('Success! Informed consent data has been saved to the database.')
                    ->assertSee('Success! Informed consent data has been saved to the database.');
        });
    }

    /**
     * 2. PBI #6 - TC.InformedConsent.002 (Negative):
     * Student attempts to submit consent form without checking the agreement box
     */
    public function test_tc_informed_consent_002_submit_blocked_when_unchecked(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/test-consent')
                    ->assertNotChecked('#is_agreed')
                    ->assertDisabled('#submitBtn');
        });
    }

    /**
     * 3. PBI #7 - TC.AdminConsent.001 (Positive):
     * Administrator views submitted student consent records in the admin table
     */
    public function test_tc_admin_consent_001_view_submitted_records(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/consents')
                    ->assertSee('Student Consent Records')
                    ->assertSee('Student ID')
                    ->assertSee('Consent Status')
                    ->assertSee('Agreed At')
                    ->assertSee('Agreed');
        });
    }

    /**
     * 4. PBI #8 - TC.DataPolicy.001 (Positive):
     * Student reviews all four sections of the data usage policy before consenting
     */
    public function test_tc_data_policy_001_review_four_policy_sections(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/test-consent')
                    ->assertSee('1. Purpose of Data Collection')
                    ->assertSee('2. Confidentiality & Privacy Boundaries')
                    ->assertSee('3. Data Sharing & Legal Exceptions')
                    ->assertSee('4. Non-Emergency & Non-Diagnostic Scope')
                    ->check('#is_agreed')
                    ->press('Agree & Continue')
                    ->waitForText('Success! Informed consent data has been saved to the database.');
        });
    }

    /**
     * 5. PBI #8 - TC.DataPolicy.002 (Negative):
     * Student declines the informed consent and data usage terms
     */
    public function test_tc_data_policy_002_decline_consent_flow(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/test-consent')
                    ->press('Decline')
                    ->assertVisible('#declineAlert')
                    ->assertSee('Counselling services cannot proceed without your informed consent.')
                    ->assertNotChecked('#is_agreed')
                    ->assertDisabled('#submitBtn');
        });
    }
}