<?php
require_once 'common/config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Get customer details
$customerId = $_SESSION['customer_id'];
$customerResult = $conn->query("SELECT * FROM customers WHERE id = $customerId");
$customer = $customerResult->fetch_assoc();

// Calculate total
$total = 0;
$restaurantId = null;
$itemIds = array_keys($_SESSION['cart']);
$ids = implode(',', $itemIds);
$result = $conn->query("SELECT mi.*, r.id as restaurant_id FROM menu_items mi JOIN restaurants r ON mi.restaurant_id = r.id WHERE mi.id IN ($ids)");

$cartItems = [];
while ($item = $result->fetch_assoc()) {
    $item['quantity'] = $_SESSION['cart'][$item['id']];
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $restaurantId = $item['restaurant_id'];
    $cartItems[] = $item;
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $address = sanitize($_POST['address']);
    
    // Create order
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, restaurant_id, status, total_amount) VALUES (?, ?, 'Pending', ?)");
    $stmt->bind_param("iid", $customerId, $restaurantId, $total);
    $stmt->execute();
    $orderId = $conn->insert_id;
    
    // Add order details
    foreach ($cartItems as $item) {
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    
    // Clear cart
    unset($_SESSION['cart']);
    
    echo json_encode(['success' => true, 'order_id' => $orderId]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Checkout - Hunger Food</title>
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
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
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
<body class="bg-gray-50 pb-32">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="cart.php" class="text-gray-600">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-lg font-bold text-gray-800">Checkout</h1>
            <div></div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="bg-white rounded-2xl shadow-md p-6 mb-4">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-primary"></i>
                Delivery Address
            </h2>
            <form id="checkoutForm">
                <textarea name="address" rows="3" required class="w-full border border-gray-300 rounded-xl p-4 focus:ring-2 focus:ring-primary focus:border-transparent outline-none"><?php echo htmlspecialchars($customer['address']); ?></textarea>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 mb-4">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Order Summary</h2>
            <div class="space-y-3">
                <?php foreach ($cartItems as $item): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600"><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
                        <span class="font-semibold text-gray-800">₹<?php echo number_format($item['subtotal'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="border-t pt-3 flex justify-between">
                    <span class="font-bold text-gray-800">Total</span>
                    <span class="font-bold text-primary text-xl">₹<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-primary"></i>
                Payment Method
            </h2>
            <div class="bg-secondary border-2 border-primary rounded-xl p-4 flex items-center gap-3">
                <i class="fas fa-hand-holding-usd text-primary text-2xl"></i>
                <div>
                    <p class="font-semibold text-gray-800">Cash on Delivery</p>
                    <p class="text-sm text-gray-600">Pay when you receive</p>
                </div>
            </div>
        </div>
    </main>

    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg">
        <div class="max-w-7xl mx-auto">
            <button onclick="placeOrder()" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-orange-600 transition transform active:scale-95">
                Place Order - ₹<?php echo number_format($total, 2); ?>
            </button>
        </div>
    </div>

    <script>
        async function placeOrder() {
            const form = document.getElementById('checkoutForm');
            const formData = new FormData(form);
            formData.append('action', 'place_order');

            const response = await fetch('checkout.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                alert('Order placed successfully!');
                window.location.href = `track_order.php?id=${result.order_id}`;
            } else {
                alert('Failed to place order. Please try again.');
            }
        }
    </script>
</body>
</html>
