<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Grant;
use App\Models\CycleConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        })->only(['store', 'update', 'destroy', 'toggle', 'uploadProposals', 'uploadSingleProposal']);
    }

    // ─── ADMIN: Show/Hide Research Calls ──────────────────────────────────

    public function visibilityIndex(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $cycleId = $request->input('cycle_id');
        $visibility = $request->input('visibility'); // '' all | shown | hidden

        $query = Program::with(['grant', 'cycleConfig'])
            ->withCount('projects');

        if ($cycleId) {
            $query->where('cycle_id', $cycleId);
        }
        if ($visibility === 'shown') {
            $query->where('is_visible', true);
        } elseif ($visibility === 'hidden') {
            $query->where('is_visible', false);
        }

        $programs = $query->orderBy('program_title')->get();
        $cycleConfigs = CycleConfig::orderBy('year', 'desc')->get();

        return view('programs.visibility', compact(
            'programs', 'cycleConfigs', 'cycleId', 'visibility'
        ));
    }

    public function toggleVisibility(Request $request, $id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $program = Program::findOrFail($id);
        $program->update(['is_visible' => !$program->is_visible]);

        $state = $program->is_visible ? 'shown to LPIs' : 'hidden from LPIs';

        return redirect()->back()
            ->with('success', "Research call \"{$program->program_title}\" is now {$state}.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grant_id' => 'required|exists:grants,id',
            'cycle_id' => 'nullable|exists:cycle_configs,id',
            'program_title' => 'required|string|max:255|unique:programs,program_title',
            'prog_rpt_deadline' => 'nullable|date',
            'prog_rpt2_deadline' => 'nullable|date',
            'final_rpt_deadline' => 'nullable|date',
            'description' => 'nullable|string',
            'excel' => 'required|file|mimes:xlsx,xls,csv',
            'proposals_zip' => 'nullable|file|mimes:zip',
        ]);

        // Create the program
        $program = Program::create([
            'grant_id' => $validated['grant_id'],
            'cycle_id' => $validated['cycle_id'] ?? null,
            'program_title' => $validated['program_title'],
            'prog_rpt_deadline' => $validated['prog_rpt_deadline'] ?? null,
            'prog_rpt2_deadline' => $validated['prog_rpt2_deadline'] ?? null,
            'final_rpt_deadline' => $validated['final_rpt_deadline'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // Load the grant relationship
        $program->load('grant');

        $importCount = 0;
        $errors = [];

        // Process Excel file
        if ($request->hasFile('excel')) {
            $excelPath = $request->file('excel')->store('temp/excel_imports');

            try {
                $spreadsheet = IOFactory::load(storage_path('app/' . $excelPath));
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray();

                Log::info("Excel import: Loaded spreadsheet with " . count($data) . " rows");

                // Skip header row if present
                $startRow = 0;
                if (isset($data[0][0]) && !is_numeric($data[0][0])) {
                    $startRow = 1;
                }

                // Detect if student grant - check grant category first, then column count
                $isStudentGrant = false;
                $grantCategory = $program->grant ? strtolower($program->grant->category) : '';
                Log::info("Excel import: Grant category = {$grantCategory}");

                if ($grantCategory === 'student') {
                    $isStudentGrant = true;
                } else {
                    // Fallback: check column count (19+ columns = student grant)
                    $headerRow = $data[0] ?? [];
                    $columnCount = count(array_filter($headerRow, fn($v) => $v !== null && $v !== ''));
                    Log::info("Excel import: Column count = {$columnCount}");
                    $isStudentGrant = $columnCount >= 18;
                }
                Log::info("Excel import: isStudentGrant = " . ($isStudentGrant ? 'true' : 'false'));

                // Log header row for debugging
                Log::info("Excel import: Header row", $data[0] ?? []);

                Log::info("Excel import: Starting row processing from row {$startRow}, total rows: " . count($data));
                Log::info("Excel import: First row data", $data[0] ?? []);

                foreach (array_slice($data, $startRow) as $rowIndex => $row) {
                    // Ensure row is an array
                    if (!is_array($row)) {
                        Log::info("Excel import: Skipping row " . ($startRow + $rowIndex) . " - not an array");
                        continue;
                    }

                    $oldProjectId = trim($row[1] ?? '');
                    
                    if (empty($oldProjectId)) {
                        Log::info("Excel import: Skipping row " . ($startRow + $rowIndex) . " - no project ID. Row data: " . json_encode(array_slice($row, 0, 10)));
                        continue;
                    }

                    $author = trim($row[4] ?? '');
                    $email = trim($row[5] ?? '');

                    // Column mappings differ between regular and student grants
                    if ($isStudentGrant) {
                        // Student Grant (19 columns): title=8, pillars=9, tags=7
                        $title = trim($row[8] ?? '');
                        $pillarsRaw = trim($row[9] ?? '');
                        $tagsRaw = trim($row[7] ?? '');
                    } else {
                        // Regular Grant (12 columns): title=3, pillars=9, tags=10
                        $title = trim($row[3] ?? '');
                        $pillarsRaw = trim($row[9] ?? '');
                        $tagsRaw = trim($row[10] ?? '');
                    }

                    Log::info("Excel import: Row " . ($startRow + $rowIndex) . " - ID: {$oldProjectId}, Title: {$title}, Email: {$email}");

                    if (empty($title)) {
                        Log::info("Excel import: Skipping row " . ($startRow + $rowIndex) . " - no title");
                        continue;
                    }

                    try {
                        $projectData = [
                            'program_id' => $program->id,
                            'title' => $title,
                            'email' => $email,
                            'author' => $author,
                        ];

                        // Student grant additional fields
                        if ($isStudentGrant) {
                            $projectData['requested_budget_qar'] = !empty($row[15]) ? (float) str_replace(',', '', $row[15]) : null;
                            $projectData['college_decision'] = trim($row[16] ?? '');
                            $projectData['rsd_feedback'] = trim($row[17] ?? '');
                            $projectData['final_rsd_decision'] = trim($row[18] ?? '');
                            $projectData['grant_type'] = 'student';
                        }

                        Log::info("Excel import: Creating/updating project {$oldProjectId}", $projectData);
                        $project = \App\Models\Project::updateOrCreate(
                            ['old_project_id' => $oldProjectId, 'program_id' => $program->id],
                            $projectData
                        );
                        
                        if ($project->wasRecentlyCreated) {
                            Log::info("Excel import: NEW project {$oldProjectId} created with ID {$project->id}");
                        } else {
                            Log::info("Excel import: EXISTING project {$oldProjectId} updated (ID {$project->id})");
                        }

                        // Look up user by matching the email from the Excel against the users table.
                        if (!empty($email)) {
                            $matchedUser = \App\Models\User::where('email', $email)->first();
                            if ($matchedUser) {
                                if (!$matchedUser->is_active) {
                                    $matchedUser->update(['is_active' => true]);
                                }
                                Log::info("Excel import: Found existing user {$email}");
                            } else {
                                $matchedUser = \App\Models\User::create([
                                    'name' => $author ?: $email,
                                    'email' => $email,
                                    'type' => 'LPI',
                                    'is_active' => true,
                                    'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                                ]);
                                Log::info("Excel import: Created new user {$email}");
                            }
                            $project->update(['lpi_id' => $matchedUser->id]);
                        } else {
                            Log::info("Excel import: No email for project {$oldProjectId}, skipping user assignment");
                        }

                        // Record status in status_histories for each imported project
                        // Student grants: auto-register (no LPI registration step)
                        // Regular grants: mark as unregistered (LPI must register later)
                        if ($isStudentGrant) {
                            if (!$project->hasStatus('registered')) {
                                $project->recordStatus('registered', ['imported' => true], auth()->id());
                            }
                        } else {
                            if (!$project->hasStatus('unregistered')) {
                                $project->recordStatus('unregistered', ['imported' => true], auth()->id());
                            }
                        }

                        // Notify the LPI about the newly imported project (automatic,
                        // only on first creation so re-imports don't spam).
                        if ($project->wasRecentlyCreated && $matchedUser) {
                            app(\App\Services\EventMailService::class)->send(
                                'project_imported',
                                $matchedUser,
                                $project->fresh(),
                                auth()->user()
                            );
                        }

                        // Process pillars
                        if (!empty($pillarsRaw)) {
                            $pillarValues = array_map('trim', preg_split('/[\r\n]+/', $pillarsRaw));
                            $pillarValues = array_filter($pillarValues, function($v) { return $v !== ''; });
                            $foundPillarIds = [];
                            $unmatchedValues = [];
                            foreach ($pillarValues as $pv) {
                                $escapedPv = str_replace(['%', '_'], ['\\%', '\\_'], $pv);
                                $matchingPillars = \App\Models\Pillar::where(function($q) use ($escapedPv, $pv) {
                                    $q->where('subpillar', $pv)
                                      ->orWhere('subpillar', 'LIKE', $pv . "\n%")
                                      ->orWhere('subpillar', 'LIKE', "%\n" . $pv)
                                      ->orWhere('subpillar', 'LIKE', "%\n" . $pv . "\n%");
                                })->get();
                                if ($matchingPillars->isNotEmpty()) {
                                    foreach ($matchingPillars as $mp) {
                                        $foundPillarIds[] = $mp->id;
                                    }
                                } else {
                                    $unmatchedValues[] = $pv;
                                }
                            }
                            $foundPillarIds = array_unique($foundPillarIds);
                            if (!empty($unmatchedValues)) {
                                $errors[] = "Row (ID: {$oldProjectId}): pillar value(s) not found — " . implode(', ', $unmatchedValues);
                            }
                            if (!empty($foundPillarIds)) {
                                $project->pillars()->syncWithoutDetaching($foundPillarIds);
                            }
                        }

                        // Process tags (colleges)
                        if (!empty($tagsRaw)) {
                            $collegeCodes = array_map('trim', explode(',', $tagsRaw));
                            $foundColleges = \App\Models\College::whereIn('code', $collegeCodes)->pluck('code')->toArray();
                            $missingCollegeCodes = array_diff($collegeCodes, $foundColleges);
                            if (!empty($missingCollegeCodes)) {
                                $errors[] = "Row (ID: {$oldProjectId}): college code(s) not found — " . implode(', ', $missingCollegeCodes);
                            }
                            $collegeIds = \App\Models\College::whereIn('code', $foundColleges)->pluck('id')->toArray();
                            if (!empty($collegeIds)) {
                                $project->colleges()->syncWithoutDetaching($collegeIds);
                            }
                        }

                        // Process student grants: import students from Excel
                        if ($isStudentGrant && !empty($row[12])) {
                            $studentIds = array_map('trim', explode(',', $row[12]));
                            $nationality = trim($row[13] ?? '');
                            Log::info("Processing student grant: project {$oldProjectId}, students: " . implode(', ', $studentIds), ['nationality' => $nationality]);

                            foreach ($studentIds as $stdId) {
                                if (empty($stdId)) continue;

                                try {
                                    // Create or update project student
                                    $projectStudent = \App\Models\ProjectStudent::updateOrCreate(
                                        ['project_id' => $project->id, 'std_id' => $stdId],
                                        [
                                            'user_id' => $matchedUser->id ?? null,
                                            'type' => 'UG', // Default, will be updated from API
                                            'nationality' => $nationality,
                                            'days' => 0,
                                            'score' => 0,
                                        ]
                                    );

                                    // Fetch student info from SIS API and save details
                                    \App\Models\ProjectStudentDetail::saveFromApi($projectStudent->id, $stdId);
                                    Log::info("Successfully imported student {$stdId} for project {$oldProjectId}");
                                } catch (\Exception $e) {
                                    $errors[] = "Student import error (ID: {$stdId}): " . $e->getMessage();
                                    Log::error("Failed to import student {$stdId}: " . $e->getMessage());
                                }
                            }
                        }

                        $importCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Row error (ID: {$oldProjectId}): " . $e->getMessage();
                        Log::error("Excel import: Row error for {$oldProjectId}: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Excel processing error: " . $e->getMessage();
            } finally {
                Storage::delete($excelPath);
            }
        }

        // Process Proposals ZIP
        $proposalsMatched = 0;
        $proposalsSkipped = 0;
        $proposalsUnmatched = [];
        if ($request->hasFile('proposals_zip')) {
            $zipFile = $request->file('proposals_zip');

            // Build the designated folder path: {cycle_year}/{grant_code}/proposals/
            $cycleYear = $program->cycle ? $program->cycle->year : 'unknown';
            $grantCode = $program->grant ? $program->grant->grant_code : 'unknown';
            $extractPath = storage_path('app/uploads/' . $cycleYear . '/' . $grantCode . '/proposals/');

            // Stage PDFs in a temp dir first; only matched files are copied into
            // the proposals folder, so unmatched files are never stored.
            $stagingPath = storage_path('app/temp/proposals_' . uniqid());
            if (!is_dir($stagingPath)) {
                mkdir($stagingPath, 0755, true);
            }

            try {
                $zip = new \ZipArchive();
                if ($zip->open($zipFile->getRealPath()) === true) {
                    $zip->extractTo($stagingPath);
                    $zip->close();

                    // Build the match index once and reuse it for every extracted file.
                    $matchIndex = $this->buildProposalMatchIndex($program);

                    foreach ($this->stagedPdfFiles($stagingPath) as $staged) {
                        $file = basename($staged);

                        // Match the PDF to a project by its file-safe id and
                        // store it under the canonical id-only name.
                        $result = $this->matchProposalToProject($file, $program, $matchIndex, $staged, $extractPath);
                        if ($result === 'matched') {
                            $proposalsMatched++;
                        } elseif ($result === 'already') {
                            $proposalsSkipped++;
                        } else {
                            $proposalsUnmatched[] = $file;
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "ZIP extraction error: " . $e->getMessage();
            } finally {
                // Discard staged files (unmatched ones included).
                $this->cleanupDir($stagingPath);
            }
        }


        $message = "Program '{$program->program_title}' created successfully. {$importCount} projects imported.";
        if (!empty($errors)) {
            $message .= ' Some errors occurred.';
        }

        // Check which projects are missing proposal PDFs
        $projectsWithoutPdf = \App\Models\Project::where('program_id', $program->id)
            ->whereNull('proposal_filename')
            ->select('old_project_id', 'title')
            ->get();

        $totalInExcel = $importCount + count($errors);
        $missingPdfCount = $projectsWithoutPdf->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'program' => $program,
                'importCount' => $importCount,
                'totalInExcel' => $totalInExcel,
                'importErrors' => $errors,
                'projectsWithoutPdf' => $projectsWithoutPdf,
                'missingPdfCount' => $missingPdfCount,
                'proposalsMatched' => $proposalsMatched,
                'proposalsSkipped' => $proposalsSkipped,
                'proposalsUnmatched' => $proposalsUnmatched,
            ]);
        }

        return redirect()->route('programs.show', $program->id)
            ->with('success', $message);
    }

    public function show($id)
    {
        $program = Program::with(['grant', 'projects.lpi', 'projects' => function ($q) {
            $q->orderBy('title');
        }])->findOrFail($id);

        // Get latest status for each project
        $projectIds = $program->projects->pluck('id');
        $statusCounts = \DB::table('status_histories')
            ->select('status', \DB::raw('count(*) as count'))
            ->whereIn('project_id', $projectIds)
            ->whereRaw('id = (SELECT MAX(sh2.id) FROM status_histories sh2 WHERE sh2.project_id = status_histories.project_id)')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('programs.show', compact('program', 'statusCounts'));
    }

    public function index(Request $request)
    {
        $query = Program::with(['grant', 'cycleConfig'])
            ->whereHas('projects')
            ->withCount('projects');

        if ($request->filled('grant')) {
            $query->where('grant_id', $request->grant);
        }

        if ($request->filled('cycle')) {
            $query->where('cycle_id', $request->cycle);
        }

        if ($request->filled('visibility')) {
            $query->where('is_visible', $request->visibility === 'visible');
        }

        if ($request->filled('grant_type')) {
            $query->whereHas('grant', function ($q) use ($request) {
                $q->where('category', $request->grant_type);
            });
        }

        $programs = $query
            ->orderBy(
                \App\Models\CycleConfig::select('year')
                    ->whereColumn('cycle_configs.id', 'programs.cycle_id')
                    ->limit(1),
                'desc'
            )
            ->orderBy(
                \App\Models\Grant::select('grant_code')
                    ->whereColumn('grants.id', 'programs.grant_id')
                    ->limit(1),
                'asc'
            )
            ->get();

        // passed to view so the filter form retains the selected value
        $statusFilter = $request->input('status', '');

        // Full lists (used by the create/edit modal).
        $grants = Grant::where('is_active', true)->orderBy('grant_code')->get();
        $cycleConfigs = CycleConfig::orderBy('title')->get();

        // ── Cascading filter options ─────────────────────────────────────
        // Cycle → Grant Type → Grant. Each level narrows the next.
        $selectedCycle = $request->filled('cycle') ? (int) $request->cycle : null;
        $selectedGrantType = $request->filled('grant_type') ? $request->grant_type : null;

        // Grant types available within the selected cycle (regular before student).
        $grantTypeQuery = Grant::query()->whereNotNull('category');
        if ($selectedCycle) {
            $grantTypeQuery->whereHas('programs', function ($q) use ($selectedCycle) {
                $q->where('cycle_id', $selectedCycle);
            });
        }
        $typePriority = ['regular' => 0, 'student' => 1];
        $filterGrantTypes = $grantTypeQuery->distinct()->pluck('category')
            ->sortBy(fn ($t) => $typePriority[$t] ?? 99)
            ->values();

        // Grants available within the selected cycle + grant type.
        $filterGrantQuery = Grant::where('is_active', true);
        if ($selectedCycle) {
            $filterGrantQuery->whereHas('programs', function ($q) use ($selectedCycle) {
                $q->where('cycle_id', $selectedCycle);
            });
        }
        if ($selectedGrantType) {
            $filterGrantQuery->where('category', $selectedGrantType);
        }
        $filterGrants = $filterGrantQuery->orderBy('grant_code')->get();

        return view('programs.index', compact(
            'programs', 'grants', 'cycleConfigs', 'statusFilter',
            'filterGrantTypes', 'filterGrants'
        ));
    }

    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $validated = $request->validate([
            'grant_id' => 'nullable|exists:grants,id',
            'cycle_id' => 'nullable|exists:cycle_configs,id',
            'program_title' => 'nullable|string|max:255|unique:programs,program_title,' . $program->id,
            'prog_rpt_deadline' => 'nullable|date',
            'prog_rpt2_deadline' => 'nullable|date',
            'final_rpt_deadline' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        // Update all validated fields — null values are intentional here:
        // clearing a deadline input must remove it (null = window handled by
        // the lock logic), unlike before when the filter silently preserved
        // stale deadlines forever.
        //
        // Exception: program_title is a NOT NULL column, so never let an empty
        // submission blank it out.
        if (array_key_exists('program_title', $validated) && trim((string) $validated['program_title']) === '') {
            unset($validated['program_title']);
        }

        $program->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Program updated successfully.', 'program' => $program]);
        }

        return redirect()->route('programs.show', $program->id)
            ->with('success', 'Program updated successfully.');
    }

    public function toggle($id)
    {
        $program = Program::findOrFail($id);

        $program->update([
            'is_visible' => !$program->is_visible,
        ]);

        $status = $program->is_visible ? 'shown' : 'hidden';
        $message = "Research call '{$program->program_title}' {$status} successfully.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_visible' => $program->is_visible,
            ]);
        }

        return redirect()->route('programs.index')
            ->with('success', $message);
    }

    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        $projectCount = $program->projects()->count();

        // Build the storage path for proposals
        $cycleYear = $program->cycle ? $program->cycle->year : 'unknown';
        $grantCode = $program->grant ? $program->grant->grant_code : 'unknown';
        $proposalsDir = storage_path('app/uploads/' . $cycleYear . '/' . $grantCode . '/proposals');

        // Collect every file that must be removed AFTER the DB transaction
        // commits (proposals + all submitted version files). Unlinking inside
        // the transaction would leave the disk inconsistent if it failed.
        $filesToDelete = [];
        foreach ($program->projects as $project) {
            if ($proposalsDir && is_dir($proposalsDir)) {
                // Prefer the stored name, and also cover the canonical file-safe
                // name plus legacy variants in case the column is empty/stale.
                $oldId = $project->getFileSafeOldProjectId();
                $candidates = array_filter([
                    $project->proposal_filename ? $proposalsDir . '/' . $project->proposal_filename : null,
                    $oldId ? $proposalsDir . '/' . $oldId . '.pdf' : null,
                    $oldId ? $proposalsDir . '/' . $oldId . '_proposal.pdf' : null,
                    $oldId ? $proposalsDir . '/' . $oldId . '_Application.pdf' : null,
                ]);

                foreach (array_unique($candidates) as $proposalPath) {
                    if (file_exists($proposalPath)) {
                        $filesToDelete[] = $proposalPath;
                    }
                }
            }
            foreach ($project->submissions as $submission) {
                $fullPath = storage_path('app/' . $submission->file_path);
                if (file_exists($fullPath)) {
                    $filesToDelete[] = $fullPath;
                }
            }
        }

        // Delete associated projects and their related data — wrapped in a
        // transaction so a mid-loop failure cannot leave half-deleted state.
        DB::transaction(function () use ($program) {
            foreach ($program->projects as $project) {
                // Delete related records
                $project->outcomes()->delete();
                $project->students()->delete();
                $project->researchers()->delete();
                $project->contributions()->delete();
                $project->commitments()->delete();
                $project->submissions()->delete();
                $project->pillars()->detach();
                $project->colleges()->detach();
                $project->statusHistories()->delete();

                // Delete reviewer assignments
                DB::table('projects_reviewers')->where('project_id', $project->id)->delete();

                // Delete grading records
                DB::table('progress_report_grading')->where('project_id', $project->id)->delete();
                DB::table('final_report_grading')->where('project_id', $project->id)->delete();

                // Delete the project
                $project->delete();
            }

            // Delete the program
            $program->delete();
        });

        foreach ($filesToDelete as $path) {
            @unlink($path);
        }

        // Delete the proposals directory if it exists and is empty
        if ($proposalsDir && is_dir($proposalsDir) && $this->isDirectoryEmpty($proposalsDir)) {
            rmdir($proposalsDir);
        }

        $message = "Research call deleted successfully. {$projectCount} project(s) were also removed.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('programs.index')
            ->with('success', $message);
    }

    /**
     * Fetch student information from SIS API.
     */
    private function fetchStudentFromSIS(string $studentId): ?array
    {
        try {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 10,
            ]);

            $response = $client->request('GET', 'http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std', [
                'headers' => [
                    'sec_key' => 'STD@R',
                    'st_id' => $studentId,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['items']) && is_array($body['items']) && count($body['items']) > 0) {
                $item = end($body['items']);
                return $item;
            }
        } catch (\Exception $e) {
            \Log::warning("SIS API failed for student {$studentId}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Upload proposal PDFs (from ZIP/RAR archive or individual files).
     */
    public function uploadProposals(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'archive' => 'required|file|mimes:zip,rar|max:51200',
        ]);

        $program = Program::findOrFail($request->program_id);
        $cycleYear = $program->cycle ? $program->cycle->year : 'unknown';
        $grantCode = $program->grant ? $program->grant->grant_code : 'unknown';
        $extractPath = storage_path('app/uploads/' . $cycleYear . '/' . $grantCode . '/proposals/');

        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        $file = $request->file('archive');
        $extension = strtolower($file->getClientOriginalExtension());

        $matched = 0;
        $unmatched = [];
        $skippedExisting = 0;

        // Build once; reused for every extracted PDF.
        $matchIndex = $this->buildProposalMatchIndex($program);

        // Staging directory: PDFs are extracted here first and only moved into
        // the proposals folder once they match a project. Unmatched files are
        // left in the temp dir and deleted with it, so they are never stored.
        $stagingPath = storage_path('app/temp/proposals_' . uniqid());
        if (!is_dir($stagingPath)) {
            mkdir($stagingPath, 0755, true);
        }

        if ($extension === 'zip') {
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) === true) {
                // First pass: extract all PDF files only into staging
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if (pathinfo($entryName, PATHINFO_EXTENSION) === 'pdf' && !str_starts_with($entryName, '__MACOSX')) {
                        $zip->extractTo($stagingPath, $entryName);
                        Log::info("ZIP extracted to staging: {$entryName}");
                    }
                }
                $zip->close();

                // Second pass: match files to projects; matched ones get copied
                // into the proposals folder under the canonical id name.
                foreach ($this->stagedPdfFiles($stagingPath) as $staged) {
                    $f = basename($staged);

                    $result = $this->matchProposalToProject($f, $program, $matchIndex, $staged, $extractPath);
                    if ($result === 'matched') {
                        $matched++;
                    } elseif ($result === 'already') {
                        $skippedExisting++;
                    } else {
                        $unmatched[] = $f;
                    }
                }
            } else {
                $this->cleanupDir($stagingPath);
                return response()->json(['error' => 'Failed to open ZIP file.'], 422);
            }
        } elseif ($extension === 'rar') {
            // For RAR, we need to use a different approach
            // Extract to temp directory first
            $tempPath = storage_path('app/temp/rar_' . time());
            mkdir($tempPath, 0755, true);

            $file->move($tempPath, 'archive.rar');

            // Try using unrar command if available
            $rarFile = $tempPath . '/archive.rar';
            $output = [];
            $returnCode = 0;
            exec("unar -o {$stagingPath} {$rarFile} 2>&1", $output, $returnCode);

            if ($returnCode === 0) {
                // Match files to projects; matched ones get copied in.
                foreach ($this->stagedPdfFiles($stagingPath) as $staged) {
                    $f = basename($staged);

                    $result = $this->matchProposalToProject($f, $program, $matchIndex, $staged, $extractPath);
                    if ($result === 'matched') {
                        $matched++;
                    } elseif ($result === 'already') {
                        $skippedExisting++;
                    } else {
                        $unmatched[] = $f;
                    }
                }
            } else {
                // Cleanup temp
                array_map('unlink', glob("{$tempPath}/*"));
                rmdir($tempPath);
                $this->cleanupDir($stagingPath);
                return response()->json(['error' => 'Failed to extract RAR file. Make sure unrar is installed.'], 422);
            }

            // Cleanup temp
            array_map('unlink', glob("{$tempPath}/*"));
            rmdir($tempPath);
        }

        // Discard everything left in staging (unmatched / non-matched files).
        $this->cleanupDir($stagingPath);

        $totalMatched = $matched + $skippedExisting;

        $message = "{$totalMatched} proposal(s) matched.";
        if ($matched > 0) {
            $message .= " {$matched} newly linked";
        }
        if ($skippedExisting > 0) {
            $message .= ($matched > 0 ? ', ' : ' ') . "{$skippedExisting} already linked (skipped)";
        }
        if ($totalMatched > 0) {
            $message .= '.';
        }

        return response()->json([
            'success' => true,
            'matched' => $totalMatched,
            'newlyMatched' => $matched,
            'skippedExisting' => $skippedExisting,
            'unmatched' => $unmatched,
            'message' => $message,
        ]);
    }

    /**
     * Check if a directory is empty.
     */
    private function isDirectoryEmpty(string $path): bool
    {
        $files = scandir($path);
        return $files === false || count($files) <= 2; // '.' and '..'
    }

    /**
     * Build a lookup of normalized project-id keys => projects for a program.
     * Multiple keys are indexed per project so filenames that had to replace
     * or drop filesystem-illegal characters (notably "/") still resolve.
     *
     * @return array<string, \App\Models\Project[]>
     */
    private function buildProposalMatchIndex(Program $program): array
    {
        $index = [];

        $projects = \App\Models\Project::where('program_id', $program->id)->get();

        foreach ($projects as $project) {
            foreach ($this->proposalMatchKeys((string) $project->old_project_id) as $key) {
                $index[$key][] = $project;
            }
        }

        return $index;
    }

    /**
     * Normalized comparison keys for a project id / proposal filename.
     * Handles ids containing "/" by trying the slash replaced with "-",
     * removed, and an alphanumeric-only compact form.
     *
     * @return string[]
     */
    private function proposalMatchKeys(string $value): array
    {
        $value = trim($value);

        $keys = [
            strtolower($value),
            strtolower(str_replace(['/', '\\'], '-', $value)),
            strtolower(str_replace(['/', '\\'], '', $value)),
            strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $value)),
        ];

        return array_values(array_unique(array_filter($keys, fn ($k) => $k !== '')));
    }

    /**
     * Match a proposal filename to a project and update if no existing proposal.
     * Returns 'matched' (newly linked), 'already' (matched but a proposal was
     * already linked), or 'unmatched'.
     *
     * Accepted filename conventions (all matched by the file-safe project id):
     *   <old_id>.pdf | <old_id>_Application.pdf | <old_id>_proposal.pdf
     * Whatever the incoming name, the file is stored under the canonical
     * file-safe project id (e.g. "QUIKT-CENG-2627-1014.pdf") and
     * proposal_filename is set to that standardized name.
     *
     * @param  array<string, \App\Models\Project[]>  $matchIndex
     * @param  string|null  $sourcePath  Staged PDF to place when a project matches.
     * @param  string|null  $destDir     Proposals folder to place it in.
     */
    private function matchProposalToProject(string $filename, Program $program, array $matchIndex, ?string $sourcePath = null, ?string $destDir = null): string
    {
        $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

        // Strip optional "Application" / "proposal" (+ " - Copy") suffixes.
        $candidateId = preg_replace('/\s*[-_]?\s*(Application|proposal)(\s*-\s*Copy)?$/i', '', trim($filenameWithoutExt));

        $candidates = $candidateId !== $filenameWithoutExt
            ? [$candidateId, $filenameWithoutExt]
            : [$filenameWithoutExt];

        $tried = [];
        $alreadyLinked = false;

        foreach ($candidates as $candidate) {
            foreach ($this->proposalMatchKeys($candidate) as $key) {
                if (isset($tried[$key])) {
                    continue;
                }
                $tried[$key] = true;

                if (empty($matchIndex[$key])) {
                    continue;
                }

                foreach ($matchIndex[$key] as $project) {
                    // Always store/match under the canonical file-safe id.
                    $storedName = $project->getFileSafeOldProjectId() . '.pdf';
                    $current = $project->proposal_filename;

                    if (empty($current) || $current !== $storedName) {
                        // Only write the file to disk when it actually matched.
                        // If the copy fails we must NOT point the DB at a file
                        // that does not exist (that would make it "invisible" to
                        // the file explorer / download paths).
                        if ($sourcePath !== null && $destDir !== null) {
                            if (!$this->placeProposalFile($sourcePath, $destDir, $storedName)) {
                                continue;
                            }
                        }

                        Log::info("PDF match: '{$filename}' MATCHED project (ID: {$project->id}, old: {$project->old_project_id}) -> stored as '{$storedName}'");
                        $project->update(['proposal_filename' => $storedName]);

                        return 'matched';
                    }

                    // Project already points at this exact canonical file. If the
                    // file is missing on disk, restore it from the staged copy.
                    if ($sourcePath !== null && $destDir !== null) {
                        $destPath = $destDir . '/' . $storedName;
                        if (!file_exists($destPath)) {
                            if (!$this->placeProposalFile($sourcePath, $destDir, $storedName)) {
                                continue;
                            }
                            Log::info("PDF match: '{$filename}' restored missing file for project (ID: {$project->id}) as '{$storedName}'");
                            return 'matched';
                        }
                    }
                    $alreadyLinked = true;
                }
            }
        }

        if ($alreadyLinked) {
            Log::info("PDF match: '{$filename}' already linked to a project (canonical name)");
            return 'already';
        }

        Log::info("PDF match: '{$filename}' UNMATCHED - no project matched candidate '{$candidateId}'");
        return 'unmatched';
    }

    /**
     * List PDF files (recursively) inside a staging directory.
     *
     * @return string[] Absolute paths
     */
    private function stagedPdfFiles(string $dir): array
    {
        $result = [];
        $items = scandir($dir);
        if ($items === false) {
            return $result;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $result = array_merge($result, $this->stagedPdfFiles($path));
            } elseif (strtolower(pathinfo($item, PATHINFO_EXTENSION)) === 'pdf') {
                $result[] = $path;
            }
        }

        return $result;
    }

    /**
     * Copy a staged proposal into the proposals folder under its canonical name,
     * replacing any previous file of the same name. Returns true only when the
     * file was actually written, so callers never mark the DB as having a file
     * that is not on disk.
     */
    private function placeProposalFile(string $sourcePath, string $destDir, string $storedName): bool
    {
        if (!is_dir($destDir)) {
            if (!@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                Log::error("Proposal store: could not create directory '{$destDir}'");
                return false;
            }
        }

        $destPath = $destDir . '/' . $storedName;
        if (file_exists($destPath)) {
            @unlink($destPath);
        }

        if (!@copy($sourcePath, $destPath)) {
            Log::error("Proposal store: failed to copy '{$sourcePath}' -> '{$destPath}'");
            return false;
        }

        return true;
    }

    /**
     * Recursively delete a directory and its contents.
     */
    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items !== false) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $path = $dir . '/' . $item;
                if (is_dir($path)) {
                    $this->cleanupDir($path);
                } else {
                    @unlink($path);
                }
            }
        }

        @rmdir($dir);
    }

    /**
     * Upload a single proposal PDF for a project.
     */
    public function uploadSingleProposal(Request $request, $id)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        $project = \App\Models\Project::findOrFail($id);
        $program = $project->program;

        if (!$program) {
            return response()->json(['error' => 'Project has no associated program.'], 422);
        }

        $cycleYear = $program->cycle ? $program->cycle->year : 'unknown';
        $grantCode = $program->grant ? $program->grant->grant_code : 'unknown';
        $relativePath = 'uploads/' . $cycleYear . '/' . $grantCode . '/proposals';
        $dir = storage_path('app/' . $relativePath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $request->file('pdf');

        // Use the deterministic storage name (filesystem-safe: "/" stripped) so
        // the served path matches getStorageFilename()/serveProposal().
        $filename = basename($project->getStorageFilename('proposal'));

        // Delete old proposal if exists
        if ($project->proposal_filename) {
            $oldPath = $dir . '/' . $project->proposal_filename;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $file->storeAs($relativePath, $filename);
        $project->update(['proposal_filename' => $filename]);

        return response()->json([
            'success' => true,
            'message' => 'Proposal uploaded successfully.',
            'filename' => $filename,
        ]);
    }
}
