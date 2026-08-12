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
        .cmd-search,
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
    $overviewItems = [
        ['route' => 'home', 'icon' => 'fa-solid fa-table-cells-large', 'label' => 'Dashboard'],
    ];
    if (!$isAdmin) {
        $overviewItems[] = ['route' => 'announcements.index', 'icon' => 'fa-solid fa-bullhorn', 'label' => 'Announcements', 'can' => true];
    }
    $routeGroups = [
        'Overview' => $overviewItems,
        $isAdmin ? 'Research Calls & Projects' : 'Projects' => [
            'programs'  => ['route' => 'programs.index', 'icon' => 'fa-solid fa-arrows-rotate', 'label' => 'Research Calls', 'can' => $isAdmin],
            'projects' => ['route' => 'projects.available', 'icon' => 'fa-solid fa-diagram-project', 'label' => 'Projects', 'can' => true],
            'my-assignments' => ['route' => 'projects.my-assignments', 'icon' => 'fa-solid fa-check-double', 'label' => 'My Assignments', 'can' => false],
            'graded-projects' => ['route' => 'gradedProjects', 'icon' => 'fa-solid fa-star', 'label' => 'Graded Projects', 'can' => false],
        ],
        'Administration' => [
            'users' => ['route' => 'users.index', 'icon' => 'fa-solid fa-users', 'label' => 'Users', 'can' => $isAdmin],
            'teams' => ['route' => 'teams.index', 'icon' => 'fa-solid fa-users-gear', 'label' => 'Team', 'can' => $isAdmin],
            'announcements' => ['route' => 'announcements.index', 'icon' => 'fa-solid fa-bullhorn', 'label' => 'Announcements', 'can' => $isAdmin],
            'admin-upload' => ['route' => 'admin-upload.index', 'icon' => 'fa-solid fa-cloud-arrow-up', 'label' => 'Upload Reports', 'can' => $isAdmin],
            'budget-utilization' => ['route' => 'budget-utilization.index', 'icon' => 'fa-solid fa-coins', 'label' => 'Budget Utilization', 'can' => $isAdmin],
            'reviewer-grading' => ['route' => 'reviewer-grading.index', 'icon' => 'fa-solid fa-star', 'label' => 'Reviewer Grading', 'can' => $isAdmin],
            'send-email' => ['route' => 'admin.send-email', 'icon' => 'fa-solid fa-envelope', 'label' => 'Send Email', 'can' => $isAdmin],
            'email-templates' => ['route' => 'email-templates.index', 'icon' => 'fa-solid fa-file-lines', 'label' => 'Email Templates', 'can' => $isAdmin],
            'file-explorer' => ['route' => 'file-explorer.index', 'icon' => 'fa-solid fa-folder-tree', 'label' => 'File Downloads', 'can' => $isAdmin],
        ],
        'Reports' => [
            'cycle-progress' => ['route' => 'reports.cycle-progress', 'icon' => 'fa-solid fa-chart-bar', 'label' => 'Research Call Summary', 'can' => $isAdmin],
        ],
        'Configuration' => [
            'scores' => ['route' => 'scores.index', 'icon' => 'fa-solid fa-star', 'label' => 'Scores', 'can' => $isAdmin],
            'gauge-settings' => ['route' => 'gauge-settings.index', 'icon' => 'fa-solid fa-gauge-high', 'label' => 'Gauge Settings', 'can' => $isAdmin],
            'grant-types' => ['route' => 'grant-types.index', 'icon' => 'fa-solid fa-trophy', 'label' => 'Grant Types', 'can' => $isAdmin],
            'pillars' => ['route' => 'pillars.index', 'icon' => 'fa-solid fa-columns', 'label' => 'Research Pillars', 'can' => $isAdmin],
            'colleges' => ['route' => 'colleges.index', 'icon' => 'fa-solid fa-university', 'label' => 'Colleges/Institutes', 'can' => $isAdmin],
            'cycle_configs' => ['route' => 'cycle-configs.index', 'icon' => 'fa-solid fa-calendar-alt', 'label' => 'Cycles', 'can' => $isAdmin],
        ],
    ];
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
                    <div class="sidebar-nav-label sidebar-section-toggle" data-section="{{ $sectionLabel }}">
                        <span>{{ $sectionLabel }}</span>
                        <i class="fas fa-chevron-down sidebar-section-arrow"></i>
                    </div>
                    <div class="sidebar-section-items {{ $hasActive ? '' : 'collapsed' }}">
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

            {{-- Search --}}
            <div class="cmd-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search applicants, ID, program…" class="cmd-search-input">
            </div>

            {{-- Role Switcher (for composite role users) --}}
            @if($user->canSwitchRole())
                <div class="role-switcher">
                    <form id="roleSwitchForm" method="POST" action="{{ route('switch-role') }}">
                        @csrf
                        <div class="cmd-role-select">
                            <i class="fa-solid fa-user-shield"></i>
                            <select name="role" onchange="this.form.submit()" class="role-select-dropdown">
                                @foreach($user->subRoles() as $role)
                                    <option value="{{ $role }}" {{ ($user->activeRole() === $role) ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
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

            {{-- Footer --}}
            <div class="fluent-footer">
                <span>&copy; {{ date('Y') }} Qatar University. All rights reserved.</span>
                <div class="footer-right">
                    <span>Powered by <strong>RTS</strong></span>
                    <span>v2.0.0</span>
                </div>
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
    <a href="{{ route('announcements.index') }}" class="dropdown-item text-center" style="border-top:1px solid var(--color-ink-100);font-size:12px;color:var(--color-brand-500);">
        <i class="fas fa-bullhorn"></i> View All Announcements
    </a>
</div>

<div id="userDropdown" class="fluent-dropdown" style="display:none;">
    <div class="dropdown-header"><strong>{{ $user->name }}</strong><br><small>{{ $user->email }}</small></div>
    <a class="dropdown-item" href="{{ route('home') }}"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
    <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fa-solid fa-gear"></i> Profile Settings</a>
    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>

<div id="aboutDropdown" class="fluent-dropdown" style="display:none;">
    <div class="dropdown-header"><strong>About RTS</strong></div>
    <a class="dropdown-item" href="{{ route('about.index') }}"><i class="fa-solid fa-circle-info"></i> About RTS</a>
    <a class="dropdown-item" href="{{ route('about.help') }}"><i class="fa-solid fa-circle-question"></i> Help Center</a>
    <a class="dropdown-item" href="{{ route('about.team') }}"><i class="fa-solid fa-users"></i> Our Team</a>
</div>

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
                list.innerHTML =
                    '<div class="dropdown-item disabled">' +
                        '<div class="dropdown-item-inner">' +
                            '<div class="text-center w-100 py-2" style="color:var(--color-ink-400);font-size:13px;">' +
                                '<i class="fa-regular fa-bell-slash"></i> No new notifications' +
                            '</div>' +
                        '</div>' +
                    '</div>';
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
function openWorkflowModal(projectId, action, size) {
    // Cleanup any previous modals
    $('#workflowModal').remove();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');

    // Determine modal width based on size parameter
    var modalWidth = '540px';
    if (size === 'lg') {
        modalWidth = '820px';
    } else if (size === 'xl') {
        modalWidth = '960px';
    } else if (size === 'sm') {
        modalWidth = '380px';
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

    $.get('/workflow/modal/' + action + '/' + projectId, function(res) {
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
    if (!errorDiv || !errorText || !decisionError) {
        // Fallback if span not found — use erroDiv directly
        if (!errorText && errorDiv) {
            errorDiv.textContent = 'An unexpected error occurred.';
            errorDiv.style.display = 'block';
        }
        return;
    }

    errorDiv.style.display = 'none';
    decisionError.style.display = 'none';

    // Validate decision selection
    const accept = form.querySelector('input[name="accept"]:checked');
    if (!accept) {
        decisionError.textContent = 'Please select Accept or Reject.';
        decisionError.style.display = 'block';
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
    data.append('accept', accept.value);
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
                    gravity: 'top',
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
                gravity: 'top',
                position: 'right',
                style: { background: 'var(--color-danger, #b3261e)' }
            }).showToast();
        }
    });
}

/**
 * Centralized toast notification — top-right corner via Toastify.
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
        gravity: 'top',
        position: 'right',
        style: { background: bg }
    }).showToast();
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
</body>
