@extends('layouts.app')

@section('title', $project->title . ' - RTS')

@section('content')
{{-- Page Head --}}
<div class="page-head">
    <div>
        <h1><i class="fas fa-project-diagram"></i> Project Details</h1>
    </div>
    <div class="page-actions">
        <a href="{{ route('projects.report-card', $project->id) }}" class="btn-secondary" target="_blank">
            <i class="fas fa-print"></i> Project Report Card
        </a>
        <a href="{{ route('projects.available') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
</div>

{{-- Research Call Inactive Banner --}}
@if($project->program && !$project->programIsActive())
<div style="background:linear-gradient(135deg, #fbeef1 0%, #f3d2da 100%); border:1px solid var(--color-brand-200); border-radius:8px; padding:14px 18px; margin-bottom:22px; display:flex; align-items:center; gap:12px;">
    <div style="width:36px; height:36px; border-radius:50%; background:var(--color-brand-500); color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
        <i class="fas fa-lock"></i>
    </div>
    <div>
        <strong style="color:var(--color-brand-800); font-size:14px;">Research Call Inactive</strong>
        <p style="margin:2px 0 0 0; color:var(--color-brand-700); font-size:13px;">
            The research call <strong>{{ $project->program->program_title }}</strong> is no longer active (deadline passed on {{ optional($project->program->extended_deadline ?? $project->program->deadline)->format('M d, Y') ?? 'N/A' }}).
            Projects under this research call cannot be manipulated.
        </p>
    </div>
</div>
@endif

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

