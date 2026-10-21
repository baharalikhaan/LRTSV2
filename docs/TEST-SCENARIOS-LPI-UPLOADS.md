# Test Scenarios — LPI Report Uploads & Resubmission

Detailed test scenarios for the LPI upload side of the workflow: uploading
progress/final/PR2/readiness/ethical reports, the deadline locks, and the
full rejection → admin review → **v2 resubmission** chain (v1 is kept, every
reviewer set is archived & re-opened).

**Environment**

| Item | Value |
|---|---|
| Test database | `rtsnew_test` (pinned in `phpunit.xml`, every test rolls back via `DatabaseTransactions` — real data untouched) |
| Runner | PHPUnit 9 (`vendor/bin/phpunit`) |
| PHP | `C:\xampp74\php__\php.exe` |
| App under test | Full HTTP kernel — every scenario is executed as a real HTTP request (routing, auth, validation, controllers, Blade, MySQL) |
| Files on disk | Uploads write to the real `storage/app/uploads/...` tree; each test tracks the files it created and deletes them in `tearDown` (no `Storage::fake` — the controller checks `storage_path()` directly) |

**How to read a scenario**

* **Preconditions** — state that must exist before the first step.
* **Steps** — what the tester does (UI wording where the check is visual).
* **Expected** — the pass condition.
* **Automated by** — `Class::method` executed by PHPUnit.

**Server-side rules the scenarios verify** (`ProgressController`)

* `uploadFile` (`POST /progress/upload-submission/{id}`): PDF only, ≤10 MB; LPI
  ownership enforced (`authorizeLpi`); deadline enforced via `isTypeLocked`
  (a **null deadline counts as passed**); ethical uploads are never
  deadline-gated; Progress Report 2 additionally requires the project's
  `extended_progress` flag.
* **Versioning:** while no rejection exists, each type is a single replaceable
  v1 file. After a report-type rejection (status `*_rejected` /
  `*_rejection_reviewed`, or a rejected grading row), the next upload becomes
  **v2** — v1 stays on disk for comparison and a new `{id}_{type}_v2.pdf` is
  written. A v2 upload calls `archiveAndResetGrading`, which archives and
  re-opens **every** reviewer's set (`publish` → `pending`, previous grading
  kept in `previous_grading`).
* **Resubmission window:** `isResubmitRequested` is true when the latest
  `*_rejection_reviewed` status has `metadata.action = send_to_lpi` and no
  later grading/rejection exists. While true, the deadline lock is lifted for
  that report type (upload **and** the matching LPI form save).
* **Ethical/Other rejections** (`rejection_type = ethical|other`) skip the
  admin review step and *hold* the upload (no v2 expected — the reviewer
  edits the existing grading instead).
* `saveFinalReport` (`POST /progress/final/{id}`) records `final_added` and
  requires at least one readiness/final file; project data (outcomes etc.) is
  frozen by the final deadline via `isDataLocked` (never re-opened by a
  resubmission).

---

## A. Progress report upload (version 1)

### A-01 Upload happy path
* **Preconditions:** Active LPI owns the project; `prog_rpt_deadline` is in
  the future; no previous progress submission.
* **Steps:** As the LPI, `POST /progress/upload-submission/{id}` with a PDF
  and `type=progress` (UI: upload area on the progress page → *Upload*).
* **Expected:** HTTP 200 `success=true, version=1`; one
  `project_submissions` row (`type=progress`, `version=1`,
  `stored_filename={id}_progress.pdf`); file exists under
  `uploads/{year}/{grant}/progress_reports/`; `serveFile2` with the
  submission's `submission_id` serves the PDF (HTTP 200).
* **Automated by:** `LpiReportUploadTest::test_a01_progress_upload_stores_v1_and_serves_download`

### A-02 Re-upload replaces the single v1 row (no rejection yet)
* **Preconditions:** A-01 state (v1 uploaded, window still open).
* **Steps:** Upload a second PDF with `type=progress`.
* **Expected:** Still exactly **one** progress row (`version=1`); the first
  file is deleted from disk; the row's `stored_filename` stays
  `{id}_progress.pdf` but points at the new file.
