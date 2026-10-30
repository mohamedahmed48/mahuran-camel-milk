<?php
require_once 'config.php';

echo "<pre>";

function addColumnSafe($pdo, $table, $column, $definition) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->fetch();
        if (!$check) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            echo "Added $column to $table\n";
        } else {
            echo "Column $column already exists in $table\n";
        }
    } catch (PDOException $e) {
        echo "Error adding $column to $table: " . $e->getMessage() . "\n";
    }
}

// Check and add columns to orders table
$order_cols = ['confirmed_at', 'processed_at', 'packed_at', 'shipped_at', 'delivered_at', 'cancelled_at'];
foreach ($order_cols as $col) {
    addColumnSafe($pdo, 'orders', $col, 'DATETIME DEFAULT NULL');
}

// Check and add columns to deliveries table
$delivery_cols = ['assigned_at', 'delivered_at'];
foreach ($delivery_cols as $col) {
    addColumnSafe($pdo, 'deliveries', $col, 'DATETIME DEFAULT NULL');
}

// Check and add columns to notifications
$notif_cols = ['is_read'];
foreach ($notif_cols as $col) {
    addColumnSafe($pdo, 'notifications', $col, 'BOOLEAN DEFAULT FALSE');
}
$notif_cols2 = ['created_at'];
foreach ($notif_cols2 as $col) {
    addColumnSafe($pdo, 'notifications', $col, 'DATETIME DEFAULT CURRENT_TIMESTAMP');
}

echo "Done fixing DB columns.\n";
echo "</pre>";
?>
