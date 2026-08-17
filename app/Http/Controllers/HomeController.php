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
        // ── Project lifecycle counts (by LATEST status from status_histories) ──
        // Get the latest status for each project using a subquery
        $latestStatuses = \DB::table('status_histories as sh')
            ->select('sh.project_id', 'sh.status')
            ->whereRaw('sh.id = (SELECT MAX(sh2.id) FROM status_histories sh2 WHERE sh2.project_id = sh.project_id)')
            ->get()
            ->pluck('status');

        // Count projects per ACTUAL status present in the data (not just known labels)
        $statusCounts = $latestStatuses->countBy()->toArray();

        // Build a label map: known labels + any other status codes mapped to themselves
        $statusLabels = Project::statusLabels();
        foreach (array_keys($statusCounts) as $code) {
            if (!isset($statusLabels[$code])) {
                $statusLabels[$code] = $code;
            }
        }

        // Also count projects with no status history at all
        $totalProjects = Project::count();
        $projectsWithStatus = $latestStatuses->count();
        $statusCounts['no_status'] = $totalProjects - $projectsWithStatus;

        // ── Active programs & cycles ───────────────────────────────────────
        // Use programs' own status (active = final deadline not passed)
        $activePrograms = Program::withCount('projects as project_count')
            ->active()  // uses Program::scopeActive() which checks deadlines
            ->get();

        // Show all CycleConfigs (cycles are the configuration templates)
        $cycles = \App\Models\CycleConfig::all();

        // ── Key aggregates ─────────────────────────────────────────────────
        $totalPrograms      = Program::count();
        $activeProgramsCount = Program::active()->count();
        $totalProjects      = Project::count();
        $completedProjects  = $latestStatuses->filter(function ($s) {
            return $s === Project::STATUS_GRADED;
        })->count();

        return view('dashboard.admin', [
            'activeRole'          => 'Admin',
            'statusCounts'        => $statusCounts,
            'statusLabels'        => $statusLabels,
            'activePrograms'      => $activePrograms,
            'cycles'              => $cycles,
            'totalPrograms'       => $totalPrograms,
            'activeProgramsCount' => $activeProgramsCount,
            'totalProjects'       => $totalProjects,
            'completedProjects'   => $completedProjects,
            'announcements'       => $this->globalAnnouncements(),
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
        $publicationsTotal = $myProjects->sum(function ($p) {
            return $p->publications ? $p->publications->count() : 0;
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
              ->orWhere('audience', 'all')
              ->orWhere('audience', 'LPI');
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
            ->withPivot('proposalstatus')
            ->orderBy('title')
            ->get();

        $statuses = Project::statusLabels();

        // ── Stat counts ─────────────────────────────────────────────────────
        $totalAssigned = $assignedProjects->count();

        // Pending proposals: assigned projects where this reviewer has NOT yet
        // accepted (proposalstatus is not 'accepted')
        $pendingProposals = $assignedProjects->filter(function ($p) use ($userId) {
            return $p->pivot->proposalstatus !== 'accepted';
        })->count();

        // Pending gradings: projects where this reviewer has accepted (proposalstatus = accepted)
        // but has NOT yet reached Graded status
        $pendingGradings = $assignedProjects->filter(function ($p) use ($userId) {
            $accepted = $p->pivot->proposalstatus === 'accepted';
            $hasGradedStatus = $p->hasStatus(Project::STATUS_GRADED);
            return $accepted && !$hasGradedStatus;
        })->count();

        // Graded: projects that have reached Graded status
        $graded = $assignedProjects->filter(function ($p) use ($userId) {
            return $p->hasStatus(Project::STATUS_GRADED);
        })->count();

        // ── Reviewer-specific announcements ──────────────────────────────
        $reviewerAnnouncements = Announcement::where(function ($q) {
            $q->whereNull('audience')
              ->orWhere('audience', '')
              ->orWhere('audience', 'all')
              ->orWhere('audience', 'Reviewer');
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
        $acceptedCount = $assignedProjects->filter(function ($p) {
            return $p->pivot->proposalstatus === 'accepted';
        })->count();
        $reviewedCount = $graded;
        $pendingCount  = $assignedProjects->count() - $acceptedCount;
        $inProgressCount = $acceptedCount - $graded;

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
        $count = 0;

        if ($role === 'Reviewer') {
            $count = $user->reviewedProjects()
                ->visibleProgram()
                ->whereHas('statusHistories', function ($q) {
                    $q->whereIn('status', ['Assigned', 'Claimed']);
                })
                ->count();
        } elseif ($role === 'LPI') {
            $count = Project::where('lpi_id', $user->id)
                ->whereHas('statusHistories', function ($q) {
                    $q->whereIn('status', ['Registered', 'Progressed', 'Accepted']);
                })
                ->count();
        } elseif ($role === 'Admin') {
            $count = Project::whereHas('statusHistories', function ($q) {
                $q->where('status', 'Registered');
            })->count();
        }

        return response()->json(['count' => $count]);
    }
}
