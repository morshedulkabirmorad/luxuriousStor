<?php
require_once 'common/config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customerId = $_SESSION['customer_id'];
$orders = $conn->query("SELECT o.*, r.name as restaurant_name FROM orders o JOIN restaurants r ON o.restaurant_id = r.id WHERE o.customer_id = $customerId ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Orders - Hunger Food</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        body {
            touch-action: pan-x pan-y;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF6347',
                        secondary: '#FFF5F3'
                    }
                }
            }
        }
        
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('gesturestart', e => e.preventDefault());
    </script>
</head>
<body class="bg-gray-50 pb-20">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="index.php" class="text-gray-600">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-lg font-bold text-gray-800">My Orders</h1>
            <div></div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <?php if ($orders->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <a href="track_order.php?id=<?php echo $order['id']; ?>" class="block bg-white rounded-2xl shadow-md p-4 hover:shadow-lg transition">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-bold text-gray-800">Order #<?php echo $order['id']; ?></h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($order['restaurant_name']); ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                <?php 
                                    if ($order['status'] === 'Delivered') echo 'bg-green-100 text-green-700';
                                    elseif ($order['status'] === 'Cancelled') echo 'bg-red-100 text-red-700';
                                    else echo 'bg-yellow-100 text-yellow-700';
                                ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-clock"></i>
                                <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                            </span>
                            <span class="text-primary font-bold text-lg">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-receipt text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg mb-4">No orders yet</p>
                <a href="index.php" class="inline-block bg-primary text-white px-6 py-3 rounded-xl font-semibold hover:bg-orange-600 transition">
                    Start Ordering
                </a>
            </div>
        <?php endif; ?>
    </main>

    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-around">
            <a href="index.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs">Home</span>
            </a>
            <a href="cart.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-shopping-cart text-xl mb-1"></i>
                <span class="text-xs">Cart</span>
            </a>
            <a href="my_orders.php" class="flex flex-col items-center text-primary">
                <i class="fas fa-receipt text-xl mb-1"></i>
                <span class="text-xs font-semibold">Orders</span>
            </a>
            <a href="profile.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs">Profile</span>
            </a>
        </div>
    </nav>
</body>
</html>
