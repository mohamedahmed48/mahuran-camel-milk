<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Sales Pages - Orders, Deliveries, Subscriptions, Finance, Reports, Salary

if ($logged_in && $current_role != 'customer') {
    
    // Get data for sales pages
    try {
        $branch_context = getBranchContextQuery();
        $where = $branch_context; // default where clause for generic queries
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
        
        $orders = $pdo->query("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE $where_o ORDER BY o.id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        $all_orders = $pdo->query("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE $where_o ORDER BY o.id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $drivers = $pdo->query("SELECT u.* FROM users u WHERE role='driver' AND ({$branch_context} OR u.branch_id IS NULL)")->fetchAll(PDO::FETCH_ASSOC);
        $delivery_zones = $pdo->query("SELECT * FROM delivery_zones WHERE {$branch_context}")->fetchAll(PDO::FETCH_ASSOC);
        
        // For drivers, filter pending deliveries to show only their own
        if ($current_role == 'driver') {
            $pending_deliveries = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.phone as customer_phone FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE $where_d AND d.driver_id = {$_SESSION['user_id']} AND d.status IN ('assigned', 'in_transit') ORDER BY d.delivery_date")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $pending_deliveries = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.phone as customer_phone FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE $where_d AND d.status IN ('assigned', 'in_transit') ORDER BY d.delivery_date")->fetchAll(PDO::FETCH_ASSOC);
        }
        $cancellation_requests = $pdo->query("SELECT o.*, c.name as customer_name, c.phone as customer_phone FROM orders o JOIN customers c ON o.customer_id = c.id WHERE $where_o AND o.status = 'cancellation_requested' ORDER BY o.order_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $customer_subscriptions = $pdo->query("SELECT cs.*, c.name as customer_name, COALESCE(pl.name, 'No Plan') as plan_name FROM customer_subscriptions cs LEFT JOIN customers c ON cs.customer_id = c.id LEFT JOIN subscription_plans pl ON cs.plan_id = pl.id WHERE $where_cs ORDER BY cs.start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $subscription_plans = $pdo->query("SELECT * FROM subscription_plans WHERE {$branch_context} AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        $subscription_invoices = $pdo->query("SELECT si.*, cs.customer_id, c.name as customer_name FROM subscription_invoices si LEFT JOIN customer_subscriptions cs ON si.subscription_id = cs.id LEFT JOIN customers c ON cs.customer_id = c.id WHERE $where_si ORDER BY si.invoice_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $all_payments = $pdo->query("SELECT p.*, o.order_number, c.name as customer_name FROM payments p JOIN orders o ON p.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE $where_p ORDER BY p.payment_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $all_expenses = $pdo->query("SELECT * FROM expenses WHERE {$branch_context} ORDER BY expense_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $salary_payments = $pdo->query("SELECT sp.*, s.employee_id, s.position FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE $where_sp ORDER BY sp.payment_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $salary_deductions = $pdo->query("SELECT sd.*, s.employee_id, s.position FROM salary_deductions sd JOIN staff s ON sd.staff_id = s.id WHERE $where_sd ORDER BY sd.year DESC, sd.month DESC")->fetchAll(PDO::FETCH_ASSOC);
        $salary_bonuses = $pdo->query("SELECT sb.*, s.employee_id, s.position FROM salary_bonuses sb JOIN staff s ON sb.staff_id = s.id WHERE $where_sb ORDER BY sb.year DESC, sb.month DESC")->fetchAll(PDO::FETCH_ASSOC);
        $payslips = $pdo->query("SELECT p.*, s.employee_id, s.position, u.full_name as employee_name FROM payslips p JOIN staff s ON p.staff_id = s.id JOIN users u ON s.user_id = u.id WHERE $where_p ORDER BY p.generated_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $sales_targets = $pdo->query("SELECT st.*, s.position as staff_name FROM sales_targets st LEFT JOIN staff s ON st.staff_id = s.id WHERE $where_st ORDER BY st.year DESC, st.month DESC")->fetchAll(PDO::FETCH_ASSOC);
        $sales_commissions = $pdo->query("SELECT sc.*, u.full_name as staff_name, o.order_number FROM sales_commissions sc LEFT JOIN staff s ON sc.staff_id = s.id LEFT JOIN users u ON s.user_id = u.id LEFT JOIN orders o ON sc.order_id = o.id WHERE $where_sc ORDER BY sc.calculated_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        
        // For drivers, filter driver performance to show only their own
        if ($current_role == 'driver') {
            $driver_performance = $pdo->query("SELECT dp.*, u.full_name as driver_name FROM driver_performance dp JOIN users u ON dp.driver_id = u.id WHERE $where_dp AND dp.driver_id = {$_SESSION['user_id']} ORDER BY dp.delivery_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $driver_performance = $pdo->query("SELECT dp.*, u.full_name as driver_name FROM driver_performance dp JOIN users u ON dp.driver_id = u.id WHERE $where_dp ORDER BY dp.delivery_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // For drivers, filter delivery proofs to show only their own
        if ($current_role == 'driver') {
            $delivery_proofs = $pdo->query("SELECT dp.*, d.order_id, o.order_number FROM delivery_proofs dp JOIN deliveries d ON dp.delivery_id = d.id JOIN orders o ON d.order_id = o.id WHERE $where_dp AND d.driver_id = {$_SESSION['user_id']} ORDER BY dp.uploaded_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $delivery_proofs = $pdo->query("SELECT dp.*, d.order_id, o.order_number FROM delivery_proofs dp JOIN deliveries d ON dp.delivery_id = d.id JOIN orders o ON d.order_id = o.id WHERE $where_dp ORDER BY dp.uploaded_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        }
        $products = $pdo->query("SELECT * FROM products WHERE {$branch_context} AND status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $customers = $pdo->query("SELECT * FROM customers WHERE {$branch_context} ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $staff_list = $pdo->query("SELECT s.*, u.full_name FROM staff s LEFT JOIN users u ON s.user_id = u.id WHERE $where_s ORDER BY s.employee_id")->fetchAll(PDO::FETCH_ASSOC);
        $settings_raw = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
        $settings = array_column($settings_raw, 'setting_value', 'setting_key');
        
    } catch (PDOException $e) {
        $orders = [];
        $all_orders = [];
        $drivers = [];
        $delivery_zones = [];
        $pending_deliveries = [];
        $cancellation_requests = [];
        $customer_subscriptions = [];
        $subscription_plans = [];
        $subscription_invoices = [];
        $all_payments = [];
        $all_expenses = [];
        $salary_payments = [];
        $sales_targets = [];
        $sales_commissions = [];
        $staff_list = [];
        $driver_performance = [];
        $delivery_proofs = [];
        $salary_deductions = [];
        $salary_bonuses = [];
        $payslips = [];
    }
    

    // Sales - POS (Point of Sale)
    if ($page == 'sales' && $subpage == 'pos') {
        $branch_context = getBranchContextQuery();
        $products = $pdo->query("SELECT * FROM products WHERE {$branch_context} AND status = 'active' AND stock_quantity > 0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $customers = $pdo->query("SELECT * FROM customers WHERE {$branch_context} ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $company_name = COMPANY_NAME;
        ?>
        <div class="page-title">
            <h2><i class="bi bi-cart-check"></i> Point of Sale (POS)</h2>
            <div class="breadcrumb"><span class="breadcrumb-item active">Walk-in / Direct Store Sale</span></div>
        </div>

        <!-- Receipt Modal -->
        <div class="modal fade" id="receiptModal" tabindex="-1">
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-body p-0">
                <div id="receipt-content" style="font-family:monospace;font-size:13px;padding:20px;">
                  <div class="text-center mb-2">
                    <?php if (!empty($settings['company_logo'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="Logo" style="max-height: 50px; margin-bottom: 5px;"><br>
                    <?php endif; ?>
                    <strong id="rcp-company" style="font-size:15px;"></strong><br>
                    <small>🐪 Camel Farm Fresh Products</small><br>
                    <small id="rcp-date"></small>
                  </div>
                  <hr style="border-top:1px dashed #000;">
                  <div><strong>Customer:</strong> <span id="rcp-customer"></span></div>
                  <div><strong>Payment:</strong> <span id="rcp-payment"></span></div>
                  <hr style="border-top:1px dashed #000;">
                  <table style="width:100%;font-size:12px;" id="rcp-items-table">
                    <thead><tr><th class="text-start">Item</th><th>Qty</th><th class="text-end">Price</th></tr></thead>
                    <tbody id="rcp-items"></tbody>
                  </table>
                  <hr style="border-top:1px dashed #000;">
                  <div class="d-flex justify-content-between"><span>Subtotal:</span><strong id="rcp-subtotal"></strong></div>
                  <div class="d-flex justify-content-between"><span>Discount:</span><strong id="rcp-discount"></strong></div>
                  <div class="d-flex justify-content-between" style="font-size:15px;"><span><b>TOTAL:</b></span><strong id="rcp-total"></strong></div>
                  <div class="d-flex justify-content-between mt-2"><span>Cash Paid:</span><strong id="rcp-paid"></strong></div>
                  <div class="d-flex justify-content-between text-success"><span><b>Change:</b></span><strong id="rcp-change"></strong></div>
                  <hr style="border-top:1px dashed #000;">
                  <div class="text-center"><small>Thank you for your purchase!<br>Please come again 🙏</small></div>
                </div>
              </div>
              <div class="modal-footer d-flex justify-content-between">
                <button class="btn btn-primary btn-sm" onclick="printReceipt()"><i class="bi bi-printer"></i> Print</button>
                <button class="btn btn-success btn-sm" id="btn-confirm-receipt"><i class="bi bi-check2-circle"></i> Confirm</button>
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
            <!-- Cart -->
            <div class="col-lg-8">
                <div class="form-container">
                    <h4><i class="bi bi-cart3"></i> Shopping Cart</h4>
                    <div class="table-responsive">
                        <table class="table table-hover" id="cart-table">
                            <thead><tr><th>Product</th><th>Unit Price</th><th>Qty</th><th>Total</th><th>✕</th></tr></thead>
                            <tbody id="pos-cart-body">
                                <tr id="empty-row"><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-cart3" style="font-size:2em;"></i><br>Select a product below to add to cart</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-3 p-3 bg-light rounded">
                        <div class="col-md-4"><label class="form-label fw-bold">Discount ($)</label><input type="number" id="pos-discount" class="form-control" min="0" step="0.01" value="0" oninput="updateTotals()"></div>
                        <div class="col-md-4 d-flex align-items-end"><div class="w-100 p-3 bg-white rounded border text-center"><small class="text-muted d-block">Subtotal</small><strong id="pos-subtotal" class="fs-5">$0.00</strong></div></div>
                        <div class="col-md-4 d-flex align-items-end"><div class="w-100 p-3 bg-primary text-white rounded text-center"><small class="d-block" style="opacity:.8;">GRAND TOTAL</small><strong class="fs-4" id="pos-grand-total">$0.00</strong></div></div>
                    </div>
                </div>
            </div>

            <!-- Customer & Payment -->
            <div class="col-lg-4">
                <div class="form-container mb-3">
                    <h4><i class="bi bi-person-lines-fill"></i> Customer Info</h4>
                    <div class="mb-2">
                        <label class="form-label">Customer Type</label>
                        <select id="pos-customer-type" class="form-control" onchange="toggleCustomerType()">
                            <option value="walkin">Walk-in Customer</option>
                            <option value="registered">Registered Customer</option>
                        </select>
                    </div>
                    <div id="walkin-fields">
                        <div class="mb-2"><label class="form-label">Customer Name (optional)</label><input type="text" id="walkin-name" class="form-control" placeholder="Walk-in Customer"></div>
                        <div class="mb-2"><label class="form-label">Phone (optional)</label><input type="text" id="walkin-phone" class="form-control" placeholder="e.g. 061xxxxxxx"></div>
                    </div>
                    <div id="registered-fields" style="display:none;">
                        <label class="form-label">Select Customer</label>
                        <select id="pos-customer-id" class="form-control">
                            <option value="0">-- Select Customer --</option>
                            <?php foreach ($customers as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?> (<?php echo $c['phone']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-container mb-3">
                    <h4><i class="bi bi-cash-coin"></i> Payment</h4>
                    <div class="mb-2">
                        <label class="form-label">Payment Method</label>
                        <select id="pos-payment-method" class="form-control" onchange="toggleCash()">
                            <option value="cash">💵 Cash</option>
                            <option value="mobile_money">📱 Mobile Money</option>
                            <option value="card">💳 Card</option>
                        </select>
                    </div>
                    <div id="cash-fields">
                        <div class="mb-2"><label class="form-label">Amount Paid ($)</label><input type="number" id="pos-amount-paid" class="form-control" min="0" step="0.01" placeholder="0.00" oninput="calcChange()"></div>
                        <div class="p-2 bg-success text-white rounded text-center"><small>Change to Return</small><div class="fs-5 fw-bold" id="pos-change">$0.00</div></div>
                    </div>
                </div>

                <button type="button" id="btn-preview-sale" class="btn btn-warning btn-lg w-100 mb-2 shadow-sm">
                    <i class="bi bi-receipt"></i> Preview Receipt & Complete
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="form-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0"><i class="bi bi-grid"></i> Available Products</h4>
                        <input type="text" id="product-search" class="form-control w-auto" placeholder="🔍 Search product..." oninput="filterProducts()">
                    </div>
                    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-3" id="products-grid">
                        <?php foreach ($products as $p): ?>
                        <div class="col product-item" data-name="<?php echo strtolower($p['name']); ?>">
                            <div class="card h-100 border-0 shadow-sm pos-product-card"
                                 onclick="addToCart(<?php echo $p['id']; ?>, '<?php echo addslashes(htmlspecialchars($p['name'])); ?>', <?php echo $p['price']; ?>, <?php echo $p['stock_quantity']; ?>)"
                                 style="cursor:pointer;transition:all 0.2s;">
                                <img src="uploads/<?php echo htmlspecialchars($p['image'] ?: 'default-product.jpg'); ?>"
                                     class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/120x80?text=Product'"
                                     style="height:90px;object-fit:cover;">
                                <div class="card-body p-2 text-center">
                                    <div class="fw-bold" style="font-size:0.82em;"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div class="text-primary fw-bold">$<?php echo number_format($p['price'],2); ?></div>
                                    <small class="text-muted">Stock: <?php echo $p['stock_quantity']; ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                        <div class="col-12 text-center text-muted py-4">No active products with stock found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        let cart = [];
        const companyName = '<?php echo addslashes($company_name); ?>';

        function toggleCustomerType() {
            const t = document.getElementById('pos-customer-type').value;
            document.getElementById('walkin-fields').style.display = t === 'walkin' ? 'block' : 'none';
            document.getElementById('registered-fields').style.display = t === 'registered' ? 'block' : 'none';
        }
        function toggleCash() {
            const m = document.getElementById('pos-payment-method').value;
            document.getElementById('cash-fields').style.display = m === 'cash' ? 'block' : 'none';
            calcChange();
        }
        function filterProducts() {
            const q = document.getElementById('product-search').value.toLowerCase();
            document.querySelectorAll('.product-item').forEach(el => {
                el.style.display = el.dataset.name.includes(q) ? 'block' : 'none';
            });
        }
        function addToCart(id, name, price, stock) {
            const existing = cart.find(i => i.id === id);
            if (existing) {
                if (existing.qty < stock) existing.qty++;
                else { alert('Maximum stock reached!'); return; }
            } else {
                cart.push({id, name, price, qty:1, stock});
            }
            renderCart(); updateTotals();
        }
        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            renderCart(); updateTotals();
        }
        function updateQty(id, qty) {
            const item = cart.find(i => i.id === id);
            if (item) item.qty = Math.min(item.stock, Math.max(1, parseInt(qty)||1));
            updateTotals();
        }
        function renderCart() {
            const body = document.getElementById('pos-cart-body');
            if (cart.length === 0) {
                body.innerHTML = '<tr id="empty-row"><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-cart3" style="font-size:2em;"></i><br>Select a product below to add to cart</td></tr>';
                return;
            }
            body.innerHTML = cart.map(item => `
                <tr>
                    <td><strong>${item.name}</strong></td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td><input type="number" class="form-control form-control-sm" style="width:70px;" value="${item.qty}" min="1" max="${item.stock}" onchange="updateQty(${item.id},this.value)"></td>
                    <td><strong>$${(item.price*item.qty).toFixed(2)}</strong></td>
                    <td><button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})"><i class="bi bi-x-lg"></i></button></td>
                </tr>`).join('');
        }
        function getSubtotal() { return cart.reduce((s,i)=>s+i.price*i.qty,0); }
        function getDiscount() { return parseFloat(document.getElementById('pos-discount').value)||0; }
        function getTotal() { return Math.max(0, getSubtotal()-getDiscount()); }
        function updateTotals() {
            document.getElementById('pos-subtotal').textContent = '$'+getSubtotal().toFixed(2);
            document.getElementById('pos-grand-total').textContent = '$'+getTotal().toFixed(2);
            calcChange();
        }
        function calcChange() {
            const paid = parseFloat(document.getElementById('pos-amount-paid').value)||0;
            const change = Math.max(0, paid - getTotal());
            document.getElementById('pos-change').textContent = '$'+change.toFixed(2);
        }
        
        function printReceipt() {
            const rcpContent = document.getElementById('receipt-content').innerHTML;
            const printWindow = window.open('', '', 'height=600,width=350');
            printWindow.document.write('<html><head><title>Receipt</title>');
            printWindow.document.write('<style>body { font-family: monospace; font-size: 13px; margin: 0; padding: 10px; } hr { border-top: 1px dashed #000; border-bottom: none; } .text-center { text-align: center; } .d-flex { display: flex; justify-content: space-between; margin-bottom: 5px; } .text-end { text-align: right; } .text-start { text-align: left; } th { text-align: left; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(rcpContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 250);
        }

        document.getElementById('btn-preview-sale').addEventListener('click', function() {
            if (cart.length === 0) { alert('Cart is empty! Please add products first.'); return; }
            const total = getTotal();
            const discount = getDiscount();
            const method = document.getElementById('pos-payment-method').value;
            if (method === 'cash') {
                const paid = parseFloat(document.getElementById('pos-amount-paid').value)||0;
                if (paid < total && paid !== 0) { alert('Amount paid is less than the total! Please collect the correct amount.'); return; }
            }
            // Fill receipt
            const custType = document.getElementById('pos-customer-type').value;
            let custName = custType === 'walkin'
                ? (document.getElementById('walkin-name').value || 'Walk-in Customer')
                : (document.getElementById('pos-customer-id').selectedOptions[0].text);
            document.getElementById('rcp-company').textContent = companyName;
            document.getElementById('rcp-date').textContent = new Date().toLocaleString();
            document.getElementById('rcp-customer').textContent = custName;
            document.getElementById('rcp-payment').textContent = method.replace('_',' ').toUpperCase();
            document.getElementById('rcp-subtotal').textContent = '$'+getSubtotal().toFixed(2);
            document.getElementById('rcp-discount').textContent = '-$'+discount.toFixed(2);
            document.getElementById('rcp-total').textContent = '$'+total.toFixed(2);
            const paid = parseFloat(document.getElementById('pos-amount-paid').value)||total;
            document.getElementById('rcp-paid').textContent = '$'+paid.toFixed(2);
            document.getElementById('rcp-change').textContent = '$'+Math.max(0,paid-total).toFixed(2);
            document.getElementById('rcp-items').innerHTML = cart.map(i=>`<tr><td class="text-start">${i.name}</td><td>${i.qty}</td><td class="text-end">$${(i.price*i.qty).toFixed(2)}</td></tr>`).join('');
            
            const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
            modal.show();
        });
        
        document.getElementById('btn-confirm-receipt').addEventListener('click', function() {
            const custType = document.getElementById('pos-customer-type').value;
            const custName = custType === 'walkin' ? (document.getElementById('walkin-name').value||'Walk-in Customer') : '';
            const custPhone = custType === 'walkin' ? (document.getElementById('walkin-phone').value||'') : '';
            const custId = custType === 'registered' ? document.getElementById('pos-customer-id').value : '0';
            const data = {
                action:'pos_sale', 
                customer_id:custId, 
                walkin_name:custName,
                walkin_phone:custPhone, 
                payment_method:document.getElementById('pos-payment-method').value,
                subtotal:getSubtotal(), 
                discount:getDiscount(), 
                total_amount:getTotal(),
                amount_paid:document.getElementById('pos-amount-paid').value||getTotal(),
                items:cart
            };
            const form = document.createElement('form');
            form.method='POST'; form.action='index.php?page=sales&subpage=pos';
            const inp1=document.createElement('input'); inp1.type='hidden'; inp1.name='action'; inp1.value='pos_sale'; form.appendChild(inp1);
            const inp2=document.createElement('input'); inp2.type='hidden'; inp2.name='pos_data'; inp2.value=JSON.stringify(data); form.appendChild(inp2);
            document.body.appendChild(form); form.submit();
        });
        </script>
        <style>
        .pos-product-card:hover { transform:translateY(-4px); box-shadow:0 8px 25px rgba(0,0,0,0.15) !important; }
        </style>
        <?php
    }
    
    // Sales Overview
    elseif ($page == 'sales' && $subpage == 'overview') {
        $branch_context = getBranchContextQuery();
        $overview_total_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE {$branch_context} AND status = 'delivered'")->fetchColumn() ?: 0;
        $overview_total_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE {$branch_context}")->fetchColumn() ?: 0;
        $overview_pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE {$branch_context} AND status IN ('pending', 'confirmed', 'processing', 'packed', 'shipped')")->fetchColumn() ?: 0;
        $overview_cancelled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE {$branch_context} AND status IN ('cancelled', 'cancellation_requested')")->fetchColumn() ?: 0;
        $overview_avg_order = $pdo->query("SELECT AVG(total_amount) FROM orders WHERE {$branch_context} AND status = 'delivered'")->fetchColumn() ?: 0;

        $branch_context_o = getBranchContextQuery('o');
        $top_products = $pdo->query("SELECT p.name, SUM(oi.quantity) as total_qty FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id WHERE {$branch_context_o} AND o.status = 'delivered' GROUP BY oi.product_id ORDER BY total_qty DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        $recent_sales = $pdo->query("SELECT o.order_number,o.order_date,o.total_amount,o.status,c.name AS customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE {$branch_context_o} ORDER BY o.order_date DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Sales Overview</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Sales Overview</span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3"><div class="dashboard-card"><h3>$<?php echo number_format($overview_total_sales,2); ?></h3><p>Total Sales</p></div></div>
            <div class="col-md-3"><div class="dashboard-card"><h3><?php echo $overview_total_orders; ?></h3><p>Total Orders</p></div></div>
            <div class="col-md-3"><div class="dashboard-card"><h3><?php echo $overview_pending_orders; ?></h3><p>Pending Orders</p></div></div>
            <div class="col-md-3"><div class="dashboard-card"><h3><?php echo $overview_cancelled_orders; ?></h3><p>Cancelled Orders</p></div></div>
        </div>
        <div class="row mt-4"><div class="col-md-4"><div class="dashboard-card"><h3>$<?php echo number_format($overview_avg_order,2); ?></h3><p>Avg Delivered Order</p></div></div></div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="form-container">
                    <h4>Top Products</h4>
                    <table class="table">
                        <thead><tr><th>Product</th><th>Units Sold</th></tr></thead>
                        <tbody>
                            <?php foreach ($top_products as $tp): ?>
                                <tr><td><?php echo htmlspecialchars($tp['name']); ?></td><td><?php echo $tp['total_qty']; ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-container">
                    <h4>Recent Sales</h4>
                    <table class="table">
                        <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php foreach ($recent_sales as $sale): ?>
                                <tr>
                                    <td><?php echo $sale['order_number']; ?></td>
                                    <td><?php echo $sale['customer_name'] ?: 'Guest'; ?></td>
                                    <td>$<?php echo number_format($sale['total_amount'],2); ?></td>
                                    <td><span class="badge bg-<?php echo $sale['status'] == 'delivered' ? 'success' : 'info'; ?>"><?php echo ucfirst($sale['status']); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($sale['order_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    // Orders - List
    elseif ($page == 'orders' && $subpage == 'list') {
        ?>
        <div class="page-title">
            <h2>All Orders</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Orders</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr><th>Order #</th><th>Date</th><th>Customer</th><th>Amount</th><th>Status</th><th>Payment</th><th>Actions</th> </thead>
                <tbody>
                    <?php foreach ($all_orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?> <td><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?> <td><?php echo $order['customer_name']; ?> <td>$<?php echo number_format($order['total_amount'], 2); ?> <td><span class="badge bg-<?php echo $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'info'); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span> <td><span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['payment_status']); ?></span> <td><a href="?page=orders&subpage=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Orders - View
    elseif ($page == 'orders' && $subpage == 'view') {
        $order_id = $_GET['id'] ?? 0;
        $order_stmt = $pdo->prepare("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address,
                   d.status as delivery_status, d.delivery_date, d.driver_id, d.delivery_address, d.recipient_name,
                   u.full_name as driver_name, p.status as payment_status, p.amount as payment_amount
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            LEFT JOIN deliveries d ON o.id = d.order_id
            LEFT JOIN users u ON d.driver_id = u.id
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE o.id = ? AND " . getBranchContextQuery('o') . "
        ");
        $order_stmt->execute([$order_id]);
        $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo "<div class='alert alert-danger'>Order not found.</div>";
        } else {
            $order_items_stmt = $pdo->prepare("SELECT oi.*, p.name, p.unit FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $order_items_stmt->execute([$order_id]);
            $order_items = $order_items_stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="page-title">
                <h2>Order Details - <?php echo $order['order_number']; ?></h2>
                <div class="breadcrumb">
                    <span class="breadcrumb-item"><a href="?page=orders&subpage=list">Orders</a></span>
                    <span class="breadcrumb-item active"><?php echo $order['order_number']; ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="form-container">
                        <h4>Order Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order Number:</strong> <?php echo $order['order_number']; ?></p>
                                <p><strong>Order Date:</strong> <?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></p>
                                <p><strong>Status:</strong>
                                    <span class="badge bg-<?php echo $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'info'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'] ?? 'pending')); ?>
                                    </span>
                                </p>
                                <p><strong>Payment Status:</strong>
                                    <span class="badge bg-<?php echo ($order['payment_status'] ?? '') == 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($order['payment_status'] ?? 'unpaid'); ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> <?php echo $order['customer_name']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $order['customer_phone']; ?></p>
                                <p><strong>Delivery Address:</strong> <?php echo $order['delivery_address']; ?></p>
                                <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                            </div>
                        </div>

                        <h5 class="mt-4">Order Items</h5>
                        <table class="table">
                            <thead>
                                <tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo $item['name']; ?></td>
                                    <td><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-container">
                        <h4>Actions</h4>
                        <?php if ($order['status'] == 'pending'): ?>
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="action" value="accept_order">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" class="btn btn-success w-100">Accept Order</button>
                        </form>
                        <?php elseif ($order['status'] == 'confirmed' && (!$order['delivery_status'] || $order['delivery_status'] == 'assigned')): ?>
                        <form method="POST" class="mb-3" onsubmit="return confirm('Are you sure you want to assign this delivery? This will notify the driver.');">
                            <input type="hidden" name="action" value="assign_delivery">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Select Driver</label>
                                <select name="driver_id" class="form-control" required>
                                    <option value="">Choose Driver</option>
                                    <?php foreach ($drivers as $driver): ?>
                                    <option value="<?php echo $driver['id']; ?>"><?php echo $driver['full_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Assign Delivery</button>
                        </form>
                        <?php elseif ($order['status'] == 'shipped' || $order['delivery_status'] == 'in_transit'): ?>
                        <form method="POST" class="mb-3" onsubmit="return confirm('Are you sure you want to mark this order as delivered? This will complete the order and notify the customer.');">
                            <input type="hidden" name="action" value="mark_delivered">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" class="btn btn-success w-100">Mark as Delivered</button>
                        </form>
                        <?php endif; ?>

                        <?php if ($order['status'] == 'confirmed' || $order['status'] == 'processing' || $order['status'] == 'packed' || $order['status'] == 'shipped'): ?>
                        <button onclick="window.open('?page=orders&subpage=print_invoice&id=<?php echo $order['id']; ?>', '_blank')" class="btn btn-info w-100 mb-3">Print Invoice</button>
                        <?php endif; ?>

                        <a href="?page=orders&subpage=list" class="btn btn-secondary w-100">Back to Orders</a>
                    </div>

                    <?php if ($order['delivery_status']): ?>
                    <div class="form-container mt-3">
                        <h4>Delivery Information</h4>
                        <p><strong>Status:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['delivery_status'])); ?></p>
                        <p><strong>Driver:</strong> <?php echo $order['driver_name'] ?: 'Not assigned'; ?></p>
                        <p><strong>Delivery Date:</strong> <?php echo $order['delivery_date'] ? date('d M Y', strtotime($order['delivery_date'])) : 'Not scheduled'; ?></p>
                        <p><strong>Recipient:</strong> <?php echo $order['recipient_name']; ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    }

    // Orders - Invoices
    elseif ($page == 'orders' && $subpage == 'invoices') {
        $branch_context = getBranchContextQuery('i');
        
        $invoices_stmt = $pdo->prepare("
            SELECT i.*, o.order_number, c.name as customer_name 
            FROM invoices i 
            LEFT JOIN orders o ON i.order_id = o.id 
            LEFT JOIN customers c ON i.customer_id = c.id 
            WHERE {$branch_context} 
            ORDER BY i.invoice_date DESC
        ");
        $invoices_stmt->execute();
        $invoices_list = $invoices_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_invoiced = 0;
        $total_paid = 0;
        $total_unpaid = 0;
        foreach ($invoices_list as $inv) {
            $amt = floatval($inv['total_amount']);
            $total_invoiced += $amt;
            if ($inv['status'] == 'paid') {
                $total_paid += $amt;
            } else {
                $total_unpaid += $amt;
            }
        }
        ?>
        <div class="page-title d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Invoices</h2>
                <div class="breadcrumb">
                    <span class="breadcrumb-item"><a href="?page=orders&subpage=list">Orders</a></span>
                    <span class="breadcrumb-item active">Invoice Management</span>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="dashboard-card bg-light border-start border-primary border-4 p-3 rounded shadow-sm">
                    <small class="text-uppercase text-muted fw-bold">Total Invoiced</small>
                    <h3 class="text-primary mt-1">$<?php echo number_format($total_invoiced, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card bg-light border-start border-success border-4 p-3 rounded shadow-sm">
                    <small class="text-uppercase text-muted fw-bold">Total Collected</small>
                    <h3 class="text-success mt-1">$<?php echo number_format($total_paid, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card bg-light border-start border-warning border-4 p-3 rounded shadow-sm">
                    <small class="text-uppercase text-muted fw-bold">Outstanding</small>
                    <h3 class="text-warning mt-1">$<?php echo number_format($total_unpaid, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="form-container shadow-sm p-4 rounded bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices_list as $inv): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                <td>
                                    <?php if ($inv['order_id']): ?>
                                        <a href="?page=orders&subpage=view&id=<?php echo $inv['order_id']; ?>" class="text-decoration-none fw-bold text-primary">
                                            <?php echo htmlspecialchars($inv['order_number']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($inv['customer_name'] ?: 'Walk-in / Guest'); ?></td>
                                <td><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($inv['due_date'])); ?></td>
                                <td class="fw-bold">$<?php echo number_format($inv['total_amount'], 2); ?></td>
                                <td>
                                    <?php if ($inv['status'] == 'paid'): ?>
                                        <span class="badge bg-success px-2 py-1">Paid</span>
                                    <?php elseif ($inv['status'] == 'overdue' || (strtotime($inv['due_date']) < time() && $inv['status'] == 'unpaid')): ?>
                                        <span class="badge bg-danger px-2 py-1">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-2 py-1">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <?php if ($inv['order_id']): ?>
                                            <a href="?page=orders&subpage=print_invoice&id=<?php echo $inv['order_id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print/PDF">
                                                <i class="bi bi-printer"></i> Print
                                            </a>
                                            <a href="?page=orders&subpage=view&id=<?php echo $inv['order_id']; ?>" class="btn btn-sm btn-outline-primary ms-1" title="Details">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($invoices_list)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No invoice records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // Orders - Print Invoice
    elseif ($page == 'orders' && $subpage == 'print_invoice') {
        $order_id = $_GET['id'] ?? 0;
        $order_stmt = $pdo->prepare("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.id = ? AND " . getBranchContextQuery('o') . "
        ");
        $order_stmt->execute([$order_id]);
        $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo "<div class='alert alert-danger'>Order not found.</div>";
        } else {
            $order_items_stmt = $pdo->prepare("SELECT oi.*, p.name, p.unit FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $order_items_stmt->execute([$order_id]);
            $order_items = $order_items_stmt->fetchAll(PDO::FETCH_ASSOC);
            $invoice_stmt = $pdo->prepare("SELECT * FROM invoices WHERE order_id = ?");
            $invoice_stmt->execute([$order_id]);
            $invoice = $invoice_stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Invoice - <?php echo $order['order_number']; ?></title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-family: 'Segoe UI', sans-serif; background: white; }
                    .invoice-header { border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 20px; }
                    .company-info { margin-bottom: 30px; }
                    .invoice-details { margin-bottom: 30px; }
                    .invoice-items { margin-bottom: 30px; }
                    .invoice-summary { border-top: 2px solid #eee; padding-top: 20px; }
                    @media print { .no-print { display: none; } body { background: white; } }
                </style>
            </head>
            <body>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="invoice-header">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="company-info">
                                            <?php if (!empty($settings['company_logo'])): ?>
                                                <img src="<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="Logo" style="max-height: 80px; margin-bottom: 15px; display: block;">
                                            <?php endif; ?>
                                            <h2><?php echo htmlspecialchars($settings['company_name'] ?? COMPANY_NAME); ?></h2>
                                            <p><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></p>
                                            <p>Phone: <?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?></p>
                                            <p>Email: <?php echo htmlspecialchars($settings['company_email'] ?? ''); ?></p>
                                        </div>
                                        <div class="company-about">
                                            <h5>About Us</h5>
                                            <p><?php echo htmlspecialchars($settings['company_description'] ?? 'We are committed to providing the finest camel milk products with exceptional quality and service. Our farm produces premium camel milk using traditional methods combined with modern technology to ensure freshness and nutritional value.'); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <h3>INVOICE</h3>
                                        <p><strong>Invoice #:</strong> <?php echo $invoice['invoice_number'] ?? 'N/A'; ?></p>
                                        <p><strong>Order #:</strong> <?php echo $order['order_number']; ?></p>
                                        <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($order['order_date'])); ?></p>
                                        <p><strong>Due Date:</strong> <?php echo $invoice ? date('d M Y', strtotime($invoice['due_date'])) : 'N/A'; ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="invoice-details">
                                <h5>Bill To:</h5>
                                <p><strong><?php echo $order['customer_name']; ?></strong></p>
                                <p><?php echo $order['customer_address']; ?></p>
                                <p>Phone: <?php echo $order['customer_phone']; ?></p>
                            </div>

                            <div class="invoice-items">
                                <h5>Order Items</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['name']; ?></td>
                                            <td><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="invoice-summary">
                                <div class="row">
                                    <div class="col-8"></div>
                                    <div class="col-4">
                                        <table class="table">
                                            <?php if ($invoice): ?>
                                            <tr>
                                                <td>Subtotal:</td>
                                                <td class="text-end">$<?php echo number_format($invoice['subtotal'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Tax:</td>
                                                <td class="text-end">$<?php echo number_format($invoice['tax'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Delivery Fee:</td>
                                                <td class="text-end">$<?php echo number_format($invoice['delivery_fee'], 2); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total Amount:</th>
                                                <th class="text-end">$<?php echo number_format($invoice['total_amount'], 2); ?></th>
                                            </tr>
                                            <?php else: ?>
                                            <tr>
                                                <th>Total Amount:</th>
                                                <th class="text-end">$<?php echo number_format($order['total_amount'], 2); ?></th>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-5 no-print">
                                <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
                                <button onclick="window.close()" class="btn btn-secondary">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
        }
        exit;
    }

    // Orders - Create (Sales)
    elseif ($page == 'orders' && $subpage == 'create') {
        ?>
        <div class="page-title">
            <h2>Create Order</h2>
            <div class="breadcrumb"><span class="breadcrumb-item active">Sell Products</span></div>
        </div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="create_order">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers ?? [] as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?> (<?php echo htmlspecialchars($c['phone']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Delivery Address</label>
                        <input type="text" name="delivery_address" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Delivery Phone</label>
                        <input type="text" name="delivery_phone" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered">
                        <thead><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead>
                        <tbody id="order-rows">
                            <tr>
                                <td>
                                    <select name="product_id[]" class="form-control product-select" required>
                                        <option value="">Select product</option>
                                        <?php foreach ($products ?? [] as $p): ?>
                                            <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>"><?php echo htmlspecialchars($p['name']); ?> (<?php echo number_format($p['price'],2); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" name="quantity[]" class="form-control quantity-input" min="1" value="1" required></td>
                                <td><input type="text" class="form-control unit-price" readonly></td>
                                <td><input type="text" class="form-control line-total" readonly></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-row">Add Item</button>
                    <div class="fw-bold">Grand Total: $<span id="grand-total">0.00</span></div>
                </div>
                <input type="hidden" name="grand_total" id="grand_total_input" value="0">
                <button type="submit" class="btn btn-success">Submit Order</button>
            </form>
        </div>
        <script>
            function recalcRow(row) {
                const product = row.querySelector('.product-select');
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                const price = parseFloat(product.selectedOptions[0].dataset.price || 0);
                row.querySelector('.unit-price').value = price.toFixed(2);
                row.querySelector('.line-total').value = (price * quantity).toFixed(2);
                recalcTotal();
            }

            function recalcTotal() {
                let total = 0;
                document.querySelectorAll('.line-total').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById('grand-total').textContent = total.toFixed(2);
                document.getElementById('grand_total_input').value = total.toFixed(2);
            }

            document.addEventListener('change', event => {
                if (event.target.closest('.product-select') || event.target.closest('.quantity-input')) {
                    const row = event.target.closest('tr');
                    recalcRow(row);
                }
            });

            document.getElementById('add-row').addEventListener('click', () => {
                const tableBody = document.getElementById('order-rows');
                const newRow = tableBody.insertRow();
                newRow.innerHTML = `
                    <td>
                        <select name="product_id[]" class="form-control product-select" required>
                            <option value="">Select product</option>
                            <?php foreach ($products ?? [] as $p): ?>
                                <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>"><?php echo addslashes(htmlspecialchars($p['name'])); ?> (<?php echo number_format($p['price'],2); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="quantity[]" class="form-control quantity-input" min="1" value="1" required></td>
                    <td><input type="text" class="form-control unit-price" readonly></td>
                    <td><input type="text" class="form-control line-total" readonly></td>
                `;
            });

            // initial fill
            recalcRow(document.querySelector('#order-rows tr'));
        </script>
        <?php
    }
    
    // Orders - Pending
    elseif ($page == 'orders' && $subpage == 'pending') {
        $pending_orders = array_filter($all_orders, function($o) { return $o['status'] == 'pending'; });
        ?>
        <div class="page-title">
            <h2>Pending Orders</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Pending Approval</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead> <tr><th>Order #</th><th>Date</th><th>Customer</th><th>Amount</th><th>Actions</th></tr> </thead>
                <tbody>
                    <?php foreach ($pending_orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this order?')">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pending_orders)): ?>
                    <tr><td colspan="5" class="text-center">No pending orders.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Orders - Processing
    elseif ($page == 'orders' && $subpage == 'processing') {
        $processing_orders = array_filter($all_orders, function($o) { return in_array($o['status'], ['confirmed', 'processing', 'packed', 'shipped']); });
        ?>
        <div class="page-title">
            <h2>Processing Orders</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Orders in Progress</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead> <tr><th>Order #</th><th>Date</th><th>Customer</th><th>Amount</th><th>Status</th><th>Actions</th></tr> </thead>
                <tbody>
                    <?php foreach ($processing_orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></td>
                        <td><?php echo $order['customer_name']; ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="form-control form-control-sm d-inline-block" style="width: auto;">
                                    <option value="processing">Process</option>
                                    <option value="packed">Pack</option>
                                    <option value="shipped">Ship</option>
                                    <option value="delivered">Deliver</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Orders - Cancellations
    elseif ($page == 'orders' && $subpage == 'cancellations') {
        ?>
        <div class="page-title">
            <h2>Cancellation Requests</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Manage Cancellations</span>
            </div>
        </div>
        <div class="sales-dashboard">
            <?php if (empty($cancellation_requests)): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> No cancellation requests pending.</div>
            <?php else: ?>
            <?php foreach ($cancellation_requests as $request): ?>
            <div class="cancellation-card">
                <div class="row">
                    <div class="col-md-2"><strong>Order #: <?php echo $request['order_number']; ?></strong><br><small>Date: <?php echo date('d M Y', strtotime($request['order_date'])); ?></small></div>
                    <div class="col-md-3"><strong>Customer:</strong><br><?php echo $request['customer_name']; ?><br><small><?php echo $request['customer_phone']; ?></small></div>
                    <div class="col-md-4"><strong>Cancellation Reason:</strong><div class="reason"><?php echo $request['cancellation_reason'] ?: 'No reason provided'; ?></div></div>
                    <div class="col-md-3"><strong>Order Total:</strong><br>$<?php echo number_format($request['total_amount'], 2); ?></div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <form method="POST" class="d-inline-block me-2" onsubmit="return confirm('Are you sure you want to approve this cancellation?');">
                            <input type="hidden" name="action" value="approve_cancellation">
                            <input type="hidden" name="order_id" value="<?php echo $request['id']; ?>">
                            <button type="submit" class="btn btn-danger">Approve Cancellation</button>
                        </form>
                        <form method="POST" class="d-inline-block" onsubmit="return confirm('Reject this cancellation request?');">
                            <input type="hidden" name="action" value="reject_cancellation">
                            <input type="hidden" name="order_id" value="<?php echo $request['id']; ?>">
                            <button type="submit" class="btn btn-warning">Reject Cancellation</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    // Delivery - Zones (hide from drivers)
    elseif ($page == 'delivery' && $subpage == 'zones') {
        if ($current_role == 'driver') {
            $_SESSION['message'] = "You don't have permission to view this page.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=dashboard');
            exit;
        }
        ?>
        <div class="page-title">
            <h2>Delivery Zones</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Manage Zones</span>
            </div>
        </div>
        <div class="form-container mb-4">
            <h4>Add New Delivery Zone</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_delivery_zone">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Zone Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">City</label><input type="text" name="city" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Delivery Fee ($)</label><input type="number" name="delivery_fee" class="form-control" step="0.01" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Estimated Time</label><input type="text" name="estimated_time" class="form-control" placeholder="e.g., 30-45 min" required></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Zone</button>
            </form>
        </div>
        <div class="table-container">
            <h4>Delivery Zones</h4>
            <table class="table"><thead><tr><th>Zone Name</th><th>City</th><th>Delivery Fee</th><th>Estimated Time</th></tr></thead><tbody><?php foreach ($delivery_zones ?? [] as $zone): ?><tr><td><?php echo $zone['name']; ?></td><td><?php echo $zone['city']; ?></td><td>$<?php echo number_format($zone['delivery_fee'], 2); ?></td><td><?php echo $zone['estimated_time']; ?></td></tr><?php endforeach; ?></tbody></table>
        </div>
        <?php
    }
    
    // Delivery - Assign (hide from drivers)
    elseif ($page == 'delivery' && $subpage == 'assign') {
        if ($current_role == 'driver') {
            $_SESSION['message'] = "You don't have permission to view this page.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=dashboard');
            exit;
        }
        $branch_context = getBranchContextQuery('o');
        $pending_orders_for_delivery = $pdo->query("SELECT o.*, c.name as customer_name, c.phone, c.address FROM orders o JOIN customers c ON o.customer_id = c.id WHERE {$branch_context} AND o.status IN ('packed', 'shipped') AND NOT EXISTS (SELECT 1 FROM deliveries d WHERE d.order_id = o.id) ORDER BY o.order_date")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Assign Deliveries</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">New Assignments</span>
            </div>
        </div>
        <div class="table-container">
            <h4>Orders Ready for Delivery</h4>
            <table class="table"><thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Address</th><th>Amount</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($pending_orders_for_delivery as $order): ?>
            <tr>
                <td><?php echo $order['order_number']; ?></td>
                <td><?php echo $order['customer_name']; ?></td>
                <td><?php echo $order['phone']; ?></td>
                <td><?php echo $order['address']; ?></td>
                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                    <form method="POST" class="row g-2" onsubmit="return confirm('Are you sure you want to assign this delivery? This will notify the driver.');">
                        <input type="hidden" name="action" value="assign_delivery">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="col-md-5"><select name="driver_id" class="form-control form-control-sm" required><option value="">Select Driver</option><?php foreach ($drivers ?? [] as $driver): ?><option value="<?php echo $driver['id']; ?>"><?php echo $driver['full_name']; ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><input type="date" name="delivery_date" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required></div>
                        <div class="col-md-3"><button type="submit" class="btn btn-sm btn-primary">Assign</button></div>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
        <?php
    }
    
    // Delivery - Tracking
    elseif ($page == 'delivery' && $subpage == 'tracking') {
        $branch_context = ($current_role == 'driver') ? '1=1' : getBranchContextQuery('d');
        
        // For drivers, show only their assigned deliveries
        if ($current_role == 'driver') {
            $active_deliveries = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.phone, u.full_name as driver_name FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id JOIN users u ON d.driver_id = u.id WHERE {$branch_context} AND d.driver_id = {$_SESSION['user_id']} AND d.status IN ('assigned', 'in_transit') ORDER BY d.delivery_date")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $active_deliveries = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.phone, u.full_name as driver_name FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id JOIN users u ON d.driver_id = u.id WHERE {$branch_context} AND d.status IN ('assigned', 'in_transit') ORDER BY d.delivery_date")->fetchAll(PDO::FETCH_ASSOC);
        }
        ?>
        <div class="page-title">
            <h2>Track Deliveries</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active"><?php echo $current_role == 'driver' ? 'My Deliveries' : 'Active Deliveries'; ?></span>
            </div>
        </div>
        <div class="row">
            <?php foreach ($active_deliveries as $delivery): ?>
            <div class="col-md-6">
                <div class="delivery-card">
                    <div class="row">
                        <div class="col-md-8">
                            <strong>Order #<?php echo $delivery['order_number']; ?></strong><br>
                            <small>Customer: <?php echo $delivery['customer_name']; ?> (<?php echo $delivery['customer_phone'] ?? 'N/A'; ?>)</small><br>
                            <?php if ($current_role != 'driver'): ?>
                            <small>Driver: <?php echo $delivery['driver_name']; ?></small><br>
                            <?php endif; ?>
                            <small>Address: <?php echo $delivery['delivery_address']; ?></small><br>
                            <small>Due: <?php echo date('d M Y', strtotime($delivery['delivery_date'])); ?></small>
                        </div>
                        <div class="col-md-4">
                            <span class="badge bg-<?php echo $delivery['status'] == 'in_transit' ? 'info' : 'warning'; ?>"><?php echo ucfirst($delivery['status']); ?></span>
                            
                            <?php if ($current_role == 'driver'): ?>
                                <!-- Driver-specific actions -->
                                <?php if ($delivery['status'] == 'assigned'): ?>
                                <div class="mt-2">
                                    <form method="POST" class="mb-2">
                                        <input type="hidden" name="action" value="accept_delivery_assignment">
                                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Accept this delivery assignment?')">Accept Assignment</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal<?php echo $delivery['id']; ?>">Cancel Assignment</button>
                                    
                                    <!-- Cancel Modal -->
                                    <div class="modal fade" id="cancelModal<?php echo $delivery['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Cancel Assignment</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="action" value="cancel_delivery_assignment">
                                                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Reason for cancellation</label>
                                                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-danger w-100">Confirm Cancellation</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php elseif ($delivery['status'] == 'in_transit'): ?>
                                <div class="mt-2">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="mark_delivery_completed">
                                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-primary w-100" onclick="return confirm('Mark this delivery as completed?')">Mark as Completed</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Admin/Manager view -->
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="action" value="update_delivery_status">
                                    <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                                    <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                        <option value="assigned" <?php echo $delivery['status'] == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                        <option value="in_transit" <?php echo $delivery['status'] == 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($active_deliveries)): ?>
            <div class="col-12"><div class="alert alert-success">No active deliveries.</div></div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    // Delivery - View Order Details
    elseif ($page == 'delivery' && $subpage == 'view') {
        $delivery_id = $_GET['id'] ?? null;
        if (!$delivery_id) {
            $_SESSION['message'] = "Invalid delivery ID.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=delivery&subpage=tracking');
            exit;
        }

        $branch_context = ($current_role == 'driver') ? '1=1' : getBranchContextQuery('d');
        
        $stmt = $pdo->prepare("
            SELECT d.*, o.order_number, o.order_date, o.total_amount, 
                   c.name as customer_name, c.phone as customer_phone, c.address as customer_address
            FROM deliveries d 
            JOIN orders o ON d.order_id = o.id 
            JOIN customers c ON o.customer_id = c.id 
            WHERE {$branch_context} AND d.id = ? 
            " . ($current_role == 'driver' ? " AND d.driver_id = " . intval($_SESSION['user_id']) : "") . "
        ");
        $stmt->execute([$delivery_id]);
        $delivery = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$delivery) {
            $_SESSION['message'] = "Delivery not found or access denied.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=delivery&subpage=tracking');
            exit;
        }
        
        // fetch items
        $items_stmt = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $items_stmt->execute([$delivery['order_id']]);
        $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Order Details #<?php echo $delivery['order_number']; ?></h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item"><a href="?page=delivery&subpage=tracking">Deliveries</a></span>
                <span class="breadcrumb-item active">Order #<?php echo $delivery['order_number']; ?></span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="form-container mb-4">
                    <h4><i class="bi bi-box-seam"></i> Order Items</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-container mb-4">
                    <h4><i class="bi bi-person"></i> Customer Info</h4>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($delivery['customer_name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($delivery['customer_phone']); ?></p>
                    <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($delivery['delivery_address']); ?></p>
                </div>
                
                <div class="form-container">
                    <h4><i class="bi bi-truck"></i> Delivery Status</h4>
                    <p>
                        Current Status: 
                        <span class="badge bg-<?php echo match($delivery['status']) { 'assigned'=>'primary', 'in_transit'=>'info', 'delivered'=>'success', 'cancelled'=>'danger', default=>'secondary'}; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $delivery['status'])); ?>
                        </span>
                    </p>
                    
                    <?php if ($current_role == 'driver' && $delivery['status'] == 'assigned'): ?>
                    <hr>
                    <p>Please accept or cancel this delivery assignment:</p>
                    <form method="POST" action="index.php?page=delivery&subpage=tracking" class="mb-2">
                        <input type="hidden" name="action" value="accept_delivery_assignment">
                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Accept this delivery assignment?')"><i class="bi bi-check-circle"></i> Accept Assignment</button>
                    </form>
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal"><i class="bi bi-x-circle"></i> Cancel Assignment</button>
                    
                    <!-- Cancel Modal -->
                    <div class="modal fade" id="cancelModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Cancel Assignment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="index.php?page=delivery&subpage=tracking">
                                        <input type="hidden" name="action" value="cancel_delivery_assignment">
                                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Reason for cancellation</label>
                                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-danger w-100">Confirm Cancellation</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($current_role == 'driver' && $delivery['status'] == 'in_transit'): ?>
                    <hr>
                    <form method="POST" action="index.php?page=delivery&subpage=tracking">
                        <input type="hidden" name="action" value="mark_delivery_completed">
                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                        <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Mark this delivery as completed?')"><i class="bi bi-check2-all"></i> Mark as Completed</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Delivery - Completed
    elseif ($page == 'delivery' && $subpage == 'completed') {
        if ($current_role == 'driver') {
            $_SESSION['message'] = "You don't have permission to view this page.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=dashboard');
            exit;
        }
        $branch_context = getBranchContextQuery('d');
        
        $completed_deliveries = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name, c.phone, u.full_name as driver_name FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id JOIN users u ON d.driver_id = u.id WHERE {$branch_context} AND d.status = 'delivered' ORDER BY d.delivered_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Completed Deliveries</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Completed Orders</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Driver</th>
                        <th>Delivery Address</th>
                        <th>Status</th>
                        <th>Completion Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completed_deliveries as $delivery): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($delivery['order_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($delivery['customer_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($delivery['phone'] ?? 'N/A'); ?></small></td>
                        <td><span class="badge bg-primary bg-opacity-75"><i class="bi bi-person"></i> <?php echo htmlspecialchars($delivery['driver_name']); ?></span></td>
                        <td><?php echo htmlspecialchars($delivery['delivery_address']); ?></td>
                        <td><span class="badge bg-success"><i class="bi bi-check-circle"></i> Completed</span></td>
                        <td><?php echo $delivery['delivered_at'] ? date('d M Y h:i A', strtotime($delivery['delivered_at'])) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($completed_deliveries)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-inbox fs-2"></i><br>No completed deliveries found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Delivery - Proof
    elseif ($page == 'delivery' && $subpage == 'proof') {
        $branch_context = getBranchContextQuery('d');
        
        // For drivers, show only their deliveries
        if ($current_role == 'driver') {
            $delivered_without_proof = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE {$branch_context} AND d.driver_id = {$_SESSION['user_id']} AND d.status = 'delivered' AND d.proof_of_delivery IS NULL ORDER BY d.delivery_date DESC")->fetchAll(PDO::FETCH_ASSOC);
            $delivery_proofs = $pdo->query("SELECT dp.*, d.order_id, o.order_number FROM delivery_proofs dp JOIN deliveries d ON dp.delivery_id = d.id JOIN orders o ON d.order_id = o.id WHERE {$branch_context} AND d.driver_id = {$_SESSION['user_id']} ORDER BY dp.uploaded_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $delivered_without_proof = $pdo->query("SELECT d.*, o.order_number, c.name as customer_name FROM deliveries d JOIN orders o ON d.order_id = o.id JOIN customers c ON o.customer_id = c.id WHERE {$branch_context} AND d.status = 'delivered' AND d.proof_of_delivery IS NULL ORDER BY d.delivery_date DESC")->fetchAll(PDO::FETCH_ASSOC);
            $delivery_proofs = $pdo->query("SELECT dp.*, d.order_id, o.order_number FROM delivery_proofs dp JOIN deliveries d ON dp.delivery_id = d.id JOIN orders o ON d.order_id = o.id WHERE {$branch_context} ORDER BY dp.uploaded_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        }
        ?>
        <div class="page-title">
            <h2>Proof of Delivery</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active"><?php echo $current_role == 'driver' ? 'My Deliveries' : 'Upload Proof'; ?></span>
            </div>
        </div>
        <div class="table-container">
            <h4>Deliveries Missing Proof</h4>
            <table class="table"><thead><tr><th>Order #</th><th>Customer</th><th>Delivery Date</th><th>Address</th><th>Proof</th></tr></thead><tbody>
            <?php foreach ($delivered_without_proof as $delivery): ?>
            <tr>
                <td><?php echo $delivery['order_number']; ?></td>
                <td><?php echo $delivery['customer_name']; ?></td>
                <td><?php echo date('d M Y', strtotime($delivery['delivery_date'])); ?></td>
                <td><?php echo $delivery['delivery_address']; ?></td>
                <td>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_proof">
                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                        <input type="file" name="proof_image" accept="image/*" required>
                        <button type="submit" class="btn btn-sm btn-primary mt-1">Upload</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody></table>
            
            <h4 class="mt-4">Recent Proofs</h4>
            <table class="table"><thead><tr><th>Order #</th><th>Image</th><th>Uploaded</th></tr></thead><tbody>
            <?php foreach ($delivery_proofs ?? [] as $proof): ?>
            <tr><td><?php echo $proof['order_number']; ?></td><td><a href="assets/proofs/<?php echo $proof['image_path']; ?>" target="_blank">View Proof</a></td><td><?php echo date('d M Y H:i', strtotime($proof['uploaded_at'])); ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
        <?php
    }
    
    // Delivery - Drivers (hide from drivers)
    elseif ($page == 'delivery' && $subpage == 'drivers') {
        if ($current_role == 'driver') {
            $_SESSION['message'] = "You don't have permission to view this page.";
            $_SESSION['message_type'] = "danger";
            header('Location: ?page=dashboard');
            exit;
        }
        ?>
        <div class="page-title">
            <h2>Drivers</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Manage Drivers</span>
            </div>
        </div>
        <div class="table-container">
            <h4>Driver List</h4>
            <table class="table"><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Today's Deliveries</th><th>Performance</th></tr></thead><tbody>
            <?php foreach ($drivers ?? [] as $driver):
                $branch_context = getBranchContextQuery();
                $today_deliveries = $pdo->prepare("SELECT COUNT(*) FROM deliveries WHERE driver_id = ? AND delivery_date = CURDATE() AND {$branch_context}");
                $today_deliveries->execute([$driver['id']]);
                $today_count = $today_deliveries->fetchColumn();
                $performance = $pdo->prepare("SELECT AVG(rating) FROM driver_performance WHERE driver_id = ? AND {$branch_context}");
                $performance->execute([$driver['id']]);
                $avg_rating = $performance->fetchColumn() ?: 'N/A';
            ?>
            <tr>
                <td><?php echo $driver['full_name']; ?></td>
                <td><?php echo $driver['phone']; ?></td>
                <td><?php echo $driver['email']; ?></td>
                <td><?php echo $today_count; ?></td>
                <td><?php if ($avg_rating != 'N/A'): ?><div class="progress" style="height: 15px; width: 100px;"><div class="progress-bar bg-success" style="width: <?php echo $avg_rating * 20; ?>%"><?php echo number_format($avg_rating, 1); ?>/5</div></div><?php else: ?>No ratings<?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
        <div class="table-container mt-4">
            <h4>Driver Performance</h4>
            <table class="table"><thead><tr><th>Driver</th><th>Date</th><th>Deliveries</th><th>On Time</th><th>Rating</th></tr></thead><tbody>
            <?php foreach ($driver_performance ?? [] as $perf): ?>
            <tr><td><?php echo $perf['driver_name']; ?></td><td><?php echo date('d M Y', strtotime($perf['delivery_date'])); ?></td><td><?php echo $perf['deliveries_count']; ?></td><td><?php echo $perf['on_time_deliveries']; ?></td><td><?php echo number_format($perf['rating'], 1); ?>/5</td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
        <?php
    }
    
    // Subscriptions - Plans
    elseif ($page == 'subscriptions' && $subpage == 'plans') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'add_subscription_plan') {
                $name = trim($_POST['name']);
                $plan_type = $_POST['plan_type'];
                $quantity_per_day = (float)$_POST['quantity_per_day'];
                $price = (float)$_POST['price'];
                $status = $_POST['status'];
                $branch_id = $_SESSION['branch_id'] ?? null;
                
                $stmt = $pdo->prepare("INSERT INTO subscription_plans (branch_id, name, plan_type, quantity_per_day, price, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$branch_id, $name, $plan_type, $quantity_per_day, $price, $status]);
                
                $_SESSION['message'] = "Subscription Plan added successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ?page=subscriptions&subpage=plans");
                exit;
            } elseif ($_POST['action'] === 'edit_subscription_plan') {
                $id = (int)$_POST['plan_id'];
                $name = trim($_POST['name']);
                $plan_type = $_POST['plan_type'];
                $quantity_per_day = (float)$_POST['quantity_per_day'];
                $price = (float)$_POST['price'];
                $status = $_POST['status'];
                
                $stmt = $pdo->prepare("UPDATE subscription_plans SET name=?, plan_type=?, quantity_per_day=?, price=?, status=? WHERE id=?");
                $stmt->execute([$name, $plan_type, $quantity_per_day, $price, $status, $id]);
                
                $_SESSION['message'] = "Subscription Plan updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ?page=subscriptions&subpage=plans");
                exit;
            } elseif ($_POST['action'] === 'delete_subscription_plan') {
                $id = (int)$_POST['plan_id'];
                $stmt = $pdo->prepare("DELETE FROM subscription_plans WHERE id=?");
                $stmt->execute([$id]);
                
                $_SESSION['message'] = "Subscription Plan deleted successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ?page=subscriptions&subpage=plans");
                exit;
            }
        }
        $branch_context = getBranchContextQuery();
        $subscription_plans = $pdo->query("SELECT * FROM subscription_plans WHERE {$branch_context}")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Subscription Plans</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Manage Plans</span>
            </div>
        </div>
        <div class="form-container mb-4">
            <h4>Add New Plan</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_subscription_plan">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Type</label>
                        <select name="plan_type" class="form-control" required>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Quantity/Day (Liters)</label>
                        <input type="number" name="quantity_per_day" class="form-control" step="0.1" value="1.0" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" name="price" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Plan</button>
            </form>
        </div>
        <div class="table-container">
            <h4>Current Plans</h4>
            <table class="table">
                <thead><tr><th>Name</th><th>Type</th><th>Quantity/Day</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($subscription_plans ?? [] as $plan): ?>
                <tr>
                    <td><?php echo htmlspecialchars($plan['name']); ?></td>
                    <td><?php echo ucfirst($plan['plan_type']); ?></td>
                    <td><?php echo $plan['quantity_per_day']; ?> L</td>
                    <td>$<?php echo number_format($plan['price'], 2); ?></td>
                    <td><span class="badge bg-<?php echo $plan['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($plan['status']); ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPlanModal<?php echo $plan['id']; ?>"><i class="bi bi-pencil"></i> Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                            <input type="hidden" name="action" value="delete_subscription_plan">
                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
                <!-- Edit Modal -->
                <div class="modal fade" id="editPlanModal<?php echo $plan['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST">
                            <input type="hidden" name="action" value="edit_subscription_plan">
                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Plan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>Plan Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($plan['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Type</label>
                                    <select name="plan_type" class="form-control" required>
                                        <option value="daily" <?php echo $plan['plan_type'] == 'daily' ? 'selected' : ''; ?>>Daily</option>
                                        <option value="weekly" <?php echo $plan['plan_type'] == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                        <option value="monthly" <?php echo $plan['plan_type'] == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Quantity/Day (L)</label>
                                    <input type="number" name="quantity_per_day" class="form-control" step="0.1" value="<?php echo $plan['quantity_per_day']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Price ($)</label>
                                    <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $plan['price']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="active" <?php echo $plan['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $plan['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Subscriptions - Active
    elseif ($page == 'subscriptions' && $subpage == 'active') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'add_customer_subscription') {
                $customer_id = (int)$_POST['customer_id'];
                $plan_id = (int)$_POST['plan_id'];
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $delivery_time = $_POST['delivery_time'];
                $status = $_POST['status'];
                
                $stmt = $pdo->prepare("INSERT INTO customer_subscriptions (customer_id, plan_id, start_date, end_date, delivery_time, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$customer_id, $plan_id, $start_date, $end_date, $delivery_time, $status]);
                
                $_SESSION['message'] = "Customer Subscription added successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ?page=subscriptions&subpage=active");
                exit;
            } elseif ($_POST['action'] === 'delete_customer_subscription') {
                $id = (int)$_POST['subscription_id'];
                $stmt = $pdo->prepare("DELETE FROM customer_subscriptions WHERE id=?");
                $stmt->execute([$id]);
                
                $_SESSION['message'] = "Customer Subscription deleted successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: ?page=subscriptions&subpage=active");
                exit;
            }
        }
        $where_cs = getBranchContextQuery('cs');
        $customer_subscriptions = $pdo->query("SELECT cs.*, c.name as customer_name, COALESCE(pl.name, 'No Plan') as plan_name FROM customer_subscriptions cs LEFT JOIN customers c ON cs.customer_id = c.id LEFT JOIN subscription_plans pl ON cs.plan_id = pl.id WHERE $where_cs ORDER BY cs.start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        $subscription_plans = $pdo->query("SELECT * FROM subscription_plans WHERE " . getBranchContextQuery() . " AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        $customers = $pdo->query("SELECT * FROM customers WHERE " . getBranchContextQuery() . " ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Active Subscriptions</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Current Subscriptions</span>
            </div>
        </div>
        
        <div class="form-container mb-4">
            <h4>Assign New Subscription</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_customer_subscription">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Plan</label>
                        <select name="plan_id" class="form-control" required>
                            <option value="">Select Plan</option>
                            <?php foreach ($subscription_plans as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Delivery Time</label>
                        <input type="time" name="delivery_time" class="form-control" value="07:00" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Subscription</button>
            </form>
        </div>

        <div class="table-container">
            <table class="table"><thead><tr><th>Customer</th><th>Plan</th><th>Start Date</th><th>End Date</th><th>Delivery Time</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($customer_subscriptions ?? [] as $sub): ?>
            <tr>
                <td><?php echo htmlspecialchars($sub['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($sub['plan_name']); ?></td>
                <td><?php echo date('d M Y', strtotime($sub['start_date'])); ?></td>
                <td><?php echo date('d M Y', strtotime($sub['end_date'])); ?></td>
                <td><?php echo $sub['delivery_time']; ?></td>
                <td><span class="badge bg-<?php echo $sub['status'] == 'active' ? 'success' : ($sub['status'] == 'paused' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($sub['status']); ?></span></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="generate_subscription_invoice">
                        <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-warning"><i class="bi bi-receipt"></i> Invoice</button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this subscription?');">
                        <input type="hidden" name="action" value="delete_customer_subscription">
                        <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
        <div class="table-container mt-4">
            <h4>Subscription Invoices</h4>
            <table class="table"><thead><tr><th>Invoice #</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($subscription_invoices ?? [] as $inv): ?>
            <tr><td><?php echo $inv['invoice_number']; ?></td><td><?php echo htmlspecialchars($inv['customer_name']); ?></td><td><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></td><td>$<?php echo number_format($inv['amount'], 2); ?></td><td><span class="badge bg-<?php echo $inv['status'] == 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($inv['status']); ?></span></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
        <?php
    }

    // Subscriptions - Auto Billing
    elseif ($page == 'subscriptions' && $subpage == 'billing') {
        // Calculate totals
        $total_outstanding = 0;
        $total_collected = 0;
        foreach ($subscription_invoices ?? [] as $inv) {
            if ($inv['status'] == 'paid') {
                $total_collected += $inv['amount'];
            } else {
                $total_outstanding += $inv['amount'];
            }
        }
        $active_subs_count = count(array_filter($customer_subscriptions ?? [], function($s) { return $s['status'] == 'active'; }));
        ?>
        <div class="page-title">
            <h2>Subscription Auto-Billing Engine</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item"><a href="?page=subscriptions&subpage=active">Subscriptions</a></span>
                <span class="breadcrumb-item active">Auto Billing</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-gradient-primary text-white p-4" style="border-radius: 15px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase mb-1" style="font-size: 0.75rem;">Active Subscriptions</h6>
                            <h3 class="mb-0 text-white"><?php echo $active_subs_count; ?></h3>
                        </div>
                        <div class="fs-1"><i class="bi bi-people-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-gradient-success text-white p-4" style="border-radius: 15px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase mb-1" style="font-size: 0.75rem;">Total Revenue Collected</h6>
                            <h3 class="mb-0 text-white">$<?php echo number_format($total_collected, 2); ?></h3>
                        </div>
                        <div class="fs-1"><i class="bi bi-cash-coin"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-gradient-warning text-white p-4" style="border-radius: 15px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase mb-1" style="font-size: 0.75rem;">Outstanding Invoices</h6>
                            <h3 class="mb-0 text-white">$<?php echo number_format($total_outstanding, 2); ?></h3>
                        </div>
                        <div class="fs-1"><i class="bi bi-clock-history"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-container mb-4 text-center p-5 bg-light" style="border-radius: 15px; border: 2px dashed #dee2e6;">
            <div class="mx-auto mb-3" style="max-width: 600px;">
                <span class="fs-1">⚡</span>
                <h4 class="mt-3">Auto Billing Engine</h4>
                <p class="text-muted">Click the button below to process monthly billing. The engine will scan all active subscriptions and generate new invoices for any subscription that has not been billed in the past 28 days.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="run_auto_billing">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="bi bi-lightning-charge-fill"></i> Run Auto Billing Now</button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <h4>Subscription Invoice List</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subscription_invoices)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No invoices generated yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($subscription_invoices as $inv): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($inv['invoice_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($inv['customer_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($inv['invoice_date'])); ?></td>
                                    <td><strong>$<?php echo number_format($inv['amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $inv['status'] == 'paid' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($inv['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($inv['status'] !== 'paid'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="pay_subscription_invoice">
                                                <input type="hidden" name="invoice_id" value="<?php echo $inv['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i> Mark as Paid</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-success"><i class="bi bi-check-all"></i> Paid on <?php echo $inv['payment_date'] ? date('d M Y', strtotime($inv['payment_date'])) : 'N/A'; ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // Subscriptions - Pause/Resume
    elseif ($page == 'subscriptions' && $subpage == 'pause') {
        ?>
        <div class="page-title">
            <h2>Pause & Resume Subscriptions</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item"><a href="?page=subscriptions&subpage=active">Subscriptions</a></span>
                <span class="breadcrumb-item active">Pause / Resume</span>
            </div>
        </div>
        <div class="table-container">
            <h4>Manage Subscription Status</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Plan</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customer_subscriptions)): ?>
                            <tr><td colspan="6" class="text-center text-muted">No subscriptions found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($customer_subscriptions as $sub): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($sub['customer_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($sub['plan_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($sub['start_date'])); ?></td>
                                    <td><?php echo date('d M Y', strtotime($sub['end_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $sub['status'] == 'active' ? 'success' : ($sub['status'] == 'paused' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($sub['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($sub['status'] == 'active'): ?>
                                            <form method="POST" style="display:inline-block;" class="me-1">
                                                <input type="hidden" name="action" value="update_subscription_status">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <input type="hidden" name="status" value="paused">
                                                <input type="hidden" name="redirect_to" value="?page=subscriptions&subpage=pause">
                                                <button type="submit" class="btn btn-sm btn-warning"><i class="bi bi-pause-fill"></i> Pause</button>
                                            </form>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="update_subscription_status">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <input type="hidden" name="redirect_to" value="?page=subscriptions&subpage=pause">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this subscription?')"><i class="bi bi-x-circle-fill"></i> Cancel</button>
                                            </form>
                                        <?php elseif ($sub['status'] == 'paused' || $sub['status'] == 'cancelled'): ?>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="update_subscription_status">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <input type="hidden" name="status" value="active">
                                                <input type="hidden" name="redirect_to" value="?page=subscriptions&subpage=pause">
                                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-play-fill"></i> Resume / Reactivate</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    // Finance - Payments
    elseif ($page == 'finance' && $subpage == 'payments') {
        ?>
        <div class="page-title">
            <h2>Payments</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">All Transactions</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table"><thead><tr><th>Date</th><th>Order #</th><th>Customer</th><th>Amount</th><th>Method</th><th>Transaction ID</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($all_payments as $payment): ?>
            <tr>
                <td><?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></td>
                <td><?php echo $payment['order_number']; ?></td>
                <td><?php echo $payment['customer_name']; ?></td>
                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                <td><small><?php echo $payment['transaction_id']; ?></small></td>
                <td><span class="badge bg-<?php echo $payment['status'] == 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($payment['status']); ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
        <?php
    }
    
    // Finance - Expenses
    elseif ($page == 'finance' && $subpage == 'expenses') {
        $total_expenses = array_sum(array_column($all_expenses, 'amount'));
        ?>
        <div class="page-title">
            <h2>Expenses</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Track Expenses</span>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="icon"><i class="bi bi-cash"></i></div>
                    <h3>$<?php echo number_format($total_expenses, 2); ?></h3>
                    <p>Total Expenses</p>
                </div>
            </div>
        </div>
        <div class="form-container mb-4">
            <h4>Add Expense</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_expense">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Category</label><select name="category" class="form-control" required><option value="Feed">Feed</option><option value="Veterinary">Veterinary</option><option value="Salary">Salary</option><option value="Equipment">Equipment</option><option value="Transport">Transport</option><option value="Utilities">Utilities</option><option value="Other">Other</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Amount</label><input type="number" name="amount" class="form-control" step="0.01" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Payment Method</label><select name="payment_method" class="form-control" required><option value="cash">Cash</option><option value="bank">Bank Transfer</option><option value="mobile">Mobile Money</option></select></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" required></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Expense</button>
            </form>
        </div>
        <div class="table-container">
            <h4>Expense History</h4>
            <table class="table"><thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Payment Method</th> </thead><tbody>
            <?php foreach ($all_expenses as $expense): ?>
            <tr><td><?php echo date('d M Y', strtotime($expense['expense_date'])); ?> <td><?php echo $expense['category']; ?> <td><?php echo $expense['description']; ?> <td>$<?php echo number_format($expense['amount'], 2); ?> <td><?php echo ucfirst($expense['payment_method']); ?> 
            <?php endforeach; ?>
            </tbody> </table>
        </div>
        <?php
    }
    
    // Finance - Reports
    elseif ($page == 'finance' && $subpage == 'reports') {
        $selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
        
        $branch_context = getBranchContextQuery();
        
        $fin_view = $_GET['view'] ?? 'statement';
        $fin_interval = $_GET['interval'] ?? 'monthly';
        
        if ($fin_view == 'granular') {
            // Run granular queries
            if ($fin_interval == 'daily') {
                $rev_q = "SELECT DATE(dt) as period, SUM(amount) as total FROM (
                    SELECT DATE(order_date) as dt, total_amount as amount FROM orders WHERE {$branch_context} AND status = 'delivered'
                    UNION ALL
                    SELECT DATE(sale_date) as dt, total_amount as amount FROM pos_walkin_sales WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE(payment_date) as dt, amount as amount FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND payment_date IS NOT NULL
                ) t GROUP BY period ORDER BY period DESC LIMIT 30";
                
                $exp_q = "SELECT DATE(dt) as period, SUM(amount) as total FROM (
                    SELECT DATE(expense_date) as dt, amount as amount FROM expenses WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE(sp.payment_date) as dt, amount as amount FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE(purchase_date) as dt, total_amount as amount FROM feed_purchases WHERE {$branch_context}
                ) t GROUP BY period ORDER BY period DESC LIMIT 30";
            } elseif ($fin_interval == 'weekly') {
                $rev_q = "SELECT period, SUM(amount) as total FROM (
                    SELECT CONCAT(YEAR(order_date), '-W', WEEK(order_date, 1)) as period, total_amount as amount FROM orders WHERE {$branch_context} AND status = 'delivered'
                    UNION ALL
                    SELECT CONCAT(YEAR(sale_date), '-W', WEEK(sale_date, 1)) as period, total_amount as amount FROM pos_walkin_sales WHERE {$branch_context}
                    UNION ALL
                    SELECT CONCAT(YEAR(payment_date), '-W', WEEK(payment_date, 1)) as period, amount as amount FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND payment_date IS NOT NULL
                ) t GROUP BY period ORDER BY period DESC LIMIT 20";
                
                $exp_q = "SELECT period, SUM(amount) as total FROM (
                    SELECT CONCAT(YEAR(expense_date), '-W', WEEK(expense_date, 1)) as period, amount as amount FROM expenses WHERE {$branch_context}
                    UNION ALL
                    SELECT CONCAT(YEAR(sp.payment_date), '-W', WEEK(sp.payment_date, 1)) as period, amount as amount FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context}
                    UNION ALL
                    SELECT CONCAT(YEAR(purchase_date), '-W', WEEK(purchase_date, 1)) as period, total_amount as amount FROM feed_purchases WHERE {$branch_context}
                ) t GROUP BY period ORDER BY period DESC LIMIT 20";
            } elseif ($fin_interval == 'monthly') {
                $rev_q = "SELECT period, SUM(amount) as total FROM (
                    SELECT DATE_FORMAT(order_date, '%Y-%m') as period, total_amount as amount FROM orders WHERE {$branch_context} AND status = 'delivered'
                    UNION ALL
                    SELECT DATE_FORMAT(sale_date, '%Y-%m') as period, total_amount as amount FROM pos_walkin_sales WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE_FORMAT(payment_date, '%Y-%m') as period, amount as amount FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND payment_date IS NOT NULL
                ) t GROUP BY period ORDER BY period DESC";
                
                $exp_q = "SELECT period, SUM(amount) as total FROM (
                    SELECT DATE_FORMAT(expense_date, '%Y-%m') as period, amount as amount FROM expenses WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE_FORMAT(sp.payment_date, '%Y-%m') as period, amount as amount FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE_FORMAT(purchase_date, '%Y-%m') as period, total_amount as amount FROM feed_purchases WHERE {$branch_context}
                ) t GROUP BY period ORDER BY period DESC";
            } else { // yearly
                $rev_q = "SELECT period, SUM(amount) as total FROM (
                    SELECT DATE_FORMAT(order_date, '%Y') as period, total_amount as amount FROM orders WHERE {$branch_context} AND status = 'delivered'
                    UNION ALL
                    SELECT DATE_FORMAT(sale_date, '%Y') as period, total_amount as amount FROM pos_walkin_sales WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE_FORMAT(payment_date, '%Y') as period, amount as amount FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND payment_date IS NOT NULL
                ) t GROUP BY period ORDER BY period DESC";
                
                $exp_q = "SELECT period, SUM(amount) as total FROM (
                    SELECT DATE_FORMAT(expense_date, '%Y') as period, amount as amount FROM expenses WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE_FORMAT(sp.payment_date, '%Y') as period, amount as amount FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context}
                    UNION ALL
                    SELECT DATE_FORMAT(purchase_date, '%Y') as period, total_amount as amount FROM feed_purchases WHERE {$branch_context}
                ) t GROUP BY period ORDER BY period DESC";
            }
            
            $rev_data = $pdo->query($rev_q)->fetchAll(PDO::FETCH_ASSOC);
            $exp_data = $pdo->query($exp_q)->fetchAll(PDO::FETCH_ASSOC);
            
            $merged_fin = [];
            foreach ($rev_data as $r) {
                $merged_fin[$r['period']] = [
                    'period' => $r['period'],
                    'revenue' => floatval($r['total']),
                    'expense' => 0.0
                ];
            }
            foreach ($exp_data as $e) {
                if (!isset($merged_fin[$e['period']])) {
                    $merged_fin[$e['period']] = [
                        'period' => $e['period'],
                        'revenue' => 0.0,
                        'expense' => 0.0
                    ];
                }
                $merged_fin[$e['period']]['expense'] = floatval($e['total']);
            }
            krsort($merged_fin);
            ?>
            <div class="page-title d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Financial Reports</h2>
                    <div class="breadcrumb"><span class="breadcrumb-item active">Granular P&L Statement</span></div>
                </div>
                <button onclick="window.print()" class="btn btn-outline-primary d-print-none"><i class="bi bi-printer"></i> Print Statement</button>
            </div>
            
            <div class="d-flex gap-2 mb-4 d-print-none">
                <a href="?page=finance&subpage=reports&view=statement" class="btn btn-outline-primary">Monthly Statement View</a>
                <a href="?page=finance&subpage=reports&view=granular&interval=monthly" class="btn btn-primary">Granular P&L Statement</a>
            </div>
            
            <!-- Interval Selector -->
            <div class="form-container mb-4 d-print-none">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="page" value="finance">
                    <input type="hidden" name="subpage" value="reports">
                    <input type="hidden" name="view" value="granular">
                    
                    <div class="col-md-4">
                        <label class="form-label">Period Interval</label>
                        <select name="interval" class="form-select" onchange="this.form.submit()">
                            <option value="daily" <?php echo $fin_interval == 'daily' ? 'selected' : ''; ?>>Daily P&L</option>
                            <option value="weekly" <?php echo $fin_interval == 'weekly' ? 'selected' : ''; ?>>Weekly P&L</option>
                            <option value="monthly" <?php echo $fin_interval == 'monthly' ? 'selected' : ''; ?>>Monthly P&L</option>
                            <option value="yearly" <?php echo $fin_interval == 'yearly' ? 'selected' : ''; ?>>Yearly P&L</option>
                        </select>
                    </div>
                </form>
            </div>
            
            <div class="card border-0 shadow-sm p-4 print-container">
                <div class="text-center mb-4">
                    <?php if (!empty($settings['company_logo'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="Logo" style="max-height: 80px; margin-bottom: 15px;">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars(COMPANY_NAME); ?></h3>
                    <h5 class="text-muted">Granular Profit & Loss (P&L) Statement</h5>
                    <h6>Interval: <?php echo ucfirst($fin_interval); ?></h6>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Period</th>
                                <th class="text-end">Revenue (Inflow)</th>
                                <th class="text-end">Expenses (Outflow)</th>
                                <th class="text-end">Net Profit / Loss</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($merged_fin)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No financial records found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($merged_fin as $period => $row): ?>
                                    <?php 
                                    $net = $row['revenue'] - $row['expense'];
                                    $status_class = $net >= 0 ? 'success' : 'danger';
                                    $status_text = $net >= 0 ? 'Profit' : 'Loss';
                                    ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <?php 
                                            if ($fin_interval == 'daily') {
                                                echo date('d M Y', strtotime($period));
                                            } else {
                                                echo htmlspecialchars($period);
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end text-success fw-bold">$<?php echo number_format($row['revenue'], 2); ?></td>
                                        <td class="text-end text-danger fw-bold">$<?php echo number_format($row['expense'], 2); ?></td>
                                        <td class="text-end fw-bold text-<?php echo $status_class; ?>">$<?php echo number_format($net, 2); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
        } else {
            // Original Monthly Performance Statement
            // 1. Sales
            $sales_stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE {$branch_context} AND status = 'delivered' AND YEAR(order_date) = ? AND MONTH(order_date) = ?");
            $sales_stmt->execute([$selected_year, $selected_month]);
            $sales_total = floatval($sales_stmt->fetchColumn() ?: 0);
            
            // 2. Walk-in POS Sales
            $walkin_stmt = $pdo->prepare("SELECT SUM(total_amount) FROM pos_walkin_sales WHERE YEAR(sale_date) = ? AND MONTH(sale_date) = ?");
            $walkin_stmt->execute([$selected_year, $selected_month]);
            $walkin_total = floatval($walkin_stmt->fetchColumn() ?: 0);
            
            // 3. Subscriptions
            $sub_stmt = $pdo->prepare("SELECT SUM(amount) FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND YEAR(si.invoice_date) = ? AND MONTH(si.invoice_date) = ?");
            $sub_stmt->execute([$selected_year, $selected_month]);
            $sub_total = floatval($sub_stmt->fetchColumn() ?: 0);
            
            $total_revenue = $sales_total + $walkin_total + $sub_total;
            
            // 4. Expenses
            $exp_stmt = $pdo->prepare("SELECT SUM(amount) FROM expenses WHERE {$branch_context} AND YEAR(expense_date) = ? AND MONTH(expense_date) = ?");
            $exp_stmt->execute([$selected_year, $selected_month]);
            $expenses_total = floatval($exp_stmt->fetchColumn() ?: 0);
            
            // 5. Salaries
            $salary_stmt = $pdo->prepare("SELECT SUM(amount) FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context} AND YEAR(sp.payment_date) = ? AND MONTH(sp.payment_date) = ?");
            $salary_stmt->execute([$selected_year, $selected_month]);
            $salary_total = floatval($salary_stmt->fetchColumn() ?: 0);
            
            $total_outflow = $expenses_total + $salary_total;
            $net_income = $total_revenue - $total_outflow;
            
            // Expense breakdown
            $exp_cat_stmt = $pdo->prepare("SELECT category, SUM(amount) as total FROM expenses WHERE {$branch_context} AND YEAR(expense_date) = ? AND MONTH(expense_date) = ? GROUP BY category ORDER BY total DESC");
            $exp_cat_stmt->execute([$selected_year, $selected_month]);
            $expenses_by_category = $exp_cat_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Payment methods from online payments
            $pay_method_stmt = $pdo->prepare("SELECT payment_method, SUM(amount) as total FROM payments WHERE {$branch_context} AND status = 'completed' AND YEAR(payment_date) = ? AND MONTH(payment_date) = ? GROUP BY payment_method");
            $pay_method_stmt->execute([$selected_year, $selected_month]);
            $online_pm = $pay_method_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Payment methods from walk-in sales
            $walkin_pm_stmt = $pdo->prepare("SELECT payment_method, SUM(total_amount) as total FROM pos_walkin_sales WHERE YEAR(sale_date) = ? AND MONTH(sale_date) = ? GROUP BY payment_method");
            $walkin_pm_stmt->execute([$selected_year, $selected_month]);
            $walkin_pm = $walkin_pm_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Merge payment methods
            $pm_totals = [];
            foreach ($online_pm as $pm) {
                $method = $pm['payment_method'] ?: 'unknown';
                $pm_totals[$method] = ($pm_totals[$method] ?? 0) + floatval($pm['total']);
            }
            foreach ($walkin_pm as $pm) {
                $method = $pm['payment_method'] ?: 'unknown';
                $pm_totals[$method] = ($pm_totals[$method] ?? 0) + floatval($pm['total']);
            }
            ?>
            <div class="page-title d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Financial Reports</h2>
                    <div class="breadcrumb"><span class="breadcrumb-item active">Performance Statement</span></div>
                </div>
                <button onclick="window.print()" class="btn btn-outline-primary d-print-none"><i class="bi bi-printer"></i> Print Statement</button>
            </div>
            
            <div class="d-flex gap-2 mb-4 d-print-none">
                <a href="?page=finance&subpage=reports&view=statement" class="btn btn-primary">Monthly Statement View</a>
                <a href="?page=finance&subpage=reports&view=granular&interval=monthly" class="btn btn-outline-primary">Granular P&L Statement</a>
            </div>
            
            <!-- Filter Controls -->
            <div class="form-container mb-4 d-print-none">
                <form method="GET" class="row align-items-end">
                    <input type="hidden" name="page" value="finance">
                    <input type="hidden" name="subpage" value="reports">
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select" onchange="this.form.submit()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $m == $selected_month ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" onchange="this.form.submit()">
                            <?php for ($y = date('Y') - 5; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $selected_year ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <a href="?page=finance&subpage=reports" class="btn btn-secondary w-100">Reset filters</a>
                    </div>
                </form>
            </div>
            
            <!-- Report Statement Card -->
            <div class="card border-0 shadow-sm p-4 print-container">
                <div class="text-center mb-4">
                    <?php if (!empty($settings['company_logo'])): ?>
                        <img src="<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="Logo" style="max-height: 80px; margin-bottom: 15px;">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars(COMPANY_NAME); ?></h3>
                    <h5 class="text-muted">Monthly Financial Report</h5>
                    <h6>For the Period: <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?></h6>
                </div>
                
                <!-- Overall Cards -->
                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-light">
                            <small class="text-uppercase text-muted fw-bold">Total Inflow (Revenue)</small>
                            <h3 class="text-success mt-2">$<?php echo number_format($total_revenue, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-light">
                            <small class="text-uppercase text-muted fw-bold">Total Outflow (Expenses)</small>
                            <h3 class="text-danger mt-2">$<?php echo number_format($total_outflow, 2); ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-light">
                            <small class="text-uppercase text-muted fw-bold">Net Earnings (Profit)</small>
                            <h3 class="<?php echo $net_income >= 0 ? 'text-success' : 'text-danger'; ?> mt-2">
                                $<?php echo number_format($net_income, 2); ?>
                            </h3>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <!-- Inflow Breakdown -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-light h-100">
                            <div class="card-header bg-success text-white fw-bold">Inflow (Revenue) Breakdown</div>
                            <div class="card-body">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr><th>Source</th><th class="text-end">Amount</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Online Orders & Deliveries</td>
                                            <td class="text-end text-success fw-bold">$<?php echo number_format($sales_total, 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Walk-in Retail POS Sales</td>
                                            <td class="text-end text-success fw-bold">$<?php echo number_format($walkin_total, 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Paid Subscriptions</td>
                                            <td class="text-end text-success fw-bold">$<?php echo number_format($sub_total, 2); ?></td>
                                        </tr>
                                        <tr class="table-light">
                                            <td class="fw-bold">Total Inflow</td>
                                            <td class="text-end text-success fw-bold">$<?php echo number_format($total_revenue, 2); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <!-- Payment Method Mix -->
                                <h6 class="mt-4 fw-bold">Sales Mix by Payment Method</h6>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($pm_totals as $method => $total): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><?php echo ucfirst(str_replace('_', ' ', $method)); ?></span>
                                            <span class="badge bg-secondary">$<?php echo number_format($total, 2); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (empty($pm_totals)): ?>
                                        <li class="list-group-item text-muted text-center px-0">No payment records found.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Outflow Breakdown -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-light h-100">
                            <div class="card-header bg-danger text-white fw-bold">Outflow (Expense) Breakdown</div>
                            <div class="card-body">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr><th>Category</th><th class="text-end">Amount</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Staff Salaries</td>
                                            <td class="text-end text-danger fw-bold">$<?php echo number_format($salary_total, 2); ?></td>
                                        </tr>
                                        <?php foreach ($expenses_by_category as $ec): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ec['category']); ?></td>
                                                <td class="text-end text-danger fw-bold">$<?php echo number_format($ec['total'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-light">
                                            <td class="fw-bold">Total Outflow</td>
                                            <td class="text-end text-danger fw-bold">$<?php echo number_format($total_outflow, 2); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end mt-4 pt-4 border-top">
                    <p class="mb-0 text-muted">Generated by Antigravity Financial Engine at <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
            </div>
            
            <style>
            @media print {
                body * {
                    visibility: hidden;
                }
                .print-container, .print-container * {
                    visibility: visible;
                }
                .print-container {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    border: none !important;
                    box-shadow: none !important;
                }
                .d-print-none {
                    display: none !important;
                }
            }
            </style>
            <?php
            }
    }
    elseif ($page == 'finance' && $subpage == 'profit-loss') {
        $branch_context = getBranchContextQuery();
        // TODAY P&L
        $t_online = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE {$branch_context} AND status = 'delivered' AND DATE(order_date) = CURDATE()")->fetchColumn() ?: 0;
        $t_pos = $pdo->query("SELECT SUM(total_amount) FROM pos_walkin_sales WHERE {$branch_context} AND DATE(sale_date) = CURDATE()")->fetchColumn() ?: 0;
        $t_subs = $pdo->query("SELECT SUM(amount) FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND DATE(si.payment_date) = CURDATE()")->fetchColumn() ?: 0;
        $today_rev = $t_online + $t_pos + $t_subs;
        
        $t_exp = $pdo->query("SELECT SUM(amount) FROM expenses WHERE {$branch_context} AND DATE(expense_date) = CURDATE()")->fetchColumn() ?: 0;
        $t_sal = $pdo->query("SELECT SUM(amount) FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context} AND DATE(sp.payment_date) = CURDATE()")->fetchColumn() ?: 0;
        $t_feed = $pdo->query("SELECT SUM(total_amount) FROM feed_purchases WHERE {$branch_context} AND DATE(purchase_date) = CURDATE()")->fetchColumn() ?: 0;
        $today_exp = $t_exp + $t_sal + $t_feed;
        $today_profit = $today_rev - $today_exp;
        
        // WEEKLY P&L
        $w_online = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE {$branch_context} AND status = 'delivered' AND YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn() ?: 0;
        $w_pos = $pdo->query("SELECT SUM(total_amount) FROM pos_walkin_sales WHERE {$branch_context} AND YEARWEEK(sale_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn() ?: 0;
        $w_subs = $pdo->query("SELECT SUM(amount) FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND YEARWEEK(si.payment_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn() ?: 0;
        $week_rev = $w_online + $w_pos + $w_subs;
        
        $w_exp = $pdo->query("SELECT SUM(amount) FROM expenses WHERE {$branch_context} AND YEARWEEK(expense_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn() ?: 0;
        $w_sal = $pdo->query("SELECT SUM(amount) FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context} AND YEARWEEK(sp.payment_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn() ?: 0;
        $w_feed = $pdo->query("SELECT SUM(total_amount) FROM feed_purchases WHERE {$branch_context} AND YEARWEEK(purchase_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn() ?: 0;
        $week_exp = $w_exp + $w_sal + $w_feed;
        $week_profit = $week_rev - $week_exp;
        
        // MONTHLY P&L
        $m_online = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE {$branch_context} AND status = 'delivered' AND MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $m_pos = $pdo->query("SELECT SUM(total_amount) FROM pos_walkin_sales WHERE {$branch_context} AND MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $m_subs = $pdo->query("SELECT SUM(amount) FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND MONTH(si.payment_date) = MONTH(CURDATE()) AND YEAR(si.payment_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $month_rev = $m_online + $m_pos + $m_subs;
        
        $m_exp = $pdo->query("SELECT SUM(amount) FROM expenses WHERE {$branch_context} AND MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $m_sal = $pdo->query("SELECT SUM(amount) FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context} AND MONTH(sp.payment_date) = MONTH(CURDATE()) AND YEAR(sp.payment_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $m_feed = $pdo->query("SELECT SUM(total_amount) FROM feed_purchases WHERE {$branch_context} AND MONTH(purchase_date) = MONTH(CURDATE()) AND YEAR(purchase_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $month_exp = $m_exp + $m_sal + $m_feed;
        $month_profit = $month_rev - $month_exp;
        
        // YEARLY P&L
        $y_online = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE {$branch_context} AND status = 'delivered' AND YEAR(order_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $y_pos = $pdo->query("SELECT SUM(total_amount) FROM pos_walkin_sales WHERE {$branch_context} AND YEAR(sale_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $y_subs = $pdo->query("SELECT SUM(amount) FROM subscription_invoices si JOIN customer_subscriptions cs ON si.subscription_id = cs.id WHERE {$branch_context} AND si.status = 'paid' AND YEAR(si.payment_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $year_rev = $y_online + $y_pos + $y_subs;
        
        $y_exp = $pdo->query("SELECT SUM(amount) FROM expenses WHERE {$branch_context} AND YEAR(expense_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $y_sal = $pdo->query("SELECT SUM(amount) FROM salary_payments sp JOIN staff s ON sp.staff_id = s.id WHERE {$branch_context} AND YEAR(sp.payment_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $y_feed = $pdo->query("SELECT SUM(total_amount) FROM feed_purchases WHERE {$branch_context} AND YEAR(purchase_date) = YEAR(CURDATE())")->fetchColumn() ?: 0;
        $year_exp = $y_exp + $y_sal + $y_feed;
        $year_profit = $year_rev - $year_exp;
        ?>
        <div class="page-title mb-4">
            <h2>Profit & Loss (P&L) Dashboard</h2>
            <div class="breadcrumb"><span class="breadcrumb-item active">Operational Performance</span></div>
        </div>
        
        <div class="row g-4 mb-4">
            <!-- Today's P&L -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white fw-bold">Today's P&L</div>
                    <div class="card-body">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Inflow:</span>
                            <span class="text-success fw-bold">$<?php echo number_format($today_rev, 2); ?></span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="text-muted">Outflow:</span>
                            <span class="text-danger fw-bold">$<?php echo number_format($today_exp, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Net:</span>
                            <span class="fs-5 fw-bold text-<?php echo $today_profit >= 0 ? 'success' : 'danger'; ?>">
                                $<?php echo number_format($today_profit, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Weekly P&L -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white fw-bold">This Week's P&L</div>
                    <div class="card-body">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Inflow:</span>
                            <span class="text-success fw-bold">$<?php echo number_format($week_rev, 2); ?></span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="text-muted">Outflow:</span>
                            <span class="text-danger fw-bold">$<?php echo number_format($week_exp, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Net:</span>
                            <span class="fs-5 fw-bold text-<?php echo $week_profit >= 0 ? 'success' : 'danger'; ?>">
                                $<?php echo number_format($week_profit, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Monthly P&L -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-warning text-dark fw-bold">This Month's P&L</div>
                    <div class="card-body">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Inflow:</span>
                            <span class="text-success fw-bold">$<?php echo number_format($month_rev, 2); ?></span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="text-muted">Outflow:</span>
                            <span class="text-danger fw-bold">$<?php echo number_format($month_exp, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Net:</span>
                            <span class="fs-5 fw-bold text-<?php echo $month_profit >= 0 ? 'success' : 'danger'; ?>">
                                $<?php echo number_format($month_profit, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Yearly P&L -->
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-info text-white fw-bold">This Year's P&L</div>
                    <div class="card-body">
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Inflow:</span>
                            <span class="text-success fw-bold">$<?php echo number_format($year_rev, 2); ?></span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between">
                            <span class="text-muted">Outflow:</span>
                            <span class="text-danger fw-bold">$<?php echo number_format($year_exp, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Net:</span>
                            <span class="fs-5 fw-bold text-<?php echo $year_profit >= 0 ? 'success' : 'danger'; ?>">
                                $<?php echo number_format($year_profit, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions and Redirects -->
        <div class="form-container">
            <h4>P&L Analytics Options</h4>
            <p class="text-muted">For a granular list and breakdown of inflow and outflow items across dates, check out the Financial Reports section.</p>
            <a href="?page=finance&subpage=reports&view=granular" class="btn btn-primary">Go to Granular P&L Statement</a>
        </div>
        <?php
    }
    
    // Sales - Targets
    elseif ($page == 'sales' && $subpage == 'targets') {
        ?>
        <div class="page-title"><h2>Sales Targets</h2><div class="breadcrumb"><span class="breadcrumb-item active">Manage Sales Targets</span></div></div>
        <div class="form-container mb-4">
            <h4>Add New Sales Target</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_sales_target">
                <div class="row">
                    <div class="col-md-3 mb-3"><label class="form-label">Year</label><input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Month</label><select name="month" class="form-control" required><?php for($m=1;$m<=12;$m++): ?><option value="<?php echo $m; ?>"><?php echo date('F', mktime(0,0,0,$m,1)); ?></option><?php endfor; ?></select></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Target Amount ($)</label><input type="number" name="target_amount" class="form-control" step="0.01" required></div>
                    <div class="col-md-3 mb-3"><label class="form-label">Staff (Optional)</label><select name="staff_id" class="form-control"><option value="">-- Select Staff --</option><?php foreach ($staff_list ?? [] as $staff): ?><option value="<?php echo $staff['id']; ?>"><?php echo $staff['position']; ?></option><?php endforeach; ?></select></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Target</button>
            </form>
        </div>
        <div class="table-container">
            <h4>Existing Sales Targets</h4>
            <table class="table"><thead>  <th>Year</th><th>Month</th><th>Target Amount</th><th>Achieved Amount</th><th>Progress</th><th>Staff</th> </thead><tbody>
            <?php foreach ($sales_targets ?? [] as $target): ?>
             <tr><td><?php echo $target['year']; ?> <td><?php echo date('F', mktime(0,0,0,$target['month'],1)); ?> <td>$<?php echo number_format($target['target_amount'], 2); ?> <td>$<?php echo number_format($target['achieved_amount'], 2); ?> <td><?php $progress = $target['target_amount'] > 0 ? ($target['achieved_amount'] / $target['target_amount']) * 100 : 0; ?><div class="progress"><div class="progress-bar bg-<?php echo $progress >= 100 ? 'success' : ($progress >= 75 ? 'info' : 'warning'); ?>" style="width: <?php echo min($progress,100); ?>%"><?php echo number_format($progress,1); ?>%</div></div> <td><?php echo $target['staff_name'] ?? 'Overall'; ?>
            <?php endforeach; ?>
            </tbody> </table>
        </div>
        <?php
    }
    
    // Sales - Commission
    elseif ($page == 'sales' && $subpage == 'commission') {
        $total_commission = array_reduce($sales_commissions ?? [], function($carry, $item){return $carry + ($item['commission_amount'] ?: 0);}, 0);
        ?>
        <div class="page-title"><h2>Sales Commission</h2><div class="breadcrumb"><span class="breadcrumb-item active">Sales Commission</span></div></div>
        <div class="row">
            <div class="col-md-3"><div class="dashboard-card"><h3>$<?php echo number_format($total_commission,2); ?></h3><p>Total Commission</p></div></div>
            <div class="col-md-3"><div class="dashboard-card"><h3><?php echo count($sales_commissions ?? []); ?></h3><p>Commission Records</p></div></div>
        </div>
        <div class="form-container mb-4">
            <h4>Add Commission</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_sales_commission">
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Staff</label><select name="staff_id" class="form-control" required><?php foreach ($staff_list ?? [] as $staff): ?><option value="<?php echo $staff['id']; ?>"><?php echo $staff['position']; ?> (<?php echo $staff['employee_id']; ?>)</option><?php endforeach; ?></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Order</label><select name="order_id" class="form-control" required><option value="">-- Select Order --</option><?php foreach ($all_orders as $order): ?><option value="<?php echo $order['id']; ?>"><?php echo $order['order_number']; ?> - $<?php echo number_format($order['total_amount'],2); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2 mb-3"><label class="form-label">Rate (%)</label><input type="number" step="0.01" name="commission_rate" class="form-control" value="5.00" required></div>
                    <div class="col-md-2 mb-3"><label class="form-label">Status</label><select name="status" class="form-control"><option value="pending">Pending</option><option value="paid">Paid</option></select></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Commission</button>
            </form>
        </div>
        <div class="table-container"><h4>Commission Records</h4><table class="table"><thead><tr><th>Date</th><th>Staff</th><th>Order</th><th>Rate</th><th>Amount</th><th>Status</th><th>Paid At</th></tr></thead><tbody><?php foreach ($sales_commissions ?? [] as $sc): ?><tr><td><?php echo date('d M Y', strtotime($sc['calculated_date'])); ?></td><td><?php echo $sc['staff_name']; ?></td><td><?php echo $sc['order_number']; ?></td><td><?php echo number_format($sc['commission_rate'],2); ?>%</td><td>$<?php echo number_format($sc['commission_amount'],2); ?></td><td><?php echo ucfirst($sc['status']); ?></td><td><?php echo $sc['paid_date'] ? date('d M Y', strtotime($sc['paid_date'])) : '-'; ?></td></tr><?php endforeach; ?></tbody></table></div>
        <?php
    }
    
    // Sales - Deliveries
    elseif ($page == 'sales' && $subpage == 'deliveries') {
        ?>
        <div class="page-title"><h2>Pending Deliveries</h2><div class="breadcrumb"><span class="breadcrumb-item active">Manage Deliveries</span></div></div>
        <div class="sales-dashboard">
            <?php foreach ($pending_deliveries ?? [] as $delivery): ?>
            <div class="delivery-card">
                <div class="row">
                    <div class="col-md-3"><strong>Order #: <?php echo $delivery['order_number']; ?></strong><br><small class="text-muted">Delivery ID: #<?php echo $delivery['id']; ?></small></div>
                    <div class="col-md-3"><i class="bi bi-person"></i> <?php echo $delivery['customer_name']; ?><br><i class="bi bi-telephone"></i> <?php echo $delivery['customer_phone']; ?></div>
                    <div class="col-md-3"><i class="bi bi-geo-alt"></i> <?php echo $delivery['delivery_address']; ?><br><i class="bi bi-calendar"></i> Due: <?php echo date('d M Y', strtotime($delivery['delivery_date'])); ?></div>
                    <div class="col-md-3">
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="action" value="update_delivery_status">
                            <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                            <select name="status" class="form-control form-control-sm">
                                <option value="assigned" <?php echo $delivery['status'] == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                <option value="in_transit" <?php echo $delivery['status'] == 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                                <option value="delivered">Delivered</option>
                                <option value="failed">Failed</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($pending_deliveries)): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> No pending deliveries! All orders are delivered.</div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    // Salary - Payments
    elseif ($page == 'salary' && $subpage == 'payments') {
        ?>
        <div class="page-title">
            <h2>Salary Payments</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Salary Management</span>
            </div>
        </div>
        <div class="form-container mb-4">
            <h4>Record Salary Payment</h4>
            <form method="POST">
                <input type="hidden" name="action" value="pay_salary">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Select Staff</label><select name="staff_id" id="staff_id" class="form-control" required><option value="">-- Select Staff --</option><?php foreach ($staff_list ?? [] as $staff): ?><option value="<?php echo $staff['id']; ?>" data-salary="<?php echo $staff['salary']; ?>"><?php echo htmlspecialchars(($staff['full_name'] ?? $staff['employee_id']) . ' (' . $staff['employee_id'] . ') - ' . $staff['position']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Month</label><select name="month" class="form-control" required><option value="January">January</option><option value="February">February</option><option value="March">March</option><option value="April">April</option><option value="May">May</option><option value="June">June</option><option value="July">July</option><option value="August">August</option><option value="September">September</option><option value="October">October</option><option value="November">November</option><option value="December">December</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Year</label><input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Amount</label><input type="number" name="amount" id="salary_amount" class="form-control" step="0.01" readonly required></div>
                    <script>
                        document.getElementById('staff_id').addEventListener('change', function() {
                            var salary = this.options[this.selectedIndex].getAttribute('data-salary') || '';
                            document.getElementById('salary_amount').value = salary ? parseFloat(salary).toFixed(2) : '';
                        });
                    </script>
                </div>
                <button type="submit" class="btn btn-primary">Record Payment</button>
            </form>
        </div>
        <div class="table-container">
            <h4>Salary Payment History</h4>
            <table class="table"><thead> <th>Employee</th><th>Month</th><th>Year</th><th>Amount</th><th>Payment Date</th> </thead><tbody>
            <?php foreach ($salary_payments ?? [] as $payment): ?>
             <tr><td><?php echo $payment['employee_id']; ?> <td><?php echo $payment['month']; ?> <td><?php echo $payment['year']; ?> <td>$<?php echo number_format($payment['amount'], 2); ?> <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?>
            <?php endforeach; ?>
            </tbody> </table>
        </div>
        <?php
    }
    
    // Salary - Payroll
    elseif ($page == 'salary' && $subpage == 'payroll') {
        $selected_month = $_GET['month'] ?? date('F');
        $selected_year = $_GET['year'] ?? date('Y');

        // Calculate payroll info
        $payroll = [];
        foreach ($staff_list as $staff) {
            $branch_context = getBranchContextQuery();
            $deductions = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM salary_deductions WHERE staff_id = ? AND month = ? AND year = ? AND {$branch_context}");
            $deductions->execute([$staff['id'], $selected_month, $selected_year]);
            $deduction_total = $deductions->fetchColumn() ?: 0;
            
            $bonuses = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM salary_bonuses WHERE staff_id = ? AND month = ? AND year = ? AND {$branch_context}");
            $bonuses->execute([$staff['id'], $selected_month, $selected_year]);
            $bonus_total = $bonuses->fetchColumn() ?: 0;
            
            $net = $staff['salary'] - $deduction_total + $bonus_total;
            
            $paid = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM salary_payments WHERE staff_id = ? AND month = ? AND year = ? AND {$branch_context}");
            $paid->execute([$staff['id'], $selected_month, $selected_year]);
            $paid_amount = $paid->fetchColumn() ?: 0;

            $payroll[] = [
                'staff' => $staff,
                'basic' => $staff['salary'],
                'deductions' => $deduction_total,
                'bonuses' => $bonus_total,
                'net' => $net,
                'paid' => $paid_amount,
                'balance' => $net - $paid_amount,
            ];
        }
        ?>
        <div class="page-title">
            <h2>Payroll</h2>
            <div class="breadcrumb"><span class="breadcrumb-item active">Salary Management</span></div>
        </div>
        <div class="form-container mb-4">
            <h4>Payroll Period</h4>
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="salary">
                <input type="hidden" name="subpage" value="payroll">
                <div class="col-md-4"><select name="month" class="form-control">
                    <?php foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $m): ?>
                        <option value="<?php echo $m; ?>" <?php echo $selected_month == $m ? 'selected' : ''; ?>><?php echo $m; ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-4"><input type="number" name="year" class="form-control" value="<?php echo $selected_year; ?>" required></div>
                <div class="col-md-4"><button type="submit" class="btn btn-primary">View Payroll</button></div>
            </form>
        </div>
        <div class="table-container mb-4">
            <h4>Payroll Summary (<?php echo htmlspecialchars($selected_month . ' ' . $selected_year); ?>)</h4>
            <table class="table table-striped"><thead><tr><th>Employee</th><th>Basic</th><th>Deductions</th><th>Bonuses</th><th>Net</th><th>Paid</th><th>Balance</th></tr></thead><tbody>
            <?php foreach ($payroll as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars(($row['staff']['full_name'] ?? $row['staff']['employee_id']) . ' (' . $row['staff']['employee_id'] . ') - ' . $row['staff']['position']); ?></td>
                <td>$<?php echo number_format($row['basic'],2); ?></td>
                <td>$<?php echo number_format($row['deductions'],2); ?></td>
                <td>$<?php echo number_format($row['bonuses'],2); ?></td>
                <td>$<?php echo number_format($row['net'],2); ?></td>
                <td>$<?php echo number_format($row['paid'],2); ?></td>
                <td>$<?php echo number_format($row['balance'],2); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody></table>
        </div>
        <div class="form-container mb-4">
            <h4>Run Payroll (Generate Payments)</h4>
            <form method="POST">
                <input type="hidden" name="action" value="run_payroll">
                <input type="hidden" name="month" value="<?php echo htmlspecialchars($selected_month); ?>">
                <input type="hidden" name="year" value="<?php echo htmlspecialchars($selected_year); ?>">
                <button type="submit" class="btn btn-success">Run Payroll for <?php echo htmlspecialchars($selected_month . ' ' . $selected_year); ?></button>
            </form>
        </div>
        <?php
    }

    // Salary - Deductions
    elseif ($page == 'salary' && $subpage == 'deductions') {
        ?>
        <div class="page-title">
            <h2>Salary Deductions</h2>
            <div class="breadcrumb"><span class="breadcrumb-item active">Salary Management</span></div>
        </div>
        <div class="form-container mb-4">
            <h4>Add Deduction</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_deduction">
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Staff</label><select name="staff_id" class="form-control" required><option value="">-- Select Staff --</option><?php foreach ($staff_list ?? [] as $staff): ?><option value="<?php echo $staff['id']; ?>"><?php echo htmlspecialchars(($staff['full_name'] ?? $staff['employee_id']) . ' (' . $staff['employee_id'] . ') - ' . $staff['position']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Month</label><select name="month" class="form-control" required><option value="January">January</option><option value="February">February</option><option value="March">March</option><option value="April">April</option><option value="May">May</option><option value="June">June</option><option value="July">July</option><option value="August">August</option><option value="September">September</option><option value="October">October</option><option value="November">November</option><option value="December">December</option></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Year</label><input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required></div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Type</label><input type="text" name="deduction_type" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Amount</label><input type="number" name="amount" class="form-control" step="0.01" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Deduction</button>
            </form>
        </div>
        <div class="table-container"><h4>Deduction History</h4><table class="table"><thead><tr><th>Employee</th><th>Month</th><th>Year</th><th>Type</th><th>Amount</th><th>Description</th></tr></thead><tbody><?php foreach ($salary_deductions ?? [] as $d): ?><tr><td><?php echo $d['employee_id']; ?></td><td><?php echo $d['month']; ?></td><td><?php echo $d['year']; ?></td><td><?php echo htmlspecialchars($d['deduction_type']); ?></td><td>$<?php echo number_format($d['amount'], 2); ?></td><td><?php echo htmlspecialchars($d['description']); ?></td></tr><?php endforeach; ?></tbody></table></div>
        <?php
    }

    // Salary - Bonuses
    elseif ($page == 'salary' && $subpage == 'bonus') {
        ?>
        <div class="page-title">
            <h2>Salary Bonuses</h2>
            <div class="breadcrumb"><span class="breadcrumb-item active">Salary Management</span></div>
        </div>
        <div class="form-container mb-4">
            <h4>Add Bonus</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_bonus">
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Staff</label><select name="staff_id" class="form-control" required><?php foreach ($staff_list ?? [] as $staff): ?><option value="<?php echo $staff['id']; ?>"><?php echo $staff['employee_id']; ?> - <?php echo $staff['position']; ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Month</label><select name="month" class="form-control" required><option value="January">January</option><option value="February">February</option><option value="March">March</option><option value="April">April</option><option value="May">May</option><option value="June">June</option><option value="July">July</option><option value="August">August</option><option value="September">September</option><option value="October">October</option><option value="November">November</option><option value="December">December</option></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Year</label><input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required></div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Type</label><input type="text" name="bonus_type" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Amount</label><input type="number" name="amount" class="form-control" step="0.01" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
                </div>
                <button type="submit" class="btn btn-primary">Add Bonus</button>
            </form>
        </div>
        <div class="table-container"><h4>Bonus History</h4><table class="table"><thead><tr><th>Employee</th><th>Month</th><th>Year</th><th>Type</th><th>Amount</th><th>Description</th></tr></thead><tbody><?php foreach ($salary_bonuses ?? [] as $b): ?><tr><td><?php echo $b['employee_id']; ?></td><td><?php echo $b['month']; ?></td><td><?php echo $b['year']; ?></td><td><?php echo htmlspecialchars($b['bonus_type']); ?></td><td>$<?php echo number_format($b['amount'], 2); ?></td><td><?php echo htmlspecialchars($b['description']); ?></td></tr><?php endforeach; ?></tbody></table></div>
        <?php
    }

    // Salary - Payslips
    elseif ($page == 'salary' && $subpage == 'payslips') {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        ?>
        <div class="page-title d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Payslips</h2>
                <div class="breadcrumb">
                    <span class="breadcrumb-item"><a href="?page=salary&subpage=payroll">Salary Management</a></span>
                    <span class="breadcrumb-item active">Payslip Records</span>
                </div>
            </div>
        </div>

        <div class="form-container shadow-sm p-4 rounded bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Position</th>
                            <th>Period</th>
                            <th>Net Salary</th>
                            <th>Generated Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payslips ?? [] as $p): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($p['employee_id']); ?></td>
                                <td><?php echo htmlspecialchars($p['employee_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['position']); ?></td>
                                <td><?php echo ($months[$p['month']] ?? $p['month']) . ' ' . $p['year']; ?></td>
                                <td class="fw-bold text-success">$<?php echo number_format($p['net_salary'], 2); ?></td>
                                <td><?php echo date('d M Y', strtotime($p['generated_date'])); ?></td>
                                <td class="text-center">
                                    <a href="?page=salary&subpage=print_payslip&id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-printer"></i> View & Print
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($payslips)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No payslip records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // Salary - Print Payslip
    elseif ($page == 'salary' && $subpage == 'print_payslip') {
        $payslip_id = $_GET['id'] ?? 0;
        $payslip_stmt = $pdo->prepare("
            SELECT p.*, s.employee_id, s.position, s.salary as base_salary_original, u.full_name as employee_name, b.name as branch_name 
            FROM payslips p 
            JOIN staff s ON p.staff_id = s.id 
            JOIN users u ON s.user_id = u.id 
            LEFT JOIN branches b ON p.branch_id = b.id
            WHERE p.id = ? AND " . getBranchContextQuery('p') . "
        ");
        $payslip_stmt->execute([$payslip_id]);
        $p = $payslip_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$p) {
            echo "<div class='alert alert-danger'>Payslip not found.</div>";
        } else {
            $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];
            $month_name = $months[$p['month']] ?? $p['month'];
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Payslip - <?php echo $p['employee_id']; ?> - <?php echo $month_name; ?> <?php echo $p['year']; ?></title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
                <style>
                    body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; }
                    .payslip-container { max-width: 800px; margin: 40px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                    .payslip-header { border-bottom: 2px solid #198754; padding-bottom: 20px; margin-bottom: 30px; }
                    .payslip-title { font-size: 26px; font-weight: 700; color: #198754; text-transform: uppercase; letter-spacing: 1px; }
                    .payslip-meta { font-size: 14px; color: #6c757d; }
                    .payslip-details { margin-bottom: 30px; }
                    .table-summary { background-color: #f8f9fa; font-weight: bold; }
                    .btn-print { margin-bottom: 20px; }
                    @media print {
                        .btn-print { display: none !important; }
                        body { background: white; }
                        .payslip-container { margin: 0; padding: 0; box-shadow: none; max-width: 100%; }
                    }
                </style>
            </head>
            <body>
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mx-auto btn-print mt-4" style="max-width: 800px;">
                    <a href="?page=salary&subpage=payslips" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Payslips</a>
                    <button onclick="window.print()" class="btn btn-success btn-sm"><i class="bi bi-printer"></i> Print Payslip</button>
                </div>

                <div class="payslip-container">
                    <div class="payslip-header d-flex justify-content-between align-items-start">
                        <div>
                            <?php if (!empty($settings['company_logo'])): ?>
                                <img src="<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="Logo" style="max-height: 60px; margin-bottom: 10px; display: block;">
                            <?php endif; ?>
                            <h2 class="mb-1 text-success fw-bold"><?php echo COMPANY_NAME; ?></h2>
                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($p['branch_name'] ?: 'Main Branch'); ?></p>
                            <small class="text-muted">Camel Farming & Delivery Operations</small>
                        </div>
                        <div class="text-end">
                            <div class="payslip-title">PAYSLIP</div>
                            <div class="payslip-meta mt-2">
                                <strong>Period:</strong> <?php echo $month_name; ?> <?php echo $p['year']; ?><br>
                                <strong>Generated:</strong> <?php echo date('d M Y', strtotime($p['generated_date'])); ?>
                            </div>
                        </div>
                    </div>

                    <div class="payslip-details">
                        <div class="row bg-light p-3 rounded mb-4 border g-3">
                            <div class="col-md-6">
                                <span class="text-muted d-block small">Employee Name:</span>
                                <strong class="fs-6 text-dark"><?php echo htmlspecialchars($p['employee_name']); ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block small">Employee ID:</span>
                                <strong class="fs-6 text-dark"><?php echo htmlspecialchars($p['employee_id']); ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block small">Position / Designation:</span>
                                <strong class="fs-6 text-dark"><?php echo htmlspecialchars($p['position']); ?></strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block small">Statement Date:</span>
                                <strong class="fs-6 text-dark"><?php echo date('d M Y', strtotime($p['generated_date'])); ?></strong>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Earnings -->
                            <div class="col-md-6 mb-4">
                                <h5 class="border-bottom pb-2 text-success fw-bold"><i class="bi bi-plus-circle-fill"></i> Earnings</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td>Basic Salary</td>
                                        <td class="text-end fw-bold text-dark">$<?php echo number_format($p['base_salary'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Total Bonuses</td>
                                        <td class="text-end text-success fw-bold">+$<?php echo number_format($p['total_bonuses'], 2); ?></td>
                                    </tr>
                                    <tr class="border-top table-light">
                                        <td class="fw-bold">Total Earnings</td>
                                        <td class="text-end fw-bold text-success">$<?php echo number_format($p['base_salary'] + $p['total_bonuses'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Deductions -->
                            <div class="col-md-6 mb-4">
                                <h5 class="border-bottom pb-2 text-danger fw-bold"><i class="bi bi-dash-circle-fill"></i> Deductions</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td>Salary Deductions</td>
                                        <td class="text-end text-danger fw-bold">-$<?php echo number_format($p['total_deductions'], 2); ?></td>
                                    </tr>
                                    <tr class="border-top table-light">
                                        <td class="fw-bold">Total Deductions</td>
                                        <td class="text-end fw-bold text-danger">-$<?php echo number_format($p['total_deductions'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="card bg-success text-white p-3 mb-4 rounded border-0 shadow-sm mt-3">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h5 class="mb-0 fw-bold text-uppercase">Net Take-Home Pay</h5>
                                </div>
                                <div class="col-4 text-end">
                                    <h3 class="mb-0 fw-bold">$<?php echo number_format($p['net_salary'], 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-5 pt-4">
                        <div class="col-6 text-center">
                            <div style="border-top: 1px solid #dee2e6; width: 80%; margin: 0 auto;" class="pt-2">
                                <small class="text-muted d-block">Employee Signature</small>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div style="border-top: 1px solid #dee2e6; width: 80%; margin: 0 auto;" class="pt-2">
                                <small class="text-muted d-block">Authorized Signatory</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </body>
            </html>
            <?php
            exit;
        }
    }

    // Notifications - Alerts/SMS/WhatsApp/Email
    elseif ($page == 'notifications' && in_array($subpage, ['alerts', 'sms', 'whatsapp', 'email'])) {
        if ($subpage == 'alerts') {
            $notifications = getUserNotifications($pdo, $_SESSION['user_id'], 100);
        }
        ?>
        <div class="page-title"><h2>Notifications</h2><div class="breadcrumb"><span class="breadcrumb-item active"><?php echo ucfirst($subpage); ?></span></div></div>
        <?php if ($subpage == 'alerts'): ?>
            <div class="mb-3">
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="mark_all_notifications">
                    <button type="submit" class="btn btn-sm btn-success">Mark All Read</button>
                </form>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead><tr><th>When</th><th>Title</th><th>Message</th><th>Type</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($notifications as $note): 
                            $order_link = null;
                            if (strpos($note['message'], 'order #') !== false || strpos($note['message'], 'Order #') !== false) {
                                // Extract order number from message
                                if (preg_match('/order #([A-Z0-9-]+)/i', $note['message'], $matches)) {
                                    $order_number = $matches[1];
                                    $order_lookup = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
                                    $order_lookup->execute([$order_number]);
                                    $order_data = $order_lookup->fetch(PDO::FETCH_ASSOC);
                                    if ($order_data) {
                                        $order_link = "?page=orders&subpage=view&id=" . $order_data['id'];
                                    }
                                }
                            }
                        ?>
                        <tr>
                            <td><?php echo date('d M Y H:i', strtotime($note['created_at'])); ?></td>
                            <td>
                                <?php if ($order_link): ?>
                                    <a href="<?php echo $order_link; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($note['title']); ?> <i class="bi bi-arrow-right-circle"></i>
                                    </a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($note['title']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($order_link): ?>
                                    <a href="<?php echo $order_link; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($note['message']); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($note['message']); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo ucfirst($note['type']); ?></td>
                            <td><?php echo $note['is_read'] ? 'Read' : 'Unread'; ?></td>
                            <td>
                                <?php if (!$note['is_read']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="mark_notification_read">
                                    <input type="hidden" name="notification_id" value="<?php echo $note['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Mark Read</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="form-container mb-4">
                <h4>Send <?php echo ucfirst($subpage); ?> Notification</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="send_<?php echo $subpage; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Recipient Phone</label><input type="text" name="phone" class="form-control" placeholder="Enter phone number or leave blank for all" <?php echo $subpage == 'email' ? '' : 'required'; ?>></div>
                        <?php if ($subpage == 'email'): ?><div class="col-md-6 mb-3"><label class="form-label">Recipient Email</label><input type="email" name="email" class="form-control" placeholder="Enter email address (optional)"></div><?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="<?php echo ucfirst($subpage); ?> Alert" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Message</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            </div>
        <?php endif; ?>
        <?php
    }
    
    // Settings - General
    elseif ($page == 'settings' && $subpage == 'general') {
        ?>
        <div class="page-title"><h2>Company Settings</h2><div class="breadcrumb"><span class="breadcrumb-item active">General Settings</span></div></div>
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_settings">
                <input type="hidden" name="redirect_subpage" value="general">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Company Name</label><input type="text" name="setting_company_name" class="form-control" value="<?php echo $settings['company_name'] ?? ''; ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Company Email</label><input type="email" name="setting_company_email" class="form-control" value="<?php echo $settings['company_email'] ?? ''; ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Company Phone</label><input type="text" name="setting_company_phone" class="form-control" value="<?php echo $settings['company_phone'] ?? ''; ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Company Logo</label><input type="file" name="setting_company_logo" class="form-control"></div>
                    <?php if (!empty($settings['company_logo'])): ?>
                        <div class="col-md-12 mb-3"><img src="<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="Company Logo" style="max-height:70px;"></div>
                    <?php endif; ?>
                    <div class="col-md-6 mb-3"><label class="form-label">Currency</label><select name="setting_currency" class="form-control"><option value="USD" <?php echo ($settings['currency'] ?? '') == 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option><option value="SOS" <?php echo ($settings['currency'] ?? '') == 'SOS' ? 'selected' : ''; ?>>SOS - Somali Shilling</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Tax Rate (%)</label><input type="number" name="setting_tax_rate" class="form-control" step="0.1" value="<?php echo $settings['tax_rate'] ?? '5'; ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Delivery Fee</label><input type="number" name="setting_delivery_fee" class="form-control" step="0.01" value="<?php echo $settings['delivery_fee'] ?? '2.00'; ?>"></div>
                </div>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
        <?php
    }
    
    // Reports - Milk Production
    elseif ($page == 'reports' && $subpage == 'milk') {
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
                <h2>Milk Production Reports</h2>
                <div class="breadcrumb"><span class="breadcrumb-item active">Aggregated Production Reports</span></div>
            </div>
            <button onclick="window.print()" class="btn btn-outline-primary d-print-none"><i class="bi bi-printer"></i> Print Report</button>
        </div>
        
        <!-- Filters -->
        <div class="form-container mb-4 d-print-none">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="subpage" value="milk">
                
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
    
    // Reports - Sales
    elseif ($page == 'reports' && $subpage == 'sales') {
        $branch_context = getBranchContextQuery();
        $where_sr = getBranchContextQuery('sr');
        $sales_reports = $pdo->query("SELECT sr.*, p.name as top_product_name FROM sales_reports sr LEFT JOIN products p ON sr.top_product_id = p.id WHERE {$where_sr} ORDER BY sr.report_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Sales Reports</h2><div class="breadcrumb"><span class="breadcrumb-item active">Generate Reports</span></div></div>
        <div class="form-container">
            <h4>Generate Sales Report</h4>
            <form method="POST">
                <input type="hidden" name="action" value="generate_sales_report">
                <div class="row">
                    <div class="col-md-5 mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>" required></div>
                    <div class="col-md-5 mb-3"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="col-md-2 mb-3 d-flex align-items-end"><button type="submit" class="btn btn-primary">Generate</button></div>
                </div>
            </form>
        </div>
        <div class="table-container mt-4">
            <h4>Generated Reports</h4>
            <table class="table"><thead> <th>Date</th><th>Total Sales</th><th>Total Orders</th><th>Avg Order Value</th><th>Top Product</th> </thead><tbody>
            <?php foreach ($sales_reports as $report): ?>
             <tr><td><?php echo date('d M Y', strtotime($report['report_date'])); ?> <td>$<?php echo number_format($report['total_sales'], 2); ?> <td><?php echo $report['total_orders']; ?> <td>$<?php echo number_format($report['average_order_value'], 2); ?> <td><?php echo $report['top_product_name'] ?? 'N/A'; ?>
            <?php endforeach; ?>
            </tbody> </table>
        </div>
        <?php
    }
    
    // Reports - Customers
    elseif ($page == 'reports' && $subpage == 'customers') {
        $branch_context = getBranchContextQuery();
        $customer_stats = $pdo->query("
            SELECT 
                COUNT(*) as total_customers,
                COUNT(DISTINCT city) as total_cities
            FROM customers WHERE status='active' AND {$branch_context}
        ")->fetch(PDO::FETCH_ASSOC);
        
        $top_customers = $pdo->query("
            SELECT c.name, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount), 0) as total_spent
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id AND o.status = 'delivered'
            WHERE c.status='active' AND {$branch_context}
            GROUP BY c.id
            ORDER BY total_spent DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Customer Reports</h2><div class="breadcrumb"><span class="breadcrumb-item active">Customer Analytics</span></div></div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><?php echo $customer_stats['total_customers']; ?></h3>
                    <p>Total Customers</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h3><?php echo $customer_stats['total_cities']; ?></h3>
                    <p>Cities Covered</p>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <h4>Top 10 Customers by Spending</h4>
            <table class="table">
                <thead>
                    <tr><th>Customer</th><th>Orders</th><th>Total Spent</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($top_customers as $cust): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cust['name']); ?></td>
                        <td><?php echo $cust['order_count']; ?></td>
                        <td>$<?php echo number_format($cust['total_spent'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Reports - Products
    elseif ($page == 'reports' && $subpage == 'products') {
        $branch_context = getBranchContextQuery();
        $product_stats = $pdo->query("
            SELECT 
                COUNT(*) as total_products,
                SUM(stock_quantity) as total_stock,
                SUM(CASE WHEN stock_quantity < 10 THEN 1 ELSE 0 END) as low_stock_count
            FROM products WHERE status='active' AND {$branch_context}
        ")->fetch(PDO::FETCH_ASSOC);
        
        $top_products = $pdo->query("
            SELECT p.name, p.category, COALESCE(SUM(oi.quantity), 0) as total_sold, COALESCE(SUM(oi.quantity * oi.price), 0) as revenue
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'delivered'
            WHERE p.status='active' AND {$branch_context}
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Product Reports</h2><div class="breadcrumb"><span class="breadcrumb-item active">Product Analytics</span></div></div>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3><?php echo $product_stats['total_products']; ?></h3>
                    <p>Total Products</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3><?php echo $product_stats['total_stock']; ?></h3>
                    <p>Total Stock Units</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3><?php echo $product_stats['low_stock_count']; ?></h3>
                    <p>Low Stock Items</p>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <h4>Top 10 Products by Sales</h4>
            <table class="table">
                <thead>
                    <tr><th>Product</th><th>Category</th><th>Units Sold</th><th>Revenue</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($top_products as $prod): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($prod['name']); ?></td>
                        <td><?php echo htmlspecialchars($prod['category']); ?></td>
                        <td><?php echo $prod['total_sold']; ?></td>
                        <td>$<?php echo number_format($prod['revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Reports - Delivery
    elseif ($page == 'reports' && $subpage == 'delivery') {
        $branch_context = getBranchContextQuery();
        $delivery_stats = $pdo->query("
            SELECT 
                COUNT(*) as total_deliveries,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned_count
            FROM deliveries WHERE {$branch_context}
        ")->fetch(PDO::FETCH_ASSOC);
        
        $recent_deliveries = $pdo->query("
            SELECT d.*, o.order_number, c.name as customer_name, u.full_name as driver_name
            FROM deliveries d
            LEFT JOIN orders o ON d.order_id = o.id
            LEFT JOIN customers c ON o.customer_id = c.id
            LEFT JOIN staff s ON d.driver_id = s.id
            LEFT JOIN users u ON s.user_id = u.id
            WHERE {$branch_context}
            ORDER BY d.delivery_date DESC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Delivery Reports</h2><div class="breadcrumb"><span class="breadcrumb-item active">Delivery Analytics</span></div></div>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h3><?php echo $delivery_stats['total_deliveries']; ?></h3>
                    <p>Total Deliveries</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h3><?php echo $delivery_stats['delivered_count']; ?></h3>
                    <p>Delivered</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h3><?php echo $delivery_stats['assigned_count']; ?></h3>
                    <p>Assigned</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h3><?php echo $delivery_stats['pending_count']; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <h4>Recent Deliveries</h4>
            <table class="table">
                <thead>
                    <tr><th>Date</th><th>Order #</th><th>Customer</th><th>Driver</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_deliveries as $del): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($del['delivery_date'])); ?></td>
                        <td><?php echo $del['order_number'] ?? 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($del['customer_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($del['driver_name'] ?? 'Unassigned'); ?></td>
                        <td><span class="badge bg-<?php echo $del['status'] == 'delivered' ? 'success' : ($del['status'] == 'assigned' ? 'info' : 'warning'); ?>"><?php echo ucfirst($del['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    // Reports - Expenses
    elseif ($page == 'reports' && $subpage == 'expenses') {
        $branch_context = getBranchContextQuery();
        $expense_stats = $pdo->query("
            SELECT 
                COUNT(*) as total_expenses,
                SUM(amount) as total_amount,
                SUM(CASE WHEN category = 'Feed' THEN amount ELSE 0 END) as feed_expenses,
                SUM(CASE WHEN category = 'Veterinary' THEN amount ELSE 0 END) as veterinary_expenses,
                SUM(CASE WHEN category = 'Salary' THEN amount ELSE 0 END) as salary_expenses,
                SUM(CASE WHEN category = 'Equipment' THEN amount ELSE 0 END) as equipment_expenses
            FROM expenses WHERE {$branch_context}
        ")->fetch(PDO::FETCH_ASSOC);
        
        $expense_by_category = $pdo->query("
            SELECT category, SUM(amount) as total, COUNT(*) as count
            FROM expenses WHERE {$branch_context}
            GROUP BY category
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $recent_expenses = $pdo->query("
            SELECT * FROM expenses WHERE {$branch_context}
            ORDER BY expense_date DESC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Expense Reports</h2><div class="breadcrumb"><span class="breadcrumb-item active">Expense Analytics</span></div></div>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3><?php echo $expense_stats['total_expenses']; ?></h3>
                    <p>Total Expenses</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3>$<?php echo number_format($expense_stats['total_amount'] ?: 0, 2); ?></h3>
                    <p>Total Amount</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3>$<?php echo number_format($expense_stats['feed_expenses'] ?: 0, 2); ?></h3>
                    <p>Feed Expenses</p>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="table-container">
                    <h4>Expenses by Category</h4>
                    <table class="table">
                        <thead>
                            <tr><th>Category</th><th>Total</th><th>Count</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expense_by_category as $cat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cat['category']); ?></td>
                                <td>$<?php echo number_format($cat['total'], 2); ?></td>
                                <td><?php echo $cat['count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="table-container">
                    <h4>Recent Expenses</h4>
                    <table class="table">
                        <thead>
                            <tr><th>Date</th><th>Category</th><th>Amount</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_expenses as $exp): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($exp['expense_date'])); ?></td>
                                <td><?php echo htmlspecialchars($exp['category']); ?></td>
                                <td>$<?php echo number_format($exp['amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Reports - Staff
    elseif ($page == 'reports' && $subpage == 'staff') {
        $branch_context = getBranchContextQuery();
        $staff_stats = $pdo->query("
            SELECT 
                COUNT(*) as total_staff,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_staff,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_staff
            FROM staff WHERE {$branch_context}
        ")->fetch(PDO::FETCH_ASSOC);
        
        $top_performers = $pdo->query("
            SELECT s.employee_id, u.full_name, s.position, 
                   COALESCE(SUM(dp.deliveries_count), 0) as total_deliveries,
                   COALESCE(SUM(dp.on_time_deliveries), 0) as on_time_deliveries
            FROM staff s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN driver_performance dp ON u.id = dp.driver_id
            WHERE {$branch_context}
            GROUP BY s.id
            ORDER BY total_deliveries DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $salary_summary = $pdo->query("
            SELECT sp.*, s.employee_id, u.full_name as staff_name
            FROM salary_payments sp
            JOIN staff s ON sp.staff_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE {$branch_context}
            ORDER BY sp.payment_date DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title"><h2>Staff Performance Reports</h2><div class="breadcrumb"><span class="breadcrumb-item active">Staff Analytics</span></div></div>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3><?php echo $staff_stats['total_staff']; ?></h3>
                    <p>Total Staff</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3><?php echo $staff_stats['active_staff']; ?></h3>
                    <p>Active Staff</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3><?php echo $staff_stats['inactive_staff']; ?></h3>
                    <p>Inactive Staff</p>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="table-container">
                    <h4>Top Performers (Deliveries)</h4>
                    <table class="table">
                        <thead>
                            <tr><th>Employee</th><th>Position</th><th>Total Deliveries</th><th>On-Time</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_performers as $perf): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($perf['full_name'] ?: $perf['employee_id']); ?></td>
                                <td><?php echo htmlspecialchars($perf['position']); ?></td>
                                <td><?php echo $perf['total_deliveries']; ?></td>
                                <td><?php echo $perf['on_time_deliveries']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="table-container">
                    <h4>Recent Salary Payments</h4>
                    <table class="table">
                        <thead>
                            <tr><th>Employee</th><th>Amount</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salary_summary as $sal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sal['staff_name']); ?></td>
                                <td>$<?php echo number_format($sal['amount'], 2); ?></td>
                                <td><?php echo date('d M Y', strtotime($sal['payment_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Settings - Delivery
    elseif ($page == 'settings' && $subpage == 'delivery') {
        ?>
        <div class="page-title"><h2>Delivery Settings</h2><div class="breadcrumb"><span class="breadcrumb-item active">Delivery Cities</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                <input type="hidden" name="redirect_subpage" value="delivery">
                <div class="row">
                    <div class="col-md-12 mb-3"><label class="form-label">Delivery Cities (comma-separated)</label><input type="text" name="setting_delivery_cities" class="form-control" value="<?php echo htmlspecialchars($settings['delivery_cities'] ?? ''); ?>"></div>
                    <div class="col-md-12 mb-3"><label class="form-label">Delivery Hours</label><input type="text" name="setting_delivery_hours" class="form-control" value="<?php echo htmlspecialchars($settings['delivery_hours'] ?? ''); ?>"></div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
        <?php
    }
    
    // Settings - Maintenance
    elseif ($page == 'settings' && $subpage == 'maintenance') {
        ?>
        <div class="page-title"><h2>Maintenance Mode</h2><div class="breadcrumb"><span class="breadcrumb-item active">Maintenance</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                <input type="hidden" name="redirect_subpage" value="maintenance">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Maintenance Mode (on/off)</label><select name="setting_maintenance_mode" class="form-control"><option value="off" <?php echo (($settings['maintenance_mode'] ?? 'off')=='off' ? 'selected':''); ?>>Off</option><option value="on" <?php echo (($settings['maintenance_mode'] ?? 'off')=='on' ? 'selected':''); ?>>On</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Maintenance Message</label><input type="text" name="setting_maintenance_message" class="form-control" value="<?php echo htmlspecialchars($settings['maintenance_message'] ?? 'Scheduled maintenance in progress'); ?>"></div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
        <?php
    }
    
    // Settings - Currency
    elseif ($page == 'settings' && $subpage == 'currency') {
        ?>
        <div class="page-title"><h2>Currency & Tax</h2><div class="breadcrumb"><span class="breadcrumb-item active">Currency Settings</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                <input type="hidden" name="redirect_subpage" value="currency">
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Currency</label><input type="text" name="setting_currency" class="form-control" value="<?php echo htmlspecialchars($settings['currency'] ?? 'USD'); ?>"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Tax Rate (%)</label><input type="number" name="setting_tax_rate" class="form-control" step="0.1" value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '5'); ?>"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Delivery Fee</label><input type="number" name="setting_delivery_fee" class="form-control" step="0.01" value="<?php echo htmlspecialchars($settings['delivery_fee'] ?? '2.00'); ?>"></div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
        <?php
    }

    // Settings - Language
    elseif ($page == 'settings' && $subpage == 'language') {
        ?>
        <div class="page-title"><h2>Language Settings</h2><div class="breadcrumb"><span class="breadcrumb-item active">Language</span></div></div>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                <input type="hidden" name="redirect_subpage" value="language">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Default Language</label><select name="setting_default_language" class="form-control"><option value="en" <?php echo (($settings['default_language'] ?? 'en') == 'en' ? 'selected' : ''); ?>>English</option><option value="so" <?php echo (($settings['default_language'] ?? 'en') == 'so' ? 'selected' : ''); ?>>Somali</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Date Format</label><input type="text" name="setting_date_format" class="form-control" value="<?php echo htmlspecialchars($settings['date_format'] ?? 'Y-m-d'); ?>"></div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
        <?php
    }

    // Security - Monitoring
    elseif ($page == 'security' && $subpage == 'monitoring') {
        $branch_context = getBranchContextQuery();
        $user_count = $pdo->query("SELECT COUNT(*) FROM users WHERE {$branch_context}")->fetchColumn();
        $active_sessions = $pdo->query("SELECT COUNT(*) FROM system_activity_logs WHERE action LIKE '%login%' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)")->fetchColumn();
        ?>
        <div class="page-title"><h2>System Monitoring</h2><div class="breadcrumb"><span class="breadcrumb-item active">Monitoring</span></div></div>
        <div class="row"><div class="col-md-3"><div class="dashboard-card"><h3><?php echo $user_count; ?></h3><p>Total Users</p></div></div><div class="col-md-3"><div class="dashboard-card"><h3><?php echo $active_sessions; ?></h3><p>Recent Logins</p></div></div><div class="col-md-6"><div class="dashboard-card"><h4>Maintenance Mode</h4><p><?php echo ($settings['maintenance_mode'] ?? 'off') == 'on' ? 'On' : 'Off'; ?> - <?php echo htmlspecialchars($settings['maintenance_message'] ?? ''); ?></p></div></div></div>
        <?php
    }

    // Customers - List
    elseif ($page == 'customers' && $subpage == 'list') {
        ?>
        <div class="page-title">
            <h2>All Customers</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Customer Management</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Address</th><th>City</th><th>Registration Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($customer['address']); ?></td>
                        <td><?php echo htmlspecialchars($customer['city'] ?: 'N/A'); ?></td>
                        <td><?php echo isset($customer['created_at']) && $customer['created_at'] ? date('d M Y', strtotime($customer['created_at'])) : 'N/A'; ?></td>
                        <td>
                            <a href="?page=customers&subpage=view&id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">View Orders</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Customers - View Orders
    elseif ($page == 'customers' && $subpage == 'view') {
        $customer_id = $_GET['id'] ?? 0;
        $branch_context = getBranchContextQuery();
        $customer_stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ? AND {$branch_context}");
        $customer_stmt->execute([$customer_id]);
        $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);
        $customer_orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? AND {$branch_context} ORDER BY order_date DESC");
        $customer_orders_stmt->execute([$customer_id]);
        $customer_orders = $customer_orders_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            echo "<div class='alert alert-danger'>Customer not found.</div>";
        } else {
            ?>
            <div class="page-title">
                <h2>Customer: <?php echo htmlspecialchars($customer['name']); ?></h2>
                <div class="breadcrumb">
                    <span class="breadcrumb-item"><a href="?page=customers&subpage=list">Customers</a></span>
                    <span class="breadcrumb-item active"><?php echo htmlspecialchars($customer['name']); ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-container">
                        <h4>Customer Details</h4>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email'] ?: 'N/A'); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['address']); ?></p>
                        <p><strong>City:</strong> <?php echo htmlspecialchars($customer['city'] ?: 'N/A'); ?></p>
                        <p><strong>Registered:</strong> <?php echo (!empty($customer['created_at'])) ? date('d M Y', strtotime($customer['created_at'])) : 'N/A'; ?></p>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-container">
                        <h4>Order History</h4>
                        <table class="table">
                            <thead>
                                <tr><th>Order #</th><th>Date</th><th>Amount</th><th>Status</th><th>Payment</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customer_orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_number']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><span class="badge bg-<?php echo $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'info'); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                                    <td><span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                    <td><a href="?page=orders&subpage=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">View</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    // Customers - Add
    elseif ($page == 'customers' && $subpage == 'add') {
        ?>
        <div class="page-title">
            <h2>Add New Customer</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item"><a href="?page=customers&subpage=list">Customers</a></span>
                <span class="breadcrumb-item active">Add Customer</span>
            </div>
        </div>
        <div class="form-container">
            <h4>Customer Information</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_customer">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Add Customer
                </button>
                <a href="?page=customers&subpage=list" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        <?php
    }

    // Customers - Address Book
    elseif ($page == 'customers' && $subpage == 'addresses') {
        $branch_context = getBranchContextQuery();
        $addresses = $pdo->query("SELECT DISTINCT address, city, COUNT(*) as customer_count FROM customers WHERE status='active' AND {$branch_context} GROUP BY address, city ORDER BY customer_count DESC")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Address Book</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item"><a href="?page=customers&subpage=list">Customers</a></span>
                <span class="breadcrumb-item active">Address Book</span>
            </div>
        </div>
        <div class="table-container">
            <h4>Delivery Addresses</h4>
            <table class="table">
                <thead>
                    <tr><th>Address</th><th>City</th><th>Customer Count</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($addresses as $addr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($addr['address']); ?></td>
                        <td><?php echo htmlspecialchars($addr['city'] ?: 'N/A'); ?></td>
                        <td><span class="badge bg-primary"><?php echo $addr['customer_count']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Customers - Order History
    elseif ($page == 'customers' && $subpage == 'history') {
        $branch_context = getBranchContextQuery('o');
        $all_orders = $pdo->query("SELECT o.*, c.name as customer_name FROM orders o JOIN customers c ON o.customer_id = c.id WHERE {$branch_context} ORDER BY o.order_date DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Order History</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item"><a href="?page=customers&subpage=list">Customers</a></span>
                <span class="breadcrumb-item active">Order History</span>
            </div>
        </div>
        <div class="table-container">
            <h4>Recent Orders (All Customers)</h4>
            <table class="table">
                <thead>
                    <tr><th>Order #</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th><th>Payment</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($all_orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><span class="badge bg-<?php echo $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'info'); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                        <td><span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                        <td><a href="?page=orders&subpage=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Customers - Support Requests
    elseif ($page == 'customers' && $subpage == 'support') {
        // Get filter parameters
        $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
        $priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
        
        // Build query
        $where = ['1=1'];
        $params = [];
        
        if ($status_filter) {
            $where[] = 'st.status = ?';
            $params[] = $status_filter;
        }
        
        if ($priority_filter) {
            $where[] = 'st.priority = ?';
            $params[] = $priority_filter;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get tickets
        $stmt = $pdo->prepare("
            SELECT st.*, c.name as customer_name, u.full_name as assigned_to_name
            FROM support_tickets st
            LEFT JOIN customers c ON st.customer_id = c.id
            LEFT JOIN users u ON st.assigned_to = u.id
            WHERE $where_clause
            ORDER BY st.created_at DESC
        ");
        $stmt->execute($params);
        $tickets = $stmt->fetchAll();
        
        // Get ticket details if viewing specific ticket
        $ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
        $ticket_details = null;
        $ticket_replies = [];
        
        if ($ticket_id) {
            $stmt = $pdo->prepare("
                SELECT st.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email,
                       u.full_name as assigned_to_name
                FROM support_tickets st
                LEFT JOIN customers c ON st.customer_id = c.id
                LEFT JOIN users u ON st.assigned_to = u.id
                WHERE st.id = ?
            ");
            $stmt->execute([$ticket_id]);
            $ticket_details = $stmt->fetch();
            
            if ($ticket_details) {
                $stmt = $pdo->prepare("
                    SELECT tr.*, u.full_name as user_name, u.role as user_role
                    FROM ticket_replies tr
                    LEFT JOIN users u ON tr.user_id = u.id
                    WHERE tr.ticket_id = ?
                    ORDER BY tr.created_at ASC
                ");
                $stmt->execute([$ticket_id]);
                $ticket_replies = $stmt->fetchAll();
            }
        }
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                if ($_POST['action'] === 'update_status' && $ticket_id) {
                    $status = $_POST['status'];
                    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
                    
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
                    
                    header("Location: ?page=customers&subpage=support&ticket_id=$ticket_id");
                    exit;
                }
                
                if ($_POST['action'] === 'add_reply' && $ticket_id) {
                    $message = trim($_POST['message']);
                    $is_internal = isset($_POST['is_internal']) ? 1 : 0;
                    
                    if (!empty($message)) {
                        $stmt = $pdo->prepare("
                            INSERT INTO ticket_replies (ticket_id, user_id, message, is_internal)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([$ticket_id, $_SESSION['user_id'], $message, $is_internal]);
                        
                        // Update ticket timestamp
                        $stmt = $pdo->prepare("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$ticket_id]);
                        
                        header("Location: ?page=customers&subpage=support&ticket_id=$ticket_id");
                        exit;
                    }
                }
            }
        }
        
        // Get staff for assignment
        $staff = $pdo->query("SELECT id, full_name FROM users WHERE role IN ('admin', 'manager', 'staff') ORDER BY full_name")->fetchAll();
        ?>
        <div class="page-title">
            <h2>Support Requests</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item"><a href="?page=customers&subpage=list">Customers</a></span>
                <span class="breadcrumb-item active">Support Requests</span>
            </div>
        </div>
        
        <?php if ($ticket_id && $ticket_details): ?>
        <!-- View Ticket Details -->
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Ticket #<?php echo htmlspecialchars($ticket_details['ticket_number']); ?></h3>
                <a href="?page=customers&subpage=support" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Tickets
                </a>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Customer Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($ticket_details['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($ticket_details['customer_phone'] ?? 'N/A'); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($ticket_details['customer_email'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Ticket Information</h6>
                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($ticket_details['subject']); ?></p>
                            <p><strong>Category:</strong> <?php echo htmlspecialchars($ticket_details['category'] ?? 'General'); ?></p>
                            <p><strong>Priority:</strong> 
                                <span class="badge bg-<?php 
                                    echo match($ticket_details['priority']) {
                                        'urgent' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'info',
                                        'low' => 'secondary',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst($ticket_details['priority']); ?>
                                </span>
                            </p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo match($ticket_details['status']) {
                                        'open' => 'primary',
                                        'in_progress' => 'warning',
                                        'resolved' => 'success',
                                        'closed' => 'dark',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo str_replace('_', ' ', ucfirst($ticket_details['status'])); ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($ticket_details['created_at'])); ?></p>
                            <?php if ($ticket_details['resolved_at']): ?>
                            <p><strong>Resolved:</strong> <?php echo date('M d, Y H:i', strtotime($ticket_details['resolved_at'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Description</h6>
                    <p><?php echo nl2br(htmlspecialchars($ticket_details['description'])); ?></p>
                </div>
            </div>
            
            <!-- Update Status Form -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Update Status</h6>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="open" <?php echo $ticket_details['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="in_progress" <?php echo $ticket_details['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $ticket_details['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="closed" <?php echo $ticket_details['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Assign To</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($staff as $s): ?>
                                    <option value="<?php echo $s['id']; ?>" <?php echo $ticket_details['assigned_to'] == $s['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['full_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Replies -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Conversation</h6>
                    
                    <?php foreach ($ticket_replies as $reply): ?>
                    <div class="border rounded p-3 mb-3 <?php echo $reply['is_internal'] ? 'bg-light' : ''; ?>">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>
                                <?php echo htmlspecialchars($reply['user_name'] ?? 'Customer'); ?>
                                <?php if ($reply['is_internal']): ?>
                                <span class="badge bg-warning text-dark">Internal</span>
                                <?php endif; ?>
                            </strong>
                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></small>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Add Reply Form -->
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="action" value="add_reply">
                        <div class="mb-3">
                            <label class="form-label">Add Reply</label>
                            <textarea name="message" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_internal" class="form-check-input" id="is_internal">
                            <label class="form-check-label" for="is_internal">Internal note (not visible to customer)</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Reply</button>
                    </form>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Ticket List -->
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>All Support Tickets</h3>
                <div class="d-flex gap-2">
                    <form method="GET" class="d-flex gap-2">
                        <input type="hidden" name="page" value="customers">
                        <input type="hidden" name="subpage" value="support">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="">All Priority</option>
                            <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </form>
                </div>
            </div>
            
            <?php if (empty($tickets)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No support tickets found.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Customer</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['ticket_number']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['customer_name'] ?? 'Unknown'); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($ticket['priority']) {
                                        'urgent' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'info',
                                        'low' => 'secondary',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst($ticket['priority']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($ticket['status']) {
                                        'open' => 'primary',
                                        'in_progress' => 'warning',
                                        'resolved' => 'success',
                                        'closed' => 'dark',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo str_replace('_', ' ', ucfirst($ticket['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                            <td>
                                <a href="?page=customers&subpage=support&ticket_id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php
    }

    // Notifications
    elseif ($page == 'notifications') {
        $notifications = getUserNotifications($pdo, $_SESSION['user_id'], 50);
        ?>
        <div class="page-title">
            <h2>Notifications</h2>
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Alerts & Messages</span>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="form-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><i class="bi bi-bell"></i> Your Notifications</h4>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="mark_all_notifications_read">
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2-all"></i> Mark all as read</button>
                        </form>
                    </div>
                    
                    <?php if (empty($notifications)): ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                            You don't have any notifications yet.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $n): ?>
                                <?php
                                $order_num_match = null;
                                $delivery_id = null;
                                $target_order_id = null;
                                
                                if ($current_role == 'driver' && $n['title'] == 'New Delivery Assignment') {
                                    if (preg_match('/order #([A-Z0-9-]+)/', $n['message'], $matches)) {
                                        $order_num_match = $matches[1];
                                    }
                                    if ($order_num_match) {
                                        $del_stmt = $pdo->prepare("SELECT d.id FROM deliveries d JOIN orders o ON d.order_id = o.id WHERE o.order_number = ? AND d.driver_id = ?");
                                        $del_stmt->execute([$order_num_match, $_SESSION['user_id']]);
                                        $delivery_id = $del_stmt->fetchColumn();
                                    }
                                } elseif (in_array($current_role, ['admin', 'manager', 'staff']) && $n['title'] == 'New Order Received') {
                                    if (preg_match('/order #([A-Z0-9-]+)/', $n['message'], $matches)) {
                                        $order_num_match = $matches[1];
                                        $ord_stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
                                        $ord_stmt->execute([$order_num_match]);
                                        $target_order_id = $ord_stmt->fetchColumn();
                                    }
                                }
                                ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start <?php echo $n['is_read'] ? 'bg-light' : ''; ?>">
                                    <div class="ms-2 me-auto w-100">
                                        <?php if ($delivery_id): ?>
                                        <a href="?page=delivery&subpage=view&id=<?php echo $delivery_id; ?>" class="text-decoration-none d-block text-dark">
                                        <?php elseif ($target_order_id): ?>
                                        <a href="?page=orders&subpage=view&id=<?php echo $target_order_id; ?>" class="text-decoration-none d-block text-dark">
                                        <?php endif; ?>
                                        
                                        <div class="fw-bold text-<?php echo $n['type'] == 'error' ? 'danger' : ($n['type'] == 'success' ? 'success' : 'primary'); ?>">
                                            <?php echo htmlspecialchars($n['title']); ?>
                                        </div>
                                        <div><?php echo htmlspecialchars($n['message']); ?></div>
                                        <small class="text-muted"><i class="bi bi-clock"></i> <?php echo date('d M Y h:i A', strtotime($n['created_at'])); ?></small>
                                        
                                        <?php if ($delivery_id): ?>
                                        <div class="mt-2">
                                            <span class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View Order Details</span>
                                        </div>
                                        </a>
                                        <?php elseif ($target_order_id): ?>
                                        <div class="mt-2">
                                            <span class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Process Order</span>
                                        </div>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$n['is_read']): ?>
                                        <form method="POST" class="ms-3">
                                            <input type="hidden" name="action" value="mark_notification_read">
                                            <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-link text-success p-0" title="Mark as read"><i class="bi bi-check-circle fs-5"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php } // close previous block
    // Subscriptions - Add
    elseif ($page == 'subscriptions' && $subpage == 'add') {
        // Simple form to add a subscription (placeholder implementation)
        $where = $branch_context; // use branch context for customer filtering
        $customers = $pdo->query("SELECT id, name FROM customers WHERE {$where}")->fetchAll(PDO::FETCH_ASSOC);
        $subscription_plans = $pdo->query("SELECT * FROM subscription_plans WHERE {$branch_context} AND status='active'")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="page-title">
            <h2>Add Subscription</h2>
            <div class="breadcrumb"><span class="breadcrumb-item active">Subscriptions</span></div>
        </div>
        <div class="form-container mb-4">
            <h4>New Subscription</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_subscription">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">-- Select Customer --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subscription Plan</label>
                        <select name="plan_id" class="form-control" id="plan_select">
                            <option value="">-- Select Existing Plan --</option>
                            <?php foreach ($subscription_plans as $plan): ?>
                                <option value="<?php echo $plan['id']; ?>" data-price="<?php echo $plan['price']; ?>"><?php echo htmlspecialchars($plan['name']); ?> ($<?php echo number_format($plan['price'], 2); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Or New Plan Name</label>
                        <input type="text" name="plan_name" id="plan_name" class="form-control" placeholder="Only if not selecting above">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" name="price" class="form-control" step="0.01" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Subscription</button>
            </form>
            <script>
                document.getElementById('plan_select').addEventListener('change', function() {
                    const priceInput = document.querySelector('input[name="price"]');
                    const planNameInput = document.getElementById('plan_name');
                    
                    if (this.value) {
                        const selectedOption = this.options[this.selectedIndex];
                        priceInput.value = selectedOption.getAttribute('data-price');
                        planNameInput.value = '';
                        planNameInput.removeAttribute('required');
                    } else {
                        planNameInput.setAttribute('required', 'required');
                    }
                });
            </script>
        </div>
<?php }
    
    // Default for any page not handled
    else {
?>
    <div class="page-title">
        <h2><?php echo ucfirst(str_replace('-', ' ', $page)); ?></h2>
        <div class="breadcrumb">
            <span class="breadcrumb-item active"><?php echo ucfirst(str_replace('-', ' ', $subpage)); ?></span>
        </div>
    </div>
    <div class="form-container">

    </div>
<?php
    }
}
