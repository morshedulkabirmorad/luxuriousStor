<?php
session_start();
require_once '../common/config.php';

if(!isset($_SESSION['delivery_id'])) {
    header('Location: login.php');
    exit;
}

$delivery_id = $_SESSION['delivery_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'get_earnings') {
        $stats = [];
        
        $result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE delivery_partner_id = $delivery_id AND status = 'Delivered'");
        $stats['total_deliveries'] = $result->fetch_assoc()['total'];
        
        $stats['total_earnings'] = $stats['total_deliveries'] * 50;
        
        $result = $conn->query("SELECT COUNT(*) as today FROM orders WHERE delivery_partner_id = $delivery_id AND status = 'Delivered' AND DATE(created_at) = CURDATE()");
        $stats['today_deliveries'] = $result->fetch_assoc()['today'];
        
        $stats['today_earnings'] = $stats['today_deliveries'] * 50;
        
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
    <title>Earnings - Delivery Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
      .premium-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
      .premium-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid rgba(0,0,0,0.05);
      }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">
    <div class="premium-gradient text-white p-6 flex items-center justify-between sticky top-0 z-50 shadow-2xl">
        <a href="index.php" class="w-14 h-14 bg-white/20 backdrop-blur-lg rounded-2xl flex items-center justify-center hover:bg-white/30 transition-all shadow-lg">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-2xl font-bold tracking-tight">Earnings</h1>
        <div class="w-14"></div>
    </div>

    <div class="p-4 pb-24">
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-2xl p-4 text-white shadow-xl bg-gradient-to-br from-green-500 to-emerald-600">
                <i class="fas fa-rupee-sign text-2xl mb-2 opacity-90"></i>
                <p class="text-3xl font-extrabold" id="totalEarnings">₹0</p>
                <p class="text-sm/5 opacity-95">Total Earnings</p>
            </div>

            <div class="rounded-2xl p-4 text-white shadow-xl bg-gradient-to-br from-blue-500 to-indigo-600">
                <i class="fas fa-box text-2xl mb-2 opacity-90"></i>
                <p class="text-3xl font-extrabold" id="totalDeliveries">0</p>
                <p class="text-sm/5 opacity-95">Total Deliveries</p>
            </div>

            <div class="rounded-2xl p-4 text-white shadow-xl bg-gradient-to-br from-amber-500 to-orange-500">
                <i class="fas fa-calendar-day text-2xl mb-2 opacity-90"></i>
                <p class="text-3xl font-extrabold" id="todayEarnings">₹0</p>
                <p class="text-sm/5 opacity-95">Today's Earnings</p>
            </div>

            <div class="rounded-2xl p-4 text-white shadow-xl bg-gradient-to-br from-purple-500 to-violet-600">
                <i class="fas fa-motorcycle text-2xl mb-2 opacity-90"></i>
                <p class="text-3xl font-extrabold" id="todayDeliveries">0</p>
                <p class="text-sm/5 opacity-95">Today's Deliveries</p>
            </div>
        </div>

        <div class="premium-card rounded-2xl shadow-xl p-4 mt-6">
            <h3 class="font-bold mb-1">Earning Rate</h3>
            <p class="text-gray-600">₹50 per delivery</p>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-xl border-t border-gray-200/50 px-8 py-4 flex justify-around shadow-2xl">
        <a href="index.php" class="flex flex-col items-center text-gray-400 hover:text-gray-600 transition-colors">
            <div class="w-12 h-12 flex items-center justify-center mb-1">
                <i class="fas fa-motorcycle text-2xl"></i>
            </div>
            <span class="text-xs font-medium">Orders</span>
        </a>
        <a href="earnings.php" class="flex flex-col items-center transform scale-110">
            <div class="w-12 h-12 premium-gradient rounded-2xl flex items-center justify-center mb-1 shadow-lg">
                <i class="fas fa-rupee-sign text-white text-xl"></i>
            </div>
            <span class="text-xs font-bold text-purple-600">Earnings</span>
        </a>
        <a href="profile.php" class="flex flex-col items-center text-gray-400 hover:text-gray-600 transition-colors">
            <div class="w-12 h-12 flex items-center justify-center mb-1">
                <i class="fas fa-user text-2xl"></i>
            </div>
            <span class="text-xs font-medium">Profile</span>
        </a>
    </div>

    <script>
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => e.preventDefault());

        async function loadEarnings() {
            const formData = new FormData();
            formData.append('action', 'get_earnings');
            
            const response = await fetch('earnings.php', {
                method: 'POST',
                body: formData
            });
            const stats = await response.json();
            
            document.getElementById('totalEarnings').textContent = '₹' + stats.total_earnings;
            document.getElementById('totalDeliveries').textContent = stats.total_deliveries;
            document.getElementById('todayEarnings').textContent = '₹' + stats.today_earnings;
            document.getElementById('todayDeliveries').textContent = stats.today_deliveries;
        }

        loadEarnings();
    </script>
</body>
</html>
