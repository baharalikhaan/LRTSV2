<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reviewer_rejections') || !Schema::hasTable('status_histories')) {
            return;
        }

        // Get all rejected records that don't have a reviewer_rejection entry yet
        $rejectedRecords = DB::table('status_histories')
            ->where('status', 'progress_rejected')
            ->orWhere('status', 'proposal_rejected')
            ->whereNotNull('user_id')
            ->select('project_id', 'user_id', 'metadata', 'created_at')
            ->get();

        // Group by project_id + user_id to avoid duplicates
        $grouped = $rejectedRecords->groupBy(function ($item) {
            return $item->project_id . '_' . $item->user_id;
        });

        foreach ($grouped as $key => $records) {
            $first = $records->first();
            
            // Skip if already exists in reviewer_rejections
            $exists = DB::table('reviewer_rejections')
                ->where('project_id', $first->project_id)
                ->where('user_id', $first->user_id)
                ->exists();
            
            if (!$exists) {
                DB::table('reviewer_rejections')->insert([
                    'project_id' => $first->project_id,
                    'user_id'    => $first->user_id,
                    'reason'     => is_array($first->metadata) ? ($first->metadata['comment'] ?? null) : null,
                    'created_at' => $first->created_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        // No rollback needed — backfill is idempotent
    }
};
