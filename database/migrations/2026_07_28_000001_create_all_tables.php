<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllTables extends Migration
{
    public function up()
    {
        // ─── Tables with no foreign key dependencies ───────────────────

        Schema::create('nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('type')->default('Student');
            $table->string('faculty')->nullable();
            $table->string('qu_id')->nullable();
            $table->foreignId('nationality_id')->nullable()->constrained('nationalities')->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->string('college')->nullable();
            $table->string('pillars')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['tokenable_type', 'tokenable_id']);
        });

        Schema::create('grants', function (Blueprint $table) {
            $table->id();
            $table->string('grant_code')->unique();
            $table->string('grant_name');
            $table->enum('category', ['student', 'regular'])->default('regular');
            $table->string('funding_agency')->nullable();
            $table->unsignedSmallInteger('max_duration_years')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cycle_configs', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('pillars', function (Blueprint $table) {
            $table->id();
            $table->string('pillar', 250);
            $table->longText('subpillar')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('label', 20)->nullable();
            $table->decimal('value', 5, 2);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('reviewer_grading', function (Blueprint $table) {
            $table->id();
            $table->integer('reviewer');
            $table->integer('cycle');
            $table->integer('conflict');
            $table->integer('responsiveness');
            $table->integer('comprehensiveness');
            $table->integer('no_reviewers');
            $table->integer('behaviour');
            $table->string('scope_of_supply', 250)->default('Written Scientific Review');
            $table->string('mode_of_selection', 250)->default('From ORS Database');
            $table->string('basis_of_approval', 250)->default('Previous Successful Review');
            $table->string('type_extent_of_control', 250)->default('Former review Evaluation');
            $table->string('designation_of_approver', 250)->default('Post-Award Manager');
            $table->integer('user_id');
            $table->date('created_at')->useCurrent();
            $table->date('updated_at')->useCurrent();
        });

        Schema::create('team', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('name');
            $table->string('role');
            $table->string('introduction');
            $table->string('email')->nullable();
            $table->timestamps();
            $table->string('phone', 20)->nullable();
            $table->string('address', 250)->nullable();
        });

        // ─── Tables with foreign key dependencies ────────────────────

        Schema::create('announcement', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable();
            $table->string('audience', 50)->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('program_title');
            $table->timestamp('prog_rpt_deadline')->nullable();
            $table->timestamp('extended_prog_rpt_deadline')->nullable();
            $table->date('prog_rpt2_deadline')->nullable();
            $table->date('extended_prog_rpt2_deadline')->nullable();
            $table->timestamp('final_rpt_deadline')->nullable();
            $table->timestamp('extended_final_rpt_deadline')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->foreignId('grant_id')->nullable()->constrained('grants')->nullOnDelete();
            $table->foreignId('cycle_id')->nullable()->constrained('cycle_configs')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar', 500)->nullable();
            $table->text('abstract')->nullable();
            $table->string('keywords', 500)->nullable();
            $table->string('author')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('old_project_id')->nullable();
            $table->decimal('total_score', 10, 2)->nullable();
            $table->foreignId('lpi_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->decimal('requested_budget_qar', 12, 2)->nullable();
            $table->string('proposal_filename')->nullable();
            $table->string('college_decision')->nullable();
            $table->text('rsd_feedback')->nullable();
            $table->string('final_rsd_decision')->nullable();
            $table->timestamps();
        });

        Schema::create('commitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('q1article')->default(0);
            $table->integer('q2article')->default(0);
            $table->integer('q3article')->default(0);
            $table->integer('q4article')->default(0);
            $table->integer('confArticle')->default(0);
            $table->integer('books')->default(0);
            $table->integer('editBooks')->default(0);
            $table->integer('chapters')->default(0);
            $table->integer('ip')->default(0);
            $table->integer('filedPatent')->default(0);
            $table->integer('openSourceSW')->default(0);
            $table->boolean('startUp')->default(false);
            $table->boolean('ethical')->default(false);
            $table->integer('master')->default(0);
            $table->integer('UG')->default(0);
            $table->integer('Phd')->default(0);
            $table->boolean('crossCollege')->default(false);
            $table->timestamps();
        });

        Schema::create('projects_reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20)->nullable();
            $table->string('proposalstatus', 50)->default('0');
            $table->date('statusdate')->nullable();
            $table->timestamps();
        });

        Schema::create('project_college', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('college_id')->constrained('colleges');
            $table->timestamps();
        });

        Schema::create('project_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->text('detail')->nullable();
            $table->tinyInteger('score')->default(0);
            $table->timestamps();
        });

        Schema::create('project_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('identifier');
            $table->date('online_date')->nullable();
            $table->enum('verifcation_by_system', ['verified', 'not-verified', 'pending'])->default('pending');
            $table->enum('verifcation_by_reviewer', ['verified', 'not-verified', 'pending'])->default('pending');
            $table->tinyInteger('score');
            $table->timestamps();
        });

        Schema::create('project_pillar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('pillar_id')->constrained('pillars');
            $table->timestamps();
        });

        Schema::create('project_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('authors')->nullable();
            $table->string('publication_title');
            $table->string('journal')->nullable();
            $table->string('year')->nullable();
            $table->string('doi')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('project_researchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('category', 100)->nullable();
            $table->integer('days')->default(0);
            $table->tinyInteger('score')->default(0);
            $table->timestamps();
        });

        Schema::create('project_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['UG', 'masters', 'PhD']);
            $table->string('std_id');
            $table->integer('days');
            $table->tinyInteger('score');
            $table->timestamps();
        });

        Schema::create('project_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['progress', 'final', 'readiness']);
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->integer('version')->default(1);
            $table->string('file_path');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('status');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->index(['project_id', 'status']);
        });

        Schema::create('final_report_grading', function (Blueprint $table) {
            $table->id();
            $table->double('gradeA', 8, 2)->nullable();
            $table->longText('commentA')->nullable();
            $table->double('gradeB', 8, 2)->nullable();
            $table->longText('commentB')->nullable();
            $table->double('gradeC', 8, 2)->nullable();
            $table->longText('commentC')->nullable();
            $table->double('gradeD', 8, 2)->nullable();
            $table->longText('commentD')->nullable();
            $table->double('total', 8, 2)->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('project_id')->constrained('projects');
            $table->enum('publish', ['accepted', 'rejected', 'pending', 'reserved'])->default('pending');
            $table->boolean('isAdmin')->default(false);
            $table->integer('isAccepted');
            $table->timestamps();
        });

        Schema::create('progress_report_grading', function (Blueprint $table) {
            $table->id();
            $table->string('analysis')->nullable();
            $table->string('comments')->nullable();
            $table->string('recommendation')->nullable();
            $table->string('path')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('project_id')->constrained('projects');
            $table->enum('publish', ['accepted', 'rejected', 'reserved', 'pending'])->default('pending');
            $table->integer('achievementsRating')->default(1);
            $table->integer('publicationsRating')->default(1);
            $table->integer('studentsRating')->default(1);
            $table->string('achievementsComments', 1200)->nullable();
            $table->string('publicationsComments', 1200)->nullable();
            $table->string('studentsComments', 1200)->nullable();
            $table->integer('ethical')->default(-1);
            $table->integer('isAccepted');
            $table->string('report_type', 50)->nullable();
            $table->integer('budgetRating')->nullable();
            $table->string('budgetComments', 1200)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progress_report_grading');
        Schema::dropIfExists('final_report_grading');
        Schema::dropIfExists('status_histories');
        Schema::dropIfExists('project_submissions');
        Schema::dropIfExists('project_students');
        Schema::dropIfExists('project_researchers');
        Schema::dropIfExists('project_publications');
        Schema::dropIfExists('project_pillar');
        Schema::dropIfExists('project_outcomes');
        Schema::dropIfExists('project_contributions');
        Schema::dropIfExists('project_college');
        Schema::dropIfExists('projects_reviewers');
        Schema::dropIfExists('commitments');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('announcement');
        Schema::dropIfExists('team');
        Schema::dropIfExists('reviewer_grading');
        Schema::dropIfExists('scores');
        Schema::dropIfExists('colleges');
        Schema::dropIfExists('pillars');
        Schema::dropIfExists('cycle_configs');
        Schema::dropIfExists('grants');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
        Schema::dropIfExists('nationalities');
    }
}