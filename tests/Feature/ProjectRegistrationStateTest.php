<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * "Already registered" detection.
 *
 * Projects can reach the later stages without a literal 'registered' row
 * (registered on behalf of an LPI by an administrator, or imported with the LPI
 * already attached). The menus used to look only for that row, so a project
 * sitting at progress_rejected was offered "Register" again instead of its real
 * next step (e.g. Review Progress Rejection).
 *
 * Runs against rtsnew_test and rolls back every test.
 */
class ProjectRegistrationStateTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'like', '%Admin%')->where('is_active', 1)->orderBy('id')->first();
    }

    private function lpi(): ?User
    {
        return User::where('type', 'like', '%LPI%')->where('is_active', 1)->orderBy('id')->first();
    }

    private function projectWithFiles(): ?Project
    {
        return Project::whereNotNull('lpi_id')->whereHas('submissions')->orderBy('id')->first();
    }

    /** Wipe the workflow history and leave a single status row. */
    private function resetStatus(Project $project, string $status, array $metadata = []): void
    {
        DB::table('status_histories')->where('project_id', $project->id)->delete();
        $project->recordStatus($status, $metadata, null);
        $project->refresh();
    }

    public function test_later_stage_without_registered_row_counts_as_registered()
    {
        $project = $this->projectWithFiles();
        if (!$project) {
            $this->markTestSkipped('No project with an LPI and files.');
        }

        $this->resetStatus($project, Project::STATUS_PROGRESS_REJECTED, [
            'rejection_type'   => 'report',
            'rejection_reason' => 'Missing evidence.',
        ]);

        $this->assertTrue(
            $project->isRegisteredWorkflow(),
            'A project past registration must count as registered even without a literal "registered" row.'
        );
    }

    public function test_admin_menu_offers_the_next_step_not_register()
    {
        $project = $this->projectWithFiles();
        $admin   = $this->admin();
        if (!$project || !$admin) {
            $this->markTestSkipped('No project with files or no active admin.');
        }

        $this->resetStatus($project, Project::STATUS_PROGRESS_REJECTED, [
            'rejection_type'   => 'report',
            'rejection_reason' => 'Missing evidence.',
        ]);

        $actions = array_column($project->availableActions($admin), 'action');

        $this->assertNotContains('register', $actions, 'An admin must never be offered Register for a project in the workflow.');
        $this->assertContains(
            'review-progress-rejection',
            $actions,
            'A progress_rejected project must offer the admin the Review Progress Rejection (forward to LPI) action.'
        );
    }

    public function test_projects_page_has_no_register_link_for_a_project_in_the_workflow()
    {
        $project = $this->projectWithFiles();
        $admin   = $this->admin();
        if (!$project || !$admin) {
            $this->markTestSkipped('No project with files or no active admin.');
        }

        $this->resetStatus($project, Project::STATUS_PROGRESS_REJECTED, [
            'rejection_type'   => 'report',
            'rejection_reason' => 'Missing evidence.',
        ]);

        $html = $this->actingAs($admin)->get('/projects')->assertOk()->getContent();

        $this->assertStringNotContainsString(
            '/projects/' . $project->id . '/register-wizard',
            $html,
            'The project menu must not offer Register for a project that already entered the workflow.'
        );
        $this->assertStringContainsString(
            'openWorkflowModal(' . $project->id . ", 'review-rejection'",
            $html,
            'The project menu must offer the rejection review (forward to LPI) instead.'
        );
    }

    public function test_imported_project_without_workflow_status_still_offers_register()
    {
        $project = $this->projectWithFiles();
        $lpi     = $this->lpi();
        if (!$project || !$lpi) {
            $this->markTestSkipped('No project or no active LPI.');
        }

        DB::table('status_histories')->where('project_id', $project->id)->delete();
        $project->recordStatus(Project::STATUS_IMPORTED, [], null);
        $project->refresh();

        $this->assertFalse(
            $project->isRegisteredWorkflow(),
            'An imported-only project is not registered yet.'
        );

        $actions = array_column($project->availableActions($lpi), 'action');
        $this->assertContains('register', $actions, 'Imported projects must still offer Register.');
    }
}
