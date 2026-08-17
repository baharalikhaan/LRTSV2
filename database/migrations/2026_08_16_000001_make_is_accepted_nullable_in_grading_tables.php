<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeIsAcceptedNullableInGradingTables extends Migration
{
    /**
     * The isAccepted columns were created NOT NULL, but a pending/reset grade
     * has no accept/reject decision yet. Make them nullable so resetting a
     * grade to 'pending' (LPI resubmission) no longer violates the constraint.
     *
     * Raw SQL is used because the legacy schema exposes isAccepted as a `bit`
     * column, which Doctrine DBAL cannot introspect for ->change().
     */
    public function up()
    {
        DB::statement('ALTER TABLE progress_report_grading MODIFY isAccepted INT NULL');
        DB::statement('ALTER TABLE final_report_grading MODIFY isAccepted INT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE progress_report_grading MODIFY isAccepted INT NOT NULL');
        DB::statement('ALTER TABLE final_report_grading MODIFY isAccepted INT NOT NULL');
    }
}