<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewerRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviewer_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reviewer_id');        // the reviewer being rated
            $table->unsignedBigInteger('user_id');             // the admin who rated
            $table->unsignedBigInteger('program_id');           // the program being rated
            $table->integer('conflict')->default(0);           // 0-10 star rating
            $table->integer('responsiveness')->default(0);     // 0-10 star rating
            $table->integer('comprehensiveness')->default(0);  // 0-10 star rating
            $table->integer('no_reviewers')->default(0);       // number of reviews done
            $table->integer('behaviour')->default(0);          // 0-10 star rating

            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');

            $table->unique(['reviewer_id', 'program_id']); // one rating per reviewer per program
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviewer_ratings');
    }
}