<?php

namespace App\Console\Commands;

use App\Http\Controllers\ProgramController;
use App\Models\Grant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Imports the "Internal grants" conf-tool master workbook (one tab per grant)
 * into Cycle research calls.
 *
 * Usage:
 *   php artisan import:grants-2026
 *   php artisan import:grants-2026 --source="storage/app/imports/Internal grants 2026.xlsx"
 *   php artisan import:grants-2026 --cycle=2026 --password=secret
 *   php artisan import:grants-2026 --dry-run
 *
 * Idempotent — grant types / programs that already exist (matched by code /
 * title) are reported and skipped, so the command can be re-run safely.
 */
class ImportGrants2026 extends Command
{
    protected $signature = 'import:grants-2026
        {--source=storage/app/imports/Internal grants 2026.xlsx : Master workbook path}
        {--cycle=2026 : Cycle year under which research calls are created}
        {--act-as= : Email of the admin user to act as (defaults to the first admin)}
        {--password= : Set this password for LPI users auto- or re-created during the import}
        {--dry-run : Report what would be imported without writing anything}';

    protected $description = 'Import the internal-grants master workbook (one tab per grant) as research calls — grant types, projects, users, colleges, deadlines';

    /** sheet name => [grant_code, call title, category] */
    private array $tabs = [
        'CG'          => ['grant' => 'QUCG', 'title' => 'Collaborative Grants'],
        'GOV'         => ['grant' => 'GOV',  'title' => 'Governmental and Industrial Collaboration Grants'],
        'IKT'         => ['grant' => 'IKT',  'title' => 'Innovation and Knowledge Transfer Grants (Team)'],
        'IKT-Student' => ['grant' => 'IKTS', 'title' => 'Innovation and Knowledge Transfer Grants (Student)', 'category' => 'student'],
        'QUCP'        => ['grant' => 'QUCP', 'title' => 'National Capacity Building Grants (QUCP)'],
        'ICG'         => ['grant' => 'ICG',  'title' => 'Institutional Collaboration Grants'],
    ];

    /** source sheet => data-row column map discovered from the 2026 workbook */
    private const COLUMN_MAPS = [
        //  0 = '#', then per-sheet layout: pid/lpi/job/email/user/college/rank/title/start/end
        'CG'          => ['pid' => 2, 'lpi' => 4, 'job' => 5, 'email' => 6, 'user' => 7, 'college' => 8, 'rank' => 9, 'title' => 10, 'start' => 11, 'end' => 12],
        'GOV'         => ['pid' => 1, 'lpi' => 3, 'job' => 4, 'email' => 5, 'user' => 6, 'college' => 7, 'rank' => 8, 'title' => 9,  'start' => 10, 'end' => 11],
        'IKT'         => ['pid' => 1, 'lpi' => 3, 'job' => 4, 'email' => 5, 'user' => 6, 'college' => 7, 'rank' => 8, 'title' => 9,  'start' => 10, 'end' => 11],
        'IKT-Student' => ['pid' => 1, 'lpi' => 3, 'job' => 4, 'email' => 5, 'user' => 6, 'college' => 7, 'rank' => 8, 'title' => 9,  'start' => 10, 'end' => 11],
        'QUCP'        => ['pid' => 2, 'lpi' => 3, 'job' => 4, 'email' => 5, 'user' => 6, 'college' => 7, 'rank' => 8, 'title' => 9,  'start' => 10, 'end' => 11],
        'ICG'         => ['pid' => 1, 'lpi' => 4, 'job' => 5, 'email' => 6, 'user' => 7, 'college' => 8, 'rank' => 9, 'title' => 10, 'start' => null, 'end' => 12],
    ];

    /** conf-tool college code => college-table code */
    private const COLLEGE_ALIASES = [
        'CNUR'      => 'CON',      // College of Nursing
        'IKCHSS'    => 'IKHSS',    // Ibn Khaldon Center
        'QU HEALTH' => 'QH',       // QU Health
    ];

    /** college codes not present in the table — safe to auto-create on demand */
    private const AUTO_CREATE_COLLEGES = [
        'DGS'  => 'Deanship of General Studies',
        'DLOE' => 'Office for Digital Learning and Online Education',
    ];

