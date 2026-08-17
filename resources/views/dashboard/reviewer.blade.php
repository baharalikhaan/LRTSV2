@extends('layouts.app')

@section('title', 'Reviewer Dashboard - RTS')

@section('content')
{{-- Stat Cards — project review breakdown --}}
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
            <span class="stat-trend up">{{ $pendingCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
        <div class="stat-label">Pending</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Not yet accepted / proposal pending</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge maroon"><i class="fas fa-hourglass-half"></i></div>
            <span class="stat-trend up">{{ $inProgressCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $inProgressCount ?? 0 }}</div>
        <div class="stat-label">In Progress</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Accepted, grading not submitted</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon-badge success"><i class="fas fa-check-circle"></i></div>
            <span class="stat-trend up">{{ $reviewedCount ?? 0 }}</span>
        </div>
        <div class="stat-value">{{ $reviewedCount ?? 0 }}</div>
        <div class="stat-label">Reviewed</div>
        <div class="stat-subs">
            <span class="stat-sub inactive">Successfully graded</span>
        </div>
    </div>
</div>

{{-- My Reviews + Rating Panel side-by-side --}}
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
                <thead><tr><th>Project ID</th><th>Project Title</th><th>Research Call</th><th>&nbsp;</th></tr></thead>
                <tbody>
                    @forelse($assignedProjects ?? [] as $project)
                    <tr>
                        <td>
                            <code style="font-size:12px;">{{ $project->old_project_id ?? $project->id }}</code>
                        </td>
                        <td>
                            <a href="{{ route('projects.show', $project->id) }}" style="font-weight:500;color:var(--brand-500);">
                                {{ \Illuminate\Support\Str::limit($project->project_title ?? $project->title, 40) }}
                            </a>
                        </td>
                        <td style="font-size:12.5px;color:var(--ink-600);">
                            {{ $project->program->program_title ?? '—' }}
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
                                        data-project-title="{{ $project->project_title ?? $project->title }}"
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
                        <td colspan="4">
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

    {{-- My Rating — given by admins per research call --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-star"></i> My Performance Rating</h2>
        </div>
        <div class="panel-body p-0">
            @if(($ratingRows ?? collect())->count() > 0)
            <div style="padding:14px 16px; border-bottom:1px solid var(--ink-100,#eeedf0); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div>
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--ink-400,#8b8592); font-weight:500;">Overall Average</div>
                    <div style="font-size:22px; font-weight:700; color:var(--brand-500,#8d1b3d);">{{ $overallAverage ?? 0 }} <span style="font-size:12px; color:var(--ink-400,#8b8592);">/ 5</span></div>
                </div>
                <span class="pill success" style="font-size:11px;">Rated by admin per research call</span>
            </div>
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
            @else
            <div class="empty-state py-4">
                <i class="fas fa-star-half-alt"></i>
                <p class="mb-0">No ratings yet. Your performance rating per research call will appear here.</p>
            </div>
            @endif
        </div>
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
@endsection