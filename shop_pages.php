<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Shop Pages - Products, Cart, Checkout, Orders, Invoices, Profile

if ($current_page == 'shop') {
    // Get customer data if logged in
    $customer_data = null;
    if (isset($_SESSION['customer_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $customer_data = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get cart count
    $cart_count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += $item['quantity'];
        }
    }
    
    // Get products for shop display
    $shop_products = $pdo->query("SELECT * FROM products WHERE status='active' ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);
    $settings_raw = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_ASSOC);
    $settings = array_column($settings_raw, 'setting_value', 'setting_key');
    
    $subpage = $_GET['subpage'] ?? 'products';
    
    // Handle cart actions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action == 'add_to_cart') {
            $product_id = $_POST['product_id'];
            $quantity = (int)$_POST['quantity'];

            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $product_id) {
                    $item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION['cart'][] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'unit' => $product['unit']
                ];
            }

            $_SESSION['message'] = "Product added to cart!";
            $_SESSION['message_type'] = "success";
            header('Location: ?page=shop&subpage=products');
            exit;
        }
        
        elseif ($action == 'update_cart') {
            $product_id = $_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($quantity <= 0) {
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['id'] == $product_id) {
                        unset($_SESSION['cart'][$key]);
                        break;
                    }
                }
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            } else {
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $product_id) {
                        $item['quantity'] = $quantity;
                        break;
                    }
                }
            }
            
            $_SESSION['message'] = "Cart updated!";
            $_SESSION['message_type'] = "success";
            header('Location: ?page=shop&subpage=cart');
            exit;
        }
        
        elseif ($action == 'remove_from_cart') {
            $product_id = $_POST['product_id'];
            
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $product_id) {
                    unset($_SESSION['cart'][$key]);
                    break;
                }
            }
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            
            $_SESSION['message'] = "Product removed from cart!";
            $_SESSION['message_type'] = "success";
            header('Location: ?page=shop&subpage=cart');
            exit;
        }
        
        elseif ($action == 'place_order') {
            if (!isset($_SESSION['customer_id'])) {
                $_SESSION['message'] = "Please login to place order!";
                $_SESSION['message_type'] = "danger";
                header('Location: ?page=login');
                exit;
            }
            
            $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
            $total_amount = $_POST['total_amount'];
            $delivery_address = $_POST['delivery_address'];
            $delivery_phone = $_POST['delivery_phone'];
            $delivery_instructions = $_POST['delivery_instructions'] ?? '';
            $payment_method = $_POST['payment_method'];
            
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, order_date, total_amount, status, payment_status, delivery_address, delivery_phone, delivery_instructions) VALUES (?, ?, NOW(), ?, 'pending', 'paid', ?, ?, ?)");
            $stmt->execute([$order_number, $_SESSION['customer_id'], $total_amount, $delivery_address, $delivery_phone, $delivery_instructions]);
            $order_id = $pdo->lastInsertId();
            
            $tax_rate = floatval($settings['tax_rate'] ?? 5) / 100;
            $delivery_fee = floatval($settings['delivery_fee'] ?? 2.00);
            $cart_total = 0;
            
            foreach ($_SESSION['cart'] as $item) {
                $cart_total += $item['price'] * $item['quantity'];
            }
            
            $subtotal = $cart_total;
            $tax = $subtotal * $tax_rate;
            $grand_total = $subtotal + $tax + $delivery_fee;
            
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $item) {
                $item_stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?")->execute([$item['quantity'], $item['id']]);
            }
            
            createInvoice($pdo, $order_id, $_SESSION['customer_id'], $subtotal, $tax, $delivery_fee, $grand_total);
            
            $delivery_stmt = $pdo->prepare("INSERT INTO deliveries (order_id, delivery_date, status, delivery_address, recipient_name) VALUES (?, DATE_ADD(NOW(), INTERVAL 1 DAY), 'assigned', ?, ?)");
            $delivery_stmt->execute([$order_id, $delivery_address, $_SESSION['customer_name']]);
            
            $payment_stmt = $pdo->prepare("INSERT INTO payments (order_id, amount, payment_date, payment_method, status) VALUES (?, ?, NOW(), ?, 'completed')");
            $payment_stmt->execute([$order_id, $grand_total, $payment_method]);
            
            logOrderStatus($pdo, $order_id, 'pending', $_SESSION['user_id'], 'Order placed');
            
            unset($_SESSION['cart']);
            
            notifyStaff($pdo, 
                "New Order Received", 
                "New order #{$order_number} has been placed. Total: $" . number_format($grand_total, 2),
                'info'
            );
            
            $_SESSION['message'] = "Order placed successfully! Order #: $order_number";
            $_SESSION['message_type'] = "success";
            $_SESSION['last_order'] = $order_number;
            
            header('Location: ?page=shop&subpage=orders');
            exit;
        }
        
        elseif ($action == 'update_profile') {
            if (isset($_SESSION['customer_id'])) {
                $stmt = $pdo->prepare("UPDATE customers SET name=?, phone=?, address=?, city=? WHERE id=?");
                $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['address'], $_POST['city'], $_SESSION['customer_id']]);
                $_SESSION['customer_name'] = $_POST['name'];
                
                $_SESSION['message'] = "Profile updated successfully!";
                $_SESSION['message_type'] = "success";
                header('Location: ?page=shop&subpage=profile');
                exit;
            }
        }
        
        elseif ($action == 'cancel_order') {
            $order_id = $_POST['order_id'];
            $reason = $_POST['cancellation_reason'] ?? 'Customer requested cancellation';
            
            $order_check = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND customer_id = ?");
            $order_check->execute([$order_id, $_SESSION['customer_id']]);
            $order = $order_check->fetch(PDO::FETCH_ASSOC);
            
            if ($order && in_array($order['status'], ['pending', 'confirmed'])) {
                $pdo->prepare("UPDATE orders SET status = 'cancellation_requested', cancellation_reason = ? WHERE id = ?")
                    ->execute([$reason, $order_id]);
                
                logOrderStatus($pdo, $order_id, 'cancellation_requested', $_SESSION['user_id'], $reason);
                
                notifyStaff($pdo, 
                    "Cancellation Request", 
                    "Customer requested cancellation for order #{$order_id}. Reason: {$reason}",
                    'warning'
                );
                
                $_SESSION['message'] = "Cancellation request submitted successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "This order cannot be cancelled.";
                $_SESSION['message_type'] = "danger";
            }
            
            header('Location: ?page=shop&subpage=orders');
            exit;
        }
    }
    
    // Check for session messages
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    
    // Products Page
    if ($subpage == 'products') {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Shop - <?php echo COMPANY_NAME; ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
            <style>
                :root {
                    --primary: #007bff;
                    --secondary: #0056b3;
                    --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                }
                body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
                .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
                .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
                
                .customer-nav {
                    background: white;
                    padding: 15px 0;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    margin-bottom: 30px;
                }
                
                .customer-nav .nav-links {
                    display: flex;
                    align-items: center;
                    gap: 30px;
                }
                
                .customer-nav .nav-links a {
                    color: #555;
                    text-decoration: none;
                    font-weight: 500;
                    transition: color 0.3s;
                }
                
                .customer-nav .nav-links a:hover,
                .customer-nav .nav-links a.active {
                    color: var(--primary);
                }
                
                .cart-icon {
                    position: relative;
                    cursor: pointer;
                    font-size: 1.3em;
                    color: #555;
                }
                
                .cart-badge {
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background: #dc3545;
                    color: white;
                    font-size: 0.7em;
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .product-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                    gap: 25px;
                    margin-top: 30px;
                }
                
                .product-card {
                    background: #fff;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                    transition: all 0.3s ease;
                    position: relative;
                    border: 1px solid rgba(0,0,0,0.05);
                }
                
                .product-card:hover {
                    transform: translateY(-10px);
                    box-shadow: 0 15px 30px rgba(102, 126, 234, 0.2);
                }
                
                .product-badge {
                    position: absolute;
                    top: 15px;
                    right: 15px;
                    background: var(--gradient);
                    color: white;
                    padding: 5px 15px;
                    border-radius: 25px;
                    font-size: 0.8em;
                    font-weight: 600;
                    z-index: 2;
                }
                
                .product-image {
                    height: 220px;
                    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                
                .product-image img {
                    max-width: 100%;
                    max-height: 180px;
                    object-fit: contain;
                }
                
                .product-info {
                    padding: 20px;
                    text-align: center;
                }
                
                .product-category {
                    color: var(--primary);
                    font-size: 0.8em;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    margin-bottom: 5px;
                }
                
                .product-name {
                    font-size: 1.2em;
                    font-weight: 600;
                    color: #333;
                    margin-bottom: 10px;
                }
                
                .product-price {
                    font-size: 1.5em;
                    font-weight: bold;
                    color: var(--primary);
                    margin-bottom: 15px;
                }
                
                .product-price small {
                    font-size: 0.6em;
                    color: #999;
                    font-weight: normal;
                }
                
                .btn-add-cart {
                    background: var(--gradient);
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 25px;
                    font-weight: 500;
                    transition: all 0.3s;
                    width: 100%;
                    cursor: pointer;
                }
                
                .btn-add-cart:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                }
                
                .quantity-input {
                    width: 70px;
                    text-align: center;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    padding: 5px;
                }
                
                .footer {
                    background: var(--gradient);
                    color: white;
                    padding: 60px 0 20px;
                    margin-top: 60px;
                }
                
                .footer a { color: rgba(255,255,255,0.9); text-decoration: none; }
                .footer a:hover { color: white; }
                
                .alert {
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="customer-nav">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="nav-links">
                            <a href="?page=home">Home</a>
                            <a href="?page=shop&subpage=products" class="active">Shop</a>
                            <?php if (isset($_SESSION['customer_id'])): ?>
                            <a href="?page=shop&subpage=orders">My Orders</a>
                            <a href="?page=shop&subpage=invoices">My Invoices</a>
                            <a href="?page=shop&subpage=profile">Profile</a>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (isset($_SESSION['customer_id'])): ?>
                            <span>Welcome, <?php echo $_SESSION['customer_name']; ?></span>
                            <a href="?logout=1" class="btn btn-outline-danger btn-sm">Logout</a>
                            <?php else: ?>
                            <a href="?page=login" class="btn btn-primary btn-sm">Login</a>
                            <a href="?page=register" class="btn btn-outline-primary btn-sm">Register</a>
                            <?php endif; ?>
                            <div class="cart-icon" onclick="window.location.href='?page=shop&subpage=cart'">
                                <i class="bi bi-cart"></i>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <h2 class="mb-4">Our Products</h2>
                
                <div class="product-grid">
                    <?php foreach ($shop_products as $product): ?>
                    <div class="product-card">
                        <div class="product-badge"><?php echo $product['category']; ?></div>
                        <div class="product-image">
                            <img src="assets/images/<?php echo $product['image'] ?: 'default-product.jpg'; ?>" 
                                 alt="<?php echo $product['name']; ?>"
                                 onerror="this.src='assets/images/camel-milk.jpg'">
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?php echo $product['category']; ?></div>
                            <div class="product-name"><?php echo $product['name']; ?></div>
                            <div class="product-price">
                                $<?php echo number_format($product['price'], 2); ?> 
                                <small>/<?php echo $product['unit']; ?></small>
                            </div>
                            <div class="product-description text-muted small mb-3">
                                <?php echo $product['description'] ?? 'Fresh from our farm'; ?>
                            </div>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="10" class="quantity-input" required>
                                <button type="submit" class="btn-add-cart">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <footer class="footer">
                <div class="container text-center">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
    // Cart Page
    elseif ($subpage == 'cart') {
        $cart_total = 0;
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $cart_total += $item['price'] * $item['quantity'];
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Shopping Cart - <?php echo COMPANY_NAME; ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
            <style>
                :root {
                    --primary: #007bff;
                    --secondary: #0056b3;
                    --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                }
                body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
                .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
                .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
                
                .customer-nav {
                    background: white;
                    padding: 15px 0;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    margin-bottom: 30px;
                }
                
                .customer-nav .nav-links {
                    display: flex;
                    align-items: center;
                    gap: 30px;
                }
                
                .customer-nav .nav-links a {
                    color: #555;
                    text-decoration: none;
                    font-weight: 500;
                }
                
                .customer-nav .nav-links a:hover {
                    color: var(--primary);
                }
                
                .cart-icon {
                    position: relative;
                    cursor: pointer;
                    font-size: 1.3em;
                    color: #555;
                }
                
                .cart-badge {
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background: #dc3545;
                    color: white;
                    font-size: 0.7em;
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .cart-table {
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                }
                
                .cart-table th {
                    background: var(--gradient);
                    color: white;
                    border: none;
                    padding: 15px;
                }
                
                .cart-table td {
                    padding: 15px;
                    vertical-align: middle;
                }
                
                .quantity-input {
                    width: 70px;
                    text-align: center;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    padding: 5px;
                }
                
                .btn-update, .btn-remove {
                    padding: 5px 15px;
                    border-radius: 5px;
                    cursor: pointer;
                }
                
                .btn-update { background: var(--primary); color: white; border: none; }
                .btn-remove { background: #dc3545; color: white; border: none; }
                
                .cart-summary {
                    background: white;
                    border-radius: 15px;
                    padding: 25px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                }
                
                .footer {
                    background: var(--gradient);
                    color: white;
                    padding: 60px 0 20px;
                    margin-top: 60px;
                }
                
                .alert {
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="customer-nav">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="nav-links">
                            <a href="?page=home">Home</a>
                            <a href="?page=shop&subpage=products">Shop</a>
                            <?php if (isset($_SESSION['customer_id'])): ?>
                            <a href="?page=shop&subpage=orders">My Orders</a>
                            <a href="?page=shop&subpage=invoices">My Invoices</a>
                            <a href="?page=shop&subpage=profile">Profile</a>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (isset($_SESSION['customer_id'])): ?>
                            <span>Welcome, <?php echo $_SESSION['customer_name']; ?></span>
                            <a href="?logout=1" class="btn btn-outline-danger btn-sm">Logout</a>
                            <?php else: ?>
                            <a href="?page=login" class="btn btn-primary btn-sm">Login</a>
                            <a href="?page=register" class="btn btn-outline-primary btn-sm">Register</a>
                            <?php endif; ?>
                            <div class="cart-icon" onclick="window.location.href='?page=shop&subpage=cart'">
                                <i class="bi bi-cart"></i>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <h2 class="mb-4">Shopping Cart</h2>
                
                <?php if (empty($_SESSION['cart'])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart" style="font-size: 5em; color: #ccc;"></i>
                    <h3 class="mt-3">Your cart is empty</h3>
                    <p class="text-muted">Browse our products and add items to your cart</p>
                    <a href="?page=shop&subpage=products" class="btn btn-primary mt-3">Continue Shopping</a>
                </div>
                <?php else: ?>
                <div class="row">
                    <div class="col-md-8">
                        <table class="table cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </thead>
                            <tbody>
                                <?php foreach ($_SESSION['cart'] as $item): ?>
                                <tr>
                                    <td><?php echo $item['name']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_cart">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" max="10" class="quantity-input" onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="remove_from_cart">
                                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn-remove">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="cart-summary">
                            <h4>Order Summary</h4>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal:</span>
                                <strong>$<?php echo number_format($cart_total, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax (<?php echo floatval($settings['tax_rate'] ?? 5); ?>%):</span>
                                <strong>$<?php echo number_format($cart_total * (floatval($settings['tax_rate'] ?? 5) / 100), 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Delivery Fee:</span>
                                <strong>$<?php echo number_format(floatval($settings['delivery_fee'] ?? 2.00), 2); ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span>Total:</span>
                                <strong class="text-primary">$<?php echo number_format($cart_total * (1 + floatval($settings['tax_rate'] ?? 5) / 100) + floatval($settings['delivery_fee'] ?? 2.00), 2); ?></strong>
                            </div>
                            <a href="?page=shop&subpage=checkout" class="btn btn-primary w-100">Proceed to Checkout</a>
                            <a href="?page=shop&subpage=products" class="btn btn-outline-secondary w-100 mt-2">Continue Shopping</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <footer class="footer">
                <div class="container text-center">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
    // Checkout Page
    elseif ($subpage == 'checkout') {
        if (!isset($_SESSION['customer_id'])) {
            header('Location: ?page=login');
            exit;
        }
        
        $cart_total = 0;
        foreach ($_SESSION['cart'] ?? [] as $item) {
            $cart_total += $item['price'] * $item['quantity'];
        }
        $tax_rate = floatval($settings['tax_rate'] ?? 5) / 100;
        $tax = $cart_total * $tax_rate;
        $delivery_fee = floatval($settings['delivery_fee'] ?? 2.00);
        $grand_total = $cart_total + $tax + $delivery_fee;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Checkout - <?php echo COMPANY_NAME; ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
            <style>
                :root {
                    --primary: #007bff;
                    --secondary: #0056b3;
                    --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                }
                body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
                .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
                .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
                
                .customer-nav {
                    background: white;
                    padding: 15px 0;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    margin-bottom: 30px;
                }
                
                .customer-nav .nav-links {
                    display: flex;
                    align-items: center;
                    gap: 30px;
                }
                
                .customer-nav .nav-links a {
                    color: #555;
                    text-decoration: none;
                    font-weight: 500;
                }
                
                .checkout-form {
                    background: white;
                    border-radius: 15px;
                    padding: 30px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                }
                
                .order-summary {
                    background: white;
                    border-radius: 15px;
                    padding: 30px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                }
                
                .footer {
                    background: var(--gradient);
                    color: white;
                    padding: 60px 0 20px;
                    margin-top: 60px;
                }
                
                .btn-primary { cursor: pointer; }
                
                .alert {
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="customer-nav">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="nav-links">
                            <a href="?page=home">Home</a>
                            <a href="?page=shop&subpage=products">Shop</a>
                            <a href="?page=shop&subpage=orders">My Orders</a>
                            <a href="?page=shop&subpage=invoices">My Invoices</a>
                            <a href="?page=shop&subpage=profile">Profile</a>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span>Welcome, <?php echo $_SESSION['customer_name']; ?></span>
                            <a href="?logout=1" class="btn btn-outline-danger btn-sm">Logout</a>
                            <div class="cart-icon" onclick="window.location.href='?page=shop&subpage=cart'">
                                <i class="bi bi-cart"></i>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <h2 class="mb-4">Checkout</h2>
                
                <?php if (empty($_SESSION['cart'])): ?>
                <div class="alert alert-warning">
                    Your cart is empty. <a href="?page=shop&subpage=products">Continue shopping</a>.
                </div>
                <?php else: ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="checkout-form">
                            <h4>Delivery Information</h4>
                            <form method="POST" id="checkoutForm">
                                <input type="hidden" name="action" value="place_order">
                                <input type="hidden" name="total_amount" value="<?php echo $grand_total; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" value="<?php echo $customer_data['name']; ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="delivery_phone" class="form-control" value="<?php echo $customer_data['phone']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Delivery Address</label>
                                        <input type="text" name="delivery_address" class="form-control" value="<?php echo $customer_data['address']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">City</label>
                                        <input type="text" name="delivery_city" class="form-control" value="<?php echo $customer_data['city']; ?>" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Delivery Instructions (Optional)</label>
                                        <textarea name="delivery_instructions" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Payment Method</label>
                                        <select name="payment_method" class="form-control" required>
                                            <option value="cash">Cash on Delivery</option>
                                            <option value="mobile_money">Mobile Money</option>
                                            <option value="bank">Bank Transfer</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="order-summary">
                            <h4>Order Summary</h4>
                            <table class="table">
                                <?php foreach ($_SESSION['cart'] ?? [] as $item): ?>
                                <tr>
                                    <td><?php echo $item['name']; ?> x<?php echo $item['quantity']; ?></td>
                                    <td class="text-end">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end">$<?php echo number_format($cart_total, 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Tax (5%)</td>
                                    <td class="text-end">$<?php echo number_format($tax, 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Delivery Fee</td>
                                    <td class="text-end">$<?php echo number_format($delivery_fee, 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end">$<?php echo number_format($grand_total, 2); ?></th>
                                </tr>
                            </table>
                            
                            <button type="submit" form="checkoutForm" class="btn btn-primary w-100">
                                Place Order
                            </button>
                            
                            <p class="text-muted small mt-3">
                                <i class="bi bi-truck"></i> Free delivery within city<br>
                                <i class="bi bi-clock"></i> Delivery within 24 hours
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <footer class="footer">
                <div class="container text-center">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
    // Orders Page
    elseif ($subpage == 'orders') {
        if (!isset($_SESSION['customer_id'])) {
            header('Location: ?page=login');
            exit;
        }
        
        $customer_orders = $pdo->prepare("
            SELECT o.*, d.status as delivery_status, i.invoice_number 
            FROM orders o 
            LEFT JOIN deliveries d ON o.id = d.order_id 
            LEFT JOIN invoices i ON o.id = i.order_id
            WHERE o.customer_id = ? 
            ORDER BY o.order_date DESC
        ");
        $customer_orders->execute([$_SESSION['customer_id']]);
        $orders_list = $customer_orders->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>My Orders - <?php echo COMPANY_NAME; ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
            <style>
                :root {
                    --primary: #007bff;
                    --secondary: #0056b3;
                    --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                }
                body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
                .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
                .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
                
                .customer-nav {
                    background: white;
                    padding: 15px 0;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    margin-bottom: 30px;
                }
                
                .customer-nav .nav-links {
                    display: flex;
                    align-items: center;
                    gap: 30px;
                }
                
                .customer-nav .nav-links a {
                    color: #555;
                    text-decoration: none;
                    font-weight: 500;
                }
                
                .customer-nav .nav-links a:hover {
                    color: var(--primary);
                }
                
                .orders-table {
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                }
                
                .orders-table th {
                    background: var(--gradient);
                    color: white;
                    border: none;
                    padding: 15px;
                }
                
                .orders-table td {
                    padding: 15px;
                }
                
                .order-confirmation {
                    background: white;
                    border-radius: 15px;
                    padding: 40px;
                    text-align: center;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                    margin-bottom: 30px;
                }
                
                .confirmation-icon {
                    font-size: 5em;
                    color: #28a745;
                    margin-bottom: 20px;
                }
                
                .order-number {
                    font-size: 1.5em;
                    background: #f8f9fa;
                    padding: 15px 30px;
                    border-radius: 50px;
                    display: inline-block;
                    margin: 20px 0;
                    font-family: monospace;
                }
                
                .cancel-btn {
                    background: #dc3545;
                    color: white;
                    border: none;
                    padding: 5px 15px;
                    border-radius: 5px;
                    cursor: pointer;
                }
                
                .cancel-btn:hover {
                    background: #c82333;
                }
                
                .cancellation-form {
                    display: inline-block;
                }
                
                .cancellation-reason {
                    min-width: 200px;
                    padding: 5px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    margin-right: 5px;
                }
                
                .footer {
                    background: var(--gradient);
                    color: white;
                    padding: 60px 0 20px;
                    margin-top: 60px;
                }
                
                .alert {
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="customer-nav">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="nav-links">
                            <a href="?page=home">Home</a>
                            <a href="?page=shop&subpage=products">Shop</a>
                            <a href="?page=shop&subpage=orders" class="active">My Orders</a>
                            <a href="?page=shop&subpage=invoices">My Invoices</a>
                            <a href="?page=shop&subpage=profile">Profile</a>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span>Welcome, <?php echo $_SESSION['customer_name']; ?></span>
                            <a href="?logout=1" class="btn btn-outline-danger btn-sm">Logout</a>
                            <div class="cart-icon" onclick="window.location.href='?page=shop&subpage=cart'">
                                <i class="bi bi-cart"></i>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <h2 class="mb-4">My Orders</h2>
                
                <?php if (isset($_SESSION['last_order'])): ?>
                <div class="order-confirmation">
                    <div class="confirmation-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <h3>Order Placed Successfully!</h3>
                    <p>Thank you for your order. We'll start processing it right away.</p>
                    <div class="order-number">Order #: <?php echo $_SESSION['last_order']; ?></div>
                    <p>An invoice has been generated and sent to your email.</p>
                    <?php unset($_SESSION['last_order']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (empty($orders_list)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-bag" style="font-size: 5em; color: #ccc;"></i>
                    <h3 class="mt-3">No orders yet</h3>
                    <p class="text-muted">Start shopping to place your first order</p>
                    <a href="?page=shop&subpage=products" class="btn btn-primary mt-3">Shop Now</a>
                </div>
                <?php else: ?>
                <table class="table orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Delivery Status</th>
                            <th>Invoice</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders_list as $order): ?>
                        <tr>
                            <td><?php echo $order['order_number']; ?></td>
                            <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <?php
                                $status_class = [
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'processing' => 'primary',
                                    'packed' => 'secondary',
                                    'shipped' => 'info',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    'cancellation_requested' => 'warning'
                                ];
                                $status = $order['status'];
                                $class = $status_class[$status] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $class; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($order['delivery_status']): ?>
                                <span class="badge bg-<?php echo $order['delivery_status'] == 'delivered' ? 'success' : 'info'; ?>">
                                    <?php echo ucfirst($order['delivery_status']); ?>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($order['invoice_number']): ?>
                                <a href="?page=shop&subpage=invoice&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-text"></i> View
                                </a>
                                <?php else: ?>
                                <span class="text-muted">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                <form method="POST" class="cancellation-form" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    <input type="hidden" name="action" value="cancel_order">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="text" name="cancellation_reason" class="cancellation-reason" placeholder="Reason (optional)">
                                    <button type="submit" class="cancel-btn">Cancel</button>
                                </form>
                                <?php elseif ($order['status'] == 'cancellation_requested'): ?>
                                <span class="text-warning">Cancellation requested</span>
                                <?php elseif ($order['status'] == 'cancelled'): ?>
                                <span class="text-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <footer class="footer">
                <div class="container text-center">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
    // Single Invoice Page
    elseif ($subpage == 'invoice') {
        if (!isset($_SESSION['customer_id'])) {
            header('Location: ?page=login');
            exit;
        }
        $order_id = $_GET['id'] ?? 0;
        $order_stmt = $pdo->prepare("
            SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE o.id = ? AND o.customer_id = ?
        ");
        $order_stmt->execute([$order_id, $_SESSION['customer_id']]);
        $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo "<div class='container mt-5'><div class='alert alert-danger'>Invoice not found.</div></div>";
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
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
                <style>
                    body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
                    .invoice-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); margin: 30px auto; max-width: 800px; }
                    .invoice-header { border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 20px; }
                    .company-info { margin-bottom: 30px; }
                    .invoice-details { margin-bottom: 30px; }
                    .invoice-items { margin-bottom: 30px; }
                    @media print { 
                        .no-print { display: none !important; } 
                        body { background: white; } 
                        .invoice-card { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="invoice-card">
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

                        <div class="row invoice-details">
                            <div class="col-6">
                                <h5>Bill To:</h5>
                                <p><strong><?php echo $order['customer_name']; ?></strong></p>
                                <p><?php echo $order['customer_address']; ?></p>
                                <p>Phone: <?php echo $order['customer_phone']; ?></p>
                            </div>
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

                        <div class="row justify-content-end">
                            <div class="col-6 col-md-4">
                                <table class="table table-borderless">
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

                        <div class="text-center mt-5 no-print">
                            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print Invoice</button>
                            <a href="?page=shop&subpage=orders" class="btn btn-secondary">Back to Orders</a>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
        }
        exit;
    }
    // Invoices Page
    elseif ($subpage == 'invoices') {
        if (!isset($_SESSION['customer_id'])) {
            header('Location: ?page=login');
            exit;
        }
        
        $customer_invoices = $pdo->prepare("
            SELECT i.*, o.order_number 
            FROM invoices i 
            JOIN orders o ON i.order_id = o.id 
            WHERE i.customer_id = ? 
            ORDER BY i.invoice_date DESC
        ");
        $customer_invoices->execute([$_SESSION['customer_id']]);
        $invoices_list = $customer_invoices->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>My Invoices - <?php echo COMPANY_NAME; ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
            <style>
                :root {
                    --primary: #007bff;
                    --secondary: #0056b3;
                    --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                }
                body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
                .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
                .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
                
                .customer-nav {
                    background: white;
                    padding: 15px 0;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    margin-bottom: 30px;
                }
                
                .customer-nav .nav-links {
                    display: flex;
                    align-items: center;
                    gap: 30px;
                }
                
                .customer-nav .nav-links a {
                    color: #555;
                    text-decoration: none;
                    font-weight: 500;
                }
                
                .customer-nav .nav-links a:hover {
                    color: var(--primary);
                }
                
                .invoices-table {
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                }
                
                .invoices-table th {
                    background: var(--gradient);
                    color: white;
                    border: none;
                    padding: 15px;
                }
                
                .invoices-table td {
                    padding: 15px;
                }
                
                .invoice-card {
                    background: white;
                    border-radius: 15px;
                    padding: 30px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                    margin-bottom: 30px;
                }
                
                .invoice-header {
                    border-bottom: 2px solid var(--primary);
                    padding-bottom: 20px;
                    margin-bottom: 20px;
                }
                
                .invoice-details {
                    margin-bottom: 30px;
                }
                
                .invoice-items {
                    margin-bottom: 30px;
                }
                
                .invoice-summary {
                    border-top: 2px solid #eee;
                    padding-top: 20px;
                }
                
                .footer {
                    background: var(--gradient);
                    color: white;
                    padding: 60px 0 20px;
                    margin-top: 60px;
                }
                
                .alert {
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="customer-nav">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="nav-links">
                            <a href="?page=home">Home</a>
                            <a href="?page=shop&subpage=products">Shop</a>
                            <a href="?page=shop&subpage=orders">My Orders</a>
                            <a href="?page=shop&subpage=invoices" class="active">My Invoices</a>
                            <a href="?page=shop&subpage=profile">Profile</a>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span>Welcome, <?php echo $_SESSION['customer_name']; ?></span>
                            <a href="?logout=1" class="btn btn-outline-danger btn-sm">Logout</a>
                            <div class="cart-icon" onclick="window.location.href='?page=shop&subpage=cart'">
                                <i class="bi bi-cart"></i>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <h2 class="mb-4">My Invoices</h2>
                
                <?php if (empty($invoices_list)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-file-text" style="font-size: 5em; color: #ccc;"></i>
                    <h3 class="mt-3">No invoices yet</h3>
                    <p class="text-muted">Invoices will appear here after you place orders</p>
                    <a href="?page=shop&subpage=products" class="btn btn-primary mt-3">Shop Now</a>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($invoices_list as $invoice): ?>
                    <div class="col-md-12">
                        <div class="invoice-card">
                            <div class="invoice-header">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <?php if (!empty($settings['company_logo'])): ?>
                                            <img src="<?php echo htmlspecialchars($settings['company_logo']); ?>" alt="<?php echo htmlspecialchars($settings['company_name'] ?? 'Company'); ?>" style="max-height:80px; max-width:240px;" />
                                        <?php endif; ?>
                                        <h3><?php echo htmlspecialchars($settings['company_name'] ?? COMPANY_NAME); ?></h3>
                                        <p><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></p>
                                        <p>Phone: <?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?></p>
                                        <p>Email: <?php echo htmlspecialchars($settings['company_email'] ?? ''); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4>About Us</h4>
                                        <p><?php echo htmlspecialchars($settings['company_description'] ?? 'We are committed to providing the finest camel milk products with exceptional quality and service. Our farm produces premium camel milk using traditional methods combined with modern technology to ensure freshness and nutritional value. We take pride in our ethical farming practices and dedication to customer satisfaction.'); ?></p>
                                        <p><strong>Visit us at our farm to experience the freshness firsthand!</strong></p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <h4>Invoice #<?php echo $invoice['invoice_number']; ?></h4>
                                        <p><strong>Order #:</strong> <?php echo $invoice['order_number']; ?></p>
                                        <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($invoice['invoice_date'])); ?></p>
                                        <p><strong>Due Date:</strong> <?php echo date('d M Y', strtotime($invoice['due_date'])); ?></p>
                                        <p>
                                            <strong>Status:</strong>
                                            <span class="badge bg-<?php echo $invoice['status'] == 'paid' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($invoice['status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="invoice-details">
                                <h5>Invoice Details</h5>
                                <table class="table">
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
                                </table>
                            </div>
                            
                            <div class="text-end">
                                <button class="btn btn-primary" onclick="window.print()">Print Invoice</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <footer class="footer">
                <div class="container text-center">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
    // Profile Page
    elseif ($subpage == 'profile') {
        if (!isset($_SESSION['customer_id'])) {
            header('Location: ?page=login');
            exit;
        }
        
        $total_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
        $total_orders->execute([$_SESSION['customer_id']]);
        $total_orders_count = $total_orders->fetchColumn();
        
        $delivered_orders = $pdo->prepare("SELECT COUNT(*) FROM deliveries d JOIN orders o ON d.order_id = o.id WHERE o.customer_id = ? AND d.status = 'delivered'");
        $delivered_orders->execute([$_SESSION['customer_id']]);
        $delivered_count = $delivered_orders->fetchColumn();
        
        $pending_orders = $pdo->prepare("SELECT COUNT(*) FROM deliveries d JOIN orders o ON d.order_id = o.id WHERE o.customer_id = ? AND d.status IN ('assigned', 'in_transit')");
        $pending_orders->execute([$_SESSION['customer_id']]);
        $pending_count = $pending_orders->fetchColumn();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>My Profile - <?php echo COMPANY_NAME; ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
            <style>
                :root {
                    --primary: #007bff;
                    --secondary: #0056b3;
                    --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                }
                body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
                .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
                .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
                
                .customer-nav {
                    background: white;
                    padding: 15px 0;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    margin-bottom: 30px;
                }
                
                .customer-nav .nav-links {
                    display: flex;
                    align-items: center;
                    gap: 30px;
                }
                
                .customer-nav .nav-links a {
                    color: #555;
                    text-decoration: none;
                    font-weight: 500;
                }
                
                .customer-nav .nav-links a:hover {
                    color: var(--primary);
                }
                
                .profile-container {
                    background: white;
                    border-radius: 15px;
                    padding: 30px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                }
                
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }
                
                .stat-card {
                    background: linear-gradient(135deg, #007bff10, #0056b310);
                    padding: 20px;
                    border-radius: 10px;
                    text-align: center;
                }
                
                .stat-icon {
                    font-size: 2.5em;
                    color: var(--primary);
                    margin-bottom: 10px;
                }
                
                .stat-number {
                    font-size: 2em;
                    font-weight: bold;
                    color: #333;
                }
                
                .stat-label {
                    color: #666;
                }
                
                .footer {
                    background: var(--gradient);
                    color: white;
                    padding: 60px 0 20px;
                    margin-top: 60px;
                }
                
                .btn-primary { cursor: pointer; }
                
                .alert {
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="customer-nav">
                <div class="container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="nav-links">
                            <a href="?page=home">Home</a>
                            <a href="?page=shop&subpage=products">Shop</a>
                            <a href="?page=shop&subpage=orders">My Orders</a>
                            <a href="?page=shop&subpage=invoices">My Invoices</a>
                            <a href="?page=shop&subpage=profile" class="active">Profile</a>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span>Welcome, <?php echo $_SESSION['customer_name']; ?></span>
                            <a href="?logout=1" class="btn btn-outline-danger btn-sm">Logout</a>
                            <div class="cart-icon" onclick="window.location.href='?page=shop&subpage=cart'">
                                <i class="bi bi-cart"></i>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <h2 class="mb-4">My Profile</h2>
                
                <div class="profile-container">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-bag"></i></div>
                            <div class="stat-number"><?php echo $total_orders_count; ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-truck"></i></div>
                            <div class="stat-number"><?php echo $delivered_count; ?></div>
                            <div class="stat-label">Delivered</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="bi bi-clock"></i></div>
                            <div class="stat-number"><?php echo $pending_count; ?></div>
                            <div class="stat-label">In Progress</div>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $customer_data['name']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $customer_data['phone']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo $customer_data['email']; ?>" readonly>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo $customer_data['city']; ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo $customer_data['address']; ?></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>

            <footer class="footer">
                <div class="container text-center">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
    exit;
}