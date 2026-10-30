<?php
$host = 'localhost';
$dbname = 'mahuran_db';
$user = 'root'; // Adjust if needed
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("UPDATE users SET role = 'super_admin' WHERE role IN ('admin', 'manager')");
    $stmt = $pdo->query("SELECT id, username, role FROM users");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['username'] . " -> " . $row['role'] . "\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
