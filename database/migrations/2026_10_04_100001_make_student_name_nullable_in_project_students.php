<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * project_students.student_name was created NOT NULL (legacy shape) but the
 * student-adding flows write only the legacy fields (type, std_id, days,
 * score) — the name is intended to arrive later from the QU SIS API. Make it
 * nullable so rows can be created offline and backfilled by the API later
 * (or shown as "-" in the UI).
 */
class MakeStudentNameNullableInProjectStudents extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_students') && Schema::hasColumn('project_students', 'student_name')) {
            Schema::table('project_students', function (Blueprint $table) {
                $table->string('student_name')->nullable()->default(null)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_students') && Schema::hasColumn('project_students', 'student_name')) {
            // Only revert if there are no NULL rows left
            $hasNulls = DB::table('project_students')->whereNull('student_name')->exists();
            if ($hasNulls) {
                return; // cannot restore NOT NULL without data loss — skip
            }
            Schema::table('project_students', function (Blueprint $table) {
                $table->string('student_name')->nullable(false)->default('')->change();
            });
        }
    }
}