@if(!auth()->user()->isReviewer())
{{-- Lifecycle Progress Bar + Inline Actions --}}
<div class="panel" style="margin-bottom: 22px;">
    <div class="panel-head">
        <h2><i class="fas fa-tasks"></i> Project Lifecycle</h2>
    </div>
    <div class="panel-body" style="padding: 12px 16px;">
        @php
            $stages = $project->lifecycle;
            $currentStage = $project->lifecycle_stage;
            $stageKeys = array_keys($stages);
            $totalStages = count($stages);

            // Map lifecycle stage keys to available action types (single-reviewer workflow)
            $availableActions = $project->availableActions(auth()->user());

            // Build a lookup: stage_key => action data for quick matching
            $actionLookup = [];
            foreach ($availableActions as $act) {
                $actionType = $act['action'];
                if ($actionType === 'register') $actionLookup['registered'] = $act;
                elseif (in_array($actionType, ['progress'])) $actionLookup['progress_added'] = $act;
                elseif (in_array($actionType, ['final-report'])) $actionLookup['progress_reviewed'] = $act;
                elseif (in_array($actionType, ['assign'])) $actionLookup['assigned'] = $act;
                elseif (in_array($actionType, ['claim'])) $actionLookup['assigned'] = $act;
                elseif (in_array($actionType, ['progress-grade', 'final-grade'])) $actionLookup['progress_added'] = $act;
                elseif (in_array($actionType, ['report-card'])) $actionLookup['graded'] = $act;
            }
        @endphp

        {{-- Row 1: Lifecycle progress bar (100% inline styles — no CSS class dependencies) --}}
        <div style="display:flex; justify-content:space-between; margin:8px 0 4px; padding:0;">
            @foreach($stages as $key => $stage)
                @php
                    $stageIndex = array_search($key, $stageKeys);
                    $isComplete = $stage['done'];
                    $isCurrent = $stageIndex >= $currentStage && !$isComplete && $stageIndex === $currentStage;
                    $stepDate = $stage['date'] ?? null;
                    $formattedDate = $stepDate ? \Carbon\Carbon::parse($stepDate)->format('M d, Y') : null;
                    $stepUser = $stage['user_name'] ?? null;
                @endphp
                <div style="flex:1; text-align:center; position:relative;">
                    {{-- Connector line to the next node --}}
                    @if(!$loop->last)
                        @php
                            $nextIsComplete = $stages[$stageKeys[$stageIndex+1]]['done'] ?? false;
                            $connColor = $nextIsComplete ? '#8d1b3d' : '#eeedf0';
                        @endphp
                        <div style="position:absolute; top:25px; left:calc(50% + 26px); right:calc(-50% + 26px); height:3px; background:{{ $connColor }}; z-index:1; border-radius:2px;"></div>
                    @endif

                    @if($isCurrent)
                        {{-- Double-ring wrapper for current step --}}
                        <div style="display:inline-block; width:62px; height:62px; border:2px solid #8d1b3d; border-radius:50%; background:#fff; position:relative; z-index:2;">
                            <div style="width:50px; height:50px; border-radius:50%; background:#8d1b3d; color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; margin:4px auto 0;">
                                <i class="fa-solid {{ $stage['icon'] }}" style="font-style:normal !important;"></i>
                            </div>
                        </div>
                    @elseif($isComplete)
                        {{-- Filled circle with stage icon + check badge --}}
                        <div style="position:relative; display:inline-block;">
                            <div style="width:50px; height:50px; border-radius:50%; background:#8d1b3d; color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; margin:0 auto 12px; position:relative; z-index:2;">
                                <i class="fa-solid {{ $stage['icon'] }}" style="font-style:normal !important;"></i>
                            </div>
                            <span style="position:absolute; bottom:8px; right:-4px; width:18px; height:18px; border-radius:50%; background:#1f8a5f; color:#fff; border:2px solid #fff; display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.2); z-index:3;">
                                <i class="fa-solid fa-check" style="font-size:9px; line-height:1; font-style:normal !important;"></i>
                            </span>
                        </div>
                    @else
                        {{-- Gray circle with xmark --}}
                        <div style="width:50px; height:50px; border-radius:50%; background:#b4b0ba; color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; margin:0 auto 12px; position:relative; z-index:2;">
                            <i class="fa-solid fa-xmark" style="font-style:normal !important;"></i>
                        </div>
                    @endif

                    {{-- Label --}}
                    <p style="margin:0; font-size:13px; font-weight:600; line-height:1.3; color:{{ $isComplete || $isCurrent ? '#63102b' : '#8b8592' }};">
                        {{ $stage['label'] }}
                    </p>

                    {{-- Date --}}
                    @if($formattedDate)
                        <span style="display:block; margin-top:3px; font-size:10px; font-weight:500; color:#b8496b; white-space:nowrap;">
                            <i class="far fa-calendar-alt" style="font-style:normal !important;"></i> {{ $formattedDate }}
                        </span>
                    @endif
                    {{-- User --}}
                    @if($stepUser)
                        <span style="display:block; margin-top:1px; font-size:9.5px; font-weight:500; color:#8d1b3d; white-space:nowrap;">
                            <i class="fas fa-user-circle" style="font-style:normal !important;"></i> {{ $stepUser }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

    </div>
</div>
@endif

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
@endphp

{{-- Commitments vs Outcomes --}}
<div style="margin-bottom:22px;">
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-chart-bar"></i> Commitments vs Outcomes</h2>
        </div>
        <div class="panel-body" style="display:flex; flex-direction:column; gap:16px;">
                @if($hasCommitments)
                @php
                    $totalCount = $project->outcomes->count();
                @endphp

                {{-- Summary Stats --}}
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                    <div style="padding:12px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:8px; text-align:center;">
                        <div style="font-size:24px; font-weight:700; color:var(--brand-600);">{{ $totalCount }}</div>
                        <div style="font-size:11px; color:var(--ink-500); text-transform:uppercase;">Total Outcomes</div>
                    </div>
                    <div style="padding:12px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; text-align:center;">
                        <div style="font-size:24px; font-weight:700; color:#059669;">{{ $commitmentsData['verifiedCount'] }}</div>
                        <div style="font-size:11px; color:var(--ink-500); text-transform:uppercase;">Verified</div>
                    </div>
                    <div style="padding:12px; background:#fefce8; border:1px solid #fde68a; border-radius:8px; text-align:center;">
                        <div style="font-size:24px; font-weight:700; color:#d97706;">{{ $commitmentsData['unverifiedCount'] }}</div>
                        <div style="font-size:11px; color:var(--ink-500); text-transform:uppercase;">Unverified</div>
                    </div>
                </div>

                {{-- Publications: Commitments vs Outcomes --}}
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

                {{-- IP & Innovation: Commitments vs Outcomes --}}
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

                {{-- Students: Commitments vs Outcomes --}}
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
</div>

@if(!$isViewer)
{{-- LPI Progress Report Data (shown to LPI / Admin only) --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-chart-line"></i> Progress Report Details</h2>
    </div>
    <div class="panel-body" style="display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:22px;">

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

        {{-- Intellectual Property & Contributions --}}
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

        {{-- Report Submissions --}}
        <div>
            <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:10px;">
                <i class="fas fa-file-pdf" style="margin-right:5px; color:var(--danger);"></i> Report Submissions
            </div>
            @if($submissions->count() > 0)
                <div style="display:flex; flex-direction:column; gap:6px;">
                    @foreach($submissions as $sub)
                    <div style="display:flex; align-items:center; gap:8px; padding:7px 10px; background:var(--sand-50); border:1px solid var(--ink-100); border-radius:6px;">
                        <span class="pill {{ $sub->type === 'final' ? 'success' : ($sub->type === 'readiness' ? 'warning' : 'info') }}" style="flex-shrink:0;">
                            {{ ucfirst($sub->type) }} Report{{ $sub->version > 1 ? ' (v' . $sub->version . ')' : '' }}
                        </span>
                        <a href="{{ route('serveFile2', ['type' => $sub->type, 'id' => $project->id, 'submission_id' => $sub->id]) }}" target="_blank" style="font-size:12px; color:var(--brand-500); font-weight:500; word-break:break-all;">
                            {{ $sub->stored_filename }}
                        </a>
                        <span style="margin-left:auto; font-size:10px; color:var(--ink-400); white-space:nowrap;">{{ $sub->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div style="color:var(--ink-400); font-size:13px;">No report files uploaded.</div>
            @endif
        </div>
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

@endsection
