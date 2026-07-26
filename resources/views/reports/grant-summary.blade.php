@extends('layouts.app')

@section('title', 'Grant Summary Report - RTS')

@section('content')
{{-- ============================================================ --}}
{{-- SCREEN CONTROLS (hidden on print)                          --}}
{{-- ============================================================ --}}
<div class="no-print" style="margin-bottom:12px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
        <form method="GET" action="{{ route('reports.grant-summary') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:4px;">
                <label for="cycle_id" style="font-size:11px;font-weight:500;color:var(--ink-600,#4c4553);white-space:nowrap;">Cycle:</label>
                <select name="cycle_id" id="cycle_id" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--ink-200,#d8d6dc);border-radius:4px;font-size:11px;font-family:inherit;color:var(--ink-700,#38333e);background:#fff;min-width:150px;">
                    <option value="">All Cycles</option>
                    @foreach($cycles as $cycle)
                    <option value="{{ $cycle->id }}" {{ request('cycle_id') == $cycle->id ? 'selected' : '' }}>
                        {{ $cycle->title }} ({{ $cycle->year }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;align-items:center;gap:4px;">
                <label for="category" style="font-size:11px;font-weight:500;color:var(--ink-600,#4c4553);white-space:nowrap;">Category:</label>
                <select name="category" id="category" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--ink-200,#d8d6dc);border-radius:4px;font-size:11px;font-family:inherit;color:var(--ink-700,#38333e);background:#fff;min-width:140px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;align-items:center;gap:4px;">
                <label for="program_status" style="font-size:11px;font-weight:500;color:var(--ink-600,#4c4553);white-space:nowrap;">Prog. Status:</label>
                <select name="program_status" id="program_status" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--ink-200,#d8d6dc);border-radius:4px;font-size:11px;font-family:inherit;color:var(--ink-700,#38333e);background:#fff;min-width:120px;">
                    <option value="">All</option>
                    <option value="with_active" {{ request('program_status') === 'with_active' ? 'selected' : '' }}>With Active</option>
                    <option value="all_inactive" {{ request('program_status') === 'all_inactive' ? 'selected' : '' }}>All Inactive</option>
                </select>
            </div>
            @if(request('cycle_id') || request('category') || request('program_status'))
            <a href="{{ route('reports.grant-summary') }}" style="font-size:10px;color:var(--ink-400,#8b8592);text-decoration:none;">&times; Clear</a>
            @endif
        </form>
        <button onclick="window.print()" style="display:inline-flex;align-items:center;gap:4px;background:var(--brand-500,#8d1b3d);color:#fff;border:none;border-radius:4px;padding:5px 10px;font-size:11px;font-weight:500;cursor:pointer;">
            <i class="fas fa-print" style="font-size:11px;"></i> Print / PDF
        </button>
    </div>
</div>

{{-- ============================================================ --}}
{{-- A4 PAGE WRAPPER                                            --}}
{{-- ============================================================ --}}
<div class="a4-report-page">

    {{-- ---- REPORT HEADER ---- --}}
    <div class="a4-report-header">
        <div class="a4-header-brand">
            <div class="a4-brand-mark">QU</div>
            <div class="a4-brand-text">
                <div class="a4-org-name">Qatar University</div>
                <div class="a4-org-sub">Research Tracking System</div>
            </div>
        </div>
        <div class="a4-header-divider"></div>
        <div class="a4-header-title-block">
            <div class="a4-report-title">Grant Summary Report</div>
            <div class="a4-report-desc">Aggregate report of grants with program and project breakdown</div>
        </div>
        <div class="a4-header-meta">
            <table class="a4-meta-table">
                <tr><td class="a4-meta-label">Generated:</td><td class="a4-meta-value">{{ now()->format('d M Y, h:i A') }}</td></tr>
                <tr><td class="a4-meta-label">Prepared by:</td><td class="a4-meta-value">RTS</td></tr>
            </table>
        </div>
    </div>

    {{-- ---- DETAIL TABLE ---- --}}
    <div class="a4-table-section">
        <table class="a4-data-table">
            <thead>
                <tr>
                    <th>Grant Code</th>
                    <th>Grant Name</th>
                    <th class="a4-cell-num">Active Research Calls</th>
                    <th class="a4-cell-num">Inactive Research Calls</th>
                    <th class="a4-cell-num">Total Projects</th>
                    <th class="a4-cell-num">Registered</th>
                    <th class="a4-cell-num">Pending</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grantData as $row)
                <tr>
                    <td><strong>{{ $row['grant']->grant_code }}</strong></td>
                    <td class="a4-cell-title">{{ $row['grant']->grant_name ?? 'Grant' }}</td>
                    <td class="a4-cell-num"><span class="a4-status a4-status-active">{{ $row['active_programs'] }}</span></td>
                    <td class="a4-cell-num"><span class="a4-status a4-status-inactive">{{ $row['inactive_programs'] }}</span></td>
                    <td class="a4-cell-num">{{ $row['total_projects'] }}</td>
                    <td class="a4-cell-num">{{ $row['registered_projects'] }}</td>
                    <td class="a4-cell-num">{{ $row['total_projects'] - $row['registered_projects'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="a4-cell-empty">No grants found.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                @php
                    $totals = [
                        'active' => $grantData->sum('active_programs'),
                        'inactive' => $grantData->sum('inactive_programs'),
                        'projects' => $grantData->sum('total_projects'),
                        'registered' => $grantData->sum('registered_projects'),
                    ];
                @endphp
                <tr>
                    <td colspan="2" class="a4-foot-label">Totals:</td>
                    <td class="a4-cell-num">{{ $totals['active'] }}</td>
                    <td class="a4-cell-num">{{ $totals['inactive'] }}</td>
                    <td class="a4-cell-num">{{ $totals['projects'] }}</td>
                    <td class="a4-cell-num">{{ $totals['registered'] }}</td>
                    <td class="a4-cell-num">{{ $totals['projects'] - $totals['registered'] }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ---- FOOTER ---- --}}
    <div class="a4-report-footer">
        <div class="a4-footer-line"></div>
        <div class="a4-footer-text">
            <span>&copy; {{ date('Y') }} Qatar University — RTS</span>
            <span class="a4-footer-sep">|</span>
            <span>Grant Summary Report</span>
            <span class="a4-footer-sep">|</span>
            <span>Page <span class="a4-page-number"></span></span>
            <span class="a4-footer-sep">|</span>
            <span>{{ now()->format('d M Y') }}</span>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* =============================================================
   A4 REPORT PAGE — Screen & Print Styles
   ============================================================= */

/* ---- Page wrapper: white A4-ish card on screen ---- */
.a4-report-page {
    max-width: 100%;
    margin: 0 auto;
    background: #fff;
    border: 1px solid var(--ink-100, #eeedf0);
    border-radius: 6px;
    box-shadow: var(--fluent-depth-4, 0 2px 4px rgba(22,19,26,.09), 0 0px 2px rgba(22,19,26,.07));
    padding: 18px 20px 14px;
    font-family: 'Inter', 'Segoe UI Variable', 'Segoe UI', ui-sans-serif, system-ui, sans-serif;
    color: var(--ink-800, #241f2a);
    font-size: 8.5px;
    line-height: 1.35;
}

/* ---- Report Header ---- */
.a4-report-header {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1.5px solid var(--brand-500, #8d1b3d);
}
.a4-header-brand {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.a4-brand-mark {
    width: 24px;
    height: 24px;
    background: var(--brand-500, #8d1b3d);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 800;
    font-size: 10px;
}
.a4-brand-text {
    line-height: 1.15;
}
.a4-org-name {
    font-size: 8px;
    font-weight: 700;
    color: var(--ink-800, #241f2a);
}
.a4-org-sub {
    font-size: 7px;
    color: var(--ink-400, #8b8592);
}
.a4-header-divider {
    width: 1px;
    height: 24px;
    background: var(--ink-200, #d8d6dc);
    flex-shrink: 0;
}
.a4-header-title-block {
    flex: 1;
    min-width: 0;
}
.a4-report-title {
    font-size: 11px;
    font-weight: 700;
    color: var(--ink-900, #16131a);
    margin: 0 0 1px;
    line-height: 1.2;
}
.a4-report-desc {
    font-size: 7px;
    color: var(--ink-400, #8b8592);
    margin: 0;
}
.a4-header-meta {
    flex-shrink: 0;
}
.a4-meta-table {
    border-collapse: collapse;
    font-size: 6.5px;
}
.a4-meta-table td {
    padding: 0 0 0 6px;
    line-height: 1.35;
}
.a4-meta-label {
    color: var(--ink-400, #8b8592);
    text-align: right;
    font-weight: 500;
}
.a4-meta-value {
    color: var(--ink-700, #38333e);
    font-weight: 600;
}

/* ---- Data Table ---- */
.a4-table-section {
    margin-bottom: 8px;
}
.a4-data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7px;
}
.a4-data-table th {
    background: var(--sand-50, #faf7f0);
    color: var(--ink-600, #4c4553);
    font-weight: 600;
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 3px 3px;
    text-align: left;
    white-space: nowrap;
    border: 0.25px solid var(--ink-200, #d8d6dc);
}
.a4-data-table td {
    padding: 2.5px 3px;
    vertical-align: middle;
    border: 0.25px solid var(--ink-100, #eeedf0);
}
.a4-data-table tbody tr:nth-child(even) {
    background: #fafafa;
}
.a4-data-table tfoot td {
    border-top: 0.5px solid var(--ink-300, #b4b0ba);
    background: var(--sand-50, #faf7f0);
    padding: 3px 3px;
    font-size: 7px;
    font-weight: 600;
}
.a4-foot-label {
    text-align: right;
}
.a4-cell-num {
    text-align: center;
    font-variant-numeric: tabular-nums;
}
.a4-cell-title {
    font-weight: 500;
    color: var(--ink-800, #241f2a);
}
.a4-cell-empty {
    text-align: center;
    padding: 16px;
    color: var(--ink-400, #8b8592);
}

/* ---- Status badges ---- */
.a4-status {
    display: inline-block;
    font-size: 6px;
    font-weight: 600;
    padding: 0 4px;
    border-radius: 2px;
    letter-spacing: 0.02em;
}
.a4-status-active {
    background: #e8f5e9;
    color: #2e7d32;
    border: 0.25px solid #a5d6a7;
}
.a4-status-inactive {
    background: #fafafa;
    color: #888;
    border: 0.25px solid #ddd;
}

/* ---- Footer ---- */
.a4-report-footer {
    margin-top: 10px;
}
.a4-footer-line {
    border-top: 0.5px solid var(--ink-200, #d8d6dc);
    margin-bottom: 4px;
}
.a4-footer-text {
    text-align: center;
    font-size: 6px;
    color: var(--ink-400, #8b8592);
}
.a4-footer-sep {
    margin: 0 4px;
    color: var(--ink-200, #d8d6dc);
}

/* =============================================================
   PRINT STYLES — Printer-friendly, hairline borders, light fonts
   ============================================================= */
@media print {
    .a4-report-page {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: #fff !important;
    }

    .a4-report-header {
        border-bottom: 0.5px solid #ccc !important;
    }
    .a4-brand-mark {
        background: none !important;
        color: #333 !important;
        font-weight: 500 !important;
    }
    .a4-header-divider {
        background: #ccc !important;
    }
    .a4-report-title {
        font-weight: 600 !important;
        font-size: 10px !important;
    }
    .a4-report-desc,
    .a4-org-sub {
        font-size: 6.5px !important;
        color: #888 !important;
    }
    .a4-org-name {
        font-size: 7.5px !important;
        font-weight: 600 !important;
        color: #444 !important;
    }
    .a4-meta-table td {
        font-size: 6px !important;
    }
    .a4-meta-label {
        color: #999 !important;
    }
    .a4-meta-value {
        color: #555 !important;
        font-weight: 500 !important;
    }

    .a4-data-table {
        font-size: 6.5px !important;
        font-weight: 400 !important;
    }
    .a4-data-table th {
        background: none !important;
        background-color: transparent !important;
        color: #444 !important;
        font-weight: 600 !important;
        font-size: 6px !important;
        border: 0.3px solid #bbb !important;
        border-bottom: 0.5px solid #999 !important;
        padding: 2.5px 3px !important;
    }
    .a4-data-table td {
        background: none !important;
        background-color: #fff !important;
        color: #333 !important;
        font-weight: 400 !important;
        font-size: 6.5px !important;
        border: 0.3px solid #ddd !important;
        padding: 2px 3px !important;
    }
    .a4-data-table tbody tr:nth-child(even) td {
        background: none !important;
        background-color: #fff !important;
    }
    .a4-data-table tfoot td {
        background: none !important;
        background-color: #fff !important;
        border-top: 0.5px solid #999 !important;
        font-weight: 600 !important;
        font-size: 6.5px !important;
        color: #444 !important;
    }

    .a4-status {
        background: none !important;
        border: none !important;
        padding: 0 !important;
        font-size: 6px !important;
        font-weight: 500 !important;
    }
    .a4-status-active {
        color: #2e7d32 !important;
    }
    .a4-status-inactive {
        color: #888 !important;
    }

    .a4-cell-title {
        font-weight: 500 !important;
        color: #333 !important;
    }
    .a4-cell-empty {
        color: #999 !important;
    }
    .a4-foot-label {
        color: #444 !important;
    }

    .a4-report-footer {
        page-break-inside: avoid;
    }
    .a4-footer-line {
        border-top: 0.3px solid #ccc !important;
    }
    .a4-footer-text {
        font-size: 5.5px !important;
        color: #999 !important;
    }
    .a4-footer-sep {
        color: #ccc !important;
    }

    .a4-page-number::after {
        counter-increment: page 1;
        content: counter(page);
    }

    .a4-data-table tr {
        page-break-inside: avoid;
    }
    .a4-data-table thead {
        display: table-header-group;
    }
    .a4-data-table tfoot {
        display: table-footer-group;
    }
}

.a4-page-number::after {
    content: "1";
}
</style>
@endpush

@push('scripts')
<script>
// Clean static report that prints as A4 — no DataTables needed
</script>
@endpush
