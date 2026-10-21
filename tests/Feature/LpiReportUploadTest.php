<?php

namespace Tests\Feature;

use App\Models\FinalReportGrading;
use App\Models\ProgressReportGrading;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Executable scenarios from docs/TEST-SCENARIOS-LPI-UPLOADS.md — the LPI
 * upload side of the workflow: v1 uploads, deadline locks, the rejection →
 * admin review → v2 resubmission chain (v1 kept, every reviewer set archived
 * & re-opened), ethical approvals, PR2/readiness gates and the project-data
 * freeze.
 *
 * Uploads write to the real storage/app/uploads tree (the controller checks
 * storage_path() directly, so Storage::fake cannot be used); every test
 * tracks the files it creates and deletes them in tearDown.
 *
 * Runs real HTTP requests against rtsnew_test and rolls back after every test.
 */
class LpiReportUploadTest extends TestCase
{
    use DatabaseTransactions;

    /** @var string[] storage-relative paths created during the test */
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $relative) {
            $full = storage_path('app/' . $relative);
            if (is_file($full)) {
                @unlink($full);
            }
        }
        $this->createdFiles = [];

        parent::tearDown();
    }

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    private function activeLpi(): ?User
    {
        return $this->activeUser('LPI');
    }

    private function reviewer(): ?User
    {
        return User::where('type', 'Reviewer')->where('is_active', 1)->orderBy('id')->first();
    }

    private function secondReviewer(): ?User
    {
        return User::where('type', 'Reviewer')->where('is_active', 1)->orderBy('id')->skip(1)->first();
    }

    /**
     * Project safe for upload tests: no submissions of the given types (the
     * replace branch would delete real files), no rejection / completion
     * statuses on record (they change the lock & version behaviour), and an
     * LPI we control.
     */
    private function cleanProject(array $withoutTypes, bool $nonStudent = false): ?Project
    {
        $lpi = $this->activeLpi();
        if (!$lpi) {
            return null;
        }

        $query = Project::whereDoesntHave('submissions', function ($q) use ($withoutTypes) {
            $q->whereIn('type', $withoutTypes);
        })
            ->whereNotNull('program_id')
            ->whereHas('program')
            ->whereDoesntHave('statusHistories', function ($q) {
                $q->whereIn('status', [
                    Project::STATUS_PROGRESS_REVIEWED,
                    Project::STATUS_PROGRESS_REJECTED,
                    Project::STATUS_PROGRESS_REJ_REVIEWED,
                    Project::STATUS_PROGRESS2_ADDED,
                    Project::STATUS_PROGRESS2_REVIEWED,
                    Project::STATUS_PROGRESS2_REJECTED,
                    Project::STATUS_PROGRESS2_REJ_REVIEWED,
                    Project::STATUS_FINAL_ADDED,
                    Project::STATUS_FINAL_REJECTED,
                    Project::STATUS_FINAL_REJ_REVIEWED,
                    Project::STATUS_GRADED,
                ]);
            });

        if ($nonStudent) {
            $query->where(function ($q) {
                $q->whereNull('grant_type')->orWhere('grant_type', '!=', 'student');
            })->whereDoesntHave('program.grant', function ($q) {
                $q->where('category', 'student');
            });
        }

        $project = $query->first();

        if (!$project) {
            return null;
        }

        $project->lpi_id = $lpi->id;
        $project->save();

        return $project;
    }

    /** Progress stage ready for grading: report submitted, deadline passed. */
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
                Project::STATUS_PROGRESS_REJ_REVIEWED,
                Project::STATUS_PROGRESS2_ADDED,
                Project::STATUS_PROGRESS2_REVIEWED,
                Project::STATUS_PROGRESS2_REJECTED,
                Project::STATUS_FINAL_ADDED,
                Project::STATUS_FINAL_REJECTED,
                Project::STATUS_GRADED,
            ]);
        })->first();
    }

    /** Final stage ready for grading: final submitted, deadline passed. */
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

    private function setProgramDeadline(Project $project, string $field, $when): void
    {
        DB::table('programs')->where('id', $project->program_id)->update([$field => $when]);
    }

    /** Upload via the real LPI endpoint; tracks the created file for tearDown. */
    private function upload(User $lpi, Project $project, string $type, string $name = 'report.pdf', ?string $content = null)
    {
        // create() only *reports* a size (the stored file stays empty), so
        // tests that must distinguish files pass real PDF-magic content.
        $file = $content === null
            ? UploadedFile::fake()->create($name, 2, 'application/pdf')
            : UploadedFile::fake()->createWithContent($name, $content);

        $response = $this->actingAs($lpi)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => $file,
            'type' => $type,
        ]);

        // TestResponse::json() rethrows the request's stored exception on a
        // non-JSON body, so only decode successful (JSON) upload responses.
        if ($response->getStatusCode() === 200) {
            $path = $response->json('submission.file_path');
            if ($path) {
                $this->createdFiles[] = $path;
            }
        }

        return $response;
    }

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
            $payload['rejection_reason'] = 'LPI upload scenario rejection.';
        }

        return $payload;
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
        ];

        if ($decision === 'rejected') {
            $payload['rejection_type']   = 'report';
            $payload['rejection_reason'] = 'LPI upload scenario rejection.';
        }

        return $payload;
    }

    /** Reviewer rejects the progress stage (single-reviewer consensus). */
    private function rejectProgress(Project $project, User $reviewer): void
    {
        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);

        $this->actingAs($reviewer)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('rejected'));
    }

    private function adminSendToLpi(Project $project, User $admin, string $reportType = 'progress'): void
    {
        $this->actingAs($admin)->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => 'Please fix the issues and resubmit.',
            'report_type' => $reportType,
        ])->assertOk();
    }

    // —"— Group A: progress upload (version 1) —"——"——"——"——"——"——"——"——"——"——"——

    public function test_a01_progress_upload_stores_v1_and_serves_download()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['progress']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(2));

        $this->upload($lpi, $project, 'progress')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('type', 'progress')
            ->assertJsonPath('version', 1);

        $submission = $project->submissions()->where('type', 'progress')->first();
        $this->assertNotNull($submission);
        $this->assertSame(1, (int) $submission->version);
        $this->assertStringEndsWith('_progress.pdf', $submission->stored_filename);
        $this->assertFileExists(storage_path('app/' . $submission->file_path));

        $this->actingAs($lpi)->get(route('serveFile2', [
            'type'          => 'progress',
            'id'            => $project->id,
            'submission_id' => $submission->id,
        ]))->assertOk();
    }

    public function test_a02_progress_reupload_replaces_single_row_while_open()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['progress']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(2));

        // Deterministic filenames collide ({id}_progress.pdf), so distinguish
        // the two uploads by content: the replace must leave the NEW file.
        $firstPath = $this->upload($lpi, $project, 'progress', 'first.pdf', '%PDF-1.4 first-version')
            ->assertOk()
            ->json('submission.file_path');

        $this->upload($lpi, $project, 'progress', 'second.pdf', '%PDF-1.4 second-version')
            ->assertOk()
            ->assertJsonPath('version', 1);

        $rows = $project->submissions()->where('type', 'progress')->get();
        $this->assertCount(1, $rows, 'Re-uploading before any rejection must replace the single v1 row.');
        $storedPath = storage_path('app/' . $rows->first()->file_path);
        $this->assertFileExists($storedPath);
        $this->assertStringContainsString('second-version', (string) file_get_contents($storedPath), 'The stored file must be the newly uploaded one.');
        $this->assertSame($firstPath, $rows->first()->file_path, 'The deterministic v1 filename is reused.');
    }

    public function test_a03_progress_upload_locked_after_deadline()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['progress']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->subDay());

        $this->upload($lpi, $project, 'progress')->assertStatus(423);
        $this->assertSame(0, $project->submissions()->where('type', 'progress')->count());
    }

    public function test_a04_non_pdf_upload_rejected_by_validation()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['progress']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(2));

        $this->actingAs($lpi)->postJson(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain'),
            'type' => 'progress',
        ])->assertStatus(422)->assertJsonValidationErrors(['file']);

        $this->assertSame(0, $project->submissions()->where('type', 'progress')->count());
    }

    public function test_a05_non_owner_lpi_cannot_upload()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['progress']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(2));

        $otherUser = User::where('id', '!=', $lpi->id)->first();
        $project->lpi_id = $otherUser->id;
        $project->save();

        $this->upload($lpi, $project, 'progress')->assertStatus(403);
        $this->assertSame(0, $project->submissions()->where('type', 'progress')->count());
    }

    public function test_a06_reviewer_and_guest_cannot_upload()
    {
        $reviewer = $this->reviewer();
        $project  = $this->cleanProject(['progress']);
        if (!$reviewer || !$project) {
            $this->markTestSkipped('Missing reviewer or clean project in test database.');
        }

        // Guest first — actingAs() persists for the rest of the test.
        $this->post(route('progress.upload-submission', ['id' => $project->id]), [
            'type' => 'progress',
        ])->assertRedirect();

        $this->actingAs($reviewer)->post(route('progress.upload-submission', ['id' => $project->id]), [
            'file' => UploadedFile::fake()->create('report.pdf', 2, 'application/pdf'),
            'type' => 'progress',
        ])->assertStatus(403);
        $this->actingAs($reviewer)->get(route('progress.update', ['id' => $project->id]))
            ->assertStatus(403);

        $this->assertSame(0, $project->submissions()->where('type', 'progress')->count());
    }

    public function test_a08_lpi_sees_the_progress_upload_workspace()
    {
        $lpi = $this->activeLpi();
        $project = $this->cleanProject(['progress', 'progress2', 'final', 'readiness', 'ethical'], true);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(7));

        $html = $this->actingAs($lpi)
            ->get(route('progress.update', ['id' => $project->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Update Progress', $html);
        $this->assertStringContainsString('Progress Report', $html);
        $this->assertStringContainsString('Upload Progress Report', $html);
        $this->assertStringContainsString('data-type="progress"', $html);
    }

    public function test_a07_progress_form_save_follows_window_and_records_progress_added()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['progress']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(2));

        $this->actingAs($lpi)->postJson(route('progress.save', ['id' => $project->id]), [
            'narrative' => 'Mid-term progress narrative.',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertSame(
            Project::STATUS_PROGRESS_ADDED,
            $project->statusHistories()->latest('id')->first()->status
        );

        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->subDay());

        $this->actingAs($lpi)->postJson(route('progress.save', ['id' => $project->id]), [])
            ->assertStatus(423);
    }

    // —"— Group B: rejection → admin review → resubmission (progress) —"——"——"——

    public function test_b01_reviewer_rejection_completes_to_progress_rejected()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer in test database.');
        }

        $this->rejectProgress($project, $reviewer);

        $latest = $project->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_PROGRESS_REJECTED, $latest->status);
        $this->assertSame('report', $latest->metadata['rejection_type'] ?? null);

        $row = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->where('report_type', 'progress')
            ->first();
        $this->assertNotNull($row);
        $this->assertSame('rejected', $row->publish);
        $this->assertNotEmpty($row->rejection_reason);
    }

    public function test_b02_only_admin_can_review_rejection()
    {
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$project || !$reviewer) {
            $this->markTestSkipped('No progress-stage project or reviewer in test database.');
        }

        $this->rejectProgress($project, $reviewer);

        $other = $this->secondReviewer() ?? $reviewer;
        $this->actingAs($other)->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => 'Not allowed.',
            'report_type' => 'progress',
        ])->assertStatus(403);

        $this->assertFalse($project->hasStatus(Project::STATUS_PROGRESS_REJ_REVIEWED));
    }

    public function test_b03_admin_send_to_lpi_records_resubmission_request()
    {
        $project = $this->progressProject();
        $admin   = $this->activeUser('Admin');
        if (!$project || !$admin || !$this->reviewer()) {
            $this->markTestSkipped('Missing progress-stage project, reviewer or admin.');
        }

        $this->rejectProgress($project, $this->reviewer());
        $this->adminSendToLpi($project, $admin);
        $reviewedRejection = $project->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_PROGRESS_REJ_REVIEWED, $reviewedRejection->status);
        $this->assertSame('send_to_lpi', $reviewedRejection->metadata['action'] ?? null);

        $latest = $project->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_PROGRESS_REJ_REVIEWED, $latest->status);
        $this->assertSame('send_to_lpi', $latest->metadata['action'] ?? null);
        $this->assertNotEmpty($latest->metadata['admin_message'] ?? null);
    }

    public function test_b04_progress_v2_upload_unlocked_past_deadline_keeps_v1()
    {
        $lpi     = $this->activeLpi();
        $project = $this->progressProject();
        $admin   = $this->activeUser('Admin');
        if (!$lpi || !$project || !$admin || !$this->reviewer()) {
            $this->markTestSkipped('Missing fixtures for the resubmission chain.');
        }

        $project->lpi_id = $lpi->id;
        $project->save();

        $v1 = $project->submissions()->where('type', 'progress')->first();
        $this->assertNotNull($v1);
        $v1Path = storage_path('app/' . $v1->file_path);
        if (!is_file($v1Path)) {
            // The test database may predate the file on disk; materialise a v1
            // so the "v1 kept" assertion is meaningful.
            @mkdir(dirname($v1Path), 0755, true);
            file_put_contents($v1Path, '%PDF-1.4 test v1');
            $this->createdFiles[] = $v1->file_path;
        }

        $this->rejectProgress($project, $this->reviewer());
        $this->adminSendToLpi($project, $admin);

        // Deadline is long passed — the resubmission request must unlock it.
        $this->upload($lpi, $project, 'progress')
            ->assertOk()
            ->assertJsonPath('version', 2);

        $rows = $project->submissions()->where('type', 'progress')->orderBy('version')->get();
        $this->assertCount(2, $rows);
        $this->assertSame(1, (int) $rows[0]->version);
        $this->assertSame(2, (int) $rows[1]->version);
        $this->assertStringEndsWith('_progress_v2.pdf', $rows[1]->stored_filename);
        $this->assertFileExists($v1Path, 'v1 must be kept on disk for comparison.');
        $this->assertFileExists(storage_path('app/' . $rows[1]->file_path));

        $this->actingAs($lpi)->get(route('serveFile2', [
            'type'          => 'progress',
            'id'            => $project->id,
            'submission_id' => $rows[1]->id,
        ]))->assertOk();

        $lpiPage = $this->actingAs($lpi)->get(route('progress.update', ['id' => $project->id]));
        $lpiPage->assertOk()
            ->assertSee('Progress Resubmission Requested')
            ->assertSee('Re-Upload Progress Report (v2)')
            ->assertSee($rows[0]->stored_filename)
            ->assertSee($rows[1]->stored_filename);
        $this->assertStringContainsString('data-versioned="1"', $lpiPage->getContent());
    }

    public function test_b05_progress_v2_archives_and_resets_every_reviewer_set()
    {
        $lpi      = $this->activeLpi();
        $project  = $this->progressProject();
        $admin    = $this->activeUser('Admin');
        $reviewerA = $this->reviewer();
        $reviewerB = $this->secondReviewer();
        if (!$lpi || !$project || !$admin || !$reviewerA || !$reviewerB) {
            $this->markTestSkipped('Need LPI, admin and two reviewers in test database.');
        }

        $project->lpi_id = $lpi->id;
        $project->save();

        $this->syncReviewers($project, [$reviewerA->id, $reviewerB->id]);
        $this->claim($project, $reviewerA);
        $this->claim($project, $reviewerB);

        $this->actingAs($reviewerA)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('rejected'));
        $this->actingAs($reviewerB)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));

        $this->assertSame(
            Project::STATUS_PROGRESS_REJECTED,
            $project->statusHistories()->latest('id')->first()->status,
            'Any rejected set must win when the consensus completes.'
        );

        $this->adminSendToLpi($project, $admin);

        $this->upload($lpi, $project, 'progress')
            ->assertOk()
            ->assertJsonPath('version', 2);

        foreach ([$reviewerA, $reviewerB] as $reviewer) {
            $row = ProgressReportGrading::where('project_id', $project->id)
                ->where('user_id', $reviewer->id)
                ->where('report_type', 'progress')
                ->first();
            $this->assertSame('pending', $row->publish, "Reviewer {$reviewer->id} set must re-open blank for v2.");
            $this->assertNotNull($row->previous_grading, "Reviewer {$reviewer->id} set must be archived.");
        }

        $rowA = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewerA->id)->where('report_type', 'progress')->first();
        $rowB = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewerB->id)->where('report_type', 'progress')->first();
        $this->assertSame('rejected', $rowA->previous_grading['publish'] ?? null);
        $this->assertSame('accepted', $rowB->previous_grading['publish'] ?? null);
    }

    public function test_b06_progress_form_save_reopens_after_resubmission()
    {
        $lpi     = $this->activeLpi();
        $project = $this->progressProject();
        $admin   = $this->activeUser('Admin');
        if (!$lpi || !$project || !$admin || !$this->reviewer()) {
            $this->markTestSkipped('Missing fixtures for the resubmission chain.');
        }

        $project->lpi_id = $lpi->id;
        $project->save();

        $this->rejectProgress($project, $this->reviewer());
        $this->adminSendToLpi($project, $admin);

        // The progress deadline is passed, yet the resubmission re-opens the form.
        $this->actingAs($lpi)->postJson(route('progress.save', ['id' => $project->id]), [
            'narrative' => 'Resubmission narrative after admin review.',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertSame(
            Project::STATUS_PROGRESS_ADDED,
            $project->statusHistories()->latest('id')->first()->status,
            'progress_added clears the LPI resubmission bell state.'
        );
    }

    public function test_b07_resubmission_closes_after_next_grading()
    {
        $lpi      = $this->activeLpi();
        $project  = $this->progressProject();
        $admin    = $this->activeUser('Admin');
        $reviewer = $this->reviewer();
        if (!$lpi || !$project || !$admin || !$reviewer) {
            $this->markTestSkipped('Missing fixtures for the resubmission chain.');
        }

        $project->lpi_id = $lpi->id;
        $project->save();

        $this->rejectProgress($project, $reviewer);
        $this->actingAs($admin)
            ->get(route('projects.show', ['id' => $project->id]))
            ->assertOk()
            ->assertSee('Review Progress Rejection');

        $this->adminSendToLpi($project, $admin);

        $reviewedRejection = $project->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_PROGRESS_REJ_REVIEWED, $reviewedRejection->status);
        $this->assertSame('send_to_lpi', $reviewedRejection->metadata['action'] ?? null);

        $this->actingAs($lpi)
            ->get(route('progress.update', ['id' => $project->id]))
            ->assertOk()
            ->assertSee('Progress Resubmission Requested');

        $v1 = $project->submissions()->where('type', 'progress')->where('version', 1)->first();
        $this->assertNotNull($v1);
        $v1Path = storage_path('app/' . $v1->file_path);
        if (!is_file($v1Path)) {
            @mkdir(dirname($v1Path), 0755, true);
            file_put_contents($v1Path, '%PDF-1.4 original-v1');
            $this->createdFiles[] = $v1->file_path;
        }

        $this->upload($lpi, $project, 'progress')->assertOk()->assertJsonPath('version', 2);

        $this->actingAs($lpi)->postJson(route('progress.save', ['id' => $project->id]), [
            'narrative' => 'V2 progress resubmission saved for reviewer review.',
        ])->assertOk()->assertJsonPath('success', true);
        $this->assertSame(
            Project::STATUS_PROGRESS_ADDED,
            $project->statusHistories()->latest('id')->first()->status,
            'The LPI save records the v2 resubmission in the workflow history.'
        );

        $versions = $project->submissions()->where('type', 'progress')->orderBy('version')->get();
        $this->assertCount(2, $versions);
        $this->assertSame(1, (int) $versions[0]->version);
        $this->assertSame(2, (int) $versions[1]->version);
        $this->assertFileExists($v1Path);
        $this->assertFileExists(storage_path('app/' . $versions[1]->file_path));

        $this->actingAs($lpi)
            ->get(route('progress.update', ['id' => $project->id]))
            ->assertOk()
            ->assertSee($versions[0]->stored_filename)
            ->assertSee($versions[1]->stored_filename)
            ->assertSee('Re-Upload Progress Report (v2)');

        $this->actingAs($reviewer)
            ->get(route('projects.grading', ['id' => $project->id]))
            ->assertOk()
            ->assertSee($versions[1]->stored_filename);

        $this->actingAs($reviewer)
            ->post("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'));

        $this->assertSame(
            Project::STATUS_PROGRESS_REVIEWED,
            $project->statusHistories()->latest('id')->first()->status
        );

        // The resubmission window closed with the next grading — locked again.
        $this->upload($lpi, $project, 'progress')->assertStatus(423);
    }

    // —"— Group C: final report —"——"——"——"——"——"——"——"——"——"——"——"——"——"——

    public function test_c01_final_upload_stores_v1()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['final']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->addDays(2));

        $this->upload($lpi, $project, 'final')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('version', 1);

        $submission = $project->submissions()->where('type', 'final')->first();
        $this->assertNotNull($submission);
        $this->assertStringEndsWith('_final.pdf', $submission->stored_filename);
        $this->assertFileExists(storage_path('app/' . $submission->file_path));

        $this->actingAs($lpi)->get(route('serveFile2', [
            'type'          => 'final',
            'id'            => $project->id,
            'submission_id' => $submission->id,
        ]))->assertOk();
    }

    public function test_c02_final_upload_and_form_locked_after_deadline()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['final']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->subDay());

        $this->upload($lpi, $project, 'final')->assertStatus(423);

        $this->actingAs($lpi)->postJson(route('progress.save-final', ['id' => $project->id]), [
            'submission_notes' => 'Should be refused.',
        ])->assertStatus(423);

        $this->assertSame(0, $project->submissions()->where('type', 'final')->count());
    }

    public function test_c03_final_rejection_chain_reopens_v2_and_resets_grading()
    {
        $lpi      = $this->activeLpi();
        $project  = $this->finalProject();
        $admin    = $this->activeUser('Admin');
        $reviewer = $this->reviewer();
        if (!$lpi || !$project || !$admin || !$reviewer) {
            $this->markTestSkipped('Missing fixtures for the final rejection chain.');
        }

        $project->lpi_id = $lpi->id;
        $project->save();

        $v1 = $project->submissions()->where('type', 'final')->first();
        $v1Path = storage_path('app/' . $v1->file_path);

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);
        $this->actingAs($reviewer)
            ->post("/grading/{$project->id}/save-final-grade", $this->finalPayload('rejected'));

        $this->assertSame(
            Project::STATUS_FINAL_REJECTED,
            $project->statusHistories()->latest('id')->first()->status
        );

        $this->adminSendToLpi($project, $admin, 'final');

        $this->upload($lpi, $project, 'final')
            ->assertOk()
            ->assertJsonPath('version', 2);

        $rows = $project->submissions()->where('type', 'final')->orderBy('version')->get();
        $this->assertCount(2, $rows);
        $this->assertStringEndsWith('_final_v2.pdf', $rows[1]->stored_filename);
        $this->assertFileExists($v1Path, 'v1 must be kept on disk for comparison.');
        $this->assertFileExists(storage_path('app/' . $rows[1]->file_path));

        $grading = FinalReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->first();
        $this->assertNotNull($grading);
        $this->assertSame('pending', $grading->publish, 'The final set must re-open blank for v2.');
        $this->assertNotNull($grading->previous_grading);
        $this->assertSame('rejected', $grading->previous_grading['publish'] ?? null);
    }

    public function test_c04_final_form_save_records_final_added()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['final', 'readiness']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->addDays(2));

        $this->actingAs($lpi)->postJson(route('progress.save-final', ['id' => $project->id]), [
            'submission_notes' => 'No file uploaded yet.',
        ])->assertStatus(422);

        $this->upload($lpi, $project, 'final')->assertOk();

        $this->actingAs($lpi)->postJson(route('progress.save-final', ['id' => $project->id]), [
            'submission_notes' => 'Final report submitted.',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertSame(
            Project::STATUS_FINAL_ADDED,
            $project->statusHistories()->latest('id')->first()->status
        );
    }

    // —"— Group D: ethical approvals —"——"——"——"——"——"——"——"——"——"——"——"——

    public function test_d01_ethical_upload_ignores_deadlines()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['ethical']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->subDay());
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->subDay());

        $this->upload($lpi, $project, 'ethical', 'ethics.pdf')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('version', 1);

        $submission = $project->submissions()->where('type', 'ethical')->first();
        $this->assertNotNull($submission);
        $this->assertStringEndsWith('_ethical_1.pdf', $submission->stored_filename);
        $this->assertFileExists(storage_path('app/' . $submission->file_path));
    }

    public function test_d02_ethical_uploads_append_numbered_files()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['ethical']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }

        $this->upload($lpi, $project, 'ethical', 'ethics-1.pdf')->assertOk();
        $this->upload($lpi, $project, 'ethical', 'ethics-2.pdf')->assertOk();

        $rows = $project->submissions()->where('type', 'ethical')->orderBy('id')->get();
        $this->assertCount(2, $rows, 'Ethical approvals must append, never replace.');
        $this->assertStringEndsWith('_ethical_1.pdf', $rows[0]->stored_filename);
        $this->assertStringEndsWith('_ethical_2.pdf', $rows[1]->stored_filename);
        $this->assertFileExists(storage_path('app/' . $rows[0]->file_path));
        $this->assertFileExists(storage_path('app/' . $rows[1]->file_path));
    }

    public function test_d03_ethical_rejection_holds_report_upload_and_routes_to_admin()
    {
        $lpi     = $this->activeLpi();
        $admin   = $this->activeUser('Admin');
        $reviewer = $this->reviewer();
        $project = $this->cleanProject(['progress']);
        if (!$lpi || !$admin || !$reviewer || !$project) {
            $this->markTestSkipped('Missing LPI, admin, reviewer or clean project.');
        }
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(2));

        $project->recordStatus(Project::STATUS_PROGRESS_REJECTED, [
            'triggered_by'   => 'progress-grade',
            'rejection_type' => 'ethical',
            'comment'        => 'Ethics documentation issue.',
        ], $reviewer->id);

        // Window is open, yet the ethical rejection holds the REPORT upload
        // (the LPI re-sends the ethical approval, never the progress report).
        $this->upload($lpi, $project, 'progress')->assertStatus(423);

        // ...but the ethical APPROVAL upload itself is allowed.
        $this->upload($lpi, $project, 'ethical', 'ethics.pdf')->assertOk();

        // The admin review step applies to ethical rejections too — the admin
        // forwards the request to the LPI (no 422).
        $this->assertTrue($project->fresh()->hasUnreviewedRejection('progress'));
        $this->actingAs($admin)->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => 'Please re-send the ethical approval.',
            'report_type' => 'progress',
        ])->assertOk()->assertJsonPath('success', true);
    }

    // —"— Group E: Progress Report 2 & readiness gates —"——"——"——"——"——"——

    public function test_e01_progress2_upload_requires_extended_progress()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['progress2']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        DB::table('projects')->where('id', $project->id)->update(['extended_progress' => 0]);
        $this->setProgramDeadline($project, 'prog_rpt2_deadline', now()->addDays(2));

        $this->upload($lpi, $project, 'progress2')->assertStatus(403);
        $this->assertSame(0, $project->submissions()->where('type', 'progress2')->count());
    }

    public function test_e02_progress2_upload_records_progress2_added()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['progress2']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        DB::table('projects')->where('id', $project->id)->update(['extended_progress' => 1]);
        $this->setProgramDeadline($project, 'prog_rpt2_deadline', now()->addDays(2));

        $this->upload($lpi, $project, 'progress2')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('version', 1);

        $submission = $project->submissions()->where('type', 'progress2')->first();
        $this->assertNotNull($submission);
        $this->assertStringEndsWith('_progress2.pdf', $submission->stored_filename);
        $this->assertFileExists(storage_path('app/' . $submission->file_path));

        $this->assertSame(
            Project::STATUS_PROGRESS2_ADDED,
            $project->statusHistories()->latest('id')->first()->status
        );
    }

    public function test_e03_readiness_follows_final_window()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['readiness']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        // Readiness is a final-phase deliverable — gated by the FINAL period
        // (deadline + the final rejection hold), not the PR2 window.
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->addDays(2));

        $this->upload($lpi, $project, 'readiness')
            ->assertOk()
            ->assertJsonPath('version', 1);

        $submission = $project->submissions()->where('type', 'readiness')->first();
        $this->assertNotNull($submission);
        $this->assertStringEndsWith('_readiness.pdf', $submission->stored_filename);

        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->subDay());
        $this->upload($lpi, $project, 'readiness')->assertStatus(423);
    }

    // —"— Group F: project data freeze —"——"——"——"——"——"——"——"——"——"——"———

    public function test_f01_project_data_frozen_after_final_deadline()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject([]);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }

        $outcomes = [
            'outcomes' => [
                ['type' => 'journal_q1', 'detail' => '10.1000/lrts-scenario'],
            ],
        ];

        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->subDay());
        $this->actingAs($lpi)->postJson(route('progress.save-outcomes', ['id' => $project->id]), $outcomes)
            ->assertStatus(423);

        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->addDays(2));
        $this->actingAs($lpi)->postJson(route('progress.save-outcomes', ['id' => $project->id]), $outcomes)
            ->assertOk();
    }

    // —— Group G: upload & outcomes integrity (bug-fix regressions) ————————

    /** G-01 A readiness-only final submit must NOT claim the final report was added. */
    public function test_g01_readiness_only_final_submit_does_not_record_final_added()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject(['final', 'readiness']);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->addDays(2));
        // Readiness is deadline-gated by the PR2 window, not the final one.
        $this->setProgramDeadline($project, 'prog_rpt2_deadline', now()->addDays(2));

        $this->upload($lpi, $project, 'readiness')->assertOk();

        $this->actingAs($lpi)->postJson(route('progress.save-final', ['id' => $project->id]), [
            'submission_notes' => 'Readiness only.',
        ])->assertOk();

        $this->assertFalse(
            $project->statusHistories()->where('status', Project::STATUS_FINAL_ADDED)->exists(),
            'A readiness-only submit must not record final_added (no final report exists).'
        );
    }

    /** G-02 The upload's download_url must target the exact version uploaded (v2, not stale v1). */
    public function test_g02_upload_download_url_targets_the_uploaded_version()
    {
        $lpi      = $this->activeLpi();
        $project  = $this->progressProject();
        $admin    = $this->activeUser('Admin');
        $reviewer = $this->reviewer();
        if (!$lpi || !$project || !$admin || !$reviewer) {
            $this->markTestSkipped('Missing fixtures for the resubmission chain.');
        }

        $project->lpi_id = $lpi->id;
        $project->save();

        $this->rejectProgress($project, $reviewer);
        $this->adminSendToLpi($project, $admin);

        $res = $this->upload($lpi, $project, 'progress', 'v2.pdf', '%PDF-1.4 v2-marker-content');
        $res->assertOk()->assertJsonPath('version', 2);

        $downloadUrl = $res->json('submission.download_url');
        $this->assertNotNull($downloadUrl, 'The response must expose submission.download_url.');
        $this->assertStringContainsString('submission_id=', $downloadUrl, 'download_url must pin the exact submission.');

        $served = $this->actingAs($lpi)->get(route('serveFile2', [
            'type'          => 'progress',
            'id'            => $project->id,
            'submission_id' => $res->json('submission.id'),
        ]));
        $served->assertOk();

        // BinaryFileResponse::getContent() is empty — read the stored file.
        $stored = storage_path('app/' . $res->json('submission.file_path'));
        $this->assertFileExists($stored);
        $this->assertStringContainsString('v2-marker-content', (string) file_get_contents($stored), 'The stored file must be the v2 content.');
    }

    /** G-03 saveOutcomes must upsert only — never wipe rows or reset verification. */
    public function test_g03_save_outcomes_preserves_verification_and_existing_rows()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject([]);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->addDays(2));

        $scholarly = $project->outcomes()->create([
            'user_id'                 => $lpi->id,
            'type'                    => 'journal_q1',
            'identifier'              => '10.1000/existing',
            'verifcation_by_system'   => 'verified',
            'verifcation_by_reviewer' => 'verified',
            'score'                   => 8,
        ]);
        $contribution = $project->outcomes()->create([
            'user_id'                 => $lpi->id,
            'type'                    => 'ip_disclosure',
            'identifier'              => 'Our IP',
            'verifcation_by_system'   => 'pending',
            'verifcation_by_reviewer' => 'verified',
            'score'                   => 4,
        ]);

        // Payload shapes that used to wipe data: a scholarly detail that does
        // not match the existing row, and a contribution WITHOUT `submitted`
        // (which defaulted to 'No' and skipped the re-insert).
        $this->actingAs($lpi)->postJson(route('progress.save-outcomes', ['id' => $project->id]), [
            'outcomes' => [
                ['type' => 'journal_q1', 'detail' => '10.1000/brand-new'],
                ['type' => 'ip_disclosure'],
            ],
        ])->assertOk();

        $scholarly   = $scholarly->fresh();
        $contribution = $contribution->fresh();

        $this->assertNotNull($scholarly, 'Existing verified scholarly row must survive.');
        $this->assertSame('verified', $scholarly->verifcation_by_system);
        $this->assertSame('verified', $scholarly->verifcation_by_reviewer);
        $this->assertSame(8, (int) $scholarly->score);

        $this->assertNotNull($contribution, 'Contribution row must not be wiped.');
        $this->assertSame('verified', $contribution->verifcation_by_reviewer);

        $this->assertSame(
            1,
            $project->outcomes()->where('type', 'journal_q1')->where('identifier', '10.1000/brand-new')->count(),
            'A new scholarly entry is added (additive upsert).'
        );
    }

    /** G-04 A text-only form save must not reset the reviewer's grading. */
    public function test_g04_text_only_progress_save_does_not_reset_grading()
    {
        $lpi      = $this->activeLpi();
        $project  = $this->progressProject();
        $reviewer = $this->reviewer();
        if (!$lpi || !$project || !$reviewer) {
            $this->markTestSkipped('Missing LPI, progress-stage project or reviewer.');
        }

        $project->lpi_id = $lpi->id;
        $project->save();

        $this->syncReviewers($project, [$reviewer->id]);
        $this->claim($project, $reviewer);
        $this->actingAs($reviewer)
            ->postJson("/grading/{$project->id}/save-progress-grade", $this->progressPayload('accepted'))
            ->assertOk();

        $row = ProgressReportGrading::where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->where('report_type', 'progress')
            ->first();
        $this->assertSame('accepted', $row->publish);

        // Re-open the LPI window and save the form WITHOUT a new file.
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(2));
        $this->actingAs($lpi)->postJson(route('progress.save', ['id' => $project->id]), [
            'narrative' => 'Text only, no new PDF.',
        ])->assertOk();

        $this->assertSame(
            'accepted',
            $row->fresh()->publish,
            'A text-only form save must not wipe an in-progress grading set.'
        );
    }

    // —— Group H: full LPI submission lifecycle across the two periods ————————

    /**
     * H-01 The LPI works inside BOTH submission periods, then both close.
     *
     * Requirement under test:
     *  - the PROGRESS report is bound to the progress period (prog_rpt_deadline);
     *  - OUTCOMES, STUDENTS, readiness, ethical approval and the FINAL report
     *    are bound to the FINAL period (final_rpt_deadline).
     *
     * While the periods are open the LPI may upload, REPLACE the older
     * progress/final reports and edit outcomes/students; once each period
     * closes, its own artefacts (and the data) are locked.
     *
     * Ethical approvals stay exempt from the deadline lock by design — the
     * ethical-rejection re-send always happens after the final period — so
     * they are asserted only for the "allowed" side here (see D-01).
     */
    public function test_h01_lpi_full_submission_lifecycle_within_both_periods()
    {
        $lpi     = $this->activeLpi();
        $project = $this->cleanProject([]);
        if (!$lpi || !$project) {
            $this->markTestSkipped('Missing LPI or clean project in test database.');
        }

        // Keep the SIS lookup off the real network (it is best-effort anyway).
        config(['services.student_api.use_test_response' => true]);

        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->addDays(3));
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->addDays(10));

        // ─────────── Phase A — inside both periods ───────────────────────────

        // 1. Progress report (progress period).
        $this->upload($lpi, $project, 'progress', 'progress-v1.pdf', '%PDF-1.4 progress-version-1')
            ->assertOk()->assertJsonPath('version', 1);
        $this->assertSame(1, $project->submissions()->where('type', 'progress')->count());

        // 2. Replace the older progress report (still inside the period).
        $this->upload($lpi, $project, 'progress', 'progress-v2.pdf', '%PDF-1.4 progress-version-REPLACED')
            ->assertOk()->assertJsonPath('version', 1);
        $rows = $project->submissions()->where('type', 'progress')->get();
        $this->assertCount(1, $rows, 'Re-upload replaces, never piles up.');
        $this->assertStringContainsString(
            'progress-version-REPLACED',
            (string) file_get_contents(storage_path('app/' . $rows->first()->file_path)),
            'The replacement content must be what is stored.'
        );

        // 3. Readiness, ethical approval and the final report (final period).
        $this->upload($lpi, $project, 'readiness', 'readiness.pdf', '%PDF-1.4 readiness-1')->assertOk();
        $this->upload($lpi, $project, 'ethical', 'ethics.pdf', '%PDF-1.4 ethics-1')->assertOk();
        $this->upload($lpi, $project, 'final', 'final-v1.pdf', '%PDF-1.4 final-version-1')
            ->assertOk()->assertJsonPath('version', 1);

        // 4. Replace the older final report (still inside the period).
        $this->upload($lpi, $project, 'final', 'final-v2.pdf', '%PDF-1.4 final-version-REPLACED')
            ->assertOk()->assertJsonPath('version', 1);
        $finalRows = $project->submissions()->where('type', 'final')->get();
        $this->assertCount(1, $finalRows, 'Re-upload replaces the final report.');
        $this->assertStringContainsString(
            'final-version-REPLACED',
            (string) file_get_contents(storage_path('app/' . $finalRows->first()->file_path))
        );

        // 5. Outcomes + students are editable while the FINAL period is open.
        $outcome = $project->outcomes()->where('type', 'ip_disclosure')->first();
        if (!$outcome) {
            $this->actingAs($lpi)->postJson(route('progress.save-single-outcome', ['id' => $project->id]), [
                'type'   => 'ip_disclosure',
                'detail' => 'Filed patent during the open window.',
            ])->assertOk();
            $outcome = $project->outcomes()->where('type', 'ip_disclosure')->first();
        }
        $this->assertNotNull($outcome, 'An outcome can be created while the final period is open.');

        $studentCountBefore = $project->students()->count();
        $this->actingAs($lpi)->postJson(route('progress.save-single-student', ['id' => $project->id]), [
            'type'   => 'UG',
            'std_id' => 'STD-OPEN-1',
            'days'   => 30,
        ])->assertOk();
        $this->assertSame($studentCountBefore + 1, $project->students()->count());

        // Re-saving outcomes must not wipe what exists (additive upsert).
        $outcomeId = $outcome->id;
        $this->actingAs($lpi)->postJson(route('progress.save-outcomes', ['id' => $project->id]), [
            'outcomes' => [
                ['type' => 'ip_disclosure', 'detail' => 'Filed patent during the open window.', 'submitted' => 'Yes'],
            ],
        ])->assertOk();
        $this->assertNotNull($project->outcomes()->find($outcomeId), 'Re-saving must not wipe the row.');

        // ─────────── Phase B — both periods close ────────────────────────────
        $this->setProgramDeadline($project, 'prog_rpt_deadline', now()->subDay());
        $this->setProgramDeadline($project, 'final_rpt_deadline', now()->subDay());

        $progressPath = $project->submissions()->where('type', 'progress')->first()->file_path;
        $finalPath    = $project->submissions()->where('type', 'final')->first()->file_path;

        // Progress period closed → the progress report is locked (incl. replace).
        $this->upload($lpi, $project, 'progress', 'late.pdf', '%PDF-1.4 progress-late')
            ->assertStatus(423);
        $this->assertSame($progressPath, $project->submissions()->where('type', 'progress')->first()->file_path,
            'A refused upload must not touch the stored progress report.');

        // Final period closed → final + readiness are locked (incl. replace).
        $this->upload($lpi, $project, 'final', 'late.pdf', '%PDF-1.4 final-late')->assertStatus(423);
        $this->upload($lpi, $project, 'readiness', 'late.pdf', '%PDF-1.4 readiness-late')->assertStatus(423);
        $this->assertSame($finalPath, $project->submissions()->where('type', 'final')->first()->file_path,
            'A refused upload must not touch the stored final report.');

        // Data (outcomes / students) frozen with the final period.
        $this->actingAs($lpi)->postJson(route('progress.save-single-outcome', ['id' => $project->id]), [
            'type'   => 'ip_disclosure',
            'detail' => 'Should be refused.',
        ])->assertStatus(423);

        $this->actingAs($lpi)->postJson(route('progress.save-outcomes', ['id' => $project->id]), [
            'outcomes' => [
                ['type' => 'ip_disclosure', 'detail' => 'Should be refused.', 'submitted' => 'Yes'],
            ],
        ])->assertStatus(423);

        $this->actingAs($lpi)->postJson(route('progress.save-single-student', ['id' => $project->id]), [
            'type'   => 'UG',
            'std_id' => 'STD-LATE-1',
            'days'   => 10,
        ])->assertStatus(423);
        $this->assertSame($studentCountBefore + 1, $project->students()->count(),
            'A frozen student save must not add a row.');
    }
}
