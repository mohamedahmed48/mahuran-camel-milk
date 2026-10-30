<?php
require_once 'config.php';
try {
    $extra_metrics['branches_count'] = $pdo->query("SELECT COUNT(*) FROM branches")->fetchColumn() ?: 0;
    echo "branches OK\n";
    $extra_metrics['global_camels'] = $pdo->query("SELECT COUNT(*) FROM camels")->fetchColumn() ?: 0;
    echo "global_camels OK\n";
    $extra_metrics['global_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
    echo "global_users OK\n";
    $extra_metrics['global_sales'] = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'")->fetchColumn() ?: 0;
    echo "global_sales OK\n";
    $extra_metrics['global_milk'] = $pdo->query("SELECT SUM(quantity_liters) FROM milk_collection WHERE collection_date = CURDATE()")->fetchColumn() ?: 0;
    echo "global_milk OK\n";
    $extra_metrics['active_subs'] = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'")->fetchColumn() ?: 0;
    echo "active_subs OK\n";
    $extra_metrics['active_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'confirmed')")->fetchColumn() ?: 0;
    echo "active_orders OK\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
