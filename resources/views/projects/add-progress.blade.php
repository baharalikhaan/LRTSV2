@php
    $project ??= null;
    $outcomes ??= collect();
    $structuredOutcomeTypes ??= [];
    $submissions ??= collect();
    $deadlines ??= [];
    $projectStudents ??= collect();
    $projectResearchers ??= collect();

    // Extended progress report (Progress Report 2) availability — gated by the
    // admin-controlled extended_progress flag on the project.
    $extendedProgress = $project ? (bool) $project->extended_progress : false;

    // Cross-College & Research Awards from outcomes
    $crossCollegeOutcome = $outcomes->where('type', 'cross_college')->first();
    $crossCollegeValue = $crossCollegeOutcome ? 'Yes' : 'No';
    $crossCollegeDetails = $crossCollegeOutcome ? $crossCollegeOutcome->identifier : '';

    $researchAwardsOutcome = $outcomes->where('type', 'research_awards')->first();
    $researchAwardsValue = $researchAwardsOutcome ? 'Yes' : 'No';
    $researchAwardsDetails = $researchAwardsOutcome ? $researchAwardsOutcome->identifier : '';
    // Extract latest submissions by type for display in the upload cards
    $progressSub = $submissions->where('type', 'progress')->last();
    $progress2Sub = $submissions->where('type', 'progress2')->last();
    $readinessSub = $submissions->where('type', 'readiness')->last();
    $finalSub = $submissions->where('type', 'final')->last();

    // Pre-group outcomes by type for pre-filling the form
    $outcomeGroups = $outcomes->groupBy('type');

    // Mode: 'progress' (Add/Update Progress), 'final' (Add Final Report), or 'update' (Unified)
    $mode = $mode ?? 'progress';
    $isFinalMode = $mode === 'final';
    $isUpdateMode = $mode === 'update';

    // Readonly once the progress report has been submitted (file uploaded) and not rejected.
    // Check file existence instead of status (buttons removed, files uploaded directly).
    $progressSubmitted = $project
        && $submissions->where('type', 'progress')->count() > 0
        && !$project->hasStatus(\App\Models\Project::STATUS_PROGRESS_REJECTED);

    // Final report submitted? Check file existence instead of status.
    $finalSubmitted = $project && $submissions->where('type', 'final')->count() > 0;

    // Check if admin has sent back for resubmission (stays open until reviewer grades it)
    $lastProgressRejReviewed = $project->statusHistories()->where('status', \App\Models\Project::STATUS_PROGRESS_REJ_REVIEWED)->latest()->first();
    $progressResubmitRequested = $lastProgressRejReviewed
        && ($lastProgressRejReviewed->metadata['action'] ?? null) === 'send_to_lpi'
        && !$project->statusHistories()
            ->whereIn('status', [\App\Models\Project::STATUS_PROGRESS_REVIEWED, \App\Models\Project::STATUS_PROGRESS_REJECTED])
            ->where('created_at', '>', $lastProgressRejReviewed->created_at)
            ->exists();

    $lastProgressRejection = $project->statusHistories()->where('status', \App\Models\Project::STATUS_PROGRESS_REJECTED)->latest()->first();
    $progressRejectionReason = $lastProgressRejection ? ($lastProgressRejection->metadata['comment'] ?? $lastProgressRejection->metadata['reason'] ?? null) : null;

    // Progress Report 2 resubmission state (extended progress report)
    $lastProgress2RejReviewed = $project->statusHistories()->where('status', \App\Models\Project::STATUS_PROGRESS2_REJ_REVIEWED)->latest()->first();
    $progress2ResubmitRequested = $lastProgress2RejReviewed
        && ($lastProgress2RejReviewed->metadata['action'] ?? null) === 'send_to_lpi'
        && !$project->statusHistories()
            ->whereIn('status', [\App\Models\Project::STATUS_PROGRESS2_REVIEWED, \App\Models\Project::STATUS_PROGRESS2_REJECTED])
            ->where('created_at', '>', $lastProgress2RejReviewed->created_at)
            ->exists();

    $lastProgress2Rejection = $project->statusHistories()->where('status', \App\Models\Project::STATUS_PROGRESS2_REJECTED)->latest()->first();
    $progress2RejectionReason = $lastProgress2Rejection ? ($lastProgress2Rejection->metadata['comment'] ?? $lastProgress2Rejection->metadata['reason'] ?? null) : null;

    $lastFinalRejReviewed = $project->statusHistories()->where('status', \App\Models\Project::STATUS_FINAL_REJ_REVIEWED)->latest()->first();
    $finalResubmitRequested = $lastFinalRejReviewed
        && ($lastFinalRejReviewed->metadata['action'] ?? null) === 'send_to_lpi'
        && !$project->statusHistories()
            ->whereIn('status', [\App\Models\Project::STATUS_GRADED, \App\Models\Project::STATUS_FINAL_REJECTED])
            ->where('created_at', '>', $lastFinalRejReviewed->created_at)
            ->exists();

    $lastFinalRejection = $project->statusHistories()->where('status', \App\Models\Project::STATUS_FINAL_REJECTED)->latest()->first();
    $finalRejectionReason = $lastFinalRejection ? ($lastFinalRejection->metadata['comment'] ?? $lastFinalRejection->metadata['reason'] ?? null) : null;

    // Which sections are locked:
    //  - Progress: locked when the deadline passes — until then the LPI may
    //    submit AND overwrite freely (UNLESS resubmission requested)
    //  - Final: locked if deadline passed OR submitted (UNLESS resubmission requested)
    $progressDeadlinePassed = false;
    $finalDeadlinePassed = false;
    if (isset($deadlines['progress'])) {
        $progressDl = $deadlines['progress'];
        $progressEffectiveDl = $progressDl['original'] ?? null;
        $progressDeadlinePassed = $progressEffectiveDl ? now()->greaterThan($progressEffectiveDl) : true;
    }
    if (isset($deadlines['final'])) {
        $finalDl = $deadlines['final'];
        $finalEffectiveDl = $finalDl['original'] ?? null;
        $finalDeadlinePassed = $finalEffectiveDl ? now()->greaterThan($finalEffectiveDl) : true;
    }

    // Lock only if NOT resubmission requested.
    // Progress: gated ONLY by the deadline (submission alone does not lock);
    // the LPI can re-upload/overwrite until the deadline arrives.
    $progressLocked = !$progressResubmitRequested && $progressDeadlinePassed;

    // Progress Report 2 (extended progress): same semantics as progress report 1,
    // using the extended progress report deadline (falls back to the progress one).
    $progress2DeadlinePassed = false;
    if (isset($deadlines['progress2'])) {
        $progress2Dl = $deadlines['progress2'];
        $progress2EffectiveDl = $progress2Dl['original'] ?? null;
        $progress2DeadlinePassed = $progress2EffectiveDl ? now()->greaterThan($progress2EffectiveDl) : true;
    }
    $progress2Locked = !$progress2ResubmitRequested && $progress2DeadlinePassed;
    $finalLocked = !$finalResubmitRequested && $finalDeadlinePassed;
    // Outcomes & Students stay open until the final report deadline arrives.
    // They do NOT reopen during a final resubmission — only the final-report
    // upload part re-opens (matches the server-side data lock).
    $outcomesAndStaffLocked = $finalDeadlinePassed;

    // Resubmission requests: admin reviewed rejection and sent back to LPI (open until reviewer grades)
    $lastProgressRejReviewed = $project
        ? $project->statusHistories()->where('status', \App\Models\Project::STATUS_PROGRESS_REJ_REVIEWED)->latest()->first()
        : null;
    $progressResubmitRequested = $lastProgressRejReviewed
        && ($lastProgressRejReviewed->metadata['action'] ?? null) === 'send_to_lpi'
        && !$project->statusHistories()
            ->whereIn('status', [\App\Models\Project::STATUS_PROGRESS_REVIEWED, \App\Models\Project::STATUS_PROGRESS_REJECTED])
            ->where('created_at', '>', $lastProgressRejReviewed->created_at)
            ->exists();

    $lastProgressRejection = $project
        ? $project->statusHistories()->where('status', \App\Models\Project::STATUS_PROGRESS_REJECTED)->latest()->first()
        : null;
    $progressRejectionReason = $lastProgressRejection ? ($lastProgressRejection->metadata['comment'] ?? $lastProgressRejection->metadata['reason'] ?? null) : null;

    $lastFinalRejReviewed = $project
        ? $project->statusHistories()->where('status', \App\Models\Project::STATUS_FINAL_REJ_REVIEWED)->latest()->first()
        : null;
    $finalResubmitRequested = $lastFinalRejReviewed
        && ($lastFinalRejReviewed->metadata['action'] ?? null) === 'send_to_lpi'
        && !$project->statusHistories()
            ->whereIn('status', [\App\Models\Project::STATUS_GRADED, \App\Models\Project::STATUS_FINAL_REJECTED])
            ->where('created_at', '>', $lastFinalRejReviewed->created_at)
            ->exists();

    $lastFinalRejection = $project
        ? $project->statusHistories()->where('status', \App\Models\Project::STATUS_FINAL_REJECTED)->latest()->first()
        : null;
    $finalRejectionReason = $lastFinalRejection ? ($lastFinalRejection->metadata['comment'] ?? $lastFinalRejection->metadata['reason'] ?? null) : null;
@endphp

@extends('layouts.app')

@section('title', 'Update Progress — ' . ($project->title ?? 'Project'))

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-chart-line"></i> Update Progress</h1>
        <p>
            <span style="color:#8d1b3d; font-weight:600;">{{ $project->old_project_id }}</span>
            <span style="color:var(--ink-400); margin:0 6px;">·</span>
            {{ $project->title ?? '' }}
        </p>
    </div>
    <div class="page-actions">
        <a href="{{ route('projects.show', $project->id) }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
    </div>
</div>

