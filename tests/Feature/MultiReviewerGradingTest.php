<?php

namespace Tests\Feature;

use App\Models\ProgressReportGrading;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Multi-reviewer grading: each reviewer (and the admin) keeps his own
 * independent grading set for the same project.
 *
 *  - Drafts are per user: a reviewer sees/edits only HIS own draft.
 *  - Assigning a second reviewer adds alongside (never replaces).
 *  - Each reviewer claims for himself before his grading form unlocks.
 *  - A submitted set is locked (publish != pending) while others stay free.
 *
 * Runs against the dedicated test database (phpunit.xml -> rtsnew_test) and
 * rolls back every test via DatabaseTransactions, so no real data is touched.
 */
class MultiReviewerGradingTest extends TestCase
{
    use DatabaseTransactions;

    /** Two distinct active reviewers (falls back to skip when unavailable). */
    private function twoReviewers(): array
    {
        $reviewers = User::where('type', 'Reviewer')
            ->where('is_active', 1)
            ->orderBy('id')
            ->take(2)
            ->get();

        return $reviewers->count() >= 2 ? $reviewers->all() : [];
    }

    /** A project sitting at the progress step (submitted, not yet reviewed/rejected) with an open deadline. */
    private function gradableProject(): ?Project
    {
        return Project::whereHas('submissions', function ($q) {
            $q->where('type', 'progress');
        })->whereHas('program', function ($q) {
            $q->whereNull('prog_rpt_deadline')->orWhere('prog_rpt_deadline', '<', now());
        })->whereDoesntHave('statusHistories', function ($q) {
            $q->whereIn('status', [
                Project::STATUS_PROGRESS_REVIEWED,
                Project::STATUS_PROGRESS_REJECTED,
            ]);
        })->first();
    }

