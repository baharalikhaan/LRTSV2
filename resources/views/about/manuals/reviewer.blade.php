<div class="manual-layout">
    {{-- Table of contents --}}
    <aside class="manual-toc">
        <div class="panel">
            <div class="panel-head"><h2 style="font-size:13px;"><i class="fas fa-list-ul"></i> Contents</h2></div>
            <div class="panel-body" style="padding:8px;">
                <a href="#intro">1. Introduction &amp; Scope</a>
                <a href="#signin">2. Authentication</a>
                <a href="#dashboard">3. Dashboard</a>
                <a href="#assignments">4. Assignments &amp; Eligibility</a>
                <a href="#proposal">5. Proposal Decision</a>
                <a href="#grading-model">6. The Grading Model</a>
                <a href="#grading-progress">7. Progress Report Grading</a>
                <a href="#grading-final">8. Final Report Grading</a>
                <a href="#rejections">9. Rejection Types &amp; Flows</a>
                <a href="#edit">10. Editing a Grade</a>
                <a href="#verification">11. Verification Duties</a>
                <a href="#grades">12. Viewing Your Grades</a>
                <a href="#performance">13. Reviewer Performance</a>
                <a href="#notifications">14. Notifications</a>
                <a href="#profile">15. Profile &amp; Roles</a>
                <a href="#troubleshooting">16. Troubleshooting</a>
                <a href="#glossary">17. Glossary</a>
            </div>
        </div>
    </aside>

    {{-- Manual body --}}
    <div class="manual-body">

        <section id="intro" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-circle-info"></i> 1. Introduction &amp; Scope</h2></div>
            <div class="panel-body">
                <p class="manual-lead">As a <strong>Reviewer</strong> in RTS you assess the projects assigned to you — accepting or rejecting proposals, grading progress and final reports, and verifying the outcomes, people and ethical approvals that LPIs submit.</p>
                <p>This manual is the operational reference for the reviewer role. It documents the decision model, the grading methodology (including how sections are auto-scored), the rejection types and their follow-up flows, and the verification duties attached to each project.</p>
                <div class="manual-callout"><i class="fas fa-lightbulb"></i> Use the <strong>Contents</strong> list to jump to a topic. All work happens from your dashboard and the project's grading page.</div>
            </div>
        </section>

        <section id="signin" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-right-to-bracket"></i> 2. Authentication</h2></div>
            <div class="panel-body">
                <p>RTS authenticates against your Qatar University identity, either via <strong>QU Single Sign-On (SAML/ADFS)</strong> or, where configured, a local email and password.</p>
                <p><strong>Account matching.</strong> On SSO sign-in the assertion is matched against your <strong>QU ID</strong>. Only <em>active</em> accounts may sign in. Accounts without a saved nationality are prompted to set one immediately after login before the application can be used.</p>
                <div class="manual-callout"><i class="fas fa-shield-halved"></i> If SSO completes but you are returned to the login page, your account may be inactive or your QU ID unmatched. Contact the research office.</div>

                <h4 class="proc-heading"><i class="fas fa-right-to-bracket"></i> How to sign in, step by step</h4>
                <ol class="manual-steps">
                    <li><strong>Open the login page.</strong> Browse to the RTS address. When you are not signed in, the login screen appears with the Qatar University logo and either the <em>Sign in with Qatar University</em> button (SSO mode) or an email/password form (local mode).</li>
                    <li><strong>Start the sign-in.</strong> In <em>SSO mode</em>, click <em>Sign in with Qatar University</em> — you are redirected to the university identity provider (ADFS). In <em>local mode</em>, type your email and password, then click <em>Login</em>.</li>
                    <li><strong>Authenticate with the university.</strong> Enter your QU credentials on the ADFS page. RTS never sees your password — it receives a signed assertion and matches it to your reviewer account by QU ID.</li>
                    <li><strong>Answer the first-login prompt (once).</strong> If your account has no nationality saved, a window that cannot be dismissed asks you to choose one; select it and save to continue.</li>
                    <li><strong>You land on your dashboard.</strong> On success the reviewer dashboard opens. On failure you return to the login page with a clear message (inactive account, unmatched QU ID, or a temporary IdP problem).</li>
                </ol>
            </div>
        </section>

        <section id="dashboard" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-table-cells-large"></i> 3. Dashboard</h2></div>
            <div class="panel-body">
                <p>Your dashboard summarises your review workload and provides the entry point to every assignment:</p>
                <ul class="manual-steps">
                    <li><strong>Total Assigned</strong> — all projects assigned to you.</li>
                    <li><strong>Pending Proposals</strong> — assignments awaiting your accept/reject decision.</li>
                    <li><strong>Pending Gradings</strong> — accepted projects whose report is ready to grade.</li>
                    <li><strong>Graded</strong> — projects you have completed.</li>
                </ul>
                <p>The <strong>My Reviews</strong> table lists each assignment with its status and the next action; the <strong>Announcements</strong> panel carries reviewer-targeted notices.</p>

                <h4 class="proc-heading"><i class="fas fa-table-cells-large"></i> Reading the dashboard, item by item</h4>
                <ol class="manual-steps">
                    <li><strong>Total Assigned.</strong> How many projects are currently assigned to you.</li>
                    <li><strong>Pending Proposals.</strong> Assignments you have not yet accepted or rejected — these need a decision first.</li>
                    <li><strong>Pending Gradings.</strong> Accepted projects whose report is ready to grade (the reporting deadline has passed).</li>
                    <li><strong>Graded.</strong> Projects you have completed.</li>
                    <li><strong>My Reviews.</strong> The table of all your assignments — each row shows the project, its status and the next action (<em>Accept/Reject a proposal</em>, <em>Grade a report</em>, or <em>View grades</em>).</li>
                    <li><strong>Announcements.</strong> Reviewer-targeted notices from the research office.</li>
                </ol>
            </div>
        </section>

        <section id="assignments" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-check-double"></i> 4. Assignments &amp; Eligibility</h2></div>
            <div class="panel-body">
                <p>Projects are assigned by the research office. RTS uses a <strong>single-reviewer</strong> model: one reviewer owns a project's grading at any time.</p>
                <p><strong>Eligibility rules.</strong></p>
                <ul class="manual-steps">
                    <li>You are offered for assignment based on your declared research pillars.</li>
                    <li>A reviewer who has previously <em>rejected</em> a project's proposal is excluded from being re-assigned to that project.</li>
                    <li>An assignment may be withdrawn by the office before you act on it; you will be notified if un-assigned.</li>
                </ul>
                <p>When a project is assigned and you accept it, it moves to <em>Claimed</em> and you become its grader until completion.</p>
            </div>
        </section>

        <section id="proposal" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-inbox"></i> 5. Proposal Decision</h2></div>
            <div class="panel-body">
                <p>Open an assigned project to read the proposal PDF and project details, then record your decision.</p>
                <div class="mini-flow">
                    <div class="mini-step"><span class="ms-num">1</span><span class="ms-text"><span class="ms-label">Assigned</span><span class="ms-sub">Appears in My Reviews</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">2</span><span class="ms-text"><span class="ms-label">Review Proposal</span><span class="ms-sub">Read the PDF &amp; details</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">3</span><span class="ms-text"><span class="ms-label">Decide</span><span class="ms-sub">Accept or reject</span></span></div>
                </div>
                <div class="mini-decision">
                    <div class="md-row"><span class="md-tag accept"><i class="fas fa-check"></i> Accept</span><span class="md-text">Confirms you will grade the project — it moves to <em>Claimed</em> and you become its grader.</span></div>
                    <div class="md-row"><span class="md-tag reject"><i class="fas fa-xmark"></i> Reject</span><span class="md-text">Supply a reason. The project returns to the office for a different reviewer, and you are recorded as having declined it.</span></div>
                </div>
                <div class="manual-callout"><i class="fas fa-triangle-exclamation"></i> Rejection is durable: you cannot later be re-assigned to a project you have rejected. Reject only when you genuinely cannot review it (for example a conflict of interest).</div>
            </div>
        </section>

        <section id="grading-model" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-scale-balanced"></i> 6. The Grading Model</h2></div>
            <div class="panel-body">
                <p>Grading is structured into scored <strong>sections</strong>, each rated on a <strong>1–5</strong> scale with a free-text comment. Two principles govern the model:</p>
                <ul class="manual-steps">
                    <li><strong>Commitments-driven.</strong> The LPI declares expected outputs (commitments) at registration. RTS compares actual outcomes against those commitments and computes a suggested score for each auto-graded section.</li>
                    <li><strong>Reviewer authority.</strong> Auto-scores are a guide, not a verdict — you may adjust any score before submitting. Where the auto-score is disabled by the system settings, the section is scored manually.</li>
                </ul>
                <p><strong>How an auto-score is computed.</strong> For a section, RTS sums the weighted value of the recorded outcomes and divides by the weighted value of the corresponding commitments, then scales the ratio to the 1–5 range and caps it at 5:</p>
                <div class="formula">section score = min( round( (actual outcomes ÷ expected commitments) × 5 , 2 ) , 5 )</div>
                <p>If there are no commitments for a section, its auto-score is 0. The final-report grading includes a section that is <strong>never</strong> auto-scored and is always entered manually.</p>
                <p><strong>Deadline gating.</strong> A report becomes gradable only after the research call's corresponding reporting deadline has passed. Until then the grading tab is not available, even if the report has been submitted.</p>
                <p><strong>Versioning.</strong> Where multiple versions of a report exist, a <strong>version selector</strong> lets you choose which version you are grading. Resubmissions (for example <em>v2</em>) open a fresh grading form while prior gradings are retained read-only.</p>
            </div>
        </section>

        <section id="grading-progress" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-star"></i> 7. Progress Report Grading</h2></div>
            <div class="panel-body">
                <p>When an LPI submits a <strong>Progress Report</strong> (and, for extended projects, a <strong>Progress Report 2</strong>), the grading page presents the report PDF alongside your grading form. The progress form has three sections, each rated <strong>1–5</strong> with a comment:</p>
                <div class="mini-flow">
                    <div class="mini-step"><span class="ms-num">A</span><span class="ms-text"><span class="ms-label">Achievements</span><span class="ms-sub">Against objectives</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">B</span><span class="ms-text"><span class="ms-label">Publications &amp; IP</span><span class="ms-sub">Outputs recorded</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">C</span><span class="ms-text"><span class="ms-label">Students &amp; Researchers</span><span class="ms-sub">Involvement</span></span></div>
                </div>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Section</th><th>Assesses</th><th>Auto-scored</th></tr></thead>
                        <tbody>
                            <tr><td><strong>A — Achievements against objectives</strong></td><td>IP disclosures, patents, open-source software, start-ups, prototypes and cross-college activity versus commitments.</td><td>Yes</td></tr>
                            <tr><td><strong>B — Publications &amp; IP</strong></td><td>Q1–Q4 journal articles, conference articles, books, edited books and chapters versus commitments.</td><td>Yes</td></tr>
                            <tr><td><strong>C — Student &amp; Researcher Involvement</strong></td><td>Masters, Undergraduate and PhD students, and researchers attached to the project.</td><td>Yes</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>A <strong>Commitments vs Outcomes</strong> panel lets you compare the LPI's declared commitments against the actual outcomes side-by-side while scoring.</p>
                <div class="manual-callout"><i class="fas fa-file-lines"></i> If more than one version of the report exists, pick the version you are grading with the version selector before submitting.</div>

                <h4 class="proc-heading"><i class="fas fa-star"></i> How to grade a report, step by step</h4>
                <ol class="manual-steps">
                    <li><strong>Open the grading page.</strong> From your dashboard's <em>My Reviews</em> table, click <em>Grade</em> on the project (or open the project and use its grading action). The grading page opens with the report PDF on the left and your scoring form on the right.</li>
                    <li><strong>Read the proposal.</strong> Open the <em>Proposal</em> tab to re-read the original proposal and confirm what the project set out to do.</li>
                    <li><strong>Read the submitted report.</strong> Open the <em>Progress Report</em> tab (or <em>Progress Report 2</em> / <em>Final Report</em> as appropriate) to read the document the LPI submitted. If several versions exist, choose the one you are grading in the version selector.</li>
                    <li><strong>Compare commitments vs outcomes.</strong> Use the <em>Commitments vs Outcomes</em> panel to see the LPI's declared commitments beside the outcomes actually recorded.</li>
                    <li><strong>Score each section.</strong> For <em>Section A — Achievements</em>, <em>Section B — Publications &amp; IP</em> and <em>Section C — Student &amp; Researcher Involvement</em>, choose a rating from 1 to 5. RTS shows a suggested (auto-calculated) score based on outcomes vs commitments; you may adjust it. Add a short comment for each section.</li>
                    <li><strong>Verify the evidence.</strong> While scoring, toggle each outcome, student and researcher between <em>Verified</em> and <em>Pending</em>, and confirm a valid ethical-approval document is attached where required.</li>
                    <li><strong>Submit or reject.</strong> Click <em>Submit</em> to record the grade (the project advances to <em>Progress Reviewed</em>). To reject instead, switch the publish status to <em>Rejected</em>, choose a <strong>rejection type</strong> (<em>Report</em> / <em>Missing/Invalid Ethical Approval</em> / <em>Other</em>) and enter a reason — see section 9 for how each type is followed up.</li>
                </ol>
            </div>
        </section>

        <section id="grading-final" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-file-signature"></i> 8. Final Report Grading</h2></div>
            <div class="panel-body">
                <p>The <strong>Final Report</strong> is graded in the same grading page once its deadline has passed. The final form uses four scored sections with comments; three follow the same commitments-driven auto-scoring as progress grading, and one is entered manually.</p>
                <p>Review the final report, the recorded outcomes, the ethical approvals and the outcomes verification states before submitting. When you accept the final grade the project is marked <em>Graded</em> and the report card is published to the LPI.</p>
                <div class="manual-callout"><i class="fas fa-lock"></i> Grading is a deliberate act: submitting records the project's status transition and (subject to settings) notifies the LPI. Review carefully before submitting.</div>
            </div>
        </section>

        <section id="rejections" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-ban"></i> 9. Rejection Types &amp; Flows</h2></div>
            <div class="panel-body">
                <p>Instead of accepting, you may reject a report. Select the rejection type that matches the problem — each drives a different follow-up. Always include a clear comment.</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Rejection type</th><th>Meaning</th><th>Follow-up flow</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><strong>Report</strong></td>
                                <td>The report itself needs revision.</td>
                                <td>The LPI uploads a new version (for example <em>v2</em>); you are re-opened onto the new version and grade it afresh.</td>
                            </tr>
                            <tr>
                                <td><strong>Missing / Invalid Ethical Approval</strong></td>
                                <td>The ethical approval is absent or not valid.</td>
                                <td>The LPI uploads the correct document; no new report version is created and you <em>edit</em> the existing grading.</td>
                            </tr>
                            <tr>
                                <td><strong>Other</strong></td>
                                <td>Any other issue not requiring a new report.</td>
                                <td>No resubmission is requested; you <em>edit</em> the existing grading once the LPI has acted.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>For <em>Report</em> rejections the previous version and grading remain visible read-only so the history is preserved. For the other two types the grading form simply re-opens for editing.</p>
            </div>
        </section>

        <section id="edit" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-pen-to-square"></i> 10. Editing a Grade</h2></div>
            <div class="panel-body">
                <p>After an <em>Ethical</em> or <em>Other</em> rejection — or after the LPI has addressed a note — you re-open the project and use the <strong>Edit Grading</strong> action to amend your existing grade.</p>
                <p>Your previously submitted grading is shown read-only until you save the update, so you always have the prior state as reference.</p>
            </div>
        </section>

        <section id="verification" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-circle-check"></i> 11. Verification Duties</h2></div>
            <div class="panel-body">
                <p>Alongside scoring, you verify the evidence the LPI has recorded. Each verifiable item carries a state you toggle between <strong>Verified</strong> and <strong>Pending</strong>:</p>
                <ul class="manual-steps">
                    <li><strong>Outcomes</strong> — publications, IP and other outputs (check the DOI/identifier resolution and publisher badge where shown).</li>
                    <li><strong>Students</strong> — the attached students and their details.</li>
                    <li><strong>Researchers</strong> — the researchers credited on the project.</li>
                    <li><strong>Ethical approvals</strong> — confirm a valid approval document is attached and applicable.</li>
                </ul>
                <p>Verification is part of the audit trail and feeds the project's report card and the research office's oversight.</p>
            </div>
        </section>

        <section id="grades" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-file-circle-check"></i> 12. Viewing Your Grades</h2></div>
            <div class="panel-body">
                <p>From the dashboard's <strong>My Reviews</strong> table, use <strong>View Grades</strong> on any project to see the grade you submitted — section scores, comments and the overall result — in a report-card view.</p>
            </div>
        </section>

        <section id="performance" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-chart-simple"></i> 13. Reviewer Performance</h2></div>
            <div class="panel-body">
                <p>The research office reviews reviewer activity across assigned projects — projects reviewed, gradings completed and timeliness. Your dashboard reflects your live workload so you can see what remains pending.</p>
            </div>
        </section>

        <section id="notifications" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-bullhorn"></i> 14. Notifications</h2></div>
            <div class="panel-body">
                <p>Reviewer-targeted <strong>announcements</strong> appear on your dashboard and the Announcements page, and the <strong>notification bell</strong> highlights pending tasks.</p>
                <p>When the deployment's email switch is enabled, event-based emails are sent to you — for example when a project is assigned to you, when a grading reminder is issued, or when you are un-assigned from a project. These are driven by editable templates maintained by the research office.</p>
            </div>
        </section>

        <section id="profile" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-id-card"></i> 15. Profile &amp; Roles</h2></div>
            <div class="panel-body">
                <p>Open your profile from the top-right user menu. Your <strong>email</strong> and <strong>QU ID</strong> are provisioned by the university and shown read-only.</p>
                <p><strong>Composite roles.</strong> If your account holds more than one role (for example Reviewer and LPI), a <strong>role switcher</strong> appears in the top bar — switch roles to access each role's menus and actions.</p>
            </div>
        </section>

        <section id="troubleshooting" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-screwdriver-wrench"></i> 16. Troubleshooting</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Symptom</th><th>Likely cause / resolution</th></tr></thead>
                        <tbody>
                            <tr><td>Grading tab not shown</td><td>The report's deadline has not yet passed, or the report has not been submitted.</td></tr>
                            <tr><td>Cannot re-assign yourself after rejecting</td><td>By design — rejected proposals cannot be re-assigned to the same reviewer.</td></tr>
                            <tr><td>Auto-score looks low</td><td>The LPI recorded fewer outcomes than commitments (or none). The score is a guide and can be adjusted.</td></tr>
                            <tr><td>Grade form blank after resubmission</td><td>A new report version opens a fresh grading form; the prior grading is retained read-only.</td></tr>
                            <tr><td>Ethical/Other rejection cannot resubmit</td><td>Those types do not request a new report — use <strong>Edit Grading</strong> once the LPI has acted.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="glossary" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-book"></i> 17. Glossary</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Term</th><th>Definition</th></tr></thead>
                        <tbody>
                            <tr><td>Commitment</td><td>An expected output declared by the LPI at registration; the grading baseline.</td></tr>
                            <tr><td>Outcome</td><td>A concrete result recorded against a commitment.</td></tr>
                            <tr><td>Auto-score</td><td>A suggested section score computed from outcomes vs commitments (capped at 5).</td></tr>
                            <tr><td>Version (vN)</td><td>A specific submission of a report; resubmissions increment the version.</td></tr>
                            <tr><td>Claimed</td><td>State after a reviewer accepts a proposal — the reviewer owns the grading.</td></tr>
                            <tr><td>Report Card</td><td>The published grading summary for a project.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="manual-callout"><i class="fas fa-circle-question"></i> For anything not covered here, contact the research office.</div>
            </div>
        </section>

    </div>
</div>
