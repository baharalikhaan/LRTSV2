<?php

namespace Tests\Feature;

use App\Mail\GenericEmailMail;
use App\Models\EmailTemplate;
use App\Models\Project;
use App\Models\User;
use App\Services\EventMailService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Workflow notification emails (reviewer assigned / un-assigned, proposal
 * accepted / rejected, report received / evaluated…).
 *
 * They share the rich email layout used by the Research Call Summary
 * reminders: academic heading, "what is required" bullets, project details
 * with deadlines and a call-to-action. Verified with Mail::fake() so no
 * real email leaves the application.
 */
class WorkflowNotificationMailTest extends TestCase
{
    use DatabaseTransactions;

    private function project(): ?Project
    {
        return Project::with(['program.cycle', 'program.grant', 'lpi'])
            ->whereNotNull('program_id')
            ->orderBy('id')
            ->first();
    }

    public function test_generic_email_renders_project_details_and_guidance()
    {
        $project = $this->project();
        if (!$project) {
            $this->markTestSkipped('No project in the test database.');
        }

        $mail = new GenericEmailMail(
            'Reviewer Assignment Withdrawn — ' . ($project->old_project_id ?: $project->id),
            "You have been withdrawn from the review of the project below.",
            'Research Tracking System',
            'Dr. Test Reviewer',
            null,
            null,
            [
                'heading'      => 'Reviewer Assignment Withdrawn',
                'project'      => GenericEmailMail::projectContext($project),
                'action_label' => 'Open the project',
                'guidance'     => [
                    'The project has been returned to the research office for reassignment.',
                    'Any evaluation you already submitted in RTS is retained.',
                ],
            ]
        );

        $html = $mail->build()->render();

        $this->assertStringContainsString('Reviewer Assignment Withdrawn', $html);
        $this->assertStringContainsString('What is required', $html);
        $this->assertStringContainsString('Project details', $html);
        $this->assertStringContainsString(e((string) ($project->old_project_id ?: $project->id)), $html);
        $this->assertStringContainsString(e($project->title), $html);
        $this->assertStringContainsString('Open the project', $html);

        // Grant name and cycle year are part of the details table.
        $context = GenericEmailMail::projectContext($project);

        if (!empty($context['grant_name'])) {
            $this->assertStringContainsString('Grant', $html);
            $this->assertStringContainsString(e($context['grant_name']), $html);
        }

        if (!empty($context['cycle_year'])) {
            $this->assertStringContainsString('Year', $html);
            $this->assertStringContainsString(e((string) $context['cycle_year']), $html);
        }
    }

    public function test_event_mail_is_sent_with_the_rich_layout()
    {
        $project = $this->project();
        if (!$project) {
            $this->markTestSkipped('No project in the test database.');
        }

        // The test database may not be seeded with the system templates.
        EmailTemplate::updateOrCreate(
            ['key' => 'reviewer_assigned'],
            [
                'name'     => 'Reviewer Assigned',
                'category' => 'notification',
                'subject'  => EmailTemplate::EVENTS['reviewer_assigned']['subject'],
                'body'     => EmailTemplate::EVENTS['reviewer_assigned']['body'],
            ]
        );

        $reviewer = User::where('type', 'like', '%Reviewer%')->first();
        $actor    = User::where('type', 'like', '%Admin%')->first();

        if (!$reviewer || !$actor) {
            $this->markTestSkipped('No reviewer or admin in the test database.');
        }

        config(['mail.enabled' => true]);
        Mail::fake();

        app(EventMailService::class)->send('reviewer_assigned', $reviewer, $project, $actor);

        Mail::assertSent(GenericEmailMail::class, function (GenericEmailMail $mail) use ($project) {
            // The rendered view cannot be produced while Mail is faked, so the
            // mailable's own payload is asserted instead.
            return str_contains($mail->subjectLine, 'Reviewer Assignment Confirmed')
                && $mail->heading === 'Reviewer Assigned'
                && $mail->actionLabel === 'Open the project'
                && count($mail->actionPoints) >= 3
                && $mail->disclaimer === GenericEmailMail::DISCLAIMER
                && ($mail->project['old_project_id'] ?? null) == ($project->old_project_id ?: $project->id);
        });
    }

