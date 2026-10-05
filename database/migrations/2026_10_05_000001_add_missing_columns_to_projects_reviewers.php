<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The baseline create_all_tables migration declares the projects_reviewers
 * pivot with role / proposalstatus / statusdate columns, but it is guarded by
 * "if (!Schema::hasTable(...))" so an older live table that predates those
 * columns never received them. The assign-reviewer flow (and the proposal
 * accept/reject + grading lookups) then fail with "Unknown column 'role'".
 *
 * This adds the missing columns idempotently on existing schemas.
 */
class AddMissingColumnsToProjectsReviewers extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('projects_reviewers')) {
            return;
        }

        Schema::table('projects_reviewers', function (Blueprint $table) {
            if (!Schema::hasColumn('projects_reviewers', 'role')) {
                $table->string('role', 20)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('projects_reviewers', 'proposalstatus')) {
                $table->string('proposalstatus', 50)->default('0')->after('role');
            }
            if (!Schema::hasColumn('projects_reviewers', 'statusdate')) {
                $table->date('statusdate')->nullable()->after('proposalstatus');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('projects_reviewers')) {
            return;
        }

        Schema::table('projects_reviewers', function (Blueprint $table) {
            foreach (['statusdate', 'proposalstatus', 'role'] as $column) {
                if (Schema::hasColumn('projects_reviewers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
