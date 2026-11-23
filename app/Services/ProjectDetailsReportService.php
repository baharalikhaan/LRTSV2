<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Project Details Report.
 *
 * Produces ONE flat record per project containing every piece of information
 * the system holds about that project (research call, grant, cycle, LPI,
 * pillars, colleges, reviewers, students, hired researchers, contributions,
 * outcomes, publications, IP, submissions, all three gradings, budget and
 * the administrative decisions) so the result can be exported to Excel as a
 * single row per project.
 *
 * Every field is tagged with the table it comes from, so the picker can
 * categorise the choices by source table.
 *
 * All child tables are read with bulk queries grouped by project_id so the
 * report stays fast with hundreds of projects (no N+1 queries).
 */
class ProjectDetailsReportService
{
    /**
     * The recommended set of fields shown until the user picks their own.
     * Everything is still available in the field picker.
     */
    public const DEFAULT_FIELDS = [
        'project_id', 'title', 'grant_type',
        'program_title', 'grant_code', 'grant_name', 'cycle_year',
        'lpi_name', 'lpi_email',
        'pillars', 'colleges',
        'student_count', 'student_ug', 'student_masters', 'student_phd',
        'student_qatari', 'student_non_qatari',
        'reviewer_count', 'outcome_count', 'publication_count',
        'commit_q1_articles', 'commit_q2_articles', 'commit_q3_articles', 'commit_q4_articles',
        'commit_conf_articles', 'commit_books', 'commit_edit_books', 'commit_chapters',
        'commit_ip', 'commit_filed_patents', 'commit_open_source', 'commit_startup',
        'commit_ethical', 'commit_cross_college',
        'commit_masters', 'commit_ug', 'commit_phd',
        'pr1_status', 'pr2_status', 'final_status', 'final_total',
        'budget_amount', 'actual_exp_amount', 'utilization_percent',
    ];

    /**
     * The source tables the report fields come from, in display order.
     * Used to categorise the fields in the picker.
     *
     * @return array<string, array{label:string,table:string}>
     */
    public static function sources(): array
    {
        return [
            'projects'                        => ['label' => 'Projects', 'table' => 'projects'],
            'programs'                        => ['label' => 'Research Calls', 'table' => 'programs'],
            'grants'                          => ['label' => 'Grants', 'table' => 'grants'],
            'cycle_configs'                   => ['label' => 'Cycles', 'table' => 'cycle_configs'],
            'users'                           => ['label' => 'Users (LPI & Reviewers)', 'table' => 'users'],
            'pillars'                         => ['label' => 'Research Pillars', 'table' => 'pillars'],
            'colleges'                        => ['label' => 'Colleges', 'table' => 'colleges'],
            'projects_reviewers'              => ['label' => 'Project Reviewers', 'table' => 'projects_reviewers'],
            'project_students'                => ['label' => 'Project Students', 'table' => 'project_students'],
            'project_researchers'             => ['label' => 'Hired Researchers', 'table' => 'project_researchers'],
            'project_contributions'           => ['label' => 'Contributions', 'table' => 'project_contributions'],
            'project_outcomes'                => ['label' => 'Outcomes', 'table' => 'project_outcomes'],
            'project_publications'            => ['label' => 'Publications', 'table' => 'project_publications'],
            'project_intellectual_properties' => ['label' => 'Intellectual Properties', 'table' => 'project_intellectual_properties'],
            'commitments'                 => ['label' => 'Commitments', 'table' => 'commitments'],
            'project_submissions'             => ['label' => 'Report Submissions', 'table' => 'project_submissions'],
            'progress_report_grading'         => ['label' => 'Progress Report Grading', 'table' => 'progress_report_grading'],
            'final_report_grading'            => ['label' => 'Final Report Grading', 'table' => 'final_report_grading'],
            'project_budgets'                 => ['label' => 'Project Budgets', 'table' => 'project_budgets'],
        ];
    }

