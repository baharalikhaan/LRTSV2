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

{{-- Core Project Info --}}
<div style="display:flex; align-items:center; justify-content:space-between; gap:16px; padding:10px 18px; background:linear-gradient(135deg, #fafbfc 0%, #f5f6f8 100%); border:1px solid var(--ink-100); border-radius:8px; margin-bottom:16px; font-size:13px;">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <span style="font-weight:700; color:#8d1b3d; font-family:monospace;">{{ $project->old_project_id }}</span>
        <span style="color:var(--ink-300);">·</span>
        <span style="font-weight:600; color:var(--ink-800); max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $project->title }}</span>
        <span style="color:var(--ink-300);">·</span>
        <span style="color:var(--ink-600);">{{ $project->grant->grant_name ?? $project->program->grant->grant_name ?? '—' }}</span>
        <span style="color:var(--ink-300);">·</span>
        <span style="color:var(--ink-600);">{{ $project->program->program_title ?? '—' }}</span>
        <span style="color:var(--ink-300);">·</span>
        <span style="color:var(--ink-600);">{{ $project->lpi->email ?? '—' }}</span>
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
    $isViewer = auth()->user()->isReviewer();

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

    $students = $project->students()->orderBy('type')->get();
    $researchers = $project->researchers()->orderBy('created_at')->get();
    $contributions = $project->contributions()->orderBy('created_at')->get();
    $submissions = $project->submissions()->orderBy('created_at', 'desc')->get();

    $progressSubs = $submissions->whereIn('type', ['progress', 'progress2'])->values();
    $finalSubs = $submissions->where('type', 'final')->values();
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

