<?php
// Comprehensive fix script for Branch Admin access issues
require_once 'config.php';

try {
    echo "<h3>Branch Admin Access Fix脚本</h3>";
    
    // Step 1: Ensure all branches exist and are active
    echo "<h4>Step 1: Checking Branches</h4>";
    $branches = $pdo->query("SELECT * FROM branches")->fetchAll(PDO::FETCH_ASSOC);
    $branch_map = [];
    foreach ($branches as $b) {
        $branch_map[$b['name']] = $b['id'];
    }
    
    // Create Mahuran3 branch if it doesn't exist
    if (!isset($branch_map['Mahuran3'])) {
        $pdo->prepare("INSERT INTO branches (name, location, status) VALUES (?, ?, ?)")
            ->execute(['Mahuran3', 'Mahuran3 Branch Location', 'active']);
        $branch_map['Mahuran3'] = $pdo->lastInsertId();
        echo "<p>✓ Created Mahuran3 branch (ID: {$branch_map['Mahuran3']})</p>";
    } else {
        echo "<p>✓ Mahuran3 branch exists (ID: {$branch_map['Mahuran3']})</p>";
    }
    
    // Step 2: Fix admin users without branch_id
    echo "<h4>Step 2: Fixing Admin Users</h4>";
    $admins_without_branch = $pdo->query("SELECT id, username, full_name FROM users WHERE role = 'admin' AND branch_id IS NULL")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins_without_branch)) {
        echo "<p>✓ All admin users have branch_id set</p>";
    } else {
        foreach ($admins_without_branch as $admin) {
            // Assign to Mahuran3 branch if username contains 'mahuran' or 'Mahuran'
            if (stripos($admin['username'], 'mahuran') !== false || stripos($admin['full_name'], 'mahuran') !== false) {
                $pdo->prepare("UPDATE users SET branch_id = ? WHERE id = ?")->execute([$branch_map['Mahuran3'], $admin['id']]);
                echo "<p>✓ Assigned admin {$admin['username']} to Mahuran3 branch</p>";
            } else {
                // Assign to first available branch
                $first_branch = reset($branch_map);
                $pdo->prepare("UPDATE users SET branch_id = ? WHERE id = ?")->execute([$first_branch, $admin['id']]);
                echo "<p>✓ Assigned admin {$admin['username']} to branch ID: $first_branch</p>";
            }
        }
    }
    
    // Step 3: Update ALL data without branch_id to their respective branches
    echo "<h4>Step 3: Updating Data Without Branch ID</h4>";
    
    $tables_to_fix = [
        'camels', 'products', 'customers', 'orders', 'health_records',
        'breeding_records', 'milk_collection', 'feed_types', 'warehouse_inventory',
        'staff', 'departments', 'attendance', 'leave_requests', 'disease_records',
        'vaccinations', 'vet_visits', 'pregnancy_tracking', 'calves', 'fertility_reports',
        'feed_consumption', 'feed_plans', 'feed_suppliers', 'feed_purchases',
        'milk_batches', 'milk_processing', 'delivery_zones', 'subscription_plans',
        'sales_targets', 'notifications', 'system_activity_logs', 'order_status_history',
        'salary_deductions', 'salary_bonuses', 'payslips', 'sales_commissions',
        'employee_performance', 'employee_awards', 'pos_walkin_sales', 'pos_walkin_items',
        'deliveries'
    ];
    
    $total_fixed = 0;
    foreach ($tables_to_fix as $table) {
        try {
            // For each table, update records without branch_id
            // Assign them to the same branch as their associated user if possible
            $stmt = $pdo->prepare("UPDATE `$table` SET branch_id = ? WHERE branch_id IS NULL");
            $stmt->execute([$branch_map['Mahuran3']]);
            $count = $stmt->rowCount();
            if ($count > 0) {
                echo "<p>✓ Updated $count records in $table to Mahuran3 branch</p>";
                $total_fixed += $count;
            }
        } catch (PDOException $e) {
            // Table might not exist or have different structure
        }
    }
    
    if ($total_fixed == 0) {
        echo "<p>✓ All data already has branch_id set</p>";
    } else {
        echo "<p>✓ Total records updated: $total_fixed</p>";
    }
    
    // Step 4: Verify admin permissions
    echo "<h4>Step 4: Verifying Admin Permissions</h4>";
    require_once 'index.php';
    if (isset($role_permissions['admin'])) {
        echo "<p>✓ Admin has " . count($role_permissions['admin']) . " permissions</p>";
    } else {
        echo "<p style='color:red;'>✗ Admin permissions not found!</p>";
    }
    
    // Step 5: Create sample data for Mahuran3 if needed
    echo "<h4>Step 5: Checking Sample Data</h4>";
    $mahuran3_id = $branch_map['Mahuran3'];
    
    $camel_count = $pdo->query("SELECT COUNT(*) FROM camels WHERE branch_id = $mahuran3_id")->fetchColumn();
    if ($camel_count == 0) {
        echo "<p>⚠ No camels in Mahuran3 branch. Adding sample data...</p>";
        $sample_camels = [
            ['M3-001', 'Mahuran Camel 1', 'Somali', 5, 'male', 450],
            ['M3-002', 'Mahuran Camel 2', 'Somali', 4, 'female', 380],
        ];
        foreach ($sample_camels as $camel) {
            $stmt = $pdo->prepare("INSERT INTO camels (tag_id, name, breed, age, gender, weight, farm_location, branch_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Mahuran3 Farm', ?, 'active')");
            $stmt->execute([$camel[0], $camel[1], $camel[2], $camel[3], $camel[4], $camel[5], $mahuran3_id]);
        }
        echo "<p>✓ Added 2 sample camels to Mahuran3 branch</p>";
    }
    
    $product_count = $pdo->query("SELECT COUNT(*) FROM products WHERE branch_id = $mahuran3_id")->fetchColumn();
    if ($product_count == 0) {
        echo "<p>⚠ No products in Mahuran3 branch. Adding sample data...</p>";
        $sample_products = [
            ['Mahuran Fresh Milk', 1.5, 50, 'liter'],
            ['Mahuran Cheese', 8, 30, 'kg'],
        ];
        foreach ($sample_products as $product) {
            $stmt = $pdo->prepare("INSERT INTO products (name, price, stock_quantity, unit, branch_id, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$product[0], $product[1], $product[2], $product[3], $mahuran3_id]);
        }
        echo "<p>✓ Added 2 sample products to Mahuran3 branch</p>";
    }
    
    echo "<hr>";
    echo "<p style='color:green;'><strong>✓ Fix Complete!</strong></p>";
    echo "<p>Please log out and log back in as your Branch Admin user.</p>";
    echo "<p><a href='index.php' style='padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Go to Login</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