    private function assignPivot(Project $project, int $userId): void
    {
        DB::table('projects_reviewers')->updateOrInsert(
            ['project_id' => $project->id, 'user_id' => $userId],
            ['role' => 'Reviewer', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    /** Claim (accept the proposal) for THIS reviewer — required before grading. */
    private function claim(Project $project, User $reviewer): void
    {
        $pivotId = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->value('id');

        $this->actingAs($reviewer)->postJson('/workflow/submit-decision', [
            'project_id' => $project->id,
            'r_id'       => $pivotId,
            'accept'     => 'accepted',
        ])->assertOk();
    }

    /** Reviewer B never sees Reviewer A's draft — each works on his own set. */
    public function test_drafts_are_isolated_per_reviewer()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$reviewerA) {
            $this->markTestSkipped('Need two active reviewers in test database.');
        }
        $project = $this->gradableProject();
        if (!$project) {
            $this->markTestSkipped('No gradable project (progress submitted, deadline open).');
        }

        $this->assignPivot($project, $reviewerA->id);
        $this->assignPivot($project, $reviewerB->id);
        // The grading form only unlocks after the reviewer claims for himself.
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        // A saves a draft with a distinctive marker.
        $res = $this->actingAs($reviewerA)->post("/grading/{$project->id}/save-progress-grade", [
            'save_action'        => 'draft',
            'report_type'        => 'progress',
            'publish'            => 'accepted',
            'achievementsRating' => 4,
            'publicationsRating' => 4,
            'studentsRating'     => 4,
            'budgetRating'       => 4,
            'achievementsComments' => 'ZZUNIQUE_ADRAFT_MARKER',
        ]);
        $this->assertContains($res->getStatusCode(), [200, 302], 'A draft save should succeed.');

        // The draft is stored under A and still pending (editable).
        $this->assertDatabaseHas('progress_report_grading', [
            'project_id' => $project->id,
            'user_id'    => $reviewerA->id,
            'achievementsComments' => 'ZZUNIQUE_ADRAFT_MARKER',
            'publish'    => 'pending',
        ]);

        // A reopens the grading page → HIS draft is prefilled.
        $pageA = $this->actingAs($reviewerA)->get("/projects/{$project->id}/grading");
        $pageA->assertOk();
        $this->assertStringContainsString('ZZUNIQUE_ADRAFT_MARKER', $pageA->getContent());

        // B opens the same project → blank form, A's draft invisible.
        $pageB = $this->actingAs($reviewerB)->get("/projects/{$project->id}/grading");
        $pageB->assertOk();
        $this->assertStringNotContainsString('ZZUNIQUE_ADRAFT_MARKER', $pageB->getContent(),
            'Reviewer B must start from scratch, never see Reviewer A\'s grades.');
    }

    /** Assigning a second reviewer adds alongside — the first stays assigned. */
    public function test_second_reviewer_is_added_without_replacing()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$admin || !$reviewerA || !$reviewerB) {
            $this->markTestSkipped('Need an active admin and two active reviewers.');
        }
        $project = Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects in test database.');
        }

        // Assign the first reviewer via the endpoint.
        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerA->id],
        ])->assertOk()->assertJson(['success' => true]);

        // Assign a SECOND reviewer — must add, not replace.
        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerB->id],
        ])->assertOk()->assertJson(['success' => true]);

        $assigned = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->pluck('user_id')
            ->all();

        $this->assertContains($reviewerA->id, $assigned, 'First reviewer must stay assigned.');
        $this->assertContains($reviewerB->id, $assigned, 'Second reviewer must be added.');
    }

    /** Each reviewer claims for himself — his claim never unlocks another. */
    public function test_claim_is_tracked_per_reviewer()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$reviewerA) {
            $this->markTestSkipped('Need two active reviewers in test database.');
        }
        // Pick a project whose LATEST status is a pre-claim state (or that has
        // no status yet) so recording "Claimed" on accept is deterministic.
        $project = Project::where(function ($q) {
            $q->whereRaw("EXISTS (SELECT 1 FROM status_histories sh WHERE sh.project_id = projects.id AND sh.status IN ('imported', 'registered', 'Assigned', 'proposal_rejected', 'reviewer_unassigned') AND sh.id = (SELECT MAX(sh2.id) FROM status_histories sh2 WHERE sh2.project_id = projects.id))")
                ->orWhereDoesntHave('statusHistories');
        })->whereDoesntHave('statusHistories', function ($q) {
            $q->where('status', Project::STATUS_GRADED);
        })->first();
        if (!$project) {
            $this->markTestSkipped('No project at a pre-claim stage in test database.');
        }

        $this->assignPivot($project, $reviewerA->id);
        $this->assignPivot($project, $reviewerB->id);

        $pivotA = DB::table('projects_reviewers')
            ->where('project_id', $project->id)->where('user_id', $reviewerA->id)->first();

        // Before claiming: A gets the claim action, not the grading action.
        $actionsBefore = $project->availableActions($reviewerA);
        $this->assertContains('claim', array_column($actionsBefore, 'action'));
        $this->assertNotContains('open-grading', array_column($actionsBefore, 'action'));

        // A accepts the proposal.
        $this->actingAs($reviewerA)->postJson('/workflow/submit-decision', [
            'project_id' => $project->id,
            'r_id'       => $pivotA->id,
            'accept'     => 'accepted',
        ])->assertOk();

        // A has claimed …
        $this->assertNotNull(
            DB::table('projects_reviewers')
                ->where('project_id', $project->id)->where('user_id', $reviewerA->id)
                ->value('claimed_at'),
            'Reviewer A\'s claim must be recorded on HIS assignment.'
        );
        $this->assertSame(
            Project::STATUS_CLAIMED,
            $project->fresh()->currentWorkflowStatus(),
            'Accepting records the project-level Claimed status (once).'
        );

        // … B has NOT: he still sees the claim action and no grading action.
        $this->assertNull(
            DB::table('projects_reviewers')
                ->where('project_id', $project->id)->where('user_id', $reviewerB->id)
                ->value('claimed_at')
        );
        $actionsB = $project->availableActions($reviewerB);
        $this->assertContains('claim', array_column($actionsB, 'action'),
            'Reviewer B must still claim for himself.');
        $this->assertNotContains('open-grading', array_column($actionsB, 'action'));

        // A's menu now offers grading.
        $actionsA = $project->availableActions($reviewerA);
        $this->assertContains('open-grading', array_column($actionsA, 'action'));
    }

    /** A submitted set is locked while the other reviewer's set stays free. */
    public function test_submitted_grades_lock_only_for_that_reviewer()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$reviewerA) {
            $this->markTestSkipped('Need two active reviewers in test database.');
        }
        $project = $this->gradableProject();
        if (!$project) {
            $this->markTestSkipped('No gradable project (progress submitted, deadline open).');
        }

        $this->assignPivot($project, $reviewerA->id);
        $this->assignPivot($project, $reviewerB->id);
        // The grading form only unlocks after the reviewer claims for himself.
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        // A submits (publish = accepted → read-only next time).
        $this->actingAs($reviewerA)->post("/grading/{$project->id}/save-progress-grade", [
            'save_action'        => 'submit',
            'report_type'        => 'progress',
            'publish'            => 'accepted',
            'achievementsRating' => 5,
            'publicationsRating' => 5,
            'studentsRating'     => 5,
            'budgetRating'       => 5,
            'achievementsComments' => 'ZZUNIQUE_ASUBMITTED_MARKER',
        ]);
        $this->assertDatabaseHas('progress_report_grading', [
            'project_id' => $project->id,
            'user_id'    => $reviewerA->id,
            'publish'    => 'accepted',
            'achievementsComments' => 'ZZUNIQUE_ASUBMITTED_MARKER',
        ]);

        // A's page now shows the submitted (read-only) set …
        $pageA = $this->actingAs($reviewerA)->get("/projects/{$project->id}/grading");
        $pageA->assertOk();
        $this->assertStringContainsString('ZZUNIQUE_ASUBMITTED_MARKER', $pageA->getContent());

        // … while B still gets a blank, editable form.
        $pageB = $this->actingAs($reviewerB)->get("/projects/{$project->id}/grading");
        $pageB->assertOk();
        $this->assertStringNotContainsString('ZZUNIQUE_ASUBMITTED_MARKER', $pageB->getContent());

        // No cross-contamination: only A's row exists for this round.
        $rows = ProgressReportGrading::where('project_id', $project->id)
            ->where('report_type', 'progress')
            ->whereIn('user_id', [$reviewerA->id, $reviewerB->id])
            ->get();
        $this->assertCount(1, $rows);
        $this->assertSame($reviewerA->id, $rows->first()->user_id);
    }
}
