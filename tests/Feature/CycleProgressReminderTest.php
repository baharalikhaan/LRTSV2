<?php

namespace Tests\Feature;

use App\Mail\ProjectReminderMail;
use App\Models\Program;
use App\Models\Project;
use App\Models\ReportReminderSent;
use App\Models\User;
use App\Services\CycleProgressReportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Cycle summary report — "Send Email Reminders" functionality.
 *
 * The endpoint queues a ProjectReminderMail per pending project (grouped by
 * LPI / reviewer / admin recipient) and records a ReportReminderSent log row.
 * Verified with Mail::fake() so NO real email is ever sent.
 */
class CycleProgressReminderTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    /** A research call that actually has pending projects for the column. */
    private function programWithPending(string $column): ?int
    {
        $svc = new CycleProgressReportService();
        foreach (Program::pluck('id') as $pid) {
            $report = $svc->buildReportByProgram((int) $pid);
            if (!$report['program']) {
                continue;
            }
            if ($svc->getPendingProjects($report['rows'], $column)->count() > 0) {
                return (int) $pid;
            }
        }
        return null;
    }

    public function test_guest_is_rejected()
    {
        $this->postJson(route('reports.cycle-progress.send-reminder'), [
            'program_id' => 1, 'column_key' => 'final_report',
        ])->assertStatus(401);
    }

    public function test_non_admin_is_rejected()
    {
        $lpi = $this->activeUser('LPI');
        if (!$lpi) {
            $this->markTestSkipped('No LPI.');
        }

        $this->actingAs($lpi)->postJson(route('reports.cycle-progress.send-reminder'), [
            'program_id' => 1, 'column_key' => 'final_report',
        ])->assertStatus(403);
    }

    /** MAIL_ENABLED=false (the live default) must refuse to send. */
    public function test_send_is_refused_when_mail_is_disabled()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        config(['mail.enabled' => false]);

        $this->actingAs($admin)->postJson(route('reports.cycle-progress.send-reminder'), [
            'program_id' => 1, 'column_key' => 'final_report',
        ])->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_invalid_column_key_is_rejected()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        config(['mail.enabled' => true]);

        $this->actingAs($admin)->postJson(route('reports.cycle-progress.send-reminder'), [
            'program_id' => 1, 'column_key' => 'not_a_real_column',
        ])->assertStatus(422)->assertJsonValidationErrors(['column_key']);
    }

    /** LPI-recipient column: one ProjectReminderMail per pending project + a log row. */
    public function test_lpi_reminder_queues_project_reminder_mail()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        $programId = $this->programWithPending('final_report');
        if (!$programId) {
            $this->markTestSkipped('No research call has pending final reports.');
        }

        config(['mail.enabled' => true]);
        Mail::fake();

        $res = $this->actingAs($admin)->postJson(route('reports.cycle-progress.send-reminder'), [
            'program_id' => $programId,
            'column_key' => 'final_report',
        ]);

        $res->assertOk()->assertJsonPath('success', true);
        $count = (int) $res->json('count');
        $this->assertGreaterThan(0, $count, 'Expected at least one pending LPI reminder.');

        Mail::assertQueued(ProjectReminderMail::class, $count);

        $this->assertSame(
            $count,
            ReportReminderSent::where('program_id', $programId)
                ->where('column_key', 'final_report')
                ->where('recipient_type', 'lpi')
                ->count(),
            'Every queued reminder must be logged.'
        );
    }

    /** Reviewer-recipient column targets the assigned reviewer. */
    public function test_reviewer_reminder_queues_to_assigned_reviewers()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        $programId = $this->programWithPending('final_grading');
        if (!$programId) {
            $this->markTestSkipped('No research call has pending final gradings.');
        }

        config(['mail.enabled' => true]);
        Mail::fake();

        $res = $this->actingAs($admin)->postJson(route('reports.cycle-progress.send-reminder'), [
            'program_id' => $programId,
            'column_key' => 'final_grading',
        ]);

        $res->assertOk()->assertJsonPath('success', true);
        Mail::assertQueued(ProjectReminderMail::class);

        $this->assertGreaterThan(
            0,
            ReportReminderSent::where('column_key', 'final_grading')
                ->where('recipient_type', 'reviewer')
                ->count()
        );
    }

    /** No pending projects → a friendly success with count 0 and nothing queued. */
    public function test_no_pending_projects_reports_zero()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No admin.');
        }

        // Pick a program/column combination that is fully complete.
        $svc = new CycleProgressReportService();
        $programId = null;
        foreach (Program::pluck('id') as $pid) {
            $report = $svc->buildReportByProgram((int) $pid);
            if ($report['program'] && $svc->getPendingProjects($report['rows'], 'registration')->count() === 0) {
                $programId = (int) $pid;
                break;
            }
        }
        if (!$programId) {
            $this->markTestSkipped('No fully-registered research call found.');
        }

        config(['mail.enabled' => true]);
        Mail::fake();

        $res = $this->actingAs($admin)->postJson(route('reports.cycle-progress.send-reminder'), [
            'program_id' => $programId,
            'column_key' => 'registration',
        ]);

        $res->assertOk()->assertJsonPath('success', true)->assertJsonPath('count', 0);
        Mail::assertNothingQueued();
    }

    public function test_research_call_summary_includes_progress_report_2_column()
    {
        $admin = $this->activeUser('Admin');
        $project = Project::whereNotNull('program_id')
            ->whereHas('program')
            ->whereDoesntHave('program.grant', function ($query) {
                $query->where('category', 'student');
            })
            ->first();

        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a regular research-call project.');
        }

        $project->extended_progress = true;
        $project->save();
        if (!$project->submissions()->where('type', 'progress2')->exists()) {
            $project->submissions()->create([
                'type' => 'progress2',
                'file_path' => 'uploads/test/progress_reports/PR2-test.pdf',
                'stored_filename' => 'PR2-test.pdf',
                'original_filename' => 'PR2-test.pdf',
                'version' => 1,
                'user_id' => $admin->id,
                'submitted' => true,
                'submitted_at' => now(),
            ]);
        }

        $report = (new CycleProgressReportService())->buildReportByProgram((int) $project->program_id);
        $row = $report['rows']->firstWhere('id', $project->id);

        $this->assertNotNull($row);
        $this->assertTrue($row['progress2_enabled']);
        $this->assertTrue($row['has_progress2_report']);
        $this->assertGreaterThan(0, $report['footer']['progress2_report']['completed']);
        $this->assertArrayHasKey('progress2_report', CycleProgressReportService::COLUMNS);

        $html = $this->actingAs($admin)
            ->get(route('reports.cycle-progress', ['program_id' => $project->program_id]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Progress Report 2', $html);
    }
}
