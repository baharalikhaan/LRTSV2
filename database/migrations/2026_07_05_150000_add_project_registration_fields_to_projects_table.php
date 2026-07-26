<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectRegistrationFieldsToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('title_ar', 500)->nullable()->after('title');
            $table->text('abstract')->nullable()->after('title_ar');
            $table->string('keywords', 500)->nullable()->after('abstract');
            $table->string('author')->nullable()->after('keywords');
            $table->string('email')->nullable()->after('author');
            $table->string('phone', 50)->nullable()->after('email');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'abstract', 'keywords', 'author', 'email', 'phone']);
        });
    }
}