    public function handle(): int
    {
        $source    = $this->option('source');
        $cycleYear = (int) $this->option('cycle');
        $dryRun    = (bool) $this->option('dry-run');
        $secret    = $this->option('password');
        $actAs     = (string) $this->option('act-as');

        $sourcePath = realpath($source) ?: $source;
        if (!is_file($sourcePath)) {
            $this->error("Source workbook not found: {$source}");
            return 1;
        }

        // acting admin
        $admin = $actAs !== '' && $actAs !== null
            ? User::where('email', $actAs)->first()
            : User::whereRaw('FIND_IN_SET("Admin", type) > 0')->first();
        if (!$admin || !$admin->isAdmin()) {
            $this->error($actAs
                ? "`{$actAs}` is not an admin — use --act-as= with an admin email."
                : 'No admin user found — use --act-as=<admin email>.');
            return 1;
        }
        Auth::login($admin);

        // cycle
        $cycleId = DB::table('cycle_configs')->where('year', $cycleYear)->value('id');
        if (!$cycleId) {
            $this->error("Cycle {$cycleYear} does not exist — create it (System Settings → Cycles) or pass --cycle=<year>.");
            return 1;
        }

        $workbook = IOFactory::load($sourcePath);
        $totalImported = 0;

        foreach ($this->tabs as $tabName => $meta) {
            if ($workbook->getSheetByName($tabName) === null) {
                $this->warn("Tab [{$tabName}] not found in source — skipping.");
                continue;
            }

            $grantCode = $meta['grant'];
            $category  = $meta['category'] ?? 'regular';
            $programTitle = "{$grantCode} - Cycle {$cycleYear}";

            $program = Program::where('program_title', $programTitle)->first();
            if ($program) {
                $this->line("<comment>⟳</comment> program '{$programTitle}' exists (id {$program->id}) — skipped");
                continue;
            }

            // grant type
            $grant = Grant::where('grant_code', $grantCode)->first();
            if (!$grant) {
                if (in_array($grantCode, ['QUCG', 'QUCP'], true)) {
                    $this->error("Required grant type {$grantCode} is missing — expected to pre-exist. Aborting.");
                    return 1;
                }
                if ($dryRun) {
                    $this->line("[dry-run] would create grant type {$grantCode} ({$category}) — {$meta['title']}");
                } else {
                    $grant = Grant::create([
                        'grant_code' => $grantCode,
                        'grant_name' => $meta['title'],
                        'category'   => $category,
                        'is_active'  => true,
                    ]);
                    $this->info("<info>＋</info> grant type {$grantCode} ({$category}) created (id {$grant->id})");
                }
            }
            if ($dryRun) {
                $this->line("[dry-run] would create program '{$programTitle}' from tab [{$tabName}]");
                continue;
            }

            // normalize the tab into an importable 12/19-column file
            $split = $this->buildNormalizedFile($workbook->getSheetByName($tabName), $tabName);

            if (empty($split['rows'])) {
                $this->line("<comment>○</comment> tab [{$tabName}]: no importable rows — skipped");
                continue;
            }
            foreach ($split['skipped'] as $s) {
                $this->warn("  skipped row in [{$tabName}]: {$s}");
            }

            // run the REAL importer (same code path as the research-call UI)
            $upload = new UploadedFile($split['path'], basename($split['path']),
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
            $request = Request::create('/programs', 'POST', [
                'grant_id'      => $grant->id,
                'cycle_id'      => $cycleId,
                'program_title' => $programTitle,
                'description'   => 'Imported automatically by import:grants-2026',
            ], [], [], [
                'HTTP_ACCEPT'  => 'application/json',
                'CONTENT_TYPE' => 'multipart/form-data',
            ]);
            $request->files->set('excel', $upload);

            $response  = (new ProgramController())->store($request);
            $payload   = json_decode($response->getContent(), true);
            $programId = $payload['program']['id'] ?? null;
            $importCount = $payload['importCount'] ?? 0;
            $totalImported += (int) $importCount;

            $this->info("<info>●</info> program '{$programTitle}' (id {$programId}): {$importCount} projects imported");

            foreach (array_slice($payload['importErrors'] ?? [], 0, 10) as $e) {
                $this->warn("  ! {$e}");
            }

            // deadlines: PR1 = PR2 = final = the tab's latest End date
            if (!empty($split['finalDeadline'])) {
                DB::table('programs')->where('id', $programId)->update([
                    'prog_rpt_deadline'  => $split['finalDeadline'],
                    'prog_rpt2_deadline' => substr($split['finalDeadline'], 0, 10),
                    'final_rpt_deadline' => $split['finalDeadline'],
                ]);
                $this->line("   deadlines — PR1 = PR2 = final = {$split['finalDeadline']}");
            } else {
                $this->warn('   no End-dates in tab — deadlines left unset');
            }

            // optional known password for the LPI users of this program
            if (!empty($secret)) {
                $userIds = $this->getProgramLpiIds((int) $programId);
                DB::table('users')->whereIn('id', $userIds)->update([
                    'password' => Hash::make($secret),
                ]);
                $this->line("   password set for {$userIds->count()} LPI user(s)");
            }

            @unlink($split['path']);
        }

        // summary of the proposal-PDF gap (proposals zip is not part of this command)
        $missingPdf = DB::table('projects')
            ->join('programs', 'programs.id', '=', 'projects.program_id')
            ->where('programs.cycle_id', $cycleId)
            ->whereNull('projects.proposal_filename')
            ->count();
        $this->newLine();
        $this->line("Cycle {$cycleYear} summary: <info>{$totalImported}</info> projects imported; <comment>{$missingPdf}</comment> project(s) still without a proposal PDF.");

        return 0;
    }

    /**
     * Normalize one source sheet into the app importer's row shape:
     *  - regular: 12 columns (id, old_project_id, cycle, title, author, email,
     *    added, created_at, updated_at, pillars, tags, grant_type) + qu_user_name
     *  - student: 19 columns matching the legacy student layout + qu_user_name
     * Writes a temp .xlsx and returns its path along with the max End date.
     */
    private function buildNormalizedFile($sheet, string $tabName): array
    {
        $map    = self::COLUMN_MAPS[$tabName];
        $isStudent = $tabName === 'IKT-Student';
        $label  = $this->tabs[$tabName]['title'];

        $rows = [];
        $skipped = [];
        $finalDeadline = null;

        $data = $sheet->toArray();

        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }
            $pid = trim((string) ($row[$map['pid']] ?? ''));
            // conf-tool pattern e.g. QUCG-CENG-26/27-897, QUIKT-BRC-2026-936,
            // QUST-1-CENG-2025-196, QUCG-QU Health-26/27-924
            if (empty($pid) || !preg_match('/^QU[A-Z]{2,5}(-\d)?-[A-Z0-9 ]{2,14}-(\d{4}|\d{2}\/\d{2})-\d+$/i', $pid)
                || $pid === 'Project ID') {
                $junk = trim((string) ($row[$map['title']] ?? ''));
                if ($junk !== '' && $junk !== 'Project Title' && $junk !== null) {
                    $skipped[] = $pid !== '' ? $pid : substr($junk, 0, 40);
                }
                continue;
            }

            $title  = trim((string) ($row[$map['title']] ?? ''));
            $lpi    = trim((string) ($row[$map['lpi']] ?? ''));
            $email  = trim((string) ($row[$map['email']] ?? ''));
            $user   = trim((string) ($row[$map['user']] ?? ''));
            $jobid  = trim((string) ($row[$map['job']] ?? ''));
            $rank   = trim((string) ($row[$map['rank']] ?? ''));
            $igant  = trim((string) ($row[$map['pid'] !== null ? $map['pid'] - 1 : 0] ?? ''));

            if ($title === '') {
                $skipped[] = $pid;
                continue;
            }

            // colleges → alias-mapped, comma-joined codes; missing ones upserted
            $codesRaw = trim((string) ($row[$map['college']] ?? ''));
            $codesRaw = str_replace(["\r", "\n"], ' ', $codesRaw);
            $codes = [];
            foreach (explode(',', $codesRaw) as $c) {
                $c = strtoupper(trim($c));
                if ($c === '') {
                    continue;
                }
                $c = self::COLLEGE_ALIASES[$c] ?? $c;
                $this->ensureCollege($c);
                $codes[] = $c;
            }
            $tags = implode(', ', $codes);

            // End date → candidate final deadline
            $endRaw = $map['end'] !== null ? ($row[$map['end']] ?? null) : null;
            if ($endRaw instanceof \DateTimeInterface) {
                $end = Carbon::instance($endRaw)->format('Y-m-d H:i:s');
            } else {
                $endStr = trim((string) ($endRaw ?? ''));
                $end = $endStr !== '' ? Carbon::parse($endStr)->format('Y-m-d H:i:s') : null;
            }
            if ($end !== null && ($finalDeadline === null || strcmp($end, $finalDeadline) > 0)) {
                $finalDeadline = $end;
            }

            $seq = count($rows) + 1;

            if ($isStudent) {
                // 19-column student layout (index = what ProgramController reads)
                $rows[] = [
                    $seq,            // 0  '#'
                    $pid,            // 1  old_project_id
                    $jobid,          // 2  Job ID (not read)
                    '',              // 3  (not read)
                    $lpi,            // 4  author
                    $email,          // 5  email
                    $igant,          // 6  Project ID number (not read)
                    $tags,           // 7  tags → college codes
                    $title,          // 8  title
                    '',              // 9  pillars (not in source)
                    '',              // 10 duration
                    '',              // 11 students names
                    '',              // 12 student QUID(s)
                    '',              // 13 nationality
                    $rank,           // 14 (not read)
                    '',              // 15 requested budget
                    '',              // 16 colleges decision
                    '',              // 17 rsd feedback
                    '',              // 18 final rsd decision
                    $user,           // trailing: qu account user name (not read)
                ];
            } else {
                // 12-column regular layout (+ trailing qu account user name)
                $rows[] = [
                    $seq,            // 0  id
                    $pid,            // 1  old_project_id
                    $label,          // 2  cycle (not read)
                    $title,          // 3  title
                    $lpi,            // 4  author
                    $email,          // 5  email
                    '0',             // 6  added
                    '',              // 7  created_at raw (not read)
                    '',              // 8  updated_at (not read)
                    '',              // 9  pillars (not in source)
                    $tags,           // 10 tags → college codes
                    $category,       // 11 grant_type label
                    $user,           // trailing: qu account user name (not read)
                ];
            }
        }

