<?php

namespace Tests\Feature;

use App\Mail\GenericEmailMail;
use App\Models\EmailTemplate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Reviewer accept/reject consent.
 *
 * The LPI notification for these two decisions is temporarily disabled (the
 * sends are commented out in WorkflowController::submitProposalDecision), so
 * these tests guard that no email leaves the application while the decision
 * itself is still recorded correctly. Restore the "when re-enabled" assertions
 * shown in the comments if the emails are switched back on.
 */
class ReviewerDecisionEmailTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'Admin')->where('is_active', true)->first();
    }

    private function reviewer(): ?User
    {
        return User::whereIn('type', ['Reviewer', 'LPI+Reviewer'])->where('is_active', true)->first();
    }

    /** A project with an LPI and a research call. */
    private function project(): ?Project
    {
        return Project::with('program.cycle', 'lpi')
            ->whereNotNull('program_id')
            ->whereNotNull('lpi_id')
            ->orderBy('id')
            ->first();
    }

    private function seedTemplates(): void
    {
        foreach (['proposal_accepted', 'proposal_rejected'] as $key) {
            EmailTemplate::updateOrCreate(
                ['key' => $key],
                [
                    'name'     => EmailTemplate::EVENTS[$key]['name'],
                    'category' => 'notification',
                    'subject'  => EmailTemplate::EVENTS[$key]['subject'],
                    'body'     => EmailTemplate::EVENTS[$key]['body'],
                ]
            );
        }
    }

    /** Assign the reviewer (unclaimed) and put the project in the "Assigned" state. */
    private function assign(Project $project, User $reviewer, User $admin): int
    {
        DB::table('projects_reviewers')->insert([
            'project_id' => $project->id,
            'user_id'    => $reviewer->id,
            'role'       => 'Reviewer',
            'claimed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $project->recordStatus(Project::STATUS_ASSIGNED, ['triggered_by' => 'test'], $admin->id);

        return (int) DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->value('id');
    }

    public function test_accepting_the_proposal_claims_the_review_without_emailing_the_lpi()
    {
        $admin = $this->admin();
        $project = $this->project();
        $reviewer = $this->reviewer();

        if (!$admin || !$project || !$reviewer || !$project->lpi) {
            $this->markTestSkipped('Needs an admin, a project with an LPI and a reviewer.');
        }

        $rId = $this->assign($project, $reviewer, $admin);

        $this->seedTemplates();
        config(['mail.enabled' => true]);
        Mail::fake();

        $this->actingAs($reviewer)
            ->postJson(route('workflow.submit-decision'), [
                'project_id' => $project->id,
                'r_id'       => $rId,
                'accept'     => 'accepted',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // The reviewer's own assignment is claimed…
        $this->assertNotNull(
            DB::table('projects_reviewers')->where('id', $rId)->value('claimed_at'),
            'Accepting should claim the reviewer assignment.'
        );

        // …and the project moves to Claimed.
        $this->assertSame(Project::STATUS_CLAIMED, $project->fresh()->currentWorkflowStatus());

        // …but the LPI is not emailed (temporarily disabled).
        Mail::assertNothingSent();

        // When re-enabled, instead assert:
        //   Mail::assertSent(GenericEmailMail::class, fn ($mail) =>
        //       $mail->heading === 'Proposal Accepted (LPI)');
        //   and that the latest email_send_log row for $project->lpi->email
        //   has a subject starting with "Proposal Accepted".
    }

    public function test_rejecting_the_proposal_is_recorded_without_emailing_the_lpi()
    {
        $admin = $this->admin();
        $project = $this->project();
        $reviewer = $this->reviewer();

        if (!$admin || !$project || !$reviewer || !$project->lpi) {
            $this->markTestSkipped('Needs an admin, a project with an LPI and a reviewer.');
        }

        $rId = $this->assign($project, $reviewer, $admin);

        $this->seedTemplates();
        config(['mail.enabled' => true]);
        Mail::fake();

        $this->actingAs($reviewer)
            ->postJson(route('workflow.submit-decision'), [
                'project_id'    => $project->id,
                'r_id'          => $rId,
                'accept'        => 'rejected',
                'reject_reason' => 'Outside my research area',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // Audit trail + assignment removed + status recorded.
        $this->assertDatabaseHas('reviewer_rejections', [
            'project_id' => $project->id,
            'user_id'    => $reviewer->id,
        ]);
        $this->assertDatabaseMissing('projects_reviewers', ['id' => $rId]);
        $this->assertSame(
            Project::STATUS_PROPOSAL_REJECTED,
            $project->fresh()->currentWorkflowStatus()
        );

        // The LPI is not emailed (temporarily disabled).
        Mail::assertNothingSent();

        // When re-enabled, instead assert:
        //   Mail::assertSent(GenericEmailMail::class, fn ($mail) =>
        //       $mail->heading === 'Proposal Rejected (LPI)');
    }

    public function test_no_email_is_sent_when_mail_is_disabled()
    {
        $admin = $this->admin();
        $project = $this->project();
        $reviewer = $this->reviewer();

        if (!$admin || !$project || !$reviewer) {
            $this->markTestSkipped('Needs an admin, a project and a reviewer.');
        }

        $rId = $this->assign($project, $reviewer, $admin);

        config(['mail.enabled' => false]);
        Mail::fake();

        $this->actingAs($reviewer)
            ->postJson(route('workflow.submit-decision'), [
                'project_id' => $project->id,
                'r_id'       => $rId,
                'accept'     => 'accepted',
            ])
            ->assertOk();

        Mail::assertNothingSent();
    }
}
