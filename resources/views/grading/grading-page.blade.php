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

    // Check file existence instead of status (buttons removed, files uploaded directly)
    $progressSubmitted = $submissions->where('type', 'progress')->count() > 0;
    $finalSubmitted = $submissions->where('type', 'final')->count() > 0;

    $isProgressStep = $progressSubmitted && !$project->hasStatus(\App\Models\Project::STATUS_PROGRESS_REVIEWED);
    $isFinalStep    = $finalSubmitted && !$project->hasStatus(\App\Models\Project::STATUS_GRADED);

    // Deadline-gated: only show progress/final tabs when deadlines have passed
    $showProgressTab = $progressDeadlinePassed;
    $showFinalTab = $finalDeadlinePassed;

    $progressVersions = $submissions->where('type', 'progress')->sortByDesc('version');
    $finalVersions = $submissions->where('type', 'final')->sortByDesc('version');
    $readinessVersions = $submissions->where('type', 'readiness')->sortByDesc('version');

    // Progress Report 2 (extended progress) state — mirrors progress report 1
    $progress2Versions = ($progress2Versions ?? $submissions->where('type', 'progress2'))->sortByDesc('version');
    $isProgress2Step = $progress2Submitted
        && !$project->hasStatus(\App\Models\Project::STATUS_PROGRESS2_REVIEWED)
        && !$project->hasStatus(\App\Models\Project::STATUS_PROGRESS2_REJECTED);
    $showProgress2Tab = ($project->extended_progress ?? false) && ($progress2DeadlinePassed ?? false);

    // Determine default left tab based on project state
    $defaultLeftTab = 'ltab-proposal';
    if ($finalSubmitted && $showFinalTab) {
        $defaultLeftTab = 'ltab-final';
    } elseif ($progressSubmitted && $showProgressTab) {
        $defaultLeftTab = 'ltab-progress';
    } elseif ($proposalExists) {
        $defaultLeftTab = 'ltab-proposal';
    }
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>
            <i class="fas fa-star" style="color:var(--brand-500);"></i>
            @if($isProgressStep)
                Progress Grading
            @elseif($isFinalStep)
                Final Grading
            @else
                Project Grading
            @endif
        </h1>
        <p>Grade the reports submitted by <strong>{{ $project->lpi->name ?? 'LPI' }}</strong>.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('projects.available') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     UNIFIED SPLIT LAYOUT — Left: PDF/Data tabs | Right: Grading form
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="ws-split">

    {{-- ─── LEFT COLUMN: Document & Data Tabs ─── --}}
    <div class="ws-pdf-col">
        <div class="ws-left-tabs" role="tablist">
            <button type="button" class="ws-tab {{ $defaultLeftTab === 'ltab-proposal' ? 'active' : '' }}" role="tab" data-tab="ltab-proposal" onclick="switchLeftTab('ltab-proposal', this)">
                <i class="fas fa-file-pdf"></i> Proposal
            </button>
            <button type="button" class="ws-tab {{ $defaultLeftTab === 'ltab-commitments' ? 'active' : '' }}" role="tab" data-tab="ltab-commitments" onclick="switchLeftTab('ltab-commitments', this)">
                <i class="fas fa-handshake"></i> Commitments
            </button>
            @if($showProgressTab)
            <button type="button" class="ws-tab {{ $defaultLeftTab === 'ltab-progress' ? 'active' : '' }}" role="tab" data-tab="ltab-progress" onclick="switchLeftTab('ltab-progress', this)">
                <i class="fas fa-chart-line"></i> Progress Report
            </button>
            @endif
            @if($showProgress2Tab)
            <button type="button" class="ws-tab {{ $defaultLeftTab === 'ltab-progress2' ? 'active' : '' }}" role="tab" data-tab="ltab-progress2" onclick="switchLeftTab('ltab-progress2', this)">
                <i class="fas fa-chart-line"></i> Progress Report 2
            </button>
            @endif
            @if($showFinalTab)
            <button type="button" class="ws-tab {{ $defaultLeftTab === 'ltab-final' ? 'active' : '' }}" role="tab" data-tab="ltab-final" onclick="switchLeftTab('ltab-final', this)">
                <i class="fas fa-check-circle"></i> Final Report
            </button>
            @endif
            @if($showFinalTab)
            <button type="button" class="ws-tab {{ $defaultLeftTab === 'ltab-readiness' ? 'active' : '' }}" role="tab" data-tab="ltab-readiness" onclick="switchLeftTab('ltab-readiness', this)">
                <i class="fas fa-map"></i> Readiness Report
            </button>
            @endif
            <button type="button" class="ws-tab {{ $defaultLeftTab === 'ltab-outcomes' ? 'active' : '' }}" role="tab" data-tab="ltab-outcomes" onclick="switchLeftTab('ltab-outcomes', this)">
                <i class="fas fa-trophy"></i> Outcomes
            </button>
            <button type="button" class="ws-tab {{ $defaultLeftTab === 'ltab-ethical' ? 'active' : '' }}" role="tab" data-tab="ltab-ethical" onclick="switchLeftTab('ltab-ethical', this)">
                <i class="fas fa-shield-alt"></i> Ethical Approval
            </button>
        </div>

        {{-- Tab: Project Proposal --}}
        <div class="ws-left-tab-panel" id="ltab-proposal" style="display:{{ $defaultLeftTab === 'ltab-proposal' ? 'block' : 'none' }};">
            @if(!$proposalExists)
            <div class="ws-card" style="max-width:620px;margin:32px auto;text-align:center;padding:36px 28px;">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--sand-50);color:var(--ink-400);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-lock" style="font-size:24px;"></i>
                </div>
                <h3 style="font-size:16px;font-weight:600;color:var(--ink-800);margin:0 0 8px;">Proposal Not Available</h3>
                <p style="font-size:13px;color:var(--ink-500);margin:0;line-height:1.55;">
                    No proposal has been uploaded for this project yet.
                </p>
            </div>
            @else
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> Project Proposal</span>
                    <a href="{{ route('serveFile2', ['type' => 'proposal', 'id' => $project->id]) }}" target="_blank" class="ws-btn ws-btn-outline" style="font-size:11px;padding:4px 8px;">
                        <i class="fas fa-external-link-alt"></i> Open
                    </a>
                </div>
                <iframe class="ws-iframe" src="{{ route('serveFile2', ['type' => 'proposal', 'id' => $project->id]) }}#toolbar=0&navpanes=0&view=FitH"></iframe>
            </div>
            @endif
        </div>

        {{-- Tab: Commitments --}}
        <div class="ws-left-tab-panel" id="ltab-commitments" style="display:{{ $defaultLeftTab === 'ltab-commitments' ? 'block' : 'none' }};">
            <div class="ws-card">
                <div class="ws-section-title"><span><i class="fas fa-handshake"></i> Commitments vs Outcomes</span></div>
                @php
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

                    $outcomeCounts = [];
                    foreach ($outcomes as $o) {
                        $type = $o->type;
                        if (!isset($outcomeCounts[$type])) $outcomeCounts[$type] = 0;
                        $outcomeCounts[$type]++;
                    }

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

        {{-- Tab: Progress Report --}}
        @if($showProgressTab)
        <div class="ws-left-tab-panel" id="ltab-progress" style="display:{{ $defaultLeftTab === 'ltab-progress' ? 'block' : 'none' }};">
            @if(empty($progressSubmitted) || !$progressSubmitted)
            <div class="ws-card" style="max-width:620px;margin:32px auto;text-align:center;padding:36px 28px;">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--sand-50);color:var(--ink-400);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-lock" style="font-size:24px;"></i>
                </div>
                <h3 style="font-size:16px;font-weight:600;color:var(--ink-800);margin:0 0 8px;">Progress Report Not Available Yet</h3>
                <p style="font-size:13px;color:var(--ink-500);margin:0;line-height:1.55;">
                    The LPI has not officially submitted the progress report for this project.
                    Grading will be unlocked once the report is submitted.
                    Also, the deadline of the research call has not yet passed — review is possible only after the submission deadline has passed.
                </p>
            </div>
            @else
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> Progress Report</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        @if($progressVersions->count() > 1)
                        <select class="ws-version-select" data-iframe="progress-iframe" onchange="switchVersion(this, 'progress-iframe')" style="font-size:11px;padding:3px 6px;border:1px solid var(--ink-200);border-radius:6px;background:#fff;max-width:260px;">
                            @foreach($progressVersions as $pv)
                            <option value="{{ $pv->id }}" data-version="{{ $pv->version }}" {{ $loop->first ? 'selected' : '' }}>v{{ $pv->version }} — {{ $pv->stored_filename }}</option>
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
            @endif
        </div>
        @endif

        {{-- Tab: Progress Report 2 --}}
        @if($showProgress2Tab)
        <div class="ws-left-tab-panel" id="ltab-progress2" style="display:{{ $defaultLeftTab === 'ltab-progress2' ? 'block' : 'none' }};">
            @if(empty($progress2Submitted) || !$progress2Submitted)
            <div class="ws-card" style="max-width:620px;margin:32px auto;text-align:center;padding:36px 28px;">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--sand-50);color:var(--ink-400);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-lock" style="font-size:24px;"></i>
                </div>
                <h3 style="font-size:16px;font-weight:600;color:var(--ink-800);margin:0 0 8px;">Progress Report 2 Not Available Yet</h3>
                <p style="font-size:13px;color:var(--ink-500);margin:0;line-height:1.55;">
                    The LPI has not officially submitted the Progress Report 2 for this project.
                    Grading will be unlocked once the report is submitted.
                    Also, the deadline of the research call has not yet passed — review is possible only after the submission deadline has passed.
                </p>
            </div>
            @else
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> Progress Report 2</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        @if($progress2Versions->count() > 1)
                        <select class="ws-version-select" data-iframe="progress2-iframe" onchange="switchVersion(this, 'progress2-iframe')" style="font-size:11px;padding:3px 6px;border:1px solid var(--ink-200);border-radius:6px;background:#fff;max-width:260px;">
                            @foreach($progress2Versions as $pv)
                            <option value="{{ $pv->id }}" data-version="{{ $pv->version }}" {{ $loop->first ? 'selected' : '' }}>v{{ $pv->version }} — {{ $pv->stored_filename }}</option>
                            @endforeach
                        </select>
                        @elseif($progress2Versions->count() === 1)
                            <span style="font-size:11px;color:var(--ink-400);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $progress2Versions->first()->stored_filename }}</span>
                        @else
                            <span style="font-size:11px;color:var(--ink-400);">No files uploaded</span>
                        @endif
                        <a href="{{ route('serveFile2', ['type' => 'progress2', 'id' => $project->id]) }}" target="_blank" class="ws-btn ws-btn-outline" style="font-size:11px;padding:4px 8px;">
                            <i class="fas fa-external-link-alt"></i> Open
                        </a>
                    </div>
                </div>
                <iframe id="progress2-iframe" class="ws-iframe" src="{{ $progress2Versions->count() > 0 ? route('serveFile2', ['submission_id' => $progress2Versions->first()->id]) : route('serveFile2', ['type' => 'progress2', 'id' => $project->id]) }}#toolbar=0&navpanes=0&view=FitH"></iframe>
            </div>
            @endif
        </div>
        @endif

        {{-- Tab: Final Report --}}
        @if($showFinalTab)
        <div class="ws-left-tab-panel" id="ltab-final" style="display:{{ $defaultLeftTab === 'ltab-final' ? 'block' : 'none' }};">
            @if(empty($finalSubmitted) || !$finalSubmitted)
            <div class="ws-card" style="max-width:620px;margin:32px auto;text-align:center;padding:36px 28px;">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--sand-50);color:var(--ink-400);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-lock" style="font-size:24px;"></i>
                </div>
                <h3 style="font-size:16px;font-weight:600;color:var(--ink-800);margin:0 0 8px;">Final Report Not Available Yet</h3>
                <p style="font-size:13px;color:var(--ink-500);margin:0;line-height:1.55;">
                    The LPI has not officially submitted the final report for this project.
                    Grading will be unlocked once the report is submitted.
                    Also, the deadline of the research call has not yet passed — review is possible only after the submission deadline has passed.
                </p>
            </div>
            @else
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> Final Report</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        @if($finalVersions->count() > 1)
                        <select class="ws-version-select" data-iframe="final-iframe" onchange="switchVersion(this, 'final-iframe')" style="font-size:11px;padding:3px 6px;border:1px solid var(--ink-200);border-radius:6px;background:#fff;max-width:260px;">
                            @foreach($finalVersions as $fv)
                            <option value="{{ $fv->id }}" data-version="{{ $fv->version }}" {{ $loop->first ? 'selected' : '' }}>v{{ $fv->version }} — {{ $fv->stored_filename }}</option>
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
            @endif
        </div>
        @endif

        {{-- Tab: QU Readiness Map --}}
        @if($showFinalTab)
        <div class="ws-left-tab-panel" id="ltab-readiness" style="display:{{ $defaultLeftTab === 'ltab-readiness' ? 'block' : 'none' }};">
            @if(!$readinessSubmitted)
            <div class="ws-card" style="max-width:620px;margin:32px auto;text-align:center;padding:36px 28px;">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--sand-50);color:var(--ink-400);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-lock" style="font-size:24px;"></i>
                </div>
                <h3 style="font-size:16px;font-weight:600;color:var(--ink-800);margin:0 0 8px;">Readiness Report Not Available Yet</h3>
                <p style="font-size:13px;color:var(--ink-500);margin:0;line-height:1.55;">
                    The LPI has not officially submitted the readiness report for this project.
                    Grading will be unlocked once the report is submitted.
                    Also, the deadline of the research call has not yet passed — review is possible only after the submission deadline has passed.
                </p>
            </div>
            @else
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> QU Readiness Map</span>
                    <div style="display:flex;align-items:center;gap:6px;">
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
            @endif
        </div>
        @endif

        {{-- Tab: Outcomes --}}
        <div class="ws-left-tab-panel" id="ltab-outcomes" style="display:{{ $defaultLeftTab === 'ltab-outcomes' ? 'block' : 'none' }};">

            {{-- ── Publications ── --}}
            @php
                $pubTypes = ['journal_q1','journal_q2','journal_q3','journal_q4','conference','book','edited_book','book_chapter'];
                $outcomePubs = $outcomes->filter(fn($o) => in_array($o->type, $pubTypes));
            @endphp
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-book-open"></i> Publications</span>
                    <span class="ws-pill ws-pill-ink">{{ $outcomePubs->count() }} record(s)</span>
                </div>
                @if($outcomePubs->count())
                    <div style="overflow-x:auto;">
                        <table class="ws-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Authors</th>
                                    <th>Journal</th>
                                    <th>Year</th>
                                    <th>DOI</th>
                                    <th>System Verification</th>
                                    <th>Reviewer Verification</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outcomePubs as $o)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $o->type ?? '—' }}</td>
                                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:500;">
                                        @if($o->publication)
                                            {{ $o->publication->publication_title ?? '—' }}
                                        @else
                                            <span style="color:var(--ink-400);">{{ $o->identifier ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $o->publication->authors ?? '—' }}
                                    </td>
                                    <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $o->publication->journal ?? '—' }}
                                    </td>
                                    <td>{{ $o->publication->year ?? '—' }}</td>
                                    <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        @if($o->publication && $o->publication->doi)
                                            <a href="https://doi.org/{{ $o->publication->doi }}" target="_blank" style="color:var(--brand-600);text-decoration:none;">{{ $o->publication->doi }}</a>
                                        @else
                                            <span style="color:var(--ink-400);">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if($o->publication)
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #a8e6b8;background:#e8f5ee;color:#059669;white-space:nowrap;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                Verified
                                            </span>
                                        @else
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #f5c6cb;background:#fbeef1;color:#b3261e;white-space:nowrap;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                Not Found
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if($o->verifcation_by_reviewer === 'verified')
                                            <button type="button" class="rv-toggle" data-type="outcome" data-id="{{ $o->id }}" data-status="pending" title="Click to unverify"
                                                style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #a8e6b8;background:#e8f5ee;color:#059669;cursor:pointer;transition:all .15s;white-space:nowrap;"
                                                onmouseover="this.style.background='#d1f5db'" onmouseout="this.style.background='#e8f5ee'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                Verified
                                            </button>
                                        @else
                                            <button type="button" class="rv-toggle" data-type="outcome" data-id="{{ $o->id }}" data-status="verified" title="Click to verify"
                                                style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px dashed var(--ink-300);background:#fff;color:var(--ink-600);cursor:pointer;transition:all .15s;white-space:nowrap;"
                                                onmouseover="this.style.borderColor='var(--brand-400)';this.style.background='var(--brand-50)'" onmouseout="this.style.borderColor='var(--ink-300)';this.style.background='#fff'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                Verify
                                            </button>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" onclick="document.getElementById('pub-detail-{{ $o->id }}').style.display='flex'" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid var(--ink-200);background:#fff;color:var(--ink-600);cursor:pointer;transition:all .15s;white-space:nowrap;" onmouseover="this.style.borderColor='var(--brand-400)';this.style.background='var(--brand-50)'" onmouseout="this.style.borderColor='var(--ink-200)';this.style.background='#fff'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                            View
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Publication Detail Modals --}}
                    @foreach($outcomePubs as $o)
                        @if($o->publication)
                        <div class="ws-modal-overlay" id="pub-detail-{{ $o->id }}" style="display:none;">
                            <div class="ws-modal" style="max-width:520px;">
                                <div class="ws-modal-head">
                                    <span><i class="fas fa-book-open"></i> Publication Details</span>
                                    <button type="button" class="ws-modal-close" onclick="this.closest('.ws-modal-overlay').style.display='none'">&times;</button>
                                </div>
                                <div class="ws-modal-body" style="max-height:70vh;overflow-y:auto;">
                                    <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;">
                                        <table style="width:100%;font-size:12px;border-collapse:collapse;">
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);width:120px;">Title</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->publication_title ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Authors</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->authors ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Journal</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->journal ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Year</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->year ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">DOI</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->doi ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">URL</td><td style="padding:8px 0;color:var(--ink-800);word-break:break-all;">{{ $o->publication->url ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Status</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->status ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Score</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->score ?? '—' }}</td></tr>
                                            <tr><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">System Verified</td><td style="padding:8px 0;color:var(--ink-800);">{{ ucfirst($o->verifcation_by_system ?? 'pending') }}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else
                    <p style="color:var(--ink-400);font-size:13px;margin:0;">No publications recorded.</p>
                @endif
            </div>

            {{-- ── Students ── --}}
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-user-graduate"></i> Students</span>
                    <span class="ws-pill ws-pill-ink">{{ $students->count() }} record(s)</span>
                </div>
                @if($students->count())
                    <div style="overflow-x:auto;">
                        <table class="ws-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Major</th>
                                    <th>College</th>
                                    <th>Program</th>
                                    <th>Days</th>
                                    <th>System Verification</th>
                                    <th>Reviewer Verification</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $s)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="ws-pill ws-pill-brand">{{ $s->type ?? '—' }}</span></td>
                                    <td>{{ $s->std_id ?? '—' }}</td>
                                    <td style="font-weight:500;">
                                        @if($s->details)
                                            {{ $s->details->full_name }}
                                        @else
                                            <span style="color:var(--ink-400);">—</span>
                                        @endif
                                    </td>
                                    <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        @if($s->details)
                                            {{ $s->details->major ?? '—' }}
                                        @else
                                            <span style="color:var(--ink-400);">—</span>
                                        @endif
                                    </td>
                                    <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        @if($s->details)
                                            {{ $s->details->college ?? '—' }}
                                        @else
                                            <span style="color:var(--ink-400);">—</span>
                                        @endif
                                    </td>
                                    <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        @if($s->details)
                                            {{ $s->details->std_program ?? '—' }}
                                        @else
                                            <span style="color:var(--ink-400);">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">{{ $s->days ?? '—' }}</td>
                                    <td style="text-align:center;">
                                        @if($s->details)
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #a8e6b8;background:#e8f5ee;color:#059669;white-space:nowrap;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                Verified
                                            </span>
                                        @else
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #f5c6cb;background:#fbeef1;color:#b3261e;white-space:nowrap;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                Not Found
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if($s->verifcation_by_reviewer === 'verified')
                                            <button type="button" class="rv-toggle" data-type="student" data-id="{{ $s->id }}" data-status="pending" title="Click to unverify"
                                                style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #a8e6b8;background:#e8f5ee;color:#059669;cursor:pointer;transition:all .15s;white-space:nowrap;"
                                                onmouseover="this.style.background='#d1f5db'" onmouseout="this.style.background='#e8f5ee'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                Verified
                                            </button>
                                        @else
                                            <button type="button" class="rv-toggle" data-type="student" data-id="{{ $s->id }}" data-status="verified" title="Click to verify"
                                                style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px dashed var(--ink-300);background:#fff;color:var(--ink-600);cursor:pointer;transition:all .15s;white-space:nowrap;"
                                                onmouseover="this.style.borderColor='var(--brand-400)';this.style.background='var(--brand-50)'" onmouseout="this.style.borderColor='var(--ink-300)';this.style.background='#fff'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                Verify
                                            </button>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" onclick="document.getElementById('student-detail-{{ $s->id }}').style.display='flex'" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid var(--ink-200);background:#fff;color:var(--ink-600);cursor:pointer;transition:all .15s;white-space:nowrap;" onmouseover="this.style.borderColor='var(--brand-400)';this.style.background='var(--brand-50)'" onmouseout="this.style.borderColor='var(--ink-200)';this.style.background='#fff'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                            View
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Student Detail Modals --}}
                    @foreach($students as $s)
                        @if($s->details)
                        <div class="ws-modal-overlay" id="student-detail-{{ $s->id }}" style="display:none;">
                            <div class="ws-modal" style="max-width:520px;">
                                <div class="ws-modal-head">
                                    <span><i class="fas fa-user-graduate"></i> Student Details</span>
                                    <button type="button" class="ws-modal-close" onclick="this.closest('.ws-modal-overlay').style.display='none'">&times;</button>
                                </div>
                                <div class="ws-modal-body" style="max-height:70vh;overflow-y:auto;">
                                    <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;">
                                        <table style="width:100%;font-size:12px;border-collapse:collapse;">
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);width:130px;">Student ID</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->student_id ?? $s->std_id ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Name</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->full_name ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Type</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->type ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Status</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->student_status ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Major</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->major ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Minor</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->minor ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">College</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->college ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Program</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->std_program ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Level</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->std_level ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Admission Term</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->admission_term ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Registration</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->reg_in_course ?? '—' }}</td></tr>
                                            <tr><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Days</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->days ?? '—' }}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else
                    <p style="color:var(--ink-400);font-size:13px;margin:0;">No students recorded.</p>
                @endif
            </div>

            {{-- ── Researchers ── --}}
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-users"></i> Researchers</span>
                    <span class="ws-pill ws-pill-ink">{{ $researchers->count() }} record(s)</span>
                </div>
                @if($researchers->count())
                    <div style="overflow-x:auto;">
                        <table class="ws-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Days</th>
                                    <th>Reviewer Verification</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($researchers as $r)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td style="font-weight:500;">{{ $r->name ?? '—' }}</td>
                                    <td>{{ $r->category ?? '—' }}</td>
                                    <td style="text-align:center;">{{ $r->days ?? '—' }}</td>
                                    <td style="text-align:center;">
                                        @if(($r->verifcation_by_reviewer ?? 'pending') === 'verified')
                                            <button type="button" class="rv-toggle" data-type="researcher" data-id="{{ $r->id }}" data-status="pending" title="Click to unverify"
                                                style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #a8e6b8;background:#e8f5ee;color:#059669;cursor:pointer;transition:all .15s;white-space:nowrap;"
                                                onmouseover="this.style.background='#d1f5db'" onmouseout="this.style.background='#e8f5ee'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                Verified
                                            </button>
                                        @else
                                            <button type="button" class="rv-toggle" data-type="researcher" data-id="{{ $r->id }}" data-status="verified" title="Click to verify"
                                                style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px dashed var(--ink-300);background:#fff;color:var(--ink-600);cursor:pointer;transition:all .15s;white-space:nowrap;"
                                                onmouseover="this.style.borderColor='var(--brand-400)';this.style.background='var(--brand-50)'" onmouseout="this.style.borderColor='var(--ink-300)';this.style.background='#fff'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                Verify
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="color:var(--ink-400);font-size:13px;margin:0;">No researchers recorded.</p>
                @endif
            </div>

            {{-- ── Contributions (from outcomes table, excluding publications) ── --}}
            @php
                $pubBookTypes = ['journal_q1','journal_q2','journal_q3','journal_q4','conference','book','edited_book','book_chapter'];
                $outcomeContributions = $outcomes->reject(fn($o) => in_array($o->type, $pubBookTypes));
            @endphp
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-lightbulb"></i> Contributions</span>
                    <span class="ws-pill ws-pill-ink">{{ $outcomeContributions->count() }} record(s)</span>
                </div>
                @if($outcomeContributions->count())
                    <div style="overflow-x:auto;">
                        <table class="ws-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Identifier</th>
                                    <th>System Verification</th>
                                    <th>Reviewer Verification</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outcomeContributions as $c)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $c->type ?? '—' }}</td>
                                    <td style="max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $c->identifier ?? '—' }}
                                    </td>
                                    <td style="text-align:center;">
                                        @if(($c->verifcation_by_system ?? 'pending') === 'verified')
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #a8e6b8;background:#e8f5ee;color:#059669;white-space:nowrap;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                Verified
                                            </span>
                                        @else
                                            <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #f5c6cb;background:#fbeef1;color:#b3261e;white-space:nowrap;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if(($c->verifcation_by_reviewer ?? 'pending') === 'verified')
                                            <button type="button" class="rv-toggle" data-type="outcome" data-id="{{ $c->id }}" data-status="pending" title="Click to unverify"
                                                style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #a8e6b8;background:#e8f5ee;color:#059669;cursor:pointer;transition:all .15s;white-space:nowrap;"
                                                onmouseover="this.style.background='#d1f5db'" onmouseout="this.style.background='#e8f5ee'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                                Verified
                                            </button>
                                        @else
                                            <button type="button" class="rv-toggle" data-type="outcome" data-id="{{ $c->id }}" data-status="verified" title="Click to verify"
                                                style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px dashed var(--ink-300);background:#fff;color:var(--ink-600);cursor:pointer;transition:all .15s;white-space:nowrap;"
                                                onmouseover="this.style.borderColor='var(--brand-400)';this.style.background='var(--brand-50)'" onmouseout="this.style.borderColor='var(--ink-300)';this.style.background='#fff'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                                Verify
                                            </button>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" onclick="document.getElementById('contrib-detail-{{ $c->id }}').style.display='flex'" style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid var(--ink-200);background:#fff;color:var(--ink-600);cursor:pointer;transition:all .15s;white-space:nowrap;" onmouseover="this.style.borderColor='var(--brand-400)';this.style.background='var(--brand-50)'" onmouseout="this.style.borderColor='var(--ink-200)';this.style.background='#fff'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                            View
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Contribution Detail Modals --}}
                    @foreach($outcomeContributions as $c)
                        <div class="ws-modal-overlay" id="contrib-detail-{{ $c->id }}" style="display:none;">
                            <div class="ws-modal" style="max-width:520px;">
                                <div class="ws-modal-head">
                                    <span><i class="fas fa-lightbulb"></i> Contribution Details</span>
                                    <button type="button" class="ws-modal-close" onclick="this.closest('.ws-modal-overlay').style.display='none'">&times;</button>
                                </div>
                                <div class="ws-modal-body" style="max-height:70vh;overflow-y:auto;">
                                    <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;">
                                        <table style="width:100%;font-size:12px;border-collapse:collapse;">
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);width:130px;">Type</td><td style="padding:8px 0;color:var(--ink-800);">{{ $c->type ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Identifier</td><td style="padding:8px 0;color:var(--ink-800);">{{ $c->identifier ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Online Date</td><td style="padding:8px 0;color:var(--ink-800);">{{ $c->online_date ? $c->online_date->format('d M Y') : '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Score</td><td style="padding:8px 0;color:var(--ink-800);">{{ $c->score ?? '—' }}</td></tr>
                                            <tr><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">System Verified</td><td style="padding:8px 0;color:var(--ink-800);">{{ ucfirst($c->verifcation_by_system ?? 'pending') }}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p style="color:var(--ink-400);font-size:13px;margin:0;">No contributions recorded.</p>
                @endif
            </div>

        </div>

        {{-- Tab: Ethical Approval --}}
        <div class="ws-left-tab-panel" id="ltab-ethical" style="display:{{ $defaultLeftTab === 'ltab-ethical' ? 'block' : 'none' }};">
            @php
                $commitEthical = $commitments->ethical ?? 0;
                $gradingEthical = $progressGrading->ethical ?? null;
            @endphp
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-shield-alt"></i> Ethical Approval Status</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="ws-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight:500;">Committed in Proposal</td>
                                <td>
                                    @if($commitEthical)
                                        <span class="ws-pill ws-pill-success">Yes</span>
                                    @else
                                        <span class="ws-pill ws-pill-ink">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight:500;">Ethical Approval Document</td>
                                <td>
                                    @if($ethicalExists)
                                        <span class="ws-pill ws-pill-success">Uploaded</span>
                                    @else
                                        <span class="ws-pill ws-pill-ink">Not Uploaded</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight:500;">Reviewer Verification</td>
                                <td>
                                    @if($gradingEthical === 1)
                                        <span class="ws-pill ws-pill-success">Approved</span>
                                    @elseif($gradingEthical === 0)
                                        <span class="ws-pill ws-pill-danger">Not Approved</span>
                                    @else
                                        <span class="ws-pill ws-pill-ink">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($ethicalExists)
            <div class="ws-card">
                <div class="ws-section-title">
                    <span><i class="fas fa-file-pdf"></i> Ethical Approval Document</span>
                    <a href="{{ route('serveFile2', ['type' => 'ethical', 'id' => $project->id]) }}" target="_blank" class="ws-btn ws-btn-outline" style="font-size:11px;padding:4px 8px;">
                        <i class="fas fa-external-link-alt"></i> Open
                    </a>
                </div>
                <iframe class="ws-iframe" src="{{ route('serveFile2', ['type' => 'ethical', 'id' => $project->id]) }}#toolbar=0&navpanes=0&view=FitH"></iframe>
            </div>
            @else
            <div class="ws-card" style="max-width:620px;margin:16px auto;text-align:center;padding:36px 28px;">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--sand-50);color:var(--ink-400);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-lock" style="font-size:24px;"></i>
                </div>
                <h3 style="font-size:16px;font-weight:600;color:var(--ink-800);margin:0 0 8px;">No Ethical Approval Document</h3>
                <p style="font-size:13px;color:var(--ink-500);margin:0;line-height:1.55;">
                    No ethical approval document has been uploaded for this project yet.
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- ─── RIGHT COLUMN: Grading Forms ─── --}}
    <div class="ws-form-col">

        @php
            $gradedProgress  = $progressGrading && $progressGrading->publish !== 'pending';
            $gradedProgress2 = $progress2Grading && $progress2Grading->publish !== 'pending';
            $gradedFinal     = $finalGrading && $finalGrading->publish !== 'pending';

            $hasActiveProgress  = $isProgressStep && $progressDeadlinePassed;
            $hasActiveProgress2 = $isProgress2Step && $progress2DeadlinePassed;
            $hasActiveFinal     = $isFinalStep && $finalDeadlinePassed;

            // Edit-in-place: ?edit_grading=1&report=X forces the rejected X grading
            // panel into form mode when the rejection type allows editing without
            // a new report version (ethical / other).
            $editRejectFlag   = request()->query('edit_grading');
            $editRejectReport = request()->query('report');
            $forcedEditPanel  = null;
            if ($editRejectFlag && in_array($editRejectReport, ['progress', 'progress2', 'final'], true)) {
                $rejStatusMap = [
                    'progress'  => \App\Models\Project::STATUS_PROGRESS_REJECTED,
                    'progress2' => \App\Models\Project::STATUS_PROGRESS2_REJECTED,
                    'final'     => \App\Models\Project::STATUS_FINAL_REJECTED,
                ];
                $lastEditRej = $project->statusHistories()
                    ->where('status', $rejStatusMap[$editRejectReport])
                    ->latest()->first();
                $lastEditRejType = $lastEditRej->metadata['rejection_type'] ?? 'report';
                if (in_array($lastEditRejType, ['ethical', 'other'], true)) {
                    if ($editRejectReport === 'progress' && $gradedProgress) {
                        $hasActiveProgress = true;
                        $forcedEditPanel = 'rtab-progress';
                    } elseif ($editRejectReport === 'progress2' && $gradedProgress2) {
                        $hasActiveProgress2 = true;
                        $forcedEditPanel = 'rtab-progress2';
                    } elseif ($editRejectReport === 'final' && $gradedFinal) {
                        $hasActiveFinal = true;
                        $forcedEditPanel = 'rtab-final';
                    }
                }
            }

            $hasAnyGradedTab  = $gradedProgress || $gradedProgress2 || $gradedFinal;
            $hasAnyActiveTab  = $hasActiveProgress || $hasActiveProgress2 || $hasActiveFinal;

            $hasRejection = ($project->hasStatus(\App\Models\Project::STATUS_PROGRESS_REJECTED) && (!$progressGrading || $progressGrading->publish === 'pending'))
                || ($project->hasStatus(\App\Models\Project::STATUS_PROGRESS2_REJECTED) && (!$progress2Grading || $progress2Grading->publish === 'pending'))
                || ($project->hasStatus(\App\Models\Project::STATUS_FINAL_REJECTED) && (!$finalGrading || $finalGrading->publish === 'pending'));
        @endphp

        @if($hasAnyActiveTab || $hasAnyGradedTab)

            @php
                $defaultRightPanel = null;
                if ($forcedEditPanel) $defaultRightPanel = $forcedEditPanel;
                elseif ($hasActiveProgress) $defaultRightPanel = 'rtab-progress';
                elseif ($gradedProgress) $defaultRightPanel = 'rtab-ro-progress';
                elseif ($hasActiveProgress2) $defaultRightPanel = 'rtab-progress2';
                elseif ($gradedProgress2) $defaultRightPanel = 'rtab-ro-progress2';
                elseif ($hasActiveFinal) $defaultRightPanel = 'rtab-final';
                elseif ($gradedFinal) $defaultRightPanel = 'rtab-ro-final';
            @endphp

            {{-- Tab buttons --}}
            <div class="ws-right-tabs" role="tablist">
                @if($hasActiveProgress)
                <button type="button" class="ws-tab {{ $defaultRightPanel === 'rtab-progress' ? 'active' : '' }}" role="tab" data-tab="rtab-progress" onclick="switchRightTab('rtab-progress', this)">
                    <i class="fas fa-chart-line"></i> Progress Grading
                </button>
                @elseif($gradedProgress)
                <button type="button" class="ws-tab {{ $defaultRightPanel === 'rtab-ro-progress' ? 'active' : '' }}" role="tab" data-tab="rtab-ro-progress" onclick="switchRightTab('rtab-ro-progress', this)">
                    <i class="fas fa-chart-line"></i> Progress Grading
                </button>
                @endif

                @if($hasActiveProgress2)
                <button type="button" class="ws-tab {{ $defaultRightPanel === 'rtab-progress2' ? 'active' : '' }}" role="tab" data-tab="rtab-progress2" onclick="switchRightTab('rtab-progress2', this)">
                    <i class="fas fa-chart-line"></i> Progress 2 Grading
                </button>
                @elseif($gradedProgress2)
                <button type="button" class="ws-tab {{ $defaultRightPanel === 'rtab-ro-progress2' ? 'active' : '' }}" role="tab" data-tab="rtab-ro-progress2" onclick="switchRightTab('rtab-ro-progress2', this)">
                    <i class="fas fa-chart-line"></i> Progress 2 Grading
                </button>
                @endif

                @if($hasActiveFinal)
                <button type="button" class="ws-tab {{ $defaultRightPanel === 'rtab-final' ? 'active' : '' }}" role="tab" data-tab="rtab-final" onclick="switchRightTab('rtab-final', this)">
                    <i class="fas fa-star"></i> Final Grade
                </button>
                @elseif($gradedFinal)
                <button type="button" class="ws-tab {{ $defaultRightPanel === 'rtab-ro-final' ? 'active' : '' }}" role="tab" data-tab="rtab-ro-final" onclick="switchRightTab('rtab-ro-final', this)">
                    <i class="fas fa-star"></i> Final Grade
                </button>
                @endif
            </div>

            {{-- ── Active Progress Grading Tab ── --}}
            @if($hasActiveProgress)
            <div class="ws-right-tab-panel" id="rtab-progress" style="display:{{ $defaultRightPanel === 'rtab-progress' ? 'block' : 'none' }};">
                @include('grading.partials.progress-grade', [
                    'report' => null,
                    'grading' => $progressGrading ?? null,
                    'index' => 1,
                    'project' => $project,
                    'showSubmitGrade' => $progressGrading && $progressGrading->isAccepted !== null && !$project->hasStatus(\App\Models\Project::STATUS_GRADED),
                    'showAsSummary' => false,
                ])
            </div>
            @endif

            {{-- ── Active Progress 2 Grading Tab ── --}}
            @if($hasActiveProgress2)
            <div class="ws-right-tab-panel" id="rtab-progress2" style="display:{{ $defaultRightPanel === 'rtab-progress2' ? 'block' : 'none' }};">
                @include('grading.partials.progress-grade', [
                    'report' => null,
                    'grading' => $progress2Grading ?? null,
                    'index' => 1,
                    'project' => $project,
                    'report_type' => 'progress2',
                    'showSubmitGrade' => $progress2Grading && $progress2Grading->isAccepted !== null && !$project->hasStatus(\App\Models\Project::STATUS_GRADED),
                    'showAsSummary' => false,
                ])
            </div>
            @endif

            {{-- ── Active Final Grading Tab ── --}}
            @if($hasActiveFinal)
            <div class="ws-right-tab-panel" id="rtab-final" style="display:{{ $defaultRightPanel === 'rtab-final' ? 'block' : 'none' }};">
                @include('grading.partials.final-grades', [
                    'grading' => $finalGrading,
                    'project' => $project,
                ])
            </div>
            @endif

            {{-- ── Graded Progress (read-only) ── --}}
            @if($gradedProgress && !$hasActiveProgress)
            <div class="ws-right-tab-panel" id="rtab-ro-progress" style="display:{{ $defaultRightPanel === 'rtab-ro-progress' ? 'block' : 'none' }};">
                @include('grading.partials.progress-grade', [
                    'grading' => $progressGrading,
                    'project' => $project,
                    'showAsSummary' => true
                ])
            </div>
            @endif

            {{-- ── Graded Progress 2 (read-only) ── --}}
            @if($gradedProgress2 && !$hasActiveProgress2)
            <div class="ws-right-tab-panel" id="rtab-ro-progress2" style="display:{{ $defaultRightPanel === 'rtab-ro-progress2' ? 'block' : 'none' }};">
                @include('grading.partials.progress-grade', [
                    'grading' => $progress2Grading,
                    'project' => $project,
                    'showAsSummary' => true,
                    'report_type' => 'progress2'
                ])
            </div>
            @endif

            {{-- ── Graded Final (read-only) ── --}}
            @if($gradedFinal && !$hasActiveFinal)
            <div class="ws-right-tab-panel" id="rtab-ro-final" style="display:{{ $defaultRightPanel === 'rtab-ro-final' ? 'block' : 'none' }};">
                @include('grading.partials.final-grades', [
                    'grading' => $finalGrading,
                    'project' => $project,
                    'showAsSummary' => true
                ])
            </div>
            @endif

        {{-- NO ACTIVE TABS AND NO GRADED TABS — check for rejections --}}
        @elseif($hasRejection)
            @php
                $hasProgressRejection = $project->hasStatus(\App\Models\Project::STATUS_PROGRESS_REJECTED) && (!$progressGrading || $progressGrading->publish === 'pending');
                $hasProgress2Rejection = $project->hasStatus(\App\Models\Project::STATUS_PROGRESS2_REJECTED) && (!$progress2Grading || $progress2Grading->publish === 'pending');
                $hasFinalRejection = $project->hasStatus(\App\Models\Project::STATUS_FINAL_REJECTED) && (!$finalGrading || $finalGrading->publish === 'pending');
            @endphp

                @if($hasProgressRejection && $hasFinalRejection)
                <div class="ws-right-tabs" role="tablist">
                    <button type="button" class="ws-tab active" role="tab" data-tab="rtab-progress-rej" onclick="switchRightTab('rtab-progress-rej', this)">
                        <i class="fas fa-chart-line"></i> Progress Rejection Review
                    </button>
                    <button type="button" class="ws-tab" role="tab" data-tab="rtab-final-rej" onclick="switchRightTab('rtab-final-rej', this)">
                        <i class="fas fa-star"></i> Final Rejection Review
                    </button>
                </div>

                <div class="ws-right-tab-panel" id="rtab-progress-rej" style="display:block;">
                    @include('grading.partials.progress-grade', [
                        'grading' => $progressGrading,
                        'project' => $project,
                        'showAsSummary' => true
                    ])
                </div>

                <div class="ws-right-tab-panel" id="rtab-final-rej" style="display:none;">
                    @include('grading.partials.final-grades', [
                        'grading' => $finalGrading,
                        'project' => $project
                    ])
                </div>

                @elseif($hasProgressRejection)
                    @include('grading.partials.progress-grade', [
                        'grading' => $progressGrading,
                        'project' => $project,
                        'showAsSummary' => true
                    ])

                @elseif($hasProgress2Rejection)
                    @include('grading.partials.progress-grade', [
                        'grading' => $progress2Grading,
                        'project' => $project,
                        'showAsSummary' => true,
                        'report_type' => 'progress2'
                    ])

                @elseif($hasFinalRejection)
                    @include('grading.partials.final-grades', [
                        'grading' => $finalGrading,
                        'project' => $project
                    ])
                @endif

            @else
                <div class="ws-card" style="max-width:620px;margin:32px auto;text-align:center;padding:36px 28px;">
                    <div style="width:64px;height:64px;border-radius:50%;background:var(--sand-50);color:var(--ink-400);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <i class="fas fa-clipboard-check" style="font-size:24px;"></i>
                    </div>
                    <h3 style="font-size:16px;font-weight:600;color:var(--ink-800);margin:0 0 8px;">No Active Grading Step</h3>
                    <p style="font-size:13px;color:var(--ink-500);margin:0;line-height:1.55;">
                        There is no grading step currently active for this project.
                        The grading forms will appear here when the LPI submits a report.
                    </p>
                </div>
            @endif

            {{-- ── Archived grading of superseded versions — shown in this pane when an older version is selected on the left ── --}}
            @if($progressVersions->count() > 1 && ($progressGrading->previous_grading ?? null))
            <div class="ws-right-tab-panel" id="rtab-prev-progress" style="display:none;">
                @include('grading.partials.previous-grading', ['archive' => $progressGrading->previous_grading, 'reportType' => 'progress', 'project' => $project])
            </div>
            @endif

            @if($progress2Versions->count() > 1 && ($progress2Grading->previous_grading ?? null))
            <div class="ws-right-tab-panel" id="rtab-prev-progress2" style="display:none;">
                @include('grading.partials.previous-grading', ['archive' => $progress2Grading->previous_grading, 'reportType' => 'progress2', 'project' => $project])
            </div>
            @endif

            @if($finalVersions->count() > 1 && ($finalGrading->previous_grading ?? null))
            <div class="ws-right-tab-panel" id="rtab-prev-final" style="display:none;">
                @include('grading.partials.previous-grading', ['archive' => $finalGrading->previous_grading, 'reportType' => 'final', 'project' => $project])
            </div>
            @endif

    </div>
