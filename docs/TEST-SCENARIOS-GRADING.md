# Test Scenarios — Multi-Reviewer Grading & Consensus Workflow

Detailed test scenarios for the multi-reviewer grading feature: several reviewers
(and the admin) can grade the same project, each keeping an independent grading
set, while the project-level workflow status advances only on **consensus**
(every assigned reviewer has submitted; any rejection wins).

**Environment**

| Item | Value |
|---|---|
| Test database | `rtsnew_test` (pinned in `phpunit.xml`, every test rolls back via `DatabaseTransactions` — real data untouched) |
| Runner | PHPUnit 9 (`vendor/bin/phpunit`) |
| PHP | `C:\xampp74\php__\php.exe` |
| App under test | Full HTTP kernel — every scenario is executed as a real HTTP request (routing, auth, validation, controllers, Blade, MySQL) |

**How to read a scenario**

* **Preconditions** – state that must exist before the first step.
* **Steps** – what the tester does (UI wording where the check is visual).
* **Expected** – the pass condition.
* **Automated by** – `Class::method` executed by PHPUnit, or *Manual* if it
  requires visual/eyeball confirmation.

---

## A. Reviewer assignment (multi-reviewer)

### A-01 First assignment creates an unclaimed assignment
* **Preconditions:** Admin logged in. Project exists with **no** reviewer assigned.
* **Steps:**
  1. Projects page → Actions → *Assign Reviewer* → pick Reviewer A → *Assign Reviewer*.
* **Expected:**
  * Success toast "Reviewer assigned successfully."
  * Assignment row exists with `claimed_at = NULL` (not yet claimed).
  * Project workflow status = **Assigned**.
* **Automated by:** `GradingWorkflowScenariosTest::test_a01_first_assignment_is_unclaimed_and_sets_assigned_status`

### A-02 Second reviewer is added alongside (never replaces)
* **Preconditions:** Reviewer A assigned **and already claimed** (status = Claimed).
* **Steps:**
  1. Projects page → Actions → *Add Reviewer* → pick Reviewer B → submit.
* **Expected:**
  * **Both** A and B remain assigned (A's row untouched, A's `claimed_at` kept).
  * B's `claimed_at = NULL` (he must claim for himself).
  * Project status stays **Claimed** (adding a reviewer must not roll the workflow back to Assigned).
  * Assign modal shows "1 reviewer(s) already assigned" and marks A as "already assigned" in the dropdown.
* **Automated by:** `GradingWorkflowScenariosTest::test_a02_second_reviewer_is_added_without_rolling_back_status` (+ `MultiReviewerGradingTest::test_second_reviewer_is_added_without_replacing`)

### A-03 Re-assigning the same reviewer is idempotent
* **Steps:** Assign Reviewer A to the project a second time.
* **Expected:** Success response "Selected reviewer is already assigned to this project."; assignment count unchanged (no duplicate row).
* **Automated by:** `GradingWorkflowScenariosTest::test_a03_reassigning_the_same_reviewer_is_idempotent`

### A-04 A reviewer who previously rejected cannot be re-assigned
* **Preconditions:** Reviewer B has a `reviewer_rejections` row for this project.
* **Steps:** Try to assign Reviewer B.
* **Expected:** HTTP 422 — "This reviewer previously rejected this project and cannot be reassigned."
* **Automated by:** `GradingWorkflowScenariosTest::test_a04_reviewer_who_rejected_cannot_be_reassigned`

### A-05 Only admins can assign
* **Steps:** As (non-admin) Reviewer A, POST the assign endpoint.
* **Expected:** HTTP 403; no assignment rows change.
* **Automated by:** `GradingWorkflowScenariosTest::test_a05_only_admins_can_assign_reviewers`

