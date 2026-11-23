@extends('layouts.app')

@section('title', $pageTitle . ' - RTS')

@push('styles')
<style>
    /* The grading table is very wide (49 columns). Keep the horizontal scroll
       inside the table wrapper: allow the layout's flex items to shrink below
       the table's min-content width so the page itself never scrolls sideways. */
    .app-shell,
    .fluent-content,
    .fluent-content-body {
        min-width: 0;
    }

    .fluent-content-body {
        max-width: 100vw;
        overflow-x: hidden;
    }
</style>

{{-- Same grid treatment as the Project Details report: scroll pane, two pinned
     header rows, pinned first column, sand/maroon header rules. --}}
@include('reports.partials.report-table', ['tableId' => 'gradingDetailsTable', 'nowrapCells' => false])

<style>
    /* Keep rows compact: long reviewer text is clamped to four lines on screen
       (the full text is in the tooltip and in the Excel export). */
    .gd-cell {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ─── Column picker (same pattern as Project Details) ─── */
    .field-picker { position: relative; display: inline-block; }
    .field-picker-toggle {
        display: inline-flex; align-items: center; gap: 6px; background: #fff;
        border: 1px solid var(--ink-200, #e3e0e6); border-radius: 6px; padding: 5px 10px;
        font-size: 12px; font-weight: 500; color: var(--ink-700, #463f50); cursor: pointer; white-space: nowrap;
    }
    .field-picker-toggle:hover { border-color: var(--brand-500, #8d1b3d); }
    .field-picker-badge { background: var(--brand-500, #8d1b3d); color: #fff; border-radius: 10px; padding: 1px 7px; font-size: 11px; font-weight: 600; }
    .field-picker-caret { font-size: 9px; transition: transform .15s ease; }
    .field-picker.open .field-picker-caret { transform: rotate(180deg); }
    .field-picker-panel {
        position: absolute; z-index: 60; top: calc(100% + 6px); left: 0; width: 400px; max-width: 92vw;
        background: #fff; border: 1px solid var(--ink-200, #e3e0e6); border-radius: 10px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, .14); display: flex; flex-direction: column; overflow: hidden;
    }
    .field-picker-panel[hidden] { display: none !important; }
    .field-picker-head { padding: 10px; border-bottom: 1px solid var(--ink-100, #eee); display: flex; flex-direction: column; gap: 8px; }
    .field-picker-head .search-input {
        width: 100%; padding: 6px 9px; border: 1px solid var(--ink-200, #e3e0e6);
        border-radius: 6px; font-size: 12px; font-family: inherit;
    }
    .field-picker-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .field-picker-actions button {
        background: #fff; border: 1px solid var(--ink-200, #e3e0e6); border-radius: 5px;
        padding: 3px 9px; font-size: 11px; color: var(--ink-700, #463f50); cursor: pointer;
    }
    .field-picker-actions button:hover { border-color: var(--brand-500, #8d1b3d); color: var(--brand-500, #8d1b3d); }
    .field-picker-hint { font-size: 10.5px; color: var(--ink-400, #8b8592); }
    .field-picker-body { overflow-y: auto; max-height: 360px; padding: 6px 4px 6px 10px; }
    .field-picker-group { margin-bottom: 8px; }
    .field-picker-group-head {
        display: flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700;
        letter-spacing: .04em; text-transform: uppercase; color: var(--brand-500, #8d1b3d);
        padding: 5px 0 4px; cursor: pointer; border-bottom: 1px solid rgba(141, 27, 61, .18);
    }
    .field-picker-group-title { color: var(--brand-600, #7a1636); }
    .field-picker-group-head em { font-style: normal; font-weight: 600; margin-left: auto; color: var(--brand-500, #8d1b3d); }
    .field-picker-item { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: var(--ink-700, #463f50); padding: 2px 0; cursor: pointer; }
    .field-picker-item:hover .field-picker-item-label { color: var(--brand-500, #8d1b3d); }
    .field-picker-item input, .field-picker-group-head input { margin: 0; cursor: pointer; }
    .field-picker-item-label { flex: 1; }
    .field-picker-item-group { font-size: 10.5px; color: var(--ink-400, #8b8592); white-space: nowrap; }
    .field-picker-foot { border-top: 1px solid var(--ink-100, #eee); padding: 9px 10px; display: flex; align-items: center; gap: 8px; background: #fbfbfc; }
    .field-picker-status { font-size: 11.5px; color: var(--ink-500, #6f6a78); flex: 1; }
    .field-picker .btn-link { background: none; border: none; color: var(--ink-500, #6f6a78); font-size: 12px; cursor: pointer; padding: 4px 6px; }
    .field-picker .btn-link:hover { color: var(--ink-800, #2f2b38); }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h1>
            <i class="fas fa-star-half-stroke"></i>
            {{ $pageTitle }}
        </h1>
        <p>
            Reviewer feedback and recorded outcomes joined per project, for regular-grant and
            student-grant projects. Reviewers appear as “Reviewer 1 / 2 / …” only.
        </p>
    </div>
    <div class="page-actions" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('reports.grading-details-export', array_filter($filters, fn ($v) => !in_array($v, [null, '', 'all'], true))) }}"
           class="btn-primary btn-sm" style="text-decoration:none;">
            <i class="fas fa-file-excel"></i> Export to Excel
        </a>
    </div>
</div>

{{-- ── Column picker + filters ─────────────────────────────────────── --}}
<div class="panel" style="margin-bottom:16px;overflow:visible;">
    <div class="panel-body" style="padding:14px 16px;overflow:visible;">
        <form id="gradingFilterForm" method="GET"
              action="{{ route('reports.grading-details') }}">

            {{-- Column picker + category (own row) --}}
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:12px;">
                <div class="field-picker" id="fieldPicker">
                    <button type="button" class="field-picker-toggle" id="fieldPickerToggle" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-table-columns"></i>
                        <span>Columns</span>
                        <span class="field-picker-badge" id="fieldPickerCount">{{ $selectedCount }} / {{ $totalColumns }}</span>
                        <i class="fas fa-chevron-down field-picker-caret"></i>
                    </button>

                    <div class="field-picker-panel" id="fieldPickerPanel" hidden>
                        <div class="field-picker-head">
                            <input type="text" id="fieldSearch" class="search-input" placeholder="Search columns...">
                            <div class="field-picker-actions">
                                <button type="button" data-field-action="all">All</button>
                                <button type="button" data-field-action="none">None</button>
                                <button type="button" data-field-action="default">Recommended</button>
                            </div>
                            <div class="field-picker-hint">Columns are grouped by report section and follow the selected category; the raw <em>PR 1/PR 2/Final – Grading Table</em> and <em>Outcomes – Table</em> groups hold every column of those tables. The Excel export uses the same selection.</div>
                        </div>

                        <div class="field-picker-body" id="fieldPickerBody">
                            @foreach($allGroups as $groupLabel => $groupColumns)
                                <div class="field-picker-group" data-group="{{ $loop->index }}">
                                    <label class="field-picker-group-head">
                                        <input type="checkbox" class="field-group-toggle" data-group="{{ $loop->index }}">
                                        <span class="field-picker-group-title">{{ $groupLabel }}</span>
                                        <em>{{ count($groupColumns) }}</em>
                                    </label>
                                    @foreach($groupColumns as [$key, $label])
                                        <label class="field-picker-item"
                                               data-label="{{ \Illuminate\Support\Str::lower($label) }} {{ \Illuminate\Support\Str::lower($groupLabel) }}">
                                            <input type="checkbox" name="fields[]" value="{{ $key }}" class="field-check"
                                                {{ in_array($key, $selectedFields, true) ? 'checked' : '' }}>
                                            <span class="field-picker-item-label">{{ $label }}</span>
                                            <span class="field-picker-item-group">{{ $groupLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <div class="field-picker-foot">
                            <span class="field-picker-status" id="fieldPickerStatus">{{ $selectedCount }} of {{ $totalColumns }} columns selected</span>
                            <button type="button" class="btn-link" id="fieldPickerCancel">Cancel</button>
                            <button type="button" class="btn-primary btn-sm" id="fieldPickerApply">
                                <i class="fas fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:7px;">
                    <label for="category" style="font-size:10px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;">Category</label>
                    <select name="category" id="category"
                            style="padding:5px 8px;border:1px solid var(--ink-200);border-radius:6px;font-size:12px;background:#fff;min-width:150px;">
                        <option value="all" {{ ($filters['category'] ?? 'all') === 'all' ? 'selected' : '' }}>All projects</option>
                        <option value="regular" {{ ($filters['category'] ?? 'all') === 'regular' ? 'selected' : '' }}>Regular grants</option>
                        <option value="student" {{ ($filters['category'] ?? 'all') === 'student' ? 'selected' : '' }}>Student grants</option>
                    </select>
                </div>

                <span style="font-size:10.5px;color:var(--ink-400);">
                    Filters cascade (category → cycle → research call → grant); both categories use the same table and “All projects” exports both worksheets.
                </span>
            </div>

            {{-- Filters --}}
            <div style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;">
                <div>
                    <label for="cycle_id" style="display:block;font-size:10px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Cycle</label>
                    <select name="cycle_id" id="cycle_id" style="padding:5px 8px;border:1px solid var(--ink-200);border-radius:6px;font-size:12px;background:#fff;min-width:150px;">
                        <option value="">All cycles</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle->id }}" {{ (string) ($filters['cycle_id'] ?? '') === (string) $cycle->id ? 'selected' : '' }}>
                                {{ $cycle->title }} ({{ $cycle->year }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="program_id" style="display:block;font-size:10px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Research call</label>
                    <select name="program_id" id="program_id" style="padding:5px 8px;border:1px solid var(--ink-200);border-radius:6px;font-size:12px;background:#fff;min-width:220px;">
                        <option value="">All research calls</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ (string) ($filters['program_id'] ?? '') === (string) $program->id ? 'selected' : '' }}>
                                {{ $program->program_title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="grant_id" style="display:block;font-size:10px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Grant</label>
                    <select name="grant_id" id="grant_id" style="padding:5px 8px;border:1px solid var(--ink-200);border-radius:6px;font-size:12px;background:#fff;min-width:200px;">
                        <option value="">All grants</option>
                        @foreach($grants as $grant)
                            <option value="{{ $grant->id }}" {{ (string) ($filters['grant_id'] ?? '') === (string) $grant->id ? 'selected' : '' }}>
                                {{ $grant->grant_code }}{{ $grant->grant_name ? ' — ' . $grant->grant_name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="q" style="display:block;font-size:10px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Search</label>
                    <input type="text" name="q" id="q" value="{{ $filters['q'] ?? '' }}" placeholder="Project ID or title"
                           style="padding:5px 8px;border:1px solid var(--ink-200);border-radius:6px;font-size:12px;background:#fff;min-width:190px;">
                </div>

                <button type="submit" class="btn-primary btn-sm" style="height:30px;"><i class="fas fa-filter"></i> Apply</button>
                <a href="{{ route('reports.grading-details') }}"
                   style="font-size:11px;color:var(--ink-400);text-decoration:none;padding-bottom:7px;">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- ── Summary ────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
    <span class="pill info" style="font-size:11px;">Regular grants: <strong>{{ $totals['regular'] }}</strong></span>
    <span class="pill info" style="font-size:11px;">Student grants: <strong>{{ $totals['student'] }}</strong></span>
    <span style="font-size:11px;color:var(--ink-400);">
        Showing {{ count($rows) }} project(s) · {{ $selectedCount }} of {{ $totalColumns }} columns —
        generated {{ now()->format('d M Y, H:i') }}.
    </span>
</div>

@php
    $columnGroups = $visibleGroups;
@endphp

<div class="panel" style="margin-bottom:18px;">
    <div class="panel-head">
        <h2 style="font-size:13px;">
            <i class="fas fa-star-half-stroke"></i>
            Grading Details
            <span style="font-weight:400;color:var(--ink-400);">({{ count($rows) }})</span>
        </h2>
    </div>
    <div class="panel-body" style="padding:0;">
        @php
            $headerGroups = collect($columnGroups)
                ->map(fn ($cols, $label) => ['label' => $label, 'span' => count($cols)])
                ->values();
        @endphp
        <div class="report-table-scroll">
            <table class="fluent-table w-100" id="gradingDetailsTable"
                   data-category="{{ $filters['category'] ?? 'all' }}">
                <thead>
                    {{-- Column-name row; the grouped row is prepended after DataTables initialises. --}}
                    <tr>
                        @foreach($columnGroups as $groupColumns)
                            @foreach($groupColumns as [$key, $label])
                                <th>{{ $label }}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr style="border-bottom:1px solid var(--ink-100);">
                            @foreach($columnGroups as $groupColumns)
                                @foreach($groupColumns as [$key, $label])
                                    @php
                                        $value = $row[$key] ?? '';
                                        $identity = in_array($key, ['project_id', 'title', 'category', 'status', 'research_call', 'grant', 'cycle', 'lpi', 'lpi_email'], true);
                                        $isDecision = in_array($key, ['p1_decision', 'p2_decision', 'f_decision'], true);
                                        $isScore = in_array($key, ['f_grade_a', 'f_grade_b', 'f_grade_c', 'f_grade_d', 'f_total'], true);
                                    @endphp

                                    @if($identity)
                                        <td style="color:var(--ink-700);min-width:130px;">
                                            @if($key === 'project_id')
                                                <span style="font-weight:600;color:var(--brand-700);">{{ $value }}</span>
                                            @elseif($key === 'status')
                                                <span style="color:var(--ink-400);">{{ $value }}</span>
                                            @elseif($key === 'lpi_email' || $key === 'cycle')
                                                <span style="color:var(--ink-400);">{{ $value }}</span>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @else
                                        <td style="min-width:{{ in_array($key, ['p1_comments', 'p2_comments', 'f_comments', 'p1_analysis', 'p2_analysis', 'p1_recommendation', 'p2_recommendation'], true) ? '190' : '150' }}px;"
                                            @if($isScore && is_numeric($value)) data-order="{{ $value }}" @endif>
                                            @if($value !== '')
                                                <div class="gd-cell" title="{{ $value }}" style="white-space:pre-line;line-height:1.45;{{ $isDecision ? 'font-weight:600;color:var(--ink-800);' : 'color:var(--ink-600);' }}">{{ $value }}</div>
                                            @else
                                                <span style="color:var(--ink-300);">—</span>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ collect($columnGroups)->sum(fn ($c) => count($c)) }}" style="padding:26px;text-align:center;color:var(--ink-400);font-size:12px;">
                                No projects for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="font-size:11px;color:var(--ink-400);margin-top:6px;">
    Tip: use “Columns” to choose what this report shows (the selection is kept per category); the summary columns are
    selected by default, and the raw columns of <em>progress_report_grading</em>, <em>final_report_grading</em> and
    <em>project_outcomes</em> are available under their own groups (use “All” to add them). “Export to Excel” writes
    exactly the selected columns, keeping the two-level header.
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // ---- DataTable (same treatment as the Project Details report) ----
    var table = $('#gradingDetailsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        buttons: [],
        order: [],
        pageLength: 25
    });

    // The grouped header row is added after init so DataTables keeps its own
    // column-name row (that is where the sorting arrows live).
    var headerGroups = @json($headerGroups);
    var thead = $('#gradingDetailsTable thead');

    if (thead.length && headerGroups.length) {
        var rowHtml = '<tr class="group-header-row">';
        headerGroups.forEach(function (group) {
            rowHtml += '<th colspan="' + group.span + '" class="group-header-cell">' + group.label + '</th>';
        });
        rowHtml += '</tr>';
        thead.prepend(rowHtml);
    }

    function syncGroupHeaderOffset() {
        var groupCell = $('#gradingDetailsTable thead tr.group-header-row th').first();
        if (!groupCell.length) { return; }
        $('#gradingDetailsTable').css('--group-header-h', groupCell.outerHeight() + 'px');
    }

    syncGroupHeaderOffset();
    $(window).on('resize', syncGroupHeaderOffset);
    if (window.ResizeObserver) {
        new ResizeObserver(syncGroupHeaderOffset).observe(thead.get(0));
    }

    // ---- column picker ----
    var picker = $('#fieldPicker');
    var panel = $('#fieldPickerPanel');
    var checks = panel.find('.field-check');
    var total = checks.length;

    function updateCounts() {
        var n = checks.filter(':checked').length;
        $('#fieldPickerCount').text(n + ' / ' + total);
        $('#fieldPickerStatus').text(n + ' of ' + total + ' columns selected');
        $('#fieldPickerApply').prop('disabled', n === 0);
    }

    function syncGroupToggles() {
        panel.find('.field-picker-group').each(function () {
            var items = $(this).find('.field-check');
            var checked = items.filter(':checked').length;
            var toggle = $(this).find('.field-group-toggle');
            toggle.prop('checked', checked > 0 && checked === items.length);
            toggle.prop('indeterminate', checked > 0 && checked < items.length);
        });
    }

    panel.on('change', '.field-check', function () {
        updateCounts();
        syncGroupToggles();
    });

    panel.on('change', '.field-group-toggle', function () {
        var isChecked = $(this).is(':checked');
        $(this).closest('.field-picker-group').find('.field-check').prop('checked', isChecked);
        updateCounts();
        syncGroupToggles();
    });

    panel.on('click', '[data-field-action]', function () {
        var action = $(this).data('field-action');

        if (action === 'all') {
            checks.prop('checked', true);
        } else if (action === 'none') {
            checks.prop('checked', false);
        } else if (action === 'default') {
            checks.prop('checked', false);
            var recommended = @json($recommended);
            checks.each(function () {
                if (recommended.indexOf(this.value) !== -1) {
                    $(this).prop('checked', true);
                }
            });
        }

        updateCounts();
        syncGroupToggles();
    });

    $('#fieldSearch').on('input', function () {
        var q = $.trim($(this).val()).toLowerCase();

        panel.find('.field-picker-item').each(function () {
            var label = $(this).data('label') || '';
            $(this).toggle(q === '' || label.indexOf(q) !== -1);
        });

        panel.find('.field-picker-group').each(function () {
            $(this).toggle($(this).find('.field-picker-item:visible').length > 0);
        });
    });

    $('#fieldPickerToggle').on('click', function (e) {
        e.stopPropagation();
        picker.toggleClass('open');
        panel.prop('hidden', !picker.hasClass('open'));
        $(this).attr('aria-expanded', picker.hasClass('open') ? 'true' : 'false');
        if (picker.hasClass('open')) {
            $('#fieldSearch').trigger('focus');
        }
    });

    $('#fieldPickerCancel').on('click', function () {
        picker.removeClass('open');
        panel.prop('hidden', true);
        $(this).attr('aria-expanded', 'false');
    });

    $('#fieldPickerApply').on('click', function () {
        $('#gradingFilterForm').trigger('submit');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#fieldPicker').length) {
            picker.removeClass('open');
            panel.prop('hidden', true);
        }
    });

    // ---- cascading filters (category → cycle → research call → grant) ----
    // Each change reloads so the child lists are rebuilt from the parent; the
    // picker's unapplied edits are not submitted with the reload.
    $('#category, #cycle_id, #program_id, #grant_id').on('change', function () {
        panel.find('input[name="fields[]"]').prop('disabled', true);
        $('#gradingFilterForm').trigger('submit');
    });

    updateCounts();
    syncGroupToggles();
});
</script>
@endpush