* **Automated by:** `LpiReportUploadTest::test_a02_progress_reupload_replaces_single_row_while_open`

### A-03 Locked after the deadline
* **Preconditions:** `prog_rpt_deadline` in the past; no resubmission request
  on record.
* **Steps:** Upload `type=progress`.
* **Expected:** HTTP 423 — "The upload window for this report has closed…";
  no row, no file.
* **Automated by:** `LpiReportUploadTest::test_a03_progress_upload_locked_after_deadline`

### A-04 Non-PDF rejected by validation
* **Steps:** Upload a `.txt` file with `type=progress`.
* **Expected:** HTTP 422 validation error (`file` must be a PDF); no row.
* **Automated by:** `LpiReportUploadTest::test_a04_non_pdf_upload_rejected_by_validation`

### A-05 Non-owner LPI refused
* **Preconditions:** Project owned by a different user.
* **Steps:** A different active LPI uploads.
* **Expected:** HTTP 403 — "You are not the LPI for this project."
* **Automated by:** `LpiReportUploadTest::test_a05_non_owner_lpi_cannot_upload`

### A-06 Reviewers and guests refused
* **Steps:**
  1. As an active Reviewer, POST the upload endpoint.
  2. As a guest (no session), POST the upload endpoint.
* **Expected:** Reviewer → HTTP 403 ("You are not authorized…"); guest →
  redirected to the login page (HTTP 302).
* **Automated by:** `LpiReportUploadTest::test_a06_reviewer_and_guest_cannot_upload`

### A-07 The progress form save follows the same window
* **Preconditions:** Window open (deadline in the future).
* **Steps:**
  1. As the LPI, `POST /progress/save/{id}` (minimal body).
  2. Set `prog_rpt_deadline` in the past and repeat the same POST.
* **Expected:** Step 1 → HTTP 200 and a `progress_added` status is recorded;
  step 2 → HTTP 423 "The progress report editing window has closed."
* **Automated by:** `LpiReportUploadTest::test_a07_progress_form_save_follows_window_and_records_progress_added`

---

## B. Rejection → admin review → resubmission (progress report)

### B-01 Reviewer rejection completes to `progress_rejected`
* **Preconditions:** Progress stage ready for grading (report submitted,
  `prog_rpt_deadline` passed); Reviewer A assigned + claimed.
* **Steps:** Reviewer A submits the grading form with *publish = rejected*
  (rejection type *Report*).
* **Expected:** Consensus (single assigned reviewer) completes immediately:
  latest status = `progress_rejected` with `metadata.rejection_type=report`;
  A's `progress_report_gradings` row has `publish=rejected` +
  `rejection_reason`.
* **Automated by:** `LpiReportUploadTest::test_b01_reviewer_rejection_completes_to_progress_rejected`

### B-02 Only an admin may review the rejection
* **Preconditions:** B-01 state.
* **Steps:** As Reviewer B (not admin), `POST /workflow/review-rejection/{id}`
  with a valid body (`action=send_to_lpi`, `report_type=progress`).
* **Expected:** HTTP 403 "Only administrators can review rejections."; no
  `progress_rejection_reviewed` status recorded.
* **Automated by:** `LpiReportUploadTest::test_b02_only_admin_can_review_rejection`

### B-03 Admin "Send to LPI" records the resubmission request
* **Steps:** As the admin, `POST /workflow/review-rejection/{id}` with
  `action=send_to_lpi`, a message and `report_type=progress`.
* **Expected:** HTTP 200; latest status = `progress_rejection_reviewed` with
  `metadata.action=send_to_lpi` + `admin_message`; the LPI receives the
  *"Progress Report Resubmission Required"* notification (bell task on the
  LPI side).
* **Automated by:** `LpiReportUploadTest::test_b03_admin_send_to_lpi_records_resubmission_request`

