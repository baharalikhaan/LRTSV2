<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * "Grading Details" report.
 *
 * One row per project, with:
 *   - the project identity (ID, title, research call, grant, cycle, LPI, status),
 *   - the reviewer feedback of every stage (Progress Report 1, Progress Report 2,
 *     Final Report): decision + scores + comments, reviewers numbered only
 *     ("Reviewer 1/2/…") — never named,
 *   - the recorded outcomes, split by type (Q1–Q4, conference, books, IP,
 *     patents, software, start-ups, cross-college, awards).
 *
 * Student-grant projects are kept apart from regular ones: the screen shows two
 * sections and the Excel export writes two worksheets ("Regular Grants" and
 * "Student Grants"), each with a two-level (grouped) header.
 */
class GradingDetailsReportService
{
    public const PUBLICATION_TYPES = [
        'journal_q1', 'journal_q2', 'journal_q3', 'journal_q4',
        'conference', 'book', 'edited_book', 'book_chapter',
    ];

    public const OUTCOME_LABELS = [
        'journal_q1'         => 'Q1 Journal',
        'journal_q2'         => 'Q2 Journal',
        'journal_q3'         => 'Q3 Journal',
        'journal_q4'         => 'Q4 Journal',
        'conference'         => 'Conference Paper',
        'book'               => 'Book',
        'edited_book'        => 'Edited Book',
        'book_chapter'       => 'Book Chapter',
        'ip_disclosure'      => 'IP Disclosure',
        'provisional_patent' => 'Provisional Patent',
        'patent_granted'     => 'Granted Patent',
        'open_source_sw'     => 'Open-source Software',
        'startup'            => 'Start-up',
        'cross_college'      => 'Cross-college Output',
        'research_awards'    => 'Research Award',
        'publication'        => 'Publication',
        'patent'             => 'Patent',
        'presentation'       => 'Presentation',
        'award'              => 'Award',
        'grant'              => 'Grant',
        'thesis'             => 'Thesis',
        'other'              => 'Other',
    ];

    /**
     * Two-level header: group label → [column key, sub-column label, width].
     * A group with more than one column is merged across its sub-columns in the
     * exported workbook; simple groups (identity) get their own label only.
     *
     * These are the summary columns (the default selection); the raw columns of
     * the grading / outcome tables live in TABLE_GROUPS below.
     */
    public const SUMMARY_GROUPS = [
        'Project' => [
            ['project_id', 'Project ID', 22],
            ['title', 'Project Title', 40],
            ['category', 'Category', 12],
            ['status', 'Workflow Status', 20],
        ],
        'Research Call / Grant' => [
            ['research_call', 'Research Call', 26],
            ['grant', 'Grant', 26],
            ['cycle', 'Cycle', 10],
            ['lpi', 'LPI', 22],
            ['lpi_email', 'LPI Email', 26],
        ],
        'PR 1' => [
            ['p1_decision', 'Decision', 20],
            ['p1_achievements', 'Achievements', 34],
            ['p1_publications', 'Publications', 34],
            ['p1_students', 'Students', 34],
            ['p1_budget', 'Budget', 34],
            ['p1_ethical', 'Ethical', 14],
            ['p1_analysis', 'Analysis', 34],
            ['p1_recommendation', 'Recommendation', 34],
            ['p1_comments', 'Comments', 40],
        ],
        'PR 2' => [
            ['p2_decision', 'Decision', 20],
            ['p2_achievements', 'Achievements', 34],
            ['p2_publications', 'Publications', 34],
            ['p2_students', 'Students', 34],
            ['p2_budget', 'Budget', 34],
            ['p2_ethical', 'Ethical', 14],
            ['p2_analysis', 'Analysis', 34],
            ['p2_recommendation', 'Recommendation', 34],
            ['p2_comments', 'Comments', 40],
        ],
        'Final Grading' => [
            ['f_decision', 'Decision', 20],
            ['f_grade_a', 'A', 10],
            ['f_grade_b', 'B', 10],
            ['f_grade_c', 'C', 10],
            ['f_grade_d', 'D', 10],
            ['f_total', 'Total', 10],
            ['f_comments', 'Comments', 45],
        ],
        'Publications' => [
            ['pub_q1', 'Q1', 30],
            ['pub_q2', 'Q2', 30],
            ['pub_q3', 'Q3', 30],
            ['pub_q4', 'Q4', 30],
            ['pub_conference', 'Conference', 30],
            ['pub_book', 'Book', 26],
            ['pub_edited_book', 'Edited Book', 26],
            ['pub_book_chapter', 'Chapter', 26],
        ],
        'Intellectual Property' => [
            ['ip_disclosure', 'IP Disclosure', 26],
            ['ip_provisional', 'Provisional Patent', 26],
            ['ip_granted', 'Granted Patent', 26],
            ['ip_software', 'Open-source SW', 26],
            ['ip_startup', 'Start-up', 24],
        ],
        'Other Outputs' => [
            ['other_cross_college', 'Cross-college', 24],
            ['other_awards', 'Research Award', 24],
        ],
    ];

