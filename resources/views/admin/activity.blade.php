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

<div class="activity-layout">
    <div class="activity-main">
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
            @php
                $sortLink = function ($key) use ($sort, $dir) {
                    $next = ($sort === $key && $dir === 'asc') ? 'desc' : 'asc';
                    return request()->fullUrlWithQuery(['sort' => $key, 'dir' => $next, 'page' => null]);
                };
                $arrow = function ($key) use ($sort, $dir) {
                    return $sort === $key ? ($dir === 'asc' ? '▲' : '▼') : '';
                };
            @endphp
            <thead>
                <tr>
                    <th style="width:150px;"><a class="th-sort" href="{{ $sortLink('when') }}">When {!! $arrow('when') !!}</a></th>
                    <th><a class="th-sort" href="{{ $sortLink('user') }}">User {!! $arrow('user') !!}</a></th>
                    <th style="width:80px;"><a class="th-sort" href="{{ $sortLink('event') }}">Event {!! $arrow('event') !!}</a></th>
                    <th><a class="th-sort" href="{{ $sortLink('activity') }}">Activity {!! $arrow('activity') !!}</a></th>
                    <th style="width:120px;"><a class="th-sort" href="{{ $sortLink('ip') }}">IP {!! $arrow('ip') !!}</a></th>
                    <th style="width:90px;"><a class="th-sort" href="{{ $sortLink('duration') }}">Duration {!! $arrow('duration') !!}</a></th>
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
    </div>{{-- /.activity-main --}}

    <aside class="activity-side">
        <div class="panel">
            <div class="panel-head">
                <h2 style="font-size:13px;margin:0;"><i class="fas fa-users" style="margin-right:6px;"></i> Active Users Today</h2>
                <span class="pill success" style="font-size:10.5px;">{{ $activeUsersToday->count() }}</span>
            </div>
            <div class="panel-body p-0">
                @forelse($activeUsersToday as $entry)
                <div class="active-user">
                    <div class="active-user-avatar">{{ strtoupper(substr($entry->user->name ?? '?', 0, 1)) }}</div>
                    <div class="active-user-info">
                        <div class="active-user-name">{{ $entry->user->name ?? 'Unknown user' }}</div>
                        <div class="active-user-meta">{{ $entry->user->email ?? '' }}</div>
                        <div class="active-user-meta">
                            <i class="fas fa-clock"></i> {{ $entry->created_at?->format('H:i') }}
                            &middot; <i class="fas fa-network-wired"></i> {{ $entry->ip ?: '—' }}
                        </div>
                    </div>
                    <div class="active-user-stats">
                        <div class="active-user-stat">
                            <span class="aus-num">{{ $activityCounts[$entry->user_id] ?? 0 }}</span>
                            <span class="aus-lbl">activities</span>
                        </div>
                        <div class="active-user-stat">
                            <span class="aus-num">{{ $timeSpentHuman[$entry->user_id] ?? '0s' }}</span>
                            <span class="aus-lbl">spent</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:24px 12px;">
                    <i class="fas fa-user-slash"></i>
                    <h5 style="font-size:13px;">No sign-ins today</h5>
                </div>
                @endforelse
            </div>
        </div>
    </aside>
</div>{{-- /.activity-layout --}}
@endsection

@push('styles')
<style>
.fluent-alert--success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}

/* Sortable column headers */
.th-sort { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.th-sort:hover { color: var(--brand-600,#7a1636); }

/* Two-column layout: main log (70%) + active users (30%) */
.activity-layout { display:grid; grid-template-columns:minmax(0,1fr) 30%; gap:16px; align-items:start; }
@media (max-width: 1000px) { .activity-layout { grid-template-columns:1fr; } }

.active-user { display:flex; align-items:center; gap:10px; padding:10px 14px; border-bottom:1px solid var(--ink-100,#eeedf0); }
.active-user:last-child { border-bottom:none; }
.active-user-avatar {
    width:32px; height:32px; border-radius:50%; flex-shrink:0;
    background:var(--brand-50,#fbeef1); color:var(--brand-600,#7a1636);
    display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700;
}
.active-user-info { flex:1; min-width:0; }
.active-user-name { font-size:12.5px; font-weight:600; color:var(--ink-800,#241f2a); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.active-user-meta { font-size:11px; color:var(--ink-400,#8b8592); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* Right side of the row: the two key metrics */
.active-user-stats { display:flex; gap:14px; flex-shrink:0; text-align:center; }
.active-user-stat { display:flex; flex-direction:column; line-height:1.15; }
.active-user-stat .aus-num { font-size:13px; font-weight:700; color:var(--brand-600,#7a1636); }
.active-user-stat .aus-lbl { font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:var(--ink-400,#8b8592); }
</style>
@endpush
