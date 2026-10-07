@php
    $appName = config('app.name', 'RTS');
    $currentRoute = request()->route() ? request()->route()->getName() : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $appName }} – Research Tracking System">

    <title>@yield('title', $appName . ' – RTS')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">

    {{-- Font Awesome 6 (free) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />

    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    {{-- QU × Fluent Design Theme --}}
    <link rel="stylesheet" href="{{ asset('css/qu-theme.css') }}">

    {{-- A4 Print Styles (global for all report views) --}}
    <style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 15mm 10mm;
        }
        body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Inter', 'Segoe UI Variable', 'Segoe UI', ui-sans-serif, system-ui, sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        .no-print,
        .fluent-command-bar,
        .fluent-sidebar,
        .fluent-footer,
        .sidebar-overlay,
        .fluent-dropdown,
        .app-shell > aside,
        .btn-primary,
        .page-actions,
        .dataTables_wrapper .dt-buttons,
        .dataTables_filter,
        .dataTables_paginate,
        .dataTables_info,
        .dataTables_length,
        .icon-btn,
        .role-switcher,
        .notif-dot,
        #notifDropdown,
        #userDropdown,
        .fluent-alert,
        #workflowModal,
        .modal,
        .modal-backdrop,
        .toastify {
            display: none !important;
        }
        .app-shell,
        .fluent-content,
        .fluent-content-body {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            display: block !important;
            background: #fff !important;
            border: none !important;
            box-shadow: none !important;
            overflow: visible !important;
        }
        .print-report-header { display: flex !important; align-items: center; gap: 14px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #8d1b3d; }
        .print-report-header .brand-mark { width: 36px; height: 36px; background: #8d1b3d; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 14px; letter-spacing: .03em; flex-shrink: 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .print-report-header .header-text { flex: 1; }
        .print-report-header .header-text .report-name { font-size: 16px; font-weight: 700; color: #1a1a1a; margin: 0; line-height: 1.3; }
        .print-report-header .header-text .report-sub { font-size: 10px; color: #666; margin: 0; }
        .print-report-header .header-meta { text-align: right; font-size: 8px; color: #888; flex-shrink: 0; }
        .print-report-header .header-meta div { line-height: 1.5; }
        .print-report-header .header-meta strong { color: #555; }
        .report-table { font-size: 8.5px !important; }
        .report-table th { font-size: 7.5px !important; padding: 3px 4px !important; background: #f2ead6 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .report-table td { font-size: 8px !important; padding: 2px 4px !important; }
        .report-table tbody tr:nth-child(even) { background: #faf7f0 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .report-table tfoot tr { background: #f2ead6 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .report-table th, .report-table td { border: 0.4px solid #aaa !important; }
        .report-table .pill { font-size: 7px !important; padding: 1px 4px !important; border-radius: 2px !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .pill.success { background: #e8f5e9 !important; color: #2e7d32 !important; border: 0.5px solid #a5d6a7 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .pill.inactive { background: #fafafa !important; color: #888 !important; border: 0.5px solid #ddd !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .pill.info { background: #e3f2fd !important; color: #1565c0 !important; border: 0.5px solid #90caf9 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .pill.warning { background: #fff8e1 !important; color: #f57f17 !important; border: 0.5px solid #ffe082 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        a { text-decoration: none !important; color: #8d1b3d !important; }
        .print-report-footer { display: block !important; text-align: center; color: #999; font-size: 7.5px; margin-top: 16px; border-top: 0.5px solid #ccc; padding-top: 6px; }
        .page-break { page-break-before: always; }
    }
    .print-report-header, .print-report-footer { display: none; }

    {{-- Page numbering via CSS counter --}}
    .print-report-footer .pageNumber::after { content: counter(page); }

    {{-- Elegant small tooltips --}}
    .tooltip-inner {
        font-size: 11px;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 4px;
        max-width: 200px;
        line-height: 1.4;
        background: var(--ink-800, #38333e);
        color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    .tooltip {
        --bs-tooltip-opacity: 0.92;
    }
    .tooltip-arrow::before {
        border-top-color: var(--ink-800, #38333e) !important;
    }
    </style>

    @stack('styles')
</head>
<body class="@auth @else app-layout--guest @endauth">

{{-- ============================================ --}}
{{-- AUTHENTICATED LAYOUT (Fluent Shell)         --}}
{{-- ============================================ --}}
@auth
@php
    $user = Auth::user();
    $isAdmin = $user->isAdmin();
    $isReviewer = $user->isReviewer();
    $overviewItems = [
        ['route' => 'home', 'icon' => 'fa-solid fa-table-cells-large', 'label' => 'Dashboard'],
    ];
    if (!$isAdmin) {
        $overviewItems[] = ['route' => 'announcements.index', 'icon' => 'fa-solid fa-bullhorn', 'label' => 'Announcements', 'can' => true];
    }
    $routeGroups = [
        '' => $overviewItems,
        'Research Calls' => [
            'research-calls'  => ['route' => 'programs.index', 'icon' => 'fa-solid fa-arrows-rotate', 'label' => 'Research Calls', 'can' => $isAdmin],
            'research-call-visibility' => ['route' => 'programs.visibility', 'icon' => 'fa-solid fa-eye-slash', 'label' => 'Show/Hide Research Calls', 'can' => $isAdmin],
            'cycle-progress' => ['route' => 'reports.cycle-progress', 'icon' => 'fa-solid fa-chart-bar', 'label' => 'Research Call Summary', 'can' => $isAdmin],
        ],
        'Projects' => [
            'projects-list' => ['route' => 'projects.available', 'icon' => 'fa-solid fa-diagram-project', 'label' => 'All Projects', 'can' => true],
            'my-assignments' => ['route' => 'projects.my-assignments', 'icon' => 'fa-solid fa-check-double', 'label' => 'My Assignments', 'can' => false],
            'graded-projects' => ['route' => 'gradedProjects', 'icon' => 'fa-solid fa-star', 'label' => 'Graded Projects', 'can' => false],
            'reviewer-assignment' => ['route' => 'projects.reviewer-assignment', 'icon' => 'fa-solid fa-user-check', 'label' => 'Reviewer Assignment', 'can' => $isAdmin],
            'extend-progress' => ['route' => 'projects.extend-progress', 'icon' => 'fa-solid fa-calendar-plus', 'label' => 'Extend Progress Report', 'can' => $isAdmin],
            'admin-upload' => ['route' => 'admin-upload.index', 'icon' => 'fa-solid fa-cloud-arrow-up', 'label' => 'Upload Reports', 'can' => $isAdmin],
            'pending-reviews' => ['route' => 'projects.pending-reviews', 'icon' => 'fa-solid fa-clock-rotate-left', 'label' => 'Evaluate Projects', 'can' => $isAdmin],
            'report-cards' => ['route' => 'reports.report-cards', 'icon' => 'fa-solid fa-file-lines', 'label' => 'Report Cards', 'can' => $isAdmin],
        ],
        'Administration' => [
            'users' => ['route' => 'users.index', 'icon' => 'fa-solid fa-users', 'label' => 'Users', 'can' => $isAdmin],
            'teams' => ['route' => 'teams.index', 'icon' => 'fa-solid fa-users-gear', 'label' => 'Team', 'can' => $isAdmin],
            'announcements-admin' => ['route' => 'announcements.index', 'icon' => 'fa-solid fa-bullhorn', 'label' => 'Announcements', 'can' => $isAdmin],
            'budget-utilization' => ['route' => 'budget-utilization.index', 'icon' => 'fa-solid fa-coins', 'label' => 'Budget Utilization', 'can' => $isAdmin],
            'reviewer-grading' => ['route' => 'reviewer-grading.index', 'icon' => 'fa-solid fa-star', 'label' => 'Reviewer Grading', 'can' => false],
            'send-email' => ['route' => 'admin.send-email', 'icon' => 'fa-solid fa-envelope', 'label' => 'Send Email', 'can' => $isAdmin],
            'email-templates' => ['route' => 'email-templates.index', 'icon' => 'fa-solid fa-file-lines', 'label' => 'Email Templates', 'can' => $isAdmin],
            'file-explorer' => ['route' => 'file-explorer.index', 'icon' => 'fa-solid fa-folder-tree', 'label' => 'File Downloads', 'can' => $isAdmin],
        ],
        'System Settings' => [
            'system-settings' => ['route' => 'admin.system-settings', 'icon' => 'fa-solid fa-cog', 'label' => 'System Settings', 'can' => $isAdmin],
            'cycle_configs' => ['route' => 'cycle-configs.index', 'icon' => 'fa-solid fa-calendar-alt', 'label' => 'Cycles', 'can' => $isAdmin],
            'grant-types' => ['route' => 'grant-types.index', 'icon' => 'fa-solid fa-trophy', 'label' => 'Grant Types', 'can' => $isAdmin],
            'pillars' => ['route' => 'pillars.index', 'icon' => 'fa-solid fa-columns', 'label' => 'Research Pillars', 'can' => $isAdmin],
            'colleges' => ['route' => 'colleges.index', 'icon' => 'fa-solid fa-university', 'label' => 'Colleges/Institutes', 'can' => $isAdmin],
        ],
    ];
@endphp

@php
    // ── Pending tasks for notification bell ─────────────────────────────
    $pendingTasks = [];
    $isLPI = $user->isLPI();
    $activeRole = $user->activeRole();

    if ($isAdmin) {
        // Admin: pending reviewer assignments
        $pendingAssignments = \App\Models\Project::whereNull('lpi_id')
            ->whereHas('statusHistories', function ($q) {
                $q->where('status', 'registered');
            })->count();
        if ($pendingAssignments > 0) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-user-check',
                'color' => '#d97706',
                'label' => $pendingAssignments . ' project' . ($pendingAssignments > 1 ? 's' : '') . ' pending reviewer assignment',
                'url'   => route('projects.reviewer-assignment'),
            ];
        }

        // Admin: pending gradings (projects with progress/final submitted but not graded)
        $pendingGradings = \App\Models\Project::whereHas('statusHistories', function ($q) {
            $q->whereIn('status', ['progress_added', 'progress_reviewed', 'final_added']);
        })->where('id', 'not in', function ($q) {
            $q->select('project_id')->from('status_histories')->where('status', 'Graded');
        })->count();
        if ($pendingGradings > 0) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-star',
                'color' => '#7c3aed',
                'label' => $pendingGradings . ' project' . ($pendingGradings > 1 ? 's' : '') . ' pending grading',
                'url'   => route('projects.available'),
            ];
        }
    }

    if ($isLPI && $activeRole === 'LPI') {
        // LPI: projects pending registration (no status history yet)
        $pendingRegistration = \App\Models\Project::where('lpi_id', $user->id)
            ->whereDoesntHave('statusHistories', function ($q) {
                $q->where('status', 'registered');
            })->count();
        if ($pendingRegistration > 0) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-user-plus',
                'color' => '#d97706',
                'label' => $pendingRegistration . ' project' . ($pendingRegistration > 1 ? 's' : '') . ' pending registration',
                'url'   => route('projects.available'),
            ];
        }

        // LPI: projects pending progress report upload (registered but no progress added)
        $pendingProgress = \App\Models\Project::where('lpi_id', $user->id)
            ->whereHas('statusHistories', function ($q) {
                $q->where('status', 'registered');
            })
            ->whereDoesntHave('statusHistories', function ($q) {
                $q->whereIn('status', ['progress_added', 'progress_reviewed', 'progress_rejected', 'final_added', 'Graded']);
            })->count();
        if ($pendingProgress > 0) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-clock',
                'color' => '#dc2626',
                'label' => $pendingProgress . ' project' . ($pendingProgress > 1 ? 's' : '') . ' pending progress report',
                'url'   => route('projects.available'),
            ];
        }

        // LPI: rejected reports needing resubmission
        $rejectedCount = \App\Models\Project::where('lpi_id', $user->id)
            ->whereHas('statusHistories', function ($q) {
                $q->whereIn('status', ['progress_rejected', 'progress_rejection_reviewed', 'final_rejected']);
            })
            ->whereDoesntHave('statusHistories', function ($q) {
                $q->whereIn('status', ['progress_added', 'final_added', 'Graded']);
            })->count();
        if ($rejectedCount > 0) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-exclamation-triangle',
                'color' => '#dc2626',
                'label' => $rejectedCount . ' rejected report' . ($rejectedCount > 1 ? 's' : '') . ' needing resubmission',
                'url'   => route('projects.available'),
            ];
        }
    }

    if ($isReviewer && $activeRole === 'Reviewer') {
        // Reviewer: projects assigned but not yet graded
        // NOTE: proposalstatus column removed — show all assigned as pending
        $pendingProposals = \App\Models\Project::whereHas('reviewers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->visibleProgram()->whereDoesntHave('statusHistories', function ($q) {
            $q->where('status', \App\Models\Project::STATUS_GRADED);
        })->count();
        if ($pendingProposals > 0) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-inbox',
                'color' => '#d97706',
                'label' => $pendingProposals . ' project' . ($pendingProposals > 1 ? 's' : '') . ' pending your review',
                'url'   => route('projects.available'),
            ];
        }

        // Reviewer: graded (completed)
        $pendingGrading = \App\Models\Project::whereHas('reviewers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->visibleProgram()
          ->whereDoesntHave('statusHistories', function ($q) {
              $q->where('status', \App\Models\Project::STATUS_GRADED);
          })->count();
        if ($pendingGrading > 0) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-star',
                'color' => '#7c3aed',
                'label' => $pendingGrading . ' accepted project' . ($pendingGrading > 1 ? 's' : '') . ' pending grading',
                'url'   => route('projects.available'),
            ];
        }
    }
