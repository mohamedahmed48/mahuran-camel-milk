<?php
require_once 'config.php';
require_once 'functions.php';

// Mock session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$logged_in = true;
$current_role = 'super_admin';

// Include api_dashboard but capture output
ob_start();
include 'api_dashboard.php';
$output = ob_get_clean();

echo $output;
