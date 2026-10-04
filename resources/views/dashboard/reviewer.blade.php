@extends('layouts.app')

@section('title', 'Reviewer Dashboard - RTS')

@section('content')
<div class="dash-layout">
    <div class="dash-main">

        {{-- Primary KPI cards --}}
        <div class="stat-grid">
            <a class="stat-card" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge info"><i class="fas fa-tasks"></i></div>
                </div>
                <div class="stat-value">{{ $totalAssigned ?? 0 }}</div>
                <div class="stat-label">Total Assigned</div>
                <div class="stat-subs">
                    <span class="stat-sub active">Projects to review</span>
                </div>
            </a>

            <a class="stat-card {{ ($pendingCount ?? 0) > 0 ? 'stat-card--alert' : '' }}" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge gold"><i class="fas fa-inbox"></i></div>
                </div>
                <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
                <div class="stat-label">Pending Proposals</div>
                <div class="stat-subs">
                    <span class="stat-sub">Awaiting your acceptance</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge maroon"><i class="fas fa-hourglass-half"></i></div>
                </div>
                <div class="stat-value">{{ $inProgressCount ?? 0 }}</div>
                <div class="stat-label">Pending Grading</div>
                <div class="stat-subs">
                    <span class="stat-sub">Awaiting grading</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge success"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-value">{{ $gradedCount ?? 0 }}</div>
                <div class="stat-label">Graded</div>
                <div class="stat-subs">
                    <span class="stat-sub active">Reviews completed</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge review"><i class="fas fa-star"></i></div>
                </div>
                <div class="stat-value">{{ $overallAverage ?? 0 }}</div>
                <div class="stat-label">Avg Rating</div>
                <div class="stat-subs">
                    <span class="stat-sub">Out of 5.0</span>
                </div>
            </a>
        </div>

        {{-- Status donut + My Reviews --}}
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
                        <h2><i class="fas fa-check-double"></i> My Reviews</h2>
                        <div class="panel-actions">
                            <a href="{{ route('projects.available') }}" class="btn-secondary btn-sm">View All</a>
                        </div>
                    </div>
                    <div class="panel-body p-0">
                        <table class="fluent-table">
                            <thead><tr><th>Project</th><th>Research Call</th><th>Status</th><th>&nbsp;</th></tr></thead>
                            <tbody>
                                @forelse($assignedProjects->take(6) ?? [] as $project)
                                <tr>
                                    <td>
                                        <div style="font-weight:500;font-size:13px;">{{ \Illuminate\Support\Str::limit($project->project_title ?? $project->title, 35) }}</div>
                                        <div style="font-size:11px;color:var(--ink-400,#8b8592);">ID: {{ $project->old_project_id ?? $project->id }}</div>
                                    </td>
                                    <td style="font-size:12px;color:var(--ink-600);">{{ $project->program->program_title ?? '—' }}</td>
                                    <td>
                                        @php
                                            $hasGraded = $project->hasStatus(\App\Models\Project::STATUS_GRADED);
                                        @endphp
                                        @if($hasGraded)
                                            <span class="pill success">Graded</span>
                                        @else
                                            <span class="pill info">In Progress</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hasGraded)
                                            <button type="button" class="btn-secondary btn-sm open-grade-modal"
                                                    data-project-id="{{ $project->id }}"
                                                    data-project-title="{{ $project->project_title ?? $project->title }}"
                                                    style="font-size:11px;padding:4px 10px;">
                                                <i class="fas fa-star"></i> View
                                            </button>
                                        @else
                                            <a href="{{ route('projects.grading', $project->id) }}" class="btn-primary btn-sm" style="text-decoration:none;font-size:11px;padding:4px 10px;">
                                                <i class="fas fa-star"></i> Grade
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4"><div class="empty-state py-4"><i class="fas fa-check-square"></i><p class="mb-0">No projects assigned yet.</p></div></td></tr>
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
                            @forelse($reviewerAnnouncements ?? [] as $announcement)
                            <tr>
                                <td style="white-space:nowrap;">{{ $announcement->created_at ? $announcement->created_at->format('d M Y') : '—' }}</td>
                                <td style="font-weight:500;">{{ $announcement->title }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($announcement->message ?? $announcement->description ?? '', 120) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No announcements</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Performance Rating (full width) --}}
        @if(($ratingRows ?? collect())->count() > 0)
        <div class="panel" style="margin-top:18px;">
            <div class="panel-head">
                <h2><i class="fas fa-star"></i> My Performance Rating</h2>
                <div class="panel-actions">
                    <span class="pill success" style="font-size:11px;">Overall: {{ $overallAverage ?? 0 }} / 5.0</span>
                </div>
            </div>
            <div class="panel-body p-0">
                <table class="fluent-table">
                    <thead>
                        <tr>
                            <th>Research Call</th>
                            <th>Conflict</th>
                            <th>Responsiveness</th>
                            <th>Comprehensiveness</th>
                            <th># Reviews</th>
                            <th>Behaviour</th>
                            <th>Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ratingRows as $row)
                        <tr>
                            <td>
                                <div style="font-weight:500; font-size:12.5px;">{{ $row['program'] }}</div>
                                <div style="font-size:11px; color:var(--ink-400,#8b8592);">{{ $row['cycle'] }}</div>
                            </td>
                            <td style="text-align:center;">{{ $row['conflict'] > 0 ? $row['conflict'] : '—' }}</td>
                            <td style="text-align:center;">{{ $row['responsiveness'] > 0 ? $row['responsiveness'] : '—' }}</td>
                            <td style="text-align:center;">{{ $row['comprehensiveness'] > 0 ? $row['comprehensiveness'] : '—' }}</td>
                            <td style="text-align:center;">{{ $row['no_reviewers'] > 0 ? $row['no_reviewers'] : '—' }}</td>
                            <td style="text-align:center;">{{ $row['behaviour'] > 0 ? $row['behaviour'] : '—' }}</td>
                            <td style="text-align:center;">
                                <span class="pill {{ $row['average'] >= 4 ? 'success' : ($row['average'] >= 3 ? 'review' : 'maroon') }}">
                                    {{ $row['average'] > 0 ? $row['average'] : '—' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

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
