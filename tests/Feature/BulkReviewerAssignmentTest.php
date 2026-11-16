<?php

namespace Tests\Feature;

use App\Mail\GenericEmailMail;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Bulk reviewer assignment (the Reviewer Assignment page) must behave like the
 * assignment modal on the project page:
 *  - the reviewer is added alongside the existing ones (never replaced),
 *  - a reviewer who previously rejected the project is skipped,
 *  - every newly assigned reviewer receives the assignment email,
 *  - an already-assigned reviewer is not notified twice.
 */
class BulkReviewerAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'Admin')->where('is_active', true)->first();
    }

    private function project(): ?Project
    {
        return Project::whereNotNull('program_id')->orderBy('id')->first();
    }

    private function reviewer(): ?User
    {
        return User::whereIn('type', ['Reviewer', 'LPI+Reviewer'])->where('is_active', true)->first();
    }

    /** The test database may hold an older copy of the system template. */
    private function seedAssignmentTemplate(): void
    {
        \App\Models\EmailTemplate::updateOrCreate(
            ['key' => 'reviewer_assigned'],
            [
                'name'     => 'Reviewer Assigned',
                'category' => 'notification',
                'subject'  => \App\Models\EmailTemplate::EVENTS['reviewer_assigned']['subject'],
                'body'     => \App\Models\EmailTemplate::EVENTS['reviewer_assigned']['body'],
            ]
        );
    }

    public function test_bulk_assign_emails_the_newly_assigned_reviewer()
    {
        $admin = $this->admin();
        $project = $this->project();
        $reviewer = $this->reviewer();

        if (!$admin || !$project || !$reviewer) {
            $this->markTestSkipped('Needs an admin, a project and a reviewer in the test database.');
        }

        // Start from a clean slate for this project/reviewer pair.
        DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->delete();

        $this->seedAssignmentTemplate();
        config(['mail.enabled' => true]);
        Mail::fake();

        $response = $this->actingAs($admin)->post(route('projects.bulk-assign'), [
            'assignments' => [
                ['project_id' => $project->id, 'reviewers' => [$reviewer->id]],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects_reviewers', [
            'project_id' => $project->id,
            'user_id'    => $reviewer->id,
        ]);

        Mail::assertSent(GenericEmailMail::class, function (GenericEmailMail $mail) {
            return $mail->heading === 'Reviewer Assigned'
                && str_contains((string) $mail->subjectLine, 'Reviewer Assignment Confirmed');
        });
    }

    public function test_bulk_assign_does_not_email_an_already_assigned_reviewer()
    {
        $admin = $this->admin();
        $project = $this->project();
        $reviewer = $this->reviewer();

        if (!$admin || !$project || !$reviewer) {
            $this->markTestSkipped('Needs an admin, a project and a reviewer in the test database.');
        }

        DB::table('projects_reviewers')->updateOrInsert(
            ['project_id' => $project->id, 'user_id' => $reviewer->id],
            ['role' => 'Reviewer', 'created_at' => now(), 'updated_at' => now()]
        );

        $this->seedAssignmentTemplate();
        config(['mail.enabled' => true]);
        Mail::fake();

        $this->actingAs($admin)->post(route('projects.bulk-assign'), [
            'assignments' => [
                ['project_id' => $project->id, 'reviewers' => [$reviewer->id]],
            ],
        ])->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_bulk_assign_skips_a_reviewer_who_previously_rejected_the_project()
    {
        $admin = $this->admin();
        $project = $this->project();
        $reviewer = $this->reviewer();

        if (!$admin || !$project || !$reviewer) {
            $this->markTestSkipped('Needs an admin, a project and a reviewer in the test database.');
        }

        DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->delete();

        DB::table('reviewer_rejections')->insert([
            'project_id' => $project->id,
            'user_id'    => $reviewer->id,
            'created_at' => now(),
        ]);

        $this->seedAssignmentTemplate();
        config(['mail.enabled' => true]);
        Mail::fake();

        $response = $this->actingAs($admin)->post(route('projects.bulk-assign'), [
            'assignments' => [
                ['project_id' => $project->id, 'reviewers' => [$reviewer->id]],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('projects_reviewers', [
            'project_id' => $project->id,
            'user_id'    => $reviewer->id,
        ]);

        Mail::assertNothingSent();
    }

    public function test_the_un_assign_link_sits_beside_each_assigned_reviewer()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('Needs an admin.');
        }

        // The unfiltered page is an action queue, so pick a project that the
        // page actually lists (assigned + a status that needs attention).
        $project = Project::has('reviewers')
            ->orderBy('id')
            ->get()
            ->first(function (Project $p) {
                return in_array(
                    $p->currentWorkflowStatus(),
                    ['Assigned', 'Claimed', 'proposal_rejected', 'progress_rejected'],
                    true
                );
            });

        if (!$project) {
            $this->markTestSkipped('No assigned project that needs reviewer attention.');
        }

        $reviewer = $project->reviewers->first();

        $response = $this->actingAs($admin)->get(route('projects.reviewer-assignment'));
        $response->assertOk();

        $html = $response->getContent();

        // Each assigned reviewer carries his own un-assign link, in the
        // "Assigned reviewer" column (before the reviewer dropdown).
        $this->assertMatchesRegularExpression(
            '/' . preg_quote(e($reviewer->name), '/') . '.{0,400}?confirmUnassignReviewer\(' . $project->id . ', ' . $reviewer->id . '\)/s',
            $html,
            'The un-assign link should sit next to the reviewer name.'
        );

        // The old un-assign buttons in the action column are gone.
        $this->assertStringNotContainsString('btn-danger btn-sm', $html, 'The action column should only hold the reviewer dropdown.');

        // One dropdown per listed project (each row also posts its project id,
        // which is the second "assignments[...]" occurrence).
        $selects = substr_count($html, 'class="reviewer-select"');
        $this->assertGreaterThan(0, $selects);
        $this->assertSame(
            substr_count($html, '][project_id]') ?: substr_count($html, '[project_id]'),
            $selects,
            'Every row should still have its reviewer dropdown.'
        );
    }

    public function test_reviewer_assignment_page_renders_a_well_formed_table()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('Needs an admin in the test database.');
        }

        $response = $this->actingAs($admin)->get(route('projects.reviewer-assignment'));
        $response->assertOk();

        $html = $response->getContent();

        // The header/body cell counts must match, otherwise DataTables breaks.
        if (preg_match('/<table[^>]*id="projectsTable".*?<\/table>/s', $html, $table)) {
            $headerCells = preg_match_all('/<th[\s>]/', $table[0]);
            $firstRow = null;

            if (preg_match('/<tbody>(.*?)<\/tr>/s', $table[0], $row)) {
                $firstRow = $row[1];
            }

            $this->assertGreaterThan(0, $headerCells, 'The table should have a header.');

            if ($firstRow !== null) {
                $bodyCells = preg_match_all('/<td[\s>]/', $firstRow);
                $this->assertSame(
                    $headerCells,
                    $bodyCells,
                    'The table header and body must have the same number of columns.'
                );
            }

            // One "Assigned reviewer" column only.
            $this->assertSame(1, preg_match_all('/<th[^>]*>\s*Assigned reviewer\s*<\/th>/i', $table[0]));
        }
    }
}
