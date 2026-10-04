<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportLegacyFiles extends Command
{
    protected $signature = 'import:legacy-files {--dry-run : Show what would be done without moving files}';
    protected $description = 'Move and rename legacy uploaded files into the new storage structure';

    private array $stats = [
        'moved'     => 0,
        'skipped'   => 0,
        'no_project'=> 0,
        'errors'    => 0,
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $legacyBase = base_path('uploads');
        $storageBase = storage_path('app/uploads');

        if (!is_dir($legacyBase)) {
            $this->error("Legacy uploads folder not found: {$legacyBase}");
            return 1;
        }

        // Build a lookup: normalized_old_project_id => project record
        // Also build a "compact" lookup (all non-alphanumeric stripped) for fuzzy matching
        $projects = DB::table('projects')
            ->whereNotNull('program_id')
            ->whereNotNull('old_project_id')
            ->get()
            ->keyBy(function ($p) {
                return $this->normalizeOldId($p->old_project_id);
            });

        // Extra index: compact form (only alphanumeric) for matching IDs with missing separators
        $projectsByCompact = $projects->keyBy(function ($p) {
            return $this->compactId($p->old_project_id);
        });

        // Build program lookup: program_id => {grant_code, cycle_year}
        $programs = DB::table('programs')
            ->join('grants', 'programs.grant_id', '=', 'grants.id')
            ->join('cycle_configs', 'programs.cycle_id', '=', 'cycle_configs.id')
            ->select('programs.id as program_id', 'grants.grant_code', 'cycle_configs.year as cycle_year')
            ->get()
            ->keyBy('program_id');

        $this->info("Found " . count($projects) . " projects with program assigned.");
        $this->info("Found " . count($programs) . " programs.");
        $this->newLine();

        $folderMap = [
            'lpi_project_proposals' => 'proposal',
            'progress_reports'      => 'progress',
            'readiness_reports'     => 'readiness',
            'final_reports'         => 'final',
            'ethical_approvals'     => 'ethical',
        ];

        foreach ($folderMap as $sourceFolder => $type) {
            $sourceDir = $legacyBase . '/' . $sourceFolder;
            if (!is_dir($sourceDir)) {
                $this->line("  [<info>SKIP</info>] Folder not found: {$sourceFolder}");
                continue;
            }
            $this->line("  Processing: <info>{$sourceFolder}</info> (type: {$type})");
            $this->processFolder($sourceDir, $type, $projects, $projectsByCompact, $programs, $storageBase, $dryRun);
        }

        $this->newLine();
        $this->info("=== Import Complete ===");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Moved', $this->stats['moved']],
                ['Skipped (duplicate dest)', $this->stats['skipped']],
                ['No matching project', $this->stats['no_project']],
                ['Errors', $this->stats['errors']],
            ]
        );

        return 0;
    }

    private function processFolder(
        string $sourceDir,
        string $type,
        $projects,
        $projectsByCompact,
        $programs,
        string $storageBase,
        bool $dryRun
    ): void {
        $allFiles = $this->getAllPdfFiles($sourceDir);

        foreach ($allFiles as $filePath) {
            $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $filePath);
            $fileName = basename($filePath);

            // Try to resolve the old_project_id and version from this file
            $resolved = $this->resolveProjectId($relativePath, $fileName, $type, $projects, $projectsByCompact);
            if ($resolved === null) {
                // Try the normalized full path (handles \ in filenames split into dirs)
                $resolved = $this->resolveFromPath($relativePath, $type, $projects, $projectsByCompact);
            }

            if ($resolved === null) {
                $this->line("    [<comment>SKIP</comment>] No project for: {$relativePath}");
                $this->stats['no_project']++;
                continue;
            }

            ['old_project_id' => $rawOldId, 'version' => $version, 'project' => $project] = $resolved;

            if (!isset($programs[$project->program_id])) {
                $this->line("    [<comment>SKIP</comment>] No program for project_id={$project->id}: {$fileName}");
                $this->stats['no_project']++;
                continue;
            }

            $prog = $programs[$project->program_id];

            $typeFolderMap = [
                'proposal' => 'proposals',
                'progress' => 'progress_reports',
                'readiness'=> 'readiness_reports',
                'final'    => 'final_reports',
                'ethical'  => 'ethical_approvals',
            ];
            $typeFolder = $typeFolderMap[$type];

            $cleanOldId = str_replace(['/', '\\'], '', $rawOldId);
            $versionSuffix = $version > 0 ? '_v' . $version : '';
            $targetFileName = $cleanOldId . '_' . $type . $versionSuffix . '.pdf';

            $targetDir = 'uploads/' . $prog->cycle_year . '/' . $prog->grant_code . '/' . $typeFolder;
            $fullTargetDir = $storageBase . '/' . $prog->cycle_year . '/' . $prog->grant_code . '/' . $typeFolder;
            $fullTargetPath = $fullTargetDir . '/' . $targetFileName;

            if (file_exists($fullTargetPath)) {
                $this->line("    [<comment>DUP</comment>] Already exists: {$targetDir}/{$targetFileName}");
                $this->stats['skipped']++;
                continue;
            }

            if ($dryRun) {
                $this->line("    [<info>DRY</info>] {$relativePath} -> {$targetDir}/{$targetFileName}");
                $this->stats['moved']++;
                continue;
            }

            if (!is_dir($fullTargetDir)) {
                mkdir($fullTargetDir, 0755, true);
            }

            if (!copy($filePath, $fullTargetPath)) {
                $this->line("    [<error>ERR</error>] Failed to copy: {$relativePath}");
                $this->stats['errors']++;
                continue;
            }

            if ($type !== 'proposal' && $type !== 'ethical') {
                $this->upsertSubmission($project, $type, $version, $targetFileName, $targetDir . '/' . $targetFileName);
            }

            if ($type === 'proposal') {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['proposal_filename' => $targetFileName]);
            }

            $this->line("    [<info>OK</info>] {$relativePath} -> {$targetDir}/{$targetFileName}");
            $this->stats['moved']++;
        }
    }

    /**
     * Try to resolve project ID from the basename of the file.
     */
    private function resolveProjectId(string $relativePath, string $fileName, string $type, $projects, $projectsByCompact): ?array
    {
        $result = $this->extractProjectId($fileName, $type);
        if ($result === null) {
            return null;
        }

        $normalizedId = $this->normalizeOldId($result['old_project_id']);
        if (isset($projects[$normalizedId])) {
            return [...$result, 'project' => $projects[$normalizedId]];
        }

        // Fuzzy match: compact form (strip all non-alphanumeric)
        $compact = $this->compactId($result['old_project_id']);
        if (isset($projectsByCompact[$compact])) {
            $project = $projectsByCompact[$compact];
            // Use the canonical old_project_id from DB
            return [
                'old_project_id' => $project->old_project_id,
                'version' => $result['version'],
                'project' => $project,
            ];
        }

        return null;
    }

    /**
     * Reconstruct old_project_id from the full relative path by:
     * 1. Normalizing separators to /
     * 2. Stripping .pdf
     * 3. Stripping the first component (grant folder name)
     * 4. Running extractProjectId-style cleanup on the remainder
     *
     * Then look up in projects DB.
     */
    private function resolveFromPath(string $relativePath, string $type, $projects, $projectsByCompact): ?array
    {
        // Normalize to forward slashes, strip .pdf
        $normalized = str_replace('\\', '/', $relativePath);
        $normalized = preg_replace('/\.pdf$/i', '', $normalized);

        $parts = explode('/', $normalized);
        if (count($parts) < 2) {
            return null;
        }

        // Strip first component (grant folder name)
        $rest = implode('/', array_slice($parts, 1));

        // Extract version from [v], [v1], [v2], etc. at end
        $version = 0;
        if (preg_match('/\[v(\d*)\]$/i', $rest, $vm)) {
            $version = isset($vm[1]) && $vm[1] !== '' ? (int) $vm[1] : 1;
            $rest = substr($rest, 0, -strlen($vm[0]));
        }

        // Strip " Application" and " Application - Copy" suffixes
        $rest = preg_replace('/\s+Application(\s*-\s*Copy)?$/i', '', $rest);

        // Strip trailing _1, _2, _3 for ethical approvals
        if ($type === 'ethical') {
            $rest = preg_replace('/_\d+$/', '', $rest);
        }

        $candidateId = trim($rest);
        if (empty($candidateId)) {
            return null;
        }

        // The candidate now uses / where the DB might have / or - where the DB has /
        // Normalize and try lookup
        $normalizedId = $this->normalizeOldId($candidateId);
        if (isset($projects[$normalizedId])) {
            return [
                'old_project_id' => $candidateId,
                'version' => $version,
                'project' => $projects[$normalizedId],
            ];
        }

        // Try with - instead of / (some IDs use - where DB has /)
        $altId = str_replace('/', '-', $candidateId);
        $normalizedAlt = $this->normalizeOldId($altId);
        if (isset($projects[$normalizedAlt])) {
            return [
                'old_project_id' => $projects[$normalizedAlt]->old_project_id,
                'version' => $version,
                'project' => $projects[$normalizedAlt],
            ];
        }

        // Fuzzy match: compact form
        $compact = $this->compactId($candidateId);
        if (isset($projectsByCompact[$compact])) {
            $project = $projectsByCompact[$compact];
            return [
                'old_project_id' => $project->old_project_id,
                'version' => $version,
                'project' => $project,
            ];
        }

        return null;
    }

    /**
     * Extract old_project_id and version from a filename.
     */
    private function extractProjectId(string $fileName, string $type): ?array
    {
        if (!preg_match('/\.pdf$/i', $fileName)) {
            return null;
        }

        $base = preg_replace('/\.pdf$/i', '', $fileName);

        // Extract version from [v], [v1], [v2], etc.
        $version = 0;
        if (preg_match('/\[v(\d*)\]$/i', $base, $vm)) {
            $version = isset($vm[1]) && $vm[1] !== '' ? (int) $vm[1] : 1;
            $base = substr($base, 0, -strlen($vm[0]));
        }

        // Remove " Application" and " Application - Copy" suffixes
        $base = preg_replace('/\s+Application(\s*-\s*Copy)?$/i', '', $base);

        // Remove trailing _1, _2, _3 for ethical approvals
        if ($type === 'ethical' && preg_match('/_\d+$/', $base)) {
            $base = preg_replace('/_\d+$/', '', $base);
        }

        $oldId = trim($base);
        if (empty($oldId)) {
            return null;
        }

        return ['old_project_id' => $oldId, 'version' => $version];
    }

    /**
     * Normalize old_project_id for DB lookup.
     */
    private function normalizeOldId(string $id): string
    {
        return strtolower(trim(str_replace(['\\', '/'], '-', $id)));
    }

    /**
     * Create a compact form of old_project_id: lowercase alphanumeric only.
     * Used for fuzzy matching when separators are missing or different.
     */
    private function compactId(string $id): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $id));
    }

    /**
     * Recursively find all PDF files in a directory.
     */
    private function getAllPdfFiles(string $dir): array
    {
        $files = [];
        $items = File::allFiles($dir);
        foreach ($items as $item) {
            if (strtolower($item->getExtension()) === 'pdf') {
                $files[] = $item->getPathname();
            }
        }
        return $files;
    }

    /**
     * Insert a project_submissions record for the imported file.
     */
    private function upsertSubmission($project, string $type, int $version, string $storedFilename, string $filePath): void
    {
        $existing = DB::table('project_submissions')
            ->where('project_id', $project->id)
            ->where('type', $type)
            ->where('version', $version > 0 ? $version : 1)
            ->first();

        if ($existing) {
            return;
        }

        DB::table('project_submissions')->insert([
            'project_id'        => $project->id,
            'type'              => $type,
            'original_filename' => $storedFilename,
            'stored_filename'   => $storedFilename,
            'file_path'         => $filePath,
            'version'           => $version > 0 ? $version : 1,
            'submitted'         => 1,
            'submitted_at'      => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
