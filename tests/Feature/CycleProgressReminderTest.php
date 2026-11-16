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

    /** The email body carries the project identity, its deadline and the guidance. */
    public function test_reminder_email_contains_project_details_and_guidance()
    {
        $project = Project::with(['program.cycle', 'program.grant', 'lpi'])
            ->whereNotNull('program_id')->whereNotNull('lpi_id')->orderBy('id')->first();

        if (!$project || !$project->program) {
            $this->markTestSkipped('No project with a research call and an LPI.');
        }

        $details = ProjectReminderMail::detailsForProject($project, 'outcomes');

        $mail = new ProjectReminderMail(
            'outcomes',
            $project->title,
            $project->program->program_title,
            'Test Recipient',
            $details
        );

        $html = $mail->render();

        // Project identity
        $this->assertStringContainsString(e((string) ($project->old_project_id ?: $project->id)), $html);
        $this->assertStringContainsString('Project title', $html);
        $this->assertStringContainsString(e($project->title), $html);
        $this->assertStringContainsString('Research call', $html);

        // The guidance is merged into the merged "Instructions" checklist
        $this->assertStringContainsString('Instructions', $html);
        $this->assertStringNotContainsString('What is required', $html);
        $this->assertStringContainsString('journal articles', $html);

        // The deadline for that column, when the research call has one
        if ($project->program->prog_rpt_deadline) {
            $this->assertSame($project->program->prog_rpt_deadline->format('d M Y'), $details['deadline']);
            $this->assertStringContainsString($details['deadline'], $html);
        }
    }

    /** Every column has a distinct subject plus guidance and a closing action. */
    public function test_every_column_has_its_own_subject_and_guidance()
    {
        $service = new CycleProgressReportService();
        $subjects = [];

        foreach (array_keys(CycleProgressReportService::COLUMNS) as $key) {
            $context = $service->getColumnEmailContext($key);

            foreach (['subject', 'intro', 'guidance', 'action'] as $part) {
                $this->assertArrayHasKey($part, $context, "Column [{$key}] is missing [{$part}].");
            }
            $this->assertNotEmpty($context['guidance'], "Column [{$key}] has no guidance bullets.");
            $this->assertGreaterThanOrEqual(1, count($context['guidance']), "Column [{$key}] needs at least one guidance point.");
            $this->assertLessThanOrEqual(3, count($context['guidance']), "Column [{$key}] should stay short - at most three guidance points.");

            // The section must fit in a few lines, so every bullet is a single
            // short sentence rather than a paragraph.
            foreach ($context['guidance'] as $point) {
                $this->assertLessThanOrEqual(
                    100,
                    mb_strlen($point),
                    "Column [{$key}] has a long guidance bullet (" . mb_strlen($point) . " chars): {$point}"
                );
            }

            $subjects[] = $context['subject'];
        }

        $this->assertCount(
            count(array_unique($subjects)),
            $subjects,
            'Two columns share the same email subject.'
        );
    }

    /** The admin reminder lists the pending projects instead of a single one. */
    public function test_admin_email_lists_the_pending_projects()
    {
        $mail = new ProjectReminderMail(
            'reviewer_count',
            '2 project(s) awaiting action',
            'Sample Research Call',
            'Admin User',
            ['pending' => [
                ['id' => 'IRCC-2024-001', 'title' => 'First pending project'],
                ['id' => 'IRCC-2024-002', 'title' => 'Second pending project'],
            ]]
        );

        $html = $mail->render();

        $this->assertStringContainsString('Projects awaiting action', $html);
        $this->assertStringContainsString('IRCC-2024-001', $html);
        $this->assertStringContainsString('Second pending project', $html);
        $this->assertStringContainsString('Sample Research Call', $html);
    }

    /** Subjects are academic in tone and identify the project and research call. */
    public function test_subject_lines_are_professional_and_identify_the_project()
    {
        $service = new CycleProgressReportService();

        $this->assertSame(
            'Final Report Submission Required',
            $service->getColumnEmailContext('final_report')['subject']
        );
        $this->assertSame(
            'Outcome Documentation Required',
            $service->getColumnEmailContext('outcomes')['subject']
        );
        $this->assertSame(
            'Reviewer Assignment Required',
            $service->getColumnEmailContext('reviewer_count')['subject']
        );

        // Single-project reminder: base + project id + research call
        $mail = new ProjectReminderMail(
            'final_report',
            'Some project title',
            'IRCC - Cycle 2024',
            'Test Recipient',
            ['old_project_id' => 'IRCC-2024-494']
        );
        $mail->build();

        $this->assertSame(
            'Final Report Submission Required — IRCC-2024-494 (IRCC - Cycle 2024)',
            $mail->subject
        );
    }

    /** The admin subject reports how many projects are waiting. */
    public function test_admin_subject_counts_the_pending_projects()
    {
        $mail = new ProjectReminderMail(
            'reviewer_count',
            '2 project(s) awaiting action',
            'IRCC - Cycle 2024',
            'Admin User',
            ['pending' => [
                ['id' => 'IRCC-2024-001', 'title' => 'First'],
                ['id' => 'IRCC-2024-002', 'title' => 'Second'],
            ]]
        );
        $mail->build();

        $this->assertSame(
            'Reviewer Assignment Required — 2 projects (IRCC - Cycle 2024)',
            $mail->subject
        );
    }

    /** Every LPI reminder carries the submission instructions and the VPN disclaimer. */
    public function test_lpi_reminders_carry_the_instructions_and_disclaimer()
    {
        $project = Project::with(['program.cycle', 'program.grant', 'lpi'])
            ->whereNotNull('program_id')->whereNotNull('lpi_id')->orderBy('id')->first();

        if (!$project) {
            $this->markTestSkipped('No project with a research call and an LPI.');
        }

        $call = $project->program->program_title ?? 'Call';

        // The instructions list the three online templates and the four
        // submission requirements, and the disclaimer closes the email.
        $columns = [
            'progress_report'  => 'progress',
            'progress2_report' => 'progress',
            'final_report'     => 'final',
            'readiness_report' => 'readiness',
            'outcomes'         => null,
        ];

        foreach ($columns as $column => $current) {
            $mail = new ProjectReminderMail(
                $column,
                $project->title,
                $call,
                'Test Recipient',
                ProjectReminderMail::detailsForProject($project, $column)
            );

            $html = $mail->render();

            // The stage guidance and the submission requirements now live in
            // one "Instructions" checklist, guidance first.
            $this->assertStringContainsString('Instructions', $html, "[{$column}] should show the instructions section.");
            $this->assertStringNotContainsString('What is required', $html, "[{$column}] must no longer show a separate requirements section.");
            $this->assertLessThan(
                strpos($html, 'Online templates'),
                strpos($html, 'Instructions'),
                "[{$column}] the instructions must start the merged checklist."
            );
            $this->assertStringContainsString('Online templates', $html, "[{$column}] is missing the online templates point.");
            $this->assertStringContainsString('Required signature', $html, "[{$column}] is missing the signature requirement.");
            $this->assertStringContainsString('Grant information', $html, "[{$column}] is missing the grant requirement.");
            $this->assertStringContainsString('Project title', $html, "[{$column}] is missing the title requirement.");
            $this->assertStringContainsString('Support and queries', $html, "[{$column}] is missing the support point.");
            $this->assertStringContainsString('mailto:ros-submissions@qu.edu.qa', $html, "[{$column}] should link the support address.");

            // Templates are linked from the official QU post-award forms page.
            $this->assertStringContainsString(CycleProgressReportService::TEMPLATE_URL, $html, "[{$column}] should link the QU post-award forms page.");
            $this->assertStringNotContainsString('/download-template/', $html, "[{$column}] must no longer link a local template file.");

            $expectedLabel = [
                'progress_report'  => 'Progress Report',
                'progress2_report' => 'Progress Report',
                'final_report'     => 'Final Report',
                'readiness_report' => 'Readiness Mapping Report',
                'outcomes'         => 'Post-Award Forms',
            ][$column];

            $this->assertStringContainsString($expectedLabel, $html, "[{$column}] should label the template link [{$expectedLabel}].");

            // The big download box was replaced by the inline links.
            $this->assertStringNotContainsString('Download template', $html, "[{$column}] should no longer show the download box.");

            // Disclaimer closes the email, after the call-to-action.
            $this->assertStringContainsString('GlobalProtect', $html, "[{$column}] is missing the disclaimer.");
            $this->assertGreaterThan(
                strpos($html, 'Open the project'),
                strpos($html, 'GlobalProtect'),
                "[{$column}] the disclaimer must come after the call-to-action."
            );

            // Instructions sit below the button, and above the disclaimer.
            $this->assertGreaterThan(
                strpos($html, 'Open the project'),
                strpos($html, 'Instructions'),
                "[{$column}] the instructions must come after the project button."
            );
            $this->assertGreaterThan(
                strpos($html, 'Required signature'),
                strpos($html, 'GlobalProtect'),
                "[{$column}] the disclaimer must follow the instructions."
            );

            // The grant row was removed from the details table.
            $this->assertStringNotContainsString('<td class="k">Grant</td>', $html, "[{$column}] must no longer show a grant row.");

            // The guidance points are the first entries of the merged checklist.
            $context = (new CycleProgressReportService)->getColumnEmailContext($column);
            $this->assertSame($context['guidance'], array_column(array_slice($context['instructions'], 0, count($context['guidance'])), 'text'));
            foreach ($context['guidance'] as $point) {
                $this->assertGreaterThan(
                    strpos($html, 'Instructions'),
                    strpos($html, e($point)),
                    "[{$column}] the guidance should sit inside the instructions panel."
                );
            }
        }

        // Reviewer and admin reminders keep the guidance, but none of the LPI
        // submission requirements.
        foreach (['reviewer_count', 'progress_grading', 'final_grading'] as $column) {
            $mail = new ProjectReminderMail(
                $column,
                '2 project(s) awaiting action',
                $call,
                'Test Recipient',
                ['pending' => [['id' => 'IRCC-2024-001', 'title' => 'First']]]
            );

            $html = $mail->render();

            $this->assertStringContainsString('Instructions', $html, "[{$column}] should keep the guidance checklist.");
            $this->assertStringNotContainsString('Required signature', $html, "[{$column}] must not show the LPI instructions.");
            $this->assertStringNotContainsString('GlobalProtect', $html, "[{$column}] must not show the LPI disclaimer.");
        }
    }
}
