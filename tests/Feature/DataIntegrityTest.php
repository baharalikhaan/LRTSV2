<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Data integrity: saving then retrieving records, and the file-safe project id
 * rules used for every stored document (proposals, reports).
 */
class DataIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    /** A created project is persisted and read back unchanged. */
    public function test_project_save_and_retrieve_round_trip()
    {
        $project = Project::create([
            'old_project_id' => 'TEST-ROUNDTRIP-1',
            'title'          => 'Round Trip Project',
            'email'          => 'roundtrip@example.com',
            'author'         => 'Dr. Round Trip',
        ]);

        $this->assertDatabaseHas('projects', [
            'old_project_id' => 'TEST-ROUNDTRIP-1',
            'title'          => 'Round Trip Project',
        ]);

        $found = Project::where('old_project_id', 'TEST-ROUNDTRIP-1')->first();
        $this->assertNotNull($found);
        $this->assertSame('Round Trip Project', $found->title);
        $this->assertSame('roundtrip@example.com', $found->email);
    }

    /** A project id containing "/" is normalised for filenames. */
    public function test_file_safe_project_id_strips_slashes()
    {
        $project = new Project(['old_project_id' => 'QUIKT-CENG-26/27-1014']);

        $this->assertSame('QUIKT-CENG-2627-1014', $project->getFileSafeOldProjectId());

        // The generated document NAME (last path segment) must be slash-free.
        $proposalName = basename($project->getStorageFilename('proposal'));
        $this->assertSame('QUIKT-CENG-2627-1014.pdf', $proposalName);
        $this->assertStringNotContainsString('/', $proposalName);
    }

    /** Report documents keep their "<id>_<type>.pdf" naming and are slash-free. */
    public function test_report_filename_is_slash_free()
    {
        $project = new Project(['old_project_id' => 'QUCG-QU Health-25/26-692']);

        foreach (['progress', 'progress2', 'readiness', 'final', 'ethical'] as $type) {
            $name = basename($project->getStorageFilename($type));
            $this->assertStringNotContainsString('/', $name, "{$type} filename must not contain a slash");
            $this->assertStringContainsString('QUCG-QU Health-2526-692', $name);
        }
    }

    /** Updating a record persists the change (save path). */
    public function test_project_update_persists()
    {
        $project = Project::create([
            'old_project_id' => 'TEST-UPDATE-1',
            'title'          => 'Before',
        ]);

        $project->update(['title' => 'After']);
        $this->assertSame('After', Project::find($project->id)->title);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'After']);
    }
}
