<?php
// Debug script - test if saving works
require_once 'config.php';
require_once 'functions.php';

echo "<h2>DEBUG: Testing Save Functionality</h2>";

// 1. Check database connection
echo "<h3>1. Database Connection</h3>";
echo "PDO connected: YES<br>";

// 2. Check if departments table exists and its columns
echo "<h3>2. Departments Table Structure</h3>";
try {
    $cols = $pdo->query("SHOW COLUMNS FROM departments")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    foreach ($cols as $col) {
        echo "Column: {$col['Field']} | Type: {$col['Type']} | Null: {$col['Null']} | Key: {$col['Key']} | Default: {$col['Default']}<br>";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
}

// 3. Check session
echo "<h3>3. Session Info</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Logged in: " . (isset($_SESSION['logged_in']) ? 'YES' : 'NO') . "<br>";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Branch ID: " . ($_SESSION['branch_id'] ?? 'NULL') . "<br>";

// 4. Try inserting a test department
echo "<h3>4. Test INSERT</h3>";
try {
    $branch_id = $_SESSION['branch_id'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO departments (name, description, branch_id) VALUES (?, ?, ?)");
    $result = $stmt->execute(['DEBUG_TEST_DEPT', 'Debug test', $branch_id]);
    echo "INSERT result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
    echo "Last Insert ID: " . $pdo->lastInsertId() . "<br>";
    
    // Clean up
    $pdo->exec("DELETE FROM departments WHERE name = 'DEBUG_TEST_DEPT'");
    echo "Cleanup: deleted test row<br>";
} catch (Exception $e) {
    echo "INSERT ERROR: " . $e->getMessage() . "<br>";
}

// 5. Check existing departments
echo "<h3>5. Existing Departments</h3>";
try {
    $depts = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);
    echo "Total departments: " . count($depts) . "<br>";
    foreach ($depts as $d) {
        echo "- ID:{$d['id']} Name:{$d['name']} Branch:{$d['branch_id']}<br>";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
}

// 6. Check POST handling
echo "<h3>6. POST Test Form</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<strong>POST Data Received:</strong><pre>";
    print_r($_POST);
    echo "</pre>";
}
?>
<form method="POST">
    <input type="hidden" name="action" value="test_action">
    <input type="text" name="test_field" value="hello">
    <button type="submit">Test POST</button>
</form>
