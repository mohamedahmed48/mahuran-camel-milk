<?php
// MAHURAN CAMEL FARMING & MILK DELIVERY SYSTEM
// Authentication and Database Initialization File

// Create database and tables
function initializeDatabase($pdo) {
    try {
        $sql = "
        -- Users Table
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE DEFAULT NULL,
            full_name VARCHAR(200) NOT NULL,
            phone VARCHAR(20),
            role ENUM('super_admin', 'admin', 'manager', 'staff', 'vet', 'driver', 'customer') DEFAULT 'customer',
            branch_id INT DEFAULT NULL,
            status ENUM('active', 'blocked', 'pending') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL
        );

        -- Branches Table
        CREATE TABLE IF NOT EXISTS branches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            location VARCHAR(200),
            phone VARCHAR(20),
            email VARCHAR(100),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Login History Table
        CREATE TABLE IF NOT EXISTS login_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT,
            status ENUM('success', 'failed') DEFAULT 'success',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        -- Departments Table
        CREATE TABLE IF NOT EXISTS departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Staff Table
        CREATE TABLE IF NOT EXISTS staff (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            employee_id VARCHAR(50) UNIQUE,
            department_id INT,
            position VARCHAR(100),
            hire_date DATE,
            salary DECIMAL(10,2),
            phone VARCHAR(20),
            email VARCHAR(100),
            address TEXT,
            profile_picture VARCHAR(255) DEFAULT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
        );

        -- Attendance Table
        CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT,
            date DATE,
            check_in TIME,
            check_out TIME,
            status ENUM('present', 'absent', 'late') DEFAULT 'present',
            FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
        );

        -- Leave Requests Table
        CREATE TABLE IF NOT EXISTS leave_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT,
            leave_type VARCHAR(50),
            start_date DATE,
            end_date DATE,
            reason TEXT,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
        );

        -- Salary Payments Table
        CREATE TABLE IF NOT EXISTS salary_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT,
            month VARCHAR(20),
            year INT,
            amount DECIMAL(10,2),
            payment_date DATE,
            status ENUM('paid', 'pending') DEFAULT 'pending',
            FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
        );

        -- Camels Table
        CREATE TABLE IF NOT EXISTS camels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tag_id VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(100),
            breed VARCHAR(100),
            age INT,
            gender ENUM('male', 'female') NOT NULL,
            weight DECIMAL(10,2),
            farm_location VARCHAR(200),
            status ENUM('active', 'sold', 'deceased') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Camel Ownership Table
        CREATE TABLE IF NOT EXISTS camel_ownership (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            owner_name VARCHAR(200),
            owner_phone VARCHAR(20),
            owner_address TEXT,
            purchase_date DATE,
            purchase_price DECIMAL(10,2),
            sale_date DATE,
            sale_price DECIMAL(10,2),
            notes TEXT,
            FOREIGN KEY (camel_id) REFERENCES camels(id) ON DELETE CASCADE
        );

        -- Health Records Table
        CREATE TABLE IF NOT EXISTS health_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            record_date DATE,
            diagnosis TEXT,
            treatment TEXT,
            vet_name VARCHAR(100),
            next_visit DATE,
            FOREIGN KEY (camel_id) REFERENCES camels(id) ON DELETE CASCADE
        );

        -- Disease Records Table
        CREATE TABLE IF NOT EXISTS disease_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            disease_name VARCHAR(200),
            diagnosed_date DATE,
            severity ENUM('mild', 'moderate', 'severe'),
            treatment TEXT,
            recovery_date DATE,
            vet_name VARCHAR(100),
            FOREIGN KEY (camel_id) REFERENCES camels(id) ON DELETE CASCADE
        );

        -- Vaccinations Table
        CREATE TABLE IF NOT EXISTS vaccinations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            vaccine_name VARCHAR(200),
            vaccination_date DATE,
            next_due_date DATE,
            administered_by VARCHAR(100),
            FOREIGN KEY (camel_id) REFERENCES camels(id) ON DELETE CASCADE
        );

        -- Vet Visits Table
        CREATE TABLE IF NOT EXISTS vet_visits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            visit_date DATE,
            vet_name VARCHAR(100),
            purpose TEXT,
            findings TEXT,
            recommendations TEXT,
            follow_up_date DATE,
            FOREIGN KEY (camel_id) REFERENCES camels(id) ON DELETE CASCADE
        );

        -- Breeding Records Table
        CREATE TABLE IF NOT EXISTS breeding_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            female_camel_id INT,
            male_camel_id INT,
            breeding_date DATE,
            expected_delivery DATE,
            actual_delivery DATE,
            status VARCHAR(50),
            notes TEXT,
            FOREIGN KEY (female_camel_id) REFERENCES camels(id),
            FOREIGN KEY (male_camel_id) REFERENCES camels(id)
        );

        -- Pregnancy Tracking Table
        CREATE TABLE IF NOT EXISTS pregnancy_tracking (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            breeding_record_id INT,
            check_date DATE,
            trimester INT,
            health_status VARCHAR(100),
            notes TEXT,
            FOREIGN KEY (camel_id) REFERENCES camels(id),
            FOREIGN KEY (breeding_record_id) REFERENCES breeding_records(id)
        );

        -- Calves Table
        CREATE TABLE IF NOT EXISTS calves (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            mother_id INT,
            birth_date DATE,
            gender ENUM('male', 'female'),
            birth_weight DECIMAL(10,2),
            health_status VARCHAR(50),
            tag_id VARCHAR(50),
            notes TEXT,
            FOREIGN KEY (mother_id) REFERENCES camels(id)
        );

        -- Fertility Reports Table
        CREATE TABLE IF NOT EXISTS fertility_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            report_date DATE,
            fertility_score INT,
            notes TEXT,
            recommended_for_breeding BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (camel_id) REFERENCES camels(id)
        );

        -- Feed Types Table
        CREATE TABLE IF NOT EXISTS feed_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200),
            category VARCHAR(100),
            unit VARCHAR(50),
            price_per_unit DECIMAL(10,2)
        );

        -- Feed Consumption Table
        CREATE TABLE IF NOT EXISTS feed_consumption (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            feed_type_id INT,
            date DATE,
            quantity DECIMAL(10,2),
            FOREIGN KEY (camel_id) REFERENCES camels(id),
            FOREIGN KEY (feed_type_id) REFERENCES feed_types(id)
        );

        -- Feed Plans Table
        CREATE TABLE IF NOT EXISTS feed_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200),
            description TEXT,
            camel_type VARCHAR(100),
            daily_quantity DECIMAL(10,2),
            feed_type_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (feed_type_id) REFERENCES feed_types(id)
        );

        -- Feed Suppliers Table
        CREATE TABLE IF NOT EXISTS feed_suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200),
            contact_person VARCHAR(100),
            phone VARCHAR(20),
            email VARCHAR(100),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Feed Purchases Table
        CREATE TABLE IF NOT EXISTS feed_purchases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT,
            feed_type_id INT,
            purchase_date DATE,
            quantity DECIMAL(10,2),
            unit_price DECIMAL(10,2),
            total_amount DECIMAL(10,2),
            payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'unpaid',
            notes TEXT,
            FOREIGN KEY (supplier_id) REFERENCES feed_suppliers(id),
            FOREIGN KEY (feed_type_id) REFERENCES feed_types(id)
        );

        -- Milk Collection Table
        CREATE TABLE IF NOT EXISTS milk_collection (
            id INT AUTO_INCREMENT PRIMARY KEY,
            camel_id INT,
            collection_date DATE,
            shift ENUM('morning', 'afternoon', 'evening'),
            quantity_liters DECIMAL(10,2),
            quality_grade ENUM('A', 'B', 'C', 'rejected'),
            collector_id INT,
            FOREIGN KEY (camel_id) REFERENCES camels(id),
            FOREIGN KEY (collector_id) REFERENCES users(id)
        );

        -- Milk Batches Table
        CREATE TABLE IF NOT EXISTS milk_batches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            batch_number VARCHAR(50) UNIQUE,
            collection_date DATE,
            total_quantity DECIMAL(10,2),
            processing_status ENUM('pending', 'processed', 'rejected') DEFAULT 'pending',
            quality_approved BOOLEAN DEFAULT FALSE,
            pasteurized BOOLEAN DEFAULT FALSE,
            pasteurization_date DATETIME,
            pasteurized_by INT,
            expiry_date DATE,
            FOREIGN KEY (pasteurized_by) REFERENCES users(id)
        );

        -- Milk Processing Records Table
        CREATE TABLE IF NOT EXISTS milk_processing (
            id INT AUTO_INCREMENT PRIMARY KEY,
            batch_id INT,
            process_date DATETIME,
            process_type VARCHAR(50),
            quantity_processed DECIMAL(10,2),
            processed_by INT,
            notes TEXT,
            FOREIGN KEY (batch_id) REFERENCES milk_batches(id),
            FOREIGN KEY (processed_by) REFERENCES users(id)
        );

        -- Warehouse Inventory Table
        CREATE TABLE IF NOT EXISTS warehouse_inventory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_type VARCHAR(100),
            batch_id INT,
            quantity DECIMAL(10,2),
            unit VARCHAR(50),
            storage_location VARCHAR(100),
            temperature DECIMAL(5,2),
            received_date DATE,
            expiry_date DATE,
            status ENUM('in_stock', 'low_stock', 'expired', 'disposed') DEFAULT 'in_stock',
            FOREIGN KEY (batch_id) REFERENCES milk_batches(id)
        );

        -- Temperature Logs Table
        CREATE TABLE IF NOT EXISTS temperature_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            location VARCHAR(100),
            log_date DATETIME,
            temperature DECIMAL(5,2),
            recorded_by INT,
            notes TEXT,
            FOREIGN KEY (recorded_by) REFERENCES users(id)
        );

        -- Products Table
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200),
            category VARCHAR(100),
            price DECIMAL(10,2),
            stock_quantity DECIMAL(10,2),
            unit VARCHAR(50),
            status ENUM('active', 'inactive') DEFAULT 'active',
            image VARCHAR(255) DEFAULT 'default-product.jpg',
            description TEXT
        );

        -- Customers Table
        CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            name VARCHAR(200),
            phone VARCHAR(20),
            email VARCHAR(100),
            address TEXT,
            city VARCHAR(100),
            status ENUM('active', 'inactive') DEFAULT 'active',
            FOREIGN KEY (user_id) REFERENCES users(id)
        );

        -- Customer Addresses Table
        CREATE TABLE IF NOT EXISTS customer_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            address_type ENUM('home', 'work', 'other') DEFAULT 'home',
            address TEXT,
            city VARCHAR(100),
            is_default BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        );

        -- Support Tickets Table
        CREATE TABLE IF NOT EXISTS support_tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            ticket_number VARCHAR(50) UNIQUE,
            subject VARCHAR(200),
            message TEXT,
            status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            resolved_at DATETIME,
            assigned_to INT,
            FOREIGN KEY (customer_id) REFERENCES customers(id),
            FOREIGN KEY (assigned_to) REFERENCES users(id)
        );

        -- Ticket Replies Table
        CREATE TABLE IF NOT EXISTS ticket_replies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT,
            user_id INT,
            reply TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );

        -- Subscription Plans Table
        CREATE TABLE IF NOT EXISTS subscription_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200),
            plan_type ENUM('daily', 'weekly', 'monthly'),
            quantity_per_day DECIMAL(10,2),
            price DECIMAL(10,2),
            status ENUM('active', 'inactive') DEFAULT 'active'
        );

        -- Customer Subscriptions Table
        CREATE TABLE IF NOT EXISTS customer_subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            plan_id INT,
            start_date DATE,
            end_date DATE,
            status ENUM('active', 'paused', 'cancelled') DEFAULT 'active',
            delivery_address TEXT,
            delivery_time TIME,
            FOREIGN KEY (customer_id) REFERENCES customers(id),
            FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
        );

        -- Subscription Invoices Table
        CREATE TABLE IF NOT EXISTS subscription_invoices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subscription_id INT,
            invoice_number VARCHAR(50) UNIQUE,
            invoice_date DATE,
            amount DECIMAL(10,2),
            status ENUM('paid', 'unpaid', 'overdue') DEFAULT 'unpaid',
            payment_date DATE,
            FOREIGN KEY (subscription_id) REFERENCES customer_subscriptions(id) ON DELETE CASCADE
        );

        -- Orders Table
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) UNIQUE,
            customer_id INT,
            order_date DATETIME,
            total_amount DECIMAL(10,2),
            status ENUM('pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'cancellation_requested') DEFAULT 'pending',
            payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'unpaid',
            delivery_address TEXT,
            delivery_phone VARCHAR(20),
            delivery_instructions TEXT,
            cancellation_reason TEXT,
            cancelled_at DATETIME NULL,
            confirmed_at DATETIME NULL,
            processed_at DATETIME NULL,
            packed_at DATETIME NULL,
            shipped_at DATETIME NULL,
            delivered_at DATETIME NULL,
            FOREIGN KEY (customer_id) REFERENCES customers(id)
        );

        -- Order Items Table
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            product_id INT,
            quantity DECIMAL(10,2),
            price DECIMAL(10,2),
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id)
        );

        -- Invoices Table
        CREATE TABLE IF NOT EXISTS invoices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_number VARCHAR(50) UNIQUE,
            order_id INT,
            customer_id INT,
            invoice_date DATETIME,
            due_date DATE,
            subtotal DECIMAL(10,2),
            tax DECIMAL(10,2),
            delivery_fee DECIMAL(10,2),
            total_amount DECIMAL(10,2),
            status ENUM('paid', 'unpaid', 'overdue') DEFAULT 'unpaid',
            pdf_path VARCHAR(255),
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (customer_id) REFERENCES customers(id)
        );

        -- Deliveries Table
        CREATE TABLE IF NOT EXISTS deliveries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            driver_id INT,
            delivery_date DATE,
            status ENUM('assigned', 'in_transit', 'delivered', 'failed', 'cancelled') DEFAULT 'assigned',
            delivery_address TEXT,
            recipient_name VARCHAR(200),
            proof_of_delivery TEXT,
            signature_path VARCHAR(255),
            delivered_at DATETIME,
            accepted_at DATETIME,
            cancelled_at DATETIME,
            cancellation_reason TEXT,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (driver_id) REFERENCES users(id)
        );

        -- Delivery Proof Images Table
        CREATE TABLE IF NOT EXISTS delivery_proofs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            delivery_id INT,
            image_path VARCHAR(255),
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (delivery_id) REFERENCES deliveries(id) ON DELETE CASCADE
        );

        -- Driver Performance Table
        CREATE TABLE IF NOT EXISTS driver_performance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            driver_id INT,
            delivery_date DATE,
            deliveries_count INT,
            on_time_deliveries INT,
            rating DECIMAL(3,2),
            FOREIGN KEY (driver_id) REFERENCES users(id)
        );

        -- Payments Table
        CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            amount DECIMAL(10,2),
            payment_date DATETIME,
            payment_method ENUM('cash', 'mobile_money', 'bank'),
            status ENUM('completed', 'pending', 'failed') DEFAULT 'pending',
            transaction_id VARCHAR(100),
            FOREIGN KEY (order_id) REFERENCES orders(id)
        );

        -- Expenses Table
        CREATE TABLE IF NOT EXISTS expenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            expense_date DATE,
            category VARCHAR(100),
            description TEXT,
            amount DECIMAL(10,2),
            payment_method VARCHAR(50)
        );

        -- Notifications Table
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(200),
            message TEXT,
            type ENUM('info', 'success', 'warning', 'danger'),
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        -- Order Status History Table
        CREATE TABLE IF NOT EXISTS order_status_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            status VARCHAR(50),
            changed_by INT,
            changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (changed_by) REFERENCES users(id)
        );

        -- System Settings Table
        CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE,
            setting_value TEXT,
            description TEXT
        );

        -- Sales Targets Table
        CREATE TABLE IF NOT EXISTS sales_targets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            year INT,
            month INT,
            target_amount DECIMAL(10,2),
            achieved_amount DECIMAL(10,2) DEFAULT 0,
            staff_id INT,
            FOREIGN KEY (staff_id) REFERENCES staff(id)
        );

        -- Delivery Zones Table
        CREATE TABLE IF NOT EXISTS delivery_zones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            city VARCHAR(100),
            delivery_fee DECIMAL(10,2),
            estimated_time VARCHAR(50)
        );

        -- Roles Table for Role Management
        CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE,
            description TEXT,
            permissions JSON
        );

        -- Permissions Table
        CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE,
            description TEXT
        );
        
        -- Sales Reports Table
        CREATE TABLE IF NOT EXISTS sales_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_date DATE,
            total_sales DECIMAL(10,2),
            total_orders INT,
            average_order_value DECIMAL(10,2),
            top_product_id INT,
            report_data JSON,
            FOREIGN KEY (top_product_id) REFERENCES products(id)
        );
        
        -- Salary Deductions Table
        CREATE TABLE IF NOT EXISTS salary_deductions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT,
            month VARCHAR(20),
            year INT,
            deduction_type VARCHAR(100),
            amount DECIMAL(10,2),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
        );
        
        -- Salary Bonuses Table
        CREATE TABLE IF NOT EXISTS salary_bonuses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT,
            month VARCHAR(20),
            year INT,
            bonus_type VARCHAR(100),
            amount DECIMAL(10,2),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
        );
        
        -- Payslips Table
        CREATE TABLE IF NOT EXISTS payslips (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT,
            month VARCHAR(20),
            year INT,
            base_salary DECIMAL(10,2),
            total_deductions DECIMAL(10,2),
            total_bonuses DECIMAL(10,2),
            net_salary DECIMAL(10,2),
            generated_date DATETIME,
            pdf_path VARCHAR(255),
            FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
        );
        
        -- Sales Commissions Table
        CREATE TABLE IF NOT EXISTS sales_commissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT,
            order_id INT,
            commission_rate DECIMAL(5,2),
            commission_amount DECIMAL(10,2),
            status ENUM('pending', 'paid') DEFAULT 'pending',
            calculated_date DATETIME,
            paid_date DATETIME,
            FOREIGN KEY (staff_id) REFERENCES staff(id),
            FOREIGN KEY (order_id) REFERENCES orders(id)
        );
        
        -- Database Backups Table
        CREATE TABLE IF NOT EXISTS database_backups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            backup_name VARCHAR(200),
            backup_path VARCHAR(500),
            backup_size BIGINT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        );
        
        -- System Activity Logs Table
        CREATE TABLE IF NOT EXISTS system_activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100),
            description TEXT,
            ip_address VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );
        
        -- System Monitoring Table
        CREATE TABLE IF NOT EXISTS system_monitoring (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cpu_usage DECIMAL(5,2),
            memory_usage DECIMAL(5,2),
            disk_usage DECIMAL(5,2),
            active_users INT,
            logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Employee performance scores (monthly)
        CREATE TABLE IF NOT EXISTS employee_performance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT NOT NULL,
            branch_id INT,
            month INT NOT NULL COMMENT '1-12',
            year INT NOT NULL,
            attendance_score DECIMAL(5,2) DEFAULT 0 COMMENT 'Based on attendance percentage',
            punctuality_score DECIMAL(5,2) DEFAULT 0,
            tasks_completed INT DEFAULT 0,
            milk_collected_liters DECIMAL(10,2) DEFAULT 0,
            custom_score DECIMAL(5,2) DEFAULT 0 COMMENT 'Manual score set by manager',
            total_score DECIMAL(5,2) DEFAULT 0,
            notes TEXT,
            ranked_position INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_staff_month (staff_id, month, year)
        ) ENGINE=InnoDB;

        -- Employee awards/recognition
        CREATE TABLE IF NOT EXISTS employee_awards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            staff_id INT NOT NULL,
            branch_id INT,
            award_type VARCHAR(100) NOT NULL COMMENT 'employee_of_month, top_performer, etc.',
            award_title VARCHAR(200) NOT NULL,
            award_description TEXT,
            month INT,
            year INT,
            cash_bonus DECIMAL(10,2) DEFAULT 0,
            given_by INT COMMENT 'user_id who gave the award',
            award_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;

        -- Walk-in sales (POS specific - track walk-in customers)
        CREATE TABLE IF NOT EXISTS pos_walkin_sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sale_number VARCHAR(50) UNIQUE,
            branch_id INT,
            cashier_id INT COMMENT 'user_id of cashier',
            customer_name VARCHAR(200) DEFAULT 'Walk-in Customer',
            customer_phone VARCHAR(30) DEFAULT NULL,
            payment_method VARCHAR(50) DEFAULT 'cash',
            subtotal DECIMAL(10,2) DEFAULT 0,
            discount DECIMAL(10,2) DEFAULT 0,
            tax DECIMAL(10,2) DEFAULT 0,
            total_amount DECIMAL(10,2) DEFAULT 0,
            amount_paid DECIMAL(10,2) DEFAULT 0,
            change_amount DECIMAL(10,2) DEFAULT 0,
            sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT
        ) ENGINE=InnoDB;

        -- POS Walk-in items
        CREATE TABLE IF NOT EXISTS pos_walkin_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sale_id INT NOT NULL,
            product_id INT,
            product_name VARCHAR(200),
            quantity DECIMAL(10,2) DEFAULT 1,
            unit_price DECIMAL(10,2),
            total_price DECIMAL(10,2),
            FOREIGN KEY (sale_id) REFERENCES pos_walkin_sales(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";
        
        $pdo->exec($sql);
        
        // Add columns if not exist
        try {
            $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT 'default-product.jpg'");
            $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS description TEXT");
            $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_address TEXT");
            $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_phone VARCHAR(20)");
            $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_instructions TEXT");
            $pdo->exec("ALTER TABLE calves ADD COLUMN IF NOT EXISTS birth_weight DECIMAL(10,2)");
            $pdo->exec("ALTER TABLE calves ADD COLUMN IF NOT EXISTS tag_id VARCHAR(50)");
            $pdo->exec("ALTER TABLE calves ADD COLUMN IF NOT EXISTS notes TEXT");
            $pdo->exec("ALTER TABLE milk_batches ADD COLUMN IF NOT EXISTS pasteurized BOOLEAN DEFAULT FALSE");
            $pdo->exec("ALTER TABLE milk_batches ADD COLUMN IF NOT EXISTS pasteurization_date DATETIME");
            $pdo->exec("ALTER TABLE milk_batches ADD COLUMN IF NOT EXISTS pasteurized_by INT");
            $pdo->exec("ALTER TABLE milk_batches ADD COLUMN IF NOT EXISTS expiry_date DATE");
            $pdo->exec("ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS signature_path VARCHAR(255)");
            $pdo->exec("ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS delivered_at DATETIME");
            $pdo->exec("ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS accepted_at DATETIME");
            $pdo->exec("ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS cancelled_at DATETIME");
            $pdo->exec("ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS cancellation_reason TEXT");
            $pdo->exec("ALTER TABLE deliveries MODIFY COLUMN status ENUM('assigned', 'in_transit', 'delivered', 'failed', 'cancelled') DEFAULT 'assigned'");
            $pdo->exec("ALTER TABLE payments ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(100)");
            $pdo->exec("ALTER TABLE staff ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255)");
            
            // Add payment_method column to orders table (needed for POS sales)
            $pdo->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT NULL");
            
            // Update payments table to accept 'card' payment method
            try {
                $pdo->exec("ALTER TABLE payments MODIFY COLUMN payment_method VARCHAR(50) DEFAULT 'cash'");
            } catch (PDOException $e) {}
            
            // Add plain_password column if not exists
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN plain_password VARCHAR(255) DEFAULT NULL");
            } catch (PDOException $e) {}
            
            // Make email nullable in users table and update existing empty emails
            try {
                $pdo->exec("UPDATE users SET email = NULL WHERE email = ''");
                $pdo->exec("ALTER TABLE users MODIFY email VARCHAR(100) NULL");
            } catch (PDOException $e) {}
            
            // Branch ID Columns
            $tables_to_branch = [
                'users', 'staff', 'departments', 'attendance', 'leave_requests', 'salary_payments',
                'camels', 'health_records', 'disease_records', 'vaccinations', 'vet_visits', 
                'breeding_records', 'pregnancy_tracking', 'calves', 'fertility_reports',
                'feed_types', 'feed_consumption', 'feed_plans', 'feed_suppliers', 'feed_purchases',
                'milk_collection', 'milk_batches', 'milk_processing', 'warehouse_inventory',
                'products', 'orders', 'invoices', 'payments', 'expenses', 'delivery_zones',
                'customers', 'support_tickets', 'subscription_plans', 'sales_targets',
                'notifications', 'system_activity_logs', 'order_status_history', 'database_backups',
                'salary_deductions', 'salary_bonuses', 'payslips', 'sales_commissions',
                'employee_performance', 'employee_awards', 'pos_walkin_sales', 'pos_walkin_items'
            ];
            
            foreach ($tables_to_branch as $table) {
                try {
                    $pdo->exec("ALTER TABLE `$table` ADD COLUMN IF NOT EXISTS branch_id INT DEFAULT NULL");
                } catch (PDOException $e) {}
            }
        } catch (PDOException $e) {
            // Columns might already exist
        }
        
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Insert default data
function insertDefaultData($pdo) {
    try {
        // Check if branches exist
        $branch_count = $pdo->query("SELECT COUNT(*) FROM branches")->fetchColumn();
        if ($branch_count == 0) {
            $pdo->prepare("INSERT INTO branches (name, location, status) VALUES (?, ?, ?)")
                ->execute(['Main Branch', 'Hargeisa Headquarters', 'active']);
            $main_branch_id = $pdo->lastInsertId();
        } else {
            $main_branch_id = $pdo->query("SELECT id FROM branches LIMIT 1")->fetchColumn();
        }

        // Check if superadmin exists specifically
        $super_admin_exists = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'superadmin' OR role = 'super_admin'")->fetchColumn();
        
        if($super_admin_exists == 0) {
            // Create super admin user
            $super_password = password_hash('superadmin123', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, password, email, full_name, role, branch_id, plain_password) VALUES (?, ?, ?, ?, ?, NULL, ?)")
                ->execute(['superadmin', $super_password, 'superadmin@mahuran.com', 'Global Super Administrator', 'super_admin', 'superadmin123']);
        }


            
            // Create vet user
            $vet_password = password_hash('vet123', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, password, email, full_name, role, branch_id, plain_password) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute(['vet', $vet_password, 'vet@mahuran.com', 'Dr. Ahmed Hassan', 'vet', $main_branch_id, 'vet123']);
            
            // Create driver user
            $driver_password = password_hash('driver123', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, password, email, full_name, role, branch_id, plain_password) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute(['driver', $driver_password, 'driver@mahuran.com', 'Ali Omar', 'driver', $main_branch_id, 'driver123']);
            
            // Create staff user
            $staff_password = password_hash('staff123', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, password, email, full_name, role, branch_id, plain_password) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute(['staff', $staff_password, 'staff@mahuran.com', 'Farm Staff', 'staff', $main_branch_id, 'staff123']);

        // Ensure staff, vet, and driver users have branch_id set
        $roles_to_fix = ['staff', 'vet', 'driver'];
        foreach ($roles_to_fix as $role) {
            $pdo->prepare("UPDATE users SET branch_id = ? WHERE role = ? AND branch_id IS NULL")
                ->execute([$main_branch_id, $role]);
        }

        // Populate plain_password for existing default users if NULL
        $defaults = [
            'superadmin' => 'superadmin123',
            'vet' => 'vet123',
            'driver' => 'driver123',
            'staff' => 'staff123'
        ];
        foreach ($defaults as $u => $p) {
            $pdo->prepare("UPDATE users SET plain_password = ? WHERE username = ? AND (plain_password IS NULL OR plain_password = '')")
                ->execute([$p, $u]);
        }
            
            // Create departments
            $dept_count = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
            if ($dept_count == 0) {
                $departments = ['Management', 'Veterinary', 'Farm Operations', 'Processing', 'Delivery', 'Sales'];
                foreach($departments as $dept) {
                    $pdo->prepare("INSERT INTO departments (name, description, branch_id) VALUES (?, ?, ?)")
                        ->execute([$dept, $dept . ' Department', $main_branch_id]);
                }
            }
            
            // Create feed types
            $feed_count = $pdo->query("SELECT COUNT(*) FROM feed_types")->fetchColumn();
            if ($feed_count == 0) {
                $feeds = [
                    ['Hay', 'Forage', 'kg', 5.00],
                    ['Grain Mix', 'Concentrate', 'kg', 15.00],
                    ['Mineral Blocks', 'Supplement', 'piece', 25.00],
                    ['Fresh Fodder', 'Green Feed', 'kg', 3.00]
                ];
                foreach($feeds as $feed) {
                    $pdo->prepare("INSERT INTO feed_types (name, category, unit, price_per_unit, branch_id) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$feed[0], $feed[1], $feed[2], $feed[3], $main_branch_id]);
                }
            }
            
            // Create feed suppliers
            $supplier_count = $pdo->query("SELECT COUNT(*) FROM feed_suppliers")->fetchColumn();
            if ($supplier_count == 0) {
                $suppliers = [
                    ['Green Pastures Ltd', 'Ahmed Hassan', '+252612345678', 'ahmed@greenpastures.com', 'Hargeisa Road'],
                    ['Animal Feed Co', 'Fatima Ali', '+252612345679', 'info@animalfeed.com', 'Industrial Area'],
                    ['Organic Feeds', 'Omar Farah', '+252612345680', 'omar@organicfeeds.com', 'Farm Road']
                ];
                foreach($suppliers as $supplier) {
                    $pdo->prepare("INSERT INTO feed_suppliers (name, contact_person, phone, email, address, branch_id) VALUES (?, ?, ?, ?, ?, ?)")
                        ->execute([$supplier[0], $supplier[1], $supplier[2], $supplier[3], $supplier[4], $main_branch_id]);
                }
            }
            
            // Create feed plans
            $plan_count = $pdo->query("SELECT COUNT(*) FROM feed_plans")->fetchColumn();
            if ($plan_count == 0) {
                $feed_plans = [
                    ['Lactating Females', 'High nutrition for milk production', 'Female Lactating', 15.00, 1],
                    ['Adult Males', 'Standard maintenance', 'Adult Male', 10.00, 2],
                    ['Young Calves', 'Growth formula', 'Calf', 5.00, 4],
                    ['Pregnant Females', 'Prenatal nutrition', 'Female Pregnant', 12.00, 1]
                ];
                foreach($feed_plans as $plan) {
                    $pdo->prepare("INSERT INTO feed_plans (name, description, camel_type, daily_quantity, feed_type_id, branch_id) VALUES (?, ?, ?, ?, ?, ?)")
                        ->execute([$plan[0], $plan[1], $plan[2], $plan[3], $plan[4], $main_branch_id]);
                }
            }
            
            // Create subscription plans
            $sub_count = $pdo->query("SELECT COUNT(*) FROM subscription_plans")->fetchColumn();
            if ($sub_count == 0) {
                $plans = [
                    ['Daily Basic', 'daily', 1.00, 30.00, 'active'],
                    ['Daily Family', 'daily', 3.00, 80.00, 'active'],
                    ['Weekly Saver', 'weekly', 2.00, 50.00, 'active'],
                    ['Monthly Premium', 'monthly', 2.00, 55.00, 'active']
                ];
                foreach($plans as $plan) {
                    $pdo->prepare("INSERT INTO subscription_plans (name, plan_type, quantity_per_day, price, status, branch_id) VALUES (?, ?, ?, ?, ?, ?)")
                        ->execute([$plan[0], $plan[1], $plan[2], $plan[3], $plan[4], $main_branch_id]);
                }
            }
            
            // Create delivery zones
            $zone_count = $pdo->query("SELECT COUNT(*) FROM delivery_zones")->fetchColumn();
            if ($zone_count == 0) {
                $zones = [
                    ['Central Zone', 'Hargeisa', 2.00, '30-45 min'],
                    ['North Zone', 'Hargeisa', 3.00, '45-60 min'],
                    ['South Zone', 'Hargeisa', 3.50, '50-70 min'],
                    ['Suburbs', 'Berbera', 5.00, '90-120 min']
                ];
                foreach($zones as $zone) {
                    $pdo->prepare("INSERT INTO delivery_zones (name, city, delivery_fee, estimated_time, branch_id) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$zone[0], $zone[1], $zone[2], $zone[3], $main_branch_id]);
                }
            }
            
            // Create products with images
            $prod_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            if ($prod_count == 0) {
                $products = [
                    ['Fresh Camel Milk', 'Milk', 3.50, 100, 'liter', 'active', "default-product.jpg", 'Fresh raw camel milk straight from our farm. Rich in nutrients and vitamins.'],
                    ['Organic Camel Milk', 'Milk', 4.50, 50, 'liter', 'active', 'organic-milk.jpg', 'Certified organic camel milk from free-range camels.'],
                    ['Camel Milk Yogurt', 'Dairy', 5.00, 60, 'piece', 'active', 'camel-yogurt.jpg', 'Creamy yogurt made from fresh camel milk. Probiotic-rich.'],
                    ['Camel Cheese', 'Dairy', 8.00, 40, 'piece', 'active', 'camel-cheese.jpg', 'Aged camel milk cheese with unique flavor.'],
                    ['Camel Ghee', 'Dairy', 12.00, 25, 'kg', 'active', 'camel-ghee.jpg', 'Pure clarified butter from camel milk. Ideal for cooking.'],
                    ['Flavored Camel Milk', 'Milk', 4.00, 75, 'liter', 'active', 'flavored-milk.jpg', 'Camel milk with natural flavors - chocolate and strawberry available.'],
                    ['Camel Milk Powder', 'Dairy', 15.00, 30, 'kg', 'active', 'milk-powder.jpg', 'Premium camel milk powder. Easy to store and transport.'],
                    ['Camel Milk Soap', 'Beauty', 3.00, 200, 'piece', 'active', 'camel-soap.jpg', 'Natural soap made with camel milk. Good for skin.']
                ];
                foreach($products as $product) {
                    $pdo->prepare("INSERT INTO products (name, category, price, stock_quantity, unit, status, image, description, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                        ->execute([$product[0], $product[1], $product[2], $product[3], $product[4], $product[5], $product[6], $product[7], $main_branch_id]);
                }
            }
            
            // System settings
            $settings = [
                ['company_name', 'Mahuran Camel Farm', 'Company Name'],
                ['company_email', 'info@mahuran.com', 'Company Email'],
                ['company_phone', '+252 61 2345678', 'Company Phone'],
                ['currency', 'USD', 'Default Currency'],
                ['tax_rate', '5', 'Tax Rate'],
                ['delivery_fee', '2.00', 'Default Delivery Fee']
            ];
            foreach($settings as $setting) {
                $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)")
                    ->execute($setting);
            }
            
            // Add sample sales targets
            $target_count = $pdo->query("SELECT COUNT(*) FROM sales_targets")->fetchColumn();
            if ($target_count == 0) {
                $current_year = date('Y');
                for ($m = 1; $m <= 12; $m++) {
                    $pdo->prepare("INSERT INTO sales_targets (year, month, target_amount, achieved_amount, branch_id) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$current_year, $m, 5000.00, rand(3000, 6000), $main_branch_id]);
                }
            }
            
            // Add sample roles
            $roles = [
                ['super_admin', 'Full system access across all branches', json_encode(['all' => true])],
                ['staff', 'Basic staff access', json_encode(['attendance' => true, 'leave' => true])],
                ['vet', 'Veterinary access', json_encode(['health' => true, 'vaccinations' => true, 'breeding' => true])],
                ['driver', 'Delivery access', json_encode(['deliveries' => true])]
            ];
            foreach($roles as $role) {
                $pdo->prepare("INSERT IGNORE INTO roles (name, description, permissions) VALUES (?, ?, ?)")
                    ->execute($role);
            }
    } catch(PDOException $e) {
        // Skip errors
    }
}

// ==================== LOGIN SYSTEM ====================

// Handle Login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' AND role IN ('super_admin', 'staff', 'vet', 'driver', 'customer')");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent, status) VALUES (?, ?, ?, 'success')")
            ->execute([$user['id'], $ip_address, $user_agent]);
        
        if ($user['role'] == 'customer') {
            $cust = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
            $cust->execute([$user['id']]);
            $customer = $cust->fetch(PDO::FETCH_ASSOC);
            if ($customer) {
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $user['full_name'];
            }
            header('Location: ?page=shop&subpage=products');
            exit;
        } else {
            header('Location: ?page=dashboard');
            exit;
        }
    } else {
        $error = "Invalid username or password";
        if ($user) {
            $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent, status) VALUES (?, ?, ?, 'failed')")
                ->execute([$user['id'], $ip_address, $user_agent]);
        }
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ?page=home');
    exit;
}

// Simplified Customer Login (name + phone)
if (isset($_POST['login_customer'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name && $phone) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE full_name = ? AND phone = ? AND role = 'customer' AND status = 'active'");
        $stmt->execute([$name, $phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            // Fetch customer record if needed
            $custStmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
            $custStmt->execute([$user['id']]);
            $cust = $custStmt->fetch(PDO::FETCH_ASSOC);
            if ($cust) {
                $_SESSION['customer_id'] = $cust['id'];
                $_SESSION['customer_name'] = $user['full_name'];
            }
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent, status) VALUES (?, ?, ?, 'success')")
                ->execute([$user['id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
            header('Location: ?page=shop&subpage=products');
            exit;
        } else {
            $error = "Invalid name or phone.";
        }
    } else {
        $error = "Name and phone are required.";
    }
}
// Handle Customer Registration
if (isset($_POST['register_customer'])) {
    try {
        // Validate required fields
        if (empty($_POST['full_name']) || empty($_POST['phone']) || empty($_POST['address'])) {
            $reg_error = "Name, phone, and address are required for registration.";
        } else {
            // Check if phone already registered
            $check = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $check->execute([$_POST['phone']]);
            if ($check->fetch()) {
                $reg_error = "Phone number already registered!";
            } else {
                // Generate a random password (not used for login)
                $password = password_hash(bin2hex(random_bytes(4)), PASSWORD_DEFAULT);
                // Insert user record
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, role, status) VALUES (?, ?, NULL, ?, ?, 'customer', 'active')");
                $stmt->execute([$_POST['phone'], $password, $_POST['full_name'], $_POST['phone']]);
                $user_id = $pdo->lastInsertId();
                // Insert customer with address
                $stmt = $pdo->prepare("INSERT INTO customers (user_id, name, phone, email, address, city, status) VALUES (?, ?, ?, NULL, ?, '', 'active')");
                $stmt->execute([$user_id, $_POST['full_name'], $_POST['phone'], $_POST['address']]);
                $customer_id = $pdo->lastInsertId();
                // Set session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $_POST['phone'];
                $_SESSION['full_name'] = $_POST['full_name'];
                $_SESSION['role'] = 'customer';
                $_SESSION['logged_in'] = true;
                $_SESSION['customer_id'] = $customer_id;
                $_SESSION['customer_name'] = $_POST['full_name'];
                // Redirect
                header('Location: ?page=shop&subpage=products');
                exit;
            }
        }
    }
    catch (PDOException $e) {
        $reg_error = "Error: " . $e->getMessage();
    }
}

// Check login status
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$current_role = $_SESSION['role'] ?? '';

// Public pages
$public_pages = ['home', 'about', 'products', 'contact', 'login', 'register'];
$current_page = $_GET['page'] ?? 'home';

// Redirect to login if not logged in and trying to access protected pages
if (!$logged_in && !in_array($current_page, $public_pages) && $current_page != 'shop') {
    header('Location: ?page=home');
    exit;
}

// Redirect logged in customers to shop
if ($logged_in && $current_role == 'customer' && in_array($current_page, ['login', 'register'])) {
    header('Location: ?page=shop&subpage=products');
    exit;
}

// Prevent customers from seeing management pages
if ($logged_in && $current_role == 'customer' && !in_array($current_page, ['home', 'about', 'products', 'contact', 'shop', 'login', 'register'])) {
    header('Location: ?page=shop&subpage=products');
    exit;
}