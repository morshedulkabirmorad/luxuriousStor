<?php
session_start();
require_once '../common/config.php';

if(!isset($_SESSION['restaurant_id'])) {
    header('Location: login.php');
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'get_stats') {
        $stats = [];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE restaurant_id = $restaurant_id");
        $stats['total_orders'] = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE restaurant_id = $restaurant_id AND status = 'Delivered'");
        $stats['total_revenue'] = $result->fetch_assoc()['revenue'] ?? 0;
        
        $result = $conn->query("SELECT COUNT(*) as pending FROM orders WHERE restaurant_id = $restaurant_id AND status = 'Pending'");
        $stats['pending_orders'] = $result->fetch_assoc()['pending'];
        
        echo json_encode($stats);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Restaurant Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="bg-[#FF6347] text-white p-4 flex items-center justify-between sticky top-0 z-50 shadow-lg">
        <a href="index.php" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold">Reports & Analytics</h1>
        <div class="w-10"></div>
    </div>

    <div class="p-4 pb-24">
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white">
                <i class="fas fa-shopping-bag text-2xl mb-2 opacity-80"></i>
                <p class="text-3xl font-bold" id="totalOrders">0</p>
                <p class="text-sm opacity-90">Total Orders</p>
            </div>
            
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white">
                <i class="fas fa-rupee-sign text-2xl mb-2 opacity-80"></i>
                <p class="text-3xl font-bold" id="totalRevenue">₹0</p>
                <p class="text-sm opacity-90">Revenue</p>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-4 text-white">
                <i class="fas fa-clock text-2xl mb-2 opacity-80"></i>
                <p class="text-3xl font-bold" id="pendingOrders">0</p>
                <p class="text-sm opacity-90">Pending</p>
            </div>
            
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 text-white">
                <i class="fas fa-star text-2xl mb-2 opacity-80"></i>
                <p class="text-3xl font-bold">4.5</p>
                <p class="text-sm opacity-90">Rating</p>
            </div>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3 flex justify-around">
        <a href="index.php" class="flex flex-col items-center text-gray-400">
            <i class="fas fa-receipt text-xl mb-1"></i>
            <span class="text-xs">Orders</span>
        </a>
        <a href="menu.php" class="flex flex-col items-center text-gray-400">
            <i class="fas fa-utensils text-xl mb-1"></i>
            <span class="text-xs">Menu</span>
        </a>
        <a href="reports.php" class="flex flex-col items-center text-[#FF6347]">
            <i class="fas fa-chart-line text-xl mb-1"></i>
            <span class="text-xs font-medium">Reports</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center text-gray-400">
            <i class="fas fa-user text-xl mb-1"></i>
            <span class="text-xs">Profile</span>
        </a>
    </div>

    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => e.preventDefault());

        async function loadStats() {
            const formData = new FormData();
            formData.append('action', 'get_stats');
            
            const response = await fetch('reports.php', {
                method: 'POST',
                body: formData
            });
            const stats = await response.json();
            
            document.getElementById('totalOrders').textContent = stats.total_orders;
            document.getElementById('totalRevenue').textContent = '₹' + stats.total_revenue;
            document.getElementById('pendingOrders').textContent = stats.pending_orders;
        }

        loadStats();
    </script>
</body>
</html>
