
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\GrantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PillarController;
use App\Http\Controllers\CollegeController;
use App\Http\Controllers\CycleConfigController;
use App\Http\Controllers\RegisterWizardController;
use App\Http\Controllers\ScoreController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

// ─── Dashboard ───────────────────────────────────────────────────────────────
Route::get('/home', [HomeController::class, 'index'])->name('home');

// ─── Role Switcher ──────────────────────────────────────────────────────────
Route::post('/switch-role', [HomeController::class, 'switchRole'])->name('switch-role');

// ─── Notifications API ──────────────────────────────────────────────────────
Route::get('/notifications', [HomeController::class, 'notifications'])->name('notifications');

// ─── Grants ──────────────────────────────────────────────────────────────────
Route::prefix('grant-types')->name('grant-types.')->group(function () {
    Route::get('/', [GrantController::class, 'index'])->name('index');
    Route::get('/create', [GrantController::class, 'create'])->name('create');
    Route::post('/', [GrantController::class, 'store'])->name('store');
    Route::get('/{grant}', [GrantController::class, 'show'])->name('show');
    Route::get('/{grant}/edit', [GrantController::class, 'edit'])->name('edit');
    Route::put('/{grant}', [GrantController::class, 'update'])->name('update');
    Route::delete('/{grant}', [GrantController::class, 'destroy'])->name('destroy');
});

// ─── Profile ──────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// ─── Programs ──────────────────────────────────────────────────────────────────
Route::prefix('programs')->name('programs.')->group(function () {
    Route::get('/', [ProgramController::class, 'index'])->name('index');
    Route::get('/create', [ProgramController::class, 'create'])->name('create');
    Route::get('/{id}', [ProgramController::class, 'show'])->name('show');
    Route::post('/', [ProgramController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ProgramController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ProgramController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProgramController::class, 'destroy'])->name('destroy');
    Route::post('toggle/{id}', [ProgramController::class, 'toggle'])->name('toggle');
});

// ─── Projects ────────────────────────────────────────────────────────────────
Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/available', [ProjectController::class, 'availableProjects'])->name('available');
    Route::get('/register/{id}', [ProjectController::class, 'register'])->name('register');
    Route::post('/register', [ProjectController::class, 'storeRegistration'])->name('store-registration');
    Route::get('/my', function () {
        return redirect()->route('projects.available');
    })->name('my');
    Route::get('/assign-review/{cycleId}', [ProjectController::class, 'assignView'])->name('assign-review');
    Route::post('/bulk-assign', [ProjectController::class, 'bulkAssign'])->name('bulk-assign');
    Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
    Route::get('/my-assignments', [ProjectController::class, 'myAssignments'])->name('my-assignments');
});

// ─── Users ───────────────────────────────────────────────────────────────────
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
});

// ─── Announcements ───────────────────────────────────────────────────────────
Route::prefix('announcements')->name('announcements.')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::post('/', [AnnouncementController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [AnnouncementController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AnnouncementController::class, 'update'])->name('update');
    Route::delete('/{id}', [AnnouncementController::class, 'destroy'])->name('destroy');
});

// ─── Scores (Configuration) ──────────────────────────────────────────────────
Route::prefix('scores')->name('scores.')->group(function () {
    Route::get('/', [ScoreController::class, 'index'])->name('index');
    Route::post('/', [ScoreController::class, 'store'])->name('store');
    Route::get('/{score}/edit', [ScoreController::class, 'edit'])->name('edit');
    Route::put('/{score}', [ScoreController::class, 'update'])->name('update');
    Route::delete('/{score}', [ScoreController::class, 'destroy'])->name('destroy');
});

// ─── Pillars (Configuration) ──────────────────────────────────────────────────
Route::prefix('pillars')->name('pillars.')->group(function () {
    Route::get('/', [PillarController::class, 'index'])->name('index');
    Route::get('/create', [PillarController::class, 'create'])->name('create');
    Route::post('/', [PillarController::class, 'store'])->name('store');
    Route::get('/{pillar}/edit', [PillarController::class, 'edit'])->name('edit');
    Route::put('/{pillar}', [PillarController::class, 'update'])->name('update');
    Route::delete('/{pillar}', [PillarController::class, 'destroy'])->name('destroy');
});

