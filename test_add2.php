<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['branch_id'] = null;
require_once 'config.php';
try {
    $employee_id = "EMP998";
    $department_id = ""; // simulate empty form submission
    $position = "Tester";
    $hire_date = "2026-05-02";
    $salary = 5000;
    $phone = "123456789";
    $email = "test2@test.com";
    $address = "123 Test St";
    $status = "active";
    $profile_picture = null;
    $branch_id = null;

    $stmt = $pdo->prepare("INSERT INTO staff (employee_id, department_id, position, hire_date, salary, phone, email, address, profile_picture, branch_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$employee_id, $department_id, $position, $hire_date, $salary, $phone, $email, $address, $profile_picture, $branch_id, $status]);
    echo "SUCCESS";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