{{-- ========== TABBED FORM ========== --}}
<form id="mainProgressForm" action="{{ $isFinalMode ? route('progress.save-final', $project->id) : route('progress.save', $project->id) }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Priority 0: Progress 2 resubmission requested (extended progress) --}}
    @if($extendedProgress && $progress2ResubmitRequested)
    <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:14px 18px; margin-bottom:16px;">
        <p style="margin:0; color:#9a3412; font-size:13px; font-style:italic;">
            <strong>Progress Report 2 Resubmission Requested</strong> — The reviewer rejected your Progress Report 2. Please review the concerns below and upload a revised version.
        </p>
        @if($lastProgress2RejReviewed->metadata['admin_message'] ?? null)
        <p style="margin:6px 0 0; color:#78350f; font-size:12px;">
            <strong>Admin message:</strong> {{ $lastProgress2RejReviewed->metadata['admin_message'] }}
        </p>
        @endif
        @if($progress2RejectionReason)
        <p style="margin:6px 0 0; color:#9a3412; font-size:12px;">
            <strong>Reviewer's rejection reason:</strong> {{ $progress2RejectionReason }}
        </p>
        @endif
    </div>
    {{-- Priority 1: Resubmission requested (show first) --}}
    @elseif($progressResubmitRequested)
    <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:14px 18px; margin-bottom:16px;">
        <p style="margin:0; color:#9a3412; font-size:13px; font-style:italic;">
            <strong>Progress Resubmission Requested</strong> — The reviewer rejected your progress report. Please review the concerns below and upload a revised version.
        </p>
        @if($lastProgressRejReviewed->metadata['admin_message'] ?? null)
        <p style="margin:6px 0 0; color:#78350f; font-size:12px;">
            <strong>Admin message:</strong> {{ $lastProgressRejReviewed->metadata['admin_message'] }}
        </p>
        @endif
        @if($progressRejectionReason)
        <p style="margin:6px 0 0; color:#9a3412; font-size:12px;">
            <strong>Reviewer's rejection reason:</strong> {{ $progressRejectionReason }}
        </p>
        @endif
    </div>
    @elseif($finalResubmitRequested)
    <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:14px 18px; margin-bottom:16px;">
        <p style="margin:0; color:#9a3412; font-size:13px; font-style:italic;">
            <strong>Final Report Resubmission Requested</strong> — The reviewer rejected your final report. Please review the concerns below and upload a revised version.
        </p>
        @if($lastFinalRejReviewed->metadata['admin_message'] ?? null)
        <p style="margin:6px 0 0; color:#78350f; font-size:12px;">
            <strong>Admin message:</strong> {{ $lastFinalRejReviewed->metadata['admin_message'] }}
        </p>
        @endif
        @if($finalRejectionReason)
        <p style="margin:6px 0 0; color:#9a3412; font-size:12px;">
            <strong>Reviewer's rejection reason:</strong> {{ $finalRejectionReason }}
        </p>
        @endif
    </div>
    {{-- Priority 2: Deadline messages --}}
    @elseif($finalDeadlinePassed)
    <div style="background:#fef2f2; border:1px solid var(--danger); border-radius:8px; padding:14px 18px; margin-bottom:16px;">
        <p style="margin:0; color:#991b1b; font-size:13px; font-style:italic;">
            <strong>Final Report Deadline Passed</strong> — The deadline has passed. Outcomes, Students & Researchers, Final Report, and Readiness Report are now readonly.
        </p>
    </div>
    @elseif($progressDeadlinePassed)
    <div style="background:#eef6fd; border:1px solid #a8cbe8; border-radius:8px; padding:14px 18px; margin-bottom:16px;">
        <p style="margin:0; color:#1d6fb8; font-size:13px; font-style:italic;">
            <strong>Progress Report Deadline Passed</strong> — The progress report upload is now closed. You can still update Outcomes and Students & Researchers.
        </p>
    </div>
    @elseif($progressSubmitted)
    <div style="background:#eef6fd; border:1px solid #a8cbe8; border-radius:8px; padding:14px 18px; margin-bottom:16px;">
        <p style="margin:0; color:#1d6fb8; font-size:13px; font-style:italic;">
            <strong>Progress Report Submitted</strong> — Your progress report has been submitted. You may still re-upload an updated version until the progress report deadline arrives.
        </p>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB HEADER --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="ws-tabs" role="tablist">
        <button type="button" class="ws-tab active" role="tab" data-tab="tab-outcomes" onclick="switchTab('tab-outcomes', this)">
            <i class="fas fa-trophy" style="font-size:12px;"></i> Outcomes
        </button>
        <button type="button" class="ws-tab" role="tab" data-tab="tab-staff" onclick="switchTab('tab-staff', this)">
            <i class="fas fa-users" style="font-size:12px;"></i> Students & Researchers
        </button>
        <button type="button" class="ws-tab" role="tab" data-tab="tab-progress-report" onclick="switchTab('tab-progress-report', this)">
            <i class="fas fa-chart-line" style="font-size:12px;"></i> Progress Report
        </button>
        <button type="button" class="ws-tab" role="tab" data-tab="tab-final-report" onclick="switchTab('tab-final-report', this)">
            <i class="fas fa-file-signature" style="font-size:12px;"></i> Final Report
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: OUTCOMES (Scholarly Articles + Intellectual Property) --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-outcomes" class="ws-tab-panel" role="tabpanel">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
        <div class="ws-section-block">
            <h2 class="ws-section-title">Scholarly Articles</h2>

                    @php
                        $scholarlyTypes = [
                            'journal_q1'    => 'Journal articles (Web of Science — Q1)',
                            'journal_q2'    => 'Journal articles (Web of Science — Q2)',
                            'journal_q3'    => 'Journal articles (Web of Science — Q3)',
                            'journal_q4'    => 'Journal articles (Web of Science — Q4)',
                            'conference'    => 'Indexed international conferences',
                            'book'          => 'Published Books',
                            'edited_book'   => 'Edited Books (collection)',
                            'book_chapter'  => 'Book Chapters',
                        ];
                        $contribTypeKeys = ['ip_disclosure','provisional_patent','patent_granted','open_source_sw','startup','cross_college','research_awards'];
                        $scholarlyOutcomes = $outcomes->filter(function($o) use ($contribTypeKeys) { return !in_array($o->type, $contribTypeKeys); });
                    @endphp

                @if($outcomesAndStaffLocked)
                {{-- READONLY TABLE VIEW --}}
                <div class="ws-card" style="padding:16px 18px;">
                    @if($scholarlyOutcomes->count() > 0)
                    <table class="ws-table">
                        <thead>
                            <tr>
                                <th style="width:40px;"></th>
                                <th>Type</th>
                                <th>Publication Details</th>
                                <th>Verified</th>
                                <th style="width:80px;">Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scholarlyOutcomes as $so)
                            @php
                                $journal = strtolower($so->publication->journal ?? '');
                                $publisherBadge = '';
                                $publisherName = '';
                                $publisherColor = '555555';
                                
                                if (str_contains($journal, 'springer')) {
                                    $publisherBadge = 'Springer';
                                    $publisherColor = '0d6b3b';
                                    $publisherName = 'Springer';
                                } elseif (str_contains($journal, 'elsevier') || str_contains($journal, 'sciencedirect')) {
                                    $publisherBadge = 'Elsevier';
                                    $publisherColor = 'ff6c0f';
                                    $publisherName = 'Elsevier';
                                } elseif (str_contains($journal, 'ieee')) {
                                    $publisherBadge = 'IEEE';
                                    $publisherColor = '00629b';
                                    $publisherName = 'IEEE';
                                } elseif (str_contains($journal, 'wiley')) {
                                    $publisherBadge = 'Wiley';
                                    $publisherColor = '005a9c';
                                    $publisherName = 'Wiley';
                                } elseif (str_contains($journal, 'taylor') || str_contains($journal, 'francis')) {
                                    $publisherBadge = 'T%26F';
                                    $publisherColor = 'b7282e';
                                    $publisherName = 'Taylor & Francis';
                                } elseif (str_contains($journal, 'mdpi')) {
                                    $publisherBadge = 'MDPI';
                                    $publisherColor = '0067a5';
                                    $publisherName = 'MDPI';
                                } elseif (str_contains($journal, 'acm')) {
                                    $publisherBadge = 'ACM';
                                    $publisherColor = '0076a8';
                                    $publisherName = 'ACM';
                                } elseif (str_contains($journal, 'oxford')) {
                                    $publisherBadge = 'OUP';
                                    $publisherColor = '002147';
                                    $publisherName = 'Oxford University Press';
                                } elseif (str_contains($journal, 'cambridge')) {
                                    $publisherBadge = 'CUP';
                                    $publisherColor = 'c8e6c9';
                                    $publisherName = 'Cambridge University Press';
                                } elseif (str_contains($journal, 'sage')) {
                                    $publisherBadge = 'SAGE';
                                    $publisherColor = 'ff8f1c';
                                    $publisherName = 'SAGE';
                                } elseif (str_contains($journal, 'nature')) {
                                    $publisherBadge = 'Nature';
                                    $publisherColor = '0070c0';
                                    $publisherName = 'Nature';
                                } elseif (str_contains($journal, 'science')) {
                                    $publisherBadge = 'Science';
                                    $publisherColor = 'cc0000';
                                    $publisherName = 'Science';
                                }
                            @endphp
                            <tr>
                                <td style="text-align:center;">
                                    @if($publisherBadge)
                                        <img src="https://img.shields.io/badge/{{ $publisherBadge }}-{{ $publisherColor }}?style=flat&logo=&logoColor=white" alt="{{ $publisherName }}" style="height:20px;" title="{{ $publisherName }}">
                                    @else
                                        <div style="width:24px;height:24px;border-radius:4px;background:var(--ink-200);display:flex;align-items:center;justify-content:center;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--ink-500)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:11px;">
                                    <span style="padding:2px 6px;border-radius:4px;background:var(--ink-200);color:var(--ink-600);font-weight:500;">{{ $scholarlyTypes[$so->type] ?? $so->type }}</span>
                                </td>
                                <td style="font-size:12px;">
                                    @if($so->publication)
                                        @if($so->publication->publication_title)
                                            <div style="font-weight:500;color:var(--ink-700);">{{ Str::limit($so->publication->publication_title, 70) }}</div>
                                        @endif
                                        <div style="color:var(--ink-500);margin-top:2px;">
                                            @if($so->publication->journal)
                                                <span>{{ $so->publication->journal }}</span>
                                            @endif
                                            @if($so->publication->year)
                                                <span> ({{ $so->publication->year }})</span>
                                            @endif
                                        </div>
                                        @if($so->publication->authors)
                                            <div style="color:var(--ink-400);font-size:11px;margin-top:2px;">{{ Str::limit($so->publication->authors, 80) }}</div>
                                        @endif
                                    @else
                                        <span style="color:var(--ink-400);">{{ $so->identifier }}</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($so->publication)
                                        <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:#d1fae5;color:#065f46;font-weight:500;display:inline-flex;align-items:center;gap:3px;">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Verified
                                        </span>
                                    @else
                                        <span style="color:var(--ink-400);font-size:11px;">—</span>
                                    @endif
                                </td>
                                <td style="font-size:12px;white-space:nowrap;">
                                    @if($so->publication && $so->publication->url)
                                        <a href="{{ $so->publication->url }}" target="_blank" style="color:var(--brand-500);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                            View
                                        </a>
                                    @elseif($so->identifier)
                                        <a href="https://doi.org/{{ $so->identifier }}" target="_blank" style="color:var(--brand-500);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                            View
                                        </a>
                                    @else
                                        <span style="color:var(--ink-400);">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No scholarly articles recorded.</p>
                    @endif
                </div>
                @else
                {{-- EDITABLE FORM --}}
                <div class="ws-card" style="padding:16px 18px;">
                    <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                        <div style="flex:1;min-width:180px;">
                            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Article Type</label>
                            <select id="scholarlyType" class="ws-input" style="width:100%;">
                                @foreach($scholarlyTypes as $tKey => $tLabel)
                                    <option value="{{ $tKey }}">{{ $tLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1;min-width:180px;">
                            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">DOI / Identifier</label>
                            <input type="text" id="scholarlyIdentifier" class="ws-input" placeholder="Enter DOI / ISBN…" style="width:100%;">
                        </div>
                        <button type="button" class="ws-btn ws-btn-primary ws-btn-sm" onclick="addScholarlyRecord()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Article
                        </button>
                    </div>

                    <div id="scholarlyRecords" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                        @foreach($scholarlyOutcomes as $so)
                        @php
                            $journal = strtolower($so->publication->journal ?? '');
                            $publisherBadge = '';
                            $publisherName = '';
                            $publisherColor = '555555';
                            
                            if (str_contains($journal, 'springer')) {
                                $publisherBadge = 'Springer';
                                $publisherColor = '0d6b3b';
                                $publisherName = 'Springer';
                            } elseif (str_contains($journal, 'elsevier') || str_contains($journal, 'sciencedirect')) {
                                $publisherBadge = 'Elsevier';
                                $publisherColor = 'ff6c0f';
                                $publisherName = 'Elsevier';
                            } elseif (str_contains($journal, 'ieee')) {
                                $publisherBadge = 'IEEE';
                                $publisherColor = '00629b';
                                $publisherName = 'IEEE';
                            } elseif (str_contains($journal, 'wiley')) {
                                $publisherBadge = 'Wiley';
                                $publisherColor = '005a9c';
                                $publisherName = 'Wiley';
                            } elseif (str_contains($journal, 'taylor') || str_contains($journal, 'francis')) {
                                $publisherBadge = 'T%26F';
                                $publisherColor = 'b7282e';
                                $publisherName = 'Taylor & Francis';
                            } elseif (str_contains($journal, 'mdpi')) {
                                $publisherBadge = 'MDPI';
                                $publisherColor = '0067a5';
                                $publisherName = 'MDPI';
                            } elseif (str_contains($journal, 'acm')) {
                                $publisherBadge = 'ACM';
                                $publisherColor = '0076a8';
                                $publisherName = 'ACM';
                            } elseif (str_contains($journal, 'oxford')) {
                                $publisherBadge = 'OUP';
                                $publisherColor = '002147';
                                $publisherName = 'Oxford University Press';
                            } elseif (str_contains($journal, 'cambridge')) {
                                $publisherBadge = 'CUP';
                                $publisherColor = 'c8e6c9';
                                $publisherName = 'Cambridge University Press';
                            } elseif (str_contains($journal, 'sage')) {
                                $publisherBadge = 'SAGE';
                                $publisherColor = 'ff8f1c';
                                $publisherName = 'SAGE';
                            } elseif (str_contains($journal, 'nature')) {
                                $publisherBadge = 'Nature';
                                $publisherColor = '0070c0';
                                $publisherName = 'Nature';
                            } elseif (str_contains($journal, 'science')) {
                                $publisherBadge = 'Science';
                                $publisherColor = 'cc0000';
                                $publisherName = 'Science';
                            }
                        @endphp
                        <div class="scholarly-record-row" data-id="{{ $so->id }}" style="padding:10px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <input type="hidden" name="outcomes[{{ $so->type }}][existing][]" value="{{ $so->id }}">
                                <input type="hidden" name="outcomes[{{ $so->type }}][detail][]" value="{{ $so->identifier }}">
                                @if($publisherBadge)
                                    <img src="https://img.shields.io/badge/{{ $publisherBadge }}-{{ $publisherColor }}?style=flat&logo=&logoColor=white" alt="{{ $publisherName }}" style="height:18px;flex-shrink:0;" title="{{ $publisherName }}">
                                @else
                                    <div style="width:22px;height:22px;border-radius:4px;background:var(--ink-200);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--ink-500)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    </div>
                                @endif
                                <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--ink-200);color:var(--ink-600);font-weight:500;flex-shrink:0;">{{ $scholarlyTypes[$so->type] ?? $so->type }}</span>
                                <span style="flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);">{{ $so->identifier }}</span>
                                @if($so->publication)
                                    <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:#d1fae5;color:#065f46;font-weight:500;flex-shrink:0;display:inline-flex;align-items:center;gap:3px;">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Verified
                                    </span>
                                @else
                                    <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:500;flex-shrink:0;">Not Verified</span>
                                    <button type="button" class="ws-btn ws-btn-outline" onclick="verifyOutcome(this, '{{ $so->id }}', '{{ $so->identifier }}')" style="font-size:10px;padding:2px 8px;height:auto;flex-shrink:0;">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                        Verify
                                    </button>
                                @endif
                                @if($so->publication && $so->publication->url)
                                <a href="{{ $so->publication->url }}" target="_blank" style="color:var(--brand-500);text-decoration:none;font-size:11px;display:inline-flex;align-items:center;gap:3px;flex-shrink:0;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    View
                                </a>
                                @endif
                                <button type="button" class="ws-btn-icon ws-btn-icon-danger" onclick="deleteOutcomeRecord(this, '{{ $so->id }}')" title="Delete" style="flex-shrink:0;width:26px;height:26px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                            @if($so->publication)
                            <div style="margin-top:6px;padding-top:6px;border-top:1px solid var(--ink-100);font-size:11px;color:var(--ink-500);">
                                @if($so->publication->publication_title)
                                    <span style="font-weight:500;color:var(--ink-700);">{{ Str::limit($so->publication->publication_title, 60) }}</span>
                                @endif
                                @if($so->publication->journal)
                                    <span> — {{ $so->publication->journal }}</span>
                                @endif
                                @if($so->publication->year)
                                    <span>({{ $so->publication->year }})</span>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                        @if($scholarlyOutcomes->count() === 0)
                        <div id="scholarlyEmpty" style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No scholarly articles added yet.</div>
                        @endif
                    </div>
                </div>
                @endif
        </div>

        <div class="ws-section-block">
            <h2 class="ws-section-title">Intellectual Property</h2>

                @php
                    $ipTypes = [
                        'ip_disclosure'      => 'Intellectual Property Disclosure',
                        'provisional_patent' => 'Provisional Patent',
                        'patent_granted'     => 'Patents Granted',
                        'open_source_sw'     => 'Open Source Software',
                        'startup'            => 'Start-Up Created',
                    ];
                    $ipOutcomes = $outcomes->whereIn('type', array_keys($ipTypes));
                @endphp

                @if($outcomesAndStaffLocked)
                {{-- READONLY TABLE VIEW --}}
                <div class="ws-card" style="padding:16px 18px;">
                    @if($ipOutcomes->count() > 0)
                    <table class="ws-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ipOutcomes as $io)
                            <tr>
                                <td><span class="ws-pill ws-pill-brand">{{ $ipTypes[$io->type] ?? ucfirst(str_replace('_',' ',$io->type)) }}</span></td>
                                <td style="font-family:monospace;font-size:12px;">{{ $io->identifier }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No IP records recorded.</p>
                    @endif
                </div>
                @else
                {{-- EDITABLE FORM --}}
                <div class="ws-card" style="padding:16px 18px;">
                    <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                        <div style="flex:1;min-width:180px;">
                            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">IP Type</label>
                            <select id="ipType" class="ws-input" style="width:100%;">
                                @foreach($ipTypes as $tKey => $tLabel)
                                    <option value="{{ $tKey }}">{{ $tLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1;min-width:180px;">
                            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Detail</label>
                            <input type="text" id="ipDetail" class="ws-input" placeholder="Enter detail…" style="width:100%;">
                        </div>
                        <button type="button" class="ws-btn ws-btn-primary ws-btn-sm" onclick="addIpRecord()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add IP
                        </button>
                    </div>

                    <div id="ipRecords" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                        @foreach($ipOutcomes as $io)
                        <div class="ip-record-row" data-id="{{ $io->id }}" style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;">
                            <input type="hidden" name="outcomes[{{ $io->type }}][existing][]" value="{{ $io->id }}">
                            <input type="hidden" name="outcomes[{{ $io->type }}][detail][]" value="{{ $io->identifier }}">
                            <span class="ws-pill ws-pill-brand" style="flex-shrink:0;">{{ $ipTypes[$io->type] ?? ucfirst(str_replace('_',' ',$io->type)) }}</span>
                            <span style="flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);word-break:break-all;">{{ $io->identifier }}</span>
                            <button type="button" class="ws-btn-icon ws-btn-icon-danger" onclick="deleteOutcomeRecord(this, '{{ $io->id }}')" title="Delete" style="flex-shrink:0;width:26px;height:26px;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        @endforeach
                        @if($ipOutcomes->count() === 0)
                        <div id="ipEmpty" style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No IP records added yet.</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: STUDENTS & RESEARCHERS --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-staff" class="ws-tab-panel" style="display:none;" role="tabpanel">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
        <div class="ws-section-block">
            <h2 class="ws-section-title">Students</h2>
                @if($outcomesAndStaffLocked)
                {{-- READONLY TABLE VIEW --}}
                @if($projectStudents->count() > 0)
                <table class="ws-table">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>College</th>
                            <th>Major / Program</th>
                            <th>Status</th>
                            <th>Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projectStudents as $s)
                        @php
                            $details = $s->details;
                        @endphp
                        <tr>
                            <td><span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--brand-100);color:var(--brand-700);font-weight:500;">{{ $s->type == 'UG' ? 'Undergraduate' : ($s->type == 'masters' ? 'Masters' : 'PhD') }}</span></td>
                            <td style="font-family:monospace;font-size:12px;font-weight:500;">{{ $s->std_id }}</td>
                            <td style="font-size:12px;">
                                @if($details)
                                    <div style="font-weight:500;color:var(--ink-700);">{{ $details->full_name }}</div>
                                    @if($details->admission_term)
                                        <div style="font-size:10px;color:var(--ink-400);">Admit: {{ $details->admission_term }}</div>
                                    @endif
                                @else
                                    <span style="color:var(--ink-400);">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px;">
                                @if($details && $details->college)
                                    <div style="color:var(--ink-600);">{{ $details->college }}</div>
                                    @if($details->std_level)
                                        <div style="font-size:10px;color:var(--ink-400);">{{ $details->std_level }}</div>
                                    @endif
                                @else
                                    <span style="color:var(--ink-400);">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px;">
                                @if($details)
                                    @if($details->major)
                                        <div style="color:var(--ink-600);">{{ $details->major }}</div>
                                    @endif
                                    @if($details->std_program)
                                        <div style="font-size:10px;color:var(--ink-400);">{{ $details->std_program }}</div>
                                    @endif
                                    @if($details->minor && $details->minor !== 'Undeclared')
                                        <div style="font-size:10px;color:var(--ink-400);">Minor: {{ $details->minor }}</div>
                                    @endif
                                @else
                                    <span style="color:var(--ink-400);">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px;">
                                @if($details && $details->student_status)
                                    <span class="ws-pill {{ $details->student_status === 'Active' ? 'ws-pill-success' : 'ws-pill-ink' }}">{{ $details->student_status }}</span>
                                    @if($details->reg_in_course)
                                        <div style="font-size:10px;color:var(--ink-400);margin-top:2px;">{{ $details->reg_in_course }}</div>
                                    @endif
                                @else
                                    <span style="color:var(--ink-400);">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px;text-align:right;">{{ $s->days }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No students recorded.</p>
                @endif
                @else
                {{-- EDITABLE FORM --}}
                <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                    <div style="flex:0 0 130px;max-width:100%;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Level</label>
                        <select id="studentLevel" class="ws-input" style="width:100%;">
                            <option value="UG">Undergraduate</option>
                            <option value="masters">Masters</option>
                            <option value="PhD">PhD</option>
                        </select>
                    </div>
                    <div style="flex:1;min-width:120px;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Student ID</label>
                        <input type="text" id="studentId" class="ws-input" placeholder="Enter student ID…" style="width:100%;">
                    </div>
                    <div style="flex:0 0 80px;max-width:100%;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Days</label>
                        <input type="number" id="studentDays" class="ws-input" placeholder="0" min="0" max="365" style="width:100%;">
                    </div>
                    <button type="button" class="ws-btn ws-btn-primary ws-btn-sm" onclick="addStudentRecord()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Student
                    </button>
                </div>

                <div id="studentRecords" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                    @foreach($projectStudents as $s)
                    @php
                        $details = $s->details;
                    @endphp
                    <div class="student-item-row" data-id="{{ $s->id }}" data-type="{{ $s->type }}" style="padding:10px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;">
                        <input type="hidden" name="students[{{ $s->id }}][existing]" value="{{ $s->id }}">
                        <input type="hidden" name="students[{{ $s->id }}][type]" value="{{ $s->type }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($details && $details->std_level)
                                <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--brand-100);color:var(--brand-700);font-weight:600;flex-shrink:0;">{{ $details->std_level }}</span>
                            @else
                                <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--ink-200);color:var(--ink-600);font-weight:500;flex-shrink:0;">{{ $s->type == 'UG' ? 'UG' : ($s->type == 'masters' ? 'MSc' : 'PhD') }}</span>
                            @endif
                            <span style="flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $s->std_id }}</span>
                            @if($details)
                                <span style="font-size:9px;padding:2px 5px;border-radius:3px;background:#d1fae5;color:#065f46;font-weight:500;flex-shrink:0;">✓</span>
                            @else
                                <span style="font-size:9px;padding:2px 5px;border-radius:3px;background:#fef3c7;color:#92400e;font-weight:500;flex-shrink:0;">!</span>
                            @endif
                            <span style="font-size:10px;color:var(--ink-500);flex-shrink:0;white-space:nowrap;">{{ $s->days }}d</span>
                            <button type="button" class="ws-btn-icon ws-btn-icon-danger" onclick="deleteStudentRecord(this, '{{ $s->id }}')" title="Delete" style="flex-shrink:0;width:22px;height:22px;">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        @if($details && ($details->full_name || $details->college || $details->std_program || $details->major))
                        <div style="margin-top:5px;padding-top:5px;border-top:1px solid var(--ink-100);font-size:11px;color:var(--ink-500);">
                            @if($details->full_name)
                                <span style="font-weight:500;color:var(--ink-700);">{{ $details->full_name }}</span>
                            @endif
                            @if($details->college)
                                <span> — {{ $details->college }}</span>
                            @endif
                            @if($details->std_program)
                                <span>({{ $details->std_program }})</span>
                            @endif
                            @if($details->major)
                                <span> | {{ $details->major }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                    @if($projectStudents->count() === 0)
                    <div id="studentEmpty" style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No students added yet.</div>
                    @endif
                </div>
                @endif
        </div>

        <div class="ws-section-block">
            <h2 class="ws-section-title">Hired Researchers</h2>
                @if($outcomesAndStaffLocked)
                {{-- READONLY TABLE VIEW --}}
                @if($projectResearchers->count() > 0)
                <table class="ws-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projectResearchers as $r)
                        <tr>
                            <td style="font-size:12px;">{{ $r->name }}</td>
                            <td><span class="ws-pill ws-pill-brand">{{ $r->category }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No researchers recorded.</p>
                @endif
                @else
                {{-- EDITABLE FORM --}}
                <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                    <div style="flex:1;min-width:120px;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Name</label>
                        <input type="text" id="researcherName" class="ws-input" placeholder="Researcher name…" style="width:100%;">
                    </div>
                    <div style="flex:0 0 90px;max-width:100%;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Category</label>
                        <select id="researcherCategory" class="ws-input" style="width:100%;">
                            <option value="RA">RA</option>
                            <option value="GA">GA</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <button type="button" class="ws-btn ws-btn-primary ws-btn-sm" onclick="addResearcherRecord()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Researcher
                    </button>
                </div>

                <div id="researcherRecords" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                    @foreach($projectResearchers as $r)
                    <div class="researcher-item-row" data-id="{{ $r->id }}" style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;">
                        <input type="hidden" name="researchers[{{ $r->id }}][existing]" value="{{ $r->id }}">
                        <input type="hidden" name="researchers[{{ $r->id }}][name]" value="{{ $r->name }}">
                        <input type="hidden" name="researchers[{{ $r->id }}][category]" value="{{ $r->category }}">
                        <span class="ws-pill ws-pill-brand" style="flex-shrink:0;">{{ $r->category }}</span>
                        <span style="flex:1;font-size:12px;color:var(--ink-700);word-break:break-all;">{{ $r->name }}</span>
                        <button type="button" class="ws-btn-icon ws-btn-icon-danger" onclick="deleteResearcherRecord(this, '{{ $r->id }}')" title="Delete" style="flex-shrink:0;width:26px;height:26px;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                    @endforeach
                    @if($projectResearchers->count() === 0)
                    <div id="researcherEmpty" style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No researchers added yet.</div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- ── CROSS-COLLEGE PARTICIPATION & RESEARCH AWARDS (toggles) ── --}}
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-top:18px;">
            <div class="ws-card" style="padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;border-bottom:1px solid var(--ink-100);padding-bottom:8px;">
                    <span style="font-size:12px;font-weight:600;color:var(--brand-600);line-height:1.3;">Cross-College Participation</span>
                    @if($outcomesAndStaffLocked)
                    <span class="ws-pill {{ $crossCollegeValue === 'Yes' ? 'ws-pill-success' : 'ws-pill-ink' }}">{{ $crossCollegeValue }}</span>
                    @else
                    <label class="ws-toggle" style="flex-shrink:0;">
                        <input type="hidden" name="cross_college" value="No">
                        <input type="checkbox" name="cross_college" value="Yes" class="ws-toggle-checkbox" data-details-id="details-cross-college" onchange="saveToggle('cross_college', this.checked ? 'Yes' : 'No')" {{ $crossCollegeValue === 'Yes' ? 'checked' : '' }}>
                        <span class="ws-toggle-slider" style="width:58px;height:26px;">
                            <span class="ws-toggle-label ws-toggle-no" style="font-size:10px;">No</span>
                            <span class="ws-toggle-label ws-toggle-yes" style="font-size:10px;">Yes</span>
                        </span>
                    </label>
                    @endif
                </div>
                @if($outcomesAndStaffLocked)
                @if($crossCollegeValue === 'Yes' && $crossCollegeDetails)
                <p style="font-size:12px;color:var(--ink-700);margin:0;line-height:1.5;">{{ $crossCollegeDetails }}</p>
                @else
                <p style="font-size:12px;color:var(--ink-400);margin:0;text-align:center;">No cross-college participation.</p>
                @endif
                @else
                <div id="details-cross-college" class="ws-card-details" style="margin-top:0;padding-top:0;border-top:none;{{ $crossCollegeValue === 'Yes' ? '' : 'display:none;' }}">
                    <textarea name="cross_college_detail" class="ws-input ws-textarea" rows="2" placeholder="Describe cross-college participation…" style="font-size:12px;" onblur="saveToggle('cross_college', document.querySelector('input[name=\'cross_college\'][value=\'Yes\']').checked ? 'Yes' : 'No')">{{ $crossCollegeDetails }}</textarea>
                </div>
                @endif
            </div>

            <div class="ws-card" style="padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;border-bottom:1px solid var(--ink-100);padding-bottom:8px;">
                    <span style="font-size:12px;font-weight:600;color:var(--brand-600);line-height:1.3;">Research Awards</span>
                    @if($outcomesAndStaffLocked)
                    <span class="ws-pill {{ $researchAwardsValue === 'Yes' ? 'ws-pill-success' : 'ws-pill-ink' }}">{{ $researchAwardsValue }}</span>
                    @else
                    <label class="ws-toggle" style="flex-shrink:0;">
                        <input type="hidden" name="research_awards" value="No">
                        <input type="checkbox" name="research_awards" value="Yes" class="ws-toggle-checkbox" data-details-id="details-research-awards" onchange="saveToggle('research_awards', this.checked ? 'Yes' : 'No')" {{ $researchAwardsValue === 'Yes' ? 'checked' : '' }}>
                        <span class="ws-toggle-slider" style="width:58px;height:26px;">
                            <span class="ws-toggle-label ws-toggle-no" style="font-size:10px;">No</span>
                            <span class="ws-toggle-label ws-toggle-yes" style="font-size:10px;">Yes</span>
                        </span>
                    </label>
                    @endif
                </div>
                @if($outcomesAndStaffLocked)
                @if($researchAwardsValue === 'Yes' && $researchAwardsDetails)
                <p style="font-size:12px;color:var(--ink-700);margin:0;line-height:1.5;">{{ $researchAwardsDetails }}</p>
                @else
                <p style="font-size:12px;color:var(--ink-400);margin:0;text-align:center;">No research awards.</p>
                @endif
                @else
                <div id="details-research-awards" class="ws-card-details" style="margin-top:0;padding-top:0;border-top:none;{{ $researchAwardsValue === 'Yes' ? '' : 'display:none;' }}">
                    <textarea name="research_awards_detail" class="ws-input ws-textarea" rows="2" placeholder="Describe research awards…" style="font-size:12px;" onblur="saveToggle('research_awards', document.querySelector('input[name=\'research_awards\'][value=\'Yes\']').checked ? 'Yes' : 'No')">{{ $researchAwardsDetails }}</textarea>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: PROGRESS REPORT --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-progress-report" class="ws-tab-panel" style="display:none;" role="tabpanel">
        @php
            $progressLatest = $progressSub ?? null;
            $progressDl = $deadlines['progress'] ?? [];
            $progressEffectiveDeadline = $progressDl['original'] ?? null;
            $progressDeadlinePassed = $progressEffectiveDeadline ? now()->greaterThan($progressEffectiveDeadline) : true;
        @endphp

        <div class="ws-card" style="max-width:480px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--ink-100);">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--brand-50);color:var(--brand-600);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <strong style="font-size:13px;color:var(--ink-800);">Progress Report</strong>
                    @if($progressEffectiveDeadline)
                        <div style="font-size:11px;color:{{ $progressDeadlinePassed ? 'var(--danger)' : 'var(--ink-400)' }};">
                            Deadline: {{ $progressEffectiveDeadline->format('M d, Y') }}
                            @if($progressDeadlinePassed) <span style="font-weight:600;">(Passed)</span> @endif
                        </div>
                    @endif
                </div>
            </div>

