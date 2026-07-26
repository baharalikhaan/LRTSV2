<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lrts_mcp', 'root', '');
$stmt = $pdo->query('DESCRIBE colleges');
echo "Colleges table columns:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}
echo "\nFirst 5 records:\n";
$stmt2 = $pdo->query('SELECT * FROM colleges LIMIT 5');
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
