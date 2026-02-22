<?php
require_once 'common/config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$customerId = $_SESSION['customer_id'];

// Get order details
$stmt = $conn->prepare("SELECT o.*, r.name as restaurant_name, r.address as restaurant_address, dp.name as delivery_name, dp.phone as delivery_phone FROM orders o JOIN restaurants r ON o.restaurant_id = r.id LEFT JOIN delivery_partners dp ON o.delivery_partner_id = dp.id WHERE o.id = ? AND o.customer_id = ?");
$stmt->bind_param("ii", $orderId, $customerId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: my_orders.php");
    exit();
}

// Get order items
$orderItems = $conn->query("SELECT od.*, mi.name FROM order_details od JOIN menu_items mi ON od.item_id = mi.id WHERE od.order_id = $orderId");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Track Order - Hunger Food</title>
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
            background: linear-gradient(135deg, #FFF5F3 0%, #FFE8E3 100%);
        }
        .status-line {
            position: relative;
        }
        .status-line::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 48px;
            bottom: -20px;
            width: 3px;
            background: linear-gradient(180deg, #FFE8E3 0%, #FFF5F3 100%);
        }
        .status-line.active::before {
            background: linear-gradient(180deg, #FF6347 0%, #FF8C7A 100%);
        }
        .status-icon {
            transition: all 0.3s ease;
        }
        .status-icon.active {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:active {
            transform: scale(0.98);
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
<body>
    <header class="bg-gradient-to-r from-primary to-orange-500 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-5 flex items-center justify-between">
            <a href="my_orders.php" class="text-white hover:bg-white/20 rounded-full w-10 h-10 flex items-center justify-center transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-xl font-bold text-white">Order #<?php echo $order['id']; ?></h1>
            <div class="w-10"></div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 pb-20">
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-5 card-hover border-2 border-orange-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-primary to-orange-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-route text-white text-xl"></i>
                </div>
                <h2 class="text-xl font-bold bg-gradient-to-r from-primary to-orange-500 bg-clip-text text-transparent">Tracking Status</h2>
            </div>
            
            <div class="space-y-6">
                <div class="status-line <?php echo in_array($order['status'], ['Pending', 'Accepted', 'Preparing', 'Ready', 'Picked Up', 'Delivered']) ? 'active' : ''; ?>">
                    <div class="flex items-start gap-4">
                        <div class="status-icon w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg <?php echo in_array($order['status'], ['Pending', 'Accepted', 'Preparing', 'Ready', 'Picked Up', 'Delivered']) ? 'bg-gradient-to-br from-primary to-orange-500 text-white active' : 'bg-gray-200 text-gray-400'; ?>">
                            <i class="fas fa-receipt text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-lg">Order Placed</p>
                            <p class="text-sm text-gray-500 mt-1"><i class="far fa-clock mr-1"></i><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="status-line <?php echo in_array($order['status'], ['Accepted', 'Preparing', 'Ready', 'Picked Up', 'Delivered']) ? 'active' : ''; ?>">
                    <div class="flex items-start gap-4">
                        <div class="status-icon w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg <?php echo in_array($order['status'], ['Accepted', 'Preparing', 'Ready', 'Picked Up', 'Delivered']) ? 'bg-gradient-to-br from-primary to-orange-500 text-white active' : 'bg-gray-200 text-gray-400'; ?>">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-lg">Order Accepted</p>
                            <p class="text-sm text-gray-500 mt-1">Restaurant confirmed your order</p>
                        </div>
                    </div>
                </div>

                <div class="status-line <?php echo in_array($order['status'], ['Preparing', 'Ready', 'Picked Up', 'Delivered']) ? 'active' : ''; ?>">
                    <div class="flex items-start gap-4">
                        <div class="status-icon w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg <?php echo in_array($order['status'], ['Preparing', 'Ready', 'Picked Up', 'Delivered']) ? 'bg-gradient-to-br from-primary to-orange-500 text-white active' : 'bg-gray-200 text-gray-400'; ?>">
                            <i class="fas fa-fire text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-lg">Preparing Food</p>
                            <p class="text-sm text-gray-500 mt-1">Your delicious meal is being prepared</p>
                        </div>
                    </div>
                </div>

                <div class="status-line <?php echo in_array($order['status'], ['Ready', 'Picked Up', 'Delivered']) ? 'active' : ''; ?>">
                    <div class="flex items-start gap-4">
                        <div class="status-icon w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg <?php echo in_array($order['status'], ['Ready', 'Picked Up', 'Delivered']) ? 'bg-gradient-to-br from-primary to-orange-500 text-white active' : 'bg-gray-200 text-gray-400'; ?>">
                            <i class="fas fa-box-open text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-lg">Ready for Pickup</p>
                            <p class="text-sm text-gray-500 mt-1">Food is ready and packed</p>
                        </div>
                    </div>
                </div>

                <div class="status-line <?php echo in_array($order['status'], ['Picked Up', 'Delivered']) ? 'active' : ''; ?>">
                    <div class="flex items-start gap-4">
                        <div class="status-icon w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg <?php echo in_array($order['status'], ['Picked Up', 'Delivered']) ? 'bg-gradient-to-br from-primary to-orange-500 text-white active' : 'bg-gray-200 text-gray-400'; ?>">
                            <i class="fas fa-shipping-fast text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-lg">Out for Delivery</p>
                            <p class="text-sm text-gray-500 mt-1">On the way to your location</p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-start gap-4">
                        <div class="status-icon w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg <?php echo $order['status'] === 'Delivered' ? 'bg-gradient-to-br from-green-500 to-emerald-500 text-white active' : 'bg-gray-200 text-gray-400'; ?>">
                            <i class="fas fa-check-double text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-lg">Delivered</p>
                            <p class="text-sm text-gray-500 mt-1">Enjoy your meal!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($order['delivery_name'])): ?>
            <div class="bg-gradient-to-br from-white to-orange-50 rounded-3xl shadow-xl p-6 mb-5 card-hover border-2 border-orange-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary to-orange-500 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-motorcycle text-white"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Delivery Partner</h2>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-primary to-orange-500 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                            <?php echo strtoupper(substr($order['delivery_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($order['delivery_name']); ?></p>
                            <p class="text-sm text-gray-600 flex items-center gap-1 mt-1">
                                <i class="fas fa-phone text-primary"></i>
                                <?php echo htmlspecialchars($order['delivery_phone']); ?>
                            </p>
                        </div>
                    </div>
                    <?php if (!empty($order['location_link'])): ?>
                        <a href="<?php echo htmlspecialchars($order['location_link']); ?>" target="_blank" class="bg-gradient-to-br from-primary to-orange-500 text-white w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg hover:shadow-xl transition">
                            <i class="fas fa-map-marker-alt text-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-gradient-to-br from-white to-orange-50 rounded-3xl shadow-xl p-6 mb-5 card-hover border-2 border-orange-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-primary to-orange-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-white"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Order Items</h2>
            </div>
            <div class="space-y-3">
                <?php while ($item = $orderItems->fetch_assoc()): ?>
                    <div class="flex justify-between items-center bg-white rounded-xl p-3 border border-orange-100">
                        <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($item['name']); ?> <span class="text-primary font-bold">×<?php echo $item['quantity']; ?></span></span>
                        <span class="font-bold text-gray-800">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endwhile; ?>
                <div class="border-t-2 border-orange-200 pt-4 flex justify-between items-center">
                    <span class="font-bold text-gray-800 text-lg">Total Amount</span>
                    <span class="font-bold text-primary text-2xl">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-white to-orange-50 rounded-3xl shadow-xl p-6 card-hover border-2 border-orange-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-primary to-orange-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-store text-white"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Restaurant</h2>
            </div>
            <div class="bg-white rounded-xl p-4 border border-orange-100">
                <p class="font-bold text-gray-800 text-lg mb-2"><?php echo htmlspecialchars($order['restaurant_name']); ?></p>
                <p class="text-sm text-gray-600 flex items-start gap-2">
                    <i class="fas fa-map-marker-alt text-primary mt-1"></i>
                    <span><?php echo htmlspecialchars($order['restaurant_address']); ?></span>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
