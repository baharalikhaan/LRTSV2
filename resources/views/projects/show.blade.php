@extends('layouts.app')

@section('title', $project->title . ' - RTS')

@section('content')
{{-- Page Head --}}
<div class="page-head">
    <div>
        <h1><i class="fas fa-project-diagram"></i> Project Details</h1>
    </div>
    <div class="page-actions">
        @php
            $userActions = $project->availableActions(auth()->user());
        @endphp
        <div class="dropdown" style="position:relative;display:inline-block;">
            <button class="btn-secondary" type="button" onclick="toggleProjectMenu(this)" style="background:#8d1b3d;color:#fff;border-color:#8d1b3d;">
                <i class="fas fa-cog"></i> Actions ▾
            </button>
            <div class="action-menu" style="display:none;position:fixed;z-index:10000;background:#fff;border:1px solid #ddd;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);min-width:200px;padding:4px 0;">
                @foreach($userActions as $act)
                    @if($act['action'] === 'progress' || $act['action'] === 'final-report')
                        <a class="dropdown-item" href="{{ route('progress.update', $project->id) }}" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                            <i class="fas fa-chart-line" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                        </a>
                    @elseif($act['action'] === 'progress-grade' || $act['action'] === 'final-grade')
                        <a class="dropdown-item" href="{{ route('projects.grading', $project->id) }}" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                            <i class="fas fa-star" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                        </a>
                    @elseif($act['action'] === 'open-grading')
                        <a class="dropdown-item" href="{{ route('projects.grading', $project->id) }}" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                            <i class="fas fa-clipboard-check" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                        </a>
                    @elseif($act['action'] === 'report-card')
                        <a class="dropdown-item" href="{{ route('projects.report-card', $project->id) }}" target="_blank" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                            <i class="fas fa-file-alt" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                        </a>
                    @elseif($act['action'] === 'claim')
                        <a class="dropdown-item" href="#" onclick="openWorkflowModal({{ $project->id }}, 'accept-proposal')" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                            <i class="fas fa-check-circle" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                        </a>
                    @elseif($act['action'] === 'assign')
                        <a class="dropdown-item" href="#" onclick="openWorkflowModal({{ $project->id }}, 'assign')" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                            <i class="fas fa-user-tag" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                        </a>
                    @elseif($act['action'] === 'unassign-reviewer')
                        <a class="dropdown-item" href="#" onclick="confirmUnassignReviewer({{ $project->id }})" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#dc3545;">
                            <i class="fas fa-user-minus" style="width:16px;text-align:center;font-size:11px;"></i> {{ $act['label'] }}
                        </a>
                    @elseif($act['action'] === 'review-progress-rejection')
                        <a class="dropdown-item" href="#" onclick="openWorkflowModal({{ $project->id }}, 'review-rejection', 'lg', 'report_type=progress')" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                            <i class="fas fa-balance-scale" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                        </a>
                    @elseif($act['action'] === 'review-final-rejection')
                        <a class="dropdown-item" href="#" onclick="openWorkflowModal({{ $project->id }}, 'review-rejection', 'lg', 'report_type=final')" style="padding:8px 14px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                            <i class="fas fa-balance-scale" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        <a href="{{ route('projects.available') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
</div>

{{-- Project Header --}}
<div class="panel" style="margin-bottom:16px;">
    <div class="panel-body" style="display:flex; gap:28px; align-items:flex-start; flex-wrap:wrap;">

        {{-- Left: identity --}}
        <div style="flex:1 1 320px; min-width:280px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px; flex-wrap:wrap;">
                <span style="font-weight:700; color:#8d1b3d; font-family:monospace; font-size:15px;">{{ $project->old_project_id }}</span>
                @php
                    $flowStatus = $project->currentWorkflowStatus();
                    $statusLabel = $flowStatus ? ucfirst(strtolower(str_replace(['_', '-'], ' ', $flowStatus))) : '—';
                @endphp
                <span class="pill info">{{ $statusLabel }}</span>
            </div>
            <h1 style="font-family:'Fraunces', serif; font-size:22px; font-weight:600; color:var(--ink-900); margin:0; line-height:1.35; overflow-wrap:anywhere;">{{ $project->title }}</h1>
        </div>

        {{-- Right: balanced info grid --}}
        <div style="flex:1 1 420px; min-width:300px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px 24px;">
            <div>
                <div style="font-size:10px; font-weight:700; color:var(--ink-400); text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px;">Grant</div>
                <div style="font-size:13px; font-weight:600; color:var(--ink-800);">{{ $project->grant->grant_name ?? $project->program->grant->grant_name ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:10px; font-weight:700; color:var(--ink-400); text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px;">Program</div>
                <div style="font-size:13px; font-weight:600; color:var(--ink-800);">{{ $project->program->program_title ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:10px; font-weight:700; color:var(--ink-400); text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px;">Status</div>
                <div style="font-size:13px; font-weight:600; color:var(--brand-600);">{{ $statusLabel }}</div>
            </div>
            <div>
                <div style="font-size:10px; font-weight:700; color:var(--ink-400); text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px;">LPI</div>
                <div style="font-size:13px; font-weight:600; color:var(--ink-800);">
                    @if($project->lpi)
                        {{ $project->lpi->name }}
                        <div style="font-size:11px; font-weight:400; color:var(--ink-500);">{{ $project->lpi->email }}</div>
                    @else
                        —
                    @endif
                </div>
            </div>
            <div>
                <div style="font-size:10px; font-weight:700; color:var(--ink-400); text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px;">Cycle</div>
                <div style="font-size:13px; font-weight:600; color:var(--ink-800);">{{ $project->program->cycle->year ?? '—' }}</div>
            </div>
        </div>

    </div>
</div>

@php
    $commitment = $project->commitments()->first();
    $hasCommitments = $commitment && (
        $commitment->q1article || $commitment->q2article || $commitment->q3article || $commitment->q4article ||
        $commitment->confArticle || $commitment->books || $commitment->editBooks || $commitment->chapters ||
        $commitment->ip || $commitment->filedPatent || $commitment->openSourceSW || $commitment->startUp ||
        $commitment->ethical || $commitment->master || $commitment->UG || $commitment->Phd || $commitment->crossCollege
    );
    $outcomes = $project->outcomes()->orderBy('created_at', 'desc')->get();

    // LPI progress report data (shown to LPI / Admin, not reviewers)
    $isViewer = auth()->user()->isReviewer() && !auth()->user()->isLPI() && !auth()->user()->isAdmin();

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
    $ipTypes = [
        'ip_disclosure'      => 'Intellectual Property Disclosure',
        'provisional_patent' => 'Provisional Patent',
        'patent_granted'     => 'Patents Granted',
        'open_source_sw'     => 'Open Source Software',
        'startup'            => 'Start-Up Created',
    ];
    $contribTypeKeys = array_merge(array_keys($ipTypes), ['cross_college', 'research_awards']);
    $scholarlyOutcomes = $outcomes->filter(function($o) use ($contribTypeKeys) { return !in_array($o->type, $contribTypeKeys); });
    $ipOutcomes = $outcomes->whereIn('type', array_keys($ipTypes));
    $crossCollegeOutcome = $outcomes->where('type', 'cross_college')->first();
    $researchAwardsOutcome = $outcomes->where('type', 'research_awards')->first();

    $students = $project->students()->orderBy('role')->get();
    $researchers = $project->researchers()->orderBy('created_at')->get();
    $contributions = $project->contributions()->orderBy('created_at')->get();
    $submissions = $project->submissions()->orderBy('created_at', 'desc')->get();

    $progressSubs = $submissions->whereIn('type', ['progress', 'progress2'])->values();
    $readinessSubs = $submissions->where('type', 'readiness')->values();
    $finalSubs = $submissions->where('type', 'final')->values();
    $ethicalSubs = $submissions->where('type', 'ethical')->values();
@endphp

@php
    $progressRows = function ($g) {
        return [
            ['label' => 'Progress Toward Achieving Outcomes', 'word' => $g->achievementsRatingRef->rating ?? null, 'rating' => $g->achievementsRating ?? null, 'comments' => $g->achievementsComments ?? null],
            ['label' => 'Progress in Publications',            'word' => $g->publicationsRatingRef->rating ?? null, 'rating' => $g->publicationsRating ?? null,       'comments' => $g->publicationsComments ?? null],
            ['label' => 'Student Involvement & Capacity Building', 'word' => $g->studentsRatingRef->rating ?? null, 'rating' => $g->studentsRating ?? null,           'comments' => $g->studentsComments ?? null],
            ['label' => 'Budget Utilization',                  'word' => $g->budgetRatingRef->rating ?? null,       'rating' => $g->budgetRating ?? null,               'comments' => $g->budgetComments ?? null],
        ];
    };
@endphp

<div style="display:flex; gap:20px; align-items:flex-start;">

{{-- Left: Main Content (75%) --}}
<div style="flex:0 0 75%; max-width:75%;">

{{-- ═══════════ 1. COMMITMENTS ═══════════ --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-handshake"></i> Commitments</h2>
    </div>
    <div class="panel-body" style="display:flex; flex-direction:column; gap:16px;">
        @if($hasCommitments)
            @php
                $pubItems = $commitmentsData['pubItems'];
                $ipItems = $commitmentsData['ipItems'];
                $studentItems = $commitmentsData['studentItems'];
            @endphp
            <div style="display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px; align-items:start;">

                {{-- Publications --}}
                <div>
                    <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                        <i class="fas fa-file-alt" style="margin-right:5px; color:var(--brand-500);"></i> Publications
                    </div>
                    <div style="overflow-x:auto; border:1px solid var(--ink-100); border-radius:6px;">
                        <table class="fluent-table w-100" style="font-size:11.5px;">
                            <thead>
                                <tr>
                                    <th style="padding:7px 9px;">Item</th>
                                    <th style="padding:7px 9px;" class="text-center">Commit</th>
                                    <th style="padding:7px 9px;" class="text-center">Total</th>
                                    <th style="padding:7px 9px;" class="text-center">Verified</th>
                                    <th style="padding:7px 9px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pubItems as $item)
                                    @if($item['commit'] !== null)
                                    @php
                                        $statusBg = '#d1fae5'; $statusFg = '#065f46'; $statusTxt = 'On Track';
                                        if ($item['verified'] < $item['commit'] && $item['total'] >= $item['commit']) { $statusBg = '#fef3c7'; $statusFg = '#92400e'; $statusTxt = 'In Progress'; }
                                        if ($item['verified'] < $item['commit'] && $item['total'] < $item['commit']) { $statusBg = 'var(--ink-100)'; $statusFg = 'var(--ink-600)'; $statusTxt = 'Behind'; }
                                    @endphp
                                    <tr>
                                        <td style="padding:7px 9px; font-weight:500; color:var(--ink-800);">{{ $item['label'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center">{{ $item['commit'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center">{{ $item['total'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center">{{ $item['verified'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center"><span style="font-size:9.5px; padding:2px 7px; border-radius:3px; background:{{ $statusBg }}; color:{{ $statusFg }}; font-weight:600; white-space:nowrap;">{{ $statusTxt }}</span></td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- IP & Innovation --}}
                <div>
                    <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                        <i class="fas fa-lightbulb" style="margin-right:5px; color:var(--gold-500);"></i> IP &amp; Innovation
                    </div>
                    <div style="overflow-x:auto; border:1px solid var(--ink-100); border-radius:6px;">
                        <table class="fluent-table w-100" style="font-size:11.5px;">
                            <thead>
                                <tr>
                                    <th style="padding:7px 9px;">Item</th>
                                    <th style="padding:7px 9px;" class="text-center">Commit</th>
                                    <th style="padding:7px 9px;" class="text-center">Total</th>
                                    <th style="padding:7px 9px;" class="text-center">Verified</th>
                                    <th style="padding:7px 9px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ipItems as $item)
                                    @if($item['commit'] !== null)
                                    @php
                                        $statusBg = '#d1fae5'; $statusFg = '#065f46'; $statusTxt = 'On Track';
                                        if ($item['verified'] < $item['commit'] && $item['total'] >= $item['commit']) { $statusBg = '#fef3c7'; $statusFg = '#92400e'; $statusTxt = 'In Progress'; }
                                        if ($item['verified'] < $item['commit'] && $item['total'] < $item['commit']) { $statusBg = 'var(--ink-100)'; $statusFg = 'var(--ink-600)'; $statusTxt = 'Behind'; }
                                    @endphp
                                    <tr>
                                        <td style="padding:7px 9px; font-weight:500; color:var(--ink-800);">{{ $item['label'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center">{{ $item['commit'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center">{{ $item['total'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center">{{ $item['verified'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center"><span style="font-size:9.5px; padding:2px 7px; border-radius:3px; background:{{ $statusBg }}; color:{{ $statusFg }}; font-weight:600; white-space:nowrap;">{{ $statusTxt }}</span></td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Students & Training --}}
                <div>
                    <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                        <i class="fas fa-graduation-cap" style="margin-right:5px; color:var(--brand-500);"></i> Students &amp; Training
                    </div>
                    <div style="overflow-x:auto; border:1px solid var(--ink-100); border-radius:6px;">
                        <table class="fluent-table w-100" style="font-size:11.5px;">
                            <thead>
                                <tr>
                                    <th style="padding:7px 9px;">Item</th>
                                    <th style="padding:7px 9px;" class="text-center">Commit</th>
                                    <th style="padding:7px 9px;" class="text-center">Count</th>
                                    <th style="padding:7px 9px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($studentItems as $item)
                                    @if($item['commit'] !== null)
                                    @php $onTrack = $item['count'] >= $item['commit']; @endphp
                                    <tr>
                                        <td style="padding:7px 9px; font-weight:500; color:var(--ink-800);">{{ $item['label'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center">{{ $item['commit'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center">{{ $item['count'] }}</td>
                                        <td style="padding:7px 9px;" class="text-center"><span style="font-size:9.5px; padding:2px 7px; border-radius:3px; background:{{ $onTrack ? '#d1fae5' : 'var(--ink-100)' }}; color:{{ $onTrack ? '#065f46' : 'var(--ink-600)' }}; font-weight:600; white-space:nowrap;">{{ $onTrack ? 'On Track' : 'Behind' }}</span></td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        @else
            <div style="text-align:center; color:var(--ink-400); font-size:13px; padding:16px 0;">
                <i class="fas fa-handshake" style="font-size:24px; margin-bottom:8px; display:block; opacity:.5;"></i>
                No commitments recorded yet for this project.
            </div>
        @endif
    </div>
</div>

{{-- ═══════════ PROJECT FILES ═══════════ --}}
@php
    $allFiles = [];
    if ($project->proposal_filename) {
        $allFiles[] = [
            'type'  => 'Proposal',
            'pill'  => 'danger',
            'name'  => $project->proposal_filename,
            'url'   => route('serveFile2', ['type' => 'proposal', 'id' => $project->id]),
            'date'  => null,
        ];
    }
    foreach ($progressSubs as $sub) {
        $label = $sub->type === 'progress2' ? 'Progress Report 2' : 'Progress Report 1';
        $allFiles[] = [
            'type'  => $label . ($sub->version > 1 ? ' (v' . $sub->version . ')' : ''),
            'pill'  => $sub->type === 'progress2' ? 'warning' : 'info',
            'name'  => $sub->stored_filename,
            'url'   => route('serveFile2', ['type' => $sub->type, 'id' => $project->id, 'submission_id' => $sub->id]),
            'date'  => $sub->created_at ? $sub->created_at->format('M d, Y') : '—',
        ];
    }
    foreach ($finalSubs as $sub) {
        $allFiles[] = [
            'type'  => 'Final Report' . ($sub->version > 1 ? ' (v' . $sub->version . ')' : ''),
            'pill'  => 'success',
            'name'  => $sub->stored_filename,
            'url'   => route('serveFile2', ['type' => $sub->type, 'id' => $project->id, 'submission_id' => $sub->id]),
            'date'  => $sub->created_at ? $sub->created_at->format('M d, Y') : '—',
        ];
    }
    foreach ($readinessSubs as $sub) {
        $allFiles[] = [
            'type'  => 'Readiness Report' . ($sub->version > 1 ? ' (v' . $sub->version . ')' : ''),
            'pill'  => 'info',
            'name'  => $sub->stored_filename,
            'url'   => route('serveFile2', ['type' => $sub->type, 'id' => $project->id, 'submission_id' => $sub->id]),
            'date'  => $sub->created_at ? $sub->created_at->format('M d, Y') : '—',
        ];
    }
    foreach ($ethicalSubs as $sub) {
        $allFiles[] = [
            'type'  => 'Ethical Approval' . ($sub->version > 1 ? ' (v' . $sub->version . ')' : ''),
            'pill'  => 'warning',
            'name'  => $sub->stored_filename,
            'url'   => route('serveFile2', ['type' => $sub->type, 'id' => $project->id, 'submission_id' => $sub->id]),
            'date'  => $sub->created_at ? $sub->created_at->format('M d, Y') : '—',
        ];
    }
@endphp

<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-file-pdf" style="color:var(--danger,#b3261e);"></i> Project Files</h2>
    </div>
    <div class="panel-body p-0">
        @if(count($allFiles) > 0)
            <table class="fluent-table w-100" style="font-size:12px;">
                <thead>
                    <tr>
                        <th style="min-width:60px;">#</th>
                        <th style="min-width:160px;">File Type</th>
                        <th>File Name</th>
                        <th style="min-width:100px;">Uploaded</th>
                        <th class="text-center" style="min-width:80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allFiles as $i => $file)
                    <tr>
                        <td style="font-weight:600; color:var(--ink-500);">{{ $i + 1 }}</td>
                        <td><span class="pill {{ $file['pill'] }}" style="font-size:10px;">{{ $file['type'] }}</span></td>
                        <td style="font-weight:500; word-break:break-all;">{{ $file['name'] }}</td>
                        <td style="color:var(--ink-400);">{{ $file['date'] ?? '—' }}</td>
                        <td class="text-center">
                            <a href="{{ $file['url'] }}" target="_blank"
                               class="btn btn-sm btn-primary" style="font-size:10px; padding:3px 8px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                <i class="fas fa-external-link-alt" style="font-size:9px;"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding:32px; text-align:center; color:var(--ink-400); font-size:13px;">
                <i class="fas fa-folder-open" style="font-size:24px; display:block; margin-bottom:8px; opacity:.4;"></i>
                No files uploaded for this project yet.
            </div>
        @endif
    </div>
</div>

@if(!$isViewer)

{{-- ═══════════ 2. OUTCOMES ═══════════ --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-trophy"></i> Outcomes</h2>
    </div>
    <div class="panel-body" style="display:flex; flex-direction:column; gap:22px;">
        <div style="display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:22px; align-items:start;">

            {{-- Scholarly Articles --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-file-alt" style="margin-right:5px; color:var(--brand-500);"></i> Scholarly Articles
                    <span class="pill info" style="margin-left:6px;">{{ $scholarlyOutcomes->count() }}</span>
                </div>
                @if($scholarlyOutcomes->count() > 0)
                    <div style="overflow-x:auto; border:1px solid var(--ink-100); border-radius:6px;">
                        <table class="fluent-table w-100" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="padding:8px 10px;">Type</th>
                                    <th style="padding:8px 10px;">Publisher</th>
                                    <th style="padding:8px 10px;">Identifier</th>
                                    <th style="padding:8px 10px;">Publication</th>
                                    <th style="padding:8px 10px;" class="text-center">Status</th>
                                    <th style="padding:8px 10px;" class="text-center">Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scholarlyOutcomes as $so)
                                @php
                                    $journal = strtolower($so->publication->journal ?? '');
                                    $publisherBadge = '';
                                    $publisherColor = '555555';
                                    if (str_contains($journal, 'springer')) { $publisherBadge = 'Springer'; $publisherColor = '0d6b3b'; }
                                    elseif (str_contains($journal, 'elsevier') || str_contains($journal, 'sciencedirect')) { $publisherBadge = 'Elsevier'; $publisherColor = 'ff6c0f'; }
                                    elseif (str_contains($journal, 'ieee')) { $publisherBadge = 'IEEE'; $publisherColor = '00629b'; }
                                    elseif (str_contains($journal, 'wiley')) { $publisherBadge = 'Wiley'; $publisherColor = '005a9c'; }
                                    elseif (str_contains($journal, 'nature')) { $publisherBadge = 'Nature'; $publisherColor = '0070c0'; }
                                    elseif (str_contains($journal, 'science')) { $publisherBadge = 'Science'; $publisherColor = 'cc0000'; }
                                @endphp
                                <tr>
                                    <td style="padding:8px 10px;"><span class="pill info">{{ $scholarlyTypes[$so->type] ?? ucfirst(str_replace('_',' ',$so->type)) }}</span></td>
                                    <td style="padding:8px 10px;">
                                        @if($publisherBadge)
                                            <img src="https://img.shields.io/badge/{{ $publisherBadge }}-{{ $publisherColor }}?style=flat&logo=&logoColor=white" alt="" style="height:16px;">
                                        @else
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 10px; font-family:monospace; color:var(--ink-700);">{{ $so->identifier }}</td>
                                    <td style="padding:8px 10px;">
                                        @if($so->publication)
                                            @if($so->publication->publication_title)
                                                <div style="font-weight:500; color:var(--ink-800);">{{ Str::limit($so->publication->publication_title, 80) }}</div>
                                            @endif
                                            @if($so->publication->journal || $so->publication->year)
                                                <div style="font-size:11px; color:var(--ink-500);">
                                                    {{ $so->publication->journal }}@if($so->publication->year) ({{ $so->publication->year }})@endif
                                                </div>
                                            @endif
                                        @else
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 10px;" class="text-center">
                                        @if($so->publication)
                                            <span style="font-size:10px; padding:2px 6px; border-radius:3px; background:#d1fae5; color:#065f46; font-weight:500;">✓ Verified</span>
                                        @else
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 10px;" class="text-center">
                                        @if($so->publication && $so->publication->url)
                                            <a href="{{ $so->publication->url }}" target="_blank" style="font-size:11px; color:var(--brand-500); text-decoration:none;">View</a>
                                        @else
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="color:var(--ink-400); font-size:13px;">No scholarly articles added.</div>
                @endif
            </div>

            {{-- Students --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-graduation-cap" style="margin-right:5px; color:var(--brand-500);"></i> Students / Trainings
                    <span class="pill info" style="margin-left:6px;">{{ $students->count() }}</span>
                </div>
                @if($students->count() > 0)
                    <div style="overflow-x:auto; border:1px solid var(--ink-100); border-radius:6px;">
                        <table class="fluent-table w-100" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="padding:8px 10px;">Type</th>
                                    <th style="padding:8px 10px;">Student ID</th>
                                    <th style="padding:8px 10px;">Name</th>
                                    <th style="padding:8px 10px;">College</th>
                                    <th style="padding:8px 10px;">Program</th>
                                    <th style="padding:8px 10px;">Major</th>
                                    <th style="padding:8px 10px;" class="text-center">Status</th>
                                    <th style="padding:8px 10px;" class="text-center">Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $s)
                                @php $details = $s->details; @endphp
                                <tr>
                                    <td style="padding:8px 10px;"><span class="pill info">{{ $s->type == 'UG' ? 'UG' : ($s->type == 'masters' ? 'MSc' : 'PhD') }}</span></td>
                                    <td style="padding:8px 10px; font-family:monospace; color:var(--ink-700);">{{ $s->std_id }}</td>
                                    <td style="padding:8px 10px;">
                                        @if($details && $details->full_name)
                                            <span style="font-weight:500; color:var(--ink-800);">{{ $details->full_name }}</span>
                                        @else
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 10px;">
                                        @if($details && $details->college)
                                            {{ $details->college }}
                                        @else
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 10px;">
                                        @if($details && $details->std_program)
                                            {{ $details->std_program }}
                                        @else
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 10px;">
                                        @if($details && $details->major)
                                            {{ $details->major }}
                                        @else
                                            <span style="color:var(--ink-400);">&mdash;</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 10px;" class="text-center">
                                        @if($details)
                                            @if($details->student_status)
                                                <span style="font-size:10px; padding:2px 6px; border-radius:3px; background:{{ $details->student_status === 'Active' ? '#d1fae5' : 'var(--ink-100)' }}; color:{{ $details->student_status === 'Active' ? '#065f46' : 'var(--ink-600)' }}; font-weight:500;">{{ $details->student_status }}</span>
                                            @else
                                                <span style="font-size:10px; padding:2px 6px; border-radius:3px; background:#d1fae5; color:#065f46; font-weight:500;">✓</span>
                                            @endif
                                        @else
                                            <span style="font-size:10px; padding:2px 6px; border-radius:3px; background:#fef3c7; color:#92400e; font-weight:500;">Not Verified</span>
                                        @endif
                                    </td>
                                    <td style="padding:8px 10px;" class="text-center">{{ $s->days }}d</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="color:var(--ink-400); font-size:13px;">No students added.</div>
                @endif
            </div>

            {{-- Intellectual Property --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-lightbulb" style="margin-right:5px; color:var(--gold-500);"></i> IP &amp; Innovation
                    <span class="pill warning" style="margin-left:6px;">{{ $ipOutcomes->count() }}</span>
                </div>
                @if($ipOutcomes->count() > 0)
                    <div style="overflow-x:auto; border:1px solid var(--ink-100); border-radius:6px;">
                        <table class="fluent-table w-100" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th style="padding:8px 10px;">Type</th>
                                    <th style="padding:8px 10px;">Identifier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ipOutcomes as $io)
                                <tr>
                                    <td style="padding:8px 10px;"><span class="pill warning">{{ $ipTypes[$io->type] ?? ucfirst(str_replace('_',' ',$io->type)) }}</span></td>
                                    <td style="padding:8px 10px; font-family:monospace; color:var(--ink-700); word-break:break-all;">{{ $io->identifier }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="color:var(--ink-400); font-size:13px;">No intellectual property records added.</div>
                @endif
            </div>

            {{-- Hired Researchers --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-user-tie" style="margin-right:5px; color:var(--brand-500);"></i> Hired Researchers
                </div>
                @if($researchers->count() > 0)
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        @foreach($researchers as $r)
                        <div style="display:flex; align-items:center; gap:8px; padding:7px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                            <span class="pill inactive" style="flex-shrink:0;">{{ $r->category }}</span>
                            <span style="font-size:12px; color:var(--ink-700); word-break:break-all;">{{ $r->name }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div style="color:var(--ink-400); font-size:13px;">No researchers added.</div>
                @endif
            </div>

            {{-- Cross-College & Research Awards --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-building-columns" style="margin-right:5px; color:var(--gold-500);"></i> Cross-College & Awards
                </div>
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <div style="padding:7px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px; font-size:12px; color:var(--ink-700);">
                        <strong style="color:var(--ink-800);">Cross-College Participation:</strong>
                        @if($crossCollegeOutcome)
                            {{ $crossCollegeOutcome->identifier ?: 'Yes' }}
                        @else
                            <span style="color:var(--ink-400);">No</span>
                        @endif
                    </div>
                    <div style="padding:7px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px; font-size:12px; color:var(--ink-700);">
                        <strong style="color:var(--ink-800);">Research Awards:</strong>
                        @if($researchAwardsOutcome)
                            {{ $researchAwardsOutcome->identifier ?: 'Yes' }}
                        @else
                            <span style="color:var(--ink-400);">No</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ 6. GRADING ═══════════ --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-star"></i> Grading</h2>
    </div>
    <div class="panel-body" style="display:flex; flex-direction:column; gap:18px;">
        @if($progressGradings->count() || $progress2Gradings->count() || $finalGradings->count())

            {{-- Progress Report 1 Grading --}}
            @if($progressGradings->count())
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-chart-line" style="margin-right:5px; color:var(--brand-500);"></i> Progress Report 1 Grading
                </div>
                @foreach($progressGradings as $g)
                <div style="padding:10px 12px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:8px; margin-bottom:8px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="font-weight:600; color:var(--ink-800); font-size:12.5px;">{{ $g->user->name ?? 'Reviewer' }}</span>
                        <span class="pill {{ $g->isAccepted == 1 ? 'success' : 'danger' }}" style="flex-shrink:0;">
                            {{ $g->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                        </span>
                        @if($g->created_at)
                        <span style="margin-left:auto; font-size:10px; color:var(--ink-400);">{{ $g->created_at ? $g->created_at->format('d M Y') : '—' }}</span>
                        @endif
                    </div>
                    @foreach($progressRows($g) as $r)
                    <div style="display:flex; justify-content:space-between; align-items:baseline; gap:12px; padding:5px 0; border-bottom:1px solid var(--ink-100);">
                        <span style="font-size:12px; color:var(--ink-600);">{{ $r['label'] }}</span>
                        <span style="font-size:12.5px; font-weight:500; color:var(--brand-600); white-space:nowrap;">
                            {{ $r['word'] ? $r['word'] . ' (' . $r['rating'] . '/5)' : ($r['rating'] !== null ? $r['rating'] . '/5' : '—') }}
                        </span>
                    </div>
                    @if($r['comments'])
                    <div style="font-size:11.5px; color:var(--ink-500); padding:4px 0 2px; white-space:pre-wrap;">{{ $r['comments'] }}</div>
                    @endif
                    @endforeach
                    @if($g->recommendation)
                    <div style="font-size:11.5px; color:var(--ink-600); padding-top:6px; border-top:1px solid var(--ink-100); margin-top:4px;">
                        <span style="font-size:10px; text-transform:uppercase; letter-spacing:.04em; color:var(--ink-400); display:block; margin-bottom:2px;">Recommendation</span>
                        {{ $g->recommendation }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- Progress Report 2 Grading --}}
            @if($progress2Gradings->count())
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-chart-line" style="margin-right:5px; color:var(--gold-500);"></i> Progress Report 2 Grading
                </div>
                @foreach($progress2Gradings as $g)
                <div style="padding:10px 12px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:8px; margin-bottom:8px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="font-weight:600; color:var(--ink-800); font-size:12.5px;">{{ $g->user->name ?? 'Reviewer' }}</span>
                        <span class="pill {{ $g->isAccepted == 1 ? 'success' : 'danger' }}" style="flex-shrink:0;">
                            {{ $g->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                        </span>
                        @if($g->created_at)
                        <span style="margin-left:auto; font-size:10px; color:var(--ink-400);">{{ $g->created_at ? $g->created_at->format('d M Y') : '—' }}</span>
                        @endif
                    </div>
                    @foreach($progressRows($g) as $r)
                    <div style="display:flex; justify-content:space-between; align-items:baseline; gap:12px; padding:5px 0; border-bottom:1px solid var(--ink-100);">
                        <span style="font-size:12px; color:var(--ink-600);">{{ $r['label'] }}</span>
                        <span style="font-size:12.5px; font-weight:500; color:var(--brand-600); white-space:nowrap;">
                            {{ $r['word'] ? $r['word'] . ' (' . $r['rating'] . '/5)' : ($r['rating'] !== null ? $r['rating'] . '/5' : '—') }}
                        </span>
                    </div>
                    @if($r['comments'])
                    <div style="font-size:11.5px; color:var(--ink-500); padding:4px 0 2px; white-space:pre-wrap;">{{ $r['comments'] }}</div>
                    @endif
                    @endforeach
                    @if($g->recommendation)
                    <div style="font-size:11.5px; color:var(--ink-600); padding-top:6px; border-top:1px solid var(--ink-100); margin-top:4px;">
                        <span style="font-size:10px; text-transform:uppercase; letter-spacing:.04em; color:var(--ink-400); display:block; margin-bottom:2px;">Recommendation</span>
                        {{ $g->recommendation }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            {{-- Final Report Grading --}}
            @if($finalGradings->count())
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-flag-checkered" style="margin-right:5px; color:var(--success);"></i> Final Report Grading
                </div>
                @foreach($finalGradings as $g)
                @php
                    $finalSections = [
                        ['label' => 'Achievements against objectives', 'grade' => $g->gradeA ?? null, 'comment' => $g->commentA ?? null],
                        ['label' => 'Publications & IP',              'grade' => $g->gradeB ?? null, 'comment' => $g->commentB ?? null],
                        ['label' => 'Student & Young Researcher Involvement', 'grade' => $g->gradeC ?? null, 'comment' => $g->commentC ?? null],
                        ['label' => 'Project Impact',                 'grade' => $g->gradeD ?? null, 'comment' => $g->commentD ?? null],
                    ];
                @endphp
                <div style="padding:10px 12px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:8px; margin-bottom:8px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="font-weight:600; color:var(--ink-800); font-size:12.5px;">{{ $g->user->name ?? 'Reviewer' }}</span>
                        <span class="pill {{ $g->isAccepted == 1 ? 'success' : 'danger' }}" style="flex-shrink:0;">
                            {{ $g->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                        </span>
                        @if($g->created_at)
                        <span style="margin-left:auto; font-size:10px; color:var(--ink-400);">{{ $g->created_at ? $g->created_at->format('d M Y') : '—' }}</span>
                        @endif
                    </div>
                    @foreach($finalSections as $s)
                    <div style="display:flex; justify-content:space-between; align-items:baseline; gap:12px; padding:5px 0; border-bottom:1px solid var(--ink-100);">
                        <span style="font-size:12px; color:var(--ink-600);">{{ $s['label'] }}</span>
                        <span style="font-size:12.5px; font-weight:500; color:var(--brand-600); white-space:nowrap;">{{ $s['grade'] !== null ? $s['grade'] . '/5' : '—' }}</span>
                    </div>
                    @if($s['comment'])
                    <div style="font-size:11.5px; color:var(--ink-500); padding:4px 0 2px; white-space:pre-wrap;">{{ $s['comment'] }}</div>
                    @endif
                    @endforeach
                    <div style="display:flex; justify-content:space-between; align-items:baseline; gap:12px; padding:6px 0 0; margin-top:4px; border-top:1px solid var(--ink-100);">
                        <span style="font-size:12px; font-weight:600; color:var(--ink-700);">Total Score</span>
                        <span style="font-size:15px; font-weight:700; color:var(--brand-600);">{{ $g->total ?? '—' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        @else
            <div style="color:var(--ink-400); font-size:13px;">No grades submitted for this project yet.</div>
        @endif
    </div>
</div>
@endif

</div>{{-- /left 75% --}}

{{-- Right: Status History Sidebar (25%) --}}
<div style="flex:0 0 25%; max-width:25%; position:sticky; top:20px; align-self:flex-start;">
    @php
        $history = $project->statusHistories()->with('user')->latest()->limit(15)->get();
        $isAdminUser = auth()->user()->isAdmin();
    @endphp
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-clock-rotate-left"></i> {{ $isAdminUser ? 'Status History' : 'Current Status' }}</h2>
        </div>
        <div class="panel-body">
            @if($isAdminUser)
                {{-- Admin: show full status history --}}
                @if($history->count())
                    <div style="display:flex; flex-direction:column; gap:0;">
                        @foreach($history as $h)
                        @php
                            $normalizedStatus = strtolower($h->status);
                            $statusColor = match($normalizedStatus) {
                                'registered' => '#2563a8',
                                'assigned' => '#7c3aed',
                                'claimed' => '#059669',
                                'proposal_rejected' => '#b3261e',
                                'progress_added' => '#2563a8',
                                'progress_reviewed' => '#059669',
                                'progress_rejected' => '#b3261e',
                                'progress2_added' => '#2563a8',
                                'progress2_reviewed' => '#059669',
                                'progress2_rejected' => '#b3261e',
                                'final_added' => '#2563a8',
                                'graded' => '#059669',
                                'final_rejected' => '#b3261e',
                                default => 'var(--ink-500)',
                            };
                            $label = ucfirst(str_replace('_', ' ', $normalizedStatus));
                        @endphp
                        <div style="display:flex; align-items:flex-start; gap:8px; padding:8px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--ink-100);' : '' }}">
                            <div style="width:8px; height:8px; border-radius:50%; background:{{ $statusColor }}; margin-top:4px; flex-shrink:0;"></div>
                            <div style="flex:1; min-width:0;">
                                <span style="font-size:11.5px; font-weight:500; color:var(--ink-700); display:block;">{{ $label }}</span>
                                <span style="font-size:10px; color:var(--ink-400); display:block;">{{ $h->user->name ?? 'System' }} · {{ $h->created_at ? $h->created_at->format('d M Y, g:i A') : '—' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div style="color:var(--ink-400); font-size:12px; text-align:center; padding:12px 0;">No status history yet.</div>
                @endif
            @else
                {{-- LPI / Reviewer: show only current status --}}
                @php
                    $latestStatus = $project->statusHistories()->latest()->first();
                    if ($latestStatus) {
                        $normalizedLatest = strtolower($latestStatus->status);
                        $currentLabel = ucfirst(str_replace('_', ' ', $normalizedLatest));
                        $statusColor = match($normalizedLatest) {
                            'registered' => '#2563a8',
                            'assigned' => '#7c3aed',
                            'claimed' => '#059669',
                            'proposal_rejected' => '#b3261e',
                            'progress_added' => '#2563a8',
                            'progress_reviewed' => '#059669',
                            'progress_rejected' => '#b3261e',
                            'progress2_added' => '#2563a8',
                            'progress2_reviewed' => '#059669',
                            'progress2_rejected' => '#b3261e',
                            'final_added' => '#2563a8',
                            'graded' => '#059669',
                            'final_rejected' => '#b3261e',
                            default => 'var(--ink-500)',
                        };
                    } else {
                        $currentLabel = 'No Status';
                        $statusColor = 'var(--ink-400)';
                    }

                    // Map technical statuses to user-friendly labels
                    $friendlyLabels = [
                        'registered'             => 'Pending Progress',
                        'assigned'               => 'Pending Reviewer Assignment',
                        'claimed'                => 'Pending Proposal Acceptance',
                        'proposal_rejected'      => 'Proposal Rejected',
                        'progress_added'         => 'Pending Progress Review',
                        'progress_reviewed'      => 'Progress Approved',
                        'progress_rejected'      => 'Progress Rejected',
                        'progress2_added'        => 'Pending Progress 2 Review',
                        'progress2_reviewed'     => 'Progress 2 Approved',
                        'progress2_rejected'     => 'Progress 2 Rejected',
                        'final_added'            => 'Pending Final Review',
                        'graded'                 => 'Graded',
                        'final_rejected'         => 'Final Rejected',
                        'reviewer_unassigned'    => 'Reviewer Unassigned',
                    ];
                    $displayLabel = $friendlyLabels[$normalizedLatest] ?? $currentLabel;
                @endphp
                <div style="text-align:center; padding:12px 0;">
                    <div style="width:12px; height:12px; border-radius:50%; background:{{ $statusColor }}; margin:0 auto 10px;"></div>
                    <div style="font-size:13px; font-weight:600; color:var(--ink-800); margin-bottom:4px;">{{ $displayLabel }}</div>
                    @if($latestStatus)
                        <div style="font-size:10px; color:var(--ink-400);">{{ $latestStatus->created_at ? $latestStatus->created_at->format('d M, g:i A') : '—' }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

</div>{{-- /flex container --}}

@push('styles')
<style>
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .4; transform: scale(.7); }
}

.commitment-chip {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 500;
    color: var(--ink-700);
    background: var(--sand-50);
    border: 1px solid var(--ink-100);
    border-radius: var(--fluent-radius-md);
    line-height: 1.3;
}
.commitment-chip strong {
    color: var(--brand-600);
    font-weight: 700;
}
</style>
@endpush

@push('scripts')
<script>
    function toggleProjectMenu(btn) {
        var menu = btn.nextElementSibling;
        var wasOpen = menu.style.display === 'block';
        closeProjectMenus();
        if (!wasOpen) {
            var rect = btn.getBoundingClientRect();
            menu.style.left = (rect.right - 200) + 'px';
            menu.style.top = (rect.bottom + 2) + 'px';
            menu.style.display = 'block';
        }
    }

    function closeProjectMenus() {
        document.querySelectorAll('.action-menu').forEach(function(m) { m.style.display = 'none'; });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            closeProjectMenus();
        }
    });
</script>
@endpush

@endsection
