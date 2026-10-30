<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, status FROM camels");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "Camel " . $row['id'] . " status: " . $row['status'] . "\n";
}
