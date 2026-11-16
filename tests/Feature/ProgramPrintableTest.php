<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Printable (A4) version of the research call page — /programs/{id}/printable
 */
class ProgramPrintableTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'like', '%Admin%')->first();
    }

    private function program(): ?Program
    {
        return Program::with('projects')->has('projects')->first() ?: Program::first();
    }

    public function test_admin_can_open_the_printable_research_call_page()
    {
        $admin = $this->admin();
        $program = $this->program();
        if (!$admin || !$program) {
            $this->markTestSkipped('No admin or research call in the test database.');
        }

        $res = $this->actingAs($admin)->get('/programs/' . $program->id . '/printable');

        $res->assertStatus(200);
        $res->assertSee('Research Call');
        $res->assertSee(e($program->program_title));
    }

    public function test_the_printable_page_lists_the_research_call_projects()
    {
        $admin = $this->admin();
        $program = $this->program();
        if (!$admin || !$program) {
            $this->markTestSkipped('No admin or research call in the test database.');
        }

        $res = $this->actingAs($admin)->get('/programs/' . $program->id . '/printable');

        $res->assertStatus(200);
        foreach ($program->projects->take(3) as $project) {
            $res->assertSee(e($project->old_project_id ?? $project->id));
        }
    }

    public function test_the_research_call_page_links_to_the_printable_version()
    {
        $admin = $this->admin();
        $program = $this->program();
        if (!$admin || !$program) {
            $this->markTestSkipped('No admin or research call in the test database.');
        }

        $res = $this->actingAs($admin)->get('/programs/' . $program->id);

        $res->assertStatus(200);
        $res->assertSee(route('programs.printable', $program->id), false);
    }

    public function test_guests_are_redirected_from_the_printable_page()
    {
        $program = $this->program();
        if (!$program) {
            $this->markTestSkipped('No research call in the test database.');
        }

        $this->get('/programs/' . $program->id . '/printable')->assertRedirect('/login');
    }
}
