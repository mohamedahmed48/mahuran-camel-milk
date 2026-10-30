<?php
require_once '../config.php';
try {
    $stmt = $pdo->query("DESCRIBE deliveries");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} catch (Exception $e) {
    echo $e->getMessage();
}
