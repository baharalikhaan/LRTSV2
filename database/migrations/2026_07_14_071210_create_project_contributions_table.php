<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectContributionsTable extends Migration
{
    public function up()
    {
        Schema::create('project_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->comment('journal_q1, journal_q2, journal_q3, journal_q4, conference, book, edited_book, book_chapter, ip_disclosure, provisional_patent, patent_granted, open_source_sw, startup, hired_researcher, cross_college, research_awards');
            $table->text('detail')->nullable();
            $table->tinyInteger('score')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_contributions');
    }
}