    /**
     * Column definitions, in display order.
     *
     * @return array<int, array{key:string,label:string,group:string,source:string}>
     */
    public static function columns(): array
    {
        $c = function (string $key, string $label, string $group, string $source) {
            return ['key' => $key, 'label' => $label, 'group' => $group, 'source' => $source];
        };

        return [
            // ---------------- projects ----------------
            $c('project_id', 'Project ID', 'Project', 'projects'),
            $c('title', 'Project Title', 'Project', 'projects'),
            $c('title_ar', 'Project Title (Arabic)', 'Project', 'projects'),
            $c('abstract', 'Abstract', 'Project', 'projects'),
            $c('keywords', 'Keywords', 'Project', 'projects'),
            $c('grant_type', 'Grant Type', 'Project', 'projects'),
            $c('total_score', 'Total Score', 'Project', 'projects'),
            $c('author', 'Author', 'LPI & Contact', 'projects'),
            $c('email', 'Email', 'LPI & Contact', 'projects'),
            $c('phone', 'Phone', 'LPI & Contact', 'projects'),
            $c('extended_progress', 'Extended Progress', 'Pillars & Colleges', 'projects'),
            $c('proposal_filename', 'Proposal File', 'Pillars & Colleges', 'projects'),
            $c('requested_budget_qar', 'Requested Budget (QAR)', 'Budget', 'projects'),
            $c('spending', 'Spending (QAR)', 'Budget', 'projects'),
            $c('spending_detail', 'Spending Detail', 'Budget', 'projects'),
            $c('college_decision', 'College Decision', 'Decisions', 'projects'),
            $c('rsd_feedback', 'RSD Feedback', 'Decisions', 'projects'),
            $c('final_rsd_decision', 'Final RSD Decision', 'Decisions', 'projects'),
            $c('student_engagement', 'Student Engagement', 'Decisions', 'projects'),
            $c('student_publications', 'Student Publications', 'Decisions', 'projects'),
            $c('student_project_draft', 'Student Form State', 'Decisions', 'projects'),

            // ---------------- programs ----------------
            $c('program_title', 'Research Call', 'Research Call', 'programs'),
            $c('prog_rpt_deadline', 'Progress Report Deadline', 'Research Call', 'programs'),
            $c('prog_rpt2_deadline', 'Progress Report 2 Deadline', 'Research Call', 'programs'),
            $c('final_rpt_deadline', 'Final Report Deadline', 'Research Call', 'programs'),

            // ---------------- grants ----------------
            $c('grant_code', 'Grant Code', 'Research Call', 'grants'),
            $c('grant_name', 'Grant Name', 'Research Call', 'grants'),
            $c('grant_category', 'Grant Category', 'Research Call', 'grants'),
            $c('funding_agency', 'Funding Agency', 'Research Call', 'grants'),

            // ---------------- cycle_configs ----------------
            $c('cycle_year', 'Cycle Year', 'Research Call', 'cycle_configs'),
            $c('cycle_title', 'Cycle Title', 'Research Call', 'cycle_configs'),

            // ---------------- users (LPI) ----------------
            $c('lpi_name', 'LPI Name', 'LPI & Contact', 'users'),
            $c('lpi_email', 'LPI Email', 'LPI & Contact', 'users'),
            $c('lpi_qu_id', 'LPI QU ID', 'LPI & Contact', 'users'),
            $c('lpi_phone', 'LPI Phone', 'LPI & Contact', 'users'),
            $c('lpi_college', 'LPI College', 'LPI & Contact', 'users'),
            $c('lpi_faculty', 'LPI Faculty', 'LPI & Contact', 'users'),
            $c('lpi_type', 'LPI Role', 'LPI & Contact', 'users'),

            // ---------------- pillars / colleges ----------------
            $c('pillars', 'Research Pillars', 'Pillars & Colleges', 'pillars'),
            $c('colleges', 'Colleges', 'Pillars & Colleges', 'colleges'),

            // ---------------- projects_reviewers ----------------
            $c('reviewer_count', 'Reviewers Count', 'Reviewers', 'projects_reviewers'),
            $c('reviewer_names', 'Reviewers', 'Reviewers', 'projects_reviewers'),
            $c('reviewer_emails', 'Reviewer Emails', 'Reviewers', 'projects_reviewers'),
            $c('reviewer_roles', 'Reviewer Roles', 'Reviewers', 'projects_reviewers'),
            $c('reviewer_claimed', 'Reviewer Claimed At', 'Reviewers', 'projects_reviewers'),

            // ---------------- project_students ----------------
            $c('student_count', 'Students Count', 'Students', 'project_students'),
            $c('student_ug', 'Students UG', 'Students', 'project_students'),
            $c('student_masters', 'Students Masters', 'Students', 'project_students'),
            $c('student_phd', 'Students PhD', 'Students', 'project_students'),
            $c('student_qatari', 'Students Qatari', 'Students', 'project_students'),
            $c('student_non_qatari', 'Students Non-Qatari', 'Students', 'project_students'),
            $c('student_verified', 'Students Verified', 'Students', 'project_students'),
            $c('student_days', 'Students Total Days', 'Students', 'project_students'),
            $c('student_names', 'Student Names', 'Students', 'project_students'),
            $c('student_std_ids', 'Student QU IDs', 'Students', 'project_students'),
            $c('student_details', 'Student Details', 'Students', 'project_students'),

            // ---------------- project_researchers ----------------
            $c('researcher_count', 'Researchers Count', 'Researchers', 'project_researchers'),
            $c('researcher_names', 'Researchers', 'Researchers', 'project_researchers'),
            $c('researcher_categories', 'Researcher Categories', 'Researchers', 'project_researchers'),
            $c('researcher_days', 'Researchers Total Days', 'Researchers', 'project_researchers'),

            // ---------------- commitments ----------------
            $c('commit_q1_articles', 'Committed Q1 Articles', 'Commitments', 'commitments'),
            $c('commit_q2_articles', 'Committed Q2 Articles', 'Commitments', 'commitments'),
            $c('commit_q3_articles', 'Committed Q3 Articles', 'Commitments', 'commitments'),
            $c('commit_q4_articles', 'Committed Q4 Articles', 'Commitments', 'commitments'),
            $c('commit_conf_articles', 'Committed Conference Articles', 'Commitments', 'commitments'),
            $c('commit_books', 'Committed Books', 'Commitments', 'commitments'),
            $c('commit_edit_books', 'Committed Edited Books', 'Commitments', 'commitments'),
            $c('commit_chapters', 'Committed Chapters', 'Commitments', 'commitments'),
            $c('commit_ip', 'Committed IP Disclosures', 'Commitments', 'commitments'),
            $c('commit_filed_patents', 'Committed Filed Patents', 'Commitments', 'commitments'),
            $c('commit_open_source', 'Committed Open Source SW', 'Commitments', 'commitments'),
            $c('commit_startup', 'Committed Start-up', 'Commitments', 'commitments'),
            $c('commit_ethical', 'Committed Ethical Approval', 'Commitments', 'commitments'),
            $c('commit_masters', 'Committed Masters Students', 'Commitments', 'commitments'),
            $c('commit_ug', 'Committed Undergraduate Students', 'Commitments', 'commitments'),
            $c('commit_phd', 'Committed PhD Students', 'Commitments', 'commitments'),
            $c('commit_cross_college', 'Committed Cross-College', 'Commitments', 'commitments'),

            // ---------------- project_contributions ----------------
            $c('contribution_count', 'Contributions Count', 'Outputs', 'project_contributions'),
            $c('contribution_types', 'Contribution Types', 'Outputs', 'project_contributions'),

            // ---------------- project_outcomes ----------------
            $c('outcome_count', 'Outcomes Count', 'Outputs', 'project_outcomes'),
            $c('outcome_verified', 'Outcomes Verified', 'Outputs', 'project_outcomes'),
            $c('outcome_types', 'Outcome Types', 'Outputs', 'project_outcomes'),
            $c('outcome_identifiers', 'Outcome Identifiers', 'Outputs', 'project_outcomes'),

            // ---------------- project_publications ----------------
            $c('publication_count', 'Publications Count', 'Outputs', 'project_publications'),
            $c('publication_list', 'Publications', 'Outputs', 'project_publications'),

            // ---------------- project_intellectual_properties ----------------
            $c('ip_count', 'Intellectual Properties Count', 'Outputs', 'project_intellectual_properties'),
            $c('ip_list', 'Intellectual Properties', 'Outputs', 'project_intellectual_properties'),

            // ---------------- project_submissions ----------------
            $c('sub_progress', 'Progress Report Submission', 'Reports Submitted', 'project_submissions'),
            $c('sub_progress2', 'Progress Report 2 Submission', 'Reports Submitted', 'project_submissions'),
            $c('sub_final', 'Final Report Submission', 'Reports Submitted', 'project_submissions'),
            $c('sub_readiness', 'Readiness Report Submission', 'Reports Submitted', 'project_submissions'),
            $c('sub_ethical', 'Ethical Approval Submission', 'Reports Submitted', 'project_submissions'),

            // ---------------- progress_report_grading ----------------
            $c('pr1_status', 'PR1 Grading Status', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr1_reviewer', 'PR1 Reviewer', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr1_achievements', 'PR1 Achievements Rating', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr1_publications', 'PR1 Publications Rating', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr1_students', 'PR1 Students Rating', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr1_budget', 'PR1 Budget Rating', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr1_ethical', 'PR1 Ethical Approval', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr1_rejection', 'PR1 Rejection', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr1_comments', 'PR1 Comments', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_status', 'PR2 Grading Status', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_reviewer', 'PR2 Reviewer', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_achievements', 'PR2 Achievements Rating', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_publications', 'PR2 Publications Rating', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_students', 'PR2 Students Rating', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_budget', 'PR2 Budget Rating', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_ethical', 'PR2 Ethical Approval', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_rejection', 'PR2 Rejection', 'Reviews & Grades', 'progress_report_grading'),
            $c('pr2_comments', 'PR2 Comments', 'Reviews & Grades', 'progress_report_grading'),

            // ---------------- final_report_grading ----------------
            $c('final_status', 'Final Grading Status', 'Reviews & Grades', 'final_report_grading'),
            $c('final_reviewer', 'Final Grading Reviewer', 'Reviews & Grades', 'final_report_grading'),
            $c('final_grade_a', 'Final Grade A (Achievements)', 'Reviews & Grades', 'final_report_grading'),
            $c('final_grade_b', 'Final Grade B (Publications & IP)', 'Reviews & Grades', 'final_report_grading'),
            $c('final_grade_c', 'Final Grade C (Students & Researchers)', 'Reviews & Grades', 'final_report_grading'),
            $c('final_grade_d', 'Final Grade D', 'Reviews & Grades', 'final_report_grading'),
            $c('final_score_a', 'Final Score A', 'Reviews & Grades', 'final_report_grading'),
            $c('final_score_b', 'Final Score B', 'Reviews & Grades', 'final_report_grading'),
            $c('final_score_c', 'Final Score C', 'Reviews & Grades', 'final_report_grading'),
            $c('final_total', 'Final Total Score', 'Reviews & Grades', 'final_report_grading'),
            $c('final_rejection', 'Final Rejection', 'Reviews & Grades', 'final_report_grading'),

            // ---------------- project_budgets ----------------
            $c('budget_amount', 'Allocated Budget (QAR)', 'Budget', 'project_budgets'),
            $c('actual_exp_amount', 'Actual Expenditure (QAR)', 'Budget', 'project_budgets'),
            $c('commitment_amount', 'Commitment Amount (QAR)', 'Budget', 'project_budgets'),
            $c('available_balance', 'Available Balance (QAR)', 'Budget', 'project_budgets'),
            $c('utilization_percent', 'Budget Utilization %', 'Budget', 'project_budgets'),
        ];
    }

