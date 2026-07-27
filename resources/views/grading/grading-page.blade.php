@extends('layouts.app')

@section('title', 'Grading — ' . ($project->title ?? 'Project'))

@php
    $gradeReviewerPivot = DB::table('projects_reviewers')
        ->where('project_id', $project->id)
        ->where('user_id', auth()->id())
        ->first();
    $gradeLabel = 'Grade';

    $cycleTitle = $project->cycle_title ?? ($project->program->cycle->title ?? '');
    $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);
    $hasPR2 = $project->has_progress_report2 ?? false;
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-star" style="color:var(--brand-500);"></i> Project Grading</h1>
        <p>Grade the reports submitted by <strong>{{ $project->lpi->name ?? 'LPI' }}</strong>.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('projects.available') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
</div>

{{-- Research Call Inactive Banner --}}
@if($project && $project->program && !$project->programIsActive())
<div style="background:linear-gradient(135deg,#fbeef1 0%,#f3d2da 100%);border:1px solid var(--brand-200);border-radius:8px;padding:14px 18px;margin-bottom:22px;display:flex;align-items:center;gap:12px;">
    <div style="width:36px;height:36px;border-radius:50%;background:var(--brand-500);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">
        <i class="fas fa-lock"></i>
    </div>
    <div>
        <strong style="color:var(--brand-800);font-size:14px;">Research Call Inactive</strong>
        <p style="margin:2px 0 0 0;color:var(--brand-700);font-size:13px;">
            The research call <strong>{{ $project->program->program_title }}</strong> is no longer active. Grading cannot be submitted.
        </p>
    </div>
</div>
@endif

{{-- ========== TABBED FORM (Fluent Workspace UI) ========== --}}
<div class="ws-tabs" role="tablist">
    <button type="button" class="ws-tab active" role="tab" data-tab="tab-proposal" onclick="switchTab('tab-proposal', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Project Proposal
    </button>
    <button type="button" class="ws-tab" role="tab" data-tab="tab-progress" onclick="switchTab('tab-progress', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Progress Report
    </button>
    <button type="button" class="ws-tab" role="tab" data-tab="tab-final" onclick="switchTab('tab-final', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Final Report
    </button>
    <button type="button" class="ws-tab" role="tab" data-tab="tab-readiness" onclick="switchTab('tab-readiness', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        QU Readiness Map
    </button>
    <button type="button" class="ws-tab" role="tab" data-tab="tab-review" onclick="switchTab('tab-review', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Review & Submit
    </button>
</div>

{{-- ========== TAB PANELS ========== --}}

