<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

echo "=== Student Grant Excel ===" . PHP_EOL;
$sheet = IOFactory::load('conftool_data_student_grant.xlsx')->getActiveSheet();
$data = $sheet->toArray();

echo "Total rows: " . count($data) . PHP_EOL;
echo "Total columns: " . count($data[0]) . PHP_EOL;
echo PHP_EOL;

echo "Header row (row 0):" . PHP_EOL;
foreach ($data[0] as $col => $val) {
    if ($val !== null && $val !== '') {
        echo "  Column {$col}: {$val}" . PHP_EOL;
    }
}

echo PHP_EOL . "First data row (row 1):" . PHP_EOL;
foreach ($data[1] as $col => $val) {
    if ($val !== null && $val !== '') {
        echo "  Column {$col}: {$val}" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Regular Grant Excel ===" . PHP_EOL;
$sheet2 = IOFactory::load('conftool_data_regular_grant.xlsx')->getActiveSheet();
$data2 = $sheet2->toArray();

echo "Total rows: " . count($data2) . PHP_EOL;
echo "Total columns: " . count($data2[0]) . PHP_EOL;
echo PHP_EOL;

echo "Header row (row 0):" . PHP_EOL;
foreach ($data2[0] as $col => $val) {
    if ($val !== null && $val !== '') {
        echo "  Column {$col}: {$val}" . PHP_EOL;
    }
}

echo PHP_EOL . "First data row (row 1):" . PHP_EOL;
foreach ($data2[1] as $col => $val) {
    if ($val !== null && $val !== '') {
        echo "  Column {$col}: {$val}" . PHP_EOL;
    }
}
