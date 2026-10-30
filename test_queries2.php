<?php
require_once 'config.php';
require_once 'functions.php';
$where_o = getBranchContextQuery('o');
$q = $pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status IN ('pending', 'confirmed', 'processing')");
echo "Active orders: " . ($q->fetchColumn() ?: 0) . "\n";

$where_d = getBranchContextQuery('d');
$q2 = $pdo->query("SELECT COUNT(*) FROM deliveries d WHERE $where_d AND status='delivered' AND DATE(delivered_at) = CURDATE()");
echo "Delivered today deliveries: " . ($q2 ? $q2->fetchColumn() : 'error') . "\n";

$q3 = $pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status='delivered' AND DATE(delivered_at) = CURDATE()");
echo "Delivered today orders: " . ($q3 ? $q3->fetchColumn() : 'error') . "\n";

$where = getBranchContextQuery();
$q4 = $pdo->query("SELECT COUNT(*) FROM camels WHERE status='active' AND $where");
echo "Active camels: " . ($q4 ? $q4->fetchColumn() : 'error') . "\n";
