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
        'progress2_report' => [
            'key' => 'progress2_report',
            'label' => 'Progress Report 2',
            'group' => 'LPI',
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
            ->orderBy(Program::select('program_title')->whereColumn('programs.id', 'projects.program_id')->limit(1))
            ->orderBy('projects.id')
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

        $progress2ReportProjects = DB::table('project_submissions')
            ->whereIn('project_id', $projectIds)
            ->where('type', 'progress2')
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
            $progressReportProjects, $progress2ReportProjects, $finalReportProjects, $readinessReportProjects,
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
                'progress2_enabled'       => (bool) $project->extended_progress,
                'has_progress2_report'    => $progress2ReportProjects->contains($id),
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
            ->orderBy('projects.id')
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

        $progress2ReportProjects = DB::table('project_submissions')
            ->whereIn('project_id', $projectIds)
            ->where('type', 'progress2')
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
            $progressReportProjects, $progress2ReportProjects, $finalReportProjects, $readinessReportProjects,
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
                'progress2_enabled'       => (bool) $project->extended_progress,
                'has_progress2_report'    => $progress2ReportProjects->contains($id),
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
            ->orderBy('projects.id')
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

        // Qatari counts per project
        $qatariCounts = DB::table('project_students')
            ->whereIn('project_id', $projectIds)
            ->whereRaw("LOWER(nationality) = 'qatari'")
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        // Non-Qatari counts per project
        $nonQatariCounts = DB::table('project_students')
            ->whereIn('project_id', $projectIds)
            ->whereRaw("LOWER(nationality) = 'non-qatari'")
            ->select('project_id', DB::raw('COUNT(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        // Budget data from projects.requested_budget_qar
        $requestedBudgets = DB::table('projects')
            ->whereIn('id', $projectIds)
            ->pluck('requested_budget_qar', 'id');

        // Spending data from projects.spending column
        $projectSpending = DB::table('projects')
            ->whereIn('id', $projectIds)
            ->select('id', 'spending')
            ->pluck('spending', 'id');

        // Registered projects
        $registeredProjects = DB::table('status_histories')
            ->whereIn('project_id', $projectIds)
            ->where('status', 'registered')
            ->distinct()
            ->pluck('project_id');

        // Build rows
        $rows = $projects->map(function ($project) use ($studentCounts, $qatariCounts, $nonQatariCounts, $requestedBudgets, $projectSpending, $registeredProjects) {
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
            $budget = $requestedBudgets[$id] ?? $project->requested_budget_qar ?? 0;
            $spending = (float) ($projectSpending[$id] ?? 0);
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
                'qatari_count'        => $qatariCounts[$id] ?? 0,
                'non_qatari_count'    => $nonQatariCounts[$id] ?? 0,
                'has_engagement'      => $hasEngagement,
                'has_publications'    => $hasPublications,
                'has_ethical_approval'=> $hasEthicalApproval,
                'budget'              => $budget,
                'spending'            => $spending,
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
            'qatari_count' => $rows->sum('qatari_count'),
            'non_qatari_count' => $rows->sum('non_qatari_count'),
            'engagement' => [
                'completed' => $rows->where('has_engagement', true)->count(),
                'pending'   => $total - $rows->where('has_engagement', true)->count(),
            ],
            'publications' => [
                'completed' => $rows->where('has_publications', true)->count(),
                'pending'   => $total - $rows->where('has_publications', true)->count(),
            ],
            'total_budget' => $rows->sum('budget'),
            'total_spending' => $rows->sum('spending'),
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
            'progress2_report' => [
                'completed' => $rows->where('progress2_enabled', true)->where('has_progress2_report', true)->count(),
                'pending'   => $rows->where('progress2_enabled', true)->where('has_progress2_report', false)->count(),
                'not_applicable' => $total - $rows->where('progress2_enabled', true)->count(),
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
            case 'progress2_report':
                return $rows->filter(fn ($row) => ($row['progress2_enabled'] ?? false) && !($row['has_progress2_report'] ?? false));
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
     * Official Qatar University page that hosts all post-award report forms
     * (Progress, Final and Readiness Mapping templates). The reminder emails
     * link here instead of to a local copy, so the LPI always downloads the
     * current form.
     */
    public const TEMPLATE_URL = 'https://www.qu.edu.qa/en-us/Research/offices/research-support/post-awards-forms';

    /**
     * Report templates offered in the reminder, keyed by the column type and
     * labelled for the "Online templates" instruction.
     */
    public const REPORT_TEMPLATES = [
        'progress'  => 'Progress Report template',
        'final'     => 'Final Report template',
        'readiness' => 'Readiness Mapping Report template',
    ];

    /**
     * Submission requirements listed under "Instructions" in every reminder
     * sent to an LPI. The first point carries the template links.
     *
     * @return array<int, array{label:string,text:string,templates?:bool}>
     */
    public static function instructionPoints(): array
    {
        return [
            [
                'label'     => 'Online templates',
                'text'      => 'The official templates are available online — use the one for the report you are submitting.',
                'templates' => true,
            ],
            [
                'label' => 'Required signature',
                'text'  => 'The report must be signed by the Associate Dean of Research of the respective College or Center.',
            ],
            [
                'label' => 'Grant information',
                'text'  => 'The grant number must match the one provided in the email.',
            ],
            [
                'label' => 'Project title',
                'text'  => 'The project title must exactly match the title provided in the email.',
            ],
            [
                'label' => 'Support and queries',
                'text'  => 'If you encounter any issues, have questions, or need clarification, please email ros-submissions@qu.edu.qa. Our team will be happy to assist you.',
            ],
        ];
    }

    /** Off-campus access notice shown at the end of the LPI reminders. */
    public const DISCLAIMER = \App\Mail\GenericEmailMail::DISCLAIMER;

    /**
     * Determine the email subject, message, guidance and the deadline that
     * belongs to a given column.
     *
     * Returned keys:
     *   subject        — email subject
     *   message        — short one-line summary (kept for backwards compatibility)
     *   intro          — opening paragraph of the email
     *   guidance       — array of bullet points explaining what is required
     *   action         — closing paragraph telling the recipient what to do
     *   deadline_field — Program column holding the deadline (null = none)
     *   deadline_label — human label for that deadline
     *   template_type  — report template offered for that column (null = none)
     *   template_label — human label for that template
     *   instructions   — merged checklist: the guidance points followed by the
     *                    submission requirements (the latter LPI-only)
     *   templates      — report templates to link (LPI reminders only)
     *   disclaimer     — off-campus access notice (LPI reminders only)
     */
    public function getColumnEmailContext(string $column): array
    {
        $context = $this->columnContext($column);

        // "What is required" and "Instructions" were merged into one
        // "Instructions" checklist: the stage-specific guidance first, then the
        // general submission requirements.
        $points = [];
        foreach (($context['guidance'] ?? []) as $guidancePoint) {
            $points[] = ['label' => null, 'text' => $guidancePoint];
        }

        // Only the LPI reminders carry the submission requirements and the VPN
        // disclaimer; reviewers and admins action the project in RTS.
        if ($this->getRecipientType($column) === 'lpi') {
            // Link the official QU post-award forms page, labelled with the
            // report this reminder is asking for (Progress Report 2 is
            // submitted on the Progress Report form).
            $type = $context['template_type'] ?? null;
            if ($type === 'progress2') {
                $type = 'progress';
            }

            $templates = ($type && isset(self::REPORT_TEMPLATES[$type]))
                ? [self::REPORT_TEMPLATES[$type] => self::TEMPLATE_URL]
                : ['Post-Award Forms' => self::TEMPLATE_URL];

            $requirements = self::instructionPoints();
            $requirements[0]['text'] = count($templates) === 1 && $type
                ? 'The official template for this report is available online — please use it for your submission.'
                : 'The official templates are available online — use the one for the report you are submitting.';

            foreach ($requirements as $requirement) {
                $points[] = $requirement;
            }

            $context['templates']  = $templates;
            $context['disclaimer'] = self::DISCLAIMER;
        }

        $context['instructions'] = $points;

        return $context;
    }

    /**
     * Raw per-column context (without the merged instructions / disclaimer).
     */
    private function columnContext(string $column): array
    {
        switch ($column) {
            case 'registration':
                return [
                    'subject'        => 'Project Registration Required',
                    'message'        => 'You have projects pending registration in the current cycle. Please complete the registration at your earliest convenience.',
                    'intro'          => 'Our records show that the project below has not been registered for the current research cycle yet.',
                    'guidance'       => [
                        'Complete the registration form: team, research pillars and administering college.',
                        'Confirm the budget, objectives, methodology and expected outcomes.',
                        'Registration is required before any report can be submitted.',
                    ],
                    'action'         => 'Please complete the registration at your earliest convenience so the project can move to the review stage.',
                    'deadline_field' => null,
                    'deadline_label' => null,
                ];

            case 'outcomes':
                return [
                    'subject'        => 'Outcome Documentation Required',
                    'message'        => 'You have projects with no outcomes recorded. Please add your research outcomes.',
                    'intro'          => 'Our records show that no research outcomes have been recorded for the project below.',
                    'guidance'       => [
                        'Record each outcome: journal articles, conference papers, books or chapters.',
                        'Include intellectual property: disclosures, patents, software, prototypes or awards.',
                        'Add the identifier (DOI or patent number) and the online date.',
                    ],
                    'action'         => 'Please add the outcomes achieved so far. They are compared against the commitments declared at registration and drive the project evaluation.',
                    'deadline_field' => 'prog_rpt_deadline',
                    'deadline_label' => 'Progress Report 1 deadline',
                ];

            case 'students':
                return [
                    'subject'        => 'Student Involvement Details Required',
                    'message'        => 'You have projects with no students recorded. Please add your research students.',
                    'intro'          => 'Our records show that no students have been recorded against the project below.',
                    'guidance'       => [
                        'Add every student on the project — undergraduate, master\'s or PhD.',
                        'Record the QU student ID, study level, role and days contributed.',
                    ],
                    'action'         => 'Please add the students involved so the project record is complete and the student commitments can be verified.',
                    'deadline_field' => 'prog_rpt_deadline',
                    'deadline_label' => 'Progress Report 1 deadline',
                ];

            case 'contributions':
                return [
                    'subject'        => 'Team Contribution Details Required',
                    'message'        => 'You have projects with no contributions recorded. Please add your research contributions.',
                    'intro'          => 'Our records show that no contributions have been recorded for the project below.',
                    'guidance'       => [
                        'Record one entry per team member with a short description of their role.',
                        'Contributions cover methodology, data collection, analysis, software and writing.',
                    ],
                    'action'         => 'Please add the contributions so the involvement of the whole team is documented.',
                    'deadline_field' => 'prog_rpt_deadline',
                    'deadline_label' => 'Progress Report 1 deadline',
                ];

            case 'progress_report':
                return [
                    'template_type'  => 'progress',
                    'template_label' => 'Progress Report 1 template',
                    'subject'        => 'Progress Report 1 Submission Required',
                    'message'        => 'You have projects with pending progress reports. Please submit the progress report before the deadline.',
                    'intro'          => 'Our records show that the Progress Report 1 for the project below has not been submitted yet.',
                    'guidance'       => [
                        'Upload the report as a PDF.',
                    ],
                    'action'         => 'Please submit the progress report before the deadline shown above.',
                    'deadline_field' => 'prog_rpt_deadline',
                    'deadline_label' => 'Progress Report 1 deadline',
                ];

            case 'progress2_report':
                return [
                    'template_type'  => 'progress2',
                    'template_label' => 'Progress Report 2 template',
                    'subject'        => 'Progress Report 2 Submission Required',
                    'message'        => 'You have extended-progress projects with pending Progress Report 2 submissions. Please submit Progress Report 2 before the deadline.',
                    'intro'          => 'The project below has extended progress enabled, and its Progress Report 2 has not been submitted yet.',
                    'guidance'       => [
                        'Complete the outcomes, students and contributions for the extended period.',
                    ],
                    'action'         => 'Please submit Progress Report 2 before the deadline shown above.',
                    'deadline_field' => 'prog_rpt2_deadline',
                    'deadline_label' => 'Progress Report 2 deadline',
                ];

            case 'final_report':
                return [
                    'template_type'  => 'final',
                    'template_label' => 'Final Report template',
                    'subject'        => 'Final Report Submission Required',
                    'message'        => 'You have projects with pending final reports. Please submit the final report before the deadline.',
                    'intro'          => 'Our records show that the final report for the project below has not been submitted yet.',
                    'guidance'       => [
                        'Complete the final outcomes, students and contributions sections.',
                    ],
                    'action'         => 'Please submit the final report before the deadline shown above.',
                    'deadline_field' => 'final_rpt_deadline',
                    'deadline_label' => 'Final report deadline',
                ];

            case 'readiness_report':
                return [
                    'template_type'  => 'readiness',
                    'template_label' => 'Readiness Report template',
                    'subject'        => 'Readiness Report Submission Required',
                    'message'        => 'You have projects with pending readiness reports. Please submit the readiness report before the deadline.',
                    'intro'          => 'The readiness report for the project below has not been submitted yet.',
                    'guidance'       => [
                        'Upload the readiness report as a PDF from the project page.',
                    ],
                    'action'         => 'Please submit the readiness report so the final evaluation can proceed.',
                    'deadline_field' => 'final_rpt_deadline',
                    'deadline_label' => 'Final report deadline',
                ];

            case 'reviewer_count':
                return [
                    'subject'        => 'Reviewer Assignment Required',
                    'message'        => 'The following projects have no reviewers assigned. Kindly assign reviewers to proceed with the evaluation.',
                    'intro'          => 'The projects listed below do not have a reviewer assigned yet.',
                    'guidance'       => [
                        'Each project needs at least one reviewer.',
                        'Assign from the Reviewer Assignment page, avoiding conflicts of interest.',
                    ],
                    'action'         => 'Please assign reviewers so the evaluation of these projects can start.',
                    'deadline_field' => null,
                    'deadline_label' => null,
                ];

            case 'progress_grading':
                return [
                    'subject'        => 'Progress Report Evaluation Pending',
                    'message'        => 'The following projects have not been graded for their progress reports.',
                    'intro'          => 'The progress reports of the projects below are waiting for your evaluation.',
                    'guidance'       => [
                        'Review the report against the commitments declared at registration.',
                        'Score the four sections and add your comments.',
                        'Submit the evaluation to release the outcome to the LPI.',
                    ],
                    'action'         => 'Please complete the progress grading at your earliest convenience.',
                    'deadline_field' => 'prog_rpt_deadline',
                    'deadline_label' => 'Progress Report 1 deadline',
                ];

            case 'final_grading':
                return [
                    'subject'        => 'Final Report Evaluation Pending',
                    'message'        => 'The following projects have not been graded for their final reports.',
                    'intro'          => 'The final reports of the projects below are waiting for your evaluation.',
                    'guidance'       => [
                        'Review the final report and the outcomes recorded against it.',
                        'Score sections A to C and add the overall assessment (D).',
                        'Submit the evaluation to complete the project.',
                    ],
                    'action'         => 'Please complete the final grading at your earliest convenience.',
                    'deadline_field' => 'final_rpt_deadline',
                    'deadline_label' => 'Final report deadline',
                ];

            default:
                return [
                    'subject'        => 'Action Required',
                    'message'        => 'You have pending items in the cycle progress report.',
                    'intro'          => 'There are pending items requiring your attention in the research call below.',
                    'guidance'       => [],
                    'action'         => 'Please review the research call and complete the outstanding items.',
                    'deadline_field' => null,
                    'deadline_label' => null,
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
