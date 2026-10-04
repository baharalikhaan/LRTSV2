@extends('layouts.app')

@section('title', 'Extend Progress Report - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-calendar-plus"></i> Extend Progress Report</h1>
        <p>Toggle the progress report deadline extension for each project.</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger py-2" style="border-radius:8px;font-size:13px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions">
            <form method="GET" class="filter-bar" id="filterForm">
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle_id" onchange="this.form.submit()">
                        <option value="">All Cycles</option>
                        @foreach($cycleConfigs as $cycle)
                            <option value="{{ $cycle->id }}" {{ ($cycleId ?? '') == $cycle->id ? 'selected' : '' }}>{{ $cycle->title }} ({{ $cycle->year }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Research Call:</label>
                    <select name="program_id" onchange="this.form.submit()">
                        <option value="">All Research Calls</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ ($programId ?? '') == $program->id ? 'selected' : '' }}>{{ $program->program_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="unregistered" {{ ($status ?? '') == 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                        <option value="registered" {{ ($status ?? '') == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="claimed" {{ ($status ?? '') == 'claimed' ? 'selected' : '' }}>Claimed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
        </div>
    </div>

    <div class="panel-body p-0">
        @if($confProjects->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-check-circle" style="opacity:0.3;"></i>
                <h5>No Projects Found</h5>
                <p>No projects match your current filters.</p>
            </div>
        @else
            <table class="fluent-table w-100" id="projectsTable">
                <thead>
                    <tr>
                        <th style="min-width:130px;">Project ID</th>
                        <th>Title</th>
                        <th>Grant</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th class="text-center" style="min-width:210px;">Extend Progress Report</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($confProjects as $cp)
                    @php
                        $flowStatus = $cp->currentWorkflowStatus();
                        $isRegistered = $cp->hasStatus(\App\Models\Project::STATUS_REGISTERED) && $cp->lpi_id;
                        $statusLabel = (!$isRegistered && $flowStatus === 'imported') ? 'Unregistered' : ucfirst($flowStatus ?? '—');
                        switch ($flowStatus) {
                            case 'imported': $statusPillClass = 'warning'; break;
                            case 'registered': $statusPillClass = 'accepted'; break;
                            case 'Assigned':
                            case 'assigned': $statusPillClass = 'review'; break;
                            case 'Claimed':
                            case 'claimed': $statusPillClass = 'accepted'; break;
                            case 'progress_added':
                            case 'progress_reviewed': $statusPillClass = 'info'; break;
                            case 'progress_rejected':
                            case 'final_rejected':
                            case 'rejected': $statusPillClass = 'danger'; break;
                            case 'final_added': $statusPillClass = 'info'; break;
                            case 'Graded':
                            case 'graded': $statusPillClass = 'accepted'; break;
                            default: $statusPillClass = 'ink'; break;
                        }
                    @endphp
                    <tr>
                        <td><a href="{{ route('projects.show', $cp->id) }}"><code>{{ $cp->old_project_id }}</code></a></td>
                        <td><span style="font-weight:500;">{{ $cp->title }}</span></td>
                        <td><span class="pill info" style="font-size:11px;">{{ $cp->program->grant->grant_code ?? 'N/A' }}</span></td>
                        <td><span class="pill info" style="font-size:11px;">{{ ucfirst($cp->program->grant->category ?? 'N/A') }}</span></td>
                        <td><span class="pill {{ $statusPillClass }}">{{ $statusLabel }}</span></td>
                        <td class="text-center">
                            <form action="{{ route('projects.toggle-extended-progress') }}" method="POST" style="display:inline-flex;align-items:center;gap:8px;">
                                @csrf
                                <input type="hidden" name="project_id" value="{{ $cp->id }}">
                                <button type="submit" class="btn btn-sm {{ $cp->extended_progress ? 'btn-primary' : 'btn-secondary' }}"
                                        style="white-space:nowrap;">
                                    <i class="fas {{ $cp->extended_progress ? 'fa-check-circle' : 'fa-calendar-plus' }}" style="font-size:11px;"></i>
                                    {{ $cp->extended_progress ? 'Extended — Click to Revoke' : 'Extend Progress Report' }}
                                </button>
                                @if($cp->extended_progress)
                                    <span class="pill accepted" style="font-size:10px;">Extended</span>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($confProjects->count() > 0)
<script>
    $(document).ready(function() {
        var table = $('#projectsTable').DataTable({
            dom: 'rt<"bottom"lip>',
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [5] },
                { searchable: false, targets: [5] }
            ]
        });

        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
    });
</script>
@endif
@endpush