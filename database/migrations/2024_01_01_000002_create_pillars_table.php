<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePillarsTable extends Migration
{
    public function up()
    {
        Schema::create('pillars', function (Blueprint $table) {
            $table->id();
            $table->string('pillar');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('old_project_id')->nullable();
            $table->string('status')->default('Pending');
            $table->string('year')->nullable();
            $table->timestamp('expiry')->nullable();
            $table->decimal('total_score', 10, 2)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cycle_id')->nullable()->constrained('cycles')->onDelete('set null');
            $table->string('grant_type')->nullable();
            $table->decimal('requested_budget_qar', 12, 2)->nullable();
            $table->string('college_decision')->nullable();
            $table->text('rsd_feedback')->nullable();
            $table->string('final_rsd_decision')->nullable();
            $table->timestamps();
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('type');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('filepath');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('outcome');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('commitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('commitment');
            $table->text('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('grading_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('reviewer_grade', 10, 2)->nullable();
            $table->decimal('outcome_grade', 10, 2)->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('submissions_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->decimal('score', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('progress_report_gradings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->decimal('grade', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('final_report_gradings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->decimal('grade', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('progress_grading_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->json('draft_data')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('final_grading_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->json('draft_data')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('announcement', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcement');
        Schema::dropIfExists('final_grading_drafts');
        Schema::dropIfExists('progress_grading_drafts');
        Schema::dropIfExists('final_report_gradings');
        Schema::dropIfExists('progress_report_gradings');
        Schema::dropIfExists('submissions_scores');
        Schema::dropIfExists('grading_details');
        Schema::dropIfExists('commitments');
        Schema::dropIfExists('outcomes');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('pillars');
    }
}
