<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Process Actions File - Handles all POST requests

require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: ?page=login');
    exit;
}

$action = $_POST['action'] ?? '';
$current_role = $_SESSION['role'] ?? '';
$current_branch_id = $_SESSION['branch_id'] ?? null;

// Enforce strict Action-Level Permissions
$action_permissions = [
    'super_admin' => ['*'], // allowed all actions

    'staff' => [
        'add_camel', 'update_camel_status', 'update_camel', 'delete_camel', 'add_ownership',
        'add_health_record', 'add_disease', 'add_vaccination', 'add_vet_visit',
        'add_breeding', 'update_breeding', 'add_pregnancy_check', 'add_calf', 'add_fertility_report',
        'add_feed_type', 'add_feed_plan', 'add_feed_consumption', 'add_feed_supplier', 'add_feed_purchase',
        'add_milk_collection', 'create_batch', 'pasteurize_batch', 'approve_quality',
        'update_inventory', 'add_temperature_log', 'dispose_inventory', 'add_manual_inventory',
        'mark_notification_read', 'mark_all_notifications', 'mark_all_notifications_read',
        'pos_sale', 'create_order', 'update_order_status', 'add_customer', 'accept_order',
        'add_task', 'update_task_status'
    ],
    'vet' => [
        'add_health', 'add_disease', 'add_vaccination', 'add_vet_visit',
        'mark_notification_read', 'mark_all_notifications_read'
    ],
    'driver' => [
        'update_delivery_status', 'add_proof', 'mark_delivered',
        'accept_delivery_assignment', 'cancel_delivery_assignment', 'mark_delivery_completed',
        'mark_notification_read', 'mark_all_notifications_read', 'employee_mark_attendance'
    ],
    'manager' => [
        'employee_mark_attendance'
    ]
];

// Add employee_mark_attendance to staff and vet
$action_permissions['staff'][] = 'employee_mark_attendance';
$action_permissions['vet'][] = 'employee_mark_attendance';

$allowed_actions = $action_permissions[$current_role] ?? [];
if (!in_array('*', $allowed_actions) && !in_array($action, $allowed_actions)) {
    $_SESSION['message'] = "Waad ku mahadsan tahay, laakiin ma haysatid ogolaansho aad ku fuliso ficilkan (Awoodda: " . ucfirst($current_role) . ").";
    $_SESSION['message_type'] = "danger";
    header('Location: ?page=dashboard');
    exit;
}

// ==================== USER MANAGEMENT ACTIONS ====================

if ($action == 'add_user') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $plain_password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $email = (isset($_POST['email']) && trim($_POST['email']) !== '') ? trim($_POST['email']) : null;
    $phone = trim($_POST['phone'] ?? '');
    $role = (isset($_POST['role']) && $_POST['role'] !== '') ? $_POST['role'] : null;
    // Security check: Branch admins cannot create super_admin or admin roles
    if (!isSuperAdmin()) {
        if ($role === 'super_admin' || $role === 'admin') {
            $role = 'staff'; // Default to a safe role if they tried to spoof it
        }
    }

    try {
        // Check for duplicate username
        $existing = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $existing->execute([$username]);
        if ($existing->fetch()) {
            $_SESSION['message'] = "Error: This ID has already been taken.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=users&subpage=add');
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, role, status, plain_password) VALUES (?, ?, ?, ?, ?, ?, 'active', ?)");
        $stmt->execute([$username, $password, $email, $full_name, $phone, $role, $plain_password]);
        $user_id = $pdo->lastInsertId();
        
        logActivity($pdo, $_SESSION['user_id'], 'add_user', "Added user: {$username}");
        createNotification($pdo, $user_id, "Welcome to the system", "Your account has been created successfully.", 'success');
        
        $_SESSION['message'] = "User added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=users&subpage=list');
    exit;
}

if ($action == 'update_user') {
    $user_id = (isset($_POST['user_id']) && $_POST['user_id'] !== '') ? $_POST['user_id'] : null;
    $full_name = trim($_POST['full_name']);
    $email = (isset($_POST['email']) && trim($_POST['email']) !== '') ? trim($_POST['email']) : null;
    $phone = trim($_POST['phone'] ?? '');
    $role = (isset($_POST['role']) && $_POST['role'] !== '') ? $_POST['role'] : null;
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    
    // Security check: Branch admins cannot escalate roles to super_admin or admin
    if (!isSuperAdmin()) {
        if ($role === 'super_admin' || $role === 'admin') {
            // Force role to remain unchanged or something safe if they tried to spoof it
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $role = $stmt->fetchColumn();
        }
    }

    try {
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $plain_password = $_POST['password'];
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ?, status = ?, password = ?, plain_password = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $role, $status, $password, $plain_password, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $role, $status, $user_id]);
        }
        
        logActivity($pdo, $_SESSION['user_id'], 'update_user', "Updated user ID: {$user_id}");
        
        $_SESSION['message'] = "User updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=users&subpage=list');
    exit;
}

if ($action == 'self_change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    if ($new_password !== $confirm_password) {
        $_SESSION['message'] = "Password-ka cusub iyo kan aad ku xaqiijisay isuma eka! (New password and confirmation do not match!)";
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=change-password');
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ?, plain_password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $new_password, $user_id]);
            
            logActivity($pdo, $user_id, 'change_password', "User changed their password.");
            
            $_SESSION['message'] = "Password-ka waa la badalay! (Password changed successfully!)";
            $_SESSION['message_type'] = "success";
            header('Location: ?page=dashboard');
            exit;
        } else {
            $_SESSION['message'] = "Password-ka hada sax ma aha! (Current password is incorrect!)";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=change-password');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=change-password');
        exit;
    }
}

if ($action == 'delete_user') {
    $user_id = (isset($_POST['user_id']) && $_POST['user_id'] !== '') ? $_POST['user_id'] : null;
    
    // Security check: Branch admins can only delete users in their branch
    if (!isSuperAdmin()) {
        $stmt = $pdo->prepare("SELECT branch_id, role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_to_delete = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user_to_delete || $user_to_delete['branch_id'] != $current_branch_id || $user_to_delete['role'] == 'super_admin') {
            $_SESSION['message'] = "Unauthorized!";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=users&subpage=list');
            exit;
        }
    }

    try {
        // Prevent deleting self
        if ($user_id == $_SESSION['user_id']) {
            throw new Exception("You cannot delete your own account.");
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'delete_user', "Deleted user ID: {$user_id}");
        
        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=users&subpage=list');
    exit;
}

if ($action == 'add_role') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $permissions = json_encode($_POST['permissions'] ?? []);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO roles (name, description, permissions) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $permissions]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_role', "Added role: {$name}");
        
        $_SESSION['message'] = "Role added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=users&subpage=roles');
    exit;
}

// ==================== STAFF MANAGEMENT ACTIONS ====================

if ($action == 'add_staff') {
    $employee_id = trim($_POST['employee_id']);
    $department_id = (isset($_POST['department_id']) && $_POST['department_id'] !== '') ? $_POST['department_id'] : null;
    $position = trim($_POST['position']);
    $hire_date = (isset($_POST['hire_date']) && $_POST['hire_date'] !== '') ? $_POST['hire_date'] : null;
    $salary = (isset($_POST['salary']) && $_POST['salary'] !== '') ? $_POST['salary'] : null;
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address'] ?? '');
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    $profile_picture = null;

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $uploadResult = uploadProfilePicture($_FILES['profile_picture'], STAFF_UPLOAD_PATH);
        if ($uploadResult) {
            $profile_picture = $uploadResult;
        } else {
            $_SESSION['message'] = "Error: Invalid image file or file size exceeds limit (Max 5MB. Allowed: JPG, PNG, WEBP, GIF).";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=staff&subpage=add');
            exit;
        }
    }

    try {
        // Check for duplicate employee_id
        $existing = $pdo->prepare("SELECT id FROM staff WHERE employee_id = ?");
        $existing->execute([$employee_id]);
        if ($existing->fetch()) {
            $_SESSION['message'] = "Error: This ID has already been taken.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=staff&subpage=add');
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO staff (employee_id, department_id, position, hire_date, salary, phone, email, address, profile_picture, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $department_id, $position, $hire_date, $salary, $phone, $email, $address, $profile_picture, $status]);

        logActivity($pdo, $_SESSION['user_id'], 'add_staff', "Added staff: {$employee_id}");
        notifyStaff($pdo, "New Staff Added", "New staff member {$employee_id} has been added.", 'info');

        $_SESSION['message'] = "Staff added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=staff&subpage=list');
    exit;
}

