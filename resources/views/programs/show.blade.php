@extends('layouts.app')

@section('title', $program->program_title . ' - RTS')

@php
    $totalProjects = $program->projects->count();
    $registeredProjects = $program->projects->filter(function($p) { return $p->added ?? false; })->count();
    $pendingProjects = $totalProjects - $registeredProjects;
    $isActive = $program->isActive();
@endphp

@section('content')
{{-- Page Head --}}
<div class="page-head">
    <div>
        <h1><i class="fas fa-sync-alt"></i> {{ $program->program_title }}</h1>
        <p>Research call overview, deadlines, and project submissions.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('programs.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Research Calls
        </a>
        @if(Auth::user() && Auth::user()->isAdmin())
        <a href="{{ route('projects.assign-review', $program->id) }}" class="btn-primary btn-sm">
            <i class="fas fa-user-check"></i> Assign Reviewers
        </a>
        @endif
    </div>
</div>

{{-- Inactive Research Call Banner --}}
@if(!$isActive)
<div style="background:linear-gradient(135deg, #fbeef1 0%, #f3d2da 100%); border:1px solid var(--color-brand-200); border-radius:8px; padding:14px 18px; margin-bottom:22px; display:flex; align-items:center; gap:12px;">
    <div style="width:36px; height:36px; border-radius:50%; background:var(--color-brand-500); color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
        <i class="fas fa-clock"></i>
    </div>
    <div>
        <strong style="color:var(--color-brand-800); font-size:14px;">Research Call Inactive</strong>
        <p style="margin:2px 0 0 0; color:var(--color-brand-700); font-size:13px;">
            This research call's final deadline
            ({{ optional($program->extended_final_rpt_deadline ?? $program->final_rpt_deadline)->format('M d, Y') ?? 'N/A' }})
            has passed. Projects under this research call can no longer be manipulated.
        </p>
    </div>
</div>
@endif

{{-- Main Content: Two Columns --}}
<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px;">
    {{-- Left: Grant & Cycle Info --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-tag"></i> Grant & Cycle</h2>
        </div>
        <div class="panel-body" style="display:grid; gap:14px;">
            <div class="detail-row">
                <span class="detail-label">Grant</span>
                <span class="detail-value">
                    @if($program->grant)
                        <span class="pill info">{{ $program->grant->grant_code }}</span>
                        {{ $program->grant->grant_title ?? $program->grant->grant_name ?? '' }}
                    @else
                        &mdash;
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Cycle</span>
                <span class="detail-value">
                    @if($program->cycleConfig)
                        <span class="pill secondary">{{ $program->cycleConfig->year ?? $program->cycleConfig->title ?? 'N/A' }}</span>
                    @else
                        &mdash;
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Research Call Status</span>
                <span class="detail-value">
                    @if($isActive)
                        <span class="pill success" style="gap:5px;">
                            <i class="fas fa-check-circle" style="font-size:11px;"></i> Active
                        </span>
                    @else
                        <span class="pill danger" style="gap:5px;">
                            <i class="fas fa-lock" style="font-size:11px;"></i> Inactive
                        </span>
                    @endif
                </span>
            </div>
            @if($program->description)
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value" style="font-size:13px; color:var(--color-ink-600); line-height:1.6;">
                    {{ $program->description }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Right: Statistics & Quick Summary --}}
    <div class="panel">
        <div class="panel-head">
            <h2><i class="fas fa-chart-pie"></i> Statistics</h2>
        </div>
        <div class="panel-body">
            {{-- Stat cards in 2x2 grid --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div class="stat-card-flat" style="text-align:center; padding:14px 10px; background:var(--color-brand-50); border-radius:6px;">
                    <div style="font-size:26px; font-weight:700; color:var(--color-brand-500); line-height:1.2;">
                        {{ $totalProjects }}
                    </div>
                    <div style="font-size:11.5px; font-weight:500; color:var(--color-brand-600); text-transform:uppercase; letter-spacing:0.04em; margin-top:4px;">
                        Total Projects
                    </div>
                </div>
                <div class="stat-card-flat" style="text-align:center; padding:14px 10px; background:var(--color-ink-50); border-radius:6px;">
                    <div style="font-size:26px; font-weight:700; color:var(--success); line-height:1.2;">
                        {{ $registeredProjects }}
                    </div>
                    <div style="font-size:11.5px; font-weight:500; color:var(--color-ink-500); text-transform:uppercase; letter-spacing:0.04em; margin-top:4px;">
                        Registered
                    </div>
                </div>
                <div class="stat-card-flat" style="text-align:center; padding:14px 10px; background:var(--color-ink-50); border-radius:6px;">
                    <div style="font-size:26px; font-weight:700; color:var(--warning); line-height:1.2;">
                        {{ $pendingProjects }}
                    </div>
                    <div style="font-size:11.5px; font-weight:500; color:var(--color-ink-500); text-transform:uppercase; letter-spacing:0.04em; margin-top:4px;">
                        Pending Registration
                    </div>
                </div>
                <div class="stat-card-flat" style="text-align:center; padding:14px 10px; background:var(--color-ink-50); border-radius:6px;">
                    <div style="font-size:26px; font-weight:700; color:var(--color-info); line-height:1.2;">
                        {{ $program->projects->filter(function($p) { return $p->added ?? false; })->count() }}
                    </div>
                    <div style="font-size:11.5px; font-weight:500; color:var(--color-ink-500); text-transform:uppercase; letter-spacing:0.04em; margin-top:4px;">
                        With Proposals
                    </div>
                </div>
            </div>

            {{-- Activity timeline mini --}}
            <div style="border-top:1px solid var(--color-ink-100); padding-top:12px;">
                <div style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-ink-400); margin-bottom:8px;">
                    <i class="fas fa-history"></i> Timeline
                </div>
                <div style="display:grid; gap:6px;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:12.5px;">
                        <span style="width:8px; height:8px; border-radius:50%; background:var(--color-brand-300); flex-shrink:0;"></span>
                        <span style="color:var(--color-ink-500);">Created</span>
                        <span style="color:var(--color-ink-400); margin-left:auto;">{{ $program->created_at->format('M d, Y') }}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:12.5px;">
                        <span style="width:8px; height:8px; border-radius:50%; background:{{ $isActive ? 'var(--success)' : 'var(--color-ink-300)' }}; flex-shrink:0;"></span>
                        <span style="color:var(--color-ink-500);">Last Updated</span>
                        <span style="color:var(--color-ink-400); margin-left:auto;">{{ $program->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Deadlines Panel --}}
<div class="panel" style="margin-bottom: 22px;">
    <div class="panel-head">
        <h2><i class="fas fa-calendar-alt"></i> Deadlines & Reporting</h2>
        <div class="panel-actions">
            @if($isActive)
                <span class="pill success" style="gap:5px;"><i class="fas fa-check-circle" style="font-size:11px;"></i> Open</span>
            @else
                <span class="pill danger" style="gap:5px;"><i class="fas fa-lock" style="font-size:11px;"></i> Closed</span>
            @endif
        </div>
    </div>
    <div class="panel-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
            {{-- Progress Report 1 --}}
            <div style="border:1px solid var(--color-ink-100); border-radius:6px; padding:14px 16px;">
                <div style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-ink-400); margin-bottom:8px;">
                    <i class="fas fa-file-alt"></i> Progress Report 1
                </div>
                <div class="detail-row" style="margin-bottom:6px;">
                    <span class="detail-label">Deadline</span>
                    <span class="detail-value">{{ $program->prog_rpt_deadline ? $program->prog_rpt_deadline->format('M d, Y H:i') : '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Extended</span>
                    <span class="detail-value">{{ $program->extended_prog_rpt_deadline ? $program->extended_prog_rpt_deadline->format('M d, Y H:i') : '—' }}</span>
                </div>
                @php
                    $effDeadline1 = $program->extended_prog_rpt_deadline ?? $program->prog_rpt_deadline;
                    $past1 = $effDeadline1 ? now()->greaterThan($effDeadline1) : null;
                @endphp
                @if($effDeadline1)
                <div style="margin-top:6px;">
                    @if($past1)
                        <span class="pill danger" style="font-size:10px;">Passed</span>
                    @else
                        <span class="pill success" style="font-size:10px;">
                            {{ now()->diffInDays($effDeadline1, false) >= 0 ? now()->diffInDays($effDeadline1) . ' days left' : 'Due soon' }}
                        </span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Progress Report 2 --}}
            <div style="border:1px solid var(--color-ink-100); border-radius:6px; padding:14px 16px;">
                <div style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-ink-400); margin-bottom:8px;">
                    <i class="fas fa-file-alt"></i> Progress Report 2
                </div>
                <div class="detail-row" style="margin-bottom:6px;">
                    <span class="detail-label">Deadline</span>
                    <span class="detail-value">{{ $program->prog_rpt2_deadline ? $program->prog_rpt2_deadline->format('M d, Y H:i') : '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Extended</span>
                    <span class="detail-value">{{ $program->extended_prog_rpt2_deadline ? $program->extended_prog_rpt2_deadline->format('M d, Y H:i') : '—' }}</span>
                </div>
                @php
                    $effDeadline2 = $program->extended_prog_rpt2_deadline ?? $program->prog_rpt2_deadline;
                    $past2 = $effDeadline2 ? now()->greaterThan($effDeadline2) : null;
                @endphp
                @if($effDeadline2)
                <div style="margin-top:6px;">
                    @if($past2)
                        <span class="pill danger" style="font-size:10px;">Passed</span>
                    @else
                        <span class="pill success" style="font-size:10px;">
                            {{ now()->diffInDays($effDeadline2, false) >= 0 ? now()->diffInDays($effDeadline2) . ' days left' : 'Due soon' }}
                        </span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Final Report --}}
            <div style="border:1px solid var(--color-ink-100); border-radius:6px; padding:14px 16px; {{ !$isActive ? 'background:var(--color-brand-50);' : '' }}">
                <div style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--color-ink-400); margin-bottom:8px;">
                    <i class="fas fa-flag-checkered"></i> Final Report
                </div>
                <div class="detail-row" style="margin-bottom:6px;">
                    <span class="detail-label">Deadline</span>
                    <span class="detail-value">{{ $program->final_rpt_deadline ? $program->final_rpt_deadline->format('M d, Y H:i') : '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Extended</span>
                    <span class="detail-value">{{ $program->extended_final_rpt_deadline ? $program->extended_final_rpt_deadline->format('M d, Y H:i') : '—' }}</span>
                </div>
                @php
                    $effDeadlineFinal = $program->extended_final_rpt_deadline ?? $program->final_rpt_deadline;
                    $pastFinal = $effDeadlineFinal ? now()->greaterThan($effDeadlineFinal) : null;
                @endphp
                @if($effDeadlineFinal)
                <div style="margin-top:6px;">
                    @if($pastFinal)
                        <span class="pill danger" style="font-size:10px;"><i class="fas fa-lock"></i> Passed</span>
                    @else
                        <span class="pill success" style="font-size:10px;">
                            {{ now()->diffInDays($effDeadlineFinal, false) >= 0 ? now()->diffInDays($effDeadlineFinal) . ' days left' : 'Due soon' }}
                        </span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Projects Table --}}