{{-- Project Proposal --}}
<div class="ws-tab-panel" id="tab-proposal" style="display:block;">
    <div class="ws-split">
        <div class="ws-pdf-col">
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> Project Proposal</span>
                    <a href="{{ route('serveFile2', ['type' => 'proposal', 'id' => $project->id]) }}" target="_blank" class="ws-btn ws-btn-outline" style="font-size:11px;padding:4px 8px;">
                        <i class="fas fa-external-link-alt"></i> Open
                    </a>
                </div>
                <iframe class="ws-iframe" src="{{ route('serveFile2', ['type' => 'proposal', 'id' => $project->id]) }}#toolbar=0&navpanes=0&view=FitH"></iframe>
            </div>
        </div>
        <div class="ws-form-col">
            <div class="ws-card">
                <div class="ws-section-title"><span><i class="fas fa-list-check"></i> Commitments vs Outcomes</span></div>
                @php
                    // Map commitment fields to outcome types for counting
                    $commitOutcomeMap = [
                        'q1article'   => ['label' => 'Q1 Articles',              'outcome_type' => 'journal_q1'],
                        'q2article'   => ['label' => 'Q2 Articles',              'outcome_type' => 'journal_q2'],
                        'q3article'   => ['label' => 'Q3 Articles',              'outcome_type' => 'journal_q3'],
                        'q4article'   => ['label' => 'Q4 Articles',              'outcome_type' => 'journal_q4'],
                        'confArticle' => ['label' => 'Conference Articles',       'outcome_type' => 'conference'],
                        'books'       => ['label' => 'Books',                     'outcome_type' => 'book'],
                        'editBooks'   => ['label' => 'Edited Books',              'outcome_type' => 'edited_book'],
                        'chapters'    => ['label' => 'Book Chapters',             'outcome_type' => 'book_chapter'],
                        'ip'          => ['label' => 'IP Disclosure',             'outcome_type' => 'ip_disclosure'],
                        'filedPatent' => ['label' => 'Filed Patents',             'outcome_type' => 'provisional_patent'],
                        'grantedPatent' => ['label' => 'Granted Patents',         'outcome_type' => 'granted_patent'],
                        'prototype'   => ['label' => 'Prototypes',                'outcome_type' => 'prototype'],
                        'opensource'  => ['label' => 'Open Source Software',      'outcome_type' => 'open_source'],
                        'openSourceSW'=> ['label' => 'Open Source SW',            'outcome_type' => 'open_source_sw'],
                        'startUp'     => ['label' => 'Startups',                  'outcome_type' => 'startup'],
                        'master'      => ['label' => 'Masters Students',          'outcome_type' => null],
                        'UG'          => ['label' => 'Undergraduate Students',    'outcome_type' => null],
                        'Phd'         => ['label' => 'PhD Students',              'outcome_type' => null],
                        'crossCollege'=> ['label' => 'Cross-College Participation','outcome_type' => 'cross_college'],
                        'ethical'     => ['label' => 'Ethical Approvals',         'outcome_type' => null],
                    ];

                    // Count outcomes by type
                    $outcomeCounts = [];
                    foreach ($outcomes as $o) {
                        $type = $o->type;
                        if (!isset($outcomeCounts[$type])) $outcomeCounts[$type] = 0;
                        $outcomeCounts[$type]++;
                    }

                    // Build rows
                    $commitRows = [];
                    if ($commitments) {
                        foreach ($commitOutcomeMap as $field => $info) {
                            $committed = (int)($commitments->$field ?? 0);
                            if ($committed === 0) continue;
                            $outcomeType = $info['outcome_type'];
                            $achieved = $outcomeType ? (int)($outcomeCounts[$outcomeType] ?? 0) : 0;
                            $deficiency = $committed - $achieved;
                            $commitRows[] = [
                                'label'     => $info['label'],
                                'committed' => $committed,
                                'achieved'  => $achieved,
                                'deficiency'=> $deficiency,
                                'status'    => $deficiency <= 0 ? 'met' : 'short',
                            ];
                        }
                    }
                @endphp
                @if(count($commitRows))
                    <div style="overflow-x:auto;">
                        <table class="ws-table">
                            <thead>
                                <tr>
                                    <th>Commitment</th>
                                    <th style="text-align:center;">Committed</th>
                                    <th style="text-align:center;">Outcomes</th>
                                    <th style="text-align:center;">Deficiency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commitRows as $row)
                                <tr>
                                    <td style="font-weight:500;">{{ $row['label'] }}</td>
                                    <td style="text-align:center;">{{ $row['committed'] }}</td>
                                    <td style="text-align:center;">
                                        @if($row['status'] === 'met')
                                            <span style="display:inline-flex;align-items:center;gap:4px;color:var(--success);font-weight:600;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                {{ $row['achieved'] }}
                                            </span>
                                        @else
                                            <span style="color:var(--ink-700);font-weight:500;">{{ $row['achieved'] }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if($row['deficiency'] <= 0)
                                            <span style="display:inline-flex;align-items:center;gap:4px;color:var(--success);font-weight:600;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                Met
                                            </span>
                                        @else
                                            <span style="color:var(--danger);font-weight:600;">-{{ $row['deficiency'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Narrative deficiency summary --}}
                    @php
                        $metCount = 0;
                        $shortCount = 0;
                        $totalDeficit = 0;
                        $minDeficit = null;
                        $maxDeficit = 0;
                        foreach ($commitRows as $row) {
                            if ($row['status'] === 'met') {
                                $metCount++;
                            } else {
                                $shortCount++;
                                $totalDeficit += $row['deficiency'];
                                if ($minDeficit === null || $row['deficiency'] < $minDeficit) $minDeficit = $row['deficiency'];
                                if ($row['deficiency'] > $maxDeficit) $maxDeficit = $row['deficiency'];
                            }
                        }
                    @endphp
                    <div style="margin-top:12px;font-size:12px;font-style:italic;color:var(--ink-500);line-height:1.6;">
                        @if($shortCount === 0)
                            <span style="color:var(--success);font-weight:600;font-style:normal;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:3px;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                All {{ $metCount }} commitment(s) have been met or exceeded.
                            </span>
                        @else
                            Out of <strong>{{ count($commitRows) }}</strong> commitment(s),
                            <strong>{{ $metCount }}</strong> met and <strong>{{ $shortCount }}</strong> short
                            with a total deficiency of <strong>{{ $totalDeficit }}</strong>,
                            with minimum deficiency of <strong>{{ $minDeficit }}</strong> and maximum of <strong>{{ $maxDeficit }}</strong> per commitment.
                        @endif
                    </div>
                @else
                    <p style="color:var(--ink-400);font-size:13px;margin:0;">No commitments recorded.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Progress Report --}}
