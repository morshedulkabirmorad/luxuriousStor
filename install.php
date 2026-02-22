<?php

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'hunger_food');

// Create connection without database first
$conn_temp = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn_temp->connect_error) {
    die("
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Installation Error</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-red-50 min-h-screen flex items-center justify-center p-4'>
        <div class='bg-white rounded-2xl shadow-xl p-8 max-w-md'>
            <div class='text-red-500 text-6xl mb-4 text-center'>
                <i class='fas fa-exclamation-circle'></i>
            </div>
            <h1 class='text-2xl font-bold text-gray-800 mb-4 text-center'>Database Connection Failed</h1>
            <p class='text-gray-600 mb-4'>Please check your database credentials in install.php</p>
            <p class='text-sm text-gray-500 bg-gray-100 p-3 rounded'>Error: " . $conn_temp->connect_error . "</p>
        </div>
    </body>
    </html>
    ");
}

// Create database
$conn_temp->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
$conn_temp->close();

// Reconnect with database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        onesignal_player_id TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS restaurants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        address TEXT,
        cuisine VARCHAR(255),
        image_url TEXT,
        approved TINYINT(1) DEFAULT 0,
        onesignal_player_id TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS delivery_partners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        vehicle_info VARCHAR(255),
        approved TINYINT(1) DEFAULT 0,
        onesignal_player_id TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        restaurant_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(500),
        is_available TINYINT(1) DEFAULT 1,
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        restaurant_id INT NOT NULL,
        delivery_partner_id INT,
        status VARCHAR(50) DEFAULT 'Pending',
        total_amount DECIMAL(10,2) NOT NULL,
        location_link TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
        FOREIGN KEY (delivery_partner_id) REFERENCES delivery_partners(id) ON DELETE SET NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS order_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        item_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES menu_items(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        app_name VARCHAR(255) DEFAULT 'Hunger Food',
        support_email VARCHAR(255),
        support_phone VARCHAR(20),
        onesignal_app_id TEXT,
        onesignal_api_key TEXT
    )"
];

$success = true;
foreach ($tables as $table) {
    if (!$conn->query($table)) {
        $success = false;
        break;
    }
}

// Insert default admin
$adminCheck = $conn->query("SELECT * FROM admin WHERE username = 'admin'");
if ($adminCheck->num_rows == 0) {
    $password = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin (username, password) VALUES ('admin', '$password')");
}

// Insert default settings
$settingsCheck = $conn->query("SELECT * FROM settings WHERE id = 1");
if ($settingsCheck->num_rows == 0) {
    $conn->query("INSERT INTO settings (app_name, support_email, support_phone) VALUES ('Hunger Food', 'support@hungerfood.com', '+1234567890')");
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete - Hunger Food</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF6347'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-2xl w-full">
        <div class="text-center mb-8">
            <div class="inline-block bg-green-100 rounded-full p-6 mb-4">
                <i class="fas fa-check-circle text-green-500 text-6xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Installation Complete!</h1>
            <p class="text-gray-600">Hunger Food has been successfully installed</p>
        </div>

        <div class="bg-gray-50 rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Default Login Credentials</h2>
            
            <div class="space-y-4">
                <div class="bg-white rounded-xl p-4 border-l-4 border-primary">
                    <h3 class="font-semibold text-gray-800 mb-2">Admin Panel</h3>
                    <p class="text-sm text-gray-600">Username: <span class="font-mono bg-gray-100 px-2 py-1 rounded">admin</span></p>
                    <p class="text-sm text-gray-600">Password: <span class="font-mono bg-gray-100 px-2 py-1 rounded">123456</span></p>
                    <p class="text-xs text-gray-500 mt-2">Access: <a href="admin/login.php" class="text-primary hover:underline">admin/login.php</a></p>
                </div>

                <div class="bg-white rounded-xl p-4 border-l-4 border-blue-500">
                    <h3 class="font-semibold text-gray-800 mb-2">Customer Panel</h3>
                    <p class="text-sm text-gray-600">Create new account or login</p>
                    <p class="text-xs text-gray-500 mt-2">Access: <a href="login.php" class="text-primary hover:underline">login.php</a></p>
                </div>

                <div class="bg-white rounded-xl p-4 border-l-4 border-orange-500">
                    <h3 class="font-semibold text-gray-800 mb-2">Restaurant Panel</h3>
                    <p class="text-sm text-gray-600">Register as restaurant partner</p>
                    <p class="text-xs text-gray-500 mt-2">Access: <a href="restaurant/login.php" class="text-primary hover:underline">restaurant/login.php</a></p>
                </div>

                <div class="bg-white rounded-xl p-4 border-l-4 border-green-500">
                    <h3 class="font-semibold text-gray-800 mb-2">Delivery Partner Panel</h3>
                    <p class="text-sm text-gray-600">Register as delivery partner</p>
                    <p class="text-xs text-gray-500 mt-2">Access: <a href="delivery/login.php" class="text-primary hover:underline">delivery/login.php</a></p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-1">Important Security Note</h3>
                    <p class="text-sm text-gray-600">Please change the default admin password after first login and delete this install.php file for security.</p>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <a href="admin/login.php" class="flex-1 bg-primary text-white py-3 rounded-xl font-semibold text-center hover:bg-orange-600 transition">
                Go to Admin Panel
            </a>
            <a href="login.php" class="flex-1 bg-gray-800 text-white py-3 rounded-xl font-semibold text-center hover:bg-gray-700 transition">
                Go to Customer App
            </a>
        </div>
    </div>

    <script>
        // Auto redirect after 10 seconds
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 10000);
    </script>
</body>
</html>
