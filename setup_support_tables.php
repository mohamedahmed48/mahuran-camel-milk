<?php
require_once 'config.php';

// Create support_tickets table
$sql1 = "CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT DEFAULT NULL,
    ticket_number VARCHAR(50) DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    category VARCHAR(100) DEFAULT NULL,
    assigned_to INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    UNIQUE KEY ticket_number (ticket_number),
    KEY customer_id (customer_id),
    KEY assigned_to (assigned_to),
    CONSTRAINT support_tickets_ibfk_1 FOREIGN KEY (customer_id) REFERENCES customers (id),
    CONSTRAINT support_tickets_ibfk_2 FOREIGN KEY (assigned_to) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Create ticket_replies table
$sql2 = "CREATE TABLE IF NOT EXISTS ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    message TEXT,
    is_internal TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY ticket_id (ticket_id),
    KEY user_id (user_id),
    CONSTRAINT ticket_replies_ibfk_1 FOREIGN KEY (ticket_id) REFERENCES support_tickets (id) ON DELETE CASCADE,
    CONSTRAINT ticket_replies_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $pdo->exec($sql1);
    echo "support_tickets table created or already exists.\n";
    
    $pdo->exec($sql2);
    echo "ticket_replies table created or already exists.\n";
    
    echo "Support tables setup completed successfully.\n";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}
