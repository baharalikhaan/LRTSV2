<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewerRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('reviewer_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->integer('conflict')->default(0);
            $table->integer('responsiveness')->default(0);
            $table->integer('comprehensiveness')->default(0);
            $table->integer('no_reviewers')->default(0);
            $table->integer('behaviour')->default(0);
            $table->timestamps();

            $table->unique(['reviewer_id', 'program_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviewer_ratings');
    }
}
