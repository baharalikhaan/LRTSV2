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
                            'The "Un-assign reviewer" confirmation now matches the application theme - maroon accent and icon, themed header, body and footer, the standard outlined Cancel and maroon Un-assign buttons - instead of the previous indigo/red custom styling; the reviewer table also keeps its column widths on smaller screens (it scrolls horizontally rather than squeezing the title column)',
                            'Bulk reviewer assignment now runs inside a progress modal - the form posts one project at a time and the dialog shows "3 / 5", a maroon progress bar and a live list of what happened (✓ project ID — reviewer, ⚠ skipped, ✗ failed); the backdrop is static so a stray click cannot dismiss it, a Cancel button stops the remaining assignments, and the page reloads when the run finishes (or is cancelled) so the table shows the new assignments, with a summary toast on the reloaded page',
                            'Reviewer Assignment confirmations now appear as the standard bottom-right toast (green "success", red "error") instead of an inline alert box at the top of the page, so the bulk-assign result no longer pushes the table down',
                            'The research-call chooser now uses the same DataTable treatment as the other project tables - export toolbar (Copy / CSV / Excel / PDF / Print), the table search box, paging info and pagination - while the column headers stay clickable for the server-side sort (cycle year, research call, grant and the three deadlines); the research-call name in the first column is styled like the project-table links (maroon, regular weight, underlined) instead of a bold blue link',
                            'The research-call chooser now shows what each call\'s deadlines are instead of the grant / cycle / project-count badges - three columns (Progress 1, Progress 2 and Final report deadline) display the date plus a short "N days left" or "passed" note, coloured red when overdue and amber when due within two weeks; the number of projects is kept as a plain, sortable column (no badge), all deadline columns are sortable, and the default order stays newest cycle first then research call title',
                            'Reviewer Assignment table layout - each reviewer in the "Assigned reviewer" column now carries his own "Un-assign" link right next to the name (the confirmation prompt is unchanged), and the Action column keeps only the reviewer dropdown used for adding a reviewer; the column widths were rebalanced so the reviewer names, the link and the project ID stay readable',
                            'The research-call banner on every filtered project list shows how many projects are listed out of the research call total, and Reviewer Assignment now lists the whole research call when one is selected (it stays an assignment queue - only projects that need a reviewer - on the unfiltered "Show all" view); a project is flagged only when the reviewer-assignment action is actually pending, i.e. nobody is assigned yet or the reviewer rejected the proposal (report rejections belong to the All Projects page), with a maroon edge, a "Needs reviewer" badge and a "N need reviewer" header count, so the page and the chooser figure always agree; pages that load their table by Ajax show the total only',
                            'Sorting added to the projects pages - the research-call chooser and the All Projects list now default to the newest research-call cycle first and then the research call title (A-Z), with the project id as the final tie-breaker; the chooser headers (Research Call, Grant, Cycle, Projects, Assigned) are clickable to sort by that column and toggle the direction, and the chosen sort is kept while the cycle filter stays applied',
                            'Projects sub-menu entries now open a research-call chooser first - Reviewer Assignment, Extend Progress Report, Upload Reports, Evaluate Projects, Report Cards (and the reviewer pages My Assignments / Graded Projects) list every research call with its grant, cycle, project count and assigned count on one compact line each, and clicking a row (or its name) opens that page filtered to the single research call; a "Show all projects" action keeps the old unfiltered list reachable, the filtered page shows a "Research call" banner with "Change research call" and "Show all", and the cycle filter on the chooser narrows the list before picking',
                            'The research-call filter is now honoured by every project list behind the chooser - My Assignments, Graded Projects, Report Cards and Upload Reports gained a programme filter (the latter in its server-side DataTable query), joining Reviewer Assignment, Extend Progress Report and Evaluate Projects which already supported it',
                            'Security: file downloads are no longer open to every logged-in user - the document viewer now lets an administrator read any file but restricts an LPI or a reviewer to the projects they own or are assigned to (previously any account could read any proposal or report by changing the id in the link); the raw "file" parameter is admin-only and the unused public template-download route was removed',
                            'Security: dependency updates - guzzle 7.13.1 to 7.15.2 (with promises/psr7), league/commonmark 2.8.2 to 2.10.7 and phpseclib 3.0.55 to 3.0.58, cutting the reported advisories from 51 to 31; the remainder sit in Laravel 8 (end-of-life since January 2023) and the Symfony 5.4 packages it pins, so clearing them needs a planned framework upgrade',
                            'Security: the reviewer-grading confirmation message is now escaped (it was printed as raw HTML from the session) and .env backups are git-ignored so a credentials file cannot be committed by accident',
                            'Fixed the corrupted dash placeholders on the grant page (funding agency / max duration) and in the system settings page, which displayed garbled characters when the value was empty; the same mojibake was cleaned out of the source comments in the grading and workflow controllers',
                            'Fixed the health check - the test database pointed project CDIRCC-2024-556 at a proposal file name that no longer existed on disk, which reported a false failure for the whole suite',
                            'Fixed the admin reminder recipient query - deactivated admins can no longer slip in through the orWhere (it is now scoped by is_active)',
                            'Clean-up: removed the orphaned per-cycle reviewer assignment page (route, controller action and view - the Reviewer Assignment page replaced it), the duplicate /projects/available route alias, two notification helpers left behind by the per-recipient refactor, the unused Comment / Tag / ProjectStudentTraining models with the User::comments() relation (their tables were never created), the orphaned about/index view, nine unused status constants and a leftover console.log',
                            'Research calls now have a printable A4 version (/programs/{id}/printable) — the same sheet as the project printable (grant, cycle, status, visibility, deadlines, project status summary and the full project list with lead PI, status and file counts), with a "Print / PDF" button on the research call page',
                            'Research Call report booklet retired — the per-research-call A4 booklet and its buttons have been removed and replaced by the lighter Printable Version sheet, which is reached from the research call page and from each research call\'s Actions menu on the research calls list',
                            'Unified page action buttons — the research call page now matches the project page (one maroon primary action followed by small outlined secondary buttons) and every page-action button shares one size across the app',
                            'Research Call Summary reminder emails rewritten - each of the eleven reminder columns now sends a professional email with the project ID, project title, research call and the deadline that applies to that stage (with days-left or overdue highlighting), plus a short "What is required" checklist of two or three one-line points (e.g. the outcomes reminder names the outcome types that must be recorded and why they matter); the admin "assign reviewers" mail lists every pending project instead of a comma-separated line',
                            'Research Call Summary reminder emails — subjects and headings rewritten in an academic/administrative register (e.g. "Outcome Documentation Required", "Final Report Submission Required", "Reviewer Assignment Required", "Final Report Evaluation Pending") and each subject now identifies the project and research call, e.g. "Final Report Submission Required — IRCC-2024-494 (IRCC – Cycle 2024)"; the admin mail reports the number of pending projects, and the email header shows the reminder title instead of a generic system name',
                            'Workflow notification emails (reviewer assigned / un-assigned, proposal accepted / rejected, project added / registered, progress report received / evaluated, final report evaluated) now use the same rich email design as the reminders — academic heading, project details table with the project ID, project title, research call, grant name, cycle year and report deadlines, "what is required" bullets where relevant and an "Open the project" button — and their subjects follow the same academic pattern (e.g. "Reviewer Assignment Confirmed — IRCC-2024-494 (IRCC – Cycle 2024)"); the eight system templates were rewritten accordingly (greeting and duplicated project lines removed because the layout renders them) and a new *program_title* placeholder is available',
                            'Progress, Readiness and Final report submission reminders now carry a single "Instructions" panel below the "Open the project" button, merging the former "What is required" section and the submission requirements into one numbered checklist - the stage guidance first, then the online template (each reminder links only the template it asks for: Progress Report, Final Report or Readiness Mapping Report), the required signature of the Associate Dean of Research, the grant number, the exact project title and the ros-submissions@qu.edu.qa support address - the details table now also shows the grant name and code the instructions refer to, and the email closes with a matching grey "Disclaimer" note about accessing RTS over the QU network / GlobalProtect VPN; both panels use a small grey typeface, the submission requirements and disclaimer appear only in the reminders sent to lead PIs, and reviewer/admin reminders keep just their own checklist of steps',
                            'Bulk reviewer assignment (Reviewer Assignment page) now sends the same assignment email as the assign-reviewer modal on the project page - each newly assigned reviewer receives the "Reviewer Assignment Confirmed" notification, an already-assigned reviewer is not emailed twice, and a reviewer who previously rejected the project is skipped with a note in the confirmation message; the assignment now also records the reviewer role and the "Assigned" status with its trigger, matching the modal',
                            'Fixed the Reviewer Assignment page table - it declared one "Assigned Reviewer" column but rendered the reviewer cell twice, so the DataTable header and body no longer lined up, the Action column was pushed out of the table and sorting/searching applied to the wrong columns; the duplicate column is gone, column widths are now fixed (Project ID, LPI, Project title, Grant, Category, Status, Assigned reviewer, Action), long titles are clamped to two lines with the full text on hover, the LPI email truncates cleanly, status labels read "Reviewer unassigned" instead of "Reviewer_unassigned", and the page finally applies its Status filter (Unregistered / Registered / Claimed), which was previously ignored by the controller',
                            'Reviewer assignment email reformatted - it now follows the reminder layout: a project details table limited to the project identity (project ID, project title and research call), an "Action required" list (evaluate the Progress Report against the approved proposal with the rubric, give scores and justifying feedback, and recommend whether the project should continue or be discontinued), the "Open the project" button and a grey "Disclaimer" note about accessing RTS over the QU network / GlobalProtect VPN; the grant, cycle year and report deadlines were left out of this email (they belong to the LPI), the duplicated instruction sentence was removed from the template body (template and database copy both updated) and the disclaimer text now has a single source of truth shared with the report reminders',
                            'Report submission instructions trimmed - the Progress, Final and Readiness reminders now open the Instructions panel with a single stage line ("Upload the report as a PDF." for Progress Report 1, "Complete the final outcomes, students and contributions sections." for the Final Report, "Upload the readiness report as a PDF from the project page." for the Readiness Report) directly followed by the four submission requirements; the descriptive lines ("Complete the report form…", "The assigned reviewer evaluates and grades the report.", "Summarise the whole project…", "Confirms the project is ready for its final evaluation." and the duplicated upload line) were removed',
                            'Reminder emails link the official Qatar University post-award forms page (qu.edu.qa/en-us/Research/offices/research-support/post-awards-forms) instead of the locally stored DOCX files - the "Online templates" point opens that page labelled with the report being requested (Progress Report template, Final Report template or Readiness Mapping Report template, or "Post-Award Forms" for the other reminder types), so every recipient always gets the current form; the grant row was also removed from the reminder details table (project ID, project title, research call and the deadline remain)',
                            'Reviewer un-assignment and assignment emails now close with a "Support and queries" block (ros-submissions@qu.edu.qa, as a mailto link) and the grey "Disclaimer" note about accessing RTS over the QU network / GlobalProtect VPN; the un-assignment email also drops the grant, cycle year and report-deadline rows so both reviewer emails show only the project ID, title and research call',
                            'Report upload pages now link the official Qatar University post-award forms page instead of the locally stored DOCX files - the "Download Template" links on the Progress 1, Progress 2, Final and Readiness cards open qu.edu.qa/en-us/Research/offices/research-support/post-awards-forms (in a new tab, labelled "Download Template (QU website)"), so the LPI always receives the current version of each form and the reminders and the pages can no longer drift apart',
                            'Disclaimer wording updated across the reviewer notifications and the report reminders - the note now reads "The RTS system is accessible only through the Qatar University (QU) network, either while on campus or remotely via the GlobalProtect VPN." (one shared constant, so every email changes together); the reviewer assignment email also drops its fourth action point, leaving three steps',
                            'Reviewer un-assignment no longer sends an email - the withdrawal notice is temporarily commented out in WorkflowController::unassignReviewer (recipient, templates, disclaimer/support options and the reviewer-details trimming are all kept, so reinstating it is just uncommenting the block); the reviewer is still removed from the project, the status and consensus logic are unchanged, and a test now guards that no email reaches the removed reviewer',
                            'Fixed the reviewer un-assignment email - it was addressed to the reviewers still assigned to the project (usually none, since the assignment had just been deleted) instead of the reviewer being removed; each removed reviewer now receives the withdrawal notice individually',
                            'Fixed the server-side tables failing with a DataTables "Ajax error" when the page-length selector is set to "All" — the query was sent with an OFFSET but no LIMIT, which MySQL/MariaDB rejects; the File Explorer project list, the Upload Reports list and the Budget Utilization list now return every record correctly',
                            'New "Project Details" report (Reports section) — every project in a single record: one row per project carrying all 126 fields the system holds about it (project identification, research call / grant / cycle, LPI and contact details, research pillars and colleges, reviewers, students, hired researchers, contributions, outcomes, publications, intellectual properties, all report submissions, the three gradings with their ratings and rejection reasons, budget and utilisation, and the college / RSD decisions)',
                            'Project Details Report — filters by cycle, grant, research call and workflow status, plus a free-text search; the on-screen table shows the key fields while "Export Full Report to Excel" downloads a real .xlsx workbook with every field for every project (header row frozen, auto-filter enabled, one row per project)',
                            'Project Details Report — a multi-select field picker (126 fields grouped by their source database table, with a search box and All / None / Recommended shortcuts) now decides which fields are displayed and exported; the choice is remembered for the next visit and the Excel export always contains exactly the fields on screen, always led by the Project ID column so every row stays identifiable',
                            'Project Details Report — the field picker\'s source-table headings now follow the maroon theme (maroon heading text, a light maroon badge for the table name, a maroon field count and a maroon hairline under each group) instead of the grey defaults',
                            'Project Details Report — the project\'s commitments (the outputs the LPI promised: Q1–Q4 articles, conference papers, books, edited books, chapters, IP disclosures, filed patents, open-source software, start-ups, ethical approval, masters / undergraduate / PhD students and cross-college work) are now included as their own Commitments group, so the export shows promised against delivered',
                            'Project Details Report — the results table now has a two-level header: a merged group row (Project, Research Call, LPI & Contact, Pillars & Colleges, Reviewers, Students, Researchers, Outputs, Reports Submitted, Reviews & Grades, Budget, Decisions) above the column names, and the exported workbook carries the same grouped header row',
                            'Project Details Report — the grid\'s own Copy / CSV / Excel / PDF / Print buttons were removed from the results table; the report is exported with its own "Export to Excel" action, which produces a real .xlsx of the selected fields',
                            'Project Details Report — maroon hairline rules now frame both header rows on screen (above the group row, under it and under the column names, with a hairline between groups); the same rules are drawn in the exported workbook and the print/PDF output',
                            'Project Details Report — both header rows are now fixed: the group row and the column names stay pinned while the results scroll (the table scrolls in its own pane), and in the exported workbook both header rows are frozen below row 2; the Excel headings are now light sand tints with maroon text instead of a solid block',
                            'Project Details Report — scrolling fixes: the column sorting arrows are back (the header tint no longer overwrites their icon), the Project ID column is pinned on the left so rows stay identifiable while scrolling sideways, and the pinned column is opaque so the scrolled content no longer shows through it',
                            'Project Details Report — the field picker now sits on its own row under the filters instead of inline with them, and the wide results table scrolls inside the panel instead of stretching the page (the sidebar no longer scrolls away with it)',
                            'Sidebar — a dedicated "Reports" section now lists Project Details alongside the Research Call Status, Grant Summary, Pillar Summary and Student Grant Summary reports, which previously had no navigation link',
                            'Fixed the feature test suite — a stale cached configuration file was overriding the APP_URL and DB_DATABASE values pinned in phpunit.xml, so every HTTP test was sent to /lrts/... (404 on every request) and the tests were running against the live rtsnew database instead of rtsnew_test; the `test` composer script now clears the configuration cache before running the suite',
                        ],
                    ],
                    [
                        'version'   => '2.3.1',
                        'date'      => 'October 2026',
                        'tagline'   => 'Mandatory Nationality on First Login',
                        'changes'   => [
                            'Public landing footer — the Qatar University logo now renders white on transparency to match and contrast with the dark footer',
                            'Public landing page — removed the illustrated sketch from the hero while preserving the title, slogans, sign-in action and responsive layout',
                            'Pending Reviews — admins can now review submitted progress/final reports even when no reviewer is assigned; the assigned-reviewer cell shows a dash for unassigned projects',
                            'Research Calls list — Grant Code is now plain text with Grant Type beneath it; added the Progress Report 2 Deadline column alongside the other report deadlines',
                            'Edit Research Call modal now correctly pre-fills all three existing deadline fields; saving the modal no longer clears deadlines because the date data attributes now match the JavaScript keys',
                            'Research Call Summary — added a Progress Report 2 status column for extended-progress projects, with completed/pending totals and an LPI reminder action; projects without Progress Report 2 enabled show N/A',
                            'Admin Upload Reports — when the current workflow status shows a rejected Progress Report, admin uploads are saved as v2 while retaining the v1 submission and file; subsequent admin re-uploads replace only v2 and record progress_added in status history. Added a Rejected Progress filter, a compact notice that clears after v2 is uploaded, and green Progress, Progress 2 and Final badges show multiple versions together (e.g. "Uploaded v1,v2")',
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
                            'User activity log — records each sign-in (with IP, user agent and, on sign-out, session duration) and the actions users perform; an "Activity" button beside the footer version number (admins only) opens a filterable, sortable, paginated viewer with daily sign-in/active-user counts and a right-hand "Active Users Today" panel (30%) listing each user who signed in today with their sign-in time, IP, number of activities performed and time spent (measured session time, including the current open session)',
                            'Footer pinned to the bottom — the footer (copyright, version history and the admin Activity/Logs links) now sits at the bottom of the page on short pages instead of floating mid-page',
                            'Research Call Summary report — added a Cycle filter before the Research Call dropdown; selecting a cycle narrows the research-call list to that cycle (cascading)',
                            'LPI & Reviewer manuals — added annotated screenshots (numbered callouts) for signing in, the dashboard, the project list/detail, the progress workspace and the grading page',
                            'LPI & Reviewer manuals — screenshots removed in favour of detailed, self-contained step-by-step procedures: each action (signing in, reading the dashboard, finding/opening a project, submitting a progress report, grading a report) is now described step by step with what to click, where, and the expected result, so no screenshots are needed',
                            'Projects list — the Cycle and Research Call filters are now cascading: choosing a Cycle narrows the Research Call dropdown to that cycle (and clears a stale call selection); the same cascading behaviour was added to the Reviewer Assignment page',
                            'Assign Reviewer modal (project page) — the reviewer dropdown is now a custom themed dropdown that groups reviewers by research pillar (with an "Unassigned" group); pillar headers are maroon, hover/selection follow the brand colour, and the menu is fixed-positioned so it extends outside the modal instead of being clipped',
                            'Fixed editing a research call — the Edit Deadlines modal submitted an empty program title, causing "Column program_title cannot be null"; the stray field is removed and the update now ignores an empty title so the existing name is preserved',
                            'Projects list — added an "LPI" column after Project ID (showing the LPI name with their email beneath it) and merged the separate Grant and Category columns into a single "Grant" column (grant code with the grant type beneath it)',
                            'Report Cards page now lists only graded projects — a project appears only when its Progress and/or Final report grading is completed (publish status no longer "pending"); an info notice states that only progress/final graded projects are displayed here, and the cycle/grant filter dropdowns are built from that same graded set',
                            'Help Center gained a "System workflow" tab — an illustrated end-to-end walkthrough of the whole lifecycle available to every role (guests included): the happy-path flow and rejection loop diagrams, a phases/actors/statuses table, assignment & per-reviewer claiming, grading drafts and the consensus rules, all three rejection scenarios, extended progress (PR2), the full status reference, notification-bell tasks per role, event e-mails and deadline/locking rules; the General manual\'s descriptions were refreshed to match the new tab set',
                            'Projects list Status filter now offers every workflow status (built from the canonical status list: Imported, Assigned, Claimed, Proposal Rejected, Progress Added/Reviewed/Rejected, PR2 variants, Final Added/Rejected, Graded, Reviewer Unassigned, plus the computed Unregistered) and each option filters by the project\'s LATEST status — the exact value shown in its Status pill; previously only Unregistered/Registered/Claimed were selectable and those two matched any project that had ever passed through the state',
                            'LPI & Reviewer manuals + AI assistant prompt aligned with the current workflow — the reviewer manual no longer claims a single-reviewer model (several reviewers can grade the same project, each with an invisible-to-others grade set; claims are per reviewer; the stage advances only once every assigned reviewer submitted), both manuals now describe the notification-bell pending tasks (admin rejection review queue, LPI resubmission task that clears on resubmit), and the AI prompt gained a Notification Bell section plus updated lifecycle/grade-behavior/assignment/rejection-flow wording',
                            'Notification bell fixed — the admin now gets a "N rejected report(s) awaiting your review" pending task whenever a report-type rejection still needs his review (same rules as the Review Rejection action: latest of the rejection round is still a rejection, ethical/other rejections skip it, graded projects never count), so a rejected progress/final report actually surfaces on the bell; the LPI\'s "needing resubmission" task now keys on the latest workflow status instead of an all-time check that permanently hid it for any project that had ever uploaded a progress report — it now appears on rejection and clears automatically on resubmission',
                            'Workflow hardening (audit fixes) — grading now requires the reviewer\'s OWN claim server-side (direct URL/POST can no longer skip Accept/Reject); un-assigning a reviewer re-runs the consensus so removing a blocking reviewer completes the stage instead of wedging it, and an admin may now also pull a claimed-but-silent reviewer (with reason, notification and audit status); a completed stage is frozen (a reviewer added later stores his set but can never re-open or flip the status); proposal accepted/rejected mails now fire once per round instead of once per reviewer; the LPI proposal-upload API refuses non-admins (policy enforced, not just hidden); removed the legacy consensus-bypass endpoints /grading/{id}/submit-grade, /grading/{id}/final-grade and /workflow/submit-progress-review',
                            'Consensus grading — the workflow status now advances only when EVERY assigned reviewer has submitted his own set for a stage: a first (or early) submission keeps the stage open instead of closing it for the other reviewers, any rejected set wins when the consensus completes (reason/type persisted on the grading row), the LPI "graded" email fires once at completion, and the grading form availability is now per-user (each reviewer\'s form stays open until he personally submits); Evaluate Projects became the admin\'s personal queue (rows leave only once HE submits), final grading follows the same rules, and a v2 resubmission now re-opens EVERY reviewer\'s set',
                            'Help Center menu & tabs tidied — the top navigation "?" dropdown now only lists Help Center and Our Team (the direct LPI/Reviewer manual links were removed), and the Help Center itself shows role-based tabs: an LPI sees only General + LPI, a reviewer only General + Reviewer, admins keep all four; opening a restricted tab by URL (e.g. ?tab=admin) falls back to the user\'s own manual',
                            'LPI & Admin manuals updated to match current behaviour — the LPI manual no longer lists "Upload Proposal" as an LPI action (proposals are uploaded/managed by the research office only; view & download remain) and now states that commitments are set at registration and can only be updated by an administrator afterwards (registration table, project-page tour, glossary and troubleshooting updated); the admin manual gained the matching "Edit Commitments" entry under Grading Oversight',
                            'Multi-reviewer grading — a project can now have several reviewers assigned at the same time: the admin adds reviewers alongside the existing ones (Assign modal, Reviewer Assignment page and Projects action menu all gained per-reviewer Add/Un-assign controls), each reviewer claims (Accept/Reject Proposal) for himself before his own grading form unlocks, and every reviewer/admin works on his own independent grading set — a reviewer sees only his own draft (editable until he saves, read-only afterwards) or a blank form if he has not started, while the earlier reviewer\'s scores stay stored; new claimed_at column on projects_reviewers with backfill',
                            'Fixed AJAX calls failing with "Failed to load action." after moving to XAMPP — hand-written root-relative URLs in JavaScript (workflow modals incl. Accept/Reject Proposal, proposal uploads, register wizard, view-grade, email-template previews, teams edit) and in the grading save forms now resolve against the app base URL, so every feature works when the app runs in a subfolder such as /lrts',
                            'Grading page — admins can now edit a project\'s commitments: an "Edit Commitments" button on the Commitments tab opens an inline editor (Publications / IP & Innovation / Students & Training groups) with Save/Cancel; saving updates the commitments and refreshes the comparison table — reviewers keep full view but never see the edit controls (enforced server-side, admin-only route)',
                            'Consistent project ordering across the app — every project table (Projects list, My Projects, My Assignments, Reviewer Assignment, Extend Progress, Evaluate Projects, Upload Reports, File Explorer, Graded Projects, Report Cards, Budget Utilization, dashboards and progress reports) is now sorted by research call title A→Z and then by project ID ascending, instead of the mixed date/received-order sorting used before',
                            'Research Calls page — the Edit Deadlines modal is now wider (760px) with the three deadline fields in a single row, and opens with the line "Editing deadlines of the research call <title>" where the research call title is bold and highlighted',
                            'Reviewer Assignment, Extend Progress Report, Upload Reports, Evaluate Projects (Pending Reviews) and Report Cards pages — the "LPI" column now sits right after Project ID and shows the LPI name with their email beneath it (matching the projects list styling)',
                            'LPI uploads hardened — Progress Report 2 uploads now require the project\'s extended-progress flag server-side (mirroring the grading-side gate), so the PR2 type that the UI hides for non-extended projects can no longer be posted directly to the upload endpoint',
                            'Resubmission window precision fixed — the upload/form lock checks (and the admin rejection review) now compare status-history records by their monotonic id instead of created_at, so a reviewer grading in the same second as the admin\'s "Send to LPI" review reliably closes the resubmission window; previously same-second actions could leave the LPI upload unlocked after the project had already been re-graded',
                            'Automated test suite extended — new LpiReportUploadTest suite (25 scenarios from docs/TEST-SCENARIOS-LPI-UPLOADS.md): progress/final/PR2/readiness/ethical uploads, deadline locks & resubmission unlocks, owner/guest/role gates, v1 replace semantics, the full rejection → admin review → v2 chain (v1 kept, every reviewer set archived & re-opened, window closing after the next grading) and the project-data freeze after the final deadline',
                            'Fixed Graded Projects cycle filter — the Cycle dropdown filtered on a non-existent projects.cycle_id column (SQL error / empty page); it now resolves through the program\'s cycle, so filtering the Graded Projects list by cycle works',
                            'Automated test suite extended — new ReviewerGradingTest suite (39 scenarios from docs/TEST-SCENARIOS-REVIEWER-GRADING.md): grading-page access gates, progress/final save validation & deadline/stage/extended-progress locks, draft-vs-submit publish semantics, the final total (= scoreA+scoreB+scoreC), the verification AJAX endpoint (IDOR all-or-nothing guard + admin bypass), the Graded Projects page, the View-Grade modal and the admin Evaluate Projects queue (per-admin rows, drafts still owed, reviewer submissions not clearing the admin\'s row)',
                            'Full lifecycle regression added (scenario I-01 in docs/TEST-SCENARIOS-GRADING.md) — one end-to-end test covering: reviewer rejects the proposal → removed & blocked from re-assignment → admin re-assigns a different reviewer → two-reviewer consensus where one accepts and one rejects → admin forwards the rejection → LPI uploads v2 (both sets archived & re-opened, claims preserved) → the rejecting reviewer re-grades v2 and consensus completes',
                            'Fixed Ethical/Other rejection deadlock — an ethical or "other" report rejection used to leave the project stuck at "rejected" forever: the reviewer\'s in-place edit never cleared the status, so the LPI\'s progress upload stayed locked indefinitely. Re-evaluating the stage on the reviewer\'s re-grade now resolves the rejection (status advances, the upload hold lifts); a different or late reviewer still cannot reopen a completed stage',
                            'Ethical/Other rejections now route through the admin — previously an ethical or "other" report rejection was invisible to the admin (no bell task, no Review Rejection action) and could only be resolved by the reviewer editing their own grading. Now the admin is notified, reviews it and forwards it to the LPI just like a report rejection; the LPI is asked to re-send the ETHICAL APPROVAL document (never the progress/final report, whose upload stays locked), after which the reviewer re-reviews and the rejection resolves',
                            'Final report rejection lifecycle regression added (scenario I-03 in docs/TEST-SCENARIOS-GRADING.md) — end-to-end test covering: reviewer rejects the final report → consensus records final_rejected → admin forwards → LPI uploads a final v2 past the deadline (v1 kept, every final grading set archived & reset) → reviewer re-grades the v2 → the project reaches the terminal Graded status',
                            'Ethical rejection on the final report verified (scenario I-04) — the admin forwards it, the LPI re-sends the ethical approval (both the final and readiness uploads stay locked, since readiness shares the final rejection hold), and the reviewer\'s re-review completes the project to Graded',
                            'LPI upload & outcomes hardening (bug fixes) — (a) a readiness-only final-form submit no longer records "Final Added" (the reviewer was blocked by a status that claimed a final report existed); (b) an upload\'s download link now pins submission_id, so a v2 upload no longer returns a URL serving the stale v1 file; (c) report uploads now write the new file BEFORE retiring the old rows and check the write result, so a failed write can no longer destroy the previous version and leave a dangling record; (d) saveOutcomes now upserts instead of delete-and-reinsert — re-saving can no longer wipe rows added record-by-record (resetting verification flags, scores and DOI links, or deleting IP/contribution rows outright); (e) a text-only progress form save no longer resets the reviewers\' in-progress grading set (only a new uploaded file does)',
                            'Auto-grade integrity fix — the final-report auto-grade (sections A and B) now only counts outcomes that have actually been verified (by CrossRef or by the reviewer\'s Verify toggle); previously any unverified outcome raised the score, so a batch of unverified entries could inflate the reviewer\'s auto-grade hint',
                            'Readiness report now bound to the FINAL submission period — it was gated by the Progress Report 2 deadline (and the UI gated it by the final deadline), so the two disagreed and a readiness upload could be refused (or allowed) at the wrong time. Readiness now follows the final report deadline and rejection hold, matching the UI and the rule that outcomes/students/readiness/ethical/final are all bound to the final window (progress stays bound to the progress period)',
                            'Full LPI submission-lifecycle regression added (scenario H-01 in docs/TEST-SCENARIOS-LPI-UPLOADS.md) — one end-to-end test covering both submission periods: inside them the LPI uploads and replaces the progress/final reports, uploads readiness + ethical approval, and creates outcomes/students; once each period closes its own artefacts and the project data are locked (refused uploads leave the stored files untouched)',
                            'Research Call report booklet — a printable A4 booklet page at /programs/{id}/booklet (admin-only) for any research call, with the QU research logo and compact project-detail report styling: printer-safe Arial/Helvetica typography, 9px body text, 8.5px tables, maroon hairline section headings, compact spacing and a print-only Back / Print / Save PDF toolbar. It retains the call-specific content (description, cumulative chart, summary, workflow, grading, budget, research output, people arranged in two columns and project appendix) and now flows continuously across A4 pages, repeating table headers without forced breaks between content groups. Admins can open it from each research call\'s Actions menu or directly by URL',
                            'DataTable export buttons (Copy / CSV / Excel / PDF / Print) — every data table in the app now shows a compact, left-aligned export toolbar with light-weight labels, Font Awesome line (regular) icons and a hairline light-maroon border; Excel uses SheetJS and PDF uses jsPDF + AutoTable (both newly loaded). Columns tagged with the no-export class (e.g. an Actions column) are excluded from every export, PDF exports use landscape A4, and the DataTables defaults are now applied immediately after the libraries load instead of inside a DOMContentLoaded listener — previously page tables initialized via $(document).ready inherited the defaults too late, so the export buttons never appeared. Pages that override the DataTables dom option had the buttons slot (B) added so they pick up the same toolbar',
                            'Cycle summary report — the "Send Email Reminders" buttons verified and covered by regression tests (guest/admin gating, the MAIL_ENABLED guard, column validation, LPI and reviewer recipient routing, and the no-pending short-circuit); each queued ProjectReminderMail is matched by a ReportReminderSent log row. Note: MAIL_ENABLED is currently false in .env, so the buttons report "Email sending is disabled" until it is switched on',
                            'Fixed the reviewer notification bell showing the same task twice — "N projects pending your review" and "N accepted projects pending grading" ran an identical query (assigned + not Graded), so both always listed the same projects. "Ready to grade" now counts only projects the reviewer has accepted (claimed) AND whose progress/final report exists with its deadline passed, and it is only shown when that is a strict subset of the queue — so when every pending project is already ready the bell shows a single task instead of repeating the same records',
                            'Printable project detail page — the project detail page (/projects/{id}) gained a "Printable Version" button that opens a compact A4 summary at /projects/{id}/printable. Print-first, dense layout with printer-safe typography, hairline rules, complete labels and repeating table headers. The QU-logo report shows Commitments as a full-width section with Publications in the left column and IP & Innovation plus Students & Training in the right; full-width Project files follow immediately, then outcome tables. Binary commitment verification values display as Yes/No and commitment statuses match the detail page (Behind / In Progress / On Track). Grading tables give comments more width for longer text. Status History was removed and reviewer names are omitted from grading. The detail-page Actions, Printable Version and Back to Projects controls use compact sizing. The print toolbar provides Back / Print / Save PDF, reviewers retain the restricted view (no Outcomes/Grading), and projects without a research call remain supported',
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
