<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Processing Pages - Milk Collection, Processing, Warehouse

global $page, $subpage, $pdo, $logged_in, $current_role;

if (!isset($page)) $page = $_GET['page'] ?? 'dashboard';
if (!isset($subpage)) $subpage = $_GET['subpage'] ?? 'list';

if ($logged_in && $current_role != 'customer') {
    
    // Get data for processing pages
    try {
        $branch_context_mc = getBranchContextQuery('mc');
        $branch_context_c = getBranchContextQuery('c');
        
        $milk_collections = $pdo->query("SELECT mc.*, c.tag_id, c.name as camel_name, u.full_name as collector_name FROM milk_collection mc JOIN camels c ON mc.camel_id = c.id JOIN users u ON mc.collector_id = u.id WHERE {$branch_context_mc} ORDER BY mc.collection_date DESC, mc.shift")->fetchAll(PDO::FETCH_ASSOC);
        $camels = $pdo->query("SELECT * FROM camels c WHERE {$branch_context_c} AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $milk_collections = [];
        $camels = [];
    }
    
    // Milk Collection - Daily
    if ($page == 'milk-collection' && $subpage == 'daily') {
        ?>
        <div class="page-title">
            <h2>Daily Milk Collection</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">View Collections</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                     <tr><th>Date</th><th>Camel</th><th>Shift</th><th>Quantity (L)</th><th>Quality</th><th>Collected By</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($milk_collections ?? [] as $collection): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($collection['collection_date'])); ?></td>
                        <td><?php echo $collection['camel_name'] . ' (' . $collection['tag_id'] . ')'; ?></td>
                        <td><?php echo ucfirst($collection['shift']); ?></td>
                        <td><?php echo $collection['quantity_liters']; ?> L</td>
                        <td><span class="badge bg-<?php echo $collection['quality_grade'] == 'A' ? 'success' : ($collection['quality_grade'] == 'B' ? 'info' : ($collection['quality_grade'] == 'C' ? 'warning' : 'danger')); ?>">Grade <?php echo $collection['quality_grade']; ?></span></td>
                        <td><?php echo $collection['collector_name']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($milk_collections)): ?>
                    <tr><td colspan="6" class="text-center">No milk collections found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Milk Collection - Add
    elseif ($page == 'milk-collection' && $subpage == 'add') {
        ?>
        <div class="page-title">
            <h2>Record Milk Collection</h2>
            <div class="breadcrumb">
                <a href="?page=milk-collection&subpage=daily" class="breadcrumb-item">Milk Collection</a>
                <span class="breadcrumb-item active">Add Collection</span>
            </div>
        </div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="add_milk_collection">
                <input type="hidden" name="collector_id" value="<?php echo $_SESSION['user_id']; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Select Camel</label>
                        <select name="camel_id" class="form-control" required>
                            <?php foreach ($camels ?? [] as $camel): ?>
                            <option value="<?php echo $camel['id']; ?>"><?php echo $camel['tag_id']; ?> - <?php echo $camel['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Collection Date</label>
                        <input type="date" name="collection_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Shift</label>
                        <select name="shift" class="form-control" required>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quantity (Liters)</label>
                        <input type="number" name="quantity_liters" class="form-control" step="0.1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quality Grade</label>
                        <select name="quality_grade" class="form-control" required>
                            <option value="A">Grade A - Premium</option>
                            <option value="B">Grade B - Standard</option>
                            <option value="C">Grade C - Economy</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Record Collection</button>
            </form>
        </div>
        <?php
    }
    
    // Milk Collection - Quality
    elseif ($page == 'milk-collection' && $subpage == 'quality') {
        $branch_context = getBranchContextQuery();
        $quality_stats = $pdo->query("SELECT quality_grade, COUNT(*) as count, SUM(quantity_liters) as total FROM milk_collection WHERE {$branch_context} GROUP BY quality_grade")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Quality Grading</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Quality Analysis</span>
            </div>
        </div>
        <div class="row mb-4">
            <?php foreach ($quality_stats as $stat): ?>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-star<?php echo $stat['quality_grade'] == 'A' ? '-fill' : ($stat['quality_grade'] == 'B' ? '' : '-half'); ?>"></i></div>
                    <h3><?php echo $stat['total']; ?> L</h3>
                    <p>Grade <?php echo $stat['quality_grade']; ?> (<?php echo $stat['count']; ?> collections)</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="table-container">
            <h4>Recent Quality Records</h4>
            <table class="table">
                <thead><tr><th>Date</th><th>Camel</th><th>Shift</th><th>Quantity</th><th>Quality</th></tr></thead>
                <tbody>
                    <?php foreach (array_slice($milk_collections ?? [], 0, 20) as $collection): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($collection['collection_date'])); ?></td>
                        <td><?php echo $collection['camel_name']; ?></td>
                        <td><?php echo ucfirst($collection['shift']); ?></td>
                        <td><?php echo $collection['quantity_liters']; ?> L</td>
                        <td><span class="badge bg-<?php echo $collection['quality_grade'] == 'A' ? 'success' : ($collection['quality_grade'] == 'B' ? 'info' : ($collection['quality_grade'] == 'C' ? 'warning' : 'danger')); ?>">Grade <?php echo $collection['quality_grade']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Milk Collection - Rejected
    elseif ($page == 'milk-collection' && $subpage == 'rejected') {
        $branch_context = getBranchContextQuery('mc');
        $rejected_collections = $pdo->query("SELECT mc.*, c.tag_id, c.name as camel_name, u.full_name as collector_name FROM milk_collection mc JOIN camels c ON mc.camel_id = c.id JOIN users u ON mc.collector_id = u.id WHERE {$branch_context} AND mc.quality_grade = 'rejected' ORDER BY mc.collection_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Rejected Milk Records</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Quality Control</span>
            </div>
        </div>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> Total Rejected: <?php echo array_sum(array_column($rejected_collections, 'quantity_liters')); ?> L
        </div>
        <div class="table-container">
            <table class="table">
                <thead><tr><th>Date</th><th>Camel</th><th>Shift</th><th>Quantity (L)</th><th>Collected By</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($rejected_collections as $collection): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($collection['collection_date'])); ?></td>
                        <td><?php echo $collection['camel_name'] . ' (' . $collection['tag_id'] . ')'; ?></td>
                        <td><?php echo ucfirst($collection['shift']); ?></td>
                        <td><?php echo $collection['quantity_liters']; ?> L</td>
                        <td><?php echo $collection['collector_name']; ?></td>
                        <td><button class="btn btn-sm btn-warning" onclick="alert('Investigate quality issue')">Investigate</button></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rejected_collections)): ?>
                    <tr><td colspan="6" class="text-center">No rejected milk records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Milk Collection - Analytics Report
    elseif ($page == 'milk-collection' && $subpage == 'analytics') {
        $branch_context = getBranchContextQuery('mc');
        $interval = $_GET['interval'] ?? 'daily';
        
        if ($interval == 'daily') {
            $prod_query = "SELECT mc.collection_date as period, 
                                  SUM(mc.quantity_liters) as total_liters,
                                  COUNT(DISTINCT mc.camel_id) as camels_milked,
                                  AVG(mc.quantity_liters) as avg_per_camel,
                                  SUM(CASE WHEN mc.quality_grade = 'rejected' THEN mc.quantity_liters ELSE 0 END) as rejected_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'A' THEN mc.quantity_liters ELSE 0 END) as grade_a_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'B' THEN mc.quantity_liters ELSE 0 END) as grade_b_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'C' THEN mc.quantity_liters ELSE 0 END) as grade_c_liters
                           FROM milk_collection mc 
                           WHERE {$branch_context}
                           GROUP BY mc.collection_date 
                           ORDER BY mc.collection_date DESC 
                           LIMIT 30";
        } elseif ($interval == 'weekly') {
            $prod_query = "SELECT YEARWEEK(mc.collection_date, 1) as period,
                                  SUM(mc.quantity_liters) as total_liters,
                                  COUNT(DISTINCT mc.camel_id) as camels_milked,
                                  AVG(mc.quantity_liters) as avg_per_camel,
                                  SUM(CASE WHEN mc.quality_grade = 'rejected' THEN mc.quantity_liters ELSE 0 END) as rejected_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'A' THEN mc.quantity_liters ELSE 0 END) as grade_a_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'B' THEN mc.quantity_liters ELSE 0 END) as grade_b_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'C' THEN mc.quantity_liters ELSE 0 END) as grade_c_liters
                           FROM milk_collection mc 
                           WHERE {$branch_context}
                           GROUP BY YEARWEEK(mc.collection_date, 1) 
                           ORDER BY period DESC";
        } elseif ($interval == 'monthly') {
            $prod_query = "SELECT DATE_FORMAT(mc.collection_date, '%Y-%m') as period,
                                  SUM(mc.quantity_liters) as total_liters,
                                  COUNT(DISTINCT mc.camel_id) as camels_milked,
                                  AVG(mc.quantity_liters) as avg_per_camel,
                                  SUM(CASE WHEN mc.quality_grade = 'rejected' THEN mc.quantity_liters ELSE 0 END) as rejected_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'A' THEN mc.quantity_liters ELSE 0 END) as grade_a_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'B' THEN mc.quantity_liters ELSE 0 END) as grade_b_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'C' THEN mc.quantity_liters ELSE 0 END) as grade_c_liters
                           FROM milk_collection mc 
                           WHERE {$branch_context}
                           GROUP BY DATE_FORMAT(mc.collection_date, '%Y-%m') 
                           ORDER BY period DESC";
        } else { // yearly
            $prod_query = "SELECT DATE_FORMAT(mc.collection_date, '%Y') as period,
                                  SUM(mc.quantity_liters) as total_liters,
                                  COUNT(DISTINCT mc.camel_id) as camels_milked,
                                  AVG(mc.quantity_liters) as avg_per_camel,
                                  SUM(CASE WHEN mc.quality_grade = 'rejected' THEN mc.quantity_liters ELSE 0 END) as rejected_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'A' THEN mc.quantity_liters ELSE 0 END) as grade_a_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'B' THEN mc.quantity_liters ELSE 0 END) as grade_b_liters,
                                  SUM(CASE WHEN mc.quality_grade = 'C' THEN mc.quantity_liters ELSE 0 END) as grade_c_liters
                           FROM milk_collection mc 
                           WHERE {$branch_context}
                           GROUP BY DATE_FORMAT(mc.collection_date, '%Y') 
                           ORDER BY period DESC";
        }
        
        $report_data = $pdo->query($prod_query)->fetchAll(PDO::FETCH_ASSOC);
        
        // Cumulative Metrics
        $total_liters = array_sum(array_column($report_data, 'total_liters'));
        $total_rejected = array_sum(array_column($report_data, 'rejected_liters'));
        $total_grade_a = array_sum(array_column($report_data, 'grade_a_liters'));
        $avg_yield = count($report_data) > 0 ? (array_sum(array_column($report_data, 'avg_per_camel')) / count($report_data)) : 0;
        
        $grade_a_pct = $total_liters > 0 ? ($total_grade_a / $total_liters) * 100 : 0;
        $rejected_pct = $total_liters > 0 ? ($total_rejected / $total_liters) * 100 : 0;
        ?>
        <div class="page-title d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Milk Production Analytics</h2>
                <div class="breadcrumb"><span class="breadcrumb-item active">Aggregated Production Reports</span></div>
            </div>
            <button onclick="window.print()" class="btn btn-outline-primary d-print-none"><i class="bi bi-printer"></i> Print Report</button>
        </div>
        
        <!-- Filters -->
        <div class="form-container mb-4 d-print-none">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="milk-collection">
                <input type="hidden" name="subpage" value="analytics">
                
                <div class="col-md-4">
                    <label class="form-label fw-bold">Aggregate Interval</label>
                    <select name="interval" class="form-select" onchange="this.form.submit()">
                        <option value="daily" <?php echo $interval == 'daily' ? 'selected' : ''; ?>>Daily Production</option>
                        <option value="weekly" <?php echo $interval == 'weekly' ? 'selected' : ''; ?>>Weekly Production</option>
                        <option value="monthly" <?php echo $interval == 'monthly' ? 'selected' : ''; ?>>Monthly Production</option>
                        <option value="yearly" <?php echo $interval == 'yearly' ? 'selected' : ''; ?>>Yearly Production</option>
                    </select>
                </div>
            </form>
        </div>
        
        <!-- Metrics Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white fw-bold">Total Milk Yield</div>
                    <div class="card-body text-center">
                        <h3 class="my-2 text-primary"><?php echo number_format($total_liters, 1); ?> L</h3>
                        <p class="text-muted mb-0">Total volume collected</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white fw-bold">Average Yield/Camel</div>
                    <div class="card-body text-center">
                        <h3 class="my-2 text-success"><?php echo number_format($avg_yield, 2); ?> L</h3>
                        <p class="text-muted mb-0">Average yield per milking session</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-info text-white fw-bold">Premium Grade A</div>
                    <div class="card-body text-center">
                        <h3 class="my-2 text-info"><?php echo number_format($total_grade_a, 1); ?> L</h3>
                        <p class="text-muted mb-0"><?php echo number_format($grade_a_pct, 1); ?>% of total production</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger text-white fw-bold">Rejected Volume</div>
                    <div class="card-body text-center">
                        <h3 class="my-2 text-danger"><?php echo number_format($total_rejected, 1); ?> L</h3>
                        <p class="text-muted mb-0"><?php echo number_format($rejected_pct, 1); ?>% rejection rate</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Table container -->
        <div class="card border-0 shadow-sm p-4 print-container">
            <div class="text-center mb-4 d-none d-print-block">
                <h3><?php echo htmlspecialchars(COMPANY_NAME); ?></h3>
                <h5 class="text-muted">Milk Production & Collection Aggregation Report</h5>
                <h6>Interval: <?php echo ucfirst($interval); ?></h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Period</th>
                            <th class="text-end">Total Yield (L)</th>
                            <th class="text-end">Camels Milked</th>
                            <th class="text-end">Avg Yield/Camel</th>
                            <th class="text-end">Grade A (L)</th>
                            <th class="text-end">Grade B/C (L)</th>
                            <th class="text-end text-danger">Rejected (L)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report_data)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No production records found for this period.</td></tr>
                        <?php else: ?>
                            <?php foreach ($report_data as $row): ?>
                                <tr>
                                    <td class="fw-bold">
                                        <?php 
                                        if ($interval == 'daily') {
                                            echo date('d M Y', strtotime($row['period']));
                                        } else {
                                            echo htmlspecialchars($row['period']);
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end fw-bold text-primary"><?php echo number_format($row['total_liters'], 1); ?> L</td>
                                    <td class="text-end"><?php echo $row['camels_milked']; ?></td>
                                    <td class="text-end"><?php echo number_format($row['avg_per_camel'], 2); ?> L</td>
                                    <td class="text-end text-success"><?php echo number_format($row['grade_a_liters'], 1); ?> L</td>
                                    <td class="text-end text-secondary"><?php echo number_format($row['grade_b_liters'] + $row['grade_c_liters'], 1); ?> L</td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($row['rejected_liters'], 1); ?> L</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    

    
    // Products Management - List
    elseif ($page == 'products' && $subpage == 'list') {
        $branch_context = getBranchContextQuery();
        $all_products = $pdo->query("SELECT * FROM products WHERE {$branch_context}")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>All Products</h2>
            <div class="d-flex gap-2">
                <a href="?page=products&subpage=add" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Product</a>
                <a href="?page=sales&subpage=pos" class="btn btn-success"><i class="bi bi-cart-plus"></i> New Sale (POS)</a>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Unit</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($all_products ?? [] as $product): ?>
                    <tr>
                        <td><img src="assets/images/<?php echo $product['image'] ?: 'default-product.jpg'; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" onerror="this.src='https://via.placeholder.com/50'"></td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['category']; ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['stock_quantity']; ?></td>
                        <td><?php echo $product['unit']; ?></td>
                        <td><span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo $product['status']; ?></span></td>
                        <td>
                            <a href="?page=products&subpage=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this product?')">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Products Management - Add
    elseif ($page == 'products' && $subpage == 'add') {
        ?>
        <div class="page-title">
            <h2>Add New Product</h2>
            <div class="breadcrumb">
                <a href="?page=products&subpage=list" class="breadcrumb-item">Products</a>
                <span class="breadcrumb-item active">Add Product</span>
            </div>
        </div>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Product Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Category</label><select name="category" class="form-control" required><option value="Milk">Milk</option><option value="Dairy">Dairy</option><option value="Beauty">Beauty</option><option value="Other">Other</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Price</label><input type="number" name="price" class="form-control" step="0.01" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Stock Quantity</label><input type="number" name="stock_quantity" class="form-control" step="0.01" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Unit</label><select name="unit" class="form-control" required><option value="liter">Liter</option><option value="piece">Piece</option><option value="kg">Kilogram</option><option value="pack">Pack</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Product Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>
        <?php
    }
    
    // Products Management - Edit
    elseif ($page == 'products' && $subpage == 'edit') {
        $product_id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo "<div class='alert alert-danger'>Product not found.</div>";
        } else {
        ?>
        <div class="page-title">
            <h2>Edit Product: <?php echo $product['name']; ?></h2>
            <div class="breadcrumb">
                <a href="?page=products&subpage=list" class="breadcrumb-item">Products</a>
                <span class="breadcrumb-item active">Edit Product</span>
            </div>
        </div>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_product">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Product Name</label><input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Category</label><select name="category" class="form-control" required>
                        <option value="Milk" <?php echo $product['category'] == 'Milk' ? 'selected' : ''; ?>>Milk</option>
                        <option value="Dairy" <?php echo $product['category'] == 'Dairy' ? 'selected' : ''; ?>>Dairy</option>
                        <option value="Beauty" <?php echo $product['category'] == 'Beauty' ? 'selected' : ''; ?>>Beauty</option>
                        <option value="Other" <?php echo $product['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Price</label><input type="number" name="price" class="form-control" step="0.01" value="<?php echo $product['price']; ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Stock Quantity</label><input type="number" name="stock_quantity" class="form-control" step="0.01" value="<?php echo $product['stock_quantity']; ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Unit</label><select name="unit" class="form-control" required>
                        <option value="liter" <?php echo $product['unit'] == 'liter' ? 'selected' : ''; ?>>Liter</option>
                        <option value="piece" <?php echo $product['unit'] == 'piece' ? 'selected' : ''; ?>>Piece</option>
                        <option value="kg" <?php echo $product['unit'] == 'kg' ? 'selected' : ''; ?>>Kilogram</option>
                        <option value="pack" <?php echo $product['unit'] == 'pack' ? 'selected' : ''; ?>>Pack</option>
                    </select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Product Image</label><input type="file" name="image" class="form-control" accept="image/*"><small>Leave blank to keep current image</small></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?php echo $product['description']; ?></textarea></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Status</label><select name="status" class="form-control">
                        <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select></div>
                </div>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </form>
        </div>
        <?php
        }
    }
    elseif ($page == 'products' && $subpage == 'stock') {
        $branch_context = getBranchContextQuery();
        $low_stock = $pdo->query("SELECT * FROM products WHERE {$branch_context} AND stock_quantity < 10 AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Stock Levels</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Inventory Status</span>
            </div>
        </div>
        <div class="table-container">
            <h4>Low Stock Alerts</h4>
            <table class="table">
                <thead> <th>Product</th><th>Category</th><th>Current Stock</th><th>Unit</th><th>Status</th><th>Action</th> </thead>
                <tbody>
                    <?php foreach ($low_stock as $product): ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['category']; ?></td>
                        <td><span class="badge bg-danger"><?php echo $product['stock_quantity']; ?> <?php echo $product['unit']; ?></span></td>
                        <td><?php echo $product['unit']; ?></td>
                        <td><span class="badge bg-warning">Low Stock</span></td>
                        <td><button class="btn btn-sm btn-primary" onclick="alert('Restock product')">Restock</button></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($low_stock)): ?>
                    <tr><td colspan="6" class="text-center">All products have sufficient stock.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Products Management - Categories
    elseif ($page == 'products' && $subpage == 'categories') {
        $branch_context = getBranchContextQuery();
        $categories = $pdo->query("SELECT DISTINCT category FROM products WHERE {$branch_context}")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Product Categories</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Manage Categories</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead> <th>Category</th><th>Product Count</th><th>Actions</th> </thead>
                <tbody>
                    <?php foreach ($categories as $cat): 
                        $branch_context = getBranchContextQuery();
                        $count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category = ? AND {$branch_context}");
                        $count->execute([$cat['category']]);
                        $product_count = $count->fetchColumn();
                    ?>
                    <tr>
                        <td><?php echo $cat['category']; ?></td>
                        <td><?php echo $product_count; ?> products</td>
                        <td><button class="btn btn-sm btn-info" onclick="window.location.href='?page=products&subpage=list&category=<?php echo urlencode($cat['category']); ?>'">View Products</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Products Management - Alerts
    elseif ($page == 'products' && $subpage == 'alerts') {
        $branch_context = getBranchContextQuery();
        $low_stock = $pdo->query("SELECT * FROM products WHERE {$branch_context} AND stock_quantity < 10 AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        $out_of_stock = $pdo->query("SELECT * FROM products WHERE {$branch_context} AND stock_quantity <= 0 AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Stock Alerts</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Low Stock & Out of Stock</span>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="alert alert-warning">
                    <h5>Low Stock Products (<?php echo count($low_stock); ?>)</h5>
                    <p>Products with stock less than 10 units</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-danger">
                    <h5>Out of Stock (<?php echo count($out_of_stock); ?>)</h5>
                    <p>Products with zero or negative stock</p>
                </div>
            </div>
        </div>
        <div class="table-container">
            <h4>Low Stock Products</h4>
            <table class="table">
                <thead> <th>Product</th><th>Category</th><th>Current Stock</th><th>Unit</th><th>Action</th> </thead>
                <tbody>
                    <?php foreach ($low_stock as $product): ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['category']; ?></td>
                        <td><span class="badge bg-warning"><?php echo $product['stock_quantity']; ?> <?php echo $product['unit']; ?></span></td>
                        <td><?php echo $product['unit']; ?></td>
                        <td><button class="btn btn-sm btn-primary" onclick="alert('Restock product: <?php echo $product['name']; ?>')">Restock</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h4 class="mt-4">Out of Stock Products</h4>
            <table class="table">
                <thead> <th>Product</th><th>Category</th><th>Current Stock</th><th>Unit</th><th>Action</th> </thead>
                <tbody>
                    <?php foreach ($out_of_stock as $product): ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['category']; ?></td>
                        <td><span class="badge bg-danger"><?php echo $product['stock_quantity']; ?> <?php echo $product['unit']; ?></span></td>
                        <td><?php echo $product['unit']; ?></td>
                        <td><button class="btn btn-sm btn-success" onclick="alert('Restock product: <?php echo $product['name']; ?>')">Restock</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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