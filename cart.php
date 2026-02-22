<?php
require_once 'common/config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_quantity') {
        $itemId = intval($_POST['item_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0) {
            $_SESSION['cart'][$itemId] = $quantity;
        } else {
            unset($_SESSION['cart'][$itemId]);
        }
        
        echo json_encode(['success' => true]);
        exit();
    }
    
    if ($_POST['action'] === 'remove_item') {
        $itemId = intval($_POST['item_id']);
        unset($_SESSION['cart'][$itemId]);
        
        echo json_encode(['success' => true]);
        exit();
    }
    
    if ($_POST['action'] === 'clear_cart') {
        unset($_SESSION['cart']);
        echo json_encode(['success' => true]);
        exit();
    }
}

// Get cart items
$cartItems = [];
$total = 0;
$restaurantId = null;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $itemIds = array_keys($_SESSION['cart']);
    $ids = implode(',', $itemIds);
    $result = $conn->query("SELECT mi.*, r.name as restaurant_name, r.id as restaurant_id FROM menu_items mi JOIN restaurants r ON mi.restaurant_id = r.id WHERE mi.id IN ($ids)");
    
    while ($item = $result->fetch_assoc()) {
        $item['quantity'] = $_SESSION['cart'][$item['id']];
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
        $restaurantId = $item['restaurant_id'];
        $cartItems[] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cart - Hunger Food</title>
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
<body class="bg-gray-50 pb-32">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="index.php" class="text-gray-600">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-lg font-bold text-gray-800">My Cart</h1>
            <?php if (!empty($cartItems)): ?>
                <button onclick="clearCart()" class="text-red-500 text-sm font-semibold">
                    Clear All
                </button>
            <?php else: ?>
                <div></div>
            <?php endif; ?>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <?php if (!empty($cartItems)): ?>
            <div class="space-y-4 mb-6">
                <?php foreach ($cartItems as $item): ?>
                    <div class="bg-white rounded-2xl shadow-md p-4" id="item-<?php echo $item['id']; ?>">
                        <div class="flex gap-4">
                            <div class="w-20 h-20 bg-gradient-to-br from-primary to-orange-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover rounded-xl">
                                <?php else: ?>
                                    <i class="fas fa-utensils text-white text-2xl"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2">₹<?php echo number_format($item['price'], 2); ?> each</p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3 bg-secondary rounded-lg p-1">
                                        <button onclick="updateQuantity(<?php echo $item['id']; ?>, -1)" class="w-8 h-8 bg-white rounded-lg text-primary font-bold hover:bg-primary hover:text-white transition">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <span class="font-bold text-gray-800 w-8 text-center" id="qty-<?php echo $item['id']; ?>"><?php echo $item['quantity']; ?></span>
                                        <button onclick="updateQuantity(<?php echo $item['id']; ?>, 1)" class="w-8 h-8 bg-white rounded-lg text-primary font-bold hover:bg-primary hover:text-white transition">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <span class="text-primary font-bold text-lg" id="subtotal-<?php echo $item['id']; ?>">
                                        ₹<?php echo number_format($item['subtotal'], 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-shopping-cart text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg mb-4">Your cart is empty</p>
                <a href="index.php" class="inline-block bg-primary text-white px-6 py-3 rounded-xl font-semibold hover:bg-orange-600 transition">
                    Browse Restaurants
                </a>
            </div>
        <?php endif; ?>
    </main>

    <?php if (!empty($cartItems)): ?>
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-gray-600">Total Amount</span>
                    <span class="text-2xl font-bold text-primary" id="totalAmount">₹<?php echo number_format($total, 2); ?></span>
                </div>
                <a href="checkout.php" class="block w-full bg-primary text-white text-center py-3 rounded-xl font-semibold hover:bg-orange-600 transition transform active:scale-95">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const itemPrices = <?php echo json_encode(array_column($cartItems, 'price', 'id')); ?>;

        async function updateQuantity(itemId, change) {
            const qtyElement = document.getElementById(`qty-${itemId}`);
            let currentQty = parseInt(qtyElement.textContent);
            let newQty = currentQty + change;

            if (newQty < 1) {
                if (confirm('Remove this item from cart?')) {
                    await removeItem(itemId);
                }
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_quantity');
            formData.append('item_id', itemId);
            formData.append('quantity', newQty);

            const response = await fetch('cart.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                qtyElement.textContent = newQty;
                const subtotal = itemPrices[itemId] * newQty;
                document.getElementById(`subtotal-${itemId}`).textContent = `₹${subtotal.toFixed(2)}`;
                updateTotal();
            }
        }

        async function removeItem(itemId) {
            const formData = new FormData();
            formData.append('action', 'remove_item');
            formData.append('item_id', itemId);

            const response = await fetch('cart.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                document.getElementById(`item-${itemId}`).remove();
                updateTotal();
                
                // Reload if cart is empty
                const items = document.querySelectorAll('[id^="item-"]');
                if (items.length === 0) {
                    location.reload();
                }
            }
        }

        async function clearCart() {
            if (!confirm('Clear all items from cart?')) return;

            const formData = new FormData();
            formData.append('action', 'clear_cart');

            const response = await fetch('cart.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                location.reload();
            }
        }

        function updateTotal() {
            let total = 0;
            Object.keys(itemPrices).forEach(itemId => {
                const qtyElement = document.getElementById(`qty-${itemId}`);
                if (qtyElement) {
                    const qty = parseInt(qtyElement.textContent);
                    total += itemPrices[itemId] * qty;
                }
            });
            document.getElementById('totalAmount').textContent = `₹${total.toFixed(2)}`;
        }
    </script>
</body>
</html>
