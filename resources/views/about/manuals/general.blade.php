<div class="manual-layout">
    {{-- Table of contents --}}
    <aside class="manual-toc">
        <div class="panel">
            <div class="panel-head"><h2 style="font-size:13px;"><i class="fas fa-list-ul"></i> Contents</h2></div>
            <div class="panel-body" style="padding:8px;">
                <a href="#intro">1. What is RTS</a>
                <a href="#signin">2. Signing In</a>
                <a href="#interface">3. The Interface</a>
                <a href="#projects">4. Finding Projects</a>
                <a href="#statuses">5. Understanding Statuses</a>
                <a href="#notifications">6. Notifications &amp; Announcements</a>
                <a href="#printing">7. Printing &amp; Exporting</a>
                <a href="#profile">8. Profile &amp; Account</a>
                <a href="#support">9. Getting Help</a>
                <a href="#glossary">10. Glossary</a>
            </div>
        </div>
    </aside>

    {{-- Manual body --}}
    <div class="manual-body">

        <section id="intro" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-circle-info"></i> 1. What is RTS</h2></div>
            <div class="panel-body">
                <p class="manual-lead">The Research Tracking System (RTS) is Qatar University's platform for managing internal research grants across their whole lifecycle — from the research call and project registration, through progress and final reporting, to reviewer grading and research outcomes.</p>
                <p>RTS serves three main roles. Your experience is tailored to the role(s) assigned to your account:</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Role</th><th>What you do in RTS</th></tr></thead>
                        <tbody>
                            <tr><td><strong>LPI</strong></td><td>Register projects and submit reports, outcomes and ethical approvals.</td></tr>
                            <tr><td><strong>Reviewer</strong></td><td>Decide proposals, grade reports and verify evidence.</td></tr>
                            <tr><td><strong>Administrator</strong></td><td>Configure research calls, manage users and reference data, assign reviewers and oversee the process.</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>This General guide covers the parts of RTS common to everyone. Role-specific procedures are documented in the LPI, Reviewer and Administrator manuals, available from the tabs above.</p>
            </div>
        </section>

        <section id="signin" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-right-to-bracket"></i> 2. Signing In</h2></div>
            <div class="panel-body">
                <p>RTS uses your <strong>Qatar University account</strong>. Depending on the deployment, you sign in with either:</p>
                <ul class="manual-steps">
                    <li><strong>QU Single Sign-On (SSO)</strong> — click <em>Sign in with QU</em> and authenticate at the university IdP. No separate password is required.</li>
                    <li><strong>Email &amp; password</strong> — enter your registered email and password.</li>
                </ul>
                <p>Your account is matched to your <strong>QU ID</strong> (your university ID-based email). Only active accounts may sign in. If you cannot sign in, contact the research office to confirm your account is active and your QU ID is correct.</p>
                <div class="manual-callout"><i class="fas fa-shield-halved"></i> The first time you sign in you may be asked to confirm your <strong>nationality</strong> before you can use the application.</div>
            </div>
        </section>

        <section id="interface" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-window-maximize"></i> 3. The Interface</h2></div>
            <div class="panel-body">
                <p>The layout has a persistent <strong>sidebar</strong> on the left and a <strong>command bar</strong> across the top of the content area.</p>
                <ul class="manual-steps">
                    <li><strong>Sidebar</strong> — the main navigation, grouped into sections (Overview, Research Calls, Projects, Administration, System Settings). Only the sections relevant to your role(s) are shown; sections collapse and remember their state.</li>
                    <li><strong>Breadcrumb</strong> — the top-left of the command bar shows where you are.</li>
                    <li><strong>Role switcher</strong> — appears for accounts holding more than one role; switch roles to change the available menus and actions.</li>
                    <li><strong>Notification bell</strong> — pending tasks and reminders relevant to you.</li>
                    <li><strong>Help menu</strong> (the question-mark icon) — links to the Help Center, the LPI/Reviewer manuals (shown per your role) and the Team page.</li>
                    <li><strong>User menu</strong> (top-right) — your profile and sign-out.</li>
                </ul>
            </div>
        </section>

        <section id="projects" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-diagram-project"></i> 4. Finding Projects</h2></div>
            <div class="panel-body">
                <p>Projects are the core objects in RTS. How you reach them depends on your role:</p>
                <ul class="manual-steps">
                    <li><strong>LPI</strong> — <em>All Projects</em> lists projects assigned to you; the dashboard stat cards drill into stage-specific subsets.</li>
                    <li><strong>Reviewer</strong> — <em>My Reviews</em> on your dashboard lists your assignments.</li>
                    <li><strong>Administrator</strong> — project lists, research-call pages and the reviewer-assignment queue.</li>
                </ul>
                <p>Every project has a <strong>detail page</strong> that acts as its hub — showing the summary, current status, documents and the actions available at that stage.</p>
            </div>
        </section>

        <section id="statuses" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-list-check"></i> 5. Understanding Statuses</h2></div>
            <div class="panel-body">
                <p>A project's <strong>status</strong> tells you where it is in the workflow and who needs to act next. The main stages, in order, are:</p>
                <div class="mini-flow">
                    <div class="mini-step"><span class="ms-num">1</span><span class="ms-text"><span class="ms-label">Registered</span><span class="ms-sub">LPI completes registration</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">2</span><span class="ms-text"><span class="ms-label">Assigned / Claimed</span><span class="ms-sub">Reviewer assigned &amp; accepts</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">3</span><span class="ms-text"><span class="ms-label">Progress / Final</span><span class="ms-sub">Reports submitted</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">4</span><span class="ms-text"><span class="ms-label">Graded</span><span class="ms-sub">Completed</span></span></div>
                </div>
                <p>Reports may be <em>rejected</em> at the progress or final stage, which returns them to the LPI for correction. The status reference in the LPI manual lists every status and its meaning.</p>
            </div>
        </section>

        <section id="notifications" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-bullhorn"></i> 6. Notifications &amp; Announcements</h2></div>
            <div class="panel-body">
                <ul class="manual-steps">
                    <li><strong>Announcements</strong> — targeted messages from the research office, shown on your dashboard and the Announcements page.</li>
                    <li><strong>Notification bell</strong> — pending tasks and reminders relevant to your role.</li>
                    <li><strong>Email notifications</strong> — event-based emails (for example a project assigned, a report graded, or a resubmission requested), sent when the deployment's email switch is enabled.</li>
                </ul>
            </div>
        </section>

        <section id="printing" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-print"></i> 7. Printing &amp; Exporting</h2></div>
            <div class="panel-body">
                <p>Report cards and several reports are <strong>print-optimised for A4</strong>. Use your browser's print function from the relevant page; navigation and controls are automatically hidden in the printed output.</p>
                <p>Administrators can additionally export documents as ZIP archives (project-level or research-call-level) from the File Downloads module.</p>
            </div>
        </section>

        <section id="profile" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-id-card"></i> 8. Profile &amp; Account</h2></div>
            <div class="panel-body">
                <p>Open your profile from the top-right user menu. Your <strong>email</strong> and <strong>QU ID</strong> are provisioned by the university and shown read-only. Keep your other details (department, college) current so notifications and announcements reach you correctly.</p>
                <p>Your <strong>nationality</strong>, set on first login, is used in reporting and statistics.</p>
            </div>
        </section>

        <section id="support" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-circle-question"></i> 9. Getting Help</h2></div>
            <div class="panel-body">
                <p>This Help Center provides role-based manuals:</p>
                <ul class="manual-steps">
                    <li><strong>Administrator</strong>, <strong>LPI</strong>, <strong>Reviewer</strong> — full operational references (tabs above).</li>
                    <li><strong>General</strong> — this common guide.</li>
                </ul>
                <p>If your question is not covered, contact the research office. When reporting an issue, include the project ID, the page you were on and any error message shown.</p>
                <div class="manual-callout"><i class="fas fa-lightbulb"></i> An AI assistant may be available from the Help Center, depending on whether it is enabled for your deployment.</div>
            </div>
        </section>

        <section id="glossary" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-book"></i> 10. Glossary</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Term</th><th>Definition</th></tr></thead>
                        <tbody>
                            <tr><td>RTS</td><td>Research Tracking System — this platform.</td></tr>
                            <tr><td>Research Call</td><td>A grant × cycle instance grouping projects with shared deadlines.</td></tr>
                            <tr><td>LPI</td><td>Lead Principal Investigator.</td></tr>
                            <tr><td>Reviewer</td><td>The person who assesses proposals and grades reports.</td></tr>
                            <tr><td>Commitment</td><td>An expected output declared at registration.</td></tr>
                            <tr><td>Outcome</td><td>A concrete result recorded against a commitment.</td></tr>
                            <tr><td>Report Card</td><td>The published grading summary for a project.</td></tr>
                            <tr><td>QU ID</td><td>Your university ID-based email, used to match your account.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</div>
