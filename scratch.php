<?php
require 'c:/xampp/htdocs/mahu/config.php';
$pdo->exec("UPDATE users SET role = 'super_admin' WHERE role IN ('admin', 'manager')");
$stmt = $pdo->query("SELECT id, username, role FROM users");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['username'] . " -> " . $row['role'] . "\n";
}
?>
