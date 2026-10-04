<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Role-aware dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->activeRole();

        // ── Admin sees the full aggregate dashboard ────────────────────────
        if ($role === 'Admin') {
            return $this->adminDashboard($user);
        }

        // ── LPI sees their own projects + available slots ──────────────────
        if ($role === 'LPI') {
            return $this->lpiDashboard($user);
        }

        // ── Reviewer sees their assigned work ──────────────────────────────
        if ($role === 'Reviewer') {
            return $this->reviewerDashboard($user);
        }

        // Fallback (just in case) – show the generic view
        return view('home', [
            'activeRole' => $role,
            'announcements' => $this->globalAnnouncements(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Admin dashboard
    // ─────────────────────────────────────────────────────────────────────────
    private function adminDashboard($user)
    {
        // ── Active programs & cycles ───────────────────────────────────────
        $activePrograms = Program::withCount('projects as project_count')
            ->active()
            ->get();

        $inactiveProgramsCount = Program::whereNotNull('final_rpt_deadline')
            ->where('final_rpt_deadline', '<=', now()->toDateTimeString())
            ->count();

        $cycles = \App\Models\CycleConfig::all();

        // ── Key aggregates ─────────────────────────────────────────────────
        $totalPrograms       = Program::count();
        $activeProgramsCount = Program::active()->count();

        // Assigned/unassigned reviewer counts
        $assignedProjects   = Project::whereHas('reviewers')->count();

        // ── Per-program stats table ──────────────────────────────────────
        $programStats = Program::withCount('projects as total_projects')
            ->get()
            ->filter(fn($p) => $p->total_projects > 0)
            ->map(function ($program) {
                $projectIds = $program->projects->pluck('id');

                // Registered projects (have 'registered' in status_histories)
                $registeredIds = \DB::table('status_histories')
                    ->whereIn('project_id', $projectIds)
                    ->where('status', 'registered')
                    ->distinct('project_id')
                    ->pluck('project_id');

                $registered     = $registeredIds->count();
                $total          = $program->total_projects;
                $nonRegistered  = $total - $registered;

                // ── PR1 (Progress Report 1) ──
                $submittedPR1 = \DB::table('project_submissions')
                    ->whereIn('project_id', $projectIds)
                    ->whereRaw("LOWER(type) = 'progress'")
                    ->distinct('project_id')
                    ->count('project_id');

                $pendingPR1 = $registered - $submittedPR1;
                if ($pendingPR1 < 0) $pendingPR1 = 0;

                $reviewedPR1 = \DB::table('progress_report_grading')
                    ->whereIn('project_id', $projectIds)
                    ->where(function ($q) {
                        $q->where('report_type', 'progress')->orWhereNull('report_type');
                    })
                    ->distinct('project_id')
                    ->count('project_id');

                $reviewPendingPR1 = $submittedPR1 - $reviewedPR1;
                if ($reviewPendingPR1 < 0) $reviewPendingPR1 = 0;

                // ── PR2 (Progress Report 2) ──
                $submittedPR2 = \DB::table('project_submissions')
                    ->whereIn('project_id', $projectIds)
                    ->whereRaw("LOWER(type) = 'progress2'")
                    ->distinct('project_id')
                    ->count('project_id');

                $pendingPR2 = $submittedPR1 - $submittedPR2;
                if ($pendingPR2 < 0) $pendingPR2 = 0;

                $reviewedPR2 = \DB::table('progress_report_grading')
                    ->whereIn('project_id', $projectIds)
                    ->where('report_type', 'progress2')
                    ->distinct('project_id')
                    ->count('project_id');

                $reviewPendingPR2 = $submittedPR2 - $reviewedPR2;
                if ($reviewPendingPR2 < 0) $reviewPendingPR2 = 0;

                // ── Readiness Report ──
                $submittedReadiness = \DB::table('project_submissions')
                    ->whereIn('project_id', $projectIds)
                    ->whereRaw("LOWER(type) = 'readiness'")
                    ->distinct('project_id')
                    ->count('project_id');

                $pendingReadiness = $registered - $submittedReadiness;
                if ($pendingReadiness < 0) $pendingReadiness = 0;

                // ── Final Report ──
                $submittedFinal = \DB::table('project_submissions')
                    ->whereIn('project_id', $projectIds)
                    ->whereRaw("LOWER(type) = 'final'")
                    ->distinct('project_id')
                    ->count('project_id');

                $pendingFinal = $submittedPR2 - $submittedFinal;
                if ($pendingFinal < 0) $pendingFinal = 0;

                $reviewedFinal = \DB::table('final_report_grading')
                    ->whereIn('project_id', $projectIds)
                    ->distinct('project_id')
                    ->count('project_id');

                $reviewPendingFinal = $submittedFinal - $reviewedFinal;
                if ($reviewPendingFinal < 0) $reviewPendingFinal = 0;

                return [
                    'name'              => $program->program_title,
                    'is_active'         => $program->isActive(),
                    'total_projects'    => $total,
                    'registered'        => $registered,
                    'non_registered'    => $nonRegistered,
                    'submitted_pr1'     => $submittedPR1,
                    'pending_pr1'       => $pendingPR1,
                    'reviewed_pr1'      => $reviewedPR1,
                    'review_pending_pr1'=> $reviewPendingPR1,
                    'submitted_pr2'     => $submittedPR2,
                    'pending_pr2'       => $pendingPR2,
                    'reviewed_pr2'      => $reviewedPR2,
                    'review_pending_pr2'=> $reviewPendingPR2,
                    'submitted_readiness' => $submittedReadiness,
                    'pending_readiness' => $pendingReadiness,
                    'submitted_final'   => $submittedFinal,
                    'pending_final'     => $pendingFinal,
                    'reviewed_final'    => $reviewedFinal,
                    'review_pending_final' => $reviewPendingFinal,
                ];
            })
            ->sortByDesc('total_projects')
            ->values();

        // ── Compute gadget totals from the table data (ensures alignment) ──
        $totalProjects       = $programStats->sum('total_projects');
        $registeredProjects   = $programStats->sum('registered');
        $unregisteredProjects = $programStats->sum('non_registered');
        $unassignedProjects  = $totalProjects - $assignedProjects;
        $reportsSubmitted     = $programStats->sum('submitted_pr1')
                              + $programStats->sum('submitted_pr2')
                              + $programStats->sum('submitted_readiness')
                              + $programStats->sum('submitted_final');
        $reportsReviewed      = $programStats->sum('reviewed_pr1')
                              + $programStats->sum('reviewed_pr2')
                              + $programStats->sum('reviewed_final');

        $totalLpis      = User::where('type', 'like', '%LPI%')->count();
        $totalReviewers = User::where('type', 'like', '%Reviewer%')->count();

        // ── Announcements ─────────────────────────────────────────────────
        $announcements = Announcement::where(function ($q) {
            $q->whereNull('audience')
              ->orWhere('audience', '')
              ->orWhereRaw('LOWER(audience) IN (?, ?)', ['all', 'admin']);
        })->where('is_active', true)
          ->where(function ($q) {
              $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
          })->latest()->take(8)->get();

        return view('dashboard.admin', [
            'activeRole'           => 'Admin',
            'activePrograms'       => $activePrograms,
            'inactiveProgramsCount'=> $inactiveProgramsCount,
            'cycles'               => $cycles,
            'totalPrograms'        => $totalPrograms,
            'activeProgramsCount'  => $activeProgramsCount,
            'totalProjects'        => $totalProjects,
            'registeredProjects'   => $registeredProjects,
            'unregisteredProjects' => $unregisteredProjects,
            'reportsSubmitted'     => $reportsSubmitted,
            'reportsReviewed'      => $reportsReviewed,
            'assignedProjects'     => $assignedProjects,
            'unassignedProjects'   => $unassignedProjects,
            'programStats'         => $programStats,
            'totalLpis'            => $totalLpis,
            'totalReviewers'       => $totalReviewers,
            'announcements'        => $announcements,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LPI dashboard
    // ─────────────────────────────────────────────────────────────────────────
    private function lpiDashboard($user)
    {
        $myProjects = Project::where('lpi_id', $user->id)
            ->with('latestStatus')
            ->with('program')
            ->with('program.grant')
            ->with('program.cycle')
            ->with('pillars')
            ->with('publications')
            ->with('students')
            ->with('outcomes')
            ->orderBy('created_at', 'desc')
            ->get();

        $statuses = Project::statusLabels();

        // Available projects (those in Registered status that don't have an LPI yet)
        $available = Project::select('id', 'title', 'created_at')
            ->whereNull('lpi_id')
            ->whereHas('statusHistories', function ($q) {
                $q->where('status', 'registered');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // ── LPI Project Statistics ────────────────────────────────────────
        $allProjectsCount = $myProjects->count();

        // Unregistered: projects claimed by this LPI but NOT yet progressed through workflow
        $unregisteredCount = $myProjects->filter(function ($p) {
            $latest = optional($p->latestStatus)->status;
            return !$latest || $latest === '' || !in_array($latest, [
                Project::STATUS_REGISTERED,
                Project::STATUS_ASSIGNED,
                Project::STATUS_CLAIMED,
                Project::STATUS_PROGRESS_ADDED,
                Project::STATUS_PROGRESS_REVIEWED,
                Project::STATUS_PROGRESS_REJECTED,
                Project::STATUS_PROGRESS_REJ_REVIEWED,
                Project::STATUS_PROGRESS2_ADDED,
                Project::STATUS_PROGRESS2_REVIEWED,
                Project::STATUS_PROGRESS2_REJECTED,
                Project::STATUS_PROGRESS2_REJ_REVIEWED,
                Project::STATUS_FINAL_ADDED,
                Project::STATUS_GRADED,
            ]);
        })->count();

        // Report Upload Pending: projects registered (or beyond) but NOT yet added progress report
        $reportUploadPendingCount = $myProjects->filter(function ($p) {
            $latest = optional($p->latestStatus)->status;
            return in_array($latest, [
                Project::STATUS_REGISTERED,
                Project::STATUS_ASSIGNED,
                Project::STATUS_CLAIMED,
            ]);
        })->count();

        // Progress Report Done: projects that have progress_added or were reviewed (but not yet added final report)
        $progressDoneCount = $myProjects->filter(function ($p) {
            $latest = optional($p->latestStatus)->status;
            return in_array($latest, [
                Project::STATUS_PROGRESS_ADDED,
                Project::STATUS_PROGRESS_REVIEWED,
                Project::STATUS_PROGRESS_REJECTED,
                Project::STATUS_PROGRESS_REJ_REVIEWED,
                Project::STATUS_PROGRESS2_ADDED,
                Project::STATUS_PROGRESS2_REVIEWED,
                Project::STATUS_PROGRESS2_REJECTED,
                Project::STATUS_PROGRESS2_REJ_REVIEWED,
                Project::STATUS_FINAL_ADDED,
            ]);
        })->count();

        // Graded: projects fully graded
        $gradedCount = $myProjects->filter(function ($p) {
            $latest = optional($p->latestStatus)->status;
            return $latest === Project::STATUS_GRADED;
        })->count();

        // ── Aggregated stats table data ──────────────────────────────────
        // Grant availed: distinct grants across this LPI's projects
        $grantsAvailed = $myProjects->filter(function ($p) {
            return $p->program && $p->program->grant;
        })->unique(function ($p) {
            return $p->program->grant_id;
        })->map(function ($p) {
            $grant = $p->program->grant;
            return [
                'id'   => $grant->id,
                'name' => $grant->grant_name,
                'code' => $grant->grant_code,
            ];
        })->values();

        // Cycles worked: distinct cycles across this LPI's projects
        $cyclesWorked = $myProjects->filter(function ($p) {
            return $p->program && $p->program->cycle;
        })->unique(function ($p) {
            return $p->program->cycle_id;
        })->map(function ($p) {
            $cycle = $p->program->cycle;
            return [
                'id'    => $cycle->id,
                'title' => $cycle->title,
            ];
        })->values();

        // Programs worked: distinct programs across this LPI's projects
        $programsWorked = $myProjects->filter(function ($p) {
            return $p->program;
        })->unique('program_id')->map(function ($p) {
            return [
                'id'    => $p->program->id,
                'title' => $p->program->program_title,
            ];
        })->values();

        // Publications: total count and list grouped by project
        // NOTE: `publications` is also a raw text column on the projects table
        // (student grants store a list there), so always go through the
        // relation method to count the structured ProjectPublication rows.
        $publicationsTotal = $myProjects->sum(function ($p) {
            return $p->publications()->count();
        });

        // Students attached: total count
        $studentsTotal = $myProjects->sum(function ($p) {
            return $p->students ? $p->students->count() : 0;
        });

        // Pillars: distinct pillars across this LPI's projects
        $pillarsWorked = $myProjects->flatMap(function ($p) {
            return $p->pillars;
        })->unique('id')->values()->map(function ($pillar) {
            return [
                'id'    => $pillar->id,
                'title' => $pillar->title ?? $pillar->pillar,
            ];
        });

        // ── LPI-specific announcements ──────────────────────────────────
        $lpiAnnouncements = Announcement::where(function ($q) {
            $q->whereNull('audience')
              ->orWhere('audience', '')
              ->orWhereRaw('LOWER(audience) IN (?, ?)', ['all', 'lpi']);
        })->where('is_active', true)
          ->where(function ($q) {
              $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
          })->latest()->take(6)->get();

        // ── Outcomes grouped by program ──────────────────────────────────
        $outcomesByProgram = [];
        foreach ($myProjects as $p) {
            if (!$p->program) continue;
            $progName = $p->program->program_title ?? '(No Program)';
            $progId = $p->program_id;
            if (!isset($outcomesByProgram[$progId])) {
                $outcomesByProgram[$progId] = [
                    'name'     => $progName,
                    'projects' => 0,
                    'outcomes' => 0,
                    'items'    => [],
                ];
            }
            $outcomesByProgram[$progId]['projects']++;
            $projectOutcomes = $p->outcomes ?? $p->outcomes()->get();
            $outcomesByProgram[$progId]['outcomes'] += $projectOutcomes->count();
            foreach ($projectOutcomes as $o) {
                $outcomesByProgram[$progId]['items'][] = [
                    'project_title' => $p->project_title ?? $p->title,
                    'project_id'    => $p->id,
                    'outcome'       => $o->outcome,
                    'status'        => $o->status,
                ];
            }
        }
        // Limit items shown per program to 3
        foreach ($outcomesByProgram as &$prog) {
            $prog['items'] = array_slice($prog['items'], 0, 3);
        }
        unset($prog);
        usort($outcomesByProgram, fn($a, $b) => strcmp($a['name'], $b['name']));

        // ── Per-program breakdown ────────────────────────────────────────
        $programsStats = [];
        foreach ($myProjects as $p) {
            $progKey = $p->program_id ?? 0;
            if (!isset($programsStats[$progKey])) {
                $programsStats[$progKey] = [
                    'name'       => optional($p->program)->program_title ?? '(No Program)',
                    'all'        => 0,
                    'unreg'      => 0,
                    'pending'    => 0,
                    'progress'   => 0,
                    'graded'     => 0,
                ];
            }
            $latest = optional($p->latestStatus)->status;
            $programsStats[$progKey]['all']++;
            if (!$latest || $latest === '' || !in_array($latest, [
                Project::STATUS_REGISTERED, Project::STATUS_ASSIGNED,
                Project::STATUS_CLAIMED, Project::STATUS_PROGRESS_ADDED,
                Project::STATUS_PROGRESS_REVIEWED, Project::STATUS_PROGRESS_REJECTED,
                Project::STATUS_PROGRESS_REJ_REVIEWED,
                Project::STATUS_PROGRESS2_ADDED, Project::STATUS_PROGRESS2_REVIEWED,
                Project::STATUS_PROGRESS2_REJECTED, Project::STATUS_PROGRESS2_REJ_REVIEWED,
                Project::STATUS_FINAL_ADDED, Project::STATUS_GRADED,
            ])) {
                $programsStats[$progKey]['unreg']++;
            } elseif (in_array($latest, [Project::STATUS_REGISTERED, Project::STATUS_ASSIGNED, Project::STATUS_CLAIMED])) {
                $programsStats[$progKey]['pending']++;
            } elseif (in_array($latest, [
                Project::STATUS_PROGRESS_ADDED, Project::STATUS_PROGRESS_REVIEWED, Project::STATUS_PROGRESS_REJECTED,
                Project::STATUS_PROGRESS_REJ_REVIEWED,
                Project::STATUS_PROGRESS2_ADDED, Project::STATUS_PROGRESS2_REVIEWED,
                Project::STATUS_PROGRESS2_REJECTED, Project::STATUS_PROGRESS2_REJ_REVIEWED,
            ])) {
                $programsStats[$progKey]['progress']++;
            } elseif ($latest === Project::STATUS_GRADED) {
                $programsStats[$progKey]['graded']++;
            }
        }
        usort($programsStats, fn($a, $b) => strcmp($a['name'], $b['name']));

        // ── Per-pillar breakdown ─────────────────────────────────────────
        $pillarsStats = [];
        foreach ($myProjects as $p) {
            $projectPillars = $p->pillars->count() > 0 ? $p->pillars : collect([(object)['id' => 0, 'pillar' => '(No Pillar)']]);
            foreach ($projectPillars as $pillar) {
                $pillarKey = $pillar->id ?? 0;
                if (!isset($pillarsStats[$pillarKey])) {
                    $pillarsStats[$pillarKey] = [
                        'name'     => $pillar->pillar ?? '(No Pillar)',
                        'all'      => 0,
                        'unreg'    => 0,
                        'pending'  => 0,
                        'progress' => 0,
                        'graded'   => 0,
                    ];
                }
                $latest = optional($p->latestStatus)->status;
                $pillarsStats[$pillarKey]['all']++;
                if (!$latest || $latest === '' || !in_array($latest, [
                    Project::STATUS_REGISTERED, Project::STATUS_ASSIGNED,
                    Project::STATUS_CLAIMED, Project::STATUS_PROGRESS_ADDED,
                    Project::STATUS_PROGRESS_REVIEWED, Project::STATUS_PROGRESS_REJECTED,
                    Project::STATUS_PROGRESS_REJ_REVIEWED,
                    Project::STATUS_PROGRESS2_ADDED, Project::STATUS_PROGRESS2_REVIEWED,
                    Project::STATUS_PROGRESS2_REJECTED, Project::STATUS_PROGRESS2_REJ_REVIEWED,
                    Project::STATUS_FINAL_ADDED, Project::STATUS_GRADED,
                ])) {
                    $pillarsStats[$pillarKey]['unreg']++;
                } elseif (in_array($latest, [Project::STATUS_REGISTERED, Project::STATUS_ASSIGNED, Project::STATUS_CLAIMED])) {
                    $pillarsStats[$pillarKey]['pending']++;
                } elseif (in_array($latest, [
                    Project::STATUS_PROGRESS_ADDED, Project::STATUS_PROGRESS_REVIEWED, Project::STATUS_PROGRESS_REJECTED,
                    Project::STATUS_PROGRESS_REJ_REVIEWED,
                    Project::STATUS_PROGRESS2_ADDED, Project::STATUS_PROGRESS2_REVIEWED,
                    Project::STATUS_PROGRESS2_REJECTED, Project::STATUS_PROGRESS2_REJ_REVIEWED,
                ])) {
                    $pillarsStats[$pillarKey]['progress']++;
                } elseif ($latest === Project::STATUS_GRADED) {
                    $pillarsStats[$pillarKey]['graded']++;
                }
            }
        }
        usort($pillarsStats, fn($a, $b) => strcmp($a['name'], $b['name']));

        // ── Status counts for donut chart ─────────────────────────────────
        $statusCounts = $myProjects->groupBy(function ($p) {
            return optional($p->latestStatus)->status ?? 'no_status';
        })->map->count()->toArray();

        $statusLabels = Project::statusLabels();
        $statusLabels['no_status'] = 'No Status';

        // ── Active research calls for this LPI's projects ────────────────
        $activeResearchCalls = Program::whereHas('projects', function ($q) use ($user) {
            $q->where('lpi_id', $user->id);
        })->active()
            ->with('cycle')
            ->withCount(['projects as my_projects_count' => function ($q) use ($user) {
                $q->where('lpi_id', $user->id);
            }])
            ->orderBy('program_title')
            ->get();

        // ── Deadline approaching (nearest final_rpt_deadline from active programs) ──
        $nearestDeadline = Program::active()
            ->whereNotNull('final_rpt_deadline')
            ->where('final_rpt_deadline', '>=', now())
            ->orderBy('final_rpt_deadline')
            ->first();

        return view('dashboard.lpi', [
            'activeRole'              => 'LPI',
            'myProjects'              => $myProjects,
            'statuses'                => $statuses,
            'available'               => $available,
            'allProjectsCount'        => $allProjectsCount,
            'unregisteredCount'       => $unregisteredCount,
            'reportUploadPendingCount'=> $reportUploadPendingCount,
            'progressDoneCount'       => $progressDoneCount,
            'gradedCount'             => $gradedCount,
            'grantsAvailed'           => $grantsAvailed,
            'cyclesWorked'            => $cyclesWorked,
            'programsWorked'          => $programsWorked,
            'publicationsTotal'       => $publicationsTotal,
            'studentsTotal'           => $studentsTotal,
            'pillarsWorked'           => $pillarsWorked,
            'programsStats'           => $programsStats,
            'pillarsStats'            => $pillarsStats,
            'lpiAnnouncements'        => $lpiAnnouncements,
            'outcomesByProgram'       => $outcomesByProgram,
            'statusCounts'            => $statusCounts,
            'statusLabels'            => $statusLabels,
            'activeResearchCalls'     => $activeResearchCalls,
            'nearestDeadline'         => $nearestDeadline,
            'announcements'           => $this->globalAnnouncements(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Reviewer dashboard
    // ─────────────────────────────────────────────────────────────────────────
    private function reviewerDashboard($user)
    {
        $userId = $user->id;

        // Projects assigned to this reviewer (from the projects_reviewers pivot)
        $assignedProjects = $user->reviewedProjects()
            ->visibleProgram()
            ->with('latestStatus')
            ->orderBy('title')
            ->get();

        $statuses = Project::statusLabels();

        // ── Stat counts ─────────────────────────────────────────────────────
        $totalAssigned = $assignedProjects->count();

        // Pending proposals: assigned projects where this reviewer has NOT yet
        // accepted (proposalstatus is not 'accepted')
        // NOTE: proposalstatus column removed from DB — treat all as accepted
        $pendingProposals = 0;

        // Pending gradings: projects where this reviewer has accepted (proposalstatus = accepted)
        // but has NOT yet reached Graded status
        $pendingGradings = $assignedProjects->filter(function ($p) use ($userId) {
            $hasGradedStatus = $p->hasStatus(Project::STATUS_GRADED);
            return !$hasGradedStatus;
        })->count();

        // Graded: projects that have reached Graded status
        $graded = $assignedProjects->filter(function ($p) use ($userId) {
            return $p->hasStatus(Project::STATUS_GRADED);
        })->count();

        // ── Reviewer-specific announcements ──────────────────────────────
        $reviewerAnnouncements = Announcement::where(function ($q) {
            $q->whereNull('audience')
              ->orWhere('audience', '')
              ->orWhereRaw('LOWER(audience) IN (?, ?)', ['all', 'reviewer']);
        })->where('is_active', true)
          ->where(function ($q) {
              $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
          })->latest()->take(6)->get();

        // ── Reviewer ratings given by admins per research call ───────────
        $ratings = \App\Models\ReviewerRating::where('reviewer_id', $user->id)
            ->with('program.cycle')
            ->get();

        // Attach computed average per research call
        $ratingRows = $ratings->map(function ($r) {
            $vals = [
                (int) $r->conflict,
                (int) $r->responsiveness,
                (int) $r->comprehensiveness,
                (int) $r->no_reviewers,
                (int) $r->behaviour,
            ];
            $rated = array_filter($vals, fn($v) => $v > 0);
            $avg = count($rated) > 0 ? array_sum($rated) / count($rated) : 0;
            return [
                'program' => $r->program->program_title ?? '—',
                'cycle'   => $r->program->cycle->title ?? '—',
                'conflict' => $vals[0],
                'responsiveness' => $vals[1],
                'comprehensiveness' => $vals[2],
                'no_reviewers' => $vals[3],
                'behaviour' => $vals[4],
                'average' => round($avg, 1),
            ];
        });

        $overallAverage = 0;
        if ($ratingRows->count() > 0) {
            $overallAverage = round($ratingRows->avg('average'), 1);
        }

        // ── Project review breakdown for stat cards ──────────────────────
        $acceptedCount = $assignedProjects->count();
        $reviewedCount = $graded;
        $pendingCount  = $assignedProjects->count() - $acceptedCount;
        $inProgressCount = $acceptedCount - $graded;

        // ── Status counts for donut chart ─────────────────────────────────
        $statusCounts = $assignedProjects->groupBy(function ($p) {
            return optional($p->latestStatus)->status ?? 'no_status';
        })->map->count()->toArray();

        $statusLabels = Project::statusLabels();
        $statusLabels['no_status'] = 'No Status';

        // ── Unique research calls this reviewer works on ──────────────────
        $researchCallsCount = $assignedProjects->filter(function ($p) {
            return $p->program;
        })->unique('program_id')->count();

        // ── Acceptance rate ───────────────────────────────────────────────
        $acceptanceRate = $totalAssigned > 0 ? round(($acceptedCount / $totalAssigned) * 100) : 0;

        return view('dashboard.reviewer', [
            'activeRole'            => 'Reviewer',
            'assignedProjects'      => $assignedProjects,
            'statuses'              => $statuses,
            'totalAssigned'         => $totalAssigned,
            'pendingProposals'      => $pendingProposals,
            'pendingGradings'       => $pendingGradings,
            'gradedCount'           => $graded,
            'acceptedCount'         => $acceptedCount,
            'reviewedCount'         => $reviewedCount,
            'pendingCount'          => $pendingCount,
            'inProgressCount'       => $inProgressCount,
            'statusCounts'          => $statusCounts,
            'statusLabels'          => $statusLabels,
            'researchCallsCount'    => $researchCallsCount,
            'acceptanceRate'        => $acceptanceRate,
            'ratingRows'            => $ratingRows,
            'overallAverage'        => $overallAverage,
            'reviewerAnnouncements' => $reviewerAnnouncements,
            'announcements'         => $this->globalAnnouncements(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Shared helpers
    // ─────────────────────────────────────────────────────────────────────────
    private function globalAnnouncements()
    {
        return Announcement::where(function ($q) {
            $q->whereNull('audience')
              ->orWhere('audience', '');
        })->where('is_active', true)
          ->where(function ($q) {
              $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
          })->latest()->take(5)->get();
    }

    // ─── Role Switcher ─────────────────────────────────────────────────────
    public function switchRole(Request $request)
    {
        $request->validate(['role' => 'required|string']);
        session(['active_role' => $request->role]);
        return redirect()->route('home');
    }

    // ─── Notifications API ──────────────────────────────────────────────────
    public function notifications()
    {
        $user = Auth::user();
        $role = $user->activeRole();

        // Admin doesn't see announcements (they create them for others)
        if ($user->isAdmin()) {
            return response()->json([
                'count'         => 0,
                'announcements' => [],
            ]);
        }

        $announcements = Announcement::where(function ($q) use ($role) {
            $q->whereNull('audience')
              ->orWhere('audience', '')
              ->orWhereRaw('LOWER(audience) IN (?, ?)', ['all', strtolower($role)]);
        })->where('is_active', true)
          ->where(function ($q) {
              $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
          })->latest()->take(10)->get();

        $items = $announcements->map(function ($a) {
            return [
                'title'      => $a->title,
                'message'    => $a->message,
                'type'       => $a->type,
                'url'        => route('announcements.index'),
                'created_at' => $a->created_at ? $a->created_at->format('d M Y') : '',
            ];
        })->toArray();

        return response()->json([
            'count'         => $announcements->count(),
            'announcements' => $items,
        ]);
    }
}
