<?php
session_start();
require_once '../common/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = (int)$_POST['order_id'];
    $action = $_POST['action'];
    
    if ($action === 'cancel') {
        $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
    } elseif ($action === 'reassign') {
        $delivery_id = (int)$_POST['delivery_id'];
        $stmt = $conn->prepare("UPDATE orders SET delivery_partner_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $delivery_id, $order_id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true]);
    exit();
}

// Fetch all orders with details
$orders = $conn->query("
    SELECT o.*, 
           c.name as customer_name, 
           r.name as restaurant_name,
           d.name as delivery_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN restaurants r ON o.restaurant_id = r.id
    LEFT JOIN delivery_partners d ON o.delivery_partner_id = d.id
    ORDER BY o.created_at DESC
");

// Fetch available delivery partners
$delivery_partners = $conn->query("SELECT id, name FROM delivery_partners WHERE approved = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Orders - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { -webkit-touch-callout: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
        select { -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; user-select: text; }
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
                <h1 class="text-xl font-bold text-gray-800">Orders</h1>
            </div>
            <a href="login.php?logout=1" class="text-primary">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </a>
        </div>
    </div>

    <div class="p-4 space-y-4 pb-24">
        <?php while ($order = $orders->fetch_assoc()): ?>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-bold text-gray-800">Order #<?php echo $order['id']; ?></h3>
                    <p class="text-sm text-gray-500"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                    <?php echo $order['status']; ?>
                </span>
            </div>
            
            <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-user w-4 text-gray-400"></i>
                    <span class="text-gray-700"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-store w-4 text-gray-400"></i>
                    <span class="text-gray-700"><?php echo htmlspecialchars($order['restaurant_name']); ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-motorcycle w-4 text-gray-400"></i>
                    <span class="text-gray-700"><?php echo $order['delivery_name'] ? htmlspecialchars($order['delivery_name']) : 'Not assigned'; ?></span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-dollar-sign w-4 text-gray-400"></i>
                    <span class="font-bold text-primary">$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button onclick="showReassignModal(<?php echo $order['id']; ?>)" class="flex-1 bg-blue-500 text-white py-2 rounded-xl text-sm font-semibold hover:bg-blue-600 transition">
                    <i class="fas fa-exchange-alt mr-1"></i>Reassign
                </button>
                <button onclick="cancelOrder(<?php echo $order['id']; ?>)" class="flex-1 bg-red-500 text-white py-2 rounded-xl text-sm font-semibold hover:bg-red-600 transition">
                    <i class="fas fa-times mr-1"></i>Cancel
                </button>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div id="reassignModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-end">
        <div class="bg-white w-full rounded-t-3xl p-6">
            <h3 class="text-xl font-bold mb-4">Reassign Delivery Partner</h3>
            <select id="deliverySelect" class="w-full p-3 border border-gray-300 rounded-xl mb-4">
                <?php 
                $delivery_partners->data_seek(0);
                while ($dp = $delivery_partners->fetch_assoc()): 
                ?>
                <option value="<?php echo $dp['id']; ?>"><?php echo htmlspecialchars($dp['name']); ?></option>
                <?php endwhile; ?>
            </select>
            <div class="flex gap-2">
                <button onclick="closeModal()" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold">Cancel</button>
                <button onclick="confirmReassign()" class="flex-1 bg-primary text-white py-3 rounded-xl font-semibold">Confirm</button>
            </div>
        </div>
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
            <a href="orders.php" class="flex flex-col items-center gap-1 text-primary">
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
        let currentOrderId = null;

        function showReassignModal(orderId) {
            currentOrderId = orderId;
            document.getElementById('reassignModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('reassignModal').classList.add('hidden');
        }

        function confirmReassign() {
            const deliveryId = document.getElementById('deliverySelect').value;
            fetch('orders.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `order_id=${currentOrderId}&action=reassign&delivery_id=${deliveryId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch('orders.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `order_id=${orderId}&action=cancel`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>
