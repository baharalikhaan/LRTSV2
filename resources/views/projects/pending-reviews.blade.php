@extends('layouts.app')

@section('title', 'Pending Reviews - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-clock-rotate-left"></i> Pending Reviews</h1>
        <p>Projects with reviews due — step in as admin for assigned reviewers.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions">
            <form method="GET" class="filter-bar" id="filterForm">
                <div class="filter-group">
                    <label>Research Cycle:</label>
                    <select name="cycle_id" style="min-width:180px;">
                        <option value="">All Cycles</option>
                        @foreach($cycleConfigs as $cycle)
                            <option value="{{ $cycle->id }}" {{ ($cycleId ?? '') == $cycle->id ? 'selected' : '' }}>{{ $cycle->title }} ({{ $cycle->year }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Grant:</label>
                    <select name="grant_id" style="min-width:160px;">
                        <option value="">All Grants</option>
                        @foreach($grants as $grant)
                            <option value="{{ $grant->id }}" {{ ($grantId ?? '') == $grant->id ? 'selected' : '' }}>{{ $grant->grant_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Program:</label>
                    <select name="program_id" style="min-width:220px;">
                        <option value="">All Programs</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ ($programId ?? '') == $prog->id ? 'selected' : '' }}>{{ $prog->program_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" onchange="this.form.submit();" style="min-width:180px;">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" {{ ($status ?? '') == $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
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
        @if($pendingReviews->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-check-circle" style="opacity:0.3;"></i>
                <h5>No Pending Reviews</h5>
                <p>All reviewers are up to date — nothing to step in for.</p>
            </div>
        @else
            <table class="fluent-table w-100" id="pendingReviewsTable">
                <thead>
                    <tr>
                        <th style="min-width:130px;">Project ID</th>
                        <th>Title</th>
                        <th>Program</th>
                        <th>LPI</th>
                        <th style="min-width:180px;">Assigned Reviewer</th>
                        <th style="min-width:160px;">Review Type</th>
                        <th class="text-center" style="min-width:150px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingReviews as $item)
                    <tr>
                        <td><a href="{{ route('projects.show', $item->project->id) }}"><code>{{ $item->project->old_project_id ?? $item->project->id }}</code></a></td>
                        <td><span style="font-weight:500;">{{ $item->project->title ?? $item->project->project_title ?? '—' }}</span></td>
                        <td>{{ $item->project->program->program_title ?? '—' }}</td>
                        <td>{{ $item->project->lpi->name ?? '—' }}</td>
                        <td>{{ $item->reviewer }}</td>
                        <td>
                            @php
                                $pillClass = match($item->reviewType) {
                                    'awaiting_claim' => 'gold',
                                    'pending_upload' => 'warning',
                                    'progress_report', 'progress2_report' => 'info',
                                    'final_report' => 'success',
                                    default => 'ink',
                                };
                            @endphp
                            <span class="pill {{ $pillClass }}">{{ $item->reviewTypeLabel }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('projects.grading', $item->project->id) }}" class="btn btn-primary btn-sm" style="font-size:11px;padding:4px 10px;text-decoration:none;">
                                <i class="fas fa-star" style="font-size:11px;"></i> Review as Admin
                            </a>
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
<script>
    $(document).ready(function() {
        var table = $('#pendingReviewsTable').DataTable({
            dom: 'rt<"bottom"lip>',
            order: [[0, 'asc']],
            pageLength: 25,
            language: { emptyTable: 'No pending reviews found.' }
        });

        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        var filterUrl = '{{ route("projects.pending-reviews.filter-data") }}';

        function reloadDropdowns(cycleId, grantId, callback) {
            $.get(filterUrl, { cycle_id: cycleId, grant_id: grantId }, function(data) {
                var grantSel = $('select[name="grant_id"]');
                var progSel = $('select[name="program_id"]');
                var currentGrant = grantSel.val();
                var currentProg = progSel.val();

                grantSel.find('option:gt(0)').remove();
                $.each(data.grants, function(i, g) {
                    var opt = $('<option>').val(g.id).text(g.label);
                    if (g.id == currentGrant) opt.attr('selected', 'selected');
                    grantSel.append(opt);
                });

                progSel.find('option:gt(0)').remove();
                $.each(data.programs, function(i, p) {
                    var opt = $('<option>').val(p.id).text(p.label);
                    if (p.id == currentProg) opt.attr('selected', 'selected');
                    progSel.append(opt);
                });

                if (callback) callback();
            });
        }

        $('select[name="cycle_id"]').on('change', function() {
            var cycleId = $(this).val();
            reloadDropdowns(cycleId, '', function() {
                $('select[name="grant_id"]').val('');
                $('select[name="program_id"]').val('');
                $('#filterForm').submit();
            });
        });

        $('select[name="grant_id"]').on('change', function() {
            var cycleId = $('select[name="cycle_id"]').val();
            var grantId = $(this).val();
            $.get(filterUrl, { cycle_id: cycleId, grant_id: grantId }, function(data) {
                var progSel = $('select[name="program_id"]');
                var currentProg = progSel.val();
                progSel.find('option:gt(0)').remove();
                $.each(data.programs, function(i, p) {
                    var opt = $('<option>').val(p.id).text(p.label);
                    if (p.id == currentProg) opt.attr('selected', 'selected');
                    progSel.append(opt);
                });
                $('#filterForm').submit();
            });
        });

        $('select[name="program_id"]').on('change', function() {
            $('#filterForm').submit();
        });

        $('select[name="status"]').on('change', function() {
            $('#filterForm').submit();
        });
    });
</script>
@endpush