    /**
     * Raw columns of the three source tables, so every value the grading and
     * outcome tables hold can be reported (the summary groups above merge some
     * of them into one cell). Selectable from the column picker, and kept out
     * of the default selection so the initial view stays compact.
     */
    public const TABLE_GROUPS = [
        'PR 1 – Grading Table' => [
            ['p1t_publish', 'Publish', 14],
            ['p1t_report_type', 'Report Type', 14],
            ['p1t_is_accepted', 'Accepted Flag', 12],
            ['p1t_ach_rating', 'Achievements Rating', 12],
            ['p1t_ach_comments', 'Achievements Comments', 34],
            ['p1t_pub_rating', 'Publications Rating', 12],
            ['p1t_pub_comments', 'Publications Comments', 34],
            ['p1t_stu_rating', 'Students Rating', 12],
            ['p1t_stu_comments', 'Students Comments', 34],
            ['p1t_bud_rating', 'Budget Rating', 12],
            ['p1t_bud_comments', 'Budget Comments', 34],
            ['p1t_ethical', 'Ethical', 14],
            ['p1t_analysis', 'Analysis', 30],
            ['p1t_recommendation', 'Recommendation', 30],
            ['p1t_comments', 'Comments', 34],
            ['p1t_rejection_type', 'Rejection Type', 14],
            ['p1t_rejection_reason', 'Rejection Reason', 34],
            ['p1t_graded_at', 'Graded At', 18],
        ],
        'PR 2 – Grading Table' => [
            ['p2t_publish', 'Publish', 14],
            ['p2t_report_type', 'Report Type', 14],
            ['p2t_is_accepted', 'Accepted Flag', 12],
            ['p2t_ach_rating', 'Achievements Rating', 12],
            ['p2t_ach_comments', 'Achievements Comments', 34],
            ['p2t_pub_rating', 'Publications Rating', 12],
            ['p2t_pub_comments', 'Publications Comments', 34],
            ['p2t_stu_rating', 'Students Rating', 12],
            ['p2t_stu_comments', 'Students Comments', 34],
            ['p2t_bud_rating', 'Budget Rating', 12],
            ['p2t_bud_comments', 'Budget Comments', 34],
            ['p2t_ethical', 'Ethical', 14],
            ['p2t_analysis', 'Analysis', 30],
            ['p2t_recommendation', 'Recommendation', 30],
            ['p2t_comments', 'Comments', 34],
            ['p2t_rejection_type', 'Rejection Type', 14],
            ['p2t_rejection_reason', 'Rejection Reason', 34],
            ['p2t_graded_at', 'Graded At', 18],
        ],
        'Final – Grading Table' => [
            ['ft_publish', 'Publish', 14],
            ['ft_grade_a', 'Grade A', 10],
            ['ft_grade_b', 'Grade B', 10],
            ['ft_grade_c', 'Grade C', 10],
            ['ft_grade_d', 'Grade D', 10],
            ['ft_score_a', 'Score A', 10],
            ['ft_score_b', 'Score B', 10],
            ['ft_score_c', 'Score C', 10],
            ['ft_auto_grade_a', 'Auto Grade A', 12],
            ['ft_auto_grade_b', 'Auto Grade B', 12],
            ['ft_auto_grade_c', 'Auto Grade C', 12],
            ['ft_comment_a', 'Comment A', 30],
            ['ft_comment_b', 'Comment B', 30],
            ['ft_comment_c', 'Comment C', 30],
            ['ft_comment_d', 'Comment D', 30],
            ['ft_total', 'Total', 10],
            ['ft_rejection_type', 'Rejection Type', 14],
            ['ft_rejection_reason', 'Rejection Reason', 34],
            ['ft_included_items', 'Included Items', 30],
            ['ft_graded_at', 'Graded At', 18],
        ],
        'Outcomes – Table' => [
            ['ot_type', 'Type', 18],
            ['ot_identifier', 'Identifier', 34],
            ['ot_online_date', 'Online Date', 14],
            ['ot_verified_system', 'System Verification', 16],
            ['ot_verified_reviewer', 'Reviewer Verification', 16],
            ['ot_score', 'Score', 10],
        ],
    ];

