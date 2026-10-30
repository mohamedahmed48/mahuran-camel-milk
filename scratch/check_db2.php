<?php
require_once '../config.php';
try {
    $stmt = $pdo->query("DESCRIBE deliveries");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('out.txt', print_r($cols, true));
} catch (Exception $e) {
    file_put_contents('out.txt', $e->getMessage());
}
