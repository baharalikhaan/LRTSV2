@extends('layouts.app')

@section('title', 'Admin Dashboard - RTS')

@section('content')
{{-- Stat Cards (Fluent) — Key Metrics --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge gold"><i class="fas fa-layer-group"></i></div>
            <span class="stat-trend up">{{ $totalPrograms ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $totalPrograms ?? 0 }}</div>
        <div class="stat-label">Total Research Calls</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge maroon"><i class="fas fa-calendar-check"></i></div>
            <span class="stat-trend up">{{ $activeProgramsCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $activeProgramsCount ?? 0 }}</div>
        <div class="stat-label">Active Research Calls</div>
        <div class="stat-subs">
            <span class="stat-sub active">Deadlines not passed</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge info"><i class="fas fa-project-diagram"></i></div>
            <span class="stat-trend up">{{ $totalProjects ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $totalProjects ?? 0 }}</div>
        <div class="stat-label">Total Projects</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge success"><i class="fas fa-flag-checkered"></i></div>
            <span class="stat-trend up">{{ $completedProjects ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $completedProjects ?? 0 }}</div>
        <div class="stat-label">Completed Projects</div>
        <div class="stat-subs">
            <span class="stat-sub active">Finally graded</span>
        </div>
    </div>
</div>

{{-- Status breakdown + Active Research Calls row --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px;">
    {{-- Projects by Status --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-chart-pie"></i> Projects by Status</h2>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table">
                <thead>
                    <tr><th>Status</th><th>Count</th></tr>
                </thead>
                <tbody>
                    @forelse($statusCounts ?? [] as $code => $count)
                    @if($count > 0)
                    <tr>
                        <td>
                            @php
                                if ($code === 'no_status') {
                                    $class = 'ink';
                                } elseif (in_array($code, ['Graded', 'graded', 'Completed'])) {
                                    $class = 'success';
                                } elseif (in_array($code, ['Assigned', 'Claimed', 'accepted', 'Accepted', 'Claim-1', 'Claim-2', 'Grade-1', 'Grade-2'])) {
                                    $class = 'review';
                                } elseif (in_array($code, ['registered', 'Registered', 'progress_add', 'progress_added', 'progress_reviewed', 'progress_rejected', 'final_added', 'report'])) {
                                    $class = 'info';
                                } else {
                                    $class = 'info';
                                }
                            @endphp
                            <span class="pill {{ $class }}">{{ $statusLabels[$code] ?? ($code === 'no_status' ? 'No Status' : $code) }}</span>
                        </td>
                        <td><span style="font-weight:600;">{{ $count }}</span></td>
                    </tr>
                    @endif
                    @empty
                    <tr><td colspan="2"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No projects yet</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Active Research Calls --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-layer-group"></i> Active Research Calls</h2>
            <div class="panel-actions">
                <a href="{{ route('programs.index') }}" class="btn-secondary btn-sm">View All</a>
            </div>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table">
                <thead><tr><th>Research Call</th><th>Projects</th><th>Cycle</th></tr></thead>
                <tbody>
                    @forelse($activePrograms ?? [] as $program)
                    <tr>
                        <td><span style="font-weight:500;">{{ $program->program_title }}</span></td>
                        <td><span class="pill info">{{ $program->project_count ?? 0 }}</span></td>
                        <td><span class="pill success">{{ $program->cycle->title ?? 'N/A' }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="3"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No active research calls</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(count($announcements ?? []) > 0)
<div class="panel">
    <div class="panel-head">
        <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
        <div class="panel-actions">
            <a href="{{ route('announcements.index') }}" class="btn-secondary btn-sm">View All</a>
        </div>
    </div>
    <div class="panel-body">
        @foreach($announcements as $announcement)
        <div style="padding:8px 0; border-bottom:1px solid var(--ink-100);">
            <div style="display:flex; align-items:center; gap:8px;">
                <i class="fas fa-bullhorn" style="color:var(--gold-500); font-size:13px;"></i>
                <span style="font-weight:500; font-size:13px;">{{ $announcement->title }}</span>
                <span style="margin-left:auto; font-size:11px; color:var(--ink-400);">{{ $announcement->created_at ? $announcement->created_at->format('d M Y') : '' }}</span>
            </div>
            @if($announcement->body)
            <p style="font-size:12.5px; color:var(--ink-600); margin:4px 0 0 24px;">{{ \Illuminate\Support\Str::limit($announcement->body, 120) }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection