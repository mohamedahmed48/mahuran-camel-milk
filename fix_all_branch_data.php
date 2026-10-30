<?php
// Comprehensive fix script to update all data with branch_id
require_once 'config.php';

try {
    // Get the main branch ID
    $main_branch_id = $pdo->query("SELECT id FROM branches LIMIT 1")->fetchColumn();
    
    if (!$main_branch_id) {
        echo "<h3>No branch found</h3>";
        echo "<p>Please create a branch first.</p>";
        echo "<p><a href='index.php'>Go to Login</a></p>";
        exit;
    }
    
    echo "<h3>Branch Data Fix Complete</h3>";
    
    // Update users without branch_id
    $roles_to_fix = ['staff', 'vet', 'driver', 'admin', 'manager'];
    foreach ($roles_to_fix as $role) {
        $stmt = $pdo->prepare("UPDATE users SET branch_id = ? WHERE role = ? AND branch_id IS NULL");
        $stmt->execute([$main_branch_id, $role]);
        $count = $stmt->rowCount();
        if ($count > 0) {
            echo "<p>Updated $count $role users with branch_id</p>";
        }
    }
    
    // Update camels without branch_id
    $stmt = $pdo->prepare("UPDATE camels SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $camel_count = $stmt->rowCount();
    if ($camel_count > 0) {
        echo "<p>Updated $camel_count camels with branch_id</p>";
    }
    
    // Update products without branch_id
    $stmt = $pdo->prepare("UPDATE products SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $product_count = $stmt->rowCount();
    if ($product_count > 0) {
        echo "<p>Updated $product_count products with branch_id</p>";
    }
    
    // Update customers without branch_id
    $stmt = $pdo->prepare("UPDATE customers SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $customer_count = $stmt->rowCount();
    if ($customer_count > 0) {
        echo "<p>Updated $customer_count customers with branch_id</p>";
    }
    
    // Update orders without branch_id
    $stmt = $pdo->prepare("UPDATE orders SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $order_count = $stmt->rowCount();
    if ($order_count > 0) {
        echo "<p>Updated $order_count orders with branch_id</p>";
    }
    
    // Update health records without branch_id
    $stmt = $pdo->prepare("UPDATE health_records SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $health_count = $stmt->rowCount();
    if ($health_count > 0) {
        echo "<p>Updated $health_count health records with branch_id</p>";
    }
    
    // Update breeding records without branch_id
    $stmt = $pdo->prepare("UPDATE breeding_records SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $breeding_count = $stmt->rowCount();
    if ($breeding_count > 0) {
        echo "<p>Updated $breeding_count breeding records with branch_id</p>";
    }
    
    // Update milk collection without branch_id
    $stmt = $pdo->prepare("UPDATE milk_collection SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $milk_count = $stmt->rowCount();
    if ($milk_count > 0) {
        echo "<p>Updated $milk_count milk collection records with branch_id</p>";
    }
    
    // Update feed types without branch_id
    $stmt = $pdo->prepare("UPDATE feed_types SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $feed_count = $stmt->rowCount();
    if ($feed_count > 0) {
        echo "<p>Updated $feed_count feed types with branch_id</p>";
    }
    
    // Update warehouse inventory without branch_id
    $stmt = $pdo->prepare("UPDATE warehouse_inventory SET branch_id = ? WHERE branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $warehouse_count = $stmt->rowCount();
    if ($warehouse_count > 0) {
        echo "<p>Updated $warehouse_count warehouse inventory records with branch_id</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>All data now has branch_id set to: $main_branch_id</strong></p>";
    echo "<p><a href='index.php'>Go to Login</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
