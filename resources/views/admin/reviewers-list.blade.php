@extends('layouts.app')

@section('title', 'Reviewer Grading - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-star"></i> Reviewer Grading</h1>
        <p>Select a reviewer to rate their performance per program.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:space-between;">
            <form method="GET" class="filter-bar" id="filterForm" style="margin-bottom:0;">
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
                <div class="filter-group">
                    <label>College:</label>
                    <select id="collegeFilter" class="search-input" style="min-width:140px;">
                        <option value="">All Colleges</option>
                        @foreach(($colleges ?? []) as $college)
                            <option value="{{ $college->name }}">{{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Pillar:</label>
                    <select id="pillarFilter" class="search-input" style="min-width:140px;">
                        <option value="">All Pillars</option>
                        @foreach(($pillars ?? []) as $pillar)
                            <option value="{{ $pillar->pillar }}">{{ $pillar->pillar }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <span style="font-size:12px;color:var(--color-ink-400);white-space:nowrap;">
                <span id="resultCount">{{ count($reviewers ?? []) }}</span> reviewers
            </span>
        </div>
    </div>
    <div class="panel-body p-0">
        @if(!($reviewers ?? null) || count($reviewers) === 0)
            <div class="empty-state py-5 text-center" style="color:var(--color-ink-400);">
                <i class="fas fa-users" style="font-size:32px;opacity:0.4;margin-bottom:8px;"></i>
                <h5>No Reviewers Found</h5>
                <p>No reviewers with reviewed projects are available.</p>
            </div>
        @else
            <table class="fluent-table w-100" id="reviewersTable">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Type</th>
                        <th>Colleges</th>
                        <th>Pillars</th>
                        <th>Projects Reviewed</th>
                        <th class="text-center" style="min-width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($reviewers ?? []) as $reviewer)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:30px;height:30px;border-radius:50%;background:var(--color-brand-50);color:var(--color-brand-600);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($reviewer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:500;font-size:13px;">{{ $reviewer->name }}</div>
                                    <div style="font-size:11px;color:var(--color-ink-400);">{{ $reviewer->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-{{ $reviewer->type }}">{{ $reviewer->type }}</span></td>
                        <td>
                            @forelse($reviewer->college_list as $college)
                                <span class="pill" style="margin:1px 2px;">{{ $college->name }}</span>
                            @empty
                                <span style="color:var(--color-ink-300);font-size:11px;">—</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse($reviewer->pillar_list as $pillar)
                                <span class="pill" style="margin:1px 2px;">{{ $pillar->pillar }}</span>
                            @empty
                                <span style="color:var(--color-ink-300);font-size:11px;">—</span>
                            @endforelse
                        </td>
                        <td>
                            <span style="font-weight:700;color:var(--color-brand-500);font-size:15px;">{{ $reviewer->reviewed_projects_count }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('reviewer-grading.show', $reviewer->id) }}" class="btn-primary btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
                                <i class="fas fa-star"></i> Rate
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
    @if(($reviewers ?? null) && count($reviewers) > 0)
    var table = $('#reviewersTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        pageLength: 20,
        lengthChange: false,
        columnDefs: [
            { orderable: false, targets: [5] },
            { searchable: false, targets: [5] }
        ],
        language: {
            emptyTable: "No reviewers match your filters.",
            zeroRecords: "No reviewers match your filters."
        },
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
        $('#resultCount').text(table.page.info().recordsDisplay);
    });

    $('#collegeFilter').on('change', function() {
        var val = $(this).val();
        if (val) {
            table.column(2).search('^\\s*' + val.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*$', true, false).draw();
        } else {
            table.column(2).search('').draw();
        }
        $('#resultCount').text(table.page.info().recordsDisplay);
    });

    $('#pillarFilter').on('change', function() {
        var val = $(this).val();
        if (val) {
            table.column(3).search('^\\s*' + val.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*$', true, false).draw();
        } else {
            table.column(3).search('').draw();
        }
        $('#resultCount').text(table.page.info().recordsDisplay);
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
