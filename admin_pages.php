<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Admin Pages - User, Staff, Camel, Health, Breeding, Feed Management

// Make sure variables are defined
global $page, $subpage, $pdo, $logged_in, $current_role, $message, $message_type;

if (!isset($page)) $page = $_GET['page'] ?? 'dashboard';
if (!isset($subpage)) $subpage = $_GET['subpage'] ?? 'list';

if ($logged_in && $current_role != 'customer') {
    
    // Get data for dropdowns
    try {
        $where = getBranchContextQuery();
        $where_u = getBranchContextQuery('u');
        $where_lh = getBranchContextQuery('lh');
        $where_s = getBranchContextQuery('s');
        $where_c = getBranchContextQuery('c');
        $where_co = getBranchContextQuery('co');
        $where_dr = getBranchContextQuery('dr');
        $where_v = getBranchContextQuery('v');
        $where_vv = getBranchContextQuery('vv');
        $where_br = getBranchContextQuery('br');
        $where_pt = getBranchContextQuery('pt');
        $where_cl = getBranchContextQuery('cl');
        $where_fr = getBranchContextQuery('fr');
        $where_fp = getBranchContextQuery('fp');
        $where_fc = getBranchContextQuery('fc');
        $where_lr = getBranchContextQuery('lr');

        $departments = $pdo->query("SELECT * FROM departments WHERE $where")->fetchAll(PDO::FETCH_ASSOC);
        $staff_list = $pdo->query("SELECT s.*, COALESCE(u.full_name, s.email, s.employee_id) as name, d.name as department_name FROM staff s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN departments d ON s.department_id = d.id WHERE $where_s")->fetchAll(PDO::FETCH_ASSOC);
        $users = $pdo->query("SELECT * FROM users WHERE $where")->fetchAll(PDO::FETCH_ASSOC);
        $login_history = $pdo->query("SELECT lh.*, u.username, u.full_name FROM login_history lh JOIN users u ON lh.user_id = u.id WHERE $where_lh ORDER BY lh.login_time DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
        $camels = $pdo->query("SELECT * FROM camels WHERE status='active' AND $where")->fetchAll(PDO::FETCH_ASSOC);
        $all_camels = $pdo->query("SELECT * FROM camels WHERE $where ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $camel_ownership = $pdo->query("SELECT co.*, c.tag_id, c.name as camel_name FROM camel_ownership co JOIN camels c ON co.camel_id = c.id WHERE $where_co ORDER BY co.purchase_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $disease_records = $pdo->query("SELECT dr.*, c.tag_id, c.name as camel_name FROM disease_records dr JOIN camels c ON dr.camel_id = c.id WHERE $where_dr ORDER BY dr.diagnosed_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $vaccinations = $pdo->query("SELECT v.*, c.tag_id, c.name as camel_name FROM vaccinations v JOIN camels c ON v.camel_id = c.id WHERE $where_v ORDER BY v.vaccination_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $vet_visits = $pdo->query("SELECT vv.*, c.tag_id, c.name as camel_name FROM vet_visits vv JOIN camels c ON vv.camel_id = c.id WHERE $where_vv ORDER BY vv.visit_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $breeding_records = $pdo->query("SELECT br.*, fc.tag_id as female_tag, fc.name as female_name, mc.tag_id as male_tag, mc.name as male_name FROM breeding_records br JOIN camels fc ON br.female_camel_id = fc.id LEFT JOIN camels mc ON br.male_camel_id = mc.id WHERE $where_br ORDER BY br.breeding_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $pregnancy_tracking = $pdo->query("SELECT pt.*, c.tag_id, c.name as camel_name FROM pregnancy_tracking pt JOIN camels c ON pt.camel_id = c.id WHERE $where_pt ORDER BY pt.check_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $calves = $pdo->query("SELECT cl.*, c.tag_id as mother_tag, c.name as mother_name FROM calves cl LEFT JOIN camels c ON cl.mother_id = c.id WHERE $where_cl ORDER BY cl.birth_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $fertility_reports = $pdo->query("SELECT fr.*, c.tag_id, c.name as camel_name FROM fertility_reports fr JOIN camels c ON fr.camel_id = c.id WHERE $where_fr ORDER BY fr.report_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $feed_types = $pdo->query("SELECT * FROM feed_types WHERE $where")->fetchAll(PDO::FETCH_ASSOC);
        $feed_plans = $pdo->query("SELECT fp.*, ft.name as feed_type_name FROM feed_plans fp LEFT JOIN feed_types ft ON fp.feed_type_id = ft.id WHERE $where_fp")->fetchAll(PDO::FETCH_ASSOC);
        $feed_consumption = $pdo->query("SELECT fc.*, c.tag_id, c.name as camel_name, ft.name as feed_type_name FROM feed_consumption fc JOIN camels c ON fc.camel_id = c.id JOIN feed_types ft ON fc.feed_type_id = ft.id WHERE $where_fc ORDER BY fc.date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $feed_suppliers = $pdo->query("SELECT * FROM feed_suppliers WHERE $where")->fetchAll(PDO::FETCH_ASSOC);
        $feed_purchases = $pdo->query("SELECT fp.*, fs.name as supplier_name, ft.name as feed_type_name FROM feed_purchases fp JOIN feed_suppliers fs ON fp.supplier_id = fs.id JOIN feed_types ft ON fp.feed_type_id = ft.id WHERE $where_fp ORDER BY fp.purchase_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $leave_requests = $pdo->query("SELECT lr.*, s.employee_id, s.position FROM leave_requests lr JOIN staff s ON lr.staff_id = s.id WHERE $where_lr ORDER BY lr.start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $products = $pdo->query("SELECT * FROM products WHERE status='active' AND $where")->fetchAll(PDO::FETCH_ASSOC);
        $customers = $pdo->query("SELECT * FROM customers WHERE status='active' AND $where")->fetchAll(PDO::FETCH_ASSOC);
        $roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
        $all_branches = $pdo->query("SELECT * FROM branches")->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        $settings_result = $pdo->query("SELECT * FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($settings_result as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $unread_notifications = getUnreadNotificationsCount($pdo, $_SESSION['user_id']);
        $total_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered' AND $where")->fetchColumn() ?: 0;
        $today_milk = $pdo->query("SELECT SUM(quantity_liters) FROM milk_collection WHERE collection_date = CURDATE() AND $where")->fetchColumn() ?: 0;
        
        // Count for widgets
        $count_users = $pdo->query("SELECT COUNT(*) FROM users WHERE $where")->fetchColumn() ?: 0;
        $count_camels = $pdo->query("SELECT COUNT(*) FROM camels WHERE status='active' AND $where")->fetchColumn() ?: 0;
        $count_customers = $pdo->query("SELECT COUNT(*) FROM customers WHERE status='active' AND $where")->fetchColumn() ?: 0;
        $count_products = $pdo->query("SELECT COUNT(*) FROM products WHERE status='active' AND $where")->fetchColumn() ?: 0;
        $count_feed_plans = $pdo->query("SELECT COUNT(*) FROM feed_plans WHERE $where_fp")->fetchColumn() ?: 0;
        
    } catch (PDOException $e) {
        $unread_notifications = 0;
        $total_sales = 0;
        $today_milk = 0;
    }
    
    // Dashboard
    if ($page == 'dashboard') {
        // Query extra dashboard metrics depending on roles
        $extra_metrics = [];
        try {
            if ($current_role == 'super_admin') {
                $extra_metrics['branches_count'] = $pdo->query("SELECT COUNT(*) FROM branches")->fetchColumn() ?: 0;
                $extra_metrics['global_camels'] = $pdo->query("SELECT COUNT(*) FROM camels")->fetchColumn() ?: 0;
                $extra_metrics['global_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
                $extra_metrics['global_sales'] = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'")->fetchColumn() ?: 0;
                $extra_metrics['global_milk'] = $pdo->query("SELECT SUM(quantity_liters) FROM milk_collection WHERE collection_date = CURDATE()")->fetchColumn() ?: 0;
                $extra_metrics['active_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'confirmed')")->fetchColumn() ?: 0;
                $extra_metrics['completed_today'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered' AND DATE(delivered_at) = CURDATE()")->fetchColumn() ?: 0;
                $extra_metrics['count_camels'] = $count_camels;
                
                // Get branch comparison
                $extra_metrics['branch_stats'] = $pdo->query("
                    SELECT b.id, b.name, b.location,
                           (SELECT COUNT(*) FROM camels c WHERE c.branch_id = b.id) as camel_count,
                           (SELECT COUNT(*) FROM staff s WHERE s.branch_id = b.id) as staff_count,
                           (SELECT COUNT(*) FROM orders o WHERE o.branch_id = b.id AND o.status='delivered') as order_count,
                           (SELECT SUM(total_amount) FROM orders o WHERE o.branch_id = b.id AND o.status='delivered') as sales_amount
                    FROM branches b
                    ORDER BY b.name ASC
                ")->fetchAll(PDO::FETCH_ASSOC);
            }
            elseif ($current_role == 'admin' || $current_role == 'manager') {
                $branch_id = $_SESSION['branch_id'] ?? null;
                $branch_condition = $branch_id ? "branch_id = " . intval($branch_id) : "1=0";
                $extra_metrics['branch_sales'] = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status='delivered' AND $branch_condition")->fetchColumn() ?: 0;
                $extra_metrics['branch_camels'] = $pdo->query("SELECT COUNT(*) FROM camels WHERE status='active' AND $branch_condition")->fetchColumn() ?: 0;
                $extra_metrics['branch_customers'] = $pdo->query("SELECT COUNT(*) FROM customers WHERE status='active' AND $branch_condition")->fetchColumn() ?: 0;
                $extra_metrics['branch_deliveries'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE status IN ('assigned','in_transit') AND $branch_condition")->fetchColumn() ?: 0;
                $extra_metrics['branch_feed_plans'] = $pdo->query("SELECT COUNT(*) FROM feed_plans WHERE $branch_condition")->fetchColumn() ?: 0;
            }
            elseif ($current_role == 'staff') {
                $branch_id = $_SESSION['branch_id'] ?? 1;
                $extra_metrics['staff_camels'] = $pdo->query("SELECT COUNT(*) FROM camels WHERE branch_id = $branch_id AND status='active'")->fetchColumn() ?: 0;
                $extra_metrics['staff_milk_today'] = $pdo->query("SELECT SUM(quantity_liters) FROM milk_collection WHERE branch_id = $branch_id AND collection_date = CURDATE()")->fetchColumn() ?: 0;
                $extra_metrics['staff_feed_today'] = $pdo->query("SELECT COUNT(*) FROM feed_consumption WHERE date = CURDATE() AND camel_id IN (SELECT id FROM camels WHERE branch_id = $branch_id)")->fetchColumn() ?: 0;
            }
            elseif ($current_role == 'vet') {
                $extra_metrics['total_camels'] = $pdo->query("SELECT COUNT(*) FROM camels WHERE status='active'")->fetchColumn() ?: 0;
                $extra_metrics['sick_camels'] = $pdo->query("SELECT COUNT(DISTINCT camel_id) FROM disease_records WHERE (recovery_date IS NULL OR recovery_date = '' OR recovery_date = '0000-00-00')")->fetchColumn() ?: 0;
                $extra_metrics['vaccinations_today'] = $pdo->query("SELECT COUNT(*) FROM vaccinations WHERE vaccination_date = CURDATE()")->fetchColumn() ?: 0;
                $extra_metrics['vet_visits_today'] = $pdo->query("SELECT COUNT(*) FROM vet_visits WHERE visit_date = CURDATE()")->fetchColumn() ?: 0;
                $extra_metrics['pregnant_camels'] = $pdo->query("SELECT COUNT(DISTINCT camel_id) FROM pregnancy_tracking WHERE status='pregnant'")->fetchColumn() ?: 0;
            }
            elseif ($current_role == 'driver') {
                $uid = $_SESSION['user_id'];
                $extra_metrics['assigned_orders'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'assigned'")->fetchColumn() ?: 0;
                $extra_metrics['accepted_orders'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'in_transit'")->fetchColumn() ?: 0;
                $extra_metrics['today_completed'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'delivered' AND DATE(delivered_at) = CURDATE()")->fetchColumn() ?: 0;
                $extra_metrics['today_cancelled'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'cancelled' AND DATE(cancelled_at) = CURDATE()")->fetchColumn() ?: 0;
                $extra_metrics['total_completed'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'delivered'")->fetchColumn() ?: 0;
                $extra_metrics['total_cancelled'] = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE driver_id = $uid AND status = 'cancelled'")->fetchColumn() ?: 0;
            }
        } catch (PDOException $e) {
            // Gracefully ignore db schema gaps in other developer versions
        }
        ?>
        <div class="page-title">
            <h2><?php echo ucfirst(str_replace('_', ' ', $current_role)); ?> Dashboard</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Dashboard</span>
            </div>
        </div>
        
        <div class="row mb-2">
            <div class="col-12">
                <div class="card border-0 shadow-lg text-white p-2" style="background: linear-gradient(135deg, #1e1e2d 0%, #2d2d44 100%); border-radius: 18px;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h2 class="mb-0 text-white fw-bold">Ku soo dhowow, <?php echo $_SESSION['full_name']; ?>! 👋</h2>
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-broadcast me-1 spinner-grow spinner-grow-sm" style="width: 8px; height: 8px;"></i> Live Auto-Sync
                                </span>
                            </div>
                            <p class="mb-0 text-white-50">Heerka Awooddaada: <strong class="text-warning"><?php echo ucfirst(str_replace('_', ' ', $current_role)); ?></strong>. Halkan kala soco dhammaan shaqooyinka laguu xilsaaray maanta.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($current_role == 'super_admin' || $current_role == 'admin' || $current_role == 'manager' || $current_role == 'staff'): ?>
                                <a href="?page=sales&subpage=pos" class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm"><i class="bi bi-cart-plus me-1"></i> POS Sale</a>
                                <a href="?page=milk-collection&subpage=add" class="btn btn-success btn-sm px-3 rounded-pill shadow-sm"><i class="bi bi-droplet-fill me-1"></i> Record Milk</a>
                            <?php elseif ($current_role == 'vet'): ?>
                                <a href="?page=health&subpage=add-disease" class="btn btn-danger btn-sm px-3 rounded-pill shadow-sm"><i class="bi bi-bug me-1"></i> Log Disease</a>
                                <a href="?page=health&subpage=add-vaccination" class="btn btn-info btn-sm px-3 rounded-pill shadow-sm"><i class="bi bi-shield-plus me-1"></i> Vaccinate</a>
                            <?php elseif ($current_role == 'driver'): ?>
                                <a href="?page=delivery&subpage=tracking" class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm"><i class="bi bi-geo me-1"></i> Track Deliveries</a>
                                <a href="?page=delivery&subpage=proof" class="btn btn-success btn-sm px-3 rounded-pill shadow-sm"><i class="bi bi-camera me-1"></i> Upload Proof</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <?php if ($current_role == 'super_admin'): ?>
        <!-- ==================== SUPER ADMIN REAL-TIME DASHBOARD ==================== -->
        
        <!-- Row 1: Real-Time Stats Metric Cards -->
        <div class="row g-2 mb-2">
            <!-- Card 1: Total Revenue -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-card text-white p-2 shadow-sm border-0" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Revenue</p>
                            <h3 class="mb-0 fw-bold" id="dash_global_sales">$<?php echo number_format($extra_metrics['global_sales'] ?? 0, 2); ?></h3>
                        </div>
                        <div class="p-2 rounded-circle bg-white bg-opacity-20 fs-4"><i class="bi bi-currency-dollar"></i></div>
                    </div>
                    <div class="mt-2" style="height: 30px; position: relative;">
                        <canvas id="chart_sales_spark"></canvas>
                    </div>
                </div>
            </div>

            <!-- Card 2: Today's Milk Harvest -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-card text-white p-2 shadow-sm border-0" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Milk Harvest Today</p>
                            <h3 class="mb-0 fw-bold" id="dash_global_milk"><?php echo number_format($extra_metrics['global_milk'] ?? 0, 1); ?> L</h3>
                        </div>
                        <div class="p-2 rounded-circle bg-white bg-opacity-20 fs-4"><i class="bi bi-droplet-fill"></i></div>
                    </div>
                    <div class="mt-2" style="height: 30px; position: relative;">
                        <canvas id="chart_milk_spark"></canvas>
                    </div>
                </div>
            </div>

            <!-- Card 3: Customers & Users -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-card text-white p-2 shadow-sm border-0" style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Customers & Users</p>
                            <h3 class="mb-0 fw-bold" id="dash_global_users"><?php echo number_format($extra_metrics['global_users'] ?? 0); ?></h3>
                        </div>
                        <div class="p-2 rounded-circle bg-white bg-opacity-20 fs-4"><i class="bi bi-people-fill"></i></div>
                    </div>
                    <div class="mt-2" style="height: 30px; position: relative;">
                        <canvas id="chart_customer_spark"></canvas>
                    </div>
                </div>
            </div>

            <!-- Card 4: Active Orders & Pending -->
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="?page=sales&subpage=deliveries" class="text-decoration-none">
                <div class="dashboard-card text-white p-2 shadow-sm border-0" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Active & Pending Orders</p>
                            <h3 class="mb-0 fw-bold" id="dash_active_orders"><?php echo number_format($extra_metrics['active_orders'] ?? 0); ?> Orders</h3>
                        </div>
                        <div class="p-2 rounded-circle bg-white bg-opacity-20 fs-4"><i class="bi bi-cart-clock-fill"></i></div>
                    </div>
                    <div class="mt-2" style="height: 30px; position: relative;">
                        <canvas id="chart_orders_spark"></canvas>
                    </div>
                </div>
                </a>
            </div>

            <!-- Card 5: Inventory Stock Items -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dashboard-card text-white p-2 shadow-sm border-0" style="background: linear-gradient(135deg, #6f42c1 0%, #4e268f 100%); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Products Inventory</p>
                            <h3 class="mb-0 fw-bold" id="dash_count_products"><?php echo number_format($count_products ?? 0); ?> Items</h3>
                        </div>
                        <div class="p-2 rounded-circle bg-white bg-opacity-20 fs-4"><i class="bi bi-box-seam-fill"></i></div>
                    </div>
                    <div class="mt-2" style="height: 30px; position: relative;">
                        <canvas id="chart_inventory_spark"></canvas>
                    </div>
                </div>
            </div>

            <!-- Card 6: Active Camels Herd -->
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="?page=camels&subpage=list" class="text-decoration-none">
                <div class="dashboard-card text-white p-2 shadow-sm border-0" style="background: linear-gradient(135deg, #fd7e14 0%, #ca5b00 100%); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Active Camels Herd</p>
                            <h3 class="mb-0 fw-bold" id="dash_count_camels"><?php echo number_format($extra_metrics['count_camels'] ?? 0); ?> Camels</h3>
                        </div>
                        <div class="p-2 rounded-circle bg-white bg-opacity-20 fs-4"><i class="bi bi-pin-map-fill"></i></div>
                    </div>
                    <div class="mt-2" style="height: 30px; position: relative;">
                        <canvas id="chart_camels_spark"></canvas>
                    </div>
                </div>
                </a>
            </div>



            <!-- Card 8: Delivered Today -->
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="?page=reports&subpage=delivery" class="text-decoration-none">
                <div class="dashboard-card text-white p-2 shadow-sm border-0" style="background: linear-gradient(135deg, #20c997 0%, #148060 100%); border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Delivered Today</p>
                            <h3 class="mb-0 fw-bold" id="dash_completed_today"><?php echo number_format($extra_metrics['completed_today'] ?? 0); ?> Delivered</h3>
                        </div>
                        <div class="p-2 rounded-circle bg-white bg-opacity-20 fs-4"><i class="bi bi-truck-flatbed"></i></div>
                    </div>
                    <div class="mt-2" style="height: 30px; position: relative;">
                        <canvas id="chart_delivery_spark"></canvas>
                    </div>
                </div>
                </a>
            </div>
        </div>

        <!-- Section Header for Task Management Charts -->
        <?php
        // Auto-update overdue tasks
        $pdo->query("UPDATE tasks SET status = 'overdue' WHERE status IN ('pending', 'in_progress') AND due_date < CURDATE()");

        // Fetch tasks
        $tasks_query = "SELECT t.*, u.full_name as assignee_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id ORDER BY FIELD(t.status, 'overdue', 'in_progress', 'pending', 'completed'), t.due_date ASC";
        $tasks = $pdo->query($tasks_query)->fetchAll(PDO::FETCH_ASSOC);

        // Fetch users for assignment dropdown
        $users_list = $pdo->query("SELECT id, full_name, role FROM users WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);

        // Task Stats
        $task_stats = [
            'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0
        ];
        foreach ($tasks as $t) {
            $task_stats[$t['status']]++;
        }
        ?>
        <div class="d-flex align-items-center justify-content-between mb-2 mt-2">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square me-2 text-primary"></i>Task Management</h4>
                <p class="text-muted small mb-0">Create, track, and manage team tasks.</p>
            </div>
            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class="bi bi-plus-lg me-1"></i> Add Task</button>
        </div>

        <!-- Task Stats -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100 rounded-4 border-start border-4 border-warning">
                    <h6 class="text-muted mb-1 small">Pending</h6>
                    <h3 class="mb-0 fw-bold"><?php echo $task_stats['pending']; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100 rounded-4 border-start border-4 border-info">
                    <h6 class="text-muted mb-1 small">In Progress</h6>
                    <h3 class="mb-0 fw-bold"><?php echo $task_stats['in_progress']; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100 rounded-4 border-start border-4 border-success">
                    <h6 class="text-muted mb-1 small">Completed</h6>
                    <h3 class="mb-0 fw-bold"><?php echo $task_stats['completed']; ?></h3>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100 rounded-4 border-start border-4 border-danger">
                    <h6 class="text-muted mb-1 small">Overdue</h6>
                    <h3 class="mb-0 fw-bold"><?php echo $task_stats['overdue']; ?></h3>
                </div>
            </div>
        </div>

        <!-- Task List -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Task</th>
                                <th>Assigned To</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tasks)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">No tasks found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($tasks as $task): 
                                    $status_badge = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'overdue' => 'danger'];
                                    $priority_badge = ['low' => 'secondary', 'medium' => 'primary', 'high' => 'danger'];
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($task['title']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($task['description']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($task['assignee_name']): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:28px;height:28px;font-size:12px;">
                                                    <?php echo strtoupper(substr($task['assignee_name'], 0, 1)); ?>
                                                </div>
                                                <span class="small fw-semibold"><?php echo htmlspecialchars($task['assignee_name']); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-<?php echo $priority_badge[$task['priority']]; ?> bg-opacity-10 text-<?php echo $priority_badge[$task['priority']]; ?> rounded-pill text-capitalize"><?php echo $task['priority']; ?></span></td>
                                    <td>
                                        <div class="small fw-semibold <?php echo ($task['status'] == 'overdue') ? 'text-danger' : 'text-muted'; ?>">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo $task['due_date'] ? date('d M Y', strtotime($task['due_date'])) : 'No Due Date'; ?>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-<?php echo $status_badge[$task['status']]; ?> rounded-pill text-capitalize px-3"><?php echo str_replace('_', ' ', $task['status']); ?></span></td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <form method="POST" class="m-0">
                                                <input type="hidden" name="action" value="update_task_status">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </form>
                                            <form method="POST" class="m-0" onsubmit="return confirm('Delete this task?');">
                                                <input type="hidden" name="action" value="delete_task">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Task Modal -->
        <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_task">
                        <div class="modal-header border-bottom-0 pb-0">
                            <h5 class="modal-title fw-bold">Create New Task</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Task Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control form-control-lg bg-light border-0" required placeholder="What needs to be done?">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Description</label>
                                <textarea name="description" class="form-control bg-light border-0" rows="3" placeholder="Add more details..."></textarea>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Assign To</label>
                                    <select name="assigned_to" class="form-select bg-light border-0">
                                        <option value="">-- Unassigned --</option>
                                        <?php foreach ($users_list as $u): ?>
                                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['full_name']); ?> (<?php echo ucfirst($u['role']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Priority</label>
                                    <select name="priority" class="form-select bg-light border-0">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Due Date</label>
                                <input type="date" name="due_date" class="form-control bg-light border-0">
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn text-muted fw-semibold px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Create Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Row 3: Real-Time Live Activities Stream & Notifications Grid -->
        <div class="row g-2 mb-2">
            <!-- Left Column: Live Activities Stream -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm p-2 h-100" style="border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-activity me-2 text-primary"></i>Live System Activity Stream</h6>
                        <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-record-fill me-1 text-success spinner-grow spinner-grow-sm" style="width: 8px; height: 8px;"></i> Real-Time</span>
                    </div>
                    <div id="dash_live_activities" class="pe-1">
                        <div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading live activity stream...</div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Notifications -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm p-2 h-100" style="border-radius: 16px;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bell-fill me-2 text-warning"></i>Live System Notifications</h6>
                        <a href="?page=notifications" class="small text-decoration-none fw-bold">View All</a>
                    </div>
                    <div id="dash_live_notifications">
                        <div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Fetching live alerts...</div>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($current_role == 'admin' || $current_role == 'manager'): ?>
        <div class="row g-2">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="dashboard-card bg-gradient-primary text-white">
                    <div class="card-content">
                        <p>Branch Revenue</p>
                        <h3>$<?php echo number_format($extra_metrics['branch_sales'] ?? 0, 2); ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-currency-dollar"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="dashboard-card bg-gradient-success text-white">
                    <div class="card-content">
                        <p>Branch Active Camels</p>
                        <h3><?php echo number_format($extra_metrics['branch_camels'] ?? 0); ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-geo-fill"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="dashboard-card bg-gradient-info text-white">
                    <div class="card-content">
                        <p>Active Customers</p>
                        <h3><?php echo number_format($extra_metrics['branch_customers'] ?? 0); ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="dashboard-card bg-gradient-warning text-white">
                    <div class="card-content">
                        <p>Active Deliveries</p>
                        <h3><?php echo number_format($extra_metrics['branch_deliveries'] ?? 0); ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-truck"></i></div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="dashboard-card bg-gradient-secondary text-white">
                    <div class="card-content">
                        <p>Feed Plans</p>
                        <h3><?php echo number_format($extra_metrics['branch_feed_plans'] ?? 0); ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-list-check"></i></div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="form-container">
                    <h4>Branch Operations</h4>
                    <div class="list-group">
                        <a href="?page=camels&subpage=list" class="list-group-item list-group-item-action list-group-item-info mb-2 rounded border-0"><i class="bi bi-list-ul"></i> Review Camels</a>
                        <a href="?page=milk-collection&subpage=add" class="list-group-item list-group-item-action list-group-item-success mb-2 rounded border-0"><i class="bi bi-droplet"></i> Record Milk Collection</a>
                        <a href="?page=feed&subpage=plans" class="list-group-item list-group-item-action list-group-item-warning mb-2 rounded border-0"><i class="bi bi-clipboard-data"></i> Manage Feed Plans</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-container text-center p-2">
                    <span style="font-size: 4em;">📊</span>
                    <h4 class="mt-3">Branch Leadership Desk</h4>
                    <p class="text-muted">You are viewing the branch-specific operations dashboard. Use this space for quick oversight of sales, herd levels, and delivery activity.</p>
                </div>
            </div>
        </div>

        <?php elseif ($current_role == 'staff'): ?>
        <div class="row g-2">
            <div class="col-md-4">
                <div class="dashboard-card bg-gradient-primary text-white">
                    <div class="card-content">
                        <p>Total Farm Camels</p>
                        <h3><?php echo number_format($extra_metrics['staff_camels'] ?? 0); ?></h3>
                    </div>
                    <div class="icon">🐪</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card bg-gradient-success text-white">
                    <div class="card-content">
                        <p>My Milk Collected Today</p>
                        <h3><?php echo number_format($extra_metrics['staff_milk_today'] ?? 0, 1); ?> L</h3>
                    </div>
                    <div class="icon"><i class="bi bi-droplet-fill"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card bg-gradient-warning text-white">
                    <div class="card-content">
                        <p>Feed Consumptions Logged</p>
                        <h3><?php echo number_format($extra_metrics['staff_feed_today'] ?? 0); ?> logs</h3>
                    </div>
                    <div class="icon"><i class="bi bi-calendar-check"></i></div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="form-container">
                    <h4>Shaqadaada Maanta (Your Daily Task Log)</h4>
                    <div class="list-group">
                        <a href="?page=camels&subpage=add" class="list-group-item list-group-item-action list-group-item-info mb-2 rounded border-0"><i class="bi bi-plus-circle"></i> 🐪 Ku Diiwaangeli Geel Cusub (Register Camel)</a>
                        <a href="?page=milk-collection&subpage=add" class="list-group-item list-group-item-action list-group-item-success mb-2 rounded border-0"><i class="bi bi-droplet"></i> 🥛 Diiwaangeli Caanaha Maanta (Record Milk Collection)</a>
                        <a href="?page=feed&subpage=add-consumption" class="list-group-item list-group-item-action list-group-item-warning mb-2 rounded border-0"><i class="bi bi-calendar-check"></i> 🌾 Geli Diiwaanka Quudinta Geela (Log Feed Consumption)</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-container text-center p-2">
                    <span style="font-size: 4em;">📈</span>
                    <h4 class="mt-3">Daily Operations Reminder</h4>
                    <p class="text-muted">Fadlan hubi inaad geliso dhammaan xogta maalinlaha ah sida caanaha la saaray iyo quudinta geela si nidaamka xogtiisu u noqoto mid sax ah.</p>
                </div>
            </div>
        </div>

        <?php elseif ($current_role == 'vet'): ?>
        <div class="row g-2">
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-primary text-white">
                    <div class="card-content">
                        <p>Herd Active Camels</p>
                        <h3><?php echo number_format($extra_metrics['total_camels'] ?? 0); ?></h3>
                    </div>
                    <div class="icon">🐪</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-danger text-white">
                    <div class="card-content">
                        <p>Camels Currently Sick</p>
                        <h3><?php echo number_format($extra_metrics['sick_camels'] ?? 0); ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-heart-pulse"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-info text-white">
                    <div class="card-content">
                        <p>Vaccinations Logged Today</p>
                        <h3><?php echo number_format($extra_metrics['vaccinations_today'] ?? 0); ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-shield-check"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-warning text-white">
                    <div class="card-content">
                        <p>Vet Visits Scheduled Today</p>
                        <h3><?php echo number_format($extra_metrics['vet_visits_today'] ?? 0); ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-telephone-plus"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-md-6">
                <div class="form-container">
                    <h4>Veterinary Quick Links</h4>
                    <div class="list-group">
                        <a href="?page=health&subpage=add-disease" class="list-group-item list-group-item-action list-group-item-danger mb-2 rounded border-0"><i class="bi bi-bug"></i> 🏥 Geli Diiwaanka Xanuunada (Log Disease Record)</a>
                        <a href="?page=health&subpage=add-vaccination" class="list-group-item list-group-item-action list-group-item-info mb-2 rounded border-0"><i class="bi bi-shield-plus"></i> 💉 Diiwaangeli Tallaalka (Record Camel Vaccination)</a>
                        <a href="?page=health&subpage=add-visit" class="list-group-item list-group-item-action list-group-item-warning mb-2 rounded border-0"><i class="bi bi-telephone"></i> 📞 Diiwaangeli Booqasho Caafimaad (Add Vet Visit Log)</a>
                        <a href="?page=breeding&subpage=pregnancy" class="list-group-item list-group-item-action list-group-item-success mb-2 rounded border-0"><i class="bi bi-heart"></i> 🤰 Soco Uurka Geela (Track Pregnancy Status)</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-container">
                    <h4>Xaaladaha Caafimaad ee Degdega ah (Sick Camels Queue)</h4>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr><th>Tag ID</th><th>Camel Name</th><th>Disease</th><th>Diagnosed</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $sick_camels = $pdo->query("SELECT dr.*, c.tag_id, c.name FROM disease_records dr JOIN camels c ON dr.camel_id = c.id WHERE (dr.recovery_date IS NULL OR dr.recovery_date = '' OR dr.recovery_date = '0000-00-00') ORDER BY dr.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                            if (empty($sick_camels)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Dhammaan geelu waa bedqab! No active cases. 🌟</td></tr>
                            <?php else:
                            foreach ($sick_camels as $sc): ?>
                            <tr><td><?php echo $sc['tag_id']; ?></td><td><?php echo $sc['name']; ?></td><td><?php echo $sc['disease_name']; ?></td><td><?php echo $sc['diagnosed_date']; ?></td></tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($current_role == 'driver'): ?>
        <div class="row g-2">
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-primary text-white">
                    <div class="card-content">
                        <p>Assigned / Pending</p>
                        <h3 id="dash_assigned"><?php echo $extra_metrics['assigned_orders'] ?? 0; ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-clock-history"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-info text-white">
                    <div class="card-content">
                        <p>Accepted (In Transit)</p>
                        <h3 id="dash_accepted"><?php echo $extra_metrics['accepted_orders'] ?? 0; ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-truck"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-success text-white">
                    <div class="card-content">
                        <p>Completed Today</p>
                        <h3 id="dash_today_completed"><?php echo $extra_metrics['today_completed'] ?? 0; ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-danger text-white">
                    <div class="card-content">
                        <p>Canceled Today</p>
                        <h3 id="dash_today_cancelled"><?php echo $extra_metrics['today_cancelled'] ?? 0; ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-x-circle"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-secondary text-white" style="background: linear-gradient(135deg, #6c757d, #495057);">
                    <div class="card-content">
                        <p>Total Completed</p>
                        <h3 id="dash_total_completed"><?php echo $extra_metrics['total_completed'] ?? 0; ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-award"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card bg-gradient-secondary text-white" style="background: linear-gradient(135deg, #8b0000, #5c0000);">
                    <div class="card-content">
                        <p>Total Canceled</p>
                        <h3 id="dash_total_cancelled"><?php echo $extra_metrics['total_cancelled'] ?? 0; ?></h3>
                    </div>
                    <div class="icon"><i class="bi bi-x-octagon"></i></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-container" style="height: 100%;">
                    <h4>Driver Quick Actions</h4>
                    <div class="list-group">
                        <a href="?page=delivery&subpage=tracking" class="list-group-item list-group-item-action list-group-item-info mb-2 rounded border-0"><i class="bi bi-geo"></i> 🗺️ View Assigned Route & Delivery Maps</a>
                        <a href="?page=delivery&subpage=tracking" class="list-group-item list-group-item-action list-group-item-success mb-2 rounded border-0"><i class="bi bi-camera"></i> 📸 Upload Proof of Delivery (Photo)</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-md-6">
                <div class="form-container">
                    <h4>Assigned Deliveries Queue (Active)</h4>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr><th>Order #</th><th>Customer</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="dash_active_queue">
                            <?php
                            $uid = $_SESSION['user_id'];
                            $queue = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.address FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE d.driver_id = $uid AND d.status IN ('assigned', 'in_transit') ORDER BY d.delivery_date ASC")->fetchAll(PDO::FETCH_ASSOC);
                            if (empty($queue)) {
                                echo '<tr><td colspan="4" class="text-center">No active deliveries assigned.</td></tr>';
                            } else {
                                foreach ($queue as $q) {
                                    $badge = $q['status'] == 'assigned' ? '<span class="badge bg-warning">Assigned</span>' : '<span class="badge bg-info">In Transit</span>';
                                    echo '<tr><td>#'.$q['order_number'].'</td><td>'.$q['customer_name'].'<br><small class="text-muted">'.$q['address'].'</small></td><td>'.$badge.'</td><td><a href="?page=delivery&subpage=view&id='.$q['id'].'" class="btn btn-sm btn-primary">View</a></td></tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-container">
                    <h4>Recent Activity</h4>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr><th>Order #</th><th>Customer</th><th>Status</th><th>Time</th></tr>
                        </thead>
                        <tbody id="dash_recent_activity">
                            <?php
                            $recent = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.address 
                                                   FROM deliveries d 
                                                   JOIN orders o ON d.order_id = o.id 
                                                   JOIN customers c ON o.customer_id = c.id 
                                                   WHERE d.driver_id = $uid AND d.status IN ('assigned', 'in_transit', 'delivered', 'cancelled') 
                                                   ORDER BY COALESCE(d.delivered_at, d.cancelled_at, d.accepted_at, d.assigned_at, d.delivery_date) DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                            if (empty($recent)) {
                                echo '<tr><td colspan="4" class="text-center">No recent activity</td></tr>';
                            } else {
                                foreach ($recent as $r) {
                                    $statusBadge = 'secondary';
                                    if ($r['status'] == 'delivered') $statusBadge = 'success';
                                    if ($r['status'] == 'cancelled') $statusBadge = 'danger';
                                    if ($r['status'] == 'in_transit') $statusBadge = 'info';
                                    if ($r['status'] == 'assigned') $statusBadge = 'warning';
                                    
                                    $actionTime = $r['delivered_at'] ?: ($r['cancelled_at'] ?: ($r['accepted_at'] ?: ($r['assigned_at'] ?: '')));
                                    $formattedTime = $actionTime ? date('d M Y, h:i A', strtotime($actionTime)) : 'N/A';
                                    
                                    echo '<tr><td>'.$r['order_number'].'</td><td>'.$r['customer_name'].'</td><td><span class="badge bg-'.$statusBadge.'">'.strtoupper(str_replace('_', ' ', $r['status'])).'</span></td><td>'.$formattedTime.'</td></tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <?php else: ?>
            <?php
            $_SESSION['message'] = 'Your role is not authorized to view this dashboard.';
            $_SESSION['message_type'] = 'danger';
            header('Location: ?page=home');
            exit;
            ?>
        <?php endif; ?>
        <?php
    }
    
    // Users Management - List
    elseif ($page == 'users' && $subpage == 'list') {
        ?>
        <div class="page-title"><h2>All Users</h2><div class="breadcrumb"><span class="breadcrumb-item active">Users</span></div></div>
        <div class="table-container">
            <table class="table table-striped table-hover">
                <thead> <tr><th>ID</th><th>Username</th><?php if (isSuperAdmin()): ?><th>Password</th><?php endif; ?><th>Full Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Actions</th></tr> </thead>
                <tbody>
                    <?php foreach ($users ?? [] as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <?php if (isSuperAdmin()): ?>
                        <td><code style="background-color:#f1f3f5; padding: 2px 6px; border-radius: 4px;"><?php echo htmlspecialchars($user['plain_password'] ?? 'Hashed'); ?></code></td>
                        <?php endif; ?>
                        <td><?php echo $user['full_name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['phone']; ?></td>
                        <td>

                        </td>
                        <td><span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>"><?php echo $user['status']; ?></span></td>
                        <td>
                            <a href="?page=users&subpage=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Users Management - Add
    elseif ($page == 'users' && $subpage == 'add') {
        ?>
        <div class="page-title"><h2>Add New User</h2><div class="breadcrumb"><a href="?page=users&subpage=list" class="breadcrumb-item">Users</a><span class="breadcrumb-item active">Add User</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            <?php if (isSuperAdmin()): ?>
                                <option value="super_admin">Super Admin (Global)</option>
                            <?php endif; ?>
                            <option value="staff">Staff</option>
                            <option value="vet">Veterinarian</option>
                            <option value="driver">Driver</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add User</button>
            </form>
        </div>
        <?php
    }
    
    // Users Management - Edit
    elseif ($page == 'users' && $subpage == 'edit') {
        $user_id = $_GET['id'] ?? null;
        $user_item = [];
        if ($user_id) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_item = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            // Branch admins can only edit users in their branch
            if (!isSuperAdmin() && $user_item['branch_id'] != $_SESSION['branch_id']) {
                echo "<div class='alert alert-danger'>You don't have permission to edit this user.</div>";
                exit;
            }
        }
        ?>
        <div class="page-title"><h2>Edit User</h2><div class="breadcrumb"><a href="?page=users&subpage=list" class="breadcrumb-item">Users</a><span class="breadcrumb-item active">Edit User</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_item['id'] ?? ''); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Username</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user_item['username'] ?? ''); ?>" disabled></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user_item['full_name'] ?? ''); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_item['email'] ?? ''); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_item['phone'] ?? ''); ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            <?php if (isSuperAdmin()): ?>
                                <option value="super_admin" <?php echo ($user_item['role'] ?? '') == 'super_admin' ? 'selected' : ''; ?>>Super Admin (Global)</option>
                            <?php endif; ?>
                            <option value="staff" <?php echo ($user_item['role'] ?? '') == 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="vet" <?php echo ($user_item['role'] ?? '') == 'vet' ? 'selected' : ''; ?>>Veterinarian</option>
                            <option value="driver" <?php echo ($user_item['role'] ?? '') == 'driver' ? 'selected' : ''; ?>>Driver</option>
                            <option value="customer" <?php echo ($user_item['role'] ?? '') == 'customer' ? 'selected' : ''; ?>>Customer</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3"><label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?php echo ($user_item['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="blocked" <?php echo ($user_item['status'] ?? '') == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                            <option value="pending" <?php echo ($user_item['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
            </form>
        </div>
        <?php
    }
    
    // Users Management - Roles
    elseif ($page == 'users' && $subpage == 'roles') {
        ?>
        <div class="page-title"><h2>Roles & Permissions</h2><div class="breadcrumb"><a href="?page=users&subpage=list" class="breadcrumb-item">Users</a><span class="breadcrumb-item active">Roles</span></div></div>
        <div class="form-container mb-4">
            <h4>Add New Role</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_role">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Role Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
                </div>
                <h5>Permissions</h5>
                <div class="row">
                    <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[users]" value="1"><label class="form-check-label">Manage Users</label></div></div>
                    <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[staff]" value="1"><label class="form-check-label">Manage Staff</label></div></div>
                    <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[camels]" value="1"><label class="form-check-label">Manage Camels</label></div></div>
                    <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[health]" value="1"><label class="form-check-label">Health Records</label></div></div>
                    <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[breeding]" value="1"><label class="form-check-label">Breeding</label></div></div>
                    <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[orders]" value="1"><label class="form-check-label">Orders</label></div></div>
                    <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[products]" value="1"><label class="form-check-label">Products</label></div></div>
                    <div class="col-md-3 mb-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[reports]" value="1"><label class="form-check-label">Reports</label></div></div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Add Role</button>
            </form>
        </div>
        <div class="table-container"><h4>Existing Roles</h4><table class="table table-striped table-hover"><thead> <tr><th>Role Name</th><th>Description</th><th>Permissions</th><th>Actions</th></tr> </thead><tbody><?php foreach ($roles ?? [] as $role): ?><tr><td><?php echo $role['name']; ?></td><td><?php echo $role['description']; ?></td><td><?php $perms = json_decode($role['permissions'], true); echo is_array($perms) ? implode(', ', array_keys(array_filter($perms))) : 'None'; ?></td><td><button class="btn btn-sm btn-warning">Edit</button></td></tr><?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Users Management - Login History
    elseif ($page == 'users' && $subpage == 'login-history') {
        ?>
        <div class="page-title"><h2>Login History</h2><div class="breadcrumb"><a href="?page=users&subpage=list" class="breadcrumb-item">Users</a><span class="breadcrumb-item active">Login History</span></div></div>
        <div class="table-container">
            <table class="table table-striped table-hover">
                <thead> <tr><th>User</th><th>Login Time</th><th>IP Address</th><th>Status</th><th>User Agent</th></tr> </thead>
                <tbody><?php foreach ($login_history ?? [] as $log): ?><tr><td><?php echo $log['full_name'] . ' (' . $log['username'] . ')'; ?></td><td><?php echo date('d M Y H:i:s', strtotime($log['login_time'])); ?></td><td><?php echo $log['ip_address']; ?></td><td><span class="badge bg-<?php echo $log['status'] == 'success' ? 'success' : 'danger'; ?>"><?php echo $log['status']; ?></span></td><td><small><?php echo substr($log['user_agent'], 0, 50) . '...'; ?></small></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
        <?php
    }
    
    // Staff Management - List
    elseif ($page == 'staff' && $subpage == 'list') {
        $branch_context = getBranchContextQuery('s');
        
        // Fetch staff list
        $staff_list = $pdo->query("
            SELECT s.*, u.full_name as name, u.email, d.name as department_name 
            FROM staff s 
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN departments d ON s.department_id = d.id
            WHERE {$branch_context}
            ORDER BY s.employee_id
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Handle staff deletion
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_staff') {
            $staff_id = (int)$_POST['staff_id'];
            
            $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
            $stmt->execute([$staff_id]);
            
            $_SESSION['message'] = "Staff member deleted successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=staff&subpage=list");
            exit;
        }
        ?>
        <div class="page-title">
            <h2>All Staff</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Staff</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle" style="font-size: 0.9rem;">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Role & Dept</th>
                        <th>Contact</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff_list as $staff): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo getProfilePictureUrl($staff['profile_picture'] ?? ''); ?>" 
                                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;margin-right:12px;">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($staff['name']); ?></div>
                                    <small class="text-muted">ID: <?php echo htmlspecialchars($staff['employee_id']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($staff['position']); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($staff['department_name'] ?? 'N/A'); ?></small>
                        </td>
                        <td>
                            <div><i class="bi bi-telephone text-muted"></i> <?php echo htmlspecialchars($staff['phone'] ?? 'N/A'); ?></div>
                            <small class="text-muted"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($staff['email'] ?? 'N/A'); ?></small>
                        </td>
                        <td>$<?php echo number_format($staff['salary'] ?? 0, 2); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $staff['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($staff['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="?page=staff&subpage=employee_report&id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-info" title="View Report">
                                <i class="bi bi-person-badge"></i>
                            </a>
                            <a href="?page=staff&subpage=edit&id=<?php echo $staff['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this staff member?');">
                                <input type="hidden" name="action" value="delete_staff">
                                <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($staff_list)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No staff members found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Staff Management - Add
    elseif ($page == 'staff' && $subpage == 'add') {
        $branch_context = getBranchContextQuery('s');
        
        // Fetch departments
        $departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        // Handle staff addition
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_staff') {
            $employee_id = trim($_POST['employee_id']);
            $department_id = (int)$_POST['department_id'];
            $position = trim($_POST['position']);
            $hire_date = $_POST['hire_date'];
            $salary = (float)$_POST['salary'];
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            $status = $_POST['status'];
            
            // Handle profile picture upload with validation
            $profile_picture = null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadProfilePicture($_FILES['profile_picture']);
                if ($uploadResult) {
                    $profile_picture = $uploadResult;
                } else {
                    $_SESSION['message'] = "Error: Invalid image format or file size exceeds limit (Max 5MB. Allowed: JPG, PNG, WEBP, GIF).";
                    $_SESSION['message_type'] = "danger";
                    header("Location: ?page=staff&subpage=add");
                    exit;
                }
            }
            
            // Insert staff record
            $stmt = $pdo->prepare("INSERT INTO staff (employee_id, department_id, position, hire_date, salary, phone, email, profile_picture, status, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $department_id, $position, $hire_date, $salary, $phone, $email, $profile_picture, $status, $_SESSION['branch_id'] ?? null]);
            
            $_SESSION['message'] = "Staff member added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=staff&subpage=list");
            exit;
        }
        ?>
        <div class="page-title">
            <h2>Add New Staff</h2>
            <div class="breadcrumb">
                <a href="?page=staff&subpage=list" class="breadcrumb-item">Staff</a>
                <span class="breadcrumb-item active">Add Staff</span>
            </div>
        </div>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_staff">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee ID</label>
                        <input type="text" name="employee_id" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control" required>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salary</label>
                        <input type="number" name="salary" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg">
                        <small class="text-muted">Allowed formats: JPG, JPEG, PNG, WebP (Max 5MB)</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Staff</button>
            </form>
        </div>
        <?php
    }

    // Staff Management - Edit
    elseif ($page == 'staff' && $subpage == 'edit') {
        $branch_context = getBranchContextQuery('s');
        $staff_id = $_GET['id'] ?? null;
        $staff_item = [];
        
        // Fetch departments
        $departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        if ($staff_id) {
            $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ? AND {$branch_context}");
            $stmt->execute([$staff_id]);
            $staff_item = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }
        
        // Handle staff update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_staff') {
            $staff_id = (int)$_POST['staff_id'];
            $department_id = (int)$_POST['department_id'];
            $position = trim($_POST['position']);
            $hire_date = $_POST['hire_date'];
            $salary = (float)$_POST['salary'];
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            $address = trim($_POST['address']);
            $status = $_POST['status'];
            
            // Handle profile picture upload with validation
            $profile_picture = $staff_item['profile_picture'] ?? null;
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadProfilePicture($_FILES['profile_picture']);
                if ($uploadResult) {
                    $profile_picture = $uploadResult;
                    if (!empty($staff_item['profile_picture'])) {
                        $oldPhoto = $staff_item['profile_picture'];
                        if (file_exists('uploads/' . $oldPhoto)) @unlink('uploads/' . $oldPhoto);
                        if (defined('STAFF_UPLOAD_PATH') && file_exists(STAFF_UPLOAD_PATH . $oldPhoto)) @unlink(STAFF_UPLOAD_PATH . $oldPhoto);
                    }
                } else {
                    $_SESSION['message'] = "Error: Invalid image format or file size exceeds limit (Max 5MB. Allowed: JPG, PNG, WEBP, GIF).";
                    $_SESSION['message_type'] = "danger";
                    header("Location: ?page=staff&subpage=edit&id={$staff_id}");
                    exit;
                }
            }
            
            // Update staff record
            $stmt = $pdo->prepare("UPDATE staff SET department_id = ?, position = ?, hire_date = ?, salary = ?, phone = ?, email = ?, address = ?, profile_picture = ?, status = ? WHERE id = ?");
            $stmt->execute([$department_id, $position, $hire_date, $salary, $phone, $email, $address, $profile_picture, $status, $staff_id]);
            
            $_SESSION['message'] = "Staff member updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=staff&subpage=list");
            exit;
        }
        ?>
        <div class="page-title">
            <h2>Edit Staff</h2>
            <div class="breadcrumb">
                <a href="?page=staff&subpage=list" class="breadcrumb-item">Staff</a>
                <span class="breadcrumb-item active">Edit Staff</span>
            </div>
        </div>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_staff">
                <input type="hidden" name="staff_id" value="<?php echo htmlspecialchars($staff_item['id'] ?? ''); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Employee ID</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($staff_item['employee_id'] ?? ''); ?>" disabled>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control" required>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo ($staff_item['department_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($staff_item['position'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" class="form-control" value="<?php echo htmlspecialchars($staff_item['hire_date'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salary</label>
                        <input type="number" name="salary" class="form-control" step="0.01" value="<?php echo htmlspecialchars($staff_item['salary'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($staff_item['phone'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($staff_item['email'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Profile Picture</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?php echo getProfilePictureUrl($staff_item['profile_picture'] ?? ''); ?>" alt="Profile Picture" class="rounded-circle border" style="width:50px;height:50px;object-fit:cover;">
                            <input type="file" name="profile_picture" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg">
                        </div>
                        <small class="text-muted">Allowed formats: JPG, JPEG, PNG, WebP (Max 5MB)</small>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($staff_item['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?php echo ($staff_item['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($staff_item['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Staff</button>
            </form>
        </div>
        <?php
    }
    
    // Staff Management - Departments
    elseif ($page == 'staff' && $subpage == 'departments') {
        // Fetch departments
        $departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        
        // Handle department addition
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_department') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            
            $_SESSION['message'] = "Department added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=staff&subpage=departments");
            exit;
        }
        ?>
        <div class="page-title">
            <h2>Departments</h2>
            <div class="breadcrumb">
                <a href="?page=staff&subpage=list" class="breadcrumb-item">Staff</a>
                <span class="breadcrumb-item active">Departments</span>
            </div>
        </div>
        <div class="form-container mb-4">
            <h4>Add New Department</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_department">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Department</button>
            </form>
        </div>
        <div class="table-container">
            <h4>Existing Departments</h4>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dept['name']); ?></td>
                        <td><?php echo htmlspecialchars($dept['description'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d M Y', strtotime($dept['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($departments)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No departments found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Staff Management - Leave
    elseif ($page == 'staff' && $subpage == 'leave') {
        $branch_context = getBranchContextQuery('s');
        
        // Fetch leave requests
        $leave_requests = $pdo->query("
            SELECT lr.*, s.employee_id, s.position, u.full_name as staff_name
            FROM leave_requests lr
            JOIN staff s ON lr.staff_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE {$branch_context}
            ORDER BY lr.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        // Handle leave status updates
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_leave_status') {
            $leave_id = (int)$_POST['leave_id'];
            $status = $_POST['status'];
            
            $stmt = $pdo->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
            $stmt->execute([$status, $leave_id]);
            
            $_SESSION['message'] = "Leave request " . ucfirst($status) . " successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=staff&subpage=leave");
            exit;
        }
        ?>
        <div class="page-title">
            <h2>Leave Management</h2>
            <div class="breadcrumb">
                <a href="?page=staff&subpage=list" class="breadcrumb-item">Staff</a>
                <span class="breadcrumb-item active">Leave Requests</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_requests as $leave): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($leave['staff_name']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($leave['employee_id']); ?> - <?php echo htmlspecialchars($leave['position']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars(ucfirst($leave['leave_type'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($leave['start_date'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($leave['end_date'])); ?></td>
                        <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $leave['status'] == 'approved' ? 'success' : ($leave['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                <?php echo ucfirst($leave['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($leave['status'] == 'pending'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="update_leave_status">
                                <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="update_leave_status">
                                <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leave_requests)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No leave requests found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Staff Management - Employee Report
    elseif ($page == 'staff' && $subpage == 'employee_report') {
        $branch_context = getBranchContextQuery('s');
        $employee_id = $_GET['id'] ?? 0;
        
        if ($employee_id) {
            $stmt = $pdo->prepare("SELECT s.*, u.email, u.full_name as name, u.role FROM staff s JOIN users u ON s.user_id = u.id WHERE s.id = ? AND {$branch_context}");
            $stmt->execute([$employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $staff_members = $pdo->query("SELECT s.*, u.full_name as name FROM staff s JOIN users u ON s.user_id = u.id WHERE {$branch_context}")->fetchAll(PDO::FETCH_ASSOC);
        }
        ?>
        <div class="page-title">
            <h2>Employee Report</h2>
            <div class="breadcrumb">
                <a href="?page=staff&subpage=list" class="breadcrumb-item">Staff</a>
                <span class="breadcrumb-item active">Employee Report</span>
            </div>
        </div>
        
        <?php if ($employee_id && $employee): ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-container text-center">
                        <img src="<?php echo getProfilePictureUrl($employee['profile_picture'] ?? ''); ?>" alt="Profile" class="rounded-circle mb-3 border border-3" style="width:150px; height:150px; object-fit:cover;">
                        <h4><?php echo htmlspecialchars($employee['name']); ?></h4>
                        <p class="text-muted mb-1"><?php echo htmlspecialchars($employee['position']); ?></p>
                        <span class="badge bg-primary"><?php echo $employee['employee_id']; ?></span>
                    </div>
                    <div class="form-container mt-3">
                        <h5>Personal Info</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-envelope text-muted me-2"></i> <?php echo htmlspecialchars($employee['email']); ?></li>
                            <li class="mb-2"><i class="bi bi-telephone text-muted me-2"></i> <?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></li>
                            <li class="mb-2"><i class="bi bi-calendar text-muted me-2"></i> Hire Date: <?php echo $employee['hire_date'] ? date('M d, Y', strtotime($employee['hire_date'])) : 'N/A'; ?></li>
                            <li><i class="bi bi-wallet text-muted me-2"></i> Salary: $<?php echo number_format($employee['salary'] ?? 0, 2); ?></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-container mb-3">
                        <div class="d-flex justify-content-between">
                            <h5>Attendance Overview</h5>
                        </div>
                        <?php
                        $att_stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE staff_id = ? GROUP BY status");
                        $att_stmt->execute([$employee_id]);
                        $attendance_stats = $att_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                        
                        $present = ($attendance_stats['present'] ?? 0) + ($attendance_stats['late'] ?? 0) + (($attendance_stats['half_day'] ?? 0) * 0.5);
                        
                        // Calculate working days since hire date
                        $hire_date = strtotime($employee['hire_date'] ?? date('Y-m-d'));
                        $now = time();
                        $working_days_since_hire = max(1, floor(($now - $hire_date) / (60 * 60 * 24)) + 1); // +1 to include today
                        
                        $total_days = $working_days_since_hire;
                        $present_percent = min(100, ($present / $total_days) * 100);
                        ?>
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $present_percent; ?>%">Present (<?php echo round($present_percent); ?>%)</div>
                            <div class="progress-bar bg-warning" style="width: <?php echo (($attendance_stats['late'] ?? 0) / $total_days) * 100; ?>%">Late</div>
                            <div class="progress-bar bg-danger" style="width: <?php echo (($attendance_stats['absent'] ?? 0) / $total_days) * 100; ?>%">Absent</div>
                        </div>
                    </div>
                    
                    <div class="form-container">
                        <h5>Performance & Awards</h5>
                        <?php
                        $perf_stmt = $pdo->prepare("SELECT * FROM employee_performance WHERE staff_id = ? ORDER BY year DESC, month DESC LIMIT 5");
                        $perf_stmt->execute([$employee_id]);
                        $performances = $perf_stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead><tr><th>Month/Year</th><th>Total Score</th><th>Rank</th><th>Notes</th></tr></thead>
                                <tbody>
                                    <?php foreach ($performances as $p): ?>
                                    <tr>
                                        <td><?php echo date('F Y', mktime(0,0,0,$p['month'],1,$p['year'])); ?></td>
                                        <td><span class="badge bg-primary"><?php echo $p['total_score']; ?>/100</span></td>
                                        <td>#<?php echo $p['ranked_position'] ?: '-'; ?></td>
                                        <td><?php echo htmlspecialchars($p['notes']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($performances)) echo '<tr><td colspan="4" class="text-center">No performance records found.</td></tr>'; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="form-container">
                <form method="GET" class="mb-4">
                    <input type="hidden" name="page" value="staff">
                    <input type="hidden" name="subpage" value="employee_report">
                    <div class="input-group">
                        <select name="id" class="form-select" required>
                            <option value="">Select an Employee...</option>
                            <?php foreach ($staff_members as $sm): ?>
                            <option value="<?php echo $sm['id']; ?>"><?php echo htmlspecialchars($sm['name']); ?> (<?php echo $sm['employee_id']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        <?php
    }
    
    // Staff Management - Performance Competition
    elseif ($page == 'staff' && $subpage == 'performance') {
        $branch_context = getBranchContextQuery('s');
        $current_month = date('n');
        $current_year = date('Y');
        $previous_month = $current_month == 1 ? 12 : $current_month - 1;
        $previous_month_year = $current_month == 1 ? $current_year - 1 : $current_year;
        
        // Get filter parameters
        $filter_month = isset($_GET['month']) ? (int)$_GET['month'] : $current_month;
        $filter_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'update_all_scores') {
                updateAllEmployeesPerformance($pdo, $filter_month, $filter_year);
                $_SESSION['message'] = "Performance scores updated for all employees!";
                $_SESSION['message_type'] = "success";
                header("Location: ?page=staff&subpage=performance&month=$filter_month&year=$filter_year");
                exit;
            }
            
            if ($_POST['action'] === 'update_single_score') {
                $staff_id = (int)$_POST['staff_id'];
                $tasks_completed = (int)$_POST['tasks_completed'];
                $custom_score = (float)$_POST['custom_score'];
                $notes = trim($_POST['notes']);
                
                // Get or create performance record
                $stmt = $pdo->prepare("SELECT * FROM employee_performance WHERE staff_id = ? AND month = ? AND year = ?");
                $stmt->execute([$staff_id, $filter_month, $filter_year]);
                $performance = $stmt->fetch();
                
                if ($performance) {
                    $stmt = $pdo->prepare("UPDATE employee_performance SET tasks_completed = ?, custom_score = ?, notes = ? WHERE id = ?");
                    $stmt->execute([$tasks_completed, $custom_score, $notes, $performance['id']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO employee_performance (staff_id, month, year, tasks_completed, custom_score, notes) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$staff_id, $filter_month, $filter_year, $tasks_completed, $custom_score, $notes]);
                }
                
                // Recalculate score
                updateEmployeePerformanceScore($pdo, $staff_id, $filter_month, $filter_year);
                
                $_SESSION['message'] = "Performance score updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ?page=staff&subpage=performance&month=$filter_month&year=$filter_year");
                exit;
            }
        }
        
        // Get leaderboard
        $leaderboard = $pdo->prepare("
            SELECT ep.*, s.employee_id, u.full_name as name, s.profile_picture, s.position, d.name as department_name
            FROM employee_performance ep
            JOIN staff s ON ep.staff_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN departments d ON s.department_id = d.id
            WHERE ep.month = ? AND ep.year = ? AND {$branch_context}
            ORDER BY ep.total_score DESC
        ");
        $leaderboard->execute([$filter_month, $filter_year]);
        $leaderboard = $leaderboard->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all staff for dropdown
        $staff_members = $pdo->query("SELECT s.*, u.full_name as name FROM staff s JOIN users u ON s.user_id = u.id WHERE {$branch_context}")->fetchAll(PDO::FETCH_ASSOC);
        
        // Get employee with most absences in previous month
        $most_absent = getEmployeeWithMostAbsences($pdo, $previous_month, $previous_month_year);
        
        // Get employee with best attendance in current year
        $best_attendance = getEmployeeWithBestAttendance($pdo, $current_year);
        
        // Get all employees performance for detailed view
        $all_performance = getAllEmployeesPerformance($pdo, $filter_month, $filter_year);
        
        // Get department statistics
        $dept_stats = $pdo->prepare("
            SELECT d.name as department_name, 
                   AVG(ep.total_score) as avg_score,
                   AVG(ep.attendance_score) as avg_attendance,
                   COUNT(*) as employee_count
            FROM employee_performance ep
            JOIN staff s ON ep.staff_id = s.id
            LEFT JOIN departments d ON s.department_id = d.id
            WHERE ep.month = ? AND ep.year = ? AND {$branch_context}
            GROUP BY d.name
        ");
        $dept_stats->execute([$filter_month, $filter_year]);
        $dept_stats = $dept_stats->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2><i class="bi bi-trophy text-warning"></i> Employee Performance & Attendance Management</h2>
            <div class="breadcrumb">
                <a href="?page=staff&subpage=list" class="breadcrumb-item">Staff</a>
                <span class="breadcrumb-item active">Performance</span>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-people fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Total Employees</h6>
                                <h4 class="card-title mb-0"><?php echo count($staff_members); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-graph-up fs-4 text-success"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Avg Performance Score</h6>
                                <h4 class="card-title mb-0"><?php echo count($all_performance) > 0 ? number_format(array_sum(array_column($all_performance, 'total_score')) / count($all_performance), 1) : '0'; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-calendar-check fs-4 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Avg Attendance</h6>
                                <h4 class="card-title mb-0"><?php echo count($all_performance) > 0 ? number_format(array_sum(array_column($all_performance, 'attendance_score')) / count($all_performance), 1) : '0'; ?>%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                            </div>
                            <div>
                                <h6 class="card-subtitle mb-1 text-muted">Most Absent (Last Month)</h6>
                                <h4 class="card-title mb-0 small"><?php echo $most_absent ? htmlspecialchars(substr($most_absent['full_name'], 0, 15)) : 'N/A'; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Best Attendance Card -->
        <?php if ($best_attendance): ?>
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="bi bi-award-fill fs-1 me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Best Attendance This Year</h5>
                <p class="mb-0">
                    <strong><?php echo htmlspecialchars($best_attendance['full_name']); ?></strong> 
                    (<?php echo htmlspecialchars($best_attendance['position']); ?>) has the best attendance rate of 
                    <strong><?php echo number_format($best_attendance['attendance_rate'], 1); ?>%</strong>
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Filter Controls -->
        <div class="form-container mb-4">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select" onchange="window.location.href='?page=staff&subpage=performance&month='+this.value+'&year='+document.querySelector('[name=year]').value">
                        <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $filter_month == $m ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select" onchange="window.location.href='?page=staff&subpage=performance&month='+document.querySelector('[name=month]').value+'&year='+this.value">
                        <?php for($y=$current_year-2; $y<=$current_year+1; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo $filter_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scoreModal">
                            <i class="bi bi-plus-circle"></i> Update Score
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="update_all_scores">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Recalculate all performance scores based on attendance data?')">
                                <i class="bi bi-arrow-clockwise"></i> Recalculate All
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Leaderboard -->
            <div class="col-lg-8">
                <div class="form-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="m-0">🏆 Performance Rankings - <?php echo date('F Y', mktime(0, 0, 0, $filter_month, 1, $filter_year)); ?></h4>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Attendance</th>
                                    <th>Punctuality</th>
                                    <th>Tasks</th>
                                    <th>Total Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; foreach ($leaderboard as $lb): ?>
                                <tr class="<?php echo $rank == 1 ? 'table-warning' : ($rank == 2 ? 'table-secondary' : ($rank == 3 ? 'table-danger' : '')); ?>">
                                    <td>
                                        <?php 
                                        if ($rank == 1) echo '<i class="bi bi-trophy-fill text-warning fs-4"></i>';
                                        elseif ($rank == 2) echo '<i class="bi bi-award-fill text-secondary fs-4"></i>';
                                        elseif ($rank == 3) echo '<i class="bi bi-award-fill text-danger fs-4"></i>';
                                        else echo "<strong>#$rank</strong>";
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo getProfilePictureUrl($lb['profile_picture'] ?? ''); ?>" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($lb['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($lb['position']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($lb['department_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-<?php echo $lb['attendance_score'] >= 80 ? 'success' : ($lb['attendance_score'] >= 60 ? 'warning' : 'danger'); ?>" style="width: <?php echo $lb['attendance_score']; ?>%"></div>
                                        </div>
                                        <small><?php echo number_format($lb['attendance_score'], 1); ?>%</small>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-info" style="width: <?php echo $lb['punctuality_score']; ?>%"></div>
                                        </div>
                                        <small><?php echo number_format($lb['punctuality_score'], 1); ?>%</small>
                                    </td>
                                    <td><?php echo $lb['tasks_completed']; ?></td>
                                    <td><strong class="fs-5 text-primary"><?php echo number_format($lb['total_score'], 1); ?></strong></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editScoreModal" onclick="editScore(<?php echo $lb['staff_id']; ?>, '<?php echo addslashes($lb['name']); ?>', <?php echo $lb['tasks_completed']; ?>, <?php echo $lb['custom_score']; ?>, '<?php echo addslashes($lb['notes'] ?? ''); ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php $rank++; endforeach; ?>
                                <?php if(empty($leaderboard)): ?>
                                    <tr><td colspan="8" class="text-muted py-4 text-center">No performance data for this period. Click "Recalculate All" to generate scores.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Department Statistics -->
                <?php if (!empty($dept_stats)): ?>
                <div class="form-container mt-4">
                    <h4>Department Performance</h4>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Employees</th>
                                    <th>Avg Score</th>
                                    <th>Avg Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dept_stats as $dept): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dept['department_name'] ?? 'Unassigned'); ?></td>
                                    <td><?php echo $dept['employee_count']; ?></td>
                                    <td><?php echo number_format($dept['avg_score'], 1); ?></td>
                                    <td><?php echo number_format($dept['avg_attendance'], 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Side Panel -->
            <div class="col-lg-4">
                <!-- Scoring System Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body bg-light rounded">
                        <h5 class="card-title text-primary"><i class="bi bi-info-circle"></i> Scoring System</h5>
                        <ul class="mb-0 ps-3 mt-2 small">
                            <li><strong>Attendance (40%):</strong> Based on presence rate</li>
                            <li><strong>Punctuality (30%):</strong> Based on on-time arrival</li>
                            <li><strong>Tasks (20%):</strong> Completed duties (30 = 100%)</li>
                            <li><strong>Custom (10%):</strong> Manager bonus points</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Most Absent Employee -->
                <?php if ($most_absent && $most_absent['absence_count'] > 0): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body bg-danger bg-opacity-10 rounded">
                        <h5 class="card-title text-danger"><i class="bi bi-exclamation-triangle"></i> Most Absent - <?php echo date('F Y', mktime(0, 0, 0, $previous_month, 1, $previous_month_year)); ?></h5>
                        <div class="mt-3">
                            <strong><?php echo htmlspecialchars($most_absent['full_name']); ?></strong>
                            <p class="mb-1 small text-muted"><?php echo htmlspecialchars($most_absent['position']); ?></p>
                            <span class="badge bg-danger"><?php echo $most_absent['absence_count']; ?> absences</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Recent Awards -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-award text-warning"></i> Recent Awards</h5>
                        <?php
                        $awards = $pdo->query("SELECT a.*, u.full_name as staff_name FROM employee_awards a JOIN staff s ON a.staff_id = s.id JOIN users u ON s.user_id = u.id WHERE {$branch_context} ORDER BY a.award_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($awards as $aw): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <i class="bi bi-award text-warning me-2"></i> <strong><?php echo htmlspecialchars($aw['staff_name']); ?></strong> 
                                    <br><small class="text-muted"><?php echo htmlspecialchars($aw['award_title']); ?></small>
                                </div>
                                <div class="text-end">
                                    <?php if($aw['cash_bonus'] > 0): ?><span class="badge bg-success">+$<?php echo number_format($aw['cash_bonus'], 2); ?></span><?php endif; ?>
                                    <small class="text-muted d-block"><?php echo date('M d, Y', strtotime($aw['award_date'])); ?></small>
                                </div>
                            </li>
                            <?php endforeach; ?>
                            <?php if(empty($awards)): ?>
                            <li class="list-group-item text-muted text-center">No awards given yet</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Score Modal -->
        <div class="modal fade" id="scoreModal" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST">
                    <input type="hidden" name="action" value="update_single_score">
                    
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Update Performance Score</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Employee</label>
                            <select name="staff_id" class="form-select" required>
                                <?php foreach($staff_members as $sm): ?>
                                <option value="<?php echo $sm['id']; ?>"><?php echo htmlspecialchars($sm['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Tasks Completed</label>
                                <input type="number" name="tasks_completed" class="form-control" min="0" value="0" required>
                                <small class="text-muted">30 tasks = 100%</small>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Custom Bonus Score</label>
                                <input type="number" name="custom_score" class="form-control" min="0" max="100" step="0.1" value="0">
                                <small class="text-muted">Max 100 points</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Performance notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Score</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Score Modal -->
        <div class="modal fade" id="editScoreModal" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST">
                    <input type="hidden" name="action" value="update_single_score">
                    <input type="hidden" name="staff_id" id="edit_staff_id">
                    
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Edit Performance Score</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Employee</label>
                            <input type="text" id="edit_staff_name" class="form-control" readonly>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Tasks Completed</label>
                                <input type="number" name="tasks_completed" id="edit_tasks" class="form-control" min="0" required>
                                <small class="text-muted">30 tasks = 100%</small>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Custom Bonus Score</label>
                                <input type="number" name="custom_score" id="edit_custom" class="form-control" min="0" max="100" step="0.1">
                                <small class="text-muted">Max 100 points</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Score</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function editScore(staffId, staffName, tasks, custom, notes) {
            document.getElementById('edit_staff_id').value = staffId;
            document.getElementById('edit_staff_name').value = staffName;
            document.getElementById('edit_tasks').value = tasks;
            document.getElementById('edit_custom').value = custom;
            document.getElementById('edit_notes').value = notes;
        }
        </script>
        <?php
    }
    
    // Staff Management - Attendance
    elseif ($page == 'staff' && $subpage == 'attendance') {
        $branch_context = getBranchContextQuery('s');
        $current_month = date('n');
        $current_year = date('Y');
        
        // Get filter parameters
        $filter_month = isset($_GET['month']) ? (int)$_GET['month'] : $current_month;
        $filter_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
        $filter_department = isset($_GET['department']) ? (int)$_GET['department'] : null;
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'mark_attendance') {
                $date = $_POST['date'];
                $attendance_data = $_POST['attendance'] ?? [];
                
                try {
                    foreach ($attendance_data as $staff_id => $status) {
                        // Check if attendance record exists
                        $stmt = $pdo->prepare("SELECT id FROM attendance WHERE staff_id = ? AND date = ?");
                        $stmt->execute([$staff_id, $date]);
                        $existing = $stmt->fetch();
                        
                        if ($existing) {
                            $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE id = ?");
                            $stmt->execute([$status, $existing['id']]);
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO attendance (staff_id, date, status, branch_id) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$staff_id, $date, $status, $_SESSION['branch_id'] ?? null]);
                        }
                    }
                    
                    if (isset($_POST['ajax'])) {
                        // Get updated attendance statistics for the month
                        $month_start = date('Y-m-01', mktime(0, 0, 0, $filter_month, 1, $filter_year));
                        $month_end = date('Y-m-t', mktime(0, 0, 0, $filter_month, 1, $filter_year));
                        
                        $updated_stats = $pdo->prepare("
                            SELECT 
                                s.id as staff_id,
                                s.employee_id,
                                s.profile_picture,
                                u.full_name as name,
                                d.name as department_name,
                                COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
                                COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
                                COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
                                COUNT(CASE WHEN a.status = 'half_day' THEN 1 END) as half_days,
                                COUNT(CASE WHEN a.status = 'leave' THEN 1 END) as leave_days,
                                COUNT(*) as total_days
                            FROM staff s
                            JOIN users u ON s.user_id = u.id
                            LEFT JOIN departments d ON s.department_id = d.id
                            LEFT JOIN attendance a ON s.id = a.staff_id AND a.date BETWEEN ? AND ?
                            WHERE s.status = 'active' AND {$branch_context}
                            GROUP BY s.id, s.employee_id, s.profile_picture, u.full_name, d.name
                            ORDER BY u.full_name
                        ");
                        $updated_stats->execute([$month_start, $month_end]);
                        $updated_stats = $updated_stats->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Update performance scores after all attendance is saved
                        foreach ($attendance_data as $staff_id => $status) {
                            $d_month = date('n', strtotime($date));
                            $d_year = date('Y', strtotime($date));
                            try {
                                updateEmployeePerformanceScore($pdo, $staff_id, $d_month, $d_year);
                            } catch (Exception $pe) {
                                // Log performance update error but don't fail the attendance save
                                error_log("Performance score update failed for staff $staff_id: " . $pe->getMessage());
                            }
                        }
                        
                        echo json_encode(['success' => true, 'message' => 'Attendance saved successfully.', 'stats' => $updated_stats]);
                        exit;
                    }
                    
                    // Update performance scores for non-AJAX requests
                    foreach ($attendance_data as $staff_id => $status) {
                        $d_month = date('n', strtotime($date));
                        $d_year = date('Y', strtotime($date));
                        try {
                            updateEmployeePerformanceScore($pdo, $staff_id, $d_month, $d_year);
                        } catch (Exception $pe) {
                            error_log("Performance score update failed for staff $staff_id: " . $pe->getMessage());
                        }
                    }
                    
                    $_SESSION['message'] = "Attendance marked successfully!";
                    $_SESSION['message_type'] = "success";
                    header("Location: ?page=staff&subpage=attendance&month=$filter_month&year=$filter_year");
                    exit;
                } catch (Exception $e) {
                    if (isset($_POST['ajax'])) {
                        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                        exit;
                    }
                    $_SESSION['message'] = "Error: " . $e->getMessage();
                    $_SESSION['message_type'] = "danger";
                    header("Location: ?page=staff&subpage=attendance&month=$filter_month&year=$filter_year");
                    exit;
                }
            }
        }
        
        // Get staff members
        $where = "WHERE s.status = 'active'";
        $params = [];
        
        if ($filter_department) {
            $where .= " AND s.department_id = ?";
            $params[] = $filter_department;
        }
        
        $staff_query = "SELECT s.*, u.full_name as name, d.name as department_name FROM staff s JOIN users u ON s.user_id = u.id LEFT JOIN departments d ON s.department_id = d.id $where AND {$branch_context}";
        $stmt = $pdo->prepare($staff_query);
        $stmt->execute($params);
        $staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get departments for filter
        $departments = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);
        
        // Get attendance for selected date
        $selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        $attendance_data = [];
        
        if ($selected_date) {
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE date = ?");
            $stmt->execute([$selected_date]);
            $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($attendance_records as $record) {
                $attendance_data[$record['staff_id']] = $record['status'];
            }
        }
        
        // Get attendance statistics for the month
        $month_start = date('Y-m-01', mktime(0, 0, 0, $filter_month, 1, $filter_year));
        $month_end = date('Y-m-t', mktime(0, 0, 0, $filter_month, 1, $filter_year));
        
        $attendance_stats = $pdo->prepare("
            SELECT 
                s.id as staff_id,
                s.employee_id,
                s.profile_picture,
                u.full_name as name,
                d.name as department_name,
                COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
                COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days,
                COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
                COUNT(CASE WHEN a.status = 'half_day' THEN 1 END) as half_days,
                COUNT(CASE WHEN a.status = 'leave' THEN 1 END) as leave_days,
                COUNT(*) as total_days
            FROM staff s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN departments d ON s.department_id = d.id
            LEFT JOIN attendance a ON s.id = a.staff_id AND a.date BETWEEN ? AND ?
            WHERE s.status = 'active' AND {$branch_context}
            GROUP BY s.id, s.employee_id, s.profile_picture, u.full_name, d.name
            ORDER BY u.full_name
        ");
        $attendance_stats->execute([$month_start, $month_end]);
        $attendance_stats = $attendance_stats->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2><i class="bi bi-calendar-check text-primary"></i> Attendance Management</h2>
            <div class="breadcrumb">
                <a href="?page=staff&subpage=list" class="breadcrumb-item">Staff</a>
                <span class="breadcrumb-item active">Attendance</span>
            </div>
        </div>
        
        <!-- Filter Controls -->
        <div class="form-container mb-4">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select" onchange="window.location.href='?page=staff&subpage=attendance&month='+this.value+'&year='+document.querySelector('[name=year]').value">
                        <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $filter_month == $m ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select" onchange="window.location.href='?page=staff&subpage=attendance&month='+document.querySelector('[name=month]').value+'&year='+this.value">
                        <?php for($y=$current_year-2; $y<=$current_year+1; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo $filter_year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select" onchange="window.location.href='?page=staff&subpage=attendance&month='+document.querySelector('[name=month]').value+'&year='+document.querySelector('[name=year]').value+'&department='+this.value">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $filter_department == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">
                        <i class="bi bi-calendar-plus"></i> Mark Attendance
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Attendance Statistics -->
        <div class="form-container mb-4" id="attendanceTableContainer">
            <h4>Monthly Attendance Summary - <?php echo date('F Y', mktime(0, 0, 0, $filter_month, 1, $filter_year)); ?></h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Present</th>
                            <th>Late</th>
                            <th>Absent</th>
                            <th>Half Day</th>
                            <th>Leave</th>
                            <th>Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_stats as $stat): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo getProfilePictureUrl($stat['profile_picture'] ?? ''); ?>" class="rounded-circle me-2" style="width:36px;height:36px;object-fit:cover;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($stat['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($stat['employee_id']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($stat['department_name'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-success"><?php echo $stat['present_days']; ?></span></td>
                            <td><span class="badge bg-warning"><?php echo $stat['late_days']; ?></span></td>
                            <td><span class="badge bg-danger"><?php echo $stat['absent_days']; ?></span></td>
                            <td><span class="badge bg-info"><?php echo $stat['half_days']; ?></span></td>
                            <td><span class="badge bg-secondary"><?php echo $stat['leave_days']; ?></span></td>
                            <td>
                                <?php 
                                $present_count = $stat['present_days'] + $stat['late_days'] + ($stat['half_days'] * 0.5);
                                
                                $calc_month = $filter_month ?: date('n');
                                $calc_year = $filter_year ?: date('Y');
                                
                                $days_in_month = cal_days_in_month(CAL_GREGORIAN, $calc_month, $calc_year);
                                $total_working_days = $days_in_month;
                                
                                if ($calc_year == date('Y') && $calc_month == date('n')) {
                                    $total_working_days = date('j'); // Days up to today
                                }
                                if ($total_working_days <= 0) $total_working_days = 1;
                                
                                $rate = min(100, ($present_count / $total_working_days) * 100);
                                ?>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo $rate >= 80 ? 'success' : ($rate >= 60 ? 'warning' : 'danger'); ?>" style="width: <?php echo $rate; ?>%"></div>
                                </div>
                                <small><?php echo number_format($rate, 1); ?>% (<?php echo $present_count; ?>/<?php echo $total_working_days; ?> days)</small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Mark Attendance Modal -->
        <div class="modal fade" id="markAttendanceModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" id="markAttendanceForm">
                    <input type="hidden" name="action" value="mark_attendance">
                    <input type="hidden" name="ajax" value="1">
                    
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Mark Attendance</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staff_members as $staff): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo getProfilePictureUrl($staff['profile_picture'] ?? ''); ?>" class="rounded-circle me-2" style="width:30px;height:30px;object-fit:cover;">
                                                <span><?php echo htmlspecialchars($staff['name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($staff['department_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <select name="attendance[<?php echo $staff['id']; ?>]" class="form-select form-select-sm">
                                                <option value="present" <?php echo ($attendance_data[$staff['id']] ?? '') === 'present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="late" <?php echo ($attendance_data[$staff['id']] ?? '') === 'late' ? 'selected' : ''; ?>>Late</option>
                                                <option value="absent" <?php echo ($attendance_data[$staff['id']] ?? '') === 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                <option value="half_day" <?php echo ($attendance_data[$staff['id']] ?? '') === 'half_day' ? 'selected' : ''; ?>>Half Day</option>
                                                <option value="leave" <?php echo ($attendance_data[$staff['id']] ?? '') === 'leave' ? 'selected' : ''; ?>>Leave</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Attendance</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        document.getElementById('markAttendanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            submitBtn.disabled = true;
            
            fetch(window.location.href, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Show success message dynamically
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show mb-4';
                    alertDiv.innerHTML = data.message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    document.querySelector('#attendanceTableContainer').parentNode.insertBefore(alertDiv, document.querySelector('#attendanceTableContainer'));
                    
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('markAttendanceModal'));
                    if (modal) modal.hide();
                    
                    // Fetch the updated page content to refresh the table without reloading
                    fetch(window.location.href)
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newTable = doc.querySelector('#attendanceTableContainer').innerHTML;
                        document.querySelector('#attendanceTableContainer').innerHTML = newTable;
                    });
                } else {
                    alert('Error: ' + (data.message || 'Unknown error occurred.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving attendance.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
        </script>
        <?php
    }
    
    // Camel Registration - List
    elseif ($page == 'camels' && $subpage == 'list') {
        ?>
        <div class="page-title"><h2>All Camels</h2><div class="breadcrumb"><span class="breadcrumb-item active">Camels</span></div></div>
        <div class="table-container">
            <table class="table table-striped table-hover">
                <thead> <tr><th>Tag ID</th><th>Name</th><th>Breed</th><th>Age</th><th>Gender</th><th>Weight</th><th>Location</th><th>Status</th><th>Actions</th></tr> </thead>
                <tbody><?php foreach ($camels ?? [] as $camel): ?><tr><td><?php echo $camel['tag_id']; ?></td><td><?php echo $camel['name']; ?></td><td><?php echo $camel['breed']; ?></td><td><?php echo $camel['age']; ?> years</td><td><?php echo ucfirst($camel['gender']); ?></td><td><?php echo $camel['weight']; ?> kg</td><td><?php echo $camel['farm_location']; ?></td><td><span class="badge bg-<?php echo $camel['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo $camel['status']; ?></span></td><td><a href="?page=camels&subpage=edit&id=<?php echo $camel['id']; ?>" class="btn btn-sm btn-warning">Edit</a> <a href="?page=health&subpage=add&camel_id=<?php echo $camel['id']; ?>" class="btn btn-sm btn-info">Health</a> <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to mark this camel as deceased? This action cannot be undone.');"><input type="hidden" name="action" value="delete_camel"><input type="hidden" name="camel_id" value="<?php echo $camel['id']; ?>"><button type="submit" class="btn btn-sm btn-danger">Delete</button></form></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
        <?php
    }
    
    // Camel Registration - Add
    elseif ($page == 'camels' && $subpage == 'add') {
        ?>
        <div class="page-title"><h2>Register New Camel</h2><div class="breadcrumb"><a href="?page=camels&subpage=list" class="breadcrumb-item">Camels</a><span class="breadcrumb-item active">Register Camel</span></div></div>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_camel">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Tag ID</label><input type="text" name="tag_id" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Breed</label><input type="text" name="breed" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Age (years)</label><input type="number" name="age" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Gender</label><select name="gender" class="form-control" required><option value="male">Male</option><option value="female">Female</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Weight (kg)</label><input type="number" name="weight" class="form-control" step="0.1"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Farm Location</label><input type="text" name="farm_location" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="sold">Sold</option><option value="deceased">Deceased</option></select></div>
                </div>
                <button type="submit" class="btn btn-primary">Register Camel</button>
            </form>
        </div>
        <?php
    }
    
    // Camel Registration - Edit
    elseif ($page == 'camels' && $subpage == 'edit') {
        $camel_id = $_GET['id'] ?? null;
        $camel_item = null;
        if ($camel_id) {
            $stmt = $pdo->prepare("SELECT * FROM camels WHERE id = ?");
            $stmt->execute([$camel_id]);
            $camel_item = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$camel_item) {
            echo "<div class='alert alert-danger'>Camel not found!</div>";
        } else {
            ?>
            <div class="page-title"><h2>Edit Camel Details</h2><div class="breadcrumb"><a href="?page=camels&subpage=list" class="breadcrumb-item">Camels</a><span class="breadcrumb-item active">Edit Camel</span></div></div>
            <div class="form-container">
                <form method="POST">
                    <input type="hidden" name="action" value="update_camel">
                    <input type="hidden" name="camel_id" value="<?php echo htmlspecialchars($camel_item['id']); ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Tag ID</label><input type="text" name="tag_id" class="form-control" value="<?php echo htmlspecialchars($camel_item['tag_id']); ?>" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($camel_item['name']); ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Breed</label><input type="text" name="breed" class="form-control" value="<?php echo htmlspecialchars($camel_item['breed']); ?>" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Age (years)</label><input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($camel_item['age']); ?>" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Gender</label><select name="gender" class="form-control" required>
                            <option value="male" <?php echo $camel_item['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $camel_item['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                        </select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Weight (kg)</label><input type="number" name="weight" class="form-control" step="0.1" value="<?php echo htmlspecialchars($camel_item['weight']); ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Farm Location</label><input type="text" name="farm_location" class="form-control" value="<?php echo htmlspecialchars($camel_item['farm_location']); ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Status</label><select name="status" class="form-control">
                            <option value="active" <?php echo $camel_item['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="sold" <?php echo $camel_item['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                            <option value="deceased" <?php echo $camel_item['status'] == 'deceased' ? 'selected' : ''; ?>>Deceased</option>
                        </select></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Camel</button>
                </form>
            </div>
            <?php
        }
    }
    
    // Camel Registration - Ownership
    elseif ($page == 'camels' && $subpage == 'ownership') {
        ?>
        <div class="page-title"><h2>Ownership Records</h2><div class="breadcrumb"><a href="?page=camels&subpage=list" class="breadcrumb-item">Camels</a><span class="breadcrumb-item active">Ownership</span></div></div>
        <div class="form-container mb-4">
            <h4>Add Ownership Record</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_ownership">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><?php foreach ($all_camels ?? [] as $camel): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Owner Name</label><input type="text" name="owner_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Owner Phone</label><input type="text" name="owner_phone" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Purchase Price</label><input type="number" name="purchase_price" class="form-control" step="0.01"></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Owner Address</label><textarea name="owner_address" class="form-control" rows="2"></textarea></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                    <div class="col-md-6 mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="mark_sold" value="1"><label class="form-check-label">Mark Camel as Sold</label></div></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Record</button>
            </form>
        </div>
        <div class="table-container"><h4>Ownership History</h4><table class="table table-striped table-hover"><thead> <td><th>Camel</th><th>Owner</th><th>Purchase Date</th><th>Purchase Price</th><th>Contact</th><th>Notes</th> </thead><tbody><?php foreach ($camel_ownership ?? [] as $record): ?>  <td><?php echo $record['camel_name'] . ' (' . $record['tag_id'] . ')'; ?> <td><?php echo $record['owner_name']; ?> <td><?php echo date('d M Y', strtotime($record['purchase_date'])); ?> <td>$<?php echo number_format($record['purchase_price'], 2); ?> <td><?php echo $record['owner_phone']; ?> <td><?php echo $record['notes']; ?> <?php endforeach; ?></tbody> </table> </div>
        <?php
    }
    
    // Camel Registration - Status
    elseif ($page == 'camels' && $subpage == 'status') {
        ?>
        <div class="page-title"><h2>Status Tracking</h2><div class="breadcrumb"><a href="?page=camels&subpage=list" class="breadcrumb-item">Camels</a><span class="breadcrumb-item active">Status</span></div></div>
        <div class="table-container"><table class="table table-striped table-hover"><thead> <th>Tag ID</th><th>Name</th><th>Current Status</th><th>Location</th><th>Last Updated</th><th>Actions</th> </thead><tbody><?php foreach ($all_camels ?? [] as $camel): ?> <tr><td><?php echo $camel['tag_id']; ?> <td><?php echo $camel['name']; ?> <td><span class="badge bg-<?php echo $camel['status'] == 'active' ? 'success' : ($camel['status'] == 'sold' ? 'info' : 'secondary'); ?>"><?php echo ucfirst($camel['status']); ?></span> <td><?php echo $camel['farm_location']; ?> <td><?php echo date('d M Y', strtotime($camel['created_at'])); ?> <td><a href="?page=camels&subpage=edit&id=<?php echo $camel['id']; ?>" class="btn btn-sm btn-warning">Update Status</a> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
        // Begin Camel Health block (PHP context already open)
    // Camels - Health (view all health records or for a specific camel)
    elseif ($page == 'camels' && $subpage == 'health') {
        if (!in_array($current_role, ['admin','manager','vet','staff'])) {
            echo "<div class='alert alert-danger'>You do not have permission to view Camel Health.</div>";
            return;
        }

        $camel_id = $_GET['id'] ?? null;
        $branch_context_hr = getBranchContextQuery('hr');
        $branch_context_c = getBranchContextQuery('c');

        // Handle form submission for adding health record
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_health_record') {
            $camel_id = (int)$_POST['camel_id'];
            $record_date = $_POST['record_date'];
            $diagnosis = trim($_POST['diagnosis']);
            $treatment = trim($_POST['treatment']);
            $vet_name = trim($_POST['vet_name']);
            $next_visit = $_POST['next_visit'] ?: null;
            $notes = trim($_POST['notes']);

            $stmt = $pdo->prepare("INSERT INTO health_records (camel_id, record_date, diagnosis, treatment, vet_name, next_visit, notes, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$camel_id, $record_date, $diagnosis, $treatment, $vet_name, $next_visit, $notes, $_SESSION['branch_id'] ?? null]);

            $_SESSION['message'] = "Health record added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=camels&subpage=health");
            exit;
        }

        if (!$camel_id) {
            // Show all health records with option to filter by camel
            $all_health_records = $pdo->query("
                SELECT hr.*, c.tag_id, c.name as camel_name 
                FROM health_records hr 
                JOIN camels c ON hr.camel_id = c.id 
                WHERE {$branch_context_hr}
                ORDER BY hr.record_date DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            $camels = $pdo->query("SELECT c.id, c.tag_id, c.name FROM camels c WHERE {$branch_context_c}")
                ->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="page-title">
                <h2><i class="bi bi-heart-pulse text-danger"></i> Health Records</h2>
                <div class="breadcrumb">
                    <a href="?page=camels&subpage=list" class="breadcrumb-item">Camels</a>
                    <span class="breadcrumb-item active">Health Records</span>
                </div>
            </div>

            <!-- Add Health Record Form -->
            <div class="form-container mb-4">
                <h4>Add New Health Record</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="add_health_record">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Camel</label>
                            <select name="camel_id" class="form-control" required>
                                <option value="">Choose a camel...</option>
                                <?php foreach ($camels as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['tag_id'] . ' - ' . $c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Record Date</label>
                            <input type="date" name="record_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diagnosis</label>
                            <input type="text" name="diagnosis" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Treatment</label>
                            <input type="text" name="treatment" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vet Name</label>
                            <input type="text" name="vet_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Next Visit Date</label>
                            <input type="date" name="next_visit" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Health Record</button>
                </form>
            </div>

            <!-- All Health Records Table -->
            <div class="table-container">
                <h4>All Health Records</h4>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Camel</th>
                            <th>Diagnosis</th>
                            <th>Treatment</th>
                            <th>Vet</th>
                            <th>Next Visit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_health_records as $rec): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($rec['record_date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($rec['camel_name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($rec['tag_id']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($rec['diagnosis']); ?></td>
                            <td><?php echo htmlspecialchars($rec['treatment']); ?></td>
                            <td><?php echo htmlspecialchars($rec['vet_name']); ?></td>
                            <td><?php echo $rec['next_visit'] ? date('d M Y', strtotime($rec['next_visit'])) : '-'; ?></td>
                            <td>
                                <a href="?page=camels&subpage=health&id=<?php echo $rec['camel_id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View Camel
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($all_health_records)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No health records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
            return;
        }

        // Show health records for specific camel
        $stmt = $pdo->prepare("SELECT c.id, c.tag_id, c.name FROM camels c WHERE c.id = ? AND {$branch_context_c}");
        $stmt->execute([$camel_id]);
        $camel = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$camel) {
            echo "<div class='alert alert-danger'>Camel not found or inaccessible.</div>";
            return;
        }

        $health_records = $pdo->query(
            "SELECT hr.* FROM health_records hr WHERE hr.camel_id = {$camel_id} AND {$branch_context_hr} ORDER BY hr.record_date DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2><i class="bi bi-heart-pulse text-danger"></i> Health Records - <?php echo htmlspecialchars($camel['name']); ?></h2>
            <div class="breadcrumb">
                <a href="?page=camels&subpage=list" class="breadcrumb-item">Camels</a>
                <a href="?page=camels&subpage=health" class="breadcrumb-item">Health Records</a>
                <span class="breadcrumb-item active"><?php echo htmlspecialchars($camel['name']); ?></span>
            </div>
        </div>

        <!-- Add Health Record for this camel -->
        <div class="form-container mb-4">
            <h4>Add Health Record for <?php echo htmlspecialchars($camel['name']); ?></h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_health_record">
                <input type="hidden" name="camel_id" value="<?php echo $camel_id; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Record Date</label>
                        <input type="date" name="record_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Diagnosis</label>
                        <input type="text" name="diagnosis" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Treatment</label>
                        <input type="text" name="treatment" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Vet Name</label>
                        <input type="text" name="vet_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Next Visit Date</label>
                        <input type="date" name="next_visit" class="form-control">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Record</button>
                <a href="?page=camels&subpage=health" class="btn btn-secondary">View All Records</a>
            </form>
        </div>

        <div class="table-container">
            <h4>Health History for <?php echo htmlspecialchars($camel['name']); ?></h4>
            <table class="table table-striped table-hover">
                <thead><tr><th>Date</th><th>Diagnosis</th><th>Treatment</th><th>Vet</th><th>Next Visit</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php foreach ($health_records as $rec): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($rec['record_date'])); ?></td>
                        <td><?php echo htmlspecialchars($rec['diagnosis']); ?></td>
                        <td><?php echo htmlspecialchars($rec['treatment']); ?></td>
                        <td><?php echo htmlspecialchars($rec['vet_name']); ?></td>
                        <td><?php echo $rec['next_visit'] ? date('d M Y', strtotime($rec['next_visit'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($rec['notes'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($health_records)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No health records found for this camel.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Camels - Breeding (view all breeding records or for a specific camel)
    elseif ($page == 'camels' && $subpage == 'breeding') {
        if (!in_array($current_role, ['admin','manager','vet','staff'])) {
            echo "<div class='alert alert-danger'>You do not have permission to view Camel Breeding.</div>";
            return;
        }

        $camel_id = $_GET['id'] ?? null;
        $branch_context_br = getBranchContextQuery('br');
        $branch_context_c = getBranchContextQuery('c');

        // Handle form submission for adding breeding record
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_breeding_record') {
            $female_camel_id = (int)$_POST['female_camel_id'];
            $male_camel_id = !empty($_POST['male_camel_id']) ? (int)$_POST['male_camel_id'] : null;
            $breeding_date = $_POST['breeding_date'];
            $expected_delivery = $_POST['expected_delivery'] ?: null;
            $actual_delivery = $_POST['actual_delivery'] ?: null;
            $status = $_POST['status'];
            $notes = trim($_POST['notes']);

            $stmt = $pdo->prepare("INSERT INTO breeding_records (female_camel_id, male_camel_id, breeding_date, expected_delivery, actual_delivery, status, notes, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$female_camel_id, $male_camel_id, $breeding_date, $expected_delivery, $actual_delivery, $status, $notes, $_SESSION['branch_id'] ?? null]);

            $_SESSION['message'] = "Breeding record added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=camels&subpage=breeding");
            exit;
        }

        if (!$camel_id) {
            // Show all breeding records with option to filter by camel
            $all_breeding_records = $pdo->query("
                SELECT br.*, 
                       fc.tag_id as female_tag, fc.name as female_name,
                       mc.tag_id as male_tag, mc.name as male_name
                FROM breeding_records br 
                JOIN camels fc ON br.female_camel_id = fc.id 
                LEFT JOIN camels mc ON br.male_camel_id = mc.id
                WHERE {$branch_context_br}
                ORDER BY br.breeding_date DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            $female_camels = $pdo->query("SELECT c.id, c.tag_id, c.name FROM camels c WHERE c.gender = 'female' AND {$branch_context_c}")
                ->fetchAll(PDO::FETCH_ASSOC);
            $male_camels = $pdo->query("SELECT c.id, c.tag_id, c.name FROM camels c WHERE c.gender = 'male' AND {$branch_context_c}")
                ->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="page-title">
                <h2><i class="bi bi-heart text-success"></i> Breeding Records</h2>
                <div class="breadcrumb">
                    <a href="?page=camels&subpage=list" class="breadcrumb-item">Camels</a>
                    <span class="breadcrumb-item active">Breeding Records</span>
                </div>
            </div>

            <!-- Add Breeding Record Form -->
            <div class="form-container mb-4">
                <h4>Add New Breeding Record</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="add_breeding_record">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Female Camel</label>
                            <select name="female_camel_id" class="form-control" required>
                                <option value="">Choose female camel...</option>
                                <?php foreach ($female_camels as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['tag_id'] . ' - ' . $c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Male Camel (Optional)</label>
                            <select name="male_camel_id" class="form-control">
                                <option value="">Choose male camel...</option>
                                <?php foreach ($male_camels as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['tag_id'] . ' - ' . $c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Breeding Date</label>
                            <input type="date" name="breeding_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Expected Delivery</label>
                            <input type="date" name="expected_delivery" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Actual Delivery</label>
                            <input type="date" name="actual_delivery" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="pending">Pending</option>
                                <option value="pregnant">Pregnant</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Breeding Record</button>
                </form>
            </div>

            <!-- All Breeding Records Table -->
            <div class="table-container">
                <h4>All Breeding Records</h4>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Female</th>
                            <th>Male</th>
                            <th>Expected Delivery</th>
                            <th>Actual Delivery</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_breeding_records as $rec): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($rec['breeding_date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($rec['female_name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($rec['female_tag']); ?></small>
                            </td>
                            <td>
                                <?php if ($rec['male_name']): ?>
                                <strong><?php echo htmlspecialchars($rec['male_name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($rec['male_tag']); ?></small>
                                <?php else: ?>
                                <span class="text-muted">External/Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $rec['expected_delivery'] ? date('d M Y', strtotime($rec['expected_delivery'])) : '-'; ?></td>
                            <td><?php echo $rec['actual_delivery'] ? date('d M Y', strtotime($rec['actual_delivery'])) : '-'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $rec['status'] == 'completed' ? 'success' : ($rec['status'] == 'pregnant' ? 'primary' : ($rec['status'] == 'failed' ? 'danger' : 'warning')); ?>">
                                    <?php echo ucfirst($rec['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?page=camels&subpage=breeding&id=<?php echo $rec['female_camel_id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View Female
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($all_breeding_records)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No breeding records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
            return;
        }

        // Show breeding records for specific camel
        $stmt = $pdo->prepare("SELECT c.id, c.tag_id, c.name, c.gender FROM camels c WHERE c.id = ? AND {$branch_context_c}");
        $stmt->execute([$camel_id]);
        $camel = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$camel) {
            echo "<div class='alert alert-danger'>Camel not found or inaccessible.</div>";
            return;
        }

        if ($camel['gender'] == 'female') {
            $query = "SELECT br.*, mc.name as mate_name, mc.tag_id as mate_tag FROM breeding_records br LEFT JOIN camels mc ON br.male_camel_id = mc.id WHERE br.female_camel_id = {$camel_id} AND {$branch_context_br} ORDER BY br.breeding_date DESC";
        } else {
            $query = "SELECT br.*, fc.name as mate_name, fc.tag_id as mate_tag FROM breeding_records br JOIN camels fc ON br.female_camel_id = fc.id WHERE br.male_camel_id = {$camel_id} AND {$branch_context_br} ORDER BY br.breeding_date DESC";
        }
        $camel_breeding_records = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

        $female_camels = $pdo->query("SELECT c.id, c.tag_id, c.name FROM camels c WHERE c.gender = 'female' AND {$branch_context_c}")
            ->fetchAll(PDO::FETCH_ASSOC);
        $male_camels = $pdo->query("SELECT c.id, c.tag_id, c.name FROM camels c WHERE c.gender = 'male' AND {$branch_context_c}")
            ->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2><i class="bi bi-heart text-success"></i> Breeding Records - <?php echo htmlspecialchars($camel['name']); ?></h2>
            <div class="breadcrumb">
                <a href="?page=camels&subpage=list" class="breadcrumb-item">Camels</a>
                <a href="?page=camels&subpage=breeding" class="breadcrumb-item">Breeding Records</a>
                <span class="breadcrumb-item active"><?php echo htmlspecialchars($camel['name']); ?></span>
            </div>
        </div>

        <!-- Add Breeding Record for this camel -->
        <div class="form-container mb-4">
            <h4>Add Breeding Record for <?php echo htmlspecialchars($camel['name']); ?></h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_breeding_record">
                <?php if ($camel['gender'] == 'female'): ?>
                <input type="hidden" name="female_camel_id" value="<?php echo $camel_id; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Male Camel (Optional)</label>
                        <select name="male_camel_id" class="form-control">
                            <option value="">Choose male camel...</option>
                            <?php foreach ($male_camels as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['tag_id'] . ' - ' . $c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                <input type="hidden" name="male_camel_id" value="<?php echo $camel_id; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Female Camel</label>
                        <select name="female_camel_id" class="form-control" required>
                            <option value="">Choose female camel...</option>
                            <?php foreach ($female_camels as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['tag_id'] . ' - ' . $c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Breeding Date</label>
                        <input type="date" name="breeding_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Expected Delivery</label>
                        <input type="date" name="expected_delivery" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Actual Delivery</label>
                        <input type="date" name="actual_delivery" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="pregnant">Pregnant</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Record</button>
                <a href="?page=camels&subpage=breeding" class="btn btn-secondary">View All Records</a>
            </form>
        </div>

        <div class="table-container">
            <h4>Breeding History for <?php echo htmlspecialchars($camel['name']); ?></h4>
            <table class="table table-striped table-hover">
                <thead><tr><th>Date</th><th>Mate</th><th>Expected Delivery</th><th>Actual Delivery</th><th>Status</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php if (empty($camel_breeding_records)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No breeding records found for this camel.</td></tr>
                    <?php else: ?>
                        <?php foreach ($camel_breeding_records as $rec): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($rec['breeding_date'])); ?></td>
                            <td><?php echo $rec['mate_name'] ? htmlspecialchars($rec['mate_name'] . ' (' . $rec['mate_tag'] . ')') : 'Unknown/External'; ?></td>
                            <td><?php echo $rec['expected_delivery'] ? date('d M Y', strtotime($rec['expected_delivery'])) : '-'; ?></td>
                            <td><?php echo $rec['actual_delivery'] ? date('d M Y', strtotime($rec['actual_delivery'])) : '-'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $rec['status'] == 'completed' ? 'success' : ($rec['status'] == 'pregnant' ? 'primary' : ($rec['status'] == 'failed' ? 'danger' : 'warning')); ?>">
                                    <?php echo ucfirst($rec['status'] ?? 'pending'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($rec['notes']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    // Health & Veterinary - Records
    elseif ($page == 'health' && $subpage == 'records') {
        ?>
        <div class="page-title"><h2>Health Records</h2><div class="breadcrumb"><span class="breadcrumb-item active">Health Records</span></div></div>
        <div class="form-container mb-4">
            <h4>Add Health Record</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_health_record">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Record Date</label><input type="date" name="record_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Diagnosis</label><input type="text" name="diagnosis" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Treatment</label><input type="text" name="treatment" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Veterinarian</label><input type="text" name="vet_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Next Visit</label><input type="date" name="next_visit" class="form-control"></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Health Record</button>
            </form>
        </div>
        <?php
    }
    
    // Health & Veterinary - Diseases
    elseif ($page == 'health' && $subpage == 'diseases') {
        ?>
        <div class="page-title"><h2>Disease Records</h2><div class="breadcrumb"><a href="?page=health&subpage=records" class="breadcrumb-item">Health</a><span class="breadcrumb-item active">Diseases</span></div></div>
        <div class="form-container mb-4">
            <h4>Add Disease Record</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_disease">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Disease Name</label><input type="text" name="disease_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Diagnosed Date</label><input type="date" name="diagnosed_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Severity</label><select name="severity" class="form-control" required><option value="mild">Mild</option><option value="moderate">Moderate</option><option value="severe">Severe</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Veterinarian</label><input type="text" name="vet_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Recovery Date</label><input type="date" name="recovery_date" class="form-control"></div>
                    <div class="col-12 mb-3"><label class="form-label">Treatment</label><textarea name="treatment" class="form-control" rows="2" required></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Disease Record</button>
            </form>
        </div>
        <div class="table-container"><h4>Disease History</h4><table class="table table-striped table-hover"><thead> <th>Camel</th><th>Disease</th><th>Diagnosed</th><th>Severity</th><th>Vet</th><th>Recovery</th><th>Treatment</th> </thead><tbody><?php foreach ($disease_records ?? [] as $record): ?> <tr><td><?php echo $record['camel_name'] . ' (' . $record['tag_id'] . ')'; ?> <td><?php echo $record['disease_name']; ?> <td><?php echo date('d M Y', strtotime($record['diagnosed_date'])); ?> <td><span class="badge bg-<?php echo $record['severity'] == 'severe' ? 'danger' : ($record['severity'] == 'moderate' ? 'warning' : 'info'); ?>"><?php echo ucfirst($record['severity']); ?></span> <td><?php echo $record['vet_name']; ?> <td><?php echo $record['recovery_date'] ? date('d M Y', strtotime($record['recovery_date'])) : '-'; ?> <td><?php echo substr($record['treatment'], 0, 30) . '...'; ?> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Health & Veterinary - Vaccinations
    elseif ($page == 'health' && $subpage == 'vaccinations') {
        ?>
        <div class="page-title"><h2>Vaccination Records</h2><div class="breadcrumb"><a href="?page=health&subpage=records" class="breadcrumb-item">Health</a><span class="breadcrumb-item active">Vaccinations</span></div></div>
        <div class="form-container mb-4">
            <h4>Add Vaccination</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_vaccination">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Vaccine Name</label><input type="text" name="vaccine_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Vaccination Date</label><input type="date" name="vaccination_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Next Due Date</label><input type="date" name="next_due_date" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Administered By</label><input type="text" name="administered_by" class="form-control" required></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Vaccination</button>
            </form>
        </div>
        <div class="table-container"><h4>Vaccination History</h4><table class="table table-striped table-hover"><thead> <th>Camel</th><th>Vaccine</th><th>Date</th><th>Next Due</th><th>Administered By</th> </thead><tbody><?php foreach ($vaccinations ?? [] as $vac): ?> <tr><td><?php echo $vac['camel_name'] . ' (' . $vac['tag_id'] . ')'; ?> <td><?php echo $vac['vaccine_name']; ?> <td><?php echo date('d M Y', strtotime($vac['vaccination_date'])); ?> <td><?php if ($vac['next_due_date']): ?><?php echo date('d M Y', strtotime($vac['next_due_date'])); ?><?php if (strtotime($vac['next_due_date']) < time()): ?><span class="badge bg-danger">Overdue</span><?php endif; ?><?php else: ?>-<?php endif; ?> <td><?php echo $vac['administered_by']; ?> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Health & Veterinary - Vet Visits
    elseif ($page == 'health' && $subpage == 'vet-visits') {
        ?>
        <div class="page-title"><h2>Vet Visits</h2><div class="breadcrumb"><a href="?page=health&subpage=records" class="breadcrumb-item">Health</a><span class="breadcrumb-item active">Vet Visits</span></div></div>
        <div class="form-container mb-4">
            <h4>Record Vet Visit</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_vet_visit">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Visit Date</label><input type="date" name="visit_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Veterinarian</label><input type="text" name="vet_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Purpose</label><input type="text" name="purpose" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Follow-up Date</label><input type="date" name="follow_up_date" class="form-control"></div>
                    <div class="col-12 mb-3"><label class="form-label">Findings</label><textarea name="findings" class="form-control" rows="2" required></textarea></div>
                    <div class="col-12 mb-3"><label class="form-label">Recommendations</label><textarea name="recommendations" class="form-control" rows="2"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Record Visit</button>
            </form>
        </div>
        <div class="table-container"><h4>Visit History</h4><table class="table table-striped table-hover"><thead> <th>Camel</th><th>Date</th><th>Vet</th><th>Purpose</th><th>Findings</th><th>Follow-up</th> </thead><tbody><?php foreach ($vet_visits ?? [] as $visit): ?> <tr><td><?php echo $visit['camel_name'] . ' (' . $visit['tag_id'] . ')'; ?> <td><?php echo date('d M Y', strtotime($visit['visit_date'])); ?> <td><?php echo $visit['vet_name']; ?> <td><?php echo $visit['purpose']; ?> <td><?php echo substr($visit['findings'], 0, 30) . '...'; ?> <td><?php echo $visit['follow_up_date'] ? date('d M Y', strtotime($visit['follow_up_date'])) : '-'; ?> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Breeding - Records & Add
    elseif ($page == 'breeding' && in_array($subpage, ['records', 'add'])) {
        ?>
        <div class="page-title"><h2>Breeding Records</h2><div class="breadcrumb"><span class="breadcrumb-item active">Breeding Records</span></div></div>
        <div class="form-container mb-4">
            <h4>Add Breeding Record</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_breeding">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Female Camel</label><select name="female_camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): if ($camel['gender'] == 'female'): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endif; endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Male Camel</label><select name="male_camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): if ($camel['gender'] == 'male'): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endif; endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Breeding Date</label><input type="date" name="breeding_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Breeding Record</button>
            </form>
        </div>
        <div class="form-container mb-4">
            <h4>Update Breeding (Record Delivery)</h4>
            <form method="POST">
                <input type="hidden" name="action" value="update_breeding">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Breeding Record</label><select name="breeding_id" class="form-control" required><option value="">-- Select --</option><?php foreach ($breeding_records ?? [] as $breed): if (!$breed['actual_delivery']): ?><option value="<?php echo $breed['id']; ?>"><?php echo $breed['female_tag'] . ' x ' . $breed['male_tag'] . ' - ' . date('d M Y', strtotime($breed['breeding_date'])); ?></option><?php endif; endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Actual Delivery Date</label><input type="date" name="actual_delivery" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Status</label><select name="status" class="form-control" required><option value="completed">Completed - Live Birth</option><option value="stillbirth">Stillbirth</option><option value="miscarriage">Miscarriage</option></select></div>
                </div>
                <button type="submit" class="btn btn-primary">Update Record</button>
            </form>
        </div>
        <div class="table-container"><h4>Breeding History</h4><table class="table table-striped table-hover"><thead> <th>Female</th><th>Male</th><th>Breeding Date</th><th>Expected Delivery</th><th>Actual Delivery</th><th>Status</th><th>Notes</th> </thead><tbody><?php foreach ($breeding_records ?? [] as $record): ?> <tr><td><?php echo $record['female_name'] . ' (' . $record['female_tag'] . ')'; ?> <td><?php echo $record['male_name'] . ' (' . $record['male_tag'] . ')'; ?> <td><?php echo date('d M Y', strtotime($record['breeding_date'])); ?> <td><?php echo date('d M Y', strtotime($record['expected_delivery'])); ?> <td><?php echo $record['actual_delivery'] ? date('d M Y', strtotime($record['actual_delivery'])) : '-'; ?> <td><span class="badge bg-<?php echo $record['status'] == 'completed' ? 'success' : ($record['status'] == 'pending' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($record['status'] ?? 'pending'); ?></span> <td><?php echo $record['notes']; ?> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Breeding - Pregnancy Tracking
    elseif ($page == 'breeding' && $subpage == 'pregnancy') {
        ?>
        <div class="page-title"><h2>Pregnancy Tracking</h2><div class="breadcrumb"><a href="?page=breeding&subpage=records" class="breadcrumb-item">Breeding</a><span class="breadcrumb-item active">Pregnancy Tracking</span></div></div>
        <div class="form-container mb-4">
            <h4>Add Pregnancy Check</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_pregnancy_check">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): if ($camel['gender'] == 'female'): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endif; endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Breeding Record (Optional)</label><select name="breeding_record_id" class="form-control"><option value="">-- None --</option><?php foreach ($breeding_records ?? [] as $breed): if (!$breed['actual_delivery']): ?><option value="<?php echo $breed['id']; ?>"><?php echo $breed['female_tag'] . ' - ' . date('d M Y', strtotime($breed['breeding_date'])); ?></option><?php endif; endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Check Date</label><input type="date" name="check_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Trimester</label><select name="trimester" class="form-control" required><option value="1">First Trimester (0-4 months)</option><option value="2">Second Trimester (4-8 months)</option><option value="3">Third Trimester (8-13 months)</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Health Status</label><input type="text" name="health_status" class="form-control" placeholder="e.g., Healthy, Needs attention" required></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Check</button>
            </form>
        </div>
        <div class="table-container"><h4>Pregnancy Checks</h4><table class="table table-striped table-hover"><thead> <th>Camel</th><th>Check Date</th><th>Trimester</th><th>Health Status</th><th>Notes</th> </thead><tbody><?php foreach ($pregnancy_tracking ?? [] as $track): ?> <tr><td><?php echo $track['camel_name'] . ' (' . $track['tag_id'] . ')'; ?> <td><?php echo date('d M Y', strtotime($track['check_date'])); ?> <td><?php echo $track['trimester']; ?> <td><?php echo $track['health_status']; ?> <td><?php echo $track['notes']; ?> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Breeding - Calves
    elseif ($page == 'breeding' && $subpage == 'calves') {
        ?>
        <div class="page-title"><h2>Calf Registration</h2><div class="breadcrumb"><a href="?page=breeding&subpage=records" class="breadcrumb-item">Breeding</a><span class="breadcrumb-item active">Calves</span></div></div>
        <div class="form-container mb-4">
            <h4>Register New Calf</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_calf">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Calf Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Tag ID</label><input type="text" name="tag_id" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Mother</label><select name="mother_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): if ($camel['gender'] == 'female'): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endif; endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Birth Date</label><input type="date" name="birth_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Gender</label><select name="gender" class="form-control" required><option value="male">Male</option><option value="female">Female</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Birth Weight (kg)</label><input type="number" name="birth_weight" class="form-control" step="0.1"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Health Status</label><input type="text" name="health_status" class="form-control" value="Healthy"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Breeding Record (Optional)</label><select name="breeding_id" class="form-control"><option value="">-- None --</option><?php foreach ($breeding_records ?? [] as $breed): if (!$breed['actual_delivery']): ?><option value="<?php echo $breed['id']; ?>"><?php echo $breed['female_tag'] . ' - ' . date('d M Y', strtotime($breed['breeding_date'])); ?></option><?php endif; endforeach; ?></select></div>
                    <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Register Calf</button>
            </form>
        </div>
        <div class="table-container"><h4>Calves</h4><table class="table table-striped table-hover"><thead> <th>Name</th><th>Tag ID</th><th>Mother</th><th>Birth Date</th><th>Gender</th><th>Weight</th><th>Health</th><th>Notes</th> </thead><tbody><?php foreach ($calves ?? [] as $calf): ?> <tr><td><?php echo $calf['name']; ?> <td><?php echo $calf['tag_id']; ?> <td><?php echo $calf['mother_name'] . ' (' . $calf['mother_tag'] . ')'; ?> <td><?php echo date('d M Y', strtotime($calf['birth_date'])); ?> <td><?php echo ucfirst($calf['gender']); ?> <td><?php echo $calf['birth_weight'] ? $calf['birth_weight'] . ' kg' : '-'; ?> <td><?php echo $calf['health_status']; ?> <td><?php echo $calf['notes']; ?> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Breeding - Fertility Reports
    elseif ($page == 'breeding' && $subpage == 'fertility') {
        ?>
        <div class="page-title"><h2>Fertility Reports</h2><div class="breadcrumb"><a href="?page=breeding&subpage=records" class="breadcrumb-item">Breeding</a><span class="breadcrumb-item active">Fertility</span></div></div>
        <div class="form-container mb-4">
            <h4>Add Fertility Report</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_fertility_report">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): if ($camel['gender'] == 'female'): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endif; endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Report Date</label><input type="date" name="report_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Fertility Score (1-10)</label><input type="number" name="fertility_score" class="form-control" min="1" max="10" required></div>
                    <div class="col-md-6 mb-3"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="recommended_for_breeding" value="1"><label class="form-check-label">Recommended for Breeding</label></div></div>
                    <div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Report</button>
            </form>
        </div>
        <div class="table-container"><h4>Fertility Reports</h4><table class="table table-striped table-hover"><thead> <th>Camel</th><th>Report Date</th><th>Score</th><th>Recommended</th><th>Notes</th> </thead><tbody><?php foreach ($fertility_reports ?? [] as $report): ?> <tr><td><?php echo $report['camel_name'] . ' (' . $report['tag_id'] . ')'; ?> <td><?php echo date('d M Y', strtotime($report['report_date'])); ?> <td><div class="progress" style="height: 15px;"><div class="progress-bar bg-<?php echo $report['fertility_score'] >= 8 ? 'success' : ($report['fertility_score'] >= 5 ? 'warning' : 'danger'); ?>" style="width: <?php echo $report['fertility_score'] * 10; ?>%"><?php echo $report['fertility_score']; ?>/10</div></div> <td><?php if ($report['recommended_for_breeding']): ?><span class="badge bg-success">Yes</span><?php else: ?><span class="badge bg-secondary">No</span><?php endif; ?> <td><?php echo $report['notes']; ?> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Feed & Nutrition - Add Feed Type
    elseif ($page == 'feed' && $subpage == 'add') {
        ?>
        <div class="page-title">
            <h2>Add Feed Type</h2>
            <div class="breadcrumb">
                <a href="?page=feed&subpage=types" class="breadcrumb-item">Feed Management</a>
                <span class="breadcrumb-item active">Add Feed Type</span>
            </div>
        </div>
        <div class="form-container">
            <h4>Add New Feed Type</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_feed_type">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Feed Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control" required>
                            <option value="Forage">Forage</option>
                            <option value="Concentrate">Concentrate</option>
                            <option value="Supplement">Supplement</option>
                            <option value="Green Feed">Green Feed</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-control" required>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="ton">Ton</option>
                            <option value="piece">Piece</option>
                            <option value="bag">Bag</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price per Unit ($)</label>
                        <input type="number" name="price_per_unit" class="form-control" step="0.01" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Feed Type</button>
            </form>
        </div>
        <?php
    }
    
    // Feed & Nutrition - Types
    elseif ($page == 'feed' && $subpage == 'types') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_feed_type') {
            $name = trim($_POST['name']);
            $category = trim($_POST['category']);
            $unit = trim($_POST['unit']);
            $price_per_unit = (float)$_POST['price_per_unit'];
            
            $stmt = $pdo->prepare("INSERT INTO feed_types (name, category, unit, price_per_unit) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $category, $unit, $price_per_unit]);
            
            $_SESSION['message'] = "Feed Type added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=feed&subpage=types");
            exit;
        }
        $feed_types = $pdo->query("SELECT * FROM feed_types ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Feed Types</h2><div class="breadcrumb"><span class="breadcrumb-item active">Manage Feed Types</span></div></div>
        <div class="form-container mb-4">
            <h4>Add New Feed Type</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_feed_type">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Feed Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Category</label><select name="category" class="form-control" required><option value="Forage">Forage</option><option value="Concentrate">Concentrate</option><option value="Supplement">Supplement</option><option value="Green Feed">Green Feed</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Unit</label><select name="unit" class="form-control" required><option value="kg">Kilogram (kg)</option><option value="ton">Ton</option><option value="piece">Piece</option><option value="bag">Bag</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Price per Unit ($)</label><input type="number" name="price_per_unit" class="form-control" step="0.01" required></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Feed Type</button>
            </form>
        </div>
        <div class="table-container"><h4>Feed Types List</h4><table class="table table-striped table-hover"><thead> <tr><th>Name</th><th>Category</th><th>Unit</th><th>Price/Unit</th></tr> </thead><tbody><?php foreach ($feed_types ?? [] as $type): ?> <tr><td><?php echo $type['name']; ?></td> <td><?php echo $type['category']; ?></td> <td><?php echo $type['unit']; ?></td> <td>$<?php echo number_format($type['price_per_unit'], 2); ?></td></tr> <?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Feed & Nutrition - Plans
    elseif ($page == 'feed' && $subpage == 'plans') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_feed_plan') {
            $name = trim($_POST['name']);
            $camel_type = trim($_POST['camel_type']);
            $feed_type_id = (int)$_POST['feed_type_id'];
            $daily_quantity = (float)$_POST['daily_quantity'];
            $description = trim($_POST['description']);
            
            $stmt = $pdo->prepare("INSERT INTO feed_plans (name, camel_type, feed_type_id, daily_quantity, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $camel_type, $feed_type_id, $daily_quantity, $description]);
            
            $_SESSION['message'] = "Feed Plan added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=feed&subpage=plans");
            exit;
        }
        $feed_types = $pdo->query("SELECT id, name FROM feed_types ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $feed_plans = $pdo->query("SELECT fp.*, ft.name as feed_type_name FROM feed_plans fp JOIN feed_types ft ON fp.feed_type_id = ft.id ORDER BY fp.name")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Feed Plans</h2><div class="breadcrumb"><span class="breadcrumb-item active">Manage Feed Plans</span></div></div>
        <div class="form-container mb-4">
            <h4>Add New Feed Plan</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_feed_plan">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Plan Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Camel Type</label><select name="camel_type" class="form-control" required><option value="Lactating Female">Lactating Female</option><option value="Pregnant Female">Pregnant Female</option><option value="Adult Male">Adult Male</option><option value="Young Calf">Young Calf</option><option value="Growing">Growing</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Feed Type</label><select name="feed_type_id" class="form-control" required><?php foreach ($feed_types ?? [] as $type): ?><option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Daily Quantity (kg)</label><input type="number" name="daily_quantity" class="form-control" step="0.1" required></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Feed Plan</button>
            </form>
        </div>
        <div class="table-container"><h4>Existing Feed Plans</h4><table class="table table-striped table-hover"><thead> <tr><th>Name</th><th>Camel Type</th><th>Feed Type</th><th>Daily Quantity</th><th>Description</th></tr> </thead><tbody><?php foreach ($feed_plans ?? [] as $plan): ?> <tr><td><?php echo $plan['name']; ?></td> <td><?php echo $plan['camel_type']; ?></td> <td><?php echo $plan['feed_type_name']; ?></td> <td><?php echo $plan['daily_quantity']; ?> kg</td> <td><?php echo $plan['description']; ?></td></tr> <?php endforeach; ?></tbody> </table></div>
        <?php
    }
    
    // Feed & Nutrition - Consumption
    elseif ($page == 'feed' && $subpage == 'consumption') {
        ?>
        <div class="page-title"><h2>Feed Consumption</h2><div class="breadcrumb"><span class="breadcrumb-item active">Record Feed Consumption</span></div></div>
        <div class="form-container mb-4">
            <h4>Record Feed Consumption</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_feed_consumption">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><?php foreach ($camels ?? [] as $camel): ?><option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Feed Type</label><select name="feed_type_id" class="form-control" required><?php foreach ($feed_types ?? [] as $type): ?><option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Date</label><input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Quantity (kg)</label><input type="number" name="quantity" class="form-control" step="0.1" required></div>
                </div>
                <button type="submit" class="btn btn-primary">Record Consumption</button>
            </form>
        </div>
        <div class="table-container"><h4>Consumption History</h4><table class="table table-striped table-hover"><thead> <th>Camel</th><th>Feed Type</th><th>Date</th><th>Quantity</th> </thead><tbody><?php foreach ($feed_consumption ?? [] as $consume): ?> <tr><td><?php echo $consume['camel_name'] . ' (' . $consume['tag_id'] . ')'; ?> <td><?php echo $consume['feed_type_name']; ?> <td><?php echo date('d M Y', strtotime($consume['date'])); ?> <td><?php echo $consume['quantity']; ?> kg <?php endforeach; ?></tbody> </div>
        <?php
    }
    
    // Feed & Nutrition - Suppliers
    elseif ($page == 'feed' && $subpage == 'suppliers') {
        ?>
        <div class="page-title"><h2>Feed Suppliers</h2><div class="breadcrumb"><span class="breadcrumb-item active">Manage Suppliers</span></div></div>
        <div class="form-container mb-4">
            <h4>Add New Supplier</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_feed_supplier">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Company Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Supplier</button>
            </form>
        </div>
        <div class="table-container"><h4>Suppliers List</h4><table class="table table-striped table-hover"><thead> <th>Name</th><th>Contact Person</th><th>Phone</th><th>Email</th><th>Address</th> </thead><tbody><?php foreach ($feed_suppliers ?? [] as $supplier): ?> <tr><td><?php echo $supplier['name']; ?> <td><?php echo $supplier['contact_person']; ?> <td><?php echo $supplier['phone']; ?> <td><?php echo $supplier['email']; ?> <td><?php echo $supplier['address']; ?> <?php endforeach; ?></tbody> </div>
        <?php
    }
    
    // Feed & Nutrition - Purchases
    elseif ($page == 'feed' && $subpage == 'purchases') {
        ?>
        <div class="page-title"><h2>Feed Purchases</h2><div class="breadcrumb"><span class="breadcrumb-item active">Record Purchases</span></div></div>
        <div class="form-container mb-4">
            <h4>Record New Purchase</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_feed_purchase">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-control" required><?php foreach ($feed_suppliers ?? [] as $supplier): ?><option value="<?php echo $supplier['id']; ?>"><?php echo $supplier['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Feed Type</label><select name="feed_type_id" class="form-control" required><?php foreach ($feed_types ?? [] as $type): ?><option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" step="0.1" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Unit Price ($)</label><input type="number" name="unit_price" class="form-control" step="0.01" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Payment Status</label><select name="payment_status" class="form-control" required><option value="paid">Paid</option><option value="unpaid">Unpaid</option><option value="partial">Partial</option></select></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary">Record Purchase</button>
            </form>
        </div>
        <div class="table-container"><h4>Purchase History</h4><table class="table table-striped table-hover"><thead> <th>Date</th><th>Supplier</th><th>Feed Type</th><th>Quantity</th><th>Unit Price</th><th>Total</th><th>Payment Status</th> </thead><tbody><?php foreach ($feed_purchases ?? [] as $purchase): ?> <tr><td><?php echo date('d M Y', strtotime($purchase['purchase_date'])); ?> <td><?php echo $purchase['supplier_name']; ?> <td><?php echo $purchase['feed_type_name']; ?> <td><?php echo $purchase['quantity']; ?> <td>$<?php echo number_format($purchase['unit_price'], 2); ?> <td>$<?php echo number_format($purchase['total_amount'], 2); ?> <td><span class="badge bg-<?php echo $purchase['payment_status'] == 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($purchase['payment_status']); ?></span> <?php endforeach; ?></tbody> </div>
        <?php
    }
    
    // =========================================================================
    // HEALTH MANAGEMENT MODULE (VET & ADMIN)
    // =========================================================================
    elseif ($page == 'health' && $subpage == 'list') {
        $camels = $pdo->query("SELECT c.*, b.name as branch_name FROM camels c LEFT JOIN branches b ON c.branch_id = b.id WHERE c.status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Monitor Herd Health</h2><div class="breadcrumb"><span class="breadcrumb-item active">Health Overview</span></div></div>
        <div class="form-container">
            <h4>Health Status Overview</h4>
            <table class="table table-striped table-hover">
                <thead><tr><th>Camel</th><th>Gender</th><th>Branch</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($camels as $c): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($c['name']); ?></strong> (<?php echo htmlspecialchars($c['tag_id']); ?>)</td>
                        <td><?php echo ucfirst(htmlspecialchars($c['gender'])); ?></td>
                        <td><?php echo htmlspecialchars($c['branch_name'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="?page=health&subpage=view&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i> View Health History</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    elseif ($page == 'health' && $subpage == 'add-disease') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_disease') {
            $camel_id = (int)$_POST['camel_id'];
            $disease_name = trim($_POST['disease_name']);
            $diagnosed_date = $_POST['diagnosed_date'];
            $severity = $_POST['severity'];
            $treatment = trim($_POST['treatment']);
            $vet_name = $_SESSION['full_name'] ?? 'Vet';
            
            $stmt = $pdo->prepare("INSERT INTO disease_records (camel_id, disease_name, diagnosed_date, severity, treatment, vet_name) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$camel_id, $disease_name, $diagnosed_date, $severity, $treatment, $vet_name]);
            
            $_SESSION['message'] = "Disease record added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=health&subpage=list");
            exit;
        }
        $camels = $pdo->query("SELECT id, tag_id, name FROM camels WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Log Disease Record</h2><div class="breadcrumb"><a href="?page=health&subpage=list" class="breadcrumb-item">Health</a><span class="breadcrumb-item active">Add Disease</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="add_disease">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><option value="">-- Select Camel --</option><?php foreach ($camels as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['tag_id']); ?> - <?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Disease Name</label><input type="text" name="disease_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Diagnosed Date</label><input type="date" name="diagnosed_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Severity</label><select name="severity" class="form-control" required><option value="mild">Mild</option><option value="moderate">Moderate</option><option value="severe">Severe</option></select></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Treatment Prescribed</label><textarea name="treatment" class="form-control" rows="3" required></textarea></div>
                </div>
                <button type="submit" class="btn btn-danger"><i class="bi bi-bug"></i> Log Disease</button>
            </form>
        </div>
        <?php
    }
    elseif ($page == 'health' && $subpage == 'add-vaccination') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_vaccination') {
            $camel_id = (int)$_POST['camel_id'];
            $vaccine_name = trim($_POST['vaccine_name']);
            $vaccination_date = $_POST['vaccination_date'];
            $next_due_date = $_POST['next_due_date'] ?: null;
            $administered_by = $_SESSION['full_name'] ?? 'Vet';
            
            $stmt = $pdo->prepare("INSERT INTO vaccinations (camel_id, vaccine_name, vaccination_date, next_due_date, administered_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$camel_id, $vaccine_name, $vaccination_date, $next_due_date, $administered_by]);
            
            $_SESSION['message'] = "Vaccination record added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=health&subpage=list");
            exit;
        }
        $camels = $pdo->query("SELECT id, tag_id, name FROM camels WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Record Camel Vaccination</h2><div class="breadcrumb"><a href="?page=health&subpage=list" class="breadcrumb-item">Health</a><span class="breadcrumb-item active">Add Vaccination</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="add_vaccination">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><option value="">-- Select Camel --</option><?php foreach ($camels as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['tag_id']); ?> - <?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Vaccine Name</label><input type="text" name="vaccine_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Vaccination Date</label><input type="date" name="vaccination_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Next Due Date (Optional)</label><input type="date" name="next_due_date" class="form-control"></div>
                </div>
                <button type="submit" class="btn btn-info text-white"><i class="bi bi-shield-plus"></i> Record Vaccination</button>
            </form>
        </div>
        <?php
    }
    elseif ($page == 'health' && $subpage == 'add-visit') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_visit') {
            $camel_id = (int)$_POST['camel_id'];
            $visit_date = $_POST['visit_date'];
            $purpose = trim($_POST['purpose']);
            $findings = trim($_POST['findings']);
            $recommendations = trim($_POST['recommendations']);
            $follow_up_date = $_POST['follow_up_date'] ?: null;
            $vet_name = $_SESSION['full_name'] ?? 'Vet';
            
            $stmt = $pdo->prepare("INSERT INTO vet_visits (camel_id, visit_date, vet_name, purpose, findings, recommendations, follow_up_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$camel_id, $visit_date, $vet_name, $purpose, $findings, $recommendations, $follow_up_date]);
            
            $_SESSION['message'] = "Vet visit logged successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ?page=health&subpage=list");
            exit;
        }
        $camels = $pdo->query("SELECT id, tag_id, name FROM camels WHERE status = 'active'")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Add Vet Visit Log</h2><div class="breadcrumb"><a href="?page=health&subpage=list" class="breadcrumb-item">Health</a><span class="breadcrumb-item active">Vet Visit</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="add_visit">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Camel</label><select name="camel_id" class="form-control" required><option value="">-- Select Camel --</option><?php foreach ($camels as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['tag_id']); ?> - <?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Visit Date</label><input type="date" name="visit_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Purpose of Visit</label><input type="text" name="purpose" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Findings</label><textarea name="findings" class="form-control" rows="3" required></textarea></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Recommendations</label><textarea name="recommendations" class="form-control" rows="3" required></textarea></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Follow-up Date (Optional)</label><input type="date" name="follow_up_date" class="form-control"></div>
                </div>
                <button type="submit" class="btn btn-warning"><i class="bi bi-telephone"></i> Log Visit</button>
            </form>
        </div>
        <?php
    }
    elseif ($page == 'health' && $subpage == 'view') {
        $camel_id = (int)($_GET['id'] ?? 0);
        $camel = $pdo->prepare("SELECT * FROM camels WHERE id = ?");
        $camel->execute([$camel_id]);
        $camel = $camel->fetch(PDO::FETCH_ASSOC);
        
        if (!$camel) {
            echo "<div class='alert alert-danger'>Camel not found!</div>";
        } else {
            $diseases = $pdo->prepare("SELECT * FROM disease_records WHERE camel_id = ? ORDER BY diagnosed_date DESC");
            $diseases->execute([$camel_id]);
            $diseases = $diseases->fetchAll(PDO::FETCH_ASSOC);
            
            $vaccinations = $pdo->prepare("SELECT * FROM vaccinations WHERE camel_id = ? ORDER BY vaccination_date DESC");
            $vaccinations->execute([$camel_id]);
            $vaccinations = $vaccinations->fetchAll(PDO::FETCH_ASSOC);
            
            $visits = $pdo->prepare("SELECT * FROM vet_visits WHERE camel_id = ? ORDER BY visit_date DESC");
            $visits->execute([$camel_id]);
            $visits = $visits->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="page-title"><h2>Health History: <?php echo htmlspecialchars($camel['name']); ?></h2><div class="breadcrumb"><a href="?page=health&subpage=list" class="breadcrumb-item">Health</a><span class="breadcrumb-item active">View History</span></div></div>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="form-container">
                        <h4>Disease Records</h4>
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Date</th><th>Disease</th><th>Severity</th><th>Treatment</th><th>Vet</th></tr></thead>
                            <tbody>
                                <?php foreach($diseases as $d): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($d['diagnosed_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($d['disease_name']); ?></td>
                                    <td><span class="badge bg-<?php echo $d['severity'] == 'severe' ? 'danger' : ($d['severity'] == 'moderate' ? 'warning' : 'info'); ?>"><?php echo ucfirst($d['severity']); ?></span></td>
                                    <td><?php echo htmlspecialchars($d['treatment']); ?></td>
                                    <td><?php echo htmlspecialchars($d['vet_name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($diseases)): ?><tr><td colspan="5" class="text-center text-muted">No disease records found.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="form-container">
                        <h4>Vaccinations</h4>
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Date</th><th>Vaccine</th><th>Next Due</th></tr></thead>
                            <tbody>
                                <?php foreach($vaccinations as $v): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($v['vaccination_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($v['vaccine_name']); ?></td>
                                    <td><?php echo $v['next_due_date'] ? date('d M Y', strtotime($v['next_due_date'])) : 'N/A'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($vaccinations)): ?><tr><td colspan="3" class="text-center text-muted">No vaccinations found.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="form-container">
                        <h4>Vet Visits</h4>
                        <table class="table table-sm table-striped">
                            <thead><tr><th>Date</th><th>Purpose</th><th>Vet</th></tr></thead>
                            <tbody>
                                <?php foreach($visits as $v): ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($v['visit_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($v['purpose']); ?></td>
                                    <td><?php echo htmlspecialchars($v['vet_name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($visits)): ?><tr><td colspan="3" class="text-center text-muted">No visits found.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    
    // Change Password Page
    elseif ($page == 'change-password') {
        ?>
        <div class="page-title"><h2>Badal Password-ka / Change Password</h2><div class="breadcrumb"><span class="breadcrumb-item active">Change Password</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="self_change_password">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password-ka Hada (Current Password)</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password-ka Cusub (New Password)</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Hubi Password-ka Cusub (Confirm New Password)</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Cusboonaysii Password-ka / Update Password</button>
            </form>
        </div>
        <?php
    }
    
    // Default for any page not handled
    else {
        ?>
        <div class="page-title">
            <h2><?php echo ucfirst(str_replace('-', ' ', $page)); ?></h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active"><?php echo ucfirst(str_replace('-', ' ', $subpage)); ?></span>
            </div>
        </div>
        <div class="alert alert-warning mt-4">
            <h5><i class="bi bi-exclamation-triangle"></i> Boggan Waa La Dhisayaa (Under Construction)</h5>
            <p>Boggan (<?php echo ucfirst(str_replace('-', ' ', $page)); ?> - <?php echo ucfirst(str_replace('-', ' ', $subpage)); ?>) wali lama dhamaystirin. Fadlan dib u soo noqo mar kale.</p>
        </div>
        <?php
    }
}
