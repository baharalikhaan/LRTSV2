<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSubpillarToPillarsTable extends Migration
{
    public function up()
    {
        // Step 1: Add subpillar column (skip if already exists)
        if (!Schema::hasColumn('pillars', 'subpillar')) {
            Schema::table('pillars', function (Blueprint $table) {
                $table->text('subpillar')->nullable()->after('description');
            });
        }

        // Step 2: Consolidate data — for each distinct pillar name, keep the first row,
        // gather duplicate IDs, then update pivot FK references and delete duplicates.
        // Since all descriptions are null and we don't have real subpillar data,
        // we just deduplicate by pillar name and flag them as needing subpillars.

        $pillars = DB::table('pillars')->select('id', 'pillar', 'description')->orderBy('id')->get();
        $grouped = $pillars->groupBy('pillar');

        foreach ($grouped as $pillarName => $items) {
            if ($items->count() <= 1) {
                continue; // already unique
            }

            $first = $items->first();
            $keepId = $first->id;
            $deleteIds = $items->pluck('id')->slice(1)->values()->toArray();

            // Mark first row with subpillar flag
            DB::table('pillars')->where('id', $keepId)->update([
                'subpillar' => 'needs update', // placeholder - user can edit later
            ]);

            // Update user_pillars FK references
            DB::table('user_pillars')->whereIn('pillar_id', $deleteIds)->update(['pillar_id' => $keepId]);

            // Update project_pillar FK references
            DB::table('project_pillar')->whereIn('pillar_id', $deleteIds)->update(['pillar_id' => $keepId]);

            // Delete duplicate rows
            DB::table('pillars')->whereIn('id', $deleteIds)->delete();
        }
    }

    public function down()
    {
        Schema::table('pillars', function (Blueprint $table) {
            $table->dropColumn('subpillar');
        });
    }
}
