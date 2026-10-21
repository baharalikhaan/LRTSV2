<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminUploadProgressV2Test extends TestCase
{
    use DatabaseTransactions;

    /** @var string[] */
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            $fullPath = storage_path('app/' . $path);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        parent::tearDown();
    }

    public function test_admin_upload_after_progress_rejection_saves_v2_and_preserves_v1()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        $project = Project::whereNotNull('program_id')
            ->whereDoesntHave('submissions', function ($query) {
                $query->where('type', 'progress');
            })
            ->whereDoesntHave('statusHistories', function ($query) {
                $query->whereIn('status', [
                    Project::STATUS_PROGRESS_REJECTED,
                    Project::STATUS_PROGRESS_REJ_REVIEWED,
                ]);
            })
            ->first();

        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a project without an existing progress report or rejection.');
        }

        // Use a unique storage name so this test cannot overwrite an existing file.
        $project->old_project_id = 'ADMIN-V2-TEST-' . $project->id;
        $project->save();
        $project->load(['program.cycle', 'program.grant']);

        $directory = $project->getStorageDir('progress_reports');
        $oldId = $project->getFileSafeOldProjectId();
        $v1Filename = $oldId . '_progress.pdf';
        $v1Path = $directory . '/' . $v1Filename;
        $v2Path = $directory . '/' . $oldId . '_progress_v2.pdf';

        $fullV1Path = storage_path('app/' . $v1Path);
        if (!is_dir(dirname($fullV1Path))) {
            mkdir(dirname($fullV1Path), 0755, true);
        }
        file_put_contents($fullV1Path, '%PDF-test-v1');
        $this->createdFiles[] = $v1Path;

        $v1 = $project->submissions()->create([
            'type' => 'progress',
            'file_path' => $v1Path,
            'stored_filename' => $v1Filename,
            'original_filename' => 'original-v1.pdf',
            'version' => 1,
            'user_id' => $admin->id,
            'submitted' => true,
            'submitted_at' => now(),
        ]);
        $project->recordStatus(Project::STATUS_PROGRESS_REJ_REVIEWED, ['action' => 'send_to_lpi'], $admin->id);

        $response = $this->actingAs($admin)->post(route('admin-upload.upload'), [
            'project_id' => $project->id,
            'progress' => UploadedFile::fake()->create('admin-progress-v2.PDF', 20, 'application/pdf'),
        ]);
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['message' => '1 report(s) uploaded successfully. Progress Report saved as v2; v1 was preserved.']);

        $latestStatus = $project->statusHistories()->latest('id')->first();
        $this->assertSame(Project::STATUS_PROGRESS_ADDED, $latestStatus->status);
        $this->assertSame('admin_upload', $latestStatus->metadata['triggered_by'] ?? null);

        $submissions = $project->submissions()->where('type', 'progress')->orderBy('version')->get();
        $this->assertCount(2, $submissions);
        $this->assertSame($v1->id, $submissions[0]->id);
        $this->assertSame(1, (int) $submissions[0]->version);
        $this->assertSame(2, (int) $submissions[1]->version);
        $this->assertSame($oldId . '_progress_v2.pdf', $submissions[1]->stored_filename);
        $this->assertSame($v2Path, $submissions[1]->file_path);
        $this->assertSame($admin->id, (int) $submissions[1]->user_id);
        $this->assertFileExists($fullV1Path);
        $this->assertSame('%PDF-test-v1', file_get_contents($fullV1Path));
        $this->assertFileExists(storage_path('app/' . $v2Path));
        $this->createdFiles[] = $v2Path;

        $firstV2Id = $submissions[1]->id;
        $this->actingAs($admin)->post(route('admin-upload.upload'), [
            'project_id' => $project->id,
            'progress' => UploadedFile::fake()->create('admin-progress-v2-replacement.pdf', 20, 'application/pdf'),
        ])->assertOk()->assertJsonPath('success', true);

        $submissions = $project->submissions()->where('type', 'progress')->orderBy('version')->get();
        $this->assertCount(2, $submissions, 'Replacing v2 must not create a v3 row.');
        $this->assertSame($v1->id, $submissions[0]->id, 'The original v1 record must remain untouched.');
        $this->assertNotSame($firstV2Id, $submissions[1]->id, 'The previous v2 row is replaced.');
        $this->assertSame(2, (int) $submissions[1]->version);
        $this->assertSame('%PDF-test-v1', file_get_contents($fullV1Path));
        $this->assertFileExists(storage_path('app/' . $v2Path));

        $project->extended_progress = true;
        $project->save();
        foreach (['final' => 'final_reports', 'progress2' => 'progress_reports'] as $type => $folder) {
            foreach ([1, 2] as $version) {
                $filename = $oldId . '_' . $type . ($version === 2 ? '_v2' : '') . '.pdf';
                $project->submissions()->create([
                    'type' => $type,
                    'file_path' => $project->getStorageDir($folder) . '/' . $filename,
                    'stored_filename' => $filename,
                    'original_filename' => $filename,
                    'version' => $version,
                    'user_id' => $admin->id,
                    'submitted' => true,
                    'submitted_at' => now(),
                ]);
            }
        }

        $listQuery = [
            'search' => ['value' => $oldId],
            'start' => 0,
            'length' => 10,
        ];
        $listResponse = $this->actingAs($admin)->getJson(
            route('admin-upload.ajax-list') . '?' . http_build_query($listQuery)
        );
        $listResponse->assertOk();
        $row = collect($listResponse->json('data'))->firstWhere('id', $project->id);
        $this->assertNotNull($row);
        $this->assertStringContainsString('Uploaded v1,v2', $row['progress']);
        $this->assertStringNotContainsString('Rejected — next upload will be v2', $row['progress']);
        $this->assertStringContainsString('Uploaded v1,v2', $row['progress2']);
        $this->assertStringContainsString('Uploaded v1,v2', $row['final']);
        $this->assertStringNotContainsString('Available:', $row['progress']);
    }

    public function test_normal_admin_progress_upload_still_creates_v1()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        $project = Project::whereNotNull('program_id')
            ->whereDoesntHave('submissions', function ($query) {
                $query->where('type', 'progress');
            })
            ->whereDoesntHave('statusHistories', function ($query) {
                $query->whereIn('status', [
                    Project::STATUS_PROGRESS_REJECTED,
                    Project::STATUS_PROGRESS_REJ_REVIEWED,
                ]);
            })
            ->first();

        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a project without a progress report or active rejection.');
        }

        $project->old_project_id = 'ADMIN-V1-TEST-' . $project->id;
        $project->save();
        $project->load(['program.cycle', 'program.grant']);

        $response = $this->actingAs($admin)->post(route('admin-upload.upload'), [
            'project_id' => $project->id,
            'progress' => UploadedFile::fake()->create('admin-progress-v1.pdf', 20, 'application/pdf'),
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $submission = $project->submissions()->where('type', 'progress')->firstOrFail();
        $this->assertSame(1, (int) $submission->version);
        $this->assertSame($project->getFileSafeOldProjectId() . '_progress.pdf', $submission->stored_filename);
        $this->assertFileExists(storage_path('app/' . $submission->file_path));
        $this->createdFiles[] = $submission->file_path;
    }

    public function test_rejected_progress_filter_shows_v2_notice_only_for_rejected_projects()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        $project = Project::whereNotNull('program_id')
            ->whereDoesntHave('submissions', function ($query) {
                $query->where('type', 'progress');
            })
            ->whereDoesntHave('statusHistories', function ($query) {
                $query->whereIn('status', [
                    Project::STATUS_PROGRESS_REJECTED,
                    Project::STATUS_PROGRESS_REJ_REVIEWED,
                ]);
            })
            ->first();

        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a project without a progress report or active rejection.');
        }

        $project->old_project_id = 'ADMIN-REJECT-FILTER-' . $project->id;
        $project->save();
        $this->actingAs($admin)->get(route('admin-upload.index'))
            ->assertOk()
            ->assertSee('Rejected reports:')
            ->assertSee('data-rejected="progress"', false);
        $project->recordStatus(Project::STATUS_PROGRESS_REJECTED, [], $admin->id);

        $query = [
            'search' => ['value' => $project->old_project_id],
            'start' => 0,
            'length' => 10,
            'rejected_progress' => 1,
        ];
        $ajaxUrl = route('admin-upload.ajax-list') . '?' . http_build_query($query);
        $response = $this->actingAs($admin)->getJson($ajaxUrl);
        $response->assertOk();

        $row = collect($response->json('data'))->firstWhere('id', $project->id);
        $this->assertNotNull($row, 'The rejected project should appear when the rejected filter is active.');
        $this->assertStringContainsString('Rejected — next upload will be v2', $row['progress']);
        $this->assertStringContainsString('Progress v2', $row['action']);

        $project->recordStatus(Project::STATUS_PROGRESS_ADDED, ['triggered_by' => 'progress'], $admin->id);
        $response = $this->actingAs($admin)->getJson($ajaxUrl);
        $response->assertOk();
        $this->assertNull(
            collect($response->json('data'))->firstWhere('id', $project->id),
            'Once the report is resubmitted, it should no longer match the rejected-only filter.'
        );
    }

    public function test_upload_without_a_received_file_returns_an_error_instead_of_false_success()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        $project = Project::whereNotNull('program_id')->first();
        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a project.');
        }

        $submissionCount = $project->submissions()->count();
        $response = $this->actingAs($admin)->post(route('admin-upload.upload'), [
            'project_id' => $project->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonFragment(['message' => 'The server did not receive a file. Check that it is a PDF and within the server upload-size limit, then try again.']);
        $this->assertSame($submissionCount, $project->submissions()->count());
    }
}
