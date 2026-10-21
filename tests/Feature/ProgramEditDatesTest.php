<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProgramEditDatesTest extends TestCase
{
    use DatabaseTransactions;

    private function activeAdmin(): ?User
    {
        return User::where('type', 'Admin')->where('is_active', 1)->first();
    }

    public function test_edit_modal_renders_date_attributes_using_the_keys_read_by_javascript()
    {
        $admin = $this->activeAdmin();
        $program = Program::whereHas('projects')->first();
        if (!$admin || !$program) {
            $this->markTestSkipped('Need an admin and a research call.');
        }

        $program->update([
            'prog_rpt_deadline' => '2026-11-03',
            'prog_rpt2_deadline' => '2026-12-04',
            'final_rpt_deadline' => '2027-01-05',
        ]);

        $html = $this->actingAs($admin)->get(route('programs.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-field-prog-rpt-deadline="2026-11-03"', $html);
        $this->assertStringContainsString('data-field-prog-rpt2-deadline="2026-12-04"', $html);
        $this->assertStringContainsString('data-field-final-rpt-deadline="2027-01-05"', $html);
        $this->assertStringContainsString("data.fieldProgRptDeadline", $html);
        $this->assertStringContainsString("data.fieldProgRpt2Deadline", $html);
        $this->assertStringContainsString("data.fieldFinalRptDeadline", $html);
        $this->assertStringContainsString('<th>Grant</th>', $html);
        $this->assertStringNotContainsString('>Type</th>', $html, 'Grant Type should be shown beneath Grant, not in its own column.');
        $this->assertStringContainsString('<th>Prog. Report 2 Deadline</th>', $html);
        if ($program->grant) {
            $this->assertStringContainsString('font-weight:600;color:var(--ink-700,#38333e);">' . $program->grant->grant_code . '</div>', $html);
        }
    }

    public function test_partial_research_call_update_preserves_deadlines_not_submitted()
    {
        $admin = $this->activeAdmin();
        $program = Program::first();
        if (!$admin || !$program) {
            $this->markTestSkipped('Need an admin and a research call.');
        }

        $program->update([
            'prog_rpt_deadline' => '2026-11-03',
            'prog_rpt2_deadline' => '2026-12-04',
            'final_rpt_deadline' => '2027-01-05',
        ]);

        $this->actingAs($admin)
            ->put(route('programs.update', ['id' => $program->id]), [
                'description' => 'Updated without touching the report deadlines.',
            ])
            ->assertRedirect(route('programs.show', ['id' => $program->id]));

        $program->refresh();
        $this->assertSame('2026-11-03', $program->prog_rpt_deadline->format('Y-m-d'));
        $this->assertSame('2026-12-04', $program->prog_rpt2_deadline->format('Y-m-d'));
        $this->assertSame('2027-01-05', $program->final_rpt_deadline->format('Y-m-d'));
        $this->assertSame('Updated without touching the report deadlines.', $program->description);
    }
}
