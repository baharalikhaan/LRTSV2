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
            'commitments',
            'outcomes.publication',
            'students.details',
            'researchers',
            'contributions',
            'submissions',
        ])->findOrFail($id);

        // Compute commitments vs outcomes for the view
        $commitment = $project->commitments()->first();
        $hasCommitments = $commitment && (
            $commitment->q1article || $commitment->q2article || $commitment->q3article || $commitment->q4article ||
            $commitment->confArticle || $commitment->books || $commitment->editBooks || $commitment->chapters ||
            $commitment->ip || $commitment->filedPatent || $commitment->openSourceSW || $commitment->startUp ||
            $commitment->ethical || $commitment->master || $commitment->UG || $commitment->Phd || $commitment->crossCollege
        );

        $commitmentsData = $this->computeCommitmentsVsOutcomes($project, $commitment);

        $finalGradings = \App\Models\FinalReportGrading::with('user')
            ->where('project_id', $id)->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();
        $progressGradings = \App\Models\ProgressReportGrading::with(['user', 'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef'])
            ->where('project_id', $id)
            ->where(function ($q) {
                $q->where('report_type', 'progress')->orWhereNull('report_type');
            })
            ->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();
        $progress2Gradings = \App\Models\ProgressReportGrading::with(['user', 'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef'])
            ->where('project_id', $id)->where('report_type', 'progress2')
            ->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();

        return view('projects.show', compact(
            'project', 'commitment', 'hasCommitments', 'commitmentsData',
            'finalGradings', 'progressGradings', 'progress2Gradings'
        ));
    }

    public function reportCard($id)
    {
        $project = Project::with([
            'program.grant',
            'lpi',
            'pillars',
            'colleges',
            'commitments',
            'outcomes.publication',
            'students.details',
            'researchers',
            'contributions',
            'submissions',
        ])->findOrFail($id);

        $commitment = $project->commitments()->first();
        $finalGradings = \App\Models\FinalReportGrading::with('user')
            ->where('project_id', $id)->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();
        $progressGradings = \App\Models\ProgressReportGrading::with(['user', 'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef'])
            ->where('project_id', $id)
            ->where(function ($q) {
                $q->where('report_type', 'progress')->orWhereNull('report_type');
            })
            ->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();
        $progress2Gradings = \App\Models\ProgressReportGrading::with(['user', 'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef'])
            ->where('project_id', $id)->where('report_type', 'progress2')
            ->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();

        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();
        $researchers = $project->researchers()->get();

        return view('projects.report-card', compact(
            'project', 'commitment', 'finalGradings', 'progressGradings', 'progress2Gradings',
            'outcomes', 'students', 'researchers'
        ));
    }

    private function computeCommitmentsVsOutcomes($project, $commitment)
    {
        $outcomes = $project->outcomes;
        $outcomesByType = $outcomes->groupBy('type');
        $emptyCollection = collect();

        $verifiedCount = $outcomes->filter(fn($o) => $o->verifcation_by_reviewer === 'verified')->count();
        $unverifiedCount = $outcomes->filter(fn($o) => $o->verifcation_by_reviewer !== 'verified')->count();

        // Publications
        $pubTypes = [
            'journal_q1' => 'q1article', 'journal_q2' => 'q2article',
            'journal_q3' => 'q3article', 'journal_q4' => 'q4article',
            'conference' => 'confArticle', 'book' => 'books',
            'edited_book' => 'editBooks', 'book_chapter' => 'chapters',
        ];
        $pubLabels = [
            'journal_q1' => 'Q1 Journal', 'journal_q2' => 'Q2 Journal',
            'journal_q3' => 'Q3 Journal', 'journal_q4' => 'Q4 Journal',
            'conference' => 'Conference', 'book' => 'Books',
            'edited_book' => 'Ed. Books', 'book_chapter' => 'Chapters',
        ];
        $pubItems = [];
        foreach ($pubTypes as $type => $field) {
            $collection = $outcomesByType->get($type, $emptyCollection);
            $pubItems[] = [
                'label' => $pubLabels[$type],
                'commit' => $commitment->$field ?? null,
                'verified' => $collection->where('verifcation_by_reviewer', 'verified')->count(),
                'total' => $collection->count(),
            ];
        }

        // IP
        $ipVerified = $outcomesByType->get('ip_disclosure', $emptyCollection)->where('verifcation_by_reviewer', 'verified')->count();
        $ipTotal = $outcomesByType->get('ip_disclosure', $emptyCollection)->count();
        $patentsVerified = $outcomesByType->get('provisional_patent', $emptyCollection)->where('verifcation_by_reviewer', 'verified')->count() + $outcomesByType->get('patent_granted', $emptyCollection)->where('verifcation_by_reviewer', 'verified')->count();
        $patentsTotal = $outcomesByType->get('provisional_patent', $emptyCollection)->count() + $outcomesByType->get('patent_granted', $emptyCollection)->count();
        $ossVerified = $outcomesByType->get('open_source_sw', $emptyCollection)->where('verifcation_by_reviewer', 'verified')->count();
        $ossTotal = $outcomesByType->get('open_source_sw', $emptyCollection)->count();
        $startupVerified = $outcomesByType->get('startup', $emptyCollection)->where('verifcation_by_reviewer', 'verified')->count();
        $startupTotal = $outcomesByType->get('startup', $emptyCollection)->count();

        $ipItems = [
            ['label' => 'IP Disclosure', 'commit' => $commitment->ip ?? null, 'verified' => $ipVerified, 'total' => $ipTotal],
            ['label' => 'Patents', 'commit' => $commitment->filedPatent ?? null, 'verified' => $patentsVerified, 'total' => $patentsTotal],
            ['label' => 'Open Source SW', 'commit' => $commitment->openSourceSW ?? null, 'verified' => $ossVerified, 'total' => $ossTotal],
            ['label' => 'Start-up', 'commit' => $commitment->startUp ?? null, 'verified' => $startupVerified, 'total' => $startupTotal],
        ];

        // Students
        $studentItems = [
            ['label' => 'Masters', 'commit' => $commitment->master ?? null, 'count' => $project->students->filter(fn($s) => strtolower($s->type) === 'masters')->count()],
            ['label' => 'Undergrad', 'commit' => $commitment->UG ?? null, 'count' => $project->students->filter(fn($s) => strtolower($s->type) === 'ug')->count()],
            ['label' => 'PhD', 'commit' => $commitment->Phd ?? null, 'count' => $project->students->filter(fn($s) => strtolower($s->type) === 'phd')->count()],
        ];

        return compact('verifiedCount', 'unverifiedCount', 'pubItems', 'ipItems', 'studentItems');
    }

    // ─── LPI: View available (unregistered) projects ─────────────────────────

    public function availableProjects(Request $request)
    {
        $user = auth()->user();
        $programId = $request->input('program_id');
        $cycleId = $request->input('cycle_id');
        $status = $request->input('status');
        $activeRole = $user->activeRole();

        // Persist filter state so returning to the list keeps the filters
        // (e.g. after visiting a project detail page).
        if ($request->hasAny(['program_id', 'cycle_id', 'status'])) {
            session(['projects_filters' => [
                'program_id' => $programId,
                'cycle_id'   => $cycleId,
                'status'     => $status,
            ]]);
        } else {
            $filters = session('projects_filters', []);
            $programId = $programId ?? ($filters['program_id'] ?? null);
            $cycleId = $cycleId ?? ($filters['cycle_id'] ?? null);
            $status = $status ?? ($filters['status'] ?? null);
        }

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

        $programs = Program::with('grant')->orderBy('program_title')->get();
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
        $reviewers = User::with('pillars')
            ->whereIn('type', ['Reviewer', 'LPI+Reviewer'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($reviewer) {
                $reviewer->pillar_names = $reviewer->getRelation('pillars')->pluck('pillar')->filter()->implode(', ');
                return $reviewer;
            });

        return view('projects.assign-reviewer', compact('cycle', 'projects', 'reviewers'));
    }

    // ─── ADMIN: Bulk assign reviewers ────────────────────────────────────────

    public function bulkAssign(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $assignments = $request->input('assignments', []);

        DB::transaction(function () use ($assignments) {
            foreach ($assignments as $assignment) {
                $projectId = $assignment['project_id'] ?? null;
                $reviewerIds = array_filter($assignment['reviewers'] ?? []);

                foreach ($reviewerIds as $reviewerId) {
                    if ($projectId && $reviewerId) {
                        DB::table('projects_reviewers')->updateOrInsert(
                            ['project_id' => $projectId, 'user_id' => $reviewerId],
                            ['created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }

                // Record the assigned status for this project only when at least
                // one reviewer was actually selected.
                if ($projectId && count($reviewerIds) > 0) {
                    $project = Project::find($projectId);
                    if ($project) {
                        $project->recordStatus(Project::STATUS_ASSIGNED);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Reviewers assigned successfully.');
    }

    // ─── ADMIN: Reviewer Assignment page (bulk assign, projects-page style) ──

    public function reviewerAssignment(Request $request)
    {
        $cycleId = $request->input('cycle_id');
        $programId = $request->input('program_id');
        $assignment = $request->input('assignment');

        // Show projects that need reviewer attention:
        // 1. Not assigned yet (no reviewer)
        // 2. Assigned (reviewer assigned, awaiting claim)
        // 3. Proposal rejected (reviewer rejected, needs reassignment)
        $query = Project::with('program.grant', 'reviewers', 'latestStatus')
            ->where(function ($q) {
                // Not assigned yet
                $q->whereDoesntHave('reviewers')
                  // Assigned but not yet claimed (latest status is 'Assigned')
                  ->orWhere(function ($q2) {
                      $q2->whereRaw("EXISTS (SELECT 1 FROM status_histories sh WHERE sh.project_id = projects.id AND sh.status = 'Assigned' AND sh.id = (SELECT MAX(sh2.id) FROM status_histories sh2 WHERE sh2.project_id = projects.id))");
                  })
                  // Proposal rejected (latest status is 'proposal_rejected')
                  ->orWhere(function ($q2) {
                      $q2->whereRaw("EXISTS (SELECT 1 FROM status_histories sh WHERE sh.project_id = projects.id AND sh.status = 'proposal_rejected' AND sh.id = (SELECT MAX(sh2.id) FROM status_histories sh2 WHERE sh2.project_id = projects.id))");
                  });
            });

        if ($cycleId) {
            $query->whereHas('program', function ($q) use ($cycleId) {
                $q->where('cycle_id', $cycleId);
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        // Filter by assignment status
        if ($assignment === 'assigned') {
            $query->whereHas('reviewers');
        } elseif ($assignment === 'unassigned') {
            $query->whereDoesntHave('reviewers');
        }

        $confProjects = $query->orderBy('created_at', 'desc')->get();

        $programs = Program::with('grant')->orderBy('program_title')->get();
        $cycleConfigs = CycleConfig::orderBy('year', 'desc')->get();

        // Available reviewers for the dropdowns, grouped by their research pillar
        $reviewers = User::with('pillars')
            ->whereIn('type', ['Reviewer', 'LPI+Reviewer'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $reviewerGroups = [];
        foreach ($reviewers as $reviewer) {
            $pillarNames = $reviewer->getRelation('pillars')->pluck('pillar')->filter()->values()->all();
            if (empty($pillarNames)) {
                $reviewerGroups['Unassigned'][] = $reviewer;
            } else {
                foreach ($pillarNames as $pillarName) {
                    $reviewerGroups[$pillarName][] = $reviewer;
                }
            }
        }
        ksort($reviewerGroups);

        return view('projects.reviewer-assignment', compact(
            'confProjects', 'programs', 'programId', 'cycleConfigs', 'cycleId', 'reviewerGroups', 'assignment'
        ));
    }

    /**
     * Parse the `users.pillars` string column into a list of pillar names.
     */
    private function reviewerPillarNames(?string $pillars): array
    {
        if (!$pillars || trim($pillars) === '') {
            return [];
        }

        $parts = preg_split('/[;,|\n\r]+/', $pillars);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    // ─── ADMIN: Extend Progress Report page ──────────────────────────────────

    public function extendProgressIndex(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $cycleId = $request->input('cycle_id');
        $programId = $request->input('program_id');
        $status = $request->input('status');

        $query = Project::with('program.grant');

        if ($cycleId) {
            $query->whereHas('program', function ($q) use ($cycleId) {
                $q->where('cycle_id', $cycleId);
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $confProjects = $query->orderBy('created_at', 'desc')->get();

        // Apply status filter client-side (status is derived from relationships)
        if ($status === 'unregistered') {
            $confProjects = $confProjects->filter(function ($cp) {
                return !$cp->hasStatus(Project::STATUS_REGISTERED) || !$cp->lpi_id;
            });
        } elseif ($status === 'registered') {
            $confProjects = $confProjects->filter(function ($cp) {
                return $cp->hasStatus(Project::STATUS_REGISTERED);
            });
        } elseif ($status === 'claimed') {
            $confProjects = $confProjects->filter(function ($cp) {
                return $cp->hasStatus(Project::STATUS_REGISTERED) && $cp->lpi_id;
            });
        }

        $programs = Program::with('grant')->active()->get();
        $cycleConfigs = CycleConfig::orderBy('year', 'desc')->get();

        return view('projects.extend-progress', compact(
            'confProjects', 'programs', 'programId', 'cycleConfigs', 'cycleId', 'status'
        ));
    }

    // ─── ADMIN: Toggle the extended progress report flag ───────────────────

    public function toggleExtendedProgress(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $project = Project::findOrFail($request->input('project_id'));
        $project->update(['extended_progress' => !$project->extended_progress]);

        $state = $project->extended_progress ? 'enabled' : 'revoked';

        return redirect()->back()
            ->with('success', "Progress report extension {$state} for project {$project->old_project_id}.");
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

        // All assignments are treated as pending (no proposalstatus tracking)
        $pending = $totalAssigned;

        // Claimed / accepted — all treated as claimed since no rejection tracking
        $claimed = 0;

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

    // NOTE: The legacy standalone accept-proposal page/methods were removed.
    // Proposal accept/reject is now handled exclusively through the workflow
    // modal at /workflow/submit-decision (WorkflowController::submitProposalDecision).

    // ─── Pending Reviews (Admin on behalf of reviewer) ─────────────────────────
    public function pendingReviews(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Access denied. Admin only.');
        }

        $cycleId   = $request->input('cycle_id');
        $programId = $request->input('program_id');
        $grantId   = $request->input('grant_id');
        $status    = $request->input('status');

        $query = Project::with(['lpi', 'program', 'program.grant', 'reviewers', 'latestStatus'])
            ->whereHas('reviewers');

        if ($cycleId) {
            $query->whereHas('program', function ($q) use ($cycleId) {
                $q->where('cycle_id', $cycleId);
            });
        }
        if ($programId) {
            $query->where('program_id', $programId);
        }
        if ($grantId) {
            $query->whereHas('program', function ($q) use ($grantId) {
                $q->where('grant_id', $grantId);
            });
        }

        $projects = $query->get();

        $pendingReviews = collect();
        foreach ($projects as $project) {
            $currentStatus = $project->currentWorkflowStatus();

            // Skip graded projects
            if ($currentStatus === Project::STATUS_GRADED) {
                continue;
            }

            // Determine review type based on current status
            $rt = null;
            if ($currentStatus === Project::STATUS_PROGRESS_ADDED) {
                $rt = 'progress_report';
            } elseif ($currentStatus === Project::STATUS_PROGRESS2_ADDED) {
                $rt = 'progress2_report';
            } elseif ($currentStatus === Project::STATUS_FINAL_ADDED) {
                $rt = 'final_report';
            } elseif ($currentStatus === Project::STATUS_CLAIMED) {
                $rt = 'pending_upload';
            } elseif ($currentStatus === Project::STATUS_ASSIGNED) {
                $rt = 'awaiting_claim';
            } else {
                $rt = strtolower(str_replace(' ', '_', $currentStatus ?? 'unknown'));
            }

            $reviewTypeLabel = match($rt) {
                'progress_report'   => 'Progress Report 1',
                'progress2_report'  => 'Progress Report 2',
                'final_report'      => 'Final Report',
                'pending_upload'    => 'Pending Progress Upload',
                'awaiting_claim'    => 'Awaiting Reviewer Claim',
                default             => ucfirst(str_replace('_', ' ', $currentStatus ?? 'unknown')),
            };

            // Reviewer info from pivot
            $reviewerName  = $project->reviewers->first()->name ?? '—';

            // Filter by status
            if ($status && $currentStatus !== $status) {
                continue;
            }

            $pendingReviews->push((object) [
                'project'        => $project,
                'currentStatus'  => $currentStatus,
                'reviewType'     => $rt,
                'reviewTypeLabel'=> $reviewTypeLabel,
                'reviewer'       => $reviewerName,
            ]);
        }

        $cycleConfigs = \App\Models\CycleConfig::orderBy('year', 'desc')->get();

        // Cascade grants: filter by cycle if selected
        if ($cycleId) {
            $grantIds = \App\Models\Program::where('cycle_id', $cycleId)
                ->whereNotNull('grant_id')->pluck('grant_id')->unique();
            $grants = \App\Models\Grant::whereIn('id', $grantIds)->orderBy('grant_code')->get();
        } else {
            $grants = \App\Models\Grant::orderBy('grant_code')->get();
        }

        // Cascade programs: filter by cycle and/or grant
        $progQuery = \App\Models\Program::orderBy('program_title');
        if ($cycleId) {
            $progQuery->where('cycle_id', $cycleId);
        }
        if ($grantId) {
            $progQuery->where('grant_id', $grantId);
        }
        $programs = $progQuery->get();

        $statuses = $pendingReviews->pluck('currentStatus')->unique()->sort()->values()->toArray();

        return view('projects.pending-reviews', compact(
            'pendingReviews', 'cycleConfigs', 'programs', 'grants', 'statuses',
            'cycleId', 'programId', 'grantId', 'status'
        ));
    }

    public function pendingReviewFilterData(Request $request)
    {
        $cycleId = $request->input('cycle_id');
        $grantId = $request->input('grant_id');

        $grants = collect();
        $programs = collect();

        if ($cycleId) {
            $grantIds = \App\Models\Program::where('cycle_id', $cycleId)
                ->whereNotNull('grant_id')
                ->pluck('grant_id')
                ->unique();
            $grants = \App\Models\Grant::whereIn('id', $grantIds)->orderBy('grant_code')->get();

            $progQuery = \App\Models\Program::where('cycle_id', $cycleId)->orderBy('program_title');
            if ($grantId) {
                $progQuery->where('grant_id', $grantId);
            }
            $programs = $progQuery->get();
        } elseif ($grantId) {
            $programs = \App\Models\Program::where('grant_id', $grantId)->orderBy('program_title')->get();
        } else {
            $programs = \App\Models\Program::orderBy('program_title')->get();
        }

        return response()->json([
            'grants'   => $grants->map(fn($g) => ['id' => $g->id, 'label' => $g->grant_code]),
            'programs' => $programs->map(fn($p) => ['id' => $p->id, 'label' => $p->program_title]),
        ]);
    }
}
