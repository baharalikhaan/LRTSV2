# RTS (Research Tracking System) — Technical Documentation

**Version:** 2.x
**Platform:** Laravel (PHP)
**Application:** Research Project Tracking, Grading & Reporting System

---

## Table of Contents

1. [Overview](#1-overview)
2. [Technology Stack](#2-technology-stack)
3. [Overall Architecture](#3-overall-architecture)
4. [Modules & Components](#4-modules--components)
5. [Authentication & Authorization](#5-authentication--authorization)
6. [API Calls & External Integrations](#6-api-calls--external-integrations)
7. [Storage & File Management](#7-storage--file-management)
8. [Database Schema](#8-database-schema)
9. [Security Aspects](#9-security-aspects)
10. [Project Workflow / Business Logic](#10-project-workflow--business-logic)
11. [Grading Engine](#11-grading-engine)
12. [Frontend Architecture](#12-frontend-architecture)
13. [Configuration](#13-configuration)
14. [Development & Deployment](#14-development--deployment)

---

## 1. Overview

The **Research Tracking System (RTS)** is a web-based platform that manages the full
lifecycle of funded research projects, from import/registration through progress
reporting, review, final grading, and reporting. It serves three primary roles:

- **Admin** — manages programs, grants, users, reviewer assignment, and aggregation.
- **LPI** (Lead Principal Investigator) — registers projects, uploads progress/final
  reports, and logs outcomes (publications, students, IP).
- **Reviewer** — accepts/rejects proposals, grades progress and final reports.

The system integrates with external university services (Student Information System,
CrossRef, Elsevier/Scopus, Budget API) to automatically verify research outputs and
student records.

---

## 2. Technology Stack

### Backend
| Component | Technology |
|-----------|------------|
| Framework | **Laravel 8.75** (PHP `^7.3 \| ^8.0`) |
| Language | PHP 8 |
| Database | **MySQL** (`lrts_mcp`) via Eloquent ORM |
| Auth | Laravel native session auth + **Laravel Sanctum** (API tokens) |
| Templating | Blade |

### Frontend
| Component | Technology |
|-----------|------------|
| CSS framework | **Bootstrap 5.1** + **AdminLTE 3** (custom "QU Fluent" theme) |
| Build tool | **Laravel Mix 6** (Webpack 5, SASS) |
| Icons | Font Awesome |
| Scripts | jQuery, vanilla JS, Axios |
| Charts | Custom CSS gauges/pills (no heavy chart lib) |

### Key PHP Packages (`composer.json`)
| Package | Purpose |
|---------|---------|
| `maatwebsite/excel` + `phpoffice/phpspreadsheet` | Excel import/export of project data |
| `phpoffice/phpword` | Word document templates for reports |
| `barryvdh/laravel-dompdf` | PDF generation (report cards) |
| `guzzlehttp/guzzle` | Outbound HTTP calls (CrossRef, SIS, Elsevier, Budget) |
| `jeroennoten/laravel-adminlte` | Admin dashboard UI components |
| `yajra/laravel-datatables-oracle` | Server-side data tables |
| `laravel/sanctum` | API token authentication |
| `spatie/simple-excel` | Lightweight Excel reading |
| `aacotroneo/laravel-saml2` | (Installed) SAML SSO support |
| `webklex/laravel-imap`, `sendgrid/sendgrid`, `phpmailer/phpmailer` | Email sending |
| `phpseclib/phpseclib` | Secure communications/crypto |

### Node Dependencies (`package.json`)
Laravel Mix, Webpack, Bootstrap 5, Axios, Popper, Lodash, SASS.

---

## 3. Overall Architecture

The application follows Laravel's standard **MVC** pattern with a **monolithic**
deployment model (single web application serving Blade views and AJAX endpoints).

```
Browser (Blade + jQuery/Axios)
        │
        ▼
   public/index.php ──► Laravel HTTP Kernel
        │
        ├── Global Middleware (CORS, TrustProxies, TrimStrings…)
        ├── Web Middleware Group (CSRF, Session, EncryptCookies…)
        │
        ▼
      Routes (routes/web.php, routes/api.php)
        │
        ▼
     Controllers (app/Http/Controllers)
        │
        ├── Services (app/Services) — GradingCalculator, CycleProgressReportService
        ├── Models (app/Models) — Eloquent ORM
        │
        ▼
      MySQL Database
        │
        ▼
   Storage (uploads/, PDF files, Excel)
        │
        ▼
   External APIs (SIS, CrossRef, Elsevier, Budget)
```

### Request Flow
1. User authenticates via session (CSRF-protected forms).
2. Route matched in `routes/web.php`.
3. Controller loads data through Eloquent models.
4. Blade view rendered with inline styles + shared layout (`layouts/app.blade.php`).
5. Many actions are AJAX (jQuery) hitting POST/GET endpoints returning JSON.

### Key Architectural Traits
- **Role-based routing** to separate dashboards (`dashboard.admin`, `dashboard.lpi`,
  `dashboard.reviewer`) via `HomeController@index`.
- **Status-history driven workflow** — project progression is tracked in the
  `status_histories` table, not a mutable status column (though a fallback column exists).
- **Deterministic file naming** — files are stored under a hierarchical path derived
  from cycle year + grant code, with versioned filenames for resubmissions.
- **Server-side enforcement** of business rules (e.g., upload deadlines/locks are
  checked in the controller, not only the UI).

---

## 4. Modules & Components

The system is organized into the following functional modules. Each maps to one or
more controllers under `app/Http/Controllers/`.

### 4.1 Dashboard & Role Switching
- **Controller:** `HomeController`
- **Routes:** `GET /home`, `POST /switch-role`, `GET /notifications`
- **Views:** `resources/views/dashboard/{admin,lpi,reviewer}.blade.php`
- **Behavior:** Renders a role-specific dashboard (Admin aggregates all projects;
  LPI sees own projects + available slots; Reviewer sees assigned work + performance
  ratings). Users with combined roles (e.g., `Admin+LPI+Reviewer`) can switch via
  session `active_role`.

### 4.2 Grants (Grant Types)
- **Controller:** `GrantController`
- **Routes:** `grant-types.*`
- **Models:** `Grant`
- **Purpose:** CRUD for grant types (student vs regular), grant codes, funding agency.

### 4.3 Programs (Research Calls)
- **Controller:** `ProgramController`
- **Routes:** `programs.*`
- **Models:** `Program`, `CycleConfig`
- **Purpose:** Manage research-call programs tied to a grant + cycle, with report
  deadlines. Also handles **Excel import** of project proposals and single-proposal upload.

### 4.4 Projects (Core)
- **Controller:** `ProjectController`, `RegisterWizardController`
- **Routes:** `projects.*`, `wizard.*`
- **Models:** `Project`, `Commitment`, `Outcome`, `ProjectPublication`,
  `ProjectStudent`, `ProjectResearcher`, `ProjectContribution`,
  `ProjectStudentDetail`, `ProjectIntellectualProperty`, `ProjectSubmission`
- **Purpose:** Core project lifecycle — listing, registration, progress/final report
  management, report card generation, reviewer assignment, detail view.

### 4.5 Progress Reports
- **Controller:** `ProgressController`
- **Routes:** `progress.*`
- **Models:** `ProjectSubmission`, `Outcome`, `ProjectStudent`, `ProjectResearcher`
- **Purpose:** LPI uploads progress/progress2/readiness/final reports (PDF), adds
  outcomes (articles, IP, students, researchers, contributions), verifies student
  details via SIS, and verifies publication DOIs via CrossRef.

### 4.6 Grading
- **Controller:** `GradingController`, `WorkflowController`, `ReviewerGradingController`
- **Routes:** `grading.*`, `projects.grading`, `workflow.*`, `reviewer-grading.*`
- **Models:** `ProgressReportGrading`, `FinalReportGrading`, `Score`, `Rating`
- **Purpose:** Reviewer grades progress & final reports; auto-calculation of scores
  via `GradingCalculator`; admin reviews of rejections; reviewer performance ratings.

### 4.7 Users & Profiles
- **Controller:** `UserController`, `ProfileController`
- **Routes:** `users.*`, `profile.*`
- **Model:** `User`, `Nationality`
- **Purpose:** User CRUD, role management, profile editing.

### 4.8 Announcements
- **Controller:** `AnnouncementController`
- **Routes:** `announcements.*`
- **Model:** `Announcement`
- **Purpose:** Role-scoped announcements shown on dashboards.

### 4.9 Email & Notifications
- **Controller:** `EmailController`, `EmailTemplateController`
- **Routes:** `admin.*`, `email-templates.*`
- **Models:** `EmailSendLog`, `EmailTemplate`
- **Purpose:** Compose/send/retry emails with a send log; reusable email templates.

### 4.10 Reports
- **Controller:** `ReportController`
- **Routes:** `reports.*`
- **Models:** `Project`, `Program`, `Grant`, `Pillar`, `CycleConfig`, `ProjectBudget`
- **Purpose:** Program status, grant summary, project status, pillar summary,
  cycle progress (with reminder emails), budget utilization reports (CSV/HTML).

### 4.11 Configuration Modules
- **Controllers:** `ScoreController`, `PillarController`, `CollegeController`,
  `CycleConfigController`, `GaugeSettingsController`, `TagController`, `TeamController`
- **Purpose:** Maintain lookup/configuration data (scores, pillars, colleges, cycles,
  gauge settings, teams).

### 4.12 File Explorer
- **Controller:** `FileExplorerController`
- **Routes:** `file-explorer.*`
- **Purpose:** Browse and **ZIP-download** a project's or program's report files.

### 4.13 Budget Utilization
- **Controller:** `BudgetUtilizationController`
- **Routes:** `budget-utilization.*`
- **Model:** `ProjectBudget`
- **Purpose:** Sync project budget data from the external Budget API and send reminders.

### 4.14 Admin Upload
- **Controller:** `AdminUploadController`
- **Routes:** `admin-upload.*`
- **Purpose:** Batch upload of report files by admin.

### 4.15 Help / About / Team
- **Controller:** `AboutController`
- **Routes:** `about.*`
- **Purpose:** Static informational pages.

---

## 5. Authentication & Authorization

### 5.1 Authentication
- **Mechanism:** Laravel's built-in session authentication via `Auth::routes()`.
- **Controllers:** `app/Http/Controllers/Auth/*` (Login, Register, Forgot/Reset
  Password, Verification, Confirm Password).
- **Login Controller:** `LoginController` uses `AuthenticatesUsers` trait; redirects to
  `/home` on success.
- **API tokens:** Laravel Sanctum installed; `routes/api.php` exposes
  `GET /api/user` under `auth:sanctum`.
- **Guest guard:** `guest` middleware (`RedirectIfAuthenticated`) prevents logged-in
  users from viewing login pages.

### 5.2 Roles & Permissions
Roles are stored as a **single string** in the `users.type` column, using a `+`
separator for combined roles:

| `type` value | Roles granted |
|--------------|----------------|
| `Admin` | Admin |
| `LPI` | LPI |
| `Reviewer` | Reviewer |
| `LPI+Reviewer` | LPI + Reviewer |
| `Admin+LPI+Reviewer` | Admin + LPI + Reviewer |

**Role helper methods on `User`:**
- `isAdmin()` — type is `Admin` or `Admin+LPI+Reviewer`
- `isLPI()` — LPI, LPI+Reviewer, Admin+LPI+Reviewer
- `isReviewer()` — Reviewer, LPI+Reviewer, Admin+LPI+Reviewer
- `subRoles()` — splits the type string into an array
- `activeRole()` — returns the current session role (`session('active_role')`) if
  valid, otherwise the first sub-role

**Authorization model:**
- There is **no dedicated roles/permissions package** (no `spatie/laravel-permission`).
- Authorization is implemented via **inline role checks** in controllers and views,
  e.g. `$project->availableActions(auth()->user())`, `$this->authorizeLpi($project)`,
  `@if(!$user->isReviewer())`, and `$user->activeRole()`.
- The `Controller` base class / `ProgressController` has private helpers like
  `authorizeLpi()` and `enforceDataOpen()` to restrict LPI actions to their own
  projects and enforce data-open windows.

### 5.3 Role Switching
- `POST /switch-role` stores the chosen role in the session (`active_role`).
- `User::canSwitchRole()` returns true when a user has multiple sub-roles.

---

## 6. API Calls & External Integrations

Outbound HTTP calls are made with **GuzzleHTTP** from controllers/models. All external
credentials are defined in `config/services.php` (reading from `.env`).

### 6.1 Student Information System (SIS)
- **Purpose:** Verify student records added to a project.
- **Model:** `ProjectStudentDetail::fetchFromApi($studentId)`
- **Endpoint:** `STUDENT_API_URL`
- **Auth:** custom headers `sec_key` and `st_id`.
- **Test mode:** `STUDENT_API_USE_TEST_RESPONSE=true` returns a canned response
  (`getTestResponse()`) to avoid hitting production during development. Falls back to
  the test response on any API error.

### 6.2 CrossRef API (DOI verification)
- **Purpose:** Auto-fetch publication metadata (title, journal, authors, year) from a
  DOI, populating `project_publications`.
- **Controller:** `ProgressController::fetchPublicationFromApi()`
- **Endpoint:** `CROSSREF_API_URL` (`https://api.crossref.org/works/{doi}`)
- **Validation:** DOI must match `/^10\.\d{4,}\/.+/`.
- **SSL:** `CROSSREF_API_VERIFY_SSL=false` (disabled in this environment).

### 6.3 Elsevier / Scopus API (backup DOI verification)
- **Purpose:** Backup source for verifying publication DOIs.
- **Configured** in `config/services.php` with `key` and `inst_token`.

### 6.4 Budget API
- **Purpose:** Fetch project budget utilization data.
- **Controller:** `BudgetUtilizationController::sync()`
- **Endpoint:** `BUDGET_API_URL`
- **Test mode:** `BUDGET_API_MOCK=true`.

### 6.5 Test Endpoint
- `GET /test-crossref-api/{doi}` — a manual test route that hits CrossRef and returns
  the JSON result.

> **Note:** Many external calls set `'verify' => false` on the Guzzle client,
> disabling SSL certificate verification. This is a **security concern** for
> production (see Section 9).

---

## 7. Storage & File Management

### 7.1 Filesystem Configuration
- **Default disk:** `local` (root = `storage/app`) via `FILESYSTEM_DRIVER=local`.
- Also configured: `public` disk (`storage/app/public`), and `s3` (unused/empty).

### 7.2 Directory Structure
```
storage/app/
├── uploads/                     ← Project report files
│   └── {cycle_year}/{grant_code}/
│       ├── proposals/
│       ├── progress_reports/
│       ├── readiness_reports/
│       └── final_reports/
├── downloads/                   ← Report templates (progress/final/readiness .docx)
├── temp/                        ← Temporary uploads (Excel imports)
└── app/
    └── email-attachments/       ← Email attachments
```

### 7.3 Deterministic Path & Naming
- **Directory:** `Project::getStorageDir($typeFolder)` →
  `uploads/{cycle_year}/{grant_code}/{type_folder}`.
  - `cycle_year` from `program.cycle.year`
  - `grant_code` from `program.grant.grant_code`
- **Filename:** `Project::getStorageFilename($type, $version)` →
  `{old_project_id}_{type}[_v{version}].pdf`
  - `old_project_id` is sanitized of `/` characters.
  - `type` ∈ `proposal | progress | progress2 | readiness | final`.

### 7.4 Versioning / Resubmission Logic (`ProgressController::uploadFile`)
Files are stored as single-file-per-type (replace on re-upload), with special
**v2 resubmission** behavior:
- **Progress / Progress2 / Final** on admin resubmission after rejection: keep v1,
  write/overwrite **v2** (`_v2.pdf`).
- **Readiness / Final / Progress2** normal: replace existing single file (`v1`).
- Validation: `required|file|mimes:pdf|max:10240` (PDF only, max 10 MB).
- Server-side lock: LPI cannot upload once the report window is closed
  (`isTypeLocked()` → HTTP 423).

### 7.5 File Serving (`GET /serveFile2`)
- Serves stored PDFs in an iframe.
- If `submission_id` is provided, serves that exact submission version.
- Otherwise resolves the deterministic path by `type` + project `id`.
- Includes a friendly "File does not exist" placeholder instead of a 404 page.
- A generic `file` query param is guarded against path traversal
  (`preg_match('/(\.\.|\/|\\\\)/')` → 403).

### 7.6 ZIP Downloads (`FileExplorerController`)
- `downloadProject($id)` — Zips all of a project's report files into
  `project_{old_id}.zip` and streams it (deleted after send).
- `downloadProgram($programId)` — Zips all files for a research call.

### 7.7 Report Templates
- `GET /download-template/{type}` serves `.docx` templates
  (`progress`, `final`, `readiness`) from `storage/downloads/`.

---

## 8. Database Schema

**DB:** MySQL (`lrts_mcp`). Migrations under `database/migrations/`.

### Core Tables
| Table | Purpose |
|-------|---------|
| `users` | Users (role in `type` column, college/pillars as strings) |
| `nationalities` | Nationality lookup |
| `grants` | Grant types (category: student/regular) |
| `cycle_configs` | Research cycles (year, title) |
| `pillars` | Research pillars |
| `colleges` | College lookup |
| `scores` | Score/weight configuration for outcome types |
| `programs` | Research calls (grant + cycle, deadlines) |
| `projects` | Core project records (LPI, program, budget, student-grant fields) |
| `commitments` | Committed research output targets (q1-q4, IP, students) |
| `projects_reviewers` | Pivot: project ↔ reviewer (role, proposalstatus) |
| `project_college` / `project_pillar` | Pivot tables |
| `project_outcomes` | Achieved outcomes (type, identifier, verification flags) |
| `project_publications` | Publication metadata (DOI, journal, title, year) |
| `project_students` | Student entries on a project |
| `project_students_details` | Student details fetched from SIS API |
| `project_researchers` | Hired researchers |
| `project_contributions` | IP/patent contributions |
| `project_submissions` | Report file submissions (type, version, file_path) |
| `status_histories` | Workflow status audit trail (project → status → user) |
| `progress_report_grading` | Progress report grades/ratings per reviewer |
| `final_report_grading` | Final report grades (A–D, total, publish) |
| `reviewer_grading` / `reviewer_ratings` | Reviewer performance ratings |
| `reviewer_rejections` | Rejection records |
| `announcement` | Announcements |
| `email_send_log` / `email_templates` | Email tracking & templates |
| `gauge_settings`, `team`, `project_budgets` | Config/team/budget |
| `report_reminders_sent` | Cycle progress reminder tracking |
| `personal_access_tokens` | Sanctum tokens |

### Key Enum Values
- `projects.status` legacy + `status_histories.status` (see Section 10).
- `project_outcomes.verifcation_by_system` /
  `verifcation_by_reviewer` ∈ `verified | not-verified | pending`.
- `project_submissions.type` ∈ `progress | progress2 | final | readiness`.
- `progress_report_grading.publish` / `final_report_grading.publish` ∈
  `accepted | rejected | reserved | pending`.

> **Note:** There is a minor typo in the DB — `verifcation_*` (misspelled
> "verification") is used consistently across schema and code.

---

## 9. Security Aspects

### 9.1 Implemented Controls
- **CSRF Protection:** All web forms are protected by Laravel's `VerifyCsrfToken`
  middleware; `csrf_field()` / `@csrf` used in Blade.
- **Session-based auth** with encrypted cookies (`EncryptCookies`).
- **Input validation:** Controllers validate with Laravel validation rules
  (e.g., `mimes:pdf`, `max:10240`, `in:...`, `exists:...`).
- **Mass-assignment protection:** Eloquent `$fillable` whitelists.
- **Password hashing:** Laravel default bcrypt; passwords hidden in serialization.
- **Path traversal protection:** `serveFile2` rejects `..`, `/`, `\` in the `file`
  query param.
- **Role-based UI gating:** Sensitive data (outcome/student/IP details) is hidden from
  reviewers in views via `@if(!$isViewer)`.
- **Server-side enforcement:** Upload deadlines/locks and LPI ownership are checked in
  the controller (not only the UI).
- **Route protection:** Many route groups apply `auth` middleware; the base
  `Controller` applies `auth` in constructors (e.g., `ProjectController`, `HomeController`).

### 9.2 Risks & Recommendations
1. **SSL verification disabled** — Guzzle clients use `'verify' => false` for SIS,
   CrossRef, Elsevier, and Budget APIs. **Enable SSL verification in production.**
2. **No dedicated authorization package** — access control is inline/role-string based.
   Recommend centralizing with policies/middleware to reduce risk of missed checks.
3. **`APP_DEBUG=true`** in `.env` — must be `false` in production to avoid exposing
   stack traces and config.
4. **CORS wide-open** (`allowed_origins = ['*']`, `supports_credentials = false`).
   Restrict origins for production.
5. **Sanctum `expiration = null`** — personal access tokens never expire. Set an
   expiration policy if API tokens are used.
6. **Secrets in `.env`** — several API keys are committed in `.env` (which is
   git-ignored, but be careful not to commit it). Use a secret manager / environment
   variables in production.
7. **`/test-crossref-api/{doi}` route** is unauthenticated and publicly reachable —
   restrict it or remove it in production.
8. **File upload validation** is good (PDF-only, size-limited), but consider scanning
   uploaded files for malware and storing them outside the web root (already done —
   `storage/app/uploads` is not publicly served).
9. **Rate limiting** — `throttle:api` is applied to the API group only; web routes
   (login, etc.) rely on Laravel defaults. Consider throttling login.

---

## 10. Project Workflow / Business Logic

Project progression is tracked via the `status_histories` table. Each status is
recorded by `Project::recordStatus($status, $metadata, $userId)`.

### 10.1 Workflow Stages (Single-Reviewer)
| # | Status (code) | Action | Next Status | Actor |
|---|---------------|--------|-------------|-------|
| 0 | `imported` | Import from Excel | `registered` | System/Admin |
| 1 | `registered` | Assign Reviewer | `Assigned` | Admin |
| 2 | `Assigned` | Accept/Reject Proposal | `Claimed` | Reviewer |
| 3 | `progress_added` | Review Progress | `progress_reviewed` | Reviewer |
| — | `progress_rejected` | Admin Reviews | (see admin) | Admin |
| 4 | `progress_reviewed` | Add Final Report | `final_added` | LPI |
| 5 | `final_added` | Grade Final | `Graded` | Reviewer |
| — | `final_rejected` | Admin Reviews | (see admin) | Admin |
| 6 | `Graded` | Report Card (view) | — | All |

### 10.2 Additional Statuses
- **Extended/second progress report:** `progress2_added`, `progress2_reviewed`,
  `progress2_rejected`, `progress2_rejection_reviewed`.
- **Admin rejection review:** `progress_rejection_reviewed`, `final_rejection_reviewed`.
- **Proposal rejection:** `proposal_rejected`.
- **Reviewer removal:** `reviewer_unassigned`.

### 10.3 Derived Status Helpers (`Project`)
- `current_status` accessor — latest status history (falls back to `status` column).
- `latestStatus()` — `hasOne` relation via `latestOfMany()`.
- `hasStatus($status)` — whether the project ever reached a status.
- `hasUnreviewedRejection($type)` — round-aware check for whether the most recent
  rejection is still unreviewed (so a second/third rejection after resubmission
  reappears for the admin).
- `getLifecycleAttribute()` — ordered lifecycle stages with done/dates/deadlines.
- `getLifecycleStageAttribute()` — 0-based index of the current stage.
- `availableActions($user)` — computes the action menu per role + status.

### 10.4 Deadlines
Programs define report deadlines (`prog_rpt_deadline`, `final_rpt_deadline`, plus
extended variants). The lifecycle and upload lock logic compare against `now()`
to determine if a stage is "current" or closed.

---

## 11. Grading Engine

### 11.1 Score Configuration
The `scores` table maps outcome types to point values. `Score::getMap()` /
`Score::getByCategory()` return these. Defaults (from `GradingCalculator`):
- Journal Q1–Q4: 8 / 6 / 4 / 3
- Conference: 2, Book: 8, Ed. Book: 6, Chapter: 4
- IP disclosure: 4, Provisional patent: 7, Open source SW: 8, Startup: 10
- Masters: 2, UG: 1, PhD: 3, Cross-college: 2

### 11.2 `GradingCalculator` (app/Services/GradingCalculator.php)
- `calculateExpectedScore($projectId)` — sums `commitment × score`.
- `calculateVerifiedScore($projectId)` — sums scores of outcomes flagged
  `verifcation_by_reviewer = 'verified'`, plus verified students.
- `calculateGradeA($projectId)` — `(verified/expected) × 5`, rounded to 2 dp
  (outcome-verification grade on a 0–5 scale).
- `getOutcomeTypes()`, `getIpTypes()`, `getStudentTypes()` — return configured types.

### 11.3 Grading Records
- **Progress report grading** (`progress_report_grading`): ratings for
  achievements, publications, students, budget (each with a word label via the
  `ratings` lookup table and `/5` numeric), ethical approval, analysis,
  recommendation, `publish` (accepted/rejected/pending), `isAccepted`, `report_type`
  (`progress` / `progress2`).
- **Final report grading** (`final_report_grading`): four section grades
  (A: Achievements, B: Publications & IP, C: Student involvement, D: Impact),
  comments, `total`, `publish`, `isAccepted`.
- **Reviewer grading** (`GradingController`): saves progress & final grades,
  updates verification flags, and submits/publishes grades.
- **Report Card** (`ProjectController@reportCard`) — printable PDF-style page
  summarizing per-reviewer progress/final remarks and overall status.

### 11.4 Reviewer Performance Ratings
`ReviewerRating` (table `reviewer_ratings`) records admin evaluations of reviewers
per program/cycle on: `conflict`, `responsiveness`, `comprehensiveness`,
`no_reviewers`, `behaviour` (1–5). Shown on the reviewer dashboard with an
overall average.

---

## 12. Frontend Architecture

- **Layout:** `resources/views/layouts/app.blade.php` (~1000 lines) — global shell
  including sidebar, top bar, CSRF meta, jQuery, Bootstrap, and shared JS handlers
  (e.g., the delegated `.open-grade-modal` AJAX handler, project menu toggling).
- **Styling:** Bootstrap 5 + custom CSS with design tokens (CSS variables):
  `--brand-500`, `--ink-*`, `--sand-50`, `--gold-500`, `--success`, `--danger`, etc.
- **Panels:** consistent `.panel`, `.panel-head`, `.panel-body` component classes.
- **Modals:** Workflow modals loaded via AJAX into a shared modal container
  (`WorkflowController@modal`).
- **AJAX patterns:** jQuery `$.post`/`$.ajax` with CSRF token; JSON responses
  `{success, message, error, ...}`.
- **Role-aware rendering:** Views conditionally render sections per role (e.g.,
  reviewer sees limited project detail; admin/LPI see outcomes, reports, grading).

---

## 13. Configuration

Key config files:
- `config/database.php` — MySQL connection (default `mysql`).
- `config/filesystems.php` — local disk (default).
- `config/services.php` — external API credentials (SIS, CrossRef, Elsevier, Budget).
- `config/cors.php` — CORS settings (`api/*`).
- `config/sanctum.php` — API token behavior.
- `.env` — environment-specific settings (DB, mail, API keys, flags).

Relevant environment flags:
- `APP_DEBUG`, `APP_ENV`, `APP_KEY`, `APP_URL`
- `DB_*` — database connection
- `FILESYSTEM_DRIVER=local`
- `MAIL_*` — SMTP (defaults to `mailhog` in dev)
- `STUDENT_API_*`, `CROSSREF_API_*`, `ELSEVIER_API_*`, `BUDGET_API_*` — integrations
- `BUDGET_API_MOCK`, `STUDENT_API_USE_TEST_RESPONSE` — dev mock flags

---

## 14. Development & Deployment

### 14.1 Local Setup
```bash
composer install
npm install
cp .env.example .env        # configure DB, mail, API keys
php artisan key:generate
php artisan migrate
npm run dev                 # compile assets (Laravel Mix)
php artisan serve
```

### 14.2 Common Commands
```bash
php artisan migrate            # run database migrations
php artisan view:cache         # compile Blade views (verify template syntax)
php artisan config:cache
php artisan route:list         # inspect routes
npm run watch                  # watch frontend assets
```

### 14.3 Live Deployment (XAMPP + Virtual Host)

This section provides the complete steps to deploy RTS on a **live server** running
**XAMPP on Windows**. Follow the steps in order.

> **Server profile used in this guide:** XAMPP 7.x (with PHP 7.x/8.x), Apache on
> Windows, MySQL. The application document root is `C:/xampp74/htdocs/LRTS`.

#### 14.3.1 Prerequisites
- **XAMPP** (V.7.0.0 or newer) installed on the server.
- MySQL (included with XAMPP) running and a database created for the application.
- The RTS application source copied into `htdocs`, or cloned from the Git repository:
  ```bash
  cd C:/xampp74/htdocs
  git clone <repo-url> LRTS
  ```

#### 14.3.2 Setup the Virtual Host

1. **Map the sub-domain to localhost (test only).**
   Edit the hosts file:
   ```
   C:\Windows\System32\drivers\etc\hosts
   ```
   Add the line:
   ```
   127.0.0.1   lrts.qu.edu.qa
   ```
   > This line is only for **local testing** so the virtual host works on the server
   > machine before DNS is live. It is **not** required (and should be removed on the
   > live server) once DNS points the sub-domain to the server's public IP.

2. **Enable the virtual host include in Apache.**
   In `C:\xampp74\apache\conf\httpd.conf`, make sure the following line is **not
   commented out** (remove the leading `#` if present):
   ```apache
   Include conf/extra/httpd-vhosts.conf
   ```

3. **Configure the virtual host.**
   Edit `C:\xampp74\apache\conf\extra\httpd-vhosts.conf`. The correct configuration
   has **two** virtual host blocks — a catch-all default (required so Apache does not
   use the wrong doc root) and the RTS host. Add the following:
   ```apache
   # ── Catch-all default (required to avoid serving wrong doc root) ──
   <VirtualHost *:80>
       ServerAdmin webmaster@qu.edu.qa
       DocumentRoot "C:/xampp74/htdocs"
       ServerName localhost
   </VirtualHost>

   # ── RTS live host (HTTP) ──
   <VirtualHost *:80>
       ServerAdmin lrts@qu.edu.qa
       DocumentRoot "C:/xampp74/htdocs/LRTS/public"
       ServerName lrts.qu.edu.qa
       ErrorLog "logs/lrts.qu.edu.qa-error.log"
       CustomLog "logs/lrts.qu.edu.qa-access.log" common

       <Directory "C:/xampp74/htdocs/LRTS/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   > **Important corrections vs. the original draft:**
   > - `DocumentRoot` must point to the Laravel **`public`** folder
   >   (`C:/xampp74/htdocs/LRTS/public`), **not** the project root. Laravel's
   >   `public/index.php` is the single entry point.
   > - `ServerName` should be the real sub-domain `lrts.qu.edu.qa` (not a placeholder
   >   like `dummy-host2.example.com`).
   > - The `<Directory>` block with `AllowOverride All` is required so the `.htaccess`
   >   file (which rewrites all requests to `index.php`) is honoured.

4. **Restart Apache** so the virtual host takes effect:
   - Via the XAMPP Control Panel, or
   - From the command line:
     ```bash
     C:\xampp74\apache\bin\httpd.exe -k restart
     ```

#### 14.3.3 DNS Settings

> **🛠 Performed by ITS (Information Technology Services, Qatar University).**
> The application team provides the server's IP address; ITS performs the DNS
> configuration and makes the sub-domain live.

5. **Register the sub-domain.** Contact **ITS (Information Technology Services)**
   and request that a sub-domain be created and pointed to the server's IP address:
   ```
   lrts.qu.edu.qa  →  <server public IP>
   ```
   **ITS performs the following:**
   - Creates the DNS `A` record (and, for HTTPS, the `CNAME`/`A` record) for
     `lrts.qu.edu.qa`.
   - Assigns the sub-domain to the server's public IP.
   - (Optional) Issues the SSL certificate for HTTPS.
   This step is what makes the system reachable over the network from other machines.
   > The application team should **coordinate with ITS** and provide the server IP;
   > no direct server access to DNS is available to the application team.

#### 14.3.4 Application Configuration (Laravel)
6. **Configure the `.env` file** for production:
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://lrts.qu.edu.qa        # use https once SSL is installed

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=lrts_mcp
   DB_USERNAME=root
   DB_PASSWORD=<your_db_password>
   ```
   > Set `APP_DEBUG=false` so errors and configuration are not exposed publicly.

7. **Run migrations** to create/update the database schema:
   ```bash
   php artisan migrate --force
   ```
8. **Optimize the application for production:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```
9. **Set filesystem permissions** (Windows XAMPP generally handles this, but ensure
   the web server can write to):
   - `storage/framework/` (sessions, cache, views)
   - `storage/logs/`
   - `storage/app/uploads/` (uploaded PDFs)
10. **Verify file permissions for storage links** — if the app uses the `public`
    disk, create the storage symlink:
    ```bash
    php artisan storage:link
    ```

#### 14.3.5 SSL Certificate (HTTPS)

To serve the system over **HTTPS**, obtain and install an SSL certificate.

> **Who does what:** The **application team** generates the CSR and installs the
> certificate on the server. **ITS** signs/issues the certificate and returns the
> `.crt`/`.cer` file.

11. **Generate a Certificate Signing Request (CSR)** *(application team)*. Open a
    command prompt in the OpenSSL directory (XAMPP ships OpenSSL) and run:
    ```bash
    openssl req -new -newkey rsa:2048 -nodes -keyout server.key -out server.csr \
      -subj "/C=QA/L=Doha/O=Qatar University/OU=IT Department/CN=*.qu.edu.qa"
    ```
    This produces two files:
    - `server.key` — the **private key** (keep this yourself, never share it).
    - `server.csr` — the **certificate signing request** (send this to ITS).

12. **Submit the CSR to ITS** *(performed by ITS)*. ITS signs the certificate and
    returns a certificate file (`server.cer` or `server.crt`). ITS may also issue the
    certificate directly if the domain is already registered under Qatar University.

13. **Install the certificate.**
    - Copy `server.crt` into `C:\xampp74\apache\conf\ssl.crt\`
    - Copy `server.key` into `C:\xampp74\apache\conf\ssl.key\`

14. **Add an HTTPS virtual host.** In
    `C:\xampp74\apache\conf\extra\httpd-vhosts.conf` add:
    ```apache
    # ── RTS live host (HTTPS) ──
    <VirtualHost *:443>
        ServerAdmin lrts@qu.edu.qa
        DocumentRoot "C:/xampp74/htdocs/LRTS/public"
        ServerName lrts.qu.edu.qa

        SSLEngine on
        SSLCertificateFile "C:/xampp74/apache/conf/ssl.crt/server.crt"
        SSLCertificateKeyFile "C:/xampp74/apache/conf/ssl.key/server.key"

        <Directory "C:/xampp74/htdocs/LRTS/public">
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
    ```

15. **Restart Apache** for the SSL changes to take effect:
    ```bash
    C:\xampp74\apache\bin\httpd.exe -k restart
    ```

16. **(Recommended) Force HTTPS redirection.** Ensure all traffic uses HTTPS by
    adding the following to `C:\xampp74\htdocs\LRTS\public\.htaccess` (at the top):
    ```apache
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    ```
    > The `.htaccess` file must already contain Laravel's default rewrite rules
    > (routing all requests through `index.php`). Add only the HTTPS block above it.

#### 14.3.6 Live Deployment Checklist

The table marks each task with the responsible party: **App** (application team) or
**ITS** (Information Technology Services, Qatar University).

| Step | Task | Performed by | Status |
|------|------|--------------|--------|
| 1 | XAMPP installed (V.7.0.0+) | App | ☐ |
| 2 | RTS copied/cloned into `htdocs/LRTS` | App | ☐ |
| 3 | Hosts file entry (local test only) | App | ☐ |
| 4 | Virtual host configured (HTTP, doc root = `public`) | App | ☐ |
| 5 | Sub-domain DNS (`lrts.qu.edu.qa`) registered | **ITS** | ☐ |
| 6 | `.env` set for production (`APP_DEBUG=false`) | App | ☐ |
| 7 | `php artisan migrate --force` run | App | ☐ |
| 8 | Config/route/view caches built | App | ☐ |
| 9 | Storage directories writable | App | ☐ |
| 10 | CSR generated and sent to ITS | App | ☐ |
| 11 | SSL certificate signed / issued | **ITS** | ☐ |
| 12 | SSL certificate installed on server | App | ☐ |
| 13 | HTTPS virtual host + restart Apache | App | ☐ |
| 14 | HTTPS redirect in place | App | ☐ |

> **Summary of ITS (Information Technology Services) responsibilities:**
> 1. Register the sub-domain `lrts.qu.edu.qa` and point it (DNS `A`/`CNAME` record)
>    to the server's public IP.
> 2. Sign/issue the SSL certificate after receiving the CSR from the application team.
>
> All other tasks (server setup, application configuration, virtual host, certificate
> installation, HTTPS redirection) are performed by the application team.

### 14.4 Testing
- PHPUnit configured (`phpunit.xml`).
- Tests under `tests/` — includes `ProjectModelTest` (unit) covering Project model
  helpers (status, lifecycle, actions).

---

*Documentation generated from code analysis of the RTS v2 codebase.*
