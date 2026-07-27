@extends('layouts.app')

@section('title', 'About RTS')

@section('content')
<div class="about-page">

    {{-- Hero Section --}}
    <div class="panel" style="margin-bottom:24px;">
        <div class="panel-body" style="padding:32px 28px; text-align:center;">
            <div style="display:flex; align-items:center; justify-content:center; margin:0 auto 18px; max-width:380px; background:var(--color-brand-500); border-radius:12px; padding:16px 20px;">
                <img src="{{ asset('images/research_logo.png') }}" alt="QU Research Logo" style="width:100%; height:auto;">
            </div>
            <h1 style="font-size:28px; font-weight:700; color:var(--color-ink-900); margin:0 0 6px;">Research Tracking System</h1>
            <p style="font-size:14px; color:var(--color-ink-500); margin:0 0 4px;">Qatar University — Office of Research & Graduate Studies</p>
            <div style="display:flex; align-items:center; justify-content:center; gap:10px; margin-top:10px;">
                <span class="pill success" style="font-size:12px;">v2.0.0</span>
                <span style="font-size:12px; color:var(--color-ink-400);">Released July 2026</span>
            </div>
        </div>
    </div>

    {{-- Description --}}
    <div class="panel" style="margin-bottom:24px;">
        <div class="panel-head">
            <h2><i class="fas fa-info-circle"></i> About the Application</h2>
        </div>
        <div class="panel-body" style="font-size:14px; line-height:1.7; color:var(--color-ink-600);">
            <p>RTS (Research Tracking System) is a comprehensive research project management platform developed for <strong>Qatar University's Office of Research & Graduate Studies</strong>. It streamlines the entire lifecycle of research projects from registration through final grading.</p>
            <p>The system supports three primary user roles — <strong>Administrators</strong>, <strong>LPIs (Lead Project Investigators)</strong>, and <strong>Reviewers</strong> — each with tailored dashboards and workflows. Built on Laravel with a QU × Fluent Design interface, RTS provides an enterprise-grade experience that reflects Qatar University's institutional identity.</p>
        </div>
    </div>

    {{-- Key Features --}}
    <div class="panel" style="margin-bottom:24px;">
        <div class="panel-head">
            <h2><i class="fas fa-star"></i> Key Features</h2>
        </div>
        <div class="panel-body" style="padding:20px 24px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                @php
                    $features = [
                        ['icon' => 'fa-solid fa-table-cells-large', 'color' => 'brand', 'title' => 'Role-Based Dashboards', 'desc' => 'Separate Admin, LPI, and Reviewer views with role-specific metrics and actions.'],
                        ['icon' => 'fas fa-diagram-project', 'color' => 'info', 'title' => 'Project Lifecycle Management', 'desc' => 'Track projects from registration, progress reporting, assignment, grading, to completion.'],
                        ['icon' => 'fas fa-check-double', 'color' => 'gold', 'title' => 'Reviewer Workflow', 'desc' => 'Proposal acceptance/rejection, grading submission, and report card generation.'],
                        ['icon' => 'fas fa-user-friends', 'color' => 'success', 'title' => 'Reviewer Assignment', 'desc' => 'Assign two reviewers per project with mutual-exclusion validation.'],
                        ['icon' => 'fas fa-tasks', 'color' => 'info', 'title' => 'Outcomes Management', 'desc' => 'Record and track project outcomes (publications, IP, students) via registration wizard.'],
                        ['icon' => 'fas fa-file-export', 'color' => 'maroon', 'title' => 'Export & Reporting', 'desc' => 'Built-in DataTables with CSV, Excel, PDF, and Print export on all tables.'],
                        ['icon' => 'fas fa-chart-line', 'color' => 'success', 'title' => 'Research Call & Pillar Analytics', 'desc' => 'Per-research-call and per-pillar breakdowns with status distribution across the lifecycle.'],
                        ['icon' => 'fas fa-bullhorn', 'color' => 'gold', 'title' => 'Announcement System', 'desc' => 'Role-targeted announcements with audience filtering (Admin, LPI, Reviewer).'],
                        ['icon' => 'fas fa-university', 'color' => 'brand', 'title' => 'QU × Fluent Design', 'desc' => 'Custom UI theme with maroon/sand/gold brand tokens, acrylic surfaces, and Fluent depth.'],
                    ];
                @endphp
                @foreach($features as $f)
                <div style="display:flex; gap:14px; padding:14px; background:var(--color-sand-50); border-radius:6px; border:1px solid var(--color-ink-100);">
                    <div style="flex-shrink:0; width:38px; height:38px; border-radius:6px; display:flex; align-items:center; justify-content:center; background:var(--color-{{ $f['color'] === 'brand' ? 'brand-100' : ($f['color'] === 'gold' ? 'gold-100' : ($f['color'] === 'info' ? 'info' : ($f['color'] === 'success' ? 'success' : 'brand-100'))) }}); color:var(--color-{{ $f['color'] === 'brand' ? 'brand-600' : ($f['color'] === 'gold' ? 'gold-600' : ($f['color'] === 'info' ? 'info' : ($f['color'] === 'success' ? 'success' : 'brand-600'))) }}); font-size:16px;">
                        <i class="{{ $f['icon'] }}"></i>
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:13.5px; color:var(--color-ink-800);">{{ $f['title'] }}</div>
                        <div style="font-size:12.5px; color:var(--color-ink-500); line-height:1.5; margin-top:2px;">{{ $f['desc'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Version History --}}
    <div class="panel" style="margin-bottom:24px;">
        <div class="panel-head">
            <h2><i class="fas fa-clock-rotate-left"></i> Version History</h2>
        </div>
        <div class="panel-body p-0">
            @foreach($versionHistory as $i => $ver)
            <div style="padding:18px 20px; {{ !$loop->last ? 'border-bottom:1px solid var(--color-ink-100);' : '' }}">
                <div style="display:flex; align-items:flex-start; gap:14px;">
                    <div style="flex-shrink:0; width:64px; height:64px; border-radius:6px; background:var(--color-brand-500); display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; box-shadow:var(--fluent-depth-2);">
                        <span style="font-weight:700; font-size:16px; line-height:1;">{{ $ver['version'] }}</span>
                        <span style="font-size:9px; opacity:.8;">{{ explode(' ', $ver['date'])[0] }}</span>
                    </div>
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span style="font-weight:600; font-size:15px; color:var(--color-ink-800);">{{ $ver['tagline'] }}</span>
                            <span style="font-size:11.5px; color:var(--color-ink-400);">{{ $ver['date'] }}</span>
                        </div>
                        <ul style="margin:8px 0 0; padding-left:18px; font-size:13px; color:var(--color-ink-600); line-height:1.7;">
                            @foreach($ver['changes'] as $change)
                            <li>{{ $change }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>


</div>
@endsection
