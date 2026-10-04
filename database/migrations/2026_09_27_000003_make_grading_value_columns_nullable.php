<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The archived-grading reset (archiveAndResetGrading) nulls out the value
     * columns so the reviewer's form re-opens blank after an LPI resubmission.
     * These columns were created NOT NULL (scoreA default 0, ratings default 1,
     * ethical default -1), which rejected the reset UPDATE.
     */
    public function up(): void
    {
        Schema::table('final_report_grading', function (Blueprint $table) {
            $table->decimal('scoreA', 8, 2)->nullable()->default(null)->change();
            $table->decimal('scoreB', 8, 2)->nullable()->default(null)->change();
            $table->decimal('scoreC', 8, 2)->nullable()->default(null)->change();
        });

        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->integer('achievementsRating')->nullable()->default(null)->change();
            $table->integer('publicationsRating')->nullable()->default(null)->change();
            $table->integer('studentsRating')->nullable()->default(null)->change();
            $table->integer('ethical')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Data-bearing schema change - restoring NOT NULL would fail on rows
        // that legitimately hold nulls now.
    }
};
