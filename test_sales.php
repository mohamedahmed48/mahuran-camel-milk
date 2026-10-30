<?php
require 'config.php';
require 'functions.php';

// Simulate session
$_SESSION['role'] = 'super_admin';
$_SESSION['branch_id'] = 1;
$logged_in = true;
$current_role = 'super_admin';
$page = 'salary';
$subpage = 'payroll';

// Suppress output so we don't dump the whole HTML page
ob_start();
require 'sales_pages.php';
ob_end_clean();

echo "Staff list count: " . count($staff_list) . "\n";
if (count($staff_list) > 0) {
    echo "First staff: " . ($staff_list[0]['full_name'] ?? $staff_list[0]['employee_id']) . "\n";
} else {
    echo "STAFF LIST IS EMPTY!\n";
}
?>
