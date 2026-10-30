<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE orders");
echo "Orders:\n";
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . " " . $row['Type'] . "\n";
}
$stmt = $pdo->query("DESCRIBE deliveries");
echo "\nDeliveries:\n";
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . " " . $row['Type'] . "\n";
}
$stmt = $pdo->query("DESCRIBE camels");
echo "\nCamels:\n";
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . " " . $row['Type'] . "\n";
}