@endphp

<div class="app-shell">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="fluent-sidebar" id="fluentSidebar">
        {{-- Brand --}}
        <div class="sidebar-brand" style="justify-content:center; padding:0; margin:0; gap:0;">
            <img src="{{ asset('images/logo.png') }}" alt="QU Logo" style="width:176px; height:auto; display:block; margin:0; padding:0;">
        </div>

        {{-- Navigation --}}
        @foreach($routeGroups as $sectionLabel => $items)
            @php
                $visibleItems = array_filter($items, function($i) { return $i['can'] ?? true; });
            @endphp
            @if(count($visibleItems))
                @php
                    $hasActive = false;
                    foreach ($visibleItems as $key => $item) {
                        if (request()->routeIs($item['route'] . '*') || request()->routeIs($key.'*')) {
                            $hasActive = true; break;
                        }
                    }
                @endphp
                <div class="sidebar-section">
                    @if($sectionLabel !== '')
                    <div class="sidebar-nav-label sidebar-section-toggle" data-section="{{ $sectionLabel }}">
                        <span>{{ $sectionLabel }}</span>
                        <i class="fas fa-chevron-down sidebar-section-arrow"></i>
                    </div>
                    @endif
                    <div class="sidebar-section-items {{ ($hasActive || $sectionLabel === '') ? '' : 'collapsed' }}">
                        @foreach($visibleItems as $key => $item)
                            @php
                                $isActive = request()->routeIs($item['route'] . '*') || request()->routeIs($key.'*');
                            @endphp
                            <a class="sidebar-nav-item {{ $isActive ? 'active' : '' }}"
                               href="{{ route($item['route']) }}">
                                <i class="{{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Footer --}}
        <div class="sidebar-footer-section">
            <div class="sidebar-user-chip">
                <div class="sidebar-avatar">
                    {{ collect(explode(' ', $user->name))->map(function($w) { return substr($w, 0, 1); })->take(2)->implode('') }}
                </div>
                <div class="sidebar-user-info">
                    <div class="name">{{ $user->name }}</div>
                    <div class="role">{{ $user->type }}</div>
                    <div class="email" style="font-size:10px; color:rgba(250,247,240,.4); word-break:break-all; line-height:1.3; margin-top:2px;">{{ $user->email }}</div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile sidebar overlay --}}
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    {{-- ============ MAIN CONTENT ============ --}}
    <div class="fluent-content">

        {{-- Command Bar (acrylic) --}}
        <div class="fluent-command-bar">
            {{-- Breadcrumb --}}
            <nav class="breadcrumb">
                <a href="{{ route('home') }}"><i class="fa-solid fa-house" style="font-size:13px;"></i></a>
                <span>›</span> <b>@yield('title', 'Dashboard')</b>
            </nav>

            {{-- Role Switcher (for composite role users) --}}
            @if($user->canSwitchRole())
                <div class="role-switcher">
                    <form id="roleSwitchForm" method="POST" action="{{ route('switch-role') }}">
                        @csrf
                        <input type="hidden" name="role" id="roleSwitchInput" value="{{ $user->activeRole() }}">
                        <div class="role-btn-group">
                            @foreach($user->subRoles() as $role)
                                <button type="button"
                                        class="role-toggle-btn {{ ($user->activeRole() === $role) ? 'active' : '' }}"
                                        onclick="setActiveRole('{{ $role }}')">
                                    {{ $role }}
                                </button>
                            @endforeach
                        </div>
                    </form>
                </div>
            @endif

        {{-- Notifications --}}
        <div class="icon-btn" id="notifToggle">
            <i class="fa-regular fa-bell"></i>
            <span class="notif-dot" id="notifDot" style="display:none;"></span>
        </div>

        {{-- About --}}
        <div class="icon-btn" id="aboutToggle">
            <i class="fa-regular fa-circle-question" style="font-size:19px;"></i>
        </div>

            {{-- User dropdown --}}
            <div class="icon-btn" id="userToggle">
                <i class="fa-regular fa-circle-user" style="font-size:20px;"></i>
            </div>

        </div>

        {{-- ============ CONTENT BODY ============ --}}
        <div class="fluent-content-body">

            {{-- Flash Messages (queued as toasts) --}}
            @if(session('success') || session('error') || session('warning') || session('info'))
            <script>
                window._flashMessages = window._flashMessages || [];
                @if(session('success'))
                window._flashMessages.push({type: 'success', message: @json(session('success'))});
                @endif
                @if(session('error'))
                window._flashMessages.push({type: 'error', message: @json(session('error'))});
                @endif
                @if(session('warning'))
                window._flashMessages.push({type: 'warning', message: @json(session('warning'))});
                @endif
                @if(session('info'))
                window._flashMessages.push({type: 'info', message: @json(session('info'))});
                @endif
            </script>
            @endif

            {{-- Page Content --}}
            @yield('content')

        </div>

        {{-- Footer — a direct child of .fluent-content (flex column) so it is
             always pushed to the bottom of the page --}}
        <div class="fluent-footer">
            <span>&copy; {{ date('Y') }} Qatar University. All rights reserved.</span>
            <div class="footer-right">
                 @auth
                 @if(auth()->user()->isAdmin())
                 <a href="{{ route('admin.activity') }}" title="User activity log"
                    style="color:var(--ink-400);font-size:12px;text-decoration:none;margin-right:14px;">
                     <i class="fas fa-user-clock"></i> Activity
                 </a>
                 <a href="{{ route('admin.logs') }}" title="System logs / issues"
                    style="color:var(--ink-400);font-size:12px;text-decoration:none;margin-right:14px;">
                     <i class="fas fa-bug"></i> Logs
                 </a>
                 @endif
                 @endauth
                 <a href="javascript:void(0)" onclick="document.getElementById('versionHistoryModal').style.display='flex'" style="color:var(--ink-400);cursor:pointer;font-size:12px;">v2.3.1</a>
            </div>
        </div>
    </div>
