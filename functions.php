<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Functions File

// Function to create notification
function createNotification($pdo, $user_id, $title, $message, $type = 'info') {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message, $type]);
}

// Function to create invoice
function createInvoice($pdo, $order_id, $customer_id, $subtotal, $tax, $delivery_fee, $total) {
    $invoice_number = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
    $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, order_id, customer_id, invoice_date, due_date, subtotal, tax, delivery_fee, total_amount, status) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), ?, ?, ?, ?, 'unpaid')");
    return $stmt->execute([$invoice_number, $order_id, $customer_id, $subtotal, $tax, $delivery_fee, $total]);
}

// Function to log order status change
function logOrderStatus($pdo, $order_id, $status, $changed_by, $notes = '') {
    $stmt = $pdo->prepare("INSERT INTO order_status_history (order_id, status, changed_by, notes) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$order_id, $status, $changed_by, $notes]);
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

function getBranchContextQuery($alias = '') {
    // Branch management has been removed. Always return true condition for queries.
    return "1=1"; 
}

// Function to notify admins and staff
function notifyStaff($pdo, $title, $message, $type = 'info') {
    $sql = "SELECT id FROM users WHERE role IN ('super_admin', 'admin', 'manager', 'staff')";
    $params = [];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($staff as $user) {
        createNotification($pdo, $user['id'], $title, $message, $type);
    }
}

// Function to get unread notifications count
function getUnreadNotificationsCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// Function to mark notification as read
function markNotificationRead($pdo, $notification_id, $user_id) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notification_id, $user_id]);
}

// Function to get user notifications
function getUserNotifications($pdo, $user_id, $limit = 10) {
    $limit = max(1, intval($limit));
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT " . $limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sendSMS($to, $message) {
    // Placeholder for real SMS provider integration (e.g., Twilio, Nexmo)
    // Return true when this is successful in production.
    return true;
}

function sendWhatsApp($to, $message) {
    // Placeholder for real WhatsApp API integration.
    return true;
}

function sendEmail($to, $subject, $message) {
    // Placeholder for real email sending logic. Unit tests can use this in place of mail().
    return mail($to, $subject, $message, "Content-Type: text/plain; charset=UTF-8\r\n");
}

function logActivity($pdo, $user_id, $action, $description = '') {
    if (!$user_id) {
        return false;
    }
    $stmt = $pdo->prepare("INSERT INTO system_activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $action, $description, $_SERVER['REMOTE_ADDR'] ?? '']);
}

function uploadFile($file, $destinationDir) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $allowedExt = ['jpg','jpeg','png','gif','webp','pdf'];
    $maxSize = 10 * 1024 * 1024;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        return false;
    }
    if ($file['size'] > $maxSize) {
        return false;
    }

    if (!file_exists($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    $filename = uniqid('up_', true) . '.' . $ext;
    $target = rtrim($destinationDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $filename;
    }
    return false;
}

function uploadProfilePicture($file, $destinationDir = null) {
    if (!$destinationDir) {
        $destinationDir = defined('STAFF_UPLOAD_PATH') ? STAFF_UPLOAD_PATH : (defined('UPLOAD_PATH') ? UPLOAD_PATH : __DIR__ . '/uploads/');
    }
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 5 * 1024 * 1024;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        return false;
    }
    if ($file['size'] > $maxSize || $file['size'] <= 0) {
        return false;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowedMimes)) {
            return false;
        }
    } elseif (function_exists('getimagesize')) {
        $check = @getimagesize($file['tmp_name']);
        if ($check === false) {
            return false;
        }
    }

    if (!file_exists($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    $filename = 'staff_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = rtrim($destinationDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $filename;
    }
    return false;
}

function getProfilePictureUrl($filename) {
    if (!empty($filename)) {
        if (defined('STAFF_UPLOAD_PATH') && file_exists(STAFF_UPLOAD_PATH . $filename)) {
            return 'uploads/staff/' . htmlspecialchars($filename);
        }
        if (defined('UPLOAD_PATH') && file_exists(UPLOAD_PATH . $filename)) {
            return 'uploads/' . htmlspecialchars($filename);
        }
        if (file_exists(__DIR__ . '/uploads/staff/' . $filename)) {
            return 'uploads/staff/' . htmlspecialchars($filename);
        }
        if (file_exists(__DIR__ . '/uploads/' . $filename)) {
            return 'uploads/' . htmlspecialchars($filename);
        }
        return 'uploads/' . htmlspecialchars($filename);
    }
    return 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="%236c757d"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm0 14c-2.03 0-3.8-1.04-4.83-2.61.03-1.6 3.22-2.48 4.83-2.48s4.8 1.88 4.83 2.48C15.8 18.96 14.03 20 12 20z"/></svg>';
}

function createDatabaseBackup($pdo, $created_by) {
    $timestamp = date('Ymd_His');
    $fileName = "backup_{$timestamp}.sql";
    $filePath = BACKUP_PATH . $fileName;

    $mysqldump = 'mysqldump';
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $xampp_path = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (file_exists($xampp_path)) {
            $mysqldump = '"' . $xampp_path . '"';
        }
    }

    $command = sprintf(
        '%s --user=%s --password=%s --host=%s %s > %s 2>&1',
        $mysqldump,
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASS),
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_NAME),
        escapeshellarg($filePath)
    );

    exec($command, $output, $returnVar);
    if ($returnVar !== 0 || !file_exists($filePath)) {
        return false;
    }

    $stmt = $pdo->prepare("INSERT INTO database_backups (backup_name, backup_path, backup_size, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fileName, $filePath, $size, $created_by]);

    return true;
}

function generatePayslip($pdo, $staff_id, $month, $year) {
    $staff = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
    $staff->execute([$staff_id]);
    $staff_data = $staff->fetch(PDO::FETCH_ASSOC);
    if (!$staff_data) {
        return false;
    }

    $base_salary = $staff_data['salary'] ?: 0;

    $deductions = $pdo->prepare("SELECT SUM(amount) FROM salary_deductions WHERE staff_id = ? AND month = ? AND year = ?");
    $deductions->execute([$staff_id, $month, $year]);
    $total_deductions = $deductions->fetchColumn() ?: 0;

    $bonuses = $pdo->prepare("SELECT SUM(amount) FROM salary_bonuses WHERE staff_id = ? AND month = ? AND year = ?");
    $bonuses->execute([$staff_id, $month, $year]);
    $total_bonuses = $bonuses->fetchColumn() ?: 0;

    $net_salary = $base_salary - $total_deductions + $total_bonuses;
    $pdf_path = "payslip_{$staff_id}_{$year}_{$month}.pdf";
    $stmt = $pdo->prepare("INSERT INTO payslips (staff_id, month, year, base_salary, total_deductions, total_bonuses, net_salary, generated_date, pdf_path) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->execute([$staff_id, $month, $year, $base_salary, $total_deductions, $total_bonuses, $net_salary, $pdf_path]);

    return true;
}