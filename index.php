<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Main Entry Point

require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';

// Initialize database and insert default data
initializeDatabase($pdo);
insertDefaultData($pdo);

// Make sure page and subpage are defined
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$subpage = isset($_GET['subpage']) ? $_GET['subpage'] : 'list';

// Handle POST actions for admin/management pages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $page !== 'shop') {
    require_once 'process_actions.php';
    exit;
}

// Customer Pages - These pages are accessible to everyone
$customer_pages = ['home', 'about', 'contact', 'login', 'register'];
if (!($logged_in && $current_role != 'customer')) {
    $customer_pages[] = 'products';
}
if (in_array($page, $customer_pages)) {
    require_once 'customer_pages.php';
    exit;
}

// Shop Pages - For customers only
if ($page == 'shop') {
    require_once 'shop_pages.php';
    exit;
}

// Management Pages - For admin, manager, staff, etc.
if ($logged_in && $current_role != 'customer') {
    
    // Role-based Page Authorization
    $role_permissions = [
        'super_admin' => [
            'dashboard', 'users', 'staff', 'camels', 'health', 'feed',
            'milk-collection', 'products', 'customers', 'orders', 'delivery',
            'subscriptions', 'finance', 'reports', 'sales', 'salary', 'settings', 'notifications', 'change-password'
        ],
        'admin' => [
            'dashboard', 'staff', 'camels', 'health', 'feed', 'milk-collection',
            'products', 'customers', 'orders', 'delivery', 'subscriptions',
            'sales', 'notifications', 'change-password'
        ],
        'manager' => [
            'dashboard', 'staff', 'camels', 'health', 'feed', 'milk-collection',
            'products', 'customers', 'orders', 'delivery',
            'sales', 'notifications', 'change-password'
        ],
        'staff' => [
            'dashboard', 'camels', 'health', 'feed', 'milk-collection',
            'sales', 'notifications', 'change-password'
        ],
        'vet' => [
            'dashboard', 'camels', 'health', 'feed', 'breeding', 'notifications', 'change-password'
        ],
        'driver' => [
            'dashboard', 'delivery', 'notifications', 'change-password'
        ]
    ];
    
    $allowed_pages = $role_permissions[$current_role] ?? ['dashboard'];
    if (!in_array($page, $allowed_pages)) {
        $_SESSION['message'] = "Waad ku mahadsan tahay, laakiin ma haysatid ogolaansho aad ku aragto boggan (Awoodda: " . ucfirst($current_role) . ").";
        $_SESSION['message_type'] = "danger";
        header('Location: ?page=dashboard');
        exit;
    }
    
    // Buffer the content
    ob_start();
    
    // Determine which content file to include
    if (in_array($page, ['dashboard', 'users', 'staff', 'camels', 'health', 'breeding', 'feed', 'change-password'])) {
        require_once 'admin_pages.php';
    } elseif (in_array($page, ['milk-collection', 'products'])) {
        require_once 'processing_pages.php';
    } elseif (in_array($page, ['orders', 'delivery', 'subscriptions', 'finance', 'reports', 'sales', 'salary', 'settings', 'customers', 'notifications'])) {
        require_once 'sales_pages.php';
    } else {
        // Attempt to include a page-specific file if it exists
        $page_file = $page . '.php';
        if (file_exists($page_file)) {
            require_once $page_file;
        } else {
            // Show 404 page
            http_response_code(404);
            }

        }
    

    // Capture the content
    $page_content = ob_get_clean();

    


    
    // Include the layout with the content
    require_once 'layout.php';
    exit;
}

// If no page matched, show 404
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .error-container {
            text-align: center;
            color: white;
        }
        .error-container h1 {
            font-size: 6rem;
            font-weight: bold;
        }
        .error-container p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        .btn-home {
            background: white;
            color: #007bff;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            color: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <p>Oops! The page you're looking for doesn't exist.</p>
        <a href="?page=home" class="btn-home">Go to Homepage</a>
    </div>
</body>
</html>