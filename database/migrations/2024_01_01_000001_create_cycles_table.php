<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCyclesTable extends Migration
{
    public function up()
    {
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->string('cycle_title');
            $table->string('grant_type')->nullable();
            $table->timestamp('prog_rpt_deadline')->nullable();
            $table->timestamp('extended_prog_rpt_deadline')->nullable();
            $table->timestamp('final_rpt_deadline')->nullable();
            $table->timestamp('extended_final_rpt_deadline')->nullable();
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cycles');
    }
}
