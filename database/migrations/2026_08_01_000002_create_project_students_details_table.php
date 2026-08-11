<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_students_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_student_id')->constrained('project_students')->cascadeOnDelete();
            $table->string('student_id', 50);
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('student_status', 50)->nullable();
            $table->string('major', 100)->nullable();
            $table->string('minor', 100)->nullable();
            $table->string('college', 100)->nullable();
            $table->string('std_program', 150)->nullable();
            $table->string('std_level', 50)->nullable();
            $table->string('admission_term', 50)->nullable();
            $table->string('reg_in_course', 100)->nullable();
            $table->text('raw_response')->nullable();
            $table->timestamps();

            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_students_details');
    }
};
