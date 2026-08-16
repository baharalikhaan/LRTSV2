<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Program;
use App\Models\CycleConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CycleProgressReportService
{
    /**
     * Column definitions for the report.
     */
    public const COLUMNS = [
        'registration' => [
            'key' => 'registration',
            'label' => 'Registration',
            'group' => 'LPI',
            'type' => 'boolean',
        ],
        'outcomes' => [
            'key' => 'outcomes',
            'label' => 'Outcomes',
            'group' => 'LPI',
            'type' => 'count',
        ],
        'students' => [
            'key' => 'students',
            'label' => 'Students',
            'group' => 'LPI',
            'type' => 'count',
        ],
        'contributions' => [
            'key' => 'contributions',
            'label' => 'Contribution',
            'group' => 'LPI',
            'type' => 'count',
        ],
        'progress_report' => [
            'key' => 'progress_report',
            'label' => 'Progress Report',
            'group' => 'Admin',
            'type' => 'boolean',
        ],
        'final_report' => [
            'key' => 'final_report',
            'label' => 'Final Report',
            'group' => 'Admin',
            'type' => 'boolean',
        ],
        'readiness_report' => [
            'key' => 'readiness_report',
            'label' => 'Readiness Report',
            'group' => 'Admin',
            'type' => 'boolean',
        ],
        'reviewer_count' => [
            'key' => 'reviewer_count',
            'label' => 'Reviewers Assigned',
            'group' => 'Reviewer',
            'type' => 'count',
        ],
        'progress_grading' => [
            'key' => 'progress_grading',
            'label' => 'Progress Grading',
            'group' => 'Reviewer',
            'type' => 'count',
        ],
        'final_grading' => [
            'key' => 'final_grading',
            'label' => 'Final Grading',
            'group' => 'Reviewer',
            'type' => 'count',
        ],
    ];

    /**
     * Build the report for a given cycle. One row per project.
     *
     * @return array{rows: Collection, footer: array, totalProjects: int, cycle: CycleConfig|null}
     */
    public function buildReport(int $cycleId): array
    {
        $cycle = CycleConfig::find($cycleId);

        // Get all program IDs for this cycle
        $programIds = Program::where('cycle_id', $cycleId)->pluck('id')->toArray();

        // Get all projects in this cycle (through programs)
        $projects = Project::query()
            ->whereIn('program_id', $programIds)
            ->with(['lpi', 'program'])
            ->get();

        $projectIds = $projects->pluck('id')->toArray();

        if (empty($projectIds)) {
            return [
                'rows'           => collect(),
                'footer'         => $this->buildFooter(collect()),
                'totalProjects'  => 0,
                'cycle'          => $cycle,
            ];
        }

        // Build project-to-program map
        $projectProgramMap = $projects->pluck('program_id', 'id');

        // Pre-fetch all child counts in bulk (no N+1)

        // Outcomes per project
        $outcomesCounts = DB::table('project_outcomes')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        // Students per project
        $studentsCounts = DB::table('project_students')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        // Contributions per project
        $contributionsCounts = DB::table('project_contributions')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        // Progress report submissions (distinct projects with at least one submission)
        $progressReportProjects = DB::table('project_submissions')
            ->whereIn('project_id', $projectIds)
            ->where('type', 'progress')
            ->distinct()
            ->pluck('project_id');

        // Final report submissions
        $finalReportProjects = DB::table('project_submissions')
            ->whereIn('project_id', $projectIds)
            ->where('type', 'final')
            ->distinct()
            ->pluck('project_id');

        // Readiness report submissions
        $readinessReportProjects = DB::table('project_submissions')
            ->whereIn('project_id', $projectIds)
            ->where('type', 'readiness')
            ->distinct()
            ->pluck('project_id');

        // Reviewer assignments per project
        $reviewerCounts = DB::table('projects_reviewers')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        // Progress grading (completed: publish != pending)
        $progressGradingProjects = DB::table('progress_report_grading')
            ->whereIn('project_id', $projectIds)
            ->where('publish', '!=', 'pending')
            ->distinct()
            ->pluck('project_id');

        // Final grading (completed: publish != pending)
        $finalGradingProjects = DB::table('final_report_grading')
            ->whereIn('project_id', $projectIds)
            ->where('publish', '!=', 'pending')
            ->distinct()
            ->pluck('project_id');

        // Registered projects (have STATUS_REGISTERED in status_histories)
        $registeredProjects = DB::table('status_histories')
            ->whereIn('project_id', $projectIds)
            ->where('status', Project::STATUS_REGISTERED)
            ->distinct()
            ->pluck('project_id');

        // Build rows
        $rows = $projects->map(function ($project) use (
            $outcomesCounts, $studentsCounts, $contributionsCounts,
            $progressReportProjects, $finalReportProjects, $readinessReportProjects,
            $reviewerCounts, $progressGradingProjects, $finalGradingProjects,
            $registeredProjects
        ) {
            $id = $project->id;

            return [
                'id'                      => $id,
                'old_project_id'          => $project->old_project_id ?? $id,
                'title'                   => $project->title,
                'program'                 => $project->program ? $project->program->program_title : '—',
                'lpi_name'                => $project->lpi ? $project->lpi->name : '—',
                'lpi_email'               => $project->lpi ? $project->lpi->email : null,
                'registration'            => $registeredProjects->contains($id),
                'outcomes_count'          => $outcomesCounts[$id] ?? 0,
                'students_count'          => $studentsCounts[$id] ?? 0,
                'contributions_count'     => $contributionsCounts[$id] ?? 0,
                'has_progress_report'     => $progressReportProjects->contains($id),
                'has_final_report'        => $finalReportProjects->contains($id),
                'has_readiness_report'    => $readinessReportProjects->contains($id),
                'reviewer_count'          => $reviewerCounts[$id] ?? 0,
                'progress_grading_count'  => $progressGradingProjects->contains($id) ? 1 : 0,
                'final_grading_count'     => $finalGradingProjects->contains($id) ? 1 : 0,
            ];
        });

        $totalProjects = $rows->count();
        $footer = $this->buildFooter($rows);

        return [
            'rows'          => $rows,
            'footer'        => $footer,
            'totalProjects' => $totalProjects,
            'cycle'         => $cycle,
        ];
    }

    /**
     * Build the report for a given program (research call). One row per project.
     *
     * @return array{rows: Collection, footer: array, totalProjects: int, program: Program|null}
     */
    public function buildReportByProgram(int $programId): array
    {
        $program = Program::with('grant', 'cycle')->find($programId);

        // Get all projects in this program
        $projects = Project::query()
            ->where('program_id', $programId)
            ->with(['lpi', 'program'])
            ->get();

        $projectIds = $projects->pluck('id')->toArray();

        if (empty($projectIds)) {
            return [
                'rows'           => collect(),
                'footer'         => $this->buildFooter(collect()),
                'totalProjects'  => 0,
                'program'        => $program,
            ];
        }

        // Pre-fetch all child counts in bulk (no N+1)
        $outcomesCounts = DB::table('project_outcomes')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $studentsCounts = DB::table('project_students')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $contributionsCounts = DB::table('project_contributions')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $progressReportProjects = DB::table('project_submissions')
            ->whereIn('project_id', $projectIds)
            ->where('type', 'progress')
            ->distinct()
            ->pluck('project_id');

        $finalReportProjects = DB::table('project_submissions')
            ->whereIn('project_id', $projectIds)
            ->where('type', 'final')
            ->distinct()
            ->pluck('project_id');

        $readinessReportProjects = DB::table('project_submissions')
            ->whereIn('project_id', $projectIds)
            ->where('type', 'readiness')
            ->distinct()
            ->pluck('project_id');

        $reviewerCounts = DB::table('projects_reviewers')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $progressGradingProjects = DB::table('progress_report_grading')
            ->whereIn('project_id', $projectIds)
            ->where('publish', '!=', 'pending')
            ->distinct()
            ->pluck('project_id');

        $finalGradingProjects = DB::table('final_report_grading')
            ->whereIn('project_id', $projectIds)
            ->where('publish', '!=', 'pending')
            ->distinct()
            ->pluck('project_id');

        $registeredProjects = DB::table('status_histories')
            ->whereIn('project_id', $projectIds)
            ->where('status', Project::STATUS_REGISTERED)
            ->distinct()
            ->pluck('project_id');

        // Build rows
        $rows = $projects->map(function ($project) use (
            $outcomesCounts, $studentsCounts, $contributionsCounts,
            $progressReportProjects, $finalReportProjects, $readinessReportProjects,
            $reviewerCounts, $progressGradingProjects, $finalGradingProjects,
            $registeredProjects
        ) {
            $id = $project->id;

            return [
                'id'                      => $id,
                'old_project_id'          => $project->old_project_id ?? $id,
                'title'                   => $project->title,
                'program'                 => $project->program ? $project->program->program_title : '—',
                'lpi_name'                => $project->lpi ? $project->lpi->name : '—',
                'lpi_email'               => $project->lpi ? $project->lpi->email : null,
                'registration'            => $registeredProjects->contains($id),
                'outcomes_count'          => $outcomesCounts[$id] ?? 0,
                'students_count'          => $studentsCounts[$id] ?? 0,
                'contributions_count'     => $contributionsCounts[$id] ?? 0,
                'has_progress_report'     => $progressReportProjects->contains($id),
                'has_final_report'        => $finalReportProjects->contains($id),
                'has_readiness_report'    => $readinessReportProjects->contains($id),
                'reviewer_count'          => $reviewerCounts[$id] ?? 0,
                'progress_grading_count'  => $progressGradingProjects->contains($id) ? 1 : 0,
                'final_grading_count'     => $finalGradingProjects->contains($id) ? 1 : 0,
            ];
        });

        $totalProjects = $rows->count();
        $footer = $this->buildFooter($rows);

        return [
            'rows'          => $rows,
            'footer'        => $footer,
            'totalProjects' => $totalProjects,
            'program'       => $program,
        ];
    }

    /**
     * Build the student grant report for a given program. One row per project.
     *
     * @return array{rows: Collection, footer: array, totalProjects: int, program: Program|null}
     */
    public function buildStudentGrantReport(int $programId): array
    {
        $program = Program::with('grant', 'cycle')->find($programId);

        // Get all projects in this program
        $projects = Project::query()
            ->where('program_id', $programId)
            ->with(['lpi', 'program'])
            ->get();

        $projectIds = $projects->pluck('id')->toArray();
        $totalProjects = $projects->count();

        if (empty($projectIds)) {
            return [
                'rows'           => collect(),
                'footer'         => $this->buildStudentGrantFooter(collect()),
                'totalProjects'  => 0,
                'program'        => $program,
            ];
        }

        // Get total student counts per project
        $studentCounts = DB::table('project_students')
            ->whereIn('project_id', $projectIds)
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        // Get budget data from ProjectBudget
        $budgetData = DB::table('project_budgets')
            ->whereIn('project_id', $projectIds)
            ->pluck('actual_exp_amount', 'project_id');

        $budgetAmounts = DB::table('project_budgets')
            ->whereIn('project_id', $projectIds)
            ->pluck('budget_amount', 'project_id');

        // Registered projects
        $registeredProjects = DB::table('status_histories')
            ->whereIn('project_id', $projectIds)
            ->where('status', 'registered')
            ->distinct()
            ->pluck('project_id');

        // Build rows
        $rows = $projects->map(function ($project) use ($studentCounts, $budgetData, $budgetAmounts, $registeredProjects) {
            $id = $project->id;
            $totalStudents = $studentCounts[$id] ?? 0;

            // Check form saved (registration status)
            $formSaved = $registeredProjects->contains($id);

            // Check engagement (column may not exist)
            $hasEngagement = false;
            if (\Illuminate\Support\Facades\Schema::hasColumn('projects', 'student_engagement')) {
                $hasEngagement = !empty($project->student_engagement);
            }

            // Check publications
            $hasPublications = false;
            if (\Illuminate\Support\Facades\Schema::hasColumn('projects', 'publications')) {
                $hasPublications = !empty($project->publications);
            }
            if (!$hasPublications) {
                $hasPublications = $project->outcomes()->whereIn('type', [
                    'journal_q1', 'journal_q2', 'journal_q3', 'journal_q4',
                    'conference', 'book', 'edited_book', 'book_chapter'
                ])->count() > 0;
            }

            // Check ethical approval
            $hasEthicalApproval = $project->submissions()
                ->where('type', 'readiness')
                ->count() > 0;

            // Calculate spending
            $budget = $budgetAmounts[$id] ?? $project->budget ?? $project->requested_budget_qar ?? 0;
            $spending = $budgetData[$id] ?? 0;
            $utilization = $budget > 0 ? round(($spending / $budget) * 100, 2) : 0;

            if ($budget > 0 && $spending > 0) {
                if ($utilization > 100) {
                    $spendingStatus = 'exceeded';
                } elseif ($utilization < 100) {
                    $spendingStatus = 'under';
                } else {
                    $spendingStatus = 'full';
                }
            } elseif ($spending == 0 && $formSaved) {
                $spendingStatus = 'no_spending';
            } else {
                $spendingStatus = 'na';
            }

            return [
                'old_project_id'      => $project->old_project_id ?? $id,
                'lpi_email'           => $project->lpi ? $project->lpi->email : null,
                'form_saved'          => $formSaved,
                'total_students'      => $totalStudents,
                'has_engagement'      => $hasEngagement,
                'has_publications'    => $hasPublications,
                'has_ethical_approval'=> $hasEthicalApproval,
                'utilization_pct'     => $utilization,
                'spending_status'     => $spendingStatus,
            ];
        });

        $footer = $this->buildStudentGrantFooter($rows);

        return [
            'rows'          => $rows,
            'footer'        => $footer,
            'totalProjects' => $totalProjects,
            'program'       => $program,
        ];
    }

    /**
     * Build footer for student grant report.
     */
    protected function buildStudentGrantFooter(Collection $rows): array
    {
        $total = $rows->count();

        return [
            'form_saved' => [
                'completed' => $rows->where('form_saved', true)->count(),
                'pending'   => $total - $rows->where('form_saved', true)->count(),
            ],
            'total_students' => $rows->sum('total_students'),
            'engagement' => [
                'completed' => $rows->where('has_engagement', true)->count(),
                'pending'   => $total - $rows->where('has_engagement', true)->count(),
            ],
            'publications' => [
                'completed' => $rows->where('has_publications', true)->count(),
                'pending'   => $total - $rows->where('has_publications', true)->count(),
            ],
        ];
    }

    /**
     * Compute the footer aggregate: completed + pending per column.
     */
    protected function buildFooter(Collection $rows): array
    {
        $total = $rows->count();

        return [
            'registration' => [
                'completed' => $rows->where('registration', true)->count(),
                'pending'   => $total - $rows->where('registration', true)->count(),
            ],
            'outcomes' => [
                'completed' => $rows->where('outcomes_count', '>', 0)->count(),
                'pending'   => $total - $rows->where('outcomes_count', '>', 0)->count(),
            ],
            'students' => [
                'completed' => $rows->where('students_count', '>', 0)->count(),
                'pending'   => $total - $rows->where('students_count', '>', 0)->count(),
            ],
            'contributions' => [
                'completed' => $rows->where('contributions_count', '>', 0)->count(),
                'pending'   => $total - $rows->where('contributions_count', '>', 0)->count(),
            ],
            'progress_report' => [
                'completed' => $rows->where('has_progress_report', true)->count(),
                'pending'   => $total - $rows->where('has_progress_report', true)->count(),
            ],
            'final_report' => [
                'completed' => $rows->where('has_final_report', true)->count(),
                'pending'   => $total - $rows->where('has_final_report', true)->count(),
            ],
            'readiness_report' => [
                'completed' => $rows->where('has_readiness_report', true)->count(),
                'pending'   => $total - $rows->where('has_readiness_report', true)->count(),
            ],
            'reviewer_count' => [
                'completed' => $rows->where('reviewer_count', '>', 0)->count(),
                'pending'   => $total - $rows->where('reviewer_count', '>', 0)->count(),
            ],
            'progress_grading' => [
                'completed' => $rows->where('progress_grading_count', '>', 0)->count(),
                'pending'   => $total - $rows->where('progress_grading_count', '>', 0)->count(),
            ],
            'final_grading' => [
                'completed' => $rows->where('final_grading_count', '>', 0)->count(),
                'pending'   => $total - $rows->where('final_grading_count', '>', 0)->count(),
            ],
        ];
    }

    /**
     * Get the pending projects for a specific column.
     */
    public function getPendingProjects(Collection $rows, string $column): Collection
    {
        switch ($column) {
            case 'registration':
                return $rows->where('registration', false);
            case 'outcomes':
                return $rows->where('outcomes_count', 0);
            case 'students':
                return $rows->where('students_count', 0);
            case 'contributions':
                return $rows->where('contributions_count', 0);
            case 'progress_report':
                return $rows->where('has_progress_report', false);
            case 'final_report':
                return $rows->where('has_final_report', false);
            case 'readiness_report':
                return $rows->where('has_readiness_report', false);
            case 'reviewer_count':
                return $rows->where('reviewer_count', 0);
            case 'progress_grading':
                return $rows->where('progress_grading_count', 0);
            case 'final_grading':
                return $rows->where('final_grading_count', 0);
            default:
                return collect();
        }
    }

    /**
     * Determine the email subject and body hint for a given column.
     */
    public function getColumnEmailContext(string $column): array
    {
        switch ($column) {
            case 'registration':
                return [
                    'subject' => 'Reminder: Project Registration Pending',
                    'message' => 'You have projects pending registration in the current cycle. Please complete the registration at your earliest convenience.',
                ];
            case 'outcomes':
                return [
                    'subject' => 'Reminder: Outcomes Not Yet Added',
                    'message' => 'You have projects with no outcomes recorded. Please add your research outcomes.',
                ];
            case 'students':
                return [
                    'subject' => 'Reminder: Students Not Yet Added',
                    'message' => 'You have projects with no students recorded. Please add your research students.',
                ];
            case 'contributions':
                return [
                    'subject' => 'Reminder: Contributions Not Yet Added',
                    'message' => 'You have projects with no contributions recorded. Please add your research contributions.',
                ];
            case 'progress_report':
                return [
                    'subject' => 'Reminder: Progress Report Not Submitted',
                    'message' => 'You have projects with pending progress reports. Please submit the progress report before the deadline.',
                ];
            case 'final_report':
                return [
                    'subject' => 'Reminder: Final Report Not Submitted',
                    'message' => 'You have projects with pending final reports. Please submit the final report before the deadline.',
                ];
            case 'readiness_report':
                return [
                    'subject' => 'Reminder: Readiness Report Not Submitted',
                    'message' => 'You have projects with pending readiness reports. Please submit the readiness report before the deadline.',
                ];
            case 'reviewer_count':
                return [
                    'subject' => 'Action Required: Assign Reviewers',
                    'message' => 'The following projects have no reviewers assigned. Kindly assign reviewers to proceed with the evaluation.',
                ];
            case 'progress_grading':
                return [
                    'subject' => 'Reminder: Progress Grading Pending',
                    'message' => 'The following projects have not been graded for their progress reports.',
                ];
            case 'final_grading':
                return [
                    'subject' => 'Reminder: Final Grading Pending',
                    'message' => 'The following projects have not been graded for their final reports.',
                ];
            default:
                return [
                    'subject' => 'Reminder: Action Required',
                    'message' => 'You have pending items in the cycle progress report.',
                ];
        }
    }

    /**
     * Determine if the reminder for a column should go to LPIs or admins.
     */
    public function getRecipientType(string $column): string
    {
        if ($column === 'reviewer_count') {
            return 'admin';
        }
        if (in_array($column, ['progress_grading', 'final_grading'])) {
            return 'reviewer';
        }
        return 'lpi';
    }
}
