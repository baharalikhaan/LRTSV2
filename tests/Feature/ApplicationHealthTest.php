<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Health: route smoke-testing (broken links / 5xx), PDF serving and
 * database-vs-disk consistency for stored documents.
 */
class ApplicationHealthTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'Admin')->where('is_active', 1)->first();
    }

    /**
     * Every parameterless GET route must not blow up (no 5xx) for an admin.
     * This catches broken routes, missing views and controller errors.
     */
    public function test_parameterless_get_routes_do_not_return_server_errors()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $failures = [];

        foreach (Route::getRoutes() as $route) {
            if (!in_array('GET', $route->methods(), true)) {
                continue;
            }
            if (count($route->parameterNames()) > 0 || str_contains($route->uri(), '{')) {
                continue; // needs parameters — skipped here
            }

            $uri = '/' . ltrim($route->uri(), '/');

            try {
                $status = $this->actingAs($admin)->get($uri)->getStatusCode();
                if ($status >= 500) {
                    $failures[] = "{$uri} => {$status}";
                }
            } catch (\Throwable $e) {
                $failures[] = "{$uri} => EXCEPTION " . $e->getMessage();
            }
        }

        $this->assertEmpty($failures, "GET routes returned server errors:\n" . implode("\n", $failures));
    }

    /** A proposal PDF that exists on disk must be served as application/pdf. */
    public function test_proposal_pdf_is_served_when_file_exists()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $project = Project::whereNotNull('proposal_filename')
            ->where('proposal_filename', '!=', '')
            ->get()
            ->first(function ($p) {
                $path = storage_path('app/' . $p->getStorageDir('proposals') . '/' . $p->proposal_filename);
                return file_exists($path);
            });

        if (!$project) {
            $this->markTestSkipped('No project with an on-disk proposal to test.');
        }

        $res = $this->actingAs($admin)->get('/serveFile2?type=proposal&id=' . $project->id);

        $res->assertStatus(200);
        $this->assertStringContainsString(
            'application/pdf',
            (string) $res->headers->get('content-type'),
            'serveFile2 must return a PDF content type'
        );
    }

    /**
     * Every project whose proposal_filename is set must have that exact file on
     * disk (using the stored name, which may be legacy "_proposal" or canonical).
     * Scoped to the supported cycles (2024 / 2025).
     */
    public function test_proposal_filename_always_has_a_file_on_disk()
    {
        $missing = [];

        Project::whereNotNull('proposal_filename')
            ->where('proposal_filename', '!=', '')
            ->whereHas('program.cycle', fn ($q) => $q->whereIn('year', [2024, 2025]))
            ->chunk(200, function ($projects) use (&$missing) {
                foreach ($projects as $project) {
                    $path = storage_path('app/' . $project->getStorageDir('proposals') . '/' . $project->proposal_filename);
                    if (!file_exists($path)) {
                        $missing[] = "project #{$project->id} ({$project->old_project_id}) => {$project->proposal_filename}";
                    }
                }
            });

        $this->assertEmpty(
            $missing,
            "Projects reference proposals missing from disk:\n" . implode("\n", $missing)
        );
    }
}
