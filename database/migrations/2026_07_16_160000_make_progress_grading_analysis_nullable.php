<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->string('analysis')->nullable()->change();
            $table->string('comments')->nullable()->change();
            $table->string('recommendation')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('progress_report_grading', function (Blueprint $table) {
            $table->string('analysis')->nullable(false)->change();
            $table->string('comments')->nullable(false)->change();
            $table->string('recommendation')->nullable(false)->change();
        });
    }
};