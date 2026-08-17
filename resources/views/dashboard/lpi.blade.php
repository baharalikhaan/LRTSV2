@extends('layouts.app')

@section('title', 'LPI Dashboard - RTS')

@section('content')
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

@endsection