    /** Summary groups followed by the raw table groups. */
    public static function groups(): array
    {
        return self::SUMMARY_GROUPS + self::TABLE_GROUPS;
    }

    /** Flat list of [key, label, width] in worksheet order. */
    public static function columns(): array
    {
        $columns = [];
        foreach (self::groups() as $group) {
            foreach ($group as $column) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /** The default selection: the summary groups only. */
    public static function defaultFields(): array
    {
        return self::fieldsOf(self::SUMMARY_GROUPS);
    }

    /** Every selectable column key (summary + raw table columns). */
    public static function allFields(): array
    {
        return self::fieldsOf(self::groups());
    }

    private static function fieldsOf(array $groups): array
    {
        $fields = [];

        foreach ($groups as $columns) {
            foreach ($columns as $column) {
                $fields[] = $column[0];
            }
        }

        return $fields;
    }

    /** A leaner default selection offered by the "Recommended" button. */
    public static function recommendedFields(): array
    {
        $always = [
            'project_id', 'title', 'category', 'research_call', 'grant', 'lpi', 'status',
            'p1_decision', 'p2_decision', 'f_decision', 'f_total',
        ];

        $outcomes = [
            'pub_q1', 'pub_q2', 'pub_q3', 'pub_q4', 'pub_conference', 'pub_book',
            'pub_edited_book', 'pub_book_chapter',
            'ip_disclosure', 'ip_provisional', 'ip_granted', 'ip_software', 'ip_startup',
            'other_cross_college', 'other_awards',
        ];

        return array_values(array_intersect(self::defaultFields(), array_merge($always, $outcomes)));
    }

    /**
     * GROUPS filtered to the selected column keys (empty groups are dropped).
     *
     * @param  string[]  $selectedFields
     */
    public static function groupsFor(array $selectedFields = []): array
    {
        if (empty($selectedFields)) {
            return self::groups();
        }

        $groups = [];

        foreach (self::groups() as $label => $columns) {
            $kept = array_values(array_filter(
                $columns,
                fn ($column) => in_array($column[0], $selectedFields, true)
            ));

            if ($kept) {
                $groups[$label] = $kept;
            }
        }

        return $groups;
    }

    // ------------------------------------------------------------------
    // data
    // ------------------------------------------------------------------

    /**
     * @return array{regular: array, student: array, totals: array, filters: array}
     */
    public static function build(array $filters = []): array
    {
        $query = Project::with([
            'program.grant',
            'program.cycle',
            'lpi',
            'progressGradings.user',
            'progress2Gradings.user',
            'finalGradings.user',
            'outcomes.publication',
        ]);

        if (!empty($filters['cycle_id'])) {
            $query->whereHas('program', fn ($q) => $q->where('cycle_id', $filters['cycle_id']));
        }

        if (!empty($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }

        if (!empty($filters['grant_id'])) {
            $query->whereHas('program', fn ($q) => $q->where('grant_id', $filters['grant_id']));
        }

        if (!empty($filters['q'])) {
            $term = '%' . $filters['q'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('old_project_id', 'like', $term)
                    ->orWhere('title', 'like', $term);
            });
        }

        $projects = $query
            ->orderByRaw('(SELECT program_title FROM programs WHERE programs.id = projects.program_id)')
            ->orderBy('projects.id')
            ->get();

        $regular = [];
        $student = [];
        $all = [];

        foreach ($projects as $project) {
            $isStudent = $project->isStudentProject();

            if (($filters['category'] ?? 'all') === 'regular' && $isStudent) {
                continue;
            }
            if (($filters['category'] ?? 'all') === 'student' && !$isStudent) {
                continue;
            }

            $row = self::row($project);
            $all[] = $row;

            if ($isStudent) {
                $student[] = $row;
            } else {
                $regular[] = $row;
            }
        }

        return [
            'regular' => $regular,
            'student' => $student,
            'all'     => $all,
            'totals'  => ['regular' => count($regular), 'student' => count($student)],
            'filters' => $filters,
        ];
    }

    private static function row(Project $project): array
    {
        $program = $project->program;
        $grant = $program->grant ?? null;
        $cycle = $program->cycle ?? null;
        $status = $project->currentWorkflowStatus();
        $labels = Project::statusLabels();

        $progress1 = $project->progressGradings
            ->filter(fn ($g) => ($g->report_type ?? 'progress') === 'progress');
        $progress2 = $project->progress2Gradings;
        $final = $project->finalGradings;

        // Reviewers are numbered per project (id order), never named.
        $reviewerIds = $progress1->pluck('user_id')
            ->merge($progress2->pluck('user_id'))
            ->merge($final->pluck('user_id'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $indexMap = [];
        foreach ($reviewerIds as $i => $userId) {
            $indexMap[$userId] = $i + 1;
        }

        $p1 = self::progressStage($progress1, $indexMap);
        $p2 = self::progressStage($progress2, $indexMap);
        $f = self::finalStage($final, $indexMap);

        [$publications, $ip, $other] = self::outcomes($project->outcomes);
        $outcomeTable = self::outcomeTable($project->outcomes);

        $row = [
            'project_id'    => $project->old_project_id ?: $project->id,
            'title'         => $project->title,
            'category'      => $project->isStudentProject() ? 'Student' : 'Regular',
            'research_call' => $program->program_title ?? '',
            'grant'         => $grant ? trim(($grant->grant_code ?? '') . ' — ' . ($grant->grant_name ?? ''), ' —') : '',
            'cycle'         => $cycle->year ?? ($cycle->title ?? ''),
            'lpi'           => $project->lpi->name ?? '',
            'lpi_email'     => $project->lpi->email ?? '',
            'status'        => $labels[$status] ?? ($status ? ucfirst(str_replace('_', ' ', $status)) : ''),
            'status_raw'    => $status,

            'p1_decision'       => $p1['decision'],
            'p1_achievements'   => $p1['achievements'],
            'p1_publications'   => $p1['publications'],
            'p1_students'       => $p1['students'],
            'p1_budget'         => $p1['budget'],
            'p1_ethical'        => $p1['ethical'],
            'p1_analysis'       => $p1['analysis'],
            'p1_recommendation' => $p1['recommendation'],
            'p1_comments'       => $p1['comments'],

            'p2_decision'       => $p2['decision'],
            'p2_achievements'   => $p2['achievements'],
            'p2_publications'   => $p2['publications'],
            'p2_students'       => $p2['students'],
            'p2_budget'         => $p2['budget'],
            'p2_ethical'        => $p2['ethical'],
            'p2_analysis'       => $p2['analysis'],
            'p2_recommendation' => $p2['recommendation'],
            'p2_comments'       => $p2['comments'],

            'f_decision' => $f['decision'],
            'f_grade_a'  => $f['a'],
            'f_grade_b'  => $f['b'],
            'f_grade_c'  => $f['c'],
            'f_grade_d'  => $f['d'],
            'f_total'    => $f['total'],
            'f_comments' => $f['comments'],

            'pub_q1'          => $publications['journal_q1'] ?? '',
            'pub_q2'          => $publications['journal_q2'] ?? '',
            'pub_q3'          => $publications['journal_q3'] ?? '',
            'pub_q4'          => $publications['journal_q4'] ?? '',
            'pub_conference'  => $publications['conference'] ?? '',
            'pub_book'        => $publications['book'] ?? '',
            'pub_edited_book' => $publications['edited_book'] ?? '',
            'pub_book_chapter' => $publications['book_chapter'] ?? '',

            'ip_disclosure'  => $ip['ip_disclosure'] ?? '',
            'ip_provisional' => $ip['provisional_patent'] ?? '',
            'ip_granted'     => $ip['patent_granted'] ?? '',
            'ip_software'    => $ip['open_source_sw'] ?? '',
            'ip_startup'     => $ip['startup'] ?? '',

            'other_cross_college' => $other['cross_college'] ?? '',
            'other_awards'        => $other['research_awards'] ?? '',
        ];

        // Raw progress_report_grading columns, per reviewer.
        foreach (self::PROGRESS_TABLE_KEYS as $key) {
            $row['p1t_' . $key] = $p1[$key] ?? '';
            $row['p2t_' . $key] = $p2[$key] ?? '';
        }

        // Raw final_report_grading columns, per reviewer.
        foreach (self::FINAL_TABLE_KEYS as $key) {
            $row['ft_' . $key] = $f[$key] ?? '';
        }

        // Raw project_outcomes columns, one line per outcome.
        foreach (self::OUTCOME_TABLE_KEYS as $key) {
            $row['ot_' . $key] = $outcomeTable[$key] ?? '';
        }

        return $row;
    }

    /** Raw progress_report_grading fields exposed as columns (suffix after p1t_/p2t_). */
    private const PROGRESS_TABLE_KEYS = [
        'publish', 'report_type', 'is_accepted',
        'ach_rating', 'ach_comments', 'pub_rating', 'pub_comments',
        'stu_rating', 'stu_comments', 'bud_rating', 'bud_comments',
        'ethical', 'analysis', 'recommendation', 'comments',
        'rejection_type', 'rejection_reason', 'graded_at',
    ];

    /** Raw final_report_grading fields exposed as columns (suffix after ft_). */
    private const FINAL_TABLE_KEYS = [
        'publish', 'grade_a', 'grade_b', 'grade_c', 'grade_d',
        'score_a', 'score_b', 'score_c',
        'auto_grade_a', 'auto_grade_b', 'auto_grade_c',
        'comment_a', 'comment_b', 'comment_c', 'comment_d',
        'total', 'rejection_type', 'rejection_reason', 'included_items', 'graded_at',
    ];

    /** Raw project_outcomes fields exposed as columns (suffix after ot_). */
    private const OUTCOME_TABLE_KEYS = [
        'type', 'identifier', 'online_date', 'verified_system', 'verified_reviewer', 'score',
    ];

    /**
     * One progress stage's feedback, split per graded item. Reviewers are
     * identified by their number only ("Reviewer 1"), never by name or e-mail.
     *
     * @return array<string,string>
     */
    private static function progressStage(Collection $gradings, array $indexMap): array
    {
        $cells = [
            'decision' => [], 'achievements' => [], 'publications' => [], 'students' => [],
            'budget' => [], 'ethical' => [], 'analysis' => [], 'recommendation' => [], 'comments' => [],
            'publish' => [], 'report_type' => [], 'is_accepted' => [],
            'ach_rating' => [], 'ach_comments' => [], 'pub_rating' => [], 'pub_comments' => [],
            'stu_rating' => [], 'stu_comments' => [], 'bud_rating' => [], 'bud_comments' => [],
            'rejection_type' => [], 'rejection_reason' => [], 'graded_at' => [],
        ];

        foreach ($gradings as $g) {
            $who = self::who($g, $indexMap);

            $cells['decision'][]     = $who . ': ' . self::decisionLabel($g);
            $cells['achievements'][] = self::rated($who, $g->achievementsRating, $g->achievementsComments);
            $cells['publications'][] = self::rated($who, $g->publicationsRating, $g->publicationsComments);
            $cells['students'][]     = self::rated($who, $g->studentsRating, $g->studentsComments);
            $cells['budget'][]       = self::rated($who, $g->budgetRating, $g->budgetComments);
            $cells['ethical'][]      = $who . ': ' . ($g->ethical === null ? '—' : ($g->ethical ? 'Yes' : 'No'));

            // Raw table values (split ratings/comments, workflow fields).
            $cells['publish'][]       = $who . ': ' . self::decisionLabel($g);
            $cells['report_type'][]   = $who . ': ' . ($g->report_type ?: '—');
            $cells['is_accepted'][]   = $who . ': ' . ($g->isAccepted === null ? '—' : ($g->isAccepted ? 'Yes' : 'No'));
            $cells['ach_rating'][]    = $who . ': ' . self::n($g->achievementsRating);
            $cells['ach_comments'][]  = $g->achievementsComments ? $who . ': ' . $g->achievementsComments : null;
            $cells['pub_rating'][]    = $who . ': ' . self::n($g->publicationsRating);
            $cells['pub_comments'][]  = $g->publicationsComments ? $who . ': ' . $g->publicationsComments : null;
            $cells['stu_rating'][]    = $who . ': ' . self::n($g->studentsRating);
            $cells['stu_comments'][]  = $g->studentsComments ? $who . ': ' . $g->studentsComments : null;
            $cells['bud_rating'][]    = $who . ': ' . self::n($g->budgetRating);
            $cells['bud_comments'][]  = $g->budgetComments ? $who . ': ' . $g->budgetComments : null;

            if ($g->rejection_type) {
                $cells['rejection_type'][] = $who . ': ' . ucfirst(str_replace('_', ' ', $g->rejection_type));
            }
            if ($g->rejection_reason) {
                $cells['rejection_reason'][] = $who . ': ' . $g->rejection_reason;
            }
            if ($g->created_at) {
                $cells['graded_at'][] = $who . ': ' . $g->created_at->format('d M Y, H:i');
            }

            if ($g->analysis) {
                $cells['analysis'][] = $who . ': ' . $g->analysis;
            }
            if ($g->recommendation) {
                $cells['recommendation'][] = $who . ': ' . $g->recommendation;
            }
            if ($g->comments) {
                $cells['comments'][] = $who . ': ' . $g->comments;
            }
        }

        return array_map(fn ($lines) => self::joinLines($lines), $cells);
    }

    /** Join a cell's lines, dropping the empty ones. */
    private static function joinLines(array $lines): string
    {
        return implode("\n", array_filter($lines, fn ($line) => $line !== null && $line !== ''));
    }

    /**
     * Final report feedback: one column per grade (A–D), plus total and comments.
     *
     * @return array<string,string>
     */
    private static function finalStage(Collection $gradings, array $indexMap): array
    {
        $cells = [
            'decision' => [], 'a' => [], 'b' => [], 'c' => [], 'd' => [], 'total' => [], 'comments' => [],
            'publish' => [], 'grade_a' => [], 'grade_b' => [], 'grade_c' => [], 'grade_d' => [],
            'score_a' => [], 'score_b' => [], 'score_c' => [],
            'auto_grade_a' => [], 'auto_grade_b' => [], 'auto_grade_c' => [],
            'comment_a' => [], 'comment_b' => [], 'comment_c' => [], 'comment_d' => [],
            'rejection_type' => [], 'rejection_reason' => [], 'included_items' => [], 'graded_at' => [],
        ];

        foreach ($gradings as $g) {
            $who = self::who($g, $indexMap);

            $cells['decision'][] = $who . ': ' . self::decisionLabel($g);
            $cells['a'][] = $who . ': ' . self::n($g->gradeA);
            $cells['b'][] = $who . ': ' . self::n($g->gradeB);
            $cells['c'][] = $who . ': ' . self::n($g->gradeC);
            $cells['d'][] = $who . ': ' . self::n($g->gradeD);

            // Raw final_report_grading columns.
            $cells['publish'][]    = $who . ': ' . self::decisionLabel($g);
            $cells['grade_a'][]    = $who . ': ' . self::n($g->gradeA);
            $cells['grade_b'][]    = $who . ': ' . self::n($g->gradeB);
            $cells['grade_c'][]    = $who . ': ' . self::n($g->gradeC);
            $cells['grade_d'][]    = $who . ': ' . self::n($g->gradeD);
            $cells['score_a'][]    = $who . ': ' . self::n($g->scoreA);
            $cells['score_b'][]    = $who . ': ' . self::n($g->scoreB);
            $cells['score_c'][]    = $who . ': ' . self::n($g->scoreC);
            $cells['auto_grade_a'][] = $who . ': ' . self::n($g->autoGradeA);
            $cells['auto_grade_b'][] = $who . ': ' . self::n($g->autoGradeB);
            $cells['auto_grade_c'][] = $who . ': ' . self::n($g->autoGradeC);
            $cells['comment_a'][]  = $g->commentA ? $who . ': ' . $g->commentA : null;
            $cells['comment_b'][]  = $g->commentB ? $who . ': ' . $g->commentB : null;
            $cells['comment_c'][]  = $g->commentC ? $who . ': ' . $g->commentC : null;
            $cells['comment_d'][]  = $g->commentD ? $who . ': ' . $g->commentD : null;

            if ($g->rejection_type) {
                $cells['rejection_type'][] = $who . ': ' . ucfirst(str_replace('_', ' ', $g->rejection_type));
            }
            if ($g->rejection_reason) {
                $cells['rejection_reason'][] = $who . ': ' . $g->rejection_reason;
            }
            if ($g->included_items) {
                $items = $g->included_items;
                $text = is_array($items)
                    ? implode(', ', array_map(fn ($i) => is_scalar($i) ? (string) $i : json_encode($i), $items))
                    : (string) $items;

                if ($text !== '') {
                    $cells['included_items'][] = $who . ': ' . $text;
                }
            }
            if ($g->created_at) {
                $cells['graded_at'][] = $who . ': ' . $g->created_at->format('d M Y, H:i');
            }

            if ($g->total !== null) {
                $cells['total'][] = $who . ': ' . self::n($g->total);
            }

            $parts = array_filter([
                $g->commentA ? 'A — ' . $g->commentA : null,
                $g->commentB ? 'B — ' . $g->commentB : null,
                $g->commentC ? 'C — ' . $g->commentC : null,
                $g->commentD ? 'D — ' . $g->commentD : null,
            ]);

            if ($parts) {
                $cells['comments'][] = $who . ': ' . implode(' | ', $parts);
            }
        }

        return array_map(fn ($lines) => self::joinLines($lines), $cells);
    }

    private static function who($grading, array $indexMap): string
    {
        $number = $indexMap[$grading->user_id] ?? null;

        return $number ? 'Reviewer ' . $number : 'Reviewer';
    }

    private static function rated(string $who, $score, $comment): string
    {
        $text = $who . ': ' . self::n($score);

        return $comment ? $text . ' — ' . $comment : $text;
    }

    /**
     * Group the outcomes by type: publications, IP and the remaining outputs.
     *
     * @return array{0: array, 1: array, 2: array}
     */
    private static function outcomes(Collection $outcomes): array
    {
        $publications = [];
        $ip = [];
        $other = [];

        foreach ($outcomes as $o) {
            $label = self::OUTCOME_LABELS[$o->type] ?? ucfirst(str_replace('_', ' ', (string) $o->type));

            if (in_array($o->type, self::PUBLICATION_TYPES, true)) {
                $publications[$o->type][] = self::publicationLine($o);
            } elseif (in_array($o->type, ['ip_disclosure', 'provisional_patent', 'patent_granted', 'open_source_sw', 'startup'], true)) {
                $ip[$o->type][] = $o->identifier ?: $label;
            } else {
                $other[$o->type][] = $o->identifier ?: $label;
            }
        }

        return [
            array_map(fn ($lines) => implode("\n", $lines), $publications),
            array_map(fn ($lines) => implode("\n", $lines), $ip),
            array_map(fn ($lines) => implode("\n", $lines), $other),
        ];
    }

    private static function publicationLine($outcome): string
    {
        $pub = $outcome->publication;
        $title = $pub->publication_title ?? $outcome->identifier;
        $parts = [];

        if ($title) {
            $parts[] = $title;
        }
        if ($pub && $pub->journal) {
            $parts[] = $pub->journal;
        }
        if ($pub && $pub->year) {
            $parts[] = $pub->year;
        }

        $line = implode(' — ', $parts);

        if ($pub && $pub->doi) {
            $line .= ' (DOI: ' . $pub->doi . ')';
        }

        return $line;
    }

    /**
     * Raw project_outcomes columns, one line per recorded outcome so the values
     * stay aligned with their type (e.g. "Q1 Journal: 10.1234/xyz").
     *
     * @return array<string,string>
     */
    private static function outcomeTable(Collection $outcomes): array
    {
        $cells = [
            'type' => [], 'identifier' => [], 'online_date' => [],
            'verified_system' => [], 'verified_reviewer' => [], 'score' => [],
        ];

        foreach ($outcomes as $o) {
            $label = self::OUTCOME_LABELS[$o->type] ?? ucfirst(str_replace('_', ' ', (string) $o->type));

            $cells['type'][] = $label;

            if ($o->identifier) {
                $cells['identifier'][] = $label . ': ' . $o->identifier;
            }
            if ($o->online_date) {
                $cells['online_date'][] = $label . ': ' . \Illuminate\Support\Carbon::parse($o->online_date)->format('d M Y');
            }
            if ($o->verfication_by_system) {
                $cells['verified_system'][] = $label . ': ' . self::verificationLabel($o->verfication_by_system);
            }
            if ($o->verfication_by_reviewer) {
                $cells['verified_reviewer'][] = $label . ': ' . self::verificationLabel($o->verfication_by_reviewer);
            }
            if ($o->score !== null && $o->score !== '') {
                $cells['score'][] = $label . ': ' . self::n($o->score);
            }
        }

        return array_map(fn ($lines) => implode("\n", $lines), $cells);
    }

    private static function verificationLabel(?string $value): string
    {
        return match ($value) {
            'verified'     => 'Verified',
            'not-verified' => 'Not verified',
            default        => 'Pending',
        };
    }

    private static function decisionLabel($grading): string
    {
        return match ($grading->publish) {
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'reserved' => 'Reserved',
            default    => 'Draft',
        };
    }

    private static function n($value): string
    {
        return $value === null ? '—' : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    // ------------------------------------------------------------------
    // excel export
    // ------------------------------------------------------------------

    /**
     * One worksheet per entry in $sheets, each with a two-level header
     * (group row merged over its sub-columns).
     *
     * @param  array<int, array{title: string, rows: array}>  $sheets
     */
    public static function exportExcelSheets(array $sheets, string $filePrefix = 'grading-details', array $selectedFields = [])
    {
        $groups = self::groupsFor($selectedFields);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('RTS')
            ->setTitle('Grading Details');

        $first = true;
        foreach ($sheets as $sheet) {
            $worksheet = $first ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            self::fillSheet($worksheet, $sheet['title'], $sheet['rows'], $groups);
            $first = false;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = $filePrefix . '-' . now()->format('Y-m-d-Hi') . '.xlsx';
        $path = storage_path('app/' . $filename);

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    private static function fillSheet($sheet, string $title, array $rows, array $groups): void
    {
        $sheet->setTitle($title);

        $columns = [];
        foreach ($groups as $groupColumns) {
            foreach ($groupColumns as $column) {
                $columns[] = $column;
            }
        }

        $lastCol = count($columns);
        $lastLetter = Coordinate::stringFromColumnIndex($lastCol);

        // Row 1: merged group labels.
        $col = 1;
        foreach ($groups as $groupLabel => $groupColumns) {
            $startLetter = Coordinate::stringFromColumnIndex($col);
            $endCol = $col + count($groupColumns) - 1;
            $endLetter = Coordinate::stringFromColumnIndex($endCol);

            $sheet->setCellValue($startLetter . '1', $groupLabel);
            if ($endCol > $col) {
                $sheet->mergeCells($startLetter . '1:' . $endLetter . '1');
            }

            $col = $endCol + 1;
        }

        // Row 2: sub-column labels.
        foreach ($columns as $i => [$key, $label, $width]) {
            $letter = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($letter . '2', $label);
            $sheet->getColumnDimension($letter)->setWidth($width);
        }

        $groupRange = 'A1:' . $lastLetter . '1';
        $sheet->getStyle($groupRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($groupRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('8D1B3D');
        $sheet->getStyle($groupRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $subRange = 'A2:' . $lastLetter . '2';
        $sheet->getStyle($subRange)->getFont()->setBold(true)->getColor()->setRGB('4C0C21');
        $sheet->getStyle($subRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F3D2DA');
        $sheet->getStyle($subRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(34);

        $r = 3;
        foreach ($rows as $row) {
            foreach ($columns as $i => [$key, $label, $width]) {
                $letter = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValueExplicit(
                    $letter . $r,
                    (string) ($row[$key] ?? ''),
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }
            $r++;
        }

        $lastRow = max($r - 1, 2);

        $sheet->freezePane('A3');
        if ($lastRow > 2) {
            $sheet->setAutoFilter('A2:' . $lastLetter . $lastRow);
        }

        $sheet->getStyle('A3:' . $lastLetter . max($lastRow, 3))
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->getStyle('A1:' . $lastLetter . max($lastRow, 3))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_HAIR)
            ->getColor()->setRGB('D9D2C5');

        if ($lastRow === 2) {
            $sheet->setCellValue('A3', 'No projects for this category.');
        }
    }
}
