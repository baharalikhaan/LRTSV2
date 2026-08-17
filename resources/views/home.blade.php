@extends('layouts.app')

@section('title', 'Dashboard - RTS')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h2><i class="fas fa-home"></i> Dashboard</h2>
    </div>
    <div class="panel-body">
        <div class="empty-state py-5">
            <i class="fas fa-user-circle"></i>
            <h5 class="mb-1">Welcome, {{ Auth::user()->name }}</h5>
            <p class="mb-0" style="color:var(--ink-500);">You are signed in as <strong>{{ $activeRole ?? 'User' }}</strong>. Use the menu above to navigate.</p>
        </div>
    </div>
</div>

@if(count($announcements ?? []) > 0)
<div class="panel" style="margin-top:18px;">
    <div class="panel-head">
        <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
    </div>
    <div class="panel-body">
        @foreach($announcements as $announcement)
        <div style="padding:8px 0; border-bottom:1px solid var(--ink-100);">
            <div style="display:flex; align-items:center; gap:8px;">
                <i class="fas fa-bullhorn" style="color:var(--gold-500); font-size:13px;"></i>
                <span style="font-weight:500; font-size:13px;">{{ $announcement->title }}</span>
                <span style="margin-left:auto; font-size:11px; color:var(--ink-400);">{{ $announcement->created_at ? $announcement->created_at->format('d M Y') : '' }}</span>
            </div>
            @if($announcement->body)
            <p style="font-size:12.5px; color:var(--ink-600); margin:4px 0 0 24px;">{{ \Illuminate\Support\Str::limit($announcement->body, 120) }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection