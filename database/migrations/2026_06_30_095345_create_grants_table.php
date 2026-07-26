<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrantsTable extends Migration
{
    public function up()
    {
        Schema::create('grants', function (Blueprint $table) {
            $table->id();
            $table->string('grant_code', 50)->unique();
            $table->string('grant_name', 255);
            $table->enum('category', ['student', 'regular'])->default('regular');
            $table->string('funding_agency', 255)->nullable();
            $table->unsignedSmallInteger('max_duration_years')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grants');
    }
}
