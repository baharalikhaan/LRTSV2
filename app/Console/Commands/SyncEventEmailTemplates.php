<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use Illuminate\Console\Command;

/**
 * The system event templates live in the database (so administrators can
 * edit them), which means a code deployment does not update their wording.
 * This command brings those rows in line with EmailTemplate::EVENTS.
 *
 * Safe by default: a row whose text differs is reported and left alone unless
 * --force is given (an administrator may have customised it).
 */
class SyncEventEmailTemplates extends Command
{
    protected $signature = 'email-templates:sync
                            {--dry-run : Only report what would change}
                            {--force   : Overwrite templates whose text differs}';

    protected $description = 'Create or update the system event email templates from EmailTemplate::EVENTS';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force  = (bool) $this->option('force');

        $created = $updated = $skipped = $inSync = 0;

        foreach (EmailTemplate::EVENTS as $key => $definition) {
            $row = EmailTemplate::where('key', $key)->first();

            if (!$row) {
                $this->line("  <info>missing</info>  {$key} — will be created");
                if (!$dryRun) {
                    EmailTemplate::create([
                        'key'      => $key,
                        'name'     => $definition['name'],
                        'category' => $definition['category'],
                        'subject'  => $definition['subject'],
                        'body'     => $definition['body'],
                    ]);
                }
                $created++;
                continue;
            }

            $dirty = $row->name !== $definition['name']
                || $row->category !== $definition['category']
                || $row->subject !== $definition['subject']
                || $row->body !== $definition['body'];

            if (!$dirty) {
                $this->line("  <info>ok</info>       {$key}");
                $inSync++;
                continue;
            }

            if (!$force) {
                $this->line("  <comment>differs</comment>  {$key} — kept (use --force to overwrite)");
                $skipped++;
                continue;
            }

            $this->line("  <info>update</info>   {$key}");
            if (!$dryRun) {
                $row->name = $definition['name'];
                $row->category = $definition['category'];
                $row->subject = $definition['subject'];
                $row->body = $definition['body'];
                $row->save();
            }
            $updated++;
        }

        $this->newLine();
        $this->line(sprintf(
            '%s%d in sync, %d created, %d updated, %d kept.',
            $dryRun ? 'Dry run: ' : '',
            $inSync,
            $created,
            $updated,
            $skipped
        ));

        if ($skipped > 0 && !$force) {
            $this->line('Re-run with --force to apply the shipped wording to the templates marked "differs".');
        }

        return self::SUCCESS;
    }
}
