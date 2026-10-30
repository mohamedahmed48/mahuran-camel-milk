<?php
require_once 'config.php';
try {
    $active_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'confirmed')")->fetchColumn() ?: 0;
    $completed_today = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered' AND DATE(delivered_at) = CURDATE()")->fetchColumn() ?: 0;
    $count_camels = $pdo->query("SELECT COUNT(*) FROM camels WHERE status='active'")->fetchColumn() ?: 0;

    echo "active_orders: $active_orders\n";
    echo "completed_today: $completed_today\n";
    echo "count_camels: $count_camels\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
