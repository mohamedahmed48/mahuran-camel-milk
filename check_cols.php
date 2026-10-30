<?php
require_once 'config.php';
$stmt = $pdo->query("SHOW COLUMNS FROM deliveries");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
