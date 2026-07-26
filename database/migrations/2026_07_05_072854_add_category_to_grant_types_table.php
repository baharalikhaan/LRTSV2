<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToGrantTypesTable extends Migration
{
    public function up()
    {
        Schema::table('grant_types', function (Blueprint $table) {
            $table->enum('category', ['Regular', 'Student'])->default('Regular')->after('grant_code');
        });
    }

    public function down()
    {
        Schema::table('grant_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
