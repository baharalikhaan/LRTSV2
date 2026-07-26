<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Just check the controller logic
$controller = $app->make(App\Http\Controllers\ReviewerGradingController::class);
try {
    $ref = new ReflectionMethod($controller, 'index');
    $result = $ref->invoke($controller);
    echo "Success: " . get_class($result) . "\n";
    $data = $result->getData();
    echo "Reviewers: " . gettype($data['reviewers']) . " count: " . count($data['reviewers']) . "\n";
    echo "Colleges: " . gettype($data['colleges']) . " count: " . count($data['colleges']) . "\n";
    echo "Pillars: " . gettype($data['pillars']) . " count: " . count($data['pillars']) . "\n";

    // Check the reviewers
    foreach ($data['reviewers'] as $r) {
        echo "Reviewer: {$r->name} (type: {$r->type})\n";
        echo "  Colleges: " . gettype($r->colleges) . " count: " . ($r->colleges ? count($r->colleges) : 0) . "\n";
        echo "  Pillars: " . gettype($r->pillars) . " count: " . ($r->pillars ? count($r->pillars) : 0) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
