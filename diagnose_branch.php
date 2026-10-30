<?php
require 'config.php';

echo "<pre>";
echo "=== BRANCH DIAGNOSTIC ===\n\n";

// 1. Check branches
echo "--- BRANCHES ---\n";
$branches = $pdo->query("SELECT * FROM branches")->fetchAll(PDO::FETCH_ASSOC);
foreach ($branches as $b) {
    echo "  ID:{$b['id']} | Name:{$b['name']} | Status:{$b['status']}\n";
}
$first_branch_id = $branches[0]['id'] ?? 'NONE';
echo "First branch ID: $first_branch_id\n\n";

// 2. Check users and their branch_ids
echo "--- USERS ---\n";
$users = $pdo->query("SELECT id, username, role, branch_id FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "  ID:{$u['id']} | User:{$u['username']} | Role:{$u['role']} | Branch:{$u['branch_id']}\n";
}

// 3. Check key tables for NULL branch_id counts
echo "\n--- NULL BRANCH_ID COUNTS ---\n";
$tables = [
    'camels', 'products', 'customers', 'orders', 'staff', 'departments',
    'disease_records', 'vaccinations', 'vet_visits', 'breeding_records',
    'pregnancy_tracking', 'calves', 'fertility_reports', 'feed_types',
    'feed_plans', 'feed_consumption', 'feed_suppliers', 'feed_purchases',
    'milk_collection', 'milk_batches', 'warehouse_inventory',
    'subscription_plans', 'delivery_zones', 'expenses', 'notifications',
    'invoices', 'payments', 'deliveries', 'leave_requests',
    'salary_payments', 'attendance', 'camel_ownership',
    'sales_targets', 'sales_commissions', 'order_status_history',
    'login_history', 'pos_walkin_sales'
];

foreach ($tables as $table) {
    try {
        $total = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $null_count = $pdo->query("SELECT COUNT(*) FROM `$table` WHERE branch_id IS NULL")->fetchColumn();
        $has_data = $total > 0 ? "HAS DATA" : "EMPTY";
        $status = ($null_count > 0 && $total > 0) ? "*** PROBLEM ***" : "OK";
        echo "  $table: Total=$total, NullBranch=$null_count [$has_data] $status\n";
    } catch (Exception $e) {
        echo "  $table: ERROR - " . $e->getMessage() . "\n";
    }
}

// 4. Quick check: what a staff user with branch_id=1 would see
echo "\n--- SIMULATION: Staff with branch_id=$first_branch_id ---\n";
$test_tables = ['camels', 'products', 'customers', 'orders', 'staff', 'feed_types', 'feed_plans', 'departments'];
foreach ($test_tables as $t) {
    try {
        $visible = $pdo->query("SELECT COUNT(*) FROM `$t` WHERE branch_id = $first_branch_id")->fetchColumn();
        $total = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "  $t: Visible=$visible / Total=$total\n";
    } catch (Exception $e) {
        echo "  $t: ERROR\n";
    }
}

echo "\n=== DONE ===\n";
echo "</pre>";