</div>

{{-- Logout form (hidden) --}}
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>

{{-- Simple Dropdown Menus --}}
<div id="notifDropdown" class="fluent-dropdown" style="display:none;">
    <div class="dropdown-header">
        <span>Notifications</span>
        <small id="notifCount" style="color:var(--color-ink-400);"></small>
    </div>
    <div id="notifList">
        <div class="dropdown-item disabled">
            <div class="dropdown-item-inner">
                <div class="text-center w-100 py-2" style="color:var(--color-ink-400);font-size:13px;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>
    @if(count($pendingTasks) > 0)
    <div style="border-top:1px solid var(--color-ink-100); padding:8px 0;">
        <div style="padding:6px 16px 4px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--color-ink-400);">
            <i class="fa-solid fa-list-check" style="margin-right:4px;"></i> Pending Tasks
        </div>
        @foreach($pendingTasks as $task)
        <a href="{{ $task['url'] }}" class="dropdown-item" style="padding:6px 16px;">
            <div class="dropdown-item-inner" style="gap:8px;">
                <i class="{{ $task['icon'] }}" style="color:{{ $task['color'] }}; font-size:13px; margin-top:1px;"></i>
                <span style="font-size:12.5px; color:var(--color-ink-700); line-height:1.3;">{{ $task['label'] }}</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif
    <div id="notifEmpty" class="dropdown-item disabled" style="display:none;">
        <div class="dropdown-item-inner">
            <div class="text-center w-100 py-3" style="color:var(--color-ink-400);font-size:13px;">
                <i class="fa-regular fa-bell-slash"></i> No new updates
            </div>
        </div>
    </div>
</div>

<div id="userDropdown" class="fluent-dropdown" style="display:none;">
    <div class="dropdown-header"><strong>{{ $user->name }}</strong><br><small>{{ $user->email }}</small></div>
    <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-gear"></i> Profile Settings</a>
    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<div id="aboutDropdown" class="fluent-dropdown" style="display:none;">
<div class="dropdown-header"><strong>About RTS</strong></div>
<a class="dropdown-item" href="{{ route('about.help') }}"><i class="fa-solid fa-circle-question"></i> Help Center</a>
    @if(auth()->user()->isAdmin() || auth()->user()->isLPI())
    <a class="dropdown-item" href="{{ route('about.help', ['tab' => 'lpi']) }}"><i class="fa-solid fa-user-tie"></i> LPI Manual</a>
    @endif
    @if(auth()->user()->isAdmin() || auth()->user()->isReviewer())
    <a class="dropdown-item" href="{{ route('about.help', ['tab' => 'reviewer']) }}"><i class="fa-solid fa-user-check"></i> Reviewer Manual</a>
    @endif
    <a class="dropdown-item" href="{{ route('about.team') }}"><i class="fa-solid fa-users"></i> Our Team</a>
</div>

{{-- Nationality setup modal — non-dismissable until nationality is set --}}
@php
    $nationalityUser = auth()->user();
    $needsNationality = $nationalityUser && is_null($nationalityUser->nationality_id);
@endphp
@if($needsNationality)
@php
    $nationalityOptions = \App\Models\Nationality::orderBy('name')->get();
@endphp
<div id="nationalityModal" style="display:flex;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.55);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:460px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.25);padding:28px 28px 24px;">
        <div style="text-align:center;margin-bottom:18px;">
            <div style="width:56px;height:56px;margin:0 auto 12px;border-radius:50%;background:var(--ink-50,#f5f4f2);display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-globe" style="font-size:24px;color:var(--brand-500,#6c4cf1);"></i>
            </div>
            <h3 style="margin:0;font-size:17px;font-weight:700;color:var(--ink-800,#1e1927);">Nationality Required</h3>
            <p style="margin:6px 0 0;font-size:13px;color:var(--ink-500,#5d6677);">Please select your nationality to continue. You can change it later from Profile Settings.</p>
        </div>
        <label for="nationalityModalSelect" style="display:block;font-size:12.5px;font-weight:600;color:var(--ink-700,#403a4d);margin-bottom:6px;">Nationality *</label>
        <select id="nationalityModalSelect" style="width:100%;padding:9px 12px;border:1px solid var(--ink-100,#eceef2);border-radius:8px;font-size:14px;color:var(--ink-800,#1e1927);background:#fff;margin-bottom:18px;">
            <option value="" selected disabled>-- Select Nationality --</option>
            @foreach($nationalityOptions as $nat)
            <option value="{{ $nat->id }}">{{ $nat->name }}</option>
            @endforeach
        </select>
        <button id="nationalityModalSave" type="button" style="width:100%;padding:10px 16px;border:none;border-radius:8px;font-size:14px;font-weight:600;color:#fff;background:var(--brand-500,#6c4cf1);cursor:pointer;">
            <i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>Save &amp; Continue
        </button>
        @if($errors->any())
        <p style="margin:10px 0 0;font-size:12px;color:var(--color-danger,#dc3545);text-align:center;">{{ $errors->first('nationality_id') ?: $errors->first() }}</p>
        @endif
    </div>
