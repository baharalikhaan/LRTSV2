<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    $c = App\Models\College::orderBy('name')->get();
    echo 'Count: ' . count($c) . PHP_EOL;
    echo 'Type: ' . gettype($c) . PHP_EOL;
    foreach ($c as $college) {
        echo ' - ' . $college->name . PHP_EOL;
    }
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
