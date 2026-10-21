<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Printable (A4) version of the project detail page.
 *
 * Covers the new route/view, that it renders the project data in the
 * report-card-inspired header/logo, that the detail page links to it, and that
 * the role gating (reviewers don't see Outcomes/Grading) is preserved.
 */
class ProjectPrintableTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    private function someProject(): ?Project
    {
        return Project::whereNotNull('program_id')->whereNotNull('old_project_id')->first()
            ?: Project::first();
    }

    public function test_guest_is_redirected_to_login()
    {
        $project = $this->someProject();
        if (!$project) {
            $this->markTestSkipped('No project in test database.');
        }

        $this->get(route('projects.printable', ['id' => $project->id]))
            ->assertRedirect();
    }

    public function test_admin_renders_the_printable_project_summary()
    {
        $admin   = $this->activeUser('Admin');
        $project = $this->someProject();
        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a project.');
        }

        $res = $this->actingAs($admin)->get(route('projects.printable', ['id' => $project->id]));
        $res->assertOk();

        $html = $res->getContent();
        $this->assertStringContainsString('text/html', (string) $res->headers->get('content-type'));

        // A4 page + QU logo, print control and the core sections.
        $this->assertStringContainsString('@page', $html);
        $this->assertStringContainsString('size: A4 portrait', $html);
        $this->assertStringContainsString('Print / Save PDF', $html);
        $this->assertStringContainsString('Project Details', $html);
        $this->assertStringContainsString('Project files', $html);
        $this->assertStringContainsString('research_logo.png', $html);
        $this->assertStringContainsString('style="width:65%;">Comment', $html, 'Progress grading comments should have extra width.');
        $this->assertStringContainsString('Behind', $html, 'Commitment status should be computed from the project totals.');

        // Commitments use two columns, followed by full-width files and then outcomes.
        $sectionPositions = [
            strpos($html, '>Commitments</div>'),
            strpos($html, '>Publications</div>'),
            strpos($html, '>IP &amp; innovation</div>'),
            strpos($html, '>Students &amp; training</div>'),
            strpos($html, '>Project files'),
            strpos($html, 'Outcomes — scholarly articles'),
        ];
        $this->assertNotContains(false, $sectionPositions, 'Expected printable sections were not rendered.');
        $sortedSectionPositions = $sectionPositions;
        sort($sortedSectionPositions);
        $this->assertSame($sortedSectionPositions, $sectionPositions);

        // The project's own identity must be in the document.
        $this->assertStringContainsString($project->old_project_id, $html);
    }

    public function test_detail_page_links_to_the_printable_version()
    {
        $admin   = $this->activeUser('Admin');
        $project = $this->someProject();
        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a project.');
        }

        $res = $this->actingAs($admin)->get(route('projects.show', ['id' => $project->id]));
        $res->assertOk();

        $html = $res->getContent();
        $this->assertStringContainsString('Printable Version', $html);
        $this->assertStringContainsString(
            '/projects/' . $project->id . '/printable',
            $html,
            'The detail page must link to the printable version.'
        );
    }

    /** A reviewer-only account must not get the Outcomes / Grading sections. */
    public function test_reviewer_omits_outcomes_and_grading_sections()
    {
        $reviewer = User::where('type', 'Reviewer')->where('is_active', 1)->first();
        $project = $this->someProject();
        if (!$reviewer || !$project) {
            $this->markTestSkipped('Need a reviewer-only account and a project.');
        }

        $html = $this->actingAs($reviewer)
            ->get(route('projects.printable', ['id' => $project->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Project files', $html, 'Reviewers still see the summary sections.');
        $this->assertStringNotContainsString('>Outcomes<', $html, 'Outcomes must be hidden from reviewers.');
        $this->assertStringNotContainsString('>Grading<', $html, 'Grading must be hidden from reviewers.');
    }

    public function test_missing_project_returns_404()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        $this->actingAs($admin)
            ->get(route('projects.printable', ['id' => 999999]))
            ->assertNotFound();
    }

    /**
     * A project with NO program assigned (program_id = null) must still render.
     * Its deadlines/grant/research-call cells fall back to "—" instead of
     * throwing "Attempt to read property ... on null".
     */
    public function test_printable_renders_for_a_project_without_a_program()
    {
        $admin = $this->activeUser('Admin');
        $project = Project::whereNull('program_id')->first();
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }
        if (!$project) {
            $this->markTestSkipped('No project without a program in the test database.');
        }

        $res = $this->actingAs($admin)->get(route('projects.printable', ['id' => $project->id]));
        $res->assertOk();

        $html = $res->getContent();
        $this->assertStringContainsString('Project Details', $html);
        $this->assertStringContainsString('Project files', $html);
        $this->assertStringContainsString($project->old_project_id ?? (string) $project->id, $html);
    }
}
