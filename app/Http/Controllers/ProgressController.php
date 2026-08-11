<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectStudent;
use App\Models\ProjectStudentDetail;
use App\Models\ProjectPublication;
use App\Models\Outcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ProgressController extends Controller
{
    /**
     * Show the full-page add progress form.
     * If progress data already exists, show it in edit mode.
     */
    public function add($id)
    {
        $project = Project::with([
            'program', 'grant', 'lpi', 'latestStatus',
            'commitments', 'pillars', 'colleges',
        ])->findOrFail($id);

        // Only allow LPI / admin to add progress
        $user = Auth::user();
        $role = $user->activeRole();
        if (!in_array($role, ['LPI', 'Admin'])) {
            abort(403, 'You are not authorized to add progress reports.');
        }

        // Check if program is active
        if (!$project->programIsActive()) {
            return redirect()->route('projects.show', $id)
                ->with('error', 'This program is no longer active. Progress cannot be added.');
        }

        $data = $this->loadFormData($project);
        $data['mode'] = 'progress';

        return view('projects.add-progress', $data);
    }

    /**
     * Load all shared form data for the unified Add/Update Progress + Final Report page.
     */
    protected function loadFormData(Project $project)
    {
        // Get existing outcomes for this project with publication details
        $outcomes = $project->outcomes()
            ->with('publication')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get existing submissions for this project
        $submissions = $project->submissions()
            ->orderBy('created_at', 'desc')
            ->get();

        // Available outcome types for scholarly/publication outcomes
        $outcomeTypes = ['publication', 'patent', 'presentation', 'award', 'grant', 'thesis', 'other'];
        // Expanded types for the new structured outcomes tab
        $structuredOutcomeTypes = [
            'journal_q1'       => 'Journal articles (Web of Science — Q1)',
            'journal_q2'       => 'Journal articles (Web of Science — Q2)',
            'journal_q3'       => 'Journal articles (Web of Science — Q3)',
            'journal_q4'       => 'Journal articles (Web of Science — Q4)',
            'conference'       => 'Indexed international conferences',
            'book'             => 'Published Books',
            'edited_book'      => 'Edited Books (collection)',
            'book_chapter'     => 'Book Chapters',
            'ip_disclosure'    => 'Intellectual Property Disclosure',
            'provisional_patent' => 'Provisional Patent',
            'patent_granted'   => 'Patents Granted',
            'open_source_sw'   => 'Open Source Software',
            'startup'          => 'Start-Up Created',
        ];

        // Get existing project students with their details
        $projectStudents = $project->students()->with('details')->orderBy('type')->get();

        // Get existing researchers
        $projectResearchers = $project->researchers()->orderBy('created_at')->get();

        // Get existing contributions (IP disclosure, patents, open source, startup)
        $contributionGroups = $project->contributions()
            ->orderBy('created_at')
            ->get()
            ->groupBy('type');

        // Deadline info from program
        $program = $project->program;
        $deadlines = [
            'progress' => [
                'original' => $program ? $program->prog_rpt_deadline : null,
                'extended' => $program ? $program->extended_prog_rpt_deadline : null,
                'label' => 'Progress Report',
            ],
            'readiness' => [
                'original' => $program ? $program->prog_rpt2_deadline : null,
                'extended' => $program ? $program->extended_prog_rpt2_deadline : null,
                'label' => 'Readiness Report',
            ],
            'final' => [
                'original' => $program ? $program->final_rpt_deadline : null,
                'extended' => $program ? $program->extended_final_rpt_deadline : null,
                'label' => 'Final Report',
            ],
        ];

        return compact(
            'project',
            'outcomes',
            'outcomeTypes',
            'structuredOutcomeTypes',
            'submissions',
            'deadlines',
            'projectStudents',
            'projectResearchers',
            'contributionGroups'
        );
    }

    /**
     * Save the progress report (status transition + outcome data).
     */
    public function save(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $user = Auth::user();
        $role = $user->activeRole();
        if (!in_array($role, ['LPI', 'Admin'])) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        if (!$project->programIsActive()) {
            return response()->json(['success' => false, 'error' => 'Program is no longer active.'], 422);
        }

        // Validate
        $validated = $request->validate([
            'narrative'     => 'nullable|string|max:5000',
            'achievements'  => 'nullable|string|max:5000',
            'challenges'    => 'nullable|string|max:5000',
            'next_steps'    => 'nullable|string|max:5000',

            // IP / Declaration fields (Yes/No) — kept for backward compat
            'has_ip_disclosure'         => 'nullable|in:Yes,No',
            'has_provisional_patent'    => 'nullable|in:Yes,No',
            'has_granted_patent'        => 'nullable|in:Yes,No',
            'has_open_source_software'  => 'nullable|in:Yes,No',
            'has_startup'               => 'nullable|in:Yes,No',

            // Outcomes (full-form submit only carries detail inputs as arrays; the
            // `type` is set via the dedicated saveOutcomes endpoint, so we only
            // loosely validate the container and skip type-less rows in the loop)
            'outcomes'      => 'nullable|array',
            'outcomes.*.type'       => 'nullable|string',
            'outcomes.*.identifier' => 'nullable|string|max:500',
            'outcomes.*.online_date' => 'nullable|date',

            // File submissions
            'submissions'   => 'nullable|array',
            'submissions.*' => 'nullable|file|mimes:pdf|max:10240',

            'submission_notes' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($project, $validated, $user, $request) {
            // Save outcomes
            if (!empty($validated['outcomes'])) {
                foreach ($validated['outcomes'] as $outcomeData) {
                    // Skip entries coming from the full-form submit that lack a type
                    // (those are detail-only inputs; types are saved via saveOutcomes)
                    if (empty($outcomeData['type'])) {
                        continue;
                    }
                    $project->outcomes()->create([
                        'user_id'     => $user->id,
                        'type'        => $outcomeData['type'],
                        'identifier'  => $outcomeData['identifier'] ?? null,
                        'online_date' => $outcomeData['online_date'] ?? null,
                        'verifcation_by_system'    => false,
                        'verifcation_by_reviewer'  => false,
                        'score'       => 0,
                    ]);
                }
            }

            // Save file submissions (progress report only — readiness/final are in final step)
            if ($request->hasFile('submissions')) {
                $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);
                foreach ($request->file('submissions') as $type => $file) {
                    if ($file === null) continue;
                    // Only save progress type files here
                    if ($type !== 'progress' && $type !== 'progress_report') continue;
                    $typeLabel = 'progress';
                    $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
                    $dir = $project->getStorageDir('progress_reports');
                    // Delete existing report of this type
                    $existing = $project->submissions()->where('type', 'progress')->first();
                    if ($existing && $existing->stored_filename && $existing->stored_filename !== $storedFilename) {
                        $oldPath = storage_path('app/' . $dir . '/' . $existing->stored_filename);
                        if (file_exists($oldPath)) { @unlink($oldPath); }
                    }
                    $file->storeAs($dir, $storedFilename);
                    $path = $dir . '/' . $storedFilename;
                    $project->submissions()->create([
                        'type'              => 'progress',
                        'file_path'         => $path,
                        'stored_filename'   => $storedFilename,
                        'original_filename' => $file->getClientOriginalName(),
                        'notes'             => $validated['submission_notes'] ?? null,
                        'user_id'           => $user->id,
                    ]);
                }
            }

            // Record the progress_added status (always record on save, even after rejection)
            $project->recordStatus(Project::STATUS_PROGRESS_ADDED, [
                'triggered_by' => 'progress',
            ], $user->id);

            // If progress was previously graded, reset the grading to pending
            $existingGrading = \App\Models\ProgressReportGrading::where('project_id', $project->id)
                ->where('publish', '!=', 'pending')
                ->first();
            if ($existingGrading) {
                $existingGrading->update(['publish' => 'pending']);
            }
        });

        // Handle both AJAX and standard form submission
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress report saved successfully.',
                'redirect' => route('projects.show', $project->id),
            ]);
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Progress report saved successfully.');
    }

    /**
     * AJAX: Upload a single submission file immediately on file selection.
     * Accepts POST with: file (the uploaded file), type (progress|readiness|final)
     *
     * Normal behavior (no rejection): replaces any existing file of the same type,
     * keeping version = 1.
     *
     * After progress rejection: creates a new version (version N+1) and keeps the
     * previous version on disk for reviewer comparison.
     *
     * Returns JSON with the saved record and a rendered HTML snippet for the
     * download link.
     */
    public function uploadFile(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = auth()->user();

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
            'type' => 'required|in:progress,progress_extended,readiness,final',
        ]);

        $file = $request->file('file');
        $type = $request->input('type');

        // For extended progress, check if enabled
        if ($type === 'progress_extended' && !$project->is_extended) {
            return response()->json([
                'success' => false,
                'error'   => 'Extended progress report is not enabled for this project.',
            ], 422);
        }

        // Map to submission type and version
        $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);

        if ($type === 'readiness') {
            $submissionType = 'readiness';
            $typeLabel = 'readiness';
            $dir = $project->getStorageDir('readiness_reports');
        } elseif ($type === 'final') {
            $submissionType = 'final';
            $typeLabel = 'final';
            $dir = $project->getStorageDir('final_reports');
        } else {
            $submissionType = 'progress';
            $typeLabel = 'progress';
            $dir = $project->getStorageDir('progress_reports');
        }

        if ($type === 'progress_extended') {
            $submissionType = 'progress';
            $typeLabel = 'progress';
            $dir = $project->getStorageDir('progress_reports');
            // Extended progress = version 2
            $maxVersion = $project->submissions()->where('type', 'progress')->max('version') ?? 1;
            $newVersion = max($maxVersion, 2);
            $storedFilename = $oldId . '_progress_v' . $newVersion . '.pdf';
            $version = $newVersion;
        } elseif ($type === 'readiness' || $type === 'final') {
            // Final and readiness: replace existing (single file per type)
            $existing = $project->submissions()->where('type', $submissionType)->first();
            if ($existing) {
                $oldPath = storage_path('app/' . $existing->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existing->delete();
            }
            $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
            $version = 1;
        } elseif ($project->hasStatus(Project::STATUS_PROGRESS_REJECTED) || $project->hasStatus(Project::STATUS_PROGRESS_EXT_REJECTED)
                   || \App\Models\ProgressReportGrading::where('project_id', $project->id)->where('publish', 'rejected')->exists()) {
            // After rejection: create a new version while keeping old
            $maxVersion = $project->submissions()->where('type', $submissionType)->max('version') ?? 1;
            $newVersion = $maxVersion + 1;
            $storedFilename = $oldId . '_' . $typeLabel . '_v' . $newVersion . '.pdf';
            $version = $newVersion;
        } else {
            // Normal upload: replace existing (single file per type)
            $existing = $project->submissions()->where('type', $submissionType)->first();
            if ($existing) {
                $oldPath = storage_path('app/' . $existing->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existing->delete();
            }
            $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
            $version = 1;
        }

        $file->storeAs($dir, $storedFilename);
        $path = $dir . '/' . $storedFilename;

        $submission = $project->submissions()->create([
            'type'              => $submissionType,
            'file_path'         => $path,
            'stored_filename'   => $storedFilename,
            'original_filename' => $file->getClientOriginalName(),
            'version'           => $version,
            'notes'             => $request->input('notes'),
            'user_id'           => $user->id,
        ]);

        // Render the download link HTML snippet
        $downloadUrl = route('serveFile2', ['type' => $submissionType, 'id' => $project->id]);
        $linkHtml = '<a href="' . $downloadUrl . '" target="_blank" style="color:var(--brand-500);font-size:12px;font-weight:500;">'
                  . '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>'
                  . e($submission->stored_filename)
                  . '</a>';

        return response()->json([
            'success'      => true,
            'type'         => $submissionType,
            'version'      => $version,
            'submission'   => [
                'id'                => $submission->id,
                'file_path'         => $submission->file_path,
                'original_filename' => $submission->original_filename,
                'stored_filename'   => $submission->stored_filename,
                'version'           => $version,
                'created_at'        => $submission->created_at->toDateTimeString(),
                'download_url'      => $downloadUrl,
            ],
            'link_html'    => $linkHtml,
            'message'      => 'Report uploaded successfully.',
        ]);
    }

    /**
     * AJAX: Delete the submission file of a given type.
     * Accepts POST with: submission_id (the submission record ID)
     * Removes the file from disk and deletes the DB record.
     */
    public function deleteFile(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'submission_id' => 'required|exists:project_submissions,id',
        ]);

        $submission = $project->submissions()
            ->where('id', $request->input('submission_id'))
            ->firstOrFail();

        // Delete the physical file
        $fullPath = storage_path('app/' . $submission->file_path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        $submission->delete();

        return response()->json([
            'success' => true,
            'type'    => $submission->type,
            'message' => 'File deleted successfully.',
        ]);
    }

    /**
     * Resolve the EFFECTIVE deadline for a report type.
     * Extended deadline wins if set; otherwise original deadline.
     * Returns null when no deadline is configured (treated as "no constraint").
     */
    protected function effectiveDeadline(Project $project, string $type)
    {
        $program = $project->program;
        if (!$program) {
            return null;
        }

        if ($type === 'progress') {
            return $program->extended_prog_rpt_deadline ?? $program->prog_rpt_deadline;
        }
        if ($type === 'readiness') {
            return $program->extended_prog_rpt2_deadline ?? $program->prog_rpt2_deadline;
        }
        if ($type === 'final') {
            return $program->extended_final_rpt_deadline ?? $program->final_rpt_deadline;
        }

        return null;
    }

    /**
     * Show the full-page "Add Final Report" form.
     * Reuses the unified add-progress page — the final report section is editable
     * and the progress sections are readonly.
     */
    public function addFinalReport($id)
    {
        $project = Project::with([
            'program', 'grant', 'lpi', 'latestStatus',
            'commitments', 'pillars', 'colleges',
        ])->findOrFail($id);

        $user = Auth::user();
        $role = $user->activeRole();
        if (!in_array($role, ['LPI', 'Admin'])) {
            abort(403, 'You are not authorized to submit final reports.');
        }

        if (!$project->programIsActive()) {
            return redirect()->route('projects.show', $id)
                ->with('error', 'This program is no longer active. Reports cannot be submitted.');
        }

        $data = $this->loadFormData($project);
        $data['mode'] = 'final';

        return view('projects.add-progress', $data);
    }

    /**
     * Save the final step: upload readiness + final report files and
     * record the final_added status.
     */
    public function saveFinalReport(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $user = Auth::user();
        $role = $user->activeRole();
        if (!in_array($role, ['LPI', 'Admin'])) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        if (!$project->programIsActive()) {
            return response()->json(['success' => false, 'error' => 'Program is no longer active.'], 422);
        }

        $validated = $request->validate([
            'submission_notes' => 'nullable|string|max:2000',
            'submissions'   => 'nullable|array',
            'submissions.*' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        // Require at least the final report (or readiness) to have been uploaded,
        // either via the AJAX upload or via direct form file inputs.
        $hasFinalUpload = $request->hasFile('submissions')
            || $project->submissions()->whereIn('type', ['readiness', 'final'])->exists();

        if (!$hasFinalUpload) {
            return response()->json([
                'success' => false,
                'error'   => 'Please upload the readiness and/or final report before submitting.',
            ], 422);
        }

        DB::transaction(function () use ($project, $validated, $user, $request) {
            if ($request->hasFile('submissions')) {
                $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);
                foreach ($request->file('submissions') as $type => $file) {
                    if ($file === null) continue;
                    if (!in_array($type, ['readiness', 'final'])) continue;

                    $typeLabel = $type === 'readiness' ? 'readiness' : 'final';
                    $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
                    $dir = $project->getStorageDir($type === 'readiness' ? 'readiness_reports' : 'final_reports');

                    $existing = $project->submissions()->where('type', $type)->first();
                    if ($existing && $existing->stored_filename && $existing->stored_filename !== $storedFilename) {
                        $oldPath = storage_path('app/' . $dir . '/' . $existing->stored_filename);
                        if (file_exists($oldPath)) { @unlink($oldPath); }
                    }
                    $file->storeAs($dir, $storedFilename);
                    $path = $dir . '/' . $storedFilename;
                    $project->submissions()->create([
                        'type'              => $type,
                        'file_path'         => $path,
                        'stored_filename'   => $storedFilename,
                        'original_filename' => $file->getClientOriginalName(),
                        'notes'             => $validated['submission_notes'] ?? null,
                        'user_id'           => $user->id,
                    ]);
                }
            }

            // Record the final_added status
            if (!$project->hasStatus(Project::STATUS_FINAL_ADDED)) {
                $project->recordStatus(Project::STATUS_FINAL_ADDED, [
                    'triggered_by' => 'final-report',
                ], $user->id);
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Final report submitted successfully.',
                'redirect' => route('projects.show', $project->id),
            ]);
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Final report submitted successfully.');
    }

    /**
     * AJAX: Save all Project Outcomes (Tab 1).
     * Handles both:
     *   - Scholarly types (journal Q1-Q4, conference, book, etc.) as DOI text inputs
     *   - Contribution types (ip_disclosure, provisional_patent, patent_granted,
     *     open_source_sw, startup) as Yes/No toggles with optional detail textarea
     * All saved to project_outcomes table.
     */
    public function saveOutcomes(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'outcomes'             => 'required|array',
            'outcomes.*.type'      => 'required|string',
            'outcomes.*.detail'    => 'nullable|string|max:500',
            'outcomes.*.submitted' => 'nullable|string|in:Yes,No',
        ]);

        DB::transaction(function () use ($project, $validated, $user) {
            // Get only the types being saved in this request
            $incomingTypes = collect($validated['outcomes'])->pluck('type')->unique()->values()->toArray();

            // Delete existing outcomes only for these specific types (clean re-insert)
            $project->outcomes()->whereIn('type', $incomingTypes)->delete();

            // Insert each outcome
            foreach ($validated['outcomes'] as $outcomeData) {
                $type = $outcomeData['type'];
                $submitted = $outcomeData['submitted'] ?? 'No';
                $detail = $outcomeData['detail'] ?? null;

                // Contribution types (toggle-based) — only save if submitted == 'Yes'
                if (in_array($type, ['ip_disclosure', 'provisional_patent', 'patent_granted', 'open_source_sw', 'startup'])) {
                    if ($submitted === 'Yes') {
                        $project->outcomes()->create([
                            'user_id'     => $user->id,
                            'type'        => $type,
                            'identifier'  => $detail ?? '',
                            'online_date' => null,
                            'verifcation_by_system'   => 'pending',
                            'verifcation_by_reviewer' => 'pending',
                            'score'       => 0,
                        ]);
                    }
                    continue;
                }

                // Scholarly types (text-based DOI) — only save non-empty detail
                if (!empty($detail)) {
                    $project->outcomes()->create([
                        'user_id'     => $user->id,
                        'type'        => $type,
                        'identifier'  => $detail,
                        'online_date' => null,
                        'verifcation_by_system'   => 'pending',
                        'verifcation_by_reviewer' => 'pending',
                        'score'       => 0,
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Outcomes saved successfully.',
        ]);
    }

    /**
     * AJAX: Save a single outcome record (one article / one IP entry at a time).
     * Accepts: type, detail. Creates a new project_outcomes row and returns the
     * created record id so the UI can tag the row for later deletion.
     */
    public function saveSingleOutcome(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'type'   => 'required|string',
            'detail' => 'required|string|max:500',
        ]);

        $type = $validated['type'];
        $detail = $validated['detail'];

        // Scholarly article types that need API verification
        $scholarlyTypes = ['journal_q1', 'journal_q2', 'journal_q3', 'journal_q4', 'conference', 'book', 'edited_book', 'book_chapter'];

        // Try to fetch publication details from API
        $publication = null;
        $isVerified = false;

        if (in_array($type, $scholarlyTypes)) {
            $publication = $this->fetchPublicationFromApi($detail, $project->id, null);
            $isVerified = ($publication !== null);
        }

        // Save outcome regardless of API success
        $outcome = $project->outcomes()->create([
            'user_id'     => $user->id,
            'type'        => $type,
            'identifier'  => $detail,
            'online_date' => $publication && $publication->year ? $publication->year . '-01-01' : null,
            'verifcation_by_system'   => $isVerified ? 'verified' : 'pending',
            'verifcation_by_reviewer' => 'pending',
            'score'       => 0,
        ]);

        // Update publication with outcome_id if API succeeded
        if ($publication) {
            $publication->update(['outcome_id' => $outcome->id]);
        }

        return response()->json([
            'success'     => true,
            'message'     => $isVerified ? 'Record saved and verified.' : 'Record saved. Verification pending.',
            'id'          => $outcome->id,
            'publication' => $publication ? [
                'title'  => $publication->publication_title,
                'journal'=> $publication->journal,
                'year'   => $publication->year,
                'doi'    => $publication->doi,
                'authors'=> $publication->authors,
                'url'    => $publication->url,
            ] : null,
        ]);
    }

    /**
     * AJAX: Verify an outcome via CrossRef API.
     */
    public function verifyOutcome(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'outcome_id' => 'required|integer|exists:project_outcomes,id',
            'doi'        => 'required|string',
        ]);

        $outcome = $project->outcomes()->where('id', $validated['outcome_id'])->first();

        if (!$outcome) {
            return response()->json(['success' => false, 'error' => 'Outcome not found.'], 404);
        }

        // Try to fetch publication details from CrossRef API
        $publication = $this->fetchPublicationFromApi($validated['doi'], $project->id, $outcome->id);

        if ($publication) {
            // Update outcome with verification
            $outcome->update([
                'verifcation_by_system' => 'verified',
                'online_date' => $publication->year ? $publication->year . '-01-01' : null,
            ]);

            // Update or create publication record
            $publication->update(['outcome_id' => $outcome->id]);

            return response()->json([
                'success'     => true,
                'message'     => 'Article verified successfully.',
                'publication' => [
                    'title'  => $publication->publication_title,
                    'journal'=> $publication->journal,
                    'year'   => $publication->year,
                    'doi'    => $publication->doi,
                    'authors'=> $publication->authors,
                    'url'    => $publication->url,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'error'   => 'DOI not found in CrossRef API. Please check the DOI format.',
        ]);
    }

    /**
     * Fetch publication details from CrossRef API and store in project_publications.
     */
    private function fetchPublicationFromApi($doi, $projectId, $outcomeId)
    {
        try {
            // Validate DOI format (must start with 10.)
            if (!preg_match('/^10\.\d{4,}\/.+/', $doi)) {
                \Log::info('Invalid DOI format: ' . $doi);
                return null;
            }

            $client = new \GuzzleHttp\Client([
                'verify'   => config('services.crossref_api.verify_ssl', false),
                'timeout'  => 15,
                'headers'  => [
                    'User-Agent' => 'LRTS-System/1.0 (mailto:admin@university.edu)',
                ],
            ]);

            $url = config('services.crossref_api.url', 'https://api.crossref.org/works/') . $doi;
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();
                $res = json_decode($body, true);
                $message = $res['message'] ?? [];

                $title = $message['title'][0] ?? '';
                $journal = $message['publisher'] ?? ($message['container-title'][0] ?? '');
                $type = $message['type'] ?? '';
                $pubUrl = $message['URL'] ?? '';
                
                // Extract year from published date
                $year = null;
                if (isset($message['published']['date-parts'][0][0])) {
                    $year = $message['published']['date-parts'][0][0];
                } elseif (isset($message['indexed']['date-time'])) {
                    $year = substr($message['indexed']['date-time'], 0, 4);
                }

                // Extract authors
                $authors = '';
                if (isset($message['author']) && is_array($message['author'])) {
                    $authorParts = [];
                    foreach ($message['author'] as $author) {
                        $given = $author['given'] ?? '';
                        $family = $author['family'] ?? '';
                        $authorParts[] = trim($given . ' ' . $family);
                    }
                    $authors = implode(', ', $authorParts);
                }

                // Store in project_publications
                $publication = ProjectPublication::create([
                    'project_id'        => $projectId,
                    'outcome_id'        => $outcomeId,
                    'authors'           => $authors,
                    'publication_title' => $title,
                    'journal'           => $journal,
                    'year'              => $year,
                    'doi'               => $doi,
                    'url'               => $pubUrl,
                    'status'            => 'published',
                ]);

                \Log::info('CrossRef API success for DOI: ' . $doi . ' - Title: ' . $title);
                return $publication;
            }
        } catch (Throwable $e) {
            \Log::warning('CrossRef API failed for DOI: ' . $doi . ' - ' . $e->getMessage());
        }

        return null;
    }

    /**
     * AJAX: Delete a single outcome record by id.
     */
    public function deleteOutcome(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|integer|exists:project_outcomes,id',
        ]);

        $outcome = $project->outcomes()->where('id', $validated['id'])->first();
        if (!$outcome) {
            return response()->json(['success' => false, 'error' => 'Record not found.'], 404);
        }

        $outcome->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.',
        ]);
    }

    /**
     * AJAX: Save a single student row (one at a time).
     */
    public function saveSingleStudent(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'type'   => 'required|string|in:UG,masters,PhD',
            'std_id' => 'required|string|max:100',
            'days'   => 'nullable|integer|min:0|max:365',
        ]);

        // Save student record
        $student = $project->students()->create([
            'user_id' => $user->id,
            'type'    => $validated['type'],
            'std_id'  => $validated['std_id'],
            'days'    => $validated['days'] ?? 0,
            'score'   => 0,
        ]);

        // Fetch student details from QU SIS API
        $studentDetails = ProjectStudentDetail::saveFromApi($student->id, $validated['std_id']);

        $responseData = [
            'success' => true,
            'message' => 'Student saved.',
            'id'      => $student->id,
        ];

        // Include student details if API was successful
        if ($studentDetails) {
            $responseData['student'] = [
                'full_name' => $studentDetails->full_name,
                'first_name' => $studentDetails->first_name,
                'last_name' => $studentDetails->last_name,
                'student_status' => $studentDetails->student_status,
                'major' => $studentDetails->major,
                'college' => $studentDetails->college,
                'std_program' => $studentDetails->std_program,
                'std_level' => $studentDetails->std_level,
            ];
        }

        return response()->json($responseData);
    }

    /**
     * AJAX: Delete a single student row by id.
     */
    public function deleteStudent(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|integer|exists:project_students,id',
        ]);

        $student = $project->students()->where('id', $validated['id'])->first();
        if (!$student) {
            return response()->json(['success' => false, 'error' => 'Record not found.'], 404);
        }
        $student->delete();

        return response()->json(['success' => true, 'message' => 'Student deleted.']);
    }

    /**
     * AJAX: Retry student verification from SIS API.
     */
    public function retryStudentVerification(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'required|integer|exists:project_students,id',
            'std_id'     => 'required|string',
        ]);

        $student = $project->students()->where('id', $validated['student_id'])->first();

        if (!$student) {
            return response()->json(['success' => false, 'error' => 'Student not found.'], 404);
        }

        // Try to fetch from API again
        $studentDetails = ProjectStudentDetail::saveFromApi($student->id, $validated['std_id']);

        if ($studentDetails) {
            return response()->json([
                'success' => true,
                'message' => 'Student verified successfully.',
                'student' => [
                    'full_name' => $studentDetails->full_name,
                    'first_name' => $studentDetails->first_name,
                    'last_name' => $studentDetails->last_name,
                    'student_status' => $studentDetails->student_status,
                    'major' => $studentDetails->major,
                    'college' => $studentDetails->college,
                    'std_program' => $studentDetails->std_program,
                    'std_level' => $studentDetails->std_level,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Student not found in SIS API. Please check the Student ID.',
        ]);
    }

    /**
     * AJAX: Save a single hired researcher row (one at a time).
     */
    public function saveSingleResearcher(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
        ]);

        $researcher = $project->researchers()->create([
            'name'     => $validated['name'],
            'category' => $validated['category'] ?? 'RA',
            'days'     => 0,
            'score'    => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Researcher saved.',
            'id'      => $researcher->id,
        ]);
    }

    /**
     * AJAX: Delete a single researcher row by id.
     */
    public function deleteResearcher(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'id' => 'required|integer|exists:project_researchers,id',
        ]);

        $researcher = $project->researchers()->where('id', $validated['id'])->first();
        if (!$researcher) {
            return response()->json(['success' => false, 'error' => 'Record not found.'], 404);
        }
        $researcher->delete();

        return response()->json(['success' => true, 'message' => 'Researcher deleted.']);
    }

    /**
     * AJAX: Save a single toggle outcome (cross_college / research_awards).
     */
    public function saveToggle(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'type'    => 'required|string|in:cross_college,research_awards',
            'value'   => 'required|string|in:Yes,No',
            'detail'  => 'nullable|string|max:500',
        ]);

        $type = $validated['type'];
        $value = $validated['value'];

        $project->outcomes()->where('type', $type)->delete();
        if ($value === 'Yes') {
            $project->outcomes()->create([
                'user_id'     => $user->id,
                'type'        => $type,
                'identifier'  => $validated['detail'] ?? '',
                'online_date' => null,
                'verifcation_by_system'   => 'pending',
                'verifcation_by_reviewer' => 'pending',
                'score'       => 0,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Saved.']);
    }

    /**
     * AJAX: Save only the Personnel data (Tab 2 — UG, Masters, PhD
     * + Hired Researchers + Cross College Participation + Research Awards).
     * Students go to project_students table; researchers/outcomes go to
     * project_outcomes (types: hired_researcher, cross_college, research_awards).
     */
    public function saveStudents(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            // Student rows
            'students'                   => 'nullable|array',
            'students.*.type'            => 'required|string|in:UG,masters,PhD',
            'students.*.std_id'          => 'nullable|string|max:100',
            'students.*.days'            => 'nullable|integer|min:0|max:365',
            // Hired Researchers (via project_researchers table)
            'researchers'                => 'nullable|array',
            'researchers.*.name'         => 'nullable|string|max:255',
            'researchers.*.category'     => 'nullable|string|max:100',
            'researchers.*.days'         => 'nullable|integer|min:0|max:365',
            // Cross-College Participation (Yes/No toggle)
            'cross_college'              => 'nullable|string|in:Yes,No',
            'cross_college_detail'       => 'nullable|string|max:500',
            // Research Awards (Yes/No toggle)
            'research_awards'            => 'nullable|string|in:Yes,No',
            'research_awards_detail'     => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($project, $validated, $user) {
            // ── 1. Save Students ──────────────────────────────────────────
            $project->students()->delete();
            if (!empty($validated['students'])) {
                foreach ($validated['students'] as $studentData) {
                    $project->students()->create([
                        'user_id' => $user->id,
                        'type'    => $studentData['type'],
                        'std_id'  => $studentData['std_id'] ?? null,
                        'days'    => $studentData['days'] ?? 0,
                        'score'   => 0,
                    ]);
                }
            }

            // ── 2. Save Hired Researchers (project_researchers table) ────
            $project->researchers()->delete();
            if (!empty($validated['researchers'])) {
                foreach ($validated['researchers'] as $researcherData) {
                    if (!empty($researcherData['name'])) {
                        $project->researchers()->create([
                            'name'     => $researcherData['name'],
                            'category' => $researcherData['category'] ?? 'RA',
                            'days'     => $researcherData['days'] ?? 0,
                            'score'    => 0,
                        ]);
                    }
                }
            }

            // ── 3. Save Cross-College Participation (project_outcomes) ───
            $project->outcomes()->where('type', 'cross_college')->delete();
            if (($validated['cross_college'] ?? 'No') === 'Yes') {
                $project->outcomes()->create([
                    'user_id'     => $user->id,
                    'type'        => 'cross_college',
                    'identifier'  => $validated['cross_college_detail'] ?? '',
                    'online_date' => null,
                    'verifcation_by_system'   => 'pending',
                    'verifcation_by_reviewer' => 'pending',
                    'score'       => 0,
                ]);
            }

            // ── 4. Save Research Awards (project_outcomes) ───────────────
            $project->outcomes()->where('type', 'research_awards')->delete();
            if (($validated['research_awards'] ?? 'No') === 'Yes') {
                $project->outcomes()->create([
                    'user_id'     => $user->id,
                    'type'        => 'research_awards',
                    'identifier'  => $validated['research_awards_detail'] ?? '',
                    'online_date' => null,
                    'verifcation_by_system'   => 'pending',
                    'verifcation_by_reviewer' => 'pending',
                    'score'       => 0,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Personnel data saved successfully.',
        ]);
    }

    /**
     * AJAX: Save only the Contributions (Tab 3 — IP Disclosure, Patents,
     * Open Source Software, Start-Up Created).
     */
    public function saveContributions(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $validated = $request->validate([
            'contributions'              => 'nullable|array',
            'contributions.*.type'       => 'nullable|string|max:100',
            'contributions.*.submitted'  => 'nullable|string|in:Yes,No',
            'contributions.*.detail'     => 'nullable|string|max:500',
        ]);

        $allowedTypes = [
            'ip_disclosure',
            'provisional_patent',
            'patent_granted',
            'open_source_sw',
            'startup',
        ];

        DB::transaction(function () use ($project, $validated, $user, $allowedTypes) {
            foreach ($allowedTypes as $type) {
                // Delete existing records of this type
                $project->contributions()->where('type', $type)->delete();

                // Find matching contribution in input
                $contribution = collect($validated['contributions'] ?? [])
                    ->firstWhere('type', $type);

                if (!$contribution || ($contribution['submitted'] ?? 'No') !== 'Yes') {
                    continue;
                }

                $project->contributions()->create([
                    'user_id' => $user->id,
                    'type'    => $type,
                    'detail'  => $contribution['detail'] ?? '',
                    'score'   => 0,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Contributions saved successfully.',
        ]);
    }

}
