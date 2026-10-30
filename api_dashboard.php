<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// API for Real-Time Dashboard Updates

require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!$logged_in) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$data = [];

try {
    $where = getBranchContextQuery();
    $where_d = getBranchContextQuery('d');
    $where_o = getBranchContextQuery('o');
    $where_s = getBranchContextQuery('s');
    $where_c = getBranchContextQuery('c');

    // Shared Notifications
    $data['unread_notifications'] = getUnreadNotificationsCount($pdo, $_SESSION['user_id']);
    $data['notifications'] = getUserNotifications($pdo, $_SESSION['user_id'], 5);

    if ($current_role == 'driver') {
        $uid = intval($_SESSION['user_id']);
        $data['assigned_orders'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'assigned'")->fetchColumn() ?: 0;
        $data['pending_orders'] = $data['assigned_orders'];
        $data['accepted_orders'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'in_transit'")->fetchColumn() ?: 0;
        $data['completed_orders'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'delivered'")->fetchColumn() ?: 0;
        $data['canceled_orders'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'cancelled'")->fetchColumn() ?: 0;
        $data['today_deliveries'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'delivered' AND DATE(delivered_at) = CURDATE()")->fetchColumn() ?: 0;
        $data['today_cancellations'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'cancelled' AND DATE(cancelled_at) = CURDATE()")->fetchColumn() ?: 0;

        $recent = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.address 
                               FROM deliveries d 
                               JOIN orders o ON d.order_id = o.id 
                               JOIN customers c ON o.customer_id = c.id 
                               WHERE d.driver_id = $uid AND d.status IN ('assigned', 'in_transit', 'delivered', 'cancelled') 
                               ORDER BY COALESCE(d.delivered_at, d.cancelled_at, d.accepted_at, d.assigned_at, d.delivery_date) DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $data['recent_activity'] = $recent ? $recent : [];

        $queue = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.address, c.phone as customer_phone
                              FROM deliveries d 
                              JOIN orders o ON d.order_id = o.id 
                              JOIN customers c ON o.customer_id = c.id 
                              WHERE d.driver_id = $uid AND d.status IN ('assigned', 'in_transit') 
                              ORDER BY d.delivery_date ASC")->fetchAll(PDO::FETCH_ASSOC);
        $data['active_queue'] = $queue ? $queue : [];

    } else {
        // Admin & Super Admin Stats
        $data['pending_orders'] = intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status='pending'")->fetchColumn() ?: 0);
        $data['active_deliveries'] = intval($pdo->query("SELECT COUNT(*) FROM deliveries d WHERE $where_d AND status IN ('assigned', 'in_transit')")->fetchColumn() ?: 0);
        $data['completed_today'] = intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status='delivered' AND DATE(delivered_at) = CURDATE()")->fetchColumn() ?: 0);
        $data['sales_today'] = floatval($pdo->query("SELECT SUM(total_amount) FROM orders o WHERE $where_o AND status='delivered' AND DATE(delivered_at) = CURDATE()")->fetchColumn() ?: 0);
        $data['global_sales'] = floatval($pdo->query("SELECT SUM(total_amount) FROM orders o WHERE $where_o AND status='delivered'")->fetchColumn() ?: 0);
        $data['global_milk'] = floatval($pdo->query("SELECT SUM(quantity_liters) FROM milk_collection WHERE collection_date = CURDATE() AND $where")->fetchColumn() ?: 0);
        $data['global_users'] = intval($pdo->query("SELECT COUNT(*) FROM customers WHERE $where")->fetchColumn() ?: 0);
        $data['count_products'] = intval($pdo->query("SELECT COUNT(*) FROM products WHERE status='active' AND $where")->fetchColumn() ?: 0);
        $data['count_camels'] = intval($pdo->query("SELECT COUNT(*) FROM camels WHERE status='active' AND $where")->fetchColumn() ?: 0);
        $data['active_orders'] = intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status IN ('pending', 'confirmed')")->fetchColumn() ?: 0);
    }

    // Chart Datasets (Last 7 Days)
    $chart_labels = [];
    $chart_sales = [];
    $chart_revenue = [];
    $chart_milk = [];
    $chart_customers = [];
    $chart_active_orders = [];
    $chart_delivered = [];
    $chart_camels = [];

    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('D (d/m)', strtotime($d));

        // Sales count on day
        $stmtSc = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(order_date) = ? AND $where");
        $stmtSc->execute([$d]);
        $chart_sales[] = intval($stmtSc->fetchColumn() ?: 0);

        // Revenue amount on day
        $stmtRev = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE status='delivered' AND DATE(delivered_at) = ? AND $where");
        $stmtRev->execute([$d]);
        $chart_revenue[] = floatval($stmtRev->fetchColumn() ?: 0);

        // Milk quantity on day
        $stmtM = $pdo->prepare("SELECT SUM(quantity_liters) FROM milk_collection WHERE collection_date = ? AND $where");
        $stmtM->execute([$d]);
        $chart_milk[] = floatval($stmtM->fetchColumn() ?: 0);

        // Customers total up to day (No created_at column in customers)
        $stmtC = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE $where");
        $stmtC->execute();
        $chart_customers[] = intval($stmtC->fetchColumn() ?: 0);

        // Active orders total up to day
        $stmtAo = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'confirmed') AND DATE(order_date) <= ? AND $where_o");
        $stmtAo->execute([$d]);
        $chart_active_orders[] = intval($stmtAo->fetchColumn() ?: 0);

        // Deliveries on day
        $stmtDo = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status='delivered' AND DATE(delivered_at) = ? AND $where_o");
        $stmtDo->execute([$d]);
        $chart_delivered[] = intval($stmtDo->fetchColumn() ?: 0);

        // Camels active up to day
        $stmtCa = $pdo->prepare("SELECT COUNT(*) FROM camels WHERE status='active' AND DATE(created_at) <= ? AND $where");
        $stmtCa->execute([$d]);
        $chart_camels[] = intval($stmtCa->fetchColumn() ?: 0);
    }

    $data['chart_labels'] = $chart_labels;
    $data['chart_sales'] = $chart_sales;
    $data['chart_revenue'] = $chart_revenue;
    $data['chart_milk'] = $chart_milk;
    $data['chart_customers'] = $chart_customers;
    
    // New 7-day trend arrays for sparklines
    $data['chart_active_orders'] = $chart_active_orders;
    $data['chart_delivered'] = $chart_delivered;
    $data['chart_camels'] = $chart_camels;

    $data['chart_orders'] = [
        'pending' => intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status='pending'")->fetchColumn() ?: 0),
        'completed' => intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status='delivered'")->fetchColumn() ?: 0),
        'canceled' => intval($pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status='cancelled'")->fetchColumn() ?: 0)
    ];

    // Inventory Stock Chart
    $data['chart_inventory'] = $pdo->query("SELECT name, stock_quantity FROM products WHERE status='active' AND $where ORDER BY stock_quantity DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);



    // Delivery Status Chart
    $data['chart_delivery'] = [
        'assigned' => intval($pdo->query("SELECT COUNT(*) FROM deliveries d WHERE $where_d AND status='assigned'")->fetchColumn() ?: 0),
        'in_transit' => intval($pdo->query("SELECT COUNT(*) FROM deliveries d WHERE $where_d AND status='in_transit'")->fetchColumn() ?: 0),
        'delivered' => intval($pdo->query("SELECT COUNT(*) FROM deliveries d WHERE $where_d AND status='delivered'")->fetchColumn() ?: 0),
        'cancelled' => intval($pdo->query("SELECT COUNT(*) FROM deliveries d WHERE $where_d AND status='cancelled'")->fetchColumn() ?: 0)
    ];

    // Recent Activities Feed for Dashboard
    $activities = [];
    try {
        $recent_orders = $pdo->query("SELECT o.order_number, o.total_amount, o.status, o.order_date as created_at, c.name as customer_name FROM orders o JOIN customers c ON o.customer_id = c.id WHERE $where_o ORDER BY o.id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recent_orders as $ro) {
            $activities[] = [
                'type' => 'order',
                'icon' => 'bi-cart-check',
                'badge' => 'primary',
                'title' => 'Order #' . $ro['order_number'],
                'desc' => $ro['customer_name'] . ' • $' . number_format($ro['total_amount'], 2),
                'status' => ucfirst($ro['status']),
                'time' => date('h:i A', strtotime($ro['created_at'])),
                'timestamp' => strtotime($ro['created_at'])
            ];
        }
    } catch (Exception $e) {}

    try {
        $recent_milk = $pdo->query("SELECT mc.quantity_liters, mc.shift, mc.collection_date, c.name as camel_name FROM milk_collection mc JOIN camels c ON mc.camel_id = c.id WHERE $where ORDER BY mc.id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recent_milk as $rm) {
            $activities[] = [
                'type' => 'milk',
                'icon' => 'bi-droplet-fill',
                'badge' => 'success',
                'title' => 'Milk Harvest (' . ucfirst($rm['shift']) . ')',
                'desc' => $rm['quantity_liters'] . 'L from ' . $rm['camel_name'],
                'status' => 'Collected',
                'time' => date('d M', strtotime($rm['collection_date'])),
                'timestamp' => strtotime($rm['collection_date'])
            ];
        }
    } catch (Exception $e) {}

    try {
        $recent_cust = $pdo->query("SELECT name, created_at FROM customers WHERE $where ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recent_cust as $rc) {
            $activities[] = [
                'type' => 'customer',
                'icon' => 'bi-person-plus',
                'badge' => 'info',
                'title' => 'New Customer',
                'desc' => $rc['name'] . ' joined system',
                'status' => 'Active',
                'time' => date('h:i A', strtotime($rc['created_at'] ?? 'now')),
                'timestamp' => strtotime($rc['created_at'] ?? 'now')
            ];
        }
    } catch (Exception $e) {}

    usort($activities, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    $data['recent_activities'] = array_slice($activities, 0, 6);

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
