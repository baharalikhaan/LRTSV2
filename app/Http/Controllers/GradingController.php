<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProgressReportGrading;
use App\Models\FinalReportGrading;
use App\Models\Commitment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AiSetting;

class GradingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isReviewer() && !auth()->user()->isAdmin()) {
                abort(403, 'Access denied. Reviewers or Admins only.');
            }
            return $next($request);
        });
    }

    /**
     * Show all projects that have been graded or need grading.
     */
    public function gradedProjects()
    {
        $user = Auth::user();

        $cycles = \App\Models\CycleConfig::orderBy('title')->get();
        $cycleId = request('cycle_id');

        $query = $user->reviewedProjects()->visibleProgram()->with('lpi', 'program', 'program.grant');

        if ($cycleId) {
            $query->whereHas('program', function ($q) use ($cycleId) {
                $q->where('cycle_id', $cycleId);
            });
        }

        $gradedProjects = $query
            ->orderBy(\App\Models\Program::select('program_title')->whereColumn('programs.id', 'projects.program_id')->limit(1))
            ->orderBy('projects.id')
            ->paginate(15);

        return view('grading.gradedProjects', compact('gradedProjects', 'cycles', 'cycleId'));
    }

    /**
     * Show the grading form for a specific project.
     * Deprecated alias Ã¢â‚¬â€ the unified grading page is at /projects/{id}/grading.
     */
    public function gradeProject($id)
    {
        return redirect()->route('projects.grading', $id);
    }

    /**
     * Show the unified full-page grading view (merged progress + final grading).
     * Replaces the old separate progressgrading and finalgrading routes.
     */
    public function grading($id)
    {
        $user = Auth::user();

        $project = Project::with([
            'lpi',
            'program',
            'program.grant',
            'commitments',
            'contributions',
            'outcomes',
            'outcomes.publication',
            'students',
            'students.details',
            'researchers',
        ])->findOrFail($id);

        // Gate: only assigned reviewers or admins may grade this project.
        if (!$user->isAdmin() && !$this->isAssignedReviewer($project, $user)) {
            abort(403, 'You are not assigned to review this project.');
        }

        // Gate: a reviewer must claim for HIMSELF first Ã¢â‚¬â€ the menu enforces
        // this, and direct URL visits must not bypass the claim step.
        if (!$user->isAdmin() && !$this->hasOwnClaim($project, $user)) {
            abort(403, 'Accept the proposal (claim the project) before grading.');
        }

        // Each user works on HIS OWN grading set: a reviewer (or the admin)
        // sees his own draft (editable) or his own submitted grades (read-only),
        // never another reviewer's scores. A reviewer assigned later therefore
        // starts from a blank form while the earlier reviewer's set stays stored.
        $finalGrading = FinalReportGrading::where('project_id', $id)
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();
        $progressGrading = ProgressReportGrading::where('project_id', $id)
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('report_type', 'progress')->orWhereNull('report_type');
            })
            ->orderBy('id', 'desc')
            ->first();
        $progress2Grading = $project->extended_progress
            ? ProgressReportGrading::where('project_id', $id)
                ->where('user_id', $user->id)
                ->where('report_type', 'progress2')
                ->orderBy('id', 'desc')
                ->first()
            : null;

        $commitments = $project->commitments()->first();
        $contributions = $project->contributions()->get();
        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();
        $researchers = $project->researchers()->get();

        // Auto-grade only counts outcomes that have actually been verified (by
        // the CrossRef check or by the reviewer). Unverified rows are excluded
        // so a batch of unverified entries cannot inflate the section scores —
        // the reviewer verifies what should count via the Verify toggles.
        $verifiedOutcomes = $outcomes->filter(fn ($o) => ($o->verifcation_by_system ?? '') === 'verified'
            || ($o->verifcation_by_reviewer ?? '') === 'verified');

        $submissions = $project->submissions()
            ->orderBy('created_at', 'desc')
            ->get();

        // LPI submission gates Ã¢â‚¬â€ check file existence instead of status
        // (buttons removed, files uploaded directly)
        $proposalExists = $this->proposalFileExists($project);
        $progressSubmitted = $submissions->where('type', 'progress')->count() > 0;
        $progress2Submitted = $project->extended_progress ? $submissions->where('type', 'progress2')->count() > 0 : false;
        $finalSubmitted    = $submissions->where('type', 'final')->count() > 0;
        $readinessSubmitted = $submissions->where('type', 'readiness')->count() > 0;

        // Ethical approval file exists
        $ethicalExists = file_exists(storage_path('app/' . $project->getStorageFilename('ethical')));

        // Whether progress was rejected (show rejection info)
        $progressRejected = $project->hasStatus(Project::STATUS_PROGRESS_REJECTED);

        // All submission versions (for reviewer to compare after rejection)
        $progressVersions = $project->submissions()
            ->where('type', 'progress')
            ->orderBy('version', 'asc')
            ->get();

        $progress2Versions = $project->extended_progress
            ? $project->submissions()->where('type', 'progress2')->orderBy('version', 'asc')->get()
            : collect();

        $typeMappings = [
            'prototype'      => 'Prototype',
            'patent'         => 'Patent',
            'open_source'    => 'Open Source Software',
            'book'            => 'Book',
            'book_chapter'  => 'Book Chapter',
            'article'        => 'Article',
            'conference'     => 'Conference Paper',
            'masters'        => 'Masters Student',
            'phd'            => 'PhD Student',
            'undergrad'      => 'Undergraduate Student',
        ];

        // Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬ Auto-grade calculations (scores from database) Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
        $scoreMap = \App\Models\Score::getMap();

        $achievementTypes = [
            'ip_disclosure', 'provisional_patent', 'patent_granted',
            'open_source', 'open_source_sw', 'startup', 'prototype', 'cross_college',
        ];

        $studentTypes = ['masters', 'UG', 'PhD'];

        // Ã¢â€â‚¬Ã¢â€â‚¬ Section A: Achievements against objectives Ã¢â€â‚¬Ã¢â€â‚¬
        $expectedSumA = 0;
        if ($commitments) {
            $expectedSumA = ($commitments->ip ?? 0) * ($scoreMap['ip_disclosure'] ?? 0)
                + ($commitments->filedPatent ?? 0) * ($scoreMap['provisional_patent'] ?? 0)
                + ($commitments->openSourceSW ?? 0) * ($scoreMap['open_source_sw'] ?? 0)
                + ($commitments->startUp ?? 0) * ($scoreMap['startup'] ?? 0);
        }
        $actualSumA = $verifiedOutcomes->filter(fn($o) => in_array($o->type, $achievementTypes))->reduce(function ($carry, $o) use ($scoreMap) {
            return $carry + ($scoreMap[$o->type] ?? 0);
        }, 0);
        $autoGradeA = $expectedSumA > 0 ? min(round(($actualSumA / $expectedSumA) * 5, 2), 5) : 0;

        // Ã¢â€â‚¬Ã¢â€â‚¬ Section B: Publications & IP Ã¢â€â‚¬Ã¢â€â‚¬
        $pubTypes = ['journal_q1','journal_q2','journal_q3','journal_q4','conference','book','edited_book','book_chapter'];
        $expectedSumB = 0;
        if ($commitments) {
            $expectedSumB = ($commitments->q1article ?? 0) * ($scoreMap['journal_q1'] ?? 0)
                + ($commitments->q2article ?? 0) * ($scoreMap['journal_q2'] ?? 0)
                + ($commitments->q3article ?? 0) * ($scoreMap['journal_q3'] ?? 0)
                + ($commitments->q4article ?? 0) * ($scoreMap['journal_q4'] ?? 0)
                + ($commitments->confArticle ?? 0) * ($scoreMap['conference'] ?? 0)
                + ($commitments->books ?? 0) * ($scoreMap['book'] ?? 0)
                + ($commitments->editBooks ?? 0) * ($scoreMap['edited_book'] ?? 0)
                + ($commitments->chapters ?? 0) * ($scoreMap['book_chapter'] ?? 0);
        }
        $actualSumB = $verifiedOutcomes->filter(fn($o) => in_array($o->type, $pubTypes))->reduce(function ($carry, $o) use ($scoreMap) {
            return $carry + ($scoreMap[$o->type] ?? 0);
        }, 0);
        $autoGradeB = $expectedSumB > 0 ? min(round(($actualSumB / $expectedSumB) * 5, 2), 5) : 0;

        // Ã¢â€â‚¬Ã¢â€â‚¬ Section C: Student & Researcher Involvement Ã¢â€â‚¬Ã¢â€â‚¬
        $expectedSumC = 0;
        if ($commitments) {
            $expectedSumC = ($commitments->master ?? 0) * ($scoreMap['masters'] ?? 0)
                + ($commitments->UG ?? 0) * ($scoreMap['ug'] ?? 0)
                + ($commitments->Phd ?? 0) * ($scoreMap['phd'] ?? 0);
        }
        $actualSumC = $students->reduce(function ($carry, $s) use ($scoreMap) {
            $key = strtolower($s->type);
            return $carry + ($scoreMap[$key] ?? 0);
        }, 0) + $researchers->reduce(function ($carry, $r) use ($scoreMap) {
            return $carry + ($scoreMap['researcher'] ?? 0);
        }, 0);
        $autoGradeC = $expectedSumC > 0 ? min(round(($actualSumC / $expectedSumC) * 5, 2), 5) : 0;

        // Grade D: no auto-calculation
        $autoGradeD = null;

        // Auto-grade visibility setting
        $autoGradeVisibility = AiSetting::get('auto_grade_visibility', '1');
        $isAdmin = $user->isAdmin();

        // Deadline info Ã¢â‚¬â€ show/hide grading forms based on deadlines
        $program = $project->program;
        $progressDeadline = $program ? ($program->prog_rpt_deadline ?? null) : null;
        $progress2Deadline = $program ? $program->prog_rpt2_deadline : null;
        $finalDeadline = $program ? ($program->final_rpt_deadline ?? null) : null;
        $now = now();
        $progressDeadlinePassed = $progressDeadline ? $now->greaterThan($progressDeadline) : true;
        $progress2DeadlinePassed = $project->extended_progress ? ($progress2Deadline ? $now->greaterThan($progress2Deadline) : true) : false;
        $finalDeadlinePassed = $finalDeadline ? $now->greaterThan($finalDeadline) : true;

        return view('grading.grading-page', compact(
            'project',
            'finalGrading',
            'progressGrading',
            'progress2Grading',
            'commitments',
            'contributions',
            'outcomes',
            'students',
            'researchers',
            'submissions',
            'typeMappings',
            'proposalExists',
            'ethicalExists',
            'progressSubmitted',
            'progress2Submitted',
            'finalSubmitted',
            'readinessSubmitted',
            'progressRejected',
            'progressVersions',
            'progress2Versions',
            'autoGradeA',
            'autoGradeB',
            'autoGradeC',
            'autoGradeD',
            'expectedSumA',
            'scoreMap',
            'progressDeadline',
            'progress2Deadline',
            'finalDeadline',
            'progressDeadlinePassed',
            'progress2DeadlinePassed',
            'finalDeadlinePassed',
            'autoGradeVisibility',
            'isAdmin',
        ));
    }

    /**
     * Keep old method names as aliases for backward compatibility.
     */
    public function gradingPage($id)
    {
        return $this->grading($id);
    }

    public function finalGradingPage($id)
    {
        return $this->grading($id);
    }

    /**
     * Update a project's commitments from the grading page (admin only).
     * Reviewers can view commitments but never edit them.
     */
    public function updateCommitments(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403);
        }

        $project = Project::findOrFail($id);

        $numeric = ['nullable', 'integer', 'min:0'];
        $request->validate([
            'q1article'    => $numeric,
            'q2article'    => $numeric,
            'q3article'    => $numeric,
            'q4article'    => $numeric,
            'confArticle'  => $numeric,
            'books'        => $numeric,
            'editBooks'    => $numeric,
            'chapters'     => $numeric,
            'ip'           => $numeric,
            'filedPatent'  => $numeric,
            'openSourceSW' => $numeric,
            'master'       => $numeric,
            'UG'           => $numeric,
            'Phd'          => $numeric,
            'startUp'      => 'nullable|in:0,1',
            'ethical'      => 'nullable|in:0,1',
            'crossCollege' => 'nullable|in:0,1',
        ]);

        Commitment::updateOrCreate(
            ['project_id' => $project->id],
            [
                'q1article'    => (int) ($request->input('q1article') ?? 0),
                'q2article'    => (int) ($request->input('q2article') ?? 0),
                'q3article'    => (int) ($request->input('q3article') ?? 0),
                'q4article'    => (int) ($request->input('q4article') ?? 0),
                'confArticle'  => (int) ($request->input('confArticle') ?? 0),
                'books'        => (int) ($request->input('books') ?? 0),
                'editBooks'    => (int) ($request->input('editBooks') ?? 0),
                'chapters'     => (int) ($request->input('chapters') ?? 0),
                'ip'           => (int) ($request->input('ip') ?? 0),
                'filedPatent'  => (int) ($request->input('filedPatent') ?? 0),
                'openSourceSW' => (int) ($request->input('openSourceSW') ?? 0),
                'startUp'      => $request->boolean('startUp') ? 1 : 0,
                'ethical'      => $request->boolean('ethical') ? 1 : 0,
                'master'       => (int) ($request->input('master') ?? 0),
                'UG'           => (int) ($request->input('UG') ?? 0),
                'Phd'          => (int) ($request->input('Phd') ?? 0),
                'crossCollege' => $request->boolean('crossCollege') ? 1 : 0,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Commitments updated successfully.',
        ]);
    }

    /**
     * Save the progress report grade with section-level ratings.
     */
    public function saveProgressGrade(Request $request, $id)
    {
        $saveAction = $request->input('save_action', 'submit');

        $rules = [
            'achievementsRating' => 'required|integer|between:1,5',
            'publicationsRating' => 'required|integer|between:1,5',
            'studentsRating'     => 'required|integer|between:1,5',
            'budgetRating'       => 'required|integer|between:1,5',
            'achievementsComments' => 'nullable|string|max:1200',
            'publicationsComments' => 'nullable|string|max:1200',
            'studentsComments'     => 'nullable|string|max:1200',
            'budgetComments'       => 'nullable|string|max:1200',
            'ethical'            => 'nullable|in:0,1',
            'analysis'           => 'nullable|string|max:255',
            'comments'           => 'nullable|string|max:255',
            'recommendation'     => 'nullable|string|max:255',
            'report_type'        => 'nullable|in:progress,progress2',
            'rejection_reason'   => 'nullable|string|max:2000',
            'rejection_type'     => 'nullable|in:report,ethical,other',
        ];

        // For "draft", publish is optional; for "submit", it's required
        if ($saveAction === 'draft') {
            $rules['publish'] = 'nullable|in:accepted,rejected,reserved,pending';
            $rules['rejection_reason'] = 'nullable|string|max:2000';
            $rules['rejection_type'] = 'nullable|in:report,ethical,other';
        } else {
            $rules['publish'] = 'required|in:accepted,rejected,reserved,pending';
            $rules['rejection_reason'] = 'required_if:publish,rejected|nullable|string|max:2000';
            $rules['rejection_type'] = 'required_if:publish,rejected|nullable|in:report,ethical,other';
        }

        $request->validate($rules);

        $project = Project::findOrFail($id);
        $user = Auth::user();

        // Gate: only assigned reviewers or admins may grade this project.
        if (!$user->isAdmin() && !$this->isAssignedReviewer($project, $user)) {
            return response()->json(['success' => false, 'error' => 'You are not assigned to review this project.'], 403);
        }

        // Gate: a reviewer must claim for himself first Ã¢â‚¬â€ direct POSTs cannot
        // skip the claim step.
        if (!$user->isAdmin() && !$this->hasOwnClaim($project, $user)) {
            return response()->json(['success' => false, 'error' => 'Accept the proposal (claim the project) before grading.'], 403);
        }

        // Report type discriminator: 'progress' (report 1) or 'progress2' (extended report)
        $reportType = $request->report_type ?: 'progress';
        $isProgress2 = $reportType === 'progress2';

        // Gate: progress report 2 grading is only available when the admin has
        // enabled the extended progress flag for this project.
        if ($isProgress2 && !$project->extended_progress) {
            return response()->json([
                'success' => false,
                'error'   => 'Progress Report 2 is not enabled for this project.',
            ], 403);
        }

        // Check file existence instead of status (buttons removed, files uploaded directly)
        $hasProgressAdded = $project->submissions()
            ->where('type', $isProgress2 ? 'progress2' : 'progress')
            ->count() > 0;

        // Gate: reviewer can only grade if progress has been submitted
        if (!$hasProgressAdded) {
            return response()->json([
                'success' => false,
                'error'   => $isProgress2
                    ? 'The LPI has not submitted Progress Report 2 yet. Grading is locked until then.'
                    : 'The LPI has not submitted the progress report yet. Grading is locked until then.',
            ], 403);
        }

        // Gate: grading opens once the progress deadline passes (null = always open).
        $program = $project->program;
        $progressDeadline = $isProgress2
            ? ($program ? $program->prog_rpt2_deadline : null)
            : ($program ? ($program->prog_rpt_deadline ?? null) : null);
        if ($progressDeadline && !now()->greaterThan($progressDeadline)) {
            return response()->json([
                'success' => false,
                'error'   => 'Grading opens after the ' . ($isProgress2 ? 'extended progress report' : 'progress report') . ' deadline.',
            ], 403);
        }

        // Determine publish value based on save_action
        $publishValue = $saveAction === 'draft' ? 'pending' : $request->publish;

        // Determine isAccepted from the user's actual selection
        $userSelection = $request->publish;
        $isAcceptedValue = $userSelection === 'accepted' ? 1 : ($userSelection === 'rejected' ? 0 : 0);

        // Use updateOrCreate with report_type to handle progress/progress2 separately Ã¢â‚¬â€
        // grading write + workflow status recorded inside one transaction so a
        // mid-flight failure cannot desynchronize publish vs. status history.
        $grading = DB::transaction(function () use ($project, $user, $request, $reportType, $isProgress2, $publishValue, $isAcceptedValue, $userSelection, $saveAction, &$stageCompleted) {
            $row = \App\Models\ProgressReportGrading::updateOrCreate(
                ['project_id' => $project->id, 'user_id' => $user->id, 'report_type' => $reportType],
                [
                    'achievementsRating'    => $request->achievementsRating,
                    'publicationsRating'    => $request->publicationsRating,
                    'studentsRating'        => $request->studentsRating,
                    'budgetRating'          => $request->budgetRating,
                    'achievementsComments'  => $request->achievementsComments,
                    'publicationsComments'  => $request->publicationsComments,
                    'studentsComments'      => $request->studentsComments,
                    'budgetComments'        => $request->budgetComments,
                    'ethical'               => $request->has('ethical') ? 1 : 0,
                    'analysis'              => $request->analysis ?? '',
                    'comments'              => $request->comments ?? '',
                    'recommendation'        => $request->recommendation ?? '',
                    'publish'               => $publishValue,
                    'report_type'           => $reportType,
                    'isAccepted'            => $isAcceptedValue,
                    'isAdmin'               => $user->isAdmin(),
                    // Kept on the row so the reason survives until the
                    // consensus completes and records the *_REJECTED status.
                    'rejection_type'        => $userSelection === 'rejected' ? ($request->rejection_type ?: 'report') : null,
                    'rejection_reason'      => $userSelection === 'rejected' ? ($request->rejection_reason ?? $request->comments) : null,
                ]
            );

            // On submit, advance the workflow only through the multi-reviewer
            // consensus: the stage status flips once EVERY assigned reviewer
            // has submitted his own set (a standalone admin grade with no
            // reviewers completes immediately). Any rejected set wins.
            $stageCompleted = false;
            if ($saveAction === 'submit' && in_array($userSelection, ['accepted', 'rejected'], true)) {
                $stageCompleted = $this->recordStageCompletion(
                    $project,
                    $isProgress2 ? 'progress2' : 'progress',
                    $isProgress2 ? Project::STATUS_PROGRESS2_REVIEWED : Project::STATUS_PROGRESS_REVIEWED,
                    $isProgress2 ? Project::STATUS_PROGRESS2_REJECTED : Project::STATUS_PROGRESS_REJECTED,
                    $user,
                    [
                        'triggered_by'  => $isProgress2 ? 'progress2-grade' : 'progress-grade',
                        'comment'       => $request->rejection_reason ?? $request->comments ?? null,
                        'rejection_type' => $request->rejection_type ?: 'report',
                    ]
                );
            }

            return $row;
        });

        $message = 'Progress grade saved successfully.';
        if ($saveAction === 'submit'
            && !$stageCompleted
            && in_array($userSelection, ['accepted', 'rejected'], true)) {
            $message = 'Progress grade saved. The stage will complete once every assigned reviewer has submitted.';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Save the final report grade with section-level scores (gradeA-D + comments).
     */
    public function saveFinalGrade(Request $request, $id)
    {
        $saveAction = $request->input('save_action', 'submit');

        $rules = [
            'gradeA'    => 'required|numeric|between:1,5',
            'commentA'  => 'nullable|string|max:2000',
            'gradeB'    => 'required|numeric|between:1,5',
            'commentB'  => 'nullable|string|max:2000',
            'gradeC'    => 'required|numeric|between:1,5',
            'commentC'  => 'nullable|string|max:2000',
            'gradeD'    => 'required|numeric|between:1,5',
            'commentD'  => 'nullable|string|max:2000',
            'scoreA'    => 'nullable|numeric',
            'scoreB'    => 'nullable|numeric',
            'scoreC'    => 'nullable|numeric',
            'autoGradeA'=> 'nullable|numeric',
            'autoGradeB'=> 'nullable|numeric',
            'autoGradeC'=> 'nullable|numeric',
            'rejection_reason' => 'nullable|string|max:2000',
            'rejection_type'   => 'nullable|in:report,ethical,other',
        ];

        if ($saveAction === 'draft') {
            $rules['publish'] = 'nullable|in:accepted,rejected,pending';
            $rules['rejection_reason'] = 'nullable|string|max:2000';
            $rules['rejection_type'] = 'nullable|in:report,ethical,other';
        } else {
            $rules['publish'] = 'required|in:accepted,rejected,pending';
            $rules['rejection_reason'] = 'required_if:publish,rejected|nullable|string|max:2000';
            $rules['rejection_type'] = 'required_if:publish,rejected|nullable|in:report,ethical,other';
        }

        $request->validate($rules);

        $project = Project::findOrFail($id);
        $user = Auth::user();

        // Gate: only assigned reviewers or admins may grade this project.
        if (!$user->isAdmin() && !$this->isAssignedReviewer($project, $user)) {
            return response()->json(['success' => false, 'error' => 'You are not assigned to review this project.'], 403);
        }

        // Gate: a reviewer must claim for himself first Ã¢â‚¬â€ direct POSTs cannot
        // skip the claim step.
        if (!$user->isAdmin() && !$this->hasOwnClaim($project, $user)) {
            return response()->json(['success' => false, 'error' => 'Accept the proposal (claim the project) before grading.'], 403);
        }

        // Gate: reviewer can only grade the final report once the LPI has added it.
        // Check file existence instead of status (buttons removed, files uploaded directly)
        if ($project->submissions()->where('type', 'final')->count() === 0) {
            return response()->json([
                'success' => false,
                'error'   => 'The LPI has not submitted the final report yet. Grading is locked until then.',
            ], 403);
        }

        // Gate: grading opens once the final deadline passes (null = always open).
        $program = $project->program;
        $finalDeadline = $program ? ($program->final_rpt_deadline ?? null) : null;
        if ($finalDeadline && !now()->greaterThan($finalDeadline)) {
            return response()->json([
                'success' => false,
                'error'   => 'Grading opens after the final report deadline.',
            ], 403);
        }

        $publishValue = $saveAction === 'draft' ? 'pending' : $request->publish;

        // Determine isAccepted from the user's actual selection
        $userSelection = $request->publish;
        $isAcceptedValue = $userSelection === 'accepted' ? 1 : 0;

        $total = ($request->scoreA ?? 0) +
                 ($request->scoreB ?? 0) +
                 ($request->scoreC ?? 0);

        $final = DB::transaction(function () use ($project, $user, $request, $publishValue, $isAcceptedValue, $total, $userSelection) {
            $row = FinalReportGrading::updateOrCreate(
                // Keyed per (project, user): an admin grading on behalf no
                // longer silently overwrites the assigned reviewer's row
                // (which previously broke the reviewer's View Grades modal).
                ['project_id' => $project->id, 'user_id' => $user->id],
                [
                    'gradeA'      => $request->gradeA,
                    'commentA'    => $request->commentA ?? '',
                    'gradeB'      => $request->gradeB,
                    'commentB'    => $request->commentB ?? '',
                    'gradeC'      => $request->gradeC,
                    'commentC'    => $request->commentC ?? '',
                    'gradeD'      => $request->gradeD,
                    'commentD'    => $request->commentD ?? '',
                    'scoreA'      => $request->scoreA ?? 0,
                    'autoGradeA'  => $request->autoGradeA ?? 0,
                    'scoreB'      => $request->scoreB ?? 0,
                    'autoGradeB'  => $request->autoGradeB ?? 0,
                    'scoreC'      => $request->scoreC ?? 0,
                    'autoGradeC'  => $request->autoGradeC ?? 0,
                    'total'       => $total,
                    'publish'     => $publishValue,
                    'isAccepted'  => $isAcceptedValue,
                    'isAdmin'     => $user->isAdmin(),
                    // Kept on the row so the reason survives until the
                    // consensus completes and records the *_REJECTED status.
                    'rejection_type'   => $userSelection === 'rejected' ? ($request->rejection_type ?: 'report') : null,
                    'rejection_reason' => $userSelection === 'rejected' ? ($request->rejection_reason ?? $request->comments) : null,
                ]
            );

            return $row;
        });

        // On submit, advance the workflow only through the multi-reviewer
        // consensus: the status flips once EVERY assigned reviewer has
        // submitted his own final set (standalone admin grading completes
        // immediately). Any rejected set wins.
        $stageCompleted = false;
        if ($saveAction === 'submit' && in_array($userSelection, ['accepted', 'rejected'], true)) {
            $stageCompleted = $this->recordStageCompletion(
                $project,
                'final',
                Project::STATUS_GRADED,
                Project::STATUS_FINAL_REJECTED,
                $user,
                [
                    'triggered_by'   => 'final-grade',
                    'comment'        => $request->rejection_reason ?? $request->comments ?? null,
                    'rejection_type' => $request->rejection_type ?: 'report',
                ]
            );
        }

        $message = 'Final grade submitted successfully.';
        if ($saveAction === 'submit'
            && !$stageCompleted
            && in_array($userSelection, ['accepted', 'rejected'], true)) {
            $message = 'Final grade saved. Grading completes once every assigned reviewer has submitted.';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update verification status for outcomes, students, and researchers (AJAX endpoint).
     */
    public function updateVerification(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:outcome,student,researcher',
            'ids'    => 'required|array',
            'status' => 'required|in:verified,pending',
        ]);

        $model = null;
        switch ($request->type) {
            case 'outcome':
                $model = \App\Models\Outcome::class;
                break;
            case 'student':
                $model = \App\Models\ProjectStudent::class;
                break;
            case 'researcher':
                $model = \App\Models\ProjectResearcher::class;
                break;
        }

        if ($model) {
            // IDOR guard: everyone in scope must belong to a project the
            // calling reviewer is assigned to (admins may verify on behalf).
            $user = Auth::user();
            $rows = $model::whereIn('id', $request->ids)
                ->with('project')
                ->get();
            foreach ($rows as $row) {
                $project = $row->project;
                if ($user->isAdmin()) continue;
                if (!$project || !$this->isAssignedReviewer($project, $user)) {
                    return response()->json([
                        'success' => false,
                        'error'   => 'You are not assigned to review this project.',
                    ], 403);
                }
            }

            $model::whereIn('id', $request->ids)->update([
                'verifcation_by_reviewer' => $request->status,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * True when the given user is one of the assigned reviewers for a project.
     */
    protected function isAssignedReviewer(Project $project, User $user): bool
    {
        return DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * True when this user has claimed (accepted the proposal for) the project.
     * A reviewer must claim for HIMSELF before the grading form unlocks Ã¢â‚¬â€
     * direct URL/POST visits cannot skip the claim step.
     */
    protected function hasOwnClaim(Project $project, User $user): bool
    {
        return DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->whereNotNull('claimed_at')
            ->exists();
    }

    /**
     * Consensus completion for a grading stage (multi-reviewer).
     *
     * The project-level workflow status only advances once EVERY assigned
     * reviewer has submitted his own set for this stage (a standalone admin
     * grade with no reviewers completes immediately). On completion the stage
     * flips exactly once: any rejected set wins over accepted ones.
     *
     * A stage that already completed (reviewed/rejected status on record) is
     * FROZEN: later sets Ã¢â‚¬â€ e.g. from a reviewer added afterwards Ã¢â‚¬â€ are stored
     * but never re-open or flip the workflow. The LPI notification fires here,
     * exactly once, on a no-rejection completion. Also invoked when an
     * assignment is removed, so dropping the blocking reviewer completes the
     * stage instead of leaving it wedged.
     *
     * @return bool true when this call completed the stage with no rejection
     *              (and the LPI was notified).
     */
    public function recordStageCompletion(
        Project $project,
        string $stage,
        string $reviewedStatus,
        string $rejectedStatus,
        User $actor,
        array $metadata = []
    ): bool {
        // Already completed for this round — grades are records only.
        // Exception: an Ethical/Other rejection carries no v2 resubmission and
        // no admin review step — it is resolved by the rejector amending their
        // own grading in place. Let that one re-grade re-evaluate the stage;
        // every other case stays frozen so a late reviewer cannot reopen it.
        if (in_array($project->currentWorkflowStatus(), [$reviewedStatus, $rejectedStatus], true)) {
            $amendingOwnEthicalRejection = false;
            if ($project->currentWorkflowStatus() === $rejectedStatus) {
                $latestRejection = $project->statusHistories()
                    ->where('status', $rejectedStatus)
                    ->latest('id')
                    ->first();
                $amendingOwnEthicalRejection = $latestRejection
                    && in_array($latestRejection->metadata['rejection_type'] ?? 'report', ['ethical', 'other'], true)
                    && (string) ($latestRejection->metadata['rejected_by'] ?? '') === (string) $actor->id;
            }
            if (!$amendingOwnEthicalRejection) {
                return false;
            }
        }

        $assigned = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->pluck('user_id');
        $submitted = $this->stageSubmitters($project, $stage);

        if ($submitted->isEmpty()) {
            return false; // nobody has graded this stage (e.g. last reviewer removed)
        }
        if ($assigned->isNotEmpty() && $assigned->diff($submitted)->isNotEmpty()) {
            return false; // still waiting for other reviewers
        }

        $rejectedRow = $this->latestRejectedRow($project, $stage);

        if ($rejectedRow) {
            if (empty($metadata['comment'])) {
                $metadata['comment'] = $rejectedRow->rejection_reason;
            }
            if (empty($metadata['rejection_type'])) {
                $metadata['rejection_type'] = $rejectedRow->rejection_type ?: 'report';
            }
            $metadata['rejected_by'] = $rejectedRow->user_id;
        }

        $project->recordStatus(
            $rejectedRow ? $rejectedStatus : $reviewedStatus,
            array_merge([
                'triggered_by'  => 'grade-consensus',
                'report_type'   => $stage,
                'all_submitted' => true,
            ], $metadata),
            $actor->id
        );

        if ($rejectedRow === null) {
            $this->notifyLpiReportGraded(
                $project->fresh(),
                $actor,
                $stage === 'final' ? 'final_graded' : 'progress_graded'
            );
        }

        return $rejectedRow === null;
    }

    /**
     * User ids that have submitted (publish != pending) their own set for
     * the given stage Ã¢â‚¬â€ used to decide whether the consensus is complete.
     *
     * @return \Illuminate\Support\Collection
     */
    private function stageSubmitters(Project $project, string $stage)
    {
        if ($stage === 'final') {
            return FinalReportGrading::where('project_id', $project->id)
                ->where('publish', '!=', 'pending')
                ->distinct()
                ->pluck('user_id');
        }

        return ProgressReportGrading::where('project_id', $project->id)
            ->where('publish', '!=', 'pending')
            ->when(
                $stage === 'progress2',
                fn ($q) => $q->where('report_type', 'progress2'),
                fn ($q) => $q->where(function ($w) {
                    $w->where('report_type', 'progress')->orWhereNull('report_type');
                })
            )
            ->distinct()
            ->pluck('user_id');
    }

    /**
     * The most recent rejected set for the stage, if any Ã¢â‚¬â€ carries the
     * rejection reason/type for the *_REJECTED status metadata.
     */
    private function latestRejectedRow(Project $project, string $stage)
    {
        if ($stage === 'final') {
            return FinalReportGrading::where('project_id', $project->id)
                ->where('publish', 'rejected')
                ->orderBy('id', 'desc')
                ->first();
        }

        return ProgressReportGrading::where('project_id', $project->id)
            ->where('publish', 'rejected')
            ->when(
                $stage === 'progress2',
                fn ($q) => $q->where('report_type', 'progress2'),
                fn ($q) => $q->where(function ($w) {
                    $w->where('report_type', 'progress')->orWhereNull('report_type');
                })
            )
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Send the report-graded notification to the project's LPI
     * (automatic; gated by MAIL_ENABLED inside EventMailService).
     */
    private function notifyLpiReportGraded(Project $project, $actor, string $eventKey): void
    {
        $lpi = $project->lpi;
        if (!$lpi) {
            return;
        }

        app(\App\Services\EventMailService::class)->send(
            $eventKey,
            $lpi,
            $project,
            $actor instanceof User ? $actor : $lpi
        );
    }

    /**
     * Whether a proposal PDF actually exists on disk for the project.
     * Mirrors the resolution order used by serveFile2 / serveProposal:
     * canonical <id>.pdf first, then legacy <id>_proposal/_Application names
     * and the stored proposal_filename value.
     */
    private function proposalFileExists(Project $project): bool
    {
        $dir = storage_path('app/' . $project->getStorageDir('proposals'));
        $oldId = $project->getFileSafeOldProjectId();

        $candidates = [
            $dir . '/' . $oldId . '.pdf',
            $dir . '/' . $oldId . '_proposal.pdf',
            $dir . '/' . $oldId . '_Application.pdf',
        ];

        if (!empty($project->proposal_filename)) {
            $candidates[] = $dir . '/' . $project->proposal_filename;
        }

        foreach (array_unique($candidates) as $candidate) {
            if (file_exists($candidate)) {
                return true;
            }
        }

        return false;
    }
}
