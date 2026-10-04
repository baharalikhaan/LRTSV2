<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Archive the previous submitted grading when the LPI uploads a new
     * report version. The archived copy powers the read-only "Previous
     * Grading" panel shown under the superseded PDF on the grading page.
     */
    public function up(): void
    {
        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->json('previous_grading')->nullable()->after('report_type');
        });

        Schema::table('final_report_grading', function (Blueprint $table) {
            $table->json('previous_grading')->nullable()->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->dropColumn('previous_grading');
        });

        Schema::table('final_report_grading', function (Blueprint $table) {
            $table->dropColumn('previous_grading');
        });
    }
};
