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
        $progress2Grading = \App\Models\ProgressReportGrading::where('project_id', $projectId)
            ->where('report_type', 'progress2')->first();
        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();
        $researchers = $project->researchers()->get();

        return view('workflow.modals.report-card', compact(
            'project', 'commitment', 'finalGrading', 'progressGrading', 'progress2Grading',
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
                $progress2Grading = \App\Models\ProgressReportGrading::where('project_id', $projectId)
                    ->where('report_type', 'progress2')->first();
                $outcomes = $project->outcomes()->get();
                $students = $project->students()->get();
                $researchers = $project->researchers()->get();

                $html = view('workflow.modals.report-card', compact(
                    'project', 'commitment', 'finalGrading', 'progressGrading', 'progress2Grading',
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
            'report_type' => 'nullable|in:progress,progress2',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        $user = auth()->user();

        $isProgress2 = ($validated['report_type'] ?? 'progress') === 'progress2';

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
            if (!$project->hasStatus($isProgress2 ? Project::STATUS_PROGRESS2_REVIEWED : Project::STATUS_PROGRESS_REVIEWED)) {
                $project->recordStatus($isProgress2 ? Project::STATUS_PROGRESS2_REVIEWED : Project::STATUS_PROGRESS_REVIEWED, [
                    'triggered_by' => 'progress-review-accept',
                    'report_type'  => $validated['report_type'] ?? 'progress',
                ], $user->id);
            }

            return response()->json([
                'success' => true,
                'message' => $isProgress2
                    ? 'Progress Report 2 approved. The LPI can now submit the final report.'
                    : 'Progress report approved. The LPI can now submit the final report.',
            ]);
        }

        // Rejected — keep reviewer assigned and record progress_rejected
        $project->recordStatus($isProgress2 ? Project::STATUS_PROGRESS2_REJECTED : Project::STATUS_PROGRESS_REJECTED, [
            'triggered_by' => 'progress-review-reject',
            'comment'      => $validated['comment'] ?? null,
            'report_type'  => $validated['report_type'] ?? 'progress',
        ], $user->id);

        return response()->json([
            'success' => true,
            'message' => $isProgress2
                ? 'Progress Report 2 rejected. The admin will review and send it back to the LPI.'
                : 'Progress report rejected. The admin will review and send it back to the LPI.',
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

        if ($validated['report_type'] === 'final') {
            $status = Project::STATUS_FINAL_REJ_REVIEWED;
        } elseif ($validated['report_type'] === 'progress2') {
            $status = Project::STATUS_PROGRESS2_REJ_REVIEWED;
        } else {
            $status = Project::STATUS_PROGRESS_REJ_REVIEWED;
        }

        $project->recordStatus($status, [
            'action' => 'send_to_lpi',
            'admin_message' => $validated['message'],
            'report_type' => $validated['report_type'],
            'triggered_by' => 'review-rejection',
        ], $user->id);

        $reportLabel = $validated['report_type'] === 'final' ? 'Final Report'
            : ($validated['report_type'] === 'progress2' ? 'Progress Report 2' : 'Progress Report');

        $this->sendNotificationToLpi(
            $project,
            $user,
            "{$reportLabel} Resubmission Required",
            "The admin has reviewed the reviewer's rejection of your {$reportLabel} for: {$project->title}\n\n" .
            "Admin message: {$validated['message']}\n\n" .
            "Please resubmit your {$reportLabel}."
        );

        return response()->json(['success' => true, 'message' => "{$reportLabel} sent to LPI for resubmission."]);
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
