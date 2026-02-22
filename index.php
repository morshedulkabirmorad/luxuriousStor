<?php
require_once 'common/config.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Get approved restaurants
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$query = "SELECT * FROM restaurants WHERE approved = 1";
if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR cuisine LIKE '%$search%')";
}
$restaurants = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Home - Hunger Food</title>
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
        input {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        body {
            touch-action: pan-x pan-y;
        }
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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
<body class="bg-gray-50">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Hunger Food</h1>
                    <p class="text-sm text-gray-600">Hello, <?php echo $_SESSION['customer_name']; ?></p>
                </div>
                <a href="profile.php" class="bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </a>
            </div>
            
            <form method="GET" class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" placeholder="Search restaurants or cuisine..." value="<?php echo htmlspecialchars($search); ?>" class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
            </form>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">Available Restaurants</h2>
            <a href="my_orders.php" class="text-primary font-semibold flex items-center gap-2">
                <i class="fas fa-receipt"></i>
                My Orders
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if ($restaurants->num_rows > 0): ?>
                <?php while ($restaurant = $restaurants->fetch_assoc()): ?>
                    <a href="restaurant.php?id=<?php echo $restaurant['id']; ?>" class="bg-white rounded-2xl shadow-md overflow-hidden transform transition hover:scale-105 active:scale-95">
                        <div class="w-full bg-gradient-to-br from-primary to-orange-600 flex items-center justify-center overflow-hidden" style="aspect-ratio: 16/9;">
                            <?php if (!empty($restaurant['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($restaurant['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($restaurant['name']); ?>" 
                                     class="w-full h-full object-cover"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full bg-gradient-to-br from-primary to-orange-600 flex items-center justify-center" style="display:none;">
                                    <i class="fas fa-store text-white text-5xl"></i>
                                </div>
                            <?php else: ?>
                                <i class="fas fa-store text-white text-5xl"></i>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg text-gray-800 mb-1"><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                            <p class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                <?php echo htmlspecialchars($restaurant['address']); ?>
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs bg-secondary text-primary px-3 py-1 rounded-full font-semibold">
                                    <?php echo htmlspecialchars($restaurant['cuisine']); ?>
                                </span>
                                <span class="text-primary font-semibold">
                                    View Menu <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg">No restaurants found</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-around">
            <a href="index.php" class="flex flex-col items-center text-primary">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs font-semibold">Home</span>
            </a>
            <a href="cart.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-shopping-cart text-xl mb-1"></i>
                <span class="text-xs">Cart</span>
            </a>
            <a href="my_orders.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-receipt text-xl mb-1"></i>
                <span class="text-xs">Orders</span>
            </a>
            <a href="profile.php" class="flex flex-col items-center text-gray-400 hover:text-primary transition">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs">Profile</span>
            </a>
        </div>
    </nav>
</body>
</html>
