<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportSubmissionStateAndReviewerRejections extends Migration
{
    public function up()
    {
        // Track per-report submission state (draft vs submitted) + when submitted.
        // Idempotent: skip if the column already exists (e.g. from a partial run).
        if (!Schema::hasColumn('project_submissions', 'submitted')) {
            Schema::table('project_submissions', function (Blueprint $table) {
                $table->boolean('submitted')->default(false)->after('version');
                $table->timestamp('submitted_at')->nullable()->after('submitted');
            });
        }
        if (!Schema::hasIndex('project_submissions', 'project_submissions_project_id_type_submitted_index')) {
            Schema::table('project_submissions', function (Blueprint $table) {
                $table->index(['project_id', 'type', 'submitted']);
            });
        }

        // Audit trail of reviewer proposal rejections — used to prevent re-assigning
        // the same reviewer to a project they previously rejected.
        if (!Schema::hasTable('reviewer_rejections')) {
            Schema::create('reviewer_rejections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('reason')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['project_id', 'user_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('reviewer_rejections');

        Schema::table('project_submissions', function (Blueprint $table) {
            if (Schema::hasIndex('project_submissions', 'project_submissions_project_id_type_submitted_index')) {
                $table->dropIndex(['project_id', 'type', 'submitted']);
            }
            if (Schema::hasColumn('project_submissions', 'submitted')) {
                $table->dropColumn(['submitted', 'submitted_at']);
            }
        });
    }
}