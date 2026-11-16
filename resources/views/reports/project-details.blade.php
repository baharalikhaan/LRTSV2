@extends('layouts.app')

@section('title', 'Project Details Report - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-table-list"></i> Project Details Report</h1>
        <p>
            Every project in one record &mdash; one row per project. Pick the fields you want to
            see; the Excel export contains exactly the same fields.
        </p>
    </div>
    <div class="page-actions" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('reports.project-details-export', $filters) }}" class="btn-primary btn-sm" style="text-decoration:none;">
            <i class="fas fa-file-excel"></i> Export to Excel
        </a>
        <button onclick="window.print()" class="btn-primary btn-sm" style="flex-shrink:0;">
            <i class="fas fa-print"></i> Print / PDF
        </button>
    </div>
</div>

@if(session('error'))
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

@php
    $columns = $data['columns'];
    $rows = $data['rows'];
    $totalColumns = 0;
    foreach ($groups as $group) {
        $totalColumns += count($group['columns']);
    }
    $selectedCount = count($selectedFields);
@endphp

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;margin-bottom:0;">
                <div class="filter-row">
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle_id" id="cycleFilter">
                        <option value="">All Cycles</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle->id }}" {{ (($filters['cycle_id'] ?? '') == $cycle->id) ? 'selected' : '' }}>{{ $cycle->year }} &mdash; {{ $cycle->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Grant:</label>
                    <select name="grant_id" id="grantFilter">
                        <option value="">All Grants</option>
                        @foreach($grants as $grant)
                            <option value="{{ $grant->id }}" {{ (($filters['grant_id'] ?? '') == $grant->id) ? 'selected' : '' }}>{{ $grant->grant_code }} &mdash; {{ $grant->grant_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Research Call:</label>
                    <select name="program_id" id="programFilter">
                        <option value="">All Research Calls</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ (($filters['program_id'] ?? '') == $program->id) ? 'selected' : '' }}>{{ $program->program_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" id="statusFilter">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ (($filters['status'] ?? '') === $status) ? 'selected' : '' }}>{{ str_replace('_', ' ', $status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search title, ID, LPI..." class="search-input">
                </div>
                </div>

                {{-- Field picker: multi-select of the report fields (own row) --}}
                <div class="field-picker" id="fieldPicker">
                    <button type="button" class="field-picker-toggle" id="fieldPickerToggle" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-table-columns"></i>
                        <span>Fields</span>
                        <span class="field-picker-badge" id="fieldPickerCount">{{ $selectedCount }} / {{ $totalColumns }}</span>
                        <i class="fas fa-chevron-down field-picker-caret"></i>
                    </button>

                    <div class="field-picker-panel" id="fieldPickerPanel" hidden>
                        <div class="field-picker-head">
                            <input type="text" id="fieldSearch" class="search-input" placeholder="Search fields or table names...">
                            <div class="field-picker-actions">
                                <button type="button" data-field-action="all">All</button>
                                <button type="button" data-field-action="none">None</button>
                                <button type="button" data-field-action="default">Recommended</button>
                            </div>
                            <div class="field-picker-hint">Fields are grouped by the database table they come from.</div>
                        </div>

                        <div class="field-picker-body" id="fieldPickerBody">
                            @foreach($groups as $source => $group)
                                <div class="field-picker-group" data-group="{{ $source }}">
                                    <label class="field-picker-group-head">
                                        <input type="checkbox" class="field-group-toggle" data-group="{{ $source }}">
                                        <span class="field-picker-group-title">{{ $group['label'] }}</span>
                                        <code class="field-picker-table">{{ $group['table'] }}</code>
                                        <em>{{ count($group['columns']) }}</em>
                                    </label>
                                    @foreach($group['columns'] as $column)
                                        <label class="field-picker-item" data-label="{{ \Illuminate\Support\Str::lower($column['label']) }} {{ \Illuminate\Support\Str::lower($group['table']) }}">
                                            <input type="checkbox" name="fields[]" value="{{ $column['key'] }}" class="field-check"
                                                {{ in_array($column['key'], $selectedFields, true) ? 'checked' : '' }}>
                                            <span class="field-picker-item-label">{{ $column['label'] }}</span>
                                            <span class="field-picker-item-group">{{ $column['group'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <div class="field-picker-foot">
                            <span class="field-picker-status" id="fieldPickerStatus">{{ $selectedCount }} of {{ $totalColumns }} fields selected</span>
                            <button type="button" class="btn-link" id="fieldPickerCancel">Cancel</button>
                            <button type="button" class="btn-primary btn-sm" id="fieldPickerApply">
                                <i class="fas fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="panel-body">
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:12px;font-size:12.5px;color:var(--ink-500,#6f6a78);">
            <span><strong style="color:var(--ink-800,#2f2b38);">{{ count($rows) }}</strong> project(s)</span>
            <span><strong style="color:var(--ink-800,#2f2b38);">{{ $selectedCount }}</strong> of {{ $totalColumns }} fields shown</span>
            <span>The Excel export contains exactly these fields.</span>
        </div>

        <div class="report-table-scroll">
            <table class="fluent-table w-100" id="projectDetailsTable" style="font-size:12.5px;">
                <thead>
                    <tr>
                        @foreach($columns as $c)
                            <th style="min-width:120px;">{{ $c['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @foreach($columns as $c)
                                @php $value = $row[$c['key']] ?? null; @endphp
                                <td>
                                    @if($c['key'] === 'project_id')
                                        <span style="font-weight:600;">{{ $value }}</span>
                                    @elseif(is_numeric($value) && in_array($c['key'], ['budget_amount','actual_exp_amount','utilization_percent','final_total'], true))
                                        <span style="text-align:right;">{{ $value }}</span>
                                    @elseif(strlen((string) $value) > 60)
                                        <div style="max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $value }}">{{ $value }}</div>
                                    @else
                                        {{ $value ?? '—' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}">
                                <div class="empty-state py-4">
                                    <i class="fas fa-table-list"></i>
                                    <h5>No Projects Found</h5>
                                    <p>No projects match the selected filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.fluent-alert{padding:10px 14px;border-radius:6px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.fluent-alert--error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
#projectDetailsTable td,#projectDetailsTable th{padding:8px 10px !important;vertical-align:middle;white-space:nowrap;}
/* The table is much wider than the screen: keep it scrolling inside the panel
   instead of stretching the grid column (and the sidebar) with it. The pane
   scrolls vertically too, so both header rows can stay pinned. */
.report-table-scroll{
    overflow:auto;
    contain:inline-size;
    max-width:100%;
    max-height:calc(100vh - 250px);
}
/* separate borders are needed for the sticky header rows */
#projectDetailsTable{border-collapse:separate;border-spacing:0;}
/* grouped second header row */
#projectDetailsTable thead tr.group-header-row th{
    background-color:var(--sand-50,#faf7f0);
    font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
    color:var(--brand-700,#63102b);text-align:center;
    padding:6px 10px !important;white-space:nowrap;
    border-top:1px solid var(--brand-500,#8d1b3d);
    border-bottom:1px solid var(--brand-500,#8d1b3d);
    position:sticky;top:0;z-index:3;
}
/* hairline between neighbouring groups */
#projectDetailsTable thead tr.group-header-row th + th{
    border-left:1px solid rgba(141,27,61,.28);
}
/* maroon hairline under the column-name row (the primary header).
   background-color (not the background shorthand) so DataTables' sorting
   arrow background image survives. */
#projectDetailsTable thead tr:not(.group-header-row) th{
    background-color:var(--sand-100,#f2ead6);
    border-bottom:1px solid var(--brand-500,#8d1b3d);
    position:sticky;top:var(--group-header-h,28px);z-index:2;
}
/* keep the header rules and their tint when printed */
#projectDetailsTable thead th{
    -webkit-print-color-adjust:exact;print-color-adjust:exact;
}

/* The Project ID column stays pinned on the left while scrolling sideways,
   so every row can still be identified. */
#projectDetailsTable thead tr.group-header-row th:first-child{
    position:sticky;left:0;z-index:4;
    background-color:var(--sand-50,#faf7f0);
    border-left:none;
}
#projectDetailsTable thead tr:not(.group-header-row) th:first-child{
    position:sticky;left:0;z-index:3;
    background-color:var(--sand-100,#f2ead6);
}
#projectDetailsTable tbody td:first-child{
    position:sticky;left:0;z-index:1;
    background-color:#fff;
    border-right:1px solid var(--ink-100,#eee);
}
/* The pinned cell must be opaque, so the striped and hover tints are
   applied as solid colours (the app's rgba tints would let the
   scrolling content bleed through). */
#projectDetailsTable tbody tr:nth-child(even) td:first-child{
    background-color:#faf7f0;
}
#projectDetailsTable tbody tr:hover td:first-child{
    background-color:#fbeef1;
}

/* ---- field picker ---- */
.filter-row{display:flex;align-items:center;gap:14px;flex-wrap:wrap;width:100%;}
/* The table is much wider than the screen: keep it scrolling inside the panel
   instead of stretching the grid column (and the sidebar) with it. */
.report-table-scroll{overflow-x:auto;contain:inline-size;max-width:100%;}
.field-picker{position:relative;display:inline-block;}
.field-picker-toggle{
    display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid var(--ink-200,#e3e0e6);
    border-radius:6px;padding:5px 10px;font-size:12px;font-weight:500;color:var(--ink-700,#463f50);cursor:pointer;white-space:nowrap;
}
.field-picker-toggle:hover{border-color:var(--brand-500,#8d1b3d);}
.field-picker-badge{background:var(--brand-500,#8d1b3d);color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:600;}
.field-picker-caret{font-size:9px;transition:transform .15s ease;}
.field-picker.open .field-picker-caret{transform:rotate(180deg);}
.field-picker-panel{
    position:absolute;z-index:60;top:calc(100% + 6px);left:0;width:380px;max-width:92vw;
    background:#fff;border:1px solid var(--ink-200,#e3e0e6);border-radius:10px;box-shadow:0 12px 32px rgba(0,0,0,.14);
    display:flex;flex-direction:column;overflow:hidden;
}
.field-picker-panel[hidden]{display:none !important;}
.field-picker-head{padding:10px;border-bottom:1px solid var(--ink-100,#eee);display:flex;flex-direction:column;gap:8px;}
.field-picker-head .search-input{width:100%;}
.field-picker-actions{display:flex;gap:6px;flex-wrap:wrap;}
.field-picker-hint{font-size:10.5px;color:var(--ink-400,#8b8592);}
.field-picker-actions button{
    background:#fff;border:1px solid var(--ink-200,#e3e0e6);border-radius:5px;padding:3px 9px;font-size:11px;
    color:var(--ink-700,#463f50);cursor:pointer;
}
.field-picker-actions button:hover{border-color:var(--brand-500,#8d1b3d);color:var(--brand-500,#8d1b3d);}
.field-picker-body{overflow-y:auto;max-height:340px;padding:6px 4px 6px 10px;}
.field-picker-group{margin-bottom:8px;}
.field-picker-group-head{
    display:flex;align-items:center;gap:7px;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;
    color:var(--brand-500,#8d1b3d);padding:5px 0 4px;cursor:pointer;
    border-bottom:1px solid rgba(141,27,61,.18);
}
.field-picker-group-title{color:var(--brand-600,#7a1636);}
.field-picker-table{
    font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:10px;font-weight:600;letter-spacing:0;
    text-transform:none;background:var(--brand-50,#fbeef1);color:var(--brand-600,#7a1636);
    border:1px solid rgba(141,27,61,.18);border-radius:4px;padding:1px 6px;
}
.field-picker-group-head em{font-style:normal;font-weight:600;text-transform:none;letter-spacing:0;margin-left:auto;color:var(--brand-500,#8d1b3d);}
.field-picker-item{display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--ink-700,#463f50);padding:2px 0;cursor:pointer;}
.field-picker-item:hover .field-picker-item-label{color:var(--brand-500,#8d1b3d);}
.field-picker-item input,.field-picker-group-head input{margin:0;cursor:pointer;}
.field-picker-item-label{flex:1;}
.field-picker-item-group{font-size:10.5px;color:var(--ink-400,#8b8592);white-space:nowrap;}
.field-picker-foot{
    border-top:1px solid var(--ink-100,#eee);padding:9px 10px;display:flex;align-items:center;gap:8px;background:#fbfbfc;
}
.field-picker-status{font-size:11.5px;color:var(--ink-500,#6f6a78);flex:1;}
.btn-link{background:none;border:none;color:var(--ink-500,#6f6a78);font-size:12px;cursor:pointer;padding:4px 6px;}
.btn-link:hover{color:var(--ink-800,#2f2b38);}
.field-picker-panel .btn-primary{border:none;cursor:pointer;}

@media print{
    .no-print,.fluent-command-bar,.fluent-sidebar,.fluent-footer,.sidebar-overlay,.fluent-dropdown,.app-shell>aside,.btn-primary,.page-actions,.dataTables_wrapper .dt-buttons,.dataTables_filter,.dataTables_paginate,.dataTables_info,.dataTables_length,.icon-btn,.cmd-search,.role-switcher,.notif-dot,#notifDropdown,#userDropdown,.fluent-alert,#workflowModal,.modal,.modal-backdrop,.toastify,.field-picker{display:none!important;}
    .app-shell,.fluent-content,.fluent-content-body{margin:0!important;padding:0!important;max-width:100%!important;width:100%!important;display:block!important;background:#fff!important;border:none!important;box-shadow:none!important;overflow:visible!important;}
    /* let the table flow over the printed pages again */
    .report-table-scroll{max-height:none!important;overflow:visible!important;}
    #projectDetailsTable thead th,#projectDetailsTable tbody td{position:static!important;}
    #projectDetailsTable thead{display:table-header-group;}
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#projectDetailsTable').DataTable({
        // No 'B' (buttons) and an empty button list: the report has its own
        // "Export to Excel" action, so the grid's Copy/CSV/Excel/PDF/Print
        // buttons are not wanted here.
        dom: 'rt<"bottom"lip>',
        buttons: [],
        order: [],
        pageLength: 25
    });

    // Second header row: the group each column belongs to, spanning its
    // columns. DataTables rebuilds the thead on init, so it is added after.
    var headerGroups = @json($headerGroups);
    var thead = $('#projectDetailsTable thead');
    if (thead.length && headerGroups.length) {
        var rowHtml = '<tr class="group-header-row">';
        headerGroups.forEach(function (group) {
            rowHtml += '<th colspan="' + group.span + '" class="group-header-cell">' + group.label + '</th>';
        });
        rowHtml += '</tr>';
        thead.prepend(rowHtml);
    }

    // Pin the column-name row right under the pinned group row.
    function syncStickyOffset() {
        var groupCell = thead.find('tr.group-header-row th').first();
        if (!groupCell.length) { return; }
        $('#projectDetailsTable').css('--group-header-h', groupCell.outerHeight() + 'px');
    }
    syncStickyOffset();
    $(window).on('resize', syncStickyOffset);
    if (window.ResizeObserver) {
        new ResizeObserver(syncStickyOffset).observe(thead.get(0));
    }

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    ['#cycleFilter', '#grantFilter', '#programFilter', '#statusFilter'].forEach(function(sel) {
        $(sel).on('change', function() {
            $('#filterForm').submit();
        });
    });

    // ---------------- field picker ----------------
    var picker = $('#fieldPicker');
    var panel = $('#fieldPickerPanel');
    var checks = panel.find('.field-check');
    var total = checks.length;

    function updateCounts() {
        var n = checks.filter(':checked').length;
        $('#fieldPickerCount').text(n + ' / ' + total);
        $('#fieldPickerStatus').text(n + ' of ' + total + ' fields selected');
        $('#fieldPickerApply').prop('disabled', n === 0);
    }

    function syncGroupToggles() {
        panel.find('.field-picker-group').each(function() {
            var items = $(this).find('.field-check');
            var checked = items.filter(':checked').length;
            var toggle = $(this).find('.field-group-toggle');
            toggle.prop('checked', checked > 0 && checked === items.length);
            toggle.prop('indeterminate', checked > 0 && checked < items.length);
        });
    }

    panel.on('change', '.field-check', function() {
        updateCounts();
        syncGroupToggles();
    });

    panel.on('change', '.field-group-toggle', function() {
        var isChecked = $(this).is(':checked');
        $(this).closest('.field-picker-group').find('.field-check').prop('checked', isChecked);
        updateCounts();
        syncGroupToggles();
    });

    panel.on('click', '[data-field-action]', function() {
        var action = $(this).data('field-action');
        if (action === 'all') {
            checks.prop('checked', true);
        } else if (action === 'none') {
            checks.prop('checked', false);
        } else if (action === 'default') {
            checks.prop('checked', false);
            var recommended = @json(\App\Services\ProjectDetailsReportService::DEFAULT_FIELDS);
            checks.each(function() {
                if (recommended.indexOf(this.value) !== -1) { $(this).prop('checked', true); }
            });
        }
        updateCounts();
        syncGroupToggles();
    });

    // filter the field list
    $('#fieldSearch').on('input', function() {
        var q = $.trim($(this).val()).toLowerCase();
        panel.find('.field-picker-item').each(function() {
            var label = $(this).data('label') || '';
            $(this).toggle(q === '' || label.indexOf(q) !== -1);
        });
        panel.find('.field-picker-group').each(function() {
            var visible = $(this).find('.field-picker-item:visible').length;
            $(this).toggle(visible > 0);
        });
    });

    $('#fieldPickerToggle').on('click', function(e) {
        e.stopPropagation();
        picker.toggleClass('open');
        panel.prop('hidden', !picker.hasClass('open'));
        $(this).attr('aria-expanded', picker.hasClass('open') ? 'true' : 'false');
        if (picker.hasClass('open')) { $('#fieldSearch').trigger('focus'); }
    });

    $('#fieldPickerCancel').on('click', function() {
        picker.removeClass('open');
        panel.prop('hidden', true);
    });

    $('#fieldPickerApply').on('click', function() {
        var selected = checks.filter(':checked').map(function() { return this.value; }).get();
        if (!selected.length) {
            window.alert('Please select at least one field.');
            return;
        }
        // The checkboxes already carry name="fields[]", so submitting the form
        // sends the selection; the controller stores it and cleans the URL.
        $('#filterForm').submit();
    });

    $(document).on('click', function(e) {
        if (!picker.is(e.target) && !picker.find(e.target).length) {
            picker.removeClass('open');
            panel.prop('hidden', true);
        }
    });

    updateCounts();
    syncGroupToggles();
});
</script>
@endpush
