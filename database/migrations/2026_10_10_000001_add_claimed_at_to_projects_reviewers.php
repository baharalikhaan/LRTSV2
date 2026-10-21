<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-reviewer grading support.
 *
 * The pivot previously had no per-reviewer claim state — claiming was inferred
 * from the project-level STATUS_CLAIMED history row, which cannot distinguish
 * between reviewers once a project can have several assigned at once.
 * `claimed_at` records when THIS reviewer accepted the proposal, so each
 * reviewer claims for himself before his own grading form unlocks.
 *
 * Backfill: existing assignments whose project has moved beyond the pre-claim
 * states (i.e. the single legacy reviewer already claimed it) get
 * claimed_at = created_at.
 */
class AddClaimedAtToProjectsReviewers extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('projects_reviewers') || Schema::hasColumn('projects_reviewers', 'claimed_at')) {
            return;
        }

        Schema::table('projects_reviewers', function (Blueprint $table) {
            $table->timestamp('claimed_at')->nullable()->after('statusdate');
        });

        // Project states that mean "not claimed yet" (pre-claim queue states).
        $preClaim = [
            \App\Models\Project::STATUS_IMPORTED,
            \App\Models\Project::STATUS_REGISTERED,
            \App\Models\Project::STATUS_ASSIGNED,
            \App\Models\Project::STATUS_PROPOSAL_REJECTED,
            \App\Models\Project::STATUS_REVIEWER_UNASSIGNED,
        ];

        // Latest status per project (id order = chronological).
        $latestStatus = [];
        DB::table('status_histories')
            ->select('project_id', 'status')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$latestStatus) {
                foreach ($rows as $row) {
                    $latestStatus[$row->project_id] = $row->status;
                }
            });

        DB::table('projects_reviewers')
            ->select('id', 'project_id', 'created_at')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($latestStatus, $preClaim) {
                foreach ($rows as $row) {
                    $status = $latestStatus[$row->project_id] ?? null;
                    if ($status !== null && !in_array($status, $preClaim, true)) {
                        DB::table('projects_reviewers')
                            ->where('id', $row->id)
                            ->update(['claimed_at' => $row->created_at ?? now()]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('projects_reviewers') || !Schema::hasColumn('projects_reviewers', 'claimed_at')) {
            return;
        }

        Schema::table('projects_reviewers', function (Blueprint $table) {
            $table->dropColumn('claimed_at');
        });
    }
}
