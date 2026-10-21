<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-reviewer consensus grading.
 *
 * With consensus, a reviewer may reject before the stage completes — at that
 * point NO workflow status is recorded yet (the stage stays open for the
 * remaining reviewers), so the old "rejection metadata lives on the status
 * history row" approach would lose the reason/type. Persist them on the
 * grading row instead; at consensus completion they are copied onto the
 * *_REJECTED status for the admin rejection-review flow.
 */
class AddRejectionFieldsToGradings extends Migration
{
    public function up(): void
    {
        foreach (['progress_report_grading', 'final_report_grading'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'rejection_type')) {
                    $t->string('rejection_type', 20)->nullable();
                }
                if (!Schema::hasColumn($table, 'rejection_reason')) {
                    $t->string('rejection_reason', 2000)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['progress_report_grading', 'final_report_grading'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                foreach (['rejection_reason', 'rejection_type'] as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $t->dropColumn($column);
                    }
                }
            });
        }
    }
}