    /**
     * Build the report rows.
     *
     * @param  array       $filters  category, cycle_id, grant_id, program_id, q, status
     * @param  array|null  $fields   column keys to include (null = the recommended set)
     * @return array{rows: array<int, array<string, mixed>>, columns: array}
     */
    public static function build(array $filters = [], ?array $fields = null): array
    {
        $allColumns = self::columns();
        $selected = self::resolveColumns($allColumns, $fields);

        $category = $filters['category'] ?? 'all';

        $projects = Project::query()
            ->with(['program.grant', 'program.cycleConfig', 'lpi'])
            ->when($category === 'student', function ($q) {
                // Mirrors Project::isStudentProject().
                $q->where(function ($w) {
                    $w->where('projects.grant_type', 'student')
                        ->orWhereHas('program.grant', fn($g) => $g->where('grants.category', 'student'));
                });
            })
            ->when($category === 'regular', function ($q) {
                $q->where(function ($w) {
                    $w->whereNull('projects.grant_type')
                        ->orWhere('projects.grant_type', '!=', 'student');
                })->whereDoesntHave('program.grant', fn($g) => $g->where('grants.category', 'student'));
            })
            ->when($filters['cycle_id'] ?? null, function ($q, $v) {
                $q->whereHas('program', fn($p) => $p->where('cycle_id', $v));
            })
            ->when($filters['grant_id'] ?? null, function ($q, $v) {
                $q->whereHas('program.grant', fn($g) => $g->where('grants.id', $v));
            })
            ->when($filters['program_id'] ?? null, function ($q, $v) {
                $q->where('program_id', $v);
            })
            ->when($filters['q'] ?? null, function ($q, $v) {
                $term = '%' . $v . '%';
                $q->where(fn($w) => $w->where('old_project_id', 'like', $term)
                    ->orWhere('title', 'like', $term));
            })
            ->orderBy('id')
            ->get();

        $columns = $selected;
        $ids = $projects->pluck('id')->all();

        if (empty($ids)) {
            return ['rows' => [], 'columns' => $columns];
        }

        $child = self::childData($ids);
        $rows = [];

        foreach ($projects as $project) {
            $rows[] = self::projectRow($project, $child);
        }

        // Status filter is applied after the rows are built (the current
        // workflow status lives in status_histories, not on the projects row).
        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            $rows = array_values(array_filter($rows, function ($r) use ($status) {
                return strtolower((string) ($r['current_status'] ?? '')) === $status;
            }));
        }

