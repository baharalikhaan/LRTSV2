<?php

namespace Tests\Feature;

use App\Models\Commitment;
use App\Models\FinalReportGrading;
use App\Models\Outcome;
use App\Models\ProgressReportGrading;
use App\Models\Project;
use App\Models\ProjectResearcher;
use App\Models\ProjectStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Reviewer grading part + the admin Evaluate Projects queue.
 *
 * Covers the access gates on the grading page, the progress/final save
 * endpoints (validation, drafts, deadline + stage + extended-progress gates,
 * final total), the verification AJAX endpoint (IDOR guard, admin bypass,
 * all-or-nothing batch), the Graded Projects page, the View-Grade modal and
 * the Evaluate Projects queue inclusion/exit rules.
 *
 * Consensus / claims / un-assign lifecycle lives in ConsensusGradingTest,
 * MultiReviewerGradingTest and GradingWorkflowScenariosTest.
 *
 * Runs against rtsnew_test and rolls back every test.
 */
class ReviewerGradingTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    private function reviewer(): ?User
    {
        return User::where('type', 'Reviewer')->where('is_active', 1)->orderBy('id')->first();
    }

    private function secondReviewer(): ?User
    {
        return User::where('type', 'Reviewer')->where('is_active', 1)->orderBy('id')->skip(1)->first();
    }

    private function admin(): ?User
    {
        return $this->activeUser('Admin');
    }

    private function lpi(): ?User
    {
        return $this->activeLpiUser();
    }

    private function activeLpiUser(): ?User
    {
        return User::where('type', 'LPI')->where('is_active', 1)->first();
    }

    /** Wipe legacy assignments so the reviewer team is deterministic. */
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

    /** Progress stage ready to grade: report submitted + deadline passed. */
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

    /** Final stage ready to grade: final submitted + deadline passed. */
    private function finalProject(): ?Project
    {
        return Project::whereHas('submissions', function ($q) {
            $q->where('type', 'final');
        })->whereHas('program', function ($q) {
            $q->whereNull('final_rpt_deadline')->orWhere('final_rpt_deadline', '<', now());
        })->whereDoesntHave('statusHistories', function ($q) {
            $q->whereIn('status', [
                Project::STATUS_FINAL_REJECTED,
                Project::STATUS_FINAL_REJ_REVIEWED,
                Project::STATUS_GRADED,
            ]);
        })->first();
    }

    /**
     * Project that owes ONLY the progress stage to this admin: a reviewer is
     * assigned, a progress report exists, no final/PR2 report exists (so the
     * queue row clears the moment progress is submitted), and the admin has
     * not published a progress set yet.
     */
    private function queueProject(int $adminId): ?Project
    {
        return Project::whereHas('reviewers')
            ->whereNotNull('program_id')
            ->whereHas('program')
            ->whereHas('submissions', function ($q) {
                $q->where('type', 'progress');
            })
            ->whereDoesntHave('submissions', function ($q) {
                $q->whereIn('type', ['final', 'progress2']);
            })
            ->whereDoesntHave('progressGradings', function ($q) use ($adminId) {
                $q->where('user_id', $adminId)->where('publish', '!=', 'pending');
            })
            ->first();
    }

    private function setProgramDeadline(Project $project, string $field, $when): void
    {
        DB::table('programs')->where('id', $project->program_id)->update([$field => $when]);
    }

    /**
     * Unique, changelog-free needle for presence/absence assertions on the
     * graded-projects and evaluate-queue pages. old_project_id is unsafe —
     * the footer version history quotes an example id verbatim.
     */
    private function gradingPath(Project $project): string
    {
        return '/projects/' . $project->id . '/grading';
    }

    private function progressPayload(string $decision = 'accepted'): array
    {
        $payload = [
            'save_action'        => 'submit',
            'report_type'        => 'progress',
            'publish'            => $decision,
            'achievementsRating' => 4,
            'publicationsRating' => 4,
            'studentsRating'     => 4,
            'budgetRating'       => 4,
        ];

        if ($decision === 'rejected') {
            $payload['rejection_type']   = 'report';
            $payload['rejection_reason'] = 'Reviewer grading scenario reason.';
        }

        return $payload;
    }

    private function finalPayload(string $decision = 'accepted'): array
    {
        $payload = [
            'save_action' => 'submit',
            'publish'     => $decision,
            'gradeA'      => 4,
            'gradeB'      => 4,
            'gradeC'      => 4,
            'gradeD'      => 4,
            'scoreA'      => 5,
            'scoreB'      => 6,
            'scoreC'      => 7,
        ];

        if ($decision === 'rejected') {
            $payload['rejection_type']   = 'report';
            $payload['rejection_reason'] = 'Reviewer grading scenario reason.';
        }

        return $payload;
    }

    // —— Group A: grading page access ——————————————————————————————

    public function test_a01_assigned_and_claimed_reviewer_opens_grading_page()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $progressSubmission = $project->submissions()->where('type', 'progress')->latest('version')->first();
        $this->actingAs($reviewer)
            ->get(route('projects.grading', ['id' => $project->id]))
            ->assertOk()
            ->assertSee('Progress Report')
            ->assertSee($progressSubmission->stored_filename);
    }

    public function test_a02_assigned_but_unclaimed_reviewer_is_rejected()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);

        $this->actingAs($reviewer)
            ->get(route('projects.grading', ['id' => $project->id]))
            ->assertStatus(403);
    }

    public function test_a03_unassigned_reviewer_is_rejected()
    {
        $project = $this->progressProject();
        $reviewer = $this->secondReviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or second reviewer.');
        }

        $this->syncReviewers($project, [$this->reviewer()->id]);

        $this->actingAs($reviewer)
            ->get(route('projects.grading', ['id' => $project->id]))
            ->assertStatus(403);
    }

    public function test_a04_admin_opens_grading_page_without_assignment()
    {
        $project = $this->progressProject();
        $admin   = $this->admin();
        if (!$project || !$admin) {
            $this->markTestSkipped('No progress-stage project or admin.');
        }

        $this->syncReviewers($project, [$this->reviewer()->id]);

        $this->actingAs($admin)
            ->get(route('projects.grading', ['id' => $project->id]))
            ->assertOk();
    }

    public function test_a05_lpi_cannot_open_grading_page()
    {
        $project = $this->progressProject();
        $lpi     = $this->lpi();
        if (!$project || !$lpi) {
            $this->markTestSkipped('No progress-stage project or LPI.');
        }

        $this->actingAs($lpi)
            ->get(route('projects.grading', ['id' => $project->id]))
            ->assertStatus(403);
    }

    public function test_a06_grade_alias_redirects_to_unified_page()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $this->actingAs($reviewer)
            ->get(route('grading.grade', ['id' => $project->id]))
            ->assertRedirect(route('projects.grading', ['id' => $project->id]));
    }

    // —— Group B: progress grading save ————————————————————————————

    public function test_b01_submitted_accepted_progress_is_stored()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $this->progressPayload('accepted'))
            ->assertOk()
            ->assertJsonPath('success', true);

        $row = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->where('report_type', 'progress')
            ->first();
        $this->assertNotNull($row);
        $this->assertSame('accepted', $row->publish);
        $this->assertSame(1, (int) $row->isAccepted);
    }

    public function test_b02_draft_forces_publish_pending()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        // A draft must never count as a submission, even if publish is sent.
        $payload = $this->progressPayload('accepted');
        $payload['save_action'] = 'draft';

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $payload)
            ->assertOk();

        $row = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->where('report_type', 'progress')
            ->first();
        $this->assertSame('pending', $row->publish);
    }

    public function test_b03_rejection_requires_reason_and_type()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), [
                'save_action'        => 'submit',
                'report_type'        => 'progress',
                'publish'            => 'rejected',
                'achievementsRating' => 3,
                'publicationsRating' => 3,
                'studentsRating'     => 3,
                'budgetRating'       => 3,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason', 'rejection_type']);
    }

    public function test_b04_rating_outside_range_is_rejected()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $payload = $this->progressPayload('accepted');
        $payload['achievementsRating'] = 9;

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['achievementsRating']);
    }

    public function test_b05_unassigned_reviewer_cannot_save()
    {
        $project  = $this->progressProject();
        $reviewer = $this->secondReviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or second reviewer.');
        }

        $this->syncReviewers($project, [$this->reviewer()->id]);

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $this->progressPayload('accepted'))
            ->assertStatus(403);
    }

    public function test_b06_unclaimed_reviewer_cannot_save()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $this->progressPayload('accepted'))
            ->assertStatus(403);
    }

    public function test_b07_grading_locked_before_deadline()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(2));

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $this->progressPayload('accepted'))
            ->assertStatus(403);
    }

    public function test_b08_grading_locked_without_progress_submission()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        // Remove the progress report — grading must refuse with no submission.
        $project->submissions()->where('type', 'progress')->delete();

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $this->progressPayload('accepted'))
            ->assertStatus(403);
    }

    public function test_b09_progress2_requires_extended_progress()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        DB::table('projects')->where('id', $project->id)->update(['extended_progress' => 0]);
        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $payload = $this->progressPayload('accepted');
        $payload['report_type'] = 'progress2';

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $payload)
            ->assertStatus(403);
    }

    public function test_b10_reserved_publish_accepted_on_progress()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $payload = $this->progressPayload('accepted');
        $payload['publish'] = 'reserved';

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $payload)
            ->assertOk();

        $row = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->where('report_type', 'progress')
            ->first();
        $this->assertSame('reserved', $row->publish);
    }

    // —— Group C: final grading save ————————————————————————————————

    public function test_c01_final_total_is_sum_of_scores()
    {
        $project  = $this->finalProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No final-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveFinalGrade', ['id' => $project->id]), $this->finalPayload('accepted'))
            ->assertOk()
            ->assertJsonPath('success', true);

        $row = FinalReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->first();
        $this->assertNotNull($row);
        $this->assertSame('accepted', $row->publish);
        $this->assertSame(18.0, (float) $row->total);
    }

    public function test_c02_final_rejection_requires_reason_and_type()
    {
        $project  = $this->finalProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No final-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveFinalGrade', ['id' => $project->id]), [
                'save_action' => 'submit',
                'publish'     => 'rejected',
                'gradeA'      => 3,
                'gradeB'      => 3,
                'gradeC'      => 3,
                'gradeD'      => 3,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason', 'rejection_type']);
    }

    public function test_c03_final_grading_locked_without_submission()
    {
        $project  = $this->finalProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No final-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $project->submissions()->where('type', 'final')->delete();

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveFinalGrade', ['id' => $project->id]), $this->finalPayload('accepted'))
            ->assertStatus(403);
    }

    public function test_c04_final_grading_locked_before_deadline()
    {
        $project  = $this->finalProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No final-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->addDays(2));

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveFinalGrade', ['id' => $project->id]), $this->finalPayload('accepted'))
            ->assertStatus(403);
    }

    public function test_c05_reserved_publish_rejected_on_final()
    {
        $project  = $this->finalProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No final-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $payload = $this->finalPayload('accepted');
        $payload['publish'] = 'reserved';

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveFinalGrade', ['id' => $project->id]), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['publish']);
    }

    public function test_c06_final_draft_forces_publish_pending()
    {
        $project  = $this->finalProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No final-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $payload = $this->finalPayload('accepted');
        $payload['save_action'] = 'draft';

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveFinalGrade', ['id' => $project->id]), $payload)
            ->assertOk();

        $row = FinalReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->first();
        $this->assertSame('pending', $row->publish);
    }

    // —— Group D: updateVerification ————————————————————————————————

    public function test_d01_assigned_reviewer_verifies_own_outcomes()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $outcome = Outcome::where('project_id', $project->id)->first()
            ?: Outcome::whereNotNull('project_id')->first();
        if (!$outcome) {
            $this->markTestSkipped('No outcome in test database.');
        }

        $project = $outcome->project;
        $this->syncReviewers($project, [$reviewer->id]);

        $this->actingAs($reviewer)
            ->postJson(route('grading.updateVerification'), [
                'type'   => 'outcome',
                'ids'    => [$outcome->id],
                'status' => 'verified',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('verified', $outcome->fresh()->verifcation_by_reviewer);
    }

    public function test_d02_unassigned_reviewer_cannot_verify()
    {
        $outcome = Outcome::whereNotNull('project_id')->first();
        if (!$outcome) {
            $this->markTestSkipped('No outcome in test database.');
        }

        $project  = $outcome->project;
        $reviewer = $this->secondReviewer();
        if (!$reviewer) {
            $this->markTestSkipped('No second reviewer.');
        }

        $this->syncReviewers($project, [$this->reviewer()->id]);
        $before = $outcome->fresh()->verifcation_by_reviewer;

        $this->actingAs($reviewer)
            ->postJson(route('grading.updateVerification'), [
                'type'   => 'outcome',
                'ids'    => [$outcome->id],
                'status' => 'verified',
            ])
            ->assertStatus(403);

        $this->assertSame($before, $outcome->fresh()->verifcation_by_reviewer, 'No row may change on a 403.');
    }

    public function test_d03_admin_can_verify_any_rows()
    {
        $outcome = Outcome::whereNotNull('project_id')->first();
        if (!$outcome) {
            $this->markTestSkipped('No outcome in test database.');
        }

        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        // No reviewer assignment needed — the admin may verify on behalf.
        DB::table('projects_reviewers')->where('project_id', $outcome->project_id)->delete();

        $this->actingAs($admin)
            ->postJson(route('grading.updateVerification'), [
                'type'   => 'outcome',
                'ids'    => [$outcome->id],
                'status' => 'verified',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('verified', $outcome->fresh()->verifcation_by_reviewer);
    }

    public function test_d04_one_foreign_id_aborts_whole_batch()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $own   = Outcome::where('project_id', $project->id)->first();
        $foreign = Outcome::whereNotNull('project_id')->where('project_id', '!=', $project->id)->first();
        if (!$own || !$foreign) {
            $this->markTestSkipped('Need an owned and a foreign outcome.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $ownBefore    = $own->fresh()->verifcation_by_reviewer;
        $foreignBefore = $foreign->fresh()->verifcation_by_reviewer;

        $this->actingAs($reviewer)
            ->postJson(route('grading.updateVerification'), [
                'type'   => 'outcome',
                'ids'    => [$own->id, $foreign->id],
                'status' => 'verified',
            ])
            ->assertStatus(403);

        $this->assertSame($ownBefore, $own->fresh()->verifcation_by_reviewer, 'Batch must be all-or-nothing.');
        $this->assertSame($foreignBefore, $foreign->fresh()->verifcation_by_reviewer, 'Batch must be all-or-nothing.');
    }

    public function test_d05_invalid_type_or_status_is_rejected()
    {
        $reviewer = $this->reviewer();
        if (!$reviewer) {
            $this->markTestSkipped('No reviewer.');
        }

        $this->actingAs($reviewer)
            ->postJson(route('grading.updateVerification'), [
                'type'   => 'bogus',
                'ids'    => [1],
                'status' => 'verified',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);

        $this->actingAs($reviewer)
            ->postJson(route('grading.updateVerification'), [
                'type'   => 'outcome',
                'ids'    => [1],
                'status' => 'bogus',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // —— Group E: Graded Projects page ———————————————————————————————

    public function test_e01_reviewer_sees_assigned_projects()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);

        $this->actingAs($reviewer)
            ->get(route('grading.index'))
            ->assertOk()
            ->assertSee($this->gradingPath($project));
    }

    public function test_e02_admin_without_assignment_sees_empty_list()
    {
        $project = $this->progressProject();
        $admin   = $this->admin();
        if (!$project || !$admin) {
            $this->markTestSkipped('No progress-stage project or admin.');
        }

        $this->syncReviewers($project, [$this->reviewer()->id]);
        DB::table('projects_reviewers')->where('user_id', $admin->id)->delete();

        $this->actingAs($admin)
            ->get(route('grading.index'))
            ->assertOk()
            ->assertDontSee($this->gradingPath($project));
    }

    public function test_e03_cycle_filter_narrows_list()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);

        // The cycle filter must resolve through the program (no projects.cycle_id).
        $cycleId = $project->program->cycle_id ?? null;
        $this->actingAs($reviewer)
            ->get(route('grading.index', array_filter(['cycle_id' => $cycleId])))
            ->assertOk();

        // A cycle that owns none of the reviewer's programs yields an empty list.
        $otherCycle = DB::table('cycle_configs')
            ->where('id', '!=', $cycleId)
            ->value('id');
        if ($otherCycle) {
            $this->actingAs($reviewer)
                ->get(route('grading.index', ['cycle_id' => $otherCycle]))
                ->assertOk()
                ->assertDontSee($this->gradingPath($project));
        }
    }

    // —— Group F: View-Grade modal ———————————————————————————————————

    public function test_f01_assigned_reviewer_sees_own_grading()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $this->progressPayload('accepted'))
            ->assertOk();

        $this->actingAs($reviewer)
            ->get(route('workflow.view-grade', ['projectId' => $project->id]))
            ->assertOk();
    }

    public function test_f02_unassigned_reviewer_gets_error_payload()
    {
        $project = $this->progressProject();
        $reviewer = $this->secondReviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or second reviewer.');
        }

        $this->syncReviewers($project, [$this->reviewer()->id]);

        $this->actingAs($reviewer)
            ->getJson(route('workflow.view-grade', ['projectId' => $project->id]))
            ->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_f03_admin_bypasses_assignment_check()
    {
        $project = $this->progressProject();
        $admin   = $this->admin();
        if (!$project || !$admin) {
            $this->markTestSkipped('No progress-stage project or admin.');
        }

        $this->syncReviewers($project, [$this->reviewer()->id]);
        DB::table('projects_reviewers')->where('user_id', $admin->id)->delete();

        $this->actingAs($admin)
            ->get(route('workflow.view-grade', ['projectId' => $project->id]))
            ->assertOk();
    }

    // —— Group G: Evaluate Projects queue (admin) ————————————————————

    public function test_g01_reviewer_cannot_open_queue()
    {
        $reviewer = $this->reviewer();
        if (!$reviewer) {
            $this->markTestSkipped('No reviewer.');
        }

        $this->actingAs($reviewer)
            ->get(route('projects.pending-reviews'))
            ->assertStatus(403);
    }

    public function test_g02_project_without_reviewer_can_appear_for_admin_review()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        $project = $this->queueProject($admin->id);
        if (!$project) {
            $this->markTestSkipped('No project with a progress report awaiting the admin\'s grade.');
        }

        // The project remains reviewable by the admin even with no reviewer assigned.
        DB::table('projects_reviewers')->where('project_id', $project->id)->delete();

        $this->actingAs($admin)
            ->get(route('projects.pending-reviews'))
            ->assertOk()
            ->assertSee($this->gradingPath($project));
    }

    public function test_g03_project_appears_until_admin_submits()
    {
        $admin   = $this->admin();
        $reviewer = $this->reviewer();
        if (!$admin || !$reviewer) {
            $this->markTestSkipped('No admin or reviewer.');
        }

        $project = $this->queueProject($admin->id);
        if (!$project) {
            $this->markTestSkipped('No queue-ready project.');
        }

        $this->syncReviewers($project, [$reviewer->id]);

        $this->actingAs($admin)
            ->get(route('projects.pending-reviews'))
            ->assertOk()
            ->assertSee($this->gradingPath($project));

        // The assigned reviewer submitting must NOT clear the admin's row.
        $this->claim($project, $reviewer);
        $this->actingAs($reviewer)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $this->progressPayload('accepted'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('projects.pending-reviews'))
            ->assertOk()
            ->assertSee($this->gradingPath($project), 'Reviewer submission must not clear the admin queue row.');
    }

    public function test_g04_row_clears_once_admin_submits()
    {
        $admin    = $this->admin();
        $reviewer = $this->reviewer();
        if (!$admin || !$reviewer) {
            $this->markTestSkipped('No admin or reviewer.');
        }

        $project = $this->queueProject($admin->id);
        if (!$project) {
            $this->markTestSkipped('No queue-ready project.');
        }

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $this->actingAs($admin)
            ->get(route('projects.pending-reviews'))
            ->assertOk()
            ->assertSee($this->gradingPath($project));

        // The admin steps in and submits his own set.
        $this->actingAs($admin)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $this->progressPayload('accepted'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('projects.pending-reviews'))
            ->assertOk()
            ->assertDontSee($this->gradingPath($project), 'The row must clear once THIS admin has submitted.');
    }

    public function test_g05_draft_still_counts_as_owed()
    {
        $admin    = $this->admin();
        $reviewer = $this->reviewer();
        if (!$admin || !$reviewer) {
            $this->markTestSkipped('No admin or reviewer.');
        }

        $project = $this->queueProject($admin->id);
        if (!$project) {
            $this->markTestSkipped('No queue-ready project.');
        }

        $this->syncReviewers($project, [$reviewer->id]);

        $payload = $this->progressPayload('accepted');
        $payload['save_action'] = 'draft';

        $this->actingAs($admin)
            ->postJson(route('grading.saveProgressGrade', ['id' => $project->id]), $payload)
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('projects.pending-reviews'))
            ->assertOk()
            ->assertSee($this->gradingPath($project), 'A draft is not a submission — the row must remain owed.');
    }

    public function test_g06_status_filter_narrows_queue()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        $project = $this->queueProject($admin->id);
        if (!$project) {
            $this->markTestSkipped('No queue-ready project.');
        }

        $reviewer = $this->reviewer();
        if ($reviewer) {
            $this->syncReviewers($project, [$reviewer->id]);
        }

        $status = $project->currentWorkflowStatus();

        // Matching status keeps the row.
        $this->actingAs($admin)
            ->get(route('projects.pending-reviews', ['status' => $status]))
            ->assertOk()
            ->assertSee($this->gradingPath($project));

        // A non-matching status filters it out.
        $other = $status === 'Claimed' ? 'Registered' : 'Claimed';
        $this->actingAs($admin)
            ->get(route('projects.pending-reviews', ['status' => $other]))
            ->assertOk()
            ->assertDontSee($this->gradingPath($project));
    }

    // —— Group H: auto-grade integrity ————————————————————————————————————

    /** H-01 The auto-grade must only count VERIFIED outcomes. */
    public function test_h01_auto_grade_ignores_unverified_outcomes()
    {
        $admin   = $this->admin();
        $project = $this->finalProject();
        if (!$admin || !$project) {
            $this->markTestSkipped('No admin or final-stage project.');
        }

        // Isolate section B: exactly one expected publication point-set and a
        // single outcome, so auto-grade B is 0 (unverified) → 5 (verified).
        Commitment::updateOrCreate(['project_id' => $project->id], [
            'q1article'  => 1,
            'q2article'  => 0,
            'q3article'  => 0,
            'q4article'  => 0,
            'confArticle' => 0,
            'books'      => 0,
            'editBooks'  => 0,
            'chapters'   => 0,
        ]);

        // The auto-grade display is setting-gated (test DB ships it as '0').
        DB::table('ai_settings')->updateOrInsert(
            ['key' => 'auto_grade_visibility'],
            ['value' => '1']
        );

        // A single, currently UNVERIFIED journal_q1 outcome (no other pub rows).
        $project->outcomes()
            ->whereIn('type', ['journal_q1', 'journal_q2', 'journal_q3', 'journal_q4', 'conference', 'book', 'edited_book', 'book_chapter'])
            ->delete();
        $outcome = $project->outcomes()->create([
            'user_id'                 => $admin->id,
            'type'                    => 'journal_q1',
            'identifier'              => '10.1000/auto-grade',
            'verifcation_by_system'   => 'pending',
            'verifcation_by_reviewer' => 'pending',
            'score'                   => 0,
        ]);

        // Unverified → section B auto-grade must be 0.
        $unverified = $this->actingAs($admin)
            ->get(route('projects.grading', ['id' => $project->id]))
            ->assertOk()
            ->getContent();
        $this->assertMatchesRegularExpression(
            '/ws-pub-grade">\s*0\s*</',
            $unverified,
            'An unverified outcome must not contribute to the auto-grade.'
        );

        // Reviewer verifies it → section B auto-grade becomes 5 (8/8 × 5).
        $outcome->update(['verifcation_by_reviewer' => 'verified']);
        $verified = $this->actingAs($admin)
            ->get(route('projects.grading', ['id' => $project->id]))
            ->assertOk()
            ->getContent();
        $this->assertMatchesRegularExpression(
            '/ws-pub-grade">\s*5\s*</',
            $verified,
            'A verified outcome must count toward the auto-grade.'
        );
    }
}
