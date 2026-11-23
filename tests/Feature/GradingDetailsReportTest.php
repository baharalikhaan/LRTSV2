<?php

namespace Tests\Feature;

use App\Models\FinalReportGrading;
use App\Models\Project;
use App\Models\ProgressReportGrading;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * "Grading Details" report — one page for both grant categories, with a
 * category selector in the filters, a column picker and a two-level header.
 * Reviewers are numbered only (no names), and the export writes two worksheets
 * when "All projects" is selected, one sheet when a category is selected.
 *
 * Runs against rtsnew_test and rolls back every test.
 */
class GradingDetailsReportTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'like', '%Admin%')->where('is_active', 1)->orderBy('id')->first();
    }

    /** A reviewer who graded something and is not an LPI anywhere (pure reviewer identity). */
    private function gradingReviewerName(): ?string
    {
        $lpiIds = \Illuminate\Support\Facades\DB::table('projects')
            ->whereNotNull('lpi_id')->distinct()->pluck('lpi_id');

        $grading = ProgressReportGrading::where('publish', '!=', 'pending')
            ->whereNotNull('user_id')->whereNotIn('user_id', $lpiIds)->first()
            ?: FinalReportGrading::where('publish', '!=', 'pending')
                ->whereNotNull('user_id')->whereNotIn('user_id', $lpiIds)->first();

        return $grading && $grading->user ? $grading->user->name : null;
    }

    private function projectIdOf(bool $student): ?string
    {
        $project = Project::whereNotNull('lpi_id')->orderBy('id')->get()
            ->first(fn ($p) => $p->isStudentProject() === $student);

        return $project ? (string) ($project->old_project_id ?: $project->id) : null;
    }

    private function loadWorkbook($response)
    {
        $path = $response->getFile()->getPathname();
        $this->assertFileExists($path);

        return IOFactory::createReader('Xlsx')->load($path);
    }

    /** Option values of a <select> in the rendered page ('' is the "all" option). */
    private function selectOptions(string $html, string $id): array
    {
        $start = strpos($html, 'id="' . $id . '"');
        $this->assertNotFalse($start, 'Select #' . $id . ' not found.');

        $block = substr($html, $start, strpos($html, '</select>', $start) - $start);

        preg_match_all('/<option value="([^"]*)"[^>]*>(.*?)<\/option>/s', $block, $m);

        $options = [];
        foreach ($m[1] as $i => $value) {
            $options[$value] = trim(preg_replace('/\s+/', ' ', $m[2][$i]));
        }

        return $options;
    }

    /** Every project with its research call + grant loaded. */
    private function projects(): \Illuminate\Support\Collection
    {
        return Project::with(['program.grant', 'program.cycle'])->orderBy('id')->get();
    }

    private function cycleIdsFor(?bool $student): array
    {
        return $this->projects()
            ->filter(fn ($p) => $student === null || $p->isStudentProject() === $student)
            ->map(fn ($p) => $p->program->cycle_id ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function test_report_opens_with_the_category_selector_and_both_totals()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $html = $this->actingAs($admin)
            ->get(route('reports.grading-details'))
            ->assertOk()
            ->getContent();

        foreach ([
            'Grading Details Report',
            'name="category"',
            'All projects',
            'Regular grants',
            'Student grants',
            'PR 1',
            'PR 2',
            'Final Grading',
            'Achievements',
            'Provisional Patent',
            'Export to Excel',
        ] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }

        $this->assertMatchesRegularExpression('/Regular grants:\s*<strong>\d+<\/strong>/', $html);
        $this->assertMatchesRegularExpression('/Student grants:\s*<strong>\d+<\/strong>/', $html);
        $this->assertStringNotContainsString('Assigned Reviewers', $html);
    }

    public function test_category_selector_narrows_the_report()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $html = $this->actingAs($admin)
            ->get(route('reports.grading-details', ['category' => 'student']))
            ->assertOk()
            ->getContent();

        // No regular rows are loaded, so the regular total drops to zero.
        $this->assertMatchesRegularExpression('/Regular grants:\s*<strong>0<\/strong>/', $html);

        $regularId = $this->projectIdOf(false);
        if ($regularId) {
            $start = strpos($html, '<table');
            $table = substr($html, $start, strpos($html, '</table>', $start) - $start);
            $this->assertStringNotContainsString($regularId, $table);
        }
    }

    public function test_search_filter_keeps_the_report_scoped_to_its_category()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $studentId = $this->projectIdOf(true);
        if (!$studentId) {
            $this->markTestSkipped('No student-grant project.');
        }

        // The search is a LIKE match, so count what is expected from the DB first.
        $expected = Project::whereNotNull('lpi_id')->get()
            ->filter(fn ($p) => $p->isStudentProject()
                && str_contains((string) ($p->old_project_id ?: $p->id), $studentId))
            ->count();

        $html = $this->actingAs($admin)
            ->get(route('reports.grading-details', ['category' => 'student', 'q' => $studentId]))
            ->assertOk()
            ->getContent();

        $this->assertGreaterThan(0, $expected);
        $this->assertStringContainsString($studentId, $html);
        $this->assertMatchesRegularExpression('/Regular grants:\s*<strong>0<\/strong>/', $html);
        $this->assertStringContainsString('Showing ' . $expected . ' project(s)', $html);
        $this->assertStringNotContainsString('No projects for the selected filters.', $html);
    }

    public function test_report_never_shows_reviewer_names()
    {
        $admin = $this->admin();
        $name = $this->gradingReviewerName();

        if (!$admin || !$name) {
            $this->markTestSkipped('No admin or no published grading.');
        }

        $html = $this->actingAs($admin)
            ->get(route('reports.grading-details'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString($name, $html, 'Reviewer names must stay out of the report.');
        $this->assertMatchesRegularExpression('/Reviewer [0-9]+:/', $html, 'Reviewers must be numbered.');
    }

    public function test_non_admins_cannot_open_the_report()
    {
        $lpi = User::where('type', 'LPI')->where('is_active', 1)->first();
        if (!$lpi) {
            $this->markTestSkipped('No active LPI.');
        }

        $this->actingAs($lpi)->get(route('reports.grading-details'))->assertRedirect();
    }

    public function test_filters_are_category_then_cycle_then_research_call_then_grant_without_status()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $html = $this->actingAs($admin)
            ->get(route('reports.grading-details'))
            ->assertOk()
            ->getContent();

        $formStart = strpos($html, 'id="gradingFilterForm"');
        $formEnd = strpos($html, '</form>', $formStart);
        $form = substr($html, $formStart, $formEnd - $formStart);

        $category = strpos($form, 'for="category"');
        $cycle = strpos($form, 'for="cycle_id"');
        $call = strpos($form, 'for="program_id"');
        $grant = strpos($form, 'for="grant_id"');
        $search = strpos($form, 'for="q"');

        $this->assertNotFalse($category + $cycle + $call + $grant + $search);
        $this->assertTrue($category < $cycle, 'Category must come first.');
        $this->assertTrue($cycle < $call, 'Cycle must come before Research call.');
        $this->assertTrue($call < $grant, 'Research call must come before Grant.');
        $this->assertTrue($grant < $search, 'Grant must come before Search.');

        $this->assertStringNotContainsString('Any status', $form);
        $this->assertStringNotContainsString('name="status"', $form);
    }

    public function test_export_of_all_projects_writes_two_worksheets()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $response = $this->actingAs($admin)->get(route('reports.grading-details-export'));
        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('content-type'));

        $book = $this->loadWorkbook($response);

        try {
            $this->assertSame(['Regular Grants', 'Student Grants'], $book->getSheetNames());

            $sheet = $book->getSheet(0);

            // Group row + sub-columns.
            $this->assertSame('Project', $sheet->getCell('A1')->getValue());
            $this->assertSame('PR 1', $sheet->getCell('J1')->getValue());
            $this->assertSame('Final Grading', $sheet->getCell('AB1')->getValue());
            $this->assertSame('Publications', $sheet->getCell('AI1')->getValue());
            $this->assertSame('Intellectual Property', $sheet->getCell('AQ1')->getValue());
            $this->assertSame('Achievements', $sheet->getCell('K2')->getValue());
            $this->assertSame('A', $sheet->getCell('AC2')->getValue());
            $this->assertSame('Research Award', $sheet->getCell('AW2')->getValue());

            $merges = $sheet->getMergeCells();
            $this->assertArrayHasKey('A1:D1', $merges);
            $this->assertArrayHasKey('AI1:AP1', $merges);
            $this->assertArrayHasKey('AQ1:AU1', $merges);
        } finally {
            @unlink($response->getFile()->getPathname());
            $book->disconnectWorksheets();
        }
    }

    public function test_export_of_one_category_writes_a_single_worksheet()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        foreach (['regular' => 'Regular Grants', 'student' => 'Student Grants'] as $category => $sheetName) {
            $response = $this->actingAs($admin)
                ->get(route('reports.grading-details-export', ['category' => $category]));
            $response->assertOk();

            $book = $this->loadWorkbook($response);

            try {
                $this->assertSame([$sheetName], $book->getSheetNames());

                $expected = $category === 'student' ? 'Student' : 'Regular';
                foreach (array_slice($book->getSheet(0)->toArray(), 2) as $row) {
                    $value = $row[2] ?? '';
                    if ($value !== '' && $value !== null) {
                        $this->assertSame($expected, $value);
                    }
                }
            } finally {
                @unlink($response->getFile()->getPathname());
                $book->disconnectWorksheets();
            }
        }
    }

    public function test_column_selection_is_remembered_and_scopes_the_export()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $page = route('reports.grading-details');

        $this->actingAs($admin)
            ->get($page . '?fields[]=project_id&fields[]=title&fields[]=p1_decision&fields[]=pub_q1')
            ->assertRedirect();

        $html = $this->actingAs($admin)->get($page)->assertOk()->getContent();

        $total = count(\App\Services\GradingDetailsReportService::allFields());

        $this->assertStringContainsString('4 / ' . $total, $html);
        $this->assertStringContainsString('4 of ' . $total . ' columns', $html);

        $start = strpos($html, '<table');
        $table = substr($html, $start, strpos($html, '</table>', $start) - $start);

        $this->assertStringNotContainsString('Final Grading', $table);
        $this->assertStringNotContainsString('Provisional Patent', $table);
        $this->assertStringNotContainsString('Achievements', $table);

        $response = $this->actingAs($admin)->get(route('reports.grading-details-export', ['category' => 'regular']));
        $response->assertOk();

        $book = $this->loadWorkbook($response);

        try {
            $sheet = $book->getSheet(0);

            $this->assertSame('Project', $sheet->getCell('A1')->getValue());
            $this->assertSame('PR 1', $sheet->getCell('C1')->getValue());
            $this->assertSame('Publications', $sheet->getCell('D1')->getValue());
            $this->assertSame('Project ID', $sheet->getCell('A2')->getValue());
            $this->assertSame('Decision', $sheet->getCell('C2')->getValue());
            $this->assertSame('Q1', $sheet->getCell('D2')->getValue());
            $this->assertSame('D', $sheet->getHighestColumn());
        } finally {
            @unlink($response->getFile()->getPathname());
            $book->disconnectWorksheets();
        }
    }

    public function test_student_category_uses_the_same_columns_as_regular()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $student = $this->actingAs($admin)
            ->get(route('reports.grading-details', ['category' => 'student']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('49 / ' . count(\App\Services\GradingDetailsReportService::allFields()), $student);
        $this->assertStringContainsString('49 of ' . count(\App\Services\GradingDetailsReportService::allFields()) . ' columns selected', $student);
        $this->assertStringContainsString('data-category="student"', $student);

        $pickerStart = strpos($student, 'id="fieldPickerBody"');
        $picker = substr($student, $pickerStart, strpos($student, 'id="fieldPickerStatus"', $pickerStart) - $pickerStart);

        foreach (['Project ID', 'PR 1', 'PR 2', 'Final Grading', 'Achievements', 'Provisional Patent', 'Q1', 'Research Award'] as $needle) {
            $this->assertStringContainsString($needle, $picker);
        }

        // Student grants are graded/report outcomes through the same tables, so
        // the rendered table has exactly the same header as the regular one.
        $regular = $this->actingAs($admin)
            ->get(route('reports.grading-details', ['category' => 'regular']))
            ->assertOk()
            ->getContent();

        $studentStart = strpos($student, '<thead>', strpos($student, 'id="gradingDetailsTable"'));
        $studentHead = substr($student, $studentStart, strpos($student, '</thead>', $studentStart) - $studentStart);

        $regularStart = strpos($regular, '<thead>', strpos($regular, 'id="gradingDetailsTable"'));
        $regularHead = substr($regular, $regularStart, strpos($regular, '</thead>', $regularStart) - $regularStart);

        $this->assertSame($regularHead, $studentHead, 'Both categories must render the same columns.');
        $this->assertStringContainsString('Achievements', $studentHead);
        $this->assertStringContainsString('Total', $studentHead);
    }

    public function test_column_selection_is_remembered_per_category()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $page = route('reports.grading-details');

        $this->actingAs($admin)
            ->get($page . '?category=student&fields[]=project_id&fields[]=title')
            ->assertRedirect();

        $student = $this->actingAs($admin)
            ->get($page . '?category=student')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('2 / ' . count(\App\Services\GradingDetailsReportService::allFields()), $student);

        // Regular keeps its own (full) selection.
        $regular = $this->actingAs($admin)
            ->get($page . '?category=regular')
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(
            count(\App\Services\GradingDetailsReportService::defaultFields()) . ' / ' . count(\App\Services\GradingDetailsReportService::allFields()),
            $regular
        );

        // The student export follows the student selection.
        $response = $this->actingAs($admin)
            ->get(route('reports.grading-details-export', ['category' => 'student']));
        $response->assertOk();

        $book = $this->loadWorkbook($response);

        try {
            $sheet = $book->getSheet(0);

            $this->assertSame('Project', $sheet->getCell('A1')->getValue());
            $this->assertSame('Project ID', $sheet->getCell('A2')->getValue());
            $this->assertSame('Project Title', $sheet->getCell('B2')->getValue());
            $this->assertSame('B', $sheet->getHighestColumn());
        } finally {
            @unlink($response->getFile()->getPathname());
            $book->disconnectWorksheets();
        }
    }

    public function test_cycles_are_scoped_to_the_selected_category()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        foreach (['student' => true, 'regular' => false] as $category => $isStudent) {
            $html = $this->actingAs($admin)
                ->get(route('reports.grading-details', ['category' => $category]))
                ->assertOk()
                ->getContent();

            $options = array_map('strval', array_keys($this->selectOptions($html, 'cycle_id')));
            array_shift($options); // "All cycles"
            sort($options);

            $this->assertSame(
                $this->cycleIdsFor($isStudent),
                $options,
                'Cycle options must only list cycles that hold ' . $category . ' projects.'
            );
        }
    }

    public function test_research_calls_follow_the_selected_cycle()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $studentCycles = $this->cycleIdsFor(true);
        if (!$studentCycles) {
            $this->markTestSkipped('No student cycle.');
        }

        $cycleId = (int) $studentCycles[0];

        $expected = $this->projects()
            ->filter(fn ($p) => $p->isStudentProject() && (int) ($p->program->cycle_id ?? 0) === $cycleId)
            ->map(fn ($p) => (string) $p->program_id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $html = $this->actingAs($admin)
            ->get(route('reports.grading-details', ['category' => 'student', 'cycle_id' => $cycleId]))
            ->assertOk()
            ->getContent();

        $options = array_map('strval', array_keys($this->selectOptions($html, 'program_id')));
        array_shift($options); // "All research calls"
        sort($options);

        $this->assertSame($expected, $options);

        // A research call from another cycle is not offered.
        $otherCycleProgram = $this->projects()
            ->first(fn ($p) => $p->program
                && (int) $p->program->cycle_id !== $cycleId
                && $p->isStudentProject());

        if ($otherCycleProgram) {
            $this->assertNotContains((string) $otherCycleProgram->program_id, $options);
        }
    }

    public function test_grants_follow_the_selected_research_call()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $studentCycles = $this->cycleIdsFor(true);
        if (!$studentCycles) {
            $this->markTestSkipped('No student cycle.');
        }

        $cycleId = (int) $studentCycles[0];

        $program = $this->projects()
            ->first(fn ($p) => $p->isStudentProject() && (int) ($p->program->cycle_id ?? 0) === $cycleId);

        if (!$program || !$program->program->grant_id) {
            $this->markTestSkipped('No student research call with a grant.');
        }

        $html = $this->actingAs($admin)
            ->get(route('reports.grading-details', [
                'category'   => 'student',
                'cycle_id'   => $cycleId,
                'program_id' => $program->program_id,
            ]))
            ->assertOk()
            ->getContent();

        $options = array_map('strval', array_keys($this->selectOptions($html, 'grant_id')));
        array_shift($options); // "All grants"

        $this->assertSame([(string) $program->program->grant_id], $options);
    }

    public function test_child_filters_outside_the_parent_scope_are_reset()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $studentCycles = $this->cycleIdsFor(true);
        $regularOnlyCycle = $this->cycleIdsFor(false)
            ? array_values(array_diff($this->cycleIdsFor(false), $studentCycles))
            : [];

        if (!$studentCycles || !$regularOnlyCycle) {
            $this->markTestSkipped('Need a student cycle and a regular-only cycle.');
        }

        // A regular-only cycle inside the student category is dropped.
        $response = $this->actingAs($admin)->get(route('reports.grading-details', [
            'category' => 'student',
            'cycle_id' => $regularOnlyCycle[0],
        ]));

        $response->assertRedirect();
        $this->assertStringNotContainsString('cycle_id=' . $regularOnlyCycle[0], (string) $response->headers->get('Location'));

        // A regular-only research call inside a student cycle is dropped too.
        $regularProgram = $this->projects()
            ->first(fn ($p) => !$p->isStudentProject() && $p->program
                && (int) $p->program->cycle_id === (int) $studentCycles[0]);

        if ($regularProgram) {
            $response = $this->actingAs($admin)->get(route('reports.grading-details', [
                'category'   => 'student',
                'cycle_id'   => $studentCycles[0],
                'program_id' => $regularProgram->program_id,
            ]));

            $response->assertRedirect();
            $this->assertStringNotContainsString(
                'program_id=' . $regularProgram->program_id,
                (string) $response->headers->get('Location')
            );
        }
    }

    public function test_raw_progress_final_and_outcome_table_columns_are_available()
    {
        $fields = \App\Services\GradingDetailsReportService::allFields();

        $expected = [
            // progress_report_grading
            'p1t_publish', 'p1t_report_type', 'p1t_is_accepted',
            'p1t_ach_rating', 'p1t_ach_comments', 'p1t_pub_rating', 'p1t_pub_comments',
            'p1t_stu_rating', 'p1t_stu_comments', 'p1t_bud_rating', 'p1t_bud_comments',
            'p1t_ethical', 'p1t_analysis', 'p1t_recommendation', 'p1t_comments',
            'p1t_rejection_type', 'p1t_rejection_reason', 'p1t_graded_at',
            // final_report_grading
            'ft_publish', 'ft_grade_a', 'ft_score_a', 'ft_auto_grade_a', 'ft_comment_a',
            'ft_comment_d', 'ft_total', 'ft_rejection_type', 'ft_rejection_reason',
            'ft_included_items', 'ft_graded_at',
            // project_outcomes
            'ot_type', 'ot_identifier', 'ot_online_date',
            'ot_verified_system', 'ot_verified_reviewer', 'ot_score',
        ];

        foreach ($expected as $key) {
            $this->assertContains($key, $fields, "Raw column [{$key}] is missing from the report.");
        }

        // The summary columns stay the default selection; raw ones are opt-in.
        $defaults = \App\Services\GradingDetailsReportService::defaultFields();

        $this->assertContains('p1_decision', $defaults);
        $this->assertNotContains('p1t_publish', $defaults);
        $this->assertGreaterThan(count($defaults), count($fields));

        $data = \App\Services\GradingDetailsReportService::build(['category' => 'regular']);

        $graded = collect($data['regular'])->first(fn ($r) => ($r['p1t_publish'] ?? '') !== '');
        $this->assertNotNull($graded, 'No regular project has progress grading rows.');

        // Reviewers stay numbered, never named.
        $this->assertStringContainsString('Reviewer', $graded['p1t_publish']);
        $this->assertDoesNotMatchRegularExpression('/\b(?:Dr\.?|Prof\.?)\s/', $graded['p1t_publish']);
        $this->assertNotSame('', $graded['p1t_graded_at']);

        $outcomes = collect($data['regular'])->first(fn ($r) => ($r['ot_type'] ?? '') !== '');
        $this->assertNotNull($outcomes, 'No regular project has outcomes.');

        $this->assertNotSame('', $outcomes['ot_type']);
        $this->assertNotSame('', $outcomes['ot_score']);
        $this->assertStringContainsString(':', $outcomes['ot_identifier']);
    }

    public function test_raw_table_columns_are_listed_in_the_column_picker()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $html = $this->actingAs($admin)
            ->get(route('reports.grading-details'))
            ->assertOk()
            ->getContent();

        foreach (['PR 1 – Grading Table', 'PR 2 – Grading Table', 'Final – Grading Table', 'Outcomes – Table'] as $group) {
            $this->assertStringContainsString($group, $html);
        }

        foreach (['Achievements Rating', 'Auto Grade A', 'System Verification', 'Rejection Reason'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
    }

    public function test_student_export_keeps_the_full_grading_header()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin.');
        }

        $response = $this->actingAs($admin)
            ->get(route('reports.grading-details-export', ['category' => 'student']));
        $response->assertOk();

        $book = $this->loadWorkbook($response);

        try {
            $this->assertSame(['Student Grants'], $book->getSheetNames());

            $sheet = $book->getSheet(0);

            $this->assertSame('Project', $sheet->getCell('A1')->getValue());
            $this->assertSame('PR 1', $sheet->getCell('J1')->getValue());
            $this->assertSame('Final Grading', $sheet->getCell('AB1')->getValue());
            $this->assertSame('Publications', $sheet->getCell('AI1')->getValue());
            $this->assertSame('Intellectual Property', $sheet->getCell('AQ1')->getValue());
            $this->assertSame('Achievements', $sheet->getCell('K2')->getValue());
            $this->assertSame('Research Award', $sheet->getCell('AW2')->getValue());
            $this->assertSame('AW', $sheet->getHighestColumn());

            $merges = $sheet->getMergeCells();
            $this->assertArrayHasKey('A1:D1', $merges);
            $this->assertArrayHasKey('AI1:AP1', $merges);
            $this->assertArrayHasKey('AQ1:AU1', $merges);
        } finally {
            @unlink($response->getFile()->getPathname());
            $book->disconnectWorksheets();
        }
    }
}