        return ['rows' => $rows, 'columns' => $columns];
    }

    /**
     * Narrow the full column list down to the requested fields, keeping the
     * canonical order and dropping anything unknown.
     *
     * The Project ID column is always included and always leads, so every
     * record can be traced back to its project.
     */
    public static function resolveColumns(array $allColumns, ?array $fields): array
    {
        if (empty($fields)) {
            $fields = self::DEFAULT_FIELDS;
        }

        $valid = array_column($allColumns, 'key');
        $wanted = array_values(array_intersect($valid, $fields));

        if (empty($wanted)) {
            $wanted = array_values(array_intersect($valid, self::DEFAULT_FIELDS));
        }

        if (!in_array('project_id', $wanted, true)) {
            array_unshift($wanted, 'project_id');
        }

        return array_values(array_filter(
            $allColumns,
            fn($c) => in_array($c['key'], $wanted, true)
        ));
    }

    /**
     * Group the given columns for the two-level table header.
     *
     * @param  array  $columns  resolved columns
     * @return array<int, array{label:string,span:int}>
     */
    public static function groups(array $columns): array
    {
        $groups = [];

        foreach ($columns as $column) {
            $label = $column['group'] ?? 'Other';
            if (!isset($groups[$label])) {
                $groups[$label] = ['label' => $label, 'span' => 0];
            }
            $groups[$label]['span']++;
        }

        return array_values($groups);
    }

    /**
     * Read every child table once, grouped by project id.
     */
    private static function childData(array $ids): array
    {
        $data = [];

        $data['pillars'] = DB::table('project_pillar')
            ->join('pillars', 'pillars.id', '=', 'project_pillar.pillar_id')
            ->whereIn('project_pillar.project_id', $ids)
            ->select('project_pillar.project_id', 'pillars.pillar')
            ->get()
            ->groupBy('project_id');

        $data['colleges'] = DB::table('project_college')
            ->join('colleges', 'colleges.id', '=', 'project_college.college_id')
            ->whereIn('project_college.project_id', $ids)
            ->select('project_college.project_id', 'colleges.name')
            ->get()
            ->groupBy('project_id');

        $data['reviewers'] = DB::table('projects_reviewers')
            ->join('users', 'users.id', '=', 'projects_reviewers.user_id')
            ->whereIn('projects_reviewers.project_id', $ids)
            ->select('projects_reviewers.project_id', 'users.name', 'users.email', 'projects_reviewers.role', 'projects_reviewers.claimed_at')
            ->orderBy('projects_reviewers.id')
            ->get()
            ->groupBy('project_id');

        $data['students'] = DB::table('project_students as ps')
            ->leftJoin('project_students_details as psd', 'psd.project_student_id', '=', 'ps.id')
            ->leftJoin('users as su', 'su.id', '=', 'ps.user_id')
            ->whereIn('ps.project_id', $ids)
            ->select(
                'ps.project_id', 'ps.type', 'ps.std_id', 'ps.days', 'ps.student_name',
                'ps.nationality', 'ps.verifcation_by_reviewer',
                'psd.first_name', 'psd.last_name', 'psd.major', 'psd.minor',
                'psd.college', 'psd.std_program', 'psd.std_level', 'psd.student_status',
                'su.name as user_name'
            )
            ->orderBy('ps.id')
            ->get()
            ->groupBy('project_id');

        $data['researchers'] = DB::table('project_researchers')
            ->whereIn('project_id', $ids)
            ->select('project_id', 'name', 'category', 'days')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['commitments'] = DB::table('commitments')
            ->whereIn('project_id', $ids)
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['contributions'] = DB::table('project_contributions')
            ->whereIn('project_id', $ids)
            ->select('project_id', 'type', 'detail')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['outcomes'] = DB::table('project_outcomes')
            ->whereIn('project_id', $ids)
            ->select('project_id', 'type', 'identifier', 'verifcation_by_reviewer', 'verifcation_by_system')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['publications'] = DB::table('project_publications')
            ->whereIn('project_id', $ids)
            ->select('project_id', 'publication_title', 'journal', 'year', 'status')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['ip'] = DB::table('project_intellectual_properties')
            ->whereIn('project_id', $ids)
            ->select('project_id', 'ip_type', 'title', 'filing_status')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['submissions'] = DB::table('project_submissions')
            ->whereIn('project_id', $ids)
            ->select('project_id', 'type', 'version', 'submitted', 'submitted_at', 'original_filename')
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['progress_gradings'] = DB::table('progress_report_grading')
            ->whereIn('project_id', $ids)
            ->select(
                'project_id', 'user_id', 'report_type', 'publish', 'isAccepted', 'isAdmin',
                'achievementsRating', 'publicationsRating', 'studentsRating', 'budgetRating',
                'ethical', 'analysis', 'comments', 'recommendation',
                'achievementsComments', 'publicationsComments', 'studentsComments', 'budgetComments',
                'rejection_type', 'rejection_reason', 'created_at'
            )
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['final_gradings'] = DB::table('final_report_grading')
            ->whereIn('project_id', $ids)
            ->select(
                'project_id', 'user_id', 'publish', 'isAccepted', 'isAdmin',
                'gradeA', 'gradeB', 'gradeC', 'gradeD',
                'scoreA', 'scoreB', 'scoreC', 'autoGradeA', 'autoGradeB', 'autoGradeC',
                'total', 'commentA', 'commentB', 'commentC', 'commentD',
                'rejection_type', 'rejection_reason', 'created_at'
            )
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        $data['budgets'] = DB::table('project_budgets')
            ->whereIn('project_id', $ids)
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');

        // Latest workflow status per project (status_histories is append-only).
        $data['statuses'] = DB::table('status_histories')
            ->whereIn('project_id', $ids)
            ->select('project_id', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('project_id')
            ->map(fn($g) => $g->first());

        // Rating id => label (1..5)
        $data['ratings'] = DB::table('ratings')->pluck('rating', 'id')->toArray();

        // Grader names (reviewers who graded) — resolved in one pass.
        $graderIds = collect($data['progress_gradings']->flatten())
            ->pluck('user_id')
            ->merge($data['final_gradings']->flatten()->pluck('user_id'))
            ->filter()
            ->unique()
            ->values();
        $data['graders'] = $graderIds->isEmpty()
            ? collect()
            : User::whereIn('id', $graderIds->all())->pluck('name', 'id');

        return $data;
    }

    /**
     * Flatten one project into a single record.
     */
    private static function projectRow(Project $project, array $d): array
    {
        $program = $project->program;
        $grant = $program ? $program->grant : null;
        $cycle = $program ? $program->cycleConfig : null;
        $lpi = $project->lpi;
        $pid = $project->id;

        $row = [
            'project_id' => $project->old_project_id ?: $project->id,
            'title' => $project->title,
            'title_ar' => $project->title_ar,
            'abstract' => $project->abstract,
            'keywords' => $project->keywords,
            'grant_type' => $project->grant_type,
            'total_score' => $project->total_score,
            'created_at' => self::fmt($project->created_at),
            'updated_at' => self::fmt($project->updated_at),

            'program_title' => $program ? $program->program_title : null,
            'grant_code' => $grant ? $grant->grant_code : null,
            'grant_name' => $grant ? $grant->grant_name : null,
            'grant_category' => $grant ? $grant->category : null,
            'funding_agency' => $grant ? $grant->funding_agency : null,
            'cycle_year' => $cycle ? $cycle->year : null,
            'cycle_title' => $cycle ? $cycle->title : null,
            'prog_rpt_deadline' => $program ? self::fmt($program->prog_rpt_deadline) : null,
            'prog_rpt2_deadline' => $program ? self::fmt($program->prog_rpt2_deadline) : null,
            'final_rpt_deadline' => $program ? self::fmt($program->final_rpt_deadline) : null,

            'lpi_name' => $lpi ? $lpi->name : null,
            'lpi_email' => $lpi ? $lpi->email : null,
            'lpi_qu_id' => $lpi ? $lpi->qu_id : null,
            'lpi_phone' => $lpi ? $lpi->phone : null,
            'lpi_college' => $lpi ? $lpi->college : null,
            'lpi_faculty' => $lpi ? $lpi->faculty : null,
            'lpi_type' => $lpi ? $lpi->type : null,
            'author' => $project->author,
            'email' => $project->email,
            'phone' => $project->phone,

            'pillars' => self::join(($d['pillars']->get($pid) ?? collect())->pluck('pillar')),
            'colleges' => self::join(($d['colleges']->get($pid) ?? collect())->pluck('name')),
            'extended_progress' => $project->extended_progress ? 'Yes' : 'No',
            'proposal_filename' => $project->proposal_filename,

            'reviewer_count' => ($d['reviewers']->get($pid) ?? collect())->count(),
            'reviewer_names' => self::join(($d['reviewers']->get($pid) ?? collect())->pluck('name')),
            'reviewer_emails' => self::join(($d['reviewers']->get($pid) ?? collect())->pluck('email')),
            'reviewer_roles' => self::join(($d['reviewers']->get($pid) ?? collect())->pluck('role')),
            'reviewer_claimed' => self::join(
                ($d['reviewers']->get($pid) ?? collect())->filter(fn($r) => $r->claimed_at)->pluck('claimed_at')
            ),
        ];

        // ---- Students -------------------------------------------------
        $students = $d['students']->get($pid) ?? collect();
        $ug = $masters = $phd = $qatari = $nonQatari = $verified = $days = 0;
        $names = [];
        $stdIds = [];
        $details = [];

        foreach ($students as $s) {
            $level = strtolower((string) $s->type);
            if ($level === 'ug') {
                $ug++;
            } elseif ($level === 'masters') {
                $masters++;
            } elseif ($level === 'phd') {
                $phd++;
            }

            $nat = strtolower((string) $s->nationality);
            if ($nat !== '') {
                if (strpos($nat, 'non') !== false) {
                    $nonQatari++;
                } else {
                    $qatari++;
                }
            }

            if (($s->verifcation_by_reviewer ?? '') === 'verified') {
                $verified++;
            }

            $days += (int) $s->days;

            $name = trim((string) ($s->student_name ?: ''));
            if ($name === '') {
                $full = trim(((string) $s->first_name) . ' ' . ((string) $s->last_name));
                $name = $full !== '' ? $full : (string) ($s->user_name ?: '');
            }
            if ($name !== '') {
                $names[] = $name . ($s->type ? ' (' . $s->type . ')' : '');
            }
            if ($s->std_id) {
                $stdIds[] = $s->std_id;
            }

            $bits = array_filter([
                $name,
                $s->type ? 'level: ' . $s->type : null,
                $s->std_level ? 'program level: ' . $s->std_level : null,
                $s->std_program ? 'program: ' . $s->std_program : null,
                $s->major ? 'major: ' . $s->major : null,
                $s->college ? 'college: ' . $s->college : null,
                $s->nationality ? 'nationality: ' . $s->nationality : null,
                $s->days ? 'days: ' . $s->days : null,
            ]);
            if ($bits) {
                $details[] = implode(', ', $bits);
            }
        }

        $row += [
            'student_count' => $students->count(),
            'student_ug' => $ug,
            'student_masters' => $masters,
            'student_phd' => $phd,
            'student_qatari' => $qatari,
            'student_non_qatari' => $nonQatari,
            'student_verified' => $verified,
            'student_days' => $days,
            'student_names' => self::join($names),
            'student_std_ids' => self::join($stdIds),
            'student_details' => self::join($details, '; '),
        ];

        // ---- Hired researchers ----------------------------------------
        $researchers = $d['researchers']->get($pid) ?? collect();
        $row += [
            'researcher_count' => $researchers->count(),
            'researcher_names' => self::join($researchers->pluck('name')),
            'researcher_categories' => self::join($researchers->pluck('category')),
            'researcher_days' => (int) $researchers->sum('days'),
        ];

        // ---- Commitments (what the LPI promised) ---------------------------
        $commitment = ($d['commitments']->get($pid) ?? collect())->last();

        $row += [
            'commit_q1_articles' => $commitment ? $commitment->q1article : null,
            'commit_q2_articles' => $commitment ? $commitment->q2article : null,
            'commit_q3_articles' => $commitment ? $commitment->q3article : null,
            'commit_q4_articles' => $commitment ? $commitment->q4article : null,
            'commit_conf_articles' => $commitment ? $commitment->confArticle : null,
            'commit_books' => $commitment ? $commitment->books : null,
            'commit_edit_books' => $commitment ? $commitment->editBooks : null,
            'commit_chapters' => $commitment ? $commitment->chapters : null,
            'commit_ip' => $commitment ? $commitment->ip : null,
            'commit_filed_patents' => $commitment ? $commitment->filedPatent : null,
            'commit_open_source' => $commitment ? $commitment->openSourceSW : null,
            'commit_startup' => $commitment ? ($commitment->startUp ? 'Yes' : 'No') : null,
            'commit_ethical' => $commitment ? ($commitment->ethical ? 'Yes' : 'No') : null,
            'commit_masters' => $commitment ? $commitment->master : null,
            'commit_ug' => $commitment ? $commitment->UG : null,
            'commit_phd' => $commitment ? $commitment->Phd : null,
            'commit_cross_college' => $commitment ? ($commitment->crossCollege ? 'Yes' : 'No') : null,
        ];

        // ---- Contributions / outcomes / publications / IP --------------
        $contributions = $d['contributions']->get($pid) ?? collect();
        $outcomes = $d['outcomes']->get($pid) ?? collect();
        $publications = $d['publications']->get($pid) ?? collect();
        $ips = $d['ip']->get($pid) ?? collect();

        $row += [
            'contribution_count' => $contributions->count(),
            'contribution_types' => self::join($contributions->pluck('type')),
            'outcome_count' => $outcomes->count(),
            'outcome_verified' => $outcomes->filter(fn($o) => $o->verifcation_by_reviewer === 'verified')->count(),
            'outcome_types' => self::join($outcomes->pluck('type')),
            'outcome_identifiers' => self::join($outcomes->pluck('identifier')),
            'publication_count' => $publications->count(),
            'publication_list' => self::join($publications->map(function ($p) {
                return trim($p->publication_title . ($p->journal ? ' (' . $p->journal . ')' : '')
                    . ($p->year ? ' ' . $p->year : ''));
            })->all(), '; '),
            'ip_count' => $ips->count(),
            'ip_list' => self::join($ips->map(function ($i) {
                return trim($i->ip_type . ': ' . $i->title);
            })->all(), '; '),
        ];

        // ---- Submissions ------------------------------------------------
        $subs = $d['submissions']->get($pid) ?? collect();
        $row += [
            'sub_progress' => self::submission($subs, 'progress'),
            'sub_progress2' => self::submission($subs, 'progress2'),
            'sub_final' => self::submission($subs, 'final'),
            'sub_readiness' => self::submission($subs, 'readiness'),
            'sub_ethical' => self::submission($subs, 'ethical'),
        ];

        // ---- Grading -----------------------------------------------------
        $pr = $d['progress_gradings']->get($pid) ?? collect();
        $pr1 = $pr->filter(fn($g) => ($g->report_type === 'progress') || ($g->report_type === null))->last();
        $pr2 = $pr->filter(fn($g) => $g->report_type === 'progress2')->last();

        $row += [
            'pr1_status' => $pr1 ? $pr1->publish : null,
            'pr1_reviewer' => $pr1 ? self::graderName($d['graders'], $pr1->user_id) : null,
            'pr1_achievements' => $pr1 ? self::ratingLabel($d['ratings'], $pr1->achievementsRating) : null,
            'pr1_publications' => $pr1 ? self::ratingLabel($d['ratings'], $pr1->publicationsRating) : null,
            'pr1_students' => $pr1 ? self::ratingLabel($d['ratings'], $pr1->studentsRating) : null,
            'pr1_budget' => $pr1 ? self::ratingLabel($d['ratings'], $pr1->budgetRating) : null,
            'pr1_ethical' => $pr1 ? ($pr1->ethical !== null ? ($pr1->ethical ? 'Yes' : 'No') : null) : null,
            'pr1_rejection' => $pr1 ? self::rejection($pr1->rejection_type, $pr1->rejection_reason) : null,
            'pr1_comments' => $pr1 ? self::join(array_filter([$pr1->analysis, $pr1->comments, $pr1->recommendation]), ' / ') : null,

            'pr2_status' => $pr2 ? $pr2->publish : null,
            'pr2_reviewer' => $pr2 ? self::graderName($d['graders'], $pr2->user_id) : null,
            'pr2_achievements' => $pr2 ? self::ratingLabel($d['ratings'], $pr2->achievementsRating) : null,
            'pr2_publications' => $pr2 ? self::ratingLabel($d['ratings'], $pr2->publicationsRating) : null,
            'pr2_students' => $pr2 ? self::ratingLabel($d['ratings'], $pr2->studentsRating) : null,
            'pr2_budget' => $pr2 ? self::ratingLabel($d['ratings'], $pr2->budgetRating) : null,
            'pr2_ethical' => $pr2 ? ($pr2->ethical !== null ? ($pr2->ethical ? 'Yes' : 'No') : null) : null,
            'pr2_rejection' => $pr2 ? self::rejection($pr2->rejection_type, $pr2->rejection_reason) : null,
            'pr2_comments' => $pr2 ? self::join(array_filter([$pr2->analysis, $pr2->comments, $pr2->recommendation]), ' / ') : null,
        ];

        $fg = ($d['final_gradings']->get($pid) ?? collect())->last();
        $row += [
            'final_status' => $fg ? $fg->publish : null,
            'final_reviewer' => $fg ? self::graderName($d['graders'], $fg->user_id) : null,
            'final_grade_a' => $fg ? $fg->gradeA : null,
            'final_grade_b' => $fg ? $fg->gradeB : null,
            'final_grade_c' => $fg ? $fg->gradeC : null,
            'final_grade_d' => $fg ? $fg->gradeD : null,
            'final_score_a' => $fg ? $fg->scoreA : null,
            'final_score_b' => $fg ? $fg->scoreB : null,
            'final_score_c' => $fg ? $fg->scoreC : null,
            'final_total' => $fg ? $fg->total : null,
            'final_rejection' => $fg ? self::rejection($fg->rejection_type, $fg->rejection_reason) : null,
        ];

        // ---- Budget --------------------------------------------------------
        $budget = ($d['budgets']->get($pid) ?? collect())->last();
        $allocated = $budget ? (float) $budget->budget_amount : (float) $project->requested_budget_qar;
        $spent = $budget ? (float) $budget->actual_exp_amount : (float) $project->spending;

        $row += [
            'budget_amount' => $budget ? $budget->budget_amount : $project->requested_budget_qar,
            'actual_exp_amount' => $budget ? $budget->actual_exp_amount : $project->spending,
            'commitment_amount' => $budget ? $budget->commitment_amount : null,
            'available_balance' => $budget ? $budget->available_balance : null,
            'utilization_percent' => $allocated > 0 ? round(($spent / $allocated) * 100, 1) : null,
            'requested_budget_qar' => $project->requested_budget_qar,
            'spending' => $project->spending,
            'spending_detail' => $project->spending_detail,
        ];

        // ---- Decisions -------------------------------------------------------
        $status = $d['statuses']->get($pid);
        $row += [
            'current_status' => $status ? $status->status : null,
            'status_changed_at' => $status ? self::fmt($status->created_at) : null,
            'college_decision' => $project->college_decision,
            'rsd_feedback' => $project->rsd_feedback,
            'final_rsd_decision' => $project->final_rsd_decision,
            'student_engagement' => $project->student_engagement,
            'student_publications' => $project->publications,
            'student_project_draft' => $project->student_project_draft,
        ];

        return $row;
    }

    /**
     * Export the report to a real .xlsx workbook (one row per project).
     */
    public static function exportExcel(array $data, array $filters = [])
    {
        $rows = $data['rows'];

        // Every exported row must identify its project, so the Project ID
        // column always leads (resolveColumns guarantees it).
        $columns = $data['columns'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Project Details');

        // Row 1: the group each column belongs to (merged across its columns)
        $col = 1;
        $groupStarts = [];
        foreach (self::groups($columns) as $group) {
            $start = $col;
            $groupStarts[] = $start;
            $sheet->setCellValue([$col, 1], $group['label']);
            if ($group['span'] > 1) {
                $sheet->mergeCells([$start, 1, $start + $group['span'] - 1, 1]);
            }
            $col += $group['span'];
        }

        // Row 2: the column names
        $col = 1;
        foreach ($columns as $c) {
            $sheet->setCellValue([$col, 2], $c['label']);
            $col++;
        }
        $lastCol = $col - 1;

        // Data rows
        $row = 3;
        foreach ($rows as $r) {
            $col = 1;
            foreach ($columns as $c) {
                $value = $r[$c['key']] ?? null;
                $sheet->setCellValue([$col, $row], $value === null ? '' : $value);
                $col++;
            }
            $row++;
        }
        $lastRow = max($row - 1, 2);

        // Header styling
        $maroon = 'FF8D1B3D';
        $border = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        $groupStyle = $sheet->getStyle([1, 1, $lastCol, 1]);
        $groupStyle->getFont()->setBold(true)->getColor()->setARGB('FF63102B');
        $groupStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFAF7F0');
        $groupStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        // Maroon hairlines: top of the sheet, under the group row.
        $groupStyle->getBorders()->getTop()->setBorderStyle($border)->getColor()->setARGB($maroon);
        $groupStyle->getBorders()->getBottom()->setBorderStyle($border)->getColor()->setARGB($maroon);

        // Hairline between neighbouring groups.
        foreach (array_slice($groupStarts, 1) as $start) {
            $sheet->getStyle([$start, 1])->getBorders()->getLeft()
                ->setBorderStyle($border)->getColor()->setARGB('FF8D1B3D');
        }

        $headerStyle = $sheet->getStyle([1, 2, $lastCol, 2]);
        $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FF63102B');
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF2EAD6');
        $headerStyle->getAlignment()->setWrapText(true)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        // Maroon hairline under the column-name row.
        $headerStyle->getBorders()->getBottom()->setBorderStyle($border)->getColor()->setARGB($maroon);

        $sheet->freezePane('A3');
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(32);

        if ($lastRow > 2) {
            $sheet->setAutoFilter([1, 2, $lastCol, $lastRow]);
            $sheet->getStyle([1, 3, $lastCol, $lastRow])
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
                ->setWrapText(false);
        }

        // Column widths (a sensible default; the Excel user can auto-fit)
        for ($i = 1; $i <= $lastCol; $i++) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $label = $columns[$i - 1]['label'];
            $width = in_array(strtolower($label), ['abstract', 'keywords', 'comments', 'student details', 'spending detail', 'student engagement'], true) ? 45 : 22;
            $sheet->getColumnDimension($letter)->setWidth($width);
        }

        $filename = 'project-details-report-' . now()->format('Y-m-d-Hi') . '.xlsx';
        $path = storage_path('app/' . $filename);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($path);

        $spreadsheet->disconnectWorksheets();

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    // ------------------------------------------------------------------
    // helpers
    // ------------------------------------------------------------------

    private static function join($values, string $glue = '; '): ?string
    {
        $values = collect($values)
            ->filter(fn($v) => is_scalar($v) && $v !== '')
            ->map(fn($v) => (string) $v)
            ->unique()
            ->values();

        return $values->isEmpty() ? null : $values->implode($glue);
    }

    private static function fmt($value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private static function ratingLabel($ratings, $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $label = $ratings[$value] ?? null;

        return $label ? $label . ' (' . $value . ')' : (string) $value;
    }

    private static function graderName($graders, $userId): ?string
    {
        if (!$userId) {
            return null;
        }

        return $graders[$userId] ?? null;
    }

    private static function rejection(?string $type, ?string $reason): ?string
    {
        if (!$type && !$reason) {
            return null;
        }

        return trim(($type ?: 'rejected') . ($reason ? ': ' . $reason : ''));
    }

    private static function submission($subs, string $type): ?string
    {
        $rows = $subs->filter(fn($s) => $s->type === $type);

        if ($rows->isEmpty()) {
            return null;
        }

        $parts = $rows->map(function ($s) {
            $state = $s->submitted ? 'submitted' : 'draft';
            $when = $s->submitted_at ? ' on ' . self::fmt($s->submitted_at) : '';
            $name = $s->original_filename ? ' (' . $s->original_filename . ')' : '';

            return 'v' . $s->version . ' ' . $state . $when . $name;
        })->values();

        return $parts->implode('; ');
    }
}
