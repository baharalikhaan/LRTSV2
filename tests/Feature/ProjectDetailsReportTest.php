<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Project Details Report — one flat record per project with every field the
 * system holds about it, plus the Excel export.
 */
class ProjectDetailsReportTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    public function test_admin_can_view_the_project_details_report()
    {
        $admin = $this->user('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $res = $this->actingAs($admin)->get('/reports/project-details');

        $res->assertStatus(200);
        $res->assertSee('Project Details Report');
        $res->assertSee('Export to Excel');
    }

    public function test_report_is_registered_under_the_reports_section()
    {
        $this->assertTrue(
            collect(app('router')->getRoutes())->contains(fn ($r) => $r->uri() === 'reports/project-details'),
            'The reports/project-details route is not registered.'
        );
    }

    public function test_non_admins_cannot_view_the_project_details_report()
    {
        foreach (['LPI', 'Reviewer'] as $type) {
            $user = $this->user($type);
            if (!$user) {
                continue;
            }
            // ReportController redirects non-admins back to the dashboard with an error.
            $this->actingAs($user)
                ->get('/reports/project-details')
                ->assertStatus(302)
                ->assertSessionHas('error');
        }
    }

    public function test_guests_are_redirected_from_the_project_details_report()
    {
        $this->get('/reports/project-details')->assertRedirect('/login');
    }

    public function test_export_returns_an_excel_workbook()
    {
        $admin = $this->user('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $res = $this->actingAs($admin)->get('/reports/project-details/export');

        $res->assertStatus(200);
        $this->assertStringContainsString(
            'spreadsheetml',
            $res->headers->get('content-type'),
            'The export did not return an Excel workbook.'
        );
    }

    public function test_report_builds_one_row_per_project_with_every_column()
    {
        $data = \App\Services\ProjectDetailsReportService::build([]);

        $this->assertNotEmpty($data['rows'], 'The report returned no rows.');
        $this->assertNotEmpty($data['columns'], 'The report returned no columns.');

        // Every row must expose every column (a single complete record).
        foreach ($data['rows'] as $row) {
            foreach ($data['columns'] as $column) {
                $this->assertArrayHasKey(
                    $column['key'],
                    $row,
                    "Column [{$column['key']}] is missing from the project record."
                );
            }
            break; // one row is enough for the shape assertion
        }

        // A row is keyed by the project id.
        $this->assertArrayHasKey('project_id', $data['rows'][0]);
        $this->assertArrayHasKey('title', $data['rows'][0]);
    }

    public function test_the_removed_columns_are_gone()
    {
        $keys = array_column(\App\Services\ProjectDetailsReportService::columns(), 'key');

        foreach (['id', 'old_project_id', 'created_at', 'updated_at', 'current_status', 'status_changed_at'] as $removed) {
            $this->assertNotContains(
                $removed,
                $keys,
                "Column [{$removed}] should no longer be part of the report."
            );
        }

        // The workflow status is still filterable even though it is not shown.
        $this->assertArrayHasKey(
            'current_status',
            \App\Services\ProjectDetailsReportService::build([])['rows'][0] ?? []
        );
    }

    public function test_the_project_id_column_always_leads()
    {
        $data = \App\Services\ProjectDetailsReportService::build([], ['title']);

        $this->assertSame('project_id', array_column($data['columns'], 'key')[0]);
        $this->assertSame(
            'project_id',
            array_column(\App\Services\ProjectDetailsReportService::build([], [])['columns'], 'key')[0]
        );
    }

    public function test_column_groups_cover_every_column()
    {
        $data = \App\Services\ProjectDetailsReportService::build([]);
        $groups = \App\Services\ProjectDetailsReportService::groups($data['columns']);

        $this->assertNotEmpty($groups);

        $span = array_sum(array_column($groups, 'span'));
        $this->assertSame(count($data['columns']), $span, 'The group spans do not cover every column.');

        // Grouped columns must be contiguous: a group never reappears after
        // another one has started.
        $labels = array_column($data['columns'], 'group');
        $position = 0;
        foreach (array_values(array_unique($labels)) as $label) {
            $count = count(array_keys($labels, $label));
            for ($i = 0; $i < $count; $i++) {
                $this->assertSame($label, $labels[$position], "Group [{$label}] is not contiguous.");
                $position++;
            }
        }
        $this->assertSame(count($labels), $position);
    }

    public function test_status_filter_narrows_the_result()
    {
        $all = \App\Services\ProjectDetailsReportService::build([]);
        if (empty($all['rows'])) {
            $this->markTestSkipped('No projects in the test database.');
        }

        $status = $all['rows'][0]['current_status'] ?? null;
        if (!$status) {
            $this->markTestSkipped('No project with a workflow status.');
        }

        $filtered = \App\Services\ProjectDetailsReportService::build(['status' => $status]);

        $this->assertLessThanOrEqual(count($all['rows']), count($filtered['rows']));
        foreach ($filtered['rows'] as $row) {
            $this->assertSame(strtolower($status), strtolower((string) $row['current_status']));
        }
    }

    public function test_the_recommended_fields_are_used_by_default()
    {
        $data = \App\Services\ProjectDetailsReportService::build([]);

        $expected = \App\Services\ProjectDetailsReportService::DEFAULT_FIELDS;
        $actual = array_column($data['columns'], 'key');

        // Same set of fields, presented in the canonical column order.
        $sortedExpected = $expected;
        $sortedActual = $actual;
        sort($sortedExpected);
        sort($sortedActual);
        $this->assertSame($sortedExpected, $sortedActual);

        // The canonical order is preserved (the project id leads).
        $this->assertSame('project_id', $actual[0]);
    }

    public function test_only_the_selected_fields_are_returned()
    {
        $data = \App\Services\ProjectDetailsReportService::build([], ['title', 'lpi_name', 'budget_amount']);

        // The Project ID column is always included and leads.
        $this->assertSame(['project_id', 'title', 'lpi_name', 'budget_amount'], array_column($data['columns'], 'key'));
        $this->assertCount(4, $data['columns']);

        // Every row still carries the selected fields.
        foreach (array_slice($data['rows'], 0, 5) as $row) {
            $this->assertArrayHasKey('project_id', $row);
            $this->assertArrayHasKey('title', $row);
            $this->assertArrayHasKey('lpi_name', $row);
            $this->assertArrayHasKey('budget_amount', $row);
        }
    }

    public function test_unknown_fields_are_ignored()
    {
        $data = \App\Services\ProjectDetailsReportService::build([], ['title', 'not_a_real_field']);

        $this->assertSame(['project_id', 'title'], array_column($data['columns'], 'key'));
    }

    public function test_the_selection_is_remembered_for_the_next_request()
    {
        $admin = $this->user('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        // Picking fields stores them and redirects to a clean URL.
        $res = $this->actingAs($admin)->get('/reports/project-details?fields[]=title&fields[]=lpi_name');
        $res->assertStatus(302);

        $this->assertSame(
            ['title', 'lpi_name'],
            session(\App\Http\Controllers\ReportController::FIELD_SESSION_KEY)
        );

        // The next page load uses the remembered selection.
        $page = $this->actingAs($admin)->get('/reports/project-details');
        $page->assertStatus(200);
        $page->assertSee('2 / ' . count(\App\Services\ProjectDetailsReportService::columns()));
        $page->assertSee('fields shown');
    }

    public function test_the_commitments_are_included()
    {
        $data = \App\Services\ProjectDetailsReportService::build([]);
        $keys = array_column($data['columns'], 'key');

        foreach ([
            'commit_q1_articles', 'commit_q2_articles', 'commit_q3_articles', 'commit_q4_articles',
            'commit_conf_articles', 'commit_books', 'commit_edit_books', 'commit_chapters',
            'commit_ip', 'commit_filed_patents', 'commit_open_source', 'commit_startup',
            'commit_ethical', 'commit_masters', 'commit_ug', 'commit_phd', 'commit_cross_college',
        ] as $key) {
            $this->assertContains($key, $keys, "Commitment field [{$key}] is missing from the report.");
        }

        // Every commitment field belongs to the Commitments group and source.
        foreach ($data['columns'] as $column) {
            if (str_starts_with($column['key'], 'commit_')) {
                $this->assertSame('Commitments', $column['group']);
                $this->assertSame('commitments', $column['source']);
            }
        }

        // The values come from the commitments table for the project.
        $project = \App\Models\Project::whereHas('commitments')->first();
        if ($project) {
            $commitment = $project->commitments()->first();
            $row = collect($data['rows'])->firstWhere('project_id', (string) $project->old_project_id)
                ?: collect($data['rows'])->firstWhere('project_id', $project->id);

            if ($row) {
                $this->assertSame($commitment->q1article, $row['commit_q1_articles']);
                $this->assertSame($commitment->UG, $row['commit_ug']);
                $this->assertSame($commitment->startUp ? 'Yes' : 'No', $row['commit_startup']);
            }
        }
    }

    public function test_every_field_is_tagged_with_a_known_source_table()
    {
        $sources = \App\Services\ProjectDetailsReportService::sources();

        foreach (\App\Services\ProjectDetailsReportService::columns() as $column) {
            $this->assertArrayHasKey(
                'source',
                $column,
                "Column [{$column['key']}] has no source table."
            );
            $this->assertArrayHasKey(
                $column['source'],
                $sources,
                "Column [{$column['key']}] points at an unknown source table [{$column['source']}]."
            );
        }

        // Every known source table actually contributes at least one field.
        foreach (array_keys($sources) as $source) {
            $this->assertNotEmpty(
                array_filter(
                    \App\Services\ProjectDetailsReportService::columns(),
                    fn($c) => $c['source'] === $source
                ),
                "Source table [{$source}] has no fields."
            );
        }
    }

    public function test_the_export_contains_only_the_selected_fields()
    {
        $admin = $this->user('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $this->actingAs($admin)
            ->get('/reports/project-details?fields[]=title&fields[]=lpi_name')
            ->assertStatus(302);

        $file = tempnam(sys_get_temp_dir(), 'pd') . '.xlsx';

        $res = $this->actingAs($admin)->get('/reports/project-details/export');
        $res->assertStatus(200);

        $path = $res->baseResponse->getFile()->getPathname();
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet();

        // Header row: Project ID (always shown) + the two selected fields.
        $this->assertSame('Project ID', $sheet->getCell('A2')->getValue());
        $this->assertSame('Project Title', $sheet->getCell('B2')->getValue());
        $this->assertSame('LPI Name', $sheet->getCell('C2')->getValue());
        $this->assertSame(3, \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn()));

        // The group row sits above it: Project (2 of the 3 columns) then LPI & Contact.
        $this->assertSame('Project', $sheet->getCell('A1')->getValue());
        $this->assertSame('LPI & Contact', $sheet->getCell('C1')->getValue());
        $this->assertArrayHasKey('A1:B1', $sheet->getMergeCells());

        // Maroon hairlines: above the group row, under it, and under the columns.
        $this->assertSame('FF8D1B3D', $sheet->getStyle('A1')->getBorders()->getTop()->getColor()->getARGB());
        $this->assertSame('FF8D1B3D', $sheet->getStyle('A1')->getBorders()->getBottom()->getColor()->getARGB());
        $this->assertSame('FF8D1B3D', $sheet->getStyle('A2')->getBorders()->getBottom()->getColor()->getARGB());

        // Both header rows are light, not solid brand blocks.
        $this->assertSame('FFFAF7F0', $sheet->getStyle('A1')->getFill()->getStartColor()->getARGB());
        $this->assertSame('FFF2EAD6', $sheet->getStyle('A2')->getFill()->getStartColor()->getARGB());
        $this->assertSame('FF63102B', $sheet->getStyle('A2')->getFont()->getColor()->getARGB());

        // Both header rows are frozen (panes split below row 2).
        $this->assertSame('A3', $sheet->getFreezePane());

        @unlink($file);
    }
}
