<?php
require_once 'config.php';
$uid = 1;
try {
    $recent = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.address 
                           FROM deliveries d 
                           JOIN orders o ON d.order_id = o.id 
                           JOIN customers c ON o.customer_id = c.id 
                           WHERE d.driver_id = $uid AND d.status IN ('in_transit', 'delivered', 'cancelled') 
                           ORDER BY COALESCE(d.delivered_at, d.cancelled_at, d.accepted_at) DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "Recent OK.\n";
} catch (Exception $e) {
    echo "Recent Error: " . $e->getMessage() . "\n";
}

try {
    $queue = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.address, c.phone as customer_phone
                          FROM deliveries d 
                          JOIN orders o ON d.order_id = o.id 
                          JOIN customers c ON o.customer_id = c.id 
                          WHERE d.driver_id = $uid AND d.status IN ('assigned', 'in_transit') 
                          ORDER BY d.delivery_date ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo "Queue OK.\n";
} catch (Exception $e) {
    echo "Queue Error: " . $e->getMessage() . "\n";
}