<div class="ws-upload-card {{ ($progressDeadlinePassed || $progressLocked) && !$progressResubmitRequested ? 'ws-upload-card--deadline-passed' : '' }}" data-type="progress" data-versioned="{{ $progressResubmitRequested ? '1' : '0' }}" data-can-delete="{{ $progressResubmitRequested || !$progressDeadlinePassed ? '1' : '0' }}">
                <div class="ws-current-file" data-type="progress">
                    @php
                        $progressVersions = $submissions->where('type', 'progress')->sortBy('version');
                        $progressNextVersion = $progressResubmitRequested ? 2 : (($progressVersions->max('version') ?? 0) + 1);
                    @endphp
                    @if($progressVersions->count() > 0)
                        @foreach($progressVersions as $pv)
                        <div class="ws-file-row" data-id="{{ $pv->id }}" style="margin-bottom:4px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <a href="{{ route('serveFile2', ['type' => 'progress', 'id' => $project->id, 'submission_id' => $pv->id]) }}" target="_blank" class="ws-file-link">{{ $pv->stored_filename }}</a>
                            <span class="ws-file-version" style="font-size:10px;font-weight:600;color:{{ $loop->last ? 'var(--success)' : 'var(--ink-400)' }};background:{{ $loop->last ? '#d1fae5' : 'var(--ink-50)' }};padding:1px 6px;border-radius:4px;">v{{ $pv->version }}</span>
                            <span class="ws-file-date">{{ $pv->created_at->format('M d, Y H:i') }}</span>
                            @if(($pv->version == 1 && !$progressDeadlinePassed) || ($pv->version == 2 && $progressResubmitRequested))
                            <button type="button" class="ws-btn-icon ws-btn-icon-danger ws-delete-file" data-id="{{ $pv->id }}" title="Delete">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                            @endif
                        </div>
                        @endforeach