    /** The assignment email is laid out like the reminders. */
    public function test_reviewer_assignment_email_has_details_action_and_disclaimer()
    {
        $project = $this->project();
        if (!$project) {
            $this->markTestSkipped('No project in the test database.');
        }

        $mail = new GenericEmailMail(
            'Reviewer Assignment Confirmed — ' . ($project->old_project_id ?: $project->id),
            'The project below has been assigned to you for evaluation. Please review the proposal and record your decision in RTS.',
            'Research Tracking System',
            'Dr. Test Reviewer',
            null,
            null,
            [
                'heading'       => 'Reviewer Assigned',
                'project'       => EventMailService::projectDetailsFor('reviewer_assigned', $project),
                'action_label'  => 'Open the project',
                'action_points' => EventMailService::ACTION_POINTS['reviewer_assigned'],
                'disclaimer'    => GenericEmailMail::DISCLAIMER,
                'support_email' => GenericEmailMail::SUPPORT_EMAIL,
            ]
        );

        $html = $mail->build()->render();

        // Project details table — identity only.
        $this->assertStringContainsString('Project details', $html);
        $this->assertStringContainsString(e((string) ($project->old_project_id ?: $project->id)), $html);
        $this->assertStringContainsString(e($project->title), $html);

        // The grant, the cycle year and the report deadlines stay out of it.
        $this->assertStringNotContainsString('>Grant<', $html, 'The assignment email must not show a grant row.');
        $this->assertStringNotContainsString('>Year<', $html, 'The assignment email must not show a cycle year row.');
        $this->assertStringNotContainsString('Deadlines', $html, 'The assignment email must not show the report deadlines.');

        // Action required steps
        $this->assertStringContainsString('Action required', $html);
        foreach (EventMailService::ACTION_POINTS['reviewer_assigned'] as $point) {
            $this->assertStringContainsString(e($point), $html);
        }

        // Disclaimer, after the call-to-action
        $this->assertStringContainsString('Disclaimer', $html);
        $this->assertStringContainsString('GlobalProtect', $html);

        // Support and queries mailbox
        $this->assertStringContainsString('Support and queries', $html);
        $this->assertStringContainsString('mailto:' . GenericEmailMail::SUPPORT_EMAIL, $html);

        // Order: details → action required → button → disclaimer
        $this->assertLessThan(strpos($html, 'Action required'), strpos($html, 'Project details'));
        $this->assertLessThan(strpos($html, 'Open the project'), strpos($html, 'Action required'));
        $this->assertLessThan(strpos($html, 'GlobalProtect'), strpos($html, 'Open the project'));
    }

    /**
     * The un-assignment email is currently disabled on purpose (the send is
     * commented out in WorkflowController::unassignReviewer). When it is
     * reinstated, restore the assertions noted at the end of this test.
     */
    public function test_reviewer_unassignment_sends_no_email_while_disabled()
    {
        $admin = User::where('type', 'Admin')->where('is_active', true)->first();
        $project = Project::has('reviewers')->whereNotNull('program_id')->orderBy('id')->first();

        if (!$admin || !$project) {
            $this->markTestSkipped('Needs an admin and a project with an assigned reviewer.');
        }

        $reviewer = $project->reviewers->first();

        config(['mail.enabled' => true]);
        Mail::fake();

        $this->actingAs($admin)
            ->post(route('workflow.unassign-reviewer'), [
                'project_id'  => $project->id,
                'reviewer_id' => $reviewer->id,
                'reason'      => 'Reviewer availability',
            ])
            ->assertOk();

        // The reviewer is still removed from the project…
        $this->assertDatabaseMissing('projects_reviewers', [
            'project_id' => $project->id,
            'user_id'    => $reviewer->id,
        ]);

        // …but no withdrawal email is queued or sent to the removed reviewer
        // (other project notifications may still go out to the LPI).
        Mail::assertNotSent(GenericEmailMail::class, fn (GenericEmailMail $mail) => $this->addressedTo($mail, $reviewer->email));
        Mail::assertNotQueued(GenericEmailMail::class, fn (GenericEmailMail $mail) => $this->addressedTo($mail, $reviewer->email));

        // When reinstated, assert instead:
        //   Mail::assertQueued(GenericEmailMail::class, fn ($mail) =>
        //       $this->addressedTo($mail, $reviewer->email)
        //       && $mail->heading === 'Reviewer Assignment Withdrawn'
        //       && $mail->disclaimer === GenericEmailMail::DISCLAIMER
        //       && $mail->supportEmail === GenericEmailMail::SUPPORT_EMAIL);
    }

    /** Whether the mailable is addressed to the given address. */
    private function addressedTo(GenericEmailMail $mail, string $email): bool
    {
        foreach ($mail->to as $recipient) {
            if (($recipient['address'] ?? null) === $email) {
                return true;
            }
        }

        return false;
    }

    public function test_event_template_subjects_are_academic_and_project_aware()
    {
        foreach (EmailTemplate::EVENTS as $key => $template) {
            $this->assertStringContainsString(
                '*old_project_id*',
                $template['subject'],
                "Template [{$key}] should identify the project in its subject."
            );
            $this->assertStringContainsString(
                '*program_title*',
                $template['subject'],
                "Template [{$key}] should identify the research call in its subject."
            );
            $this->assertStringContainsString(
                '—',
                $template['subject'],
                "Template [{$key}] should use the academic em-dash subject format."
            );
            $this->assertStringNotContainsString(
                'Dear *name*',
                $template['body'],
                "Template [{$key}] must not include the greeting — the layout renders it."
            );
        }
    }
}