### B-04 Resubmission unlocks the upload past the deadline (v2, v1 kept)
* **Preconditions:** B-03 state; `prog_rpt_deadline` passed; v1 on disk.
* **Steps:** As the LPI, upload a new PDF with `type=progress`.
* **Expected:** HTTP 200 with `version=2`; **two** rows now (v1 untouched +
  v2); new file `{id}_progress_v2.pdf` on disk; v1 file still exists;
  `serveFile2` with the v2 `submission_id` serves the new PDF.
* **Automated by:** `LpiReportUploadTest::test_b04_progress_v2_upload_unlocked_past_deadline_keeps_v1`

### B-05 Every reviewer set is archived & reset on v2
* **Preconditions:** Two reviewers (A rejected, B accepted) — consensus
  completed with a rejection; admin sent it back to the LPI.
* **Steps:** LPI uploads the v2 progress PDF.
* **Expected:** Both `progress_report_gradings` rows are re-opened
  (`publish=pending`, `rejection_type/rejection_reason` cleared) and each
  keeps its previous set in `previous_grading` (A's archived
  `publish=rejected`, B's archived `publish=accepted`) — the reviewers now
  grade v2 with blank forms.
* **Automated by:** `LpiReportUploadTest::test_b05_progress_v2_archives_and_resets_every_reviewer_set`

### B-06 The LPI form save re-opens after the resubmission request
* **Preconditions:** B-03 state (deadline passed, resubmission open).
* **Steps:** As the LPI, `POST /progress/save/{id}`.
* **Expected:** HTTP 200 (the deadline lock is lifted while the resubmission
  is open); latest status becomes `progress_added` — the LPI's "report
  needing resubmission" bell task disappears.
* **Automated by:** `LpiReportUploadTest::test_b06_progress_form_save_reopens_after_resubmission`

### B-07 The resubmission closes after the next grading
* **Preconditions:** B-04 state (v2 uploaded); Reviewer A's set reset.
* **Steps:**
  1. Reviewer A grades v2 and submits *accepted* (consensus completes).
  2. As the LPI, try to upload `type=progress` again.
* **Expected:** Step 1 → latest status `progress_reviewed`; step 2 → HTTP
  423 again (the resubmission window closed with the next grading).
* **Automated by:** `LpiReportUploadTest::test_b07_resubmission_closes_after_next_grading`

---

## C. Final report

### C-01 Final upload happy path
* **Preconditions:** `final_rpt_deadline` in the future; no final submission.
* **Steps:** As the LPI, upload `type=final`.
* **Expected:** HTTP 200 `version=1`; row `type=final`; file
  `{id}_final.pdf` under `final_reports/`; downloadable via `serveFile2`.
* **Automated by:** `LpiReportUploadTest::test_c01_final_upload_stores_v1`

### C-02 Final upload & form locked after the deadline
* **Preconditions:** `final_rpt_deadline` in the past.
* **Steps:**
  1. Upload `type=final`.
  2. `POST /progress/final/{id}` (the final form save).
* **Expected:** Both → HTTP 423 ("The upload window for this report has
  closed…" / "The final report editing window has closed.").
* **Automated by:** `LpiReportUploadTest::test_c02_final_upload_and_form_locked_after_deadline`

### C-03 Final rejection chain re-opens v2 and resets the final grading
* **Preconditions:** Final stage ready for grading (final submitted, deadline
  passed); Reviewer A assigned + claimed; A submits *rejected* → status
  `final_rejected`; admin reviews with `send_to_lpi` →
  `final_rejection_reviewed`.
* **Steps:** As the LPI, upload `type=final` again (deadline still passed).
* **Expected:** HTTP 200 `version=2`; v1 kept on disk; new
  `{id}_final_v2.pdf`; A's `final_report_gradings` row re-opened
  (`publish=pending`) with the rejected set archived in `previous_grading`.
* **Automated by:** `LpiReportUploadTest::test_c03_final_rejection_chain_reopens_v2_and_resets_grading`

### C-04 Final form save records `final_added` (and requires a file)
* **Preconditions:** Final window open.
* **Steps:**
  1. `POST /progress/final/{id}` with **no** readiness/final file uploaded
     anywhere on the project.
  2. Upload a final PDF, then repeat the POST with `submission_notes`.
* **Expected:** Step 1 → HTTP 422 "Please upload the readiness and/or final
  report before submitting."; step 2 → HTTP 200 and a `final_added` status.
* **Automated by:** `LpiReportUploadTest::test_c04_final_form_save_records_final_added`

---

## D. Ethical approvals

### D-01 Ethical uploads ignore the reporting deadlines
* **Preconditions:** All reporting deadlines passed.
* **Steps:** As the LPI, upload `type=ethical`.
* **Expected:** HTTP 200 (never deadline-gated); row `type=ethical`,
  `version=1`; file `{id}_ethical_1.pdf` under `ethical_approvals/`.
* **Automated by:** `LpiReportUploadTest::test_d01_ethical_upload_ignores_deadlines`

### D-02 Multiple ethical approvals append numbered files
* **Steps:** Upload two ethical PDFs in a row.
* **Expected:** **Two** rows (`_ethical_1`, `_ethical_2`); nothing replaced or
  deleted; both files on disk.
* **Automated by:** `LpiReportUploadTest::test_d02_ethical_uploads_append_numbered_files`

### D-03 An Ethical/Other rejection holds the report upload but routes to the admin
* **Preconditions:** Latest `progress_rejected` status carries
  `metadata.rejection_type=ethical`; the progress window is still open.
* **Steps:**
  1. As the LPI, upload `type=progress` (the report).
  2. As the LPI, upload `type=ethical` (the approval document).
  3. As the admin, forward the rejection (`send_to_lpi`).
* **Expected:** Step 1 → HTTP 423 (the report itself is never re-uploaded for
  an ethical rejection); step 2 → HTTP 200 (the ethical approval upload is
  never deadline- or hold-gated); step 3 → HTTP 200 (the admin **does** review
  and forward ethical rejections — the LPI is asked to re-send the ethical
  approval, not the report).
* **Automated by:** `LpiReportUploadTest::test_d03_ethical_rejection_holds_report_upload_and_routes_to_admin`

---

## E. Progress Report 2 & readiness gates

### E-01 Progress Report 2 requires the extended-progress flag
* **Preconditions:** Project **without** `extended_progress`;
  `prog_rpt2_deadline` in the future (so only the flag can refuse it).
* **Steps:** As the LPI, upload `type=progress2`.
* **Expected:** HTTP 403 "Progress Report 2 is not enabled for this project."
  (mirrors the grading-side gate — the hidden UI type cannot be posted
  around); no row.
* **Automated by:** `LpiReportUploadTest::test_e01_progress2_upload_requires_extended_progress`

### E-02 Progress Report 2 happy path records `progress2_added`
* **Preconditions:** `extended_progress` on; `prog_rpt2_deadline` in the
  future.
* **Steps:** As the LPI, upload `type=progress2`.
* **Expected:** HTTP 200 `version=1`; row `type=progress2`; latest status
  `progress2_added` (the extended report tracks its own lifecycle).
* **Automated by:** `LpiReportUploadTest::test_e02_progress2_upload_records_progress2_added`

### E-03 Readiness follows the FINAL window
* **Preconditions:** `final_rpt_deadline` in the future (readiness is a
  final-phase deliverable — `effectiveDeadline('readiness')` returns the final
  deadline, and it shares the final stage's rejection-hold / resubmission state).
* **Steps:**
  1. As the LPI, upload `type=readiness`.
  2. Set `final_rpt_deadline` in the past and upload again.
* **Expected:** Step 1 → HTTP 200, single row `{id}_readiness.pdf`; step 2 →
  HTTP 423 (no resubmission on record).
* **Automated by:** `LpiReportUploadTest::test_e03_readiness_follows_final_window`

---

## F. Project data freeze

### F-01 Outcomes are frozen after the final deadline
* **Preconditions:** `final_rpt_deadline` in the past.
* **Steps:**
  1. As the LPI, `POST /progress/save-outcomes/{id}` with a valid outcomes
     payload.
  2. Set `final_rpt_deadline` in the future and repeat.
* **Expected:** Step 1 → HTTP 423 "The editing window for project data has
  closed…"; step 2 → HTTP 200 (data editable again). A final resubmission
  never re-opens project data (only the final-report upload).
* **Automated by:** `LpiReportUploadTest::test_f01_project_data_frozen_after_final_deadline`

---

## G. Upload & outcomes integrity (bug-fix regressions)

### G-01 A readiness-only final submit does not record `final_added`
* **Steps:** Upload only `type=readiness`, then `POST /progress/final/{id}`.
* **Expected:** HTTP 200 but **no** `final_added` status — a readiness-only
  submit must not claim a final report exists (the reviewer is blocked until a
  final file is present).
* **Automated by:** `LpiReportUploadTest::test_g01_readiness_only_final_submit_does_not_record_final_added`

### G-02 The upload's `download_url` pins the exact version
* **Preconditions:** A rejected progress report (so the next upload is a v2).
* **Steps:** Upload the v2, then fetch the returned `submission.download_url`.
* **Expected:** The URL contains `submission_id=` and serving it returns the
  **v2** content — not the deterministic v1 filename (which still exists on
  disk for comparison).
* **Automated by:** `LpiReportUploadTest::test_g02_upload_download_url_targets_the_uploaded_version`

### G-03 `saveOutcomes` upserts instead of wiping
* **Preconditions:** A verified `journal_q1` row and a contribution
  (`ip_disclosure`) row already exist (e.g. added record-by-record).
* **Steps:** `POST /progress/save-outcomes` with a payload containing a
  different scholarly detail **and** a contribution entry with no `submitted`
  field (the old foot-gun shape).
* **Expected:** The existing verified row survives with its verification,
  score and DOI link intact; the contribution row survives; the new scholarly
  entry is added. No delete-then-reinsert occurs.
* **Automated by:** `LpiReportUploadTest::test_g03_save_outcomes_preserves_verification_and_existing_rows`

### G-04 A text-only form save does not reset grading
* **Preconditions:** A reviewer has submitted an accepted progress set.
* **Steps:** Re-open the LPI's progress window and `POST /progress/save/{id}`
  with **no** file.
* **Expected:** HTTP 200 and the reviewer's set is untouched — only a new
  uploaded file may archive & reset the grading.
* **Automated by:** `LpiReportUploadTest::test_g04_text_only_progress_save_does_not_reset_grading`

---

## H. Full LPI submission lifecycle across both periods

### H-01 The LPI works inside both periods, then both close
* **Preconditions:** `prog_rpt_deadline` in the future (progress period) and
  `final_rpt_deadline` in the future (final period); no submissions yet.
* **Steps (inside both periods):**
  1. Upload the progress report, then re-upload to replace it.
  2. Upload the readiness report, the ethical approval and the final report;
     re-upload the final to replace it.
  3. Create an outcome, add a student, then re-save the outcomes.
* **Steps (both periods close):**
  4. Re-upload the progress report; re-upload the final and the readiness.
  5. Save an outcome; save the outcomes in bulk; save a student.
* **Expected:**
  * Inside the periods **everything succeeds**: progress/replacement keeps a
    single row with the new content; readiness, ethical and final upload; the
    final replacement keeps a single row; outcomes and students are created;
    re-saving outcomes does not wipe the row.
  * After the periods close: progress → **423** (progress period governs it),
    final and readiness → **423** (final period governs them), outcomes /
    students / bulk outcomes → **423** (data frozen), and the refused uploads
    leave the stored files untouched.
  * Ethical approval is exempt from the deadline lock (asserted separately in
    D-01) because the ethical-rejection re-send always lands after the final
    period.
* **Automated by:** `LpiReportUploadTest::test_h01_lpi_full_submission_lifecycle_within_both_periods`

---

## Related suites

The grading side of the same chain (consensus, claims, frozen stages, bell
tasks) is covered by:

* `ConsensusGradingTest` / `MultiReviewerGradingTest` — consensus & claims
* `GradingWorkflowScenariosTest` — assignment, legacy routes, admin queue
* `NotificationBellTest` — admin rejection-review + LPI resubmission tasks
* `docs/TEST-SCENARIOS-GRADING.md` — the grading-side scenario catalogue
