<?php

namespace Tests\Feature;

use App\Http\Controllers\ProgressController;
use App\Models\EmailSendLog;
use App\Models\FinalReportGrading;
use App\Models\ProgressReportGrading;
use App\Models\Project;
use App\Models\ReviewerRejection;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Executable scenarios from docs/TEST-SCENARIOS-GRADING.md (groups A, B, D,
 * E, G — the lifecycle and consensus scenarios live in MultiReviewerGradingTest
 * and ConsensusGradingTest, referenced from the same document).
 *
 * Runs real HTTP requests through the full application kernel against the
 * dedicated rtsnew_test database and rolls back after every test.
 */
class GradingWorkflowScenariosTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    private function twoReviewers(): array
    {
        $reviewers = User::where('type', 'Reviewer')
            ->where('is_active', 1)
            ->orderBy('id')
            ->take(2)
            ->get();

        return $reviewers->count() >= 2 ? $reviewers->all() : [];
    }

    private function threeReviewers(): array
    {
        $reviewers = User::where('type', 'Reviewer')
            ->where('is_active', 1)
            ->orderBy('id')
            ->take(3)
            ->get();

        return $reviewers->count() >= 3 ? $reviewers->all() : [];
    }

    private function anyProject(): ?Project
    {
        return Project::first();
    }

    private function setProgramDeadline(Project $project, string $field, $when): void
    {
        DB::table('programs')->where('id', $project->program_id)->update([$field => $when]);
    }

    /**
     * Deterministic reviewer team: wipes legacy assignments first.
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

    private function pivotId(Project $project, int $userId): int
    {
        return (int) DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $userId)
            ->value('id');
    }

    /** Claim (accept the proposal) for THIS reviewer — required before grading. */
    private function claim(Project $project, User $reviewer): void
    {
        $this->actingAs($reviewer)->postJson('/workflow/submit-decision', [
            'project_id' => $project->id,
            'r_id'       => $this->pivotId($project, $reviewer->id),
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
            'rejection_type'     => 'report',
            'rejection_reason'   => 'Scenario test reason.',
        ];
    }

    private function finalPayload(string $decision): array
    {
        $payload = [
            'save_action' => 'submit',
            'publish'     => $decision,
            'gradeA'      => 4,
            'gradeB'      => 4,
            'gradeC'      => 4,
            'gradeD'      => 4,
            'scoreA'      => 5,
            'scoreB'      => 5,
            'scoreC'      => 5,
        ];

        if ($decision === 'rejected') {
            $payload['rejection_type']   = 'report';
            $payload['rejection_reason'] = 'Final report scenario reason.';
        }

        return $payload;
    }

    /** Final stage: final report submitted, deadline passed, round still open. */
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
                Project::STATUS_FINAL_REJ_REVIEWED,
            ]);
        })->first();
    }

    /** Progress stage: submitted report, open deadline, round still open for progress. */
    private function progressProject(): ?Project
    {
        return Project::whereHas('submissions', function ($q) {
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
        })->first();
    }

    // ─── Group A: reviewer assignment ────────────────────────────────────────

    /** A-01 First assignment creates an unclaimed assignment + Assigned status. */
    public function test_a01_first_assignment_is_unclaimed_and_sets_assigned_status()
    {
        $admin = $this->activeUser('Admin');
        [$reviewerA] = $this->twoReviewers();
        $project = $this->anyProject();
        if (!$admin || !$reviewerA || !$project) {
            $this->markTestSkipped('Missing admin, reviewer or project.');
        }
        $this->syncReviewers($project, []);

        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerA->id],
        ])->assertOk()->assertJson(['success' => true]);

        $pivot = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $reviewerA->id)
            ->first();
        $this->assertNotNull($pivot, 'Assignment row must exist.');
        $this->assertNull($pivot->claimed_at, 'A fresh assignment must start unclaimed.');
        $this->assertSame(
            Project::STATUS_ASSIGNED,
            $project->fresh()->currentWorkflowStatus(),
            'First assignment records the Assigned status.'
        );
    }

    /** A-02 A second reviewer is added alongside — status and first claim survive. */
    public function test_a02_second_reviewer_is_added_without_rolling_back_status()
    {
        $admin = $this->activeUser('Admin');
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        // Latest status must be a pre-claim state (or none) so recording
        // "Claimed" on A's accept is deterministic under the pre-claim rule.
        $project = Project::where(function ($q) {
            $q->whereRaw("EXISTS (SELECT 1 FROM status_histories sh WHERE sh.project_id = projects.id AND sh.status IN ('imported', 'registered', 'Assigned', 'proposal_rejected', 'reviewer_unassigned') AND sh.id = (SELECT MAX(sh2.id) FROM status_histories sh2 WHERE sh2.project_id = projects.id))")
                ->orWhereDoesntHave('statusHistories');
        })->whereDoesntHave('statusHistories', function ($q) {
            $q->where('status', Project::STATUS_GRADED);
        })->first();
        if (!$admin || !$reviewerA || !$reviewerB || !$project) {
            $this->markTestSkipped('Missing admin, two reviewers or a suitable project.');
        }

        $this->syncReviewers($project, [$reviewerA->id]);
        // A claims → status Claimed, A's row stamped.
        $this->actingAs($reviewerA)->postJson('/workflow/submit-decision', [
            'project_id' => $project->id,
            'r_id'       => $this->pivotId($project, $reviewerA->id),
            'accept'     => 'accepted',
        ])->assertOk();

        // Admin adds reviewer B.
        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerB->id],
        ])->assertOk()->assertJson(['success' => true]);

        $rows = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->get();
        $this->assertCount(2, $rows, 'Both reviewers must stay assigned.');

        $claimedA = $rows->firstWhere('user_id', $reviewerA->id)->claimed_at ?? null;
        $claimedB = $rows->firstWhere('user_id', $reviewerB->id)->claimed_at ?? null;
        $this->assertNotNull($claimedA, 'A\'s claim must be preserved.');
        $this->assertNull($claimedB, 'B must still claim for himself.');
        $this->assertSame(
            Project::STATUS_CLAIMED,
            $project->fresh()->currentWorkflowStatus(),
            'Adding a reviewer must not roll the status back from Claimed.'
        );
    }

    /** A-03 Re-assigning the same reviewer is idempotent. */
    public function test_a03_reassigning_the_same_reviewer_is_idempotent()
    {
        $admin = $this->activeUser('Admin');
        [$reviewerA] = $this->twoReviewers();
        $project = $this->anyProject();
        if (!$admin || !$reviewerA || !$project) {
            $this->markTestSkipped('Missing admin, reviewer or project.');
        }
        $this->syncReviewers($project, []);

        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerA->id],
        ])->assertOk()->assertJson(['success' => true]);

        $res = $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerA->id],
        ])->assertOk();

        $this->assertStringContainsString('already assigned', $res->json('message'));
        $this->assertSame(
            1,
            DB::table('projects_reviewers')->where('project_id', $project->id)->count(),
            'Re-assigning must not create a duplicate row.'
        );
    }

    /** A-04 A reviewer who previously rejected this project cannot be re-assigned. */
    public function test_a04_reviewer_who_rejected_cannot_be_reassigned()
    {
        $admin = $this->activeUser('Admin');
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        $project = $this->anyProject();
        if (!$admin || !$reviewerA || !$reviewerB || !$project) {
            $this->markTestSkipped('Missing admin, two reviewers or a project.');
        }
        $this->syncReviewers($project, [$reviewerA->id]);

        ReviewerRejection::create([
            'project_id' => $project->id,
            'user_id'    => $reviewerB->id,
            'reason'     => 'Scenario: previously rejected.',
        ]);

        $res = $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerB->id],
        ]);
        $res->assertStatus(422);
        $this->assertStringContainsString('previously rejected', $res->json('error'));

        $this->assertNull(
            DB::table('projects_reviewers')
                ->where('project_id', $project->id)
                ->where('user_id', $reviewerB->id)
                ->first(),
            'A previously rejecting reviewer must not be re-assigned.'
        );
    }

    /** A-05 Only admins can assign reviewers. */
    public function test_a05_only_admins_can_assign_reviewers()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        $project = $this->anyProject();
        if (!$reviewerA || !$reviewerB || !$project) {
            $this->markTestSkipped('Missing two reviewers or a project.');
        }
        $this->syncReviewers($project, []);

        $this->actingAs($reviewerA)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerB->id],
        ])->assertStatus(403);

        $this->assertSame(
            0,
            DB::table('projects_reviewers')->where('project_id', $project->id)->count(),
            'A non-admin assignment attempt must change nothing.'
        );
    }

    // ─── Group B: claiming ───────────────────────────────────────────────────

    /** B-03 Rejecting removes ONLY my assignment — the other reviewer stays. */
    public function test_b03_rejecting_removes_only_that_reviewer()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        $project = $this->anyProject();
        if (!$reviewerA || !$reviewerB || !$project) {
            $this->markTestSkipped('Missing two reviewers or a project.');
        }
        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);

        $this->actingAs($reviewerA)->postJson('/workflow/submit-decision', [
            'project_id'  => $project->id,
            'r_id'        => $this->pivotId($project, $reviewerA->id),
            'accept'      => 'rejected',
            'reject_reason' => 'Scenario: not my area.',
        ])->assertOk();

        $this->assertSame(
            0,
            DB::table('projects_reviewers')
                ->where('project_id', $project->id)
                ->where('user_id', $reviewerA->id)
                ->count(),
            'The rejecting reviewer\'s own assignment must be removed.'
        );
        $this->assertSame(
            1,
            DB::table('projects_reviewers')
                ->where('project_id', $project->id)
                ->where('user_id', $reviewerB->id)
                ->count(),
            'The other reviewer must stay assigned.'
        );
        $this->assertSame(
            Project::STATUS_PROPOSAL_REJECTED,
            $project->fresh()->currentWorkflowStatus(),
            'The project goes back to the admin queue.'
        );
        $this->assertTrue(
            ReviewerRejection::where('project_id', $project->id)
                ->where('user_id', $reviewerA->id)
                ->exists(),
            'The rejection must be recorded so A can never be re-assigned.'
        );
    }

    /** B-04 Another reviewer's assignment id cannot be used for my decision. */
    public function test_b04_reviewer_cannot_use_another_reviewers_pivot_id()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        $project = $this->anyProject();
        if (!$reviewerA || !$reviewerB || !$project) {
            $this->markTestSkipped('Missing two reviewers or a project.');
        }
        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);

        $this->actingAs($reviewerA)->postJson('/workflow/submit-decision', [
            'project_id' => $project->id,
            'r_id'       => $this->pivotId($project, $reviewerB->id), // B's row!
            'accept'     => 'accepted',
        ])->assertStatus(403);

        $claimedB = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $reviewerB->id)
            ->value('claimed_at');
        $this->assertNull($claimedB, 'B\'s assignment must not be stamped by A.');
    }

    // ─── Group D: consensus & notification ───────────────────────────────────

    /** D-04 A standalone admin grade (no reviewers) completes immediately. */
    public function test_d04_standalone_admin_grade_completes_immediately()
    {
        $admin = $this->activeUser('Admin');
        $project = $this->progressProject();
        if (!$admin || !$project) {
            $this->markTestSkipped('Missing admin or progress-stage project.');
        }
        $this->syncReviewers($project, []); // no reviewers → standalone

        $this->actingAs($admin)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));

        $this->assertSame(
            Project::STATUS_PROGRESS_REVIEWED,
            $project->fresh()->currentWorkflowStatus(),
            'With no assigned reviewers the admin\'s grade completes the stage immediately.'
        );
    }

    /** D-05 The LPI "graded" e-mail fires exactly once — at completion only. */
    public function test_d05_lpi_email_sent_once_at_completion()
    {
        $admin = $this->activeUser('Admin');
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        if (!$admin || !$reviewerA || !$reviewerB) {
            $this->markTestSkipped('Missing admin and two reviewers.');
        }

        $project = Project::whereHas('submissions', function ($q) {
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
        })->whereNotNull('lpi_id')->get()->first(function ($p) {
            return $p->lpi && $p->lpi->email;
        });
        if (!$project) {
            $this->markTestSkipped('No progress-stage project with an LPI e-mail.');
        }

        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);
        // Claim both BEFORE enabling event mail — the proposal_accepted mails
        // would otherwise pollute the count below.
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        config(['mail.enabled' => true]); // event mail active for this scenario
        $to = $project->lpi->email;
        $base = EmailSendLog::where('recipient_email', $to)->count();

        // Incomplete consensus → no "graded" mail.
        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertSame(
            $base,
            EmailSendLog::where('recipient_email', $to)->count(),
            'No graded mail before the consensus completes.'
        );

        // Completion → exactly one mail.
        $this->actingAs($reviewerB)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertSame(
            $base + 1,
            EmailSendLog::where('recipient_email', $to)->count(),
            'Exactly one graded mail at completion (never one per reviewer).'
        );
    }

    // ─── Group E: the admin's personal queue ─────────────────────────────────

    /** E-04 All stages the admin still owes appear in one combined label. */
    public function test_e04_queue_shows_all_owed_stages_in_one_label()
    {
        $admin = $this->activeUser('Admin');
        [$reviewerA] = $this->twoReviewers();
        if (!$admin || !$reviewerA) {
            $this->markTestSkipped('Missing admin and reviewer.');
        }

        $adminId = (int) $admin->id;
        $project = Project::whereHas('submissions', function ($q) {
            $q->where('type', 'progress');
        })->whereHas('submissions', function ($q) {
            $q->where('type', 'final');
        })->whereDoesntHave('progressGradings', function ($q) use ($adminId) {
            $q->where('user_id', $adminId)
                ->where('publish', '!=', 'pending')
                ->where(function ($w) {
                    $w->where('report_type', 'progress')->orWhereNull('report_type');
                });
        })->whereDoesntHave('finalGradings', function ($q) use ($adminId) {
            $q->where('user_id', $adminId)
                ->where('publish', '!=', 'pending');
        })->first();
        if (!$project) {
            $this->markTestSkipped('No project owing both progress and final to the admin.');
        }

        $this->syncReviewers($project, [$reviewerA->id]);

        $html = $this->actingAs($admin)
            ->get(route('projects.pending-reviews'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            'Progress Report 1 · Final Report',
            $html,
            'The queue must show every owed stage in one label.'
        );
    }

    // ─── Group G: rejection & resubmission (v2) ──────────────────────────────

    /** G-01 A v2 resubmission re-opens EVERY reviewer's set (archived first). */
    public function test_g01_resubmission_resets_every_reviewers_set()
    {
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        $project = $this->progressProject();
        if (!$reviewerA || !$reviewerB || !$project) {
            $this->markTestSkipped('Missing reviewers or progress-stage project.');
        }

        ProgressReportGrading::create([
            'project_id'          => $project->id,
            'user_id'             => $reviewerA->id,
            'report_type'         => 'progress',
            'achievementsRating'  => 4,
            'publicationsRating'  => 4,
            'studentsRating'      => 4,
            'budgetRating'        => 4,
            'comments'            => 'A accepted the first version.',
            'publish'             => 'accepted',
            'isAccepted'          => 1,
        ]);
        ProgressReportGrading::create([
            'project_id'          => $project->id,
            'user_id'             => $reviewerB->id,
            'report_type'         => 'progress',
            'achievementsRating'  => 2,
            'publicationsRating'  => 2,
            'studentsRating'      => 2,
            'budgetRating'        => 2,
            'comments'            => 'B rejected the first version.',
            'publish'             => 'rejected',
            'isAccepted'          => 0,
            'rejection_type'      => 'report',
            'rejection_reason'    => 'Needs correction.',
        ]);

        $this->invokeArchiveAndReset($project->id, 'progress');

        $rowA = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewerA->id)->firstOrFail();
        $rowB = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewerB->id)->firstOrFail();

        $this->assertSame('pending', $rowA->publish, 'A\'s set must re-open as draft.');
        $this->assertSame('pending', $rowB->publish, 'B\'s set must re-open as draft.');
        $this->assertNull($rowB->rejection_type, 'Rejection type cleared on reset.');
        $this->assertNull($rowB->rejection_reason, 'Rejection reason cleared on reset.');
        $this->assertNotNull($rowA->previous_grading, 'A\'s scores archived to previous_grading.');
        $this->assertSame('accepted', $rowA->previous_grading['publish'] ?? null);
    }

    /** G-02 An ethical/other rejection is never reset by a v2 upload. */
    public function test_g02_ethical_rejection_is_not_reset_on_resubmission()
    {
        [$reviewerA] = $this->twoReviewers();
        $admin = $this->activeUser('Admin');
        $project = $this->progressProject();
        if (!$reviewerA || !$admin || !$project) {
            $this->markTestSkipped('Missing reviewer, admin or progress-stage project.');
        }

        // The rejection flow expects an ethical/other typed rejection on record.
        $project->recordStatus(Project::STATUS_PROGRESS_REJECTED, [
            'rejection_type' => 'ethical',
            'comment'        => 'Missing valid approval.',
        ], $admin->id);

        $row = ProgressReportGrading::create([
            'project_id'         => $project->id,
            'user_id'            => $reviewerA->id,
            'report_type'        => 'progress',
            'achievementsRating' => 3,
            'publicationsRating' => 3,
            'studentsRating'     => 3,
            'budgetRating'       => 3,
            'comments'           => 'Rejected for ethical reasons.',
            'publish'            => 'rejected',
            'isAccepted'         => 0,
            'rejection_type'     => 'ethical',
            'rejection_reason'   => 'Approval missing.',
        ]);

        $this->invokeArchiveAndReset($project->id, 'progress');

        $row->refresh();
        $this->assertSame(
            'rejected',
            $row->publish,
            'Ethical/other rejections keep their set — the reviewer edits it in place.'
        );
        $this->assertSame('ethical', $row->rejection_type);
    }

    // ─── Group B: claiming ───────────────────────────────────────────────────

    /** B-05 The grading page AND the save require MY OWN claim. */
    public function test_b05_grading_requires_own_claim()
    {
        [$reviewerA] = $this->twoReviewers();
        $project = $this->progressProject();
        if (!$reviewerA || !$project) {
            $this->markTestSkipped('Missing reviewer or progress-stage project.');
        }
        $this->syncReviewers($project, [$reviewerA->id]);

        // Assigned but NOT claimed → direct URL and direct POST are refused …
        $this->actingAs($reviewerA)
            ->get("/projects/{$project->id}/grading")
            ->assertStatus(403);
        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'))
            ->assertStatus(403);

        // … after claiming his own proposal both work again.
        $this->claim($project, $reviewerA);
        $this->actingAs($reviewerA)
            ->get("/projects/{$project->id}/grading")
            ->assertOk();
    }

    // ─── Group D: consensus edge cases ───────────────────────────────────────

    /** D-06 Un-assigning the blocking reviewer completes the stage. */
    public function test_d06_unassigning_the_blocker_completes_the_stage()
    {
        $admin = $this->activeUser('Admin');
        [$reviewerA, $reviewerB] = $this->twoReviewers();
        $project = $this->progressProject();
        if (!$admin || !$reviewerA || !$reviewerB || !$project) {
            $this->markTestSkipped('Missing admin, two reviewers or a progress-stage project.');
        }

        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertNotSame(
            Project::STATUS_PROGRESS_REVIEWED,
            $project->fresh()->currentWorkflowStatus(),
            'Stage still waits for B.'
        );

        // The admin pulls the silent (but claimed) B out — allowed now.
        $this->actingAs($admin)->postJson('/workflow/unassign-reviewer', [
            'project_id'  => $project->id,
            'reviewer_id' => $reviewerB->id,
        ])->assertOk();

        $this->assertSame(
            Project::STATUS_PROGRESS_REVIEWED,
            $project->fresh()->currentWorkflowStatus(),
            'Removing the blocker completes the stage for the remaining reviewer.'
        );
        $this->assertSame(
            1,
            DB::table('projects_reviewers')->where('project_id', $project->id)->count(),
            'Only reviewer A remains assigned.'
        );
    }

    /** D-07 A reviewer added AFTER completion cannot reopen the frozen stage. */
    public function test_d07_late_reviewer_cannot_reopen_a_completed_stage()
    {
        $admin = $this->activeUser('Admin');
        $reviewers = User::where('type', 'Reviewer')
            ->where('is_active', 1)
            ->orderBy('id')
            ->take(3)
            ->get();
        if (!$admin || $reviewers->count() < 3) {
            $this->markTestSkipped('Need an active admin and three active reviewers.');
        }
        [$reviewerA, $reviewerB, $reviewerC] = $reviewers->all();

        $project = $this->progressProject();
        if (!$project) {
            $this->markTestSkipped('No project at the progress step.');
        }

        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->actingAs($reviewerB)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));
        $this->assertSame(Project::STATUS_PROGRESS_REVIEWED, $project->fresh()->currentWorkflowStatus());

        // A reviewer added afterwards must not touch the completed status …
        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerC->id],
        ])->assertOk();
        $this->assertSame(Project::STATUS_PROGRESS_REVIEWED, $project->fresh()->currentWorkflowStatus());

        // … his claim must not regress it …
        $this->claim($project, $reviewerC);
        $this->assertSame(Project::STATUS_PROGRESS_REVIEWED, $project->fresh()->currentWorkflowStatus());

        // … and his rejection is stored as HIS set while the stage stays frozen.
        $this->actingAs($reviewerC)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('rejected'));
        $this->assertSame(
            Project::STATUS_PROGRESS_REVIEWED,
            $project->fresh()->currentWorkflowStatus(),
            'A late rejection must not reopen a completed stage.'
        );
        $this->assertDatabaseHas('progress_report_grading', [
            'project_id' => $project->id,
            'user_id'    => $reviewerC->id,
            'publish'    => 'rejected',
        ]);
    }

    // ─── Group H: policy & removed legacy endpoints ──────────────────────────

    /** H-01 The proposal upload API refuses LPIs (research-office only). */
    public function test_h01_lpi_cannot_upload_proposal_via_api()
    {
        $lpi = $this->activeUser('LPI');
        $project = Project::first();
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or project.');
        }

        $this->actingAs($lpi)
            ->post(route('wizard.upload-proposal', ['id' => $project->id]), [])
            ->assertStatus(403);

        // The admin role passes the policy gate (what follows is never a 403).
        $admin = $this->activeUser('Admin');
        if ($admin) {
            $res = $this->actingAs($admin)->post(route('wizard.upload-proposal', ['id' => $project->id]), []);
            $this->assertNotSame(403, $res->getStatusCode(), 'Admins are not blocked by the policy gate.');
        }
    }

    /** H-02 The legacy consensus-bypass endpoints no longer exist. */
    public function test_h02_legacy_consensus_bypass_routes_are_removed()
    {
        $project = Project::first();
        if (!$project) {
            $this->markTestSkipped('No projects in test database.');
        }

        $this->post("/grading/{$project->id}/submit-grade")->assertNotFound();
        $this->post("/grading/{$project->id}/final-grade")->assertNotFound();
        $this->post('/workflow/submit-progress-review')->assertNotFound();
    }

    /**
     * I-01 Full lifecycle: proposal rejection → re-assignment → two-reviewer
     * consensus with a rejection → admin forward → LPI v2 resubmission →
     * re-grading on v2.
     *
     * Steps asserted:
     *  1. Admin assigns Reviewer A.
     *  2. A rejects the proposal (A is removed, logged in reviewer_rejections,
     *     status proposal_rejected, LPI notified).
     *  3. Admin re-assigns Reviewer B (allowed — a different reviewer).
     *  4. B accepts (claims); status goes back to Claimed.
     *  5. Admin also assigns Reviewer C alongside B.
     *  6. B and C both claim; B submits an accepted progress set, C submits a
     *     rejected one; consensus completes and the rejection wins.
     *  7. Admin forwards the rejection to the LPI (send_to_lpi).
     *  8. LPI uploads a v2 progress report; every reviewer set is archived and
     *     re-opened blank.
     *  9. Reviewer C (the one who rejected) grades v2 again, then B re-grades
     *     so the consensus completes on v2 (progress_reviewed).
     */
    public function test_i01_proposal_reject_reassign_consensus_resubmission_regrade()
    {
        [$reviewerA, $reviewerB, $reviewerC] = $this->threeReviewers();
        $project = $this->progressProject();
        $admin   = $this->activeUser('Admin');
        if (!$project || !$admin) {
            $this->markTestSkipped('Need a progress-stage project, an admin and three reviewers.');
        }

        $lpi = $project->lpi;
        if (!$lpi) {
            $this->markTestSkipped('Project has no LPI.');
        }

        // Start from a clean slate: no reviewers, no proposal-rejection noise.
        $this->syncReviewers($project, []);
        ReviewerRejection::where('project_id', $project->id)->delete();

        // ── 1. Admin assigns Reviewer A ────────────────────────────────────
        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'  => $project->id,
            'reviewer_ids' => [$reviewerA->id],
        ])->assertOk();

        $this->assertNotNull($this->pivotId($project, $reviewerA->id));
        $this->assertSame(Project::STATUS_ASSIGNED, $project->currentWorkflowStatus());

        // ── 2. Reviewer A rejects the proposal ─────────────────────────────
        $this->actingAs($reviewerA)->postJson('/workflow/submit-decision', [
            'project_id' => $project->id,
            'r_id'       => $this->pivotId($project, $reviewerA->id),
            'accept'     => 'rejected',
            'reason'     => 'Proposal is out of scope.',
        ])->assertOk();

        $this->assertNull($this->pivotId($project, $reviewerA->id) ?: null, 'A must be removed from the assignment.');
        $this->assertSame(0, (int) $this->pivotId($project, $reviewerA->id));
        $this->assertDatabaseHas('reviewer_rejections', [
            'project_id' => $project->id,
            'user_id'    => $reviewerA->id,
        ]);
        $this->assertSame(Project::STATUS_PROPOSAL_REJECTED, $project->fresh()->currentWorkflowStatus());

        // ── 3. Admin re-assigns Reviewer B (a different reviewer) ──────────
        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerB->id],
        ])->assertOk();

        $this->assertNotNull($this->pivotId($project, $reviewerB->id));
        $this->assertSame(Project::STATUS_ASSIGNED, $project->fresh()->currentWorkflowStatus());

        // A still cannot be reassigned (blocked by reviewer_rejections).
        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerA->id],
        ])->assertStatus(422);

        // ── 4. Reviewer B accepts (claims) ─────────────────────────────────
        $this->claim($project, $reviewerB);
        $this->assertSame(Project::STATUS_CLAIMED, $project->fresh()->currentWorkflowStatus());

        // ── 5. Admin also assigns Reviewer C alongside B ───────────────────
        $this->actingAs($admin)->postJson('/workflow/assign-reviewers', [
            'project_id'   => $project->id,
            'reviewer_ids' => [$reviewerC->id],
        ])->assertOk();

        $this->assertNotNull($this->pivotId($project, $reviewerC->id));
        $this->assertSame(Project::STATUS_CLAIMED, $project->fresh()->currentWorkflowStatus(), 'Adding a reviewer must not roll the status back.');

        // ── 6. Both review the progress report ─────────────────────────────
        $this->claim($project, $reviewerC);

        // B accepts.
        $this->actingAs($reviewerB)
            ->postJson("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'))
            ->assertOk();

        // The stage is still open — C has not submitted yet.
        $this->assertSame(
            Project::STATUS_CLAIMED,
            $project->fresh()->currentWorkflowStatus(),
            'Consensus must wait for every assigned reviewer.'
        );

        // C rejects → consensus completes, rejection wins.
        $this->actingAs($reviewerC)
            ->postJson("/grading/{$project->id}/save-progress-grade", $this->progressPayload('rejected'))
            ->assertOk();

        $this->assertSame(Project::STATUS_PROGRESS_REJECTED, $project->fresh()->currentWorkflowStatus());

        $rowB = ProgressReportGrading::where('project_id', $project->id)->where('user_id', $reviewerB->id)->first();
        $rowC = ProgressReportGrading::where('project_id', $project->id)->where('user_id', $reviewerC->id)->first();
        $this->assertSame('accepted', $rowB->publish);
        $this->assertSame('rejected', $rowC->publish);

        // ── 7. Admin forwards the rejection to the LPI ─────────────────────
        $this->actingAs($admin)->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => 'Please fix the highlighted issues and resubmit the progress report.',
            'report_type' => 'progress',
        ])->assertOk();

        $this->assertSame(Project::STATUS_PROGRESS_REJ_REVIEWED, $project->fresh()->currentWorkflowStatus());

        // ── 8. LPI uploads a v2 progress report ────────────────────────────
        $this->actingAs($lpi)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('progress-v2.pdf', 2, 'application/pdf'),
            'type' => 'progress',
        ])->assertOk()->assertJsonPath('version', 2);

        // Every reviewer set is archived and re-opened blank.
        $rowB = $rowB->fresh();
        $rowC = $rowC->fresh();
        $this->assertSame('pending', $rowB->publish, 'B\'s accepted set must re-open for v2.');
        $this->assertSame('pending', $rowC->publish, 'C\'s rejected set must re-open for v2.');
        $this->assertSame('accepted', $rowB->previous_grading['publish'] ?? null);
        $this->assertSame('rejected', $rowC->previous_grading['publish'] ?? null);

        // Claims survive the reset — both can re-grade without re-claiming.
        $this->assertNotNull(DB::table('projects_reviewers')->where('project_id', $project->id)->where('user_id', $reviewerB->id)->value('claimed_at'));
        $this->assertNotNull(DB::table('projects_reviewers')->where('project_id', $project->id)->where('user_id', $reviewerC->id)->value('claimed_at'));

        // ── 9. Reviewer C (who rejected) grades v2 again ───────────────────
        $this->actingAs($reviewerC)
            ->postJson("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'))
            ->assertOk();

        // C has re-submitted for v2, but B has not — consensus stays open.
        $this->assertSame(
            Project::STATUS_PROGRESS_REJ_REVIEWED,
            $project->fresh()->currentWorkflowStatus(),
            'Consensus on v2 must still wait for B.'
        );

        // B re-grades so the consensus completes on v2.
        $this->actingAs($reviewerB)
            ->postJson("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'))
            ->assertOk();

        $this->assertSame(Project::STATUS_PROGRESS_REVIEWED, $project->fresh()->currentWorkflowStatus());
        $this->assertSame('accepted', $rowC->fresh()->publish);
        $this->assertSame('accepted', $rowB->fresh()->publish);
    }

    /**
     * I-02 Ethical rejection lifecycle — routes through the admin, but the LPI
     * re-sends the ETHICAL APPROVAL document, never the progress/final report.
     *
     * Steps asserted:
     *  1. Reviewer rejects with rejection_type=ethical.
     *  2. The admin sees the rejection (hasUnreviewedRejection) and forwards it
     *     (send_to_lpi) — no 422.
     *  3. The LPI re-sends the ethical approval (upload type=ethical) — allowed.
     *  4. The progress-report upload stays LOCKED (423) — no report v2.
     *  5. The reviewer re-reviews (amends to accepted) → resolves to
     *     progress_reviewed.
     *  6. archiveAndResetGrading never resets an ethical rejection.
     */
    public function test_i02_ethical_rejection_routes_through_admin_and_lpi_resends_ethical()
    {
        [$reviewerA] = $this->twoReviewers();
        $project = $this->progressProject();
        $admin   = $this->activeUser('Admin');
        if (!$project || !$admin || !$reviewerA) {
            $this->markTestSkipped('Need a progress-stage project, an admin and a reviewer.');
        }

        $lpi = $project->lpi;
        if (!$lpi) {
            $this->markTestSkipped('Project has no LPI.');
        }

        $this->syncReviewers($project, [$reviewerA->id]);
        ReviewerRejection::where('project_id', $project->id)->delete();
        $this->claim($project, $reviewerA);

        // ── 1. Reject with an Ethical rejection type ───────────────────────
        $reject = $this->progressPayload('rejected');
        $reject['rejection_type'] = 'ethical';
        $this->actingAs($reviewerA)
            ->postJson("/grading/{$project->id}/save-progress-grade", $reject)
            ->assertOk();

        $latest = $project->fresh()->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_PROGRESS_REJECTED, $latest->status);
        $this->assertSame('ethical', $latest->metadata['rejection_type'] ?? null);

        // ── 2. The admin SEES the rejection and can forward it ─────────────
        $this->assertTrue(
            $project->fresh()->hasUnreviewedRejection('progress'),
            'An ethical rejection must surface for the admin review step.'
        );

        $this->actingAs($admin)->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => 'The ethical approval is missing — please re-send it.',
            'report_type' => 'progress',
        ])->assertOk()->assertJsonPath('success', true);

        $latest = $project->fresh()->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_PROGRESS_REJ_REVIEWED, $latest->status);
        $this->assertSame('send_to_lpi', $latest->metadata['action'] ?? null);
        $this->assertSame('ethical', $latest->metadata['rejection_type'] ?? null);
        $this->assertFalse($project->fresh()->hasUnreviewedRejection('progress'), 'Forwarded — no longer unreviewed.');

        // ── 3. The LPI re-sends the ETHICAL APPROVAL (allowed) ─────────────
        $this->actingAs($lpi)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('ethics.pdf', 2, 'application/pdf'),
            'type' => 'ethical',
        ])->assertOk()->assertJsonPath('success', true);

        // ── 4. The progress-report upload stays LOCKED (no report v2) ──────
        $this->actingAs($lpi)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('v2.pdf', 2, 'application/pdf'),
            'type' => 'progress',
        ])->assertStatus(423, 'The report itself must NOT be re-uploaded for an ethical rejection.');

        // ── 5. The reviewer re-reviews → resolves ──────────────────────────
        $this->actingAs($reviewerA)
            ->postJson("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'))
            ->assertOk();

        $this->assertSame(
            Project::STATUS_PROGRESS_REVIEWED,
            $project->fresh()->currentWorkflowStatus(),
            'Re-reviewing after the ethical re-send must resolve the rejection.'
        );

        // ── 6. archiveAndResetGrading never resets an ethical rejection ────
        $this->invokeArchiveAndReset($project->id, 'progress');
        $rowA = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewerA->id)->first();
        $this->assertSame('accepted', $rowA->publish, 'The re-review set must stand; no reset.');
    }

    /**
     * I-03 Final report rejection chain — the terminal path to Graded.
     *
     * A rejection of the FINAL report (a different stage than progress):
     * reviewer rejects → consensus records final_rejected → admin forwards →
     * LPI uploads a final v2 (unlocked past the deadline, v1 kept, every
     * final grading set reset) → reviewer re-grades the v2 → the stage
     * completes to the terminal Graded status.
     */
    public function test_i03_final_rejection_admin_forward_v2_regrade_reaches_graded()
    {
        [$reviewerA] = $this->twoReviewers();
        $project = $this->finalProject();
        $admin   = $this->activeUser('Admin');
        if (!$project || !$admin || !$reviewerA) {
            $this->markTestSkipped('Need a final-stage project, an admin and a reviewer.');
        }

        $lpi = $project->lpi;
        if (!$lpi) {
            $this->markTestSkipped('Project has no LPI.');
        }

        $this->syncReviewers($project, [$reviewerA->id]);
        ReviewerRejection::where('project_id', $project->id)->delete();
        $this->claim($project, $reviewerA);

        // ── 1. Reviewer rejects the FINAL report ───────────────────────────
        $this->actingAs($reviewerA)
            ->postJson("/grading/{$project->id}/save-final-grade", $this->finalPayload('rejected'))
            ->assertOk();

        $this->assertSame(Project::STATUS_FINAL_REJECTED, $project->fresh()->currentWorkflowStatus());

        $row = FinalReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewerA->id)->first();
        $this->assertNotNull($row);
        $this->assertSame('rejected', $row->publish);
        $this->assertSame('report', $row->rejection_type);

        // ── 2. Admin forwards the rejection to the LPI ─────────────────────
        $this->assertTrue($project->fresh()->hasUnreviewedRejection('final'));

        $this->actingAs($admin)->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => 'The final report needs revision before acceptance.',
            'report_type' => 'final',
        ])->assertOk()->assertJsonPath('success', true);

        $latest = $project->fresh()->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_FINAL_REJ_REVIEWED, $latest->status);
        $this->assertSame('send_to_lpi', $latest->metadata['action'] ?? null);

        // ── 3. LPI uploads a final v2 (unlocked past the deadline) ─────────
        $this->assertTrue($project->submissions()->where('type', 'final')->where('version', 1)->exists());

        $this->actingAs($lpi)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('final-v2.pdf', 2, 'application/pdf'),
            'type' => 'final',
        ])->assertOk()->assertJsonPath('version', 2);

        $finalRows = $project->submissions()->where('type', 'final')->orderBy('version')->get();
        $this->assertCount(2, $finalRows, 'v1 must be kept alongside the new v2.');
        $this->assertSame(2, (int) $finalRows[1]->version);

        // ── 4. Every final grading set is archived & reset for v2 ──────────
        $row = $row->fresh();
        $this->assertSame('pending', $row->publish, 'The rejected set must re-open blank for v2.');
        $this->assertNotNull($row->previous_grading);
        $this->assertSame('rejected', $row->previous_grading['publish'] ?? null);

        // ── 5. Reviewer re-grades the v2 → the stage completes ─────────────
        $this->actingAs($reviewerA)
            ->postJson("/grading/{$project->id}/save-final-grade", $this->finalPayload('accepted'))
            ->assertOk();

        $this->assertSame(
            Project::STATUS_GRADED,
            $project->fresh()->currentWorkflowStatus(),
            'Re-grading the final v2 must reach the terminal Graded status.'
        );
        $this->assertSame('accepted', $row->fresh()->publish);
    }

    /**
     * I-04 Final report rejection with rejection_type=ethical.
     *
     * Same admin-mediated routing as I-02, but on the FINAL stage: the admin
     * forwards it, the LPI re-sends the ethical approval, the final report
     * itself stays locked (its upload AND the readiness upload both share the
     * final rejection hold), and the reviewer's re-review completes to Graded.
     */
    public function test_i04_final_ethical_rejection_routes_through_admin_to_graded()
    {
        [$reviewerA] = $this->twoReviewers();
        $project = $this->finalProject();
        $admin   = $this->activeUser('Admin');
        if (!$project || !$admin || !$reviewerA) {
            $this->markTestSkipped('Need a final-stage project, an admin and a reviewer.');
        }

        $lpi = $project->lpi;
        if (!$lpi) {
            $this->markTestSkipped('Project has no LPI.');
        }

        $this->syncReviewers($project, [$reviewerA->id]);
        ReviewerRejection::where('project_id', $project->id)->delete();
        $this->claim($project, $reviewerA);

        // ── 1. Reviewer rejects the FINAL report with an Ethical type ──────
        $reject = $this->finalPayload('rejected');
        $reject['rejection_type'] = 'ethical';
        $this->actingAs($reviewerA)
            ->postJson("/grading/{$project->id}/save-final-grade", $reject)
            ->assertOk();

        $latest = $project->fresh()->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_FINAL_REJECTED, $latest->status);
        $this->assertSame('ethical', $latest->metadata['rejection_type'] ?? null);

        // ── 2. The admin SEES it and forwards it (no 422) ─────────────────
        $this->assertTrue($project->fresh()->hasUnreviewedRejection('final'));

        $this->actingAs($admin)->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => 'The ethical approval for the final report is invalid.',
            'report_type' => 'final',
        ])->assertOk()->assertJsonPath('success', true);

        $latest = $project->fresh()->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_FINAL_REJ_REVIEWED, $latest->status);
        $this->assertSame('ethical', $latest->metadata['rejection_type'] ?? null);

        // ── 3. LPI re-sends the ETHICAL APPROVAL (allowed) ─────────────────
        $this->actingAs($lpi)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('ethics-final.pdf', 2, 'application/pdf'),
            'type' => 'ethical',
        ])->assertOk()->assertJsonPath('success', true);

        // ── 4. The FINAL report (and readiness) stay locked ────────────────
        $this->actingAs($lpi)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('final-v2.pdf', 2, 'application/pdf'),
            'type' => 'final',
        ])->assertStatus(423, 'The final report itself must NOT be re-uploaded.');

        $this->actingAs($lpi)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('readiness.pdf', 2, 'application/pdf'),
            'type' => 'readiness',
        ])->assertStatus(423, 'Readiness shares the final rejection hold.');

        // ── 5. The reviewer re-reviews → Graded ────────────────────────────
        $this->actingAs($reviewerA)
            ->postJson("/grading/{$project->id}/save-final-grade", $this->finalPayload('accepted'))
            ->assertOk();

        $this->assertSame(
            Project::STATUS_GRADED,
            $project->fresh()->currentWorkflowStatus(),
            'The ethical rejection on final must resolve to the terminal Graded status.'
        );
    }

    /**
     * Call the protected archiveAndResetGrading() exactly as uploadFile()
     * does for a v2 resubmission.
     */
    private function invokeArchiveAndReset(int $projectId, string $reportType): void
    {
        $method = new \ReflectionMethod(ProgressController::class, 'archiveAndResetGrading');
        $method->setAccessible(true);
        $method->invoke(app(ProgressController::class), $projectId, $reportType);
    }
}
