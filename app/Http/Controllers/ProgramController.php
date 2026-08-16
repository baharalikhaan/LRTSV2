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
    public function create()
    {
        $grants = Grant::where('is_active', true)->orderBy('grant_code')->get();
        $cycleConfigs = CycleConfig::orderBy('title')->get();
        return view('programs.create', compact('grants', 'cycleConfigs'));
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
        if ($request->hasFile('proposals_zip')) {
            $zipFile = $request->file('proposals_zip');

            // Build the designated folder path: {cycle_year}/{grant_code}/proposals/
            $cycleYear = $program->cycle ? $program->cycle->year : 'unknown';
            $grantCode = $program->grant ? $program->grant->grant_code : 'unknown';
            $extractPath = storage_path('app/uploads/' . $cycleYear . '/' . $grantCode . '/proposals/');

            try {
                $zip = new \ZipArchive();
                if ($zip->open($zipFile->getRealPath()) === true) {
                    // Ensure the directory exists
                    if (!is_dir($extractPath)) {
                        mkdir($extractPath, 0755, true);
                    }
                    $zip->extractTo($extractPath);
                    $zip->close();

                    $files = scandir($extractPath);
                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..') continue;
                        $filenameWithoutExt = pathinfo($file, PATHINFO_FILENAME);

                        // Proposal files use the conf-tool naming <old_id>_Application.pdf,
                        // so strip the "_Application" suffix to match the project's old_project_id.
                        $candidateId = $filenameWithoutExt;
                        $candidateId = preg_replace('/_Application$/i', '', $candidateId);

                        $updated = \App\Models\Project::where('old_project_id', $candidateId)
                            ->where('program_id', $program->id)
                            ->update(['proposal_filename' => $file]);

                        // Fallback: try matching the full filename without extension too
                        if ($updated === 0 && $candidateId !== $filenameWithoutExt) {
                            \App\Models\Project::where('old_project_id', $filenameWithoutExt)
                                ->where('program_id', $program->id)
                                ->update(['proposal_filename' => $file]);
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "ZIP extraction error: " . $e->getMessage();
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

        return view('programs.show', compact('program'));
    }

    public function index(Request $request)
    {
        $query = Program::with(['grant', 'cycleConfig']);

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

        $programs = $query->orderBy('created_at', 'desc')->get();

        // passed to view so the filter form retains the selected value
        $statusFilter = $request->input('status', '');

        $grants = Grant::where('is_active', true)->orderBy('grant_code')->get();
        $cycleConfigs = CycleConfig::orderBy('title')->get();
        return view('programs.index', compact('programs', 'grants', 'cycleConfigs', 'statusFilter'));
    }

    public function edit($id)
    {
        $program = Program::findOrFail($id);
        $grants = Grant::where('is_active', true)->orderBy('grant_code')->get();
        $cycleConfigs = CycleConfig::orderBy('title')->get();
        return view('programs.edit', compact('program', 'grants', 'cycleConfigs'));
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

        // Only update fields that are present in the request
        $updateData = array_filter($validated, function($value) {
            return !is_null($value);
        });

        $program->update($updateData);

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

        // Delete associated projects and their related data
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
            
            // Delete proposal file from storage
            if ($project->proposal_filename && file_exists($proposalsDir . '/' . $project->proposal_filename)) {
                @unlink($proposalsDir . '/' . $project->proposal_filename);
            }

            // Delete the project
            $project->delete();
        }

        // Delete the proposals directory if it exists and is empty
        if (is_dir($proposalsDir) && $this->isDirectoryEmpty($proposalsDir)) {
            rmdir($proposalsDir);
        }

        // Delete the program
        $program->delete();

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

        if ($extension === 'zip') {
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) === true) {
                // First pass: extract all PDF files only
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if (pathinfo($entryName, PATHINFO_EXTENSION) === 'pdf' && !str_starts_with($entryName, '__MACOSX')) {
                        $zip->extractTo($extractPath, $entryName);
                        Log::info("ZIP extracted: {$entryName}");
                    }
                }
                $zip->close();

                // Second pass: match files to projects
                $files = scandir($extractPath);
                foreach ($files as $f) {
                    if ($f === '.' || $f === '..' || pathinfo($f, PATHINFO_EXTENSION) !== 'pdf') continue;

                    $result = $this->matchProposalToProject($f, $program, $extractPath);
                    if ($result === 'matched') {
                        $matched++;
                    } elseif ($result === 'exists') {
                        $skippedExisting++;
                    } else {
                        $unmatched[] = $f;
                    }
                }
            } else {
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
            exec("unar -o {$extractPath} {$rarFile} 2>&1", $output, $returnCode);

            if ($returnCode === 0) {
                // Match files to projects
                $files = scandir($extractPath);
                foreach ($files as $f) {
                    if ($f === '.' || $f === '..' || pathinfo($f, PATHINFO_EXTENSION) !== 'pdf') continue;

                    $result = $this->matchProposalToProject($f, $program, $extractPath);
                    if ($result === 'matched') {
                        $matched++;
                    } elseif ($result === 'exists') {
                        $skippedExisting++;
                    } else {
                        $unmatched[] = $f;
                    }
                }
            } else {
                // Cleanup temp
                array_map('unlink', glob("{$tempPath}/*"));
                rmdir($tempPath);
                return response()->json(['error' => 'Failed to extract RAR file. Make sure unrar is installed.'], 422);
            }

            // Cleanup temp
            array_map('unlink', glob("{$tempPath}/*"));
            rmdir($tempPath);
        }

        $message = "{$matched} proposal(s) uploaded successfully.";
        if ($skippedExisting > 0) {
            $message .= " {$skippedExisting} already had proposals (skipped).";
        }

        return response()->json([
            'success' => true,
            'matched' => $matched,
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
     * Match a proposal filename to a project and update if no existing proposal.
     * Returns 'matched', 'exists', or 'unmatched'.
     */
    private function matchProposalToProject(string $filename, Program $program, string $extractPath): string
    {
        $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

        // Try matching: {old_project_id}_Application.pdf or {old_project_id}.pdf
        $candidateId = preg_replace('/_Application$/i', '', $filenameWithoutExt);

        // Get all project IDs for this program to check against
        $projectIds = \App\Models\Project::where('program_id', $program->id)
            ->pluck('old_project_id')
            ->toArray();

        Log::info("PDF match: Checking '{$filename}' (candidate: '{$candidateId}') against " . count($projectIds) . " projects");

        $project = \App\Models\Project::where('old_project_id', $candidateId)
            ->where('program_id', $program->id)
            ->first();

        if (!$project && $candidateId !== $filenameWithoutExt) {
            $project = \App\Models\Project::where('old_project_id', $filenameWithoutExt)
                ->where('program_id', $program->id)
                ->first();
        }

        if ($project) {
            // Skip if project already has a proposal
            if (!empty($project->proposal_filename)) {
                Log::info("PDF match: '{$filename}' matched project '{$candidateId}' but already has proposal");
                return 'exists';
            }
            Log::info("PDF match: '{$filename}' MATCHED project '{$candidateId}' (ID: {$project->id})");
            $project->update(['proposal_filename' => $filename]);
            return 'matched';
        }

        Log::info("PDF match: '{$filename}' UNMATCHED - no project found with ID '{$candidateId}'");
        return 'unmatched';
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
        $filename = $project->old_project_id . '_Application.pdf';

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
