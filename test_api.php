<?php
session_start();
$_SESSION['role'] = 'super_admin';
$_SESSION['user_id'] = 1;
$_SESSION['full_name'] = 'Test';
$_SESSION['branch_id'] = 1;
$logged_in = true;
$current_role = 'super_admin';
require_once 'config.php';
require_once 'functions.php';

echo "Testing api_dashboard.php as super_admin...\n";
ob_start();
require 'api_dashboard.php';
$output = ob_get_clean();
print_r(json_decode($output, true));