</div>

@include('grading.partials.help-modals')

@endsection

@push('styles')
<style>
/* ─── Character counter ─── */
.ws-char-count {
    position: absolute;
    bottom: 6px;
    right: 8px;
    font-size: 10px;
    color: var(--ink-400);
    pointer-events: none;
    font-weight: 500;
}

/* ─── Page head ─── */
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

/* ─── Tabs (shared for left, right) ─── */
.ws-tabs, .ws-left-tabs, .ws-right-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid var(--ink-100);
    margin-bottom: 14px;
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
.ws-tab i { opacity: .8; font-size: 12px; }

.ws-tab-panel, .ws-left-tab-panel, .ws-right-tab-panel { animation: fadeIn .2s ease; }
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

/* ─── 70/30 split ─── */
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

/* ─── Grade radio row read-only ─── */
.ws-grade-row-readonly { display: flex; gap: 6px; justify-content: center; }
.ws-grade-readonly-opt {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 6px;
    border: 1px solid var(--ink-200); background: var(--ink-50);
    font-weight: 600; font-size: 13px; color: var(--ink-500);
}
.ws-grade-readonly-opt.selected {
    background: var(--brand-500); color: #fff; border-color: var(--brand-600);
}

/* ─── Help link ─── */
.ws-help {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11.5px; font-weight: 600; color: var(--brand-600);
    background: var(--brand-50); border: 1px solid var(--brand-200);
    border-radius: 6px; padding: 5px 10px; cursor: pointer; text-decoration: none;
}
.ws-help:hover { background: var(--brand-100); }

