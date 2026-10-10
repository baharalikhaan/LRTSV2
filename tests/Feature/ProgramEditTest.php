<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Regression: editing a research call (deadlines) must never blank out the
 * NOT NULL program_title (the edit modal previously submitted an empty title).
 */
class ProgramEditTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'Admin')->where('is_active', 1)->first();
    }

    public function test_editing_deadlines_without_a_title_keeps_the_title()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $program = Program::first();
        $originalTitle = $program->program_title;

        $res = $this->actingAs($admin)->put('/programs/' . $program->id, [
            'prog_rpt_deadline'   => '2026-10-15',
            'final_rpt_deadline'  => '2026-12-15',
            'description'         => 'updated from test',
        ]);

        $this->assertContains($res->getStatusCode(), [200, 302]);
        $this->assertSame($originalTitle, $program->fresh()->program_title);
    }

    public function test_submitting_an_empty_title_does_not_blank_it()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $program = Program::first();
        $originalTitle = $program->program_title;

        $this->actingAs($admin)->put('/programs/' . $program->id, [
            'program_title'      => '',
            'prog_rpt_deadline'  => '2026-10-15',
        ]);

        $this->assertSame($originalTitle, $program->fresh()->program_title);
    }

    public function test_a_new_title_is_saved()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $program = Program::first();

        $this->actingAs($admin)->put('/programs/' . $program->id, [
            'program_title' => 'Renamed Research Call ' . uniqid(),
        ]);

        $this->assertStringStartsWith('Renamed Research Call', $program->fresh()->program_title);
    }
}
