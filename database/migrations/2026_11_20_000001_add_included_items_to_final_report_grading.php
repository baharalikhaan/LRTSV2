<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remember which outcomes/students/researchers the reviewer kept "active" in
 * the auto-grade of a final report.
 *
 * The four checkbox groups (achievement_outcomes[], publication_outcomes[],
 * student_items[], researcher_items[]) only drove the score calculation in the
 * browser: the selection was never sent anywhere, so a saved draft reopened
 * with every item back at its verification-based default. The selection is now
 * stored as one JSON document per grading row:
 *
 *   {"achievements":[1,4],"publications":[9],"students":[],"researchers":[2]}
 */
class AddIncludedItemsToFinalReportGrading extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('final_report_grading')) {
            return;
        }

        Schema::table('final_report_grading', function (Blueprint $table) {
            if (!Schema::hasColumn('final_report_grading', 'included_items')) {
                $table->json('included_items')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('final_report_grading')) {
            return;
        }

        Schema::table('final_report_grading', function (Blueprint $table) {
            if (Schema::hasColumn('final_report_grading', 'included_items')) {
                $table->dropColumn('included_items');
            }
        });
    }
}
