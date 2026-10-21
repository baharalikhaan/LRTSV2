<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Consensus grading: the project-level workflow status flips only once EVERY
 * assigned reviewer has submitted his own set for the stage.
 *
 *  - First submit does not complete the stage (others may still grade).
 *  - Any rejected set wins when the consensus completes.
 *  - After one reviewer submits, the OTHER reviewer still gets his own
 *    editable form (per-user gating instead of status gating).
 *  - The Evaluate Projects queue is the admin's personal queue: a row
 *    disappears only once HE has submitted his own set.
 *  - Final stage follows the same rules (Graded only after everyone).
 *
 * Runs against the dedicated test database (phpunit.xml -> rtsnew_test) and
 * rolls back every test via DatabaseTransactions, so no real data is touched.
 */
class ConsensusGradingTest extends TestCase
{
    use DatabaseTransactions;

    private function twoReviewers(): array
    {
        $reviewers = User::where('type', 'Reviewer')
            ->where('is_active', 1)
            ->orderBy('id')
            ->take(2)
            ->get();

        return $reviewers->count() >= 2 ? $reviewers->all() : [];
    }

    /**
     * Progress stage: submitted report, open deadline, and in a round where
     * the progress stage can still complete (no completed/advanced status on
     * record — the consensus freezes those).
     *
     * $withoutPublishedBy excludes projects where that user already has a
     * published progress set (needed for the admin's personal queue test).
     */
    private function progressProject(?int $withoutPublishedBy = null): ?Project
    {
        $query = Project::whereHas('submissions', function ($q) {
            $q->where('type', 'progress');
        })->whereHas('program', function ($q) {
            $q->whereNull('prog_rpt_deadline')->orWhere('prog_rpt_deadline', '<', now());
        })->whereDoesntHave('statusHistories', function ($q) {
            $q->whereIn('status', [
                Project::STATUS_PROGRESS_REVIEWED,
                Project::STATUS_PROGRESS_REJECTED,
                Project::STATUS_PROGRESS2_ADDED,
                Project::STATUS_PROGRESS2_REVIEWED,
                Project::STATUS_PROGRESS2_REJECTED,
                Project::STATUS_FINAL_ADDED,
                Project::STATUS_FINAL_REJECTED,
                Project::STATUS_GRADED,
            ]);
        });

        if ($withoutPublishedBy !== null) {
            $query->whereDoesntHave('progressGradings', function ($q) use ($withoutPublishedBy) {
                $q->where('user_id', $withoutPublishedBy)
                    ->where('publish', '!=', 'pending')
                    ->where(function ($w) {
                        $w->where('report_type', 'progress')->orWhereNull('report_type');
                    });
            });
            // The queue test asserts the row LEAVES after the progress submit,
            // so pick a project that does not owe other stages (final / PR2).
            $query->whereDoesntHave('submissions', function ($q) {
                $q->whereIn('type', ['final', 'progress2']);
            });
        }

        return $query->first();
    }

    /** Final stage: submitted final report, open deadline, not yet completed. */
    private function finalProject(): ?Project
    {
        return Project::whereHas('submissions', function ($q) {
            $q->where('type', 'final');
        })->whereHas('program', function ($q) {
            $q->whereNull('final_rpt_deadline')->orWhere('final_rpt_deadline', '<', now());
        })->whereDoesntHave('statusHistories', function ($q) {
            $q->whereIn('status', [
                Project::STATUS_GRADED,
                Project::STATUS_FINAL_REJECTED,
            ]);
        })->first();
    }