@else
                        <div class="ws-file-empty">No file uploaded yet.</div>
                    @endif
                </div>

                @if(!$progressLocked || $progressResubmitRequested)
                    <input type="file" class="ws-file-input" accept=".pdf">
                    <button type="button" class="ws-btn ws-btn-outline ws-upload-btn ws-upload-btn--full">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        {{ $progressResubmitRequested ? 'Re-Upload Progress Report (v' . $progressNextVersion . ')' : ($progressLatest ? 'Re-Upload' : 'Upload Progress Report') }}
                    </button>
                    <div class="ws-upload-status"></div>
                    <a href="{{ route('download.template', 'progress') }}" target="_blank" class="ws-template-link">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download Template (DOCX)
                    </a>
                @else
                    <div class="ws-upload-locked">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        @if($progressDeadlinePassed) Deadline passed — upload closed @elseif($progressLocked) Already submitted @endif
                    </div>
                @endif
            </div>
        </div>

        @if($extendedProgress)
        @php
            $progress2Latest = $progress2Sub ?? null;
            $progress2Dl = $deadlines['progress2'] ?? [];
            $progress2EffectiveDeadline = $progress2Dl['original'] ?? null;
        @endphp
        <div class="ws-card" style="max-width:480px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--ink-100);">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--brand-50);color:var(--brand-600);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <strong style="font-size:13px;color:var(--ink-800);">Progress Report 2</strong>
                    @if($progress2EffectiveDeadline)
                        <div style="font-size:11px;color:{{ $progress2DeadlinePassed ? 'var(--danger)' : 'var(--ink-400)' }};">
                            Deadline: {{ $progress2EffectiveDeadline->format('M d, Y') }}
                            @if($progress2DeadlinePassed) <span style="font-weight:600;">(Passed)</span> @endif
                        </div>
                    @endif
                </div>
            </div>

<div class="ws-upload-card {{ ($progress2DeadlinePassed || $progress2Locked) && !$progress2ResubmitRequested ? 'ws-upload-card--deadline-passed' : '' }}" data-type="progress2" data-versioned="{{ $progress2ResubmitRequested ? '1' : '0' }}" data-can-delete="{{ $progress2ResubmitRequested || !$progress2DeadlinePassed ? '1' : '0' }}">
                <div class="ws-current-file" data-type="progress2">
                    @php
                        $progress2Versions = $submissions->where('type', 'progress2')->sortBy('version');
                        $progress2NextVersion = $progress2ResubmitRequested ? 2 : (($progress2Versions->max('version') ?? 0) + 1);
                    @endphp
                    @if($progress2Versions->count() > 0)
                        @foreach($progress2Versions as $pv)
                        <div class="ws-file-row" data-id="{{ $pv->id }}" style="margin-bottom:4px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <a href="{{ route('serveFile2', ['type' => 'progress2', 'id' => $project->id, 'submission_id' => $pv->id]) }}" target="_blank" class="ws-file-link">{{ $pv->stored_filename }}</a>
                            <span class="ws-file-version" style="font-size:10px;font-weight:600;color:{{ $loop->last ? 'var(--success)' : 'var(--ink-400)' }};background:{{ $loop->last ? '#d1fae5' : 'var(--ink-50)' }};padding:1px 6px;border-radius:4px;">v{{ $pv->version }}</span>
                            <span class="ws-file-date">{{ $pv->created_at->format('M d, Y H:i') }}</span>
                            @if(($pv->version == 1 && !$progress2DeadlinePassed) || ($pv->version == 2 && $progress2ResubmitRequested))
                            <button type="button" class="ws-btn-icon ws-btn-icon-danger ws-delete-file" data-id="{{ $pv->id }}" title="Delete">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                            @endif
                        </div>
                        @endforeach