// ─── Colleges (Configuration) ─────────────────────────────────────────────────
Route::prefix('colleges')->name('colleges.')->group(function () {
    Route::get('/', [CollegeController::class, 'index'])->name('index');
    Route::get('/create', [CollegeController::class, 'create'])->name('create');
    Route::post('/', [CollegeController::class, 'store'])->name('store');
    Route::get('/{college}/edit', [CollegeController::class, 'edit'])->name('edit');
    Route::put('/{college}', [CollegeController::class, 'update'])->name('update');
    Route::delete('/{college}', [CollegeController::class, 'destroy'])->name('destroy');
});

// ─── Cycle Configs (Configuration) ────────────────────────────────────────────
Route::prefix('cycle-configs')->name('cycle-configs.')->group(function () {
    Route::get('/', [CycleConfigController::class, 'index'])->name('index');
    Route::post('/', [CycleConfigController::class, 'store'])->name('store');
    Route::put('/{cycleConfig}', [CycleConfigController::class, 'update'])->name('update');
    Route::delete('/{cycleConfig}', [CycleConfigController::class, 'destroy'])->name('destroy');
});

// ─── Grading ─────────────────────────────────────────────────────────────────
Route::prefix('grading')->name('grading.')->group(function () {
    Route::get('/', [GradingController::class, 'gradedProjects'])->name('index');
    Route::get('/{id}/grade', [GradingController::class, 'gradeProject'])->name('grade');
    Route::post('/{id}/save-progress-grade', [GradingController::class, 'saveProgressGrade'])->name('saveProgressGrade');
    Route::post('/{id}/save-final-grade', [GradingController::class, 'saveFinalGrade'])->name('saveFinalGrade');
    Route::post('/{id}/final-grade', [GradingController::class, 'submitFinalGrade'])->name('submitFinalGrade');
    Route::post('/{id}/submit-grade', [GradingController::class, 'submitGrade'])->name('submitGrade');
});

// ─── Project Grading Pages (full page, merged progress + final grading) ─────
Route::get('/projects/{id}/grading', [GradingController::class, 'grading'])
    ->name('projects.grading');

// Serve uploaded PDFs (proposals / progress / final / readiness reports)
// Uses the hierarchical path: {cycle_year}/{program_title}/{type_folder}/{old_id}_{type}.pdf
// Accepts optional 'submission_id' to serve a specific version of a submission file.
Route::get('/serveFile2', function (\Illuminate\Http\Request $request) {
    // Friendly "file not available" placeholder shown in the PDF iframe
    // instead of Laravel's default 404 page.
    $fileNotAvailable = function () {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root {
    --brand-500: #6c4cf1; --brand-500-soft: #f1edff;
    --ink-100: #eceef2; --ink-500: #5d6677; --sand-50: #faf8f5;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--sand-50); color: var(--ink-500);
    display: flex; align-items: center; justify-content: center; min-height: 100vh;
  }
  .box { text-align: center; }
  .icon {
    width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 50%;
    background: var(--brand-500-soft); color: var(--brand-500);
    display: flex; align-items: center; justify-content: center;
  }
  .msg { font-size: 14px; font-weight: 500; }
</style>
</head>
<body>
  <div class="box">
    <div class="icon">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
      </svg>
    </div>
    <p class="msg">File does not exist</p>
  </div>
</body>
</html>
HTML;

        return response($html, 404, ['Content-Type' => 'text/html; charset=UTF-8']);
    };

    $submissionId = $request->query('submission_id', '');
    $type = $request->query('type', '');
    $id   = $request->query('id', '');

    // If a specific submission_id is provided, serve that exact version
    if ($submissionId) {
        $submission = \App\Models\ProjectSubmission::find($submissionId);
        if ($submission && $submission->file_path) {
            $path = storage_path('app/' . $submission->file_path);
            if (file_exists($path)) {
                return response()->file($path, ['Content-Type' => 'application/pdf']);
            }
        }
        return $fileNotAvailable();
    }

    if (in_array($type, ['proposal', 'progress', 'readiness', 'final']) && $id) {
        $project = \App\Models\Project::find($id);
        if ($project) {
            // Try the deterministic filename first
            $path = storage_path('app/' . $project->getStorageFilename($type));
            if (file_exists($path)) {
                return response()->file($path, ['Content-Type' => 'application/pdf']);
            }

            // For proposals, also try legacy naming (_Application.pdf instead of _proposal.pdf)
            if ($type === 'proposal') {
                $oldId = str_replace('/', '', $project->old_project_id ?? $project->id);
                $dir = $project->getStorageDir('proposals');
                $legacyPath = storage_path('app/' . $dir . '/' . $oldId . '_Application.pdf');
                if (file_exists($legacyPath)) {
                    return response()->file($legacyPath, ['Content-Type' => 'application/pdf']);
                }
            }
        }
        return $fileNotAvailable();
    }

    $file = $request->query('file', '');
    // Prevent path traversal
    if (preg_match('/(\.\.|\/|\\\\)/', $file)) {
        abort(403);
    }
    $path = storage_path('app/' . ltrim($file, '/'));
    if (!file_exists($path)) {
        return $fileNotAvailable();
    }
    return response()->file($path, ['Content-Type' => 'application/pdf']);
})->name('serveFile2');

