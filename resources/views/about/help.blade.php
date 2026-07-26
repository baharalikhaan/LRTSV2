@extends('layouts.app')

@section('title', 'Help Center — RTS')

@section('content')
<div class="help-page">

    {{-- Page header --}}
    <div class="panel" style="margin-bottom:22px;">
        <div class="panel-body" style="padding:24px 28px; text-align:center;">
            <i class="fas fa-circle-question" style="font-size:36px; color:var(--color-brand-500); margin-bottom:10px;"></i>
            <h1 style="font-size:24px; font-weight:700; color:var(--color-ink-900); margin:0 0 4px;">Help Center</h1>
            <p style="font-size:13.5px; color:var(--color-ink-500); margin:0;">Interactive guide to using RTS — select your role below to get started.</p>
        </div>
    </div>

    {{-- Role-based tab navigation --}}
    <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;" id="helpTabs">
        <button class="btn-primary" style="font-size:12.5px; padding:9px 18px;" onclick="switchHelpTab('admin')">
            <i class="fas fa-user-shield"></i> Administrator
        </button>
        <button class="btn-secondary" style="font-size:12.5px; padding:9px 18px;" onclick="switchHelpTab('lpi')">
            <i class="fas fa-user-tie"></i> LPI
        </button>
        <button class="btn-secondary" style="font-size:12.5px; padding:9px 18px;" onclick="switchHelpTab('reviewer')">
            <i class="fas fa-user-check"></i> Reviewer
        </button>
        <button class="btn-secondary" style="font-size:12.5px; padding:9px 18px;" onclick="switchHelpTab('general')">
            <i class="fas fa-globe"></i> General
        </button>
    </div>

    {{-- ===== ADMIN HELP ===== --}}
    <div id="help-admin" class="help-section">
        <div class="panel" style="margin-bottom:18px;">
            <div class="panel-head" style="border-left:3px solid var(--color-brand-500);">
                <h2><i class="fas fa-user-shield" style="color:var(--color-brand-500);"></i> Administrator Guide</h2>
            </div>
        </div>

        <div class="help-card-grid">
            {{-- Dashboard --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-brand-100);color:var(--color-brand-600);">
                        <i class="fas fa-table-cells-large"></i>
                    </div>
                    <h3>Dashboard</h3>
                    <p>Your admin dashboard shows an overview of the entire system: active cycles and research calls, total projects, and user counts. The <strong>Projects by Status</strong> table gives a quick view of distribution across all lifecycle stages, and <strong>Active Research Calls</strong> lists current running research calls with their project counts.</p>
                </div>
            </div>

            {{-- Research Calls --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-sand-100);color:var(--color-sand-600);">
                        <i class="fas fa-arrows-rotate"></i>
                    </div>
                    <h3>Research Calls</h3>
                    <p>Research calls are the core organizational unit. Each research call belongs to a <strong>Grant</strong> and a <strong>Cycle</strong>. Create research calls under <em>Administration → Research Calls</em>. Set deadlines and status; active research calls appear on the dashboard. You can view project counts per research call.</p>
                    <div class="help-tip"><i class="fas fa-lightbulb"></i> A research call becomes inactive once its deadline passes. Projects under an inactive research call are still viewable.</div>
                </div>
            </div>

            {{-- Users --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-info);color:#fff;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Users</h3>
                    <p>Manage all system users under <em>Administration → Users</em>. Each user has a <strong>type</strong> that determines their role: Admin, LPI, Reviewer, or composite (e.g., LPI+Reviewer). Users with composite roles can switch between roles via the dropdown in the top command bar.</p>
                </div>
            </div>

            {{-- Announcements --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-gold-100);color:var(--color-gold-600);">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>Announcements</h3>
                    <p>Create targeted announcements under <em>Administration → Announcements</em>. Set the <strong>audience</strong> field to target specific roles (Admin, LPI, Reviewer) or leave empty for global announcements. Announcements appear on the respective dashboards.</p>
                </div>
            </div>

            {{-- Configuration --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-brand-100);color:var(--color-brand-600);">
                        <i class="fas fa-gear"></i>
                    </div>
                    <h3>Configuration</h3>
                    <p>Manage <strong>Grants</strong>, <strong>Research Pillars</strong>, <strong>Colleges/Institutes</strong>, and <strong>Cycles</strong> under the Configuration section. These are foundational data that programs and projects reference. Ensure grants and cycles are defined before creating programs.</p>
                </div>
            </div>

            {{-- Reports --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-success);color:#fff;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Reports</h3>
                    <p>Four report types are available: <strong>Research Call Status</strong>, <strong>Grant Summary</strong>, <strong>Project Status</strong>, and <strong>Pillar Summary</strong>. Each report can be exported to CSV, Excel, PDF, or printed. Use DataTables buttons in the toolbar for export.</p>
                    <div class="help-tip"><i class="fas fa-lightbulb"></i> For print-friendly output, use the Print button — it strips chrome (sidebar, command bar) and renders clean A4-format tables.</div>
                </div>
            </div>

            {{-- Workflow Management --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-gold-100);color:var(--color-gold-600);">
                        <i class="fas fa-arrows-spin"></i>
                    </div>
                    <h3>Workflow Management</h3>
                    <p>As an admin, you manage project lifecycle stages via workflow modals. From a project's detail page, use action buttons to:</p>
                    <ul style="margin:6px 0 0; padding-left:18px; font-size:12.5px;">
                        <li><strong>Assign Reviewers</strong> — Select two distinct reviewers for a project.</li>
                        <li><strong>Accept/Reject Proposal</strong> — After reviewers accept, admin can accept the full proposal with an agreement PDF.</li>
                        <li><strong>View Report Card</strong> — See the combined grading summary from both reviewers.</li>
                    </ul>
                </div>
            </div>

            {{-- Projects --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-info);color:#fff;">
                        <i class="fas fa-diagram-project"></i>
                    </div>
                    <h3>Projects</h3>
                    <p>The <strong>Projects</strong> page lists all projects in the system. Use the <em>Assign Reviewers</em> workflow to assign reviewers to projects in the <em>Registered</em> status. Each project has a detail page showing its full information, status history, and associated reviewers.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== LPI HELP ===== --}}
    <div id="help-lpi" class="help-section" style="display:none;">
        <div class="panel" style="margin-bottom:18px;">
            <div class="panel-head" style="border-left:3px solid var(--color-gold-500);">
                <h2><i class="fas fa-user-tie" style="color:var(--color-gold-500);"></i> LPI Guide</h2>
            </div>
        </div>

        <div class="help-card-grid">
            {{-- Dashboard --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-brand-100);color:var(--color-brand-600);">
                        <i class="fas fa-table-cells-large"></i>
                    </div>
                    <h3>Dashboard</h3>
                    <p>Your dashboard shows 5 stat cards: <strong>All Projects</strong>, <strong>Unregistered</strong>, <strong>Report Upload Pending</strong>, <strong>Progress Report Done</strong>, and <strong>Graded</strong>. Below the stats, you'll find breakdowns <strong>By Research Call</strong> and <strong>By Pillar</strong>, plus a <strong>LPI Contribution Summary</strong> showing your grants availed, cycles worked, research calls worked, publications, and students attached.</p>
                </div>
            </div>

            {{-- Project Registration --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-sand-100);color:var(--color-sand-600);">
                        <i class="fas fa-file-circle-plus"></i>
                    </div>
                    <h3>Project Registration</h3>
                    <p>To register a project, go to <strong>Projects</strong> from the sidebar and click <em>Register</em> on an available project. The registration wizard walks you through:</p>
                    <ol style="margin:6px 0 0; padding-left:18px; font-size:12.5px;">
                        <li>Basic project information (title, abstract, keywords)</li>
                        <li>Team members and students attached</li>
                        <li>Expected outcomes (publications, IP, student theses)</li>
                        <li>Budget and resource requirements</li>
                    </ol>
                    <div class="help-tip"><i class="fas fa-lightbulb"></i> Save your progress at each step. You can return to complete registration later.</div>
                </div>
            </div>

            {{-- Progress Reports --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-info);color:#fff;">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                    <h3>Progress Reports</h3>
                    <p>After your project is registered, you can submit <strong>Progress Reports</strong>. Go to your project's detail page and click <em>Add Progress Report</em>. You can upload supporting documents and describe achievements, challenges, and next steps.</p>
                </div>
            </div>

            {{-- Viewing Grades --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-gold-100);color:var(--color-gold-600);">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Viewing Grades</h3>
                    <p>Once both reviewers have submitted their grades, your project will show a <em>View Grades</em> button. Click it to see your <strong>Report Card</strong> — a teal-themed summary showing scores across 5 categories: Innovation, Feasibility, Methodology, Impact, and Presentation. The overall score and grade (Excellent, Good, Satisfactory, Needs Improvement) are displayed.</p>
                </div>
            </div>

            {{-- Contribution Summary --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-success);color:#fff;">
                        <i class="fas fa-chart-simple"></i>
                    </div>
                    <h3>Contribution Summary</h3>
                    <p>The <strong>LPI Contribution Summary</strong> on your dashboard shows 5 mini-gadgets: <strong>Grants Availed</strong> (distinct grants across your projects), <strong>Cycles Worked</strong>, <strong>Research Calls Worked</strong>, <strong>Publications</strong> (total count), and <strong>Students Attached</strong>. These give you a high-level view of your research portfolio.</p>
                </div>
            </div>

            {{-- Announcements --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-brand-100);color:var(--color-brand-600);">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>Announcements</h3>
                    <p>LPI-specific announcements appear on your dashboard in the Announcements panel. These are created by administrators and targeted to the LPI audience. Keep an eye on these for important deadlines, policy changes, and system updates.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== REVIEWER HELP ===== --}}
    <div id="help-reviewer" class="help-section" style="display:none;">
        <div class="panel" style="margin-bottom:18px;">
            <div class="panel-head" style="border-left:3px solid var(--color-info);">
                <h2><i class="fas fa-user-check" style="color:var(--color-info);"></i> Reviewer Guide</h2>
            </div>
        </div>

        <div class="help-card-grid">
            {{-- Dashboard --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-brand-100);color:var(--color-brand-600);">
                        <i class="fas fa-table-cells-large"></i>
                    </div>
                    <h3>Dashboard</h3>
                    <p>Your reviewer dashboard has 4 stat cards: <strong>Total Assigned</strong>, <strong>Pending Proposals</strong> (not yet accepted/rejected), <strong>Pending Gradings</strong> (accepted but not yet graded), and <strong>Graded</strong> (completed). Below, the <strong>My Reviews</strong> table lists all assigned projects, and the <strong>Announcements</strong> panel shows reviewer-targeted messages.</p>
                </div>
            </div>

            {{-- Accepting / Rejecting Proposals --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-gold-100);color:var(--color-gold-600);">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3>Accepting / Rejecting Proposals</h3>
                    <p>When a project is assigned to you, it appears in <strong>My Assignments</strong>. Click <em>Accept Proposal</em> to review the project details. You can either:</p>
                    <ul style="margin:6px 0 0; padding-left:18px; font-size:12.5px;">
                        <li><strong>Accept</strong> — Upload a signed agreement PDF. This confirms you will grade the project.</li>
                        <li><strong>Reject</strong> — Provide a reason. The admin will be notified to find a replacement reviewer.</li>
                    </ul>
                </div>
            </div>

            {{-- Grading --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-info);color:#fff;">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Grading Projects</h3>
                    <p>After accepting a proposal, the <em>Grade Project</em> button becomes available. The grading form has <strong>5 criteria</strong>, each scored 1–5:</p>
                    <ul style="margin:6px 0 0; padding-left:18px; font-size:12.5px;">
                        <li><strong>Innovation</strong> — Novelty and originality of the research</li>
                        <li><strong>Feasibility</strong> — Practical achievability within the timeframe</li>
                        <li><strong>Methodology</strong> — Soundness of the research approach</li>
                        <li><strong>Impact</strong> — Potential contribution to the field</li>
                        <li><strong>Presentation</strong> — Clarity and quality of the proposal</li>
                    </ul>
                    <p style="margin-top:6px; font-size:12.5px;">You can also add comments for each criterion. The overall score is auto-calculated.</p>
                    <div class="help-tip"><i class="fas fa-lightbulb"></i> Grading is a one-time submission. Review carefully before submitting.</div>
                </div>
            </div>

            {{-- Viewing Your Grades --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-success);color:#fff;">
                        <i class="fas fa-file-circle-check"></i>
                    </div>
                    <h3>Viewing Your Grades</h3>
                    <p>On your dashboard, the <strong>My Reviews</strong> table has a <em>View Grades</em> button for each project. Click it to see your submitted grade in a Report Card modal. This shows your scores per criterion, your comments, and the overall result.</p>
                </div>
            </div>

            {{-- Announcements --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-sand-100);color:var(--color-sand-600);">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>Announcements</h3>
                    <p>Reviewer-specific announcements appear on your dashboard. Check these regularly for updates on grading deadlines, process changes, and important notices from the research office.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== GENERAL HELP ===== --}}
    <div id="help-general" class="help-section" style="display:none;">
        <div class="panel" style="margin-bottom:18px;">
            <div class="panel-head" style="border-left:3px solid var(--color-ink-400);">
                <h2><i class="fas fa-globe" style="color:var(--color-ink-500);"></i> General Guide</h2>
            </div>
        </div>

        <div class="help-card-grid">
            {{-- Navigation --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-brand-100);color:var(--color-brand-600);">
                        <i class="fas fa-compass"></i>
                    </div>
                    <h3>Navigation</h3>
                    <p>The sidebar on the left provides access to all sections. Use the <strong>hamburger menu</strong> on mobile to toggle the sidebar. The top <strong>command bar</strong> has search, role switcher (for composite users), notifications bell, and your user menu with logout.</p>
                </div>
            </div>

            {{-- Role Switching --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-gold-100);color:var(--color-gold-600);">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Role Switching</h3>
                    <p>If you have multiple roles (e.g., LPI+Reviewer), use the <strong>role dropdown</strong> in the top command bar to switch between them. Each role has its own dashboard and available actions. The active role is shown in the dropdown.</p>
                </div>
            </div>

            {{-- DataTables --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-info);color:#fff;">
                        <i class="fas fa-table"></i>
                    </div>
                    <h3>Working with Tables</h3>
                    <p>All data tables support:</p>
                    <ul style="margin:6px 0 0; padding-left:18px; font-size:12.5px;">
                        <li><strong>Search</strong> — Type in the search box to filter rows in real time.</li>
                        <li><strong>Sorting</strong> — Click column headers to sort ascending/descending.</li>
                        <li><strong>Export</strong> — Use the toolbar buttons to copy, export as CSV/Excel/PDF, or print.</li>
                        <li><strong>Pagination</strong> — Choose how many rows to show per page (10, 25, 50, 100, or All).</li>
                    </ul>
                </div>
            </div>

            {{-- Filters & Search --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-sand-100);color:var(--color-sand-600);">
                        <i class="fas fa-magnifying-glass"></i>
                    </div>
                    <h3>Search & Filters</h3>
                    <p>The command bar includes a global search field. Use it to search for project titles, applicant names, research call names, or project IDs. Results update as you type. Each table also has its own column-specific filtering.</p>
                </div>
            </div>

            {{-- Notifications --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-brand-100);color:var(--color-brand-600);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Notifications</h3>
                    <p>The bell icon in the command bar shows a red dot when you have new notifications. Click it to open the dropdown with recent announcements. Notifications refresh automatically every 60 seconds. Click <em>View All Announcements</em> at the bottom for the full list.</p>
                </div>
            </div>

            {{-- Printing --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-success);color:#fff;">
                        <i class="fas fa-print"></i>
                    </div>
                    <h3>Printing Reports</h3>
                    <p>When printing a report, the system automatically:</p>
                    <ul style="margin:6px 0 0; padding-left:18px; font-size:12.5px;">
                        <li>Hides sidebar, command bar, and all chrome</li>
                        <li>Renders clean A4-format tables</li>
                        <li>Includes page numbers and report headers</li>
                        <li>Preserves color-coded status pills</li>
                    </ul>
                </div>
            </div>

            {{-- Keyboard Shortcuts --}}
            <div class="panel help-card">
                <div class="panel-body" style="padding:18px 20px;">
                    <div class="help-card-icon" style="background:var(--color-gold-100);color:var(--color-gold-600);">
                        <i class="fas fa-keyboard"></i>
                    </div>
                    <h3>Tips & Best Practices</h3>
                    <ul style="margin:6px 0 0; padding-left:18px; font-size:12.5px;">
                        <li>Use <strong>Chrome</strong> or <strong>Edge</strong> for the best experience.</li>
                        <li>Always <strong>save your work</strong> before navigating away from a form.</li>
                        <li>Session timeouts — if idle for too long, log in again.</li>
                        <li>Contact your system administrator if you encounter errors or need access to additional features.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.help-card-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
    margin-bottom:24px;
}
@media (max-width:680px) {
    .help-card-grid { grid-template-columns:1fr; }
}
.help-card .panel-body h3 {
    font-size:15px;
    font-weight:600;
    color:var(--color-ink-800);
    margin:0 0 6px;
}
.help-card .panel-body p {
    font-size:12.5px;
    color:var(--color-ink-600);
    line-height:1.6;
    margin:0 0 4px;
}
.help-card-icon {
    width:36px;
    height:36px;
    border-radius:6px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:15px;
    margin-bottom:10px;
}
.help-tip {
    background:var(--color-sand-50);
    border-left:3px solid var(--color-gold-400);
    padding:8px 10px;
    margin-top:8px;
    border-radius:4px;
    font-size:12px;
    color:var(--color-ink-600);
}
.help-tip i { color:var(--color-gold-500); margin-right:6px; }
.help-section ul, .help-section ol {
    font-size:12.5px;
    color:var(--color-ink-600);
    line-height:1.7;
}
</style>
@endpush

@push('scripts')
<script>
function switchHelpTab(tab) {
    // Hide all sections
    document.querySelectorAll('.help-section').forEach(function(el) {
        el.style.display = 'none';
    });
    // Show the selected section
    var target = document.getElementById('help-' + tab);
    if (target) target.style.display = 'block';
    // Update button styles
    document.querySelectorAll('#helpTabs button').forEach(function(btn) {
        btn.className = 'btn-secondary';
        btn.style.fontSize = '12.5px';
        btn.style.padding = '9px 18px';
    });
    var activeBtn = document.querySelector('#helpTabs button[onclick*="' + tab + '"]');
    if (activeBtn) {
        activeBtn.className = 'btn-primary';
        activeBtn.style.fontSize = '12.5px';
        activeBtn.style.padding = '9px 18px';
    }
}
// Default to admin tab on load
document.addEventListener('DOMContentLoaded', function() {
    switchHelpTab('admin');
});
</script>
@endpush
