<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddReportSubmissionStateAndReviewerRejections extends Migration
{
    /**
     * Check whether an index exists on a table (Laravel 8 has no Schema::hasIndex).
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select('SHOW INDEX FROM `' . $table . '`');
            foreach ($indexes as $index) {
                if (($index->Key_name ?? $index->key_name) === $indexName) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // Table may not exist yet — treat as no index.
        }

        return false;
    }

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
        if (!Schema::hasTable('project_submissions')
            || !$this->indexExists('project_submissions', 'project_submissions_project_id_type_submitted_index')) {
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
            if (Schema::hasColumn('project_submissions', 'submitted')) {
                $table->dropColumn(['submitted', 'submitted_at']);
            }
        });

        if (Schema::hasTable('project_submissions')
            && $this->indexExists('project_submissions', 'project_submissions_project_id_type_submitted_index')) {
            Schema::table('project_submissions', function (Blueprint $table) {
                $table->dropIndex('project_submissions_project_id_type_submitted_index');
            });
        }
    }
}
