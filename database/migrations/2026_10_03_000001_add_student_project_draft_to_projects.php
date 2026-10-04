<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student-grant progress form lock. Mirrors the legacy `student_project_draft`
 * column ('save' = the LPI submitted the one-shot student form and it is
 * permanently locked).
 */
class AddStudentProjectDraftToProjects extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects') && !Schema::hasColumn('projects', 'student_project_draft')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('student_project_draft', 20)->nullable()->after('final_rsd_decision');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'student_project_draft')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('student_project_draft');
            });
        }
    }
}
