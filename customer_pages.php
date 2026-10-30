<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Customer Pages - Home, Products, About, Contact

// Home Page
if ($current_page == 'home') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo COMPANY_NAME; ?> - Premium Camel Milk Products</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
        <style>
            :root {
                --primary: #007bff;
                --primary-dark: #0056b3;
                --primary-light: #4dabf7;
                --secondary: #0056b3;
                --accent: #f687b3;
                --success: #48bb78;
                --warning: #ecc94b;
                --danger: #f56565;
                --light: #f7fafc;
                --dark: #2d3748;
                --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                color: var(--dark);
                overflow-x: hidden;
            }
            
            .navbar {
                background: white;
                box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1);
                padding: 15px 0;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
                font-weight: bold;
                background: var(--gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            
            .nav-link {
                color: var(--dark) !important;
                font-weight: 500;
                margin: 0 10px;
            }
            
            .nav-link:hover {
                color: var(--primary) !important;
            }
            
            .btn-primary {
                background: var(--gradient);
                border: none;
                padding: 10px 25px;
                border-radius: 50px;
                font-weight: 600;
                cursor: pointer;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            }
            
            .btn-outline-primary {
                border: 2px solid var(--primary);
                color: var(--primary);
                border-radius: 50px;
                padding: 8px 25px;
                font-weight: 600;
                cursor: pointer;
            }
            
            .btn-outline-primary:hover {
                background: var(--gradient);
                color: white;
                border-color: transparent;
            }
            
            .hero-section {
                background: linear-gradient(135deg, #007bff10 0%, #0056b310 100%);
                padding: 80px 0;
                min-height: 600px;
                display: flex;
                align-items: center;
            }
            
            .hero-title {
                font-size: 3.5rem;
                font-weight: 800;
                background: var(--gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                margin-bottom: 20px;
            }
            
            .hero-text {
                font-size: 1.2rem;
                color: #666;
                margin-bottom: 30px;
            }
            
            .feature-card {
                background: white;
                border-radius: 15px;
                padding: 30px;
                text-align: center;
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
                transition: all 0.3s;
                height: 100%;
            }
            
            .feature-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 15px 30px rgba(102, 126, 234, 0.2);
            }
            
            .feature-icon {
                font-size: 3rem;
                background: var(--gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                margin-bottom: 20px;
            }
            
            .product-card {
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
                transition: all 0.3s;
                height: 100%;
                position: relative;
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
                border-radius: 50px;
                font-size: 0.8rem;
                font-weight: 600;
                z-index: 2;
            }
            
            .product-image {
                height: 250px;
                background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .product-image img {
                max-width: 100%;
                max-height: 200px;
                object-fit: contain;
            }
            
            .product-info {
                padding: 20px;
                text-align: center;
            }
            
            .product-name {
                font-size: 1.2rem;
                font-weight: 600;
                color: var(--dark);
                margin-bottom: 10px;
            }
            
            .product-price {
                font-size: 1.5rem;
                font-weight: bold;
                color: var(--primary);
                margin-bottom: 15px;
            }
            
            .footer {
                background: var(--gradient);
                color: white;
                padding: 60px 0 20px;
            }
            
            .footer a {
                color: rgba(255,255,255,0.9);
                text-decoration: none;
            }
            
            .footer a:hover {
                color: white;
                text-decoration: underline;
            }
            
            .cart-icon {
                position: relative;
                cursor: pointer;
                font-size: 1.3rem;
                color: var(--primary);
                margin-left: 20px;
            }
            
            .cart-badge {
                position: absolute;
                top: -8px;
                right: -8px;
                background: var(--danger);
                color: white;
                font-size: 0.7rem;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-20px); }
            }
            
            .float-animation {
                animation: float 3s ease-in-out infinite;
            }
            
            .login-help {
                background: #e3f2fd;
                border-left: 4px solid var(--primary);
                padding: 10px 15px;
                margin: 10px 0;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
            <div class="container">
                <a class="navbar-brand" href="?page=home">
                    <i class="bi bi-cup-straw"></i> <?php echo COMPANY_NAME; ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="?page=home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=products">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=shop&subpage=products">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=contact">Contact</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <?php if ($logged_in): ?>
                            <?php if ($current_role == 'customer'): ?>
                                <a href="?page=shop&subpage=orders" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-bag"></i> My Orders
                                </a>
                                <div class="cart-icon" onclick="window.location.href='?page=shop&subpage=cart'">
                                    <i class="bi bi-cart"></i>
                                    <span class="cart-badge"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                                </div>
                                <div class="dropdown ms-3">
                                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['customer_name'] ?? 'User'; ?>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?page=shop&subpage=profile">Profile</a></li>
                                        <li><a class="dropdown-item" href="?page=shop&subpage=orders">My Orders</a></li>
                                        <li><a class="dropdown-item" href="?page=shop&subpage=invoices">My Invoices</a></li>
                                        <li><a class="dropdown-item" href="?page=support">Support Tickets</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="?logout=1">Logout</a></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <a href="?page=dashboard" class="btn btn-primary me-2">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                                <a href="?logout=1" class="btn btn-outline-danger">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="?page=login" class="btn btn-outline-primary me-2">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                            <a href="?page=register" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1 class="hero-title">
                            Fresh Camel Milk<br>Delivered to Your Door
                        </h1>
                        <p class="hero-text">
                            Experience the purest camel milk from our family farm. 
                            Rich in nutrients, easy to digest, and delivered fresh daily.
                        </p>
                        <div class="d-flex gap-3">
                            <a href="?page=shop&subpage=products" class="btn btn-primary btn-lg">
                                <i class="bi bi-shop"></i> Shop Now
                            </a>
                            <a href="?page=about" class="btn btn-outline-primary btn-lg">
                                Learn More
                            </a>
                        </div>
                        <div class="mt-4">
                            <div class="d-flex align-items-center gap-4">
                                <div>
                                    <i class="bi bi-truck text-primary fs-4"></i>
                                    <span class="ms-2">Free Delivery</span>
                                </div>
                                <div>
                                    <i class="bi bi-droplet text-primary fs-4"></i>
                                    <span class="ms-2">100% Natural</span>
                                </div>
                                <div>
                                    <i class="bi bi-clock text-primary fs-4"></i>
                                    <span class="ms-2">24/7 Service</span>
                                </div>
                            </div>
                        </div>
                        

                    </div>
                    <div class="col-lg-6">
                        <div class="text-center float-animation">
                            <img src="assets/images/camel-milk.jpg"
                                 alt="Camel Milk" class="img-fluid rounded-4 shadow-lg" style="max-height: 400px;"
                                 onerror="this.src='https://via.placeholder.com/400?text=Camel+Milk'">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-5">
            <div class="container">
                <h2 class="text-center mb-5" style="background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">
                    Why Choose Us?
                </h2>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="bi bi-droplet"></i></div>
                            <h4>100% Natural</h4>
                            <p class="text-muted">No additives, preservatives, or hormones. Pure camel milk straight from our farm.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="bi bi-truck"></i></div>
                            <h4>Daily Delivery</h4>
                            <p class="text-muted">Fresh milk delivered to your doorstep every morning before 7 AM.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="bi bi-heart"></i></div>
                            <h4>Rich in Nutrients</h4>
                            <p class="text-muted">Packed with vitamins, minerals, and immune-boosting properties.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="bi bi-tree"></i></div>
                            <h4>Ethical Farming</h4>
                            <p class="text-muted">Happy camels, better milk. Our camels are free-range and well-cared for.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-5 bg-white">
            <div class="container">
                <h2 class="text-center mb-5" style="background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">
                    Our Best Sellers
                </h2>
                <div class="row g-4">
                    <?php 
                    $featured_products = $pdo->query("SELECT * FROM products WHERE status='active' LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($featured_products as $product): 
                    ?>
                    <div class="col-md-3">
                        <div class="product-card">
                            <div class="product-badge"><?php echo $product['category']; ?></div>
                            <div class="product-image">
                                <img src="assets/images/<?php echo $product['image'] ?: 'default-product.jpg'; ?>" 
                                     alt="<?php echo $product['name']; ?>"
                                     onerror="this.src='assets/images/camel-milk.jpg'">
                            </div>
                            <div class="product-info">
                                <h5 class="product-name"><?php echo $product['name']; ?></h5>
                                <div class="product-price">
                                    $<?php echo number_format($product['price'], 2); ?> 
                                    <small style="color: #999; font-size: 0.8rem;">/<?php echo $product['unit']; ?></small>
                                </div>
                                <a href="?page=shop&subpage=products" class="btn btn-primary w-100">
                                    <i class="bi bi-cart-plus"></i> Shop Now
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-5">
                    <a href="?page=shop&subpage=products" class="btn btn-outline-primary btn-lg">
                        View All Products <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-5">
            <div class="container">
                <h2 class="text-center mb-5" style="background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">
                    What Our Customers Say
                </h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="testimonial-card p-4 bg-white rounded-4 shadow-sm">
                            <i class="bi bi-quote fs-1 text-primary opacity-25"></i>
                            <p class="mb-3">"I've been drinking camel milk for 6 months now. My digestion has improved significantly! The delivery is always on time."</p>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-circle fs-2 text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-0">Ahmed Hassan</h6>
                                    <small class="text-muted">Regular Customer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="testimonial-card p-4 bg-white rounded-4 shadow-sm">
                            <i class="bi bi-quote fs-1 text-primary opacity-25"></i>
                            <p class="mb-3">"The milk is so fresh and creamy. My whole family loves it. Best camel milk in town!"</p>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-circle fs-2 text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-0">Fatima Ali</h6>
                                    <small class="text-muted">Subscriber since 2023</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="testimonial-card p-4 bg-white rounded-4 shadow-sm">
                            <i class="bi bi-quote fs-1 text-primary opacity-25"></i>
                            <p class="mb-3">"Great customer service and reasonable prices. The subscription plan saves us money!"</p>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-circle fs-2 text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-0">Omar Ibrahim</h6>
                                    <small class="text-muted">Family Customer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <h5><i class="bi bi-cup-straw"></i> <?php echo COMPANY_NAME; ?></h5>
                        <p class="text-white-50">Providing the finest camel milk products since 2010. Fresh, natural, and delivered with care.</p>
                    </div>
                    <div class="col-md-2">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="?page=home">Home</a></li>
                            <li><a href="?page=products">Products</a></li>
                            <li><a href="?page=shop&subpage=products">Shop</a></li>
                            <li><a href="?page=about">About Us</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h5>Contact Info</h5>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-geo-alt"></i> Farm Road, Hargiesa</li>
                            <li><i class="bi bi-telephone"></i> +252 632345678</li>
                            <li><i class="bi bi-envelope"></i> info@mahuraan.com</li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h5>Follow Us</h5>
                        <div class="d-flex gap-3">
                            <a href="#"><i class="bi bi-facebook fs-4"></i></a>
                            <a href="#"><i class="bi bi-instagram fs-4"></i></a>
                            <a href="#"><i class="bi bi-twitter fs-4"></i></a>
                            <a href="#"><i class="bi bi-whatsapp fs-4"></i></a>
                        </div>
                    </div>
                </div>
                <hr class="mt-4 bg-white opacity-25">
                <div class="text-center text-white-50">
                    <small>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</small>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleSidebar() {}
            function toggleSubMenu(element) {}
            function toggleCart() { window.location.href = '?page=shop&subpage=cart'; }
            function addToCart(productId, quantity) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="${productId}">
                    <input type="hidden" name="quantity" value="${quantity}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
            document.addEventListener('DOMContentLoaded', function() {
                var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
                var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                    return new bootstrap.Dropdown(dropdownToggleEl);
                });
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Products Page
elseif ($current_page == 'products') {
    $all_products = $pdo->query("SELECT * FROM products WHERE status='active' ORDER BY category")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $pdo->query("SELECT DISTINCT category FROM products WHERE status='active'")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Our Products - <?php echo COMPANY_NAME; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
        <style>
            :root {
                --primary: #007bff;
                --secondary: #0056b3;
                --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            }
            body { font-family: 'Segoe UI', sans-serif; }
            .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
            .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .page-header {
                background: var(--gradient);
                color: white;
                padding: 60px 0;
                margin-bottom: 40px;
            }
            .product-card {
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
                transition: all 0.3s;
                height: 100%;
                margin-bottom: 30px;
            }
            .product-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 15px 30px rgba(102, 126, 234, 0.2);
            }
            .product-image {
                height: 250px;
                background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .product-image img {
                max-width: 100%;
                max-height: 200px;
                object-fit: contain;
            }
            .product-info { padding: 20px; }
            .product-name { font-size: 1.2rem; font-weight: 600; margin-bottom: 10px; }
            .product-price { font-size: 1.3rem; font-weight: bold; color: var(--primary); margin-bottom: 15px; }
            .category-badge {
                display: inline-block;
                padding: 5px 15px;
                background: var(--gradient);
                color: white;
                border-radius: 50px;
                font-size: 0.8rem;
                margin-bottom: 10px;
            }
            .footer {
                background: var(--gradient);
                color: white;
                padding: 60px 0 20px;
                margin-top: 60px;
            }
            .btn-primary, .btn-outline-primary { cursor: pointer; }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <a class="navbar-brand" href="?page=home"><i class="bi bi-cup-straw"></i> <?php echo COMPANY_NAME; ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item"><a href="?page=home" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="?page=products" class="nav-link active">Products</a></li>
                        <li class="nav-item"><a href="?page=shop&subpage=products" class="nav-link">Shop</a></li>
                        <li class="nav-item"><a href="?page=about" class="nav-link">About</a></li>
                        <li class="nav-item"><a href="?page=contact" class="nav-link">Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="page-header">
            <div class="container text-center">
                <h1 class="display-4 fw-bold">Our Products</h1>
                <p class="lead">Premium camel milk and dairy products</p>
            </div>
        </div>

        <div class="container mb-5">
            <!-- Category Filter -->
            <div class="text-center mb-5">
                <button class="btn btn-outline-primary category-filter active" data-category="all">All Products</button>
                <?php foreach ($categories as $cat): ?>
                <button class="btn btn-outline-primary category-filter" data-category="<?php echo $cat['category']; ?>">
                    <?php echo $cat['category']; ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Products Grid -->
            <div class="row" id="productsGrid">
                <?php foreach ($all_products as $product): ?>
                <div class="col-md-4 product-item" data-category="<?php echo $product['category']; ?>">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/<?php echo $product['image'] ?: 'default-product.jpg'; ?>" 
                                 alt="<?php echo $product['name']; ?>"
                                 onerror="this.src='assets/images/camel-milk.jpg'">
                        </div>
                        <div class="product-info">
                            <span class="category-badge"><?php echo $product['category']; ?></span>
                            <h3 class="product-name"><?php echo $product['name']; ?></h3>
                            <p class="text-muted"><?php echo $product['description'] ?? 'Fresh from our farm'; ?></p>
                            <div class="product-price">
                                $<?php echo number_format($product['price'], 2); ?> 
                                <small style="color: #999;">/<?php echo $product['unit']; ?></small>
                            </div>
                            <a href="?page=shop&subpage=products" class="btn btn-primary w-100">
                                <i class="bi bi-cart-plus"></i> Shop Now
                            </a>
                        </div>
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
        <script>
            document.querySelectorAll('.category-filter').forEach(button => {
                button.addEventListener('click', function() {
                    document.querySelectorAll('.category-filter').forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const category = this.dataset.category;
                    document.querySelectorAll('.product-item').forEach(item => {
                        if (category === 'all' || item.dataset.category === category) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
            function toggleSidebar() {}
            function toggleSubMenu(element) {}
            function toggleCart() { window.location.href = '?page=shop&subpage=cart'; }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Support Tickets Page
elseif ($current_page == 'support') {
    if (!$logged_in || $current_role != 'customer') {
        header('Location: ?page=login');
        exit;
    }
    
    $customer_id = $_SESSION['customer_id'] ?? null;
    $ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'create_ticket') {
                $subject = trim($_POST['subject']);
                $description = trim($_POST['description']);
                $priority = $_POST['priority'] ?? 'medium';
                $category = $_POST['category'] ?? 'general';
                
                if (!empty($subject) && !empty($description) && $customer_id) {
                    $ticket_id = createSupportTicket($pdo, $customer_id, $subject, $description, $priority, $category);
                    header("Location: ?page=support&ticket_id=$ticket_id");
                    exit;
                }
            }
            
            if ($_POST['action'] === 'add_reply' && $ticket_id) {
                $message = trim($_POST['message']);
                
                if (!empty($message)) {
                    addTicketReply($pdo, $ticket_id, $_SESSION['user_id'], $message, false);
                    header("Location: ?page=support&ticket_id=$ticket_id");
                    exit;
                }
            }
        }
    }
    
    // Get customer tickets
    $tickets = getCustomerTickets($pdo, $customer_id);
    
    // Get ticket details if viewing specific ticket
    $ticket_details = null;
    $ticket_replies = [];
    
    if ($ticket_id) {
        $ticket_details = getTicketById($pdo, $ticket_id);
        if ($ticket_details && $ticket_details['customer_id'] == $customer_id) {
            $ticket_replies = getTicketReplies($pdo, $ticket_id);
        } else {
            $ticket_id = 0;
            $ticket_details = null;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Support Tickets - <?php echo COMPANY_NAME; ?></title>
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
            .page-header {
                background: var(--gradient);
                color: white;
                padding: 60px 0;
                margin-bottom: 40px;
            }
            .ticket-card {
                background: white;
                border-radius: 15px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                transition: all 0.3s;
            }
            .ticket-card:hover {
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
            }
            .reply-bubble {
                background: #f8f9fa;
                border-radius: 15px;
                padding: 15px;
                margin-bottom: 15px;
            }
            .reply-bubble.admin {
                background: #e3f2fd;
                border-left: 4px solid var(--primary);
            }
            .reply-bubble.customer {
                background: #f1f8e9;
                border-left: 4px solid #48bb78;
            }
            .btn-primary { cursor: pointer; }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <a class="navbar-brand" href="?page=home"><i class="bi bi-cup-straw"></i> <?php echo COMPANY_NAME; ?></a>
                <div class="ms-auto">
                    <a href="?page=shop&subpage=orders" class="btn btn-outline-primary me-2">
                        <i class="bi bi-bag"></i> My Orders
                    </a>
                    <a href="?logout=1" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="page-header">
            <div class="container text-center">
                <h1 class="display-4 fw-bold">Support Tickets</h1>
                <p class="lead">Get help with your orders and account</p>
            </div>
        </div>

        <div class="container mb-5">
            <?php if ($ticket_id && $ticket_details): ?>
            <!-- View Ticket Details -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Ticket #<?php echo htmlspecialchars($ticket_details['ticket_number']); ?></h3>
                        <a href="?page=support" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Tickets
                        </a>
                    </div>
                    
                    <div class="ticket-card">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <h5><?php echo htmlspecialchars($ticket_details['subject']); ?></h5>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> <?php echo date('M d, Y H:i', strtotime($ticket_details['created_at'])); ?>
                                </small>
                            </div>
                            <div>
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
                            </div>
                        </div>
                        <p class="mb-4"><?php echo nl2br(htmlspecialchars($ticket_details['description'])); ?></p>
                        
                        <h6 class="mb-3">Conversation</h6>
                        
                        <?php foreach ($ticket_replies as $reply): ?>
                        <div class="reply-bubble <?php echo $reply['user_role'] === 'customer' ? 'customer' : 'admin'; ?>">
                            <div class="d-flex justify-content-between mb-2">
                                <strong><?php echo htmlspecialchars($reply['user_name'] ?? 'You'); ?></strong>
                                <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($reply['created_at'])); ?></small>
                            </div>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if ($ticket_details['status'] !== 'closed'): ?>
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="action" value="add_reply">
                            <div class="mb-3">
                                <label class="form-label">Add Reply</label>
                                <textarea name="message" class="form-control" rows="3" required placeholder="Type your message here..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Send Reply
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle"></i> This ticket is closed. No further replies can be added.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Ticket List & Create Form -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>My Support Tickets</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                            <i class="bi bi-plus-circle"></i> New Ticket
                        </button>
                    </div>
                    
                    <?php if (empty($tickets)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mb-0">You haven't submitted any support tickets yet.</p>
                        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                            Create Your First Ticket
                        </button>
                    </div>
                    <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5><?php echo htmlspecialchars($ticket['subject']); ?></h5>
                                <p class="text-muted mb-2">
                                    <small>
                                        <i class="bi bi-hash"></i> <?php echo htmlspecialchars($ticket['ticket_number']); ?>
                                        <span class="mx-2">|</span>
                                        <i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?>
                                    </small>
                                </p>
                                <p class="mb-0 text-truncate" style="max-width: 400px;">
                                    <?php echo htmlspecialchars(substr($ticket['description'], 0, 100)) . '...'; ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="mb-2">
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
                                </div>
                                <a href="?page=support&ticket_id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Create Ticket Modal -->
        <div class="modal fade" id="createTicketModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Support Ticket</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="create_ticket">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Subject *</label>
                                <input type="text" name="subject" class="form-control" required placeholder="Brief description of your issue">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="general">General</option>
                                        <option value="order">Order Issue</option>
                                        <option value="delivery">Delivery Problem</option>
                                        <option value="payment">Payment Issue</option>
                                        <option value="product">Product Quality</option>
                                        <option value="account">Account Issue</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea name="description" class="form-control" rows="5" required placeholder="Please provide detailed information about your issue..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Submit Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// About Page
elseif ($current_page == 'about') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>About Us - <?php echo COMPANY_NAME; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
        <style>
            :root {
                --primary: #007bff;
                --secondary: #0056b3;
                --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            }
            body { font-family: 'Segoe UI', sans-serif; }
            .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
            .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .page-header {
                background: var(--gradient);
                color: white;
                padding: 60px 0;
                margin-bottom: 40px;
            }
            .about-section { padding: 60px 0; }
            .value-card {
                background: white;
                border-radius: 15px;
                padding: 30px;
                text-align: center;
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
                height: 100%;
            }
            .value-icon { font-size: 3rem; color: var(--primary); margin-bottom: 20px; }
            .footer {
                background: var(--gradient);
                color: white;
                padding: 60px 0 20px;
                margin-top: 60px;
            }
            .btn-primary, .btn-outline-primary { cursor: pointer; }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <a class="navbar-brand" href="?page=home"><i class="bi bi-cup-straw"></i> <?php echo COMPANY_NAME; ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item"><a href="?page=home" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="?page=products" class="nav-link">Products</a></li>
                        <li class="nav-item"><a href="?page=shop&subpage=products" class="nav-link">Shop</a></li>
                        <li class="nav-item"><a href="?page=about" class="nav-link active">About</a></li>
                        <li class="nav-item"><a href="?page=contact" class="nav-link">Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="page-header">
            <div class="container text-center">
                <h1 class="display-4 fw-bold">About Us</h1>
                <p class="lead">Our story, our values, our commitment to quality</p>
            </div>
        </div>

        <div class="container">
            <div class="row about-section">
                <div class="col-md-6">
                    <h2 class="mb-4">Our Story</h2>
                    <p class="lead">Founded in 2026, Mahuraan Camel Farm has been dedicated to providing the highest quality camel milk and dairy products to our community.</p>
                    <p>What started as a small family farm with just a few camels has grown into a modern facility with over 100 camels, while maintaining our commitment to traditional, ethical farming practices. We believe that happy, well-cared-for camels produce the best milk.</p>
                    <p>Our mission is to bring the numerous health benefits of camel milk to families everywhere, delivered fresh to their doorstep with a smile.</p>
                </div>
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1566576919906-c8629e8a7d7f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Our Farm" class="img-fluid rounded-4 shadow">
                </div>
            </div>

            <div class="row about-section">
                <h2 class="text-center mb-5" style="background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Our Values
                </h2>
                <div class="col-md-4 mb-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="bi bi-heart"></i></div>
                        <h4>Animal Welfare</h4>
                        <p>Our camels are free-range, well-fed, and receive the best veterinary care. Happy camels = better milk.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="bi bi-droplet"></i></div>
                        <h4>Pure Quality</h4>
                        <p>No additives, no preservatives. Just pure, fresh camel milk straight from our farm to your table.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="bi bi-truck"></i></div>
                        <h4>Reliable Delivery</h4>
                        <p>Fresh milk delivered to your doorstep every morning. We pride ourselves on our punctuality.</p>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleSidebar() {}
            function toggleSubMenu(element) {}
            function toggleCart() { window.location.href = '?page=shop&subpage=cart'; }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Contact Page
elseif ($current_page == 'contact') {
    if (isset($_POST['send_message'])) {
        $contact_success = "Thank you for your message. We'll get back to you soon!";
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Contact Us - <?php echo COMPANY_NAME; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
        <style>
            :root {
                --primary: #007bff;
                --secondary: #0056b3;
                --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            }
            body { font-family: 'Segoe UI', sans-serif; }
            .navbar { background: white; box-shadow: 0 2px 15px rgba(102, 126, 234, 0.1); padding: 15px 0; }
            .navbar-brand { font-size: 1.5rem; font-weight: bold; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .page-header {
                background: var(--gradient);
                color: white;
                padding: 60px 0;
                margin-bottom: 40px;
            }
            .contact-info {
                background: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
                height: 100%;
            }
            .contact-icon {
                width: 50px;
                height: 50px;
                background: var(--gradient);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                margin-right: 15px;
            }
            .contact-form {
                background: white;
                border-radius: 15px;
                padding: 40px;
                box-shadow: 0 5px 20px rgba(102, 126, 234, 0.1);
            }
            .footer {
                background: var(--gradient);
                color: white;
                padding: 60px 0 20px;
                margin-top: 60px;
            }
            .btn-primary { cursor: pointer; }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <a class="navbar-brand" href="?page=home"><i class="bi bi-cup-straw"></i> <?php echo COMPANY_NAME; ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item"><a href="?page=home" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="?page=products" class="nav-link">Products</a></li>
                        <li class="nav-item"><a href="?page=shop&subpage=products" class="nav-link">Shop</a></li>
                        <li class="nav-item"><a href="?page=about" class="nav-link">About</a></li>
                        <li class="nav-item"><a href="?page=contact" class="nav-link active">Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="page-header">
            <div class="container text-center">
                <h1 class="display-4 fw-bold">Contact Us</h1>
                <p class="lead">Get in touch with us</p>
            </div>
        </div>

        <div class="container mb-5">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="contact-info">
                        <div class="d-flex align-items-center mb-4">
                            <div class="contact-icon"><i class="bi bi-geo-alt"></i></div>
                            <div>
                                <h5 class="mb-0">Visit Us</h5>
                                <p class="text-muted mb-0">Farm Road, Hargiesa</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-4">
                            <div class="contact-icon"><i class="bi bi-telephone"></i></div>
                            <div>
                                <h5 class="mb-0">Call Us</h5>
                                <p class="text-muted mb-0">+252 61 234 5678</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-4">
                            <div class="contact-icon"><i class="bi bi-envelope"></i></div>
                            <div>
                                <h5 class="mb-0">Email Us</h5>
                                <p class="text-muted mb-0">info@mahuran.com</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="contact-icon"><i class="bi bi-clock"></i></div>
                            <div>
                                <h5 class="mb-0">Working Hours</h5>
                                <p class="text-muted mb-0">Mon-Sat: 8AM - 8PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8 mb-4">
                    <div class="contact-form">
                        <h3 class="mb-4">Send us a Message</h3>
                        <?php if (isset($contact_success)): ?>
                        <div class="alert alert-success"><?php echo $contact_success; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" name="subject" class="form-control" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="send_message" class="btn btn-primary">
                                        Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="container-fluid p-0">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123961.42875151527!2d44.0205244!3d9.562389!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x16be024ff011f07f%3A0x95c73d9ec9a5cf42!2sHargeisa!5e0!3m2!1sen!2sso!4v1680000000000!5m2!1sen!2sso" 
                width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>

        <footer class="footer">
            <div class="container text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleSidebar() {}
            function toggleSubMenu(element) {}
            function toggleCart() { window.location.href = '?page=shop&subpage=cart'; }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Login Page
elseif ($current_page == 'login') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo SYSTEM_NAME; ?> - Login</title>
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
            .login-container { max-width: 400px; width: 90%; }
            .card { border: none; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
            .card-header {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                color: white; text-align: center; padding: 30px; border: none;
            }
            .card-body { padding: 40px; }
            .form-control {
                height: 50px; border-radius: 10px; border: 1px solid #e0e0e0;
                padding-left: 45px; margin-bottom: 20px;
            }
            .input-group { position: relative; }
            .input-icon {
                position: absolute; left: 15px; top: 16px; color: #007bff; z-index: 10;
            }
            .btn-login {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                border: none; height: 50px; border-radius: 10px; color: white;
                font-weight: bold; width: 100%; margin-top: 10px;
                cursor: pointer;
            }
            .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102,126,234,0.4); }
            .register-link { text-align: center; margin-top: 20px; }
            .register-link a { color: #007bff; text-decoration: none; }
            .alert { border-radius: 10px; margin-bottom: 20px; }
            .nav-pills .nav-link {
                color: var(--dark) !important;
                border-radius: 10px;
                border: 1px solid #e0e0e0;
                margin: 0;
                transition: all 0.3s;
                font-weight: 600;
            }
            .nav-pills .nav-link.active {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                color: white !important;
                border-color: transparent;
            }
            .nav-item {
                flex: 1;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h2>🐪 <?php echo SYSTEM_NAME; ?></h2>
                    <p>Please login to continue</p>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <ul class="nav nav-pills mb-4" id="loginTabs">
                        <li class="nav-item me-2">
                            <button type="button" class="nav-link active w-100" id="btn-customer" onclick="switchTab('customer')">Customer Login</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link w-100" id="btn-staff" onclick="switchTab('staff')">Staff Login</button>
                        </li>
                    </ul>
                    
                    <div id="form-customer">
                        <form method="POST">
                            <div class="input-group">
                                <span class="input-icon"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                            </div>
                            <div class="input-group">
                                <span class="input-icon"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                            </div>
                            <button type="submit" name="login_customer" class="btn-login">Customer Login</button>
                        </form>
                    </div>
                    
                    <div id="form-staff" style="display: none;">
                        <form method="POST">
                            <div class="input-group">
                                <span class="input-icon"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Username or Email" required>
                            </div>
                            <div class="input-group">
                                <span class="input-icon"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <button type="submit" name="login" class="btn-login">Staff Login</button>
                        </form>
                    </div>

                    <script>
                        function switchTab(type) {
                            var btnCust = document.getElementById('btn-customer');
                            var btnStaff = document.getElementById('btn-staff');
                            var formCust = document.getElementById('form-customer');
                            var formStaff = document.getElementById('form-staff');
                            
                            if (type === 'customer') {
                                btnCust.classList.add('active');
                                btnStaff.classList.remove('active');
                                formCust.style.display = 'block';
                                formStaff.style.display = 'none';
                            } else {
                                btnCust.classList.remove('active');
                                btnStaff.classList.add('active');
                                formCust.style.display = 'none';
                                formStaff.style.display = 'block';
                            }
                        }
                    </script>
                    
                    <div class="register-link mt-4">
                        New customer? <a href="?page=register">Create an account</a>
                    </div>
                    <div class="register-link mt-2">
                        <a href="?page=home">← Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    </body>
    </html>
    <?php
    exit;
}

// Registration Page
elseif ($current_page == 'register') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo SYSTEM_NAME; ?> - Register</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', sans-serif;
                padding: 20px;
            }
            .register-container { max-width: 600px; width: 100%; }
            .card { border: none; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
            .card-header {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                color: white; text-align: center; padding: 30px; border: none;
            }
            .card-body { padding: 40px; }
            .form-control, .form-select {
                height: 50px; border-radius: 10px; border: 1px solid #e0e0e0;
            }
            .btn-register {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                border: none; height: 50px; border-radius: 10px; color: white;
                font-weight: bold; width: 100%; margin-top: 20px;
                cursor: pointer;
            }
            .btn-register:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102,126,234,0.4); }
            .login-link { text-align: center; margin-top: 20px; }
            .login-link a { color: #007bff; text-decoration: none; }
            .alert { border-radius: 10px; margin-bottom: 20px; }
            .form-label { font-weight: 500; color: #555; }
        </style>
    </head>
    <body>
        <div class="register-container">
            <div class="card">
                <div class="card-header">
                    <h2>🐪 <?php echo SYSTEM_NAME; ?></h2>
                    <p>Create your customer account</p>
                </div>
                <div class="card-body">
                    <?php if (isset($reg_error)): ?>
                    <div class="alert alert-danger"><?php echo $reg_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Delivery Address</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Enter your delivery address" required></textarea>
                            </div>
                        </div>
                        <button type="submit" name="register_customer" class="btn-register">
                            Register & Start Shopping
                        </button>
                    </form>
                    
                    <div class="login-link">
                        Already have an account? <a href="?page=login">Login here</a>
                    </div>
                    <div class="login-link mt-2">
                        <a href="?page=home">← Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}