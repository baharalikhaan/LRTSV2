@extends('layouts.app')

@section('title', 'File Explorer')

@section('content')
<div class="file-explorer-page">

    {{-- Header --}}
    <div class="panel" style="margin-bottom:24px;">
        <div class="panel-body" style="padding:24px 28px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="flex-shrink:0; width:44px; height:44px; border-radius:10px; background:var(--color-brand-500); display:flex; align-items:center; justify-content:center; color:#fff; font-size:18px;">
                    <i class="fa-solid fa-folder-tree"></i>
                </div>
                <div>
                    <h1 style="font-size:20px; font-weight:700; color:var(--color-ink-900); margin:0;">File Explorer</h1>
                    <p style="font-size:13px; color:var(--color-ink-500); margin:2px 0 0;">Browse and download project files — by research call or by project</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">

        {{-- ── Left: Research Call Browser ── --}}
        <div class="panel">
            <div class="panel-head">
                <h2><i class="fa-solid fa-arrows-rotate"></i> Research Calls</h2>
            </div>
            <div class="panel-body p-0">
                @forelse($programs as $program)
                    @php
                        $year      = $program->cycle->year ?? '—';
                        $grantCode = $program->grant->grant_code ?? '—';
                    @endphp
                    <div style="padding:14px 20px; {{ !$loop->last ? 'border-bottom:1px solid var(--color-ink-100);' : '' }}">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:12px; min-width:0;">
                                <div style="flex-shrink:0; width:42px; height:42px; border-radius:8px; background:var(--color-brand-500); display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; line-height:1;">
                                    <span style="font-weight:700; font-size:13px;">{{ $year }}</span>
                                    <span style="font-size:9px; opacity:.8;">{{ $grantCode }}</span>
                                </div>
                                <div style="min-width:0;">
                                    <div style="font-weight:600; font-size:13.5px; color:var(--color-ink-800); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $program->program_title }}</div>
                                    <div style="font-size:12px; color:var(--color-ink-400);">{{ $program->project_count }} project{{ $program->project_count !== 1 ? 's' : '' }}</div>
                                </div>
                            </div>
                            <a href="{{ route('file-explorer.download-program', $program->id) }}"
                               class="btn btn-sm btn-ghost" title="Download research call ZIP">
                                <i class="fa-solid fa-file-zipper"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:40px 20px; color:var(--color-ink-400);">
                        <i class="fa-solid fa-calendar-xmark" style="font-size:28px; margin-bottom:8px; display:block; color:var(--color-ink-300);"></i>
                        <p style="margin:0;">No research calls found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── Right: Project Search ── --}}
        <div class="panel">
            <div class="panel-head">
                <h2><i class="fa-solid fa-magnifying-glass"></i> Search Projects</h2>
            </div>
            <div class="panel-body" style="padding:16px 20px;">
                <table id="projectSearchTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Grant</th>
                            <th>Files</th>
                            <th class="text-center">ZIP</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>

@push('styles')
<style>
    .fluent-alert {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .fluent-alert--error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#projectSearchTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("file-explorer.ajax-list") }}',
        columns: [
            { data: 'old_project_id', name: 'old_project_id', title: 'ID', width: '80px' },
            { data: 'title', name: 'title', title: 'Title', orderable: true, searchable: true },
            { data: 'grant', name: 'grant', title: 'Grant', orderable: false, searchable: false, width: '60px' },
            { data: 'files', name: 'files', title: 'Files', orderable: false, searchable: false, width: '70px' },
            { data: 'action', name: 'action', title: 'ZIP', orderable: false, searchable: false, className: 'text-center', width: '50px' },
        ],
        order: [[0, 'desc']],
        pageLength: 10,
    });
});
</script>
@endpush

@endsection
