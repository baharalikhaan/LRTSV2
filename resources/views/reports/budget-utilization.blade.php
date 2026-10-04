@extends('layouts.app')

@section('title', 'Budget Utilization Report - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-coins"></i> Budget Utilization Report</h1>
        <p>Overview of project budget spending and utilization across all research calls.</p>
    </div>
</div>

@if(session('error'))
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;margin-bottom:0;">
                <div class="filter-group">
                    <label>Research Call:</label>
                    <select name="program_id" id="programFilter">
                        <option value="">All Research Calls</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}">{{ $prog->program_title }} ({{ $prog->cycle->year ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search by project ID, name, LPI..." class="search-input">
                </div>
            </form>
            <button onclick="window.print()" class="btn-primary btn-sm" style="flex-shrink:0;">
                <i class="fas fa-print"></i> Print / PDF
            </button>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="budgetReportTable" style="font-size:12.5px;">
            <thead>
                <tr>
                    <th>Project ID</th>
                    <th>Project Name</th>
                    <th>LPI</th>
                    <th>Research Call</th>
                    <th style="text-align:right;">Budget (QAR)</th>
                    <th style="text-align:right;">Spent (QAR)</th>
                    <th style="text-align:right;">Committed (QAR)</th>
                    <th style="text-align:right;">Balance (QAR)</th>
                    <th>Utilization</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalBudget = 0;
                    $totalSpent = 0;
                    $totalCommitted = 0;
                    $totalBalance = 0;
                @endphp
                @forelse(\App\Models\ProjectBudget::with('project.program.grant', 'project.program.cycle', 'project.lpi')->orderByDesc('id')->get() as $b)
                    @php
                        $project = $b->project;
                        if (!$project) continue;
                        $oldId = $project->old_project_id ?? $project->id;
                        $lpiName = $project->lpi ? $project->lpi->name : '—';
                        $programTitle = $project->program->program_title ?? '—';
                        $cycleYear = $project->program->cycle->year ?? '—';
                        $grantCode = $project->program->grant->grant_code ?? '—';
                        $pct = $b->budget_amount > 0 ? round(($b->actual_exp_amount / $b->budget_amount) * 100, 1) : 0;
                        $pctColor = $pct < 50 ? '#f59e0b' : ($pct <= 90 ? '#22c55e' : '#2563eb');

                        $totalBudget += $b->budget_amount;
                        $totalSpent += $b->actual_exp_amount;
                        $totalCommitted += $b->commitment_amount;
                        $totalBalance += $b->available_balance;
                    @endphp
                    <tr>
                        <td style="font-weight:500;">{{ $oldId }}</td>
                        <td>
                            <div style="font-weight:500;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $project->title }}">{{ $project->title }}</div>
                        </td>
                        <td>{{ $lpiName }}</td>
                        <td>
                            <div style="font-weight:500;">{{ $programTitle }}</div>
                            <div style="font-size:11px;color:var(--ink-400,#8b8592);">{{ $cycleYear }} / {{ $grantCode }}</div>
                        </td>
                        <td style="text-align:right;font-weight:500;">{{ number_format($b->budget_amount, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($b->actual_exp_amount, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($b->commitment_amount, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($b->available_balance, 2) }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="flex:1;height:6px;background:#e5e7eb;border-radius:3px;overflow:hidden;min-width:60px;">
                                    <div style="height:100%;width:{{ min($pct, 100) }}%;background:{{ $pctColor }};border-radius:3px;"></div>
                                </div>
                                <span style="font-size:12px;font-weight:600;color:{{ $pctColor }};">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state py-4">
                                <i class="fas fa-coins"></i>
                                <h5>No Budget Data</h5>
                                <p>No budget records found. Sync budget data from the Budget Utilization page first.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($totalBudget > 0)
            <tfoot>
                <tr style="background:var(--sand-50,#faf7f0);font-weight:600;">
                    <td colspan="4" style="text-align:right;">Total</td>
                    <td style="text-align:right;">{{ number_format($totalBudget, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($totalSpent, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($totalCommitted, 2) }}</td>
                    <td style="text-align:right;">{{ number_format($totalBalance, 2) }}</td>
                    <td>
                        @php
                            $overallPct = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;
                            $overallColor = $overallPct < 50 ? '#f59e0b' : ($overallPct <= 90 ? '#22c55e' : '#2563eb');
                        @endphp
                        <span style="font-size:13px;font-weight:700;color:{{ $overallColor }};">{{ $overallPct }}%</span>
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
.fluent-alert{padding:10px 14px;border-radius:6px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.fluent-alert--error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
#budgetReportTable td,#budgetReportTable th{padding:8px 10px !important;vertical-align:middle;}
@media print{
    .no-print,.fluent-command-bar,.fluent-sidebar,.fluent-footer,.sidebar-overlay,.fluent-dropdown,.app-shell>aside,.btn-primary,.page-actions,.dataTables_wrapper .dt-buttons,.dataTables_filter,.dataTables_paginate,.dataTables_info,.dataTables_length,.icon-btn,.cmd-search,.role-switcher,.notif-dot,#notifDropdown,#userDropdown,.fluent-alert,#workflowModal,.modal,.modal-backdrop,.toastify{display:none!important;}
    .app-shell,.fluent-content,.fluent-content-body{margin:0!important;padding:0!important;max-width:100%!important;width:100%!important;display:block!important;background:#fff!important;border:none!important;box-shadow:none!important;overflow:visible!important;}
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#budgetReportTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'desc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [1,2,3,8] }],
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#programFilter').on('change', function() {
        var val = $(this).val();
        if (val) {
            table.column(3).search(val).draw();
        } else {
            table.column(3).search('').draw();
        }
    });
});
</script>
@endpush
