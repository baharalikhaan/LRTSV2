<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeIsAcceptedNullableInGradingTables extends Migration
{
    /**
     * The isAccepted columns were created NOT NULL, but a pending/reset grade
     * has no accept/reject decision yet. Make them nullable so resetting a
     * grade to 'pending' (LPI resubmission) no longer violates the constraint.
     */
    public function up()
    {
        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->integer('isAccepted')->nullable()->change();
        });
        Schema::table('final_report_grading', function (Blueprint $table) {
            $table->integer('isAccepted')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->integer('isAccepted')->nullable(false)->change();
        });
        Schema::table('final_report_grading', function (Blueprint $table) {
            $table->integer('isAccepted')->nullable(false)->change();
        });
    }
}
