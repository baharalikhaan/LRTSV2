@extends('layouts.app')

@section('title', 'LPI Dashboard - RTS')

@section('content')
<div class="dash-layout">
    <div class="dash-main">

        {{-- Primary KPI cards --}}
        <div class="stat-grid">
            <a class="stat-card" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge info"><i class="fas fa-folder-open"></i></div>
                </div>
                <div class="stat-value">{{ $allProjectsCount ?? 0 }}</div>
                <div class="stat-label">My Projects</div>
                <div class="stat-subs">
                    <span class="stat-sub active">Total assigned to you</span>
                </div>
            </a>

            <a class="stat-card {{ ($unregisteredCount ?? 0) > 0 ? 'stat-card--alert' : '' }}" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge gold"><i class="fas fa-user-plus"></i></div>
                </div>
                <div class="stat-value">{{ $unregisteredCount ?? 0 }}</div>
                <div class="stat-label">Un-Registered</div>
                <div class="stat-subs">
                    <span class="stat-sub">Pending registration</span>
                </div>
            </a>

            <a class="stat-card {{ ($reportUploadPendingCount ?? 0) > 0 ? 'stat-card--alert' : '' }}" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge maroon"><i class="fas fa-clock"></i></div>
                </div>
                <div class="stat-value">{{ $reportUploadPendingCount ?? 0 }}</div>
                <div class="stat-label">Registered</div>
                <div class="stat-subs">
                    <span class="stat-sub">Pending progress update</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge info"><i class="fas fa-check-double"></i></div>
                </div>
                <div class="stat-value">{{ $progressDoneCount ?? 0 }}</div>
                <div class="stat-label">Completed</div>
                <div class="stat-subs">
                    <span class="stat-sub active">Progress updated</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge success"><i class="fas fa-flag-checkered"></i></div>
                </div>
                <div class="stat-value">{{ $gradedCount ?? 0 }}</div>
                <div class="stat-label">Graded</div>
                <div class="stat-subs">
                    <span class="stat-sub active">Review completed</span>
                </div>
            </a>
        </div>

        {{-- Deadline alert --}}
        @if(isset($nearestDeadline) && $nearestDeadline)
        <div class="deadline-alert">
            <div class="deadline-alert-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="deadline-alert-body">
                <span class="deadline-alert-title">Upcoming Deadline</span>
                <span class="deadline-alert-text">
                    <strong>{{ $nearestDeadline->program_title }}</strong> — Final report due
                    <strong>{{ $nearestDeadline->final_rpt_deadline->format('d M Y') }}</strong>
                    ({{ $nearestDeadline->final_rpt_deadline->diffForHumans() }})
                </span>
            </div>
            <a href="{{ route('projects.available') }}" class="deadline-alert-link">View Projects</a>
        </div>
        @endif

        {{-- Status donut + Active Research Calls --}}
        <div class="dash-row">
            <div class="panel">
                <div class="panel-head">
                    <h2><i class="fas fa-chart-pie"></i> My Projects by Status</h2>
                </div>
                <div class="panel-body">
                    <div class="status-chart-wrap">
                        <canvas id="statusDonut" height="220"></canvas>
                    </div>
                    <table class="fluent-table status-legend-table">
                        <thead><tr><th>Status</th><th class="text-end">Count</th></tr></thead>
                        <tbody>
                            @php
                                $colorMap = [
                                    'no_status' => '#94a3b8',
                                    'Graded' => '#16a34a', 'graded' => '#16a34a', 'Completed' => '#16a34a',
                                    'Assigned' => '#d97706', 'Claimed' => '#d97706', 'accepted' => '#d97706',
                                    'Accepted' => '#d97706', 'Claim-1' => '#d97706', 'Claim-2' => '#d97706',
                                    'Grade-1' => '#d97706', 'Grade-2' => '#d97706',
                                    'progress_added' => '#2563eb', 'progress_reviewed' => '#2563eb',
                                    'progress2_added' => '#7c3aed', 'progress2_reviewed' => '#7c3aed',
                                    'final_added' => '#0891b2',
                                ];
                            @endphp
                            @forelse($statusCounts ?? [] as $code => $count)
                            @if($count > 0)
                            <tr>
                                <td>
                                    <span class="status-dot" style="background:{{ $colorMap[$code] ?? '#2563eb' }};"></span>
                                    {{ $statusLabels[$code] ?? ($code === 'no_status' ? 'No Status' : $code) }}
                                </td>
                                <td class="text-end"><span style="font-weight:600;">{{ $count }}</span></td>
                            </tr>
                            @endif
                            @empty
                            <tr><td colspan="2"><div class="empty-state py-3"><i class="fas fa-inbox"></i><p class="mb-0">No projects yet</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dash-col">
                <div class="panel">
                    <div class="panel-head">
                        <h2><i class="fas fa-diagram-project"></i> My Projects</h2>
                        <div class="panel-actions">
                            <a href="{{ route('projects.available') }}" class="btn-secondary btn-sm">View All</a>
                        </div>
                    </div>
                    <div class="panel-body p-0">
                        <table class="fluent-table">
                            <thead><tr><th>Project</th><th>Research Call</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($myProjects->take(6) as $project)
                                <tr>
                                    <td><span style="font-weight:500;">{{ $project->title }}</span></td>
                                    <td>{{ $project->program->program_title ?? '—' }}</td>
                                    <td>
                                        @php
                                            $latest = $project->latestStatus->status ?? null;
                                            $statusClass = 'secondary';
                                            if ($latest === 'Graded') $statusClass = 'success';
                                            elseif (in_array($latest, ['progress_added','progress_reviewed','progress2_added','progress2_reviewed','final_added'])) $statusClass = 'info';
                                            elseif (in_array($latest, ['Registered','Assigned','Claimed','Accepted'])) $statusClass = 'gold';
                                        @endphp
                                        <span class="pill {{ $statusClass }}">{{ $latest ? ucfirst($latest) : 'No Status' }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No projects yet</p></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- Announcements (half width) --}}
        <div class="dash-row" style="margin-top:18px;">
            <div class="panel">
                <div class="panel-head">
                    <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
                </div>
                <div class="panel-body p-0">
                    <table class="fluent-table">
                        <thead><tr><th>Date</th><th>Title</th><th>Message</th></tr></thead>
                        <tbody>
                            @forelse($lpiAnnouncements ?? [] as $announcement)
                            <tr>
                                <td style="white-space:nowrap;">{{ $announcement->created_at->format('d M Y') }}</td>
                                <td style="font-weight:500;">{{ $announcement->title }}</td>
                                <td>{{ Str::limit($announcement->message ?? $announcement->description ?? '', 120) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No announcements</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
.dash-layout{display:block;}
.dash-main{min-width:0;}
a.stat-card{text-decoration:none; color:inherit; transition:transform .12s, box-shadow .12s;}
a.stat-card:hover{transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,0,0,.08);}
.stat-card--alert{border-color:var(--brand-200,#e8a4b8);}
.stat-card--alert .stat-icon-badge.gold{background:#fef3c7; color:#b45309;}
.stat-card--alert .stat-value{color:var(--brand-600,#8d1b3d);}

.dash-main .stat-grid{grid-template-columns:repeat(5,minmax(0,1fr));}
.kpi-row-secondary{display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin:16px 0 22px;}
.mini-stat{display:flex; flex-direction:column; gap:4px; padding:14px 16px; background:#fff; border:1px solid var(--ink-100,#eceef2); border-radius:10px; text-decoration:none; color:inherit; transition:transform .12s, box-shadow .12s;}
.mini-stat:hover{transform:translateY(-2px); box-shadow:0 5px 16px rgba(0,0,0,.07);}
.mini-stat-value{font-size:26px; font-weight:700; color:var(--ink-800,#2b2733); line-height:1;}
.mini-stat-label{font-size:11.5px; color:var(--ink-500,#6f6a78); font-weight:500;}

.deadline-alert{display:flex; align-items:center; gap:14px; padding:14px 18px; margin-bottom:18px; background:linear-gradient(135deg,#fef3c7,#fde68a); border:1px solid #f59e0b; border-radius:10px;}
.deadline-alert-icon{font-size:22px; color:#b45309; flex-shrink:0;}
.deadline-alert-body{display:flex; flex-direction:column; gap:2px; min-width:0;}
.deadline-alert-title{font-size:12px; font-weight:700; color:#92400e; text-transform:uppercase; letter-spacing:.5px;}
.deadline-alert-text{font-size:13px; color:#78350f; line-height:1.4;}
.deadline-alert-link{margin-left:auto; padding:8px 16px; font-size:12px; font-weight:600; color:#92400e; background:rgba(255,255,255,.7); border:1px solid #f59e0b; border-radius:8px; text-decoration:none; white-space:nowrap; transition:background .15s;}
.deadline-alert-link:hover{background:rgba(255,255,255,.95);}

.dash-row{display:grid; grid-template-columns:1fr 1fr; gap:18px;}
.dash-col{display:flex; flex-direction:column; gap:18px; min-width:0;}
.status-chart-wrap{position:relative; height:230px; margin-bottom:10px;}
.status-legend-table td{font-size:12.5px;}
.status-dot{display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:7px; vertical-align:middle;}

@media (max-width: 1100px){
    .dash-layout{grid-template-columns:1fr;}
    .dash-main .stat-grid{grid-template-columns:repeat(3,minmax(0,1fr));}
    .kpi-row-secondary{grid-template-columns:repeat(2,1fr);}
    .dash-row{grid-template-columns:1fr;}
}
@media (max-width: 700px){
    .dash-main .stat-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function(){
    var canvas = document.getElementById('statusDonut');
    if (!canvas || typeof Chart === 'undefined') return;

    @php
        $jsLabels = []; $jsCounts = []; $jsColors = [];
        $colorMap = [
            'no_status' => '#94a3b8',
            'Graded' => '#16a34a', 'graded' => '#16a34a', 'Completed' => '#16a34a',
            'Assigned' => '#d97706', 'Claimed' => '#d97706', 'accepted' => '#d97706',
            'Accepted' => '#d97706', 'Claim-1' => '#d97706', 'Claim-2' => '#d97706',
            'Grade-1' => '#d97706', 'Grade-2' => '#d97706',
            'progress_added' => '#2563eb', 'progress_reviewed' => '#2563eb',
            'progress2_added' => '#7c3aed', 'progress2_reviewed' => '#7c3aed',
            'final_added' => '#0891b2',
        ];
        foreach (($statusCounts ?? []) as $code => $count) {
            if ($count <= 0) continue;
            $jsLabels[] = $statusLabels[$code] ?? ($code === 'no_status' ? 'No Status' : $code);
            $jsCounts[] = $count;
            $jsColors[] = $colorMap[$code] ?? '#2563eb';
        }
    @endphp
    var data = {
        labels: @json($jsLabels),
        datasets: [{
            data: @json($jsCounts),
            backgroundColor: @json($jsColors),
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function(ctx){ return ctx.label + ': ' + ctx.parsed; } } }
            }
        }
    });
});
</script>
@endpush
