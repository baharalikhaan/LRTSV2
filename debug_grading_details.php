<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

echo "=== grading_details table ===\n";

try {
    $count = DB::table('grading_details')->count();
    echo "Table exists! Total rows: $count\n";

    $rows = DB::table('grading_details')->where('project_id', 9)->get();
    foreach ($rows as $r) {
        echo "  id={$r->id} user_id={$r->user_id} graded_at=" . ($r->graded_at ?? 'NULL') . "\n";
    }
} catch (\Exception $e) {
    echo "Table 'grading_details' does NOT exist: " . $e->getMessage() . "\n";
}

echo "\n=== showing all tables ===\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    echo "  " . implode(', ', (array)$t) . "\n";
}

echo "\n=== progress_report_gradings (project 9) ===\n";
$rows = DB::table('progress_report_gradings')->where('project_id', 9)->get();
foreach ($rows as $r) {
    echo "  id={$r->id} reviewer_id={$r->reviewer_id} graded_by=" . ($r->graded_by ?? 'N/A') . "\n";
}

echo "\n=== final_report_gradings (project 9) ===\n";
$rows = DB::table('final_report_gradings')->where('project_id', 9)->get();
foreach ($rows as $r) {
    echo "  id={$r->id} reviewer_id={$r->reviewer_id}\n";
}
