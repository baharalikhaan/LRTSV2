<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Program;
use App\Models\CycleConfig;
use App\Models\User;
use App\Models\Pillar;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─── Show project details ──────────────────────────────────────────────────

    public function show($id)
    {
        $project = Project::with([
            'program.grant',
            'lpi',
            'pillars',
            'colleges',
        ])->findOrFail($id);

        return view('projects.show', compact('project'));
    }

    // ─── LPI: View available (unregistered) projects ─────────────────────────

    public function availableProjects(Request $request)
    {
        $user = auth()->user();
        $programId = $request->input('program_id');
        $cycleId = $request->input('cycle_id');
        $status = $request->input('status');
        $activeRole = $user->activeRole();

        $query = Project::with('program.grant');

        // ─── Role-based filtering ────────────────────────────────────────
        // Admin: see all projects (no filter)
        // LPI: see projects where they are the LPI, or projects not yet claimed
        // Reviewer: see only projects assigned to them
        if ($activeRole === 'LPI') {
            $query->where(function ($q) use ($user) {
                $q->where('lpi_id', $user->id)
                  ->orWhereNull('lpi_id');
            });
        } elseif ($activeRole === 'Reviewer') {
            $query->visibleProgram()->whereHas('reviewers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($cycleId) {
            $query->whereHas('program', function ($q) use ($cycleId) {
                $q->where('cycle_id', $cycleId);
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $confProjects = $query->orderBy('created_at', 'desc')->get();

        // Apply status filter client-side (since status is derived from relationships)
        if ($status === 'unregistered') {
            $confProjects = $confProjects->filter(function ($cp) {
                return !$cp->hasStatus(Project::STATUS_REGISTERED) || !$cp->lpi_id;
            });
        } elseif ($status === 'registered') {
            $confProjects = $confProjects->filter(function ($cp) use ($user) {
                return $cp->hasStatus(Project::STATUS_REGISTERED) && $cp->lpi_id === $user->id;
            });
        } elseif ($status === 'claimed') {
            $confProjects = $confProjects->filter(function ($cp) use ($user) {
                return $cp->hasStatus(Project::STATUS_REGISTERED) && $cp->lpi_id && $cp->lpi_id !== $user->id;
            });
        }

        $programs = Program::with('grant')->active()->get();
        $cycleConfigs = CycleConfig::orderBy('year', 'desc')->get();

        return view('projects.available', compact('confProjects', 'programs', 'programId', 'cycleConfigs', 'cycleId', 'user', 'status'));
    }

    // ─── LPI: Registration Step 1 (basic info) ───────────────────────────────

    public function register($id)
    {
        $confProject = Project::with('program.grant')->findOrFail($id);

        if ($confProject->hasStatus(Project::STATUS_REGISTERED)) {
            return redirect()->route('projects.available')
                ->with('error', 'This project has already been registered.');
        }

        // Block if the program is inactive
        if (!$confProject->programIsActive()) {
            return redirect()->route('projects.available')
                ->with('error', 'This program is no longer active. Projects under this program cannot be registered.');
        }

        // Check the logged-in user hasn't already registered this project
        $user = auth()->user();
        if ($confProject->lpi_id !== null && $confProject->lpi_id !== $user->id) {
            return redirect()->route('projects.available')
                ->with('error', 'This project has already been claimed by another PI.');
        }

        $pillars = Pillar::orderBy('name')->get();
        $colleges = College::orderBy('name')->get();

        return view('projects.register', compact('confProject', 'pillars', 'colleges'));
    }

    // ─── LPI: Store registration (finalize) ──────────────────────────────────

    public function storeRegistration(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'objectives' => 'nullable|string',
            'methodology' => 'nullable|string',
            'expected_outcomes' => 'nullable|string',
            'requested_budget_qar' => 'nullable|numeric|min:0',
            'pillars' => 'nullable|array',
            'pillars.*' => 'exists:pillars,id',
            'colleges' => 'nullable|array',
            'colleges.*' => 'exists:colleges,id',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if ($project->hasStatus(Project::STATUS_REGISTERED)) {
            return redirect()->route('projects.available')
                ->with('error', 'This project has already been registered.');
        }

        // Block if the program is inactive
        if (!$project->programIsActive()) {
            return redirect()->route('projects.available')
                ->with('error', 'This program is no longer active. Projects under this program cannot be registered.');
        }

        $user = auth()->user();

        DB::transaction(function () use ($validated, $project, $user) {
            // Update the project with registration data
            $project->update([
                'lpi_id' => $user->id,
                'college_decision' => 'pending',
                'requested_budget_qar' => $validated['requested_budget_qar'] ?? $project->requested_budget_qar,
            ]);

            // Record registration status
            $project->recordStatus(Project::STATUS_REGISTERED, null, $user->id);


            // Attach pillars
            if (!empty($validated['pillars'])) {
                $project->pillars()->attach($validated['pillars']);
            }

            // Attach colleges
            if (!empty($validated['colleges'])) {
                $project->colleges()->attach($validated['colleges']);
            }

            // Create default commitments record
            $project->commitments()->create([
                'description' => 'Project commitments pending',
                'is_met' => false,
            ]);
        });

        return redirect()->route('projects.available')
            ->with('success', 'Project registered successfully.');
    }

    // ─── LPI: View my registered projects ────────────────────────────────────

    public function myProjects(Request $request)
    {
        $user = auth()->user();
        $programId = $request->input('program_id');
        $cycleId = $request->input('cycle_id');

        $query = Project::with('program.grant')
            ->where('lpi_id', $user->id);

        if ($cycleId) {
            $query->whereHas('program', function ($q) use ($cycleId) {
                $q->where('cycle_id', $cycleId);
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $projects = $query->orderBy('created_at', 'desc')->get();
        $programs = Program::with('grant')->active()->orderBy('program_title')->get();
        $cycleConfigs = CycleConfig::orderBy('year', 'desc')->get();

        return view('projects.my', compact('projects', 'programs', 'programId', 'cycleConfigs', 'cycleId'));
    }

    // ─── ADMIN: Reviewer assignment page ────────────────────────────────────

    public function assignView($cycleId)
    {
        $cycle = Program::with('grant')->findOrFail($cycleId);

        // Get projects that have NO reviewers assigned yet
        $projects = Project::where('program_id', $cycleId)
            ->whereNotIn('id', function ($q) {
                $q->select('project_id')->from('projects_reviewers');
            })
            ->get();

        // Get available reviewers with their pillars
        $reviewers = User::whereIn('type', ['Reviewer', 'LPI+Reviewer'])
            ->where('is_active', true)
            ->with('pillars')
            ->get()
            ->map(function ($reviewer) {
                $reviewer->pillar_names = $reviewer->pillars->pluck('name')->implode(', ');
                return $reviewer;
            });

        return view('projects.assign-reviewer', compact('cycle', 'projects', 'reviewers'));
    }

    // ─── ADMIN: Bulk assign reviewers ────────────────────────────────────────

    public function bulkAssign(Request $request)
    {
        $assignments = $request->input('assignments', []);

        DB::transaction(function () use ($assignments) {
            foreach ($assignments as $assignment) {
                $projectId = $assignment['project_id'] ?? null;
                $reviewerIds = $assignment['reviewers'] ?? [];

                foreach ($reviewerIds as $reviewerId) {
                    if ($projectId && $reviewerId) {
                        DB::table('projects_reviewers')->updateOrInsert(
                            ['project_id' => $projectId, 'user_id' => $reviewerId],
                            ['created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }

                // Record the assigned status for this project
                if ($projectId) {
                    $project = Project::find($projectId);
                    if ($project && !$project->hasStatus(Project::STATUS_ASSIGNED)) {
                        $project->recordStatus(Project::STATUS_ASSIGNED);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Reviewers assigned successfully.');
    }

    // ─── REVIEWER: View assignments & accept/reject ─────────────────────────

    public function myAssignments()
    {
        $user = auth()->user();

        $assignments = DB::table('projects_reviewers')
            ->join('projects', 'projects.id', '=', 'projects_reviewers.project_id')
            ->join('programs', 'programs.id', '=', 'projects.program_id')
            ->join('users as lpi', 'lpi.id', '=', 'projects.lpi_id')
            ->where('projects_reviewers.user_id', $user->id)
            ->where('programs.is_visible', true)
            ->select(
                'projects_reviewers.id as r_id',
                'projects_reviewers.proposalstatus',
                'projects_reviewers.statusdate',
                'projects.id as project_id',
                'projects.title as project_title',
                'projects.old_project_id',
                'programs.program_title',
                'lpi.name as lpi_name',
                'lpi.email as lpi_email'
            )
            ->get();

        // ─── Stats for reviewer dashboard ─────────────────────────────────
        // Total assigned
        $totalAssigned = $assignments->count();

        // Pending (proposalstatus is null)
        $pending = $assignments->whereNull('proposalstatus')->count();

        // Claimed / accepted
        $claimed = $assignments->where('proposalstatus', 'accepted')->count();

        // Graded
        $projectIds = $assignments->pluck('project_id');
        $gradedCount = \App\Models\Project::whereIn('id', $projectIds)
            ->whereHas('statusHistories', function ($q) {
                $q->where('status', \App\Models\Project::STATUS_GRADED);
            })
            ->count();

        return view('projects.my-assignments', compact(
            'assignments',
            'totalAssigned',
            'pending',
            'claimed',
            'gradedCount'
        ));
    }

    // ─── REVIEWER: Accept proposal page ─────────────────────────────────────

    public function acceptProposal($rId)
    {
        $assignment = DB::table('projects_reviewers')
            ->join('projects', 'projects.id', '=', 'projects_reviewers.project_id')
            ->join('programs', 'programs.id', '=', 'projects.program_id')
            ->join('users', 'users.id', '=', 'projects_reviewers.user_id')
            ->where('projects_reviewers.id', $rId)
            ->where('programs.is_visible', true)
            ->select(
                'projects_reviewers.id',
                'projects_reviewers.project_id',
                'projects_reviewers.proposalstatus',
                'projects.title as project_title',
                'projects.old_project_id',
                'programs.program_title',
                'users.email'
            )
            ->first();

        if (!$assignment) {
            abort(404, 'Assignment not found.');
        }

        return view('projects.accept-proposal', compact('assignment'));
    }

    // ─── REVIEWER: Submit acceptance/rejection ──────────────────────────────

    public function acceptProposalPost(Request $request)
    {
        $validated = $request->validate([
            'r_id' => 'required|exists:projects_reviewers,id',
            'accept' => 'required|in:accepted,rejected',
        ]);

        DB::table('projects_reviewers')
            ->where('id', $validated['r_id'])
            ->update([
                'proposalstatus' => $validated['accept'],
                'statusdate' => now(),
            ]);

        return redirect()->route('projects.my-assignments')
            ->with('success', 'Proposal ' . $validated['accept'] . ' successfully.');
    }
}
