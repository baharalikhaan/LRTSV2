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
}
