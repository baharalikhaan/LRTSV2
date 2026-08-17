<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProgressReportGrading;
use App\Models\FinalReportGrading;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin() && !auth()->user()->isReviewer()) {
                abort(403, 'Access denied. Reviewers only.');
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

        $cycles = \App\Models\CycleConfig::orderBy('program_title')->get();
        $cycleId = request('cycle_id');

        $query = $user->isAdmin()
            ? Project::with('lpi', 'program', 'program.grant')
            : $user->reviewedProjects()->visibleProgram()->with('lpi', 'program', 'program.grant');

        if ($cycleId) {
            $query->where('cycle_id', $cycleId);
        }

        $gradedProjects = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('grading.gradedProjects', compact('gradedProjects', 'cycles', 'cycleId'));
    }

    /**
     * Show the grading form for a specific project.
     * Deprecated alias — the unified grading page is at /projects/{id}/grading.
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

        // Gate: admins may grade any project; reviewers must be assigned.
        if (!$user->isAdmin() && !$this->isAssignedReviewer($project, $user)) {
            abort(403, 'You are not assigned to review this project.');
        }

        $finalGrading = FinalReportGrading::where('project_id', $id)->first();
        $progressGrading = ProgressReportGrading::where('project_id', $id)
            ->where(function ($q) {
                $q->where('report_type', 'progress')->orWhereNull('report_type');
            })
            ->first();
        $progress2Grading = $project->extended_progress
            ? ProgressReportGrading::where('project_id', $id)->where('report_type', 'progress2')->first()
            : null;

        $commitments = $project->commitments()->first();
        $contributions = $project->contributions()->get();
        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();
        $researchers = $project->researchers()->get();

        $submissions = $project->submissions()
            ->orderBy('created_at', 'desc')
            ->get();

        // LPI submission gates — check file existence instead of status
        // (buttons removed, files uploaded directly)
        $progressSubmitted = $submissions->where('type', 'progress')->count() > 0;
        $progress2Submitted = $project->extended_progress ? $submissions->where('type', 'progress2')->count() > 0 : false;
        $finalSubmitted    = $submissions->where('type', 'final')->count() > 0;

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

        // ─── Auto-grade calculations (scores from database) ───
        $scoreMap = \App\Models\Score::getMap();

        $achievementTypes = [
            'ip_disclosure', 'provisional_patent', 'granted_patent',
            'open_source', 'open_source_sw', 'startup', 'prototype', 'cross_college',
        ];

        $studentTypes = ['masters', 'UG', 'PhD'];

        // Grade A & B: Expected sum from commitments
        $expectedSumA = 0;
        if ($commitments) {
            $expectedSumA = ($commitments->q1article ?? 0) * ($scoreMap['journal_q1'] ?? 0)
                + ($commitments->q2article ?? 0) * ($scoreMap['journal_q2'] ?? 0)
                + ($commitments->q3article ?? 0) * ($scoreMap['journal_q3'] ?? 0)
                + ($commitments->q4article ?? 0) * ($scoreMap['journal_q4'] ?? 0)
                + ($commitments->confArticle ?? 0) * ($scoreMap['conference'] ?? 0)
                + ($commitments->books ?? 0) * ($scoreMap['book'] ?? 0)
                + ($commitments->editBooks ?? 0) * ($scoreMap['edited_book'] ?? 0)
                + ($commitments->chapters ?? 0) * ($scoreMap['book_chapter'] ?? 0)
                + ($commitments->ip ?? 0) * ($scoreMap['ip_disclosure'] ?? 0)
                + ($commitments->filedPatent ?? 0) * ($scoreMap['provisional_patent'] ?? 0)
                + ($commitments->grantedPatent ?? 0) * ($scoreMap['granted_patent'] ?? 0)
                + ($commitments->openSourceSW ?? 0) * ($scoreMap['open_source_sw'] ?? 0)
                + ($commitments->startUp ?? 0) * ($scoreMap['startup'] ?? 0);
        }

        // Grade A & B: Actual sum from outcomes (use scoreMap from database)
        $actualSumA = $outcomes->reduce(function ($carry, $o) use ($scoreMap) {
            return $carry + ($scoreMap[$o->type] ?? 0);
        }, 0);

        // Grade A: normalized to 1-5, capped at 5
        $autoGradeA = $expectedSumA > 0 ? min(round(($actualSumA / $expectedSumA) * 5, 2), 5) : 0;

        // Grade B: same calculation as Grade A (publications & IP)
        $autoGradeB = $expectedSumA > 0 ? min(round(($actualSumA / $expectedSumA) * 5, 2), 5) : 0;

        // Grade C: Student & Researcher Involvement
        $expectedSumC = 0;
        if ($commitments) {
            $expectedSumC = ($commitments->master ?? 0) * ($scoreMap['masters'] ?? 0)
                + ($commitments->UG ?? 0) * ($scoreMap['ug'] ?? 0)
                + ($commitments->Phd ?? 0) * ($scoreMap['phd'] ?? 0);
        }
        // Use scoreMap from database for students and researchers
        $actualSumC = $students->reduce(function ($carry, $s) use ($scoreMap) {
            $key = strtolower($s->type);
            return $carry + ($scoreMap[$key] ?? 0);
        }, 0) + $researchers->reduce(function ($carry, $r) use ($scoreMap) {
            return $carry + ($scoreMap['researcher'] ?? 0);
        }, 0);
        $autoGradeC = $expectedSumC > 0 ? min(round(($actualSumC / $expectedSumC) * 5, 2), 5) : 0;

        // Grade D: no auto-calculation
        $autoGradeD = null;

        // Deadline info — show/hide grading forms based on deadlines
        $program = $project->program;
        $progressDeadline = $program ? ($program->prog_rpt_deadline ?? null) : null;
        $progress2Deadline = $program ? $program->prog_rpt2_deadline : null;
        $finalDeadline = $program ? ($program->final_rpt_deadline ?? null) : null;
        $now = now();
        $progressDeadlinePassed = $progressDeadline ? $now->greaterThan($progressDeadline) : true;
        $progress2DeadlinePassed = $project->extended_progress ? ($progress2Deadline ? $now->greaterThan($progress2Deadline) : true) : false;
        $finalDeadlinePassed = $finalDeadline ? $now->greaterThan($finalDeadline) : true;

        // Latest rejection reason for pre-filling the re-grade form after resubmission
        $progressRejection = $project->statusHistories()
            ->where('status', Project::STATUS_PROGRESS_REJECTED)
            ->latest()->first();
        $progress2Rejection = $project->statusHistories()
            ->where('status', Project::STATUS_PROGRESS2_REJECTED)
            ->latest()->first();
        $finalRejection = $project->statusHistories()
            ->where('status', Project::STATUS_FINAL_REJECTED)
            ->latest()->first();
        $progressRejectionReason = $progressRejection->metadata['comment'] ?? $progressRejection->metadata['reason'] ?? null;
        $progress2RejectionReason = $progress2Rejection ? ($progress2Rejection->metadata['comment'] ?? $progress2Rejection->metadata['reason'] ?? null) : null;
        $finalRejectionReason = $finalRejection->metadata['comment'] ?? $finalRejection->metadata['reason'] ?? null;

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
            'progressSubmitted',
            'progress2Submitted',
            'finalSubmitted',
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
            'progressRejectionReason',
            'progress2RejectionReason',
            'finalRejectionReason'
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
        ];

        // For "draft", publish is optional; for "submit", it's required
        if ($saveAction === 'draft') {
            $rules['publish'] = 'nullable|in:accepted,rejected,reserved,pending';
            $rules['rejection_reason'] = 'nullable|string|max:2000';
        } else {
            $rules['publish'] = 'required|in:accepted,rejected,reserved,pending';
            $rules['rejection_reason'] = 'required_if:publish,rejected|nullable|string|max:2000';
        }

        $request->validate($rules);

        $project = Project::findOrFail($id);
        $user = Auth::user();

        // Gate: admins may grade any project; reviewers must be assigned.
        if (!$user->isAdmin() && !$this->isAssignedReviewer($project, $user)) {
            return response()->json(['success' => false, 'error' => 'You are not assigned to review this project.'], 403);
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

        // Use updateOrCreate with report_type to handle progress/progress2 separately
        $grading = \App\Models\ProgressReportGrading::updateOrCreate(
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
            ]
        );

        // On submit, record the workflow status
        if ($saveAction === 'submit') {
            if ($userSelection === 'accepted') {
                $project->recordStatus($isProgress2 ? Project::STATUS_PROGRESS2_REVIEWED : Project::STATUS_PROGRESS_REVIEWED, [
                    'triggered_by' => $isProgress2 ? 'progress2-grade-accept' : 'progress-grade-accept',
                    'report_type'  => $reportType,
                ], $user->id);
            } elseif ($userSelection === 'rejected') {
                $project->recordStatus($isProgress2 ? Project::STATUS_PROGRESS2_REJECTED : Project::STATUS_PROGRESS_REJECTED, [
                    'triggered_by' => $isProgress2 ? 'progress2-grade-reject' : 'progress-grade-reject',
                    'comment'      => $request->rejection_reason ?? $request->comments ?? null,
                    'report_type'  => $reportType,
                ], $user->id);

                // A rejected grade must not persist: remove the grading record
                // entirely so the reviewer's form re-opens fresh after the LPI
                // resubmits (v2). The rejection reason lives in status history.
                \App\Models\ProgressReportGrading::where('project_id', $project->id)
                    ->where('user_id', $user->id)
                    ->where('report_type', $reportType)
                    ->delete();
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Progress grade saved successfully.']);
        }

        return redirect()->back()->with('success', 'Progress grade saved successfully.');
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
        ];

        if ($saveAction === 'draft') {
            $rules['publish'] = 'nullable|in:accepted,rejected,pending';
            $rules['rejection_reason'] = 'nullable|string|max:2000';
        } else {
            $rules['publish'] = 'required|in:accepted,rejected,pending';
            $rules['rejection_reason'] = 'required_if:publish,rejected|nullable|string|max:2000';
        }

        $request->validate($rules);

        $project = Project::findOrFail($id);
        $user = Auth::user();

        // Gate: admins may grade any project; reviewers must be assigned.
        if (!$user->isAdmin() && !$this->isAssignedReviewer($project, $user)) {
            return response()->json(['success' => false, 'error' => 'You are not assigned to review this project.'], 403);
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

        $final = FinalReportGrading::updateOrCreate(
            ['project_id' => $project->id],
            [
                'user_id'     => $user->id,
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
            ]
        );

        // On submit, record the workflow status
        if ($saveAction === 'submit') {
            if ($userSelection === 'accepted') {
                $project->recordStatus(Project::STATUS_GRADED, [
                    'triggered_by' => 'final-grade',
                ], $user->id);
            } elseif ($userSelection === 'rejected') {
                $project->recordStatus(Project::STATUS_FINAL_REJECTED, [
                    'triggered_by' => 'final-grade-reject',
                    'comment'      => $request->rejection_reason ?? $request->comments ?? null,
                ], $user->id);

                // A rejected grade must not persist: remove the grading record
                // entirely so the reviewer's form re-opens fresh after the LPI
                // resubmits (v2). The rejection reason lives in status history.
                FinalReportGrading::where('project_id', $project->id)
                    ->where('user_id', $user->id)
                    ->delete();
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Final grade saved successfully.']);
        }

        return redirect()->back()->with('success', 'Final grade submitted successfully.');
    }

    /**
     * Submit the final grade (legacy endpoint).
     */
    public function submitFinalGrade(Request $request, $id)
    {
        return $this->saveFinalGrade($request, $id);
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
     * Submit the grade — marks the project as Graded in the workflow.
     */
    public function submitGrade(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        // Admins may finalize any project; reviewers must be assigned.
        if (!$user->isAdmin() && !$this->isAssignedReviewer($project, $user)) {
            return response()->json(['success' => false, 'error' => 'You are not assigned to review this project.'], 403);
        }

        // Gate: cannot finalize the grade until the LPI has added the final report.
        // Check file existence instead of status (buttons removed, files uploaded directly)
        if ($project->submissions()->where('type', 'final')->count() === 0) {
            return response()->json([
                'success' => false,
                'error'   => 'The LPI has not submitted the final report yet. You cannot finalize the grade until then.',
            ], 403);
        }

        if (!$project->hasStatus(Project::STATUS_GRADED)) {
            $project->recordStatus(Project::STATUS_GRADED, [
                'triggered_by' => 'submit-grade',
            ], $user->id);
        }

        return response()->json(['success' => true, 'message' => 'Grade submitted successfully. Project marked as Graded.']);
    }
}