<div class="ws-tab-panel" id="tab-progress" style="display:none;">
    <div class="ws-split">
        <div class="ws-pdf-col">
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> Progress Report</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        @php $progressVersions = $submissions->where('type', 'progress')->sortByDesc('version'); @endphp
                        @if($progressVersions->count() > 1)
                        <select class="ws-version-select" data-iframe="progress-iframe" onchange="switchVersion(this, 'progress-iframe')" style="font-size:11px;padding:3px 6px;border:1px solid var(--ink-200);border-radius:6px;background:#fff;max-width:260px;">
                            @foreach($progressVersions as $pv)
                            <option value="{{ $pv->id }}" {{ $loop->first ? 'selected' : '' }}>v{{ $pv->version }} — {{ $pv->stored_filename }}</option>
                            @endforeach
                        </select>
                        @elseif($progressVersions->count() === 1)
                            <span style="font-size:11px;color:var(--ink-400);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $progressVersions->first()->stored_filename }}</span>
                        @else
                            <span style="font-size:11px;color:var(--ink-400);">No files uploaded</span>
                        @endif
                        <a href="{{ route('serveFile2', ['type' => 'progress', 'id' => $project->id]) }}" target="_blank" class="ws-btn ws-btn-outline" style="font-size:11px;padding:4px 8px;">
                            <i class="fas fa-external-link-alt"></i> Open
                        </a>
                    </div>
                </div>
                <iframe id="progress-iframe" class="ws-iframe" src="{{ $progressVersions->count() > 0 ? route('serveFile2', ['submission_id' => $progressVersions->first()->id]) : route('serveFile2', ['type' => 'progress', 'id' => $project->id]) }}#toolbar=0&navpanes=0&view=FitH"></iframe>
            </div>
        </div>
        <div class="ws-form-col">
            @include('grading.partials.progress-grade', [
                'report' => null,
                'grading' => $progressGrading ?? null,
                'index' => 1,
                'project' => $project,
                'showSubmitGrade' => $progressGrading && $progressGrading->isAccepted !== null && !$project->hasStatus(\App\Models\Project::STATUS_GRADED)
            ])
        </div>
    </div>
</div>

{{-- Final Report --}}
<div class="ws-tab-panel" id="tab-final" style="display:none;">
    <div class="ws-split">
        <div class="ws-pdf-col">
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> Final Report</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        @php $finalVersions = $submissions->where('type', 'final')->sortByDesc('version'); @endphp
                        @if($finalVersions->count() > 1)
                        <select class="ws-version-select" data-iframe="final-iframe" onchange="switchVersion(this, 'final-iframe')" style="font-size:11px;padding:3px 6px;border:1px solid var(--ink-200);border-radius:6px;background:#fff;max-width:260px;">
                            @foreach($finalVersions as $fv)
                            <option value="{{ $fv->id }}" {{ $loop->first ? 'selected' : '' }}>v{{ $fv->version }} — {{ $fv->stored_filename }}</option>
                            @endforeach
                        </select>
                        @elseif($finalVersions->count() === 1)
                            <span style="font-size:11px;color:var(--ink-400);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $finalVersions->first()->stored_filename }}</span>
                        @else
                            <span style="font-size:11px;color:var(--ink-400);">No files uploaded</span>
                        @endif
                        <a href="{{ route('serveFile2', ['type' => 'final', 'id' => $project->id]) }}" target="_blank" class="ws-btn ws-btn-outline" style="font-size:11px;padding:4px 8px;">
                            <i class="fas fa-external-link-alt"></i> Open
                        </a>
                    </div>
                </div>
                <iframe id="final-iframe" class="ws-iframe" src="{{ $finalVersions->count() > 0 ? route('serveFile2', ['submission_id' => $finalVersions->first()->id]) : route('serveFile2', ['type' => 'final', 'id' => $project->id]) }}#toolbar=0&navpanes=0&view=FitH"></iframe>
            </div>
        </div>
        <div class="ws-form-col">
            @include('grading.partials.final-grades', [
                'grading' => $finalGrading,
                'project' => $project
            ])
        </div>
    </div>
