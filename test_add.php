<?php
session_start();
$_SESSION['user_id'] = 1; // mock
$_SESSION['branch_id'] = null; // simulate super admin

require_once 'config.php';
require_once 'functions.php';

try {
    $employee_id = "EMP999";
    $department_id = 1; // Assuming department 1 exists. We will try null if it fails.
    $position = "Tester";
    $hire_date = "2026-05-02";
    $salary = 5000;
    $phone = "123456789";
    $email = "test@test.com";
    $address = "123 Test St";
    $status = "active";
    $profile_picture = null;
    $branch_id = null;

    $stmt = $pdo->prepare("INSERT INTO staff (employee_id, department_id, position, hire_date, salary, phone, email, address, profile_picture, branch_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$employee_id, $department_id, $position, $hire_date, $salary, $phone, $email, $address, $profile_picture, $branch_id, $status]);
    echo "SUCCESS: " . $pdo->lastInsertId();
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
