<?php

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'hunger_food');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Check if database doesn't exist
    $conn_check = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn_check->connect_error) {
        die("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Database Error</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        </head>
        <body class='bg-red-50 min-h-screen flex items-center justify-center p-4'>
            <div class='bg-white rounded-2xl shadow-xl p-8 max-w-md text-center'>
                <i class='fas fa-database text-red-500 text-6xl mb-4'></i>
                <h1 class='text-2xl font-bold text-gray-800 mb-4'>Database Connection Failed</h1>
                <p class='text-gray-600 mb-4'>Cannot connect to MySQL server</p>
                <p class='text-sm text-gray-500 bg-gray-100 p-3 rounded mb-4'>Please check if MySQL is running</p>
            </div>
        </body>
        </html>
        ");
    }
    
    // Database doesn't exist, redirect to install
    $result = $conn_check->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($result->num_rows == 0) {
        $conn_check->close();
        
        // Get current URL path
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        $current_url = $protocol . "://" . $host . $uri;
        
        // Don't redirect if already on install.php
        if (strpos($current_url, 'install.php') === false) {
            // Get the base path
            $base_path = dirname($_SERVER['SCRIPT_NAME']);
            $install_path = rtrim($base_path, '/') . '/install.php';
            
            // Redirect to install.php
            header("Location: " . $install_path);
            exit();
        }
    }
    $conn_check->close();
    
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

$check_column = $conn->query("SHOW COLUMNS FROM restaurants LIKE 'image_url'");
if ($check_column && $check_column->num_rows == 0) {
    $conn->query("ALTER TABLE restaurants ADD COLUMN image_url TEXT AFTER cuisine");
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to sanitize input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Removed OneSignal helper function completely

?>
