<?php
require 'config.php';
try {
    $pdo->prepare('INSERT INTO products (name, category, price, stock_quantity, unit, status, image, description, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute(['Test Product', 'Milk', 10.0, 100.0, 'liter', 'active', 'test.jpg', 'Test', 1]);
    echo 'Success';
} catch(Exception $e) {
    echo $e->getMessage();
}
