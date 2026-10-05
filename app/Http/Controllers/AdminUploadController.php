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
        $cycleConfigs = \App\Models\CycleConfig::orderBy('year', 'desc')->get();
        return view('admin.upload-reports.index', compact('cycleConfigs'));
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
                'projects.extended_progress',
                'projects.lpi_id',
                'projects.program_id',
                'projects.proposal_filename',
                'programs.program_title',
                'programs.cycle_id'
            )
            ->leftJoin('programs', 'projects.program_id', '=', 'programs.id')
            ->with(['lpi', 'program.cycle', 'program.grant', 'submissions']);

        // Cycle filter
        $cycleId = $request->input('cycle_id');
        if ($cycleId) {
            $query->where('programs.cycle_id', $cycleId);
        }

        $recordsTotal = Project::count();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('projects.old_project_id', 'like', "%{$search}%")
                  ->orWhere('projects.title', 'like', "%{$search}%")
                  ->orWhere('programs.program_title', 'like', "%{$search}%");
            });
        }

        // Missing reports filter
        $missing = $request->input('missing', []);
        if (!is_array($missing)) {
            $missing = $missing ? [$missing] : [];
        }
        if (in_array('proposal', $missing)) {
            $query->where(function ($q) {
                $q->whereNull('projects.proposal_filename')
                  ->orWhere('projects.proposal_filename', '');
            });
        }
        if (in_array('progress', $missing)) {
            $query->whereDoesntHave('submissions', function ($sq) {
                $sq->where('type', 'progress');
            });
        }
        if (in_array('final', $missing)) {
            $query->whereDoesntHave('submissions', function ($sq) {
                $sq->where('type', 'final');
            });
        }
        if (in_array('readiness', $missing)) {
            $query->whereDoesntHave('submissions', function ($sq) {
                $sq->where('type', 'readiness');
            });
        }
        if (in_array('ethical', $missing)) {
            $query->whereDoesntHave('submissions', function ($sq) {
                $sq->where('type', 'ethical');
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
            $hasProposal = !empty($p->proposal_filename);
            $hasProgress = $p->submissions->where('type', 'progress')->first();
            $hasFinal = $p->submissions->where('type', 'final')->first();
            $hasReadiness = $p->submissions->where('type', 'readiness')->first();
            $hasEthical = $p->submissions->where('type', 'ethical')->first();
            $isExtended = (bool) ($p->extended_progress ?? false);
            $hasProgress2 = $p->submissions->where('type', 'progress2')->first();

            $proposalBadge = $hasProposal
                ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>';

            $progressBadge = $hasProgress
                ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>';

            $finalBadge = $hasFinal
                ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>';

            $readinessBadge = $hasReadiness
                ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>';

            $ethicalBadge = $hasEthical
                ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>';

            // Missing reports (comma separated)
            $missingItems = [];
            if (empty($p->proposal_filename)) {
                $missingItems[] = 'Proposal';
            }
            if (!$hasProgress) {
                $missingItems[] = 'Progress Report';
            }
            if (!$hasFinal) {
                $missingItems[] = 'Final Report';
            }
            if (!$hasReadiness) {
                $missingItems[] = 'Readiness Report';
            }
            if (!$hasEthical) {
                $missingItems[] = 'Ethical Approval';
            }
            $missingStr = implode(', ', $missingItems);
            $missingCell = $missingStr
                ? '<span style="color:#b91c1c;font-size:11px;font-weight:600;line-height:1.4;">' . e($missingStr) . '</span>'
                : '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Complete</span>';

            $progress2Badge = !$isExtended
                ? '<span class="pill inactive" style="font-size:10px;">—</span>'
                : ($hasProgress2
                    ? '<span class="pill success" style="font-size:10px;"><i class="fas fa-check" style="font-size:9px;"></i> Uploaded</span>'
                    : '<span class="pill inactive" style="font-size:10px;"><i class="fas fa-minus" style="font-size:9px;"></i> None</span>');

            return [
                'id'            => $p->id,
                'old_project_id' => e($oldId),
                'title'         => '<div style="font-weight:500;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' . e($p->title) . '">' . e($p->title) . '</div>',
                'lpi'           => '<div style="font-weight:500;">' . e($lpiName) . '</div>',
                'lpi_email'     => '<span style="font-size:12px;">' . e($lpiEmail) . '</span>',
                'program'       => '<div style="font-weight:500;max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' . e($programTitle) . '">' . e($programTitle) . '</div><div style="font-size:11px;color:var(--ink-400,#8b8592);">' . e($cycleYear . ' / ' . $grantCode) . '</div>',
                'proposal'      => $proposalBadge,
                'progress'      => $progressBadge,
                'progress2'     => $progress2Badge,
                'final'         => $finalBadge,
                'readiness'      => $readinessBadge,
                'ethical'       => $ethicalBadge,
                'missing'       => $missingCell,
                'action'        => $this->renderUploadForm($p, $oldId, $hasProgress, $hasProgress2, $hasFinal, $hasReadiness),
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
    }

    /**
     * Render the inline upload form HTML for a project row.
     */
    private function renderUploadForm(Project $p, string $oldId, $hasProgress, $hasProgress2, $hasFinal, $hasReadiness): string
    {
        $html = '<form class="admin-upload-form" data-project-id="' . $p->id . '" enctype="multipart/form-data" style="display:flex;gap:4px;align-items:center;flex-wrap:nowrap;">';
        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
        $html .= '<input type="hidden" name="project_id" value="' . $p->id . '">';

        // Proposal
        $hasProposal = !empty($p->proposal_filename);
        $proposalClass = $hasProposal ? ' has-file' : '';
        $proposalLabel = $hasProposal ? '<i class="fas fa-check" style="font-size:10px;"></i> Proposal' : '<i class="fas fa-file-pdf" style="font-size:10px;"></i> Proposal';
        $html .= '<label class="admin-upload-label' . $proposalClass . '" title="Proposal">';
        $html .= '<input type="file" name="proposal" accept=".pdf" class="admin-upload-input" data-type="proposal">';
        $html .= '<span class="admin-upload-btn btn-sm btn-secondary" style="font-size:10px;padding:3px 8px;cursor:pointer;">';
        $html .= $proposalLabel;
        $html .= '</span>';
        $html .= '</label>';

        // Progress
        $html .= '<label class="admin-upload-label" title="Progress Report">';
        $html .= '<input type="file" name="progress" accept=".pdf" class="admin-upload-input" data-type="progress">';
        $html .= '<span class="admin-upload-btn btn-sm btn-secondary" style="font-size:10px;padding:3px 8px;cursor:pointer;">';
        $html .= '<i class="fas fa-file-pdf" style="font-size:10px;"></i> Progress';
        $html .= '</span>';
        $html .= '</label>';

        // Progress 2 (only for extended progress projects)
        if ($p->extended_progress) {
            $html .= '<label class="admin-upload-label" title="Progress Report 2">';
            $html .= '<input type="file" name="progress2" accept=".pdf" class="admin-upload-input" data-type="progress2">';
            $html .= '<span class="admin-upload-btn btn-sm btn-secondary" style="font-size:10px;padding:3px 8px;cursor:pointer;">';
            $html .= '<i class="fas fa-file-pdf" style="font-size:10px;"></i> Progress 2';
            $html .= '</span>';
            $html .= '</label>';
        }

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

        // Ethical Approval
        $html .= '<label class="admin-upload-label" title="Ethical Approval">';
        $html .= '<input type="file" name="ethical" accept=".pdf" class="admin-upload-input" data-type="ethical">';
        $html .= '<span class="admin-upload-btn btn-sm btn-secondary" style="font-size:10px;padding:3px 8px;cursor:pointer;">';
        $html .= '<i class="fas fa-file-pdf" style="font-size:10px;"></i> Ethical';
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

        foreach (['proposal', 'progress', 'progress2', 'final', 'readiness', 'ethical'] as $type) {
            if (!$request->hasFile($type)) {
                continue;
            }

            if ($type === 'progress2' && !$project->extended_progress) {
                $errors[] = "{$type}: This project does not have Progress Report 2 enabled";
                continue;
            }

            $file = $request->file($type);
            if (!$file->isValid() || $file->getClientOriginalExtension() !== 'pdf') {
                $errors[] = "{$type}: Invalid file";
                continue;
            }

            try {
                if ($type === 'proposal') {
                    $this->storeProposal($project, $file);
                } else {
                    $this->storeReport($project, $type, $file, $user);
                }
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
     * Store a proposal file.
     */
    private function storeProposal(Project $project, $file): void
    {
        $oldId = $project->getFileSafeOldProjectId();
        $safeName = $oldId . '.pdf';

        $dir = $project->getStorageDir('proposals');
        $fullDir = storage_path('app/' . $dir);
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        // Delete old file if exists
        if ($project->proposal_filename && $project->proposal_filename !== $safeName) {
            $oldPath = $fullDir . '/' . $project->proposal_filename;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $file->storeAs($dir, $safeName);
        $project->update(['proposal_filename' => $safeName]);
    }

    /**
     * Store a report file using the same logic as ProgressController.
     */
    private function storeReport(Project $project, string $type, $file, $user): void
    {
        $oldId = $project->getFileSafeOldProjectId();

        $typeFolderMap = [
            'progress'  => 'progress_reports',
            'progress2' => 'progress_reports',
            'final'     => 'final_reports',
            'readiness' => 'readiness_reports',
            'ethical'   => 'ethical_approvals',
        ];

        $typeFolder = $typeFolderMap[$type];
        $dir = $project->getStorageDir($typeFolder);
        $fullDir = storage_path('app/' . $dir);
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        $storedFilename = $oldId . '_' . $type . '.pdf';
        $path = $dir . '/' . $storedFilename;

        // Delete ALL existing submissions of this type (deterministic cleanup)
        $project->submissions()->where('type', $type)->get()
            ->each(function ($existing) {
                $oldPath = storage_path('app/' . $existing->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existing->delete();
            });

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
