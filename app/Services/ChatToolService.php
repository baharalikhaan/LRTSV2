<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Program;
use App\Models\CycleConfig;
use App\Models\Grant;
use App\Models\FinalReportGrading;
use App\Models\ProgressReportGrading;
use App\Models\ProjectSubmission;
use Illuminate\Support\Facades\DB;

class ChatToolService
{
    /**
     * Safe columns only — no PII (names, emails, phones, budgets, CNIC, etc.)
     * Only includes columns that actually exist in the projects table.
     */
    private const PROJECT_SAFE_COLUMNS = [
        'projects.id', 'projects.title', 'projects.old_project_id',
        'projects.total_score', 'projects.lpi_id', 'projects.program_id',
        'projects.proposal_filename', 'projects.created_at',
    ];

    /**
     * Get projects scoped to the logged-in user's role.
     * Matches the filtering in ProjectController::availableProjects().
     */
    public function getMyProjects(int $userId, string $activeRole): array
    {
        $query = Project::select(self::PROJECT_SAFE_COLUMNS)
            ->with([
                'program:id,program_title',
                'grant:id,grant_name',
            ]);

        if ($activeRole === 'admin') {
            // Admin sees all projects
        } elseif ($activeRole === 'lpi') {
            // LPI: own projects + unclaimed projects (matches availableProjects)
            $query->where(function ($q) use ($userId) {
                $q->where('lpi_id', $userId)
                  ->orWhereNull('lpi_id');
            });
        } elseif ($activeRole === 'reviewer') {
            // Reviewer: only assigned projects via projects_reviewers pivot
            $query->whereHas('reviewers', fn($q) => $q->where('user_id', $userId));
        } else {
            return [];
        }

        $projects = $query->orderByDesc('created_at')->limit(25)->get();

        return $projects->map(fn($p) => [
            'id'       => $p->id,
            'title'    => $p->title,
            'program'  => $p->program->program_title ?? null,
            'grant'    => $p->grant->grant_name ?? null,
            'total_score' => $p->total_score,
            'created'  => $p->created_at->format('Y-m-d'),
        ])->toArray();
    }

    /**
     * Get safe details for a single project (role-scoped).
     */
    public function getProjectDetails(int $projectId, int $userId, string $activeRole): ?array
    {
        $project = Project::select(self::PROJECT_SAFE_COLUMNS)
            ->with([
                'program:id,program_title,prog_rpt_deadline,final_rpt_deadline',
                'grant:id,grant_name',
                'pillars:id,pillar',
                'colleges:id,name',
            ])
            ->find($projectId);

        if (!$project) {
            return null;
        }

        // Role scoping — matches availableProjects filtering
        if ($activeRole === 'admin') {
            // Admin can view any
        } elseif ($activeRole === 'lpi') {
            // LPI can view own projects + unclaimed
            if ($project->lpi_id !== null && $project->lpi_id !== $userId) {
                return null;
            }
        } elseif ($activeRole === 'reviewer') {
            $isAssigned = $project->reviewers()->where('user_id', $userId)->exists();
            if (!$isAssigned) {
                return null;
            }
        } else {
            return null;
        }

        $commitment = $project->commitments()->first();

        return [
            'id'        => $project->id,
            'title'     => $project->title,
            'program'   => $project->program->program_title ?? null,
            'grant'     => $project->grant->grant_name ?? null,
            'pillars'   => $project->pillars->pluck('pillar')->toArray(),
            'colleges'  => $project->colleges->pluck('name')->toArray(),
            'total_score' => $project->total_score,
            'created'   => $project->created_at->format('Y-m-d'),
            'commitments' => $commitment ? [
                'publications' => $commitment->q1article + $commitment->q2article + $commitment->q3article + $commitment->q4article + $commitment->confArticle,
                'books'        => $commitment->books + $commitment->editBooks + $commitment->chapters,
                'ip'           => $commitment->ip + $commitment->filedPatent + $commitment->openSourceSW + $commitment->startUp,
                'students'     => $commitment->master + $commitment->UG + $commitment->Phd,
            ] : null,
        ];
    }

