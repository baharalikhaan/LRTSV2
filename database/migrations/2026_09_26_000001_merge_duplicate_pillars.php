<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Duplicate pillar rows created by an older seeder are merged into their
     * canonical counterpart (name-based so it works on any database):
     *
     *   Energy / Environment                        -> Energy and Environment
     *   Health                                      -> Health and Biomedical Sciences
     *   Information and Communication Technology    -> Information and Communication Technologies
     *   Social                                      -> Social Sciences and Humanities
     *
     * Sub-pillar entries are appended losslessly, all user/project assignments
     * are re-pointed first (user_pillars cascades on delete, project_pillar is
     * RESTRICT), and finally the duplicate row is removed.
     */
    private array $merges = [
        'Energy' => 'Energy and Environment',
        'Environment' => 'Energy and Environment',
        'Health' => 'Health and Biomedical Sciences',
        'Information and Communication Technology' => 'Information and Communication Technologies',
        'Social' => 'Social Sciences and Humanities',
    ];

    public function up(): void
    {
        foreach ($this->merges as $sourceName => $targetName) {
            $source = DB::table('pillars')->where('pillar', $sourceName)->first();
            $target = DB::table('pillars')->where('pillar', $targetName)->first();

            if (!$source && !$target) {
                continue;
            }

            // Target missing but source exists: just rename it into place.
            if (!$target && $source) {
                DB::table('pillars')->where('id', $source->id)->update([
                    'pillar' => $targetName,
                    'updated_at' => now(),
                ]);
                continue;
            }

            // Source already merged (idempotent re-run).
            if (!$source || $source->id === $target->id) {
                continue;
            }

            // 1. Merge sub-pillar entries (deduped, target order first).
            $targetItems = $this->splitEntries($target->subpillar);
            foreach ($this->splitEntries($source->subpillar) as $item) {
                if (!in_array($item, $targetItems, true)) {
                    $targetItems[] = $item;
                }
            }
            DB::table('pillars')->where('id', $target->id)->update([
                'subpillar' => implode("\n", $targetItems),
                'updated_at' => now(),
            ]);

            // 2. user_pillars: drop rows for users who already have the target,
            //    then re-point the rest (target must exist before source delete).
            DB::delete(
                'DELETE up FROM user_pillars up
                 INNER JOIN user_pillars keep ON keep.user_id = up.user_id AND keep.pillar_id = ?
                 WHERE up.pillar_id = ?',
                [$target->id, $source->id]
            );
            DB::table('user_pillars')->where('pillar_id', $source->id)->update(['pillar_id' => $target->id]);

            // 3. project_pillar: same treatment.
            DB::delete(
                'DELETE pp FROM project_pillar pp
                 INNER JOIN project_pillar keep ON keep.project_id = pp.project_id AND keep.pillar_id = ?
                 WHERE pp.pillar_id = ?',
                [$target->id, $source->id]
            );
            DB::table('project_pillar')->where('pillar_id', $source->id)->update(['pillar_id' => $target->id]);

            // 4. Remove the duplicate row now that nothing references it.
            DB::table('pillars')->where('id', $source->id)->delete();
        }
    }

    public function down(): void
    {
        // Merging is not reversible: the duplicate rows and their merged
        // sub-pillar entries are gone. Re-create pillars manually via the
        // Pillars admin page if a rollback is ever needed.
    }

    /**
     * Split a sub-pillar string into entries. Newline-formatted values keep
     * entries that contain commas intact; legacy comma-formatted values are
     * split on commas (same rule the admin UI uses for display).
     */
    private function splitEntries(?string $value): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $parts = preg_split('/\r?\n/', $value);
        if (count($parts) === 1) {
            $parts = preg_split('/,/', $value);
        }

        return array_values(array_filter(array_map('trim', $parts), fn ($v) => $v !== ''));
    }
};
