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
        return $this->updateProgress($id);
    }

    /**
     * Show the unified Update Progress page.
     * Combines progress and final report functionality into a single page.
     */
    public function updateProgress($id)
    {
        $project = Project::with([
            'program', 'grant', 'lpi', 'latestStatus',
            'commitments', 'pillars', 'colleges',
        ])->findOrFail($id);

        // Only allow LPI / admin to add progress
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Please login to continue.');
        }
        $role = $user->activeRole();
        if ($role !== 'LPI') {
            abort(403, 'You are not authorized to add progress reports.');
        }

    // Ownership: unclaimed projects are open to the registering LPI;
    // once claimed, only the project's own LPI may view/update it.
    if ($project->lpi_id !== null && $project->lpi_id !== $user->id) {
        abort(403, 'You are not the LPI for this project.');
    }

    // Student-grant projects use a dedicated one-shot progress form
    // (no PDF progress report upload, no narrative/outcome tabs).
    if ($project->isStudentProject()) {
        $ethicalDocs = $project->submissions()
            ->where('type', 'ethical')->orderBy('id')->get();
        return view('projects.add-progress-student', [
            'project'          => $project,
            'projectStudents'  => $project->students()->with('details')->orderBy('id')->get(),
            'ethicalDocs'      => $ethicalDocs,
            'isLocked'         => $project->student_project_draft === 'save',
            'deadlines'        => [
                'prog_rpt_deadline'  => $project->program->prog_rpt_deadline ?? null,
                'prog_rpt2_deadline' => $project->program->prog_rpt2_deadline ?? null,
                'final_rpt_deadline' => $project->program->final_rpt_deadline ?? null,
            ],
        ]);
    }

    $data = $this->loadFormData($project);
        $data['mode'] = 'update';

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
        $projectStudents = $project->students()->with('details')->orderBy('type')->orderBy('id')->get();

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
                'label' => 'Progress Report',
            ],
            'progress2' => [
                'original' => $program ? $program->prog_rpt2_deadline : null,
                'label' => 'Progress Report 2',
            ],
            'readiness' => [
                'original' => $program ? $program->prog_rpt2_deadline : null,
                'label' => 'Readiness Report',
            ],
            'final' => [
                'original' => $program ? $program->final_rpt_deadline : null,
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
        $this->authorizeLpi($project);

        $user = Auth::user();

        // The progress form is gated by the progress report window.
        if ($user->activeRole() === 'LPI' && $this->isTypeLocked($project, 'progress')) {
            return response()->json([
                'success' => false,
                'error'   => 'The progress report editing window has closed.',
            ], 423);
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
                        'verifcation_by_system'    => 'pending',
                        'verifcation_by_reviewer'  => 'pending',
                        'score'       => 0,
                    ]);
                }
            }

            // Save file submissions (progress report only — readiness/final are in final step)
            if ($request->hasFile('submissions')) {
                $oldId = $project->getFileSafeOldProjectId();
                foreach ($request->file('submissions') as $type => $file) {
                    if ($file === null) continue;
                    // Only save progress type files here
                    if ($type !== 'progress' && $type !== 'progress_report') continue;
                    $typeLabel = 'progress';
                    $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
                    $dir = $project->getStorageDir('progress_reports');
                    $file->storeAs($dir, $storedFilename);
                    $path = $dir . '/' . $storedFilename;

                    // Replace: remove ALL existing DB rows of this type so no
                    // duplicate submission records pile up (rows first(), the
                    // row(s) now reference the freshly overwritten file).
                    $project->submissions()->where('type', 'progress')->delete();
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

            // If this progress report was previously graded, archive the last
            // submitted grading and reset the row so the reviewer's form
            // re-opens blank for the new version. Scoped to report_type='progress'
            // so a rejected final/progress2 grading is not reopened by mistake.
            $this->archiveAndResetGrading($project->id, 'progress');
        });

        // Confirm to the LPI that their progress report was submitted (automatic).
        $this->notifyLpiProgressUpdated($project->fresh(), $user);

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
     * Student-grant progress form save (one-shot page, no PDF progress report).
     * Persists: per-student nationality, publications list, spending +
     * spending_detail vs the allocated budget, student engagement narrative.
     *
     * action=draft — saves silently, the form stays editable
     * action=submit — same save + sets student_project_draft='save', which
     *                 permanently LOCKS the form (legacy behavior); the Admin
     *                 must reset the flag in the DB to reopen it.
     */
    public function saveStudentProgress(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorizeLpi($project);

        if (!$project->isStudentProject()) {
            return response()->json([
                'success' => false,
                'error'   => 'This form applies to student-grant projects only.',
            ], 422);
        }

        $alreadyLocked = $project->student_project_draft === 'save';
        if ($alreadyLocked) {
            return response()->json([
                'success' => false,
                'error'   => 'This form has already been submitted and is locked. Please contact the admin for corrections.',
            ], 423);
        }

        $validated = $request->validate([
            'action'             => 'required|in:draft,submit',
            'students'           => 'nullable|array',
            'students.*.id'      => 'nullable|integer',
            'students.*.category' => 'required_with:students|in:Qatari,Non-Qatari',
            'publications'       => 'nullable|string|max:5000',
            'spending'           => 'nullable|numeric|min:0',
            'spending_details'   => 'nullable|string|max:2000',
            'student_engagement' => 'nullable|string|max:5000',
        ]);

        $isSubmitting = $validated['action'] === 'submit';

        if ($isSubmitting && (trim((string) ($validated['student_engagement'] ?? '')) === ''
            || !isset($validated['spending']))) {
            return response()->json([
                'success' => false,
                'error'   => 'Student engagement and spending are required to submit the form.',
            ], 422);
        }

        DB::transaction(function () use ($project, $validated, $isSubmitting) {
            // Per-student nationality (legacy stored lowercase; DB today holds
            // title-case 'Qatari' / 'Non-Qatari' — keep title-case)
            foreach (($validated['students'] ?? []) as $student) {
                if (!empty($student['id']) && isset($student['category'])) {
                    DB::table('project_students')
                        ->where('id', $student['id'])
                        ->where('project_id', $project->id)
                        ->update(['nationality' => $student['category']]);
                }
            }

            $project->update([
                'publications'         => $validated['publications'] ?? null,
                'spending'             => $validated['spending'] ?? null,
                'spending_detail'      => $validated['spending_details'] ?? null,
                'student_engagement'   => $validated['student_engagement'] ?? null,
                'student_project_draft' => $isSubmitting ? 'save' : $project->student_project_draft,
            ]);
        });

        return response()->json([
            'success'    => true,
            'locked'     => $isSubmitting,
            'message'    => $isSubmitting
                ? 'Progress submitted successfully. The form is now locked.'
                : 'Progress saved as draft (form remains editable).',
        ]);
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
        $this->authorizeLpi($project);
        $user = auth()->user();

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
            'type' => 'required|in:progress,progress2,readiness,final,ethical',
        ]);

        $file = $request->file('file');
        $type = $request->input('type');

        // Server-side deadline/lock enforcement: LPI cannot bypass the UI.
        // Ethical approvals are part of the student form and are not
        // deadline-gated.
        if ($type !== 'ethical' && $user->activeRole() === 'LPI' && $this->isTypeLocked($project, $type)) {
            return response()->json([
                'success' => false,
                'error'   => 'The upload window for this report has closed. Please contact the admin if you need to resubmit.',
            ], 423);
        }

        // Map to submission type and version
        $oldId = $project->getFileSafeOldProjectId();

        if ($type === 'ethical') {
            // Ethical approval: MULTIPLE files allowed — each upload appends a
            // numbered row; nothing is replaced/deleted (deletion is separate).
            $submissionType = 'ethical';
            $typeLabel = 'ethical';
            $dir = $project->getStorageDir('ethical_approvals');
            $next = $project->submissions()->where('type', 'ethical')->count() + 1;
            $storedFilename = $oldId . '_' . $typeLabel . '_' . $next . '.pdf';
            $version = 1;

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

            $downloadUrl = route('serveFile2', ['type' => $submissionType, 'id' => $project->id, 'submission_id' => $submission->id]);

            return response()->json([
                'success'    => true,
                'type'       => $submissionType,
                'version'    => $version,
                'submission' => [
                    'id'                => $submission->id,
                    'file_path'         => $submission->file_path,
                    'original_filename' => $submission->original_filename,
                    'stored_filename'   => $submission->stored_filename,
                    'version'           => $version,
                    'created_at'        => $submission->created_at->toDateTimeString(),
                    'download_url'      => $downloadUrl,
                ],
                'link_html'  => '<a href="' . $downloadUrl . '" target="_blank" style="color:var(--brand-500);font-size:12px;font-weight:500;">'
                              . e($submission->stored_filename) . '</a>',
                'message'    => 'Report uploaded successfully.',
            ]);
        }

        if ($type === 'readiness') {
            $submissionType = 'readiness';
            $typeLabel = 'readiness';
            $dir = $project->getStorageDir('readiness_reports');
        } elseif ($type === 'final') {
            $submissionType = 'final';
            $typeLabel = 'final';
            $dir = $project->getStorageDir('final_reports');
        } elseif ($type === 'progress2') {
            $submissionType = 'progress2';
            $typeLabel = 'progress2';
            $dir = $project->getStorageDir('progress_reports');
        } else {
            $submissionType = 'progress';
            $typeLabel = 'progress';
            $dir = $project->getStorageDir('progress_reports');
        }

        if ($type === 'readiness') {
            // Readiness: replace existing (single file per type)
            $existingRows = $project->submissions()->where('type', $submissionType)->get();
            foreach ($existingRows as $existing) {
                $oldPath = storage_path('app/' . $existing->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existing->delete();
            }
            $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
            $version = 1;
        } elseif ($type === 'final' && ($project->hasStatus(Project::STATUS_FINAL_REJECTED) || $project->hasStatus(Project::STATUS_FINAL_REJ_REVIEWED))) {
            // Admin resubmission: keep v1, write/overwrite v2 only
            $existingV2 = $project->submissions()->where('type', $submissionType)->where('version', 2)->first();
            if ($existingV2) {
                $oldPath = storage_path('app/' . $existingV2->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existingV2->delete();
            }
            $storedFilename = $oldId . '_' . $typeLabel . '_v2.pdf';
            $version = 2;
        } elseif ($type === 'final') {
            // Final: replace existing (single file per type)
            $existingRows = $project->submissions()->where('type', $submissionType)->get();
            foreach ($existingRows as $existing) {
                $oldPath = storage_path('app/' . $existing->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existing->delete();
            }
            $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
            $version = 1;
        } elseif ($type === 'progress2' && ($project->hasStatus(Project::STATUS_PROGRESS2_REJECTED)
                   || $project->hasStatus(Project::STATUS_PROGRESS2_REJ_REVIEWED)
                   || \App\Models\ProgressReportGrading::where('project_id', $project->id)
                        ->where('report_type', 'progress2')
                        ->where('publish', 'rejected')
                        ->exists())) {
            // Progress 2 admin resubmission: keep v1, write/overwrite v2 only
            $existingV2 = $project->submissions()->where('type', $submissionType)->where('version', 2)->first();
            if ($existingV2) {
                $oldPath = storage_path('app/' . $existingV2->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existingV2->delete();
            }
            $storedFilename = $oldId . '_' . $typeLabel . '_v2.pdf';
            $version = 2;
        } elseif ($type === 'progress2') {
            // Progress 2 normal upload: replace existing (single file)
            $existingRows = $project->submissions()->where('type', $submissionType)->get();
            foreach ($existingRows as $existing) {
                $oldPath = storage_path('app/' . $existing->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existing->delete();
            }
            $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
            $version = 1;
        } elseif ($project->hasStatus(Project::STATUS_PROGRESS_REJECTED)
                   || $project->hasStatus(Project::STATUS_PROGRESS_REJ_REVIEWED)
                   || \App\Models\ProgressReportGrading::where('project_id', $project->id)
                        ->where('report_type', 'progress')
                        ->where('publish', 'rejected')->exists()) {
            // Admin resubmission: keep v1, write/overwrite v2 only
            $existingV2 = $project->submissions()->where('type', $submissionType)->where('version', 2)->first();
            if ($existingV2) {
                $oldPath = storage_path('app/' . $existingV2->file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
                $existingV2->delete();
            }
            $storedFilename = $oldId . '_' . $typeLabel . '_v2.pdf';
            $version = 2;
        } else {
            // Normal upload: replace existing (single file per type)
            $existingRows = $project->submissions()->where('type', $submissionType)->get();
            foreach ($existingRows as $existing) {
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

        // Resubmission (v2): archive the last submitted grading and re-open
        // the reviewer's form blank for the new version. A rejected grade
        // would otherwise keep the form read-only forever.
        if ($version === 2) {
            if ($submissionType === 'final' || $submissionType === 'progress' || $submissionType === 'progress2') {
                $this->archiveAndResetGrading($project->id, $submissionType);
            }
        }

        // Record the progress2_added status so the extended report tracks its
        // own workflow lifecycle (mirrors progress_added for progress report 1).
        if ($submissionType === 'progress2') {
            $project->recordStatus(Project::STATUS_PROGRESS2_ADDED, [
                'triggered_by' => 'progress2',
            ], $user->id);

            // Confirm to the LPI that progress report 2 was submitted (automatic).
            $this->notifyLpiProgressUpdated($project->fresh(), $user);
        }

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
        $this->authorizeLpi($project);

        $request->validate([
            'submission_id' => 'required|exists:project_submissions,id',
        ]);

        $submission = $project->submissions()
            ->where('id', $request->input('submission_id'))
            ->firstOrFail();

        // Server-side delete rules: v2 only while a resubmission is open;
        // v1 only before the deadline passes. Otherwise the file is locked.
        $type = $submission->type;
        $canDelete = false;
        if ($type === 'ethical') {
            // Ethical approvals (student form): deletable until the form is
            // locked by submit — files can be replaced by re-upload anyway.
            $canDelete = $project->student_project_draft !== 'save';
        } elseif ($type === 'readiness') {
            // Readiness: single-file semantics (no v2) — deletable only while
            // the reporting window is open, like every other v1 file.
            $deadline = $this->effectiveDeadline($project, $type);
            $canDelete = $deadline !== null && now()->lessThan($deadline);
        } elseif ($submission->version == 2 && $this->isResubmitRequested($project, $type)) {
            $canDelete = true;
        } elseif ($submission->version == 1) {
            $deadline = $this->effectiveDeadline($project, $type);
            // Null deadline = treated as passed → v1 not deletable.
            $canDelete = $deadline !== null && now()->lessThan($deadline);
        }
        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'error'   => 'This file can no longer be deleted.',
            ], 423);
        }

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
     * Archive the last submitted grading into previous_grading and reset the
     * row so the reviewer re-grades the newly uploaded version from a blank
     * form. The archive powers the read-only "Previous Grading" panel shown
     * under the superseded PDF on the grading page.
     *
     * Draft rows (never submitted) keep their values and are only reset.
     */
    protected function archiveAndResetGrading(int $projectId, string $reportType): void
    {
        // Ethical/Other rejections expect no new report version — the reviewer
        // edits the existing grading record instead, so never archive/reset it.
        $project = Project::find($projectId);
        if ($project) {
            $rejectedStatus = $reportType === 'final'
                ? Project::STATUS_FINAL_REJECTED
                : ($reportType === 'progress2' ? Project::STATUS_PROGRESS2_REJECTED : Project::STATUS_PROGRESS_REJECTED);
            $lastRejection = $project->statusHistories()
                ->where('status', $rejectedStatus)
                ->latest()->first();
            if ($lastRejection && in_array($lastRejection->metadata['rejection_type'] ?? 'report', ['ethical', 'other'], true)) {
                return;
            }
        }

        if ($reportType === 'final') {
            $row = \App\Models\FinalReportGrading::where('project_id', $projectId)->first();
            if (!$row) {
                return;
            }

            if (in_array($row->publish, ['accepted', 'rejected'], true)) {
                $row->previous_grading = $row->only([
                    'gradeA', 'scoreA', 'commentA',
                    'gradeB', 'scoreB', 'commentB',
                    'gradeC', 'scoreC', 'commentC',
                    'gradeD', 'commentD', 'total',
                    'publish', 'isAccepted', 'user_id',
                ]);
                foreach (['gradeA', 'scoreA', 'commentA', 'gradeB', 'scoreB', 'commentB',
                          'gradeC', 'scoreC', 'commentC', 'gradeD', 'commentD', 'total'] as $field) {
                    $row->{$field} = null;
                }
            }

            $row->publish = 'pending';
            $row->isAccepted = 0;
            $row->save();
            return;
        }

        $row = \App\Models\ProgressReportGrading::where('project_id', $projectId)
            ->where('report_type', $reportType)
            ->first();
        if (!$row) {
            return;
        }

        if (in_array($row->publish, ['accepted', 'rejected'], true)) {
            $row->previous_grading = $row->only([
                'achievementsRating', 'publicationsRating', 'studentsRating', 'budgetRating',
                'achievementsComments', 'publicationsComments', 'studentsComments', 'budgetComments',
                'ethical', 'analysis', 'comments', 'recommendation', 'path',
                'publish', 'isAccepted', 'user_id',
            ]);
            foreach (['achievementsRating', 'publicationsRating', 'studentsRating', 'budgetRating',
                      'achievementsComments', 'publicationsComments', 'studentsComments', 'budgetComments',
                      'ethical', 'analysis', 'comments', 'recommendation', 'path'] as $field) {
                $row->{$field} = null;
            }
        }

        $row->publish = 'pending';
        $row->isAccepted = 0;
        $row->save();
    }

    /**
     * Resolve the EFFECTIVE deadline for a report type.
     * Extended deadline wins if set; otherwise original deadline.
     * Returns null when no deadline is configured (treated as already passed).
     */
    protected function effectiveDeadline(Project $project, string $type)
    {
        $program = $project->program;
        if (!$program) {
            return null;
        }

        if ($type === 'progress') {
            return $program->prog_rpt_deadline;
        }
        if ($type === 'progress2') {
            return $program->prog_rpt2_deadline;
        }
        if ($type === 'readiness') {
            return $program->prog_rpt2_deadline;
        }
        if ($type === 'final') {
            return $program->final_rpt_deadline;
        }

        return null;
    }

    /**
     * True when the LPI has an active resubmission request for a report type
     * (admin reviewed a rejection and sent it back — stays open until graded).
     */
    protected function isResubmitRequested(Project $project, string $type): bool
    {
        if ($type === 'final') {
            $last = $project->statusHistories()->where('status', Project::STATUS_FINAL_REJ_REVIEWED)->latest()->first();
            return $last
                && ($last->metadata['action'] ?? null) === 'send_to_lpi'
                && !$project->statusHistories()
                    ->whereIn('status', [Project::STATUS_GRADED, Project::STATUS_FINAL_REJECTED])
                    ->where('created_at', '>', $last->created_at)
                    ->exists();
        }

        if ($type === 'progress2') {
            $last = $project->statusHistories()->where('status', Project::STATUS_PROGRESS2_REJ_REVIEWED)->latest()->first();
            return $last
                && ($last->metadata['action'] ?? null) === 'send_to_lpi'
                && !$project->statusHistories()
                    ->whereIn('status', [Project::STATUS_PROGRESS2_REVIEWED, Project::STATUS_PROGRESS2_REJECTED])
                    ->where('created_at', '>', $last->created_at)
                    ->exists();
        }

        $last = $project->statusHistories()->where('status', Project::STATUS_PROGRESS_REJ_REVIEWED)->latest()->first();
        return $last
            && ($last->metadata['action'] ?? null) === 'send_to_lpi'
            && !$project->statusHistories()
                ->whereIn('status', [Project::STATUS_PROGRESS_REVIEWED, Project::STATUS_PROGRESS_REJECTED])
                ->where('created_at', '>', $last->created_at)
                ->exists();
    }

    /**
     * True when a report type is locked server-side.
     * A null deadline is treated as already passed (always locked).
     */
    /**
     * True while the latest rejection for a report type is an Ethical/Other
     * rejection that has not yet been resolved — no new report version is
     * expected, so the upload stays locked (the reviewer edits the grading
     * instead of receiving a v2).
     */
    protected function isRejectionHold(Project $project, string $type): bool
    {
        if ($type === 'final') {
            $rejectionStatus = Project::STATUS_FINAL_REJECTED;
            $laterStatuses = [Project::STATUS_GRADED, Project::STATUS_FINAL_REJECTED];
        } elseif ($type === 'progress2') {
            $rejectionStatus = Project::STATUS_PROGRESS2_REJECTED;
            $laterStatuses = [Project::STATUS_PROGRESS2_REVIEWED, Project::STATUS_PROGRESS2_REJECTED];
        } else {
            $rejectionStatus = Project::STATUS_PROGRESS_REJECTED;
            $laterStatuses = [Project::STATUS_PROGRESS_REVIEWED, Project::STATUS_PROGRESS_REJECTED];
        }

        $last = $project->statusHistories()->where('status', $rejectionStatus)->latest()->first();
        if (!$last || !in_array($last->metadata['rejection_type'] ?? 'report', ['ethical', 'other'], true)) {
            return false;
        }

        return !$project->statusHistories()
            ->whereIn('status', $laterStatuses)
            ->where('created_at', '>', $last->created_at)
            ->exists();
    }

    protected function isTypeLocked(Project $project, string $type): bool
    {
        $deadline = $this->effectiveDeadline($project, $type);
        // Null deadline is treated as already passed (always locked).
        $deadlinePassed = $deadline ? now()->greaterThan($deadline) : true;

        if ($type === 'final' || $type === 'readiness') {
            return $this->isRejectionHold($project, 'final')
                || (!$this->isResubmitRequested($project, 'final') && $deadlinePassed);
        }

        if ($type === 'progress2') {
            return $this->isRejectionHold($project, 'progress2')
                || (!$this->isResubmitRequested($project, 'progress2') && $deadlinePassed);
        }

        // progress: gated ONLY by the deadline — the LPI may submit and overwrite
        // freely until the deadline arrives, then the reviewer takes over.
        // Ethical/Other rejections lock the upload regardless of the deadline.
        return $this->isRejectionHold($project, 'progress')
            || (!$this->isResubmitRequested($project, 'progress') && $deadlinePassed);
    }

    /**
     * True when the "project data" (outcomes, students, researchers, contributions)
     * is frozen. Per the workflow, these are coupled with the FINAL report deadline,
     * not the progress deadline. A final resubmission re-opens ONLY the final-report
     * upload, never the project data, so this ignores resubmission requests.
     */
    protected function isDataLocked(Project $project): bool
    {
        $deadline = $this->effectiveDeadline($project, 'final');
        // Null deadline is treated as already passed (always locked).
        $deadlinePassed = $deadline ? now()->greaterThan($deadline) : true;
        return $deadlinePassed;
    }

    /**
     * Abort with a 423 (locked) JSON response when the LPI tries to edit
     * project data after the final report window has closed.
     * Admins are always allowed.
     */
    protected function enforceDataOpen(Project $project)
    {
        $user = Auth::user();
        if ($user->activeRole() !== 'LPI') {
            return;
        }
        if ($this->isDataLocked($project)) {
            abort(423, 'The editing window for project data has closed. Please contact the admin if you need to make changes.');
        }
    }

    /**
     * Abort unless the current user is an admin or the project's own LPI.
     */
    protected function authorizeLpi(Project $project): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Please login to continue.');
        }

        $role = $user->activeRole();
        if ($role !== 'LPI') {
            abort(403, 'You are not authorized to perform this action.');
        }

        if ($project->lpi_id !== null && $project->lpi_id !== $user->id) {
            abort(403, 'You are not the LPI for this project.');
        }
    }

    /**
     * Show the full-page "Add Final Report" form.
     * Reuses the unified add-progress page — the final report section is editable
     * and the progress sections are readonly.
     */
    public function addFinalReport($id)
    {
        return $this->updateProgress($id);
    }

    /**
     * Save the final step: upload readiness + final report files and
     * record the final_added status.
     */
    public function saveFinalReport(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $this->authorizeLpi($project);

        $user = Auth::user();

        // The final form is gated by the final report window.
        if ($user->activeRole() === 'LPI' && $this->isTypeLocked($project, 'final')) {
            return response()->json([
                'success' => false,
                'error'   => 'The final report editing window has closed.',
            ], 423);
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
                $oldId = $project->getFileSafeOldProjectId();
                foreach ($request->file('submissions') as $type => $file) {
                    if ($file === null) continue;
                    if (!in_array($type, ['readiness', 'final'])) continue;

                    $typeLabel = $type === 'readiness' ? 'readiness' : 'final';
                    $storedFilename = $oldId . '_' . $typeLabel . '.pdf';
                    $dir = $project->getStorageDir($type === 'readiness' ? 'readiness_reports' : 'final_reports');

                    $file->storeAs($dir, $storedFilename);
                    $path = $dir . '/' . $storedFilename;

                    // Replace: remove ALL existing DB rows of this type so no
                    // duplicate submission records pile up.
                    $project->submissions()->where('type', $type)->delete();
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

            // Record the final_added status (always record on save, even if files already uploaded)
            $project->recordStatus(Project::STATUS_FINAL_ADDED, [
                'triggered_by' => 'final-report',
            ], $user->id);

            // Re-open the reviewer's grading for a resubmitted final report
            // (archive the rejected grading so it stays visible read-only under
            // the superseded PDF, and open the form blank for the new version).
            if ($project->hasStatus(Project::STATUS_FINAL_REJECTED) || $project->hasStatus(Project::STATUS_FINAL_REJ_REVIEWED)) {
                $this->archiveAndResetGrading($project->id, 'final');
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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

        DB::transaction(function () use ($project, $validated, $user, $type, $detail, $publication, $isVerified, &$outcome) {
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
        });

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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);

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

                // Store in project_publications — reuse a previously fetched
                // row for the same project+DOI so repeat verifications do not
                // create duplicate publication records.
                $publication = ProjectPublication::where('project_id', $projectId)
                    ->where('doi', $doi)
                    ->first();
                $publicationData = [
                    'project_id'        => $projectId,
                    'authors'           => $authors,
                    'publication_title' => $title,
                    'journal'           => $journal,
                    'year'              => $year,
                    'doi'               => $doi,
                    'url'               => $pubUrl,
                    'status'            => 'published',
                ];
                if ($publication) {
                    // Preserve the current outcome linkage on re-verification
                    unset($publicationData['project_id']);
                    $publicationData['outcome_id'] = $outcomeId ?? $publication->outcome_id;
                    $publication->update($publicationData);
                } else {
                    $publication = ProjectPublication::create($publicationData);
                }

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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);

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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
        $user = Auth::user();

        $validated = $request->validate([
            // Student rows
            'students'                   => 'nullable|array',
            'students.*.type'            => 'required|string|in:UG,masters,PhD',
            'students.*.student_id'      => 'nullable|string|max:100',
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
                    // std_id (the QU student id) is NOT NULL in the schema —
                    // only save rows that actually carry an id.
                    $stdId = $studentData['student_id'] ?? null;
                    if (empty($stdId)) {
                        continue;
                    }
                    $project->students()->create([
                        'user_id' => $user->id,
                        'type'    => $studentData['type'],
                        'std_id'  => $stdId,
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
        $this->authorizeLpi($project);
        $this->enforceDataOpen($project);
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

    /**
     * Send the "progress updated" confirmation to the project's LPI
     * (automatic; gated by MAIL_ENABLED inside EventMailService).
     */
    private function notifyLpiProgressUpdated(Project $project, $actor): void
    {
        $lpi = $project->lpi;
        if (!$lpi) {
            return;
        }

        app(\App\Services\EventMailService::class)->send(
            'progress_updated',
            $lpi,
            $project,
            $actor instanceof \App\Models\User ? $actor : $lpi
        );
    }

}
