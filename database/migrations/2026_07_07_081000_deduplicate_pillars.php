<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DeduplicatePillars extends Migration
{
    /**
     * Run the migrations.
     *
     * The subpillar column already exists (from a previous partial migration).
     * This migration consolidates duplicate pillar names by:
     * 1. Merging subpillar text from duplicates into the first row.
     * 2. Updating FK references in pivot tables.
     * 3. Deleting duplicate rows.
     */
    public function up(): void
    {
        $pillars = DB::table('pillars')
            ->select('id', 'pillar', 'subpillar', 'description')
            ->orderBy('id')
            ->get();

        $grouped = $pillars->groupBy('pillar');

        foreach ($grouped as $pillarName => $items) {
            if ($items->count() <= 1) {
                continue;
            }

            $first = $items->first();
            $keepId = $first->id;
            $deleteIds = $items->pluck('id')->slice(1)->values()->toArray();

            // Collect all unique subpillar values, starting with the first row
            $allSubpillars = collect();
            foreach ($items as $item) {
                if (!is_null($item->subpillar) && trim($item->subpillar) !== '') {
                    $allSubpillars->push(trim($item->subpillar));
                }
            }
            $uniqueSubpillars = $allSubpillars->unique()->values()->toArray();

            // Update the kept row with merged subpillars
            if (!empty($uniqueSubpillars)) {
                DB::table('pillars')
                    ->where('id', $keepId)
                    ->update(['subpillar' => implode("\n", $uniqueSubpillars)]);
            }

            // Update pivot table FK references
            DB::table('user_pillars')
                ->whereIn('pillar_id', $deleteIds)
                ->update(['pillar_id' => $keepId]);

            DB::table('project_pillar')
                ->whereIn('pillar_id', $deleteIds)
                ->update(['pillar_id' => $keepId]);

            // Delete duplicate rows
            DB::table('pillars')
                ->whereIn('id', $deleteIds)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot easily undo deduplication; data is lost.
    }
}
