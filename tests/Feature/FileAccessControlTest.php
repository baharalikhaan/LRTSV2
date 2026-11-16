<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * serveFile2 must not hand out another project's documents.
 *
 * Previously any authenticated user could read any file by changing the id;
 * now an admin may read everything, while an LPI / reviewer only sees the
 * files of a project they own or are assigned to.
 */
class FileAccessControlTest extends TestCase
{
    use DatabaseTransactions;

    private function projectWithProposal(): ?Project
    {
        return Project::with('program.cycle', 'program.grant')
            ->whereNotNull('proposal_filename')
            ->where('proposal_filename', '!=', '')
            ->whereNotNull('lpi_id')
            ->whereHas('program.cycle')
            ->get()
            ->first(function (Project $project) {
                $path = storage_path('app/' . $project->getStorageDir('proposals') . '/' . $project->proposal_filename);

                return file_exists($path);
            });
    }

    public function test_the_owner_lpi_can_open_the_proposal()
    {
        $project = $this->projectWithProposal();
        if (!$project) {
            $this->markTestSkipped('No project with a proposal file on disk.');
        }

        $this->actingAs(User::find($project->lpi_id))
            ->get(route('serveFile2', ['type' => 'proposal', 'id' => $project->id]))
            ->assertOk();
    }

    public function test_another_lpi_cannot_open_the_proposal()
    {
        $project = $this->projectWithProposal();
        if (!$project) {
            $this->markTestSkipped('No project with a proposal file on disk.');
        }

        $outsider = User::where('type', 'LPI')
            ->where('id', '!=', $project->lpi_id)
            ->first();

        if (!$outsider) {
            $this->markTestSkipped('No second LPI in the test database.');
        }

        $this->actingAs($outsider)
            ->get(route('serveFile2', ['type' => 'proposal', 'id' => $project->id]))
            ->assertForbidden();
    }

    public function test_an_assigned_reviewer_can_open_the_proposal()
    {
        $project = Project::with('program.cycle', 'program.grant')
            ->has('reviewers')
            ->get()
            ->first(function (Project $project) {
                $path = storage_path('app/' . $project->getStorageDir('proposals') . '/' . $project->proposal_filename);

                return $project->proposal_filename && file_exists($path);
            });

        if (!$project) {
            $this->markTestSkipped('No assigned project with a proposal file on disk.');
        }

        $this->actingAs($project->reviewers->first())
            ->get(route('serveFile2', ['type' => 'proposal', 'id' => $project->id]))
            ->assertOk();
    }

    public function test_an_admin_can_open_any_proposal()
    {
        $project = $this->projectWithProposal();
        $admin = User::where('type', 'Admin')->where('is_active', true)->first();

        if (!$project || !$admin) {
            $this->markTestSkipped('Needs an admin and a project with a proposal file.');
        }

        $this->actingAs($admin)
            ->get(route('serveFile2', ['type' => 'proposal', 'id' => $project->id]))
            ->assertOk();
    }

    public function test_a_submission_id_cannot_be_used_to_cross_projects()
    {
        $project = Project::with('program.cycle', 'program.grant')
            ->has('reviewers')
            ->get()
            ->first(function (Project $project) {
                $path = storage_path('app/' . $project->getStorageDir('proposals') . '/' . $project->proposal_filename);

                return $project->proposal_filename && file_exists($path);
            });

        if (!$project) {
            $this->markTestSkipped('No assigned project with a proposal file on disk.');
        }

        $submission = DB::table('project_submissions')
            ->where('project_id', $project->id)
            ->whereNotNull('file_path')
            ->first();

        $outsider = User::where('type', 'LPI')
            ->where('id', '!=', $project->lpi_id)
            ->first();

        if (!$submission || !$outsider) {
            $this->markTestSkipped('Needs a submission row and a second LPI.');
        }

        $this->actingAs($outsider)
            ->get(route('serveFile2', ['submission_id' => $submission->id]))
            ->assertForbidden();
    }

    public function test_the_raw_file_parameter_is_admin_only()
    {
        $project = $this->projectWithProposal();
        if (!$project) {
            $this->markTestSkipped('No project with a proposal file on disk.');
        }

        $outsider = User::where('type', 'LPI')->where('id', '!=', $project->lpi_id)->first();
        if (!$outsider) {
            $this->markTestSkipped('No second LPI in the test database.');
        }

        $relative = $project->getStorageDir('proposals') . '/' . $project->proposal_filename;

        $this->actingAs($outsider)
            ->get(route('serveFile2', ['file' => $relative]))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login()
    {
        $this->get(route('serveFile2', ['type' => 'proposal', 'id' => 1]))
            ->assertRedirect(route('login'));
    }
}
