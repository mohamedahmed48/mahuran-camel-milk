<?php
require_once 'config.php';
$stmt = $pdo->query("SHOW COLUMNS FROM deliveries");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
