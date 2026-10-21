<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Workflow modal loading (projects page action menu → Accept/Reject Proposal etc.).
 *
 * The modal endpoint must return HTML for reviewers/admins so the AJAX modal
 * can render — a non-200 here surfaces in the UI as the "Failed to load action."
 * overlay error.
 *
 * Runs against the dedicated test database (phpunit.xml -> rtsnew_test) and
 * rolls back every test via DatabaseTransactions, so no real data is touched.
 */
class WorkflowModalTest extends TestCase
{
    use DatabaseTransactions;

    /** Reviewers can load the Accept/Reject Proposal (claim) modal for any project. */
    public function test_reviewer_can_load_accept_proposal_modal()
    {
        $reviewer = User::whereIn('type', ['Reviewer', 'LPI+Reviewer'])
            ->where('is_active', 1)
            ->first();
        $project = Project::first();

        if (!$reviewer || !$project) {
            $this->markTestSkipped('Missing reviewer or project in test database.');
        }

        $res = $this->actingAs($reviewer)
            ->get("/workflow/modal/accept-proposal/{$project->id}");

        $res->assertOk();
        $this->assertNotEmpty($res->json('html'), 'accept-proposal modal must return HTML');
        $this->assertStringContainsString('Review Proposal', $res->json('html'));
    }

    /** The other workflow modal actions also return HTML for an admin. */
    public function test_admin_can_load_workflow_modals()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        $project = Project::first();

        if (!$admin || !$project) {
            $this->markTestSkipped('Missing admin or project in test database.');
        }

        foreach (['assign', 'accept-proposal', 'report-card'] as $action) {
            $res = $this->actingAs($admin)
                ->get("/workflow/modal/{$action}/{$project->id}");

            $res->assertOk();
            $this->assertNotEmpty($res->json('html'), "modal '{$action}' must return HTML");
        }
    }
}
