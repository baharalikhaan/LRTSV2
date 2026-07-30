<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'program', 'grant', 'lpi', 'latestStatus',
        ])->findOrFail($projectId);
        return view('workflow.modals.report-card', compact('project'));
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
                $html = view('workflow.modals.report-card', compact('project'))->render();
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
            'progress'   => Project::STATUS_PROGRESS,
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
            'progress'        => Project::STATUS_PROGRESS,
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

            // Record the Assigned status if not already set
            if (!$project->hasStatus(Project::STATUS_ASSIGNED)) {
                $project->recordStatus(Project::STATUS_ASSIGNED, ['triggered_by' => 'assign'], auth()->id());
            }
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
            \App\Models\ReviewerRejection::create([
                'project_id' => $project->id,
                'user_id'    => $user->id,
                'reason'     => $validated['reject_reason'] ?? null,
            ]);

            // 2. Clear the assignment (back to admin queue)
            DB::table('projects_reviewers')
                ->where('id', $validated['r_id'])
                ->delete();

            // 3. Record the rejection status in the workflow history
            if (!$project->hasStatus(Project::STATUS_REJECTED)) {
                $project->recordStatus(Project::STATUS_REJECTED, [
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
}
