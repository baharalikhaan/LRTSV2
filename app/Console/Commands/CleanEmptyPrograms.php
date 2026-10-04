<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanEmptyPrograms extends Command
{
    protected $signature = 'clean:empty-programs {--dry-run : Preview without deleting}';

    protected $description = 'Delete programs (research calls) that have 0 projects';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $emptyPrograms = DB::table('programs')
            ->select('programs.id', 'programs.program_title')
            ->leftJoin('projects', 'projects.program_id', '=', 'programs.id')
            ->groupBy('programs.id', 'programs.program_title')
            ->havingRaw('COUNT(projects.id) = 0')
            ->get();

        if ($emptyPrograms->isEmpty()) {
            $this->info('No empty programs found.');
            return 0;
        }

        $this->info("Found <comment>{$emptyPrograms->count()}</comment> programs with 0 projects:");
        $this->newLine();

        foreach ($emptyPrograms as $p) {
            $this->line("  <comment>[{$p->id}]</comment> {$p->program_title}");
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN — no deletions made.');
            return 0;
        }

        if (!$this->confirm('Delete these programs?')) {
            $this->info('Aborted.');
            return 0;
        }

        $ids = $emptyPrograms->pluck('id')->toArray();
        DB::table('programs')->whereIn('id', $ids)->delete();

        $this->info("Deleted <comment>" . count($ids) . "</comment> programs.");
        return 0;
    }
}
