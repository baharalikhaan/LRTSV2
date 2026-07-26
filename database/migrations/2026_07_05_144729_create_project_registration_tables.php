<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectRegistrationTables extends Migration
{
    public function up()
    {
        Schema::create('project_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('authors')->nullable();
            $table->string('publication_title');
            $table->string('journal')->nullable();
            $table->string('year')->nullable();
            $table->string('doi')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('project_intellectual_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('ip_type')->comment('patent, copyright, trademark, etc.');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('filing_status')->default('pending')->comment('pending, filed, granted, published');
            $table->string('application_number')->nullable();
            $table->timestamps();
        });

        Schema::create('project_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('student_name');
            $table->string('student_id')->nullable();
            $table->string('college')->nullable();
            $table->string('department')->nullable();
            $table->string('role')->nullable()->comment('RA, TA, volunteer, etc.');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_students');
        Schema::dropIfExists('project_intellectual_properties');
        Schema::dropIfExists('project_publications');
    }
}
