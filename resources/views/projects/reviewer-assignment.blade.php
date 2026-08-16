@extends('layouts.app')

@section('title', 'Reviewer Assignment - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-user-check"></i> Reviewer Assignment</h1>
        <p>Bulk assign reviewers to projects that do not have a reviewer yet.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success py-2" style="border-radius:8px;font-size:13px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
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

    <form action="{{ route('projects.bulk-assign') }}" method="POST">
        @csrf
        @if($confProjects->count() > 0)
        <div class="panel-head" style="border-bottom:1px solid var(--ink-100);background:var(--sand-50);">
            <span style="font-size:13px;color:var(--ink-500);">
                <i class="fas fa-info-circle"></i> Select a reviewer for each project, then click
                <strong>Bulk Assign</strong>. Projects with no reviewer selected will be skipped.
            </span>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-user-check"></i> Bulk Assign Reviewers
            </button>
        </div>
        @endif
        <div class="panel-body p-0">
            @if($confProjects->isEmpty())
                <div class="empty-state py-5">
                    <i class="fas fa-check-circle" style="opacity:0.3;"></i>
                    <h5>No Projects Found</h5>
                    <p>All projects already have reviewers assigned, or no projects match your current filters.</p>
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
                            <th style="min-width:230px;">Reviewer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($confProjects as $index => $cp)
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
                            <td>
                                <select name="assignments[{{ $index }}][reviewers][]" class="reviewer-select">
                                    <option value="">— Select Reviewer —</option>
                                    @foreach($reviewerGroups as $pillarName => $pillarReviewers)
                                        <optgroup label="{{ $pillarName }}">
                                            @foreach($pillarReviewers as $reviewer)
                                                <option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <input type="hidden" name="assignments[{{ $index }}][project_id]" value="{{ $cp->id }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .reviewer-select {
        width: 210px;
        max-width: 100%;
        padding: 5px 26px 5px 10px;
        font-size: 12px;
        font-weight: 500;
        color: var(--ink-700, #38333e);
        border: 1px solid var(--ink-200, #d8d6dc);
        border-radius: var(--fluent-radius-md, 6px);
        background: #fff;
        cursor: pointer;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='%23675f6e'%3E%3Cpath d='M1 1l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
    }
    .reviewer-select:hover { border-color: var(--brand-300, #d3738f); }
    .reviewer-select:focus {
        border-color: var(--brand-500, #8d1b3d);
        box-shadow: 0 0 0 2px rgba(141,27,61,.15);
    }
    .reviewer-select optgroup {
        font-size: 11px;
        font-weight: 700;
        color: var(--brand-600, #7a1636);
        background: var(--sand-50, #faf7f0);
    }
    .reviewer-select optgroup option {
        font-size: 12px;
        font-weight: 500;
        color: var(--ink-700, #38333e);
        background: #fff;
    }
</style>
@endpush

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