<?php
require 'config.php';

$staff = $pdo->query("SELECT s.id, s.employee_id, s.position, s.branch_id, s.user_id, u.full_name FROM staff s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.id")->fetchAll(PDO::FETCH_ASSOC);

echo "Staff records:\n";
foreach($staff as $r) {
    $name = $r['full_name'] ?? 'NULL';
    echo "ID:{$r['id']} EmpID:{$r['employee_id']} Pos:{$r['position']} Branch:{$r['branch_id']} UserID:{$r['user_id']} Name:{$name}\n";
}
echo "Total: " . count($staff) . "\n";
?>
