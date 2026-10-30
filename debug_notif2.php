<?php
require_once 'config.php';
require_once 'functions.php';

echo "<pre>";

// 1. Show notifications table structure
echo "=== NOTIFICATIONS TABLE STRUCTURE ===\n";
$cols = $pdo->query('DESCRIBE notifications')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) {
    echo $c['Field'] . ' - ' . $c['Type'] . "\n";
}

// 2. Show all notifications (last 20)
echo "\n=== LAST 20 NOTIFICATIONS ===\n";
$rows = $pdo->query('SELECT * FROM notifications ORDER BY id DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo "ID:{$r['id']} user_id:{$r['user_id']} title:{$r['title']} branch_id:" . ($r['branch_id'] ?? 'NULL') . " read:{$r['is_read']} created:{$r['created_at']}\n";
}

// 3. Show drivers
echo "\n=== DRIVERS (users with role=driver) ===\n";
$drivers = $pdo->query("SELECT u.id, u.username, u.full_name, u.role, u.branch_id FROM users u WHERE u.role='driver'")->fetchAll(PDO::FETCH_ASSOC);
foreach($drivers as $d) {
    echo "user_id:{$d['id']} username:{$d['username']} name:{$d['full_name']} branch:{$d['branch_id']}\n";
}

// 4. Show staff table for drivers
echo "\n=== STAFF TABLE for drivers ===\n";
$staff = $pdo->query("SELECT s.id as staff_id, s.user_id, s.name, s.position, s.branch_id FROM staff s WHERE s.position LIKE '%driver%' OR s.user_id IN (SELECT id FROM users WHERE role='driver')")->fetchAll(PDO::FETCH_ASSOC);
foreach($staff as $s) {
    echo "staff_id:{$s['staff_id']} user_id:" . ($s['user_id'] ?? 'NULL') . " name:{$s['name']} position:{$s['position']} branch:" . ($s['branch_id'] ?? 'NULL') . "\n";
}

// 5. Show deliveries
echo "\n=== DELIVERIES (last 10) ===\n";
$del = $pdo->query('SELECT id, order_id, driver_id, status, branch_id FROM deliveries ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
foreach($del as $d) {
    echo "del_id:{$d['id']} order_id:{$d['order_id']} driver_id:{$d['driver_id']} status:{$d['status']} branch:" . ($d['branch_id'] ?? 'NULL') . "\n";
}

// 6. Check what the assign_delivery form sends as driver_id
echo "\n=== DRIVER SELECT OPTIONS (from delivery assign page) ===\n";
// Check which query is used for the driver dropdown
$drv1 = $pdo->query("SELECT id, username, full_name FROM users WHERE role='driver'")->fetchAll(PDO::FETCH_ASSOC);
echo "From users table (role=driver):\n";
foreach($drv1 as $d) {
    echo "  id:{$d['id']} name:{$d['full_name']}\n";
}

$drv2 = $pdo->query("SELECT s.id as staff_id, s.user_id, s.name FROM staff s WHERE s.position LIKE '%driver%'")->fetchAll(PDO::FETCH_ASSOC);
echo "From staff table (position=driver):\n";
foreach($drv2 as $d) {
    echo "  staff_id:{$d['staff_id']} user_id:" . ($d['user_id'] ?? 'NULL') . " name:{$d['name']}\n";
}

// 7. Check notifications for driver user IDs specifically
echo "\n=== NOTIFICATIONS FOR DRIVER USER IDs ===\n";
foreach($drivers as $d) {
    $count = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
    $count->execute([$d['id']]);
    echo "Driver user_id:{$d['id']} ({$d['full_name']}) has " . $count->fetchColumn() . " notifications\n";
    
    $notifs = $pdo->prepare("SELECT id, title, message, created_at FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 5");
    $notifs->execute([$d['id']]);
    $nlist = $notifs->fetchAll(PDO::FETCH_ASSOC);
    foreach($nlist as $n) {
        echo "  - [{$n['id']}] {$n['title']}: {$n['message']} ({$n['created_at']})\n";
    }
}

echo "</pre>";
?>
