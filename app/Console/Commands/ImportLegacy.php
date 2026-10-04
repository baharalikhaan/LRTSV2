<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportLegacy extends Command
{
    protected $signature = 'import:legacy
        {--dry-run : Preview changes without writing to database}
        {--clean : Truncate all target tables before importing}
        {--skip= : Comma-separated table names to skip}';

    protected $description = 'Import data from legacy lagacy_data.sql into the new database schema';

    private array $parsed = [];
    private bool $dryRun = false;
    private array $skip = [];
    private array $log = [];

    // ID mapping tables: old_id => new_id
    private array $userMap = [];
    private array $nationalityMap = [];
    private array $grantMap = [];
    private array $cycleConfigMap = [];
    private array $programMap = [];
    private array $projectMap = [];
    private array $pillarMap = [];
    private array $outcomeMap = [];
    private array $collegeMap = [];  // tag_code => college_id
    private array $tagIdToCollegeMap = [];  // legacy tag_id => college_id

    // Grant code extraction patterns
    private static array $grantPatterns = [
        'QUIHS'   => ['QUIHS'],
        'QUHI'    => ['QUHI'],
        'QUST'    => ['QUST', 'STD', 'STUDENT'],
        'QUCG'    => ['QUCG'],
        'QUCP'    => ['QUCP'],
        'CDIRCC'  => ['CDIRCC'],
        'IRCCSQU' => ['IRCCSQU'],
        'NCBP'    => ['NCBP'],
        'HIG'     => ['HIG'],
        'IRCC'    => ['IRCC'],
    ];

    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');
        $this->skip = array_filter(array_map('trim', explode(',', $this->option('skip') ?? '')));

        $sqlPath = base_path('lagacy_data.sql');
        if (!File::exists($sqlPath)) {
            $this->error("File not found: {$sqlPath}");
            return 1;
        }

        $this->info('Parsing legacy SQL file...');
        $this->parsed = $this->parseSqlFile($sqlPath);
        $totalRows = array_sum(array_map('count', $this->parsed));
        $this->info("Parsed {$totalRows} rows across " . count($this->parsed) . " tables.");

        // Show parsed table summary
        foreach ($this->parsed as $tname => $tdata) {
            $count = count($tdata['rows']);
            $cols = count($tdata['columns']);
            $this->line("  <comment>{$tname}</comment>: {$count} rows ({$cols} columns)");
        }

        if ($this->dryRun) {
            $this->warn('=== DRY RUN MODE — no changes will be written ===');
        }

        if ($this->option('clean') && !$this->dryRun) {
            $this->cleanDatabase();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Pre-load existing records into ID maps for re-run support
        $this->preloadExistingMaps();

        try {
            $this->importNationalities();
            $this->importUsers();
            $this->importGrants();
            $this->importColleges();
            $this->importCycleConfigs();
            $this->importPrograms();
            $this->importPillars();
            $this->importProjects();
            $this->importStatusHistories();
            $this->importProjectSubmissions();
            $this->importCommitments();
            $this->importProjectsReviewers();
            $this->importProjectContributions();
            $this->importProjectOutcomes();
            $this->importProjectPublications();
            $this->importProjectPillar();
            $this->importProjectStudents();
            $this->importStudentGrantStudents();
            $this->importFinalReportGrading();
            $this->importProgressReportGrading();
            $this->fixOrphanGradings();
            $this->importReviewerGrading();
            $this->importTeam();
            $this->importEmailTemplates();
            $this->importEmailSendLog();
            $this->importRatings();
            $this->importGaugeSettings();
            $this->importUserColleges();
            $this->importUserPillars();
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->newLine();
        $this->info('=== Import Summary ===');
        foreach ($this->log as $line) {
            $this->line($line);
        }

        $this->info('Import completed successfully.');
        return 0;
    }

    // ─────────────────────────────────────────────────────────
    // PRE-LOAD EXISTING RECORDS (for re-run support)
    // ─────────────────────────────────────────────────────────

    private function preloadExistingMaps(): void
    {
        $this->info('Pre-loading existing records...');

        // Load nationalities
        foreach (DB::table('nationalities')->get() as $n) {
            $this->nationalityMap[$n->name] = $n->id;
        }

        // Load users by email → map old user IDs from legacy SQL
        $legacyUsers = $this->tableData('users');
        foreach ($legacyUsers as $row) {
            $existing = DB::table('users')->where('email', $row['email'])->first();
            if ($existing) {
                $this->userMap[$row['id']] = $existing->id;
            }
        }

        // Load grants
        foreach (DB::table('grants')->get() as $g) {
            $this->grantMap[$g->grant_code] = $g->id;
        }

        // Load colleges
        foreach (DB::table('colleges')->get() as $c) {
            $this->collegeMap[$c->code] = $c->id;
        }

        // Build tag_id → college_id mapping from legacy tags data
        foreach ($this->tableData('tags') as $tag) {
            $code = trim($tag['tag']);
            if (isset($this->collegeMap[$code])) {
                $this->tagIdToCollegeMap[$tag['id']] = $this->collegeMap[$code];
            }
        }

        // Load cycle configs by year
        foreach (DB::table('cycle_configs')->get() as $cc) {
            $this->cycleConfigMap[$cc->year] = $cc->id;
        }

        // Load programs by title (GRANT_CODE - Cycle YEAR format)
        $legacyCycles = $this->tableData('cycle');
        foreach ($legacyCycles as $cycle) {
            $grantCode = $this->extractGrantCode($cycle['cycle_title'], $cycle['grant_type'] ?? null);
            $cycleNumber = $this->extractCycleNumber($cycle['cycle_title']);
            $year = $this->extractYearFromTitle($cycle['cycle_title'], $cycle['created_at'] ?? null);
            $cycleYear = $cycleNumber ? (2017 + $cycleNumber) : $year;
            $title = $grantCode ? ($grantCode . ' - Cycle ' . $cycleYear) : $cycle['cycle_title'];
            $existing = DB::table('programs')->where('program_title', $title)->first();
            if ($existing) {
                $this->programMap[$cycle['id']] = $existing->id;
            }
        }

        // Load pillars by name
        $legacyPillars = $this->tableData('pillars');
        $grouped = [];
        foreach ($legacyPillars as $row) {
            $name = trim($row['pillar']);
            if (!isset($grouped[$name])) {
                $grouped[$name] = $name;
            }
        }
        foreach ($grouped as $name) {
            $existing = DB::table('pillars')->where('pillar', $name)->first();
            if ($existing) {
                foreach ($legacyPillars as $row) {
                    if (trim($row['pillar']) === $name) {
                        $this->pillarMap[$row['id']] = $existing->id;
                    }
                }
            }
        }

        // Load projects by old_project_id
        $legacyProjects = $this->tableData('projects');
        foreach ($legacyProjects as $row) {
            if (!empty($row['old_project_id'])) {
                $existing = DB::table('projects')->where('old_project_id', $row['old_project_id'])->first();
                if ($existing) {
                    $this->projectMap[$row['id']] = $existing->id;
                }
            }
        }

        // Load outcomes by mapping old outcome IDs
        $legacyOutcomes = $this->tableData('outcomes');
        foreach ($legacyOutcomes as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            if ($newProjectId) {
                $existing = DB::table('project_outcomes')
                    ->where('project_id', $newProjectId)
                    ->where('type', $row['type'])
                    ->where('identifier', $row['identifier'])
                    ->first();
                if ($existing) {
                    $this->outcomeMap[$row['id']] = $existing->id;
                }
            }
        }

        $this->line("  <comment>nationalityMap</comment>: " . count($this->nationalityMap));
        $this->line("  <comment>userMap</comment>: " . count($this->userMap));
        $this->line("  <comment>grantMap</comment>: " . count($this->grantMap));
        $this->line("  <comment>cycleConfigMap</comment>: " . count($this->cycleConfigMap));
        $this->line("  <comment>programMap</comment>: " . count($this->programMap));
        $this->line("  <comment>pillarMap</comment>: " . count($this->pillarMap));
        $this->line("  <comment>projectMap</comment>: " . count($this->projectMap));
        $this->line("  <comment>outcomeMap</comment>: " . count($this->outcomeMap));
    }

    // ─────────────────────────────────────────────────────────
    // CLEAN DATABASE
    // ─────────────────────────────────────────────────────────

    private function cleanDatabase(): void
    {
        $this->warn('Cleaning database (truncating target tables)...');

        $tables = [
            // Child tables first (reverse FK order)
            'project_students_details',
            'report_reminders_sent',
            'reviewer_ratings',
            'email_send_log',
            'progress_report_grading',
            'final_report_grading',
            'status_histories',
            'project_submissions',
            'project_students',
            'project_researchers',
            'project_publications',
            'project_pillar',
            'project_outcomes',
            'project_contributions',
            'project_college',
            'projects_reviewers',
            'commitments',
            'user_pillars',
            'user_colleges',
            // Core tables
            'projects',
            'programs',
            // Reference tables
            'pillars',
            'cycle_configs',
            'grants',
            'team',
            'email_templates',
            'gauge_settings',
            'colleges',
            'users',
            'nationalities',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $truncated = 0;
        foreach ($tables as $table) {
            $exists = DB::select("SHOW TABLES LIKE '{$table}'");
            if (!empty($exists)) {
                DB::statement("TRUNCATE TABLE `{$table}`");
                $this->line("  Truncated: <comment>{$table}</comment>");
                $truncated++;
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Clear ID maps
        $this->userMap = [];
        $this->nationalityMap = [];
        $this->grantMap = [];
        $this->cycleConfigMap = [];
        $this->programMap = [];
        $this->projectMap = [];
        $this->pillarMap = [];
        $this->outcomeMap = [];
        $this->collegeMap = [];
        $this->tagIdToCollegeMap = [];

        $this->info("Cleaned {$truncated} tables.");
    }

    // ─────────────────────────────────────────────────────────
    // SQL PARSER
    // ─────────────────────────────────────────────────────────

    private function parseSqlFile(string $path): array
    {
        $tables = [];
        $currentTable = null;
        $columns = [];
        $buffer = '';
        $inInsert = false;
        $insertCount = 0;

        $handle = fopen($path, 'r');
        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            $trimmed = trim($line);

            // Skip comments, empty lines, SET, transactions
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')
                || str_starts_with($trimmed, 'SET ') || str_starts_with($trimmed, 'START TRANSACTION')
                || str_starts_with($trimmed, 'COMMIT') || str_starts_with($trimmed, '/*!')
                || str_starts_with($trimmed, 'CREATE TABLE') || str_starts_with($trimmed, 'ALTER TABLE')
                || str_starts_with($trimmed, 'ADD CONSTRAINT') || str_starts_with($trimmed, 'KEY ')
                || str_starts_with($trimmed, 'PRIMARY') || str_starts_with($trimmed, 'UNIQUE')
                || str_starts_with($trimmed, 'CONSTRAINT') || str_starts_with($trimmed, 'ENGINE=')
                || str_starts_with($trimmed, ') ENGINE')) {
                continue;
            }

            // Detect INSERT INTO — may have VALUES on same line or next
            if (!$inInsert && preg_match('/^INSERT\s+INTO\s+`(\w+)`\s*\(([^)]+)\)\s*VALUES\s*(.*)$/i', $line, $m)) {
                $currentTable = $m[1];
                $columns = array_map(fn($c) => trim($c, ' `'), explode(',', $m[2]));
                $rest = trim($m[3]);
                $insertCount++;

                if ($rest !== '') {
                    $buffer = $rest;
                    if (str_ends_with($rest, ';')) {
                        $this->parseInsertValues($tables, $currentTable, $columns, rtrim($rest, ';'));
                        $inInsert = false;
                        $buffer = '';
                        $currentTable = null;
                        $columns = [];
                    } else {
                        $inInsert = true;
                    }
                } else {
                    $inInsert = true;
                    $buffer = '';
                }
                continue;
            }

            if ($inInsert) {
                $buffer .= ' ' . $line;
                // Scan ENTIRE buffer to find the FIRST ; that is outside a string context
                $inString = false;
                $esc = false;
                $semicolonPos = -1;
                for ($bi = 0; $bi < strlen($buffer); $bi++) {
                    $ch = $buffer[$bi];
                    if ($esc) { $esc = false; continue; }
                    if ($ch === '\\') { $esc = true; continue; }
                    if ($ch === "'" && !$inString) { $inString = true; continue; }
                    if ($ch === "'" && $inString) { $inString = false; continue; }
                    if (!$inString && $ch === ';') { $semicolonPos = $bi; break; }
                }
                if ($semicolonPos >= 0) {
                    $valuesStr = substr($buffer, 0, $semicolonPos);
                    $this->parseInsertValues($tables, $currentTable, $columns, $valuesStr);
                    $inInsert = false;
                    $buffer = '';
                    $currentTable = null;
                    $columns = [];
                }
            }
        }
        fclose($handle);

        $this->line("  Detected <comment>{$insertCount}</comment> INSERT statements total");

        return $tables;
    }

    private function parseInsertValues(array &$tables, string $table, array $columns, string $valuesStr): void
    {
        if (!isset($tables[$table])) {
            $tables[$table] = ['columns' => $columns, 'rows' => []];
        }

        $colCount = count($columns);
        $tuples = $this->extractValueTuples($valuesStr);
        $matched = 0;
        $mismatched = 0;

        foreach ($tuples as $tuple) {
            $values = $this->parseTupleValues($tuple);
            if (count($values) === $colCount) {
                $tables[$table]['rows'][] = array_combine($columns, $values);
                $matched++;
            } else {
                $mismatched++;
                if ($mismatched <= 3) {
                    $this->line("  <error>COL MISMATCH</error> {$table}: expected {$colCount} cols, got " . count($values) . " values");
                    $this->line("    First 5 values: " . json_encode(array_slice($values, 0, 5)));
                }
            }
        }

        if ($mismatched > 0) {
            $this->line("  <comment>{$table}</comment> batch: {$matched} matched, {$mismatched} mismatched out of " . count($tuples) . " tuples");
        }
    }

    private function extractValueTuples(string $str): array
    {
        $tuples = [];
        $depth = 0;
        $current = '';
        $inString = false;
        $escape = false;
        $len = strlen($str);
        $debugTupleCount = 0;

        for ($i = 0; $i < $len; $i++) {
            $c = $str[$i];

            if ($escape) {
                $current .= $c;
                $escape = false;
                continue;
            }

            if ($c === '\\') {
                $escape = true;
                $current .= $c;
                continue;
            }

            if ($c === "'" && !$inString) {
                $inString = true;
                $current .= $c;
                continue;
            }

            if ($c === "'" && $inString) {
                $inString = false;
                $current .= $c;
                continue;
            }

            if (!$inString) {
                if ($c === '(') {
                    if ($depth === 0) {
                        $current = '';
                        $depth++;
                        continue;
                    }
                    $depth++;
                } elseif ($c === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $tuples[] = $current;
                        $debugTupleCount++;
                        continue;
                    }
                }
            }

            $current .= $c;
        }

        if ($debugTupleCount === 0 && $len > 10) {
            $this->line("  <error>WARN</error> extractValueTuples: 0 tuples from {$len} char string");
            $this->line("    First 200 chars: " . substr($str, 0, 200));
        }

        return $tuples;
    }

    private function parseTupleValues(string $tuple): array
    {
        // Pre-process: convert bit values b'0'/b'1' to numeric before parsing
        $tuple = preg_replace("/b'([01])'/", '$1', $tuple);

        $values = [];
        $current = '';
        $inString = false;
        $escape = false;
        $len = strlen($tuple);

        for ($i = 0; $i < $len; $i++) {
            $c = $tuple[$i];

            if ($escape) {
                switch ($c) {
                    case 'n': $current .= "\n"; break;
                    case 'r': $current .= "\r"; break;
                    case 't': $current .= "\t"; break;
                    case "'": $current .= "'"; break;
                    case '\\': $current .= '\\'; break;
                    default: $current .= $c; break;
                }
                $escape = false;
                continue;
            }

            if ($c === '\\' && $inString) {
                $escape = true;
                continue;
            }

            if ($c === "'" && !$inString) {
                $inString = true;
                continue;
            }

            if ($c === "'" && $inString) {
                $inString = false;
                continue;
            }

            if (!$inString && $c === ',') {
                $values[] = $this->normalizeValue(trim($current));
                $current = '';
                continue;
            }

            $current .= $c;
        }

        $values[] = $this->normalizeValue(trim($current));

        return $values;
    }

    private function normalizeValue(string $value): mixed
    {
        if ($value === 'NULL' || $value === 'null') {
            return null;
        }
        if ($value === "''" || $value === '') {
            return '';
        }
        // Numeric
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }
        // String (strip quotes) - should not happen after parseTupleValues but safety net
        if (strlen($value) >= 2 && str_starts_with($value, "'") && str_ends_with($value, "'")) {
            return substr($value, 1, -1);
        }
        return $value;
    }

    // ─────────────────────────────────────────────────────────
    // UTILITY METHODS
    // ─────────────────────────────────────────────────────────

    private function tableData(string $table): array
    {
        return $this->parsed[$table]['rows'] ?? [];
    }

    private function shouldSkip(string $table): bool
    {
        return in_array($table, $this->skip);
    }

    private function logSkip(string $table, array $row, string $reason): void
    {
        $id = $row['id'] ?? 'N/A';
        $this->line("  <error>SKIP</error> [{$table}] id={$id}: {$reason}");
    }

    private function log(string $msg): void
    {
        $this->log[] = $msg;
        if ($this->dryRun) {
            $this->line("  [DRY] {$msg}");
        }
    }

    private function write(string $table, array $data): void
    {
        if (!$this->dryRun) {
            DB::table($table)->insert($data);
        }
    }

    private function writeOne(string $table, array $data): int
    {
        if ($this->dryRun) {
            return 0;
        }
        return DB::table($table)->insertGetId($data);
    }

    private function extractGrantCode(string $title, ?string $grantType = null): ?string
    {
        // If grant_type is 'student', it's a student grant (QUST)
        if ($grantType === 'student') {
            return 'QUST';
        }

        $upper = strtoupper(trim($title));

        // Skip test cycles
        if (str_starts_with($upper, 'TEST')) {
            return null;
        }

        // Handle specific IRCC variants first (before general prefix matching)
        if (str_contains($upper, 'IRCC LOCAL') || str_contains($upper, 'CDIRCC')) {
            return 'CDIRCC';
        }
        if (str_contains($upper, 'IRCCSQU') || str_contains($upper, 'IRCC SQU')) {
            return 'IRCCSQU';
        }
        if (str_contains($upper, 'IRCC INTERNATIONAL')) {
            return 'IRCC';
        }

        // Try matching prefix from title
        foreach (self::$grantPatterns as $code => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_starts_with($upper, $pattern)) {
                    return $code;
                }
            }
        }

        // Fallback: try to extract from old_project_id patterns in from_conf_tool
        // by checking the cycle_title keywords
        if (str_contains($upper, 'STD') || str_contains($upper, 'STUDENT')) {
            return 'QUST';
        }

        return null;
    }

    private function extractYearFromTitle(string $title, ?string $createdAt = null): ?int
    {
        // Try explicit 4-digit year first (2020-2030)
        if (preg_match('/\b(20[2-3][0-9])\b/', $title, $m)) {
            return (int) $m[1];
        }

        // Try "Spring 25" or "Fall 2025" pattern
        if (preg_match('/(?:Spring|Fall|Summer|Winter)\s+(\d{2,4})/i', $title, $m)) {
            $y = (int) $m[1];
            if ($y < 100) $y += 2000;
            if ($y >= 2020 && $y <= 2030) return $y;
        }

        // Try "22" style short year at end or after space
        if (preg_match('/\b(\d{2})\b/', $title, $m)) {
            $y = (int) $m[1] + 2000;
            if ($y >= 2022 && $y <= 2030) return $y;
        }

        // Fallback: use created_at year
        if ($createdAt && preg_match('/^(\d{4})/', $createdAt, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function extractCycleNumber(string $title): ?int
    {
        // Extract cycle number from titles like "CDIRCC Local 8", "IRCC SQU 7", "QUHI 7"
        // Must be a single digit (1-9), NOT part of a 2-digit year like "22"
        if (preg_match('/\b([1-9])\b/', $title, $m)) {
            // Make sure it's not part of a larger number
            $pos = strpos($title, $m[1]);
            $before = $pos > 0 ? $title[$pos - 1] : ' ';
            $after = ($pos + 1 < strlen($title)) ? $title[$pos + 1] : ' ';
            if (!is_numeric($before) && !is_numeric($after)) {
                return (int) $m[1];
            }
        }
        return null;
    }

    private function mapUserType(string $legacyType): string
    {
        return match ($legacyType) {
            'LPI' => 'Student',
            'Reviewer' => 'Reviewer',
            'LPI+Reviewer' => 'Reviewer',
            'Admin' => 'Admin',
            default => 'Student',
        };
    }

    private function mapProjectStatus(string $status): string
    {
        return match ($status) {
            'Pending' => 'submitted',
            'Accepted' => 'approved',
            'Rejected' => 'rejected',
            'Completed' => 'completed',
            default => 'submitted',
        };
    }

    private function mapStudentType(string $level): string
    {
        $lower = strtolower($level);
        if (str_contains($lower, 'undergraduate') || str_contains($lower, 'bachelor')) {
            return 'UG';
        }
        if (str_contains($lower, 'master')) {
            return 'masters';
        }
        if (str_contains($lower, 'doctor') || str_contains($lower, 'phd')) {
            return 'PhD';
        }
        return 'UG';
    }

    private function cleanDate($value): ?string
    {
        if ($value === null || $value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }
        return $value;
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: NATIONALITIES
    // ─────────────────────────────────────────────────────────

    private function importNationalities(): void
    {
        if ($this->shouldSkip('nationalities')) return;

        $this->info('Importing nationalities...');

        $nationalities = ['Qatari', 'Non-Qatari'];
        foreach ($nationalities as $name) {
            $existing = DB::table('nationalities')->where('name', $name)->first();
            if ($existing) {
                $this->nationalityMap[$name] = $existing->id;
            } else {
                $id = $this->writeOne('nationalities', [
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->nationalityMap[$name] = $id;
            }
        }
        $this->log('Nationalities: ' . count($nationalities) . ' records');
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: USERS
    // ─────────────────────────────────────────────────────────

    private function importUsers(): void
    {
        if ($this->shouldSkip('users')) return;

        $this->info('Importing users...');
        $rows = $this->tableData('users');

        $testIds = [33, 34, 35, 36];
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (in_array($row['id'], $testIds)) {
                $skipped++;
                continue;
            }

            // Map nationality
            $nationalityId = null;
            $nat = trim($row['nationality'] ?? '');
            if ($nat === 'Qatri') {
                $nationalityId = $this->nationalityMap['Qatari'] ?? null;
            } elseif ($nat === 'Non-Qatri') {
                $nationalityId = $this->nationalityMap['Non-Qatari'] ?? null;
            }

            // Check if user already exists by email
            $existing = DB::table('users')->where('email', $row['email'])->first();
            if ($existing) {
                $this->userMap[$row['id']] = $existing->id;
                $skipped++;
                continue;
            }

            $data = [
                'name' => $row['name'],
                'email' => $row['email'],
                'email_verified_at' => $this->cleanDate($row['email_verified_at']),
                'password' => $row['password'] ?? '',
                'type' => $this->mapUserType($row['type']),
                'qu_id' => $row['userid'] ?? null,
                'nationality' => $nat ?: null,
                'is_active' => true,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $newId = $this->writeOne('users', $data);
            $this->userMap[$row['id']] = $newId;
            $imported++;
        }

        $this->log("Users: {$imported} imported, {$skipped} skipped (test/duplicate)");
        $this->line("  userMap has <comment>" . count($this->userMap) . "</comment> entries");

        // ── Create test users for testing ──────────────────────────────
        $testPassword = bcrypt('password');
        $testUsers = [
            ['name' => 'Test Admin',   'email' => 'admin@qu.edu.qa',   'type' => 'Admin'],
            ['name' => 'Test LPI',     'email' => 'lpi@qu.edu.qa',     'type' => 'LPI'],
            ['name' => 'Test Reviewer','email' => 'reviewer@qu.edu.qa','type' => 'Reviewer'],
        ];

        foreach ($testUsers as $tu) {
            $exists = DB::table('users')->where('email', $tu['email'])->first();
            if (!$exists) {
                $newId = $this->writeOne('users', [
                    'name'              => $tu['name'],
                    'email'             => $tu['email'],
                    'email_verified_at' => now(),
                    'password'          => $testPassword,
                    'type'              => $tu['type'],
                    'is_active'         => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
                $this->log("Created test user: {$tu['name']} ({$tu['email']}) — id {$newId}");
            } else {
                $this->log("Test user already exists: {$tu['email']}");
            }
        }
        // Show a few mapped IDs for verification
        $sample = array_slice($this->userMap, 0, 5, true);
        foreach ($sample as $oldId => $newId) {
            $this->line("    old[{$oldId}] => new[{$newId}]");
        }
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: GRANTS
    // ─────────────────────────────────────────────────────────

    private function importGrants(): void
    {
        if ($this->shouldSkip('grants')) return;

        $this->info('Importing grants (from grant_title)...');
        $rows = $this->tableData('grant_title');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $code = trim($row['code']);

            // Check if already exists
            if (isset($this->grantMap[$code])) {
                $skipped++;
                continue;
            }

            $existing = DB::table('grants')->where('grant_code', $code)->first();
            if ($existing) {
                $this->grantMap[$code] = $existing->id;
                $skipped++;
            } else {
                $isStudent = ($code === 'QUST');
                $id = $this->writeOne('grants', [
                    'grant_code' => $code,
                    'grant_name' => trim($row['title']),
                    'category' => $isStudent ? 'student' : 'regular',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->grantMap[$code] = $id;
                $this->line("  <info>{$code}</info>: {$row['title']}");
                $imported++;
            }
        }

        // Add grants that exist in cycle data but not in grant_title
        $extraGrants = [
            'IRCCSQU' => ['name' => 'IRCC SQU Grant', 'category' => 'regular'],
            'NCBP'    => ['name' => 'National Capacity Building Program', 'category' => 'regular'],
            'HIG'     => ['name' => 'High Impact Grant', 'category' => 'regular'],
        ];
        foreach ($extraGrants as $code => $info) {
            if (!isset($this->grantMap[$code])) {
                $existing = DB::table('grants')->where('grant_code', $code)->first();
                if ($existing) {
                    $this->grantMap[$code] = $existing->id;
                } else {
                    $id = $this->writeOne('grants', [
                        'grant_code' => $code,
                        'grant_name' => $info['name'],
                        'category' => $info['category'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->grantMap[$code] = $id;
                    $this->line("  <info>{$code}</info>: {$info['name']}");
                    $imported++;
                }
            }
        }

        $this->log("Grants: {$imported} created, {$skipped} skipped (" . implode(', ', array_keys($this->grantMap)) . ")");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: COLLEGES (from legacy tags table)
    // ─────────────────────────────────────────────────────────

    private function importColleges(): void
    {
        if ($this->shouldSkip('colleges')) return;

        $this->info('Importing colleges (from tags)...');
        $rows = $this->tableData('tags');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $code = trim($row['tag']);
            $name = trim($row['tagtitle'] ?? $code);

            // Check if already exists
            if (isset($this->collegeMap[$code])) {
                $skipped++;
                continue;
            }

            $existing = DB::table('colleges')->where('name', $name)->first();
            if ($existing) {
                $this->collegeMap[$code] = $existing->id;
                $this->tagIdToCollegeMap[$row['id']] = $existing->id;
                $skipped++;
            } else {
                $id = $this->writeOne('colleges', [
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->collegeMap[$code] = $id;
                $this->tagIdToCollegeMap[$row['id']] = $id;
                $this->line("  <info>{$code}</info>: {$name}");
                $imported++;
            }
        }

        $this->log("Colleges: {$imported} created, {$skipped} skipped (" . count($this->collegeMap) . " total)");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: CYCLE CONFIGS
    // ─────────────────────────────────────────────────────────

    private function importCycleConfigs(): void
    {
        if ($this->shouldSkip('cycle_configs')) return;

        $this->info('Importing cycle configs...');
        $cycles = $this->tableData('cycle');

        $years = [];
        foreach ($cycles as $cycle) {
            $year = $this->extractYearFromTitle($cycle['cycle_title'], $cycle['created_at'] ?? null);
            if ($year && !in_array($year, $years)) {
                $years[] = $year;
            }
        }

        $imported = 0;
        foreach ($years as $year) {
            $existing = DB::table('cycle_configs')->where('year', $year)->first();
            if ($existing) {
                $this->cycleConfigMap[$year] = $existing->id;
            } else {
                $id = $this->writeOne('cycle_configs', [
                    'year' => $year,
                    'title' => "Cycle {$year}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->cycleConfigMap[$year] = $id;
                $imported++;
            }
        }

        $this->log("Cycle Configs: {$imported} created for years: " . implode(', ', $years));
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROGRAMS (from legacy cycle table)
    // ─────────────────────────────────────────────────────────

    private function importPrograms(): void
    {
        if ($this->shouldSkip('programs')) return;

        $this->info('Importing programs (from legacy cycles)...');
        $cycles = $this->tableData('cycle');
        $imported = 0;

        foreach ($cycles as $cycle) {
            $grantCode = $this->extractGrantCode($cycle['cycle_title'], $cycle['grant_type'] ?? null);
            $year = $this->extractYearFromTitle($cycle['cycle_title'], $cycle['created_at'] ?? null);

            $grantId = $grantCode ? ($this->grantMap[$grantCode] ?? null) : null;
            $cycleConfigId = $year ? ($this->cycleConfigMap[$year] ?? null) : null;

            $isStudent = ($cycle['grant_type'] ?? '') === 'student';

            // Extract cycle number and map to year (7→2024, 8→2025)
            $cycleNumber = $this->extractCycleNumber($cycle['cycle_title']);
            $cycleYear = $cycleNumber ? (2017 + $cycleNumber) : $year;

            // Generate title as "GRANT_CODE - Cycle YEAR"
            $title = $grantCode ? ($grantCode . ' - Cycle ' . $cycleYear) : $cycle['cycle_title'];

            $data = [
                'program_title' => $title,
                'prog_rpt_deadline' => $this->cleanDate($cycle['prog_rpt_deadline']),
                'final_rpt_deadline' => $this->cleanDate($cycle['final_rpt_deadline']),
                'grant_id' => $grantId,
                'cycle_id' => $cycleConfigId,
                'created_at' => $this->cleanDate($cycle['created_at']),
                'updated_at' => $this->cleanDate($cycle['updated_at']),
            ];

            $newId = $this->writeOne('programs', $data);
            $this->programMap[$cycle['id']] = $newId;
            $imported++;
        }

        $this->log("Programs: {$imported} imported from legacy cycles");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PILLARS
    // ─────────────────────────────────────────────────────────

    private function importPillars(): void
    {
        if ($this->shouldSkip('pillars')) return;

        $this->info('Importing pillars...');
        $rows = $this->tableData('pillars');

        // Group by pillar name, collect all subpillars
        $grouped = [];
        foreach ($rows as $row) {
            $name = trim($row['pillar']);
            if (!isset($grouped[$name])) {
                $grouped[$name] = [];
            }
            $sub = trim($row['subpillar'] ?? '');
            if ($sub !== '' && $sub !== $name && !in_array($sub, $grouped[$name])) {
                $grouped[$name][] = $sub;
            }
        }

        $imported = 0;
        foreach ($grouped as $pillarName => $subpillars) {
            $existing = DB::table('pillars')->where('pillar', $pillarName)->first();
            if ($existing) {
                // Map all legacy IDs that belong to this pillar
                foreach ($rows as $row) {
                    if (trim($row['pillar']) === $pillarName) {
                        $this->pillarMap[$row['id']] = $existing->id;
                    }
                }
            } else {
                $subpillarText = implode(", ", $subpillars);
                $data = [
                    'pillar' => $pillarName,
                    'subpillar' => $subpillarText !== '' ? $subpillarText : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $newId = $this->writeOne('pillars', $data);

                // Map all legacy IDs that belong to this pillar
                foreach ($rows as $row) {
                    if (trim($row['pillar']) === $pillarName) {
                        $this->pillarMap[$row['id']] = $newId;
                    }
                }
                $imported++;
                $subCount = count($subpillars);
                $this->line("  <info>{$pillarName}</info>: {$subCount} subpillars");
            }
        }

        $this->log("Pillars: {$imported} created from " . count($grouped) . " unique names (legacy had " . count($rows) . " records)");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROJECTS
    // ─────────────────────────────────────────────────────────

    private function importProjects(): void
    {
        if ($this->shouldSkip('projects')) return;

        $this->info('Importing projects...');
        $rows = $this->tableData('projects');
        $this->line("  Parsed <comment>" . count($rows) . "</comment> project rows from SQL");
        $imported = 0;
        $skipped = 0;
        $skipReasons = ['no_lpi' => 0, 'bad_data' => 0];

        foreach ($rows as $i => $row) {
            // Skip if already imported (pre-loaded from existing records)
            if (isset($this->projectMap[$row['id']])) {
                continue;
            }

            $lpiId = $this->userMap[$row['user_id']] ?? null;
            if (!$lpiId) {
                $skipped++;
                $skipReasons['no_lpi']++;
                if ($skipped <= 5) {
                    $this->line("  <error>SKIP</error> project id={$row['id']} user_id={$row['user_id']} - LPI not found in userMap");
                }
                continue;
            }

            $programId = $this->programMap[$row['cycle']] ?? null;

            $budget = null;
            if (!empty($row['requested_budget_qar'])) {
                $budget = (float) str_replace(',', '', $row['requested_budget_qar']);
            }

            $data = [
                'title' => $row['title'],
                'old_project_id' => $row['old_project_id'] ?? null,
                'lpi_id' => $lpiId,
                'program_id' => $programId,
                'total_score' => $row['total_score'] ?? 0,
                'requested_budget_qar' => $budget,
                'college_decision' => $row['college_decision'] ?? null,
                'rsd_feedback' => $row['rsd_feedback'] ?? null,
                'final_rsd_decision' => $row['final_rsd_decision'] ?? null,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $newId = $this->writeOne('projects', $data);
            $this->projectMap[$row['id']] = $newId;
            $imported++;
        }

        $this->log("Projects: {$imported} imported, {$skipped} skipped");
        if ($skipped > 0) {
            $this->line("  Skip reasons: " . json_encode($skipReasons));
        }
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: STATUS HISTORIES (derived from data relationships)
    // ─────────────────────────────────────────────────────────

    private function importStatusHistories(): void
    {
        if ($this->shouldSkip('status_histories')) return;

        $this->info('Importing status histories (derived from submissions & grading)...');

        // Pre-index legacy data by project_id
        $submissionsByProject = [];
        foreach ($this->tableData('submissions') as $s) {
            $pid = $s['project_id'];
            $type = strtolower($s['type'] ?? '');
            $submissionsByProject[$pid][$type][] = $s;
        }

        $progressGradingByProject = [];
        foreach ($this->tableData('progress_report_grading') as $g) {
            $progressGradingByProject[$g['project_id']][] = $g;
        }

        $finalGradingByProject = [];
        foreach ($this->tableData('final_report_grading') as $g) {
            $finalGradingByProject[$g['project_id']][] = $g;
        }

        $reviewersByProject = [];
        foreach ($this->tableData('projects_reviewers') as $r) {
            $reviewersByProject[$r['project_id']][] = $r;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($this->tableData('projects') as $row) {
            $newProjectId = $this->projectMap[$row['id']] ?? null;
            if (!$newProjectId) {
                $skipped++;
                continue;
            }

            $legacyPid = $row['id'];
            $createdAt = $this->cleanDate($row['created_at']);

            // Extra project metadata
            $projectMeta = [];
            foreach (['spending', 'spending_detail', 'publications', 'student_engagement', 'student_project_draft', 'IsAdmin', 'conf_tool_id'] as $field) {
                if (!empty($row[$field])) {
                    $projectMeta[$field] = $row[$field];
                }
            }

            // 1. Always: project exists → registered
            $this->write('status_histories', [
                'project_id' => $newProjectId,
                'status' => 'registered',
                'metadata' => !empty($projectMeta) ? json_encode($projectMeta) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $imported++;

            // 2. Reviewer assigned → Assigned (using reviewer created_at)
            //    Reviewer accepted → Claimed (using statusdate)
            //    Reviewer rejected → proposal_rejected (using statusdate)
            if (isset($reviewersByProject[$legacyPid])) {
                $assignedDone = false;
                foreach ($reviewersByProject[$legacyPid] as $rv) {
                    // Assigned status (from reviewer created_at)
                    if (!$assignedDone && !empty($rv['created_at'])) {
                        $this->write('status_histories', [
                            'project_id' => $newProjectId,
                            'status' => 'Assigned',
                            'metadata' => null,
                            'created_at' => $this->cleanDate($rv['created_at']),
                            'updated_at' => $this->cleanDate($rv['created_at']),
                        ]);
                        $imported++;
                        $assignedDone = true;
                    }

                    // Claimed or proposal_rejected (from proposalstatus + statusdate)
                    $proposalStatus = strtolower(trim($rv['proposalstatus'] ?? ''));
                    $statusDate = $this->cleanDate($rv['statusdate']);
                    if ($statusDate) {
                        if ($proposalStatus === 'accepted') {
                            $this->write('status_histories', [
                                'project_id' => $newProjectId,
                                'status' => 'Claimed',
                                'metadata' => null,
                                'created_at' => $statusDate,
                                'updated_at' => $statusDate,
                            ]);
                            $imported++;
                        } elseif ($proposalStatus === 'rejected') {
                            $this->write('status_histories', [
                                'project_id' => $newProjectId,
                                'status' => 'proposal_rejected',
                                'metadata' => null,
                                'created_at' => $statusDate,
                                'updated_at' => $statusDate,
                            ]);
                            $imported++;
                        }
                    }
                }
            }

            // 3. Has progress submission → progress_added
            $hasProgress = isset($submissionsByProject[$legacyPid]['progress']);
            if ($hasProgress) {
                $firstProgress = $submissionsByProject[$legacyPid]['progress'][0];
                $this->write('status_histories', [
                    'project_id' => $newProjectId,
                    'status' => 'progress_added',
                    'metadata' => null,
                    'created_at' => $this->cleanDate($firstProgress['created_at']),
                    'updated_at' => $this->cleanDate($firstProgress['updated_at']),
                ]);
                $imported++;
            }

            // 4. Has final submission → final_added
            $hasFinal = isset($submissionsByProject[$legacyPid]['final']);
            if ($hasFinal) {
                $firstFinal = $submissionsByProject[$legacyPid]['final'][0];
                $this->write('status_histories', [
                    'project_id' => $newProjectId,
                    'status' => 'final_added',
                    'metadata' => null,
                    'created_at' => $this->cleanDate($firstFinal['created_at']),
                    'updated_at' => $this->cleanDate($firstFinal['updated_at']),
                ]);
                $imported++;
            }

            // 5. Has progress grading → progress_reviewed / progress_rejected
            if (isset($progressGradingByProject[$legacyPid])) {
                foreach ($progressGradingByProject[$legacyPid] as $g) {
                    $accepted = ($g['isAccepted'] ?? 0) == 1;
                    $status = $accepted ? 'progress_reviewed' : 'progress_rejected';
                    $this->write('status_histories', [
                        'project_id' => $newProjectId,
                        'status' => $status,
                        'metadata' => null,
                        'created_at' => $this->cleanDate($g['created_at']),
                        'updated_at' => $this->cleanDate($g['updated_at']),
                    ]);
                    $imported++;
                }
            }

            // 6. Has final grading → Graded / final_rejected
            if (isset($finalGradingByProject[$legacyPid])) {
                foreach ($finalGradingByProject[$legacyPid] as $g) {
                    $publish = $g['publish'] ?? 'pending';
                    if ($publish === 'accepted') {
                        $status = 'Graded';
                    } elseif ($publish === 'rejected') {
                        $status = 'final_rejected';
                    } else {
                        $status = 'Graded'; // default for pending/reserved
                    }
                    $this->write('status_histories', [
                        'project_id' => $newProjectId,
                        'status' => $status,
                        'metadata' => null,
                        'created_at' => $this->cleanDate($g['created_at']),
                        'updated_at' => $this->cleanDate($g['updated_at']),
                    ]);
                    $imported++;
                }
            }
        }

        $this->log("Status Histories: {$imported} created, {$skipped} projects skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROJECT SUBMISSIONS (from legacy submissions)
    // ─────────────────────────────────────────────────────────

    private function importProjectSubmissions(): void
    {
        if ($this->shouldSkip('project_submissions')) return;

        $this->info('Importing project submissions...');
        $rows = $this->tableData('submissions');
        $imported = 0;
        $skipped = 0;

        // Map legacy types to new types
        $typeMap = [
            'progress'  => 'progress',
            'final'     => 'final',
            'readiness' => 'readiness',
            'proposal'  => null, // skip
        ];

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            if (!$newProjectId) {
                $skipped++;
                continue;
            }

            $legacyType = strtolower(trim($row['type'] ?? ''));
            $newType = $typeMap[$legacyType] ?? null;
            if ($newType === null) {
                $skipped++;
                continue;
            }

            // Skip if already exists (re-run safety)
            if (DB::table('project_submissions')
                ->where('project_id', $newProjectId)
                ->where('type', $newType)
                ->where('created_at', $this->cleanDate($row['created_at']))
                ->exists()) {
                continue;
            }

            $filename = $row['title'] ?? 'submission';

            $data = [
                'project_id'        => $newProjectId,
                'user_id'           => $newUserId,
                'type'              => $newType,
                'original_filename' => $filename,
                'stored_filename'   => $filename,
                'version'           => 1,
                'file_path'         => 'legacy/' . $newProjectId . '/' . $newType . '/' . $row['id'],
                'notes'             => null,
                'created_at'        => $this->cleanDate($row['created_at']),
                'updated_at'        => $this->cleanDate($row['updated_at']),
            ];

            $this->write('project_submissions', $data);
            $imported++;
        }

        $this->log("Project Submissions: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: COMMITMENTS
    // ─────────────────────────────────────────────────────────

    private function importCommitments(): void
    {
        if ($this->shouldSkip('commitments')) return;

        $this->info('Importing commitments...');
        $rows = $this->tableData('commitments');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            if (!$newProjectId) {
                $skipped++;
                continue;
            }

            $data = [
                'project_id' => $newProjectId,
                'q1article' => $row['q1article'] ?? 0,
                'q2article' => $row['q2article'] ?? 0,
                'q3article' => $row['q3article'] ?? 0,
                'q4article' => $row['q4article'] ?? 0,
                'confArticle' => $row['confArticle'] ?? 0,
                'books' => $row['books'] ?? 0,
                'editBooks' => $row['editBooks'] ?? 0,
                'chapters' => $row['chapters'] ?? 0,
                'ip' => $row['ip'] ?? 0,
                'filedPatent' => $row['filedPatent'] ?? 0,
                'openSourceSW' => $row['openSourceSW'] ?? 0,
                'startUp' => $row['startUp'] ?? false,
                'ethical' => $row['ethical'] ?? false,
                'master' => $row['master'] ?? 0,
                'UG' => $row['UG'] ?? 0,
                'Phd' => $row['Phd'] ?? 0,
                'crossCollege' => $row['crossCollege'] ?? false,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('commitments', $data);
            $imported++;
        }

        $this->log("Commitments: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROJECTS REVIEWERS
    // ─────────────────────────────────────────────────────────

    private function importProjectsReviewers(): void
    {
        if ($this->shouldSkip('projects_reviewers')) return;

        $this->info('Importing project reviewers...');
        $rows = $this->tableData('projects_reviewers');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            if (!$newProjectId || !$newUserId) {
                $reason = !$newProjectId ? "project_id {$row['project_id']} not in projectMap" : "user_id {$row['user_id']} not in userMap";
                $this->logSkip('projects_reviewers', $row, $reason);
                $skipped++;
                continue;
            }

            // Skip if already exists (re-run safety)
            if (DB::table('projects_reviewers')->where('project_id', $newProjectId)->where('user_id', $newUserId)->exists()) {
                continue;
            }

            $data = [
                'project_id' => $newProjectId,
                'user_id' => $newUserId,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('projects_reviewers', $data);
            $imported++;
        }

        $this->log("Project Reviewers: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROJECT CONTRIBUTIONS
    // ─────────────────────────────────────────────────────────

    private function importProjectContributions(): void
    {
        if ($this->shouldSkip('project_contributions')) return;

        $this->info('Importing project contributions...');
        $rows = $this->tableData('contribution');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            if (!$newProjectId || !$newUserId) {
                $skipped++;
                continue;
            }

            $data = [
                'project_id' => $newProjectId,
                'user_id' => $newUserId,
                'type' => $row['type'],
                'detail' => $row['detail'] ?? null,
                'score' => $row['score'] ?? 0,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('project_contributions', $data);
            $imported++;
        }

        $this->log("Project Contributions: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROJECT OUTCOMES
    // ─────────────────────────────────────────────────────────

    private function importProjectOutcomes(): void
    {
        if ($this->shouldSkip('project_outcomes')) return;

        $this->info('Importing project outcomes...');
        $rows = $this->tableData('outcomes');
        $imported = 0;
        $skipped = 0;

        // Map legacy outcome type codes to new type names
        $typeMap = [
            'q1'         => 'journal_q1',
            'q2'         => 'journal_q2',
            'q3'         => 'journal_q3',
            'q4'         => 'journal_q4',
            'conference' => 'conference',
            'conf'       => 'conference',
            'bookChap'   => 'book_chapter',
            'book_chapter' => 'book_chapter',
            'book'       => 'book',
            'pubBook'    => 'book',
            'editedBook' => 'edited_book',
            'editBook'   => 'edited_book',
            'edited_book' => 'edited_book',
            'ip'         => 'ip_disclosure',
            'ip_disclosure' => 'ip_disclosure',
            'patent'     => 'provisional_patent',
            'provisional_patent' => 'provisional_patent',
            'patent_granted' => 'patent_granted',
            'oss'        => 'open_source_sw',
            'open_source_sw' => 'open_source_sw',
            'startup'    => 'startup',
            'startUp'    => 'startup',
        ];

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            if (!$newProjectId) {
                $this->logSkip('project_outcomes', $row, "project_id {$row['project_id']} not in projectMap");
                $skipped++;
                continue;
            }

            // Map the legacy type to new type
            $legacyType = trim($row['type'] ?? '');
            $newType = $typeMap[$legacyType] ?? null;

            // Skip entries that are pillar IDs (numeric) or pillar names (not in type map)
            if ($newType === null) {
                // If it's a numeric ID or a long string (pillar name), skip it
                if (is_numeric($legacyType) || strlen($legacyType) > 20) {
                    $skipped++;
                    continue;
                }
                // Try lowercase match
                $newType = $typeMap[strtolower($legacyType)] ?? null;
                if ($newType === null) {
                    $this->logSkip('project_outcomes', $row, "unknown outcome type '{$legacyType}'");
                    $skipped++;
                    continue;
                }
            }

            // Skip if already exists (re-run safety)
            if (DB::table('project_outcomes')->where('project_id', $newProjectId)->where('type', $newType)->where('identifier', $row['identifier'])->exists()) {
                continue;
            }

            $data = [
                'project_id' => $newProjectId,
                'user_id' => $newUserId,
                'type' => $newType,
                'identifier' => $row['identifier'],
                'online_date' => $this->cleanDate($row['online_date']),
                'verifcation_by_system' => $row['verifcation_by_system'] ?? 'pending',
                'verifcation_by_reviewer' => $row['verifcation_by_reviewer'] ?? 'pending',
                'score' => $row['score'] ?? 0,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $newId = $this->writeOne('project_outcomes', $data);
            $this->outcomeMap[$row['id']] = $newId;
            $imported++;
        }

        $this->log("Project Outcomes: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROJECT PUBLICATIONS (from publication_detail)
    // ─────────────────────────────────────────────────────────

    private function importProjectPublications(): void
    {
        if ($this->shouldSkip('project_publications')) return;

        $this->info('Importing project publications...');
        $rows = $this->tableData('publication_detail');
        $imported = 0;
        $skipped = 0;

        // Build outcome→project mapping
        $outcomeProjectMap = [];
        foreach ($this->tableData('outcomes') as $o) {
            $newOutcomeId = $this->outcomeMap[$o['id']] ?? null;
            $newProjectId = $this->projectMap[$o['project_id']] ?? null;
            if ($newOutcomeId && $newProjectId) {
                $outcomeProjectMap[$o['id']] = $newProjectId;
            }
        }

        foreach ($rows as $row) {
            $projectId = $outcomeProjectMap[$row['outcome_id']] ?? null;
            if (!$projectId) {
                $this->logSkip('project_publications', $row, "outcome_id {$row['outcome_id']} not mapped to project");
                $skipped++;
                continue;
            }

            $year = null;
            if (!empty($row['publication_date'])) {
                $year = substr($row['publication_date'], 0, 4);
            }

            $data = [
                'project_id' => $projectId,
                'publication_title' => $row['title'],
                'journal' => $row['venue'] ?? null,
                'year' => $year,
                'doi' => $row['url'] ?? null,
                'status' => 'published',
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('project_publications', $data);
            $imported++;
        }

        $this->log("Project Publications: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROJECT PILLAR
    // ─────────────────────────────────────────────────────────

    private function importProjectPillar(): void
    {
        if ($this->shouldSkip('project_pillar')) return;

        $this->info('Importing project-pillar relations...');
        $rows = $this->tableData('project_pillar');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            $newPillarId = $this->pillarMap[$row['pillar_id']] ?? null;
            if (!$newProjectId || !$newPillarId) {
                $reason = !$newProjectId ? "project_id {$row['project_id']} not in projectMap" : "pillar_id {$row['pillar_id']} not in pillarMap";
                $this->logSkip('project_pillar', $row, $reason);
                $skipped++;
                continue;
            }

            // Skip if already exists (re-run safety)
            if (DB::table('project_pillar')->where('project_id', $newProjectId)->where('pillar_id', $newPillarId)->exists()) {
                continue;
            }

            $data = [
                'project_id' => $newProjectId,
                'pillar_id' => $newPillarId,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('project_pillar', $data);
            $imported++;
        }

        $this->log("Project Pillar: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROJECT STUDENTS (from attached_students)
    // ─────────────────────────────────────────────────────────

    private function importProjectStudents(): void
    {
        if ($this->shouldSkip('project_students')) return;

        $this->info('Importing project students (from attached_students)...');
        $rows = $this->tableData('attached_students');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            if (!$newProjectId) {
                $this->logSkip('project_students', $row, "project_id {$row['project_id']} not in projectMap");
                $skipped++;
                continue;
            }

            // Look up student name from users table
            $studentName = null;
            $college = null;
            $department = null;
            if ($newUserId) {
                $user = DB::table('users')->where('id', $newUserId)->first();
                if ($user) {
                    $studentName = $user->name;
                    $college = $user->college;
                    $department = $user->department;
                }
            }

            $stdId = $row['std_id'] ?? '';
            $type = $row['type'] ?? 'UG';

            // Skip if already exists (re-run safety)
            if (DB::table('project_students')->where('project_id', $newProjectId)->where('student_id', $stdId)->exists()) {
                continue;
            }

            $data = [
                'project_id' => $newProjectId,
                'student_name' => $studentName ?? 'Unknown',
                'student_id' => $stdId,
                'college' => $college,
                'department' => $department,
                'role' => $type,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('project_students', $data);
            $imported++;
        }

        $this->log("Project Students (attached): {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: STUDENT GRANT STUDENTS
    // ─────────────────────────────────────────────────────────

    private function importStudentGrantStudents(): void
    {
        if ($this->shouldSkip('studentgrant_students')) return;

        $this->info('Importing student grant students...');
        $rows = $this->tableData('studentgrant_students');
        $imported = 0;
        $skipped = 0;
        $usersCreated = 0;

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            if (!$newProjectId) {
                $this->logSkip('commitments', $row, "project_id {$row['project_id']} not in projectMap");
                $skipped++;
                continue;
            }

            // Skip if already exists (re-run safety)
            if (DB::table('commitments')->where('project_id', $newProjectId)->exists()) {
                continue;
            }

            // Find or create user by email
            $email = $row['email'];
            $existingUser = DB::table('users')->where('email', $email)->first();
            $userId = $existingUser?->id;

            if (!$userId) {
                // Create user from student data
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                $userId = $this->writeOne('users', [
                    'name' => $name,
                    'email' => $email,
                    'password' => '',
                    'type' => 'Student',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $usersCreated++;
            }

            // Check if project_students record already exists
            $studentIdStr = (string) ($row['student_id'] ?? '');
            $exists = DB::table('project_students')
                ->where('project_id', $newProjectId)
                ->where('student_id', $studentIdStr)
                ->first();

            if ($exists) {
                $projectStudentId = $exists->id;
            } else {
                // Create project_students record
                $type = $this->mapStudentType($row['std_level'] ?? '');
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                $projectStudentId = DB::table('project_students')->insertGetId([
                    'project_id' => $newProjectId,
                    'student_name' => $name ?: 'Unknown',
                    'student_id' => $studentIdStr,
                    'college' => $row['college'] ?? null,
                    'role' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create project_students_details record
            $this->write('project_students_details', [
                'project_student_id' => $projectStudentId,
                'student_id' => (string) ($row['student_id'] ?? ''),
                'first_name' => $row['first_name'] ?? null,
                'last_name' => $row['last_name'] ?? null,
                'student_status' => $row['student_status'] ?? null,
                'major' => $row['major'] ?? null,
                'minor' => $row['minor'] ?? null,
                'college' => $row['college'] ?? null,
                'std_program' => $row['std_program'] ?? null,
                'std_level' => $row['std_level'] ?? null,
                'admission_term' => $row['admission_term'] ?? null,
                'reg_in_course' => $row['reg_in_course'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $imported++;
        }

        $this->log("Student Grant Students: {$imported} imported ({$usersCreated} new users created), {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: FINAL REPORT GRADING
    // ─────────────────────────────────────────────────────────

    private function importFinalReportGrading(): void
    {
        if ($this->shouldSkip('final_report_grading')) return;

        $this->info('Importing final report grading...');
        $rows = $this->tableData('final_report_grading');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            if (!$newProjectId || !$newUserId) {
                $reason = !$newProjectId ? "project_id {$row['project_id']} not in projectMap" : "user_id {$row['user_id']} not in userMap";
                $this->logSkip('final_report_grading', $row, $reason);
                $skipped++;
                continue;
            }

            // Skip if already exists (re-run safety)
            if (DB::table('final_report_grading')->where('project_id', $newProjectId)->where('user_id', $newUserId)->exists()) {
                continue;
            }

            $data = [
                'gradeA' => $row['gradeA'] ?? null,
                'commentA' => $row['commentA'] ?? null,
                'gradeB' => $row['gradeB'] ?? null,
                'commentB' => $row['commentB'] ?? null,
                'gradeC' => $row['gradeC'] ?? null,
                'commentC' => $row['commentC'] ?? null,
                'gradeD' => $row['gradeD'] ?? null,
                'commentD' => $row['commentD'] ?? null,
                'total' => $row['total'] ?? null,
                'user_id' => $newUserId,
                'project_id' => $newProjectId,
                'publish' => $row['publish'] ?? 'pending',
                'isAdmin' => $row['isAdmin'] ?? false,
                'isAccepted' => $row['isAccepted'] ?? 0,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('final_report_grading', $data);
            $imported++;
        }

        $this->log("Final Report Grading: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: PROGRESS REPORT GRADING
    // ─────────────────────────────────────────────────────────

    private function importProgressReportGrading(): void
    {
        if ($this->shouldSkip('progress_report_grading')) return;

        $this->info('Importing progress report grading...');
        $rows = $this->tableData('progress_report_grading');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newProjectId = $this->projectMap[$row['project_id']] ?? null;
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            if (!$newProjectId || !$newUserId) {
                $reason = !$newProjectId ? "project_id {$row['project_id']} not in projectMap" : "user_id {$row['user_id']} not in userMap";
                $this->logSkip('progress_report_grading', $row, $reason);
                $skipped++;
                continue;
            }

            // Skip if already exists (re-run safety)
            if (DB::table('progress_report_grading')->where('project_id', $newProjectId)->where('user_id', $newUserId)->exists()) {
                continue;
            }

            $data = [
                'analysis' => $row['analysis'] ?? null,
                'comments' => $row['comments'] ?? null,
                'recommendation' => $row['recommendation'] ?? null,
                'path' => $row['path'] ?? null,
                'user_id' => $newUserId,
                'project_id' => $newProjectId,
                'publish' => $row['publish'] ?? 'pending',
                'achievementsRating' => $row['achievementsRating'] ?? 1,
                'publicationsRating' => $row['publicationsRating'] ?? 1,
                'studentsRating' => $row['studentsRating'] ?? 1,
                'achievementsComments' => $row['achievementsComments'] ?? null,
                'publicationsComments' => $row['publicationsComments'] ?? null,
                'studentsComments' => $row['studentsComments'] ?? null,
                'ethical' => $row['ethical'] ?? -1,
                'isAccepted' => $row['isAccepted'] ?? 0,
                'budgetRating' => $row['budgetRating'] ?? null,
                'budgetComments' => $row['budgetComments'] ?? null,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('progress_report_grading', $data);
            $imported++;
        }

        $this->log("Progress Report Grading: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // FIX: Create missing submissions for orphan gradings
    // ─────────────────────────────────────────────────────────

    private function fixOrphanGradings(): void
    {
        $this->info('Fixing orphan grading records (creating missing submissions)...');
        $fixed = 0;

        // Find progress_report_grading records with no matching progress submission
        $orphanPRG = DB::table('progress_report_grading as prg')
            ->whereNotIn('project_id', function ($q) {
                $q->select('project_id')
                    ->from('project_submissions')
                    ->whereRaw("LOWER(type) = 'progress'");
            })
            ->get();

        foreach ($orphanPRG as $row) {
            // Use the grading's created_at as submission date
            $data = [
                'project_id'        => $row->project_id,
                'user_id'           => $row->user_id,
                'type'              => 'progress',
                'original_filename' => 'Legacy progress report',
                'stored_filename'   => 'legacy_progress',
                'version'           => 1,
                'file_path'         => 'legacy/' . $row->project_id . '/progress/grading_' . $row->id,
                'notes'             => 'Auto-created from orphan grading record',
                'submitted'         => 1,
                'submitted_at'      => $row->created_at,
                'created_at'        => $row->created_at,
                'updated_at'        => $row->updated_at ?? $row->created_at,
            ];

            DB::table('project_submissions')->insert($data);
            $this->log("Fixed orphan progress grading for project_id {$row->project_id} (grading_id {$row->id})");
            $fixed++;
        }

        // Find final_report_grading records with no matching final submission
        $orphanFRG = DB::table('final_report_grading as frg')
            ->whereNotIn('project_id', function ($q) {
                $q->select('project_id')
                    ->from('project_submissions')
                    ->whereRaw("LOWER(type) = 'final'");
            })
            ->get();

        foreach ($orphanFRG as $row) {
            $data = [
                'project_id'        => $row->project_id,
                'user_id'           => $row->user_id,
                'type'              => 'final',
                'original_filename' => 'Legacy final report',
                'stored_filename'   => 'legacy_final',
                'version'           => 1,
                'file_path'         => 'legacy/' . $row->project_id . '/final/grading_' . $row->id,
                'notes'             => 'Auto-created from orphan grading record',
                'submitted'         => 1,
                'submitted_at'      => $row->created_at,
                'created_at'        => $row->created_at,
                'updated_at'        => $row->updated_at ?? $row->created_at,
            ];

            DB::table('project_submissions')->insert($data);
            $this->log("Fixed orphan final grading for project_id {$row->project_id} (grading_id {$row->id})");
            $fixed++;
        }

        $this->log("Orphan Gradings Fixed: {$fixed} missing submissions created");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: REVIEWER GRADING
    // ─────────────────────────────────────────────────────────

    private function importReviewerGrading(): void
    {
        if ($this->shouldSkip('reviewer_grading')) return;

        $this->info('Importing reviewer grading...');
        $rows = $this->tableData('reviewer_grading');
        $imported = 0;

        foreach ($rows as $row) {
            $data = [
                'reviewer' => $row['reviewer'],
                'cycle' => $row['cycle'],
                'conflict' => $row['conflict'],
                'responsiveness' => $row['responsiveness'],
                'comprehensiveness' => $row['comprehensiveness'],
                'no_reviewers' => $row['no_reviewers'],
                'behaviour' => $row['behaviour'],
                'scope_of_supply' => $row['scope_of_supply'] ?? 'Written Scientific Review',
                'mode_of_selection' => $row['mode_of_selection'] ?? 'From ORS Database',
                'basis_of_approval' => $row['basis_of_approval'] ?? 'Previous Successful Review',
                'type_extent_of_control' => $row['type_extent_of_control'] ?? 'Former review Evaluation',
                'designation_of_approver' => $row['designation_of_approver'] ?? 'Post-Award Manager',
                'user_id' => $row['user_id'],
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('reviewer_grading', $data);
            $imported++;
        }

        $this->log("Reviewer Grading: {$imported} imported");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: TEAM (from about_us)
    // ─────────────────────────────────────────────────────────

    private function importTeam(): void
    {
        if ($this->shouldSkip('team')) return;

        $this->info('Importing team (from about_us)...');
        $rows = $this->tableData('about_us');
        $imported = 0;

        foreach ($rows as $row) {
            $data = [
                'path' => $row['path'] ?? '',
                'name' => $row['name'],
                'role' => $row['role'] ?? '',
                'introduction' => $row['introduction'] ?? '',
                'email' => $row['email'] ?? null,
                'phone' => $row['phone'] ?? null,
                'address' => $row['address'] ?? null,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('team', $data);
            $imported++;
        }

        $this->log("Team: {$imported} imported from about_us");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: EMAIL TEMPLATES (from email_content)
    // ─────────────────────────────────────────────────────────

    private function importEmailTemplates(): void
    {
        if ($this->shouldSkip('email_templates')) return;

        $this->info('Importing email templates...');
        $rows = $this->tableData('email_content');
        $imported = 0;

        foreach ($rows as $row) {
            $body = ($row['contenta'] ?? '') . "\n\n" . ($row['contentb'] ?? '');
            $signature = ($row['farewell'] ?? '') . "\n" . ($row['regards'] ?? '');

            $data = [
                'name' => $row['title'] ?? $row['subject'],
                'subject' => $row['subject'],
                'body' => trim($body),
                'signature' => trim($signature),
                'category' => 'general',
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ];

            $this->write('email_templates', $data);
            $imported++;
        }

        $this->log("Email Templates: {$imported} imported");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: EMAIL SEND LOG (from email_sending_status)
    // ─────────────────────────────────────────────────────────

    private function importEmailSendLog(): void
    {
        if ($this->shouldSkip('email_send_log')) return;

        $this->info('Importing email send log...');
        $rows = $this->tableData('email_sending_status');
        $imported = 0;
        $skipped = 0;

        // Find an admin user to use as sent_by fallback
        $adminUser = DB::table('users')->where('type', 'Admin')->first();
        $sentBy = $adminUser?->id ?? 1;

        foreach ($rows as $row) {
            $status = 'sent';
            if (strtolower($row['sending_status'] ?? '') !== 'success') {
                $status = 'failed';
            }

            $data = [
                'sent_by' => $sentBy,
                'recipient_email' => $row['email'] ?? '',
                'subject' => $row['title'] ?? '',
                'body' => $row['body'] ?? '',
                'status' => $status,
                'error_message' => $row['error_message'] ?? null,
                'sent_at' => $this->cleanDate($row['datetime']),
                'created_at' => $this->cleanDate($row['datetime']),
                'updated_at' => $this->cleanDate($row['datetime']),
            ];

            $this->write('email_send_log', $data);
            $imported++;
        }

        $this->log("Email Send Log: {$imported} imported");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: RATINGS
    // ─────────────────────────────────────────────────────────

    private function importRatings(): void
    {
        if ($this->shouldSkip('ratings')) return;

        $this->info('Importing ratings...');
        $rows = $this->tableData('ratings');
        $imported = 0;

        // Ratings are pre-seeded by migration (1-5). Only import if table is empty.
        $existingCount = DB::table('ratings')->count();
        if ($existingCount > 0) {
            $this->log("Ratings: Skipped ({$existingCount} records already seeded)");
            return;
        }

        foreach ($rows as $row) {
            $this->write('ratings', [
                'id' => $row['id'],
                'rating' => $row['rating'],
            ]);
            $imported++;
        }

        $this->log("Ratings: {$imported} imported");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: GAUGE SETTINGS
    // ─────────────────────────────────────────────────────────

    private function importGaugeSettings(): void
    {
        if ($this->shouldSkip('gauge_settings')) return;

        $this->info('Importing gauge settings...');
        $rows = $this->tableData('guage_settings');
        $imported = 0;

        foreach ($rows as $row) {
            $existing = DB::table('gauge_settings')->where('name', $row['title'])->first();
            if ($existing) {
                // Update existing record with legacy values
                if (!$this->dryRun) {
                    DB::table('gauge_settings')->where('id', $existing->id)->update([
                        'redfrom' => $row['redfrom'],
                        'redto' => $row['redto'],
                        'yellowfrom' => $row['yellowfrom'],
                        'yellowto' => $row['yellowto'],
                        'greenfrom' => $row['greenfrom'],
                        'greento' => $row['greento'],
                        'updated_at' => $this->cleanDate($row['updated_at']),
                    ]);
                }
            } else {
                $data = [
                    'name' => $row['title'],
                    'redfrom' => $row['redfrom'],
                    'redto' => $row['redto'],
                    'yellowfrom' => $row['yellowfrom'],
                    'yellowto' => $row['yellowto'],
                    'greenfrom' => $row['greenfrom'],
                    'greento' => $row['greento'],
                    'created_at' => $this->cleanDate($row['created_at']),
                    'updated_at' => $this->cleanDate($row['updated_at']),
                ];
                $this->write('gauge_settings', $data);
            }
            $imported++;
        }

        $this->log("Gauge Settings: {$imported} processed (updated or created)");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: USER COLLEGES (from legacy users_tags)
    // ─────────────────────────────────────────────────────────

    private function importUserColleges(): void
    {
        if ($this->shouldSkip('user_colleges')) return;

        $this->info('Importing user colleges (from users_tags)...');
        $rows = $this->tableData('users_tags');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            $newCollegeId = $this->tagIdToCollegeMap[$row['tag_id']] ?? null;

            if (!$newUserId) {
                $this->logSkip('user_colleges', $row, "user_id {$row['user_id']} not in userMap");
                $skipped++;
                continue;
            }
            if (!$newCollegeId) {
                $this->logSkip('user_colleges', $row, "tag_id {$row['tag_id']} not in tagIdToCollegeMap");
                $skipped++;
                continue;
            }

            // Skip if already exists (re-run safety)
            if (DB::table('user_colleges')->where('user_id', $newUserId)->where('college_id', $newCollegeId)->exists()) {
                continue;
            }

            $this->write('user_colleges', [
                'user_id' => $newUserId,
                'college_id' => $newCollegeId,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ]);
            $imported++;
        }

        $this->log("User Colleges: {$imported} imported, {$skipped} skipped");
    }

    // ─────────────────────────────────────────────────────────
    // IMPORT: USER PILLARS
    // ─────────────────────────────────────────────────────────

    private function importUserPillars(): void
    {
        if ($this->shouldSkip('user_pillars')) return;

        $this->info('Importing user pillars...');
        $rows = $this->tableData('user_pillars');
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $newUserId = $this->userMap[$row['user_id']] ?? null;
            $newPillarId = $this->pillarMap[$row['pillar_id']] ?? null;

            if (!$newUserId) {
                $this->logSkip('user_pillars', $row, "user_id {$row['user_id']} not in userMap");
                $skipped++;
                continue;
            }
            if (!$newPillarId) {
                $this->logSkip('user_pillars', $row, "pillar_id {$row['pillar_id']} not in pillarMap");
                $skipped++;
                continue;
            }

            // Skip if already exists (re-run safety)
            if (DB::table('user_pillars')->where('user_id', $newUserId)->where('pillar_id', $newPillarId)->exists()) {
                continue;
            }

            $this->write('user_pillars', [
                'user_id' => $newUserId,
                'pillar_id' => $newPillarId,
                'created_at' => $this->cleanDate($row['created_at']),
                'updated_at' => $this->cleanDate($row['updated_at']),
            ]);
            $imported++;
        }

        $this->log("User Pillars: {$imported} imported, {$skipped} skipped");
    }
}
