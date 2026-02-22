<?php
session_start();
require_once '../common/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all customers
$customers = $conn->query("SELECT * FROM customers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Customers - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { -webkit-touch-callout: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
        body { touch-action: pan-x pan-y; }
    </style>
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#FF6347' } } } }
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('gesturestart', e => e.preventDefault());
    </script>
</head>
<body class="bg-gray-50">
    <div class="bg-white shadow-sm border-b sticky top-0 z-10">
        <div class="flex items-center justify-between p-4">
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-gray-600">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Customers</h1>
            </div>
            <a href="login.php?logout=1" class="text-primary">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </a>
        </div>
    </div>

    <div class="p-4 space-y-4 pb-24">
        <?php while ($customer = $customers->fetch_assoc()): ?>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                    <span class="text-primary font-bold text-lg"><?php echo strtoupper(substr($customer['name'], 0, 1)); ?></span>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($customer['name']); ?></h3>
                    <p class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></p>
                </div>
            </div>
            
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-envelope w-4"></i>
                    <span><?php echo htmlspecialchars($customer['email']); ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-phone w-4"></i>
                    <span><?php echo htmlspecialchars($customer['phone']); ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-map-marker-alt w-4"></i>
                    <span><?php echo htmlspecialchars($customer['address']); ?></span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg">
        <div class="flex justify-around py-3">
            <a href="index.php" class="flex flex-col items-center gap-1 text-gray-400">
                <i class="fas fa-home text-xl"></i>
                <span class="text-xs">Dashboard</span>
            </a>
            <a href="restaurants.php" class="flex flex-col items-center gap-1 text-gray-400">
                <i class="fas fa-store text-xl"></i>
                <span class="text-xs">Restaurants</span>
            </a>
            <a href="delivery_boys.php" class="flex flex-col items-center gap-1 text-gray-400">
                <i class="fas fa-motorcycle text-xl"></i>
                <span class="text-xs">Delivery</span>
            </a>
            <a href="orders.php" class="flex flex-col items-center gap-1 text-gray-400">
                <i class="fas fa-receipt text-xl"></i>
                <span class="text-xs">Orders</span>
            </a>
            <a href="settings.php" class="flex flex-col items-center gap-1 text-gray-400">
                <i class="fas fa-cog text-xl"></i>
                <span class="text-xs">Settings</span>
            </a>
        </div>
    </div>
</body>
</html>
