@extends('layouts.app')

@section('title', $program->program_title . ' - RTS')

@php
    $totalProjects = $program->projects->count();
    $isActive = $program->isActive();

    // Status display config: key => [label, pill class, icon]
    $statusConfig = [
        'registered'             => ['Registered',             'ink',       'fa-circle'],
        'Assigned'               => ['Assigned',               'review',    'fa-user-check'],
        'Claimed'                => ['Claimed',                'accepted',  'fa-handshake'],
        'proposal_rejected'      => ['Proposal Rejected',      'danger',    'fa-times-circle'],
        'progress_added'         => ['Progress Added',         'info',      'fa-file-upload'],
        'progress_reviewed'      => ['Progress Reviewed',      'accepted',  'fa-check-circle'],
        'progress_rejected'      => ['Progress Rejected',      'danger',    'fa-times-circle'],
        'final_added'            => ['Final Added',            'info',      'fa-file-upload'],
        'Graded'                 => ['Graded',                 'accepted',  'fa-award'],
        'final_rejected'         => ['Final Rejected',         'danger',    'fa-times-circle'],
        'reviewer_unassigned'    => ['Reviewer Unassigned',    'warning',   'fa-user-slash'],
        'progress2_added'        => ['Progress 2 Added',       'info',      'fa-file-upload'],
        'progress2_reviewed'     => ['Progress 2 Reviewed',    'accepted',  'fa-check-circle'],
        'progress2_rejected'     => ['Progress 2 Rejected',    'danger',    'fa-times-circle'],
    ];
    // Build ordered list from config, only include statuses that have counts
    $orderedStatuses = [];
    foreach ($statusConfig as $key => $cfg) {
        if (isset($statusCounts[$key]) && $statusCounts[$key] > 0) {
            $orderedStatuses[$key] = $cfg + ['count' => $statusCounts[$key]];
        }
    }
    // Add any statuses not in config
    foreach ($statusCounts as $key => $count) {
        if (!isset($orderedStatuses[$key]) && $count > 0) {
            $orderedStatuses[$key] = [ucfirst(str_replace('_', ' ', $key)), 'ink', 'fa-circle', 'count' => $count];
        }
    }
    $statusTotal = array_sum($statusCounts);
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-sync-alt"></i> {{ $program->program_title }}</h1>
        <p>Research call overview, deadlines, and project submissions.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('programs.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@if(!$isActive)
<div style="background:linear-gradient(135deg, #fbeef1 0%, #f3d2da 100%); border:1px solid var(--color-brand-200); border-radius:8px; padding:14px 18px; margin-bottom:22px; display:flex; align-items:center; gap:12px;">
    <div style="width:36px; height:36px; border-radius:50%; background:var(--color-brand-500); color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
        <i class="fas fa-clock"></i>
    </div>
    <div>
        <strong style="color:var(--color-brand-800); font-size:14px;">Research Call Inactive</strong>
        <p style="margin:2px 0 0 0; color:var(--color-brand-700); font-size:13px;">
            This research call's final deadline has passed. Projects are read-only.
        </p>
    </div>
</div>
@endif

{{-- Info Bar --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-body" style="display:flex; align-items:stretch; gap:0; padding:0;">
        {{-- Grant --}}
        <div style="flex:1; padding:16px 20px; border-right:1px solid var(--ink-100,#eceef2);">
            <div style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:var(--ink-400); margin-bottom:6px;">Grant</div>
            @if($program->grant)
                <div style="font-size:14px; font-weight:600; color:var(--ink-800);">{{ $program->grant->grant_code }}</div>
                <div style="font-size:11px; color:var(--ink-500);">{{ $program->grant->grant_name }}</div>
            @else
                <div style="font-size:14px; color:var(--ink-300);">—</div>
            @endif
        </div>
        {{-- Cycle --}}
        <div style="flex:1; padding:16px 20px; border-right:1px solid var(--ink-100,#eceef2);">
            <div style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:var(--ink-400); margin-bottom:6px;">Cycle</div>
            @if($program->cycleConfig)
                <div style="font-size:14px; font-weight:600; color:var(--ink-800);">{{ $program->cycleConfig->year ?? $program->cycleConfig->title ?? 'N/A' }}</div>
            @else
                <div style="font-size:14px; color:var(--ink-300);">—</div>
            @endif
        </div>
        {{-- Projects --}}
        <div style="flex:1; padding:16px 20px; border-right:1px solid var(--ink-100,#eceef2);">
            <div style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:var(--ink-400); margin-bottom:6px;">Projects</div>
            <div style="font-size:20px; font-weight:700; color:var(--ink-800);">{{ $totalProjects }}</div>
        </div>
        {{-- Status --}}
        <div style="flex:1; padding:16px 20px;">
            <div style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:var(--ink-400); margin-bottom:6px;">Status</div>
            @if($isActive)
                <span class="pill success" style="font-size:12px;"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
            @else
                <span class="pill danger" style="font-size:12px;"><i class="fas fa-lock" style="font-size:10px;"></i> Inactive</span>
            @endif
        </div>
    </div>
</div>

{{-- Status Summary + Deadlines side by side --}}
<div style="display:flex; gap:22px; margin-bottom:22px; align-items:flex-start;">

    {{-- Status Summary --}}
    @if($statusTotal > 0)
    <div class="panel" style="flex:1; min-width:0;">
        <div class="panel-head">
            <h2><i class="fas fa-chart-pie"></i> Project Status Summary</h2>
            <div class="panel-actions">
                <span style="font-size:12px; color:var(--color-ink-400);">{{ $statusTotal }} total</span>
            </div>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table w-100">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th class="text-center" style="width:80px;">Count</th>
                        <th class="text-center" style="width:70px;">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderedStatuses as $key => $cfg)
                    <tr>
                        <td>
                            <span class="pill {{ $cfg[1] }}" style="font-size:11px;">
                                <i class="fas {{ $cfg[2] }}" style="font-size:9px; margin-right:4px;"></i>
                                {{ $cfg[0] }}
                            </span>
                        </td>
                        <td class="text-center" style="font-weight:600;">{{ $cfg['count'] }}</td>
                        <td class="text-center" style="color:var(--color-ink-400);">{{ round(($cfg['count'] / $statusTotal) * 100, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Deadlines --}}
    <div class="panel" style="flex:1; min-width:0;">
        <div class="panel-head">
            <h2><i class="fas fa-calendar-alt"></i> Deadlines</h2>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100">
            <thead>
                <tr>
                    <th>Report Type</th>
                    <th>Deadline</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $deadlines = [
                        ['label' => 'Progress Report 1', 'deadline' => $program->prog_rpt_deadline],
                        ['label' => 'Progress Report 2', 'deadline' => $program->prog_rpt2_deadline],
                        ['label' => 'Final Report', 'deadline' => $program->final_rpt_deadline],
                    ];
                @endphp
                @foreach($deadlines as $d)
                @php
                    $past = $d['deadline'] ? now()->greaterThan($d['deadline']) : null;
                @endphp
                <tr>
                    <td style="font-weight:500;">{{ $d['label'] }}</td>
                    <td>{{ $d['deadline'] ? $d['deadline']->format('M d, Y H:i') : '—' }}</td>
                    <td>
                        @if($d['deadline'])
                            @if($past)
                                <span class="pill danger" style="font-size:10px;"><i class="fas fa-lock"></i> Passed</span>
                            @else
                                <span class="pill success" style="font-size:10px;">{{ now()->diffInDays($d['deadline']) }} days left</span>
                            @endif
                        @else
                            <span class="pill ink" style="font-size:10px;">Not set</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div> {{-- End flex container --}}

{{-- Projects --}}
<div class="panel">
    <div class="panel-head">
        <h2><i class="fas fa-diagram-project"></i> Projects</h2>
        <div class="panel-actions">
            <span style="font-size:12px; color:var(--color-ink-400);">{{ $totalProjects }} total</span>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="projectsTable" style="table-layout:auto;">
            <thead>
                <tr>
                    <th style="width:40px; white-space:nowrap;">#</th>
                    <th style="white-space:nowrap;">Project ID</th>
                    <th style="min-width:250px;">Title</th>
                    <th style="white-space:nowrap;">LPI</th>
                    <th style="white-space:nowrap;">Email</th>
                    <th style="white-space:nowrap;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($program->projects as $cp)
                @php
                    $flowStatus = $cp->currentWorkflowStatus();
                    $statusLabel = ucfirst(str_replace('_', ' ', strtolower($flowStatus ?? 'imported')));
                    switch ($flowStatus) {
                        case 'imported': $pillClass = 'warning'; break;
                        case 'registered': $pillClass = 'accepted'; break;
                        case 'Assigned': case 'assigned': $pillClass = 'review'; break;
                        case 'Claimed': case 'claimed': $pillClass = 'accepted'; break;
                        case 'progress_added': case 'progress_reviewed': $pillClass = 'info'; break;
                        case 'progress_rejected': case 'final_rejected': case 'rejected': $pillClass = 'danger'; break;
                        case 'final_added': $pillClass = 'info'; break;
                        case 'Graded': case 'graded': $pillClass = 'accepted'; break;
                        default: $pillClass = 'ink'; break;
                    }
                @endphp
                <tr>
                    <td style="white-space:nowrap;">{{ $loop->iteration }}</td>
                    <td style="white-space:nowrap;"><code>{{ $cp->old_project_id }}</code></td>
                    <td>
                        <a href="{{ route('projects.show', $cp->id) }}" style="font-weight:500; color:var(--color-brand-500); text-decoration:none; white-space:nowrap;">
                            {{ $cp->project_title ?? $cp->title }}
                        </a>
                    </td>
                    <td style="white-space:nowrap;">{{ $cp->lpi->name ?? 'N/A' }}</td>
                    <td style="white-space:nowrap;">{{ $cp->lpi->email ?? 'N/A' }}</td>
                    <td style="white-space:nowrap;"><span class="pill {{ $pillClass }}">{{ $statusLabel }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state py-4">
                            <i class="fas fa-file-alt"></i>
                            <h5>No Projects</h5>
                            <p>No projects have been imported for this research call yet.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
#projectsTable td,
#projectsTable th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 400px;
}
#projectsTable td:nth-child(3) {
    max-width: 350px;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    @if($totalProjects > 0)
    $('#projectsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        pageLength: 25
    });
    @endif
});
</script>
@endpush
