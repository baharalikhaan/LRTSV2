<?php

namespace Tests\Feature;

use App\Models\FinalReportGrading;
use App\Models\ProgressReportGrading;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Reviewer anonymity towards the LPI.
 *
 * Reviewers are internal to the research office: the LPI may see the outcome of
 * a review (and its remarks) but never who the reviewer is, nor any of the
 * admin/reviewer-only screens that list reviewers by name or e-mail.
 *
 * Runs against rtsnew_test and rolls back every test.
 */
class LpiPrivacyTest extends TestCase
{
    use DatabaseTransactions;

    private function lpi(): ?User
    {
        return User::where('type', 'LPI')->where('is_active', 1)
            ->whereHas('projects')
            ->orderBy('id')
            ->first();
    }

    private function admin(): ?User
    {
        return User::where('type', 'Admin')->where('is_active', 1)->orderBy('id')->first();
    }

    /** An LPI project that already carries a published grading. */
    private function gradedLpiProject(): ?array
    {
        $grading = ProgressReportGrading::with('user')
            ->where('publish', '!=', 'pending')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->first()
            ?: FinalReportGrading::with('user')
                ->where('publish', '!=', 'pending')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->first();

        if (!$grading || !$grading->user) {
            return null;
        }

        $project = Project::find($grading->project_id);
        $lpi = $project ? User::find($project->lpi_id) : null;

        return ($project && $lpi) ? [$project, $lpi, $grading->user] : null;
    }

    public function test_lpi_cannot_open_the_reviewer_assignment_page()
    {
        $lpi = $this->lpi();
        if (!$lpi) {
            $this->markTestSkipped('No active LPI.');
        }

        $this->actingAs($lpi)
            ->get('/projects/reviewer-assignment')
            ->assertForbidden();
    }

    public function test_lpi_cannot_fetch_the_assignment_or_rejection_modals()
    {
        $lpi = $this->lpi();
        $project = Project::where('lpi_id', $lpi->id ?? 0)->first();
        if (!$lpi || !$project) {
            $this->markTestSkipped('No LPI project.');
        }

        foreach (['assign', 'review-rejection', 'progress', 'review', 'accept-proposal'] as $action) {
            $this->actingAs($lpi)
                ->get("/workflow/modal/{$action}/{$project->id}")
                ->assertForbidden();
        }
    }

    public function test_project_sheet_hides_the_reviewer_identity_from_the_lpi()
    {
        $data = $this->gradedLpiProject();
        if (!$data) {
            $this->markTestSkipped('No published grading on an LPI project.');
        }

        [$project, $lpi, $reviewer] = $data;

        $this->assertNotSame(
            $reviewer->id,
            $lpi->id,
            'The grading must belong to a reviewer other than the LPI.'
        );

        $html = $this->actingAs($lpi)
            ->get('/projects/' . $project->id)
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString($reviewer->name, $html, 'The LPI must not see the reviewer name.');
        $this->assertStringNotContainsString($reviewer->email, $html, 'The LPI must not see the reviewer e-mail.');
        $this->assertStringContainsString('Reviewer', $html, 'Grading is still shown, under a generic label.');
    }

    public function test_admin_still_sees_the_reviewer_name()
    {
        $data = $this->gradedLpiProject();
        $admin = $this->admin();
        if (!$data || !$admin) {
            $this->markTestSkipped('No published grading or no admin.');
        }

        [$project, , $reviewer] = $data;

        $html = $this->actingAs($admin)
            ->get('/projects/' . $project->id)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($reviewer->name, $html, 'The admin keeps the reviewer name.');
    }

    public function test_lpi_cannot_open_another_lpis_project()
    {
        $lpi = $this->lpi();
        $foreign = Project::where('lpi_id', '!=', $lpi->id ?? 0)->whereNotNull('lpi_id')->first();
        if (!$lpi || !$foreign) {
            $this->markTestSkipped('No LPI or no foreign project.');
        }

        $this->actingAs($lpi)->get('/projects/' . $foreign->id)->assertForbidden();
        $this->actingAs($lpi)->get('/projects/' . $foreign->id . '/printable')->assertForbidden();
        $this->actingAs($lpi)->get('/projects/' . $foreign->id . '/report-card')->assertForbidden();
    }

    public function test_lpi_may_open_its_own_report_card()
    {
        $lpi = $this->lpi();
        $project = Project::where('lpi_id', $lpi->id ?? 0)->first();
        if (!$lpi || !$project) {
            $this->markTestSkipped('No LPI project.');
        }

        $this->actingAs($lpi)
            ->get('/projects/' . $project->id . '/report-card')
            ->assertOk();
    }
}