    /**
     * Deterministic reviewer team: wipes legacy assignments so the consensus
     * count only includes the reviewers this test submits with.
     */
    private function syncReviewers(Project $project, array $userIds): void
    {
        DB::table('projects_reviewers')->where('project_id', $project->id)->delete();
        foreach ($userIds as $userId) {
            DB::table('projects_reviewers')->insert([
                'project_id' => $project->id,
                'user_id'    => $userId,
                'role'       => 'Reviewer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
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

    private function progressPayload(string $decision): array
    {
        return [
            'save_action'        => 'submit',
            'report_type'        => 'progress',
            'publish'            => $decision,
            'achievementsRating' => 4,
            'publicationsRating' => 4,
            'studentsRating'     => 4,
            'budgetRating'       => 4,
            // required when the decision is a rejection
            'rejection_type'     => 'report',
            'rejection_reason'   => 'Consensus test rejection reason.',
        ];
    }

    private function finalPayload(string $decision): array
    {
        return [
            'save_action' => 'submit',
            'publish'     => $decision,
            'gradeA'      => 4,
            'gradeB'      => 4,
            'gradeC'      => 4,
            'gradeD'      => 4,
        ];
    }

    /** The stage completes only when the SECOND reviewer submits. */
    public function test_progress_stage_waits_for_every_reviewer()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$reviewerA) {
            $this->markTestSkipped('Need two active reviewers in test database.');
        }
        $project = $this->progressProject();
        if (!$project) {
            $this->markTestSkipped('No project at the progress step.');
        }

        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);
        // The grading form only unlocks after each reviewer claims for himself.
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        // A submits accepted — stage must stay open for B.
        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertNotSame(
            Project::STATUS_PROGRESS_REVIEWED,
            $project->fresh()->currentWorkflowStatus(),
            'Stage must not complete while another reviewer has not submitted.'
        );

        // B submits accepted — consensus complete, status flips once.
        $this->actingAs($reviewerB)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertSame(
            Project::STATUS_PROGRESS_REVIEWED,
            $project->fresh()->currentWorkflowStatus(),
            'Stage completes when every assigned reviewer submitted.'
        );
    }

    /** Any rejected set wins when the consensus completes. */
    public function test_rejection_wins_on_consensus_completion()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$reviewerA) {
            $this->markTestSkipped('Need two active reviewers in test database.');
        }
        $project = $this->progressProject();
        if (!$project) {
            $this->markTestSkipped('No project at the progress step.');
        }

        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);
        // The grading form only unlocks after each reviewer claims for himself.
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        // A rejects — stage stays open (no status flip yet).
        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('rejected'));
        $this->assertNotSame(
            Project::STATUS_PROGRESS_REJECTED,
            $project->fresh()->currentWorkflowStatus(),
            'An early rejection must not flip the status before everyone submitted.'
        );

        // B accepts — consensus complete, but the rejection wins.
        $this->actingAs($reviewerB)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertSame(
            Project::STATUS_PROGRESS_REJECTED,
            $project->fresh()->currentWorkflowStatus(),
            'A rejected set wins when the consensus completes.'
        );
    }

    /** The second reviewer still gets his own editable form after A submits. */
    public function test_form_is_gated_per_user_not_by_status()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$reviewerA) {
            $this->markTestSkipped('Need two active reviewers in test database.');
        }
        $project = $this->progressProject();
        if (!$project) {
            $this->markTestSkipped('No project at the progress step.');
        }

        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);
        // The grading form only unlocks after each reviewer claims for himself.
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));

        // B still gets a blank, editable form.
        $pageB = $this->actingAs($reviewerB)->get("/projects/{$project->id}/grading");
        $pageB->assertOk();
        $this->assertStringContainsString(
            'name="achievementsComments"',
            $pageB->getContent(),
            'Reviewer B must still see his own editable grading form after A submitted.'
        );

        // A submitted → his own form closes (read-only panel instead).
        $pageA = $this->actingAs($reviewerA)->get("/projects/{$project->id}/grading");
        $pageA->assertOk();
        $this->assertStringNotContainsString(
            'name="achievementsComments"',
            $pageA->getContent(),
            'Reviewer A\'s submitted set must be read-only.'
        );
    }

    /** Final stage: Graded only after every reviewer submitted. */
    public function test_final_stage_waits_for_every_reviewer()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$reviewerA) {
            $this->markTestSkipped('Need two active reviewers in test database.');
        }
        $project = $this->finalProject();
        if (!$project) {
            $this->markTestSkipped('No project with a submitted final report and open deadline.');
        }

        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);
        // The grading form only unlocks after each reviewer claims for himself.
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        $this->actingAs($reviewerA)
            ->postJson("/grading/{$project->id}/save-final-grade", $this->finalPayload('accepted'))
            ->assertOk();
        $this->assertNotSame(
            Project::STATUS_GRADED,
            $project->fresh()->currentWorkflowStatus(),
            'Graded must not be recorded while another reviewer has not submitted.'
        );

        $this->actingAs($reviewerB)
            ->postJson("/grading/{$project->id}/save-final-grade", $this->finalPayload('accepted'))
            ->assertOk();
        $this->assertSame(
            Project::STATUS_GRADED,
            $project->fresh()->currentWorkflowStatus(),
            'Graded is recorded once every assigned reviewer submitted.'
        );

        // F-02: after Graded the menu offers View Grades only.
        $actionsB = $project->availableActions($reviewerB);
        $this->assertContains('view-grade', array_column($actionsB, 'action'));
        $this->assertNotContains(
            'open-grading',
            array_column($actionsB, 'action'),
            'After Graded the menu must offer View Grades only.'
        );
    }

    /** Evaluate Projects = admin's personal queue: leaves only for HIM. */
    public function test_evaluate_queue_is_admin_personal()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        [$reviewerA] = $this->twoReviewers();
        if (!$admin || !$reviewerA) {
            $this->markTestSkipped('Need an active admin and reviewer.');
        }
        $project = $this->progressProject((int) $admin->id);
        if (!$project) {
            $this->markTestSkipped('No project at the progress step.');
        }

        $this->syncReviewers($project, [$reviewerA->id]);
        $this->claim($project, $reviewerA); // grading requires his own claim
        $queueUrl = route('projects.pending-reviews');
        $marker = $project->title;

        // Reviewer A submits — the row must STILL be in the admin's queue
        // (it only disappears for whoever submits).
        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertStringContainsString(
            $marker,
            $this->actingAs($admin)->get($queueUrl)->getContent(),
            'The queue keeps the row while the admin himself has not submitted.'
        );

        // Admin submits his own set — now it leaves HIS queue.
        $this->actingAs($admin)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertStringNotContainsString(
            $marker,
            $this->actingAs($admin)->get($queueUrl)->getContent(),
            'The row must disappear once the admin submitted his own set.'
        );
    }
}
