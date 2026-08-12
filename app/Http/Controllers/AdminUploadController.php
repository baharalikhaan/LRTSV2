<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->isAdmin()) {
                return redirect()->route('home')->with('error', 'Unauthorized.');
            }
            return $next($request);
        });
    }

    /**
     * Show the admin upload page.
     */
    public function index()
    {
        return view('admin.upload-reports.index');
    }

    /**
     * Server-side DataTable AJAX endpoint.
     */
    public function ajaxList(Request $request)
    {
        $search = $request->input('search.value', '');

        $query = Project::query()
            ->select(
                'projects.id',
                'projects.old_project_id',
                'projects.title',
                'programs.program_title',
                'programs.cycle_id'
            )
            ->leftJoin('programs', 'projects.program_id', '=', 'programs.id')
            ->with(['lpi', 'program.cycle', 'program.grant', 'submissions']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('projects.old_project_id', 'like', "%{$search}%")
                  ->orWhere('projects.title', 'like', "%{$search}%")
                  ->orWhere('programs.program_title', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $projects = $query->orderByDesc('projects.id')
            ->skip($start)->take($length)
            ->get();

        $data = $projects->map(function ($p) {
            $oldId = $p->old_project_id ?? $p->id;
            $lpiName = $p->lpi ? $p->lpi->name : '—';
            $lpiEmail = $p->lpi ? $p->lpi->email : '—';
            $programTitle = $p->program->program_title ?? '—';
            $cycleYear = $p->program->cycle->year ?? '—';
            $grantCode = $p->program->grant->grant_code ?? '—';

            // Check existing submissions
            $hasProgress = $p->submissions->where('type', 'progress')->first();
            $hasFinal = $p->submissions->where('type', 'final')->first();
            $hasReadiness = $p->submissions->where('type', 'readiness')->first();

            $progressBadge = $hasProgress
                ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>';

            $finalBadge = $hasFinal
                ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>';

            $readinessBadge = $hasReadiness
                ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>';

            return [
                'id'            => $p->id,
                'old_project_id' => e($oldId),
                'title'         => '<div style="font-weight:500;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' . e($p->title) . '">' . e($p->title) . '</div>',
                'lpi'           => '<div style="font-weight:500;">' . e($lpiName) . '</div><div style="font-size:11px;color:var(--ink-400,#8b8592);">' . e($lpiEmail) . '</div>',
                'program'       => '<div style="font-weight:500;max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' . e($programTitle) . '">' . e($programTitle) . '</div><div style="font-size:11px;color:var(--ink-400,#8b8592);">' . e($cycleYear . ' / ' . $grantCode) . '</div>',
                'progress'      => $progressBadge,
                'final'         => $finalBadge,
                'readiness'     => $readinessBadge,
                'action'        => $this->renderUploadForm($p, $oldId, $hasProgress, $hasFinal, $hasReadiness),
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
     * Render the inline upload form HTML for a project row.
     */
    private function renderUploadForm(Project $p, string $oldId, $hasProgress, $hasFinal, $hasReadiness): string
    {
        $html = '<form class="admin-upload-form" data-project-id="' . $p->id . '" enctype="multipart/form-data" style="display:flex;gap:4px;align-items:center;flex-wrap:nowrap;">';
        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
        $html .= '<input type="hidden" name="project_id" value="' . $p->id . '">';

        // Progress
        $html .= '<label class="admin-upload-label" title="Progress Report">';
        $html .= '<input type="file" name="progress" accept=".pdf" class="admin-upload-input" data-type="progress">';
        $html .= '<span class="admin-upload-btn btn-sm btn-secondary" style="font-size:10px;padding:3px 8px;cursor:pointer;">';
        $html .= '<i class="fas fa-file-pdf" style="font-size:10px;"></i> Progress';
        $html .= '</span>';
        $html .= '</label>';

        // Final
        $html .= '<label class="admin-upload-label" title="Final Report">';
        $html .= '<input type="file" name="final" accept=".pdf" class="admin-upload-input" data-type="final">';
        $html .= '<span class="admin-upload-btn btn-sm btn-secondary" style="font-size:10px;padding:3px 8px;cursor:pointer;">';
        $html .= '<i class="fas fa-file-pdf" style="font-size:10px;"></i> Final';
        $html .= '</span>';
        $html .= '</label>';

        // Readiness
        $html .= '<label class="admin-upload-label" title="Readiness Report">';
        $html .= '<input type="file" name="readiness" accept=".pdf" class="admin-upload-input" data-type="readiness">';
        $html .= '<span class="admin-upload-btn btn-sm btn-secondary" style="font-size:10px;padding:3px 8px;cursor:pointer;">';
        $html .= '<i class="fas fa-file-pdf" style="font-size:10px;"></i> Readiness';
        $html .= '</span>';
        $html .= '</label>';

        // Submit
        $html .= '<button type="submit" class="btn-sm btn-primary admin-upload-submit" style="font-size:10px;padding:3px 8px;">';
        $html .= '<i class="fas fa-upload" style="font-size:10px;"></i>';
        $html .= '</button>';

        $html .= '</form>';

        return $html;
    }

    /**
     * Handle the file upload (AJAX).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $project = Project::with('program.cycle', 'program.grant')->find($request->input('project_id'));
        $user = auth()->user();

        $uploaded = 0;
        $errors = [];

        foreach (['progress', 'final', 'readiness'] as $type) {
            if (!$request->hasFile($type)) {
                continue;
            }

            $file = $request->file($type);
            if (!$file->isValid() || $file->getClientOriginalExtension() !== 'pdf') {
                $errors[] = "{$type}: Invalid file";
                continue;
            }

            try {
                $this->storeReport($project, $type, $file, $user);
                $uploaded++;
            } catch (\Exception $e) {
                $errors[] = "{$type}: " . $e->getMessage();
            }
        }

        if ($uploaded === 0 && !empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . implode('; ', $errors),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "{$uploaded} report(s) uploaded successfully." . (!empty($errors) ? ' ' . implode(' ', $errors) : ''),
        ]);
    }

    /**
     * Store a report file using the same logic as ProgressController.
     */
    private function storeReport(Project $project, string $type, $file, $user): void
    {
        $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);

        $typeFolderMap = [
            'progress'  => 'progress_reports',
            'final'     => 'final_reports',
            'readiness' => 'readiness_reports',
        ];

        $typeFolder = $typeFolderMap[$type];
        $dir = $project->getStorageDir($typeFolder);
        $storedFilename = $oldId . '_' . $type . '.pdf';
        $path = $dir . '/' . $storedFilename;

        // Delete existing submission of this type
        $existing = $project->submissions()->where('type', $type)->first();
        if ($existing) {
            $oldPath = storage_path('app/' . $existing->file_path);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $existing->delete();
        }

        // Store the file
        $file->storeAs($dir, $storedFilename);

        // Create submission record
        $project->submissions()->create([
            'type'              => $type,
            'file_path'         => $path,
            'stored_filename'   => $storedFilename,
            'original_filename' => $file->getClientOriginalName(),
            'version'           => 1,
            'user_id'           => $user->id,
            'submitted'         => true,
            'submitted_at'      => now(),
        ]);
    }
}
