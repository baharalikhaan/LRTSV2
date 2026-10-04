<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('final_report_grading', function (Blueprint $table) {
            $table->decimal('scoreA', 8, 2)->default(0)->after('gradeA');
            $table->decimal('scoreB', 8, 2)->default(0)->after('gradeB');
            $table->decimal('scoreC', 8, 2)->default(0)->after('gradeC');
            $table->decimal('autoGradeA', 5, 2)->default(0)->after('scoreA');
            $table->decimal('autoGradeB', 5, 2)->default(0)->after('scoreB');
            $table->decimal('autoGradeC', 5, 2)->default(0)->after('scoreC');
        });
    }

    public function down(): void
    {
        Schema::table('final_report_grading', function (Blueprint $table) {
            $table->dropColumn(['scoreA', 'scoreB', 'scoreC', 'autoGradeA', 'autoGradeB', 'autoGradeC']);
        });
    }
};
