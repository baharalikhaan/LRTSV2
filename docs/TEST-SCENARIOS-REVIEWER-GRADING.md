# Test Scenarios — Reviewer Grading & the Admin Evaluate Projects Queue

Detailed test scenarios for the **reviewer part** (grading page access, the
progress/final save endpoints, the verification AJAX endpoint, the Graded
Projects page and the View-Grade modal) and the **admin Evaluate Projects
queue** (`/projects/pending-reviews`).

**Environment**

| Item | Value |
|---|---|
| Test database | `rtsnew_test` (pinned in `phpunit.xml`, every test rolls back via `DatabaseTransactions`) |
| Runner | PHPUnit 9 (`vendor/bin/phpunit`) |
| PHP | `C:\xampp74\php__\php.exe` |
| App under test | Full HTTP kernel — every scenario is a real HTTP request |

**Server-side rules the scenarios verify** (`GradingController`, `ProjectController`)

* **Controller-level role gate:** every `GradingController` method requires an
  active Reviewer or Admin (`GradingController::__construct`), else 403.
* **Grading page** (`GET /projects/{id}/grading`): assigned reviewer **or**
  admin, then own claim (`claimed_at`) **or** admin — two separate 403s.
* **save-progress-grade / save-final-grade:** validation runs first, then
  assigned → own-claim → stage-file → deadline gates (admin bypasses the first
  two). `save_action=draft` always stores `publish=pending`. Consensus runs on
  submit.
* **Deadline semantics:** a **null** deadline is treated as *always open* for
  grading (the opposite of the LPI upload lock, where null = passed).
* **updateVerification:** validates, then an all-or-nothing IDOR guard — every
  id must belong to a project the caller is assigned to (admins bypass); one
  foreign id 403s the whole batch before any write.
* **Evaluate Projects queue** (`ProjectController::pendingReviews`): admin-only;
  a project appears only when it has **≥1 assigned reviewer** and the *logged-in
  admin* has not published a set for a ready stage. It is **per-admin** — another
  reviewer submitting never clears the admin's row, and a draft (`publish=pending`)
  still counts as owed.
* **Graded Projects page** (`GET /grading`): lists only the caller's assigned
  projects; the cycle filter resolves through the program (`programs.cycle_id`).

---

## A. Grading page access

### A-01 Assigned + claimed reviewer opens the page
* **Preconditions:** Reviewer A assigned and claimed; progress report submitted.
* **Steps:** As Reviewer A, `GET /projects/{id}/grading`.
* **Expected:** HTTP 200 — the grading page renders.
* **Automated by:** `ReviewerGradingTest::test_a01_assigned_and_claimed_reviewer_opens_grading_page`

### A-02 Assigned but unclaimed reviewer is rejected
* **Preconditions:** Reviewer A assigned, **not** claimed.
* **Steps:** As Reviewer A, open the grading page.
* **Expected:** HTTP 403 — "Accept the proposal (claim the project) before grading."
* **Automated by:** `ReviewerGradingTest::test_a02_assigned_but_unclaimed_reviewer_is_rejected`

### A-03 Unassigned reviewer is rejected
* **Steps:** As Reviewer B (not assigned), open the grading page.
* **Expected:** HTTP 403 — "You are not assigned to review this project."
* **Automated by:** `ReviewerGradingTest::test_a03_unassigned_reviewer_is_rejected`

### A-04 Admin opens the page without assignment
* **Steps:** As an Admin (not in `projects_reviewers`), open the grading page.
* **Expected:** HTTP 200 — the admin bypasses both the assignment and claim gates.
* **Automated by:** `ReviewerGradingTest::test_a04_admin_opens_grading_page_without_assignment`

### A-05 An LPI cannot open the page
* **Steps:** As an active LPI, open the grading page.
* **Expected:** HTTP 403 — "Access denied. Reviewers or Admins only." (controller-level gate).
* **Automated by:** `ReviewerGradingTest::test_a05_lpi_cannot_open_grading_page`

