@extends('layouts.app')

@section('title', 'Report Cards - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-file-lines"></i> Report Cards</h1>
        <p>View and print report cards for graded projects.</p>
    </div>
</div>

<div class="alert alert-info" style="font-size:13px; border-radius:8px;">
    <i class="fas fa-circle-info"></i> Only projects graded on the <strong>Progress</strong> and/or <strong>Final</strong> report are displayed here.
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:space-between;">
            <form method="GET" class="filter-bar" id="filterForm" style="margin-bottom:0;">
                <div class="filter-group">
                    <label>Research Cycle:</label>
                    <select id="cycleFilter" name="cycle_id" class="search-input" style="min-width:200px;" onchange="this.form.submit();">
                        <option value="">All Cycles</option>
                        @foreach($cycleConfigs as $cycle)
                            <option value="{{ $cycle->id }}" {{ $cycleId == $cycle->id ? 'selected' : '' }}>{{ $cycle->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Grant:</label>
                    <select id="grantFilter" name="grant_id" class="search-input" style="min-width:160px;" onchange="this.form.submit();">
                        <option value="">All Grants</option>
                        @foreach($grants as $grant)
                            <option value="{{ $grant->id }}" {{ ($grantId ?? '') == $grant->id ? 'selected' : '' }}>{{ $grant->grant_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search projects..." class="search-input">
                </div>
            </form>
        </div>
    </div>

    <div class="panel-body p-0">
        @if($projects->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-file-lines" style="opacity:0.3;"></i>
                <h5>No Reviewed Projects</h5>
                <p>No projects have been reviewed or graded yet for the selected filters.</p>
            </div>
        @else
            <table class="fluent-table w-100" id="reportCardsTable">
                <thead>
                    <tr>
                        <th style="min-width:130px;">Project ID</th>
                        <th style="width:40%; min-width:280px;">Title</th>
                        <th style="min-width:120px;">LPI</th>
                        <th class="text-center">Reviewed Reports</th>
                        <th class="text-center" style="width:170px; min-width:170px;">Status</th>
                        <th class="text-center" style="min-width:120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                    @php
                        $hasPG = $project->progressGradings->isNotEmpty();
                        $hasPG2 = $project->progress2Gradings->isNotEmpty();
                        $hasFG = $project->finalGradings->isNotEmpty();

                        $reviewedReports = [];
                        if ($hasPG) $reviewedReports[] = 'PR1';
                        if ($hasPG2) $reviewedReports[] = 'PR2';
                        if ($hasFG) $reviewedReports[] = 'Final';

                        $fg = $project->finalGradings->first();
                        $pg = $project->progressGradings->first();
                        $statusLabel = 'Pending';
                        $statusPill = 'amber';
                        if ($fg) {
                            $statusLabel = $fg->isAccepted == 1 ? 'Final Accepted' : 'Final Rejected';
                            $statusPill = $fg->isAccepted == 1 ? 'ok' : 'no';
                        } elseif ($pg) {
                            $statusLabel = $pg->isAccepted == 1 ? 'Progress Accepted' : 'Progress Rejected';
                            $statusPill = $pg->isAccepted == 1 ? 'ok' : 'no';
                        }
                    @endphp

                    <tr>
                        <td>
                            <a href="{{ route('projects.report-card', $project->id) }}" target="_blank"
                               style="font-weight:600; color:var(--color-brand-500); text-decoration:none;">
                                {{ $project->old_project_id ?? '#' . $project->id }}
                            </a>
                        </td>
                        <td>
                            <span style="font-weight:400;">{{ $project->title }}</span>
                        </td>
                        <td>
                            {{ $project->lpi->name ?? '—' }}
                        </td>
                        <td class="text-center">
                            @if($hasPG)
                                <span class="pill info" style="font-size:11px;">PR1</span>
                            @endif
                            @if($hasPG2)
                                <span class="pill info" style="font-size:11px;">PR2</span>
                            @endif
                            @if($hasFG)
                                <span class="pill success" style="font-size:10px; padding:2px 6px;">Final</span>
                            @endif
                            @if(!$hasPG && !$hasPG2 && !$hasFG)
                                <span class="pill inactive" style="font-size:10px; padding:2px 6px;">—</span>
                            @endif
                        </td>
                        <td class="text-center" style="width:170px; min-width:170px; white-space:nowrap;">
                            <span class="pill {{ $statusPill }}" style="font-size:11px; font-weight:500; margin:0 auto;">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('projects.report-card', $project->id) }}" target="_blank"
                               class="btn btn-sm btn-primary" style="font-size:11px; padding:4px 10px; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                <i class="fas fa-file-pdf" style="font-size:11px;"></i> Report Card
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#reportCardsTable').DataTable({
            dom: 'rtbottomlip',
            autoWidth: false,
            order: [[0, 'asc']],
            columnDefs: [
                { width: '40%', targets: [1] },
                { width: '170px', targets: [4] },
                { orderable: false, targets: [5] },
                { searchable: false, targets: [5] }
            ]
        });

        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        $('#cycleFilter').on('change', function() {
            $('#filterForm').submit();
        });
    });
</script>
@endpush

@push('styles')
<style>
    #reportCardsTable { font-size: 12px; }
    #reportCardsTable thead th { font-weight: 600; }
    #reportCardsTable tbody td { font-weight: 400; }
    #reportCardsTable .pill { font-weight: 500; }
</style>
@endpush

@endsection
