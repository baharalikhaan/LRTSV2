<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Program;
use App\Models\CycleConfig;
use App\Models\User;
use App\Models\Pillar;
use App\Models\College;
use App\Models\ReviewerRejection;
use App\Services\EventMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        return view('projects.show', $this->projectShowData($id));
    }

    /** Printable (A4) version of the project detail page. */
    public function printable($id)
    {
        return view('projects.printable', $this->projectShowData($id));
    }

    /**
     * Shared payload for the project detail page and its printable version,
     * so both always render the same data.
     */
    private function projectShowData($id): array
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

        return compact(
            'project', 'commitment', 'hasCommitments', 'commitmentsData',
            'finalGradings', 'progressGradings', 'progress2Gradings'
        );
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

        $query = Project::with(['program.grant', 'lpi', 'reviewers']);

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

        $confProjects = $query
            ->orderBy(Program::select('program_title')->whereColumn('programs.id', 'projects.program_id')->limit(1))
            ->orderBy('projects.id')
            ->get();

        // Apply status filter client-side (since status is derived from relationships).
        // "unregistered" is a computed pseudo-status (imported but never claimed);
        // every real workflow status matches the project's LATEST status — the
        // same value the Status pill displays.
        if ($status === 'unregistered') {
            $confProjects = $confProjects->filter(function ($cp) {
                return !$cp->hasStatus(Project::STATUS_REGISTERED) || !$cp->lpi_id;
            });
        } elseif ($status) {
            $confProjects = $confProjects->filter(function ($cp) use ($status) {
                return $cp->currentWorkflowStatus() === $status;
            });
        }

        // Research calls are narrowed to the selected cycle (cascading filter).
        $programsQuery = Program::with('grant')->orderBy('program_title');
        if ($cycleId) {
            $programsQuery->where('cycle_id', $cycleId);
        }
        $programs = $programsQuery->get();
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
        // (Admin registering on behalf of an LPI bypasses the ownership check)
        $user = auth()->user();
        if (!$user->isAdmin() && $confProject->lpi_id !== null && $confProject->lpi_id !== $user->id) {
            return redirect()->route('projects.available')
                ->with('error', 'This project has already been claimed by another LPI.');
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

        // Notify the LPI that their registration is confirmed (automatic).
        $lpi = $project->fresh()->lpi;
        if ($lpi) {
            app(\App\Services\EventMailService::class)->send('project_registered', $lpi, $project, $user);
        }

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

        $projects = $query
            ->orderBy(Program::select('program_title')->whereColumn('programs.id', 'projects.program_id')->limit(1))
            ->orderBy('projects.id')
            ->get();
        $programs = Program::with('grant')->active()->orderBy('program_title')->get();
        $cycleConfigs = CycleConfig::orderBy('year', 'desc')->get();

        return view('projects.my', compact('projects', 'programs', 'programId', 'cycleConfigs', 'cycleId'));
    }
    // ─── ADMIN: Bulk assign reviewers ────────────────────────────────────────

    public function bulkAssign(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $assignments = $request->input('assignments', []);

        /** @var array<int, array<int, int>> $newlyAssigned  project_id => reviewer ids */
        $newlyAssigned = [];
        $skippedRejected = 0;

        DB::transaction(function () use ($assignments, &$newlyAssigned, &$skippedRejected) {
            foreach ($assignments as $assignment) {
                $projectId = $assignment['project_id'] ?? null;
                $reviewerIds = array_filter($assignment['reviewers'] ?? []);

                if (!$projectId || count($reviewerIds) === 0) {
                    continue;
                }

                // Multi-reviewer model: add without removing the existing ones.
                $hadReviewers = DB::table('projects_reviewers')
                    ->where('project_id', $projectId)
                    ->exists();

                foreach ($reviewerIds as $reviewerId) {
                    // A reviewer who rejected this project must not be reassigned.
                    if (Schema::hasTable('reviewer_rejections')
                        && ReviewerRejection::where('project_id', $projectId)->where('user_id', $reviewerId)->exists()) {
                        $skippedRejected++;
                        continue;
                    }

                    $alreadyAssigned = DB::table('projects_reviewers')
                        ->where('project_id', $projectId)
                        ->where('user_id', $reviewerId)
                        ->exists();

                    if ($alreadyAssigned) {
                        continue; // idempotent — already on the team
                    }

                    DB::table('projects_reviewers')->insert([
                        'project_id' => $projectId,
                        'user_id'    => $reviewerId,
                        'role'       => 'Reviewer',
                        'claimed_at' => null, // each reviewer claims for himself
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $newlyAssigned[$projectId][] = (int) $reviewerId;
                }

                // Record the assigned status only for the first assignment —
                // adding a reviewer to an in-review project must not roll its
                // workflow status (Claimed/progress_*) back to Assigned.
                if (!$hadReviewers && !empty($newlyAssigned[$projectId])) {
                    $project = Project::find($projectId);
                    if ($project) {
                        $project->recordStatus(Project::STATUS_ASSIGNED, [
                            'triggered_by' => 'bulk-assign',
                            'reviewer_id'  => $newlyAssigned[$projectId][0],
                        ], auth()->id());
                    }
                }
            }
        });

        // Notify the newly assigned reviewers — the same assignment email the
        // single-project modal sends. Already-assigned reviewers are not
        // notified again. Gated by MAIL_ENABLED inside EventMailService.
        $actor = auth()->user();
        $notified = 0;
        $notifiedProjects = [];

        foreach ($newlyAssigned as $projectId => $reviewerIds) {
            $project = Project::find($projectId);
            if (!$project) {
                continue;
            }

            foreach ($reviewerIds as $reviewerId) {
                $reviewer = User::find($reviewerId);
                if (!$reviewer) {
                    continue;
                }

                app(EventMailService::class)->send('reviewer_assigned', $reviewer, $project, $actor);
                $notified++;
                $notifiedProjects[$projectId] = true;
            }
        }

        $message = $notified > 0
            ? "Reviewers assigned successfully. {$notified} assignment notification" . ($notified === 1 ? '' : 's')
                . ' queued for ' . count($notifiedProjects) . ' project' . (count($notifiedProjects) === 1 ? '' : 's') . '.'
            : 'Reviewers assigned successfully. No new reviewer was added (already assigned or previously rejected).';

        if ($skippedRejected > 0) {
            $message .= " {$skippedRejected} selection" . ($skippedRejected === 1 ? '' : 's')
                . ' skipped because the reviewer previously rejected that project.';
        }

        return redirect()->back()->with('success', $message);
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
        // 4. Progress rejected (report rejected, may need reassignment)
        $query = Project::with('program.grant', 'lpi', 'reviewers', 'latestStatus')
            ->where(function ($q) {
                // Not assigned yet
                $q->whereDoesntHave('reviewers')
                  // Latest status is one of the states that need reviewer
                  // attention (awaiting claim, claimed — another reviewer may
                  // be added alongside —, proposal rejected, or a rejected
                  // progress report).
                  ->orWhere(function ($q2) {
                      $q2->whereRaw("EXISTS (SELECT 1 FROM status_histories sh WHERE sh.project_id = projects.id AND sh.status IN ('Assigned', 'Claimed', 'proposal_rejected', 'progress_rejected') AND sh.id = (SELECT MAX(sh2.id) FROM status_histories sh2 WHERE sh2.project_id = projects.id))");
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

        // Workflow status filter (matches the latest status history entry).
        $status = $request->input('status');
        if ($status) {
            $latestStatus = "(SELECT sh2.status FROM status_histories sh2 WHERE sh2.project_id = projects.id ORDER BY sh2.id DESC LIMIT 1)";

            if ($status === 'unregistered') {
                $query->whereRaw("COALESCE({$latestStatus}, 'imported') = 'imported'");
            } elseif ($status === 'registered') {
                $query->whereRaw("{$latestStatus} = 'registered'");
            } elseif ($status === 'claimed') {
                $query->whereRaw("{$latestStatus} = 'Claimed'");
            }
        }

        $confProjects = $query
            ->orderBy(Program::select('program_title')->whereColumn('programs.id', 'projects.program_id')->limit(1))
            ->orderBy('projects.id')
            ->get();

        // Research calls are narrowed to the selected cycle (cascading filter).
        $programsQuery = Program::with('grant')->orderBy('program_title');
        if ($cycleId) {
            $programsQuery->where('cycle_id', $cycleId);
        }
        $programs = $programsQuery->get();
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
            'confProjects', 'programs', 'programId', 'cycleConfigs', 'cycleId', 'reviewerGroups', 'assignment', 'status'
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

        $query = Project::with('program.grant', 'lpi');

        if ($cycleId) {
            $query->whereHas('program', function ($q) use ($cycleId) {
                $q->where('cycle_id', $cycleId);
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $confProjects = $query
            ->orderBy(Program::select('program_title')->whereColumn('programs.id', 'projects.program_id')->limit(1))
            ->orderBy('projects.id')
            ->get();

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
            ->orderBy('programs.program_title')
            ->orderBy('projects.id')
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

        $adminId = $user->id;

        $query = Project::with([
            'lpi', 'program', 'program.grant', 'reviewers', 'latestStatus',
            'submissions', 'progressGradings', 'progress2Gradings', 'finalGradings',
        ]);

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

        $projects = $query
            ->orderBy(Program::select('program_title')->whereColumn('programs.id', 'projects.program_id')->limit(1))
            ->orderBy('projects.id')
            ->get();

        $pendingReviews = collect();
        foreach ($projects as $project) {
            $currentStatus = $project->currentWorkflowStatus();

            // Personal queue: stages that are ready to grade where THIS admin
            // has not submitted his own set yet. A row disappears from the
            // queue only for him — once he submits — never because another
            // reviewer already graded (consensus is tracked in the status).
            $owed = [];

            if ($project->submissions->contains('type', 'progress')
                && !$project->progressGradings->contains(function ($g) use ($adminId) {
                    return (int) $g->user_id === (int) $adminId
                        && $g->publish !== 'pending'
                        && in_array($g->report_type, ['progress', null], true);
                })) {
                $owed[] = ['progress_report', 'Progress Report 1'];
            }

            if (($project->extended_progress ?? false)
                && $project->submissions->contains('type', 'progress2')
                && !$project->progress2Gradings->contains(function ($g) use ($adminId) {
                    return (int) $g->user_id === (int) $adminId && $g->publish !== 'pending';
                })) {
                $owed[] = ['progress2_report', 'Progress Report 2'];
            }

            if ($project->submissions->contains('type', 'final')
                && !$project->finalGradings->contains(function ($g) use ($adminId) {
                    return (int) $g->user_id === (int) $adminId && $g->publish !== 'pending';
                })) {
                $owed[] = ['final_report', 'Final Report'];
            }

            if (empty($owed)) {
                continue; // he has submitted his set for every ready stage
            }

            $rt = $owed[0][0];
            $reviewTypeLabel = implode(' · ', array_column($owed, 1));

            // Assigned reviewer(s) for display
            $reviewerName = $project->reviewers->pluck('name')->implode(', ') ?: '—';

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