### A-06 The legacy grade alias redirects
* **Steps:** As an assigned+claimed reviewer, `GET /grading/{id}/grade`.
* **Expected:** Redirect to `/projects/{id}/grading` (the unified page).
* **Automated by:** `ReviewerGradingTest::test_a06_grade_alias_redirects_to_unified_page`

---

## B. Progress grading save

### B-01 A submitted accepted set is stored
* **Preconditions:** Reviewer A assigned + claimed; progress submitted; deadline passed (or null).
* **Steps:** `POST /grading/{id}/save-progress-grade` with `save_action=submit`, `publish=accepted`, four ratings.
* **Expected:** HTTP 200 `{success:true}`; a `progress_report_grading` row with `publish=accepted`, `isAccepted=1`, `report_type=progress`.
* **Automated by:** `ReviewerGradingTest::test_b01_submitted_accepted_progress_is_stored`

### B-02 A draft always stores `publish=pending`
* **Steps:** Send the same payload with `save_action=draft` (even with `publish=accepted`).
* **Expected:** HTTP 200; the row is stored with `publish=pending` — a draft never counts as a submission.
* **Automated by:** `ReviewerGradingTest::test_b02_draft_forces_publish_pending`

### B-03 A rejection requires reason and type
* **Steps:** Submit `publish=rejected` without `rejection_reason`/`rejection_type`.
* **Expected:** HTTP 422 with validation errors on both fields.
* **Automated by:** `ReviewerGradingTest::test_b03_rejection_requires_reason_and_type`

### B-04 Ratings are bounded 1–5
* **Steps:** Submit with `achievementsRating=9`.
* **Expected:** HTTP 422 on `achievementsRating`.
* **Automated by:** `ReviewerGradingTest::test_b04_rating_outside_range_is_rejected`

### B-05 An unassigned reviewer cannot save
* **Steps:** As Reviewer B (unassigned), POST the save endpoint.
* **Expected:** HTTP 403 — "You are not assigned to review this project."
* **Automated by:** `ReviewerGradingTest::test_b05_unassigned_reviewer_cannot_save`

### B-06 An unclaimed reviewer cannot save
* **Steps:** As assigned-but-unclaimed Reviewer A, POST the save endpoint.
* **Expected:** HTTP 403 — "Accept the proposal (claim the project) before grading."
* **Automated by:** `ReviewerGradingTest::test_b06_unclaimed_reviewer_cannot_save`

### B-07 Grading is locked before the deadline
* **Preconditions:** `prog_rpt_deadline` in the future.
* **Steps:** As assigned+claimed Reviewer A, POST the save endpoint.
* **Expected:** HTTP 403 — "Grading opens after the … deadline."
* **Automated by:** `ReviewerGradingTest::test_b07_grading_locked_before_deadline`

### B-08 Grading is locked with no progress submission
* **Steps:** Delete the progress submission, then POST the save endpoint.
* **Expected:** HTTP 403 — "Grading is locked until then."
* **Automated by:** `ReviewerGradingTest::test_b08_grading_locked_without_progress_submission`

### B-09 Progress Report 2 requires the extended-progress flag
* **Preconditions:** `extended_progress` off.
* **Steps:** POST with `report_type=progress2`.
* **Expected:** HTTP 403 — "Progress Report 2 is not enabled for this project."
* **Automated by:** `ReviewerGradingTest::test_b09_progress2_requires_extended_progress`

### B-10 `reserved` is a valid publish value on progress
* **Steps:** Submit with `publish=reserved`.
* **Expected:** HTTP 200; the row stores `publish=reserved` (progress allows the four-value enum; final does not — see C-05).
* **Automated by:** `ReviewerGradingTest::test_b10_reserved_publish_accepted_on_progress`

---

## C. Final grading save

