@extends('layouts.app')

@section('title', 'Reviewer Assignment - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-user-check"></i> Reviewer Assignment</h1>
        <p>Assign reviewers to projects and manage existing assignments.</p>
    </div>
</div>

@include('projects.partials.research-call-context', ['contextKey' => 'reviewer-assignment', 'listedCount' => $confProjects->count()])

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
                    <select name="cycle_id" onchange="onAssignCycleChange(this)">
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
                    <label>Assignment:</label>
                    <select name="assignment" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="unassigned" {{ ($assignment ?? '') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                        <option value="assigned" {{ ($assignment ?? '') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
            <script>
                // Changing the cycle resets the research-call selection; the
                // call list is narrowed to the chosen cycle server-side.
                function onAssignCycleChange(sel) {
                    var form = sel.form;
                    var prog = form.querySelector('[name="program_id"]');
                    if (prog) prog.value = '';
                    form.submit();
                }
            </script>
        </div>
    </div>

    <form action="{{ route('projects.bulk-assign') }}" method="POST" id="bulkAssignForm">
        @csrf
        @if($confProjects->count() > 0)
        @php
            $assignedCount = $confProjects->filter(fn($p) => $p->reviewers->isNotEmpty())->count();
            $unassignedCount = $confProjects->filter(fn($p) => $p->reviewers->isEmpty())->count();
        @endphp
        <div class="panel-head" style="border-bottom:1px solid var(--ink-100);background:var(--sand-50);gap:16px;">
            <span style="flex:1;min-width:0;font-size:12.5px;line-height:1.5;color:var(--ink-500);">
                <i class="fas fa-info-circle"></i> Select a reviewer per project — for an already-assigned project the selected reviewer is
                <strong>added alongside</strong> the existing one (each reviews independently). Then click
                <strong>Bulk Assign</strong>. Projects with no reviewer selected will be skipped; every newly assigned reviewer receives
                an assignment email.
                <span style="display:inline-flex;gap:6px;margin-left:10px;vertical-align:middle;">
                    <span class="pill warning" style="font-size:11px;">{{ $actionCount }} need action</span>
                    <span class="pill review" style="font-size:11px;">{{ $assignedCount }} Assigned</span>
                    <span class="pill warning" style="font-size:11px;">{{ $unassignedCount }} Unassigned</span>
                </span>
            </span>
            <button type="submit" class="btn btn-primary btn-sm" id="bulkAssignBtn" style="flex-shrink:0;">
                <i class="fas fa-user-check"></i> Bulk Assign Reviewers
            </button>
        </div>
        @endif
        <div class="panel-body p-0" style="overflow-x:auto;">
            @if($confProjects->isEmpty())
                <div class="empty-state py-5">
                    <i class="fas fa-check-circle" style="opacity:0.3;"></i>
                    <h5>No Projects Found</h5>
                    <p>No projects match your current filters.</p>
                </div>
            @else
                <table class="fluent-table w-100" id="projectsTable">
                    <colgroup>
                        <col style="width:150px;">
                        <col style="width:140px;">
                        <col>
                        <col style="width:75px;">
                        <col style="width:85px;">
                        <col style="width:105px;">
                        <col style="width:230px;">
                        <col style="width:190px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Project ID</th>
                            <th>LPI</th>
                            <th>Project title</th>
                            <th>Grant</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Assigned reviewer</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($confProjects as $index => $cp)
                        @php
                            $flowStatus = $cp->currentWorkflowStatus();
                            $isRegistered = $cp->hasStatus(\App\Models\Project::STATUS_REGISTERED) && $cp->lpi_id;
                            $statusLabel = (!$isRegistered && $flowStatus === 'imported') ? 'Unregistered' : ucfirst(str_replace('_', ' ', $flowStatus ?? '—'));
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
                            $assignedReviewers = $cp->reviewers;
                            $hasReviewer = $assignedReviewers->isNotEmpty();
                            $needsAction = (bool) ($needsReviewerAction[$cp->id] ?? false);
                        @endphp
                        <tr class="{{ $needsAction ? 'needs-action' : '' }}">
                            <td style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><a href="{{ route('projects.show', $cp->id) }}"><code>{{ $cp->old_project_id }}</code></a></td>
                            <td>
                                @if($cp->lpi)
                                    <div style="font-weight:500;">{{ $cp->lpi->name }}</div>
                                    <div title="{{ $cp->lpi->email }}" style="font-size:11px;color:var(--color-ink-400);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cp->lpi->email }}</div>
                                @else
                                    <span style="font-size:12px;color:var(--ink-400);font-style:italic;">—</span>
                                @endif
                            </td>
                            <td>
                                <span title="{{ $cp->title }}" style="font-weight:500;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $cp->title }}</span>
                            </td>
                            <td><span class="pill info" style="font-size:11px;white-space:nowrap;">{{ $cp->program->grant->grant_code ?? 'N/A' }}</span></td>
                            <td><span class="pill info" style="font-size:11px;white-space:nowrap;">{{ ucfirst($cp->program->grant->category ?? 'N/A') }}</span></td>
                            <td>
                                <span class="pill {{ $statusPillClass }}">{{ $statusLabel }}</span>
                                @if($needsAction)
                                    <span class="pill warning" style="font-size:10px;margin-left:4px;white-space:nowrap;" title="This project still needs reviewer attention">
                                        Needs action
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($hasReviewer)
                                    <div style="display:flex;flex-direction:column;gap:4px;align-items:flex-start;">
                                        @foreach($assignedReviewers as $reviewer)
                                            <span class="pill review" style="font-size:11px;display:inline-flex;align-items:center;gap:6px;max-width:100%;">
                                                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                    {{ $reviewer->name }}@if($reviewer->pivot->claimed_at) <i class="fas fa-check" title="Claimed" style="font-size:9px;"></i> @endif
                                                </span>
                                                {{-- Un-assign this reviewer straight from his name --}}
                                                <a href="#"
                                                   onclick="event.preventDefault(); event.stopPropagation(); confirmUnassignReviewer({{ $cp->id }}, {{ $reviewer->id }});"
                                                   title="Un-assign {{ $reviewer->name }}"
                                                   style="font-size:10px;font-weight:600;color:#a93434;text-decoration:underline;white-space:nowrap;">
                                                    Un-assign
                                                </a>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="font-size:12px;color:var(--ink-400);font-style:italic;">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:6px;align-items:stretch;">
                                    <select name="assignments[{{ $index }}][reviewers][]" class="reviewer-select">
                                        <option value="">{{ $hasReviewer ? '— Add reviewer —' : '— Select Reviewer —' }}</option>
                                        @foreach($reviewerGroups as $pillarName => $pillarReviewers)
                                            <optgroup label="{{ $pillarName }}">
                                                @foreach($pillarReviewers as $reviewer)
                                                    @if(!$assignedReviewers->pluck('id')->contains($reviewer->id))
                                                        <option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="assignments[{{ $index }}][project_id]" value="{{ $cp->id }}">
                                </div>
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
    /* Fixed layout so the column widths declared in the DataTable config are
       honoured exactly (the title column would otherwise be squeezed by the
       wide LPI / action cells). */
    #projectsTable {
        table-layout: fixed;
    }
    #projectsTable th,
    #projectsTable td {
        overflow-wrap: anywhere;
    }

    /* Rows of a research call that still need reviewer attention (the page
       lists the whole call when a research call is selected). */
    #projectsTable tbody tr.needs-action > td:first-child {
        box-shadow: inset 3px 0 0 var(--brand-500, #8d1b3d);
    }
    #projectsTable tbody tr.needs-action {
        background: rgba(141, 27, 61, .03);
    }

    .reviewer-select {
        width: 100%;
        min-width: 160px;
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
            dom: 'Brt<"bottom"lip>',
            order: [],
            autoWidth: false,
            pageLength: 25,
            columns: [
                { width: '150px' }, // Project ID
                { width: '140px' }, // LPI
                { width: 'auto' },  // Project title — takes the remaining space
                { width: '75px' },  // Grant
                { width: '85px' },  // Category
                { width: '105px' }, // Status
                { width: '230px' }, // Assigned reviewer (+ un-assign link)
                { width: '190px' }  // Action (reviewer dropdown)
            ],
            columnDefs: [
                { orderable: false, searchable: false, targets: [6, 7] }
            ]
        });

        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
    });
</script>
@endif
@endpush
