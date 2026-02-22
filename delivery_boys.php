<?php
session_start();
require_once '../common/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $delivery_id = (int)$_POST['delivery_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE delivery_partners SET approved = 1 WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE delivery_partners SET approved = 0 WHERE id = ?");
    }
    $stmt->bind_param("i", $delivery_id);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    exit();
}

// Fetch all delivery partners
$delivery_boys = $conn->query("SELECT * FROM delivery_partners ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Delivery Partners - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { -webkit-touch-callout: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
        input, textarea { -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; user-select: text; }
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
                <h1 class="text-xl font-bold text-gray-800">Delivery Partners</h1>
            </div>
            <a href="login.php?logout=1" class="text-primary">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </a>
        </div>
    </div>

    <div class="p-4 space-y-4 pb-24">
        <?php while ($delivery = $delivery_boys->fetch_assoc()): ?>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($delivery['name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($delivery['vehicle_info']); ?></p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $delivery['approved'] ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                    <?php echo $delivery['approved'] ? 'Approved' : 'Pending'; ?>
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-envelope w-4"></i>
                    <span><?php echo htmlspecialchars($delivery['email']); ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-phone w-4"></i>
                    <span><?php echo htmlspecialchars($delivery['phone']); ?></span>
                </div>
            </div>
            
            <div class="flex gap-2">
                <?php if (!$delivery['approved']): ?>
                <button onclick="updateStatus(<?php echo $delivery['id']; ?>, 'approve')" class="flex-1 bg-green-500 text-white py-2 rounded-xl font-semibold hover:bg-green-600 transition">
                    <i class="fas fa-check mr-2"></i>Approve
                </button>
                <?php else: ?>
                <button onclick="updateStatus(<?php echo $delivery['id']; ?>, 'reject')" class="flex-1 bg-red-500 text-white py-2 rounded-xl font-semibold hover:bg-red-600 transition">
                    <i class="fas fa-times mr-2"></i>Revoke
                </button>
                <?php endif; ?>
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
            <a href="delivery_boys.php" class="flex flex-col items-center gap-1 text-primary">
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

    <script>
        function updateStatus(id, action) {
            fetch('delivery_boys.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `delivery_id=${id}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>
