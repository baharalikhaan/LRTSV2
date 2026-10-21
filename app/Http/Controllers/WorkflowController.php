<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\EmailSendLog;
use App\Mail\GenericEmailMail;
use App\Services\EventMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class WorkflowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        })->only(['assignReviewers']);
    }

    public function showAssignForm($projectId)
    {
        $project = Project::with(['program', 'grant', 'latestStatus'])->findOrFail($projectId);
        $reviewers = User::where('type', 'LIKE', '%Reviewer%')->get();
        return view('workflow.modals.assign', compact('project', 'reviewers'));
    }

    public function showReviewForm($projectId)
    {
        $project = Project::with(['program', 'grant', 'latestStatus'])->findOrFail($projectId);
        return view('workflow.modals.review', compact('project'));
    }

    public function showAcceptProposalForm($projectId)
    {
        $project = Project::with(['program', 'grant', 'commitments', 'latestStatus'])->findOrFail($projectId);
        return view('workflow.modals.accept-proposal', compact('project'));
    }

    public function showProgressForm($projectId)
    {
        $project = Project::with(['program', 'grant', 'latestStatus'])->findOrFail($projectId);
        return view('workflow.modals.progress', compact('project'));
    }

    public function showReportCardForm($projectId)
    {
        $project = Project::with([
            'program.grant', 'lpi', 'pillars', 'colleges',
            'commitments', 'outcomes.publication',
            'students.details', 'researchers',
        ])->findOrFail($projectId);

        $commitment = $project->commitments()->first();
        $finalGradings = \App\Models\FinalReportGrading::with('user')
            ->where('project_id', $projectId)->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();
        $progressGradings = \App\Models\ProgressReportGrading::with(['user', 'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef'])
            ->where('project_id', $projectId)
            ->where(function ($q) {
                $q->where('report_type', 'progress')->orWhereNull('report_type');
            })
            ->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();
        $progress2Gradings = \App\Models\ProgressReportGrading::with(['user', 'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef'])
            ->where('project_id', $projectId)->where('report_type', 'progress2')
            ->where('publish', '!=', 'pending')
            ->orderBy('user_id')->get();
        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();
        $researchers = $project->researchers()->get();

        return view('workflow.modals.report-card', compact(
            'project', 'commitment', 'finalGradings', 'progressGradings', 'progress2Gradings',
            'outcomes', 'students', 'researchers'
        ));
    }

    public function modal($action, $projectId)
    {
        $project = Project::with(['program', 'grant', 'latestStatus'])->findOrFail($projectId);

        switch ($action) {
            case 'progress':
                $html = view('workflow.modals.progress', compact('project'))->render();
                break;

            case 'assign':
                $html = view('workflow.modals.assign', compact('project'))->render();
                break;

            case 'review':
                $html = view('workflow.modals.review', compact('project'))->render();
                break;

            case 'accept-proposal':
                $html = view('workflow.modals.accept-proposal', compact('project'))->render();
                break;

            case 'report-card':
                $project = Project::with([
                    'program.grant', 'lpi', 'pillars', 'colleges',
                    'commitments', 'outcomes.publication',
                    'students.details', 'researchers',
                ])->findOrFail($projectId);

                $commitment = $project->commitments()->first();
                $finalGradings = \App\Models\FinalReportGrading::with('user')
                    ->where('project_id', $projectId)->where('publish', '!=', 'pending')
                    ->orderBy('user_id')->get();
                $progressGradings = \App\Models\ProgressReportGrading::with(['user', 'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef'])
                    ->where('project_id', $projectId)
                    ->where(function ($q) {
                        $q->where('report_type', 'progress')->orWhereNull('report_type');
                    })
                    ->where('publish', '!=', 'pending')
                    ->orderBy('user_id')->get();
                $progress2Gradings = \App\Models\ProgressReportGrading::with(['user', 'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef'])
                    ->where('project_id', $projectId)->where('report_type', 'progress2')
                    ->where('publish', '!=', 'pending')
                    ->orderBy('user_id')->get();
                $outcomes = $project->outcomes()->get();
                $students = $project->students()->get();
                $researchers = $project->researchers()->get();

                $html = view('workflow.modals.report-card', compact(
                    'project', 'commitment', 'finalGradings', 'progressGradings', 'progress2Gradings',
                    'outcomes', 'students', 'researchers'
                ))->render();
                break;

            case 'review-rejection':
                $reportType = request()->query('report_type', 'progress');
                $html = view('workflow.modals.review-rejection', compact('project', 'reportType'))->render();
                break;

            default:
                return response()->json(['error' => 'Unknown action.'], 422);
        }

        return response()->json(['html' => $html]);
    }

    /**
     * Record a status transition for a project action.
     */
    public function transition(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'action'     => 'required|string',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $user = auth()->user();

        // Spreadsheet workflow status map
        $statusMap = [
            'register'   => Project::STATUS_REGISTERED,
            'progress'   => Project::STATUS_PROGRESS_ADDED,
            'assign'     => Project::STATUS_ASSIGNED,
            'claim'      => 'claim',  // handled specially in submitProposalDecision
            'report-card' => null,    // view only, no status transition
        ];

        $status = $statusMap[$validated['action']] ?? null;

        // If no status or view-only (report-card), just return success
        if ($status === null) {
            return response()->json([
                'success' => true,
                'message' => 'Action processed.',
                'status'  => $status,
            ]);
        }

        // Verify the action is valid for this project
        $validActions = $project->availableActions($user);
        $validActionKeys = array_column($validActions, 'action');

        if (!in_array($validated['action'], $validActionKeys)) {
            return response()->json(['error' => 'Action not available for this project.'], 422);
        }

        // Record the status
        $project->recordStatus($status, ['triggered_by' => $validated['action']], $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status'  => $status,
        ]);
    }

    /**
     * Record status and redirect (for non-AJAX fallback - e.g. assign reviewer).
     */
    public function recordAndRedirect(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'action'     => 'required|string',
            'redirect'   => 'nullable|string',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $user = auth()->user();

        $statusMap = [
            'register'        => Project::STATUS_REGISTERED,
            'progress'        => Project::STATUS_PROGRESS_ADDED,
            'assign'          => Project::STATUS_ASSIGNED,
        ];

        $status = $statusMap[$validated['action']] ?? null;
        if ($status) {
            // Same guard as transition(): the action must be available for this
            // project and this user (prevents arbitrary status stamping by
            // non-admins / non-owners on projects they don't manage).
            $validActions = $project->availableActions($user);
            $validActionKeys = array_column($validActions, 'action');

            if (!in_array($validated['action'], $validActionKeys, true)) {
                $redirectUrl = $validated['redirect'] ?? route('projects.available');
                return redirect($redirectUrl)->with('error', 'Action not available for this project.');
            }

            $project->recordStatus($status, null, $user->id);
        }

        $redirectUrl = $validated['redirect'] ?? route('projects.available');

        return redirect($redirectUrl)->with('success', 'Status updated successfully.');
    }

    /**
     * AJAX: Assign a single reviewer to a project via the workflow modal.
     * Also records the 'Assigned' status on the project.
     */
    public function assignReviewers(Request $request)
    {
        $validated = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'reviewer_ids' => 'required|array|min:1',
            'reviewer_ids.*' => 'exists:users,id',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        $reviewerIds = $validated['reviewer_ids'];

        // Server-side check: prevent reassigning a reviewer who previously rejected this project
        if (Schema::hasTable('reviewer_rejections')) {
            foreach ($reviewerIds as $reviewerId) {
                $previouslyRejected = \App\Models\ReviewerRejection::where('project_id', $project->id)
                    ->where('user_id', $reviewerId)
                    ->exists();
                if ($previouslyRejected) {
                    return response()->json([
                        'success' => false,
                        'error'   => 'This reviewer previously rejected this project and cannot be reassigned.',
                    ], 422);
                }
            }
        }

        $newlyAssigned = [];

        DB::transaction(function () use ($project, $reviewerIds, &$newlyAssigned) {
            // Multi-reviewer model: reviewers are ADDED alongside the existing
            // ones (never replaced), so each keeps his own grading set.
            $hadReviewers = DB::table('projects_reviewers')
                ->where('project_id', $project->id)
                ->exists();

            foreach ($reviewerIds as $reviewerId) {
                $alreadyAssigned = DB::table('projects_reviewers')
                    ->where('project_id', $project->id)
                    ->where('user_id', $reviewerId)
                    ->exists();

                if ($alreadyAssigned) {
                    continue; // idempotent â€” already on the team
                }

                DB::table('projects_reviewers')->insert([
                    'project_id' => $project->id,
                    'user_id'    => $reviewerId,
                    'role'       => 'Reviewer',
                    'claimed_at' => null, // each reviewer claims for himself
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $newlyAssigned[] = $reviewerId;
            }

            // Record Assigned status only for the FIRST assignment â€” adding
            // another reviewer to an already in-review project must not roll
            // its workflow status (Claimed/progress_*) back to Assigned.
            if (!$hadReviewers && !empty($newlyAssigned)) {
                $project->recordStatus(Project::STATUS_ASSIGNED, [
                    'triggered_by' => 'assign',
                    'reviewer_id'  => $newlyAssigned[0],
                ], auth()->id());
            }
        });

        if (empty($newlyAssigned)) {
            return response()->json([
                'success' => true,
                'message' => 'Selected reviewer is already assigned to this project.',
            ]);
        }

        // Notify the assigned reviewer (automatic; gated by MAIL_ENABLED).
        foreach ($newlyAssigned as $reviewerId) {
            $reviewer = User::find($reviewerId);
            if ($reviewer) {
                app(EventMailService::class)->send('reviewer_assigned', $reviewer, $project, auth()->user());
            }
        }

        return response()->json(['success' => true, 'message' => 'Reviewer assigned successfully.']);
    }

    /**
     * AJAX: Submit accept/reject decision from the workflow modal.
     *
     * Single-reviewer workflow: when the reviewer accepts, records Claimed directly.
     */
    public function submitProposalDecision(Request $request)
    {
        $validated = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'r_id'             => 'required|integer|exists:projects_reviewers,id',
            'accept'           => 'required|in:accepted,rejected',
            'reject_reason'    => 'nullable|string|max:2000',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        $user = auth()->user();

        // Verify this user is actually assigned as a reviewer for this project
        $reviewerRecord = DB::table('projects_reviewers')
            ->where('id', $validated['r_id'])
            ->where('project_id', $validated['project_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$reviewerRecord) {
            return response()->json(['error' => 'You are not authorized to make a decision on this proposal.'], 403);
        }

        // â”€â”€â”€ ACCEPTED: keep the assignment and record Claimed â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        if ($validated['accept'] === 'accepted') {
            // Per-reviewer claim: mark THIS reviewer's assignment as claimed so
            // his own grading form unlocks (other assigned reviewers still have
            // to accept for themselves).
            DB::table('projects_reviewers')
                ->where('id', $validated['r_id'])
                ->update(['claimed_at' => now(), 'updated_at' => now()]);

            // Project-level Claimed status + LPI mail are recorded only from a
            // pre-claim state (no status yet / registered / assigned / proposal
            // rejected / reviewer un-assigned): the FIRST claim of a round
            // records it, a later reviewer's claim never regresses a post-claim
            // status (progress_added, final_added, graded, â€¦), and the LPI is
            // mailed once per round instead of once per reviewer.
            $current = $project->currentWorkflowStatus();
            $preClaimStates = [
                Project::STATUS_IMPORTED,
                Project::STATUS_REGISTERED,
                Project::STATUS_ASSIGNED,
                Project::STATUS_PROPOSAL_REJECTED,
                Project::STATUS_REVIEWER_UNASSIGNED,
            ];
            if ($current === null || in_array($current, $preClaimStates, true)) {
                $project->recordStatus(Project::STATUS_CLAIMED, [
                    'triggered_by'  => 'claim',
                ], $user->id);

                $lpi = $project->lpi;
                if ($lpi) {
                    app(EventMailService::class)->send('proposal_accepted', $lpi, $project, $user);
                }
            }

            return response()->json(['success' => true, 'message' => 'Proposal accepted successfully.']);
        }

        // â”€â”€â”€ REJECTED: audit trail + un-assign + record Rejected â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // The project goes back to the admin queue so a different reviewer can be
        // assigned. The rejecting reviewer is recorded in `reviewer_rejections` so
        // the assign UI can exclude them from the dropdown.
        DB::transaction(function () use ($validated, $project, $user, $reviewerRecord) {
            // 1. Audit trail of who rejected (so admin doesn't re-assign the same reviewer)
            if (\Illuminate\Support\Facades\Schema::hasTable('reviewer_rejections')) {
                \App\Models\ReviewerRejection::create([
                    'project_id' => $project->id,
                    'user_id'    => $user->id,
                    'reason'     => $validated['reject_reason'] ?? null,
                ]);
            }

            // 2. Clear the assignment (back to admin queue)
            DB::table('projects_reviewers')
                ->where('id', $validated['r_id'])
                ->delete();

            // 3. Record the proposal rejection status (NOT progress_rejected).
            // Round-aware guard: record unless the CURRENT latest status is
            // already a proposal rejection (prevents double-submit duplicates).
            // The previous all-time hasStatus() check suppressed the SECOND
            // proposal round entirely, leaving the project locked on "Assigned"
            // with no reviewer and no admin actions recorded.
            $latestStatus = $project->statusHistories()
                ->orderByDesc('id')
                ->value('status');
            $recorded = false;
            if ($latestStatus !== Project::STATUS_PROPOSAL_REJECTED) {
                $project->recordStatus(Project::STATUS_PROPOSAL_REJECTED, [
                    'triggered_by' => 'reject-proposal',
                    'rejected_by'  => $user->id,
                    'reason'       => $validated['reject_reason'] ?? null,
                ], $user->id);
                $recorded = true;
            }

            // Notify the LPI once per rejection round â€” in lock-step with the
            // status guard above, so a second rejecting reviewer does not mail
            // the same "proposal rejected" event again.
            if ($recorded) {
                $lpi = $project->fresh()->lpi;
                if ($lpi) {
                    app(EventMailService::class)->send('proposal_rejected', $lpi, $project, $user, [
                        '*reason*' => $validated['reject_reason'] ?? '',
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Proposal rejected. The project has been returned to the admin queue for reassignment.',
        ]);
    }

    /**
     * AJAX endpoint: returns rendered HTML for the "View Grade" modal.
     * Used on the home dashboard by reviewers to see their submitted grade.
     */
    public function viewGrade($projectId)
    {
        $project = Project::with(['program.grant', 'latestStatus'])->findOrFail($projectId);

        // Ensure the authenticated user is assigned to this project as a reviewer
        $isAssigned = DB::table('projects_reviewers')
            ->where('project_id', $projectId)
            ->where('user_id', auth()->id())
            ->exists();

        if (!auth()->user()->isAdmin() && !$isAssigned) {
            return response()->json([
                'success' => false,
                'error' => 'You are not assigned to review this project.',
            ]);
        }

        // Load this reviewer's grading records for each report stage
        $userId = auth()->id();
        $progressGrading = \App\Models\ProgressReportGrading::with([
            'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef',
        ])
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->where('report_type', 'progress')->orWhereNull('report_type');
            })
            ->first();

        $progress2Grading = \App\Models\ProgressReportGrading::with([
            'achievementsRatingRef', 'publicationsRatingRef', 'studentsRatingRef', 'budgetRatingRef',
        ])
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->where('report_type', 'progress2')
            ->first();

        $finalGrading = \App\Models\FinalReportGrading::where('project_id', $projectId)
            ->where('user_id', $userId)
            ->first();

        $html = view('workflow.modals.view-grade', compact(
            'project', 'progressGrading', 'progress2Grading', 'finalGrading'
        ))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    /**
     * AJAX: Admin reviews progress rejection.
     * Can either send to LPI for resubmission or override rejection.
     */
    public function reviewProgressRejection(Request $request, $id)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'action'      => 'required|in:send_to_lpi',
            'message'     => 'required|string|max:2000',
            'report_type' => 'required|in:progress,progress2,final',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Only administrators can review rejections.'], 403);
        }

        // Ethical/Other rejections ALSO route through this step: the admin
        // forwards them to the LPI, who re-sends the ethical approval document
        // (the progress/final report itself is never re-uploaded — that upload
        // stays locked by the rejection hold).

        // There must be a pending (still un-reviewed) report-type rejection for
        // the given report type. This also blocks recording a "reviewed"
        // status on projects that were never rejected, or whose latest
        // rejection is already resolved/superseded.
        if (!$project->hasUnreviewedRejection($validated['report_type'])) {
            return response()->json([
                'error' => 'There is no pending report rejection to review for this project.',
            ], 422);
        }

        if ($validated['report_type'] === 'final') {
            $status = Project::STATUS_FINAL_REJ_REVIEWED;
        } elseif ($validated['report_type'] === 'progress2') {
            $status = Project::STATUS_PROGRESS2_REJ_REVIEWED;
        } else {
            $status = Project::STATUS_PROGRESS_REJ_REVIEWED;
        }

        // Read the rejection type so the forward can tell the LPI WHAT to fix:
        // an Ethical/Other rejection expects the ethical-approval document to
        // be re-sent, never a re-upload of the progress/final report itself.
        $rejectedStatusMap = [
            'final'     => Project::STATUS_FINAL_REJECTED,
            'progress2' => Project::STATUS_PROGRESS2_REJECTED,
            'progress'  => Project::STATUS_PROGRESS_REJECTED,
        ];
        $lastRejection = $project->statusHistories()
            ->where('status', $rejectedStatusMap[$validated['report_type']])
            ->latest('id')
            ->first();
        $rejectionType = $lastRejection->metadata['rejection_type'] ?? 'report';
        $isEthicalOrOther = in_array($rejectionType, ['ethical', 'other'], true);

        $project->recordStatus($status, [
            'action' => 'send_to_lpi',
            'admin_message' => $validated['message'],
            'report_type' => $validated['report_type'],
            'rejection_type' => $rejectionType,
            'triggered_by' => 'review-rejection',
        ], $user->id);

        $reportLabel = $validated['report_type'] === 'final' ? 'Final Report'
            : ($validated['report_type'] === 'progress2' ? 'Progress Report 2' : 'Progress Report');

        if ($isEthicalOrOther) {
            $subject = "Ethical Approval Re-send Required";
            $body = "The admin has reviewed the reviewer's rejection of your {$reportLabel} for: {$project->title}\n\n" .
                "Reason ({$rejectionType}): {$validated['message']}\n\n" .
                "Please re-send the ethical approval document for this project. The {$reportLabel} itself does not need to be re-uploaded.";
            $message = "Ethical approval re-send request sent to LPI.";
        } else {
            $subject = "{$reportLabel} Resubmission Required";
            $body = "The admin has reviewed the reviewer's rejection of your {$reportLabel} for: {$project->title}\n\n" .
                "Admin message: {$validated['message']}\n\n" .
                "Please resubmit your {$reportLabel}.";
            $message = "{$reportLabel} sent to LPI for resubmission.";
        }

        $this->sendNotificationToLpi($project, $user, $subject, $body);

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * AJAX: Admin un-assigns a reviewer from a project.
     * Only allowed if reviewer hasn't claimed the project yet.
     */
    public function unassignReviewer(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'reviewer_id' => 'nullable|integer|exists:users,id',
            'reason'      => 'nullable|string|max:2000',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Only administrators can un-assign reviewers.'], 403);
        }

        // Affected assignments: one specific reviewer, or (legacy) all of them.
        $entriesQuery = DB::table('projects_reviewers')->where('project_id', $project->id);
        if (!empty($validated['reviewer_id'])) {
            $entriesQuery->where('user_id', $validated['reviewer_id']);
        }
        $entries = $entriesQuery->get();

        if ($entries->isEmpty()) {
            return response()->json(['error' => 'No reviewer is currently assigned to this project.'], 422);
        }

        // Deliberate decision: the admin MAY also remove a claimed reviewer.
        // A claimed-but-silent reviewer would otherwise wedge the multi-reviewer
        // consensus forever (nobody can complete the stage and he cannot be
        // pulled out). The action is admin-only, asks for a reason, notifies
        // the reviewer and is audited through the reviewer_unassigned status.

        // Get reviewer info(s) before deleting for notification
        $removedUsers = User::whereIn('id', $entries->pluck('user_id'))->get();

        DB::transaction(function () use ($project, $entries) {
            DB::table('projects_reviewers')
                ->whereIn('id', $entries->pluck('id'))
                ->delete();

            // Only step the project back to "unassigned" when nobody is left â€”
            // with another reviewer still assigned the review simply continues.
            $remaining = DB::table('projects_reviewers')
                ->where('project_id', $project->id)
                ->exists();

            if (!$remaining) {
                $project->recordStatus(Project::STATUS_REVIEWER_UNASSIGNED, [
                    'triggered_by' => 'unassign-reviewer',
                    'reviewer_id'  => $entries->first()->user_id,
                ], auth()->id());
            }
        });

        // Consensus re-check: removing this reviewer may leave only reviewers
        // who already submitted â€” complete whatever stage he was blocking
        // (recordStageCompletion is a no-op when nothing changed).
        $grading = app(GradingController::class);
        $grading->recordStageCompletion($project, 'progress', Project::STATUS_PROGRESS_REVIEWED, Project::STATUS_PROGRESS_REJECTED, $user, ['triggered_by' => 'unassign-consensus']);
        $grading->recordStageCompletion($project, 'progress2', Project::STATUS_PROGRESS2_REVIEWED, Project::STATUS_PROGRESS2_REJECTED, $user, ['triggered_by' => 'unassign-consensus']);
        $grading->recordStageCompletion($project, 'final', Project::STATUS_GRADED, Project::STATUS_FINAL_REJECTED, $user, ['triggered_by' => 'unassign-consensus']);

        // Notify the un-assigned reviewer(s)
        foreach ($removedUsers as $removedUser) {
            $this->sendNotificationToReviewers(
                $project,
                $user,
                'Reviewer Un-assignment',
                "You have been un-assigned from project: {$project->title} (ID: {$project->old_project_id}).\n\n" .
                (!empty($validated['reason']) ? "Reason: {$validated['reason']}\n\n" : '') .
                "Please contact the admin if you have any questions."
            );
        }

        return response()->json(['success' => true, 'message' => 'Reviewer un-assigned successfully. You can now assign a new reviewer.']);
    }

    /**
     * Send notification email to all active admins.
     */
    private function sendNotificationToAdmins(Project $project, User $sender, string $subject, string $body): void
    {
        if (!config('mail.enabled')) return;

        $admins = User::where('type', 'Admin')->where('is_active', true)->get();
        $subject = "{$subject} - {$project->title}";

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->queue(new GenericEmailMail(
                    $subject,
                    $body,
                    'Research Tracking System',
                    $admin->name
                ));

                EmailSendLog::create([
                    'sent_by' => $sender->id,
                    'recipient_email' => $admin->email,
                    'recipient_name' => $admin->name,
                    'subject' => $subject,
                    'body' => $body,
                    'status' => 'queued',
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to send notification to admin {$admin->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Send notification email to the project's LPI.
     */
    private function sendNotificationToLpi(Project $project, User $sender, string $subject, string $body): void
    {
        if (!config('mail.enabled')) return;

        $lpi = $project->lpi;
        if (!$lpi) return;

        $subject = "{$subject} - {$project->title}";

        try {
            Mail::to($lpi->email)->queue(new GenericEmailMail(
                $subject,
                $body,
                'Research Tracking System',
                $lpi->name
            ));

            EmailSendLog::create([
                'sent_by' => $sender->id,
                'recipient_email' => $lpi->email,
                'recipient_name' => $lpi->name,
                'subject' => $subject,
                'body' => $body,
                'status' => 'queued',
            ]);
        } catch (\Exception $e) {
            \Log::error("Failed to send notification to LPI {$lpi->email}: " . $e->getMessage());
        }
    }

    /**
     * Send notification email to all reviewers assigned to the project.
     */
    private function sendNotificationToReviewers(Project $project, User $sender, string $subject, string $body): void
    {
        if (!config('mail.enabled')) return;

        $reviewers = $project->reviewers;
        $subject = "{$subject} - {$project->title}";

        foreach ($reviewers as $reviewer) {
            try {
                Mail::to($reviewer->email)->queue(new GenericEmailMail(
                    $subject,
                    $body,
                    'Research Tracking System',
                    $reviewer->name
                ));

                EmailSendLog::create([
                    'sent_by' => $sender->id,
                    'recipient_email' => $reviewer->email,
                    'recipient_name' => $reviewer->name,
                    'subject' => $subject,
                    'body' => $body,
                    'status' => 'queued',
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to send notification to reviewer {$reviewer->email}: " . $e->getMessage());
            }
        }
    }

}
