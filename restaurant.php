<?php
require_once 'common/config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$restaurantId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get restaurant details
$stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = ? AND approved = 1");
$stmt->bind_param("i", $restaurantId);
$stmt->execute();
$restaurant = $stmt->get_result()->fetch_assoc();

if (!$restaurant) {
    header("Location: index.php");
    exit();
}

// Get menu items
$menuItems = $conn->query("SELECT * FROM menu_items WHERE restaurant_id = $restaurantId AND is_available = 1");

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $itemId = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$itemId])) {
        $_SESSION['cart'][$itemId] += $quantity;
    } else {
        $_SESSION['cart'][$itemId] = $quantity;
    }
    
    echo json_encode(['success' => true, 'cart_count' => array_sum($_SESSION['cart'])]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($restaurant['name']); ?> - Hunger Food</title>
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
            <h1 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($restaurant['name']); ?></h1>
            <a href="cart.php" class="relative">
                <i class="fas fa-shopping-cart text-xl text-gray-600"></i>
                <span id="cartBadge" class="absolute -top-2 -right-2 bg-primary text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                    <?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                </span>
            </a>
        </div>
    </header>

    <div class="bg-gradient-to-br from-primary to-orange-600 text-white p-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-store text-primary text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($restaurant['name']); ?></h2>
                    <p class="text-white/90"><?php echo htmlspecialchars($restaurant['cuisine']); ?></p>
                </div>
            </div>
            <p class="text-white/90">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo htmlspecialchars($restaurant['address']); ?>
            </p>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Menu</h2>
        
        <div class="space-y-4">
            <?php if ($menuItems->num_rows > 0): ?>
                <?php while ($item = $menuItems->fetch_assoc()): ?>
                    <div class="bg-white rounded-2xl shadow-md p-4 flex gap-4">
                        <div class="w-24 h-24 bg-gradient-to-br from-primary to-orange-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover rounded-xl">
                            <?php else: ?>
                                <i class="fas fa-utensils text-white text-3xl"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-primary font-bold text-lg">₹<?php echo number_format($item['price'], 2); ?></span>
                                <button onclick="addToCart(<?php echo $item['id']; ?>)" class="bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-orange-600 transition transform active:scale-95">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-utensils text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg">No menu items available</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        async function addToCart(itemId) {
            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('item_id', itemId);
            formData.append('quantity', 1);

            const response = await fetch('restaurant.php?id=<?php echo $restaurantId; ?>', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                document.getElementById('cartBadge').textContent = result.cart_count;
                
                // Show feedback
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Added';
                btn.classList.add('bg-green-500');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('bg-green-500');
                }, 1000);
            }
        }
    </script>
</body>
</html>