// Convenience aliases
Route::get('/graded-projects', [GradingController::class, 'gradedProjects'])->name('gradedProjects');
// Note: /programs, /users, /announcements are defined under their own prefix groups above.
// Using the index route name gives proper 'programs.index', 'users.index', 'announcements.index'.

// ─── Registration Wizard (Standalone page + AJAX) ───────────────────────
Route::prefix('wizard')->name('wizard.')->group(function () {
    Route::get('/load/{id}', [RegisterWizardController::class, 'wizard'])->name('load');
    Route::post('/save-all', [RegisterWizardController::class, 'saveAll'])->name('save-all');
    Route::get('/proposal/{id}', [RegisterWizardController::class, 'serveProposal'])->name('proposal');
    Route::post('/upload-proposal/{id}', [RegisterWizardController::class, 'uploadProposal'])->name('upload-proposal');
});

// Standalone registration wizard page (full-page, not modal)
Route::get('/projects/register-wizard/{id}', [RegisterWizardController::class, 'registerPage'])
    ->name('projects.register-wizard');

// ─── Workflow (Status Transitions via AJAX Modals) ──────────────────────
Route::prefix('workflow')->name('workflow.')->group(function () {
    Route::get('/modal/{action}/{projectId}', [\App\Http\Controllers\WorkflowController::class, 'modal'])->name('modal');
    Route::post('/transition', [\App\Http\Controllers\WorkflowController::class, 'transition'])->name('transition');
    Route::post('/record-redirect', [\App\Http\Controllers\WorkflowController::class, 'recordAndRedirect'])->name('record-redirect');
    Route::post('/assign-reviewers', [\App\Http\Controllers\WorkflowController::class, 'assignReviewers'])->name('assign-reviewers');
    Route::post('/submit-decision', [\App\Http\Controllers\WorkflowController::class, 'submitProposalDecision'])->name('submit-decision');
    Route::get('/view-grade/{projectId}', [\App\Http\Controllers\WorkflowController::class, 'viewGrade'])->name('view-grade');
});

