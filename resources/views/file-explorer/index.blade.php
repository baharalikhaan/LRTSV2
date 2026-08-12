@extends('layouts.app')

@section('title', 'File Downloads - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-folder-tree"></i> File Downloads</h1>
        <p>Browse and download project files — by research call or by project.</p>
    </div>
</div>

@if(session('error'))
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div style="display:grid; grid-template-columns:2fr 3fr; gap:20px; align-items:start;">

    {{-- Left: Research Call Browser --}}
    <div class="panel">
        <div class="panel-head">
            <div class="panel-actions">
                <h2 style="font-size:14px; margin:0;"><i class="fas fa-arrows-rotate" style="margin-right:6px;"></i> Research Calls</h2>
            </div>
        </div>
        <div class="panel-body p-0">
            @forelse($programs as $program)
                @php
                    $year      = $program->cycle->year ?? '—';
                    $grantCode = $program->grant->grant_code ?? '—';
                @endphp
                <div style="padding:12px 18px; {{ !$loop->last ? 'border-bottom:1px solid var(--ink-100, #eceef2);' : '' }}">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                        <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                            <div style="flex-shrink:0; width:38px; height:38px; border-radius:6px; background:var(--brand-500, #8d1b3d); display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; line-height:1;">
                                <span style="font-weight:700; font-size:12px;">{{ $year }}</span>
                                <span style="font-size:8px; opacity:.8;">{{ $grantCode }}</span>
                            </div>
                            <div style="min-width:0;">
                                <div style="font-weight:500; font-size:13px; color:var(--ink-800, #38333e); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $program->program_title }}</div>
                                <div style="font-size:11.5px; color:var(--ink-400, #8b8592);">{{ $program->project_count }} project{{ $program->project_count !== 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <a href="{{ route('file-explorer.download-program', $program->id) }}"
                           class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;white-space:nowrap;"
                           title="Download research call ZIP" data-bs-toggle="tooltip">
                            <i class="fas fa-file-zipper" style="font-size:11px;"></i> ZIP
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state py-4">
                    <i class="fas fa-calendar-xmark"></i>
                    <h5>No Research Calls</h5>
                    <p>No research calls found in the system.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right: Project Search --}}
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
                        <input type="text" id="tableSearch" placeholder="Search by project ID or title..." class="search-input">
                    </div>
                </form>
            </div>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table w-100" id="projectSearchTable" style="font-size:12.5px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Grant</th>
                        <th>Files</th>
                        <th class="text-center" style="min-width:70px;">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.fluent-alert{padding:10px 14px;border-radius:6px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.fluent-alert--error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#projectSearchTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("file-explorer.ajax-list") }}',
            data: function(d) {
                d.program_id = $('#programFilter').val();
            }
        },
        dom: 'rt<"bottom"lip>',
        columns: [
            { data: 'old_project_id', name: 'old_project_id', title: 'Project ID', width: '140px' },
            { data: 'title', name: 'title', title: 'Title', orderable: true, searchable: true },
            { data: 'grant', name: 'grant', title: 'Grant', orderable: false, searchable: false, width: '70px' },
            { data: 'files', name: 'files', title: 'Files', orderable: false, searchable: false, width: '70px' },
            { data: 'action', name: 'action', title: 'Action', orderable: false, searchable: false, className: 'text-center', width: '70px' },
        ],
        order: [[0, 'desc']],
        pageLength: 10,
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
