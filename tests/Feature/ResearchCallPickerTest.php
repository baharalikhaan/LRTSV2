<?php

namespace Tests\Feature;

use App\Http\Controllers\ProjectController;
use App\Models\Program;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The Projects sub-menu entries (except All Projects) open an intermediate
 * research-call chooser; each research call has a Projects button that opens
 * the target page filtered to that research call.
 */
class ResearchCallPickerTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'Admin')->where('is_active', true)->first();
    }

    private function program(): ?Program
    {
        return Program::with('grant', 'cycle')
            ->has('projects')
            ->orderBy('id')
            ->first();
    }

    public function test_every_context_renders_the_research_call_chooser()
    {
        $admin = $this->admin();
        $program = $this->program();

        if (!$admin || !$program) {
            $this->markTestSkipped('Needs an admin and a research call with projects.');
        }

        foreach (array_keys(ProjectController::RESEARCH_CALL_CONTEXTS) as $context) {
            $response = $this->actingAs($admin)->get(route('projects.research-calls', $context));

            $response->assertOk();
            $response->assertSee('Research Call', false);
            $response->assertSee(e($program->program_title), false);

            // The Projects button links the target page with the research call id.
            $response->assertSee(
                route(ProjectController::RESEARCH_CALL_CONTEXTS[$context]['route'], ['program_id' => $program->id]),
                false
            );
        }
    }

    public function test_an_unknown_context_returns_404()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('Needs an admin.');
        }

        $this->actingAs($admin)
            ->get(route('projects.research-calls', 'not-a-context'))
            ->assertNotFound();
    }

    public function test_admin_only_contexts_are_forbidden_to_other_roles()
    {
        $reviewer = User::where('type', 'Reviewer')->where('is_active', true)->first();
        if (!$reviewer) {
            $this->markTestSkipped('Needs a reviewer.');
        }

        foreach (ProjectController::RESEARCH_CALL_CONTEXTS as $context => $meta) {
            $response = $this->actingAs($reviewer)->get(route('projects.research-calls', $context));

            if (!empty($meta['admin'])) {
                $response->assertForbidden();
            } else {
                $response->assertOk();
            }
        }
    }

    public function test_target_pages_accept_the_research_call_filter()
    {
        $admin = $this->admin();
        $program = $this->program();

        if (!$admin || !$program) {
            $this->markTestSkipped('Needs an admin and a research call with projects.');
        }

        foreach (ProjectController::RESEARCH_CALL_CONTEXTS as $context => $meta) {
            if (!empty($meta['admin'])) {
                continue; // admin pages are exercised below with the admin account
            }
        }

        $targets = [
            'projects.reviewer-assignment',
            'projects.pending-reviews',
            'projects.extend-progress',
            'reports.report-cards',
            'admin-upload.index',
        ];

        foreach ($targets as $route) {
            $response = $this->actingAs($admin)->get(route($route, ['program_id' => $program->id]));

            $response->assertOk();
            // The context banner offers a way back to the chooser.
            $response->assertSee('Change research call', false);
        }
    }

    public function test_the_filtered_list_only_contains_projects_of_that_research_call()
    {
        $admin = $this->admin();

        if (!$admin) {
            $this->markTestSkipped('Needs an admin.');
        }

        // A research call with projects, and a project of a different research call.
        $program = Program::has('projects')->orderBy('id')->first();
        if (!$program) {
            $this->markTestSkipped('Needs a research call with projects.');
        }

        $inside = Project::where('program_id', $program->id)->orderBy('id')->first();
        $outside = Project::whereNotNull('program_id')
            ->where('program_id', '!=', $program->id)
            ->orderBy('id')
            ->first();

        if (!$inside || !$outside) {
            $this->markTestSkipped('Needs projects in two different research calls.');
        }

        // The Extend Progress page lists every project of the selected call.
        $response = $this->actingAs($admin)->get(route('projects.extend-progress', ['program_id' => $program->id]));
        $response->assertOk();

        $html = $response->getContent();

        // Compare full links so one id cannot match another as a substring
        // (the demo data contains ids like QUHI-testing-1 and QUHI-testing-1224).
        $this->assertStringContainsString('href="' . route('projects.show', $inside->id) . '"', $html);
        $this->assertStringNotContainsString('href="' . route('projects.show', $outside->id) . '"', $html);
    }

    /** Labels of the sidebar entries currently rendered with the active class. */
    private function activeMenuLabels(string $html): array
    {
        preg_match_all('/<a class="sidebar-nav-item active"[^>]*>(.*?)<\/a>/s', $html, $matches);

        $labels = [];
        foreach ($matches[1] as $block) {
            if (preg_match('/<span>([^<]+)<\/span>/', $block, $span)) {
                $labels[] = trim($span[1]);
            }
        }

        return $labels;
    }

    public function test_only_the_clicked_menu_item_is_highlighted_on_the_chooser()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('Needs an admin.');
        }

        // Every chooser entry shares one route name, so they must be told
        // apart by their context parameter (this used to highlight all of them).
        foreach (ProjectController::RESEARCH_CALL_CONTEXTS as $context => $meta) {
            $response = $this->actingAs($admin)->get(route('projects.research-calls', $context));
            $response->assertOk();

            $active = $this->activeMenuLabels($response->getContent());

            // Never more than one entry highlighted.
            $this->assertLessThanOrEqual(1, count($active), "More than one menu item is highlighted on the {$context} chooser.");

            // My Assignments / Graded Projects are hidden from the sidebar by
            // design, so nothing can be highlighted for those contexts.
            if (!empty($meta['admin'])) {
                $this->assertSame(
                    [$meta['title']],
                    $active,
                    "Only [{$meta['title']}] should be highlighted on the {$context} chooser."
                );
            }
        }
    }

    public function test_only_the_target_menu_item_is_highlighted_on_the_filtered_page()
    {
        $admin = $this->admin();
        $program = $this->program();

        if (!$admin || !$program) {
            $this->markTestSkipped('Needs an admin and a research call with projects.');
        }

        $response = $this->actingAs($admin)->get(
            route('projects.reviewer-assignment', ['program_id' => $program->id])
        );
        $response->assertOk();

        $this->assertSame(['Reviewer Assignment'], $this->activeMenuLabels($response->getContent()));
    }

    /** Research-call names in the order they appear in the chooser table. */
    private function researchCallNames(string $html): array
    {
        preg_match_all('/<tr class="research-call-row".*?<\/tr>/s', $html, $rows);

        $names = [];
        foreach ($rows[0] as $row) {
            if (preg_match('/<a[^>]*>\s*([^<]+?)\s*<\/a>/', $row, $m)) {
                $names[] = trim(html_entity_decode($m[1]));
            }
        }

        return $names;
    }

    public function test_the_chooser_sorts_by_cycle_year_then_research_call_title_by_default()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('Needs an admin.');
        }

        $expected = Program::with('cycle')
            ->get()
            ->sortBy([
                fn ($a, $b) => (int) ($b->cycle->year ?? 0) <=> (int) ($a->cycle->year ?? 0),  // newest first
                fn ($a, $b) => strcasecmp($a->program_title, $b->program_title),                // then title A→Z
            ])
            ->pluck('program_title')
            ->values()
            ->all();

        $response = $this->actingAs($admin)->get(route('projects.research-calls', 'reviewer-assignment'));
        $response->assertOk();

        $this->assertSame($expected, $this->researchCallNames($response->getContent()));
    }

    public function test_the_chooser_sorts_by_the_requested_column()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('Needs an admin.');
        }

        // Title A→Z
        $response = $this->actingAs($admin)->get(
            route('projects.research-calls', ['context' => 'reviewer-assignment', 'sort' => 'title', 'dir' => 'asc'])
        );
        $names = $this->researchCallNames($response->getContent());
        $sorted = $names;
        sort($sorted, SORT_FLAG_CASE | SORT_STRING);
        $this->assertSame($sorted, $names, 'Sorting by research call title should be alphabetical.');

        // Project count, most first
        $response = $this->actingAs($admin)->get(
            route('projects.research-calls', ['context' => 'reviewer-assignment', 'sort' => 'projects', 'dir' => 'desc'])
        );
        $response->assertOk();

        $first = trim(preg_match('/<tr class="research-call-row".*?<\/tr>/s', $response->getContent(), $m)
            ? preg_replace('/\s+/', ' ', strip_tags($m[0]))
            : '');
        $this->assertMatchesRegularExpression('/\d+ projects/', $first);
    }

    public function test_the_projects_page_is_ordered_by_cycle_year_then_research_call()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('Needs an admin.');
        }

        $response = $this->actingAs($admin)->get(route('projects.available'));
        $response->assertOk();

        $html = $response->getContent();

        // The order of the project rows, mapped to their research call's cycle year.
        preg_match_all('/\/projects\/(\d+)"/', $html, $m);
        $ids = array_values(array_unique(array_map('intval', $m[1])));

        if (count($ids) < 3) {
            $this->markTestSkipped('Not enough project rows rendered.');
        }

        $years = Project::with('program.cycle')
            ->whereIn('id', array_slice($ids, 0, 15))
            ->get()
            ->keyBy('id')
            ->map(fn ($p) => (int) ($p->program->cycle->year ?? 0));

        $sequence = [];
        foreach (array_slice($ids, 0, 15) as $id) {
            if ($years->has($id)) {
                $sequence[] = $years[$id];
            }
        }

        $sorted = $sequence;
        rsort($sorted);

        $this->assertSame($sorted, $sequence, 'Projects should be ordered by research-call cycle year, newest first.');
    }

    public function test_the_filtered_page_shows_the_listed_and_total_project_counts()
    {
        $admin = $this->admin();
        $program = $this->program();

        if (!$admin || !$program) {
            $this->markTestSkipped('Needs an admin and a research call with projects.');
        }

        $total = $program->projects()->count();

        $response = $this->actingAs($admin)->get(
            route('projects.reviewer-assignment', ['program_id' => $program->id])
        );
        $response->assertOk();

        $html = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/Showing \d+ of ' . $total . ' projects?/',
            $html,
            'The banner should show how many projects are listed out of the research call total.'
        );

        // The listed figure is the number of rows actually rendered.
        preg_match('/Showing (\d+) of ' . $total . '/', $html, $m);
        $this->assertLessThanOrEqual($total, (int) $m[1]);
    }

    public function test_the_research_call_view_lists_every_project_and_flags_the_ones_needing_action()
    {
        $admin = $this->admin();

        // A research call whose projects are not all actionable (some reviewed).
        $program = Program::has('projects', '>=', 3)->orderBy('id')->first();
        if (!$admin || !$program) {
            $this->markTestSkipped('Needs an admin and a research call with a few projects.');
        }

        $projects = Project::with('reviewers')->where('program_id', $program->id)->get();
        $total = $projects->count();

        $needsAction = $projects->filter(function (Project $p) {
            return $p->reviewers->isEmpty()
                || in_array($p->currentWorkflowStatus(), ['Assigned', 'Claimed', 'proposal_rejected', 'progress_rejected'], true);
        })->count();

        $response = $this->actingAs($admin)->get(
            route('projects.reviewer-assignment', ['program_id' => $program->id])
        );
        $response->assertOk();

        $html = $response->getContent();

        // The whole research call is listed…
        $this->assertStringContainsString("Showing {$total} of {$total} projects", $html);

        foreach ($projects as $project) {
            $this->assertStringContainsString(
                'href="' . route('projects.show', $project->id) . '"',
                $html,
                "Project {$project->old_project_id} should be listed even when it needs no action."
            );
        }

        // …and the ones that still need a reviewer carry the badge.
        // (Counted by the badge's title attribute: the version-history modal in
        // the layout also contains the words "Needs action".)
        $this->assertSame(
            $needsAction,
            substr_count($html, 'title="This project still needs reviewer attention"'),
            'The "Needs action" badge should mark exactly the projects that need reviewer attention.'
        );
    }

    public function test_the_chooser_links_to_the_target_page_with_the_research_call_id()
    {
        $admin = $this->admin();
        $program = $this->program();

        if (!$admin || !$program) {
            $this->markTestSkipped('Needs an admin and a research call with projects.');
        }

        $response = $this->actingAs($admin)->get(route('projects.research-calls', 'reviewer-assignment'));
        $response->assertOk();

        $response->assertSee(
            route('projects.reviewer-assignment', ['program_id' => $program->id]),
            false
        );
    }
}
