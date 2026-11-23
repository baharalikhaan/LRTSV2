<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widen the progress-report reviewer comments so the new 1,500-character
 * limit fits (the textareas used to stop at 500 characters and the columns
 * were VARCHAR(1200)). TEXT removes the ceiling for good; the form still
 * enforces 1,500 characters.
 */
class WidenProgressGradingCommentColumns extends Migration
{
    private array $columns = [
        'achievementsComments',
        'publicationsComments',
        'studentsComments',
        'budgetComments',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('progress_report_grading')) {
            return;
        }

        foreach ($this->columns as $column) {
            if (Schema::hasColumn('progress_report_grading', $column)) {
                DB::statement("ALTER TABLE `progress_report_grading` MODIFY `{$column}` TEXT NULL");
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('progress_report_grading')) {
            return;
        }

        foreach ($this->columns as $column) {
            if (Schema::hasColumn('progress_report_grading', $column)) {
                DB::statement("ALTER TABLE `progress_report_grading` MODIFY `{$column}` VARCHAR(1200) NULL");
            }
        }
    }
}
