<?php
// setup_database.php - Initialize SQLite database with all tables
// Uses the Database singleton class.
// NOTE: To use this setup, change the driver in classes/Database.php to 'sqlite'.

require_once __DIR__ . '/classes/Database.php';

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    echo "<h2>🗄️ Database Setup - Pearl Land Commodities</h2>";
    echo "<hr>";
    
    // 1. USERS TABLE
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        full_name TEXT NOT NULL,
        role TEXT NOT NULL CHECK(role IN ('manager', 'stock_clerk', 'account_clerk')),
        email TEXT,
        phone TEXT,
        status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created users table<br>";
    
    // 2. CUSTOMERS TABLE
    $pdo->exec("DROP TABLE IF EXISTS customers");
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        customer_id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_code TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        phone TEXT,
        email TEXT,
        address TEXT,
        city TEXT,
        credit_limit DECIMAL(10, 2) DEFAULT 0,
        status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created customers table<br>";
    
    // 3. SUPPLIERS TABLE
    $pdo->exec("DROP TABLE IF EXISTS suppliers");
    $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
        supplier_id INTEGER PRIMARY KEY AUTOINCREMENT,
        supplier_code TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        phone TEXT,
        email TEXT,
        address TEXT,
        city TEXT,
        payment_terms TEXT,
        status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created suppliers table<br>";
    
    // 4. PRODUCTS TABLE
    $pdo->exec("DROP TABLE IF EXISTS products");
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        product_id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_code TEXT UNIQUE NOT NULL,
        product_name TEXT NOT NULL,
        category TEXT,
        description TEXT,
        unit_price DECIMAL(10, 2) NOT NULL,
        cost_price DECIMAL(10, 2),
        quantity_in_stock INTEGER DEFAULT 0,
        reorder_level INTEGER DEFAULT 10,
        supplier_id INTEGER,
        status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
    )");
    echo "✅ Created products table<br>";
    
    // 5. ORDERS TABLE
    $pdo->exec("DROP TABLE IF EXISTS orders");
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        order_id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_number TEXT UNIQUE NOT NULL,
        customer_id INTEGER NOT NULL,
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        delivery_date DATE,
        total_amount DECIMAL(10, 2),
        status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'confirmed', 'shipped', 'delivered', 'cancelled')),
        notes TEXT,
        created_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
        FOREIGN KEY (created_by) REFERENCES users(user_id)
    )");
    echo "✅ Created orders table<br>";
    
    // 6. ORDER ITEMS TABLE
    $pdo->exec("DROP TABLE IF EXISTS order_items");
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        order_item_id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity INTEGER NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        line_total DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id),
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    )");
    echo "✅ Created order_items table<br>";
    
    // 7. STOCK MOVEMENTS TABLE
    $pdo->exec("DROP TABLE IF EXISTS stock_movements");
    $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
        movement_id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        movement_type TEXT NOT NULL CHECK(movement_type IN ('in', 'out', 'adjustment')),
        quantity INTEGER NOT NULL,
        reference_type TEXT,
        reference_id INTEGER,
        notes TEXT,
        recorded_by INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(product_id),
        FOREIGN KEY (recorded_by) REFERENCES users(user_id)
    )");
    echo "✅ Created stock_movements table<br>";
    
    // 8. INVOICES TABLE
    $pdo->exec("DROP TABLE IF EXISTS invoices");
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
        invoice_id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_number TEXT UNIQUE NOT NULL,
        order_id INTEGER,
        customer_id INTEGER NOT NULL,
        invoice_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        due_date DATE,
        total_amount DECIMAL(10, 2),
        tax_amount DECIMAL(10, 2),
        paid_amount DECIMAL(10, 2) DEFAULT 0,
        status TEXT DEFAULT 'unpaid' CHECK(status IN ('unpaid', 'partial', 'paid', 'overdue')),
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(order_id),
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    )");
    echo "✅ Created invoices table<br>";
    
    // === INSERT SAMPLE DATA ===
    echo "<hr><h3>📝 Adding Sample Data...</h3>";
    
    // Insert Users
    $pdo->exec("INSERT INTO users (username, password, full_name, role, email, phone) VALUES
        ('manager', 'manager123', 'Admin Manager', 'manager', 'manager@pearlland.com', '9876543210'),
        ('clerk', 'clerk123', 'Stock Clerk', 'stock_clerk', 'clerk@pearlland.com', '9876543211'),
        ('account', 'account123', 'Account Clerk', 'account_clerk', 'account@pearlland.com', '9876543212')
    ");
    echo "✅ Inserted 3 users<br>";
    
    // Insert Customers
    $pdo->exec("INSERT INTO customers (customer_code, name, phone, email, address, city, credit_limit) VALUES
        ('CUST001', 'ABC Trading Co.', '9111111111', 'abc@company.com', '123 Market St', 'Delhi', 50000),
        ('CUST002', 'XYZ Spice House', '9222222222', 'xyz@company.com', '456 Trade Lane', 'Mumbai', 75000),
        ('CUST003', 'Prime Retailers', '9333333333', 'prime@company.com', '789 Commerce Ave', 'Bangalore', 100000),
        ('CUST004', 'Quality Foods Ltd', '9444444444', 'quality@company.com', '321 Supply Rd', 'Chennai', 60000),
        ('CUST005', 'Fresh Mart', '9555555555', 'fresh@company.com', '654 Retail Blvd', 'Kolkata', 45000)
    ");
    echo "✅ Inserted 5 customers<br>";
    
    // Insert Suppliers
    $pdo->exec("INSERT INTO suppliers (supplier_code, name, phone, email, address, city, payment_terms) VALUES
        ('SUP001', 'Green Valley Farms', '8111111111', 'greenvalley@supplier.com', '100 Farm Road', 'Kerala', 'Net 30'),
        ('SUP002', 'Spice Masters Ltd', '8222222222', 'spicemasters@supplier.com', '200 Industrial Area', 'Andhra Pradesh', 'Net 45'),
        ('SUP003', 'Premium Imports', '8333333333', 'premiumimports@supplier.com', '300 Port Road', 'Gujarat', 'Net 15'),
        ('SUP004', 'Regional Producers', '8444444444', 'regional@supplier.com', '400 Farming Zone', 'Rajasthan', 'Net 30')
    ");
    echo "✅ Inserted 4 suppliers<br>";
    
    // Insert Products
    $pdo->exec("INSERT INTO products (product_code, product_name, category, unit_price, cost_price, quantity_in_stock, supplier_id) VALUES
        ('PROD001', 'Red Chilli Powder', 'Spices', 250.00, 150.00, 500, 1),
        ('PROD002', 'Turmeric Powder', 'Spices', 200.00, 120.00, 750, 1),
        ('PROD003', 'Coriander Seeds', 'Seeds', 180.00, 100.00, 600, 2),
        ('PROD004', 'Black Pepper', 'Spices', 300.00, 180.00, 400, 2),
        ('PROD005', 'Cumin Seeds', 'Seeds', 150.00, 80.00, 800, 3),
        ('PROD006', 'Garam Masala Mix', 'Spices', 350.00, 200.00, 300, 4),
        ('PROD007', 'Fenugreek Leaves', 'Herbs', 120.00, 60.00, 450, 1),
        ('PROD008', 'Asafoetida', 'Spices', 400.00, 250.00, 200, 3)
    ");
    echo "✅ Inserted 8 products<br>";
    
    // Insert Orders
    $pdo->exec("INSERT INTO orders (order_number, customer_id, order_date, total_amount, status, created_by) VALUES
        ('ORD001', 1, '2026-06-01', 15000.00, 'delivered', 1),
        ('ORD002', 2, '2026-06-03', 22500.00, 'shipped', 1),
        ('ORD003', 3, '2026-06-05', 18750.00, 'confirmed', 1),
        ('ORD004', 4, '2026-06-06', 25000.00, 'pending', 1),
        ('ORD005', 5, '2026-06-07', 12500.00, 'pending', 1)
    ");
    echo "✅ Inserted 5 orders<br>";
    
    // Insert Order Items
    $pdo->exec("INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total) VALUES
        (1, 1, 20, 250.00, 5000.00),
        (1, 2, 30, 200.00, 6000.00),
        (1, 5, 50, 150.00, 7500.00),
        (2, 3, 75, 180.00, 13500.00),
        (2, 4, 30, 300.00, 9000.00),
        (3, 6, 25, 350.00, 8750.00),
        (3, 7, 100, 120.00, 12000.00),
        (4, 8, 50, 400.00, 20000.00),
        (4, 1, 25, 250.00, 6250.00),
        (5, 2, 40, 200.00, 8000.00),
        (5, 5, 50, 150.00, 7500.00)
    ");
    echo "✅ Inserted 11 order items<br>";
    
    // Insert Invoices
    $pdo->exec("INSERT INTO invoices (invoice_number, order_id, customer_id, due_date, total_amount, tax_amount, paid_amount, status) VALUES
        ('INV001', 1, 1, '2026-06-21', 15000.00, 2700.00, 15000.00, 'paid'),
        ('INV002', 2, 2, '2026-06-23', 22500.00, 4050.00, 10000.00, 'partial'),
        ('INV003', 3, 3, '2026-06-25', 18750.00, 3375.00, 0, 'unpaid'),
        ('INV004', 4, 4, '2026-06-26', 25000.00, 4500.00, 0, 'unpaid'),
        ('INV005', 5, 5, '2026-06-27', 12500.00, 2250.00, 0, 'unpaid')
    ");
    echo "✅ Inserted 5 invoices<br>";
    
    // Insert Stock Movements
    $pdo->exec("INSERT INTO stock_movements (product_id, movement_type, quantity, reference_type, notes, recorded_by) VALUES
        (1, 'in', 200, 'purchase', 'Initial stock intake', 2),
        (2, 'in', 300, 'purchase', 'Initial stock intake', 2),
        (1, 'out', 20, 'order', 'Sold with order ORD001', 2),
        (3, 'in', 150, 'purchase', 'Fresh supply', 2)
    ");
    echo "✅ Inserted 4 stock movements<br>";
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Database Setup Complete!</h3>";
    echo "<p><strong>Tables Created:</strong> 8</p>";
    echo "<p><strong>Sample Data Inserted:</strong> Yes</p>";
    echo "<p><a href='index.php'>👈 Back to Login</a></p>";
    
} catch(PDOException $e) {
    echo "<h2>❌ Error Setting Up Database</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
