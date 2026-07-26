<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Pillar;
use App\Models\College;
use App\Models\Commitment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;

class RegisterWizardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Load the wizard modal content for a project.
     */
    public function wizard($id)
    {
        $confProject = Project::with('program.grant')->findOrFail($id);

        if ($confProject->hasStatus(Project::STATUS_REGISTERED)) {
            return response()->json(['error' => 'This project has already been registered.'], 422);
        }

        // Block if the program is inactive
        if (!$confProject->programIsActive()) {
            return response()->json(['error' => 'This program is no longer active. Projects under this program cannot be registered or manipulated.'], 422);
        }

        $user = auth()->user();
        if ($confProject->lpi_id !== null && $confProject->lpi_id !== $user->id) {
            return response()->json(['error' => 'This project has already been claimed by another PI.'], 422);
        }

        $pillars = Pillar::selectRaw('MIN(id) as id, pillar')
                         ->groupBy('pillar')
                         ->orderBy('pillar')
                         ->get();
        $colleges = College::orderBy('name')->get();

        // Load existing pillar and college selections for this project
        $projectPillarIds = $confProject->pillars()->pluck('pillars.id')->toArray();
        $projectCollegeIds = $confProject->colleges()->pluck('colleges.id')->toArray();

        $html = view('projects.register-wizard', compact('confProject', 'pillars', 'colleges', 'projectPillarIds', 'projectCollegeIds'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Load the full-page registration wizard for a project.
     * Similar to wizard() but returns a full Blade view (not modal AJAX).
     */
    public function registerPage($id)
    {
        $confProject = Project::with('program.grant')->findOrFail($id);

        if ($confProject->hasStatus(Project::STATUS_REGISTERED)) {
            return redirect()->back()->with('error', 'This project has already been registered.');
        }

        if (!$confProject->programIsActive()) {
            return redirect()->back()->with('error', 'This program is no longer active. Projects under this program cannot be registered or manipulated.');
        }

        $user = auth()->user();
        if ($confProject->lpi_id !== null && $confProject->lpi_id !== $user->id) {
            return redirect()->back()->with('error', 'This project has already been claimed by another PI.');
        }

        $pillars = Pillar::selectRaw('MIN(id) as id, pillar')
                         ->groupBy('pillar')
                         ->orderBy('pillar')
                         ->get();
        $colleges = College::orderBy('name')->get();

        // Load existing pillar and college selections for this project
        $projectPillarIds = $confProject->pillars()->pluck('pillars.id')->toArray();
        $projectCollegeIds = $confProject->colleges()->pluck('colleges.id')->toArray();

        // Build proposal PDF URL if a proposal file exists
        $proposalUrl = null;
        if ($confProject->proposal_filename) {
            $proposalUrl = route('wizard.proposal', $confProject->id);
        }

        return view('projects.register-wizard-page', compact('confProject', 'pillars', 'colleges', 'proposalUrl', 'projectPillarIds', 'projectCollegeIds'));
    }

    /**
     * Serve the proposal PDF for a project (authenticated).
     */
    public function serveProposal($id)
    {
        $project = Project::findOrFail($id);

        if (!$project->proposal_filename) {
            abort(404, 'No proposal file for this project.');
        }

        $filePath = $project->getStorageDir('proposals') . '/' . $project->proposal_filename;
        $fullPath = storage_path('app/' . $filePath);

        if (!file_exists($fullPath)) {
            abort(404, 'Proposal file not found on disk.');
        }

        $filename = basename($project->proposal_filename);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Upload a proposal PDF for a project (via AJAX from the wizard page).
     */
    public function uploadProposal(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'proposal_file' => 'required|file|mimes:pdf|max:20480', // 20 MB
        ]);

        $file = $request->file('proposal_file');
        $originalName = $file->getClientOriginalName();

        // Deterministic filename: <old_project_id>_proposal.pdf
        $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);
        $safeName = $oldId . '_proposal.pdf';

        $dir = $project->getStorageDir('proposals');
        $file->storeAs($dir, $safeName);

        // Delete old file if exists
        if ($project->proposal_filename && $project->proposal_filename !== $safeName) {
            $oldPath = storage_path('app/' . $dir . '/' . $project->proposal_filename);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $project->update(['proposal_filename' => $safeName]);

        $proposalUrl = route('wizard.proposal', $project->id);

        return response()->json([
            'success' => true,
            'message' => 'Proposal uploaded successfully.',
            'proposal_url' => $proposalUrl,
            'filename' => $originalName,
        ]);
    }

    /**
     * Save all wizard data at once (called from Review & Submit step).
     */
    public function saveAll(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'project_title_en' => 'required|string|max:500',
            'pi_name' => 'required|string|max:255',
            'pi_email' => 'required|email|max:255',
            // Pillars
            'pillars' => 'nullable|array',
            'pillars.*' => 'exists:pillars,id',
            // College (single select)
            'college_id' => 'nullable|exists:colleges,id',
            // Publications
            'pub_q1' => 'nullable|integer|min:0',
            'pub_q2' => 'nullable|integer|min:0',
            'pub_q3' => 'nullable|integer|min:0',
            'pub_q4' => 'nullable|integer|min:0',
            'pub_conf' => 'nullable|integer|min:0',
            'pub_books' => 'nullable|integer|min:0',
            'pub_edit_books' => 'nullable|integer|min:0',
            'pub_chapters' => 'nullable|integer|min:0',
            // IP & innovation
            'ip_count' => 'nullable|integer|min:0',
            'ip_patents' => 'nullable|integer|min:0',
            'ip_opensource' => 'nullable|integer|min:0',
            'ip_startup' => 'nullable|boolean',
            'ip_ethical' => 'nullable|boolean',
            // Students
            'stu_master' => 'nullable|integer|min:0',
            'stu_ug' => 'nullable|integer|min:0',
            'stu_phd' => 'nullable|integer|min:0',
            'stu_cross' => 'nullable|boolean',
        ])->validate();

        $project = Project::findOrFail($validated['project_id']);

        if ($project->hasStatus(Project::STATUS_REGISTERED)) {
            return response()->json(['error' => 'This project has already been registered.'], 422);
        }

        $user = auth()->user();

        DB::transaction(function () use ($validated, $project, $user) {
            // 1. Update project details and mark as registered
            $project->update([
                'title' => $validated['project_title_en'],
                'author' => $validated['pi_name'],
                'email' => $validated['pi_email'],
                'lpi_id' => $user->id,
                'college_decision' => 'pending',
            ]);

            // 2. Attach pillars
            if (!empty($validated['pillars'])) {
                $project->pillars()->sync($validated['pillars']);
            }

            // 3. Attach college (single)
            if (!empty($validated['college_id'])) {
                $project->colleges()->sync([$validated['college_id']]);
            }

            // 5. Save commitments — map flat field names to Commitment model columns
            $map = [
                'pub_q1' => 'q1article',
                'pub_q2' => 'q2article',
                'pub_q3' => 'q3article',
                'pub_q4' => 'q4article',
                'pub_conf' => 'confArticle',
                'pub_books' => 'books',
                'pub_edit_books' => 'editBooks',
                'pub_chapters' => 'chapters',
                'ip_count' => 'ip',
                'ip_patents' => 'filedPatent',
                'ip_opensource' => 'openSourceSW',
                'ip_startup' => 'startUp',
                'ip_ethical' => 'ethical',
                'stu_master' => 'master',
                'stu_ug' => 'UG',
                'stu_phd' => 'Phd',
                'stu_cross' => 'crossCollege',
            ];

            $cmtData = ['project_id' => $project->id];
            $hasValue = false;
            foreach ($map as $formField => $dbCol) {
                $val = $validated[$formField] ?? null;
                // Treat checkboxes '0'/'1' string as integers
                if ($val === '0' || $val === '1') {
                    $val = (int) $val;
                }
                $cmtData[$dbCol] = $val ?? 0;
                if ($val !== null && $val !== 0 && $val !== '0') {
                    $hasValue = true;
                }
            }

            if ($hasValue) {
                $project->commitments()->create($cmtData);
            }
            // 6. Record registration status
            $project->recordStatus(Project::STATUS_REGISTERED, null, $user->id);
        });

        return response()->json([
            'success' => true,
            'message' => 'Project registered successfully.',
            'redirect' => route('projects.my'),
        ]);
    }
}