</div>

{{-- QU Readiness Map --}}
<div class="ws-tab-panel" id="tab-readiness" style="display:none;">
    <div class="ws-card">
        <div class="ws-section-title">
            <span><i class="fas fa-file-pdf"></i> QU Readiness Map</span>
            <div style="display:flex;align-items:center;gap:6px;">
                @php $readinessVersions = $submissions->where('type', 'readiness')->sortByDesc('version'); @endphp
                @if($readinessVersions->count() > 1)
                <select class="ws-version-select" data-iframe="readiness-iframe" onchange="switchVersion(this, 'readiness-iframe')" style="font-size:11px;padding:3px 6px;border:1px solid var(--ink-200);border-radius:6px;background:#fff;max-width:260px;">
                    @foreach($readinessVersions as $rv)
                    <option value="{{ $rv->id }}" {{ $loop->first ? 'selected' : '' }}>v{{ $rv->version }} — {{ $rv->stored_filename }}</option>
                    @endforeach
                </select>
                @elseif($readinessVersions->count() === 1)
                    <span style="font-size:11px;color:var(--ink-400);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $readinessVersions->first()->stored_filename }}</span>
                @else
                    <span style="font-size:11px;color:var(--ink-400);">No files uploaded</span>
                @endif
                <a href="{{ route('serveFile2', ['type' => 'readiness', 'id' => $project->id]) }}" target="_blank" class="ws-btn ws-btn-outline" style="font-size:11px;padding:4px 8px;">
                    <i class="fas fa-external-link-alt"></i> Open
                </a>
            </div>
        </div>
        <iframe id="readiness-iframe" class="ws-iframe" src="{{ $readinessVersions->count() > 0 ? route('serveFile2', ['submission_id' => $readinessVersions->first()->id]) : route('serveFile2', ['type' => 'readiness', 'id' => $project->id]) }}#toolbar=0&navpanes=0&view=FitH"></iframe>
    </div>
</div>

