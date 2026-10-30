<?php
ob_start();
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$logged_in = true;
$current_role = 'super_admin';
include 'api_dashboard.php';
$output = ob_get_clean();
echo "OUTPUT START\n";
echo $output;
echo "\nOUTPUT END\n";
