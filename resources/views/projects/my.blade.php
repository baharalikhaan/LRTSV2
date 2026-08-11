@extends('layouts.app')

@section('title', 'My Projects - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-list"></i> My Registered Projects</h1>
        <p>Projects you've registered.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('projects.available') }}" class="btn-secondary">
            <i class="fas fa-download"></i> Available Projects
        </a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2><i class="fas fa-list"></i> My Projects</h2>
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
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="projectsTable">
            <thead>
                <tr>
                    <th>Project ID</th>
                    <th>Title</th>
                    <th>Research Call</th>
                    <th>Grant</th>
                    <th>Category</th>
                    <th>Budget (QAR)</th>
                    <th>Status</th>
                    <th class="text-center" style="min-width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                @php $availActions = $project->availableActions(auth()->user()); @endphp
                <tr>
                    <td><a href="{{ route('projects.show', $project->id) }}"><code>{{ $project->old_project_id }}</code></a></td>
                    <td><a href="{{ route('projects.show', $project->id) }}" style="font-weight:500;">{{ $project->title }}</a></td>
                    <td>{{ $project->program->program_title ?? '—' }}</td>
                    <td><span class="pill info" style="font-size:11px;">{{ $project->program->grant->grant_code ?? 'N/A' }}</span></td>
                    <td><span class="pill info" style="font-size:11px;">{{ ucfirst($project->program->grant->category ?? 'N/A') }}</span></td>
                    <td>{{ $project->requested_budget_qar ? number_format($project->requested_budget_qar, 2) : '—' }}</td>
                    <td>
                        @php
                            $flowStatus = $project->currentWorkflowStatus();
                            switch($flowStatus) {
                                case 'imported': $flowPillClass = 'warning'; break;
                                case 'registered': $flowPillClass = 'info'; break;
                                case 'assigned': $flowPillClass = 'review'; break;
                                case 'claimed': $flowPillClass = 'accepted'; break;
                                case 'progress':
                                case 'progress_add':
                                case 'progress_added':
                                case 'progress_reviewed': $flowPillClass = 'info'; break;
                                case 'progress_rejected':
                                case 'rejected':
                                case 'proposal_rejected': $flowPillClass = 'danger'; break;
                                case 'final_added': $flowPillClass = 'info'; break;
                                case 'accepted': $flowPillClass = 'accepted'; break;
                                case 'reviewed': $flowPillClass = 'info'; break;
                                case 'graded': $flowPillClass = 'accepted'; break;
                                case 'report': $flowPillClass = 'info'; break;
                                default: $flowPillClass = 'ink'; break;
                            }
                        @endphp
                        <span class="pill {{ $flowPillClass }}">{{ ucfirst($flowStatus) }}</span>
                    </td>
                    <td class="text-center">
                        <div style="display:flex;gap:4px;justify-content:center;align-items:center;flex-wrap:wrap;">
                            @include('projects.partials.workflow-actions', ['project' => $project, 'actions' => $availActions])
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">You haven't registered any projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#projectsTable').DataTable({
            dom: 'rt<"bottom"lip>',
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [7] },
                { searchable: false, targets: [7] }
            ]
        });

        // Connect custom search input to DataTables search
        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
    });
</script>
@endpush
