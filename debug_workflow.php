<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Project;
use App\Models\User;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\DB;

// Find the first project that has Claimed status
$project = Project::whereIn('id', function($q) {
    $q->select('project_id')->from('status_histories')->where('status', 'Claimed');
})->first();

if (!$project) {
    echo "No project found with Claimed status.\n";
    // Try any project
    $project = Project::first();
}

echo "Project ID: {$project->id}\n";
echo "Project Title: {$project->title}\n";
echo "Current status: " . ($project->current_status ?? 'null') . "\n\n";

// Show all statuses for this project
echo "=== Status Histories ===\n";
$statuses = StatusHistory::where('project_id', $project->id)->orderBy('id')->get();
foreach ($statuses as $s) {
    echo "  #{$s->id}: {$s->status} (user_id: {$s->user_id})\n";
}

echo "\n=== Status Checks ===\n";
echo "HasStatus('Assigned'): " . ($project->hasStatus('Assigned') ? 'YES' : 'NO') . "\n";
echo "HasStatus('Claim-1'): " . ($project->hasStatus('Claim-1') ? 'YES' : 'NO') . "\n";
echo "HasStatus('Claim-2'): " . ($project->hasStatus('Claim-2') ? 'YES' : 'NO') . "\n";
echo "HasStatus('Claimed'): " . ($project->hasStatus('Claimed') ? 'YES' : 'NO') . "\n";
echo "HasStatus('Grade-1'): " . ($project->hasStatus('Grade-1') ? 'YES' : 'NO') . "\n";
echo "HasStatus('Grade-2'): " . ($project->hasStatus('Grade-2') ? 'YES' : 'NO') . "\n";
echo "HasStatus('Graded'): " . ($project->hasStatus('Graded') ? 'YES' : 'NO') . "\n";
echo "HasStatus('registered'): " . ($project->hasStatus(Project::STATUS_REGISTERED) ? 'YES' : 'NO') . "\n";
echo "HasStatus('progress_added'): " . ($project->hasStatus(Project::STATUS_PROGRESS) ? 'YES' : 'NO') . "\n";

// Check reviewers
echo "\n=== Reviewers ===\n";
$reviewers = DB::table('projects_reviewers')->where('project_id', $project->id)->get();
foreach ($reviewers as $r) {
    $user = User::find($r->user_id);
    echo "  User #{$r->user_id} ({$user->name}): role={$r->role}, proposalstatus={$r->proposalstatus}\n";

    echo "  userHasClaimed({$r->user_id}): " . ($project->userHasClaimed($r->user_id) ? 'YES' : 'NO') . "\n";
    echo "  userHasGraded({$r->user_id}): " . ($project->userHasGraded($r->user_id) ? 'YES' : 'NO') . "\n";
    echo "  isReviewer: " . ($user->isReviewer() ? 'YES' : 'NO') . "\n";

    echo "  availableActions for user {$r->user_id}:\n";
    $actions = $project->availableActions($user);
    foreach ($actions as $a) {
        echo "    - {$a['action']} ({$a['label']})\n";
    }
    if (empty($actions)) echo "    (empty)\n";
}
