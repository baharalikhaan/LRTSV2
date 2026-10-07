<div class="manual-layout">
    {{-- Table of contents --}}
    <aside class="manual-toc">
        <div class="panel">
            <div class="panel-head"><h2 style="font-size:13px;"><i class="fas fa-list-ul"></i> Contents</h2></div>
            <div class="panel-body" style="padding:8px;">
                <a href="#intro">1. Introduction &amp; Scope</a>
                <a href="#signin">2. Authentication</a>
                <a href="#dashboard">3. Dashboard</a>
                <a href="#projects">4. Project Portfolio</a>
                <a href="#statuses">5. Status Reference</a>
                <a href="#register">6. Project Registration</a>
                <a href="#proposal">7. Proposal Document</a>
                <a href="#progress">8. Progress Reporting</a>
                <a href="#final">9. Final &amp; Readiness Reports</a>
                <a href="#ethical">10. Ethical Approvals</a>
                <a href="#outcomes">11. Outcomes &amp; Verification</a>
                <a href="#students">12. Students (SIS Lookup)</a>
                <a href="#resubmit">13. Rejection &amp; Resubmission</a>
                <a href="#grades">14. Grading &amp; Report Card</a>
                <a href="#studentgrant">15. Student-Grant Projects</a>
                <a href="#budget">16. Budget Utilization</a>
                <a href="#notifications">17. Notifications</a>
                <a href="#profile">18. Profile &amp; Roles</a>
                <a href="#troubleshooting">19. Troubleshooting</a>
                <a href="#glossary">20. Glossary</a>
            </div>
        </div>
    </aside>

    {{-- Manual body --}}
    <div class="manual-body">

        <section id="intro" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-circle-info"></i> 1. Introduction &amp; Scope</h2></div>
            <div class="panel-body">
                <p class="manual-lead">The Research Tracking System (RTS) is Qatar University's platform for the full lifecycle of internal research grants — from research-call setup and project registration, through progress and final reporting, to reviewer grading and outcome tracking.</p>
                <p>This manual is the operational reference for a <strong>Lead Principal Investigator (LPI)</strong>. It documents every action available to you, the underlying rules the system enforces, and the reference data (statuses, categories and validation) you will encounter. It is written for both first-time and experienced users.</p>
                <div class="manual-callout"><i class="fas fa-lightbulb"></i> Most LPI actions are performed from a project's <strong>detail page</strong>. Use the <strong>Contents</strong> list to jump to a topic.</div>
            </div>
        </section>

        <section id="signin" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-right-to-bracket"></i> 2. Authentication</h2></div>
            <div class="panel-body">
                <p>RTS authenticates against your Qatar University identity. The active method is controlled by the deployment's login mode:</p>
                <ul class="manual-steps">
                    <li><strong>QU Single Sign-On (SSO / ADFS)</strong> — click <em>Sign in with QU</em>. RTS consumes the SAML assertion returned by the university IdP; no local password is used.</li>
                    <li><strong>Email &amp; password</strong> — used for local accounts and administration.</li>
                </ul>
                <p><strong>Account matching.</strong> On SSO sign-in, RTS matches the assertion against your <strong>QU ID</strong> (the ID-based university email held on your account). Only accounts flagged <em>active</em> are permitted to sign in; deactivated accounts are rejected.</p>
                <p><strong>First sign-in.</strong> Accounts without a saved nationality are presented with a non-dismissable nationality prompt immediately after login, which must be completed before the application can be used.</p>
                <div class="manual-callout"><i class="fas fa-shield-halved"></i> If SSO completes at the IdP but you are returned to the login page, your account may be inactive or your QU ID may not match. Contact the research office to reconcile it.</div>
            </div>
        </section>

        <section id="dashboard" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-table-cells-large"></i> 3. Dashboard</h2></div>
            <div class="panel-body">
                <p>The dashboard is your portfolio overview. It comprises:</p>
                <ul class="manual-steps">
                    <li><strong>KPI stat cards</strong> — <em>All Projects</em>, <em>Unregistered</em>, <em>Report Upload Pending</em>, <em>Progress Report Done</em> and <em>Graded</em>. Each card is clickable and drills into the matching project set.</li>
                    <li><strong>Distribution breakdowns</strong> — <em>By Research Call</em> and <em>By Pillar</em>, so you can gauge where your portfolio is concentrated.</li>
                    <li><strong>LPI Contribution Summary</strong> — <em>Grants Availed</em> (distinct grants across your projects), <em>Cycles Worked</em>, <em>Research Calls Worked</em>, <em>Publications</em> (total) and <em>Students Attached</em>.</li>
                    <li><strong>Announcements</strong> — LPI-targeted notices published by the research office.</li>
                </ul>
                <p>The counts reflect your projects' latest workflow status, so a project appears in exactly one stage bucket at a time.</p>
            </div>
        </section>

        <section id="projects" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-diagram-project"></i> 4. Project Portfolio</h2></div>
            <div class="panel-body">
                <p>Open <strong>All Projects</strong> to see every project assigned to you. From each row you can <strong>Register</strong> (start the wizard) or <strong>View</strong> (open the project detail page).</p>
                <p>The project detail page is the operational hub. Depending on the project's state it exposes the relevant actions — <em>Register</em>, <em>Upload Proposal</em>, <em>Update Progress</em>, <em>Submit Final Report</em>, <em>View Report Card</em> — together with the proposal, reports, outcomes, ethical approvals and the current status.</p>
                <p><strong>Ownership &amp; access.</strong> You can act on a project that is unclaimed or claimed by you. Once a project has been registered and bound to an LPI, the registration form is locked to that owner; administrative overrides are handled by the research office.</p>
            </div>
        </section>

        <section id="statuses" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-list-check"></i> 5. Status Reference</h2></div>
            <div class="panel-body">
                <p>Every project carries a workflow status that advances as you and your reviewer act. Understanding these is essential to interpreting the dashboard and the available actions.</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Status</th><th>Meaning</th><th>Next actor</th></tr></thead>
                        <tbody>
                            <tr><td><span class="pill ink">Unregistered</span></td><td>Imported into a research call; awaiting your registration.</td><td>LPI</td></tr>
                            <tr><td><span class="pill info">Registered</span></td><td>You completed the registration wizard.</td><td>Research office / Reviewer</td></tr>
                            <tr><td><span class="pill primary">Assigned</span></td><td>A reviewer has been assigned and must accept.</td><td>Reviewer</td></tr>
                            <tr><td><span class="pill success">Claimed</span></td><td>The reviewer accepted the proposal and owns its grading.</td><td>LPI</td></tr>
                            <tr><td><span class="pill warning">Progress Added</span></td><td>You submitted Progress Report 1.</td><td>Reviewer</td></tr>
                            <tr><td><span class="pill success">Progress Reviewed</span></td><td>Progress Report 1 was graded/accepted.</td><td>LPI</td></tr>
                            <tr><td><span class="pill danger">Progress Rejected</span></td><td>Progress Report 1 was rejected — action required.</td><td>LPI</td></tr>
                            <tr><td><span class="pill warning">Progress 2 Added</span></td><td>You submitted Progress Report 2 (extended projects).</td><td>Reviewer</td></tr>
                            <tr><td><span class="pill warning">Final Added</span></td><td>You submitted the final report.</td><td>Reviewer</td></tr>
                            <tr><td><span class="pill success">Graded</span></td><td>Final grading accepted — the project is complete.</td><td>—</td></tr>
                            <tr><td><span class="pill danger">Proposal Rejected</span></td><td>The reviewer declined the assignment; returned to the office.</td><td>Research office</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="manual-callout"><i class="fas fa-circle-info"></i> "…Rejection Reviewed" states indicate the office has reviewed a rejection and either requested a resubmission or re-opened grading for editing.</div>
            </div>
        </section>

        <section id="register" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-file-circle-plus"></i> 6. Project Registration</h2></div>
            <div class="panel-body">
                <p>Registration must be completed before reporting begins. Open an available project and click <strong>Register</strong>. The wizard is a single form split across four steps; your proposal PDF is displayed alongside for reference.</p>
                <div class="mini-flow">
                    <div class="mini-step"><span class="ms-num">1</span><span class="ms-text"><span class="ms-label">Basic Info</span><span class="ms-sub">Title &amp; PI details</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">2</span><span class="ms-text"><span class="ms-label">Pillar &amp; College</span><span class="ms-sub">Select pillars + college</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">3</span><span class="ms-text"><span class="ms-label">Commitments</span><span class="ms-sub">Publications, IP, students</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">4</span><span class="ms-text"><span class="ms-label">Review &amp; Submit</span><span class="ms-sub">Confirm &amp; submit</span></span></div>
                </div>
                <p><strong>Field reference.</strong></p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Step</th><th>Fields</th><th>Notes</th></tr></thead>
                        <tbody>
                            <tr><td>Basic Info</td><td>Project title, PI name, PI email</td><td>Title and PI identity are required; pre-filled from the research call where possible.</td></tr>
                            <tr><td>Pillar &amp; College</td><td>Research pillars (multi-select), College/Institute (single)</td><td>Select every pillar the project spans; choose the administering college.</td></tr>
                            <tr><td>Commitments</td><td>Publications: Q1–Q4 articles, conference articles, books, edited books, chapters. IP: disclosures, filed patents, open-source SW, start-up. People: Masters, Undergraduate, PhD, cross-college. Ethical requirement flag.</td><td>Whole numbers ≥ 0. Commitments become the yardstick the reviewer uses to auto-score your outputs.</td></tr>
                            <tr><td>Review &amp; Submit</td><td>Read-only summary + confirmation checkbox</td><td>Submission records <em>Registered</em> and locks the form.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="manual-callout"><i class="fas fa-floppy-disk"></i> Use <strong>Save as Draft</strong> to persist partial answers without submitting. <strong>Restore</strong> reloads your last saved draft. Submitting permanently locks the form.</div>
            </div>
        </section>

        <section id="proposal" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-file-pdf"></i> 7. Proposal Document</h2></div>
            <div class="panel-body">
                <p>The proposal PDF is normally imported by the research office when the research call is created and matched to your project ID. You can view it inline during registration and from the project detail page.</p>
                <p>If no proposal is attached, upload one from the project page (or during registration). Constraints:</p>
                <ul class="manual-steps">
                    <li>Format: <strong>PDF</strong> only; maximum size <strong>20 MB</strong>.</li>
                    <li>Uploading replaces any existing proposal document for the project.</li>
                </ul>
                <p>Stored proposals are addressed by the project's file-safe ID, so document lookups remain consistent even when the project ID contains characters such as "/".</p>
            </div>
        </section>

        <section id="progress" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-clock-rotate-left"></i> 8. Progress Reporting</h2></div>
            <div class="panel-body">
                <p>From the project detail page, click <strong>Update Progress</strong> to open the progress workspace. A progress submission comprises three parts: recorded <em>outcomes</em>, the <em>progress report PDF</em>, and (optionally) supporting files.</p>
                <p><strong>Progress Report 1 and Progress Report 2.</strong> Extended projects require a second report. Report 2 becomes available per the research call's configuration and follows the same process.</p>
                <p><strong>Deadlines and locking.</strong> Each research call defines a progress and a final reporting deadline. Uploads are only accepted while the corresponding window is open; once a deadline passes the upload control is locked. Reviewers can grade a report only after its deadline has passed.</p>
                <p><strong>Versioning.</strong> Each submission is versioned. Your initial report is <em>v1</em>; if the reviewer rejects it and you resubmit, the new file is stored as <em>v2</em> and the reviewer is re-opened onto the new version while the previous version is retained read-only.</p>
                <div class="manual-callout"><i class="fas fa-lightbulb"></i> Record outcomes <em>before</em> submitting the report — the reviewer compares them against your commitments, and this drives the auto-score.</div>
            </div>
        </section>

        <section id="final" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-file-signature"></i> 9. Final &amp; Readiness Reports</h2></div>
            <div class="panel-body">
                <p>At project completion submit the <strong>Final Report</strong>, and where the grant requires it a <strong>Readiness Report</strong>. Both are uploaded as PDFs from the final reporting page together with any outstanding outcomes.</p>
                <p>After the final report is submitted it enters final grading. When the reviewer accepts the final grade, the project is marked <em>Graded</em>, the report card is published to you, and (if notifications are enabled) you receive a confirmation email.</p>
            </div>
        </section>

        <section id="ethical" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-shield-halved"></i> 10. Ethical Approvals</h2></div>
            <div class="panel-body">
                <p>Where your project requires ethical approval, upload the approval document(s) from the project page. RTS supports <strong>multiple</strong> ethical documents per project; each is listed with its filename and upload date and can be viewed or removed individually.</p>
                <p>During grading the reviewer verifies the ethical approval. If it is missing or invalid, the reviewer may reject the report with the type <em>Missing/Invalid Ethical Approval</em>. In that case you upload a corrected document and the reviewer re-opens and edits the existing grading — no new report version is requested.</p>
            </div>
        </section>

        <section id="outcomes" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-trophy"></i> 11. Outcomes &amp; Verification</h2></div>
            <div class="panel-body">
                <p>Outcomes are the measurable results of your project. They are recorded on the progress workspace and are grouped into three families used by the grading engine:</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Family</th><th>Types</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Achievements</strong></td><td>IP disclosure, provisional patent, granted patent, open-source software, start-up, prototype, cross-college activity</td></tr>
                            <tr><td><strong>Publications</strong></td><td>Q1/Q2/Q3/Q4 journal articles, conference articles, books, edited books, book chapters</td></tr>
                            <tr><td><strong>People</strong></td><td>Masters, Undergraduate and PhD students; researchers</td></tr>
                        </tbody>
                    </table>
                </div>
                <p><strong>Identifiers &amp; validation.</strong> Journal and conference outputs accept a <strong>DOI / identifier</strong>. RTS validates the identifier against the Crossref service and, where resolved, displays the publisher badge. Unresolvable identifiers are flagged.</p>
                <p><strong>Verification.</strong> Each outcome carries a verification state (<em>Verified</em> / <em>Pending</em>) that the reviewer toggles while grading. This state feeds the reviewer's audit view of your project.</p>
            </div>
        </section>

        <section id="students" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-user-graduate"></i> 12. Students (SIS Lookup)</h2></div>
            <div class="panel-body">
                <p>When you add a student you provide the <strong>student ID (QU ID)</strong>, the level and the number of days. RTS then queries the QU Student Information System (SIS) and auto-fills the student's name, programme, college and status.</p>
                <ul class="manual-steps">
                    <li>On a successful lookup the student's details are stored and displayed on the project.</li>
                    <li>If the lookup fails (for example the SIS is unreachable), the student row is still created but its details remain blank. Use <strong>Verify</strong> on the student later to retry the lookup.</li>
                </ul>
                <div class="manual-callout"><i class="fas fa-circle-info"></i> A failed SIS lookup never blocks your report — it simply leaves the detail fields empty until a successful verification.</div>
            </div>
        </section>

        <section id="resubmit" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-rotate"></i> 13. Rejection &amp; Resubmission</h2></div>
            <div class="panel-body">
                <p>When a reviewer rejects a report, the project displays the rejection with the reviewer's reason and the rejection type. Your next action depends on that type:</p>
                <div class="mini-flow">
                    <div class="mini-step"><span class="ms-num">1</span><span class="ms-text"><span class="ms-label">Report Rejected</span><span class="ms-sub">Reason + type shown</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">2</span><span class="ms-text"><span class="ms-label">Correct &amp; Resubmit</span><span class="ms-sub">New version or ethical doc</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">3</span><span class="ms-text"><span class="ms-label">Re-graded</span><span class="ms-sub">Reviewer scores again</span></span></div>
                </div>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Rejection type</th><th>Your action</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Report</strong></td><td>Upload a corrected report (stored as a new version, e.g. <em>v2</em>) and submit it. The reviewer re-grades the new version.</td></tr>
                            <tr><td><strong>Missing / Invalid Ethical Approval</strong></td><td>Upload the correct ethical document. The reviewer re-opens and edits the existing grading — no new report version.</td></tr>
                            <tr><td><strong>Other</strong></td><td>Address the reviewer's note; the reviewer re-opens and edits the existing grading — no resubmission of the report itself.</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>While a rejection is unresolved the report upload stays locked, so completing the requested action is what moves the project forward.</p>
            </div>
        </section>

        <section id="grades" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-star"></i> 14. Grading &amp; Report Card</h2></div>
            <div class="panel-body">
                <p>Once a report has been graded, a <strong>View Report Card</strong> action appears on the project. The report card presents the reviewer's section scores, comments and the overall result, and is print-optimised (A4).</p>
                <p>Grading is performed by your assigned reviewer in scored sections; for progress reports these are <em>Achievements against objectives</em>, <em>Publications &amp; IP</em> and <em>Student &amp; Researcher Involvement</em>; the final report adds further sections. Scores are rated on a 1–5 scale.</p>
            </div>
        </section>

        <section id="studentgrant" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-user-graduate"></i> 15. Student-Grant Projects</h2></div>
            <div class="panel-body">
                <p>Projects under a <strong>student grant</strong> use a dedicated one-page progress form in place of the regular report tabs. It captures per-student Qatari/Non-Qatari designation, a publications list, a spending-versus-allocated-budget meter, a student-engagement narrative and multiple ethical-approval PDFs.</p>
                <p>The form supports <strong>Save as Draft</strong>, <strong>Restore</strong> to the last saved point, and a permanent lock on submission. Student-grant projects are auto-registered at import, so they typically skip the registration wizard.</p>
            </div>
        </section>

        <section id="budget" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-coins"></i> 16. Budget Utilization</h2></div>
            <div class="panel-body">
                <p>For projects with a budget, the research office tracks utilisation. You may receive a <strong>budget utilisation reminder</strong> email containing your total, actual spend, available balance and utilisation percentage.</p>
            </div>
        </section>

        <section id="notifications" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-bullhorn"></i> 17. Notifications</h2></div>
            <div class="panel-body">
                <p>RTS keeps you informed through three channels:</p>
                <ul class="manual-steps">
                    <li><strong>Announcements</strong> — targeted messages shown on your dashboard and the Announcements page.</li>
                    <li><strong>The notification bell</strong> — pending tasks and reminders relevant to your role.</li>
                    <li><strong>Email notifications</strong> — event-based messages sent when the deployment's email switch is enabled.</li>
                </ul>
                <p><strong>Events that trigger an email to you</strong> (subject to the system email switch): a project is added to a research call, your registration is confirmed, a proposal decision is recorded, you submit a progress report, and a report is graded. Each is driven by an editable template maintained by the research office.</p>
            </div>
        </section>

        <section id="profile" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-id-card"></i> 18. Profile &amp; Roles</h2></div>
            <div class="panel-body">
                <p>Open your profile from the top-right user menu. Your <strong>email</strong> and <strong>QU ID</strong> are provisioned by the university and shown read-only; keep your department and college current so reports and notices reach you correctly.</p>
                <p><strong>Composite roles.</strong> If your account holds more than one role (for example LPI and Reviewer), a <strong>role switcher</strong> appears in the top bar. Switch to the appropriate role to see that role's menus and actions.</p>
            </div>
        </section>

        <section id="troubleshooting" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-screwdriver-wrench"></i> 19. Troubleshooting</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Symptom</th><th>Likely cause / resolution</th></tr></thead>
                        <tbody>
                            <tr><td>Returned to the login page after SSO</td><td>Account inactive or QU ID not matched. Contact the research office.</td></tr>
                            <tr><td>Cannot upload a report</td><td>The reporting window is closed (deadline passed) or an unresolved rejection is locking the upload.</td></tr>
                            <tr><td>Proposal not shown</td><td>No proposal was imported; upload one from the project page (PDF, ≤ 20 MB).</td></tr>
                            <tr><td>Student details blank after adding</td><td>The SIS lookup failed. Use <strong>Verify</strong> on the student to retry.</td></tr>
                            <tr><td>DOI shows no publisher badge</td><td>The identifier could not be resolved by Crossref; verify the DOI format.</td></tr>
                            <tr><td>Report rejected as "ethical"</td><td>Upload the correct ethical approval; no new report version is needed.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="glossary" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-book"></i> 20. Glossary</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Term</th><th>Definition</th></tr></thead>
                        <tbody>
                            <tr><td>LPI</td><td>Lead Principal Investigator — owner of the project and its reporting.</td></tr>
                            <tr><td>Research Call</td><td>A grant cycle instance (grant × cycle) under which projects are grouped.</td></tr>
                            <tr><td>Commitments</td><td>The expected outputs you declare at registration; used as the grading baseline.</td></tr>
                            <tr><td>Outcome</td><td>A concrete result of the project (publication, IP, person) recorded against commitments.</td></tr>
                            <tr><td>Version (vN)</td><td>A specific submission of a report; resubmissions increment the version.</td></tr>
                            <tr><td>SIS</td><td>QU Student Information System — source of student records.</td></tr>
                            <tr><td>Report Card</td><td>The published grading summary for a project.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="manual-callout"><i class="fas fa-circle-question"></i> For anything not covered here, contact the research office.</div>
            </div>
        </section>

    </div>
</div>
