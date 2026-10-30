<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM orders");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    $data = $pdo->query("SELECT * FROM orders WHERE status='delivered' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    print_r($data);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
