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
        $this->assertSame('progress_submitted', Project::STATUS_PROGRESS_SUBMITTED);
        $this->assertSame('final_submitted',    Project::STATUS_FINAL_SUBMITTED);
        $this->assertSame('rejected',           Project::STATUS_REJECTED);

        $this->assertNotEquals(Project::STATUS_PROGRESS_SUBMITTED, Project::STATUS_FINAL_SUBMITTED);
        $this->assertNotEquals(Project::STATUS_PROGRESS_SUBMITTED, Project::STATUS_REJECTED);
        $this->assertNotEquals(Project::STATUS_FINAL_SUBMITTED,    Project::STATUS_REJECTED);
    }

    public function test_status_labels_cover_submitted_and_rejected_states()
    {
        $labels = Project::statusLabels();

        $this->assertSame('Progress Submitted', $labels[Project::STATUS_PROGRESS_SUBMITTED] ?? null);
        $this->assertSame('Final Submitted',    $labels[Project::STATUS_FINAL_SUBMITTED] ?? null);
        $this->assertSame('Rejected',           $labels[Project::STATUS_REJECTED] ?? null);
        $this->assertSame('Graded',             $labels[Project::STATUS_GRADED] ?? null);
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