if ($action == 'update_staff') {
    $staff_id = (isset($_POST['staff_id']) && $_POST['staff_id'] !== '') ? $_POST['staff_id'] : null;
    $department_id = (isset($_POST['department_id']) && $_POST['department_id'] !== '') ? $_POST['department_id'] : null;
    $position = trim($_POST['position']);
    $salary = (isset($_POST['salary']) && $_POST['salary'] !== '') ? $_POST['salary'] : null;
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address'] ?? '');
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    $profile_picture = null;

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $uploadResult = uploadProfilePicture($_FILES['profile_picture'], STAFF_UPLOAD_PATH);
        if ($uploadResult) {
            $profile_picture = $uploadResult;
            $existing = $pdo->prepare("SELECT profile_picture FROM staff WHERE id = ?");
            $existing->execute([$staff_id]);
            $existingPhoto = $existing->fetchColumn();
            if ($existingPhoto) {
                if (file_exists(STAFF_UPLOAD_PATH . $existingPhoto)) {
                    @unlink(STAFF_UPLOAD_PATH . $existingPhoto);
                } elseif (file_exists(UPLOAD_PATH . $existingPhoto)) {
                    @unlink(UPLOAD_PATH . $existingPhoto);
                }
            }
        } else {
            $_SESSION['message'] = "Error: Invalid image file or file size exceeds limit (Max 5MB. Allowed: JPG, PNG, WEBP, GIF).";
            $_SESSION['message_type'] = "danger";
            header("Location: ?page=staff&subpage=edit&id={$staff_id}");
            exit;
        }
    }

    try {
        if ($profile_picture) {
            $stmt = $pdo->prepare("UPDATE staff SET department_id = ?, position = ?, salary = ?, phone = ?, email = ?, address = ?, profile_picture = ?, status = ? WHERE id = ?");
            $stmt->execute([$department_id, $position, $salary, $phone, $email, $address, $profile_picture, $status, $staff_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE staff SET department_id = ?, position = ?, salary = ?, phone = ?, email = ?, address = ?, status = ? WHERE id = ?");
            $stmt->execute([$department_id, $position, $salary, $phone, $email, $address, $status, $staff_id]);
        }

        logActivity($pdo, $_SESSION['user_id'], 'update_staff', "Updated staff ID: {$staff_id}");

        $_SESSION['message'] = "Staff updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=staff&subpage=list');
    exit;
}

if ($action == 'delete_staff') {
    $staff_id = (isset($_POST['staff_id']) && $_POST['staff_id'] !== '') ? $_POST['staff_id'] : null;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt->execute([$staff_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'delete_staff', "Deleted staff ID: {$staff_id}");
        
        $_SESSION['message'] = "Staff deleted successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=staff&subpage=list');
    exit;
}

if ($action == 'mark_notification_read') {
    $notification_id = (isset($_POST['notification_id']) && $_POST['notification_id'] !== '') ? $_POST['notification_id'] : null;

    try {
        markNotificationRead($pdo, $notification_id, $_SESSION['user_id']);
        $_SESSION['message'] = "Notification marked as read.";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }

    $redirect = $_POST['redirect'] ?? '?page=notifications&subpage=alerts';
    header('Location: ' . $redirect);
    exit;
}

if ($action == 'mark_all_notifications') {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['message'] = "All notifications marked as read.";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=notifications&subpage=alerts');
    exit;
}

if ($action == 'add_department') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    
    try {
        // Check for duplicate department name
        $existing = $pdo->prepare("SELECT id FROM departments WHERE name = ?");
        $existing->execute([$name]);
        if ($existing->fetch()) {
            $_SESSION['message'] = "Error: This ID has already been taken.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=staff&subpage=departments');
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_department', "Added department: {$name}");
        
        $_SESSION['message'] = "Department added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=staff&subpage=departments');
    exit;
}



// ==================== CAMEL MANAGEMENT ACTIONS ====================

if ($action == 'add_camel') {
    $tag_id = trim($_POST['tag_id']);
    $name = trim($_POST['name'] ?? '');
    $breed = trim($_POST['breed']);
    $age = (isset($_POST['age']) && $_POST['age'] !== '') ? $_POST['age'] : null;
    $gender = (isset($_POST['gender']) && $_POST['gender'] !== '') ? $_POST['gender'] : null;
    $weight = $_POST['weight'] ?? null;
    $farm_location = trim($_POST['farm_location'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    try {
        // Check for duplicate camel tag_id
        $existing = $pdo->prepare("SELECT id FROM camels WHERE tag_id = ?");
        $existing->execute([$tag_id]);
        if ($existing->fetch()) {
            $_SESSION['message'] = "Error: This ID has already been taken.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=camels&subpage=add');
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO camels (tag_id, name, breed, age, gender, weight, farm_location, branch_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tag_id, $name, $breed, $age, $gender, $weight, $farm_location, $current_branch_id, $status]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_camel', "Added camel: {$tag_id}");
        notifyStaff($pdo, "New Camel Registered", "New camel {$tag_id} has been registered.", 'info');
        
        $_SESSION['message'] = "Camel registered successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=camels&subpage=list');
    exit;
}

if ($action == 'update_camel') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $tag_id = trim($_POST['tag_id']);
    $name = trim($_POST['name'] ?? '');
    $breed = trim($_POST['breed']);
    $age = (isset($_POST['age']) && $_POST['age'] !== '') ? $_POST['age'] : null;
    $gender = (isset($_POST['gender']) && $_POST['gender'] !== '') ? $_POST['gender'] : null;
    $weight = $_POST['weight'] ?? null;
    $farm_location = trim($_POST['farm_location'] ?? '');
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE camels SET tag_id = ?, name = ?, breed = ?, age = ?, gender = ?, weight = ?, farm_location = ?, status = ? WHERE id = ?");
        $stmt->execute([$tag_id, $name, $breed, $age, $gender, $weight, $farm_location, $status, $camel_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'update_camel', "Updated camel ID: {$camel_id}");
        
        $_SESSION['message'] = "Camel updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=camels&subpage=list');
    exit;
}

if ($action == 'delete_camel') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE camels SET status = 'deceased' WHERE id = ?");
        $stmt->execute([$camel_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'delete_camel', "Marked camel ID: {$camel_id} as deceased");
        
        $_SESSION['message'] = "Camel marked as deceased!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=camels&subpage=list');
    exit;
}

if ($action == 'add_ownership') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $owner_name = trim($_POST['owner_name']);
    $owner_phone = trim($_POST['owner_phone'] ?? '');
    $owner_address = trim($_POST['owner_address'] ?? '');
    $purchase_date = (isset($_POST['purchase_date']) && $_POST['purchase_date'] !== '') ? $_POST['purchase_date'] : null;
    $purchase_price = $_POST['purchase_price'] ?? null;
    $notes = trim($_POST['notes'] ?? '');
    $mark_sold = isset($_POST['mark_sold']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO camel_ownership (camel_id, owner_name, owner_phone, owner_address, purchase_date, purchase_price, notes, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $owner_name, $owner_phone, $owner_address, $purchase_date, $purchase_price, $notes, $current_branch_id]);
        
        if ($mark_sold) {
            $pdo->prepare("UPDATE camels SET status = 'sold' WHERE id = ?")->execute([$camel_id]);
        }
        
        logActivity($pdo, $_SESSION['user_id'], 'add_ownership', "Added ownership record for camel ID: {$camel_id}");
        
        $_SESSION['message'] = "Ownership record added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=camels&subpage=ownership');
    exit;
}

// ==================== HEALTH RECORDS ACTIONS ====================

if ($action == 'add_health_record') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $record_date = (isset($_POST['record_date']) && $_POST['record_date'] !== '') ? $_POST['record_date'] : null;
    $diagnosis = trim($_POST['diagnosis']);
    $treatment = trim($_POST['treatment']);
    $vet_name = trim($_POST['vet_name']);
    $next_visit = $_POST['next_visit'] ?? null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO health_records (camel_id, record_date, diagnosis, treatment, vet_name, next_visit, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $record_date, $diagnosis, $treatment, $vet_name, $next_visit, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_health_record', "Added health record for camel ID: {$camel_id}");
        
        // Notify vet if next visit is set
        if ($next_visit) {
            $vets = $pdo->query("SELECT id FROM users WHERE role = 'vet'")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($vets as $vet) {
                createNotification($pdo, $vet['id'], "Upcoming Health Check", "Camel has a health check scheduled for {$next_visit}.", 'warning');
            }
        }
        
        $_SESSION['message'] = "Health record added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=health&subpage=records');
    exit;
}

if ($action == 'add_disease') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $disease_name = trim($_POST['disease_name']);
    $diagnosed_date = (isset($_POST['diagnosed_date']) && $_POST['diagnosed_date'] !== '') ? $_POST['diagnosed_date'] : null;
    $severity = (isset($_POST['severity']) && $_POST['severity'] !== '') ? $_POST['severity'] : null;
    $treatment = trim($_POST['treatment']);
    $vet_name = trim($_POST['vet_name']);
    $recovery_date = $_POST['recovery_date'] ?? null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO disease_records (camel_id, disease_name, diagnosed_date, severity, treatment, vet_name, recovery_date, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $disease_name, $diagnosed_date, $severity, $treatment, $vet_name, $recovery_date, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_disease', "Added disease record for camel ID: {$camel_id} - {$disease_name}");
        
        // Notify staff about severe disease
        if ($severity == 'severe') {
            notifyStaff($pdo, "Severe Disease Alert", "Camel has been diagnosed with {$disease_name} (Severe). Please take immediate action.", 'danger');
        }
        
        $_SESSION['message'] = "Disease record added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=health&subpage=diseases');
    exit;
}

if ($action == 'add_vaccination') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $vaccine_name = trim($_POST['vaccine_name']);
    $vaccination_date = (isset($_POST['vaccination_date']) && $_POST['vaccination_date'] !== '') ? $_POST['vaccination_date'] : null;
    $next_due_date = $_POST['next_due_date'] ?? null;
    $administered_by = trim($_POST['administered_by']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO vaccinations (camel_id, vaccine_name, vaccination_date, next_due_date, administered_by, branch_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $vaccine_name, $vaccination_date, $next_due_date, $administered_by, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_vaccination', "Added vaccination for camel ID: {$camel_id} - {$vaccine_name}");
        
        // Set reminder for next due date
        if ($next_due_date) {
            $staff = $pdo->query("SELECT id FROM users WHERE role IN ('admin', 'manager', 'vet')")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($staff as $user) {
                createNotification($pdo, $user['id'], "Vaccination Reminder", "Vaccination for {$vaccine_name} is due on {$next_due_date}.", 'warning');
            }
        }
        
        $_SESSION['message'] = "Vaccination record added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=health&subpage=vaccinations');
    exit;
}

if ($action == 'add_vet_visit') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $visit_date = (isset($_POST['visit_date']) && $_POST['visit_date'] !== '') ? $_POST['visit_date'] : null;
    $vet_name = trim($_POST['vet_name']);
    $purpose = trim($_POST['purpose']);
    $findings = trim($_POST['findings']);
    $recommendations = trim($_POST['recommendations'] ?? '');
    $follow_up_date = $_POST['follow_up_date'] ?? null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO vet_visits (camel_id, visit_date, vet_name, purpose, findings, recommendations, follow_up_date, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $visit_date, $vet_name, $purpose, $findings, $recommendations, $follow_up_date, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_vet_visit', "Added vet visit for camel ID: {$camel_id}");
        
        $_SESSION['message'] = "Vet visit recorded successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=health&subpage=vet-visits');
    exit;
}

// ==================== BREEDING ACTIONS ====================

if ($action == 'add_breeding') {
    $female_camel_id = (isset($_POST['female_camel_id']) && $_POST['female_camel_id'] !== '') ? $_POST['female_camel_id'] : null;
    $male_camel_id = (isset($_POST['male_camel_id']) && $_POST['male_camel_id'] !== '') ? $_POST['male_camel_id'] : null;
    $breeding_date = (isset($_POST['breeding_date']) && $_POST['breeding_date'] !== '') ? $_POST['breeding_date'] : null;
    $notes = trim($_POST['notes'] ?? '');
    
    // Calculate expected delivery date (approximately 13 months gestation)
    $expected_delivery = date('Y-m-d', strtotime($breeding_date . ' + 390 days'));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO breeding_records (female_camel_id, male_camel_id, breeding_date, expected_delivery, status, notes, branch_id) VALUES (?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([$female_camel_id, $male_camel_id, $breeding_date, $expected_delivery, $notes, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_breeding', "Added breeding record - Female: {$female_camel_id}, Male: {$male_camel_id}");
        
        // Set pregnancy tracking reminder
        createNotification($pdo, $_SESSION['user_id'], "Breeding Record Added", "Pregnancy tracking should begin. Expected delivery: {$expected_delivery}.", 'info');
        
        $_SESSION['message'] = "Breeding record added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=breeding&subpage=records');
    exit;
}

if ($action == 'update_breeding') {
    $breeding_id = (isset($_POST['breeding_id']) && $_POST['breeding_id'] !== '') ? $_POST['breeding_id'] : null;
    $actual_delivery = (isset($_POST['actual_delivery']) && $_POST['actual_delivery'] !== '') ? $_POST['actual_delivery'] : null;
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE breeding_records SET actual_delivery = ?, status = ? WHERE id = ?");
        $stmt->execute([$actual_delivery, $status, $breeding_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'update_breeding', "Updated breeding record ID: {$breeding_id} - Status: {$status}");
        
        if ($status == 'completed') {
            // Create calf registration notification
            notifyStaff($pdo, "New Calf Born", "A new calf has been born! Please register the calf in the system.", 'success');
        }
        
        $_SESSION['message'] = "Breeding record updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=breeding&subpage=records');
    exit;
}

if ($action == 'add_pregnancy_check') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $breeding_record_id = $_POST['breeding_record_id'] ?: null;
    $check_date = (isset($_POST['check_date']) && $_POST['check_date'] !== '') ? $_POST['check_date'] : null;
    $trimester = (isset($_POST['trimester']) && $_POST['trimester'] !== '') ? $_POST['trimester'] : null;
    $health_status = trim($_POST['health_status']);
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO pregnancy_tracking (camel_id, breeding_record_id, check_date, trimester, health_status, notes, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $breeding_record_id, $check_date, $trimester, $health_status, $notes, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_pregnancy_check', "Added pregnancy check for camel ID: {$camel_id}");
        
        $_SESSION['message'] = "Pregnancy check added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=breeding&subpage=pregnancy');
    exit;
}

if ($action == 'add_calf') {
    $name = trim($_POST['name']);
    $mother_id = (isset($_POST['mother_id']) && $_POST['mother_id'] !== '') ? $_POST['mother_id'] : null;
    $birth_date = (isset($_POST['birth_date']) && $_POST['birth_date'] !== '') ? $_POST['birth_date'] : null;
    $gender = (isset($_POST['gender']) && $_POST['gender'] !== '') ? $_POST['gender'] : null;
    $birth_weight = $_POST['birth_weight'] ?? null;
    $health_status = trim($_POST['health_status']);
    $tag_id = trim($_POST['tag_id'] ?? '');
    $breeding_id = $_POST['breeding_id'] ?: null;
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO calves (name, mother_id, birth_date, gender, birth_weight, health_status, tag_id, notes, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $mother_id, $birth_date, $gender, $birth_weight, $health_status, $tag_id, $notes, $current_branch_id]);
        $calf_id = $pdo->lastInsertId();
        
        // Register the calf as a new camel
        $camel_stmt = $pdo->prepare("INSERT INTO camels (tag_id, name, breed, age, gender, weight, branch_id, status) VALUES (?, ?, ?, 0, ?, ?, ?, 'active')");
        $camel_stmt->execute([$tag_id, $name, 'Mixed', $gender, $birth_weight ?? 0, $current_branch_id]);
        
        // Update breeding record if provided
        if ($breeding_id) {
            $pdo->prepare("UPDATE breeding_records SET actual_delivery = ?, status = 'completed' WHERE id = ?")->execute([$birth_date, $breeding_id]);
        }
        
        logActivity($pdo, $_SESSION['user_id'], 'add_calf', "Added calf: {$name} from mother ID: {$mother_id}");
        
        notifyStaff($pdo, "New Calf Registered", "New calf {$name} has been registered. Mother ID: {$mother_id}.", 'success');
        
        $_SESSION['message'] = "Calf registered successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=breeding&subpage=calves');
    exit;
}

if ($action == 'add_fertility_report') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $report_date = (isset($_POST['report_date']) && $_POST['report_date'] !== '') ? $_POST['report_date'] : null;
    $fertility_score = (isset($_POST['fertility_score']) && $_POST['fertility_score'] !== '') ? $_POST['fertility_score'] : null;
    $recommended_for_breeding = isset($_POST['recommended_for_breeding']) ? 1 : 0;
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO fertility_reports (camel_id, report_date, fertility_score, notes, recommended_for_breeding, branch_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $report_date, $fertility_score, $notes, $recommended_for_breeding, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_fertility_report', "Added fertility report for camel ID: {$camel_id} - Score: {$fertility_score}");
        
        if ($recommended_for_breeding && $fertility_score >= 7) {
            notifyStaff($pdo, "Breeding Recommendation", "Camel is recommended for breeding with fertility score {$fertility_score}/10.", 'success');
        }
        
        $_SESSION['message'] = "Fertility report added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=breeding&subpage=fertility');
    exit;
}

// ==================== FEED & NUTRITION ACTIONS ====================

if ($action == 'add_feed_type') {
    $name = trim($_POST['name']);
    $category = (isset($_POST['category']) && $_POST['category'] !== '') ? $_POST['category'] : null;
    $unit = (isset($_POST['unit']) && $_POST['unit'] !== '') ? $_POST['unit'] : null;
    $price_per_unit = (isset($_POST['price_per_unit']) && $_POST['price_per_unit'] !== '') ? $_POST['price_per_unit'] : null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO feed_types (name, category, unit, price_per_unit, branch_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $unit, $price_per_unit, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_feed_type', "Added feed type: {$name}");
        
        $_SESSION['message'] = "Feed type added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=feed&subpage=types');
    exit;
}

if ($action == 'add_feed_plan') {
    $name = trim($_POST['name']);
    $camel_type = (isset($_POST['camel_type']) && $_POST['camel_type'] !== '') ? $_POST['camel_type'] : null;
    $feed_type_id = (isset($_POST['feed_type_id']) && $_POST['feed_type_id'] !== '') ? $_POST['feed_type_id'] : null;
    $daily_quantity = (isset($_POST['daily_quantity']) && $_POST['daily_quantity'] !== '') ? $_POST['daily_quantity'] : null;
    $description = trim($_POST['description'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO feed_plans (name, description, camel_type, daily_quantity, feed_type_id, branch_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $camel_type, $daily_quantity, $feed_type_id, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_feed_plan', "Added feed plan: {$name}");
        
        $_SESSION['message'] = "Feed plan added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=feed&subpage=plans');
    exit;
}

if ($action == 'add_feed_consumption') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $feed_type_id = (isset($_POST['feed_type_id']) && $_POST['feed_type_id'] !== '') ? $_POST['feed_type_id'] : null;
    $date = (isset($_POST['date']) && $_POST['date'] !== '') ? $_POST['date'] : null;
    $quantity = (isset($_POST['quantity']) && $_POST['quantity'] !== '') ? $_POST['quantity'] : null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO feed_consumption (camel_id, feed_type_id, date, quantity, branch_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $feed_type_id, $date, $quantity, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_feed_consumption', "Added feed consumption record for camel ID: {$camel_id}");
        
        $_SESSION['message'] = "Feed consumption recorded successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=feed&subpage=consumption');
    exit;
}

if ($action == 'add_feed_supplier') {
    $name = trim($_POST['name']);
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO feed_suppliers (name, contact_person, phone, email, address, branch_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $contact_person, $phone, $email, $address, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_feed_supplier', "Added feed supplier: {$name}");
        
        $_SESSION['message'] = "Supplier added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=feed&subpage=suppliers');
    exit;
}

if ($action == 'add_feed_purchase') {
    $supplier_id = (isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '') ? $_POST['supplier_id'] : null;
    $feed_type_id = (isset($_POST['feed_type_id']) && $_POST['feed_type_id'] !== '') ? $_POST['feed_type_id'] : null;
    $purchase_date = (isset($_POST['purchase_date']) && $_POST['purchase_date'] !== '') ? $_POST['purchase_date'] : null;
    $quantity = (isset($_POST['quantity']) && $_POST['quantity'] !== '') ? $_POST['quantity'] : null;
    $unit_price = (isset($_POST['unit_price']) && $_POST['unit_price'] !== '') ? $_POST['unit_price'] : null;
    $total_amount = $quantity * $unit_price;
    $payment_status = (isset($_POST['payment_status']) && $_POST['payment_status'] !== '') ? $_POST['payment_status'] : null;
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO feed_purchases (supplier_id, feed_type_id, purchase_date, quantity, unit_price, total_amount, payment_status, notes, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$supplier_id, $feed_type_id, $purchase_date, $quantity, $unit_price, $total_amount, $payment_status, $notes, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_feed_purchase', "Added feed purchase - Total: {$total_amount}");
        
        $_SESSION['message'] = "Purchase recorded successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=feed&subpage=purchases');
    exit;
}

// ==================== MILK COLLECTION & PROCESSING ACTIONS ====================

if ($action == 'add_milk_collection') {
    $camel_id = (isset($_POST['camel_id']) && $_POST['camel_id'] !== '') ? $_POST['camel_id'] : null;
    $collection_date = (isset($_POST['collection_date']) && $_POST['collection_date'] !== '') ? $_POST['collection_date'] : null;
    $shift = (isset($_POST['shift']) && $_POST['shift'] !== '') ? $_POST['shift'] : null;
    $quantity_liters = (isset($_POST['quantity_liters']) && $_POST['quantity_liters'] !== '') ? $_POST['quantity_liters'] : null;
    $quality_grade = (isset($_POST['quality_grade']) && $_POST['quality_grade'] !== '') ? $_POST['quality_grade'] : null;
    $collector_id = (isset($_POST['collector_id']) && $_POST['collector_id'] !== '') ? $_POST['collector_id'] : null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO milk_collection (camel_id, collection_date, shift, quantity_liters, quality_grade, collector_id, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$camel_id, $collection_date, $shift, $quantity_liters, $quality_grade, $collector_id, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_milk_collection', "Added milk collection - Camel: {$camel_id}, Qty: {$quantity_liters}L");
        
        // Check if total for the day reaches batch threshold
        $total_daily = $pdo->prepare("SELECT SUM(quantity_liters) FROM milk_collection WHERE collection_date = ? AND quality_grade != 'rejected'");
        $total_daily->execute([$collection_date]);
        $daily_total = $total_daily->fetchColumn() ?: 0;
        
        if ($daily_total >= 50) {
            notifyStaff($pdo, "Batch Ready", "Daily milk collection reached {$daily_total}L. Ready to create a new batch.", 'info');
        }
        
        $_SESSION['message'] = "Milk collection recorded successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=milk-collection&subpage=daily');
    exit;
}

if ($action == 'create_batch') {
    $collection_date = (isset($_POST['collection_date']) && $_POST['collection_date'] !== '') ? $_POST['collection_date'] : null;
    $total_quantity = (isset($_POST['total_quantity']) && $_POST['total_quantity'] !== '') ? $_POST['total_quantity'] : null;
    $batch_number = 'BATCH-' . date('Ymd') . '-' . rand(100, 999);
    $expiry_date = date('Y-m-d', strtotime($collection_date . ' + 5 days'));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO milk_batches (batch_number, collection_date, total_quantity, processing_status, expiry_date, branch_id) VALUES (?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([$batch_number, $collection_date, $total_quantity, $expiry_date, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'create_batch', "Created batch: {$batch_number}");
        
        // Add to warehouse inventory
        $warehouse_stmt = $pdo->prepare("INSERT INTO warehouse_inventory (product_type, batch_id, quantity, unit, storage_location, received_date, expiry_date, branch_id, status) VALUES ('Raw Milk', ?, ?, 'L', 'Cooler A', ?, ?, ?, 'in_stock')");
        $warehouse_stmt->execute([$pdo->lastInsertId(), $total_quantity, $collection_date, $expiry_date, $current_branch_id]);
        
        notifyStaff($pdo, "New Batch Created", "New batch {$batch_number} has been created with {$total_quantity}L of milk.", 'success');
        
        $_SESSION['message'] = "Batch created successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=processing&subpage=batches');
    exit;
}

if ($action == 'pasteurize_batch') {
    $batch_id = (isset($_POST['batch_id']) && $_POST['batch_id'] !== '') ? $_POST['batch_id'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE milk_batches SET pasteurized = TRUE, pasteurization_date = NOW(), pasteurized_by = ?, processing_status = 'processed' WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $batch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'pasteurize_batch', "Pasteurized batch ID: {$batch_id}");
        
        // Update warehouse inventory
        $warehouse_stmt = $pdo->prepare("UPDATE warehouse_inventory SET product_type = 'Pasteurized Milk', storage_location = 'Cooler B' WHERE batch_id = ?");
        $warehouse_stmt->execute([$batch_id]);
        
        notifyStaff($pdo, "Batch Pasteurized", "Batch has been pasteurized and is ready for packaging.", 'info');
        
        $_SESSION['message'] = "Batch pasteurized successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=processing&subpage=pasteurization');
    exit;
}

if ($action == 'approve_quality') {
    $batch_id = (isset($_POST['batch_id']) && $_POST['batch_id'] !== '') ? $_POST['batch_id'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE milk_batches SET quality_approved = TRUE WHERE id = ?");
        $stmt->execute([$batch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'approve_quality', "Approved quality for batch ID: {$batch_id}");
        
        $_SESSION['message'] = "Quality approved successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=processing&subpage=quality');
    exit;
}

if ($action == 'add_temperature_log') {
    $location = (isset($_POST['location']) && $_POST['location'] !== '') ? $_POST['location'] : null;
    $temperature = (isset($_POST['temperature']) && $_POST['temperature'] !== '') ? $_POST['temperature'] : null;
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO temperature_logs (location, log_date, temperature, recorded_by, notes, branch_id) VALUES (?, NOW(), ?, ?, ?, ?)");
        $stmt->execute([$location, $temperature, $_SESSION['user_id'], $notes, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_temperature_log', "Recorded temperature at {$location}: {$temperature}°C");
        
        // Check temperature alert
        if (($location == 'Cooler A' || $location == 'Cooler B') && $temperature > 4) {
            notifyStaff($pdo, "Temperature Alert", "Temperature in {$location} is {$temperature}°C. Exceeds safe limit of 4°C.", 'danger');
        }
        
        $_SESSION['message'] = "Temperature recorded successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=warehouse&subpage=temperature');
    exit;
}

if ($action == 'dispose_inventory') {
    $inventory_id = (isset($_POST['inventory_id']) && $_POST['inventory_id'] !== '') ? $_POST['inventory_id'] : null;
    $reason = trim($_POST['reason'] ?? '');
    
    try {
        $stmt = $pdo->prepare("UPDATE warehouse_inventory SET status = 'disposed' WHERE id = ?");
        $stmt->execute([$inventory_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'dispose_inventory', "Disposed inventory ID: {$inventory_id} - Reason: {$reason}");
        
        $_SESSION['message'] = "Inventory item disposed successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=warehouse&subpage=expiry');
    exit;
}

if ($action == 'add_manual_inventory') {
    $product_type = trim($_POST['product_type']);
    $quantity = floatval($_POST['quantity']);
    $unit = trim($_POST['unit']);
    $storage_location = trim($_POST['storage_location']);
    $received_date = trim($_POST['received_date']);
    $expiry_date = trim($_POST['expiry_date']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO warehouse_inventory (product_type, quantity, unit, storage_location, received_date, expiry_date, branch_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'in_stock')");
        $stmt->execute([$product_type, $quantity, $unit, $storage_location, $received_date, $expiry_date, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_manual_inventory', "Manually added {$quantity}{$unit} of {$product_type} to inventory");
        
        $_SESSION['message'] = "Inventory added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=warehouse&subpage=inventory');
    exit;
}

// ==================== PRODUCT ACTIONS ====================

if ($action == 'add_product') {
    $name = trim($_POST['name']);
    $category = (isset($_POST['category']) && $_POST['category'] !== '') ? $_POST['category'] : null;
    $price = (isset($_POST['price']) && $_POST['price'] !== '') ? $_POST['price'] : null;
    $stock_quantity = (isset($_POST['stock_quantity']) && $_POST['stock_quantity'] !== '') ? $_POST['stock_quantity'] : null;
    $unit = (isset($_POST['unit']) && $_POST['unit'] !== '') ? $_POST['unit'] : null;
    $description = trim($_POST['description'] ?? '');
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    
    $image = 'default-product.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploaded = uploadFile($_FILES['image'], UPLOAD_PATH);
        if ($uploaded) {
            $image = $uploaded;
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock_quantity, unit, status, image, description, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $stock_quantity, $unit, $status, $image, $description, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_product', "Added product: {$name}");
        
        $_SESSION['message'] = "Product added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=products&subpage=list');
    exit;
}

if ($action == 'add_customer') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, city, address, notes, status, branch_id) VALUES (?, ?, ?, ?, ?, ?, 'active', ?)");
        $stmt->execute([$name, $phone, $email, $city, $address, $notes, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_customer', "Added customer: {$name}");
        
        $_SESSION['message'] = "Customer added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=customers&subpage=list');
    exit;
}

if ($action == 'update_product') {
    $product_id = (isset($_POST['product_id']) && $_POST['product_id'] !== '') ? $_POST['product_id'] : null;
    $name = trim($_POST['name']);
    $category = (isset($_POST['category']) && $_POST['category'] !== '') ? $_POST['category'] : null;
    $price = (isset($_POST['price']) && $_POST['price'] !== '') ? $_POST['price'] : null;
    $stock_quantity = (isset($_POST['stock_quantity']) && $_POST['stock_quantity'] !== '') ? $_POST['stock_quantity'] : null;
    $unit = (isset($_POST['unit']) && $_POST['unit'] !== '') ? $_POST['unit'] : null;
    $description = trim($_POST['description'] ?? '');
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploaded = uploadFile($_FILES['image'], UPLOAD_PATH);
        if ($uploaded) {
            $image = $uploaded;
        }
    }
    
    try {
        if ($image) {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock_quantity = ?, unit = ?, description = ?, status = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $category, $price, $stock_quantity, $unit, $description, $status, $image, $product_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock_quantity = ?, unit = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $category, $price, $stock_quantity, $unit, $description, $status, $product_id]);
        }
        
        logActivity($pdo, $_SESSION['user_id'], 'update_product', "Updated product ID: {$product_id}");
        
        // Check for low stock alert
        if ($stock_quantity < 10 && $status == 'active') {
            notifyStaff($pdo, "Low Stock Alert", "Product {$name} has low stock: {$stock_quantity} {$unit}.", 'warning');
        }
        
        $_SESSION['message'] = "Product updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=products&subpage=list');
    exit;
}

if ($action == 'delete_product') {
    $product_id = (isset($_POST['product_id']) && $_POST['product_id'] !== '') ? $_POST['product_id'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$product_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'delete_product', "Deactivated product ID: {$product_id}");
        
        $_SESSION['message'] = "Product deactivated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=products&subpage=list');
    exit;
}

// ==================== ORDER MANAGEMENT ACTIONS ====================

if ($action == 'update_order_status') {
    $order_id = (isset($_POST['order_id']) && $_POST['order_id'] !== '') ? $_POST['order_id'] : null;
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    $notes = $_POST['notes'] ?? '';
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        
        // Update timestamp fields
        $timestamp_field = '';
        switch ($status) {
            case 'confirmed': $timestamp_field = 'confirmed_at'; break;
            case 'processing': $timestamp_field = 'processed_at'; break;
            case 'packed': $timestamp_field = 'packed_at'; break;
            case 'shipped': $timestamp_field = 'shipped_at'; break;
            case 'delivered': $timestamp_field = 'delivered_at'; break;
            case 'cancelled': $timestamp_field = 'cancelled_at'; break;
        }
        if ($timestamp_field) {
            $pdo->prepare("UPDATE orders SET {$timestamp_field} = NOW() WHERE id = ?")->execute([$order_id]);
        }
        
        // If status is updated to confirmed, auto-update payment status to paid
        if ($status == 'confirmed') {
            $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?")->execute([$order_id]);
            $pdo->prepare("UPDATE invoices SET status = 'paid', payment_date = NOW() WHERE order_id = ?")->execute([$order_id]);
        }
        
        logOrderStatus($pdo, $order_id, $status, $_SESSION['user_id'], $notes);
        logActivity($pdo, $_SESSION['user_id'], 'update_order_status', "Updated order ID: {$order_id} to status: {$status}");
        
        // Notify customer
        $order_info = $pdo->prepare("SELECT customer_id FROM orders WHERE id = ?");
        $order_info->execute([$order_id]);
        $order = $order_info->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            $customer_stmt = $pdo->prepare("SELECT user_id FROM customers WHERE id = ?");
            $customer_stmt->execute([$order['customer_id']]);
            $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer) {
                createNotification($pdo, $customer['user_id'], "Order Status Updated", "Your order has been {$status}.", $status == 'delivered' ? 'success' : 'info');
            }
        }
        
        $_SESSION['message'] = "Order status updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    $redirect = $_POST['redirect'] ?? '?page=orders&subpage=list';
    header('Location: ' . $redirect);
    exit;
}

if ($action == 'accept_order') {
    $order_id = (isset($_POST['order_id']) && $_POST['order_id'] !== '') ? $_POST['order_id'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed', payment_status = 'paid', confirmed_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Auto-update invoice status to paid
        $pdo->prepare("UPDATE invoices SET status = 'paid', payment_date = NOW() WHERE order_id = ?")->execute([$order_id]);
        
        logOrderStatus($pdo, $order_id, 'confirmed', $_SESSION['user_id'], 'Order accepted by staff');
        logActivity($pdo, $_SESSION['user_id'], 'accept_order', "Accepted order ID: {$order_id}");
        
        // Notify customer
        $order_info = $pdo->prepare("SELECT o.customer_id, c.user_id FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
        $order_info->execute([$order_id]);
        $order = $order_info->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['user_id']) {
            createNotification($pdo, $order['user_id'], "Order Accepted", "Your order has been accepted and is being processed.", 'success');
        }
        
        $_SESSION['message'] = "Order accepted successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=orders&subpage=view&id=' . $order_id);
    exit;
}

if ($action == 'assign_delivery') {
    $order_id = (isset($_POST['order_id']) && $_POST['order_id'] !== '') ? $_POST['order_id'] : null;
    $driver_id = (isset($_POST['driver_id']) && $_POST['driver_id'] !== '') ? $_POST['driver_id'] : null;
    $delivery_date = (isset($_POST['delivery_date']) && $_POST['delivery_date'] !== '') ? $_POST['delivery_date'] : date('Y-m-d', strtotime('+1 day'));
    
    try {
        // Update order status to shipped (was processing before, but shipped makes more sense when assigned)
        $pdo->prepare("UPDATE orders SET status = 'shipped', shipped_at = NOW() WHERE id = ?")->execute([$order_id]);
        
        // Create or update delivery
        $delivery_check = $pdo->prepare("SELECT id FROM deliveries WHERE order_id = ?");
        $delivery_check->execute([$order_id]);
        $existing_delivery = $delivery_check->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_delivery) {
            $pdo->prepare("UPDATE deliveries SET driver_id = ?, delivery_date = ?, status = 'assigned', assigned_at = NOW() WHERE id = ?")
                ->execute([$driver_id, $delivery_date, $existing_delivery['id']]);
        } else {
            $order_info = $pdo->prepare("SELECT delivery_address, delivery_phone, customer_id, order_number FROM orders WHERE id = ?");
            $order_info->execute([$order_id]);
            $order = $order_info->fetch(PDO::FETCH_ASSOC);
            
            $customer_info = $pdo->prepare("SELECT name FROM customers WHERE id = ?");
            $customer_info->execute([$order['customer_id']]);
            $customer = $customer_info->fetch(PDO::FETCH_ASSOC);
            
            $pdo->prepare("INSERT INTO deliveries (order_id, driver_id, delivery_date, status, delivery_address, recipient_name, recipient_phone, assigned_at) VALUES (?, ?, ?, 'assigned', ?, ?, ?, NOW())")
                ->execute([$order_id, $driver_id, $delivery_date, $order['delivery_address'], $customer['name'], $order['delivery_phone']]);
        }
        
        logOrderStatus($pdo, $order_id, 'shipped', $_SESSION['user_id'], 'Delivery assigned to driver');
        logActivity($pdo, $_SESSION['user_id'], 'assign_delivery', "Assigned delivery for order ID: {$order_id} to driver ID: {$driver_id}");
        
        // Notify customer
        $order_info = $pdo->prepare("SELECT o.customer_id, o.order_number, c.user_id FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
        $order_info->execute([$order_id]);
        $order = $order_info->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['user_id']) {
            createNotification($pdo, $order['user_id'], "Delivery Assigned", "Your order has been assigned for delivery.", 'info');
        }
        
        // Notify driver (THIS WAS MISSING!)
        if ($order) {
            createNotification($pdo, $driver_id, "New Delivery Assignment", "You have been assigned a new delivery for order #{$order['order_number']}.", 'info');
        }
        
        $_SESSION['message'] = "Delivery assigned successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirect back to the page they came from if available, otherwise view order
    $redirect = $_SERVER['HTTP_REFERER'] ?? '?page=orders&subpage=view&id=' . $order_id;
    header('Location: ' . $redirect);
    exit;
}

if ($action == 'mark_delivered') {
    $order_id = (isset($_POST['order_id']) && $_POST['order_id'] !== '') ? $_POST['order_id'] : null;
    
    try {
        // Update order and delivery status
        $pdo->prepare("UPDATE orders SET status = 'delivered', delivered_at = NOW() WHERE id = ?")->execute([$order_id]);
        $pdo->prepare("UPDATE deliveries SET status = 'delivered', delivered_at = NOW() WHERE order_id = ?")->execute([$order_id]);
        
        // Update payment status
        $pdo->prepare("UPDATE payments SET status = 'completed' WHERE order_id = ?")->execute([$order_id]);
        
        logOrderStatus($pdo, $order_id, 'delivered', $_SESSION['user_id'], 'Order marked as delivered');
        logActivity($pdo, $_SESSION['user_id'], 'mark_delivered', "Marked order ID: {$order_id} as delivered");
        
        // Notify customer
        $order_info = $pdo->prepare("SELECT o.customer_id, c.user_id FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
        $order_info->execute([$order_id]);
        $order = $order_info->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['user_id']) {
            createNotification($pdo, $order['user_id'], "Order Delivered", "Your order has been successfully delivered. Thank you for shopping with us!", 'success');
        }
        
        $_SESSION['message'] = "Order marked as delivered!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=orders&subpage=view&id=' . $order_id);
    exit;
}

if ($action == 'create_order') {
    $customer_id = (isset($_POST['customer_id']) && $_POST['customer_id'] !== '') ? $_POST['customer_id'] : null;
    $delivery_address = trim($_POST['delivery_address']);
    $delivery_phone = trim($_POST['delivery_phone']);
    $payment_method = (isset($_POST['payment_method']) && $_POST['payment_method'] !== '') ? $_POST['payment_method'] : null;
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $grand_total = $_POST['grand_total'] ?? 0;

    if (empty($product_ids) || empty($quantities)) {
        $_SESSION['message'] = "Please add at least one product to the order.";
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=orders&subpage=create');
        exit;
    }

    try {
        $pdo->beginTransaction();

        $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, order_date, total_amount, status, payment_status, payment_method, delivery_address, delivery_phone, branch_id) VALUES (?, ?, NOW(), ?, 'confirmed', 'paid', ?, ?, ?, ?)");
        $stmt->execute([$order_number, $customer_id, $grand_total, $payment_method, $delivery_address, $delivery_phone, $current_branch_id]);
        $order_id = $pdo->lastInsertId();

        $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stock_stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

        foreach ($product_ids as $index => $product_id) {
            $qty = max(1, intval($quantities[$index]));
            if ($qty <= 0) continue;

            $product_info = $pdo->prepare("SELECT price, stock_quantity FROM products WHERE id = ? AND status='active'");
            $product_info->execute([$product_id]);
            $product = $product_info->fetch(PDO::FETCH_ASSOC);
            if (!$product) continue;

            if ($product['stock_quantity'] < $qty) {
                throw new Exception("Insufficient stock for product ID: {$product_id}");
            }

            $item_total = $product['price'] * $qty;
            $item_stmt->execute([$order_id, $product_id, $qty, $product['price']]);
            $stock_stmt->execute([$qty, $product_id]);
        }

        $pdo->commit();

        // Log and notify
        logActivity($pdo, $_SESSION['user_id'], 'create_order', "Created order {$order_number} for customer ID {$customer_id}");
        
        // Notify Super Admin, Admin, Manager, and Staff about new order
        notifyStaff($pdo, 
            "New Order Created", 
            "Order #{$order_number} has been created. Total: $" . number_format($grand_total, 2),
            'info',
            $current_branch_id
        );
        
        $_SESSION['message'] = "Order created successfully!";
        $_SESSION['message_type'] = "success";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }

    header('Location: ?page=orders&subpage=list');
    exit;
}
if ($action == 'pos_sale') {
    $pos_data = json_decode($_POST['pos_data'], true);
    
    if (!$pos_data) {
        $_SESSION['message'] = "Invalid POS data.";
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=sales&subpage=pos');
        exit;
    }
    
    $customer_id = intval($pos_data['customer_id'] ?? 0);
    $payment_method = $pos_data['payment_method'] ?? 'cash';
    $subtotal = floatval($pos_data['subtotal'] ?? 0);
    $discount = floatval($pos_data['discount'] ?? 0);
    $total_amount = floatval($pos_data['total_amount'] ?? 0);
    $amount_paid = floatval($pos_data['amount_paid'] ?? 0);
    $change_amount = max(0, $amount_paid - $total_amount);
    $items = $pos_data['items'] ?? [];
    
    $walkin_name = $pos_data['walkin_name'] ?? 'Walk-in Customer';
    $walkin_phone = $pos_data['walkin_phone'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        // Save to pos_walkin_sales table
        $sale_number = 'POS-' . date('Ymd') . '-' . rand(1000, 9999);
        $stmt_pos = $pdo->prepare("INSERT INTO pos_walkin_sales (sale_number, branch_id, cashier_id, customer_name, customer_phone, payment_method, subtotal, discount, total_amount, amount_paid, change_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_pos->execute([$sale_number, $current_branch_id, $_SESSION['user_id'], $walkin_name, $walkin_phone, $payment_method, $subtotal, $discount, $total_amount, $amount_paid, $change_amount]);
        $sale_id = $pdo->lastInsertId();
        
        $item_stmt_pos = $pdo->prepare("INSERT INTO pos_walkin_items (sale_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        
        // Maintain backwards compatibility with Orders/Customers for reports
        if ($customer_id == 0) {
            $guest = $pdo->query("SELECT id FROM customers WHERE name = 'Walk-in Customer' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if ($guest) {
                $customer_id = $guest['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO customers (name, phone, address, branch_id) VALUES ('Walk-in Customer', 'N/A', 'Store POS', ?)");
                $stmt->execute([$current_branch_id]);
                $customer_id = $pdo->lastInsertId();
            }
        }
        
        // 2. Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, order_date, total_amount, status, payment_status, payment_method, branch_id, delivered_at) VALUES (?, ?, NOW(), ?, 'delivered', 'paid', ?, ?, NOW())");
        $stmt->execute([$sale_number, $customer_id, $total_amount, $payment_method, $current_branch_id]);
        $order_id = $pdo->lastInsertId();
        
        // 3. Process Items
        $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stock_stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        
        foreach ($items as $item) {
            $product_id = intval($item['id']);
            $qty = intval($item['qty']);
            $price = floatval($item['price']);
            
            $item_total = $qty * $price;
            $item_stmt_pos->execute([$sale_id, $product_id, $item['name'], $qty, $price, $item_total]);
            
            $item_stmt->execute([$order_id, $product_id, $qty, $price]);
            $stock_stmt->execute([$qty, $product_id]);
        }
        
        // 4. Create Payment
        $stmt = $pdo->prepare("INSERT INTO payments (order_id, amount, payment_date, payment_method, status, branch_id) VALUES (?, ?, NOW(), ?, 'completed', ?)");
        $stmt->execute([$order_id, $total_amount, $payment_method, $current_branch_id]);
        
        // 5. Create Invoice
        createInvoice($pdo, $order_id, $customer_id, $subtotal, 0, 0, $total_amount);
        
        $pdo->commit();
        
        logActivity($pdo, $_SESSION['user_id'], 'pos_sale', "Completed POS sale #{$sale_number}");
        
        // Notify Super Admin, Admin, Manager, and Staff about new POS sale
        notifyStaff($pdo, 
            "New POS Sale", 
            "POS Sale #{$sale_number} completed. Total: $" . number_format($total_amount, 2),
            'success',
            $current_branch_id
        );
        
        $_SESSION['message'] = "POS Sale #{$sale_number} completed successfully!";
        $_SESSION['message_type'] = "success";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "POS Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    header('Location: ?page=sales&subpage=pos');
    exit;
}

if ($action == 'approve_cancellation') {
    $order_id = (isset($_POST['order_id']) && $_POST['order_id'] !== '') ? $_POST['order_id'] : null;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Update order status
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Restore product stock
        $items_stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $items_stmt->execute([$order_id]);
        $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?")->execute([$item['quantity'], $item['product_id']]);
        }
        
        logOrderStatus($pdo, $order_id, 'cancelled', $_SESSION['user_id'], 'Cancellation approved');
        logActivity($pdo, $_SESSION['user_id'], 'approve_cancellation', "Approved cancellation for order ID: {$order_id}");
        
        // Notify customer
        $order_info = $pdo->prepare("SELECT o.customer_id, c.user_id FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
        $order_info->execute([$order_id]);
        $order = $order_info->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['user_id']) {
            createNotification($pdo, $order['user_id'], "Order Cancelled", "Your order cancellation has been approved.", 'warning');
        }
        
        $pdo->commit();
        
        $_SESSION['message'] = "Cancellation approved successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=orders&subpage=cancellations');
    exit;
}

if ($action == 'reject_cancellation') {
    $order_id = (isset($_POST['order_id']) && $_POST['order_id'] !== '') ? $_POST['order_id'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ? AND status = 'cancellation_requested'");
        $stmt->execute([$order_id]);
        
        logOrderStatus($pdo, $order_id, 'confirmed', $_SESSION['user_id'], 'Cancellation rejected');
        logActivity($pdo, $_SESSION['user_id'], 'reject_cancellation', "Rejected cancellation for order ID: {$order_id}");
        
        // Notify customer
        $order_info = $pdo->prepare("SELECT o.customer_id, c.user_id FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
        $order_info->execute([$order_id]);
        $order = $order_info->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['user_id']) {
            createNotification($pdo, $order['user_id'], "Cancellation Rejected", "Your cancellation request has been rejected. Order is being processed.", 'danger');
        }
        
        $_SESSION['message'] = "Cancellation rejected!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=orders&subpage=cancellations');
    exit;
}

// ==================== DELIVERY ACTIONS ====================

if ($action == 'add_delivery_zone') {
    $name = trim($_POST['name']);
    $city = trim($_POST['city']);
    $delivery_fee = (isset($_POST['delivery_fee']) && $_POST['delivery_fee'] !== '') ? $_POST['delivery_fee'] : null;
    $estimated_time = trim($_POST['estimated_time']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO delivery_zones (name, city, delivery_fee, estimated_time, branch_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $city, $delivery_fee, $estimated_time, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_delivery_zone', "Added delivery zone: {$name}");
        
        $_SESSION['message'] = "Delivery zone added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=delivery&subpage=zones');
    exit;
}



if ($action == 'accept_delivery_assignment') {
    $delivery_id = (isset($_POST['delivery_id']) && $_POST['delivery_id'] !== '') ? $_POST['delivery_id'] : null;
    
    try {
        // Verify this delivery is assigned to the current user (driver)
        $stmt = $pdo->prepare("SELECT d.*, o.order_number FROM deliveries d JOIN orders o ON d.order_id = o.id WHERE d.id = ? AND d.driver_id = ? AND d.status = 'assigned'");
        $stmt->execute([$delivery_id, $_SESSION['user_id']]);
        $delivery = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$delivery) {
            $_SESSION['message'] = "Delivery not found or not assigned to you.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=delivery&subpage=tracking');
            exit;
        }
        
        // Update delivery status to in_transit
        $pdo->prepare("UPDATE deliveries SET status = 'in_transit', accepted_at = NOW() WHERE id = ?")->execute([$delivery_id]);
        
        // Update order status to shipped
        $pdo->prepare("UPDATE orders SET status = 'shipped' WHERE id = ?")->execute([$delivery['order_id']]);
        
        logActivity($pdo, $_SESSION['user_id'], 'accept_delivery_assignment', "Accepted delivery assignment for order #{$delivery['order_number']}");
        
        // Notify admin/staff that driver accepted
        notifyStaff($pdo, 
            "Driver Accepted Assignment", 
            "Driver has accepted delivery for order #{$delivery['order_number']}",
            'success',
            $current_branch_id
        );
        
        $_SESSION['message'] = "Delivery assignment accepted!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=delivery&subpage=tracking');
    exit;
}

if ($action == 'cancel_delivery_assignment') {
    $delivery_id = (isset($_POST['delivery_id']) && $_POST['delivery_id'] !== '') ? $_POST['delivery_id'] : null;
    $reason = trim($_POST['reason'] ?? '');
    
    try {
        // Verify this delivery is assigned to the current user (driver)
        $stmt = $pdo->prepare("SELECT d.*, o.order_number FROM deliveries d JOIN orders o ON d.order_id = o.id WHERE d.id = ? AND d.driver_id = ? AND d.status = 'assigned'");
        $stmt->execute([$delivery_id, $_SESSION['user_id']]);
        $delivery = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$delivery) {
            $_SESSION['message'] = "Delivery not found or not assigned to you.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=delivery&subpage=tracking');
            exit;
        }
        
        // Update delivery status to cancelled
        $pdo->prepare("UPDATE deliveries SET status = 'cancelled', cancelled_at = NOW(), cancellation_reason = ? WHERE id = ?")->execute([$reason, $delivery_id]);
        
        // Update order status back to packed
        $pdo->prepare("UPDATE orders SET status = 'packed' WHERE id = ?")->execute([$delivery['order_id']]);
        
        logActivity($pdo, $_SESSION['user_id'], 'cancel_delivery_assignment', "Cancelled delivery assignment for order #{$delivery['order_number']}. Reason: {$reason}");
        
        // Notify admin/staff that driver cancelled
        notifyStaff($pdo, 
            "Driver Cancelled Assignment", 
            "Driver cancelled delivery for order #{$delivery['order_number']}. Reason: {$reason}",
            'warning',
            $current_branch_id
        );
        
        $_SESSION['message'] = "Delivery assignment cancelled!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=delivery&subpage=tracking');
    exit;
}

if ($action == 'mark_delivery_completed') {
    $delivery_id = (isset($_POST['delivery_id']) && $_POST['delivery_id'] !== '') ? $_POST['delivery_id'] : null;
    
    try {
        // Verify this delivery is assigned to the current user (driver)
        $stmt = $pdo->prepare("SELECT d.*, o.order_number, o.customer_id FROM deliveries d JOIN orders o ON d.order_id = o.id WHERE d.id = ? AND d.driver_id = ? AND d.status = 'in_transit'");
        $stmt->execute([$delivery_id, $_SESSION['user_id']]);
        $delivery = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$delivery) {
            $_SESSION['message'] = "Delivery not found or not in transit.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=delivery&subpage=tracking');
            exit;
        }
        
        $pdo->beginTransaction();
        
        // Update delivery status to delivered
        $pdo->prepare("UPDATE deliveries SET status = 'delivered', delivered_at = NOW() WHERE id = ?")->execute([$delivery_id]);
        
        // Update order status to delivered
        $pdo->prepare("UPDATE orders SET status = 'delivered', delivered_at = NOW() WHERE id = ?")->execute([$delivery['order_id']]);
        
        // Update driver performance
        $perf_stmt = $pdo->prepare("INSERT INTO driver_performance (driver_id, delivery_date, deliveries_count, on_time_deliveries, rating) VALUES (?, CURDATE(), 1, 1, 5) ON DUPLICATE KEY UPDATE deliveries_count = deliveries_count + 1, on_time_deliveries = on_time_deliveries + 1");
        $perf_stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        
        logActivity($pdo, $_SESSION['user_id'], 'mark_delivery_completed', "Marked delivery as completed for order #{$delivery['order_number']}");
        
        // Notify customer about delivery
        if ($delivery['customer_id']) {
            $customer_stmt = $pdo->prepare("SELECT user_id FROM customers WHERE id = ?");
            $customer_stmt->execute([$delivery['customer_id']]);
            $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);
            if ($customer && $customer['user_id']) {
                createNotification($pdo, $customer['user_id'], "Order Delivered", "Your order #{$delivery['order_number']} has been successfully delivered. Thank you for shopping with us!", 'success');
            }
        }
        
        // Notify admin/staff about completion
        notifyStaff($pdo, 
            "Delivery Completed", 
            "Driver completed delivery for order #{$delivery['order_number']}",
            'success',
            $current_branch_id
        );
        
        $_SESSION['message'] = "Delivery marked as completed!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=delivery&subpage=tracking');
    exit;
}

if ($action == 'update_delivery_status') {
    $delivery_id = (isset($_POST['delivery_id']) && $_POST['delivery_id'] !== '') ? $_POST['delivery_id'] : null;
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE deliveries SET status = ? WHERE id = ?");
        $stmt->execute([$status, $delivery_id]);
        
        if ($status == 'delivered') {
            $pdo->prepare("UPDATE deliveries SET delivered_at = NOW() WHERE id = ?")->execute([$delivery_id]);
            
            // Update order status to delivered
            $delivery_stmt = $pdo->prepare("SELECT order_id FROM deliveries WHERE id = ?");
            $delivery_stmt->execute([$delivery_id]);
            $delivery = $delivery_stmt->fetch(PDO::FETCH_ASSOC);
            if ($delivery) {
                $pdo->prepare("UPDATE orders SET status = 'delivered', delivered_at = NOW() WHERE id = ?")->execute([$delivery['order_id']]);
                
                // Update driver performance
                $driver_stmt = $pdo->prepare("SELECT driver_id FROM deliveries WHERE id = ?");
                $driver_stmt->execute([$delivery_id]);
                $driver = $driver_stmt->fetch(PDO::FETCH_ASSOC);
                
                $perf_stmt = $pdo->prepare("INSERT INTO driver_performance (driver_id, delivery_date, deliveries_count, on_time_deliveries, rating) VALUES (?, CURDATE(), 1, 1, 5) ON DUPLICATE KEY UPDATE deliveries_count = deliveries_count + 1, on_time_deliveries = on_time_deliveries + 1");
                $perf_stmt->execute([$driver['driver_id']]);
            }
        }
        
        logActivity($pdo, $_SESSION['user_id'], 'update_delivery_status', "Updated delivery ID: {$delivery_id} to status: {$status}");
        
        $_SESSION['message'] = "Delivery status updated!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=delivery&subpage=tracking');
    exit;
}

if ($action == 'upload_proof') {
    $delivery_id = (isset($_POST['delivery_id']) && $_POST['delivery_id'] !== '') ? $_POST['delivery_id'] : null;
    
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == UPLOAD_ERR_OK) {
        $filename = uploadFile($_FILES['proof_image'], PROOF_PATH);
        
        if ($filename) {
            try {
                $stmt = $pdo->prepare("INSERT INTO delivery_proofs (delivery_id, image_path) VALUES (?, ?)");
                $stmt->execute([$delivery_id, $filename]);
                
                // Update delivery proof path
                $pdo->prepare("UPDATE deliveries SET proof_of_delivery = ? WHERE id = ?")->execute([$filename, $delivery_id]);
                
                logActivity($pdo, $_SESSION['user_id'], 'upload_proof', "Uploaded proof for delivery ID: {$delivery_id}");
                
                $_SESSION['message'] = "Proof uploaded successfully!";
                $_SESSION['message_type'] = "success";
            } catch (PDOException $e) {
                $_SESSION['message'] = "Error: " . $e->getMessage();
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Failed to upload image. Please try again.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Please select an image to upload.";
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=delivery&subpage=proof');
    exit;
}

// ==================== SUBSCRIPTION ACTIONS ====================

if ($action == 'add_subscription_plan') {
    $name = trim($_POST['name']);
    $plan_type = (isset($_POST['plan_type']) && $_POST['plan_type'] !== '') ? $_POST['plan_type'] : null;
    $quantity_per_day = (isset($_POST['quantity_per_day']) && $_POST['quantity_per_day'] !== '') ? $_POST['quantity_per_day'] : null;
    $price = (isset($_POST['price']) && $_POST['price'] !== '') ? $_POST['price'] : null;
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO subscription_plans (name, plan_type, quantity_per_day, price, status, branch_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $plan_type, $quantity_per_day, $price, $status, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_subscription_plan', "Added subscription plan: {$name}");
        
        $_SESSION['message'] = "Subscription plan added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=subscriptions&subpage=plans');
    exit;
}

if ($action == 'add_subscription' || $action == 'create_subscription') {
    $customer_id = (isset($_POST['customer_id']) && $_POST['customer_id'] !== '') ? $_POST['customer_id'] : null;
    $plan_id = (isset($_POST['plan_id']) && $_POST['plan_id'] !== '') ? $_POST['plan_id'] : null;
    $start_date = (isset($_POST['start_date']) && $_POST['start_date'] !== '') ? $_POST['start_date'] : null;
    $end_date = (isset($_POST['end_date']) && $_POST['end_date'] !== '') ? $_POST['end_date'] : null;
    
    if (!$end_date && $start_date) {
        $end_date = date('Y-m-d', strtotime($start_date . ' + 30 days'));
    }
    
    $delivery_address = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
    $delivery_time = (isset($_POST['delivery_time']) && $_POST['delivery_time'] !== '') ? $_POST['delivery_time'] : '08:00:00';
    
    try {
        $pdo->beginTransaction();

        // If plan_id is not provided but plan_name is, find or create the plan
        if (!$plan_id && isset($_POST['plan_name']) && $_POST['plan_name'] !== '') {
            $plan_name = trim($_POST['plan_name']);
            $price = floatval($_POST['price'] ?? 0);
            
            // Check if plan already exists for this branch (or globally)
            $plan_stmt = $pdo->prepare("SELECT id FROM subscription_plans WHERE name = ? AND (branch_id = ? OR branch_id IS NULL) LIMIT 1");
            $plan_stmt->execute([$plan_name, $current_branch_id]);
            $existing_plan = $plan_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_plan) {
                $plan_id = $existing_plan['id'];
            } else {
                // Create a plan dynamically
                $create_plan_stmt = $pdo->prepare("INSERT INTO subscription_plans (name, plan_type, quantity_per_day, price, status, branch_id) VALUES (?, 'daily', 1.00, ?, 'active', ?)");
                $create_plan_stmt->execute([$plan_name, $price, $current_branch_id]);
                $plan_id = $pdo->lastInsertId();
            }
        }
        
        // Fetch customer address if not provided
        if (empty($delivery_address) && $customer_id) {
            $cust_stmt = $pdo->prepare("SELECT address FROM customers WHERE id = ?");
            $cust_stmt->execute([$customer_id]);
            $cust_address = $cust_stmt->fetchColumn();
            if ($cust_address) {
                $delivery_address = trim($cust_address);
            } else {
                $delivery_address = 'Address not provided';
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO customer_subscriptions (customer_id, plan_id, start_date, end_date, status, delivery_address, delivery_time, branch_id) VALUES (?, ?, ?, ?, 'active', ?, ?, ?)");
        $stmt->execute([$customer_id, $plan_id, $start_date, $end_date, $delivery_address, $delivery_time, $current_branch_id]);
        $sub_id = $pdo->lastInsertId();
        
        // Generate an invoice for this new subscription
        $price = 0;
        if ($plan_id) {
            $price_stmt = $pdo->prepare("SELECT price FROM subscription_plans WHERE id = ?");
            $price_stmt->execute([$plan_id]);
            $price = floatval($price_stmt->fetchColumn() ?: 0);
        }
        
        $invoice_number = 'SUB-INV-' . date('Ymd') . '-' . rand(1000, 9999);
        $inv_stmt = $pdo->prepare("INSERT INTO subscription_invoices (subscription_id, invoice_number, invoice_date, amount, status) VALUES (?, ?, CURDATE(), ?, 'unpaid')");
        $inv_stmt->execute([$sub_id, $invoice_number, $price]);

        $pdo->commit();
        
        logActivity($pdo, $_SESSION['user_id'], 'add_subscription', "Created subscription ID: {$sub_id} for customer ID: {$customer_id}");
        
        $_SESSION['message'] = "Subscription created successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=subscriptions&subpage=active');
    exit;
}

if ($action == 'update_subscription_status') {
    $subscription_id = (isset($_POST['subscription_id']) && $_POST['subscription_id'] !== '') ? $_POST['subscription_id'] : null;
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE customer_subscriptions SET status = ? WHERE id = ?");
        $stmt->execute([$status, $subscription_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'update_subscription_status', "Updated subscription ID: {$subscription_id} status to {$status}");
        
        $_SESSION['message'] = "Subscription status updated to " . ucfirst($status) . " successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    $redirect = $_POST['redirect_to'] ?? '?page=subscriptions&subpage=active';
    header("Location: " . $redirect);
    exit;
}

if ($action == 'generate_subscription_invoice') {
    $subscription_id = (isset($_POST['subscription_id']) && $_POST['subscription_id'] !== '') ? $_POST['subscription_id'] : null;
    
    try {
        $stmt = $pdo->prepare("SELECT cs.*, sp.price FROM customer_subscriptions cs JOIN subscription_plans sp ON cs.plan_id = sp.id WHERE cs.id = ?");
        $stmt->execute([$subscription_id]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sub) {
            throw new Exception("Subscription not found.");
        }
        
        $price = floatval($sub['price'] ?: 0);
        $invoice_number = 'SUB-INV-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $inv_stmt = $pdo->prepare("INSERT INTO subscription_invoices (subscription_id, invoice_number, invoice_date, amount, status) VALUES (?, ?, CURDATE(), ?, 'unpaid')");
        $inv_stmt->execute([$subscription_id, $invoice_number, $price]);
        
        logActivity($pdo, $_SESSION['user_id'], 'generate_subscription_invoice', "Generated invoice {$invoice_number} for subscription ID: {$subscription_id}");
        
        $_SESSION['message'] = "Invoice {$invoice_number} generated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    $redirect = $_POST['redirect_to'] ?? '?page=subscriptions&subpage=active';
    header("Location: " . $redirect);
    exit;
}

if ($action == 'pay_subscription_invoice') {
    $invoice_id = (isset($_POST['invoice_id']) && $_POST['invoice_id'] !== '') ? $_POST['invoice_id'] : null;
    
    try {
        $stmt = $pdo->prepare("UPDATE subscription_invoices SET status = 'paid', payment_date = CURDATE() WHERE id = ?");
        $stmt->execute([$invoice_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'pay_subscription_invoice', "Marked subscription invoice ID: {$invoice_id} as paid");
        
        $_SESSION['message'] = "Invoice marked as paid successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    $redirect = $_POST['redirect_to'] ?? '?page=subscriptions&subpage=billing';
    header("Location: " . $redirect);
    exit;
}

if ($action == 'run_auto_billing') {
    try {
        // Fetch all active subscriptions
        $stmt = $pdo->query("SELECT cs.*, sp.price FROM customer_subscriptions cs JOIN subscription_plans sp ON cs.plan_id = sp.id WHERE cs.status = 'active'");
        $active_subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $generated_count = 0;
        
        foreach ($active_subs as $sub) {
            $sub_id = $sub['id'];
            $price = floatval($sub['price'] ?: 0);
            
            // Check if there is an invoice in the current month (or past 28 days)
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM subscription_invoices WHERE subscription_id = ? AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 28 DAY)");
            $check_stmt->execute([$sub_id]);
            $has_recent_invoice = $check_stmt->fetchColumn() > 0;
            
            if (!$has_recent_invoice) {
                $invoice_number = 'SUB-INV-' . date('Ymd') . '-' . rand(1000, 9999);
                $inv_stmt = $pdo->prepare("INSERT INTO subscription_invoices (subscription_id, invoice_number, invoice_date, amount, status) VALUES (?, ?, CURDATE(), ?, 'unpaid')");
                $inv_stmt->execute([$sub_id, $invoice_number, $price]);
                $generated_count++;
            }
        }
        
        logActivity($pdo, $_SESSION['user_id'], 'run_auto_billing', "Executed auto-billing engine. Generated {$generated_count} invoices.");
        
        $_SESSION['message'] = "Auto-billing completed! Generated {$generated_count} new invoice(s).";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=subscriptions&subpage=billing');
    exit;
}

// ==================== FINANCE ACTIONS ====================

if ($action == 'add_expense') {
    $expense_date = (isset($_POST['expense_date']) && $_POST['expense_date'] !== '') ? $_POST['expense_date'] : null;
    $category = (isset($_POST['category']) && $_POST['category'] !== '') ? $_POST['category'] : null;
    $description = trim($_POST['description']);
    $amount = (isset($_POST['amount']) && $_POST['amount'] !== '') ? $_POST['amount'] : null;
    $payment_method = (isset($_POST['payment_method']) && $_POST['payment_method'] !== '') ? $_POST['payment_method'] : null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (expense_date, category, description, amount, payment_method, branch_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$expense_date, $category, $description, $amount, $payment_method, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_expense', "Added expense: {$category} - {$amount}");
        
        $_SESSION['message'] = "Expense added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=finance&subpage=expenses');
    exit;
}

if ($action == 'record_payment') {
    $order_id = (isset($_POST['order_id']) && $_POST['order_id'] !== '') ? $_POST['order_id'] : null;
    $amount = (isset($_POST['amount']) && $_POST['amount'] !== '') ? $_POST['amount'] : null;
    $payment_method = (isset($_POST['payment_method']) && $_POST['payment_method'] !== '') ? $_POST['payment_method'] : null;
    $transaction_id = $_POST['transaction_id'] ?? '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO payments (order_id, amount, payment_date, payment_method, status, transaction_id, branch_id) VALUES (?, ?, NOW(), ?, 'completed', ?, ?)");
        $stmt->execute([$order_id, $amount, $payment_method, $transaction_id, $current_branch_id]);
        
        // Update order payment status
        $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?")->execute([$order_id]);
        
        // Update invoice status
        $pdo->prepare("UPDATE invoices SET status = 'paid', payment_date = NOW() WHERE order_id = ?")->execute([$order_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'record_payment', "Recorded payment for order ID: {$order_id} - {$amount}");
        
        $_SESSION['message'] = "Payment recorded successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=finance&subpage=payments');
    exit;
}

// ==================== SALES ACTIONS ====================

if ($action == 'add_sales_target') {
    $year = (isset($_POST['year']) && $_POST['year'] !== '') ? $_POST['year'] : null;
    $month = (isset($_POST['month']) && $_POST['month'] !== '') ? $_POST['month'] : null;
    $target_amount = (isset($_POST['target_amount']) && $_POST['target_amount'] !== '') ? $_POST['target_amount'] : null;
    $staff_id = $_POST['staff_id'] ?: null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO sales_targets (year, month, target_amount, staff_id, branch_id) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE target_amount = ?");
        $stmt->execute([$year, $month, $target_amount, $staff_id, $current_branch_id, $target_amount]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_sales_target', "Added sales target for {$month}/{$year}: {$target_amount}");
        
        $_SESSION['message'] = "Sales target added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=sales&subpage=targets');
    exit;
}

if ($action == 'add_sales_commission') {
    $staff_id = (isset($_POST['staff_id']) && $_POST['staff_id'] !== '') ? $_POST['staff_id'] : null;
    $order_id = (isset($_POST['order_id']) && $_POST['order_id'] !== '') ? $_POST['order_id'] : null;
    $commission_rate = (isset($_POST['commission_rate']) && $_POST['commission_rate'] !== '') ? $_POST['commission_rate'] : null;
    $status = (isset($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null;

    $order_stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE id = ?");
    $order_stmt->execute([$order_id]);
    $order_info = $order_stmt->fetch(PDO::FETCH_ASSOC);
    $commission_amount = 0;

    if ($order_info) {
        $commission_amount = ($order_info['total_amount'] * $commission_rate) / 100;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO sales_commissions (staff_id, order_id, commission_rate, commission_amount, status, calculated_date, paid_date, branch_id) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
        $paid_date = $status === 'paid' ? date('Y-m-d') : null;
        $stmt->execute([$staff_id, $order_id, $commission_rate, $commission_amount, $status, $paid_date, $current_branch_id]);

        logActivity($pdo, $_SESSION['user_id'], 'add_sales_commission', "Added sales commission for staff ID: {$staff_id}, order ID: {$order_id}");
        $_SESSION['message'] = "Sales commission added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }

    header('Location: ?page=sales&subpage=commission');
    exit;
}

if ($action == 'generate_sales_report') {
    $start_date = (isset($_POST['start_date']) && $_POST['start_date'] !== '') ? $_POST['start_date'] : null;
    $end_date = (isset($_POST['end_date']) && $_POST['end_date'] !== '') ? $_POST['end_date'] : null;
    
    try {
        // Get sales data
        $sales_stmt = $pdo->prepare("SELECT SUM(total_amount) as total_sales, COUNT(*) as total_orders, AVG(total_amount) as avg_order FROM orders WHERE order_date BETWEEN ? AND ? AND status = 'delivered'");
        $sales_stmt->execute([$start_date, $end_date]);
        $sales_data = $sales_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get top product
        $top_stmt = $pdo->prepare("SELECT oi.product_id, p.name, SUM(oi.quantity) as total_quantity FROM order_items oi JOIN orders o ON oi.order_id = o.id JOIN products p ON oi.product_id = p.id WHERE o.order_date BETWEEN ? AND ? AND o.status = 'delivered' GROUP BY oi.product_id ORDER BY total_quantity DESC LIMIT 1");
        $top_stmt->execute([$start_date, $end_date]);
        $top_product = $top_stmt->fetch(PDO::FETCH_ASSOC);
        
        $report_data = json_encode([
            'start_date' => $start_date,
            'end_date' => $end_date,
            'sales_by_day' => []
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO sales_reports (report_date, total_sales, total_orders, average_order_value, top_product_id, report_data) VALUES (NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([
            $sales_data['total_sales'] ?? 0,
            $sales_data['total_orders'] ?? 0,
            $sales_data['avg_order'] ?? 0,
            $top_product['product_id'] ?? null,
            $report_data
        ]);
        
        logActivity($pdo, $_SESSION['user_id'], 'generate_sales_report', "Generated sales report for {$start_date} to {$end_date}");
        
        $_SESSION['message'] = "Sales report generated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=reports&subpage=sales');
    exit;
}

// ==================== SALARY ACTIONS ====================

if ($action == 'pay_salary') {
    $staff_id = (isset($_POST['staff_id']) && $_POST['staff_id'] !== '') ? $_POST['staff_id'] : null;
    $month = (isset($_POST['month']) && $_POST['month'] !== '') ? $_POST['month'] : null;
    $year = (isset($_POST['year']) && $_POST['year'] !== '') ? $_POST['year'] : null;
    $amount = (isset($_POST['amount']) && $_POST['amount'] !== '') ? $_POST['amount'] : null;
    
    try {
        // Security: Validate staff exists
        $staff_check = $pdo->prepare("SELECT id FROM staff WHERE id = ?");
        $staff_check->execute([$staff_id]);
        if (!$staff_check->fetch()) {
            $_SESSION['message'] = "Error: Invalid staff member.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=salary&subpage=payments');
            exit;
        }
        
        // Check if salary was already paid within the last 30 days
        $recent_payment = $pdo->prepare("SELECT payment_date FROM salary_payments WHERE staff_id = ? AND payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY payment_date DESC LIMIT 1");
        $recent_payment->execute([$staff_id]);
        $last_payment = $recent_payment->fetch(PDO::FETCH_ASSOC);
        
        if ($last_payment) {
            $_SESSION['message'] = "Salary has already been paid for this period. The next payment can only be processed after 30 days.";
            $_SESSION['message_type'] = "warning";
            header('Location: ?page=salary&subpage=payments');
            exit;
        }
        
        // Security: Validate amount is positive
        if ($amount <= 0) {
            $_SESSION['message'] = "Error: Salary amount must be greater than zero.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=salary&subpage=payments');
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO salary_payments (staff_id, month, year, amount, payment_date, status, branch_id) VALUES (?, ?, ?, ?, NOW(), 'paid', ?)");
        $stmt->execute([$staff_id, $month, $year, $amount, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'pay_salary', "Paid salary for staff ID: {$staff_id} - {$month} {$year}: {$amount}");
        
        // Generate payslip
        generatePayslip($pdo, $staff_id, $month, $year);
        
        // Notify staff
        $staff_stmt = $pdo->prepare("SELECT user_id FROM staff WHERE id = ?");
        $staff_stmt->execute([$staff_id]);
        $staff = $staff_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($staff && $staff['user_id']) {
            createNotification($pdo, $staff['user_id'], "Salary Paid", "Your salary for {$month} {$year} of \${$amount} has been paid.", 'success');
        }
        
        $_SESSION['message'] = "Salary paid successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=salary&subpage=payments');
    exit;
}

if ($action == 'run_payroll') {
    $month = (isset($_POST['month']) && $_POST['month'] !== '') ? $_POST['month'] : null;
    $year = (isset($_POST['year']) && $_POST['year'] !== '') ? $_POST['year'] : null;

    try {
        $staff_stmt = $pdo->query("SELECT * FROM staff");
        $staff_list = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($staff_list as $staff) {
            $deductions = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM salary_deductions WHERE staff_id = ? AND month = ? AND year = ?");
            $deductions->execute([$staff['id'], $month, $year]);
            $deduction_total = $deductions->fetchColumn() ?: 0;

            $bonuses = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM salary_bonuses WHERE staff_id = ? AND month = ? AND year = ?");
            $bonuses->execute([$staff['id'], $month, $year]);
            $bonus_total = $bonuses->fetchColumn() ?: 0;

            $net_salary = $staff['salary'] - $deduction_total + $bonus_total;

            $existing = $pdo->prepare("SELECT COUNT(*) FROM salary_payments WHERE staff_id = ? AND month = ? AND year = ?");
            $existing->execute([$staff['id'], $month, $year]);

            if ($existing->fetchColumn() == 0) {
                $stmt = $pdo->prepare("INSERT INTO salary_payments (staff_id, month, year, amount, payment_date, status, branch_id) VALUES (?, ?, ?, ?, NOW(), 'paid', ?)");
                $stmt->execute([$staff['id'], $month, $year, $net_salary, $current_branch_id]);
                generatePayslip($pdo, $staff['id'], $month, $year);
            }
        }

        logActivity($pdo, $_SESSION['user_id'], 'run_payroll', "Payroll run for {$month} {$year}");
        $_SESSION['message'] = "Payroll processed for {$month} {$year}.";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }

    header('Location: ?page=salary&subpage=payroll');
    exit;
}

if ($action == 'add_deduction') {
    $staff_id = (isset($_POST['staff_id']) && $_POST['staff_id'] !== '') ? $_POST['staff_id'] : null;
    $month = (isset($_POST['month']) && $_POST['month'] !== '') ? $_POST['month'] : null;
    $year = (isset($_POST['year']) && $_POST['year'] !== '') ? $_POST['year'] : null;
    $deduction_type = (isset($_POST['deduction_type']) && $_POST['deduction_type'] !== '') ? $_POST['deduction_type'] : null;
    $amount = (isset($_POST['amount']) && $_POST['amount'] !== '') ? $_POST['amount'] : null;
    $description = trim($_POST['description']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO salary_deductions (staff_id, month, year, deduction_type, amount, description, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$staff_id, $month, $year, $deduction_type, $amount, $description, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_deduction', "Added deduction for staff ID: {$staff_id} - {$deduction_type}: {$amount}");
        
        // Regenerate payslip
        generatePayslip($pdo, $staff_id, $month, $year);
        
        $_SESSION['message'] = "Deduction added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=salary&subpage=deductions');
    exit;
}

if ($action == 'add_bonus') {
    $staff_id = (isset($_POST['staff_id']) && $_POST['staff_id'] !== '') ? $_POST['staff_id'] : null;
    $month = (isset($_POST['month']) && $_POST['month'] !== '') ? $_POST['month'] : null;
    $year = (isset($_POST['year']) && $_POST['year'] !== '') ? $_POST['year'] : null;
    $bonus_type = (isset($_POST['bonus_type']) && $_POST['bonus_type'] !== '') ? $_POST['bonus_type'] : null;
    $amount = (isset($_POST['amount']) && $_POST['amount'] !== '') ? $_POST['amount'] : null;
    $description = trim($_POST['description']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO salary_bonuses (staff_id, month, year, bonus_type, amount, description, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$staff_id, $month, $year, $bonus_type, $amount, $description, $current_branch_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_bonus', "Added bonus for staff ID: {$staff_id} - {$bonus_type}: {$amount}");
        
        // Regenerate payslip
        generatePayslip($pdo, $staff_id, $month, $year);
        
        $_SESSION['message'] = "Bonus added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=salary&subpage=bonus');
    exit;
}

// ==================== SETTINGS ACTIONS ====================

if ($action == 'update_settings') {
    if (!isSuperAdmin()) {
        $_SESSION['message'] = "Unauthorized: Only Super Admins can update system settings.";
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=dashboard');
        exit;
    }
    // First, fetch current settings
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $current_settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $current_settings[$row['setting_key']] = $row['setting_value'];
    }

    $settings = [
        'company_name' => isset($_POST['setting_company_name']) ? $_POST['setting_company_name'] : ($current_settings['company_name'] ?? ''),
        'company_email' => isset($_POST['setting_company_email']) ? $_POST['setting_company_email'] : ($current_settings['company_email'] ?? ''),
        'company_phone' => isset($_POST['setting_company_phone']) ? $_POST['setting_company_phone'] : ($current_settings['company_phone'] ?? ''),
        'currency' => isset($_POST['setting_currency']) ? $_POST['setting_currency'] : ($current_settings['currency'] ?? 'USD'),
        'tax_rate' => isset($_POST['setting_tax_rate']) ? $_POST['setting_tax_rate'] : ($current_settings['tax_rate'] ?? '5'),
        'delivery_fee' => isset($_POST['setting_delivery_fee']) ? $_POST['setting_delivery_fee'] : ($current_settings['delivery_fee'] ?? '2.00'),
        'delivery_cities' => isset($_POST['setting_delivery_cities']) ? $_POST['setting_delivery_cities'] : ($current_settings['delivery_cities'] ?? ''),
        'delivery_hours' => isset($_POST['setting_delivery_hours']) ? $_POST['setting_delivery_hours'] : ($current_settings['delivery_hours'] ?? ''),
        'maintenance_mode' => isset($_POST['setting_maintenance_mode']) ? $_POST['setting_maintenance_mode'] : ($current_settings['maintenance_mode'] ?? 'off'),
        'maintenance_message' => isset($_POST['setting_maintenance_message']) ? $_POST['setting_maintenance_message'] : ($current_settings['maintenance_message'] ?? 'Scheduled maintenance in progress'),
        'default_language' => isset($_POST['setting_default_language']) ? $_POST['setting_default_language'] : ($current_settings['default_language'] ?? 'en'),
        'date_format' => isset($_POST['setting_date_format']) ? $_POST['setting_date_format'] : ($current_settings['date_format'] ?? 'Y-m-d')
    ];

    if (isset($_FILES['setting_company_logo']) && $_FILES['setting_company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['setting_company_logo']['error'] === UPLOAD_ERR_OK) {
            $logoFilename = uploadFile($_FILES['setting_company_logo'], UPLOAD_PATH);
            if ($logoFilename) {
                $settings['company_logo'] = 'uploads/' . $logoFilename;
            } else {
                $_SESSION['message'] = "Failed to upload logo file. Please check file type (jpg, png) and size.";
                $_SESSION['message_type'] = "danger";
                header('Location: ?page=settings&subpage=' . urlencode($_POST['redirect_subpage'] ?? 'general'));
                exit;
            }
        } else {
            $_SESSION['message'] = "Upload error code: " . $_FILES['setting_company_logo']['error'];
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=settings&subpage=' . urlencode($_POST['redirect_subpage'] ?? 'general'));
            exit;
        }
    }

    try {
        foreach ($settings as $key => $value) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?");
            $check->execute([$key]);
            if ($check->fetchColumn() > 0) {
                $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
                $stmt->execute([$key, $value]);
            }
        }

        logActivity($pdo, $_SESSION['user_id'], 'update_settings', "Updated system settings");

        $_SESSION['message'] = "Settings updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }

    $redirectSubpage = $_POST['redirect_subpage'] ?? 'general';
    header('Location: ?page=settings&subpage=' . urlencode($redirectSubpage));
    exit;
}

// ==================== NOTIFICATION ACTIONS ====================

if ($action == 'mark_notification_read') {
    $notification_id = (isset($_POST['notification_id']) && $_POST['notification_id'] !== '') ? $_POST['notification_id'] : null;
    markNotificationRead($pdo, $notification_id, $_SESSION['user_id']);
    $_SESSION['message'] = "Notification marked as read.";
    $_SESSION['message_type'] = "success";
    header('Location: ' . ($_POST['redirect'] ?? '?page=dashboard'));
    exit;
}

if ($action == 'send_notification') {
    $user_id = (isset($_POST['user_id']) && $_POST['user_id'] !== '') ? $_POST['user_id'] : null;
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $type = (isset($_POST['type']) && $_POST['type'] !== '') ? $_POST['type'] : null;
    
    createNotification($pdo, $user_id, $title, $message, $type);
    logActivity($pdo, $_SESSION['user_id'], 'send_notification', "Sent notification to user ID: {$user_id}");
    
    $_SESSION['message'] = "Notification sent!";
    $_SESSION['message_type'] = "success";
    header('Location: ?page=notifications&subpage=sms');
    exit;
}

if ($action == 'send_sms') {
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $subject = trim($_POST['subject'] ?? 'SMS Alert');

    if (empty($message)) {
        $_SESSION['message'] = "SMS message cannot be empty.";
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=notifications&subpage=sms');
        exit;
    }

    if ($phone) {
        if (sendSMS($phone, $message)) {
            createNotification($pdo, null, $subject, "SMS to {$phone}: {$message}", 'sms');
        }
    } else {
        $users = $pdo->query("SELECT id, phone FROM users WHERE role != 'customer'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            if (!empty($user['phone'])) {
                sendSMS($user['phone'], $message);
            }
            createNotification($pdo, $user['id'], $subject, "SMS broadcast: {$message}", 'sms');
        }
    }

    logActivity($pdo, $_SESSION['user_id'], 'send_sms', "Sent SMS notification");
    $_SESSION['message'] = "SMS sent successfully!";
    $_SESSION['message_type'] = "success";
    header('Location: ?page=notifications&subpage=sms');
    exit;
}

if ($action == 'send_whatsapp') {
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $subject = trim($_POST['subject'] ?? 'WhatsApp Alert');

    if (empty($message)) {
        $_SESSION['message'] = "WhatsApp message cannot be empty.";
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=notifications&subpage=whatsapp');
        exit;
    }

    if ($phone) {
        if (sendWhatsApp($phone, $message)) {
            createNotification($pdo, null, $subject, "WhatsApp to {$phone}: {$message}", 'whatsapp');
        }
    } else {
        $users = $pdo->query("SELECT id, phone FROM users WHERE role != 'customer'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            if (!empty($user['phone'])) {
                sendWhatsApp($user['phone'], $message);
            }
            createNotification($pdo, $user['id'], $subject, "WhatsApp broadcast: {$message}", 'whatsapp');
        }
    }

    logActivity($pdo, $_SESSION['user_id'], 'send_whatsapp', "Sent WhatsApp notification");
    $_SESSION['message'] = "WhatsApp message queued successfully!";
    $_SESSION['message_type'] = "success";
    header('Location: ?page=notifications&subpage=whatsapp');
    exit;
}

if ($action == 'send_email') {
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Email Alert');

    if (empty($message)) {
        $_SESSION['message'] = "Email message cannot be empty.";
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=notifications&subpage=email');
        exit;
    }

    if ($email) {
        if (sendEmail($email, $subject, $message)) {
            createNotification($pdo, null, $subject, "Email to {$email}: {$message}", 'email');
        }
    } else {
        $users = $pdo->query("SELECT id, email FROM users WHERE role != 'customer'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            if (!empty($user['email'])) {
                sendEmail($user['email'], $subject, $message);
            }
            createNotification($pdo, $user['id'], $subject, "Email broadcast: {$message}", 'email');
        }
    }

    logActivity($pdo, $_SESSION['user_id'], 'send_email', "Sent email notification");
    $_SESSION['message'] = "Email notification queued successfully!";
    $_SESSION['message_type'] = "success";
    header('Location: ?page=notifications&subpage=email');
    exit;
}

// ==================== SECURITY ACTIONS ====================

if ($action == 'create_backup') {
    if (createDatabaseBackup($pdo, $_SESSION['user_id'])) {
        logActivity($pdo, $_SESSION['user_id'], 'create_backup', "Created database backup");
        $_SESSION['message'] = "Backup created successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to create backup. Please check permissions.";
        $_SESSION['message_type'] = "danger";
    }
    header('Location: ?page=security&subpage=backup');
    exit;
}

if ($action == 'restore_backup') {
    $backup_id = (isset($_POST['backup_id']) && $_POST['backup_id'] !== '') ? $_POST['backup_id'] : null;
    // Restore logic here
    $_SESSION['message'] = "Restore functionality requires manual intervention.";
    $_SESSION['message_type'] = "warning";
    header('Location: ?page=security&subpage=backup');
    exit;
}

// ==================== HR ACTIONS ====================

if ($action == 'update_performance') {
    $staff_id = intval($_POST['staff_id']);
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    $attendance_score = floatval($_POST['attendance_score']);
    $punctuality_score = floatval($_POST['punctuality_score']);
    $task_score = floatval($_POST['task_score']);
    $custom_score = floatval($_POST['custom_score']);
    $notes = $_POST['notes'];
    
    $total_score = $attendance_score + $punctuality_score + $task_score + $custom_score;
    
    try {
        // Insert or Update
        $stmt = $pdo->prepare("INSERT INTO employee_performance (staff_id, branch_id, month, year, attendance_score, punctuality_score, tasks_completed, custom_score, total_score, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE attendance_score=VALUES(attendance_score), punctuality_score=VALUES(punctuality_score), tasks_completed=VALUES(tasks_completed), custom_score=VALUES(custom_score), total_score=VALUES(total_score), notes=VALUES(notes)");
        $stmt->execute([$staff_id, $current_branch_id, $month, $year, $attendance_score, $punctuality_score, $task_score, $custom_score, $total_score, $notes]);
        
        // Update Rankings
        $pdo->query("SET @rank=0;");
        $pdo->query("UPDATE employee_performance p JOIN (SELECT id, @rank:=@rank+1 AS rank FROM employee_performance WHERE month = $month AND year = $year AND branch_id = $current_branch_id ORDER BY total_score DESC) r ON p.id = r.id SET p.ranked_position = r.rank WHERE p.month = $month AND p.year = $year AND p.branch_id = $current_branch_id;");
        
        logActivity($pdo, $_SESSION['user_id'], 'update_performance', "Updated performance for staff ID {$staff_id}");
        $_SESSION['message'] = "Performance score updated successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error updating performance: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    header('Location: ?page=staff&subpage=performance');
    exit;
}

if ($action == 'give_award') {
    $staff_id = intval($_POST['staff_id']);
    $award_title = $_POST['award_title'];
    $cash_bonus = floatval($_POST['cash_bonus']);
    $award_description = $_POST['award_description'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO employee_awards (staff_id, branch_id, award_type, award_title, award_description, month, year, cash_bonus, given_by, award_date) VALUES (?, ?, 'custom', ?, ?, MONTH(NOW()), YEAR(NOW()), ?, ?, NOW())");
        $stmt->execute([$staff_id, $current_branch_id, $award_title, $award_description, $cash_bonus, $_SESSION['user_id']]);
        
        logActivity($pdo, $_SESSION['user_id'], 'give_award', "Gave award '{$award_title}' to staff ID {$staff_id}");
        $_SESSION['message'] = "Award given successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error giving award: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    header('Location: ?page=staff&subpage=performance');
    exit;
}

// ==================== DEFAULT ====================

// ==================== TASK MANAGEMENT ACTIONS ====================

if ($action == 'add_task') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $assigned_to = (isset($_POST['assigned_to']) && $_POST['assigned_to'] !== '') ? intval($_POST['assigned_to']) : null;
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = (isset($_POST['due_date']) && $_POST['due_date'] !== '') ? $_POST['due_date'] : null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, priority, due_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $assigned_to, $priority, $due_date]);
        
        logActivity($pdo, $_SESSION['user_id'], 'add_task', "Added task: {$title}");
        
        $_SESSION['message'] = "Task added successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error adding task: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    header('Location: ?page=dashboard');
    exit;
}

if ($action == 'update_task_status') {
    $task_id = intval($_POST['task_id']);
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->execute([$status, $task_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'update_task_status', "Updated task ID {$task_id} to {$status}");
        
        $_SESSION['message'] = "Task status updated!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error updating task: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    header('Location: ?page=dashboard');
    exit;
}

if ($action == 'delete_task') {
    $task_id = intval($_POST['task_id']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'delete_task', "Deleted task ID {$task_id}");
        
        $_SESSION['message'] = "Task deleted successfully!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error deleting task: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    header('Location: ?page=dashboard');
    exit;
}
if ($action == 'mark_notification_read') {
    $notification_id = $_POST['notification_id'] ?? null;
    if ($notification_id) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $_SESSION['user_id']]);
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=notifications'));
    exit;
}

if ($action == 'mark_all_notifications_read') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=notifications'));
    exit;
}

if ($action == '') {
    // No action, redirect back
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=dashboard'));
    exit;
}