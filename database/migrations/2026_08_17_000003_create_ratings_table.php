<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRatingsTable extends Migration
{
    /**
     * Ratings lookup table (mirrors the legacy `ratings` table).
     *
     * The progress report grading columns (achievementsRating, publicationsRating,
     * studentsRating, budgetRating) store an integer id (1-5) that references
     * this table's id. The `rating` column holds the human-readable label, e.g.
     * 1 => Very Dissatisfied, 2 => Dissatisfied, 3 => Neutral, 4 => Satisfied,
     * 5 => Very Satisfied.
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('rating', 20)->unique();
        });

        DB::table('ratings')->insert([
            ['id' => 1, 'rating' => 'Very Dissatisfied'],
            ['id' => 2, 'rating' => 'Dissatisfied'],
            ['id' => 3, 'rating' => 'Neutral'],
            ['id' => 4, 'rating' => 'Satisfied'],
            ['id' => 5, 'rating' => 'Very Satisfied'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}