{{-- Review & Submit --}}
<div class="ws-tab-panel" id="tab-review" style="display:none;">
    <div class="ws-card">
        <div class="ws-section-title">
            <span><i class="fas fa-check-circle"></i> Review & Submit Grade</span>
        </div>

        <p style="color:var(--ink-500);font-size:13px;margin:0 0 16px;line-height:1.5;">
            Review your grading decisions and submit the final grade. Once submitted, the project status will be marked as <strong>Graded</strong> in the workflow.
        </p>

        {{-- Summary of saved grades --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px;">
            {{-- Progress Report Grade Summary --}}
            <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="font-size:12px;font-weight:600;color:var(--ink-800);margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-star" style="color:var(--brand-500);"></i> Progress Report
                </div>
                @if($progressGrading && $progressGrading->isAccepted !== null)
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                        @if($progressGrading->isAccepted == 1)
                            <span style="display:inline-flex;align-items:center;gap:4px;color:var(--success);font-weight:600;font-size:12px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                Accepted
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;color:var(--danger);font-weight:600;font-size:12px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                Rejected
                            </span>
                        @endif
                    </div>
                    @php
                        $pgSections = [
                            'A' => ['label' => 'Scientific Merit', 'grade' => $progressGrading->achievementsRating ?? null, 'comment' => $progressGrading->achievementsComments ?? null],
                            'B' => ['label' => 'Methodology', 'grade' => $progressGrading->publicationsRating ?? null, 'comment' => $progressGrading->publicationsComments ?? null],
                            'C' => ['label' => 'Progress vs Plan', 'grade' => $progressGrading->studentsRating ?? null, 'comment' => $progressGrading->studentsComments ?? null],
                            'D' => ['label' => 'Budget Compliance', 'grade' => $progressGrading->budgetRating ?? null, 'comment' => $progressGrading->budgetComments ?? null],
                        ];
                    @endphp
                    @foreach($pgSections as $key => $s)
                        <div style="margin-top:6px;padding-top:6px;border-top:1px solid var(--ink-100);">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-weight:600;color:var(--ink-700);font-size:11px;">{{ $s['label'] }}</span>
                                <strong style="color:var(--brand-600);font-size:14px;">{{ $s['grade'] ?? '—' }}/5</strong>
                            </div>
                            @if($s['comment'])
                                <div style="margin-top:2px;color:var(--ink-500);font-size:10.5px;font-style:italic;">{{ $s['comment'] }}</div>
                            @endif
                        </div>
                    @endforeach
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--ink-100);display:flex;justify-content:space-between;align-items:center;">
                        <span class="ws-mini-label">Total</span>
                        <strong style="color:var(--brand-600);font-size:15px;">
                            @php
                                $pgTotal = collect([
                                    $progressGrading->achievementsRating,
                                    $progressGrading->publicationsRating,
                                    $progressGrading->studentsRating,
                                    $progressGrading->budgetRating
                                ])->sum();
                            @endphp
                            {{ $pgTotal > 0 ? $pgTotal : '—' }}
                        </strong>
                    </div>
                @else
                    <div style="font-size:12px;color:var(--ink-400);">
                        <i class="fas fa-exclamation-triangle" style="color:var(--warning);margin-right:4px;"></i>
                        Not yet graded. Please complete the Progress Report tab first.
                    </div>
                @endif
            </div>

            {{-- Final Report Grade Summary --}}
            <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="font-size:12px;font-weight:600;color:var(--ink-800);margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-star" style="color:var(--brand-500);"></i> Final Report
                </div>
                @if($finalGrading && $finalGrading->isAccepted !== null)
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                        @if($finalGrading->isAccepted == 1)
                            <span style="display:inline-flex;align-items:center;gap:4px;color:var(--success);font-weight:600;font-size:12px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                Accepted
                            </span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;color:var(--danger);font-weight:600;font-size:12px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                Rejected
                            </span>
                        @endif
                    </div>
                    @php
                        $fgSections = [
                            'A' => ['label' => 'Achievements against objectives', 'grade' => $finalGrading->gradeA ?? null, 'comment' => $finalGrading->commentA ?? null],
                            'B' => ['label' => 'Publications & IP', 'grade' => $finalGrading->gradeB ?? null, 'comment' => $finalGrading->commentB ?? null],
                            'C' => ['label' => 'Student & Young Researcher Involvement', 'grade' => $finalGrading->gradeC ?? null, 'comment' => $finalGrading->commentC ?? null],
                            'D' => ['label' => 'Project Impact', 'grade' => $finalGrading->gradeD ?? null, 'comment' => $finalGrading->commentD ?? null],
                        ];
                    @endphp
                    @foreach($fgSections as $key => $s)
                        <div style="margin-top:6px;padding-top:6px;border-top:1px solid var(--ink-100);">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-weight:600;color:var(--ink-700);font-size:11px;">{{ $s['label'] }}</span>
                                <strong style="color:var(--brand-600);font-size:14px;">{{ $s['grade'] ?? '—' }}/5</strong>
                            </div>
                            @if($s['comment'])
                                <div style="margin-top:2px;color:var(--ink-500);font-size:10.5px;font-style:italic;">{{ $s['comment'] }}</div>
                            @endif
                        </div>
                    @endforeach
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--ink-100);display:flex;justify-content:space-between;align-items:center;">
                        <span class="ws-mini-label">Total</span>
                        <strong style="color:var(--brand-600);font-size:15px;">{{ $finalGrading->total ?? '—' }}</strong>
                    </div>
                @else
                    <div style="font-size:12px;color:var(--ink-400);">
                        <i class="fas fa-exclamation-triangle" style="color:var(--warning);margin-right:4px;"></i>
                        Not yet graded. Please complete the Final Report tab first.
                    </div>
                @endif
            </div>
        </div>

        {{-- Submit Grade Button --}}
        <div style="display:flex;justify-content:center;padding-top:16px;border-top:1px solid var(--ink-100);">
            @if($project->hasStatus(\App\Models\Project::STATUS_GRADED))
                <div style="display:flex;align-items:center;gap:8px;padding:12px 20px;background:#e8f5ee;border:1px solid #a8e6b8;border-radius:8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <span style="font-size:14px;font-weight:600;color:var(--success);">Grade Already Submitted</span>
                </div>
            @else
                <button type="button" class="ws-btn ws-btn-primary" id="submitGradeBtn" onclick="submitFinalGrade()" style="font-size:14px;padding:10px 24px;">
                    <i class="fas fa-check-circle"></i> Submit Grade
                </button>
            @endif
        </div>
    </div>
