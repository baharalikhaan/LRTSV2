<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameCollegeProjectToProjectCollege extends Migration
{
    public function up()
    {
        Schema::rename('college_project', 'project_college');
    }

    public function down()
    {
        Schema::rename('project_college', 'college_project');
    }
}
