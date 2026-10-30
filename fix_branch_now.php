<?php
// Quick fix script to update existing staff, vet, and driver users to have branch_id
require_once 'config.php';

try {
    // Get the main branch ID
    $main_branch_id = $pdo->query("SELECT id FROM branches LIMIT 1")->fetchColumn();
    
    if (!$main_branch_id) {
        echo "No branch found. Please create a branch first.\n";
        exit;
    }
    
    // Update staff users without branch_id
    $stmt = $pdo->prepare("UPDATE users SET branch_id = ? WHERE role = 'staff' AND branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $staff_count = $stmt->rowCount();
    
    // Update vet users without branch_id
    $stmt = $pdo->prepare("UPDATE users SET branch_id = ? WHERE role = 'vet' AND branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $vet_count = $stmt->rowCount();
    
    // Update driver users without branch_id
    $stmt = $pdo->prepare("UPDATE users SET branch_id = ? WHERE role = 'driver' AND branch_id IS NULL");
    $stmt->execute([$main_branch_id]);
    $driver_count = $stmt->rowCount();
    
    echo "<h3>Branch ID Fix Complete</h3>";
    echo "<p>Successfully updated:</p>";
    echo "<ul>";
    echo "<li>$staff_count staff users</li>";
    echo "<li>$vet_count vet users</li>";
    echo "<li>$driver_count driver users</li>";
    echo "</ul>";
    echo "<p>All users now have branch_id set to: $main_branch_id</p>";
    echo "<p><a href='index.php'>Go to Login</a></p>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
