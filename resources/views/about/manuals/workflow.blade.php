<div class="manual-layout">
    {{-- Table of contents --}}
    <aside class="manual-toc">
        <div class="panel">
            <div class="panel-head"><h2 style="font-size:13px;"><i class="fas fa-list-ul"></i> Contents</h2></div>
            <div class="panel-body" style="padding:8px;">
                <a href="#overview">1. End-to-End Flow</a>
                <a href="#phases">2. Phases, Actors &amp; Statuses</a>
                <a href="#assignment">3. Assignment &amp; Claiming</a>
                <a href="#grading">4. Grading, Drafts &amp; Consensus</a>
                <a href="#rejections">5. Rejection Scenarios</a>
                <a href="#extended">6. Extended Progress (PR2)</a>
                <a href="#statuses">7. Status Reference</a>
                <a href="#notifications">8. Notifications &amp; Emails</a>
                <a href="#deadlines">9. Deadlines &amp; Locking</a>
            </div>
        </div>
    </aside>

    {{-- Manual body --}}
    <div class="manual-body">

        {{-- 1. End-to-end flow --}}
        <section id="overview" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-diagram-project"></i> 1. End-to-End Flow</h2></div>
            <div class="panel-body">
                <p class="manual-lead">The complete RTS lifecycle — from the research call import to the published report card — with the actor responsible for each step. Every box shows <strong>what happens</strong> · <strong>who acts</strong> · <em>resulting status</em>.</p>

                <p><strong>Happy path</strong> (everything accepted):</p>
                <div class="mini-flow">
                    <div class="mini-step"><span class="ms-num">1</span><span class="ms-text"><span class="ms-label">Import</span><span class="ms-sub">Admin · → Imported</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">2</span><span class="ms-text"><span class="ms-label">Register + Commitments</span><span class="ms-sub">LPI · → Registered</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">3</span><span class="ms-text"><span class="ms-label">Assign Reviewers</span><span class="ms-sub">Admin · → Assigned</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">4</span><span class="ms-text"><span class="ms-label">Accept Proposal</span><span class="ms-sub">Each reviewer · → Claimed</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">5</span><span class="ms-text"><span class="ms-label">Progress Report</span><span class="ms-sub">LPI · → Progress Added</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">6</span><span class="ms-text"><span class="ms-label">Progress Grading</span><span class="ms-sub">Reviewers (consensus) · → Progress Reviewed</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">7</span><span class="ms-text"><span class="ms-label">Final Report</span><span class="ms-sub">LPI · → Final Added</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">8</span><span class="ms-text"><span class="ms-label">Final Grading</span><span class="ms-sub">Reviewers (consensus) · → Graded</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">9</span><span class="ms-text"><span class="ms-label">Report Card</span><span class="ms-sub">LPI / Admin · published</span></span></div>
                </div>

                <p><strong>Rejection loop</strong> (a reviewer rejects at any grading step):</p>
                <div class="mini-flow">
                    <div class="mini-step"><span class="ms-num">A</span><span class="ms-text"><span class="ms-label">Reject + reason</span><span class="ms-sub">Reviewer · his set is stored</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">B</span><span class="ms-text"><span class="ms-label">Consensus completes</span><span class="ms-sub">all assigned reviewers submitted</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">C</span><span class="ms-text"><span class="ms-label">Admin reviews rejection</span><span class="ms-sub">adds own comment → Send to LPI</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">D</span><span class="ms-text"><span class="ms-label">Correct &amp; Resubmit (v2)</span><span class="ms-sub">LPI · all grade sets re-open</span></span></div>
                    <span class="mini-arrow"><i class="fas fa-arrow-right"></i></span>
                    <div class="mini-step"><span class="ms-num">E</span><span class="ms-text"><span class="ms-label">Re-grade</span><span class="ms-sub">Reviewers · fresh forms</span></span></div>
                </div>
                <div class="manual-callout"><i class="fas fa-circle-info"></i> <strong>Ethical / Other rejections</strong> also go through the admin (boxes B–D), but the LPI re-sends the <em>ethical approval document</em> — never the report itself, whose upload stays locked (see section 5).</div>
            </div>
        </section>

        {{-- 2. Phases, actors & statuses --}}
        <section id="phases" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-users"></i> 2. Phases, Actors &amp; Statuses</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Phase</th><th>Actor</th><th>Key actions</th><th>Status set</th></tr></thead>
                        <tbody>
                            <tr><td><strong>1. Import</strong></td><td>Admin</td><td>Import the research call from Excel; proposal PDFs are matched by project ID (file-safe name).</td><td><span class="pill warning">Imported</span></td></tr>
                            <tr><td><strong>2. Registration</strong></td><td>LPI</td><td>4-step wizard: Basic info → Pillars &amp; College → <strong>Commitments</strong> → Review &amp; Submit. Commitments lock on submit; afterwards only an admin can change them (grading page → Commitments tab).</td><td><span class="pill accepted">Registered</span></td></tr>
                            <tr><td><strong>3. Proposal</strong></td><td>Research office (admin)</td><td>The proposal PDF is uploaded by the office (Research Calls → Upload Proposals, or Upload Reports). LPIs can only view and download it.</td><td>—</td></tr>
                            <tr><td><strong>4. Assignment</strong></td><td>Admin</td><td>Assign one or <strong>several reviewers</strong> (added alongside each other); per-reviewer Un-assign with reason.</td><td><span class="pill review">Assigned</span></td></tr>
                            <tr><td><strong>5. Claim</strong></td><td>Each reviewer</td><td>Accept/Reject Proposal — a reviewer must claim <strong>for himself</strong> before his grading form opens.</td><td><span class="pill accepted">Claimed</span> · <span class="pill danger">Proposal Rejected</span></td></tr>
                            <tr><td><strong>6. Progress reporting</strong></td><td>LPI</td><td>Record outcomes/students, upload Progress Report 1 (and PR2 when extended), readiness and ethical approval files.</td><td><span class="pill warning">Progress Added</span> · <span class="pill warning">Progress 2 Added</span></td></tr>
                            <tr><td><strong>7. Progress grading</strong></td><td>Reviewers + Admin</td><td>Score sections A–C (auto-scored), accept or reject with a type. Runs on <strong>consensus</strong> (section 4).</td><td><span class="pill info">Progress Reviewed</span> · <span class="pill danger">Progress Rejected</span></td></tr>
                            <tr><td><strong>8. Final reporting</strong></td><td>LPI</td><td>Upload the final report; project data freezes at the final deadline.</td><td><span class="pill info">Final Added</span></td></tr>
                            <tr><td><strong>9. Final grading</strong></td><td>Reviewers + Admin</td><td>Sections A–C auto-scored, D manual; same consensus rules.</td><td><span class="pill accepted">Graded</span> · <span class="pill danger">Final Rejected</span></td></tr>
                            <tr><td><strong>10. Report card</strong></td><td>LPI / Admin</td><td>Published evaluation: every reviewer's grades, outcomes, students; printable A4.</td><td>—</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- 3. Assignment & claiming --}}
        <section id="assignment" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-user-check"></i> 3. Assignment &amp; Claiming</h2></div>
            <div class="panel-body">
                <ul class="manual-steps">
                    <li><strong>Multiple reviewers.</strong> A project may hold several reviewers at the same time. The Assign modal lists who is already assigned; a new reviewer is <em>added alongside</em> them — nobody is replaced, and each reviewer keeps his own grading set.</li>
                    <li><strong>Claiming is per reviewer.</strong> Every reviewer must run Accept/Reject Proposal for <em>himself</em> (tracked on his own assignment). His claim never unlocks another reviewer's grading form; the server enforces this (grading returns 403 before your own claim exists).</li>
                    <li><strong>Reject.</strong> Supply a reason: only <em>your</em> assignment is removed, you are recorded in the rejection log and can never be re-assigned to that project, and the project shows <span class="pill danger">Proposal Rejected</span> until the admin assigns someone else.</li>
                    <li><strong>Un-assign (admin).</strong> Per reviewer, with an optional reason; the reviewer is notified. An admin may also pull a reviewer who already claimed (otherwise a silent reviewer would block the stage forever); <span class="pill">Reviewer Unassigned</span> is only recorded when the <em>last</em> reviewer is removed.</li>
                </ul>
                <div class="mini-decision">
                    <div class="md-row"><span class="md-tag accept"><i class="fas fa-check"></i> Accept</span><span class="md-text">Your claim is stamped → project shows <em>Claimed</em> → your grading form unlocks.</span></div>
                    <div class="md-row"><span class="md-tag reject"><i class="fas fa-xmark"></i> Reject</span><span class="md-text">You are removed and blacklisted for that project; other assigned reviewers are untouched.</span></div>
                </div>
            </div>
        </section>

        {{-- 4. Grading, drafts & consensus --}}
        <section id="grading" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-star"></i> 4. Grading, Drafts &amp; Consensus</h2></div>
            <div class="panel-body">
                <p>Grading happens on the project's grading page (PDF left, form right) and is strictly <strong>per user</strong>:</p>
                <ul class="manual-steps">
                    <li><strong>Draft → Submit → Read-only.</strong> Save as Draft keeps your form editable on every visit; once you Submit, your set becomes read-only for you. A rejection re-opens it via the rejection flow (section 5).</li>
                    <li><strong>Isolation.</strong> On the grading page you only ever see <em>your own</em> draft or submitted set — other reviewers' and the admin's scores are invisible to you. All sets appear together only on the project page and report card.</li>
                    <li><strong>Admin can grade too.</strong> An admin grades on behalf from <em>Evaluate Projects</em> or the grading page; his set is stored under his own account and never overwrites a reviewer's rows.</li>
                    <li><strong>Evaluate Projects = your personal queue.</strong> A row appears when a stage is ready and <em>you</em> have not submitted; it disappears only for you once you submit — never because someone else did.</li>
                </ul>

                <p><strong>Consensus rules</strong> — the project-level status advances only when the whole team is done:</p>
                <div class="mini-decision">
                    <div class="md-row"><span class="md-tag loop"><i class="fas fa-rotate"></i> Wait</span><span class="md-text">The first (or any early) submission keeps the stage open — it does <strong>not</strong> close it for the other reviewers.</span></div>
                    <div class="md-row"><span class="md-tag accept"><i class="fas fa-check"></i> Complete</span><span class="md-text">When <strong>every assigned reviewer</strong> has submitted, the stage flips <strong>once</strong> (<em>Progress Reviewed</em> / <em>Graded</em>). A standalone admin grade (no reviewers assigned) completes immediately.</span></div>
                    <div class="md-row"><span class="md-tag reject"><i class="fas fa-xmark"></i> Rejection wins</span><span class="md-text">If <strong>any</strong> submitted set is rejected, the completed status is the rejected one — the rejection flow (section 5) starts.</span></div>
                    <div class="md-row"><span class="md-tag loop"><i class="fas fa-lock"></i> Frozen</span><span class="md-text">After a stage completes it is frozen: a reviewer added later can still store his own set, but it can never re-open or flip the completed status. Removing a reviewer who never submitted re-runs the consensus, so the stage completes for the rest.</span></div>
                </div>
                <div class="manual-callout"><i class="fas fa-envelope"></i> The LPI's "report graded" e-mail fires <strong>once at completion</strong> — not once per reviewer.</div>
            </div>
        </section>

        {{-- 5. Rejection scenarios --}}
        <section id="rejections" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-balance-scale"></i> 5. Rejection Scenarios</h2></div>
            <div class="panel-body">
                <p>A rejection always stores the reason, type and rejecting reviewer on the rejecting reviewer's own set. What happens next depends on the type chosen in the grading form:</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Type</th><th>Admin step</th><th>New version from LPI</th><th>Grading after</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><strong>Report</strong><br><span style="font-size:11px;color:var(--ink-400);">default</span></td>
                                <td>Yes — <em>Review Progress/Final Rejection</em>: the admin reads the reviewer's reason, adds his own message and clicks <strong>Send to LPI</strong> → status <em>*_rejection_reviewed</em>, LPI e-mailed, red banner on the LPI's project page.</td>
                                <td>Yes — v2 upload. <strong>Every reviewer's set is archived and re-opened blank</strong> so the whole team re-grades the new version.</td>
                                <td>Fresh forms for all assigned reviewers (consensus restarts).</td>
                            </tr>
                            <tr>
                                <td><strong>Missing / Invalid Ethical Approval</strong></td>
                                <td>Yes — admin reviews and forwards it.</td>
                                <td>No — upload the corrected ethical document only (report upload stays locked).</td>
                                <td>Reviewer re-opens with <em>Edit Grading</em> (<code>?edit_grading=1</code>) and re-submits once the LPI has re-sent the approval.</td>
                            </tr>
                            <tr>
                                <td><strong>Other</strong></td>
                                <td>Yes.</td>
                                <td>No — typically the supporting document is re-sent.</td>
                                <td>Same as ethical — reviewer re-opens with <em>Edit Grading</em>.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="manual-callout"><i class="fas fa-circle-info"></i> While the admin review of a <em>Report</em> rejection is still open, the admin's bell shows <strong>"N rejected report(s) awaiting your review"</strong>; the LPI's bell shows <strong>"N rejected report(s) needing resubmission"</strong> until he resubmits or the project is graded.</div>
            </div>
        </section>

        {{-- 6. Extended progress --}}
        <section id="extended" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-clock"></i> 6. Extended Progress (PR2)</h2></div>
            <div class="panel-body">
                <ul class="manual-steps">
                    <li>Admin toggles the extension per project (<em>Extend Progress Report</em> page) — this opens the <strong>Progress Report 2</strong> window and its deadline.</li>
                    <li>LPI submits PR2 → <span class="pill warning">Progress 2 Added</span>; reviewers grade it exactly like PR1 (own set, draft/submit, consensus) → <span class="pill info">Progress 2 Reviewed</span> or <span class="pill danger">Progress 2 Rejected</span>.</li>
                    <li>PR2 has its own rejection cycle (admin review → v2 of PR2 → re-grade), independent of PR1's.</li>
                </ul>
            </div>
        </section>

        {{-- 7. Status reference --}}
        <section id="statuses" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-table-cells-large"></i> 7. Status Reference</h2></div>
            <div class="panel-body">
                <p>The status pill on any list shows the project's <strong>latest</strong> status (and the status filter matches exactly these values):</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Status</th><th>Meaning</th><th>Set by</th></tr></thead>
                        <tbody>
                            <tr><td><em>Unregistered</em></td><td>Imported but not yet registered (computed, not a stored status).</td><td>—</td></tr>
                            <tr><td><span class="pill warning">Imported</span></td><td>Row created from the research call workbook.</td><td>Admin (import)</td></tr>
                            <tr><td><span class="pill accepted">Registered</span></td><td>LPI completed the registration wizard.</td><td>LPI</td></tr>
                            <tr><td><span class="pill review">Assigned</span></td><td>Reviewer(s) added to the project.</td><td>Admin</td></tr>
                            <tr><td><span class="pill accepted">Claimed</span></td><td>The first reviewer accepted the proposal (per-reviewer claims continue alongside).</td><td>Reviewer</td></tr>
                            <tr><td><span class="pill danger">Proposal Rejected</span></td><td>A reviewer declined; only his assignment was removed; back in the admin queue.</td><td>Reviewer</td></tr>
                            <tr><td><span class="pill">Reviewer Unassigned</span></td><td>The last assigned reviewer was removed.</td><td>Admin</td></tr>
                            <tr><td><span class="pill warning">Progress Added</span></td><td>LPI submitted the progress report.</td><td>LPI</td></tr>
                            <tr><td><span class="pill info">Progress Reviewed</span></td><td>Consensus complete — every assigned reviewer submitted (all accepted).</td><td>Reviewers / Admin</td></tr>
                            <tr><td><span class="pill danger">Progress Rejected</span></td><td>Consensus complete with at least one rejected set.</td><td>Reviewers / Admin</td></tr>
                            <tr><td><span class="pill info">Progress Rejection Reviewed</span></td><td>Admin sent the rejection back to the LPI (report type only).</td><td>Admin</td></tr>
                            <tr><td><span class="pill warning">Progress 2 Added</span></td><td>LPI submitted the extended progress report.</td><td>LPI</td></tr>
                            <tr><td><span class="pill info">Progress 2 Reviewed</span></td><td>PR2 consensus complete (accepted).</td><td>Reviewers / Admin</td></tr>
                            <tr><td><span class="pill danger">Progress 2 Rejected</span></td><td>PR2 consensus complete (rejected).</td><td>Reviewers / Admin</td></tr>
                            <tr><td><span class="pill info">Progress 2 Rejection Reviewed</span></td><td>Admin sent the PR2 rejection back to the LPI.</td><td>Admin</td></tr>
                            <tr><td><span class="pill info">Final Added</span></td><td>LPI submitted the final report.</td><td>LPI</td></tr>
                            <tr><td><span class="pill accepted">Graded</span></td><td>Final consensus complete (accepted) → report card published.</td><td>Reviewers / Admin</td></tr>
                            <tr><td><span class="pill danger">Final Rejected</span></td><td>Final consensus complete (rejected).</td><td>Reviewers / Admin</td></tr>
                            <tr><td><span class="pill info">Final Rejection Reviewed</span></td><td>Admin sent the final rejection back to the LPI.</td><td>Admin</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- 8. Notifications & emails --}}
        <section id="notifications" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-bell"></i> 8. Notifications &amp; Emails</h2></div>
            <div class="panel-body">
                <p>The <strong>bell icon</strong> in the top bar lists role-aware <em>Pending Tasks</em> (static, server-rendered) plus announcements loaded on click:</p>
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Role</th><th>Pending tasks shown</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Admin</strong></td><td>Projects pending reviewer assignment · projects pending grading · <strong>rejected reports awaiting your review</strong> (rejections not yet reviewed — including ethical/other, which you forward so the LPI re-sends the approval; graded projects never count).</td></tr>
                            <tr><td><strong>LPI</strong></td><td>Projects pending registration · progress reports still to upload · <strong>rejected reports needing resubmission</strong> (follows the latest status — disappears on resubmission or grading).</td></tr>
                            <tr><td><strong>Reviewer</strong></td><td>Assigned projects pending your review · accepted projects pending your grading.</td></tr>
                        </tbody>
                    </table>
                </div>
                <p><strong>Event e-mails</strong> (gated by the MAIL_ENABLED switch; editable under Email Templates; every send logged in the Email Send Log): project imported / added to a call, registration confirmed, reviewer assigned or un-assigned, proposal accepted (once per round) or rejected (once per round), progress updated, and <em>report graded — fired once when the consensus completes</em>. Manual admin e-mails (Send Email page) bypass the switch.</p>
            </div>
        </section>

        {{-- 9. Deadlines & locking --}}
        <section id="deadlines" class="panel manual-section">
            <div class="panel-head"><h2><i class="fas fa-clock"></i> 9. Deadlines &amp; Locking</h2></div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="fluent-table">
                        <thead><tr><th>Deadline (per research call)</th><th>Controls</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Progress report deadline</strong></td><td>Progress Report 1 upload window for the LPI; reviewers may only grade after it passes.</td></tr>
                            <tr><td><strong>Progress report 2 deadline</strong> (extended projects)</td><td>PR2 / readiness report window; same grading rule.</td></tr>
                            <tr><td><strong>Final report deadline</strong></td><td>Final report upload window; also freezes project data (outcomes/students/contributions) and gates final grading.</td></tr>
                        </tbody>
                    </table>
                </div>
                <ul class="manual-steps">
                    <li><strong>Resubmissions are exempt.</strong> After a report-type rejection (and the admin's Send to LPI), the LPI's upload re-opens for version 2 even if the deadline has passed; ethical approvals are never deadline-gated.</li>
                    <li>A missing deadline is treated as "no window" for uploads — contact the research office to set it on the research call.</li>
                </ul>
            </div>
        </section>

    </div>
</div>
