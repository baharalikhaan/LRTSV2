<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTagsToColleges extends Migration
{
    public function up()
    {
        // Rename main table: tags -> colleges
        Schema::rename('tags', 'colleges');

        // Rename pivot tables
        // users_tags -> college_user, with tag_id -> college_id
        Schema::table('users_tags', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
        });
        Schema::rename('users_tags', 'college_user');
        Schema::table('college_user', function (Blueprint $table) {
            $table->renameColumn('tag_id', 'college_id');
        });

        // project_tag -> college_project, with tag_id -> college_id
        Schema::table('project_tag', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
        });
        Schema::rename('project_tag', 'college_project');
        Schema::table('college_project', function (Blueprint $table) {
            $table->renameColumn('tag_id', 'college_id');
        });
    }

    public function down()
    {
        // Reverse pivot renames
        Schema::table('college_project', function (Blueprint $table) {
            $table->renameColumn('college_id', 'tag_id');
        });
        Schema::rename('college_project', 'project_tag');
        Schema::table('project_tag', function (Blueprint $table) {
            $table->foreign('tag_id')->references('id')->on('colleges')->onDelete('cascade');
        });

        Schema::table('college_user', function (Blueprint $table) {
            $table->renameColumn('college_id', 'tag_id');
        });
        Schema::rename('college_user', 'users_tags');
        Schema::table('users_tags', function (Blueprint $table) {
            $table->foreign('tag_id')->references('id')->on('colleges')->onDelete('cascade');
        });

        // Reverse main table rename
        Schema::rename('colleges', 'tags');
    }
}
