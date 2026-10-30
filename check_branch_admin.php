<?php
// Diagnostic script to check Branch Admin access issues
require_once 'config.php';

echo "<h3>Branch Admin Diagnostic Report</h3>";
echo "<style>table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#007bff;color:white;}</style>";

// Check all branches
echo "<h4>1. All Branches</h4>";
$branches = $pdo->query("SELECT * FROM branches")->fetchAll(PDO::FETCH_ASSOC);
echo "<table><tr><th>ID</th><th>Name</th><th>Location</th><th>Status</th></tr>";
foreach ($branches as $b) {
    echo "<tr><td>{$b['id']}</td><td>{$b['name']}</td><td>{$b['location']}</td><td>{$b['status']}</td></tr>";
}
echo "</table>";

// Check admin users
echo "<h4>2. Admin Users</h4>";
$admins = $pdo->query("SELECT id, username, full_name, role, branch_id, status FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_ASSOC);
echo "<table><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Branch ID</th><th>Status</th></tr>";
foreach ($admins as $a) {
    $branch_status = $a['branch_id'] ? "<span style='color:green;'>✓ Branch {$a['branch_id']}</span>" : "<span style='color:red;'>✗ NULL</span>";
    echo "<tr><td>{$a['id']}</td><td>{$a['username']}</td><td>{$a['full_name']}</td><td>{$a['branch_id']} $branch_status</td><td>{$a['status']}</td></tr>";
}
echo "</table>";

// Check for Mahuran3 specifically
echo "<h4>3. Mahuran3 Branch Check</h4>";
$mahuran3 = $pdo->query("SELECT * FROM branches WHERE name LIKE '%Mahuran3%' OR name LIKE '%mahuran3%'")->fetchAll(PDO::FETCH_ASSOC);
if (empty($mahuran3)) {
    echo "<p style='color:red;'>❌ Mahuran3 branch not found in database!</p>";
} else {
    echo "<p style='color:green;'>✓ Mahuran3 branch found:</p>";
    echo "<table><tr><th>ID</th><th>Name</th><th>Location</th><th>Status</th></tr>";
    foreach ($mahuran3 as $b) {
        echo "<tr><td>{$b['id']}</td><td>{$b['name']}</td><td>{$b['location']}</td><td>{$b['status']}</td></tr>";
    }
    echo "</table>";
    
    // Check users assigned to Mahuran3
    $branch_id = $mahuran3[0]['id'];
    echo "<h4>4. Users in Mahuran3 Branch</h4>";
    $branch_users = $pdo->query("SELECT id, username, full_name, role, branch_id FROM users WHERE branch_id = $branch_id")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($branch_users)) {
        echo "<p style='color:red;'>❌ No users assigned to Mahuran3 branch!</p>";
    } else {
        echo "<table><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th></tr>";
        foreach ($branch_users as $u) {
            echo "<tr><td>{$u['id']}</td><td>{$u['username']}</td><td>{$u['full_name']}</td><td>{$u['role']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Check data in Mahuran3 branch
    echo "<h4>5. Data in Mahuran3 Branch</h4>";
    $data_counts = [
        'Camels' => "SELECT COUNT(*) FROM camels WHERE branch_id = $branch_id",
        'Products' => "SELECT COUNT(*) FROM products WHERE branch_id = $branch_id",
        'Customers' => "SELECT COUNT(*) FROM customers WHERE branch_id = $branch_id",
        'Orders' => "SELECT COUNT(*) FROM orders WHERE branch_id = $branch_id",
    ];
    
    echo "<table><tr><th>Data Type</th><th>Count</th></tr>";
    foreach ($data_counts as $name => $query) {
        $count = $pdo->query($query)->fetchColumn();
        $status = $count > 0 ? "<span style='color:green;'>✓</span>" : "<span style='color:red;'>✗ 0</span>";
        echo "<tr><td>$name</td><td>$count $status</td></tr>";
    }
    echo "</table>";
}

// Check admin permissions
echo "<h4>6. Admin Role Permissions</h4>";
require_once 'index.php';
if (isset($role_permissions['admin'])) {
    echo "<p>Admin has access to: " . implode(', ', $role_permissions['admin']) . "</p>";
} else {
    echo "<p style='color:red;'>❌ Admin role permissions not found!</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Go to Login</a></p>";
?>
