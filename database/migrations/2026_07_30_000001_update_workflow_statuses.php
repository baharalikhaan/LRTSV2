<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate old status values to new workflow
        // 'progress_add' → 'progress_added'
        DB::table('status_histories')
            ->where('status', Project::STATUS_PROGRESS) // 'progress_add'
            ->update(['status' => Project::STATUS_PROGRESS_ADDED]);

        // 'progress_submitted' → 'progress_reviewed'
        DB::table('status_histories')
            ->where('status', Project::STATUS_PROGRESS_SUBMITTED)
            ->update(['status' => Project::STATUS_PROGRESS_REVIEWED]);

        // 'final_submitted' → 'final_added'
        DB::table('status_histories')
            ->where('status', Project::STATUS_FINAL_SUBMITTED)
            ->update(['status' => Project::STATUS_FINAL_ADDED]);

        // 'rejected' → 'progress_rejected' (only for proposal rejections that
        // should now be a progress_rejection — claim rejections keep 'rejected')
        // We leave 'rejected' as-is since it was used for claim rejections
        // which have a different meaning.
    }

    public function down(): void
    {
        DB::table('status_histories')
            ->where('status', Project::STATUS_PROGRESS_ADDED)
            ->update(['status' => Project::STATUS_PROGRESS]);

        DB::table('status_histories')
            ->where('status', Project::STATUS_PROGRESS_REVIEWED)
            ->update(['status' => Project::STATUS_PROGRESS_SUBMITTED]);

        DB::table('status_histories')
            ->where('status', Project::STATUS_FINAL_ADDED)
            ->update(['status' => Project::STATUS_FINAL_SUBMITTED]);
    }
};
