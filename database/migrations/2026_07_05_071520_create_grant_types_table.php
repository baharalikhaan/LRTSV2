<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrantTypesTable extends Migration
{
    public function up()
    {
        Schema::create('grant_types', function (Blueprint $table) {
            $table->id();
            $table->string('grant_code', 50)->unique();
            $table->string('grant_title');
            $table->text('description')->nullable();
            $table->string('funding_agency')->nullable();
            $table->string('duration', 100)->nullable();
            $table->boolean('isactive')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grant_types');
    }
}
