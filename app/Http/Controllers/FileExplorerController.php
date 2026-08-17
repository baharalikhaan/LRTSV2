<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Project;
use App\Models\Program;
use ZipArchive;

class FileExplorerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private const TYPES = [
        'proposal'   => 'proposals',
        'progress'   => 'progress_reports',
        'progress2'  => 'progress_reports',
        'final'      => 'final_reports',
        'readiness'  => 'readiness_reports',
    ];

    /**
     * Main page — project search + research call browser.
     */
    public function index()
    {
        $programs = Program::with('cycle', 'grant')
            ->withCount('projects as project_count')
            ->orderByDesc('id')
            ->get();

        return view('file-explorer.index', compact('programs'));
    }

    /**
     * AJAX: search projects by old_project_id or title.
     */
    public function ajaxList(Request $request)
    {
        $search  = $request->input('search.value', '');
        $program = $request->input('program_id', '');

        $query = Project::query()
            ->select('id', 'old_project_id', 'title', 'program_id')
            ->with(['program.cycle', 'program.grant']);

        // Filter by program (research call)
        if ($program) {
            $query->where('program_id', $program);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('old_project_id', 'like', "%{$search}%")
                   ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $total    = $query->count();
        $start    = (int) $request->input('start', 0);
        $length   = (int) $request->input('length', 10);
        $projects = $query->orderBy('created_at', 'desc')->skip($start)->take($length)->get();

        $data = $projects->map(function ($p) {
            $oldId     = $p->old_project_id ?? $p->id;
            $grantCode = $p->program->grant->grant_code ?? '—';
            $cycleYear = $p->program->cycle->year ?? '—';

            $files = $this->getProjectFiles($p);
            $fileCount = $files->count();

            return [
                'old_project_id' => e($oldId),
                'title'          => e($p->title),
                'cycle'          => e($cycleYear),
                'grant'          => e($grantCode),
                'files'          => $fileCount . ' file' . ($fileCount !== 1 ? 's' : ''),
                'action'         => '<div class="btn-action-group" style="white-space:nowrap;"><a href="' . route('file-explorer.download-project', $p->id) . '" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;" title="Download ZIP" data-bs-toggle="tooltip"><i class="fas fa-file-zipper" style="font-size:11px;"></i> ZIP</a></div>',
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
    }

    /**
     * Download all files for a single project as a ZIP.
     * Structure: {old_id}/proposals/…, {old_id}/progress_reports/…, etc.
     */
    public function downloadProject(int $id)
    {
        $project = Project::with('program.cycle', 'program.grant')->find($id);
        abort_unless($project, 404);

        $files = $this->getProjectFiles($project);
        if ($files->isEmpty()) {
            return back()->with('error', 'No files found for this project.');
        }

        $oldId  = str_replace('/', '', $project->old_project_id ?? $project->id);
        $zipName = "project_{$oldId}.zip";
        $zipPath = storage_path("app/{$zipName}");

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file['full_path'], $oldId . '/' . $file['type_folder'] . '/' . $file['name']);
            }
            $zip->close();

            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        }

        abort(500, 'Could not create ZIP');
    }

    /**
     * Download all files for an entire research call (program) as a ZIP.
     * Structure: {project_old_id}/{type_folder}/…
     */
    public function downloadProgram(int $programId)
    {
        $program = Program::with('projects', 'cycle', 'grant')->find($programId);
        abort_unless($program, 404);

        $grantCode = $program->grant->grant_code ?? 'other';
        $zipName   = "research_call_{$grantCode}_{$program->cycle->year}.zip";
        $zipPath   = storage_path("app/{$zipName}");
        $hasFiles  = false;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($program->projects as $project) {
                $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);
                $files = $this->getProjectFiles($project);
                foreach ($files as $file) {
                    $hasFiles = true;
                    $zip->addFile(
                        $file['full_path'],
                        $oldId . '/' . $file['type_folder'] . '/' . $file['name']
                    );
                }
            }
            $zip->close();
        }

        if (!$hasFiles) {
            @unlink($zipPath);
            return back()->with('error', 'No files found for this research call.');
        }

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    /**
     * Collect all existing files for a project across all types.
     *
     * @return \Illuminate\Support\Collection<int, array{full_path: string, name: string, type_folder: string, type: string}>
     */
    private function getProjectFiles(Project $project): \Illuminate\Support\Collection
    {
        $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);
        $files = collect();

        foreach (self::TYPES as $type => $typeFolder) {
            $dir = storage_path('app/' . $project->getStorageDir($typeFolder));

            if (!is_dir($dir)) {
                continue;
            }

            // Check versioned files first (v2, v3, …), then the base file
            $versions = glob($dir . '/' . $oldId . '_' . $type . '_v*.pdf');
            $baseFile = $dir . '/' . $oldId . '_' . $type . '.pdf';

            if ($versions) {
                foreach ($versions as $vPath) {
                    $files->push([
                        'full_path'  => $vPath,
                        'name'       => basename($vPath),
                        'type_folder'=> $typeFolder,
                        'type'       => $type,
                    ]);
                }
            } elseif (file_exists($baseFile)) {
                $files->push([
                    'full_path'  => $baseFile,
                    'name'       => basename($baseFile),
                    'type_folder'=> $typeFolder,
                    'type'       => $type,
                ]);
            }
        }

        return $files;
    }
}