</div>

{{-- Help modals --}}
@include('grading.partials.help-modals')

@endsection

@push('styles')
<style>
/* ─── Page head (matches Add Progress) ─── */
.page-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.page-head h1 {
    font-size: 20px;
    font-weight: 600;
    color: var(--ink-800);
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.page-head p { margin: 0; color: var(--ink-500); font-size: 12.5px; }
.page-actions { display: flex; gap: 10px; align-items: center; }
.btn-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff; color: var(--ink-600);
    border: 1px solid var(--ink-200); border-radius: 6px;
    padding: 6px 11px; font-size: 12px; font-weight: 600;
    text-decoration: none; cursor: pointer;
    box-shadow: var(--fluent-depth-2);
}
.btn-secondary:hover { background: var(--ink-50); border-color: var(--ink-300); }

/* ─── Workspace tabs (identical to Add Progress) ─── */
.ws-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid var(--ink-100);
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.ws-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border: none;
    background: transparent;
    color: var(--ink-500);
    font-size: 12.5px;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color .15s, border-color .15s;
    font-family: inherit;
}
.ws-tab:hover { color: var(--brand-500); }
.ws-tab.active {
    color: var(--brand-600);
    border-bottom-color: var(--brand-500);
    font-weight: 600;
}
.ws-tab svg { opacity: .8; }

.ws-tab-panel { animation: fadeIn .2s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

/* ─── Cards ─── */
.ws-card {
    background: #fff;
    border: 1px solid var(--ink-100);
    border-radius: 8px;
    padding: 14px;
    margin-bottom: 14px;
    box-shadow: var(--fluent-depth-2);
}
.ws-section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--ink-800);
    margin-bottom: 11px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--ink-100);
}
.ws-section-title span { display: inline-flex; align-items: center; gap: 8px; }
.ws-section-title i { color: var(--brand-500); }

/* ─── Mini label & fields ─── */
.ws-mini-label {
    font-size: 11px; text-transform: uppercase; letter-spacing: .06em;
    color: var(--ink-400); font-weight: 600;
}
.ws-field-label {
    display: block; font-size: 11.5px; font-weight: 600;
    color: var(--ink-600); margin-bottom: 5px;
}
.ws-input {
    width: 100%;
    border: 1px solid var(--ink-200);
    border-radius: 6px;
    padding: 6px 9px;
    font-size: 12px;
    font-family: inherit;
    color: var(--ink-800);
    background: #fff;
}
.ws-input:focus { outline: none; border-color: var(--brand-400); box-shadow: 0 0 0 3px var(--brand-50); }

