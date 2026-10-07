@extends('layouts.app')

@section('title', 'User Activity - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-user-clock"></i> User Activity</h1>
        <p>Sign-ins (with IP and session duration) and the actions users performed.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.activity') }}" class="btn-secondary btn-sm"><i class="fas fa-rotate"></i> Refresh</a>
        <form action="{{ route('admin.activity.clear') }}" method="POST" class="d-inline"
              onsubmit="return confirm('Clear the entire activity log? This cannot be undone.');">
            @csrf
            <button type="submit" class="btn-secondary btn-sm" style="color:var(--danger);">
                <i class="fas fa-trash"></i> Clear Activity
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="fluent-alert fluent-alert--success" style="margin-bottom:16px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:16px;">
    <div class="panel"><div class="panel-body" style="text-align:center;">
        <div style="font-size:22px;font-weight:700;color:var(--brand-600);">{{ number_format($stats['logins_today']) }}</div>
        <div style="font-size:11px;font-weight:600;color:var(--ink-500);text-transform:uppercase;letter-spacing:.04em;">Sign-ins today</div>
    </div></div>
    <div class="panel"><div class="panel-body" style="text-align:center;">
        <div style="font-size:22px;font-weight:700;color:var(--success);">{{ number_format($stats['active_users_today']) }}</div>
        <div style="font-size:11px;font-weight:600;color:var(--ink-500);text-transform:uppercase;letter-spacing:.04em;">Active users today</div>
    </div></div>
    <div class="panel"><div class="panel-body" style="text-align:center;">
        <div style="font-size:22px;font-weight:700;color:var(--ink-700);">{{ number_format($stats['total']) }}</div>
        <div style="font-size:11px;font-weight:600;color:var(--ink-500);text-transform:uppercase;letter-spacing:.04em;">Total entries</div>
    </div></div>
</div>

<div class="panel">
    <div class="panel-head">
        <form method="GET" class="filter-bar" style="margin-bottom:0;width:100%;">
            <div class="filter-group">
                <label>User:</label>
                <select name="user_id" onchange="this.form.submit()">
                    <option value="">All users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Event:</label>
                <select name="event" onchange="this.form.submit()">
                    <option value="">All events</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev }}" {{ request('event') === $ev ? 'selected' : '' }}>{{ ucfirst($ev) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Search:</label>
                <input type="text" name="q" value="{{ request('q') }}" class="search-input" placeholder="route, IP, URL...">
            </div>
            <button type="submit" class="btn-secondary btn-sm"><i class="fas fa-search"></i> Filter</button>
            @if(request()->hasAny(['user_id','event','q']))
                <a href="{{ route('admin.activity') }}" class="btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
            @endif
        </form>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" style="font-size:12.5px;">
            <thead>
                <tr>
                    <th style="width:150px;">When</th>
                    <th>User</th>
                    <th style="width:80px;">Event</th>
                    <th>Activity</th>
                    <th style="width:120px;">IP</th>
                    <th style="width:90px;">Duration</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;color:var(--ink-500);">{{ $log->created_at?->format('d M Y H:i') }}</td>
                    <td>
                        @if($log->user)
                            <div style="font-weight:500;">{{ $log->user->name }}</div>
                            <div style="font-size:11px;color:var(--ink-400);">{{ $log->user->email }}</div>
                        @else
                            <span style="color:var(--ink-400);">—</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $pill = ['login'=>'success','logout'=>'inactive','view'=>'info','action'=>'primary'][$log->event] ?? 'ink';
                        @endphp
                        <span class="pill {{ $pill }}" style="font-size:10.5px;">{{ ucfirst($log->event) }}</span>
                    </td>
                    <td>
                        <div style="color:var(--ink-700);">{{ $log->description ?: '—' }}</div>
                        @if($log->url)
                            <div style="font-size:11px;color:var(--ink-400);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:420px;" title="{{ $log->url }}">
                                <code>{{ $log->method }}</code> {{ $log->url }}
                            </div>
                        @endif
                    </td>
                    <td style="font-family:ui-monospace,Consolas,monospace;font-size:11.5px;">{{ $log->ip ?: '—' }}</td>
                    <td>{{ $log->duration_for_humans ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state py-4">
                            <i class="fas fa-user-clock"></i>
                            <h5>No activity recorded</h5>
                            <p>User sign-ins and actions will appear here.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:14px;">{{ $logs->links() }}</div>
@endsection

@push('styles')
<style>
.fluent-alert--success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
</style>
@endpush
