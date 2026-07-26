<?php
$dbName = 'lrts_mcp';
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=$dbName", 'root', '');

    // Check for user_college table
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_college'");
    if ($stmt && $stmt->rowCount() > 0) {
        echo "Found user_college table in '$dbName'!\n";
        $stmt2 = $pdo->query('DESCRIBE user_college');
        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "No user_college table found.\n";
    }

    // Check for college_user table
    $stmt = $pdo->query("SHOW TABLES LIKE 'college_user'");
    if ($stmt && $stmt->rowCount() > 0) {
        echo "Found college_user table in '$dbName'!\n";
        $stmt2 = $pdo->query('DESCRIBE college_user');
        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "No college_user table found.\n";
    }

    // Show all tables containing 'college' or 'user'
    echo "\nAll tables with 'college' or 'user' in name:\n";
    $stmt3 = $pdo->query("SHOW TABLES LIKE '%college%'");
    while ($row = $stmt3->fetch(PDO::FETCH_NUM)) {
        echo "  - {$row[0]}\n";
    }
    $stmt4 = $pdo->query("SHOW TABLES LIKE '%user%'");
    while ($row = $stmt4->fetch(PDO::FETCH_NUM)) {
        echo "  - {$row[0]}\n";
    }

} catch(Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