/* ─── Buttons ─── */
.ws-btn {
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 6px; padding: 6px 12px; font-size: 12px; font-weight: 600;
    cursor: pointer; border: 1px solid transparent; font-family: inherit;
    text-decoration: none;
}
.ws-btn-primary { background: var(--brand-500); color: #fff; border-color: var(--brand-600); box-shadow: var(--fluent-depth-2); }
.ws-btn-primary:hover { background: var(--brand-600); box-shadow: var(--fluent-depth-4); }
.ws-btn-outline { background: #fff; color: var(--brand-600); border-color: var(--brand-200); }
.ws-btn-outline:hover { background: var(--brand-50); }
.ws-form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }

/* ─── Pills & badges ─── */
.ws-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11.5px; font-weight: 600; padding: 3px 9px;
    border-radius: 999px; border: 1px solid transparent;
}
.ws-pill-brand { background: var(--brand-50); color: var(--brand-600); border-color: var(--brand-200); }
.ws-pill-success { background: #e6f4ea; color: var(--success); border-color: #a8e6b8; }
.ws-pill-warning { background: #fef7e0; color: var(--warning); border-color: #fce8b2; }
.ws-pill-ink { background: var(--ink-50); color: var(--ink-500); border-color: var(--ink-100); }

/* ─── Table ─── */
.ws-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.ws-table th {
    text-align: left; padding: 7px 10px; font-weight: 600; font-size: 10.5px;
    text-transform: uppercase; letter-spacing: .06em; color: var(--ink-400);
    border-bottom: 1px solid var(--ink-100); background: var(--sand-50);
}
.ws-table td { padding: 7px 10px; border-bottom: 1px solid var(--ink-100); color: var(--ink-700); }
.ws-table tbody tr:hover { background: var(--ink-50); }

/* ─── Iframe ─── */
.ws-iframe {
    width: 100%;
    height: 70vh;
    min-height: 480px;
    border: 1px solid var(--ink-100);
    border-radius: 8px;
    background: var(--sand-50);
}

/* ─── 70/30 split: PDF (left) + form (right) ─── */
.ws-split {
    display: grid;
    grid-template-columns: 70% 30%;
    gap: 18px;
    align-items: start;
}
.ws-split .ws-pdf-col { min-width: 0; }
.ws-split .ws-form-col { min-width: 0; }
@media (max-width: 900px) {
    .ws-split { grid-template-columns: 1fr; }
}

/* ─── Toggle switch (Ethical Approval) ─── */
.ws-toggle {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 24px;
    cursor: pointer;
}
.ws-toggle input { opacity: 0; width: 0; height: 0; }
.ws-toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--ink-300);
    border-radius: 999px;
    transition: background .2s;
}
.ws-toggle-slider::before {
    content: "";
    position: absolute;
    height: 18px; width: 18px;
    left: 3px; top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
    box-shadow: var(--fluent-depth-2);
}
.ws-toggle input:checked + .ws-toggle-slider {
    background: var(--brand-500);
}
.ws-toggle input:checked + .ws-toggle-slider::before {
    transform: translateX(22px);
}

/* ─── Grade radio row ─── */
.ws-grade-row { display: flex; gap: 6px; justify-content: center; }
.ws-grade-opt { cursor: pointer; }
.ws-grade-opt input { position: absolute; opacity: 0; }
.ws-grade-opt span {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 6px;
    border: 1px solid var(--ink-200); background: #fff;
    font-weight: 600; font-size: 13px; color: var(--ink-600); transition: all .15s;
}
.ws-grade-opt:hover span { border-color: var(--brand-300); color: var(--brand-600); }
.ws-grade-opt input:checked + span {
    background: var(--brand-500); color: #fff; border-color: var(--brand-600);
    box-shadow: var(--fluent-depth-4);
}

/* ─── Help link ─── */
.ws-help {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11.5px; font-weight: 600; color: var(--brand-600);
    background: var(--brand-50); border: 1px solid var(--brand-200);
    border-radius: 6px; padding: 5px 10px; cursor: pointer; text-decoration: none;
}
.ws-help:hover { background: var(--brand-100); }

/* ─── Help modal (custom WS) ─── */
.ws-modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(22,19,26,.45);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.ws-modal {
    background: #fff; border-radius: 8px; width: 100%; max-width: 480px;
    box-shadow: var(--fluent-depth-16); overflow: hidden;
    animation: modalIn .18s ease-out;
}
@keyframes modalIn { from { transform: translateY(8px); opacity: 0; } to { transform: none; opacity: 1; } }
.ws-modal-head {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--brand-500); color: #fff; padding: 13px 18px;
    font-size: 15px; font-weight: 600;
}
.ws-modal-head span { display: inline-flex; align-items: center; gap: 8px; }
.ws-modal-close {
    background: transparent; border: none; color: #fff; font-size: 22px;
    line-height: 1; cursor: pointer; opacity: .85;
}
.ws-modal-close:hover { opacity: 1; }
.ws-modal-body { padding: 16px 18px; font-size: 13px; color: var(--ink-600); }
.ws-modal-body ul { margin: 6px 0 0; padding-left: 18px; }
.ws-modal-body li { margin-bottom: 3px; }

/* ─── Toast ─── */
/* (moved to centralized showToast in layout) */

