<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Research Call report booklet (PDF export).
 *
 * Covers the admin-only gate on the new route and that the booklet actually
 * renders through dompdf without error for a research call with data.
 */
class ResearchCallBookletTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    /** A research call that has projects, so the booklet has real content. */
    private function programWithProjects(): ?Program
    {
        return Program::whereHas('projects')->first()
            ?: Program::orderBy('id')->first();
    }

    public function test_guest_is_redirected_to_login()
    {
        $program = $this->programWithProjects();
        if (!$program) {
            $this->markTestSkipped('No research call in test database.');
        }

        $this->get(route('programs.booklet', ['id' => $program->id]))
            ->assertRedirect();
    }

    public function test_non_admin_cannot_generate_the_booklet()
    {
        $program = $this->programWithProjects();
        $lpi     = $this->activeUser('LPI');
        if (!$program || !$lpi) {
            $this->markTestSkipped('Need a research call and an LPI.');
        }

        $this->actingAs($lpi)
            ->get(route('programs.booklet', ['id' => $program->id]))
            ->assertRedirect(route('home'));
    }

    public function test_missing_research_call_returns_404()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        $this->actingAs($admin)
            ->get(route('programs.booklet', ['id' => 999999]))
            ->assertNotFound();
    }

    public function test_admin_can_open_the_booklet_from_the_research_call_actions_menu()
    {
        $admin = $this->activeUser('Admin');
        $program = $this->programWithProjects();
        if (!$admin || !$program) {
            $this->markTestSkipped('Need an admin and a research call.');
        }

        $response = $this->actingAs($admin)->get(route('programs.index'));

        $response->assertOk();
        $response->assertSee('Report Booklet');
        $response->assertSee(route('programs.booklet', ['id' => $program->id]));
    }

    public function test_admin_renders_the_printable_booklet()
    {
        $admin   = $this->activeUser('Admin');
        $program = $this->programWithProjects();
        if (!$admin || !$program) {
            $this->markTestSkipped('Need an admin and a research call.');
        }

        $response = $this->actingAs($admin)
            ->get(route('programs.booklet', ['id' => $program->id]));

        $response->assertOk();
        $response->assertHeader('content-type');
        $this->assertStringContainsString('text/html', (string) $response->headers->get('content-type'));

        // The printable page: A4 sheets, print control and the booklet content.
        $response->assertSee('Report Booklet');
        $response->assertSee($program->program_title);
        $response->assertSee('Print / Save as PDF');
        // Template-style sections (report1.jpg layout).
        $response->assertSee('Description');
        $response->assertSee('Cumulative Chart');
        $response->assertSee('Workflow Distribution');
        $response->assertSee('Overview');
        $response->assertSee('Summary');
        $response->assertSee('Key Figures');
        $response->assertSee('Project Appendix');

        // The visibility flag must not appear anywhere in the booklet.
        $response->assertDontSee('Visibility');

        // Print-specific styling matches the compact project-detail report.
        $html = $response->getContent();
        $this->assertStringContainsString('@page', $html);
        $this->assertStringContainsString('size:A4 portrait', $html);
        $this->assertStringContainsString('class="sheet"', $html);
        $this->assertStringContainsString('class="rc-head"', $html, 'The compact report header must be present.');
        $this->assertStringContainsString('images/research_logo.png', $html);
        $this->assertStringContainsString('font-size:8.5px', $html, 'Compact table typography must be present.');
        $this->assertStringContainsString('class="people-cols"', $html, 'People content must use a two-column layout.');
        $this->assertStringNotContainsString('page-break-after:always', $html, 'Content groups must flow naturally instead of forcing page breaks.');
        $this->assertStringNotContainsString('Page 1</span>', $html, 'The booklet must not use hard-coded page numbers.');
    }

    /** The booklet must render for an EMPTY research call too (no divide-by-zero / empty-state errors). */
    public function test_booklet_renders_for_a_research_call_with_no_projects()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        $program = Program::whereDoesntHave('projects')->first();
        if (!$program) {
            // Create one so the empty path is still exercised.
            $program = Program::create([
                'program_title' => 'Empty Booklet Test Call',
                'cycle_id'      => Program::first()->cycle_id ?? null,
                'grant_id'      => Program::first()->grant_id ?? null,
                'is_visible'    => false,
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('programs.booklet', ['id' => $program->id]));

        $response->assertOk();
        $this->assertStringContainsString('text/html', (string) $response->headers->get('content-type'));
        $response->assertSee('No projects');
    }
}