@else
                        <div class="ws-file-empty">No file uploaded yet.</div>
                    @endif
                </div>

                @if(!$progress2Locked || $progress2ResubmitRequested)
                    <input type="file" class="ws-file-input" accept=".pdf">
                    <button type="button" class="ws-btn ws-btn-outline ws-upload-btn ws-upload-btn--full">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        {{ $progress2ResubmitRequested ? 'Re-Upload Progress Report 2 (v' . $progress2NextVersion . ')' : ($progress2Latest ? 'Re-Upload' : 'Upload Progress Report 2') }}
                    </button>
                    <div class="ws-upload-status"></div>
                    <a href="{{ route('download.template', 'progress') }}" target="_blank" class="ws-template-link">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download Template (DOCX)
                    </a>
                @else
                    <div class="ws-upload-locked">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        @if($progress2DeadlinePassed) Deadline passed — upload closed @elseif($progress2Locked) Already submitted @endif
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: FINAL REPORT --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-final-report" class="ws-tab-panel" style="display:none;" role="tabpanel">
        @php
            $finalLatest = $submissions->where('type', 'final')->last();
            $readinessLatest = $submissions->where('type', 'readiness')->last();
            $finalDl = $deadlines['final'] ?? [];
            $finalEffectiveDeadline = $finalDl['original'] ?? null;
            $finalDeadlinePassed = $finalEffectiveDeadline ? now()->greaterThan($finalEffectiveDeadline) : true;
            $readinessDl = $deadlines['readiness'] ?? [];
            $readinessEffectiveDeadline = $readinessDl['original'] ?? null;
            $readinessDeadlinePassed = $readinessEffectiveDeadline ? now()->greaterThan($readinessEffectiveDeadline) : true;
        @endphp

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
        <div class="ws-card">
            {{-- Final Report --}}
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--ink-100);">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--brand-50);color:var(--brand-600);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <strong style="font-size:13px;color:var(--ink-800);">Final Report</strong>
                    @if($finalEffectiveDeadline)
                        <div style="font-size:11px;color:{{ $finalDeadlinePassed ? 'var(--danger)' : 'var(--ink-400)' }};">
                            Deadline: {{ $finalEffectiveDeadline->format('M d, Y') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="ws-upload-card {{ ($finalDeadlinePassed || $finalLocked) && !$finalResubmitRequested ? 'ws-upload-card--deadline-passed' : '' }}" data-type="final" data-versioned="{{ $finalResubmitRequested ? '1' : '0' }}" data-can-delete="{{ $finalResubmitRequested || !$finalDeadlinePassed ? '1' : '0' }}">
                <div class="ws-current-file" data-type="final">
                    @php
                        $finalVersions = $submissions->where('type', 'final')->sortBy('version');
                        $finalNextVersion = $finalResubmitRequested ? 2 : (($finalVersions->max('version') ?? 0) + 1);
                    @endphp
                    @if($finalVersions->count() > 0)
                        @foreach($finalVersions as $fv)
                        <div class="ws-file-row" data-id="{{ $fv->id }}" style="margin-bottom:4px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <a href="{{ route('serveFile2', ['type' => 'final', 'id' => $project->id, 'submission_id' => $fv->id]) }}" target="_blank" class="ws-file-link">{{ $fv->stored_filename }}</a>
                            <span class="ws-file-version" style="font-size:10px;font-weight:600;color:{{ $loop->last ? 'var(--success)' : 'var(--ink-400)' }};background:{{ $loop->last ? '#d1fae5' : 'var(--ink-50)' }};padding:1px 6px;border-radius:4px;">v{{ $fv->version }}</span>
                            <span class="ws-file-date">{{ $fv->created_at->format('M d, Y H:i') }}</span>
                            @if(($fv->version == 1 && !$finalDeadlinePassed) || ($fv->version == 2 && $finalResubmitRequested))
                            <button type="button" class="ws-btn-icon ws-btn-icon-danger ws-delete-file" data-id="{{ $fv->id }}" title="Delete">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                            @endif
                        </div>
                        @endforeach
@else
                        <div class="ws-file-empty">No file uploaded yet.</div>
                    @endif
                </div>
                @if(!$finalLocked || $finalResubmitRequested)
                    <input type="file" class="ws-file-input" accept=".pdf">
                    <button type="button" class="ws-btn ws-btn-outline ws-upload-btn ws-upload-btn--full">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        {{ $finalResubmitRequested ? 'Re-Upload Final Report (v' . $finalNextVersion . ')' : ($finalLatest ? 'Re-Upload Final Report' : 'Upload Final Report') }}
                    </button>
                    <div class="ws-upload-status"></div>
                @else
                    <div class="ws-upload-locked">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        @if($finalDeadlinePassed) Deadline passed — upload closed @elseif($finalLocked) Already submitted @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="ws-card">
            {{-- Readiness Report --}}
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--ink-100);">
                <div style="width:36px;height:36px;border-radius:8px;background:#fef3c7;color:#92400e;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <strong style="font-size:13px;color:var(--ink-800);">Readiness Report</strong>
                    @if($readinessEffectiveDeadline)
                        <div style="font-size:11px;color:{{ $readinessDeadlinePassed ? 'var(--danger)' : 'var(--ink-400)' }};">
                            Deadline: {{ $readinessEffectiveDeadline->format('M d, Y') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="ws-upload-card {{ $finalDeadlinePassed || $finalLocked ? 'ws-upload-card--deadline-passed' : '' }}" data-type="readiness" data-can-delete="{{ !$finalDeadlinePassed && !$finalLocked ? '1' : '0' }}">
                <div class="ws-current-file" data-type="readiness">
                    @if($readinessLatest)
                        <div class="ws-file-row" data-id="{{ $readinessLatest->id }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <a href="{{ route('serveFile2', ['type' => 'readiness', 'id' => $project->id]) }}" target="_blank" class="ws-file-link">{{ $readinessLatest->stored_filename }}</a>
                            <span class="ws-file-date">{{ $readinessLatest->created_at->format('M d, Y H:i') }}</span>
                            @if(!$finalDeadlinePassed && !$finalLocked)
                            <button type="button" class="ws-btn-icon ws-btn-icon-danger ws-delete-file" data-id="{{ $readinessLatest->id }}" title="Delete">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                            @endif
                        </div>
                    @else
                        <div class="ws-file-empty">No file uploaded yet.</div>
                    @endif
                </div>
                @if(!$finalDeadlinePassed && !$finalLocked)
                    <input type="file" class="ws-file-input" accept=".pdf">
                    <button type="button" class="ws-btn ws-btn-outline ws-upload-btn ws-upload-btn--full">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        {{ $readinessLatest ? 'Re-Upload' : 'Upload Readiness Report' }}
                    </button>
                    <div class="ws-upload-status"></div>
                    <a href="{{ route('download.template', 'readiness') }}" target="_blank" class="ws-template-link">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download Template (DOCX)
                    </a>
                @else
                    <div class="ws-upload-locked">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        @if($finalDeadlinePassed) Deadline passed — upload closed @elseif($finalLocked) Already submitted @endif
                    </div>
                @endif
            </div>
        </div>
        </div>{{-- /grid --}}
    </div>


</form>
@endsection

@push('scripts')
<style>
/* ═══════════════════════════════════════════════════════════════════════════
   Fluent Design System — Workspace UI
   ═══════════════════════════════════════════════════════════════════════════ */

/* ─── Tab Navigation (Fluent-style) ─── */
.ws-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid var(--ink-100);
    padding: 0;
    margin-bottom: 16px;
    background: var(--sand-50);
    border-radius: 8px 8px 0 0;
    padding: 4px 4px 0 4px;
}
.ws-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-500);
    background: transparent;
    border: 1px solid transparent;
    border-bottom: none;
    border-radius: 6px 6px 0 0;
    cursor: pointer;
    transition: color .15s, background .15s, border-color .15s;
    font-family: inherit;
    position: relative;
    top: 1px;
    white-space: nowrap;
}
.ws-tab:hover {
    color: var(--brand-500);
    background: rgba(141,27,61,.06);
}
.ws-tab.active {
    color: var(--brand-600);
    background: #fff;
    border-color: var(--ink-100);
    font-weight: 600;
    box-shadow: 0 -1px 3px rgba(22,19,26,.04);
}
.ws-tab svg { flex-shrink: 0; }
.ws-tab-panel {
    animation: tabFadeIn .15s ease-out;
    padding: 0;
    margin: 0;
}
@keyframes tabFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ─── Card ─── */
.ws-card {
    background: #fff;
    border: 1px solid var(--ink-100);
    border-radius: 8px;
    padding: 16px 18px;
    box-shadow: var(--fluent-depth-2);
    transition: box-shadow .15s, border-color .15s;
}
.ws-card:hover { box-shadow: var(--fluent-depth-4); border-color: var(--ink-200); }
.ws-section-block {
    margin-bottom: 28px;
    padding: 0 2px;
}
.ws-section-block:last-child { margin-bottom: 0; }

.ws-stepper {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 20px;
    padding: 12px 16px;
    background: #fff;
    border: 1px solid var(--ink-100);
    border-radius: 8px;
}
.ws-stepper-step {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    cursor: pointer;
    border: none;
    background: transparent;
    border-radius: 6px;
    transition: all .15s ease;
    font-family: inherit;
}
.ws-stepper-step:hover { background: var(--ink-50); }
.ws-stepper-step.is-active { background: var(--brand-50); }
.ws-stepper-dot {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--ink-200);
    color: var(--ink-600);
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
    transition: all .15s ease;
}
.ws-stepper-step.is-active .ws-stepper-dot {
    background: var(--brand-500);
    color: #fff;
}
.ws-stepper-num { line-height: 1; }
.ws-stepper-check { display: none; }
.ws-stepper-step.is-completed .ws-stepper-dot {
    background: var(--success, #22c55e);
    color: #fff;
}
.ws-stepper-step.is-completed .ws-stepper-num { display: none; }
.ws-stepper-step.is-completed .ws-stepper-check { display: block; }
.ws-stepper-text {
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-600);
    white-space: nowrap;
}
.ws-stepper-step.is-active .ws-stepper-text {
    font-weight: 600;
    color: var(--brand-700);
}
.ws-stepper-connector {
    flex: 1;
    height: 2px;
    background: var(--ink-200);
    margin: 0 4px;
    min-width: 20px;
}
.ws-section-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--brand-700);
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ws-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--ink-100);
}
.ws-card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink-800);
    display: flex;
    align-items: center;
}
.ws-card-body { padding: 0; }
.ws-card-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ws-card-row-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-700);
    flex: 0 0 55%;
}
.ws-card-row-input { flex: 0 0 auto; }
.ws-card-details {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--ink-50);
}

/* ─── Toggle Switch ─── */
.ws-toggle {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}
.ws-toggle-checkbox { display: none; }
.ws-toggle-slider {
    position: relative;
    width: 68px;
    height: 30px;
    background: var(--ink-200);
    border-radius: 15px;
    transition: background .2s;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 10px;
}
.ws-toggle-checkbox:checked + .ws-toggle-slider {
    background: var(--brand-500);
}
.ws-toggle-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    transition: color .2s;
}
.ws-toggle-no { color: #fff; }
.ws-toggle-yes { color: rgba(255,255,255,.6); }
.ws-toggle-checkbox:checked + .ws-toggle-slider .ws-toggle-no { color: rgba(255,255,255,.6); }
.ws-toggle-checkbox:checked + .ws-toggle-slider .ws-toggle-yes { color: #fff; }

/* ─── Inputs ─── */
.ws-input {
    font-family: inherit;
    font-size: 13px;
    padding: 7px 10px;
    border: 1px solid var(--ink-200);
    border-radius: 6px;
    background: #fff;
    color: var(--ink-800);
    transition: border-color .15s, box-shadow .15s;
    outline: none;
    width: 100%;
}
.ws-input:focus {
    border-color: var(--brand-500);
    box-shadow: 0 0 0 2px rgba(141,27,61,.15);
}
.ws-input-sm { padding: 6px 9px; font-size: 12.5px; }
.ws-input-narrow { max-width: 90px; }
.ws-input-date { max-width: 150px; }
.ws-input-suffix {
    font-size: 11px;
    color: var(--ink-400);
    white-space: nowrap;
}
.ws-textarea {
    resize: vertical;
    min-height: 52px;
}
.ws-file-input { display: none; }

/* ─── Buttons ─── */
.ws-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background .15s, box-shadow .15s, border-color .15s;
    white-space: nowrap;
}
.ws-btn-primary {
    background: var(--brand-500);
    color: #fff;
    border-color: var(--brand-600);
    box-shadow: var(--fluent-depth-2);
}
.ws-btn-primary:hover {
    background: var(--brand-600);
    box-shadow: var(--fluent-depth-4);
}
.ws-btn-outline {
    background: transparent;
    color: var(--ink-600);
    border-color: var(--ink-200);
}
.ws-btn-outline:hover {
    background: var(--ink-50);
    border-color: var(--ink-300);
}
.ws-btn-sm { padding: 5px 12px; font-size: 12px; }
.ws-btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 1px solid var(--ink-100);
    border-radius: 6px;
    background: transparent;
    color: var(--ink-500);
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
    flex-shrink: 0;
}
.ws-btn-icon:hover {
    background: var(--brand-50);
    color: var(--brand-500);
    border-color: var(--brand-200);
}
.ws-btn-icon-danger:hover {
    background: #fbeef1;
    color: var(--danger);
    border-color: #f3d2da;
}

/* ─── Badges & Pills ─── */
.ws-badge {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 999px;
    letter-spacing: .02em;
}
.ws-badge-ink {
    background: var(--ink-50);
    color: var(--ink-500);
    border: 1px solid var(--ink-100);
}
.ws-badge-brand {
    background: var(--brand-50);
    color: var(--brand-600);
    border: 1px solid var(--brand-200);
}
.ws-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 999px;
    border: 1px solid transparent;
}
.ws-pill-brand {
    background: var(--brand-50);
    color: var(--brand-600);
    border-color: var(--brand-200);
}
.ws-pill-success {
    background: #e6f4ea;
    color: var(--success);
    border-color: #a8e6b8;
}
.ws-pill-warning {
    background: #fef7e0;
    color: var(--warning);
    border-color: #fce8b2;
}
.ws-pill-ink {
    background: var(--ink-50);
    color: var(--ink-500);
    border-color: var(--ink-100);
}

/* ─── Table ─── */
.ws-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.ws-table th {
    text-align: left;
    padding: 9px 12px;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-400);
    border-bottom: 1px solid var(--ink-100);
    background: var(--sand-50);
}
.ws-table td {
    padding: 9px 12px;
    border-bottom: 1px solid var(--ink-100);
    color: var(--ink-700);
}
.ws-table tbody tr:hover { background: var(--ink-50); }

/* ─── Outcome / Student dynamic rows ─── */
.outcome-item-row,
.student-item-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    padding: 6px 8px;
    background: var(--ink-50);
    border-radius: 6px;
    border: 1px solid var(--ink-100);
}
.outcome-input-group,
.student-input-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}
.outcome-input-group .ws-input { flex: 1; }