### C-01 The total is the sum of the section scores
* **Preconditions:** Final submitted; deadline passed (or null); Reviewer A assigned + claimed.
* **Steps:** Submit accepted with `scoreA=5, scoreB=6, scoreC=7`.
* **Expected:** HTTP 200; the row stores `publish=accepted`, `isAccepted=1` and `total=18` (5+6+7 — raw points, not the 1–5 grades).
* **Automated by:** `ReviewerGradingTest::test_c01_final_total_is_sum_of_scores`

### C-02 A rejection requires reason and type
* **Steps:** Submit `publish=rejected` without the rejection fields.
* **Expected:** HTTP 422 on both.
* **Automated by:** `ReviewerGradingTest::test_c02_final_rejection_requires_reason_and_type`

### C-03 Grading is locked with no final submission
* **Steps:** Delete the final submission, then POST.
* **Expected:** HTTP 403.
* **Automated by:** `ReviewerGradingTest::test_c03_final_grading_locked_without_submission`

### C-04 Grading is locked before the deadline
* **Preconditions:** `final_rpt_deadline` in the future.
* **Expected:** HTTP 403.
* **Automated by:** `ReviewerGradingTest::test_c04_final_grading_locked_before_deadline`

### C-05 `reserved` is rejected on final
* **Steps:** Submit with `publish=reserved`.
* **Expected:** HTTP 422 on `publish` (final allows only accepted/rejected/pending).
* **Automated by:** `ReviewerGradingTest::test_c05_reserved_publish_rejected_on_final`

### C-06 A draft always stores `publish=pending`
* **Steps:** Send an accepted payload with `save_action=draft`.
* **Expected:** HTTP 200; the row stores `publish=pending`.
* **Automated by:** `ReviewerGradingTest::test_c06_final_draft_forces_publish_pending`

---

## D. Reviewer verification (`updateVerification`)

### D-01 An assigned reviewer verifies their own project's outcomes
* **Steps:** As an assigned reviewer, `POST /grading/update-verification` with `type=outcome`, `ids=[…]`, `status=verified`.
* **Expected:** HTTP 200 `{success:true}`; the rows' `verifcation_by_reviewer` becomes `verified`.
* **Automated by:** `ReviewerGradingTest::test_d01_assigned_reviewer_verifies_own_outcomes`

### D-02 An unassigned reviewer is refused
* **Steps:** As an unassigned reviewer, verify an outcome of a project they are not on.
* **Expected:** HTTP 403; **no** row changes.
* **Automated by:** `ReviewerGradingTest::test_d02_unassigned_reviewer_cannot_verify`

### D-03 An admin may verify any rows
* **Steps:** As an admin with no assignment, verify an outcome.
* **Expected:** HTTP 200 — the admin bypasses the assignment check.
* **Automated by:** `ReviewerGradingTest::test_d03_admin_can_verify_any_rows`

### D-04 One foreign id aborts the whole batch
* **Preconditions:** Reviewer assigned to project X only.
* **Steps:** Verify `[own outcome of X, foreign outcome of Y]`.
* **Expected:** HTTP 403; **neither** row changes — the batch is all-or-nothing, never a partial write.
* **Automated by:** `ReviewerGradingTest::test_d04_one_foreign_id_aborts_whole_batch`

### D-05 Invalid type or status is rejected
* **Steps:** POST `type=bogus`, then `status=bogus`.
* **Expected:** HTTP 422 on the offending field.
* **Automated by:** `ReviewerGradingTest::test_d05_invalid_type_or_status_is_rejected`

---

## E. Graded Projects page

### E-01 A reviewer sees their assigned projects
* **Steps:** As a reviewer assigned to a project, `GET /grading`.
* **Expected:** HTTP 200; the project's grading link (`/projects/{id}/grading`) is present.
* **Automated by:** `ReviewerGradingTest::test_e01_reviewer_sees_assigned_projects`

### E-02 An admin with no assignment sees an empty list
* **Steps:** As an admin with no `projects_reviewers` rows, `GET /grading`.
* **Expected:** HTTP 200; the project's grading link is absent.
* **Automated by:** `ReviewerGradingTest::test_e02_admin_without_assignment_sees_empty_list`

