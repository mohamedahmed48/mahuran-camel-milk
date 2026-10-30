<?php
require 'config.php';

$tables = [
    'departments','staff','attendance','leave_requests','salary_payments',
    'camels','camel_ownership','health_records','disease_records','vaccinations',
    'vet_visits','breeding_records','pregnancy_tracking','calves','fertility_reports',
    'feed_types','feed_consumption','feed_plans','feed_suppliers','feed_purchases',
    'milk_collection','milk_batches','warehouse_inventory','products','orders',
    'invoices','payments','expenses','customers','notifications',
    'salary_deductions','salary_bonuses','payslips','delivery_zones','support_tickets'
];

foreach ($tables as $t) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'branch_id'")->fetch();
        if (!$check) {
            $pdo->exec("ALTER TABLE `$t` ADD COLUMN branch_id INT DEFAULT NULL");
            echo "ADDED branch_id: $t\n";
        } else {
            echo "EXISTS: $t\n";
        }
    } catch (Exception $e) {
        echo "ERR $t: " . $e->getMessage() . "\n";
    }
}

// Test department insert
try {
    $stmt = $pdo->prepare("INSERT INTO departments (name, description, branch_id) VALUES (?, ?, ?)");
    $stmt->execute(['TEST_DEPT', 'test', null]);
    $id = $pdo->lastInsertId();
    echo "INSERT TEST SUCCESS - ID: $id\n";
    $pdo->exec("DELETE FROM departments WHERE id = $id");
    echo "CLEANUP DONE\n";
} catch (Exception $e) {
    echo "INSERT ERR: " . $e->getMessage() . "\n";
}

// Show columns of departments
echo "\nDepartments table columns:\n";
$cols = $pdo->query("SHOW COLUMNS FROM departments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "  " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\nDONE\n";