/* ─── Help modal ─── */
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

/* ─── Read-only summary ─── */
.ws-ro-summary {
    background: var(--sand-50);
    border: 1px solid var(--ink-100);
    border-radius: 6px;
    padding: 12px 14px;
    font-size: 13px;
}
.ws-ro-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--ink-100);
}
.ws-ro-row:last-child { border-bottom: none; }
.ws-ro-label { font-weight: 600; color: var(--ink-700); font-size: 12.5px; }
.ws-ro-value { font-weight: 600; color: var(--brand-600); font-size: 14px; }
.ws-ro-comment { color: var(--ink-500); font-size: 12px; font-style: italic; margin-top: 3px; }

/* ─── Auto-grade box ─── */
.ws-auto-grade-box {
    background: var(--brand-50);
    border: 1px solid var(--brand-200);
    border-radius: 6px;
    padding: 8px 12px;
    margin-bottom: 12px;
    font-size: 12px;
    color: var(--brand-700);
    display: flex;
    align-items: center;
    gap: 6px;
}
.ws-auto-grade-box i { color: var(--brand-500); }
.ws-auto-hint {
    color: var(--warning);
    font-size: 11px;
    margin-top: 6px;
    padding: 4px 8px;
    background: #fef7e0;
    border: 1px solid #fce8b2;
    border-radius: 4px;
    display: none;
}