### E-03 The cycle filter narrows the list
* **Steps:** `GET /grading?cycle_id={the project's cycle}` then `?cycle_id={another cycle}`.
* **Expected:** The matching cycle keeps the project; a non-matching cycle excludes it. (This filter resolves through `programs.cycle_id` — it must not 500.)
* **Automated by:** `ReviewerGradingTest::test_e03_cycle_filter_narrows_list`

---

## F. View-Grade modal

### F-01 An assigned reviewer sees their own grading
* **Preconditions:** Reviewer A assigned + claimed + submitted an accepted progress set.
* **Steps:** `GET /workflow/view-grade/{projectId}`.
* **Expected:** HTTP 200 — the modal HTML renders their own set.
* **Automated by:** `ReviewerGradingTest::test_f01_assigned_reviewer_sees_own_grading`

### F-02 An unassigned reviewer gets an error payload
* **Steps:** As Reviewer B (unassigned), `GET /workflow/view-grade/{projectId}`.
* **Expected:** HTTP 200 with `{success:false, error:"You are not assigned…"}`.
* **Automated by:** `ReviewerGradingTest::test_f02_unassigned_reviewer_gets_error_payload`

### F-03 An admin bypasses the assignment check
* **Steps:** As an admin with no assignment, open the modal.
* **Expected:** HTTP 200.
* **Automated by:** `ReviewerGradingTest::test_f03_admin_bypasses_assignment_check`

---

## G. Evaluate Projects queue (admin)

### G-01 Reviewers cannot open the queue
* **Steps:** As a reviewer, `GET /projects/pending-reviews`.
* **Expected:** HTTP 403 — "Access denied. Admin only."
* **Automated by:** `ReviewerGradingTest::test_g01_reviewer_cannot_open_queue`

### G-02 A project with no reviewer never appears
* **Steps:** As an admin, open the queue for a project that has a progress report but no assigned reviewer.
* **Expected:** HTTP 200; the project's grading link is absent.
* **Automated by:** `ReviewerGradingTest::test_g02_project_without_reviewer_never_appears`

### G-03 The row survives another reviewer's submission
* **Preconditions:** Reviewer A assigned; project owes progress to the admin.
* **Steps:**
  1. Open the queue as the admin — the project is present.
  2. Reviewer A submits an accepted progress set.
  3. Re-open the queue as the admin.
* **Expected:** The project is **still** present — the queue is per-admin; another reviewer's submission never clears the admin's row.
* **Automated by:** `ReviewerGradingTest::test_g03_project_appears_until_admin_submits`

### G-04 The row clears once THIS admin submits
* **Steps:** As the admin, submit your own accepted progress set, then re-open the queue.
* **Expected:** The project is gone — the row cleared for the admin who submitted.
* **Automated by:** `ReviewerGradingTest::test_g04_row_clears_once_admin_submits`

### G-05 A draft still counts as owed
* **Steps:** As the admin, save a **draft** (`save_action=draft`), then open the queue.
* **Expected:** The project is still present — a draft is not a submission.
* **Automated by:** `ReviewerGradingTest::test_g05_draft_still_counts_as_owed`

### G-06 The status filter narrows the queue
* **Steps:** `GET /projects/pending-reviews?status={the project's status}` then `?status={another status}`.
* **Expected:** The matching status keeps the row; a non-matching status filters it out.
* **Automated by:** `ReviewerGradingTest::test_g06_status_filter_narrows_queue`

---

## Related suites

Consensus, claims, un-assign and resubmission lifecycle live in:

* `ConsensusGradingTest` / `MultiReviewerGradingTest` — consensus, per-user sets, claims
* `GradingWorkflowScenariosTest` — assignment, un-assign, legacy routes, admin queue labels
* `docs/TEST-SCENARIOS-GRADING.md` — the grading-side scenario catalogue
