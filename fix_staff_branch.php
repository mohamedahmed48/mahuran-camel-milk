<?php
// Fix script to update existing staff, vet, and driver users to have branch_id
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
    
    echo "Successfully updated:\n";
    echo "- $staff_count staff users\n";
    echo "- $vet_count vet users\n";
    echo "- $driver_count driver users\n";
    echo "All users now have branch_id set to: $main_branch_id\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
