<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Commitments editing on the grading page: admin only, reviewers read-only.
 *
 * Runs against the dedicated test database (phpunit.xml -> rtsnew_test) and
 * rolls back every test via DatabaseTransactions, so no real data is touched.
 */
class CommitmentEditTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    private function anyProject(): ?Project
    {
        return Project::first();
    }

    /** Guests are sent to the login screen. */
    public function test_guest_cannot_update_commitments()
    {
        $project = $this->anyProject();
        if (!$project) {
            $this->markTestSkipped('No projects in test database.');
        }

        $res = $this->post("/projects/{$project->id}/commitments", ['q1article' => 5]);

        $this->assertContains(
            $res->getStatusCode(),
            [301, 302],
            "Guest should be redirected (got {$res->getStatusCode()})"
        );
    }

    /** Reviewers can view commitments on the grading page but never edit them. */
    public function test_reviewer_cannot_update_commitments()
    {
        $project = $this->anyProject();
        $reviewer = $this->activeUser('Reviewer');
        if (!$project || !$reviewer) {
            $this->markTestSkipped('Missing reviewer or project in test database.');
        }

        $res = $this->actingAs($reviewer)
            ->postJson("/projects/{$project->id}/commitments", ['q1article' => 5]);

        $res->assertStatus(403);
    }

    /** Admins can create/update a project's commitment values. */
    public function test_admin_can_update_commitments()
    {
        $project = $this->anyProject();
        $admin = $this->activeUser('Admin');
        if (!$project || !$admin) {
            $this->markTestSkipped('Missing admin or project in test database.');
        }

        $res = $this->actingAs($admin)->postJson("/projects/{$project->id}/commitments", [
            'q1article'    => 7,
            'q2article'    => 1,
            'confArticle'  => 2,
            'chapters'     => 3,
            'ip'           => 1,
            'filedPatent'  => 1,
            'openSourceSW' => 2,
            'startUp'      => 1,
            'ethical'      => 1,
            'master'       => 3,
            'UG'           => 4,
            'Phd'          => 1,
            'crossCollege' => 1,
        ]);

        $res->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('commitments', [
            'project_id'   => $project->id,
            'q1article'    => 7,
            'confArticle'  => 2,
            'openSourceSW' => 2,
            'startUp'      => 1,
            'ethical'      => 1,
            'master'       => 3,
            'Phd'          => 1,
            'crossCollege' => 1,
        ]);
    }

    /** Negative commitment values are rejected. */
    public function test_validation_rejects_negative_values()
    {
        $project = $this->anyProject();
        $admin = $this->activeUser('Admin');
        if (!$project || !$admin) {
            $this->markTestSkipped('Missing admin or project in test database.');
        }

        $res = $this->actingAs($admin)
            ->postJson("/projects/{$project->id}/commitments", ['q1article' => -1]);

        $res->assertStatus(422);
    }
}
