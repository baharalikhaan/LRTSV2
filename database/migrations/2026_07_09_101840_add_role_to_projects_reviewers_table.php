<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleToProjectsReviewersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects_reviewers', function (Blueprint $table) {
            $table->enum('role', ['Reviewer1', 'Reviewer2'])->nullable()->after('user_id');
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
            $table->dropColumn('role');
        });
    }
}
