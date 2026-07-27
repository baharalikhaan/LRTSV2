 @extends('layouts.app')

@section('title', 'Dashboard - RTS')

@section('content')
@if($activeRole === 'Admin')
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
                                } elseif (in_array($code, ['registered', 'Registered', 'progress_add', 'progress_added', 'report'])) {
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
@endif

@if($activeRole === 'LPI')
{{-- Stat Cards --}}
<div class="stat-grid stat-grid-5">
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge info"><i class="fas fa-folder-open"></i></div>
            <span class="stat-trend up">{{ $allProjectsCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $allProjectsCount ?? 0 }}</div>
        <div class="stat-label">All Projects</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Total projects assigned</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge maroon"><i class="fas fa-user-plus"></i></div>
            <span class="stat-trend up">{{ $unregisteredCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $unregisteredCount ?? 0 }}</div>
        <div class="stat-label">Unregistered</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Project registration not completed</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge gold"><i class="fas fa-clock"></i></div>
            <span class="stat-trend up">{{ $reportUploadPendingCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $reportUploadPendingCount ?? 0 }}</div>
        <div class="stat-label">Report Upload Pending</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Progress report not yet added</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge info"><i class="fas fa-check-double"></i></div>
            <span class="stat-trend up">{{ $progressDoneCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $progressDoneCount ?? 0 }}</div>
        <div class="stat-label">Progress Report Done</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Report submitted, awaiting review</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge success"><i class="fas fa-flag-checkered"></i></div>
            <span class="stat-trend up">{{ $gradedCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $gradedCount ?? 0 }}</div>
        <div class="stat-label">Graded</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Fully reviewed and graded</span>
        </div>
    </div>
</div>

{{-- Per-Research-Call & Per-Pillar Breakdown Panels --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px;">
    {{-- By Research Call --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-layer-group"></i> By Research Call</h2>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table">
                <thead>
                    <tr>
                        <th>Research Call</th>
                        <th style="text-align:center;">All</th>
                        <th style="text-align:center;">Unreg.</th>
                        <th style="text-align:center;">Pending</th>
                        <th style="text-align:center;">Progress</th>
                        <th style="text-align:center;">Graded</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programsStats ?? [] as $stat)
                    <tr>
                        <td style="font-weight:500;">{{ $stat['name'] }}</td>
                        <td style="text-align:center;font-weight:600;">{{ $stat['all'] }}</td>
                        <td style="text-align:center;"><span class="pill maroon">{{ $stat['unreg'] }}</span></td>
                        <td style="text-align:center;"><span class="pill gold">{{ $stat['pending'] }}</span></td>
                        <td style="text-align:center;"><span class="pill info">{{ $stat['progress'] }}</span></td>
                        <td style="text-align:center;"><span class="pill success">{{ $stat['graded'] }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No research calls data</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- By Pillar --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-columns"></i> By Pillar</h2>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table">
                <thead>
                    <tr>
                        <th>Pillar</th>
                        <th style="text-align:center;">All</th>
                        <th style="text-align:center;">Unreg.</th>
                        <th style="text-align:center;">Pending</th>
                        <th style="text-align:center;">Progress</th>
                        <th style="text-align:center;">Graded</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pillarsStats ?? [] as $stat)
                    <tr>
                        <td style="font-weight:500;">{{ $stat['name'] }}</td>
                        <td style="text-align:center;font-weight:600;">{{ $stat['all'] }}</td>
                        <td style="text-align:center;"><span class="pill maroon">{{ $stat['unreg'] }}</span></td>
                        <td style="text-align:center;"><span class="pill gold">{{ $stat['pending'] }}</span></td>
                        <td style="text-align:center;"><span class="pill info">{{ $stat['progress'] }}</span></td>
                        <td style="text-align:center;"><span class="pill success">{{ $stat['graded'] }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No pillars data</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- LPI Contribution Summary — Mini stat gadgets --}}
    <div class="stat-grid stat-grid-5">
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge maroon"><i class="fas fa-hand-holding-usd"></i></div>
            <span class="stat-trend up">{{ $grantsAvailed->count() ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $grantsAvailed->count() ?? 0 }}</div>
        <div class="stat-label">Grants Availed</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge info"><i class="fas fa-sync-alt"></i></div>
            <span class="stat-trend up">{{ $cyclesWorked->count() ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $cyclesWorked->count() ?? 0 }}</div>
        <div class="stat-label">Cycles Worked</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge gold"><i class="fas fa-layer-group"></i></div>
            <span class="stat-trend up">{{ $programsWorked->count() ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $programsWorked->count() ?? 0 }}</div>
        <div class="stat-label">Research Calls Worked</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge success"><i class="fas fa-book-open"></i></div>
            <span class="stat-trend up">{{ $publicationsTotal ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $publicationsTotal ?? 0 }}</div>
        <div class="stat-label">Publications</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge review"><i class="fas fa-user-graduate"></i></div>
            <span class="stat-trend up">{{ $studentsTotal ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $studentsTotal ?? 0 }}</div>
        <div class="stat-label">Students Attached</div>
    </div>
</div>

{{-- LPI Announcements — consistent style panel --}}
@if(isset($lpiAnnouncements) && $lpiAnnouncements->count() > 0)
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lpiAnnouncements as $announcement)
                <tr>
                    <td style="white-space:nowrap;">{{ $announcement->created_at->format('d M Y') }}</td>
                    <td style="font-weight:500;">{{ $announcement->title }}</td>
                    <td>{{ Str::limit($announcement->message ?? $announcement->description ?? '', 100) }}</td>
                </tr>
                @empty
                <tr><td colspan="3"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No announcements</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@endif

@if($activeRole === 'Reviewer')
{{-- Stat Cards — 4-key metrics --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge info"><i class="fas fa-tasks"></i></div>
            <span class="stat-trend up">{{ $totalAssigned ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $totalAssigned ?? 0 }}</div>
        <div class="stat-label">Total Assigned</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">All projects assigned to me</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge gold"><i class="fas fa-inbox"></i></div>
            <span class="stat-trend up">{{ $pendingProposals ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $pendingProposals ?? 0 }}</div>
        <div class="stat-label">Pending Proposals</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Not yet accepted/rejected</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge maroon"><i class="fas fa-hourglass-half"></i></div>
            <span class="stat-trend up">{{ $pendingGradings ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $pendingGradings ?? 0 }}</div>
        <div class="stat-label">Pending Gradings</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Accepted, grading not submitted</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge success"><i class="fas fa-check-circle"></i></div>
            <span class="stat-trend up">{{ $gradedCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $gradedCount ?? 0 }}</div>
        <div class="stat-label">Graded</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Successfully graded</span>
        </div>
    </div>
</div>

{{-- My Reviews table + Announcements side-by-side --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px;">
    {{-- My Reviews --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-check-double"></i> My Reviews</h2>
            <div class="panel-actions">
                <a href="{{ route('projects.available') }}" class="btn-secondary btn-sm">View All</a>
            </div>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table">
                <thead><tr><th>Project ID</th><th>Project Title</th><th>Research Call</th><th>Grant Category</th><th>&nbsp;</th></tr></thead>
                <tbody>
                    @forelse($assignedProjects ?? [] as $project)
                    <tr>
                        <td>
                            <code style="font-size:12px;">{{ $project->old_project_id ?? $project->id }}</code>
                        </td>
                        <td>
                            <a href="{{ route('projects.show', $project->id) }}" style="font-weight:500;color:var(--brand-500);">
                                {{ \Illuminate\Support\Str::limit($project->project_title, 45) }}
                            </a>
                        </td>
                        <td style="font-size:12.5px;color:var(--color-ink-600);">
                            {{ $project->program->program_title ?? '—' }}
                        </td>
                        <td style="font-size:12.5px;color:var(--color-ink-600);">
                            @if($project->program && $project->program->grant)
                                {{ $project->program->grant->category ?? $project->program->grant->grant_code ?? '—' }}
                            @else
                                <span style="color:var(--color-ink-400);">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $isClaimed = $project->pivot->proposalstatus === 'accepted';
                                $hasGraded = $project->hasStatus(\App\Models\Project::STATUS_GRADED);
                            @endphp
                            @if(!$isClaimed)
                                <span class="text-muted" style="font-size:12px;">Pending proposal</span>
                            @elseif($hasGraded)
                                <button type="button" class="btn-secondary btn-sm open-grade-modal"
                                        data-project-id="{{ $project->id }}"
                                        data-project-title="{{ $project->project_title }}"
                                        title="View your grade for this project">
                                    <i class="fas fa-star"></i> View Grades
                                </button>
                            @else
                                <a href="{{ route('projects.grading', $project->id) }}" class="btn-primary btn-sm" style="text-decoration:none;font-size:11px;padding:4px 10px;">
                                    <i class="fas fa-star" style="font-size:11px;"></i> Grade Project
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state py-4">
                                <i class="fas fa-check-square"></i>
                                <p class="mb-0">No projects assigned to you yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Announcements --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
            <div class="panel-actions">
                <a href="{{ route('announcements.index') }}" class="btn-secondary btn-sm">View All</a>
            </div>
        </div>
        <div class="panel-body p-0">
            @if(isset($reviewerAnnouncements) && $reviewerAnnouncements->count() > 0)
            <table class="fluent-table">
                <thead>
                    <tr><th>Date</th><th>Title</th><th>Message</th></tr>
                </thead>
                <tbody>
                    @forelse($reviewerAnnouncements as $announcement)
                    <tr>
                        <td style="white-space:nowrap;font-size:12px;">{{ $announcement->created_at ? $announcement->created_at->format('d M Y') : '—' }}</td>
                        <td style="font-weight:500;">{{ $announcement->title }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($announcement->message ?? $announcement->description ?? '', 80) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No announcements</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
            @else
            <div class="empty-state py-4">
                <i class="fas fa-inbox"></i>
                <p class="mb-0">No announcements yet.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@if(count($announcements ?? []) > 0)
<div class="panel" style="margin-top: 18px;">
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

{{-- Fallback for unrecognised roles (or if no dashboard sections match) --}}
@if(!in_array($activeRole ?? null, ['Admin', 'LPI', 'Reviewer']))
<div class="panel">
    <div class="panel-body text-center py-5">
        <i class="fas fa-user-shield" style="font-size:32px;color:var(--ink-300);"></i>
        <h3 style="margin-top:12px;font-weight:600;">Welcome, {{ Auth::user()->name }}</h3>
        <p style="color:var(--ink-500);">Active role: <strong>{{ $activeRole }}</strong></p>
        <p style="color:var(--ink-500);font-size:13px;">Use the switcher in the top bar to switch between your available roles.</p>
        <a href="{{ route('projects.available') }}" class="btn-primary" style="margin-top:8px;">
            <i class="fas fa-search"></i> Browse Projects
        </a>
    </div>
</div>
@endif

@endsection
