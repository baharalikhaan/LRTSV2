<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Help Center tab visibility: role-based user manuals.
 *
 *  - LPI    → General + LPI tabs only
 *  - Reviewer → General + Reviewer tabs only
 *  - Admin  → all four tabs (oversight)
 *  A tab outside the allowed set falls back to the role default
 *  (e.g. ?tab=admin opened by an LPI shows the LPI manual).
 *
 * Runs against the dedicated test database (phpunit.xml -> rtsnew_test) and
 * rolls back every test via DatabaseTransactions, so no real data is touched.
 */
class HelpCenterTabsTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    public function test_lpi_sees_only_general_and_lpi_tabs()
    {
        $lpi = $this->activeUser('LPI');
        if (!$lpi) {
            $this->markTestSkipped('No active LPI in test database.');
        }

        $html = $this->actingAs($lpi)->get('/about/help')->assertOk()->getContent();

        $this->assertStringContainsString('tab=general', $html);
        $this->assertStringContainsString('tab=workflow', $html, 'LPI must see the System workflow tab.');
        $this->assertStringContainsString('tab=lpi', $html);
        $this->assertStringNotContainsString('tab=admin"', $html, 'LPI must not see the Administrator tab.');
        $this->assertStringNotContainsString('tab=reviewer"', $html, 'LPI must not see the Reviewer tab.');

        // Default tab for an LPI is his own manual.
        $this->assertStringContainsString('tab=lpi" class="help-tab active"', $html);
    }

    public function test_reviewer_sees_only_general_and_reviewer_tabs()
    {
        $reviewer = $this->activeUser('Reviewer');
        if (!$reviewer) {
            $this->markTestSkipped('No active Reviewer in test database.');
        }

        $html = $this->actingAs($reviewer)->get('/about/help')->assertOk()->getContent();

        $this->assertStringContainsString('tab=general', $html);
        $this->assertStringContainsString('tab=workflow', $html, 'Reviewer must see the System workflow tab.');
        $this->assertStringContainsString('tab=reviewer', $html);
        $this->assertStringNotContainsString('tab=admin"', $html, 'Reviewer must not see the Administrator tab.');
        $this->assertStringNotContainsString('tab=lpi"', $html, 'Reviewer must not see the LPI tab.');
    }

    public function test_admin_sees_all_four_tabs()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active Admin in test database.');
        }

        $html = $this->actingAs($admin)->get('/about/help')->assertOk()->getContent();

        $this->assertStringContainsString('tab=general', $html);
        $this->assertStringContainsString('tab=workflow', $html, 'Admin must see the System workflow tab.');
        $this->assertStringContainsString('tab=admin', $html);
        $this->assertStringContainsString('tab=lpi', $html);
        $this->assertStringContainsString('tab=reviewer', $html);
    }

    public function test_lpi_cannot_open_the_admin_manual_via_url()
    {
        $lpi = $this->activeUser('LPI');
        if (!$lpi) {
            $this->markTestSkipped('No active LPI in test database.');
        }

        $html = $this->actingAs($lpi)->get('/about/help?tab=admin')->assertOk()->getContent();

        // The restricted tab falls back to the LPI manual.
        $this->assertStringContainsString('Project Registration', $html, 'LPI should land on his own manual.');
        $this->assertStringNotContainsString('File Downloads</h2>', $html, 'Admin manual content must not render for an LPI.');
        $this->assertStringNotContainsString('tab=admin"', $html, 'Admin tab must not appear in the nav.');
    }

    /** The System workflow tab is visible to guests too (public guide). */
    public function test_guest_sees_general_and_workflow_tabs()
    {
        $html = $this->get('/about/help')->assertOk()->getContent();

        $this->assertStringContainsString('tab=general', $html);
        $this->assertStringContainsString('tab=workflow', $html, 'Guests must see the System workflow tab.');
        $this->assertStringNotContainsString('tab=admin"', $html);
        $this->assertStringNotContainsString('tab=lpi"', $html);
        $this->assertStringNotContainsString('tab=reviewer"', $html);

        // The tab renders the illustrated workflow content.
        $workflow = $this->get('/about/help?tab=workflow')->assertOk()->getContent();
        $this->assertStringContainsString('End-to-End Flow', $workflow, 'The workflow illustration must render.');
        $this->assertStringContainsString('Consensus rules', $workflow, 'The workflow manual must cover the consensus rules.');
    }
}
