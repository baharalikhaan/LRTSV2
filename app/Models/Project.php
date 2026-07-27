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
    // Spreadsheet-defined statuses (single-reviewer workflow)
    public const STATUS_REGISTERED = 'registered';
    public const STATUS_PROGRESS = 'progress_add';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_CLAIMED = 'Claimed';
    public const STATUS_GRADED = 'Graded';

    // Deprecated/removed (kept so old status_history records don't break)
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
     */
    public function getStorageFilename(string $type): string
    {
        $oldId = str_replace('/', '', $this->old_project_id ?? $this->id);
        $typeFolder = $type === 'proposal' ? 'proposals'
            : ($type === 'progress' ? 'progress_reports'
            : ($type === 'readiness' ? 'readiness_reports' : 'final_reports'));
        return $this->getStorageDir($typeFolder) . '/' . $oldId . '_' . $type . '.pdf';
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
            self::STATUS_REGISTERED => 'Registered',
            self::STATUS_PROGRESS   => 'Progress Added',
            self::STATUS_ASSIGNED   => 'Assigned',
            self::STATUS_CLAIMED    => 'Claimed',
            self::STATUS_GRADED     => 'Graded',
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
     * Determine which actions are available to a user based on the single-reviewer workflow.
     *
     * Workflow:
     * | # | Current Status   | Next Action     | Result Status       | By Whom   |
     * |---|------------------|-----------------|---------------------|-----------|
     * | 0 | unregistered     | Register        | registered          | LPI       |
     * | 1 | registered       | Add Progress    | progress_add        | LPI       |
     * | 2 | progress_add     | Assign Reviewer | Assigned            | Admin     |
     * | 3 | Assigned         | Claim           | Claimed             | Reviewer  |
     * | 4 | Claimed          | Grade           | Graded              | Reviewer  |
     * | 5 | Graded           | Report Card     | (view only)         | All       |
     */
    public function availableActions($user = null): array
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return [];
        }

        $role = $user->activeRole();
        $actions = [];

        $hasRegistered = $this->hasStatus(self::STATUS_REGISTERED);
        $hasProgress   = $this->hasStatus(self::STATUS_PROGRESS);
        $hasAssigned   = $this->hasStatus(self::STATUS_ASSIGNED);
        $hasClaimed    = $this->hasStatus(self::STATUS_CLAIMED);
        $hasGraded     = $this->hasStatus(self::STATUS_GRADED) || in_array($this->currentWorkflowStatus(), ['graded', 'report', self::STATUS_GRADED]);

        // LPI actions
        if ($role === 'LPI') {
            if (!$hasRegistered) {
                $actions[] = ['action' => 'register', 'label' => 'Register'];
            }

            if ($hasRegistered && !$hasProgress) {
                $actions[] = ['action' => 'progress', 'label' => 'Add Progress'];
            }

            if ($hasGraded) {
                $actions[] = ['action' => 'report-card', 'label' => 'Report Card'];
            }

            return $actions;
        }

        // Reviewer actions
        if ($user->isReviewer()) {
            return $this->getReviewerActions($user, $hasAssigned, $hasClaimed, $hasGraded);
        }

        // Admin actions
        if ($role === 'Admin') {
            $adminActions = [];

            if ($hasProgress && !$hasAssigned) {
                $adminActions[] = ['action' => 'assign', 'label' => 'Assign Reviewer'];
            }

            if ($hasGraded) {
                $adminActions[] = ['action' => 'report-card', 'label' => 'Report Card'];
            }

            // Admin may also be a reviewer — merge their reviewer actions if assigned
            if ($user->isReviewer()) {
                $reviewerActions = $this->getReviewerActions($user, $hasAssigned, $hasClaimed, $hasGraded);
                return array_merge($adminActions, $reviewerActions);
            }

            return $adminActions;
        }

        return $actions;
    }

    /**
     * Get reviewer-specific actions for the single-reviewer workflow.
     *
     * Step 3: Claiming
     * Step 4: Progress Grading
     * Step 5: Final Grading
     * Step 6: Graded (complete)
     */
    private function getReviewerActions(
        $user,
        bool $hasAssigned,
        bool $hasClaimed,
        bool $hasGraded
    ): array {
        $reviewerActions = [];

        // Check if this reviewer is actually assigned to this project
        $isAssignedToProject = $this->reviewers()->where('user_id', $user->id)->exists();

        if (!$isAssignedToProject) {
            return [];
        }

        $hasUserClaimed = $this->userHasClaimed($user->id);
        $hasUserGraded = $this->userHasGraded($user->id);

        // Check if the user has saved a progress grade
        $hasProgressGrade = \App\Models\ProgressReportGrading::where('project_id', $this->id)
            ->where('user_id', $user->id)
            ->exists();

        // Check if the user has saved a final grade
        $hasFinalGrade = \App\Models\FinalReportGrading::where('project_id', $this->id)
            ->where('user_id', $user->id)
            ->exists();

        // Must be Assigned (or beyond) for any reviewer actions
        if (!$hasAssigned && !$hasClaimed) {
            return [];
        }

        // Step 3: Claim — reviewer can claim if they haven't already and project is not yet graded
        if (!$hasUserClaimed && !$hasGraded) {
            $reviewerActions[] = ['action' => 'claim', 'label' => 'Claim', 'step' => 'Claim'];
        }

        // Step 4: Progress Grade — reviewer can grade progress if they have claimed
        if ($hasUserClaimed && !$hasProgressGrade && !$hasGraded) {
            $reviewerActions[] = ['action' => 'progress-grade', 'label' => 'Grade Project', 'step' => 'Progress Grade'];
        }

        // Step 5: Final Grade — after progress grade is done, show final grade
        if ($hasUserClaimed && $hasProgressGrade && !$hasFinalGrade && !$hasGraded) {
            $reviewerActions[] = ['action' => 'final-grade', 'label' => 'Grade Project', 'step' => 'Final Grade'];
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
     * Build the lifecycle stages array based on status history.
     * Returns an ordered array of stages with labels, icons, done status, dates, and user names.
     */
    public function getLifecycleAttribute(): array
    {
        $stages = [
            'submission' => ['label' => 'Import from conf-tool', 'icon' => 'fa-file-import', 'done' => false, 'date' => null, 'user_name' => null],
            'registered' => ['label' => 'Project Registration by LPI', 'icon' => 'fa-clipboard-check', 'done' => false, 'date' => null, 'user_name' => null],
            'progress'   => ['label' => 'Progress Update by LPI', 'icon' => 'fa-chart-line', 'done' => false, 'date' => null, 'user_name' => null],
            'assigned'   => ['label' => 'Reviewer Assignment by Admin', 'icon' => 'fa-user-tag', 'done' => false, 'date' => null, 'user_name' => null],
            'claimed'    => ['label' => 'Proposal Acceptance by Reviewer', 'icon' => 'fa-handshake', 'done' => false, 'date' => null, 'user_name' => null],
            'graded'     => ['label' => 'Grading by Reviewer', 'icon' => 'fa-flag-checkered', 'done' => false, 'date' => null, 'user_name' => null],
        ];

        // Map status_history status values to our lifecycle keys
        // Includes legacy dual-reviewer statuses for backward compatibility with old records
        $statusStageMap = [
            'registered'     => 'registered',
            'progress_add'   => 'progress',
            'Assigned'       => 'assigned',
            'Claimed'        => 'claimed',
            'Graded'         => 'graded',
        ];

        // Get all status histories ordered by created_at
        $histories = $this->statusHistories()->with('user')->orderBy('created_at')->get();

        foreach ($histories as $history) {
            $stageKey = $statusStageMap[$history->status] ?? null;
            if ($stageKey && isset($stages[$stageKey])) {
                $stages[$stageKey]['done'] = true;
                $stages[$stageKey]['date'] = $history->created_at->toDateTimeString();
                $stages[$stageKey]['user_name'] = $history->user ? $history->user->name : null;
            }
        }

        // Always mark "submission" as done if the project exists
        $stages['submission']['done'] = true;
        $stages['submission']['date'] = $this->submitted_at ? $this->submitted_at->toDateTimeString() : $this->created_at->toDateTimeString();

        // Check if there were imported/unregistered statuses — mark submission as earliest
        $earliestHistory = $this->statusHistories()->orderBy('created_at')->first();
        if ($earliestHistory && !$this->submitted_at) {
            $stages['submission']['date'] = $earliestHistory->created_at->toDateTimeString();
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

        if (in_array($status, ['rejected', 'returned'])) {
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
