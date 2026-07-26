<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCycleConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('cycle_configs', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cycle_configs');
    }
}