### A-06 The admin MAY remove a claimed reviewer (no deadlock)
* **Preconditions:** Reviewer A assigned and **claimed**; Reviewer B assigned and already submitted.
* **Steps:** Admin opens the Un-assign control for A (Projects menu / Reviewer Assignment page) with a reason and confirms.
* **Expected:** Removal succeeds (no "already claimed" refusal); A is notified; the `reviewer_unassigned` status is only recorded when nobody remains assigned. See D-06 for the consensus completion that follows.
* **Automated by:** `GradingWorkflowScenariosTest::test_d06_unassigning_the_blocker_completes_the_stage` (removal of the claimed B)

---

## B. Claiming (per reviewer)

### B-01 Menu follows MY claim, not the project status
* **Preconditions:** Reviewer A and B both assigned, nobody claimed.
* **Steps:**
  1. Open the project's Actions menu as Reviewer A → expect **Accept/Reject Proposal**.
  2. A accepts the proposal.
  3. Re-open A's menu → expect **Grade Project** (no more claim).
  4. Open the same menu as Reviewer B → still **Accept/Reject Proposal**.
* **Expected:** A's claim never unlocks B's grading.
* **Automated by:** `MultiReviewerGradingTest::test_claim_is_tracked_per_reviewer`

### B-02 Accept records the Claimed status once, on MY assignment row
* **Steps:** Reviewer A accepts the proposal.
* **Expected:** A's assignment row gets `claimed_at` timestamp; project status = **Claimed** — recorded only from a *pre-claim* state (registered / assigned / proposal-rejected / reviewer-un-assigned / no status), so a later reviewer's claim never regresses a post-claim status (progress_added, graded, …), and the LPI is mailed the proposal-accepted event **once per round** (not once per reviewer).
* **Automated by:** `MultiReviewerGradingTest::test_claim_is_tracked_per_reviewer` (asserts both `claimed_at` and status)

### B-03 Rejecting removes only MY assignment
* **Preconditions:** A and B assigned, neither claimed.
* **Steps:** As Reviewer A, Actions → Accept/Reject Proposal → **Reject** with a reason → confirm.
* **Expected:**
  * A's assignment row is deleted; **B stays assigned**.
  * A gets a `reviewer_rejections` row (can never be re-assigned).
  * Project status = **Proposal Rejected**.
* **Automated by:** `GradingWorkflowScenariosTest::test_b03_rejecting_removes_only_that_reviewer`

### B-04 Another reviewer's pivot id cannot be used
* **Steps:** As Reviewer A, submit the claim decision carrying Reviewer B's assignment id.
* **Expected:** HTTP 403 "You are not authorized to make a decision on this proposal."; nothing changes.
* **Automated by:** `GradingWorkflowScenariosTest::test_b04_reviewer_cannot_use_another_reviewers_pivot_id`

### B-05 Claiming is enforced server-side (page AND save)
* **Preconditions:** Reviewer A assigned but **not yet claimed**.
* **Steps:**
  1. Open `/projects/{id}/grading` directly by URL as A.
  2. POST a grade to `/grading/{id}/save-progress-grade` directly.
  3. Claim the proposal, then repeat step 1.
* **Expected:** Steps 1–2 → HTTP 403 "Accept the proposal (claim the project) before grading."; after claiming, the page opens (HTTP 200). The menu flow is no longer the only enforcement.
* **Automated by:** `GradingWorkflowScenariosTest::test_b05_grading_requires_own_claim`

---

## C. Draft → submit → read-only lifecycle (per user)

### C-01 Draft is editable and prefilled on the next visit
* **Preconditions:** Progress report submitted, deadline open, A claimed.
* **Steps:**
  1. As Reviewer A open the grading page → score a section → **Save as Draft**.
  2. Log in again as A, open the grading page.
* **Expected:** Toast "Progress grade saved successfully."; the form reappears **editable** with A's saved ratings/comments prefilled (row `publish = pending`).
* **Automated by:** `MultiReviewerGradingTest::test_drafts_are_isolated_per_reviewer`