<div class="panel">
    <div class="panel-head">
        <h2><i class="fas fa-file-alt"></i> Projects</h2>
        <div class="panel-actions">
            <span class="text-muted small fw-medium" style="font-size:12px; color:var(--color-ink-400);">
                {{ $totalProjects }} total
                @if(!$isActive)
                    &middot; <span style="color:var(--color-brand-500);">Read-only</span>
                @endif
            </span>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="projectsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Project ID</th>
                    <th>Title</th>
                    <th>PI Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                @forelse($program->projects as $cp)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><code>{{ $cp->old_project_id }}</code></td>
                    <td>
                        <a href="{{ route('projects.show', $cp->id) }}" style="font-weight:500; color:var(--color-brand-500); text-decoration:none;">
                            {{ $cp->title }}
                        </a>
                    </td>
                    <td>{{ $cp->author ?? ($cp->lpi->name ?? 'N/A') }}</td>
                    <td><a href="mailto:{{ $cp->lpi->email ?? '' }}" style="color:var(--color-ink-500); text-decoration:none;">{{ $cp->lpi->email ?? 'N/A' }}</a></td>
                    <td>
                        @if($cp->added ?? false)
                            <span class="pill success"><i class="fas fa-check-circle"></i> Registered</span>
                        @else
                            <span class="pill review"><i class="fas fa-clock"></i> Pending</span>
                        @endif
                    </td>
                    <td style="font-weight:600;">{{ $cp->total_score ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state py-4">
                            <i class="fas fa-file-alt"></i>
                            <h5>No Projects Imported</h5>
                            <p>Import projects by uploading an Excel file when creating a program.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($program->projects->count() > 0)
    $('#projectsTable').DataTable({
        dom: '<"row align-items-center"<"col-sm-6"B><"col-sm-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row align-items-center"<"col-sm-5"i><"col-sm-7"p>>',
        buttons: [
            { extend: 'copy', text: '<i class="fas fa-copy"></i> Copy', className: 'btn btn-sm' },
            { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-sm', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn btn-sm', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-sm', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'print', text: '<i class="fas fa-print"></i> Print', className: 'btn btn-sm', exportOptions: { columns: [0,1,2,3,4,5,6] } }
        ],
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [] }
        ]
    });
    @endif
});
</script>
@endpush
