<?php
// Complete fix script - updates branch_id and adds sample data
require_once 'config.php';

try {
    // Get the main branch ID
    $main_branch_id = $pdo->query("SELECT id FROM branches LIMIT 1")->fetchColumn();
    
    if (!$main_branch_id) {
        // Create branch if none exists
        $pdo->prepare("INSERT INTO branches (name, location, status) VALUES (?, ?, ?)")
            ->execute(['Main Branch', 'Hargeisa Headquarters', 'active']);
        $main_branch_id = $pdo->lastInsertId();
        echo "<p>Created Main Branch (ID: $main_branch_id)</p>";
    }
    
    echo "<h3>Complete System Fix</h3>";
    
    // Update ALL users without branch_id
    $stmt = $pdo->prepare("UPDATE users SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $user_count = $stmt->rowCount();
    echo "<p>Updated $user_count users with branch_id</p>";
    
    // Update ALL tables with branch_id
    $tables = [
        'camels', 'products', 'customers', 'orders', 'health_records',
        'breeding_records', 'milk_collection', 'feed_types', 'warehouse_inventory',
        'staff', 'departments', 'attendance', 'leave_requests', 'disease_records',
        'vaccinations', 'vet_visits', 'pregnancy_tracking', 'calves', 'fertility_reports',
        'feed_consumption', 'feed_plans', 'feed_suppliers', 'feed_purchases',
        'milk_batches', 'milk_processing', 'delivery_zones', 'subscription_plans',
        'sales_targets', 'notifications', 'system_activity_logs', 'order_status_history',
        'salary_deductions', 'salary_bonuses', 'payslips', 'sales_commissions',
        'employee_performance', 'employee_awards', 'pos_walkin_sales', 'pos_walkin_items'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->prepare("UPDATE `$table` SET branch_id = ? WHERE branch_id IS NULL");
            $stmt->execute([$main_branch_id]);
            $count = $stmt->rowCount();
            if ($count > 0) {
                echo "<p>Updated $count records in $table with branch_id</p>";
            }
        } catch (PDOException $e) {
            // Table might not exist, skip
        }
    }
    
    // Check if camels exist, if not add sample data
    $camel_count = $pdo->query("SELECT COUNT(*) FROM camels")->fetchColumn();
    if ($camel_count == 0) {
        echo "<p>No camels found. Adding sample camels...</p>";
        $sample_camels = [
            ['C001', 'Hamed', 'Somali', 5, 'male', 450, 'Main Farm'],
            ['C002', 'Nimo', 'Somali', 4, 'female', 380, 'Main Farm'],
            ['C003', 'Ali', 'Somali', 6, 'male', 520, 'Main Farm'],
            ['C004', 'Fatima', 'Somali', 3, 'female', 340, 'Main Farm'],
            ['C005', 'Omar', 'Somali', 7, 'male', 580, 'Main Farm']
        ];
        
        foreach ($sample_camels as $camel) {
            $stmt = $pdo->prepare("INSERT INTO camels (tag_id, name, breed, age, gender, weight, farm_location, branch_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$camel[0], $camel[1], $camel[2], $camel[3], $camel[4], $camel[5], $camel[6], $main_branch_id]);
        }
        echo "<p>Added 5 sample camels</p>";
    } else {
        echo "<p>Found $camel_count camels in database</p>";
    }
    
    // Check if products exist, if not add sample data
    $product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($product_count == 0) {
        echo "<p>No products found. Adding sample products...</p>";
        $sample_products = [
            ['Fresh Camel Milk', 1.5, 50, 'liter'],
            ['Camel Milk Powder', 25, 100, 'kg'],
            ['Camel Cheese', 8, 30, 'kg'],
            ['Camel Butter', 12, 25, 'kg'],
            ['Camel Yogurt', 3, 40, 'liter']
        ];
        
        foreach ($sample_products as $product) {
            $stmt = $pdo->prepare("INSERT INTO products (name, price, stock_quantity, unit, branch_id, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$product[0], $product[1], $product[2], $product[3], $main_branch_id]);
        }
        echo "<p>Added 5 sample products</p>";
    } else {
        echo "<p>Found $product_count products in database</p>";
    }
    
    // Check if customers exist, if not add sample data
    $customer_count = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    if ($customer_count == 0) {
        echo "<p>No customers found. Adding sample customers...</p>";
        $sample_customers = [
            ['Ahmed Mohamed', '+252612345678', 'Hargeisa, Somaliland'],
            ['Fatima Ali', '+252612345679', 'Hargeisa, Somaliland'],
            ['Walk-in Customer', 'N/A', 'Store POS']
        ];
        
        foreach ($sample_customers as $customer) {
            $stmt = $pdo->prepare("INSERT INTO customers (name, phone, address, branch_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$customer[0], $customer[1], $customer[2], $main_branch_id]);
        }
        echo "<p>Added 3 sample customers</p>";
    } else {
        echo "<p>Found $customer_count customers in database</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>✓ Fix Complete!</strong></p>";
    echo "<p>All data now has branch_id set to: $main_branch_id</p>";
    echo "<p>Sample data has been added if none existed.</p>";
    echo "<p><a href='index.php' style='padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Go to Login</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
