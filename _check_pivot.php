<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lrts_mcp', 'root', '');

echo "=== Checking pivot tables ===\n\n";

foreach (['user_college', 'user_pillars', 'projects_reviewers'] as $table) {
    $s = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($s->rowCount() > 0) {
        echo "✓ $table EXISTS\n";
        $desc = $pdo->query("DESCRIBE $table");
        while ($r = $desc->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$r['Field']} ({$r['Type']})\n";
        }
    } else {
        echo "✗ $table MISSING\n";
    }
    echo "\n";
}

echo "=== Sample data ===\n\n";

// Check user_college data
$uc = $pdo->query("SELECT * FROM user_college LIMIT 5");
echo "user_college rows:\n";
while ($r = $uc->fetch(PDO::FETCH_ASSOC)) {
    echo "  id={$r['id']} user_id={$r['user_id']} tag_id={$r['tag_id']}\n";
}

// Check user_pillars data
$up = $pdo->query("SELECT * FROM user_pillars LIMIT 5");
echo "\nuser_pillars rows:\n";
while ($r = $up->fetch(PDO::FETCH_ASSOC)) {
    echo "  id={$r['id']} user_id={$r['user_id']} pillar_id={$r['pillar_id']}\n";
}

// Check colleges data
$c = $pdo->query("SELECT * FROM colleges LIMIT 10");
echo "\ncolleges:\n";
while ($r = $c->fetch(PDO::FETCH_ASSOC)) {
    echo "  #{$r['id']}: {$r['name']}\n";
}
