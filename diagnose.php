<?php
// Diagnostic script to check database state
require_once 'config.php';

echo "<h3>Database Diagnostic Report</h3>";
echo "<style>table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#007bff;color:white;}</style>";

// Check branches
echo "<h4>1. Branches</h4>";
$branches = $pdo->query("SELECT * FROM branches")->fetchAll(PDO::FETCH_ASSOC);
if (empty($branches)) {
    echo "<p style='color:red;'>❌ No branches found!</p>";
} else {
    echo "<table><tr><th>ID</th><th>Name</th><th>Location</th><th>Status</th></tr>";
    foreach ($branches as $b) {
        echo "<tr><td>{$b['id']}</td><td>{$b['name']}</td><td>{$b['location']}</td><td>{$b['status']}</td></tr>";
    }
    echo "</table>";
}

// Check users
echo "<h4>2. Users</h4>";
$users = $pdo->query("SELECT id, username, role, branch_id, status FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "<table><tr><th>ID</th><th>Username</th><th>Role</th><th>Branch ID</th><th>Status</th></tr>";
foreach ($users as $u) {
    $branch_status = $u['branch_id'] ? "<span style='color:green;'>✓</span>" : "<span style='color:red;'>✗ NULL</span>";
    echo "<tr><td>{$u['id']}</td><td>{$u['username']}</td><td>{$u['role']}</td><td>{$u['branch_id']} $branch_status</td><td>{$u['status']}</td></tr>";
}
echo "</table>";

// Check camels
echo "<h4>3. Camels</h4>";
$camel_count = $pdo->query("SELECT COUNT(*) FROM camels")->fetchColumn();
echo "<p>Total camels: $camel_count</p>";
if ($camel_count > 0) {
    $camels = $pdo->query("SELECT id, tag_id, name, branch_id, status FROM camels LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table><tr><th>ID</th><th>Tag ID</th><th>Name</th><th>Branch ID</th><th>Status</th></tr>";
    foreach ($camels as $c) {
        $branch_status = $c['branch_id'] ? "<span style='color:green;'>✓</span>" : "<span style='color:red;'>✗ NULL</span>";
        echo "<tr><td>{$c['id']}</td><td>{$c['tag_id']}</td><td>{$c['name']}</td><td>{$c['branch_id']} $branch_status</td><td>{$c['status']}</td></tr>";
    }
    echo "</table>";
}

// Check products
echo "<h4>4. Products</h4>";
$product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
echo "<p>Total products: $product_count</p>";
if ($product_count > 0) {
    $products = $pdo->query("SELECT id, name, branch_id, status, stock_quantity FROM products LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table><tr><th>ID</th><th>Name</th><th>Branch ID</th><th>Status</th><th>Stock</th></tr>";
    foreach ($products as $p) {
        $branch_status = $p['branch_id'] ? "<span style='color:green;'>✓</span>" : "<span style='color:red;'>✗ NULL</span>";
        echo "<tr><td>{$p['id']}</td><td>{$p['name']}</td><td>{$p['branch_id']} $branch_status</td><td>{$p['status']}</td><td>{$p['stock_quantity']}</td></tr>";
    }
    echo "</table>";
}

// Check customers
echo "<h4>5. Customers</h4>";
$customer_count = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
echo "<p>Total customers: $customer_count</p>";
if ($customer_count > 0) {
    $customers = $pdo->query("SELECT id, name, branch_id FROM customers LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table><tr><th>ID</th><th>Name</th><th>Branch ID</th></tr>";
    foreach ($customers as $c) {
        $branch_status = $c['branch_id'] ? "<span style='color:green;'>✓</span>" : "<span style='color:red;'>✗ NULL</span>";
        echo "<tr><td>{$c['id']}</td><td>{$c['name']}</td><td>{$c['branch_id']} $branch_status</td></tr>";
    }
    echo "</table>";
}

// Check session
echo "<h4>6. Current Session</h4>";
session_start();
echo "<p>Logged in: " . (isset($_SESSION['logged_in']) ? ($_SESSION['logged_in'] ? 'Yes' : 'No') : 'No') . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";
echo "<p>Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
echo "<p>Branch ID: " . ($_SESSION['branch_id'] ?? 'Not set') . "</p>";

echo "<hr>";
echo "<p><a href='index.php'>Go to Login</a></p>";
?>
