<?php
require 'config.php';

// Remove duplicate departments (keep the first one of each name)
echo "Removing duplicate departments...\n";

$depts = $pdo->query("SELECT name, MIN(id) as keep_id, COUNT(*) as cnt FROM departments GROUP BY LOWER(name)")->fetchAll(PDO::FETCH_ASSOC);

foreach ($depts as $d) {
    if ($d['cnt'] > 1) {
        $deleted = $pdo->exec("DELETE FROM departments WHERE LOWER(name) = LOWER('{$d['name']}') AND id != {$d['keep_id']}");
        echo "Removed $deleted duplicate(s) of '{$d['name']}' - keeping ID {$d['keep_id']}\n";
    }
}

echo "\n--- Final departments list ---\n";
$final = $pdo->query("SELECT * FROM departments ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($final as $d) {
    echo "  ID:{$d['id']} | {$d['name']} | Branch:{$d['branch_id']}\n";
}
echo "\nDONE\n";