</div>
<script>
(function () {
    var saveBtn = document.getElementById('nationalityModalSave');
    var select = document.getElementById('nationalityModalSelect');
    var busy = false;
    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            if (!select.value) {
                if (window.showToast) { window.showToast('warning', 'Please select your nationality.'); }
                else { alert('Please select your nationality.'); }
                select.focus();
                return;
            }
            if (busy) return;
            busy = true;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i>Saving...';
            $.ajax({
                url: '{{ route('profile.nationality') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    nationality_id: select.value
                },
                success: function (resp) {
                    if (resp && resp.success) {
                        if (window.showToast) {
                            window.showToast('success', resp.message || 'Nationality updated successfully.');
                            setTimeout(function () { location.reload(); }, 600);
                        } else {
                            location.reload();
                        }
                    } else {
                        busy = false;
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>Save &amp; Continue';
                        if (window.showToast) { window.showToast('error', (resp && resp.message) || 'Could not save your nationality.'); }
                    }
                },
                error: function () {
                    busy = false;
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>Save &amp; Continue';
                    if (window.showToast) { window.showToast('error', 'Network error. Please try again.'); }
                    else { alert('Network error. Please try again.'); }
                }
            });
        });
    }
})();
</script>
@endif

@endauth

{{-- ============================================ --}}
{{-- GUEST LAYOUT --}}
{{-- ============================================ --}}
@guest
    <div class="guest-layout">
        @yield('content')
    </div>
@endguest

