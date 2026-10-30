<?php
require_once 'config.php';
// Create tables for employee performance tracking and awards

$sqls = [
    // Employee performance scores (monthly)
    "CREATE TABLE IF NOT EXISTS employee_performance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id INT NOT NULL,
        branch_id INT,
        month INT NOT NULL COMMENT '1-12',
        year INT NOT NULL,
        attendance_score DECIMAL(5,2) DEFAULT 0 COMMENT 'Based on attendance percentage',
        punctuality_score DECIMAL(5,2) DEFAULT 0,
        tasks_completed INT DEFAULT 0,
        milk_collected_liters DECIMAL(10,2) DEFAULT 0,
        custom_score DECIMAL(5,2) DEFAULT 0 COMMENT 'Manual score set by manager',
        total_score DECIMAL(5,2) DEFAULT 0,
        notes TEXT,
        ranked_position INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_staff_month (staff_id, month, year)
    ) ENGINE=InnoDB",

    // Employee awards/recognition
    "CREATE TABLE IF NOT EXISTS employee_awards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        staff_id INT NOT NULL,
        branch_id INT,
        award_type VARCHAR(100) NOT NULL COMMENT 'employee_of_month, top_performer, etc.',
        award_title VARCHAR(200) NOT NULL,
        award_description TEXT,
        month INT,
        year INT,
        cash_bonus DECIMAL(10,2) DEFAULT 0,
        given_by INT COMMENT 'user_id who gave the award',
        award_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    // Walk-in sales (POS specific - track walk-in customers)
    "CREATE TABLE IF NOT EXISTS pos_walkin_sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_number VARCHAR(50) UNIQUE,
        branch_id INT,
        cashier_id INT COMMENT 'user_id of cashier',
        customer_name VARCHAR(200) DEFAULT 'Walk-in Customer',
        customer_phone VARCHAR(30) DEFAULT NULL,
        payment_method VARCHAR(50) DEFAULT 'cash',
        subtotal DECIMAL(10,2) DEFAULT 0,
        discount DECIMAL(10,2) DEFAULT 0,
        tax DECIMAL(10,2) DEFAULT 0,
        total_amount DECIMAL(10,2) DEFAULT 0,
        amount_paid DECIMAL(10,2) DEFAULT 0,
        change_amount DECIMAL(10,2) DEFAULT 0,
        sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS pos_walkin_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT NOT NULL,
        product_id INT,
        product_name VARCHAR(200),
        quantity DECIMAL(10,2) DEFAULT 1,
        unit_price DECIMAL(10,2),
        total_price DECIMAL(10,2),
        FOREIGN KEY (sale_id) REFERENCES pos_walkin_sales(id) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

$errors = [];
foreach ($sqls as $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ OK: " . substr($sql, 0, 60) . "...<br>";
    } catch (PDOException $e) {
        $errors[] = $e->getMessage();
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
}

if (empty($errors)) {
    echo "<br><strong style='color:green'>✅ All tables created successfully!</strong>";
} else {
    echo "<br><strong style='color:red'>Some errors occurred. Check above.</strong>";
}
?>
