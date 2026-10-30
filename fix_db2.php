<?php
$host = 'localhost';
$dbname = 'mahuran_db';
$user = 'root'; // Adjust if needed
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Delete any users who still have the deprecated roles (if any)
    $stmt1 = $pdo->prepare("DELETE FROM users WHERE role IN ('admin', 'manager')");
    $stmt1->execute();
    
    // Delete specific users that were created by default or identified as manager/admin by username
    $usernames_to_delete = ['admin', 'manager', 'branchadmin'];
    $placeholders = implode(',', array_fill(0, count($usernames_to_delete), '?'));
    
    $stmt2 = $pdo->prepare("DELETE FROM users WHERE username IN ($placeholders)");
    $stmt2->execute($usernames_to_delete);
    
    echo "Successfully deleted deprecated manager and branch admin accounts.\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
