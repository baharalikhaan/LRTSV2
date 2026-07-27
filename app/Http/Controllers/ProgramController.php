<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Grant;
use App\Models\CycleConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'extended_prog_rpt_deadline' => 'nullable|date',
            'prog_rpt2_deadline' => 'nullable|date',
            'extended_prog_rpt2_deadline' => 'nullable|date',
            'final_rpt_deadline' => 'nullable|date',
            'extended_final_rpt_deadline' => 'nullable|date',
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
            'extended_prog_rpt_deadline' => $validated['extended_prog_rpt_deadline'] ?? null,
            'prog_rpt2_deadline' => $validated['prog_rpt2_deadline'] ?? null,
            'extended_prog_rpt2_deadline' => $validated['extended_prog_rpt2_deadline'] ?? null,
            'final_rpt_deadline' => $validated['final_rpt_deadline'] ?? null,
            'extended_final_rpt_deadline' => $validated['extended_final_rpt_deadline'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $importCount = 0;
        $errors = [];

        // Process Excel file
        if ($request->hasFile('excel')) {
            $excelPath = $request->file('excel')->store('temp/excel_imports');

            try {
                $spreadsheet = IOFactory::load(storage_path('app/' . $excelPath));
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray();

                // Skip header row if present
                $startRow = 0;
                if (isset($data[0][0]) && !is_numeric($data[0][0])) {
                    $startRow = 1;
                }

                foreach (array_slice($data, $startRow) as $row) {
                    if (empty($row[3]) && empty($row[1])) {
                        continue;
                    }

                    $oldProjectId = trim($row[1] ?? '');
                    $title = trim($row[3] ?? '');
                    $email = trim($row[5] ?? '');
                    $author = trim($row[4] ?? '');
                    $pillarsRaw = trim($row[9] ?? '');
                    $tagsRaw = trim($row[10] ?? '');

                    if (empty($oldProjectId) || empty($title)) {
                        continue;
                    }

                    try {
                        $project = \App\Models\Project::updateOrCreate(
                            ['old_project_id' => $oldProjectId],
                            [
                                'program_id' => $program->id,
                                'title' => $title,
                                'email' => $email,
                                'author' => $author,
                            ]
                        );

                        // Look up user by matching the email from the Excel against the users table.
                        // If no user exists, create one with name=email, email=email, type=LPI, is_active=true.
                        // If user exists, ensure they are active (do not overwrite existing name/type).
                        if (!empty($email)) {

                            $matchedUser = \App\Models\User::where('email', $email)->first();
                            if ($matchedUser) {
                                // Ensure the existing user is active; do not overwrite name/type
                                if (!$matchedUser->is_active) {
                                    $matchedUser->update(['is_active' => true]);
                                }
                            } else {
                                // Create a new user with the author name (fallback to email), type LPI, active
                                $matchedUser = \App\Models\User::create([
                                    'name' => $author ?: $email,
                                    'email' => $email,
                                    'type' => 'LPI',
                                    'is_active' => true,
                                    'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                                ]);
                            }
                            // Link the project to its LPI. The projects table uses `lpi_id`
                            // (it no longer has a `user_id` column).
                            $project->update([
                                'lpi_id' => $matchedUser->id,
                            ]);
                        }

                        // Record unregistered status in status_histories for each imported project
                        if (!$project->hasStatus('unregistered')) {
                            $project->recordStatus('unregistered', ['imported' => true], auth()->id());
                        }

                        // Process pillars: match each Excel pillar value against pillars.subpillar
                        // (which contains newline-separated subpillar names) using LIKE with line-break boundaries.
                        // The Excel column values are separated by newlines (enter key), not commas.
                        if (!empty($pillarsRaw)) {
                            $pillarValues = array_map('trim', preg_split('/[\r\n]+/', $pillarsRaw));
                            $pillarValues = array_filter($pillarValues, function($v) { return $v !== ''; });
                            $foundPillarIds = [];
                            $unmatchedValues = [];
                            foreach ($pillarValues as $pv) {
                                $escapedPv = str_replace(['%', '_'], ['\\%', '\\_'], $pv);
                                $matchingPillars = \App\Models\Pillar::where(function($q) use ($escapedPv, $pv) {
                                    $q->where('subpillar', $pv)                              // exact match (single value)
                                      ->orWhere('subpillar', 'LIKE', $pv . "\n%")             // starts with value
                                      ->orWhere('subpillar', 'LIKE', "%\n" . $pv)             // ends with value
                                      ->orWhere('subpillar', 'LIKE', "%\n" . $pv . "\n%");   // value in the middle
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
                                $errors[] = "Row (ID: {$oldProjectId}): pillar value(s) not found in subpillar column — " . implode(', ', $unmatchedValues);
                            }
                            if (!empty($foundPillarIds)) {
                                $project->pillars()->syncWithoutDetaching($foundPillarIds);
                            }
                        }

                        // Process tags (colleges): match against colleges.code and sync pivot
                        if (!empty($tagsRaw)) {
                            $collegeCodes = array_map('trim', explode(',', $tagsRaw));
                            $foundColleges = \App\Models\College::whereIn('code', $collegeCodes)->pluck('code')->toArray();
                            $missingCollegeCodes = array_diff($collegeCodes, $foundColleges);
                            if (!empty($missingCollegeCodes)) {
                                $errors[] = "Row (ID: {$oldProjectId}): college code(s) not found in lookup table — " . implode(', ', $missingCollegeCodes);
                            }
                            $collegeIds = \App\Models\College::whereIn('code', $foundColleges)->pluck('id')->toArray();
                            if (!empty($collegeIds)) {
                                $project->colleges()->syncWithoutDetaching($collegeIds);
                            }
                        }

                        $importCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Row error (ID: {$oldProjectId}): " . $e->getMessage();
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
                        \App\Models\Project::where('old_project_id', $filenameWithoutExt)
                            ->where('program_id', $program->id)
                            ->update(['proposal_filename' => $file]);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "ZIP extraction error: " . $e->getMessage();
            }
        }

        $message = "Program '{$program->program_title}' created successfully. {$importCount} projects imported.";
        if (!empty($errors)) {
            $message .= ' Some errors occurred: ' . implode(' | ', $errors);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'program' => $program,
                'importCount' => $importCount,
                'importErrors' => $errors,
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
            'grant_id' => 'exists:grants,id',
            'cycle_id' => 'nullable|exists:cycle_configs,id',
            'program_title' => 'required|string|max:255|unique:programs,program_title,' . $program->id,
            'prog_rpt_deadline' => 'nullable|date',
            'extended_prog_rpt_deadline' => 'nullable|date|after_or_equal:prog_rpt_deadline',
            'prog_rpt2_deadline' => 'nullable|date',
            'extended_prog_rpt2_deadline' => 'nullable|date|after_or_equal:prog_rpt2_deadline',
            'final_rpt_deadline' => 'nullable|date',
            'extended_final_rpt_deadline' => 'nullable|date|after_or_equal:final_rpt_deadline',
            'description' => 'nullable|string',
        ]);

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
        $program->delete();

        return redirect()->route('programs.index')
            ->with('success', 'Program deleted successfully.');
    }
}
