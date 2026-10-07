<div class="manual-layout">
    {{-- Table of contents --}}
    <aside class="manual-toc">
        <div class="panel">
            <div class="panel-head"><h2 style="font-size:13px;"><i class="fas fa-list-ul"></i> Contents</h2></div>
            <div class="panel-body" style="padding:8px;">
                <a href="#intro">1. Introduction &amp; Scope</a>
                <a href="#roles">2. Authentication &amp; Roles</a>
                <a href="#dashboard">3. Admin Dashboard</a>
                <a href="#research-calls">4. Research Calls</a>
                <a href="#config">5. Cycles, Grants &amp; Pillars</a>
                <a href="#users">6. Users &amp; Roles</a>
                <a href="#assignment">7. Reviewer Assignment</a>
                <a href="#reports">8. Report Management</a>
                <a href="#grading">9. Grading Oversight</a>
                <a href="#budget">10. Budget Utilization</a>
                <a href="#email">11. Email &amp; Templates</a>
                <a href="#settings">12. System Settings</a>
                <a href="#files">13. File Downloads</a>
                <a href="#content">14. Announcements &amp; Team</a>
                <a href="#troubleshooting">15. Troubleshooting</a>
                <a href="#glossary">16. Glossary</a>
            </div>
        </div>
    </aside>

    {{-- Manual body --}}
    <div class="manual-body">

        <section id="intro" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-circle-info"></i> 1. Introduction &amp; Scope</h2></div>
            <div class="panel-body">
                <p class="manual-lead">The Administrator role governs the configuration and operation of RTS: setting up research calls, managing users and reference data, assigning reviewers, overseeing grading and reports, and maintaining the system's settings.</p>
                <p>This manual is the operational reference for administrators. It covers the administrative workflows, the data model behind them, and the settings that control platform behaviour.</p>
                <div class="manual-callout"><i class="fas fa-user-shield"></i> Administrative menus are visible only to accounts with the Admin role. Composite-role users switch to the Admin role from the top bar.</div>
            </div>
        </section>

        <section id="roles" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-user-shield"></i> 2. Authentication &amp; Roles</h2></div>
            <div class="panel-body">
                <p>RTS supports <strong>QU Single Sign-On (SAML/ADFS)</strong> and local email/password authentication. Accounts are matched by <strong>QU ID</strong>; only active accounts may sign in.</p>
                <p>The platform recognises four role types:</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Role</th><th>Capabilities</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Admin</strong></td><td>Full configuration, research-call setup, user management, reviewer assignment, oversight, settings.</td></tr>
                            <tr><td><strong>LPI</strong></td><td>Registers projects, submits reports and outcomes.</td></tr>
                            <tr><td><strong>Reviewer</strong></td><td>Decides proposals, grades reports, verifies evidence.</td></tr>
                            <tr><td><strong>LPI+Reviewer</strong></td><td>Composite role; a role switcher appears in the top bar.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="dashboard" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-table-cells-large"></i> 3. Admin Dashboard</h2></div>
            <div class="panel-body">
                <p>The admin dashboard provides a system-wide overview: KPI cards (projects by stage), a distribution chart, and active/inactive research calls. It also surfaces operational alerts such as projects pending reviewer assignment and missing reports.</p>
                <p>Use the dashboard to triage daily work, then drill into the specific module from the sidebar.</p>
            </div>
        </section>

        <section id="research-calls" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-arrows-rotate"></i> 4. Research Calls</h2></div>
            <div class="panel-body">
                <p>A <strong>research call</strong> is a grant-cycle instance (grant × cycle) that groups projects and defines their reporting deadlines. Create and manage them under <strong>Research Calls</strong>.</p>
                <p><strong>Creating a call.</strong> Supply the grant, cycle and deadlines. You can import projects in the same step by uploading:</p>
                <ul class="manual-steps">
                    <li>An <strong>Excel workbook</strong> of projects (project ID, title, LPI, pillars, colleges, budgets, and student-grant columns where applicable), and</li>
                    <li>A <strong>proposals ZIP</strong> containing the proposal PDFs.</li>
                </ul>
                <p>Proposal PDFs are matched to projects by project ID; files that cannot be matched are listed in the import result and are <strong>not stored</strong>. The import is idempotent — re-running it updates existing projects rather than duplicating them.</p>
                <p><strong>Filtering.</strong> The list supports cascading filters — <em>Cycle → Grant Type → Grant → Status → Visibility</em> — so each level narrows the next.</p>
                <p><strong>Visibility.</strong> Each call can be shown or hidden from LPIs using the visibility toggle (or the dedicated Show/Hide page).</p>
                <div class="manual-callout"><i class="fas fa-lightbulb"></i> When importing, the proposals ZIP is extracted to a staging area and only matched files are written to storage — unmatched files are discarded.</div>
            </div>
        </section>

        <section id="config" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-sliders"></i> 5. Cycles, Grants &amp; Pillars</h2></div>
            <div class="panel-body">
                <p>Reference data underpins every project. Maintain it under <strong>System Settings</strong>:</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Entity</th><th>Purpose</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Cycles</strong></td><td>The reporting years (e.g. 2024, 2025). Research calls reference a cycle.</td></tr>
                            <tr><td><strong>Grant Types</strong></td><td>Categories of funding (e.g. Regular, Student) used to classify grants.</td></tr>
                            <tr><td><strong>Grants</strong></td><td>The funding schemes (grant code, name, category) available to research calls.</td></tr>
                            <tr><td><strong>Research Pillars</strong></td><td>Multi-level research areas; projects select one or more and reviewers are matched by pillar.</td></tr>
                            <tr><td><strong>Colleges / Institutes</strong></td><td>Administering units with short codes used in imports.</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>Changes to reference data affect new assignments and imports; existing projects retain their recorded selections.</p>
            </div>
        </section>

        <section id="users" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-users"></i> 6. Users &amp; Roles</h2></div>
            <div class="panel-body">
                <p>Manage accounts under <strong>Users</strong>. Each user has a name, email, role, optional <strong>QU ID</strong>, department, college, pillar affiliations and an active flag.</p>
                <ul class="manual-steps">
                    <li><strong>Role</strong> determines the menus and actions available; composite roles are supported.</li>
                    <li><strong>QU ID</strong> is the key used for SSO matching — keep it accurate for every user who signs in via SSO.</li>
                    <li><strong>Active</strong> accounts can sign in; deactivating a user blocks both SSO and password login.</li>
                    <li><strong>Pillars</strong> drive reviewer eligibility suggestions during assignment.</li>
                </ul>
                <p>Users are also auto-created during project import when an unknown LPI email is encountered; review those accounts and complete their QU ID and role.</p>
            </div>
        </section>

        <section id="assignment" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-user-check"></i> 7. Reviewer Assignment</h2></div>
            <div class="panel-body">
                <p>The <strong>Reviewer Assignment</strong> page lists projects that need reviewer attention. A project appears when it is <strong>unassigned</strong>, when it is <strong>Assigned</strong> and awaiting the reviewer's claim, when a proposal was <strong>rejected</strong>, or when a progress report was <strong>rejected</strong> (so you can reassign).</p>
                <p>RTS uses a <strong>single-reviewer</strong> model. Assigning a reviewer replaces any existing assignment for that project. A reviewer who previously rejected a project's proposal cannot be assigned to it again.</p>
                <div class="manual-callout"><i class="fas fa-shield-halved"></i> Assigning a reviewer records an <em>Assigned</em> status and (if email is enabled) notifies the reviewer automatically.</div>
            </div>
        </section>

        <section id="reports" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-cloud-arrow-up"></i> 8. Report Management</h2></div>
            <div class="panel-body">
                <p>Administrators can act on reports on behalf of LPIs where necessary:</p>
                <ul class="manual-steps">
                    <li><strong>Upload Reports</strong> — upload proposal, progress, final or readiness reports for a project, and flag which are still missing.</li>
                    <li><strong>Extend Progress Report</strong> — adjust reporting deadlines to extend the submission window for a call.</li>
                    <li><strong>Evaluate Projects</strong> — review projects pending assessment.</li>
                </ul>
                <p>Uploaded reports follow the same versioning and storage rules as LPI submissions and are tracked in the project's submission history.</p>
            </div>
        </section>

        <section id="grading" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-scale-balanced"></i> 9. Grading Oversight</h2></div>
            <div class="panel-body">
                <p>Administrators oversee grading rather than performing it. Key surfaces:</p>
                <ul class="manual-steps">
                    <li><strong>Report Cards</strong> — published grading summaries for projects, printable (A4).</li>
                    <li><strong>Reviewer Grading</strong> — reviewer activity and performance across assigned projects.</li>
                    <li><strong>Grading Form</strong> (System Settings) — configure the grading sections and their scoring.</li>
                    <li><strong>Scores</strong> (System Settings) — the weighted score map that powers auto-scoring of outcomes.</li>
                </ul>
                <p>Auto-scores are derived from outcomes versus commitments; you can toggle their visibility to reviewers from the AI settings.</p>
            </div>
        </section>

        <section id="budget" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-coins"></i> 10. Budget Utilization</h2></div>
            <div class="panel-body">
                <p>Where projects carry budgets, the <strong>Budget Utilization</strong> module tracks spend against allocation. You can issue a <strong>utilisation reminder</strong> to an LPI, which emails a summary of total, actual, available and utilisation percentage.</p>
            </div>
        </section>

        <section id="email" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-envelope"></i> 11. Email &amp; Templates</h2></div>
            <div class="panel-body">
                <p>RTS sends two classes of email:</p>
                <ul class="manual-steps">
                    <li><strong>Automatic (event) emails</strong> — triggered by workflow events (reviewer assigned, proposal decision, registration confirmed, project imported, progress submitted, report graded). These obey the deployment's email switch (<code>MAIL_ENABLED</code>); when off, they are suppressed and not logged.</li>
                    <li><strong>Manual emails</strong> — composed on the <strong>Send Email</strong> page and sent to selected users by role. Manual sends deliberately bypass the switch.</li>
                </ul>
                <p><strong>Email Templates</strong> holds the editable subject/body for each event. System templates are tagged <em>System</em>, are protected from deletion, and support placeholders such as <code>*name*</code>, <code>*old_project_id*</code>, <code>*project_title*</code>, <code>*grant_title*</code>, <code>*cycle*</code> and <code>*link*</code>. Every send is recorded in the <strong>Email Send Log</strong> with its status.</p>
            </div>
        </section>

        <section id="settings" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-cog"></i> 12. System Settings</h2></div>
            <div class="panel-body">
                <p>System Settings is organised into tabs:</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Tab</th><th>Controls</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Gauges</strong></td><td>Threshold gauges used across dashboards and reports.</td></tr>
                            <tr><td><strong>Grading Form</strong></td><td>Configuration of the grading sections.</td></tr>
                            <tr><td><strong>Scores</strong></td><td>The weighted score values per outcome type that drive auto-scoring.</td></tr>
                            <tr><td><strong>AI Assistant</strong></td><td>Enable/disable the AI assistant, set the model/mode, and toggle auto-grade visibility.</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>Score changes take effect on subsequent grading calculations; existing gradings are not retroactively altered.</p>
            </div>
        </section>

        <section id="files" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-folder-tree"></i> 13. File Downloads</h2></div>
            <div class="panel-body">
                <p>The <strong>File Downloads</strong> module exports documents as ZIP archives — either for a single project (all its proposals and reports) or for an entire research call. Archives are organised by project ID and document type.</p>
                <p>Proposal and report filenames use the project's file-safe ID, so exports remain consistent even when a project ID contains characters such as "/".</p>
            </div>
        </section>

        <section id="content" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-bullhorn"></i> 14. Announcements &amp; Team</h2></div>
            <div class="panel-body">
                <p><strong>Announcements</strong> are targeted notices published to a role audience (Admin, LPI, Reviewer) and shown on the recipients' dashboards and the Announcements page.</p>
                <p>The <strong>Team</strong> module maintains the public "Our Team" page content.</p>
            </div>
        </section>

        <section id="troubleshooting" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-screwdriver-wrench"></i> 15. Troubleshooting</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Symptom</th><th>Likely cause / resolution</th></tr></thead>
                        <tbody>
                            <tr><td>Project not appearing for assignment</td><td>It may already be assigned and not in a reassignment state, or filtered out by cycle/program.</td></tr>
                            <tr><td>Proposal PDFs unmatched after import</td><td>The PDF filename did not match a project ID; the file was discarded. Rename to the project ID and re-import.</td></tr>
                            <tr><td>Automatic emails not sending</td><td>The email switch is off, or the SMTP host is unreachable. Manual Send Email still works.</td></tr>
                            <tr><td>Reviewer cannot grade</td><td>The reporting deadline has not passed, or the report was not submitted.</td></tr>
                            <tr><td>User cannot sign in via SSO</td><td>Account inactive or QU ID not set/mismatched.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="glossary" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-book"></i> 16. Glossary</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Term</th><th>Definition</th></tr></thead>
                        <tbody>
                            <tr><td>Research Call</td><td>A grant × cycle instance grouping projects with shared deadlines.</td></tr>
                            <tr><td>Reference data</td><td>Cycles, grants, grant types, pillars and colleges.</td></tr>
                            <tr><td>Assignment</td><td>Linking a reviewer to a project for proposal decision and grading.</td></tr>
                            <tr><td>Auto-score</td><td>A suggested grading section score computed from outcomes vs commitments.</td></tr>
                            <tr><td>Email switch</td><td>The <code>MAIL_ENABLED</code> flag gating automatic event emails.</td></tr>
                            <tr><td>Report Card</td><td>The published grading summary for a project.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="manual-callout"><i class="fas fa-circle-question"></i> For anything not covered here, contact the system maintainers.</div>
            </div>
        </section>

    </div>
</div>