{{-- ============================================ --}}
{{-- SCRIPTS --}}
{{-- ============================================ --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

{{-- Bootstrap JS (for modal functionality only, no styles) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

{{-- Chart.js (dashboard charts) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle for mobile (overlay)
    window.toggleSidebar = function() {
        var sidebar = document.getElementById('fluentSidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var isOpen = sidebar.classList.contains('sidebar-open');
        if (isOpen) {
            sidebar.classList.remove('sidebar-open');
            overlay.style.display = 'none';
        } else {
            sidebar.classList.add('sidebar-open');
            overlay.style.display = 'block';
        }
    };

    // Simple dropdown toggles
    function toggleDropdown(id) {
        var dd = document.getElementById(id);
        if (dd.style.display === 'block') {
            dd.style.display = 'none';
        } else {
            document.querySelectorAll('.fluent-dropdown').forEach(function(d) { d.style.display = 'none'; });
            dd.style.display = 'block';
        }
    }

    // Role switcher — segmented button group
    // Attached to window because inline onclick handlers resolve in global
    // scope, while this script block runs inside DOMContentLoaded.
    window.setActiveRole = function(role) {
        var input = document.getElementById('roleSwitchInput');
        var form = document.getElementById('roleSwitchForm');
        if (!input || !form || input.value === role) {
            return;
        }
        // Immediate visual feedback before the page reloads.
        document.querySelectorAll('.role-toggle-btn').forEach(function(btn) {
            btn.classList.toggle('active', btn.getAttribute('onclick').indexOf("'" + role + "'") !== -1);
        });
        input.value = role;
        form.submit();
    };

    // Sidebar collapsable sections
    function toggleSection(header) {
        var container = header.parentElement;
        var items = container.querySelector('.sidebar-section-items');
        var isCollapsed = items.classList.contains('collapsed');
        if (isCollapsed) {
            items.classList.remove('collapsed');
            header.classList.remove('collapsed');
        } else {
            items.classList.add('collapsed');
            header.classList.add('collapsed');
        }
        try { sessionStorage.setItem('sidebar_' + header.getAttribute('data-section'), isCollapsed ? 'open' : 'collapsed'); } catch(e) {}
    }
    document.querySelectorAll('.sidebar-section-toggle').forEach(function(toggle) {
        var section = toggle.getAttribute('data-section');
        var items = toggle.parentElement.querySelector('.sidebar-section-items');
        try {
            var saved = sessionStorage.getItem('sidebar_' + section);
            if (saved === 'collapsed') {
                items.classList.add('collapsed');
                toggle.classList.add('collapsed');
            }
        } catch(e) {}
        toggle.addEventListener('click', function() { toggleSection(this); });
    });

    var notifBtn = document.getElementById('notifToggle');
    var userBtn = document.getElementById('userToggle');
    var aboutBtn = document.getElementById('aboutToggle');
    if (notifBtn) notifBtn.addEventListener('click', function(e) { e.stopPropagation(); toggleDropdown('notifDropdown'); });
    if (userBtn) userBtn.addEventListener('click', function(e) { e.stopPropagation(); toggleDropdown('userDropdown'); });
    if (aboutBtn) aboutBtn.addEventListener('click', function(e) { e.stopPropagation(); toggleDropdown('aboutDropdown'); });

    // ─── Fetch Notifications via AJAX ──────────────────────────────────────
    function fetchNotifications() {
        fetch('{{ route('notifications') }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var list = document.getElementById('notifList');
            var dot = document.getElementById('notifDot');
            var count = document.getElementById('notifCount');

            if (!list) return;

            if (data.count > 0) {
                dot.style.display = '';
                if (count) count.textContent = '(' + data.count + ' new)';
                list.style.display = '';
                var empty = document.getElementById('notifEmpty');
                if (empty) empty.style.display = 'none';

                list.innerHTML = '';
                data.announcements.forEach(function(a) {
                    var iconMap = {
                        'general': 'fa-info-circle',
                        'important': 'fa-exclamation-triangle',
                        'deadline': 'fa-clock',
                        'update': 'fa-sync-alt'
                    };
                    var icon = iconMap[a.type] || 'fa-info-circle';
                    var colorMap = {
                        'general': 'var(--color-info)',
                        'important': 'var(--color-danger)',
                        'deadline': 'var(--color-warning)',
                        'update': 'var(--color-ink-500)'
                    };
                    var color = colorMap[a.type] || 'var(--color-info)';

                    var item = document.createElement('a');
                    item.href = a.url;
                    item.className = 'dropdown-item';
                    item.innerHTML =
                        '<div class="dropdown-item-inner" style="align-items:flex-start;">' +
                            '<i class="fa-solid ' + icon + '" style="color:' + color + ';margin-top:2px;"></i>' +
                            '<div>' +
                                '<div style="font-weight:500;font-size:13px;">' + escapeHtml(a.title) + '</div>' +
                                '<div style="font-size:12px;color:var(--color-ink-400);line-height:1.3;">' + escapeHtml(a.message) + '</div>' +
                                '<div style="font-size:10px;margin-top:3px;display:flex;gap:6px;align-items:center;">' +
                                    '<small style="color:var(--color-ink-400);">' + a.created_at + '</small>' +
                                    '<span class="pill" style="font-size:9px;padding:1px 5px;background:var(--color-brand-100);color:var(--color-brand-600);">Notification</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
                    list.appendChild(item);
                });
            } else {
                dot.style.display = 'none';
                if (count) count.textContent = '';
                list.innerHTML = '';
                list.style.display = 'none';
                // Show "No new updates" only if there are also no pending tasks
                var empty = document.getElementById('notifEmpty');
                var hasPendingTasks = document.querySelector('#notifDropdown [style*="Pending Tasks"]') || document.querySelector('#notifDropdown .fa-list-check');
                if (empty && !hasPendingTasks) {
                    empty.style.display = '';
                }
            }
        })
        .catch(function() {
            var list = document.getElementById('notifList');
            if (list) {
                list.innerHTML =
                    '<div class="dropdown-item disabled">' +
                        '<div class="dropdown-item-inner">' +
                            '<div class="text-center w-100 py-2" style="color:var(--color-danger);font-size:13px;">' +
                                '<i class="fa-solid fa-exclamation-circle"></i> Failed to load notifications' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Fetch on load
    fetchNotifications();

    // Show bell dot if there are pending tasks (static Blade content)
    var pendingTasksSection = document.querySelector('#notifDropdown [style*="Pending Tasks"]') || document.querySelector('#notifDropdown .fa-list-check');
    if (pendingTasksSection) {
        var dot = document.getElementById('notifDot');
        if (dot) dot.style.display = '';
    }

    // Refresh every 60 seconds
    setInterval(fetchNotifications, 60000);

    document.addEventListener('click', function() {
        document.querySelectorAll('.fluent-dropdown').forEach(function(d) { d.style.display = 'none'; });
    });

    // DataTable defaults with Buttons + search
    if ($.fn.dataTable) {
        $.extend($.fn.dataTable.defaults, {
            dom: '<"dt-toolbar"<"dt-buttons"B><"dt-search"f>>' +
                 '<"dt-table-wrap"t>' +
                 '<"dt-bottom"<"dt-info"i><"dt-paginate"p>>',
            buttons: [
                { extend: 'copy', text: '<i class="fa-solid fa-copy"></i> Copy', className: 'btn btn-dt' },
                { extend: 'csv', text: '<i class="fa-solid fa-file-csv"></i> CSV', className: 'btn btn-dt' },
                { extend: 'excel', text: '<i class="fa-solid fa-file-excel"></i> Excel', className: 'btn btn-dt' },
                { extend: 'pdf', text: '<i class="fa-solid fa-file-pdf"></i> PDF', className: 'btn btn-dt' },
                { extend: 'print', text: '<i class="fa-solid fa-print"></i> Print', className: 'btn btn-dt' }
            ],
            language: {
                search: 'Search:',
                searchPlaceholder: 'Search records…',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                infoEmpty: 'No records available',
                infoFiltered: '(filtered from _MAX_ total records)',
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left"></i>',
                    next: '<i class="fa-solid fa-chevron-right"></i>'
                }
            },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            initComplete: function() {
                // Style the search input
                var searchInput = $(this).closest('.dataTables_wrapper').find('.dataTables_filter input');
                searchInput.attr('placeholder', 'Search records…');
            }
        });
    }
});
</script>

{{-- ============================================ --}}
{{-- GLOBAL WORKFLOW FUNCTIONS                   --}}
{{-- ============================================ --}}
<script>
/**
 * Open a workflow modal for a given action.
 * @param {number} projectId
 * @param {string} action - 'progress', 'assign', 'review', 'report-card'
 * @param {string|null} size - optional size: 'sm', 'lg', 'xl', or null for default (540px)
 */
function openWorkflowModal(projectId, action, size, queryString) {
    // Cleanup any previous modals
    $('#workflowModal').remove();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');

    // Determine modal width based on size parameter
    var modalWidth = '420px';
    if (size === 'lg') {
        modalWidth = '720px';
    } else if (size === 'xl') {
        modalWidth = '900px';
    } else if (size === 'sm') {
        modalWidth = '360px';
    }

    const modal = $('<div class="modal fade" id="workflowModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">'
        + '<div class="modal-dialog modal-dialog-centered" role="document" style="max-width:' + modalWidth + ';">'
        + '<div class="modal-content" style="border:none;border-radius:8px;overflow:hidden;">'
        + '<div class="text-center py-5">'
        + '<i class="fas fa-spinner fa-spin" style="font-size:28px;color:var(--color-brand-500);"></i>'
        + '<p class="mt-3" style="font-size:13px;color:var(--color-ink-500);">Loading…</p>'
        + '</div></div></div></div>');

    $('body').append(modal);
    modal.modal('show');

    var url = '/workflow/modal/' + action + '/' + projectId;
    if (queryString) {
        url += '?' + queryString;
    }

    $.get(url, function(res) {
        if (res.html) {
            modal.find('.modal-content').html(res.html);
        } else if (res.error) {
            modal.find('.modal-content').html('<div class="p-4 text-center"><div class="alert alert-danger mb-0">' + res.error + '</div></div>');
        }
    }).fail(function(xhr) {
        const err = xhr.responseJSON;
        modal.find('.modal-content').html(
            '<div class="p-4 text-center"><div class="alert alert-danger mb-0">'
            + (err?.error || 'Failed to load action.')
            + '</div></div>'
        );
    });
}

/**
 * Submit assignment of a single reviewer from the assign modal.
 */
function submitAssignment() {
    const btn = document.getElementById('saveAssignBtn');
    const errorDiv = document.getElementById('assignError');
    const projectId = document.querySelector('input[name="project_id"]').value;
    const reviewerSelect = document.getElementById('reviewer_1');

    if (!errorDiv || !reviewerSelect) return;

    errorDiv.style.display = 'none';

    if (!reviewerSelect.value) {
        errorDiv.textContent = 'Please select a reviewer.';
        errorDiv.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning...';

    var csrf = document.querySelector('input[name="_token"]');
    if (!csrf) {
        errorDiv.textContent = 'CSRF token missing. Please refresh the page.';
        errorDiv.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Assign Reviewer';
        return;
    }

    var data = new FormData();
    data.append('_token', csrf.value);
    data.append('project_id', projectId);
    data.append('reviewer_ids[]', reviewerSelect.value);

    fetch('/workflow/assign-reviewers', {
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        body: data
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            $('#workflowModal').modal('hide');
            showToast('success', 'Reviewer assigned successfully!');
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showToast('error', data.error || 'Failed to assign reviewer.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Assign Reviewer';
        }
    })
    .catch(function() {
        showToast('error', 'Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Assign Reviewer';
    });
}

/**
 * Submit accept/reject proposal decision from the workflow modal.
 * This handles file upload via FormData.
 */
function submitProposalDecision() {
    const form = document.getElementById('proposalDecisionForm');
    if (!form) return;

    const btn = document.getElementById('submitDecisionBtn');
    const errorDiv = document.getElementById('proposalError');
    const errorText = document.getElementById('proposalErrorText');
    const decisionError = document.getElementById('decisionError');

    errorDiv.style.display = 'none';
    if (decisionError) decisionError.style.display = 'none';

    // Validate decision selection using hidden input
    const decisionValue = document.getElementById('decisionValue');
    if (!decisionValue || !decisionValue.value) {
        if (decisionError) {
            decisionError.textContent = 'Please select Accept or Reject.';
            decisionError.style.display = 'block';
        }
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    const rId = form.querySelector('input[name="r_id"]').value;
    const projectId = form.querySelector('input[name="project_id"]').value;
    var csrf = document.querySelector('meta[name="csrf-token"]');

    var data = new FormData();
    data.append('_token', csrf ? csrf.content : '');
    data.append('project_id', projectId);
    data.append('r_id', rId);
    data.append('accept', decisionValue.value);
    // Include the optional rejection reason when present.
    var reasonEl = form.querySelector('textarea[name="reject_reason"]');
    if (reasonEl && reasonEl.value.trim()) {
        data.append('reject_reason', reasonEl.value.trim());
    }

    fetch('/workflow/submit-decision', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.content : ''
        },
        body: data
    })
    .then(function(res) { return res.json(); })
    .then(function(response) {
        if (response.success) {
            $('#workflowModal').modal('hide');
            showToast('success', response.message);
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showToast('error', response.error || 'Failed to submit decision.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Decision';
        }
    })
    .catch(function() {
        showToast('error', 'Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Decision';
    });
}

/**
 * Record a status transition via AJAX and close the modal.
 * Also reloads the current page on success (or auto-updates).
 */
function recordStatus(projectId, action) {
    $.ajax({
        url: '/workflow/transition',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            project_id: projectId,
            action: action
        },
        success: function(res) {
            if (res.success) {
                $('#workflowModal').modal('hide');
                // Flash a success message, then reload
                Toastify({
                    text: res.message || 'Status updated successfully.',
                    duration: 3000,
                    gravity: 'bottom',
                    position: 'right',
                    style: { background: 'var(--color-success, #1f8a5f)' }
                }).showToast();
                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
        },
        error: function(xhr) {
            const err = xhr.responseJSON;
            Toastify({
                text: err?.error || 'Failed to update status.',
                duration: 4000,
                gravity: 'bottom',
                position: 'right',
                style: { background: 'var(--color-danger, #b3261e)' }
            }).showToast();
        }
    });
}

/**
 * Centralized toast notification — bottom-right corner via Toastify.
 * Usage: showToast('success', 'Message'); showToast('error', 'Message');
 */
function showToast(type, message) {
    var bg;
    switch (type) {
        case 'success': bg = '#1f8a5f'; break;
        case 'error':   bg = '#b3261e'; break;
        case 'warning': bg = '#e6a135'; break;
        default:        bg = '#2b6db5'; break;
    }
    Toastify({
        text: message,
        duration: 4000,
        gravity: 'bottom',
        position: 'right',
        style: { background: bg }
    }).showToast();
}

/**
 * Confirm and execute un-assignment of a reviewer from a project.
 */
function confirmUnassignReviewer(projectId) {
    // Cleanup any previous modals
    $('#workflowModal').remove();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');

    var modal = $('<div class="modal fade" id="workflowModal" tabindex="-1" role="dialog" data-backdrop="static">'
        + '<div class="modal-dialog modal-dialog-centered" role="document" style="max-width:480px;">'
        + '<div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">'
        + '<div style="padding:28px 28px 20px;text-align:center;">'
        + '<div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#fee2e2,#fecaca);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">'
        + '<i class="fas fa-user-minus" style="color:#dc2626;font-size:22px;"></i>'
        + '</div>'
        + '<h5 style="margin:0 0 8px;font-weight:700;font-size:17px;color:#1e1b4b;">Un-assign Reviewer?</h5>'
        + '<p style="margin:0 0 20px;font-size:13px;color:#64748b;line-height:1.5;">'
        + 'This will remove the currently assigned reviewer from this project. '
        + 'You can assign a new reviewer afterwards.</p>'
        + '<div style="margin-bottom:20px;text-align:left;">'
        + '<label style="font-size:11px;font-weight:600;display:block;margin-bottom:6px;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Reason (optional)</label>'
        + '<textarea id="unassignReason" rows="2" maxlength="2000" style="width:100%;font-size:13px;border:2px solid #e2e8f0;border-radius:8px;padding:10px 12px;color:#1e1b4b;resize:none;font-family:inherit;" placeholder="Optional reason for un-assigning..."></textarea>'
        + '</div>'
        + '<div style="display:flex;gap:10px;justify-content:center;">'
        + '<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="padding:10px 20px;border-radius:8px;font-weight:600;font-size:13px;">Cancel</button>'
        + '<button type="button" class="btn btn-sm" id="confirmUnassignBtn" style="padding:10px 24px;border-radius:8px;font-weight:600;font-size:13px;background:linear-gradient(135deg,#ef4444,#dc2626);border:none;color:#fff;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(239,68,68,.3);" onclick="executeUnassignReviewer(' + projectId + ')">'
        + '<i class="fas fa-user-minus"></i> Un-assign'
        + '</button>'
        + '</div>'
        + '<div id="unassignError" style="display:none;margin-top:12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:13px;color:#991b1b;text-align:left;"></div>'
        + '</div>'
        + '</div></div></div>');

    $('body').append(modal);
    modal.modal('show');
}

function executeUnassignReviewer(projectId) {
    var btn = document.getElementById('confirmUnassignBtn');
    var errorDiv = document.getElementById('unassignError');
    var reason = document.getElementById('unassignReason').value;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Un-assigning...';
    errorDiv.style.display = 'none';

    var data = new FormData();
    data.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    data.append('project_id', projectId);
    if (reason.trim()) data.append('reason', reason.trim());

    fetch('/workflow/unassign-reviewer', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: data,
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            $('#workflowModal').modal('hide');
            showToast('success', data.message || 'Reviewer un-assigned successfully.');
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            errorDiv.textContent = data.error || 'Failed to un-assign reviewer.';
            errorDiv.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-minus"></i> Un-assign';
        }
    })
    .catch(function() {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-minus"></i> Un-assign';
    });
}

// Process any queued flash messages from redirect (deferred until page ready)
document.addEventListener('DOMContentLoaded', function() {
    if (window._flashMessages && window._flashMessages.length) {
        window._flashMessages.forEach(function(msg) {
            showToast(msg.type, msg.message);
        });
        window._flashMessages = [];
    }
});

/**
 * Open the "View Grade" modal for a project (used on home dashboard).
 * Loads the view-grade modal partial via AJAX.
 */
$(document).on('click', '.open-grade-modal', function() {
    const projectId = $(this).data('project-id');
    const projectTitle = $(this).data('project-title') || 'Project';

    // Cleanup any previous modal
    $('#workflowModal').remove();
    $('.modal-backdrop').remove();

    // Show loading
    const modal = $('<div class="modal fade" id="workflowModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">'
        + '<div class="modal-dialog modal-dialog-centered" role="document" style="max-width:560px;">'
        + '<div class="modal-content" style="border-radius:8px;border:none;box-shadow:var(--fluent-depth-16);">'
        + '<div class="modal-body text-center py-4"><i class="fas fa-spinner fa-spin" style="font-size:24px;color:var(--color-brand-500);"></i><p style="margin-top:8px;color:var(--color-ink-500);">Loading grade details...</p></div>'
        + '</div></div></div>');
    $('body').append(modal);
    modal.modal('show');

    // Load the view-grade partial via AJAX
    $.ajax({
        url: '/workflow/view-grade/' + projectId,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Replace body with our rendered view
                modal.find('.modal-content').html(response.html);
            } else {
                modal.find('.modal-content').html(
                    '<div class="modal-body text-center py-4">'
                    + '<i class="fas fa-exclamation-triangle" style="font-size:24px;color:var(--color-danger);"></i>'
                    + '<p style="margin-top:8px;color:var(--color-ink-500);">' + (response.error || 'Could not load grade details.') + '</p>'
                    + '<button type="button" class="btn-secondary btn-sm" data-dismiss="modal">Close</button>'
                    + '</div>'
                );
            }
        },
        error: function() {
            modal.find('.modal-content').html(
                '<div class="modal-body text-center py-4">'
                + '<i class="fas fa-exclamation-triangle" style="font-size:24px;color:var(--color-danger);"></i>'
                + '<p style="margin-top:8px;color:var(--color-ink-500);">Network error. Please try again.</p>'
                + '<button type="button" class="btn-secondary btn-sm" data-dismiss="modal">Close</button>'
                + '</div>'
            );
        }
    });
});
</script>

{{-- Toastify (lightweight toast notifications) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js"></script>

@stack('scripts')

{{-- Version History Modal --}}
<div id="versionHistoryModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:12px;width:560px;max-width:92vw;max-height:80vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid var(--ink-100,#eceef2);">
            <div>
                <h3 style="margin:0;font-size:16px;font-weight:700;color:var(--ink-800,#1e1927);">Version History</h3>
                <p style="margin:2px 0 0;font-size:12px;color:var(--ink-400,#8c8994);">RTS — Research Tracking System</p>
            </div>
            <button onclick="document.getElementById('versionHistoryModal').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:18px;color:var(--ink-400,#8c8994);padding:4px 8px;border-radius:6px;" onmouseover="this.style.background='var(--ink-50,#f5f4f2)'" onmouseout="this.style.background='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div style="overflow-y:auto;flex:1;padding:16px 24px;">
            @php
                $versionHistory = [
                    [
                        'version'   => '2.3.1',
                        'date'      => 'October 2026',
                        'tagline'   => 'Mandatory Nationality on First Login',
                        'changes'   => [
                            'Users without a saved nationality are now met with a non-dismissable modal right after login (both SSO and password logins) that must be completed with a nationality choice before the application can be used',
                            'Research Pillars and Colleges/Institutes pages restyled to match the Grant Types page — same table name styling, italic description captions, shared chip/badge look for sub-pillars and departments, and primary-colored Edit buttons; pillar delete now asks for confirmation',
                            'AI Assistant — system prompt expanded with the report rejection workflows (Report / Missing-Invalid Ethical Approval / Other types, admin review & resubmission flow, and edit-in-place grading flow)',
                            'Sidebar — Cycles moved back under System Settings (it had been relocated to Administration)',
                            'Workflow hardening — status-transition endpoints now verify the action is actually available for the user and project before recording a status; admin rejection review requires a still-pending report rejection (no more review of a non-existent or already-resolved rejection); a second proposal rejection of the same project is now recorded instead of silently suppressed (prevents the project from getting stuck with no reviewer and no admin actions)',
                            'Send Email page — manual admin emails no longer depend on the MAIL_ENABLED switch (the switch now only silences automatic system notifications such as reminders); failures remain visible in the send log with Retry',
                            'New artisan command `import:grants-2026` — imports the internal-grants master workbook (one tab per grant) as research calls / projects into a cycle: auto-creates missing grant types, matches and auto-creates LPI users, links colleges (with code aliases), and applies per-tab end dates as report deadlines; idempotent and safe to re-run, with optional --password for auto-created users and --dry-run preview',
                            'Fixed Update Progress page crash (Unknown column "type" in project_students order clause) — added the missing legacy student-grant columns (user_id with FK to users, type, std_id, days, score) idempotently',
                            'Student-grant progress form restored (parity with the legacy app) — student-category projects now open a one-shot Student Progress page instead of the regular report tabs: per-student Qatari/Non-Qatari selection, publications list, spending vs allocated-budget meter, student-engagement narrative and multiple ethical-approval PDFs; includes Save as Draft, Restore-to-last-saved point, and a permanent form lock on Submit (legacy student_project_draft behavior)',
                            'Fixed LPI dashboard crash when a project stores a publications list (the string column shadowed the publications relation) — dashboard now counts structured publication rows via the relation method',
                            'New AUTH_LOGIN_MODE switch in .env — one switch activates a single login system: local (email+password), saml (QU ADFS) or mock (MockSAML test); the login page and the logout routine adapt to it (SSO modes hide the password form and perform an IdP single-logout on sign-out instead of leaving the IdP session alive)',
                            'Report template downloads fixed — the Download Template (DOCX) links on the progress update page (Progress 1 / Progress 2 / Final / Readiness cards) now serve the actual template files from storage/app/downloads; the Final Report card had no template link at all and is added',
                            'Admins can now register projects too — the Register action on the projects page is available to both LPI and Admin roles; when an admin registers on behalf of an LPI, the project is bound to the real lead PI (resolved from the PI email the wizard collects, creating the account if missing) instead of being assigned to the admin themselves',
                            'AI Assistant on/off switch — new "Enable AI Assistant" toggle in the System Settings AI tab: when off, the Gemini chat widget is removed from the Help Center and /ai/chat requests are rejected for everyone; toggle state is stored in the ai_settings table and takes effect immediately',
                            'Proposal filenames standardized — proposals are now matched and stored using the project id alone (e.g. <code>QUIKT-CENG-2627-1014.pdf</code>), with any "/" removed so filesystem-illegal IDs such as QUIKT-CENG-26/27-1014 resolve correctly; the old <code>_Application</code> / <code>_proposal</code> suffixes are still accepted on upload but every stored proposal uses the canonical id-only name, and all serving/lookup paths read that name',
                            'Proposal upload feedback fixed — the research-call import result now shows a "Proposals Matched" count plus any unmatched files from the ZIP, and the context-menu bulk upload reports newly-linked vs already-linked proposals instead of always showing "0 matched"',
                            'Unmatched proposal files are no longer stored — ZIP/RAR proposals are now extracted to a temporary staging folder and only copied into the proposals directory once they match a project; files with no matching project id are discarded and listed as unmatched in the result, so stray/mismatched PDFs never land on disk',
                            'Proposal file lookups aligned with the canonical naming — admin ZIP exports (project/program downloads) now include proposals stored as the id-only filename, and the grading page proposal tab presence-check now resolves the same way the file-serving routes do (canonical first, legacy fallback)',
                            'Report files use the same file-safe project id — progress, progress 2, readiness, final and ethical uploads now build filenames via the shared helper (stripping "/" and "\") so project ids containing "/" resolve identically on upload, download and serving',
                            'Event-based email notifications — new automatic emails (toggled by the MAIL_ENABLED switch) sent when: a reviewer is assigned a project, a proposal is accepted/rejected, a project is registered, a project is imported into a research call, a progress report is submitted, and a progress/final report is graded (with a report-card link). Subjects/bodies are editable under Email Templates (each system template is tagged "System"), support placeholders such as *name*, *old_project_id*, *project_title*, *grant_title*, *cycle* and *link*, and every send is recorded in the Email Send Log',
                            'SSO login fixed — QU ADFS assertions that omit the SAML NameID are now accepted (wantNameId disabled); authentication uses the "email id" attribute, so the previous "NameID not found in the assertion of the Response" error no longer blocks login',
                            'Registration wizard submit fixed — submitting no longer hangs: event emails are now delivered after the HTTP response (a slow/unreachable SMTP host used to block the request for ~20s), the submit handler is a dependency-free fetch with a guaranteed re-enable on any error, agreement is validated in-page, and a successful submit smoothly redirects to the project list',
                            'SSO matching now uses the QU ID — the QU ADFS "email id" attribute is matched against users.qu_id (the QU university ID-based address) first, falling back to the email column for accounts without a QU ID',
                            'Student SIS API diagnostics — failures when adding/verifying a student during progress update are now logged with full detail (request URL, HTTP status and response body, JSON errors, or the connection/DNS exception), instead of a single terse line',
                            'Reviewer assignment page — projects whose latest status is progress_rejected now appear (alongside unassigned / Assigned / proposal_rejected), so admins can reassign the reviewer after a rejected progress report',
                            'Research Calls filter bar — filters reordered to Cycle → Grant Type → Grant → Status → Visibility and made cascading: choosing a Cycle narrows the Grant Type options (and clears stale selections), and choosing a Grant Type narrows the Grant options',
                            'New user manuals — dedicated LPI and Reviewer user manual pages (sticky table of contents, light outlined step illustrations, reference tables, troubleshooting and glossary) covering authentication, the status lifecycle, registration, reporting, outcomes, the grading model with its auto-score formula, rejection flows and more; the Help menu shows the LPI manual to LPIs and the Reviewer manual to reviewers (admins see both)',
                            'System Settings tabs restyled — the segmented pill control is now a clean underline tab bar (active tab marked with a brand-colored underline and icon)',
                            'Help Center rebuilt as a tabbed reference — the old role buttons are replaced by underline tabs (Administrator / LPI / Reviewer / General), each opening a full manual (LPI, Reviewer and newly authored Administrator and General guides); the tab defaults to the signed-in user role, the AI assistant is retained, and the Help menu links open the matching tab',
                            'Proposal upload hardened — a proposal is only recorded against a project once its file is confirmed written to disk; copy/create failures are now logged instead of being silently swallowed, so a project can no longer point to a proposal that is missing (which would show as "no files" in File Downloads)',
                            'Top-bar layout — the role switcher now sits on the right of the command bar, directly beside the notification bell (previously it floated on the left after the breadcrumb)',
                            'Users list — added a QU ID column (shown between Email and Type, labelled "User Name")',
                            'SSO login link fixed — the "/saml/login" alias was shadowed by the SAML package catch-all "{idpName}/login" route, so the "Sign in with QU" button returned a 500; the alias now uses "/sso" and works',
                            'serveFile2 hardened — requesting a missing or invalid file now shows the friendly "file does not exist" page instead of a 500 error',
                            'Automated test suite added — dedicated test database (rtsnew_test) with Feature tests for security/role access, route smoke-testing, PDF serving, DB↔disk document consistency and data save/retrieve integrity; run with `php vendor/bin/phpunit`',
                            'Graceful SSO failures — a failed QU single sign-on (unreachable IdP, invalid assertion, certificate problem) or a QU account that is not registered/inactive in RTS now returns to the login page with a clear message instead of a raw 500 error',
                            'Admin system log viewer — a "Logs" button beside the footer version number (admins only) opens an issue logger that lists recent Laravel log entries (filterable by level, with expandable stack traces) and lets the admin clear the log files',
                            'User activity log — records each sign-in (with IP, user agent and, on sign-out, session duration) and the actions users perform; an "Activity" button beside the footer version number (admins only) opens a filterable, paginated viewer with daily sign-in/active-user counts',
                            'Footer pinned to the bottom — the footer (copyright, version history and the admin Activity/Logs links) now sits at the bottom of the page on short pages instead of floating mid-page',
                            'Research Call Summary report — added a Cycle filter before the Research Call dropdown; selecting a cycle narrows the research-call list to that cycle (cascading)',
                        ],
                    ],
                    [
                        'version'   => '2.3.0',
                        'date'      => 'September 2026',
                        'tagline'   => 'Rejection Types & Edit-in-Place Grading',
                        'changes'   => [
                            'Grading — Progress Report 2 versions now default to the latest version selected in the version dropdown (previously the oldest version was selected)',
                            'Grading — new Rejection Type dropdown when rejecting a report (Progress Report Rejection / Missing/Invalid Ethical Approval / Other); the type is saved with the rejection and shown on all rejection panels',
                            'Grading — Missing/Invalid Ethical Approval and Other rejections skip the admin review step and expect no new report version: no resubmission request, no v2 upload unlock, and the reviewer re-opens and edits the existing grading via an Edit Grading button',
                            'Update Progress (LPI) — ethical/Other rejections show an informational banner instead of a resubmission request, and the report upload stays locked while the rejection is unresolved',
                            'AI Assistant — system prompt updated to document rejection types, the edit-in-place grading flow, and the persisted read-only rejection panels',
                            'Report Cards list — fixed Blade template rendering error',
                            'Report Cards list — removed Grant and Category columns for a more focused project overview',
                            'Printable project report card redesigned to follow the supplied evaluation-card layout in RTS brand colors',
                            'Printable report card typography updated to Qatar University’s Helvetica Neue style',
                            'Route audit — removed unreachable/broken routes (dead create/edit pages lacking views, announcements edit, legacy cycles & tags features), fixed Users redirects to users.index, wired the missing Student Grant Summary report route, and added the missing Grant Type edit page',
                            'Security hardening — authentication and admin-only guards added to config CRUD (grant types, pillars, colleges, cycles), AI settings, workflow endpoints, reviewer assignment, reviewer grading, file downloads, score management and project registration; report/proposal PDF serving now requires login; deactivated accounts can no longer log in with password; progress pages and outcome verification restricted to the project’s own LPI or assigned reviewer',
                        ],
                    ],
                    [
                        'version'   => '2.2.0',
                        'date'      => 'September 2026',
                        'tagline'   => 'Dashboard Redesign & Access Control',
                        'changes'   => [
                            'Redesigned Admin dashboard — KPI cards, donut chart, active/inactive research calls, missing reports alert',
                            'Redesigned LPI dashboard — project status donut, research calls with deadlines, deadline alert banner',
                            'Redesigned Reviewer dashboard — project status donut, review list, performance rating, acceptance rate',
                            'Pending Tasks section in notification bell — role-aware actionable items (admin: assignments/gradings, LPI: registration/progress, reviewer: proposals/grading)',
                            'Gemini AI Assistant chat widget in Help Center — context-aware system guidance with markdown formatting',
                            'Admin toggle for auto-grade visibility in final report grading — can hide outcome checkboxes and auto-calculated scores for simpler grading experience',
                            'Unified System Settings page — Gauges, Grading Form, and AI Assistant consolidated into a single tabbed settings page',
                            'Admin dashboard Reports Submitted gadget now includes Readiness Report count',
                            'Legacy file import command — moved 435 files into structured storage with correct naming',
                            'Pillars page — sub-pillars now parsed as comma-separated parts, shown as individual chips and editable add/remove in modal',
                            'File Explorer — research call list now hides calls with no projects and shows an info message stating only calls with at least one project are listed',
                            'All Projects page — cycle, research call, and status filters now remain applied when you open a project and return to the list',
                            'Gauge settings — all gauge thresholds now accept values up to 10,000, fixing saves that were previously blocked for higher values',
                            'Colleges — official QU college codes added to every college and a duplicate college record removed',
                            'Departments — academic departments organized under their parent colleges (35 departments across 8 colleges)',
                            'Top command bar — non-functional search box removed',
                            'Reviewer dashboard — fixed crash on open (Undefined variable $isClaimed) caused by a leftover proposal-status check',
                            'Send Email — sends immediately instead of queued (shared hosting has no queue worker); real SMTP errors now shown in the Send Status log',
                            'Send Email — fixed attachment path so uploaded attachments are actually included in outgoing mail',
                            'Setup URL — added mail test (?mailtest=your@email) to diagnose SMTP on shared hosting without exposing the password',
                            'Ethical Approval — new 5th report type: admins can upload ethical approvals; shown on the project page, upload dashboard and File Explorer',
                            'Research Pillars — duplicate pillar options merged into the canonical seven (Energy, Environment, Health, ICT and Social variants consolidated), so users and projects share one consistent list',
                            'Final grading — rejected grades now persist as a read-only panel showing the rejection reason (previously the record was deleted), and the form reopens automatically when the LPI uploads a revised report',
                            'Grading page — fixed crash opening Grading (Unknown column program_title in cycle_configs order clause)',
                            'Progress grading — rejected grades now persist as a read-only panel showing the rejection flag, reason and resubmission note (the record was previously deleted); the form reopens when the LPI resubmits',
                            'Update Progress page — fixed crash opening the page for projects with legacy submissions missing a created date (dates backfilled + null-safe rendering)',
                            'Grading — after an LPI resubmission the form re-opens blank; selecting the superseded PDF version shows the previous (rejected) grading read-only in the right pane with its rejection reason, while selecting the latest version shows the grading form',
                            'Welcome page — redesigned landing page with title and slogans, platform highlights, sign-in section with account/role information, team directory and contact details',
                            'About page — removed; Help Center, Team pages and the footer version history remain',
                            'Email kill switch — new MAIL_ENABLED environment flag; when false, no email is sent anywhere in the application (workflow notifications, reminders, compose/retry, password reset, setup mailtest) and no send-log rows are written',
                        ],
                    ],
                    [
                        'version'   => '2.1.0',
                        'date'      => 'August 2026',
                        'tagline'   => 'Access Control & Grading Improvements',
                        'changes'   => [
                            'Admin restricted from LPI and Reviewer operational pages (progress upload, grading)',
                            'Reviewer Grading, My Assignments, and Graded Projects menus hidden from sidebar',
                            'Dashboard link removed from profile dropdown',
                            'Announcement "New Announcement" hint restricted to admin only',
                            'Notification bell shows "No new updates" when no notifications or pending tasks',
                            'Removed legacy /reports/projects page and dashboard references',
                            'Project detail page restructured — Status moved to top header (3-column: Grant/Program/Status), full-page 75%/25% flex layout with sticky Status History sidebar',
                            'Grading page auto-grade fixes — correct score key `patent_granted` (not `granted_patent`), per-section independent calculation for A/B/C, maxPossible fallback for zero commitments',
                            'Char count counters added to all Final Grades comment fields (0/500 live update)',
                            'Reviewer Verification buttons added to Publications, Students, and Contributions tables in Outcomes tab — pill-shaped toggle with AJAX save',
                            'System Verification badges restyled to match reviewer verification pill look (green Verified / red Not Found)',
                            'View Details modals on Outcomes tab — Publications, Students, and Contributions tables now have "View" buttons opening modal overlays with full record details',
                            'Report Card button removed from grading page header',
                            'Grade mismatch warning ("Your selection differs from auto-calculated") now shows for all three grading sections (A, B, C)',
                            'Manually selected grades now preserved when reopening a saved draft — no longer overwritten by auto-grade on page load',
                            'Announcements page role-filtered — LPI/Reviewer now see only announcements matching their audience; inactive and expired announcements hidden',
                            'Announcement queries now use case-insensitive audience matching and `is_active` + `expires_at` filters across all endpoints',
                            'User Pillar multi-select — manage users with multiple research pillars via pivot table',
                            'Admin Pending Reviews page — list projects with overdue reviewer actions, admin can grade on behalf of reviewers',
                            'Project detail page — proposal panel now listed alongside other reports',
                            'Register wizard — "Proposal Not Available" message restyled to match grading page format',
                            'Reviewer Assignment page shows all projects (assigned and unassigned) with Assignment filter — un-assign reviewers inline',
                        ],
                    ],
                    [
                        'version'   => '2.1.0',
                        'date'      => 'August 2026',
                        'tagline'   => 'Fluent Design Refresh & Workflow Overhaul',
                        'changes'   => [
                            'Complete UI redesign — QU × Fluent Design System with custom token set',
                            'Acrylic command bar & sidebar with mica-textured brand surfaces',
                            'Role-based dashboards: Admin, LPI, and Reviewer views',
                            'Workflow engine — proposal accept/reject, grading, progress tracking',
                            'Report Card modal with structured evaluation summary',
                            'Built-in DataTables with CSV/Excel/PDF/Print export across all tables',
                            'Reviewer assignment system with mutual-exclusion logic',
                            'Outcomes management via registration wizard',
                            'LPI Contribution Summary dashboard gadgets (grants, cycles, programs, publications, students)',
                        ],
                    ],
                    [
                        'version'   => '2.0.0',
                        'date'      => 'July 2026',
                        'tagline'   => 'Initial Release',
                        'changes'   => [
                            'Core project CRUD with status tracking',
                            'User management with role-based access control',
                            'Cycle & program configuration framework',
                            'Project registration wizard (multi-step form)',
                            'Basic reporting: program status, grant summary, project status',
                            'Basic dashboard with project counts',
                            'Authentication & authorization scaffolding',
                        ],
                    ],
                ];
            @endphp
            @foreach($versionHistory as $v)
            <div style="margin-bottom:16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <span style="background:var(--brand-500,#6c4cf1);color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;">v{{ $v['version'] }}</span>
                    <span style="font-size:11px;color:var(--ink-400,#8c8994);">{{ $v['date'] }}</span>
                </div>
                <p style="margin:0 0 6px;font-size:13px;font-weight:600;color:var(--ink-700,#463f50);">{{ $v['tagline'] }}</p>
                <ul style="margin:0;padding-left:18px;">
                    @foreach($v['changes'] as $change)
                    <li style="font-size:12px;color:var(--ink-500,#5d6677);margin-bottom:3px;line-height:1.5;">{{ $change }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</div>
</body>
