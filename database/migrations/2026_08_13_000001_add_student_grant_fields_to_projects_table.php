<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add student grant fields to projects table
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'grant_type')) {
                $table->string('grant_type')->nullable()->after('final_rsd_decision');
            }
            if (!Schema::hasColumn('projects', 'student_engagement')) {
                $table->text('student_engagement')->nullable()->after('grant_type');
            }
            if (!Schema::hasColumn('projects', 'publications')) {
                $table->text('publications')->nullable()->after('student_engagement');
            }
            if (!Schema::hasColumn('projects', 'spending')) {
                $table->decimal('spending', 12, 2)->nullable()->after('publications');
            }
            if (!Schema::hasColumn('projects', 'spending_detail')) {
                $table->text('spending_detail')->nullable()->after('spending');
            }
        });

        // Add nationality to project_students table
        Schema::table('project_students', function (Blueprint $table) {
            if (!Schema::hasColumn('project_students', 'nationality')) {
                $table->string('nationality')->nullable()->after('std_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'grant_type',
                'student_engagement',
                'publications',
                'spending',
                'spending_detail',
            ]);
        });

        Schema::table('project_students', function (Blueprint $table) {
            $table->dropColumn('nationality');
        });
    }
};
