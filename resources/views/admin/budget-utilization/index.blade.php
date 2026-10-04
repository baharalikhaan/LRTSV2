@extends('layouts.app')

@section('title', 'Budget Utilization - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-coins"></i> Budget Utilization</h1>
        <p>Track project budget spending and send utilization reminders to LPIs.</p>
    </div>
</div>

@if(session('success'))
<div class="fluent-alert fluent-alert--success" style="margin-bottom:16px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- Sync Bar --}}
<div class="panel" style="margin-bottom:16px;">
    <div class="panel-body" style="padding:14px 20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--color-info,#dbeafe);color:#1d4ed8;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-sync-alt" style="font-size:14px;"></i>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--color-ink-800);">External API Sync</div>
                    <div style="font-size:11.5px;color:var(--color-ink-400);">
                        Last synced: @if($lastSync) {{ $lastSync->diffForHumans() }} ({{ $lastSync->format('d M Y, h:i A') }}) @else <span style="color:var(--color-ink-300);">Never</span> @endif
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('budget-utilization.sync') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-primary btn-sm" id="syncBtn" onclick="this.disabled=true;this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Syncing...';">
                    <i class="fas fa-sync-alt"></i> Sync from QU API
                </button>
            </form>
        </div>
    </div>
</div>

{{-- DataTable --}}
<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;">
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
                    <input type="text" id="tableSearch" placeholder="Search by project ID, name..." class="search-input">
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="budgetTable" style="font-size:12.5px;">
            <thead>
                <tr>
                    <th>Project ID</th>
                    <th>Project Name</th>
                    <th>LPI</th>
                    <th style="text-align:right;">Budget (QAR)</th>
                    <th style="text-align:right;">Spent (QAR)</th>
                    <th style="text-align:right;">Committed (QAR)</th>
                    <th style="text-align:right;">Balance (QAR)</th>
                    <th>Utilization</th>
                    <th class="text-center" style="min-width:90px;">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
.fluent-alert{padding:10px 14px;border-radius:6px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.fluent-alert--success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
.fluent-alert--error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
#budgetTable td,#budgetTable th{padding:6px 10px !important;}
#budgetTable td{text-align:right;}
#budgetTable td:first-child,#budgetTable td:nth-child(2),#budgetTable td:nth-child(3),#budgetTable td:nth-child(8),#budgetTable td:nth-child(9){text-align:left;}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#budgetTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("budget-utilization.ajax-list") }}',
            data: function(d) {
                d.program_id = $('#programFilter').val();
            }
        },
        columns: [
            { data: 'project_num', name: 'project_num', title: 'Project ID', width: '90px' },
            { data: 'project_name', name: 'project_name', title: 'Project Name', orderable: false, searchable: false },
            { data: 'lpi', name: 'lpi', title: 'LPI', orderable: false, searchable: false, width: '120px' },
            { data: 'budget', name: 'budget_amount', title: 'Budget', className: 'text-right', width: '110px' },
            { data: 'actual', name: 'actual_exp_amount', title: 'Spent', className: 'text-right', width: '110px' },
            { data: 'commitment', name: 'commitment_amount', title: 'Committed', className: 'text-right', width: '110px' },
            { data: 'balance', name: 'available_balance', title: 'Balance', className: 'text-right', width: '110px' },
            { data: 'utilization', name: 'utilization', title: 'Utilization', orderable: false, searchable: false, width: '140px' },
            { data: 'action', name: 'action', title: 'Action', orderable: false, searchable: false, className: 'text-center', width: '90px' },
        ],
        order: [[0, 'desc']],
        pageLength: 15,
        dom: 'rt<"bottom"lip>',
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#programFilter').on('change', function() {
        table.draw();
    });

    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
