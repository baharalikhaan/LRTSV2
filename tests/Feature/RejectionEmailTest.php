<?php

namespace Tests\Feature;

use App\Mail\GenericEmailMail;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The three emails the admin can send to an LPI when forwarding a rejected
 * report: a plain resubmission request, an ethical-approval request and an
 * "other" clarification request (the third one used to reuse the ethical mail).
 *
 * Verified with Mail::fake() so NO real email is ever sent.
 */
class RejectionEmailTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'like', '%Admin%')->where('is_active', 1)->orderBy('id')->first();
    }

    private function project(): ?Project
    {
        return Project::whereNotNull('lpi_id')
            ->whereHas('lpi')
            ->whereHas('program')
            ->orderBy('id')
            ->first();
    }

    /** Park the project on an unreviewed progress rejection of the given type. */
    private function rejectProgress(Project $project, string $type): void
    {
        DB::table('status_histories')->where('project_id', $project->id)->delete();
        $project->recordStatus(Project::STATUS_PROGRESS_REJECTED, [
            'rejection_type'   => $type,
            'rejection_reason' => 'Reviewer test reason.',
            'comment'          => 'Reviewer test reason.',
        ], null);
        $project->refresh();
    }

    private function forward(Project $project, string $message = 'Please clarify the highlighted points.')
    {
        return $this->actingAs($this->admin())->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => $message,
            'report_type' => 'progress',
        ]);
    }

    public function test_report_rejection_sends_the_resubmission_email()
    {
        $project = $this->project();
        if (!$project || !$this->admin()) {
            $this->markTestSkipped('No project/admin in the test database.');
        }

        $this->rejectProgress($project, 'report');

        config(['mail.enabled' => true]);
        Mail::fake();

        $this->forward($project)->assertOk();

        Mail::assertQueued(GenericEmailMail::class, function (GenericEmailMail $mail) use ($project) {
            return str_contains($mail->subjectLine, 'Progress Report Resubmission Required')
                && $mail->heading === 'Progress Report Resubmission Required'
                && str_contains($mail->body, 'requires resubmission')
                && str_contains($mail->body, 'Message from the research office:')
                && str_contains($mail->subjectLine, (string) ($project->old_project_id ?: $project->id));
        });
    }

    public function test_ethical_rejection_sends_the_ethical_approval_email()
    {
        $project = $this->project();
        if (!$project || !$this->admin()) {
            $this->markTestSkipped('No project/admin in the test database.');
        }

        $this->rejectProgress($project, 'ethical');

        config(['mail.enabled' => true]);
        Mail::fake();

        $this->forward($project)->assertOk();

        Mail::assertQueued(GenericEmailMail::class, function (GenericEmailMail $mail) {
            return $mail->subjectLine === 'Ethical Approval Documentation Required — '
                    . ($mail->project['old_project_id'] ?? '') . ' (' . ($mail->project['program_title'] ?? '') . ')'
                && $mail->heading === 'Ethical Approval Documentation Required'
                && str_contains($mail->body, 'Reason (ethical):')
                && in_array('Upload the current ethical approval document for the project from the project page.', $mail->guidance, true);
        });
    }

    public function test_other_rejection_sends_a_clarification_email_not_the_ethical_one()
    {
        $project = $this->project();
        if (!$project || !$this->admin()) {
            $this->markTestSkipped('No project/admin in the test database.');
        }

        $this->assertNotNull(
            \App\Models\EmailTemplate::findByKey('rejection_other'),
            'The rejection_other email template must exist (see the migration).'
        );

        $this->rejectProgress($project, 'other');

        config(['mail.enabled' => true]);
        Mail::fake();

        $this->forward($project)->assertOk();

        Mail::assertQueued(GenericEmailMail::class, function (GenericEmailMail $mail) {
            return $mail->heading === 'Progress Report Clarification Required'
                && str_contains($mail->subjectLine, 'Progress Report Clarification Required')
                && str_contains($mail->subjectLine, '— ')
                && str_contains($mail->body, 'Reason (other):')
                && !str_contains($mail->body, 'ethical approval documentation')
                && !str_contains($mail->subjectLine, 'Ethical Approval')
                && in_array('The Progress Report itself does not need to be uploaded again.', $mail->guidance, true);
        });
    }

    public function test_other_rejection_email_uses_the_editable_template()
    {
        $project = $this->project();
        if (!$project || !$this->admin()) {
            $this->markTestSkipped('No project/admin in the test database.');
        }

        $template = \App\Models\EmailTemplate::findByKey('rejection_other');
        if (!$template) {
            $this->markTestSkipped('The rejection_other template is missing.');
        }

        // Admin rewrites the template from the Email Templates page.
        $template->update([
            'subject' => 'Custom Clarification — *old_project_id*',
            'body'    => "Custom body for *report_label*.\n\nOffice note: *admin_message*",
        ]);

        $this->rejectProgress($project, 'other');

        config(['mail.enabled' => true]);
        Mail::fake();

        $this->forward($project, 'Please clarify the budget figures.')->assertOk();

        Mail::assertQueued(GenericEmailMail::class, function (GenericEmailMail $mail) use ($project) {
            return str_starts_with($mail->subjectLine, 'Custom Clarification — ')
                && str_contains($mail->body, 'Custom body for Progress Report.')
                && str_contains($mail->body, 'Please clarify the budget figures.');
        });
    }
}
