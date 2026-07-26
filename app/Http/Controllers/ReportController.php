<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Grant;
use App\Models\Pillar;
use App\Models\Project;
use App\Models\CycleConfig;
use App\Models\College;
use Illuminate\Http\Request;

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
                    $program->extended_prog_rpt_deadline ? $program->extended_prog_rpt_deadline->format('Y-m-d') : '',
                    $program->prog_rpt2_deadline ? $program->prog_rpt2_deadline->format('Y-m-d') : '',
                    $program->extended_prog_rpt2_deadline ? $program->extended_prog_rpt2_deadline->format('Y-m-d') : '',
                    $program->final_rpt_deadline ? $program->final_rpt_deadline->format('Y-m-d') : '',
                    $program->extended_final_rpt_deadline ? $program->extended_final_rpt_deadline->format('Y-m-d') : '',
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
}