.animate-spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ─── Character counter for textareas ───
    window.updateCharCount = function(textarea) {
        var counter = textarea.parentElement.querySelector('.ws-char-count');
        if (counter) {
            var len = textarea.value.length;
            counter.textContent = len + '/500';
            counter.style.color = len > 450 ? 'var(--danger)' : len > 400 ? '#d97706' : 'var(--ink-400)';
        }
    };
    // Initialize counters on page load
    document.querySelectorAll('textarea[maxlength="500"]').forEach(function(ta) {
        updateCharCount(ta);
    });

    // ─── Left-side tab switching ───
    window.switchLeftTab = function(tabId, btn) {
        var col = btn.closest('.ws-pdf-col');
        col.querySelectorAll('.ws-left-tab-panel').forEach(function(p) { p.style.display = 'none'; });
        col.querySelectorAll('.ws-left-tabs .ws-tab').forEach(function(t) { t.classList.remove('active'); });
        btn.classList.add('active');
        var panel = document.getElementById(tabId);
        if (panel) panel.style.display = 'block';
    };
    var firstLeftTab = document.querySelector('.ws-left-tabs .ws-tab.active');
    if (firstLeftTab) firstLeftTab.click();

    // ─── Right-side tab switching (final grading sub-tabs) ───
    window.switchRightTab = function(tabId, btn) {
        var col = btn.closest('.ws-form-col');
        col.querySelectorAll('.ws-right-tab-panel').forEach(function(p) { p.style.display = 'none'; });
        col.querySelectorAll('.ws-right-tabs .ws-tab').forEach(function(t) { t.classList.remove('active'); });
        btn.classList.add('active');
        var panel = document.getElementById(tabId);
        if (panel) panel.style.display = 'block';
    };

    // ─── Ethical approval toggle label ───
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

    // ─── Rejection reason visibility + validation ───
    function wireRejectionReason(form) {
        var input = form.querySelector('textarea[name="rejection_reason"]');
        var select = form.querySelector('select[name="rejection_type"]');
        var box = input ? input.parentElement : null;
        var rejectedRadio = form.querySelector('input[name="publish"][value="rejected"]');
        if (!box || !input || !rejectedRadio) return;

        function update() {
            var on = !!rejectedRadio.checked;
            box.style.display = on ? 'block' : 'none';
            input.required = on;
            if (select) select.required = on;
        }

        form.querySelectorAll('input[name="publish"]').forEach(function(r) {
            r.addEventListener('change', update);
        });
        update();
    }
    document.querySelectorAll('form[data-progress-grade]').forEach(function(f) { wireRejectionReason(f); });
    document.querySelectorAll('form[data-final-grade]').forEach(function(f) { wireRejectionReason(f); });

    // ─── Auto-grade diff detection ───
    document.querySelectorAll('.ws-grade-row input[type="radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var section = this.closest('.ws-achievement-section') || this.closest('.ws-publication-section') || this.closest('.ws-student-section') || this.closest('.ws-card');
            if (!section) return;
            var autoBox = section.querySelector('.ws-auto-grade-box');
            var hint = section.querySelector('.ws-auto-hint');
            if (autoBox && hint) {
                var autoVal = autoBox.dataset.value;
                var selectedVal = this.value;
                if (selectedVal !== autoVal) {
                    hint.textContent = '\u26A0 Your selection (' + selectedVal + ') differs from auto-calculated (' + autoVal + ')';
                    hint.style.display = 'block';
                } else {
                    hint.style.display = 'none';
                }
            }
        });
    });

    // ─── Achievement checkbox auto-grade recalculation ───
    function recalcAchievement(section) {
        if (!section) return;
        var autoGradeBox = section.querySelector('.ws-auto-grade-box');
        var scoreDisplay = section.querySelector('.ws-achievement-score');
        var gradeDisplay = section.querySelector('.ws-achievement-grade');
        if (!autoGradeBox) return;
        var baseExpected = parseFloat(autoGradeBox.dataset.baseExpected) || 0;

        var maxPossible = 0;
        section.querySelectorAll('.ws-check-item input[type="checkbox"]').forEach(function(cb) {
            maxPossible += parseFloat(cb.dataset.points) || 0;
        });

        var actualSum = 0;
        section.querySelectorAll('.ws-check-item input[type="checkbox"]:checked').forEach(function(checked) {
            actualSum += parseFloat(checked.dataset.points) || 0;
        });

        if (scoreDisplay) scoreDisplay.textContent = actualSum;

        var denom = baseExpected > 0 ? baseExpected : maxPossible;
        var newGrade = 0;
        if (denom > 0) {
            newGrade = Math.min(Math.round((actualSum / denom) * 5 * 100) / 100, 5);
        }
        if (gradeDisplay) gradeDisplay.textContent = denom > 0 ? newGrade : '—';
        autoGradeBox.dataset.value = newGrade;

        var form = section.closest('form');
        if (form) {
            var scoreInput = form.querySelector('.ws-score-a');
            var autoInput = form.querySelector('.ws-auto-grade-a');
            if (scoreInput) scoreInput.value = actualSum;
            if (autoInput) autoInput.value = newGrade;
        }

        if (denom > 0) {
            var roundedGrade = Math.round(newGrade);
            if (roundedGrade < 1) roundedGrade = 1;
            var existingRadio = section.querySelector('input[name="gradeA"]:checked');
            if (!existingRadio) {
                var targetRadio = section.querySelector('input[name="gradeA"][value="' + roundedGrade + '"]');
                if (targetRadio) targetRadio.checked = true;
            }
        }

        var hint = section.querySelector('.ws-auto-hint');
        if (hint && denom > 0) {
            var gradeARadio = section.querySelector('input[name="gradeA"]:checked');
            if (gradeARadio && parseFloat(gradeARadio.value) !== parseFloat(autoGradeBox.dataset.value)) {
                hint.textContent = '\u26A0 Your selection (' + gradeARadio.value + ') differs from auto-calculated (' + autoGradeBox.dataset.value + ')';
                hint.style.display = 'block';
            } else {
                hint.style.display = 'none';
            }
        } else if (hint) {
            hint.style.display = 'none';
        }
    }

    // ─── Send verification update to server ───
    function sendVerification(type, ids, status) {
        fetch('{{ route("grading.updateVerification") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type, ids: ids, status: status })
        }).catch(function(err) {
            console.error('Verification update failed:', err);
        });
    }

    // ─── Reviewer verification toggle buttons ───
    var rvVerifiedHtml = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Verified';
    var rvPendingHtml = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Verify';
    var rvVerifiedStyle = 'display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px solid #a8e6b8;background:#e8f5ee;color:#059669;cursor:pointer;transition:all .15s;white-space:nowrap;';
    var rvPendingStyle  = 'display:inline-flex;align-items:center;gap:5px;padding:5px 12px;font-size:11px;font-weight:600;border-radius:20px;border:1px dashed var(--ink-300);background:#fff;color:var(--ink-600);cursor:pointer;transition:all .15s;white-space:nowrap;';

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.rv-toggle');
        if (!btn) return;
        var type = btn.dataset.type;
        var id = btn.dataset.id;
        var newStatus = btn.dataset.status;

        sendVerification(type, [id], newStatus);

        if (newStatus === 'verified') {
            btn.innerHTML = rvVerifiedHtml;
            btn.dataset.status = 'pending';
            btn.title = 'Click to unverify';
            btn.style.cssText = rvVerifiedStyle;
        } else {
            btn.innerHTML = rvPendingHtml;
            btn.dataset.status = 'verified';
            btn.title = 'Click to verify';
            btn.style.cssText = rvPendingStyle;
        }
    });

    document.querySelectorAll('.ws-achievement-section').forEach(function(section) {
        recalcAchievement(section);
        // Send initial verification for checked items on page load
        var checkedIds = [];
        section.querySelectorAll('.ws-check-item input[type="checkbox"]:checked').forEach(function(cb) {
            checkedIds.push(cb.value);
        });
        if (checkedIds.length) sendVerification('outcome', checkedIds, 'verified');
    });

    document.querySelectorAll('.ws-achievement-section input[type="checkbox"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var section = this.closest('.ws-achievement-section');
            recalcAchievement(section);
            // Send verification update
            var type = this.dataset.points !== undefined ? 'outcome' : 'outcome';
            var id = this.value;
            var status = this.checked ? 'verified' : 'pending';
            sendVerification('outcome', [id], status);
        });
    });

    // ─── Publication checkbox recalculation ───
    function recalcPublication(section) {
        if (!section) return;
        var autoGradeBox = section.querySelector('.ws-auto-grade-box');
        var scoreDisplay = section.querySelector('.ws-pub-score');
        var gradeDisplay = section.querySelector('.ws-pub-grade');
        if (!autoGradeBox) return;
        var baseExpected = parseFloat(autoGradeBox.dataset.baseExpected) || 0;

        var maxPossible = 0;
        section.querySelectorAll('.ws-check-item input[type="checkbox"]').forEach(function(cb) {
            maxPossible += parseFloat(cb.dataset.points) || 0;
        });

        var actualSum = 0;
        section.querySelectorAll('.ws-check-item input[type="checkbox"]:checked').forEach(function(checked) {
            actualSum += parseFloat(checked.dataset.points) || 0;
        });

        if (scoreDisplay) scoreDisplay.textContent = actualSum;

        var denom = baseExpected > 0 ? baseExpected : maxPossible;
        var newGrade = 0;
        if (denom > 0) {
            newGrade = Math.min(Math.round((actualSum / denom) * 5 * 100) / 100, 5);
        }
        if (gradeDisplay) gradeDisplay.textContent = denom > 0 ? newGrade : '—';
        autoGradeBox.dataset.value = newGrade;

        var form = section.closest('form');
        if (form) {
            var scoreInput = form.querySelector('.ws-score-b');
            var autoInput = form.querySelector('.ws-auto-grade-b');
            if (scoreInput) scoreInput.value = actualSum;
            if (autoInput) autoInput.value = newGrade;
        }

        if (denom > 0) {
            var roundedGrade = Math.round(newGrade);
            if (roundedGrade < 1) roundedGrade = 1;
            var existingRadio = section.querySelector('input[name="gradeB"]:checked');
            if (!existingRadio) {
                var targetRadio = section.querySelector('input[name="gradeB"][value="' + roundedGrade + '"]');
                if (targetRadio) targetRadio.checked = true;
            }
        }

        var hint = section.querySelector('.ws-auto-hint');
        if (hint && denom > 0) {
            var gradeBRadio = section.querySelector('input[name="gradeB"]:checked');
            if (gradeBRadio && parseFloat(gradeBRadio.value) !== parseFloat(autoGradeBox.dataset.value)) {
                hint.textContent = '\u26A0 Your selection (' + gradeBRadio.value + ') differs from auto-calculated (' + autoGradeBox.dataset.value + ')';
                hint.style.display = 'block';
            } else {
                hint.style.display = 'none';
            }
        } else if (hint) {
            hint.style.display = 'none';
        }
    }

    document.querySelectorAll('.ws-publication-section').forEach(function(section) {
        recalcPublication(section);
        // Send initial verification for checked items on page load
        var checkedIds = [];
        section.querySelectorAll('.ws-check-item input[type="checkbox"]:checked').forEach(function(cb) {
            checkedIds.push(cb.value);
        });
        if (checkedIds.length) sendVerification('outcome', checkedIds, 'verified');
    });

    document.querySelectorAll('.ws-publication-section input[type="checkbox"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var section = this.closest('.ws-publication-section');
            recalcPublication(section);
            // Send verification update
            var id = this.value;
            var status = this.checked ? 'verified' : 'pending';
            sendVerification('outcome', [id], status);
        });
    });

    // ─── Student checkbox recalculation ───
    function recalcStudent(section) {
        if (!section) return;
        var autoGradeBox = section.querySelector('.ws-auto-grade-box');
        var scoreDisplay = section.querySelector('.ws-student-score');
        var gradeDisplay = section.querySelector('.ws-student-grade');
        if (!autoGradeBox) return;
        var baseExpected = parseFloat(autoGradeBox.dataset.baseExpected) || 0;

        var maxPossible = 0;
        section.querySelectorAll('.ws-check-item input[type="checkbox"]').forEach(function(cb) {
            maxPossible += parseFloat(cb.dataset.points) || 0;
        });

        var actualSum = 0;
        section.querySelectorAll('.ws-check-item input[type="checkbox"]:checked').forEach(function(checked) {
            actualSum += parseFloat(checked.dataset.points) || 0;
        });

        if (scoreDisplay) scoreDisplay.textContent = actualSum;

        var denom = baseExpected > 0 ? baseExpected : maxPossible;
        var newGrade = 0;
        if (denom > 0) {
            newGrade = Math.min(Math.round((actualSum / denom) * 5 * 100) / 100, 5);
        }
        if (gradeDisplay) gradeDisplay.textContent = denom > 0 ? newGrade : '—';
        autoGradeBox.dataset.value = newGrade;

        var form = section.closest('form');
        if (form) {
            var scoreInput = form.querySelector('.ws-score-c');
            var autoInput = form.querySelector('.ws-auto-grade-c');
            if (scoreInput) scoreInput.value = actualSum;
            if (autoInput) autoInput.value = newGrade;
        }

        if (denom > 0) {
            var roundedGrade = Math.round(newGrade);
            if (roundedGrade < 1) roundedGrade = 1;
            var existingRadio = section.querySelector('input[name="gradeC"]:checked');
            if (!existingRadio) {
                var targetRadio = section.querySelector('input[name="gradeC"][value="' + roundedGrade + '"]');
                if (targetRadio) targetRadio.checked = true;
            }
        }

        var hint = section.querySelector('.ws-auto-hint');
        if (hint && denom > 0) {
            var radio = section.querySelector('input[name="gradeC"]:checked');
            if (radio && parseFloat(radio.value) !== parseFloat(autoGradeBox.dataset.value)) {
                hint.textContent = '\u26A0 Your selection (' + radio.value + ') differs from auto-calculated (' + autoGradeBox.dataset.value + ')';
                hint.style.display = 'block';
            } else {
                hint.style.display = 'none';
            }
        } else if (hint) {
            hint.style.display = 'none';
        }
    }

    document.querySelectorAll('.ws-student-section').forEach(function(section) {
        recalcStudent(section);
        // Send initial verification for checked items on page load
        var studentIds = [];
        var researcherIds = [];
        section.querySelectorAll('.ws-check-item input[type="checkbox"]:checked').forEach(function(cb) {
            if (cb.name === 'researcher_items[]') {
                researcherIds.push(cb.value);
            } else {
                studentIds.push(cb.value);
            }
        });
        if (studentIds.length) sendVerification('student', studentIds, 'verified');
        if (researcherIds.length) sendVerification('researcher', researcherIds, 'verified');
    });

    document.querySelectorAll('.ws-student-section input[type="checkbox"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var section = this.closest('.ws-student-section');
            recalcStudent(section);
            // Send verification update
            var id = this.value;
            var status = this.checked ? 'verified' : 'pending';
            var type = this.name === 'researcher_items[]' ? 'researcher' : 'student';
            sendVerification(type, [id], status);
        });
    });

    // ─── PDF version switching ───
    // Selecting an older (superseded) version swaps the RIGHT pane to the
    // archived readonly grading; selecting the latest version brings back
    // the grading form (or its read-only summary).
    window.switchVersion = function(select, iframeId) {
        var submissionId = select.value;
        var iframe = document.getElementById(iframeId);
        if (iframe) {
            iframe.src = '{{ route("serveFile2") }}?submission_id=' + submissionId + '#toolbar=0&navpanes=0&view=FitH';
        }

        var base = iframeId.replace(/-iframe$/, '');
        var prevPanel = document.getElementById('rtab-prev-' + base);
        if (!prevPanel) return;

        var selected = select.options[select.selectedIndex];
        var selectedVersion = parseInt(selected.getAttribute('data-version') || '0', 10);
        var maxVersion = 0;
        for (var i = 0; i < select.options.length; i++) {
            maxVersion = Math.max(maxVersion, parseInt(select.options[i].getAttribute('data-version') || '0', 10));
        }
        var isOlder = selectedVersion < maxVersion;

        var formCol = prevPanel.closest('.ws-form-col');
        if (!formCol) return;

        // Normal panel for this report type: active form first, read-only summary otherwise.
        var normalId = document.getElementById('rtab-' + base) ? 'rtab-' + base
                     : (document.getElementById('rtab-ro-' + base) ? 'rtab-ro-' + base : null);

        formCol.querySelectorAll('.ws-right-tab-panel').forEach(function(p) { p.style.display = 'none'; });
        formCol.querySelectorAll('.ws-right-tabs .ws-tab').forEach(function(t) { t.classList.remove('active'); });

        var showId = isOlder ? 'rtab-prev-' + base : normalId;
        if (showId) {
            var target = document.getElementById(showId);
            if (target) target.style.display = 'block';
        }
        // Keep the report type's own tab button lit (no button exists for the prev panel itself).
        var btnTab = isOlder ? normalId : showId;
        if (btnTab) {
            var btn = formCol.querySelector('.ws-right-tabs .ws-tab[data-tab="' + btnTab + '"]');
            if (btn) btn.classList.add('active');
        }
    };

    // ─── Help modals ───
    window.openHelp = function(id) {
        var m = document.getElementById(id);
        if (m) m.style.display = 'flex';
    };
    window.closeHelp = function(id) {
        var m = document.getElementById(id);
        if (m) m.style.display = 'none';
    };

    // ─── Detail modals (publications, students) ───
    window.openDetailModal = function(id) {
        var m = document.getElementById(id);
        if (m) m.style.display = 'flex';
    };
    window.closeDetailModal = function(id) {
        var m = document.getElementById(id);
        if (m) m.style.display = 'none';
    };

    document.querySelectorAll('.ws-modal-overlay').forEach(function(ov) {
        ov.addEventListener('click', function(e) {
            if (e.target === ov) ov.style.display = 'none';
        });
    });

    // ─── Progress grade AJAX submit ───
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

    // ─── Final grade AJAX submit ───
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

});
</script>
@endpush