        // normalize the last header (13 trailing column) before saving
        $path = storage_path('app/temp_import_' . preg_replace('/[^A-Za-z0-9]+/', '_', $tabName) . '.xlsx');
        if (!is_dir(storage_path('app'))) {
            mkdir(storage_path('app'), 0775, true);
        }

        $spread = new Spreadsheet();
        $sheetOut = $spread->getActiveSheet();
        if ($isStudent) {
            $header = ['#', 'Code', 'Job ID', 'LPI Name', 'Academic Rank', 'E-Mail address', 'Project ID',
                       'College/Center', 'Project Title', 'Research Area', 'Duration (one Academic Semester)',
                       'Students Names', 'Student QUID Number', 'Students Nationality', "Student's status",
                       'Requested Budget (IN QAR)', 'Colleges Decision', 'RSD Feedback (if any)', 'Final RSD Decision',
                       'qu_user_name'];
        } else {
            $header = ['id', 'old_project_id', 'cycle', 'title', 'author', 'email', 'added',
                       'created_at', 'updated_at', 'pillars', 'tags', 'grant_type', 'qu_user_name'];
        }
        $sheetOut->fromArray($header, null, 'A1');
        $sheetOut->fromArray($rows, null, 'A2');

        (new Xlsx($spread))->save($path);

        return ['rows' => $rows, 'skipped' => $skipped, 'path' => $path, 'finalDeadline' => $finalDeadline];
    }

    /** Create a college with the given code if it's whitelisted or missing entirely. */
    private function ensureCollege(string $code): void
    {
        if (DB::table('colleges')->where('code', $code)->exists() || $code === '') {
            return;
        }
        if (array_key_exists($code, self::AUTO_CREATE_COLLEGES)) {
            DB::table('colleges')->insert([
                'code'       => $code,
                'name'       => self::AUTO_CREATE_COLLEGES[$code],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }
        $this->warn("      college code '{$code}' not found in colleges — that project row will report an error (add the college first).");
    }

    /** LPI ids of a program's projects (for optional password seeding). */
    private function getProgramLpiIds(int $programId): array
    {
        return DB::table('projects')->where('program_id', $programId)
            ->whereNotNull('lpi_id')->pluck('lpi_id')->unique()->values()->all();
    }
}