### C-02 Submitted grades become read-only for that reviewer
* **Steps:** Reviewer A clicks **Submit** (publish = Accepted). Re-open the grading page as A.
* **Expected:** No editable form — a **read-only panel** shows A's scores, comments and the Accepted banner.
* **Automated by:** `MultiReviewerGradingTest::test_submitted_grades_lock_only_for_that_reviewer`, `ConsensusGradingTest::test_form_is_gated_per_user_not_by_status`

### C-03 Reviewers are fully isolated from each other's sets
* **Preconditions:** A and B assigned, both claimed.
* **Steps:**
  1. A saves a draft with a distinctive comment.
  2. Open the grading page as B.
* **Expected:** B sees a **blank** form — none of A's ratings/comments are visible. After B submits, A still sees only his own set (and vice versa). The admin sees only **his own** set on the same page; all sets are visible together only on the project page/report card.
* **Automated by:** `MultiReviewerGradingTest::test_drafts_are_isolated_per_reviewer`, `MultiReviewerGradingTest::test_submitted_grades_lock_only_for_that_reviewer`

### C-04 Visual confirmation of the lifecycle (manual)
* **Steps:** Perform C-01/C-02 in a browser; also confirm the save toast text and the read-only banner styling.
* **Expected:** Toast reads "Progress grade saved successfully."; submitted state shows the green Accepted (or red Rejected) banner instead of radio buttons.
* **Automated by:** *Manual* (behaviour itself is covered by C-01/C-02; wording/appearance is visual)

---

## D. Consensus — when the workflow status advances

### D-01 First submission keeps the stage open
* **Preconditions:** A and B assigned+claimed; progress report submitted.
* **Steps:** As A, submit the progress grade (Accepted).
* **Expected:** Project status is **unchanged** (stage stays open for B); toast says "…The stage will complete once every assigned reviewer has submitted."
* **Automated by:** `ConsensusGradingTest::test_progress_stage_waits_for_every_reviewer`

### D-02 Status flips once, when the last reviewer submits
* **Steps:** As B, submit the progress grade (Accepted).
* **Expected:** Status becomes **Progress Reviewed** exactly once; no duplicate status rows from earlier submits.
* **Automated by:** `ConsensusGradingTest::test_progress_stage_waits_for_every_reviewer`

### D-03 Any rejection wins at completion
* **Steps:**
  1. A submits **Rejected** (reason + type) → status must NOT change yet.
  2. B submits **Accepted**.
