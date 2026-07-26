<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    $reviewers = \App\Models\User::whereHas('reviewedProjects')
        ->withCount('reviewedProjects')
        ->with(['colleges', 'pillars'])
        ->get();

    echo "Reviewers count: " . count($reviewers) . "\n";

    if (count($reviewers) > 0) {
        $r = $reviewers->first();
        echo "First reviewer: {$r->name}\n";
        echo "Colleges count: " . count($r->colleges) . "\n";
        echo "Pillars count: " . count($r->pillars) . "\n";
    }

    $colleges = \App\Models\College::orderBy('name')->get();
    echo "Colleges from DB: " . count($colleges) . "\n";

    $pillars = \App\Models\Pillar::orderBy('pillar')->get();
    echo "Pillars from DB: " . count($pillars) . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
