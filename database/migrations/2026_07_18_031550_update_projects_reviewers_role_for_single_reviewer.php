<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateProjectsReviewersRoleForSingleReviewer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Change the role ENUM from ['Reviewer1', 'Reviewer2'] to ['Reviewer'] (single-reviewer workflow)
        // First, update any existing Reviewer1/Reviewer2 values to 'Reviewer'
        DB::table('projects_reviewers')
            ->whereIn('role', ['Reviewer1', 'Reviewer2'])
            ->update(['role' => 'Reviewer']);

        // Then modify the column to accept the new value
        Schema::table('projects_reviewers', function (Blueprint $table) {
            $table->string('role', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects_reviewers', function (Blueprint $table) {
            $table->enum('role', ['Reviewer1', 'Reviewer2'])->nullable()->change();
        });
    }
}
