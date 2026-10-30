<?php
require 'config.php';
require 'functions.php';

// Simulate session
$_SESSION['role'] = 'super_admin';
$_SESSION['branch_id'] = 1;

try {
    $branch_context = getBranchContextQuery();
    $where_o = getBranchContextQuery('o');
    $where_d = getBranchContextQuery('d');
    $where_cs = getBranchContextQuery('cs');
    $where_si = getBranchContextQuery('si');
    $where_p = getBranchContextQuery('p');
    $where_sp = getBranchContextQuery('sp');
    $where_sd = getBranchContextQuery('sd');
    $where_sb = getBranchContextQuery('sb');
    $where_st = getBranchContextQuery('st');
    $where_sc = getBranchContextQuery('sc');
    $where_dp = getBranchContextQuery('dp');
    $where_s = getBranchContextQuery('s');
    
    echo "Testing queries...\n";
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->query("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE $where_o ORDER BY o.id DESC LIMIT 10"); echo "1 OK\n";
    $pdo->query("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE $where_o ORDER BY o.id DESC"); echo "2 OK\n";
    $pdo->query("SELECT u.* FROM users u WHERE role='driver' AND ({$branch_context} OR u.branch_id IS NULL)"); echo "3 OK\n";
    $pdo->query("SELECT * FROM delivery_zones WHERE {$branch_context}"); echo "4 OK\n";
    $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.phone as customer_phone FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE $where_d AND d.status IN ('assigned', 'in_transit') ORDER BY d.delivery_date"); echo "5 OK\n";
    $pdo->query("SELECT o.*, c.name as customer_name, c.phone as customer_phone FROM orders o JOIN customers c ON o.customer_id = c.id WHERE $where_o AND o.status = 'cancellation_requested' ORDER BY o.order_date DESC"); echo "6 OK\n";
    $pdo->query("SELECT cs.*, c.name as customer_name, pl.name as plan_name FROM customer_subscriptions cs JOIN customers c ON cs.customer_id = c.id JOIN subscription_plans pl ON cs.plan_id = pl.id WHERE $where_cs ORDER BY cs.start_date DESC"); echo "7 OK\n";
    $pdo->query("SELECT * FROM subscription_plans WHERE {$branch_context} AND status='active'"); echo "8 OK\n";
    $pdo->query("SELECT si.*, cs.customer_id, c.name as customer_name FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id JOIN customers c ON cs.customer_id = c.id WHERE $where_si ORDER BY si.invoice_date DESC"); echo "9 OK\n";
    $pdo->query("SELECT p.*, o.order_number, c.name as customer_name FROM payments p JOIN orders o ON p.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE $where_p ORDER BY p.payment_date DESC"); echo "10 OK\n";
    $pdo->query("SELECT * FROM expenses WHERE {$branch_context} ORDER BY expense_date DESC"); echo "11 OK\n";
    $pdo->query("SELECT sp.*, s.employee_id, s.position FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE $where_sp ORDER BY sp.payment_date DESC"); echo "12 OK\n";
    $pdo->query("SELECT sd.*, s.employee_id, s.position FROM salary_deductions sd JOIN staff s ON sd.staff_id = s.id WHERE $where_sd ORDER BY sd.year DESC, sd.month DESC"); echo "13 OK\n";
    $pdo->query("SELECT sb.*, s.employee_id, s.position FROM salary_bonuses sb JOIN staff s ON sb.staff_id = s.id WHERE $where_sb ORDER BY sb.year DESC, sb.month DESC"); echo "14 OK\n";
    $pdo->query("SELECT p.*, s.employee_id, s.position FROM payslips p JOIN staff s ON p.staff_id = s.id WHERE $where_p ORDER BY p.generated_date DESC"); echo "15 OK\n";
    $pdo->query("SELECT st.*, s.position as staff_name FROM sales_targets st LEFT JOIN staff s ON st.staff_id = s.id WHERE $where_st ORDER BY st.year DESC, st.month DESC"); echo "16 OK\n";
    $pdo->query("SELECT sc.*, u.full_name as staff_name, o.order_number FROM sales_commissions sc LEFT JOIN staff s ON sc.staff_id = s.id LEFT JOIN users u ON s.user_id = u.id LEFT JOIN orders o ON sc.order_id = o.id WHERE $where_sc ORDER BY sc.calculated_date DESC"); echo "17 OK\n";
    $pdo->query("SELECT dp.*, u.full_name as driver_name FROM driver_performance dp JOIN users u ON dp.driver_id = u.id WHERE $where_dp ORDER BY dp.delivery_date DESC"); echo "18 OK\n";
    $pdo->query("SELECT dp.*, d.order_id, o.order_number FROM delivery_proofs dp JOIN deliveries d ON dp.delivery_id = d.id JOIN orders o ON d.order_id = o.id WHERE $where_dp ORDER BY dp.uploaded_at DESC"); echo "19 OK\n";
    $pdo->query("SELECT * FROM products WHERE {$branch_context} AND status = 'active' ORDER BY name"); echo "20 OK\n";
    $pdo->query("SELECT * FROM customers WHERE {$branch_context} ORDER BY name"); echo "21 OK\n";
    
    $staff_list = $pdo->query("SELECT s.*, u.full_name FROM staff s LEFT JOIN users u ON s.user_id = u.id WHERE $where_s ORDER BY s.employee_id")->fetchAll(PDO::FETCH_ASSOC);
    echo "22 OK. Staff count: " . count($staff_list) . "\n";
    
} catch (PDOException $e) {
    echo "SQL ERROR: " . $e->getMessage() . "\n";
}
?>
