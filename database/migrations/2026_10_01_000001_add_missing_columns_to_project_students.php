<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The ProjectStudent model and controllers (progress update, student-grant
 * import, grading verification) rely on the legacy student-grant columns
 * (user_id, type, std_id, days, score) that the baseline create_all_tables
 * migration never created. This adds them idempotently on existing schemas.
 */
class AddMissingColumnsToProjectStudents extends Migration
{
    private array $columns = [
        'user_id' => 'after-project',
        'type'    => 'after-user',
        'std_id'  => 'after-type',
        'days'    => 'after-std',
        'score'   => 'after-days',
    ];

    public function up(): void
    {
        if (Schema::hasTable('project_students')) {
            Schema::table('project_students', function (Blueprint $table) {
                if (!Schema::hasColumn('project_students', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('project_id');
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('project_students', 'type')) {
                    $table->enum('type', ['UG', 'masters', 'PhD'])->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('project_students', 'std_id')) {
                    $table->string('std_id')->nullable()->after('type');
                }
                if (!Schema::hasColumn('project_students', 'days')) {
                    $table->integer('days')->default(0)->after('std_id');
                }
                if (!Schema::hasColumn('project_students', 'score')) {
                    $table->tinyInteger('score')->default(0)->after('days');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_students')) {
            if (Schema::hasColumn('project_students', 'score')) {
                Schema::table('project_students', function (Blueprint $table) {
                    $table->dropColumn('score');
                });
            }
            if (Schema::hasColumn('project_students', 'days')) {
                Schema::table('project_students', function (Blueprint $table) {
                    $table->dropColumn('days');
                });
            }
            if (Schema::hasColumn('project_students', 'std_id')) {
                Schema::table('project_students', function (Blueprint $table) {
                    $table->dropColumn('std_id');
                });
            }
            if (Schema::hasColumn('project_students', 'type')) {
                Schema::table('project_students', function (Blueprint $table) {
                    $table->dropColumn('type');
                });
            }
            if (Schema::hasColumn('project_students', 'user_id')) {
                Schema::table('project_students', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                });
            }
        }
    }
}
