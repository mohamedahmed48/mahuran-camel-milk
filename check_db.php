<?php
require_once 'config.php';
$stmt = $pdo->query("SHOW TABLES");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);
?>
