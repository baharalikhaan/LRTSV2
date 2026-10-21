<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\Pillar;
use App\Models\Program;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Renders the per-Research-Call (Program) printable "report booklet": an A4
 * white-page HTML view (printed via the browser, which can also save it as a
 * PDF) containing KPIs, workflow/pipeline statistics, grading outcomes,
 * budget, research output, people, a monthly trend chart and a project
 * appendix.
 */
class ResearchCallBookletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->isAdmin()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized. Only admins can generate research call reports.'], 403);
                }
                return redirect()->route('home')->with('error', 'Unauthorized. Only admins can generate research call reports.');
            }
            return $next($request);
        });
    }

    /** Render the printable A4 booklet page. */
    public function show($id)
    {
        $program = Program::with(['cycle', 'grant'])->findOrFail($id);

        return view('reports.research-call-booklet', $this->buildBooklet($program));
    }

    private function buildBooklet(Program $program): array
    {
        $statusLabels = [
            'imported'             => 'Imported',
            'registered'           => 'Registered',
            'Assigned'             => 'Assigned',
            'Claimed'              => 'Claimed',
            'proposal_rejected'    => 'Proposal Rejected',
            'reviewer_unassigned'  => 'Reviewer Un-assigned',
            'progress_added'       => 'Progress Added',
            'progress_reviewed'    => 'Progress Reviewed',
            'progress_rejected'    => 'Progress Rejected',
            'progress2_added'      => 'PR2 Added',
            'progress2_reviewed'   => 'PR2 Reviewed',
            'progress2_rejected'   => 'PR2 Rejected',
            'final_added'          => 'Final Added',
            'final_rejected'       => 'Final Rejected',
            'Graded'               => 'Graded',
        ];

        $projects = $program->projects()->with(['lpi', 'latestStatus'])->get();
        $ids = $projects->pluck('id')->all();
        $total = $projects->count();

        // ---- Workflow status distribution (one bulk query, no N+1) --------
        $statusRows = $ids
            ? DB::table('status_histories')->whereIn('project_id', $ids)->get(['project_id', 'status', 'created_at'])
            : collect();
        $statusesByProject = $statusRows->groupBy('project_id');

        $statusDistribution = $projects
            ->groupBy(fn ($p) => $p->currentWorkflowStatus() ?: 'imported')
            ->map(fn ($set, $key) => [
                'key'    => $key,
                'label'  => $statusLabels[$key] ?? ucfirst(str_replace('_', ' ', (string) $key)),
                'count'  => $set->count(),
                'pct'    => $total > 0 ? round(($set->count() / $total) * 100, 1) : 0,
            ])
            ->sortByDesc('count')
            ->values();

        // ---- Pipeline milestones -----------------------------------------
        $hasStatus = function (string $status) use ($statusesByProject, $projects) {
            return $projects->filter(function ($p) use ($statusesByProject, $status) {
                $rows = $statusesByProject->get($p->id);
                return $rows && $rows->contains('status', $status);
            })->count();
        };

        $pipeline = [
            ['label' => 'Registered',       'count' => $hasStatus(Project::STATUS_REGISTERED)],
            ['label' => 'Assigned',         'count' => $hasStatus(Project::STATUS_ASSIGNED)],
            ['label' => 'Claimed',          'count' => $hasStatus(Project::STATUS_CLAIMED)],
            ['label' => 'Progress Added',   'count' => $hasStatus(Project::STATUS_PROGRESS_ADDED)],
            ['label' => 'Progress Reviewed','count' => $hasStatus(Project::STATUS_PROGRESS_REVIEWED)],
            ['label' => 'Final Added',      'count' => $hasStatus(Project::STATUS_FINAL_ADDED)],
            ['label' => 'Graded',           'count' => $hasStatus(Project::STATUS_GRADED)],
        ];
        foreach ($pipeline as $i => $row) {
            $pipeline[$i]['pct'] = $total > 0 ? round(($row['count'] / $total) * 100, 1) : 0;
        }

        // ---- Grading statistics ------------------------------------------
        $progressGrades = $ids
            ? DB::table('progress_report_grading')->whereIn('project_id', $ids)->where('publish', '!=', 'pending')->get()
            : collect();
        $finalGrades = $ids
            ? DB::table('final_report_grading')->whereIn('project_id', $ids)->where('publish', '!=', 'pending')->get()
            : collect();

        $grading = [
            'progress_total'    => $progressGrades->count(),
            'progress_accepted' => $progressGrades->where('publish', 'accepted')->count(),
            'progress_rejected' => $progressGrades->where('publish', 'rejected')->count(),
            'final_total'       => $finalGrades->count(),
            'final_accepted'    => $finalGrades->where('publish', 'accepted')->count(),
            'final_rejected'    => $finalGrades->where('publish', 'rejected')->count(),
            'avg_total'         => $finalGrades->count() ? round((float) $finalGrades->avg('total'), 2) : 0,
            'avg_a'             => $finalGrades->count() ? round((float) $finalGrades->avg('gradeA'), 2) : 0,
            'avg_b'             => $finalGrades->count() ? round((float) $finalGrades->avg('gradeB'), 2) : 0,
            'avg_c'             => $finalGrades->count() ? round((float) $finalGrades->avg('gradeC'), 2) : 0,
            'avg_d'             => $finalGrades->count() ? round((float) $finalGrades->avg('gradeD'), 2) : 0,
        ];

        $gradeDistribution = $finalGrades->groupBy('gradeA')->map(fn ($set, $g) => [
            'grade' => $g,
            'count' => $set->count(),
        ])->sortKeys()->values();

        $rejectionTypes = $progressGrades->concat($finalGrades)
            ->whereNotNull('rejection_type')
            ->groupBy('rejection_type')
            ->map(fn ($set, $type) => ['type' => ucfirst((string) $type), 'count' => $set->count()])
            ->sortByDesc('count')
            ->values();

        // ---- Budget -------------------------------------------------------
        $budgetTotal = (float) $projects->sum('requested_budget_qar');
        $budgetProjects = $projects->filter(fn ($p) => (float) $p->requested_budget_qar > 0);
        $topBudget = $projects->sortByDesc('requested_budget_qar')->take(10)
            ->filter(fn ($p) => (float) $p->requested_budget_qar > 0)
            ->map(fn ($p) => [
                'id'      => $p->old_project_id ?: $p->id,
                'title'   => mb_substr((string) $p->title, 0, 60),
                'lpi'     => $p->lpi->name ?? '—',
                'budget'  => (float) $p->requested_budget_qar,
            ])->values();

        // ---- Research output ----------------------------------------------
        $outcomes = $ids ? DB::table('project_outcomes')->whereIn('project_id', $ids)->get() : collect();
        $publicationTypes = ['journal_q1', 'journal_q2', 'journal_q3', 'journal_q4', 'conference', 'book', 'edited_book', 'book_chapter'];
        $ipTypes = ['ip_disclosure', 'provisional_patent', 'patent_granted', 'open_source_sw', 'startup', 'open_source', 'prototype'];

        $outcomeTypeLabels = [
            'journal_q1' => 'Q1 Journal', 'journal_q2' => 'Q2 Journal', 'journal_q3' => 'Q3 Journal',
            'journal_q4' => 'Q4 Journal', 'conference' => 'Conference', 'book' => 'Book',
            'edited_book' => 'Edited Book', 'book_chapter' => 'Book Chapter',
            'ip_disclosure' => 'IP Disclosure', 'provisional_patent' => 'Provisional Patent',
            'patent_granted' => 'Granted Patent', 'open_source_sw' => 'Open Source SW',
            'open_source' => 'Open Source', 'startup' => 'Start-up', 'prototype' => 'Prototype',
            'cross_college' => 'Cross-college', 'research_awards' => 'Research Awards',
        ];

        $outcomesByType = $outcomes->groupBy('type')->map(fn ($set, $type) => [
            'type'  => $type,
            'label' => $outcomeTypeLabels[$type] ?? ucfirst(str_replace('_', ' ', (string) $type)),
            'count' => $set->count(),
        ])->sortByDesc('count')->values();

        $verifiedOutcomes = $outcomes->filter(fn ($o) =>
            ($o->verifcation_by_reviewer ?? '') === 'verified' || ($o->verifcation_by_system ?? '') === 'verified'
        )->count();

        $output = [
            'publications'  => $outcomes->whereIn('type', $publicationTypes)->count(),
            'ip'            => $outcomes->whereIn('type', $ipTypes)->count(),
            'total'         => $outcomes->count(),
            'verified'      => $verifiedOutcomes,
            'pending'       => $outcomes->count() - $verifiedOutcomes,
            'by_type'       => $outcomesByType,
        ];

        // ---- People -------------------------------------------------------
        $students = $ids ? DB::table('project_students')->whereIn('project_id', $ids)->get() : collect();
        $studentsByType = $students->groupBy('type')->map(fn ($set, $type) => [
            'type'  => $type,
            'label' => $type === 'masters' ? 'Masters' : ($type === 'PhD' ? 'PhD' : ($type === 'UG' ? 'Undergraduate' : ucfirst((string) $type))),
            'count' => $set->count(),
        ])->sortByDesc('count')->values();

        $studentsByNationality = $students->groupBy(fn ($s) => strtolower((string) ($s->nationality ?? 'unspecified')))
            ->map(fn ($set, $key) => ['label' => ucfirst($key), 'count' => $set->count()])
            ->sortByDesc('count')->values();

        $researchersCount = $ids ? DB::table('project_researchers')->whereIn('project_id', $ids)->count() : 0;

        // LPI load
        $lpiLoad = $projects->whereNotNull('lpi_id')->groupBy('lpi_id')->map->count()->sortDesc();
        $lpiNames = $lpiLoad->keys()->isEmpty() ? collect() : User::whereIn('id', $lpiLoad->keys()->all())->pluck('name', 'id');
        $lpiRows = $lpiLoad->map(fn ($count, $lpiId) => [
            'name'  => $lpiNames->get($lpiId, 'Unknown'),
            'count' => $count,
        ])->take(10)->values();

        // Reviewer load
        $reviewerLoad = $ids
            ? DB::table('projects_reviewers')->whereIn('project_id', $ids)->groupBy('user_id')->selectRaw('user_id, COUNT(DISTINCT project_id) as c')->get()
            : collect();
        $reviewerIds = $reviewerLoad->pluck('user_id')->all();
        $reviewerNames = $reviewerIds ? User::whereIn('id', $reviewerIds)->pluck('name', 'id') : collect();
        $reviewerRows = $reviewerLoad->sortByDesc('c')->take(10)->map(fn ($r) => [
            'name'  => $reviewerNames->get($r->user_id, 'Unknown'),
            'count' => (int) $r->c,
        ])->values();

        // Colleges & pillars
        $collegeRows = $ids
            ? DB::table('project_college')->whereIn('project_id', $ids)->get(['college_id'])
                ->groupBy('college_id')->map->count()
            : collect();
        $collegeNames = $collegeRows->keys()->isEmpty() ? collect() : College::whereIn('id', $collegeRows->keys()->all())->pluck('name', 'id');
        $collegeDistribution = $collegeRows->map(fn ($count, $cid) => [
            'label' => $collegeNames->get($cid, 'Unknown'),
            'count' => $count,
        ])->sortByDesc('count')->take(8)->values();

        $pillarRows = $ids
            ? DB::table('project_pillar')->whereIn('project_id', $ids)->get(['pillar_id'])
                ->groupBy('pillar_id')->map->count()
            : collect();
        $pillarNames = $pillarRows->keys()->isEmpty() ? collect() : Pillar::whereIn('id', $pillarRows->keys()->all())->pluck('pillar', 'id');
        $pillarDistribution = $pillarRows->map(fn ($count, $pid) => [
            'label' => $pillarNames->get($pid, 'Unknown'),
            'count' => $count,
        ])->sortByDesc('count')->take(8)->values();

        // ---- Trend: registrations & completions per month ------------------
        $byMonth = function (array $statuses) use ($statusRows) {
            return $statusRows->whereIn('status', $statuses)
                ->groupBy(fn ($r) => date('Y-m', strtotime($r->created_at)))
                ->map->count()
                ->sortKeys();
        };
        $regTrend  = $byMonth([Project::STATUS_REGISTERED]);
        $gradedTrend = $byMonth([Project::STATUS_GRADED]);

        $months = collect(array_merge($regTrend->keys()->all(), $gradedTrend->keys()->all()))
            ->unique()->sort()->values();
        $trend = [
            'months'  => $months->all(),
            'reg'     => $months->map(fn ($m) => $regTrend->get($m, 0))->all(),
            'graded'  => $months->map(fn ($m) => $gradedTrend->get($m, 0))->all(),
            'max'     => max(1, (int) max($regTrend->max() ?? 0, $gradedTrend->max() ?? 0)),
        ];

        // ---- Project appendix ---------------------------------------------
        $appendix = $projects->map(fn ($p) => [
            'id'      => $p->old_project_id ?: $p->id,
            'title'   => mb_substr((string) $p->title, 0, 55),
            'lpi'     => $p->lpi->name ?? '—',
            'status'  => $statusLabels[$p->currentWorkflowStatus()] ?? ucfirst(str_replace('_', ' ', (string) ($p->currentWorkflowStatus() ?: 'imported'))),
            'budget'  => (float) $p->requested_budget_qar,
        ])->sortBy('id')->values();

        // ---- KPIs ----------------------------------------------------------
        $kpis = [
            ['label' => 'Total Projects',   'value' => $total],
            ['label' => 'Registered',       'value' => $hasStatus(Project::STATUS_REGISTERED)],
            ['label' => 'Graded',           'value' => $hasStatus(Project::STATUS_GRADED)],
            ['label' => 'In Progress',      'value' => $total - $hasStatus(Project::STATUS_GRADED)],
            ['label' => 'Total Budget (QAR)','value' => number_format($budgetTotal)],
            ['label' => 'Avg Final Score',  'value' => $grading['avg_total'] ?: '—'],
            ['label' => 'Students',         'value' => $students->count()],
            ['label' => 'Publications',     'value' => $output['publications']],
            ['label' => 'IP Outputs',       'value' => $output['ip']],
            ['label' => 'Researchers',      'value' => $researchersCount],
            ['label' => 'LPIs',             'value' => $projects->whereNotNull('lpi_id')->unique('lpi_id')->count()],
            ['label' => 'Reviewers',        'value' => $reviewerIds ? count($reviewerIds) : 0],
        ];

        $analysis = $this->buildAnalysis($total, $pipeline, $grading, $output, $budgetTotal, $students, $statusDistribution);

        return [
            'program'            => $program,
            'kpis'               => $kpis,
            'statusDistribution' => $statusDistribution,
            'pipeline'           => $pipeline,
            'grading'            => $grading,
            'gradeDistribution'  => $gradeDistribution,
            'rejectionTypes'     => $rejectionTypes,
            'budgetTotal'        => $budgetTotal,
            'budgetProjects'     => $budgetProjects->count(),
            'topBudget'          => $topBudget,
            'output'             => $output,
            'studentsByType'     => $studentsByType,
            'studentsByNationality' => $studentsByNationality,
            'studentsTotal'      => $students->count(),
            'researchersCount'   => $researchersCount,
            'lpiRows'            => $lpiRows,
            'reviewerRows'       => $reviewerRows,
            'collegeDistribution'=> $collegeDistribution,
            'pillarDistribution' => $pillarDistribution,
            'trend'              => $trend,
            'appendix'           => $appendix,
            'analysis'           => $analysis,
            'generatedAt'        => now()->format('d M Y H:i'),
        ];
    }

    /** Short auto-generated narrative so the booklet reads as an analysis, not just numbers. */
    private function buildAnalysis(int $total, array $pipeline, array $grading, array $output, float $budget, $students, $statusDistribution): array
    {
        $notes = [];
        if ($total === 0) {
            return ['This research call currently has no projects, so there is nothing to analyse yet.'];
        }

        $graded = collect($pipeline)->firstWhere('label', 'Graded')['count'] ?? 0;
        $completion = $total > 0 ? round(($graded / $total) * 100, 1) : 0;
        $notes[] = "Of {$total} projects in this research call, {$graded} ({$completion}%) have reached the terminal <strong>Graded</strong> status; "
            . ($total - $graded) . " remain somewhere in the pipeline.";

        $lead = $statusDistribution->first();
        if ($lead) {
            $notes[] = "The largest single workflow bucket is <strong>{$lead['label']}</strong> with {$lead['count']} project(s) ({$lead['pct']}% of the call).";
        }

        if ($grading['final_total'] > 0) {
            $pass = $grading['final_accepted'];
            $rej  = $grading['final_rejected'];
            $notes[] = "Across {$grading['final_total']} completed final grading set(s), {$pass} were accepted and {$rej} rejected, "
                . "with an average total score of {$grading['avg_total']} (section averages A {$grading['avg_a']} / B {$grading['avg_b']} / C {$grading['avg_c']} / D {$grading['avg_d']}).";
        } else {
            $notes[] = 'No final report has been graded yet for this call.';
        }

        $notes[] = "Declared research output totals {$output['publications']} publication(s) and {$output['ip']} IP/innovation output(s); "
            . "{$output['verified']} outcome record(s) have been verified and {$output['pending']} still await verification.";

        $notes[] = "Requested budget across the call is " . number_format($budget) . " QAR"
            . ($budget > 0 ? ' (average ' . number_format($budget / max(1, $total)) . ' QAR per project).' : '.');

        $notes[] = "The call involves {$students->count()} student(s) recorded to date.";

        return $notes;
    }
}
