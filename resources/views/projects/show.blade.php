@extends('layouts.app')

@section('title', $project->title . ' - RTS')

@section('content')
{{-- Page Head --}}
<div class="page-head">
    <div>
        <h1><i class="fas fa-project-diagram"></i> Project Details</h1>
    </div>
    <div class="page-actions">
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
            $actionStageMap = [
                'submission' => null,
                'registered' => ['progress'],
                'progress'   => ['assign'],
                'assigned'   => ['claim'],
                'claimed'    => ['grade-proposal'],
                'graded'     => ['report-card'],
            ];

            $availableActions = $project->availableActions(auth()->user());

            // Build a lookup: stage_key => action data for quick matching
            $actionLookup = [];
            foreach ($availableActions as $act) {
                $actionType = $act['action'];
                if ($actionType === 'register') $actionLookup['submission'] = $act;
                elseif (in_array($actionType, ['progress'])) $actionLookup['registered'] = $act;
                elseif (in_array($actionType, ['assign'])) $actionLookup['progress'] = $act;
                elseif (in_array($actionType, ['claim'])) $actionLookup['assigned'] = $act;
                elseif (in_array($actionType, ['grade-proposal'])) $actionLookup['claimed'] = $act;
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
    $hasOutcomes = $outcomes->count() > 0;
    $outcomesByType = $outcomes->count() > 0 ? $outcomes->groupBy('type')->map(function($items, $type) {
        return ['type' => $type, 'count' => $items->count()];
    })->sortBy('type')->values() : collect();
@endphp

{{-- 3-Column Row: Core Information | Commitments | Outcomes --}}
<div style="display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1fr); gap:14px; margin-bottom:22px; align-items:start;">

    {{-- 1: Core Information --}}
    <div class="panel" style="margin-bottom:0;">
        <div class="panel-head">
            <h2><i class="fas fa-info-circle"></i> Core Information</h2>
        </div>
        <div class="panel-body" style="display:grid; gap:8px;">
            <div class="detail-row">
                <span class="detail-label">Project ID</span>
                <span class="detail-value"><code>{{ $project->old_project_id }}</code></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Title</span>
                <span class="detail-value" style="font-weight:600;">{{ $project->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Research Call</span>
                <span class="detail-value">{{ $project->program->program_title ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Grant</span>
                <span class="detail-value">
                    @if($project->program && $project->program->grant)
                        <span class="pill info">{{ $project->program->grant->grant_code }}</span>
                        {{ $project->program->grant->grant_title }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">PI Name</span>
                <span class="detail-value">{{ $project->lpi->name ?? $project->author ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">PI Email</span>
                <span class="detail-value">{{ $project->lpi->email ?? $project->email ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- 2: Commitments --}}
    <div class="panel" style="margin-bottom:0; overflow:hidden;">
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
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        @php
                            $pubItems = [
                                ['label' => 'Q1', 'value' => $commitment->q1article],
                                ['label' => 'Q2', 'value' => $commitment->q2article],
                                ['label' => 'Q3', 'value' => $commitment->q3article],
                                ['label' => 'Q4', 'value' => $commitment->q4article],
                                ['label' => 'Conf', 'value' => $commitment->confArticle],
                                ['label' => 'Books', 'value' => $commitment->books],
                                ['label' => 'Ed. Books', 'value' => $commitment->editBooks],
                                ['label' => 'Chapters', 'value' => $commitment->chapters],
                            ];
                        @endphp
                        @foreach($pubItems as $item)
                            @if($item['value'] !== null)
                            <span class="commitment-chip">{{ $item['label'] }}: <strong>{{ $item['value'] }}</strong></span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Intellectual Property & Innovation --}}
                <div>
                    <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                        <i class="fas fa-lightbulb" style="margin-right:5px; color:var(--gold-500);"></i> IP & Innovation
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        @php
                            $ipItems = [
                                ['label' => 'IP', 'value' => $commitment->ip],
                                ['label' => 'Patents', 'value' => $commitment->filedPatent],
                                ['label' => 'OSS', 'value' => $commitment->openSourceSW],
                                ['label' => 'Start-up', 'value' => $commitment->startUp === null ? null : ($commitment->startUp ? 'Yes' : 'No')],
                                ['label' => 'Ethical', 'value' => $commitment->ethical === null ? null : ($commitment->ethical ? 'Yes' : 'No')],
                            ];
                        @endphp
                        @foreach($ipItems as $item)
                            @if($item['value'] !== null)
                            <span class="commitment-chip">{{ $item['label'] }}: <strong>{{ $item['value'] }}</strong></span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Students & Training --}}
                <div>
                    <div style="font-size:11px; font-weight:700; color:var(--ink-500); text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px;">
                        <i class="fas fa-graduation-cap" style="margin-right:5px; color:var(--brand-500);"></i> Students & Training
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        @php
                            $studentItems = [
                                ['label' => 'Master', 'value' => $commitment->master],
                                ['label' => 'Undergrad', 'value' => $commitment->UG],
                                ['label' => 'PhD', 'value' => $commitment->Phd],
                                ['label' => 'Cross-College', 'value' => $commitment->crossCollege === null ? null : ($commitment->crossCollege ? 'Yes' : 'No')],
                            ];
                        @endphp
                        @foreach($studentItems as $item)
                            @if($item['value'] !== null)
                            <span class="commitment-chip">{{ $item['label'] }}: <strong>{{ $item['value'] }}</strong></span>
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

    {{-- 3: Outcomes --}}
    <div>
        <div class="panel" style="margin-bottom:0;">
            <div class="panel-head">
                <h2><i class="fas fa-clipboard-list"></i> Outcomes</h2>
            </div>
            <div class="panel-body" style="padding:0;">
                @if($hasOutcomes)
                <table style="width:100%; border-collapse:collapse; font-size:13px; border:1px solid var(--ink-100); border-radius:6px;">
                    <thead>
                        <tr style="background:var(--sand-50);">
                            <th style="text-align:left; padding:10px 16px; font-size:11px; font-weight:700; color:var(--ink-700); text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--ink-200);">Type</th>
                            <th style="text-align:center; padding:10px 16px; font-size:11px; font-weight:700; color:var(--ink-700); text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--ink-200); width:70px;">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($outcomesByType as $group)
                        <tr>
                            <td style="padding:7px 16px; color:var(--ink-700); border-bottom:1px solid var(--ink-50); font-weight:500;">
                                <span class="pill {{ $group['type'] === 'publication' ? 'info' : ($group['type'] === 'patent' ? 'review' : 'ink') }}">
                                    {{ ucfirst($group['type']) }}
                                </span>
                            </td>
                            <td style="padding:7px 16px; border-bottom:1px solid var(--ink-50); text-align:center; font-weight:600; font-size:15px; color:var(--brand-500);">
                                {{ $group['count'] }}
                            </td>
                        </tr>
                        @endforeach
                        {{-- Total row --}}
                        <tr style="background:var(--sand-50);">
                            <td style="padding:8px 16px; font-weight:700; color:var(--ink-800); border-bottom:1px solid var(--ink-200); font-size:12px; text-transform:uppercase; letter-spacing:.04em;">Total</td>
                            <td style="padding:8px 16px; text-align:center; font-weight:700; font-size:15px; color:var(--brand-600); border-bottom:1px solid var(--ink-200);">{{ $outcomes->count() }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <div style="padding:24px 16px; text-align:center; color:var(--ink-400); font-size:13px;">
                    <i class="fas fa-clipboard-list"></i> No outcomes recorded yet for this project.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

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
