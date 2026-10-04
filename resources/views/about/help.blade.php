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

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>

{{-- AI Assistant Chat Widget --}}
<div id="aiChatFab" onclick="toggleAiChat()" title="Ask AI Assistant">
    <i class="fas fa-robot"></i>
</div>

<div id="aiChatWindow" style="display:none;">
    <div class="ai-chat-header">
        <div style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-robot" style="font-size:16px;"></i>
            <div>
                <div style="font-weight:600; font-size:13px;">RTS Assistant</div>
                <div style="font-size:10px; opacity:.8;">Powered by Gemini AI</div>
            </div>
        </div>
        <button onclick="toggleAiChat()" style="background:none;border:none;color:#fff;font-size:16px;cursor:pointer;padding:4px;">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
    <div id="aiChatMessages" class="ai-chat-body">
        <div class="ai-msg ai-msg-bot">
            <div class="ai-msg-avatar"><i class="fas fa-robot"></i></div>
            <div class="ai-msg-content">Hi! I'm the RTS assistant. Ask me anything about how to use the system — dashboards, workflows, grading, or any feature.</div>
        </div>
    </div>
    <form id="aiChatForm" onsubmit="sendAiMessage(event)" class="ai-chat-footer">
        <input type="text" id="aiChatInput" placeholder="Ask about RTS..." autocomplete="off" maxlength="2000">
        <button type="submit" id="aiChatSend" title="Send"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>

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

/* AI Chat Widget — QU Theme */
#aiChatFab {
    position:fixed; bottom:24px; right:24px; z-index:99999;
    width:56px; height:56px; border-radius:50%;
    background:linear-gradient(135deg, var(--brand-600), var(--brand-800));
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-size:24px; cursor:pointer;
    box-shadow:0 4px 24px rgba(141,27,61,.4);
    transition:transform .2s, box-shadow .2s;
    border:2px solid rgba(255,255,255,.15);
}
#aiChatFab i { color:#fff !important; }
#aiChatFab:hover { transform:scale(1.08); box-shadow:0 6px 28px rgba(141,27,61,.5); }

