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
 * What the admin's "Review Rejection" forwards to the LPI, per rejection type:
 *   report  -> resubmission e-mail (the LPI may upload a v2)
 *   ethical -> "Ethical Approval Missing" e-mail asking for the certificate to
 *              be sent to the research office address (no RTS upload)
 *   other   -> nothing at all (the review is only recorded internally)
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

        $this->forward($project)->assertOk()->assertJsonPath('success', true);

        Mail::assertQueued(GenericEmailMail::class, function (GenericEmailMail $mail) {
            return str_contains($mail->subjectLine, 'Ethical Approval Missing')
                && str_contains($mail->body, GenericEmailMail::SUPPORT_EMAIL)
                && str_contains($mail->body, 'Reason (ethical):')
                && !str_contains($mail->body, 'upload')
                && count($mail->guidance) === 1
                && str_contains($mail->guidance[0], GenericEmailMail::SUPPORT_EMAIL);
        });
    }

    public function test_other_rejection_sends_no_email_at_all()
    {
        $project = $this->project();
        if (!$project || !$this->admin()) {
            $this->markTestSkipped('No project/admin in the test database.');
        }

        $this->rejectProgress($project, 'other');

        config(['mail.enabled' => true]);
        Mail::fake();

        $this->forward($project)->assertOk()->assertJsonPath('success', true);

        $this->assertCount(0, Mail::queued(GenericEmailMail::class), 'No e-mail may be queued for an "Other" rejection.');
        $this->assertCount(0, Mail::sent(GenericEmailMail::class), 'No e-mail may be sent for an "Other" rejection.');

        // The review is still recorded, so the admin's bell task clears.
        $this->assertSame(
            \App\Models\Project::STATUS_PROGRESS_REJ_REVIEWED,
            $project->fresh()->currentWorkflowStatus()
        );
    }
}