// ─── Progress Reports (Full Page) ──────────────────────────────────────────
Route::prefix('progress')->name('progress.')->group(function () {
    Route::get('/add/{id}', [\App\Http\Controllers\ProgressController::class, 'add'])->name('add');
    Route::post('/save/{id}', [\App\Http\Controllers\ProgressController::class, 'save'])->name('save');
    Route::post('/save-outcomes/{id}', [\App\Http\Controllers\ProgressController::class, 'saveOutcomes'])->name('save-outcomes');
    Route::post('/save-single-outcome/{id}', [\App\Http\Controllers\ProgressController::class, 'saveSingleOutcome'])->name('save-single-outcome');
    Route::post('/delete-outcome/{id}', [\App\Http\Controllers\ProgressController::class, 'deleteOutcome'])->name('delete-outcome');
    Route::post('/save-students/{id}', [\App\Http\Controllers\ProgressController::class, 'saveStudents'])->name('save-students');
    Route::post('/save-single-student/{id}', [\App\Http\Controllers\ProgressController::class, 'saveSingleStudent'])->name('save-single-student');
    Route::post('/delete-student/{id}', [\App\Http\Controllers\ProgressController::class, 'deleteStudent'])->name('delete-student');
    Route::post('/save-single-researcher/{id}', [\App\Http\Controllers\ProgressController::class, 'saveSingleResearcher'])->name('save-single-researcher');
    Route::post('/delete-researcher/{id}', [\App\Http\Controllers\ProgressController::class, 'deleteResearcher'])->name('delete-researcher');
    Route::post('/save-toggle/{id}', [\App\Http\Controllers\ProgressController::class, 'saveToggle'])->name('save-toggle');
    Route::post('/save-contributions/{id}', [\App\Http\Controllers\ProgressController::class, 'saveContributions'])->name('save-contributions');
    Route::post('/upload-submission/{id}', [\App\Http\Controllers\ProgressController::class, 'uploadFile'])->name('upload-submission');
    Route::post('/delete-submission/{id}', [\App\Http\Controllers\ProgressController::class, 'deleteFile'])->name('delete-submission');
    Route::post('/submit-report/{id}', [\App\Http\Controllers\ProgressController::class, 'submitReport'])->name('submit-report');
    Route::get('/{id}', [\App\Http\Controllers\ProgressController::class, 'show'])->name('show');
});

// ─── Reports ─────────────────────────────────────────────────────────────────
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/program-status', [\App\Http\Controllers\ReportController::class, 'programReport'])->name('program-status');
    Route::get('/program-status/csv', [\App\Http\Controllers\ReportController::class, 'programReportCsv'])->name('program-status-csv');
    Route::get('/grants', [\App\Http\Controllers\ReportController::class, 'grantReport'])->name('grant-summary');
    Route::get('/projects', [\App\Http\Controllers\ReportController::class, 'projectReport'])->name('project-status');
    Route::get('/pillars', [\App\Http\Controllers\ReportController::class, 'pillarReport'])->name('pillar-summary');
});

// ─── Reviewer Grading (Admin) ──────────────────────────────────────────────
Route::prefix('reviewer-grading')->name('reviewer-grading.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ReviewerGradingController::class, 'index'])->name('index');
    Route::post('/save-ratings', [\App\Http\Controllers\ReviewerGradingController::class, 'saveRatings'])->name('save-ratings');
    Route::get('/detail/{u_id}', [\App\Http\Controllers\ReviewerGradingController::class, 'reviewerDetail'])->name('detail');
    Route::get('/{id}', [\App\Http\Controllers\ReviewerGradingController::class, 'show'])->name('show');
});

// AJAX route for reviewer grading
Route::get('/ajax-list-reviewer-grading', [\App\Http\Controllers\ReviewerGradingController::class, 'ajaxList'])->name('ajaxListreviewerGrading');

// Convenience aliases for backward compatibility with view
Route::get('/reviewerDetail/{u_id}', [\App\Http\Controllers\ReviewerGradingController::class, 'reviewerDetail'])->name('reviewerDetail');
Route::post('/saveratings', [\App\Http\Controllers\ReviewerGradingController::class, 'saveRatings'])->name('saveratings');

// ─── About & Help ────────────────────────────────────────────────────────────
Route::prefix('about')->name('about.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AboutController::class, 'index'])->name('index');
    Route::get('/help', [\App\Http\Controllers\AboutController::class, 'help'])->name('help');
    Route::get('/team', [\App\Http\Controllers\AboutController::class, 'team'])->name('team');
});

// Placeholder for new project (to be implemented with full project creation flow)
Route::get('/projects/new', function () {
    return redirect()->route('home')->with('info', 'Project creation is not yet implemented.');
})->name('newProject');
