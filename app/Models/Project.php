<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    // New workflow statuses (single-reviewer workflow)
    public const STATUS_IMPORTED        = 'imported';
    public const STATUS_REGISTERED      = 'registered';
    public const STATUS_ASSIGNED        = 'Assigned';
    public const STATUS_CLAIMED         = 'Claimed';
    public const STATUS_PROGRESS_ADDED  = 'progress_added';
    public const STATUS_PROGRESS_REVIEWED = 'progress_reviewed';
    public const STATUS_PROGRESS_REJECTED = 'progress_rejected';
    public const STATUS_PROGRESS_EXTENDED = 'progress_extended';
    public const STATUS_PROGRESS_EXT_REVIEWED = 'progress_ext_reviewed';
    public const STATUS_PROGRESS_EXT_REJECTED = 'progress_ext_rejected';
    public const STATUS_FINAL_ADDED     = 'final_added';
    public const STATUS_GRADED          = 'Graded';
    public const STATUS_PROPOSAL_REJECTED = 'proposal_rejected';

    // Deprecated/removed (kept so old status_history records don't break)
    public const STATUS_PROGRESS           = 'progress_add';
    public const STATUS_PROGRESS_SUBMITTED = 'progress_submitted';
    public const STATUS_FINAL_SUBMITTED    = 'final_submitted';
    public const STATUS_REJECTED           = 'rejected';
    public const STATUS_CLAIM1 = 'Claim-1';
    public const STATUS_CLAIM2 = 'Claim-2';
    public const STATUS_GRADE1 = 'Grade-1';
    public const STATUS_GRADE2 = 'Grade-2';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_REPORT = 'report';
    public const STATUS_PROGRESS_LEGACY = 'progress_added';

    protected $fillable = [
        'program_id',
        'grant_id',
        'user_id',
        'old_project_id',
        'title',
        'title_ar',
        'abstract',
        'keywords',
        'author',
        'email',
        'phone',
        'status',
        'proposal_filename',
        'pillars_text',
        'tags_text',
        'lpi_id',
        'reviewer_id',
        'reviewer_assigned_at',
        'grading_finalized',
        'cycle_id',
        'is_extended',
        'grant_type',
        'current_status_id',
        'submitted_at',
        'project_title',
        'pi_name',
        'pi_email',
        'pi_phone',
        'co_pi_name',
        'co_pi_email',
        'co_pi_phone',
        'budget',
        'start_date',
        'end_date',
        'project_type',
        'project_category',
        'project_summary',
        'objectives',
        'expected_outcomes',
        'research_methodology',
        'references',
        'student_name',
        'student_id',
        'student_email',
        'student_phone',
        'student_nationality',
        'student_gender',
        'student_dob',
        'student_college',
        'student_department',
        'student_major',
        'student_gpa',
        'student_level',
        'student_courses_completed',
        'supervisor_name',
        'supervisor_email',
        'supervisor_phone',
        'supervisor_college',
        'supervisor_department',
        'co_supervisor_name',
        'co_supervisor_email',
        'co_supervisor_phone',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewer_assigned_at' => 'datetime',
        'grading_finalized' => 'boolean',
        'budget' => 'decimal:2',
    ];

    /**
     * Get the outcomes for this project.
     */
    public function outcomes(): HasMany
    {
        return $this->hasMany(Outcome::class);
    }

    /**
     * Get the program that owns this project.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Get the grant that owns this project.
     */
    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'grant_id');
    }

    /**
     * Get the LPI (Lead Principal Investigator) for this project.
     */
    public function lpi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lpi_id');
    }

    /**
     * Status histories for this project.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'project_id');
    }

    /**
     * Get the pillars for this project.
     */
    public function pillars(): BelongsToMany
    {
        return $this->belongsToMany(Pillar::class, 'project_pillar', 'project_id', 'pillar_id');
    }

    /**
     * Get the colleges for this project.
     */
    public function colleges(): BelongsToMany
    {
        return $this->belongsToMany(College::class, 'project_college', 'project_id', 'college_id');
    }

    /**
     * Get the submissions for this project.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ProjectSubmission::class);
    }

    /**
     * Get the progress report gradings for this project.
     */
    public function progressGradings(): HasMany
    {
        return $this->hasMany(ProgressReportGrading::class);
    }

    /**
     * Get the final report gradings for this project.
     */
    public function finalGradings(): HasMany
    {
        return $this->hasMany(FinalReportGrading::class);
    }

    /**
     * Get the reviewers assigned to this project.
     */
    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'projects_reviewers', 'project_id', 'user_id')
            ->withPivot('role', 'proposalstatus', 'statusdate');
    }

    /**
     * Get the storage directory path for this project's files.
     * Format: {cycle_year}/{grant_code}/{type_folder}
     * where type_folder = proposals | progress_reports | readiness_reports | final_reports
     */
    public function getStorageDir(string $typeFolder = 'proposals'): string
    {
        $cycleYear = 'unknown';
        $grantCode = 'unknown';
        if ($this->program) {
            if ($this->program->cycle) {
                $cycleYear = $this->program->cycle->year ?? 'unknown';
            }
            if ($this->program->grant) {
                $grantCode = $this->program->grant->grant_code ?? 'unknown';
            }
        }
        return 'uploads/' . $cycleYear . '/' . $grantCode . '/' . $typeFolder;
    }

    /**
     * Get the full storage filename for a given document type.
     * type = proposal | progress | readiness | final
     * Optionally pass a version number for versioned files (e.g. progress resubmission).
     */
    public function getStorageFilename(string $type, ?int $version = null): string
    {
        $oldId = str_replace('/', '', $this->old_project_id ?? $this->id);
        $typeFolder = $type === 'proposal' ? 'proposals'
            : ($type === 'progress' ? 'progress_reports'
            : ($type === 'readiness' ? 'readiness_reports' : 'final_reports'));
        $versionSuffix = $version ? '_v' . $version : '';
        return $this->getStorageDir($typeFolder) . '/' . $oldId . '_' . $type . $versionSuffix . '.pdf';
    }

    /**
     * Get the latest status history record for this project.
     */
    public function latestStatus(): HasOne
    {
        return $this->hasOne(StatusHistory::class, 'project_id')->latestOfMany();
    }

    public function hasStatus(string $status): bool
    {
        return $this->statusHistories()->where('status', $status)->exists();
    }

    public function recordStatus(string $status, ?array $metadata = null, ?int $userId = null): StatusHistory
    {
        return $this->statusHistories()->create([
            'status' => $status,
            'user_id' => $userId,
            'metadata' => $metadata ?? [],
        ]);
    }

    /**
     * Get human-readable labels for all workflow statuses.
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_IMPORTED            => 'Imported',
            self::STATUS_REGISTERED          => 'Registered',
            self::STATUS_ASSIGNED            => 'Assigned',
            self::STATUS_CLAIMED             => 'Claimed',
            self::STATUS_PROPOSAL_REJECTED     => 'Proposal Rejected',
            self::STATUS_PROGRESS_ADDED      => 'Progress Added',
            self::STATUS_PROGRESS_REVIEWED   => 'Progress Reviewed',
            self::STATUS_PROGRESS_REJECTED   => 'Progress Rejected',
            self::STATUS_PROGRESS_EXTENDED   => 'Extended Progress Added',
            self::STATUS_PROGRESS_EXT_REVIEWED => 'Extended Progress Reviewed',
            self::STATUS_PROGRESS_EXT_REJECTED => 'Extended Progress Rejected',
            self::STATUS_FINAL_ADDED         => 'Final Added',
            self::STATUS_GRADED              => 'Graded',
        ];
    }

    public function programIsActive(): bool
    {
        if (!$this->relationLoaded('program') && !$this->program()->exists()) {
            return true;
        }

        $program = $this->program;

        if (!$program) {
            return true;
        }

        return $program->isActive();
    }

    /**
     * Scope to only include projects from visible (non-hidden) programs.
     */
    public function scopeVisibleProgram($query)
    {
        return $query->whereHas('program', function ($q) {
            $q->where('is_visible', true);
        });
    }

    public function currentWorkflowStatus(): ?string
    {
        $status = $this->current_status;

        if ($status === null) {
            return null;
        }

        if (in_array($status, ['imported', 'unregistered'], true)) {
            return 'imported';
        }

        return $status;
    }

    /**
     * Get the role a specific user has on this project (always 'Reviewer' in single-reviewer workflow).
     */
    public function reviewerRole(int $userId): ?string
    {
        $pivot = DB::table('projects_reviewers')
            ->where('project_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        return $pivot->role ?? null;
    }

    /**
     * Check if this user has already claimed the project.
     */
    public function userHasClaimed(int $userId): bool
    {
        $pivot = DB::table('projects_reviewers')
            ->where('project_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        if (!$pivot) {
            return false;
        }

        return $pivot->proposalstatus === 'accepted';
    }

    /**
     * Check if this user has already graded the project.
     */
    public function userHasGraded(int $userId): bool
    {
        return $this->hasStatus(self::STATUS_GRADED);
    }

    /**
     * Determine which actions are available to a user based on the new workflow:
     *
     * Workflow (Admin Assign + LPI Add Progress run in parallel after registration):
     * | # | Current Status        | Next Action        | Result Status           | By Whom   |
     * |---|-----------------------|--------------------|-------------------------|-----------|
     * | 0 | imported / none       | Register           | registered              | LPI       |
     * | 1 | registered            | Assign Reviewer    | Assigned                | Admin     |
     * | 1 | registered            | Add Progress       | progress_added          | LPI       |
     * | 2 | Assigned              | Claim              | Claimed                 | Reviewer  |
     * | 3 | progress_added        | Review Progress    | progress_reviewed       | Reviewer  |
     * |   | progress_rejected     | Add Progress (v2)  | progress_added          | LPI       |
     * | 4 | progress_reviewed     | Add Final Report   | final_added             | LPI       |
     * | 5 | final_added           | Grade Final        | Graded                  | Reviewer  |
     * | 6 | Graded                | Report Card        | (view only)             | All       |
     */
    public function availableActions($user = null): array
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return [];
        }

        $role = $user->activeRole();
        $actions = [];

        $hasImported        = $this->hasStatus(self::STATUS_IMPORTED);
        $hasRegistered      = $this->hasStatus(self::STATUS_REGISTERED);
        $hasAssigned        = $this->hasStatus(self::STATUS_ASSIGNED);
        $hasClaimed         = $this->hasStatus(self::STATUS_CLAIMED);
        $hasProgressAdded   = $this->hasStatus(self::STATUS_PROGRESS_ADDED);
        $hasProgressRev     = $this->hasStatus(self::STATUS_PROGRESS_REVIEWED);
        $hasProgressRej     = $this->hasStatus(self::STATUS_PROGRESS_REJECTED);
    $hasProposalRej = $this->hasStatus(self::STATUS_PROPOSAL_REJECTED);
        $hasFinalAdded      = $this->hasStatus(self::STATUS_FINAL_ADDED);
        $hasGraded          = $this->hasStatus(self::STATUS_GRADED);

        $currentStatus = $this->currentWorkflowStatus();

        // Admin actions (check first so Admin+Reviewer sees both)
        if ($role === 'Admin') {
            $adminActions = [];

            // Check if reviewer exists in projects_reviewers table
            $hasReviewerEntry = DB::table('projects_reviewers')
                ->where('project_id', $this->id)
                ->exists();

            // Assign Reviewer: show if registered AND (no reviewer entry OR proposal rejected but not yet re-assigned)
            if ($hasRegistered && !$hasGraded && (!$hasReviewerEntry || ($hasProposalRej && !$hasAssigned))) {
                $adminActions[] = ['action' => 'assign', 'label' => 'Assign Reviewer'];
            }

            // Enable Extended Progress Report (after progress reviewed, before final)
            if ($hasProgressAdded && $hasProgressRev && !$hasFinalAdded && !$this->is_extended) {
                $adminActions[] = ['action' => 'enable-extended-progress', 'label' => 'Enable Extended Progress'];
            }

            // Disable Extended Progress Report (if enabled but not yet submitted)
            if ($this->is_extended && !$hasFinalAdded) {
                $adminActions[] = ['action' => 'disable-extended-progress', 'label' => 'Disable Extended Progress'];
            }

            if ($hasGraded) {
                $adminActions[] = ['action' => 'report-card', 'label' => 'Report Card'];
            }

            // If user is also a reviewer, merge reviewer actions
            if ($user->isReviewer()) {
                $reviewerActions = $this->getReviewerActions($user,
                    $hasAssigned, $hasClaimed,
                    $hasProgressAdded, $hasProgressRev,
                    $hasFinalAdded, $hasGraded
                );
                return array_merge($adminActions, $reviewerActions);
            }

            return $adminActions;
        }

        // LPI actions
        if ($role === 'LPI') {
            // Register (if imported or no status yet)
            if (!$hasRegistered && ($hasImported || !$currentStatus)) {
                $actions[] = ['action' => 'register', 'label' => 'Register'];
            }

            // Add Progress v1 — show if status is registered, assigned, claimed, or proposal_rejected
            // AND no progress added yet
            $canAddProgress = ($hasRegistered || $hasAssigned || $hasClaimed || $hasProposalRej) && !$hasProgressAdded;
            if ($canAddProgress) {
                $actions[] = ['action' => 'progress', 'label' => 'Add Progress'];
            }
            // Resubmit Progress after progress rejection (not proposal rejection)
            // Only show if last rejection is newer than last progress_added
            if ($hasProgressRej && !$hasProgressRev) {
                $lastRejection = $this->statusHistories()->where('status', self::STATUS_PROGRESS_REJECTED)->latest()->first();
                $lastProgressAdded = $this->statusHistories()->where('status', self::STATUS_PROGRESS_ADDED)->latest()->first();
                if ($lastRejection && (!$lastProgressAdded || $lastProgressAdded->created_at->lt($lastRejection->created_at))) {
                    $actions[] = ['action' => 'progress', 'label' => 'Resubmit Progress'];
                }
            }

            // Add Extended Progress v2 — after progress reviewed, if is_extended and not yet added
            $hasProgressExtended = $this->hasStatus(self::STATUS_PROGRESS_EXTENDED);
            $hasProgressExtRev = $this->hasStatus(self::STATUS_PROGRESS_EXT_REVIEWED);
            if ($this->is_extended && $hasProgressRev && !$hasProgressExtended && !$hasProgressExtRev && !$hasFinalAdded && !$hasGraded) {
                $actions[] = ['action' => 'progress-v2', 'label' => 'Add Extended Progress'];
            }
            // Resubmit Extended Progress after rejection
            $hasProgressExtRej = $this->hasStatus(self::STATUS_PROGRESS_EXT_REJECTED);
            if ($hasProgressExtRej && !$hasProgressExtRev) {
                $actions[] = ['action' => 'progress-v2', 'label' => 'Resubmit Extended Progress'];
            }

            // Add Final Report (if progress reviewed or extended progress reviewed, and not yet added final)
            if (($hasProgressRev || $hasProgressExtRev) && !$hasFinalAdded && !$hasGraded) {
                $actions[] = ['action' => 'final-report', 'label' => 'Add Final Report'];
            }

            if ($hasGraded) {
                $actions[] = ['action' => 'report-card', 'label' => 'Report Card'];
            }

            return $actions;
        }

        // Reviewer actions
        if ($user->isReviewer()) {
            return $this->getReviewerActions($user,
                $hasAssigned, $hasClaimed,
                $hasProgressAdded, $hasProgressRev,
                $hasFinalAdded, $hasGraded
            );
        }

        return $actions;
    }

    /**
     * Get reviewer-specific actions for the new workflow.
     */
    private function getReviewerActions(
        $user,
        bool $hasAssigned,
        bool $hasClaimed,
        bool $hasProgressAdded,
        bool $hasProgressReviewed,
        bool $hasFinalAdded,
        bool $hasGraded
    ): array {
        $reviewerActions = [];

        // Check if this reviewer is actually assigned to this project
        $isAssignedToProject = $this->reviewers()->where('user_id', $user->id)->exists();

        if (!$isAssignedToProject) {
            return [];
        }

        $hasUserClaimed = $this->userHasClaimed($user->id);

        // Must be Assigned (or beyond) for any reviewer actions
        if (!$hasAssigned && !$hasClaimed) {
            return [];
        }

        // Step 2: Claim — reviewer can claim if they haven't already
        if (!$hasUserClaimed && !$hasGraded) {
            $reviewerActions[] = ['action' => 'claim', 'label' => 'Claim', 'step' => 'Claim'];
        }

        // Check if extended progress was submitted
        $hasProgressExtended = $this->hasStatus(self::STATUS_PROGRESS_EXTENDED);

        // Step 4a: Review Progress V1 — after LPI has added progress report v1 (and no extended progress)
        // Only show if last progress_added is newer than last progress_rejected
        if ($hasUserClaimed && $hasProgressAdded && !$hasProgressReviewed && !$hasProgressExtended && !$hasGraded) {
            $lastRejection = $this->statusHistories()->where('status', self::STATUS_PROGRESS_REJECTED)->latest()->first();
            $lastProgressAdded = $this->statusHistories()->where('status', self::STATUS_PROGRESS_ADDED)->latest()->first();
            if (!$lastRejection || ($lastProgressAdded && $lastProgressAdded->created_at->gt($lastRejection->created_at))) {
                $reviewerActions[] = ['action' => 'progress-grade', 'label' => 'Review Progress', 'step' => 'Progress Review'];
            }
        }

        // Step 4b: Review Extended Progress V2 — after LPI has added extended progress
        // Only show if last progress_extended is newer than last progress_ext_rejected
        if ($hasUserClaimed && $hasProgressExtended && !$hasProgressReviewed && !$hasGraded) {
            $lastExtRejection = $this->statusHistories()->where('status', self::STATUS_PROGRESS_EXT_REJECTED)->latest()->first();
            $lastExtProgress = $this->statusHistories()->where('status', self::STATUS_PROGRESS_EXTENDED)->latest()->first();
            if (!$lastExtRejection || ($lastExtProgress && $lastExtProgress->created_at->gt($lastExtRejection->created_at))) {
                $reviewerActions[] = ['action' => 'progress-ext-grade', 'label' => 'Review Extended Progress', 'step' => 'Extended Progress Review'];
            }
        }

        // Step 6: Grade Final — after LPI has added final report & progress was reviewed
        if ($hasUserClaimed && $hasProgressReviewed && $hasFinalAdded && !$hasGraded) {
            $reviewerActions[] = ['action' => 'final-grade', 'label' => 'Grade Final Report', 'step' => 'Final Grade'];
        }

        return $reviewerActions;
    }

    public function intellectualProperties(): HasMany
    {
        return $this->hasMany(ProjectIntellectualProperty::class, 'project_id');
    }

    /**
     * Get the IP/Patent contributions (ip_disclosure, provisional_patent, patent_granted, open_source_sw, startup).
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(ProjectContribution::class, 'project_id');
    }

    /**
     * Get the publications for this project.
     */
    public function publications(): HasMany
    {
        return $this->hasMany(ProjectPublication::class, 'project_id');
    }

    /**
     * Get the students for this project.
     */
    public function students(): HasMany
    {
        return $this->hasMany(ProjectStudent::class, 'project_id');
    }

    /**
     * Get the researchers for this project.
     */
    public function researchers(): HasMany
    {
        return $this->hasMany(ProjectResearcher::class, 'project_id');
    }

    /**
     * Get the commitments for this project.
     */
    public function commitments(): HasMany
    {
        return $this->hasMany(Commitment::class, 'project_id');
    }

    /**
     * Accessor: Get the current status from the latest status history.
     * Falls back to the deprecated 'status' column if no history exists.
     */
    public function getCurrentStatusAttribute(): ?string
    {
        if ($this->relationLoaded('latestStatus') && $this->latestStatus) {
            return $this->latestStatus->status;
        }

        $latestStatus = $this->latestStatus()->first();

        if ($latestStatus) {
            return $latestStatus->status;
        }

        // Fallback to the direct column value
        return $this->attributes['status'] ?? null;
    }

    /**
     * Accessor: Get the current status changed_at timestamp from the latest status history.
     */
    public function getStatusChangedAtAttribute()
    {
        if ($this->relationLoaded('latestStatus') && $this->latestStatus) {
            return $this->latestStatus->created_at;
        }

        return null;
    }

    /**
     * Get the submitted-at timestamp for a given report type (progress/final/readiness).
     * Returns null if the report has not been officially submitted (only drafted).
     */
    public function submittedReportAt(string $type): ?\Illuminate\Support\Carbon
    {
        $row = $this->submissions()
            ->where('type', $type)
            ->where('submitted', true)
            ->orderByDesc('submitted_at')
            ->first();

        return $row ? $row->submitted_at : null;
    }

    /**
     * Has a report of this type been officially submitted (not just drafted)?
     */
    public function reportSubmitted(string $type): bool
    {
        return $this->submissions()
            ->where('type', $type)
            ->where('submitted', true)
            ->exists();
    }

    /**
     * Returns the latest submission record of a given type (draft or submitted).
     */
    public function latestSubmissionOfType(string $type): ?ProjectSubmission
    {
        return $this->submissions()->where('type', $type)->latest('id')->first();
    }

    /**
     * The list of reviewers who previously rejected this project's proposal.
     * Used by the admin assignment UI to exclude them from the dropdown.
     */
    public function previousRejectors()
    {
        // Defensive: reviewer_rejections may not exist until the migration runs.
        if (!\Illuminate\Support\Facades\Schema::hasTable('reviewer_rejections')) {
            return collect();
        }

        return \App\Models\ReviewerRejection::where('project_id', $this->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Build the lifecycle stages array based on status history.
     * Returns an ordered array of stages with labels, icons, done status, dates, and user names.
     */
    public function getLifecycleAttribute(): array
    {
        $stages = [
            'imported'          => ['label' => 'Import from Excel',               'icon' => 'fa-file-import',     'done' => false, 'date' => null, 'user_name' => null],
            'registered'        => ['label' => 'Project Registration by LPI',     'icon' => 'fa-clipboard-check', 'done' => false, 'date' => null, 'user_name' => null],
            'assigned'          => ['label' => 'Reviewer Assignment by Admin',    'icon' => 'fa-user-tag',        'done' => false, 'date' => null, 'user_name' => null],
            'claimed'           => ['label' => 'Proposal Acceptance by Reviewer', 'icon' => 'fa-handshake',       'done' => false, 'date' => null, 'user_name' => null],
            'progress_added'    => ['label' => 'Progress Report by LPI',          'icon' => 'fa-chart-line',      'done' => false, 'date' => null, 'user_name' => null],
            'progress_reviewed' => ['label' => 'Progress Review by Reviewer',     'icon' => 'fa-clipboard-check', 'done' => false, 'date' => null, 'user_name' => null],
            'final_added'       => ['label' => 'Final Report by LPI',             'icon' => 'fa-paper-plane',     'done' => false, 'date' => null, 'user_name' => null],
            'graded'            => ['label' => 'Final Grading by Reviewer',       'icon' => 'fa-flag-checkered',  'done' => false, 'date' => null, 'user_name' => null],
        ];

        // Map status_history status values to our lifecycle keys
        $statusStageMap = [
            'imported'            => 'imported',
            'registered'          => 'registered',
            'Assigned'            => 'assigned',
            'Claimed'             => 'claimed',
            self::STATUS_PROPOSAL_REJECTED   => 'claimed',  // unmarks the claimed stage
            'progress_added'      => 'progress_added',
            'progress_reviewed'   => 'progress_reviewed',
            'progress_rejected'   => 'progress_added',   // unmarks the progress stage
            'final_added'         => 'final_added',
            'Graded'              => 'graded',
        ];

        // Get all status histories ordered by created_at
        $histories = $this->statusHistories()->with('user')->orderBy('created_at')->get();

        foreach ($histories as $history) {
            $stageKey = $statusStageMap[$history->status] ?? null;
            if ($stageKey && isset($stages[$stageKey])) {
                if ($history->status === self::STATUS_PROGRESS_REJECTED) {
                    // Rejection unmarks the progress stage (LPI needs to resubmit)
                    $stages[$stageKey]['done'] = false;
                } else {
                    $stages[$stageKey]['done'] = true;
                    $stages[$stageKey]['date'] = $history->created_at->toDateTimeString();
                    $stages[$stageKey]['user_name'] = $history->user ? $history->user->name : null;
                }
            }
        }

        // Always mark "imported" as done if the project exists
        $stages['imported']['done'] = true;
        $stages['imported']['date'] = $this->submitted_at ? $this->submitted_at->toDateTimeString() : $this->created_at->toDateTimeString();

        // Check if there were imported/unregistered statuses — mark imported as earliest
        $earliestHistory = $this->statusHistories()->orderBy('created_at')->first();
        if ($earliestHistory && !$this->submitted_at) {
            $stages['imported']['date'] = $earliestHistory->created_at->toDateTimeString();
        }

        return $stages;
    }

    /**
     * Get the current lifecycle stage index (0-based).
     * Returns the index of the first stage that is not yet done, or the last stage if all done.
     */
    public function getLifecycleStageAttribute(): int
    {
        $stages = $this->lifecycle;
        $stageKeys = array_keys($stages);

        foreach ($stageKeys as $index => $key) {
            if (!($stages[$key]['done'] ?? false)) {
                return $index;
            }
        }

        // All done — return the last stage index
        return count($stageKeys) - 1;
    }

    /**
     * Accessor: Get the status badge class for the current status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $status = $this->current_status;

        if (!$status) {
            return 'badge badge-secondary';
        }

        $status = strtolower($status);

        if (in_array($status, ['submitted', 'resubmitted'])) {
            return 'badge badge-info';
        }

        if (in_array($status, ['under review', 'review', 'under_review'])) {
            return 'badge badge-warning';
        }

        if (in_array($status, ['approved', 'accepted', 'active'])) {
            return 'badge badge-success';
        }

        if (in_array($status, ['rejected', 'returned', self::STATUS_PROPOSAL_REJECTED, self::STATUS_PROGRESS_REJECTED])) {
            return 'badge badge-danger';
        }

        if (in_array($status, ['draft', 'in_progress', 'in progress'])) {
            return 'badge badge-secondary';
        }

        if (in_array($status, ['completed', 'closed', 'finalized'])) {
            return 'badge badge-primary';
        }

        return 'badge badge-secondary';
    }
}
