<?php
require_once 'config.php';
$driver = $pdo->query("SELECT id FROM users WHERE role = 'driver' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($driver) {
    $_SESSION['user_id'] = $driver['id'];
} else {
    echo "No driver found.\n";
    exit;
}
$_SESSION['role'] = 'driver';
$_SESSION['branch_id'] = 1;
$_SESSION['branch_role'] = 'global';

ob_start();
require 'api_dashboard.php';
$output = ob_get_clean();

echo "JSON OUTPUT:\n" . $output;
