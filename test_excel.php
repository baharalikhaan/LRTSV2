<?php
$autoloadPath = __DIR__ . '/vendor/autoload.php';
echo 'Autoload exists: ' . (file_exists($autoloadPath) ? 'YES' : 'NO') . PHP_EOL;
require $autoloadPath;
echo 'IOFactory: ' . (class_exists('PhpOffice\PhpSpreadsheet\IOFactory') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'Spreadsheet: ' . (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet') ? 'OK' : 'FAIL') . PHP_EOL;

// Check what's in the vendor dir
$vendorDir = __DIR__ . '/vendor/composer';
echo PHP_EOL . '=== Composer autoload files ===' . PHP_EOL;
foreach (glob($vendorDir . '/autoload_*.php') as $f) {
    echo basename($f) . ': ' . filesize($f) . ' bytes' . PHP_EOL;
}