* **Expected:** Status becomes **Progress Rejected**; the rejection reason/type (recorded on A's row) is copied onto the status metadata so the admin rejection-review flow can run; the LPI receives no "graded" email.
* **Automated by:** `ConsensusGradingTest::test_rejection_wins_on_consensus_completion`

### D-04 Standalone admin grade completes immediately
* **Preconditions:** Project with **no** assigned reviewers (admin grades alone).
* **Steps:** As admin, submit the progress grade.
* **Expected:** Status becomes **Progress Reviewed** on that single submission.
* **Automated by:** `GradingWorkflowScenariosTest::test_d04_standalone_admin_grade_completes_immediately`

### D-05 LPI "graded" email fires exactly once, at completion
* **Preconditions:** A and B assigned; project has an LPI with an e-mail address; `MAIL_ENABLED` on (array mailer in tests).
* **Steps:**
  1. A submits → inspect the e-mail send log.
  2. B submits (completion) → inspect again.
* **Expected:** **No** `progress_graded` mail is logged after step 1; **exactly one** after step 2 (never one per reviewer).
* **Automated by:** `GradingWorkflowScenariosTest::test_d05_lpi_email_sent_once_at_completion`

### D-06 Save toast wording while waiting (manual)
* **Steps:** While a stage is incomplete, submit your grade.
* **Expected:** Toast reads "Progress grade saved. The stage will complete once every assigned reviewer has submitted."
* **Automated by:** *Manual* (visual wording check)

### D-07 Un-assigning the blocking reviewer completes the stage
* **Preconditions:** A and B assigned+claimed; A submitted; B silent (stage open).
* **Steps:** Admin un-assigns B (allowed even though B claimed — see A-06).
* **Expected:** The stage completes immediately for the remaining reviewer — status becomes **Progress Reviewed** and the LPI gets the single graded mail; only A remains assigned. Removing a blocker can never leave the consensus wedged.
* **Automated by:** `GradingWorkflowScenariosTest::test_d06_unassigning_the_blocker_completes_the_stage`

### D-08 A reviewer added after completion cannot reopen the stage (freeze)
* **Preconditions:** Stage completed (Progress Reviewed) by A and B.
* **Steps:**
  1. Admin assigns reviewer C afterwards.
  2. C claims the proposal.
  3. C submits a **rejected** grade.
* **Expected:** The status never changes — adding, claiming and even rejecting by the late reviewer are all stored (C's rejected set is kept and visible on the report card) but the completed stage is **frozen**; no surprise re-entry into the rejection flow for the LPI.
* **Automated by:** `GradingWorkflowScenariosTest::test_d07_late_reviewer_cannot_reopen_a_completed_stage`

---

## E. Evaluate Projects — the admin's personal queue

### E-01 Row appears when a stage is ready and the admin has not graded
* **Preconditions:** Progress report submitted; admin has no published progress set.
* **Steps:** As admin, open **Evaluate Projects**.
* **Expected:** The project is listed (owed stage label shown, assigned reviewer name listed).
* **Automated by:** `ConsensusGradingTest::test_evaluate_queue_is_admin_personal`

### E-02 Another reviewer's submission does NOT remove the row
* **Steps:** Reviewer A submits his grade → reload the queue as admin.
* **Expected:** The row **remains** (it only disappears for whoever submits).
* **Automated by:** `ConsensusGradingTest::test_evaluate_queue_is_admin_personal`

### E-03 The row leaves once the ADMIN submits
* **Steps:** Admin submits his own set → reload the queue.
* **Expected:** The row is gone for the admin (other viewers' queues unaffected).
* **Automated by:** `ConsensusGradingTest::test_evaluate_queue_is_admin_personal`

### E-04 All owed stages shown in one label
* **Preconditions:** Project has both a submitted progress **and** final report; admin has submitted neither.
* **Expected:** Single row labelled `Progress Report 1 · Final Report`.
* **Automated by:** `GradingWorkflowScenariosTest::test_e04_queue_shows_all_owed_stages_in_one_label`

---

## F. Final report stage (same rules)

### F-01 Graded only after every reviewer submitted
* **Steps:** A submits final grades (Accepted) → then B submits.
* **Expected:** Status is **not** Graded after step 1; becomes **Graded** after step 2.
* **Automated by:** `ConsensusGradingTest::test_final_stage_waits_for_every_reviewer`

### F-02 Menu switches to View Grades after Graded
* **Expected:** Once status = Graded, every reviewer's Actions menu shows **View Grades** only (no Grade Project).
* **Automated by:** `ConsensusGradingTest::test_final_stage_waits_for_every_reviewer` (menu assertions)

### F-03 Both final sets visible on the report card (manual)
* **Steps:** After both submitted, open the project's Report Card / project page → Grading section.
* **Expected:** Both reviewers' final grades are listed side by side (aggregate views show everyone; personal views stay isolated).
* **Automated by:** *Manual* (aggregate rendering check)

---

## G. Rejection & resubmission (v2)

### G-01 A rejected resubmission re-opens EVERY reviewer's set
* **Preconditions:** A accepted, B rejected for the progress stage.
* **Steps:** LPI uploads a new progress report version (v2) after the rejection.
* **Expected:** **Both** rows archive their scores into `previous_grading` and reset to `publish = pending` with rejection fields cleared → both reviewers see blank, editable forms for v2.
* **Automated by:** `GradingWorkflowScenariosTest::test_g01_resubmission_resets_every_reviewers_set`

### G-02 Ethical / Other rejections are NOT reset by a v2 upload
* **Preconditions:** Latest progress rejection has type *Missing/Invalid Ethical Approval* (or *Other*).
* **Steps:** LPI uploads a v2 report.
* **Expected:** Existing sets are kept (the reviewer edits the rejected grading in place instead) — rows unchanged.
* **Automated by:** `GradingWorkflowScenariosTest::test_g02_ethical_rejection_is_not_reset_on_resubmission`

### G-03 Blank forms after reset in the real UI (manual)
* **Steps:** After G-01, log in as each reviewer and open the grading page.
* **Expected:** Each reviewer gets an empty editable form (previous scores only in the "previous grading" archive view).
* **Automated by:** *Manual* (G-01 proves the data; this confirms the screen)

---

## H. Policy & removed legacy endpoints

### H-01 The proposal upload API refuses LPIs
* **Steps:** As an LPI, POST a proposal to `wizard.upload-proposal` (the endpoint the removed wizard button used to call).
* **Expected:** HTTP 403 "Only administrators can upload proposal documents." — the research-office-only policy is enforced at the API, not just in the UI. The same request as an admin is **not** rejected by the policy gate.
* **Automated by:** `GradingWorkflowScenariosTest::test_h01_lpi_cannot_upload_proposal_via_api`

### H-02 Legacy consensus-bypass endpoints are gone
* **Steps:** POST to the retired endpoints: `/grading/{id}/submit-grade`, `/grading/{id}/final-grade`, `/workflow/submit-progress-review`.
* **Expected:** HTTP 404 for all three — the only ways to advance a stage are the real grade forms (per-user, consensus) — no authenticated back door can force `Graded` or flip reviewed/rejected on its own.
* **Automated by:** `GradingWorkflowScenariosTest::test_h02_legacy_consensus_bypass_routes_are_removed`

---

## I. Full lifecycle (proposal rejection → re-assignment → consensus → resubmission → re-grade)

### I-01 The whole chain in one scenario
* **Preconditions:** A progress-stage project with a progress report and a passed deadline; three active reviewers (A, B, C) and an admin.
* **Steps:**
  1. Admin assigns Reviewer A.
  2. A rejects the proposal.
  3. Admin re-assigns Reviewer B (a different reviewer).
  4. B accepts (claims).
  5. Admin also assigns Reviewer C alongside B.
  6. B and C both claim; B submits an accepted progress set, C submits a rejected one.
  7. Admin forwards the rejection to the LPI (`send_to_lpi`).
  8. LPI uploads a v2 progress report.
  9. Reviewer C (who rejected) grades v2 again, then B re-grades.
* **Expected:**
  * After A rejects: A is removed from the assignment, a `reviewer_rejections` row exists, status = `proposal_rejected`, and A can never be reassigned (422).
  * Re-assigning B is allowed (a different reviewer) and records `Assigned`; B's claim records `Claimed`; adding C does not roll the status back.
  * B's acceptance alone keeps the stage open; C's rejection completes the consensus and the rejection wins → `progress_rejected`.
  * Admin forwarding records `progress_rejection_reviewed` with `action=send_to_lpi`.
  * The LPI's v2 upload returns `version=2` and archives/resets **both** reviewers' sets to `publish=pending` (previous sets kept in `previous_grading`); claims survive so neither has to re-claim.
  * C re-grading v2 leaves the consensus open (B has not re-submitted); B's re-grade completes it → `progress_reviewed`.
* **Automated by:** `GradingWorkflowScenariosTest::test_i01_proposal_reject_reassign_consensus_resubmission_regrade`

### I-02 Ethical rejection lifecycle (routes through the admin)
* **Preconditions:** A progress-stage project with a progress report and a passed deadline; one assigned + claimed reviewer, an admin and an LPI.
* **Steps:**
  1. The reviewer rejects the progress report with `rejection_type = ethical`.
  2. The admin confirms the rejection is visible (`hasUnreviewedRejection`) and forwards it (`send_to_lpi`).
  3. The LPI re-sends the **ethical approval** document (`type=ethical`).
  4. The LPI tries to upload the **progress report** again.
  5. The reviewer re-reviews and amends the grading to **accepted**.
  6. `archiveAndResetGrading` is invoked for `progress`.
* **Expected:**
  * The rejection records `progress_rejected` with `metadata.rejection_type=ethical`.
  * The admin **sees** it and the forward **succeeds** (no 422) → `progress_rejection_reviewed` with `action=send_to_lpi` + `rejection_type=ethical`; the LPI notification asks for the **ethical approval**, not the report.
  * The ethical-approval upload **succeeds** (`type=ethical` is never deadline- or hold-gated).
  * The **progress-report** upload stays **locked (423)** — the report itself is never re-uploaded for an ethical rejection.
  * The reviewer's re-review **resolves** the rejection → `progress_reviewed` (the forward moved the status past the freeze).
  * `archiveAndResetGrading` **early-returns** — the re-review set stands, nothing is reset (contrast I-01, where every set is reset on a report v2).
* **Automated by:** `GradingWorkflowScenariosTest::test_i02_ethical_rejection_routes_through_admin_and_lpi_resends_ethical`

### I-03 Final report rejection chain (reaches the terminal Graded state)
* **Preconditions:** A final-stage project (final report submitted, `final_rpt_deadline` passed, no `final_rejected`/`Graded` on record); one assigned + claimed reviewer, an admin and an LPI.
* **Steps:**
  1. The reviewer rejects the **final** report (`publish=rejected`, `rejection_type=report`).
  2. The admin forwards the rejection (`report_type=final`, `send_to_lpi`).
  3. The LPI uploads a new final report (v2).
  4. The reviewer re-grades the v2 as **accepted**.
* **Expected:**
  * Step 1 → `final_rejected` on consensus; the `final_report_grading` row stores `publish=rejected`, `rejection_type=report`.
  * Step 2 → `final_rejection_reviewed` with `action=send_to_lpi`.
  * Step 3 → HTTP 200 with `version=2` (unlocked past the deadline by the resubmission request); **v1 is kept** (two final rows); every final grading set is archived (`previous_grading`) and reset to `publish=pending`.
  * Step 4 → the stage completes to the terminal **`Graded`** status and the reviewer's set stores `accepted`.
* **Automated by:** `GradingWorkflowScenariosTest::test_i03_final_rejection_admin_forward_v2_regrade_reaches_graded`

### I-04 Final report rejection with type `ethical`
* **Preconditions:** Same final-stage fixture as I-03.
* **Steps:**
  1. The reviewer rejects the **final** report with `rejection_type = ethical`.
  2. The admin forwards it (`report_type=final`, `send_to_lpi`).
  3. The LPI uploads the ethical approval (`type=ethical`).
  4. The LPI tries to upload the final report and then the readiness report.
  5. The reviewer re-reviews and submits **accepted**.
* **Expected:**
  * `final_rejected` carries `rejection_type=ethical`; the admin **sees** it (`hasUnreviewedRejection('final')`) and the forward **succeeds** → `final_rejection_reviewed` (carrying `rejection_type`).
  * The ethical-approval upload **succeeds**.
  * The **final** upload **and** the **readiness** upload both return **423** — readiness shares the final rejection hold, and neither report is re-uploaded for an ethical rejection.
  * The re-review resolves to the terminal **`Graded`** status.
* **Automated by:** `GradingWorkflowScenariosTest::test_i04_final_ethical_rejection_routes_through_admin_to_graded`

---

## Running the suite

```powershell
# Grading-workflow scenarios only, human-readable output:
C:\xampp74\php__\php.exe vendor\bin\phpunit --testdox `
  --filter "GradingWorkflowScenariosTest|ConsensusGradingTest|MultiReviewerGradingTest"

# Full regression (all suites in the project):
C:\xampp74\php__\php.exe vendor\bin\phpunit
```

Related suites outside this document: `CommitmentEditTest` (admin-only commitment
editing), `WorkflowModalTest` (modal endpoints), `HelpCenterTabsTest` (manual
tab visibility), `SecurityAccessTest`, and the data-integrity suites.
