<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Grant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CycleController extends Controller
{
    public function create()
    {
        $grants = Grant::where('is_active', true)->orderBy('grant_code')->get();
        return view('cycles.create', compact('grants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grant_id' => 'required|exists:grants,id',
            'program_title' => 'required|string|max:255|unique:programs,program_title',
            'prog_rpt_deadline' => 'nullable|date',
            'extended_prog_rpt_deadline' => 'nullable|date|after_or_equal:prog_rpt_deadline',
            'final_rpt_deadline' => 'nullable|date',
            'extended_final_rpt_deadline' => 'nullable|date|after_or_equal:final_rpt_deadline',
            'description' => 'nullable|string',
            'excel' => 'required|file|mimes:xlsx,xls,csv',
            'proposals_zip' => 'nullable|file|mimes:zip',
        ]);

        // Create the cycle
        $cycle = Cycle::create([
            'grant_id' => $validated['grant_id'],
            'program_title' => $validated['program_title'],
            'prog_rpt_deadline' => $validated['prog_rpt_deadline'] ?? null,
            'extended_prog_rpt_deadline' => $validated['extended_prog_rpt_deadline'] ?? null,
            'final_rpt_deadline' => $validated['final_rpt_deadline'] ?? null,
            'extended_final_rpt_deadline' => $validated['extended_final_rpt_deadline'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $importCount = 0;
        $errors = [];

        // Process Excel file
        if ($request->hasFile('excel')) {
            $excelPath = $request->file('excel')->store('uploads/cycles/' . $cycle->id);

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

                    if (empty($oldProjectId) || empty($title)) {
                        continue;
                    }

                    try {
                        $project = \App\Models\Project::updateOrCreate(
                            ['old_project_id' => $oldProjectId],
                            [
                                'program_id' => $cycle->id,
                                'title' => $title,
                                'email' => $email,
                                'author' => $author,
                            ]
                        );

                        if (!$project->hasStatus('unregistered')) {
                            $project->recordStatus('unregistered', ['imported' => true], auth()->id());
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
            $extractPath = storage_path('app/uploads/proposals/' . $cycle->id . '/');

            try {
                $zip = new \ZipArchive();
                if ($zip->open($zipFile->getRealPath()) === true) {
                    $zip->extractTo($extractPath);
                    $zip->close();

                    $files = scandir($extractPath);
                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..') continue;
                        $filenameWithoutExt = pathinfo($file, PATHINFO_FILENAME);
                        \App\Models\Project::where('old_project_id', $filenameWithoutExt)
                            ->where('program_id', $cycle->id)
                            ->update(['proposal_filename' => $file]);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "ZIP extraction error: " . $e->getMessage();
            }
        }

        $message = "Program '{$cycle->program_title}' created successfully. {$importCount} projects imported.";
        if (!empty($errors)) {
            $message .= ' Some errors occurred: ' . implode(' | ', $errors);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'cycle' => $cycle,
                'importCount' => $importCount,
                'importErrors' => $errors,
            ]);
        }

        return redirect()->route('cycles.show', $cycle->id)
            ->with('success', $message);
    }

    public function show($id)
    {
        $cycle = Cycle::with(['grant', 'projects.reviewers'])->findOrFail($id);

        $cycle->setRelation('projects', $cycle->projects->sortBy('title'));

        return view('cycles.show', compact('cycle'));
    }

    public function index()
    {
        $cycles = Cycle::with('grant')->orderBy('created_at', 'desc')->get();
        $grants = Grant::where('is_active', true)->orderBy('grant_code')->get();
        $filterCycles = Cycle::orderBy('program_title')->get(['id', 'program_title']);
        return view('cycles.index', compact('cycles', 'grants', 'filterCycles'));
    }

    public function edit($id)
    {
        $cycle = Cycle::findOrFail($id);
        $grants = Grant::where('is_active', true)->orderBy('grant_code')->get();
        return view('cycles.edit', compact('cycle', 'grants'));
    }

    public function update(Request $request, $id)
    {
        $cycle = Cycle::findOrFail($id);

        $validated = $request->validate([
            'program_title' => 'required|string|max:255|unique:programs,program_title,' . $cycle->id,
            'prog_rpt_deadline' => 'nullable|date',
            'extended_prog_rpt_deadline' => 'nullable|date|after_or_equal:prog_rpt_deadline',
            'final_rpt_deadline' => 'nullable|date',
            'extended_final_rpt_deadline' => 'nullable|date|after_or_equal:final_rpt_deadline',
            'description' => 'nullable|string',
        ]);

        $cycle->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Cycle updated successfully.', 'cycle' => $cycle]);
        }

        return redirect()->route('cycles.show', $cycle->id)
            ->with('success', 'Cycle updated successfully.');
    }

    public function destroy($id)
    {
        $cycle = Cycle::findOrFail($id);
        $cycle->delete();

        return redirect()->route('cycles.index')
            ->with('success', 'Cycle deleted successfully.');
    }
}
