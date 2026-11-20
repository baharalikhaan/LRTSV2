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

    {{-- Page action buttons: one size across every page (same as .btn-sm) --}}
    .page-actions .btn-primary,
    .page-actions .btn-secondary{
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
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
            'my-assignments' => ['route' => 'projects.research-calls', 'params' => ['context' => 'my-assignments'], 'active' => ['projects.my-assignments'], 'icon' => 'fa-solid fa-check-double', 'label' => 'My Assignments', 'can' => false],
            'graded-projects' => ['route' => 'projects.research-calls', 'params' => ['context' => 'graded-projects'], 'active' => ['gradedProjects'], 'icon' => 'fa-solid fa-star', 'label' => 'Graded Projects', 'can' => false],
            'reviewer-assignment' => ['route' => 'projects.research-calls', 'params' => ['context' => 'reviewer-assignment'], 'active' => ['projects.reviewer-assignment'], 'icon' => 'fa-solid fa-user-check', 'label' => 'Reviewer Assignment', 'can' => $isAdmin],
            'extend-progress' => ['route' => 'projects.research-calls', 'params' => ['context' => 'extend-progress'], 'active' => ['projects.extend-progress'], 'icon' => 'fa-solid fa-calendar-plus', 'label' => 'Extend Progress Report', 'can' => $isAdmin],
            'admin-upload' => ['route' => 'projects.research-calls', 'params' => ['context' => 'upload-reports'], 'active' => ['admin-upload.index'], 'icon' => 'fa-solid fa-cloud-arrow-up', 'label' => 'Upload Reports', 'can' => $isAdmin],
            'pending-reviews' => ['route' => 'projects.research-calls', 'params' => ['context' => 'pending-reviews'], 'active' => ['projects.pending-reviews'], 'icon' => 'fa-solid fa-clock-rotate-left', 'label' => 'Evaluate Projects', 'can' => $isAdmin],
            'report-cards' => ['route' => 'projects.research-calls', 'params' => ['context' => 'report-cards'], 'active' => ['reports.report-cards'], 'icon' => 'fa-solid fa-file-lines', 'label' => 'Report Cards', 'can' => $isAdmin],
        ],
        'Reports' => [
            'report-project-details' => ['route' => 'reports.project-details', 'icon' => 'fa-solid fa-table-list', 'label' => 'Project Details', 'can' => $isAdmin],
            'report-program-status' => ['route' => 'reports.program-status', 'icon' => 'fa-solid fa-clipboard-list', 'label' => 'Research Call Status', 'can' => $isAdmin],
            'report-grant-summary' => ['route' => 'reports.grant-summary', 'icon' => 'fa-solid fa-hand-holding-dollar', 'label' => 'Grant Summary', 'can' => $isAdmin],
            'report-pillar-summary' => ['route' => 'reports.pillar-summary', 'icon' => 'fa-solid fa-columns', 'label' => 'Pillar Summary', 'can' => $isAdmin],
            'report-student-grant-summary' => ['route' => 'reports.student-grant-summary', 'icon' => 'fa-solid fa-user-graduate', 'label' => 'Student Grant Summary', 'can' => $isAdmin],
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

        // Admin: rejections awaiting their review — the admin opens
        // "Review Rejection", adds his own comment and sends the report
        // back to the LPI. Mirrors Project::hasUnreviewedRejection():
        // the latest row of the rejection round is still a rejection,
        // graded projects never count. Ethical/Other rejections count too —
        // the admin forwards them so the LPI re-sends the ethical approval.
        $rejectionPairs = [
            ['progress_rejected', 'progress_rejection_reviewed'],
            ['progress2_rejected', 'progress2_rejection_reviewed'],
            ['final_rejected', 'final_rejection_reviewed'],
        ];
        $pairSql = [];
        foreach ($rejectionPairs as [$rejected, $reviewed]) {
            $pairSql[] = "EXISTS (
                    SELECT 1 FROM status_histories s
                    WHERE s.project_id = p.id
                      AND s.status IN ('{$rejected}', '{$reviewed}')
                      AND s.id = (SELECT MAX(x.id) FROM status_histories x
                                  WHERE x.project_id = p.id
                                    AND x.status IN ('{$rejected}', '{$reviewed}'))
                      AND s.status = '{$rejected}'
                )";
        }
        $unreviewedRejections = (int) (\Illuminate\Support\Facades\DB::selectOne(
            'SELECT COUNT(DISTINCT p.id) AS c FROM projects p
             WHERE NOT EXISTS (
                 SELECT 1 FROM status_histories g
                 WHERE g.project_id = p.id AND g.status = \'Graded\'
             )
             AND (' . implode(' OR ', $pairSql) . ')'
        )->c ?? 0);
        if ($unreviewedRejections > 0) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-balance-scale',
                'color' => '#dc2626',
                'label' => $unreviewedRejections . ' rejected report' . ($unreviewedRejections > 1 ? 's' : '') . ' awaiting your review',
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

        // LPI: rejected reports needing resubmission — shown while the
        // workflow sits on a rejection / admin-review state. Keyed on the
        // LATEST status so it disappears automatically once the LPI
        // resubmits (progress_added / final_added) or the project is graded;
        // the previous all-time check hid it forever for any project that
        // had ever uploaded a progress report.
        $rejectedCount = \App\Models\Project::where('lpi_id', $user->id)
            ->whereRaw('
                (SELECT sh.status FROM status_histories sh
                 WHERE sh.project_id = projects.id
                 ORDER BY sh.id DESC LIMIT 1)
                IN (?, ?, ?, ?, ?, ?)
            ', [
                \App\Models\Project::STATUS_PROGRESS_REJECTED,
                \App\Models\Project::STATUS_PROGRESS_REJ_REVIEWED,
                \App\Models\Project::STATUS_PROGRESS2_REJECTED,
                \App\Models\Project::STATUS_PROGRESS2_REJ_REVIEWED,
                \App\Models\Project::STATUS_FINAL_REJECTED,
                \App\Models\Project::STATUS_FINAL_REJ_REVIEWED,
            ])->count();
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

        // Reviewer: accepted (claimed) projects whose report is ready to grade
        // now — i.e. a progress or final report exists AND its deadline has
        // passed (the same conditions save-progress-grade / save-final-grade
        // enforce). Distinct from the "pending your review" queue above, which
        // counts every assigned project that is not yet Graded.
        $pendingGrading = \App\Models\Project::whereHas('reviewers', function ($q) use ($user) {
            $q->where('user_id', $user->id)->whereNotNull('claimed_at');
        })->visibleProgram()
          ->whereDoesntHave('statusHistories', function ($q) {
              $q->where('status', \App\Models\Project::STATUS_GRADED);
          })
          ->where(function ($q) {
              $q->where(function ($qq) {
                  $qq->whereHas('submissions', function ($s) {
                      $s->where('type', 'progress');
                  })->whereHas('program', function ($p) {
                      $p->whereNull('prog_rpt_deadline')->orWhere('prog_rpt_deadline', '<', now());
                  });
              })->orWhere(function ($qq) {
                  $qq->whereHas('submissions', function ($s) {
                      $s->where('type', 'final');
                  })->whereHas('program', function ($p) {
                      $p->whereNull('final_rpt_deadline')->orWhere('final_rpt_deadline', '<', now());
                  });
              });
          })
          ->count();
        // Only surface "ready to grade" when it is a strict subset of the
        // queue — if every pending project is already ready, the second task
        // would just repeat the first one for the same records.
        if ($pendingGrading > 0 && $pendingGrading < $pendingProposals) {
            $pendingTasks[] = [
                'icon'  => 'fa-solid fa-star',
                'color' => '#7c3aed',
                'label' => $pendingGrading . ' accepted project' . ($pendingGrading > 1 ? 's' : '') . ' ready to grade',
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
        @php
            // A menu item is active when its route matches. Items that share
            // one route (the research-call chooser, one per context) are told
            // apart by their parameters so only the clicked one highlights.
            $navItemActive = function (array $item, string $key): bool {
                $params = $item['params'] ?? [];

                if (request()->routeIs($item['route'] . '*')) {
                    foreach ($params as $param => $value) {
                        if ((string) request()->route($param) !== (string) $value) {
                            return false;
                        }
                    }

                    return true;
                }

                if (request()->routeIs($key . '*')) {
                    return true;
                }

                foreach ($item['active'] ?? [] as $target) {
                    if (request()->routeIs($target . '*')) {
                        return true;
                    }
                }

                return false;
            };
        @endphp
        @foreach($routeGroups as $sectionLabel => $items)
            @php
                $visibleItems = array_filter($items, function($i) { return $i['can'] ?? true; });
            @endphp
            @if(count($visibleItems))
                @php
                    $hasActive = false;
                    foreach ($visibleItems as $key => $item) {
                        if ($navItemActive($item, (string) $key)) {
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
                                $isActive = $navItemActive($item, (string) $key);
                            @endphp
                            <a class="sidebar-nav-item {{ $isActive ? 'active' : '' }}"
                               href="{{ route($item['route'], $item['params'] ?? []) }}">
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
                 <a href="javascript:void(0)" onclick="document.getElementById('versionHistoryModal').style.display='flex'" style="color:var(--ink-400);cursor:pointer;font-size:12px;">v2.4.0</a>
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
{{-- DataTables export back-ends: SheetJS for Excel, jsPDF + AutoTable for PDF --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

{{-- DataTables defaults (buttons + export back-ends) are applied HERE, right
     after the libs load, NOT inside DOMContentLoaded: page scripts use
     $(document).ready which fires BEFORE a DOMContentLoaded listener registered
     later in this file, so defaults set there arrive too late for page tables. --}}
<script>
    if (window.jQuery && jQuery.fn.dataTable) {
        jQuery.extend(jQuery.fn.dataTable.defaults, {
            dom: '<"dt-toolbar"<"dt-buttons"B><"dt-search"f>>' +
                 '<"dt-table-wrap"t>' +
                 '<"dt-bottom"<"dt-info"i><"dt-paginate"p>>',
            buttons: [
                { extend: 'copy', text: '<i class="fa-regular fa-copy"></i> Copy', className: 'btn btn-dt',
                  exportOptions: { columns: ':visible:not(.no-export)' } },
                { extend: 'csv', text: '<i class="fa-regular fa-file-lines"></i> CSV', className: 'btn btn-dt',
                  exportOptions: { columns: ':visible:not(.no-export)' } },
                { extend: 'excel', text: '<i class="fa-regular fa-file-excel"></i> Excel', className: 'btn btn-dt',
                  exportOptions: { columns: ':visible:not(.no-export)' } },
                { extend: 'pdf', text: '<i class="fa-regular fa-file-pdf"></i> PDF', className: 'btn btn-dt',
                  orientation: 'landscape', pageSize: 'A4',
                  exportOptions: { columns: ':visible:not(.no-export)' } },
                { extend: 'print', text: '<i class="fa-regular fa-print"></i> Print', className: 'btn btn-dt',
                  exportOptions: { columns: ':visible:not(.no-export)' } }
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
                var searchInput = jQuery(this).closest('.dataTables_wrapper').find('.dataTables_filter input');
                searchInput.attr('placeholder', 'Search records…');
            }
        });
    }
</script>

{{-- Bootstrap JS (for modal functionality only, no styles) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

{{-- Chart.js (dashboard charts) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

{{-- Base URL helper: the app may run in a subfolder (e.g. /lrts), so every
     hand-written root-relative URL must go through appUrl() to resolve
     against the app base instead of the domain root. --}}
<script>
    window.APP_BASE = @json(rtrim(url('/'), '/'));
    function appUrl(path) {
        return window.APP_BASE + (path.charAt(0) === '/' ? path : '/' + path);
    }
</script>

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
    // DataTable defaults (buttons + export back-ends) are set in the standalone
    // block right after the DataTables libs above — not here, because page
    // scripts use $(document).ready which fires before this listener.
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

    var url = appUrl('/workflow/modal/' + action + '/' + projectId);
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
 * Custom reviewer dropdown (assign modal): close the menu.
 */
function closeReviewerDd() {
    var menu = document.getElementById('reviewerDdMenu');
    if (menu) menu.style.display = 'none';
}

/**
 * Custom reviewer dropdown (assign modal): toggle the menu. The menu is
 * position:fixed and placed under the toggle button so it can extend outside
 * the modal without being clipped.
 */
function toggleReviewerDd(e) {
    if (e) e.stopPropagation();
    var menu = document.getElementById('reviewerDdMenu');
    if (!menu) return;

    if (menu.style.display === 'block') {
        menu.style.display = 'none';
        return;
    }

    var btn = document.querySelector('#reviewerDropdown .rv-dd-toggle');
    if (btn) {
        var r = btn.getBoundingClientRect();
        menu.style.left = r.left + 'px';
        menu.style.width = r.width + 'px';
        menu.style.top = (r.bottom + 4) + 'px';
        // Never taller than the space below the button.
        menu.style.maxHeight = Math.max(160, window.innerHeight - r.bottom - 16) + 'px';
    }
    menu.style.display = 'block';
}

// Keep the fixed menu aligned: close it on scroll / resize (but allow
// scrolling inside the menu itself).
window.addEventListener('scroll', function (e) {
    var menu = document.getElementById('reviewerDdMenu');
    if (menu && e.target && menu.contains(e.target)) return;
    closeReviewerDd();
}, true);
window.addEventListener('resize', closeReviewerDd, true);

/**
 * Custom reviewer dropdown: pick an item, update the hidden input + label.
 */
function pickReviewerDd(el) {
    var input = document.getElementById('reviewer_1');
    var label = document.getElementById('reviewerDdLabel');
    if (input) input.value = el.dataset.id || '';
    if (label) label.textContent = el.dataset.label || '— Select Reviewer —';
    var menu = document.getElementById('reviewerDdMenu');
    if (menu) {
        menu.querySelectorAll('.rv-dd-item').forEach(function (x) { x.classList.remove('is-selected'); });
    }
    el.classList.add('is-selected');
    if (menu) menu.style.display = 'none';
}

// Close the custom reviewer dropdown when clicking outside it.
document.addEventListener('click', function () {
    var menu = document.getElementById('reviewerDdMenu');
    if (menu && menu.style.display === 'block') menu.style.display = 'none';
});

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

    fetch(appUrl('/workflow/assign-reviewers'), {
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

    fetch(appUrl('/workflow/submit-decision'), {
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
        url: appUrl('/workflow/transition'),
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
function confirmUnassignReviewer(projectId, reviewerId) {
    // Cleanup any previous modals
    $('#workflowModal').remove();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');

    var modal = $('<div class="modal fade" id="workflowModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">'
        + '<div class="modal-dialog modal-dialog-centered" role="document" style="max-width:480px;">'
        + '<div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.18);">'
        + '<div class="modal-header" style="border-bottom:1px solid var(--ink-100,#eceef2);padding:16px 20px;">'
        + '<h5 class="modal-title" style="margin:0;font-size:15px;font-weight:700;color:var(--ink-800,#2f2b35);">'
        + '<i class="fas fa-user-minus" style="color:var(--brand-500,#8d1b3d);margin-right:7px;"></i>'
        + 'Un-assign reviewer?</h5>'
        + '</div>'
        + '<div class="modal-body" style="padding:16px 20px;">'
        + '<p style="margin:0 0 14px;font-size:13px;line-height:1.55;color:var(--ink-600,#4b4654);">'
        + 'This removes the selected reviewer from this project. '
        + 'Other assigned reviewers (if any) stay on the project.</p>'
        + '<label for="unassignReason" style="display:block;margin-bottom:6px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-500,#5d6677);">Reason (optional)</label>'
        + '<textarea id="unassignReason" rows="2" maxlength="2000" placeholder="Optional reason for un-assigning…" style="width:100%;font-family:inherit;font-size:13px;padding:8px 10px;border:1px solid var(--ink-100,#eceef2);border-radius:6px;background:#fff;color:var(--ink-800,#2f2b35);resize:none;"></textarea>'
        + '<div id="unassignError" style="display:none;margin-top:12px;padding:9px 12px;border-radius:6px;background:#fdf0f3;border:1px solid #e8c9d2;font-size:12.5px;color:var(--brand-600,#7a1636);"></div>'
        + '</div>'
        + '<div class="modal-footer" style="border-top:1px solid var(--ink-100,#eceef2);padding:12px 20px;display:flex;justify-content:flex-end;gap:8px;">'
        + '<button type="button" class="btn-secondary btn-sm" data-dismiss="modal">Cancel</button>'
        + '<button type="button" class="btn-primary btn-sm" id="confirmUnassignBtn" onclick="executeUnassignReviewer(' + projectId + ', ' + (reviewerId || 'null') + ')">'
        + '<i class="fas fa-user-minus"></i> Un-assign'
        + '</button>'
        + '</div>'
        + '</div></div></div>');

    $('body').append(modal);
    modal.modal('show');
}

function executeUnassignReviewer(projectId, reviewerId) {
    var btn = document.getElementById('confirmUnassignBtn');
    var errorDiv = document.getElementById('unassignError');
    var reason = document.getElementById('unassignReason').value;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Un-assigning...';
    errorDiv.style.display = 'none';

    var data = new FormData();
    data.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    data.append('project_id', projectId);
    if (reviewerId) data.append('reviewer_id', reviewerId);
    if (reason.trim()) data.append('reason', reason.trim());

    fetch(appUrl('/workflow/unassign-reviewer'), {
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
        url: appUrl('/workflow/view-grade/' + projectId),
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
                        'version'   => '2.4.0',
                        'date'      => 'November 2026',
                        'tagline'   => 'Project Details Report & Excel Export',
                        'changes'   => [
                            'Grading opens as soon as a report is uploaded (no longer waits for the research call deadline)',
                            'Progress report grading can be saved as a draft like the final report - the save action was blocked by the required ratings and by the server-side deadline check, and a draft may now be partial',
                            'Final report drafts now remember the auto-grade tick boxes - the outcomes, publications, students and researchers the reviewer included or excluded are stored with the grading and come back exactly as saved (previously the selection was only used for the browser-side score calculation and was lost on reload)',
                            'Drafts now reopen with the reviewer\'s Accept / Reject choice already selected - on the final form the recommendation was always blank after a reload, and a Reject saved with its type and reason came back empty; the progress form now restores a saved Reject, its type and its reason as well',
                            'Progress grading Help modal now carries the full 1-5 rating rubric',
                            'Grading page PDF viewer sized to one full A4 page (paper ratio of the pane width) instead of a fixed 70vh frame',
                            'Project menus no longer offer "Register" for projects already in the workflow - a project counts as registered once it has any status beyond import, so projects registered by an admin (no literal "registered" history row) now show their real next step: "Review Progress Rejection" for a rejected report, "Add Reviewer" while assigned, and the LPI gets "Update Progress"; the registration wizard and its upload endpoint also refuse to re-register them',
                            'Rejection emails gain the VPN disclaimer and drop the grant / year rows',
                            'Automatic event emails trimmed to the reviewer-assignment notice only',
                            'Bulk reviewer assignment runs in a progress modal; confirmations as bottom-right toasts',
                            'Reviewer Assignment lists the whole research call with a "Needs reviewer" flag and per-reviewer un-assign',
                            'Research-call chooser added to every project page, with deadline columns, counts and sorting',
                            'Project Details report: 126-field picker and full Excel export; printable research-call sheet',
                            'Security: file access scoped by role / ownership, dependency updates (51 to 31 advisories), wider test suite',
                        ],
                    ],
                    [
                        'version'   => '2.3.1',
                        'date'      => 'October 2026',
                        'tagline'   => 'Mandatory Nationality on First Login',
                        'changes'   => [
                            'Non-dismissable nationality step after the first login (SSO and password)',
                            'SSO: NameID-less ADFS assertions accepted, matched on QU ID, with graceful failure pages; AUTH_LOGIN_MODE switch (local / saml / mock)',
                            'Multi-reviewer grading with per-reviewer claims; a stage closes only when every reviewer submits',
                            'Ethical and "other" rejections route through the admin; edit-in-place grading without a new version',
                            'Notification-bell pending tasks fixed (admin rejection queue, LPI resubmission task)',
                            'Rich academic notification and reminder emails, plus a global MAIL_ENABLED kill switch',
                            'Registration on behalf of an LPI; proposal and report filenames normalised for ids containing "/"',
                            'Help Center rebuilt: role tabs, LPI / Reviewer / Admin manuals, AI assistant toggle',
                            'Activity log and system log viewer, footer pinned, automated test suite on a dedicated test database',
                        ],
                    ],
                    [
                        'version'   => '2.3.0',
                        'date'      => 'September 2026',
                        'tagline'   => 'Rejection Types & Edit-in-Place Grading',
                        'changes'   => [
                            'New Rejection Type (report / missing-invalid ethical approval / other), saved and shown on every rejection panel',
                            'Ethical and "other" rejections expect no new report version: the reviewer re-opens and edits the existing grading',
                            'LPI sees an informational banner and the report upload stays locked while the rejection stands',
                            'Printable report card redesigned in RTS brand colours and QU typography',
                            'Route audit and security hardening across config, workflow, grading and file endpoints',
                        ],
                    ],
                    [
                        'version'   => '2.2.0',
                        'date'      => 'September 2026',
                        'tagline'   => 'Dashboard Redesign & Access Control',
                        'changes'   => [
                            'Redesigned Admin, LPI and Reviewer dashboards (KPIs, donut charts, deadline alerts)',
                            'Notification-bell pending tasks per role (assignments, registrations, gradings)',
                            'Gemini AI assistant in the Help Center; unified tabbed System Settings page',
                            'Ethical Approval added as a fifth report type, uploadable by admins',
                            'Rejected gradings persist as read-only panels and reopen when the report is resubmitted',
                            'Welcome page redesigned; About page retired',
                            'MAIL_ENABLED kill switch, immediate sending with working attachments',
                        ],
                    ],
                    [
                        'version'   => '2.1.0',
                        'date'      => 'August 2026',
                        'tagline'   => 'Fluent Design Refresh, Access Control & Grading',
                        'changes'   => [
                            'Complete QU x Fluent redesign: acrylic command bar and sidebar, role-based dashboards',
                            'Workflow engine (proposal accept / reject, grading, progress) with reviewer assignment and DataTable exports',
                            'Admins restricted from LPI and Reviewer operational pages; menu visibility by role',
                            'Project detail page restructured with a sticky status-history sidebar',
                            'Auto-grade fixes, manual grades preserved on draft, mismatch warnings and live character counters',
                            'Reviewer verification toggles and detail modals on outcomes',
                            'Announcements filtered by audience and expiry; multi-pillar user support',
                            'Admin Pending Reviews: grade on behalf of reviewers; inline un-assignment',
                        ],
                    ],
                    [
                        'version'   => '2.0.0',
                        'date'      => 'July 2026',
                        'tagline'   => 'Initial Release',
                        'changes'   => [
                            'Core project CRUD with status tracking',
                            'User management with role-based access control',
                            'Cycle and program configuration',
                            'Multi-step project registration wizard',
                            'Basic reports (program status, grant summary, project status) and dashboard',
                            'Authentication and authorisation scaffolding',
                        ],
                    ],
                ];
            @endphp
            @foreach($versionHistory as $v)
            <div style="margin-bottom:16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <span style="background:var(--brand-500,#6c4cf1);color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;">v{{ $v['version'] }}</span>
                    <span style="margin-left:auto;font-size:11px;font-weight:600;color:var(--ink-500,#5d6677);">{{ $v['date'] }}</span>
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