/* ─── Upload Section (Clean Layout) ─── */
.ws-upload-section {
    background: #fff;
    border: 1px solid var(--ink-100);
    border-radius: 10px;
    padding: 24px;
    transition: box-shadow .15s, border-color .15s;
}
.ws-upload-section:hover { box-shadow: var(--fluent-depth-3); border-color: var(--ink-200); }
.ws-upload-section + .ws-upload-section { margin-top: 20px; }
.ws-upload-section-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--ink-100);
}
.ws-upload-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.ws-upload-section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--ink-800);
    margin: 0;
}
.ws-upload-section-deadline {
    font-size: 12px;
    color: var(--ink-400);
    margin-top: 2px;
}
.ws-deadline-passed { color: var(--danger); }
.ws-deadline-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    color: var(--danger);
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 4px;
    padding: 1px 6px;
    margin-left: 4px;
    vertical-align: middle;
}

/* ─── Upload Card ─── */
.ws-upload-card {
    border: 1px solid var(--ink-100);
    border-radius: 10px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: var(--sand-50);
    transition: box-shadow .15s, border-color .15s;
}
.ws-upload-card:hover { box-shadow: var(--fluent-depth-2); border-color: var(--ink-200); }
.ws-upload-card--deadline-passed {
    background: var(--ink-50);
    opacity: 0.85;
    border-color: var(--ink-200);
    pointer-events: none;
}
.ws-upload-card--deadline-passed .ws-current-file { pointer-events: auto; }
.ws-upload-card--deadline-passed .ws-current-file a { pointer-events: auto; }
.ws-upload-card--deadline-passed .ws-delete-file { pointer-events: auto; }

/* ─── Current File Display ─── */
.ws-current-file {
    border: 1px solid var(--ink-200);
    border-radius: 8px;
    padding: 12px 14px;
    min-height: 44px;
    background: #fff;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
}
.ws-file-row {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}
.ws-file-link {
    color: var(--brand-500);
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
}
.ws-file-link:hover { text-decoration: underline; }
.ws-file-date {
    font-size: 10px;
    color: var(--ink-400);
    margin-left: auto;
    white-space: nowrap;
}
.ws-file-empty {
    font-size: 12px;
    color: var(--ink-400);
    text-align: center;
    width: 100%;
}

