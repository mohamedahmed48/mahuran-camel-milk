<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM camels");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