.animate-spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.switchTab = function(tabId, btn) {
        document.querySelectorAll('.ws-tab-panel').forEach(function(p) { p.style.display = 'none'; });
        document.querySelectorAll('.ws-tab').forEach(function(t) { t.classList.remove('active'); });
        btn.classList.add('active');
        var panel = document.getElementById(tabId);
        if (panel) panel.style.display = 'block';
    };
    var firstTab = document.querySelector('.ws-tab.active');
    if (firstTab) firstTab.click();

    // Ethical approval toggle label update
    document.querySelectorAll('input[name="ethical"]').forEach(function(toggle) {
        function updateLabel() {
            var wrap = toggle.closest('div');
            var noLabel = wrap.querySelector('#ethicalLabel');
            var yesLabel = wrap.querySelector('#ethicalYesLabel');
            if (toggle.checked) {
                if (noLabel) noLabel.style.opacity = '.4';
                if (yesLabel) yesLabel.style.opacity = '1';
            } else {
                if (noLabel) noLabel.style.opacity = '1';
                if (yesLabel) yesLabel.style.opacity = '.4';
            }
        }
        toggle.addEventListener('change', updateLabel);
        updateLabel();
    });

    window.switchVersion = function(select, iframeId) {
        var submissionId = select.value;
        var iframe = document.getElementById(iframeId);
        if (iframe) {
            iframe.src = '{{ route("serveFile2") }}?submission_id=' + submissionId + '#toolbar=0&navpanes=0&view=FitH';
        }
    };

    window.openHelp = function(id) {
        var m = document.getElementById(id);
        if (m) m.style.display = 'flex';
    };
    window.closeHelp = function(id) {
        var m = document.getElementById(id);
        if (m) m.style.display = 'none';
    };
    document.querySelectorAll('.ws-modal-overlay').forEach(function(ov) {
        ov.addEventListener('click', function(e) {
            if (e.target === ov) ov.style.display = 'none';
        });
    });

    // Progress grade AJAX submit
    document.querySelectorAll('form[data-progress-grade]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.activeElement;
            if (!btn || !btn.matches('button[type="submit"]')) {
                btn = form.querySelector('button[type="submit"]');
            }
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin" style="margin-right:6px;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="32"/></svg> Saving…';

            var formData = new FormData(form);
            if (btn && btn.name === 'save_action') {
                formData.set('save_action', btn.value);
            }
            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || '', 'Accept': 'application/json' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('success', 'Progress grade saved.');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showToast('error', data.error || 'Failed to save.');
                    btn.disabled = false; btn.innerHTML = original;
                }
            })
            .catch(function(err) {
                showToast('error', 'Network error: ' + err.message);
                btn.disabled = false; btn.innerHTML = original;
            });
        });
    });

    // Final grade AJAX submit
    document.querySelectorAll('form[data-final-grade]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.activeElement;
            if (!btn || !btn.matches('button[type="submit"]')) {
                btn = form.querySelector('button[type="submit"]');
            }
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin" style="margin-right:6px;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="32"/></svg> Saving…';

            var formData = new FormData(form);
            if (btn && btn.name === 'save_action') {
                formData.set('save_action', btn.value);
            }
            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || '', 'Accept': 'application/json' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('success', 'Final grade saved.');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showToast('error', data.error || 'Failed to save.');
                    btn.disabled = false; btn.innerHTML = original;
                }
            })
            .catch(function(err) {
                showToast('error', 'Network error: ' + err.message);
                btn.disabled = false; btn.innerHTML = original;
            });
        });
    });

    // Submit Grade — marks project as Graded in workflow
    window.submitFinalGrade = function() {
        var btn = document.getElementById('submitGradeBtn');
        if (!btn) return;
        btn.disabled = true;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin" style="margin-right:6px;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="32"/></svg> Submitting…';

        var csrf = document.querySelector('meta[name="csrf-token"]');
        var projectId = {{ $project->id }};

        fetch('/grading/' + projectId + '/submit-grade', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.content : ''
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('success', 'Grade submitted successfully! Project marked as Graded.');
                setTimeout(function() { location.reload(); }, 1500);
            } else {
                showToast('error', data.error || 'Failed to submit grade.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Submit Grade';
            }
        })
        .catch(function(err) {
            showToast('error', 'Network error: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Submit Grade';
        });
    };
});
</script>
@endpush
