<?php
require_once 'config.php';
require_once 'functions.php';

// Mock session manually bypassing auth.php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$logged_in = true;
$current_role = 'super_admin';

// Output data like api_dashboard but without requiring auth.php
$data = [];

$where = "1=1";
$where_d = "1=1";
$where_o = "1=1";

$data['active_orders'] = intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status IN ('pending', 'confirmed')")->fetchColumn() ?: 0);
$data['completed_today'] = intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status='delivered' AND DATE(delivered_at) = CURDATE()")->fetchColumn() ?: 0);
$data['count_camels'] = intval($pdo->query("SELECT COUNT(*) FROM camels WHERE status='active' AND $where")->fetchColumn() ?: 0);

echo json_encode($data);
