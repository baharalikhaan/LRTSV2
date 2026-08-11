<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Project;

class CheckReportDeadlines extends Command
{
    protected $signature = 'reports:check-deadlines';
    protected $description = 'Check report deadlines and auto-submit progress/final reports when deadlines pass';

    public function handle()
    {
        $this->info('Checking report deadlines...');

        $this->checkProgressDeadlines();
        $this->checkExtendedProgressDeadlines();
        $this->checkFinalDeadlines();

        $this->info('Deadline check complete.');
    }

    private function checkProgressDeadlines()
    {
        $now = Carbon::now();

        // Find projects that need progress report auto-submission
        // Status: progress_reviewed (reviewer has reviewed v1)
        $projects = Project::where('status', 'progress_reviewed')
            ->with('program')
            ->get();

        foreach ($projects as $project) {
            $program = $project->program;
            if (!$program) continue;

            // Check extended progress deadline first (for projects with is_extended)
            if ($project->is_extended) {
                $extendedDeadline = $program->extended_prog_rpt2_deadline ?? $program->prog_rpt2_deadline;
                if ($extendedDeadline && $now->greaterThan($extendedDeadline)) {
                    $this->info("Auto-submitting extended progress for project {$project->old_project_id}.");
                    $this->autoSubmitExtendedProgress($project);
                }
            }
        }
    }

    private function checkExtendedProgressDeadlines()
    {
        $now = Carbon::now();

        // Find projects with extended progress enabled but not yet submitted v2
        $projects = Project::where('is_extended', true)
            ->where('status', 'progress_reviewed')
            ->with('program')
            ->get();

        foreach ($projects as $project) {
            $program = $project->program;
            if (!$program) continue;

            $effectiveDeadline = $program->extended_prog_rpt2_deadline ?? $program->prog_rpt2_deadline;

            if ($effectiveDeadline && $now->greaterThan($effectiveDeadline)) {
                $this->info("Auto-submitting extended progress for project {$project->old_project_id} (deadline passed).");
                $this->autoSubmitExtendedProgress($project);
            }
        }
    }

    private function checkFinalDeadlines()
    {
        $now = Carbon::now();

        $projects = Project::where('status', 'progress_reviewed')
            ->with('program')
            ->get();

        foreach ($projects as $project) {
            $program = $project->program;
            if (!$program) continue;

            $effectiveDeadline = $program->extended_final_rpt_deadline ?? $program->final_rpt_deadline;

            if ($effectiveDeadline && $now->greaterThan($effectiveDeadline)) {
                if (!$project->hasStatus(Project::STATUS_FINAL_ADDED)) {
                    $this->info("Auto-submitting final report for project {$project->old_project_id}.");
                    $this->autoSubmitFinal($project);
                }
            }
        }
    }

    private function autoSubmitExtendedProgress(Project $project)
    {
        try {
            DB::transaction(function () use ($project) {
                if (!$project->hasStatus(Project::STATUS_PROGRESS_EXTENDED)) {
                    $project->recordStatus(Project::STATUS_PROGRESS_EXTENDED, [
                        'triggered_by' => 'extended-progress-deadline-auto',
                        'auto_submitted' => true,
                    ], null);
                }
            });
            $this->info("Project {$project->old_project_id} extended progress auto-submitted.");
        } catch (\Exception $e) {
            $this->error("Failed to auto-submit extended progress for {$project->old_project_id}: " . $e->getMessage());
        }
    }

    private function autoSubmitFinal(Project $project)
    {
        try {
            DB::transaction(function () use ($project) {
                if (!$project->hasStatus(Project::STATUS_FINAL_ADDED)) {
                    $project->recordStatus(Project::STATUS_FINAL_ADDED, [
                        'triggered_by' => 'final-deadline-auto',
                        'auto_submitted' => true,
                    ], null);
                }
            });
            $this->info("Project {$project->old_project_id} auto-submitted successfully.");
        } catch (\Exception $e) {
            $this->error("Failed to auto-submit project {$project->old_project_id}: " . $e->getMessage());
        }
    }
}