    /**
     * Get grades for the user's projects (role-scoped).
     */
    public function getMyGrades(int $userId, string $activeRole): array
    {
        $finalGrades = FinalReportGrading::select(
                'project_id', 'gradeA', 'gradeB', 'gradeC', 'gradeD', 'publish'
            )
            ->where('publish', '!=', 'pending');

        $progressGrades = ProgressReportGrading::select(
                'project_id', 'achievementsRating', 'publicationsRating', 'studentsRating',
                'budgetRating', 'publish', 'report_type'
            )
            ->where('publish', '!=', 'pending');

        if ($activeRole === 'admin') {
            // Admin sees all
        } elseif ($activeRole === 'lpi') {
            $lpiProjectIds = Project::where('lpi_id', $userId)->pluck('id');
            $finalGrades->whereIn('project_id', $lpiProjectIds);
            $progressGrades->whereIn('project_id', $lpiProjectIds);
        } elseif ($activeRole === 'reviewer') {
            $reviewedProjectIds = Project::whereHas('reviewers', fn($q) => $q->where('user_id', $userId))->pluck('id');
            $finalGrades->whereIn('project_id', $reviewedProjectIds);
            $progressGrades->whereIn('project_id', $reviewedProjectIds);
        } else {
            return [];
        }

        $final = $finalGrades->orderByDesc('project_id')->limit(20)->get()->map(fn($g) => [
            'project_id' => $g->project_id,
            'type'       => 'final',
            'gradeA'     => $g->gradeA,
            'gradeB'     => $g->gradeB,
            'gradeC'     => $g->gradeC,
            'gradeD'     => $g->gradeD,
            'status'     => $g->publish,
        ]);

        $progress = $progressGrades->orderByDesc('project_id')->limit(20)->get()->map(fn($g) => [
            'project_id'   => $g->project_id,
            'type'         => $g->report_type ?? 'progress',
            'achievements' => $g->achievementsRating,
            'publications' => $g->publicationsRating,
            'students'     => $g->studentsRating,
            'budget'       => $g->budgetRating,
            'status'       => $g->publish,
        ]);

        return $final->concat($progress)->toArray();
    }

    /**
     * Get active research calls (public info, no scoping needed).
     */
    public function getResearchCalls(): array
    {
        return Program::select('id', 'program_title', 'prog_rpt_deadline', 'final_rpt_deadline', 'is_visible')
            ->with(['grant:id,grant_name', 'cycleConfig:id,year,title'])
            ->where('is_visible', true)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'title'         => $p->program_title,
                'grant'         => $p->grant->grant_name ?? null,
                'cycle'         => $p->cycleConfig->title ?? null,
                'year'          => $p->cycleConfig->year ?? null,
                'progress_deadline' => $p->prog_rpt_deadline?->format('Y-m-d'),
                'final_deadline'    => $p->final_rpt_deadline?->format('Y-m-d'),
            ])
            ->toArray();
    }

    /**
     * Get submission history for the user's projects (role-scoped).
     */
    public function getMySubmissions(int $userId, string $activeRole): array
    {
        $query = ProjectSubmission::select(
            'project_id', 'type', 'original_filename', 'version', 'submitted', 'submitted_at'
        );

        if ($activeRole === 'admin') {
            // Admin sees all
        } elseif ($activeRole === 'lpi') {
            $query->where('user_id', $userId);
        } elseif ($activeRole === 'reviewer') {
            $reviewedProjectIds = Project::whereHas('reviewers', fn($q) => $q->where('user_id', $userId))->pluck('id');
            $query->whereIn('project_id', $reviewedProjectIds);
        } else {
            return [];
        }

        return $query->orderByDesc('submitted_at')->limit(20)->get()->map(fn($s) => [
            'project_id' => $s->project_id,
            'type'       => $s->type,
            'file'       => $s->original_filename,
            'version'    => $s->version,
            'submitted'  => $s->submitted,
            'date'       => $s->submitted_at?->format('Y-m-d H:i'),
        ])->toArray();
    }

    /**
     * Get aggregated system stats (role-scoped).
     */
    public function getSystemStats(int $userId, string $activeRole): array
    {
        $stats = [];

        if ($activeRole === 'admin') {
            $stats['total_projects'] = Project::count();
            $stats['active_programs'] = Program::where('is_visible', true)->count();
            $stats['total_users'] = DB::table('users')->count();
        } elseif ($activeRole === 'lpi') {
            $myProjects = Project::where('lpi_id', $userId);
            $stats['my_projects'] = $myProjects->count();
        } elseif ($activeRole === 'reviewer') {
            $stats['assigned_projects'] = Project::whereHas('reviewers', fn($q) => $q->where('user_id', $userId))->count();
        }

        return $stats;
    }
}
