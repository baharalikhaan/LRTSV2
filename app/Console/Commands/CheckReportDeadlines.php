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

            $effectiveDeadline = $program->final_rpt_deadline;

            if ($effectiveDeadline && $now->greaterThan($effectiveDeadline)) {
                // Check file existence instead of status (buttons removed, files uploaded directly)
                $hasFinalFiles = $project->submissions()->where('type', 'final')->count() > 0;
                if (!$hasFinalFiles && !$project->hasStatus(Project::STATUS_FINAL_ADDED)) {
                    $this->info("Auto-submitting final report for project {$project->old_project_id}.");
                    $this->autoSubmitFinal($project);
                }
            }
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
