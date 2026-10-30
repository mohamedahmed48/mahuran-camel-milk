<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Configuration File

session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mahuran_camel_farm');
define('DB_USER', 'root');
define('DB_PASS', '');

// System Configuration
define('SYSTEM_NAME', 'MAHURAAN CAMEL FARMING');
define('COMPANY_NAME', 'Mahuraan Camel Farm');
define('VERSION', '1.0.0');

// Upload and backup paths
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('STAFF_UPLOAD_PATH', UPLOAD_PATH . 'staff/');
define('PROOF_PATH', __DIR__ . '/uploads/proofs/');
define('BACKUP_PATH', __DIR__ . '/backups/');

if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!file_exists(STAFF_UPLOAD_PATH)) {
    mkdir(STAFF_UPLOAD_PATH, 0755, true);
}
if (!file_exists(PROOF_PATH)) {
    mkdir(PROOF_PATH, 0755, true);
}
if (!file_exists(BACKUP_PATH)) {
    mkdir(BACKUP_PATH, 0755, true);
}

// Initialize Database Connection 
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Support Ticket Helper Functions
function generateTicketNumber($pdo) {
    $prefix = 'TKT';
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    return $prefix . $date . $random;
}

function createSupportTicket($pdo, $customer_id, $subject, $description, $priority = 'medium', $category = 'general') {
    $ticket_number = generateTicketNumber($pdo);
    
    $stmt = $pdo->prepare("
        INSERT INTO support_tickets (customer_id, ticket_number, subject, description, priority, category, status)
        VALUES (?, ?, ?, ?, ?, ?, 'open')
    ");
    $stmt->execute([$customer_id, $ticket_number, $subject, $description, $priority, $category]);
    
    return $pdo->lastInsertId();
}

function getCustomerTickets($pdo, $customer_id) {
    $stmt = $pdo->prepare("
        SELECT st.*, u.full_name as assigned_to_name
        FROM support_tickets st
        LEFT JOIN users u ON st.assigned_to = u.id
        WHERE st.customer_id = ?
        ORDER BY st.created_at DESC
    ");
    $stmt->execute([$customer_id]);
    return $stmt->fetchAll();
}

function getTicketById($pdo, $ticket_id) {
    $stmt = $pdo->prepare("
        SELECT st.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email,
               u.full_name as assigned_to_name
        FROM support_tickets st
        LEFT JOIN customers c ON st.customer_id = c.id
        LEFT JOIN users u ON st.assigned_to = u.id
        WHERE st.id = ?
    ");
    $stmt->execute([$ticket_id]);
    return $stmt->fetch();
}

function getTicketReplies($pdo, $ticket_id) {
    $stmt = $pdo->prepare("
        SELECT tr.*, u.full_name as user_name, u.role as user_role
        FROM ticket_replies tr
        LEFT JOIN users u ON tr.user_id = u.id
        WHERE tr.ticket_id = ?
        ORDER BY tr.created_at ASC
    ");
    $stmt->execute([$ticket_id]);
    return $stmt->fetchAll();
}

function addTicketReply($pdo, $ticket_id, $user_id, $message, $is_internal = false) {
    $stmt = $pdo->prepare("
        INSERT INTO ticket_replies (ticket_id, user_id, message, is_internal)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$ticket_id, $user_id, $message, $is_internal ? 1 : 0]);
    
    // Update ticket timestamp
    $stmt = $pdo->prepare("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$ticket_id]);
    
    return $pdo->lastInsertId();
}

function updateTicketStatus($pdo, $ticket_id, $status, $assigned_to = null) {
    $stmt = $pdo->prepare("
        UPDATE support_tickets 
        SET status = ?, assigned_to = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$status, $assigned_to, $ticket_id]);
    
    if ($status === 'resolved') {
        $stmt = $pdo->prepare("UPDATE support_tickets SET resolved_at = NOW() WHERE id = ?");
        $stmt->execute([$ticket_id]);
    }
    
    return true;
}

// Employee Performance and Attendance Helper Functions
function calculateAttendanceRate($pdo, $staff_id, $month = null, $year = null) {
    $where = "WHERE staff_id = ?";
    $params = [$staff_id];
    
    if ($month) {
        $where .= " AND MONTH(date) = ?";
        $params[] = $month;
    }
    
    if ($year) {
        $where .= " AND YEAR(date) = ?";
        $params[] = $year;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as marked_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN status = 'half_day' THEN 0.5 ELSE 0 END) as half_days
        FROM attendance 
        $where
    ");
    $stmt->execute($params);
    $result = $stmt->fetch();
    
    $present_count = $result['present_days'] + $result['late_days'] + $result['half_days'];
    
    // Calculate total working days in the given month up to today (or end of month)
    $calc_month = $month ?: date('n');
    $calc_year = $year ?: date('Y');
    
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $calc_month, $calc_year);
    $total_working_days = $days_in_month;
    
    if ($calc_year == date('Y') && $calc_month == date('n')) {
        $total_working_days = date('j'); // Days up to today
    }
    
    if ($total_working_days <= 0) $total_working_days = 1;
    
    $attendance_rate = ($present_count / $total_working_days) * 100;
    return min(100, round($attendance_rate, 2));
}

function getEmployeeAttendanceStats($pdo, $staff_id, $month = null, $year = null) {
    $where = "WHERE staff_id = ?";
    $params = [$staff_id];
    
    if ($month) {
        $where .= " AND MONTH(date) = ?";
        $params[] = $month;
    }
    
    if ($year) {
        $where .= " AND YEAR(date) = ?";
        $params[] = $year;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
            SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_days,
            SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as leave_days
        FROM attendance 
        $where
    ");
    $stmt->execute($params);
    return $stmt->fetch();
}

function getEmployeePerformance($pdo, $staff_id, $month, $year) {
    $stmt = $pdo->prepare("
        SELECT ep.*, u.full_name, s.position, s.department_id, d.name as department_name
        FROM employee_performance ep
        LEFT JOIN staff s ON ep.staff_id = s.id
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN departments d ON s.department_id = d.id
        WHERE ep.staff_id = ? AND ep.month = ? AND ep.year = ?
    ");
    $stmt->execute([$staff_id, $month, $year]);
    return $stmt->fetch();
}

function getAllEmployeesPerformance($pdo, $month, $year) {
    $stmt = $pdo->prepare("
        SELECT ep.*, u.full_name, s.position, s.department_id, d.name as department_name
        FROM employee_performance ep
        LEFT JOIN staff s ON ep.staff_id = s.id
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN departments d ON s.department_id = d.id
        WHERE ep.month = ? AND ep.year = ?
        ORDER BY ep.total_score DESC
    ");
    $stmt->execute([$month, $year]);
    return $stmt->fetchAll();
}

function getEmployeeRankings($pdo, $month, $year) {
    $employees = getAllEmployeesPerformance($pdo, $month, $year);
    $rankings = [];
    
    foreach ($employees as $index => $employee) {
        $rankings[] = [
            'rank' => $index + 1,
            'staff_id' => $employee['staff_id'],
            'full_name' => $employee['full_name'],
            'position' => $employee['position'],
            'department' => $employee['department_name'],
            'total_score' => $employee['total_score'],
            'attendance_score' => $employee['attendance_score'],
            'tasks_completed' => $employee['tasks_completed'],
            'punctuality_score' => $employee['punctuality_score']
        ];
    }
    
    return $rankings;
}

function getEmployeeWithMostAbsences($pdo, $month, $year) {
    $stmt = $pdo->prepare("
        SELECT 
            s.id as staff_id,
            u.full_name,
            s.position,
            d.name as department_name,
            COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absence_count,
            COUNT(*) as total_days
        FROM staff s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN attendance a ON s.id = a.staff_id 
            AND MONTH(a.date) = ? 
            AND YEAR(a.date) = ?
        LEFT JOIN departments d ON s.department_id = d.id
        WHERE s.status = 'active'
        GROUP BY s.id, u.full_name, s.position, d.name
        ORDER BY absence_count DESC
        LIMIT 1
    ");
    $stmt->execute([$month, $year]);
    return $stmt->fetch();
}

function getEmployeeWithBestAttendance($pdo, $year) {
    $stmt = $pdo->prepare("
        SELECT 
            s.id as staff_id,
            u.full_name,
            s.position,
            d.name as department_name,
            COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) as present_count,
            COUNT(*) as total_days,
            (COUNT(CASE WHEN a.status IN ('present', 'late') THEN 1 END) / COUNT(*) * 100) as attendance_rate
        FROM staff s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN attendance a ON s.id = a.staff_id 
            AND YEAR(a.date) = ?
        LEFT JOIN departments d ON s.department_id = d.id
        WHERE s.status = 'active'
        GROUP BY s.id, u.full_name, s.position, d.name
        HAVING total_days > 0
        ORDER BY attendance_rate DESC, present_count DESC
        LIMIT 1
    ");
    $stmt->execute([$year]);
    return $stmt->fetch();
}

function updateEmployeePerformanceScore($pdo, $staff_id, $month, $year) {
    // Calculate attendance score
    $attendance_rate = calculateAttendanceRate($pdo, $staff_id, $month, $year);
    
    // Get attendance stats for punctuality
    $attendance_stats = getEmployeeAttendanceStats($pdo, $staff_id, $month, $year);
    $total_days = $attendance_stats['total_days'] ?? 0;
    
    // Calculate punctuality score (percentage of days not late)
    $punctuality_score = 0;
    if ($total_days > 0) {
        $not_late_days = ($attendance_stats['present_days'] ?? 0) + ($attendance_stats['absent_days'] ?? 0) + ($attendance_stats['half_days'] ?? 0) + ($attendance_stats['leave_days'] ?? 0);
        $punctuality_score = ($not_late_days / $total_days) * 100;
    }
    
    // Get staff branch_id
    $staff_stmt = $pdo->prepare("SELECT branch_id FROM staff WHERE id = ?");
    $staff_stmt->execute([$staff_id]);
    $staff = $staff_stmt->fetch(PDO::FETCH_ASSOC);
    $branch_id = $staff['branch_id'] ?? null;
    
    // Get or create performance record
    $stmt = $pdo->prepare("
        SELECT * FROM employee_performance 
        WHERE staff_id = ? AND month = ? AND year = ?
    ");
    $stmt->execute([$staff_id, $month, $year]);
    $performance = $stmt->fetch();
    
    // Calculate total score (weighted average)
    // Attendance: 40%, Punctuality: 30%, Tasks: 20%, Custom: 10%
    $tasks_score = $performance['tasks_completed'] ?? 0;
    $custom_score = $performance['custom_score'] ?? 0;
    
    // Normalize tasks score (assume 30 tasks = 100%)
    $normalized_tasks = min(($tasks_score / 30) * 100, 100);
    
    $total_score = ($attendance_rate * 0.4) + ($punctuality_score * 0.3) + ($normalized_tasks * 0.2) + ($custom_score * 0.1);
    
    if ($performance) {
        // Update existing record
        $stmt = $pdo->prepare("
            UPDATE employee_performance 
            SET attendance_score = ?, punctuality_score = ?, total_score = ?, branch_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$attendance_rate, $punctuality_score, $total_score, $branch_id, $performance['id']]);
    } else {
        // Create new record
        $stmt = $pdo->prepare("
            INSERT INTO employee_performance (staff_id, branch_id, month, year, attendance_score, punctuality_score, total_score)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$staff_id, $branch_id, $month, $year, $attendance_rate, $punctuality_score, $total_score]);
    }
    
    return $total_score;
}

function updateAllEmployeesPerformance($pdo, $month, $year) {
    // Get all active staff
    $staff = $pdo->query("SELECT id FROM staff WHERE status = 'active'")->fetchAll();
    
    foreach ($staff as $employee) {
        updateEmployeePerformanceScore($pdo, $employee['id'], $month, $year);
    }
    
    // Update rankings
    $employees = getAllEmployeesPerformance($pdo, $month, $year);
    foreach ($employees as $index => $employee) {
        $stmt = $pdo->prepare("UPDATE employee_performance SET ranked_position = ? WHERE id = ?");
        $stmt->execute([$index + 1, $employee['id']]);
    }
    
    return count($staff);
}

function getDepartmentStaff($pdo, $department_id = null) {
    if ($department_id) {
        $stmt = $pdo->prepare("
            SELECT s.*, u.full_name, d.name as department_name 
            FROM staff s 
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN departments d ON s.department_id = d.id
            WHERE s.department_id = ? AND s.status = 'active'
        ");
        $stmt->execute([$department_id]);
    } else {
        $stmt = $pdo->query("
            SELECT s.*, u.full_name, d.name as department_name 
            FROM staff s 
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN departments d ON s.department_id = d.id
            WHERE s.status = 'active'
        ");
    }
    return $stmt->fetchAll();
}