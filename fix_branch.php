<?php
require 'config.php';

// Get first branch ID
$first_branch = $pdo->query("SELECT id FROM branches LIMIT 1")->fetchColumn();
echo "First branch ID: $first_branch\n";

// Update all records with NULL branch_id to first branch for key tables
$tables_to_update = [
    'departments', 'staff', 'camels', 'health_records', 'disease_records',
    'vaccinations', 'vet_visits', 'breeding_records', 'pregnancy_tracking',
    'calves', 'fertility_reports', 'feed_types', 'feed_consumption', 'feed_plans',
    'feed_suppliers', 'feed_purchases', 'products', 'salary_deductions', 'salary_bonuses', 'payslips'
];

foreach ($tables_to_update as $t) {
    try {
        $count = $pdo->exec("UPDATE `$t` SET branch_id = $first_branch WHERE branch_id IS NULL");
        echo "UPDATED $t: $count rows set to branch_id=$first_branch\n";
    } catch (Exception $e) {
        echo "ERR $t: " . $e->getMessage() . "\n";
    }
}

echo "\n--- Departments now ---\n";
$depts = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($depts as $d) {
    echo "  ID:{$d['id']} | Name:{$d['name']} | Branch:{$d['branch_id']}\n";
}

echo "\nDONE - All records with NULL branch_id updated to branch $first_branch\n";
