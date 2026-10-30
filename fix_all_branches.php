<?php
/**
 * COMPREHENSIVE BRANCH FIX SCRIPT
 * Fixes all NULL branch_id values across users and data tables,
 * and adds missing branch_id columns.
 */
require 'config.php';

echo "<pre>";
echo "========================================\n";
echo "  COMPREHENSIVE BRANCH FIX SCRIPT\n";
echo "========================================\n\n";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Step 1: Get the first (main) branch ID
$first_branch = $pdo->query("SELECT id FROM branches ORDER BY id ASC LIMIT 1")->fetchColumn();
if (!$first_branch) {
    echo "ERROR: No branches found! Creating default branch...\n";
    $pdo->exec("INSERT INTO branches (name, location, status) VALUES ('Main Branch', 'Headquarters', 'active')");
    $first_branch = $pdo->lastInsertId();
}
echo "Using Main Branch ID: $first_branch\n\n";

// Step 2: Fix users with NULL branch_id (non-customers, non-super_admin)
echo "=== STEP 1: FIXING USERS ===\n";
$null_users = $pdo->query("SELECT id, username, role, branch_id FROM users WHERE branch_id IS NULL AND role != 'super_admin'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($null_users as $u) {
    // Customers don't need a branch_id typically, but staff roles do
    if (in_array($u['role'], ['admin', 'manager', 'staff', 'vet', 'driver'])) {
        $pdo->prepare("UPDATE users SET branch_id = ? WHERE id = ?")->execute([$first_branch, $u['id']]);
        echo "  FIXED user '{$u['username']}' (role: {$u['role']}) -> branch_id=$first_branch\n";
    }
}
echo "\n";

// Step 3: Add missing branch_id columns to tables that don't have them
echo "=== STEP 2: ADDING MISSING branch_id COLUMNS ===\n";
$all_tables = [
    'deliveries', 'login_history', 'delivery_proofs', 'driver_performance',
    'customer_subscriptions', 'subscription_invoices', 'customer_addresses',
    'support_tickets', 'ticket_replies', 'health_records', 'temperature_logs',
    'milk_processing', 'system_monitoring', 'roles', 'permissions',
    'sales_reports', 'database_backups', 'system_activity_logs'
];

foreach ($all_tables as $table) {
    try {
        // Check if column exists
        $cols = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'branch_id'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN branch_id INT DEFAULT NULL");
            echo "  ADDED branch_id column to '$table'\n";
        }
    } catch (Exception $e) {
        echo "  SKIP $table: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Step 4: Fix all NULL branch_id values in data tables
echo "=== STEP 3: FIXING NULL branch_id IN DATA TABLES ===\n";
$tables_to_fix = [
    'camels', 'products', 'customers', 'orders', 'staff', 'departments',
    'disease_records', 'vaccinations', 'vet_visits', 'breeding_records',
    'pregnancy_tracking', 'calves', 'fertility_reports', 'feed_types',
    'feed_plans', 'feed_consumption', 'feed_suppliers', 'feed_purchases',
    'milk_collection', 'milk_batches', 'milk_processing', 'warehouse_inventory',
    'subscription_plans', 'customer_subscriptions', 'subscription_invoices',
    'delivery_zones', 'expenses', 'notifications', 'invoices', 'payments',
    'deliveries', 'delivery_proofs', 'driver_performance',
    'leave_requests', 'salary_payments', 'attendance', 'camel_ownership',
    'sales_targets', 'sales_commissions', 'order_status_history',
    'login_history', 'pos_walkin_sales', 'pos_walkin_items',
    'health_records', 'temperature_logs', 'support_tickets',
    'customer_addresses', 'ticket_replies',
    'salary_deductions', 'salary_bonuses', 'payslips',
    'employee_performance', 'employee_awards',
    'system_activity_logs', 'database_backups', 'sales_reports'
];

$total_fixed = 0;
foreach ($tables_to_fix as $table) {
    try {
        $count = $pdo->exec("UPDATE `$table` SET branch_id = $first_branch WHERE branch_id IS NULL");
        if ($count > 0) {
            echo "  FIXED $table: $count rows updated\n";
            $total_fixed += $count;
        }
    } catch (Exception $e) {
        echo "  SKIP $table: " . $e->getMessage() . "\n";
    }
}
echo "  Total records fixed: $total_fixed\n\n";

// Step 5: Verify the fix
echo "=== STEP 4: VERIFICATION ===\n";
$key_tables = ['camels', 'products', 'customers', 'orders', 'staff', 'departments', 
               'feed_types', 'feed_plans', 'milk_collection', 'subscription_plans', 'delivery_zones'];

foreach ($key_tables as $t) {
    try {
        $total = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $with_branch = $pdo->query("SELECT COUNT(*) FROM `$t` WHERE branch_id = $first_branch")->fetchColumn();
        $null_count = $pdo->query("SELECT COUNT(*) FROM `$t` WHERE branch_id IS NULL")->fetchColumn();
        $status = $null_count == 0 ? "✓ OK" : "✗ STILL HAS NULLS";
        echo "  $t: Total=$total, WithBranch=$with_branch, Null=$null_count $status\n";
    } catch (Exception $e) {
        echo "  $t: ERROR\n";
    }
}

// Verify users
echo "\n--- USERS AFTER FIX ---\n";
$users = $pdo->query("SELECT id, username, role, branch_id FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    $status = ($u['branch_id'] === null && $u['role'] !== 'super_admin') ? " *** NEEDS FIX" : "";
    echo "  {$u['username']} (role: {$u['role']}) -> branch_id: " . ($u['branch_id'] ?? 'NULL') . "$status\n";
}

echo "\n========================================\n";
echo "  FIX COMPLETE!\n";
echo "========================================\n";
echo "</pre>";
