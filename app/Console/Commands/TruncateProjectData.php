<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TruncateProjectData extends Command
{
    protected $signature = 'truncate:project-data {--force : Skip confirmation prompt}';

    protected $description = 'Truncate programs, projects, and all project-related tables';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will DELETE ALL data from programs, projects, and related tables. Continue?', false)) {
                $this->info('Aborted.');
                return 0;
            }
        }

        $tables = [
            // Grandchild tables first
            'project_students_details',

            // Direct children of projects
            'commitments',
            'projects_reviewers',
            'project_college',
            'project_contributions',
            'project_outcomes',
            'project_pillar',
            'project_publications',
            'project_researchers',
            'project_students',
            'project_submissions',
            'status_histories',
            'final_report_grading',
            'progress_report_grading',
            'reviewer_rejections',

            // Core
            'projects',

            // Parent
            'programs',

            // Additional
            'announcement',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $truncated = 0;
        foreach ($tables as $table) {
            $exists = DB::select("SHOW TABLES LIKE '{$table}'");
            if (!empty($exists)) {
                DB::statement("TRUNCATE TABLE `{$table}`");
                $this->line("  Truncated: <info>{$table}</info>");
                $truncated++;
            } else {
                $this->line("  Skipped (not found): <comment>{$table}</comment>");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Clear uploads folder
        $uploadPath = storage_path('app/uploads');
        if (File::isDirectory($uploadPath)) {
            File::cleanDirectory($uploadPath);
            // Recreate .gitkeep if needed
            if (!File::exists($uploadPath . '/.gitkeep')) {
                File::put($uploadPath . '/.gitkeep', '');
            }
            $this->info('Cleared: storage/app/uploads');
        }

        $this->info("Done. {$truncated} table(s) truncated.");
        return 0;
    }
}
