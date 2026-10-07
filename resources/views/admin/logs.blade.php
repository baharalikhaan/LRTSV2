@extends('layouts.app')

@section('title', 'System Logs - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-bug"></i> System Logs</h1>
        <p>Recent application errors and warnings from the Laravel log.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.logs') }}" class="btn-secondary btn-sm"><i class="fas fa-rotate"></i> Refresh</a>
        <form action="{{ route('admin.logs.clear') }}" method="POST" class="d-inline"
              onsubmit="return confirm('Clear all log files? This cannot be undone.');">
            @csrf
            <button type="submit" class="btn-secondary btn-sm" style="color:var(--danger);">
                <i class="fas fa-trash"></i> Clear Logs
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="fluent-alert fluent-alert--success" style="margin-bottom:16px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="panel" style="margin-bottom:16px;">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;width:100%;">
            <span style="font-size:12.5px;color:var(--ink-500);">
                <i class="fas fa-file-lines"></i> <code>storage/logs/laravel.log</code>
                &middot; {{ number_format($size / 1024, 1) }} KB
                &middot; showing {{ count($entries) }} entr{{ count($entries) === 1 ? 'y' : 'ies' }}
            </span>
            <div class="filter-bar" style="margin-left:auto;margin-bottom:0;">
                <div class="filter-group">
                    <label>Level:</label>
                    <select onchange="window.location='{{ route('admin.logs') }}?level=' + this.value">
                        <option value="">All levels</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ $levelFilter === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$exists)
<div class="panel">
    <div class="panel-body">
        <div class="empty-state py-4">
            <i class="fas fa-file-circle-check"></i>
            <h5>No log file yet</h5>
            <p>Nothing has been logged, or the log file has been cleared.</p>
        </div>
    </div>
</div>
@elseif(empty($entries))
<div class="panel">
    <div class="panel-body">
        <div class="empty-state py-4">
            <i class="fas fa-circle-check"></i>
            <h5>No entries{{ $levelFilter ? ' at ' . $levelFilter : '' }}</h5>
            <p>There are no matching log entries in the recent portion of the file.</p>
        </div>
    </div>
</div>
@else
<div class="log-list">
    @foreach($entries as $entry)
    <details class="log-entry">
        <summary>
            <span class="log-level log-level--{{ strtolower($entry['level']) }}">{{ $entry['level'] }}</span>
            <span class="log-time">{{ $entry['time'] ?: '—' }}</span>
            <span class="log-msg">{{ \Illuminate\Support\Str::limit(trim($entry['message']), 160) }}</span>
        </summary>
        <div class="log-body">
            <pre>{{ trim($entry['message']) }}@if($entry['context'] !== '')

{{ trim($entry['context']) }}@endif</pre>
        </div>
    </details>
    @endforeach
</div>
@endif
@endsection

@push('styles')
<style>
.fluent-alert--success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
.log-list{display:flex;flex-direction:column;gap:8px;}
.log-entry{border:1px solid var(--ink-100,#eeedf0);border-radius:8px;background:#fff;overflow:hidden;}
.log-entry>summary{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;list-style:none;font-size:12.5px;}
.log-entry>summary::-webkit-details-marker{display:none;}
.log-entry[open]>summary{border-bottom:1px solid var(--ink-100,#eeedf0);background:var(--sand-50,#faf7f0);}
.log-level{flex:0 0 auto;font-size:10px;font-weight:700;letter-spacing:.04em;padding:2px 8px;border-radius:999px;text-transform:uppercase;}
.log-level--emergency,.log-level--alert,.log-level--critical,.log-level--error{background:#fdecec;color:#b3261e;}
.log-level--warning{background:#fef3c7;color:#92400e;}
.log-level--notice,.log-level--info{background:#e8f0fb;color:#1d4ed8;}
.log-level--debug{background:var(--ink-100,#eeedf0);color:var(--ink-500);}
.log-time{flex:0 0 auto;font-family:ui-monospace,Consolas,monospace;font-size:11px;color:var(--ink-400);}
.log-msg{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--ink-700);}
.log-body{padding:0;}
.log-body pre{margin:0;padding:12px 14px;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:11.5px;line-height:1.55;color:var(--ink-700);white-space:pre-wrap;word-break:break-word;max-height:420px;overflow:auto;background:#fff;}
</style>
@endpush