{{-- ═══════════ 1. COMMITMENTS ═══════════ --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-handshake"></i> Commitments</h2>
    </div>
    <div class="panel-body" style="display:flex; flex-direction:column; gap:16px;">
        @if($hasCommitments)
            {{-- Publications --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                    <i class="fas fa-file-alt" style="margin-right:5px; color:var(--brand-500);"></i> Publications
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:8px;">
                    @php $pubItems = $commitmentsData['pubItems']; @endphp
                    @foreach($pubItems as $item)
                        @if($item['commit'] !== null)
                        @php
                            $itemColor = '#059669';
                            if ($item['verified'] < $item['commit'] && $item['total'] >= $item['commit']) $itemColor = '#d97706';
                            if ($item['verified'] < $item['commit'] && $item['total'] < $item['commit']) $itemColor = 'var(--ink-700)';
                        @endphp
                        <div style="padding:8px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                            <div style="font-size:11px; color:var(--ink-500); margin-bottom:4px;">{{ $item['label'] }}</div>
                            <div style="display:flex; align-items:baseline; gap:6px;">
                                <span style="font-size:18px; font-weight:700; color:{{ $itemColor }};">{{ $item['verified'] }}</span>
                                <span style="font-size:12px; color:var(--ink-400);">/</span>
                                <span style="font-size:14px; font-weight:600; color:var(--ink-600);">{{ $item['commit'] }}</span>
                            </div>
                            @if($item['total'] > $item['verified'])
                                <div style="font-size:10px; color:#d97706; margin-top:2px;">{{ $item['total'] - $item['verified'] }} pending</div>
                            @endif
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- IP & Innovation --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                    <i class="fas fa-lightbulb" style="margin-right:5px; color:var(--gold-500);"></i> IP & Innovation
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:8px;">
                    @php $ipItems = $commitmentsData['ipItems']; @endphp
                    @foreach($ipItems as $item)
                        @if($item['commit'] !== null)
                        @php
                            $itemColor = '#059669';
                            if ($item['verified'] < $item['commit'] && $item['total'] >= $item['commit']) $itemColor = '#d97706';
                            if ($item['verified'] < $item['commit'] && $item['total'] < $item['commit']) $itemColor = 'var(--ink-700)';
                        @endphp
                        <div style="padding:8px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                            <div style="font-size:11px; color:var(--ink-500); margin-bottom:4px;">{{ $item['label'] }}</div>
                            <div style="display:flex; align-items:baseline; gap:6px;">
                                <span style="font-size:18px; font-weight:700; color:{{ $itemColor }};">{{ $item['verified'] }}</span>
                                <span style="font-size:12px; color:var(--ink-400);">/</span>
                                <span style="font-size:14px; font-weight:600; color:var(--ink-600);">{{ $item['commit'] }}</span>
                            </div>
                            @if($item['total'] > $item['verified'])
                                <div style="font-size:10px; color:#d97706; margin-top:2px;">{{ $item['total'] - $item['verified'] }} pending</div>
                            @endif
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Students & Training --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                    <i class="fas fa-graduation-cap" style="margin-right:5px; color:var(--brand-500);"></i> Students & Training
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:8px;">
                    @php $studentItems = $commitmentsData['studentItems']; @endphp
                    @foreach($studentItems as $item)
                        @if($item['commit'] !== null)
                        @php $itemColor = $item['count'] >= $item['commit'] ? '#059669' : 'var(--ink-700)'; @endphp
                        <div style="padding:8px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                            <div style="font-size:11px; color:var(--ink-500); margin-bottom:4px;">{{ $item['label'] }}</div>
                            <div style="display:flex; align-items:baseline; gap:6px;">
                                <span style="font-size:18px; font-weight:700; color:{{ $itemColor }};">{{ $item['count'] }}</span>
                                <span style="font-size:12px; color:var(--ink-400);">/</span>
                                <span style="font-size:14px; font-weight:600; color:var(--ink-600);">{{ $item['commit'] }}</span>
                            </div>
                        </div>
                        @endif
                    @endforeach
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

@if(!$isViewer)

{{-- ═══════════ 2. OUTCOMES ═══════════ --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-trophy"></i> Outcomes</h2>
    </div>
    <div class="panel-body" style="display:flex; flex-direction:column; gap:18px;">
        <div style="display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:22px;">

            {{-- Scholarly Articles --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-file-alt" style="margin-right:5px; color:var(--brand-500);"></i> Scholarly Articles
                </div>
                @if($scholarlyOutcomes->count() > 0)
                    <div style="display:flex; flex-direction:column; gap:6px;">
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
                        <div style="padding:8px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                @if($publisherBadge)
                                    <img src="https://img.shields.io/badge/{{ $publisherBadge }}-{{ $publisherColor }}?style=flat&logo=&logoColor=white" alt="" style="height:16px;flex-shrink:0;">
                                @endif
                                <span class="pill info" style="flex-shrink:0;">{{ $scholarlyTypes[$so->type] ?? ucfirst(str_replace('_',' ',$so->type)) }}</span>
                                <span style="flex:1; font-family:monospace; font-size:12px; color:var(--ink-700); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $so->identifier }}</span>
                                @if($so->publication)
                                    <span style="font-size:9px; padding:2px 5px; border-radius:3px; background:#d1fae5; color:#065f46; font-weight:500;">✓ Verified</span>
                                @endif
                                @if($so->publication && $so->publication->url)
                                    <a href="{{ $so->publication->url }}" target="_blank" style="font-size:11px; color:var(--brand-500); text-decoration:none;">View</a>
                                @endif
                            </div>
                            @if($so->publication)
                            <div style="margin-top:5px; padding-top:5px; border-top:1px solid var(--ink-100); font-size:11px; color:var(--ink-500);">
                                @if($so->publication->publication_title)
                                    <span style="font-weight:500; color:var(--ink-700);">{{ Str::limit($so->publication->publication_title, 60) }}</span>
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
                    </div>
                @else
                    <div style="color:var(--ink-400); font-size:13px;">No scholarly articles added.</div>
                @endif
            </div>

            {{-- Students --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-graduation-cap" style="margin-right:5px; color:var(--brand-500);"></i> Students
                </div>
                @if($students->count() > 0)
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        @foreach($students as $s)
                        @php $details = $s->details; @endphp
                        <div style="padding:8px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="pill info" style="flex-shrink:0;">{{ $s->type == 'UG' ? 'UG' : ($s->type == 'masters' ? 'MSc' : 'PhD') }}</span>
                                <span style="flex:1; font-family:monospace; font-size:12px; color:var(--ink-700); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $s->std_id }}</span>
                                @if($details)
                                    <span style="font-size:9px; padding:2px 5px; border-radius:3px; background:#d1fae5; color:#065f46; font-weight:500;">✓</span>
                                    @if($details->student_status)
                                        <span style="font-size:10px; padding:2px 5px; border-radius:3px; background:{{ $details->student_status === 'Active' ? '#d1fae5' : 'var(--ink-100)' }}; color:{{ $details->student_status === 'Active' ? '#065f46' : 'var(--ink-600)' }}; font-weight:500;">{{ $details->student_status }}</span>
                                    @endif
                                @else
                                    <span style="font-size:9px; padding:2px 5px; border-radius:3px; background:#fef3c7; color:#92400e; font-weight:500;">Not Verified</span>
                                @endif
                                <span style="font-size:10px; color:var(--ink-500); white-space:nowrap;">{{ $s->days }}d</span>
                            </div>
                            @if($details)
                            <div style="margin-top:5px; padding-top:5px; border-top:1px solid var(--ink-100); font-size:11px; color:var(--ink-500);">
                                @if($details->full_name)
                                    <span style="font-weight:500; color:var(--ink-700);">{{ $details->full_name }}</span>
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
                    </div>
                @else
                    <div style="color:var(--ink-400); font-size:13px;">No students added.</div>
                @endif
            </div>

            {{-- Intellectual Property --}}
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                    <i class="fas fa-lightbulb" style="margin-right:5px; color:var(--gold-500);"></i> Intellectual Property
                </div>
                @if($ipOutcomes->count() > 0)
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        @foreach($ipOutcomes as $io)
                        <div style="display:flex; align-items:center; gap:8px; padding:7px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                            <span class="pill warning" style="flex-shrink:0;">{{ $ipTypes[$io->type] ?? ucfirst(str_replace('_',' ',$io->type)) }}</span>
                            <span style="font-family:monospace; font-size:12px; color:var(--ink-700); word-break:break-all;">{{ $io->identifier }}</span>
                        </div>
                        @endforeach
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

{{-- ═══════════ 3. PROGRESS REPORTS (FILES) ═══════════ --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-file-pdf"></i> Progress Reports</h2>
    </div>
    <div class="panel-body">
        @if($progressSubs->count() > 0)
            <div style="display:flex; flex-direction:column; gap:6px;">
                @foreach($progressSubs as $sub)
                <div style="display:flex; align-items:center; gap:8px; padding:7px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                    <span class="pill {{ $sub->type === 'progress2' ? 'warning' : 'info' }}" style="flex-shrink:0;">
                        {{ $sub->type === 'progress2' ? 'Progress Report 2' : 'Progress Report 1' }}{{ $sub->version > 1 ? ' (v' . $sub->version . ')' : '' }}
                    </span>
                    <a href="{{ route('serveFile2', ['type' => $sub->type, 'id' => $project->id, 'submission_id' => $sub->id]) }}" target="_blank" style="font-size:12px; color:var(--brand-500); font-weight:500; word-break:break-all;">
                        {{ $sub->stored_filename }}
                    </a>
                    <span style="margin-left:auto; font-size:10px; color:var(--ink-400); white-space:nowrap;">{{ $sub->created_at->format('M d, Y H:i') }}</span>
                </div>
                @endforeach
            </div>
        @else
            <div style="color:var(--ink-400); font-size:13px;">No progress report files uploaded.</div>
        @endif
    </div>
</div>

{{-- ═══════════ 4. FINAL REPORT (FILE) ═══════════ --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-flag-checkered"></i> Final Report</h2>
    </div>
    <div class="panel-body">
        @if($finalSubs->count() > 0)
            <div style="display:flex; flex-direction:column; gap:6px;">
                @foreach($finalSubs as $sub)
                <div style="display:flex; align-items:center; gap:8px; padding:7px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                    <span class="pill success" style="flex-shrink:0;">Final Report{{ $sub->version > 1 ? ' (v' . $sub->version . ')' : '' }}</span>
                    <a href="{{ route('serveFile2', ['type' => $sub->type, 'id' => $project->id, 'submission_id' => $sub->id]) }}" target="_blank" style="font-size:12px; color:var(--brand-500); font-weight:500; word-break:break-all;">
                        {{ $sub->stored_filename }}
                    </a>
                    <span style="margin-left:auto; font-size:10px; color:var(--ink-400); white-space:nowrap;">{{ $sub->created_at->format('M d, Y H:i') }}</span>
                </div>
                @endforeach
            </div>
        @else
            <div style="color:var(--ink-400); font-size:13px;">No final report uploaded.</div>
        @endif
    </div>
</div>

{{-- ═══════════ 5. GRADING ═══════════ --}}
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
                        <span style="margin-left:auto; font-size:10px; color:var(--ink-400);">{{ $g->created_at->format('d M Y') }}</span>
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
                        <span style="margin-left:auto; font-size:10px; color:var(--ink-400);">{{ $g->created_at->format('d M Y') }}</span>
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
                        <span style="margin-left:auto; font-size:10px; color:var(--ink-400);">{{ $g->created_at->format('d M Y') }}</span>
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
