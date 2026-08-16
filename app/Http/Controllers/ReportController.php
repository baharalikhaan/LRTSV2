<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Grant;
use App\Models\Pillar;
use App\Models\Project;
use App\Models\ProjectBudget;
use App\Models\CycleConfig;
use App\Models\College;
use App\Models\User;
use App\Models\ReportReminderSent;
use App\Services\CycleProgressReportService;
use App\Mail\ProjectReminderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Only Admin users can view reports
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->isAdmin()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized. Only admins can access reports.'], 403);
                }
                return redirect()->route('home')->with('error', 'Unauthorized. Only admins can access reports.');
            }
            return $next($request);
        });
    }

    /**
     * Program Status Report — shows all programs with deadline-based active/inactive status,
     * project counts, and registration progress. Accepts optional cycle_id and grant_id filter.
     */
    public function programReport(Request $request)
    {
        $cycles = CycleConfig::orderBy('year', 'desc')->orderBy('title')->get();
        $grants = Grant::orderBy('grant_code')->get();

        $query = Program::with(['grant', 'cycleConfig', 'projects']);

        if ($request->filled('cycle_id')) {
            $query->where('cycle_id', $request->cycle_id);
        }

        if ($request->filled('grant_id')) {
            $query->where('grant_id', $request->grant_id);
        }

        $programs = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total' => $programs->count(),
            'active' => $programs->filter(fn($p) => $p->isActive())->count(),
            'inactive' => $programs->filter(fn($p) => !$p->isActive())->count(),
            'total_projects' => $programs->sum(fn($p) => $p->projects->count()),
            'registered_projects' => $programs->sum(fn($p) => $p->projects->filter(fn($pr) => $pr->added ?? false)->count()),
        ];

        return view('reports.program-status', compact('programs', 'summary', 'cycles', 'grants'));
    }

    /**
     * Export program report as CSV.
     */
    public function programReportCsv()
    {
        $programs = Program::with(['grant', 'cycleConfig'])->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="program-status-report-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($programs) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, [
                'ID',
                'Program Title',
                'Grant Code',
                'Cycle',
                'Status',
                'Progress Report 1 Deadline',
                'Ext. Progress Report 1 Deadline',
                'Progress Report 2 Deadline',
                'Ext. Progress Report 2 Deadline',
                'Final Report Deadline',
                'Ext. Final Report Deadline',
                'Total Projects',
                'Registered Projects',
                'Pending Projects',
                'Created At',
            ]);

            foreach ($programs as $program) {
                $totalProjects = $program->projects->count();
                $registeredProjects = $program->projects->filter(fn($p) => $p->added ?? false)->count();
                $pendingProjects = $totalProjects - $registeredProjects;

                fputcsv($handle, [
                    $program->id,
                    $program->program_title,
                    $program->grant ? $program->grant->grant_code : 'N/A',
                    $program->cycleConfig ? $program->cycleConfig->title : 'N/A',
                    $program->isActive() ? 'Active' : 'Inactive',
                    $program->prog_rpt_deadline ? $program->prog_rpt_deadline->format('Y-m-d') : '',
                    $program->prog_rpt2_deadline ? $program->prog_rpt2_deadline->format('Y-m-d') : '',
                    $program->final_rpt_deadline ? $program->final_rpt_deadline->format('Y-m-d') : '',
                    $totalProjects,
                    $registeredProjects,
                    $pendingProjects,
                    $program->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Grant Summary Report — shows grants with program counts and status breakdown.
     * Accepts optional cycle_id, category, and status filter.
     */
    public function grantReport(Request $request)
    {
        $cycles = CycleConfig::orderBy('year', 'desc')->orderBy('title')->get();
        $categories = Grant::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        $query = Grant::with('programs.projects');

        if ($request->filled('cycle_id')) {
            $query->whereHas('programs', function ($q) use ($request) {
                $q->where('cycle_id', $request->cycle_id);
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $grants = $query->orderBy('grant_code')->get();

        $grantData = $grants->map(function ($grant) {
            $programs = $grant->programs;
            return [
                'grant' => $grant,
                'total_programs' => $programs->count(),
                'active_programs' => $programs->filter(fn($p) => $p->isActive())->count(),
                'inactive_programs' => $programs->filter(fn($p) => !$p->isActive())->count(),
                'total_projects' => $programs->sum(fn($p) => $p->projects->count()),
                'registered_projects' => $programs->sum(fn($p) => $p->projects->filter(fn($pr) => $pr->added ?? false)->count()),
            ];
        });

        // Apply post-query status filter
        $statusFilter = $request->input('program_status');
        if ($statusFilter === 'with_active') {
            $grantData = $grantData->filter(fn($row) => $row['active_programs'] > 0);
        } elseif ($statusFilter === 'all_inactive') {
            $grantData = $grantData->filter(fn($row) => $row['active_programs'] === 0);
        }

        return view('reports.grant-summary', compact('grantData', 'cycles', 'categories'));
    }

    /**
     * Project Status Report — shows all projects with their program status and workflow stage.
     * Accepts optional cycle_id, grant_id, program_id, workflow_stage, and registered filter.
     */
    public function projectReport(Request $request)
    {
        $cycles = CycleConfig::orderBy('year', 'desc')->orderBy('title')->get();
        $grants = Grant::orderBy('grant_code')->get();
        $programs = Program::orderBy('program_title')->get();

        $query = Project::with(['program.grant', 'program.cycleConfig', 'lpi'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('cycle_id')) {
            $query->whereHas('program', function ($q) use ($request) {
                $q->where('cycle_id', $request->cycle_id);
            });
        }

        if ($request->filled('grant_id')) {
            $query->whereHas('program', function ($q) use ($request) {
                $q->where('grant_id', $request->grant_id);
            });
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        $projects = $query->get();

        // Apply post-query filters
        if ($request->filled('workflow_stage')) {
            $projects = $projects->filter(fn($p) => $p->currentWorkflowStatus() === $request->workflow_stage);
        }

        if ($request->filled('registered')) {
            $isRegistered = $request->registered === 'yes';
            $projects = $projects->filter(fn($p) => ($p->added ?? false) === $isRegistered);
        }

        $summary = [
            'total' => $projects->count(),
            'registered' => $projects->filter(fn($p) => $p->added ?? false)->count(),
            'pending' => $projects->filter(fn($p) => !($p->added ?? false))->count(),
            'in_inactive_programs' => $projects->filter(fn($p) => $p->program && !$p->program->isActive())->count(),
        ];

        return view('reports.project-status', compact('projects', 'summary', 'cycles', 'grants', 'programs'));
    }

    /**
     * Pillar Summary Report — shows pillars with project counts, student involvement, and status breakdown.
     * Accepts optional pillar_id and grant_id filter.
     */
    public function pillarReport(Request $request)
    {
        $pillars = Pillar::orderBy('pillar')->get();
        $grants = Grant::orderBy('grant_code')->get();

        $query = Pillar::with('projects.students', 'projects.lpi', 'projects.program.grant')
            ->orderBy('pillar');

        if ($request->filled('pillar_id')) {
            $query->where('id', $request->pillar_id);
        }

        if ($request->filled('grant_id')) {
            $query->whereHas('projects.program', function ($q) use ($request) {
                $q->where('grant_id', $request->grant_id);
            });
        }

        $pillars = $query->get();

        $pillarData = $pillars->map(function ($pillar) {
            $projects = $pillar->projects;
            return [
                'pillar' => $pillar,
                'total_projects' => $projects->count(),
                'registered_projects' => $projects->filter(fn($p) => $p->added ?? false)->count(),
                'total_students' => $projects->sum(fn($p) => $p->students->count()),
                'total_lpis' => $projects->pluck('lpi_id')->unique()->filter()->count(),
            ];
        });

        return view('reports.pillar-summary', compact('pillarData', 'pillars', 'grants'));
    }

    /**
     * Cycle Progress Report — shows per-cycle project status across all report dimensions.
     * Admin selects a cycle, sees one row per project with footer summary and email reminder actions.
     */
    public function cycleProgressReport(Request $request)
    {
        $service = new CycleProgressReportService();
        $programs = Program::with('grant')->orderBy('program_title')->get();

        $programId = $request->input('program_id') ? (int) $request->input('program_id') : null;
        $report = null;
        $isStudentGrant = false;

        if ($programId) {
            $program = Program::with('grant')->find($programId);
            $isStudentGrant = $program && $program->grant && $program->grant->category === 'student';

            if ($isStudentGrant) {
                $report = $service->buildStudentGrantReport($programId);
            } else {
                $report = $service->buildReportByProgram($programId);
            }
        }

        return view('reports.cycle-progress-report', [
            'rows'           => $report ? $report['rows'] : collect(),
            'footer'         => $report ? $report['footer'] : [],
            'totalProjects'  => $report ? $report['totalProjects'] : 0,
            'program'        => $report ? $report['program'] : null,
            'programs'       => $programs,
            'programId'      => $programId,
            'isStudentGrant' => $isStudentGrant,
            'columns'        => CycleProgressReportService::COLUMNS,
        ]);
    }

    /**
     * Student Grant Summary Report — shows student-specific project data.
     */
    public function studentGrantSummary(Request $request)
    {
        $programs = Program::with('grant')->whereHas('grant', function ($q) {
            $q->where('category', 'student');
        })->orderBy('program_title')->get();

        $programId = $request->input('program_id') ? (int) $request->input('program_id') : null;

        $rows = collect();
        $footer = [];
        $totalProjects = 0;
        $program = null;

        if ($programId) {
            $program = Program::find($programId);

            // Get all student grant projects in this program
            $projects = Project::query()
                ->where('program_id', $programId)
                ->with(['lpi', 'program'])
                ->get();

            $projectIds = $projects->pluck('id')->toArray();
            $totalProjects = $projects->count();

            if ($totalProjects > 0) {
                // Get total student counts per project
                $studentCounts = DB::table('project_students')
                    ->whereIn('project_id', $projectIds)
                    ->select('project_id', DB::raw('COUNT(*) as total'))
                    ->groupBy('project_id')
                    ->pluck('total', 'project_id');

                // Get budget data from ProjectBudget
                $budgetData = ProjectBudget::whereIn('project_id', $projectIds)
                    ->pluck('actual_exp_amount', 'project_id');

                $budgetAmounts = ProjectBudget::whereIn('project_id', $projectIds)
                    ->pluck('budget_amount', 'project_id');

                // Build rows
                $rows = $projects->map(function ($project) use ($studentCounts, $budgetData, $budgetAmounts) {
                    $id = $project->id;
                    $totalStudents = $studentCounts[$id] ?? 0;

                    // Check form saved (registration status)
                    $formSaved = $project->hasStatus(Project::STATUS_REGISTERED);

                    // Check engagement (column may not exist in new system)
                    $hasEngagement = false;
                    if (in_array('student_engagement', $project->getFillable()) || \Illuminate\Support\Facades\Schema::hasColumn('projects', 'student_engagement')) {
                        $hasEngagement = !empty($project->student_engagement);
                    }

                    // Check publications - first check if column exists, then check outcomes table
                    $hasPublications = false;
                    if (\Illuminate\Support\Facades\Schema::hasColumn('projects', 'publications')) {
                        $hasPublications = !empty($project->publications);
                    }
                    // Also check outcomes table for publications
                    if (!$hasPublications) {
                        $hasPublications = $project->outcomes()->whereIn('type', [
                            'journal_q1', 'journal_q2', 'journal_q3', 'journal_q4',
                            'conference', 'book', 'edited_book', 'book_chapter'
                        ])->count() > 0;
                    }

                    // Check ethical approval (submission exists)
                    $hasEthicalApproval = $project->submissions()
                        ->where('type', 'readiness')
                        ->count() > 0;

                    // Calculate spending from ProjectBudget
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

                // Build footer
                $footer = [
                    'form_saved' => [
                        'completed' => $rows->where('form_saved', true)->count(),
                        'pending'   => $totalProjects - $rows->where('form_saved', true)->count(),
                    ],
                    'total_students' => $rows->sum('total_students'),
                    'engagement' => [
                        'completed' => $rows->where('has_engagement', true)->count(),
                        'pending'   => $totalProjects - $rows->where('has_engagement', true)->count(),
                    ],
                    'publications' => [
                        'completed' => $rows->where('has_publications', true)->count(),
                        'pending'   => $totalProjects - $rows->where('has_publications', true)->count(),
                    ],
                ];
            }
        }

        return view('reports.student-grant-summary', [
            'rows'           => $rows,
            'footer'         => $footer,
            'totalProjects'  => $totalProjects,
            'program'        => $program,
            'programs'       => $programs,
            'programId'      => $programId,
        ]);
    }

    /**
     * Send reminder emails for a specific column of the cycle progress report.
     * For pending projects, sends to LPIs/reviewers/admins as appropriate.
     * Returns JSON response for AJAX handling.
     */
    public function sendCycleReportReminder(Request $request)
    {
        $request->validate([
            'program_id' => 'required|integer|exists:programs,id',
            'column_key' => 'required|string|in:' . implode(',', array_keys(CycleProgressReportService::COLUMNS)),
        ]);

        $service = new CycleProgressReportService();
        $programId = (int) $request->input('program_id');
        $columnKey = $request->input('column_key');

        $report = $service->buildReportByProgram($programId);
        $pendingProjects = $service->getPendingProjects($report['rows'], $columnKey);

        if ($pendingProjects->isEmpty()) {
            return response()->json([
                'success' => true,
                'count'   => 0,
                'message' => 'No pending projects for this column.',
            ]);
        }

        $programTitle = $report['program'] ? $report['program']->program_title : 'Unknown Research Call';
        $recipientType = $service->getRecipientType($columnKey);
        $emailCount = 0;

        // Load full project models with relationships for email sending
        $pendingProjectIds = $pendingProjects->pluck('id')->toArray();
        $fullProjects = Project::whereIn('id', $pendingProjectIds)
            ->with(['lpi', 'program'])
            ->get();

        if ($recipientType === 'lpi') {
            // Group by LPI email, send one email per LPI
            $grouped = $fullProjects->where('lpi', '!=', null)->groupBy('lpi.email');

            foreach ($grouped as $email => $projects) {
                $lpiName = $projects->first()->lpi->name;

                foreach ($projects as $project) {
                    Mail::to($email)->queue(
                        new ProjectReminderMail($columnKey, $project->title, $programTitle, $lpiName)
                    );

                    ReportReminderSent::create([
                        'program_id'      => $project->program_id,
                        'column_key'      => $columnKey,
                        'project_id'      => $project->id,
                        'recipient_email' => $email,
                        'recipient_type'  => 'lpi',
                        'sent_at'         => now(),
                    ]);

                    $emailCount++;
                }
            }
        } elseif ($recipientType === 'reviewer') {
            // Find assigned reviewers for pending projects
            foreach ($fullProjects as $project) {
                $reviewer = DB::table('projects_reviewers')
                    ->where('project_id', $project->id)
                    ->first();

                if (!$reviewer) {
                    continue;
                }

                $reviewerUser = User::find($reviewer->user_id);
                if (!$reviewerUser) {
                    continue;
                }

                Mail::to($reviewerUser->email)->queue(
                    new ProjectReminderMail($columnKey, $project->title, $programTitle, $reviewerUser->name)
                );

                ReportReminderSent::create([
                    'program_id'      => $project->program_id,
                    'column_key'      => $columnKey,
                    'project_id'      => $project->id,
                    'recipient_email' => $reviewerUser->email,
                    'recipient_type'  => 'reviewer',
                    'sent_at'         => now(),
                ]);

                $emailCount++;
            }
        } else {
            // Admin: send to all admin users
            $admins = User::where('type', 'Admin')
                ->orWhere('type', 'Admin+LPI+Reviewer')
                ->get();

            $pendingTitles = $pendingProjects->pluck('title')->implode(', ');

            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(
                    new ProjectReminderMail($columnKey, $pendingTitles, $programTitle, $admin->name)
                );

                foreach ($pendingProjects as $project) {
                    ReportReminderSent::create([
                        'program_id'      => null,
                        'column_key'      => $columnKey,
                        'project_id'      => $project['id'],
                        'recipient_email' => $admin->email,
                        'recipient_type'  => 'admin',
                        'sent_at'         => now(),
                    ]);
                }

                $emailCount++;
            }
        }

        return response()->json([
            'success' => true,
            'count'   => $emailCount,
            'message' => "{$emailCount} reminder(s) sent successfully.",
        ]);
    }

    /**
     * Budget Utilization Report — shows all project budgets with utilization metrics.
     */
    public function budgetUtilizationReport(Request $request)
    {
        $programs = Program::with('grant', 'cycle')
            ->withCount('projects as project_count')
            ->orderByDesc('id')
            ->get();

        return view('reports.budget-utilization', compact('programs'));
    }
}
