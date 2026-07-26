<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=lrts_mcp', 'root', '');

$users = $pdo->query('SELECT u.id, u.name, u.email, u.type FROM users u INNER JOIN projects_reviewers pr ON pr.user_id = u.id GROUP BY u.id');

echo "Users with reviewed projects:\n";
while ($u = $users->fetch(PDO::FETCH_ASSOC)) {
    $userId = $u['id'];
    $collegeCheck = $pdo->query("SELECT COUNT(*) FROM user_college WHERE user_id = $userId")->fetchColumn();
    $pillarCheck = $pdo->query("SELECT COUNT(*) FROM user_pillars WHERE user_id = $userId")->fetchColumn();
    echo "  #{$userId}: {$u['name']} ({$u['type']}) | colleges=$collegeCheck | pillars=$pillarCheck\n";
}
