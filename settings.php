<?php
session_start();
require_once '../common/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_name = sanitize($_POST['app_name']);
    $support_email = sanitize($_POST['support_email']);
    $support_phone = sanitize($_POST['support_phone']);
    
    $stmt = $conn->prepare("UPDATE settings SET app_name = ?, support_email = ?, support_phone = ? WHERE id = 1");
    $stmt->bind_param("sss", $app_name, $support_email, $support_phone);
    
    if ($stmt->execute()) {
        $success_message = "Settings updated successfully!";
    }
}

// Fetch current settings
$settings = $conn->query("SELECT * FROM settings WHERE id = 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Settings - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { -webkit-touch-callout: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
        input { -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; user-select: text; }
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
                <h1 class="text-xl font-bold text-gray-800">Settings</h1>
            </div>
            <a href="login.php?logout=1" class="text-primary">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </a>
        </div>
    </div>

    <div class="p-4 pb-24">
        <?php if (isset($success_message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4">
            <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">App Name</label>
                    <input type="text" name="app_name" value="<?php echo htmlspecialchars($settings['app_name']); ?>" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Support Email</label>
                    <input type="email" name="support_email" value="<?php echo htmlspecialchars($settings['support_email']); ?>" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Support Phone</label>
                    <input type="tel" name="support_phone" value="<?php echo htmlspecialchars($settings['support_phone']); ?>" required class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent outline-none">
                </div>
                
                <button type="submit" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-orange-600 transition transform active:scale-95 shadow-lg">
                    <i class="fas fa-save mr-2"></i>Save Settings
                </button>
            </form>
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
            <a href="orders.php" class="flex flex-col items-center gap-1 text-gray-400">
                <i class="fas fa-receipt text-xl"></i>
                <span class="text-xs">Orders</span>
            </a>
            <a href="settings.php" class="flex flex-col items-center gap-1 text-primary">
                <i class="fas fa-cog text-xl"></i>
                <span class="text-xs">Settings</span>
            </a>
        </div>
    </div>
</body>
</html>
