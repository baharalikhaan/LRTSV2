<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Notification bell pending tasks (layouts/app.blade.php):
 *
 *  - Admin: unreviewed report-type rejections are listed ("awaiting your
 *    review") so the admin can add his comment and send the report back
 *    to the LPI — mirrors Project::hasUnreviewedRejection() in SQL.
 *    Ethical/other rejections skip the review step and are never listed.
 *  - LPI: a rejection sits on his bell while the workflow is in a
 *    rejection/admin-review state and disappears as soon as he resubmits.
 *
 * Runs against the dedicated test database (phpunit.xml -> rtsnew_test) and
 * rolls back every test via DatabaseTransactions, so no real data is touched.
 */
class NotificationBellTest extends TestCase
{
    use DatabaseTransactions;

    /** Mirror of the admin bell counter (PHP side) — SQL parity is asserted against this. */
    private function adminRejectionReviewCount(): int
    {
        return Project::all()->filter(function ($p) {
            if ($p->hasStatus(Project::STATUS_GRADED)) {
                return false;
            }
            return $p->hasUnreviewedRejection('progress')
                || $p->hasUnreviewedRejection('progress2')
                || $p->hasUnreviewedRejection('final');
        })->count();
    }

    private function adminLabel(int $n): string
    {
        return $n . ' rejected report' . ($n > 1 ? 's' : '') . ' awaiting your review';
    }

    private function latestStatus(Project $p): ?string
    {
        return $p->statusHistories()->orderByDesc('id')->value('status');
    }

    private function latestIsRejected(Project $p): bool
    {
        return in_array($this->latestStatus($p), [
            Project::STATUS_PROGRESS_REJECTED,
            Project::STATUS_PROGRESS_REJ_REVIEWED,
            Project::STATUS_PROGRESS2_REJECTED,
            Project::STATUS_PROGRESS2_REJ_REVIEWED,
            Project::STATUS_FINAL_REJECTED,
            Project::STATUS_FINAL_REJ_REVIEWED,
        ], true);
    }

    /** A report-type rejection that nobody reviewed yet shows up in the admin bell. */
    public function test_admin_bell_shows_unreviewed_rejection()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        $project = Project::all()->first(function ($p) {
            return !$p->hasStatus(Project::STATUS_GRADED)
                && !$p->hasUnreviewedRejection('progress')
                && !$p->hasUnreviewedRejection('progress2')
                && !$p->hasUnreviewedRejection('final');
        });
        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a project without pending rejections.');
        }

        $before = $this->adminRejectionReviewCount();

        $project->recordStatus(Project::STATUS_PROGRESS_REJECTED, [
            'rejection_type' => 'report',
            'comment'        => 'Bell test rejection.',
        ], $admin->id);

        $after = $this->adminRejectionReviewCount();
        $this->assertSame($before + 1, $after, 'The seeded rejection must enter the count.');

        $html = $this->actingAs($admin)
            ->get(route('projects.available'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            $this->adminLabel($after),
            $html,
            'The admin bell must list the unreviewed rejection.'
        );
    }

    /** Ethical rejections skip the admin review step — they never hit the bell. */
    /** Ethical/Other rejections ALSO hit the admin (forwarded to the LPI). */
    public function test_admin_bell_includes_ethical_rejections()
    {
        $admin = User::where('type', 'Admin')->where('is_active', 1)->first();
        $project = Project::all()->first(function ($p) {
            return !$p->hasStatus(Project::STATUS_GRADED)
                && !$p->hasUnreviewedRejection('progress')
                && !$p->hasUnreviewedRejection('progress2')
                && !$p->hasUnreviewedRejection('final');
        });
        if (!$admin || !$project) {
            $this->markTestSkipped('Need an admin and a project without pending rejections.');
        }

        $before = $this->adminRejectionReviewCount();

        $project->recordStatus(Project::STATUS_PROGRESS_REJECTED, [
            'rejection_type' => 'ethical',
            'comment'        => 'Missing valid approval.',
        ], $admin->id);

        $this->assertSame(
            $before + 1,
            $this->adminRejectionReviewCount(),
            'Ethical rejections route through the admin too, so they must be listed.'
        );

        // Once the admin forwards it, it no longer counts as unreviewed.
        $this->actingAs($admin)->postJson("/workflow/review-rejection/{$project->id}", [
            'project_id'  => $project->id,
            'action'      => 'send_to_lpi',
            'message'     => 'Please re-send the ethical approval.',
            'report_type' => 'progress',
        ])->assertOk();

        $this->assertSame(
            $before,
            $this->adminRejectionReviewCount(),
            'A forwarded rejection no longer counts as awaiting review.'
        );
    }

    /** The LPI sees the rejection until he resubmits (then it disappears). */
    public function test_lpi_bell_shows_rejection_until_resubmission()
    {
        $project = Project::whereNotNull('lpi_id')->get()->first(function ($p) {
            return $p->lpi
                && $p->lpi->type === 'LPI'
                && !$this->latestIsRejected($p);
        });
        if (!$project) {
            $this->markTestSkipped('No LPI-owned project without a pending rejection.');
        }
        $lpi = $project->lpi;

        $label = function (int $n): string {
            return $n . ' rejected report' . ($n > 1 ? 's' : '') . ' needing resubmission';
        };
        $count = function () use ($lpi): int {
            return Project::where('lpi_id', $lpi->id)->get()
                ->filter(fn ($p) => $this->latestIsRejected($p))->count();
        };

        $baseline = $count();
        $html = $this->actingAs($lpi)->get(route('projects.available'))->assertOk()->getContent();
        if ($baseline > 0) {
            $this->assertStringContainsString($label($baseline), $html);
        } else {
            $this->assertStringNotContainsString($label(1), $html, 'No rejected-report task while the count is zero.');
        }

        // A rejection lands on his bell …
        $project->recordStatus(Project::STATUS_PROGRESS_REJECTED, [
            'rejection_type' => 'report',
            'comment'        => 'Bell test rejection.',
        ], $lpi->id);
        $afterReject = $count();
        $this->assertSame($baseline + 1, $afterReject, 'The rejection must enter the LPI count.');
        $html = $this->actingAs($lpi)->get(route('projects.available'))->assertOk()->getContent();
        $this->assertStringContainsString($label($afterReject), $html,
            'The LPI bell must list his rejected report.');

        // … and disappears as soon as he resubmits the progress report.
        $project->recordStatus(Project::STATUS_PROGRESS_ADDED, [], $lpi->id);
        $afterResubmit = $count();
        $this->assertSame($baseline, $afterResubmit, 'Resubmission must clear the rejection from the count.');
        $html = $this->actingAs($lpi)->get(route('projects.available'))->assertOk()->getContent();
        if ($afterResubmit > 0) {
            $this->assertStringContainsString($label($afterResubmit), $html);
        } else {
            $this->assertStringNotContainsString($label(1), $html, 'No rejected-report task while the count is zero.');
        }
    }

    /**
     * Reviewer bell: "ready to grade" is the CLAIMED + report-ready subset,
     * not a duplicate of the "pending your review" queue.
     *
     * (Both tasks used to run the identical assigned-and-not-Graded query, so
     * they always showed the same projects.)
     */
    public function test_reviewer_ready_to_grade_is_not_a_duplicate_of_pending_review()
    {
        $reviewer = User::where('type', 'Reviewer')->where('is_active', 1)
            ->whereHas('reviewedProjects')->orderBy('id')->first();
        if (!$reviewer) {
            $this->markTestSkipped('No reviewer with an assigned project.');
        }

        $project = Project::whereHas('reviewers', function ($q) use ($reviewer) {
            $q->where('user_id', $reviewer->id);
        })->whereNotNull('program_id')->whereDoesntHave('statusHistories', function ($q) {
            $q->where('status', Project::STATUS_GRADED);
        })->first();

        if (!$project) {
            $this->markTestSkipped('Reviewer has no assigned project.');
        }

        // The bell only counts projects in a visible program — make it so.
        DB::table('programs')->where('id', $project->program_id)->update(['is_visible' => 1]);

        // Clean slate: un-claim EVERYTHING for this reviewer so nothing is
        // "ready to grade" yet (rolled back by DatabaseTransactions).
        DB::table('projects_reviewers')
            ->where('user_id', $reviewer->id)
            ->update(['claimed_at' => null]);

        $html = $this->actingAs($reviewer)->get(route('projects.available'))->assertOk()->getContent();
        $this->assertStringContainsString('pending your review', $html);
        $this->assertStringNotContainsString(
            'ready to grade',
            $html,
            'An unclaimed project must not be counted as ready to grade.'
        );

        // Claim it and give it a progress report whose deadline has passed.
        DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $reviewer->id)
            ->update(['claimed_at' => now()]);

        if (!$project->submissions()->where('type', 'progress')->exists()) {
            $project->submissions()->create([
                'type'              => 'progress',
                'file_path'         => 'uploads/test/' . $project->id . '_progress.pdf',
                'stored_filename'   => $project->id . '_progress.pdf',
                'original_filename' => 'progress.pdf',
                'version'           => 1,
                'user_id'           => $project->lpi_id ?? $reviewer->id,
            ]);
        }
        DB::table('programs')->where('id', $project->program_id)
            ->update(['prog_rpt_deadline' => now()->subDay()]);

        $html2 = $this->actingAs($reviewer)->get(route('projects.available'))->assertOk()->getContent();
        $this->assertStringContainsString(
            'ready to grade',
            $html2,
            'A claimed project with a ready report must be counted as ready to grade.'
        );
    }
}
