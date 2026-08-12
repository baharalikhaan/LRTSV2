<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\EmailSendLog;
use App\Mail\GenericEmailMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class WorkflowController extends Controller
{
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
        $finalGrading = \App\Models\FinalReportGrading::where('project_id', $projectId)->first();
        $progressGrading = \App\Models\ProgressReportGrading::where('project_id', $projectId)->first();
        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();
        $researchers = $project->researchers()->get();

        return view('workflow.modals.report-card', compact(
            'project', 'commitment', 'finalGrading', 'progressGrading',
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
                $finalGrading = \App\Models\FinalReportGrading::where('project_id', $projectId)->first();
                $progressGrading = \App\Models\ProgressReportGrading::where('project_id', $projectId)->first();
                $outcomes = $project->outcomes()->get();
                $students = $project->students()->get();
                $researchers = $project->researchers()->get();

                $html = view('workflow.modals.report-card', compact(
                    'project', 'commitment', 'finalGrading', 'progressGrading',
                    'outcomes', 'students', 'researchers'
                ))->render();
                break;

            case 'approve-extended-progress':
                $html = view('workflow.modals.approve-extended', compact('project'))->render();
                break;

            case 'review-rejection':
                $reportType = request()->query('report_type', 'progress');
                $html = view('workflow.modals.review-rejection', compact('project', 'reportType'))->render();
                break;

            case 'review-ext-rejection':
                $reportType = 'extended_progress';
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

        // Block if program is inactive
        if (!$project->programIsActive()) {
            return response()->json(['error' => 'This program is no longer active. Projects under this program cannot be manipulated.'], 422);
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

        // Block if program is inactive
        if (!$project->programIsActive()) {
            return redirect($validated['redirect'] ?? route('projects.available'))
                ->with('error', 'This program is no longer active. Projects under this program cannot be manipulated.');
        }

        $statusMap = [
            'register'        => Project::STATUS_REGISTERED,
            'progress'        => Project::STATUS_PROGRESS_ADDED,
            'assign'          => Project::STATUS_ASSIGNED,
        ];

        $status = $statusMap[$validated['action']] ?? null;
        if ($status) {
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
            'reviewer_ids' => 'required|array|min:1|max:1',
            'reviewer_ids.*' => 'exists:users,id',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        // Block if program is inactive
        if (!$project->programIsActive()) {
            return response()->json(['error' => 'This program is no longer active. Projects under this program cannot be manipulated.'], 422);
        }

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

        DB::transaction(function () use ($project, $reviewerIds) {
            // Remove all existing reviewers for this project
            DB::table('projects_reviewers')->where('project_id', $project->id)->delete();

            // Assign the single reviewer
            foreach ($reviewerIds as $reviewerId) {
                DB::table('projects_reviewers')->insert([
                    'project_id' => $project->id,
                    'user_id'    => $reviewerId,
                    'role'       => 'Reviewer',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Always record Assigned status when a reviewer is assigned
            $project->recordStatus(Project::STATUS_ASSIGNED, [
                'triggered_by' => 'assign',
                'reviewer_id'  => $reviewerIds[0] ?? null,
            ], auth()->id());
        });

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

        // Block if program is inactive
        if (!$project->programIsActive()) {
            return response()->json(['error' => 'This program is no longer active. Actions cannot be taken on projects under this program.'], 422);
        }

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

        // ─── ACCEPTED: keep the assignment and record Claimed ────────────
        if ($validated['accept'] === 'accepted') {
            DB::table('projects_reviewers')
                ->where('id', $validated['r_id'])
                ->update([
                    'proposalstatus' => 'accepted',
                    'statusdate'     => now(),
                ]);

            if (!$project->hasStatus(Project::STATUS_CLAIMED)) {
                $project->recordStatus(Project::STATUS_CLAIMED, [
                    'triggered_by'  => 'claim',
                    'reviewer_role' => $reviewerRecord->role,
                ], $user->id);
            }

            return response()->json(['success' => true, 'message' => 'Proposal accepted successfully.']);
        }

        // ─── REJECTED: audit trail + un-assign + record Rejected ─────────
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

            // 3. Record the proposal rejection status (NOT progress_rejected)
            if (!$project->hasStatus(Project::STATUS_PROPOSAL_REJECTED)) {
                $project->recordStatus(Project::STATUS_PROPOSAL_REJECTED, [
                    'triggered_by' => 'reject-proposal',
                    'rejected_by'  => $user->id,
                    'reason'       => $validated['reject_reason'] ?? null,
                ], $user->id);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Proposal rejected. The project has been returned to the admin queue for reassignment.',
        ]);
    }

    /**
     * AJAX: Submit reviewer decision on progress report (accept or reject).
     *
     * If accepted → records progress_reviewed.
     * If rejected → records progress_rejected (LPI resubmits, reviewer re-reviews).
     * The reviewer stays assigned in both cases.
     */
    public function submitProgressReview(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'accept'     => 'required|in:accepted,rejected',
            'comment'    => 'nullable|string|max:2000',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if (!$project->programIsActive()) {
            return response()->json(['error' => 'This program is no longer active.'], 422);
        }

        $user = auth()->user();

        // Verify this user is assigned as a reviewer
        $reviewerRecord = DB::table('projects_reviewers')
            ->where('project_id', $validated['project_id'])
            ->where('user_id', $user->id)
            ->where('proposalstatus', 'accepted')
            ->first();

        if (!$reviewerRecord) {
            return response()->json(['error' => 'You are not authorized to review this project.'], 403);
        }

        if ($validated['accept'] === 'accepted') {
            if (!$project->hasStatus(Project::STATUS_PROGRESS_REVIEWED)) {
                $project->recordStatus(Project::STATUS_PROGRESS_REVIEWED, [
                    'triggered_by' => 'progress-review-accept',
                ], $user->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Progress report approved. The LPI can now submit the final report.',
            ]);
        }

        // Rejected — remove reviewer and record progress_rejected
        DB::transaction(function () use ($project, $user, $validated) {
            // 1. Remove the reviewer from projects_reviewers
            DB::table('projects_reviewers')
                ->where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->delete();

            // 2. Record rejection in reviewer_rejections table
            if (\Illuminate\Support\Facades\Schema::hasTable('reviewer_rejections')) {
                \App\Models\ReviewerRejection::create([
                    'project_id' => $project->id,
                    'user_id'    => $user->id,
                    'reason'     => $validated['comment'] ?? null,
                ]);
            }

            // 3. Record progress_rejected status
            if (!$project->hasStatus(Project::STATUS_PROGRESS_REJECTED)) {
                $project->recordStatus(Project::STATUS_PROGRESS_REJECTED, [
                    'triggered_by' => 'progress-review-reject',
                    'comment'      => $validated['comment'] ?? null,
                ], $user->id);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Progress report rejected. The project has been returned to the admin queue for reassignment.',
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

        $html = view('workflow.modals.view-grade', compact('project'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    /**
     * Toggle extended progress report for a project.
     * Admin can enable/disable extended progress submission.
     */
    public function toggleExtendedProgress(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = auth()->user();

        // Only Admin can toggle
        if (!$user->isAdmin()) {
            return redirect()->back()->with('error', 'Only administrators can enable extended progress reports.');
        }

        $enable = $request->input('enable') === '1';

        $project->update(['is_extended' => $enable]);

        $status = $enable ? 'enabled' : 'disabled';
        return redirect()->back()->with('success', "Extended progress report {$status} for this project.");
    }

    /**
     * AJAX: LPI requests to upload extended progress report.
     * Records the request status and notifies admins.
     */
    public function requestExtendedProgress(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = auth()->user();

        if (!$project->programIsActive()) {
            return response()->json(['error' => 'This program is no longer active.'], 422);
        }

        // Only LPI can request
        if ($user->activeRole() !== 'LPI' && !$user->isAdmin()) {
            return response()->json(['error' => 'Only LPI can request extended progress.'], 403);
        }

        // Must have progress reviewed
        if (!$project->hasStatus(Project::STATUS_PROGRESS_REVIEWED)) {
            return response()->json(['error' => 'Progress report must be reviewed first.'], 422);
        }

        // Record the request
        $project->recordStatus(Project::STATUS_EXT_PROGRESS_REQUESTED, [
            'triggered_by' => 'request-extended-progress',
        ], $user->id);

        // Send notification to admins
        $this->sendNotificationToAdmins(
            $project,
            $user,
            'Extended Progress Request',
            "LPI {$user->name} has requested to upload an extended progress report for project: {$project->title} (ID: {$project->old_project_id}).\n\nPlease review and approve/reject this request."
        );

        return response()->json([
            'success' => true,
            'message' => 'Extended progress request submitted. Waiting for admin approval.'
        ]);
    }

    /**
     * AJAX: Admin approves or rejects extended progress request.
     */
    public function approveExtendedProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'approve' => 'required|in:approved,rejected',
            'message' => 'nullable|string|max:2000',
        ]);

        $project = Project::findOrFail($id);
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Only administrators can approve.'], 403);
        }

        if ($validated['approve'] === 'approved') {
            $project->update(['is_extended' => true]);
            $project->recordStatus(Project::STATUS_EXT_PROGRESS_APPROVED, [
                'triggered_by' => 'approve-extended-progress',
                'admin_message' => $validated['message'] ?? null,
            ], $user->id);

            // Notify LPI
            $lpiMessage = "Your request to upload an extended progress report for project: {$project->title} has been approved by {$user->name}.";
            if ($validated['message'] ?? null) {
                $lpiMessage .= "\n\nAdmin message: {$validated['message']}";
            }
            $lpiMessage .= "\n\nYou can now upload the extended progress report.";
            $this->sendNotificationToLpi($project, $user, 'Extended Progress Request Approved', $lpiMessage);

            return response()->json(['success' => true, 'message' => 'Extended progress approved. LPI can now upload.']);
        } else {
            // Record rejection of the request
            $project->recordStatus(Project::STATUS_EXT_PROGRESS_REQUEST_REJECTED, [
                'triggered_by' => 'reject-extended-progress',
                'admin_message' => $validated['message'] ?? null,
            ], $user->id);

            // Notify LPI
            $lpiMessage = "Your request to upload an extended progress report for project: {$project->title} has been rejected by {$user->name}.";
            if ($validated['message'] ?? null) {
                $lpiMessage .= "\n\nAdmin message: {$validated['message']}";
            }
            $this->sendNotificationToLpi($project, $user, 'Extended Progress Request Rejected', $lpiMessage);

            return response()->json(['success' => true, 'message' => 'Extended progress request rejected.']);
        }
    }

    /**
     * AJAX: Admin reviews progress rejection.
     * Can either send to LPI for resubmission or override rejection.
     */
    public function reviewProgressRejection(Request $request, $id)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'action' => 'required|in:send_to_lpi,override',
            'message' => 'required|string|max:2000',
            'report_type' => 'required|in:progress,extended_progress',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Only administrators can review rejections.'], 403);
        }

        $statusMap = [
            'progress' => Project::STATUS_PROGRESS_REJ_REVIEWED,
            'extended_progress' => Project::STATUS_EXT_PROGRESS_REJ_REVIEWED,
        ];

        $status = $statusMap[$validated['report_type']];

        $project->recordStatus($status, [
            'action' => $validated['action'], // 'send_to_lpi' or 'override'
            'admin_message' => $validated['message'],
            'triggered_by' => 'review-rejection',
        ], $user->id);

        $reportLabel = $validated['report_type'] === 'extended_progress' ? 'Extended Progress Report' : 'Progress Report';

        if ($validated['action'] === 'send_to_lpi') {
            // Notify LPI to resubmit
            $this->sendNotificationToLpi(
                $project,
                $user,
                "{$reportLabel} Resubmission Required",
                "The admin has reviewed the reviewer's rejection of your {$reportLabel} for: {$project->title}\n\n" .
                "Admin message: {$validated['message']}\n\n" .
                "Please resubmit your {$reportLabel}."
            );

            return response()->json(['success' => true, 'message' => "{$reportLabel} sent to LPI for resubmission."]);
        } else {
            // Override — notify reviewer to grade existing
            $this->sendNotificationToReviewers(
                $project,
                $user,
                "{$reportLabel} Rejection Overridden",
                "The admin has reviewed your rejection of the {$reportLabel} for: {$project->title}\n\n" .
                "Admin decision: Please grade the existing report.\n" .
                "Admin message: {$validated['message']}\n\n" .
                "Please proceed with grading the previously uploaded report."
            );

            return response()->json(['success' => true, 'message' => "Rejection overridden. Reviewer will grade existing {$reportLabel}."]);
        }
    }

    /**
     * AJAX: Admin un-assigns a reviewer from a project.
     * Only allowed if reviewer hasn't claimed the project yet.
     */
    public function unassignReviewer(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'reason'     => 'nullable|string|max:2000',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Only administrators can un-assign reviewers.'], 403);
        }

        if (!$project->programIsActive()) {
            return response()->json(['error' => 'This program is no longer active.'], 422);
        }

        // Check if reviewer exists
        $hasReviewerEntry = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->exists();

        if (!$hasReviewerEntry) {
            return response()->json(['error' => 'No reviewer is currently assigned to this project.'], 422);
        }

        // Check if reviewer has claimed
        $hasClaimed = $project->hasStatus(Project::STATUS_CLAIMED);
        if ($hasClaimed) {
            return response()->json(['error' => 'Cannot un-assign. The reviewer has already claimed this project.'], 422);
        }

        // Get reviewer info before deleting for notification
        $reviewer = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->first();
        $reviewerUser = User::find($reviewer->user_id ?? null);

        DB::transaction(function () use ($project, $reviewer) {
            // Remove the reviewer assignment
            DB::table('projects_reviewers')
                ->where('project_id', $project->id)
                ->delete();

            // Record the un-assign status
            $project->recordStatus(Project::STATUS_REVIEWER_UNASSIGNED, [
                'triggered_by' => 'unassign-reviewer',
                'reviewer_id'  => $reviewer->user_id ?? null,
            ], auth()->id());
        });

        // Notify the un-assigned reviewer
        if ($reviewerUser) {
            $this->sendNotificationToReviewers(
                $project,
                $user,
                'Reviewer Un-assignment',
                "You have been un-assigned from project: {$project->title} (ID: {$project->old_project_id}).\n\n" .
                ($validated['reason'] ? "Reason: {$validated['reason']}\n\n" : '') .
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
