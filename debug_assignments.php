<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Illuminate\Support\Facades\DB;

echo "=== projects_reviewers ===" . PHP_EOL;
$reviewers = DB::table('projects_reviewers')
    ->join('projects', 'projects.id', '=', 'projects_reviewers.project_id')
    ->join('users', 'users.id', '=', 'projects_reviewers.user_id')
    ->select('projects_reviewers.*', 'projects.title as project_title', 'users.name as user_name')
    ->orderBy('projects_reviewers.user_id')
    ->get();

echo "Total: " . $reviewers->count() . PHP_EOL;
foreach ($reviewers as $r) {
    $gd = DB::table('grading_details')
        ->where('project_id', $r->project_id)
        ->where('user_id', $r->user_id)
        ->exists();
    echo "User{$r->user_id}($r->user_name) -> Project{$r->project_id}($r->project_title) proposalstatus=" . ($r->proposalstatus ?? 'null') . " role=" . ($r->role ?? 'null') . " hasGradingDetail=" . ($gd ? 'Y' : 'N') . PHP_EOL;
}

echo PHP_EOL . "=== GradingDetails ===" . PHP_EOL;
$gds = DB::table('grading_details')->get();
echo "Total: " . $gds->count() . PHP_EOL;
foreach ($gds as $gd) {
    echo "  project_id={$gd->project_id} user_id={$gd->user_id} reviewer_grade={$gd->reviewer_grade}" . PHP_EOL;
}

echo PHP_EOL . "=== StatusHistories (Grade/Graded) ===" . PHP_EOL;
$shs = DB::table('status_histories')
    ->whereIn('status', ['Grade-1', 'Grade-2', 'Graded'])
    ->get();
echo "Total: " . $shs->count() . PHP_EOL;
foreach ($shs as $sh) {
    echo "  project_id={$sh->project_id} status={$sh->status} triggered_by={$sh->metadata}" . PHP_EOL;
}

echo PHP_EOL . "=== Users ===" . PHP_EOL;
$users = DB::table('users')->select('id', 'name', 'type')->get();
foreach ($users as $u) {
    echo "  User{$u->id} $u->name (type=$u->type)" . PHP_EOL;
}