#aiChatWindow {
    position:fixed; bottom:90px; right:24px; z-index:99999;
    width:500px; max-height:520px;
    background:#fff; border-radius:14px;
    box-shadow:0 12px 48px rgba(0,0,0,.2);
    display:flex; flex-direction:column; overflow:hidden;
    border:1px solid var(--ink-200);
}
.ai-chat-header {
    background:linear-gradient(135deg, var(--brand-700), var(--brand-900));
    color:var(--sand-50); padding:14px 16px;
    display:flex; align-items:center; justify-content:space-between;
}
.ai-chat-header i { color:var(--sand-50) !important; }
.ai-chat-body {
    flex:1; overflow-y:auto; padding:14px; max-height:360px;
    display:flex; flex-direction:column; gap:12px;
    background:var(--sand-50);
}
.ai-chat-footer {
    display:flex; gap:8px; padding:10px 12px;
    border-top:1px solid var(--ink-100); background:#fff;
}
.ai-chat-footer input {
    flex:1; border:1px solid var(--ink-200); border-radius:8px;
    padding:10px 14px; font-size:13px; outline:none; background:#fff;
    color:var(--ink-800); transition:border-color .15s;
}
.ai-chat-footer input:focus { border-color:var(--brand-500); box-shadow:0 0 0 2px rgba(141,27,61,.08); }
.ai-chat-footer input::placeholder { color:var(--ink-400); }
.ai-chat-footer button[type="submit"] {
    width:42px; height:42px; border-radius:8px; border:none;
    background:var(--brand-500); color:#fff;
    font-size:16px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:background .15s; flex-shrink:0;
    box-shadow:0 2px 8px rgba(141,27,61,.25);
}
.ai-chat-footer button[type="submit"] i { color:#fff !important; }
.ai-chat-footer button[type="submit"]:hover { background:var(--brand-600); }
.ai-chat-footer button[type="submit"]:disabled { opacity:.5; cursor:not-allowed; background:var(--ink-300); box-shadow:none; }

.ai-msg { display:flex; gap:8px; max-width:92%; }
.ai-msg-bot { align-self:flex-start; }
.ai-msg-user { align-self:flex-end; flex-direction:row-reverse; }
.ai-msg-avatar {
    width:30px; height:30px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:13px;
}
.ai-msg-bot .ai-msg-avatar { background:var(--brand-50); color:var(--brand-600); }
.ai-msg-user .ai-msg-avatar { background:var(--brand-500); color:#fff; }
.ai-msg-avatar i { color:inherit !important; }
.ai-msg-content {
    padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.55;
}
.ai-msg-bot .ai-msg-content {
    background:#fff; color:var(--ink-700);
    border:1px solid var(--ink-100);
    border-top-left-radius:4px;
}
.ai-msg-user .ai-msg-content {
    background:var(--brand-500); color:#fff;
    border-top-right-radius:4px;
}
.ai-msg-typing .ai-msg-content { padding:12px 18px; }
.ai-typing-dots span {
    display:inline-block; width:6px; height:6px; border-radius:50%;
    background:var(--ink-300); margin:0 2px;
    animation:aiDotPulse 1.2s infinite;
}
.ai-typing-dots span:nth-child(2) { animation-delay:.2s; }
.ai-typing-dots span:nth-child(3) { animation-delay:.4s; }
@keyframes aiDotPulse {
    0%,60%,100% { opacity:.3; transform:translateY(0); }
    30% { opacity:1; transform:translateY(-3px); }
}

@media (max-width:480px) {
    #aiChatWindow { width:calc(100vw - 32px); right:16px; bottom:80px; }
}

/* Markdown inside chat messages */
.ai-msg-content h1, .ai-msg-content h2, .ai-msg-content h3, .ai-msg-content h4 {
    font-size:13px; font-weight:700; color:var(--ink-800);
    margin:10px 0 4px; line-height:1.4;
}
.ai-msg-content h1 { font-size:14px; }
.ai-msg-content h2 { font-size:13.5px; }
.ai-msg-content p { margin:0 0 6px; }
.ai-msg-content p:last-child { margin-bottom:0; }
.ai-msg-content ul, .ai-msg-content ol {
    margin:4px 0 6px; padding-left:18px;
}
.ai-msg-content li { margin-bottom:2px; }
.ai-msg-content strong { font-weight:600; color:var(--ink-800); }
.ai-msg-content em { font-style:italic; color:var(--ink-600); }
.ai-msg-content code {
    font-family:'SF Mono', 'Consolas', monospace;
    font-size:11.5px; background:var(--sand-100);
    padding:1px 5px; border-radius:4px; color:var(--brand-700);
}
.ai-msg-content pre {
    background:var(--ink-800); color:#e2e8f0;
    padding:10px 12px; border-radius:8px; margin:6px 0;
    overflow-x:auto; font-size:11.5px; line-height:1.5;
}
.ai-msg-content pre code {
    background:none; padding:0; color:inherit; font-size:inherit;
}
.ai-msg-content table {
    width:100%; border-collapse:collapse; margin:6px 0; font-size:12px;
}
.ai-msg-content th, .ai-msg-content td {
    border:1px solid var(--ink-200); padding:5px 8px; text-align:left;
}
.ai-msg-content th {
    background:var(--sand-100); font-weight:600; color:var(--ink-700);
    font-size:11.5px;
}
.ai-msg-content td { color:var(--ink-600); }
.ai-msg-content blockquote {
    border-left:3px solid var(--brand-400); margin:6px 0;
    padding:4px 10px; background:var(--sand-50);
    color:var(--ink-600); font-size:12.5px; border-radius:0 4px 4px 0;
}
.ai-msg-content hr {
    border:none; border-top:1px solid var(--ink-200); margin:8px 0;
}
.ai-msg-content a {
    color:var(--brand-600); text-decoration:underline;
}
.ai-msg-content .code-lang {
    font-size:10px; color:var(--ink-400); text-transform:uppercase;
    margin-bottom:2px; display:block;
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

// ─── AI Chat Widget ────────────────────────────────────────────────
var aiChatHistory = [];
var aiChatOpen = false;

function toggleAiChat() {
    aiChatOpen = !aiChatOpen;
    var win = document.getElementById('aiChatWindow');
    var fab = document.getElementById('aiChatFab');
    if (aiChatOpen) {
        win.style.display = 'flex';
        fab.innerHTML = '<i class="fas fa-xmark"></i>';
        document.getElementById('aiChatInput').focus();
    } else {
        win.style.display = 'none';
        fab.innerHTML = '<i class="fas fa-robot"></i>';
    }
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function sendAiMessage(e) {
    e.preventDefault();
    var input = document.getElementById('aiChatInput');
    var msg = input.value.trim();
    if (!msg) return;

    var messagesEl = document.getElementById('aiChatMessages');
    var sendBtn = document.getElementById('aiChatSend');

    // Add user message
    var userDiv = document.createElement('div');
    userDiv.className = 'ai-msg ai-msg-user';
    userDiv.innerHTML = '<div class="ai-msg-avatar"><i class="fas fa-user"></i></div><div class="ai-msg-content">' + escapeHtml(msg) + '</div>';
    messagesEl.appendChild(userDiv);

    aiChatHistory.push({ role: 'user', text: msg });
    input.value = '';
    sendBtn.disabled = true;

    // Typing indicator
    var typingDiv = document.createElement('div');
    typingDiv.className = 'ai-msg ai-msg-bot ai-msg-typing';
    typingDiv.id = 'aiTyping';
    typingDiv.innerHTML = '<div class="ai-msg-avatar"><i class="fas fa-robot"></i></div><div class="ai-msg-content"><span class="ai-typing-dots"><span></span><span></span><span></span></span></div>';
    messagesEl.appendChild(typingDiv);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    fetch('{{ route("ai.chat") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ message: msg, history: aiChatHistory.slice(-10) })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var typing = document.getElementById('aiTyping');
        if (typing) typing.remove();

        var reply = data.reply || data.error || 'Sorry, something went wrong.';
        var botDiv = document.createElement('div');
        botDiv.className = 'ai-msg ai-msg-bot';
        botDiv.innerHTML = '<div class="ai-msg-avatar"><i class="fas fa-robot"></i></div><div class="ai-msg-content">' + escapeAiHtml(reply) + '</div>';
        messagesEl.appendChild(botDiv);

        aiChatHistory.push({ role: 'assistant', text: reply });
        messagesEl.scrollTop = messagesEl.scrollHeight;
        sendBtn.disabled = false;
        input.focus();
    })
    .catch(function() {
        var typing = document.getElementById('aiTyping');
        if (typing) typing.remove();

        var errDiv = document.createElement('div');
        errDiv.className = 'ai-msg ai-msg-bot';
        errDiv.innerHTML = '<div class="ai-msg-avatar"><i class="fas fa-robot"></i></div><div class="ai-msg-content" style="color:var(--color-danger);">Failed to connect. Please try again.</div>';
        messagesEl.appendChild(errDiv);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        sendBtn.disabled = false;
    });
}

function escapeAiHtml(str) {
    if (!str) return '';
    if (typeof marked !== 'undefined' && typeof DOMPurify !== 'undefined') {
        marked.setOptions({ breaks: true, gfm: true });
        var raw = marked.parse(str);
        return DOMPurify.sanitize(raw, { ADD_ATTR: ['target'] });
    }
    // Fallback if CDN fails
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    var html = div.innerHTML;
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\n/g, '<br>');
    return html;
}
</script>
@endpush