/* ─── Upload Button ─── */
.ws-upload-btn--full {
    width: 100%;
    justify-content: center;
    padding: 10px 16px;
}
.ws-upload-status {
    font-size: 11px;
    color: var(--ink-400);
    min-height: 16px;
}
.ws-template-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--brand-500);
    text-decoration: none;
    font-weight: 500;
}
.ws-template-link:hover { text-decoration: underline; }
.ws-upload-locked {
    text-align: center;
    padding: 8px 0;
    color: var(--ink-400);
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

/* ─── Final Submit ─── */
.ws-final-submit {
    text-align: center;
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid var(--ink-100);
}
.ws-btn-lg {
    padding: 12px 32px;
    font-size: 14px;
}
.ws-final-submit-note {
    font-size: 12px;
    color: var(--ink-400);
    margin-top: 10px;
    font-style: italic;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {

    @if($progressLocked)
    // ═══════════════════════════════════════════════════════════════════════
    // READONLY MODE — lock progress tab inputs
    // ═══════════════════════════════════════════════════════════════════════
    var progressPanel = document.getElementById('tab-progress-report');
    if (progressPanel) {
        progressPanel.querySelectorAll('input, textarea, select, button').forEach(function(el) {
            // Progress Report 2 has its own independent deadline/lock state
            // (prog_rpt2_deadline), so it must NOT be disabled by the
            // progress-report-1 deadline passing.
            if (el.closest('[data-type="progress2"]')) return;
            el.disabled = true;
            el.readOnly = true;
        });
    }
    @endif

    @if($finalLocked)
    // ═══════════════════════════════════════════════════════════════════════
    // READONLY MODE — lock final report tab inputs
    // ═══════════════════════════════════════════════════════════════════════
    var finalPanel = document.getElementById('tab-final-report');
    if (finalPanel) {
        finalPanel.querySelectorAll('input, textarea, select, button').forEach(function(el) {
            el.disabled = true;
            el.readOnly = true;
        });
    }
    @endif

    @if($outcomesAndStaffLocked)
    // ═══════════════════════════════════════════════════════════════════════
    // READONLY MODE — lock outcomes & students/researchers tab inputs
    // ═══════════════════════════════════════════════════════════════════════
    ['tab-outcomes', 'tab-staff'].forEach(function(tabId) {
        var panel = document.getElementById(tabId);
        if (panel) {
            panel.querySelectorAll('input, textarea, select, button').forEach(function(el) {
                el.disabled = true;
                el.readOnly = true;
            });
        }
    });
    @endif

    // ═══════════════════════════════════════════════════════════════════════
    // TAB SWITCHING
    // ═══════════════════════════════════════════════════════════════════════
    window.switchTab = function(tabId, btn) {
        document.querySelectorAll('.ws-tab-panel').forEach(function(p) {
            p.style.display = 'none';
        });
        document.querySelectorAll('.ws-tab').forEach(function(t) {
            t.classList.remove('active');
        });
        btn.classList.add('active');
        var panel = document.getElementById(tabId);
        if (panel) panel.style.display = 'block';
    };

    // Confirm Final Submit with confirmation dialog
    window.confirmFinalSubmit = function() {
        var hasReadiness = document.querySelector('.ws-upload-card[data-type="readiness"] .ws-file-row') !== null;
        var hasFinal = document.querySelector('.ws-upload-card[data-type="final"] .ws-file-row') !== null;

        if (!hasReadiness && !hasFinal) {
            alert('Please upload at least the Readiness Report or Final Report before confirming.');
            return;
        }

        var msg = 'Are you sure you want to submit the final report?\n\n';
        msg += 'By confirming:\n';
        msg += '• You will NOT be able to replace the Final or Readiness Report files.\n';
        msg += '• The reviewer will be notified to grade the final report.\n\n';
        msg += 'Continue with submission?';

        if (confirm(msg)) {
            document.getElementById('mainProgressForm').submit();
        }
    };

    // Ensure the right tab is active on load
    var firstTab = document.querySelector('.ws-tab.active');
    if (firstTab) firstTab.click();

    // ═══════════════════════════════════════════════════════════════════════
    // TOGGLE: Show/hide detail textareas on Yes/No toggle
    // ═══════════════════════════════════════════════════════════════════════
    document.querySelectorAll('.ws-toggle-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var detailsId = this.getAttribute('data-details-id');
            if (detailsId) {
                var details = document.getElementById(detailsId);
                if (details) {
                    details.style.display = this.checked ? 'block' : 'none';
                }
            }
        });
    });

    // ═══════════════════════════════════════════════════════════════════════
    // SCHOLARLY ARTICLES — Add a record from the type dropdown + DOI input
    // ═══════════════════════════════════════════════════════════════════════
    var scholarlyTypeLabels = {
        'journal_q1':    'Journal articles (Web of Science — Q1)',
        'journal_q2':    'Journal articles (Web of Science — Q2)',
        'journal_q3':    'Journal articles (Web of Science — Q3)',
        'journal_q4':    'Journal articles (Web of Science — Q4)',
        'conference':    'Indexed international conferences',
        'book':          'Published Books',
        'edited_book':   'Edited Books (collection)',
        'book_chapter':  'Book Chapters'
    };

    window.addScholarlyRecord = function() {
        var typeSel = document.getElementById('scholarlyType');
        var idInput = document.getElementById('scholarlyIdentifier');
        if (!typeSel || !idInput) return;
        var type = typeSel.value;
        var identifier = idInput.value.trim();
        if (!identifier) {
            showToast('error', 'Please enter a DOI / identifier.');
            idInput.focus();
            return;
        }
        var list = document.getElementById('scholarlyRecords');
        if (!list) return;

        // Optimistically add the row, then save to DB
        var empty = document.getElementById('scholarlyEmpty');
        if (empty) empty.remove();

        var row = buildOutcomeRow('scholarly-record-row', type, identifier, scholarlyTypeLabels[type] || type);
        list.appendChild(row);
        idInput.value = '';
        idInput.focus();

        saveSingleOutcome(type, identifier, row);
    };

    // ═══════════════════════════════════════════════════════════════════════
    // TAB 3: STUDENT ITEMS — Add / Remove (record-by-record save)
    // ═══════════════════════════════════════════════════════════════════════
    var studentLevelLabels = { 'UG': 'UG', 'masters': 'Masters', 'PhD': 'PhD' };

    window.addStudentRecord = function() {
        var levelSel = document.getElementById('studentLevel');
        var idInput = document.getElementById('studentId');
        var daysInput = document.getElementById('studentDays');
        if (!levelSel || !idInput) return;
        var type = levelSel.value;
        var stdId = idInput.value.trim();
        var days = daysInput ? (parseInt(daysInput.value) || 0) : 0;
        if (!stdId) {
            showToast('error', 'Please enter a student ID.');
            idInput.focus();
            return;
        }
        var list = document.getElementById('studentRecords');
        if (!list) return;

        var empty = document.getElementById('studentEmpty');
        if (empty) empty.remove();

        // Build the record row
        var row = document.createElement('div');
        row.className = 'student-item-row';
        row.setAttribute('data-type', type);
        row.style.cssText = 'padding:10px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;';

        // Add hidden inputs
        row.innerHTML = '<input type="hidden" name="students[new][' + type + ']" value="' + stdId + '">';

        // Main row container
        var inner = document.createElement('div');
        inner.style.cssText = 'display:flex;align-items:center;gap:10px;';

        // Level tag
        var levelTag = document.createElement('span');
        levelTag.className = 'level-tag';
        levelTag.style.cssText = 'font-size:10px;padding:2px 6px;border-radius:4px;background:var(--ink-200);color:var(--ink-600);font-weight:500;flex-shrink:0;';
        levelTag.textContent = type === 'UG' ? 'UG' : (type === 'masters' ? 'MSc' : 'PhD');
        inner.appendChild(levelTag);

        // Student ID (flex:1 like articles DOI)
        var idText = document.createElement('span');
        idText.style.cssText = 'flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;';
        idText.textContent = stdId;
        inner.appendChild(idText);

        // Loading indicator
        var loadingBadge = document.createElement('span');
        loadingBadge.className = 'verified-loading';
        loadingBadge.style.cssText = 'font-size:9px;padding:2px 5px;border-radius:3px;background:var(--ink-200);color:var(--ink-500);font-weight:500;flex-shrink:0;';
        loadingBadge.textContent = '...';
        inner.appendChild(loadingBadge);

        // Days
        var daysSpan = document.createElement('span');
        daysSpan.style.cssText = 'font-size:10px;color:var(--ink-500);flex-shrink:0;white-space:nowrap;';
        daysSpan.textContent = days + 'd';
        inner.appendChild(daysSpan);

        // Delete button
        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'ws-btn-icon ws-btn-icon-danger';
        delBtn.title = 'Delete';
        delBtn.style.cssText = 'flex-shrink:0;width:22px;height:22px;';
        delBtn.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
        delBtn.onclick = function() { row.remove(); };
        inner.appendChild(delBtn);

        row.appendChild(inner);
        list.appendChild(row);

        // Reset inputs
        idInput.value = '';
        if (daysInput) daysInput.value = '';
        idInput.focus();

        // Save to DB
        saveSingleStudent(type, stdId, days, row);
    };

    // Retry verification for a student
    window.retryStudentVerification = function(btn, studentId, stdId) {
        btn.disabled = true;
        btn.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';

        fetch('{{ route("progress.retry-student-verification", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ student_id: studentId, std_id: stdId })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.student) {
                showToast('success', 'Student verified successfully.');
                location.reload();
            } else {
                showToast('error', data.error || 'Student not found in API.');
                btn.disabled = false;
                btn.innerHTML = 'Verify';
            }
        })
        .catch(function(err) {
            showToast('error', 'Network error: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = 'Verify';
        });
    };

    // Verify outcome (article) via API
    window.verifyOutcome = function(btn, outcomeId, doi) {
        btn.disabled = true;
        btn.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';

        fetch('{{ route("progress.verify-outcome", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ outcome_id: outcomeId, doi: doi })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.publication) {
                showToast('success', 'Article verified successfully.');
                location.reload();
            } else {
                showToast('error', data.error || 'DOI not found in CrossRef API.');
                btn.disabled = false;
                btn.innerHTML = 'Verify';
            }
        })
        .catch(function(err) {
            showToast('error', 'Network error: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = 'Verify';
        });
    };

    window.addResearcherRecord = function() {
        var nameInput = document.getElementById('researcherName');
        var catSel = document.getElementById('researcherCategory');
        if (!nameInput) return;
        var name = nameInput.value.trim();
        var category = catSel ? catSel.value : 'RA';
        if (!name) {
            showToast('error', 'Please enter a researcher name.');
            nameInput.focus();
            return;
        }
        var list = document.getElementById('researcherRecords');
        if (!list) return;

        var empty = document.getElementById('researcherEmpty');
        if (empty) empty.remove();

        var row = document.createElement('div');
        row.className = 'researcher-item-row';
        row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;';

        var pill = document.createElement('span');
        pill.className = 'ws-pill ws-pill-brand';
        pill.style.flexShrink = '0';
        pill.textContent = category;
        row.appendChild(pill);

        var text = document.createElement('span');
        text.style.cssText = 'flex:1;font-size:12px;color:var(--ink-700);word-break:break-all;';
        text.textContent = name;
        row.appendChild(text);

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'ws-btn-icon ws-btn-icon-danger';
        delBtn.title = 'Delete';
        delBtn.style.cssText = 'flex-shrink:0;width:26px;height:26px;';
        delBtn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
        delBtn.onclick = function() { row.remove(); };
        row.appendChild(delBtn);

        list.appendChild(row);

        nameInput.value = '';
        nameInput.focus();

        saveSingleResearcher(name, category, row);
    };

    // ═══════════════════════════════════════════════════════════════════════
    // INTELLECTUAL PROPERTY — Add a record from the type dropdown + detail input
    // ═══════════════════════════════════════════════════════════════════════
    var ipTypeLabels = {
        'ip_disclosure':      'Intellectual Property Disclosure',
        'provisional_patent': 'Provisional Patent',
        'patent_granted':     'Patents Granted',
        'open_source_sw':     'Open Source Software',
        'startup':            'Start-Up Created'
    };

    window.addIpRecord = function() {
        var typeSel = document.getElementById('ipType');
        var detailInput = document.getElementById('ipDetail');
        if (!typeSel || !detailInput) return;
        var type = typeSel.value;
        var detail = detailInput.value.trim();
        if (!detail) {
            showToast('error', 'Please enter IP detail.');
            detailInput.focus();
            return;
        }
        var list = document.getElementById('ipRecords');
        if (!list) return;

        var empty = document.getElementById('ipEmpty');
        if (empty) empty.remove();

        var row = buildOutcomeRow('ip-record-row', type, detail, ipTypeLabels[type] || type);
        list.appendChild(row);
        detailInput.value = '';
        detailInput.focus();

        saveSingleOutcome(type, detail, row);
    };

    // Build a record row (used by both scholarly + IP add functions)
    function buildOutcomeRow(rowClass, type, detail, label) {
        var row = document.createElement('div');
        row.className = rowClass;
        row.style.cssText = 'padding:10px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;';

        // Inner flex container
        var inner = document.createElement('div');
        inner.style.cssText = 'display:flex;align-items:center;gap:10px;';

        // Hidden input for detail
        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'outcomes[' + type + '][detail][]';
        hidden.value = detail;
        inner.appendChild(hidden);

        // Generic book icon (will be replaced by publisher badge after API call)
        var icon = document.createElement('div');
        icon.className = 'publisher-icon';
        icon.style.cssText = 'width:22px;height:22px;border-radius:4px;background:var(--ink-200);display:flex;align-items:center;justify-content:center;flex-shrink:0;';
        icon.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--ink-500)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>';
        inner.appendChild(icon);

        // Type tag
        var typeTag = document.createElement('span');
        typeTag.style.cssText = 'font-size:10px;padding:2px 6px;border-radius:4px;background:var(--ink-200);color:var(--ink-600);font-weight:500;flex-shrink:0;';
        typeTag.textContent = label;
        inner.appendChild(typeTag);

        // DOI text
        var text = document.createElement('span');
        text.style.cssText = 'flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);';
        text.textContent = detail;
        inner.appendChild(text);

        // Delete button
        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'ws-btn-icon ws-btn-icon-danger';
        delBtn.title = 'Delete';
        delBtn.style.cssText = 'flex-shrink:0;width:26px;height:26px;';
        delBtn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
        delBtn.addEventListener('click', function() { row.remove(); });
        inner.appendChild(delBtn);

        row.appendChild(inner);

        return row;
    }

    // Save a single outcome record to the DB via AJAX
    function saveSingleOutcome(type, detail, row) {
        fetch('{{ route("progress.save-single-outcome", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type, detail: detail })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                // Tag the row with the DB id so delete can target it
                row.setAttribute('data-id', data.id);
                // Replace the local delete handler with one that hits the DB
                var delBtn = row.querySelector('.ws-btn-icon-danger');
                if (delBtn) {
                    delBtn.onclick = function() { deleteOutcomeRecord(delBtn, data.id); };
                }

                // If publication details were fetched, display them with link
                if (data.publication) {
                    var pub = data.publication;
                    var inner = row.querySelector('div');
                    
                    // Replace generic icon with publisher badge
                    var journal = (pub.journal || '').toLowerCase();
                    var publisherBadge = '';
                    var publisherColor = '555555';
                    var publisherName = pub.journal || 'Publisher';
                    
                    if (journal.indexOf('springer') !== -1) { publisherBadge = 'Springer'; publisherColor = '0d6b3b'; }
                    else if (journal.indexOf('elsevier') !== -1 || journal.indexOf('sciencedirect') !== -1) { publisherBadge = 'Elsevier'; publisherColor = 'ff6c0f'; }
                    else if (journal.indexOf('ieee') !== -1) { publisherBadge = 'IEEE'; publisherColor = '00629b'; }
                    else if (journal.indexOf('wiley') !== -1) { publisherBadge = 'Wiley'; publisherColor = '005a9c'; }
                    else if (journal.indexOf('taylor') !== -1 || journal.indexOf('francis') !== -1) { publisherBadge = 'T%26F'; publisherColor = 'b7282e'; }
                    else if (journal.indexOf('mdpi') !== -1) { publisherBadge = 'MDPI'; publisherColor = '0067a5'; }
                    else if (journal.indexOf('acm') !== -1) { publisherBadge = 'ACM'; publisherColor = '0076a8'; }
                    else if (journal.indexOf('nature') !== -1) { publisherBadge = 'Nature'; publisherColor = '0070c0'; }
                    else if (journal.indexOf('science') !== -1) { publisherBadge = 'Science'; publisherColor = 'cc0000'; }
                    
                    var iconEl = row.querySelector('.publisher-icon');
                    if (iconEl && publisherBadge) {
                        var img = document.createElement('img');
                        img.src = 'https://img.shields.io/badge/' + publisherBadge + '-' + publisherColor + '?style=flat&logo=&logoColor=white';
                        img.alt = publisherName;
                        img.style.cssText = 'height:18px;flex-shrink:0;';
                        img.title = publisherName;
                        iconEl.parentNode.replaceChild(img, iconEl);
                    }
                    
                    // Add verified badge after type tag
                    var typeTag = row.querySelector('span[style*="background:var(--ink-200)"]');
                    if (typeTag) {
                        var verifiedBadge = document.createElement('span');
                        verifiedBadge.style.cssText = 'font-size:10px;padding:2px 6px;border-radius:4px;background:#d1fae5;color:#065f46;font-weight:500;flex-shrink:0;display:inline-flex;align-items:center;gap:3px;';
                        verifiedBadge.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Verified';
                        typeTag.parentNode.insertBefore(verifiedBadge, typeTag.nextSibling);
                    }
                    
                    // Add view link before delete button
                    if (pub.url) {
                        var viewLink = document.createElement('a');
                        viewLink.href = pub.url;
                        viewLink.target = '_blank';
                        viewLink.style.cssText = 'color:var(--brand-500);text-decoration:none;font-size:11px;display:inline-flex;align-items:center;gap:3px;flex-shrink:0;';
                        viewLink.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg> View';
                        var delBtn = row.querySelector('.ws-btn-icon-danger');
                        if (delBtn) delBtn.parentNode.insertBefore(viewLink, delBtn);
                    }
                    
                    // Add publication info below
                    var pubInfo = document.createElement('div');
                    pubInfo.className = 'publication-info';
                    pubInfo.style.cssText = 'margin-top:6px;padding-top:6px;border-top:1px solid var(--ink-100);font-size:11px;color:var(--ink-500);';
                    var pubHtml = '';
                    if (pub.title) pubHtml += '<span style="font-weight:500;color:var(--ink-700);">' + escapeHtml(pub.title.substring(0, 60)) + '</span>';
                    if (pub.journal) pubHtml += ' — ' + escapeHtml(pub.journal);
                    if (pub.year) pubHtml += ' (' + pub.year + ')';
                    pubInfo.innerHTML = pubHtml;
                    row.appendChild(pubInfo);
                } else {
                    // API failed - add Not Verified badge and Verify button
                    var typeTag = row.querySelector('span[style*="background:var(--ink-200)"]');
                    if (typeTag) {
                        var notVerifiedBadge = document.createElement('span');
                        notVerifiedBadge.style.cssText = 'font-size:10px;padding:2px 6px;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:500;flex-shrink:0;';
                        notVerifiedBadge.textContent = 'Not Verified';
                        typeTag.parentNode.insertBefore(notVerifiedBadge, typeTag.nextSibling);
                        
                        var verifyBtn = document.createElement('button');
                        verifyBtn.type = 'button';
                        verifyBtn.className = 'ws-btn ws-btn-outline';
                        verifyBtn.style.cssText = 'font-size:10px;padding:2px 8px;height:auto;flex-shrink:0;';
                        verifyBtn.innerHTML = 'Verify';
                        verifyBtn.onclick = function() { verifyOutcome(verifyBtn, data.id, detail); };
                        typeTag.parentNode.insertBefore(verifyBtn, notVerifiedBadge.nextSibling);
                    }
                }

                showToast('success', data.message || 'Record saved.');
            } else {
                showToast('error', data.error || 'Failed to save record.');
                row.remove();
            }
        })
        .catch(function(err) {
            showToast('error', 'Network error: ' + err.message);
            row.remove();
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // Delete an outcome record (both pre-existing and just-saved) from the DB
    window.deleteOutcomeRecord = function(btn, id) {
        if (!id) { btn.closest('.scholarly-record-row, .ip-record-row')?.remove(); return; }
        if (!confirm('Delete this record?')) return;
        var row = btn.closest('.scholarly-record-row, .ip-record-row');
        fetch('{{ route("progress.delete-outcome", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (row) row.remove();
                showToast('success', 'Record deleted.');
            } else {
                showToast('error', data.error || 'Failed to delete.');
            }
        })
        .catch(function(err) {
            showToast('error', 'Network error: ' + err.message);
        });
    };

    // ═══════════════════════════════════════════════════════════════════════
    // SAVE OUTCOMES (AJAX) — kept for backward compatibility (bulk save)
    // ═══════════════════════════════════════════════════════════════════════
    window.saveOutcomes = function() {
        var outcomes = [];
        function collectFromList(selector) {
            document.querySelectorAll(selector).forEach(function(row) {
                var detailInput = row.querySelector('input[type="hidden"][name$="[detail][]"]');
                if (!detailInput) return;
                var detail = detailInput.value.trim();
                if (!detail) return;
                var m = detailInput.name.match(/outcomes\[([^\]]+)\]\[detail\]\[\]/);
                var type = m ? m[1] : null;
                if (!type) return;
                outcomes.push({ type: type, detail: detail });
            });
        }
        collectFromList('#scholarlyRecords .scholarly-record-row');
        collectFromList('#ipRecords .ip-record-row');

        if (outcomes.length === 0) {
            showToast('error', 'No records to save.');
            return;
        }

        fetch('{{ route("progress.save-outcomes", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ outcomes: outcomes })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('success', 'Outcomes saved successfully.');
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                showToast('error', data.error || 'Failed to save outcomes.');
            }
        })
        .catch(function(err) {
            showToast('error', 'Network error: ' + err.message);
        });
    };

    // ═══════════════════════════════════════════════════════════════════════
    // TAB 3: SAVE SINGLE STUDENT / RESEARCHER / TOGGLE (AJAX, record-by-record)
    // ═══════════════════════════════════════════════════════════════════════
    function saveSingleStudent(type, stdId, days, row) {
        fetch('{{ route("progress.save-single-student", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type, std_id: stdId, days: days })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                row.setAttribute('data-id', data.id);
                var delBtn = row.querySelector('.ws-btn-icon-danger');
                if (delBtn) delBtn.onclick = function() { deleteStudentRecord(delBtn, data.id); };

                // Show student details from API if available
                if (data.student) {
                    var std = data.student;
                    var inner = row.querySelector('div');
                    
                    // Update level tag with API level
                    var levelTag = row.querySelector('.level-tag');
                    if (levelTag && std.std_level) {
                        levelTag.style.cssText = 'font-size:10px;padding:2px 8px;border-radius:4px;background:var(--brand-100);color:var(--brand-700);font-weight:600;flex-shrink:0;';
                        levelTag.textContent = std.std_level;
                    }
                    
                    // Replace loading badge with verified badge
                    var loadingBadge = row.querySelector('.verified-loading');
                    if (loadingBadge) {
                        var verifiedBadge = document.createElement('span');
                        verifiedBadge.style.cssText = 'font-size:10px;padding:2px 6px;border-radius:4px;background:#d1fae5;color:#065f46;font-weight:500;display:inline-flex;align-items:center;gap:3px;flex-shrink:0;';
                        verifiedBadge.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Verified';
                        loadingBadge.parentNode.replaceChild(verifiedBadge, loadingBadge);
                    }
                    
                    // Add status badge after verified badge
                    if (std.student_status && inner) {
                        var statusBadge = document.createElement('span');
                        statusBadge.className = 'ws-pill ' + (std.student_status === 'Active' ? 'ws-pill-success' : 'ws-pill-ink');
                        statusBadge.style.cssText = 'font-size:10px;padding:2px 6px;flex-shrink:0;';
                        statusBadge.textContent = std.student_status;
                        var delBtn = inner.querySelector('.ws-btn-icon-danger');
                        if (delBtn) inner.insertBefore(statusBadge, delBtn);
                    }
                    
                    // Add details row below
                    if (std.full_name || std.college || std.std_program || std.major) {
                        var detailsDiv = document.createElement('div');
                        detailsDiv.style.cssText = 'margin-top:6px;padding-top:6px;border-top:1px solid var(--ink-100);font-size:11px;color:var(--ink-500);';
                        var detailsHtml = '';
                        if (std.full_name) detailsHtml += '<span style="font-weight:500;color:var(--ink-700);">' + escapeHtml(std.full_name) + '</span>';
                        if (std.college) detailsHtml += ' — ' + escapeHtml(std.college);
                        if (std.std_program) detailsHtml += ' (' + escapeHtml(std.std_program) + ')';
                        if (std.major) detailsHtml += ' | ' + escapeHtml(std.major);
                        detailsDiv.innerHTML = detailsHtml;
                        row.appendChild(detailsDiv);
                    }
                } else {
                    // No API response - show Not Verified with retry button
                    var inner = row.querySelector('div');
                    var loadingBadge = row.querySelector('.verified-loading');
                    
                    if (loadingBadge && inner) {
                        var notVerifiedBadge = document.createElement('span');
                        notVerifiedBadge.style.cssText = 'font-size:10px;padding:2px 6px;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:500;display:inline-flex;align-items:center;gap:3px;flex-shrink:0;';
                        notVerifiedBadge.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Not Verified';
                        loadingBadge.parentNode.replaceChild(notVerifiedBadge, loadingBadge);
                        
                        var retryBtn = document.createElement('button');
                        retryBtn.type = 'button';
                        retryBtn.className = 'ws-btn ws-btn-outline';
                        retryBtn.style.cssText = 'font-size:10px;padding:2px 8px;height:auto;flex-shrink:0;';
                        retryBtn.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Retry';
                        retryBtn.onclick = function() { retryStudentVerification(retryBtn, data.id, stdId); };
                        var delBtn = inner.querySelector('.ws-btn-icon-danger');
                        if (delBtn) inner.insertBefore(retryBtn, delBtn);
                    }
                }

                showToast('success', 'Student saved.');
            } else {
                showToast('error', data.error || 'Failed to save student.');
                row.remove();
            }
        })
        .catch(function(err) {
            showToast('error', 'Network error: ' + err.message);
            row.remove();
        });
    }

    function saveSingleResearcher(name, category, row) {
        fetch('{{ route("progress.save-single-researcher", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name, category: category })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                row.setAttribute('data-id', data.id);
                var delBtn = row.querySelector('.ws-btn-icon-danger');
                if (delBtn) delBtn.onclick = function() { deleteResearcherRecord(delBtn, data.id); };
                showToast('success', 'Researcher saved.');
            } else {
                showToast('error', data.error || 'Failed to save researcher.');
                row.remove();
            }
        })
        .catch(function(err) {
            showToast('error', 'Network error: ' + err.message);
            row.remove();
        });
    }

    window.deleteStudentRecord = function(btn, id) {
        if (!id) { btn.closest('.student-item-row')?.remove(); return; }
        if (!confirm('Delete this student?')) return;
        var row = btn.closest('.student-item-row');
        fetch('{{ route("progress.delete-student", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { if (row) row.remove(); showToast('success', 'Student deleted.'); }
            else showToast('error', data.error || 'Failed to delete.');
        })
        .catch(function(err) { showToast('error', 'Network error: ' + err.message); });
    };

    window.deleteResearcherRecord = function(btn, id) {
        if (!id) { btn.closest('.researcher-item-row')?.remove(); return; }
        if (!confirm('Delete this researcher?')) return;
        var row = btn.closest('.researcher-item-row');
        fetch('{{ route("progress.delete-researcher", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { if (row) row.remove(); showToast('success', 'Researcher deleted.'); }
            else showToast('error', data.error || 'Failed to delete.');
        })
        .catch(function(err) { showToast('error', 'Network error: ' + err.message); });
    };

    window.saveToggle = function(type, value) {
        var detail = '';
        if (value === 'Yes') {
            var ta = document.querySelector('textarea[name="' + type + '_detail"]');
            if (ta) detail = ta.value;
        }
        fetch('{{ route("progress.save-toggle", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type, value: value, detail: detail })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) showToast('success', 'Saved.');
            else showToast('error', data.error || 'Failed to save.');
        })
        .catch(function(err) { showToast('error', 'Network error: ' + err.message); });
    };

    // ═══════════════════════════════════════════════════════════════════════
    // TOAST NOTIFICATION
    // (moved to centralized showToast in layout)
    // ═══════════════════════════════════════════════════════════════════════

    // ═══════════════════════════════════════════════════════════════════════
    // TAB 4: REPORT SUBMISSION — AJAX upload on file selection (single file, replace)
    // ═══════════════════════════════════════════════════════════════════════
    var projectId = {{ $project->id }};
    var csrfToken = document.querySelector('input[name="_token"]')?.value || '';

    // Build the "current file" row HTML for a freshly uploaded submission
    function buildFileRow(type, sub, showDelete) {
        var baseUrl = '{{ route("serveFile2", ["type" => "PLACEHOLDER", "id" => $project->id]) }}'.replace('PLACEHOLDER', type);
        var downloadUrl = baseUrl + '&submission_id=' + sub.id;
        var row = document.createElement('div');
        row.className = 'ws-file-row';
        row.setAttribute('data-id', sub.id);
        row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:4px 0;';
        row.innerHTML =
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>' +
            '<a href="' + downloadUrl + '" target="_blank" style="color:var(--brand-500);font-size:12px;font-weight:500;flex-shrink:0;">' +
            sub.stored_filename + '</a>' +
            '<span class="ws-file-version" style="font-size:10px;font-weight:600;color:var(--success);background:#d1fae5;padding:1px 6px;border-radius:4px;flex-shrink:0;">v' + sub.version + '</span>' +
            '<span style="font-size:10px;color:var(--ink-400);flex-shrink:0;">' + sub.created_at + '</span>' +
            (showDelete ? '<button type="button" class="ws-btn-icon ws-btn-icon-danger ws-delete-file" data-id="' + sub.id + '" style="width:20px;height:20px;margin-left:auto;" title="Delete file">' +
            '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>' : '');
        return row;
    }

    // Attach delete handlers for the current file
    function attachDeleteHandlers() {
        document.querySelectorAll('.ws-delete-file').forEach(function(btn) {
            btn.removeEventListener('click', handleDeleteFile);
            btn.addEventListener('click', handleDeleteFile);
        });
    }

    function handleDeleteFile(e) {
        var btn = e.currentTarget;
        var submissionId = btn.getAttribute('data-id');
        var card = btn.closest('.ws-upload-card');
        var type = card ? card.getAttribute('data-type') : null;

        if (!confirm('Delete this file? This action cannot be undone.')) {
            return;
        }

        var formData = new FormData();
        formData.append('submission_id', submissionId);
        formData.append('_token', csrfToken);

        fetch('{{ route("progress.delete-submission", $project->id) }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        })
        .then(function(r) {
            if (!r.ok) return r.json().then(function(err) { throw new Error(err.error || 'Delete failed'); });
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                var fileBox = card ? card.querySelector('.ws-current-file') : null;
                if (fileBox) {
                    fileBox.innerHTML = '<p style="font-size:12px;color:var(--ink-400);margin:8px 0;text-align:center;">No file uploaded yet.</p>';
                }
                if (card) {
                    var uploadBtn = card.querySelector('.ws-upload-btn');
                    if (uploadBtn) {
                        uploadBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Upload';
                    }
                }
                showToast('success', data.message || 'File deleted successfully.');
            } else {
                throw new Error(data.error || 'Delete failed');
            }
        })
        .catch(function(err) {
            showToast('error', err.message);
        });
    }

    // Upload handler
    ['tab-progress-report', 'tab-final-report'].forEach(function(gridId) {
        var uploadGrid = document.getElementById(gridId);
        if (!uploadGrid) return;
        uploadGrid.querySelectorAll('.ws-upload-card').forEach(function(card) {
            var fileInput = card.querySelector('.ws-file-input');
            var uploadBtn = card.querySelector('.ws-upload-btn');
            var statusDiv = card.querySelector('.ws-upload-status');
            var fileBox = card.querySelector('.ws-current-file');
            var type = card.getAttribute('data-type');

            if (uploadBtn && fileInput) {
                uploadBtn.addEventListener('click', function(e) {
                    fileInput.click();
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    var file = this.files[0];
                    if (!file) return;

                    if (file.type !== 'application/pdf') {
                        showToast('error', 'Only PDF files are allowed.');
                        this.value = '';
                        return;
                    }
                    if (file.size > 10 * 1024 * 1024) {
                        showToast('error', 'File must be under 10MB.');
                        this.value = '';
                        return;
                    }

                    // If a file already exists and this is NOT a versioned resubmission, confirm replacement
                    var isVersioned = card.getAttribute('data-versioned') === '1';
                    var existingRow = fileBox ? fileBox.querySelector('.ws-file-row') : null;
                    if (existingRow && !isVersioned) {
                        if (!confirm('A file already exists for this report. Re-uploading will replace it. Continue?')) {
                            this.value = '';
                            return;
                        }
                    }

                    if (uploadBtn) {
                        uploadBtn.disabled = true;
                        uploadBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin" style="margin-right:4px;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="32"/></svg> Uploading…';
                    }
                    if (statusDiv) { statusDiv.textContent = 'Uploading…'; statusDiv.style.color = 'var(--ink-400)'; }

                    var formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', type);
                    formData.append('_token', csrfToken);

                    fetch('{{ route("progress.upload-submission", $project->id) }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: formData
                    })
                    .then(function(r) {
                        if (!r.ok) return r.json().then(function(e) { throw new Error(e.error || 'Upload failed'); });
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            // Re-render the current-file box: for versioned resubmissions replace any
                            // existing row with the same version number; otherwise replace with the new file.
                            if (fileBox) {
                                var isVersioned = card.getAttribute('data-versioned') === '1';
                                var canDelete = card.getAttribute('data-can-delete') === '1';
                                var newRow = buildFileRow(type, data.submission, canDelete);
                                if (isVersioned) {
                                    var existingRow = null;
                                    fileBox.querySelectorAll('.ws-file-row').forEach(function(r) {
                                        var vTag = r.querySelector('.ws-file-version');
                                        if (vTag && vTag.textContent === 'v' + data.submission.version) {
                                            existingRow = r;
                                        }
                                    });
                                    if (existingRow) {
                                        fileBox.replaceChild(newRow, existingRow);
                                    } else {
                                        fileBox.appendChild(newRow);
                                    }
                                } else {
                                    fileBox.innerHTML = '';
                                    fileBox.appendChild(newRow);
                                }
                            }
                            if (statusDiv) { statusDiv.textContent = 'Uploaded successfully!'; statusDiv.style.color = 'var(--success)'; }
                            if (uploadBtn) {
                                uploadBtn.disabled = false;
                                uploadBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Re-Upload';
                            }
                            attachDeleteHandlers();
                            showToast('success', data.message || 'Report uploaded successfully.');
                        } else {
                            throw new Error(data.error || 'Upload failed');
                        }
                    })
                    .catch(function(err) {
                        showToast('error', err.message);
                        if (statusDiv) { statusDiv.textContent = 'Upload failed. Please try again.'; statusDiv.style.color = 'var(--danger)'; }
                        if (uploadBtn) {
                            uploadBtn.disabled = false;
                            uploadBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> ' + (existingRow ? 'Re-Upload' : 'Upload');
                        }
                    });
                });
            }
        });
    });

    // Initialize delete handlers on page load
    attachDeleteHandlers();

});

</script>
<style>
/* Toast notification */
/* (moved to centralized showToast in layout) */

.animate-spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endpush
