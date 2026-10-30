<?php
require_once 'config.php';
// Let's force update the API endpoint to just return the camel count as a test
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$logged_in = true;
$current_role = 'super_admin';
$where = "1=1";
$c = intval($pdo->query("SELECT COUNT(*) FROM camels WHERE status='active' AND $where")->fetchColumn() ?: 0);
echo "Camel Count API Query: $c\n";

$where_o = "1=1";
$o = intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status IN ('pending', 'confirmed')")->fetchColumn() ?: 0);
echo "Active Orders API Query: $o\n";
