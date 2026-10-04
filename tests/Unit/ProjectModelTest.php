<?php

namespace Tests\Unit;

use App\Models\Program;
use App\Models\Project;
use App\Models\StatusHistory;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    public function test_it_accepts_program_import_fields_for_mass_assignment()
    {
        $project = new Project([
            'program_id' => 1,
            'old_project_id' => 'P-100',
            'title' => 'Sample Project',
            'email' => 'pi@example.com',
            'author' => 'Dr. Sample',
        ]);

        $this->assertSame(1, $project->program_id);
        $this->assertSame('P-100', $project->old_project_id);
        $this->assertSame('Sample Project', $project->title);
        $this->assertSame('pi@example.com', $project->email);
        $this->assertSame('Dr. Sample', $project->author);
    }

    public function test_it_treats_unregistered_projects_as_imported_for_workflow_state()
    {
        $project = new Project();
        $project->setRelation('latestStatus', new StatusHistory(['status' => 'unregistered']));

        $this->assertSame('imported', $project->currentWorkflowStatus());
    }

    public function test_it_uses_program_deadlines_to_determine_activity()
    {
        $project = new Project();
        $program = new Program(['final_rpt_deadline' => null]);
        $project->setRelation('program', $program);

        $this->assertTrue($project->programIsActive());
    }

    public function test_status_constants_define_the_new_submission_and_rejection_milestones()
    {
        $this->assertSame('progress_added',      Project::STATUS_PROGRESS_ADDED);
        $this->assertSame('progress_reviewed',   Project::STATUS_PROGRESS_REVIEWED);
        $this->assertSame('progress_rejected',   Project::STATUS_PROGRESS_REJECTED);
        $this->assertSame('final_added',         Project::STATUS_FINAL_ADDED);
        $this->assertSame('imported',            Project::STATUS_IMPORTED);

        $this->assertNotEquals(Project::STATUS_PROGRESS_REVIEWED, Project::STATUS_PROGRESS_REJECTED);
        $this->assertNotEquals(Project::STATUS_PROGRESS_ADDED,    Project::STATUS_FINAL_ADDED);
    }

    public function test_status_labels_cover_new_workflow_states()
    {
        $labels = Project::statusLabels();

        $this->assertSame('Imported',          $labels[Project::STATUS_IMPORTED] ?? null);
        $this->assertSame('Registered',        $labels[Project::STATUS_REGISTERED] ?? null);
        $this->assertSame('Assigned',          $labels[Project::STATUS_ASSIGNED] ?? null);
        $this->assertSame('Claimed',           $labels[Project::STATUS_CLAIMED] ?? null);
        $this->assertSame('Progress Added',    $labels[Project::STATUS_PROGRESS_ADDED] ?? null);
        $this->assertSame('Progress Reviewed', $labels[Project::STATUS_PROGRESS_REVIEWED] ?? null);
        $this->assertSame('Progress Rejected', $labels[Project::STATUS_PROGRESS_REJECTED] ?? null);
        $this->assertSame('Final Added',       $labels[Project::STATUS_FINAL_ADDED] ?? null);
        $this->assertSame('Graded',            $labels[Project::STATUS_GRADED] ?? null);
    }

    public function test_project_submission_casts_submitted_and_submitted_at_fields()
    {
        $submission = new \App\Models\ProjectSubmission([
            'submitted'    => true,
            'submitted_at' => '2026-07-29 14:30:00',
        ]);

        $this->assertIsBool($submission->submitted);
        $this->assertTrue($submission->submitted);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $submission->submitted_at);
    }

    public function test_reviewer_rejection_model_has_expected_fillable_attributes()
    {
        $rejection = new \App\Models\ReviewerRejection();
        $reflection = new \ReflectionClass($rejection);
        $fillable = $reflection->getProperty('fillable');
        $fillable->setAccessible(true);

        $this->assertEquals(
            ['project_id', 'user_id', 'reason'],
            $fillable->getValue($rejection)
        );
        $this->assertSame('reviewer_rejections', $rejection->getTable());
    }
}
