<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Management Layout File - Contains Sidebar and Header

// Get unread notifications count
$unread_notifications = getUnreadNotificationsCount($pdo, $_SESSION['user_id']);

$where = getBranchContextQuery();

// Get total sales for dashboard
$total_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered' AND $where")->fetchColumn() ?: 0;

// Get today's milk collection
$today_milk = $pdo->query("SELECT SUM(quantity_liters) FROM milk_collection WHERE collection_date = CURDATE() AND $where")->fetchColumn() ?: 0;

// Driver-specific statistics
if (isset($_SESSION['role']) && $_SESSION['role'] === 'driver') {
    $driver_id = $_SESSION['user_id'];
    $today_delivered = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $driver_id AND status = 'delivered' AND DATE(delivered_at) = CURDATE()")->fetchColumn() ?: 0;
    $today_cancelled = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $driver_id AND status = 'cancelled' AND DATE(cancelled_at) = CURDATE()")->fetchColumn() ?: 0;
    $today_assigned = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $driver_id AND DATE(delivery_date) = CURDATE()")->fetchColumn() ?: 0;
    $my_active_deliveries = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.phone FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE d.driver_id = $driver_id AND d.status IN ('assigned', 'in_transit') ORDER BY d.delivery_date")->fetchAll(PDO::FETCH_ASSOC);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SYSTEM_NAME; ?> - Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --secondary: #858796;
            --dark: #5a5c69;
            --light: #f8f9fc;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f8f9fc;
            overflow: hidden;
            height: 100vh;
            color: #5a5c69;
        }
        
        .wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }
        
        .sidebar {
            width: 280px;
            min-width: 280px;
            max-width: 280px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: #fff;
            transition: transform 0.3s ease, width 0.3s ease;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.08);
            overflow-y: hidden;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.1);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            flex-shrink: 0;
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.45em;
            font-weight: 700;
        }
        
        .sidebar-header p {
            margin: 5px 0 0;
            font-size: 0.8em;
            opacity: 0.85;
        }
        
        .sidebar .nav-menu {
            padding: 12px 0;
            flex: 1;
            overflow-y: auto;
        }
        
        .sidebar .nav-item {
            margin: 4px 10px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .sidebar .nav-link {
            padding: 12px 16px;
            color: rgba(255,255,255,0.93);
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.96em;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.16);
            color: #fff;
        }
        
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.24);
            color: #fff;
            border-left: 3px solid #fff;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1em;
            width: 22px;
            text-align: center;
        }
        
        .sidebar .nav-link .emoji {
            font-size: 1.1em;
            margin-right: 10px;
        }
        
        .sidebar .sub-menu {
            list-style: none;
            padding-left: 35px;
            background: rgba(0,0,0,0.08);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .sidebar .nav-item.expanded .sub-menu {
            max-height: 700px;
            transition: max-height 0.5s ease-in;
        }
        
        .sidebar .sub-menu li {
            margin: 2px 0;
        }
        
        .sidebar .sub-menu a {
            padding: 8px 10px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: block;
            font-size: 0.88em;
            border-radius: 7px;
            transition: all 0.3s ease;
        }
        
        .sidebar .sub-menu a:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }
        
        .sidebar .sub-menu a.active {
            color: #fff;
            font-weight: 600;
            background: rgba(255,255,255,0.16);
        }
        
        .content {
            flex: 1;
            margin-left: 280px;
            height: 100vh;
            transition: all 0.3s ease;
            background: #f4f6f9;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 20;
            flex-shrink: 0;
        }
        
        .main-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 24px;
        }
        
        .navbar .toggle-btn {
            background: transparent;
            border: none;
            font-size: 1.55em;
            color: #007bff;
            cursor: pointer;
            margin-right: 20px;
        }
        
        .navbar .user-info {
            display: flex;
            align-items: center;
        }
        
        .navbar .user-info i {
            font-size: 1.9em;
            color: #007bff;
            margin-right: 10px;
        }
        
        .notification-badge {
            position: relative;
            cursor: pointer;
            margin-right: 20px;
        }
        
        .notification-badge .badge {
            position: absolute;
            top: -5px;
            right: -5px;
        }
        
        .page-title {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .page-title h2 {
            color: #333;
            font-weight: 700;
            font-size: 1.6em;
            margin: 0;
        }
        
        .page-title .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .breadcrumb-item {
            color: #5a5a5a;
            font-size: 0.95em;
        }
        
        .breadcrumb-item.active {
            color: #007bff;
            font-weight: 600;
        }
        
        .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important; }
        .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important; }
        .bg-gradient-info { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important; }
        .bg-gradient-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important; }
        .bg-gradient-danger { background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important; }

        .dashboard-card {
            background: #fff;
            border-radius: 15px;
            padding: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 12px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            border: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }

        .dashboard-card.text-white .icon {
            color: rgba(255,255,255,0.25) !important;
        }

        .dashboard-card h3 {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
        }
        
        .dashboard-card p {
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 700;
            margin: 0 0 5px 0;
            opacity: 0.8;
            letter-spacing: 0.05em;
        }
        
        .dashboard-card .icon {
            font-size: 2.8rem;
            color: #dddfeb;
            transition: all 0.3s ease;
        }
        
        .form-container {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }
        
        .form-container h4 {
            margin-bottom: 18px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            font-size: 1.2em;
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            font-size: 0.95em;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 10px 22px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.25);
        }
        
        .table-container {
            background: #fff;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            overflow-x: auto;
        }
        
        .table thead th {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px;
            font-size: 0.94em;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .table td {
            padding: 10px 12px;
            font-size: 0.92em;
            vertical-align: middle;
        }
        
        .table th,
        .table td {
            white-space: nowrap;
        }
        
        .delivery-card,
        .cancellation-card {
            background: white;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 14px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .delivery-card {
            border-left: 4px solid #007bff;
        }
        
        .cancellation-card {
            border-left: 4px solid #dc3545;
        }
        
        .cancellation-card .reason {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 7px;
            margin: 10px 0;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.78em;
            font-weight: 600;
        }

        .list-group-item {
            border: none;
            border-left: 3px solid transparent;
            margin-bottom: 5px;
            border-radius: 8px !important;
            transition: all 0.2s;
            color: #5a5c69;
        }
        
        .list-group-item:hover {
            background-color: #f8f9fc;
            border-left-color: var(--primary);
            color: var(--primary);
            padding-left: 20px;
        }
        
        .badge {
            font-weight: 700;
            padding: 0.35em 0.65em;
            border-radius: 5px;
        }
        
        .bg-info { background-color: var(--info) !important; }
        .bg-success { background-color: var(--success) !important; }
        .bg-warning { background-color: var(--warning) !important; }
        .bg-danger { background-color: var(--danger) !important; }
        .bg-primary { background-color: var(--primary) !important; }
        
        .logout-btn {
            color: #dc3545;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            color: #c82333;
        }
        
        .alert {
            border-radius: 12px;
            margin-bottom: 18px;
            padding: 12px 16px;
        }
        
        .progress {
            height: 15px;
            border-radius: 10px;
        }
        
        .btn-sm {
            padding: 4px 10px;
            font-size: 0.82em;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            z-index: 1040;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
            visibility: visible;
        }
        
        @media (max-width: 1200px) {
            .sidebar {
                width: 240px;
                min-width: 240px;
                max-width: 240px;
            }
            
            .content {
                margin-left: 240px;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 280px;
                min-width: 280px;
                max-width: 280px;
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
            }
            
            .navbar {
                padding: 10px 16px;
            }
            
            .navbar .user-info {
                display: none;
            }
            
            .page-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-width: 100%;
                max-width: 100%;
            }
            
            .sidebar .nav-link {
                padding: 14px 18px;
            }
            
            .main-content {
                padding: 16px;
            }
            
            .dashboard-card {
                padding: 16px;
            }
            
            .table thead th,
            .table td {
                font-size: 0.82em;
            }
        }
        
        @media (max-width: 576px) {
            .navbar {
                flex-wrap: wrap;
            }
            
            .toggle-btn {
                margin-right: 0;
            }
            
            .page-title h2 {
                font-size: 1.35em;
            }
            
            .form-container,
            .table-container {
                padding: 16px;
            }
            
            .sidebar-header h3 {
                font-size: 1.2em;
            }
            
            .sidebar-header p {
                font-size: 0.78em;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar" style="scroll-behavior: auto;">
            <div class="sidebar-header">
                <h4>🐪 <?php echo SYSTEM_NAME; ?></h4>
                <p style="font-size: 0.75em;">v<?php echo VERSION; ?></p>
                <?php if ($logged_in): ?>
                <p class="small mt-1" style="font-size: 0.8em;">Welcome, <?php echo $_SESSION['full_name'] ?? 'User'; ?></p>
                <p>
                    <span class="badge bg-light text-dark" style="font-size: 0.7em;"><?php echo ucfirst($current_role); ?></span>
                    <?php if (isSuperAdmin()): ?>
                        <span class="badge bg-info text-dark" style="font-size: 0.7em;">Global View</span>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
            
            <div class="nav-menu" id="navMenu">
                <?php
                // Generate sidebar menu server-side to prevent scroll reset
                $isSuperAdmin = isSuperAdmin();
                $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
                $isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
                $isStaff = isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
                $isVet = isset($_SESSION['role']) && $_SESSION['role'] === 'vet';
                $isDriver = isset($_SESSION['role']) && $_SESSION['role'] === 'driver';
                $isAdminRole = $isSuperAdmin || $isAdmin || $isManager;
                $isOperations = $isAdminRole || $isStaff;
                ?>
                <div class="nav-item <?php echo $page == 'dashboard' ? 'expanded' : ''; ?>">
                    <a href="?page=dashboard" class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </div>

                <?php if ($isSuperAdmin): ?>
                <!-- 1. User & Role Management -->
                <div class="nav-item <?php echo $page == 'users' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">👥</span> Users & Roles
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=users&subpage=list" class="<?php echo $page == 'users' && $subpage == 'list' ? 'active' : ''; ?>"><i class="bi bi-list-ul"></i> All Users</a></li>
                        <li><a href="?page=users&subpage=add" class="<?php echo $page == 'users' && $subpage == 'add' ? 'active' : ''; ?>"><i class="bi bi-person-plus"></i> Add User</a></li>
                        <li><a href="?page=users&subpage=roles" class="<?php echo $page == 'users' && $subpage == 'roles' ? 'active' : ''; ?>"><i class="bi bi-shield-lock"></i> Roles & Permissions</a></li>
                        <li><a href="?page=users&subpage=login-history" class="<?php echo $page == 'users' && $subpage == 'login-history' ? 'active' : ''; ?>"><i class="bi bi-clock-history"></i> Login History</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isAdminRole): ?>
                <!-- 2. Staff & HR Management -->
                <div class="nav-item <?php echo $page == 'staff' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">👔</span> Staff & HR
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=staff&subpage=list" class="<?php echo $page == 'staff' && $subpage == 'list' ? 'active' : ''; ?>"><i class="bi bi-people"></i> All Staff</a></li>
                        <?php if ($isSuperAdmin): ?>
                        <li><a href="?page=staff&subpage=add" class="<?php echo $page == 'staff' && $subpage == 'add' ? 'active' : ''; ?>"><i class="bi bi-person-plus"></i> Add Staff</a></li>
                        <li><a href="?page=staff&subpage=departments" class="<?php echo $page == 'staff' && $subpage == 'departments' ? 'active' : ''; ?>"><i class="bi bi-diagram-3"></i> Departments</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isAdminRole || $isStaff || $isVet): ?>
                <!-- 3. Camel Registration -->
                <div class="nav-item <?php echo $page == 'camels' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">🐫</span> Camel Registration
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=camels&subpage=list" class="<?php echo $page == 'camels' && $subpage == 'list' ? 'active' : ''; ?>"><i class="bi bi-list-ul"></i> All Camels</a></li>
                        <?php if ($isAdminRole || $isStaff): ?>
                        <li><a href="?page=camels&subpage=add" class="<?php echo $page == 'camels' && $subpage == 'add' ? 'active' : ''; ?>"><i class="bi bi-plus-circle"></i> Register Camel</a></li>
                        <?php endif; ?>
                        <?php if (!$isSuperAdmin): ?>
                        <li><a href="?page=camels&subpage=health" class="<?php echo $page == 'camels' && $subpage == 'health' ? 'active' : ''; ?>"><i class="bi bi-heart-pulse"></i> Health Records</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isAdminRole || $isStaff || $isVet): ?>
                <!-- 4. Feed Management -->
                <div class="nav-item <?php echo $page == 'feed' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">🌾</span> Feed Management
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=feed&subpage=types" class="<?php echo $page == 'feed' && $subpage == 'types' ? 'active' : ''; ?>"><i class="bi bi-list-ul"></i> Feed Types</a></li>
                        <li><a href="?page=feed&subpage=plans" class="<?php echo $page == 'feed' && $subpage == 'plans' ? 'active' : ''; ?>"><i class="bi bi-clipboard-data"></i> Feed Plans</a></li>
                        <?php if ($isAdminRole || $isStaff): ?>
                        <li><a href="?page=feed&subpage=consumption" class="<?php echo $page == 'feed' && $subpage == 'consumption' ? 'active' : ''; ?>"><i class="bi bi-clipboard-data"></i> Consumption</a></li>
                        <li><a href="?page=feed&subpage=suppliers" class="<?php echo $page == 'feed' && $subpage == 'suppliers' ? 'active' : ''; ?>"><i class="bi bi-truck"></i> Suppliers</a></li>
                        <li><a href="?page=feed&subpage=purchases" class="<?php echo $page == 'feed' && $subpage == 'purchases' ? 'active' : ''; ?>"><i class="bi bi-cart"></i> Purchases</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isAdminRole || $isVet): ?>
                <!-- 5. Health & Veterinary -->
                <div class="nav-item <?php echo $page == 'health' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">🩺</span> Health & Vet
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=health&subpage=list" class="<?php echo $page == 'health' && $subpage == 'list' ? 'active' : ''; ?>"><i class="bi bi-list-ul"></i> Health Records</a></li>
                        <li><a href="?page=health&subpage=add-disease" class="<?php echo $page == 'health' && $subpage == 'add-disease' ? 'active' : ''; ?>"><i class="bi bi-bug"></i> Log Disease</a></li>
                        <li><a href="?page=health&subpage=add-vaccination" class="<?php echo $page == 'health' && $subpage == 'add-vaccination' ? 'active' : ''; ?>"><i class="bi bi-shield-plus"></i> Add Vaccination</a></li>
                        <li><a href="?page=health&subpage=add-visit" class="<?php echo $page == 'health' && $subpage == 'add-visit' ? 'active' : ''; ?>"><i class="bi bi-telephone"></i> Vet Visit Log</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin || $isStaff): ?>
                <!-- 6. Milk Collection -->
                <div class="nav-item <?php echo $page == 'milk-collection' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">🥛</span> Milk Collection
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=milk-collection&subpage=daily" class="<?php echo $page == 'milk-collection' && $subpage == 'daily' ? 'active' : ''; ?>"><i class="bi bi-calendar-day"></i> Daily Collection</a></li>
                        <li><a href="?page=milk-collection&subpage=analytics" class="<?php echo $page == 'milk-collection' && $subpage == 'analytics' ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i> Production Report</a></li>
                        <li><a href="?page=milk-collection&subpage=add" class="<?php echo $page == 'milk-collection' && $subpage == 'add' ? 'active' : ''; ?>"><i class="bi bi-plus-circle"></i> Add Collection</a></li>
                        <li><a href="?page=milk-collection&subpage=quality" class="<?php echo $page == 'milk-collection' && $subpage == 'quality' ? 'active' : ''; ?>"><i class="bi bi-star"></i> Quality Grading</a></li>
                        <li><a href="?page=milk-collection&subpage=rejected" class="<?php echo $page == 'milk-collection' && $subpage == 'rejected' ? 'active' : ''; ?>"><i class="bi bi-exclamation-triangle"></i> Rejected Records</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                

                
                <?php if ($isAdminRole): ?>
                <!-- 9. Products & Inventory -->
                <div class="nav-item <?php echo $page == 'products' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">📦</span> Products & Inventory
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=products&subpage=list" class="<?php echo $page == 'products' && $subpage == 'list' ? 'active' : ''; ?>"><i class="bi bi-list-ul"></i> All Products</a></li>
                        <li><a href="?page=products&subpage=add" class="<?php echo $page == 'products' && $subpage == 'add' ? 'active' : ''; ?>"><i class="bi bi-plus-circle"></i> Add Product</a></li>
                        <li><a href="?page=products&subpage=categories" class="<?php echo $page == 'products' && $subpage == 'categories' ? 'active' : ''; ?>"><i class="bi bi-tags"></i> Categories</a></li>
                        <li><a href="?page=products&subpage=stock" class="<?php echo $page == 'products' && $subpage == 'stock' ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i> Stock Levels</a></li>
                        <li><a href="?page=products&subpage=alerts" class="<?php echo $page == 'products' && $subpage == 'alerts' ? 'active' : ''; ?>"><i class="bi bi-exclamation-triangle"></i> Low Stock Alerts</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isAdminRole): ?>
                <!-- 10. Customer Management -->
                <div class="nav-item <?php echo $page == 'customers' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">👤</span> Customer Management
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=customers&subpage=list" class="<?php echo $page == 'customers' && $subpage == 'list' ? 'active' : ''; ?>"><i class="bi bi-people"></i> All Customers</a></li>
                        <li><a href="?page=customers&subpage=add" class="<?php echo $page == 'customers' && $subpage == 'add' ? 'active' : ''; ?>"><i class="bi bi-person-plus"></i> Add Customer</a></li>
                        <li><a href="?page=customers&subpage=addresses" class="<?php echo $page == 'customers' && $subpage == 'addresses' ? 'active' : ''; ?>"><i class="bi bi-geo-alt"></i> Address Book</a></li>
                        <li><a href="?page=customers&subpage=history" class="<?php echo $page == 'customers' && $subpage == 'history' ? 'active' : ''; ?>"><i class="bi bi-clock-history"></i> Order History</a></li>
                        <li><a href="?page=customers&subpage=support" class="<?php echo $page == 'customers' && $subpage == 'support' ? 'active' : ''; ?>"><i class="bi bi-headset"></i> Support Requests</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin || $isStaff): ?>
                <!-- 11. Order Management -->
                <div class="nav-item <?php echo ($page == 'orders' || $page == 'sales') ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">📋</span> Order Management
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=sales&subpage=pos" class="<?php echo $page == 'sales' && $subpage == 'pos' ? 'active' : ''; ?>"><i class="bi bi-cart-plus"></i> New Sale (POS)</a></li>
                        <li><a href="?page=orders&subpage=list" class="<?php echo $page == 'orders' && $subpage == 'list' ? 'active' : ''; ?>"><i class="bi bi-list-ul"></i> All Orders</a></li>
                        <?php if ($isSuperAdmin): ?>
                        <li><a href="?page=orders&subpage=create" class="<?php echo $page == 'orders' && $subpage == 'create' ? 'active' : ''; ?>"><i class="bi bi-plus-circle"></i> Create Order</a></li>
                        <li><a href="?page=orders&subpage=pending" class="<?php echo $page == 'orders' && $subpage == 'pending' ? 'active' : ''; ?>"><i class="bi bi-clock"></i> Pending Approval</a></li>
                        <li><a href="?page=orders&subpage=processing" class="<?php echo $page == 'orders' && $subpage == 'processing' ? 'active' : ''; ?>"><i class="bi bi-gear"></i> Processing</a></li>
                        <li><a href="?page=orders&subpage=cancellations" class="<?php echo $page == 'orders' && $subpage == 'cancellations' ? 'active' : ''; ?>"><i class="bi bi-x-circle"></i> Cancellation Requests</a></li>
                        <li><a href="?page=orders&subpage=invoices" class="<?php echo $page == 'orders' && $subpage == 'invoices' ? 'active' : ''; ?>"><i class="bi bi-file-text"></i> Invoices</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isAdminRole || $isDriver): ?>
                <!-- 12. Delivery & Logistics -->
                <div class="nav-item <?php echo $page == 'delivery' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">🚚</span> Delivery & Logistics
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <?php if ($isSuperAdmin): ?>
                        <li><a href="?page=delivery&subpage=zones" class="<?php echo $page == 'delivery' && $subpage == 'zones' ? 'active' : ''; ?>"><i class="bi bi-geo-alt"></i> Delivery Zones</a></li>
                        <li><a href="?page=delivery&subpage=assign" class="<?php echo $page == 'delivery' && $subpage == 'assign' ? 'active' : ''; ?>"><i class="bi bi-truck"></i> Assign Deliveries</a></li>
                        <?php endif; ?>
                        <li><a href="?page=delivery&subpage=tracking" class="<?php echo $page == 'delivery' && $subpage == 'tracking' ? 'active' : ''; ?>"><i class="bi bi-geo"></i> Track Deliveries</a></li>
                        <?php if ($isSuperAdmin): ?>
                        <li><a href="?page=delivery&subpage=completed" class="<?php echo $page == 'delivery' && $subpage == 'completed' ? 'active' : ''; ?>"><i class="bi bi-check-all"></i> Completed Deliveries</a></li>
                        <?php endif; ?>
                        <li><a href="?page=delivery&subpage=proof" class="<?php echo $page == 'delivery' && $subpage == 'proof' ? 'active' : ''; ?>"><i class="bi bi-camera"></i> Proof of Delivery</a></li>
                        <?php if ($isSuperAdmin): ?>
                        <li><a href="?page=delivery&subpage=drivers" class="<?php echo $page == 'delivery' && $subpage == 'drivers' ? 'active' : ''; ?>"><i class="bi bi-person-badge"></i> Drivers</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin): ?>
                <!-- 13. Subscription System -->
                <div class="nav-item <?php echo $page == 'subscriptions' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">📅</span> Subscription System
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=subscriptions&subpage=plans" class="<?php echo $page == 'subscriptions' && $subpage == 'plans' ? 'active' : ''; ?>"><i class="bi bi-tags"></i> Subscription Plans</a></li>
                        <li><a href="?page=subscriptions&subpage=active" class="<?php echo $page == 'subscriptions' && $subpage == 'active' ? 'active' : ''; ?>"><i class="bi bi-check-circle"></i> Active Subscriptions</a></li>
                        <li><a href="?page=subscriptions&subpage=add" class="<?php echo $page == 'subscriptions' && $subpage == 'add' ? 'active' : ''; ?>"><i class="bi bi-plus-circle"></i> New Subscription</a></li>
                        <li><a href="?page=subscriptions&subpage=pause" class="<?php echo $page == 'subscriptions' && $subpage == 'pause' ? 'active' : ''; ?>"><i class="bi bi-pause-circle"></i> Pause/Resume</a></li>
                        <li><a href="?page=subscriptions&subpage=billing" class="<?php echo $page == 'subscriptions' && $subpage == 'billing' ? 'active' : ''; ?>"><i class="bi bi-credit-card"></i> Auto Billing</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin): ?>
                <!-- 14. Payment & Finance -->
                <div class="nav-item <?php echo $page == 'finance' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">💰</span> Payment & Finance
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=finance&subpage=payments" class="<?php echo $page == 'finance' && $subpage == 'payments' ? 'active' : ''; ?>"><i class="bi bi-credit-card"></i> Payments</a></li>
                        <li><a href="?page=finance&subpage=expenses" class="<?php echo $page == 'finance' && $subpage == 'expenses' ? 'active' : ''; ?>"><i class="bi bi-cash-stack"></i> Expenses</a></li>
                        <li><a href="?page=finance&subpage=profit-loss" class="<?php echo $page == 'finance' && $subpage == 'profit-loss' ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i> Profit & Loss</a></li>
                        <li><a href="?page=finance&subpage=reports" class="<?php echo $page == 'finance' && $subpage == 'reports' ? 'active' : ''; ?>"><i class="bi bi-file-bar-graph"></i> Financial Reports</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin): ?>
                <!-- 15. Reports & Analytics -->
                <div class="nav-item <?php echo $page == 'reports' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">📊</span> Reports & Analytics
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=reports&subpage=milk" class="<?php echo $page == 'reports' && $subpage == 'milk' ? 'active' : ''; ?>"><i class="bi bi-droplet"></i> Milk Production</a></li>
                        <li><a href="?page=reports&subpage=sales" class="<?php echo $page == 'reports' && $subpage == 'sales' ? 'active' : ''; ?>"><i class="bi bi-cart"></i> Sales Reports</a></li>
                        <li><a href="?page=reports&subpage=customers" class="<?php echo $page == 'reports' && $subpage == 'customers' ? 'active' : ''; ?>"><i class="bi bi-person"></i> Customer Reports</a></li>
                        <li><a href="?page=reports&subpage=products" class="<?php echo $page == 'reports' && $subpage == 'products' ? 'active' : ''; ?>"><i class="bi bi-box"></i> Product Reports</a></li>
                        <li><a href="?page=reports&subpage=delivery" class="<?php echo $page == 'reports' && $subpage == 'delivery' ? 'active' : ''; ?>"><i class="bi bi-truck"></i> Delivery Performance</a></li>
                        <li><a href="?page=reports&subpage=expenses" class="<?php echo $page == 'reports' && $subpage == 'expenses' ? 'active' : ''; ?>"><i class="bi bi-cash"></i> Expense Reports</a></li>
                        <li><a href="?page=reports&subpage=staff" class="<?php echo $page == 'reports' && $subpage == 'staff' ? 'active' : ''; ?>"><i class="bi bi-people"></i> Staff Performance</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isAdminRole): ?>
                <!-- 16. Notifications -->
                <div class="nav-item <?php echo $page == 'notifications' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">📱</span> Notifications
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=notifications&subpage=sms" class="<?php echo $page == 'notifications' && $subpage == 'sms' ? 'active' : ''; ?>"><i class="bi bi-chat"></i> SMS Alerts</a></li>
                        <li><a href="?page=notifications&subpage=whatsapp" class="<?php echo $page == 'notifications' && $subpage == 'whatsapp' ? 'active' : ''; ?>"><i class="bi bi-whatsapp"></i> WhatsApp</a></li>
                        <li><a href="?page=notifications&subpage=email" class="<?php echo $page == 'notifications' && $subpage == 'email' ? 'active' : ''; ?>"><i class="bi bi-envelope"></i> Email</a></li>
                        <li><a href="?page=notifications" class="<?php echo $page == 'notifications' && empty($subpage) ? 'active' : ''; ?>"><i class="bi bi-bell"></i> System Alerts</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin): ?>
                <!-- 17. System Settings -->
                <div class="nav-item <?php echo $page == 'settings' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">⚙️</span> System Settings
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=settings&subpage=general" class="<?php echo $page == 'settings' && $subpage == 'general' ? 'active' : ''; ?>"><i class="bi bi-building"></i> Company Profile</a></li>
                        <li><a href="?page=settings&subpage=currency" class="<?php echo $page == 'settings' && $subpage == 'currency' ? 'active' : ''; ?>"><i class="bi bi-currency-dollar"></i> Currency & Tax</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin || $isStaff): ?>
                <!-- 18. Sales Management -->
                <div class="nav-item <?php echo $page == 'sales' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">📈</span> Sales Management
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=sales&subpage=overview" class="<?php echo $page == 'sales' && $subpage == 'overview' ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i> Sales Overview</a></li>
                        <li><a href="?page=sales&subpage=deliveries" class="<?php echo $page == 'sales' && $subpage == 'deliveries' ? 'active' : ''; ?>"><i class="bi bi-truck"></i> Pending Deliveries</a></li>
                        <?php if ($isSuperAdmin): ?>
                        <li><a href="?page=sales&subpage=targets" class="<?php echo $page == 'sales' && $subpage == 'targets' ? 'active' : ''; ?>"><i class="bi bi-bullseye"></i> Sales Targets</a></li>
                        <li><a href="?page=sales&subpage=commission" class="<?php echo $page == 'sales' && $subpage == 'commission' ? 'active' : ''; ?>"><i class="bi bi-cash"></i> Commissions</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if ($isSuperAdmin): ?>
                <!-- 19. Salary Management -->
                <div class="nav-item <?php echo $page == 'salary' ? 'expanded' : ''; ?>">
                    <a href="#" class="nav-link" onclick="toggleSubMenu(this); return false;">
                        <span class="emoji">💵</span> Salary Management
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                    </a>
                    <ul class="sub-menu">
                        <li><a href="?page=salary&subpage=payroll" class="<?php echo $page == 'salary' && $subpage == 'payroll' ? 'active' : ''; ?>"><i class="bi bi-calculator"></i> Payroll</a></li>
                        <li><a href="?page=salary&subpage=payments" class="<?php echo $page == 'salary' && $subpage == 'payments' ? 'active' : ''; ?>"><i class="bi bi-credit-card"></i> Salary Payments</a></li>
                        <li><a href="?page=salary&subpage=deductions" class="<?php echo $page == 'salary' && $subpage == 'deductions' ? 'active' : ''; ?>"><i class="bi bi-dash-circle"></i> Deductions</a></li>
                        <li><a href="?page=salary&subpage=bonus" class="<?php echo $page == 'salary' && $subpage == 'bonus' ? 'active' : ''; ?>"><i class="bi bi-gift"></i> Bonuses</a></li>
                        <li><a href="?page=salary&subpage=payslips" class="<?php echo $page == 'salary' && $subpage == 'payslips' ? 'active' : ''; ?>"><i class="bi bi-file-text"></i> Payslips</a></li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </nav>
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
        
        <!-- Page Content -->
        <div class="content" id="content">
            <nav class="navbar">
                <div class="container-fluid">
                    <button type="button" class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <div class="d-flex align-items-center flex-grow-1">
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <!-- Notifications -->
                        <a href="?page=notifications" class="notification-badge text-decoration-none text-dark">
                            <i class="bi bi-bell fs-4"></i>
                            <span id="global_notification_badge" class="badge bg-danger rounded-pill" style="<?php echo ($unread_notifications > 0) ? '' : 'display:none;'; ?>">
                                <?php echo $unread_notifications; ?>
                            </span>
                        </a>
                        
                        <div class="user-info d-flex align-items-center ms-3">
                            <i class="bi bi-person-circle"></i>
                            <div>
                                <strong><?php echo $_SESSION['full_name'] ?? 'User'; ?></strong><br>
                                <small><?php echo ucfirst($current_role); ?></small>
                            </div>
                            <a href="?page=change-password" class="ms-3 text-secondary text-decoration-none" style="font-weight: 500;">
                                <i class="bi bi-key"></i> Password
                            </a>
                            <a href="?logout=1" class="logout-btn ms-3">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
            
            <div class="main-content" id="mainContent">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show shadow-sm" role="alert">
                        <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php
                // This is where the page content will be injected
                if (isset($page_content)) {
                    echo $page_content;
                } else {
                    // If no content, show default
                    ?>
                    <div class="page-title">
                        <h2>Dashboard</h2>
                        <div class="breadcrumb">
                            <span class="breadcrumb-item active">Dashboard</span>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'driver'): ?>
                    <!-- Driver Dashboard -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="dashboard-card bg-gradient-success text-white">
                                <div class="icon"><i class="bi bi-check-circle"></i></div>
                                <h3><?php echo $today_delivered; ?></h3>
                                <p>Delivered Today</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card bg-gradient-danger text-white">
                                <div class="icon"><i class="bi bi-x-circle"></i></div>
                                <h3><?php echo $today_cancelled; ?></h3>
                                <p>Cancelled Today</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card bg-gradient-info text-white">
                                <div class="icon"><i class="bi bi-truck"></i></div>
                                <h3><?php echo $today_assigned; ?></h3>
                                <p>Assigned Today</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card bg-gradient-warning text-white">
                                <div class="icon"><i class="bi bi-box"></i></div>
                                <h3><?php echo count($my_active_deliveries); ?></h3>
                                <p>Active Deliveries</p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($my_active_deliveries)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">My Active Deliveries</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Customer</th>
                                                    <th>Phone</th>
                                                    <th>Delivery Date</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($my_active_deliveries as $delivery): ?>
                                                <tr>
                                                    <td><?php echo $delivery['order_number']; ?></td>
                                                    <td><?php echo $delivery['customer_name']; ?></td>
                                                    <td><?php echo $delivery['phone'] ?? 'N/A'; ?></td>
                                                    <td><?php echo date('d M Y', strtotime($delivery['delivery_date'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $delivery['status'] == 'in_transit' ? 'info' : 'warning'; ?>">
                                                            <?php echo ucfirst($delivery['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="?page=delivery&subpage=tracking" class="btn btn-sm btn-primary">View</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle"></i> No active deliveries assigned to you.
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <!-- Regular Dashboard for other roles -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="dashboard-card">
                                <div class="icon"><i class="bi bi-people"></i></div>
                                <h3><?php echo count($users ?? []); ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card">
                                <div class="icon"><i class="bi bi-camel"></i></div>
                                <h3><?php echo count($camels ?? []); ?></h3>
                                <p>Active Camels</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card">
                                <div class="icon"><i class="bi bi-people"></i></div>
                                <h3><?php echo count($customers ?? []); ?></h3>
                                <p>Customers</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="dashboard-card">
                                <div class="icon"><i class="bi bi-droplet"></i></div>
                                <h3><?php echo number_format($today_milk, 1); ?> L</h3>
                                <p>Today's Milk</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
        
        function toggleSubMenu(element) {
            const navItem = element.closest('.nav-item');
            navItem.classList.toggle('expanded');
        }
        
        // Close mobile sidebar when the overlay or outside content is clicked
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleButton = event.target.closest('.toggle-btn');

            if (sidebar.classList.contains('active') && !toggleButton && !sidebar.contains(event.target)) {
                closeSidebar();
            }
        });
        
        // Sidebar Scroll Position Preservation
        (function() {
            function restoreSidebarScroll() {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar) return;
                const saved = sessionStorage.getItem('sidebarScrollPosition');
                if (saved !== null) {
                    sidebar.scrollTop = parseInt(saved, 10);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                if (!sidebar) return;

                restoreSidebarScroll();
                setTimeout(restoreSidebarScroll, 50);
                setTimeout(restoreSidebarScroll, 200);

                sidebar.addEventListener('scroll', function() {
                    sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
                });

                sidebar.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
                    });
                });
            });

            window.addEventListener('beforeunload', function() {
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
                }
            });
        })();

        // Global Real-time Dashboard & Charts Management
        let dashboardCharts = {};

        function updateDashboardCharts(data) {
            if (!window.Chart || !data) return;

            const chartConfigs = {
                // Sparkline Mini Charts
                'chart_sales_spark': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_sales || [],
                            borderColor: '#ffffff',
                            backgroundColor: 'rgba(255, 255, 255, 0.25)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_milk_spark': {
                    type: 'bar',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_milk || [],
                            backgroundColor: 'rgba(255, 255, 255, 0.8)',
                            borderRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_customer_spark': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_customers || [],
                            borderColor: '#ffffff',
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_orders_spark': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_active_orders || [],
                            borderColor: '#ffffff',
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_inventory_spark': {
                    type: 'bar',
                    data: {
                        labels: data.chart_inventory ? data.chart_inventory.map(i => i.name) : [],
                        datasets: [{
                            data: data.chart_inventory ? data.chart_inventory.map(i => i.stock_quantity) : [],
                            backgroundColor: 'rgba(255, 255, 255, 0.85)',
                            borderRadius: 3
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_camels_spark': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_camels || [],
                            borderColor: '#ffffff',
                            backgroundColor: 'rgba(255, 255, 255, 0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_performance_spark': {
                    type: 'bar',
                    data: {
                        labels: data.chart_performance ? data.chart_performance.map(p => p.name) : [],
                        datasets: [{
                            data: data.chart_performance ? data.chart_performance.map(p => p.total_score) : [],
                            backgroundColor: 'rgba(255, 255, 255, 0.85)',
                            borderRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },
                'chart_delivery_spark': {
                    type: 'bar',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            data: data.chart_delivered || [],
                            backgroundColor: 'rgba(255, 255, 255, 0.85)',
                            borderRadius: 3
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                },

                // Full Interactive Main Charts
                'chart_sales_trend': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            label: 'Sales Volume (Orders)',
                            data: data.chart_sales || [],
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.12)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#4e73df',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                },
                'chart_revenue_trend': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            label: 'Revenue ($)',
                            data: data.chart_revenue || [],
                            borderColor: '#1cc88a',
                            backgroundColor: 'rgba(28, 200, 138, 0.15)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#1cc88a',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                },
                'chart_orders_overview': {
                    type: 'bar',
                    data: {
                        labels: ['Pending', 'Completed', 'Cancelled'],
                        datasets: [{
                            label: 'Orders Count',
                            data: [
                                data.chart_orders ? data.chart_orders.pending : 0,
                                data.chart_orders ? data.chart_orders.completed : 0,
                                data.chart_orders ? data.chart_orders.canceled : 0
                            ],
                            backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b'],
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                },
                'chart_customer_growth': {
                    type: 'line',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            label: 'Total Customers',
                            data: data.chart_customers || [],
                            borderColor: '#36b9cc',
                            backgroundColor: 'rgba(54, 185, 204, 0.15)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#36b9cc',
                            pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                },
                'chart_milk_stats': {
                    type: 'bar',
                    data: {
                        labels: data.chart_labels || [],
                        datasets: [{
                            label: 'Milk Collected (Liters)',
                            data: data.chart_milk || [],
                            backgroundColor: 'rgba(246, 194, 62, 0.85)',
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
                },
                'chart_inventory_overview': {
                    type: 'bar',
                    data: {
                        labels: data.chart_inventory ? data.chart_inventory.map(i => i.name) : [],
                        datasets: [{
                            label: 'Current Stock',
                            data: data.chart_inventory ? data.chart_inventory.map(i => i.stock_quantity) : [],
                            backgroundColor: 'rgba(111, 66, 193, 0.85)',
                            borderRadius: 6
                        }]
                    },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                },
                'chart_performance_overview': {
                    type: 'bar',
                    data: {
                        labels: data.chart_performance ? data.chart_performance.map(p => p.name) : [],
                        datasets: [{
                            label: 'Score',
                            data: data.chart_performance ? data.chart_performance.map(p => p.total_score) : [],
                            backgroundColor: 'rgba(231, 74, 59, 0.85)',
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { max: 100 } } }
                },
                'chart_delivery_status': {
                    type: 'doughnut',
                    data: {
                        labels: ['Assigned', 'In Transit', 'Delivered', 'Cancelled'],
                        datasets: [{
                            data: [
                                data.chart_delivery ? data.chart_delivery.assigned : 0,
                                data.chart_delivery ? data.chart_delivery.in_transit : 0,
                                data.chart_delivery ? data.chart_delivery.delivered : 0,
                                data.chart_delivery ? data.chart_delivery.cancelled : 0
                            ],
                            backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                }
            };

            Object.keys(chartConfigs).forEach(canvasId => {
                const el = document.getElementById(canvasId);
                if (!el) return;
                const config = chartConfigs[canvasId];
                if (dashboardCharts[canvasId]) {
                    dashboardCharts[canvasId].data = config.data;
                    dashboardCharts[canvasId].update('none');
                } else {
                    dashboardCharts[canvasId] = new Chart(el, config);
                }
            });
        }

        // Global Real-time Polling for Notifications and Dashboards
        function pollGlobalData() {
            fetch('api_dashboard.php')
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const d = res.data;

                        // Notifications Badge
                        const badge = document.getElementById('global_notification_badge');
                        if (badge) {
                            if (d.unread_notifications > 0) {
                                badge.textContent = d.unread_notifications;
                                badge.style.display = 'inline-block';
                            } else {
                                badge.style.display = 'none';
                            }
                        }

                        // Update Real-Time Admin Dashboard Card Stats
                        if (d.global_sales !== undefined) {
                            const el = document.getElementById('dash_global_sales');
                            if (el) el.textContent = '$' + Number(d.global_sales).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        }
                        if (d.global_milk !== undefined) {
                            const el = document.getElementById('dash_global_milk');
                            if (el) el.textContent = Number(d.global_milk).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1}) + ' L';
                        }
                        if (d.global_users !== undefined) {
                            const el = document.getElementById('dash_global_users');
                            if (el) el.textContent = Number(d.global_users).toLocaleString();
                        }
                        if (d.active_orders !== undefined) {
                            const el = document.getElementById('dash_active_orders');
                            if (el) el.textContent = Number(d.active_orders).toLocaleString() + ' Orders';
                        }
                        if (d.count_products !== undefined) {
                            const el = document.getElementById('dash_count_products');
                            if (el) el.textContent = Number(d.count_products).toLocaleString() + ' Items';
                        }
                        if (d.count_camels !== undefined) {
                            const el = document.getElementById('dash_count_camels');
                            if (el) el.textContent = Number(d.count_camels).toLocaleString() + ' Camels';
                        }
                        if (d.completed_today !== undefined) {
                            const el = document.getElementById('dash_completed_today');
                            if (el) el.textContent = Number(d.completed_today).toLocaleString() + ' Delivered';
                        }
                        
                        // Employee of the Month Widget
                        if (d.award_winner !== undefined) {
                            const wName = document.getElementById('dash_award_winner_name');
                            const wPic = document.getElementById('dash_award_winner_pic');
                            const wDept = document.getElementById('dash_award_winner_dept');
                            const wScore = document.getElementById('dash_award_winner_score');
                            
                            if (wName) {
                                if (d.award_winner) {
                                    wName.textContent = d.award_winner.name;
                                    if (wDept) wDept.textContent = d.award_winner.department_name || 'N/A';
                                    if (wScore) wScore.innerHTML = `<i class="bi bi-star-fill text-warning"></i> Attendance: ${Number(d.award_winner.attendance_score).toFixed(1)}%`;
                                    if (wPic) {
                                        wPic.src = d.award_winner.profile_picture_url;
                                        wPic.classList.remove('d-none');
                                    }
                                } else {
                                    wName.textContent = 'No data yet';
                                    if (wDept) wDept.textContent = '';
                                    if (wScore) wScore.textContent = '';
                                    if (wPic) wPic.classList.add('d-none');
                                }
                            }
                        }
                        
                        // Driver Dashboard metrics
                        if (d.assigned_orders !== undefined) {
                            const el = document.getElementById('dash_assigned');
                            if (el) el.textContent = d.assigned_orders;
                        }
                        if (d.accepted_orders !== undefined) {
                            const el = document.getElementById('dash_accepted');
                            if (el) el.textContent = d.accepted_orders;
                        }
                        if (d.today_deliveries !== undefined) {
                            const el = document.getElementById('dash_today_completed');
                            if (el) el.textContent = d.today_deliveries;
                        }
                        if (d.today_cancellations !== undefined) {
                            const el = document.getElementById('dash_today_cancelled');
                            if (el) el.textContent = d.today_cancellations;
                        }
                        if (d.completed_orders !== undefined) {
                            const el = document.getElementById('dash_total_completed');
                            if (el) el.textContent = d.completed_orders;
                        }
                        if (d.canceled_orders !== undefined) {
                            const el = document.getElementById('dash_total_cancelled');
                            if (el) el.textContent = d.canceled_orders;
                        }
                        if (d.active_queue && Array.isArray(d.active_queue)) {
                            const queueEl = document.getElementById('dash_active_queue');
                            if (queueEl) {
                                queueEl.innerHTML = '';
                                if (d.active_queue.length === 0) {
                                    queueEl.innerHTML = '<tr><td colspan="4" class="text-center">No active deliveries assigned.</td></tr>';
                                } else {
                                    d.active_queue.forEach(q => {
                                        const tr = document.createElement('tr');
                                        let statusBadge = q.status === 'assigned' ? '<span class="badge bg-warning">Assigned</span>' : '<span class="badge bg-info">In Transit</span>';
                                        tr.innerHTML = `
                                            <td>#${q.order_number}</td>
                                            <td>${q.customer_name}<br><small class="text-muted">${q.address}</small></td>
                                            <td>${statusBadge}</td>
                                            <td><a href="?page=delivery&subpage=view&id=${q.id}" class="btn btn-sm btn-primary">View</a></td>
                                        `;
                                        queueEl.appendChild(tr);
                                    });
                                }
                            }
                        }
                        
                        // Driver Recent Activity
                        if (d.recent_activity && Array.isArray(d.recent_activity)) {
                            const recentBody = document.getElementById('dash_recent_activity');
                            if (recentBody) {
                                recentBody.innerHTML = '';
                                if (d.recent_activity.length === 0) {
                                    recentBody.innerHTML = '<tr><td colspan="4" class="text-center">No recent activity</td></tr>';
                                } else {
                                    d.recent_activity.forEach(item => {
                                        const tr = document.createElement('tr');
                                        let statusBadge = 'secondary';
                                        if (item.status === 'delivered') statusBadge = 'success';
                                        if (item.status === 'cancelled') statusBadge = 'danger';
                                        if (item.status === 'in_transit') statusBadge = 'info';
                                        if (item.status === 'assigned') statusBadge = 'warning';
                                        
                                        const actionTime = item.delivered_at || item.cancelled_at || item.accepted_at || item.assigned_at || '';
                                        const formattedTime = actionTime ? new Date(actionTime).toLocaleString() : 'N/A';
                                        
                                        tr.innerHTML = `
                                            <td>${item.order_number}</td>
                                            <td>${item.customer_name}</td>
                                            <td><span class="badge bg-${statusBadge}">${(item.status || 'unknown').replace('_', ' ').toUpperCase()}</span></td>
                                            <td>${formattedTime}</td>
                                        `;
                                        recentBody.appendChild(tr);
                                    });
                                }
                            }
                        }

                        // Update Live Activities Stream
                        const actEl = document.getElementById('dash_live_activities');
                        if (actEl && d.recent_activities) {
                            actEl.innerHTML = '';
                            if (d.recent_activities.length === 0) {
                                actEl.innerHTML = '<div class="text-muted text-center py-3">No recent activities.</div>';
                            } else {
                                d.recent_activities.forEach(act => {
                                    const item = document.createElement('div');
                                    item.className = 'd-flex align-items-center justify-content-between p-2.5 mb-2 rounded bg-light border-start border-4 border-' + (act.badge || 'primary');
                                    item.style.backgroundColor = 'var(--bs-gray-100)';
                                    item.innerHTML = `
                                        <div class="d-flex align-items-center">
                                            <div class="badge bg-${act.badge || 'primary'} me-3 p-2 fs-6 rounded-circle"><i class="bi ${act.icon || 'bi-activity'}"></i></div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">${act.title}</h6>
                                                <small class="text-muted">${act.desc}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-${act.badge || 'secondary'} bg-opacity-15 text-${act.badge || 'dark'} mb-1">${act.status}</span>
                                            <div><small class="text-muted" style="font-size: 0.75rem;">${act.time}</small></div>
                                        </div>
                                    `;
                                    actEl.appendChild(item);
                                });
                            }
                        }

                        // Update Live Notifications Stream
                        const notifEl = document.getElementById('dash_live_notifications');
                        if (notifEl && d.notifications) {
                            notifEl.innerHTML = '';
                            if (d.notifications.length === 0) {
                                notifEl.innerHTML = '<div class="text-muted text-center py-3">No active notifications.</div>';
                            } else {
                                d.notifications.forEach(n => {
                                    const item = document.createElement('div');
                                    item.className = 'd-flex align-items-start p-2.5 mb-2 rounded border-bottom';
                                    item.innerHTML = `
                                        <div class="me-2 text-warning fs-5"><i class="bi bi-bell-fill"></i></div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">${n.title || 'System Notification'}</div>
                                            <div class="text-muted" style="font-size: 0.8rem;">${n.message || ''}</div>
                                            <div class="text-muted text-end" style="font-size: 0.7rem;">${n.created_at ? new Date(n.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : ''}</div>
                                        </div>
                                    `;
                                    notifEl.appendChild(item);
                                });
                            }
                        }

                        // Update Charts
                        updateDashboardCharts(d);
                    }
                })
                .catch(err => console.error("Polling error:", err));
        }

        // Run global polling and restore scroll state
        document.addEventListener('DOMContentLoaded', function() {
            pollGlobalData();
            
            const sidebarMenu = document.querySelector('.sidebar .nav-menu');
            if (sidebarMenu) {
                const scrollPos = sessionStorage.getItem('sidebar-scroll-pos');
                if (scrollPos !== null) {
                    sidebarMenu.scrollTop = parseInt(scrollPos, 10);
                }
                
                window.addEventListener('beforeunload', function() {
                    sessionStorage.setItem('sidebar-scroll-pos', sidebarMenu.scrollTop);
                });
            }
        });
        setInterval(pollGlobalData, 5000);
    </script>
</body>
</html>