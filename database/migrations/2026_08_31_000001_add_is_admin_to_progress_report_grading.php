<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIsAdminToProgressReportGrading extends Migration
{
    public function up()
    {
        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->boolean('isAdmin')->default(false)->after('isAccepted');
        });
    }

    public function down()
    {
        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->dropColumn('isAdmin');
        });
    }
}
